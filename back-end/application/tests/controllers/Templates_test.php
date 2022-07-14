<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller Templates and related models of it
 */
class Templates_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/imail/models/Templates_model');
        $this->CI->load->library('form_validation');
        $this->Templates_model = $this->CI->Templates_model;
    }

    /*
     * checking the mandatory organisation id while adding a new org member
     */
    function test_create_template() {
        $random_name = "test_".rand(1, 999999);
        $postdata = json_decode('{"loading":true,"is_edit":false,"name":"'.$random_name.'","content":"<p>test</p>","attachments":[],"existing_attachment":[],"from":"from test","subject":"test subject","folder":"private", "description":"test description"}', true);
        $output = $this->Templates_model->create($postdata, 1);
        return $this->assertTrue($output['status']);
    }
}
