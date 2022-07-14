<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once './_ci_phpunit_test/autoloader.php';
use PHPUnit\Framework\TestCase;

/**
 * Class to test the controller Templates and related models of it
 */
class OnlineAssessment_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/recruitment/models/Online_assessment_model');
        $this->CI->load->library('form_validation');
        $this->Online_assessment_model = $this->CI->Online_assessment_model;
        $this->basic_model = $this->CI->basic_model;
    }

    /**
     * Get detail of template with question and answer options
     * return json
     */
    function test_get_oa_template_by_uid() {
        $reqData = json_decode('{"uuid":"43e50f3c-a336-0349-2c5c-4054"}', true);
        $reqData = (object) $reqData;
        $response = $this->Online_assessment_model->get_oa_template_by_uid($reqData);
        return $this->assertTrue($response['status']);
    }

    /**
     * Get detail of template with question and answer options
     * return json
     */
    function test_get_oa_template_by_uid_case_fail() {
        $reqData = json_decode('{"uuid":""}', true);
        $reqData = (object) $reqData;
        $response = $this->Online_assessment_model->get_oa_template_by_uid($reqData);
        return $this->assertFalse($response['status']);
    }

    /**
     * Get detail of template with question and answer options
     * return json
     */
    function test_get_exisiting_oa_assessment_by_status() {
        
        $applicant_id = $this->basic_model->get_row('recruitment_applicant', array("MAX(id) AS last_id"));
        $application_id = $this->basic_model->get_row('recruitment_applicant_applied_application', array("MAX(id) AS last_id"));
        $job_id = $this->basic_model->get_row('recruitment_job_position', array("MAX(id) AS last_id"));

        $reqData = [
            'applicant_id' => $applicant_id->last_id ?? NULL,
            'application_id' => $application_id->last_id ?? NULL,
            'job_id' => $job_id->last_id ?? NULL,           
        ];

        $reqData = (object) $reqData;
        $response = $this->Online_assessment_model->get_exisiting_oa_assessment_by_status($reqData);
        return $this->assertFalse($response['status']);
    }

}
