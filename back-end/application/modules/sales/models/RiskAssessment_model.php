<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Class : RiskAssessment_model
 * Uses : for handle query operation of risk assessment 
 *
 */
class RiskAssessment_model extends CI_Model {

    const MULTIPLIER_TYPE_PROBABILITY = 'probability';
    const MULTIPLIER_TYPE_IMPACT = 'impact';

    function __construct() {

        parent::__construct();
    }

    /*
     * It is used to get the risk assessment list
     * 
     * Operation: 
     *  - searching
     *  - filter
     *  - sorting
     * 
     * Return type Array
     */
    public function get_risk_assessment_list($reqData, $filter_condition = '', $contact_id = 0, $uuid_user_type) {
        // Get subqueries

        $this->load->model("Common/Common_model");
        $createdByNameSubQuery = $this->Common_model->get_created_by_updated_by_sub_query("ra",$uuid_user_type,"created_by");     
        $ownerNameSubQuery = $this->get_owner_name_sub_query();
        $accountPersonSubQuery = $this->account_person_sub_query();
        $accountOrgSubQuery = $this->account_org_sub_query();

        $limit = $reqData->pageSize ?? 99999;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        // Searching column
        $src_columns = array('reference_id', 'status', 'created_by', 'topic', 'owner', 'account');
        if (isset($filter->search) && $filter->search != '') {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (!empty($filter->filter_by) && $filter->filter_by != 'all' && $filter->filter_by != $column_search) {
                    continue;
                }
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                } else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        // Sort by id 
        $available_column = ["risk_assessment_id", "reference_id", "topic", "created_date", "created_by", "status", "is_deleted"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'ra.id';
            $direction = 'DESC';
        }

        // Filter by status
        if (!empty($filter->filter_status)) {
            if ($filter->filter_status === "draft") {
                $this->db->where('ra.status', 1);
            } elseif ($filter->filter_status === "inactive") {
                $this->db->where('ra.status', 3);
            } elseif ($filter->filter_status === "final") {
                $this->db->where('ra.status', 2);
            }
        }

        $select_column = ["ra.id as risk_assessment_id", "ra.reference_id", "ra.topic", "ra.created_date", "ra.created_by", "ra.status", "ra.is_deleted"];
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(" . $createdByNameSubQuery . ") as created_by");
        $this->db->select("(" . $ownerNameSubQuery . ") as owner");
        $this->db->select("(CASE
        when ra.account_type = 1 THEN (" . $accountPersonSubQuery . ")  
        when ra.account_type = 2 THEN (" . $accountOrgSubQuery . ") 
        else '' 
        end
        ) as account");
        $this->db->select("(CASE  
            WHEN ra.status = 1 THEN 'Draft'
            WHEN ra.status = 2 THEN 'Final'
			WHEN ra.status = 3 THEN 'InActive'
			Else '' end
		) as status");
        $this->db->from('tbl_crm_risk_assessment as ra');
        $this->db->where('ra.is_deleted', 0);
        if (!empty($contact_id)) {
            $this->db->where('ra.account_id', $contact_id);
        }
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // Get total rows inserted count
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // If limit 0 return empty 
        if ($limit == 0) {
            $return = array('count' => $dt_filtered_total, 'data' => array(), 'status' => false, 'error' => 'Pagination divide by zero');
            return $return;
        }

        // Get the count per page and total page
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        // Get the query result
        $result = $query->result();
        
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetch risk assessment list successfully', 'total_item' => $total_item);
        return $return;
    }

   /*
     * It is used for generating sub-query for created by contact
     * return type sql
     */

