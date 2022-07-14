<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Group_booking_model extends Basic_model
{

    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'recruitment_applicant_applied_application';
        $this->load->model('recruitment/Recruitment_applicant_model');
        
    }

    public function get_applicants_for_group_booking($reqData, $adminId, $filter_condition = '') {
        $limit = $reqData->pageSize ?? 9999;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';
        $src_columns = array('raa.id','concat(p.firstname," ",p.lastname) as FullName', 'pe.email', 'rjp.title', 'ra.hired_as');
        $available_column = array(
                                'applicant_id', 'created', 'id','FullName','status', 'email', 'phone', 'appId',
                                'jobId', "stage", "sub_stage", "referrer_url", "channelId",
                                "job_position", 'application_id', 'recruiter','application_process_status', 'hired_as'
                            );
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                if($orderBy == "recruiter")
                    $orderBy = "recruiter_name";
                else if($orderBy == "applied_through")
                    $orderBy = "referrer_url";

                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'raa.created';
            $direction = 'DESC';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        # previous search where we had recruiters filter
        if (!empty($filter->recruiter_val)) {
            $this->db->where('raa.recruiter', $filter->recruiter_val);
        }

        # text search
        if (!empty($filter->search)) {
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

        $select_column = array(
            'ra.id as applicant_id', 'raa.created', 'raa.id','concat(p.firstname," ",p.lastname) as FullName',
            'raa.status', 'pe.email', 'pp.phone', 'ra.appId',
            'raa.jobId', "(concat(rtb.title)) as stage", "(concat(rs.stage,' - ',rs.title)) as sub_stage", "raa.referrer_url", "raa.channelId",
            "rjp.title as job_position",
            'raa.id AS application_id, raa.recruiter,raa.application_process_status',
            'ra.hired_as','ra.flagged_status'
        );

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select('(select concat(m.firstname," ",m.lastname) as recruiter_name from tbl_member as m inner join tbl_department as d on d.id = m.department AND d.short_code = "internal_staff" where m.id = raa.recruiter) as recruiter_name');

        $this->db->select("(
            CASE WHEN ra.hired_as = 1 THEN 'Yes'
            ELSE 'No' END) as hired_as_member");

        $this->db->select("(
            CASE WHEN raa.status = 1 THEN 'In Progress'
            WHEN raa.status = 3 THEN 'Hired'
            WHEN raa.status = 2 THEN 'Rejected' ELSE 'Applied' END) as status_label");

            $this->db->select("(
            CASE
                WHEN raa.application_process_status = 0 THEN 'New'
                WHEN raa.application_process_status = 1 THEN 'Screening'
                WHEN raa.application_process_status = 2 THEN 'Interviews'
                WHEN raa.application_process_status = 3 THEN 'References'
                WHEN raa.application_process_status = 4 THEN 'Documents'
                WHEN raa.application_process_status = 5 THEN 'In progress'
                WHEN raa.application_process_status = 6 THEN 'CAB'
                WHEN raa.application_process_status = 7 THEN 'Hired'
                WHEN raa.application_process_status = 8 THEN 'Unsuccessful'
                ELSE 'Unknown'
            END
        ) as process_status_label");

        $this->db->from('tbl_recruitment_applicant_applied_application as raa');
        $this->db->join('tbl_recruitment_applicant as ra', 'raa.applicant_id = ra.id', 'inner');
        $this->db->join('tbl_recruitment_job_position as rjp', 'rjp.id = raa.position_applied', 'inner');

        $this->db->join('tbl_person as p', 'ra.person_id = p.id', 'inner');
        $this->db->join('tbl_person_email as pe', 'p.id = pe.person_id AND pe.primary_email = 1', 'inner');
        $this->db->join('tbl_person_phone as pp', 'p.id = pp.person_id AND pp.primary_phone = 1', 'inner');
        $this->db->join('tbl_recruitment_stage as rs', 'rs.id = raa.current_stage', 'inner');
        $this->db->join('tbl_recruitment_stage_label as rtb', 'rtb.id = rs.stage_label_id', 'inner');
        $this->db->where_not_in('raa.application_process_status', [7]);
        $this->db->where(['ra.archive' => 0]);
        $this->db->order_by($orderBy, $direction);
        $this->db->group_by('raa.id');
        $this->db->limit($limit, ($page * $limit));
        
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
     * Get applicant list for group booking - SMS
     * - Activity tab
     */
    public function get_applicant_list_for_sms($reqData) {

        $return = ['status' => true, 'data' => ''];
        if (!empty($reqData->interview_id)) {
            $select_column = array('ria.application_id', 'ria.applicant_id', 'concat(ra.firstname," ",ra.lastname) as name', 'rap.phone', 'rae.email', 'rfa.flag_status','raaa.application_process_status', 'ria.interview_meeting_status');
            // $select_column = array('ria.applicant_id as id', 'ria.applicant_id as value', 'concat(ra.firstname," ",ra.lastname) as label', 'rap.phone', 'rae.email as subTitle', 'ria.application_id');
            $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
            $this->db->from(TBL_PREFIX . 'recruitment_interview_applicant as ria');
            $this->db->join(TBL_PREFIX . 'recruitment_interview as ri', 'ri.id = ria.interview_id', 'INNER');
            $this->db->join(TBL_PREFIX . 'recruitment_applicant as ra', 'ra.id = ria.applicant_id', 'INNER');
            $this->db->join(TBL_PREFIX . "recruitment_applicant_email as rae", "rae.applicant_id = ra.id AND rae.archive = 0 AND rae.primary_email = 1", "INNER");
            $this->db->join(TBL_PREFIX . "recruitment_applicant_phone as rap", "rap.applicant_id = ra.id AND rap.archive = 0 AND rap.primary_phone = 1", "INNER");
            $this->db->join(TBL_PREFIX . 'recruitment_job as rj', 'rj.id = ria.job_id', 'INNER');
            $this->db->join(TBL_PREFIX . 'recruitment_applicant_applied_application as raaa', 'raaa.id = ria.application_id', 'LEFT');
            $this->db->join(TBL_PREFIX . 'recruitment_flag_applicant as rfa', 'ra.id = rfa.applicant_id', 'LEFT');
            $this->db->where([ 
                'ria.interview_id' => $reqData->interview_id,
                'ria.archive' => 0
            ]);
            $this->db->where('raaa.application_process_status !=', 8);
            $this->db->where('ria.interview_meeting_status !=', 2);
            $query = $this->db->get();
            $result = $query->result();

            $data = [];            
            foreach($result as $ap_in => $applicant) {
                $data_temp = [];
                # skip if the applicant is flagged or rejected in groupbooking or application rejected
                if ($applicant->flag_status == 2 || $applicant->application_process_status == 8 || $applicant->interview_meeting_status == 2) {
                    continue;
                }
                $data_temp['id'] = $ap_in + 1;
                $data_temp['label'] = $applicant->name;
                $data_temp['value'] = $applicant->applicant_id;
                $data_temp['subTitle'] = $applicant->email;
                $data_temp['application_id'] = $applicant->application_id;
                $data_temp['phone'] = $applicant->phone;
                $data[] = $data_temp;
            }
            $return = ['status' => true, 'data' => $data];  
        }
        return $return;
    }

    /**
     * Get applicant info list for group booking - SMS
     * - Activity tab
     * @param {array} applicant_ids
     * @param {int} $interview_id
     */
    public function get_applicant_by_applicant_id($applicant_ids, $interview_id) {

        $return = [];
        if (!empty($interview_id)) {
            $select_column = array('ria.application_id', 'ria.applicant_id', 'ra.firstname', 'ra.lastname', 'concat(ra.firstname," ",ra.lastname) as name', 'rap.phone', 'rfa.flag_status','raaa.application_process_status', 'ria.interview_meeting_status');
            $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
            $this->db->from(TBL_PREFIX . 'recruitment_interview_applicant as ria');
            $this->db->join(TBL_PREFIX . 'recruitment_interview as ri', 'ri.id = ria.interview_id', 'INNER');
            $this->db->join(TBL_PREFIX . 'recruitment_applicant as ra', 'ra.id = ria.applicant_id', 'INNER');
            $this->db->join(TBL_PREFIX . "recruitment_applicant_phone as rap", "rap.applicant_id = ra.id AND rap.archive = 0 AND rap.primary_phone = 1", "INNER");
            $this->db->join(TBL_PREFIX . 'recruitment_job as rj', 'rj.id = ria.job_id', 'INNER');
            $this->db->join(TBL_PREFIX . 'recruitment_applicant_applied_application as raaa', 'raaa.id = ria.application_id', 'LEFT');
            $this->db->join(TBL_PREFIX . 'recruitment_flag_applicant as rfa', 'ra.id = rfa.applicant_id', 'LEFT');
            $this->db->where([ 
                'ria.interview_id' => $interview_id,
                'ria.archive' => 0
            ]);
            $this->db->where_in('ria.applicant_id', $applicant_ids);
            $query = $this->db->get();
            $result = $query->result();

            $data = [];
            foreach($result as $ap_in => $applicant) {
                $data_temp = [];
                # skip if the applicant is flagged or rejected in groupbooking or application rejected
                /*if ($applicant->flag_status == 2 || $applicant->application_process_status == 8 || $applicant->interview_meeting_status == 2) {
                    continue;
                }*/
                $data_temp['applicant_id'] = $applicant->applicant_id;
                $data_temp['firstname'] = $applicant->firstname;
                $data_temp['lastname'] = $applicant->lastname;
                $data_temp['name'] = $applicant->name;
                $data_temp['application_id'] = $applicant->application_id;
                $data_temp['phone'] = $applicant->phone;
                $data_temp['flag_status'] = $applicant->flag_status;
                $data_temp['application_process_status'] = $applicant->application_process_status;
                $data_temp['interview_meeting_status'] = $applicant->interview_meeting_status;
                $data[] = (object) $data_temp;
            }
            $return = $data;  
        }
        return $return;
    }

    /**
     * Get applicant info list by application id for group booking - SMS
     * - Activity tab (Application Detail)
     * @param {array} applicant_ids
     * @param {int} $interview_id
     */
    public function get_applicant_data_for_sms($reqData,$applicant_data) {
        $return = ['status' => true, 'data' => ''];
        if (!empty($reqData->application_id)) {
            $select_column = array('raaa.id as application_id', 'ra.id as applicant_id', 'concat(ra.firstname," ",ra.lastname) as name', 'rap.phone', 'rae.email', 'rfa.flag_status','raaa.application_process_status');
            $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
            $this->db->from(TBL_PREFIX . 'recruitment_applicant_applied_application as raaa');
            $this->db->join(TBL_PREFIX . 'recruitment_applicant as ra', 'ra.id = raaa.applicant_id', 'INNER');
            $this->db->join(TBL_PREFIX . "recruitment_applicant_email as rae", "rae.applicant_id = ra.id AND rae.archive = 0 AND rae.primary_email = 1", "INNER");
            $this->db->join(TBL_PREFIX . "recruitment_applicant_phone as rap", "rap.applicant_id = ra.id AND rap.archive = 0 AND rap.primary_phone = 1", "INNER");
            $this->db->join(TBL_PREFIX . 'recruitment_flag_applicant as rfa', 'ra.id = rfa.applicant_id', 'LEFT');
            $this->db->where('raaa.id', $reqData->application_id);
            if($this->Common_model->check_is_bu_unit($applicant_data)) {
                $this->db->where('raaa.bu_id', $applicant_data->business_unit['bu_id']);
            }
            $query = $this->db->get();
            $result = $query->result();

            $data = [];
            foreach($result as $ap_in => $applicant) {
                $data_temp = [];
                $data_temp['id'] = $ap_in + 1;
                $data_temp['label'] = $applicant->name;
                $data_temp['value'] = $applicant->applicant_id;
                $data_temp['subTitle'] = $applicant->email;
                $data_temp['application_id'] = $applicant->application_id;
                $data_temp['phone'] = $applicant->phone;
                $data[] = (object) $data_temp;
            }
            $return = ['status' => true, 'data' => $data];  
        }
        return $return;
    }

    /**
     * Get applicant info list for group booking - SMS
     * - Activity tab
     * @param {array} applicant_ids
     * @param {int} $application_id
     */
    public function get_applicant_by_application_id($applicant_ids, $application_id) {

        $return = [];
        if (!empty($application_id)) {
            $select_column = array('raaa.id as application_id', 'ra.id as applicant_id', 'ra.firstname', 'ra.lastname', 'concat(ra.firstname," ",ra.lastname) as name', 'rap.phone', 'rae.email', 'rfa.flag_status','raaa.application_process_status');
            $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
            $this->db->from(TBL_PREFIX . 'recruitment_applicant_applied_application as raaa');
            $this->db->join(TBL_PREFIX . 'recruitment_applicant as ra', 'ra.id = raaa.applicant_id', 'INNER');
            $this->db->join(TBL_PREFIX . "recruitment_applicant_email as rae", "rae.applicant_id = ra.id AND rae.archive = 0 AND rae.primary_email = 1", "INNER");
            $this->db->join(TBL_PREFIX . "recruitment_applicant_phone as rap", "rap.applicant_id = ra.id AND rap.archive = 0 AND rap.primary_phone = 1", "INNER");
            $this->db->join(TBL_PREFIX . 'recruitment_flag_applicant as rfa', 'ra.id = rfa.applicant_id', 'LEFT');
            $this->db->where('raaa.id', $application_id);
            $query = $this->db->get();
            $result = $query->result();

            $data = [];
            foreach($result as $ap_in => $applicant) {
                $data_temp = [];
                # skip if the applicant is flagged or rejected in groupbooking or application rejected
                /*if ($applicant->flag_status == 2 || $applicant->application_process_status == 8) {
                    continue;
                }*/
                $data_temp['applicant_id'] = $applicant->applicant_id;
                $data_temp['firstname'] = $applicant->firstname;
                $data_temp['lastname'] = $applicant->lastname;
                $data_temp['name'] = $applicant->name;
                $data_temp['application_id'] = $applicant->application_id;
                $data_temp['phone'] = $applicant->phone;
                $data_temp['flag_status'] = $applicant->flag_status;
                $data_temp['application_process_status'] = $applicant->application_process_status;
                $data[] = (object) $data_temp;
            }
            $return = $data;  
        }
        return $return;
    }
}
