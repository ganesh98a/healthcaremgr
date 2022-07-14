<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Online_assessment_model extends Basic_model
{
    var $fill_in_blank_options = [
        "input_option" => "{{INPUT_OPTION}}",
        "select_option" => "{{SELECT_OPTION}}"
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('recruitment/Recruitment_applicant_model');
        $this->load->model('recruitment/Recruitment_jobs_model');
        $this->load->model('imail/Automatic_email_model');
        $this->load->model('common/Common_model');
        $this->load->library('Asynclibrary');
        $this->load->model('Basic_model');
        $this->load->model('recruitment/Recruitment_oa_template_model');
        $this->load->model('sms/Sms_template_model');
    }
    
    public function initiate_assessment($reqData, $adminId) {
        $url = base_url()."recruitment/OnlineAssessment/send_assessment_email";
        $param = array('reqData' => $reqData, 'adminId' => $adminId, 'mail_type' => 'send_assessment' );
        $this->asynclibrary->do_in_background($url, $param);
        $req = new stdClass();
        $req->data = new stdClass();
        $req->adminId = $adminId;
        $req->data->application_id = $reqData['jobId'];
        $req->data->applicants = [];
        foreach($reqData['application_details'] as $obj) {
            $applicant = new stdClass();
            $applicant->application_id = $obj->applicationId;
            $req->data->applicants[] = $applicant;
        }
        $applicants = $this->getApplicants($req,$reqData);
        $sms_template = $this->Sms_template_model->get_sms_template_to_initiate_oa();
        if (!empty($sms_template) && !empty($sms_template->content)) {
            $jobDetails = $this->Recruitment_jobs_model->get_jobs_by_id($reqData['jobId']);
            $job_type = $jobDetails->label??'';
            $msg = $sms_template->content;
            $smsReq = new stdClass();
            $smsReq->data = new stdClass();
            $smsReq->data->msg = $msg;
            $smsReq->data->applicants = $applicants;
            $smsReq->adminId = $adminId;
            $smsReq->data->template_title = "ONCALL Group Australia $job_type Application: Online Assessment";
            $url = base_url()."sms/Sms/send_bulk_sms_oa";
            $params = array('reqData' => json_encode($smsReq));
            $this->asynclibrary->do_in_background($url, $params);
        }
        return  ['status' => true,'msg' => 'Online assessments have been issued. Please refresh the screen to view the latest status.'];
    }

    public function get_online_assessments_list($reqData, $adminId,  $extraCondition = '') {
    
        $limit = $reqData->pageSize ?? 20;
        $page = $reqData->page ?? 0;
        $filter = $reqData->filtered?? null;
        $orderBy = 'roa.id';
        $direction = 'DESC';
        $start_date = $filter->start_date ?? '';
        $end_date = $filter->end_date ?? '';
        $start_date = is_null($start_date) ? '' : $start_date;
        $end_date = is_null($end_date) ? '' : $end_date;
        $filter_by = '';

        if (!empty($filter) && is_object($filter) && property_exists($filter, 'filterBy') && $filter->filterBy != 'all') {
     		$filter_by = $filter->filterBy;
        }

        if (!empty($filter_by) && $filter_by == 1 || $filter_by == 2) {			
		    $this->db->where("roa.status", $filter_by);
        }
        else if (!empty($filter_by)) {			
		    $this->db->where("roa.job_type", $filter_by);
        }

        if (!empty($filter) && is_object($filter) && property_exists($filter, 'srch_box')) {
            $filter->search = $filter->srch_box;
        }

        $src_columns = array('roa.status','roa.id as id', 'roa.title', 'rjc.name as job_type', 'concat(m.firstname," ",m.lastname) as created_by' );

        # text search
        if (!empty($filter->search)) {
            $search_key = $this->db->escape_str($filter->search, TRUE);
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                $formated_date = '';
                if($column_search == 'roa.status') { 
                    if (strtolower($filter->search) == "active") {
                        $this->db->or_like($column_search, 1);
                    } elseif (strtolower($filter->search) == "inactive") {
                        $this->db->or_like($column_search, 2);
                    }
                }
                else if($column_search=='DATE(ri.interview_start_datetime)' || $column_search=='DATE(ri.interview_end_datetime)'){
                    $formated_date = date('Y-m-d', strtotime(str_replace('/', '-', $filter->search)));
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $formated_date);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $formated_date);
                    }
                }
                else{
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $search_key);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }


            }
            $this->db->group_end();
        }
       
        $select_column = array(
            'roa.id as id', 'roa.title', 'rjc.name as job_type', 'concat(m.firstname," ",m.lastname) as created_by'           
        );

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->select("(
            CASE WHEN roa.status=1 THEN 'Active' 
            WHEN roa.status=2 THEN 'Inactive' END) as status");

        $this->db->from(TBL_PREFIX . 'recruitment_oa_template as roa');
        $this->db->join(TBL_PREFIX . 'recruitment_job_category as rjc', 'rjc.id = roa.job_type', 'inner');
        $this->db->join(TBL_PREFIX . 'member as m', 'm.uuid = roa.created_by', 'left');
        $this->db->where('roa.archive', 0);
        
        if (!empty($extraCondition)) {
            $this->db->where($extraCondition);
        }

        $this->db->order_by($orderBy, $direction);
       
        $this->db->limit($limit, ($page * $limit));
        //list view filter condition
        $query = $this->db->get();
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();

        if (!empty($result)) {
            //fetch viewed by data
            $vlogs = [];
            $oa_ids = array_map(function($item){
                return $item->id;
            }, $result);

            if (!empty($oa_ids)) {
                $this->load->file(APPPATH.'Classes/common/ViewedLog.php');
                $viewedLog = new ViewedLog();
                // get entity type value
                $entity_type = $viewedLog->getEntityTypeValue('online_assessment');

                $result2 = $this->Common_model->getViewedLogByEntityIDs($oa_ids, $entity_type);
                
                foreach($result2 as $v) {
                    $vlogs[$v->entity_id] = $v;
                }
            }

            foreach ($result as $val) {
                if ( array_key_exists($val->id, $vlogs) ) {
                    $val->viewed_by_id = $vlogs[$val->id]->viewed_by_id;
                    $val->viewed_by = $vlogs[$val->id]->viewed_by;
                    $val->viewed_date = $vlogs[$val->id]->viewed_date;
                }           
            }
        }
        
        return array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'total_item' => $total_item);
    }

    /**
     * To Generate assessment email data
     * @param $reqData {array} front end data
     * @param $adminId {int} Admin id
     * 
     * @see get_jobs_by_id
     * @see get_assement_duration_by_type
     * @see format_time
     * @see get_applicant_email
     * @see trigger_OA_email
     * 
     * @return {array} return with status
     */
    public function create_assessment_email_data($reqData, $adminId)
    { 
        
        $jobDetails = $this->Recruitment_jobs_model->get_jobs_by_id($reqData['jobId']);
        
        $job_type_id = $jobDetails->value??'';
        $duration = $this->get_assement_duration_by_type($job_type_id);
        $duration = $this->format_time($duration);
        
        $applicant = [];
        $template_id = $reqData['assessmentId']??NULL;
        foreach($reqData['application_details'] as $application_det) {
            $applicant_id = $application_det['applicantId'];
            $application_id = $application_det['applicationId'];
            
            $app_details = $this->Recruitment_applicant_model->get_applicant_info
                ($applicant_id, $adminId);
                
            if(!empty($app_details)) {
                $firstname = $app_details['firstname'] ? ucfirst($app_details['firstname']) : '';
                $lastname = $app_details['lastname'] ? ucfirst($app_details['lastname']) : '';

                $app_phone = $this->Recruitment_applicant_model->get_applicant_phone($applicant_id);
                $user_phone = $app_phone[0]->phone ?? '';
                $app_email = ($this->Recruitment_applicant_model->get_applicant_email($applicant_id, TRUE));
                $user_email = $app_email[0]->email;
                if(!empty($user_email)) {
                    //first insert into recruitment_job_assessment
                    $uuid = $this->getGUID();
                    $this->insert_jobs_assessment($reqData['jobId'], $applicant_id, $uuid, $adminId, $template_id, $application_id);
                    $applicant = [];
                    $applicant['email'] = $user_email;
                    $applicant['firstname'] = $firstname;
                    $applicant['lastname'] = $lastname;
                    $applicant['job_type'] = $jobDetails->label??'';
                    $applicant['assesment_duration'] = $duration;
                    $applicant['assessment_link'] = "<a href='" . ASSESSMENT_HOST . "online_assessment/".$uuid."' style='color:#0b64e8;text-decoration:underline;font-weight:bold;'>" . ASSESSMENT_HOST ."online_assessment/$uuid</a>";
                    $applicant['recruiter_name'] = $reqData['recruiterName'];

                    $this->trigger_OA_email($applicant_id, $applicant, 'send_assessment_details', $adminId, $application_id);
                    // this code is commented because sms reminder has been implemented in a different story
                    // if(!empty($user_phone)) {
                    //     $message = "A assessment link has been sent to your email. Please finish it within 48 hours. -- Oncall";
                    //     $this->send_sms($message, $user_phone);
                    // }
                }
            }
        }
        return ['status' => true, 'msg' => 'Assessment Initiated successfully'];
       
    }
    
    /**
     * To send sms
     *
     * @param $message -- text
     * @param $phone_number -- text     
     * @return true
     */
    public function send_sms($message, $phone_number, $applicant, $adminId)
    {
        # publish sms
        $url = base_url()."sms/Sms/send_oa_reminder_sms";
        $param = ['msg' => $message, 'phone_number' => $phone_number, 'online_assessment' => 1, 'applicant' => json_encode($applicant), 'adminId' => $adminId, 'template_title' => 'OA Reminder - SMS'];
        $this->asynclibrary->do_in_background($url, $param);
        return true;
    }
    
     /**
     * To insert a record in tbl_recruitment_job_assessment
     *
     * @param $job_type_id  -- int
     * @param $applicant_id -- int
     * @param $uuid         -- text
     * @param $adminId      -- int
     * @param $template_id  -- int Assement template id
     * @param $application_id -- int Application id
     * 
     * @return true
     */
	public function insert_jobs_assessment($job_id, $applicant_id, $uuid, $adminId, $template_id, $application_id) 
    {
        $existing_oa_data = $this->basic_model->get_row('recruitment_job_assessment', ['id'], ['applicant_id' => $applicant_id, 'application_id' => $application_id, 'job_id' => $job_id]);
        // If OA already exist for same applied applicant application and job then mark OA status as expired 
        if(!empty($existing_oa_data)){
            $updateData = [
                'status' => OA_LINK_EXPIRED, // Link Expired
                'updated_at' => DATE_TIME
            ];  
            $where = [
                'applicant_id' => $applicant_id, 'application_id' => $application_id, 'job_id' => $job_id , 'status' => 1
            ];           
        
         $this->basic_model->update_records('recruitment_job_assessment', $updateData, $where);
        }       

		$arrJobs = [];
		$arrJobs['job_id'] = $job_id;
		$arrJobs['applicant_id'] = $applicant_id;
        $arrJobs['application_id'] = $application_id;
		$arrJobs['expiry_date'] = Date('Y-m-d H:i:s', strtotime(OA_EXPIRY_TIME));
		$arrJobs['uuid'] = $uuid;
        $arrJobs['template_id'] = $template_id;
		$arrJobs['status'] = OA_SENT;
		$arrJobs['created_at'] = DATE_TIME;
		$arrJobs['updated_at'] = DATE_TIME;

        $arrJobs['created_by'] = $adminId;     
        
        $result = $this->basic_model->insert_records('recruitment_job_assessment', $arrJobs);

        if($result){
            // create feed for OA initiation
            $dataToBeUpdated = [
                'oa_status' => OA_SENT
            ];

            $history_data = [
                'application_id' => $application_id,
                'created_by' => $adminId,
                'created_at' => DATE_TIME
            ];

            $history_id = $this->basic_model->insert_records('application_history', $history_data);
            
            $this->Recruitment_applicant_model->create_field_history($history_id, $application_id, 'oa_status', $dataToBeUpdated['oa_status'],  '');
        }

        return $result;
	}
    
    /** Helper function to Trigger Online Assessment email
     * 
     * @param $applicant_id {int} Applicant id
     * @param $email_data {array} Email datas
     * @param $email_key {string} Email template key
     * @see automatic_email_send_to_user
    */
    public function trigger_OA_email($applicant_id, $email_data, $email_key, $adminId='', $application_id='') {
        require_once APPPATH . 'Classes/Automatic_email.php';
        $obj = new Automatic_email();
        $obj->setEmail_key($email_key);
        $obj->setEmail($email_data['email']);
        $obj->setDynamic_data($email_data);
        $obj->setUserId($applicant_id);
        $obj->setUser_type(1);
        
        if(!empty($adminId)) {
            $emailContent = $obj->getEmailContent();
            //first insert history
            $history_data = [
                    'application_id' => $application_id,                
                    'created_by' => $adminId,
                    'created_at' => DATE_TIME
            ];
            
            $history_id = $this->basic_model->insert_records('application_history', $history_data); 
            $data = array();
            $data['history_id'] = $history_id;
            $data['desc'] = $emailContent;
            $data['feed_type'] = 2;
            
            $oAemailStatus = ($email_key == 'send_assessment_initiate_reminder_details') ? OA_EMAIL_REMINDER_FEED : OA_EMAIL_SENT;

            $this->basic_model->insert_records('application_history_feed', $data);
            $this->Recruitment_applicant_model->create_field_history($history_id, $application_id, 'oa_status', $oAemailStatus,  '');           
        }
        
        $obj->automatic_email_send_to_user();
    }

    /**
     * Get duration based on job type
     * 
     * @param $job_type {string} Subcategory of the job
     * @return $duration{int} Duration in seconds
     */
    function get_assement_duration_by_type($job_type) {
        
        switch ($job_type) {
            case 5:
                $duration = NDIS_JOB_READY_ASS_DURATION;
                break;
            case 4:
                $duration = DISABILITY_ASS_DURATION;
                break;
            case 3:
                $duration = HCM_CHILD_YOUTH_ASS_FAMILY;
                break;
            
            default:
                $duration = '';
                break;
        }
        
        return $duration;
    }

    /**
     * To format duration hours and minutes
     * @param $duration {int} Seconds
     * @param $display_seconds {bool} display seconds TRUE/FALSE
     * 
     * @return $time {string} hours, minutes and seconds format example 2 hours 10 minutes 2 seconds
     */
    function format_time($duration, $display_seconds = FALSE) {
        $duration = $duration??0;
        $hours = floor($duration / 3600);
        $minutes = floor(($duration / 60) % 60);
        
        $time = $minutes > 0 ? "$minutes minutes" : '';
        
        if ($hours != 0) {
            $time = "$hours hours $minutes minutes";
        }

        if($display_seconds) {
            $seconds = $duration % 60;
            $time .= $seconds > 0 ? " $seconds seconds" : '';
        }
        
        return $time;
    }
   
    /**
     * To fetch the OA templates by its jobid
     */
    function get_assessment_templates_by_jobid($job_id,$reqData) {
        
        $return  = ['status' => FALSE, 'msg' => 'Job id missing'];

        if($job_id) {
        
            $this->db->select(["roa.id as value","roa.title as label"]);

            $this->db->from(TBL_PREFIX . 'recruitment_oa_template as roa');
            $this->db->join(TBL_PREFIX . 'recruitment_job as rj', 'rj.sub_category = roa.job_type ' , 'inner');
            $this->db->where('rj.id', $job_id);
            $this->db->where('roa.archive', 0);
            $this->db->where('roa.status', 1);
            if($this->Common_model->check_is_bu_unit($reqData)) {
                $this->db->where('rj.bu_id', $reqData->business_unit['bu_id']);
            }
            $this->db->order_by('roa.id', 'DESC');
            $query = $this->db->get();

            $result = $query->result();

            $return  = ['status' => FALSE, 'msg' => 'Templates not found'];
            if(!empty($result)) {
                $return  = ['status' => TRUE, 'data' => $result, 'msg' => 'Templates fetching successfully']; 
            }           
        }

        return $return;
    }
    
    /**
     * To sends Assessment completion email to the recruiter while applicant complete their Assessment
     * 
     * @param $reqData {array} Request data
     * @param $adminId {int} Adminid
     * 
     * @see get_applicant_info - get applicant details
     * @see get_jobs_by_id - get job details
     * 
     * @return $return {array} status with message and data
     */
    public function send_assessment_completion_email_to_recruiter($reqData, $adminId) {
        
        if(empty($reqData['uuid'])) {
            return ['status' => FALSE, 'msg' => 'ID Missing'];
        }

        $select_col = ["rja.created_at","roa.job_type","rja.applicant_id", "rja.application_id", "rja.uuid", "rja.job_id", "rja.start_date_time", "rja.completed_date_time"];
        $where_conditions = ['rja.uuid' => $reqData['uuid'], 'roa.archive'=> 0];
        
        $query = $this->pull_job_assessment_by_dynamic_var($where_conditions, $select_col);        
        $result = $query->row();
        
        $return = ['status' => FALSE, 'msg' => 'Record not found'];
        
        if(!empty($result)) {
           
            $app_details = $this->Recruitment_applicant_model->get_applicant_info
            ($result->applicant_id, $adminId);
            
            if(!empty($app_details)) {
                $firstname = $app_details['firstname'] ? ucfirst($app_details['firstname']) : '';
                $lastname =$app_details['lastname'] ? ucfirst($app_details['lastname']) : '';
                $jobDetails = $this->Recruitment_jobs_model->get_jobs_by_id($result->job_id);        
                
                $applicant['email'] = OA_RECRUITER_EMAIL;
                $applicant['firstname'] = $firstname;
                $applicant['lastname'] = $lastname;
                $applicant['job_type'] = $jobDetails->label??'';
                $applicant['application_id'] = $result->application_id;
                $applicant['scheduled_date'] = date('d/m/Y h:i a', strtotime($result->created_at));
                
                $duration_taken = 'N/A';
                if(!empty($result->start_date_time) && !empty($result->completed_date_time) &&
                    $result->start_date_time != '0000-00-00 00:00:00' && 
                    $result->completed_date_time != '0000-00-00 00:00:00') {
                    $duration_taken = $this->format_time(strtotime($result->completed_date_time) - strtotime($result->start_date_time), FALSE);
                    
                }

                $applicant['duration_taken'] = !$duration_taken ? '0 minute' : $duration_taken;                
  
                $this->trigger_OA_email($result->applicant_id, $applicant, 'send_assessment_completion_details', $adminId, $result->application_id);
                
            }
        
            $return = ['status' => TRUE, 'msg' => 'Message Sent Successfully'];
        }

        return $return;
    }

    /**
     * To get uuid
     * @param none
     * @return uuid
     */
    function getGUID(){
        $charId = strtolower(md5(uniqid(rand(), true)));
        $hyphen = chr(45);
        return substr($charId, 0, 8) . $hyphen
               .substr($charId, 8, 4) . $hyphen
               .substr($charId, 12, 4) . $hyphen
               .substr($charId, 16, 4) . $hyphen
               .substr($charId, 20, 4);       
    }

    /**
     * Get template question and answer options
     * @param {array} $data
     */
    public function get_oa_template_by_uid($data) {

        # validate uuid
        $validation_rules = array(
            array('field' => 'uuid', 'label' => 'Applicant Id', 'rules' => ['required']),
        );
        $validate = $this->form_validation($data, $validation_rules);
        if (!empty($validate) && $validate['status'] === false) {
            return $validate;
        }

        $uuid = (string) $data->uuid ?? '';

        # Get assessment data
        $assessment = $this->get_applicant_assessment($uuid);
        if (empty($assessment)) {
            return array('status' => false, 'msg' => 'Assessment is not available');
        }

        $job_assessment_id = (integer) $assessment['job_assessment_id'] ?? '';
        $template_id = (integer) $assessment['template_id'] ?? '';

        # Get assessment detail 
        $assessment_detail = $this->get_assessment_details($template_id);
        if (empty($assessment_detail)) {
            return array('status' => false, 'msg' => 'Assessment template is not available');
        }

        # Get questions list
        $question_answers = $this->get_question_anser_list($template_id);

        return array( 'status' => true, 'data' => $question_answers);
    } 

    /**
     * Get question anser list by template id
     * @param {int} $template_id
     */
    function get_question_anser_list($template_id) {
        $question_answers_list = [];
        $questions_list = $this->get_oa_question_list_by_template_id($template_id);
        $options_list = $this->get_oa_question_options_by_template_id($template_id);
        $fill_in_blank_option =(object) $this->fill_in_blank_options;
        foreach($questions_list as $quest_in => $question) {
            $question_id = $question->id;
            $question_txt = $question->question;
            $serial_no = $question->serial_no;
            $question->question_raw = '';

            # index number 01, 02 ..
            $num_padded = sprintf("%02d", $quest_in+1);
            
            if ( filter_var($serial_no, FILTER_VALIDATE_INT)) {
                $serial_no = sprintf("%02d", $serial_no);
                $question->serial_no = $serial_no;
            }           
            
            $question->index_number = $num_padded;

            $filter_option = array_filter($options_list, function ($option) use (&$question_id) {
                return ($option->question_id == $question_id);
            });

            $filter_options = array_values($filter_option);
            
            # 1 -Multiple Choice, 2-Single Choice, 3-True/False, 4-Short Answers, 6 - Fill in the blank
            $answer_type = (integer) $question->answer_type;
            switch($answer_type) {
                case 1:
                case 2:
                case 3:
                    $question->options = (array) $filter_options;
                    break;
                case 4:
                    $question->options = [];
                    break;
                case 6:
                    $blank_question_type = (integer) $question->blank_question_type;
                    $question_txt_raw = $question->fill_up_formatted_question ?? '';
                    $question->question_raw = $question_txt_raw;

                    # 2 - select / 1 - input text
                    if ($blank_question_type === 2) {
                        # select option 
                        $select_option = $fill_in_blank_option->select_option;
                        $select_option_count = substr_count($question_txt_raw, $select_option);
                        $option_raw = []; 
                        $question->options_count = $select_option_count;
                        foreach($filter_options as $fil_in => $fil_option) {
                            $fil_option->label = $fil_option->option;
                            $option_raw[$fil_option->blank_question_position][] = $fil_option;
                        }
                        $question->options = $option_raw;
                    } else {
                        # input option
                        $input_option = $fill_in_blank_option->input_option;
                        $input_option_count = substr_count($question_txt_raw, $input_option);
                        $question->options = [];
                        $question->options_count = $input_option_count;
                    }
                    
                    break;
                default:
                    $question->options = [];
                    break;
            }

            $question_answers_list[] = (array) $question;
        }
        
        return $question_answers_list;
    }

    /**
     * Get assessment detail
     * @param {int} $template_id
     */
    function get_assessment_details($template_id) {
        $tbl_question_topic = TBL_PREFIX . 'recruitment_oa_template';
        $this->db->select('*');
        $this->db->from($tbl_question_topic);
        $where = ['id' => $template_id];
        $this->db->where($where);
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Get applicant assessment by uuid
     * @param {str} $uuid
     */
    function get_applicant_assessment($uuid) {
        $column = array(
            'rja.id as job_assessment_id',
            'rja.application_id',
            'rja.applicant_id',
            'rja.template_id',
            'rja.status',
            'rja.job_id',
            'rja.start_date_time',
            'rja.created_by'
        );

        $where = array(
            'rja.uuid' => $this->db->escape_str($uuid, true)
        );

        $this->db->select($column);
        $this->db->from('tbl_recruitment_job_assessment rja');
        $this->db->where($where);
        $this->db->order_by('rja.id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Get question list by template id exclude answer_type 5 (passage)
     * @param {int} $template_id
     */
    function get_oa_question_list_by_template_id($template_id)
    {
        $tbl_question_topic = TBL_PREFIX . 'recruitment_oa_questions as roaq';
        $this->db->select(['roaq.id', 'roaq.question', 'roaq.answer_type', 'roaq.is_mandatory', 'roaq.parent_question_id', 'roaq.blank_question_type', 'roaq.fill_up_formatted_question', 'roaq.serial_no']);
        $this->db->select("(
            CASE 
                WHEN parent_question_id IS NOT NULL THEN ( SELECT question FROM tbl_recruitment_oa_questions WHERE id = roaq.parent_question_id)
            ELSE '' 
            END) as question_passage
        ");
        $this->db->select("(
            CASE 
                WHEN parent_question_id IS NOT NULL THEN ( SELECT answer_type FROM tbl_recruitment_oa_questions WHERE id = roaq.parent_question_id)
            ELSE '' 
            END) as parent_anwser_type
        ");
        $this->db->select("(
            CASE 
                WHEN parent_question_id IS NOT NULL THEN true
            ELSE false
            END) as is_passage
        ");
        $this->db->from($tbl_question_topic);
        $this->db->where(['roaq.oa_template_id' => $template_id]);
        $this->db->where(['roaq.answer_type !=' => 5]);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get question list by template id
     * @param {int} $template_id
     */
    function get_oa_questions_by_template_id($template_id)
    {
        $tbl_question_topic = TBL_PREFIX . 'recruitment_oa_questions';
        $this->db->select(['id', 'question', 'answer_type', 'is_mandatory']);
        $this->db->from($tbl_question_topic);
        $this->db->where(['oa_template_id' => $template_id]);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get question list by template id
     * @param {int} $template_id
     */
    function get_oa_question_options_by_template_id($template_id)
    {
        $tbl_question_topic = TBL_PREFIX . 'recruitment_oa_answer_options';
        $this->db->select(['id', 'question_id', 'option', 'blank_question_position']);
        $this->db->from($tbl_question_topic);
        $this->db->where(['oa_template_id' => $template_id]);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Save assessment answer
     */
    function save_assessment_answer($data) {

        # validate uid
        $validation_rules = array(
            array('field' => 'uuid', 'label' => 'Applicant Id', 'rules' => ['required']),
        );
        $validate = $this->form_validation($data, $validation_rules);
        if (!empty($validate) && $validate['status'] === false) {
            return $validate;
        }

        $uuid = (string) $data->uuid ?? '';
        $answer = (array) $data->answer ?? '';        

        # Get assessment data
        $assessment = $this->get_applicant_assessment($uuid);
        if (empty($assessment)) {
            return array('status' => false, 'msg' => 'Assessment is not available');
        }

        $status = OA_SUBMITTED;
        $assessment['is_auto_save'] = FALSE;
        if(!empty($data->is_auto_save))
        {
            $assessment['is_auto_save'] = TRUE;
            $status = OA_AUTO_SUBMIT;
        }

        $app_details = $this->Recruitment_applicant_model->get_applicant_info($assessment['applicant_id'], '');
        $applicant_name = $app_details['fullname'];        

        # Update assessment status as completd
        $updateAssessmentStatus = $this->update_job_assessment_as_completed($assessment, $status, $applicant_name);
        if (!$updateAssessmentStatus) {
            return array('status' => false, 'error' => 'Something went wrong. please try again');  
        }

        $job_assessment_id = (integer) $assessment['job_assessment_id'] ?? '';
        $template_id = (integer) $assessment['template_id'] ?? '';
        if ($job_assessment_id) {
            $where = array('job_assessment_id' => $job_assessment_id);
            $updateData = array('archive' => 1, 'updated_at' => DATE_TIME);
            $this->basic_model->update_records('recruitment_oa_applicant_answer', $updateData, $where);
        }

        # Get assessment detail 
        $assessment_detail = $this->get_assessment_details($template_id);
        if (empty($assessment_detail)) {
            return array('status' => false, 'msg' => 'Assessment template is not available');
        }

        $saveAssessment = $this->save_assessment_answer_db($assessment, $answer, $template_id);
        if ($saveAssessment) {
            # Send Email
            $this->send_assessment_completion_email_to_recruiter((array) $data, '');
            return array('status' => true, 'msg' => 'Assessment submitted successfully');
        } else {
            return array('status' => false, 'error' => 'Something went wrong. please try again');
        }
    }

    /**
     * Update Assessment is completed
     * @param {object} assessment
     * @param {int} req_status
     */
    function update_job_assessment_as_completed($assessment, $req_status, $applicant_name) {
        $job_assessment_id = (integer) $assessment['job_assessment_id'] ?? '';

        if ($job_assessment_id == '') {
            return array("status" => false, "error" => "Assessment Id is null");
        }

        $where = array('id' => $job_assessment_id);
        
        # update array
        $updateData = array('status' => $req_status);
        $updateData['updated_at'] = DATE_TIME;

        if ($req_status == OA_AUTO_SUBMIT && !empty($assessment['is_auto_save'])) {
            //To avoid the time difference from front end to backend while auto save
            $jobDetails = $this->Recruitment_jobs_model->get_jobs_by_id($assessment['job_id']);        
            $job_type_id = $jobDetails->value??'';
            $duration = $this->get_assement_duration_by_type($job_type_id);            
            $updateData['completed_date_time'] = !empty($duration) ? date('Y-m-d H:i:s', strtotime($assessment['start_date_time']. " +$duration seconds")) : DATE_TIME;
            
        } elseif($req_status == OA_SUBMITTED) {
            $updateData['completed_date_time'] = DATE_TIME;
        }
        $updateAssessment = $this->basic_model->update_records('recruitment_job_assessment', $updateData, $where);
        if ($updateAssessment) {
            if($req_status == OA_SUBMITTED || $req_status == OA_AUTO_SUBMIT) {
            // create feed for OA submitted
            $dataToBeUpdated = [
                'oa_start_date_time' => $assessment['start_date_time'],
                'oa_status' => $req_status,
                'oa_completed_date_time' => $updateData['completed_date_time'],
                'oa_completed_time' => get_time_id_from_series($updateData['completed_date_time']),
                'applicant_name' => $applicant_name
            ];           
            $this->updateHistory($dataToBeUpdated, $assessment['application_id'], $assessment['created_by']);
        }
            # Send Email
            return array('status' => true, 'msg' => 'Assessment status updated successfully');
        } else {
            return array('status' => false, 'error' => 'Something went wrong. please try again');
        }
    }
    /**
     * Save assessment anser in db
     * @param {object} assessment
     * @param {array} answer
     */
    function save_assessment_answer_db($assessment, $answers, $template_id) {
        # update
        $this->load->model('Grade_online_assessment_model');
        $insAnswerData = [];

        foreach($answers as $answer) {
            $question_id = (integer) $answer->question_id;
            $answer_type = (integer) $answer->answer_type;
            $blank_question_type = (integer) $answer->blank_question_type;
            $answer_option = $answer->answer;
            $options_count = $answer->options_count ?? 0;
            $answer = [];
            $blank_position_arr = [];
            $answer_txt_arr = [];
            $is_correct_arr = [];
            $ans_grade_total  = 0;
            
            foreach($answer_option as $ans_idx => $option) {
                $answer_id = (integer) $option->answer_id;
                $blank_position = $option->blank_position ?? NULL;
                $answer_text = NULL;                
                $grade = NULL;
                $is_correct = NULL;
                if ($answer_type != 4 && $option->selected == false && ($answer_type != 6 && $blank_question_type != 2)) {
                    continue;
                }
                if ($answer_type == 4 || $answer_type == 7 ) {
                    $answer_id = NULL;
                    $answer_text = (string) $option->selected;
                    if($answer_type == 7 ){
                        $is_correct = $this->evaluate_arthimetic_questions($question_id, $answer_id,$answer_text);
                        if($is_correct)
                        {
                            $grade = $is_correct['grade'];
                        } else {
                            $grade = 0;
                    }
                }
                } else if ($answer_type == 6) {
                    $blank_position_arr[] = $blank_position;                    
                    switch ($blank_question_type) {
                        case 1:
                            $answer_id = NULL;
                            $answer_txt_arr[] = (string) $option->selected;
                            $answer = NULL;
                            break;
                        case 2:
                            $answer[] = $answer_id;
                            break;
                        default:
                            $answer_id = NULL;
                            $answer = NULL;
                            $answer_txt_arr = NULL;
                            $blank_position_arr = NULL;
                        break;
                    }
                } else if($answer_type == 1) {
                    $answer[] = $answer_id;
                }
                
                # Need to add comment
                if($answer_type != 4 && $answer_type != 6 &&  $answer_type != 7) {
                    $is_correct  = $this->grade_answer_by_question_id($question_id, $answer_id);
                    if($is_correct)
                    {
                        $grade = $is_correct['grade'];
                    } else {
                        $grade = 0;
                    }
                } else if ($answer_type == 6 && $blank_question_type == 2) {
                    $is_correct  = $this->grade_answer_by_question_id($question_id, $answer_id, $blank_position);
                    if($is_correct)
                    {
                        $is_correct_arr[] = 1;
                        $grade = $is_correct['grade'];
                        $grade_split = 1;
                        if (intVal($options_count) > 0) {
                            $grade_split = $grade / $options_count;
                        }
                        $ans_grade_total = $ans_grade_total + $grade_split;
                    } else {
                        $is_correct_arr[] = 0;
                    }
                }

                if ($answer_type == 6 && !empty($answer_txt_arr)) {
                    $ans_txt_arr = json_encode($answer_txt_arr);
                } else {
                    $ans_txt_arr = $answer_text;
                }

                if (($answer_type == 1 || $answer_type == 6)&& !empty($answer)) {
                    $answer_arr = json_encode($answer);
                } else {
                    $answer_arr = $answer_id;
                }

                # correct answer validate for fill in  blank select option
                if ($answer_type == 6 && !empty($is_correct_arr)  && $blank_question_type == 2) {
                    $ans_correct_arr = json_encode($is_correct_arr);
                    $grade_tot = $ans_grade_total;
                } else if ($answer_type != 4 && $answer_type != 6) {
                    $ans_correct_arr = $is_correct ? 1 : 0;
                    $grade_tot = $grade;
                } else {
                    $ans_correct_arr = $is_correct ? 1 : NULL;
                    $grade_tot = $grade;
                }

                $arrJobsAssessment = [];
                $arrJobsAssessment['applicant_id'] = $assessment['applicant_id'];
                $arrJobsAssessment['application_id'] = $assessment['application_id'];
                $arrJobsAssessment['job_assessment_id'] = $assessment['job_assessment_id'];
                $arrJobsAssessment['question_id'] = $question_id;
                $arrJobsAssessment['answer_id'] = $answer_arr;
                $arrJobsAssessment['blank_question_position'] = $answer_type == 6 ? json_encode($blank_position_arr) : NULL;
                $arrJobsAssessment['answer_text'] = $ans_txt_arr;
                $arrJobsAssessment['is_correct'] = $ans_correct_arr;
                $arrJobsAssessment['grade'] = $grade_tot;
                $arrJobsAssessment['archive'] = 0;
                $arrJobsAssessment['answer_type'] = $answer_type;
                $arrJobsAssessment['created_at'] = Date('Y-m-d H:i:s');
                $arrJobsAssessment['created_by'] = $assessment['applicant_id'];

                $insAnswerData[$question_id] = $arrJobsAssessment;                
                /* $key = array_search($question_id, array_column($insAnswerData, 'question_id'));
                if ($key > -1 && $answer_type != 6) {
                    $insAnswerData[$key] = $arrJobsAssessment;
                } else {
                    $insAnswerData[] = $arrJobsAssessment;
                } */

            }
        }

        if (!empty($insAnswerData)) {
            $insAnswerData = array_values($insAnswerData);
        }
        
        $saveDataAssessment = 0;
        if (!empty($insAnswerData)) {
            $saveDataAssessment = $this->basic_model->insert_update_batch('insert', 'recruitment_oa_applicant_answer', $insAnswerData);
        }

        $insAnswerData = $this->grade_multiple_choice_questions($insAnswerData,$assessment['job_assessment_id']);

         return $this->update_score_grade_percentage_by_assessment_id($assessment['job_assessment_id'],$insAnswerData,$template_id);
    }

    /**
     * Form validation - uid
     * @param {objec} $data
     */
    function form_validation($data, $validation_rules) {
        # validation
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $this->form_validation->set_data((array) $data);
        $this->form_validation->set_rules($validation_rules);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return array('status' => false, 'error' => implode(', ', $errors));
        }

        return array('status' => true, 'msg' => '');
    }
    /**
     * Get inprogress online assessment
     * @param $reqData {array} Request data
     * @param $adminId {int} Adminid
     * 
     * @see get_assessment_info - get assessmeny details
     * 
     * @return $return {array} status with message and data
     */

    function get_exisiting_oa_assessment_by_status($reqData,$appData=NULL) {
       $response = ['status' => false];
       $oa_assessment=NULL;
       if(!empty($appData) && $this->Common_model->check_is_bu_unit($appData)){
         $bu_check =  $this->basic_model->get_row('recruitment_applicant', ['id'], ['id' => $reqData->applicant_id,'bu_id'=>$appData->business_unit['bu_id']]);
         if($bu_check)
         {
            $oa_assessment =  $this->basic_model->get_row('recruitment_job_assessment', ['uuid'], ["status"=>2 , 'applicant_id' => $reqData->applicant_id, 'application_id' => $reqData->application_id]);
         }
       }
       if($oa_assessment){
        $response = ['status' => true];
    }
        
        return $response;
    }

    public function getApplicants($request,$reqData=NULL) {        
        $applicants = [];
        $response = $this->Recruitment_applicant_model->get_applications($request->data, $request->adminId,'',$reqData);
        $existing_applications = [];
        if ($response['status']) {
            foreach($response['data'] as $applicant) {
                $existing_applications[$applicant->application_id] = $applicant;
            }
        }
        foreach($request->data->applicants as $applicant) {
            if (array_key_exists($applicant->application_id, $existing_applications)) {
                $existing_applicant = $existing_applications[$applicant->application_id];
                if ($existing_applicant->application_process_status != 8 && !empty($existing_applicant->phone)) {
                    $applicants[] = (object) ['phone' => $existing_applicant->phone, 'firstname' => $existing_applicant->firstname, 'lastname' => $existing_applicant->lastname, 'application_id' => $existing_applicant->application_id, 'applicant_id' => $existing_applicant->applicant_id];
                }
            }
        }
        return $applicants;
    }    
    /**
     * Pull assessment details based on dynamic 'where' condtions
     * 
     * @param $where_conditions {array} list of where conditions
     * @param $select_col {array} coloumns needs to selected
     */
    function pull_job_assessment_by_dynamic_var($where_conditions, $select_col) {
        
        $this->db->select($select_col ?? 'rja.*');
        
        $this->db->from(TBL_PREFIX . 'recruitment_job_assessment as rja');
        $this->db->join(TBL_PREFIX . 'recruitment_oa_template as roa', 'roa.id = rja.template_id', 'inner');
        $this->db->join(TBL_PREFIX . 'recruitment_job_category as rjc', 'rjc.id = roa.job_type', 'inner');
        $this->db->join(TBL_PREFIX . 'member as m', 'm.uuid = roa.created_by', 'left');
        
        if(!empty($where_conditions)) {
            foreach($where_conditions as $column => $value) {

                $this->db->where($column, $value);
            }
        }
        return $this->db->get();
    }

    public function grade_answer_by_question_id($question_id, $answer_id, $blank_position = -1){
        $correct_answer = $this->get_oa_question_options_by_question_id($question_id, $blank_position);   
        if(!empty($correct_answer) && $correct_answer[0]->{'id'}==$answer_id){
        $question_grade = $this->get_oa_question_grade_by_question_id($question_id);  
            return ['status'=> true , 'grade' => $question_grade];
        }
        return false;
    }
    public function get_oa_question_options_by_question_id($question_id, $blank_position = -1)
    {
        $tbl_question_topic = TBL_PREFIX . 'recruitment_oa_answer_options';
        $this->db->select(['id','option']);
        $this->db->from($tbl_question_topic);
        $this->db->where(['question_id' => $question_id,'is_correct'=>1]);
        if ($blank_position > -1) {
            $this->db->where(['blank_question_position' => $blank_position]);
        }
        $query = $this->db->get();
        return $query->result();
    }   
    public function get_oa_question_grade_by_question_id($question_id)
    {
        $tbl_question_topic = TBL_PREFIX . 'recruitment_oa_questions';
        $this->db->select('grade');
        $this->db->from($tbl_question_topic);
        $this->db->where(['id' => $question_id]);
        $query = $this->db->get();
        $result=$query->result();
        return $result[0]->{'grade'};
    }

    public function filter_answer_by_type($answer_list, $answer_type)
    {
        return array_values(
            array_filter(
                $answer_list,
                function ($item) use (&$answer_type) {
                    return $item['answer_type'] == $answer_type;
                },
                0
            )
        );
    }

    public function update_score_grade_percentage_by_assessment_id($assessment_id,$result_arr,$template_id)
    {
       
        $total_grade = $this->calculate_total_grade_for_assessment($template_id);
        function do_reduce($carry, $item)
        {
        (integer)$carry +=  (integer)$item['grade'];
         return $carry;
        }
        $marks_scored = array_reduce($result_arr, "do_reduce");
        $percentage=(($marks_scored /  $total_grade) * 100);
        $percentage=number_format((float)$percentage, 2, '.', '');
        $assessment_update = [
            'total_grade'  => $total_grade,
            'marks_scored'  => $marks_scored,
            'percentage'  =>  $percentage,
          ];
        return $this->basic_model->update_records('recruitment_job_assessment',
           $assessment_update, ['id' => $assessment_id]);
    }

    public function calculate_total_grade_for_assessment($template_id)
    {
       
        $tbl_question_topic = TBL_PREFIX . 'recruitment_oa_questions';
        $this->db->select('grade');
        $this->db->from($tbl_question_topic);
        $this->db->where(['oa_template_id' =>$template_id]);
        $query  = $this->db->get();
        $result = $query->result();
        function sum($carry, $item)
        {
          (integer)$carry +=  (integer)$item->{'grade'};
          return $carry;
        }
       return array_reduce($result, "sum");
       
    }

    public function filter_option_id($correct_answer)
    {
        return array_values(
            array_filter(
                $correct_answer,
                function ($item)  {
                    return $item->{'id'};
                },
                0
            )
        );
    }

    public function grade_multiple_choice_questions($insAnswerData,$assessment_id){
    $short_answers = $this->filter_answer_by_type($insAnswerData,1);
    for($j = 0; $j < count($short_answers); $j++)
    {
        $correct_answer = $this->get_oa_question_options_by_question_id($short_answers[$j]['question_id']); 
        $correct_ans_id = $this->filter_option_id($correct_answer);
    

       $result=$this->get_mcq_result($correct_ans_id, $short_answers[$j]['answer_id']);
       $total_grade = $result  ? (integer)$this->get_oa_question_grade_by_question_id($short_answers[$j]['question_id']):0;
       $assessment_update = [
        'grade'  => $total_grade,
        'is_correct'  => $result ? $result :FALSE,
      ];
        $short_answers[$j]['grade'] = $total_grade;

        # Update grade
        $key = array_search($short_answers[$j]['question_id'], array_column($insAnswerData, 'question_id'));
        if ($key > -1) {
            $insAnswerData[$key]['grade'] = $total_grade;
        }

      $where =['job_assessment_id' => $assessment_id, 'question_id' =>$short_answers[$j]['question_id']];
      $this->basic_model->update_records('recruitment_oa_applicant_answer',
       $assessment_update, $where);
    }
    return $insAnswerData;
    }

    /**
     * @todo:doing object check will improve performance
     */
    public function get_mcq_result($arr1, $arr2){
         $arr2=json_decode($arr2);
        if(count($arr1)!== count($arr2)){
            return false;
        }
        for($i = 0; $i < count($arr1); $i++){
            $correctIndex =array_search($arr1[$i]->{'id'}, $arr2); 
           if($correctIndex === FALSE){
            return false;
           }
           array_splice($arr2, $correctIndex,1);
           
        }
        return true;
    }
    
    /**
     * Create history item for each change field
     * @param array $Id of Existing application
     * @param array $dataToBeUpdated Modified data of Lead
     * @return void
     */
    public function updateHistory($dataToBeUpdated, $application_id, $adminId) {     
        
        if (!empty($dataToBeUpdated)) {
            $history_data = [
                'application_id' => $application_id,                
                'created_by' => $adminId,
                'created_at' => DATE_TIME
            ];
            $this->update_application_feed_history($history_data, $dataToBeUpdated);
        }
    }

    /**
     * Update application feed history for sending reminders and etc
     * 
     * @param $data {array} data's history update
     * @param $des {string} Description
     * 
     * @param {bool} TRUE/FALSE 
     */
    function update_application_feed_history($data, $desc) {

        if(empty($data)) {
            return TRUE;
        }

        $data['history_id'] = $this->basic_model->insert_records('application_history', $data);
        //Unset application id since history feed table doesn't have a application_id col
        unset($data['application_id']);        
        $data['desc'] = json_encode($desc);
        $data['feed_type'] = 2;

        return $this->basic_model->insert_records('application_history_feed', $data);
    }

    /**
     * Print online assessment - using m_pdf
     * @param {array} $reqData
     * @param {int} $adminId
     */
    public function print_online_assessment($reqData, $adminId) {
        # validation rules
        $validation_rules = array(
            array('field' => 'job_assessment_id', 'label' => 'Assessment Id', 'rules' => ['required']),
            array('field' => 'application_id', 'label' => 'Application Id', 'rules' => ['required']),
        );
        
        $validate = $this->form_validation((array) $reqData, $validation_rules);

        if (!empty($validate) && $validate['status'] === false) {
            return $validate;
        }
        
        # Generate & save pdf
        list($preview_url, $pdfFilePath) = $this->generate_oa_pdf($reqData, $adminId);

        # generate & send envelope if file exist
        if(file_exists($pdfFilePath))
        {   
            return [ "status" => true, "msg" => "Online Assessment pdf generated successfully", "data" => $preview_url ];
        } else {
            return [ "status" => false, "error" => "Online Assessment pdf generation failed" ];
        }
    }

    /**
     * Genearete online assessment pdf - save the pdf in archive directory
     * @param {object} $reqData
     */
    function generate_oa_pdf($reqData, $adminId) {
        $job_assessment_id = (integer) $reqData->job_assessment_id ?? 0;
        $application_id = (integer) $reqData->application_id ?? 0;
        $template = 'online_assessment_print_v1';
        $template_style = 'online_assessment_print_css';
        $data = [];

        $this->load->model('Recruitment_oa_template_model');        
        $resData = $this->Recruitment_oa_template_model->get_assessment_result_by_id_print($job_assessment_id,$application_id, $adminId);
        
        if (empty($resData) || $resData['status'] === false) {
            return ['status' => false, 'error' => 'Something went wrong.'];
        } 

        $application = '';
        $tempData = (array) $resData['data'] ?? '';
        if (!empty($tempData) && !empty($tempData['assessment_template'])) {
            $tempData['assessment_template'] = (array) $tempData['assessment_template'];
            $application .= $tempData['assessment_template']['title'].' Result ' ?? 'Result ';
        }
        if (!empty($tempData) && !empty($tempData['assessment_details']) && !empty($tempData['assessment_details']['applicant_name'])) {
            $application .= $tempData['assessment_details']['applicant_name'];
        }
        if (!empty($tempData) && !empty($tempData['assessment_details'])) {
            $data['assessment'] = (object) $tempData['assessment_details'];
        }
        if (!empty($tempData) && !empty($tempData['assessment_template'])) {
            $data['template'] = (object) $tempData['assessment_template'];
        }
        if (!empty($tempData) && !empty($tempData['question_answers_list'])) {
            $data['question_answer'] = (object) $tempData['question_answers_list'];
        }

        # get cover page header with background image
        $data['type']='header';
        $header = $this->load->view($template,$data,true);
        # get footer image
        $data['type']='footer';
        $footer = $this->load->view($template,$data,true);
        # get cover page content
        $data['type']='content';
        $content = $this->load->view($template,$data,true);
        # get page style css
        $styleContent = $this->load->view($template_style,[],true);

        # set error reporting 0 to avoid warning and deprecated msg
        error_reporting(0);
        # Load library file
        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load();
        # set margin type
        $pdf->setAutoTopMargin='pad';
        # set header & footer line
        $pdf->defaultfooterline = 0;
        $pdf->defaultheaderline = 0;
        # set header
        $pdf->SetHeader($header);
        $pdf->SetFooter($footer);
        # set page layout
        $pdf->AddPage('P','','','','',0,0,0,20,0,0);
        # write page content
        $pdf->WriteHTML($styleContent);
        $pdf->WriteHTML($content);

        # service agreement file path create if not exist
        $fileParDir = FCPATH . ARCHIEVE_DIR;
        if (!is_dir($fileParDir)) {
            mkdir($fileParDir, 0755);
        }
        # create folder with service agreement attachment id
        $fileDir = $fileParDir;
        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0755);
        }
        # file name
        $filename = 'Online Assessment '.$application.' '.$application_id;
        $filenameWithExtension = $filename.'.pdf';
        $pdfFilePath =  $fileDir . '/' . $filenameWithExtension;

        $assessment_pdf = $pdf->Output($pdfFilePath, 'F');

        $preview_url = base_url('mediaShowTempAndDelete/' . $filename . '?filename=' . urlencode(base64_encode($filenameWithExtension)));

        return [$preview_url, $pdfFilePath];
    }


    function evaluate_arthimetic_questions($question_id, $answer_id,$answer_text){
        $correct_answer = $this->get_oa_question_options_by_question_id($question_id, -1);  
        $correct_option = (float) trim($correct_answer[0]->{'option'});
        $answer = (float) trim($answer_text);
        if(!empty($correct_answer) &&  $correct_option === $answer ){
        $question_grade = $this->get_oa_question_grade_by_question_id($question_id);  
            return ['status'=> true , 'grade' => $question_grade];
        }
        return false;
    }
}
