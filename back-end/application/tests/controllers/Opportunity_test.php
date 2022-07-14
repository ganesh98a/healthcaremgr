<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller MemberDashboard and related models of it
 */
class Opportunity_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../../modules/sales/models/OpportunityItemHistory_model');
        $this->CI->load->library('form_validation');
        $this->OpportunityItemHistory_model = $this->CI->OpportunityItemHistory_model;
    }

    /*
     * checking the history is created on on adding an item in opportunity
     */
    function test_create_history_on_item_added() {
        $adminId = 11;
        $data = [[
            'amount' => "1.00",
            'amount_editable' => "1",
            'id' => "3",
            'incr_id_opportunity_items' => "22",
            'line_item_name' => "le item",
            'line_item_number' => "l3",
            'oncall_provided' => "Yes",
            'qty' => "12",
            'qty_editable' => "1",
            'selected' => true,
            'opportunity_id' => 5
        ]];
        $item_one = [
            'incr_id_opportunity_items' => 22,
            'line_item_id' => 3,
            'amount' => 1.00,
            'qty' => 12
        ];
        $item_two = [
            'incr_id_opportunity_items' => 30,
            'line_item_id' => 4,
            'amount' => 4.00,
            'qty' => 1
        ];
        $item_one = (object) $item_one;
        $item_two = (object) $item_two;
        $opp_items = [$item_one, $item_two];
        $output = $this->OpportunityItemHistory_model->updateHistory($opp_items, $data, 5, $adminId);
        $this->assertTrue($output);
    }
    
}
