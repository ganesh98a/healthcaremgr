<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Class : Task_model
 * Uses : for handle query operation of task 
 *
 */
class Task_model extends CI_Model {

    const MULTIPLIER_TYPE_PROBABILITY = 'probability';
    const MULTIPLIER_TYPE_IMPACT = 'impact';

    function __construct() {

        parent::__construct();
    }

    /*
     * It is used to get the task list
     * 
     * Operation: 
     *  - searching
     *  - filter
     *  - sorting
     * 
     * Return type Array
     */
    public function get_task_list($reqData) {
        // Get subqueries
        $assignedToNameSubQuery = $this->get_ra_assign_to_sub_query();
        $accountPersonSubQuery = $this->account_person_sub_query();
        $activity_type = 1;

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        // Searching column
        $src_columns = array('id', 'task_name', 'due_date', 'assign_to', 'task_status', 'created_at', 'related_to', 'name');
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
        $available_columns = ['id','lead_id','task_name','due_date','assign_to','task_status','created_at','archive','related_type','related_to','related_to_id','crm_participant_id','entity_type'];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_columns)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'cpst.id';
            $direction = 'DESC';
        }

        $select_column = ['cpst.id', 'cpst.lead_id', 'task_name', 'due_date', 'assign_to', 'task_status', 'created_at', "cpst.archive", "cpst.related_type", "cpst.related_to", "cpst.related_to as related_to_id", "cpst.crm_participant_id", "cpst.entity_type" ];
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(CASE  
            WHEN cpst.task_status = 0 THEN 'Assigned'
            WHEN cpst.task_status = 1 THEN 'Completed'
			WHEN cpst.task_status = 3 THEN 'Archived'
			Else '' end
		) as task_status");
        $this->db->select("(CASE  
            WHEN cpst.related_type = 1 THEN 
                (
                    SELECT 
                    CONCAT_WS(' ', opportunity.topic,'-','(',opportunity.opportunity_number,')')
                    FROM tbl_opportunity AS opportunity
                    WHERE opportunity.id = cpst.related_to
                    LIMIT 1
                )
            WHEN cpst.related_type = 2 THEN (
                    SELECT 
                    lead.lead_topic
                    FROM tbl_leads AS lead
                    WHERE lead.id = cpst.related_to
                    LIMIT 1
                )
            WHEN cpst.related_type = 3 THEN (
                    SELECT 
                    sa.service_agreement_id
                    FROM tbl_service_agreement AS sa
                    WHERE sa.id = cpst.related_to
                    LIMIT 1
                )
            WHEN cpst.related_type = 4 THEN (
                    SELECT 
                    na.title
                    FROM tbl_need_assessment AS na
                    WHERE na.id = cpst.related_to
                    LIMIT 1
                )
            WHEN cpst.related_type = 5 THEN (
                    SELECT 
                    cra.topic
                    FROM tbl_crm_risk_assessment AS cra
                    WHERE cra.id = cpst.related_to
                    LIMIT 1
                )
            WHEN cpst.related_type = 8 THEN (
                    SELECT 
                    raaa.id
                    FROM tbl_recruitment_applicant_applied_application AS raaa
                    WHERE raaa.id = cpst.related_to
                    LIMIT 1
                )
            WHEN cpst.related_type = 9 THEN (
                SELECT 
                ri.title
                FROM tbl_recruitment_interview AS ri
                WHERE ri.id = cpst.related_to
                LIMIT 1
            )
            Else '' end
        ) as related_to");
        $this->db->select("((select applicant_id from tbl_recruitment_applicant_applied_application as raaa where raaa.id = cpst.related_to)
                    ) as applicant_id");
        $this->db->select("(CASE  
            WHEN cpst.entity_type = 1 and cpst.lead_id is null THEN 
                (
                    SELECT 
                    CONCAT_WS(' ', person.firstname, person.lastname)
                    FROM tbl_person AS person
                    WHERE person.id = cpst.crm_participant_id
                    LIMIT 1
                )
            WHEN cpst.entity_type = 2 and cpst.lead_id is null THEN 
                (
                    SELECT 
                    CONCAT_WS(' ', person.firstname, person.lastname)
                    FROM tbl_person AS person
                    WHERE person.id = cpst.crm_participant_id
                    LIMIT 1
                )
            WHEN cpst.entity_type = 3 and cpst.lead_id is null THEN 
                (
                    SELECT 
                    CONCAT_WS(' ', person.firstname, person.lastname)
                    FROM tbl_person AS person
                    WHERE person.id = cpst.crm_participant_id
                    LIMIT 1
                )
            WHEN cpst.entity_type = 4 and cpst.lead_id is null and cpst.crm_participant_id is null THEN (
                    SELECT 
                    CONCAT_WS(' ', lead.firstname, lead.lastname)
                    FROM tbl_leads AS lead
                    WHERE lead.id = cpst.crm_participant_id
                    LIMIT 1
                )
            WHEN (cpst.entity_type = 8 or cpst.entity_type = 9) and cpst.lead_id is null and cpst.crm_participant_id is not null THEN (
                SELECT 
                CONCAT_WS(' ', person.firstname, person.lastname)
                FROM tbl_person AS person
                WHERE person.id = cpst.crm_participant_id
                LIMIT 1
            )
            WHEN (cpst.entity_type = 8 or cpst.entity_type = 9) and cpst.lead_id is null and cpst.crm_participant_id is null THEN (
                SELECT 
                CONCAT_WS(' ', lead.firstname, lead.lastname)
                FROM tbl_leads AS lead
                WHERE lead.id = cpst.crm_participant_id
                LIMIT 1
            )
            WHEN cpst.entity_type = 4 and cpst.lead_id is null and cpst.crm_participant_id is not null THEN (
                SELECT 
                CONCAT_WS(' ', person.firstname, person.lastname)
                FROM tbl_person AS person
                WHERE person.id = cpst.crm_participant_id
                LIMIT 1
            )
            WHEN cpst.entity_type = 5 and cpst.lead_id is null THEN (
                    SELECT 
                    CONCAT_WS(' ', person.firstname, person.lastname)
                    FROM tbl_person AS person
                    WHERE person.id = cpst.crm_participant_id
                    LIMIT 1
                )
            WHEN cpst.lead_id is not null THEN (
                SELECT 
                CONCAT_WS(' ', lead.firstname, lead.lastname)
                FROM tbl_leads AS lead
                WHERE lead.id = cpst.lead_id
                LIMIT 1
            )
            WHEN cpst.crm_participant_id is not null and cpst.entity_type = 0 THEN (
                SELECT 
                CONCAT_WS(' ', `person`.`firstname`, person.lastname)
                FROM tbl_person AS person
                WHERE person.id = cpst.crm_participant_id
                LIMIT 1
           )
            Else '' end
        ) as name");

        $this->db->join("tbl_sales_activity as act", "act.taskId = cpst.id ", "INNER");
        $this->db->where("act.activity_type", $activity_type);
        
        $this->db->from('tbl_crm_participant_schedule_task as cpst');
        $this->db->select("(" . $assignedToNameSubQuery . ") as assign_to");
        $this->db->where('cpst.archive', 0);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // Get total rows inserted count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

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
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetch task list successfully');
        return $return;
    }

   /*
     * It is used for generating sub-query for Assigned to contact
     * return type sql
     */
    public function get_ra_assign_to_sub_query() {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = cpst.assign_to", null, false);
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
        $this->db->where("sub_p.id = cpst.crm_participant_id", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * It is used to get the task by id
     * 
     * param taskid   
     * 
     * 
     */
    function get_task_details_for_view($reqData) {
         // Get subqueries         
         $task_id = $reqData->task_id;
         $assignedToNameSubQuery = $this->get_ra_assign_to_sub_query();
      
         $select_column = ['id',"cpst.lead_id", 'task_name', 'priority', 'due_date', 'assign_to', 'task_status', 'created_at', "cpst.archive", "cpst.related_type", "cpst.related_to", "cpst.related_to as related_to_id", "cpst.crm_participant_id", "cpst.entity_type" ];
         // Query
         $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
         $this->db->select("(CASE  
         WHEN cpst.task_status = 0 THEN 'Assigned'
         WHEN cpst.task_status = 1 THEN 'Completed'
         WHEN cpst.task_status = 3 THEN 'Archived'
         Else '' end
         ) as task_status");
         $this->db->select("(CASE  
            WHEN cpst.related_type = 1 THEN 
                (
                    SELECT 
                    CONCAT_WS(' ', opportunity.topic,'-','(',opportunity.opportunity_number,')')
                    FROM tbl_opportunity AS opportunity
                    WHERE opportunity.id = cpst.related_to
                    LIMIT 1
                )
            WHEN cpst.related_type = 2 THEN (
                    SELECT 
                    CONCAT_WS(' ', lead.lead_topic,'-','(',lead.lead_number,')')
                    FROM tbl_leads AS lead
                    WHERE lead.id = cpst.related_to
                    LIMIT 1
                )
            WHEN cpst.related_type = 3 THEN (
                    SELECT 
                    sa.service_agreement_id
                    FROM tbl_service_agreement AS sa
                    WHERE sa.id = cpst.related_to
                    LIMIT 1
                )
            WHEN cpst.related_type = 4 THEN (
                    SELECT 
                    na.title
                    FROM tbl_need_assessment AS na
                    WHERE na.id = cpst.related_to
                    LIMIT 1
                )
            WHEN cpst.related_type = 5 THEN (
                    SELECT 
                    cra.topic
                    FROM tbl_crm_risk_assessment AS cra
                    WHERE cra.id = cpst.related_to
                    LIMIT 1
                )
            WHEN cpst.related_type = 8 THEN (
                SELECT 
                raaa.id
                FROM tbl_recruitment_applicant_applied_application AS raaa
                WHERE raaa.id = cpst.related_to
                LIMIT 1
                )
            WHEN cpst.related_type = 9 THEN (
                SELECT 
                ri.title
                FROM tbl_recruitment_interview AS ri
                WHERE ri.id = cpst.related_to
                LIMIT 1
                )
            Else '' end
        ) as related_to");
        $this->db->select("(CASE  
            WHEN cpst.entity_type = 1 and cpst.lead_id is null THEN 
                (
                    SELECT 
                    CONCAT_WS(' ', person.firstname, person.lastname)
                    FROM tbl_person AS person
                    WHERE person.id = cpst.crm_participant_id
                    LIMIT 1
                )
            WHEN cpst.entity_type = 2 and cpst.lead_id is null THEN 
                (
                    SELECT 
                    CONCAT_WS(' ', person.firstname, person.lastname)
                    FROM tbl_person AS person
                    WHERE person.id = cpst.crm_participant_id
                    LIMIT 1
                )
            WHEN cpst.entity_type = 3 and cpst.lead_id is null THEN 
                (
                    SELECT 
                    CONCAT_WS(' ', person.firstname, person.lastname)
                    FROM tbl_person AS person
                    WHERE person.id = cpst.crm_participant_id
                    LIMIT 1
                )
            WHEN cpst.entity_type = 4 and cpst.lead_id is null and cpst.crm_participant_id is null THEN (
                SELECT 
                CONCAT_WS(' ', lead.firstname, lead.lastname)
                FROM tbl_leads AS lead
                WHERE lead.id = cpst.crm_participant_id
                LIMIT 1
            )
            WHEN cpst.entity_type = 4 and cpst.lead_id is null and cpst.crm_participant_id is not null THEN (
                SELECT 
                CONCAT_WS(' ', person.firstname, person.lastname)
                FROM tbl_person AS person
                WHERE person.id = cpst.crm_participant_id
                LIMIT 1
            )
            WHEN cpst.entity_type = 5 and cpst.lead_id is null THEN (
                SELECT 
                CONCAT_WS(' ', person.firstname, person.lastname)
                FROM tbl_person AS person
                WHERE person.id = cpst.crm_participant_id
                LIMIT 1
            )
            WHEN (cpst.entity_type = 8 or cpst.entity_type = 9) and cpst.lead_id is null and cpst.crm_participant_id is not null THEN (
                SELECT 
                CONCAT_WS(' ', person.firstname, person.lastname)
                FROM tbl_person AS person
                WHERE person.id = cpst.crm_participant_id
                LIMIT 1
            )
            WHEN (cpst.entity_type = 8 or cpst.entity_type = 9) and cpst.lead_id is null and cpst.crm_participant_id is null THEN (
                SELECT 
                CONCAT_WS(' ', lead.firstname, lead.lastname)
                FROM tbl_leads AS lead
                WHERE lead.id = cpst.crm_participant_id
                LIMIT 1
            )
            WHEN cpst.lead_id is not null THEN (
                SELECT 
                CONCAT_WS(' ', lead.firstname, lead.lastname)
                FROM tbl_leads AS lead
                WHERE lead.id = cpst.lead_id
                LIMIT 1
            )
            WHEN cpst.crm_participant_id is not null and cpst.entity_type = 0 THEN (
                SELECT 
                CONCAT_WS(' ', `person`.`firstname`, person.lastname)
                FROM tbl_person AS person
                WHERE person.id = cpst.crm_participant_id
                LIMIT 1
           )
            Else '' end
        ) as name");
        $this->db->select("((select applicant_id from tbl_recruitment_applicant_applied_application as raaa where raaa.id = cpst.related_to)
                    ) as applicant_id");
        $this->db->select("(select name from tbl_crm_task_priority as ctp where ctp.id = cpst.priority) as priority");
         $this->db->from('tbl_crm_participant_schedule_task as cpst');
         $this->db->select("(" . $assignedToNameSubQuery . ") as assign_to");
         $this->db->where('cpst.archive', 0);
         $this->db->where('cpst.id', $task_id);
 
         $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
         // Get total rows inserted count
 
       
         // Get the query result
         $result = $query->row();
         $return = array('data' => $result, 'status' => true);
         return $return;
    }


    function get_task_details_for_edit($reqData) {
        $task_id = $reqData->task_id;
     
        $select_column = ['id', 'task_name','crm_participant_id','entity_id', 'priority', 'due_date', 'assign_to', 'task_status', 'created_at', "cpst.archive", "cpst.related_type", "cpst.related_to", "cpst.related_to as related_to_id", "cpst.crm_participant_id", "cpst.entity_type" ];
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);  
    
            $this->db->select("(CASE  
            WHEN cpst.entity_type = 1 and cpst.lead_id is null THEN 
                (
                    SELECT 
                    CONCAT_WS(' ', person.firstname, person.lastname)
                    FROM tbl_person AS person
                    WHERE person.id = cpst.crm_participant_id
                    LIMIT 1
                )
            WHEN cpst.entity_type = 2 and cpst.lead_id is null THEN 
                (
                    SELECT 
                    CONCAT_WS(' ', person.firstname, person.lastname)
                    FROM tbl_person AS person
                    WHERE person.id = cpst.crm_participant_id
                    LIMIT 1
                )
            WHEN cpst.entity_type = 3 and cpst.lead_id is null THEN 
                (
                    SELECT 
                    CONCAT_WS(' ', person.firstname, person.lastname)
                    FROM tbl_person AS person
                    WHERE person.id = cpst.crm_participant_id
                    LIMIT 1
                )
            WHEN cpst.entity_type = 4 and cpst.lead_id is null and cpst.crm_participant_id is null THEN (
                    SELECT 
                    CONCAT_WS(' ', lead.firstname, lead.lastname)
                    FROM tbl_leads AS lead
                    WHERE lead.id = cpst.crm_participant_id
                    LIMIT 1
                )
            WHEN cpst.entity_type = 4 and cpst.lead_id is null and cpst.crm_participant_id is not null THEN (
                SELECT 
                CONCAT_WS(' ', person.firstname, person.lastname)
                FROM tbl_person AS person
                WHERE person.id = cpst.crm_participant_id
                LIMIT 1
            )
            WHEN cpst.entity_type = 5 and cpst.lead_id is null THEN (
                    SELECT 
                    CONCAT_WS(' ', person.firstname, person.lastname)
                    FROM tbl_person AS person
                    WHERE person.id = cpst.crm_participant_id
                    LIMIT 1
                )
            WHEN (cpst.entity_type = 8 or cpst.entity_type = 9) and cpst.lead_id is null and cpst.crm_participant_id is not null THEN (
                SELECT 
                CONCAT_WS(' ', person.firstname, person.lastname)
                FROM tbl_person AS person
                WHERE person.id = cpst.crm_participant_id
                LIMIT 1
            )
            WHEN (cpst.entity_type = 8 or cpst.entity_type = 9) and cpst.lead_id is null and cpst.crm_participant_id is null THEN (
                SELECT 
                CONCAT_WS(' ', lead.firstname, lead.lastname)
                FROM tbl_leads AS lead
                WHERE lead.id = cpst.crm_participant_id
                LIMIT 1
            )
            WHEN cpst.lead_id is not null THEN (
                SELECT 
                CONCAT_WS(' ', lead.firstname, lead.lastname)
                FROM tbl_leads AS lead
                WHERE lead.id = cpst.lead_id
                LIMIT 1
            )
            Else '' end
        ) as name");
       $this->db->select("(select name from tbl_crm_task_priority as ctp where ctp.id = cpst.priority) as priority");
        $this->db->from('tbl_crm_participant_schedule_task as cpst');
        // $this->db->select("(" . $assignedToNameSubQuery . ") as assign_to");
        $this->db->where('cpst.archive', 0);
        $this->db->where('cpst.id', $task_id);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // Get total rows inserted count

      
        // Get the query result
        $result = $query->row();
        $return = array('data' => $result, 'status' => true);
        return $return;
   }
}

    
