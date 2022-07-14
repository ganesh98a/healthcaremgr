<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Job forms controller
 * 
 * @property-read \Recruitment_quiz_model $Recruitmentform_model
 */
class RecruitmentQuiz extends \MX_Controller {

    public function __construct() {
        parent::__construct();

        // tell form validation library to point to this 
        // subclass of MX_Controller when using callback validations
        // @see https://bitbucket.org/wiredesignz/codeigniter-modular-extensions-hmvc/src/codeigniter-3.x/
        $this->load->library('Form_validation');
        $this->form_validation->CI = & $this;
        $this->load->library('UserName');
        $this->load->helper(['array']);
        $this->load->model('Recruitment_quiz_model');
        $this->loges->setLogType('recruitment_forms');
        $this->load->helper('i_pad');
    }

    public function create_update_quiz() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        if (!empty($reqData)) {
            $data = (array) $reqData->data;


            # appending timings into date fields
            $data['quiz_start_datetime'] = $data['quiz_start_date']." ".$data['quiz_start_time'];
            $data['quiz_end_datetime'] = $data['quiz_end_date']." ".$data['quiz_end_time']; 


            // Validation rules set
            $validation_rules = [
                array('field' => 'title', 'label' => 'Title', 'rules' => 'required'),
                array('field' => 'form_id', 'label' => 'Form Template', 'rules' => 'required', "errors" => [ "required" => "Form Template is required" ]),
                array('field' => 'related_to', 'label' => 'Related To', 'rules' => 'required', "errors" => [ "required" => "Related to application is required" ]),
                array('field' => 'owner', 'label' => 'Owner', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required', "errors" => [ "required" => "Missing Application Id" ]),
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', "errors" => [ "required" => "Missing Applicant Id" ]),
                array(
                    'field' => 'quiz_start_datetime', 'label' => 'Quiz start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect quiz start date & time',
                    ]
                ),
                array(
                    'field' => 'quiz_end_datetime', 'label' => 'Quiz end date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect quiz end date & time',
                    ]
                    ),
            ];

            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {

                $this->load->model('Basic_model');

                // Call create form model
                $quiz = $this->Recruitment_quiz_model->create_update_quiz($data, $adminId);
                // According to that form will be created
                if ($quiz['status'] == true) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    /**
                     * Create logs. it will represent the user action they have made.
                     */
                    if($data['quiz_id']){
                        $this->loges->setTitle("Quiz updated for " . $data['title'] ." by " . $adminName);  // update title in log
                    }else{
                        $this->loges->setTitle("New quiz created for " . $data['title'] ." by " . $adminName);  // Set title in log
                    }
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    // Create log
                    $this->loges->createLog(); 
                    $data = array('quiz_id' => $quiz['quiz_id']);
                    $response = ['status' => true, 'msg' => $quiz['msg'], 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $quiz['error']];
                }
            } else {
                // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => 'Requested data is null'];
        }

        echo json_encode($response);
        exit();
    }

    /*
     * For getting form list
     * 
     * Return type json
     * - count 
     * - data
     * - status
     */
    function get_quiz_list_by_id() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required', "errors" => [ "required" => "Missing Application Id" ]),
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', "errors" => [ "required" => "Missing Applicant Id" ]),
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get form list
                $result = $this->Recruitment_quiz_model->get_quiz_list_by_id($reqData->data);
            } else {
               // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }      
        echo json_encode($result);
        exit();  
    }

    /**
     * Get form details by form id
     */
    public function get_quiz_detail_by_id() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'quiz_id', 'label' => 'Quiz Id', 'rules' => 'required', "errors" => [ "required" => "Missing Quiz Id" ]),
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get form list;
                $result = $this->Recruitment_quiz_model->get_quiz_status_detail_by_id($reqData->data);
                if($result['status']){
                    $result = $this->Recruitment_quiz_model->get_quiz_detail_by_id($reqData->data);
                }
               
            } else {
               // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }      
        echo json_encode($result);
        exit(); 
    }
    /*
     * To get form question list
     * 
     * Return type json
     * - count 
     * - data
     * - status
     */
    function get_questions_list_by_applicant_quiz_and_form_id() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'form_id', 'label' => 'Form Applicant Id', 'rules' => 'required', "errors" => [ "required" => "Missing Form Id" ])
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get form list
                $result = $this->Recruitment_quiz_model->get_questions_list_by_applicant_quiz_and_form_id($reqData->data);
            } else {
               // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }      
        echo json_encode($result);
        exit();  
    }  
    /*
     * fetches all the quiz statuses
     */
    function get_quiz_stage_status() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Recruitment_quiz_model->get_quiz_stage_status();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetches all the final quiz statuses for quiz details page
     */
    function get_quiz_statuses_final() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $response = $this->Recruitment_quiz_model->get_quiz_statuses_final();
            echo json_encode($response);
        }
        exit(0);
    }

     /**
     * Updating the quiz status.
     */
    public function update_quiz_status() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->Recruitment_quiz_model->update_quiz_status($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

     /*	
     * update the applicant quiz status as in progess	
     */	
    function update_applicant_quiz_status() {	
        $reqData=(array) api_request_handler();         	
        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];	
        if (!empty($reqData)) {	
            $data = (array) $reqData;	
            $task_id = $data['task_id'] ?? 0;	
            # validation rule	
            $validation_rules = [	
                array('field' => 'task_id', 'label' => 'Recruitment task applicant id', 'rules' => 'required'),               	
            ];           	
            # set data in libray for validate	
            $this->form_validation->set_data($reqData);	
            # set validation rule	
            $this->form_validation->set_rules($validation_rules);	
            # check data is valid or not	
            if ($this->form_validation->run()) {	
                # call update member modal function	
                $updated_member_id = $this->Recruitment_quiz_model->update_applicant_quiz_status($task_id,3);	
                # check $member_id is not empty	
                if ($updated_member_id) {	
                    $msg = 'Quiz status updated successfully.';	
                    $response = ['status' => true, 'msg' => $msg];	
                } else {	
                    $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];	
                }	
            } else {	
                $errors = $this->form_validation->error_array();	
                $response = ['status' => false, 'error' => implode(', ', $errors)];	
            }	
        }	
        echo json_encode($response);	
        exit();	
    }    
    /*	
     * get the applicant question and answer
     */	
    public function get_question_details(){
     $reqData = $reqData1 = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $result = $this->Recruitment_quiz_model->get_applicant_question_answer_details($reqData);
            echo json_encode($result);
        }           
    }
    /**
     * Updating the applicant quiz result to correct or incorrect.
     */
    public function update_answer(){
        $reqData = $reqData1 = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $result = $this->Recruitment_quiz_model->update_answer($reqData);
            echo json_encode($result);
        }           
    }

    /*
     * post parameters: form-id 
     * result: status true or false 
     */
    public function archive_quiz() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data->id)) {
            $quiz_id = $reqData->data->id;
            // check alreay active form anywgere
            $this->Recruitment_quiz_model->archive_quiz($quiz_id);
            $return = ["status" => true, "msg" => "Quiz is archived successfully"];
            
        } else {
            $return = ["status" => false, "error" => "quiz id is missing"];
        }
        
        echo json_encode($return);
    }
}
