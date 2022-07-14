<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model file that act as both data provider
 * or data repository for RecruitmentForm controller actions
 */
class Recruitment_interview_model extends Basic_Model {

    public function __construct() {
        parent::__construct();
        $this->table_name = 'recruitment_interview';
        $this->load->helper(['array']);
        $this->load->model('recruitment/Recruitment_applicant_model');
        $this->load->model('recruitment/Recruitment_interview_applicant');
        $this->object_fields['Applicants'] = [
                                                'field' => 'id',
                                                'foreign_key' => 'interview_id',
                                                'object_fields' => $this->Recruitment_interview_applicant->getObjectFields(),
                                                'hide_in_condition' => true
                                            ];
        $this->object_fields['invite_type'] = [
                                                    'label' => 'Interview Type',
                                                    'values' => [['label' => 'Quiz', 'value' => 1], ['label' => 'Meeting Invite', 'value' => 2]]
                                                ];
        $this->object_fields['interview_type_id'] = [
                                                        'label' => 'About',
                                                        'values' => $this->get_all_interview_type()
                                                    ];
        $this->object_fields['interview_stage_status'] = [
                                                'label' => 'Status',
                                                'values' => [['label' => 'Open', 'value' => 0], ['label' => 'Scheduled', 'value' => 1], ['label' => 'In progress', 'value' => 2], ['label' => 'Successful', 'value' => 3]]
                                            ];
        $this->setInterviewRecipients();
        $this->load->library('Asynclibrary');
        require_once APPPATH . 'Classes/CommunicationLog.php';
    }

    public $interview_stage_status = [
        "0" => "Open",
        "1" => "Scheduled",
        "2" => "In progress",
        "3" => "Successful",
        "4" => "Unsuccessful",
    ];

    public $interview_stage_status_final = [
        "3" => "Successful",
        "4" => "Unsuccessful",
    ];

    public $interview_stage_status_grouped = [
        "0" => "Open",
        "1" => "Scheduled",
        "2" => "In progress",
        "5" => "Closed",
    ];

    /**
     * returns the list of interview type
     */
    function get_all_interview_type() {
        $this->db->select([ "rit.id as value", "rit.name as label"]);
        $this->db->from("tbl_recruitment_interview_type as rit");
        $this->db->where("rit.archive !=" , 1);

        return $this->db->get()->result();
    }

    /**
     * returns the list of recruitment location
     */
    function get_all_recruitment_location($data) {

        $search = array_key_exists('search', $data) ? $data['search'] : '' ;
        $this->db->select([ "rl.id as value", "rl.name as label"]);
        $this->db->from("tbl_recruitment_location as rl");
        $this->db->where("rl.archive !=" , 1);
        if(!empty($search)){
            $this->db->like('rl.name',$search);
        }
        return $this->db->get()->result();
    }

    /*
     * it is used for making sub query created by (who creator|updated of member)
     * return type sql
     */
    private function get_owner_sub_query($column_by, $tbl_alais) {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = ".$tbl_alais.".".$column_by, null, false);
        $this->db->limit(1);
        
        return $this->db->get_compiled_select();
    }
    /*
    * it is used for making sub query created by (who creator|updated of member)
    * return type sql
    */
   private function get_location_sub_query($column_by, $tbl_alais) {
        $this->db->select("rl.name as label");
        $this->db->from("tbl_recruitment_location rl");
        $this->db->where("rl.id = ".$tbl_alais.".".$column_by, null, false);
        return $this->db->get_compiled_select();
   }

   /*
     * it is used for making sub query created by (who creator|updated of member)
     * return type sql
     */
    private function get_interview_type_by_sub_query($column_by, $tbl_alais) {
        $this->db->select("rit.name as label");
        $this->db->from("tbl_recruitment_interview_type as rit");
        $this->db->where("rit.id = ".$tbl_alais.".".$column_by, null, false);
        return $this->db->get_compiled_select();
    }
    /**
     * Get interview details
     */
    function get_interview_by_id($interview_id) {
        $currtent_date_time = DATE_TIME;

        $owner_sub_query = $this->get_owner_sub_query('owner','ri');
        $location_sub_query = $this->get_location_sub_query('location_id','ri');
        $interview_type_sub_query = $this->get_interview_type_by_sub_query('interview_type_id','ri');
        $form_sub_query = $this->get_form_data_by_sub_query('form_id','ri');

        $this->db->select([ "ri.*", "ms.event_id", "ms.interview_type_id as event_interview_type_id", "ms.odata_context as ms_event_org_id","ms.event_status as ms_event_status","m.uuid as owner","CONCAT_WS(' ', m.firstname,m.lastname) as owner_name ","m.username as owner_email"]);
        $this->db->from("tbl_recruitment_interview as ri");
        $this->db->join('tbl_ms_events_logs as ms', 'ri.ms_event_log_id = ms.id', 'left');
        $this->db->join('tbl_member as m', 'ri.owner = m.uuid', 'left');
        $this->db->select("(" . $location_sub_query . ") as location");
        $this->db->select("(" . $interview_type_sub_query . ") as interview_type");
        $this->db->select("(" . $form_sub_query . ") as form_name");

        $this->db->where("ri.id =" , $interview_id);

        $this->db->select("(
            CASE WHEN ri.invite_type=1 THEN 'Quiz'
                 WHEN ri.invite_type=2 THEN 'Meeting Invite'
                 END) as invite_type_name");
        $this->db->select("(
            CASE WHEN ri.interview_stage_status=0 THEN 'Open'
                WHEN ri.interview_stage_status=1 THEN 'Scheduled'
                WHEN ri.interview_stage_status=2 THEN 'In progress'
                WHEN ri.interview_stage_status=3 THEN 'Successful'
                WHEN ri.interview_stage_status=4 THEN 'Unsuccessful'
                END) as interview_stage_status_label");
        $this->db->select("(
            CASE WHEN ('".$currtent_date_time."' > ri.interview_end_datetime) THEN 'Expired'
                Else 'Scheduled' end
                ) as interview_time_status");

        $query = $this->db->get();
        $interview = $query->row();
        $dataResult = $interview;

        

        if(!empty($interview->interview_start_datetime) && $interview->interview_start_datetime != "0000-00-00 00:00:00"){
                $interview->interview_start_time = get_time_id_from_series($interview->interview_start_datetime);
                $interview->interview_start_date = date("Y-m-d", strtotime($interview->interview_start_datetime) );
        }else{
            $interview->interview_start_datetime = '';
            $interview->interview_start_time = '';
            $interview->interview_end_date = '';
        }           

        if(!empty($interview->interview_end_datetime) && $interview->interview_end_datetime != "0000-00-00 00:00:00"){
            $interview->interview_end_time = get_time_id_from_series($interview->interview_end_datetime);
            $interview->interview_end_date = date("Y-m-d", strtotime($interview->interview_end_datetime) );
        }else{
            $interview->interview_end_datetime = '';
            $interview->interview_end_time = '';
            $interview->interview_end_date = '';
        }
           

        if (!$interview) {
            return ['status' => false, 'error' => "Group Booking is not exist. something went wrong"];
        }
        if(!empty($interview->owner) && $interview->owner != "0"){
            $interview->owner = ['value'=>$interview->owner ,'label'=>$interview->owner_name];
        }
        if(!empty($interview->location_id) && $interview->location_id != "0"){
            $interview->location = ['value'=>$interview->location_id ,'label'=>$interview->location];
        }
        if(!empty($interview->form_id) && $interview->form_id != "0"){
            $interview->form_template = ['value'=>$interview->form_id ,'label'=>$interview->form_name];
        }
        return ['status' => true, 'msg' => "Interview detail fetched successfully", 'data' => $interview];
    }

    /**
     * Create update interview details
     */
    function create_update_interview($post_data, $adminId) {
        $id = $post_data['interview_id'] ?? 0;
        $currtent_date_time = DATE_TIME;
        $save_task_data = array(
            'title'=> $post_data['title'],
            'owner'=> $post_data['owner'] ?? null,
            'location_id' => $post_data['location_id'] ?? null,
            'interview_type_id' => $post_data['interview_type_id'] ?? null,
            'interview_start_datetime'=>date("Y-m-d H:i:s", strtotime($post_data['interview_start_datetime'])),
            'interview_end_datetime'=>date("Y-m-d H:i:s", strtotime($post_data['interview_end_datetime'])),
            'interview_duration'=>$post_data['interview_duration'] ?? null,
            'max_applicant' => $post_data['max_applicant'] ?? null,
            'invite_type' => $post_data['invite_type'] ?? null,
            'form_id' => $post_data['form_id'] ?? null,
            'meeting_link' => $post_data['meeting_link'] ?? null,
            'description' => $post_data['description'] ?? null,
        );

        if((strtotime($save_task_data['interview_start_datetime'])) > (strtotime($save_task_data['interview_end_datetime']))){
            return ['status' => false, 'error' => "Incorrect Group Booking start & end date/time"];
        }
        if(!$id && $currtent_date_time >= $save_task_data['interview_start_datetime']){
                return ['status' => false, 'error' => "Please choose future start time"];
        }

        if($save_task_data['interview_start_datetime'] == $save_task_data['interview_end_datetime']){
            return ['status' => false, 'error' => "End time should be greater than start time"];
        }

        if($id){
            #validating existing interview
            $existingInterview = $this->db->get_where('tbl_recruitment_interview', ['id' => $id, 'archive' => 0], 1)->row_array();
            if (!$existingInterview) {
                return [
                    'status' => false,
                    'error' => 'The Group Booking you are trying to modify was either removed or marked as archived',
                ];
            }
            // Update interview data
            $save_task_data["updated"] = DATE_TIME;
            $save_task_data["updated_by"] = $adminId;
            $this->Basic_model->update_records("recruitment_interview", $save_task_data, ["id" => $id]);
            $interview_id = $id;
            $update_history_data = array(
                'title'=> $post_data['title'],
                'owner'=> $post_data['owner'] ? $post_data['owner'] : 0,
                'location_id' => $post_data['location_id'] ? $post_data['location_id'] : 0,
                'interview_type_id' => $post_data['interview_type_id'] ? $post_data['interview_type_id'] : 0,
                'interview_start_datetime'=>date("Y-m-d H:i:s", strtotime($post_data['interview_start_datetime'])),
                'interview_end_datetime'=>date("Y-m-d H:i:s", strtotime($post_data['interview_end_datetime'])),
                'description' => $post_data['description'] ? $post_data['description'] : '',
                'max_applicant' => $post_data['max_applicant'] ?? null,
                'invite_type' => $post_data['invite_type'] ?? null,
                // 'form_id' => $post_data['form_id'] ?? null,
                'meeting_link' => $post_data['meeting_link'] ?? null,
            );
            #update recruitment interview history
            $this->updateHistory($existingInterview, $update_history_data, $adminId);
        }else{
            // Create interview data
            $save_task_data["created"] = DATE_TIME;
            $save_task_data["created_by"] = $adminId;
            $interview_id = $this->Basic_model->insert_records('recruitment_interview', $save_task_data);
            #update recruitment interview history
            $this->updateHistory(['id' => $interview_id, 'created' => ''], $save_task_data, $adminId);
        }


        if($id){
            return ['status' => true, 'msg' => "Group Booking updated successfully" , 'interview_id' => $id];
        }else{
            return ['status' => true, 'msg' => "Group Booking created successfully" , 'interview_id' => $interview_id];
        }

    }

