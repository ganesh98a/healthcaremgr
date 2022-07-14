<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Finanace_shift_payroll_model extends CI_Model
{

    public function __construct()
    {
        // Call the CI_Model constructor
        parent::__construct();
    }

    private function get_work_area_sub_query()
    {
        $this->db->select('sub_fft.name');
        $this->db->from('tbl_funding_type sub_fft');
        $this->db->where('sub_fft.id=s.funding_type',null,false);
        $this->db->where('sub_fft.archive', 0);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_shift_member_sub_query()
    {
        $this->db->select("REPLACE(concat(COALESCE(sub_m.firstname,''),' ',COALESCE(sub_m.middlename,''),' ',COALESCE(sub_m.lastname,'')),'  ',' ')", false);
        $this->db->from('tbl_member sub_m');
        $this->db->join("tbl_shift_member as sub_sm", "sub_sm.memberId=sub_m.uuid AND sub_sm.status=3 AND sub_sm.archive = 0", "inner");
        $this->db->join("tbl_department as sub_d", "sub_d.id=sub_m.department AND sub_d.short_code='external_staff' and sub_d.archive=sub_m.archive", "inner");
        $this->db->where('sub_sm .shiftId=s.id');
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_shift_participant_sub_query()
    {
        $this->db->select("REPLACE(concat(COALESCE(sub_p.firstname,''),' ',COALESCE(sub_p.middlename,''),' ',COALESCE(sub_p.lastname,'')),'  ',' ')", false);
        $this->db->from('tbl_participant as sub_p');
        $this->db->join("tbl_shift_participant as sub_sp", "sub_sp.participantId=sub_p.id AND sub_sp.status=1", "inner");
        $this->db->where('sub_sp.shiftId=s.id');
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }
    public function get_shift_participant_sub_query_by_other(){
        return $this->get_shift_participant_sub_query();
    }
    public function get_shift_site_sub_query_by_other(){
        return $this->get_shift_site_sub_query();
    }

    private function get_shift_site_sub_query()
    {
        $this->db->select("sub_os.site_name", false);
        $this->db->from('tbl_organisation_site as sub_os');
        $this->db->join("tbl_shift_site as sub_ss", "sub_ss.siteId=sub_os.id AND sub_os.status=1 AND sub_os.archive=0", "inner");
        $this->db->where('sub_ss.shiftId=s.id');
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_shift_house_sub_query()
    {
        $this->db->select("sub_h.name", false);
        $this->db->from('tbl_house as sub_h');
        $this->db->join("tbl_shift_users as sub_su", "sub_su.user_for=sub_h.id AND sub_su.user_type=7 AND sub_h.archive=0", "inner");
        $this->db->where('sub_su.shiftId=s.id');
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_shift_org_or_sub_org_sub_query($orgType=1)
    {
        $user_type = $orgType ==1 ? 4 :5; //org and sub org
        $this->db->select("sub_o.name", false);
        $this->db->from('tbl_organisation as sub_o');
        $this->db->join("tbl_shift_users as sub_su", "sub_su.user_for=sub_o.id AND sub_su.user_type='".$user_type."' AND sub_o.archive=0", "inner");
        if($orgType==1){
            $this->db->where("sub_o.parent_org",0);
        }else if($orgType==1){
            $this->db->where("sub_o.parent_org !=",0);
        }
        $this->db->where('sub_su.shiftId=s.id');
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_shift_user_sub_query()
    {
        $orgSubQuery=$this->get_shift_org_or_sub_org_sub_query(1);
        $subOrgSubQuery=$this->get_shift_org_or_sub_org_sub_query(2);
        $houseSubQuery=$this->get_shift_house_sub_query(2);
        $shiftParticipantSubQuery = $this->get_shift_participant_sub_query();
        $shiftSiteSubQuery = $this->get_shift_site_sub_query();
       return "CASE 
       WHEN s.booked_by=2 OR s.booked_by=3 THEN 
       (" . $shiftParticipantSubQuery . ") 
       WHEN s.booked_by=1 THEN 
       (" . $shiftSiteSubQuery . ") 
       WHEN s.booked_by=4 THEN 
       (" . $orgSubQuery . ") 
       WHEN s.booked_by=5 THEN 
       (" . $subOrgSubQuery . ") 
       WHEN s.booked_by=7 THEN 
       (" . $houseSubQuery . ") 
       ELSE '' END";
    }
    
    private function get_participant_gender_subQuery()
    {
        $this->db->select("CASE WHEN sub_p.gender=1 THEN 'male' WHEN sub_p.gender=2 THEN 'female' ELSE '' END", false);
        $this->db->from('tbl_participant as sub_p');
        $this->db->join("tbl_shift_participant as sub_sp", "sub_sp.participantId=sub_p.id AND sub_sp.status=1", "inner");
        $this->db->where('sub_sp.shiftId=s.id');
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }
    private function shift_for_status_gender_query(){
        $this->load->model(['finance/Finance_common_model']);
        $participaintQuery = $this->get_participant_gender_subQuery();
        return "CASE  WHEN s.booked_by=2 OR s.booked_by=3 THEN (".$participaintQuery.") ELSE '' END ";
    }
    
    function get_shift_list($reqData, $extraPamams = [])
    {
        $all_shift_export_to_csv = $extraPamams["all_shift_export_to_csv"] ?? false;
        
        $workAreaSubQuery = $this->get_work_area_sub_query();
        $shiftMemberSubQuery = $this->get_shift_member_sub_query();
        /* $shiftParticipantSubQuery = $this->get_shift_participant_sub_query();
        $shiftSiteSubQuery = $this->get_shift_site_sub_query(); */
        $shiftuserSubQuery = $this->get_shift_user_sub_query();
        $shiftParticipantGenderSubQuery = $this->shift_for_status_gender_query();
        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        $src_columns = array('shift_id', 'booked_by', 'shfit_date_format', 'shfit_status', 'amount',/*  'variance', */ 'total_hours', 'work_area_type', 'completed_by', 'shift_for');
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

        $available_columns = array("shift_id","booked_by","shfit_date_format","shfit_status", "amount",
                                   "total_hours","work_area_type","completed_by", "shift_for", "booked_gender"
                                );
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_columns)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 's.id';
            $direction = 'DESC';
        }

        if (!empty($filter->filter_by)) { }

        if (!empty($filter->start_date) && empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(s.shift_date, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
        } elseif (!empty($filter->start_date) && !empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(s.shift_date, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
            $this->db->where('DATE_FORMAT(s.shift_date, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        } elseif (!empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(s.shift_date, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        }


        /*  if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
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
 */

        $select_column =
            array(
                "s.id as shift_id",
                "s.booked_by",
                "DATE_FORMAT(s.shift_date,'%d/%m/%Y') as shfit_date_format",
                "CASE WHEN s.status=6 THEN 'completed' ELSE 'pending' END as shfit_status",
                "CAST(s.price as DECIMAL(10,2)) as amount",
                "'0' as variance",
                "CASE 
            WHEN time_format(timediff(s.end_time,s.start_time),'%H')>0 && time_format(timediff(s.end_time,s.start_time),'%i') >0 THEN  time_format(timediff(s.end_time,s.start_time),'%Hhrs %imin') 
            WHEN time_format(timediff(s.end_time,s.start_time),'%H')>0 && time_format(timediff(s.end_time,s.start_time),'%i') <=0 THEN time_format(timediff(s.end_time,s.start_time),'%Hhrs') 
            WHEN time_format(timediff(s.end_time,s.start_time),'%H')<=0 && time_format(timediff(s.end_time,s.start_time),'%i') >0 THEN time_format(timediff(s.end_time,s.start_time),'%imin') 
            WHEN time_format(timediff(s.end_time,s.start_time),'%H')<=0 && time_format(timediff(s.end_time,s.start_time),'%i') <=0 THEN time_format(timediff(s.end_time,s.start_time),'%Hhrs') 
            ELSE '0hrs' 
            END as total_hours",
                "(" . $workAreaSubQuery . ") as work_area_type",
                "(" . $shiftMemberSubQuery . ") as completed_by",
                $shiftuserSubQuery." as shift_for",
            "CASE WHEN s.booked_by=2 OR s.booked_by=3 THEN 'participant' 
            WHEN s.booked_by=1 then 'site'  WHEN s.booked_by=7 then 'house' WHEN s.booked_by=4 then 'org'  WHEN s.booked_by=5 then 'sub_org' ELSE '' END as booked_from",
            $shiftParticipantGenderSubQuery." as booked_gender"
            );

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_shift  as s');
        $this->db->where('s.status', 6);
        $this->db->order_by($orderBy, $direction);

        if(!$all_shift_export_to_csv){
            $this->db->limit($limit, ($page * $limit));
        }
        
        /* it is useed for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        
        if(!$all_shift_export_to_csv){
            if ($dt_filtered_total % $limit == 0) {
                $dt_filtered_total = ($dt_filtered_total / $limit);
            } else {
                $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
            }
        }

        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true);
        return $return;
    }

    public function check_payroll_exemption_exists_between_date_range($orgId = 0, $fromDate = '', $toDate = '')
    {
        if (!empty($orgId) && !empty($fromDate) && !empty($toDate)) {

            $this->db->select(['fpeo.id']);
            $this->db->from('tbl_finance_payroll_exemption_organisation fpeo');
            $this->db->where(['organisation_id' => $orgId, 'archive' => 0]);
            $this->db->group_start();
            $this->db->where("('".$fromDate."' between valid_from and valid_to)", null, false);
            $this->db->or_where("('".$toDate."' between valid_from and valid_to)", null, false);
            $this->db->group_end();
            $query = $this->db->get();
            return ['status' => true, 'data_count' => $query->num_rows()];
        } else {
            return ['status' => false, 'msg' => 'In valid request.'];
        }
    }

    private function get_org_name_sub_query()
    {
        $this->db->select("sub_o.name", false);
        $this->db->from('tbl_organisation as sub_o');
        $this->db->where('sub_o.id=fpeo.organisation_id AND fpeo.archive=sub_o.archive');
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }
    public function get_payroll_exemption_list($reqData)
    {
        $orgSubQuery = $this->get_org_name_sub_query();
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        //$cronCall = isset($reqData->cronCall) && $reqData->cronCall==1 ? true: false;

        $src_columns = array('fin_file_title', 'fin_valid_from', 'fin_valid_until', 'days_until_expire', 'fin_status', 'org_name');
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
        /*  if($cronCall){
             $this->db->where('fin_valid_until<=',29);   
        } */
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $available_columns = array( "organisation_id", "fin_file_title", "fin_valid_from", "fin_valid_until",
                                    "fin_id", "days_until_expire", "org_name"
                             );
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_columns)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'fpeo.id';
            $direction = 'DESC';
        }

        if (!empty($filter->filter_by)) { }

        if (!empty($filter->start_date) && empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fpeo.valid_from, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
        } elseif (!empty($filter->end_date) && empty($filter->start_date)) {
            $this->db->where('DATE_FORMAT(fpeo.valid_to, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        } elseif (!empty($filter->start_date) && !empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fpeo.valid_from, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
            $this->db->where('DATE_FORMAT(fpeo.valid_to, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        } else if ((empty($filter->start_date) && empty($filter->end_date)) || (!isset($filter->start_date) && !isset($filter->end_date))) {
            $this->db->where('fpeo.valid_from <=', date(DB_DATE_FORMAT));
        }

        $select_column =
            array(
                "substring_index( group_concat(fpeo.file_title order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@' ),'@@__BREAKER__@@',1) as  fin_file_title",
                "DATE_FORMAT(substring_index(group_concat(fpeo.valid_from order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1),'%d/%m/%Y') as  fin_valid_from",
                "DATE_FORMAT(substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1),'%d/%m/%Y') as fin_valid_until",
                "substring_index(group_concat(fpeo.id order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1) as  fin_id",
                "datediff(substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1),'" . date(DB_DATE_FORMAT) . "') as  days_until_expire",
                "CASE 
            WHEN substring_index(group_concat(fpeo.status order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1) = 2 THEN 'inactive'
            WHEN substring_index(group_concat(fpeo.status order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1) = 1 && datediff(substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1),'" . date(DB_DATE_FORMAT) . "')>=30 THEN 'active'
            WHEN substring_index(group_concat(fpeo.status order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1) = 1 && datediff(substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1),'" . date(DB_DATE_FORMAT) . "')< 30 &&  datediff(substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1),'" . date(DB_DATE_FORMAT) . "')>= 0 THEN 'expired soon'
            WHEN substring_index(group_concat(fpeo.status order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1) = 1 && datediff(substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1),'" . date(DB_DATE_FORMAT) . "')<0 THEN 'expired'
            ELSE ''
            END as fin_status",
                "fpeo.organisation_id",
                "fpeo.id",
                "CASE WHEN fpeo.organisation_id>0 THEN (" . $orgSubQuery . ") ELSE '' END as org_name"
            );

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_finance_payroll_exemption_organisation as fpeo');
        $this->db->join('tbl_organisation as o','o.id=fpeo.organisation_id and o.payroll_tax=1 and o.archive=0','inner');
        $this->db->where('fpeo.archive', 0);

        $this->db->order_by($orderBy, $direction);
        $this->db->group_by('fpeo.organisation_id');

        $this->db->limit($limit, ($page * $limit));
        /* it is useed for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
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

    public function set_inactive_payroll_exemption($payrollExemptionIds = [])
    {
        $payrollExemptionIds = !empty($payrollExemptionIds) && is_array($payrollExemptionIds) ? array_filter($payrollExemptionIds) : [0];
        $this->db->set('status', 2);
        $this->db->set('valid_to', DATE_CURRENT);
        $this->db->where_in('id', $payrollExemptionIds);
        $this->db->where('valid_to >=', DATE_CURRENT);
        $this->db->where('archive', 0);
        $this->db->update('tbl_finance_payroll_exemption_organisation');
        $row = $this->db->affected_rows();
        if ($row) {
            $result = ['status' => true];
        } else {
            $result = ['status' => false, 'error' => 'Invalid Request.'];
        }
        return $result;
    }

    private function payroll_exemption_notes_sub_query($column = "notes")
    {
        $this->db->select($column);
        $this->db->from('tbl_finance_payroll_exemption_organisation_notes fpeon');
        $this->db->where("fpeon.fpeo_id=fpeo.id AND fpeon.archive=fpeo.archive");
        $this->db->limit('1');
        return $this->db->get_compiled_select();
    }

    public function get_payroll_exemption_history_list($reqData)
    {
        //$org_sub_query = $this->get_org_name_sub_query();
        $noteSubQuery = $this->payroll_exemption_notes_sub_query();
        $noteIdSubQuery = $this->payroll_exemption_notes_sub_query('id');
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orgId = isset($filter->orgId) && !empty($filter->orgId) ? $filter->orgId : 0;
        $orderBy = '';
        $direction = '';

        /*  $src_columns = array('fin_file_title', 'fin_valid_from','fin_valid_until','days_until_expire','fin_status','org_name');
        if (isset($filter->search) && $filter->search!='') {
            
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if(!empty($filter->filter_by) && $filter->filter_by!='all' && $filter->filter_by != $column_search){
                    continue;
                }
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
        $queryHavingData=$this->db->get_compiled_select();
        $queryHavingData= explode('WHERE',$queryHavingData);
        $queryHaving = isset($queryHavingData[1])? $queryHavingData[1]:''; 
        */

        $available_columns = array("fin_file_title", "fin_valid_from",  "fin_valid_until", "fin_file",
                                   "fin_id", "fin_note", "fin_note_id"
                            );
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id)  && in_array($sorted[0]->id, $available_columns)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'fpeo.id';
            $direction = 'DESC';
        }

        /* if (!empty($filter->filter_by)) {
            
        } */

        /* if (!empty($filter->start_date) && empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fpeo.valid_from, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
        }elseif (!empty($filter->end_date) && empty($filter->start_date)) {
            $this->db->where('DATE_FORMAT(fpeo.valid_from, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        }elseif (!empty($filter->start_date) && !empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fpeo.valid_from, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
            $this->db->where('DATE_FORMAT(fpeo.valid_from, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        } else if ((empty($filter->start_date) && empty($filter->end_date)) || (!isset($filter->start_date) && !isset($filter->end_date))){
            $this->db->where('fpeo.valid_from <=', date(DB_DATE_FORMAT));
        } */

        $select_column =
            array(
                "fpeo.file_title as fin_file_title",
                "DATE_FORMAT(fpeo.valid_from,'%d/%m/%Y') as fin_valid_from",
                "DATE_FORMAT(fpeo.valid_to,'%d/%m/%Y') as fin_valid_until",
                "fpeo.file_path as fin_file",
                "fpeo.id as fin_id",
                "(" . $noteSubQuery . ") as fin_note",
                "(" . $noteIdSubQuery . ") as fin_note_id",
            );

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_finance_payroll_exemption_organisation as fpeo');
        $this->db->join('tbl_organisation as o','o.id=fpeo.organisation_id and o.payroll_tax=1 and o.archive=0','inner');
        $this->db->where('fpeo.archive', 0);
        $this->db->where('fpeo.organisation_id', $orgId);

        $this->db->order_by($orderBy, $direction);

        $this->db->limit($limit, ($page * $limit));
        /* it is used for subquery filter */
        /* if(!empty($queryHaving)){
            $this->db->having($queryHaving);
        } */

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
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

    public function update_payroll_exemption_note($reqData, $adminId = 0)
    {
        $response = ['status' => false, 'error' => 'Invalid Request.'];
        $noteId = isset($reqData->note_id) && !empty($reqData->note_id) ? $reqData->note_id : '';
        $notes = isset($reqData->note) ? $reqData->note : '';
        $payrollExemptionId = isset($reqData->exemption_id) ? $reqData->exemption_id : '';

        if (!empty($payrollExemptionId) && $payrollExemptionId > 0 && !empty($notes) && !empty($adminId)) {

            $dataNote = ['notes' => $notes];
            if (empty($noteId)) {
                $dataNote['created'] = DATE_TIME;
                $dataNote['created_by'] = $adminId;
                $dataNote['archive'] = 0;
                $dataNote['fpeo_id'] = $payrollExemptionId;
                $dd =  $this->basic_model->insert_records('finance_payroll_exemption_organisation_notes', $dataNote);
            } else {
                $dd =  $this->basic_model->update_records('finance_payroll_exemption_organisation_notes', $dataNote, array('archive' => 0, 'fpeo_id' => $payrollExemptionId));
            }

            if ($dd) {
                $response = ['status' => true];
            } else {
                $response = ['status' => false, 'error' => 'Invalid Request.'];
            }
        }
        return $response;
    }
    public function get_org_payroll_exemption_current_status_by_current_date($orgId = 0, $extraParms = [])
    {
        $cronCall = isset($extraParms['cron_call']) && $extraParms['cron_call'] == 1 ? true : false;
        $withinInMonthCall = !$cronCall && isset($extraParms['within_in_month_count_call']) && $extraParms['within_in_month_count_call'] == 1 ? true : false;
        $returnType = isset($extraParms['return_type']) ? $extraParms['return_type'] : 1;
        $less= '< 30';
        if ($cronCall) {
            $less= '<=30';
        }
        $select_column =
            array(

                "datediff(substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1),'" . date(DB_DATE_FORMAT) . "') as  days_until_expire",
                "CASE 
            WHEN substring_index(group_concat(fpeo.status order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1) = 2 THEN 'inactive'
            WHEN substring_index(group_concat(fpeo.status order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1) = 1 && datediff(substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1),'" . date(DB_DATE_FORMAT) . "')>=30 THEN 'active'
            WHEN substring_index(group_concat(fpeo.status order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1) = 1 && datediff(substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1),'" . date(DB_DATE_FORMAT) . "')< 30 &&  datediff(substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1),'" . date(DB_DATE_FORMAT) . "')>= 0 THEN 'expired soon'
            WHEN substring_index(group_concat(fpeo.status order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1) = 1 && datediff(substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1),'" . date(DB_DATE_FORMAT) . "')<0 THEN 'expired'
            ELSE ''
            END as fin_payroll_status",
                "fpeo.organisation_id",
                "fpeo.id",
                "substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1) as fin_valid_until",
                "CASE WHEN (substring_index(group_concat(fpeo.status order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'), '@@__BREAKER__@@', 1) = 2  || (substring_index(group_concat(fpeo.status order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'), '@@__BREAKER__@@', 1) = 1 && datediff(substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'), '@@__BREAKER__@@', 1), '" . date(DB_DATE_FORMAT) . "') ". $less.")) THEN 
            COALESCE((SELECT 1 FROM tbl_finance_payroll_exemption_organisation as sub_fpeo WHERE `sub_fpeo`.`organisation_id` = `fpeo`.`organisation_id` AND `sub_fpeo`.`archive` = `fpeo`.`archive` AND  `sub_fpeo`.`valid_from` > substring_index(group_concat(fpeo.valid_to order by fpeo.valid_from DESC SEPARATOR '@@__BREAKER__@@'),'@@__BREAKER__@@',1) LIMIT 1),0) END as next_term_available"
            );
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_finance_payroll_exemption_organisation as fpeo');
        $this->db->join('tbl_organisation as o','o.id=fpeo.organisation_id and o.payroll_tax=1 and o.archive=0','inner');
        $this->db->where('fpeo.archive', 0);
        $this->db->where('fpeo.valid_from <=', date(DB_DATE_FORMAT));
        $this->db->group_by('fpeo.organisation_id');
        if ($cronCall) {
            $limit = isset($extraParms['limit_size']) ? $extraParms['limit_size'] : 10;
            $page = isset($extraParms['current_page']) ? $extraParms['current_page'] : 0;
            $this->db->limit($limit, ($page * $limit));
            //$this->db->having('days_until_expire<30 ');
            $this->db->having('(days_until_expire=30 or days_until_expire=15 or days_until_expire=1) and next_term_available=0');
        } elseif ($withinInMonthCall) {
            $this->db->limit(1);
            $this->db->having('(days_until_expire>=0 and days_until_expire<30)');
            $query = $this->db->get();
            $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
            return $dt_filtered_total;
        } else {
            $this->db->where('fpeo.organisation_id', $orgId);
        }
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $returnType == 2 ? $query->result_array() : $query->row_array();
        } else {
            return [];
        }
    }
    public function get_payroll_fetch($requestData)
    {
        $startDate = isset($requestData->filtered->from_date) ? DateFormate($requestData->filtered->from_date, DB_DATE_FORMAT) : '';
        $endDate = isset($requestData->filtered->to_date) ? DateFormate($requestData->filtered->to_date, DB_DATE_FORMAT) : '';
        $params = array('company_id' => KEYPAY_DEFAULT_COMPANY_ID);
        $this->load->library('KeyPayFetch', $params);
        $res = $this->keypayfetch->get_payroll_gross_to_net_tax(['fromDate' => $startDate, 'toDate' => $endDate, 'groupBy' => 'DefaultLocation']);
        if ($res['status']) {
            $tempData = $res['data'];
        } else {
            //return $res;
            $tempData = [];
        }
                        $temp = [
                            ['number' => '1', 'des' => 'Total Hours', 'amount' => $tempData['totalHours'] ?? 0],
                            ['number' => '2', 'des' => 'Total Gross Earnings', 'amount' => $tempData['totalGrossEarnings'] ?? 0],
                            ['number' => '2a)', 'des' => 'Additional Payments (Opening Balance)', 'amount' => $tempData['totalOpenningBalance'] ?? 0],
                            ['number' => '2b)', 'des' => 'ADO Taken', 'amount' => $tempData['totalAdo'] ?? 0],
                            ['number' => '2c)', 'des' => 'Annual Leave Taken', 'amount' => $tempData['totalAnnualLeaveTaken'] ?? 0],
                            ['number' => '2d)', 'des' => 'Bonus', 'amount' => $tempData['totalBonus'] ?? 0],
                            ['number' => '2e)', 'des' => 'Casual - Evening Shift', 'amount' => $tempData['totalCasualEveningShift'] ?? 0],
                            ['number' => '2f)', 'des' => 'Casual - Ordinary Hours', 'amount' => $tempData['totalCasualOrdinaryHours'] ?? 0],
                            ['number' => '2g)', 'des' => 'Community Service Leave Taken', 'amount' => $tempData['totalCommunityServiceLeaveTaken'] ?? 0],
                            ['number' => '2h)', 'des' => 'Leave Loading', 'amount' => $tempData['totalLeaveLoading'] ?? 0],
                            ['number' => '3', 'des' => 'Total Super Contribution', 'amount' => $tempData['totalSuperContribution'] ?? 0],
                            ['number' => '4', 'des' => 'Total Gross Earnings plus Super', 'amount' => $tempData['totalGrossPlusSuper'] ?? 0],
                            ['number' => '5', 'des' => 'Total Net Earnings', 'amount' => $tempData['totalNetEarnings'] ?? 0],
                        ];
                        return ['status' => true, 'data' => $temp];
    }

    private function get_payroll_graph_data_exists_today_date()
    {
        $data = [];
        $this->db->select(['month_date', 'total_gross', "DATE_FORMAT(month_date,'%b_%Y') as month_year", "DATE_FORMAT(month_date,'%b') as month"]);
        $this->db->from('tbl_finance_payroll_keypay_graph_data');
        $this->db->where('archive', 0);
        $this->db->where("DATE(created)", DATE_CURRENT);
        $this->db->order_by('month_date');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result_array();
        }
        return $data;
    }
    public function get_payroll_graph_fetch()
    {
        $params = array('company_id' => KEYPAY_DEFAULT_COMPANY_ID);
        $this->load->library('KeyPayFetch', $params);
        $res = $this->get_payroll_graph_data_exists_today_date();
        if (empty($res)) {
            $resKey = $this->keypayfetch->get_payroll_gross_to_net_tax_by_last_three_year();
            if (!$resKey['status']) { } else {
                $res = $this->get_payroll_graph_data_exists_today_date();
            }
        }
        $chartData = [];
        $chartData[] = ["Qty.", "This Year", "Last Year", "Two Years Ago"];
        $years = $this->keypayfetch->get_current_finacial_year_data(3);
        rsort($years);
        $monthArray = [];
        foreach($years as $keyY => $valY){
            $tempmonth = get_interval_month_wise($valY['from_date'],$valY['to_date']);
            $monthArray = array_merge($monthArray,$tempmonth);
        }
        $yearsData = array_merge(['qty'], $years);
        //pr($yearsData);
        $resMonthYearKeyData = pos_index_change_array_data($res, 'month_year');
        if (!empty($res)) {
        //pr($resMonthYearKeyData);
        $temp = [];
            foreach ($monthArray as $rowMonthIndex) {
                 $rowMonth =  date('M',strtotime($rowMonthIndex['from_date']));
                 $rowYearVal = date('Y',strtotime($rowMonthIndex['from_date']));
                 if(!isset($temp[$rowMonth])){
                     $temp[$rowMonth][] = $rowMonth;
                 }
                 $temp[$rowMonth][] = isset($resMonthYearKeyData[$rowMonth . '_' . $rowYearVal]) ? (float)$resMonthYearKeyData[$rowMonth . '_' . $rowYearVal]['total_gross'] : 0;

                /* foreach ($yearsData as $rowYearKey => $rowYearVal) {
                    if ($rowYearVal == 'qty') {
                        $temp[$rowYearKey] = $rowMonth;
                        continue;
                    }
                    $temp[$rowYearKey] = isset($resMonthYearKeyData[$rowMonth . '_' . $rowYearVal]) ? $resMonthYearKeyData[$rowMonth . '_' . $rowYearVal]['total_gross'] : 0;
                } */    
            }
         $chartData =  array_merge($chartData,array_values($temp));
        } else {
            $chartData[] =  ['', 0, 0,0];
        }
    
        return ['status' => true, 'data' => $chartData];
    }

    public function send_renewal_email_payroll($orgId = 0, $extraParms = [])
    {
        $this->load->model('Finance_common_model');
        $orgRenewalEmail = $this->get_org_payroll_exemption_current_status_by_current_date($orgId);
        $orgDetails = $this->Finance_common_model->get_organisation_details_by_id($orgId);
        if (isset($orgRenewalEmail['days_until_expire'])   && $orgRenewalEmail['days_until_expire'] < 30  &&  isset($orgRenewalEmail['next_term_available']) && $orgRenewalEmail['next_term_available'] == 0  && isset($orgDetails['email'])) {
            $contact_name = !empty($orgDetails['contact_name']) ?  $orgDetails['contact_name'] : $orgDetails['org_name'];
            $email =  $orgDetails['email'];
            $expiredOn =  DateFormate($orgRenewalEmail['fin_valid_until'], 'd/m/Y');
            $res = send_reminder_renewal_payroll_exemption_email((object) ['to_email' => $email, 'org_contact_name' => $contact_name, 'expire_untill' => $expiredOn]);
            if ($res) {
                $return = ['status' => true, 'msg' => 'Mail send Successfuly.'];
            } else {
                $return = ['status' => false, 'error' => 'Something went wrong. please try again.'];
            }
        } else if (isset($orgRenewalEmail['next_term_available']) && $orgRenewalEmail['next_term_available'] == 1) {
            $return = ['status' => false, 'error' => 'Payroll exemption is already scheduled for that.', 'refresh_data' => true];
        } else {
            $return = ['status' => false, 'error' => 'Something went wrong. please try again.'];
        }
        return $return;
    }

    public function get_payroll_exemption_expire_within_month()
    {
        $res = $this->get_org_payroll_exemption_current_status_by_current_date(0, ['within_in_month_count_call' => 1]);
        return ['status' => true, 'data' => $res];
    }

    public function get_total_payroll_exeption_current_financial_year()
    {
        $params = array('company_id' => KEYPAY_DEFAULT_COMPANY_ID);
        $this->load->library('KeyPayFetch', $params);
        $data = $this->keypayfetch->get_total_payroll_current_finacial_year_to_current_date_data();
        $res = isset($data['status']) && $data['status'] == true ? $data['total_data'] : 0;
        return ['status' => true, 'data' => (string) $res];
    }

    public function get_shfit_queries_amount($viewType = 'week')
    {
        $params = array('company_id' => KEYPAY_DEFAULT_COMPANY_ID);
        $this->load->library('KeyPayFetch', $params);
        $data = $this->keypayfetch->get_total_payrun_out($viewType);
        $payoutTotal = isset($data['status']) && $data['status'] == true ? $data['total_data'] : 0;
        $data_payin = $this->get_shfit_total_spent_by_work_area($viewType,2);
        $payoutIn =  isset($data_payin['status']) && $data_payin['status'] == true ? $data_payin['data'] : 0;
        $total = $payoutTotal + $payoutIn;
        return ['status' => true, 'data' => [$payoutTotal, $payoutIn, $total]];
    }

    public function get_shfit_total_spent_by_work_area($viewType = 'week',$type=1)
    {
        $viewTypeData = get_shfit_payroll_filter($viewType);
        $fromDate = $viewTypeData['fromDate'];
        $toDate = $viewTypeData['toDate'];
        if($type ==1 ){
            $this->db->select([
                "CAST(sum(CASE WHEN ft.name ='ndis' THEN s.price ELSE 0 END) as DECIMAL(10,2)) as ndis_toatl",
                "CAST(sum(CASE WHEN ft.name ='welfare' THEN s.price ELSE 0 END) as DECIMAL(10,2)) as welfare_toatl",
                "CAST(sum(CASE WHEN ft.name ='private' THEN s.price ELSE 0 END) as DECIMAL(10,2)) as private_toatl"
            ]);
        }else if($type ==2 ){
            $this->db->select([
                " CAST(sum(s.price) as DECIMAL(10,2)) as total",
            ]);
        }
        $this->db->from('tbl_shift s');
        $this->db->join('tbl_funding_type ft', 'ft.id=s.funding_type and ft.archive=0 and s.status=6', 'inner');
        $this->db->where("date(s.start_time)>='" . $fromDate . "' AND date(s.start_time)<='" . $toDate . "'", NULL, FALSE);
        $this->db->group_by('s.status');
        $query = $this->db->get();
        if($type==1){
            $ndisTotal = 0;
            $welfareTotal = 0;
            $privateTotal = 0;
            if ($query->num_rows() > 0) {
                $res = $query->row_array();
                $ndisTotal = $res['ndis_toatl'];
                $welfareTotal = $res['welfare_toatl'];
                $privateTotal = $res['private_toatl'];
            }
            return ['status' => true, 'data' => [$ndisTotal, $welfareTotal,$privateTotal]];
        }else if($type==2){
            return ['status' => true ,'data' => $query->num_rows() > 0 ? $query->row()->total:0];
        }
    }
}