    public function get_ra_created_by_sub_query() {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = ra.created_by", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * It is used for generating sub-query for owner name
     * return type sql
     */
    private function get_owner_name_sub_query() {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = ra.owner_id", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * It is used for generating sub-query for person name
     * return type sql
     */
    private function account_person_sub_query() {
        $this->db->select("CONCAT_WS(' ', sub_p.firstname,sub_p.lastname)");
        $this->db->from(TBL_PREFIX . 'person as sub_p');
        $this->db->where("sub_p.id = ra.account_id", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * It is used for generating sub-query for organization name
     * return type sql
     */
    private function account_org_sub_query() {
        $this->db->select("sub_o.name");
        $this->db->from(TBL_PREFIX . 'organisation as sub_o');
        $this->db->where("sub_o.id = ra.account_id", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * To get refrence id of risk assessment
     * 
     * return type array
     */
    function get_reference_id() {
        $column = ["ra.reference_id", 'ra.status'];
        $orderBy = "ra.id";
        $orderDirection = "DESC";
        $this->db->select($column);
        $this->db->from(TBL_PREFIX . 'crm_risk_assessment as ra');
        $this->db->order_by($orderBy, $orderDirection);
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /* 
     * To create risk_assessment
     * 
     * @params {array} $data
     * @params {int} $adminId
     * 
     * return type riskAssessmentId
     */
    function create_risk_assessment($data, $adminId) {
        // Assign the data
        $insData = [
            'topic' => $data["topic"],
            'account_type' => $data["account_type"],
            'account_id' => $data["account_id"],
            'owner_id' => $data["owner_id"],
            'status' => $data["status"],
            'created_by' => $adminId,
            'created_date' => DATE_TIME
        ];
        // Insert the data using basic model function
        $riskAssessmentId = $this->basic_model->insert_records('crm_risk_assessment', $insData);

        return $riskAssessmentId;
    }

    /*
     * To fetch the risk assessment data 
     * 
     * Return type Array
     */
    public function get_risk_assessment_by_id($reqData) {
        // Check the risk assessment data
        if ($reqData && $reqData->risk_assessment_id) {
            $risk_assessment_id = $reqData->risk_assessment_id;
            $column = ["ra.id as risk_assessment_id", "ra.reference_id", "ra.topic", "ra.account_type", "ra.account_id", "ra.owner_id", "ra.created_date", "ra.created_by", "ra.status"];
            $orderBy = "ra.id";
            $orderDirection = "DESC";
            $this->db->select($column);
            $this->db->from(TBL_PREFIX . 'crm_risk_assessment as ra');
            $this->db->order_by($orderBy, $orderDirection);
            $this->db->where(['ra.id' => $risk_assessment_id]);
            $this->db->limit(1);            
            $query = $this->db->get();
            $result = $query->num_rows() > 0 ? $query->result_array() : [];
            $result = $query->num_rows() > 0 ? $result[0] : [];
            // Get owner name as label and id as value
            if($result['owner_id'] && $result['owner_id']!=null){
                $this->db->select(["CONCAT_WS(' ',m.firstname,m.lastname) as label", 'm.id as value']);
                $this->db->from(TBL_PREFIX . 'member as m');
                $this->db->join(TBL_PREFIX . "department as d", "d.id = m.department AND d.short_code = 'internal_staff'", "inner");
                $this->db->where(['m.archive' => 0,'m.id'=> $result['owner_id']]);
                $query = $this->db->get();
                $owner_row = $query->num_rows() > 0 ? $query->row_array() : [];
                $result['owner'] = $owner_row;
            }

            // Get person/org name as label and id as value
            if($result['account_id'] && $result['account_id']!=null){
                if($result['account_type'] == 1){
                    $this->db->select(["CONCAT_WS(' ',p.firstname,p.lastname) as label", 'p.id as value', "'1' as account_type"]);
                    $this->db->from(TBL_PREFIX . 'person as p');
                    $this->db->where(['p.archive' => 0,'p.id'=> $result['account_id']]);
                }
    
                if($result['account_type'] == 2){
                    $this->db->select(["org.name as label", 'org.id as value', "'2' as account_type"]);
                    $this->db->from(TBL_PREFIX . 'organisation as org');
                    $this->db->where(['org.archive' => 0,'org.id'=> $result['account_id']]);
                }

                $query = $this->db->get();
                $person_row = $query->num_rows() > 0 ? $query->row_array() : [];
                $result['account_person'] = $person_row;
            }

            return [ "status" => true, 'data' => $result, 'msg' => 'Fetch Risk Assessment data successfully' ];
        } else {
            return [ "status" => false, 'error' => 'Risk Assessment Id is null'];
        }
    }

    /* 
     * For edit risk_assessment
     * 
     * @params {array} $data
     * @params {int} $adminId
     * 
     * return type riskAssessmentId
     */
    function update_risk_assessment($data, $adminId) {
        // Check the risk assessment data
        if ($data && $data['risk_assessment_id']) {
            // Assign the data
            $updateData = [
                'topic' => $data["topic"],
                'account_type' => $data["account_type"],
                'account_id' => $data["account_id"],
                'owner_id' => $data["owner_id"],
                'status' => $data["status"],
                'updated_by' => $adminId,
                'updated_date' => DATE_TIME,
            ];
            // Update the data
            $this->db->where($where = array('id' => $data['risk_assessment_id']));
            $riskAssessmentId = $this->db->update(TBL_PREFIX . 'crm_risk_assessment', $updateData);
            return $riskAssessmentId;
        } else {
            return '';
        }
    }

    /**
     * Find all matrix evaluations by risk assessment id
     * @param int $risk_assessment_id 
     * @return array 
     */
    public function find_all_matrices_by_assessment_id($risk_assessment_id)
    {
        $query = $this->db->get_where('tbl_crm_risk_assessment_risk_matrix', [
            'archive' => 0,
            'risk_assessment_id' => $risk_assessment_id,
        ]);

        $results = $query->result_array();
        return $results;
    }
    public function find_all_behvsupp_matrices_by_assessment_id($risk_assessment_id)
    {
        $query = $this->db->get_where('tbl_ra_behavioursupport_matrix', [
            'risk_assessment_id' => $risk_assessment_id,
        ]);

        $results = $query->result_array();
        return $results;
    }


    /**
     * Find all probabilities options
     * 
     * @return array 
     */
    public function find_all_risk_probabilities() 
    {
        return $this->find_all_risk_multipliers(self::MULTIPLIER_TYPE_PROBABILITY);
    }


    /**
     * Find all impact options
     * 
     * @return array 
     */
    public function find_all_risk_impacts() 
    {
        return $this->find_all_risk_multipliers(self::MULTIPLIER_TYPE_IMPACT);
    }

    /**
     * Find all multipliers (either probability or impact) 
     * that can be use to calculate risk matrix score
     * 
     * @return array 
     */
    public function find_all_risk_multipliers($type = null) 
    {
        $where = [];
        if ($type) {
            $where['type'] = $type;
        }

        return $this->db->get_where('tbl_crm_risk_assessment_risk_multiplier', $where)->result_array();
    }
    
    /**
     * Save all risk matrices
     * 
     * @param int $risk_assessment_id 
     * @param array[] $matrices
     * @param int $adminId Usually the `$reqData->adminId`
     * @return array
     */
    public function save_all_risk_matrices($risk_assessment_id, array $matrices = [], $adminId = null)
    {
        // we trust that you did the server-side VALIDATION before calling this method!

        $existingRiskAssessment = $this->db->get_where('tbl_crm_risk_assessment', ['id' => $risk_assessment_id])->row_array();
        $existingMatrices = $this->db->get_where('tbl_crm_risk_assessment_risk_matrix', ['risk_assessment_id' => $risk_assessment_id, 'archive' => 0])->result_array();
        if (is_null($existingMatrices)) {
            $existingMatrices = [];
        }

        $existingMatricesIndexed = array_column($existingMatrices, null, 'id');

        $savedIds = [];

        foreach ($matrices as $i => $matrix) {
            $id = $matrix['id'] ?? null;
            if ($id && array_key_exists($id, $existingMatricesIndexed)) {
                // update and unset
                $existingMatrix = $existingMatricesIndexed[$id];
                $existingMatrix = [
                    'risk' => $matrix['risk'],
                    'impact_id' => $matrix['impact_id'],
                    'probability_id' => $matrix['probability_id'],
                    'updated_by' => $adminId,
                ];
                $this->db->update('tbl_crm_risk_assessment_risk_matrix', $existingMatrix, ['id' => $id]);
                $savedIds[] = $id;

                unset($existingMatricesIndexed[$id]); // important, because we will be archiving items after this foreach loop finishes
            } else {
                $this->db->insert('tbl_crm_risk_assessment_risk_matrix', [
                    'risk_assessment_id' => $risk_assessment_id,
                    'risk' => $matrix['risk'],
                    'impact_id' => $matrix['impact_id'],
                    'probability_id' => $matrix['probability_id'],
                    'created_by' => $adminId,
                ]);

                $id = $this->db->insert_id();
                $savedIds[] = $id;
            }

        }

        // archive if anything left
        if (!empty($existingMatricesIndexed)) {
            $idsToBeArchived = array_keys($existingMatricesIndexed);
            $this->db->where_in('id', $idsToBeArchived)->update('tbl_crm_risk_assessment_risk_matrix', ['archive' => 1]);
        }

        return [
            'status' => true,
            'saved_ids' => $savedIds,
            'code' => $existingRiskAssessment['reference_id'] ?? null,
        ];
    }
    public function save_all_behavsupport_matrices($risk_assessment_id, array $matrices = [], $adminId = null)
    {

        // $existingRiskAssessment = $this->db->get_where('tbl_crm_risk_assessment', ['id' => $risk_assessment_id])->row_array();
        $existingMatrices = $this->db->get_where('tbl_ra_behavioursupport_matrix', ['risk_assessment_id' => $risk_assessment_id])->result_array();
        if (is_null($existingMatrices)) {
            $existingMatrices = [];
        }
        $existingMatricesIndexed = array_column($existingMatrices, null, 'id');
        $savedIds = [];
        foreach ($matrices as $i => $matrix) {
            $id = $matrix['id'] ?? null;
            if ($id && array_key_exists($id, $existingMatricesIndexed)) {
                // update and unset
                $existingMatrix = $existingMatricesIndexed[$id];
                $existingMatrix = [
                    'risk_assessment_id' => $risk_assessment_id,
                    'behaviuor' => $matrix['risk'],
                    'likelyhood_id' => $matrix['likelyhood_id'],
                    'trigger' => $matrix['trigger'],
                    'prevention_strategy' => $matrix['prevention_strategy'],
                    'descalation_strategy' => $matrix['descalation_strategy'],
                    'updated_by' => $adminId,
                ];
                $this->db->update('tbl_ra_behavioursupport_matrix', $existingMatrix, ['id' => $id]);
                $savedIds[] = $id;
                unset($existingMatricesIndexed[$id]); // important, because we will be archiving items after this foreach loop finishes
            } else {
                $this->db->insert('tbl_ra_behavioursupport_matrix', [
                    'risk_assessment_id' => $risk_assessment_id,
                    'behaviuor' => $matrix['risk'],
                    'likelyhood_id' => $matrix['likelyhood_id'],
                    'trigger' => $matrix['trigger'],
                    'prevention_strategy' => $matrix['prevention_strategy'],
                    'descalation_strategy' => $matrix['descalation_strategy'],
                    'created_by' => $adminId,
                ]);
                $id = $this->db->insert_id();
                $savedIds[] = $id;
            }
           

        }
        // archive if anything left
        return [
            'status' => true,
            'saved_ids' => $savedIds,
        ];
    }
    
    /* 
     * Delete risk assessment
     * 
     * @params {array} $data
     * @params {int} $adminId
     * 
     * return type result
     */
    function delete_risk_assessment($data, $adminId) {
        if ($data && $data['risk_assessment_id']) {
            // Assign the data
            $updateData = [
                'is_deleted' => 1,
                'updated_by' => $adminId,
                'updated_date' => DATE_TIME,
            ];
            // Update the data using basic model
            $where = array('id' => $data['risk_assessment_id']);
            $result = $this->basic_model->update_records('crm_risk_assessment', $updateData, $where);
            return $result;
        } else {
            return '';
        }
    }

    /*
     * 
     * @params {object} $reqData
     * 
     * Return type Array - $result
     */
    public function get_risk_assessment_detail_by_id($reqData) {
        if ($reqData && $reqData->risk_assessment_id) {
            // Get subquery of cerated by
            $createdByNameSubQuery = $this->get_ra_created_by_sub_query();

            $risk_assessment_id = $reqData->risk_assessment_id;
            $column = ["ra.id as risk_assessment_id", "ra.reference_id", "ra.topic", "ra.account_type", "ra.account_id", "ra.owner_id", "ra.created_date", "ra.created_by", "ra.status as risk_status"];
            $orderBy = "ra.id";
            $orderDirection = "DESC";
            $this->db->select($column);
            $this->db->select("(CASE  
                WHEN ra.status = 1 THEN 'Draft'
                WHEN ra.status = 2 THEN 'Final'
                WHEN ra.status = 3 THEN 'InActive'
                Else '' end
            ) as status");
            $this->db->select("(" . $createdByNameSubQuery . ") as created_by");
            $this->db->from(TBL_PREFIX . 'crm_risk_assessment as ra');
            $this->db->order_by($orderBy, $orderDirection);
            $this->db->where(['ra.id' => $risk_assessment_id]);            
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->num_rows() > 0 ? $query->result_array() : [];
            $result = $query->num_rows() > 0 ? $result[0] : [];

            if (!empty($result)) {
                $result['matrices'] = $this->find_all_matrices_by_assessment_id($risk_assessment_id);
                $result['behaviour_support_matrices'] = $this->find_all_behvsupp_matrices_by_assessment_id($risk_assessment_id);
                $result['probability_options'] = $this->find_all_risk_probabilities();
                $result['impact_options'] = $this->find_all_risk_impacts();
            }


            // Get owner name as label and id as value
            if(isset($result['owner_id']) && $result['owner_id'] && $result['owner_id']!=null){
                $this->db->select(["CONCAT_WS(' ',m.firstname,m.lastname) as label", 'm.id as value']);
                $this->db->from(TBL_PREFIX . 'member as m');
                $this->db->join(TBL_PREFIX . "department as d", "d.id = m.department AND d.short_code = 'internal_staff'", "inner");
                $this->db->where(['m.archive' => 0,'m.id'=> $result['owner_id']]);
                $query = $this->db->get();
                $owner_row = $query->num_rows() > 0 ? $query->row_array() : [];
                $result['owner'] = $owner_row;
            }

            // Get person/org name as label and id as value
            if(isset($result['account_id']) && $result['account_id'] && $result['account_id']!=null){
                if($result['account_type'] == 1){
                    $this->db->select(["CONCAT_WS(' ',p.firstname,p.lastname) as label", 'p.id as value', "'1' as account_type", "p.profile_pic as avatar"]);
                    $this->db->from(TBL_PREFIX . 'person as p');
                    $this->db->where(['p.archive' => 0,'p.id'=> $result['account_id']]);
                }
    
                if($result['account_type'] == 2){
                    $this->db->select(["org.name as label", 'org.id as value', "'2' as account_type"]);
                    $this->db->from(TBL_PREFIX . 'organisation as org');
                    $this->db->where(['org.archive' => 0,'org.id'=> $result['account_id']]);
                }

                $query = $this->db->get();
                $person_row = $query->num_rows() > 0 ? $query->row_array() : [];
                $result['account_person'] = $person_row;
            }
            
            $result["living_situation"] = $this->get_living_situation_of_risk_assessment($result["risk_assessment_id"]);
            
            return [ "status" => true, 'data' => $result, 'msg' => 'Fetch Risk Assessment data successfully' ];
        } else {
            return [ "status" => false, 'error' => 'Risk Assessment Id is null'];
        }
    }
    
    /*
     * It is used for search owner - staff 
     * 
     * @params {string} $ownerName
     * $ownerName search key parameter
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
     * it is used for get account person name
     * 
     * @params {string} $accountName
     * $accountName search parameter
     * 
     * return type array
     * also used in create service agreement
     */
    public function get_account_person_name_search($accountName = '') {  
        $this->db->like('label', $accountName);
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
     * its use for update service agreement status
     * 
     * @params $reqData, $adminId
     * $reqData - reqdata of requested at front-end
     * $adminId action admin
     * 
     * return type boolean
     */
    function update_status_risk_assessment($reqData, $adminId){
        require_once APPPATH. 'Classes/sales/SalesInactiveAndCancelledReason.php';
        $reqData = (object) $reqData;
        
        $where = ["id" => $reqData->risk_assessment_id];
        $data = ["status" => $reqData->status];

        $this->basic_model->update_records("crm_risk_assessment", $data, $where);

        $response = ['status' => true, 'msg' => 'Status updated successfully.'];
       
        return $response;
    }
    
    /*
     * its use for update save living situation
     * 
     * @params $reqData, $adminId
     * $reqData - reqdata of requested at front-end
     * $adminId action admin
     * 
     * return type boolean
     */
    function save_living_situation($reqData, $adminId){
        $reqData = (object) $reqData;
        
        $living_data = [
            "risk_assessment_id" => $reqData->risk_assessment_id,  
            "living_situation" => $reqData->living_situation,  
            "living_situation_agency" => $reqData->living_situation_agency ?? "",  
            "informal_support" => $reqData->informal_support,  
            "informal_support_describe" => $reqData->informal_support_describe ?? "",  
            "lack_of_informal_support" => $reqData->lack_of_informal_support,  
            "lack_of_informal_support_describe" => $reqData->lack_of_informal_support_describe ?? "",  
            "updated" => DATE_TIME,  
            "archive" => 0,  
        ];

        $res = $this->basic_model->get_row("crm_risk_assessment_living_situation", ["id"], ["risk_assessment_id" => $reqData->risk_assessment_id]);
        
        if(!empty($res)){
            $this->basic_model->update_records("crm_risk_assessment_living_situation", $living_data, ["risk_assessment_id" => $reqData->risk_assessment_id]);
        }else{
            $living_data["created"] = DATE_TIME;
            $this->basic_model->insert_records("crm_risk_assessment_living_situation", $living_data, false);
        }
       
        return  $reqData->risk_assessment_id;;
    }
    
    /*
     * it is used for get living situation
     * 
     * @params {string} $accountName
     * $accountName search parameter
     * 
     * return type array
     * also used in create service agreement
     */
    public function get_living_situation_of_risk_assessment($risk_assessment_id) {
        $this->db->select(["risk_assessment_id", "living_situation", "living_situation_agency", "informal_support", "informal_support_describe", "lack_of_informal_support", "lack_of_informal_support_describe"]);
        $this->db->from(TBL_PREFIX . 'crm_risk_assessment_living_situation as rals');
        $this->db->where(['rals.archive' => 0]);
        $this->db->where(['rals.risk_assessment_id' => $risk_assessment_id]);
       
        return $this->db->get()->row();
    }
    
    /*
     * Model function to add/update court action
     * 
     * @params $reqData, $adminId
     * $data - request data
     * $adminId - action admin
     * $ca_action - operation "create" or "update"
     *
     * return type int - ra_court_action_id
     */
    public function save_court_action($data, $adminId, $ca_action)
    {
        if (!isset($data['risk_assessment_id'])) {
            return '';
        }
        // If value is false overwrite to NULL 
        if ($data["com_ser_order"] == 'false' || !$data["com_ser_order"]) {
            $data["com_ser_order"] = Null;
        }

        if ($data["inter_order"] == 'false' || !$data["inter_order"]) {
            $data["inter_order"] = Null;
        }

        if ($data["com_cor_order"] == 'false' || !$data["com_cor_order"]) {
            $data["com_cor_order"] = Null;
        }
        // Create order
        if ($ca_action == "create") {
            $insData = [
                'risk_assessment_id'=> $data['risk_assessment_id'],
                'not_applicable' => $data["not_applicable"],
                'com_ser_order' => $data["com_ser_order"],
                'com_cor_order' => $data["com_cor_order"],
                'inter_order' => $data["inter_order"],
                'created_by' => $adminId,
            ];
            $ra_court_action_id = $this->basic_model->insert_records('crm_risk_assessment_court_actions', $insData);
        } else {
            // Update court action
            $upData = [
                'risk_assessment_id'=> $data['risk_assessment_id'],
                'not_applicable' => $data["not_applicable"],
                'com_ser_order' => $data["com_ser_order"],
                'com_cor_order' => $data["com_cor_order"],
                'inter_order' => $data["inter_order"],
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'updated_date' => DATE_TIME,
            ];
            $ra_court_action_id = $this->basic_model->update_records('crm_risk_assessment_court_actions', $upData, array('risk_assessment_id'=> $data['risk_assessment_id']));
        }
        return $ra_court_action_id;
    }
    public function save_behaviuor_support($data, $adminId, $ca_action)
    {
        if (!isset($data['risk_assessment_id'])) {
            return '';
        }
        
        // If value is false overwrite to NULL 
        if ($data["bs_plan_status"] == '') {
            $data["bs_plan_status"] = Null;
        }

        if ($data["seclusion"] == 'false' || !$data["seclusion"]) {
            $data["seclusion"] = Null;
        }

        if ($data["chemical_constraint"] == 'false' || !$data["chemical_constraint"]) {
            $data["chemical_constraint"] = Null;
        } 
         if ($data["mechanical_constraint"] == 'false' || !$data["mechanical_constraint"]) {
            $data["mechanical_constraint"] = Null;
        }
        if ($data["physical_constraint"] == 'false' || !$data["physical_constraint"]) {
            $data["physical_constraint"] = Null;
        }
        if ($data["environmental"] == 'false' || !$data["environmental"]) {
            $data["environmental"] = Null;
        }
        // Create order
        if ($ca_action == "create") {
            $insData = [
                'risk_assessment_id'=> $data['risk_assessment_id'],
                'bs_not_applicable' => $data["bs_not_applicable"],
                'bs_plan_status' => $data["bs_plan_status"],
                'seclusion' => $data["seclusion"],
                'chemical_constraint' => $data["chemical_constraint"],
                'mechanical_constraint' => $data["mechanical_constraint"],
                'physical_constraint' => $data["physical_constraint"],
                'environmental' => $data["environmental"],
                'bs_noplan_status' => $data["bs_noplan_status"],
                'bs_plan_available_date' => $data["bs_plan_available_date"],
                'created_by' => $adminId,
            ];
            $ra_court_action_id = $this->basic_model->insert_records('ra_behavioursupport', $insData);
        } else {
            // Update court action
            $upData = [
                'risk_assessment_id'=> $data['risk_assessment_id'],
                'bs_not_applicable' => $data["bs_not_applicable"],
                'bs_plan_status' => $data["bs_plan_status"],
                'seclusion' => $data["seclusion"],
                'chemical_constraint' => $data["chemical_constraint"],
                'mechanical_constraint' => $data["mechanical_constraint"],
                'physical_constraint' => $data["physical_constraint"],
                'environmental' => $data["environmental"],
                'bs_noplan_status' => $data["bs_noplan_status"],
                'bs_plan_available_date' => $data["bs_plan_available_date"],
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'updated_date' => DATE_TIME,
            ];
            $ra_court_action_id = $this->basic_model->update_records('ra_behavioursupport', $upData, array('risk_assessment_id'=> $data['risk_assessment_id']));
        }
        return $ra_court_action_id;
    }
    
}

    
