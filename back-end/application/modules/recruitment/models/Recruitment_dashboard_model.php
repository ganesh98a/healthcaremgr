<?php

class Recruitment_dashboard_model extends CI_Model {

    public function get_task_list_dashboard($reqData) {
        $its_recruiter_admin = check_its_recruiter_admin($reqData->adminId);
        //pr($reqData->data->currentState->personal_view);
        $tbl_recruitment_task = TBL_PREFIX . 'recruitment_task';
        $tbl_recruiter = TBL_PREFIX . 'member';
        $tbl_recruitment_task_recruiter_assign = TBL_PREFIX . 'recruitment_task_recruiter_assign';
        $tbl_recruitment_task_applicant = TBL_PREFIX . 'recruitment_task_applicant';
        $tbl_recruitment_applicant = TBL_PREFIX . 'recruitment_applicant';
        $orderBy = $tbl_recruitment_task . '.start_datetime';
        $direction = 'ASC';



        //$select_column = array($tbl_recruitment_task . ".id", "CONCAT(" . $tbl_recruiter . ".firstname,' '," . $tbl_recruiter . ".lastname) AS recruiter_name", $tbl_recruitment_task . ".task_name", $tbl_recruitment_task . ".status as task_status", $tbl_recruitment_task . ".start_datetime", $tbl_recruitment_task_applicant . ".status", "CONCAT(" . $tbl_recruitment_applicant . ".firstname,' '," . $tbl_recruitment_applicant . ".middlename,' '," . $tbl_recruitment_applicant . ".lastname) AS applicant_name");
        $select_column = array(
            $tbl_recruitment_task . ".id", $tbl_recruitment_task . ".task_name", $tbl_recruitment_task . ".status as task_status", $tbl_recruitment_task . ".start_datetime", $tbl_recruitment_task_applicant . ".status",
            /* "CONCAT(" . $tbl_recruitment_applicant . ".firstname,' '," . $tbl_recruitment_applicant . ".middlename,' '," . $tbl_recruitment_applicant . ".lastname) AS applicant_name", */
            "GROUP_CONCAT(distinct CONCAT(" . $tbl_recruitment_applicant . ".id,'@__BREAKER__@ '," . $tbl_recruitment_applicant . ".firstname, ' ', " . $tbl_recruitment_applicant . ".middlename, ' ', " . $tbl_recruitment_applicant . ".lastname) SEPARATOR ', ') AS applicant"
        );
        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        // primary recruiter name get
        $this->db->select("CASE WHEN " . $tbl_recruitment_task_recruiter_assign . ".primary_recruiter='2' THEN (select concat_ws(' ',sub_a.firstname, sub_a.lastname) 
            from tbl_recruitment_task_recruiter_assign as sub_rtra
            inner join tbl_member as sub_a on sub_a.id = sub_rtra.recruiterId 
            where sub_rtra.taskId = " . $tbl_recruitment_task . ".id AND sub_rtra.primary_recruiter = 1 and sub_rtra.archive=0) else concat_ws(' '," . $tbl_recruiter . ".firstname," . $tbl_recruiter . ".lastname) end as recruiter_name");
        $this->db->from($tbl_recruitment_task);
        $this->db->where($tbl_recruitment_task . '.status', '1');

        if (isset($reqData->data->acess_type) && $reqData->data->acess_type == 1 && $its_recruiter_admin) {
            //admin 
            if (isset($reqData->data->currentState->personal_view) && $reqData->data->currentState->personal_view == 1) {
                $this->db->where($tbl_recruitment_task . ".created_by", $reqData->adminId);
            }
        } else {
            $this->db->where($tbl_recruitment_task_recruiter_assign . ".recruiterId", $reqData->adminId);
        }

        $next_date = date('Y-m-d', strtotime(date('Y-m-d') . ' + 5 days'));

        $this->db->where("date(tbl_recruitment_task.start_datetime) >=", date('Y-m-d'));
        $this->db->where("date(tbl_recruitment_task.start_datetime) <=", $next_date);


        $this->db->join($tbl_recruitment_task_recruiter_assign, $tbl_recruitment_task_recruiter_assign . '.taskId = ' . $tbl_recruitment_task . '.id AND ' . $tbl_recruitment_task . '.status = 1 AND ' . $tbl_recruitment_task_recruiter_assign . '.archive =0', 'inner');
        $this->db->join($tbl_recruiter, $tbl_recruiter . '.id = ' . $tbl_recruitment_task_recruiter_assign . '.recruiterId AND ' . $tbl_recruiter . '.archive = 0 ', 'inner');
        $this->db->join($tbl_recruitment_task_applicant, $tbl_recruitment_task_applicant . '.taskId = ' . $tbl_recruitment_task . '.id AND ' . $tbl_recruitment_task_applicant . '.archive =0', 'inner');
        $this->db->join($tbl_recruitment_applicant, $tbl_recruitment_applicant . '.id = ' . $tbl_recruitment_task_applicant . '.applicant_id AND ' . $tbl_recruitment_applicant . '.archive = 0 ', 'inner');
        $this->db->group_by($tbl_recruitment_task . ".id");
        $this->db->order_by($orderBy, $direction);
        $this->db->limit(10);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        //last_query(1); 
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;



        #echo isset($x)?'444': (isset($y)?'herere':'999');
        $dataResult = $query->result();
        #pr($dataResult);
        $data = [];
        $status = true;
        if (!empty($dataResult)) {
            $status = true;
            foreach ($dataResult as $val) {
                $val->recruiter = $val->recruiter_name;
                $val->task = $val->task_name;
                $val->task_status = (isset($val->task_status) && $val->task_status == 1 ? 'In Progress' : (($val->task_status == 2) ? 'Completed' : (($val->task_status == 3) ? 'Cancelled' : (($val->task_status == 4) ? 'Archived' : 'N/A'))));

                $val->date = isset($val->start_datetime) && !empty($val->start_datetime) ? date('d/m/Y', strtotime($val->start_datetime)) : 'N/A';
                $pattern = "/(\d+@__BREAKER__@) (\w+)/i";
                $replacement = '${2}';
                $val->applicant = preg_replace($pattern, $replacement, $val->applicant);
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count, 'status' => $status);
        return $return;
    }

    public function get_latest_action($reqData) {
        //pr($reqData->data->currentState->personal_view);
        $tbl_recruitment_task = TBL_PREFIX . 'recruitment_task';
        $tbl_recruiter = TBL_PREFIX . 'member';
        $tbl_recruitment_task_recruiter_assign = TBL_PREFIX . 'recruitment_task_recruiter_assign';
        $tbl_recruitment_task_applicant = TBL_PREFIX . 'recruitment_task_applicant';
        $tbl_recruitment_applicant = TBL_PREFIX . 'recruitment_applicant';
        $direction = 'DESC';
        $orderBy = $tbl_recruitment_task . '.action_at ' . $direction . ' ,' . $tbl_recruitment_task . '.created ' . $direction . ' ,' . $tbl_recruitment_task . '.id ' . $direction;



        $select_column = array(
            $tbl_recruitment_task . ".id",
            /* "CONCAT(" . $tbl_recruiter . ".firstname,' '," . $tbl_recruiter . ".lastname) AS recruiter_name", */
            $tbl_recruitment_task . ".task_name", $tbl_recruitment_task . ".status as task_status", $tbl_recruitment_task . ".start_datetime", $tbl_recruitment_task_applicant . ".status",
            /*  "CONCAT(" . $tbl_recruitment_applicant . ".firstname,' '," . $tbl_recruitment_applicant . ".middlename,' '," . $tbl_recruitment_applicant . ".lastname) AS applicant_name" */
            "GROUP_CONCAT(distinct CONCAT(" . $tbl_recruitment_applicant . ".id,'@__BREAKER__@ '," . $tbl_recruitment_applicant . ".firstname, ' ', " . $tbl_recruitment_applicant . ".middlename, ' ', " . $tbl_recruitment_applicant . ".lastname) SEPARATOR ', ') AS applicant"
        );
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("CASE WHEN " . $tbl_recruitment_task_recruiter_assign . ".primary_recruiter='2' THEN (select concat_ws(' ',sub_a.firstname, sub_a.lastname) 
            from tbl_recruitment_task_recruiter_assign as sub_rtra
            inner join tbl_member as sub_a on sub_a.id = sub_rtra.recruiterId 
            where sub_rtra.taskId = " . $tbl_recruitment_task . ".id AND sub_rtra.primary_recruiter = 1 and sub_rtra.archive=0) else concat_ws(' '," . $tbl_recruiter . ".firstname," . $tbl_recruiter . ".lastname) end as recruiter_name", false);
        $this->db->from($tbl_recruitment_task);
        #$this->db->where('archive=','0');

        if (isset($reqData->data->acess_type) && $reqData->data->acess_type == 1) {
            //admin 
            if (isset($reqData->data->currentState->personal_view) && $reqData->data->currentState->personal_view == 1) {
                $this->db->where($tbl_recruitment_task_recruiter_assign . ".recruiterId", $reqData->adminId);
            }
        } else {
            $this->db->where($tbl_recruitment_task_recruiter_assign . ".recruiterId", $reqData->adminId);
        }

        $previous_date = date(DB_DATE_FORMAT, strtotime(DATE_CURRENT . ' - 5 days'));

        /*  $this->db->where("date(tbl_recruitment_task.start_datetime) >=", $previous_date);
          $this->db->where("date(tbl_recruitment_task.start_datetime) <=", DATE_CURRENT); */

        $this->db->join($tbl_recruitment_task_recruiter_assign, $tbl_recruitment_task_recruiter_assign . '.taskId = ' . $tbl_recruitment_task . '.id AND ' . $tbl_recruitment_task_recruiter_assign . '.archive =0 AND ((tbl_recruitment_task.status in (\'2\',\'4\') and date(tbl_recruitment_task.action_at) between \'' . $previous_date . '\' and \'' . DATE_CURRENT . '\') or (tbl_recruitment_task.status in (\'1\') and date(tbl_recruitment_task.created) between \'' . $previous_date . '\' and \'' . DATE_CURRENT . '\'))', 'inner');
        $this->db->join($tbl_recruiter, $tbl_recruiter . '.id = ' . $tbl_recruitment_task_recruiter_assign . '.recruiterId AND ' . $tbl_recruiter . '.archive = 0 ', 'inner');
        $this->db->join($tbl_recruitment_task_applicant, $tbl_recruitment_task_applicant . '.taskId = ' . $tbl_recruitment_task . '.id AND ' . $tbl_recruitment_task_applicant . '.archive =0', 'inner');
        $this->db->join($tbl_recruitment_applicant, $tbl_recruitment_applicant . '.id = ' . $tbl_recruitment_task_applicant . '.applicant_id AND ' . $tbl_recruitment_applicant . '.archive = 0 ', 'inner');
        $this->db->group_by($tbl_recruitment_task . ".id");
        $this->db->limit(10);
        $this->db->order_by($orderBy);
        #$this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        //last_query(1); 
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;



        #echo isset($x)?'444': (isset($y)?'herere':'999');
        $dataResult = $query->result();
        #pr($dataResult);
        $data = [];
        $status = false;
        if (!empty($dataResult)) {
            $status = true;
            foreach ($dataResult as $val) {
                $val->recruiter = $val->recruiter_name;
                $val->task = $val->task_name;
                $val->task_status = (isset($val->task_status) && $val->task_status == 1 ? 'In Progress' : (($val->task_status == 2) ? 'Completed' : (($val->task_status == 3) ? 'Cancelled' : (($val->task_status == 4) ? 'Archived' : 'N/A'))));

                $val->date = isset($val->start_datetime) && !empty($val->start_datetime) ? date('d/m/Y', strtotime($val->start_datetime)) : 'N/A';
                $pattern = "/(\d+@__BREAKER__@) (\w+)/i";
                $replacement = '${2}';
                $val->applicant = preg_replace($pattern, $replacement, $val->applicant);
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count, 'status' => $status);
        return $return;
    }

    public function get_new_assigned_applicant($reqData) {
        //pr($reqData->data->currentState->personal_view);
        $tbl_recruitment_applicant = TBL_PREFIX . 'recruitment_applicant';
        $tbl_recruitment_channel = TBL_PREFIX . 'recruitment_channel';
        $tbl_recruitment_applicant_applied_application = TBL_PREFIX . 'recruitment_applicant_applied_application';
        $tbl_recruitment_department = TBL_PREFIX . 'recruitment_department';
        $tbl_recruiter = TBL_PREFIX . 'member';
        $tbl_recruiter_staff = TBL_PREFIX . 'recruitment_staff';

        $direction = 'DESC';
        $orderByWithDirection = $tbl_recruitment_applicant . '.date_applide ' . $direction . ' , ' . $tbl_recruitment_applicant . '.id ' . $direction;

        $select_column = array($tbl_recruitment_applicant . ".id", $tbl_recruitment_applicant . ".date_applide", "GROUP_CONCAT( DISTINCT " . $tbl_recruitment_channel . ".channel_name) as channel_name", "GROUP_CONCAT( DISTINCT " . $tbl_recruitment_department . ".name) as recruitment", "CONCAT(" . $tbl_recruitment_applicant . ".firstname,' '," . $tbl_recruitment_applicant . ".middlename,' '," . $tbl_recruitment_applicant . ".lastname) AS applicant_name");
        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from($tbl_recruitment_applicant);
        $this->db->join($tbl_recruitment_applicant_applied_application, $tbl_recruitment_applicant . '.id = ' . $tbl_recruitment_applicant_applied_application . '.applicant_id AND ' . $tbl_recruitment_applicant_applied_application . '.archive = ' . $tbl_recruitment_applicant . '.archive AND ' . $tbl_recruitment_applicant . '.archive = 0', 'inner');
        $this->db->join($tbl_recruiter_staff, $tbl_recruiter_staff . '.adminId = ' . $tbl_recruitment_applicant . '.recruiter AND ' . $tbl_recruiter_staff . '.archive = 0 AND ' . $tbl_recruiter_staff . '.status = 1', 'inner');
        $this->db->join($tbl_recruiter, $tbl_recruiter . '.id = ' . $tbl_recruiter_staff . '.adminId AND ' . $tbl_recruiter . '.archive = 0 ', 'inner');
        $this->db->join($tbl_recruitment_department, $tbl_recruitment_department . '.id = ' . $tbl_recruitment_applicant_applied_application . '.recruitment_area AND ' . $tbl_recruitment_department . '.archive = 0 ', 'inner');
        $this->db->join($tbl_recruitment_channel, $tbl_recruitment_channel . '.id = ' . $tbl_recruitment_applicant_applied_application . '.channelId AND ' . $tbl_recruitment_channel . '.archive = 0 ', 'inner');

        $this->db->where($tbl_recruitment_applicant . ".recruiter", $reqData->adminId);
        $this->db->group_by($tbl_recruitment_applicant . ".id");
        $this->db->order_by($orderByWithDirection);
        $this->db->limit(10);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        //last_query(1); 
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        $dataResult = $query->result();
        #pr($dataResult);
        $data = [];
        $status = false;
        if (!empty($dataResult)) {
            $status = true;
            foreach ($dataResult as $val) {
                $val->channel_name = $val->channel_name;
                $val->date_applide = isset($val->date_applide) && !empty($val->date_applide) ? date('d/m/Y', strtotime($val->date_applide)) : 'N/A';
                $val->applicant_name = $val->applicant_name;
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count, 'status' => $status);
        return $return;
    }

    public function pay_scale_approval_work_area_options() {
        $tbl_rec_app_work_area = TBL_PREFIX . 'recruitment_applicant_work_area';
        $tbl_classification_level = TBL_PREFIX . 'classification_level';
        $tbl_classification_point = TBL_PREFIX . 'classification_point';

        $work_area_option = array();

        $coulumns_work_area = array($tbl_rec_app_work_area . '.id as value', $tbl_rec_app_work_area . '.work_area as label');
        $this->db->select($coulumns_work_area);
        $this->db->from($tbl_rec_app_work_area);
        $query_work_area = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $work_area_option['work_area'] = $query_work_area->result();

        $coulumns_point = array($tbl_classification_point . '.id as value', $tbl_classification_point . '.short_title as label');
        $this->db->select($coulumns_point);
        $this->db->from($tbl_classification_point);
        $query_point = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $work_area_option['pay_point'] = $query_point->result();


        $coulumns_level = array($tbl_classification_level . '.id as value', $tbl_classification_level . '.short_title as label');
        $this->db->select($coulumns_level);
        $this->db->from($tbl_classification_level);
        $query_level = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $work_area_option['pay_level'] = $query_level->result();

        return $work_area_option;
    }

    public function update_pay_scale_approval_applicant_work_area($data) {
        $id = $data->id;
        $work_area_id = $data->work_area_id;
        $point_id = $data->point_id;
        $level_id = $data->level_id;
        $update_data = array('work_area' => $work_area_id, 'pay_point' => $point_id, 'pay_level' => $level_id);
        $where_id = array('id' => $id);
        $this->basic_model->update_records('recruitment_applicant_pay_point_options', $update_data, $where_id);
        return $return = array('status' => true);
    }

    /*This method is called from applicant umberlla stage,
    * now not in used,its void
    */
    public function pay_scale_approval_applicant_list($reqData) {
        $filter_val_arr = json_decode($reqData->data);
        $limit = $filter_val_arr->pageSize;
        $page = $filter_val_arr->page;
        $filter_by = $filter_val_arr->filtered->filter_val;

        $search = '';
        if (array_key_exists('search', $filter_val_arr->filtered)) {
            $search = $filter_val_arr->filtered->search;
        }

        $orderBy = '';
        $direction = '';
        //$result = $this->Recruitment_dashboard_model->get_new_assigned_applicant($reqData);

        $tbl_rec_app = TBL_PREFIX . 'recruitment_applicant';
        $tbl_rec_app_email = TBL_PREFIX . 'recruitment_applicant_email';
        $tbl_rec_app_phone = TBL_PREFIX . 'recruitment_applicant_phone';
        $tbl_member = TBL_PREFIX . 'member';
        $tbl_rec_app_pay_point_approval = TBL_PREFIX . 'recruitment_applicant_pay_point_approval';

        // this table records merge data in loop
        $tbl_rec_app_pay_point_opt = TBL_PREFIX . 'recruitment_applicant_pay_point_options';
        $tbl_rec_app_work_area = TBL_PREFIX . 'recruitment_applicant_work_area';
        $tbl_classification_level = TBL_PREFIX . 'classification_level';
        $tbl_classification_point = TBL_PREFIX . 'classification_point';
        $tbl_rec_app_stage_attachment = TBL_PREFIX . 'recruitment_applicant_stage_attachment';

        $this->db->select('date_format(task.start_datetime,"%d/%m/%Y")');
        $this->db->from('tbl_recruitment_task as task');
        $this->db->join("tbl_recruitment_task_stage as taskstage", "taskstage.id=task.task_stage and taskstage.key='cab_day' and taskstage.archive=0", "inner");
        $this->db->join('tbl_recruitment_task_applicant as taskapp', 'taskapp.taskId=task.id and taskapp.status=1 and taskapp.archive=0', 'inner');
        $this->db->where_in("task.status", [1, 2]);
        $this->db->where("taskapp.applicant_id=tbl_recruitment_applicant.id", null, false);
        $this->db->limit(1);
        $sub_query_cab_day = $this->db->get_compiled_select();

        $this->db->select("stage_attachment.attachment", false);
        $this->db->from('tbl_recruitment_applicant_stage_attachment stage_attachment');
        $this->db->join("tbl_recruitment_job_requirement_docs docs", "docs.id=stage_attachment.doc_category", "inner");
        $this->db->where("docs.title='" . PAY_SCALE_DOC_CATEGORY . "'", null, false);
        $this->db->where("stage_attachment.applicant_id=tbl_recruitment_applicant.id", null, false);
        $this->db->where("stage_attachment.archive=0", null, false);
        $this->db->where("stage_attachment.document_status!=2", null, false);
        //$this->db->where($where_documentstage, null, false);
        $this->db->order_by("stage_attachment.id", "desc");
        $this->db->limit(1);
        $sub_query = $this->db->get_compiled_select();

        $coulumns_recruitment_applicant = array($tbl_rec_app . '.id', "concat(tbl_recruitment_applicant.firstname,' ',tbl_recruitment_applicant.lastname) as  applicant", "date_format(tbl_recruitment_applicant.date_applide,'%d/%m/%Y') as date_applide",
            $tbl_rec_app_email . '.email',
            $tbl_rec_app_phone . '.phone',
            //"concat(tbl_member.firstname,' ',tbl_member.lastname) as  Recruiter",
            $tbl_rec_app . '.appId', $tbl_rec_app_pay_point_approval . '.id as pay_point_approval_id',
            $tbl_rec_app_pay_point_approval . '.relevant_notes', $tbl_rec_app_pay_point_approval . '.status');
        $query = $this->db->select($coulumns_recruitment_applicant);
        $this->db->select('(' . $sub_query . ') as attachment', false);
        $this->db->select('(' . $sub_query_cab_day . ') as cabday_booked', false);
        $this->db->select('(select concat(firstname," ",lastname) as recruiter_name from tbl_member inner join tbl_department ON  tbl_member.department=tbl_department.id and tbl_department.short_code!="external_staff" and tbl_member.archive=tbl_department.archive AND tbl_member.archive=0 where tbl_member.id = ' . $tbl_rec_app . '.recruiter) as Recruiter');
        $this->db->from($tbl_rec_app);
        $this->db->join($tbl_rec_app_email, $tbl_rec_app_email . '.applicant_id = ' . $tbl_rec_app . '.id', 'inner');
        $this->db->join($tbl_rec_app_phone, $tbl_rec_app_phone . '.applicant_id = ' . $tbl_rec_app . '.id', 'inner');
        /* $this->db->join($tbl_member, $tbl_member.'.id = '.$tbl_rec_app.'.recruiter', 'inner'); */
        $this->db->join($tbl_rec_app_pay_point_approval, $tbl_rec_app_pay_point_approval . '.applicant_id = ' . $tbl_rec_app . '.id', 'inner');

        // search by columns name
        $src_columns = array('tbl_recruitment_applicant.firstname', 'tbl_recruitment_applicant.lastname', 'tbl_recruitment_applicant.appId', 'spell_condition',
            /* 'tbl_member.firstname' ,'tbl_member.lastname', */
            'tbl_recruitment_applicant_email.email', 'tbl_recruitment_applicant_phone.phone');
        if (!empty($search)) {
            $this->db->group_start();
            //$search_value = $filter->search;

            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $this->db->or_like($serch_column[0], $search);
                } else if ($column_search == 'spell_condition') {

                    $this->db->or_where("case when tbl_recruitment_applicant.recruiter>0 then tbl_recruitment_applicant_pay_point_approval.id in( SELECT sub_rappa.id FROM
                tbl_recruitment_applicant_pay_point_approval as sub_rappa INNER JOIN tbl_recruitment_applicant as sub_ra ON sub_rappa.applicant_id=sub_ra.id 
                inner join tbl_member as sub_a on sub_a.id = sub_ra.recruiter
                inner join tbl_department as sub_d ON  sub_a.department=sub_d.id and sub_d.short_code!='external_staff' and sub_a.archive=sub_d.archive AND sub_a.archive=0
                where tbl_recruitment_applicant_pay_point_approval.id=sub_rappa.id AND sub_rappa.archived=0
                and (`sub_a`.`firstname` LIKE '%" . $search . "%' ESCAPE '!' OR `sub_a`.`lastname` LIKE '%" . $search . "%' ESCAPE '!')
                ) ELSE '' END ", NULL, FALSE);
                    /* $this->db->or_where("case when tbl_recruitment_applicant.recruiter>0 then rt.id in (select sub_rtra.taskId from tbl_recruitment_task_recruiter_assign as sub_rtra 
                      inner join tbl_member as sub_a on sub_a.id = sub_rtra.recruiterId where sub_rtra.taskId = rt.id AND sub_rtra.primary_recruiter = 1 AND sub_rtra.archive=0 and `sub_a`.`firstname` LIKE '%" . $search_value . "%' ESCAPE '!' OR `sub_a`.`lastname` LIKE '%" . $search_value . "%' ESCAPE '!' ) ELSE '' END", null, false); */
                } else {
                    $this->db->or_like($column_search, $search);
                }
            }
            $this->db->group_end();
        }

        if ($filter_by === 'all') {
            //echo $filter_by;
        } else {
            $where = array($tbl_rec_app_pay_point_approval . '.status' => $filter_by);
            $this->db->where($where);
        }
        $this->db->order_by($tbl_rec_app_pay_point_approval . ".requested_at", "DESC");
        $this->db->limit($limit, ($page * $limit));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();
        $data = pos_index_change_array_data($result, 'pay_point_approval_id');

        if ($dt_filtered_total > 0) {

            $where_approval_id = array_keys($data);
            // Here get all data from table tbl_rec_ap_work_area, tbl_classification_level,tbl_classification_point

            $coulumns_pay_point_opt = array(
                $tbl_rec_app_pay_point_opt . '.pay_point_approval_id', $tbl_rec_app_pay_point_opt . '.id', $tbl_rec_app_pay_point_opt . '.work_area as work_area_id',
                $tbl_rec_app_work_area . '.work_area', $tbl_classification_point . '.short_title as point_name', $tbl_classification_point . '.id as point_id',
                $tbl_classification_level . '.short_title as level_name', $tbl_classification_level . '.id as level_id'
            );

            $this->db->select($coulumns_pay_point_opt);
            $this->db->from($tbl_rec_app_pay_point_opt);
            $this->db->where_in('pay_point_approval_id', $where_approval_id);
            $this->db->join($tbl_rec_app_work_area, $tbl_rec_app_work_area . '.id = ' . $tbl_rec_app_pay_point_opt . '.work_area', 'inner');
            $this->db->join($tbl_classification_level, $tbl_classification_level . '.id = ' . $tbl_rec_app_pay_point_opt . '.pay_level', 'inner');
            $this->db->join($tbl_classification_point, $tbl_classification_point . '.id = ' . $tbl_rec_app_pay_point_opt . '.pay_point', 'inner');
            $query_point_opt = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $point_opt_result = $query_point_opt->result();

            $array_data_opt = merge_multidimensional_array_values_by_key($point_opt_result, 'pay_point_approval_id');

            foreach ($result as $key => $value) {
                $approval_id = $value->pay_point_approval_id;
                if (array_key_exists($approval_id, $array_data_opt)) {
                    //if(!isset($array_data_opt[$approval_id])){
                    $value->work_area_options = $array_data_opt[$approval_id];
                }
            }
        }
        return $return = array('count' => $dt_filtered_total, 'data' => $result);
    }

    public function save_approved_pay_scale_approval_applicant($reqData) {
        $applicant_id = $reqData->data->id;
        $update_data = array('status' => 1);
        $where_id = array('applicant_id' => $applicant_id);
        $approved_by_user_id = $reqData->adminId;
        $result = $this->basic_model->update_records('recruitment_applicant_pay_point_approval', $update_data, $where_id);
        if ($result) {
            $relevant_notes = $this->get_recruitment_stage_notes($applicant_id);
            $stage = '4.2';
            $stage_id = $this->get_recruitment_stage_id_by_stage($stage);

            $stage_notes = array();
            $stage_notes['applicant_id'] = $applicant_id;
            $stage_notes['notes'] = $relevant_notes;
            $stage_notes['created'] = DATE_TIME;
            $stage_notes['stage'] = $stage_id;
            $stage_notes['is_main_stage_label'] = 0;
            $stage_notes['recruiterId'] = $approved_by_user_id;
            $response = $this->basic_model->insert_records('recruitment_applicant_stage_notes', $stage_notes, false);
            if ($response) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function save_approved_pay_scale_approval_applicant_relevant_notes($reqData) {
        $approval_id = $reqData->data->id;
        $relevant_notes = $reqData->data->relevant_notes;
        $update_data = array('relevant_notes' => $relevant_notes);
        $where_id = array('applicant_id' => $approval_id);
        $this->basic_model->update_records('recruitment_applicant_pay_point_approval', $update_data, $where_id);
        return true;
    }

    public function get_applicant_recruitment_dashboard_graph($type = 'week') {
        $fin_year = get_current_n_previous_financial_year();

        $checkType = in_array(strtolower($type), ['week', 'year', 'month']) ? strtolower($type) : 'week';
        $tbl_rec_app = TBL_PREFIX . 'recruitment_applicant';
        $tbl_rec_task_app = TBL_PREFIX . 'recruitment_task_applicant';
        $this->db->select([
            //"COUNT(CASE when status=1 && current_stage=1 && recruiter>0  THEN (select 1 from  tbl_recruitment_applicant as a where a.id=tbl_recruitment_applicant.id and not exists (Select applicant_id FROM tbl_recruitment_task_applicant where tbl_recruitment_task_applicant.applicant_id=tbl_recruitment_applicant.id group by applicant_id having count(tbl_recruitment_task_applicant.id)>0)) ELSE NULL END) as '1'",
            //"COUNT(CASE when status=1 && current_stage=1 && recruiter>0  THEN (select 1 from tbl_recruitment_task_applicant where tbl_recruitment_task_applicant.applicant_id=tbl_recruitment_applicant.id group by applicant_id having count(tbl_recruitment_task_applicant.id)>0) ELSE NULL END) as '2'" ,
            "COUNT(CASE when status=2 THEN 1 ELSE NULL END) as '3'"], false);

        $this->db->from($tbl_rec_app);
        $this->db->where('archive', 0);
        $this->db->where('DATE(updated) <=CURDATE()', NULL, false);
        if ($checkType == 'week') {
            $this->db->where('YEARWEEK(rejected_date,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(rejected_date) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(rejected_date) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
//            $this->db->where('YEAR(rejected_date) = YEAR(CURDATE())', NULL, false);
            $this->db->where("date(rejected_date) BETWEEN '" . $fin_year['financial_start'] . "' AND '" . $fin_year['financial_end'] . "'");
        }
        $response = array();
        $query_unscess = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        if ($query_unscess->num_rows() > 0) {
            $response = $response + $query_unscess->row_array();
        }
     

        $this->db->select([
            "COUNT(CASE when status=1 && current_stage=1 && recruiter>0  THEN (select 1 from  tbl_recruitment_applicant as a where a.id=tbl_recruitment_applicant.id and not exists (Select applicant_id FROM tbl_recruitment_task_applicant where tbl_recruitment_task_applicant.applicant_id=a.id group by applicant_id having count(tbl_recruitment_task_applicant.id)>0)) ELSE NULL END) as '1'",
                //"COUNT(CASE when status=1 && current_stage=1 && recruiter>0  THEN (select 1 from tbl_recruitment_task_applicant where tbl_recruitment_task_applicant.applicant_id=tbl_recruitment_applicant.id group by applicant_id having count(tbl_recruitment_task_applicant.id)>0) ELSE NULL END) as '2'" ,
                //"COUNT(CASE when status=2 THEN 1 ELSE NULL END) as '3'"
                ], false);
        $this->db->from($tbl_rec_app);
        $this->db->where('archive', 0);
        $this->db->where('DATE(date_applide) <=CURDATE()', NULL, false);
        if ($checkType == 'week') {
            $this->db->where(' YEARWEEK(date_applide,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(date_applide) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(date_applide) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
//            $this->db->where('YEAR(created) = YEAR(CURDATE())', NULL, false);
            $this->db->where("date(date_applide) BETWEEN '" . $fin_year['financial_start'] . "' AND '" . $fin_year['financial_end'] . "'");
        }

        $query_new = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
    
        if ($query_new->num_rows() > 0) {
            $response = $response + $query_new->row_array();
        }



        //select 1 from tbl_recruitment_task_applicant where tbl_recruitment_task_applicant.applicant_id=tbl_recruitment_applicant.id group by applicant_id having count(tbl_recruitment_task_applicant.id)>0
        $this->db->select('1', false);
        $this->db->from($tbl_rec_task_app);
        $this->db->where($tbl_rec_task_app . '.applicant_id=' . $tbl_rec_app . '.id', null, false);
        $this->db->where('DATE(' . $tbl_rec_task_app . '.created) <=CURDATE()', NULL, false);
        if ($checkType == 'week') {
            $this->db->where(' YEARWEEK(' . $tbl_rec_task_app . '.created,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(' . $tbl_rec_task_app . '.created) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(' . $tbl_rec_task_app . '.created) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
//            $this->db->where('YEAR(' . $tbl_rec_task_app . '.created) = YEAR(CURDATE())', NULL, false);
            $this->db->where("date(" . $tbl_rec_task_app . ".created) BETWEEN '" . $fin_year['financial_start'] . "' AND '" . $fin_year['financial_end'] . "'");
        }
        $this->db->group_by($tbl_rec_task_app . '.applicant_id');
        $this->db->having('count(' . $tbl_rec_task_app . '.id)>0');
        $subQureyWheredata = $this->db->get_compiled_select();

        $this->db->select('1', false);
        $this->db->from($tbl_rec_app.' as sub_ra');
        $this->db->where('sub_ra.id=' . $tbl_rec_app . '.id', null, false);
        if ($checkType == 'week') {
          $this->db->where(' YEARWEEK(sub_ra.date_applide,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
          $this->db->where('YEAR(sub_ra.date_applide) = YEAR(CURDATE())', NULL, false);
          $this->db->where('MONTH(sub_ra.date_applide) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
            $this->db->where("date(sub_ra.date_applide) BETWEEN '" . $fin_year['financial_start'] . "' AND '" . $fin_year['financial_end'] . "'");
        } 
        $this->db->group_by('sub_ra.id');
        $subQureyWheredata1 = $this->db->get_compiled_select();


        $this->db->select([
            //"COUNT(CASE when status=1 && current_stage=1 && recruiter>0  THEN (select 1 from  tbl_recruitment_applicant as a where a.id=tbl_recruitment_applicant.id and not exists (Select applicant_id FROM tbl_recruitment_task_applicant where tbl_recruitment_task_applicant.applicant_id=tbl_recruitment_applicant.id group by applicant_id having count(tbl_recruitment_task_applicant.id)>0)) ELSE NULL END) as '1'",
            "COUNT(CASE when status=1 && current_stage=1 && recruiter>0  THEN (" . $subQureyWheredata . ")
            WHEN status=1 && current_stage>1 && recruiter>0 THEN (" . $subQureyWheredata1 . ")
            ELSE NULL END) as '2'",
                //"COUNT(CASE when status=2 THEN 1 ELSE NULL END) as '3'"
                ], false);
        $this->db->from($tbl_rec_app);
        $this->db->where('archive', 0);
        /*  $this->db->where('DATE(created) <=CURDATE()', NULL, false);
          if ($checkType == 'week') {
          $this->db->where(' YEARWEEK(created,7) = YEARWEEK(CURDATE(),7)', NULL, false);
          } else if ($checkType == 'month') {
          $this->db->where('YEAR(created) = YEAR(CURDATE())', NULL, false);
          $this->db->where('MONTH(created) = MONTH(CURDATE())', NULL, false);
          } else if ($checkType == 'year') {
          $this->db->where('YEAR(created) = YEAR(CURDATE())', NULL, false);
          } */
        $query_inprogress = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
       
        if ($query_inprogress->num_rows() > 0) {
            $response = $response + $query_inprogress->row_array();
        }

       
        return $response;
    }

    public function get_applicant_recruitment_dashboard_prospective_graph($type = 'week') {
        $fin_year = get_current_n_previous_financial_year();

        $checkType = in_array(strtolower($type), ['week', 'year', 'month']) ? strtolower($type) : 'week';
        $tbl_rec_app = TBL_PREFIX . 'recruitment_applicant';
        $this->db->select("COUNT(id) as 'total' ", false);
        $this->db->where('archive', 0);
        $this->db->from($tbl_rec_app);
        $this->db->where('DATE(date_applide) <=CURDATE()', NULL, false);
        if ($checkType == 'week') {
            $this->db->where(' YEARWEEK(date_applide,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(date_applide) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(date_applide) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
            $this->db->where("date(date_applide) BETWEEN '" . $fin_year['financial_start'] . "' AND '" . $fin_year['financial_end'] . "'");
//            $this->db->where('YEAR(date_applide) = YEAR(CURDATE())', NULL, false);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        }

        return 0;
    }

    public function get_applicant_recruitment_dashboard_applicant_hired_graph($type = 'week') {
        $fin_year = get_current_n_previous_financial_year();

        $checkType = in_array(strtolower($type), ['week', 'year', 'month']) ? strtolower($type) : 'week';
        $tbl_rec_app = TBL_PREFIX . 'recruitment_applicant';
        $this->db->select("COUNT(id) as 'total' ", false);
        $this->db->where(['archive' => 0, 'status' => 3]);
        $this->db->from($tbl_rec_app);
        $this->db->where('DATE(hired_date) <=CURDATE()', NULL, false);
        if ($checkType == 'week') {
            $this->db->where(' YEARWEEK(hired_date,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(hired_date) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(hired_date) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
            $this->db->where("date(hired_date) BETWEEN '" . $fin_year['financial_start'] . "' AND '" . $fin_year['financial_end'] . "'");
//            $this->db->where('YEAR(updated) = YEAR(CURDATE())', NULL, false);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        }
        return 0;
    }

    public function get_latest_status_updates_notification() {
        $this->db->select("concat_ws(' ',firstname,lastname)", false);
        $this->db->from('tbl_member');
        $this->db->where('tbl_member.id=ran.recruiterId', null, false);
        $sub_query_rec = $this->db->get_compiled_select();
        $this->db->select("concat_ws(' ',firstname,lastname)", false);
        $this->db->from('tbl_recruitment_applicant');
        $this->db->where('tbl_recruitment_applicant.id=ran.applicant_id', null, false);
        $sub_query_app = $this->db->get_compiled_select();
        $this->db->select(
                [
            'ran.id',
            'ran.action_type as mode',
            "CASE WHEN recruiterId>0 THEN (" . $sub_query_rec . ") ELSE '' END as recruiter_name",
            "CASE WHEN applicant_id>0 THEN (" . $sub_query_app . ") ELSE '' END as recruitment_applicant",
            "CASE WHEN action_type=1 THEN 'Flagged Applicant Approval'  WHEN action_type=2 then 'Pay Scale Confirmation' WHEN action_type=3 then 'Duplicate Applicant Approval' ELSE '' END as title",
            "CASE WHEN action_type=1 THEN 'flagged for inappropriate behaviour'  WHEN action_type=2 then 'has requested pay scale OH3' WHEN action_type=3 then 'marked applicant as duplicate' ELSE '' END as description",
            "date_format(ran.created,'%d/%m/%Y') notification_date"
                ], false
        );

        $this->db->from('tbl_recruitment_action_notification as ran');
        $this->db->where(['ran.status' => 0, 'ran.archive' => 0]);
        $this->db->order_by('ran.created DESC');
        $this->db->limit(10);
        $query = $this->db->get();
        //last_query(1);
        $data = [];
        if ($query->num_rows() > 0) {
            $data = $query->result();
        }
        $result = array('status' => true, 'data' => $data);
        return $result;
    }

    public function action_updates_notification($reqData) {
        $adminId = $reqData->adminId;
        $data = $reqData->data;
        $id = isset($data->id) ? $data->id : 0;
        $type = isset($data->type) ? $data->type : 0;

        $result = array('status' => true);
        if (!empty($id) && !empty($type)) {
            $dataType = $type == 1 ? 0 : $type;
            $this->basic_model->update_records('recruitment_action_notification', ['action_at' => DATE_TIME, 'action_by' => $adminId, 'status' => $dataType], ['id' => $id, 'status' => 0]);
            if ($type == 1) {
                $result['redirect'] = true;
            }
        }
        return $result;
    }

    public function multiple_work_area_details($data) {
        $data_array = json_decode($data);
        $where_column = array("pay_point_approval_id" => $data_array->pay_point_approval_id, "work_area" => $data_array->work_area_id, "id !=" => $data_array->id);
        $this->db->select("count(work_area) as cnt");
        $this->db->from('tbl_recruitment_applicant_pay_point_options');
        $this->db->where($where_column);
        $query = $this->db->get();
        $data = $query->row();
        return $data->cnt;
    }

    public function get_pay_scale_approval_status($approval_id) {
        $where_column = array("applicant_id" => $approval_id);
        $this->db->select("status");
        $this->db->from(TBL_PREFIX . 'recruitment_applicant_pay_point_approval');
        $this->db->where($where_column);
        $query = $this->db->get();
        $data = $query->row();
        return $data->status;
    }

    public function get_recruitment_stage_id_by_stage($stage) {
        $where_column = array("stage" => $stage);
        $this->db->select("id");
        $this->db->from(TBL_PREFIX . 'recruitment_stage');
        $this->db->where($where_column);
        $query = $this->db->get();
        $data = $query->row();
        return $data->id;
    }

    public function get_recruitment_stage_notes($applicant_id) {
        $where_column = array("applicant_id" => $applicant_id);
        $this->db->select("relevant_notes");
        $this->db->from(TBL_PREFIX . 'recruitment_applicant_pay_point_approval');
        $this->db->where($where_column);
        $query = $this->db->get();
        $data = $query->row();
        return $data->relevant_notes;
    }

    public function get_communication_log($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $applicant_id = $reqData->applicant_id?? 0;
        #pr($reqData);
        $orderBy = '';
        $direction = '';

        $src_columns = array('comm.id', 'comm.title', 'comm.communication_text');
        $available_column = array('id', 'communication_text', 'created', 'from', 'title', 'log_type');
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'comm.id';
            $direction = 'DESC';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        if (!empty($filter)) {
            if (isset($filter->filterBy) && $filter->filterBy != 'all') {
                $this->db->where("comm.log_type", $filter->filterBy);
            }

            if (isset($filter->applicantId) && !empty($filter->applicantId)) {
                $this->db->where("comm.userId", $filter->applicantId);
            }

            if (!empty($filter->srch_box)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $filter->srch_box);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $filter->srch_box);
                    }
                }
                $this->db->group_end();
            }
        }

        $select_column = array('comm.id', 'comm.communication_text', 'DATE_FORMAT(comm.created, "%d/%m/%Y %h:%i %p") as created', 'comm.from', 'comm.title', 'comm.log_type');

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(SELECT concat(ra.firstname,' ',ra.middlename,' ',ra.lastname)  FROM tbl_recruitment_applicant AS ra WHERE ra.id=comm.userId) as to_email");
        $this->db->select("(SELECT concat(m.firstname,' ',m.middlename,' ',m.lastname) FROM tbl_member AS m WHERE m.id=comm.send_by) as from_email");
        $this->db->select("(CASE 
            WHEN comm.log_type = 1 THEN 'SMS'
            WHEN comm.log_type = 2 THEN 'Email'
            WHEN comm.log_type = 3 THEN 'Phone'
            else '' end) as log_type
        ");

        $this->db->from('tbl_communication_log as comm');
        $this->db->where("comm.user_type", 1);
        if (isset($reqData->log_type)) {
            $this->db->where('comm.log_type', $reqData->log_type);
        }
        if (!empty($applicant_id)) {
            $this->db->where(['comm.userId' => $applicant_id]);
        }        
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query();
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();

        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true);
        return $return;
    }

}
