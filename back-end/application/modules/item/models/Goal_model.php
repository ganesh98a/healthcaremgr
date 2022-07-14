<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Class : Goal_model
 * Uses : for handle query operation of participant
 *
 */
class Goal_model extends CI_Model {

    function __construct() {

        parent::__construct();
    }

    /*
     * It is used to get the participant list
     * 
     * Operation: 
     *  - searching
     *  - filter
     *  - sorting
     * 
     * Return type Array
     */
    public function get_goals_list($reqData) {
        // Get subqueries
        $participant_name_sub_query = $this->get_name_sub_query('tg');

        $limit = sprintf("%d", $reqData->pageSize) ?? 0;
        $page = sprintf("%d", $reqData->page) ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $is_from_service_agreement=  $reqData->is_sa??false;
        $orderBy = '';
        $direction = '';

        // Searching column
        $src_columns = array('goal', 'participant');
        if(!empty($filter->search)) {
            $search_key  = $this->db->escape_str($filter->search, TRUE);
            if (!empty($search_key)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];
                    if (!empty($filter->filter_by) && $filter->filter_by != 'all' && $filter->filter_by != $column_search) {
                        continue;
                    }
                    if (strstr($column_search, "as") !== FALSE) {
                        $serch_column = explode(" as ", $column_search);
                        if (!empty($serch_column[0]))
                            $this->db->or_like($serch_column[0], $search_key);
                    } else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }
                $this->db->group_end();
            }
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        // Sort by id 
        $available_column = ["id", "participant_id", "goal", "participant_master_id", "start_date","service_type" ,"end_date"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'tg.id';
            $direction = 'DESC';
        }
        $select_column = ["tg.id", "tg.id as participant_id", "tg.goal", "tg.participant_master_id", "tg.start_date","tg.service_type" ,"tg.end_date", "'' as actions"];
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(" . $participant_name_sub_query . ") as participant");
        $this->db->from('tbl_goals_master as tg');
        $this->db->where('tg.archive', 0);
        if($is_from_service_agreement)
        {
            $this->db->where('tg.service_agreement_id', $reqData->id);
        }
        else{
            if(isset($reqData->id) && $reqData->id > 0)
            {
                $this->db->where('tg.participant_master_id', $reqData->id);
            }

            $this->db->where('tg.participant_master_id > 0');
        }
        
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
     
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // Get total rows inserted count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetch Goals list successfully');
        return $return;
    }

    /*
     * it is used for making sub query of contact name
     * return type sql
     */
    private function get_name_sub_query($tbl_alais) {
        $this->db->select("name");
        $this->db->from(TBL_PREFIX . 'participants_master as sub_p');
        $this->db->where("sub_p.id = ".$tbl_alais.".participant_master_id", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for get person name on base of @param $contactName
     * 
     * @params
     * $contactName search parameter
     * 
     * return type array
     * 
     */
    public function get_all_participant_name_search($contactName = null, $participant_id = null) {

        $queryHaving = null;
        if(!$participant_id) {
            $this->db->like('label', $contactName);
            $queryHavingData = $this->db->get_compiled_select();
            $queryHavingData = explode('WHERE', $queryHavingData);
            $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
        }

        $this->db->select(["p.name as label", 'p.id as value']);
        $this->db->from(TBL_PREFIX . 'participants_master as p');
        $this->db->where(['p.archive' => 0]);
        
        if($participant_id)
            $this->db->where(['p.id' => $participant_id]);
        else
            $this->db->having($queryHaving);

        $sql = $this->db->get_compiled_select();
        $query = $this->db->query($sql);

        return $result = $query->result();
    }

    /**
     * fetching a single goal details
     */
    public function get_goal_details($id) {
        $select_column = ["gm.id", "gm.participant_master_id", "gm.goal", "gm.objective","gm.service_type","gm.archive as is_goal_archived"];
        $this->db->select($select_column);
        $this->db->select("(CASE  
            WHEN gm.start_date = '0000-00-00 00:00:00' THEN ''
            Else DATE_FORMAT(gm.start_date, '%Y-%m-%d')  end
        ) as start_date");
        $this->db->select("(CASE  
            WHEN gm.end_date = '0000-00-00 00:00:00' THEN ''
            Else DATE_FORMAT(gm.end_date, '%Y-%m-%d')  end
        ) as end_date");
        $this->db->from('tbl_goals_master as gm');
        //$this->db->where('gm.archive', 0);
        $this->db->where('gm.id', $id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        // last_query();
        if (empty($result)) {
            $return = array('msg' => "Member not found!", 'status' => false);
            return $return;
        }

        if($result[0]->participant_master_id>0)
        {
            $part_details = $this->get_all_participant_name_search(null, $result[0]->participant_master_id);
            if($part_details)
            $result[0]->participant = $part_details[0];
        }

        $return = array('data' => $result[0], 'status' => true);
        return $return;
    }

    /* 
     * To create participant
     * 
     * @params {array} $data
     * @params {int} $adminId
     * 
     * return type participantId
     */
    function create_update_goal($data, $adminId) {
        $is_from_service_agreement=  $data['is_sa']??false;
        // Assign the data
        $goaldata = [
            'goal' => $data["goal"],
            'objective' => $data["objective"],
            'service_type'=>$data["service_type"],
            'archive' => '0',
            'start_date' => $data["start_date"],
            'end_date' => $data["end_date"],
        ];
        if($data['participant_id']>0|| $data["participant"])
        { 
            $goaldata['participant_master_id'] = $data["participant"]->value;
          
        }
        if($is_from_service_agreement)
        {
           
            if($data['service_agreement_id']>0)
            {
                $goaldata['service_agreement_id'] = $data["service_agreement_id"];
                $participant_id=$this->get_participant_id_by_sa_id($data["service_agreement_id"]);
                if($participant_id>0)
                { 
                    $goaldata['participant_master_id'] = $participant_id;

                }
            }
    
        }
        
        if(!empty($data['id'])){
            $goaldata['updated_by'] = $adminId;
            $goaldata['updated'] = DATE_TIME;
    
            $goalId = $this->basic_model->update_records('goals_master', $goaldata, $where = array('id' => $data['id']));
        }
        else {
            $goaldata['created_by'] = $adminId;
            $goaldata['created'] = DATE_TIME;

            // Insert the data using basic model function
            $goalId = $this->basic_model->insert_records('goals_master', $goaldata);
        }
        return $goalId;
    }


    
    /* 
     * To get participant service type
     * 
     * @params {array} $data
     * @params {int} $adminId
     * 
     * return type array
     */
    function get_participants_service_type($id){
        $this->load->model('sales/Lead_model');
        $this->db->select('opportunity_id');
        $this->db->from('tbl_service_agreement as sa');
        $this->db->where('sa.archive', 0);
        $this->db->where('sa.participant_id', $id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        if (empty($result)) {
            $rows = $this->Lead_model->get_lead_service_type_ref_list('lead_service_type');
            return $rows;
        }else{
            $op_id=  $result[0]->{'opportunity_id'};
           $this->db->select('topic');
           $this->db->from('tbl_opportunity as o');
           $this->db->where('o.archive', 0);
           $this->db->where('o.id', $op_id);
           $op_query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
           $op_result = $op_query->result();
          if(!empty($op_result)){
            $data=new stdClass();
            for($x=0;$x<count($op_result);$x++)
            {
                $data->label=$op_result[$x]->{'topic'};
                $data->value=$op_result[$x]->{'topic'};
            }
            return array('data' => $data, 'status' => true);
        }
        }
    }

     /* 
     * To update participant_id in  tbl_goals_master
     * 
     * @params {nt} $service agreement id
     * @params {int} $participant id
     * 
     * return type array
     */ 
    
    function update_participant_id_to_sa_goals($sa_id,$participant_id){
     if($sa_id){
         $where = ["service_agreement_id" =>$sa_id];
         $update = ["participant_master_id" => $participant_id,"updated" => DATE_TIME];
         $this->basic_model->update_records("goals_master", $update, $where);
         return ["status" => true, "msg" => "updated successfully"];   
     }

    }
    /* 
     * To update participant_id from tbl_service_agreement
     * 
     * @params {nt} $service agreement id
     * 
     * return type int
     */ 
    function get_participant_id_by_sa_id($sa_id){
        $this->db->select('participant_id');
        $this->db->from('tbl_service_agreement as s');
        $this->db->where('s.archive', 0);
        $this->db->where('s.id', $sa_id);
        $op_query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $op_result = $op_query->result();
        if(!empty($op_result)){
          return $op_result[0]->participant_id;
        }else{
            return 0;
        }
    }
   

     /* 
     * To get  shift tracked goals 
     * 
     * @params {array} 
     * 
     * return type array
     */  

    function get_tracked_goals_by_participant($reqData){
    $limit = sprintf("%d", $reqData->pageSize) ?? 0;
    $page = sprintf("%d", $reqData->page) ?? 0;
    $sorted = $reqData->sorted ?? [];
    $filter = $reqData->filtered ?? [];
    $is_restricted=$reqData->is_restricted??FALSE;
    $orderBy = '';
    $direction = '';
    # Searching column
    $src_columns =["g.goal",
    "mr.name as service_type",
    "gt.snapshot",
    "s.shift_no as shift_no",
    "CONCAT(m.firstname,' ',m.lastname)",
    "(CASE  
    WHEN gt.goal_type = 1 THEN 'Not Attempted:Not relevant to this shift'
    WHEN gt.goal_type = 2 THEN 'Not Attempted:Customers Choice'
    WHEN gt.goal_type = 3 THEN 'Verbal Prompt'
    WHEN gt.goal_type = 4 THEN 'Physical Assistance'
    WHEN gt.goal_type = 5 THEN 'Independent'
    Else '' end
    ) as action"];
    
    if (!empty($filter->search)) {
        $search_key  = $this->db->escape_str($filter->search, TRUE);
        if (!empty($search_key)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== FALSE) {
                    $serch_column = explode(" as ", $column_search);
                    if (!empty($serch_column[0]))
                        $this->db->or_like($serch_column[0], $search_key);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $search_key);
                }
            }
            $this->db->group_end();
        }
    }

    # sorting part
    $available_column = ['goal','participant_name','service_type','snapshot','shift_id','shift_no',
                         "date_submitted", "member_name","member_id","goal_id","outcome_type"];
    if (!empty($sorted)) {
        if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
            $orderBy = $sorted[0]->id;
            $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
        }
    } else {
        $orderBy = 'gt.id';
        $direction = 'DESC';
    }
    $select_column = ['g.goal','p.name as participant_name','mr.name as service_type','gt.snapshot','gt.shift_id','s.shift_no as shift_no',"DATE_FORMAT(gt.created,'%d/%m/%Y') as date_submitted", "CONCAT(m.firstname,' ',m.lastname) AS member_name","m.id as member_id","g.id as goal_id","gt.outcome_type"];
    $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
    $this->db->select("(CASE  
    WHEN gt.goal_type = 1 THEN 'Not Attempted:Not relevant to this shift'
    WHEN gt.goal_type = 2 THEN 'Not Attempted:Customers Choice'
    WHEN gt.goal_type = 3 THEN 'Verbal Prompt'
    WHEN gt.goal_type = 4 THEN 'Physical Assistance'
    WHEN gt.goal_type = 5 THEN 'Participant Proactivity'
    Else '' end
    ) as action");
    $this->db->select("(CASE WHEN gt.outcome_type = 1 THEN ('Achieved')
    WHEN gt.outcome_type = 2 THEN (' Partially Achieved')
     ELSE '' END)
    as outcometype");
    $this->db->from('tbl_shift_goal_tracking as gt');
    $this->db->join('tbl_shift as s', 's.id = gt.shift_id', 'inner');
    $this->db->join('tbl_goals_master as g', 'g.id = gt.goal_id', 'inner');
    $this->db->join('tbl_participants_master as p', 'p.id = s.account_id', 'inner');
    $this->db->join('tbl_member_role as mr', 'mr.id = p.role_id', 'left');
    $this->db->join('tbl_shift_member as sm', 'sm.id = s.accepted_shift_member_id', 'inner');
    $this->db->join('tbl_member as m', 'm.id = sm.member_id', 'inner');
    $this->db->where('s.account_id',$reqData->{'participant_id'});
    $this->db->order_by($orderBy, $direction);
    $this->db->limit($limit, ($page * $limit));
    $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
    $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
    // Get the query result
    $result = $query->result();
    return $result ;
}

    /**
     * fetching all goals by participant id
     */
    public function get_all_goals_and_shift_by_participant_id($data) {
        if (empty($data->account_id) && empty($data->goal_id)){
            return;
        } 

        $start_date = '';
        $end_date = '';
        if(!empty($data->selected_date_type) && !empty($data->start_end_date)){
            $start_end_date = explode("," , $this->db->escape_str($data->start_end_date, TRUE));
            if(isset($start_end_date[0]))  $start_date = $start_end_date[0];
            if(isset($start_end_date[1])) $end_date = $start_end_date[1];
        }
        $this->db->select(["s.id as shift_id","s.scheduled_start_datetime","s.account_id", "gm.participant_master_id", "gm.goal","gm.service_type", " 'false'  as expand_option", 'sgt.snapshot','sgt.goal_type','sgt.outcome_type']);
      
        $this->db->select("(CASE  
        WHEN sgt.goal_type = 1 THEN 'Not Attempted:Not relevant to this shift'
        WHEN sgt.goal_type = 2 THEN 'Not Attempted:Customers Choice'
        WHEN sgt.goal_type = 3 THEN 'Verbal Prompt'
        WHEN sgt.goal_type = 4 THEN 'Physical Assistance'
        WHEN sgt.goal_type = 5 THEN 'Participant Proactivity'
        Else '' end
        ) as goal_action");

        $this->db->select("(CASE WHEN sgt.outcome_type = 1 THEN ('Achieved')
        WHEN sgt.outcome_type = 2 THEN (' Partially Achieved')
         ELSE '' END)
        as outcometype");
        $this->db->from('tbl_shift_goal_tracking as sgt');
        $this->db->join('tbl_shift as s', 's.id = sgt.shift_id', 'inner');
        $this->db->join('tbl_goals_master as gm', 'gm.id = sgt.goal_id', 'inner');
        $this->db->join('tbl_participants_master as p', 'p.id = s.account_id', 'inner');


        $this->db->where(["s.account_id" => $this->db->escape_str($data->account_id), 
                          "s.account_type" => $this->db->escape_str($data->account_type), "s.status"=>5, "gm.archive"=>0]);
        if($data->goal_id!='all'){
            $this->db->where("gm.id" , $data->goal_id);   
        }
        if(!empty($start_date) && !empty($end_date)){
            $this->db->where( "DATE(s.scheduled_start_datetime) BETWEEN '".$start_date."' AND '".$end_date."' ");
        }

        $this->db->where("s.account_type", "1");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        if (empty($query->result())) {
            $return = array('msg' => "Shift not found!", 'status' => false);
            return $return;
        }
       
        $result = [];
        $item_ary = $query->result();
        foreach ($item_ary as $val) {       
            $expand = false;
            $val->expand_option = $expand;
            $result[] = $val;
        }   

        $shift_details = [];
        $shift_category_list = [];       

        if ($query->num_rows() > 0) {
            foreach ($item_ary as $key => $value) {
                
                $shift_details[$value->shift_id]['shift_data'][] = $value;
                $shift_details[$value->shift_id]['scheduled_start_datetime'] = $value->scheduled_start_datetime;               
            }
            $shift_category_list['list'] = $shift_details;
        }     
       
        return array('data' => $shift_category_list, 'status' => true);
    }
    /*
     * It is used to get goals list by participant id
     * 
     * Operation: 
     *  - searching
     *  - filter
     *  - sorting
     * 
     * Return type Array
     */
    public function get_all_goals_list_by_participant($reqData) { 

        if(empty($reqData->participant_id)){
            return;
        }
       
        $select_column = ["tg.id as value", "tg.goal as label"];
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_goals_master as tg');
        $this->db->where(['tg.archive' => 0, 'tg.participant_master_id' => $reqData->participant_id]);
        $this->db->order_by('tg.id', 'DESC');
       
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());      

        // Get the query result
        $goal_result = $query->result_array();
        return array('data' => $goal_result, 'status' => true, 'msg' => 'Fetch Goals list successfully');        
    }
}

    
