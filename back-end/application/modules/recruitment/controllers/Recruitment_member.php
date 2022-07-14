<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read \Recruitment_applicant_model $Recruitment_applicant_model
 * @property-read \Recruitment_applicant_stages_model $stage_model
 * @package
 */
class Recruitment_member extends MX_Controller
{
   
    use formCustomValidation;

    function __construct()
    {
        parent::__construct();
        $this->load->model('recruitment/Recruitment_member_model');  
        $this->load->model('Basic_model');
        $this->load->model('recruitment/Recruitmentform_model');
        $this->load->model('schedule/Schedule_model');
        $this->load->model('member/Member_model');
        $this->load->model('sales/Account_model');
        $this->load->model('recruitment/Recruitmentformapplicant_model');
        $this->load->model('recruitment/Recruitment_quiz_model');
        $this->load->model('recruitment/Recruitment_applicant_model');
        $this->load->model('sales/Opportunity_model');
        $this->load->model('schedule/Shift_member_model');
        $this->load->model('schedule/Schedule_attachment_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;      
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

    /**
     * Retrieve applicant details. Common use-case is editing existing lead.
     *
     * `POST: /recruitment/Recruitment_member/get_applicant_details`
     *
     * @return CI_Output
     */
    public function get_applicant_member_details_by_id(){
        $reqData = request_handler();
        
        $reqData =(array) $reqData->data;
        $is_member = false;
        if(isset($reqData['member_id'])){
            $is_member = true;
        }

        if (empty($reqData['applicant_id']) && empty($reqData['member_id'])) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        $applicant_info=$this->Recruitment_member_model->get_applicant_member_details_by_id($reqData , $is_member);

        echo json_encode($applicant_info);
        exit();
     }


     public function update_applicant_info(){
        $reqData = request_handler();
        $reqData = (array) $reqData->data;
        $response = $this->validate_applicant_data($reqData);
        if($response['status']){
            $response_data=$this->Recruitment_member_model->update_applicant_profile_info($reqData);
            if(!empty($response_data) && $response_data['status']){
                echo json_encode(["status"=>true,"success"=>"Updated successfully",'address_id'=>(int)$response_data['address_id']??'']);
                exit();
            }else{
                echo json_encode(["status"=>false,"error"=>system_msgs('something_went_wrong')]);
                exit();
            }
        }else{
            echo json_encode($response);
            exit();
        }
    }

    public function get_state_list(){
        $state_list=$this->Recruitment_member_model->state_list();
        $response = array('status' => true, 'data' => $state_list);
        echo json_encode($response);
        exit();
    }

    /**
     * fetching a single shift member details using member and shift id
     */
    function get_shift_member_details_frm_member_id() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $data = $reqData->data;
            $result = $this->Schedule_model->get_shift_member_details_frm_member_id($data->member_id,$data->id);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * updating the shift status when requested by a web portal user
     */
    function update_shift_status() {
        $reqData = request_handler();
        $contactNumber=CONTACT_NUMBER_ONCALL;
        if (!empty($reqData)) {
            $data = object_to_array($reqData->data);
            if ($data['status'] == 5) {
                //check if shift notes and actuals are updated

                $result = $this->Schedule_model->get_shift_details($data['id']);
            if (empty($result) || $result['status'] != true) {
                $response = ['status' => false, 'error' => "Shift does not exist anymore."];
                return $response;
            }
            else{
                if(strtotime(date('Y-m-d H:i:s'))>strtotime($result['data']->scheduled_end_date))
                {
                  
                    if(round(abs(strtotime($result['data']->scheduled_end_date) - strtotime(date('Y-m-d H:i:s')))/60,2)>10)
                    {
                        $result = ['status' => false, 'timeElaspe'=>true,'error' => 'To complete a shift after 10 minutes from the scheduled time please contact <b>'.$contactNumber.'</b>.'];
                        echo json_encode($result);
                        exit();
                       
                    }
                }
                if(strtotime(date('Y-m-d H:i:s'))<strtotime($result['data']->scheduled_end_date))
                {
                    if(round((strtotime($result['data']->scheduled_end_date) - strtotime(date('Y-m-d H:i:s')))/60,2)>10)
                    {
                        $result = ['status' => false, 'timeElaspe'=>true,'error' => 'To complete a shift before 10 minutes from the scheduled time please contact <b>'.$contactNumber.'</b>.'];
                        echo json_encode($result);
                        exit();
                       
                    }
                }
            }
            
                $notes = $this->Schedule_model->get_shift_goal_tracking_details($data);
                if (!empty($notes) && !empty($notes['data'])) {
                    $notes_report = $notes['data']['goals_notes_reports'];
                    if (empty($notes_report) || empty($notes_report->task_taken) || empty($notes_report->worked_well) || empty($notes_report->done_better)) {
                        $result = ['status' => false,'timeElaspe'=>false, 'error' => 'Fill timesheet and shift notes details in order to complete the shift'];
                        echo json_encode($result);
                        exit();
                    }
                } else {
                    $result = ['status' => false,'timeElaspe'=>false, 'error' => 'Fill timesheet and shift notes details in order to complete the shift'];
                    echo json_encode($result);
                    exit();
                }
            }

        if($data['status'] == 4)
        {
            $result = $this->Schedule_model->get_shift_details($data['id']);
            if (empty($result) || $result['status'] != true) {
                $response = ['status' => false, 'error' => "Shift does not exist anymore."];
                return $response;
            }
            else{
                if(strtotime(date('Y-m-d H:i:s'))>strtotime($result['data']->scheduled_start_date))
                {
                  
                    if(round(abs(strtotime($result['data']->scheduled_start_date) - strtotime(date('Y-m-d H:i:s')))/60,2)>10)
                    {
                        $result = ['status' => false,'timeElaspe'=>true, 'error' => 'To start a shift after 10 minutes from  the scheduled time please contact <b>'.$contactNumber.'</b>.'];
                        echo json_encode($result);
                        exit();
                       
                    }
                }
                if(strtotime(date('Y-m-d H:i:s'))<strtotime($result['data']->scheduled_start_date))
                {
                    if(round((strtotime($result['data']->scheduled_start_date) - strtotime(date('Y-m-d H:i:s')))/60,2)>10)
                    {
                        $result = ['status' => false, 'timeElaspe'=>true,'error' => 'To start a shift before 10 minutes from the scheduled time please contact <b>'.$contactNumber.'</b>.'];
                        echo json_encode($result);
                        exit();
                        
                    }
                }
            }
        }
      
        
            $result = $this->Schedule_model->update_shift_status($data, $data['member_id']);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetching required shift statuses for web portal
     */
    function get_shift_status_portal() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $result = $this->Schedule_model->get_shift_status_portal();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting the shift details
     */
    function get_shift_details() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $result = $this->Schedule_model->get_shift_details($reqData->data->id, false, true, false, '', $reqData->uuid_user_type);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    //Get Goal details
    function get_goals_list() {
        $reqData = request_handler();

        if (!empty($reqData)) {
            // Call model for get participant list
            $result = $this->Schedule_model->get_goals_list($reqData->data);
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * calculate the duration between two timings and returns in HH:MM format
     */
    function calculate_shift_duration() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $result = $this->Schedule_model->calculate_shift_duration(object_to_array($reqData->data));
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
    * get shift break types from reference list
    */
    function get_shift_break_types() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $rows = $this->basic_model->get_result('references', ['archive'=>0,'type'=>25],['id as value','display_name as label', 'key_name']);
            $result = ["status" => true, "data" => $rows];
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * calculate the duration between two timings and returns in HH:MM format
     */
    function calculate_break_duration() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $data = object_to_array($reqData);
            $result = $this->Schedule_model->calculate_break_duration($data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * its used for create/update shift
     * handle request for create/update shift modal
     */
    function create_update_shift_portal() {
        $reqData = request_handlerFile(false,true,false,true);
        $uuid_user_type = $reqData->uuid_user_type;
        if (!empty($reqData)) {
            $data = object_to_array($reqData);
            
            if($data['actual_travel_duration_hr'] && $data['actual_travel_duration_min']) {
                $data['actual_travel_duration'] = $data['actual_travel_duration_hr'] . ":"
                . $data['actual_travel_duration_min'];
            } else {
                $data['actual_travel_duration'] = '';
            }

            $result = $this->Schedule_model->create_update_shift_portal($data, $data['member_id'], $uuid_user_type);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * returns time slots in a gap of half-hour
     */
    function get_time_slots_half_hour() {
        $reqData = request_handler();
        $numericIndex = true;
        $data = $this->Member_model->get_time_slots_half_hour($numericIndex);
        $response = array('status' => true, 'data' => $data);
        echo json_encode($response);
    }

    /*
    * get all reference data of unavailability to assign to members
    */
    function get_unavailability_type_data() {
        $reqData = request_handler();
        $rows["unavailability_types"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>22],['id as value','display_name as label']);
        echo json_encode(["status" => true, "data" => $rows]);
        exit(0);
    }

    /*
     * its used for create/update member unavailability
     * handle request for create/update member unavailability modal
     */
    function create_update_member_unavailability() {
        $reqData = request_handler();

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (empty($reqData)) {
            echo json_encode($response);
            exit();
        }
        $data = (array) $reqData->data;
        $member_id = $data['member_id'];
        $response = $this->Member_model->create_update_member_unavailability($data, $member_id);
        if(isset($response) && $response['status'] == true) {
            $this->loges->setCreatedBy($member_id);
            $this->load->library('UserName');
            $adminName = $this->Member_model->get_member_fullname($member_id);

            # create log setter getter
            if (!empty($data['id'])) {
                $this->loges->setTitle("Updated member unavailability by " . $adminName);
            } else {
                $this->loges->setTitle("New member unavailability created by " . $adminName);
            }
            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($member_id);
            $this->loges->setCreatedBy($member_id);
            $this->loges->createLog();
        }
        echo json_encode($response);
        exit();
    }

    /**
     * Mark member unavailability as archived.
     */
    public function archive_member_unavailability() {
        $reqData = request_handler();

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (empty($reqData)) {
            echo json_encode($response);
            exit();
        }
        $data = (array) $reqData->data;
        $member_id = $data['member_id'];
        $response = $this->Member_model->archive_member_unavailability($data, $member_id);
        if(isset($response) && $response['status'] == true) {
            $this->load->library('UserName');
            $adminName = $this->Member_model->get_member_fullname($member_id);
            $this->loges->setTitle(sprintf("Successfully archived member unavailability with ID of %s by %s", $data['id'], $adminName));
            $this->loges->setSpecific_title(sprintf("Successfully archived member unavailability with ID of %s by %s", $data['id'], $adminName));  // set title in log
            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($member_id);
            $this->loges->setCreatedBy($member_id);
            $this->loges->createLog();
        }
        echo json_encode($response);
        exit();
    }

    /**
     * Retrieve member unavailability details.
     */
    public function get_member_unavailability_details() {
        $reqData = request_handler();
        $data = $reqData->data;

        if (empty($data->id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        $result = $this->Member_model->get_member_unavailability_details($data->id);
        echo json_encode($result);
        exit();
    }

    /*
     * For getting members' unavailability list
     */
    function get_member_unavailability_list() {
        $reqData =  request_handler();
        $reqData = $reqData->data;
        try {
            $validation_rules = array(
                array('field' => 'member_id', 'label' => 'Member id', 'rules' => 'required|greater_than[0]', "error" => [ 'required' => 'Missing Member Id', 'greater_than' =>  'Missing Member Id']),
            );
            $this->form_validation->set_message('greater_than', 'Missing Member Id');
            $this->form_validation->set_data((array)$reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                if (!empty($reqData)) {
                    $result = $this->Member_model->get_member_unavailability_list($reqData);
                } else {
                    $result = ['status' => false, 'error' => 'Requested data is null'];
                }
            } else {
                $errors = $this->form_validation->error_array();
                $result = array('status' => false, 'error' => implode(', ', $errors));
            }
        } catch (Exception $e) {
            $result = array('status' => false, 'error' => system_msgs('something_went_wrong'));
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Accepting or rejecting a shift for a given member
     * Making necessary flow changes depending upon the action requested
     */
    public function accept_reject_shift() {
        $reqData = request_handler();
        $reqData = $reqData->data;
        if (!empty($reqData)) {
            $result = $this->Schedule_model->accept_reject_shift((array)$reqData);
            // $result = ['status' => true, 'msg' => 'done'];
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetching published shifts that are assigned to a member
     * function is used mainly in the member/applicant web app
     */
    function get_shifts_member() {
        $reqData = (array) request_handler();
        $reqData = $reqData['data'];
        try {
            $validation_rules = array(
                array('field' => 'member_id', 'label' => 'Member id', 'rules' => 'required|greater_than[0]', "error" => [ 'required' => 'Missing Member Id', 'greater_than' =>  'Missing Member Id']),
            );
            $this->form_validation->set_message('greater_than', 'Missing Member Id');
            $this->form_validation->set_data((array)$reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                if (!empty($reqData)) {
                    $result = $this->Shift_member_model->get_available_shifts_member($reqData);
                } else {
                    $result = ['status' => false, 'error' => 'Requested data is null'];
                }
            } else {
                $errors = $this->form_validation->error_array();
                $result = array('status' => false, 'error' => implode(', ', $errors));
            }
        } catch (Exception $e) {
            $result = array('status' => false, 'error' => system_msgs('something_went_wrong'));
        }
        echo json_encode($result);
        exit();
    }

    /**
     * validating applicant/member app login
     * replaced PIN with Password in HCM-3083
     */
    public function ipad_valid_login(){
        $reqData = request_handler();

        $response = $this->Recruitment_member_model->validate_applicant_member_login($reqData);
        echo $response;
        exit();
    }

public function get_question_list(){
    $reqData = request_handler();
    $reqData = (array) $reqData->data;
    $response = $this->validate_task_data($reqData);

    if($response['status']){
        $taskId=$reqData['task_id'];
        $applicant_id=$reqData['applicant_id'];
        $interview_type=isset($reqData['interview_type_id'])?$reqData['interview_type_id']:0;
        $available_timing=$this->Recruitment_member_model->check_exam_start_remaining_timing_available($applicant_id,$taskId);
            $currunt_datatime=DATE_TIME; // '2019-09-26 19:30:44';

            if(empty($available_timing)){
                echo json_encode(["status"=>false,"error"=> system_msgs('something_went_wrong')]);
                exit();
            }elseif (strtotime($available_timing['start_datetime']) > strtotime($currunt_datatime)){
                echo json_encode(["status"=>false,"error"=> system_msgs('remaining_interview_start_time')]);
                exit();
            }elseif(strtotime($available_timing['end_datetime']) < strtotime($currunt_datatime)){
                echo json_encode(["status"=>false,"error"=> system_msgs('remaining_interview_end_time_over')]);
                exit();
            }

            # fetching the task application id
            $application_data = $this->basic_model->get_row('recruitment_task_applicant', ["application_id"],array('taskId'=>$taskId, 'applicant_id' => $applicant_id, "email_status" => 1, "status" => 1, "archive" => 0));
            if($application_data)
            $reqData['application_id'] = $application_data->application_id;

            $response_data=$this->Recruitment_member_model->device_question_list($reqData);
            if(!empty($response_data['status'])){
                // 2=Get Question
                $this->Recruitment_member_model->update_ipad_last_stage($reqData,'2');
                echo json_encode(["status"=>true,"success"=>"get record successfully",'data' =>$response_data['data']]);
                exit();
            }else{
                echo json_encode($response_data);
                exit();
            }
        } else{
            echo json_encode($response);
            exit();
        }
}

    /**
     * controller function that handles the questions and answers submitted from iPad
     */
    public function submit_answers_list(){
        $reqData = request_handler();
        $reqData = $reqData->data;
        $recruitment_task_applicant_id = $this->Recruitment_member_model->get_applicant_task_id($reqData);
        if($recruitment_task_applicant_id==0){
            return ['status' => false, 'error' => "recruitment task applicant id is empty"];
        }

        if(!isset($reqData['questions']) || empty($reqData['questions'])){
            echo json_encode(["status"=>false,"error"=>"no questions submitted"]);
            exit();
        }

        # checking if the quiz is submitted or not?
        $get_status_arr=$this->Recruitment_member_model->get_applicant_quiz_submit_status($recruitment_task_applicant_id);
        if(!isset($get_status_arr['status']) || $get_status_arr['status'] != 1){
            echo json_encode(["status"=>false,"error"=>"no questions submitted"]);
            exit();
        }

        $interview_type = $this->Recruitment_member_model->get_type_of_interview($recruitment_task_applicant_id);
        if(!$interview_type) {
            echo json_encode(["status"=>false,"error"=>"no interview type found"]);
            exit();
        }
        $answer_data['interview_type'] = $interview_type;

        # fetching the form id, applicant id and application id from task applicant id
        $form_data = $this->Recruitment_member_model->get_applicant_form_id_application_id($recruitment_task_applicant_id);
        if($form_data) {
            $answer_data['form_id'] = $form_data['form_id'];
            $answer_data['application_id'] = $form_data['application_id'];
            $answer_data['applicant_id'] = $form_data['applicant_id'];
        }

        # converting questions, answer options and submitted answer into right format
        foreach($reqData['questions'] as $queobj) {
            $ans_arr = null;
            $correct_ans = null;
            $ans_text = null;
            if($queobj->answer_option) {
                foreach($queobj->answer_option as $ansobj) {

                    # fetching one information from database for an answer option
                    $answer_details = $this->basic_model->get_row($table_name = 'recruitment_additional_questions_answer', $columns = array('serial', 'answer'), array('id'=>$ansobj->id));

                    $ansobj->q_id = $queobj->id;
                    $ansobj->answer_id = $ansobj->id;
                    $ansobj->value = $ansobj->option;
                    $ansobj->label = (isset($answer_details) && isset($answer_details->serial)) ? $answer_details->serial : '';

                    if($queobj->question_type == 4) {
                        $ans_text = $ansobj->option;
                        if(isset($queobj->answer_text))
                        $ans_text = $queobj->answer_text;
                    }
                    else {
                        $ans_sin = (array) $ansobj;
                        if(isset($ans_sin['answered']) && $ans_sin['answered'] == 1) {
                            $correct_ans[] = $ans_sin['answer_id'];
                        }

                        if(isset($answer_details->answer) && $answer_details->answer == 1) {
                            $ans_sin['checked'] = $answer_details->answer;
                        }
                        else {
                            $ans_sin['checked'] = 0;
                        }

                        $ans_arr[] = $ans_sin;
                    }
                }
            }

            # finding question details
            $que_details = $this->Recruitmentform_model->get_question_details($queobj->id);

            $que_sin['id'] = $queobj->id;
            $que_sin['question'] = $queobj->question;
            $que_sin['status'] = $que_details->status;
            $que_sin['created'] = $que_details->created;
            $que_sin['created_by'] = $que_details->created_by;
            $que_sin['question_type'] = $queobj->question_type;
            $que_sin['question_topic'] = $que_details->question_topic;
            $que_sin['training_category'] = $que_details->training_category;
            $que_sin['form_id'] = $que_details->form_id;
            $que_sin['display_order'] = $queobj->display_order;
            $que_sin['updated'] = $que_details->updated;
            $que_sin['archive'] = $que_details->archive;
            $que_sin['is_answer_optional'] = $que_details->is_answer_optional;
            $que_sin['is_required'] = $queobj->is_required;
            $que_sin['answers'] = $ans_arr;
            $que_sin['answer_id'] = $correct_ans;
            $que_sin['answer_text'] = $ans_text;
            $answer_data['question_answers'][] = $que_sin;
        }

        # checking if the same form by applicant for same application submitted?
        $form_applicant_id = $this->Recruitmentformapplicant_model->applicant_form_submitted($answer_data);
        $inserted_id = null;

        $answer_data['question_answers_validate'] = (object) $answer_data;

        # doing server validations of submitted data
        $validation_rules = array(
            array('field' => 'question_answers_validate[]', 'label' => 'Answers', 'rules' => 'callback_validate_submitted_answer'),
            array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required'),
            array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required'),
            array('field' => 'form_id', 'label' => 'Form Id', 'rules' => 'required')
        );

        $this->form_validation->set_data($answer_data);
        $this->form_validation->set_rules($validation_rules);

        # return back if validation fails
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $return = array('status' => false, 'error' => implode(', ', $errors));
            echo json_encode($return);
            exit;
        }
        // pr($answer_data);

        # updating or inserting form applicant accordingly
        if($form_applicant_id)
            $this->Recruitmentformapplicant_model->update($form_applicant_id, $answer_data, 0);
        else
            $form_applicant_id = $this->Recruitmentformapplicant_model->create($answer_data, 0);

        # saving the questions and their answers submitted
        $answer_data["form_applicant_id"] = $form_applicant_id;
        $this->Recruitmentformapplicant_model->save_interview_answer_of_applicant($answer_data);

        # fetching applicant's info
        $applicant_info = $this->Recruitment_applicant_model->get_applicant_info($answer_data['applicant_id'], 0);
        # fetching job applicantion's info
        $application_info = null;#ToDo
        # fetching form information
        $form_info = null; #ToDo

        # adding a log entry
        $formname = "ToDo";
        $jobtitle = "ToDo";
        $log_title = "Form name: {$formname}, applicant: ".$applicant_info['fullname']."(".$applicant_info['appId']."), Job title: {$jobtitle}";

        if ($form_applicant_id)
            $log_title = "Edit, " . $log_title;
        else
            $log_title = "Add, " . $log_title;

        $this->loges->setTitle($log_title);
        $this->loges->setUserId($answer_data['applicant_id']);
        $this->loges->setDescription(json_encode($answer_data));
        $this->loges->setCreatedBy(0);
        $this->loges->createLog();

        # marking the stage status as completed
        $this->Recruitment_member_model->update_applicant_quiz_submit_status($recruitment_task_applicant_id,1);

        # marking the ipad last stage as completed
        $this->Recruitment_member_model->update_ipad_last_stage($reqData,'3');

        $return = array('status' => true, 'msg' => "Applicant quiz successfully submitted");
        echo json_encode($return);
        exit;
    }

    /**
     * controller function that handles the questions and answers submitted from iPad
     */
    public function submit_answers_list_for_quiz(){
        $reqData =   request_handler();
        $reqData = (array) $reqData->data;
        $recruitment_task_applicant_id = $this->Recruitment_member_model->get_applicant_task_id_by_status($reqData);
        if($recruitment_task_applicant_id==0){
            return ['status' => false, 'error' => "recruitment task applicant id is empty"];
        }


        if(!isset($reqData['questions']) || empty($reqData['questions'])){
            echo json_encode(["status"=>false,"error"=>"no questions submitted"]);
            exit();
        }

        # fetching the form id, applicant id and application id from task applicant id
        $form_data = $this->Recruitment_member_model->get_applicant_form_id_application_id($recruitment_task_applicant_id);
        if($form_data) {
            $answer_data['form_id'] = $form_data['form_id'];
            $answer_data['application_id'] = $form_data['application_id'];
            $answer_data['applicant_id'] = $form_data['applicant_id'];
            $answer_data['task_id'] = $form_data['taskId'];
        }

        # converting questions, answer options and submitted answer into right format
        foreach($reqData['questions'] as $queobj) {
            $ans_arr = null;
            $correct_ans = null;
            $ans_text = null;
            if($queobj->answer_option) {
                foreach($queobj->answer_option as $ansobj) {

                    # fetching one information from database for an answer option
                    $answer_details = $this->basic_model->get_row($table_name = 'recruitment_additional_questions_answer', $columns = array('serial', 'answer'), array('id'=>$ansobj->id));

                    $ansobj->q_id = $queobj->id;
                    $ansobj->answer_id = $ansobj->id;
                    $ansobj->value = $ansobj->option;
                    $ansobj->label = (isset($answer_details) && isset($answer_details->serial)) ? $answer_details->serial : '';

                    if($queobj->question_type == 4) {
                        $ans_text = $ansobj->option;
                        if(isset($queobj->answer_text))
                        $ans_text = $queobj->answer_text;
                    }
                    else {
                        $ans_sin = (array) $ansobj;
                        if(isset($ans_sin['answered']) && $ans_sin['answered'] == 1) {
                            $correct_ans[] = $ans_sin['answer_id'];
                        }

                        if(isset($answer_details->answer) && $answer_details->answer == 1) {
                            $ans_sin['checked'] = $answer_details->answer;
                        }
                        else {
                            $ans_sin['checked'] = 0;
                        }

                        $ans_arr[] = $ans_sin;
                    }
                }
            }

            # finding question details
            $que_details = $this->Recruitmentform_model->get_question_details($queobj->id);

            $que_sin['id'] = $queobj->id;
            $que_sin['question'] = $queobj->question;
            $que_sin['status'] = $que_details->status;
            $que_sin['created'] = $que_details->created;
            $que_sin['created_by'] = $que_details->created_by;
            $que_sin['question_type'] = $queobj->question_type;
            $que_sin['question_topic'] = $que_details->question_topic;
            $que_sin['training_category'] = $que_details->training_category;
            $que_sin['form_id'] = $que_details->form_id;
            $que_sin['display_order'] = $queobj->display_order;
            $que_sin['updated'] = $que_details->updated;
            $que_sin['archive'] = $que_details->archive;
            $que_sin['is_answer_optional'] = $que_details->is_answer_optional;
            $que_sin['is_required'] = $queobj->is_required;
            $que_sin['answers'] = $ans_arr;
            $que_sin['answer_id'] = $correct_ans;
            $que_sin['answer_text'] = $ans_text;
            $answer_data['question_answers'][] = $que_sin;
        }

        # checking if the same form by applicant for same application submitted?
        $form_applicant_id = $this->Recruitmentformapplicant_model->applicant_form_submitted($answer_data);
        $inserted_id = null;

        $answer_data['question_answers_validate'] = (object) $answer_data;
        
        # doing server validations of submitted data
        $validation_rules = array(
            array('field' => 'question_answers_validate[]', 'label' => 'Answers', 'rules' => 'callback_validate_submitted_answer'),
            array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required'),
            array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required'),
            array('field' => 'form_id', 'label' => 'Form Id', 'rules' => 'required')
        );

        $this->form_validation->set_data($answer_data);
        $this->form_validation->set_rules($validation_rules);

        # return back if validation fails
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $return = array('status' => false, 'error' => implode(', ', $errors));
            echo json_encode($return);
            exit;
        }

        # updating or inserting form applicant accordingly
        if($form_applicant_id)
            $this->Recruitmentformapplicant_model->update($form_applicant_id, $answer_data, 0);
        else
            $form_applicant_id = $this->Recruitmentformapplicant_model->form_applicant_create($answer_data, 0);

        # saving the questions and their answers submitted
        $answer_data["form_applicant_id"] = $form_applicant_id;
        $this->Recruitmentformapplicant_model->save_interview_answer_of_applicant($answer_data);

        # fetching applicant's info
        $applicant_info = $this->Recruitment_applicant_model->get_applicant_info($answer_data['applicant_id'], 0);

        # fetching job applicantion's info
        $application_info = $this->basic_model->get_row('recruitment_task_applicant', ["application_id"], array('taskId'=> $reqData['task_id']));
        # fetching form information
        $form_info = null; #ToDo

        # adding a log entry
        $formname = "ToDo";
        $jobtitle = "ToDo";
        $log_title = "Form name: {$formname}, applicant: ".$applicant_info['fullname']."(".$applicant_info['appId']."), Job title: {$jobtitle}";

        if ($form_applicant_id)
            $log_title = "Edit, " . $log_title;
        else
            $log_title = "Add, " . $log_title;

        $this->loges->setTitle($log_title);
        $this->loges->setUserId($answer_data['applicant_id']);
        $this->loges->setDescription(json_encode($answer_data));
        $this->loges->setCreatedBy(0);
        $this->loges->createLog();

        //Get owner name
        $owner_data = $this->basic_model->get_row('recruitment_task', ["owner"], array('id'=> $reqData['task_id']));

        if($owner_data) {
            $applicant_info['owner'] = $owner_data->owner;
        }
        if($application_info) {
            $applicant_info['appId'] = $application_info->application_id;
        }
        # marking the stage status as completed
        $this->Recruitment_quiz_model->update_applicant_quiz_status($reqData['task_id'], 4,
             $applicant_info);

        # marking the ipad last stage as completed
        $this->Recruitment_member_model->update_ipad_last_stage($reqData,'3');

        $return = array('status' => true, 'msg' => "Applicant quiz successfully submitted");
        echo json_encode($return);
        exit;
    }

    public function validate_applicant_data($applicant_data){        
      try {
           // $this->form_validation->set_message('greater_than', 'This %s is not exist ');
        $validation_rules = array(
            array('field' => 'applicant_id', 'label' => 'Applicant id', 'rules' => 'required|greater_than[0]'),
                //array('field' => 'applicant_email', 'label' => 'Applicant email', 'rules' => 'required|valid_email|callback_check_email_already_exist_to_another_applicant['.$applicant_data['applicant_id'].']'),
            array('field' => 'applicant_phone', 'label' => 'Applicant phone', 'rules' => 'required|min_length[8]|max_length[18]|callback_check_phone_already_exist_to_another_applicant['.$applicant_data['applicant_id'].']'),
            // array('field' => 'applicant_address', 'label' => 'Applicant id', 'rules' => 'callback_check_recruitment_applicant_address[applicant_address]'),
        );
        //|callback_phone_number_check[applicant_phone,reuired,Applicant contact info should be enter valid phone number.]
        $this->form_validation->set_data($applicant_data);
        $this->form_validation->set_rules($validation_rules);

        if ($this->form_validation->run()) {
            $return = array('status' => true);
        } else {
            $errors = $this->form_validation->error_array();
                //$key=array_key_first($errors);
            $key=current(array_keys($errors));
            $msg=$errors[$key];
                $return = array('status' => false, 'error' =>$msg ); //implode(', ', $errors));
            }
        } catch (Exception $e) {
            $return = array('status' => false, 'error' => system_msgs('something_went_wrong'));
        }
        return $return;
    }

    public function validate_task_data($task_data){
        try {
            $validation_rules = array(
                array('field' => 'applicant_id', 'label' => 'Applicant id', 'rules' => 'required|greater_than[0]'),
            );
            //|callback_phone_number_check[applicant_phone,reuired,Applicant contact info should be enter valid phone number.]
            $this->form_validation->set_data($task_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
        } catch (Exception $e) {
            $return = array('status' => false, 'error' => system_msgs('something_went_wrong'));
        }
        return $return;
    }

    public function get_cab_day_task_applicant_specific_details(){
        $reqData = (array)  request_handler();
        $response = $this->validate_cab_day_task_data($reqData);
        if($response['status']){

            $task_applicant_id=addslashes($reqData['task_applicant_id']);
            $applicant_id=addslashes($reqData['applicant_id']);

            $applicant_quiz_status=$this->Recruitment_member_model->get_applicant_quiz_status($task_applicant_id);
            if(!$applicant_quiz_status['status']){
                echo json_encode(array('status' => false, 'error' => system_msgs('something_went_wrong')));
                exit();
            }
            $quiz_status=$applicant_quiz_status['data'];

                // quiz_submit_status 0->Pending, 1->Submit , 2->failed
            if($quiz_status['quiz_submit_status']==0){
                echo json_encode(array('status' => false, 'error' => "Quiz submit status is pending"));
                exit();
            }elseif($quiz_status['quiz_submit_status']==2){
                echo json_encode(array('status' => false, 'error' => "Quiz submit status is failed"));
                exit();
            }elseif($quiz_status['quiz_submit_status']==1){
                    // quiz_status 0->Pending, 1->successful , 2->unsuccessful
                if($quiz_status['quiz_status']==0){
                    echo json_encode(array('status' => false, 'error' => "Applicant result is awaiting."));
                    exit();
                }
                if($quiz_status['quiz_status']==2){
                    echo json_encode(array('status' => false, 'error' => "Applicant result is unsuccessful."));
                    exit();
                }
                    // 4=Remaining Document list (CabDay Interview)
                $this->Recruitment_member_model->update_ipad_last_stage($reqData,'4');
                echo json_encode($response_data=$this->Recruitment_member_model->get_cab_day_task_applicant_specific_details($reqData));
                exit();

            }else{
                echo json_encode(array('status' => false, 'error' => system_msgs('something_went_wrong')));
                exit();
            }


        }
        echo json_encode($response);
        exit();

    }

    private function validate_cab_day_task_data($task_data){
        try {
            $validation_rules = array(
                array('field' => 'task_applicant_id', 'label' => 'Task applicant id', 'rules' => 'required|greater_than[0]'),
                array('field' => 'applicant_id', 'label' => 'Applicant id', 'rules' => 'required|greater_than[0]')
            );
            //|callback_phone_number_check[applicant_phone,reuired,Applicant contact info should be enter valid phone number.]
            $this->form_validation->set_data($task_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
        } catch (Exception $e) {
            $return = array('status' => false, 'error' => system_msgs('something_went_wrong'));
        }
        return $return;
    }

    public function log_out(){
        $reqData = (array)  request_handler();
        $applicant_id=addslashes($reqData['applicant_id']);
        $response_data=$this->Recruitment_member_model->remove_token($applicant_id);
        echo json_encode(array('status' => true,"success"=>"logout successfully"));
    }

    public function view_draft_contract(){
        $reqData = (array)  request_handler();
        $response = $this->validate_draft_contract_data($reqData);
        if($response['status']){
            $task_applicant_id=addslashes($reqData['task_applicant_id']);
            $applicant_id=addslashes($reqData['applicant_id']);
            $interview_type=addslashes($reqData['interview_type_id']);

            $applicant_quiz_status=$this->Recruitment_member_model->get_applicant_quiz_status($task_applicant_id);
            if(!$applicant_quiz_status['status']){
                echo json_encode(array('status' => false, 'error' => "draft contract data not exist"));
                exit();
            }
            $quiz_status=$applicant_quiz_status['data'];

            // quiz_submit_status 0->Pending, 1->Submit , 2->failed
            if($quiz_status['quiz_submit_status']==0){
                echo json_encode(array('status' => false, 'error' => "Quiz submit status is pending"));
                exit();
            }elseif($quiz_status['quiz_submit_status']==2){
                echo json_encode(array('status' => false, 'error' => "Quiz submit status is failed"));
                exit();
            }elseif($quiz_status['quiz_submit_status']==1){
                // quiz_status 0->Pending, 1->successful , 2->unsuccessful
                if($quiz_status['quiz_status']==0){
                    echo json_encode(array('status' => false, 'error' => "Applicant result is awaiting."));
                    exit();
                }
                if($quiz_status['quiz_status']==2){
                    echo json_encode(array('status' => false, 'error' => "Applicant result is unsuccessful."));
                    exit();
                }
                if($interview_type==2){

                    if($quiz_status['document_status']==0){
                        echo json_encode(array('status' => false, 'error' => "Applicant document pending please provide document first."));
                        exit();
                    }
                    if($quiz_status['document_status']==2){
                        echo json_encode(array('status' => false, 'error' => "Applicant uploaded document unsuccessful."));
                        exit();
                    }
                }

                $response_data=$this->Recruitment_member_model->get_draft_contract_data($task_applicant_id);
                if(!empty($response_data)){
                    if(!empty($response_data['contract_unsigned_file'])){
                        $contract_path='';
                        if($interview_type==1){ $filePath=GROUP_INTERVIEW_CONTRACT_PATH;
                            $contract_path=base_url('mediaShowProfile/rg/'.urlencode(base64_encode($applicant_id)).'/'.urlencode(base64_encode($response_data['contract_unsigned_file'])));
                        }
                        if($interview_type==2){$filePath=CABDAY_INTERVIEW_CONTRACT_PATH;
                            $contract_path=base_url('mediaShowProfile/rc/'.urlencode(base64_encode($applicant_id)).'/'.urlencode(base64_encode($response_data['contract_unsigned_file'])));
                        }
                        $fileFCPath=FCPATH.$filePath.$response_data['contract_unsigned_file'];
                        if(file_exists($fileFCPath)){

                                //$response_data['filepath']= base_url().$filePath.$response_data['contract_unsigned_file'];
                                // 5= View Draft Contract
                            $this->Recruitment_member_model->update_ipad_last_stage($reqData,'5');
                            $response_data['filepath']=$contract_path;
                            echo json_encode(array('status' => true , 'success'=>"get record successfully" ,'data'=>$response_data));
                            exit();
                        }else{
                            echo json_encode(array('status' => false, 'error' => "file not found"));
                            exit();
                        }

                    }else{
                        echo json_encode(array('status' => false, 'error' => "file name not not exist"));
                        exit();
                    }
                }else{
                    echo json_encode(array('status' => false, 'error' => "draft contract data not exist"));
                    exit();
                }


            }else{
                echo json_encode(array('status' => false, 'error' => system_msgs('something_went_wrong')));
                exit();
            }
        }
        echo json_encode($response);
        exit();
    }

    private function validate_draft_contract_data($task_data){
        try {
            $validation_rules = array(
                array('field' => 'task_applicant_id', 'label' => 'Task applicant id', 'rules' => 'required|greater_than[0]'),
                array('field' => 'applicant_id', 'label' => 'Applicant id', 'rules' => 'required|greater_than[0]'),
                array('field' => 'interview_type_id', 'label' => 'interview type', 'rules' => 'required|greater_than[0]'),
            );
            //|callback_phone_number_check[applicant_phone,reuired,Applicant contact info should be enter valid phone number.]
            $this->form_validation->set_data($task_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
        } catch (Exception $e) {
            $return = array('status' => false, 'error' => system_msgs('something_went_wrong'));
        }
        return $return;
    }

    public function send_draft_contract(){
        require_once APPPATH . 'Classes/RenamingFileTemporary.php';

        $reqData = (array)  request_handler();
        $response = $this->validate_send_draft_contract_data($reqData);
        if($response['status']){
            $contract_file_id=addslashes($reqData['contract_file_id']);
            $applicant_id=addslashes($reqData['applicant_id']);
            $interview_type=addslashes($reqData['interview_type_id']);
            $contract_email=addslashes($reqData['contract_send_mail']);
            $contract_response=$this->Recruitment_member_model->get_contract_file_by_id($contract_file_id);
            $contract_applicant=$this->Recruitment_member_model->get_applicant_details($applicant_id);
            $recruiterId = $contract_applicant['recruiter'] ?? 0;

            $contract_data=[];
            $contract_data['file_data']=$contract_response;
            $contract_data['applicant_data']=$contract_applicant;
            $contract_data['email']=$contract_email;

            if(!empty($contract_response)){
                if($interview_type==1){
                    $filePath=GROUP_INTERVIEW_CONTRACT_PATH;
                    $interview='Group Interview';
                    // Group interview contract doc file start

                    if(!empty($contract_response['contract_unsigned_file'])){
                        $interview='';
                        $fileFCPath=FCPATH.$filePath.$contract_response['contract_unsigned_file'];
                            if(file_exists($fileFCPath)){

                                // use for rename the filename according to document category and applicant name
                                $objRen = new RenamingFileTemporary();
                                $objRen->setFilename($fileFCPath);
                                $objRen->setRequired_filename("Draft Contract_". ($contract_applicant['firstname'].' '.$contract_applicant['lastname']));

                                $contract_applicant['filepath']=$objRen->rename_file();
                                $contract_applicant['email']=$contract_email;
                                $contract_applicant['intreview']=$contract_email;
                                send_IPAD_draft_contract_email($contract_applicant);

                                 // after send it to mail delete file
                                $objRen->delete_temp();

                                $this->Recruitment_member_model->update_ipad_last_stage($reqData,'6');
                                $request_params=[];
                                $request_params['to_email'] = $contract_email;
                                $request_params['subject'] = trim($contract_applicant['firstname']??''.' '.$contract_applicant['lastname']??'').' Applicant draft contract document send successfully';
                                $request_params['body'] = trim($contract_applicant['firstname']??''.' '.$contract_applicant['lastname']??'').' Applicant draft contract document send successfully';
                                $request_params['fullname'] = trim($contract_applicant['firstname']??''.' '.$contract_applicant['lastname']??'');
                                $request_params['filename'] = $contract_response['contract_unsigned_file'] ?? '';

                                require_once APPPATH . 'Classes/CommunicationLog.php';
                                $logObj = new CommunicationLog();

                                // only for log purpose
                                $email_log_data[] = [
                                    'from' => APPLICATION_NAME,
                                    'communication_text' => $request_params['body'],
                                    'userId' => $applicant_id,
                                    'user_type' => 1,
                                    'send_by' => $recruiterId,
                                    'log_type' => 2,
                                    'created' => DATE_TIME,
                                    'title' => "Group interview DocuSign document re-send"
                                ];

                                $logObj->createMuitipleCommunicationLog($email_log_data);

                                echo json_encode(array('status' => true , 'success'=>"draft contract send successfully"));
                                exit();
                            }else{
                                echo json_encode(array('status' => false, 'error' => "file not found"));
                                exit();
                            }
                    }else{
                        echo json_encode(array('status' => false, 'error' => "file name not not exist"));
                        exit();
                    }
                    // Group interview contract doc file end
                }

                if($interview_type==2){
                    $filePath=CABDAY_INTERVIEW_CONTRACT_PATH;
                    $interview='Cab Dat Interview';

                    if(empty($contract_data['file_data']['envelope_id'])){
                        echo json_encode(array('status' => false, 'error' => "Applicant cab day document file not exist"));
                        exit();
                    }
                    if($contract_data['file_data']['signed_status']==1){
                        echo json_encode(array('status' => false, 'error' => "Applicant already signed this file"));
                        exit();
                    }

                    $docu_sign_response= $this->resend_cab_day_contract_docusign($contract_data);
                    if($docu_sign_response['status']){
                        $request_params=[];
                        $request_params['to_email'] = $contract_email;
                        $request_params['subject'] = trim($contract_applicant['firstname']??''.' '.$contract_applicant['lastname']??'').' Applicant cabday contract document send successfully';
                        $request_params['body'] = trim($contract_applicant['firstname']??''.' '.$contract_applicant['lastname']??'').' Applicant cabday contract document send successfully';
                        $request_params['fullname'] = trim($contract_applicant['firstname']??''.' '.$contract_applicant['lastname']??'');
                        $request_params['filename'] = $contract_response['contract_unsigned_file'] ?? '';

                        require_once APPPATH . 'Classes/CommunicationLog.php';
                        $logObj = new CommunicationLog();

                        // only for log purpose
                        $email_log_data[] = [
                            'from' => APPLICATION_NAME,
                            'communication_text' => $request_params['body'],
                            'userId' => $applicant_id,
                            'user_type' => 1,
                            'send_by' => $recruiterId,
                            'log_type' => 2,
                            'created' => DATE_TIME,
                            'title' => "CAB day interview DocuSign document re-send",
                        ];

                        $logObj->createMuitipleCommunicationLog($email_log_data);
                        echo json_encode(array('status' => true , 'success'=>"Cab day draft contract send successfully"));
                        exit();
                    }else{
                        echo json_encode(array('status' => false , 'success'=>"Cab day draft contract send unsuccessfully"));
                        exit();
                    }
                }

            }else{
                echo json_encode(array('status' => false, 'error' => "draft contract data not exist"));
                exit();
            }

        }else{
            echo json_encode($response);
            exit();
        }
    }
    private function validate_send_draft_contract_data($task_data){
        try {
            $validation_rules = array(
                array('field' => 'contract_file_id', 'label' => 'contract file id', 'rules' => 'required|greater_than[0]'),
                array('field' => 'applicant_id', 'label' => 'Applicant id', 'rules' => 'required|greater_than[0]'),
                array('field' => 'interview_type_id', 'label' => 'interview type', 'rules' => 'required|greater_than[0]'),
                array('field' => 'contract_send_mail', 'label' => 'contract mail', 'rules' => 'required|trim|valid_email'),
            );
            $this->form_validation->set_data($task_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
        } catch (Exception $e) {
            $return = array('status' => false, 'error' => system_msgs('something_went_wrong'));
        }
        return $return;
    }
    public function view_recruitment_presentation(){
        $reqData = (array)  request_handler();
        $response = $this->validate_recruitment_presentation($reqData);
        if($response['status']){
            $response_data=[];
            $interview_type=addslashes($reqData['interview_type_id']);
            $presentation_type=addslashes($reqData['presentation_type']);
            if($presentation_type==1){
                $response_data=$this->Recruitment_member_model->get_recruitment_presentation($interview_type);
                $presentaionFCPath=FCPATH.IPAD_DEVICE_PRESENTATION_PATH.$response_data['file_name'];
            }
            if($presentation_type==2){
                $response_data['file_name']=IPAD_DEVICE_PRESENTATION_FINAL_PPT_PATH;
                $presentaionFCPath=FCPATH.IPAD_DEVICE_PRESENTATION_PATH.IPAD_DEVICE_PRESENTATION_FINAL_PPT_PATH;
            }

            if(file_exists($presentaionFCPath)){
                $presentation_path=base_url('mediaShowProfile/rp/'.urlencode(base64_encode($response_data['file_name'])));
                $response_data['file_path']=$presentation_path;
                echo json_encode(array('status' => true , 'success'=>"get record successfully" ,'data'=>$response_data));
                exit();
            }else{
                echo json_encode(array('status' => false, 'error' => "file not found"));
                exit();
            }
        }else{
            echo json_encode($response);
            exit();
        }

    }
    private function validate_recruitment_presentation($task_data){
        try {
            $this->form_validation->set_message('less_than', 'This %s is not exist ');
            $this->form_validation->set_message('greater_than', 'This %s is not exist ');
            $validation_rules = array(
                array('field' => 'interview_type_id', 'label' => 'interview type', 'rules' => 'required|greater_than[0]|less_than[3]'),
                array('field' => 'presentation_type', 'label' => 'presentation type', 'rules' => 'required'),
            );
            $this->form_validation->set_data($task_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
        } catch (Exception $e) {
            $return = array('status' => false, 'error' => system_msgs('something_went_wrong'));
        }
        return $return;
    }
    public function get_recruitment_pin(){
        $reqData = (array)  request_handler();
        $response = $this->validate_recruitment_pin_data($reqData);
        if($response['status']){
            $applicant_id=addslashes($reqData['applicant_id']);
            $contract_file_id=addslashes($reqData['contract_file_id']);

            $contract_response=$this->Recruitment_member_model->get_contract_file_by_id($contract_file_id);

           /* if(empty($contract_response['envelope_id'])){
                echo json_encode(array('status' => false, 'error' => "Applicant cab day document file not signed"));
                exit();
            }*/
            if($contract_response['signed_status']==0){
                echo json_encode(array('status' => false, 'error' => "Applicant agreement signed is pending"));
                exit();
            }

            $contract_applicant=$this->Recruitment_member_model->get_applicant_pin_data($applicant_id);

            if(!empty($contract_applicant)){
                if(!empty($contract_applicant->participant_pin)){
                    //7= Get Contract Pin
                    $this->Recruitment_member_model->update_ipad_last_stage($reqData,'7');
                    echo json_encode(array('status' => true , 'success'=>"Get participant pin successfully",'data' =>$contract_applicant));
                    exit();
                }
                echo json_encode(array('status' => false , 'error'=>"Participant pin not exist"));
                exit();

            }else{
                echo json_encode(array('status' => false, 'error' => "Participant pin not exist"));
                exit();
            }

        }else{
            echo json_encode($response);
            exit();
        }
    }
    private function validate_recruitment_pin_data($task_data){
        try {
            $validation_rules = array(
                array('field' => 'applicant_id', 'label' => 'Applicant id', 'rules' => 'required|greater_than[0]'),
                //array('field' => 'task_applicant_id', 'label' => 'Task applicant id', 'rules' => 'required|greater_than[0]'),
                array('field' => 'contract_file_id', 'label' => 'contract file id', 'rules' => 'required|greater_than[0]')
            );
            $this->form_validation->set_data($task_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
        } catch (Exception $e) {
            $return = array('status' => false, 'error' => system_msgs('something_went_wrong'));
        }
        return $return;
    }
    public function check_cab_day_document_status(){
        $reqData = (array)  request_handler();

        $response = $this->validate_cab_day_task_data($reqData);
        if($response['status']){

            $task_applicant_id=addslashes($reqData['task_applicant_id']);
            $applicant_id=addslashes($reqData['applicant_id']);

            $applicant_quiz_status=$this->Recruitment_member_model->get_applicant_quiz_status($task_applicant_id);
            if(!$applicant_quiz_status['status']){
                echo json_encode(array('status' => false, 'error' => system_msgs('something_went_wrong')));
                exit();
            }
            $quiz_status=$applicant_quiz_status['data'];

            // quiz_submit_status 0->Pending, 1->Submit , 2->failed
            if($quiz_status['quiz_submit_status']==0){
                echo json_encode(array('status' => false, 'error' => "Quiz submit status is pending"));
                exit();
            }elseif($quiz_status['quiz_submit_status']==2){
                echo json_encode(array('status' => false, 'error' => "Quiz submit status is failed"));
                exit();
            }elseif($quiz_status['quiz_submit_status']==1){
                // quiz_status 0->Pending, 1->successful , 2->unsuccessful
                if($quiz_status['quiz_status']==0){
                    echo json_encode(array('status' => false, 'error' => "Applicant result is awaiting."));
                    exit();
                }
                if($quiz_status['quiz_status']==2){
                    echo json_encode(array('status' => false, 'error' => "Applicant result is unsuccessful."));
                    exit();
                }

                if($quiz_status['document_status']==0){
                    $response_data=$this->Recruitment_member_model->get_cab_day_task_applicant_specific_details($reqData);
                    if(empty($response_data['data']['documentInfo'])){
                        echo json_encode(array('status' => false, 'error' => "Applicant document approval is still pending."));
                        exit();
                    }else{
                        echo json_encode(array('status' => false, 'error' => "Applicant document pending please provide document first."));
                        exit();
                    }
                }
                if($quiz_status['document_status']==2){
                    echo json_encode(array('status' => false, 'error' => "Applicant uploaded document unsuccessful."));
                    exit();
                }
                echo json_encode(array('status' => true));
                exit();

            }else{
                echo json_encode(array('status' => false, 'error' => system_msgs('something_went_wrong')));
                exit();
            }


        }
        echo json_encode($response);
        exit();

    }

    private function resend_cab_day_contract_docusign($contract_data){

        if(empty($contract_data)){
            echo json_encode(array('status' => false,'error' => 'Cab day draft contract doc data required'));
            exit();
        }
        $this->load->library('DocuSignEnvelope');
        $signerDetails=array();
        $signerDetails['name']= $contract_data['applicant_data']['firstname'].' '.$contract_data['applicant_data']['lastname'];
        $signerDetails['email_subject']='Please sign recruitment applicant cab day contract Agreement';
        $signerDetails['email']=$contract_data['email'];

              // Envelope position details
        $position=array();
        $position['position_x']=100;
        $position['position_y']=100;
        $position['document_id']=1;
        $position['page_number']=1;
        $position['recipient_id']=1;

        $envlopDetails=array();
        $envlopDetails['userdetails']=$signerDetails;
        $envlopDetails['position']=$position;
        $envlopDetails['envelopeId']=$contract_data['file_data']['envelope_id'];
        $response=$this->docusignenvelope->ResendEnvelope($envlopDetails);
        return $response;
    }
    public function archive_cab_day_applicant_documents(){
        $reqData = (array)  request_handler();

        $response = $this->validate_archive_applicant_documents($reqData);
        if($response['status']){

            $task_applicant_id=addslashes($reqData['task_applicant_id']);
            $applicant_id=addslashes($reqData['applicant_id']);
            $document_id=addslashes($reqData['document_id']);

            $remove_document=$this->Recruitment_member_model->validate_cab_day_applicant_documents($document_id);
            if(!empty($remove_document)){
                if($remove_document['archive']==1){
                    echo json_encode(array('status' => false, 'error' => "Document all ready archived."));
                    exit();
                }elseif($remove_document['document_status']==0 && $remove_document['archive']==0){
                    $remove_document=$this->Recruitment_member_model->archive_cab_day_applicant_documents($document_id);
                    if($remove_document>0){
                        echo json_encode(array('status' => true, 'success' => "Document archived successfully."));
                        exit();
                    }else{
                        echo json_encode(array('status' => false, 'error' => "Document archive unsuccessfully."));
                        exit();
                    }
                }elseif($remove_document['document_status']==1){
                    echo json_encode(array('status' => false, 'error' => "This document not archived because this document already accepted by admin."));
                    exit();
                }elseif($remove_document['document_status']==2){
                    echo json_encode(array('status' => false, 'error' => "This document not archived because this document already rejected by admin."));
                    exit();
                }
            }else{
                echo json_encode(array('status' => false, 'error' => "Document data not exist."));
                exit();
            }
        }
        echo json_encode($response);
        exit();

    }
    private function validate_archive_applicant_documents($task_data){
        try {
            $validation_rules = array(
                array('field' => 'task_applicant_id', 'label' => 'Task applicant id', 'rules' => 'required|greater_than[0]'),
                array('field' => 'applicant_id', 'label' => 'Applicant id', 'rules' => 'required|greater_than[0]'),
                array('field' => 'document_id', 'label' => 'Document id', 'rules' => 'required|greater_than[0]')
            );
            $this->form_validation->set_data($task_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
        } catch (Exception $e) {
            $return = array('status' => false, 'error' => system_msgs('something_went_wrong'));
        }
        return $return;
    }

    /**
     * Retrieve applicant details. Common use-case is editing existing lead.
     *
     * `POST: /recruitment/Recruitment_member/get_applicant_details`
     *
     * @return CI_Output
     */
    public function get_applicant_details(){
        $reqData = request_handler();
        $reqData = $reqData->data;
        $applicant_info=$this->Recruitment_member_model->get_applicant_info($reqData->applicant_id);
        $response = array('status' => true, 'data' => $applicant_info);
        echo json_encode($response);
        exit();
     }
     /**
     * Get Quiz Details Lists of Current Login User In Aus Time by passing Task Id and APplicant Id
    */
     public function get_quiz_list_old(){
        $reqData = (array)  request_handler();
        $response = $this->validate_task_data($reqData);
        $currtent_date_time = DATE_TIME;
        if($response['status']){
            // $taskId=$reqData['task_id'];
            $applicant_id=$reqData['applicant_id'];
            $interview_type=$reqData['interview_type_id'];
                $this->db->select(['rt.id','rt.start_datetime','rt.end_datetime','rt.task_name','rta.application_id','gc.recruitment_task_applicant_id']);
                $this->db->select("(CASE
                    WHEN (select id from tbl_recruitment_form_applicant rfa where rfa.applicant_id = rta.applicant_id and rfa.application_id = rta.application_id and rfa.form_id = rt.form_id) THEN 'Submitted'
                    WHEN ('".$currtent_date_time."' >= rt.start_datetime AND '".$currtent_date_time."' <= rt.end_datetime) and gc.quiz_submit_status!=3 THEN 'Open'
                    WHEN ('".$currtent_date_time."' >= rt.start_datetime AND '".$currtent_date_time."' <= rt.end_datetime) and gc.quiz_submit_status=3  THEN 'In-Progress'
                    WHEN ('".$currtent_date_time."' > rt.end_datetime) THEN 'Lapsed'
                    Else 'Scheduled' end
                ) as int_status");
                $this->db->from('tbl_recruitment_task rt');
                $this->db->join('tbl_recruitment_task_applicant as rta', 'rt.id = rta.taskId AND rta.archive = 0 and rta.applicant_id = ' . $applicant_id, 'inner');
                $this->db->join('tbl_recruitment_applicant_group_or_cab_interview_detail gc', 'gc.recruitment_task_applicant_id = rta.id and gc.archive=0 and gc.mark_as_no_show=0', 'inner');
                // $this->db->where('rt.id',$taskId);
                $res= $this->db->get();
                $data=$res->result_array();

                $response = array('status' => true, 'data' => $data);
                echo json_encode($response);
                exit();
            }
            else{
                $response = array('status' => false, 'data' => "Some thing went wrong");
                echo json_encode($response);
                exit();
            }
    }
    /**
     * Get Quiz Details Lists of Current Login User In Aus Time by passing Task Id and APplicant Id
    */
    public function get_quiz_list(){
        $reqData = request_handler();
        $reqData = (array)$reqData->data;
        $response = $this->validate_task_data($reqData);
        $currtent_date_time = DATE_TIME;
        if($response['status']){
            $applicant_id=$reqData['applicant_id'];
            $interview_type=$reqData['interview_type_id'];
                $this->db->select(['rt.id','rt.start_datetime','rt.end_datetime','rt.task_name','rta.application_id']);
                $this->db->select("(CASE
                WHEN (select id from tbl_recruitment_form_applicant rfa where rfa.applicant_id = rta.applicant_id and rfa.application_id = rta.application_id and rfa.form_id = rt.form_id and rfa.task_id = rt.id) and rta.applicant_task_status=4 THEN 'Submitted'
                WHEN ('".$currtent_date_time."' >= rt.start_datetime AND '".$currtent_date_time."' <= rt.end_datetime) and  rta.applicant_task_status!=4 and rta.applicant_task_status!=3 THEN 'Open'
                WHEN ('".$currtent_date_time."' >= rt.start_datetime AND '".$currtent_date_time."' <= rt.end_datetime) and  rta.applicant_task_status=3 THEN 'In-Progress'
                WHEN ('".$currtent_date_time."' > rt.end_datetime) THEN 'Expired'
                Else 'Scheduled' end
            ) as int_status");
                $this->db->from('tbl_recruitment_task rt');
                $this->db->join('tbl_recruitment_task_applicant as rta', 'rt.id = rta.taskId AND rta.archive = 0 and rta.applicant_id = ' . $applicant_id, 'inner');
                $this->db->where('task_status >=', 1);
                $res= $this->db->get();
                $data=$res->result_array();
                // 2-open , 3-inprogress, 4-submitted ,5-Expired
                foreach ($data as $val) {
                    if($val['int_status']=='Open'){
                        $upd_data["task_status"] = 2; // Open;
                        $where = array("id"=> $val['id']);
                        $this->basic_model->update_records("recruitment_task", $upd_data, $where);
                        $this->basic_model->update_records("recruitment_task_applicant", ["applicant_task_status"=>2], ["taskId"=>$val['id']]);
                    }
                    if($val['int_status']=='Expired'){
                        $upd_data["task_status"] = 5; // Expired;
                        $where = array("id"=> $val['id']);
                        $this->basic_model->update_records("recruitment_task", $upd_data, $where);
                        $this->basic_model->update_records("recruitment_task_applicant", ["applicant_task_status"=>2], ["taskId"=>$val['id']]);
                    }
                }

                $response = array('status' => true, 'data' => $data);
                echo json_encode($response);
                exit();
            }
            else{
                $response = array('status' => false, 'data' => "Some thing went wrong");
                echo json_encode($response);
                exit();
            }
        }

    
    /*
     * get all reference data to assign to members
     */
     function get_reference_data()
     {
        $reqData = (array)  request_handler();
         $rows["likes"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>2],['id','display_name as label']);
         $rows["language"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>14],['id','display_name as label']);
         $rows["transport"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>19],['id','display_name as label']);
         echo json_encode(["status" => true, "data" => $rows]);
     }

     /*
     * its used for create/update member
     * handle request for create member modal
     */
    function update_member() {
        $reqData = (array)  request_handler();
        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData)) {
            $data = (array) $reqData['data'];
            $member_id = $data['member_id'] ?? 0;
            $adminId = $reqData['adminId'] ;
            # validation rule
            $validation_rules = [
                array('field' => 'hours_per_week', 'label' => 'Hours per week', 'rules' => 'numeric'),
                array('field' => 'max_dis_to_travel', 'label' => 'Max distance to travel', 'rules' => 'numeric'),
                array('field' => 'mem_experience', 'label' => 'Experience', 'rules' => 'numeric'),
            ];
            # set data in libray for validate
            $this->form_validation->set_data($reqData);

            # set validation rule
            $this->form_validation->set_rules($validation_rules);

            # check data is valid or not
            if ($this->form_validation->run()) {

                # call update member modal function
                $updated_member_id = $this->Recruitment_member_model->update_member($data, $member_id, $adminId);

                # check $member_id is not empty
                if ($updated_member_id) {
                    $msg = 'Support worker has been updated successfully.';
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
     * check_the_applicant_is_member
    */
    function check_the_applicant_is_member(){
        $reqData = request_handler();
        $reqData = $reqData->data;
        $applicant_id = sprintf("%d", $reqData->applicant_id);
        if($applicant_id > 0) {
            $rows["applicant_member_info"] = $this->basic_model->get_result('member', ['applicant_id'=> $applicant_id,'archive'=>0],['id','applicant_id','status']);
            echo json_encode(["status" => true, "data" => $rows]);
        } else {
            echo json_encode(["status" => false, "data" => array()]);
        }
    }

    /*
     * update the quiz status as in progess
     */
    function update_applicant_quiz_open_status() {
        $reqData = (array)  request_handler();
        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (!empty($reqData)) {
            $data = (array) $reqData;
            $recruitment_task_applicant_id = $data['task_applicant_id'] ?? 0;
            # validation rule
            $validation_rules = [
                array('field' => 'task_applicant_id', 'label' => 'Recruitment task applicant id', 'rules' => 'required'),
            ];
            # set data in libray for validate
            $this->form_validation->set_data($reqData);
            # set validation rule
            $this->form_validation->set_rules($validation_rules);
            # check data is valid or not
            if ($this->form_validation->run()) {
                # call update member modal function
                $updated_member_id = $this->Recruitment_member_model->update_applicant_quiz_submit_status($recruitment_task_applicant_id,3);
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

    /**
     * Retrieve applicant details. Common use-case is editing existing lead.
     *
     * `POST: /recruitment/Recruitment_member/get_applicant_details`
     *
     * @return CI_Output
     */
    public function get_member_details(){
        $reqData = (array)  request_handler();

        if (empty($reqData['member_id'])) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        $member_info=$this->Member_model->get_member_details($reqData['member_id']);

        echo json_encode($member_info);
        exit();
     }

     public function get_staff_safety_checklist_items() {
        $reqData = request_handler();
        $reqData = $reqData->data;
        $reqData = new stdClass();
        $reqData->data = $data;
        $checklist_info = $this->Opportunity_model->get_staff_safety_checklist_items($reqData);
        $response = ['status' => true, 'data' => $checklist_info['items']];
        echo json_encode($response);
        exit();
     }

     /*
     * its used for create/update shift
     * handle request for create/update shift modal
     */
//     function create_update_shift_portal() {
//         // Get the request data
//         $reqData = request_handler();
//        if (!empty($reqData)) {
//            $data = object_to_array($reqData->data);

//            if($data['actual_travel_duration_hr'] && $data['actual_travel_duration_min']) {
//                $data['actual_travel_duration'] = $data['actual_travel_duration_hr'] . ":"
//                . $data['actual_travel_duration_min'];
//            } else {
//                $data['actual_travel_duration'] = '';
//            }

//            $result = $this->Schedule_model->create_update_shift_portal($data, $data['member_id']);
//            if($result['status']){
//             $result = ['status' => true, 'msg' => 'Time sheet has been completed successfully', 'id' => $result['id']];
//            }else{
//             // $result = ['status' => false, 'msg' => 'Something went wrong'];
//            }
//        } else {
//            $result = ['status' => false, 'error' => 'Requested data is null'];
//        }
//        echo json_encode($result);
//        exit();
//    }

    //Get shift goal tracking details
    function get_shift_goal_tracking_details() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $data = object_to_array($reqData->data);
            $result = $this->Schedule_model->get_shift_goal_tracking_details($data);
        }else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Retrieve shift details.
     */
    public function get_shift_timesheet_attachment_details() {
        $reqData = request_handler();
        $data = $reqData->data;
        $member_id = '';
        if (empty($data->id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        if(isset($data->member_id) && !empty($data->member_id)){
            $member_id = $data->member_id;
        }

        $result = $this->Schedule_model->get_shift_timesheet_attachment_details($data->id, $member_id);
        echo json_encode($result);
        exit();
    }

    /*
     * its used for create/update shift
     * handle request for create/update shift modal
     */
    function create_update_shift_goals_portal() {
        // Get the request data
        $reqData = request_handler();
        $reqData = $reqData->data;
       if (!empty($reqData)) {
           $data = object_to_array($reqData);
           $result = $this->Schedule_model->add_update_goal_tracking($data, $data['member_id']);
       } else {
           $result = ['status' => false, 'error' => 'Requested data is null'];
       }
       echo json_encode($result);
       exit();
   }

}
