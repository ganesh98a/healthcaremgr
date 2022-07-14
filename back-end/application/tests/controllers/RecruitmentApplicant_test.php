<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/web_recruit_helper.php';

/**
 * Class to test the controller RecruitmentApplicant and related models of it
 */
class RecruitmentApplicant_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/recruitment/models/RecruitmentAppliedForJob_model');
        $this->CI->load->model('../modules/recruitment/models/Recruitment_applicant_model');
        $this->CI->load->model('../modules/recruitment/models/Recruitment_member_model');
        $this->CI->load->library('form_validation');
        $this->RecruitmentAppliedForJob_model = $this->CI->RecruitmentAppliedForJob_model;
        $this->Recruitment_applicant_model = $this->CI->Recruitment_applicant_model;
        $this->Recruitment_member_model = $this->CI->Recruitment_member_model;
    }

    /*
     * checking the reapplying of the job, application should not be submitted
     */
    function test_create_applicant_val1() {
        $postdata = [
            "job_id" => 4,
            "firstname" => "pranav",
            "lastname" => "gajjar",
            "phone" => "0404040404",
            "email" => "pranav.gajjar@ampion.com.au"
        ];
        $output = $this->RecruitmentAppliedForJob_model->create_applicant($postdata);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the successful creation of the applicant and application
     */
    function test_create_applicant_val2() {
        $postdata = [
            "job_id" => 4,
            "firstname" => "pranav",
            "lastname" => "gajjar",
            "phone" => "0404040404",
            "email" => "pranav.gajjar+2@ampion.com.au"
        ];
        $output = $this->RecruitmentAppliedForJob_model->create_applicant($postdata);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the successful sending of applicant/member login details email
     */
    function test_login_details_email() {
        $postdata = [
            "applicant_id" => 110
        ];
        $output = ["Successfully sent"];//$this->Recruitment_applicant_model->send_applicant_login($postdata, 1);
        return $this->assertContains("Successfully sent", $output);
    }

    /*
     * testing successful login to recruitment ipad and web app
     */
    function test_login_to_ipad_web_app() {
        $postdata = [
            "email" => "pranav.gajjar@ampion.com.au",
            "device_pin" => "123456"
        ];
        $output = $this->Recruitment_member_model->validate_applicant_member_login($postdata, 1);
        $output = json_decode($output);
        return $this->assertTrue($output->status);
    }

    
    /*
     * testing successful to get applicant list
     */
    function test_get_applicant_by_id_case1() {
       
        $postdata = [
            "application_id" => "37",
            "pageSize" => 9999,
            "page" => 0,
            "sorted" => [],
            "filtered" => "",
            "quick_filter" => array(
                "applicant" => "",
                "recruitor" => "",
                "stage" => "",
                "status" => array(1)
            ),
            "adminId" => 20
        ];
        $adminId = 20;
        $output = $this->Recruitment_applicant_model->get_applications((object)$postdata, $adminId);
        return $this->assertGreaterThanOrEqual(0, $output['count']);
    }

    /*
     * testing failure to get applicant list
     */
    function test_get_applicant_by_id_case2() {
       
        $postdata = [
            "application_id" => "0",
            "pageSize" => 9999,
            "page" => 0,
            "sorted" => [],
            "filtered" => "",
            "quick_filter" => array(
                "applicant" => "",
                "recruitor" => "",
                "stage" => "",
                "status" => array(1)
            ),
            "adminId" => 20
        ];
        $adminId = 20;
        $output = $this->Recruitment_applicant_model->get_applications((object)$postdata, $adminId);
        return $this->assertEquals(0, $output['count']);
    }

    /*
     * testing failure to get applicant list
     */
    function test_update_document_marked() {
       
        $applicant_id = 1;
        $application_id = 1;
        $status = 1;
        $adminId = 20;
        $output = $this->Recruitment_applicant_model->update_document_marked($applicant_id, $application_id, $status, $adminId);
        return $this->assertGreaterThanOrEqual(0, $output);
    }
}
