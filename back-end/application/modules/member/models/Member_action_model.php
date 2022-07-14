<?php

class Member_action_model extends CI_Model {

    function get_testimonial() {
        $tbl_testimonial = TBL_PREFIX . 'testimonial';
        $tbl_member = TBL_PREFIX . 'member';

        $this->db->select(array($tbl_testimonial . '.title', $tbl_testimonial . '.testimonial', $tbl_testimonial . '.full_name'));
        $this->db->from($tbl_testimonial);
        $this->db->where(array($tbl_testimonial . '.module_type' => 1));
        $this->db->order_by('RAND()');
        $this->db->limit(1);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $x = $query->result();
        return $x;
    }

    function get_all_member_count() {
        $this->db->select(array('m.id'));
        $this->db->from('tbl_member as m');
        $this->db->join('tbl_department as d', 'd.id = m.department', 'inner');

        $this->db->where('m.archive', 0);
        $this->db->where('d.short_code', 'external_staff');

        $query = $this->db->get();
        $x = $query->result();
        return $x;
    }

    function member_count_based_created($view_type) {

        if ($view_type == 'year') {
            $where['YEAR(m.created)'] = date('Y');
            $where['m.archive'] = '0';
            $this->db->where($where);
        } else if ($view_type == 'week') {
            #First day of week is Monday
            /*$where = '';
            $where1 = "m.created > DATE_SUB(NOW(), INTERVAL 1 WEEK) AND m.archive = '0'";
            $this->db->where($where1);*/
            $this->db->where(' YEARWEEK(m.created,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else {
            $where['MONTH(m.created)'] = date('m');
            $where['YEAR(m.created)'] = date('Y');
            $where['m.archive'] = '0';
            $this->db->where($where);
        }
        $this->db->select(array('m.id'));
        $this->db->from('tbl_member as m');
        $this->db->join('tbl_department as d', 'd.id = m.department', 'inner');

        $this->db->where('m.archive', 0);
        $this->db->where('d.short_code', 'external_staff');

        $query = $this->db->get();
        #last_query();
        $x = $query->result();
        return $x;
    }

    function get_member_name_and_email($id) {
        $this->db->select("concat_ws(' ',m.firstname,m.middlename,m.lastname) as fullName");
        $this->db->select("mm.email");
        $this->db->from("tbl_member as m");
        $this->db->join("tbl_member_email as mm", "mm.memberId = m.id AND mm.primary_email = 1");
        $this->db->where('m.id', $id);
        $this->db->where('m.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        return $query->row();
    }

    function get_work_area_of_member($req) {
        $memberId = $req->data->member_id;
        $this->db->select(array("mwa.work_area as value"));
        $this->db->from("tbl_member_work_area as mwa");
        $this->db->join("tbl_member as mm", "mm.id = mwa.memberId");        
        $this->db->where('mwa.memberId', $memberId);
        $this->db->where('mwa.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        return $query->row();
    }

    public function get_member_contact_history($reqData)
    {
        $memberId = $reqData->memberId;   
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $tbl_payrate = TBL_PREFIX . 'member_contact_history';
        $available_column = array("id", "contact_type", "note", "time");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = $tbl_payrate . '.id';
            $direction = 'DESC';
        }

        $select_column = array($tbl_payrate . ".id", $tbl_payrate . ".contact_type", $tbl_payrate . ".note", "DATE_FORMAT($tbl_payrate.time,'%d/%m/%Y %h:%i %p') as time");

        if (isset($filter->start_date) && $filter->start_date != '')
            $this->db->where("DATE(tbl_member_contact_history.time) >= '" . date('Y-m-d', strtotime($filter->start_date)) . "'");

        if(isset($filter->end_date) && $filter->end_date != '')
            $this->db->where("DATE(tbl_member_contact_history.time) <= '" . date('Y-m-d', strtotime($filter->end_date)) . "'");


        if(isset($filter->filter_by) && $filter->filter_by != '' && $filter->filter_by>0)
            $this->db->where(array($tbl_payrate.".contact_type"=>$filter->filter_by));

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from($tbl_payrate);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $this->db->where(array($tbl_payrate.".archive"=>0,'memberId'=>$memberId));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query();
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();
        $data = [];

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) 
            {
                $val->contact_type_str = (isset($val->contact_type) && $val->contact_type == 1 ?'Email':(($val->contact_type == 2)?'Phone':(($val->contact_type == 3)?'SMS':(($val->contact_type == 4)?'Chat':'Fax'))));;
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count,'status'=>true);
        return $return;
    }

    public function get_member_work_area($status,$memberId) 
    {
        $this->db->select(array('wa.work_area','wa.work_status','wa.archive','wa.id','rwa.work_area as wa_str'));
        $this->db->from("tbl_member_work_area as wa");
        $this->db->join("tbl_recruitment_applicant_work_area as rwa", "rwa.id = wa.work_area");        
        $this->db->where('wa.memberId', $memberId);

        if ($status == 'Archive')
            $this->db->where('wa.archive', 1);
        else
            $this->db->where('wa.archive', 0);

        $query = $this->db->get();
        //last_query();
        return $query->result();
    }

    public function get_all_skills($memberId,$memberAssistanceType)
    {
        if ($memberId) 
        {
            $this->db->select(array("pg.name", "pg.id", "pg.key_name"));
            $this->db->select("(CASE WHEN pg.key_name = 'other' then (SELECT other_title FROM tbl_member_skill where member_id=" . $memberId . " and skillId = pg.id AND archive=0 LIMIT 1) else '' end) as other_title");
            $this->db->from('tbl_participant_genral as pg');
            if($memberAssistanceType=='assistance') 
                $this->db->where(array('pg.type' => "assistance"));
            else if($memberAssistanceType=='mobility')
                $this->db->where(array('pg.type' => "mobility"));
            $this->db->order_by("pg.order", "asc");
            $query_task_info = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $skill = $query_task_info->result();
            
            $this->db->select(array("skillId", "other_title"));
            $this->db->from('tbl_member_skill');
            $this->db->where(array('tbl_member_skill.member_id' => $memberId,'tbl_member_skill.archive'=>0));
            $this->db->join("tbl_participant_genral as pg", "pg.id = tbl_member_skill.skillId AND pg.status=1",'inner');
            $all_member_skill_sql = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $member_skill = $all_member_skill_sql->result();
            $skill_id_ary = [];
            if(!empty($member_skill)){
                $skill_id_ary = array_column($member_skill, 'skillId');
            }

            if (!empty($skill)) {
                foreach ($skill as $val) {
                    if(!empty($skill_id_ary) && in_array($val->id, $skill_id_ary))
                        $val->checked = true ;
                    else
                       $val->checked =  false;
               }
           }
           return $skill;
       }else{
        return false;
    }
}

public function get_bonus_training_detail($reqData)
{

    $limit = $reqData->pageSize;
    $page = $reqData->page;
    $sorted = $reqData->sorted;
    $filter = $reqData->filtered;
    $orderBy = '';
    $direction = '';
    $tbl_bonus = TBL_PREFIX . 'member_bonus_training';
    $member_id = $reqData->member_id ?? 0;
    $available_column = array("id","title", "date","hour","note","created" );
    if (!empty($sorted)) {
        if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
            $orderBy = $sorted[0]->id;
            $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
        }
    } else {
        $orderBy = $tbl_bonus . '.id';
        $direction = 'DESC';
    }      

        /*if (!empty($reqData->filtered->srch_value)) {
            $this->db->group_start();
            $src_columns = array("CONCAT(tbl_recruitment_applicant.firstname,' ',tbl_recruitment_applicant.middlename,' ',tbl_recruitment_applicant.lastname)", "CONCAT(tbl_recruitment_applicant.firstname,' ',tbl_recruitment_applicant.lastname)", "tbl_recruitment_job_position.title as position","tbl_recruitment_job_category.name as job_category","tbl_recruitment_job_employment_type.title as employment_type");

            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $this->db->or_like($serch_column[0], $filter->srch_value);
                } else {
                    $this->db->or_like($column_search, $filter->srch_value);
                }
            }
            $this->db->group_end();
        }*/

        $select_column = array($tbl_bonus . ".id",$tbl_bonus . ".title", "date_format($tbl_bonus.date,'%d/%m/%Y') as date",$tbl_bonus . ".hour",$tbl_bonus . ".note",$tbl_bonus . ".created" );
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from($tbl_bonus);
        $this->db->where('memberId', $member_id);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        $status = false;
        if (!empty($dataResult)) {
            $status = true;
            /*foreach ($dataResult as $val) {
                $val->date = date('d/m/y',strtotime($val->date));
            }*/
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count,'status'=>true);
        return $return;
    }

