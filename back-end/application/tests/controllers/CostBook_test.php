<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller Account and related models of it
 */
class CostBook_test extends TestCase {

    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/sales/models/Account_model');
        $this->CI->load->library('form_validation');
        $this->Account_model = $this->CI->Account_model;
    }

    /**
     * Get cost book options - success
     */
    public function test_get_cost_book_options() {
        $postdata =[
            "org_id" => 1,
            "cost_code" => 2,
            "service_area" => 1,
            "site_discount" => 0
        ];
        $output = $this->Account_model->get_cost_book_options($postdata);
        # assertGreaterThanOrEqual 0 with data
        return $this->assertGreaterThanOrEqual(0, count($output));
    }
    
    /**
     * Get cost book options - fail
     */
    public function test_get_cost_book_options_fail() {
        $postdata =[
            "org_id" => 0,
            "cost_code" => 2,
            "service_area" => 1,
            "site_discount" => 0
        ];
        $output = $this->Account_model->get_cost_book_options($postdata);
        # assertGreaterThanOrEqual 0 with data
        return $this->assertEquals(0, count($output));
    }
}