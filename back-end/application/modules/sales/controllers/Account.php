<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * class : Contact
 * use : use for handle contact request and response  
 * 
 * @property-read \Account_model $Account_model
 * @property-read \Contact_model $Contact_model
 */
class Account extends MX_Controller {

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
        $this->load->model('Account_model');
        $this->load->helper('message');
        $this->load->model('common/List_view_controls_model');
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
     * its use for get company name by given @ACN and @ABN number
     * 
     * @Input ABN/ACN
     * 
     * @return type json
     * @return ["status" => true, "data" => $abn_acn_result]
     */

    function get_abn_acn_number_on_base_search() {
        $reqData = request_handler('access_crm');

        if (!empty($reqData->data->search)) {
            $search = $reqData->data->search;
            $this->load->library('abn_search');

            $abn_found = false;
            if ($search) {
                $srch_record = $this->abn_search->search_name_by_abn_number($reqData->data->search);

                $rows = array();
                if (!empty($srch_record)) {
                    $srch_record = str_replace('callback(', '', $srch_record);
                    $srch_record = substr($srch_record, 0, strlen($srch_record) - 1); //strip out last paren
                    $object = json_decode($srch_record); // stdClass object

                    if (!empty($object->EntityName)) {
                        $abn_found = true;
                        $abn_acn_result = array('abn' => $object->Abn, 'account_name' => $object->EntityName);
                        $res = ["status" => true, "data" => $abn_acn_result];
                    } else {
                        $res = ["status" => false, "error" => "ABN number not found"];
                    }
                }
            }

            if (!$abn_found) {
                $srch_record = $this->abn_search->search_name_by_acn_number($reqData->data->search);

                $rows = array();
                if (!empty($srch_record)) {
                    $srch_record = str_replace('callback(', '', $srch_record);
                    $srch_record = substr($srch_record, 0, strlen($srch_record) - 1); //strip out last paren
                    $object = json_decode($srch_record); // stdClass object

                    if (!empty($object->EntityName)) {
                        $abn_acn_result = array('abn' => $object->Abn, 'account_name' => $object->EntityName);
                        $res = ["status" => true, "data" => $abn_acn_result];
                    } else {
                        $res = ["status" => false, "error" => "ACN/ABN number not found"];
                    }
                }
            }
        } else {
            $res = ["status" => false, "data" => "ABN/ACN is mission"];
        }

        echo json_encode($res);
    }

    /*
     * its use for get account list
     * handle request form fron-end
     * 
     * return type json
     * array('count' => $dt_filtered_total, 'data' => $result, 'status' => true)
     */

    function get_account_list() {
        $reqData = request_handler('access_crm');
        $filter_condition = '';
        if (!empty($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData, true);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            if (!empty($filter_condition)) {
                $filter_condition = str_replace(['created','primary_contact_name'], ['o.created',"concat_ws(' ',pp.firstname,pp.lastname)"], $filter_condition);
            }
        }
        if (!empty($reqData->data)) {
            // call model for get account list
            $result = $this->Account_model->get_account_list($reqData, $filter_condition);
            echo json_encode($result);
            exit();
        }
    }
    public function get_account_list_names() {
        $reqData = request_handler('access_organization');
        $reqData->data = json_decode($reqData->data);
        $post_data = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->Account_model->get_account_list_names($post_data);
        echo json_encode($rows);
    }
    public function get_account_contacts() {
        $reqData = request_handler('access_organization');
        $reqData->data = $reqData->data;
        // $post_data = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->Account_model->get_account_contacts($reqData->data);
        echo json_encode($rows);
    }

    /*
     * its use for check ABN number already exist
     * #error set in default codeIgniter library errors
     * 
     * 
     * @parmas 
     * $abn ABN/ACN number
     * 
     * 
     * return type true / false
     */

