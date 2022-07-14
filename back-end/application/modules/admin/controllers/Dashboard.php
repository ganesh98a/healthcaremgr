<?php

use classRoles as adminRoles;
use AdminClass as AdminClass;

require_once APPPATH . 'traits/formCustomValidation.php';
require_once APPPATH . 'Classes/websocket/Websocket.php';
defined('BASEPATH') or exit('No direct script access allowed');

//class Master extends MX_Controller

class Dashboard extends CI_Controller {

    use formCustomValidation;

    function __construct() {
        parent::__construct();
        $this->load->model('admin_model');
        $this->loges->setLogType('user_admin');
        $this->load->library('form_validation');
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

    public function index() {
        // nothing
        // added dummy line 1
    }

    function get_create_admin_options() {
        request_handler('access_admin', 1, 1);

        $result["department_option"] = $this->basic_model->get_record_where('department', array('id as value', 'name as label', 'short_code'), ['archive' => 0, 'short_code' => 'internal_staff']);
        $result["user_type_option"] = $this->basic_model->get_record_where('admin_user_type', array('id as value', 'name as label'), ['archive' => 0]);
        $result["access_role_option"] = $this->basic_model->get_record_where('access_role
        ', array('id as value', 'name as label'), ['archive' => 0]);
        $result["business_unit_option"] = $this->basic_model->get_record_where('business_units
        ', array('id as value', 'business_unit_name as label'), ['archive' => 0]);

        echo json_encode(['status' => true, "data" => $result]);
    }

    /**
     * fetching the user details with phones and emails
     */
    public function get_user_details() {
        $reqData = request_handler('access_admin', 1, 1);
        if ($reqData->data) {
            $admin_id = $reqData->data->AdminId;
            $result = $this->admin_model->get_admin_details($admin_id);
            if (!empty($result)) {
                $result['PhoneInput'] = $this->admin_model->get_admin_phone_number($result['id']);
                $result['EmailInput'] = $this->admin_model->get_admin_email($result['id']);
               
                $response = array(
                    'status' => true,
                    'data' => $result
                );
            } else {
                $response = array(
                    'status' => false,
                    'data' => 'Invalid request'
                );
            }
            echo json_encode($response);
        }
    }

    /**
     * fetches all the user statuses
     */
    function get_user_statuses() {
        $reqData = request_handler('');

        if (!empty($reqData->data)) {
            $response = $this->admin_model->get_user_statuses();
            echo json_encode($response);
        }
        exit(0);
    }

    /*
     * For getting users' login history list
     */
    function get_user_login_history_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->admin_model->get_user_login_history_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting list of all modules stored in tbl_role table
     */
    public function get_module_names() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->admin_model->get_module_names();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting list of all objects of module stored in tbl_module_object table
     */
    public function get_module_objects() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->admin_model->get_module_objects($data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting access role details along with objects and its permissions
     */
    public function get_access_role_objects_permissions() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->admin_model->get_access_role_objects_permissions($data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * adding/updating the access role and its relevant information
     */
    public function create_update_access_role() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->admin_model->create_update_access_role($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * adding/updating the access role and its relevant information
     */
    public function get_access_roles_list() {
        $reqData = request_handler();
        $filter_condition ='';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData,true);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            if (!empty($filter_condition)) {
                $filter_condition = 
                str_replace(['created_by'],
                ['member_id'], $filter_condition);
            }  
        }
       $result = $this->admin_model->get_access_roles_list($reqData->data,$filter_condition);
        echo json_encode($result);
        exit();
    }

    /**
     * archiving access role
     */
    public function archive_access_role() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->admin_model->archive_access_role((array) $reqData->data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    public function create_edit_user() {
        // get request data
        $reqData = request_handler('access_admin', 1, 1);
        $this->loges->setCreatedBy($reqData->adminId);
        require_once APPPATH . 'Classes/admin/admin.php';
        $objAdmin = new AdminClass\Admin();

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = (!empty($data['id'])) ? $data['id'] : false;
            $data['uuid'] = (!empty($data['uuid'])) ? $data['uuid'] : false;
            $this->form_validation->set_data($data);
            $user_type = $data['user_type'] ?? 1;
            if ($user_type != 2) {
                $validation_rules = [
                    array('field' => 'firstname', 'label' => 'First Name', 'rules' => 'required'),
                    array('field' => 'lastname', 'label' => 'Last Name', 'rules' => 'required'),
                    array('field' => 'position', 'label' => 'Date Of Birth', 'rules' => 'required'),
                    array('field' => 'business_unit', 'label' => 'Business Unit', 'rules' => 'required'),
                    array('field' => 'EmailInput[]', 'label' => 'Email address', 'rules' => 'callback_check_user_emailaddress_already_exist[' . $data['user_uuid'] . ']'),
                    array('field' => 'PhoneInput[]', 'label' => 'phone number', 'rules' => 'callback_check_phone_number_validation|callback_phone_number_check[name,required,User contact should be enter valid phone number.]'),
                    array('field' => 'uuid_user_type', 'label' => 'uuid User type', 'rules' => 'required'),
                ];
            }

            // if (!$data['id']) {
            //     $validation_rules[] = array('field' => 'password', 'label' => 'password', 'rules' => 'required');
            // }

            // $validation_rules[] = array('field' => 'username', 'label' => 'UserName', 'rules' => 'callback_check_username_already_exist[' . $data['id'] . ']');


            // set rules form validation
            $this->form_validation->set_rules($validation_rules);
            if ($this->form_validation->run()) {
                if ($user_type == 2 && !$data['id']) {
                    $data['department'] = $objAdmin->get_internal_staff_department_id();
                }
                $email_result = $this->admin_model->check_dublicate_email($data['EmailInput'][0]->name, $data['user_uuid']);
                if(!empty($email_result)){
                    $response = array('status' => false, 'error' => 'Email already exist');
                }else{
                    $objAdmin->setUser_type($data['user_type']);

                    // Since username logins in admin portal are no more (HCM-191) instead of filling 
                    // the tbl_member.username column with blank, let's set the the primary email as username.
                    $objAdmin->setUsername($data['EmailInput'][0]->name ?? '');

                    // $objAdmin->setPassword($data['password'] ?? '');
                    $objAdmin->setFirstname($data['firstname'] ?? '');
                    $objAdmin->setLastname($data['lastname'] ?? '');
                    $objAdmin->setDepartment($data['department'] ?? '');
                    $objAdmin->setTimezone($data['timezone'] ?? '');
                    $objAdmin->setPosition($data['position'] ?? '');
                    $objAdmin->setIslocked($data['is_locked'] ?? '0');
                    $objAdmin->setDateUnlocked(array_key_exists('date_unlocked', $data) ? DATE_TIME : '0');
                    $objAdmin->setAccessRole(isset($data['access_role_id']) ? $data['access_role_id'] : null);
                    $objAdmin->setSecondaryEmails($data['EmailInput'] ?? []);
                    $objAdmin->setPrimaryEmail($data['EmailInput'][0]->name ?? '');
                    $objAdmin->setSecondaryPhone($data['PhoneInput'] ?? []);
                    $objAdmin->setRoles($data['rolesList'] ?? []);
                    $rand = mt_rand(10, 100000);
                    $token = encrypt_decrypt('encrypt', $rand);
                    $objAdmin->setToken($token);
                    $objAdmin->setAvatar($data['avatar'] ?? '');
                    $objAdmin->setUuid_user_type($data['uuid_user_type'] ?? '');
                    $objAdmin->setBusinessUnit($data['business_unit'] ?? '');
                }

                if ($data['id'] > 0) {

                    //check permission
                    check_permission($reqData->adminId, 'update_admin');

                    // update user
                    $objAdmin->setAdminid($data['user_uuid']);
                    $objAdmin->setUuid($data['user_uuid']);
                    $objAdmin->setMemberId($data['id']);
                    
                    $objAdmin->updateBusinessUnit($data['business_unit'] ?? '');

                    $objAdmin->updateUser();
                    if ($user_type != 2) {
                        $objAdmin->updateRoleToAdmin();
                    }

                    // Logs can be added when user give access to plan management
                    $this->insert_audit_logs($data, $reqData->adminId);
                    $this->loges->setTitle('User profile updated: ' . $reqData->adminId);
                } else {
                    //check permission
                    check_permission($reqData->adminId, 'create_admin');
                    $objAdmin->setStatus(0);
                    // create user
                    $objAdmin->createUser();
                    if ($user_type != 2) {
                        $objAdmin->updateRoleToAdmin();
                    }

                    // send welcome to admin
                    $objAdmin->send_welcome_mail();

                    // Logs can be added when user give access to plan management
                    $this->insert_audit_logs($data, $reqData->adminId);
                    $this->loges->setTitle('New admin created: ' . $reqData->adminId);
                }
                $this->loges->setUserID($objAdmin->getAdminid());
                $this->loges->setDescription(json_encode($reqData->data));
                $this->loges->createLog();

                if ($user_type != 2) {
                    // insert secondary email
                    $objAdmin->insertEmail();

                    // insert secondary email
                    $objAdmin->insertPhone();
                }

                $wbObj = new Websocket();

                // check websoket here send and update permission
                if ($wbObj->check_webscoket_on()) {
                    $data = array('chanel' => 'server', 'req_type' => 'update_user_admin', 'token' => $wbObj->get_token(), 'data' => ['adminId' => $objAdmin->getAdminid(), 'update_type' => 'update_permission']);
                    $wbObj->send_data_on_socket($data);
                }
                $response = array('status' => true);                
                
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($response);
        }
    }

    // Logs can be added when user give access to plan management
    public function insert_audit_logs($data, $adminId) {
        if ($accesskey = array_search('finance_planner', array_column($data['rolesList'], 'role_key'))) {
            if (isset($data['rolesList'][$accesskey]->access)) {
                $accessvar = $data['rolesList'][$accesskey]->access;
                if ($accessvar) {
                    $auditlogs['user'] = $adminId;
                    $auditlogs['title'] = 'New member access given';
                    $auditlogs['action'] = 14;
                    $auditlogs['description'] = 'A new member has been given an access to the plan management module ' . $data['firstname'] . ' ' . $data['lastname'];
                    $response = $this->admin_model->insert_audit_logs($auditlogs);
                }
            }
        }
    }

    public function check_phone_number_validation($phone_numbers) {
        if (!empty($phone_numbers)) {
            foreach ($phone_numbers as $val) {
                if (empty($val)) {
                    $this->form_validation->set_message('check_phone_number_validation', 'Phone number can not be empty');
                    return false;
                }
            }
        } else {
            $this->form_validation->set_message('check_phone_number_validation', 'Phone number can not be empty');
            return false;
        }
        return true;
    }

    // this function used for add and edit role
    public function add_role() {
        require_once APPPATH . 'Classes/admin/role.php';
        $objRoles = new adminRoles\Roles();
        if ($this->input->post()) {
            $role_id = $this->input->post('role_id');
            $objRoles->setrolename($this->input->post('role_name'));
            $objRoles->setroleid($role_id);
            $res = $objRoles->checkAlreadyExist();
            if ($res) {
                if ($role_id > 0) {
                    $objRoles->UpdateAllRoles();
                } else {
                    $objRoles->CreateRole();
                }
                $response = array(
                    'status' => true
                );
            } else {
                $response = array(
                    'status' => false,
                    'error' => 'This role already exist'
                );
            }
            echo json_encode($response);
        }
    }

    public function list_role() {
        $this->load->model('admin_model');
        if ($this->input->post()) {
            $reqData = json_decode($this->input->post('request'));
            $response = $this->admin_model->list_role_dataquery($reqData);
            echo json_encode($response);
        }
    }

    public function delete_role() {
        require_once APPPATH . 'Classes/admin/role.php';
        $objRoles = new adminRoles\Roles();
        if ($this->input->post()) {
            $objRoles->setArchive(1);
            $objRoles->setroleid($this->input->post('role_id'));
            $objRoles->UpdateRoles();
            echo json_encode(array(
                'status' => true
            ));
        }
    }

    public function active_inactive_role() {
        if ($this->input->post()) {
            $role_id = $this->input->post('role_id');
            $status = $this->input->post('status');
            $result = $this->basic_model->update_records('role', $role_data = array(
                'status' => $status
                    ), $where = array(
                'id' => $role_id
            ));
            echo json_encode(array(
                'status' => true
            ));
        }
    }

    // this method use for check user email already exist
    public function check_user_emailaddress_already_exist($sever_emailAddress = array(), $adminId_NEW = false) {
        $this->load->model('admin_model');
        $adminId = false;
        if ($this->input->get() || $sever_emailAddress) {
            $emails = ($this->input->get()) ? $this->input->get() : (array) $sever_emailAddress;
            if ($this->input->get('adminId') || $adminId_NEW) {
                $adminId = ($this->input->get('adminId')) ? $this->input->get('adminId') : $adminId_NEW;
                if (array_key_exists('adminId', $emails)) {
                    unset($emails['adminId']);
                }
            }
            foreach ($emails as $val) {
                $result = $this->admin_model->check_dublicate_email($val, $adminId);             
                
                if ($this->input->get()) {
                    if (!empty($result)) {
                        echo 'false';
                    } else {
                        echo 'true';
                    }
                }
                return true;
            }
        }
    }    

    // using this method get admin list
    public function list_admins() {
        $reqData = request_handler('access_admin', 1, 1);
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $response = $this->admin_model->list_admins_dataquery($reqData);
            $response['status'] = true;
            echo json_encode($response);
        }
    }

    // this mehtod use for delete user
    public function delete_user() {
        $reqData = request_handler('delete_admin', 1, 1);
        $reqData = $reqData->data;
        $wbObj = new Websocket();
        if (!empty($reqData->id)) {
            $adminID = $reqData->id;
            $uuid = $reqData->uuid;
            if (in_array($adminID, get_super_admins())) {
                $return = array(
                    'status' => false,
                    'error' => 'Sorry supar admin can not be delete'
                );
            } else {
                $this->basic_model->update_records('member', array(
                    'archive' => 1, 'status' => 0,
                        ), $where = array(
                    'id' => $adminID
                ));
                $this->basic_model->update_records('users', array(
                    'archive' => 1, 'status' => 0,
                        ), $where = array(
                    'id' => $uuid
                ));
                $this->basic_model->delete_records('member_login', $where = array(
                    'memberId' => $adminID
                ));
                $return = array(
                    'status' => true
                );
                // check websoket here send and logout user
                if ($wbObj->check_webscoket_on()) {
                    $data = array(
                        'chanel' => 'server',
                        'req_type' => 'update_user_admin',
                        'token' => $wbObj->get_token(),
                        'data' => [
                            'adminId' => $adminID,
                            'update_type' => 'logout_admin_user'
                        ]
                    );
                    $wbObj->send_data_on_socket($data);
                }
            }
        } else {
            $return = array(
                'status' => false,
                'error' => 'Invalid adminId'
            );
        }
        echo json_encode($return);
    }

    // this method use for active and inactive user
    public function active_inactive_user() {
        $reqestData = request_handler('update_admin', 1, 1);
        $reqData = $reqestData->data;
        $wbObj = new Websocket();
        if (!empty($reqData->adminID)) {
            $adminID = $reqData->adminID;
            $uuid = $reqData->uuid;
            if (in_array($adminID, get_super_admins())) {
                $return = array(
                    'status' => false,
                    'error' => 'Sorry!! super admin can not be deactive'
                );
            } else {
                $status = $reqData->status;
                $update_data = [
                    'status' => $status  
                ];
                if($status==1){
                    $update_data['archive'] = 0;
                }
                $this->basic_model->update_records('member', $role_data = $update_data, $where = array(
                    'id' => $adminID
                ));
                $this->basic_model->update_records('users', $role_data = $update_data, $where = array(
                    'id' => $uuid
                ));
                $this->basic_model->delete_records('member_login', $where = array(
                    'memberId' => $adminID
                ));
                $return = array(
                    'status' => true
                );
                // check websoket here send and logout user
                if ($wbObj->check_webscoket_on()) {
                    $data = array(
                        'chanel' => 'server',
                        'req_type' => 'update_user_admin',
                        'token' => $wbObj->get_token(),
                        'data' => [
                            'adminId' => $adminID,
                            'update_type' => 'logout_admin_user'
                        ]
                    );
                    $wbObj->send_data_on_socket($data);
                }
            }
        } else {
            $return = array(
                'status' => false,
                'error' => 'Invalid adminId'
            );
        }
        echo json_encode($return);
    }

    public function get_listing_roles() {
        // get request data
        $reqData = request_handler('access_admin', 1, 1);
        require_once APPPATH . 'Classes/admin/admin.php';
        $objAdmin = new AdminClass\Admin();
        if (!empty($reqData->data->AdminId)) {
            $objAdmin->setAdminid($reqData->data->AdminId);
            $response = $objAdmin->getUserBasedRoles(1);
            
        } else {
            $response = $this->basic_model->get_record_where('role', $column = array(
                'name',
                'id',
                'role_key'
                    ), $where = array(
                'status' => 1,
                'archive' => 0
            ));
        } 
        echo json_encode(array(
            'status' => true,
            'data' => $response
        ));
    }

    public function forgotten_pin() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            require_once APPPATH . 'Classes/admin/admin.php';
            require_once APPPATH . 'Classes/admin/permission.php';
            $obj_permission = new classPermission\Permission();
            $check_admin_res = $obj_permission->check_permission($reqData->adminId, 'access_admin');
            $check_fms_res = $obj_permission->check_permission($reqData->adminId, 'access_fms');
            if ($check_admin_res || $check_fms_res) {
                $objAdmin = new AdminClass\Admin();
                $objAdmin->setAdminid($reqData->adminId);
                $rand = mt_rand(10, 100000);
                $token = encrypt_decrypt('encrypt', $rand);
                $objAdmin->setToken($token);
                $this->basic_model->update_records('member', array(
                    'otp' => $token
                        ), $where = array(
                    'uuid' => $reqData->adminId
                ));

                $result = $this->basic_model->get_row('users', $columns = array(
                    'username'
                        ), $where = array(
                    'id' => $reqData->adminId,
                    'status' => 1
                ));
                
                $adminDetails = $this->basic_model->get_row('member', $columns = array(
                    'firstname',
                    'lastname'
                        ), $where = array(
                    'uuid' => $reqData->adminId
                ));
                $objAdmin->setPrimaryEmail($result->username);
                $objAdmin->setFirstname($adminDetails->firstname);
                $objAdmin->setLastname($adminDetails->lastname);
                $objAdmin->sendResetPinMailToAdmin();
                echo json_encode(array(
                    'status' => true,
                    'message' => 'Please visit your mail inbox for reset your pin.'
                ));
            } else {
                echo json_encode(array(
                    'status' => false,
                    'error' => 'You do not have permission to reset pin.'
                ));
            }
        }
    }

    public function get_all_rolles() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            require_once APPPATH . 'Classes/admin/admin.php';
            $objAdmin = new AdminClass\Admin();
            $objAdmin->setAdminid($reqData->adminId);
            $response = $objAdmin->getUserBasedRoles();
            echo json_encode(array(
                'status' => true,
                'data' => $response
            ));
        }
    }

