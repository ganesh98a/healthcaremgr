<?php

require_once APPPATH . 'Classes/admin/permission.php';
defined('BASEPATH') OR exit('No direct script access allowed');

class Recruitment_member_model extends CI_Model {

    public function update_applicant_profile_info($participant){ 
        $this->db->trans_begin();
        $phone_response=$this->update_applicant_phone_and_dob($participant['applicant_id'],$participant['applicant_phone'],$participant['person_id'],$participant['date_of_birth']);
       //update the applicant address
        $res_address= $this->update_applicant_address($participant['applicant_id'],$participant['applicant_address'],$participant['unit_number']); 
        //update the person address
        // $this->update_person_address($participant['person_id'],$participant['applicant_address'],$participant['unit_number']);   
        $this->update_applicant_avatar($participant['person_id'], $participant['avatar']);
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return ['status'=>FALSE];
        }
        else
        {
            $this->db->trans_commit();
            return ['status'=>True,'address_id'=>$res_address];
        }
    }

    public function update_applicant_phone_and_dob($applicant_id,$applicant_phone,$person_id, $date_of_birth){       
        $this->basic_model->update_records('recruitment_applicant_phone',array('phone' => $applicant_phone), array('applicant_id' => $applicant_id));    
        
        // TODO: Maybe update tbl_person_phone as well
        $this->basic_model->update_records('person_phone',array('phone' => $applicant_phone), array('person_id' => $person_id));    
        
       //Update DOB in applicant and also person table
        if(!empty($date_of_birth) && !empty($person_id)){
            $this->basic_model->update_records('person',array('date_of_birth' => $date_of_birth), array('id' => $person_id));
            $this->basic_model->update_records('recruitment_applicant',array('dob' => $date_of_birth), array('id' => $applicant_id));
        }

    }
    public function update_applicant_email($applicant_id,$applicant_email){      
        $this->basic_model->update_records('recruitment_applicant_email',array('email' => $applicant_email), array('applicant_id' => $applicant_id));  
        
        // TODO: Maybe update tbl_person_email as well
    }
    public function update_applicant_address($applicant_id,$address, $unit_number) 
    {
        if (!empty($address)) {
            $this->load->model("recruitment/Recruitment_applicant_model");
            $id = $this->Recruitment_applicant_model->update_applicant_address($address, $applicant_id, $unit_number);
            return ($id)? $id: false;
        }
        return false;
    }
    // update the address in contact by person id
    public function update_person_address($person_id,$address,$unit_number) 
    {
        // include peson class for create person of contact
        require_once APPPATH . 'Classes/person/person.php';
        $objPerson = new PersonClass\Person();
        if (!empty($person_id)) {
            $objPerson->setPersonId($person_id);

            $addr = [];
            if (!empty($address)) {
                $addr[] = [
                    'unit_number' => !empty($unit_number) ? $unit_number : '',
                    'street' => $address->street ?? '',
                    'state' => !empty($address->state) ? $address->state : null, 
                    'suburb' => $address->city ?? '',
                    'postcode' => $address->postal ?? '',
                ];
                // create / delete of person (contact )
                $objPerson->setPersonAddress($addr);
                $objPerson->insertAddress();
            }        
        }   
    }

function state_list() {
    $where_array['archive'] = '0';
    $columns = array('id','name');
    $this->db->select($columns);
    $this->db->where($where_array);
    $this->db->from('tbl_state');        
    $res = $this->db->get();
    return $res->result_array();
}

/**
 * validating login details of applicant/member app
 */
