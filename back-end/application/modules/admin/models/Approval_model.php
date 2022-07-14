<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Approval_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    public function get_list_approval($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';

        $src_columns = array('id','person_name', 'created_date','user_type_name','approval_area','created_time','approval_area'//,
        //'concat(tbl_participant.firstname," ",tbl_participant.middlename," ",tbl_participant.lastname)', 
        //'concat(tbl_participant.firstname," ",tbl_participant.lastname)', 'concat(tbl_member.firstname," ",tbl_member.middlename," ",tbl_member.lastname)', 
        //'concat(tbl_member.firstname," ",tbl_member.lastname)'
    );

        if (isset($filter->search) && $filter->search != "") {
            $this->db->group_start();

            for ($i = 0; $i < count($src_columns); $i++) { {
                    $column_search = $src_columns[$i];
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $filter->search);
                    }
                    elseif ($column_search != 'null') {
                        $this->db->or_like($column_search, $filter->search);
                    }
                }
            }

            $this->db->group_end();
        }

        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $available_colomn = array('id', 'userId', 'created', 'user_type', 'approval_area', 'pin');
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_colomn) ) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $this->db->order_by('pin', 'desc');
            $orderBy = 'created';
            $direction = 'desc';
        }

        if (!empty($filter->on_date)) {
            $this->db->where('Date(tbl_approval.created)', DateFormate($filter->on_date, 'Y-m-d'));
        }


        $colowmn = array('tbl_approval.id', 'tbl_approval.userId', 'tbl_approval.created', 'tbl_approval.user_type', 'tbl_approval.approval_area', 'tbl_approval.pin');
        $colowmn_other= array("case 
        WHEN user_type=2 THEN ( SELECT concat(tbl_participant.firstname,' ',tbl_participant.middlename,' ',tbl_participant.lastname) FROM tbl_participant WHERE tbl_participant.id = tbl_approval.userId )
        WHEN user_type=1 THEN ( SELECT concat(tbl_member.firstname,' ',tbl_member.middlename,' ',tbl_member.lastname) FROM tbl_member WHERE tbl_member.id = tbl_approval.userId )
        ELSE '' END as person_name
        ",
        "CASE WHEN user_type=2 THEN 'My HCM' ELSE 'Members App' END as user_type_name",
        "DATE_FORMAT(tbl_approval.created,'%d/%m/%Y') as created_date",
        "DATE_FORMAT(tbl_approval.created,'%h:%i %p') as created_time"
    );

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $colowmn)), false);
        $this->db->select($colowmn_other, false);
        $this->db->from(TBL_PREFIX . 'approval');
        //$this->db->join('tbl_participant', 'tbl_participant.id = tbl_approval.userId', 'left');
        //$this->db->join('tbl_member', 'tbl_member.id = tbl_approval.userId', 'left');


        $this->db->order_by($orderBy, $direction);

        $this->db->limit($limit, ($page * $limit));

        $this->db->where('tbl_approval.status', 0);
        $this->db->where('tbl_approval.archive', 0);
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
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
                $val->approval_area = approval_mapping($val->approval_area, 'description');
            }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $dataResult);


        return $return;
    }

    function get_approve_data($approveId) {
        $colowmn = array('id', 'userId', 'created', 'user_type', 'approval_area', 'approval_content', 'status');
        $this->db->select($colowmn);
        $this->db->from(TBL_PREFIX . 'approval');
        $this->db->where('id', $approveId);
        $this->db->where('archive', 0);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $result = $query->row();

        if (!empty($result)) {
            if ($result->user_type == 2) {
                $where = array('id' => $result->userId);
                $colown = array('concat(firstname," ",middlename,"",lastname) as username');
                $usename = $this->basic_model->get_row('participant', $colown, $where);
                $result->username = $usename->username;
            } else {
                $where = array('id' => $result->userId);
                $colown = array('concat(firstname," ",middlename,"",lastname) as username');
                $usename = $this->basic_model->get_row('member', $colown, $where);
                $result->username = $usename->username;
            }
        }

        return (array) $result;
    }

    function get_participant_place($participantId) {
        $colowmn = array('participantId', 'type', 'placeId');
        $this->db->select($colowmn);
        $this->db->from(TBL_PREFIX . 'participant_place');


        $this->db->where('participantId', $participantId);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $query->result();
    }
    
    function get_member_place($memberId) {
        $colowmn = array('memberId', 'type', 'placeId');
        $this->db->select($colowmn);
        $this->db->from(TBL_PREFIX . 'member_place');


        $this->db->where('memberId', $memberId);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $query->result();
    }
    
    

    function get_place() {
        $colowmn = array('id', 'name');
        $this->db->select($colowmn);
        $this->db->from(TBL_PREFIX . 'place');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $result = $query->result();

        $place = array();
        if (!empty($result)) {
            foreach ($result as $val) {
                $place[$val->id] = $val->name;
            }
        }

        return $place;
    }

    function get_activity() {
        $colowmn = array('id', 'name');
        $this->db->select($colowmn);
        $this->db->from(TBL_PREFIX . 'activity');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        $activiy = array();
        if (!empty($result)) {
            foreach ($result as $val) {
                $activiy[$val->id] = $val->name;
            }
        }

        return $activiy;
    }

    function get_participant_activiy($participantId) {
        $colowmn = array('participantId', 'type', 'activityId');
        $this->db->select($colowmn);
        $this->db->from(TBL_PREFIX . 'participant_activity');


        $this->db->where('participantId', $participantId);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $query->result();
    }

    function get_participant_asistance($participantId, $type) {
        $colowmn = array('assistanceId', 'type', 'participantId');
        $this->db->select($colowmn);
        $this->db->from(TBL_PREFIX . 'participant_assistance');
        $this->db->where('type', $type);


        $this->db->where('participantId', $participantId);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $query->result();
    }

    function get_genral_requirement() {
        $colowmn = array('id', 'name');
        $this->db->select($colowmn);
        $this->db->from(TBL_PREFIX . 'participant_genral');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $result = $query->result();

        $requirement = array();
        if (!empty($result)) {
            foreach ($result as $val) {
                $requirement[$val->id] = $val->name;
            }
        }

        return $requirement;
    }

    function get_participant_support_required($participantId) {
        $colowmn = array('support_required', 'participantId');
        $this->db->select($colowmn);
        $this->db->from(TBL_PREFIX . 'participant_support_required');

        $this->db->where('participantId', $participantId);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $query->result();
    }

    function get_participant_oc_service($participantId) {
        $colowmn = array('oc_service', 'participantId');
        $this->db->select($colowmn);
        $this->db->from(TBL_PREFIX . 'participant_oc_services');

        $this->db->where('participantId', $participantId);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $query->result();
    }

    function get_participant_address($participantId) {
        $colowmn = array('street', 'city', 'postal', 'state', 'site_category');
        $this->db->select($colowmn);
        $this->db->from(TBL_PREFIX . 'participant_address');
        $this->db->where('participantId', $participantId);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $query->result();
    }

    function get_state() {
        $colowmn = array('id', 'name');
        $this->db->select($colowmn);
        $this->db->from(TBL_PREFIX . 'state');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        $state = array();
        if (!empty($result)) {
            foreach ($result as $val) {
                $state[$val->id] = $val->name;
            }
        }

        return $state;
    }

    function add_to_pin($reqData) {
        $status = $reqData->status == 1 ? 0 : 1;

        $where = array("id" => $reqData->approvalId,"archive"=>0);
        $data = array("updated" => DATE_TIME, "pin" => $status);
        $this->basic_model->update_records('approval', $data, $where);
    }

}