    /*
     * it is used for making sub query form id (who creator|updated of form)
     * return type sql
     */
    private function get_form_data_by_sub_query($column_by, $tbl_alais) {
        // $this->db->select(["rf.title as label", "rf.id as value"]);
        $this->db->select("rf.title as label");
        $this->db->from("tbl_recruitment_form as rf");
        $this->db->where("rf.id = ".$tbl_alais.".".$column_by, null, false);
        return $this->db->get_compiled_select();
    }
    /**
     * fetches list of interview
     */
    public function get_interviews($reqData, $adminId, $filter_condition = '') {
        $limit = $reqData->pageSize ?? 9999;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = 'ri.created';
        $direction = 'DESC';
        $start_date = $filter->start_date ?? '';
        $end_date = $filter->end_date ?? '';
        $start_date = is_null($start_date) ? '' : $start_date;
        $end_date = is_null($end_date) ? '' : $end_date;
        $currtent_date_time =  DATE_TIME;
        $src_columns = array('ri.id','ri.title', 'DATE(ri.interview_start_datetime)',
        'DATE(ri.interview_end_datetime)', 'rit.name','rl.name','m.firstname','m.lastname',
        'concat(m.firstname," ",m.lastname) as FullName','ri.max_applicant' );

        # text search
        try{ 
            if (!empty($filter->search)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];
                    $formated_date = '';
                    if($column_search=='DATE(ri.interview_start_datetime)' || $column_search=='DATE(ri.interview_end_datetime)'){
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
                                $this->db->or_like($serch_column[0], $filter->search);
                        }
                        else if ($column_search != 'null') {
                            $this->db->or_like($column_search, $filter->search);
                        }
                    }
    
    
                }
                $this->db->group_end();
            }
    
            $select_column = array(
                'ri.id as interview_id', 'ri.title', 'ri.owner','ri.location_id','ri.interview_stage_status',
                'ri.interview_type_id', 'ri.description', 'ri.interview_start_datetime', 'ri.interview_end_datetime',
                'ri.max_applicant', 'ri.invite_type', 'ri.form_id', 'ri.meeting_link','count(ria.id) as attendees',
                'rit.name as interview_type','rl.name as location' , 'concat(m.firstname," ",m.lastname) as owner_name',
                "concat(count(ria.id),'(',ri.max_applicant,')') as max_attendees",
                "count(ria.id) as attendees_count" , "ms.event_status as ms_event_status"
            );
    
            $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
    
            $this->db->select("(
                CASE WHEN ri.invite_type=1 THEN 'Quiz'
                     WHEN ri.invite_type=2 THEN 'Meeting Invite'
                     END) as invite_type_name");
    
            $this->db->select("(
                CASE WHEN ri.interview_stage_status=0 THEN 'Open'
                    WHEN ri.interview_stage_status=1 THEN 'Scheduled'
                    WHEN ri.interview_stage_status=2 THEN 'In progress'
                    WHEN ri.interview_stage_status=3 THEN 'Successful'
                    WHEN ri.interview_stage_status=4 THEN 'Unsuccessful'
                    END) as interview_stage_status_label");
            $this->db->select("(
                CASE WHEN ('".$currtent_date_time."' > ri.interview_end_datetime) THEN 'Expired'
                    Else 'Scheduled' end
                    ) as interview_time_status");
    
            $this->db->from('tbl_recruitment_interview as ri');
            $this->db->join('tbl_recruitment_interview_type as rit', 'ri.interview_type_id = rit.id', 'left');
            $this->db->join('tbl_recruitment_location as rl', 'ri.location_id = rl.id', 'left');
            $this->db->join('tbl_member as m', 'ri.owner = m.uuid', 'left');
            $this->db->join('tbl_ms_events_logs as ms', 'ri.ms_event_log_id = ms.id', 'left');
            $this->db->join(TBL_PREFIX . 'recruitment_interview_applicant as ria', 'ri.id = ria.interview_id and ria.archive=0 ', 'LEFT');
            $this->db->where('ri.archive', 0);
    
            $this->db->order_by($orderBy, $direction);
            $this->db->group_by('ri.id');
            $this->db->limit($limit, ($page * $limit));
            //list view filter condition
            if (!empty($filter_condition)) {
                $this->db->having($filter_condition);
            }
            # Throw exception if db error occur
            if (!$query = $this->db->get()) {               
                $db_error = $this->db->error();
                throw new Exception('Something went wrong!');
            }
            $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
    
            if ($dt_filtered_total % $limit == 0) {
                $dt_filtered_total = ($dt_filtered_total / $limit);
            } else {
                $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
            }
    
            $result = $query->result();
            foreach($result as $res) {
             if(!empty($res->max_applicant)){
                $res->attendees=$res->attendees.'(' .$res->max_applicant . ')';
             } 
             if(!empty($res->interview_stage_status) && $res->interview_stage_status==4){
                $res->unsuccessful_reason=$this->get_unsuccessful_reason_of_notes($res->interview_id);
             }
            }
            $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'total_item' => $total_item);
            return $return;
        }catch(\Exception $e){
            return array('status' => false, 'error' => 'Something went wrong');            
        }
        
    }

    /*
     * input interview_id
     * return true
     */
    function archive_interview($data, $adminId){

        $id = isset($data['id']) ? $data['id'] : 0;
        $isBulkArchive=$data['isBulkArchive']??false;
        if (empty($id) && !$isBulkArchive) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the interview exist?
        if(!$isBulkArchive)
        {
            $result = $this->get_interview_by_id($data['id']);
            if (empty($result)) {
                $response = ['status' => false, 'error' => "Interview does not exist anymore."];
                return $response;
            }
           $upd_data["updated"] = DATE_TIME;
           $upd_data["updated_by"] = $adminId;
           $upd_data["archive"] = 1;
           $this->basic_model->update_records("recruitment_interview", $upd_data, ["id" => $id]);
           $msg_title = "Successfully archived Group Booking";
           $this->add_interview_log($data, $msg_title, $data['id'], $adminId);
           $response = ['status' => true, 'msg' => $msg_title];
           return $response;
        }
        else {
            for ($i = 0; $i < count($data['bulk_archive_id']); $i++) {
                $result = $this->get_interview_by_id($data['bulk_archive_id'][$i]);
                if (empty($result)) {
                    $response = ['status' => false, 'error' => 'Interview '.$data['bulk_archive_id'][$i].' does not exist anymore.'];
                    return $response;
                }

                $upd_data["updated"] = DATE_TIME;
                $upd_data["updated_by"] = $adminId;
                $upd_data["archive"] = 1;
                $this->basic_model->update_records("recruitment_interview", $upd_data,  ["id" =>$data['bulk_archive_id'][$i]]);
                $msg_title = "Successfully archived Group Booking";
                $this->add_interview_log($data, $msg_title, $data['bulk_archive_id'][$i], $adminId);
            }
            if(count($data['bulk_archive_id'])>1){
                $msg=count($data['bulk_archive_id']) . ' records successfully archived';
            }else{
                $msg ='1 record successfully archived';
            }
            return  $response = ['status' => true, 'msg' =>$msg ];
        }

    }

     /*
     * input interview_id's
     * return true
     */
    function rollback_archived_interviews($data, $adminId){

        $id = isset($data['id']) ? $data['id'] : 0;
        $isBulkRetrieve=$data['isBulkRetrieve']??false;
        if (empty($id) && !$isBulkRetrieve) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the interview exist?
        if(!$isBulkRetrieve)
        {
            $result = $this->get_interview_by_id($data['id']);
            if (empty($result)) {
                $response = ['status' => false, 'error' => "Interview does not exist anymore."];
                return $response;
            }
           $upd_data["updated"] = DATE_TIME;
           $upd_data["updated_by"] = $adminId;
           $upd_data["archive"] = 0;
           $this->basic_model->update_records("recruitment_interview", $upd_data, ["id" => $id]);
           $msg_title = "Successfully Restored Group Booking";
           $this->add_interview_log($data, $msg_title, $data['id'], $adminId);
           $response = ['status' => true, 'msg' => $msg_title];
           return $response;
        }
        else {
            for ($i = 0; $i < count($data['retrieve_archive_id_list']); $i++) {
                $result = $this->get_interview_by_id($data['retrieve_archive_id_list'][$i]);
                if (empty($result)) {
                    $response = ['status' => false, 'error' => 'Interview '.$data['retrieve_archive_id_list'][$i].' does not exist anymore.'];
                    return $response;
                }

                $upd_data["updated"] = DATE_TIME;
                $upd_data["updated_by"] = $adminId;
                $upd_data["archive"] = 0;
                $this->basic_model->update_records("recruitment_interview", $upd_data,  ["id" =>$data['retrieve_archive_id_list'][$i]]);
                $msg_title = "Successfully Restored Group Booking";
                $this->add_interview_log($data, $msg_title, $data['retrieve_archive_id_list'][$i], $adminId);
            }
            if(count($data['retrieve_archive_id_list'])>1){
                $msg=count($data['retrieve_archive_id_list']) . ' records successfully restored';
            }else{
                $msg ='1 record successfully restored';
            }
            return  $response = ['status' => true, 'msg' =>$msg ];
          
        }
    }

    /*
     * input interview_id
     * return true
     */
    
    function archived_interview_list($reqData, $adminId){

        $limit = $reqData->pageSize ?? 9999;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = 'ri.created';
        $direction = 'DESC';
        $start_date = $filter->start_date ?? '';
        $end_date = $filter->end_date ?? '';
        $start_date = is_null($start_date) ? '' : $start_date;
        $end_date = is_null($end_date) ? '' : $end_date;
        $currtent_date_time =  DATE_TIME;
        $src_columns = array('ri.id','ri.title', 'DATE(ri.interview_start_datetime)','DATE(ri.interview_end_datetime)', 'rit.name','rl.name','m.firstname','m.lastname','concat(m.firstname," ",m.lastname) as FullName','ri.max_applicant' );

        # text search
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                $formated_date = '';
                if($column_search=='DATE(ri.interview_start_datetime)' || $column_search=='DATE(ri.interview_end_datetime)'){
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
                            $this->db->or_like($serch_column[0], $filter->search);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $filter->search);
                    }
                }


            }
            $this->db->group_end();
        }

        $select_column = array(
            'ri.id as interview_id', 'ri.title', 'ri.owner','ri.location_id','ri.interview_stage_status',
            'ri.interview_type_id', 'ri.description', 'ri.interview_start_datetime', 'ri.interview_end_datetime',
            'ri.max_applicant', 'ri.invite_type', 'ri.form_id', 'ri.meeting_link',
            'rit.name as interview_type','rl.name as location' , 'concat(m.firstname," ",m.lastname) as owner_name'
        );

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->select("(
            CASE WHEN ri.invite_type=1 THEN 'Quiz'
                 WHEN ri.invite_type=2 THEN 'Meeting Invite'
                 END) as invite_type_name");

        $this->db->select("(
            CASE WHEN ri.interview_stage_status=0 THEN 'Open'
                WHEN ri.interview_stage_status=1 THEN 'Scheduled'
                WHEN ri.interview_stage_status=2 THEN 'In progress'
                WHEN ri.interview_stage_status=3 THEN 'Successful'
                WHEN ri.interview_stage_status=4 THEN 'Unsuccessful'
                END) as interview_stage_status_label");
        $this->db->select("(
            CASE WHEN ('".$currtent_date_time."' > ri.interview_end_datetime) THEN 'Expired'
                Else 'Scheduled' end
                ) as interview_time_status");

        $this->db->from('tbl_recruitment_interview as ri');
        $this->db->join('tbl_recruitment_interview_type as rit', 'ri.interview_type_id = rit.id', 'left');
        $this->db->join('tbl_recruitment_location as rl', 'ri.location_id = rl.id', 'left');
        $this->db->join('tbl_member as m', 'ri.owner = m.id', 'left');


        $this->db->where('ri.archive', 1);

        $this->db->order_by($orderBy, $direction);
        $this->db->group_by('ri.id');
        $this->db->limit($limit, ($page * $limit));
        //list view filter condition
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'total_item' => $total_item);
        return $return;

    }
    

    /**
     * used by add/edit/delete interview
     */
    public function add_interview_log($data, $title, $interview_id, $adminId) {
        $this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);

        # create log setter getter
        $this->loges->setTitle($title." by ". $adminName);
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($interview_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
    }

    /*
     * Get list of applicant name
     * @param {object} $reqData
     */
    function get_applicant_name_search($reqData) {
        $search = $reqData->search ?? '';
        $limit = $reqData->limit ?? 50;
        $this->db->select(["concat_ws(' ',firstname,lastname) as label", "ra.id as id","'contact' as type"]);
        $this->db->select("(select pe.email from tbl_person_email as  pe where pe.person_id = ra.person_id and pe.archive = 0 and pe.primary_email = 1 limit 1) as subTitle");
        $this->db->from("tbl_recruitment_applicant as ra");
        $this->db->join('tbl_recruitment_applicant_applied_application as raaa', 'raaa.applicant_id = ra.id', 'left');

        $this->db->group_start();
        $this->db->or_like(array('ra.firstname' => $search, 'ra.lastname' => $search, "concat(ra.firstname,' ',ra.lastname)" =>$search));
        $this->db->group_end();

        $this->db->where("ra.archive", 0);
        $this->db->where_not_in('raaa.application_process_status', array('7','8'));
        $this->db->limit($limit);
        $this->db->order_by('label', 'asc');
        return $this->db->get()->result();
    }

    function get_application_data_by_applicant_id($applicant_id) {
        $this->db->select(['raaa.id as label',
            'raaa.id as value', 'raaa.jobId as job_id'
        ]);

        $this->db->from('tbl_recruitment_applicant_applied_application as raaa');
        $this->db->where(['raaa.applicant_id'=> $applicant_id, 'raaa.archive' => 0]);
        $query = $this->db->get();
        $res = $query->result();
        return $res;
    }

    function check_applicant_interview_exists($reqData, $action) {
        $this->db->select('ria.id');
        $this->db->from('tbl_recruitment_interview_applicant as ria');
        if($action == 'create'){
            $this->db->where(
                [
                 'ria.interview_id'=> $reqData['interview_id'], 'ria.applicant_id'=> $reqData['applicant_id'],
                 'ria.application_id'=> $reqData['application_id'], 'ria.archive'=> 0
                ]
            );
        }else{
            $this->db->where(
                [
                 'ria.id'=> $reqData['id']
                ]
            );
        }

        $query = $this->db->get();
        $res = $query->result();
        if(!$res){
            return ['status' => false];
        }else{
            return ['status' => true, 'msg' => "Applicant ".@$reqData['applicant_id']." already exists"];
        }
        return $res;
    }

    /**
     * Create update interview details
     */
    function create_update_applicant_interview($post_data, $adminId) {
        $id = $post_data['interview_applicant_id'] ?? 0;
        $save_applicant_data = array(
            'applicant_id'=> $post_data['applicant_id'],
            'application_id'=> $post_data['application_id'],
            'job_id' => $post_data['job_id'] ,
            'interview_id' => $post_data['interview_id'] ,
        );
        if($id){
            // Update interview applicant data
            $save_applicant_data["updated"] = DATE_TIME;
            $save_applicant_data["updated_by"] = $adminId;
            $this->Basic_model->update_records("recruitment_interview_applicant", $save_applicant_data, ["id" => $id]);
        }else{
            // Create interview applicant data
            $save_applicant_data["created"] = DATE_TIME;
            $save_applicant_data["created_by"] = $adminId;
            $interview_applicant_id = $this->Basic_model->insert_records('recruitment_interview_applicant', $save_applicant_data);
        }
        if($id){
            return ['status' => true, 'msg' => "Applicant updated successfully" , 'interview_applicant_id' => $id];
        }else{
            return ['status' => true, 'msg' => "Applicant ".$interview_applicant_id." created successfully" , 'interview_applicant_id' => $interview_applicant_id];
        }

    }

    public function get_existing_applicant_list_by_interview_id($interview_id) {
        if (!empty($interview_id)) {
            $limit = $reqData->pageSize?? 99999;
            $page = $reqData->page?? 0;
            $sorted = $reqData->sorted?? NULL;
            $filter = $reqData->filtered?? NULL;
            $orderBy = 'ria.id';
            $direction = 'DESC';            

            $select_column = array('ria.*','concat(ra.firstname," ",ra.lastname) as applicant_name','rj.title as job_name','ri.invite_type', 'rae.email', 
                                   'ra.firstname','rfa.flag_status','raaa.application_process_status');

            $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

            $this->db->from(TBL_PREFIX . 'recruitment_interview_applicant as ria');
            $this->db->join(TBL_PREFIX . 'recruitment_interview as ri', 'ri.id = ria.interview_id', 'INNER');
            $this->db->join(TBL_PREFIX . 'recruitment_applicant as ra', 'ra.id = ria.applicant_id', 'INNER');
            $this->db->join(TBL_PREFIX . "recruitment_applicant_email as rae", "rae.applicant_id = ra.id AND rae.archive = 0 AND rae.primary_email = 1", "INNER");
            $this->db->join(TBL_PREFIX . 'recruitment_job as rj', 'rj.id = ria.job_id', 'INNER');
            $this->db->join(TBL_PREFIX . 'recruitment_applicant_applied_application as raaa', 'raaa.id = ria.application_id', 'LEFT');
            $this->db->join(TBL_PREFIX . 'recruitment_flag_applicant as rfa', 'ra.id = rfa.applicant_id', 'LEFT');
            $this->db->where([ 'ria.interview_id' => $interview_id,'ria.archive' => 0]);
            
            $this->db->group_by('ria.id');
            $this->db->order_by($orderBy, $direction);
            $this->db->limit($limit, ($page * $limit));

            $query = $this->db->get() ;
            $dataResult = $query->result();
            return array('data' => $dataResult, 'status' => true);

        }
    }

    public function get_applicant_list_by_interview_id($reqData, $applicant_ids = []) {
        if (!empty($reqData)) {
            $limit = $reqData->pageSize?? 20;
            $page = $reqData->page?? 0;
            $sorted = $reqData->sorted?? null;
            $filter = $reqData->filtered?? null;
            $orderBy = '';
            $direction = '';

            $src_columns = array("ria.id", 'concat(ra.firstname," ",ra.lastname) as applicant_name', "ria.applicant_id", "rj.title","DATE(ria.created)","rae.email");

            # text search
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                $formated_date = '';
                if($column_search=='DATE(ria.created)'){
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
                            $this->db->or_like($serch_column[0], $filter->search);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $filter->search);
                    }
                }


            }
            $this->db->group_end();
        }
            $available_column = array("id", "interview_id", "applicant_id", "application_id", "job_id","applicant_name","job_name","email","attendee_response","applicant_meeting_status","invited_on");
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {                    
                if($sorted[0]->id=="email"){
                    $orderBy = 'rae.email';
                }
                else if($sorted[0]->id == 'id'){
                        $orderBy = 'ria.id';
                }else{
                    $orderBy = $sorted[0]->id;
                }
                    $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }else {
                $orderBy = 'ria.id';
                $direction = 'DESC';
            }
            $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

            // Filter by status
            if (!empty($filter->filter_status)) {
                if ($filter->filter_status !== "all") {
                    $this->db->where('ria.id', $filter->filter_status);
                }
            }

            $select_column = array('ria.*','concat(ra.firstname," ",ra.lastname) as applicant_name','rj.title as job_name','ri.invite_type', 'rae.email', 'ra.firstname','rfa.flag_status','raaa.application_process_status');

            $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

            $this->db->select("(
                   CASE WHEN ria.interview_meeting_status=1 THEN 'Successful'
                    WHEN ria.interview_meeting_status=2 THEN 'Unsuccessful'
                    WHEN ria.interview_meeting_status=3 THEN 'Did not show'
                    WHEN ria.event_status=1 and ria.archive =0  THEN 'Canceled'
                    END
                    ) as applicant_meeting_status");
            $this->db->select("(
                CASE WHEN ria.attendee_response=1 THEN 'Accepted'
                    WHEN ria.attendee_response=2 THEN 'Tentative'
                    WHEN ria.attendee_response=3 THEN 'Declined'
                    Else '' 
                    END
                    ) as attendee_response");

            $this->db->select("(select pe.email from tbl_person_email as  pe where pe.person_id = ra.id and pe.archive = 0 and pe.primary_email = 1 limit 1) as subTitle");
            $this->db->from(TBL_PREFIX . 'recruitment_interview_applicant as ria');
            $this->db->join(TBL_PREFIX . 'recruitment_interview as ri', 'ri.id = ria.interview_id', 'INNER');
            $this->db->join(TBL_PREFIX . 'recruitment_applicant as ra', 'ra.id = ria.applicant_id', 'INNER');
            $this->db->join(TBL_PREFIX . "recruitment_applicant_email as rae", "rae.applicant_id = ra.id AND rae.archive = 0 AND rae.primary_email = 1", "INNER");
            $this->db->join(TBL_PREFIX . 'recruitment_job as rj', 'rj.id = ria.job_id', 'INNER');
            $this->db->join(TBL_PREFIX . 'recruitment_applicant_applied_application as raaa', 'raaa.id = ria.application_id', 'LEFT');
            $this->db->join(TBL_PREFIX . 'recruitment_flag_applicant as rfa', 'ra.id = rfa.applicant_id', 'LEFT');
            $this->db->where([ 'ria.interview_id' => $reqData->interview_id,'ria.archive' => 0]);
            //if ids are given
            if (!empty($applicant_ids)) {
                $this->db->where_in('ria.applicant_id', $applicant_ids);
            }
            $this->db->group_by('ria.id');
            $this->db->order_by($orderBy, $direction);
            $this->db->limit($limit, ($page * $limit));

            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            #last_query();die;
            $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

            if ($dt_filtered_total % $limit == 0) {
                $dt_filtered_total = ($dt_filtered_total / $limit);
            } else {
                $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
            }

            $dataResult = $query->result();

            $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'status' => true, 'total_count' => $total_count);
            return $return;
        }
    }

    /*
     * input interview_applicant_id
     * return true
     */
    function archive_applicant_interview($data, $adminId){

        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the interview exist?
        $result = $this->check_applicant_interview_exists($data,'archive');
        if (!$result['status']) {
            $response = ['status' => false, 'error' => "Applicant Interview does not exist anymore."];
            return $response;
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $upd_data["event_status"] = 1; // canceled the MS event
        $this->basic_model->update_records("recruitment_interview_applicant", $upd_data, ["id" => $id]);

        $msg_title = "Successfully archived applicant";
        $this->add_interview_log($data, $msg_title, $data['id'], $adminId);
        $response = ['status' => true, 'msg' => $msg_title];

        return $response;
    }

     /**
     * Return history items of a Applications
     * @param $data object
     * @return array
     */
    public function get_field_history($data)
    {
        $items = $this->db->select(['h.id','hf.created_at', 'h.id as history_id', 'f.id as field_history_id', 'f.field', 'f.value', 'f.prev_val', 'h.created_at', 'CONCAT(m.firstname, \' \', m.lastname) as created_by', 'h.created_at', 'hf.desc as feed_title', 'hf.id as feed_id', 'hf.feed_type'])
            ->from(TBL_PREFIX . 'recruitment_interview_history as h')
            ->where(['h.interview_id' => $data->interview_id])
            ->join(TBL_PREFIX . 'recruitment_interview_field_history as f', 'f.history_id = h.id', 'left')
            ->join(TBL_PREFIX . 'recruitment_interview_history_feed as hf', 'hf.history_id = h.id', 'left')
            ->join(TBL_PREFIX . 'member as m', 'm.uuid = h.created_by', 'left')
            ->order_by('h.id', 'DESC')
            ->get()->result();

        $this->load->model('Feed_model');
        $related_type = $this->Feed_model->get_related_type('interview');
        $feed = [];
        // map fields to rendered values
        foreach ($items as $item) {

            $item->related_type = $related_type;
            $item->expanded = true;
            $item->feed = false;
            $item->comments = [];
            $history_id = $item->history_id;
            // history comments
            $comments = $this->Feed_model->get_comment_by_history_id($item->history_id, $related_type);
            $item->comments = $comments;
            $item->comments_count = count($comments);
            $item->comment_create = false;
            $item->comment_post = false;
            $item->comment_desc = '';
            switch ($item->field) {

                case 'owner':
                    $owner = $this->db->from(TBL_PREFIX . 'member as m')->select('CONCAT(m.firstname, \' \', m.lastname) as user')->where(['id' => $item->value])->get()->result();
                    $prev_owner = $this->db->from(TBL_PREFIX . 'member as m')->select('CONCAT(m.firstname, \' \', m.lastname) as user')->where(['id' => $item->prev_val])->get()->result();
                    $item->value = !empty($owner) ? $owner[0]->user : 'N/A';
                    $item->prev_val = !empty($prev_owner) ? $prev_owner[0]->user : 'N/A';
                break;
                case 'location_id':
                    $location = $this->db->from(TBL_PREFIX . 'recruitment_location as rl')->select('rl.name')->where(['id' => $item->value])->get()->result();
                    $prev_location = $this->db->from(TBL_PREFIX . 'recruitment_location as rl')->select('rl.name')->where(['id' => $item->prev_val])->get()->result();
                    $item->value = !empty($location) ? $location[0]->name : 'N/A';
                    $item->prev_val = !empty($prev_location) ? $prev_location[0]->name : 'N/A';
                break;
                case 'interview_type_id':
                    $type = $this->db->from(TBL_PREFIX . 'recruitment_interview_type as rit')->select('rit.name')->where(['id' => $item->value])->get()->result();
                    $prev_type = $this->db->from(TBL_PREFIX . 'recruitment_interview_type as rit')->select('rit.name')->where(['id' => $item->prev_val])->get()->result();
                    $item->value = !empty($type) ? $type[0]->name : 'N/A';
                    $item->prev_val = !empty($prev_type) ? $prev_type[0]->name : 'N/A';
                break;
                case 'invite_type':
                    $item->value = $item->value == 1 ? 'Quiz' : 'Meeting Invite';
                    $item->prev_val = $item->prev_val == 1 ? 'Quiz' : 'Meeting Invite';
                // case 'form_id':
                //     $type = $this->db->from(TBL_PREFIX . 'recruitment_form as rf')->select('rf.title')->where(['id' => $item->value])->get()->result();
                //     $prev_type = $this->db->from(TBL_PREFIX . 'recruitment_form as rf')->select('rf.title')->where(['id' => $item->prev_val])->get()->result();
                //     $item->value = !empty($type) ? $type[0]->title : 'N/A';
                //     $item->prev_val = !empty($prev_type) ? $prev_type[0]->title : 'N/A';
                break;
                case 'NULL':
                case '':
                    $item->feed = true;
                    break;
                default:
                    $item->value = !empty($item->value) ? $item->value : 'N/A';
                    $item->prev_val = !empty($item->prev_val) ? $item->prev_val : 'N/A';
                    break;
            }
            if ($item->feed_id == '' || $item->feed_id == NULL) {
                $item->feed = false;
            }
            if (($item->field_history_id != '' && $item->field_history_id != NULL) || ($item->feed_id != '' && $item->feed_id != NULL)) {
                $feed[$history_id][] = $item;
            }
        }
        //krsort($feed);
        $feed = array_values($feed);
        return $feed;
    }

    /**
     * Create history item for each change field
     * @param array $existingInterview Existing interview data
     * @param array $dataToBeUpdated Modified data of Lead
     * @return void
     */
    public function updateHistory($existingInterview, $dataToBeUpdated, $adminId) {
        if (!empty($dataToBeUpdated)) {
            $new_history = $this->db->insert(
                TBL_PREFIX . 'recruitment_interview_history',
                [
                    'interview_id' => $existingInterview['id'],
                    'created_by' => $adminId,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            );
            $history_id = $this->db->insert_id();
            foreach($dataToBeUpdated as $field => $new_value) {
                    if (array_key_exists($field, $existingInterview) && $existingInterview[$field] != $new_value && !empty($new_history)) {
                        $this->create_field_history($history_id, $existingInterview['id'], $field, $new_value, $existingInterview[$field]);
                }

            }
        }
    }
   /**
     * Create history record to be used for all history items in the update
     * @param int $history_id Id of related update history
     * @param int $interview_id
     * @param string $fieldName
     * @param string $new_value
     * @param string $oldValue
     * @return int Last insert id
     */
    public function create_field_history($history_id, $interview_id, $fieldName, $newValue, $oldValue) {
        return $this->db->insert(TBL_PREFIX . 'recruitment_interview_field_history', [
            'history_id' => $history_id,
            'interview_id' => $interview_id,
            'field' => $fieldName,
            'prev_val' => $oldValue,
            'value' => $newValue ?? ''
        ]);
    }

    /**
     * calculate the duration between two timings and returns in HH:MM format
     */
    function calculate_interview_duration($interview_details) {
        $currtent_date_time = DATE_TIME;
        $interview_details['interview_start_datetime'] = $interview_details['interview_start_date']." ".$interview_details['interview_start_time'];
        $interview_details['interview_end_datetime'] = $interview_details['interview_end_date']." ".$interview_details['interview_end_time'];

        if((strtotime($interview_details['interview_start_datetime'])) > (strtotime($interview_details['interview_end_datetime']))){
            return ['status' => false,  'data' => "00:00", 'error' => "Incorrect Group Booking start & end date/time"];
        }
        if($interview_details['interview_start_datetime'] == $interview_details['interview_end_datetime']){
            return ['status' => false, 'data' => "00:00" , 'error' => "End time should be greater than start time"];
        }

        $interview_hours_details = $this->calc_duration($interview_details);
        list($total_mins) = $interview_hours_details;
        $duration = get_hour_minutes_from_int($total_mins);

        $response = ['status' => true, 'data' => $duration];
        return $response;
    }

     /**
     * calculating total interview hours and individual break hours based on break category
     */
    public function calc_duration($data) {

        $start_datetime = $data['interview_start_datetime'];
        $end_datetime = $data['interview_end_datetime'];

        # calculating total interview durations in minutes
        $total_mins = minutesDifferenceBetweenDate($start_datetime, $end_datetime);

        $return = [$total_mins];
        return $return;
    }

     /**
     * fetches all the interview statuses
     */
    public function get_interview_stage_status() {
        $data = null;
        foreach($this->interview_stage_status_grouped as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * fetches all the final interview statuses
     */
    public function get_interview_statuses_final() {
        $data = null;
        foreach($this->interview_stage_status_final as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * Updating the interview status.
     */
    function update_interview_status($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;
        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the interview exist?
        $result = $this->get_interview_by_id($data['id']);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Group booking does not exist anymore."];
            return $response;
        }

        # updating status
        $upd_data["interview_stage_status"] = $data['interview_stage_status'];
        $upd_data["updated"] = DATE_TIME;
        $this->basic_model->update_records("recruitment_interview", $upd_data, ["id" => $id]);

        if ($data['interview_stage_status'] == "4" && !empty($data['reason_drop'])) {
            $reson_data = [
                "interview_id" => $data['id'],
                "reason" =>  $data['reason_drop']?? "",
                "reason_note" =>  $data['reason_note']?? "",
            ];

            $this->basic_model->update_records("recruitment_interview_unsuccessful_reason", ["archive" => 1], ["interview_id" => $data['id']]);
            $this->basic_model->insert_records("recruitment_interview_unsuccessful_reason", $reson_data, false);
        }
        //join for applicants data
        $joins = [
            ['left' => ['table' => 'recruitment_applicant AS ra', 'on' => 'ria.applicant_id = ra.id']],
            ['left' => ['table' => 'person AS p', 'on' => 'ra.person_id = p.id']]
        ];
        $interview_applicants = $this->get_rows("recruitment_interview_applicant as ria", ["ria.id","ria.interview_meeting_status", "ra.firstname", "p.username", "ra.id as applicant_id","ria.application_id"], ["ria.archive" => 0, "ria.interview_id" => $id], $joins);
        if($data['interview_stage_status'] == "3" || $data['interview_stage_status'] == "4"){
            #check interview applicant exist
            if (!empty($interview_applicants)) {
                #update the interview related applicant as success or unsuccess
                $interview_meeting_status = 1; // successful
                if($data['interview_stage_status'] == "4"){
                    $interview_meeting_status = 2; // unsuccessful
                }
                foreach ($interview_applicants as $item) {
                    if($item->interview_meeting_status==0 && $data['selected_final_status_reason'] != 'Others' && $interview_meeting_status == 2){
                        $this->basic_model->update_records("recruitment_interview_applicant", ["event_status" => 1, "archive"=>0], ["interview_id" => $data['id'] , "id" => $item->id]);                                               
                    }else if($interview_meeting_status==1 && $item->interview_meeting_status==0){
                        $this->basic_model->update_records("recruitment_interview_applicant", ["interview_meeting_status" => $interview_meeting_status], ["interview_id" => $data['id'] , "id" => $item->id]);
                    }
                    
                }

            }
        }

        # adding a log entry
        $msg = "Group booking status is updated successfully";
        $this->add_create_update_interview_log($upd_data, $msg, $adminId, $id);
        //dispatch event
        $this->load->library('EventDispatcher');
        //load data for template
        $template_data = [];
        //organize data for each applicant to use in email template
        if (!empty($interview_applicants)) {
            foreach($interview_applicants as $interview_applicant) {
                $obj = clone $result['data'];
                $obj->meeting_link = $obj->meeting_link?? "N/A";
                $obj->interview_start_datetime = !empty($obj->interview_start_datetime) && $obj->interview_start_datetime != '0000-00-00 00:00:00'? date('d/m/Y h:i A', strtotime($obj->interview_start_datetime)) : 'N/A';
                $obj->interview_end_datetime =  !empty($obj->interview_end_datetime) && $obj->interview_end_datetime != '0000-00-00 00:00:00'? date('d/m/Y h:i A', strtotime($obj->interview_end_datetime)) : 'N/A';
                $obj->firstname = $interview_applicant->firstname?? "";
                $obj->interview_location = $obj->location['label']?? "N/A";
                $obj->userId = $interview_applicant->applicant_id?? 0;
                $template_data[$interview_applicant->username] = $obj;
            }
        }
        if (!empty($template_data)) {
            $this->eventdispatcher->dispatch('onAfterGroupbookingUpdated', $id, $template_data);
        }
        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /*
	 * used by the create_update_application function to insert a log entry on
     * application adding / updating
     */

    public function add_create_update_interview_log($data, $title, $adminId, $application_id) {
    	$this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle($title);
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($application_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
    }

    function get_unsuccessful_reason_of_notes($interview_id) {
        $res = $this->basic_model->get_row("recruitment_interview_unsuccessful_reason", ["id", "reason", "reason_note"], ["archive" => 0, "interview_id" => $interview_id]);

        if (!empty($res)) {
            $this->db->select(["r.display_name"]);
            $this->db->from(TBL_PREFIX . 'references as r');
            $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = 'unsuccessful_group_booking_reason' AND rdt.archive = 0", "INNER");
            $this->db->where("r.id", $res->reason);
            $res->reason_label = $this->db->get()->row("display_name");
        }

        return $res;
    }

    /**
     * Get interview details
     */
    function get_group_booking_applicant_by_id($applicant_interview_id) {

        $this->db->select([ "ria.*"]);
        $this->db->from("tbl_recruitment_interview_applicant as ria");
        $this->db->where("ria.id =" , $applicant_interview_id);

        $query = $this->db->get();
        $applicant_interview = $query->row();

        if (!$applicant_interview) {
            return ['status' => false, 'error' => "Applicant is not exist. something went wrong"];
        }

        return ['status' => true, 'msg' => "Applciant detail fetched successfully", 'data' => $applicant_interview];
    }

     /**
     * Get interview details by interview id
     */
    function get_group_booking_applicant_list_by_int_id($applicant_interview_id) {

        $this->db->select([ "ria.*"]);
        $this->db->from("tbl_recruitment_interview_applicant as ria");
        $this->db->where("ria.interview_id" , $applicant_interview_id);
        $this->db->where("ria.archive =" , 0);

        $query = $this->db->get();
        $applicant_interview = $query->result();

        if (!$applicant_interview) {
            return ['status' => false, 'error' => "Applicant is not exist. something went wrong"];
        }

        return ['status' => true, 'msg' => "Applciant detail fetched successfully", 'data' => $applicant_interview];
    }


    /**
     * Updating the applicant interview status.
     */
    function update_applicant_interview_status($data, $adminId) {

        $id = isset($data['id']) ? $data['id'] : 0;
        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the interview exist?
        $result = $this->get_group_booking_applicant_by_id($data['id']);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Group booking does not exist anymore."];
            return $response;
        }

        # updating status
        $upd_data["interview_meeting_status"] = $data['interview_meeting_status'];
        $upd_data["updated"] = DATE_TIME;
        $this->basic_model->update_records("recruitment_interview_applicant", $upd_data, ["id" => $id]);

        if($data['interview_meeting_status']==2){
            #Here 8 for unsuccessful application
            $application_status_data['id'] = $data['application_id'] ?? 0;
            $application_status_data['application_process_status'] = 8;
            $application_status_data['applicant_id'] = $data['applicant_id'] ?? 0;
            if(!empty($application_status_data)){
                $this->Recruitment_applicant_model->update_application_status($application_status_data, $adminId, true);
            }
            }

        # adding a log entry
        if (!empty($data['email_status_update']) && $data['email_status_update']) {
            $msg = "Applicant status is updated and email sent successfully";
        }else{
            $msg = "Applicant status is updated successfully";
        }

        $this->add_create_update_interview_log($upd_data, $msg, $adminId, $id);
        //send email
        $url = base_url()."recruitment/RecruitmentInterview/bulk_send_applicant_interview_status_email";
        $param = array('data' => $data,'applicant_ids' => [$data['applicant_id']], 'adminId' => $adminId );
        $this->asynclibrary->do_in_background($url, $param);
        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /**
     * Updating the applicant interview status.
     */
    function bulk_update_applicant_interview_status($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;      
        if (empty($id) || empty(isset($data['interview_meeting_status']))) {
            $response = ['status' => false, 'error' => "Requested data is null"];
            return $response;
        }

        # does the interview exist?
        $result = $this->get_interview_by_id($data['id']);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Group booking does not exist anymore."];
            return $response;
        }   
        if (!empty($data['selected_interview_applicant'])) {
            #update the interview related applicant as success or unsuccess or did not show
            // 1 -successful , 2- unsuccessful , 3- did not show
            $application_status_data = [];
            $applicant_ids = [];
            foreach ($data['selected_interview_applicant'] as $item) {
                if($item->interview_meeting_status==0){
                    if($data['interview_meeting_status']==2){
                        #Here 8 for unsuccessful application
                        $application_status_data['id'] = $item->application_id ?? 0;
                        $application_status_data['application_process_status'] = 8;
                        $application_status_data['applicant_id'] = $item->applicant_id ?? 0;
                            if(!empty($application_status_data)){
                                $this->Recruitment_applicant_model->update_application_status($application_status_data, $adminId, true);
                            }
                        }
                    $this->basic_model->update_records("recruitment_interview_applicant", ["interview_meeting_status" => $data['interview_meeting_status']], ["interview_id" => $data['id'] , "id" => $item->interview_applicant_id]);                    
                    
                    if (!empty($data['email_status_update'])) {
                        $applicant_ids[] = $item->applicant_id;
                    }                    
                }
            }
            
            $url = base_url()."recruitment/RecruitmentInterview/bulk_send_applicant_interview_status_email";
            $param = array('data' => $data,'applicant_ids' => $applicant_ids, 'adminId' => $adminId );
            $this->asynclibrary->do_in_background($url, $param);
        }
        

        # adding a log entry
        if (!empty($data['email_status_update']) && $data['email_status_update']) {
            $msg = "Applicant status is updated and email sent successfully";
        }else{
            $msg = "Applicant status is updated successfully";
        }
        

        $response = ['status' => true, 'msg' => $msg];
        return $response;
     }


    /**
     * fetches list of interview by search
     */
    public function get_interviews_list_by_search($reqData, $adminId) {
        $orderBy = 'ri.created';
        $direction = 'DESC';
        $currtent_date_time =  DATE_TIME;

        $select_column = array('ri.title as label', 'ri.id as value');

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_recruitment_interview as ri');

        $this->db->where(['ri.archive'=> 0,'ri.interview_end_datetime >'=> $currtent_date_time ]);
        $this->db->where_in('ri.interview_stage_status', ['0','1','2']); // retriving only open/schedule/inprogress stages

        $this->db->order_by($orderBy, $direction);
        $this->db->group_by('ri.id');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $result = $query->result();
        return array('data' => $result, 'status' => true);
    }

    /**
     * fetches max applicant count , added applicants count
     */
    public function get_max_applicant_details($reqData, $adminId) {
        $location_sub_query = $this->get_location_sub_query('location_id','ri');
        $interview_type_sub_query = $this->get_interview_type_by_sub_query('interview_type_id','ri');
        $select_column = array(
            'ri.id as interview_id','ri.max_applicant','ri.interview_start_datetime','ri.interview_end_datetime','ri.location_id','ri.meeting_link','ri.interview_type_id','ri.ms_event_log_id',
            "ms.event_id", "ms.interview_type_id as event_interview_type_id", "ms.odata_context as ms_event_org_id","ms.event_status as ms_event_status",
            "m.uuid as owner","CONCAT_WS(' ', m.firstname,m.lastname) as owner_name","m.username as owner_email",
            '(SELECT COUNT(id) FROM tbl_recruitment_interview_applicant ria WHERE ria.archive = 0 AND ria.interview_id = '.$reqData->id.') AS added_applicant_count'            
        );
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_recruitment_interview as ri');
        $this->db->join('tbl_ms_events_logs as ms', 'ri.ms_event_log_id = ms.id', 'left');
        $this->db->join('tbl_member as m', 'ri.owner = m.uuid', 'left');
        $this->db->select("(" . $location_sub_query . ") as location");
        $this->db->select("(" . $interview_type_sub_query . ") as interview_type");
        $this->db->where(['ri.archive'=> 0,'ri.id '=> $reqData->id ]);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $interview = $query->row();

       
        
        
        if(!empty($interview->interview_start_datetime) && $interview->interview_start_datetime != "0000-00-00 00:00:00"){
            $interview->interview_start_time = get_time_id_from_series($interview->interview_start_datetime);
            $interview->interview_start_date = date("Y-m-d", strtotime($interview->interview_start_datetime) );
        }else{
            $interview->interview_start_datetime = '';
            $interview->interview_start_time = '';
            $interview->interview_start_date = '';
        }       

        if(!empty($interview->interview_end_datetime) && $interview->interview_end_datetime != "0000-00-00 00:00:00"){
            $interview->interview_end_time = get_time_id_from_series($interview->interview_end_datetime);
            $interview->interview_end_date = date("Y-m-d", strtotime($interview->interview_end_datetime) );
        }else{
            $interview->interview_end_datetime = '';
            $interview->interview_end_time = '';
            $interview->interview_end_date = '';
        }
       
        if(!empty($interview->interview_id)){
            $interview->applicants=$this->get_existing_applicant_list_by_interview_id($interview->interview_id);
        }      

        return array('data' => $interview, 'status' => true);
    }


    /**
     * Create update interview details
     */
    function create_bulk_applicant_interview($post_data, $adminId, $testcase = false) {
        $post_data = $testcase ? json_decode($post_data) : $post_data;

        $interview_applicant_ids = [];
        $interview = $this->basic_model->get_row("recruitment_interview", ["id","max_applicant"], ["archive" => 0, "id" => $post_data->interview_id]);

            if( !empty($interview->max_applicant) && count($post_data->applications) > $interview->max_applicant){
                $msg = 'Select only '.$interview->max_applicant.' applications in order to update the stage and assign them to the selected group booking';
                $status = false;
            }else{
                $applicant_ids = [];
                $application_ids = [];
                $job_ids = [];
                $interview_ids = [];
                foreach ($post_data->applications as $application) {
                    $save_applicant_data[] = array(
                        'applicant_id'=> $application->applicant_id,
                        'application_id'=> $application->application_id,
                        'job_id' => $application->jobId ,
                        'interview_id' => $post_data->interview_id ,
                        'created' => DATE_TIME ,
                        'created_by' => $adminId,

                    );
                    $applicant_ids[] = $application->applicant_id;
                    $application_ids[] = $application->application_id;
                    $job_ids[] = $application->jobId;
                    $interview_ids[] = $post_data->interview_id;
                }
                if (!empty($applicant_ids)) {
                    $this->db->select("*");
                    $this->db->from(TBL_PREFIX."recruitment_interview_applicant");
                    $this->db->where_in('applicant_id', $applicant_ids);
                    $this->db->where_in('application_id', $application_ids);
                    $this->db->where_in('job_id', $job_ids);
                    $this->db->where_in('interview_id', $interview_ids);
                    $this->db->where('archive', 0);
                    $q = $this->db->get();
                    $s=$this->db->last_query();
                    $res = $q->result();
                    foreach($save_applicant_data as $k => $appdata) {
                        if ($this->applicant_exists($res, $appdata)) {
                            unset($save_applicant_data[$k]);
                        }
                    }
                    if (!empty($save_applicant_data)) {
                        $interview_applicant_ids = $this->insert_records('recruitment_interview_applicant', $save_applicant_data, true);
                    }
                }
            $status = true;
            $msg = 'Applicants added';
        }
        return ['status' => $status, 'msg' => $msg , 'interview_applicant_id' => $interview_applicant_ids];


    }

    public function applicant_exists($rows, $appdata) {
        foreach($rows as $row) {
            if ($row->applicant_id == $appdata['applicant_id'] && $row->application_id == $appdata['application_id'] && $row->job_id == $appdata['job_id'] && $row->interview_id == $appdata['interview_id']) {
                return true;
            }
        }
        return false;
    }

    public function getRecipientTypes()
    {
        $object_recipient_types = [
            'GroupBooking.Users' => 'Group Booking Users'
        ];
        $this->setRecipientTypes($object_recipient_types);
        unset($this->recipient_types['Creator']);
        unset($this->recipient_types['Owner']);
        return $this->recipient_types;
    }

    public function setInterviewRecipients()
    {
        //field should match with $this->object_fields
        $recipients['GroupBooking.Users'] = [
            ['field' => 'Applicants', 'label' => 'Applicants']
        ];
        $this->setObjectRecipients($recipients);
    }

    // Resend invite mail to list of selected applicants
    public function resend_invite_to_applicants($data, $adminId){
        $applicant_data = $data->applicants;
        $interview_id = $data->interview_id;
        $owner = $data->owner;
        $interview_type = $data->interview_type;
        $this->load->model('../../admin/models/Notification_model');
        $result = [];
        foreach($applicant_data as $applicant) {
            // Send bulk group booking email with notification
            $this->send_group_booking_bulk_invite_mail_to_applicant($applicant->applicant_id, $applicant->application_id, $adminId, $interview_id, $interview_type);

            $notification_data['title'] = 'Group booking';
            $notification_data['shortdescription'] = 'Interview: Please check the invite sent to your mailbox';
            $notification_data['userId'] = $applicant->applicant_id;
            $notification_data['user_type'] = 5;
            $notification_data['status'] = 0;
            $notification_data['sender_type'] = 2;
            $notification_data['created'] = DATE_TIME;
            $notification_data['specific_admin_user'] = $owner->value ?? NULL;
            $notification_data['entity_type'] = 9;
            $notification_data['entity_id'] = $interview_id;

            $this->Notification_model->create_notification($notification_data);
            // update the invited on details for applicants
            
            if(!empty($applicant->interview_applicant_id)){
                $this->basic_model->update_records("recruitment_interview_applicant", ["invited_on"=>DATE_TIME], ["id" => $applicant->interview_applicant_id]);
            }else{
                $this->basic_model->update_records("recruitment_interview_applicant", ["invited_on"=>DATE_TIME], ["applicant_id" => $applicant->applicant_id, "application_id" => $applicant->application_id, "interview_id" => $interview_id]);
            }

            $result[] = ['status' => true, 'msg' => 'success', 'applicant_id'=> $applicant->applicant_id];
        }
        return $result;
    }

    // Resend invite mail to list of selected applicants
    public function async_resend_invite_to_applicants($applicantData, $reqData, $adminId){
        $interview_id = $reqData['interview_id'];
        $owner = $reqData['owner'];
        $interview_type = $reqData['interview_type'];
        $this->load->model('../../admin/models/Notification_model');
            // Send bulk group booking email with notification
            $this->send_group_booking_bulk_invite_mail_to_applicant($applicantData['applicant_id'], $applicantData['application_id'], $adminId, $interview_id, $interview_type);

            $notification_data['title'] = 'Group booking';
            $notification_data['shortdescription'] = 'Interview: Please check the invite sent to your mailbox';
            $notification_data['userId'] = $applicantData['applicant_id'];
            $notification_data['user_type'] = 5;
            $notification_data['status'] = 0;
            $notification_data['sender_type'] = 2;
            $notification_data['created'] = DATE_TIME;
            $notification_data['specific_admin_user'] = $owner->value ?? NULL;
            $notification_data['entity_type'] = 9;
            $notification_data['entity_id'] = $interview_id;

            $this->Notification_model->create_notification($notification_data);

            // update the invited on details for applicants            
            if(!empty($applicantData['interview_applicant_id'])){
                $this->basic_model->update_records("recruitment_interview_applicant", ["invited_on"=>DATE_TIME], ["id" => $applicantData['interview_applicant_id']]);
            }else{
                $this->basic_model->update_records("recruitment_interview_applicant", ["invited_on"=>DATE_TIME], ["applicant_id" => $applicantData['applicant_id'], "application_id" => $applicantData['application_id'], "interview_id" => $interview_id]);
            }

      
        return ['status' => true, 'msg' => 'success', 'applicant_id'=> $applicantData['applicant_id']];
    }

    /**
     * sending bulk group booking email to applicant/applicants
     */
    function send_group_booking_bulk_invite_mail_to_applicant($applicant_id, $application_id, $adminId, $interview_id, $interview_type)
    {
        $this->load->model("Recruitment_task_action");
        # grabbing applicant details
        $applicant_details = $this->Recruitment_task_action->get_applicant_name_appid_email([$applicant_id]);
        $applicant = $applicant_details[$applicant_id];
 
        // interview Details
        $interview_details = $this->get_interview_by_id($interview_id);
        $applicant["interview_start_datetime"] = DateFormate($interview_details['data']->interview_start_datetime, DATE_TIME_VIEW_FORMAT);
        $applicant["interview_end_datetime"] = DateFormate($interview_details['data']->interview_end_datetime, DATE_TIME_VIEW_FORMAT);
        $applicant["interview_location"] =$interview_details['data']->location ? $interview_details['data']->location['label'] : 'N/A';
        $applicant["meeting_link"] =$interview_details['data']->meeting_link ? "<a href=".$interview_details['data']->meeting_link."  style='background:#0b09a233; color: #0000ff;width: auto;padding: 0;text-align: center; width:150px; padding:2px 9px; border-radius: 6px; text-align: center'>Join conversation</a>" : 'N/A';              
        # grabbing job title
        $applicant["job_title"] = $this->Recruitment_task_action->get_application_job_title($application_id);

        # grabbing admin user details
        $admin_d = $this->Recruitment_task_action->get_admin_firstname_lastname($adminId);

        $applicant['admin_firstname'] = $admin_d['firstname'] ?? '';
        $applicant['admin_lastname'] = $admin_d['lastname'] ?? '';

        require_once APPPATH . 'Classes/Automatic_email.php';
        $obj = new Automatic_email();

        if($interview_type=='Group Interview'){
            $obj->setEmail_key("group_booking_confirmation");
        }else{
            $obj->setEmail_key("group_booking_cab_day_invite");
        }
       
        $obj->setEmail($applicant['email']);
        $obj->setDynamic_data($applicant);
        $obj->setUserId($applicant_id);
        $obj->setUser_type(1);

        $obj->automatic_email_send_to_user();
    }
    //send the bulk selected template email for applicant update status
    function bulk_send_applicant_interview_status_email($data, $applicant_ids) {        
        if (!empty($data['email_status_update']) && !empty($applicant_ids) && !empty($data['selected_template'])) {
            //load all applicants data
            $data['interview_id'] = $data['interview_id']?? $data['id'];
            $applicants = $this->get_applicant_list_by_interview_id((object) $data, $applicant_ids);
            log_msg('Applicants for Group Booking bulk status update', 200, ['payload' => [$applicants, $data['selected_template']]], "", "", 'recruitment', 0, "");
            require_once APPPATH . 'Classes/Automatic_email.php';
            foreach($applicants['data'] as $applicant) {
                if (!empty($applicant->email) && $data['selected_template']) {
                    $applicant->job_title=$applicant->job_name;
                    
                    $obj = new Automatic_email();
                    $obj->setTemplateId($data['selected_template']);
                    $obj->setEmail($applicant->email);
                    $obj->setDynamic_data((array) $applicant);
                    $obj->setUserId($applicant->applicant_id);
                    $obj->setUser_type(1);
                    // send the email by template id
                    $obj->automatic_email_send_to_user_by_template_id();
                    log_msg('Email sent by bulk status update', 200, ['payload' => [$data['selected_template'], $applicant]], "", "", 'recruitment', 0, "");
                }
            }
        }
    }
    
    /**
     * Get Imail Interview template content as html
     */
    public function get_template_content_as_html($email_key) {
        $this->load->model('imail/Automatic_email_model');
        $template_data = $this->Automatic_email_model->get_template_content_details($email_key);
        $template=[];
        if (!empty($template_data)) {
            $content = $template_data['content'];
            $template_content = <<<HTML_CONTENT
            <html>
                <body>
                    <div>
                        $content
                    </div>
                </body>
            <html/>
            HTML_CONTENT;
            $status = true;
            
            $template['id'] = $template_data['id'];
            $template['template_content'] = $template_content;
            $template['name'] =  $template_data['name'];
            $template['from'] = $template_data['from'];
            $template['subject'] = $template_data['subject'];



        } else {
            $template_content = '';
            $status = false;
        }

        return ['status' => $status, 'data' => $template];
    }


    /**
     * Create update interview details
     */
   public function create_gb_ms_events_logs($post_data, $adminId) {

        $save_event_data = array(
            'interview_id'=> $post_data['interview_id'] ?? NULL,
            'event_id'=> $post_data['event_id'] ?? NULL,
            'subject' => $post_data['subject'] ?? NULL ,
            'onlineMeeting' => json_encode($post_data['onlineMeeting']) ?? NULL ,
            'onlineMeetingProvider' => $post_data['onlineMeetingProvider'] ?? NULL ,
            'onlineMeetingUrl' => $post_data['onlineMeetingUrl'] ?? NULL ,
            'organizer' => json_encode($post_data['organizer']) ?? NULL ,
            'responseRequested' => $post_data['responseRequested'] ,
            'responseStatus' => json_encode($post_data['responseStatus']) ?? NULL ,
            'attendees' => json_encode($post_data['attendees']) ?? NULL ,
            'createdDateTime' => $post_data['createdDateTime'] ?? NULL ,
            'start' => json_encode($post_data['start']) ?? NULL ,
            'end' => json_encode($post_data['end']) ?? NULL ,
            'originalEndTimeZone' => $post_data['originalEndTimeZone'] ?? NULL ,
            'originalStartTimeZone' => $post_data['originalStartTimeZone'] ?? NULL ,
            'odata_context' => $post_data['odata_context'] ?? NULL ,
            'odata_etag' => $post_data['odata_etag'] ?? NULL ,
            'allowNewTimeProposals' => $post_data['allowNewTimeProposals'] ?? NULL ,
            'hasAttachments' => $post_data['hasAttachments'] ?? NULL ,
            'hideAttendees' => $post_data['hideAttendees'] ?? NULL ,
            'iCalUId' => $post_data['iCalUId']  ?? NULL,
            'importance' => $post_data['importance'] ?? NULL ,
            'isAllDay' => $post_data['isAllDay'] ?? NULL ,
            'isCancelled' => $post_data['isCancelled'] ?? NULL ,
            'isDraft' => $post_data['isDraft'] ?? NULL ,
            'isOnlineMeeting' => $post_data['isOnlineMeeting'] ?? NULL ,
            'isOrganizer' => $post_data['isOrganizer'] ?? NULL ,
            'isReminderOn' => $post_data['isReminderOn'] ?? NULL ,
            'lastModifiedDateTime' => $post_data['lastModifiedDateTime'] ?? NULL ,
            'location' => json_encode($post_data['location']) ?? NULL ,
            'locations' => json_encode($post_data['locations']) ?? NULL ,
            'occurrenceId' => $post_data['occurrenceId'] ?? NULL ,
            'recurrence' => $post_data['recurrence'] ?? NULL ,
            'reminderMinutesBeforeStart' => $post_data['reminderMinutesBeforeStart'] ?? NULL ,
            'sensitivity' => $post_data['sensitivity'] ?? NULL ,
            'seriesMasterId' => $post_data['seriesMasterId'] ?? NULL ,
            'showAs' => $post_data['showAs'] ?? NULL ,
            'type' => $post_data['type'] ?? NULL ,
            'webLink' => $post_data['webLink'] ?? NULL ,
            'interview_type_id'=>$post_data['interview_type_id'] ?? NULL,
            'archive' => 0 ,
            'created_by' => $adminId ,
            'created_at' => DATE_TIME,
        );
         
        // Add applicants in GB
        $selected_applicants = $post_data["selected_applicants"];
        foreach($selected_applicants as $applicants) {
            $interview_applicant = $this->Recruitment_interview_model->create_update_applicant_interview((array)$applicants, $adminId);
            // update the invited on details for applicants            
            if(!empty($interview_applicant['interview_applicant_id'])){
                $this->basic_model->update_records("recruitment_interview_applicant", ["invited_on"=>DATE_TIME], ["id" => $interview_applicant['interview_applicant_id']]);
            }

            # Create communication log
            $obj_comm = new CommunicationLog();

            $obj_comm->setUser_type(1);
            $obj_comm->setUserId($applicants->applicant_id);
            $obj_comm->setFrom(APPLICATION_NAME);
            $obj_comm->setTitle($post_data['subject'] ?? NULL);
            $obj_comm->setCommunication_text($post_data['template_content'] ?? NULL);
            $obj_comm->setSend_by($adminId ?? 0);
            $obj_comm->setLog_type(2);

            $obj_comm->createCommunicationLog();
        }        
            // Create events logs
            $ms_event_log_id = $this->Basic_model->insert_records('ms_events_logs', $save_event_data);

            //Add Join URL in Group booking
         if(!empty($post_data['interview_id']) && !empty($post_data['onlineMeeting']) && !empty($post_data['onlineMeeting']->joinUrl)){
             $update_data = [
                "meeting_link"=>$post_data['onlineMeeting']->joinUrl,
                "meeting_created_via" => 1,
                "ms_event_log_id" => $ms_event_log_id
             ];
            $this->basic_model->update_records("recruitment_interview", $update_data, ["id" => $post_data['interview_id']]);
          }
        
       
            return ['status' => true, 'msg' => "Invite sent successfully!"];

    }
    
    // Resend invite mail to list of selected applicants
  public function resend_ms_invite_to_applicants($post_data, $adminId){
    $interview_id = $post_data['interview_id'];
    if(empty($interview_id)){
        return ['status' => false, 'error' => 'Interview id missing']; 
    }
    $owner = $post_data['owner'] ?? NULL;

    $update_event_data = array(
        'interview_id'=> $interview_id ?? NULL,
        'subject' => $post_data['subject'] ?? NULL ,
        'organizer' => !empty($post_data['organizer']) ? json_encode($post_data['organizer']) : NULL ,
        'responseStatus' => !empty($post_data['responseStatus']) ? json_encode($post_data['responseStatus']) : NULL , 
        'attendees' => !empty($post_data['attendees']) ? json_encode($post_data['attendees']) : NULL , 
        'createdDateTime' => $post_data['createdDateTime'] ?? NULL ,
        'start' => !empty($post_data['start']) ? json_encode($post_data['start']) : NULL , 
        'end' => !empty($post_data['end']) ? json_encode($post_data['end']) : NULL ,
        'originalEndTimeZone' => $post_data['originalEndTimeZone'] ?? NULL ,
        'originalStartTimeZone' => $post_data['originalStartTimeZone'] ?? NULL ,
        'odata_etag' => $post_data['odata_etag'] ?? NULL ,
        'importance' => $post_data['importance'] ?? NULL ,
        'isAllDay' => $post_data['isAllDay'] ?? NULL ,
        'isCancelled' => $post_data['isCancelled'] ?? NULL ,
        'isDraft' => $post_data['isDraft'] ?? NULL ,
        'isOrganizer' => $post_data['isOrganizer'] ?? NULL ,
        'lastModifiedDateTime' => $post_data['lastModifiedDateTime'] ?? NULL ,        
        'type' => $post_data['type'] ?? NULL ,
        'interview_type_id'=>$post_data['interview_type_id'] ?? NULL,
        'archive' => 0 ,
        'updated_by' => $adminId ,
        'updated_at' => DATE_TIME,
    );
    // update event log
     $ms_event_log_id = $this->Basic_model->update_records('ms_events_logs', $update_event_data, ["id" => $post_data['ms_event_log_id']]);

    $this->load->model('../../admin/models/Notification_model');

    # Create Communication log for edit interview
    if(!empty($post_data['selection']) && !empty($post_data['is_edit'])){
        $selection = $post_data['selection'];
        foreach($selection as $applicant) {
            $subject = $post_data['subject'] ?? NULL;

            # Create communication log
            $obj_comm = new CommunicationLog();

            $obj_comm->setUser_type(1);
            $obj_comm->setUserId($applicant->applicant_id);
            $obj_comm->setFrom(APPLICATION_NAME);
            $obj_comm->setTitle($subject);
            $obj_comm->setCommunication_text($post_data['template_content'] ?? NULL);
            $obj_comm->setSend_by($adminId ?? 0);
            $obj_comm->setLog_type(2);

            $obj_comm->createCommunicationLog();
        }
    }
    
    $selected_applicants = (array)$post_data['selected_applicants'];
    if(!empty($post_data['selected_applicants'])){
        foreach($selected_applicants as $applicant) {
            $notification_data['title'] = 'Group booking';
            $notification_data['shortdescription'] = 'Interview: Please check the invite sent to your mailbox';
            $notification_data['userId'] = $applicant->applicant_id;
            $notification_data['user_type'] = 5;
            $notification_data['status'] = 0;
            $notification_data['sender_type'] = 2;
            $notification_data['created'] = DATE_TIME;
            $notification_data['specific_admin_user'] = $owner->value ?? NULL;
            $notification_data['entity_type'] = 9;
            $notification_data['entity_id'] = $interview_id;
    
            $this->Notification_model->create_notification($notification_data);
    
            // update the invited details for applicants            
            if(!empty($applicant->interview_applicant_id)){
                $this->basic_model->update_records("recruitment_interview_applicant", ["invited_on"=>DATE_TIME], ["id" => $applicant->interview_applicant_id]);
            }else{
                $check_interview_applicant = $this->basic_model->get_row("recruitment_interview_applicant", ["id"], ["archive" => 0, "applicant_id" => $applicant->applicant_id, "application_id" => $applicant->application_id, "interview_id" => $interview_id]);
                if(!empty($check_interview_applicant)){
                    $this->basic_model->update_records("recruitment_interview_applicant", ["invited_on"=>DATE_TIME], ["applicant_id" => $applicant->applicant_id, "application_id" => $applicant->application_id, "interview_id" => $interview_id, "archive" => 0]);
                }else{
                    $save_applicant_data = array(
                        'applicant_id'=> $applicant->applicant_id,
                        'application_id'=> $applicant->application_id,
                        'job_id' => $applicant->job_id ,
                        'interview_id' => $interview_id ,
                        'created' => DATE_TIME ,
                        'created_by' =>$adminId,
                        "invited_on"=>DATE_TIME
                    );
    
                    $this->basic_model->insert_records("recruitment_interview_applicant", $save_applicant_data);
                }
                
            }

            $subject = $post_data['subject'] ?? NULL;

            # Create communication log
            $obj_comm = new CommunicationLog();

            $obj_comm->setUser_type(1);
            $obj_comm->setUserId($applicant->applicant_id);
            $obj_comm->setFrom(APPLICATION_NAME);
            $obj_comm->setTitle($subject);
            $obj_comm->setCommunication_text($post_data['template_content'] ?? NULL);
            $obj_comm->setSend_by($adminId ?? 0);
            $obj_comm->setLog_type(2);

            $obj_comm->createCommunicationLog();
        }
    }
    
    return ['status' => true, 'msg' => 'Invitation sent successfully'];
 }
// Check group booking exists
 public function check_any_changes_done_for_gb_update($post_data){
    $check_interview_applicant = $this->basic_model->get_row("recruitment_interview", ["id"], ["archive" => 0, "interview_end_datetime" => $post_data['interview_end_datetime'], "interview_start_datetime" => $post_data['interview_start_datetime'],
     "interview_type_id" => $post_data['interview_type_id'], "location_id" => $post_data['location_id']]);

        if(!empty($check_interview_applicant)){
            return ['status' => true];
        }else{
            return ['status' => false];
        }
    }

    /**
     * Get ms email template content as html
     */
    public function get_ms_url_template() {
        $ms_template = $this->basic_model->get_row("ms_url_template", ["id","template"], ["type" => 0]);
        if(!empty($ms_template)){
            return ['status' => true, 'data' => $ms_template];
        }else{
            return ['status' => false];
        }
    }

    /**
     * Get cancellation Imail template content as html
     */
    public function get_cancellation_template_as_html() {
        $this->load->model('imail/Automatic_email_model');
        $template = [];
        $status = false;
        $template_data = $this->Automatic_email_model->get_template_content_details("group_booking_invite_cancellation");
        $template=[];
        if (!empty($template_data)) {
            $content = $template_data['content'];
            $template_content = <<<HTML_CONTENT
            <html>
                <body>
                    <div>
                        $content
                    </div>
                </body>
            <html/>
            HTML_CONTENT;
            $status = true;
            
            $template['id'] = $template_data['id'];
            $template['template_content'] = $template_content;
            $template['name'] =  $template_data['name'];
            $template['from'] = $template_data['from'];
            $template['subject'] = $template_data['subject'];
        } 

        return ['status' => $status, 'data' => $template];
    }

 // Save the MS cancellation event to all attendees 
  public function update_cancellation_ms_invite_to_gb($post_data, $adminId){
        $interview_id = $post_data['interview_id'];
        if(empty($interview_id)){
            return ['status' => false, 'error' => 'Interview id missing']; 
        }

        $update_event_data = array(
            'event_status' => 1 ,     // cancel the MS event    
            'updated_by' => $adminId ,
            'updated_at' => DATE_TIME,
        );
        // update event log
        $ms_event_log_id = $this->Basic_model->update_records('ms_events_logs', $update_event_data, ["id" => $post_data['ms_event_log_id']]);
        
        # updating GB status
        $upd_data["interview_stage_status"] = $post_data['interview_stage_status'];
        $upd_data["meeting_link"] = 'Canceled';
        $upd_data["updated"] = DATE_TIME;
        $this->basic_model->update_records("recruitment_interview", $upd_data, ["id" => $interview_id]);

        // add canceled reason
        if ($post_data['interview_stage_status'] == "4" && !empty($post_data['cancel_reason_id'])) {
            $reson_data = [
                "interview_id" => $interview_id,
                "reason" =>  $post_data['cancel_reason_id']?? "",
                "reason_note" =>  $post_data['reason_note']?? "",
            ];

            $res = $this->basic_model->get_row("recruitment_interview_unsuccessful_reason", ["id", "reason", "reason_note"], ["archive" => 0, "interview_id" => $interview_id]);

            if (!empty($res)) {
                $this->basic_model->update_records("recruitment_interview_unsuccessful_reason", $reson_data, ["interview_id" => $interview_id]);
            }else{
                $this->basic_model->insert_records("recruitment_interview_unsuccessful_reason", $reson_data, false);
            }
    
        }

        $selected_applicants = (array)$post_data['selected_applicants'];
        if(!empty($post_data['selected_applicants'])){
            foreach($selected_applicants as $applicant) {
                // update the invited details for applicants            
                if(!empty($applicant->interview_applicant_id)){
                    // mark attendees event status as canceled                    
                    $this->basic_model->update_records("recruitment_interview_applicant", ["event_status"=>1, "archive" => 0], ["id" => $applicant->interview_applicant_id]);
                }          
                # Create communication log
                $obj_comm = new CommunicationLog();

                $obj_comm->setUser_type(1);
                $obj_comm->setUserId($applicant->applicant_id);
                $obj_comm->setFrom(APPLICATION_NAME);
                $obj_comm->setTitle($post_data['subject'] ?? NULL);
                $obj_comm->setCommunication_text($post_data['template_content'] ?? NULL);
                $obj_comm->setSend_by($adminId ?? 0);
                $obj_comm->setLog_type(2);

                $obj_comm->createCommunicationLog();
            }
        }

        return ['status' => true, 'msg' => 'Invitation Cancelled successfully'];
    }

 // store the cancellation event for particular applicant
  public function cancel_ms_event_for_particular_applicant($post_data, $adminId){
    $interview_id = $post_data['interview_id'];
    if(empty($interview_id)){
        return ['status' => false, 'error' => 'Interview id missing']; 
    }
    $update_event_data = array(
        'interview_id'=> $interview_id ?? NULL,
        'subject' => $post_data['subject'] ?? NULL ,
        'organizer' => !empty($post_data['organizer']) ? json_encode($post_data['organizer']) : NULL ,
        'responseStatus' => !empty($post_data['responseStatus']) ? json_encode($post_data['responseStatus']) : NULL , 
        'attendees' => !empty($post_data['attendees']) ? json_encode($post_data['attendees']) : NULL , 
        'createdDateTime' => $post_data['createdDateTime'] ?? NULL ,
        'start' => !empty($post_data['start']) ? json_encode($post_data['start']) : NULL , 
        'end' => !empty($post_data['end']) ? json_encode($post_data['end']) : NULL ,
        'originalEndTimeZone' => $post_data['originalEndTimeZone'] ?? NULL ,
        'originalStartTimeZone' => $post_data['originalStartTimeZone'] ?? NULL ,
        'odata_etag' => $post_data['odata_etag'] ?? NULL ,
        'importance' => $post_data['importance'] ?? NULL ,
        'isAllDay' => $post_data['isAllDay'] ?? NULL ,
        'isCancelled' => $post_data['isCancelled'] ?? NULL ,
        'isDraft' => $post_data['isDraft'] ?? NULL ,
        'isOrganizer' => $post_data['isOrganizer'] ?? NULL ,
        'lastModifiedDateTime' => $post_data['lastModifiedDateTime'] ?? NULL ,        
        'type' => $post_data['type'] ?? NULL ,
        'interview_type_id'=>$post_data['interview_type_id'] ?? NULL,
        'archive' => 0 ,
        'updated_by' => $adminId ,
        'updated_at' => DATE_TIME,
    );
    // update event log
     $ms_event_log_id = $this->Basic_model->update_records('ms_events_logs', $update_event_data, ["id" => $post_data['ms_event_log_id']]);

    $this->load->model('../../admin/models/Notification_model');    
    
    $selected_applicants = $post_data['selected_applicants'];
    if(!empty($post_data['selected_applicants'])){
            $notification_data['title'] = 'Group booking';
            $notification_data['shortdescription'] = 'Interview: Please check the invite sent to your mailbox';
            $notification_data['userId'] = $selected_applicants->applicant_id;
            $notification_data['user_type'] = 5;
            $notification_data['status'] = 0;
            $notification_data['sender_type'] = 2;
            $notification_data['created'] = DATE_TIME;
            $notification_data['specific_admin_user'] = $owner->value ?? NULL;
            $notification_data['entity_type'] = 9;
            $notification_data['entity_id'] = $interview_id;
    
            $this->Notification_model->create_notification($notification_data);
    
            // update the invited details for applicants            
            if(!empty($selected_applicants->interview_applicant_id)){
                $this->basic_model->update_records("recruitment_interview_applicant", ["event_status"=>1, "archive"=>$post_data['archive']], ["id" => $selected_applicants->interview_applicant_id]);
            }

             # Create communication log
             $obj_comm = new CommunicationLog();

             $obj_comm->setUser_type(1);
             $obj_comm->setUserId($selected_applicants->applicant_id);
             $obj_comm->setFrom(APPLICATION_NAME);
             $obj_comm->setTitle($post_data['subject'] ? 'Canceled :' .$post_data['subject'] : NULL);
             $obj_comm->setCommunication_text($post_data['template_content'] ?? NULL);
             $obj_comm->setSend_by($adminId ?? 0);
             $obj_comm->setLog_type(2);
 
             $obj_comm->createCommunicationLog();
    }
    
    return ['status' => true, 'msg' => 'Invitation sent successfully'];
 }
 // update the applicant invite response as accept/decline/tentative
 public function update_invite_response_for_all_applicant($post_data, $adminId){
        $interview_id = $post_data['interview_id'];
        if(empty($interview_id)){
            return ['status' => false, 'error' => 'Interview id missing']; 
        }
        $update_event_data = array(
            'interview_id'=> $interview_id ?? NULL,
            'responseStatus' => !empty($post_data['responseStatus']) ? json_encode($post_data['responseStatus']) : NULL , 
            'attendees' => !empty($post_data['attendees']) ? json_encode($post_data['attendees']) : NULL , 
            'updated_by' => $adminId ,
            'updated_at' => DATE_TIME,
        );
        // update event log
        $ms_event_log_id = $this->Basic_model->update_records('ms_events_logs', $update_event_data, ["id" => $post_data['ms_event_log_id']]);

        
        $selected_applicants = (array)$post_data['selected_applicants'];
        if(!empty($post_data['selected_applicants'])){
                foreach($selected_applicants as $applicant) {
                // update the invited details for applicants            
                if(!empty($applicant->interview_applicant_id)){
                    switch ($applicant->email_response) {
                        case 'accepted':
                            $attendee_response = 1;
                            break;
                        case 'tentativelyAccepted':
                            $attendee_response = 2;
                            break;
                        case 'declined':
                            $attendee_response = 3;
                            break;
                        case 'notResponded':
                            $attendee_response = 3;
                            break;
                        default:
                        $attendee_response = NULL;
                        break;
                    }
                   
                    $this->basic_model->update_records("recruitment_interview_applicant", ["attendee_response"=>$attendee_response, 'updated_by' => $adminId ,'updated' => DATE_TIME,], ["id" => $applicant->interview_applicant_id, "interview_id" => $interview_id]);
                }
            }    
        
        return ['status' => true];
       }  
    
    }
 // Store the ms event error log
    public function save_ms_error_log($post_data, $adminId){
        $test = (array)$post_data['err_data'];
        require_once APPPATH . 'Classes/ms_error_log/MsErrorLoges.php';
        $msError = new MsErrorClass\MsErrorLoges();
        $msError->setTitle($post_data['title']);
        $msError->setModuleId($post_data['module']=='Group Booking' ? GROUP_BOOKING : APPLICATION_LIST);
        $msError->setDescription(json_encode($test));
        $msError->setCreatedBy($adminId);
        $msError->createMsErrorLog();
        return ['status' => true];
    }
}
