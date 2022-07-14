<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller NeedAssessment and related models of it
 */

require_once './_ci_phpunit_test/autoloader.php';

use PHPUnit\Framework\TestCase;

class NeedAssessment_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/sales/models/Need_assessment_model');
        $this->CI->load->library('form_validation');
        $this->Need_assessment_model = $this->CI->Need_assessment_model;
        $this->CI->db->trans_start(true);
    }

	public function tearDown(): void { 
        $this->CI->db->trans_complete(); 
    }

	/*
     * testing that diagnosis is saved properly
     */
    function test_success_save_diagnosis() {

        $postdata = json_decode('{"0":{"id":"17585008_Atrophy of teti","label":"Atrophy of testis","conceptId":"17585008_Atrophy of teti","incr_id_diagnosis":"26","selected":"1","search_term":"Atrophic testicle","impact_on_participant":"3","plan_end_date":"2021-02-16","support_level":"1","current_plan":"1","errors":[],"primary_disability":"1"},"1":{"id":"10629009_Tetany","label":"Tetany","conceptId":"10629009_Tetany","incr_id_diagnosis":"27","selected":"1","search_term":"Tetany","impact_on_participant":"0","plan_end_date":"2021-02-24","support_level":"0","current_plan":"1","errors":[],"primary_disability":"1"},"need_assessment_id":"4"}', true);
        $output = $this->Need_assessment_model->save_diagnosis($postdata, 11, 11);
        return $this->assertTrue($output);
    }

    /*
     * update need assesment case 1
     */
    function test_edit_need_assessment_case1(){
        $adminId = '20';
        $user_id = '20';
        $reqData = '{
            
            "rows": [
                {
                    "conceptId": "126900000_Neoplam of teti",
                    "current_plan": "2",
                    "errors": {},
                    "id": "126900000_Neoplam of teti",
                    "impact_on_participant": "0",
                    "incr_id_diagnosis": "26",
                    "label": "Neoplasm of testis",
                    "plan_end_date": null,
                    "search_term": "Tumor of testis",
                    "selected": "1",
                    "support_level": "1",
                    "primary_disability": "1"
                }
            ]
        }';        
        $data =$reqData;
        $data = json_decode($data);
        if (!empty($data)) {
            $data = obj_to_arr($data->rows);
                $data['need_assessment_id'] = 4;
                $needAssessmentId = $this->Need_assessment_model->save_diagnosis($data, $adminId,$user_id);
                if ($needAssessmentId) {
                    $return = ['status'=>true, 'msg'=>'Diagnosis saved successfully'];
                } else {
                    $return = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                } 
        } else {
            // If requested data is empty or null
            $return = ['status' => false, 'error' => 'Requested data is null']; 
        }
         $status = $return['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $return['msg'];
        } else {
            $status_msg = $return['error'];
        }
        
        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

    /*
     * update need assesment case 1
     */
    function test_edit_need_assessment_faulure_case1(){
        $adminId = '20';
        $user_id = '20';
        $reqData = '{
            
            "rows": [
                {
                    "conceptId": "126900000_Neoplam of teti",
                    "current_plan": "2",
                    "errors": {},
                    "id": "126900000_Neoplam of teti",
                    "impact_on_participant": "0",
                    "label": "Neoplasm of testis",
                    "plan_end_date": null,
                    "search_term": "Tumor of testis",
                    "selected": "1",
                    "support_level": "1"
                },
                {"need_assessment_id":"4"}
            ]
        }';        
        $data =$reqData;
        $data = json_decode($data);
        if (!empty($data)) {
            $data = obj_to_arr($data->rows);
            $data['need_assessment_id'] = 4;
            $needAssessmentId = $this->Need_assessment_model->save_diagnosis($data, $adminId,$user_id);
            if ($needAssessmentId) {
                $return = ['status'=>true, 'msg'=>'Diagnosis saved successfully'];
            } else {
                $return = ['status' => false, 'error' => system_msgs('something_went_wrong')];
            } 
        } else {
            // If requested data is empty or null
            $return = ['status' => false, 'error' => 'Requested data is null']; 
        }
         $status = $return['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $return['msg'];
        } else {
            $status_msg = $return['error'];
        }
        
        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }
}