<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Recruitment_applicant_model extends Basic_Model {

    public $application_stage_status = [
        "0" => "New",
        "1" => "Screening",
        "2" => "Interviews",
        "5" => "In progress",
        "6" => "CAB",
        "7" => "Hired",
        "8" => "Unsuccessful",
    ];

    public $application_stage_status_final = [
        "7" => "Hired",
        "8" => "Unsuccessful",
    ];

    public $application_stage_status_grouped = [
        "0" => "New",
        "1" => "Screening",
        "2" => "Interviews",
        "5" => "In progress",
        "6" => "CAB",
        "9" => "Closed",
    ];

    public function __construct() {
        $this->table_name = 'recruitment_applicant';
        $this->load->model('member/Member_model');
        $this->load->model('sales/Feed_model');
        $this->load->model('common/Common_model');
        //PK of tbl_member
        $this->object_fields['firstname'] = "First Name";
        $this->object_fields['lastname'] = "Last Name";
        $this->object_fields['phone'] = function($applicantId = '') {
                                                        if (empty($applicantId)) {
                                                            return 'Phone';
                                                        }
                                                        $result = $this->get_record_where('recruitment_applicant_phone','phone', ['applicant_id' => $applicantId, 'archive' => '0']);
                                                        return $result[0]->phone;
                                                    };
        $this->object_fields['email'] = function($applicantId = '') {
                                                        if (empty($applicantId)) {
                                                            return 'Email';
                                                        }
                                                        $result = $this->get_record_where('recruitment_applicant_email','email', ['applicant_id' => $applicantId, 'archive' => '0']);
                                                        return $result[0]->email;
                                                    };
        #TODO
        // $this->object_fields['Owner'] = [
        //                                         'field' => 'recruiter',
        //                                         'object_fields' => $this->Member_model->getObjectfields()
        //                                     ];
        $this->object_fields['dob'] = "Date of Birth";
        // Call the CI_Model constructor
        parent::__construct();
    }

    /**
     * fetches list of recruiters already associated with applications
     */
    public function get_application_recruiters($reqData) {
      
        $select_column = array("distinct (raaa.recruiter) as value", "(select concat(m.firstname,' ',m.lastname) as recruiter_name from tbl_member as m inner join tbl_department as d on d.id = m.department AND d.short_code = 'internal_staff' where m.id = raaa.recruiter) as label");
        $this->db->select($select_column);
        $this->db->from('tbl_recruitment_applicant_applied_application as raaa');
        $this->db->where('raaa.archive', 0);
        $this->db->where('raaa.recruiter != 0');
        if($this->Common_model->check_is_bu_unit($reqData)) {
            $this->db->where('raaa.bu_id', $reqData->business_unit['bu_id']);
        }
        $this->db->order_by("label", "ASC");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        $return = array('status' => true, 'data' => $result);
        return $return;
    }

    /**
     * fetches list of applicants
     */
    public function get_applicants($reqData, $adminId) {
        // pr($reqData);
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $start_date = $filter->start_date ?? '';
        $end_date = $filter->end_date ?? '';
        $start_date = is_null($start_date) ? '' : $start_date;
        $end_date = is_null($end_date) ? '' : $end_date;

        $src_columns = array('ra.id', 'ra.appId', 'concat(p.firstname," ",p.lastname) as FullName', 'pe.email', 'pp.phone');
        
        $available_columns = array( 'id', 'created', 'FullName', 'status', 'email', 'phone', 'appId' );
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_columns) ) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'ra.created';
            $direction = 'DESC';
        }

        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));
        // Note: this is the status for applications table not applicant
        // (the tbl_recruitment_applicant.status is deprecated and should not be used anymore for filtering)
        $APLCN_IN_PROGRESS = 1;
        $APLCN_REJECTED = 2;
        $APLCN_HIRED = 3;

        // 4 is just an alias of 2 and 3
        $APLCN_COMPLETED = 4;

        
        if (!empty($filter->filter_val) && in_array($filter->filter_val, [$APLCN_IN_PROGRESS, $APLCN_REJECTED, $APLCN_HIRED, $APLCN_COMPLETED])) {
            if (in_array($filter->filter_val, [$APLCN_COMPLETED])) {
                $filter_val = [$APLCN_REJECTED, $APLCN_HIRED];
            } else {
                $filter_val = [$filter->filter_val];
            }

            //if (!in_array($filter_val, [$APLCN_REJECTED])) {
            //    $this->db->where('ra.flagged_status', 0);
            //}

            $this->db->where_in('raaa.status', $filter_val);
        }
        
        /*
        if (!empty($filter->filter_val)) {
            $filter_val = $this->db->escape_str($filter->filter_val);
            $this->db->where('raaa.application_process_status', $filter_val);
        }
        */
        
        if (!empty($start_date) && empty($end_date)) {
            $this->db->where('DATE_FORMAT(ra.date_applide, "%Y-%m-%d") >= ', DateFormate($start_date, 'Y-m-d'));
        } elseif (!empty($start_date) && !empty($end_date)) {
            $this->db->where('DATE_FORMAT(ra.date_applide, "%Y-%m-%d") >= ', DateFormate($start_date, 'Y-m-d'));
            $this->db->where('DATE_FORMAT(ra.date_applide, "%Y-%m-%d") <= ', DateFormate($end_date, 'Y-m-d'));
        } elseif (!empty($end_date)) {
            $this->db->where('DATE_FORMAT(ra.date_applide, "%Y-%m-%d") <= ', DateFormate($end_date, 'Y-m-d'));
        }

        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    
                    $searchKey = trim($filter->search);
                    $searchKeyArray = preg_split('/\s+/', $searchKey);
                    $searchKey = implode(" ", $searchKeyArray);
                    
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $searchKey);
                    
                    //$orderBy = $serch_column[0];
                    //$direction = 'ASC';
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        
        $select_column = array(
            'ra.id', 'ra.created', 'concat(p.firstname," ",p.lastname) as FullName',
            'ra.status', 'pe.email', 'pp.phone', 'ra.appId', 'ra.flagged_status'
        );
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_recruitment_applicant as ra');
        $this->db->join('tbl_person as p', 'ra.person_id = p.id', 'inner');
        $this->db->join('tbl_person_email as pe', 'p.id = pe.person_id AND pe.primary_email = 1', 'inner');
        $this->db->join('tbl_person_phone as pp', 'p.id = pp.person_id AND pp.primary_phone = 1', 'inner');
        $this->db->join('tbl_recruitment_applicant_applied_application as raaa', 'raaa.applicant_id = ra.id AND raaa.archive = 0', 'INNER');
        $this->db->where('ra.archive', 0);
        //$this->db->where('ra.flagged_status', 0);
        $this->db->order_by($orderBy, $direction);
        $this->db->group_by('ra.id');

        // Note: If you dont want to allow applicants appearing on different filters
        // uncomment the if-condition below
        // if (in_array($filter->filter_val, [$APLCN_COMPLETED])) {
        //     $this->db->having('num_in_progress_applications', '0');
        // }

        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        // last_query();
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result);
        return $return;
    }

    /**
     * fetches list of applications of applicants
     */
    public function get_api_applications($filter = null) {

        # fetching the required docs
        $this->db->select(['display_name', 'id', 'code']);
        $this->db->from('tbl_references');
        $this->db->where('archive', 0);
        $this->db->where('type in (4,5)');
        $this->db->where("code not in ('DL', 'WWCC Receipt', 'NPC', 'Passport', 'VISA', 'Meds', 'CPR', 'Fire Safety', 'FA', 'MH')");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $response = $query->result();
        if (!empty($response)) {
            foreach ($response as $val) {
                $all_documents[] = array('display_name' => $val->display_name, 'id' => $val->id, 'code' => $val->code);
            }
        }

        $start_date = $filter->start_date ?? '';
        $end_date = $filter->end_date ?? '';
        $start_date = is_null($start_date) ? '' : $start_date;
        $end_date = is_null($end_date) ? '' : $end_date;
        $src_columns = array(
            'applicant_id' =>'ra.appId',
            'application_id' =>'raa.id',
            'firstname' => 'p.firstname',
            'lastname' => 'p.lastname',
            'email' => 'pe.email',
            'phone' => 'pp.phone',
            'start_date' => 'raa.created',
            'end_date' => 'raa.created');
        $orderBy = 'raa.created';
        $direction = 'DESC';

        if (!empty($filter)) {
            $filter_keys = array_keys((array) $filter);
            $search_col_keys = array_keys($src_columns);
            $arrdiff = array_diff($filter_keys, $search_col_keys);
            if ($arrdiff) {
                echo json_encode(["status" => false, "error" => "Invalid filter options supplied " . implode(", ", $arrdiff)]);
                exit();
            }

            if (!empty($start_date) && !validateDateWithFormat($start_date)) {
                echo json_encode(["status" => false, "error" => "Invalid start_date provided: {$start_date}"]);
                exit();
            }

            if (!empty($end_date) && !validateDateWithFormat($end_date)) {
                echo json_encode(["status" => false, "error" => "Invalid end_date provided: {$end_date}"]);
                exit();
            }

            if (!empty($start_date) && !empty($end_date)) {
                $date1 = date_create(DateFormate($start_date, 'Y-m-d'));
                $date2 = date_create(DateFormate($end_date, 'Y-m-d'));
                $diff = date_diff($date1, $date2);
                if (isset($diff->invert) && $diff->invert > 0) {
                    echo json_encode(["status" => false, "error" => "Start date: {$start_date} should be lower or equal to end date: {$end_date}"]);
                    exit();
                }
            }
        }

        if (!empty($start_date) && empty($end_date)) {
            $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") >= ', DateFormate($start_date, 'Y-m-d'));
        } elseif (!empty($start_date) && !empty($end_date)) {
            $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") >= ', DateFormate($start_date, 'Y-m-d'));
            $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") <= ', DateFormate($end_date, 'Y-m-d'));
        } elseif (!empty($end_date)) {
            $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") <= ', DateFormate($end_date, 'Y-m-d'));
        }

        if (!empty($filter)) {
            $this->db->group_start();
            $cond_found = 0;
            foreach ($filter as $column_label => $column_value) {
                $column_search = $src_columns[$column_label];
                if($column_label == 'start_date' || $column_label == 'end_date') continue;

                $this->db->where($column_search, $column_value);
                $cond_found = 1;
            }
            if (!$cond_found)
                $this->db->or_where("1", "1");
            $this->db->group_end();
        }

        $this->db->select(array(
            'ra.id', 'raa.jobId as job_id', "raa.channelId as channel_id", 'raa.id AS application_id', "(CASE WHEN raa.status=1 THEN 'in progress' WHEN raa.status=2 THEN 'rejected' WHEN raa.status=3 THEN 'hired' ELSE '' END) as application_status"));
        $this->db->select(array('ra.appId as applicant_id', 'raa.created as date_applied', "rjp.title as job_position"));
        $this->db->select(array('(select r.display_name from tbl_references as r where r.id = p.title and r.archive = 0 and r.type = 3) as title'));

        $this->db->select(array('concat(p.firstname," ",p.lastname) as fullname', 'p.firstname', 'p.lastname', 'p.previous_name', 'p.preferred_name', 'p.date_of_birth', 'pe.email', 'pp.phone', 'pa.street', 'pa.suburb', 'pa.postcode', 's.name as state'));

        $this->db->select(array("(concat(rtb.title)) as current_stage", "(concat(rs.stage,' - ',rs.title)) as current_substage", "raa.referrer_url", "(concat(raad.street, ', ', raad.city, ', ', raad.postal, ', ', tbl_state.name)) as address"));
        $this->db->select('(select concat(m.firstname," ",m.lastname) as recruiter_name from tbl_member as m inner join tbl_department as d on d.id = m.department AND d.short_code = "internal_staff" where m.id = raa.recruiter) as recruiter_name');
        $this->db->select('(select rjc.name from tbl_recruitment_job rj, tbl_recruitment_job_category rjc where rj.id = raa.jobId and rjc.id = rj.sub_category) as job_subcategory');
        $this->db->select('(select rjc.name from tbl_recruitment_job rj, tbl_recruitment_job_category rjc where rj.id = raa.jobId and rjc.id = rj.category) as job_category');

        $this->db->from('tbl_recruitment_applicant_applied_application as raa');
        $this->db->join('tbl_recruitment_applicant as ra', 'raa.applicant_id = ra.id', 'inner');
        $this->db->join('tbl_recruitment_job_position as rjp', 'rjp.id = raa.position_applied', 'inner');
        $this->db->join('tbl_recruitment_applicant_address as raad', 'raa.applicant_id = raad.applicant_id', 'inner');
        $this->db->join('tbl_state', 'raad.state = tbl_state.id');

        $this->db->join('tbl_person as p', 'ra.person_id = p.id', 'inner');
        $this->db->join('tbl_person_email as pe', 'p.id = pe.person_id AND pe.primary_email = 1', 'inner');
        $this->db->join('tbl_person_phone as pp', 'p.id = pp.person_id AND pp.primary_phone = 1', 'inner');
        $this->db->join('tbl_person_address as pa', 'p.id = pa.person_id AND pa.primary_address = 1', 'left');
        $this->db->join('tbl_state as s', 's.id = pa.state', 'left');
        $this->db->join('tbl_recruitment_stage as rs', 'rs.id = raa.current_stage', 'inner');
        $this->db->join('tbl_recruitment_stage_label as rtb', 'rtb.id = rs.stage_label_id', 'inner');
        $this->db->where('ra.archive', 0);
        $this->db->order_by($orderBy, $direction);
        $this->db->group_by('raa.id');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // last_query();exit;
        $result = $query->result();
        $dt_filtered_total = 0;

        if (!empty($result)) {
            $dt_filtered_total = count($result);
            foreach ($result as $val) {

                $val->age = null;
                if ($val->date_of_birth) {
                    $date1 = date_create(DateFormate($val->date_of_birth, 'Y-m-d'));
                    $date2 = date_create(DATE_TIME);
                    $diff = date_diff($date1, $date2);
                    $val->age = $diff->y;
                }

                if ($val->channel_id == 2 && $val->referrer_url != '')
                    $val->applied_through = !empty($val->referrer_url) ? getDomain($val->referrer_url) : 'N/A';
                else
                    $val->applied_through = (isset($val->channel_id) && $val->channel_id == 1 ? 'Seek' : (($val->channel_id == 2) ? 'Website' : (($val->channel_id == 3) ? 'HCM Software' : 'N/A')));

                # fetching driving license details
                $doc_details = $this->get_all_attachments_by_applicant_id($val->id, $doc_cat_code = 'DL', $val->application_id, true);
                $val->driving_license_no = null;
                $val->driving_license_expiry = null;
                $val->driving_license_file = null;
                if ($doc_details) {
                    $val->driving_license_no = $doc_details[0]->reference_number;
                    $val->driving_license_expiry = $doc_details[0]->expiry_date;
                    $val->driving_license_file = base_url() . str_replace("./", "", $doc_details[0]->docPath);
                }

                # fetching WWCC details
                $doc_details = $this->get_all_attachments_by_applicant_id($val->id, $doc_cat_code = 'WWCC Receipt', $val->application_id, true);
                $val->wwcc_no = null;
                $val->wwcc_expiry = null;
                $val->wwcc_receipt = null;
                $val->wwcc_file = null;
                if ($doc_details) {
                    $val->wwcc_no = $doc_details[0]->reference_number;
                    $val->wwcc_expiry = $doc_details[0]->expiry_date;
                    $val->wwcc_receipt = $doc_details[0]->reference_number;
                    $val->wwcc_file = base_url() . str_replace("./", "", $doc_details[0]->docPath);
                }

                # fetching national police check details
                $doc_details = $this->get_all_attachments_by_applicant_id($val->id, $doc_cat_code = 'NPC', $val->application_id, true);
                $val->police_check_no = null;
                $val->police_check_issue = null;
                $val->police_check_file = null;
                if ($doc_details) {
                    $val->police_check_no = $doc_details[0]->reference_number;
                    $val->police_check_issue = $doc_details[0]->issue_date;
                    $val->police_check_file = base_url() . str_replace("./", "", $doc_details[0]->docPath);
                }

                # fetching passport details
                $doc_details = $this->get_all_attachments_by_applicant_id($val->id, $doc_cat_code = 'Passport', $val->application_id, true);
                $val->passport_no = null;
                $val->passport_file = null;
                if ($doc_details) {
                    $val->passport_no = $doc_details[0]->reference_number;
                    $val->passport_file = base_url() . str_replace("./", "", $doc_details[0]->docPath);
                }

                # fetching visa details
                $doc_details = $this->get_all_attachments_by_applicant_id($val->id, $doc_cat_code = 'VISA', $val->application_id, true);
                $val->visa_no = null;
                $val->visa_expiry = null;
                $val->visa_file = null;
                if ($doc_details) {
                    $val->visa_no = $doc_details[0]->reference_number;
                    $val->visa_expiry = $doc_details[0]->expiry_date;
                    $val->visa_file = base_url() . str_replace("./", "", $doc_details[0]->docPath);
                }

                # fetching administration medication details
                $doc_details = $this->get_all_attachments_by_applicant_id($val->id, $doc_cat_code = 'Meds', $val->application_id, true);
                $val->administration_medication_issue = null;
                $val->administration_medication_file = null;
                if ($doc_details) {
                    $val->administration_medication_issue = $doc_details[0]->issue_date;
                    $val->administration_medication_file = base_url() . str_replace("./", "", $doc_details[0]->docPath);
                }

                # fetching CPR details
                $doc_details = $this->get_all_attachments_by_applicant_id($val->id, $doc_cat_code = 'CPR', $val->application_id, true);
                $val->cpr_issue = null;
                $val->cpr_file = null;
                if ($doc_details) {
                    $val->cpr_issue = $doc_details[0]->issue_date;
                    $val->cpr_file = base_url() . str_replace("./", "", $doc_details[0]->docPath);
                }

                # fetching fire safety details
                $doc_details = $this->get_all_attachments_by_applicant_id($val->id, $doc_cat_code = 'Fire Safety', $val->application_id, true);
                $val->fire_safety_training_issue = null;
                $val->fire_safety_training_file = null;
                if ($doc_details) {
                    $val->fire_safety_training_issue = $doc_details[0]->issue_date;
                    $val->fire_safety_training_file = base_url() . str_replace("./", "", $doc_details[0]->docPath);
                }

                # fetching first aid details
                $doc_details = $this->get_all_attachments_by_applicant_id($val->id, $doc_cat_code = 'FA', $val->application_id, true);
                $val->first_aid_issue = null;
                $val->first_aid_file = null;
                if ($doc_details) {
                    $val->first_aid_issue = $doc_details[0]->issue_date;
                    $val->first_aid_file = base_url() . str_replace("./", "", $doc_details[0]->docPath);
                }

                # fetching manual handling details
                $doc_details = $this->get_all_attachments_by_applicant_id($val->id, $doc_cat_code = 'MH', $val->application_id, true);
                $val->manual_handling_issue = null;
                $val->manual_handling_file = null;
                if ($doc_details) {
                    $val->manual_handling_issue = $doc_details[0]->issue_date;
                    $val->manual_handling_file = base_url() . str_replace("./", "", $doc_details[0]->docPath);
                }

                # fetching all other documents
                foreach($all_documents as $docrow) {
                    $doc_cat_code = strtolower(str_replace(" ","-",$docrow['code']));
                    $doc_details = $this->get_all_attachments_by_applicant_id($val->id, $docrow['code'], $val->application_id, true);
                    $refno = $doc_cat_code."_no";
                    $issue = $doc_cat_code."_issue";
                    $file = $doc_cat_code."_file";

                    $val->$refno = null;
                    $val->$issue = null;
                    $val->$file = null;
                    if ($doc_details) {
                        $cntinner = 0;
                        foreach($doc_details as $res_docrow) {
                            if($cntinner) {
                                $refno = $doc_cat_code.$cntinner."_no";
                                $issue = $doc_cat_code.$cntinner."_issue";
                                $file = $doc_cat_code.$cntinner."_file";
                            }
                            $val->$refno = null;
                            $val->$issue = null;
                            $val->$file = null;

                            $val->$refno = $res_docrow->reference_number;
                            $val->$issue = $res_docrow->issue_date;
                            $val->$file = base_url() . str_replace("./", "", $res_docrow->docPath);
                            $cntinner++;
                        }
                    }
                }
            }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $result);
        return $return;
    }

    /**
     * Return history items of a Applications
     * @param $data object
     * @return array
     */
    public function get_field_history($data,$reqData)
    {
        $where = ['h.application_id' => $data->application_id];
        if($this->Common_model->check_is_bu_unit($reqData)) {
            $where = ['h.application_id' => $data->application_id,'raa.bu_id' => $reqData->business_unit['bu_id']];
        }
        $items = $this->db->select(['h.id','hf.created_at', 'h.id as history_id', 'f.id as field_history_id', 'f.field', 'f.value', 'f.prev_val', 'h.created_at', 'CONCAT(m.firstname, \' \', m.lastname) as created_by', 'h.created_at', 'hf.desc as feed_title', 'hf.id as feed_id', 'hf.feed_type'])
            ->from(TBL_PREFIX . 'application_history as h')
            ->where($where)
            ->join(TBL_PREFIX . 'application_field_history as f', 'f.history_id = h.id', 'left')
            ->join(TBL_PREFIX . 'application_history_feed as hf', 'hf.history_id = h.id', 'left')
            //->join(TBL_PREFIX . 'leads as l', 'l.id = h.lead_id', 'left')
            ->join(TBL_PREFIX . 'member as m', 'm.uuid = h.created_by', 'left')
            ->join('tbl_recruitment_applicant_applied_application as raa', 'raa.id = h.application_id', 'inner')
            ->order_by('h.id', 'DESC')
            ->get()->result();
        $application_statuses = $this->get_application_stage_status_history();
       // $this->db->join('tbl_person_email as pe', 'p.id = pe.person_id AND pe.primary_email = 1', 'inner');
        $this->load->model('Feed_model');
        $related_type = $this->Feed_model->get_related_type('application');

        $feed = [];
        //print_r($items);exit();
        // map fields to rendered values
        foreach ($items as $item) {

            $item->related_type = $related_type;
            $item->expanded = true;
            $item->feed = false;
            $item->comments = [];
            $history_id = $item->history_id;
            // history comments
            $comments = $this->Feed_model->get_comment_by_history_id($item->history_id, $related_type);
            $item->comments = $comments;
            $item->comments_count = count($comments);
            $item->comment_create = false;
            $item->comment_post = false;
            $item->comment_desc = '';


            switch ($item->field) {
                case 'status':
                    foreach ($application_statuses['data'] as $key => $val){
                        if($val['value'] == $item->value){
                            $item->value = $val['label'] ?? 'Open';
                            continue;
                        }
                        if($val['value'] == $item->prev_val){
                            $item->prev_val = $val['label'] ?? 'Open';
                            continue;
                        }
                    }
                break;
                case 'owner':
                    $owner = $this->db->from(TBL_PREFIX . 'member as m')->select('CONCAT(m.firstname, \' \', m.lastname) as user')->where(['id' => $item->value])->get()->result();
                    $prev_owner = $this->db->from(TBL_PREFIX . 'member as m')->select('CONCAT(m.firstname, \' \', m.lastname) as user')->where(['id' => $item->prev_val])->get()->result();
                    $item->value = !empty($owner) ? $owner[0]->user : 'N/A';
                    $item->prev_val = !empty($prev_owner) ? $prev_owner[0]->user : 'N/A';
                break;
                case 'job_transfer':
                    $job = $this->db->from(TBL_PREFIX . 'recruitment_job_position as rjp')->select('title')->where(['id' => $item->value])->get()->result();
                    $transfered_job = $this->db->from(TBL_PREFIX . 'recruitment_job_position as rjp')->select('title')->where(['id' => $item->prev_val])->get()->result();
                    $item->value = !empty($job) ? $job[0]->title : 'N/A';
                    $item->prev_val = !empty($transfered_job) ? $transfered_job[0]->title : 'N/A';
                break;
                case 'NULL':
                case '':
                    $item->feed = true;
                    break;
                default:
                    $item->value = !empty($item->value) ? $item->value : 'N/A';
                    $item->prev_val = !empty($item->prev_val) ? $item->prev_val : 'N/A';
                    break;
            }
            if ($item->feed_id == '' || $item->feed_id == NULL) {
                $item->feed = false;
            }
            if (($item->field_history_id != '' && $item->field_history_id != NULL) || ($item->feed_id != '' && $item->feed_id != NULL)) {
                $feed[$history_id][] = $item;
            }
        }
        //print_r($feed);exit();
        //krsort($feed);
        $feed = array_values($feed);
        return $feed;
    }

    /**
     * fetches list of applications of applicants
     */
    public function get_applications($reqData, $adminId, $filter_condition = '',$application_data=NULL) {
        $limit = $reqData->pageSize ?? 20;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';
        $start_date = $filter->start_date ?? '';
        $end_date = $filter->end_date ?? '';
        $start_date = is_null($start_date) ? '' : $start_date;
        $end_date = is_null($end_date) ? '' : $end_date;
        $its_recruiter_admin = check_its_recruiter_admin($adminId);
        $application_id = $reqData->application_id ?? '';
        $application_status = $reqData->application_status ?? '';
        $src_columns = array('raa.id','concat(p.firstname," ",p.lastname) as FullName', 'pe.email', 'rjp.title', 'ra.hired_as','raa.referred_by','rjaa.status');
        $quick_filter = $reqData->quick_filter ?? '';
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id)) {
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

        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy), true);
        # new lightening filters
        // if(isset($filter->filters)) {
        //     foreach($filter->filters as $filter_obj) {
        //         if(empty($filter_obj->select_filter_value)) continue;

        //         $sql_cond_part = GetSQLCondPartFromSymbol($filter_obj->select_filter_operator_sym, $filter_obj->select_filter_value);
        //         if($filter_obj->select_filter_field == "fullname") {
        //             $this->db->where('concat(p.firstname," ",p.lastname) '.$sql_cond_part);
        //         }
        //         if($filter_obj->select_filter_field == "job_position") {
        //             $this->db->where('rjp.title '.$sql_cond_part);
        //         }
        //         if($filter_obj->select_filter_field == "stage") {
        //             $this->db->where('raa.current_stage in  ('.$filter_obj->select_filter_value.') and raa.status not in (2,3)');
        //         }
        //         if($filter_obj->select_filter_field == "applied_through") {
        //             $this->db->where('raa.referrer_url '.$sql_cond_part);
        //         }
        //         if($filter_obj->select_filter_field == "recruiter") {
        //             $this->db->where('raa.recruiter '.$sql_cond_part);
        //         }
        //         if($filter_obj->select_filter_field == "status_label") {
        //             $this->db->where('raa.status '.$sql_cond_part);
        //         }
        //         if($filter_obj->select_filter_field == "created") {
        //             $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") '.GetSQLOperator($filter_obj->select_filter_operator_sym), DateFormate($filter_obj->select_filter_value, 'Y-m-d'));
        //         }
        //     }
        // }

        // quick_filter
        if (isset($quick_filter) == true && empty($quick_filter) == false) {
            if (isset($quick_filter->applicant) == true && $quick_filter->applicant != '') {
                $this->db->like('concat(p.firstname," ",p.lastname)', $quick_filter->applicant);
            }
            if (isset($quick_filter->recruitor) == true && $quick_filter->recruitor != '') {
                $this->db->like("(select concat(m.firstname, ' ', m.lastname) as recruiter_name from tbl_member as m inner join tbl_department as d on d.id = m.department AND d.short_code = 'internal_staff' where m.id = raa.recruiter)", $quick_filter->recruitor);
            }
            if (isset($quick_filter->stage) == true && $quick_filter->stage != '') {
                if ($quick_filter->stage != '-1') {
                    $this->db->where('raa.current_stage in ('.$quick_filter->stage.')');
                } else {
                    $status = [2,3];
                    $this->db->where_in('raa.status', $status);
                }
            }
            if (isset($quick_filter->status) == true && $quick_filter->status != '' && empty($quick_filter->status) == false) {
                $status = $quick_filter->status;
                $this->db->where_in('raa.application_process_status', $status);
            }
            if (!empty($quick_filter->oa_statuses)) {
                $this->db->where_in('rjaa.status', $quick_filter->oa_statuses);
            }
        }
        if(!empty($application_status)){
            $this->db->where_in('raa.application_process_status', '0');
        }

        # previous search where we had recruiters filter
        if (!empty($filter->recruiter_val)) {
            $this->db->where('raa.recruiter', $filter->recruiter_val);
        }

        # previous search where we had status and current stage filters
        if (!empty($filter->filter_val)) {
            if($filter->filter_val == -1)
                $this->db->where('raa.status in (2,3)');
            else
                $this->db->where('raa.current_stage in  ('.$filter->filter_val.') and raa.status not in (2,3)');
        }

        # previous search where we had start and end date
        if (!empty($start_date) && empty($end_date)) {
            $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") >= ', DateFormate($start_date, 'Y-m-d'));
        } elseif (!empty($start_date) && !empty($end_date)) {
            $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") >= ', DateFormate($start_date, 'Y-m-d'));
            $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") <= ', DateFormate($end_date, 'Y-m-d'));
        } elseif (!empty($end_date)) {
            $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") <= ', DateFormate($end_date, 'Y-m-d'));
        }

        if (!empty($filter->jobId)) {
            $this->db->where('raa.jobId', $filter->jobId);
        }

        # text search
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                
                $column_search = $src_columns[$i];
                $formated_date = '';
                if($column_search=='ra.hired_as' && ($filter->search=='Yes' || $filter->search=='No')){
                    $formated_date = $filter->search=='Yes' || $filter->search=='yes' ? '1' : '0';
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $formated_date);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $formated_date);
                    }
                }else if($column_search=='rjaa.status' && ($filter->search=='Sent' || $filter->search=='In progress' || $filter->search=='Submitted' || $filter->search=='Completed' || $filter->search=='Link Expired' || $filter->search=='Error' || $filter->search=='Moodle')){
                    $formated_date = $this->oa_status_label($filter->search);
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $formated_date);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $formated_date);
                    }
                }else{
                    if (strstr($column_search, " as") !== false) {  //firstname and lastname
                        $serch_column = explode(" as ", $column_search);
                        
                        $searchKey = trim($filter->search);
                        $searchKeyArray = preg_split('/\s+/', $searchKey);
                        $searchKey = implode(" ", $searchKeyArray);
                        
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $searchKey);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, trim($filter->search));
                    }
                }

            }
            $this->db->group_end();
        }
        
       
        $this->load->file(APPPATH.'Classes/common/ViewedLog.php');
        $viewedLog = new ViewedLog();
        // get entity type value
        $entity_type = $viewedLog->getEntityTypeValue('application');

        $select_column = array(
            'ra.id as applicant_id','ra.uuid', 'raa.created', 'raa.id','concat(p.firstname," ",p.lastname) as FullName',
            'raa.status', 'pe.email', 'pp.phone', 'ra.appId',
            'raa.jobId', "(concat(rtb.title)) as stage", "(concat(rs.stage,' - ',rs.title)) as sub_stage", "raa.referrer_url", "raa.channelId",
            "rjp.title as job_position",
            'raa.id AS application_id, raa.recruiter,raa.application_process_status',
            'ra.hired_as','raa.referred_by','ra.flagged_status', 'p.firstname', 'p.lastname','rjaa.status as oa_status'
        );

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);       

        $this->db->select("(select  CASE
        WHEN rja.status = 1 THEN 'Sent'
        WHEN rja.status = 2 THEN 'In progress'
        WHEN rja.status = 3 THEN 'Submitted'
        WHEN rja.status = 4 THEN  (SELECT concat( 'Completed',' (',`rja`.`percentage`,'%',')') from tbl_recruitment_job_assessment as rja where rja.status = 4  and `rja`.`application_id`=`raa`.`id` order by id DESC limit 1 )  
        WHEN rja.status = 5 THEN 'Link Expired'
        WHEN rja.status = 6 THEN 'Error'
        WHEN rja.status = 7 THEN 'Moodle'
        WHEN rja.status = 8 THEN 'Session Expired'
        ELSE ''
        END from tbl_recruitment_job_assessment as rja  

        WHERE `rja`.`application_id`=`raa`.`id` order by id DESC limit 1 ) as oa_status");
        $this->db->select('(SELECT id FROM tbl_recruitment_job_assessment AS raj WHERE `raj`.`application_id`=`raa`.`id` ORDER BY id DESC LIMIT 1) AS assessment_id');
        $this->db->select('(select concat(m.firstname," ",m.lastname) as recruiter_name from tbl_member as m inner join tbl_department as d on d.id = m.department AND d.short_code = "internal_staff" where m.uuid = raa.recruiter) as recruiter_name');

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
        $this->db->join('tbl_recruitment_job_assessment as rjaa', 'rjaa.id=(select max(id) from tbl_recruitment_job_assessment where raa.id=application_id)', 'left');
        $this->db->join('tbl_recruitment_job_position as rjp', 'rjp.id = raa.position_applied', 'inner');       

        $this->db->join('tbl_person as p', 'ra.person_id = p.id', 'inner');
        $this->db->join('tbl_person_email as pe', 'p.id = pe.person_id AND pe.primary_email = 1', 'inner');
        $this->db->join('tbl_person_phone as pp', 'p.id = pp.person_id AND pp.primary_phone = 1', 'inner');
        $this->db->join('tbl_recruitment_stage as rs', 'rs.id = raa.current_stage', 'inner');
        $this->db->join('tbl_recruitment_stage_label as rtb', 'rtb.id = rs.stage_label_id', 'inner');
       
        if ($application_id != '') {
            $this->db->where('raa.jobId', $application_id);
        }
        $this->db->where('ra.archive', 0);
        if($this->Common_model->check_is_bu_unit($application_data)) {
            $this->db->where('raa.bu_id', $application_data->business_unit['bu_id']);
        }
        $this->db->order_by($orderBy, $direction);
        $this->db->group_by('raa.id');
        $this->db->limit($limit, ($page * $limit));
          //list view filter condition
          if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
          }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();
        if (!empty($result)) {
            //fetch viewed by data
            $vlogs = [];
            $application_ids = array_map(function($item){
                return $item->id;
            }, $result);
            if (!empty($application_ids)) {
                $this->db->select(['vl.entity_id', "concat(m.firstname,' ',m.lastname) as viewed_by", 'vl.viewed_date', 'vl.viewed_by as viewed_by_id']);
                $this->db->from('tbl_viewed_log as vl');
                $this->db->join('tbl_member as m', 'vl.viewed_by=m.uuid and m.archive=0');
                $this->db->where_in('vl.entity_id', $application_ids);
                $query2 = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
                $result2 = $query2->result();

                foreach($result2 as $v) {
                    $vlogs[$v->entity_id] = $v;
                }
            }
            foreach ($result as $val) {                
                if ( array_key_exists($val->id, $vlogs) ) {
                    $val->viewed_by_id = $vlogs[$val->id]->viewed_by_id;
                    $val->viewed_by = $vlogs[$val->id]->viewed_by;
                    $val->viewed_date = $vlogs[$val->id]->viewed_date;
                }
                if ($val->channelId == 2 && $val->referrer_url != '')
                    $val->applied_through = !empty($val->referrer_url) ? getDomain($val->referrer_url) : 'N/A';
                else
                    $val->applied_through = (isset($val->channelId) && $val->channelId == 1 ? 'Seek' : (($val->channelId == 2) ? 'Website' : (($val->channelId == 3) ? 'HCM Software' : 'N/A')));
            }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'total_item' => $total_item);
        return $return;
    }
    /**
     * fetches all the recruitment stages and sub-stages
     */
    function get_all_recruitment_stages() {
        $select_column = array("rsl.title as label", "GROUP_CONCAT(rs.id) as value");

        $this->db->select($select_column);

        $this->db->from('tbl_recruitment_stage as rs');
        $this->db->join('tbl_recruitment_stage_label as rsl', 'rsl.id = rs.stage_label_id', 'inner');
        $this->db->where('rsl.archive', 0);
        $this->db->where('rs.archive', 0);
        $this->db->group_by("rsl.title");
        $this->db->order_by("rsl.id", "ASC");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        $obj = new stdClass();
        $obj->label = "Completed";
        $obj->value = "-1";
        $result[] = $obj;
        $return = array('status' => true, 'data' => $result);
        return $return;
    }

    function get_applicant_job_question_answer($applicant_id) {
        $this->db->select(['rasa.questionId', 'rasa.questionId', 'rjq.question', 'rjpd.channel as channelId', 'rasa.answer_status', 'rasa.answer']);

        $this->db->from('tbl_recruitment_applicant_seek_answer as rasa');
        $this->db->join('tbl_recruitment_job_question as rjq', 'rjq.id = rasa.questionId', 'inner');
        $this->db->join('tbl_recruitment_job_published_detail as rjpd', 'rjpd.jobId = rasa.jobId', 'inner');
//        $this->db->join('tbl_recruitment_channel as rc', 'rc.id = rjpd.channel', 'inner');

        $this->db->where('rasa.applicantId', $applicant_id);
        $this->db->group_by('rasa.questionId');
        $query = $this->db->get();
        $res = $query->result();

        $quest = array('website' => [], 'seek' => []);

        if (!empty($res)) {
            foreach ($res as $value) {
                if ($value->channelId == 1) {
                    $quest['seek'][] = (array) $value;
                } else {
                    $quest['website'][] = (array) $value;
                }
            }
        }

        return $quest;
    }

    function check_already_flag_applicant($applicant_id) {
        $this->db->select('applicant_id');
        $this->db->from('tbl_recruitment_flag_applicant');

        $this->db->where(['applicant_id' => $applicant_id, 'archive' => 0]);
        $this->db->where_in('flag_status', [1, 2]);

        $query = $this->db->get();
        return $query->result();
    }

    function flag_applicant($reqData, $adminId) {
        $flage_data = array(
            'applicant_id' => $reqData->applicant_id,
            'reason_id' => $reqData->reason_id,
            'reason_title' => $reqData->reason_title,
            'reason_note' => $reqData->reason_note,
            'flaged_request_by' => $adminId,
            'flaged_approve_by' => 0,
            'flag_status' => 1,
            'created' => DATE_TIME,
            'updated' => DATE_TIME,
            'archive' => 0,
        );

        $this->basic_model->insert_records('recruitment_flag_applicant', $flage_data, false);

        $this->basic_model->update_records('recruitment_applicant', ['flagged_status' => 1], ['id' => $reqData->applicant_id]);

        return true;
    }

    /**
     * fetches all the common flagging reasons
     */
    public function get_flag_reasons() {
        $this->db->select(['rfr.reason_title as label', 'rfr.id as value']);
        $this->db->from('tbl_recruitment_flag_reason as rfr');
        $query = $this->db->get();
        $res = $query->result();
        return $res;
    }

    /**
     * fetches the flag reason id if the same reason exists in recruitment_flag_reason
     * otherwise inserts a record and returns the newly created id
     */
    public function get_insert_flag_reason($reason_title) {
        $reason_data = $this->basic_model->get_row('recruitment_flag_reason', ['id'], ['reason_title' => $reason_title]);
        if (!$reason_data)
            $reason_id = $this->basic_model->insert_records('recruitment_flag_reason', ['reason_title' => $reason_title, "created" => DATE_TIME], $multiple = FALSE);
        else
            $reason_id = $reason_data->id;
        return $reason_id;
    }

    public function get_requirement_flaged_applicants($reqData, $adminId) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';

        $its_recruiter_admin = check_its_recruiter_admin($adminId);


        $src_columns = array('ra.id','ra.uuid', 'ra.appId', 'concat(ra.firstname," ",ra.middlename," ",ra.lastname) as FullName');
        $available_column = array('id','uuid', 'date_applide', 'FullName', 'status', 'updated', 'email', 'phone', 'appId', 'reason_note', 'reason_title', 'flagged_status', 'person_id');
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'ra.id';
            $direction = 'DESC';
        }

        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        if (!empty($filter->filter_val)) {
            if ($filter->filter_val == 'all') {
                $this->db->where_in('ra.flagged_status', [1, 2, 3]);
            } elseif ($filter->filter_val == 'pending') {
                $this->db->where('ra.flagged_status', 1);
            } elseif ($filter->filter_val == 'flagged') {
                $this->db->where('ra.flagged_status', 2);
            } elseif ($filter->filter_val == 'new') {
                $this->db->where('ra.flagged_status', 3);
            }
        }

        if (!$its_recruiter_admin) {
            $this->db->where('recruiter', $adminId);
        }

        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $searchKey = trim($filter->search);
                    $searchKeyArray = preg_split('/\s+/', $searchKey);
                    $searchKey = implode(" ", $searchKeyArray);
                    
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $searchKey);
                    
                    
                    /*
                    $searchKey = trim($filter->search);
                    $searchKeyArray = preg_split('/\s+/', $searchKey);
                    if(count($searchKeyArray) == 1) {
                        $this->db->or_like("ra.firstname", trim($searchKeyArray[0]));
                        $this->db->or_like("ra.lastname", trim($searchKeyArray[0]));
                    } else if(count($searchKeyArray) == 2){
                        $this->db->or_like("ra.firstname", trim($searchKeyArray[0]));
                        $this->db->or_like("ra.lastname", trim($searchKeyArray[0]));
                        $this->db->or_like("ra.firstname", trim($searchKeyArray[1]));
                        $this->db->or_like("ra.lastname", trim($searchKeyArray[1]));
                    } else {
                        $this->db->or_like($serch_column[0], trim($searchKey));
                    }
                    */
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }

        $select_column = array('ra.id','ra.uuid', 'ra.date_applide', 'concat(ra.firstname," ",ra.middlename," ",ra.lastname) as FullName', 'ra.status', 'ra.updated', 'ra_em.email', 'ra_ph.phone', 'ra.appId', 'rfa.reason_note', 'rfr.reason_title', 'ra.flagged_status', 'ra.person_id', '"" as job_position');

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select('(select concat(firstname," ",lastname) as recruiter_name from tbl_member inner join tbl_department ON  tbl_member.department=tbl_department.id and tbl_department.short_code!="external_staff" and tbl_member.archive=tbl_department.archive AND tbl_member.archive=0 where tbl_member.id = recruiter) as recruiter_name');

        $this->db->from('tbl_recruitment_applicant as ra');
        $this->db->join('tbl_recruitment_applicant_email as ra_em', 'ra_em.applicant_id = ra.id AND ra_em.primary_email = 1', 'inner');
        $this->db->join('tbl_recruitment_applicant_phone as ra_ph', 'ra_ph.applicant_id = ra.id AND ra_ph.primary_phone = 1', 'inner');
        $this->db->join('tbl_recruitment_flag_applicant as rfa', 'rfa.applicant_id = ra.id AND rfa.flag_status IN (1,2)', 'inner');
        $this->db->join('tbl_recruitment_flag_reason as rfr', 'rfr.id = rfa.reason_id', 'inner');

        $this->db->where('ra.archive', 0);

        $this->db->where(['ra.duplicated_status' => 0]);
        $this->db->order_by($orderBy, $direction);
        $this->db->group_by('ra.id');
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
//     last_query();
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();
        $person_ids = array_column($result, 'person_id');
        $profile_pics = [];
        if (!empty($person_ids)) {
            $rows = $this->db->query("select id, profile_pic from tbl_person where archive = 0 and id in ('".implode("', '", $person_ids)."')")->result_array();            
            if (!empty($rows)) {
                foreach($rows as $row) {
                    $profile_pics[$row['id']] = $row['profile_pic'];
                }
            }
        }
        if (!empty($result)) {
            foreach ($result as $val) {
                $x = $this->get_application_job_application_title($val->id);
                $val->job_position = $x['job_position'];
                $val->job_application = $x['job_application'];
                if (array_key_exists($val->person_id, $profile_pics)) {
                    $val->avatar = $profile_pics[$val->person_id];
                }
            }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $result);
        return $return;
    }

    function get_application_job_application_title($applicant_id) {
        $this->db->select(['rjp.title', 'raaa.id']);

        $this->db->from('tbl_recruitment_applicant_applied_application as raaa');
        $this->db->join('tbl_recruitment_job_position as rjp', 'raaa.position_applied = rjp.id', 'inner');
        $this->db->where('raaa.applicant_id', $applicant_id);
        $this->db->where('raaa.archive', 0);
        $query = $this->db->get();

        $res = $query->result_array();

        $return = ['job_position' => '', 'job_application' => $res];
        if (!empty($res)) {
            $return['job_position'] = implode(', ', array_column($res, 'title'));
        }

        return $return;
    }

    function get_job_postion_by_create_job() {
        $this->db->select(['rjp.title as label', 'rjp.id as value']);

        $this->db->from('tbl_recruitment_applicant_applied_application as raaa');
        $this->db->join('tbl_recruitment_job_position as rjp', 'raaa.position_applied = rjp.id', 'inner');
        $this->db->group_by('rjp.id');

        $query = $this->db->get();

        $res = $query->result();

        return $res;
    }

    function dont_flag_applicant($reqData, $adminId) {
        // update in applicant table request denied
        $update_data = array('flagged_status' => 0);
        $where_f = array('id' => $reqData->applicant_id);
        $this->basic_model->update_records('recruitment_applicant', $update_data, $where_f);

        // update in flag applicant table
        $update_f_d = array('flag_status' => 3, 'flaged_approve_by' => $adminId, 'updated' => DATE_TIME);
        $this->basic_model->update_records('recruitment_flag_applicant', $update_f_d, ['applicant_id' => $reqData->applicant_id]);

        $stages = $this->basic_model->get_record_where('recruitment_applicant_applied_application', ['id', 'current_stage'], ['applicant_id' => $reqData->applicant_id]);
        for ($i = 0; $i < count($stages); $i++) {

            $data['feed_title'] = 'Applicant and related applications are unflagged';
            $data['related_type'] = 4;
            $data['source_id'] = $stages[$i]->id;

            $this->Feed_model->save_feed($data, $adminId);
        }

        return true;
    }

    function flag_applicant_approve($reqData, $adminId) {
        // update in applicant table request approve
        $update_data = array('flagged_status' => 2, 'status' => 2, "rejected_date" => DATE_TIME);
        $where_f = array('id' => $reqData->applicant_id);
        $this->basic_model->update_records('recruitment_applicant', $update_data, $where_f);

        // update in flage applicant table
        $update_f_d = array('flag_status' => 2, 'flaged_approve_by' => $adminId, 'updated' => DATE_TIME);
        $this->basic_model->update_records('recruitment_flag_applicant', $update_f_d, ['applicant_id' => $reqData->applicant_id]);
        $this->basic_model->update_records('recruitment_applicant_stage_attachment', ['member_move_archive' => 2, 'archive' => 0], ['applicant_id' => $reqData->applicant_id]);

        // set all applications assosciated with this candidate to 'rejected'
        $this->basic_model->update_records('recruitment_applicant_applied_application', ['status' => 2, 'application_process_status' => 8, 'rejected_date' => DATE_TIME], ['applicant_id' => $reqData->applicant_id, 'application_process_status !=' => 7]);

        // remove token in user table to make as invalid req
        if(!empty($reqData->uuid)){
            $this->basic_model->update_records('users', ['status' => 0, 'token' => NULL, 'updated_at' => DATE_TIME], ['id' => $reqData->uuid, 'user_type'=> MEMBER_PORTAL]);
        }

        // set the current stage on each related application to 'unsuccessful'
        $stages = $this->basic_model->get_record_where('recruitment_applicant_applied_application', ['id', 'current_stage'], ['applicant_id' => $reqData->applicant_id]);
        
        for ($i = 0; $i < count($stages); $i++) {
            $this->basic_model->update_records('recruitment_applicant_stage', ['status' => 4, 'action_at' => DATE_TIME], ['applicant_id' => $reqData->applicant_id, 'application_id' => $stages[$i]->id, 'stageId' => $stages[$i]->current_stage]);

            $data['feed_title'] = 'Applicant and related applications are flagged';
            $data['related_type'] = 4;
            $data['source_id'] = $stages[$i]->id;

            $this->Feed_model->save_feed($data, $adminId);
        }

        return true;
    }

    public function get_recruitment_duplicate_applicants($reqData, $adminId) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';

        $its_recruiter_admin = check_its_recruiter_admin($adminId);

        // create sub query first
        $this->db->select("group_concat(CONCAT_WS('@__@BR@__@',s_rjp.title,DATE_FORMAT(s_raaa.created,'%d/%m/%Y')) SEPARATOR '@__@@__@') as job_position", false);
        //$this->db->select("group_concat(title SEPARATOR '@__@@__@') as job_position", false);
        $this->db->from('tbl_recruitment_applicant_applied_application s_raaa');
        $this->db->join("tbl_recruitment_applicant s_ra", "s_ra.id=s_raaa.applicant_id", "inner");
        $this->db->join("tbl_recruitment_job_position s_rjp", "s_rjp.id=s_raaa.position_applied", "inner");
        $this->db->where("s_ra.id=ra.duplicatedId", null, false);
        $sub_query = $this->db->get_compiled_select();

        $this->db->select("CASE WHEN status=1 THEN 'In Progress' WHEN status=2 THEN 'Rejected' WHEN status=3 THEN 'Hired' ELSE '' END", false);
        $this->db->from("tbl_recruitment_applicant sub_ra");
        $this->db->where("sub_ra.id= ra.duplicatedId", null, false);
        $sub_query1 = $this->db->get_compiled_select();

        $this->db->select("sub_ra.archive", false);
        $this->db->from("tbl_recruitment_applicant sub_ra");
        $this->db->where("sub_ra.id= ra.duplicatedId", null, false);
        $sub_query_archive_status = $this->db->get_compiled_select();


        $src_columns = array('ra.id', 'ra.appId', 'REPLACE(concat(COALESCE(ra.firstname,"")," ",COALESCE(ra.middlename," ")," ",COALESCE(ra.lastname," ")),"  "," ") as FullName', 'ra.appID', 'ra.date_applide', 'rjp.title', 'rd.name', "DATE_FORMAT(`ra`.`date_applide`, '%d/%m/%Y')");
        $available_column = array("id", "date_applide", "FullName", "status", "created", "email", "rap.phone", "job_position", 
                                  "recruitment_area", "relevantNotes", "appId", "currentApplicant");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'ra.id';
            $direction = 'DESC';
        }

        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));
        
        if (!empty($filter->filter_by) && in_array($filter->filter_by, [1, 2, 3])) {
            $this->db->where('rads.status', $filter->filter_by);
        }

        if (!$its_recruiter_admin) {
            $this->db->where('recruiter', $adminId);
        }

        if (!empty($filter->srch_box)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                /* if (strstr($column_search, "date_format(") !== false) {
                  $this->db->or_like($column_search, $filter->srch_box,false);
                  }else */ if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $searchKey = trim($filter->search);
                    $searchKeyArray = preg_split('/\s+/', $searchKey);
                    $searchKey = implode(" ", $searchKeyArray);
                    
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $searchKey);
                    
                    /*
                    $searchKey = trim($filter->srch_box);
                    $searchKeyArray = preg_split('/\s+/', $searchKey);
                    if(count($searchKeyArray) == 1) {
                        $this->db->or_like("ra.firstname", trim($searchKeyArray[0]));
                        $this->db->or_like("ra.lastname", trim($searchKeyArray[0]));
                    } else if(count($searchKeyArray) == 2){
                        $this->db->or_like("ra.firstname", trim($searchKeyArray[0]));
                        $this->db->or_like("ra.lastname", trim($searchKeyArray[0]));
                        $this->db->or_like("ra.firstname", trim($searchKeyArray[1]));
                        $this->db->or_like("ra.lastname", trim($searchKeyArray[1]));
                    } else {
                        $this->db->or_like($serch_column[0], $searchKey);
                    }
                    */
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->srch_box);
                }
            }
            $this->db->group_end();
        }


        $select_column = array('ra.id', 'ra.date_applide', 'concat_ws(" ",ra.firstname,ra.middlename,ra.lastname) as FullName', 'ra.status', 'ra.created',
            'rae.email', 'rap.phone', "group_concat(rjp.title) as job_position", "group_concat(rd.name) as recruitment_area", "COALESCE(rads.relevant_note,'') as relevantNotes",
            'ra.appId', 'ra.duplicatedId as currentApplicant');

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("case when rads.status=1 then 'Pending'
        when rads.status=2 then 'Accepted'
        when rads.status=3 then 'Rejected'
        else 'Pending' end as duplicate_application_status", false);
        $this->db->select('(' . $sub_query . ') as current_job_possition', false);
        $this->db->select('(' . $sub_query1 . ') as current_applicant_status', false);
        $this->db->select('(' . $sub_query_archive_status . ') as current_applicant_active_status', false);
        $this->db->from('tbl_recruitment_applicant as ra');
        $this->db->join('tbl_recruitment_applicant_duplicate_status as rads', 'ra.id=rads.applicant_id and ra.archive=0 and rads.archive=ra.archive and ra.duplicated_status=1', 'inner');
        $this->db->join('tbl_recruitment_applicant_applied_application as raaa', 'raaa.applicant_id =ra.id and ra.archive=0 and raaa.archive=ra.archive', 'inner');
        $this->db->join('tbl_recruitment_job_position as rjp', 'rjp.id=raaa.position_applied', 'inner');
        $this->db->join('tbl_recruitment_department as rd', 'rd.id=raaa.recruitment_area', 'inner');
        $this->db->join('tbl_recruitment_applicant_email as rae', 'rae.applicant_id = ra.id and rae.archive=0 and rae.primary_email=1', 'inner');
        $this->db->join('tbl_recruitment_applicant_phone as rap', 'rap.applicant_id = ra.id and rap.archive=0 and rap.primary_phone=1', 'inner');
        $this->db->where('ra.archive', 0);
        $this->db->order_by($orderBy, $direction);
        $this->db->group_by('ra.id');
        $this->db->having('current_applicant_active_status=0');
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query(1);
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();

        //count is use for number of pages
        $return = array('status' => true, 'count' => $dt_filtered_total, 'data' => $result);
        return $return;
    }

    public function update_duplicate_application_status($reqData, $statusType = []) {
        $response = [];
        $appId = isset($reqData->id) ? $reqData->id : '';
        $status = isset($reqData->status) ? $reqData->status : '';
        $response = ['status' => false, 'error' => 'Something went wrong.'];

        if (!empty($appId) && $appId > 0 && in_array($status, array_keys($statusType))) {

            $dataUpdate = [];
            if ($status == 'reject') {
                $dataUpdate = ['status' => 3, 'accept_sub_status' => 0, 'action_taken_date' => DATE_TIME];
            } elseif ($status == 'accept_addnem') {
                $dataUpdate = ['status' => 2, 'accept_sub_status' => 1, 'action_taken_date' => DATE_TIME];
            } elseif ($status == 'accept_editexisting') {
                $dataUpdate = ['status' => 2, 'accept_sub_status' => 2, 'action_taken_date' => DATE_TIME];
            }

            $dd = !empty($dataUpdate) ? $this->basic_model->update_records('recruitment_applicant_duplicate_status', $dataUpdate, array('status' => 1, 'archive' => 0, 'applicant_id' => $appId)) : false;

            if ($dd) {
                if ($status == 'accept_addnem') {
                    $this->add_applied_application_to_current_application($appId);
                }
                $response = ['status' => true];
            } else {
                $response = ['status' => false, 'error' => 'Something went wrong.'];
            }
        }
        return $response;
    }

    public function update_duplicate_application_relevant_note($reqData) {
        $response = ['status' => false, 'error' => 'Something went wrong.'];
        $appId = isset($reqData->id) ? $reqData->id : '';
        $notes = isset($reqData->relevant_note) ? $reqData->relevant_note : '';

        if (!empty($appId) && $appId > 0 && !empty($notes)) {

            $dataUpdate = ['relevant_note' => $notes];
            $dd = !empty($dataUpdate) ? $this->basic_model->update_records('recruitment_applicant_duplicate_status', $dataUpdate, array('archive' => 0, 'applicant_id' => $appId)) : false;

            if (!empty($dataUpdate)) {
                $response = ['status' => true];
            } else {
                $response = ['status' => false, 'error' => 'Relevant note can not be blank.'];
            }
        }
        return $response;
    }

    private function add_applied_application_to_current_application($applicantId) {
        $duplicatApplicantDetails = $this->basic_model->get_row('recruitment_applicant', ['duplicatedId'], ['id' => $applicantId, 'duplicated_status' => 1]);
        if ($duplicatApplicantDetails) {
            $currentApplicantId = $duplicatApplicantDetails->duplicatedId;
            $result = $this->basic_model->get_result('recruitment_applicant_applied_application', ['applicant_id' => $applicantId, 'archive' => 0], ['position_applied', 'recruitment_area', 'employement_type', 'channelId', 'status'], [], 1);
            if ($result) {
                if (!empty($result) && is_array($result)) {
                    $insData = array_map(function($val) use($currentApplicantId, $applicantId) {
                        return $val + ['applicant_id' => $currentApplicantId, 'from_applicant_id' => $applicantId, 'from_status' => 1];
                    }, $result);

                    $this->basic_model->insert_update_batch('insert', 'recruitment_applicant_applied_application', $insData);
                }
            }
        }
    }

    /**
     * used in authenticating the applicant/member email address
     * mainly used in ipad app and in web app login
     */
    public function auth_applicant_info($email)
    {
        // using query check username
        $column = array(
            'ra.id',
            'ra.id as applicant_id',
            'm.id as member_id',
            'concat(p.firstname," ",p.lastname) as applicant_name',
            'u.password',
            'u.status',
            'u.username',
            'ra.person_id',
            'm.status as member_status',
            'p.profile_pic as avatar'
        );
        // short_code = internal_staff mean only internal department user can login
        $where = array(
            'u.username' => $email,
            'u.archive' => 0,
            'ra.archive' => 0
        );
        $this->db->select($column);
        $this->db->from('tbl_recruitment_applicant as ra');
        $this->db->join('tbl_users u', 'u.id = ra.uuid', 'INNER');
        $this->db->join('tbl_person p', 'p.id = ra.person_id', 'INNER');
        $this->db->join('tbl_member m', 'p.id = m.person_id and ra.id=m.applicant_id', 'LEFT');
        $this->db->where($where);
        $query = $this->db->get();

        return $query->row();
    }

    /**
     * used in authenticating the member email address
     * mainly used in ipad app and in web app login
     */
    public function auth_member_info($email)
    {
        // using query check username
        $column = array(
            'm.id as member_id',
            'concat(p.firstname," ",p.lastname) as fullname',
            'u.password',
            'p.status',
            'm.fullname as member_fullname',
            'u.username',
            'm.person_id',
            'm.status as member_status',
            'p.profile_pic as avatar'
        );
        // short_code = internal_staff mean only internal department user can login
        $where = array(
            'u.username' => $email,
            'p.archive' => 0,
            'm.archive' => 0
        );
        $this->db->select($column);
        $this->db->from('tbl_member as m');
        $this->db->join('tbl_users u', 'u.id = m.uuid', 'INNER');
        $this->db->join('tbl_person p', 'p.id = m.person_id', 'INNER');
        $this->db->where($where);
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * used in authenticating the applicant/member email address
     * mainly used in ipad app and in web app login
     */
    public function auth_applicant_info_by_member_id($member_id)
    {
        // using query check username
        $column = array(
            'ra.id',
            'ra.id as applicant_id',
            'm.id as member_id',
            'concat(p.firstname," ",p.lastname) as applicant_name',
            'p.password',
            'p.status',
            'p.username',
            'ra.person_id'
        );
        // short_code = internal_staff mean only internal department user can login
        $where = array(
            'm.id' => $member_id,
            'p.archive' => 0,
            'ra.archive' => 0
        );
        $this->db->select($column);
        $this->db->from('tbl_recruitment_applicant as ra');
        $this->db->join('tbl_person p', 'p.id = ra.person_id', 'INNER');
        $this->db->join('tbl_member m', 'p.id = m.person_id', 'LEFT');
        $this->db->where($where);
        $query = $this->db->get();
        return $query->row();
    }

    function get_applicant_info($applicantId, $adminId='',$reqData=NULL) {
        $its_recruiter_admin = check_its_recruiter_admin($adminId);


        $this->db->select("Case when ra.recruiter>0 THEN (select concat(a.firstname,' ',a.lastname) as assign_recruiter from tbl_member as a  INNER JOIN tbl_department as d ON d.id = a.department AND d.short_code = 'internal_staff' where ra.recruiter = a.id) ELSE '' END as assign_recruiter");
        $this->db->select(["ra.uuid","ra.flagged_status", "ra.jobId", "ra.appId", "ra.id", "ra.status", "ra.current_stage", "ra.duplicated_status", "ra.pin", "ra.hired_as", "ra.person_id", "ra.flagged_status","ra.bu_id"]);
        $this->db->from('tbl_recruitment_applicant as ra');
        $this->db->where('ra.id', $applicantId);
        if (!$its_recruiter_admin) {
            // $this->db->where('ra.recruiter', $adminId);
        }
        if($this->Common_model->check_is_bu_unit($reqData)) {
            $this->db->where('ra.bu_id', $reqData->business_unit['bu_id']);
        }
        $query = $this->db->get();


        $res = $query->row_array();
        if (!empty($res)) {
            $res['applicant_status'] = ($res['status'] == 1) ? 'In-Progress' : (($res['status'] == 2) ? "Rejected" : "Hired");
        }
        // for fname, lname, pref, prev, title and dob
        // use tbl_person as source of info

        $person_where=['ra.id' => $res['id']];
        if($this->Common_model->check_is_bu_unit($reqData)) {
            $person_where=['ra.id' => $res['id'],'ra.bu_id' => $reqData->business_unit['bu_id']];
        }
        $personQuery = $this->db
                ->from('tbl_recruitment_applicant ra')
                ->join('tbl_person p', 'p.id = ra.person_id', 'INNER')
                ->where($person_where)
                ->select([
                    'p.firstname AS firstname',
                    'p.lastname AS lastname',
                    "concat(p.firstname, ' ', p.lastname) AS fullname",
                    "p.preferred_name",
                    "p.previous_name",
                    'p.middlename',
                    "DATE_FORMAT(p.date_of_birth, '%d/%m/%Y') AS dob",
                    "p.title",
                    "ra.id applicantID",
                    "ra.recruiter as owner",
                    'p.profile_pic as avatar',
                    'p.username'
                ])
                ->get();
    
        $person = $personQuery->row_array();
        if ($person) {
            $res = array_merge($res, $person);
        }
     
        return $res;
    }

    // Get the application info
    function get_application_info($applicantId,$applicationId, $adminId,$applicant_data) {
        $where = ['ra.id' => $applicantId];
        $this->db->select("Case when ra.recruiter>0 THEN (select concat(a.firstname,' ',a.lastname) as assign_recruiter from tbl_member as a  INNER JOIN tbl_department as d ON d.id = a.department AND d.short_code = 'internal_staff' where ra.recruiter = a.id) ELSE '' END as assign_recruiter");
        $this->db->select(["concat(ra.firstname,' ',ra.lastname) as  fullname", "ra.flagged_status", "ra.jobId", "ra.appId", "ra.id","ra.uuid", "ra.status", "ra.lastname", "ra.firstname", "ra.current_stage", "ra.duplicated_status", "ra.pin", "ra.hired_as", "ra.person_id", "ra.flagged_status",'ra.bu_id']);
        $this->db->from('tbl_recruitment_applicant as ra');
        if($this->Common_model->check_is_bu_unit($applicant_data)) {
            
            $where = ['ra.id' => $applicantId,'ra.bu_id' => $applicant_data->business_unit['bu_id']];
        }else{
            $this->db->select("tbu.business_unit_name");
            $this->db->join("tbl_business_units tbu", "tbu.id = ra.bu_id", "INNER");
        }
       
        $this->db->where($where);     
        $query = $this->db->get();
       

        $res = $query->row_array();
        
        if (!empty($res)) {
            $res['applicant_status'] = ($res['status'] == 1) ? 'In-Progress' : (($res['status'] == 2) ? "Rejected" : "Hired");
        }

        // for fname, lname, pref, prev, title and dob
        // use tbl_person as source of info
      
        $personQuery = $this->db
                ->from('tbl_recruitment_applicant ra')
                ->join('tbl_person p', 'p.id = ra.person_id', 'INNER')
                ->join("tbl_recruitment_applicant_applied_application raaa", "raaa.applicant_id = ra.id  and raaa.id=".$applicationId."" , "INNER")
                ->where($where)
                ->select([
                    'p.firstname AS firstname',
                    'p.lastname AS lastname',
                    'p.middlename AS middlename',
                    "concat(p.firstname, ' ', p.lastname) AS fullname",
                    "p.preferred_name",
                    "p.previous_name",
                    "DATE_FORMAT(p.date_of_birth, '%d/%m/%Y') AS dob",
                    "p.title",
                    "ra.id applicantID",
                    "ra.recruiter as owner",
                    "raaa.referred_by"
                ])
                ->get();

        $person = $personQuery->row_array();
        if ($person) {
            $res = array_merge($res, $person);
        }
      
        return $res;
    }

    function get_applicant_phone($applicant_id) {
        try{
            $where = array('applicant_id' => $applicant_id, 'archive' => 0);
            $column = array('id', 'phone', 'primary_phone');

            $results = $this->basic_model->get_record_where('recruitment_applicant_phone', $column, $where);

            // use tbl_person_phone as phone source
            // override phone and primary_phone values of $results above
            foreach ($results as $i => $result) {
                $this->db
                    ->from('tbl_recruitment_applicant a')
                    ->join('tbl_person_phone pp', 'a.person_id = pp.person_id', 'INNER')
                    ->where([
                        'a.id' => $applicant_id,
                        'pp.archive' => 0,
                        'pp.primary_phone' => 1
                    ])
                    ->select(['pp.*']);

                # Throw exception if db error occur
                if (!$person_phone = $this->db->get()) {               
                    $db_error = $this->db->error();
                    throw new Exception('Something went wrong!');
                }
                $person_phone = $person_phone->row_array();

                if ($person_phone) {
                    $results[$i]->phone = $person_phone['phone'];
                    $results[$i]->primary_phone = $person_phone['primary_phone'];
                    $results[$i]->person_phone_id = $person_phone['id'];
                }
            }

            return $results;
        }catch(\Exception $e){
            return array('status' => false, 'error' => 'Something went wrong');            
        }
        
    }

    /**
     * when resend login details is requested from the applicant info page
     * setting the temp password and emailing the loging details to the applicant
     */
    function send_applicant_login($reqData, $adminId) {
        if (empty($reqData['applicant_id'])) {
            $response = ['status' => false, 'error' => "ID Missing"];
            return json_encode($response);
        }

        // get applicant details
        $appdetails = $this->get_applicant_info($reqData['applicant_id'], $adminId);
        if(empty($appdetails)) {
            $response = ['status' => false, 'error' => "Applicant not found"];
            return json_encode($response);
        }

        $appemail = $this->get_applicant_email($reqData['applicant_id'], true);
        if(empty($appemail)) {
            $response = ['status' => false, 'error' => "Applicant email not found"];
            return json_encode($response);
        }
        // get auth users data
        $where = array('username' => $appemail[0]->email,"user_type" =>MEMBER_PORTAL, 'archive' => 0);
        $users = $this->basic_model->get_row('users', ['id'], $where);       

        # update person record with new password
        $rand = mt_rand(10, 100000);
        $token = encrypt_decrypt('encrypt', $rand);
        $UserUuid = '';
        
        if(!empty($users)){
            $UserUuid = $users->id;
            $where = array('id' => $UserUuid, 'user_type'=>MEMBER_PORTAL);
            $this->basic_model->update_records('users', $data = array('password_token' => $token), $where);
        }else{
            $UserUuid = $this->basic_model->insert_records('users', ["username"=>$appemail[0]->email,"status"=>1,"password_token"=>$token, "user_type"=>MEMBER_PORTAL], $multiple = FALSE);

            $user_data['uuid'] = $UserUuid;
            $where = array('id' => $reqData['applicant_id']);
            $this->basic_model->update_records('recruitment_applicant', $data = array('uuid' => $UserUuid), $where);
            if($appdetails['person_id']){
                $this->basic_model->update_records('person', ['uuid' => $UserUuid], ['id'=>$appdetails['person_id']]);
            }
            
        }

        $this->load->model('RecruitmentAppliedForJob_model');
        $this->RecruitmentAppliedForJob_model->send_new_applicant_login_email([
            'firstname' => $appdetails['firstname'],
            'lastname' => $appdetails['lastname']."<br><br>",
            'email' => $appemail[0]->email,
            'userId' => $reqData['applicant_id'],
            'admin_id' => $adminId,          
            'function_content' => "<br><br><a href=" .  $this->config->item('member_webapp_url')."/" . "reset_password/" . encrypt_decrypt('encrypt',  $UserUuid) . '/' . $token . '/' . encrypt_decrypt('encrypt', strtotime(DATE_TIME)) . '/' . encrypt_decrypt('encrypt', 'forgot_password') . '/' . encrypt_decrypt('encrypt', '2') . "  style='color: #0000ff;width: auto;padding: 0;text-align: center;'>Link</a>",
        ]);

        $response = ['status' => true, 'msg' => "Successfully sent the login details to ".$appemail[0]->email];
        return json_encode($response);
    }

    function get_applicant_email($applicant_id, $primary=false) {
        try{
            $where = array('applicant_id' => $applicant_id, 'archive' => 0);
        if($primary)
        $where["primary_email"] = 1;
        $column = array('id', 'email', 'primary_email');

        $results = $this->basic_model->get_record_where('recruitment_applicant_email', $column, $where);

        // use tbl_person_email as email source
        // override email and primary_email values of $results above
        foreach ($results as $i => $result) {
               $this->db
                    ->from('tbl_recruitment_applicant a')
                    ->join('tbl_person_email pe', 'a.person_id = pe.person_id', 'INNER')
                    ->where([
                        'a.id' => $applicant_id,
                        'pe.archive' => 0,
                        'pe.primary_email' => 1
                    ])
                    ->select(['pe.*']);                    

                # Throw exception if db error occur
                if (!$person_email = $this->db->get()) {               
                    $db_error = $this->db->error();
                    throw new Exception('Something went wrong!');
                }
                $person_email = $person_email->row_array();

            if ($person_email) {
                $results[$i]->email = $person_email['email'];
                $results[$i]->primary_email = $person_email['primary_email'];
                $results[$i]->person_email_id = $person_email['id'];
            }
        }

        return $results;
        }catch(\Exception $e){
            return array('status' => false, 'error' => 'Something went wrong');            
        }
        
    }

    function get_applicant_reference($applicant_id) {
        try{
            $where = array('rar.applicant_id' => $applicant_id, 'rar.archive' => 0);
            $column = array('rar.id', 'rar.name', 'rar.email', 'rar.phone', 'rar.status', 'rar.relevant_note', 'rar.written_reference');

            $this->db->select("(select form_id from tbl_recruitment_form_applicant as rfa where rfa.reference_id = rar.id AND rfa.archive = 0) as form_id");
            $this->db->select("(select id from tbl_recruitment_form_applicant as rfa where rfa.reference_id = rar.id  AND rfa.archive = 0) as interview_applicant_form_id");

            $this->db->select($column);
            $this->db->from("tbl_recruitment_applicant_reference as rar");
            $this->db->where($where);

            // $res = $this->db->get()->result();

            if (!$query = $this->db->get()) {               
                $db_error = $this->db->error();
                throw new Exception('Something went wrong!');
            }

            $res = $query->result();

            if (!empty($res)) {
                foreach ($res as $val) {
                    $val->interview_status = $val->form_id > 0 ? true : false;
                    $val->interview_form_id = $val->form_id > 0 ? $val->form_id : false;
                    $val->written_reference = $val->written_reference > 0 ? true : false;
                    $val->interview_applicant_form_id = $val->interview_applicant_form_id ?? false;
                }
            }

            return $res;
        } catch(\Exception $e){
            return array('status' => false, 'error' => 'Something went wrong');            
        }
        
    }   

    function get_applicant_address($applicant_id, $is_primary = null) {
        return $this->query_applicant_address($applicant_id, $is_primary)->row();
    }

    /*
     * private
     */
    function query_applicant_address($applicant_id, $is_primary = null) {
        try{
            $where = array('applicant_id' => $applicant_id);
            if (isset($is_primary)) {
            $where['primary_address'] = $is_primary;
            }

            $this->db->select(array('raa.id', 'raa.street', 'raa.city', 'raa.postal', 's.name as stateName', 'raa.state', 'raa.primary_address','raa.unit_number','raa.is_manual_address','raa.manual_address'));
            $this->db->select(array("concat(raa.street,', ',raa.city,' ',s.name,' ',raa.postal) as address"));
            $this->db->from('tbl_recruitment_applicant_address as raa');
            $this->db->join('tbl_state as s', 's.id = raa.state', 'left');

            $this->db->where($where);
            # Throw exception if db error occur
            if (!$query = $this->db->get()) {               
                $db_error = $this->db->error();
                throw new Exception('Something went wrong!');
            }
            return $query;
        }catch(\Exception $e){
            return array('status' => false, 'error' => 'Something went wrong');            
        }
        
    }

    function get_applicant_job_application($applicant_id) {
        $this->db->select(['rjp.title as position_applied', 'raaa.position_applied as position_applied_id', 'raaa.id', 'rd.name as recruitment_area', 'raaa.recruitment_area as recruitment_area_id',
            'raaa.employement_type as employement_type_id', 'r.display_name as employement_type', 'raaa.channelId as channel_id', 'raaa.referrer_url', 'rc.channel_name as channel',
            'raaa.jobId',
            'raaa.applicant_id',
            'raaa.prev_application_process_status',
            "CONCAT_WS(' ', recruiter.firstname, recruiter.lastname) as recruiter_fullname",
            'raaa.status',
            "(
            CASE
                WHEN raaa.status = 0 THEN 'Unknown'
                WHEN raaa.status = 1 THEN 'In-progress'
                WHEN raaa.status = 2 THEN 'Rejected'
                WHEN raaa.status = 3 THEN 'Completed'
                ELSE 'Unknown'
            END
        ) as status_label",
        'raaa.application_process_status',
            "(
            CASE
                WHEN raaa.application_process_status = 0 THEN 'New'
                WHEN raaa.application_process_status = 1 THEN 'Screening'
                WHEN raaa.application_process_status = 2 THEN 'Interviews'
                WHEN raaa.application_process_status = 3 THEN 'References'
                WHEN raaa.application_process_status = 4 THEN 'Documents'
                WHEN raaa.application_process_status = 5 THEN 'In progress'
                WHEN raaa.application_process_status = 6 THEN 'CAB'
                WHEN raaa.application_process_status = 7 THEN 'Hired'
                WHEN raaa.application_process_status = 8 THEN 'Unsuccessful'
                ELSE 'Unknown'
            END
        ) as process_status_label",
                // ABOVE: status_label (except 'Unknown') similar to ones mentioned in tbl_recruitment_applicant.status.
                // If you have status label of 'Unknown' displayed means something's wrong with your code
        ]);

        $this->db->from('tbl_recruitment_applicant_applied_application as raaa');
        $this->db->join('tbl_recruitment_job_position as rjp', 'raaa.position_applied = rjp.id', 'inner');
        $this->db->join('tbl_recruitment_department as rd', 'raaa.recruitment_area = rd.id', 'inner');
        $this->db->join('tbl_references as r', 'raaa.employement_type = r.id', 'inner');
        $this->db->join('tbl_recruitment_channel as rc', 'raaa.channelId = rc.id', 'inner');
        $this->db->join('tbl_member as recruiter', 'raaa.recruiter = recruiter.uuid', 'LEFT'); // let's use LEFT JOIN because raaa.recruiter is nullable and isn't guaranteed to be filled

        $this->db->where('raaa.applicant_id', $applicant_id);
        $this->db->where('raaa.archive', 0);
        $query = $this->db->get();

        $res = $query->result();
        if (!empty($res)) {
            foreach ($res as $value) {
                if ($value->channel_id == 2 && $value->referrer_url != '')
                    $value->channel = !empty($value->referrer_url) ? getDomain($value->referrer_url) : 'N/A';


                $value->job_details = $this->db
                        ->from('tbl_recruitment_job job')
                        ->join('tbl_recruitment_job_category job_category', 'job.category = job_category.id', 'INNER')
                        ->join('tbl_recruitment_job_category job_sub_category', 'job.sub_category = job_sub_category.id', 'INNER')
                        ->where(['job.id' => $value->jobId])
                        ->select([
                            'job.*',
                            'job_category.name AS job_category_label',
                            'job_sub_category.name AS job_sub_category_label',
                        ])
                        ->get('', 1)
                        ->row_array();

                // Find all forms submitted by application ID
                // @todo: To improve performance, you may want to eager-load this
                $value->submitted_forms = $this->db
                        ->from('tbl_recruitment_form_applicant fa')
                        ->join('tbl_recruitment_form f', 'f.id = fa.form_id', 'LEFT')
                        ->join('tbl_recruitment_interview_type i', 'i.id = f.interview_type', 'LEFT')
                        ->where(['fa.application_id' => $value->id,])
                        ->select([
                            'fa.*',
                            'f.interview_type as form_interview_type',
                            'i.name AS form_interview_type_label',
                            'i.key_type AS form_interview_type_key_type',
                            'f.interview_type AS form_category',
                            'i.name AS form_category_label',
                            'i.key_type AS form_category_key_type',
                            'f.title AS form_title',
                        ])
                        ->order_by('fa.date_created', 'DESC')
                        ->get()
                        ->result();
            }
        }

        return $res;
    }

    function get_applicant_stage_details($applicant_id, $application_id = 0) {
        $applicant_id = $this->db->escape_str($applicant_id, true);
        $application_id = $this->db->escape_str($application_id, true);
        $this->db->select(['rsl.id', 'rsl.title', 'rsl.stage_number', 'rsl.key_name']);
        $this->db->join('tbl_recruitment_job_stage as rjs', 'rjs.stage_id = rsl.id AND rjs.archive = 0', 'inner');
        $this->db->join('tbl_recruitment_applicant_applied_application as raaa', 'raaa.jobId = rjs.jobId AND raaa.id =' . $application_id, 'inner');
        $this->db->from("tbl_recruitment_stage_label as rsl");
        $this->db->where(["rsl.archive" => 0]);
        $this->db->order_by('stage_order_by', 'asc');
        $query = $this->db->get();
        $main_stage = $query->result();

        $this->db->select(['rs.title', 'rs.stage', 'ras.action_by', 'ras.action_at', 'rs.stage_label_id', 'rs.id', 'rs.stage_key', 'trsl.stage_number', 'trsl.display_stage_number']);
        $this->db->select("CASE
        WHEN ras.status is NULL THEN (1)
        ELSE ras.status
        END as stage_status", false);

        $this->db->select("CASE
        WHEN ras.action_by > 0 THEN (select concat(firstname,' ',lastname) as fullname from tbl_member where id = ras.action_by)
        ELSE ''
        END as action_username", false);

        $this->db->from('tbl_recruitment_stage as rs');
        $this->db->join('tbl_recruitment_applicant_stage as ras', 'ras.stageId = rs.id AND ras.archive = 0 AND ras.applicant_id = ' . $applicant_id . ' AND ras.application_id = ' . $application_id, 'LEFT');
        $this->db->join('tbl_recruitment_stage_label as trsl', 'trsl.id = rs.stage_label_id AND trsl.archive = 0', 'inner');

        $this->db->join('tbl_recruitment_job_stage as rjs', "rjs.stage_id = trsl.id AND rjs.archive = 0 AND rjs.jobId=(select jobId from tbl_recruitment_applicant_applied_application where id = '$application_id' AND applicant_id = '$applicant_id' AND archive = 0 limit 1)", 'inner');
        // $this->db->join('tbl_recruitment_applicant as ra', 'ra.jobId = rjs.jobId AND ra.id ='.$applicant_id, 'inner');

        $this->db->where('rs.archive', 0);

        $this->db->order_by('rs.stage_order', 'asc');
        $this->db->group_by('rs.id');

        $query = $this->db->get();
        #last_query();
        $stage_details = $query->result();

        $mapping_coponent = get_all_stage_mapping_component();
        $sub_stages = [];
        $main_stage_status = [];
        if (!empty($stage_details)) {
            foreach ($stage_details as $val) {
                $val->component_name = $mapping_coponent[$val->stage_key];
                if ($val->stage_status)
                    $main_stage_status[$val->stage_label_id][$val->stage] = array('stage_status' => $val->stage_status, 'action_username' => $val->action_username, 'action_at' => $val->action_at, 'display_stage_number' => $val->display_stage_number);
                $sub_stages[$val->stage_label_id][] = (array) $val;
            }
        }
        if (!empty($main_stage)) {
            foreach ($main_stage as $key => $val) {
                $x = $main_stage_status[$val->id];
                $x = $this->get_main_stage_status($x);

                $val->sub_stage = $sub_stages[$val->id];
                $main_stage[$key] = array_merge((array) $val, (array) $x);
            }
        }
        return $main_stage;
    }

    function get_main_stage_status($data) {
        if (!empty($data)) {

            $unsuccess = false;
            $success = false;
            $inprogress = false;

            foreach ($data as $val) {
                $val = (object) $val;

                if ($val->stage_status == 4) { // complete
                    $return = $val;
                    $unsuccess = true;
                } elseif ($val->stage_status == 2 && (!$unsuccess)) { // in progress
                    $return = $val;
                    $inprogress = true;
                } elseif ($val->stage_status == 3 && (!$unsuccess && !$success)) { // complete
                    $return = $val;
                    $success = true;
                } elseif ($val->stage_status == 1 && (!$unsuccess && !$success && !$inprogress)) { // pending
                    $return = $val;
                    $inprogress = true;
                }
            }

            return $return;
        }
    }

    function get_applicant_progress($applicantId, $application_id) {
        # get stage count dynamically
        # now using the applicant_applied_application table to get the count of total stages
        # using the jobid of the applied application
        $query = $this->get_applicant_stage_by_jobs_id($applicantId, $application_id);
        $count = $query->num_rows();
        $stage_count = ($count > 0) ? $count : 13;

        $this->db->select(['count(DISTINCT ras.stageId) as done_count']);
        $this->db->from('tbl_recruitment_applicant_stage as ras');
        $this->db->where('applicant_id', $applicantId);
        $this->db->where('application_id', $application_id);
        $this->db->where('archive', 0);
        $this->db->where_in('status', [3, 4]);
        $query = $this->db->get();
        $x = $query->row();

        if (!empty($x)) {
            $percent = (int) (($x->done_count / $stage_count) * 100);
        } else {
            $percent = 0;
        }

        return $percent;
    }

    function get_nex_stage_recruitment($current_stage, $applicant_id, $application_id, $extraParms = []) {
        $checkPreviousStage = $extraParms['previous_stage'] ?? false;
        $conditionSign = $checkPreviousStage ? '<' : '>';
        $conditionOrderByDirection = $checkPreviousStage ? 'DESC' : 'ASC';
        $stage_row = $this->basic_model->get_row('recruitment_stage', ['stage_order'], ['id' => $current_stage, 'archive' => 0]);
        $stage_order = '';        
        $applicant_id = $this->db->escape_str($applicant_id, true);        

        if (!empty($stage_row)) {
            $stage_order = $stage_row->stage_order;
        }

        $this->db->select(['rs.id', 'rs.stage', "rs.stage_key"]);
        $this->db->from('tbl_recruitment_stage as rs');

        $this->db->join('tbl_recruitment_stage_label as rsl', 'rsl.id = rs.stage_label_id AND rsl.archive = 0', 'inner');
        $this->db->join('tbl_recruitment_job_stage as rjs', 'rjs.stage_id = rsl.id AND rjs.archive = 0', 'inner');

        if (!empty($application_id)) {
            $application_id = $this->db->escape_str($application_id, true);
            $this->db->join('tbl_recruitment_applicant_applied_application raaa', 'raaa.jobId = rjs.jobId AND raaa.id =' . $application_id, 'inner');
        }
        // @deprecated
        else {
            $this->db->join('tbl_recruitment_applicant as ra', 'ra.jobId = rjs.jobId AND ra.id =' . $applicant_id, 'inner');
        }

        $this->db->where('rs.archive', 0);
        if ($stage_order != '') {
            $this->db->where('stage_order ' . $conditionSign, $stage_order);
        } else {
            $this->db->where('id ' . $conditionSign, $current_stage);
        }
        $this->db->order_by('stage_order', $conditionOrderByDirection);
        $query = $this->db->get();
        #last_query();
        $x = $query->row();
        return $x;
    }

    // TODO: Misleading name, should be named as update_application_stage_status
    function update_applicant_stage_status($reqData, $adminId) {
        // Note: Any vars in all UPPERCASE letters are for tbl_recruitment_applicant_applied_application.status

        $stage_status = (in_array($reqData->status, [2, 3])) ? $reqData->status : 4;
        $stage_wise_details_call = false;
        if ($stage_status == 2) {
            if (!check_its_recruiter_admin($adminId)) {
                return ['status' => false, 'error' => 'This action not allowed.'];
            }
        }

        // update current stage status according to status
        $stage_data = array('status' => $stage_status, 'action_by' => $adminId, 'action_at' => DATE_TIME);
        $stage_where = array('application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id, 'stageId' => $reqData->stageId, 'archive' => 0);
        $this->basic_model->update_records('recruitment_applicant_stage', $stage_data, $stage_where);

        if ($stage_status == 4) {
            // mark as rejected applicant
            // @deprecated. Let's reject the application, not the applicant,
            // or else you're may reject any future applications of this applicant
            $app_where = array('id' => $reqData->applicant_id);
            // $this->basic_model->update_records('recruitment_applicant', ['status' => 2, "rejected_date" => DATE_TIME], $app_where);

            $app_where = array('id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id);

            $STATUS_REJECTED = 2;
            $this->basic_model->update_records('recruitment_applicant_applied_application', ['status' => $STATUS_REJECTED, "rejected_date" => DATE_TIME], $app_where);
            $this->basic_model->update_records('recruitment_applicant_stage_attachment', ['member_move_archive' => 2, 'archive' => 0], ['application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id]);

            // Allow only if cab day
            $stage_check = [9, 10, 11, 12];
            if (isset( $reqData->stageId) && in_array($reqData->stageId, $stage_check)) {
                // get next stage id
                $next_stage_details = $this->get_nex_stage_recruitment($reqData->stageId, $reqData->applicant_id, $reqData->application_id);
                if (empty($next_stage_details)) {
                    #empty means current stage is last stage and need to update applicant last stage in applicant table,done at the end of function
                } else {
                    # updating in the applicant application table the current status of stage
                    $stage_where = array('applicant_id' => $reqData->applicant_id, 'id' => $reqData->application_id);
                    $this->basic_model->update_records('recruitment_applicant_applied_application', ['current_stage' => $next_stage_details->id], $stage_where);

                    // check stage data already exist
                    $stage_res = $this->basic_model->get_row('recruitment_applicant_stage', ['id'], ['application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id, 'archive' => 0, 'stageId' => $next_stage_details->id]);

                    $next_stage_status = 2;

                    if (empty($stage_res)) {
                        // create next stage data and mark as pending
                        $next_data = array('application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id, 'status' => $next_stage_status, 'created' => DATE_TIME, 'archive' => 0, 'stageId' => $next_stage_details->id);
                        if ($next_stage_status === 3) {
                            $next_data["action_by"] = $adminId;
                            $next_data["action_at"] = DATE_TIME;
                        }
                        $this->basic_model->insert_records('recruitment_applicant_stage', $next_data, false);
                    } else {
                        $stage_data = array('status' => $next_stage_status);
                        $stage_where = array('application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id, 'stageId' => $next_stage_details->id, "archive" => 0);
                        $this->basic_model->update_records('recruitment_applicant_stage', $stage_data, $stage_where);
                    }
                }
            }
        } elseif ($stage_status == 2) {
            $app_where = array('id' => $reqData->applicant_id, 'status !=' => 3);
            $next_stage_details = $this->get_nex_stage_recruitment($reqData->stageId, $reqData->applicant_id, $reqData->application_id);
            $applcantDataUpdate = ['current_stage' => $reqData->stageId, 'status' => 1];
            $lastStageNotArchive = false;
            if (!empty($next_stage_details) && $next_stage_details->stage_key == 'recruitment_complete') {
                $applicantRes = $this->basic_model->get_row('recruitment_applicant', ['id', 'status'], ['id' => $reqData->applicant_id, 'archive' => 0]);
                if (!empty($applicantRes) && $applicantRes->status == 3) {
                    $app_where = array('id' => $reqData->applicant_id, 'status' => 3);
                    $applcantDataUpdate['hired_as'] = 0;
                    $applcantDataUpdate['hired_date'] = '0000-00-00 00:00:00';
                }
            } else if (empty($next_stage_details) && $reqData->stage_key == 'recruitment_complete') {
                $lastStageNotArchive = true;
                $applicantRes = $this->basic_model->get_row('recruitment_applicant', ['id', 'status'], ['id' => $reqData->applicant_id, 'archive' => 0]);
                if (!empty($applicantRes) && $applicantRes->status == 2) {
                    $applcantDataUpdate['rejected_date'] = '0000-00-00 00:00:00';
                }
                $next_stage_details = (object) ['id' => $reqData->stageId, 'stage_key' => 'recruitment_complete'];
            }
            //pr([$next_stage_details,$reqData,$applcantDataUpdate]);
            // check stage data already exist
            $stage_res = $this->basic_model->get_row('recruitment_applicant_stage', ['id', 'status'], ['application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id, 'archive' => 0, 'stageId' => $next_stage_details->id]);

            ##TODO update in the application table
            $this->basic_model->update_records('recruitment_applicant', $applcantDataUpdate, $app_where);

            ## update current stage in application table
            $stage_where = array('id' => $reqData->application_id);
            $this->basic_model->update_records('recruitment_applicant_applied_application', ['current_stage' => $reqData->stageId, "status" => 1], $stage_where);

            if (!empty($stage_res) && ($next_stage_details->stage_key != 'document_checklist' && !$lastStageNotArchive)) {
                $this->basic_model->update_records('recruitment_applicant_stage', ['archive' => 1], ['stageId' => $next_stage_details->id, 'application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id]);
            }
            $taskApllicantArchiveData = ['group_schedule_interview' => 'group_interview', 'schedule_cab_day' => 'cab_day', 'schedule_individual_interview' => 'individual_interview'];
            if (array_key_exists($reqData->stage_key, $taskApllicantArchiveData)) {
                $stage_wise_details_call = true;
                $taskApplicantData = $this->get_last_task_applicant_id_by_stage_key_and_applicant_id($reqData->applicant_id, ['task_stage_key' => $taskApllicantArchiveData[$reqData->stage_key]]);
                if (!empty($taskApplicantData) && !empty($taskApplicantData['task_applicant_id'])) {
                    $this->basic_model->update_records('recruitment_task_applicant', ['archive' => 1], ['id' => $taskApplicantData['task_applicant_id']]);
                }
            }
        } else {
            // get next stage id
            $next_stage_details = $this->get_nex_stage_recruitment($reqData->stageId, $reqData->applicant_id, $reqData->application_id);
            if (empty($next_stage_details)) {
                #empty means current stage is last stage and need to update applicant last stage in applicant table,done at the end of function
            } else {
                # updating in the applicant application table the current status of stage
                $stage_where = array('applicant_id' => $reqData->applicant_id, 'id' => $reqData->application_id);
                $this->basic_model->update_records('recruitment_applicant_applied_application', ['current_stage' => $next_stage_details->id], $stage_where);

                // check stage data already exist
                $stage_res = $this->basic_model->get_row('recruitment_applicant_stage', ['id'], ['application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id, 'archive' => 0, 'stageId' => $next_stage_details->id]);

                $next_stage_status = 2;

                if (empty($stage_res)) {
                    // create next stage data and mark as pending
                    $next_data = array('application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id, 'status' => $next_stage_status, 'created' => DATE_TIME, 'archive' => 0, 'stageId' => $next_stage_details->id);
                    if ($next_stage_status === 3) {
                        $next_data["action_by"] = $adminId;
                        $next_data["action_at"] = DATE_TIME;
                    }
                    $this->basic_model->insert_records('recruitment_applicant_stage', $next_data, false);
                } else {
                    $stage_data = array('status' => $next_stage_status);
                    $stage_where = array('application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id, 'stageId' => $next_stage_details->id, "archive" => 0);
                    $this->basic_model->update_records('recruitment_applicant_stage', $stage_data, $stage_where);
                }
            }
        }

        if(!empty($reqData->selected_template)){
            $this->send_rejection_email_template_to_email($reqData->selected_template, $reqData->applicant_id, $adminId, $reqData->application_id);
            $this->create_log_on_final_stage($reqData, $adminId,1);
        }

        #This condition will call when admin create member from applicant
        if($reqData->stage_key == 'recruitment_complete' && $stage_status == 3){

            $typeDataCurrentStage = ['recruitment_complete'];
            $interViewCurrentRes = $this->get_stageid_by_key_bypass($typeDataCurrentStage);
            if (!empty($interViewCurrentRes)) {
                $last_stage_id = $interViewCurrentRes[0];
            } else {
                $last_stage_id = 14;
            }

            $hired_as = 2;
            $this->basic_model->update_records('recruitment_applicant', ['status' => '3'], ['id' => $reqData->applicant_id]);

            // update application when finalized
            $STATUS_COMPLETED = 3;
            $this->basic_model->update_records('recruitment_applicant_applied_application', ['updated' => DATE_TIME, 'status' => $STATUS_COMPLETED], ['id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id]
            );

            # send recruitment completition (welcome to Oncall) email to applicant
            $this->send_welcome_mail_to_applicant($reqData->applicant_id, $reqData->application_id, $adminId);

            if($reqData->is_create_member){
                require_once APPPATH . 'Classes/recruitment/ApplicantMoveToHCM.php';
                $applicantMoveObj = new ApplicantMoveToHCM();
                $applicantMoveObj->setUser_type('external_staff');
                $applicantMoveObj->setAdmin_User_type('0');
                $applicantMoveObj->setApplicant_id($reqData->applicant_id);

                if (isset($reqData->application_id) && !empty($reqData->application_id)) {
                    $applicantMoveObj->setApplicationId((int) $reqData->application_id);
                }

                $responseData = $applicantMoveObj->move_applicant();
                $hired_as = 1;
                $this->create_log_on_final_stage($reqData, $adminId,2);
            }
            $this->basic_model->update_records('recruitment_applicant', ['current_stage' => $last_stage_id, 'hired_as' => $hired_as, 'hired_date' => DATE_TIME], ['id' => $reqData->applicant_id]);
        }
        return ['status' => true, 'stage_wise_details_call' => $stage_wise_details_call ?? false];
    }

    /**
     * sending a welcome to HCM/Oncall email to recent applicant whose recruitment process got completed
     */
    function send_welcome_mail_to_applicant($applicant_id, $application_id, $adminId)
    {
        $this->load->model("Recruitment_task_action");

        # grabbing applicant details
        $applicant_details = $this->Recruitment_task_action->get_applicant_name_appid_email([$applicant_id]);
        $applicant = $applicant_details[$applicant_id];

        # grabbing job title
        $applicant["job_title"] = $this->Recruitment_task_action->get_application_job_title($application_id);

        # grabbing admin user details
        $admin_d = $this->Recruitment_task_action->get_admin_firstname_lastname($adminId);

        $applicant['admin_firstname'] = $admin_d['firstname'] ?? '';
        $applicant['admin_lastname'] = $admin_d['lastname'] ?? '';

        require_once APPPATH . 'Classes/Automatic_email.php';
        $obj = new Automatic_email();

        $obj->setEmail_key("hired_successfully_applicant");
        $obj->setEmail($applicant['email']);
        $obj->setDynamic_data($applicant);
        $obj->setUserId($applicant_id);
        $obj->setUser_type(1);

        $obj->automatic_email_send_to_user();
    }

    // send rejction email template to applicant
    function send_rejection_email_template_to_email($templateId, $applicantId, $adminId, $application_id){
        $this->load->model("recruitment/Recruitment_task_action");
        require_once APPPATH . 'Classes/Automatic_email.php';
        $obj = new Automatic_email();

        // get email template content
        #$email_template = $this->basic_model->get_row("email_templates", ["content", "name","from","subject"], ["id" => $templateId]);

        $this->load->model("Recruitment_task_action");
        // get applicant details
        $applicant_details = $this->Recruitment_task_action->get_applicant_name_appid_email([$applicantId]);

        if(!empty($applicant_details)){
            $applicant = $applicant_details[$applicantId];

            $applicant["job_title"] = $this->Recruitment_task_action->get_application_job_title($application_id);
            $admin_d = $this->Recruitment_task_action->get_admin_firstname_lastname($adminId);

            $applicant['admin_firstname'] = $admin_d['firstname']  ?? '';
            $applicant['admin_lastname'] = $admin_d['lastname'] ?? '';

            $obj->setTemplateId($templateId);
            $obj->setEmail($applicant['email']);
            $obj->setDynamic_data($applicant);
            $obj->setUserId($applicantId);
            $obj->setUser_type(1);

            $obj->automatic_email_send_to_user_by_template_id();
        }
    }

    function get_applicant_phone_interview_classification($applicant_id, $application_id = 0) {
        $res = $this->basic_model->get_row('recruitment_applicant_phone_interview_classification', ['id', 'classfication'], ['applicant_id' => $applicant_id, 'application_id' => $application_id]);
        if (!empty($res)) {
            return $res->classfication;
        } else {
            return false;
        }
    }

    function get_applicant_group_or_cab_interview_details($applicant_id, $application_id, $task_stage) {
        $application_id = $this->db->escape_str($application_id, true);
        $applicant_id = $this->db->escape_str($applicant_id, true);
        $this->db->select(["rt.start_datetime", "DATE_FORMAT(rt.end_datetime,'%h:%i %p') as end_time", "DATE_FORMAT(rt.start_datetime,'%h:%i %p') as start_time", "rl.name as training_location", "rt.status as task_status", "rta.email_status", "rta.invitation_accepted_at", "rta.invitation_send_at", "rta.invitation_cancel_at", "rta.status as applicant_email_status", "rt.id as taskId", "inter_det.applicant_status as applicant_result", "inter_det.quiz_status", "rta.id as task_applicant_id",
            "inter_det.mark_as_no_show", "inter_det.marked_date", "inter_det.app_orientation_status", "inter_det.app_login_status"]);

        $this->db->select("(select concat_ws(' ',m.firstname, lastname) from tbl_recruitment_task_recruiter_assign as rtra INNER JOIN tbl_member as m on m.id = rtra.recruiterId where rtra.taskId = rt.id AND rtra.archive = 0 and primary_recruiter = 1 Limit 1)
            as recruiter_in_charge", false);

        $this->db->select('CASE
            when rta.status = 2 THEN rta.invitation_cancel_at
            when inter_det.mark_as_no_show = 1 THEN inter_det.marked_date
            else rta.created
            END as manual_order', false);

        $this->db->select("CASE
            when rta.status =2 and rta.is_decline_mark_by_recruiter_user>0 THEN '1'
            else '0'
            END as is_status_decline_by_recruiter", false);

        $this->db->select(" CASE WHEN rta.status =2 and rta.is_decline_mark_by_recruiter_user>0 THEN (select concat_ws(' ',m.firstname, lastname) from tbl_member as m where m.id = rta.is_decline_mark_by_recruiter_user Limit 1)  ELSE '' END
            as status_decline_as_recruiter", false);

        $this->db->from("tbl_recruitment_task as rt");
        $this->db->join('tbl_recruitment_task_applicant as rta', 'rt.id = rta.taskId AND rta.archive = 0 and applicant_id = ' . $applicant_id.' and application_id = ' . $application_id, 'inner');
        $this->db->join('tbl_recruitment_location as rl', 'rl.id = rt.training_location', 'inner');

        $this->db->join('tbl_recruitment_applicant_group_or_cab_interview_detail as inter_det', 'inter_det.recruitment_task_applicant_id = rta.id ', 'INNER');
        $this->db->order_by('manual_order', 'desc');
        $this->db->where_in('rt.status', [1, 2]);
        $this->db->where("rt.task_stage", $task_stage);

        $query = $this->db->get();
        #last_query();
        $result = $query->result();

        $current_interview = [];
        $history_interview = [];
        if (!empty($result)) {
            foreach ($result as $val) {
                $val->invitation_send_at = ($val->invitation_send_at == '0000-00-00 00:00:00') ? '' : $val->invitation_send_at;
                $val->invitation_accepted_at = ($val->invitation_accepted_at == '0000-00-00 00:00:00') ? '' : $val->invitation_accepted_at;

                if ($val->applicant_email_status == 2 || $val->mark_as_no_show == 1 || !empty($current_interview)) {
                    $history_interview[] = $val;
                } else {
                    $current_interview = $val;
                }
            }

            if (empty($current_interview) && !empty($history_interview)) {
                $history_interview = obj_to_arr($history_interview);
                $cnt = count($history_interview);
                $current_interview = $history_interview[$cnt - 1];
                unset($history_interview[$cnt - 1]);
            }

            if ($task_stage == 6 && !empty($current_interview)) {
                $current_interview = obj_to_arr($current_interview);
                $x = $this->get_applicant_contract_details($current_interview['task_applicant_id']);
                $current_interview = array_merge($x, $current_interview);
            }
        }

        return ['history_interview' => $history_interview, 'current_interview' => $current_interview];
    }

    function get_applicant_individual_interview_details($applicant_id, $application_id, $stage_label_id) {
        $application_id = $this->db->escape_str($application_id, true);
        $applicant_id = $this->db->escape_str($applicant_id, true);
        $this->db->select(["rt.start_datetime", "DATE_FORMAT(rt.end_datetime,'%h:%i %p') as end_time", "DATE_FORMAT(rt.start_datetime,'%h:%i %p') as start_time", "rl.name as training_location", "rt.status as task_status", "rta.email_status", "rta.invitation_accepted_at", "rta.invitation_send_at", "rta.invitation_cancel_at", "rta.status as applicant_email_status", "rt.id as taskId", "rta.id as task_applicant_id", "rta.stage_label_id"]);



        $this->db->select("(select concat_ws(' ',m.firstname, lastname) from tbl_recruitment_task_recruiter_assign as rtra INNER JOIN tbl_member as m on m.id = rtra.recruiterId where rtra.taskId = rt.id AND rtra.archive = 0 and primary_recruiter = 1 Limit 1)
        as recruiter_in_charge", false);

        $this->db->select('CASE
        when rta.status = 2 THEN rta.invitation_cancel_at
        else rta.created
        END as manual_order', false);

        $this->db->select("CASE
        when rta.status =2 and rta.is_decline_mark_by_recruiter_user>0 THEN '1'
        else '0'
        END as is_status_decline_by_recruiter", false);

        $this->db->select(" CASE WHEN rta.status =2 and rta.is_decline_mark_by_recruiter_user>0 THEN (select concat_ws(' ',m.firstname, lastname) from tbl_member as m where m.id = rta.is_decline_mark_by_recruiter_user Limit 1)  ELSE '' END
        as status_decline_as_recruiter", false);

        $this->db->from("tbl_recruitment_task as rt");
        $this->db->join('tbl_recruitment_task_applicant as rta', 'rt.id = rta.taskId AND rta.archive = 0 and applicant_id = ' . $applicant_id.' and application_id = ' . $application_id, 'inner');
        $this->db->join('tbl_recruitment_location as rl', 'rl.id = rt.training_location', 'inner');
        $this->db->join('tbl_recruitment_task_stage as rts', 'rts.stage_label_id = rta.stage_label_id', 'inner');

        $this->db->order_by('manual_order', 'desc');
        $this->db->where_in('rt.status', [1, 2]);
        $this->db->where("rts.key", 'individual_interview');

        $query = $this->db->get();
        #last_query();
        $result = $query->result();

        $current_interview = [];
        $history_interview = [];
        $stage_label_id_ary = [];
        if (!empty($result)) {
            foreach ($result as $val) {

                $val->invitation_send_at = ($val->invitation_send_at == '0000-00-00 00:00:00') ? '' : $val->invitation_send_at;
                $val->invitation_accepted_at = ($val->invitation_accepted_at == '0000-00-00 00:00:00') ? '' : $val->invitation_accepted_at;

                if ($val->applicant_email_status == 2 || !empty($current_interview)) {
                    $history_interview[$val->stage_label_id][] = $val;
                } else {
                    $current_interview[$val->stage_label_id] = $val;
                }
                $stage_label_id_ary[] = $val->stage_label_id;
            }

            $stage_label_id_ary = array_unique($stage_label_id_ary);

            if (!empty($stage_label_id_ary)) {
                foreach ($stage_label_id_ary as $id) {
                    if (empty($current_interview[$id]) && !empty($history_interview[$id])) {
                        $history_interview[$id] = obj_to_arr($history_interview[$id]);
                        $cnt = count($history_interview[$id]);
                        $current_interview[$id] = $history_interview[$id][$cnt - 1];
                        unset($history_interview[$id][$cnt - 1]);
                    }
                }
            }
        }

        return ['history_interview' => $history_interview, 'current_interview' => $current_interview];
    }

    function get_applicant_contract_details($task_applicant_id) {
       /* $this->db->select(['signed_status', 'send_date', 'signed_date', 'id as contractId']);
        $this->db->from('tbl_recruitment_applicant_contract');
        $this->db->where(['archive' => 0, 'task_applicant_id' => $task_applicant_id]);*/

        $this->db->select(['tdap.signed_status', 'tdap.created_at as send_date', 'tdap.signed_date', 'tda.id as contractId']);
        $this->db->from('tbl_document_attachment as tda');
        $this->db->join('tbl_document_attachment_property as tdap', 'tdap.doc_id = tda.id', 'inner');
        $this->db->where(['tda.archive' => 0, 'tda.task_applicant_id' => $task_applicant_id]);

        $query = $this->db->get();
        $x = $query->row();

        if (!empty($x)) {
            $x->signed_date = ($x->signed_date == '0000-00-00 00:00:00') ? '' : $x->signed_date;
        } else {
            $x = [];
        }

        return (array) $x;
    }

    function update_applied_application_of_application($reqData) {
        if (!empty($reqData->applications)) {
            foreach ($reqData->applications as $val) {
                if (!empty($val->remove)) {
                    $this->basic_model->update_records('recruitment_applicant_applied_application', ['archive' => 1], ['id' => $val->id, 'applicant_id' => $reqData->applicant_id]);
                }

                if (!empty($val->id) && $val->id > 0) {
                    $update_app = array(
                        'position_applied' => $val->position_applied_id,
                        'recruitment_area' => $val->recruitment_area_id,
                        'employement_type' => $val->employement_type_id,
                        'channelId' => $val->channel_id,
                    );

                    if ($val->channel_id == 3) {
                        $update_app['referrer_url'] = getenv('OCS_ADMIN_URL');
                    }

                    $this->basic_model->update_records('recruitment_applicant_applied_application', $update_app, ['id' => $val->id, 'applicant_id' => $reqData->applicant_id]);
                } else {
                    $applications[] = array(
                        'applicant_id' => $reqData->applicant_id,
                        'position_applied' => $val->position_applied_id,
                        'recruitment_area' => $val->recruitment_area_id,
                        'employement_type' => $val->employement_type_id,
                        'channelId' => $val->channel_id,
                        'created' => DATE_TIME,
                        'archive' => 0,
                        'from_applicant_id' => 0,
                        'from_status' => 0,
                    );
                    if ($val->channel_id == 3) {
                        $update_app['referrer_url'] = getenv('OCS_ADMIN_URL');
                    }
                }
            }

            if (!empty($applications)) {
                $this->basic_model->insert_records('recruitment_applicant_applied_application', $applications, $multiple = true);
            }

            return true;
        }
    }

    function get_applicant_last_update($userId) {
        $this->db->select(['lg.specific_title', 'lg.created']);
        $this->db->from('tbl_logs as lg');
        $this->db->join('tbl_module_title as mt', 'mt.id = lg.sub_module AND mt.key_name = "recruitment_applicant"', 'inner');
        $this->db->order_by('lg.created', 'desc');
        $this->db->where('lg.created_type', 1);
        $this->db->where('lg.userId', $userId);


        $query = $this->db->get();
        $x = $query->row();
        if (!empty($x))
            return $x;
        else
            return [];
    }

    function get_applicant_logs($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $available_column = ['specific_title', 'created', "recruiter"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'lg.created';
            $direction = 'desc';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        $column = ['lg.specific_title', 'lg.created', "concat(firstname,' ',lastname) as recruiter"];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $column)), false);
        $this->db->from('tbl_logs as lg');
        $this->db->join('tbl_module_title as mt', 'mt.id = lg.sub_module AND mt.key_name = "recruitment_applicant"', 'inner');
        $this->db->join('tbl_member as m', 'm.id = lg.created_by', 'inner');
        $this->db->order_by('lg.created', 'desc');
        $this->db->where('lg.created_type', 1);
        $this->db->where('lg.userId', $reqData->applicant_id);

        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();

        $return = array('count' => $dt_filtered_total, 'data' => $result);
        return $return;
    }

    function request_for_pay_scal_approval($reqData, $adminId) {

        $pay_scal = array(
            'applicant_id' => $reqData->applicant_id,
            'requested_by' => $adminId,
            'approved_by' => '',
            'status' => 0, // set pending status
            'archived' => 0,
            'requested_at' => DATE_TIME,
            'relevant_notes' => '',
        );

        $pay_scal_id = $this->basic_model->insert_records('recruitment_applicant_pay_point_approval', $pay_scal, $multiple = FALSE);

        if ($reqData->pay_scale_approval) {
            $pay_scale_appoval_data = [];
            foreach ($reqData->pay_scale_approval as $val) {
                $val = array_filter((array) $val);
                if (empty($val)) {
                    continue;
                } else {
                    $val = (object) $val;
                }
                $pay_scale_appoval_data[] = [
                    'work_area' => $val->work_area,
                    'pay_point' => $val->pay_point,
                    'pay_level' => $val->pay_level,
                    'applicant_id' => $reqData->applicant_id,
                    'pay_point_approval_id' => $pay_scal_id,
                    'is_approved' => 0
                ];
            }

            if (!empty($pay_scale_appoval_data)) {
                $this->basic_model->insert_records('recruitment_applicant_pay_point_options', $pay_scale_appoval_data, true);
                $this->basic_model->insert_records('recruitment_applicant_pay_point_options_before_approval', $pay_scale_appoval_data, true);
            }
        }

        return true;
    }

    function get_applicant_pay_scale_approval_data($applicant_id) {
        $this->db->select(['ppa.id as pay_point_approval_id', 'ppa.status as pay_scale_approval_status', 'ppa.requested_at', 'ppa.approved_at']);
        $this->db->from('tbl_recruitment_applicant_pay_point_approval as ppa');
        $this->db->where('ppa.archived', 0);
        $this->db->where('ppa.applicant_id', $applicant_id);

        $query = $this->db->get();
        $pay_scale_data = $query->row();

        $pay_scale_approval = [];
        if (!empty($pay_scale_data)) {
            if ($pay_scale_data->pay_scale_approval_status == 1) {
                $pay_scale_approval = $this->basic_model->get_record_where('recruitment_applicant_pay_point_options', ['work_area', 'pay_point', 'pay_level'], ['pay_point_approval_id' => $pay_scale_data->pay_point_approval_id]);
            } else {
                $pay_scale_approval = $this->basic_model->get_record_where('recruitment_applicant_pay_point_options_before_approval', ['work_area', 'pay_point', 'pay_level'], ['pay_point_approval_id' => $pay_scale_data->pay_point_approval_id]);
            }
        } else {
            $pay_scale_data = [];
        }

        return ['pay_scale_details' => $pay_scale_data, 'pay_scale_approval' => $pay_scale_approval];
    }

    function update_applicant_reference_status_note($reqData) {
        $update_data = array(
            'relevant_note' => !empty($reqData->relevant_note) ? $reqData->relevant_note : '',
            'status' => ($reqData->status == 1) ? 1 : 2,
            'written_reference' => ($reqData->written_reference == 1) ? 1 : 0
        );



        $where = array('applicant_id' => $reqData->applicant_id, 'id' => $reqData->reference_id);

        $this->basic_model->update_records('recruitment_applicant_reference', $update_data, $where);

        return true;
    }

    function update_applicant_details($reqData) {
        // check any duplicant applicant exist with acceprt status
        $duplicated_response = $this->check_any_duplicate_applicant($reqData->id, 2);
        $applicantIds = [];

        if (!empty($duplicated_response)) {
            $duplicated_response = obj_to_arr($duplicated_response);

            $applicantIds = array_column($duplicated_response, 'id');
        }

        $applicantIds[] = $reqData->id;
        $applicant_uuid = $reqData->uuid;

        // update applicant details
        $this->update_applicant_info($reqData, $applicantIds);

        $this->update_applicant_emails($reqData, $applicantIds,$applicant_uuid);

        $this->update_applicant_address($reqData, $reqData->id);

        $this->update_applicant_references($reqData, $reqData->id);

        $this->update_applicant_phone_number($reqData, $applicantIds);

        //dispatch event
        // $this->load->library('EventDispatcher');
        // $this->eventdispatcher->dispatch('onAfterApplicantUpdated', $reqData->id, $reqData);

        return true;
    }

    function update_applicant_info($reqData, $applicantIds) {        
        // update applicant details
        $applicant_det = [
            'firstname' => $reqData->firstname,
            'lastname' => $reqData->lastname,
            'previous_name'=>$reqData->previous_name,
            'middlename'=>$reqData->middlename,
                // 'dob' => isset($reqData->dob) && !empty($reqData->dob) && trim(strtolower($reqData->dob)) !== 'invalid date' ? $reqData->dob : '0000-00-00',
        ];

        $this->db->where_in('id', $applicantIds);
        $this->db->where('archive', 0);
        $this->db->update(TBL_PREFIX . 'recruitment_applicant', $applicant_det);

        // also update details in tbl_person
        $found_applicants = $this->db
                ->from('tbl_recruitment_applicant')
                ->where_in('id', $applicantIds)
                ->select()
                ->get()
                ->result_array();

        foreach ($found_applicants as $applicant) {
            $cond = ['id' => $applicant['person_id']];
            $set = [
                'firstname' => $applicant['firstname'],
                'lastname' => $applicant['lastname'],
                'middlename'=>$applicant['middlename'],
                'title' => $reqData->title,
                'previous_name' => isset($reqData->previous_name) && !empty($reqData->previous_name) ? $reqData->previous_name : null,
                'preferred_name' => isset($reqData->preferred_name) && !empty($reqData->preferred_name) ? $reqData->preferred_name : null,
                'date_of_birth' => isset($reqData->dob) && !empty($reqData->dob) && trim(strtolower($reqData->dob)) !== 'invalid date' ? $reqData->dob : null,
                'profile_pic' => $reqData->avatar
            ];
            $this->db->update('tbl_person', $set, $cond);
        }
    }

    function update_applicant_emails($reqData, $applicantIds,$applicant_uuid) {
        if (!empty($reqData->emails)) {
            foreach ($reqData->emails as $val) {
                $this->db->where_in('applicant_id', $applicantIds);
//                $this->db->where('id', $val->id);
                $this->db->update(TBL_PREFIX . 'recruitment_applicant_email', ['email' => $val->email]);
            }

            $applicants = $this->db->from('tbl_recruitment_applicant')
                    ->where_in('id', $applicantIds)
                    ->select()
                    ->get()
                    ->result_array();

            $person_ids = array_values(array_column($applicants, 'person_id'));
            $this->update_person_primary_emails($person_ids, $val->email,$applicant_uuid);
        }
    }

    public function change_owner(array $applicants, $owner_id,$adminId) {
        foreach ($applicants as $app) {
            $set = ['recruiter' => $owner_id];
            $cond = ['id'=> $app->application_id, 'applicant_id' => $app->applicant_id,'jobId' => $app->jobId] ;
            $this->db->update('tbl_recruitment_applicant_applied_application', $set, $cond);
            $dataToBeUpdated = [
                'owner' =>$owner_id
            ];
           $this->updateHistory($app, $dataToBeUpdated, $adminId);
        }

    }

    public function update_person_primary_emails(array $person_ids, $email,$applicant_uuid='') {
        
        foreach ($person_ids as $person_id) {
            $set = ['email' => $email];
            $cond = ['person_id' => $person_id, 'primary_email' => 1];
            $this->db->update('tbl_person_email', $set, $cond);
        }

        // update username in users
        if(!empty($applicant_uuid)){
            $this->db->update('tbl_users', ['username' => $email], ['id' => $applicant_uuid]);
        }

        # also need to make sure we update the username field of the tbl_person table as primary email
        # is the username
        $this->db->update('tbl_person', ['username' => $email], ['id' => $person_id]);
    }

    function create_applicant_address($applicant_address) {
        $applicant_address['created'] = DATE_TIME;
        return $this->basic_model->insert_records('recruitment_applicant_address', $applicant_address);
    }

    function update_applicant_address($reqData, $applicantId, $unit_number='') {
        $id = 0;
        if(!$reqData->is_manual_address){
            if (!empty($reqData->address)) {
                $addr = devide_google_or_manual_address($reqData->address);
                $address = [
                    'unit_number' => !empty($reqData->unit_number) ? $reqData->unit_number : $unit_number,
                    'street' => $addr["street"] ?? '',
                    'city' => $addr["suburb"] ?? '',
                    'postal' => $addr["postcode"] ?? '',
                    'state' => $addr["state"] ?? '',
                    'primary_address' => 1,
                    'applicant_id' => $applicantId,
                    'is_manual_address' => $reqData->is_manual_address ?? 0,
                    'manual_address' => NULL
                ];

                //update person address if person id exists
                if(!empty($reqData->person_id) && !empty($applicantId)){
                    require_once APPPATH . 'Classes/person/person.php';
                    $objPerson = new PersonClass\Person();
    
                    $objPerson->setPersonId($reqData->person_id);
                    // update address for contact.
    
                    $change_addr = [];
                    if (!empty($addr)) {
                        $change_addr[] = [
                            'unit_number' => !empty($reqData->unit_number) ? $reqData->unit_number : $unit_number,
                            'street' => $addr["street"],
                            'state' => !empty($addr["state"]) ? $addr["state"] : null, 
                            'suburb' => $addr["suburb"] ?? '',
                            'postcode' => $addr["postcode"] ?? '',
                            'is_manual_address' => $reqData->is_manual_address ?? 0,
                            'manual_address' => $reqData->manual_address ?? NULL
                        ];
                        // create / delete of person (contact )
                        $objPerson->setPersonAddress($change_addr);
                        $objPerson->insertAddress();
                    }
                }          
    
    
            } else {

                $postcode = '';
                if (isset($reqData->postcode)) {
                    $postcode = $reqData->postcode;
                }
                if (isset($reqData->postal)) {
                    $postcode = $reqData->postal;
                }
    
                $city = '';
                if (isset($reqData->suburb)) {
                    $city = $reqData->suburb;
                }
                if (isset($reqData->city)) {
                    $city = $reqData->city;
                }
    
                $address = [
                    'unit_number' => !empty($reqData->unit_number) ? $reqData->unit_number : $unit_number,
                    'street' => $reqData->street ?? '',
                    'city' => $city ?? '',
                    'postal' => $postcode ?? '',
                    'state' => $reqData->state ?? '',
                    'primary_address' => 1,
                    'applicant_id' => $applicantId,
                    'is_manual_address' => $reqData->is_manual_address ?? 0,
                    'manual_address' => $reqData->manual_address ?? NULL
                ];
            }

            // TODO: refactor inconsistencies of address fields into 1 model (& db changes)
    
            $res = $this->exists_applicant_address($applicantId);
            if (!empty($res)) {
                $applicant_address['updated'] = DATE_TIME;
                $this->basic_model->update_records('recruitment_applicant_address', $address, ['id' => $res->id]);
                require_once APPPATH . 'Classes/person/person.php';
                $objPerson = new PersonClass\Person();

                $objPerson->setPersonId($reqData->person_id);
                // update address for contact.

                 $change_addr[] = [
                            'unit_number' => !empty($reqData->unit_number) ? $reqData->unit_number : $unit_number,
                            'street' => $reqData->street ?? '',
                            'state' => !empty($reqData->state) ? $reqData->state : null, 
                            'suburb' => $city ?? '',
                            'postcode' => $postcode ?? '',
                            'is_manual_address' => $reqData->is_manual_address ?? 0,
                            'manual_address' => $reqData->manual_address ?? NULL
                        ];
                        // create / delete of person (contact )
                        $objPerson->setPersonAddress($change_addr);
                        $objPerson->insertAddress();
                 $id = $res->id;
            } else {
                $id = $this->create_applicant_address($address);
            }
        }else{
            if(!empty($applicantId)){
                $manual_applicant_address['unit_number'] = !empty($reqData->unit_number) ? $reqData->unit_number : $unit_number;
                $manual_applicant_address['is_manual_address'] = $reqData->is_manual_address ?? 0;
                $manual_applicant_address['manual_address'] = $reqData->manual_address ?? NULL;

                $res = $this->exists_applicant_address($applicantId);
                if (!empty($res)) {
                    if(!empty($reqData->person_id)){
                        $this->basic_model->update_records('person_address', $manual_applicant_address, ['person_id' => $reqData->person_id, 'archive'=>0]);
                    }    

                    $manual_applicant_address['updated'] = DATE_TIME;
                    $this->basic_model->update_records('recruitment_applicant_address', $manual_applicant_address, ['applicant_id' => $applicantId]);
                    $id = $res->id;                   

                } else {
                    $manual_applicant_address['unit_number'] = !empty($reqData->unit_number) ? $reqData->unit_number : $unit_number;
                    $manual_applicant_address['applicant_id'] = $applicantId;
                    $manual_applicant_address['primary_address'] = 1;
                    $id = $this->create_applicant_address($manual_applicant_address);

                    if(!empty($reqData->person_id)){
                        $manual_person_address['unit_number'] = !empty($reqData->unit_number) ? $reqData->unit_number : $unit_number;
                        $manual_person_address['is_manual_address'] = $reqData->is_manual_address ?? 0;
                        $manual_person_address['manual_address'] = $reqData->manual_address ?? NULL;
                        $manual_person_address['person_id'] = $reqData->person_id;     
                        $manual_person_address['primary_address'] = 1;    
                        return $this->basic_model->insert_records('person_address', $manual_person_address);
                    }     
                }
                
            }
        }
        

        return $id;
    }

    function exists_applicant_address($applicantId) {
        return $this->basic_model->get_row('recruitment_applicant_address', ["id"], ['applicant_id' => $applicantId]);
    }

    function update_applicant_references($reqData, $applicantId) {
        if (!empty($reqData->references)) {
            $insert_ref = [];

            foreach ($reqData->references as $val) {
                $reference = ['name' => $val->name, 'email' => $val->email, 'phone' => $val->phone, 'updated' => DATE_TIME, 'applicant_id' => $applicantId];

                if (!empty(array_filter($reference))) {
                    if (!empty($val->its_delete)) {
                        $this->basic_model->update_records('recruitment_applicant_reference', ['archive' => 1], ['id' => $val->id, 'applicant_id' => $applicantId]);
                    } elseif (!empty($val->id)) {
                        $reference['applicant_id'] = $applicantId;
                        $reference['updated'] = DATE_TIME;
                        $this->basic_model->update_records('recruitment_applicant_reference', $reference, ['id' => $val->id, 'applicant_id' => $applicantId]);
                    } else {
                        $reference['applicant_id'] = $applicantId;
                        $reference['created'] = DATE_TIME;
                        $insert_ref[] = $reference;
                    }
                }
            }
        }

        if (!empty($insert_ref)) {
            $this->basic_model->insert_records('recruitment_applicant_reference', $insert_ref, true);
        }
    }

    function update_applicant_phone_number($reqData, $applicantIds) {
        if (!empty($reqData->phones)) {
            foreach ($reqData->phones as $val) {
                $this->db->where_in('applicant_id', $applicantIds);
//                $this->db->where('id', $val->id);
                $this->db->update(TBL_PREFIX . 'recruitment_applicant_phone', ['phone' => $val->phone]);
            }

            $applicants = $this->db->from('tbl_recruitment_applicant')
                    ->where_in('id', $applicantIds)
                    ->select()
                    ->get()
                    ->result_array();

            $person_ids = array_values(array_column($applicants, 'person_id'));
            $this->update_person_primary_phones($person_ids, $val->phone);
        }
    }

    public function update_person_primary_phones(array $person_ids, $phone) {
        foreach ($person_ids as $person_id) {
            $set = ['phone' => $phone];
            $cond = ['person_id' => $person_id, 'primary_phone' => 1];
            $this->db->update('tbl_person_phone', $set, $cond);
        }
    }

    public function get_all_attachments_by_applicant_id($applicantId = 0, $doc_cat_code = null, $application_id = null, $skip_archive = null) {
        $data = [];
        $applicantId = (int) $applicantId;

        require_once APPPATH . 'Classes/document/DocumentAttachment.php';

        $docAttachObj = new DocumentAttachment();
        // Get constant related_to
        $relatedTo = $docAttachObj->getConstant('RELATED_TO_RECRUITMENT');
        $draftStatus = $docAttachObj->getConstant('DOCUMENT_STATUS_DRAFT');

        $this->db->select([
            'tda.applicant_id',
            'tda.application_id',
            'tda.id',
            'tdap.file_name',
            'tdap.file_name AS attachment_title',
            'tdap.raw_name AS attachment',
            'tda.stage as stage_level',
            'tda.created_at',
            'tda.archive',
            'tda.document_status',
            'tda.doc_type_id AS doc_category',
            'tda.draft_contract_type',
            'tda.expiry_date',
            'tda.reference_number',
            'tda.issue_date',
            'tda.updated_at',
            'tdap.file_path',
            'tdap.file_size',
            'tdap.aws_object_uri',
            'tdap.aws_response',
            'tdap.aws_uploaded_flag',
            'tdap.aws_file_version_id',
            'docs.system_gen_flag',
            'tdap.signed_status',
            'tda.applicant_specific',
            'tda.license_type',
            'tda.issuing_state',
            'tda.vic_conversion_date',
            'tda.applicant_specific',
            'tda.visa_category',
            'tda.visa_category_type'
        ]);

        $this->db->select("(CASE
            WHEN tda.draft_contract_type = 1 THEN CONCAT('Draft Contract_', ra.firstname,' ', ra.lastname)
            WHEN tda.draft_contract_type = 2 THEN CONCAT('Employment Contract_', ra.firstname,' ', ra.lastname)
            else
                CASE
                    WHEN docs.title IS NOT NULL THEN
                        CONCAT(docs.title,'_',, ra.firstname,' ', ra.lastname)
                    ELSE
                        CONCAT(ra.firstname,' ', ra.lastname)
                    END
            end) as download_as
        ");

        $this->db->select("(CASE
            WHEN tda.draft_contract_type = 1 THEN 'Draft Contract'
            WHEN tda.draft_contract_type = 2 THEN 'Employment Contract'
            else docs.title end) as category_title
        ");

        $this->db->select([
            '(case when is_main_stage_label=0 then (select stage From tbl_recruitment_stage as rs where tda.stage=rs.id) else (select stage_number From tbl_recruitment_stage_label as rsl where tda.stage=rsl.id) END) as current_stage_level'
                ], false);
        $this->db->from("tbl_document_attachment as tda");
        $this->db->join("tbl_document_attachment_property as tdap", "tda.id = tdap.doc_id", "INNER");
        $this->db->join("tbl_recruitment_applicant as ra", "ra.id = tda.applicant_id", "INNER");
        $this->db->join("tbl_document_type as docs", "docs.id = tda.doc_type_id AND docs.archive = 0 ", "LEFT");
        $this->db->join("tbl_document_type_related as doc_rel", "doc_rel.doc_type_id = tda.doc_type_id AND doc_rel.related_to = ".REQUIRMENT_MODULE_ID, "LEFT");
        $this->db->join("tbl_document_category as doc_cat", "doc_cat.id = docs.doc_category_id AND doc_cat.key_name IN ('apply_job', 'recruitment_stage') ", "LEFT");

        $this->db->where('tda.document_status !=', $draftStatus);
        $this->db->where("tda.applicant_id", $applicantId);

        if(isset($application_id)) {
            $this->db->where("tda.application_id in ($application_id,0)");
        }
        if($skip_archive) {
            $this->db->where("tda.archive = 0");
        }
        $this->db->where("tda.related_to = ",$relatedTo);
        $this->db->order_by('tda.created_at', 'DESC');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result();
        }

        $collection = [];
        if (!empty($data)) {
            foreach ($data as $val) {
                if ($val->signed_status == 0 && $val->draft_contract_type == 2) {
                    continue;
                }
                //Assign the file name to s3 file path
                $filename = $val->file_path;

                if(!$val->aws_uploaded_flag) {
                    $filename = $val->attachment;
                }

                if ($val->draft_contract_type == 1) {
                    $folder_url = $val->aws_uploaded_flag == 0 ? GROUP_INTERVIEW_CONTRACT_PATH : APPLICANT_ATTACHMENT_UPLOAD_PATH;
                    $val->url = 'mediaShow/rg/' . $val->applicant_id . '/' . urlencode(base64_encode($filename));
                    $docPath = $folder_url . '/' . $filename;
                } else if ($val->draft_contract_type == 2) {
                    if ($val->aws_uploaded_flag == 0) {
                        $folder_url = CABDAY_INTERVIEW_CONTRACT_PATH;
                        $val->url = 'mediaShow/rc/' . $val->applicant_id . '/' . urlencode(base64_encode($val->applicant_id . '/' .$filename));
                    } else {
                        $folder_url = APPLICANT_ATTACHMENT_UPLOAD_PATH;
                        $val->url = 'mediaShow/rc/' . $val->applicant_id . '/' . urlencode(base64_encode($filename));
                    }
                    $docPath = $folder_url . $val->applicant_id . '/' . $filename;
                } else {
                    $val->url = 'mediaShow/r/' . $val->applicant_id . '/' . urlencode(base64_encode($filename));
                    $docPath = APPLICANT_ATTACHMENT_UPLOAD_PATH . $val->applicant_id . '/' . $filename;
                }


                $val->job = $this->find_job_by_stage_attachment(obj_to_arr($val));

                // Export with derived filename instead
                // of goiing into trouble of renaming the file during upload process
                //$download_as = $this->determine_filename_by_doc_category($val->attachment, $val->doc_category, $val->applicant_id);
                $download_name =  $val->download_as;
                $download_name = preg_replace('/\s+/', '_', $download_name);
                $extension = pathinfo($val->attachment, PATHINFO_EXTENSION);
                $val->url .= '?' . http_build_query(['download_as' => $download_name . '.' . $extension]);

                //IF file size already available then assign this value
                if($val->file_size) {
                    $val->attachment_size = $val->file_size;
                }else {
                    $docPath = $this->determine_attachment_filename_path($val->attachment, $val->applicant_id);
                    $val->docPath = $docPath;
                    $val->attachment_size = is_readable($docPath) ? filesize($docPath) : false; // if false, file does not exists or unreadable
                }


                $val->document_status_label = $this->determine_document_status_label($val->document_status);
                $collection[] = $val;
            }
        }

        return $collection;
    }

    public function get_attachment_details_by_ids($ids = [], $applicantId = 0) {
        $response = [];
        $idsData = !empty($ids) ? $ids : 0;
        $idsData = !empty($ids) && is_array($ids) ? $ids : [$idsData];
        $this->db->select(['attachment as filename', 'draft_contract_type']);
        $this->db->where_in('id', $idsData);
        $this->db->where('applicant_id', $applicantId);
        //$this->db->where('archive', 0);
        $query = $this->db->get('tbl_recruitment_applicant_stage_attachment');

        if ($query->num_rows() > 0) {
            $response = $query->result();
        }
        return $response;
    }

    public function archived_attachment_details_by_ids($ids = [], $applicantId = 0) {
        $response = [];
        $idsData = !empty($ids) ? $ids : 0;
        $idsData = !empty($ids) && is_array($ids) ? $ids : [$idsData];
        $this->db->where_in('id', $idsData);
        $this->db->where('applicant_id', $applicantId);
        $this->db->where('archive', 0);
        #$this->db->where('draft_contract_type', 0);
        $this->db->update('tbl_recruitment_applicant_stage_attachment', ['archive' => 1, 'archive_at' => DATE_TIME]);
        return $this->db->affected_rows();
    }

    public function check_any_duplicate_applicant($applicantId, $status) {
        $this->db->select('ra.id','ra.uuid');
        $this->db->from('tbl_recruitment_applicant as ra');
        $this->db->join('tbl_recruitment_applicant_duplicate_status as rads', 'rads.applicant_id = ra.id AND rads.archive = 0 AND rads.status = ' . $status, 'INNER');
        $this->db->where('ra.duplicatedId', $applicantId);
        $this->db->where('ra.duplicated_status', 1);

        $this->db->group_by('ra.id');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $query->result();
    }

    public function check_email_already_exist_to_another_applicant($applicantId, $email) {
        $this->db->select('ra.id');
        $this->db->from('tbl_recruitment_applicant as ra');
        $this->db->join('tbl_person_email pe', 'ra.person_id = pe.person_id', 'INNER');
        $this->db->where('ra.id !=', $applicantId);
        $this->db->where('pe.email', $email);
        $this->db->where('pe.primary_email', 1);
        $this->db->where('pe.archive', 0);
        $this->db->where('ra.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $query->result();
    }

    public function check_phone_already_exist_to_another_applicant($applicantId, $phone) {
        $phone = str_replace(' ', '', $phone);
        $phone = str_replace('+', '', $phone);
        $this->db->select('ra.id');
        $this->db->from('tbl_recruitment_applicant as ra');
        $this->db->join('tbl_person_phone pp', 'ra.person_id = pp.person_id', 'INNER');
        $this->db->where('ra.id !=', $applicantId);
        $this->db->where("REPLACE(REPLACE(pp.phone,' ',''), '+','') =", $phone);
        $this->db->where('pp.primary_phone', 1);
        $this->db->where('pp.archive', 0);
        $this->db->where('ra.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $res = $query->row();

        return $query->row();
    }

    public function get_applicant_mandatory_doucment_list($applicantId, $application_id, $is_required_only = false,$reqData) {
    
        $applicantId = $this->db->escape_str($applicantId, true);
        $application_id = $this->db->escape_str($application_id, true);
        $this->db->select(['docs.id as recruitment_doc_id', 'docs.title as title', 'rjpd.is_required', 'rjpd.requirement_docs_id']);
        $this->db->select("(select CONCAT(a.is_required,'|_@BREACKER@_|',a.is_approved,'|_@BREACKER@_|',id,'|_@BREACKER@_|',applicant_id) from tbl_recruitment_applicant_doc_category as a where archive = 0 AND a.recruitment_doc_id = docs.id and a.applicant_id = " . $applicantId . " and a.application_id = " . $application_id . ") as applicant_doc");

        $this->db->from('tbl_document_type as docs');

        $this->db->join('tbl_recruitment_job_posted_docs as rjpd', 'rjpd.requirement_docs_id = docs.id AND rjpd.archive = 0', 'inner');

        $this->db->join('tbl_document_category as docs_cat', 'docs.doc_category_id = docs_cat.id AND docs_cat.archive = 0', 'inner');

        $this->db->join('tbl_recruitment_applicant_applied_application as raaa', 'raaa.jobId = rjpd.jobId AND raaa.id = ' . $application_id . ' AND raaa.applicant_id = ' . $applicantId, 'inner');

        $this->db->where('docs.archive', 0);
        if ($is_required_only == true) {
            $this->db->where('rjpd.is_required', 1);
        }
        if($this->Common_model->check_is_bu_unit($reqData)) {
            $this->db->where('raaa.bu_id', $reqData->business_unit['bu_id']);
        }
        $this->db->where_in('docs_cat.key_name', ['apply_job', 'recruitment_stage']);
        # Throw exception if db error occur
        if (!$query = $this->db->get()) {               
            $db_error = $this->db->error();
            throw new Exception('Something went wrong!');
        }

        $res = $query->result();

        if (!empty($res)) {
            foreach ($res as $val) {

                // Get uploaded document
                $doc_type_id = $val->requirement_docs_id;
                $doc_type_id = $this->db->escape_str($doc_type_id, true);
                $ref_row = $this->db->query('SELECT id from tbl_document_attachment where doc_type_id = '.$doc_type_id.' AND applicant_id = '.$applicantId.' AND application_id = '.$application_id.' AND archive = 0')->row_array();
                $is_uploaded = false;

                if (isset($ref_row) == true && isset($ref_row['id']) == true) {
                    $is_uploaded = true;
                }

                if ($is_uploaded == false) {
                    $ref_row = $this->db->query('SELECT id from tbl_document_attachment where doc_type_id = '.$doc_type_id.' AND applicant_id = '.$applicantId.' AND application_id = 0 AND archive = 0')->row_array();
                    if (isset($ref_row) == true && isset($ref_row['id']) == true) {
                        $is_uploaded = true;
                    }
                }

                $val->is_uploaded = $is_uploaded;

                $val->applicant_doc;
                $x = explode('|_@BREACKER@_|', $val->applicant_doc);
                
                $val->is_approved = isset($x[1]) ? $x[1] : '';
                $val->applicant_doc_id = isset($x[2]) ? $x[2] : '';
                $val->assined = isset($x[3]) ? $x[3] : '';

                unset($val->applicant_doc);
            }
        }
        return $res;
    }

    public function update_mandatory_optional_docs_for_applicant($reqData) {
        $insert_data = [];

        if ($reqData->applicant_document_cat) {
            foreach ($reqData->applicant_document_cat as $val) {

                if (!empty($val->applicant_doc_id) && empty($val->assined)) {
                    $update_data = ['archive' => 1, 'updated' => DATE_TIME];

                    $this->basic_model->update_records('recruitment_applicant_doc_category', $update_data, ['id' => $val->applicant_doc_id]);
                } elseif (!empty($val->applicant_doc_id) && !empty($val->assined)) {

                    $update_data = ['is_required' => (($val->is_required === '1') ? 1 : 0), 'updated' => DATE_TIME];

                    $this->basic_model->update_records('recruitment_applicant_doc_category', $update_data, ['id' => $val->applicant_doc_id]);
                } elseif (!empty($val->assined)) {
                    $insert_data[] = [
                        'applicant_id' => $reqData->applicant_id,
                        'application_id' => $reqData->application_id,
                        'recruitment_doc_id' => $val->recruitment_doc_id,
                        'is_required' => $val->is_required,
                        'is_approved' => 0,
                        'created' => DATE_TIME,
                        'updated' => DATE_TIME,
                        'archive' => 0
                    ];
                }
            }

            if (!empty($insert_data)) {
                $this->basic_model->insert_records('recruitment_applicant_doc_category', $insert_data, True);
            }
        }


        return true;
    }

    public function save_application_stage_note($reqData) {
        $response = ['status' => false, 'error' => 'Something went wrong.'];
        $appId = isset($reqData->applicantId) ? $reqData->applicantId : '';
        $notes = isset($reqData->note) ? $reqData->note : '';

        $application_id = isset($reqData->application_id) && !empty($reqData->application_id) ? $reqData->application_id : 0;

        if (!empty($appId) && $appId > 0 && !empty($notes)) {

            $dataIns = [
                'applicant_id' => $appId,
                'application_id' => $application_id,
                'notes' => $notes,
                'created' => DATE_TIME,
                'stage' => $reqData->current_stage,
                'is_main_stage_label' => (int) $reqData->is_main_stage,
                'recruiterId' => (int) $reqData->recruiterId,
            ];
            $dd = $this->basic_model->insert_records('recruitment_applicant_stage_notes', $dataIns);

            if ($dd) {
                $response = ['status' => true];
            } else {
                $response = ['status' => false, 'error' => 'Something went wrong.'];
            }
        }
        return $response;
    }

     /**
     * Find notes
     */
    public function get_stage_notes() {
        $data = [];
        $this->db->select(['ras.application_id', 'ras.notes', 'ras.created', 'ras.stage', 'ras.recruiterId','raa.status']);
        $this->db->from("tbl_recruitment_applicant_stage_notes as ras");
        $this->db->join('tbl_recruitment_applicant_applied_application as raa', 'ras.application_id = raa.id', 'inner');
        $this->db->order_by('ras.created', 'DESC');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result();
        }
        return $data;
    }


    /**
     * Delete historical data
     */
    public function delete_data($id,$tableName) {
        $this->basic_model->delete_records($tableName, ["id" => $id]);

    }
     /**
     * Get Feed
     */
    public function get_feed() {
        $data = [];
        $this->db->select(['id', 'history_id']);
        $this->db->from("tbl_application_history_feed");
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result();
        }
        return $data;
    }

    /**
     * Get Stage id
     */
    public function getStageId($stage) {
        $data = [];
        $this->db->select(['id']);
        $this->db->from("tbl_recruitment_stage");
        $this->db->where("stage_label_id", $stage);
        $res = $this->db->get()->row();
        if(@$res->id)
          return $res->id;

    }

    /*
     * To save feed
     *
     * @params {array} $data
     * @params {int} $adminId
     *
     * return type feedtId
     */
    function save_feed_migrate($data,$stage_id) {
        $table_history = 'application_history';
        $table_feed = 'application_history_feed';
        $source_field = 'application_id';
        if(@$data->application_id && $data->application_id > 0)
        {

           $feed_title = $this->getFeedTitle($stage_id,$data->notes,$data->status);
           $bSuccess = $this->db->insert(
                TBL_PREFIX . $table_history,
                [
                    $source_field => $data->application_id,
                    'created_by' =>  $data->recruiterId,
                    'created_at' =>  $data->created
                ]
            );
            $history_id = $this->db->insert_id();
            $feed_id = $this->create_feed_history_entry($history_id, $data->application_id, 'feed', $feed_title, $data->recruiterId, $table_feed);
            return $history_id;

        }
    }

    /**
     * Get  Field title
     */
    public function getFeedTitle($stage,$notes,$status)
    {
        $application_statuses = $this->get_application_stage_status_history();
        $interview_array = array(2,3,4,5,15,16,17,19,20,23,21,22,24);
        $doc_array = array(6,7);
        $cab_array = array(9,10,11,12);

        if($stage == 1)
          $app_status = $application_statuses['data'][1]['label'];
        else if($stage == 8)
          $app_status = $application_statuses['data'][3]['label'];
        else if($stage == 18)
          $app_status = $application_statuses['data'][6]['label'];
        else if($stage == 14 && $status == 2)
          $app_status = $application_statuses['data'][8]['label'];
        else if($stage == 14 && $status == 3)
          $app_status = $application_statuses['data'][7]['label'];
        else if (in_array($stage,$interview_array))
          $app_status = $application_statuses['data'][2]['label'];
        else if (in_array($stage,$doc_array))
          $app_status = $application_statuses['data'][4]['label'];
        else if (in_array($stage,$cab_array))
          $app_status = $application_statuses['data'][5]['label'];
        else if($status == 0 )
          $app_status = $application_statuses['data'][0]['label'];
        if(@$app_status)
          $feedTitle = $app_status."-".$notes;
        else
          $feedTitle = $notes;
         return $feedTitle;

    }

    /**
     * Save feed
     */
    public function create_feed_history_entry($history_id, $opportunity_id, $field, $val, $adminId, $table_feed)
    {
        $bSuccess = $this->db->insert(TBL_PREFIX . $table_feed, [
            'history_id' => $history_id,
            'desc' => $val,
            'created_by' => $adminId,
            'created_at' => DATE_TIME
        ]);

        return $bSuccess;
    }

    /**
     * Find notes by stage and applicant ID. If `application_id` is not empty, will also filter by `application_id`
     */
    public function get_attachment_stage_notes_by_applicant_id($applicantId = 0, $application_id = 0) {
        $data = [];
        $applicantId = (int) $applicantId;
        $this->db->select(['rasn.applicant_id', 'rasn.id', 'rasn.notes', 'rasn.is_main_stage_label', 'date_format(rasn.created,"%d/%m/%Y") as created_date',
            'rasn.application_id',
        ]);
        $this->db->select([
            '(case when recruiterId>0 then (select concat_ws(" ",firstname,middlename,lastname) From tbl_member as m  WHERE m.id=rasn.recruiterId) else ("") END) as created_by',
            '(case when is_main_stage_label=0 then (select stage From tbl_recruitment_stage as rs where rasn.stage=rs.id) else (select stage_number From tbl_recruitment_stage_label as rsl where rasn.stage=rsl.id) END) as current_stage_level'], false);
        $this->db->from("tbl_recruitment_applicant_stage_notes as rasn");
        $this->db->where("rasn.applicant_id", $applicantId);

        if (!empty($application_id)) {
            $this->db->where("rasn.application_id", $application_id);
        }

        $this->db->order_by('rasn.created', 'DESC');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result();
        }
        return $data;
    }

    function check_applicant_onboarding_eligibility($task_applicant_id, $type = 'onboarding') {
        $typeData = $type == 'onboarding' ? 'interview_det.app_login_status' : 'interview_det.app_orientation_status';
        $task_applicant_id = $this->db->escape_str($task_applicant_id, true);
        $this->db->select([$typeData . ' as status_onboarding']);
        $this->db->from('tbl_recruitment_task as rt');
        $this->db->join('tbl_recruitment_task_applicant as rta', 'rta.taskId = rt.id AND rta.archive = 0 AND rta.status IN (0,1) AND rta.id = ' . $task_applicant_id, 'inner');
        $this->db->join('tbl_recruitment_applicant_group_or_cab_interview_detail as interview_det', 'rta.id = interview_det.recruitment_task_applicant_id AND interview_det.archive = 0', 'inner');

        $this->db->where('interview_det.recruitment_task_applicant_id', $task_applicant_id);
        $this->db->where('rt.status', 1);

        $this->db->group_by('rt.id');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->row('status_onboarding');

        return $result;
    }

    function add_applicant_orientation($task_applicant_id, $status) {
        $where = ['recruitment_task_applicant_id' => $task_applicant_id];
        $data = ['app_orientation_status' => $status];

        $this->basic_model->update_records('recruitment_applicant_group_or_cab_interview_detail', $data, $where);
    }

    function add_applicant_onboarding($task_applicant_id, $status) {
        $where = ['recruitment_task_applicant_id' => $task_applicant_id];
        $data = ['app_login_status' => $status];

        $this->basic_model->update_records('recruitment_applicant_group_or_cab_interview_detail', $data, $where);
    }

    function resend_docusign_enevlope($applicantData) {

        $task_applicant_id = $applicantData->task_applicant_id;
        $applicant_id = $applicantData->applicant_id;

        $applicant_info = $this->get_applicant_contract_data($applicant_id);

        $this->db->select(['appContract.envelope_id', 'appContract.unsigned_file']);
        $this->db->from('tbl_recruitment_applicant_contract as appContract');
        $this->db->where('appContract.task_applicant_id', $task_applicant_id);
        $this->db->where('appContract.signed_status', 0);
        $this->db->where('appContract.archive=0');
        // order by id desc
        $this->db->limit(1);

        $query_task_info = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        if ($query_task_info->num_rows() > 0) {
            $info = $query_task_info->row_array();
            $applicant_info['envelope_id'] = $info['envelope_id'];
            $applicant_info['unsigned_file'] = $info['unsigned_file'];
        }
        return $applicant_info;
    }

    public function get_applicant_contract_data($applicant_id = 0) {
        $this->db->select(array("concat(ra.firstname,' ',ra.middlename,' ',ra.lastname) as applicant_name"));
        $this->db->select(['(SELECT email FROM tbl_recruitment_applicant_email where primary_email=1 and archive=0 AND applicant_id=ra.id) as email', false]);
        $this->db->select(['(SELECT phone FROM tbl_recruitment_applicant_phone where primary_phone=1 and archive=0 AND applicant_id=ra.id) as phone', false]);
        $this->db->from('tbl_recruitment_applicant as ra');
        $this->db->where(array('ra.id' => $applicant_id));
        $this->db->where(array('ra.archive=' => 0));
        $query_task_info = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $info = $query_task_info->row_array();

        return $info;
    }

    function get_applicant_skill($applicant_id) {
        $applicant_id= $this->escape_str($applicant_id, true);
        $this->db->select(array("pg.name", "pg.id", "pg.key_name"));
        $this->db->select(['(SELECT id FROM tbl_recruitment_applicant_skill where applicant_id=' . $applicant_id . ' and skillId = pg.id) as checked', false]);
        $this->db->select("(CASE WHEN pg.key_name = 'other' then (SELECT other_title FROM tbl_recruitment_applicant_skill where applicant_id=" . $applicant_id . " and skillId = pg.id) else '' end) as other_title");
        $this->db->from('tbl_participant_genral as pg');
        $this->db->where(array('pg.type' => "assistance"));
        $this->db->order_by("pg.order", "asc");

        $query_task_info = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $skill = $query_task_info->result();

        if (!empty($skill)) {
            foreach ($skill as $val) {
                $val->checked = ($val->checked > 0) ? true : false;
            }
        }
        return $skill;
    }

    function update_applicant_skill($reqData) {
        $applicant_id = $reqData->applicant_id ?? 0;

        $skill = [];

        if (!empty($reqData->applicant_skill)) {
            foreach ($reqData->applicant_skill as $val) {
                if (!empty($val->checked)) {
                    $x = ['skillId' => $val->id, "applicant_id" => $applicant_id, "created" => DATE_TIME];

                    if ($val->key_name === 'other') {
                        $x['other_title'] = $val->other_title;
                    } else {
                        $x['other_title'] = '';
                    }

                    $skill[] = $x;
                }
            }
        }

        if (!empty($skill)) {
            $this->basic_model->delete_records("recruitment_applicant_skill", ["applicant_id" => $applicant_id]);
            $this->basic_model->insert_records('recruitment_applicant_skill', $skill, true);
        }

        return true;
    }

    /**
     * Find all types of documents that will be needed in
     * job application process
     *
     * @param int|int[] $jobID
     * @return array[]
     */
    public function find_all_doctypes_needed_by_job_id($jobID) {
        $query = $this->db
                ->from('tbl_references r')
                ->join('tbl_recruitment_job_posted_docs p', 'r.id = p.requirement_docs_id', 'LEFT')
                ->where([
                    'p.jobId' => $jobID,
                    'p.archive' => 0,
                    'r.archive' => 0
                ])
                ->select('r.*');
        $this->db->where_in('r.type', array(4, 5));
        // $sql = $query->get_compiled_select(null, false);

        $results = $query->get()->result_array();
        return $results;
    }

    /**
     * Determine whether applicant applied through `seek` or `website`
     * @param object $applicant
     */
    protected function determine_applied_through($applicant) {
        $questions = $applicant->questions;
        if (array_key_exists('seek', $questions) && !empty($questions['seek'])) {
            return "Seek";
        }

        return "Website";
    }

    // @todo:
    // Should the method be called as 'get_application_job_stage'?
    public function get_applicant_job_stage($applicant_id, $application_id = 0) {
        $applicant_id= $this->escape_str($applicant_id, true);
        $this->db->select(['rsl.key_name']);
        $this->db->from('tbl_recruitment_job_stage as rjs');
        $this->db->join('tbl_recruitment_applicant_applied_application as raaa', 'raaa.jobId = rjs.jobId AND raaa.archive = 0 AND raaa.status IN (1,2,3) and raaa.applicant_id = '.$applicant_id, 'inner');
        $this->db->join('tbl_recruitment_stage_label as rsl', 'rjs.stage_id = rsl.id AND rsl.archive = 0 AND rsl.used_in_create_job=1', 'inner');
        $this->db->where(array('rjs.archive'=>0));

        if (!empty($application_id)) {
            $this->db->where(['raaa.id' => $application_id]);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $rows = $query->result_array();
        if (!empty($rows)) {
            return array_column($rows, 'key_name');
        } else {
            return [];
        }
    }

    /**
     * Determine document status label based on given number. Document status can be 0-3.
     * If could not be determined, this will return empty string.
     *
     * Note: This method is being used exclusively by `$this->get_all_attachments_by_applicant_id()`
     * to determine the label of `tbl_recruitment_applicant_stage_attachment.document_status` column
     *
     * @param int $document_status
     * @return string
     */
    protected function determine_document_status_label($document_status) {
        $status = intval($document_status);

        switch ($status) {
            case 0:
                return 'Submitted';
            case 1:
                return 'Valid';
            case 2:
                return 'Invalid';
            case 3:
                return 'Expired';
            default:
                return '';
        }
    }

    /**
     * Finds out the location of the applicant attachment.
     *
     * Note: This method is used exclusively by `$this->get_all_attachments_by_applicant_id()`
     *
     * @param string $filename
     * @param int $applicantId
     * @return string
     */
    protected function determine_attachment_filename_path($filename, $applicantId) {
        $pathWithTrailingSlash = $this->adminPortalPath();
        $path = $pathWithTrailingSlash . implode(DIRECTORY_SEPARATOR, ["uploads", "recruitment", "applicant", $applicantId, $filename]);
        return $path;
    }

    /**
     * Determine the folder that contains the `index.php` file of the admin portal
     * based on multiple assumptions.
     *
     * Common use case of this helper method is to check if file exist or
     * as a reference point when uploading files.
     *
     * If the server is hosted publicly (by getting TLDs), this function will
     * assume that it lives in a special directory `/var/www/html/{OCS_ADMIN_URL}/public_html/`
     *
     * Otherwise it will assume that it is being served using the standard project
     * folder structure (ie. FCPATH/../../admin/back-end/)
     *
     * @return string
     */
    protected function adminPortalPath() {

        return FCPATH . DIRECTORY_SEPARATOR;

        // assume admin portal is located on '/var/www/html/{hostname}/public_html' or 'FCPATH/../../admin/back-end/'
        $admin_url = getenv('OCS_ADMIN_URL') ?: 'http://localhost:8888/';
        $admin_url = rtrim($admin_url, '/');
        $tokens = explode(".", $admin_url);
        $public_tlds = [".net", ".com", ".net.au", ".com.au"];
        $tld = "." . array_pop($tokens);
        $admin_path = implode(DIRECTORY_SEPARATOR, [realpath(dirname(dirname(FCPATH))), "admin", "back-end"]) . DIRECTORY_SEPARATOR;
        $parsed_url = parse_url($admin_url);
        if (in_array($tld, $public_tlds) && array_key_exists("host", $parsed_url)) {
            $admin_path = "/var/www/html/" . $parsed_url['host'] . "/public_html/";
        }

        return $admin_path;
    }

    /**
     * checking if the interview form has been filled by the applicant or not
     * Input: applicant_id, interview_type (key), application_id (TODO)
     * Returns: status (true/false), form id
     */
    function get_applicant_form_interview_status($applicant_id, $interview_type, $application_id) {
        $this->db->select(["rfa.id", "rfa.form_id"]);
        $this->db->from("tbl_recruitment_form_applicant as rfa");
        $this->db->join("tbl_recruitment_form as rf", "rf.id = rfa.form_id", "INNER");
        $this->db->join("tbl_recruitment_interview_type as rit", "rit.id = rf.interview_type AND rit.archive = 0", "INNER");
        $this->db->where("rit.key_type", $interview_type);
        $this->db->where("rfa.applicant_id", $applicant_id);
        $this->db->where("rfa.archive", 0);

        $this->db->where("rfa.application_id", $application_id);

        $res = $this->db->get()->row();

        return [
            "interview_applicant_form_id" => $res->id ?? '',
            "interview_status" => isset($res->id) ? true : false,
            "interview_form_id" => $res->form_id ?? ''
        ];
    }

    /**
     * Get document docs and return them as `id => code`
     * @return array<int, string>
     */
    public function document_codes() {
        $this->db->where('archive', 0);
        $this->db->where_in('type', array(5, 4));
        $q = $this->db->get_where("tbl_references")->result_array();

        $idToCodeMap = [];
        foreach ($q as $requirement) {
            $id = (int) $requirement['id'];
            $idToCodeMap[$id] = $requirement['code'];
        }

        return $idToCodeMap;
    }

    /**
     * Find job details based on stage attachment properties.
     *
     * If `application_id` is given, will use the associated job ID,
     * or we'll use the `applicant_id` to find the job ID and then find the
     * job details as fallback
     */
    public function find_job_by_stage_attachment(array $stage_attachment) {
        $application_id = (int) $stage_attachment['application_id'];
        $applicant_id = (int) $stage_attachment['applicant_id'];

        $job = null;

        // find by application ID
        if (!empty($application_id)) {
            $findJobByApplicationIdQuery = $this->db
                    ->from('tbl_recruitment_applicant_applied_application application')
                    ->join('tbl_recruitment_job job', 'application.jobId = job.id', 'INNER')
                    ->where([
                        'application.id' => $application_id,
                    ])
                    ->select(['job.*'])
                    ->get('', 1);

            $job = $findJobByApplicationIdQuery->row_array();
            return $job;
        }

        // fallback
        if (empty($application_id) && !empty($applicant_id)) {
            $findJobByApplicantId = $this->db
                    ->from('tbl_recruitment_applicant applicant')
                    ->join('tbl_recruitment_job job', 'applicant.jobId = job.id', 'INNER')
                    ->where([
                        'applicant.id' => $applicant_id,
                    ])
                    ->select(['job.*'])
                    ->get('', 1);

            $job = $findJobByApplicantId->row_array();
            return $job;
        }

        return $job;
    }

    /**
     * Determine new file name based on doc category and applicant id
     *
     * Currently used exclusively by `$this->get_all_attachments_by_applicant_id`
     *
     * @param string $originalFileNameWithExt
     * @param int $categoryId
     * @param int $applicantId
     * @return string
     */
    public function determine_filename_by_doc_category($originalFileNameWithExt, $categoryId, $applicantId) {
        $extension = pathinfo($originalFileNameWithExt, PATHINFO_EXTENSION);

        if (!$categoryId || !$applicantId) {
            return $originalFileNameWithExt;
        }

        $applicant = $this->db->get_where('tbl_recruitment_applicant', ['id' => $applicantId])->row_array();
        if (empty($applicant)) {
            return $originalFileNameWithExt;
        }

        $codes = $this->document_codes();

        $categoryId = intval($categoryId);
        if (!array_key_exists($categoryId, $codes)) {
            return $originalFileNameWithExt;
        }


        $firstname = $applicant['firstname'];
        $lastname = $applicant['lastname'];
        $docCode = $codes[$categoryId];

        $newName = trim("{$docCode}_{$firstname} {$lastname}");
        $tokens = array_filter([$newName, $extension]);
        $newFilename = implode('.', $tokens);

        return $newFilename;
    }

    function get_stageid_by_key_bypass($typeDataStage = [0]) {
        $this->db->select(['id']);
        $this->db->from('tbl_recruitment_stage');
        $this->db->where_in('stage_key', $typeDataStage);
        $this->db->where('archive', 0);
        $this->db->order_by('stage_order');
        $query = $this->db->get();
        $res = [];
        if ($query->num_rows() > 0) {
            $res = $query->result_array();
            $res = array_map('current', $res);
        }
        return $res;
    }

    function get_last_action_complete_unsucess_stage_by_applicant_Id(int $applicantId = 0, $application_id = 0) {
        $res = $res = $this->basic_model->get_row('recruitment_applicant_applied_application', ['current_stage', 'status'], ['id' => $application_id]);
        ;
        $currentStage = 1;
        $previousCompleteStage = 0;
        if (!empty($res)) {
            $currentStage = $previousCompleteStage = $res->current_stage;
            if ($res->status == 1 || $res->status == 3) {
                $previousCompleteStageData = $this->get_nex_stage_recruitment($currentStage, $applicantId, $application_id, ['previous_stage' => true]);

                if (!empty($previousCompleteStageData)) {
                    $previousCompleteStage = $previousCompleteStageData->id;
                }
            }
            $previousCompleteStage = $currentStage == 1 ? 0 : $previousCompleteStage;
        }
        return ['current_stage' => $currentStage, 'previous_stage' => $previousCompleteStage];
    }

    public function get_last_task_applicant_id_by_stage_key_and_applicant_id(int $applicant_id = 0, $extraParm = []) {
        $applicant_id = $this->db->escape_str($applicant_id, true);
        $taskStageKey = $this->db->escape_str(($extraParm['task_stage_key'] ?? ''), true);
        $this->db->select(['rta.id as task_applicant_id']);
        $this->db->from(TBL_PREFIX . 'recruitment_task_applicant as rta');
        $this->db->join(TBL_PREFIX . "recruitment_task as rt", "rt.id=rta.taskId and rta.archive=0 and rt.status!=4 and rta.applicant_id='" . $applicant_id . "'", "inner");
        $this->db->join(TBL_PREFIX . "recruitment_task_stage as rts", "rts.id=rt.task_stage and rta.archive=0 and rts.key='" . $taskStageKey . "'", "inner");
        $this->db->order_by("rt.id", "DESC");
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->row_array() : [];
    }

    public function create_log_on_final_stage($reqData, $adminId,$type)
    {
        if($type == 1){
           $log_title = 'Applicant is rejected, Applicant id :- '.$reqData->applicant_id;
        }else if($type == 2){
            $log_title = 'Applicant is hired as member, applicant id :-'.$reqData->applicant_id;
        }else{
            $log_title = '';
        }
        $this->loges->setLogType('recruitment_applicant');
        $this->loges->setTitle($log_title);
        $this->loges->setUserId($adminId);
        $this->loges->setCreatedBy($adminId);
        $this->loges->setDescription(json_encode($reqData));
        $this->loges->createLog();
    }

    /*
     * Applicant stage comes from jobs
     * when job is created at that time his stage is created
     * And applicant is linked to that job
     */

    function get_applicant_stage_by_jobs_id($applicantId, $application_id) {
        $applicant_id = $this->db->escape_str($applicant_id, true);
        $this->db->select(['rs.id', 'rsl.key_name']);
        $this->db->from("tbl_recruitment_stage as rs");
        $this->db->join('tbl_recruitment_stage_label as rsl', 'rsl.id = rs.stage_label_id AND rsl.archive = 0', 'inner');
        $this->db->join('tbl_recruitment_job_stage as rjs', 'rjs.stage_id = rsl.id AND rjs.archive = 0', 'inner');
        $this->db->join('tbl_recruitment_applicant_applied_application as raaa', 'raaa.jobId = rjs.jobId AND raaa.id =' . $application_id . ' AND raaa.applicant_id = ' . $applicantId, 'inner');
        $this->db->where("rs.archive", 0);
        $query = $this->db->get();
        return $query;
    }

    function get_recruiter_ids_by_condition($filter_condition) {
        $this->db->select("m.id");
        $this->db->from("tbl_member as m");
        $this->db->join("tbl_department as d", "d.id = m.department AND d.short_code = 'internal_staff'", 'inner');
        $this->db->where($filter_condition);
        $query = $this->db->get();
        $q=$this->db->last_query();
        $rows = $query->result_array();
        return $rows;
    }

    function get_channel_ids_by_condition($filter_condition) {
        $this->db->select("rc.id");
        $this->db->from("tbl_recruitment_channel as rc");
        $this->db->where($filter_condition);
        $query = $this->db->get();
        $rows = $query->result_array();
        return $rows;
    }

    function get_applicant_job_application_by_job_id($applicant_id, $application_id) {
        try{
            $this->db->select(['rjp.title as position_applied', 'raaa.position_applied as position_applied_id', 'raaa.id', 'rd.name as recruitment_area', 'raaa.recruitment_area as recruitment_area_id',
            'raaa.employement_type as employement_type_id', 'r.display_name as employement_type', 'raaa.channelId as channel_id', 'raaa.referrer_url', 'rc.channel_name as channel',
            'raaa.jobId',
            'raaa.applicant_id',
            'raaa.prev_application_process_status',
            "CONCAT_WS(' ', recruiter.firstname, recruiter.lastname) as recruiter_fullname",
            "recruiter.id as recruiter",
            'raaa.status','raaa.application_process_status',
            "(
            CASE
                WHEN raaa.status = 0 THEN 'Unknown'
                WHEN raaa.status = 1 THEN 'In-progress'
                WHEN raaa.status = 2 THEN 'Rejected'
                WHEN raaa.status = 3 THEN 'Completed'
                ELSE 'Unknown'
            END
        ) as status_label",
        'raaa.application_process_status',
            "(
            CASE
                WHEN raaa.application_process_status = 0 THEN 'New'
                WHEN raaa.application_process_status = 1 THEN 'Screening'
                WHEN raaa.application_process_status = 2 THEN 'Interviews'
                WHEN raaa.application_process_status = 3 THEN 'References'
                WHEN raaa.application_process_status = 4 THEN 'Documents'
                WHEN raaa.application_process_status = 5 THEN 'In progress'
                WHEN raaa.application_process_status = 6 THEN 'CAB'
                WHEN raaa.application_process_status = 7 THEN 'Hired'
                WHEN raaa.application_process_status = 8 THEN 'Rejected'
                ELSE 'Unknown'
            END
        ) as process_status_label",
        'raaa.is_reference_marked',
        'raaa.is_document_marked',
        'raaa.created'
                // ABOVE: status_label (except 'Unknown') similar to ones mentioned in tbl_recruitment_applicant.status.
                // If you have status label of 'Unknown' displayed means something's wrong with your code
        ]);
        $this->db->from('tbl_recruitment_applicant_applied_application as raaa');
        $this->db->join('tbl_recruitment_job_position as rjp', 'raaa.position_applied = rjp.id', 'inner');
        $this->db->join('tbl_recruitment_department as rd', 'raaa.recruitment_area = rd.id', 'inner');
        $this->db->join('tbl_references as r', 'raaa.employement_type = r.id', 'inner');
        $this->db->join('tbl_recruitment_channel as rc', 'raaa.channelId = rc.id', 'inner');
        $this->db->join('tbl_member as recruiter', 'raaa.recruiter = recruiter.uuid', 'LEFT'); // let's use LEFT JOIN because raaa.recruiter is nullable and isn't guaranteed to be filled
        $this->db->where('raaa.applicant_id', $applicant_id);
        $this->db->where('raaa.archive', 0);
        if(!empty($application_id)){
            $this->db->where('raaa.id', $application_id);
        }
       
        # Throw exception if db error occur
        if (!$query = $this->db->get()) {     
            $db_error = $this->db->error();
            throw new Exception('Something went wrong!');
        }
        $res = $query->row();
        if (!empty($res)) {
            try{
                    if ($res->channel_id == 2 && $res->referrer_url != '') {
                        $res->channel = !empty($res->referrer_url) ? getDomain($res->referrer_url) : 'N/A';
                    }
                    $this->db
                        ->from('tbl_recruitment_job job')
                        ->join('tbl_recruitment_job_category job_category', 'job.category = job_category.id', 'INNER')
                        ->join('tbl_recruitment_job_category job_sub_category', 'job.sub_category = job_sub_category.id', 'INNER')
                        ->where(['job.id' => $res->jobId])
                        ->select([
                            'job.*',
                            'job_category.name AS job_category_label',
                            'job_sub_category.name AS job_sub_category_label',
                        ]);
                        # Throw exception if db error occur
                        if (!$query = $this->db->get('', 1)) {               
                            $db_error = $this->db->error();
                            throw new Exception('Something went wrong!');
                        }
                    
                    $res->job_details=$query->row_array();
                    
                }catch(\Exception $e){
                    return array('status' => false, 'error' => 'Something went wrong');            
                }
               
                // Find all forms submitted by application ID
                // @todo: To improve performance, you may want to eager-load this
                try{
                     $this->db
                    ->from('tbl_recruitment_form_applicant fa')
                    ->join('tbl_recruitment_form f', 'f.id = fa.form_id', 'LEFT')
                    ->join('tbl_recruitment_interview_type i', 'i.id = f.interview_type', 'LEFT')
                    ->where(['fa.application_id' => $res->id,])
                    ->select([
                        'fa.*',
                        'f.interview_type as form_interview_type',
                        'i.name AS form_interview_type_label',
                        'i.key_type AS form_interview_type_key_type',
                        'f.interview_type AS form_category',
                        'i.name AS form_category_label',
                        'i.key_type AS form_category_key_type',
                        'f.title AS form_title',
                    ])
                    ->order_by('fa.date_created', 'DESC');
                    # Throw exception if db error occur
                    if (!$query = $this->db->get()) {               
                        $db_error = $this->db->error();
                        throw new Exception('Something went wrong!');
                    }
                    $res->submitted_forms = $query->result();                    
                    }catch(\Exception $e){
                        return array('status' => false, 'error' => 'Something went wrong');            
                    }
                }
                
             return $res;
        }catch(\Exception $e){
            return array('status' => false, 'error' => 'Something went wrong');            
        }
        
        }

         /**
     * fetching the application information
     */
    public function get_application_details($application_id = null) {
        if (empty($application_id)) return;

        $this->db->select("raaa.*");
        $this->db->from('tbl_recruitment_applicant_applied_application as raaa');

        if($application_id)
            $this->db->where("raaa.id", $application_id);

        $this->db->where("raaa.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Application not found!", 'status' => false);
            return $return;
        }
        foreach ($query->result() as $val) {
            $dataResult = $val;
        }

        $return = array('data' => $dataResult, 'status' => true, 'last_query' => $last_query);
        return $return;
    }

    /**
     * fetches all the application statuses
     */
    public function get_application_stage_status() {
        $data = null;
        foreach($this->application_stage_status_grouped as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * fetches all the application statuses
     */
    public function get_application_stage_status_history() {
        $data = null;
        foreach($this->application_stage_status as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * fetches all the application statuses
     */
    public function get_application_status_for_update() {
        $data = null;
        foreach($this->application_stage_status as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $label;
            $newrow['org_value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * fetches all the final application final statuses
     */
    public function get_application_statuses_final() {
        $data = null;
        foreach($this->application_stage_status_final as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * Updating the application status.
     */
    function update_application_status($data, $adminId , $isFromGroupBooking=false,$reqData ) {

        $id = isset($data['id']) ? $data['id'] : 0;
        if (empty($id)) {
            return ['status' => false, 'error' => "Missing ID"];
        }

        # does the application exist?
        $result = $this->get_application_details($data['id']);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Application does not exist anymore."];
            return $response;
        }
        $is_hired = $this->check_application_process_status($data['id'], $data['applicant_id'], 7);

        if($is_hired) {
            return ['status' => false, 'error' => "Application Stage cannot be reverted since it is already marked as hired."];
        }
       // History field updating through group booking or application page
        if($isFromGroupBooking){
            $dataToBeUpdated = [
                'status_updated_from_gb' =>$data['application_process_status']
            ];
        }else{
            $dataToBeUpdated = [
                'status' =>$data['application_process_status']
            ];
        }
       
        $emp_status = false;
        if($data["application_process_status"] == 7) {
            //Check the docusign document list
            $req_sign_data = new stdclass();
            $req_sign_data->applicant_id = $data['applicant_id'];
            $req_sign_data->application_id = $id;
            $req_sign_data->page = 0;
            $req_sign_data->pageSize = 1;

            $this->load->model('recruitment/Recruitment_applicant_docusign_model');
            $docu_data = $this->Recruitment_applicant_docusign_model->get_docusign_document_list($req_sign_data, false,$reqData);
            if(empty($docu_data['data'])) {
                $emp_status = ['status' => false, 'error' => "Reference check, Document check, and Signed Contract should be available"];
            } else {
                foreach($docu_data['data'] as $doc_data) {
                    if($doc_data->signed_status == 0) {
                        $emp_status = ['status' => false, 'error' => "Reference check, Document check, and Signed Contract should be available"];
                    }
                }
            }
        }
        if($emp_status) {
            return $emp_status;
        }
        # updating status
        $upd_data["application_process_status"] = $data['application_process_status'];
        //fetching prev application process status
        $upd_data["prev_application_process_status"] = $result['data']->application_process_status;
        $upd_data["updated"] = DATE_TIME;
        $this->basic_model->update_records("recruitment_applicant_applied_application", $upd_data, ["id" => $id]);
        //if hired means update recruitment status as completed
        if($data["application_process_status"] == 7) {
            $applicant_status['status'] = 3;
            $this->basic_model->update_records("recruitment_applicant", $applicant_status, ["id" => $data['applicant_id']]);
            //if there is an inactive member then activate it
            $applicant = $this->get_applicant_info($data['applicant_id'], $adminId);
            $member_id = $this->Member_model->get_member_id_from_email($applicant['username']);
            if (!empty($member_id)) {
                $this->update_records("member", ['archive' => 0, 'status' => 1, 'enable_app_access' => 1], ["id" => $member_id]);
            }
        }
        // if rejected means send the email using selected template id
        $newReq = [];
        if($data["application_process_status"] == 8){                  
            if(!empty($data['uuid'])){
                $this->basic_model->update_records('users', ['status' => 0, 'token' => NULL, 'updated_at' => DATE_TIME], ['id' => $data['uuid'], 'user_type'=> MEMBER_PORTAL]);
                $member_details = $this->basic_model->get_row('member', ['id','uuid'], ['uuid' => $data['uuid']]);
                if(!empty($member_details)){
                    $this->basic_model->update_records('member', ['status' => 0, 'updated_date' => DATE_TIME], ['uuid' =>$data['uuid']]);
                }
            }
            if(!empty($data['selected_template'])){
                // $this->send_rejection_email_template_to_email($data['selected_template'],$data['applicant_id'], $adminId, $data['id']);    
                $this->load->library('Asynclibrary');
                $newReq = ['selected_template' => $data['selected_template'], 'applicant_id' => $data['applicant_id'], 'adminId' => $adminId, 'id' => $data['id']];
                $url = base_url()."recruitment/RecruitmentApplicant/send_rejection_email_template_to_email";            
                $param = array('reqData' => $newReq );
                $this->asynclibrary->do_in_background($url, $param);
            } 
        }

        $this->updateHistory($result, $dataToBeUpdated, $adminId);

        # adding a log entry
        $msg = "Application status is updated successfully";

        $response = ['status' => true, 'msg' => $msg, 'newReq' => $newReq];
        $this->add_create_update_application_log($upd_data, $msg, $adminId, $id);
        return $response;
    }

    /**
     * Create history item for each change field
     * @param array $Id of Existing application
     * @param array $dataToBeUpdated Modified data of Lead
     * @return void
     */
    public function updateHistory($application_details, $dataToBeUpdated, $adminId) {
      
        if ((array_key_exists("status",$dataToBeUpdated ))|| (array_key_exists("status_updated_from_gb",$dataToBeUpdated)))
        {
            $filedName = 'application_process_status';
            $indexField = 'data';
            $fieldValue = $application_details['data']->$filedName;
            $application_id = $application_details['data']->id;
        }else if (array_key_exists("owner",$dataToBeUpdated))
        {
             $filedName = 'recruiter';
             $indexField = 0;
             $fieldValue = $application_details->$filedName;
             $application_id = $application_details->id;
        }
        if (!empty($dataToBeUpdated)) {
            $new_history = $this->db->insert(
                TBL_PREFIX . 'application_history',
                [
                    'application_id' => $application_id,
                    'created_by' => $adminId,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            );
            $history_id = $this->db->insert_id();

            if(array_key_exists("status_updated_from_gb",$dataToBeUpdated)){
                $this->create_field_history($history_id, $application_id, 'status_updated_from_gb', $dataToBeUpdated['status_updated_from_gb'],  $fieldValue);
            }else if(array_key_exists("oa_status",$dataToBeUpdated)){
                $this->create_field_history($history_id, $application_id, 'oa_status', $dataToBeUpdated['oa_status'],  '');
            }else{
                foreach($dataToBeUpdated as $field => $new_value) {
                    if ($fieldValue != $new_value && !empty($new_history)) {
                        $this->create_field_history($history_id, $application_id, $field, $new_value, $fieldValue);
                    }
                }
            }
            
        }
    }

    /**
     * Create history record to be used for all history items in the update
     * @param int $history_id Id of related update history
     * @param int $application_id
     * @param string $fieldName
     * @param string $new_value
     * @param string $oldValue
     * @return int Last insert id
     */
    public function create_field_history($history_id, $application_id, $fieldName, $newValue, $oldValue) {
        return $this->db->insert(TBL_PREFIX . 'application_field_history', [
            'history_id' => $history_id,
            'application_id' => $application_id,
            'field' => $fieldName,
            'prev_val' => $oldValue,
            'value' => $newValue ?? ''
        ]);
    }

    /*
     * used by the create_update_application function to insert a log entry on
     * application adding / updating
     */
    public function add_create_update_application_log($data, $title, $adminId, $application_id) {
        $this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle($title);
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($application_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
    }

    /*
     * It is used to get the document list
     * Operation:
     *  - searching
     *  - filter
     *  - sorting
     *
     * Return type Array
     */
    public function get_document_list($reqData, $is_portal = false,$app_data) {
    try{
        // Get subqueries
        $document_name_sub_query = $this->get_document_name_sub_query('tda');
        $applicant_name_sub_query = $this->get_applicant_sub_query('tda', 'entity_id');
        $member_created_by_sub_query = $this->get_created_updated_by_sub_query('created_by','tda');
        $member_updated_by_sub_query = $this->get_created_updated_by_sub_query('updated_by','tda');
        $applicant_updated_by_sub_query = $this->get_applicant_sub_query('tda', 'updated_by');
        $applicant_created_by_sub_query = $this->get_applicant_sub_query('tda', 'created_by');

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $member_id = $reqData->member_id ?? '';
        $applicant_id = $reqData->applicant_id ?? '';
        $application_id = $reqData->application_id ?? '';
        $job_id = $reqData->job_id ?? '';
        $orderBy = '';
        $direction = '';
        
        // where document type is
        $where_entity_type = [];
        $where_entity_id = [];

        //Pull job id if job id not available for mandatory field check
        if(empty($job_id)) {
            $res = $this->basic_model->get_record_where('recruitment_applicant_applied_application', ['jobId'], ['applicant_id' => $applicant_id, 'id' => $application_id]);
            $job_id = !empty($res[0]->jobId) ? $res[0]->jobId : '';
        }

        require_once APPPATH . 'Classes/document/DocumentAttachment.php';

        $docAttachObj = new DocumentAttachment();

        // Get constant
        $relatedToRecruitment = $docAttachObj->getConstant('RELATED_TO_RECRUITMENT');
        $relatedToMember = $docAttachObj->getConstant('RELATED_TO_MEMBER');
        $entityTypeApplicant = $docAttachObj->getConstant('ENTITY_TYPE_APPLICANT');
        $entityTypeMember = $docAttachObj->getConstant('ENTITY_TYPE_MEMBER');
        $draftStatus = $docAttachObj->getConstant('DOCUMENT_STATUS_DRAFT');

        if ($applicant_id != '') {
          $where_entity_type[] = $entityTypeApplicant;
          $where_entity_id[] = $applicant_id;
        }

        // Searching column
        $src_columns = array('file_name', 'file_type', 'status', 'reference_number', );
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
        $available_column = ["id", "doc_type_id", "entity_id", "entity_type", "document_id", "document_status", "member_id", "archive", "issue_date", 
                             "expiry_date", "reference_number", "created_by", "created_at", "updated_by", "updated_at", "file_name", "draft_contract_type",
                             "applicant_id", "file_type", "file_size", "raw_name", "file_ext", "attached_on",
                             "updated_on", "aws_uploaded_flag", "file_base_path", "converted_name", "uri_param_1", "system_gen_flag"
                           ];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)  || $sorted[0]->id == 'is_mandatory') {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';               
            }
        } else {
            $orderBy = 'tda.id';
            $direction = 'DESC';
        }

        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));
        // Filter by status
        if (!empty($filter->filter_status)) {
            if ($filter->filter_status === "submitted") {
                $this->db->where('tda.document_status', 0);
            } else if ($filter->filter_status === "valid") {
                $this->db->where('tda.document_status', 1);
            } else if ($filter->filter_status === "invalid") {
                $this->db->where('tda.document_status', 2);
            } else if ($filter->filter_status === "expired") {
                $this->db->where('tda.document_status', 3);
            } else if ($filter->filter_status === "draft") {
                $this->db->where('tda.document_status', 4);
            }
        }

        $base_url = base_url('mediaShow/m');

        $select_column = ["tda.id", "tda.doc_type_id", "tda.entity_id", "tda.entity_type", "tda.id as document_id", "tda.document_status", "tda.member_id", "tda.archive", "tda.issue_date", "tda.expiry_date", "tda.reference_number", "tda.created_by", "tda.created_at", "tda.updated_by", "tda.updated_at", "tdap.file_name", "tda.draft_contract_type", "tda.applicant_id",
            "tdap.file_type",
            "tdap.file_size",
            "tdap.raw_name",
            "CONCAT('.',tdap.file_ext) AS file_ext",
            "tdap.created_at AS attached_on",
            "tda.updated_at AS updated_on",
            "tdap.aws_uploaded_flag",
            "'".$base_url."' AS file_base_path",
            "TO_BASE64(tdap.file_name) AS converted_name",
            "'name=' AS uri_param_1",
            "CONCAT('mediaShow/m', '/', tda.id, '/', REPLACE(TO_BASE64(tdap.file_path), '=', '%3D%3D'), '?download_as=', REPLACE(tdap.raw_name, ' ', ''), '&s3=true') AS file_path",
            "tdt.system_gen_flag",
        ];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        // Query
        $this->db->select("(CASE
          WHEN tda.draft_contract_type = 1 THEN CONCAT('Draft Contract_', ra.firstname,' ', ra.lastname)
          WHEN tda.draft_contract_type = 2 THEN CONCAT('Employment Contract_', ra.firstname,' ', ra.lastname)
                else
                    CASE
                        WHEN tdt.title IS NOT NULL THEN
                            CONCAT(tdt.title,'_',, ra.firstname,' ', ra.lastname)
                        ELSE
                            CONCAT(ra.firstname,' ', ra.lastname)
                        END
                end) as download_as
        ");

        $this->db->select("
          CASE
            WHEN tda.entity_type = 1 THEN
            (
              CASE
              WHEN tda.draft_contract_type = 1 THEN CONCAT('Draft Contract_', ra.firstname,' ', ra.lastname)
              WHEN tda.draft_contract_type = 2 THEN CONCAT('Employment Contract_', ra.firstname,' ', ra.lastname)
              ELSE
                  CASE
                      WHEN tdt.title IS NOT NULL THEN
                          CONCAT(tdt.title,'_',, ra.firstname,' ', ra.lastname)
                      ELSE
                          CONCAT(ra.firstname,' ', ra.lastname)
                      END
              END
            )
            ELSE
              tdap.file_name
            END
           as file_name
        ");

        $this->db->select("(" . $document_name_sub_query . ") as document");
        $this->db->select("
            (" . $applicant_name_sub_query . ")
         as member");
        $this->db->select("
            (" . $applicant_created_by_sub_query . ")
         as created_by");
         $this->db->select("
            (" . $applicant_updated_by_sub_query . ")
         as updated_by");
        $this->db->select("(CASE
            WHEN tda.document_status = 0 THEN 'Submitted'
            WHEN tda.document_status = 1 THEN 'Valid'
            WHEN tda.document_status = 2 THEN 'InValid'
            WHEN tda.document_status = 3 THEN 'Expired'
            WHEN tda.document_status = 4 THEN 'Draft'
          Else '' end
        ) as status");
        $this->db->from('tbl_document_attachment as tda');
        $this->db->join('tbl_document_attachment_property as tdap', 'tdap.doc_id = tda.id AND tdap.archive = 0', 'left');
        $this->db->join('tbl_document_type as tdt', 'tdt.id = tda.doc_type_id AND tdt.archive = 0', 'left');
        $this->db->join("tbl_recruitment_applicant as ra", "ra.id = tda.entity_id AND tda.entity_type=".$entityTypeApplicant, "LEFT");
        $bu_where = ['tda.archive'=> 0];
        if($this->Common_model->check_is_bu_unit($app_data)) {
            $bu_where = ['tda.archive'=> 0,'ra.bu_id' => $app_data->business_unit['bu_id']];
        }
        $this->db->where($bu_where);
        if ($is_portal == false) {
          $this->db->where('tda.document_status !=', $draftStatus);
        }
        if(isset($application_id)) {
            $this->db->where("tda.application_id", $this->db->escape_str($application_id, true));
        }
        if (!empty($applicant_id)) {
            $this->db->group_start();
            $this->db->where("tda.entity_type", $entityTypeApplicant);
            $this->db->where("tda.entity_id", $this->db->escape_str($applicant_id, true));
            $this->db->group_end();
        }
        $this->db->where('tda.draft_contract_type !=', 2);
        if($orderBy != 'is_mandatory') {
            $this->db->order_by($orderBy, $direction);
        }
        $this->db->limit($limit, ($page * $limit));

        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        # Throw exception if db error occur
        if (!$query = $this->db->get()) {               
            $db_error = $this->db->error();
            throw new Exception('Something went wrong!');
        }

        // Get total rows inserted count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count')->row()->count;
        // If limit 0 return empty
        if ($limit == 0) {
            return array('count' => $dt_filtered_total, 'data' => array(), 'status' => false, 'error' => 'Pagination divide by zero');
        }

        // Get the count per page and total page
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        // Get the query result
        $result = $query->result();

        if (!empty($result)) {
            foreach ($result as $val) {

                $doc_type_id = $val->doc_type_id;

                if(!empty($job_id)) {
                    // Get mandatory document
                    $ref_row = $this->db->query('SELECT is_required from tbl_recruitment_job_posted_docs where requirement_docs_id = '.$doc_type_id.'  And jobId = '.$job_id.' AND archive = 0')->row_array();
                }

                $is_mandatory = 'No';
                if (isset($ref_row) == true && isset($ref_row['is_required']) == true) {
                    $is_mandatory = $ref_row['is_required'] == 1 ? 'Yes' : 'No';
                }
                $val->is_mandatory = $is_mandatory;

                //Assign the file name to s3 file path
                $filename = $val->file_path;

                if(!$val->aws_uploaded_flag) {
                    $filename = $val->raw_name;
                }

                if ($val->entity_type == 1 && $val->aws_uploaded_flag == 0) {
                  if ($val->draft_contract_type == 1) {
                      $folder_url = $val->aws_uploaded_flag == 0 ? GROUP_INTERVIEW_CONTRACT_PATH : APPLICANT_ATTACHMENT_UPLOAD_PATH;
                      $val->file_path = base_url('mediaShow/rg/' . $val->applicant_id . '/' . urlencode(base64_encode($filename)));
                      $docPath = $folder_url . '/' . $filename;
                  } else if ($val->draft_contract_type == 2) {
                      if ($val->aws_uploaded_flag == 0) {
                        $folder_url = CABDAY_INTERVIEW_CONTRACT_PATH;
                        $val->file_path = base_url('mediaShow/rc/' . $val->applicant_id . '/' . urlencode(base64_encode($val->applicant_id . '/' .$filename)));
                      } else {
                        $folder_url = APPLICANT_ATTACHMENT_UPLOAD_PATH;
                        $val->file_path = base_url('mediaShow/rc/' . $val->applicant_id . '/' . urlencode(base64_encode($filename)));
                      }
                      $docPath = $folder_url . $val->applicant_id . '/' . $filename;
                  } else {
                      $val->file_path = base_url('mediaShow/r/' . $val->applicant_id . '/' . urlencode(base64_encode($filename)));
                      $docPath = APPLICANT_ATTACHMENT_UPLOAD_PATH . $val->applicant_id . '/' . $filename;
                  }
                  // Export with derived filename instead
                  // of goiing into trouble of renaming the file during upload process
                  //$download_as = $this->determine_filename_by_doc_category($val->attachment, $val->doc_category, $val->applicant_id);
                  $download_name =  $val->download_as;
                  $download_name = preg_replace('/\s+/', '_', $download_name);
                  $extension = pathinfo($val->raw_name, PATHINFO_EXTENSION);
                  $http_build_query = http_build_query(['download_as' => $download_name . '.' . $extension]);

                  if ($val->aws_uploaded_flag == 1) {
                    $http_build_query = http_build_query(['download_as' => $download_name . '.' . $extension, 's3' => 'true']);
                  }

                  $val->file_path .= '?' . $http_build_query;
                  $val->file_path = base_url('mediaShowView?tc='.$val->id.'&rd=').urlencode(base64_encode($val->file_path));
                }
            }
        }

        $this->db->select("COUNT(*) as count");
        $this->db->from("tbl_document_attachment as tda");
        $this->db->where("tda.archive", 0);
        $this->db->where("tda.draft_contract_type !=", 2);
        if(!empty($application_id)) {
            $this->db->where("tda.application_id", $this->db->escape_str($application_id, true));
        }
        if (!empty($applicant_id)) {
            $this->db->group_start();
            $this->db->where("tda.entity_type", $entityTypeApplicant);
            $this->db->where("tda.entity_id", $this->db->escape_str($applicant_id, true));
            $this->db->group_end();
        }
        // Get total rows inserted count
        $document_row = $this->db->get()->row_array();
        $document_count = intVal($document_row['count']);

        $res = $this->basic_model->get_record_where('recruitment_applicant_applied_application', ['is_document_marked', 'application_process_status'], ['applicant_id' => $applicant_id, 'id' => $application_id]);

        $app_pros_hired_status = false;
        $is_document_marked = false;
        if (isset($res) == true && isset($res[0]->is_document_marked) == true) {
            $is_document_marked = $res[0]->is_document_marked == 0 ? false : true;
            $app_pros_hired_status = $res[0]->application_process_status == 7 || $res[0]->application_process_status == 8 ? true : false;
        }
        //Sort Is Mandatory coloumn
        if($orderBy == 'is_mandatory') {
            json_decode(json_encode($result), TRUE);
            array_multisort(array_column($result, 'is_mandatory'), (!empty($sorted[0]->desc) && $sorted[0]->desc) ? SORT_ASC : SORT_DESC, $result);
            $result = json_decode(json_encode($result), FALSE);
        }

        return array('count' => $dt_filtered_total, 'document_count' => $document_count, 'data' => $result, 'status' => true, 'msg' => 'Fetch member document list successfully', 'is_document_marked' => $is_document_marked, 'app_pros_hired_status' => $app_pros_hired_status);
     }catch(\Exception $e){
            return array('status' => false, 'error' => 'Something went wrong');            
     }       
}

    /*
     * it is used for making sub query of document type name
     * return type sql
     */
    private function get_document_name_sub_query($tbl_alais) {
        $this->db->select("td.title");
        $this->db->from(TBL_PREFIX . 'document_type as td');
        $this->db->where("td.id = ".$tbl_alais.".doc_type_id", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for making sub query created by (who creator|updated of member)
     * return type sql
     */
    private function get_created_updated_by_sub_query($column_by, $tbl_alais) {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = ".$tbl_alais.".".$column_by, null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for making sub query of member name
     * return type sql
     */
    private function get_applicant_sub_query($tbl_alais, $column_alias) {
        $this->db->select("CONCAT_WS(' ', tra.firstname,tra.lastname)");
        $this->db->from(TBL_PREFIX . 'recruitment_applicant as tra');
        $this->db->where("tra.id = ".$tbl_alais.".".$column_alias, null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }
    /*
     * it is used for to generate the CAB certificate as well as send email to applicant
     */
    public function generate_and_mail_cab_day_certificate($reqData, $adminId) {
        require_once APPPATH . 'Classes/RenamingFileTemporary.php';
        require_once APPPATH . 'Classes/Automatic_email.php';
        $data = $reqData;
        $applicant_id = $reqData['applicant_id'];
        $application_id = $reqData['application_id'];
        $applicant_email = $reqData['applicant_email'];
        $applicant = [];

        // get applicant name by appicant id
        $this->db->select([ "concat_ws(' ',firstname,lastname) as applicant_name", "id as applicant_id","ra.firstname", "ra.lastname" ]);
        $this->db->from('tbl_recruitment_applicant as ra');
        $this->db->where('ra.id', $applicant_id);
        $query = $this->db->get();
        $applicant = $query->row_array();

        $applicant_name = '';
        $msg = 'CAB certificate generated successfully.';
        if (isset($applicant) == true && empty($applicant) == false) {
            $applicant_name = $applicant['applicant_name'];
        } else {
            $applicant['applicant_name'] = '';
            $applicant['applicant_id'] = 'temp';
            return ['status' => false, 'error' => 'Applicant not found.'];
        }

        // generate cab certificate id
        $cab_certificate_category = $this->Document_type_model->get_auto_generated_doc_by_title(CAB_CERTIFICATE_VALUE, 'id');

        // if null return empty
        if (!isset($cab_certificate_category)) {
            return ['status' => false, 'error' => 'CAB certificate not getting generate.'];
        }

        $task_details = [];
        $task_details['start_datetime'] = date('Y-m-d');
        $task_details = (object) $task_details;
        // geneate cab certificate
        $filename = $this->genrate_cab_certificate_who_passed_cab_day($applicant, $task_details);

        $pdfFilePath = (FCPATH . APPLICANT_ATTACHMENT_UPLOAD_PATH . '/' . $applicant['applicant_id'] . '/' . $filename);

        $filesize = filesize($pdfFilePath);

        $member_folder = S3_APPLICANT_ATTACHMENT_UPLOAD_PATH;
        $directory_name = $applicant['applicant_id'];
        $path_parts = pathinfo($filename);
        $filename_ext =  $path_parts['extension'];
        $filename_wo_ext =  $path_parts['filename'];
        $folder_key = $member_folder . $directory_name .'/'. $filename_wo_ext . '_'. time() . '.'. $filename_ext;


        // load amazon s3 library
        $this->load->library('AmazonS3');
        $this->load->library('S3Loges');

        /**
         * set dynamic values
         * $tmp_name should be - Uploaded file tmp storage path
         * $folder_key shoulb - Saving path with file name into s3 bucket
         *      - you can add a folder like `folder/folder/filename.ext`
         */
        $this->amazons3->setSourceFile($pdfFilePath);
        $this->amazons3->setFolderKey($folder_key);
        // s3 log
        $config['file_name'] = $filename;
        $config['raw_name'] = $filename;
        $config['file_size'] = $filesize;
        $config['file_path'] = $pdfFilePath;
        $config['file_ext'] = $filename_ext;

        $this->s3loges->setModuleId(1);
        $this->s3loges->setCreatedAt(DATE_TIME);
        $this->s3loges->setCreatedBy($adminId);
        $this->s3loges->setTitle('File Transfer Initiated for Add attachment against applicant '.$directory_name);
        $this->s3loges->setLogType('init');
        $this->s3loges->setDescription(json_encode($config));
        $this->s3loges->createS3Log();

        $amazons3_updload = $this->amazons3->uploadDocument();

        if ($amazons3_updload['status'] == 200) {
            // success
            $aws_uploaded_flag = 1;
            $aws_object_uri = '';
            $aws_file_version_id = '';
            if (isset($amazons3_updload['data']) == true && empty($amazons3_updload['data']) == false) {
                $data = $amazons3_updload['data'];
                $aws_object_uri = $data['ObjectURL'] ?? '';

                if ($aws_object_uri == '' && isset($data['@metadata']) == true && isset($data['@metadata']['effectiveUri']) == true) {
                    $aws_object_uri = $data['@metadata']['effectiveUri'] ?? '';
                }

                if ($aws_file_version_id == '' && isset($data['VersionId']) == TRUE) {
                  $aws_file_version_id = $data['VersionId'] ?? '';
                }
            }
            $aws_response = $amazons3_updload['data'];
            $this->s3loges->setTitle('Applicant '.$directory_name.' - '.$filename . ' - S3 File transfer Completed');
            $this->s3loges->setLogType('success');
            $this->s3loges->setDescription(json_encode($aws_response));
            $this->s3loges->createS3Log();


        } else {
            // failed
            $this->s3loges->setTitle('Applicant '.$directory_name.' - '.$filename . ' - S3 File transfer Completed with error!');
            $this->s3loges->setLogType('failure');
            $this->s3loges->setDescription(json_encode($aws_response));
            $this->s3loges->createS3Log();

            $aws_file_version_id = '';
            $aws_object_uri = '';
            $aws_response = $amazons3_updload['data'];
            $aws_uploaded_flag = 0;
        }

        // Set default status as valid
        $valid_status = 1;
        //attachment insert data
        $app_att['application_id'] = $application_id;
        $app_att['applicant_id'] = $applicant_id;
        $app_att['draft_contract_type'] = $doc_att['is_main_stage_label'] = $app_att['stage'] =
        $doc_att['uploaded_by_applicant'] = $doc_att['member_move_archive'] = 0;
        $app_att['related_to'] = 1;
        $app_att['doc_type_id'] = (isset($cab_certificate_category)) ? $cab_certificate_category->id : 0;
        $app_att['document_status'] = $valid_status;
        $app_att['archive'] = 0;
        $app_att['entity_id'] = $applicant_id;
        $app_att['entity_type'] = 1;
        $app_att['issue_date'] = DATE_TIME;
        $app_att['created_at'] = DATE_TIME;
        $app_att['created_by'] = $adminId;
        $app_att['updated_at'] = DATE_TIME;

        // insert data
        $this->db->insert(TBL_PREFIX . 'document_attachment', $app_att);

        //Get the last inserted id
        $insert_id = $this->db->insert_id();

        $doc_prop_att['doc_id'] = $insert_id;
        $doc_prop_att['file_name'] = 'CAB Certificate';
        $doc_prop_att['file_type'] = 'application/pdf';
        $doc_prop_att['file_ext'] = 'pdf';
        $doc_prop_att['file_size'] = $filesize;
        $doc_prop_att['raw_name'] = $filename;
        $doc_prop_att['aws_uploaded_flag'] = $aws_uploaded_flag;
        $doc_prop_att['aws_response'] = json_encode($aws_response);
        $doc_prop_att['aws_object_uri'] = $aws_object_uri;
        $doc_prop_att['aws_file_version_id'] = $aws_file_version_id;
        $doc_prop_att['file_path'] = $folder_key;
        $doc_prop_att['archive'] = 0;
        $doc_prop_att['created_at'] = DATE_TIME;
        $doc_prop_att['created_by'] = $adminId;
        $doc_prop_att['updated_at'] = DATE_TIME;

        $this->db->insert(TBL_PREFIX . 'document_attachment_property', $doc_prop_att, true);
        //To send the cab certificate email
        if($reqData['email_certificate']){
            // send certificate on mail
            $this->load->model("Recruitment_task_action");
            $user_data["job_title"] = $this->Recruitment_task_action->get_application_job_title($application_id);

            // get admin name
            $admin_name = $this->Recruitment_task_action->get_admin_firstname_lastname($adminId);

            $user_data['admin_firstname'] = $admin_name['firstname'] ?? '';
            $user_data['admin_lastname'] = $admin_name['lastname'] ?? '';
            $user_data['firstname'] = $applicant['firstname'];
            $user_data['lastname'] = $applicant['lastname'];
            $user_data['email'] = $applicant_email;
            $user_data['attach_file'] = APPLICANT_ATTACHMENT_UPLOAD_PATH . $applicant_id. '/' . $filename;
            $user_data['application_id'] = $application_id;

            // use for rename the filename according to document category and applicant name
            $objRen = new RenamingFileTemporary();
            $objRen->setFilename($user_data['attach_file']);
            $objRen->setRequired_filename("CAB Certificate_".$applicant['applicant_name']);
            $user_data['attach_file'] = $objRen->rename_file();

            $obj = new Automatic_email();

            $obj->setEmail_key("cab_certificate_applicant");
            $obj->setEmail($user_data['email']);
            $obj->setDynamic_data($user_data);
            $obj->setUserId($applicant_id);
            $obj->setAttach_file($user_data['attach_file']);
            $obj->setUser_type(1);
            $obj->setSend_by($adminId);

            $obj->automatic_email_send_to_user();

            // after send it to mail delete file
            $objRen->delete_temp();

            $msg = 'CAB certificate generated and email send successfully.';

        }
        //Delete file in App server if Appser upload is no
        if(getenv('IS_APPSERVER_UPLOAD') != 'yes') {
            unlink($pdfFilePath);
        }

        return ['status' => true, 'msg' => $msg];
    }

    /*
     * it is used for to generate bulk CAB certificate as well as send email to applicant
     */
    public function generate_bulk_mail_cab_day_certificate($reqData, $adminId) {
        require_once APPPATH . 'Classes/RenamingFileTemporary.php';
        require_once APPPATH . 'Classes/Automatic_email.php';
        $data = $reqData;
        $applications = $reqData['applications'];
        $is_generated = false;
        foreach($applications as $val) {
            $applicant_id = $val->applicant_id;
            $application_id = $val->application_id;
            $applicant_email = $val->applicant_email;
            $applicant = [];

            // get applicant name by appicant id
            $this->db->select([ "concat_ws(' ',firstname,lastname) as applicant_name", "id as applicant_id","ra.firstname", "ra.lastname" ]);
            $this->db->from('tbl_recruitment_applicant as ra');
            $this->db->where('ra.id', $applicant_id);
            $query = $this->db->get();
            $applicant = $query->row_array();

            $applicant_name = '';
            if(count($applications) > 1)
               $msg = 'CAB certificates  have been generated and Email sent successfully.';
            else
               $msg = 'CAB certificate  has been generated and Email sent successfully.';
            if (isset($applicant) == true && empty($applicant) == false) {
                $applicant_name = $applicant['applicant_name'];
            } else {
                $applicant['applicant_name'] = '';
                $applicant['applicant_id'] = 'temp';
                return ['status' => false, 'error' => 'Applicant not found.'];
            }

            // generate cab certificate id
            $cab_certificate_category = $this->Document_type_model->get_auto_generated_doc_by_title(CAB_CERTIFICATE_VALUE, 'id');
            // if null return empty
            if (!isset($cab_certificate_category)) {
                return ['status' => false, 'error' => 'CAB certificate not getting generate.'];
            }

            $task_details = [];
            $task_details['start_datetime'] = date('Y-m-d');
            $task_details = (object) $task_details;
            // geneate cab certificate
            $filename = $this->genrate_cab_certificate_who_passed_cab_day($applicant, $task_details);
            $pdfFilePath = (FCPATH . APPLICANT_ATTACHMENT_UPLOAD_PATH . '/' . $applicant['applicant_id'] . '/' . $filename);
            $filesize = filesize($pdfFilePath);

            $member_folder = S3_APPLICANT_ATTACHMENT_UPLOAD_PATH;
            $directory_name = $applicant['applicant_id'];
            $path_parts = pathinfo($filename);
            $filename_ext =  $path_parts['extension'];
            $filename_wo_ext =  $path_parts['filename'];
            $folder_key = $member_folder . $directory_name .'/'. $filename_wo_ext . '_'. time() . '.'. $filename_ext;


            // load amazon s3 library
            $this->load->library('AmazonS3');
            $this->load->library('S3Loges');

            /**
             * set dynamic values
             * $tmp_name should be - Uploaded file tmp storage path
             * $folder_key shoulb - Saving path with file name into s3 bucket
             *      - you can add a folder like `folder/folder/filename.ext`
             */
            $this->amazons3->setSourceFile($pdfFilePath);
            $this->amazons3->setFolderKey($folder_key);
            // s3 log
            $config['file_name'] = $filename;
            $config['raw_name'] = $filename;
            $config['file_size'] = $filesize;
            $config['file_path'] = $pdfFilePath;
            $config['file_ext'] = $filename_ext;

            $this->s3loges->setModuleId(1);
            $this->s3loges->setCreatedAt(DATE_TIME);
            $this->s3loges->setCreatedBy($adminId);
            $this->s3loges->setTitle('File Transfer Initiated for Add attachment against applicant '.$directory_name);
            $this->s3loges->setLogType('init');
            $this->s3loges->setDescription(json_encode($config));
            $this->s3loges->createS3Log();

            $amazons3_updload = $this->amazons3->uploadDocument();
            if ($amazons3_updload['status'] == 200) {
                // success
                $aws_uploaded_flag = 1;
                $aws_object_uri = '';
                $aws_file_version_id = '';
                if (isset($amazons3_updload['data']) == true && empty($amazons3_updload['data']) == false) {
                    $data = $amazons3_updload['data'];
                    $aws_object_uri = $data['ObjectURL'] ?? '';

                    if ($aws_object_uri == '' && isset($data['@metadata']) == true && isset($data['@metadata']['effectiveUri']) == true) {
                        $aws_object_uri = $data['@metadata']['effectiveUri'] ?? '';
                    }

                    if ($aws_file_version_id == '' && isset($data['VersionId']) == TRUE) {
                    $aws_file_version_id = $data['VersionId'] ?? '';
                    }
                }
                $aws_response = $amazons3_updload['data'];
                $this->s3loges->setTitle('Applicant '.$directory_name.' - '.$filename . ' - S3 File transfer Completed');
                $this->s3loges->setLogType('success');
                $this->s3loges->setDescription(json_encode($aws_response));
                $this->s3loges->createS3Log();
            } else {
                // failed
                $this->s3loges->setTitle('Applicant '.$directory_name.' - '.$filename . ' - S3 File transfer Completed with error!');
                $this->s3loges->setLogType('failure');
                $this->s3loges->setDescription(json_encode($aws_response));
                $this->s3loges->createS3Log();

                $aws_file_version_id = '';
                $aws_object_uri = '';
                $aws_response = $amazons3_updload['data'];
                $aws_uploaded_flag = 0;
            }

            // Set default status as valid
            $valid_status = 1;
            //attachment insert data
            $app_att['application_id'] = $application_id;
            $app_att['applicant_id'] = $applicant_id;
            $app_att['draft_contract_type'] = $doc_att['is_main_stage_label'] = $app_att['stage'] =
            $doc_att['uploaded_by_applicant'] = $doc_att['member_move_archive'] = 0;
            $app_att['related_to'] = 1;
            $app_att['doc_type_id'] = (isset($cab_certificate_category)) ? $cab_certificate_category->id : 0;
            $app_att['document_status'] = $valid_status;
            $app_att['archive'] = 0;
            $app_att['entity_id'] = $applicant_id;
            $app_att['entity_type'] = 1;
            $app_att['issue_date'] = DATE_TIME;
            $app_att['created_at'] = DATE_TIME;
            $app_att['created_by'] = $adminId;
            $app_att['updated_at'] = DATE_TIME;

            // insert data
            $this->db->insert(TBL_PREFIX . 'document_attachment', $app_att);

            //Get the last inserted id
            $insert_id = $this->db->insert_id();

            $doc_prop_att['doc_id'] = $insert_id;
            $doc_prop_att['file_name'] = 'CAB Certificate';
            $doc_prop_att['file_type'] = 'application/pdf';
            $doc_prop_att['file_ext'] = 'pdf';
            $doc_prop_att['file_size'] = $filesize;
            $doc_prop_att['raw_name'] = $filename;
            $doc_prop_att['aws_uploaded_flag'] = $aws_uploaded_flag;
            $doc_prop_att['aws_response'] = json_encode($aws_response);
            $doc_prop_att['aws_object_uri'] = $aws_object_uri;
            $doc_prop_att['aws_file_version_id'] = $aws_file_version_id;
            $doc_prop_att['file_path'] = $folder_key;
            $doc_prop_att['archive'] = 0;
            $doc_prop_att['created_at'] = DATE_TIME;
            $doc_prop_att['created_by'] = $adminId;
            $doc_prop_att['updated_at'] = DATE_TIME;

            $this->db->insert(TBL_PREFIX . 'document_attachment_property', $doc_prop_att, true);
            //To send the cab certificate email
            $user_data = [];
            if($reqData['email_certificate']){
                // send certificate on mail
                $this->load->model("Recruitment_task_action");
                $user_data["job_title"] = $this->Recruitment_task_action->get_application_job_title($application_id);

                // get admin name
                $admin_name = $this->Recruitment_task_action->get_admin_firstname_lastname($adminId);

                $user_data['admin_firstname'] = $admin_name['firstname'] ?? '';
                $user_data['admin_lastname'] = $admin_name['lastname'] ?? '';
                $user_data['firstname'] = $applicant['firstname'];
                $user_data['lastname'] = $applicant['lastname'];
                $user_data['email'] = $applicant_email;
                $user_data['attach_file'] = APPLICANT_ATTACHMENT_UPLOAD_PATH . $applicant_id. '/' . $filename;
                $user_data['application_id'] = $application_id;

                // use for rename the filename according to document category and applicant name
                $objRen = new RenamingFileTemporary();
                $objRen->setFilename($user_data['attach_file']);
                $objRen->setRequired_filename("CAB Certificate_".$applicant['applicant_name']);
                $user_data['attach_file'] = $objRen->rename_file();

                $obj = new Automatic_email();

                $obj->setEmail_key("cab_certificate_applicant");
                $obj->setEmail($user_data['email']);
                $obj->setDynamic_data($user_data);
                $obj->setUserId($applicant_id);
                $obj->setAttach_file($user_data['attach_file']);
                $obj->setUser_type(1);
                $obj->setSend_by($adminId);

                $obj->automatic_email_send_to_user();

                // after send it to mail delete file
                $objRen->delete_temp();
                if(count($applications) > 1)
                     $msg = 'CAB certificates  have been generated and Email sent successfully.';
                else
                     $msg = 'CAB certificate  has been generated and Email sent successfully.';

            }
            //Delete file in App server if Appser upload is no
            if(getenv('IS_APPSERVER_UPLOAD') != 'yes') {
                unlink($pdfFilePath);
            }
            $is_generated = true;
        }
        if ($is_generated) {
            return ['status' => true, 'msg' => $msg];
            exit();
        } else {
            return ['status' => false, 'error' => 'Error in certificate generation'];
            exit();
        }
    }


    function genrate_cab_certificate_who_passed_cab_day($applicant_details, $task_details) {
        $pdf_data = ["applicant_name" => $applicant_details['applicant_name'], 'task_time' => $task_details->start_datetime];
        $html = $this->load->view("cab_certificate_pdf", $pdf_data, true);

        require_once APPPATH . 'third_party/mpdf7/vendor/autoload.php';

         $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/custom/font/directory',
            ]),
            'fontdata' => $fontData + [
        'gotham_light' => [
            'R' => 'GothamRnd-Light.ttf',
            'I' => 'GothamRnd-Light.ttf',

        ],
        'gotham_medium' => [
            'R' => 'GothamRnd-Medium.ttf',
            'I' => 'GothamRnd-Medium.ttf',

        ],
        'gotham_book' => [
            'R' => 'GothamRnd-Book.ttf',
            'I' => 'GothamRnd-Book.ttf',

        ]
            ],
            // 'default_font' => 'frutiger'
        ]);


        $mpdf->WriteHTML($html);
        $filename = "CAB_Certificate_" . rand(100000, 999999) . '.pdf';
        $pdfFilePath = APPLICANT_ATTACHMENT_UPLOAD_PATH . '/' . $applicant_details['applicant_id'] . '/' . $filename;
        create_directory(APPLICANT_ATTACHMENT_UPLOAD_PATH . '/' . $applicant_details['applicant_id']);

        $mpdf->Output($pdfFilePath, "F");



        return $filename;
    }

     // create member if the applicant hired
     function create_member_for_hired_applicant($reqData, $adminId) {
        // Note: Any vars in all UPPERCASE letters are for tbl_recruitment_applicant_applied_application.status

        #This condition will call when admin create member from applicant
            require_once APPPATH . 'Classes/recruitment/ApplicantCreateAsMemberToHCM.php';
            $applicantMoveObj = new ApplicantCreateAsMemberToHCM();
            $applicantMoveObj->setUser_type('external_staff');
            $applicantMoveObj->setAdmin_User_type('0');
            $applicantMoveObj->setApplicant_id($reqData->applicant_id);
            $applicantMoveObj->setMemberStatus($reqData->member_status);

            if (isset($reqData->application_id) && !empty($reqData->application_id)) {
                $applicantMoveObj->setApplicationId((int) $reqData->application_id);
            }

            $responseData = $applicantMoveObj->create_applicant_as_member();
            $hired_as = 1;
            $this->create_log_on_final_stage($reqData, $adminId,2);
            $this->basic_model->update_records('recruitment_applicant', ['current_stage' => 0, 'hired_as' => $hired_as, 'hired_date' => DATE_TIME], ['id' => $reqData->applicant_id]);
            if($responseData['status']){
                return ['status' => true, 'data' => $responseData];
            }else{
                return ['status' => false];
            }

    }

    // create member for bulk - hired applicant only
    function create_member_for_bulk_hired_applicant($reqData,$member_status, $adminId) {
        // Note: Any vars in all UPPERCASE letters are for tbl_recruitment_applicant_applied_application.status

        #This condition will call when admin create member from applicant
            require_once APPPATH . 'Classes/recruitment/ApplicantCreateAsMemberToHCM.php';
            $applicant_res = [];
            foreach ($reqData as $app) {
                if($app->applicant_id && $app->application_id){
                    $applicantMoveObj = new ApplicantCreateAsMemberToHCM();
                    $applicantMoveObj->setUser_type('external_staff');
                    $applicantMoveObj->setAdmin_User_type('0');
                    $applicantMoveObj->setApplicant_id($app->applicant_id);
                    $applicantMoveObj->setMemberStatus($member_status);

                    if (isset($app->application_id) && !empty($app->application_id)) {
                        $applicantMoveObj->setApplicationId((int) $app->application_id);
                    }

                    $app_check = $applicantMoveObj->check_applicant_already_active_in_hcm();
                    // if get active member then return error as applicant already exist
                    if(empty($app_check)){
                        $applicant_res[]= true;
                        $applicantMoveObj->create_applicant_as_member();
                        $hired_as = 1;
                        $this->create_log_on_final_stage($app, $adminId,2);
                        $this->basic_model->update_records('recruitment_applicant', ['current_stage' => 0, 'hired_as' => $hired_as, 'hired_date' => DATE_TIME], ['id' => $app->applicant_id]);

                        // create log
                        $this->loges->setCreatedBy($adminId);
                        $this->loges->setUserId($app->applicant_id);
                        $this->loges->setDescription(json_encode($app));

                        $applicantName = $this->username->getName('applicant', $app->applicant_id);
                        $txt = 'Successfully';
                        $this->loges->setTitle('Applicant - ' . $applicantName . ' Created as member');  // set title in log
                        $this->loges->setSpecific_title("Converted as member" . ' ' . $txt);
                        $this->loges->createLog(); // create log
                    }
                }else{
                    return ['status' => false];
                }
            }

            return $applicant_res;

    }

    /**
     * Mark as Reference verfied
     * @param {int} reference_id
     */
    function update_document_marked($applicant_id, $application_id, $status, $adminId) {
        // update member documents
        $upd_data["updated"] = DATE_TIME;
        $upd_data["is_document_marked"] = $status;
        $result = $this->basic_model->update_records("recruitment_applicant_applied_application", $upd_data, [ "applicant_id" => $applicant_id, "id" => $application_id]);

        return $result;
    }

    //Check application process status
    function check_application_process_status($application_id, $applicant_id, $status) {
        if($application_id && $applicant_id && $status) {

            $data = $this->basic_model->get_row('recruitment_applicant_applied_application',
                ['application_process_status'], ['id' => $application_id,
                'applicant_id' => $applicant_id, 'application_process_status' => $status]);

            if ($data) {
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * fetches list of applications of applicants
     */
    public function get_applicants_for_group_booking($reqData, $adminId, $filter_condition = '') {
        $limit = $reqData->pageSize ?? 9999;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';
        $start_date = $filter->start_date ?? '';
        $end_date = $filter->end_date ?? '';
        $start_date = is_null($start_date) ? '' : $start_date;
        $end_date = is_null($end_date) ? '' : $end_date;
        $its_recruiter_admin = check_its_recruiter_admin($adminId);
        $application_id = $reqData->application_id ?? '';
        $src_columns = array('raa.id','concat(p.firstname," ",p.lastname) as FullName', 'pe.email', 'rjp.title', 'ra.hired_as');
        $quick_filter = $reqData->quick_filter ?? '';
        
        $available_columns = array(
            "applicant_id", "created", "id","FullName","status", "email", "phone", "appId","jobId", "stage", "sub_stage", "referrer_url", "channelId",
            "job_position", "application_id", "recruiter" ,"application_process_status",   "hired_as"
        );
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_columns)) {
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
        # new lightening filters
        // if(isset($filter->filters)) {
        //     foreach($filter->filters as $filter_obj) {
        //         if(empty($filter_obj->select_filter_value)) continue;

        //         $sql_cond_part = GetSQLCondPartFromSymbol($filter_obj->select_filter_operator_sym, $filter_obj->select_filter_value);
        //         if($filter_obj->select_filter_field == "fullname") {
        //             $this->db->where('concat(p.firstname," ",p.lastname) '.$sql_cond_part);
        //         }
        //         if($filter_obj->select_filter_field == "job_position") {
        //             $this->db->where('rjp.title '.$sql_cond_part);
        //         }
        //         if($filter_obj->select_filter_field == "stage") {
        //             $this->db->where('raa.current_stage in  ('.$filter_obj->select_filter_value.') and raa.status not in (2,3)');
        //         }
        //         if($filter_obj->select_filter_field == "applied_through") {
        //             $this->db->where('raa.referrer_url '.$sql_cond_part);
        //         }
        //         if($filter_obj->select_filter_field == "recruiter") {
        //             $this->db->where('raa.recruiter '.$sql_cond_part);
        //         }
        //         if($filter_obj->select_filter_field == "status_label") {
        //             $this->db->where('raa.status '.$sql_cond_part);
        //         }
        //         if($filter_obj->select_filter_field == "created") {
        //             $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") '.GetSQLOperator($filter_obj->select_filter_operator_sym), DateFormate($filter_obj->select_filter_value, 'Y-m-d'));
        //         }
        //     }
        // }

        // quick_filter
        if (isset($quick_filter) == true && empty($quick_filter) == false) {
            if (isset($quick_filter->applicant) == true && $quick_filter->applicant != '') {
                $this->db->like('concat(p.firstname," ",p.lastname)', $quick_filter->applicant);
            }
            if (isset($quick_filter->recruitor) == true && $quick_filter->recruitor != '') {
                $this->db->like("(select concat(m.firstname, ' ', m.lastname) as recruiter_name from tbl_member as m inner join tbl_department as d on d.id = m.department AND d.short_code = 'internal_staff' where m.id = raa.recruiter)", $quick_filter->recruitor);
            }
            if (isset($quick_filter->stage) == true && $quick_filter->stage != '') {
                if ($quick_filter->stage != '-1') {
                    $this->db->where('raa.current_stage in ('.$quick_filter->stage.')');
                } else {
                    $status = [2,3];
                    $this->db->where_in('raa.status', $status);
                }
            }
            if (isset($quick_filter->status) == true && $quick_filter->status != '' && empty($quick_filter->status) == false) {
                $status = $quick_filter->status;
                $this->db->where_in('raa.application_process_status', $status);
            }
        }

        # previous search where we had recruiters filter
        if (!empty($filter->recruiter_val)) {
            $this->db->where('raa.recruiter', $filter->recruiter_val);
        }

        # previous search where we had status and current stage filters
        if (!empty($filter->filter_val)) {
            if($filter->filter_val == -1)
                $this->db->where('raa.status in (2,3)');
            else
                $this->db->where('raa.current_stage in  ('.$filter->filter_val.') and raa.status not in (2,3)');
        }

        # previous search where we had start and end date
        if (!empty($start_date) && empty($end_date)) {
            $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") >= ', DateFormate($start_date, 'Y-m-d'));
        } elseif (!empty($start_date) && !empty($end_date)) {
            $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") >= ', DateFormate($start_date, 'Y-m-d'));
            $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") <= ', DateFormate($end_date, 'Y-m-d'));
        } elseif (!empty($end_date)) {
            $this->db->where('DATE_FORMAT(raa.created, "%Y-%m-%d") <= ', DateFormate($end_date, 'Y-m-d'));
        }

        if (!empty($filter->jobId)) {
            $this->db->where('raa.jobId', $filter->jobId);
        }

        # text search
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                $formated_date = '';
                if($column_search=='ra.hired_as' && ($filter->search=='Yes' || $filter->search=='No')){
                    $formated_date = $filter->search=='Yes' || $filter->search=='yes' ? '1' : '0';
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $formated_date);
                        
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $formated_date);
                    }
                }else{
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        $searchKey = trim($filter->search);
                        $searchKeyArray = preg_split('/\s+/', $searchKey);
                        $searchKey = implode(" ", $searchKeyArray);
                        
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $searchKey);
                        
                        /*
                            $searchKey = trim($filter->search);
                            $searchKeyArray = preg_split('/\s+/', $searchKey);
                            if(count($searchKeyArray) == 1) {
                                $this->db->or_like("m.firstname", trim($searchKeyArray[0]));
                                $this->db->or_like("m.lastname", trim($searchKeyArray[0]));
                            } else if(count($searchKeyArray) == 2){
                                $this->db->or_like("m.firstname", trim($searchKeyArray[0]));
                                $this->db->or_like("m.lastname", trim($searchKeyArray[0]));
                                $this->db->or_like("m.firstname", trim($searchKeyArray[1]));
                                $this->db->or_like("m.lastname", trim($searchKeyArray[1]));
                            } else {
                                $this->db->or_like($serch_column[0], $searchKey);
                            }
                        */
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $filter->search);
                    }
                }

            }
            $this->db->group_end();
        }

        $this->load->file(APPPATH.'Classes/common/ViewedLog.php');
        $viewedLog = new ViewedLog();
        // get entity type value
        $entity_type = $viewedLog->getEntityTypeValue('application');

        $select_column = array(
            'ra.id as applicant_id', 'raa.created', 'raa.id','concat(p.firstname," ",p.lastname) as FullName',
            'raa.status', 'pe.email', 'pp.phone', 'ra.appId',
            'raa.jobId', "(concat(rtb.title)) as stage", "(concat(rs.stage,' - ',rs.title)) as sub_stage", "raa.referrer_url", "raa.channelId",
            "rjp.title as job_position",
            'raa.id AS application_id, raa.recruiter,raa.application_process_status',
            'ra.hired_as'
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
        if ($application_id != '') {
            $this->db->where('raa.jobId', $application_id);
        }
        $this->db->where('ra.archive', 0);
        $this->db->order_by($orderBy, $direction);
        $this->db->group_by('raa.id');
        $this->db->limit($limit, ($page * $limit));
        //list view filter condition
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();

        if (!empty($result)) {
            //fetch viewed by data
            $vlogs = [];
            $application_ids = array_map(function($item){
                return $item->id;
            }, $result);
            if (!empty($application_ids)) {
                $this->db->select(['vl.entity_id', "concat(m.firstname,' ',m.lastname) as viewed_by", 'vl.viewed_date', 'vl.viewed_by as viewed_by_id']);
                $this->db->from('tbl_viewed_log as vl');
                $this->db->join('tbl_member as m', 'vl.viewed_by=m.uuid and m.archive=0');
                $this->db->where_in('vl.entity_id', $application_ids);
                $query2 = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
                $result2 = $query2->result();
                
                foreach($result2 as $v) {
                    $vlogs[$v->entity_id] = $v;
                }
            }
            foreach ($result as $val) {
                if ( array_key_exists($val->id, $vlogs) ) {
                    $val->viewed_by_id = $vlogs[$val->id]->viewed_by_id;
                    $val->viewed_by = $vlogs[$val->id]->viewed_by;
                    $val->viewed_date = $vlogs[$val->id]->viewed_date;
                }
                if ($val->channelId == 2 && $val->referrer_url != '')
                    $val->applied_through = !empty($val->referrer_url) ? getDomain($val->referrer_url) : 'N/A';
                else
                    $val->applied_through = (isset($val->channelId) && $val->channelId == 1 ? 'Seek' : (($val->channelId == 2) ? 'Website' : (($val->channelId == 3) ? 'HCM Software' : 'N/A')));
            }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'total_item' => $total_item);
        return $return;
    }

    /**
 * when logging in from ipad, we need to fetch the task_id, interview_type_id and task_applicant_id
 * for it to work properly
 */
public function get_next_interview_details($applicant_id) {
    $tbl_rec_app_group_cab_int_detail=TBL_PREFIX.'recruitment_applicant_group_or_cab_interview_detail as interview';       
    $tbl_rec_task=TBL_PREFIX.'recruitment_task as task';
    $tbl_rec_task_app=TBL_PREFIX.'recruitment_task_applicant as rta';
    $tbl_rec_app=TBL_PREFIX.'recruitment_applicant as ra';
    $tbl_rec_app_email=TBL_PREFIX.'recruitment_applicant_email as rae';
    $tbl_rec_interview_type=TBL_PREFIX.'recruitment_interview_type as rit';

    $coulumns_rec_task=array('task.id as task_id','task.task_name','rta.id as task_applicant_id','rta.applicant_id','interview.interview_type as interview_type_id','rit.name as interview_type','interview.device_pin','task.start_datetime as task_start_time','interview.ipad_last_stage as ipad_last_stage','task.status as task_complete','ra.person_id', '0 as is_signed', '0 as contract_file_id', '\'Australia/Melbourne\' as time_zone');
    $query = $this->db->select($coulumns_rec_task);
    $this->db->from($tbl_rec_task);
    $this->db->join($tbl_rec_task_app,'rta.taskId = task.id  and rta.status=1', 'inner'); // and task.status=1
    $this->db->join($tbl_rec_app_group_cab_int_detail, 'interview.recruitment_task_applicant_id = rta.id and interview.archive=0 and interview.quiz_submit_status = 0 and interview.mark_as_no_show=0', 'inner');
    $this->db->join($tbl_rec_app, 'ra.id = rta.applicant_id and ra.archive=0', 'inner');
    $this->db->join($tbl_rec_app_email,'rae.applicant_id = ra.id and rae.archive=0 and rae.primary_email=1', 'inner');
    $this->db->join($tbl_rec_interview_type,'rit.id = interview.interview_type', 'inner');
    $this->db->where("rta.applicant_id =",$applicant_id);
    $this->db->where("task.end_datetime > CURRENT_TIMESTAMP()");
    $this->db->order_by('task.start_datetime','ASC');

    $this->db->limit(1);
    $res = $this->db->get();
    $res_array=$res->row_array();
    if($res->num_rows()>0){
        return $res->row_array();  
    }
    return [];
}
//check the applicant is flagged
function check_the_applicant_is_flagged($applicant_id) {
    $this->db->select('applicant_id');
    $this->db->from('tbl_recruitment_flag_applicant');

    $this->db->where(['applicant_id' => $applicant_id, 'archive' => 0, 'flag_status'=>2]);

    $query = $this->db->get();
    return $query->result();
 }

 /**
 * Determine oa status label based on given string.
 * If could not be determined, this will return empty string.
 *
 *
 * @param string $oa_status
 * @return string
 */
  function oa_status_label($oa_status) {
        $oa_status_val = '';
        switch ($oa_status) {
            case 'Sent':
                $oa_status_val = '1';
                break;
            case 'In progress':
                $oa_status_val = '2';
                break;
            case 'Submitted':
                $oa_status_val = '3';
                break;
            case 'Completed':
                $oa_status_val = '4';
                break;
            case 'Link Expired':
                $oa_status_val = '5';
                break;
            case 'Error':
                $oa_status_val = '6';
                break;
            case 'Moodle':
                $oa_status_val = '7';
                break;
            default:
                $oa_status_val = '';
                break;
        }

        return $oa_status_val;
    }

}
