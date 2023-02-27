<?php

namespace Revolut\Library;

class Revolut
{
    /**
     * Production API endpoint
     * 
     * @var string $prod_endpoint
     */
    protected string $prod_endpoint = 'https://merchant.revolut.com';


    /**
     * Development API endpoint
     * 
     * @var string $dev_endpoint
     */
    protected string $dev_endpoint = 'https://sandbox-merchant.revolut.com';


    /**
     * Endpoint of the Revolut API
     * 
     * @var string $endpoint
     */
    public string $endpoint;


    /**
     * Webhook endpoint to handle the events
     * 
     * @var string $webhook_endpoint
     */
    public string $webhook_endpoint;


    /**
     * Endpoint prefix based on the version
     *
     * @var string $prefix
     */
    protected string $prefix = 'api/1.0';


    /**
     * CURL client class instance
     * 
     * @var mixed $client
     */
    protected $client = \GuzzleHttp\Client::class;


    /**
     * Public key to authorize the request
     * 
     * @var string $public_key
     */
    public string $public_key;


    /**
     * Secret key to authorize the request
     * 
     * @var string $secret_key
     */
    public string $secret_key;


    /**
     * CodeIgniter instance
     * 
     * @var mixed
     */
    protected $ci;


    public function __construct()
    {
        $this->endpoint = ENVIRONMENT == 'production' ? $this->prod_endpoint : $this->dev_endpoint;
        $this->webhook_endpoint = site_url('revolut/webhook_endpoint');

        $this->ci = &get_instance();
        $this->public_key = $this->ci->revolut_gateway->decryptSetting('api_publishable_key');
        $this->secret_key = $this->ci->revolut_gateway->decryptSetting('api_secret_key');
    }

    /**
     * Send the request to the endpoint
     * 
     * @param string $path
     * @param array|null $data
     * @param string $method
     * @return mixed|null
     */
    public function request(string $path, array $data = null, string $method = 'GET')
    {
        try {
            $requestData = [
                'headers' => [
                    'Authorization' => "Bearer $this->secret_key",
                    'Content-Type' => 'application/json'
                ]
            ];

            if ($method == 'POST' && $data) {
                $requestData['body'] = json_encode($data);
            }

            $response = (new $this->client)->request($method, $this->getEndpoint($path), $requestData);

            return json_decode($response->getBody()->getContents());
        } catch (\Throwable $th) {
            if (ENVIRONMENT == 'development') {
                throw $th;
            }
            
            if($th->getCode() == 401){
                show_error('Please check the API credentials of the payment gateway Revolut is incorrect.', 'Unauthorized', 'Unauthorized Action', 401);
            }
        }
    }

    /**
     * Get the API endpoint prepared
     * 
     * @param string $path
     * @return string
     */
    public function getEndpoint(string $path)
    {
        $pieces = [
            trim($this->endpoint, '/'),
            trim($this->prefix, '/'),
            trim($path, '/')
        ];

        return implode('/', $pieces);
    }

    /**
     * Update the webhook before making any transaction
     * 
     * @return true
     */
    public function updateWebhook(): bool
    {
        $webhooks = $this->retrieveWebhooks();

        $exists = array_filter($webhooks, function ($dt) {
            return $dt->url == $this->webhook_endpoint;
        });

        if (!count($exists)) {
            $this->setWebhook($this->webhook_endpoint);
        }

        return true;
    }

    /**
     * Set new webhook along with events
     * 
     * @param string $url
     * @param array $events
     * @return object
     */
    public function setWebhook(string $url, array $events = ['ORDER_COMPLETED', 'ORDER_AUTHORISED']): object
    {
        $data = $this->request('webhooks', [
            'url' => $url,
            'events' => $events
        ], 'POST');

        return $data;
    }

    /**
     * Get the list of all webhooks
     * 
     * @return array
     */
    public function retrieveWebhooks(): array
    {
        $data = $this->request('webhooks');

        return $data;
    }

    /**
     * Delete a webhook data
     * 
     * @param string $id
     * @return mixed
     */
    public function deleteWebhook(string $id)
    {
        $data = $this->request("webhooks/$id", null, 'DELETE');

        return $data;
    }

    /**
     * Create an order
     * 
     * @param int $amount The amount must be like (from $12.10 to 1210)
     * @param string $currency
     * @param string|null $email
     * @return mixed
     */
    public function createOrder(int $amount, string $currency = 'GBP', string $description = null)
    {
        $data = [
            'amount' => $amount,
            'currency' => $currency,
            'description' => $description
        ];

        $response = $this->request("orders", $data, 'POST');

        return $response;
    }
}
