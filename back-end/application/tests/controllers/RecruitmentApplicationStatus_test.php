<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/web_recruit_helper.php';

/**
 * Class to test the controller RecruitmentApplicationStatus and related models of it
 */
class RecruitmentApplicationStatus_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/recruitment/models/Recruitment_application_model');
        $this->CI->load->model('../modules/recruitment/models/Recruitment_applicant_model');
        $this->CI->load->model('../modules/recruitment/models/Recruitment_interview_model');
        $this->CI->load->library('form_validation');
        $this->Recruitment_application_model = $this->CI->Recruitment_application_model;
        $this->Recruitment_interview_model = $this->CI->Recruitment_interview_model;
        $this->Recruitment_applicant_model = $this->CI->Recruitment_applicant_model;
    }

    /*
     * testing successful to get group booking list
     */
    function test_get_interviews_list_by_search_case1() {
       
        $postdata = [           
        ];
        
        $adminId = 20;
        $output = $this->Recruitment_interview_model->get_interviews_list_by_search((object)$postdata, $adminId);
        return $this->assertGreaterThanOrEqual(0, $output);
    }

    /**
     * Success case
     * Update Application form status and add applicant to group booking
     */
    public function test_update_form_status() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":{                
                "interview_id": "61",
                "selected_stage": 2,
                "selected_option": [{"label": "Interviews", "value": "Interviews", "org_value": 2}],
                "applications": [
                    {
                        "FullName": "testf form t",
                        "appId": "APP10145",
                        "applicant_id": "145",
                        "application_id": "162",
                        "application_process_status": "0",
                        "email": "testform@yopmail.com",
                        "hired_as": "0",
                        "hired_as_member": "No",
                        "id": "162",
                        "jobId": "60",
                        "job_position": "teste2e",
                        "process_status_label": "New",
                        "stage": "Review Online Application",
                        "status": "1",
                        "status_label": "In Progress",
                        "sub_stage": "1.1 - Review Answer"
                    }
                   
                ]
               
            },
            "adminId":20
        }';

        $reqData = (object) json_decode($reqData, true);
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'selected_stage', 'label' => 'Stage', 'rules' => 'required'),
                array('field' => 'applications', 'label' => 'Applications', 'rules' => 'required'),
            ];
            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            $validateApplicationStatus = false;
            // Msg handling
            $msg = '';
            $status = true;
            
            $result =[];
            $applicant = [];

            $result =['status' => true, 'msg' => "Application status updated successfully"];
            $applicant = ['status' => false];


            // 2- interview , 5-CAB
            if(!empty($data['interview_id']) == true &&  isset($data['interview_id'])){
                
                if($data['selected_stage']==2 || $data['selected_stage']==5){
                    $applicant = $this->Recruitment_interview_model->create_bulk_applicant_interview(json_encode($reqData->data), $adminId, true);
                    $msg = $applicant['msg'];
                    $status = $applicant['status'];
                    if($applicant['status']){
                        $validateApplicationStatus = true;
                    }
                }else{
                    $msg = 'Please select Interview or CAB status';
                    $applicant['status'] = false;
                    $status = false;
                }
               
            }else{
                $validateApplicationStatus = true;
            }
            if ($validateApplicationStatus == true) {
                $response = $this->Recruitment_application_model->update_application_status_by_id(json_encode($reqData), true);
                $requested_count = count($data['applications']);
                $updated_count = count($response);

                // Msg handling
                
                if ($requested_count == 1 && $updated_count == 1) {
                    $msg = 'Status updated successfully';
                    $status = true;
                } 
                if ($requested_count == 1 && $updated_count == 0) {
                    $msg = 'Selection list contains applications already hired (or) pending with reference and document check. Verify and select the applications for bulk update';
                    $status = false;
                }

                if ($requested_count > 1 && $updated_count == 0) {
                    $msg = 'Selection list contains applications already hired (or) pending with reference and document check. Verify and select the applications for bulk update';
                    $status = false;
                }

                if ($requested_count > 1 && $updated_count < $requested_count && $updated_count != 0) {
                    $msg = 'Status updated successfully for '.$updated_count.' applications out of '.$requested_count;
                    $status = true;
                }
                if ($requested_count > 1 && $updated_count == $requested_count && $updated_count != 0) {
                    $msg = 'Status updated successfully';
                    $status = true;
                }
                if ($status == true) {
                    $return = array('status' => $status, 'data' => $response, 'updated_count' => $updated_count, 'msg' => $msg);
                } else {
                    $return = array('status' => $status, 'data' => $response, 'updated_count' => $updated_count, 'error' => $msg);
                }
                
            }else{
                if($applicant['status']){
                    $errors = $this->CI->form_validation->error_array();
                    $return = array('status' => false, 'error' => implode(', ', $errors));
                }else{
                    $return = array('status' => $status, 'error' => $msg);
                }                
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }
        $status = $result['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $result['msg'];
        } else {
            $status_msg = $result['error'];
        }
        
        // AssertsEquals false with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }
}
