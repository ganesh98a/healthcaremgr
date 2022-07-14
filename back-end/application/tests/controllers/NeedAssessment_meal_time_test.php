<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller NeedAssessment and related models of it
 */

require_once './_ci_phpunit_test/autoloader.php';

use PHPUnit\Framework\TestCase;

class NeedAssessmentMealTime_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/sales/models/Need_assessment_model');
        $this->CI->load->library('form_validation');
        $this->Need_assessment_model = $this->CI->Need_assessment_model;
        $this->basic_model = $this->CI->basic_model;
        $this->CI->db->trans_start(true);
    }

	public function tearDown(): void { 
        $this->CI->db->trans_complete(); 
    }

    /*
     * update need assesment meal time case 1
     */
    function test_edit_need_assessment_meal_time_case1(){
        $adminId = '20';
        $postdata = [
            "aids" => 0,
            "aids_desc" => "",
            "assistance_plan_requirement" => "Test",
            "mealtime_assistance_plan" => "2",
            "not_applicable" => "pranav",
            "physical_assistance" => "1",
            "physical_assistance_desc" => "Physical",
            "require_assistance_plan" => "2",
            "risk_aspiration" => "2",
            "risk_choking" => "1",
            "verbal_prompting" => "1",
            "verbal_prompting_desc" => "verbal",
        ];
        $details = $this->basic_model->get_row('need_assessment', array("MAX(id) AS last_id"));
        if($details->last_id)
        $postdata['need_assessment_id'] = $details->last_id;
        
        $output = $this->Need_assessment_model->save_mealtime_assisstance($postdata,$adminId);
        if($output) {
            $status = ['status'=> TRUE, 'msg'=>'Mealtime Updated successfully'];
        }
        return $this->assertTrue($status['status']);
    }
}