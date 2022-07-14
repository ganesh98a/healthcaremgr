<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller NeedAssessment and related models of it
 */

require_once './_ci_phpunit_test/autoloader.php';

use PHPUnit\Framework\TestCase;

class NeedAssessment_medication_test extends TestCase {
    
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
    function test_edit_need_assessment_medication1(){
        $adminId = '20';
        $postdata = [
            "crushed_oral" => 0,
            "crushed_via_peg" => 0,
            "full_assistance_and_verbal" => "2",
            "medication_administration" => "2",
            "medication_emergency" => "1",
            "medication_vitamins_counter" => "1",
            "not_applicable" => "0",
            "reduce_concern" => "1",
            "tablets_liquid_oral" => 0
        ];
        $details = $this->basic_model->get_row('need_assessment', array("MAX(id) AS last_id"));
        if($details->last_id)
        $postdata['need_assessment_id'] = $details->last_id;
        
        $output = $this->Need_assessment_model->save_medication($postdata,$adminId);
        if($output) {
            $status = ['status'=> TRUE, 'msg'=>'Medication Updated successfully'];
        }
        return $this->assertTrue($status['status']);
    }
}