<?php

class Fms_model extends CI_Model {

    public $against_type = [ 'aga_hcm_member' => 1, 'aga_hcm_participant' => 2, 'aga_hcm_user_admin' => 3, 'aga_hcm_organisation' => 4, 'aga_hcm_site' => 5, 'aga_member_of_public' => 6, 'aga_hcm_general' => 7];

    public $fms_feedback_status = [
        "0" => "New",
        "1" => "In progress",
        "2" => "Investigation",
        "3" => "Closed",
        "4" => "Completed",
    ];

    public function get_participant_name_d($post_data) {
        $this->db->or_where("(MATCH (firstname) AGAINST ('$post_data *'))", NULL, FALSE);
        $this->db->or_where("(MATCH (middlename) AGAINST ('$post_data *'))", NULL, FALSE);
        $this->db->or_where("(MATCH (lastname) AGAINST ('$post_data *'))", NULL, FALSE);
        $this->db->where('archive =', 0);
        $this->db->where('status=', 1);
        $this->db->select("CONCAT(firstname,' ',middlename,' ',lastname) as participantName");
        $this->db->select('firstname, id');
        $query = $this->db->get(TBL_PREFIX . 'participant');
        $query->result();
        $participant_rows = array();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $participant_rows[] = array('label' => $val->participantName, 'value' => $val->id);
            }
        }
        return $participant_rows;
    }

    public function get_member_name_d($post_data) {

        $this->db->or_where("(MATCH (firstname) AGAINST ('$post_data *'))", NULL, FALSE);
        $this->db->or_where("(MATCH (middlename) AGAINST ('$post_data *'))", NULL, FALSE);
        $this->db->or_where("(MATCH (lastname) AGAINST ('$post_data *'))", NULL, FALSE);
        $this->db->where('archive =', 0, FALSE);
        $this->db->where('status=', 1, FALSE);
        $this->db->select("CONCAT(firstname,' ',middlename,' ',lastname) as memberName");
        $this->db->select(array('id'));
        $query = $this->db->get(TBL_PREFIX . 'member');
        //last_query();
        $query->result();
        $participant_rows = array();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $participant_rows[] = array('label' => $val->memberName, 'value' => $val->id);
            }
        }
        return $participant_rows;
    }

    public function get_site_name_d($post_data) {
        $this->db->like('site_name', $post_data, 'both');
        $this->db->where('archive =', 0);
        $this->db->select('site_name, id');
        $query = $this->db->get(TBL_PREFIX . 'organisation_site');
        $query->result();
        $participant_rows = array();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $participant_rows[] = array('label' => $val->site_name, 'value' => $val->id);
            }
        }
        return $participant_rows;
    }

    public function create_case($reqData) {
        $responseAry = $reqData->data;
        #pr($responseAry);
        $case_ary = array('event_date' => $responseAry->event_date,
            'initiated_by' => isset($responseAry->initiator_detail->value) ? $responseAry->initiator_detail->value : '',
            'initiated_type' => $responseAry->initiatorCategory,
            'created' => DATE_TIME,
            'Initiator_first_name' => $responseAry->initiator_first_name,
            'Initiator_last_name' => $responseAry->initiator_last_name,
            'Initiator_email' => $responseAry->initiator_email,
            'Initiator_phone' => $responseAry->initiator_phone,
            'status' => 0,
        );




        $case_id = $this->Basic_model->insert_records('fms_case', $case_ary, $multiple = FALSE);
        $this->Basic_model->insert_records('fms_case_category', array('categoryId' => $responseAry->CaseCategory, 'caseId' => $case_id), $multiple = FALSE);

        $shift_note = array('title' => $responseAry->notes_title, 'caseId' => $case_id, 'description' => $responseAry->notes, 'created_by' => $reqData->adminId, 'created' => DATE_TIME);
        $shift_caller_id = $this->Basic_model->insert_records('fms_case_notes', $shift_note, $multiple = FALSE);

        $case_reason = array('caseId' => $case_id,
            'title' => $responseAry->title,
            'description' => $responseAry->description,
            'created_by' => $reqData->adminId,
            'created_type' => 2, //admin
            'created' => DATE_TIME,
        );

        $fms_case_reason_id = $this->Basic_model->insert_records('fms_case_reason', $case_reason, $multiple = FALSE);

        $location = $responseAry->completeAddress;

        if (!empty($location)) {
            foreach ($location as $key => $addr) {
                $case_location[] = array('caseId' => $case_id,
                    'address' => $addr->street,
                    'suburb' => $addr->suburb,
                    'postal' => $addr->postal,
                    'state' => $addr->state,
                    'categoryId' => $addr->address_category,
                );
            }
            if (!empty($case_location))
                $this->Basic_model->insert_records('fms_case_location', $case_location, $multiple = TRUE);
        }
        #last_query();
        #pr($responseAry->againstDetail);die;
        if (!empty($responseAry->againstDetail)) {
            $case_againsts = array();
            foreach ($responseAry->againstDetail as $key => $val) {
                if (isset($val->againstCategory) && ($val->againstCategory == 1 || $val->againstCategory == 4)) {
                    $case_againsts[] = array('caseId' => $case_id, 'against_category' => '1', 'against_first_name' => $val->firstName, 'against_last_name' => $val->lastName, 'against_email' => isset($val->againstEmail) ? $val->againstEmail : '', 'against_phone' => isset($val->againstPhone) ? $val->againstPhone : '', 'created' => DATE_TIME, 'against_by' => '0'); //member of public
                } else if (isset($val->againstCategory) && $val->againstCategory == 3) {
                    $case_againsts[] = array('caseId' => $case_id, 'against_category' => $val->againstCategory, 'against_by' => $val->againstOnCallParticipant->value, 'created' => DATE_TIME, 'against_first_name' => null, 'against_last_name' => null, 'against_email' => null, 'against_phone' => null);
                } else if (isset($val->againstCategory) && $val->againstCategory == 2) {
                    $case_againsts[] = array('caseId' => $case_id, 'against_category' => $val->againstCategory, 'against_by' => $val->againstOnCallMember->value, 'created' => DATE_TIME, 'against_first_name' => null, 'against_last_name' => null, 'against_email' => null, 'against_phone' => null);
                } else if (isset($val->againstCategory) && $val->againstCategory == 5) {
                    $case_againsts[] = array('caseId' => $case_id, 'against_category' => $val->againstCategory, 'against_by' => $val->againstOnCallUserAdmin->value, 'created' => DATE_TIME, 'against_first_name' => null, 'against_last_name' => null, 'against_email' => null, 'against_phone' => null);
                } else if (isset($val->againstCategory) && $val->againstCategory == 6) {
                    $case_againsts[] = array('caseId' => $case_id, 'against_category' => $val->againstCategory, 'against_by' => $val->againstOnCallOrg->value, 'created' => DATE_TIME, 'against_first_name' => null, 'against_last_name' => null, 'against_email' => null, 'against_phone' => null);
                }else if (isset($val->againstCategory) && $val->againstCategory == 7) {
                    $case_againsts[] = array('caseId' => $case_id, 'against_category' => $val->againstCategory, 'against_by' => $val->againstOnCallSite->value, 'created' => DATE_TIME, 'against_first_name' => null, 'against_last_name' => null, 'against_email' => null, 'against_phone' => null);
                }
            }
            #pr($case_againsts);
            if (!empty($case_againsts))
                $shift_requirement_id = $this->Basic_model->insert_records('fms_case_against_detail', $case_againsts, $multiple = TRUE);
        }

        if (!empty($case_id)) {
            return array('status' => true, 'case_id' => $case_id);
        } else {
            return array('status' => false);
        }
    }

    public function get_fms_cases($reqData) {
        $limit = $reqData->pageSize ?? 20;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? '';
        $filter = $reqData->filtered ?? '';
        $orderBy = '';
        $direction = '';
        $tbl_fms = TBL_PREFIX . 'fms_case';

        #Search for a FMS based on: FMSID#, OCSID#, Name-Participant, Name-Member, Name-Org, Name-Sub-Org.
        $available_column = array("id","fms_type", "description", "initiated_type", "Initiator_first_name",  "Initiator_last_name", 
                                  "initiated_by", "created", "status", "categoryId", "case_category", "event_date");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = $tbl_fms . '.id';
            $direction = 'DESC';
        }

        if (isset($filter->caseType))
            $this->db->where('tbl_fms_case.fms_type =', $filter->caseType);

        if (isset($filter->status))
            $this->db->where_in('tbl_fms_case.status', $filter->status);

        if (!empty($filter->srch_box)) {
            $this->db->group_start();
            $src_columns = array($tbl_fms . ".id as fmsid", $tbl_fms . ".Initiator_first_name", $tbl_fms . ".Initiator_last_name", $tbl_fms . ".initiated_by", "CONCAT(tbl_member.firstname,' ',tbl_member.middlename,' ',tbl_member.lastname)", "CONCAT(tbl_member.firstname,' ',tbl_member.lastname)", "CONCAT(tbl_participant.firstname,' ',tbl_participant.middlename,' ',tbl_participant.lastname)", "CONCAT(tbl_participant.firstname,' ',tbl_participant.lastname)", "tbl_member.id as member_ocs_id", "tbl_participant.id as participant_ocs_id");

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


        $select_column = array($tbl_fms . ".id", $tbl_fms . ".fms_type", "tbl_fms_case_reason.description", $tbl_fms . ".initiated_type", $tbl_fms . ".Initiator_first_name", $tbl_fms . ".Initiator_last_name", $tbl_fms . ".initiated_by", $tbl_fms . ".created", $tbl_fms . ".status", "tbl_fms_case_category.categoryId", "tbl_fms_case_all_category.name as case_category", "DATE_FORMAT(tbl_fms_case.event_date,'%d/%m/%Y') as event_date");

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from($tbl_fms);
        $this->db->join('tbl_fms_case_category', 'tbl_fms_case_category.caseId = ' . $tbl_fms . '.id', 'inner');
        $this->db->join('tbl_fms_case_all_category', 'tbl_fms_case_all_category.id = tbl_fms_case_category.categoryId', 'inner');

        if (!empty($filter->srch_box)) {
            $this->db->join('tbl_participant', 'tbl_participant.id =  tbl_fms_case.initiated_by AND tbl_fms_case.initiated_type = 2', 'left');
            $this->db->join('tbl_member', 'tbl_member.id =  tbl_fms_case.initiated_by AND tbl_fms_case.initiated_type = 1', 'left');
        }

        $this->db->group_by('tbl_fms_case.id');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
//        last_query();
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $val->created = $val->created;
                $val->category = $val->case_category;
                $val->event_date = ($val->event_date != '00/00/0000') ? $val->event_date : '';

                if ($val->initiated_type == 5 || $val->initiated_type == 6) {
                    $val->initiated_by = $val->Initiator_first_name . ' ' . $val->Initiator_last_name;
                } else {
                    $val->initiated_by = get_fms_initiated_by_name($val->initiated_type, $val->initiated_by);
                }
                $val->timelapse = time_ago_in_php($val->created);
                $val->against_for = get_fms_against_name($val->id);
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count, "status" => true);
        return $return;
    }


    public function get_fms_feedback($reqData, $filter_condition = '') {
        $limit = $reqData->pageSize ?? 20;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? '';
        $filter = $reqData->filtered ?? '';
        $orderBy = '';
        $direction = '';
        $tbl_fms = TBL_PREFIX . 'fms_feedback';

        #Search for a FMS based on: FMSID#, OCSID#, Name-Participant,   Name-Member, Name-Org, Name-Sub-Org.
        $available_column = array("id", "event_date", "feedback_id", "fms_type", "description", "init_category", 
                                  "Initiator_first_name", "Initiator_last_name", "initiated_by", "created_by");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = $tbl_fms . '.id';
            $direction = 'DESC';
        }
        $feed_category_sub_query = $this->get_feed_category_sub_query();
        $init_category_sub_query = $this->get_init_category_sub_query();
        $against_category_sub_query = $this->get_against_category_sub_query();

        $select_column = array($tbl_fms . ".id", $tbl_fms . ".event_date", $tbl_fms . ".feedback_id", $tbl_fms . ".fms_type", $tbl_fms . ".description", $tbl_fms . ".initiated_type as init_category", $tbl_fms . ".Initiator_first_name", $tbl_fms . ".Initiator_last_name", $tbl_fms . ".initiated_by",  $tbl_fms . ".created_by",
        $tbl_fms . ".created", $tbl_fms . ".updated", $tbl_fms . ".updated_by",
        $tbl_fms . ".status", $tbl_fms . ".alert_type", $tbl_fms . ".feedback_type",
        "tbl_fms_feedback_category.categoryId as feed_category", "against.against_category as against_category", $tbl_fms . ".created_by as created_name", $tbl_fms . ".updated_by as updated_name", $tbl_fms . ".initiated_by as initiated_by_name","against.against_by as against_by_name", $tbl_fms . ".initiator_type", $tbl_fms . ".against_type", $tbl_fms . '.alert_type as alertType',  $tbl_fms . '.feedback_type as feedbackType');

        # text search
        if (!empty($filter->search)) {

            $src_columns = array($tbl_fms . '.id', $tbl_fms . '.feedback_id', $tbl_fms . '.created', $tbl_fms . '.updated', $tbl_fms . '.status',
            'alertType', 'feedbackType', 'feed_category', 'init_category', 'against_category', 'initiated_by_name', 'against_by_name','created_name','updated_name');

            $this->db->group_start();

            $search_array = ['0' => 'open', '1' => 'in progress', '2' => 'closed'];
            for ($i = 0; $i < count($src_columns); $i++) {

                $column_search = $src_columns[$i];

                if($column_search == $tbl_fms . '.status' && in_array(strtolower($filter->search), $search_array)) {
                    $key = array_search (strtolower($filter->search), $search_array);
                    $this->db->or_like($column_search, $key);
                    echo $key;
                }else if($column_search == $tbl_fms . '.created' || $column_search == $tbl_fms . '.updated') {
                    $formated_date = date('Y-m-d', strtotime(str_replace('/', '-', $filter->search)));
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
                        if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $filter->search);
                    }
                }


            }
            $this->db->group_end();
        }

        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->select("(
            CASE WHEN $tbl_fms.status = 0 THEN 'New'
            WHEN $tbl_fms.status = 1 THEN 'In progress'
            WHEN $tbl_fms.status = 2 THEN 'Investigation'
            WHEN $tbl_fms.status = 3 THEN 'Closed'
            WHEN $tbl_fms.status = 4 THEN 'Completed'
            ELSE 'Closed' END) as status_label");

        $this->db->select("(
            CASE WHEN $tbl_fms.alert_type = 1 THEN 'Member Alert'
            WHEN $tbl_fms.alert_type = 2 THEN 'Organisation/Participant Alert'
            ELSE '-' END) as alertType");

        $this->db->select("(
            CASE WHEN $tbl_fms.feedback_type = 1 THEN 'Complaint'
            WHEN $tbl_fms.feedback_type = 2 THEN 'Reportable Incident'
            WHEN $tbl_fms.feedback_type = 3 THEN 'Other Feedback'
            ELSE '-' END) as feedbackType");

        $this->db->select("CASE
        WHEN $tbl_fms.created_by > 0 THEN (select concat(firstname,' ',lastname) as created_name from tbl_member where id = $tbl_fms.created_by)
        ELSE ''
        END as created_name", false);

        $this->db->select("CASE
        WHEN $tbl_fms.updated_by > 0 THEN (select concat(firstname,' ',lastname) as updated_name from tbl_member where id = $tbl_fms.updated_by)
        ELSE ''
        END as updated_name", false);

        $intiator_type_sub_query = $this->get_intiator_type_sub_query();
        $against_type_sub_query = $this->get_against_type_sub_query();


        $this->db->select($intiator_type_sub_query, false);
        $this->db->select($against_type_sub_query, false);

        $this->db->select("(" . $feed_category_sub_query . ") as feed_category");
        $this->db->select("(" . $init_category_sub_query . ") as init_category");
        $this->db->select("(" . $against_category_sub_query . ") as against_category");

        $this->db->from($tbl_fms);
        $this->db->join('tbl_fms_feedback_category', 'tbl_fms_feedback_category.caseId = ' . $tbl_fms . '.id', 'inner');
        $this->db->join('tbl_fms_feedback_against_detail as against', 'against.caseId = ' . $tbl_fms . '.id', 'inner');

        if (!empty($filter->srch_box)) {
            $this->db->join('tbl_participant', 'tbl_participant.id =  tbl_fms_feedback.initiated_by AND tbl_fms_feedback.initiated_type = 2', 'left');
            $this->db->join('tbl_member', 'tbl_member.id =  tbl_fms_feedback.initiated_by AND tbl_fms_feedback.initiated_type = 1', 'left');
        }
        $this->db->where('tbl_fms_feedback.archive = 0');
        $this->db->group_by('tbl_fms_feedback.id');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        //list view filter condition
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        return array('count' => $dt_filtered_total, 'data' => $dataResult, 'total_item' => $all_count, "status" => true);
    }

    //Archive FMS feedback
    public function fms_archive_feedback($data, $adminId) {

        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            return ['status' => false, 'error' => "Missing ID"];
        }

        # does the feedback exist?
        $result = $this->basic_model->get_row('fms_feedback', ['id'], ["id" =>  $data['id']]);
        if (empty($result)) {
            return ['status' => false, 'error' => "Feedback does not exist anymore."];
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $this->basic_model->update_records("fms_feedback", $upd_data, ["id" => $id]);

        $msg_title = "Successfully archived feedback";
        return ['status' => true, 'msg' => $msg_title];

    }

    public function get_case_detail($reqData) {
        $tbl_fms = TBL_PREFIX . 'fms_feedback';
        $where = '';
        $where = $tbl_fms . ".id = " . $reqData->case_id;
        $sWhere = $where;

        $select_column = array($tbl_fms . ".id", $tbl_fms . ".initiated_type", $tbl_fms . ".Initiator_first_name", $tbl_fms . ".Initiator_last_name", $tbl_fms . ".initiated_by", $tbl_fms . ".status as caseStatus", $tbl_fms . ".created", $tbl_fms . ".fms_type");

        $this->db->select($select_column);
        $this->db->from($tbl_fms);
        $this->db->where($sWhere, null, false);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $val = $query->row();

        if (!empty($val)) {
            if ($val->initiated_type == 5 || $val->initiated_type == 6) {
                $val->initiated_by = $val->Initiator_first_name . ' ' . $val->Initiator_last_name;
            } else {
                $idid = $val->initiated_by;
                $val->initiated_by = get_fms_initiated_by_name($val->initiated_type, $idid);
                $val->ocs_id = $idid;
            }
            $val = isset($val) && !empty($val) ? $val : array();
            $val->against = get_fms_against_name($reqData->case_id);
            $val->categoryId = $this->Basic_model->get_row('fms_feedback_category', $columns = array('categoryId'), $id_array = array('caseId' => $reqData->case_id));
            $return = array('status' => TRUE, 'data' => $val);
        } else {
            $return = array('status' => FALSE, 'data' => array());
            return $return;
        }
        return $return;
    }

    //Get feedback details by feedbackid
    public function get_fms_feedback_details($feedbackId) {

        $src_columns = array('case.id','case.feedback_id','case.event_date','case.assigned_to', 'feed_cat.categoryId as FeedCategory', 'case.initiated_type as InitiatorCategory','case.alert_type as alertType','case.feedback_type as feedbackType',
            'against.against_category as AgainstCategory', 'depart.department_id as DepartmentDetails','case.description as description', 'Initiator_first_name as initFirstName',
            'Initiator_last_name as initLasttName', 'Initiator_email as initEmail', 'Initiator_phone as initPhone', 'case.initiated_by as initiated_by', 'against.against_by as against_by',
            'm.id as assigned_value', 'concat(m.firstname," ",m.lastname) as assigned_label', 'against.against_first_name as agFirstName', 'against.against_last_name as agLastName',
            'against.against_email as agEmail', 'against.against_phone as agPhone', 'feed_cat.other', 'case.alert_type as alerttype', 'case.feedback_type as feedbacktype','case.status','case.updated_by','case.notes_reason','case.email_notification',
            'case.notify_email','case.created', 'case.notes_reason', 'case.email_notification');

        $this->db->select($src_columns);
        $this->db->select(["concat(location.address,', ',location.suburb,' ',
            (select s.name from tbl_state as s where s.id = location.state),' ',location.postal) as address", "location.unit_number","location.address_id"]);

        //Get category key name
        $this->db->select("(select intref.key_name from tbl_references as intref where case.initiated_type = intref.id) as initCatOption");

        $this->db->select("(select ag.key_name from tbl_references as ag where against.against_category = ag.id) as agCatOption");

        //Get category display name
        $this->db->select("(select ag.display_name from tbl_references as ag where against.against_category = ag.id) as agCatOptionName");

        $this->db->select("(select intref.display_name from tbl_references as intref where case.initiated_type = intref.id) as initCatOptionName");

        $this->db->select("(select dep.display_name from tbl_references as dep where depart.department_id = dep.id) as departName");

        $this->db->select("(select feed.display_name from tbl_references as feed where feed_cat.categoryId = feed.id) as FeedName");

        $this->db->select("(
            CASE WHEN case.status = 0 THEN 'New'
            WHEN case.status = 1 THEN 'In progress'
            WHEN case.status = 2 THEN 'Investigation'
            WHEN case.status = 3 THEN 'Closed'
            WHEN case.status = 4 THEN 'Completed'
            ELSE 'Closed' END) as status_label");

        $this->db->select("(
            CASE WHEN case.alert_type = 1 THEN 'Member Alert'
            WHEN case.alert_type = 2 THEN 'Organisation/Participant Alert'
            ELSE 'N/A' END) as alerttype");

        $this->db->select("(
            CASE WHEN case.feedback_type = 1 THEN 'Complaint'
            WHEN case.feedback_type = 2 THEN 'Reportable Incident'
            WHEN case.feedback_type = 3 THEN 'Other Feedback'
            ELSE 'N/A' END) as feedbacktype");

        $this->db->from(TBL_PREFIX . 'fms_feedback as case');
        $this->db->join(TBL_PREFIX . 'fms_feedback_category as feed_cat', 'feed_cat.caseId = case.id', 'left');
        $this->db->join('tbl_fms_feedback_against_detail as against', 'against.caseId = case.id', 'left');
        $this->db->join(TBL_PREFIX . 'fms_feedback_department as depart', 'depart.case_id = case.id', 'left');
        $this->db->join(TBL_PREFIX . 'fms_feedback_location as location', 'location.caseId = case.id', 'left');
        $this->db->join('tbl_member m', 'm.id = case.assigned_to', 'left');

        $this->db->where('case.id', $feedbackId);

        $dataResult = $this->db->get()->row_array();

        if(!empty($dataResult)) {
            if($dataResult['assigned_to']) {
                $assigned_person['label'] = $dataResult['assigned_label'];
                $assigned_person['value'] = $dataResult['assigned_value'];
            }else{
                $assigned_person = '';
            }

            if($dataResult['other'] !== '') {
                $dataResult['otherFeedback'] = TRUE;
            }

            $dataResult['assignedTo'] = $assigned_person;

            $cat_val = [
                'init_hcm_member' => 'initOnCallMember',
                'init_hcm_participant' => 'initOnCallParticipant',
                'init_hcm_user_admin' => 'initOnCallAdmin',
                'init_hcm_organisation' => 'initOnCallOrganisation',
                'init_hcm_site' => 'initOnCallSiteName',
                'aga_hcm_member' => 'agOnCallMember',
                'aga_hcm_participant' => 'agOnCallParticipant',
                'aga_hcm_user_admin' => 'agOnCallAdmin',
                'aga_hcm_organisation' => 'agOnCallOrganisation',
                'aga_hcm_site' => 'agOnCallSiteName'
            ];

            if($dataResult['initCatOption'] == 'init_hcm_member' ||
                $dataResult['initCatOption'] == 'init_hcm_participant' ||
                $dataResult['initCatOption'] == 'init_hcm_user_admin' ||
                $dataResult['initCatOption'] == 'init_hcm_organisation' ||
                $dataResult['initCatOption'] == 'init_hcm_site') {

                $initiated_by = $dataResult['initiated_by'];

                if($initiated_by) {
                    $name = get_fms_dropdown_category_by_name($dataResult['initCatOption'], $initiated_by);

                    if(!empty($name)) {
                        $dataResult[$cat_val[$dataResult['initCatOption']]] = ['label' => $name, 'value' => $initiated_by];
                    }
                }

            }

            if($dataResult['agCatOption'] == 'aga_hcm_member' ||
                $dataResult['agCatOption'] == 'aga_hcm_participant' ||
                $dataResult['agCatOption'] == 'aga_hcm_user_admin' ||
                $dataResult['agCatOption'] == 'aga_hcm_organisation' ||
                $dataResult['agCatOption'] == 'aga_hcm_site') {

                $against_by = $dataResult['against_by'];

                if($against_by) {
                    $name = get_fms_dropdown_category_by_name($dataResult['agCatOption'], $against_by);

                    if(!empty($name)) {
                        $dataResult[$cat_val[$dataResult['agCatOption']]] = ['label' => $name, 'value' => $against_by];
                    }
                }

            }


        }
        return ['status' => TRUE, 'data' => $dataResult];
    }

    //Get feedback details by feedbackid
    public function get_member_feedback_list($reqData, $filter_condition) {

        $limit = $reqData->pageSize ?? 99999;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? '';
        $filter = $reqData->filtered ?? '';
        $reqData = (array) $reqData;
        $available_column = array('id','feedback_id','event_date','FeedCategory', 'init_category', 'description', 
                                  'initiated_by', 'against_by', 'intiator_name', 'against_name','created', 'status');
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;

                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'fk.created';
            $direction = 'DESC';
        }

        $member_id =  (!empty($reqData["member_id"])) ? $reqData["member_id"] : '';

        if(!$member_id) {
            return ['status' => FALSE, 'error' => 'Member ID missing'];
        }

        $select_columns = array('fk.id','fk.feedback_id',
        'fk.event_date','feed_cat.categoryId as FeedCategory', 'fk.initiated_type as
         init_category', 'fk.description as description', 'fk.initiated_by as
         initiated_by', 'against.against_by as against_by', 'CONCAT(fk.Initiator_first_name," ",fk.Initiator_last_name) as intiator_name', 'CONCAT(against.against_first_name," ",against.against_last_name) as against_name','fk.created', 'fk.status');

         if (!empty($filter->filter_status) && $filter->filter_status=='1') {
            $this->db->where('fk.status', $filter->filter_status);
         }else if(isset($filter->filter_status) && (empty($filter->filter_status) || $filter->filter_status=='0')){
            $this->db->where('fk.status', $filter->filter_status);
         }

         # text search
        if (!empty($filter->search)) {

            $src_columns = array('fk.id','fk.feedback_id',
            'fk.event_date','feed_category', 'description',
            'initiated_by','fk.created', 'fk.status', 'initCatOption', 'agCatOption');


            $this->db->group_start();

            $status_array = ['0' => 'New','1' => 'In progress','2' => 'Investigation','3' => 'Closed','4' => 'Completed'];
            for ($i = 0; $i < count($src_columns); $i++) {

                $column_search = $src_columns[$i];

                if($column_search == 'fk.status' && in_array(strtolower($filter->search), $status_array)) {
                    $key = array_search (strtolower($filter->search), $status_array);
                    $this->db->or_like($column_search, $key);
                }else if($column_search == 'fk.created') {
                    $formated_date = date('Y-m-d', strtotime(str_replace('/', '-', $filter->search)));
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
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $filter->search);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $filter->search);
                    }
                }

            }
            $this->db->group_end();
        }

        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select($select_columns);

        //Get Category name for both initiator and against
        $this->db->select("(select CONCAT(intref.key_name,',',intref.display_name) AS intname from tbl_references as intref where fk.initiated_type = intref.id) as initCatOption");

        $this->db->select("(select CONCAT(ag.key_name,',',ag.display_name) AS agname from tbl_references as ag where against.against_category = ag.id) as agCatOption");


        $this->db->select("(select feed.display_name from tbl_references as feed where feed_cat.categoryId = feed.id) as feed_category");

        $this->db->select("(
            CASE WHEN fk.status = 0 THEN 'New'
            WHEN fk.status = 1 THEN 'In progress'
            WHEN fk.status = 2 THEN 'Investigation'
            WHEN fk.status = 3 THEN 'Closed'
            WHEN fk.status = 4 THEN 'Completed'
            ELSE 'Closed' END) as status");

        $this->db->from(TBL_PREFIX . 'fms_feedback as fk');
        $this->db->join(TBL_PREFIX . 'fms_feedback_category as feed_cat', 'feed_cat.caseId = fk.id', 'left');
        $this->db->join('tbl_fms_feedback_against_detail as against', 'against.caseId = fk.id', 'left');

        $this->db->join('tbl_references as ag_ref', 'ag_ref.id = against.against_category AND ag_ref.key_name = "aga_hcm_member"', 'left');

        $this->db->join('tbl_references as init_ref', 'init_ref.id = fk.initiated_type AND init_ref.key_name = "init_hcm_member"', 'left');

        $this->db->join('tbl_member m', 'm.id = fk.initiated_by OR
            m.id = against.against_by', 'left');

        $this->db->where('m.id', $member_id);

        $where = '(m.id = against.against_by or m.id = fk.initiated_by)';
        $this->db->where($where);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        //list view filter condition
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $dataResult = $this->db->get()->result_array();


        if(!empty($dataResult)) {
            foreach($dataResult as $key => $result) {
                $initCatOption = $agCatOption = '';
                $result['initiator_detail'] = '';
                $result['against_detail'] = '';

                if($result['initCatOption'] && $result['agCatOption']){
                    $initCatOption = explode(',', $result['initCatOption']);
                    $agCatOption = explode(',', $result['agCatOption']);

                }
                if(!empty($initCatOption) && !empty($agCatOption)) {
                    //Get member/Participant/site/admin name
                    $initiated_by = get_fms_dropdown_category_by_name(trim($initCatOption[0]), $result['initiated_by']);

                    $against_by = get_fms_dropdown_category_by_name(trim($agCatOption[0]), $result['against_by']);

                    $dataResult[$key]['initiator_detail'] = '';
                    $dataResult[$key]['against_detail'] = '';

                    $dataResult[$key]['init_cat_name'] = trim($initCatOption[1]);
                    $dataResult[$key]['against_cat_name'] = trim($agCatOption[1]);

                    if(!empty($initiated_by)) {
                        $dataResult[$key]['initiator_detail'] = $initiated_by;
                    } else if ($initCatOption[0] == 'init_member_of_public' ||
                        $initCatOption[0] == 'init_hcm_general') {

                            $dataResult[$key]['initiator_detail'] = $result['intiator_name'];
                    }

                    if(!empty($against_by)) {
                        $dataResult[$key]['against_detail'] = $against_by;
                    }
                    else if ($agCatOption[0] == 'aga_member_of_public' || $agCatOption[0] == 'aga_hcm_general') {
                        $dataResult[$key]['against_detail'] = $result['against_name'];
                    }
                }

            }
        }

        return ['status' => TRUE, 'data' => $dataResult];
    }

    public function get_link_fms_cases($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';

        $tbl_fms = TBL_PREFIX . 'fms_case';

        $src_columns = array();
        $available_column = array("id", "event_date","initiated_type", "Initiator_first_name", "Initiator_last_name", "initiated_by", 
                                  "link_case as linkCaseId", "created", "categoryId", "case_category", "event_date");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = $tbl_fms . '.id';
            $direction = 'DESC';
        }

        $where = '';
        $where = " tbl_fms_case_link.archive = 0";
        #$where = $tbl_fms . ".id = ".$filter->caseId;
        $sWhere = $where;


        if (!empty($filter->search_box)) {
            $where = $where . " AND (";
            $sWhere = " (" . $where;
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $sWhere .= $serch_column[0] . " LIKE '%" . $this->db->escape_like_str($filter->search_box) . "%' OR ";
                } else {
                    $sWhere .= $column_search . " LIKE '%" . $this->db->escape_like_str($filter->search_box) . "%' OR ";
                }
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= '))';
        }

        $select_column = array($tbl_fms . ".id", $tbl_fms . ".event_date", $tbl_fms . ".initiated_type", $tbl_fms . ".Initiator_first_name", $tbl_fms . ".Initiator_last_name", $tbl_fms . ".initiated_by", "tbl_fms_case_link.link_case as linkCaseId", $tbl_fms . ".created", "tbl_fms_case_category.categoryId", "tbl_fms_case_all_category.name as case_category", "DATE_FORMAT(tbl_fms_case.event_date,'%d/%m/%Y') as event_date");

        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->from($tbl_fms);
        //$this->db->join('tbl_fms_case_reason', 'tbl_fms_case_reason.caseId = case.id', 'left');
        $this->db->join('tbl_fms_case_link', 'tbl_fms_case_link.link_case = case.id AND tbl_fms_case_link.caseId = ' .$filter->caseId, 'inner');
        $this->db->join('tbl_fms_case_category', 'tbl_fms_case_category.caseId = case.id', 'inner');
        $this->db->join('tbl_fms_case_all_category', 'tbl_fms_case_all_category.id = tbl_fms_case_category.categoryId', 'inner');

        $this->db->group_by('tbl_fms_case_link.id');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $this->db->where($sWhere, null, false);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query(); die;
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
               if ($val->initiated_type == 5 || $val->initiated_type == 6) {
                    $val->initiated_by = $val->Initiator_first_name . ' ' . $val->Initiator_last_name;
                } else {
                    $val->initiated_by = get_fms_initiated_by_name($val->initiated_type, $val->initiated_by);
                }
                $val->timelapse = time_ago_in_php($val->created);
                $val->against_for = get_fms_against_name($val->linkCaseId);
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count);
        return $return;
    }

    public function get_srch_fms_cases($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $tbl_fms = TBL_PREFIX . 'fms_case';

        $src_columns = array($tbl_fms . ".id", $tbl_fms . ".event_date", "tbl_fms_case_reason.description", $tbl_fms . ".Initiator_first_name", $tbl_fms . ".Initiator_last_name", $tbl_fms . ".initiated_by", "CONCAT(tbl_member.firstname,' ',tbl_member.middlename,' ',tbl_member.lastname)", "CONCAT(tbl_member.firstname,' ',tbl_member.lastname)");
        $available_column = array("linkCaseId","event_date", "description", "initiated_type", "Initiator_first_name", "Initiator_last_name", 
                                  "initiated_by", "created", "categoryId", "case_category", "event_date");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = $tbl_fms . '.id';
            $direction = 'DESC';
        }

        $where = '';

        $where = $tbl_fms . ".id != " . $filter->caseId . " AND tbl_fms_case.id NOT IN(select link_case from tbl_fms_case_link where caseId = $filter->caseId AND archive=0)";

        $sWhere = $where;

        if (!empty($filter->srch_box)) {
            $where = $where . " AND (";
            $sWhere = " (" . $where;
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $sWhere .= $serch_column[0] . " LIKE '%" . $this->db->escape_like_str($filter->srch_box) . "%' OR ";
                } else {
                    $sWhere .= $column_search . " LIKE '%" . $this->db->escape_like_str($filter->srch_box) . "%' OR ";
                }
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= '))';
        }

        if (!empty($filter->from_date)) {
            $this->db->where($tbl_fms . ".event_date >= '" . date('Y-m-d', strtotime($filter->from_date)) . "'");
        }
        if (!empty($filter->to_date)) {
            $this->db->where($tbl_fms . ".event_date <= '" . date('Y-m-d', strtotime($filter->to_date)) . "'");
        }

        $select_column = array($tbl_fms . ".id as linkCaseId", $tbl_fms . ".event_date", "tbl_fms_case_reason.description", $tbl_fms . ".initiated_type", $tbl_fms . ".Initiator_first_name", $tbl_fms . ".Initiator_last_name", $tbl_fms . ".initiated_by", $tbl_fms . ".created", "tbl_fms_case_category.categoryId", "tbl_fms_case_all_category.name as case_category", "DATE_FORMAT(tbl_fms_case.event_date,'%d/%m/%Y') as event_date");

        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from($tbl_fms);
        $this->db->join('tbl_fms_case_reason', 'tbl_fms_case_reason.caseId = ' . $tbl_fms . '.id', 'left');
        $this->db->join('tbl_member', 'tbl_member.id = ' . $tbl_fms . '.initiated_by', 'left');
        $this->db->join('tbl_fms_case_category', 'tbl_fms_case_category.caseId = ' . $tbl_fms . '.id', 'inner');
        $this->db->join('tbl_fms_case_all_category', 'tbl_fms_case_all_category.id = tbl_fms_case_category.categoryId', 'inner');
        $this->db->group_by('tbl_fms_case_reason.caseId');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $this->db->where($sWhere, null, false);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query(); die;
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        if (!empty($dataResult)) {
            #pr($dataResult);
            foreach ($dataResult as $val) {

                if ($val->initiated_type == 5 || $val->initiated_type == 6) {
                    $val->initiated_by = $val->Initiator_first_name . ' ' . $val->Initiator_last_name;
                } else {
                    $val->initiated_by = get_fms_initiated_by_name($val->initiated_type, $val->initiated_by);
                }
                $val->timelapse = time_ago_in_php($val->created);
                $val->against_for_srch = get_fms_against_name($val->linkCaseId);
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count);
        return $return;
    }

    public function get_fms_log($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $tbl_fms = TBL_PREFIX . 'fms_case_log';

        $src_columns = array();
        $available_column = array("title", "created_by", "created");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = $tbl_fms . '.id';
            $direction = 'DESC';
        }

        $where = '';
        $where = $tbl_fms . ".caseId = " . $filter->caseId;
        $sWhere = $where;

        if (!empty($filter->on)) {
            $this->db->where(array('date(tbl_fms_case_log.created)' => date('Y-m-d', strtotime($filter->on))));
        } else {
            if (!empty($filter->from_date)) {
                $this->db->where(" date(tbl_fms_case_log.created) >= '" . date('Y-m-d', strtotime($filter->from_date)) . "'");
            }
            if (!empty($filter->to_date)) {
                $this->db->where(" date(tbl_fms_case_log.created) <= '" . date('Y-m-d', strtotime($filter->to_date)) . "'");
            }
        }

        $select_column = array($tbl_fms . ".title", $tbl_fms . ".created_by", $tbl_fms . ".created");

        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from($tbl_fms);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        if (!empty($sWhere))
            $this->db->where($sWhere, null, false);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query(); die;
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $val->created_by = 'By: ' . $val->created_by;
                $val->created = date('d/m/Y - h:i', strtotime($val->created));
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult);
        return $return;
    }

    public function search_address_book($reqData) {
        $searchbox = $reqData->data->searchbox;
        $except_record = isset($reqData->data->except_record) ? $reqData->data->except_record : '';
        $remove_record = array();
        $remove_member_record = array();
        $remove_participant_record = array();
        if (!empty($except_record)) {
            foreach ($except_record as $key => $value) {
                if ($value->type == 'member')
                    $remove_member_record[] = $value->id;
                else
                    $remove_participant_record[] = $value->id;
            }
        }
        $id_member = implode(',', array_unique($remove_member_record));
        $id_participant = implode(',', array_unique($remove_participant_record));

        if (empty($id_member))
            $id_member = 0;
        if (empty($id_participant))
            $id_participant = 0;

        $query = "select concat(firstname,' ', middlename,' ',lastname) as  label, 'member'  as type,tbl_member_phone.id,tbl_member_phone.phone from tbl_member left join tbl_member_phone on tbl_member.id = tbl_member_phone.memberId AND tbl_member_phone.primary_phone = 1
            WHERE archive = 0 AND tbl_member_phone.id NOT IN($id_member)  AND concat(firstname,' ', middlename,' ',lastname) LIKE '%" . $searchbox . "%'
                union
            select concat(firstname,' ', middlename,' ',lastname) as  label, 'participant'  as type, tbl_participant.id,tbl_participant_phone.phone from tbl_participant left join tbl_participant_phone on tbl_participant.id = tbl_participant_phone.participantId AND tbl_participant_phone.primary_phone = 1
                WHERE archive = 0 AND tbl_participant_phone.id NOT IN($id_participant) AND concat(firstname,' ', middlename,' ',lastname) LIKE '%" . $searchbox . "%'";

        $query = $this->db->query($query);
        return $query->result();
    }

    public function get_contact_list($reqData) {
        $fms_address_book = TBL_PREFIX . 'fms_address_book';
        $this->db->select(array('id', 'type', 'ocs_id'));
        $this->db->from($fms_address_book);
        $this->db->where(array('caseId' => $reqData->data->case_id), null, false);
        $this->db->order_by('id', 'desc');
        $query = $this->db->get();
        $main_ary = array();
        $temp_ary = array();
        $result = $query->result();
        if (!empty($result)) {
            foreach ($result as $val) {
                $temp_ary['id'] = $val->id;
                $temp_ary['ocs_id'] = $val->ocs_id;
                if ($val->type == '1') {
                    $sql = "SELECT concat(firstname,' ', middlename,' ',lastname) as name,tbl_member_phone.phone FROM tbl_member LEFT JOIN tbl_member_phone ON tbl_member.id = tbl_member_phone.memberId AND tbl_member_phone.primary_phone = 1 AND tbl_member.id= ? WHERE archive = 0";
                    $fetch_row = $this->db->query($sql, array($val->ocs_id));
                    $row = $fetch_row->row();
                    if (!empty($row)) {
                        $temp_ary['label'] = $row->name;
                        $temp_ary['phone'] = $row->phone;

                        $temp_ary['type'] = 'member';
                    }
                } else {
                    $sql = "SELECT concat(firstname,' ', middlename,' ',lastname) as name,tbl_participant_phone.phone FROM tbl_participant LEFT JOIN tbl_participant_phone on tbl_participant.id = tbl_participant_phone.participantId AND tbl_participant_phone.primary_phone = 1 AND tbl_participant.id= ? WHERE archive = 0 ";
                    $fetch_row = $this->db->query($sql, array($val->ocs_id));
                    $row = $fetch_row->row();
                    if (!empty($row)) {
                        $temp_ary['label'] = $row->name;
                        $temp_ary['phone'] = $row->phone;
                        $temp_ary['type'] = 'participant';
                    }
                }
                $main_ary[] = $temp_ary;
            }
        }

        return $main_ary;
    }

    public function save_contact($reqData) {
        $responseAry = $reqData->data;
        $case_ary = array('caseId' => $responseAry->case_id,
            'type' => isset($responseAry->record->type) && $responseAry->record->type == 'member' ? 1 : 2,
            'ocs_id' => $responseAry->record->id,
            'created' => DATE_TIME,
        );
        return $this->Basic_model->insert_records('fms_address_book', $case_ary);
    }

    public function common_subquery_for_status() {
        return "(CASE when fc.status = 0 THEN 'Ongoing' ELSE 'Completed' end)";
    }

    public function common_subquery_for_short_description() {
        return "(select description from tbl_fms_case_reason where caseId = fc.id order by id asc limit 1)";
    }

    //Get the feedback form dropdown values
    public function get_fms_feedback_options() {
        $res = ["status" => FALSE, "error" => 'Result not found'];

        $this->db->select(['ref.display_name label', 'ref.id as value', 'type.key_name as type_name', 'ref.key_name as key_name']);
        $this->db->from(TBL_PREFIX . 'references as ref');
        $this->db->join(TBL_PREFIX . 'reference_data_type as type', 'ref.type = type.id', 'inner');
        $this->db->where_in('type.key_name',
             ['fms_feed_category', 'fms_initiator_category', 'fms_against_category',
              'fms_department_details']);
        $this->db->where("type.archive", 0);
        $query = $this->db->get();

        if($query->num_rows() > 0) {
            $result = [];

            foreach($query->result_array() as $data) {

                $result[$data['type_name']][] = ['label' => $data['label'], 'value' => $data['value'], 'key_name' => trim($data['key_name'])];

            }

            $res = ["status" => TRUE, "data" => $result];
        }

        return $res;
    }

    /*
     * its used for gettting adddress
     */
    public function get_address_for_fms($reqData)
    {
        $id = $reqData->id;

        $output = array();
        $address = ['status' => false, 'message' => 'Address not found'];

        if($reqData->type == "aga_hcm_participant") {
            $this->load->model('item/Participant_model');
            $this->db->select(["pt.id as value, concat(pt.street,', ',pt.suburb,' ',(select s.name from tbl_state as s where s.id = pt.state),' ',pt.postcode) as label, pt.unit_number"]);
            $this->db->from("tbl_participants_master as p");
            $this->db->join('tbl_person_address as pt', 'pt.person_id = p.contact_id and pt.archive = 0', 'inner');
            $this->db->where("p.id", $id);
            $query = $this->db->get();
            $address = $query->num_rows() > 0 ? $query->result_array() : [];
            $location = $this->Participant_model->get_participant_location($id);

            $output = array_merge($address,$location);

            if($output) {
                $address = array('status' => true, 'data' => $output);
            }
        }
        else if($reqData->type == "aga_hcm_organisation" || $reqData->type == "aga_hcm_site") {
            $this->db->select(["oa.id as value, concat(oa.street,', ',oa.city,' ',(select s.name from tbl_state as s where s.id = oa.state),' ',oa.postal) as label, oa.unit_number"]);
            $this->db->from("tbl_organisation_address as oa");
            $this->db->where("oa.organisationId", $id);
            $this->db->where("oa.primary_address", 1);
            $this->db->where("oa.address_type", 2); // Shipping address
            $this->db->where("oa.archive", 0);
            $query = $this->db->get();
            $orgAddress = $query->num_rows() > 0 ? $query->result_array() : [];
            if(!empty($orgAddress)) {
                $address = array('status' => true, 'data' => $orgAddress);
            }
        }

        return $address;
    }

    public function create_update_feed($data, $adminId) {
        $caseId = !empty($data["id"]) ?? '';
        $feed_history_update_data = [];
        $existing_feed_data = [];
        $against_type_data = $this->against_type;


        //Delete previous case record for update with new record
        if($caseId) {
            $this->delete_old_data_for_update($caseId);
        }

        $feed_details = $this->insert_update_feed($data, $adminId);
       
        if(!empty($feed_details['update_history_data'])){
            $feed_history_update_data = $feed_details['update_history_data'];
            $existing_feed_data = $feed_details['existingFeedBack'];
        }

        $feed_against_details = $this->insert_update_feed_against_details($data, $feed_details['case_id'], $adminId);
        if(!empty($feed_against_details['update_history_data'])){
            $feed_history_update_data['against_category'] = $feed_against_details['update_history_data']['against_category'];
            
            $feed_history_update_data['against_first_name'] = $feed_against_details['update_history_data']['against_first_name'];
            $feed_history_update_data['against_last_name'] = $feed_against_details['update_history_data']['against_last_name'];
            $feed_history_update_data['against_email'] = $feed_against_details['update_history_data']['against_email'];
            $feed_history_update_data['against_phone'] = $feed_against_details['update_history_data']['against_phone'];
          
            if($data['agCatOption']!='aga_member_of_public' && $data['agCatOption']!='aga_hcm_general'){
                $feed_history_update_data['against_by'] = $feed_against_details['update_history_data']['against_by'];
                $againstData =  array("against_type"=>$feed_details['existingFeedBack']['against_type'], "against_by"=>$feed_against_details['existingFeedBack']['against_by']);       
                $existing_feed_data['against_by'] = json_encode($againstData);
            }

            $existing_feed_data['against_category'] = $feed_against_details['existingFeedBack']['against_category'];
            $existing_feed_data['against_first_name'] = $feed_against_details['existingFeedBack']['against_first_name'];
            $existing_feed_data['against_last_name'] = $feed_against_details['existingFeedBack']['against_last_name'];
            $existing_feed_data['against_email'] = $feed_against_details['existingFeedBack']['against_email'];
            $existing_feed_data['against_phone'] = $feed_against_details['existingFeedBack']['against_phone'];
        }
        

        $feed_category_details = $this->insert_update_feed_category($data, $feed_details['case_id'], $adminId);
        if(!empty($feed_category_details['update_history_data'])){
            $feed_history_update_data['categoryId'] = $feed_category_details['update_history_data']['categoryId'];

            $existing_feed_data['categoryId'] = $feed_category_details['existingFeedBack']['categoryId'];
        }
        $feed_department_details = $this->insert_update_feed_department($data, $feed_details['case_id'], $adminId);
       
        if(!empty($feed_department_details['update_history_data'])){
            $feed_history_update_data['department_id'] = $feed_department_details['update_history_data']['department_id'];

            $existing_feed_data['department_id'] = $feed_department_details['existingFeedBack']['department_id'];
        }

        $feed_address_details = $this->insert_update_feed_address($data, $feed_details['case_id']);
        if(!empty($feed_address_details['update_history_data'])){
            $feed_history_update_data['address'] = !empty($data['unit_number']) ? $data['unit_number'].', '.$feed_address_details['update_history_data']['address'] : $feed_address_details['update_history_data']['address'];

            $existing_feed_data['address'] = $feed_address_details['existingFeedBack']['address'];
        }

        #update fms feedback history
        $this->updateHistory($existing_feed_data, $feed_history_update_data, $adminId);

        //Create Log
        $this->loges->setLogType('fms');
        $this->loges->setCreatedBy($adminId);
        $this->loges->setUserId($feed_details['case_id']);
        $this->loges->setDescription(json_encode($data));
        $this->loges->setTitle('FMS Case created : Case Id ' . $feed_details['case_id']);
        $this->loges->createLog();
        $msg = ($caseId) ? 'Feedback update successfully.' : 'Feedback created successfully.';
        if($data['alertType']==1){
            $this-> restrict_member($data,$adminId);
        }
        return ['status' => TRUE, 'msg' => $msg];

    }

    //Delete Old record for storing new values
    public function delete_old_data_for_update($caseId) {

        $this->basic_model->delete_records('fms_feedback_category', ['caseId' => $caseId]);
        $this->basic_model->delete_records('fms_feedback_against_detail', ['caseId' => $caseId]);
        $this->basic_model->delete_records('fms_feedback_department', ['case_id' => $caseId]);
        $this->basic_model->delete_records('fms_feedback_location', ['caseId' => $caseId]);
    }

    public function get_feedback_against_by_details($data){
        $ag_by = '';
        if(!empty($data['agOnCallMember'])) {
            $ag_by = $data['agOnCallMember']['value'];
        }else if(!empty($data['agOnCallParticipant'])) {
            $ag_by = $data['agOnCallParticipant']['value'];
        }else if(!empty($data['agOnCallOrganisation'])) {
            $ag_by = $data['agOnCallOrganisation']['value'];
        }else if(!empty($data['agOnCallAdmin'])) {
            $ag_by = $data['agOnCallAdmin']['value'];
        }
        else if(!empty($data['agOnCallSiteName'])) {
            $ag_by = $data['agOnCallSiteName']['value'];
        }

        return $ag_by;
    }

    /**
     * @param {$data} array list of feed form data
     * @param {$case_id} int case id optional
     *
     * Insert or update case table
     */
    public function insert_update_feed($data, $adminId = '') {
        $case_id = $data['id'] ?? 0;
        $init_by = 0;
        $update_history_data = [];
        $existingFeedBack = [];
        if(!empty($data['initOnCallMember'])) {
            $init_by = $data['initOnCallMember']['value'];
        }else if(!empty($data['initOnCallParticipant'])) {
            $init_by = $data['initOnCallParticipant']['value'];
        }else if(!empty($data['initOnCallOrganisation'])) {
            $init_by = $data['initOnCallOrganisation']['value'];
        }else if(!empty($data['initOnCallAdmin'])) {
            $init_by = $data['initOnCallAdmin']['value'];
        }
        else if(!empty($data['initOnCallSiteName'])) {
            $init_by = $data['initOnCallSiteName']['value'];
        }

        $init_type = [ 'init_hcm_member' => 1, 'init_hcm_participant' => 2, 'init_hcm_user_admin' => 3, 'init_hcm_organisation' => 4, 'init_hcm_site' => 5, 'init_member_of_public' => 6, 'init_hcm_general' => 7];

        $against_data = $this->against_type;

        $feed_ary = array(
        'alert_type' => $data['alertType'],
        'feedback_type' => $data['feedbackType'],
        'event_date' => $data['event_date'] ?? '',
        'initiated_by' => $init_by,
        'initiated_type' => $data['InitiatorCategory'] ?? NULL,
        'initiator_type' => isset($data['initCatOption']) && $init_type[trim($data['initCatOption'])] ? $init_type[trim($data['initCatOption'])] : NULL,
        'against_type' => $against_data[trim($data['agCatOption'])] ? $against_data[trim($data['agCatOption'])] : NULL,
        'Initiator_first_name' => $data['initFirstName'] ?? '',
        'Initiator_last_name' => $data['initLasttName'] ?? '',
        'Initiator_email' => $data['initEmail'] ?? '',
        'Initiator_phone' => $data['initPhone'] ?? '',
        'description' => $data['description'] ?? '',
        'assigned_to' => isset($data['assignedTo']) ? $data['assignedTo']['value'] :  NULL,
        'status' => 0,
        );

        if(!$case_id) {
            $feed_ary['created'] = DATE_TIME;
            $feed_ary['created_by'] = $adminId;
            $case_id = $this->basic_model->insert_records('fms_feedback', $feed_ary, FALSE);
             #update fms feedback history
             $this->updateHistory(['id' => $case_id, 'created' => ''], $feed_ary, $adminId);
        }
        else if($case_id) {

             #validating existing feedback
             $existingFeedBack = $this->db->get_where('tbl_fms_feedback', ['id' => $case_id, 'archive' => 0], 1)->row_array();
             if (!$existingFeedBack) {
                 return [
                     'status' => false,
                     'error' => 'The feed back you are trying to modify was either removed or marked as archived',
                 ];
             }

            $feed_ary['updated'] = DATE_TIME;
            $feed_ary['updated_by'] = $adminId;

            $this->basic_model->update_records('fms_feedback', $feed_ary, ['id' => $case_id]);

            $initiated_by_data =  array("initiator_type"=>$init_type[trim($data['initCatOption'])] ? $init_type[trim($data['initCatOption'])] : NULL, "initiated_by"=>$init_by);       
            
            $existing_initated_by_data = array("initiator_type"=>$existingFeedBack['initiator_type'] ? $existingFeedBack['initiator_type'] : NULL, "initiated_by"=>$existingFeedBack['initiated_by']);       
            $existingFeedBack['initiated_by'] = json_encode($existing_initated_by_data);

            $update_history_data = array(
                'feedback_id'=> $data['feedback_id']?? null,
                'assigned_to' => isset($data['assignedTo']) ? $data['assignedTo']['value'] :  NULL,
                'initiated_by' => json_encode($initiated_by_data),
                'initiated_type' => $data['InitiatorCategory'] ?? NULL,
                'Initiator_first_name' => $data['initFirstName'] ?? '',
                'Initiator_last_name' => $data['initLasttName'] ?? '',
                'Initiator_email' => $data['initEmail'] ?? '',
                'Initiator_phone' => $data['initPhone'] ?? '',
                'description' => $data['description'] ?? '',
                'alert_type' => $data['alertType'],
                'feedback_type' => $data['feedbackType'],
                'categoryId'=> $data['FeedCategory'],
            );
            if($data['event_date']!= '0000-00-00 00:00:00'){
                $update_history_data['event_date'] = $data['event_date'] ? date("Y-m-d H:i:s", strtotime($data['event_date'])) : '';
            }
        }
        return ['case_id' => $case_id , "update_history_data" => $update_history_data , "existingFeedBack" => $existingFeedBack];
    }



    /**
     * @param {$data} array list of feed form data
     * @param {$case_id} int case id optional
     *
     * Insert or update Against details
     */
    public function insert_update_feed_against_details($data, $case_id = '', $adminId) {
        $ag_by = $this->get_feedback_against_by_details($data);
        $against_type_data = $this->against_type;
        $againstData=[];
        $update_history_data = [];
        $existingFeedBack = [];

        $feed_ary = array(
        'against_by' => $ag_by,
        'against_category' => $data['AgainstCategory'],
        'created' => DATE_TIME,
        'against_first_name' => $data['agFirstName'] ?? '',
        'against_last_name' => $data['agLastName'] ?? '',
        'against_email' => $data['agEmail'] ?? '',
        'against_phone' => $data['agPhone'] ?? '',
        'caseId' => $case_id
        );

        $against_details = $this->basic_model->get_row('fms_feedback_against_detail', ['id'], ["caseId" => $case_id]);

        if(!$against_details) {
            $case_id = $this->basic_model->insert_records('fms_feedback_against_detail', $feed_ary, FALSE);
        }
        else if($against_details) {

            #validating existing feedback
            $existingFeedBack = $this->db->get_where('tbl_fms_feedback_against_detail', ['caseId' => $case_id], 1)->row_array();
            if (!$existingFeedBack) {
                return [
                    'status' => false,
                    'error' => 'The feed against category you are trying to modify was either removed or marked as archived',
                ];
            }

            $againstData =  array("against_type"=>$against_type_data[trim($data['agCatOption'])] ? $against_type_data[trim($data['agCatOption'])] : NULL, "against_by"=>$ag_by);
            
            $update_history_data = array(
                'against_category'=> $data['AgainstCategory'] ,
                // 'against_by'=> json_encode($againstData) ,   
                'against_first_name' => $data['agFirstName'] ?? '',
                'against_last_name' => $data['agLastName'] ?? '',
                'against_email' => $data['agEmail'] ?? '',
                'against_phone' => $data['agPhone'] ?? '',           
            );

            if($data['agCatOption']!='aga_member_of_public' && $data['agCatOption']!='aga_hcm_general'){
                $update_history_data['against_by'] =json_encode($againstData);
            }

            $this->basic_model->update_records('fms_feedback_against_detail', $feed_ary, ['id' => $against_details->id]);
        }

        return ['case_id' => $case_id , "update_history_data" => $update_history_data , "existingFeedBack" => $existingFeedBack];
    }

    /**
     * @param {$data} array list of feed form data
     * @param {$case_id} int case id optional
     *
     * Insert or update Feed Category
     */
    public function insert_update_feed_category($data, $case_id, $adminId) {
        $category_details = $this->basic_model->get_row('fms_feedback_category', ['caseId'], ["caseId" => $case_id]);
        $catid = $data['FeedCategory'];
        $other = $data['other']?? NULL;
        $update_history_data = [];
        $existingFeedBack = [];

        if(!$category_details) {
            $this->basic_model->insert_records('fms_feedback_category', ['caseId' => $case_id, 'categoryId' => $catid, 'other' => $other]);
        }
        else if($category_details) {
            #validating existing feedback
            $existingFeedBack = $this->db->get_where('tbl_fms_feedback_category', ['caseId' => $case_id], 1)->row_array();
            if (!$existingFeedBack) {
                return [
                    'status' => false,
                    'error' => 'The feed category you are trying to modify was either removed or marked as archived',
                ];
            }
          
            $update_history_data = array(
                'categoryId'=> $catid,                
            );

            $this->basic_model->update_records('fms_feedback_category', ['categoryId' => $catid, 'other' => $other], ['caseId' => $case_id]);

            return ['case_id' => $case_id , "update_history_data" => $update_history_data , "existingFeedBack" => $existingFeedBack];
        }
    }

    /**
     * @param {$data} array list of feed form data
     * @param {$case_id} int case id optional
     *
     * Insert or update Feed Department
     */
    public function insert_update_feed_department($data, $case_id, $adminId) {
        $update_history_data = [];
        $existingFeedBack = [];

        $depart_details = $this->basic_model->get_row('fms_feedback_department', ['case_id'], ["case_id" => $case_id]);

        $feed_ary = ['case_id' => $case_id, 'department_id' => $data['DepartmentDetails']];

        if(!$depart_details) {
            $case_id = $this->basic_model->insert_records('fms_feedback_department', $feed_ary, FALSE);
        }
        else if($depart_details) {
            #validating existing feedback
            $existingFeedBack = $this->db->get_where('tbl_fms_feedback_department', ['case_id' => $case_id], 1)->row_array();
            if (!$existingFeedBack) {
                return [
                    'status' => false,
                    'error' => 'The feed department you are trying to modify was either removed or marked as archived',
                ];
            }
            
            $update_history_data = array(
                'department_id'=> $data['DepartmentDetails'],                
            );
           
            $this->basic_model->update_records('fms_feedback_department', ['department_id' => $data['DepartmentDetails']], ['case_id' => $depart_details->case_id]);
           
            return ['case_id' => $case_id , "update_history_data" => $update_history_data , "existingFeedBack" => $existingFeedBack];
        }
    }

    /**
     * @param {$data} array list of feed form data
     * @param {$case_id} int case id optional
     *
     * Insert or update Address
     */
    public function insert_update_feed_address($data, $case_id = '') {
        if(!empty($data)) {

            if (!empty($data['address'])) {
                $addr = [];
                $address = devide_google_or_manual_address($data['address']);

                $addr = [
                    "caseId" => $case_id,
                    'address' => $address['street'] ?? '',
                    'state' => !empty($address["state"]) ? $address["state"] : null,
                    'suburb' => $address['suburb'] ?? '',
                    'postal' => $address['postcode'] ?? '',
                    'unit_number' => $data['unit_number'] ?? '',
                    'address_id' => $data['address_id'] ?? '',
                ];


                $loc_details = $this->basic_model->get_row('fms_feedback_location', ['caseId'], ["caseId" => $case_id]);

                if(!$loc_details) {
                    $case_id = $this->basic_model->insert_records('fms_feedback_location', $addr, FALSE);
                }
                else if($loc_details) {
                    #validating existing feedback
                    $existingFeedBack = $this->db->get_where('tbl_fms_feedback_location', ['caseId' => $case_id], 1)->row_array();
                    if (!$existingFeedBack) {
                        return [
                        'status' => false,
                        'error' => 'The location you are trying to modify was either removed or marked as archived',
                        ];
                    }
                   
                    $existing_state_name = $this->basic_model->get_row('state', ['name'], ["id" => $existingFeedBack["state"]]);
                    $existing_address_details = $existingFeedBack['address'] . $existingFeedBack['suburb'] . $existing_state_name->name . $existingFeedBack['postal'];

                    if(!empty($existingFeedBack['unit_number'])){
                        $existing_address_details = $existingFeedBack['unit_number'] . ', '.$existing_address_details;
                    }
                    $existingFeedBack['address'] = $existing_address_details;

                    $state_name = $this->basic_model->get_row('state', ['name'], ["id" => $address["state"]]);
                    $address_details = $address['street'] . $address['suburb'] . $state_name->name . $address['postcode'];
                   
                    $update_history_data = array(
                     'address'=> $address_details,                
                    );

                    $this->basic_model->update_records('fms_feedback_location', $addr, ['caseId' => $loc_details->caseId]);

                    return ['case_id' => $case_id , "update_history_data" => $update_history_data , "existingFeedBack" => $existingFeedBack];
                }
            }

        }
    }

    //Get reference data using by id
    public function get_reference_data_by_id($id) {

        $details = $this->basic_model->get_row('references', ['id','key_name','display_name'], ["id" => $id, "archive" => 0]);

        return ['value' => $id, 'label' => $details->display_name, 'key_name' => $details->key_name];
    }

    public function get_intiator_type_sub_query() {
        $tbl_fms = 'tbl_fms_feedback';

        return "CASE
            WHEN $tbl_fms.initiator_type = 1 THEN (
            SELECT mem.fullname FROM tbl_member mem join tbl_department as d on d.id = mem.department AND d.short_code = 'external_staff' WHERE mem.id = $tbl_fms.initiated_by LIMIT 1
            )
            WHEN $tbl_fms.initiator_type = 2 THEN (
                SELECT part.name FROM tbl_participants_master as part WHERE part.id = $tbl_fms.initiated_by LIMIT 1
            )
            WHEN $tbl_fms.initiator_type = 3 THEN (
                SELECT CONCAT(mem.firstname,' ', mem.lastname) AS name FROM tbl_member mem join tbl_department as d on d.id = mem.department AND d.short_code = 'internal_staff' WHERE mem.id = $tbl_fms.initiated_by LIMIT 1
            )
            WHEN $tbl_fms.initiator_type IN (4,5) THEN (
                SELECT o.name FROM tbl_organisation o WHERE o.id =$tbl_fms.initiated_by LIMIT 1
            )
            ELSE CONCAT($tbl_fms.Initiator_first_name, ' ',$tbl_fms.Initiator_last_name)
            END as initiated_by_name";
    }

    public function get_against_type_sub_query() {
        $tbl_fms = 'tbl_fms_feedback';
        return "CASE
            WHEN $tbl_fms.against_type = 1 THEN (
            SELECT mem.fullname FROM tbl_member mem join tbl_department as d on d.id = mem.department AND d.short_code = 'external_staff' WHERE mem.id = against.against_by LIMIT 1
            )
            WHEN $tbl_fms.against_type = 2 THEN (
                SELECT part.name FROM tbl_participants_master as part WHERE part.id = against.against_by LIMIT 1
            )
            WHEN $tbl_fms.against_type = 3 THEN (
                SELECT CONCAT(mem.firstname,' ', mem.lastname) AS name FROM tbl_member mem join tbl_department as d on d.id = mem.department AND d.short_code = 'internal_staff' WHERE mem.id = against.against_by LIMIT 1
            )
            WHEN $tbl_fms.against_type IN (4,5) THEN (
                SELECT o.name FROM tbl_organisation o WHERE o.id =against.against_by LIMIT 1
            )
            ELSE CONCAT(against.against_first_name, ' ', against.against_last_name)
            END as against_by_name";
    }
     /*
     * It is used for generating sub-query for Against category
     * return type sql
     */
    public function get_feed_category_sub_query() {
        $this->db->select("feed_ref.display_name as feed_display_name");
        $this->db->from(TBL_PREFIX . 'references as feed_ref');
        $this->db->where("feed_ref.id = tbl_fms_feedback_category.categoryId", null, false);

        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

     /*
     * It is used for generating sub-query for Against category
     * return type sql
     */
    public function get_init_category_sub_query() {
        $this->db->select("int_ref.display_name as int_display_name");
        $this->db->from(TBL_PREFIX . 'references as int_ref');
        $fms_feedback = TBL_PREFIX . 'fms_feedback';
        $this->db->where("int_ref.id = $fms_feedback.initiated_type", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * It is used for generating sub-query for Against category
     * return type sql
     */
    public function get_against_category_sub_query() {
        $this->db->select("against_ref.display_name as against_display_name");
        $this->db->from(TBL_PREFIX . 'references as against_ref');

        $this->db->where("against_ref.id = against.against_category", null, false);

        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /**
     * fetches the next number in creating shift
     */
    public function get_next_feedback_id() {
        # finding how many got added so far
        $details = $this->basic_model->get_row('fms_feedback', array("MAX(id) AS total"));
        $nextno = "1";
        if(!empty($details) && isset($details->total)) {
            $nextno = $details->total + 1;
        }
        $formatted_value = "FK".sprintf("%09d", $nextno);
        return array('status' => true, 'data' => $formatted_value);
    }
 
    public function restrict_member($data,$adminId){
        $this->load->model('sales/Account_model');
        $this->load->model('item/Participant_model');
        if(!empty($this->check_initiator_category($data['InitiatorCategory']))&&!empty($this->check_feedback_category($data['FeedCategory']))&&!empty($this->check_against_category($data['AgainstCategory'])))
        {   
             
         if(($this->check_against_category($data['AgainstCategory'])=='aga_hcm_participant'))
         {
            $account_info=new stdClass();
            $account_info->participant_id=$data['agOnCallParticipant']['value'];
            $account_info->member_id=$data['initOnCallMember']['value'];
            $result=$this->Participant_model->get_participant_member_list($account_info);
            $members=[];
            $member_details=new StdClass();
            for ($z = 0; $z < count($result['data']); $z++)
            {
                if($result['data'][$z]->member_id==$data['initOnCallMember']['value'])
                {
                   $val=new StdClass();
                   $val->status=$this->reference_table_query_by_name();
                   $member_obj=new stdClass();
                   $member_obj->label=$result['data'][$z]->fullname;
                   $member_obj->value=$result['data'][$z]->member_id;
                   $val->member_obj=$member_obj;
                   array_push($members,$val);
                   $this->shifts_related_to_account_and_member($account_info->participant_id,$result['data'][$z]->member_id,1,$adminId);
                   
                }
                else
                {
                   $val=new StdClass();
                   $val->status=$result['data'][$z]->status;
                   $member_obj=new stdClass();
                   $member_obj->label=$result['data'][$z]->fullname;
                   $member_obj->value=$result['data'][$z]->member_id;
                   $val->member_obj=$member_obj;
                   array_push($members,$val);
                    
                }
                   
            }
            $registeredMembers = array_map(function($item){
                return $item->member_id;
            }, $result['data']);
            if(empty($registeredMembers)||(array_search($data['initOnCallMember']['value'],$registeredMembers))<-1)
            {
                   $val=new StdClass();
                   $val->status=$this->reference_table_query_by_name();
                   $member_obj=new stdClass();
                   $member_obj->label=$data['initOnCallMember']['label'];
                   $member_obj->value=$data['initOnCallMember']['value'];
                   $val->member_obj=$member_obj;
                   array_push($members,$val);
                   $this->shifts_related_to_account_and_member($account_info->participant_id,$data['initOnCallMember']['value'],1,$adminId);
            }
           if(count($members)>0){
            $member_details->participant_members=$members;
            $member_details->participant_id=$data['agOnCallParticipant']['value'];
            $member_details=(array) obj_to_arr($member_details);
            $resp=$this->Participant_model->assign_participant_members($member_details,$adminId);
           }
                
         }
         else
         {
            $account_info=new stdClass();
            $account_info->account_id=($this->check_against_category($data['AgainstCategory'])=='aga_hcm_organisation')?$data['agOnCallOrganisation']['value']:$data['agOnCallSiteName']['value'];
            $account_info->member_id=$data['initOnCallMember']['value'];
            $result=$this->Account_model->get_organisation_members_list($account_info,true);
            if(count($result['data'])>0)
            {
             
                $member_details=new stdClass();
                $member_details->org_id=$result['data'][0]->organisation_id;
                $member_details->status=$this->reference_table_query_by_name();
                $member_details->id=$result['data'][0]->id;
                $member_details->member_id=$result['data'][0]->member_id;
                $member_data=(array) obj_to_arr($member_details);
                $resp=$this->Account_model->create_update_org_member($member_data,$adminId);
            }else{
                $member_details=new stdClass();
                $member_details->org_id=$account_info->account_id;
                $member_details->status=$this->reference_table_query_by_name();
                $member_details->id="";
                $member_details->member_id= $account_info->member_id;
                $member_data=(array) obj_to_arr($member_details);
                $resp=$this->Account_model->create_update_org_member($member_data,$adminId);
            }
            $this->shifts_related_to_account_and_member($account_info->account_id,$data['initOnCallMember']['value'],2,$adminId);
         }
        }
    }


    public function reference_table_query($id){
        $this->db->select("ref.key_name as name");
        $this->db->from(TBL_PREFIX . 'references as ref');
        $this->db->where("ref.id = $id");
        $this->db->limit(1);
        $query = $this->db->get();
        return $result = $query->num_rows() > 0 ? $query->result_array() : [];
    }

    public function reference_table_query_by_name(){
        $this->db->select("ref.id as id");
        $this->db->from(TBL_PREFIX . 'references as ref');
        $this->db->where("ref.display_name ='Do Not Use'");
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->num_rows() > 0 ? $query->result_array() : [];
        return $result[0]['id'];
    }

    


    public function check_feedback_category($feedbackCategoryId){
        $feedback=$this->reference_table_query($feedbackCategoryId);
        if(count($feedback)>0)
        {
         if($feedback[0]['name']=='location_not_suitable'){
             return true;
         }
        }else{
            return false;
        }
       
    }


    public function check_initiator_category($initiatorCategory){
        $initiator=$this->reference_table_query($initiatorCategory);
        if(count($initiator)>0)
        {
         if($initiator[0]['name']=='init_hcm_member'){
             return true;
         }
        }else{
            return false;
        }
       
    }


    public function check_against_category($againstCategoryId){
        $against=$this->reference_table_query($againstCategoryId);
        if(count($against)>0)
        {
         if($against[0]['name']=='aga_hcm_participant'
           ||$against[0]['name']=='aga_hcm_organisation'
           ||$against[0]['name']=='aga_hcm_site'){
             return $against[0]['name'];
         }
        }else{
            return false;
        }
       
    }

    public function shifts_related_to_account_and_member($account_id,$member_id,$account_type,$adminId){
        $this->db->select("sm.member_id, sm.status,sm.id as member_tbl_id,s.id,s.status as shift_status");
        $this->db->from(TBL_PREFIX . 'shift_member as sm');
        $this->db->join('tbl_member as m', 'm.id = sm.member_id  and m.archive = 0', 'inner');
        $this->db->join('tbl_shift as s', 's.id = sm.shift_id  and s.archive = 0', 'inner');
        $this->db->where(['m.id' => $member_id,'s.account_id'=>$account_id,'s.account_type'=>$account_type]);
        $this->db->where_in('s.status',['1','2','3']);
        $query = $this->db->get();
        $result = $query->num_rows() > 0 ? $query->result_array() : [];
         if(!empty($result)){
            foreach ($result as $val) {
                $this->update_shift_member($val['member_tbl_id'],$val['shift_status'],$val['id'],$adminId);
            }
         }

    }

    public function update_shift_member($member_tbl_id,$shift_status,$shift_id,$adminId){
        $this->load->model('schedule/Schedule_model');
        if($shift_status==3){
            $data=new stdClass();
            $data->id=$shift_id;
            $data->status=2;
           $response = $this->Schedule_model->update_shift_status( (array)$data, $adminId, true);
        }
        $where = ["id" =>$member_tbl_id];
        $update = ["archive" => 1,"updated" => DATE_TIME,"updated_by"=>$adminId,"is_restricted"=>1,"status"=>''];
         $this->basic_model->update_records("shift_member", $update, $where);
    }
         
    /**
     * fetches feed back type
     */
    public function get_feed_back_type_name($value) {
        $feed_back_type = 'N/A';
        if($value==1){
            $feed_back_type = 'Complaint';
        }else if($value==2){
            $feed_back_type = 'Reportable Incident';
        }else if($value==3){
            $feed_back_type = 'Other Feedback';
        }
        return $feed_back_type;
    }

    /**
     * fetches alert type
     */
    public function get_alert_type_name($value) {
        $alert_type = 'N/A';
        if($value==1){
            $alert_type = 'Member Alert';
        }else if($value==2){
            $alert_type = 'Organisation/Participant Alert';
        }
        return $alert_type;
    }


    /**
     * fetches against type
     */
    public function get_against_by_name($val, $feedbackid) {
        $against_val = json_decode($val);       
        $result = [];
        if($against_val->against_type==1){
            $this->db->select("mem.fullname as against_by_name");
            $this->db->from('tbl_member mem');
            $this->db->join("tbl_department as d", "d.id = mem.department and d.short_code = 'external_staff'", "left");
            $this->db->where("mem.id =".$against_val->against_by);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->result();            
        }else if($against_val->against_type==2){
            $this->db->select("part.name as against_by_name");
            $this->db->from('tbl_participants_master part');
            $this->db->where("part.id =".$against_val->against_by);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->result();

        }else if($against_val->against_type==3){
            $this->db->select("mem.fullname as against_by_name");
            $this->db->from('tbl_member mem');
            $this->db->join("tbl_department as d", "d.id = mem.department and d.short_code = 'internal_staff'", "left");
            $this->db->where("mem.id =".$against_val->against_by);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->result();

        }else if($against_val->against_type==4 || $against_val->against_type==5){
            $this->db->select("o.name as against_by_name");
            $this->db->from('tbl_organisation o');
            $this->db->where("o.id =".$against_val->against_by);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->result();

        }else{
            $this->db->select("CONCAT(f.against_first_name, ' ', f.against_last_name) as against_by_name");
            $this->db->from('tbl_fms_feedback_against_detail f');
            $this->db->where("f.caseId =".$feedbackid);
            $this->db->limit(1);
            $query = $this->db->get();            
            if ($query->num_rows) {
                $result = $query->result();
            }
        }        
        return $result;       
    }

    /**
     * fetches initiator type
     */
    public function get_initiator_type_name($val, $feedbackid) {        
        $initiated_val = json_decode($val);
        $result = [];
        if($initiated_val->initiator_type==1){
            $this->db->select("mem.fullname as initiator_name");
            $this->db->from('tbl_member mem');
            $this->db->join("tbl_department as d", "d.id = mem.department and d.short_code = 'external_staff'", "left");
            $this->db->where("mem.id =".$initiated_val->initiated_by);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->result();
        }else if($initiated_val->initiator_type==2){
            $this->db->select("part.name as initiator_name");
            $this->db->from('tbl_participants_master part');
            $this->db->where("part.id =".$initiated_val->initiated_by);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->result();

        }else if($initiated_val->initiator_type==3){
            $this->db->select("mem.fullname as initiator_name");
            $this->db->from('tbl_member mem');
            $this->db->join("tbl_department as d", "d.id = mem.department and d.short_code = 'internal_staff'", "left");
            $this->db->where("mem.id =".$initiated_val->initiated_by);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->result();

        }else if($initiated_val->initiator_type==4 || $initiated_val->initiator_type==5){
            $this->db->select("o.name as initiator_name");
            $this->db->from('tbl_organisation o');
            $this->db->where("o.id =".$initiated_val->initiated_by);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->result();

        }else{
            $this->db->select("CONCAT(f.Initiator_first_name, ' ', f.Initiator_last_name) initiator_name");
            $this->db->from('tbl_fms_feedback f');
            $this->db->where("f.id =".$feedbackid);
            $this->db->limit(1);
            $query = $this->db->get();
            if ($query->num_rows) {
                $result = $query->result();
            }
        }
        return $result;       
    }


    /**
     * Return history items of a Applications
     * @param $data object
     * @return array
     */
    public function get_field_history($data)
    {
        $items = $this->db->select(['h.id','hf.created_at', 'h.id as history_id', 'f.id as field_history_id', 'f.feedback_id','f.field', 'f.value', 'f.prev_val', 'h.created_at', 'CONCAT(m.firstname, \' \', m.lastname) as created_by', 'h.created_at', 'hf.desc as feed_title', 'hf.id as feed_id'])
            ->from(TBL_PREFIX . 'fms_feedback_history as h')
            ->where(['h.feedback_id' => $data->feedback_id])
            ->join(TBL_PREFIX . 'fms_feedback_field_history as f', 'f.history_id = h.id', 'left')
            ->join(TBL_PREFIX . 'fms_feedback_history_feed as hf', 'hf.history_id = h.id', 'left')
            ->join(TBL_PREFIX . 'member as m', 'm.uuid = h.created_by', 'left')
            ->order_by('h.id', 'DESC')
            ->get()->result();

        $feebback_status = $this->get_fms_feedback_status();
        $this->load->model('Feed_model');
        $related_type = $this->Feed_model->get_related_type('fms_feedback');
        $feed = [];
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
                case 'alert_type':   
                    $item->value = $this->get_alert_type_name($item->value);
                    $item->prev_val = $this->get_alert_type_name($item->prev_val);
                break;
                case 'feedback_type':
                    $item->value = $this->get_feed_back_type_name($item->value);
                    $item->prev_val = $this->get_feed_back_type_name($item->prev_val);
                break;
                case 'against_by':
                    $against_type_val = $this->get_against_by_name($item->value, $item->feedback_id);                  
                    $prev_against_type = $item->prev_val = $this->get_against_by_name($item->prev_val, $item->feedback_id);
                    
                    $item->value = !empty($against_type_val) ? $against_type_val[0]->against_by_name : 'N/A' ;
                    $item->prev_val = !empty($prev_against_type) ? $prev_against_type[0]->against_by_name : 'N/A';
                break;
                case 'against_category':
                    $this->db->select(['ref.display_name label', 'ref.id as value', 'type.key_name as type_name', 'ref.key_name as key_name']);
                    $this->db->from(TBL_PREFIX . 'references as ref');
                    $this->db->join(TBL_PREFIX . 'reference_data_type as type', 'ref.type = type.id', 'inner');
                    $this->db->where(["ref.id"=>$item->value, "type.key_name"=>'fms_against_category']);
                    $feed_category = $this->db->get()->result();

                    $this->db->select(['ref.display_name label', 'ref.id as value', 'type.key_name as type_name', 'ref.key_name as key_name']);
                    $this->db->from(TBL_PREFIX . 'references as ref');
                    $this->db->join(TBL_PREFIX . 'reference_data_type as type', 'ref.type = type.id', 'inner');
                    $this->db->where(["ref.id"=>$item->prev_val, "type.key_name"=>'fms_against_category']);
                    $prev_feed_category = $this->db->get()->result();
                    
                    $item->value = !empty($feed_category) ? $feed_category[0]->label : 'N/A';
                    $item->prev_val = !empty($prev_feed_category) ? $prev_feed_category[0]->label : 'N/A';
                break;
                case 'initiated_type':
                    $this->db->select(['ref.display_name label', 'ref.id as value', 'type.key_name as type_name', 'ref.key_name as key_name']);
                    $this->db->from(TBL_PREFIX . 'references as ref');
                    $this->db->join(TBL_PREFIX . 'reference_data_type as type', 'ref.type = type.id', 'inner');
                    $this->db->where(["ref.id"=>$item->value, "type.key_name"=>'fms_initiator_category']);
                    $feed_category = $this->db->get()->result();

                    $this->db->select(['ref.display_name label', 'ref.id as value', 'type.key_name as type_name', 'ref.key_name as key_name']);
                    $this->db->from(TBL_PREFIX . 'references as ref');
                    $this->db->join(TBL_PREFIX . 'reference_data_type as type', 'ref.type = type.id', 'inner');
                    $this->db->where(["ref.id"=>$item->prev_val, "type.key_name"=>'fms_initiator_category']);
                    $prev_feed_category = $this->db->get()->result();
             
                    $item->value = !empty($feed_category) ? $feed_category[0]->label : 'N/A';
                    $item->prev_val = !empty($prev_feed_category) ? $prev_feed_category[0]->label : 'N/A';
                break;
                case 'categoryId':
                    $this->db->select(['ref.display_name label', 'ref.id as value', 'type.key_name as type_name', 'ref.key_name as key_name']);
                    $this->db->from(TBL_PREFIX . 'references as ref');
                    $this->db->join(TBL_PREFIX . 'reference_data_type as type', 'ref.type = type.id', 'inner');
                    $this->db->where(["ref.id"=>$item->value, "type.key_name"=>'fms_feed_category']);
                    $feed_category = $this->db->get()->result();

                    $this->db->select(['ref.display_name label', 'ref.id as value', 'type.key_name as type_name', 'ref.key_name as key_name']);
                    $this->db->from(TBL_PREFIX . 'references as ref');
                    $this->db->join(TBL_PREFIX . 'reference_data_type as type', 'ref.type = type.id', 'inner');
                    $this->db->where(["ref.id"=>$item->prev_val, "type.key_name"=>'fms_feed_category']);
                    $prev_feed_category = $this->db->get()->result();
               
                    $item->value = !empty($feed_category) ? $feed_category[0]->label : 'N/A';
                    $item->prev_val = !empty($prev_feed_category) ? $prev_feed_category[0]->label : 'N/A';
                    break;
                case 'department_id':
                    $this->db->select(['ref.display_name label', 'ref.id as value', 'type.key_name as type_name', 'ref.key_name as key_name']);
                    $this->db->from(TBL_PREFIX . 'references as ref');
                    $this->db->join(TBL_PREFIX . 'reference_data_type as type', 'ref.type = type.id', 'inner');
                    $this->db->where(["ref.id"=>$item->value, "type.key_name"=>'fms_department_details']);
                    $feed_category = $this->db->get()->result();

                    $this->db->select(['ref.display_name label', 'ref.id as value', 'type.key_name as type_name', 'ref.key_name as key_name']);
                    $this->db->from(TBL_PREFIX . 'references as ref');
                    $this->db->join(TBL_PREFIX . 'reference_data_type as type', 'ref.type = type.id', 'inner');
                    $this->db->where(["ref.id"=>$item->prev_val, "type.key_name"=>'fms_department_details']);
                    $prev_feed_category = $this->db->get()->result();
               
                    $item->value = !empty($feed_category) ? $feed_category[0]->label : 'N/A';
                    $item->prev_val = !empty($prev_feed_category) ? $prev_feed_category[0]->label : 'N/A';
                break;
                case 'assigned_to':
                    $owner = $this->db->from(TBL_PREFIX . 'member as m')->select('CONCAT(m.firstname, \' \', m.lastname) as user')->where(['id' => $item->value])->get()->result();
                    $prev_owner = $this->db->from(TBL_PREFIX . 'member as m')->select('CONCAT(m.firstname, \' \', m.lastname) as user')->where(['id' => $item->prev_val])->get()->result();
                    $item->value = !empty($owner) ? $owner[0]->user : 'N/A';
                    $item->prev_val = !empty($prev_owner) ? $prev_owner[0]->user : 'N/A';
                break;
                case 'initiated_by':
                    $initiated_by = $this->get_initiator_type_name($item->value, $item->feedback_id);
                   
                    $prev_initiated_by = $item->prev_val = $this->get_initiator_type_name($item->prev_val, $item->feedback_id);
                   
                    $item->value = !empty($initiated_by) ? $initiated_by[0]->initiator_name : 'N/A';
                    $item->prev_val = !empty($prev_initiated_by) ? $prev_initiated_by[0]->initiator_name : 'N/A';

                break;
                case 'status':
                    foreach ($feebback_status['data'] as $key => $val){
                        if($val['value'] == $item->value){
                            $item->value = $val['label'] ?? 'New';
                            continue;
                        }
                        if($val['value'] == $item->prev_val){
                            $item->prev_val = $val['label'] ?? 'New';
                            continue;
                        }
                    }
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
        $feed = array_values($feed);
        return $feed;
    }

    /**
     * Create history item for each change field
     * @param array $existingFeedBack Existing feedback data
     * @param array $dataToBeUpdated Modified data of Lead
     * @return void
     */
    public function updateHistory($existingFeedBack, $dataToBeUpdated, $adminId) {
        if (!empty($dataToBeUpdated)) {
            $new_history = $this->db->insert(
                TBL_PREFIX . 'fms_feedback_history',
                [
                    'feedback_id' => $existingFeedBack['id'],
                    'created_by' => $adminId,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            );
            $history_id = $this->db->insert_id();
            foreach($dataToBeUpdated as $field => $new_value) {
               
                if(($field=='against_by' || $field=='initiated_by') && !empty($new_history) && array_key_exists($field, $existingFeedBack) ){

                    $against_by_match = json_decode($existingFeedBack[$field]) == json_decode($new_value);
                   
                    if(!$against_by_match){
                        $this->create_field_history($history_id, $existingFeedBack['id'], $field, $new_value, $existingFeedBack[$field]);
                    }
                    
                }else{ 

                    if (array_key_exists($field, $existingFeedBack) && $existingFeedBack[$field] != $new_value && !empty($new_history)) {
                        $this->create_field_history($history_id, $existingFeedBack['id'], $field, $new_value, $existingFeedBack[$field]);
                    }
                }
            }
        }
    }
   /**
     * Create history record to be used for all history items in the update
     * @param int $history_id Id of related update history
     * @param int $feedback_id
     * @param string $fieldName
     * @param string $new_value
     * @param string $oldValue
     * @return int Last insert id
     */
    public function create_field_history($history_id, $feedback_id, $fieldName, $newValue, $oldValue) {
        return $this->db->insert(TBL_PREFIX . 'fms_feedback_field_history', [
            'history_id' => $history_id,
            'feedback_id' => $feedback_id,
            'field' => $fieldName,
            'prev_val' => $oldValue,
            'value' => $newValue ?? ''
        ]);
    }

    /**
     * fetches all the feedback status
     */
    public function get_fms_feedback_status() {
        $data = null;
        foreach($this->fms_feedback_status as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * Updating the feedback status.
     */
    function update_feedback_status($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;
        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the feedback exist?
        $result = $this->get_fms_feedback_details($data['id']);
        $result = $result['data'];
        if (empty($result)) {
            return ['status' => false, 'error' => "Feedback does not exist anymore."];            
        }

        # updating status
        $upd_data["status"] = $data['status'];      
        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["notes_reason"] = $data['notes_reason'];
        $upd_data["notify_email"] = $data['notify_email'];
        $upd_data["is_profile_note"] = $data['is_profile_note'] ?? 0;    
        $upd_data["public_confidential_note"] = $data['public_confidential_note'] ?? 0;        
    
        $this->basic_model->update_records("fms_feedback", $upd_data, ["id" => $id]);
        
        $existingFeed = [ "id"=>$id,"status"=>$result['status']];
        
        $update_history = ["id"=>$id,"status"=>$data['status']];
        
        if(!empty($data['notes_reason'])) {
            $existingFeed = array_merge($existingFeed, ['notes_reason' => '']);
            $update_history = array_merge($update_history, ['notes_reason' => $data['notes_reason']]);
        }

        if(!empty($data['notify_email'])) {
            $existingFeed = array_merge($existingFeed, ['notify_email' => '']);
            $update_history = array_merge($update_history, ['notify_email' => $data['notify_email']]);
        }        
        
        #update fms feedback history
        $this->updateHistory($existingFeed, $update_history, $adminId);
        $msg = "Feed back status is updated successfully";
        if($data['email_notification']){
            $this->send_feedback_update_status_mail_to_user($data, $adminId);
            $msg = "Feed back status is updated and email sent successfully";
        }
       
        # adding a log entry        
        $this->add_create_update_feedback_log($upd_data, $msg, $adminId, $id);

        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }


     /*
	 * used by the create_update_feedback function to insert a log entry on
     * feedback adding / updating
     */

    public function add_create_update_feedback_log($data, $title, $adminId, $feedback_id) {
    	$this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle($title);
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($feedback_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
    }

    function get_admin_firstname_lastname($adminId)
    {
        $this->db->select(["m.firstname", "m.lastname"]);
        $this->db->from("tbl_member as m");
        $this->db->where("m.id", $adminId);
        $this->db->where("m.archive", 0);

        return $this->db->get()->row_array();
    }

     /**
     * sending bulk group booking email to applicant/applicants
     */
    function send_feedback_update_status_mail_to_user($data, $adminId)
    {
        
        $feedback_details = [];
        // feedback Details

        $feedback_details["feedback_id"] = $data['feedback_id'] ;
        $feedback_details["initiator_details"] = $data['initiator_details'] ? $data['initiator_details'] : 'N/A';
        $feedback_details["against_details"] =$data['against_details'] ? $data['against_details'] : 'N/A';
        $feedback_details["created_date"] =DateFormate($data['created'], 'd-m-Y H:i:s');   
           
        $feedback_details["notes_reason"] =$data['notes_reason'] ? $data['notes_reason'] : 'N/A';

        # grabbing admin user details
        $admin_d = $this->get_admin_firstname_lastname($adminId);
        $updated_by_name = $this->get_admin_firstname_lastname($adminId);

        $feedback_details['admin_firstname'] = $admin_d['firstname'] ?? '';
        $feedback_details['admin_lastname'] = $admin_d['lastname'] ?? '';

        $feedback_details["updated_by"] =$updated_by_name['firstname'].' '.$updated_by_name['lastname'];

        require_once APPPATH . 'Classes/Automatic_email.php';
        $obj = new Automatic_email();

        $obj->setEmail_key("fms_feedback_update_details");

        $obj->setEmail($data['notify_email']);
        $obj->setDynamic_data($feedback_details);
        $obj->setUserId($data['id']);
        $obj->setUser_type(1);

        $obj->automatic_email_send_to_user();
    }

    //Get feedback list for profile note
    public function get_member_profile_note_feedback_list($reqData) {

        $limit = $reqData->pageSize ?? 20;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? '';
        $filter = $reqData->filtered ?? '';
        $reqData = (array) $reqData;

        $updated_by_sub_query = $this->get_created_updated_by_sub_query('updated_by','fk');
        $available_column = array('id','feedback_id','notes_reason','is_profile_note','public_confidential_note','status');

        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;

                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'fk.created';
            $direction = 'DESC';
        }

        $member_id =  (!empty($reqData["member_id"])) ? $reqData["member_id"] : '';

        if(!$member_id) {
            return ['status' => FALSE, 'error' => 'Member ID missing'];
        }

        $select_columns = array('fk.id','fk.feedback_id','fk.notes_reason',
        'fk.is_profile_note','fk.public_confidential_note','fk.status');

         if (!empty($filter->filter_status) && $filter->filter_status=='1') {
            $this->db->where('fk.status', $filter->filter_status);
         }else if(isset($filter->filter_status) && (empty($filter->filter_status) || $filter->filter_status=='0')){
            $this->db->where('fk.status', $filter->filter_status);
         }

         # text search
        if (!empty($filter->search)) {

            $src_columns = array('fk.id','fk.feedback_id',
            'fk.notes_reason','fk.status', 'updated_by');


            $this->db->group_start();

            $status_array = ['0' => 'new','1' => 'In progress','2' => 'investigation','3' => 'closed','4' => 'completed'];
            
            for ($i = 0; $i < count($src_columns); $i++) {

                $column_search = $src_columns[$i];
                if($column_search == 'fk.status' && in_array(strtolower($filter->search), $status_array)) {
                    $key = array_search (strtolower($filter->search), $status_array);
                    $this->db->or_like($column_search, $key);
                }else if($column_search == 'fk.created') {
                    $formated_date = date('Y-m-d', strtotime(str_replace('/', '-', $filter->search)));
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
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $filter->search);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $filter->search);
                    }
                }

            }
            $this->db->group_end();
        }

        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select($select_columns);

        //Get Category name for both initiator and against


        $this->db->select("(
            CASE WHEN fk.status = 0 THEN 'New'
            WHEN fk.status = 1 THEN 'In progress'
            WHEN fk.status = 2 THEN 'Investigation'
            WHEN fk.status = 3 THEN 'Closed'
            WHEN fk.status = 4 THEN 'Completed'
            ELSE 'Closed' END) as status");

        $this->db->from(TBL_PREFIX . 'fms_feedback as fk');      
        $this->db->join('tbl_fms_feedback_against_detail as against', 'against.caseId = fk.id', 'left');
        $this->db->join('tbl_member m', 'm.id = fk.initiated_by OR
            m.id = against.against_by', 'left');
        $this->db->select("(" . $updated_by_sub_query . ") as updated_by");

        $this->db->where('m.id', $member_id);

        $where = '(m.id = against.against_by or m.id = fk.initiated_by)';
        $this->db->where($where);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $dataResult = $this->db->get()->result_array();
      
         // Get total rows count
         $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        return ['status' => TRUE, 'data' => $dataResult, 'count' => $dt_filtered_total,'total_item' => $total_item];
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
}
