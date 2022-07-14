<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class : MemberRole
 * Uses : Used for handle MemberRole request and response  
 
 * Response type - Json format
 * 
 * Library
 * form_validation - used for validating the form data
 * 
 * LogType - crm
 * 
 * @property-read \Member_role_model $MemberRole_model
 */
class MemberRole extends MX_Controller {
    // Load custom validation traits function
    use formCustomValidation;

    // Defualt construct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        
        // load role model 
        $this->load->model('Member_role_model');

        // set the log
        $this->loges->setLogType('item');
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
     * For getting role list
     * 
     * Return type json
     * - count 
     * - data
     * - status
     */
    function get_role_list() {
        // $reqData = request_handler('access_crm');
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            // Call model for get role list
            $result = $this->Member_role_model->get_role_list($reqData->data);
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }

    /*
     * For create or updating role
     * Validate request and return response according to request
     * 
     * Return type json
     *  - status
     *  - msg 
     *  - error if any error  occured
     */
    function create_update_role() {
        // Get the request data
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                array('field' => 'name', 'label' => 'Name', 'rules' => 'required',"errors" => [ "required" => "Name value is null"]),
                array('field' => 'start_date', 'label' => 'Start Date', 'rules' => 'required', "errors" => [ "required" => "Start Date value is null"]),
            ];

            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {

                $this->load->model('Basic_model');

                // Check role is exist. Using name
                $name = $data['name'];
                if(!empty($data['role_id'])){
                    $where = array('name' => $name, 'id!=' => $data['role_id'], );
                }else{
                    $where = array('name' => $name, );
                }
                $colown = array('id', 'name');
                $check_role = $this->basic_model->get_record_where('member_role', $colown, $where);

                // If not exist 
                if (!$check_role) {
                    // Call create or update role model
                    $roleId = $this->Member_role_model->create_upadte_role($data, $adminId);

                    // Check $roleId is empty or not
                    // According to that role will be created
                    if ($roleId) {
                        $this->load->library('UserName');
                        $adminName = $this->username->getName('admin', $adminId);

                        /**
                         * Create logs. it will represent the user action they have made.
                         */
                        $msg = '';
                        if(!empty($data['role_id'])){
                            $this->loges->setTitle("Role updated for " . $data['name'] ." by " . $adminName);  // Set title in log
                            $msg = 'Role has been updated successfully.';
                        }
                        else{
                            $this->loges->setTitle("Role created for " . $data['name'] ." by " . $adminName);  // Set title in log
                            $msg = 'Role has been created successfully.';
                        }
                        $this->loges->setDescription(json_encode($data));
                        $this->loges->setUserId($adminId);
                        $this->loges->setCreatedBy($adminId);
                        // Create log
                        $this->loges->createLog(); 
                        $data = array('role_id' => $roleId);
                        $response = ['status' => true, 'msg' => $msg, 'data' => $data ];
                    } else {
                        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                    }
                } else {
                     $response = ['status' => false, 'error' => 'Role already exist '];
                }
            } else {
                // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => 'Requested data is null'];
        }

        echo json_encode($response);
        exit();
    }

    /*
     * its use for contact deails
     * return type json 
     */

    function get_role_details_for_view() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData->data->roleId)) {

            // call model for get role details
            $result = $this->Member_role_model->get_role_details($reqData->data->roleId);

            if (empty($result)) {
                $response = ["status" => false, "error" => "Role is not found"];
            } else {
                $response = ["status" => true, "data" => $result];
            }
        } else {
            $response = ["status" => false, "error" => "Role id not found"];
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Mark role as archived. Also mark related person as archived.
     * 
     * Archived roles will be excluded in the list of roles.
     * 
     * @todo: If you require destroying the whole role, not just soft-deleting it, 
     * you have to look for another function or implement your own!
     */
    public function archive_role() {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;

        $this->output->set_content_type('json');

        // check if role exist
        $result = $this->Member_role_model->get_role_details($data['role_id']);
        if (empty($result)) {
            return $this->output->set_output(json_encode([
                        'status' => false,
                        'error' => 'Role does not exist anymore. Please refresh your page',
            ]));
        }

        $result = $this->Member_role_model->archive_role($data['role_id']);

        if (empty($result) || !$result['status']) {
            return $this->output->set_output(json_encode([
                        'status' => false,
                        'error' => $result['error'] ?? system_msgs('something_went_wrong'),
            ]));
        }

        // when you are are this point, the above code has ran successfully
        // Log
        $role_id = $result['id'];

        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully archived role with ID of %s by %s", $role_id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully archived role with ID of %s by %s", $role_id, $adminName));  // set title in log
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($role_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        return $this->output->set_output(json_encode([
                    'status' => true,
                    'msg' => "Role successfully archived"
        ]));
    }

    /*
     * get role list by search
     * return type json
     */
    function get_role_list_by_search() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Member_role_model->get_role_list_by_search($reqData->data->search); 
            if (empty($result)) {
                $response = ["status" => false, "error" => "Role is not found"];
            } else {
                $response = ["status" => true, "data" => $result];
            }   
        }else{
            $response = ["status" => true, "data" => $response];
        }
       
        echo json_encode($response);
        exit();  
    }

    /*
     * get role list
     * return type json
     */
    function get_active_role_list() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Member_role_model->get_role_list_by_search(); 
            if (empty($result)) {
                $response = ["status" => false, "error" => "Role is not found"];
            } else {
                $response = ["status" => true, "data" => $result];
            }   
        }else{
            $response = ["status" => true, "data" => $response];
        }
        echo json_encode($response);
        exit();  
    }
}
