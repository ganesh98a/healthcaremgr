<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Recruitment_user_management extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    public function get_new_recruiter_name($reqData) {
        $search = $reqData->search;

        $this->db->select(array('concat(a.firstname," ",a.lastname) as label', 'a.uuid as value'));
        $this->db->from('tbl_member as a');

        $this->db->join('tbl_recruitment_staff as rs', 'rs.adminId = a.uuid AND rs.archive=a.archive AND rs.approval_permission = 0 AND rs.its_recruitment_admin = 0', 'inner');
        $this->db->join('tbl_department as d', 'd.id = a.department AND d.short_code = "internal_staff"', 'inner');
        $this->db->like('concat(a.firstname," ",a.lastname)', $search);
        $this->db->where('a.archive', 0);
        $this->db->where('a.status', 1);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $result = $query->result();
//        last_query();

        return $result;
    }

    function update_allocation_recruiter_area($allocation_area, $preffer_allocation_area, $adminId) {
        $recruiter_area = [];
        if (!empty($allocation_area)) {
            foreach ($allocation_area as $val) {
                $recruiter_area[] = ['adminId' => $adminId, 'allocated_department' => $val->value, 'status' => 1, 'area_type' => 1, 'created' => DATE_TIME];
            }
        }

        if (!empty($preffer_allocation_area)) {
            foreach ($preffer_allocation_area as $val) {
                $recruiter_area[] = ['adminId' => $adminId, 'allocated_department' => $val->value, 'status' => 1, 'area_type' => 2, 'created' => DATE_TIME];
            }
        }

        if (!empty($recruiter_area)) {
            $this->basic_model->delete_records('recruitment_staff_department_allocations', ['adminId' => $adminId]);
            $this->basic_model->insert_records('recruitment_staff_department_allocations', $recruiter_area, true);
        }
    }

    public function get_department($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $tbl_rec_dep = TBL_PREFIX . 'recruitment_department';

        $src_columns = array();
        $available_column = array("id", "name", "created", "department");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = $tbl_rec_dep . '.id';
            $direction = 'DESC';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        if (!empty($filter->srch_box)) {
            $this->db->group_start();
            $src_columns = array($tbl_rec_dep . ".id", $tbl_rec_dep . ".name");

            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $this->db->or_like($serch_column[0], $filter->srch_box);
                } else {
                    $this->db->or_like($column_search, $filter->srch_box);
                }
            }
            $this->db->group_end();
        }

        $select_column = array($tbl_rec_dep . ".id", $tbl_rec_dep . ".name", $tbl_rec_dep . ".created", "'Recruitment' AS department");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from($tbl_rec_dep);
        $this->db->where('archive', '0');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        //last_query(); 
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();
        $data = [];

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $val->created = $val->created != '0000-00-00 00:00:00' ? date('d/m/Y', strtotime($val->created)) : '';
                $val->value = $val->id;
                $val->text = $val->name;
                if (isset($val->id))
                    $val->alloted_staff_member = $this->allocated_members_by_dept_id($val->id);
                else
                    $val->alloted_staff_member = array();
            }
            $data = array_merge(array(array('demo' => [])), $dataResult);
            #$data = array_merge(array(array('demo'=>[])),$dataResult);
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count);
        return $return;
    }

    public function get_staff_members($reqData) {
        require_once APPPATH . 'Classes/recruitment/admin.php';
        $objAdmin = new AdminClass\AdminRecruit();

        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $available_column = array('id', 'name', 'status', 'created', "task_cnt");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'a.id';
            $direction = 'desc';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        $src_columns = array('a.id', 'concat(a.firstname," ",a.lastname) as FullName', 'DATE_FORMAT(rs.created, "%d/%m/%Y")');

        //$this->db->select(array('rd.id as value', 'rd.name as label', 'rsda.area_type'));
        /* $this->db->select("IFNULL(GROUP_CONCAT(CONCAT(COALESCE(sub_rd.id,''),'@__BREAKER__@',COALESCE(sub_rd.name,''),'@__BREAKER__@',COALESCE(sub_rsda.area_type,'')) SEPARATOR '@__@@__@'),'') as recuritment_label_and_area",false);
          $this->db->from('tbl_recruitment_staff_department_allocations as sub_rsda');
          $this->db->join('tbl_recruitment_department as sub_rd', 'sub_rd.id = sub_rsda.allocated_department', 'inner');
          $this->db->where('sub_rsda.adminId=rs.adminId',null,false); */

          $subQuery = $this->get_recruiter_area_detail_sub_query(1);
          if (!empty($filter->srch_box)) {
            $whereSubQuery = $this->get_recruiter_area_detail_sub_query(2, $filter->srch_box);
        }

        if (!empty($filter->srch_box)) {
            $this->db->group_start();
            $search_value = $filter->srch_box;
            $this->db->or_where("rs.adminId IN (" . $whereSubQuery . ")", null, false);

            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    /*
                    $serch_column = explode(" as ", $column_search);
                    $this->db->or_like($serch_column[0], $search_value);
                    */
                    $searchKey = trim($search_value);
                    $searchKeyArray = preg_split('/\s+/', $searchKey);
                    if(count($searchKeyArray) == 1) {
                        $this->db->or_like("a.firstname", trim($searchKeyArray[0]));
                        $this->db->or_like("a.lastname", trim($searchKeyArray[0]));
                    } else if(count($searchKeyArray) == 2){
                        $this->db->or_like("a.firstname", trim($searchKeyArray[0]));
                        $this->db->or_like("a.lastname", trim($searchKeyArray[0]));
                        $this->db->or_like("a.firstname", trim($searchKeyArray[1]));
                        $this->db->or_like("a.lastname", trim($searchKeyArray[1]));
                    } else {
                        $this->db->or_like($serch_column[0], trim($searchKey));
                    }
                } else {
                    $this->db->or_like($column_search, $search_value);
                }
            }
            $this->db->group_end();
        }

        if ($filter->filter_by == 'all') {
            $this->db->where('a.archive', 0);
        } elseif ($filter->filter_by == 'active') {
            $this->db->where('rs.status', 1);
        } elseif ($filter->filter_by == 'disabled') {
            $this->db->where('rs.status', 0);
        }


        $colowmn = array('a.id', 'concat(a.firstname," ",a.lastname) as name', 'rs.status', 'rs.created', "(select count('id') from tbl_recruitment_task_recruiter_assign as rtra where rtra.recruiterId = a.id AND rtra.archive = 0 ) as task_cnt", "a.profile_pic as avatar");

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $colowmn)), false);
        $this->db->select('(' . $subQuery . ') as recuritment_label_and_area', false);
        $this->db->from('tbl_member as a');

        /* $this->db->join('tbl_recruitment_task_recruiter_assign as rtra', 'rtra.recruiterId = a.id AND rtra.archive = 0', 'left');
        $this->db->join('tbl_recruitment_task as rt', 'rt.id = rtra.taskId AND rt.status = 1', 'left'); */

        // for show only recruiter
        $this->db->join('tbl_recruitment_staff as rs', 'rs.adminId = a.id and rs.archive=a.archive AND (approval_permission = 1 OR its_recruitment_admin = 1)', 'inner');
        $this->db->join('tbl_department as d', 'd.id = a.department AND d.short_code = "internal_staff"', 'inner');

        // defualt globel status should be active (1)
        $this->db->where('a.status', 1);
        // defualt globel archive is 0
        $this->db->where('a.archive', 0);

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

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
                $objAdmin->setAdminid($val->id);
                $val->phones = $objAdmin->get_admin_phone_number();
                $val->emails = $objAdmin->get_admin_email();
                $recruiterAreaDATA = [];
                $prefferedAreaData = [];
                $areaNameData = '';
                if (!empty($val->recuritment_label_and_area)) {
                    $x = explode('@__@@__@', $val->recuritment_label_and_area);
                    if (!empty($x)) {
                        foreach ($x as $k => $v) {
                            $z = explode('@__BREAKER__@', $v);
                            if ($z[2] == 1) {
                                $recruiterAreaDATA[] = ['label' => $z[1], 'value' => $z[0]];
                            } else {
                                $prefferedAreaData[] = ['label' => $z[1], 'value' => $z[0]];
                            }
                        }
                        $areaNameData = implode(', ', array_column($recruiterAreaDATA, 'label'));
                    }
                }
                /*     $x = $this->get_staff_recruitment_area($val->id);
                  $val->recruiter_area = (!empty($x['recruiter_area'])) ? $x['recruiter_area'] : [];
                  $val->area = (!empty($x['area'])) ? $x['area'] : '';
                  $val->preffered_area = (!empty($x['preffered_area'])) ? $x['preffered_area'] : []; */
                  $val->recruiter_area = (!empty($recruiterAreaDATA)) ? $recruiterAreaDATA : [];
                  $val->area = (!empty($areaNameData)) ? $areaNameData : '';
                  $val->preffered_area = (!empty($prefferedAreaData)) ? $prefferedAreaData : [];
              }
          }
        # pr($dataResult);

          $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'status' => true);

          return $return;
      }

      function get_staff_recruitment_area($adminId) {
        $this->db->select(array('rd.id as value', 'rd.name as label', 'rsda.area_type'));
        $this->db->from('tbl_recruitment_staff_department_allocations as rsda');

        $this->db->join('tbl_recruitment_department as rd', 'rd.id = rsda.allocated_department', 'inner');
        $this->db->where("rsda.adminId", $adminId);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();

        $x = '';
        $allocation_area = [];
        $preffered_area = [];

        if (!empty($result)) {
            foreach ($result as $val) {
                if ($val['area_type'] == 1) {
                    $allocation_area[] = $val;
                } else {
                    $preffered_area[] = $val;
                }
            }

            $x = array_column($allocation_area, 'label');
            $x = implode(', ', $x);
        }
        return ['recruiter_area' => $allocation_area, 'area' => $x, 'preffered_area' => $preffered_area];
    }

    public function disable_staff($reqData) {
        $status = $this->applicant_allot_to_new_recruiter($reqData);

        if ($reqData->task_cnt > 0) {
            $this->disable_recruiter_task($reqData->staffId);
        }

        if ($status['status'] == true) {
            $disable_recruiter = array(
                'recruitment_staff_id' => $reqData->staffId,
                'disable_account' => $reqData->disable_account,
                'account_allocated_type' => $reqData->account_allocated_type,
                'relevant_note' => $reqData->relevant_note,
                //'archive' => 0,
                'created' => DATE_TIME,
            );

            $this->basic_model->insert_records('recruitment_staff_disable', $disable_recruiter);
            // update status in recruitment staff
            $this->basic_model->update_records('recruitment_staff', array('status' => 0), array('adminId' => $reqData->staffId));
        }
        return $status;
    }

    public function applicant_allot_to_new_recruiter($reqData) {
        $applicantList = isset($reqData->applicantList) ? $reqData->applicantList : array();
        $data = array();

        //if($reqData->account_allocated_type == 2 )
        {
            if (!empty($applicantList)) {
                foreach ($applicantList as $key => $val) {
                    if (isset($val->allocate_to) && $val->allocate_to->value != '') {
                        $data[] = array('recruiter' => $val->allocate_to->value, 'id' => $val->id);
                    }
                }
            }
            $status = array('status' => true);
        }
        /* else
          {
          //auto assign recruiter to applicant
          $staff = isset($reqData->staffId)?$reqData->staffId:'';
          $recruiterList_ = $this->get_recruiter_for_auto_assign_applicant($staff);
          if(empty($recruiterList_)){
          $status = array('status'=>FALSE,'error'=>'No recruiter found for Auto Allocation.');
          }else{
          if(!empty($applicantList) && !empty($recruiterList_))
          {
          foreach ($applicantList as $key => $val)
          {
          if(isset($val->allocate_to) && $val->allocate_to =='')
          {
          $applicant_id = array_rand(array_flip(array_keys($recruiterList_,min($recruiterList_))),1);
          $data[] = array('recruiter'=>$applicant_id,'id'=>$val->id);
          $recruiterList_[$applicant_id] = $recruiterList_[$applicant_id]+1;
          }
          }
          }
          $status = array('status'=>true);
          }
      } */
      if (!empty($data)) {
        $this->basic_model->insert_update_batch($action = 'update', $table_name = 'recruitment_applicant', $data, $update_base_column_key = 'id');
    }
    return $status;
}

