<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Class : Participant_location_model
 * Uses : for handle query operation of participant
 *
 */
class Participant_location_model extends CI_Model {

    function __construct() {

        parent::__construct();
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
    public function get_participant_location_list($reqData) {
        // Get subqueries
        $participant_name_sub_query = $this->get_participant_sub_query('tl');
        $created_by_sub_query = $this->get_created_updated_by_sub_query('created_by','tl');
        $updated_by_sub_query = $this->get_created_updated_by_sub_query('updated_by','tl');

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $participant_id = $reqData->participant_id ?? '';
        $orderBy = '';
        $direction = '';

        // Searching column
        $src_columns = array('name', 'address', 'active');
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
        $available_column = ["id", "location_id", "name", "active", "participant_id", "archive", "created_by", 
                             "created_at", "updated_by", "updated_at", "address","unit_number"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'tl.id';
            $direction = 'DESC';
        }

        // Filter by status
        if (!empty($filter->filter_status)) {
            if ($filter->filter_status === "active") {
                $this->db->where('tl.active', 1);
            } else if ($filter->filter_status === "inactive") {
                $this->db->where('tl.active', 0);
            }
        }

        $select_column = ["tl.id", "tl.id as location_id", "tl.name", "tl.active", "tl.participant_id", "tl.archive", "tl.created_by", "tl.created_at", "tl.updated_by", "tl.updated_at", "CONCAT(tla.unit_number,',',tla.address) as address","tla.unit_number"];
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(" . $participant_name_sub_query . ") as participant");
        $this->db->select("(" . $created_by_sub_query . ") as created_by");
        $this->db->select("(" . $updated_by_sub_query . ") as updated_by");
        $this->db->select("(CASE  
            WHEN tl.active = 1 THEN 'Yes'
            WHEN tl.active = 0 THEN 'No'
			Else '' end
		) as active");
        $this->db->from('tbl_locations_master as tl');
        $this->db->join('tbl_location_address as tla', 'tla.location_id = tl.id', 'left');
        $this->db->where('tl.participant_id', $participant_id);
        $this->db->where('tl.archive', 0);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // Get total rows inserted count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count')->row()->count;

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

        // Get total rows inserted count
        $location_row = $this->db->query('SELECT COUNT(*) as count from tbl_locations_master where participant_id = '.$participant_id.' AND archive = 0')->row_array();
        $location_count = intVal($location_row['count']);

        $return = array('count' => $dt_filtered_total, 'location_count' => $location_count, 'data' => $result, 'status' => true, 'msg' => 'Fetch location list successfully');
        return $return;
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
        $select_column = ["tla.location_id as value, tla.address as label,"];
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
     * it is used for making sub query of participant name
     * return type sql
     */
    private function get_participant_sub_query($tbl_alais) {
        $this->db->select("tpm.name");
        $this->db->from(TBL_PREFIX . 'participants_master as tpm');
        $this->db->where("tpm.id = ".$tbl_alais.".participant_id", null, false);
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
        $this->db->where("sub_m.uuid = ".$tbl_alais.".".$column_by, null, false);
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
    public function get_all_participant_name_search($contactName = '') {  
        $this->db->like('label', $contactName);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select(["tpm.name as label", 'tpm.id as value']);
        $this->db->from(TBL_PREFIX . 'participants_master as tpm');
        $this->db->where(['tpm.archive' => 0]);
        $this->db->having($queryHaving);
        $sql = $this->db->get_compiled_select();
        
        $query = $this->db->query($sql);

        return $result = $query->result();
    }

    /* 
     * To create participant location &address
     * 
     * @params {array} $data
     * @params {int} $adminId
     * 
     * return type participantId
     */
    function create_participant_location($data, $adminId) {
        /**
         * Insert location
         */
        $insData = [
            'name' => $data["name"],
            'participant_id' => $data["participant_id"],
            'active' => $data["active"],
            'description' => $data["description"] ?? '',
            'created_by' => $adminId,
            'created_at' => DATE_TIME,
        ];
        // Insert the data using basic model function
        $locationId = $this->basic_model->insert_records('locations_master', $insData);

        if (!$locationId) {
            return ['status' => false, 'error' => "Location is not created. something went wrong"];
        }

        /**
         * Insert location address
         */
        $address = trim($data["location"]);
        $insert_address = [];
        $insert_address['address'] = $address;
        $insert_address['created_by'] = $adminId;
        $insert_address['created_at'] = DATE_TIME;
        if($address != "") {
            $addr = [];
            $address = devide_google_or_manual_address($address);
            $addr = [
                'street' => $address['street'] ?? '',
                'state' => !empty($address["state"]) ? $address["state"] : null,
                'suburb' => $address['suburb'] ?? '',
                'postcode' => $address['postcode'] ?? '',
            ];
            $insert_address['street'] = $addr['street'];
            $insert_address['state'] = $addr['state'];
            $insert_address['suburb'] = $addr['suburb'];
            $insert_address['postcode'] = $addr['postcode'];
            $insert_address['unit_number'] = !empty($data['unit_number']) ? $data['unit_number'] : '';
        }
        $insert_address['location_id'] = $locationId;
        // get lat, lng
        $geometryLoc = $data["geometryLocation"] ?? '';
        if($geometryLoc != "") {
            $insert_address['lat'] = $geometryLoc->lat;
            $insert_address['lng'] = $geometryLoc->lng;
        }
        // Insert the data using basic model function
        $locationAddressId = $this->basic_model->insert_records('location_address', $insert_address);

        if (!$locationAddressId) {
            return ['status' => false, 'error' => "Location Address is not created. something went wrong"];
        }

        return ['status' => true, 'msg' => "Location created successfully", 'location_id' => $locationId];
    }

    /*
     * 
     * @params {object} $reqData
     * 
     * Return type Array - $result
     */
    public function get_participant_location_data_by_id($reqData) {
        if (isset($reqData) && isset($reqData->location_id)) {
            // Get subquery of cerated & updated by
            $created_by_sub_query = $this->get_created_updated_by_sub_query('created_by','tl');
            $updated_by_sub_query = $this->get_created_updated_by_sub_query('updated_by','tl');

            $location_id = $reqData->location_id;
            $column = ["tl.id", "tl.id as location_id", "tl.name", "tl.active", "tl.participant_id", "tl.description", "tl.archive", "tl.created_by", "tl.created_at", "tl.updated_by", "tl.updated_at", "tla.id as location_address_id", "tla.address" , "tla.lat", "tla.lng","tla.unit_number", "tpm.name as participant_name"];
            $orderBy = "tl.id";
            $orderDirection = "DESC";
            $this->db->select($column);
            
            $this->db->select("(" . $created_by_sub_query . ") as created_by");
            $this->db->select("(" . $updated_by_sub_query . ") as updated_by");
            $this->db->select("(CASE  
                WHEN tl.active = 1 THEN 'Yes'
                WHEN tl.active = 0 THEN 'No'
                Else '' end
            ) as active");
            $this->db->from(TBL_PREFIX . 'locations_master as tl');
            $this->db->join(TBL_PREFIX . 'location_address as tla', "tl.id = tla.location_id AND tla.archive = 0", "LEFT");
            $this->db->join(TBL_PREFIX . 'participants_master as tpm', "tpm.id = tl.participant_id", "LEFT");
            $this->db->order_by($orderBy, $orderDirection);
            $this->db->where(['tl.id' => $location_id]);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->num_rows() > 0 ? $query->result_array() : [];
            $result = $query->num_rows() > 0 ? $result[0] : [];
            
            return [ "status" => true, 'data' => $result, 'msg' => 'Fetch location detail successfully' ];
        } else {
            return [ "status" => false, 'error' => 'Location Id is missing'];
        }
    }

    /* 
     * To edit participant location
     * 
     * @params {array} $data
     * @params {int} $adminId
     * 
     * return type location_id
     */
    function edit_participant_location($data, $adminId) {
        // Check the participant data
        if ($data && $data['location_id']) {
            /**
             * Update location
             */
            $updateData = [
                'name' => $data["name"],
                'participant_id' => $data["participant_id"],
                'active' => $data["active"],
                'description' => $data["description"] ?? '',
                'updated_by' => $adminId,
                'updated_at' => DATE_TIME,
            ];
            // Insert the data using basic model function
            $where = array('id' => $data['location_id']);
            $locationId = $this->basic_model->update_records('locations_master', $updateData, $where);

            if (!$locationId) {
                return ['status' => false, 'error' => "Location is not updated. something went wrong"];
            }

            /**
             * Update location address
             */
            $address = trim($data["location"]);
            $updated_address = [];
            $updated_address['address'] = $address;
            $updated_address['updated_by'] = $adminId;
            $updated_address['updated_at'] = DATE_TIME;
            if($address != "") {
                $addr = [];
                $address = devide_google_or_manual_address($address);
                $addr = [
                    'street' => $address['street'] ?? '',
                    'state' => !empty($address["state"]) ? $address["state"] : null,
                    'suburb' => $address['suburb'] ?? '',
                    'postcode' => $address['postcode'] ?? '',
                ];
                $updated_address['street'] = $addr['street'];
                $updated_address['state'] = $addr['state'];
                $updated_address['suburb'] = $addr['suburb'];
                $updated_address['postcode'] = $addr['postcode'];
                $updated_address['unit_number'] = !empty($data['unit_number']) ? $data['unit_number'] : '';
            }
            $updated_address['location_id'] = $data['location_id'];
            // get lat, lng
            $geometryLoc = $data["geometryLocation"] ?? '';
            if($geometryLoc != "") {
                $updated_address['lat'] = $geometryLoc->lat;
                $updated_address['lng'] = $geometryLoc->lng;
            }
            
            // Update the data using basic model function
            $where = array('id' => $data['location_address_id']);
            $locationAddressId = $this->basic_model->update_records('location_address', $updated_address, $where);
            
            if (!$locationAddressId) {
                return ['status' => false, 'error' => "Location Address is not updated. something went wrong"];
            }

            return ['status' => true, 'msg' => "Location updated successfully", 'location_id' => $locationId];

        } else {
            return [ "status" => false, 'error' => 'Location Id is missing'];
        }
    }
}

    
