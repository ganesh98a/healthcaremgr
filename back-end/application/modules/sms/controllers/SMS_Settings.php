<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class SMS_Settings extends MX_Controller {
	 function __construct() {
        parent::__construct();
        $this->load->helper('i_pad');
    }

    /** 
      * Change default msg type as promotiona;
      */
    public function setDefaultMsgAsPromotional() {
    	api_request_handler();
        $this->load->library('AmazonSms');

        # set sms detail
        $this->amazonsms->setMessage($msg_w_pref);
        $this->amazonsms->setPhoneNumber($pho_w_pref);
        $this->amazonsms->setDefaultSMSType($this->amazonsms->sms_type_promo);
        # publish sms directly
        $result = $this->amazonsms->setSMSAttributes();
        pr($result);
    }

    /** 
      * Change default msg type as transactional
      */
    public function setDefaultMsgAsTransactional() {
    	api_request_handler();
        $this->load->library('AmazonSms');

        # set sms detail
        $this->amazonsms->setMessage($msg_w_pref);
        $this->amazonsms->setPhoneNumber($pho_w_pref);
        $this->amazonsms->setDefaultSMSType($this->amazonsms->sms_type_trans);
        # publish sms directly
        $result = $this->amazonsms->setSMSAttributes();
        pr($result);
    }

    /** 
      * Check SMS
      */
    public function checkSms() {
    	api_request_handler();
        $this->load->library('AmazonSms');
        $msg_w_pref = "Hi, This Msg Sent by Gk. With Prefix";
        $msg_wo_pref = "Hi, This Msg Sent by Gk. Without Prefix";
        $pho_w_pref = '+610497618052';
        $pho_wo_pref = '0497618052';

        # set sms detail
        $this->amazonsms->setMessage($msg_w_pref);
        $this->amazonsms->setPhoneNumber($pho_w_pref);
        # publish sms directly
        $result = $this->amazonsms->publishSms();
        pr($result);
    }

    /** 
      * Check SMS
      */
    public function checkSmsWithInput() {
    	$request = api_request_handler();

        $phone_no = $request->phone_no ?? '';
        $msg = $request->msg ?? '';

        $this->load->library('AmazonSms');

        # set sms detail
        $this->amazonsms->setMessage($msg);
        $this->amazonsms->setPhoneNumber($phone_no);
        # publish sms directly
        $result = $this->amazonsms->publishSms();
        pr($result);
    }

}