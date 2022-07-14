<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller NeedAssessment and related models of it
 */
class NeedAssessment_diagnosis_test extends TestCase {
    
    protected $CI;
    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../../modules/sales/models/Need_assessment_model');
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
}
