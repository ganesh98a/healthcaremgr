<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller  GoalPortal and related models of it
 */

require_once './_ci_phpunit_test/autoloader.php';

use PHPUnit\Framework\TestCase;

class GoalPortal_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/item/models/Goal_model');
        $this->CI->load->library('form_validation');
        $this->Goal_model = $this->CI->Goal_model;
        $this->basic_model = $this->CI->basic_model;
        $this->CI->db->trans_start(true);
    }

	public function tearDown(): void { 
        $this->CI->db->trans_complete(); 
    }

    /*
     * update goal list by participant case 1
     */
    function test_get_all_goals_list_by_participant_case1(){
        // Set data in libray for validation
        $reqData = '
        {
            "participant_id": 58
        }
        ';
        $reqData = json_decode($reqData, true);

        $reqData = (object) $reqData;

        if (empty($reqData->participant_id)) {
            $result = ['status' => false, 'error' => "Missing ID"];
        }

        $details = $this->basic_model->get_row('shift', ["MAX(account_id) AS last_participant_id"],["account_type"=>1]);
        if($details->last_participant_id){
            $reqData->participant_id = $details->last_participant_id;
        }

        # Get roster details        
        $result = $this->Goal_model->get_all_goals_list_by_participant($reqData);
        return $this->assertTrue($result['status']);
    }


    /*
     * update goals details by shift and participant id
     */
    function get_all_goals_and_shift_by_participant_id_case2(){
        // Set data in libray for validation
        $reqData = '
        {
            "account_id": 58,
            "goal_id":"all",
            "selected_date_type":"current_week",
            "start_end_date":"2021-06-06,2021-06-11"
        }
        ';
        $reqData = json_decode($reqData, true);

        $reqData = (object) $reqData;

        if (empty($reqData->account_id)) {
            $result = ['status' => false, 'error' => "Missing ID"];
        }

        $details = $this->basic_model->get_row('shift', ["MAX(account_id) AS last_participant_id"],["account_type"=>1]);
        if($details->last_participant_id){
            $reqData->account_id = $details->last_participant_id;
        }

        # Get roster details        
        $result = $this->Goal_model->get_all_goals_and_shift_by_participant_id($reqData);
        return $this->assertTrue($result['status']);
    }
}