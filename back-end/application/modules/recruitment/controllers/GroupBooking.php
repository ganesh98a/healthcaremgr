<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read \Recruitment_applicant_model $Recruitment_applicant_model
 * @property-read \Recruitment_applicant_stages_model $stage_model
 * @package
 */
class GroupBooking extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('Form_validation');
        $this->load->model('recruitment/Group_booking_model');
        $this->load->model('recruitment/Recruitment_interview_model');
        $this->load->library('Asynclibrary');
    }

    private function sendResponse($data, $succes_msg = '') {
        if ($succes_msg) {
            $response = ['status' => true, 'data' => $data, 'msg' => $succes_msg];
        } else {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        }
        echo json_encode($response);
        exit();
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # HCM- 3485, adding all requests to backend in a log file
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
        
        
    }

    function get_applicants_for_group_booking() {
        $reqData = request_handler('access_recruitment');        
        if (!empty($reqData->data)) {
            $response = $this->Group_booking_model->get_applicants_for_group_booking($reqData->data, $reqData->adminId);
            echo json_encode($response);
        }
    }

    /**
     * create and update applicant interview
     */
    public function add_group_booking_applicants() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $error = false;
        if (!empty($reqData)) {
            if (is_array($reqData->data->selected_applicants)) {
                foreach($reqData->data->selected_applicants as $data) {
                    $data = (array) $data;
                    // Validation rules set
                    $validation_rules = [
                        array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required'),
                        array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required'),
                        array('field' => 'job_id', 'label' => 'Job Id', 'rules' => 'required'),
                        array('field' => 'interview_id', 'label' => 'Interview Id', 'rules' => 'required'),
                    ];
                    // Set data in libray for validation
                    $this->form_validation->set_data($data);
                    // Set validation rule
                    $this->form_validation->set_rules($validation_rules);
                    // Check data is valid or not
                    if ($this->form_validation->run()) {

                        $this->load->model('Basic_model');
                        // create interview applicant model
                        $check_applicant = $this->Recruitment_interview_model->check_applicant_interview_exists($data, 'create');
                        if( $check_applicant['status']){
                            $error = true;
                            $response[] = ['status' => false, 'error' => $check_applicant['msg'] ];
                        }else{
                        $interview_applicant = $this->Recruitment_interview_model->create_update_applicant_interview($data, $adminId);
                        // According to that interview applicant will be created
                            if ($interview_applicant['status'] == true) {
                                /**
                                 * Create logs. it will represent the user action they have made.
                                 */
                                if($data['interview_applicant_id']){
                                    $msg_title =  $data['interview_applicant_id'] . " applicant updated for Group Booking";
                                }else{
                                    $msg_title = $interview_applicant['interview_applicant_id'] . " applicant created for Group Booking";
                                }
                                $this->Recruitment_interview_model->add_interview_log($data, $msg_title, $interview_applicant['interview_applicant_id'], $adminId);

                                $response[] = ['status' => true, 'msg' => $interview_applicant['msg'], 'data' => $data ];
                            }else {
                                $error = true;
                                $response[] = ['status' => false, 'error' => $interview_applicant['error']];
                            }
                        }
                    } else {
                        $error = true;
                        // If requested data isn't valid
                        $errors = $this->form_validation->error_array();
                        $response[] = ['status' => false, 'error' => implode(', ', $errors)];
                    }
                }
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => 'Requested data is null'];
        }
        //send email to each applicants
        if (!empty($response) && is_array($response) && !empty($reqData->data->email_applicants)) {
            $em_data = new stdClass();
            $em_data->applicants = [];
            $em_data->interview_id = 0;
            $em_data->owner = new stdClass();
            $em_data->owner->value = "";
            $em_data->owner->label = "";
            foreach($response as $v) {
                $v = (object) $v;
                if ($v->status) {
                    $applicant = new stdClass();
                    $applicant->applicant_id = $v->data['applicant_id'];
                    $applicant->application_id = $v->data['application_id'];
                    $em_data->interview_id = $v->data['interview_id'];
                    $em_data->interview_type = $v->data['interview_type'];
                    $em_data->applicants[] = $applicant;
                }
            }
            if (!empty($em_data->applicants)) {
                // Check data is valid or not
                $this->load->model(['Recruitment_interview_model']);
                // call send mail
                $url = base_url()."recruitment/GroupBooking/send_group_interview_email_invitation";            
                $param = array('reqData' => $em_data,'adminId'=>$reqData->adminId);
                
                $this->asynclibrary->do_in_background($url, $param);
                $msg = 'Invitations have been sent successfully';
                
                if($reqData->data->selected_interview_type=='Group Interview'){
                    $msg = 'Applicants were added to the Group Interview and invitations were sent successfully';
                }else if($reqData->data->selected_interview_type=='CAB day'){
                    $msg = 'Applicants were added to the CAB Day and invitations were sent successfully';
                }
    
                echo json_encode(['status' => true, 'msg' => $msg]);
                exit();

            }
        }
        if (!$error) {
            $response = ['status' => true, 'msg' => 'Applicants added successfully'];
            if (!empty($result) && $result['status']) {
                $response['msg'] .= "\n Email invitation sent successfully to applicants";
            }
        } else {
            $msg = "";
            foreach($response as $v) {
                $msg .= "\n" . @$v['error'] . @$v['msg'];
            }
            $response = ['status' => false, 'error' => $msg];
        }
        echo json_encode($response);
        exit();
    }

    function send_group_interview_email_invitation() {
        $this->load->model(['Recruitment_interview_model']);
        $reqData = $this->input->post('reqData');
        $adminId = $this->input->post('adminId', true);
        $index = 0;
        foreach($reqData['applicants'] as $val) {            
            $resSendEmail = $this->Recruitment_interview_model->async_resend_invite_to_applicants($val, $reqData,$adminId);           
            $index ++;
        }
        exit();
    }

    /**
     * Get applicant list for group booking - SMS
     * - Activity tab
     */
    function get_applicant_list_for_sms() {
        $reqData = request_handler('access_recruitment'); 
        $response = ['status' => true, 'data' => ''];       
        if (!empty($reqData->data)) {
            $response = $this->Group_booking_model->get_applicant_list_for_sms($reqData->data);
        }
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }

    /**
     * Get applicant data for application - SMS
     * - Activity tab (Application Detail)
     */
    function get_applicant_data_for_sms() {
        $reqData = request_handler('access_recruitment'); 
        $response = ['status' => true, 'data' => ''];       
        if (!empty($reqData->data)) {
            $response = $this->Group_booking_model->get_applicant_data_for_sms($reqData->data,$reqData);
        }
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }
}
