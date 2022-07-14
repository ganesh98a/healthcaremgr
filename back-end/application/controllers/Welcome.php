<?php


defined('BASEPATH') OR exit('No direct script access allowed');

//class Master extends MX_Controller  
class Welcome extends CI_Controller {

    function __construct() {
        parent::__construct();

		$this->load->helper('email_template_helper');
        $this->load->model('member/Member_model');
        $this->load->model('finance/Finance_model');
        $this->load->model('Basic_model');
    }

    public function index() {
        send_mail_smtp('pranav.gajjar@ampion.com.au', 'test', 'test', $cc_email_address = null);
    }

    /**
     * CLI controller function to fetch the keypay employees' leaves and reflect them as
     * unavailability within HCM
     */
    public function get_keypay_employee_leaves() {
        $return = $this->Member_model->get_keypay_employee_leaves(1);
        if($return['status'] == true)
            print "Success";
        else {
            $error = !empty($return['error']) ? $return['error'] : '';
            $error .= !empty($return['msg']) ? $return['msg'] : '';
            print "Error: ".$error;
        }
    }

    /**
     * CLI controller function to fetch the timesheet line items from keypay and
     * marking relative timesheet line items as paid
     */
    public function get_paid_keypay_timesheets() {
        $return = $this->Finance_model->get_paid_keypay_timesheets(1);
        if($return['status'] == true)
            print "Success";
        else {
            $error = !empty($return['error']) ? $return['error'] : '';
            $error .= !empty($return['msg']) ? $return['msg'] : '';
            print "Error: ".$error;
        }
    }
}
