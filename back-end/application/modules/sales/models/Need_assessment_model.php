<?php

defined('BASEPATH') OR exit('No direct script access allowed');

//class Master extends MX_Controller
class Need_assessment_model extends CI_Model {

    function __construct() {
      
        parent::__construct();
    }

    /*
     *its use for search owner staff in database table (admin user)
     *
     * @params
     * $ownerName search key parameter
     *
     *
     * return type array
     *
     */
    public function get_owner_staff_by_name($ownerName = '') {
        $this->db->like('label', $ownerName);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select(["CONCAT_WS(' ',m.firstname,m.lastname) as label", 'm.uuid as value']);
        $this->db->from(TBL_PREFIX . 'member as m');
        $this->db->join(TBL_PREFIX . "department as d", "d.id = m.department AND d.short_code = 'internal_staff'", "inner");
        $this->db->where(['m.archive' => 0]);
        $this->db->having($queryHaving);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /*
     * its use for get account person name on base of @param $ownerName
     *
     * @params
     * $ownerName search parameter
     *
     * return type array
     */
    public function get_account_person_name_search($ownerName = '') {
        $this->db->like('label', $ownerName);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select(["CONCAT_WS(' ',p.firstname,p.lastname) as label", 'p.id as value', "'1' as account_type"]);
        $this->db->from(TBL_PREFIX . 'person as p');
        $this->db->where(['p.archive' => 0]);
        $this->db->having($queryHaving);
        $sql[] = $this->db->get_compiled_select();

        $this->db->select(["o.name as label", 'o.id as value', "'2' as account_type"]);
        $this->db->from(TBL_PREFIX . 'organisation as o');
        $this->db->where(['o.archive' => 0]);
        $this->db->having($queryHaving);
        $sql[] = $this->db->get_compiled_select();

        $sql = implode(' union ', $sql);
        $query = $this->db->query($sql);

        return $result = $query->result();
    }

    /*
     * its use for search lead number on the base searching @param
     *
     * @params
     * $ownerName use for search in database key
     *
     * return type array
     */
    public function get_lead_number_search($ownerName = '') {
        $this->db->like('label', $ownerName);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select(["l.lead_number as label", 'l.id as value']);
        $this->db->from(TBL_PREFIX . 'leads as l');
        $this->db->where(['l.archive' => 0]);
        $this->db->having($queryHaving);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /*
     * its use for get need_assessment initial status id
     *
     * return type id (string)
     */
    function get_need_assessment_initial_status_id() {
        $this->db->select(["os.id"]);
        $this->db->from('tbl_need_assessment_status as os');
        $this->db->where(['os.archive' => 0]);
        $this->db->where(['os.key_name' => "draft"]);
        return $query = $this->db->get()->row("id");
    }

    /*
     * its use for create need assessment
     *
     * @params
     * $data its reqdata
     * $adminId created by
     *
     * return type need_assessmentId
     */
    function save_need_assessment($data, $adminId) {

        $insData = [
            'title' => $data["title"],
            'account_person' => (!empty($data['account_person'])) ? $data['account_person'] : null,
            'account_type' => (!empty($data['account_type'])) ? $data['account_type'] : null,
            'owner' => (!empty($data['owner'])) ? $data['owner'] : null,
            'status' => $this->get_need_assessment_initial_status_id(),
            'archive' => 0
        ];

        # update existing record if id is passed
        if(isset($data['need_assessment_id'])){
            $insData['updated_by'] = $adminId;
            $insData['updated'] = DATE_TIME;
            $this->basic_model->update_records('need_assessment', $insData,array('id'=>$data['need_assessment_id']));
            $need_assessmentId = $data['need_assessment_id'];
        }
        else {
            $insData['created_by'] = $adminId;
            $insData['created'] = DATE_TIME;
            $insData['updated'] = DATE_TIME;
            $need_assessmentId = $this->basic_model->insert_records('need_assessment', $insData);
        }
        return $need_assessmentId;
    }

    /*
     * its use for making sub query of owner name used in listing need assessment
     * return type sql
     */
    private function get_owner_name_sub_query($field="o.owner") {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = ".$field, null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * its use for making sub query created by (who creator of need assessment)
     * return type sql
     */
    private function get_need_assessment_created_by_sub_query() {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = o.created_by", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * its use for making sub query of account name
     * return type sql
     */
    private function get_account_name_sub_query() {
        $this->db->select("CONCAT_WS(' ', sub_p.firstname,sub_p.lastname)");
        $this->db->from(TBL_PREFIX . 'person as sub_p');
        $this->db->where("sub_p.id = o.account_person", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * its use for making sub query of need_assessment status
     * return type sql
     */
    private function get_need_assessment_status_sub_query() {
        $this->db->select("sub_os.name");
        $this->db->from(TBL_PREFIX . 'need_assessment_status as sub_os');
        $this->db->where("sub_os.id = o.status", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }


    /*
     * its use for get option of need_assessment status options
     * return type array object
     */
    function get_need_assessment_status_option() {
        $this->db->select(["id as value", "name as label"]);
        $this->db->from(TBL_PREFIX . 'need_assessment_status');
        $this->db->where("archive", 0);

        return $this->db->get()->result();
    }

    /*
     * its use for get need_assessment list
     *
     * @params
     * $reqData request data like special opration like filter,search, sort
     *
     * return type array
     *
     */
    function get_need_assessment_list($reqData, $filter_condition='', $contact_id = 0, $uuid_user_type='') {
        $ownder_name_sub_query = $this->get_owner_name_sub_query('o.owner');
        $account_name_sub_query = $this->get_account_name_sub_query();
        $need_assessment_status_sub_query = $this->get_need_assessment_status_sub_query();

        $this->load->model("Common/Common_model");
        $need_assessment_created_by_sub_query = $this->Common_model->get_created_by_updated_by_sub_query("o",$uuid_user_type,"created_by");         


        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = 'need_assessment_number';
        $direction = 'DESC';
        $src_columns = array('title','need_assessment_number', 
        "owner_name", "status","account","DATE_FORMAT(o.created,'%Y-%d-%M')",'created_by');
        

        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
        $available_column = ["title",'need_assessment_number', "id as need_assessment_id","status", 'created','created_by', 'owner']; 
       
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
                if ($orderBy == '32_format') {
                    $orderBy = "l.created";
                }
            }
        } else {
            $orderBy = 'o.id';
            $direction = 'DESC';
        }
        if (!empty($filter->filter_need_assessment_status)) {
            if ($filter->filter_need_assessment_status !== "all") {
                $this->db->where('o.status', $filter->filter_need_assessment_status);
            }
        }
        $select_column = ["title",'need_assessment_number',
        "id as need_assessment_id","status", 'DATE_FORMAT(created,"%Y-%m-%d") as created','o.created_by', 'o.owner']; 
       
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(" . $ownder_name_sub_query . ") as owner_name");
        $this->db->select("(CASE
            when o.account_type = 1 THEN (" . $account_name_sub_query . ")
            when o.account_type = 2 THEN (select sub_o.name from tbl_organisation as sub_o where sub_o.id = o.account_person)
            else ''
            end)
            as account");
        $this->db->select("(" . $need_assessment_status_sub_query . ") as status");
        $this->db->select("(" . $need_assessment_created_by_sub_query . ") as created_by");
        $this->db->from("tbl_need_assessment as o");
        $this->db->where('o.archive', 0);
        if (!empty($contact_id)) {
            $this->db->where('o.account_person', $contact_id);
        }
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($select_column); $i++) {
                $column_search = $select_column[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $result = $query->result();
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
    if($limit)
    {
    if ($dt_filtered_total % $limit == 0) {
        $dt_filtered_total = ($dt_filtered_total / $limit);
    } else {
        $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
    }
    }
    if($dt_filtered_total>0&&count($result)<1)
    {
        $result = $query->result();
    }
    
    return $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'total_item' => $total_item);
}

    /**
     * Find all possible need_assessment status types
     * @return array
     */
    protected function determine_need_assessment_status_options()
    {
        $q = $this->db
        ->from('tbl_need_assessment_status AS o')
        ->where([ 'o.archive' => 0 ])
        ->select([ 'o.id AS value', 'o.name AS label','o.id'])
        ->get();

        $results = $q->result_array();
        return $results;
    }


    public function get_need_assessment_detail($reqData)
    {
        if(!empty($reqData))
        {
            $id = $reqData->need_assessment_id;
            $tbl_o = TBL_PREFIX . 'need_assessment as o';

            $job_data = "SELECT
            o.id,
            o.title as page_title,
            o.owner as owner_id,
            o.status,
            o.account_person as person_id,
            o.account_type,
            o.id as need_assessment_id
            FROM `tbl_need_assessment` as o
            WHERE o.archive = '0'
            AND o.id = '".$id."' ";
            $job_data_ex = $this->db->query($job_data);

            $job_data_ary = $job_data_ex->row_array();

            if(empty($job_data_ary) || is_null($job_data_ary))
            {
                $return = array('status' => false);
                return $return;
            }

            $job_data_ary['owner'] = [];
            if($job_data_ary['owner_id'] && $job_data_ary['owner_id']!=null){
               $this->db->select(["CONCAT_WS(' ',m.firstname,m.lastname) as label", 'm.uuid as value']);
               $this->db->from(TBL_PREFIX . 'member as m');
               $this->db->join(TBL_PREFIX . "department as d", "d.id = m.department AND d.short_code = 'internal_staff'", "inner");
               $this->db->where(['m.archive' => 0,'m.uuid'=>$job_data_ary['owner_id']]);
               $query = $this->db->get();
               $owner_row = $query->num_rows() > 0 ? $query->row_array() : [];
               $job_data_ary['owner'] = $owner_row;
            }

            $job_data_ary['account_person'] = [];
            if($job_data_ary['person_id'] && $job_data_ary['person_id']!=null)
            {
                if($job_data_ary['account_type'] == 1){
                    $this->db->select(["CONCAT_WS(' ',p.firstname,p.lastname) as label", 'p.id as value', "'1' as account_type", "p.profile_pic as avatar"]);
                    $this->db->from(TBL_PREFIX . 'person as p');
                    $this->db->where(['p.archive' => 0,'p.id'=>$job_data_ary['person_id']]);
                    $query = $this->db->get();
                    $person_row = $query->num_rows() > 0 ? $query->row_array() : [];
                }else if($job_data_ary['account_type'] == 2){
                    $this->db->select(["org.name as label", 'org.id as value', "'2' as account_type"]);
                    $this->db->from(TBL_PREFIX . 'organisation as org');
                    $this->db->where(['org.archive' => 0,'org.id'=>$job_data_ary['person_id']]);
                    $query = $this->db->get();
                    $person_row = $query->num_rows() > 0 ? $query->row_array() : [];
                }else{
                    $person_row = [];
                }
                $job_data_ary['account_person'] = $person_row;
            }

            $return = array('status' => true, 'data' => $job_data_ary);
            $return['data']['need_assessment_status_options'] = $this->determine_need_assessment_status_options();
            return $return;
        }
    }

    /**
     * model function to add/update medication
     */
    public function save_medication($data,$adminId) {
        if(!empty($data)) {

            if(isset($data['not_applicable']) && $data['not_applicable'] == 1) {
                $medication_administration = 0;
                $medication_emergency = 0;
                $reduce_concern = 0;
                $medication_vitamins_counter = 0;
                $full_assistance_and_verbal = 0;
                $tablets_liquid_oral = 0;
                $crushed_oral = 0;
                $crushed_via_peg = 0;
            }
            else {
                $medication_administration = $data['medication_administration']??0;
                $medication_emergency = $data['medication_emergency']??0;
                $reduce_concern = $data['reduce_concern']??0;
                $medication_vitamins_counter = $data['medication_vitamins_counter']??0;
                $full_assistance_and_verbal = $data['full_assistance_and_verbal']??0;
                $tablets_liquid_oral = $data['tablets_liquid_oral']??0;
                $crushed_oral = $data['crushed_oral']??0;
                $crushed_via_peg = $data['crushed_via_peg']??0;
            }
            $insData = [
                'need_assessment_id'=>$data['need_assessment_id'],
                'not_applicable'=>$data['not_applicable']??2,
                'medication_administration'=>$medication_administration,
                'medication_emergency'=>$medication_emergency,
                'reduce_concern'=>$reduce_concern,
                'medication_vitamins_counter'=>$medication_vitamins_counter,
                'full_assistance_and_verbal'=>$full_assistance_and_verbal,
                'tablets_liquid_oral'=>$tablets_liquid_oral,
                'crushed_oral'=>$crushed_oral,
                'crushed_via_peg'=>$crushed_via_peg,
                'created_by'=>$adminId
            ];
            if(isset($data['id'])  && $data['id']){
                //update
                $medication_id = $data['need_assessment_id'];
                $this->basic_model->update_records('need_assessment_medication', $insData,array('need_assessment_id'=>$data['need_assessment_id'], 'archive'=>0));
           }else{
               //create
               $medication_id = $this->basic_model->insert_records('need_assessment_medication', $insData);
           }

           return $medication_id;

        }
    }

    /**
     * model function to add/update mealtime assistance
     */
    public function save_mealtime_assisstance($data,$adminId) {
        if(!empty($data)) {

            if(isset($data['not_applicable']) && $data['not_applicable'] == 1) {
                $risk_choking = 0;
                $risk_aspiration = 0;
                $mealtime_assistance_plan = 0;
            }
            else {
                $risk_choking = $data['risk_choking']??0;
                $risk_aspiration = $data['risk_aspiration']??0;
                $mealtime_assistance_plan = $data['mealtime_assistance_plan']??0;
            }
            $mealtime_assistance_plan_requirement = '';
            if (isset($data['assistance_plan_requirement']) == true && $mealtime_assistance_plan == 1) {
                $mealtime_assistance_plan_requirement = $data['assistance_plan_requirement'];
            }
            $insData = [
                'need_assessment_id'=>$data['need_assessment_id'],
                'not_applicable'=>$data['not_applicable']??2,
                'risk_choking'=>$risk_choking,
                'risk_aspiration'=>$risk_aspiration,
                'mealtime_assistance_plan'=>$mealtime_assistance_plan,
                'assistance_plan_requirement'=>$mealtime_assistance_plan_requirement,
                'require_assistance_plan'=>$data['require_assistance_plan'] ?? 0,
                'physical_assistance'=>$data['physical_assistance'] ?? 0,
                'physical_assistance_desc'=>$data['physical_assistance_desc'] ?? '',
                'verbal_prompting'=>$data['verbal_prompting'] ?? 0,
                'verbal_prompting_desc'=>$data['verbal_prompting_desc'] ?? '',
                'aids'=>$data['aids'] ?? 0,
                'aids_desc'=>$data['aids_desc'] ?? '',
                'created_by'=>$adminId
            ];


            if(isset($data['id'])  && $data['id']){
                //update
                $mealtime_assisstance_id = $data['need_assessment_id'];
                $this->basic_model->update_records('need_assessment_mealtime', $insData,array('need_assessment_id'=>$data['need_assessment_id'], 'archive'=>0));
           }else{
               //create
               $mealtime_assisstance_id = $this->basic_model->insert_records('need_assessment_mealtime', $insData);
           }


            // $mealtime_assisstance_id = $this->basic_model->insert_records('need_assessment_mealtime', $insData);
            return $mealtime_assisstance_id;
        }


        
    }

    /**
     * model function to add/update nutritional support
     */
    public function save_nutritional_support($data,$adminId) {
        if(!empty($data)) {
                $risk_choking = $data['risk_choking']??0;
                $risk_aspiration = $data['risk_aspiration']??0;
                $support_with_eating = $data['support_with_eating']??0;
                $peg_assistance_plan = $data['peg_assistance_plan']??0;
                $pej_assistance_plan = $data['pej_assistance_plan']??0;           
            
            $insData = [
                'need_assessment_id'=>$data['need_assessment_id'],
                'support_with_eating'=>$support_with_eating,
                'risk_aspiration'=>$risk_aspiration,
                'risk_choking'=>$risk_choking,
                'aspiration_food'=>$data['aspiration_food'],
                'aspiration_food_desc'=>$data['aspiration_food_desc'] ?? '',
                'aspiration_fluids'=>$data['aspiration_fluids'] ?? 0,
                'aspiration_fluids_desc'=>$data['aspiration_fluids_desc'] ?? '',
                'choking_food'=>$data['choking_food'] ?? 0,
                'choking_food_desc'=>$data['choking_food_desc'] ?? '',
                'choking_fluids'=>$data['choking_fluids'] ?? 0,
                'choking_fluids_desc'=>$data['choking_fluids_desc'] ?? '',
                'food_preferences_desc'=>$data['food_preferences_desc'] ?? '',
                'peg_assistance_plan'=>$peg_assistance_plan,
                'pej_assistance_plan'=>$pej_assistance_plan,                
                'created_by'=>$adminId
            ];           

            if(isset($data['id'])  && $data['id']){
                //update
                $nutritional_support_id = $data['id'];
                $this->basic_model->update_records('need_assessment_ns', $insData,array('need_assessment_id'=>$data['need_assessment_id'], 'archive'=>0));
           }else{
               //create
               $nutritional_support_id = $this->basic_model->insert_records('need_assessment_ns', $insData);
           }

            # remove existing entries of references for current member
        $this->basic_model->update_records('ns_food_preferences', array('archive' => '1', "updated" => DATE_TIME, "updated_by" => $adminId), array('na_nutritional_support_id' => $nutritional_support_id));
        // # adding food preferences
        if (!empty(json_decode($data['selected_food_preferences']))) {
            $languages = null;
            foreach ((json_decode($data['selected_food_preferences'])) as $refobj) {
                $singarr['na_nutritional_support_id'] = $nutritional_support_id;
                $singarr['food_preferences_ref_id'] = $refobj->id;
                $singarr['archive'] = 0;
                $singarr["created"] = DATE_TIME;
                $singarr["created_by"] = $adminId;
                $languages[] = $singarr;
            }
            $this->basic_model->insert_records("ns_food_preferences", $languages, $multiple = TRUE);
        }

            return $nutritional_support_id;
        }


        
    }

    /**
     * model function to add/update community access need assessment
     */
    public function save_community_access($data,$adminId)
    {
        if(!empty($data)) {
            $this->basic_model->update_records('need_assessment_community_access', array('archive'=>1),array('need_assessment_id'=>$data['need_assessment_id']));

            if(isset($data['not_applicable']) && $data['not_applicable'] == 1) {

                $grocessary_shopping = 0;
                $road_safety = 0;
                $companion_cart = 0;
                $method_transport = 0;
                $support_taxis = 0;
                $support_taxis_desc = '';
                $toileting = $organiz_admin = $bank_money =
                $community_access = $navigate_trans = 0;
            }
            else {
                $grocessary_shopping = $data['grocessary_shopping']??0;
                $road_safety = $data['road_safety']??0;
                $companion_cart = $data['companion_cart']??0;
                $method_transport = $data['method_transport']??0;
                $support_taxis = $data['support_taxis']??0;
                $support_taxis_desc = $data['support_taxis_desc']??'';
                $toileting = $data['toileting']??0;
                $organiz_admin = $data['organiz_admin']??0;
                $bank_money = $data['bank_money']??0;
                $community_access = $data['community_access']??0;
                $navigate_trans = $data['navigate_trans']??0;
            }

            $insData = [
                'need_assessment_id' => $data['need_assessment_id'],
                'not_applicable' => $data['not_applicable']??2,
                'grocessary_shopping' => $grocessary_shopping,
                'road_safety' => $road_safety,
                'companion_cart' => $companion_cart,
                'method_transport' => $method_transport,
                'support_taxis' => $support_taxis,
                'support_taxis_desc' => $support_taxis_desc,
                'toileting' => $toileting,
                'organiz_admin' => $organiz_admin,
                'bank_money' => $bank_money,
                'community_access' => $community_access,
                'navigate_trans' => $navigate_trans,
                'created_by' => $adminId
            ];
            return $this->basic_model->insert_records('need_assessment_community_access', $insData);

        }
    }

    /**
     * model function to add/update communication need assessment
     */
    public function save_communication($data,$adminId)
    {
        if(!empty($data)) {

            if(isset($data['not_applicable']) && $data['not_applicable'] == 1) {
                $communication_verbal = 0;
                $communication_book = 0;
                $communication_nonverbal = 0;
                $communication_electric = 0;
                $communication_vocalization = 0;
                $communication_sign = 0;
                $communication_other = 0;
                $companion_cart = 0;
                $interpreter = 0;
                $cognition = 0;
                $instructions = 0;
                $hearing_impared = 0;
                $visually_impared = 0;
                $communication_other_desc = '';
                $instructions_desc = '';
                $hearing_impared_desc = '';
                $visually_impared_desc = '';
                $yes_verbal_instruction = 0;
            }
            else {
                $communication_verbal = $data['communication_verbal']??0;
                $communication_book = $data['communication_book']??0;
                $communication_nonverbal = $data['communication_nonverbal']??0;
                $communication_electric = $data['communication_electric']??0;
                $communication_vocalization = $data['communication_vocalization']??0;
                $communication_sign = $data['communication_sign']??0;
                $communication_other = $data['communication_other']??0;
                $companion_cart = $data['companion_cart']??0;
                // $interpreter = $data['interpreter']??0;
                $interpreter = 0;
                $cognition = $data['cognition']??0;
                $instructions = $data['instructions']??0;
                $hearing_impared = $data['hearing_impared']??0;
                $visually_impared = $data['visually_impared']??0;
                $communication_other_desc = $data['communication_other_desc']??'';
                $instructions_desc = $data['instructions_desc'] ??'';
                $hearing_impared_desc = $data['hearing_impared_desc']??'';
                $visually_impared_desc = $data['visually_impared_desc']??'';
                $yes_verbal_instruction = $data['yes_verbal_instruction'] ?? 0;
            }

            $insData = [
                'need_assessment_id'=>$data['need_assessment_id'],
                'communication_verbal'=>$communication_verbal,
                'communication_book'=>$communication_book,
                'communication_nonverbal'=>$communication_nonverbal,
                'communication_electric'=>$communication_electric,
                'communication_vocalization'=>$communication_vocalization,
                'communication_sign'=>$communication_sign,
                'communication_other'=>$communication_other,
                'interpreter'=>$interpreter,
                'cognition'=>$cognition,
                'instructions'=>$instructions,
                'hearing_impared'=>$hearing_impared,
                'visually_impared'=>$visually_impared,
                'communication_other_desc'=>$communication_other_desc,
                'instructions_desc'=>$instructions_desc,
                'hearing_impared_desc'=>$hearing_impared_desc,
                'visually_impared_desc'=>$visually_impared_desc,
                'created_by'=>$adminId,
                'yes_verbal_instruction' => $yes_verbal_instruction
            ];

            if(isset($data['id'])  && $data['id']){
                 //update
                 $mealtime_assisstance_id = $data['need_assessment_id'];
                 $this->basic_model->update_records('need_assessment_communication', $insData,array('need_assessment_id'=>$data['need_assessment_id'], 'archive'=>0));
            }else{
                //create
                $mealtime_assisstance_id = $this->basic_model->insert_records('need_assessment_communication', $insData);
            }

            return $mealtime_assisstance_id;
        }
    }

    /**
     * model function to add/update personal care need assessment
     */
    public function save_personalcare($data,$adminId)
    {
        if(!empty($data)) {
            $this->basic_model->update_records('need_assessment_personalcare', array('archive'=>1),array('need_assessment_id'=>$data['need_assessment_id']));

            if(isset($data['not_applicable']) && $data['not_applicable'] == 1) {
                $bowelcare = 0;
                $bladdercare = 0;
                $showercare = 0;
                $dressing = 0;
                $teethcleaning = 0;
                $cooking = 0;
                $eating = 0;
                $drinking = 0;
                $lighthousework = 0;
            }
            else {
                $bowelcare = $data['bowelcare']??0;
                $bladdercare = $data['bladdercare']??0;
                $showercare = $data['showercare']??0;
                $dressing = $data['dressing']??0;
                $teethcleaning = $data['teethcleaning']??0;
                $cooking = $data['cooking']??0;
                $eating = $data['eating']??0;
                $drinking = $data['drinking']??0;
                $lighthousework = $data['lighthousework']??0;
            }

            $insData = [
                'need_assessment_id'=>$data['need_assessment_id'],
                'not_applicable'=>$data['not_applicable']??2,
                'bowelcare'=>$bowelcare,
                'bladdercare'=>$bladdercare,
                'showercare'=>$showercare,
                'dressing'=>$dressing,
                'teethcleaning'=>$teethcleaning,
                'cooking'=>$cooking,
                'eating'=>$eating,
                'drinking'=>$drinking,
                'lighthousework'=>$lighthousework,
                'created_by'=>$adminId
            ];
            $mealtime_assisstance_id = $this->basic_model->insert_records('need_assessment_personalcare', $insData);
            return $mealtime_assisstance_id;
        }
    }

    /**
     * model function to add/update mobility need assessment
     */
    public function save_mobility($data,$adminId)
    {
        if(!empty($data)) {
            $this->basic_model->update_records('need_assessment_mobility', array('archive'=>1),array('need_assessment_id'=>$data['need_assessment_id']));

            if(isset($data['not_applicable']) && $data['not_applicable'] == 1) {
                $can_mobilize = 0;
                $short_distances = 0;
                $long_distances = 0;
                $up_down_stairs = 0;
                $uneven_surfaces = 0;
                $inout_bed = 0;
                $inout_shower = 0;
                $onoff_toilet = 0;
                $inout_chair = 0;
                $inout_vehicle = 0;
                $inout_bed_equipment_used = '';
                $inout_shower_equipment_used = '';
                $onoff_toilet_equipment_used = '';
                $inout_chair_equipment_used = '';
                $inout_vehicle_equipment_used = '';
            }
            else {
                $can_mobilize = $data['can_mobilize'] ?? 0;
                $short_distances = $data['short_distances'] ?? 0;
                $long_distances = $data['long_distances'] ?? 0;
                $up_down_stairs = $data['up_down_stairs'] ?? 0;
                $uneven_surfaces = $data['uneven_surfaces'] ?? 0;

                $inout_bed = $data['inout_bed']??0;
                $inout_shower = $data['inout_shower']??0;
                $onoff_toilet = $data['onoff_toilet']??0;
                $inout_chair = $data['inout_chair']??0;
                $inout_vehicle = $data['inout_vehicle']??0;

                $inout_bed_equipment_used = $data['inout_bed_equipment_used'] ?? '';
                $inout_shower_equipment_used = $data['inout_shower_equipment_used'] ?? '';
                $onoff_toilet_equipment_used = $data['onoff_toilet_equipment_used'] ?? '';
                $inout_chair_equipment_used = $data['inout_chair_equipment_used'] ?? '';
                $inout_vehicle_equipment_used = $data['inout_vehicle_equipment_used'] ?? '';
            }

            $insData = [
                'need_assessment_id'=>$data['need_assessment_id'],
                'not_applicable'=>$data['not_applicable']??2,
                'inout_bed'=>$inout_bed,
                'inout_shower'=>$inout_shower,
                'onoff_toilet'=>$onoff_toilet,
                'inout_chair'=>$inout_chair,
                'inout_vehicle'=>$inout_vehicle,
                'created_by'=>$adminId,
                'can_mobilize' => $can_mobilize,
                'short_distances' => $short_distances,
                'long_distances' => $long_distances,
                'up_down_stairs' => $up_down_stairs,
                'uneven_surfaces' => $uneven_surfaces,
                'inout_bed_equipment_used' => $inout_bed_equipment_used,
                'inout_shower_equipment_used' => $inout_shower_equipment_used,
                'onoff_toilet_equipment_used' => $onoff_toilet_equipment_used,
                'inout_chair_equipment_used' => $inout_chair_equipment_used,
                'inout_vehicle_equipment_used' => $inout_vehicle_equipment_used,
            ];

            return $this->basic_model->insert_records('need_assessment_mobility', $insData);

        }
    }

    public function save_health_assisstance($data,$adminId)
    {
        if(!empty($data))
        {
            $this->basic_model->update_records('need_assessment_health', array('archive'=>1),array('need_assessment_id'=>$data['need_assessment_id']));

            if(isset($data['not_applicable']) && $data['not_applicable'] == 1){
                $diabetes = 0;
                $epilepsy = 0;
                $asthma = 0;
                $dietry_requirements = 0;
                $alergies = 0;
                $bladder_bowel_care = 0;
                $pressure_care = 0;
                $stoma = 0;
                $other = 0;
                $peg_pej = $anaphylaxis = $breath_assist = $mental_health = $nursing_service = 0;
                $nursing_service_reason = '';

            }else{
                $diabetes = $data['diabetes']??0;
                $epilepsy = $data['epilepsy']??0;
                $asthma = $data['asthma']??0;
                $dietry_requirements = $data['dietry_requirements']??0;
                $alergies = $data['alergies']??0;
                $bladder_bowel_care = $data['bladder_bowel_care']??0;
                $pressure_care = $data['pressure_care']??0;
                $stoma = $data['stoma']??0;
                $peg_pej = $data['peg_pej']??0;
                $anaphylaxis = $data['anaphylaxis']??0;
                $breath_assist = $data['breath_assist']??0;
                $mental_health = $data['mental_health']??0;
                $nursing_service = $data['nursing_service']??0;
                $nursing_service_reason = $data['nursing_service_reason']??'';
                $other = $data['other']??0;
            }
            $insData = [
                'need_assessment_id'=>$data['need_assessment_id'],
                'not_applicable'=>$data['not_applicable']??2,
                'diabetes'=>$diabetes,
                'epilepsy'=>$epilepsy,
                'asthma'=>$asthma,
                'dietry_requirements'=>$dietry_requirements,
                'alergies'=>$alergies,
                'bladder_bowel_care'=>$bladder_bowel_care,
                'pressure_care'=>$pressure_care,
                'stoma'=>$stoma,
                'other'=>$other,
                'other_label'=>($other == 0)?'': (($other > 0 && $data['other_label']!='')?$data['other_label']:'Other'),
                'peg_pej'=>$peg_pej,
                'anaphylaxis'=>$anaphylaxis,
                'breath_assist'=>$breath_assist,
                'mental_health'=>$mental_health,
                'nursing_service'=>$nursing_service,
                'nursing_service_reason'=>$nursing_service_reason,
                'created_by'=>$adminId
            ];
            $health_assisstance_id = $this->basic_model->insert_records('need_assessment_health', $insData);
            return $health_assisstance_id;
        }
    }

    public function save_equipment_assisstance($data,$adminId)
    {
        if(!empty($data))
        {
            $this->basic_model->update_records('need_assessment_equipment', array('archive'=>1),array('need_assessment_id'=>$data['need_assessment_id']));

            if(isset($data['not_applicable']) && $data['not_applicable'] == 1){
                $hoist_sling = $transfer_aides = $shower_chair = $wheel_chair = $walking_stick = 0;
                $other = $toilet_chair = $type = $walking_frame = $daily_safety_aids = 0;
                $daily_safety_aids_description = $hoist_sling_description = $other_description = $model_brand = $weight = '';
                $transfer_aides_description = '';
            }else{
                $walking_stick = $data['walking_stick']??0;
                $wheel_chair = $data['wheel_chair']??0;
                $shower_chair = $data['shower_chair']??0;
                $transfer_aides = $data['transfer_aides']??0;
                $daily_safety_aids = $data['daily_safety_aids']??0;
                $walking_frame = $data['walking_frame']??0;
                $type = $data['type']??0;
                $toilet_chair = $data['toilet_chair']??0;
                $hoist_sling = $data['hoist_sling']??0;
                $other = $data['other']??0;
                $weight = $data['weight']??'';
                $model_brand = $data['model_brand']??'';
                $daily_safety_aids_description = $data['daily_safety_aids_description']??'';
                $hoist_sling_description = $data['hoist_sling_description']??'';
                $other_description = $data['other_description']??'';
                $transfer_aides_description = $data['transfer_aides_description'] ?? '';
                $transfer_aides_description = $transfer_aides == '1' ? '' : $transfer_aides_description;
            }
            $insData = [
                'need_assessment_id'=>$data['need_assessment_id'],
                'not_applicable'=>$data['not_applicable']??2,
                'walking_stick'=>$walking_stick,
                'wheel_chair'=>$wheel_chair,
                'shower_chair'=>$shower_chair,
                'transfer_aides'=>$transfer_aides,
                'transfer_aides_description'=>$transfer_aides_description,
                'daily_safety_aids'=>$daily_safety_aids,
                'walking_frame'=>$walking_frame,
                'type'=>$type,
                'toilet_chair'=>$toilet_chair,
                'hoist_sling'=>$hoist_sling,
                'other'=>$other,
                'model_brand'=>$model_brand,
                'daily_safety_aids_description'=>$daily_safety_aids_description,
                'hoist_sling_description'=>$hoist_sling_description,
                'other_description'=>$other_description,
                'weight'=>$weight,
                'created_by'=>$adminId
            ];
            $health_assisstance_id = $this->basic_model->insert_records('need_assessment_equipment', $insData);
            return $health_assisstance_id;
        }
    }

    /*
     * its use for update service agreement status
     *
     * @params $reqData, $adminId
     * $reqData - reqdata of requested at front-end
     * $adminId action admin
     *
     * return type boolean
     */
    function update_status_need_assessment($reqData, $adminId){
        $reqData = (object) $reqData;

        $where = ["id" => $reqData->need_assessment_id];
        $data = ["status" => $reqData->status, "updated" => DATE_TIME];

        $this->basic_model->update_records("need_assessment", $data, $where);

        $response = ['status' => true, 'msg' => 'Status updated successfully.'];

        return $response;
    }

    function save_diagnosis_old($data, $adminId)
    {
        if(!empty($data))
        {
            $softDelete = $tempDelete = $insData = $updateData = $tempInsert = $tempUpdate = [];
            $this->basic_model->update_records('need_assessment_diagnosis', ['archive'=>1],array('need_assessment_id'=>$data['need_assessment_id']));

            foreach ($data as $item) {
                if(isset($item['selected']) && $item['selected'] == true)
                {
                        if(isset($item['plan_end_date']) && !empty($item['plan_end_date']))
                        {
                            $plan_end_date = DateFormate($item['plan_end_date'], 'Y-m-d');
                            $current_plan = 1;
                        }
                        else
                        {

                            $current_plan = (isset($item['current_plan']) && $item['current_plan'] == 1)?1:2;
                            $plan_end_date=NULL;
                        }

                        $tempInsert = [
                            'need_assessment_id' => $data['need_assessment_id']??0,
                            'concept_id' => $item['conceptId'] ??'',
                            'sno_med_id' => $item['id'] ??'',
                            'diagnosis' => $item['label']??'',
                            'search_term' => $item['search_term']??'',
                            'support_level' => $item['support_level'] ??0,
                            'current_plan' => $current_plan,
                            'plan_end_date' => $plan_end_date ,
                            'impact_on_participant' => $item['impact_on_participant'] ??0,
                            'created_by' => $adminId,
                            'updated_by' => $adminId,
                            'updated' => DATE_TIME,
							'primary_disability' => !empty($item['primary_disability'])? 1 : 0
                        ];
                        $insData[] = $tempInsert;

                   /* if(isset($item['incr_id_diagnosis']) && $item['incr_id_diagnosis'] > 0)
                    {
                        $tempUpdate = [
                            'support_level' => $item['support_level'] ??0,
                            'current_plan' => ($item['current_plan'] && $item['current_plan'] == 1)?1:2,
                            'plan_end_date' => DateFormate($item['plan_end_date'], 'Y-m-d')??'',
                            'impact_on_participant' => $item['impact_on_participant'] ??0,
                            'updated_by' => $adminId,
                            'id'=>$item['incr_id_diagnosis']
                        ];
                        $updateData[] = $tempUpdate;
                    }*/
                }
                else
                {
                   /* if(isset($item['incr_id_diagnosis']) && $item['incr_id_diagnosis'] > 0)
                    {
                        $tempDelete = [
                            'archive' => 1,
                            'id'=>$item['incr_id_diagnosis']
                        ];
                        $softDelete[] = $tempDelete;
                    }*/
                }
            }
            #pr([$insData,$updateData,$softDelete]);
            if(!empty($insData)){
                $this->basic_model->insert_records('need_assessment_diagnosis', $insData,true);
            }
            /*if(!empty($updateData)){
                $this->basic_model->insert_update_batch('update','need_assessment_diagnosis', $updateData,'id');
            }
            if(!empty($softDelete)){
                $this->basic_model->insert_update_batch('update','need_assessment_diagnosis', $softDelete,'id');
            }*/
        }
        return true;
    }

    function save_diagnosis($data, $adminId,$user_id)
    {
        if (!empty($data)) {
            $softDelete = $tempDelete = $insData = $updateData = $tempInsert = $tempUpdate = [];
            foreach ($data as $item) {
                if (isset($item['selected']) && $item['selected'] == true) {
                    if(isset($item['plan_end_date']) && !empty($item['plan_end_date']))
                        {
                            $plan_end_date = DateFormate($item['plan_end_date'], 'Y-m-d');
                            $current_plan = 1;
                        }else
                        {
                            $current_plan = (isset($item['current_plan']) && $item['current_plan'] == 1)?1:2;
                            $plan_end_date=NULL;
                        }
                    if (!array_key_exists('incr_id_diagnosis', $item) || $item['incr_id_diagnosis'] == 0) {
                        $tempInsert = [
                            'need_assessment_id' => $data['need_assessment_id']??0,
                            'concept_id' => $item['conceptId'] ??'',
                            'sno_med_id' => $item['id'] ??'',
                            'diagnosis' => $item['label']??'',
                            'search_term' => $item['search_term']??'',
                            'support_level' => $item['support_level'] ??0,
                            'current_plan' => $current_plan,
                            'plan_end_date' => $plan_end_date ,
                            'impact_on_participant' => $item['impact_on_participant'] ??0,
                            'created_by' => $adminId,
                            'updated_by' => $adminId,
                            'updated' => DATE_TIME,
                            'primary_disability' => !empty($item['primary_disability'])? 1 : 0
                        ];
                        $insData[] = $tempInsert;
                    }
                    if (array_key_exists('incr_id_diagnosis', $item) && $item['incr_id_diagnosis'] && $item['incr_id_diagnosis'] > 0) {
                        //check data exists
                        $where = array('id' => $item['incr_id_diagnosis'], 'diagnosis' => $item['label'], 'support_level' => $item['support_level'],
                        'current_plan' =>$current_plan, 'plan_end_date' => $plan_end_date,'impact_on_participant' => $item['impact_on_participant'], 'primary_disability' => $item['primary_disability']);

                        $check_for_update = $this->basic_model->get_record_where('need_assessment_diagnosis', ['id', 'updated_by'], $where);

                        $assessment_data = $this->basic_model->get_record_where('need_assessment_diagnosis', ['id', 'updated_by','updated'], ['id' => $item['incr_id_diagnosis']]);

                        $updated_id = $assessment_data && $assessment_data[0]  ? $assessment_data[0]->updated_by : 0;
                        $updated = $assessment_data && $assessment_data[0]  ? $assessment_data[0]->updated : '';
                        if(empty($check_for_update)){
                            if($assessment_data && $assessment_data[0]->updated_by != $user_id){
                                $updated_id = $user_id;
                                $updated = DATE_TIME;
                            }
                        }

                        $tempUpdate = [
                            'need_assessment_id' => $data['need_assessment_id']??0,
                            'concept_id' => $item['conceptId'] ??'',
                            'sno_med_id' => $item['id'] ??'',
                            'diagnosis' => $item['label']??'',
                            'search_term' => $item['search_term']??'',
                            'support_level' => $item['support_level'] ??0,
                            'current_plan' => $current_plan,
                            'plan_end_date' => $plan_end_date ,
                            'impact_on_participant' => $item['impact_on_participant'] ??0,
                            'updated_by' => $updated_id,
                            'updated' => $updated ? $updated : DATE_TIME,
                            'id' => $item['incr_id_diagnosis'],
                            'primary_disability' => !empty($item['primary_disability'])? 1 : 0

                        ];
                        $updateData[] = $tempUpdate;
                    }
                } else {
                    if (isset($item['incr_id_diagnosis']) && $item['incr_id_diagnosis'] > 0) {
                        $tempDelete = [
                            'archive' => 1,
                            'id' => $item['incr_id_diagnosis']
                        ];
                        $softDelete[] = $tempDelete;
                    }
                }
            }
            if (!empty($insData)) {
                $this->basic_model->insert_records('need_assessment_diagnosis', $insData, true);
            }
            if (!empty($updateData)) {
                $this->basic_model->insert_update_batch('update', 'need_assessment_diagnosis', $updateData, 'id');
            }
            if (!empty($softDelete)) {
                $this->basic_model->insert_update_batch('update', 'need_assessment_diagnosis', $softDelete, 'id');
            }
        }
        return true;
    }


    public function save_preference_assisstance($data,$adminId)
    {
        if(!empty($data))
        {
            $row = $this->basic_model->get_row('need_assessment_preferences', ["id"],['archive'=>0,'need_assessment_id'=>$data['need_assessment_id']]);
            $this->basic_model->update_records('need_assessment_preferences', array('archive'=>1),array('need_assessment_id'=>$data['need_assessment_id']));
            if(!empty($row)){
                $this->basic_model->update_records('need_assessment_preferences_detail', array('archive'=>1),array('preferences_id'=>$row->id));
            }
            //if not applicable is selected then avoid the selected value
            if (!empty($data['preferences_not_applicable'])) {
                $data['prefered_start_date'] = null;
                $data['support_worker_gender'] = 0;
                $data['known_unknown_worker'] = 0;
                $data['meet_greet_required'] = 0;
                $data['shadow_shift'] = 0;
                $data['worker_available'] = 0;
                $data['cancel_shift'] = 0;
                $data['hs_weekday'] = 0;
                $data['hs_saturday'] = 0;
                $data['hs_sunday'] = 0;
                $data['hs_sleep_over'] = 0;
                $data['hs_active_night'] = 0;
                $data['hs_public_holiday'] = 0;
                $data['as_saturday'] = 0;
                $data['as_sunday'] = 0;
                $data['as_sleep_over'] = 0;
                $data['as_active_night'] = 0;
                $data['as_public_holiday'] = 0;
                $data['in_home_shift_tasks'] = '';
                $data['community_access_shift_tasks'] = '';
                $data['active_night_details'] = '';
                $data['sleep_over_details'] = '';
                $data['as_weekday'] = 0;
            }
            $insData = [
                'need_assessment_id'=>$data['need_assessment_id'],
                'prefered_start_date'=>isset($data['prefered_start_date']) && $data['prefered_start_date']!='0000-00-00' ? DateFormate($data['prefered_start_date'], 'Y-m-d') : '',
                'support_worker_gender'=>$data['support_worker_gender']??0,
                'known_unknown_worker'=>$data['known_unknown_worker']??0,
                'meet_greet_required'=>$data['meet_greet_required']??0,
                'shadow_shift'=>$data['shadow_shift']??0,
                'worker_available'=>$data['worker_available']??0,
                'cancel_shift'=>$data['cancel_shift']??0,
                'hs_weekday'=>$data['hs_weekday']??0,
                'hs_saturday'=>$data['hs_saturday']??0,
                'hs_sunday'=>$data['hs_sunday']??0,
                'hs_sleep_over'=>$data['hs_sleep_over']??0,
                'hs_active_night'=>$data['hs_active_night']??0,
                'hs_public_holiday'=>$data['hs_public_holiday']??0,
                'as_saturday'=>$data['as_saturday']??0,
                'as_sunday'=>$data['as_sunday']??0,
                'as_sleep_over'=>$data['as_sleep_over']??0,
                'as_active_night'=>$data['as_active_night']??0,
                'as_public_holiday'=>$data['as_public_holiday']??0,
                'in_home_shift_tasks'=>$data['in_home_shift_tasks']??'',
                'community_access_shift_tasks'=>$data['community_access_shift_tasks']??'',
                'active_night_details'=>$data['active_night_details']??'',
                'sleep_over_details'=>$data['sleep_over_details']??'',
                'as_weekday'=>$data['as_weekday']??0,
                'created_by'=>$adminId,
                'not_applicable' => !empty($data['preferences_not_applicable'])? 1 : 0
            ];
            $preference_assisstance_id = $this->basic_model->insert_records('need_assessment_preferences', $insData);

            $likeSelection = $data['likeSelection'];
            if (!empty($data['preferences_not_applicable'])) {
                $likeSelection = [];
            }
            $like = $like_temp = array();
            if(!empty($likeSelection)){
                foreach ($likeSelection as $value) {
                    $like_temp['preferences_id'] = $preference_assisstance_id;
                    $like_temp['preferences_type'] = 1;
                    $like_temp['type_id'] = $value->id;
                    $like[] = $like_temp;
                }
                if(!empty($like)){
                    $this->basic_model->insert_records('need_assessment_preferences_detail', $like,true);
                }
            }

            $disLikesSelection = $data['disLikesSelection'];
            if (!empty($data['preferences_not_applicable'])) {
                $disLikesSelection = [];
            }
            $dislike = $dislike_temp = array();
            if(!empty($disLikesSelection)){
                foreach ($disLikesSelection as $value) {
                    $dislike_temp['preferences_id'] = $preference_assisstance_id;
                    $dislike_temp['preferences_type'] = 2;
                    $dislike_temp['type_id'] = $value->id;
                    $dislike[] = $dislike_temp;
                }
                if(!empty($dislike)){
                    $this->basic_model->insert_records('need_assessment_preferences_detail', $dislike,true);
                }
            }

            $langSelection = $data['langSelection'];
            if (!empty($data['preferences_not_applicable'])) {
                $langSelection = [];
            }
            $support_lang = $lang_temp = array();
            if(!empty($langSelection)){
                foreach ($langSelection as $value) {
                    $lang_temp['preferences_id'] = $preference_assisstance_id;
                    $lang_temp['preferences_type'] = 3;
                    $lang_temp['type_id'] = $value->id;
                    $support_lang[] = $lang_temp;
                }
                if(!empty($support_lang)){
                    $this->basic_model->insert_records('need_assessment_preferences_detail', $support_lang,true);
                }
            }

            $cultureSelection = $data['cultureSelection'];
            if (!empty($data['preferences_not_applicable'])) {
                $cultureSelection = [];
            }
            $culture = $culture_temp = array();
            if(!empty($cultureSelection)){
                foreach ($cultureSelection as $value) {
                    $culture_temp['preferences_id'] = $preference_assisstance_id;
                    $culture_temp['preferences_type'] = 4;
                    $culture_temp['type_id'] = $value->id;
                    $culture[] = $culture_temp;
                }
                if(!empty($culture)){
                    $this->basic_model->insert_records('need_assessment_preferences_detail', $culture,true);
                }
            }

            $intrestSelection = $data['intrestSelection'];
            if (!empty($data['preferences_not_applicable'])) {
                $intrestSelection = [];
            }
            $intrest = $intrest_temp = array();
            if(!empty($intrestSelection)){
                foreach ($intrestSelection as $value) {
                    $intrest_temp['preferences_id'] = $preference_assisstance_id;
                    $intrest_temp['preferences_type'] = 5;
                    $intrest_temp['type_id'] = $value->id;
                    $intrest[] = $intrest_temp;
                }
                if(!empty($intrest)){
                    $this->basic_model->insert_records('need_assessment_preferences_detail', $intrest,true);
                }
            }
            return $preference_assisstance_id;
        }
    }

    public function get_selected_preference_assistance($data)
    {
        if(!empty($data))
        {
            $row = $this->basic_model->get_row('need_assessment_preferences', ["id","prefered_start_date", 'support_worker_gender','known_unknown_worker','meet_greet_required','shadow_shift','worker_available','cancel_shift','hs_weekday','hs_saturday','hs_sunday','hs_sleep_over','hs_active_night','hs_public_holiday','as_weekday','as_saturday','as_sunday','as_sleep_over','as_active_night','as_public_holiday', 'in_home_shift_tasks', 'community_access_shift_tasks', 'active_night_details', 'sleep_over_details', 'not_applicable'],['archive'=>0,'need_assessment_id'=>$data['need_assessment_id']]);

            if(!empty($row)) {
                $preferences_id = $row->id;

                $this->db->select(['r.id','r.display_name as label','pd.preferences_type']);
                $this->db->from(TBL_PREFIX . 'references as r');
                $this->db->join(TBL_PREFIX . "need_assessment_preferences_detail as pd", "pd.type_id = r.id AND pd.archive = 0", "inner");
                $this->db->where(['pd.preferences_id'=>$preferences_id]);
                $query = $this->db->get();
                $rows = $query->num_rows() > 0 ? $query->result() : [];

                if(!empty($rows)){
                    $rows = obj_to_arr($rows);
                    $byGroup = group_by("preferences_type", $rows);
                    $row->selected_preference = $byGroup;
                }
            }
            return $row;
        }
    }

    /*
     * fetching the reference list of one reference type of pay rates
     */
    public function get_food_preferences_ref_list($keyname, $return_key = false) {
        if($return_key)
            $this->db->select(["r.key_name as label", 'r.id']);
        else
            $this->db->select(["r.display_name as label", 'r.id']);
        
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = '{$keyname}' AND rdt.archive = 0", "INNER");
        $this->db->where("r.archive", 0);
        $query = $this->db->get();
        if($query->num_rows() > 0 && $return_key == false)
            return $query->result_array();
        else if($query->num_rows() > 0 && $return_key == true) {
            $retrow = null;
            foreach($query->result_array() as $row) {
                $retrow[$row['label']] = $row['value'];
            }
            return $retrow;
        }
    }
}

