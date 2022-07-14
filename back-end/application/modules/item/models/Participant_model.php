<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Class : Participant_model
 * Uses : for handle query operation of participant
 *
 */
class Participant_model extends Basic_Model {

    function __construct() {
        parent::__construct();
        $this->load->model('sales/Contact_model');
        $this->load->model('../../sales/models/ServiceAgreement_model');
        $this->table_name = 'participants_master';
        $this->object_fields['name'] = 'Name';
        $this->object_fields['email'] = function($participantId = '') {
            if (empty($participantId)) {
                return 'Email';
            }
            $emails = [];
            $joins = [
                        ['left' => ['table' => 'person_email AS pe', 'on' => 'pm.contact_id = pe.person_id']]
                    ];
            $result = $this->get_rows('participants_master AS pm', ['pe.email'], ['pm.id' => $participantId], $joins );
            if (!empty($result)) {
                $emails = array_map(function($row){return $row->email;}, $result);
            }
            return $emails;
        };

    }

    /*
     * It is used to get the participant location list
     *
     * Operation:
     *  - searching
     *  - filter
     *  - sorting
     *
     * Return type Array
     */
    public function get_participant_location($participant_id) {
        $select_column = ["tla.location_id as value, tla.address as label,tla.unit_number"];
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_locations_master as tl');
        $this->db->join('tbl_location_address as tla', 'tla.location_id = tl.id', 'left');
        $this->db->where('tl.participant_id', $participant_id);
        $this->db->where('tl.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // Get the query result
        $result = $query->num_rows() > 0 ? $query->result_array() : [];
        return $result;
    }

    /*
     * It is used to get the participant list
     *
     * Operation:
     *  - searching
     *  - filter
     *  - sorting
     *
     * Return type Array
     */
    public function get_participant_list($reqData, $filter_condition = '', $uuid_user_type='') {
        // Get subqueries
        $contact_name_sub_query = $this->get_name_sub_query('tp');
        $this->load->model("Common/Common_model");
        $created_by_sub_query = $this->Common_model->get_created_by_updated_by_sub_query("tp",$uuid_user_type,"created_by");  
        $updated_by_sub_query = $this->Common_model->get_created_by_updated_by_sub_query("tp",$uuid_user_type,"updated_by");

        $limit = $reqData->pageSize ?? 99999;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        // Searching column
        $src_columns = array('name', 'contact', 'active','created_by','updated_by','DATE_FORMAT(created_at,  "%d/%m/%Y")', 'DATE_FORMAT(updated_at, "%d/%m/%Y")');
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
                }
                // else if (!empty($column_search) && empty($end_date)) {
                //     $this->db->where('DATE_FORMAT(created_at, "%Y-%m-%d") >= ', DateFormate($start_date, 'Y-m-d'));

                //     $this->db->or_like($column_search, $filter->search);
                // }
                 else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        // Sort by id
        $available_column = ["participant_id", "name", "active", "contact_id", "archive", "created_by", "created_at", "updated_by", "updated_at"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'tp.id';
            $direction = 'DESC';
        }

        // Filter by status
        if (!empty($filter->filter_status)) {
            if ($filter->filter_status === "active") {
                $this->db->where('tp.active', 1);
            } else if ($filter->filter_status === "inactive") {
                $this->db->where('tp.active', 0);
            }
        }

        $select_column = ["tp.id as participant_id", "tp.name", "tp.active", "tp.contact_id", "tp.archive", "tp.created_by", "tp.created_at", "tp.updated_by", "tp.updated_at"];
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(" . $contact_name_sub_query . ") as contact");
        $this->db->select("(" . $created_by_sub_query . ") as created_by");
        $this->db->select("(" . $updated_by_sub_query . ") as updated_by");
        $this->db->select("(CASE
            WHEN tp.active = 1 THEN 'Yes'
            WHEN tp.active = 0 THEN 'No'
			Else '' end
		) as active");
        $this->db->from('tbl_participants_master as tp');
        $this->db->where('tp.archive', 0);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
        if (!empty($filter_condition)) {
            $filter_condition = str_replace(['active', 'no', 'yes'], ['tp.active', '0', '1'], strtolower($filter_condition));
            $this->db->having($filter_condition);
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // Get total rows inserted count
        
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

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
      
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetch participant list successfully','total_item' => $total_item);
        return $return;
    }

    /**
     * function used for assigning one or more members to a participant
     */
    public function assign_participant_members($reqData, $adminId, $bulk_import = FALSE) {

        # validating is atleast one member has been selected
        $valid = false;
        $participant_members = [];
        $cnt = 0;
        $errors = null;
        if(isset($reqData['participant_members']))
        foreach ($reqData['participant_members'] as $row) {
            $valid = true;
            if(isset($row['member_obj']) && isset($row['member_obj']['value']) && !empty($row['member_obj']['value'])) {
                $participant_members[$cnt][] = $row['member_obj']['value'];
            }
            else {
                $errors[] = "Please provide the member for row-".($cnt+1);
            }
            if(isset($row['status']) && !empty($row['status'])) {
                $participant_members[$cnt][] = $row['status'];
            }
            else {
                $errors[] = "Please provide the status for row-".($cnt+1);
            }
            $cnt++;
        }

        if(empty($reqData['participant_id'])) {
            $response = [
                "status" => false,
                "error" => "participant id is missing" ];
            return $response;
        }

        # fetching existing participant member ids
        $existing_participant_member_ids = $this->get_participant_member_ids($reqData['participant_id']);

        if(!$valid && !$existing_participant_member_ids) {
            $response = [
                "status" => true,
                "error" => "Please provide atleast one support worker to assign to the participant" ];
            return $response;
        }

        if(!empty($errors)) {
            $response = [
                "status" => false,
                "error" => implode(",",$errors) ];
            return $response;
        }


        $selected_participant_member_ids = [];
        foreach ($participant_members as $row) {
            list($member_id, $status) = $row;
            $selected_participant_member_ids[] = $member_id;

            # update the existing record if it is already there
            $found_member_id = array_search($member_id,$existing_participant_member_ids);
            if($found_member_id !== FALSE) {
                $participant_member_id = $this->basic_model->update_records("participant_member", ["participant_id" => $reqData["participant_id"],"member_id" => $member_id,"status" => $status, "updated" => DATE_TIME, "updated_by" => $adminId, "archive" => 0],["member_id" => $member_id, "participant_id" => $reqData["participant_id"], "archive" => 0]);
                $participant_member_id = $member_id;
            }
            # adding an entry of participant & member
            else {
                $participant_member_id = $this->basic_model->insert_records("participant_member", ["participant_id" => $reqData["participant_id"],"member_id" => $member_id,"status" => $status, "created" => DATE_TIME, "created_by" => $adminId, "archive" => 0]);
            }

            # check $participant_member_id is not empty
            if (!$participant_member_id) {
                $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                return $response;
            }
        }

        # any existing participant member ids that are not selected this time
        # let's remove them
        $tobe_removed = array_diff($existing_participant_member_ids, $selected_participant_member_ids);
        if($tobe_removed && !$bulk_import) {
            foreach($tobe_removed as $member_id) {
                $response = $this->basic_model->update_records("participant_member",
                ["archive" => true, "updated" => DATE_TIME, "updated_by" => $adminId],
                ["member_id" => $member_id, "participant_id" => $reqData["participant_id"], "archive" => 0]);

                # check $participant_member_id is not empty
                if (!$response) {
                    $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                    return $response;
                }
            }
        }

        # adding a log entry
        $this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);

        # create log setter getter
        $this->loges->setTitle("Assigned members to participant:".$reqData['participant_id']." by " . $adminName);
        $this->loges->setDescription(json_encode($reqData));
        $this->loges->setUserId($reqData['participant_id']);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        $msg = 'Participant support worker have been assigned successfully.';
        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /**
     * fetching the participant member ids list of a given participant id
     */
    public function get_participant_member_ids($participant_id) {
        $select_column = ["p.id", "pm.member_id", "m.fullname", "pm.created", "'' as actions"];

        $this->db->select(["pm.member_id"]);
        $this->db->from('tbl_participant_member as pm');
        $this->db->join('tbl_member as m', 'm.id = pm.member_id', 'inner');
        $this->db->join('tbl_participants_master as p', 'p.id = pm.participant_id', 'inner');
        $this->db->where('pm.archive', 0);
        $this->db->where('pm.participant_id', $participant_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $participant_member_ids = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
            $participant_member_ids[] = $row->member_id;
            }
        }
        return $participant_member_ids;
    }

