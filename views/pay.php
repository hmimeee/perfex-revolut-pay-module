<html>

<head>
    <title>Pay with Revolut</title>
    <script>
        ! function(e, o, t) {
            e[t] = function(n, r) {
                var c = {
                        sandbox: "https://sandbox-merchant.revolut.com/embed.js",
                        prod: "https://merchant.revolut.com/embed.js",
                        dev: "https://merchant.revolut.codes/embed.js"
                    },
                    d = o.createElement("script");
                d.id = "revolut-checkout", d.src = c[r] || c.prod, d.async = !0, o.head.appendChild(d);
                var s = {
                    then: function(r, c) {
                        d.onload = function() {
                            r(e[t](n))
                        }, d.onerror = function() {
                            o.head.removeChild(d), c && c(new Error(t + " is failed to load"))
                        }
                    }
                };
                return "function" == typeof Promise ? Promise.resolve(s) : s
            }
        }(window, document, "RevolutCheckout");
    </script>
    <style>
        body {
            max-width: 30%;
            margin-left: auto;
            margin-right: auto;
        }

        @media screen and (max-width: 500px) {
            body {
                max-width: 70%;
            }
        }

        @media screen and (max-width: 700px) {
            body {
                max-width: 50%;
            }
        }

        #card-pay {
            border: none;
            height: 3rem;
            border-radius: 16px;
            white-space: nowrap;
            height: 3rem;
            width: 100%;
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: 16px;
            transition: background-color 300ms cubic-bezier(0.15, 0.5, 0.5, 1) 0s, color 300ms cubic-bezier(0.15, 0.5, 0.5, 1) 0s, opacity 300ms cubic-bezier(0.15, 0.5, 0.5, 1) 0s, box-shadow 200ms cubic-bezier(0.4, 0.3, 0.8, 0.6) 0s;
            color: rgb(255, 255, 255);
            background-color: rgb(25, 28, 31);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Helvetica, Arial, Arimo, sans-serif;
            font-weight: 500;
            font-size: 1rem;
            line-height: 1.375;
            letter-spacing: -0.00625em;
            text-align: center;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <button id="card-pay">Pay with Card</button>
    <div id="revolut-payment-request"></div>
    <div id="revolut-pay"></div>
    <script>
        let env = {
            production: 'prod',
            development: 'sandbox',
        }

        RevolutCheckout('<?= $id ?>', env['<?= ENVIRONMENT ?>']).then(function(RC) {
            const paymentRequest = RC.paymentRequest({
                target: document.getElementById('revolut-payment-request'),
                onSuccess() {
                    setResult('Paid')
                },
                onError(error) {
                    setResult(`Error: ${error.message}`)
                },
                // buttonStyle: { size: 'small', variant: 'light-outlined' },
            })

            paymentRequest.canMakePayment().then((method) => {
                if (method) {
                    paymentRequest.render()
                } else {
                    setResult('Not supported')
                    paymentRequest.destroy()
                }
            })

            RC.revolutPay({
                target: document.getElementById('revolut-pay'),
                phone: '<?= $invoiceObj->client->phonenumber ?>', // recommended
                onSuccess() {
                    location.href = '<?= site_url('revolut/confirmation/' . $invoice . '/' . $id) ?>';
                },
                onError(message) {
                    location.href = '<?= site_url('revolut/fail/' . $invoice) ?>';
                },
                onCancel() {
                    location.href = '<?= site_url('revolut/fail/' . $invoice) ?>';
                },
            });

            document.getElementById('card-pay').addEventListener('click', () => {
                RC.payWithPopup({
                    onSuccess() {
                        location.href = '<?= site_url('revolut/confirmation/' . $invoice . '/' . $id) ?>';
                    },
                    onError(message) {
                        location.href = '<?= site_url('revolut/fail/' . $invoice) ?>';
                    },
                    onCancel() {
                        location.href = '<?= site_url('revolut/fail/' . $invoice) ?>';
                    },
                });
            });
        });
    </script>
</body>

</html>