public function validate_device_data($device_data){
    try {
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        $validation_rules = array(
            array('field' => 'email', 'label' => 'Applicant email', 'rules' => 'required|valid_email'),
            array('field' => 'device_pin', 'label' => 'Applicant password', 'rules' => 'required'),
        );
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

/**
 * validating applicant/member app login
 * replaced PIN with Password in HCM-3083
 */
public function validate_applicant_member_login($applicant_data) {

    // validating login fields
    $val_login = $this->validate_device_data($applicant_data);
    if($val_login && $val_login['status'] == false)
    return json_encode($val_login);

    $this->load->model('recruitment/Recruitment_applicant_model');
    $result = $this->Recruitment_applicant_model->auth_applicant_info($applicant_data['email']);
    $is_member = false;
    if (empty($result)) {
        $result = $this->Recruitment_applicant_model->auth_member_info($applicant_data['email']);
        $is_member = true;
        if(empty($result)){
            $is_member = false;
            return json_encode(["status"=>false,"error"=>"Login failed"]);
        } else {
            if(empty($result->member_status)){
                return json_encode(["status"=>false,"error"=>"Thanks for your assistance during the Pilot Phase. Your login is now deactivated by admin."]);
            }
        }        
    } else {
        if(!empty($result->member_id) && empty($result->member_status)){
            return json_encode(["status"=>false,"error"=>"Thanks for your assistance during the Pilot Phase. Your login is now deactivated by admin."]);
        }
    }

    // check password using PASSWORD_BCRYPT method
    if (!password_verify($applicant_data['device_pin'], $result->password)) {
        return json_encode(["status"=>false,"error"=>"Login failed"]);
    }

    $applicant_info = (array) $result;
    $token = generate_token($applicant_info['username']);
    $response_data['token'] = $token;
    if($is_member){
        $response_data['member_id'] = $applicant_info['member_id'];
    }else{
        $response_data['applicant_id'] = $applicant_info['id'];
    }
    

    # fetching the next available interview details, this is needed due to old way of storing token
    # and validating it. If the next interview details are not found, passing all data points as blank.
    if(!$is_member){
        $int_details = $this->get_next_interview_details($applicant_info['id']);
            if($int_details) {
                $response_data['task_applicant_id'] = $int_details['task_applicant_id'];
                $response_data["interview_type_id"] = $int_details['interview_type_id'];
                $response_data['task_id'] = strval($int_details['task_id']);
                $response_data['task_name'] = $int_details['task_name'];
                $response_data["interview_type"] = $int_details['interview_type'];
                $response_data['task_start_time'] = $int_details['task_start_time'];
                $response_data['ipad_last_stage'] = $int_details['ipad_last_stage'];
                $response_data["is_signed"] = $int_details['is_signed'];
                $response_data['contract_file_id'] = $int_details['contract_file_id'];
                $response_data['time_zone'] = $int_details['time_zone'];
            }
            else {
                $response_data['task_applicant_id'] = 0;
                $response_data["interview_type_id"] = 0;
                $response_data["task_id"] = "0";
                $response_data['task_name'] ="";
                $response_data["interview_type"] = "";
                $response_data['task_start_time'] = "";
                $response_data['ipad_last_stage'] = 0;
                $response_data["is_signed"] = 0;
                $response_data['contract_file_id'] = 0;
                $response_data['time_zone'] = "Australia/Melbourne";
            }           
    }
    $response_data['applicant_info'] = $applicant_info;    
    $result = $this->Recruitment_member_model->insert_token_details($response_data, $is_member);
    if(!$result){
        return json_encode(["status"=>false,"error"=> system_msgs('something_went_wrong')]);
    }
    return json_encode(["status"=>true,"success"=>"login successfully","data" => $response_data]);
}

/**
 * when logging in from ipad, we need to fetch the task_id, interview_type_id and task_applicant_id
 * for it to work properly
 */
public function get_next_interview_details($applicant_id) {
    $tbl_rec_app_group_cab_int_detail=TBL_PREFIX.'recruitment_applicant_group_or_cab_interview_detail as interview';       
    $tbl_rec_task=TBL_PREFIX.'recruitment_task as task';
    $tbl_rec_task_app=TBL_PREFIX.'recruitment_task_applicant as rta';
    $tbl_rec_app=TBL_PREFIX.'recruitment_applicant as ra';
    $tbl_rec_app_email=TBL_PREFIX.'recruitment_applicant_email as rae';
    $tbl_rec_interview_type=TBL_PREFIX.'recruitment_interview_type as rit';

    $coulumns_rec_task=array('task.id as task_id','task.task_name','rta.id as task_applicant_id','rta.applicant_id','interview.interview_type as interview_type_id','rit.name as interview_type','interview.device_pin','task.start_datetime as task_start_time','interview.ipad_last_stage as ipad_last_stage','task.status as task_complete','ra.person_id', '0 as is_signed', '0 as contract_file_id', '\'Australia/Melbourne\' as time_zone');
    $query = $this->db->select($coulumns_rec_task);
    $this->db->from($tbl_rec_task);
    $this->db->join($tbl_rec_task_app,'rta.taskId = task.id  and rta.status=1', 'inner'); // and task.status=1
    $this->db->join($tbl_rec_app_group_cab_int_detail, 'interview.recruitment_task_applicant_id = rta.id and interview.archive=0 and interview.quiz_submit_status = 0 and interview.mark_as_no_show=0', 'inner');
    $this->db->join($tbl_rec_app, 'ra.id = rta.applicant_id and ra.archive=0', 'inner');
    $this->db->join($tbl_rec_app_email,'rae.applicant_id = ra.id and rae.archive=0 and rae.primary_email=1', 'inner');
    $this->db->join($tbl_rec_interview_type,'rit.id = interview.interview_type', 'inner');
    $this->db->where("rta.applicant_id",$applicant_id);
    $this->db->where("task.end_datetime > CURRENT_TIMESTAMP()");
    $this->db->order_by('task.start_datetime','ASC');

    $this->db->limit(1);
    $res = $this->db->get();
    $res_array=$res->row_array();
    if($res->num_rows()>0){
        return $res->row_array();  
    }
    return [];
}
    
    public function get_applicant_info($applicant_id){
        $applicant_id=addslashes($applicant_id);
        $applicant_info=[];
        $applicant_id = $this->db->escape_str($applicant_id, true);
        $sql="SELECT applicant_id, email AS applicant_email, (SELECT middlename FROM tbl_recruitment_applicant WHERE id=$applicant_id AND archive=0) as middlename,
        (SELECT previous_name FROM tbl_recruitment_applicant WHERE id=$applicant_id AND archive=0) as previous_name,
        (SELECT phone FROM tbl_recruitment_applicant_phone WHERE applicant_id=$applicant_id  AND archive=0 AND primary_phone=1 order by id desc limit 1) as applicant_phone , 
        (SELECT GROUP_CONCAT(`firstname`,' ',`lastname` ) applicant_name FROM tbl_recruitment_applicant WHERE id=$applicant_id
        AND archive=0 ) as applicant_name, (SELECT  firstname FROM tbl_recruitment_applicant WHERE id=$applicant_id
        AND archive=0) as firstname, 
        (SELECT  person_id FROM tbl_recruitment_applicant WHERE id=$applicant_id
        AND archive=0) as person_id,
        (SELECT  lastname FROM tbl_recruitment_applicant WHERE id=$applicant_id
        AND archive=0) as lastname ,
        (SELECT  dob FROM tbl_recruitment_applicant WHERE id=$applicant_id
        AND archive=0) as date_of_birth 
         FROM `tbl_recruitment_applicant_email` WHERE 
        tbl_recruitment_applicant_email.applicant_id=$applicant_id AND primary_email=1 AND archive=0 order by id desc limit 1";
        $exe_query = $this->db->query($sql);
        $rows = $exe_query->num_rows();        
        if($rows > 0){
            $records = $exe_query->row_array();  
            $applicant_info=$records;                         

            $this->load->model("recruitment/Recruitment_applicant_model");
            $applicant_info['applicant_address'] = $this->Recruitment_applicant_model->get_applicant_address($applicant_id, 1);
            if (isset($applicant_info['applicant_address'])) {
                $applicant_info['applicant_address']->state_name = $applicant_info['applicant_address']->stateName;
            }
            $applicant_info['avatar'] = '';
            if (!empty($applicant_info['person_id'])) {
                $row = $this->db->query("SELECT profile_pic FROM tbl_person WHERE id=".$applicant_info['person_id'])->row();
                $applicant_info['avatar'] = $row->profile_pic;
            }
        }
        return $applicant_info;           
    }


    // Get question list and save to table tbl_recruitment_additional_questions_for_applicant
    public function check_question_assigned_to_applicant($applicant_data){

        $this->db->select(array("question_id"));
        $this->db->from("tbl_recruitment_additional_questions_for_applicant");
        $this->db->where([ "recruitment_task_applicant_id" => $applicant_data['task_applicant_id'], "archive" => 0]);
        $res= $this->db->get();
        $res_array=$res->row_array();           
        if(!empty($res_array)){
            return true;
        }else{
            return false;
        }
    }
    public function get_question_assigned_applicant($applicant_data){
        $applicant_id=$applicant_data['applicant_id']; 

        $application_id = 0;
        if (isset($applicant_data['application_id']) && !empty($applicant_data['application_id'])) {
            $application_id = $applicant_data['application_id'];
        }

        $applicant_stage=$this->get_applicant_currunt_stage($applicant_id, $application_id);

        // if stage 3 then traning category is Group Interview there id is 1 get all question        
        if($applicant_stage=='group_interview'){
            $training_category=1;        
            return $this->device_question_list_by_stage($applicant_data,$training_category);
        }

        // if stage 6 then traning category is Cab Day Interview there id is 2 and max question is 5
        if($applicant_stage=='cab_day'){
            $training_category=2;
            $limit=CABDAY_INTERVIEW_QUESTION_LIMIT;
            return $this->device_question_list_by_stage($applicant_data,$training_category,$limit);            
        }
        return ['status' => false, 'error' => "No records found"];
    }

    public function device_question_list_by_stage($applicant_data,$training_category,$limit=0){
        $taskId=addslashes($applicant_data['task_id']);
        $applicant_id=addslashes($applicant_data['applicant_id']); 
        $interview_type=addslashes($applicant_data['interview_type_id']);

        if (isset($applicant_data['application_id']) && !empty($applicant_data['application_id'])) {
            $application_id = $applicant_data['application_id'];
            $data = $this->basic_model->get_row($table_name = 'recruitment_task_applicant', $columns = array('id'),$id_array = array('taskId'=>$taskId,'applicant_id'=>$applicant_id, 'application_id' => $application_id));
        } else {
            // @deprecated
            $data = $this->basic_model->get_row($table_name = 'recruitment_task_applicant', $columns = array('id'),$id_array = array('taskId'=>$taskId,'applicant_id'=>$applicant_id));
        }
        
        $recruitment_task_applicant_id = $data->id;
        if($recruitment_task_applicant_id>0){
            $recruitment_task_applicant_id = $this->db->escape_str($recruitment_task_applicant_id, true);
            $training_category = $this->db->escape_str($training_category, true);
            $taskId = $this->db->escape_str($taskId, true);
            $this->db->select(array('raqfa.id','raqfa.created_by','raqfa.created','qtopic.topic','raqfa.is_required'));

            $this->db->select(" GROUP_CONCAT(DISTINCT raqa.answer SEPARATOR  '@#_BREAKER_#@') as answer_correct", false);
            $this->db->select(" GROUP_CONCAT(DISTINCT raqa.id SEPARATOR  '@#_BREAKER_#@') as answer_option_id", false);
            $this->db->select(" GROUP_CONCAT(DISTINCT raqa.question_option SEPARATOR  '@#_BREAKER_#@') as answer_option", false);
            $this->db->select(" GROUP_CONCAT(DISTINCT  raqa.serial SEPARATOR  '@#_BREAKER_#@') as que_serial,raqfa.question, raqfa.question_type, raqfa.question_topic as question_topic",false);
            $this->db->from('tbl_recruitment_additional_questions as raqfa');
            $this->db->join('tbl_recruitment_additional_questions_answer as raqa', "raqa.question = raqfa.id", 'inner');
            $this->db->join('tbl_recruitment_question_topic as qtopic', 'qtopic.id = raqfa.question_topic', 'inner');
            $this->db->where("NOT EXISTS (SELECT nass.question_id
                FROM tbl_recruitment_applicant_not_assign_question as nass
                WHERE  nass.question_id = raqfa.id AND nass.archive = 0 and nass.recruitment_task_applicant_id=$recruitment_task_applicant_id)",null,false);

            #if($training_category == 1)
            {
                #$this->db->join('tbl_recruitment_form as frm', 'frm.id = raqfa.form_id AND frm.archive=0 AND frm.interview_type=1', 'inner');
                $this->db->join('tbl_recruitment_task as tsk', 'tsk.form_id = raqfa.form_id AND tsk.id='.$taskId, 'inner');
            }

            $this->db->where(array('raqfa.archive'=> 0,'raqfa.status'=> 1, 'raqfa.training_category' => $training_category, 'raqa.archive'=>0));
            
            $this->db->group_by('raqfa.id');
            $this->db->order_by('raqfa.display_order','ASC');
            if($limit>0)
                $this->db->limit($limit);

            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $rows = $query->num_rows();

            if($rows > 0){
                $records = $query->result_array();        
                $main_aray = [];

                foreach ($records as $key => $value)
                {   
                   /* $temp['id']=$value['id'];
                    $temp['question'] = $value['question'];            
                    $temp['question_type'] = $value['question_type'];                    
                    //if($value['question_type']!=4){                        
                        $answer_option_id = explode('@#_BREAKER_#@', $value['answer_option_id']);
                        $answer_option = explode('@#_BREAKER_#@', $value['answer_option']);                       
                        $answer_option_combine=array_combine($answer_option_id,$answer_option); 
                        $arrAnswer=[];
                        foreach($answer_option_combine as $oKey=>$oValue){
                            $optAnswer=[];
                            $optAnswer['id']=$oKey;
                            $optAnswer['option']=$oValue;
                            $optAnswer['answered']=false;
                            $arrAnswer[]=$optAnswer;
                        }                        
                        $temp['answer_option']=$arrAnswer;
                   // }
                    $main_aray[$value['question_topic']]['id'] = $value['question_topic'];
                    $main_aray[$value['question_topic']]['name'] = $value['topic'];
                    $main_aray[$value['question_topic']]['question_ary'][] = $temp; */
                    $this->copy_additional_questions($value['id'],$recruitment_task_applicant_id);
                }
                return ['status' => true]; //, 'data' => array("questions"=>$main_aray)];    
            }else{
                return ['status' => false, 'error' => "No records found"];
            } 
        }else{
            return ['status' => false, 'error' => "Recruitment task applicant id not found"];
        }
    }



    public function device_question_list($applicant_data){
        $applicant_id=$applicant_data['applicant_id'];
        if (isset($applicant_data['application_id']) && !empty($applicant_data['application_id'])) {
            $application_id = $applicant_data['application_id'];
        }

        return $this->get_device_question_list_by_stage($applicant_data);
    }

    public function get_device_question_list_by_stage($applicant_data,$training_category="",$limit=0){

        $taskId=addslashes($applicant_data['task_id']);
        $applicant_id=addslashes($applicant_data['applicant_id']); 
        $interview_type=isset($applicant_data['interview_type_id'])?addslashes($applicant_data['interview_type_id']):0;

        if (isset($applicant_data['application_id']) && !empty($applicant_data['application_id'])) {
            $application_id = $applicant_data['application_id'];
            $data = $this->basic_model->get_row($table_name = 'recruitment_task_applicant', $columns = array('id'),$id_array = array('taskId'=>$taskId,'applicant_id'=>$applicant_id, 'application_id' => $application_id));
        } else {
            // @deprecated
            $data = $this->basic_model->get_row($table_name = 'recruitment_task_applicant', $columns = array('id'),$id_array = array('taskId'=>$taskId,'applicant_id'=>$applicant_id));
        }

        if(!$data || !isset($data->id) || empty($data->id)){
            return ['status' => false, 'error' => "Recruitment task applicant id not found"];
        }

        # fetching the list of questions for currently allocated task
        # this will be common to all applicants within that task
        $this->db->select(array("raq.id as question_id"));
        $this->db->from('tbl_recruitment_additional_questions as raq');
        $this->db->join('tbl_recruitment_task as rt', "raq.form_id = rt.form_id and rt.id = ".$this->db->escape_str($taskId, true), 'inner');
        $this->db->where(array('raq.archive'=>0,'raq.status'=>1));
        $this->db->order_by("raq.display_order", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $rows = $query->num_rows();
        $this->load->model('recruitment/Recruitmentform_model');
        if(!$rows || $rows <= 0){
            return ['status' => false, 'error' => "No records found or questions not assigned"];
        }

        $records = $query->result_array();
        $retarray = null;
        foreach($records as $row) {
            $que_details = $this->Recruitmentform_model->get_question_details($row['question_id']);
            $answer_details = $this->Recruitmentform_model->get_answer_details($row['question_id'],true);
            if($que_details->question_type == 1)                
                $que_details->question_type_str = 'Multiple Choice Answer';
            else if($que_details->question_type == 2) 
                $que_details->question_type_str = 'Single choice answer';
            else if($que_details->question_type == 3) 
                $que_details->question_type_str = 'True/False';
            else if($que_details->question_type == 4) 
                $que_details->question_type_str = 'Short answers';
            else
                $que_details->question_type_str = '';
            
            $single_row = new stdClass();
            $single_row->id = $que_details->id;
            $single_row->question = $que_details->question;
            $single_row->question_type = $que_details->question_type;
            $single_row->question_type_str = $que_details->question_type_str;
            $single_row->is_required = $que_details->is_required;
            $single_row->display_order = $que_details->display_order;
            $new_answer_details = null;

            # generate the format the ipad understands
            if($answer_details) {
                foreach ($answer_details as $ansrow) {
                    $newansrow = null;
                    $newansrow['id'] = (int)$ansrow['answer_id'];
                    $newansrow['option'] = $ansrow['value'];
                    $newansrow['answered'] = false;
                    $new_answer_details[] = $newansrow;
                }
            }
            else if($que_details->question_type == 4) {
                # if answer key is not provided for short answer questions then, generate a dummy one so iPad can understand
                $newansrow = null;
                $newansrow['id'] = 1;
                $newansrow['option'] = "";
                $newansrow['answered'] = false;
                $new_answer_details[] = $newansrow;
            }
            $single_row->answer_option = $new_answer_details;
            $retarray[] = $single_row;
        }
        // pr($retarray);
        return ['status' => true, 'data' => array("questions"=>$retarray)];
    } 

    public function submit_answer_list($answer_data){
        $recruitment_task_applicant_id = $this->get_applicant_task_id($answer_data);
        if($recruitment_task_applicant_id==0){
            return ['status' => false, 'error' => "recruitment task applicant id is empty"];
        }

        if(isset($answer_data['questions'])){
            // UPDATE `tbl_recruitment_additional_questions_for_applicant` SET `is_answer_correct`=0
            $get_status_arr=$this->get_applicant_quiz_submit_status($recruitment_task_applicant_id);
            // pr($answer_data,0);

            if($get_status_arr['status']){

                $form_data = $this->get_applicant_form_id_application_id($recruitment_task_applicant_id);
                if($form_data) {
                    $answer_data['form_id'] = $form_data['form_id'];
                    $answer_data['application_id'] = $form_data['application_id'];
                }

                if($get_status_arr['data']==0){
                    foreach($answer_data['questions'] as $key=>$question_ary){
                        // $question_topic_id=$value->id;
                        // $question_topic=$value->name;
                        //foreach($question_ary as $qkey=>$qvalue){ 
                        $question_id=$question_ary->id;
                        $question=$question_ary->question;
                        $question_type=$question_ary->question_type;
                        $answered=$question_ary->answer_option; 

                        if($question_type==4){                    
                            $answer_list=$answered[0]->option;
                            $answer_status=0;
                        }else{
                            $isanswer=$this->check_submit_answer_is_correct($question_id,$answered);
                            $answer_status=($isanswer['status'])?1:2;
                                // $answer_list=implode(',',$isanswer['answer_val']); 
                            $answer_list=implode(',', (array)$isanswer['answer_opt']); 
                        }
                        $where_columns = ["question_id"=> $question_id,"recruitment_task_applicant_id"=>$recruitment_task_applicant_id];
                        $data = ["answer"=>$answer_list,"is_answer_correct"=>$answer_status,"updated"=>DATE_TIME];                    
                        $this->db->update('tbl_recruitment_additional_questions_for_applicant',$data, $where_columns);
                    }
                    $this->update_applicant_quiz_submit_status($recruitment_task_applicant_id,1);
                    return ['status' => true, "success"=>"record submited successfully"];
                }
                else {
                    return ['status' => false, 'error' => "This applicant allready submitted there question list"];    
                }
            }
            else {
                return ['status' => false, 'error' => "error in applicant quiz submit status"];    
            }
        }
        else {
            return ['status' => false, 'error' => "questions list not available submited successfully"];    
        }
        return ['status' => true, 'error' => "answer submited successfully"];
    }
private function check_submit_answer_is_correct($question_id,$answer_data){

    $answer_ids=[];
    $answer_options=[];    
    foreach($answer_data as $key=>$value){
        if($value->answered){
            $answer_ids[]= $value->id;
            $answer_options[]= $value->option;
        }           
    }        
    if(empty($answer_ids)){
        return ["status"=>false,"answer_val"=>$answer_ids,"answer_opt"=>$answer_options];
    }
    sort($answer_ids);
    $this->db->select(array("GROUP_CONCAT(id) as id"));
    $this->db->from("tbl_recruitment_additional_questions_answer");
    $this->db->where("question", $question_id);
    $this->db->where("answer='1'");
    $this->db->where("archive='0'");
    $this->db->order_by("id", "asc");
    $res= $this->db->get();
    $res_array=$res->row_array();
    $ids=explode(",",$res_array['id']);       
    $answer_status=check_array_equal($ids,$answer_ids);        
    return ["status"=>$answer_status,"answer_val"=>$answer_ids,"answer_opt"=>$answer_options];
}

    /**
     * Gets the `tbl_recruitment_applicant_applied_application.current_stage` 
     * and get the equivalent key in `tbl_recruitment_task_stage.key`. 
     * 
     * @param int $application_id 
     * @return string|0 
     */
    public function get_application_current_stage($application_id)
    {
        $query = $this->db
            ->from('tbl_recruitment_applicant_applied_application AS application')
            ->join('tbl_recruitment_stage AS rs', 'rs.id = application.current_stage AND rs.archive = 0', 'INNER')
            ->join('tbl_recruitment_task_stage AS rts', 'rts.stage_label_id = rs.stage_label_id AND rs.archive = 0','INNER')
            ->where([
                'application.id' => $application_id,
            ])
            ->select([
                'rts.key AS current_stage_key'
            ])
            ->get();

        $result = $query->row_array();

        if (!empty($result)) {
            return $result['current_stage_key'];
        }

        return 0;
    }


// @deprecated. Must find stage key by application ID
public function get_applicant_currunt_stage($applicant_id, $application_id = 0){
    if (!empty($application_id)) {
        return $this->get_application_current_stage($application_id);
    }

    $this->db->select(array("rts.key as current_stage_key"));
    $this->db->from("tbl_recruitment_applicant as req_app");
    $this->db->join('tbl_recruitment_stage as rs','rs.id=req_app.current_stage AND rs.archive=0','inner');
    $this->db->join('tbl_recruitment_task_stage as rts','rts.stage_label_id=rs.stage_label_id AND rs.archive=0','inner');
        //$this->db->join('tbl_recruitment_stage_label as rsl','rsl.stage_number=rs.stage_label_id AND rsl.archive=0','inner');
        //tbl_recruitment_stage

        //tbl_recruitment_stage_label

    $this->db->where("req_app.id", $applicant_id);
    $res= $this->db->get();
    $res_array=$res->row_array();        
    if(!empty($res_array)){
        return $res_array['current_stage_key'];
    } 
    return 0;
}
public function insert_token_details($token_data, $is_member){
    $this->remove_token($token_data, $is_member);
    $data = [        
        "login_token"=>$token_data['token'],       
        "archive"=>0,
        "created"=>DATE_TIME,
        "updated"=>DATE_TIME
    ];
    if($is_member){
        $data['member_id'] = $token_data['member_id'];
        $data['applicant_id'] = 0;       
    }else{
        $data['applicant_id'] = $token_data['applicant_id'];
        $data['task_applicant_id'] = $token_data['task_applicant_id'];
        $data["interviewtype"]= $token_data['interview_type_id'];
    }
    return $result=$this->db->insert('tbl_recruitment_applicant_interview_login',$data);
}
public function remove_token($token_data, $is_member){
    if($is_member){
        $this->db->update('tbl_recruitment_applicant_interview_login',["archive"=>1], array('member_id' => $token_data['member_id']));
    }else{
        $this->db->update('tbl_recruitment_applicant_interview_login',["archive"=>1], array('applicant_id' => $token_data['applicant_id']));
    }
    
}    
public function verify_ipad_token($token_data, $is_member) {    
    if($is_member){
        $member_id=$this->db->escape($token_data['member_id']);
    } else{
        $applicant_id=$this->db->escape($token_data['applicant_id']);
    } 
   
   
  
   $token=$this->db->escape($token_data['token']);
   $this->db->select(array("id"));
   $this->db->from("tbl_recruitment_applicant_interview_login");
   if($is_member){
    $this->db->where("member_id", $member_id);
   }else{
       $this->db->where("applicant_id", $applicant_id);
    }
   
   $this->db->where("login_token", $token);
   $this->db->where("archive='0'");
   $res= $this->db->get();
   $res_array=$res->row_array();
   if(!empty($res_array)){
    return $res_array['id'];
}
return 0;
}
public function get_applicant_task_id($applicant_data){
    $this->db->select(array("rta.id"));
    
    $this->db->from("tbl_recruitment_task as t");
    $this->db->join("tbl_recruitment_task_applicant as rta",'rta.taskId = t.id and rta.status=1', 'inner');
    $this->db->where("rta.applicant_id", $applicant_data['applicant_id']);
    $this->db->where("rta.application_id", $applicant_data['application_id']);
    $this->db->where("t.id=", $applicant_data['task_id']);
    $res= $this->db->get();
    $res_array=$res->row_array();
    if(!empty($res_array)){
        return $res_array['id'];
    }
    return 0;
}

public function get_applicant_task_id_by_status($applicant_data){
    $this->db->select(array("rta.id"));
    
    $this->db->from("tbl_recruitment_task as t");
    $this->db->join("tbl_recruitment_task_applicant as rta",'rta.taskId = t.id and t.task_status>0', 'inner');
    $this->db->where("rta.applicant_id", $applicant_data['applicant_id']);
    $this->db->where("rta.application_id", $applicant_data['application_id']);
    $this->db->where("t.id", $applicant_data['task_id']);
    $res= $this->db->get();
    $res_array=$res->row_array();
    if(!empty($res_array)){
        return $res_array['id'];
    }
    return 0;
}
private function copy_additional_questions($question_id,$recruitment_task_applicant_id){
    $data = ["question_id"=> $question_id,"recruitment_task_applicant_id"=>$recruitment_task_applicant_id , "is_answer_correct"=>0,"archive"=>0,"updated"=>DATE_TIME];
    $result=$this->db->insert('tbl_recruitment_additional_questions_for_applicant',$data);
    $this->update_applicant_quiz_assined_status($recruitment_task_applicant_id);
} 
public function get_applicant_quiz_submit_status($rec_task_app_id){
    $this->db->select(array("quiz_submit_status"));
    $this->db->from("tbl_recruitment_applicant_group_or_cab_interview_detail");
    $this->db->where("recruitment_task_applicant_id", $rec_task_app_id);        
    $res= $this->db->get();
    $res_array=$res->row_array();
    if(!empty($res_array)){
        return ["status"=>true,"data" => $res_array['quiz_submit_status']];
    }else{
        return ["status"=>false];
    }
}
public function update_applicant_quiz_submit_status($recruitment_task_applicant_id, $status){
    $where_app_id_columns = ["recruitment_task_applicant_id"=>$recruitment_task_applicant_id];
    $data = ["quiz_submit_status"=>$status,"updated"=>DATE_TIME];                   
    return $this->db->update('tbl_recruitment_applicant_group_or_cab_interview_detail',$data, $where_app_id_columns);
}

public function update_applicant_quiz_ans_submit_status($recruitment_task_applicant_id, $status){
    $where_app_id_columns = ["recruitment_task_applicant_id"=>$recruitment_task_applicant_id];
    $data = ["quiz_submit_status"=>$status,"updated"=>DATE_TIME];                   
    return $this->db->update('tbl_recruitment_task_applicant',$data, $where_app_id_columns);
}

#This method is not in used
public function get_cab_day_task_applicant_specific_details($applicant_data){
    $applicant_id=$applicant_data['applicant_id']; 

    $application_id = 0;
    if (isset($applicant_data['application_id']) && !empty($applicant_data['application_id'])) {
        $application_id = $applicant_data['application_id'];
    }

    $this->db->select(['radc.id as applicant_doc_id','radc.is_approved','rjrd.id as doc_type_id','rjrd.title as doc_type']);
    $this->db->select(['CASE WHEN radc.is_approved=2 THEN (SELECT id FROM tbl_recruitment_applicant_stage_attachment WHERE applicant_id=radc.applicant_id AND 
        archive=0 AND uploaded_by_applicant=1 AND doc_category=radc.recruitment_doc_id AND document_status=0  ORDER BY id DESC LIMIT 1) ELSE 0 END as attachment_id'
    ],false);
    $this->db->select(['CASE WHEN radc.is_approved=2 THEN (SELECT count(id) FROM tbl_recruitment_applicant_stage_attachment WHERE applicant_id=radc.applicant_id AND 
        archive=1 AND uploaded_by_applicant=1 AND doc_category=radc.recruitment_doc_id AND document_status=2  ORDER BY id DESC LIMIT 1) ELSE 0 END as rejected_doc_count'
    ],false);        
        /*
         * $this->db->select([
            'CASE WHEN radc.is_approved=2 THEN 1 WHEN radc.is_approved=0 THEN 0 ELSE 2 END as outstanding_doc',
            'CASE WHEN radc.is_approved=2 THEN (SELECT attachment FROM tbl_recruitment_applicant_stage_attachment WHERE applicant_id=radc.applicant_id AND archive=0 AND uploaded_by_applicant=1 AND doc_category=radc.recruitment_doc_id AND document_status=0  ORDER BY id DESC LIMIT 1) ELSE 0 END as attachment_data',
            'CASE WHEN radc.is_approved=2 THEN (SELECT id FROM tbl_recruitment_applicant_stage_attachment WHERE applicant_id=radc.applicant_id AND archive=0 AND uploaded_by_applicant=1 AND doc_category=radc.recruitment_doc_id AND document_status=0  ORDER BY id DESC LIMIT 1) ELSE 0 END as attachment_id'
        ],false); */
        $this->db->from('tbl_recruitment_applicant_doc_category as radc');
        $this->db->join('tbl_recruitment_job_requirement_docs as rjrd','radc.recruitment_doc_id=rjrd.id AND radc.archive=rjrd.archive AND radc.archive=0','inner');
        $this->db->where('radc.is_approved',2);
        $this->db->where('radc.applicant_id',$applicant_id);
        
        if (!empty($application_id)) {
            $this->db->where('radc.application_id', $application_id);
        }

        $this->db->having('attachment_id is null or attachment_id=0');
        $query = $this->db->get();       
        $res = $query->result();       
        return ['status'=>true,'data'=>['documentInfo'=>$res]];
    }

    #This method is not called anywhere its void
    public function check_cab_day_task_applicant($applicant_data){
        $applicant_id=$applicant_data['applicant_id']; 

        $application_id = 0;
        if (isset($applicant_data['application_id']) && !empty($applicant_data['application_id'])) {
            $application_id = $applicant_data['application_id'];
        }

        $this->db->select(['rjrd.id as doc_type_id']);
        $this->db->select([            
            'CASE WHEN radc.is_approved=2 THEN (SELECT id FROM tbl_recruitment_applicant_stage_attachment WHERE applicant_id=radc.applicant_id AND archive=0 AND uploaded_by_applicant=1 AND doc_category=radc.recruitment_doc_id AND document_status=0  ORDER BY id DESC LIMIT 1) ELSE 0 END as attachment_id'
        ],false);
        /*
         * $this->db->select([
            'CASE WHEN radc.is_approved=2 THEN 1 WHEN radc.is_approved=0 THEN 0 ELSE 2 END as outstanding_doc',
            'CASE WHEN radc.is_approved=2 THEN (SELECT attachment FROM tbl_recruitment_applicant_stage_attachment WHERE applicant_id=radc.applicant_id AND archive=0 AND uploaded_by_applicant=1 AND doc_category=radc.recruitment_doc_id AND document_status=0  ORDER BY id DESC LIMIT 1) ELSE 0 END as attachment_data',
            'CASE WHEN radc.is_approved=2 THEN (SELECT id FROM tbl_recruitment_applicant_stage_attachment WHERE applicant_id=radc.applicant_id AND archive=0 AND uploaded_by_applicant=1 AND doc_category=radc.recruitment_doc_id AND document_status=0  ORDER BY id DESC LIMIT 1) ELSE 0 END as attachment_id'
        ],false); */
        $this->db->from('tbl_recruitment_applicant_doc_category as radc');
        $this->db->join('tbl_recruitment_job_requirement_docs as rjrd','radc.recruitment_doc_id=rjrd.id AND radc.archive=rjrd.archive AND radc.archive=0','inner');
        $this->db->where('radc.is_approved',2);
        $this->db->where('radc.applicant_id',$applicant_id);

        if (!empty($application_id)) {
            $this->db->where('radc.application_id', $application_id);
        }

        $this->db->having('attachment_id is null or attachment_id=0');
        $query = $this->db->get();
        $res = $query->result();       
        return ['status'=>true,'data'=>['documentInfo'=>$res]];
    }
    
    public function check_exam_start_remaining_timing_available($applicant_id,$task_id){
        //$currunt_datatime=DATE_TIME; // '2019-09-26 19:30:44';
        $this->db->select(['id','start_datetime','end_datetime']);
        $this->db->from('tbl_recruitment_task');        
        $this->db->where('tbl_recruitment_task.id',$task_id);
       // $this->db->where("('".$currunt_datatime."'BETWEEN start_datetime AND end_datetime)");         
        $res= $this->db->get();
        $res_array=$res->row_array();        
        return $res_array;        
    }
    private function update_applicant_quiz_assined_status($recruitment_task_applicant_id){
        $where_app_id_columns = ["recruitment_task_applicant_id"=>$recruitment_task_applicant_id];
        $data = ["allot_question"=>1,"updated"=>DATE_TIME];                   
        $this->db->update('tbl_recruitment_applicant_group_or_cab_interview_detail',$data, $where_app_id_columns);
    }

    function get_draft_contract_data($task_applicant_id){
        $this->db->select(array("contra.unsigned_file as contract_unsigned_file","contra.id as contract_file_id","contra.signed_status as is_signed"));
        $this->db->from("tbl_recruitment_applicant_contract as contra");
        $this->db->where("contra.task_applicant_id", $task_applicant_id);
        $this->db->join('tbl_recruitment_applicant_group_or_cab_interview_detail as idetail', 'idetail.recruitment_task_applicant_id = contra.task_applicant_id');
        $this->db->join('tbl_recruitment_task_applicant as taskapp', 'taskapp.id = idetail.recruitment_task_applicant_id');
        $this->db->where("idetail.quiz_submit_status=1");
        $this->db->where("taskapp.status=1");
        $this->db->where("taskapp.archive=0");
        //$this->db->where("contra.signed_status=0");
        $this->db->where("contra.archive=0");
        $res= $this->db->get();
        return $res_array=$res->row_array();        
    }


    public function get_applicant_details($applicant_id, $application_id = 0){ 

        if (!empty($application_id)) {
            $query = $this->db
                ->from('tbl_recruitment_applicant_applied_application AS application')
                ->join('tbl_recruitment_applicant applicant', 'application.applicant_id = applicant.id', 'INNER')
                // ->join('tbl_person person', 'application.applicant_id = person.id', 'LEFT')
                ->where([
                    'application.id' => $applicant_id,
                    'applicant.id' => $applicant_id,
                    'applicant.archive' => 0,
                ])
                ->select([
                    // 'person.firstname AS firstname',
                    // 'person.lastname AS lastname',
                    'applicant.firstname AS firstname',
                    'applicant.lastname AS lastname',
                    'application.recruiter AS recruiter',
                ])
                ->get();
            
            return $query->row_array();
        }

        $this->db->select(array("firstname","lastname","recruiter"));
        $this->db->from("tbl_recruitment_applicant");
        $this->db->where("archive=0");
        $this->db->where("id", $applicant_id);
        $res= $this->db->get();
        return $res_array=$res->row_array();
    }
    function get_applicant_pin_data($applicant_id){      
        $this->db->select(array("pin as participant_pin"));
        $this->db->from("tbl_recruitment_applicant");
        $this->db->where("archive=0");
        $this->db->where("id", $applicant_id);
        $res= $this->db->get();
        return $res_array=$res->row();
    }

    function get_contract_file_by_id($contract_file_id){
        $this->db->select(array("contra.unsigned_file as contract_unsigned_file","envelope_id","signed_status","signed_file"));
        $this->db->from("tbl_recruitment_applicant_contract as contra");
        $this->db->where("contra.id",$contract_file_id);
        //$this->db->where("contra.signed_status=0");
        $this->db->where("contra.archive=0");
        $this->db->join('tbl_recruitment_applicant_group_or_cab_interview_detail as idetail', 'idetail.recruitment_task_applicant_id = contra.task_applicant_id');
        $this->db->join('tbl_recruitment_task_applicant as taskapp', 'taskapp.id = idetail.recruitment_task_applicant_id');
        $this->db->where("idetail.quiz_submit_status=1");
        $this->db->where("taskapp.status=1");
        $this->db->where("taskapp.archive=0");
        $res= $this->db->get();          
        //last_query();
        return $res_array=$res->row_array();
    }
    function get_recruitment_presentation($interview_type){
        $this->db->select(array("file_name"));
        $this->db->from("tbl_recruitment_applicant_presentation");
        $this->db->where("interview_type", $interview_type);      
        $this->db->where("archive=0");
        $res= $this->db->get();
        return $res_array=$res->row_array();
    }
    function get_applicant_quiz_status($rec_task_app_id){
        $this->db->select(array("quiz_submit_status","quiz_status","document_status"));
        $this->db->from("tbl_recruitment_applicant_group_or_cab_interview_detail");
        $this->db->where("recruitment_task_applicant_id", $rec_task_app_id);        
        $res= $this->db->get();
        $res_array=$res->row_array();
        if(!empty($res_array)){
            return ["status"=>true,"data" => $res_array];
        }else{
            return ["status"=>false];
        }
    }
    function archive_cab_day_applicant_documents($document_file_id){
        $where_file_id_columns = ["id"=>$document_file_id];
        $data = ["archive"=>1,"archive_at"=>DATE_TIME];                   
        return $this->db->update('tbl_recruitment_applicant_stage_attachment',$data, $where_file_id_columns);
    }
    function validate_cab_day_applicant_documents($document_file_id){
        $this->db->select(array("attachment_title","document_status","archive"));
        $this->db->from("tbl_recruitment_applicant_stage_attachment");
       // $this->db->where("document_status=0");      
       // $this->db->where("archive=0");
        $this->db->where("id", $document_file_id);
        $res= $this->db->get();
        return $res->row_array();
    }
    function update_ipad_last_stage($token_data,$stage_id){
        $task_applicant_id=$this->get_applicant_task_id($token_data);
        if($task_applicant_id>0){
            $where_task_applicant_id = ["recruitment_task_applicant_id"=>$task_applicant_id];
            $data = ["ipad_last_stage"=>$stage_id,"updated"=>DATE_TIME];                   
            return $this->db->update('tbl_recruitment_applicant_group_or_cab_interview_detail',$data,$where_task_applicant_id);
        }
    }

    /**
     * function returns a key used for type of interview
     */
    public function get_type_of_interview($rec_task_app_id){
        $this->db->select(array("interview_type"));
        $this->db->from("tbl_recruitment_applicant_group_or_cab_interview_detail");
        $this->db->where("recruitment_task_applicant_id", $rec_task_app_id);        
        $res= $this->db->get();
        $res_array=$res->row_array();
        if(!empty($res_array)){
            if($res_array['interview_type'] == 1)
                return "group_intervew";
            else
                return "cab_intervew";
        }
    }

    /**
     * fetches the form_id, applicant_id and application_id
     */
    function get_applicant_form_id_application_id($task_applicant_id){
        $this->db->select(array("rt.form_id", "rta.application_id", "rta.applicant_id","rta.taskId"));
        $this->db->from("tbl_recruitment_task_applicant as rta");
        $this->db->join('tbl_recruitment_task as rt', 'rt.id = rta.taskId');
        $this->db->where("rta.id", $task_applicant_id);
        $res= $this->db->get();
        return $res_array=$res->row_array();
    }

    public function get_applicant_member_details_by_id($reqData, $is_member = false){
        if (empty($reqData['applicant_id']) && empty($reqData['member_id'])) return;

        $this->db->select("m.id, m.id as member_id, CONCAT(p.firstname,' ',p.lastname) as fullname, p.firstname, p.lastname,,p.middlename,p.previous_name,p.date_of_birth,m.username,m.applicant_id,m.person_id,m.max_dis_to_travel,m.mem_experience,m.hours_per_week,m.created_by,m.updated_by,
        pp.phone,pa.street,pa.suburb,pa.postcode,pa.state,pa.primary_address,pa.unit_number,pa.is_manual_address, pa.manual_address, s.name as state_name");
        $this->db->select("(case when m.person_id > 0 THEN 
            (SELECT CONCAT(p.firstname,' ',p.lastname) from tbl_person p where p.id = m.person_id) ELSE '' END) as contact_name, p.profile_pic as avatar", false);
        $this->db->select("(CASE WHEN m.status = 1 THEN 'Active' 
                ELSE 'Inactive' END) as status_label");

        $this->db->from(TBL_PREFIX . 'member as m');
        $this->db->join('tbl_person p', 'm.person_id = p.id', 'inner');
        $this->db->join('tbl_person_phone pp', 'm.person_id = pp.person_id and pp.archive=0 and pp.primary_phone=1', 'left');
        $this->db->join('tbl_person_address pa', 'm.person_id = pa.person_id and pa.archive=0 and pa.primary_address=1', 'left');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "external_staff"', 'inner');
        $this->db->join('tbl_state as s', 's.id = pa.state', 'left');

        $this->db->group_by('m.id');
        if($is_member){
            $this->db->where("m.id", $reqData['member_id']);
        }else{
            $this->db->where("m.applicant_id", $reqData['applicant_id']);
        }
        
        $this->db->where("m.archive", "0");
      
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number()); 
        $last_query = null;        

        $dataResult = null;
        if (empty($query->result())) {
            return array('msg' => "Member not found!", 'status' => false);
        }
        foreach ($query->result() as $val) {
            $row = $val;
            $dataResult = $row;

            # account person for pre-selection
            $account_person['label'] = $val->contact_name;
            $account_person['value'] = $val->person_id;
            $dataResult->account_person = $account_person;
        }

        # fetching references selected
        if(!empty($dataResult->id)){
            $dataResult->like_selection = $this->get_member_ref_data($dataResult->id, 2);
        $dataResult->language_selection = $this->get_member_ref_data($dataResult->id, 14);
        $dataResult->transport_selection = $this->get_member_ref_data($dataResult->id, 19);
        }
        

        return array('data' => $dataResult, 'status' => true, 'last_query' => $last_query);
    }

    /**
     * fetching member's reference data selected before
     */
    public function get_member_ref_data($member_id, $ref_type_id) {
        $this->db->select("r.id as id, r.display_name as label");
        $this->db->from('tbl_member_ref_data as mr');
        $this->db->join('tbl_references r', 'mr.ref_id = r.id', 'inner');
        $this->db->where("mr.member_id", $member_id);
        $this->db->where("r.type", $ref_type_id);
        $this->db->where("mr.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dataResult = [];
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $row = $val;
                $dataResult[] = $row;
            }
        }
        return $dataResult;
    }


    function update_member($data, $member_id, $adminId) {        
        # adding languages
        //update the existing reference data

        $member_data = [
            "max_dis_to_travel" => $data['max_dis_to_travel'] ?? NULL,
            "mem_experience" => $data['mem_experience'] ?? NULL,
            "hours_per_week" => $data['hours_per_week'] ?? NULL
        ];

        if ($member_id) {
            $member_data["updated_date"] = DATE_TIME;
            $member_data["updated_by"] = $adminId;;
            $this->basic_model->update_records("member", $member_data, ["id" => $member_id]);            
        }

        
        $this->Basic_model->update_records('member_ref_data', array('archive' => '1', "updated" => DATE_TIME, "updated_by" =>  $adminId), array('member_id' => $member_id));

        if (!empty($data['language_selection'])) {
            $languages = null;
            foreach($data['language_selection'] as $refobj) {
                $singarr['member_id'] = $member_id;
                $singarr['ref_id'] = $refobj->id;
                $singarr['archive'] = 0;
                $singarr["created"] = DATE_TIME;
                $singarr["created_by"] = $adminId;;
                $languages[] = $singarr;
            }
            $this->basic_model->insert_records("member_ref_data", $languages, $multiple = TRUE);
        }

        # adding transport options
        if (!empty($data['transport_selection'])) {
            $transports = null;
            foreach($data['transport_selection'] as $refobj) {
                $singarr['member_id'] = $member_id;
                $singarr['ref_id'] = $refobj->id;
                $singarr['archive'] = 0;
                $singarr["created"] = DATE_TIME;
                $singarr["created_by"] = $adminId;;
                $transports[] = $singarr;
            }
            $this->basic_model->insert_records("member_ref_data", $transports, $multiple = TRUE);
        }

        # adding like options
        if (!empty($data['like_selection'])) {
            $likes = null;
            foreach($data['like_selection'] as $refobj) {
                $singarr['member_id'] = $member_id;
                $singarr['ref_id'] = $refobj->id;
                $singarr['archive'] = 0;
                $singarr["created"] = DATE_TIME;
                $singarr["created_by"] = $data['created_by'];
                $likes[] = $singarr;
            }
            $this->basic_model->insert_records("member_ref_data", $likes, $multiple = TRUE);
        }

        return $member_id;
    }

    public function update_applicant_avatar($person_id, $avatar) {       
        $this->basic_model->update_records('person', array('profile_pic' => $avatar), array('id' => $person_id));  
    }
}