public function allocated_members_by_dept_id($dept_id) {
    $tbl_1 = TBL_PREFIX . 'recruitment_staff_department_allocations';
    $this->db->select(array("CONCAT(tbl_member.firstname,' ',tbl_member.lastname) AS name", "tbl_member.id as hcmr_id", $tbl_1 . ".created as allocation_date", "'Lauren Mckenzie' AS manager"));
    $this->db->from($tbl_1);
    $this->db->join('tbl_member', 'tbl_recruitment_staff_department_allocations.adminId = tbl_member.id', 'left');
    $this->db->where(array($tbl_1 . '.status' => 1, $tbl_1 . '.allocated_department' => $dept_id));
    $query = $this->db->get();
    return $query->result();
}

public function get_staff_details($adminId) {
    $this->db->select(["concat(m.firstname,' ',m.lastname) as name", 'm.id', 'rs.status as recruiter_status']);
    $this->db->from("tbl_member as m");
    $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"', 'inner');
    $this->db->join('tbl_recruitment_staff as rs', 'm.id = rs.adminId', 'inner');
    $this->db->where("m.id", $adminId);
    $query = $this->db->get();

    $result = $query->row_array();

        // get recruitment area detais
    $x = $this->get_staff_recruitment_area($adminId);
    $result['recruiter_area'] = (!empty($x['recruiter_area'])) ? $x['recruiter_area'] : [];
    $result['area'] = (!empty($x['area'])) ? $x['area'] : '';
    $result['preffered_area'] = (!empty($x['preffered_area'])) ? $x['preffered_area'] : [];

    return $result;
}

