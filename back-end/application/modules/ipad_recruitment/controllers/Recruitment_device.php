<?php

defined('BASEPATH') OR exit('No direct script access allowed');
//include APPPATH . 'Classes/admin/jwt_helper.php';

//class Master extends MX_Controller
class Recruitment_device extends MX_Controller {

    use formCustomValidation;

    function __construct() {

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Recruitment_device_model');
        $this->load->model('Basic_model');
        $this->load->model('recruitment/Recruitmentform_model');
        $this->load->model('recruitment/Recruitmentformapplicant_model');
        $this->load->model('recruitment/Recruitment_applicant_model');
        $this->load->model('recruitment/Recruitment_member_model');
        $this->load->helper('i_pad');
        $this->form_validation->CI = & $this;

        if('ipad_valid_login'== $this->uri->segment(3)){

        }else{
            $reqData=api_request_handler();
            auth_login_status($reqData);
        }

    }

    public function update_applicant_info(){		
       $reqData=(array) api_request_handler();      
       $response = $this->validate_applicant_data($reqData);     
       if($response['status']){
            $response_data=$this->Recruitment_device_model->update_applicant_profile_info($reqData);
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
   $state_list=$this->Recruitment_device_model->state_list();
   $response = array('status' => true, 'data' => $state_list);
   echo json_encode($response);
   exit();
}

/**
 * validating applicant/member app login
 * replaced PIN with Password in HCM-3083
 */
public function ipad_valid_login(){
    $reqData=(array) api_request_handler();      
    $response = $this->Recruitment_member_model->validate_applicant_member_login($reqData);
    echo $response;
    exit();
}

public function get_question_list(){
    $reqData=(array) api_request_handler();      
    $response = $this->validate_task_data($reqData); 
    
    if($response['status']){
        $taskId=$reqData['task_id'];
        $applicant_id=$reqData['applicant_id']; 
        $interview_type=$reqData['interview_type_id'];
        $available_timing=$this->Recruitment_device_model->check_exam_start_remaining_timing_available($applicant_id,$taskId);
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
                $this->Recruitment_device_model->update_ipad_last_stage($reqData,'2');
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
        $reqData=(array) api_request_handler();

        $recruitment_task_applicant_id = $this->Recruitment_device_model->get_applicant_details_by_token($reqData);
        if($recruitment_task_applicant_id==0){
            return ['status' => false, 'error' => "recruitment task applicant id is empty"];
        }

        if(!isset($reqData['questions']) || empty($reqData['questions'])){
            echo json_encode(["status"=>false,"error"=>"no questions submitted"]);
            exit();
        }

        # checking if the quiz is submitted or not?
        $get_status_arr=$this->Recruitment_device_model->get_applicant_quiz_submit_status($recruitment_task_applicant_id);
        if(!isset($get_status_arr['status']) || $get_status_arr['status'] != 1){
            echo json_encode(["status"=>false,"error"=>"no questions submitted"]);
            exit();
        }

        $interview_type = $this->Recruitment_device_model->get_type_of_interview($recruitment_task_applicant_id);
        if(!$interview_type) {
            echo json_encode(["status"=>false,"error"=>"no interview type found"]);
            exit();
        }
        $answer_data['interview_type'] = $interview_type;

        # fetching the form id, applicant id and application id from task applicant id
        $form_data = $this->Recruitment_device_model->get_applicant_form_id_application_id($recruitment_task_applicant_id);
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
        $this->Recruitment_device_model->update_applicant_quiz_submit_status($recruitment_task_applicant_id);

        # marking the ipad last stage as completed
        $this->Recruitment_device_model->update_ipad_last_stage($reqData,'3');

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
                //array('field' => 'applicant_phone', 'label' => 'Applicant phone', 'rules' => 'required|min_length[8]|max_length[18]|callback_check_phone_already_exist_to_another_applicant['.$applicant_data['applicant_id'].']'),
            array('field' => 'applicant_address', 'label' => 'Applicant id', 'rules' => 'callback_check_recruitment_applicant_address[applicant_address]'),            
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
    
    public function validate_device_data($device_data){
        try {
            $validation_rules = array(
                array('field' => 'email', 'label' => 'Applicant email', 'rules' => 'required|valid_email'),
                array('field' => 'device_pin', 'label' => 'Applicant device pin', 'rules' => 'required|min_length[8]|max_length[8]'),
            );
        //|callback_phone_number_check[applicant_phone,reuired,Applicant contact info should be enter valid phone number.]
            $this->form_validation->set_data($device_data);
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

    public function validate_task_data($task_data){
        try {
            $validation_rules = array(
                array('field' => 'task_id', 'label' => 'Task id', 'rules' => 'required|greater_than[0]'),
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
        $reqData=(array) api_request_handler(); 

        $response = $this->validate_cab_day_task_data($reqData);     
        if($response['status']){

            $task_applicant_id=addslashes($reqData['task_applicant_id']);
            $applicant_id=addslashes($reqData['applicant_id']);

            $applicant_quiz_status=$this->Recruitment_device_model->get_applicant_quiz_status($task_applicant_id);      
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
                $this->Recruitment_device_model->update_ipad_last_stage($reqData,'4');
                echo json_encode($response_data=$this->Recruitment_device_model->get_cab_day_task_applicant_specific_details($reqData));
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
        $reqData=(array) api_request_handler();
        $applicant_id=addslashes($reqData['applicant_id']);
        $response_data=$this->Recruitment_device_model->remove_token($applicant_id);
        echo json_encode(array('status' => true,"success"=>"logout successfully"));
    }

    public function view_draft_contract(){
        $reqData=(array) api_request_handler();
        
        $response = $this->validate_draft_contract_data($reqData);     
        if($response['status']){
            $task_applicant_id=addslashes($reqData['task_applicant_id']);
            $applicant_id=addslashes($reqData['applicant_id']);
            $interview_type=addslashes($reqData['interview_type_id']);

            $applicant_quiz_status=$this->Recruitment_device_model->get_applicant_quiz_status($task_applicant_id);      
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

                $response_data=$this->Recruitment_device_model->get_draft_contract_data($task_applicant_id);               
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
                            $this->Recruitment_device_model->update_ipad_last_stage($reqData,'5');
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
        
        $reqData=(array) api_request_handler();
        $response = $this->validate_send_draft_contract_data($reqData);     
        if($response['status']){
            $contract_file_id=addslashes($reqData['contract_file_id']);
            $applicant_id=addslashes($reqData['applicant_id']);
            $interview_type=addslashes($reqData['interview_type_id']);
            $contract_email=addslashes($reqData['contract_send_mail']);
            $contract_response=$this->Recruitment_device_model->get_contract_file_by_id($contract_file_id); 
            $contract_applicant=$this->Recruitment_device_model->get_applicant_details($applicant_id);
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
                                
                                $this->Recruitment_device_model->update_ipad_last_stage($reqData,'6');
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
        $reqData=(array) api_request_handler();
        $response = $this->validate_recruitment_presentation($reqData);     
        if($response['status']){                      
            $response_data=[];
            $interview_type=addslashes($reqData['interview_type_id']);
            $presentation_type=addslashes($reqData['presentation_type']);
            if($presentation_type==1){
                $response_data=$this->Recruitment_device_model->get_recruitment_presentation($interview_type);   
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
        $reqData=(array) api_request_handler();
        $response = $this->validate_recruitment_pin_data($reqData);     
        if($response['status']){          
            $applicant_id=addslashes($reqData['applicant_id']); 
            $contract_file_id=addslashes($reqData['contract_file_id']);
            
            $contract_response=$this->Recruitment_device_model->get_contract_file_by_id($contract_file_id); 

           /* if(empty($contract_response['envelope_id'])){
                echo json_encode(array('status' => false, 'error' => "Applicant cab day document file not signed"));
                exit();  
            }*/
            if($contract_response['signed_status']==0){
                echo json_encode(array('status' => false, 'error' => "Applicant agreement signed is pending"));
                exit();  
            }
            
            $contract_applicant=$this->Recruitment_device_model->get_applicant_pin_data($applicant_id);
            
            if(!empty($contract_applicant)){ 
                if(!empty($contract_applicant->participant_pin)){
                    //7= Get Contract Pin
                    $this->Recruitment_device_model->update_ipad_last_stage($reqData,'7');
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
        $reqData=(array) api_request_handler(); 
        
        $response = $this->validate_cab_day_task_data($reqData);     
        if($response['status']){

            $task_applicant_id=addslashes($reqData['task_applicant_id']);
            $applicant_id=addslashes($reqData['applicant_id']);
            
            $applicant_quiz_status=$this->Recruitment_device_model->get_applicant_quiz_status($task_applicant_id);      
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
                    $response_data=$this->Recruitment_device_model->get_cab_day_task_applicant_specific_details($reqData);
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
        $reqData=(array) api_request_handler(); 

        $response = $this->validate_archive_applicant_documents($reqData);     
        if($response['status']){

            $task_applicant_id=addslashes($reqData['task_applicant_id']);
            $applicant_id=addslashes($reqData['applicant_id']);
            $document_id=addslashes($reqData['document_id']);

            $remove_document=$this->Recruitment_device_model->validate_cab_day_applicant_documents($document_id);
            if(!empty($remove_document)){                    
                if($remove_document['archive']==1){
                    echo json_encode(array('status' => false, 'error' => "Document all ready archived."));
                    exit();
                }elseif($remove_document['document_status']==0 && $remove_document['archive']==0){
                    $remove_document=$this->Recruitment_device_model->archive_cab_day_applicant_documents($document_id);
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

}
