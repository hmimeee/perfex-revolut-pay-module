<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Revolut extends App_Controller
{
    /**
     * Payment page render and pay
     * 
     * @param mixed $invoice
     * @param mixed $id
     * @return void
     */
    public function pay($invoice, $id)
    {
        $ci = &get_instance();
        $ci->load->model('invoices_model');
        $invoiceObj = $ci->invoices_model->get($invoice);

        // echo '<pre>';
        // print_r($invoiceObj->client->phonenumber);
        // exit;

        $this->load->view('pay', [
            'invoiceObj' => $invoiceObj,
            'invoice' => $invoice,
            'id' => $id
        ]);
    }

    /**
     * Payment confirmation action
     * 
     * @param mixed $invoice
     * @param mixed $id
     * @return void
     */
    public function confirmation($invoice, $id)
    {
        try {
            $ci = &get_instance();
            $ci->load->model('invoices_model');
            $invoice = $ci->invoices_model->get($invoice);

            if ($invoice->token != $id)
                show_error('The request is invalid or something bad heppened', 'Invalid request', 'Something went wrong', 400);

            $ci->db->where('id', $invoice->id);
            $ci->db->update(db_prefix() . 'invoices', [
                'status' => 2
            ]);

            set_alert('success', _l('invoice_payment_recorded'));

            redirect(site_url('invoice/' . $invoice->id . '/' . $invoice->hash));
        } catch (\Throwable $th) {
            show_error('The request is invalid or something bad heppened', 'Invalid request', 'Something went wrong', 400);
        }
    }

    /**
     * Payment cancel or error action
     * 
     * @param mixed $invoice
     * @param mixed $id
     * @return void
     */
    public function fail($invoice)
    {
        try {
            $ci = &get_instance();
            $ci->load->model('invoices_model');
            $invoice = $ci->invoices_model->get($invoice);

            set_alert('error', _l('invoice_payment_record_failed'));

            redirect(site_url('invoice/' . $invoice->id . '/' . $invoice->hash));
        } catch (\Throwable $th) {
            show_error('The request is invalid or something bad heppened', 'Invalid request', 'Something went wrong', 400);
        }
    }

    /**
     * The application Stripe webhook endpoint
     *
     * @return mixed
     */
    public function webhook_endpoint()
    {
        // $data = $this->input->post();

        // $dt['post'] = $_POST;
        // $dt['get'] = $_GET;
        // file_put_contents('revolut.txt', json_encode($dt));

        // if (isset($data['event']) && $data['event'] == 'ORDER_COMPLETED') {
        //     $this->ci->db->where('token', $data['order_id']);
        //     $this->ci->db->update(db_prefix() . 'invoices', [
        //         'status' => 2
        //     ]);
        // }

        // return true;
    }
}
