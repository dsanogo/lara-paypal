<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">


            <div class="content">
             <!-- Set up a container element for the button -->
                <div id="paypal-button-container"></div>
                <input type="text" name="amount" id="amountToPay" placeholder="enter amount to pay">
            </div>
        </div>

    <!-- Include the PayPal JavaScript SDK -->
    <script src="https://www.paypal.com/sdk/js?client-id=sb&currency=USD"></script>

    <script>
        // Render the PayPal button into #paypal-button-container

        paypal.Buttons({
            style: {
                layout:  'horizontal',
                color:   'silver',
                shape:   'pill',
                label:   'pay'
            },
            // Set up the transaction
            createOrder: function(data, actions) {
                const amount = document.getElementById('amountToPay').value;
                if(amount === ''){
                    return false;
                    alert('Please enter an amount');
                }
                return fetch('/api/paypal/order/create', {
                    method: 'post',
                    headers: {
                        'Accept': 'application/json, text/plain, */*',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({amount: amount})
                }).then(function(res) {
                    return res.json();
                }).then(function(data) {
                    return data.result.id;
                });
            },

            // Finalize the transaction
            onApprove: function(data, actions) {
                return fetch('/api/paypal/order/' + data.orderID + '/capture', {
                    method: 'post'
                }).then(function(res) {
                    if (res.error === 'INSTRUMENT_DECLINED') {
                      return actions.restart();
                    }
                    return res.json();
                }).then(function(details) {
                    document.getElementById('amountToPay').value = '';
                    // Show a success message to the buyer
                    alert('Transaction completed by ' + details.result.payer.name.given_name + '!');
                });
            }


        }).render('#paypal-button-container');
    </script>
    </body>
</html>
