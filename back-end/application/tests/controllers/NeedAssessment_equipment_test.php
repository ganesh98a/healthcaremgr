<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller NeedAssessment and related models of it
 */
class NeedAssessment_equipment_test extends TestCase {
    
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
     * testing that equipment is saved properly
     */
    function test_success_save_equipment() {
        $orderby = "id";
        $direction = "desc";
        $record = $this->CI->load->basic_model->get_row_where_orderby($table_name = 'need_assessment', $columns = array('id'), '', $orderby, $direction);
        $na_id = 1;
        if (!empty($record)) {
            $na_id = $record->id;
        }
        $postdata = json_decode('{"loading":false,"other_label":"","model_brand":"","daily_safety_aids_description":"","hoist_sling_description":"","other_description":"","need_assessment_id":"'.$na_id.'","not_applicable":"2","walking_stick":"0","wheel_chair":"0","shower_chair":"0","transfer_aides":"1","daily_safety_aids":"0","walking_frame":"0","type":"0","weight":"0","toilet_chair":"0","hoist_sling":"0","other":"0","transfer_aides_description":""}', true);
        $output = $this->Need_assessment_model->save_equipment_assisstance($postdata, 11);
        return $this->assertGreaterThanOrEqual(1,$output);
    }
}
