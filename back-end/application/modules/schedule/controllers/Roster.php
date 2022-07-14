<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Roster extends MX_Controller {

    use formCustomValidation;
    

    function __construct() {
        parent::__construct();
        $this->load->model('Roster_model');
        $this->load->model('Listing_model');
        $this->load->model('common/List_view_controls_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;

        $this->loges->setLogType('roster');
        $this->date_fields = [
            'start_date',
            'end_date'
        ];

        # DB fields
        $this->date_fields = [
            'id',
            'roster_no',
            'account_type',
            'account_id',
            'owner_id',
            'start_date',
            'end_date',
            'end_date_option',
            'roster_type',
            'fundnig_type',
            'contact_id'
        ];
    }

    /*
     * For getting roster list
     */
    function get_roster_list() {
        $reqData = request_handler();
        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            foreach ($reqData->data->tobefilterdata as $filter) {
                if (in_array($filter->select_filter_field_val, $this->date_fields) && strpos($filter->select_filter_value, '/') !== false) {
                    $parts = explode(' ', $filter->select_filter_value);
                    $dt_parts = explode('/', $parts[0]);
                    $date_time = $dt_parts[2] . '-' . $dt_parts[1] . '-' . $dt_parts[0];
                    if (!empty($parts[1])) {
                        $date_time .= ' ' . $parts[1];
                    } else {
                        $date_time .= ' 00:00:00';
                    }
                    $filter->select_filter_value = $date_time;
                }
            }
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            $account_fullname_cond = "(CASE WHEN r.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = r.account_id) WHEN r.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = r.account_id) ELSE '' END) ";

            $owner_cond = "(select concat(own.firstname, ' ', own.lastname) as owner from tbl_member own where own.id = r.owner_id)";

            $filter_condition = str_replace(["id", 'account', 'owner', 'roster_no', 'status', 'stage', 'created_by_label', 'roster_type'], ["r.id", $account_fullname_cond, $owner_cond, 'r.roster_no', 'r.status', 'r.stage', "r.created_by", 'r.roster_type' ], $filter_condition);
        }
        if (!empty($reqData->data)) {
            $result = $this->Roster_model->get_roster_list($reqData->data, $filter_condition, $reqData->adminId, $reqData->uuid_user_type);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }

    /*
     * For getting reference id of create roster
     * return type json
     */
    function get_create_reference_id() {
        request_handler();

        // get reference id 
        $rows = $this->Roster_model->get_reference_id();

        if ($rows) {
            $previousRID = $rows[0]['roster_no'];
            // split the id as prefix and value
            $splitPos = 2;
            $prefix = substr($previousRID, 0, $splitPos);
            $value = substr($previousRID, $splitPos);
            $incValue = intVal($value) + 1;
             // Add  a preceeding 0 in a 8 digit
            $strPadDigits = 8;
            $str = 0;
            $incValueWPad = str_pad($incValue, $strPadDigits, $str, STR_PAD_LEFT);
            // Join two variable
            $reference_id = $prefix.$incValueWPad;
        } else {
            $reference_id = 'RT00000001';
        }

        return $reference_id;
    }

    /*
    * get reference list
    * - Roster type
    * - Roster funding type
    */
    function get_roster_reference_data() {

        $reqData = request_handler('access_schedule');
        # Get roster type
        $roster_type = $this->Roster_model->get_roster_reference_data('roster_type');

        # Get roster funding type
        $roster_funding_type = $this->Roster_model->get_roster_reference_data('roster_funding_type');

        # Get roster end date options
        $roster_end_date_options = $this->Roster_model->get_roster_end_date_options();

        # currently logged in user
        $adminId = $reqData->adminId;
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $owner_selected = array(
            'value' => $adminId,
            'label' => $adminName
        );

        $return = [ 
            'status' => true,
            'roster_type' => $roster_type,
            'roster_funding_type' => $roster_funding_type,
            'roster_end_date_options' => $roster_end_date_options,
            'owner_selected' => $owner_selected
        ];

        return $this->output->set_output(json_encode($return));
    }

    /**
     * Get contact list for accounts | particpiant
     */
    public function get_contact_for_account_roster() {
        $reqData = request_handler('access_schedule');
        if (!empty($reqData->data)) {
            $result = $this->Roster_model->get_contact_for_account_roster($reqData->data);

            # Get primary contact
            $contact_id = $this->Roster_model->get_primary_contact($reqData->data);

            $result['primary_contact'] = $contact_id;

        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        return $this->output->set_output(json_encode($result));
    }

    /**
     * Create or Update roster
     */
    function create_update_roster() {
        $reqData = request_handler('access_schedule');
        $data = $reqData->data;
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Roster_model->create_update_roster($reqData);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        return $this->output->set_output(json_encode($result));
    }

    /**
     * Retrieve roster details.
     */
    function get_roster_detail_by_id() {
        $reqData = request_handler('access_schedule');
        $data = $reqData->data;

        if (empty($data->roster_id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $this->output->set_output(json_encode($response));
        }

        # Get roster details
        $roster_id = $data->roster_id;
        $result = $this->Roster_model->get_roster_details($roster_id, $reqData->uuid_user_type);

        return $this->output->set_output(json_encode($result));
    }

    /*
     * For Docusign document list
     *
     * Return type json
     * - count
     * - data
     * - status
     */
    function get_associated_shift_list() {
        $reqData = request_handler('access_schedule');
        if (!empty($reqData)) {
            $data = $reqData->data;
            $adminId = $reqData->adminId;

           if (empty($data->roster_id) && $data->page_name=='roster') {
                $response = ['status' => false, 'error' => "Missing roster id"];
                return $this->output->set_output(json_encode($response));
            }

            if (empty($data->participant_id) && $data->page_name=='participants') {
                $response = ['status' => false, 'error' => "Missing participant id"];
                return $this->output->set_output(json_encode($response));
            }

            $result = $this->Roster_model->get_roster_shift_list($data, $adminId);
            
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($result);
        exit();
    }
}