    public function get_bonus_point($data)
    {
        if(!empty($data)){
            $member_id = $data->member_id ?? 0;
            
            $this->db->select("COALESCE(TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(CONCAT(MOD( TIMESTAMPDIFF(hour, `s`.`start_time`, s.end_time), 24), ':', MOD( TIMESTAMPDIFF(minute, `s`.`start_time`, s.end_time), 60), ' hrs')))),'%H:%i'),0) as total_hours_worked");

            $this->db->select("COALESCE(FORMAT(SUM(TIME_TO_SEC(TIMEDIFF(DATE_FORMAT(end_time, '%Y-%m-%d %H:%i'),DATE_FORMAT(start_time, '%Y-%m-%d %H:%i')))/3600),2),0) as total_hr", false);
            $this->db->select("count(s.id) as total_shift", false);
            $this->db->from('tbl_shift as s');
            $this->db->join('tbl_shift_member as sm', 'sm.shiftId = s.id AND sm.archive = 0 AND sm.status = 3 AND sm.memberId='.$member_id, 'INNER');
            $this->db->where('s.status', 7);  
            $query = $this->db->get();
            #last_query();
            $dataResult = $query->row();

            $time = $total_hours_worked =  '0 hour, 0 min';
            if(!empty($dataResult))
            {
                $my_total_hrs = $dataResult->total_hours_worked;

                $dataResult->hour_earnt = $hour_earnt =   floor($dataResult->total_hr/MAX_HOUR_WORKED);

                if($dataResult->total_hours_worked > 0){
                    $temp_time_w = explode(':', $dataResult->total_hours_worked);
                    $hr_str_w = (isset($temp_time_w[0]) && $temp_time_w[0] > 1)?' hours':' hour';
                    $min_str_w = (isset($temp_time_w[1]) && $temp_time_w[1] > 1)?' mins':' min';
                    $total_hours_worked = $temp_time_w[0].$hr_str_w.', '.$temp_time_w[1].$min_str_w;
                }
                $dataResult->total_hours_worked_str = $total_hours_worked;

                $this->db->select("TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(`hour`))),'%H:%i' ) as total_time");
                $this->db->from('tbl_member_bonus_training');
                $this->db->where(array('memberId'=>$member_id,'archive'=>0));  
                $query = $this->db->get();

                $data_hr_used = $query->row_array();

                if(!empty($data_hr_used['total_time']) && $hour_earnt>0){
                    $my_training_hr = $data_hr_used['total_time'];

                    $temp_time = explode(':', $data_hr_used['total_time']);
                    $hr_str = (isset($temp_time[0]) && $temp_time[0] > 1)?' hours':' hour';
                    $min_str = (isset($temp_time[1]) && $temp_time[1] > 1)?' mins':' min';
                    $time = $temp_time[0].$hr_str.', '.$temp_time[1].$min_str;

                    #echo $my_total_hrs.'-------------'.$my_training_hr;

                    $time1 = strtotime($my_total_hrs);  
                    $time2 = strtotime($my_training_hr);  
                    $time_in_min = ($time1 - $time2)/60;

                    $temp_hr = intdiv($time_in_min, 60);
                    $temp_min = ($time_in_min % 60);

                    $hour = (isset($temp_hr) && $temp_hr > 1)?' hours':' hour';
                    $min = (isset($temp_min) && $temp_min > 1)?' mins':' min';
                    $dataResult->hour_remaing = $temp_hr.$hour.', '. $temp_min.$min;

                }
                $dataResult->training_hr_used = $time;
            }
            return $dataResult;
        }
    }
    public function check_email_already_exist($emails,$member_id=0) {
        $this->db->select(array('me.email'));
        $this->db->from('tbl_member_email as me');
        $this->db->join('tbl_member as m', 'm.id = me.memberId AND m.archive = 0', 'inner');
        $this->db->where('me.archive', 0);
        if($member_id>0)
            $this->db->where('me.memberId!=', $member_id);

        $this->db->where_in("me.email", $emails);
        $this->db->group_by("me.email");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return $result;
    }
}