public function get_recruitment_area_title() {
    $coluwn = array('name as label', 'id as value');
    return $this->basic_model->get_record_where('recruitment_department', $coluwn, '');
}

public function get_staff_task($reqData) {
    $adminId = $reqData->staffId;
    $date = DateFormate($reqData->date, 'm');

    $this->db->select('"0" as id');
    $this->db->select('"0" as title');
    $this->db->select('1 as allDay');
    $this->db->select('COUNT(IF(task_stage = 3, 1, NULL)) as lowData');
    $this->db->select('COUNT(IF(task_stage = 6, 1, NULL)) as mediumData');
    $this->db->select('COUNT(IF(task_stage != 3 AND task_stage != 6, 1, NULL)) as highData');
    $this->db->select('DATE_FORMAT(start_datetime, "%Y-%m-%d") as start_datetime');

    $this->db->from('tbl_recruitment_task as rt');
    $this->db->join('tbl_recruitment_task_recruiter_assign as rtra', 'rtra.taskId = rt.id AND rtra.archive=0 AND rtra.recruiterId = "' . $adminId . '"', 'INNER');
    $this->db->where('DATE_FORMAT(start_datetime, "%m") =', $date);


    $this->db->group_by('DATE_FORMAT(start_datetime, "%Y %m %d")');
    $this->db->where('rt.status', 1);
    $query = $this->db->get();
    $result = $query->result();

    if (!empty($result)) {
        foreach ($result as $val) {
            $val->lowData = ["status" => $val->lowData > 0 ? true : false, "count" => $val->lowData, "msg" => "", "title" => 'test'];
            $val->mediumData = ["status" => $val->mediumData > 0 ? true : false, "count" => $val->mediumData, "msg" => "", "title" => 'test'];
            $val->highData = ["status" => $val->highData > 0 ? true : false, "count" => $val->highData, "msg" => "", "title" => 'test'];
        }
    }

    return $result;
}

