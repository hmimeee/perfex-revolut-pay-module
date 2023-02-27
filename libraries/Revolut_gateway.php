<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Revolut\Library\Revolut;

class Revolut_gateway extends App_gateway
{
    public function __construct()
    {
        /**
         * Call App_gateway __construct function
         */
        parent::__construct();

        /**
         * Gateway unique id - REQUIRED
         * 
         * * The ID must be alphanumeric
         * * The filename (Revolut_gateway.php) and the class name must contain the id as ID_gateway
         * * In this case our id is "revolut"
         * * Filename will be Revolut_gateway.php (first letter is uppercase)
         * * Class name will be Revolut_gateway (first letter is uppercase)
         */
        $this->setId('revolut');

        /**
         * REQUIRED
         * Gateway name
         */
        $this->setName('Revolut');

        /**
         * Add gateway settings
         * You can add other settings here 
         * to fit for your gateway requirements
         *
         * Currently only 3 field types are accepted for gateway
         *
         * 'type'=>'yes_no'
         * 'type'=>'input'
         * 'type'=>'textarea'
         *
         */
        $this->setSettings(array(
            array(
                'name' => 'api_secret_key',
                'encrypted' => true,
                'label' => 'Secret Key',
                'type' => 'input',
            ),
            array(
                'name' => 'api_publishable_key',
                'encrypted' => true,
                'label' => 'Public Key',
                'type' => 'input'
            ),
            [
                'name'          => 'payment_description',
                'label'         => 'Payment Description',
                'type'          => 'textarea',
                'default_value' => 'Payment for Invoice {invoice_number}',
            ],
            array(
                'name' => 'currencies',
                'label' => 'settings_paymentmethod_currencies',
                'default_value' => 'USD,CAD'
            ),
        ));
    }

    /**
     * Each time a customer click PAY NOW button on the invoice HTML area, the script will process the payment via this function.
     * You can show forms here, redirect to gateway website, redirect to Codeigniter controller etc..
     * @param  array $data - Contains the total amount to pay and the invoice information
     * @return mixed
     */
    public function process_payment($data)
    {
        $revolut = new Revolut();
        
        $description = str_replace('{invoice_number}', format_invoice_number($data['invoiceid']), $this->getSetting('payment_description'));

        $amount = strcasecmp($data['invoice']->currency_name, 'JPY') == 0 ? intval($data['amount']) : $data['amount'] * 100;

        $currency = $data['invoice']->currency_name;

        //Create the order
        $order = $revolut->createOrder($amount, $currency, $description);

        //Store order ID in the token field
        $this->ci->db->where('id', $data['invoice']->id);
        $this->ci->db->update(db_prefix() . 'invoices', [
            'token' => $order->public_id,
        ]);

        if(isset($order->public_id)) {
            redirect($order->checkout_url);
            // redirect('revolut/pay/'.$data['invoiceid'].'/'.$order->public_id);
            exit;
        }
        
        throw new Exception("Error processing payment", 402);
    }
}
