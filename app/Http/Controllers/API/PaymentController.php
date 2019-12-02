<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
class PaymentController extends Controller
{
    private $client;

    public function __construct() {
        $this->client =  new PayPalHttpClient(
            new SandboxEnvironment(
                config('paypal')['apiClient'],
                config('paypal')['apiSecret'])
        );
    }

    public function createOrder(Request $request)
    {
        if(!isset($request->amount)){
            return null;
        }

        $amount = $request->amount;
        $request = new OrdersCreateRequest();
        $request->headers["prefer"] = "return=representation";
        $request->body = [
                            "intent" => "CAPTURE",
                            "purchase_units" => [[
                                "reference_id" => uniqid(),
                                "amount" => [
                                    "value" => $amount,
                                    "currency_code" => "USD"
                                ]
                            ]],
                            "application_context" => [
                                "cancel_url" => route('home'),
                                "return_url" => route('home')
                            ]
                        ];

        try {
            // Call API with your client and get a response for your call
            $response = $this->client->execute($request);

            // If call returns body in response, you can get the deserialized version from the result attribute of the response
            return json_encode($response);
        }catch (HttpException $ex) {
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }

    public function captureOrder(Request $request, $orderId)
    {
        // Here, OrdersCaptureRequest() creates a POST request to /v2/checkout/orders
        // $response->result->id gives the orderId of the order created above
        $request = new OrdersCaptureRequest($orderId);
        $request->prefer('return=representation');
        try {
            // Call API with your client and get a response for your call
            $response = $this->client->execute($request);

            // If call returns body in response, you can get the deserialized version from the result attribute of the response
            return json_encode($response);
        }catch (HttpException $ex) {
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }
}
