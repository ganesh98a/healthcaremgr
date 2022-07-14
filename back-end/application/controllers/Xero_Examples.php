<?php

require APPPATH . '../vendor/autoload.php';

use XeroPHP\Models\Accounting;

class Xero_Examples extends CI_Controller
{
    /**
     * Create a new xero connection
     * configuration found in application\config\config.php
     * Uses XERO API wrapper for php: https://github.com/calcinai/xero-php
     */
    public function __construct()
    {
        parent::__construct();
        
        $config =& get_config();
        $this->xero = new \XeroPHP\Application\PrivateApplication($config['xero']);
    }

    /**
     * XERO integration for creating a contact
     * If any of the listed resources do not have a XERO GUID then they should first be
     * created as a contact before invoices are pushed
     * - Participants
     * - Sites
     * - Houses
     * - Organisations
     * See https://developer.xero.com/documentation/api/contacts#POST for list of properties to set
     */
    public function create_contact()
    {
        $contact = new Accounting\Contact($this->xero);
        //$contact->save();
        //$xero_guid = $contact->getContactID();
    }

    public function get_contact()
    {
        $guid = "4bb77692-42d4-4565-85a0-8849eb85e039";
        $contact = $this->xero->loadByGUID(Accounting\Contact::class, $guid);

        // Expected 7-Eleven
        echo $contact->getName() . PHP_EOL;
    }

    public function update_contact()
    {

    }

    public function get_invoices()
    {

    }

    public function create_invoice()
    {

    }

    public function update_invoice()
    {
        
    }

    public function get_payments()
    {
        $payments = $this->xero->load(Accounting\Payment::class)->execute();

        foreach ($payments as $payment) {
            echo $payment->getInvoice()->getInvoiceID() . PHP_EOL;
        }
    }

    public function create_repeating_invoices()
    {

    }

    public function update_repeating_invoices()
    {

    }
}