    /**
     * archiving participant member
     */
    function archive_participant_member($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("participant_member", $upd_data, ["id" => $id]);

        if (!$result) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }

        # adding a log entry
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully archived participant member with ID of %s by %s", $id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully archived participant member with ID of %s by %s", $id, $adminName));  // set title in log
        $this->loges->setDescription(json_encode($upd_data));
        $this->loges->setUserId($id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        $response = ['status' => true, 'msg' => "Successfully archived participant support worker"];
        return $response;
    }

    /*
     * For getting participant members list
     */
    public function get_participant_members($reqData) {
        $select_column = ["pm.id", "pm.member_id", "m.fullname", "pm.status", "pm.created"];
        $this->db->select($select_column);
        $this->db->from('tbl_participant_member as pm');
        $this->db->join('tbl_member as m', 'm.id = pm.member_id', 'inner');
        $this->db->join('tbl_participants_master as s', 's.id = pm.participant_id', 'inner');
        $this->db->where('pm.archive', 0);
        $this->db->where('pm.participant_id', $reqData['id']);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        $retres = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
                $newrow = null;
                $newrow['status'] = $row->status;
                $newrow['member_obj'] = ["label" => $row->fullname, "value" => $row->member_id];
                $retres[] = $newrow;
            }
        }
        $return = array('data' => $retres, 'status' => true);
        return $return;
    }

    /*
     * For getting participant members list
     */
    public function get_participant_member_list($reqData) {

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = ["m.fullname", "r.display_name", "DATE_FORMAT(pm.created,'%d/%m/%Y')"];
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

        # sorting part
        $available_column = ["id", "status_label", "status", "member_id", "fullname", "participant_id", "created"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'pm.id';
            $direction = 'DESC';
        }
        $select_column = ["pm.id", "r.display_name as status_label", "pm.status", "pm.member_id", "m.fullname", "pm.participant_id", "pm.created", "'' as actions"];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->from('tbl_participant_member as pm');
        $this->db->join('tbl_member as m', 'm.id = pm.member_id', 'inner');
        $this->db->join('tbl_references as r', 'r.id = pm.status', 'inner');
        $this->db->join('tbl_participants_master as p', 'p.id = pm.participant_id', 'inner');
        $this->db->where('pm.archive', 0);

        if(isset($reqData->participant_id) && $reqData->participant_id > 0)
        $this->db->where('pm.participant_id', $reqData->participant_id);

        $this->db->order_by($orderBy, $direction);
        if($limit>0){
            $this->db->limit($limit, ($page * $limit));
        }
       
    
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        // Get total rows count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetched the list successfully');
        return $return;
    }

    /*
     * it is used for making sub query of contact name
     * return type sql
     */
    private function get_name_sub_query($tbl_alais) {
        $this->db->select("CONCAT_WS(' ', sub_p.firstname,sub_p.lastname)");
        $this->db->from(TBL_PREFIX . 'person as sub_p');
        $this->db->where("sub_p.id = ".$tbl_alais.".contact_id", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for making sub query created by (who creator|updated of participant)
     * return type sql
     */
    private function get_created_updated_by_sub_query($column_by, $tbl_alais) {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.id = ".$tbl_alais.".".$column_by, null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for get person name on base of @param $contactName
     *
     * @params
     * $contactName search parameter
     *
     * return type array
     *
     */
    public function get_all_person_name_search($contactName = '') {
        $this->db->like('label', $contactName);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select(["CONCAT_WS(' ',p.firstname,p.lastname) as label", 'p.id as value', "'1' as account_type", 'pm.contact_id','p.previous_name','p.middlename']);
        $this->db->from(TBL_PREFIX . 'person as p');
        $this->db->where(['p.archive' => 0, 'pm.contact_id' => null]);
        $this->db->group_start();
        $this->db->or_where(['p.type' => 3]);
        $this->db->or_where(['p.type' => null]);
        $this->db->group_end();
        $this->db->join(TBL_PREFIX . 'participants_master as pm', 'p.id = pm.contact_id', 'left');
        $this->db->having($queryHaving);
        $sql = $this->db->get_compiled_select();

        $query = $this->db->query($sql);
        //$s=$this->db->last_query();
        $result = $query->result();
        return $result;
    }

    /*
     * To create participant
     *
     * @params {array} $data
     * @params {int} $adminId
     *
     * return type participantId
     */
    function create_participant($data, $adminId) {
        $role_id = NULL;
        if (isset($data["role_id"]) == true && $data["role_id"] != '') {
            $role_id = $data["role_id"];
        }
        // Assign the data
        $insData = [
            'name' => $data["name"],
            'contact_id' => $data["contact_id"],            
            'cost_book_id' => (isset($data["cost_book_id"]) && !empty($data["cost_book_id"])) ? $data["cost_book_id"] : null,
            'active' => $data["active"],
            'role_id' => $role_id,
            'created_by' => $adminId,
            'created_at' => DATE_TIME,
            'owner' => $data['owner']->value ?? NULL,
        ];
        // Insert the data using basic model function
        $participantId = $this->basic_model->insert_records('participants_master', $insData);

        # Add participant in service agreement
        $this->update_service_agreement_add_participant($data, $adminId, $participantId);

        return $participantId;
    }

    /*
     * Update service agreement add participant id
     *
     * @params {array} $data
     * @params {int} $adminId
     * @params {int} $participant_id
     *
     * return type service_agreement_id
     */
    function update_service_agreement_add_participant($data, $adminId, $participantId) {
        // Check the participant data
        if ($data && isset($data['service_agreement_id']) == true && $data['service_agreement_id'] != '') {

            $service_agreement_id = $data['service_agreement_id'];

            // Assign the data
            $updateData = [
                'participant_id' => $participantId,
                'updated_by' => $adminId,
                'updated' => DATE_TIME,
            ];
            // Update the data
            $this->db->where($where = array('id' => $service_agreement_id));
            $serviceAgreementId = $this->db->update(TBL_PREFIX . 'service_agreement', $updateData);
            return $participantId;
        } else {
            return '';
        }
    }

    /*
     *
     * @params {object} $reqData
     *
     * Return type Array - $result
     */
    public function get_participant_detail_by_id($reqData, $uuid_user_type=null) {
        if (isset($reqData) && isset($reqData->participant_id)) {
            // Get subquery of cerated & updated by
            $this->load->model("Common/Common_model");
            $created_by_sub_query = $this->Common_model->get_created_by_updated_by_sub_query("tp",$uuid_user_type,"created_by"); 
            $updated_by_sub_query = $this->Common_model->get_created_by_updated_by_sub_query("tp",$uuid_user_type,"updated_by");

            $participant_id = $reqData->participant_id;
            $column = ["tp.id as participant_id", "tp.name", "tp.contact_id", "tp.active", "tp.created_by", "tp.created_at", "tp.updated_by", "tp.updated_at", "CONCAT_WS(' ', sub_p.firstname,sub_p.lastname) as contact", "sub_p.ndis_number", "r.name as role_label", "tp.role_id", "tp.cost_book_id", "rf.display_name as cost_book_name", "sub_p.profile_pic as avatar","sub_p.middlename", "sub_p.previous_name"];
            $orderBy = "tp.id";
            $orderDirection = "DESC";
            $this->db->select($column);

            $this->db->select("(" . $created_by_sub_query . ") as created_by");
            $this->db->select("(" . $updated_by_sub_query . ") as updated_by");
            $this->db->select("(CASE
                WHEN tp.active = 1 THEN 'Yes'
                WHEN tp.active = 0 THEN 'No'
                Else '' end
            ) as active");
            $this->db->from(TBL_PREFIX . 'participants_master as tp');
            $this->db->join(TBL_PREFIX . 'person as sub_p', "sub_p.id = tp.contact_id", "LEFT");
            $this->db->join(TBL_PREFIX . 'member_role as r', 'r.id = tp.role_id', 'LEFT');
            $this->db->join(TBL_PREFIX . 'references as rf', 'rf.id = tp.cost_book_id', 'left');
            $this->db->order_by($orderBy, $orderDirection);
            $this->db->where(['tp.id' => $participant_id]);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->num_rows() > 0 ? $query->result_array() : [];
            $result = $query->num_rows() > 0 ? $result[0] : [];

            return [ "status" => true, 'data' => $result, 'msg' => 'Fetch participant detail successfully' ];
        } else {
            return [ "status" => false, 'error' => 'Participant Id is null'];
        }
    }

    /*
     *
     * @params {object} $reqData
     *
     * Return type Array - $result
     */
    public function get_participant_data_by_id($reqData) {
        if (isset($reqData) && isset($reqData->participant_id)) {
            // Get subquery of cerated & updated by
            $created_by_sub_query = $this->get_created_updated_by_sub_query('created_by','tp');
            $updated_by_sub_query = $this->get_created_updated_by_sub_query('updated_by','tp');

            $participant_id = $reqData->participant_id;
            $column = ["tp.id as participant_id", "tp.name", "tp.contact_id", "tp.active", "tp.created_by", "tp.created_at", "tp.updated_by", "tp.updated_at", "CONCAT_WS(' ', sub_p.firstname,sub_p.lastname) as contact", "sub_p.ndis_number", "r.name as role_label", "tp.role_id", "tp.cost_book_id", "rf.display_name as cost_book_name", "sub_p.lastname", "sub_p.middlename","sub_p.previous_name"];
            $orderBy = "tp.id";
            $orderDirection = "DESC";
            $this->db->select($column);

            $this->db->select("(" . $created_by_sub_query . ") as created_by");
            $this->db->select("(" . $updated_by_sub_query . ") as updated_by");
            $this->db->select("(CASE
                WHEN tp.active = 1 THEN 'Yes'
                WHEN tp.active = 0 THEN 'No'
                Else '' end
            ) as active");
            $this->db->from(TBL_PREFIX . 'participants_master as tp');
            $this->db->join(TBL_PREFIX . 'person as sub_p', "sub_p.id = tp.contact_id", "LEFT");
            $this->db->join(TBL_PREFIX . 'member_role as r', 'r.id = tp.role_id', 'LEFT');
            $this->db->join(TBL_PREFIX . 'references as rf', 'rf.id = tp.cost_book_id', 'left');
            $this->db->order_by($orderBy, $orderDirection);
            $this->db->where(['tp.id' => $participant_id]);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->num_rows() > 0 ? $query->result_array() : [];
            $result = $query->num_rows() > 0 ? $result[0] : [];

            return [ "status" => true, 'data' => $result, 'msg' => 'Fetch participant detail successfully' ];
        } else {
            return [ "status" => false, 'error' => 'Participant Id is null'];
        }
    }

    /**
     * adding/updating participant details
     */
    function create_update_participant($data, $adminId) {
        $this->load->model('Goal_model');
        $participant_id = !empty($data['participant_id']) ? $data['participant_id'] : 0;
        $checklist_opportunity = $data['checklist_opportunity']?? null;
        $sa_id =!empty($data['service_agreement_id'])??null;
        // Validation rules set
        $validation_rules = [
            array('field' => 'name', 'label' => 'Name', 'rules' => 'required|callback_alpha_dash_space'),
            array('field' => 'active', 'label' => 'Active', 'rules' => 'required', "errors" => [ "required" => "Active value is null"]),
        ];

        if(!$participant_id)
            $validation_rules[] = array('field' => 'contact_id', 'label' => 'Contact', 'rules' => 'required');

        // Set data in libray for validation
        $this->form_validation->set_data($data);

        // Set validation rule
        $this->form_validation->set_rules($validation_rules);

        // Check data is valid or not
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return ['status' => false, 'error' => implode(', ', $errors)];
        }
        $this->load->model('Basic_model');

        // Check participant is exist. Using title
        $name = $data['name'];
        $where['name'] = $name;
        if($participant_id)
            $where["id !="] = $participant_id;
        $colown = array('id', 'name');
        $check_participant = $this->basic_model->get_record_where('participants_master', $colown, $where);

        // If exists
        if ($check_participant)
            return ['status' => false, 'error' => 'Participant already exist'];

        if($participant_id) {
            // Call update participant model
            $changed_participant_id = $this->Participant_model->update_participant($data, $adminId);
        }
        else {
            // Call create participant model
            $changed_participant_id = $this->Participant_model->create_participant($data, $adminId);
        }

        // Check $changed_participant_id is not empty
        if (!$changed_participant_id)
            return ['status' => false, 'error' => system_msgs('something_went_wrong')];

        // Create logs. it will represent the user action they have made.
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        if($participant_id)
            $this->loges->setTitle("Participant updated for " . $participant_id ." by " . $adminName);
        else
            $this->loges->setTitle("New participant created for " . $data['name'] ." by " . $adminName);
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($adminId);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
        //updating participant for goals related to service_agreement_id
        if($sa_id)
        {
            $this->Goal_model->update_participant_id_to_sa_goals($data['service_agreement_id'],$changed_participant_id);
        }
     
        if($changed_participant_id && !empty($checklist_opportunity)) {
            //update participant safety checklist
            $this->update_records("opportunity_staff_saftey_checklist", ["participant_id" => $changed_participant_id, "updated_at" => DATE_TIME, "updated_by" => $adminId, "archive" => 0],["opportunity_id" => $checklist_opportunity->value]);
            $response = ['status' => true, 'msg' => 'Participant has been updated successfully.'];
        } else {
            $response = ['status' => true, 'msg' => 'Participant has been created successfully.'];
        }

        return $response;
    }

    /*
     * For edit participant
     *
     * @params {array} $data
     * @params {int} $adminId
     *
     * return type participant_id
     */
    function update_participant($data, $adminId) {
        // Check the participant data
        if ($data && $data['participant_id']) {
            $role_id = NULL;
            if (isset($data["role_id"]) == true && $data["role_id"] != '') {
                $role_id = $data["role_id"];
            }
            // Assign the data
            $updateData = [
                'name' => $data["name"],                
                'active' => $data["active"],
                'cost_book_id' => (isset($data["cost_book_id"]) && !empty($data["cost_book_id"])) ? $data["cost_book_id"] : null,
                'role_id' => $role_id,
                'updated_by' => $adminId,
                'updated_at' => DATE_TIME,
                'owner' => ($data['owner'] ?? [])['value'] ?? NULL,
            ];
            // Update the data
            $this->db->where($where = array('id' => $data['participant_id']));
            $participantId = $this->db->update(TBL_PREFIX . 'participants_master', $updateData);
            return $participantId;
        } else {
            return '';
        }
    }
	/**
     * Return participant ids matching to person name
     */
    public function get_participant_ids_by_contact($filter_condition) {
        $this->db->distinct();
        $this->db->select("tp.contact_id");
        $this->db->from("tbl_participants_master as tp");
        $this->db->join(TBL_PREFIX . 'person as p', 'p.id = tp.contact_id', 'inner');
        $this->db->where($filter_condition);
        $query = $this->db->get();
        return $query->result_array();
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
     * it is used for making sub query of participant name
     * return type sql
     */
    private function get_participant_sub_query($tbl_alais, $column_alias) {
        $this->db->select("tpm.name");
        $this->db->from(TBL_PREFIX . 'participants_master as tpm');
        $this->db->where("tpm.id = ".$tbl_alais.".".$column_alias, null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }
    /*
     * it is used to get the participant document list
     * return type sql
     */
    public function get_participant_document_list($reqData, $is_portal = false) {
        // Get subqueries
        $document_name_sub_query = $this->get_document_name_sub_query('tda');
        $participant_name_sub_query = $this->get_participant_sub_query('tda', 'entity_id');
        $participant_updated_by_sub_query = $this->get_participant_sub_query('tda', 'updated_by');
        $participant_created_by_sub_query = $this->get_participant_sub_query('tda', 'created_by');

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $participant_id = $reqData->participant_id ?? '';
        $orderBy = '';
        $direction = '';

        // where document type is
        $where_entity_type = [];
        $where_entity_id = [];

        require_once APPPATH . 'Classes/document/DocumentAttachment.php';

        $docAttachObj = new DocumentAttachment();

        // Get constant
        $entityTypeParticipant = $docAttachObj->getConstant('ENTITY_TYPE_PARTICIPANTS');
        $draftStatus = $docAttachObj->getConstant('DOCUMENT_STATUS_DRAFT');

        if ($participant_id != '') {
          $where_entity_type[] = $entityTypeParticipant;
          $where_entity_id[] = $participant_id;
        }

        // Searching column
        $src_columns = array('file_name', 'file_type', 'status', 'reference_number','DATE(issue_date)','DATE(expiry_date)','DATE(attached_on)','DATE(updated_on)' );
        if (isset($filter->search) && $filter->search != '') {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (!empty($filter->filter_by) && $filter->filter_by != 'all' && $filter->filter_by != $column_search) {
                    continue;
                }
                $formated_date = '';
                if($column_search=='DATE(issue_date)' || $column_search=='DATE(expiry_date)'
                        || $column_search=='DATE(attached_on)' || $column_search=='DATE(updated_on)'){
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
                    } else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $filter->search);
                    }
                }

            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        // Sort by id
        $available_column = ["id", "doc_type_id", "entity_id", "entity_type", "document_id", "document_status", "member_id", "archive", 
                             "issue_date", "expiry_date", "reference_number", "created_by", "created_at", "updated_by", "updated_at", "file_name", 
                             "draft_contract_type", "applicant_id","file_type","file_size","raw_name","file_ext", "attached_on", "updated_on",
                             "aws_uploaded_flag","converted_name","uri_param_1", "file_path", "system_gen_flag"
                            ];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'tda.id';
            $direction = 'DESC';
        }

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
            "CONCAT('mediaShow/m/', tda.id, '/', REPLACE(TO_BASE64(tdap.file_path), '=', '%3D%3D'), '?download_as=', REPLACE(tdap.raw_name, ' ', ''), '&s3=true') AS file_path",
            "tdt.system_gen_flag",
        ];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        // Query
        $this->db->select("(CASE
          WHEN tda.draft_contract_type = 1 THEN CONCAT('Draft Contract_', tpm.name)
          WHEN tda.draft_contract_type = 2 THEN CONCAT('Employment Contract_', tpm.name)
                else
                    CASE
                        WHEN tdt.title IS NOT NULL THEN
                            CONCAT(tdt.title,'_',, tpm.name)
                        ELSE
                            tpm.name
                        END
                end) as download_as
        ");

        $this->db->select("
          CASE
            WHEN tda.entity_type = 1 THEN
            (
              CASE
              WHEN tda.draft_contract_type = 1 THEN CONCAT('Draft Contract_', tpm.name)
              WHEN tda.draft_contract_type = 2 THEN CONCAT('Employment Contract_', tpm.name)
              ELSE
                  CASE
                      WHEN tdt.title IS NOT NULL THEN
                          CONCAT(tdt.title,'_',, tpm.name)
                      ELSE
                            tpm.name
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
            (" . $participant_name_sub_query . ")
         as participant");
        $this->db->select("
            (" . $participant_created_by_sub_query . ")
         as created_by");
         $this->db->select("
            (" . $participant_updated_by_sub_query . ")
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
        $this->db->join("tbl_participants_master as tpm", "tpm.id = tda.entity_id AND tda.entity_type=".$entityTypeParticipant, "LEFT");
        $this->db->where('tda.archive', 0);
        if ($is_portal == false) {
          $this->db->where('tda.document_status !=', $draftStatus);
        }
        if ($participant_id != '' && $participant_id != 'null') {
            $this->db->group_start();
            $this->db->where("tda.entity_type", $entityTypeParticipant);
            $this->db->where("tda.entity_id", $participant_id);
            $this->db->group_end();
        }
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // echo last_query();
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
        $this->db->where("tda.archive",0);
        if ($participant_id != '' && $participant_id != 'null') {
            $this->db->group_start();
            $this->db->where("tda.entity_type", $entityTypeParticipant);
            $this->db->where("tda.entity_id", $participant_id);
            $this->db->group_end();
        }
        // Get total rows inserted count
        $document_row = $this->db->get()->row_array();

        $document_count = intVal($document_row['count']);

        return array('count' => $dt_filtered_total, 'document_count' => $document_count, 'data' => $result, 'status' => true, 'msg' => 'Fetch member document list successfully');
    }

    /**
     * Get list of service agreement associate with participants
     * @param {int} participant_id
     */
    public function get_service_agreement_linked($participant_id)
    {
        $response = $this->db->select([
            'sa.id',
            "(
                CASE
                    WHEN sa.status = 0 THEN 'Draft'
                    WHEN sa.status = 1 THEN 'Issued'
                    WHEN sa.status = 2 THEN 'Approved'
                    WHEN sa.status = 3 THEN 'Accepted'
                    WHEN sa.status = 4 THEN 'Declined'
                    WHEN sa.status = 5 THEN 'Active'
                END
            ) as status_label",
            'sa.contract_start_date',
            'topic',
            'sa.plan_start_date'
        ])
            ->from('tbl_service_agreement as sa')
            ->join('tbl_opportunity', 'tbl_opportunity.id = sa.opportunity_id')
            ->where(['sa.participant_id' => $participant_id, 'sa.archive' => 0])
            ->get()->result();

        $result = ['status' => true, 'msg' => 'List successfully Fetched', 'data' => $response, 'count' => 0];
        return $result;
    }

    /**
     * @param $agrDetails {obj} Service agreement details
     * @return $data {array} Service Agreement type
     *
     * Get the docusign details which is added against participant
     */
    public function get_service_agreement_linked_docu_sign($agrDetails) {
        if(!empty($agrDetails)) {
            $agr_details = [];
            $docu_sign_details = [];
            
            foreach($agrDetails as $res) {

                $details = $this->ServiceAgreement_model->get_service_agreement_details($res->id, 1);
                if(isset($details) && $details['data']) {
                    $agr_details[] = $details['data'];
                    $docu_sign_details = $details['data']['service_docusign_datas'];
                }

            }

            if(!empty($agr_details)) {
                $data = ['status' => TRUE, 'msg' => 'List successfully Fetched',
                    'data' => $agr_details, 'count' => count($docu_sign_details)];
            } else {
                $data = ['status' => TRUE, 'msg' => 'List successfully Fetched', 'data' => [], 'count' => 0];
            }

        } else {
            $data = ['status' => TRUE, 'msg' => 'No docusign found', 'data' => [], 'count' => 0];
        }

        return $data;
    }

    /**
     * Get list of service agreement associate with participants
     * @param {int} participant_id
     */
    public function get_service_agreement_documents($participant_id, $filter = null)
    {
        // $this->load->model('../../sales/models/ServiceAgreement_model');
        $sa_types = $this->ServiceAgreement_model->sa_types;
        //fetch all service agreements ids of participant
        $sas = $this->get_service_agreement_linked($participant_id);
        //arrange all sa with ids
        $sas_with_ids = [];
        $sa_docs = [];
        if (!empty($sas['data'])) {
            foreach($sas['data'] as $sa) {
                $sas_with_ids[$sa->id] = $sa;
            }
            $ids = array_keys($sas_with_ids);
            $docs = $this->ServiceAgreement_model->service_docusign_datas($ids, null, $filter);            
            if (!empty($docs)) {
                foreach($docs as $sa_doc) {
                    if (array_key_exists($sa_doc->document_type, $sa_types)) {
                        $sa_doc->doc_type = $sa_types[$sa_doc->document_type];
                        $sa_doc->plan_start_date = $sas_with_ids[$sa_doc->service_agreement_id]->plan_start_date;
                        $sa_docs[] = $sa_doc;
                    }
                }
            }
        }
        
        $result = ['status' => true, 'msg' => 'List successfully Fetched', 'data' => $sa_docs, 'count' => 0];
        return $result;
    }
}