function get_staff_profile_listing_task($adminId) {
    $colowmn = array('rt.id', 'rt.task_name', 'rt.task_stage', 'rt.start_datetime', 'rt.status', "rt.relevant_task_note", "rt.task_piority");

    $this->db->select($colowmn);
    $this->db->select("(select group_concat(concat_ws('@__@@__@',concat_ws(' ',a.firstname,a.lastname),a.id)) from tbl_recruitment_task_recruiter_assign as sub_rtra inner join tbl_member as a on a.id = sub_rtra.recruiterId where sub_rtra.taskId = rt.id AND sub_rtra.primary_recruiter = 1 AND sub_rtra.archive=a.archive AND a.archive=0) as primary_recruiter");

    $this->db->from('tbl_recruitment_task as rt');
    $this->db->join('tbl_recruitment_task_stage as rts', 'rts.id = rt.task_stage', 'inner');
    $this->db->join('tbl_recruitment_task_recruiter_assign as rtra', 'rtra.taskId = rt.id AND rtra.archive=0 AND rtra.recruiterId = "' . $adminId . '"', 'inner');
    $this->db->select("(select group_concat(concat_ws(' ',ra.firstname,ra.middlename,ra.lastname) separator ' , ') as applicant_names from tbl_recruitment_task_applicant as rta inner join tbl_recruitment_applicant as ra on ra.id =rta.applicant_id and rta.archive=ra.archive where rt.id=rta.taskId and rta.archive=0 order by ra.firstname,ra.middlename,ra.lastname asc) as applicant_names ");
    $this->db->where('rt.status', 1);
    $this->db->order_by('rt.start_datetime', 'desc');
    $this->db->limit(10);
    $query = $this->db->get();
    return $query->result();
}