    public function get_all_permission() {
        $reqData = request_handler();
        if ($reqData) {
            $presmission['permission'] = get_all_permission($reqData->adminId);
            echo json_encode(array(
                'status' => true,
                'data' => $presmission['permission']
            ));
        }
    }

    public function get_loges() {
        $reqData = request_handler('access_admin', 1, 1);
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $reqData = ($reqData);
            $response = $this->admin_model->get_all_loges($reqData);
            echo json_encode($response);
        }
    }

    public function update_password() {
        $reqData = request_handler();
        require_once APPPATH . 'Classes/admin/auth.php';
        $adminAuth = new Admin\Auth\Auth();
        $adminAuth->setAdminid($reqData->adminId);
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $this->form_validation->set_data((array) $reqData);
            $validation_rules = array(
                array(
                    'field' => 'password',
                    'label' => 'confirm current',
                    'rules' => 'required'
                ),
                array(
                    'field' => 'new_password',
                    'label' => 'new password',
                    'rules' => 'required|min_length[8]|max_length[25]|callback_is_password_strong'
                ),
                array(
                    'field' => 'confirm_password',
                    'label' => 'confirm new',
                    'rules' => 'required'
                ),
                array(
                    'field' => 'uuid_user_type',
                    'label' => 'Uuid User type',
                    'rules' => 'required'
                ),
            );
            // set rules form validation
            $this->form_validation->set_rules($validation_rules);
            if ($this->form_validation->run()) {
                $adminAuth->setPassword($reqData->password);
                $adminAuth->setUuid_user_type($reqData->uuid_user_type);
                $check_password = $adminAuth->verifyCurrentPassword($reqData->password);
                if ($check_password) {
                    // set new password we need to update in db
                    $adminAuth->setPassword($reqData->new_password);                   
                    // update password of admin
                    $adminAuth->reset_password();
                    $response = array(
                        'status' => true,
                        'success' => 'Your password update successfully'
                    );
                } else {
                    $response = array(
                        'status' => false,
                        'error' => 'Your password not match with current password'
                    );
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = array(
                    'status' => false,
                    'error' => implode(', ', $errors)
                );
            }
            echo json_encode($response);
        }
    }

    public function is_password_strong($password) {
        if (preg_match('#[0-9]#', $password) && preg_match('#.*^(?=.{6,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#', $password)) {

            return TRUE;
        }
        $this->form_validation->set_message('is_password_strong', 'Password must contain at least one upper case, alphanumeric and one special character');
        return FALSE;
    }


    public function update_pin() {
        $reqData = request_handler('access_admin');
        require_once APPPATH . 'Classes/admin/auth.php';
        $adminAuth = new Admin\Auth\Auth();
        $adminAuth->setAdminid($reqData->adminId);
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $this->form_validation->set_data((array) $reqData);
            $validation_rules = array(
                array(
                    'field' => 'pin',
                    'label' => 'confirm current',
                    'rules' => 'required'
                ),
                array(
                    'field' => 'new_pin',
                    'label' => 'new password',
                    'rules' => 'required|min_length[6]'
                ),
                array(
                    'field' => 'confirm_pin',
                    'label' => 'confirm new',
                    'rules' => 'required'
                ),
            );
            // set rules form validation
            $this->form_validation->set_rules($validation_rules);
            if ($this->form_validation->run()) {
                $adminAuth->setPin($reqData->pin);
                $check_pin = $adminAuth->checkCurrentPin();
                if ($check_pin) {
                    // set new pin we need to update in db
                    $adminAuth->setPin($reqData->new_pin);
                    // update pin of admin
                    $adminAuth->updatePinAdmin();
                    $response = array(
                        'status' => true,
                        'success' => 'Your pin update successfully'
                    );
                } else {
                    $response = array(
                        'status' => false,
                        'error' => 'Your pin not match with current pin'
                    );
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = array(
                    'status' => false,
                    'error' => implode(', ', $errors)
                );
            }
            echo json_encode($response);
        }
    }

    public function update_password_recovery_email() {
        $reqData = request_handler();
        require_once APPPATH . 'Classes/admin/auth.php';
        $adminAuth = new Admin\Auth\Auth();
        $adminAuth->setAdminid($reqData->adminId);
        $adminAuth->setUuid_user_type($reqData->uuid_user_type);
        
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $this->form_validation->set_data((array) $reqData);
            $validation_rules = array(
                array('field' => 'email','label' => 'email','rules' => 'required|valid_email'),
                array('field' => 'confirm_email','label' => 'confir email','rules' => 'required|valid_email'),
                array('field' => 'password','label' => 'password', 'rules' => 'required'),
            );
            // set rules form validation
            $this->form_validation->set_rules($validation_rules);
            if ($this->form_validation->run()) {
                $adminAuth->setPassword($reqData->password);
                $check_password = $adminAuth->verifyCurrentPassword();
                if (!$check_password) {
                    $response = array(
                        'status' => false,
                        'error' => 'Your enter password not match with your current password'
                    );
                    echo json_encode($response);
                    exit();
                }
                $adminAuth->setPrimaryEmail($reqData->email);
                $check_email = $adminAuth->checkExistingEmail();
                if (empty($check_email)) {
                    $token = array(
                        'email' => $reqData->email,
                        'time' => DATE_TIME,
                        'adminId' => $adminAuth->getAdminid()
                    );
                    $token = encrypt_decrypt('encrypt', json_encode($token));
                    $adminAuth->setToken($token);

                    $this->basic_model->update_records('users', array('password_token' => $token ), array('id' => $adminAuth->getAdminid()));

                    $result = $this->basic_model->get_row('member', array("firstname","lastname"), array('uuid' => $adminAuth->getAdminid()));

                    $adminAuth->setFirstname($result->firstname);
                    $adminAuth->setLastname($result->lastname);
                    // send email verification email
                    $adminAuth->sendUpdatePasswordRecoveryEmail();
                    $response = array(
                        'status' => true,
                        'success' => 'Please verify your entered email address, we sended email to your entered email'
                    );
                } else {
                    $response = array(
                        'status' => false,
                        'error' => 'This email already exist to another user'
                    );
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = array(
                    'status' => false,
                    'error' => implode(', ', $errors)
                );
            }
            echo json_encode($response);
        }
    }

    public function update_profile_pic() {
        $reqData = request_handler('access_admin');
        require_once APPPATH . 'Classes/admin/admin.php';
        $data = $reqData->data;
        $admin = new AdminClass\Admin;
        $admin->setAdminid($reqData->adminId);
        $admin->setAvatar($data->avatar);
        $admin->updateProfilePic();
        $response = array(
            'status' => true,
            'success' => 'Profile picture updated successfully'
        );
        echo json_encode($response);
        exit();
    }


    public function update_users_password() {
        $reqData = request_handler();
        require_once APPPATH . 'Classes/admin/auth.php';
        $adminAuth = new Admin\Auth\Auth();
        $adminAuth->setAdminid($reqData->adminId);
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $this->form_validation->set_data((array) $reqData);
            $validation_rules = array(
                array(
                    'field' => 'password',
                    'label' => 'confirm current',
                    'rules' => 'required'
                ),
                array(
                    'field' => 'new_password',
                    'label' => 'new password',
                    'rules' => 'required|min_length[8]|max_length[25]|callback_is_password_strong'
                ),
                array(
                    'field' => 'confirm_password',
                    'label' => 'confirm new',
                    'rules' => 'required'
                ),
            );
            // set rules form validation
            $this->form_validation->set_rules($validation_rules);
            if ($this->form_validation->run()) {
                $adminAuth->setPassword($reqData->password);
                $check_password = $adminAuth->verifyCurrentUsersPassword($reqData->password);
                if ($check_password) {
                    // set new password we need to update in db
                    $adminAuth->setPassword($reqData->new_password);
                    // update password of admin
                    $adminAuth->reset_users_password();
                    $response = array(
                        'status' => true,
                        'success' => 'Your password update successfully'
                    );
                } else {
                    $response = array(
                        'status' => false,
                        'error' => 'Your password not match with current password'
                    );
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = array(
                    'status' => false,
                    'error' => implode(', ', $errors)
                );
            }
            echo json_encode($response);
        }
    }

}
