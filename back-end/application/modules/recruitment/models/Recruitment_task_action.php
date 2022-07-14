<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Recruitment_task_action extends CI_Model
{

    public function __construct()
    {
        // Call the CI_Model constructor
        parent::__construct();
    }

    function get_recruiter_listing_for_create_task($reqData)
    {
        $search = '' ;
        if(isset($reqData->search) && !empty($reqData->search)){
            $search = $reqData->search;
        }

        if (!empty($reqData->assigned_user)) {
            $already_assigned = json_decode(json_encode($reqData->assigned_user), true);
            $already_assigned_ids = array_column($already_assigned, 'value');

            if (!empty($already_assigned_ids)) {
                $this->db->where_not_in('a.id', $already_assigned_ids);
            }
        }

        $this->db->select("(CASE WHEN a.is_super_admin = 1 THEN 1 
			WHEN ((select id from tbl_recruitment_staff as rs where rs.adminId = a.uuid and rs.archive= 0 AND rs.status = 1 AND rs.approval_permission = 1) > 0) THEN 1
			ELSE 0 end) as is_recruitment_user");
        $this->db->select(array('concat(a.firstname," ",a.lastname) as label', 'a.uuid as value', '"2" as primary_recruiter', 'a.username as email'), false);
        $this->db->from('tbl_member as a');
        $this->db->join('tbl_department as d', 'd.id = a.department AND d.short_code = "internal_staff"', 'inner');
        #$this->db->join('tbl_recruitment_staff as rs', 'rs.adminId = a.id and rs.archive=a.archive', 'inner');

        if(!empty($search)){
            $this->db->like('concat(a.firstname," ",a.lastname)', $search);
        }
        $this->db->where('a.archive', 0);
        $this->db->where('a.status', 1);
        $this->db->having("is_recruitment_user", 1);

        // now both type admin can assign as recruiter
        #$this->db->where('rs.its_recruitment_admin', 0);

        $query = $this->db->get();
        $result = $query->result();
        #last_query();
        return $result;
    }

    function get_selected_recruiter($reqData)
    {
        $search = $reqData->selected_recruiter;
        $search = !empty($search) ? $search : [0];
        $search = !empty($search) && is_array($search) ? $search : [$search];

        $this->db->where_in('a.id', $search);
        $this->db->select(array('concat(a.firstname," ",a.lastname) as label', 'a.id as value', '"2" as primary_recruiter'), false);
        $this->db->from('tbl_member as a');
        $this->db->join('tbl_department as d', 'd.id = a.department AND d.short_code = "internal_staff"', 'inner');
        $this->db->join('tbl_recruitment_staff as rs', 'rs.adminId = a.id and rs.archive=a.archive', 'inner');
        $this->db->where('a.archive', 0);
        $this->db->where('a.status', 1);
        $this->db->where('rs.status', 1);
        $this->db->where('rs.approval_permission', 1);
        $this->db->where('rs.its_recruitment_admin', 0);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $result = ['status' => true, 'recruiter_data' => $query->result()];
        } else {
            $result = ['status' => true, 'recruiter_data' => []];
        }

        return $result;
    }

    function get_applicant_option_for_create_task($reqData, $adminId, $extra_paramter = [])
    {
        // CONCERN: If application ID is given, 
        // should I search against applicants applying to the same job?
        // or should I just choose any applicant (default)?

        // if application ID is provided, include the applicant of this application
        $foundApplicantByApplicationId = null;
        if (isset($extra_paramter['application_id']) && !empty($extra_paramter['application_id'])) {
            $foundApplicantByApplicationId = $this->db->get_where('tbl_recruitment_applicant_applied_application', ['id' => $extra_paramter['application_id']], 1)->row_array();
        }

        // here applicant_ids is optional parameter for check applicant stages (server side validation)

        $search = $reqData->search;
        $serchById = isset($reqData->type) && $reqData->type == 'id' ? true : false;
        $stage_label_id = 0;

        $stage_det = $this->basic_model->get_row('recruitment_task_stage', ['stage_label_id'], ['id' => $reqData->task_stage]);
        if (!empty($stage_det)) {
            $stage_label_id = $stage_det->stage_label_id;
        }

        $its_recruiter_admin = check_its_recruiter_admin($adminId);

        if (!empty($extra_paramter['applicant_ids'])) {
            $this->db->where_in('ra.id', $extra_paramter['applicant_ids']);
        }

        if (!empty($reqData->applicant_list)) {
            $already_assigned = json_decode(json_encode($reqData->applicant_list), true);
            $already_assigned_ids = array_column($already_assigned, 'application_id');

            if (!empty($already_assigned_ids)) {
                $this->db->where_not_in('applied_applicantion.id', $already_assigned_ids);
            }
        }

        // if its not recruiter admin then add check only those applicant come who assigned him/her
        if (!$its_recruiter_admin) {
            $this->db->where('ra.recruiter', $adminId);
        }

        $this->db->select(array('concat(ra.firstname," ",ra.lastname) as label', 'ra.id as value', 'ra.id as applicant_id', 'applied_applicantion.id as application_id', 'rae.email', 'rap.phone'));
        $this->db->from('tbl_recruitment_applicant_applied_application as applied_applicantion');

        $this->db->join('tbl_recruitment_applicant as ra', 'applied_applicantion.applicant_id = ra.id', 'Inner');
        $this->db->join('tbl_recruitment_applicant_email as rae', 'rae.applicant_id = ra.id AND primary_email = 1', 'Inner');
        $this->db->join('tbl_recruitment_applicant_phone as rap', 'rap.applicant_id = ra.id AND primary_phone = 1', 'Inner');

        if ($serchById) {
            $this->db->where('applied_applicantion.id', $reqData->application_id);
            $this->db->limit(1);
        } else {
            $this->db->like('concat(ra.firstname," ",ra.lastname)', $search);
        }
        if ($stage_label_id > 0) {
            $this->db->where("applied_applicantion.current_stage IN (SELECT id FROM tbl_recruitment_stage as rs WHERE archive = 0 AND stage_label_id = " . $this->db->escape_str($stage_label_id, true) . ")", null, false);
        }
        $this->db->where('applied_applicantion.archive', 0);
        $this->db->where('applied_applicantion.status', 1);
        $this->db->where('ra.flagged_status', 0);

        if ($stage_label_id > 0) {
            $this->db->where("not EXISTS (
                select s_rta.application_id,
                (case when s_rta.status = 1 AND (s_rsl.stage_number = 3 or s_rsl.stage_number = 6) 
                then (select mark_as_no_show from tbl_recruitment_applicant_group_or_cab_interview_detail where recruitment_task_applicant_id = s_rta.id AND archive = 0)
                else 0 end) as mark_as_no_show
                from tbl_recruitment_task_applicant as s_rta 
                inner join tbl_recruitment_task s_rt on s_rt.id = s_rta.taskId AND s_rt.status = 1
                inner join tbl_recruitment_stage_label s_rsl on s_rsl.stage_number = s_rt.task_stage AND s_rsl.id = " . $this->db->escape_str($stage_label_id, true) . " 
                where applied_applicantion.id = s_rta.application_id AND s_rta.archive = 0 AND s_rta.status IN (1,0) having mark_as_no_show = 0
            )", null, false);
        }

//        if ($foundApplicantByApplicationId) {
//            // is the applicant ID of the application is already included in $already_assigned_ids,
//            // let's not reprocess it again because it is already processed when `where_not_in` is executed
//            if (!in_array($foundApplicantByApplicationId['applicant_id'], array_values($already_assigned_ids ?? []))) {
//                $this->db->or_where_in('ra.id', $foundApplicantByApplicationId['applicant_id']);
//            }
//        }

        $this->db->group_by('applied_applicantion.id');

        $query = $this->db->get();
        $result = $query->result();
//        last_query();
        return $result;
    }

    function create_task($reqData, $created_by)
    {

        $date = DateFormate($reqData->task_date, 'Y-m-d');
        $start_time = DateFormate($reqData->start_time, 'H:i');
        $end_time = DateFormate($reqData->end_time, 'H:i');

        $start_datetime = DateFormate($date . $start_time, 'Y-m-d H:i');
        $end_datetime = DateFormate($date . $end_time, 'Y-m-d H:i');

        $task_data = array(
            'task_name' => $reqData->task_name,
            'task_stage' => $reqData->task_stage,
            'created_by' => $created_by,
            'start_datetime' => $start_datetime,
            'end_datetime' => $end_datetime,
            'training_location' => $reqData->training_location,
            'mail_status' => 0,
            'form_id' => $reqData->form_id ?? 0,
            'relevant_task_note' => $reqData->relevant_task_note ?? '',
            'max_applicant' => $reqData->max_applicant,
            'created' => DATE_TIME,
            'action_at' => DATE_TIME,
            'status' => 1,
            'task_piority' => 1,
        );

        $taskId = $this->basic_model->insert_records('recruitment_task', $task_data, FALSE);

        // on basis of send email_name_key
        $email_name_key = $this->get_email_name_key_for_task($reqData->task_stage, 'create_new_task');

        $this->add_update_task_applicant($reqData->applicant_list, $taskId, $reqData->task_stage, $created_by, $email_name_key);

        $this->add_update_task_recruiter($reqData->assigned_user, $taskId);
        return $taskId; 
    }

    function get_recruitment_task_list($reqData, $adminId)
    {
        $its_recruiterment_admin = check_its_recruiter_admin($adminId);

        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $available_column = array('id', 'task_name', 'task_stage', 'start_datetime', 'status', "stage", "max_applicant");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'rt.start_datetime';
            $direction = 'asc';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        if (!empty($filter->filter_val)) {
            if ($filter->filter_val === 'in_progress') {
                $this->db->where('rt.status', 1);
            } elseif ($filter->filter_val === 'completed') {
                $this->db->where('rt.status', 2);
            } elseif ($filter->filter_val === 'archive') {
                $this->db->where('rt.status', 4);
            } elseif ($filter->filter_val === 'cancelled') {
                $this->db->where('rt.status', 3);
            }
        }
        //$extraCond = " 1=1";
        $extraCondSub = " ";
        //$extraCond = "rtra.primary_recruiter=1 ";
        if (isset($filter->recruiterId) && !empty($filter->recruiterId)) {

            //$extraCond = "rtra.recruiterId='" . $filter->recruiterId . "'";
            $this->db->where('rtra.recruiterId', $filter->recruiterId);
            $extraCondSub .= " AND sub_rtra.recruiterId='" . $filter->recruiterId . "' ";
            if (isset($filter->actionTypeSatge) && !empty($filter->actionTypeSatge)) {
                $stageTypeFilter = strtolower($filter->actionTypeSatge);
                if ($stageTypeFilter == 'group') {
                    $this->db->where('rt.task_stage', 3);
                } else if ($stageTypeFilter == 'cab') {
                    $this->db->where('rt.task_stage', 6);
                } else if ($stageTypeFilter == 'other') {
                    $this->db->where_not_in('rt.task_stage', [3, 6]);
                } else {
                    $this->db->where('rt.task_stage', 0);
                }
            }
        }

        $src_columns = array('rt.task_name', 'spell_condition');


        if (!$its_recruiterment_admin) {
            $this->db->where('if(rt.task_stage IN (3,6), true, rtra.recruiterId = "' . $adminId . '")');
            // $this->db->where('if(rt.task_stage IN (3,7), true, rt.created_by = "' . $adminId . '")');;
        }

        if (!empty($filter->search)) {
            $this->db->group_start();
            $search_value = addslashes($filter->search);

            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $this->db->or_like($serch_column[0], $search_value);
                } else if ($column_search == 'spell_condition') {

                    $this->db->or_where("case when rtra.primary_recruiter>0 then rt.id in (select sub_rtra.taskId from tbl_recruitment_task_recruiter_assign as sub_rtra 
                        inner join tbl_member as sub_a on sub_a.id = sub_rtra.recruiterId where sub_rtra.taskId = rt.id AND sub_rtra.primary_recruiter = 1 AND sub_rtra.archive=0 and `sub_a`.`firstname` LIKE '%" . $this->db->escape_str($search_value, true) . "%' ESCAPE '!' OR `sub_a`.`lastname` LIKE '%" . $this->db->escape_str($search_value, true) . "%' ESCAPE '!' ) ELSE '' END", null, false);
                } else {
                    $this->db->or_like($column_search, $search_value);
                }
            }
            $this->db->group_end();
        }



        $colowmn = array('rt.id', 'rt.task_name', 'rt.task_stage', 'rt.start_datetime', 'rt.status', "concat(rt.task_stage,' - ',rts.name) as stage", "rt.max_applicant");

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $colowmn)), false);
        $this->db->select("CASE
            WHEN rt.task_stage='3' OR rt.task_stage='6' THEN (SELECT COUNT(IF(status= 0, 1, NULL)) 'pending' FROM tbl_recruitment_task_applicant where taskId = rt.id AND archive = 0)
            ELSE NULL
            END as pending");
        $this->db->select("CASE
            WHEN rt.task_stage='3' OR rt.task_stage='6' THEN (SELECT COUNT(IF(status= 1, 1, NULL)) 'accepted' FROM tbl_recruitment_task_applicant where taskId = rt.id AND archive = 0)
            ELSE NULL
            END as accepted");


        $this->db->select("CASE WHEN rtra.primary_recruiter>0 THEN (select concat_ws(' ',sub_a.firstname, sub_a.lastname) 
            from tbl_recruitment_task_recruiter_assign as sub_rtra
            inner join tbl_member as sub_a on sub_a.id = sub_rtra.recruiterId 
            where sub_rtra.taskId = rt.id AND sub_rtra.primary_recruiter = 1 and sub_rtra.archive=0) else '' end as primary_recruiter");
        $this->db->select("(CASE WHEN rt.end_datetime<'" . DATE_TIME . "' AND rt.status = 1 THEN  '1' ELSE '0' END) as overdue_task", false);

        $this->db->from('tbl_recruitment_task as rt');
        $this->db->join('tbl_recruitment_task_stage as rts', 'rts.id = rt.task_stage', 'inner');
        $this->db->join('(SELECT sub_rtra.* FROM  `tbl_recruitment_task_recruiter_assign` as sub_rtra  where `sub_rtra`.`archive`=0 ' . $extraCondSub . ') as rtra', 'rtra.taskId = rt.id', 'left');


        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $this->db->group_by('rt.id');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        //last_query(1);

        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $val->slot_applicable = false;

                if ($val->task_stage == 3 || $val->task_stage == 6) {
                    $val->filled = $val->pending + $val->accepted;
                    $val->slot_applicable = true;
                }
            }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'status' => true);

        return $return;
    }

    function get_recruitment_task_list_calendar($reqData, $adminId)
    {
        $its_recruiter_admin = check_its_recruiter_admin($adminId);
        $str = array('1', '2');

        if (!$its_recruiter_admin) {
            $this->db->join('tbl_recruitment_task_recruiter_assign as rtra', "rtra.taskId = rt.id AND primary_recruiter = 1 AND recruiterId = " . $adminId, "INNER");
        }

        $colowmn = array('date(rt.start_datetime) as start_date', 'date(rt.end_datetime) as end_date', 'COUNT(IF( date(rt.start_datetime) >= date(NOW()) && rt.status= 1, 1, NULL)) as dueCount', '
            COUNT(IF(date(rt.start_datetime) <= date(NOW()) && rt.status= 2, 1, NULL)) as CompleteCount', 'rt.status');

        $this->db->select("coalesce(GROUP_CONCAT(CASE WHEN date(rt.start_datetime) <= date(NOW()) && rt.status= 2 THEN concat(rt.task_name,'@@SUB_BKR@@', rt.id)  ELSE NULL end SEPARATOR '@@MN_BKR@@'),'') AS comp_task_list");
        $this->db->select("coalesce(GROUP_CONCAT(CASE WHEN date(rt.start_datetime) >= date(NOW()) && rt.status= 1 THEN concat(rt.task_name,'@@SUB_BKR@@', rt.id)  ELSE NULL end SEPARATOR '@@MN_BKR@@'),'') AS due_task_list");

        $this->db->select($colowmn);
        $this->db->from('tbl_recruitment_task as rt');

        $this->db->where_in('rt.status', $str);
        $this->db->where("DATE_FORMAT(rt.start_datetime,'%m')", DateFormate($reqData->date, 'm'));
        $this->db->group_by('date(rt.start_datetime)');
        $query = $this->db->get();

        $result = $query->result();


        if (!empty($result)) {
            foreach ($result as $val) {

                $t_s = [];
                if ($val->comp_task_list) {
                    $comp = explode("@@MN_BKR@@", $val->comp_task_list);
                    if (!empty($comp)) {
                        $t_s = [];
                        foreach ($comp as $task) {
                            $x = explode("@@SUB_BKR@@", $task);
                            $t_s[] = ["taskId" => $x[1], "task_name" => $x[0]];
                        }
                    }
                }
                $val->comp_task_list = $t_s;

                $t_s = [];
                if ($val->due_task_list) {
                    $comp = explode("@@MN_BKR@@", $val->due_task_list);
                    if (!empty($comp)) {
                        $t_s = [];
                        foreach ($comp as $task) {
                            $x = explode("@@SUB_BKR@@", $task);
                            $t_s[] = ["taskId" => $x[1], "task_name" => $x[0]];
                        }
                    }
                }
                $val->due_task_list = $t_s;
            }
        }

        return ['status' => true, 'data' => $result];
    }

    function get_task_details($reqData, $adminId)
    {
        $this->db->select("(select title from tbl_recruitment_form as rf where rf.id = rt.form_id) as form_name");
        $this->db->select(['rt.task_name', 'rt.start_datetime', 'rt.end_datetime', 'rl.name as training_location', 'rt.relevant_task_note', 'rt.max_applicant', 'rts.name as stage', 'rt.status', 'rt.task_stage']);
        $this->db->from('tbl_recruitment_task as rt');
        $this->db->join('tbl_recruitment_task_stage as rts', 'rts.id = rt.task_stage', 'inner');
        $this->db->join('tbl_recruitment_location as rl', 'rl.id = rt.training_location', 'inner');
        $this->db->where('rt.id', $reqData->taskId);

        $query = $this->db->get();
        $res = $query->row();

        if (!empty($res)) {
            $res->edit_mode = true;
            if (in_array($res->status, [2, 3, 4]) || (strtotime(DATE_TIME) > strtotime($res->start_datetime))) {
                $res->edit_mode = false;
            }
            $res->assigned_user = $this->get_attached_recruit_with_task($reqData->taskId, $adminId);
            $res->applicant_list = $this->get_attached_applicant_with_task($reqData->taskId);
        }
        return $res;
    }

    function get_attached_recruit_with_task($taskId, $adminId)
    {
        $this->db->select(['rtra.id', 'rtra.recruiterId as value', 'rtra.primary_recruiter', 'concat(a.firstname," ",a.lastname) as label']);
        $this->db->from('tbl_recruitment_task_recruiter_assign as  rtra');
        $this->db->join('tbl_member as  a', 'a.id = rtra.recruiterId', 'INNER');
        $this->db->join('tbl_department as d', 'd.id = a.department AND d.short_code = "internal_staff"', 'inner');
        $this->db->where('rtra.taskId', $taskId);
        $this->db->where('rtra.archive', 0);

        $query = $this->db->get();
        $result = $query->result();

        $its_recruiter_admin = check_its_recruiter_admin($adminId);

        if (!empty($result) && !$its_recruiter_admin) {
            foreach ($result as $val) {
                if ($val->primary_recruiter == 1) {
                    $val->non_removal_primary = true;
                }
            }
        }
        return $query->result();
    }

    function get_attached_applicant_with_task($taskId)
    {
        $this->db->select(['rta.id', 'rta.status', 'rta.applicant_id as value', 'concat(ra.firstname," ",ra.lastname) as label', 'rta.applicant_id', 'ra_email.email', 'ra_phone.phone']);
        $this->db->from('tbl_recruitment_task_applicant as  rta');
        $this->db->join('tbl_recruitment_applicant as  ra', 'ra.id = rta.applicant_id', 'INNER');
        $this->db->join('tbl_recruitment_applicant_email as  ra_email', 'ra.id = ra_email.applicant_id AND primary_email = 1', 'INNER');
        $this->db->join('tbl_recruitment_applicant_phone as  ra_phone', 'ra.id = ra_email.applicant_id  AND primary_phone = 1', 'INNER');
        $this->db->where('rta.taskId', $taskId);
        $this->db->where('rta.archive', 0);

        $this->db->group_by('ra.id');
        $query = $this->db->get();
        return $query->result();
    }

    function verify_edit_task_applicant($old_applicant, $reqData)
    {
        $this->db->select(['rta.id', 'rta.applicant_id']);
        $this->db->from('tbl_recruitment_task_applicant as  rta');
        $this->db->join('tbl_recruitment_applicant as  ra', 'ra.id = rta.applicant_id', 'INNER');

        $this->db->where('rta.taskId', $reqData->taskId);
        $this->db->where_in('rta.applicant_id', $old_applicant);

        $this->db->group_by('ra.id');
        $query = $this->db->get();
        return $query->result();
    }

    function update_task($reqData, $adminId)
    {
        // on basis of send email_name_key
        $email_name_key = $this->get_email_name_key_for_task($reqData->task_stage, 'create_new_task');

        $this->add_update_task_applicant($reqData->applicant_list, $reqData->taskId, $reqData->task_stage, $adminId, $email_name_key);

        $this->add_update_task_recruiter($reqData->assigned_user, $reqData->taskId);
    }

     function add_update_task_applicant($applicant_list, $taskId, $task_stage, $adminId, $email_name_key)
    {

        if (!empty($applicant_list)) {

            $attach_applicant = [];
            $invitation_sent_applicant = [];
            $invitation_sent_applicant_id = [];
            $insert_inc = 0;

            foreach ($applicant_list as $val) {
                if (!empty($val->id) && !empty($val->removed)) {
                    $this->basic_model->update_records('recruitment_task_applicant', ['archive' => 1], ['id' => $val->id]);
                } elseif (empty($val->id)) {

                    $application_id = 0;
                    if (isset($val->application_id) && !empty($val->application_id)) {
                        $application_id = $val->application_id;
                    }

                    $status = 0;

                    // only send mail for group interview, cab day interview and all 3 stages of individual interview
                    if (in_array($task_stage, array(3,6,9,10,11)) ) {
                        $status = 1;

                        $applicantIds_for_create_interview_details[] = $val->value;
                        $mail_details[0]['applicant_id'] = $val->value;

                        $stageKeyData = ['3'=>'group_schedule_interview','9'=>'schedule_individual_interview','10'=>'schedule_individual_interview','11'=>'schedule_individual_interview'];
                        $stageKey=  $stageKeyData[$task_stage]??'';
                        if(!empty($stageKey)){

                            if ($application_id) {
                                $invitation_sent_applicant[$stageKey][] = ['task_stage' => $task_stage,'applicant_id' => $val->value, 'application_id' => $application_id];
                            } else {
                                $invitation_sent_applicant[$stageKey][] = ['task_stage' => $task_stage,'applicant_id' => $val->value];
                            }

                        }
                    }

                    /*Get stage_label_id*/
                    $stage_label_id = 0;
                    $stage_det = $this->basic_model->get_row('recruitment_task_stage', ['stage_label_id'], ['id' => $task_stage,'archive'=>0]);
                    if (!empty($stage_det)) {
                     $stage_label_id = $stage_det->stage_label_id;
                    }

                    $attach_applicant[$insert_inc] = array(
                        'taskId' => $taskId,
                        'applicant_id' => $val->value,
                        'application_id' => $application_id,
                        'applicant_message' => '',
                        'email_status' => $status,
                        'status' => 1, // we are automatically accepting the invite HCM-2757
                        //'token_email' => $token,
                        'invitation_send_at' => DATE_TIME,
                        'invitation_accepted_at' => DATE_TIME,
                        'created' => DATE_TIME,
                        'stage_label_id'=>$stage_label_id
                    );

                    $attch_arr[0] = $attach_applicant[$insert_inc];

                    // Create interview and send invitations
                    if (isset($attch_arr) && !empty($attch_arr)) {
                        $this->basic_model->insert_records('recruitment_task_applicant', $attch_arr, true);

                        if ($task_stage == 3 || $task_stage == 6) {
                            // get the recruitment task applicant id and applicant for create interview details
                            $invitation_sent_applicant_id[0] = $val->value;
                            $recruitment_task_applicant_ids = $this->get_recruitment_task_applicant_ids($taskId, $invitation_sent_applicant_id);
                            // create interview details
                            $this->create_interview_details_for_applicant($recruitment_task_applicant_ids, $task_stage);
                        }

                        if (!empty($mail_details)) {
                            if ($task_stage == 3 || $task_stage == 6) {
                                $call_for = 'for_group_cab';
                            } else {
                                $call_for = '';
                            }

                            foreach ($mail_details as $applicant) {
                                $det_task['call_for'] = $call_for;
                                $det_task['adminId'] = $adminId;
                                $det_task['taskId'] = $taskId;
                                $det_task['applicant_id'] = $applicant['applicant_id'];

                                if ($application_id) {
                                    $det_task['application_id'] = $application_id;
                                }
                                // Send invitation mail
                                $this->send_task_mail_to_applicant($det_task, $email_name_key);
                            }
                        }
                    }

                    if (!empty($invitation_sent_applicant)) {
                        $this->stage_update_invitation_sent_applicant($invitation_sent_applicant, $adminId);
                    }
                    $insert_inc++;
                }
            }
        }
    }


    function create_interview_details_for_applicant($recruitment_task_applicant_ids, $task_stage)
    {
        $interview_type = $task_stage == 3 ? 1 : 2;
        $device_pin = random_genrate_password(8);

        if (!empty($recruitment_task_applicant_ids)) {
            foreach ($recruitment_task_applicant_ids as $val) {
                $interview_details[] = array(
                    'deviceId' => 0,
                    'device_pin' => $device_pin,
                    'interview_type' => $interview_type,
                    'recruitment_task_applicant_id' => $val->recruitment_task_applicant_id,
                    'quiz_status' => 0,
                    'applicant_status' => 0,
                    'applicant_status' => 0,
                    'contract_status' => 0,
                    'archive' => 0,
                    'created' => DATE_TIME,
                    'updated' => DATE_TIME,
                );
            }
        }

        if (!empty($interview_details)) {
            $this->basic_model->insert_records('recruitment_applicant_group_or_cab_interview_detail', $interview_details, $multiple = TRUE);
        }
    }

    function get_recruitment_task_applicant_ids($taskId, $applicantIds)
    {
        $this->db->select(['id as recruitment_task_applicant_id', 'applicant_id']);
        $this->db->from('tbl_recruitment_task_applicant');
        $this->db->where_in('applicant_id', $applicantIds);
        $this->db->where('taskId', $taskId);

        $query = $this->db->get();
        return $response = $query->result();
    }

    function send_task_mail_to_applicant($appcnt_data, $email_name_kay)
    {
        $select_ary = ["rae.email", "rt.start_datetime", "rt.end_datetime", "rl.name as task_location", "rt.task_stage", "ra.firstname", "ra.lastname", "ra.id"];

        if ($appcnt_data['call_for'] == 'for_group_cab') {
            $select_ary[] = "interview_det.device_pin";
        }
        $this->db->select($select_ary);
        $this->db->from("tbl_recruitment_task as rt");
        $this->db->join("tbl_recruitment_task_applicant as rta", "rta.taskId = rt.id AND rta.archive = 0", "INNER");
        $this->db->join("tbl_recruitment_location as rl", "rt.training_location = rl.id", "INNER");
        if ($appcnt_data['call_for'] == 'for_group_cab') {
            $this->db->join("tbl_recruitment_applicant_group_or_cab_interview_detail as interview_det", "interview_det.recruitment_task_applicant_id = rta.id AND interview_det.archive = 0", "INNER");
        }
        $this->db->join("tbl_recruitment_applicant as ra", "ra.id = rta.applicant_id AND rta.archive = 0", "INNER");
        $this->db->join("tbl_recruitment_applicant_email as rae", "rae.applicant_id = ra.id AND rae.archive = 0 AND rae.primary_email = 1", "INNER");
        $this->db->where("rta.applicant_id", $appcnt_data['applicant_id']);
        $this->db->where("rt.id", $appcnt_data['taskId']);
        $this->db->where("rt.status", 1);

        $query = $this->db->get();
        $result = $query->row_array();

        $admin_d = $this->get_admin_firstname_lastname($appcnt_data['adminId']);

        $appcnt_data['admin_firstname'] = $admin_d['firstname'] ?? '';
        $appcnt_data['admin_lastname'] = $admin_d['lastname'] ?? '';

        $appcnt_data['firstname'] = $result['firstname'] ?? '';
        $appcnt_data['lastname'] = $result['lastname'] ?? '';
        $appcnt_data['email'] = $result['email'] ?? '';
        $appcnt_data['device_pin'] = $result['device_pin'] ?? '';
        $appcnt_data['task_location'] = $result['task_location'];
        $appcnt_data['task_start_time'] = DateFormate($result['start_datetime'], "d/m/Y h:i a");
        $appcnt_data['task_end_time'] = DateFormate($result['end_datetime'], "d/m/Y h:i a");

        if (isset($appcnt_data['application_id']) && !empty($appcnt_data['application_id'])) {
            $appcnt_data['job_title'] = $this->get_application_job_title($appcnt_data['application_id']); 
        } else {
            // @deprecated
            $appcnt_data['job_title'] = $this->get_applicant_job_title($appcnt_data['applicant_id']); 
        }

       // $appcnt_data['call_for'] = $appcnt_data['call_for'];
       $appcnt_data['call_for'] = '';


        $token = encrypt_decrypt('encrypt', rand(1111, 9999999));
        $appcnt_data['url'] = $this->config->item('server_url') . 'task_confirmation/' . encrypt_decrypt('encrypt', json_encode(['date_time' => DATE_TIME, 'token' => $token]));

        require_once APPPATH . 'Classes/Automatic_email.php';
        $obj = new Automatic_email();

        $appcnt_data['function_content'] = $obj->send_invitation_task_function_part($appcnt_data);

        $obj->setEmail_key($email_name_kay);
        $obj->setEmail($appcnt_data['email']);
        $obj->setDynamic_data($appcnt_data);
        $obj->setUserId($appcnt_data['applicant_id']);
        $obj->setUser_type(1);

        $obj->automatic_email_send_to_user();

        //$status = send_invitation_mail_to_applicant_for_task($appcnt_data);

        $where_u = array('taskId' => $appcnt_data['taskId'], 'applicant_id' => $appcnt_data['applicant_id']);
        $this->basic_model->update_records('recruitment_task_applicant', ['token_email' => $token], $where_u);
    }

    function add_update_task_recruiter($assign_recruiter, $taskId)
    {
        if (!empty($assign_recruiter)) {
            $pre_task_recruiter = $this->basic_model->get_record_where('recruitment_task_recruiter_assign', ['id'], $where = ['taskId' => $taskId]);
            $pre_task_recruiter = array_column(obj_to_arr($pre_task_recruiter), 'id');

            $assign_user = array();
            foreach ($assign_recruiter as $val) {
                $prim_sec = (!empty($val->primary_recruiter) && $val->primary_recruiter === '1') ? 1 : 2;

                if (!empty($val->id)) {
                    $key = array_search($val->id, $pre_task_recruiter);

                    $this->basic_model->update_records('recruitment_task_recruiter_assign', ['primary_recruiter' => $prim_sec], ['id' => $val->id]);
                    unset($pre_task_recruiter[$key]);
                } else {

                    $assign_user[] = array(
                        'recruiterId' => $val->value,
                        'taskId' => $taskId,
                        'primary_recruiter ' => $prim_sec,
                        'created' => DATE_TIME,
                        'archive' => 0,
                    );
                }
            }

            if (!empty($assign_user)) {
                $this->basic_model->insert_records('recruitment_task_recruiter_assign', $assign_user, true);
            }

            if (!empty($pre_task_recruiter)) {
                foreach ($pre_task_recruiter as $id) {
                    $this->basic_model->update_records('recruitment_task_recruiter_assign', ['archive' => 1], ['id' => $id]);
                }
            }
        }
    }

    function verify_task_confirmation_token($reqData)
    {
        $decrypt_data = encrypt_decrypt('decrypt', $reqData->token);
        $return = [];

        if (is_json($decrypt_data)) {
            $x = json_decode($decrypt_data);

            $where_ch = array('token_email' => $x->token, 'archive' => 0);
            $this->db->select(['rta.id', 'taskId', 'applicant_id', 'rta.status', 'rt.start_datetime', 'rt.task_stage', 'rta.application_id']);
            $this->db->from('tbl_recruitment_task as rt');
            $this->db->join('tbl_recruitment_task_applicant as rta', 'rta.taskId = rt.id AND rta.status = 0');
            $this->db->where(['rta.token_email' => $x->token, 'rta.archive' => 0]);
            $this->db->where('rt.status', 1);

            $query = $this->db->get();
            $response = $query->row();

            if (!empty($response)) {

                if ($response->status == 0 && strtotime($response->start_datetime) > strtotime(DATE_TIME)) {

                    if ($reqData->action === 'a') {
                        // first mark as token invitation acceprt and blank the token email field
                        $this->basic_model->update_records('recruitment_task_applicant', ['status' => 1, 'token_email' => '', 'invitation_accepted_at' => DATE_TIME], $where_ch);
                        $invitation_sent_applicant = [];
                        $stageKeyData = ['3' => 'group_applicant_responses', '9' => 'individual_applicant_responses'];
                        $task_stage = $response->task_stage ?? 0;
                        $stageKey =  $stageKeyData[$task_stage] ?? '';
                        if (!empty($stageKey)) {

                            if (isset($response->application_id) && !empty($response->application_id)) {
                                $invitation_sent_applicant[$stageKey][] = ['task_stage' => $task_stage,'applicant_id' => $response->applicant_id, 'application_id' => $response->application_id];
                            } else {
                                $invitation_sent_applicant[$stageKey][] = ['task_stage' => $task_stage,'applicant_id' => $response->applicant_id];
                            }

                            $this->stage_update_invitation_sent_applicant($invitation_sent_applicant, 0);
                        }
                    } elseif ($reqData->action === 'c') {

                        $this->basic_model->update_records('recruitment_task_applicant', ['status' => 2, 'token_email' => '', 'invitation_cancel_at' => DATE_TIME], $where_ch);
                    }

                    $return = array('status' => true);
                } else {
                    $return = array('status' => false, 'error' => 'Link has expired');
                }
            } else {
                $return = array('status' => false, 'error' => 'Invalid Request');
            }
        } else {
            $return = array('status' => false, 'error' => 'Invalid Request');
        }

        return $return;
    }

    function get_applicant_name_appid_email($applicantIds)
    {
        $this->db->select(["CONCAT_WS(' ',firstname,lastname) as fullname", "ra.appId", "rae.email", "ra.id", "firstname", "lastname"]);
        $this->db->from("tbl_recruitment_applicant as ra");
        $this->db->join("tbl_recruitment_applicant_email as rae", "rae.applicant_id = ra.id AND rae.archive = 0 AND rae.primary_email = 1", "INNER");
        $this->db->where_in("ra.id", $applicantIds);

        $query = $this->db->get();
        $result = $query->result_array();

        return pos_index_change_array_data($result, 'id');
    }

    function resend_task_mail_to_applicant($reqData, $adminId, $call_for)
    {
        $select_ary = ["rt.task_stage"];
        $this->db->select($select_ary);
        $this->db->from("tbl_recruitment_task as rt");
        $this->db->where("rt.id", $reqData->taskId);
        $this->db->where("rt.status", 1);
        $query = $this->db->get();
        $result = $query->row_array();

        $reqData->adminId = $adminId;
        $reqData->call_for = $call_for;

        // on basis of send email_name_key
        $email_name_key = $this->get_email_name_key_for_task($result['task_stage'], 're_invite');

        $this->send_task_mail_to_applicant((array) $reqData, $email_name_key);

        return true;
    }

    function get_available_group_or_cab_interview_for_applicant($task_stage, $extra_param = [])
    {
        $colowmn = array('rt.id as taskId', 'rt.task_name', 'rl.name as training_location', 'rt.start_datetime', "DATE_FORMAT(rt.end_datetime,'%h:%i %p') as end_time", "DATE_FORMAT(rt.start_datetime,'%h:%i %p') as start_time", 'rt.status', "rt.max_applicant");

        $this->db->select($colowmn);
        $this->db->select("(select concat_ws(' ',m.firstname, lastname) from tbl_recruitment_task_recruiter_assign as rtra
        INNER JOIN tbl_member as m on m.id = rtra.recruiterId 
        INNER JOIN tbl_recruitment_staff as staff on m.id = staff.adminId AND staff.status = 1 
        where rtra.taskId = rt.id AND rtra.archive = 0 and primary_recruiter = 1 ) as primary_recruiter", false);

        $this->db->select("(SELECT COUNT(IF(status = 0, 1, NULL)) 'pending' FROM tbl_recruitment_task_applicant where taskId = rt.id AND archive = 0) as pending");
        $this->db->select("(SELECT COUNT(IF(status = 1, 1, NULL)) 'accepted' FROM tbl_recruitment_task_applicant where taskId = rt.id AND archive = 0) as accepted");
        $this->db->select("(SELECT (rt.max_applicant - COUNT(id)) 'accepted' FROM tbl_recruitment_task_applicant where taskId = rt.id AND archive = 0 AND status IN (0,1)) as available");

        $this->db->from('tbl_recruitment_task as rt');
        $this->db->from('tbl_recruitment_location as rl', 'rl.id = rt.training_location', 'INNER');
        $this->db->where('rt.task_stage', $task_stage);
        $this->db->where('rt.status', 1);
        $this->db->where("rt.start_datetime >=", DATE_TIME);

        if (!empty($extra_param['applicant_id'])) {
            $this->db->where('NOT EXISTS (SELECT sb_rta.taskId FROM tbl_recruitment_task_applicant as sb_rta WHERE sb_rta.taskId = rt.id AND sb_rta.archive = 0 AND sb_rta.applicant_id = "' . $extra_param['applicant_id'] . '")');
        }

        if (!empty($extra_param['taskId'])) {
            $this->db->where('rt.id', $extra_param['taskId']);
        }

        $this->db->order_by('rt.start_datetime', 'asc');
        $this->db->limit(10);
        $this->db->having('available > 0');
        $this->db->group_by('rt.id');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        //        last_query();
        return $result;
    }

    function check_applicant_already_exist_in_interview($applicant_id, $task_stage)
    {
        $this->db->select(["rt.id", "rta.status as approve_deny_status", "interview_det.applicant_status as task_applicant_status", "interview_det.quiz_status as quiz_status", "interview_det.app_orientation_status", "interview_det.app_login_status"]);
        $this->db->from('tbl_recruitment_task as rt');
        $this->db->join('tbl_recruitment_task_applicant as rta', 'rta.taskId = rt.id AND rta.archive = 0 AND rta.status IN (0,1) AND rta.applicant_id = ' . $this->db->escape_str($applicant_id, true), 'inner');
        $this->db->join('tbl_recruitment_applicant_group_or_cab_interview_detail as interview_det', 'rta.id = interview_det.recruitment_task_applicant_id AND interview_det.archive = 0 AND interview_det.mark_as_no_show = 0', 'inner');

        $this->db->where('rt.task_stage', $task_stage);
        $this->db->where_in('rt.status', [1, 2]);

        $this->db->group_by('rt.id');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->row();

        return $result;
    }

    function check_applicant_exist_in_any_inprogress_task()
    {
    }

    function check_applicant_docusign_signed($applicant_id, $task_stage)
    {
        $this->db->select(["rt.id", "tdap.signed_status"]);
        $this->db->from('tbl_recruitment_task as rt');
        $this->db->join('tbl_recruitment_task_applicant as rta', 'rta.taskId = rt.id AND rta.archive = 0 AND rta.status IN (0,1) AND rta.applicant_id = ' . $this->db->escape_str($applicant_id, true), 'inner');
        $this->db->join('tbl_document_attachment as tda','tda.task_applicant_id = rta.id AND tda.archive = 0', 'inner');
        $this->db->join('tbl_document_attachment_property as tdap', 'tda.id = tdap.doc_id', 'inner');

        $this->db->where('rt.task_stage', $task_stage);
        $this->db->where('rt.status', 1);

        $this->db->group_by('rt.id');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->row();

        return $result;
    }

    function get_email_name_key_for_task($task_stage, $event_type)
    {
        if ($event_type === 'create_new_task') {
            $email_name_key = ($task_stage == 3) ? 'group_interview_schedule' : ((in_array($task_stage, array(9,10,11))) ? 'individual_interview_schedule' : 'cab_day_schedule');
        } elseif ($event_type === 'reschedule_task') {

            $email_name_key = ($task_stage == 3) ? 'group_interview_reschedule' : 'cab_day_reschedule';
        } else { // re-invite task

            $email_name_key = ($task_stage == 3) ? 'group_interview_re_invite' : (($task_stage == 9) ? 'individual_interview_re_invite' : 'cab_day_re_invite');
        }

        return $email_name_key;
    }

    function add_applicant_in_available_interview($reqData, $adminId)
    {
        // this function handle multiple applicant so we are send applicant id as multiple
        $applicant_list = [];

        // on basis of send email_name_key
        $email_name_key = $this->get_email_name_key_for_task($reqData->task_stage, $reqData->request_type);

        $applicant_list[] = (object) ['value' => $reqData->applicant_id];
        $this->add_update_task_applicant($applicant_list, $reqData->taskId, $reqData->task_stage, $adminId, $email_name_key);

        return true;
    }

    function check_task_eligibility_for_archive($taskId, $adminId)
    {
        $its_recruiter_admin = check_its_recruiter_admin($adminId);

        $this->db->select(['rt.created_by', 'rt.start_datetime', 'rt.commit_status', 'rts.key']);
        $this->db->from('tbl_recruitment_task as rt');
        $this->db->join('tbl_recruitment_task_stage as rts', 'rts.id = rt.task_stage AND rts.archive = 0', 'inner');

        $this->db->where('rt.id', $taskId);
        $this->db->where('rt.status', 1);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $response = $query->row();


        if (!empty($response)) {
            if (!$its_recruiter_admin && $response->created_by != $adminId) {

                $return = ['status' => false, 'error' => 'Sorry you do not have permission to complete this task.'];
            } elseif (strtotime($response->start_datetime) < strtotime(DATE_TIME) && ($response->key == 'group_interview' || $response->key == 'cab_day')) {

                $return = ['status' => false, 'error' => 'Task has started, It can not be archived.'];
            } else {
                $return = ['status' => true];
            }
        } else {
            $return = ['status' => false, 'error' => 'Task not found as in-progress'];
        }

        return $return;
    }

    public function check_applicant_task_allocated_check_assign_requiter($adminId = 0, $taskIdOrCabdayDetailId = 0, $type = 'taskId')
    {
        $this->db->select('1', false);
        $this->db->from('tbl_recruitment_applicant_group_or_cab_interview_detail sub_ragocid');
        $this->db->join('tbl_recruitment_task_applicant sub_rta', "sub_rta.id=sub_ragocid.recruitment_task_applicant_id AND sub_ragocid.archive=sub_rta.archive ", "inner");
        $this->db->join('tbl_recruitment_task_recruiter_assign sub_rtra', "sub_rtra.taskId=sub_rta.taskId AND sub_rtra.archive=sub_ragocid.archive", "inner");
        $this->db->join('tbl_recruitment_staff sub_rs', "sub_rs.adminId=sub_rtra.recruiterId and sub_rs.status=1 AND sub_rs.archive=sub_ragocid.archive", "inner");
        if ($type == 'taskId') {
            $this->db->where("sub_rta.taskId", $taskIdOrCabdayDetailId);
            $this->db->group_by("sub_rta.taskId");
        } elseif ($type == 'detailId') {
            $this->db->where("sub_ragocid.id", $taskIdOrCabdayDetailId);
            $this->db->group_by("sub_ragocid.id");
        } elseif ($type == 'taskApplicantId') {
            $this->db->where("sub_rta.id", $taskIdOrCabdayDetailId);
            $this->db->group_by("sub_rta.id");
        }
        $this->db->limit(1);

        $this->db->where("sub_rtra.recruiterId =rs.adminId and sub_ragocid.archive=rs.archive", null, false);
        $sub_query = $this->db->get_compiled_select();
        $this->db->select([
            "CASE WHEN its_recruitment_admin=1 THEN '1' WHEN its_recruitment_admin=0 THEN COALESCE( (" . $sub_query . "),0) ELSE 0 END as permission_status"
        ]);
        $this->db->from("tbl_recruitment_staff rs");
        $this->db->where("rs.adminId", $adminId);
        $this->db->where("rs.archive", 0);
        $query = $this->db->get();
        $status = $query->num_rows() > 0 ? $query->row()->permission_status : 0;
        return $status;
    }

    public function task_mark_as_decline_to_applicant_by_recuirter(int $taskId, int $applicantId, int $adminId)
    {
        $where_ch = ['applicant_id' => $applicantId, 'taskId' => $taskId, 'status' => 0];
        $res = $this->basic_model->update_records('recruitment_task_applicant', ['status' => 2, 'is_decline_mark_by_recruiter_user' => $adminId, 'token_email' => '', 'invitation_cancel_at' => DATE_TIME], $where_ch);
        if ($res) {
            $return = array('status' => true);
        } else {
            $return = array('status' => false, 'error' => 'Invalid Request please refresh page and try again.');
        }
        return $return;
    }
	
function get_create_task_form_option($reqestData){
    $this->db->select(["rf.title as label", "rf.id as value"]);
    $this->db->from("tbl_recruitment_form as rf");
    $this->db->join("tbl_recruitment_interview_type as rit", "rit.id = rf.interview_type AND rit.archive = 0", "INNER");
    $this->db->where("rf.archive", 0);
    if(!empty($reqestData)){
        $type = $reqestData->data->form_type;
        $this->db->where("rit.key_type", $type);
    }    
    return $this->db->get()->result();
}
    public function stage_update_invitation_sent_applicant($stageUpdateData=[],$recruiterId=0){
        if(!empty($stageUpdateData)){
            $this->load->model('Recruitment_applicant_model');
            $stageKeys = array_keys($stageUpdateData);
            $stageKeyRes = $this->get_stage_id_by_stage_keys($stageKeys);
            $stageKeyRes = !empty($stageKeyRes) ? pos_index_change_array_data($stageKeyRes, 'stage_key') : [];

            if (!empty($stageKeyRes)) {
                foreach ($stageUpdateData as $key => $rowsData) {
                    $stageId = $stageKeyRes[$key]['stage_id'] ?? 0;
                    if (!empty($rowsData) && !empty($stageId)) {
                        foreach ($rowsData as $row) {

                            $reqDataTo = ['status' => 3, 'applicant_id' => $row['applicant_id'], 'stageId' => $stageId, 'stage_key' => $key];

                            if (isset($row['application_id']) && !empty($row['application_id'])) {
                                $reqDataTo['application_id'] = $row['application_id'];
                            }

                            $this->Recruitment_applicant_model->update_applicant_stage_status((object) $reqDataTo, $recruiterId);
                            $this->update_stage_log((object) $reqDataTo, $recruiterId);
                        }
                    }
                }
            }
        }
    }

    public function get_stage_id_by_stage_keys($stageKeys)
    {
        $stageKeys = !empty($stageKeys) ? $stageKeys : '0';
        $stageKeys = is_array($stageKeys) ? $stageKeys : [$stageKeys];

        $this->db->select(['stage_key', 'id as stage_id']);
        $this->db->where_in('stage_key', $stageKeys);
        $this->db->from(TBL_PREFIX . 'recruitment_stage');
        $this->db->where('archive', 0);
        $this->db->order_by('id', 'ASC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    public function update_stage_log($reqData, $adminId)
    {
        $this->load->library('UserName');
        $this->loges->setLogType('recruitment_applicant');
        $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
        $stage_label = $this->username->getName('stage_label', $reqData->stageId);
        $this->loges->setCreatedBy($adminId);
        $this->loges->setUserId($reqData->applicant_id);
        $this->loges->setDescription(json_encode($reqData));
        $txt = ($reqData->status == 3) ? 'Completed' : 'Unsuccessfull';
        $this->loges->setTitle('Applicant - ' . $applicantName . ' ' . $stage_label . ' ' . $txt);  // set title in log
        $this->loges->setSpecific_title($stage_label . ' ' . $txt);
        $this->loges->createLog();
    }

    function get_applicant_job_title($applicantId, $application_id = 0)
    {
        $this->db->select("rjp.title");
        $this->db->from("tbl_recruitment_applicant_applied_application as applcntn");
        $this->db->join("tbl_recruitment_job_position as rjp", "rjp.id = applcntn.position_applied", "INNER");
        $this->db->where("applcntn.applicant_id", $applicantId);
        if($application_id)
        $this->db->where("applcntn.id", $application_id);
        $this->db->where("applcntn.archive", 0);

        return $this->db->get()->row("title");
    }

    /**
     * Determine title of the job the application ID was attached to
     * 
     * @param int $application_id 
     * @return string|null 
     */
    public function get_application_job_title($application_id)
    {
        $query = $this->db
            ->from('tbl_recruitment_applicant_applied_application AS application')
            ->join('tbl_recruitment_job AS job', 'job.id = application.jobId', 'INNER')
            ->where([
                'application.id' => $application_id,
                'application.archive' => 0,
            ])
            ->select([
                'job.title AS title'
            ])
            ->get();
        
        $result = $query->row();

        return $result->title ?? null;
    }


    function get_admin_firstname_lastname($adminId)
    {
        $this->db->select(["m.firstname", "m.lastname"]);
        $this->db->from("tbl_member as m");
        $this->db->where("m.id", $adminId);
        $this->db->where("m.archive", 0);

        return $this->db->get()->row_array();
    }
}