function get_staff_applicant_count($recruiterId = 0, $type = 'week', $mode = 0) {
    $response = ['processing' => 0, 'rejected' => 0, 'successful' => 0];
    if ($mode == 1) {
        $response = [
            'labels' => ['Successful', 'Processing', 'Rejected'],
            'datasets' => [[
                'data' => ['0', '0', '0'],
                'backgroundColor' => ['#2082ac', '#39a4d1', '#63bae9'] 
            ]
        ]
    ];
} else if ($mode == 2) {
    $response = ['successfull' => 0];
}


$checkType = in_array(strtolower($type), ['week', 'year', 'month']) ? strtolower($type) : 'week';
        /*  SELECT COUNT(CASE when status=1 THEN 1 ELSE NULL END) as processing , COUNT(CASE when status=2 THEN 1 ELSE NULL END) as rejected ,COUNT(CASE when status=3 THEN 1 ELSE NULL END) as successfull,now(), CURDATE() FROM tbl_recruitment_applicant 
          WHERE  duplicated_status=0
          and flagged_status=0
          and  archive=0 and YEARWEEK(updated,7) = YEARWEEK(CURDATE(),7)
          and  DATE(updated)<=CURDATE()
          and  recruiter='12';
          SELECT COUNT(CASE when status=1 THEN 1 ELSE NULL END) as processing , COUNT(CASE when status=2 THEN 1 ELSE NULL END) as rejected ,COUNT(CASE when status=3 THEN 1 ELSE NULL END) as successfull,now(), CURDATE() FROM tbl_recruitment_applicant WHERE  duplicated_status=0 and flagged_status=0 and  archive=0 and YEAR(updated) = YEAR(CURDATE()) and MONTH(updated) = MONTH(CURDATE()) and  DATE(updated)<=CURDATE() AND recruiter='12';
          SELECT COUNT(CASE when status=1 THEN 1 ELSE NULL END) as processing , COUNT(CASE when status=2 THEN 1 ELSE NULL END) as rejected ,COUNT(CASE when status=3 THEN 1 ELSE NULL END) as successfull,now(), CURDATE() FROM tbl_recruitment_applicant WHERE  duplicated_status=0 and flagged_status=0 and  archive=0 and YEAR(updated) = YEAR(CURDATE())  and  DATE(updated)<=CURDATE() AND recruiter='12'; */

          $this->db->select("COUNT(CASE when status=3 THEN 1 ELSE NULL END) as successfull,COUNT(CASE when status=1 THEN 1 ELSE NULL END) as processing , COUNT(CASE when status=2 THEN 1 ELSE NULL END) as rejected ", false);
          $this->db->where(['duplicated_status' => 0, 'flagged_status' => 0, 'archive' => 0, 'recruiter' => $recruiterId]);
          $this->db->where('DATE(updated) <=CURDATE()', NULL, false);
          if ($checkType == 'week') {
            $this->db->where(' YEARWEEK(updated,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(updated) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(updated) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
            $this->db->where('YEAR(updated) = YEAR(CURDATE())', NULL, false);
        }
        $query = $this->db->get('tbl_recruitment_applicant');
        if ($query->num_rows() > 0) {
            if ($mode == 1) {
                $response['datasets'][0]['data'] = array_values($query->row_array());
            } else if ($mode == 2) {
                $response = $query->row_array();
            } else {
                $response = $query->row_array();
            }
        }
        return $response;
    }

    public function get_recruiter_admin_list($reqData) {
        require_once APPPATH . 'Classes/recruitment/admin.php';
        $objAdmin = new AdminClass\AdminRecruit();

        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $available_column = array('id', 'name', 'status', 'created', 'round_robin_status');
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'a.id';
            $direction = 'desc';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        $src_columns = array('a.id', 'a.firstname', 'a.lastname', 'DATE_FORMAT(rs.created, "%d/%m/%Y")');
        /* if (!empty($filter->srch_box)) {
          $this->db->group_start();
          $search_value = $filter->srch_box;

          for ($i = 0; $i < count($src_columns); $i++) {
          $column_search = $src_columns[$i];
          if (strstr($column_search, "as") !== false) {
          $serch_column = explode(" as ", $column_search);
          $this->db->or_like($serch_column[0], $search_value);
          } else {
          $this->db->or_like($column_search, $search_value);
          }
          }
          $this->db->group_end();
      } */

      $colowmn = array('a.id', 'concat(a.firstname," ",a.lastname) as name', 'rs.status', 'rs.created', 'rs.round_robin_status');

      $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $colowmn)), false);
      $this->db->select("COALESCE((SELECT 1 FROM tbl_recruitment_staff_department_allocations as sub_rsda where sub_rsda.adminId=rs.adminId AND sub_rsda.status=1 AND sub_rsda.area_type=1 LIMIT 1),0) as is_area_allocated",false);
      $this->db->from('tbl_member as a');
      $this->db->join('tbl_department as d', 'd.id = a.department AND d.short_code = "internal_staff" AND d.archive = 0 AND a.archive = 0 AND a.status=1', 'inner');
