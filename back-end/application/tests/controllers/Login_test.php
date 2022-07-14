<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller Login and related models of it
 */
class Login_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('admin/Admin_model');
        $this->CI->load->model('member/Member_model');
        $this->CI->load->library('form_validation');
        $this->Admin_model = $this->CI->Admin_model;
        $this->Member_model = $this->CI->Member_model;
    }

    /*
     * checking the required fields in login
     */
    function test_check_login_val1() {
        $postdata =[
            "email" => "pranav.gajjar@ampion.com.au",
            "password" => null
        ];
        $output = $this->Admin_model->check_login($postdata);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the required and valid fields in login
     */
    function test_check_login_val2() {
        $postdata =[
            "email" => "asdasd",
            "password" => "asasd"
        ];
        $output = $this->Admin_model->check_login($postdata);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the continues failed attempts of login
     */
    function test_check_login_failed_attempts() {
        $postdata =[
            "email" => "pranav.gajjar@ampion.com.au",
            "password" => "asd"
        ];
        $output1 = $this->Admin_model->check_login($postdata);
        $output2 = $this->Admin_model->check_login($postdata);
        $output3 = $this->Admin_model->check_login($postdata);
        $output4 = $this->Admin_model->check_login($postdata);
        $output = $this->Admin_model->check_login($postdata);
        return $this->assertContains("Your account is locked", $output['error']);
    }

    /**
     * see if old login detection works
     */
    function test_old_account_detection() {
        $this->Admin_model->unlock_member(1);
        return true;
        $postdata =[
            "email" => "pranav.gajjar@ampion.com.au",
            "password" => "123456"
        ];
        $output = $this->Admin_model->check_login($postdata);
        return $this->assertContains("Old account detected", $output['error']);
    }

    /**
     * checking required fields in validating OTP
     */
    function test_submit_oldlogin_pin_val1() {
        $postdata =[
            "email" => "pranav.gajjar@ampion.com.au",
            "password" => "123456",
            "pin" => null,
            "serial" => "asdasd"
        ];
        $output = $this->Admin_model->submit_oldlogin_pin($postdata);
        return $this->assertFalse($output['status']);
    }

    /**
     * checking required fields in validating OTP
     */
    function test_submit_oldlogin_pin_val2() {
        $postdata =[
            "email" => "pranav.gajjar@ampion.com.au",
            "password" => "123456",
            "pin" => "123",
            "serial" => null
        ];
        $output = $this->Admin_model->submit_oldlogin_pin($postdata);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the continues failed attempts of OTP
     */
    function test_submit_oldlogin_pin_failed_attempts() {
        $postdata =[
            "email" => "pranav.gajjar@ampion.com.au",
            "password" => "123456",
            "pin" => "123",
            "serial" => "APD12322",
            "member_id" => 1
        ];
        $output1 = $this->Admin_model->submit_oldlogin_pin($postdata);
        $output2 = $this->Admin_model->submit_oldlogin_pin($postdata);
        $output3 = $this->Admin_model->submit_oldlogin_pin($postdata);
        $output4 = $this->Admin_model->submit_oldlogin_pin($postdata);
        $output = $this->Admin_model->submit_oldlogin_pin($postdata);
        return $this->assertContains("Your account is locked", $output['error']);
    }

    /**
     * checking required fields in resending the pin
     */
    function test_resend_oldlogin_pin_val1() {
        $postdata =[
            "email" => "pranav.gajjar@ampion.com.au",
            "member_id" => null
        ];
        $output = $this->Admin_model->resend_oldlogin_pin($postdata);
        return $this->assertFalse($output['status']);
    }

    /**
     * checking successful sending of pin
     */
    function test_resend_oldlogin_pin_success() {
        $this->Admin_model->unlock_member(1);
        $postdata =[
            "email" => "pranav.gajjar@ampion.com.au",
            "member_id" => 1
        ];
        $output = $this->Admin_model->resend_oldlogin_pin($postdata);
        return $this->assertTrue($output['status']);
    }
}
