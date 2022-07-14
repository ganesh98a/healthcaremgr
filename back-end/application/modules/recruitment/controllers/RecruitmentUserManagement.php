<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class RecruitmentUserManagement extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Recruitment_user_management');
        $this->load->model('Basic_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
        $this->loges->setModule(9);
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

    function get_new_recruiter_name() {
        $reqData = request_handler('access_recruitment_admin');

        if (!empty($reqData->data)) {
            $result = $this->Recruitment_user_management->get_new_recruiter_name($reqData->data);
            echo json_encode($result);
        }
    }

    function get_admin_details() {
        $reqData = request_handler('access_recruitment_admin');
        require_once APPPATH . 'Classes/admin/admin.php';
        $objAdmin = new AdminClass\Admin();

        if (!empty($reqData->data) && isset($reqData->data->adminId)) {
            $adminId = $reqData->data->adminId;
            $objAdmin->setAdminid($adminId);

            $admin = $this->Recruitment_user_management->get_staff_details($adminId);

            $admin['phones'] = $objAdmin->get_admin_phone_number();
            $admin['emails'] = $objAdmin->get_admin_email();

            echo json_encode(['status' => true, 'data' => $admin]);
        }
    }

    function get_staff_details() {
        $reqData = request_handler('access_recruitment_admin');
        require_once APPPATH . 'Classes/admin/admin.php';
        $objAdmin = new AdminClass\Admin();

        if (!empty($reqData->data) && isset($reqData->data->adminId)) {
            $adminId = $reqData->data->adminId;
            $objAdmin->setAdminid($adminId);

            $admin = $this->Recruitment_user_management->get_staff_details($adminId);

            $admin['phones'] = $objAdmin->get_admin_phone_number();
            $admin['emails'] = $objAdmin->get_admin_email();
            $admin['task_count'] = $this->Recruitment_user_management->get_total_pending_task_count($adminId);
            $admin['retcruitment_area_option'] = $this->Recruitment_user_management->get_recruitment_area_title();
            $admin['start_task_listing'] = $this->Recruitment_user_management->get_staff_profile_listing_task($adminId);
            $admin['applicant_count_type'] = 'week';
            $admin['applicant_successful_count_type'] = $admin['applicant_count_type'];
            $admin['applicant_count_data'] = $this->Recruitment_user_management->get_staff_applicant_count($adminId,$admin['applicant_count_type'],1);
            $admin['applicant_successful_count_data'] = $this->Recruitment_user_management->get_staff_applicant_count($adminId,$admin['applicant_successful_count_type'],2);;
            echo json_encode(['status' => true, 'data' => $admin]);
        }
    }

    function get_staff_task_ongoing_list() {
        $reqData = request_handler('access_recruitment_admin');
        if (!empty($reqData->data) && isset($reqData->data->adminId)) {
            $adminId = $reqData->data->adminId;
            $start_task_listing = $this->Recruitment_user_management->get_staff_profile_listing_task($adminId);
            echo json_encode(['status' => true, 'data' => $start_task_listing]);
            exit;
        }else{
            echo json_encode(['status' => false, 'data' =>[],'msg'=>'Something went wrong.']);
            exit;
        }
    }

    function get_staff_applicant_count() {
        $reqData = request_handler('access_recruitment_admin');
        if (!empty($reqData->data) && isset($reqData->data->staffId)) {
            $staffId = $reqData->data->staffId;
            $type = $reqData->data->type;
            $mode = $reqData->data->mode;
            $start_applicant_count = $this->Recruitment_user_management->get_staff_applicant_count($staffId,$type,$mode);
            echo json_encode(['status' => true, 'data' => $start_applicant_count]);
            exit;
        }else{
            echo json_encode(['status' => false, 'data' =>[],'msg'=>'Something went wrong.']);
            exit;
        }
    }


    function get_recruitment_area() {
        request_handler('access_recruitment');

        $result = $this->Recruitment_user_management->get_recruitment_area_title();

        echo json_encode(['status' => true, 'data' => $result]);
    }

    function add_new_recruiter_staff() {
        $reqData = $reqData1 = request_handler('access_recruitment_admin');
        $reqData = $reqData->data;

        if (!empty($reqData->staffId) && !empty($reqData->allocation_area)) {
            require_once APPPATH . 'Classes/recruitment/admin.php';
            $objAdmin = new AdminClass\AdminRecruit();

            $objAdmin->setAdminid($reqData->staffId);

            $recruter_details = ['adminId' => $reqData->staffId, 'approval_permission' => 1, 'status' => 1];
            $this->basic_model->update_records('recruitment_staff', $recruter_details, ['adminId' => $reqData->staffId]);

            // add recruiter area
            $this->Recruitment_user_management->update_allocation_recruiter_area($reqData->allocation_area, $reqData->preffer_allocation_area, $reqData->staffId);

            $log_title = 'User is saved as recruiter admin, admin id :- '.$reqData->staffId;
            $this->loges->setLogType('recruitment_staff');
            $this->loges->setTitle($log_title);
            $this->loges->setUserId($reqData1->adminId);
            $this->loges->setCreatedBy($reqData1->adminId);
            $this->loges->setDescription(json_encode($reqData1));
            $this->loges->createLog();
            echo json_encode(['status' => true]);
        }
    }

    // using this method get recuritment staff list
    public function get_staff_members() {
        $reqData = request_handler('access_recruitment_admin');
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $response = $this->Recruitment_user_management->get_staff_members($reqData);
            echo json_encode($response);
        }
    }

    public function disable_staff() {
        $request = request_handler('access_recruitment_admin');
        $reqData = $request->data;
        if (!empty($reqData)) {
            $validation_rules = array(
                array('field' => 'account_allocated_type', 'label' => 'Disable Account', 'rules' => 'required'),
                array('field' => 'disable_account', 'label' => 'Allocated Account', 'rules' => 'required'),
                array('field' => 'relevant_note', 'label' => 'Relavant Note', 'rules' => 'required'),
                array('field' => 'staffId', 'label' => 'Staff Note', 'rules' => 'required'),
            );

            $log_title = 'Recruiter admin is disable, user id :- '.$reqData->staffId;
            $this->loges->setLogType('recruitment_staff');
            $this->loges->setTitle($log_title);
            $this->loges->setUserId($request->adminId);
            $this->loges->setCreatedBy($request->adminId);
            $this->loges->setDescription(json_encode($request));
            $this->loges->createLog();

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run() == TRUE) {
                $return = $this->Recruitment_user_management->disable_staff($reqData);
                //$return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
        }
    }

    public function enable_recruiter_staff() {
        $request = request_handler('access_recruitment_admin');
        $reqData = $request->data;

        if (!empty($reqData)) {
            $validation_rules = array(
                array('field' => 'staffId', 'label' => 'staff Id', 'rules' => 'required'),
            );

            $log_title = 'Recruiter admin is enable, user id :- '.$reqData->staffId;
            $this->loges->setLogType('recruitment_staff');
            $this->loges->setTitle($log_title);
            $this->loges->setUserId($request->adminId);
            $this->loges->setCreatedBy($request->adminId);
            $this->loges->setDescription(json_encode($request));
            $this->loges->createLog();

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run() == TRUE) {
                $this->basic_model->update_records('recruitment_staff', ['status' => 1], ['adminId' => $reqData->staffId]);

                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
        }
    }

    public function get_department() {
        $reqData = $reqData1 = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $reqData = json_decode($reqData->data);
            $result = $this->Recruitment_model->get_department($reqData);
            echo json_encode($result);
        }
    }

    public function create_department() {
        $reqData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqData->adminId);
        #pr($reqData);
        require_once APPPATH . 'Classes/recruitment/recruitment_department.php';
        $objAdmin = new classRecuritmentDepartment\Recruitment_department();

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = (!empty($data['id'])) ? $data['id'] : false;

            $this->form_validation->set_data($data);

            $validation_rules = array(
                array('field' => 'name', 'label' => 'Department Name', 'rules' => 'required'),
            );
            // set rules form validation
            $this->form_validation->set_rules($validation_rules);
            if ($this->form_validation->run()) {

                $objAdmin->setName($data['name']);

                if ($data['id'] > 0) {
                    // update department
                    $objAdmin->setId($data['id']);
                    $objAdmin->updateRecuritmentDepartment();
                    $this->loges->setTitle('Department update: ' . $objAdmin->getName());
                } else {
                    // create department
                    $dep_id = $objAdmin->creatRecuritmentDepartment();
                    $this->loges->setTitle('New Department created: ' . $objAdmin->getName());
                }

                $this->loges->setCreatedBy($reqData->adminId);
                $this->loges->setUserId($reqData->adminId);
                $this->loges->setDescription(json_encode($reqData->data));
                $this->loges->createLog();
                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($response);
        }
    }

    public function update_alloted_department() {
        $reqData = request_handler('access_recruitment');
        $allotedData = $reqData->data;

        if (!empty($allotedData)) {

            $log_title = 'Recruiter Allocated/Preferred service area is updated, recruiter Id :-'.$allotedData->staffId;
            $this->loges->setLogType('recruitment_staff');
            $this->loges->setTitle($log_title);
            $this->loges->setUserId($reqData->adminId);
            $this->loges->setCreatedBy($reqData->adminId);
            $this->loges->setDescription(json_encode($reqData));
            $this->loges->createLog();

            if (!empty($allotedData->preffered_area) || !empty($allotedData->recruiter_area)) {
                $this->Recruitment_user_management->update_allocation_recruiter_area($allotedData->recruiter_area, $allotedData->preffered_area, $allotedData->staffId);
            }

            $response = array('status' => true);
            echo json_encode($response);
        }
    }

    public function get_staff_calander_task() {
        $reqData = request_handler('access_recruitment');

        if (isset($reqData->data->staffId)) {
            $adminId = $reqData->data->staffId;

            $result = $this->Recruitment_user_management->get_staff_task($reqData->data);

            $response = array('status' => true, 'data' => $result);
            echo json_encode($response);
        }
    }      

    public function get_round_robin_data_list() {
        $reqData = request_handler('access_recruitment_admin');
        $response = $this->Recruitment_user_management->get_recruiter_admin_list($reqData->data);
        echo json_encode($response);
    }

    public function update_round_robin_status() {
        $reqData = request_handler('access_recruitment_admin');

        $this->loges->setCreatedBy($reqData->adminId);
        $reqData = $reqData->data;
        if (!empty($reqData)) {
            $validation_rules = array(
                array('field' => 'id', 'label' => 'Recruiter', 'rules' => 'required'),
                array('field' => 'status', 'label' => 'Recruiter status', 'rules' => 'required')
            );
            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run() == TRUE) {
                $return = $this->Recruitment_user_management->update_round_robin_status($reqData);
                if (isset($return['status']) && $return['status']) {
                    $this->loges->setUserId($reqData->id); // set participant id in log
                    $this->loges->setDescription(json_encode($reqData));   // set all request data in participant in log
                    $statusText = isset($reqData->status) && $reqData->status == 1 ? 'from OFF to ON' : ' from ON to OFF';
                    $HCMGRId = isset($reqData->id) && $reqData->id > 0 ? $reqData->id : '';
                    $this->loges->setTitle('HCMGR-Id - ' . $HCMGRId . ' Round robin change status ' . $statusText);  // set title in log
                    $this->loges->createLog(); // create log
                }
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
        } else {
            $return = array('status' => false, 'error' => 'Something went wrong.');
        }
        echo json_encode($return);
    }

    public function get_recruiter_applicant_list() {
        $reqData = request_handler('access_recruitment_admin');
        $response = $this->Recruitment_user_management->get_recruiter_applicant_list($reqData->data);
        echo json_encode($response);
    }

    public function get_recruiter_name() {
        $reqData = request_handler('access_recruitment_admin');
        $reqData->data = json_decode($reqData->data);
        $rows = $this->Recruitment_user_management->get_recruiter_name($reqData->data);
        echo json_encode($rows);
    }

    public function get_recruiter_and_its_task_count() {
        $reqData = request_handler('access_recruitment');
        if (isset($reqData->data->staffId)) {
            $staffId = $reqData->data->staffId;
            $result = $this->Recruitment_user_management->get_recruiter_and_its_task_count($staffId);
            $response = array('status' => true, 'data_count' => $result);
            echo json_encode($response);
        }
    }
    
    function get_json(){
       $res = $this->basic_model->get_record_where('recruitment_applicant_applied_application', '', '');
       echo json_encode($res);
    }
}