//        $this->db->join('tbl_admin_email as ae', 'ae.adminId = a.id AND ae.primary_email = 1', 'inner');
//        $this->db->join('tbl_admin_phone as ap', 'ap.adminId = a.id AND ap.primary_phone = 1', 'left');
        // for show only recruiter AND rs.its_recruitment_admin = 1 
      $this->db->join('tbl_recruitment_staff as rs', 'rs.adminId = a.id AND rs.archive=0  AND rs.status=1', 'inner');

        // defualt globel status should be active (1)
      $this->db->where('a.status', 1);
        // defualt globel archive is 0
      $this->db->where('a.archive', 0);

      $this->db->order_by($orderBy, $direction);
      $this->db->limit($limit, ($page * $limit));

      $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
//        last_query();
      $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

      if ($dt_filtered_total % $limit == 0) {
        $dt_filtered_total = ($dt_filtered_total / $limit);
    } else {
        $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
    }

    $dataResult = $query->result();

    if (!empty($dataResult)) {
        foreach ($dataResult as $val) {

            $objAdmin->setAdminid($val->id);
            $x = $this->get_staff_recruitment_area($val->id);
            $val->area = (!empty($x['area'])) ? $x['area'] : '';
        }
    }

    $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'status' => true);

    return $return;
}

public function update_round_robin_status($reqData) {
    $response = [];
    $adminId = isset($reqData->id) ? $reqData->id : '';
    $status = isset($reqData->status) ? $reqData->status : '';
    $response = ['status' => false, 'error' => 'Something went wrong.'];

    if (!empty($adminId) && $status >= 0) {

        $dataUpdate = ['round_robin_status' => $status];
        $dd = $this->basic_model->update_records('recruitment_staff', $dataUpdate, array('adminId' => $adminId));

        if ($dd) {
            $response = ['status' => true];
        } else {
            $response = ['status' => false, 'error' => 'Something went wrong.'];
        }
    }
    return $response;
}

