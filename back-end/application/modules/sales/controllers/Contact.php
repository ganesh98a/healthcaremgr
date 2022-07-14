<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * class : Contact
 * use : use for handle contact request and response
 *
 * @property-read \Contact_model $Contact_model
 */
class Contact extends MX_Controller {

    // load custon validation traits function
    use formCustomValidation;

    // defualt contruct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        $this->loges->setLogType('crm');
        $this->load->library('UserName');
        // load contact model
        $this->load->model('Contact_model');
        $this->load->model('../../common/models/List_view_controls_model');
        $this->load->helper('message');
        $this->load->helper('i_pad');
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # HCM- 3485, adding all requests to backend in a log file
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }

    /*
     * used : load defualt option in create contact
     * input : no
     * response : state list, source list and contact type
     * return type json
     */

    function get_option_for_create_contact() {
        $reqData = request_handler('access_crm');
        $this->load->model("common/Common_model");

        // get state
        $res["stateList"] = $this->Common_model->get_state();

        // get source option
        $this->load->model("Lead_model");
        $res["source_option"] = $this->Lead_model->get_lead_source();

        // get contact type option
        $res["contact_type_option"] = $this->Contact_model->get_contact_type_option();

        // Get gender option
        $res["gender_option"] = $this->Contact_model->get_gender_option();

        echo json_encode(["status" => true, "data" => $res]);
    }

    /*
     * its use for validate contact email when create contact
     * check contact email like not empty, valid email address
     *
     * @params
     * $defualt: no use
     * its default paramter for defualt validaiton
     *
     * @$reqData reqData of create contact
     *
     *
     * set error in duflat validation libray
     * return : true, false
     */

    function check_contact_email_address($defualt, $reqData) {
        $reqData = json_decode($reqData);

        if (!empty($reqData->EmailInput)) {
            foreach ($reqData->EmailInput as $val) {
                if (!empty($val->email)) {

                    // check email address formate is valid or not
                    if (!filter_var($val->email, FILTER_VALIDATE_EMAIL)) {
                        $this->form_validation->set_message('check_contact_email_address', $val->email . ' this email address is not valid');
                        return false;
                    }
                }
            }
        }
    }

    /*
     * its is use for create/update contact
     * handle request form front-end side
     * validate request and respone according to request
     *
     * return type json
     */

    function create_update_contact() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = $data['id'] ?? 0;

            $existingContactId = $data['id'] ?? 0;

            // validation rule
            $validation_rules = [
                array('field' => 'lastname', 'label' => 'Last Name', 'rules' => 'required|max_length[40]', 'errors' => ['max_length' => "%s field cannot exceed 40 characters."]),
                array('field' => 'address', 'label' => 'street', 'rules' => 'callback_check_string_google_address_is_valid'),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required|in_list[0,1]'),
                array('field' => 'ndis_number', 'label' => 'NDIS number', 'rules' => 'trim|callback__check_ndis_number_already_been_used['. $existingContactId .']')
            ];

            // callback for email address
            if (!empty($reqData->data->EmailInput)) {
                $validation_rules[] = array('field' => 'EmailInput[]', 'label' => 'Email', 'rules' => 'callback_check_contact_email_address[' . json_encode($data) . ']');
            }

            // callback for phone number
            if (!empty($reqData->data->PhoneInput)) {
                $validation_rules[] = array('field' => 'PhoneInput[]', 'label' => 'Phone', 'rules' => 'callback_phone_number_check[phone,,Please enter valid phone number.]');
            }

            // set data in libray for validate
            $this->form_validation->set_data($data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {

                // call create / update contact model
                $contactId = $this->Contact_model->create_update_contact($data, $adminId);

                // check $contactId is not empty
                // according to that got contact is created or not
                if ($contactId) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);
                    $first_name = $data['firstname'] ?? '' ;
                    $last_name = $data['lastname'] ?? '';
                    // create log setter getter
                    if ($data["contactId"]) {
                        $this->loges->setTitle("New contact created for " . $first_name . " " . $last_name . " by " . $adminName);  // set title in log
                        $msg = "Contact has been updated successfully.";
                    } else {
                        $this->loges->setTitle("Contact updated of " . $first_name . " " . $last_name . " by " . $adminName);  // set title in log
                        $msg = "Contact has been created successfully.";
                    }

                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($contactId);
                    $this->loges->setCreatedBy($adminId);
                    $this->loges->createLog(); // create log

                    $response = ['status' => true, 'msg' => $msg, 'contactId' => $contactId];
                } else {
                    $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
        exit();
    }

    function update_contact_for_member_portal() {
        $reqData = request_handler();
        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData)) {
            $data = (array) $reqData->data;            
            // validation rule
            $validation_rules = [
                array('field' => 'phone', 'label' => 'Phone', 'rules' => 'callback_phone_number_check[phone,,Please enter valid phone number.]'),              
                array('field' => 'person_id', 'label' => 'Id', 'rules' => 'required'),              
            ];

            // set data in libray for validate
            $this->form_validation->set_data($data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {

                // call create / update contact model
                $contactId = $this->Contact_model->update_contact_for_member_portal($data);

                // check $contactId is not empty
                // according to that got contact is created or not
                if ($contactId) {
                    $response = ['status' => true, 'msg' => 'Profile has been updated successfully.', 'contactId' => $contactId];
                } else {
                    $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
        exit();
    }

    /*
     * its use for get contact list
     * handle request form fron-end
     *
     * return type json
     * array('count' => $dt_filtered_total, 'data' => $result, 'status' => true)
     */

    function get_contact_list() {        
        $reqData = request_handler('access_crm');
        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData, true);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            if (!empty($filter_condition)) {
                $filter_condition = str_replace(['type', 'status'], ['p.type', 'p.status'], $filter_condition);
            }
        }
        if (!empty($reqData->data)) {
            // call model for get contact list
            $result = $this->Contact_model->get_contact_list($reqData, $filter_condition);
            echo json_encode($result);
            exit();
        }
    }

    /*
     * its use for get contact details
     *
     * return type json
     */

    function get_contact_details() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData->data->contactId)) {

            // call model for get contact details
            $result = $this->Contact_model->get_contact_details($reqData->data->contactId);
            $response = ["status" => true, "data" => $result];
        } else {
            $response = ["status" => false, "error" => "Contact id not found"];
        }

        echo json_encode($response);
        exit();
    }

    /*
     * its use for archive contact
     * return type json
     */

    function archive_contact() {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;


        if (!empty($data["id"])) {
            $org_id = $data['id'];

            $this->Contact_model->archive_account($org_id);

            $this->load->library('UserName');
            $adminName = $this->username->getName('admin', $adminId);

            $this->loges->setTitle(sprintf("Successfully archived contact with ID of %s by %s", $org_id, $adminName));
            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($org_id);
            $this->loges->setCreatedBy($adminId);
            $this->loges->createLog();

            $response = ['status' => true, 'msg' => "Contact successfully archived"];
        } else {
            $response = ['status' => false, 'error' => "Contact id not found"];
        }

        echo json_encode($response);
    }


    /**
     * Check if NDIS number was already been used by another active account
     * @param string $value
     * @param int $dont_validate_this_person_id
     * @return bool
     */
    public function _check_ndis_number_already_been_used($value, $dont_validate_this_person_id = 0)
    {
        $ndis = preg_replace("/\s+/", '', $value);
        if (!$ndis) {
            return true;
        }

        $STATUS_ACTIVE = 1;

        $query = $this->db
                ->from('tbl_person AS p')
                ->where([
                    'p.ndis_number' => $ndis,
                    'p.status' => $STATUS_ACTIVE,
                    'p.archive' => 0,
                ])
                ->where_not_in('p.id', [$dont_validate_this_person_id])
                ->select(['p.*'])
                ->get();

        $numRows = $query->num_rows();
        if ($numRows > 0) {
            $this->form_validation->set_message(__FUNCTION__, sprintf("The NDIS number '%s' was already been taken by another active contact", $value));
            return false;
        }

        return true;
    }


    /*
     * its use for contact deails
     * return type json
     */

    function get_contact_details_for_view() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData->data->contactId)) {

            // call model for get contact details
            $result = $this->Contact_model->get_contact_details($reqData->data->contactId);

            if (empty($result) OR ( !empty($result) && $result['archive'] == 1)) {
                $response = ["status" => false, "error" => "Contact is not found"];
            } else {
                $req = ["source_data_type" => 1, "destination_data_type" => 1, "source_data_id" => $result['id']];
                $result["contacts"] = $this->Contact_model->get_sales_relation_linked_items($req);

                $req2 = ["source_data_type" => 1, "destination_data_type" => 3, "source_data_id" => $result['id']];
                $result["opportunitys"] = $this->Contact_model->get_sales_relation_linked_items($req2);

                $req2 = ["source_data_type" => 1, "destination_data_type" => 2, "source_data_id" => $result['id']];
                $result["organisations"] = $this->Contact_model->get_sales_relation_linked_items($req2);

                // populate contact with contact roles information
                $SOURCE_DATA_TYPE_PERSON = 1;
                $result['account_contact_roles'] = $this->Contact_model->get_account_contact_roles_by_source_id($result['id'], $SOURCE_DATA_TYPE_PERSON);
                $result['contacts'] = $this->Contact_model->merge_account_contact_roles_with_contact(
                    obj_to_arr($result['contacts']),
                    $result['account_contact_roles'],
                    $SOURCE_DATA_TYPE_PERSON
                );

                $response = ["status" => true, "data" => $result];
            }
        } else {
            $response = ["status" => false, "error" => "Contact id not found"];
        }

        echo json_encode($response);
        exit();
    }

    function get_option_task_field_ralated_to() {
        $reqData = request_handler('access_crm');

        if ($reqData->data->search) {
            $result = $this->Contact_model->get_option_task_field_ralated_to($reqData->data);

            $response = ["status" => true, "data" => $result];
        } else {
            $response = ["status" => true, "data" => []];
        }

        echo json_encode($response);
    }

    function get_option_task_field_name() {
        $reqData = request_handler('access_crm');

        if ($reqData->data->search) {
            $result = $this->Contact_model->get_option_task_field_name($reqData->data);

            $response = ["status" => true, "data" => $result];
        } else {
            $response = ["status" => true, "data" => []];
        }

        echo json_encode($response);
    }

    function get_option_of_contact_name_search() {
        $reqData = request_handler();
        #pr($reqData);
        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'sales_type', 'label' => 'Last Name', 'rules' => 'required'),
                array('field' => 'salesId', 'label' => 'street', 'rules' => 'required'),
            ];

            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {
                if(isset($reqData->data->sales_type) && $reqData->data->sales_type == 'lead'){
                    $result["contact_option"] = $this->Contact_model->get_option_of_lead_name_search($reqData->data);
                }else{
                    $result["contact_option"] = $this->Contact_model->get_option_of_contact_name_search($reqData->data);
                }

                $result["related_to"] = $this->Contact_model->get_default_related_to_field_value_label($reqData->data);
                if(isset($reqData->data->assign_to)){
                    $result["assign_to"] = $this->Contact_model->get_option_of_assign_to_field_value_label($reqData->data);
                }

                $response = ["status" => true, "data" => $result];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            $response = ["status" => false, "error" => "search key is required"];
        }

        echo json_encode($response);
    }

    function task_due_date_check($date) {
        if (isset($date) && $date != "") {
            $datediff = strtotime($date)- strtotime(date("Y-m-d"));
            $difference = floor($datediff/(60*60*24));
            if($difference==0)
            {
               return true;
            }else if (strtotime($date) < strtotime("now") ) {
            $this->form_validation->set_message('task_due_date_check', '{field} must be a future date.');
            return false;
            }
        }
        return true;
    }

    /**
     * getting contact accounts list
     */
    public function get_contact_accounts_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Contact_model->get_contact_accounts_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting contact list for accounts
     */
    public function get_contact_for_account() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $allow_new_contact = $reqData->data->new_contact ?? true;
            $result = $this->Contact_model->get_contact_for_account($reqData->data, $allow_new_contact);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    function create_task_for_contact() {
        $reqData = request_handler('access_crm');

        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'task_name', 'label' => 'Task Name', 'rules' => 'required'),
                array('field' => 'due_date', 'label' => 'Due Date', 'rules' => 'callback_task_due_date_check'),
                array('field' => 'assign_to', 'label' => 'assign to', 'rules' => 'required'),
                array('field' => 'salesId', 'label' => 'salesId', 'rules' => ''),
                array('field' => 'sales_type', 'label' => 'sales type', 'rules' => 'required'),
            ];

            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {

                $taskId = $this->Contact_model->create_task_for_contact($reqData->data, $reqData->adminId);

                $this->load->library('UserName');
                $adminName = $this->username->getName('admin', $reqData->adminId);

                $this->loges->setTitle("Added new task " . $taskId . " by " . $adminName);
                $this->loges->setDescription(json_encode($reqData->data));
                $this->loges->setUserId($taskId);
                $this->loges->setCreatedBy($reqData->adminId);
                $this->loges->createLog();

                $response = ["status" => true, "msg" => "Task has been created successfully."];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
    }

    function update_task_for_contact() {
        $reqData = request_handler('access_crm');

        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'task_name', 'label' => 'Task name', 'rules' => 'required'),
                // array('field' => 'due_date', 'label' => 'Due Date', 'rules' => 'callback_task_due_date_check'),
                array('field' => 'assign_to', 'label' => 'assign to', 'rules' => 'required'),
                // array('field' => 'salesId', 'label' => 'salesId', 'rules' => 'required','errors' => ["required" => "Related To field can't be empty"]),
                // array('field' => 'sales_type', 'label' => 'sales type', 'rules' => 'required'),
            ];

            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {

                $taskId = $this->Contact_model->update_task_for_contact($reqData->data, $reqData->adminId);

                $this->load->library('UserName');
                $adminName = $this->username->getName('admin', $reqData->adminId);

                $this->loges->setTitle("updated task " . $taskId . " by " . $adminName);
                $this->loges->setDescription(json_encode($reqData->data));
                $this->loges->setUserId($taskId);
                $this->loges->setCreatedBy($reqData->adminId);
                $this->loges->createLog();

                $response = ["status" => true, "msg" => "Task has been updated successfully."];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
    }


    public function get_contact_options()
    {
        $reqData = request_handler('access_crm');
        $this->output->set_output('json');
        $q = $reqData->data->query ?? '';
        $limit = $reqData->data->limit ?? '';
        $rows = $this->Contact_model->get_contact_options($q, $limit);
        return $this->output->set_output(json_encode($rows));
    }


    public function get_account_contact_role_options()
    {
        $reqData = request_handler('access_crm');
        $this->output->set_content_type('json');

        $data = $reqData->data;
        $account_type = obj_to_arr($data)['account_type'] ?? 0;

        $account_types_to_ref_type = [
            1 => '10', // Person
            2 => '11', // Org
        ];

        $options = $this->Contact_model->get_account_contact_role_options($account_types_to_ref_type[$account_type]);
        return $this->output->set_output(json_encode([
            'status' => true,
            'data' => $options,
        ]));
    }


    function create_call_log_for_contact() {
        $reqData = request_handler('access_crm');

        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'subject', 'label' => 'subject', 'rules' => 'required'),
                array('field' => 'comment', 'label' => 'comment', 'rules' => 'required'),
                array('field' => 'contactId', 'label' => 'contactId', 'rules' => 'required'),
               /*array('field' => 'related_to', 'label' => 'related_to', 'rules' => 'required'),
                array('field' => 'related_type', 'label' => 'related_type', 'rules' => 'required'),*/
                array('field' => 'salesId', 'label' => 'salesId', 'rules' => 'required'),
                array('field' => 'sales_type', 'label' => 'sales type', 'rules' => 'required'),
            ];

           
            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {

                $activityId = $this->Contact_model->create_call_log_for_contact($reqData->data, $reqData->adminId);

                $this->load->library('UserName');
                $adminName = $this->username->getName('admin', $reqData->adminId);

                $this->loges->setTitle("Added new call log " . $activityId . " by " . $adminName);
                $this->loges->setDescription(json_encode($reqData->data));
                $this->loges->setUserId($activityId);
                $this->loges->setCreatedBy($reqData->adminId);
                $this->loges->createLog();

                $response = ["status" => true, "msg" => "Call has been created successfully."];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
    }
    /**
     *  Create note activity
     **/
    function create_note_for_activity() {
        $reqData = request_handler('access_crm');

        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'title', 'label' => 'title', 'rules' => 'required'),
                array('field' => 'description', 'label' => 'description', 'rules' => 'required'),
                array('field' => 'salesId', 'label' => 'salesId', 'rules' => 'required'),
                array('field' => 'sales_type', 'label' => 'sales type', 'rules' => 'required'),
            ];
            if ($reqData->data->sales_type == "application") {
                unset($validation_rules[0]);
                $validation_rules[] = array('field' => 'note_type', 'label' => 'Note Type', 'rules' => 'required');
            }
            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {

                $activityId = $this->Contact_model->create_note_for_activity($reqData->data, $reqData->adminId);

                $this->load->library('UserName');
                $adminName = $this->username->getName('admin', $reqData->adminId);

                $this->loges->setTitle("Added new activity note " . $activityId . " by " . $adminName);
                $this->loges->setDescription(json_encode($reqData->data));
                $this->loges->setUserId($activityId);
                $this->loges->setCreatedBy($reqData->adminId);
                $this->loges->createLog();

                $response = ["status" => true, "msg" => "Note has been created successfully."];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
    }

    function get_acitvity_as_per_entity_id_and_type(){
         $reqData = request_handler();

        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'salesId', 'label' => 'salesId', 'rules' => 'required'),
                array('field' => 'sales_type', 'label' => 'sales type', 'rules' => 'required'),
            ];

            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {

                $result["activity_timeline"] = $this->Contact_model->get_acitvity_as_per_entity_id_and_type($reqData->data);

                $response = ["status" => true, "data" => $result];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
    }

    /*
     * Mark as completed the task
     * return type json
     */
    public function complete_task()
    {
        $reqData = request_handler();
        $adminId = $reqData->adminId;
        if (!empty($reqData->data)) {
            $reqData = ($reqData->data);
            $response = $this->Contact_model->complete_task($reqData->task_id, $adminId);
            echo json_encode($response);
        }
    }
    public function get_selectedfilter_contacts()
    {

        $reqData = request_handler('access_crm');

        $filterdatas = array_filter(json_decode(json_encode($reqData->data->tobefilterdata??[]), true));
        $limit = $reqData->data->pageSize ?? 9999;
        $page = $reqData->data->page ?? 0;
        $filter_logic = $reqData->data->filter_logic ?? null;
        $filter_operand_length = $reqData->data->filter_operand_length ?? 0;
        $filter_list_id = $reqData->data->filter_list_id ?? null;
        $uuid_user_type = $reqData->uuid_user_type;
         if(!empty($filter_logic)){
            $this->load->model("Common/common_model");
            $filter_logic_result = $this->common_model->validate_filter_logic($filter_logic);
             if(!$filter_logic_result){               
                $return_res = ["data"=>[] ,"status" => false, 'msg'=>'filter_error' ,"error" => "Invalid filter format"];
                echo json_encode($return_res);
                exit();
             }else{
                $result =  $this->Contact_model->get_selectedfilter_contacts($filterdatas,$limit,$page,$filter_logic,$filter_operand_length, $uuid_user_type);
                if($result['status'] && $reqData->data->save_filter_logic){
                    $this->List_view_controls_model->update_filter_by_id($filter_list_id,$filter_logic,$filter_operand_length,$filterdatas, $reqData->adminId, $uuid_user_type);
                    echo json_encode($result);
                    exit();
                }else{
                    echo json_encode($result);
                    exit();
                }

             }
         }else{

            $result =  $this->Contact_model->get_selectedfilter_contacts($filterdatas,$limit,$page,$filter_logic,$filter_operand_length);
            if($result['status'] && $reqData->data->save_filter_logic){
                $this->List_view_controls_model->update_filter_by_id($filter_list_id,$filter_logic,$filter_operand_length,$filterdatas, $reqData->adminId);
                echo json_encode($result);
                exit();
            }else{
                echo json_encode($result);
                exit();
            }


         }
    }

    /*
     * its use for get contact details by id
     *
     * return type json
     */

    function get_contact_details_by_id() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData->data->contactId)) {

            // call model for get contact details
            $result = $this->Contact_model->get_contact_details_by_id($reqData->data->contactId);
            $response = ["status" => true, "data" => $result];
        } else {
            $response = ["status" => false, "error" => "Contact id not found"];
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Get contact data for sms - SMS
     * - Activity tab (Contact Detail)
     */
    function get_contact_data_for_sms() {
        $reqData = request_handler('access_crm'); 
        $response = ['status' => true, 'data' => ''];       
        if (!empty($reqData->data)) {
            $response = $this->Contact_model->get_contact_data_for_sms($reqData->data);
        }
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }
}