    function check_abn_number_should_be_uniqe($abn, $org_id) {
        if ($abn) {
            $res = $this->Account_model->check_abn_number_should_be_uniqe($abn, $org_id);

            if (!empty($res)) {
                $this->form_validation->set_message('check_abn_number_should_be_uniqe', 'This ABN/ACN number already exist');
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    /*
     * its use for check account name already exist
     * 
     * @params $account_name
     * 
     * return type boolean
     * true / false
     */
    function check_account_name_should_be_uniqe($account_name, $org_id) {
        if ($account_name) {
            $res = $this->Account_model->check_account_name_should_be_uniqe($account_name, $org_id);

            if (!empty($res)) {
                $this->form_validation->set_message('check_account_name_should_be_uniqe', 'This Account name already exist');
                return false;
            } else {
                return true;
            }
        } else {
            $this->form_validation->set_message('check_account_name_should_be_uniqe', 'Account name is required');
            return false;
        }
    }

    /*
     * its use for create/update organisation
     * handle request for create organisation
     * 
     * @input reqData of organisation
     * 
     * return type json
     */
    function create_organisation() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->Account_model->create_organisation($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetches the next site number for a given parent org
     */
    public function get_next_site_no() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $id = $data['id']?? 0;
            $result = $this->Account_model->get_next_site_no($id);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * fetching the reference data of account roles
     */
    function get_account_roles() {
        $reqData = request_handler();
        if (!empty($reqData->data) && !empty($reqData->data->account_type)) {
            $result = $this->Account_model->get_account_roles($reqData->data->account_type);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting sub organisations list
     */
    public function get_sub_organisation_details() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->Account_model->get_sub_organisation_details($data['id'], $data['is_site'], $reqData->data, $reqData->uuid_user_type);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting organisation contacts list
     */
    public function get_account_contacts_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Account_model->get_account_contacts_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting organisation contacts list for selection
     */
    public function get_account_contacts_selection() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Account_model->get_account_contacts_selection(obj_to_arr($reqData->data));
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting organisation sites list for selection
     */
    public function get_account_sites_selection() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Account_model->get_account_sites_selection(obj_to_arr($reqData->data));
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * its use for get option of account
     * for patent account
     * 
     * @return type json
     * 
     */
    function get_option_of_account() {
        $reqData = request_handler('access_crm');

        if (!empty($reqData->data)) {

            $result = $this->Account_model->get_option_of_account($reqData->data->search, $reqData->data->type);

            echo json_encode(["status" => true, "data" => $result]);
            exit();
        }
    }

    /*
     * its used for gettting account(org) names on base of @param $ownerName
     */
    public function account_name_search($ownerName = '')
    {
        $reqData = request_handler('access_crm');
        if (!empty($reqData->data)) {
            $result = $this->Account_model->get_option_of_account($reqData->data->query, $reqData->data->type);
            echo json_encode($result);
            exit();
        }
    }

    /*
     * its use for archive organisation
     * return type json 
     */
    function archive_account() {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;

        if (!empty($data["id"])) {
            $org_id = $data['id'];
            $response = $this->Account_model->archive_account($org_id, $adminId);
        } else {
            $response = ['status' => false, 'error' => "Organisation id not found"];
        }

        echo json_encode($response);
    }

    /*
     * its use for get organisation/account details
     * return type json
     */
    function get_organisation_details() {
        $reqData = request_handler(false, 0);
        if (empty($reqData->data->org_portal)) {
            $reqData = request_handler('access_crm');
        }
        if (!empty($reqData->data->org_id)) {
            $org_id = $reqData->data->org_id;

            $det = $this->Account_model->get_organisation_details($org_id);

            $response = ['status' => true, 'data' => $det];
        } else {
            $response = ['status' => false, 'error' => "org id is required"];
        }

        echo json_encode($response);
        exit();
    }

    /*
     * getting org account details for pre-selection
     */
    function get_organisation_account_details() {
        $reqData = request_handler('access_crm');

        if (!empty($reqData->data->org_id)) {
            $org_id = $reqData->data->org_id;
            $det = $this->Account_model->get_organisation_account_details($org_id);
            $response = ['status' => true, 'data' => $det];
        } else {
            $response = ['status' => false, 'error' => "org id is required"];
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Mark account contact as archived.
     */
    public function archive_account_contact() {
        $reqData = request_handler();
        $adminId = $reqData->adminId;

        $data = (array) $reqData->data;
        $response = $this->Account_model->archive_account_contact($data, $adminId);
        echo json_encode($response);
        exit();
    }

    /**
     * Mark organisation member as archived.
     */
    public function archive_organisation_member() {
        $reqData = request_handler();
        $adminId = $reqData->adminId;

        $data = (array) $reqData->data;
        $response = $this->Account_model->archive_organisation_member($data, $adminId);
        echo json_encode($response);
        exit();
    }

    /*
     * For getting organisation members list
     */
    function get_organisation_members_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Account_model->get_organisation_members_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetching a single organisation member details
     */
    function get_organisation_member_details() {
        $reqData = request_handler('access_member');
        $data = $reqData->data;

        if (empty($data->id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        $result = $this->Account_model->get_organisation_member_details($data->id);
        echo json_encode($result);
        exit();
    }

    /*
     * its used for create/update account/org member association
     */
    function create_update_org_member() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (empty($reqData->data)) {
            echo json_encode($response);
            exit();
        }

        $data = (array) $reqData->data;
        $response = $this->Account_model->create_update_org_member($data, $adminId);
        echo json_encode($response);
        exit();
    }

    public function save_account_contact_roles()
    {
        $reqData = request_handler('access_crm');
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->Account_model->save_account_contact_roles($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }


    /*
     * its use for get view details of account
     * its handle request of view details
     * 
     * return type json
     */

    function get_account_details_for_view() {
        $reqData = request_handler();
		if (empty($reqData->data->org_portal)) {
            $reqData = request_handler('access_crm');
        }
        $adminId = $reqData->adminId;
        $uuid_user_type = $reqData->uuid_user_type;
        require_once APPPATH . 'Classes/admin/permission.php';
        $obj_permission = new classPermission\Permission();
        $result = $obj_permission->check_permission($adminId, "access_crm_admin");
        $permission = false;
        if ($result) {
            $permission = true;
        }

        if (!empty($reqData->data->org_id)) {
            $org_id = $reqData->data->org_id;           
            $response = $this->Account_model->get_account_details_for_view($org_id, $uuid_user_type);
            $additional_billing = $this->Account_model->get_organisation_additional_billing($reqData->data->org_id);
            $response['data'] = is_array($additional_billing) && !empty($additional_billing)? array_merge((array) $response['data'], $additional_billing[0]) : $response['data'];
            $response['admin_permission'] = $permission;
        } else {
            $response = ['status' => false, 'error' => "org id is required"];
        }

        echo json_encode($response);
        exit();
    }

    /*
     * it is use for validate contact email when create contact
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

    function check_payable_email_address($defualt, $reqData) {
        $reqData = json_decode($reqData);

        if (!empty($reqData->payable_email)) {
            // check email address formate is valid or not
            if (!filter_var($reqData->payable_email, FILTER_VALIDATE_EMAIL)) {
                $this->form_validation->set_message('check_payable_email_address', $reqData->payable_email . ' this payable email address is not valid');
                return false;
            }
        }
    }
/*
     * its use for get organization reference details for organization type
     * return type json
     */

    function get_organization_source() {
        $reqData = request_handler();
        $rows = $this->Account_model->get_organization_source();

        $selectOption = $reqData->data->select_option ?? 0;
        $rowsData = [];
        if ($selectOption == 1) {
            $rowsData[] = ['label' => 'Select Lead Source', 'value' => ''];
        }
        $rowsData = array_merge($rowsData, $rows);
        echo json_encode($rowsData);
    }

    /*
     * its use for get organization reference details for organization service type
     * return type json
     */

    function get_organization_service_type() {
        $reqData = request_handler();
        $rows = $this->Account_model->get_organization_service_type();

        $selectOption = $reqData->data->select_option ?? 0;
        $rowsData = [];
        if ($selectOption == 1) {
            $rowsData[] = ['label' => 'Select Service Type', 'value' => ''];
        }
        $rowsData = array_merge($rowsData, $rows);
        echo json_encode($rowsData);
    }

    
    /*
     * its use for get account child list
     * handle request form fron-end
     * 
     * return type json
     * array('count' => $dt_filtered_total, 'data' => $result, 'status' => true)
     */

    function get_account_child_list() {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;
        if (!empty($reqData->data)) {
           
            if (!empty($data["id"])) {
                $org_id = $data['id']; 
                     // call model for get account list
                $result = $this->Account_model->get_account_child_list($org_id);
                $response = ['status' => true, 'msg' => "Organisation list fetched successfully", 'data' => $result];
            } else {
                $result = [];
                $response = ['status' => false, 'error' => "Organisation id not found", 'data' => $result];
            }

        } else {
            $response = ['status' => false, 'error' => "Organisation id not found"];
        }
        echo json_encode($response);
        exit();
    }

    function get_organisation_service_area() {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;
        if (!empty($reqData->data)) {           
            if (!empty($data["id"])) {
                $org_id = $data['id']; 
                $result = $this->Account_model->get_organisation_service_area($org_id);
                $response = ['status' => true, 'msg' => "Organisation list fetched successfully", 'data' => $result];
            } else {
                $result = [];
                $response = ['status' => false, 'error' => "Organisation id not found", 'data' => $result];
            }

        } else {
            $response = ['status' => false, 'error' => "Organisation id not found"];
        }
        echo json_encode($response);
        exit();
    }

     /**
     * get service areas corresponding support worker area
     */
    public function get_mapped_sa_and_swa() {
        $reqData = request_handler();
        $response = $this->Account_model->get_mapped_sa_and_swa();
        echo json_encode($response);
        exit();
    }

    function save_billing_info() {
        $reqData = request_handler('access_crm');
        $res = $this->Account_model->save_billing_info($reqData->data, $reqData->adminId);
        $response = ['status' => false, 'error' => "Something went wrong"];
        if ($res) {
            $response = ['status' => true, 'message' => "Billing information updated"];
        }
        echo json_encode($response);
    }

    function get_organisation_billing_info() {
        $reqData = request_handler('access_crm');
        $org_id = $reqData->data->org_id?? 0;
        $res = $this->Account_model->get_organisation_billing($org_id);
        $response = ['status' => false, 'error' => "Something went wrong"];
        if ($res) {
            $response = ['status' => true, 'message' => "Billing information fetched successfully", 'data' => is_array($res)? $res : null];
        }
        echo json_encode($response);
    }
    
    /**
     * Get cost book options
     */
    public function get_cost_book_options() {
        $reqData = request_handler('access_crm');
        $data = $reqData->data;
        $result = $this->Account_model->get_cost_book_options($data);
        echo json_encode(['status'=>true, 'data' => $result]);
    }
}