public function get_recruiter_applicant_list($reqData) {
        //
    $reqData = json_decode($reqData);
    $staffId = isset($reqData->staffId) ? $reqData->staffId : 0;
    $limit = isset($reqData->pageSize) ? $reqData->pageSize : '';
    $page = isset($reqData->page) ? $reqData->page : '';
    $sorted = isset($reqData->sorted) ? $reqData->sorted : array();
        //$filter = isset($reqData->filtered)?$reqData->filtered:'';
    $orderBy = '';
    $direction = '';

    $recruitment_applicant = TBL_PREFIX . 'recruitment_applicant';
    $available_column = array("id", "current_stage", "applicant_name");
    if (!empty($sorted)) {
        if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
            $orderBy = $sorted[0]->id;
            $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
        }
    } else {
        $orderBy = $recruitment_applicant . '.id';
        $direction = 'DESC';
    }
    $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

    $this->db->where($recruitment_applicant . ".archive" . ' =', 0);
    $this->db->where($recruitment_applicant . ".recruiter" . ' =', $staffId);
        //$this->db->where_in($recruitment_applicant . ".status",'1,3',false);
    $this->db->where_in($recruitment_applicant . ".status", '1', false);

        /* if (!empty($reqData->filtered->srch_value)) {
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
      } */

      $select_column = array($recruitment_applicant . ".id", $recruitment_applicant . ".current_stage", "CONCAT($recruitment_applicant.firstname,' ',$recruitment_applicant.middlename, ' ', $recruitment_applicant.lastname) AS applicant_name");

      $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
      $this->db->from($recruitment_applicant);

        /* $this->db->join('tbl_recruitment_job_position', 'tbl_recruitment_job_position.id = ' . $tbl_job . '.position AND tbl_recruitment_job_position.archive = 0', 'inner');
          $this->db->join('tbl_recruitment_job_category', 'tbl_recruitment_job_category.id = ' . $tbl_job . '.category AND tbl_recruitment_job_category.archive = 0', 'inner');
          $this->db->join('tbl_recruitment_job_employment_type', 'tbl_recruitment_job_employment_type.id = ' . $tbl_job . '.employment_type AND tbl_recruitment_job_employment_type.archive = 0', 'inner');
          $this->db->join('tbl_recruitment_applicant', 'tbl_recruitment_applicant.jobId = ' . $tbl_job . '.id AND tbl_recruitment_applicant.archive = 0 AND tbl_recruitment_applicant.status = 1', 'left'); */

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


        if (isset($reqData->filtered->account_allocated_type) && $reqData->filtered->account_allocated_type != '' && $reqData->filtered->account_allocated_type == 1) {
            $staff = isset($reqData->staffId) ? $reqData->staffId : '';
            $recruiterList_ = $this->get_recruiter_for_auto_assign_applicant($staff);
            $recruitor_rows = [];
            if (!empty($recruiterList_)) {
                foreach ($recruiterList_ as $val) {
                    $recruitor_rows[$val->id] = $val->applicant_cnt;
                }
            }
        }

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $val->allocate_to = '';
                if (!empty($recruitor_rows) && isset($reqData->filtered->account_allocated_type) && $reqData->filtered->account_allocated_type != '' && $reqData->filtered->account_allocated_type == 1) {
                    $alloted_recruiter = array_rand(array_flip(array_keys($recruitor_rows, min($recruitor_rows))), 1);
                    $modify_ary = pos_index_change_array_data($recruiterList_, 'id');
                    $val->allocate_to = array('value' => $alloted_recruiter, 'applicant_id' => $val->id, 'label' => $modify_ary[$alloted_recruiter]['memberName']);
                    $recruitor_rows[$alloted_recruiter] = $recruitor_rows[$alloted_recruiter] + 1;
                }
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count);
        return $return;
    }

    public function get_recruiter_name($post_data) {
        $query = isset($post_data->query) ? $post_data->query : '';
        $staff_id = isset($post_data->staff_id) ? $post_data->staff_id : '';

        $this->db->select("CONCAT(firstname,' ',middlename,' ',lastname) as memberName");
        $this->db->select(array('m.id'));

        $this->db->group_start();
        $this->db->or_where("(MATCH (firstname) AGAINST ('$query *'))", NULL, FALSE);
        $this->db->or_where("(MATCH (middlename) AGAINST ('$query *'))", NULL, FALSE);
        $this->db->or_where("(MATCH (lastname) AGAINST ('$query *'))", NULL, FALSE);
        $this->db->group_end();

        $this->db->where('m.archive', 0);
        $this->db->where('m.status', 1);
        $this->db->where('m.id!=', $staff_id);
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"', 'inner');
        $this->db->join('tbl_recruitment_staff as rs', 'rs.adminId = m.id AND rs.its_recruitment_admin=0 AND rs.status=1 AND rs.approval_permission =1 AND rs.archive=0', 'inner');
        $sql_query = $this->db->get(TBL_PREFIX . 'member as m');
        #last_query();
        $participant_rows = array();
        if (!empty($sql_query->result())) {
            foreach ($sql_query->result() as $val) {
                $participant_rows[] = array('label' => $val->memberName, 'value' => $val->id);
            }
        }
        return $participant_rows;
    }

    public function get_recruiter_for_auto_assign_applicant($staff_id) {
        $area_type = array();
        if ($staff_id != '' && $staff_id != 0) {
            $get_member_area = $this->basic_model->get_result('recruitment_staff_department_allocations', array('adminId' => $staff_id), $columns = array('area_type'), $order_by = array(), true);
            
            if(!empty($get_member_area)){
                $area_type = array_column($get_member_area, 'area_type');
                if (!empty($area_type))
                    $this->db->join('tbl_recruitment_staff_department_allocations as rsda', 'rsda.adminId = m.id AND rsda.area_type IN("' . implode(',', $area_type) . '")', 'INNER');
            }
        }

        $this->db->select(array("CONCAT(m.firstname,' ',m.middlename,' ',m.lastname) as memberName", "(select count('id') from tbl_recruitment_applicant where `tbl_recruitment_applicant`.`recruiter` = `m`.`id` AND `tbl_recruitment_applicant`.`archive` = 0 ) as applicant_cnt", 'm.id'));
        #$this->db->select(array('m.id'));

        $this->db->where('m.archive', 0);
        $this->db->where('m.status', 1);
        $this->db->where('m.id!=', $staff_id);
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"', 'INNER');
        $this->db->join('tbl_recruitment_staff as rs', 'rs.adminId = m.id AND rs.its_recruitment_admin=0 AND rs.status=1 AND rs.approval_permission =1 AND rs.archive=0', 'inner');
        #$this->db->join('tbl_recruitment_applicant', 'tbl_recruitment_applicant.recruiter = m.id AND tbl_recruitment_applicant.archive = 0 AND tbl_recruitment_applicant.status = 1', 'INNER');
        $this->db->group_by('m.id');
        $sql_query = $this->db->get(TBL_PREFIX . 'member as m');
        $recruitor_rows = array();
        return $sql_query->result();
    }

    public function get_recruiter_and_its_task_count($staff_id) {
        $this->db->select(array('m.id'));
        $this->db->where('m.archive', 0);
        $this->db->where('m.status', 1);
        $this->db->where('m.id!=', $staff_id);
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"', 'inner');
        $this->db->join('tbl_recruitment_staff as rs', 'rs.adminId = m.id AND rs.its_recruitment_admin=0 AND rs.status=1', 'inner');
        $recruiter_cnt_sql_query = $this->db->get(TBL_PREFIX . 'member as m');
        $cnt_1 = $recruiter_cnt_sql_query->num_rows();

        $this->db->select(array('rtra.id'));
        $this->db->where('rtra.recruiterId=', $staff_id);
        $this->db->join('tbl_member as m', 'm.id = rtra.recruiterId AND rtra.archive = 0 AND m.status =1', 'inner');
        $sql_query = $this->db->get(TBL_PREFIX . 'recruitment_task_recruiter_assign as rtra');
        $cnt_2 = $sql_query->num_rows();

        return array('recruiter_count' => $cnt_1, 'recruiter_task_count' => $cnt_2);
    }

    public function get_total_pending_task_count($staff_id) {
        $this->db->select("count(rt.id) as task_cnt", false);
        $this->db->from('tbl_member as m');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"', 'inner');
        $this->db->join('tbl_recruitment_staff as rs', 'rs.adminId = m.id  AND rs.archive=m.archive AND rs.status=1', 'inner');
        $this->db->join('tbl_recruitment_task_recruiter_assign as rtra', 'rtra.recruiterId = m.id  AND rtra.archive=m.archive AND rs.status=1', 'inner');
        $this->db->join('tbl_recruitment_task as rt', 'rt.id = rtra.taskId  AND rt.status=1', 'inner');
        $this->db->where('m.archive', 0);
        $this->db->where('m.status', 1);
        $this->db->where('m.id=', $staff_id);
        $query = $this->db->get();
        //last_query(1);die;
        if ($query->num_rows() > 0) {
            return $query->row_array()['task_cnt'];
        } else {
            return 0;
        }
    }

    public function disable_recruiter_task($staff_id) {
        if (!empty($staff_id)) {
            $tbl_1 = TBL_PREFIX . 'recruitment_task';

            $this->db->select(array($tbl_1 . ".id"));
            $this->db->from($tbl_1);
            $this->db->join('tbl_recruitment_task_recruiter_assign as rtra', 'tbl_recruitment_task.id = rtra.taskId AND rtra.recruiterId=' . $this->db->escape_str($staff_id, true) . '', 'INNER');
            $this->db->where(array('rtra.archive' => 0, 'tbl_recruitment_task.status' => 1));
            $query = $this->db->get();
            $task_rows = $query->result_array();
            $ids = '';
            if (!empty($task_rows)) {
                $ids = array_column($task_rows, 'id');
                $this->db->where_in('taskId', $ids);
                $this->basic_model->update_records('recruitment_task_recruiter_assign', array('archive' => 1), array('recruiterId' => $staff_id));

                // get number of recruiter as per task 
            }
        }
    }

    public function get_recruiter_area_detail_sub_query($type = 1, $search_value = '') {
        $search_value = addslashes($search_value);
        if ($type == 1) {
            $this->db->select("IFNULL(GROUP_CONCAT(CONCAT(COALESCE(sub_rd.id,''),'@__BREAKER__@',COALESCE(sub_rd.name,''),'@__BREAKER__@',COALESCE(sub_rsda.area_type,'')) SEPARATOR '@__@@__@'),'') as recuritment_label_and_area", false);
        } else if ($type == 2) {
            $this->db->select("GROUP_CONCAT(DISTINCT sub_rsda.adminId) as recuritment_label_and_area", false);
            $this->db->where("`sub_rd`.`name` LIKE '%" . $this->db->escape_str($search_value, true) . "%' ESCAPE '!'");
        }
        $this->db->from('tbl_recruitment_staff_department_allocations as sub_rsda');
        $this->db->join('tbl_recruitment_department as sub_rd', 'sub_rd.id = sub_rsda.allocated_department', 'inner');
        $this->db->where('sub_rsda.adminId=rs.adminId', null, false);
        return $subQuery = $this->db->get_compiled_select();
    }

}
