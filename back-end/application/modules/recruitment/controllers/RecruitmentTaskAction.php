<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property-read \Recruitment_task_action $Recruitment_task_action
 * @property-read \Recruitment_applicant_stages_model $stage_model
 * @package 
 */
class RecruitmentTaskAction extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Recruitment_task_action');
        $this->load->model('Recruitment_applicant_stages_model','stage_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
        $this->load->library('UserName');
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

    public function get_recruiter_listing_for_create_task() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $result = $this->Recruitment_task_action->get_recruiter_listing_for_create_task($reqData->data);
            echo json_encode($result);
        }
    }

    public function get_selected_recruiter() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $result = $this->Recruitment_task_action->get_selected_recruiter($reqData->data);
            echo json_encode($result);
        }
    }

    public function get_applicant_option_for_create_task() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $validation_rules = array(
                array('field' => 'task_stage', 'label' => 'task stage', 'rules' => 'required'),
                array('field' => 'search', 'label' => 'reason note', 'rules' => 'required'),
            );

            $this->form_validation->set_data((array) $reqData->data);
            $this->form_validation->set_rules($validation_rules);

            $extra_parameter = [];
            if (isset($reqData->data->application_id) && !empty($reqData->data->application_id)) {
                $extra_parameter['application_id'] = $reqData->data->application_id;
            }


            if ($this->form_validation->run()) {
                $result = $this->Recruitment_task_action->get_applicant_option_for_create_task($reqData->data, $reqData->adminId, $extra_parameter);
            } else {
                $errors = $this->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($result);
            exit();
        }
    }

    function check_assigned_user($d, $data) {
        $data = json_decode($data, true);

        $assigned_user = $data['assigned_user'];
        $current_adminId = $data['current_admin'];

        // first check its not blank mean need to assign at least one recruiter
        if (empty($assigned_user)) {
            $this->form_validation->set_message('check_assigned_user', 'Please assign at least one staff user');
            return false;
        } elseif (count($assigned_user) > 12) { // check assign usered can not greater than 12
            $this->form_validation->set_message('check_assigned_user', 'Assigned staff user can not be greater than 12');

            return false;
        } else {
            // check here primary recruiter assign or not
            $primary_recruiter_found = false;

            foreach ($assigned_user as $val) {
                if (!empty($val['primary_recruiter']) && ($val['primary_recruiter'] === '1')) {
                    $primary_recruiter_found = true;


                    // check its recruiter admin
                    // if its not recruiter admin then primary recruiter and current admin user id should be same
                    $its_recruiter_admin = check_its_recruiter_admin($current_adminId);
                    if (!$its_recruiter_admin) {

                        if ((int) $val['value'] !== (int) $current_adminId) {
                            $this->form_validation->set_message('check_assigned_user', 'Staff user can only assign primary staff to it self');
                            return false;
                        }
                    }
                }
            }

            if (!$primary_recruiter_found) {
                $this->form_validation->set_message('check_assigned_user', 'Primary staff user not found');
                return false;
            }
        }
    }

    function get_recruiter_stage() {
        $reqData = request_handler('access_recruitment');

        $its_recruiter_admin = check_its_recruiter_admin($reqData->adminId);
        if (!empty($reqData)) {
            $where = '';
            $recruiter_data = [];

            if (!$its_recruiter_admin) {
                $where = ['recruiter_view' => 0];

                $admin_data = $this->basic_model->get_row('member', ["concat(firstname,' ',lastname) as label", "id as value"], ['id' => $reqData->adminId]);
                $admin_data = json_decode(json_encode($admin_data), true);

                $admin_data['non_removal_primary'] = true;
                $admin_data['primary_recruiter'] = '1';
                $recruiter_data[] = $admin_data;
            }

            $result['task_stage_option'] = $this->basic_model->get_record_where_orderby('recruitment_task_stage', ['name as label', 'id as value', "stage_label_id as stage_number", "key as stage_key"], $where,'sort_order');
            $result['recruitment_location'] = $this->basic_model->get_record_where('recruitment_location', ['name as label', 'id as value'], ['archive' => 0]);
            $result['its_recruiter_admin'] = $its_recruiter_admin;
            $result['recruiter_data'] = $recruiter_data;

            $return = ['status' => true, 'data' => $result];
            echo json_encode($return);
        }
    }

    /*
     * function : check_attach_applicant
     * use : custum server side validation method use in create task request for verfiy attach applicant list
     */

    function check_attach_applicant($d, $reqest_data) {

        // here $d is user_less parameter and req_data is default request varibale
        $reqest_data = json_decode($reqest_data);
        $data = $reqest_data->data;

        // first check applicant list not empty
        if (empty($data->applicant_list)) {
            $this->form_validation->set_message('check_attach_applicant', 'Please select at least on applicant to continue');
            return false;
        } else {

            // object to array converstion
            $attach_appicant = obj_to_arr($data->applicant_list);


            $old_applicant = $new_applicant = [];
            foreach ($attach_appicant as $val) {
                if (!empty($val['id']) && $val['id'] > 0) {
                    $old_applicant[] = $val['applicant_id'];
                } else {
                    $new_applicant[] = $val['applicant_id'];
                }
            }


            $applicant_map = array_column($attach_appicant, 'label', 'applicant_id');


            // for verify new applicant
            if (!empty($new_applicant)) {
                $extra_p = array();
                $extra_p['applicant_ids'] = $new_applicant;

                // make custom request for according to required paramter for method of model (get_applicant_option_for_create_task)
                $cust_req = array('search' => '', 'task_stage' => $data->task_stage);

                // call function to verify applicant list
                $response = $this->Recruitment_task_action->get_applicant_option_for_create_task((object) $cust_req, $reqest_data->adminId, $extra_p);

                // verify difference
                return $this->verify_attach_applicant_list_diif($response, $new_applicant, $applicant_map);
            }

            if (!empty($old_applicant)) {
                $response = $this->Recruitment_task_action->verify_edit_task_applicant($old_applicant, $data, $applicant_map);

                // verify difference
                return $this->verify_attach_applicant_list_diif($response, $old_applicant, $applicant_map);
            }
        }
    }

    private function verify_attach_applicant_list_diif($query_response, $req_app_ids, $applicant_map) {
        if (!empty($query_response)) {
            // object to array conversion
            $return_applicant_list = obj_to_arr($query_response);

            $verfied_app_ids = array_column($return_applicant_list, 'applicant_id');



            $diff = array_diff($req_app_ids, $verfied_app_ids);

            if (!empty($diff)) {
                foreach ($diff as $id) {
                    $applicant_names[] = $applicant_map[$id];
                }

                $error = implode(', ', $applicant_names) . " not valid applicant for this stage or may be not assign to staff user";
                $this->form_validation->set_message('check_attach_applicant', $error);

                return false;
            } else {
                return true;
            }
        } else {
            foreach ($req_app_ids as $val) {
                $applicant_names[] = $applicant_map[$val];
            }

            $error = implode(', ', $applicant_names) . " applicant not valid applicant for this stage or may be not assign to staff user";
            $this->form_validation->set_message('check_attach_applicant', $error);

            return false;
        }
    }

    public function create_task() {
        $reqData = request_handler('access_recruitment');

        $data = (array) $reqData->data;

        $ass_temp = array('assigned_user' => $data['assigned_user'], 'current_admin' => $reqData->adminId);

        if (!empty($data)) {
            $validation_rules = array(
                array('field' => 'task_name', 'label' => 'Task name', 'rules' => 'required|max_length[100]', 'errors' => ['max_length' => '%s can not be more than 100 characters.']),
                array('field' => 'task_stage', 'label' => 'Task stage', 'rules' => 'required'),
                array('field' => 'task_date', 'label' => 'Task date', 'rules' => 'required'),
                array('field' => 'start_time', 'label' => 'Start time', 'rules' => 'required'),
                array('field' => 'end_time', 'label' => 'End Time', 'rules' => 'required'),
                array('field' => 'training_location', 'label' => 'training location', 'rules' => 'required'),
                array('field' => 'relevant_task_note', 'label' => 'Relevant task note', 'rules' => 'max_length[500]', 'errors' => ['max_length' => '%s can not be more than 500 characters.']),
                array('field' => 'max_applicant', 'label' => 'Max applicant', 'rules' => 'required|integer|less_than[11]'),
                array('field' => 'specific_assign_check', 'label' => 'Max applicant', 'rules' => 'callback_check_assigned_user[' . json_encode($ass_temp) . ']'),
//                array('field' => 'specific_applicant_check', 'label' => 'Max applicant', 'rules' => 'callback_check_attach_applicant[' . json_encode($reqData) . ']'),
            );


            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $taskId = $this->Recruitment_task_action->create_task($reqData->data, $reqData->adminId);

                //logs
                $this->loges->setCreatedBy($reqData->adminId);
                $this->loges->setUserId($taskId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->setTitle('New Task created : Task Id ' . $taskId);
                $this->loges->createLog();

                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
            exit();
        }
    }

    public function get_recruitment_task_list() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $result = $this->Recruitment_task_action->get_recruitment_task_list($reqData->data, $reqData->adminId);
            echo json_encode($result);
        }
    }

    function complete_recruiterment_task() {
        $reqData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqData->adminId);
        $request = $reqData->data;

        if (!empty($request->taskId)) {
            $taskId = $request->taskId;
            $its_recruiter_admin = check_its_recruiter_admin($reqData->adminId);

            $res = $this->basic_model->get_row('recruitment_task', ['created_by', 'start_datetime', 'commit_status', 'task_stage'], ['id' => $taskId]);
            if (!$its_recruiter_admin) {
                $created_by = '';
                if (!empty($res)) {
                    $created_by = $res->created_by;
                }

                if ($created_by != $reqData->adminId) {
                    echo json_encode(['status' => false, 'error' => 'Sorry you do not have permission to complete this task.']);
                    exit();
                }
            }

            $start_datetime = '';
            if (!empty($res)) {
                $start_datetime = $res->start_datetime;
            }

            if (!empty($start_datetime) && strtotime($start_datetime) > strtotime(DATE_TIME)) {
                echo json_encode(['status' => false, 'error' => 'Future task can not be mark as completed.']);
                exit();
            }

            if ($res->task_stage == 3 || $res->task_stage == 6) {
                if ($res->task_stage != 1) {
                    echo json_encode(['status' => false, 'error' => 'Need to commit first for complete task.']);
                    exit();
                }
            }

            $this->basic_model->update_records('recruitment_task', ['status' => 2, 'action_at' => DATE_TIME], ['id' => $taskId]);

            //logs
            $this->loges->setUserId($reqData->adminId);
            $this->loges->setDescription(json_encode($reqData));
            $this->loges->setTitle('Completed task : Task Id ' . $taskId);
            $this->loges->createLog();

            echo json_encode(['status' => true]);
        }
    }

    function archive_recruiterment_task() {
        $reqData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqData->adminId);
        $request = $reqData->data;

        if (!empty($request->taskId)) {
            $taskId = $request->taskId;

            $response = $this->Recruitment_task_action->check_task_eligibility_for_archive($taskId, $reqData->adminId);

            if ($response['status']) {
                $this->basic_model->update_records('recruitment_task', ['status' => 4, 'action_at' => DATE_TIME], ['id' => $taskId]);

                //logs
                $this->loges->setUserId($reqData->adminId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->setTitle('Archive task : Task Id ' . $taskId);
                $this->loges->createLog();

                echo json_encode(['status' => true]);
                exit();
            } else {
                echo json_encode($response);
                exit();
            }
        }
    }

    public function get_recruitment_task_list_calendar() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $result = $this->Recruitment_task_action->get_recruitment_task_list_calendar($reqData->data, $reqData->adminId);
            echo json_encode($result);
        }
    }

    public function get_task_details() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $result = $this->Recruitment_task_action->get_task_details($reqData->data, $reqData->adminId);
            echo json_encode(['status' => true, 'data' => $result]);
        }
    }

    public function update_task() {
        $reqData = request_handler('access_recruitment');

        $data = (array) $reqData->data;

        $ass_temp = array('assigned_user' => $data['assigned_user'], 'current_admin' => $reqData->adminId);

        if (!empty($data)) {
            $validation_rules = array(
                array('field' => 'status', 'label' => 'Status', 'rules' => 'required'),
                array('field' => 'task_stage', 'label' => 'Task stage', 'rules' => 'required'),
                array('field' => 'specific_assign_check', 'label' => 'Max applicant', 'rules' => 'callback_check_assigned_user[' . json_encode($ass_temp) . ']'),
                array('field' => 'specific_applicant_check', 'label' => 'Max applicant', 'rules' => 'callback_check_attach_applicant[' . json_encode($reqData) . ']'),
            );

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $taskId = $this->Recruitment_task_action->update_task($reqData->data, $reqData->adminId);
                $taskId = $reqData->data->taskId;
                //logs
                $this->loges->setCreatedBy($reqData->adminId);
                $this->loges->setUserId($reqData->adminId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->setTitle('Task Update : Task Id ' . $taskId);
                $this->loges->createLog();
                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
            exit();
        }
    }

    function verify_task_confirmation_by_email() {
        $reqData = request_handler(0, 0);

        if (!empty($reqData)) {
            $validation_rules = array(
                array('field' => 'token', 'label' => 'token', 'rules' => 'required'),
                array('field' => 'action', 'label' => 'action', 'rules' => 'required'),
            );

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $return = $this->Recruitment_task_action->verify_task_confirmation_token($reqData);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
            exit();
        }
    }

    function resend_task_mail_to_applicant() {
        $reqestData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqestData->adminId);
        $reqData = $reqestData->data;

        if (!empty($reqData)) {
            $reqData->taskId = isset($reqData->taskId) ? $reqData->taskId : "";
            $validation_rules = array(
                array('field' => 'taskId', 'label' => 'task Id', 'rules' => 'required'),
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required|callback_verify_applicant_with_task[' . $reqData->taskId . ']'),
            );

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $row = $this->basic_model->get_row('recruitment_applicant', ['current_stage'], ['id' => $reqData->applicant_id, 'archive' => 0]);

               $applicant_all_stage = $this->stage_model->get_all_stage_key_for_applicant($reqData->applicant_id, $reqData->application_id);
                $db_stages = [];
                if(!empty($applicant_all_stage))
                $db_stages = array_column($applicant_all_stage, 'key_name');

                if(!empty($db_stages) && in_array('individual_interview', $db_stages) && !empty($row)){
                    #method call in individual interview and resend mail is call from stage "Schedule Individual interview" and "Applicant Responses"
                    $this->Recruitment_task_action->resend_task_mail_to_applicant($reqData, $reqestData->adminId,'for_individual_interview');    
                }else{
                    #method call in individual/group interview and resend mail is call from stage "Schedule Cab Day " and "CAB Applicant Responses" OR "group_schedule_interview" and "group_applicant_responses"
                    $this->Recruitment_task_action->resend_task_mail_to_applicant($reqData, $reqestData->adminId,'for_group_cab');
                }                

                //logs
                $this->loges->setUserId($reqData->taskId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->setTitle('Resend reminder mail : Task Id ' . $reqData->taskId);
                $this->loges->createLog();

                $return = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
            exit();
        }
    }

    function verify_applicant_with_task($applicant_id, $taskId) {
        if ($applicant_id && $taskId) {
            $res = $this->basic_model->get_row('recruitment_task_applicant', ['id', 'status'], ['taskId' => $taskId, 'applicant_id' => $applicant_id, 'archive' => 0]);

            if (!$res) {
                $error = "Applicant Id not valid for this task";
                $this->form_validation->set_message('verify_applicant_with_task', $error);

                return false;
            } elseif ($res->status != 0) {
                $error = "Applicant already answered inviatation mail";
                $this->form_validation->set_message('verify_applicant_with_task', $error);

                return false;
            }

            return true;
        }
    }

    function get_available_group_or_cab_interview_for_applicant() {
        $reqestData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqestData->adminId);
        $reqData = $reqestData->data;

        if (!empty($reqData->applicant_id)) {
            $extra['applicant_id'] = $reqData->applicant_id;
            $result['available_interview'] = $this->Recruitment_task_action->get_available_group_or_cab_interview_for_applicant($reqData->task_stage, $extra);

            $applicant = $this->basic_model->get_row('recruitment_applicant', ["concat(firstname,' ',lastname) as fullname"], ['id' => $reqData->applicant_id]);

            if (!empty($applicant)) {
                $result['applicant_name'] = $applicant->fullname;
            }

            echo json_encode(['status' => true, 'data' => $result]);
            exit();
        }
    }

    function add_applicant_in_available_interview() {
        $reqestData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqestData->adminId);
        $reqData = $reqestData->data;

        if (!empty($reqData->taskId) && isset($reqData->applicant_id)) {
            $reqData->task_stage = (isset($reqData->task_stage)) ? $reqData->task_stage : '';
            $validation_rules = array(
                array('field' => 'taskId', 'label' => 'task Id', 'rules' => 'required|callback_check_in_task_having_available_one_space[' . $reqData->task_stage . ']'),
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required|callback_check_applicant_declined_group_interview[' . json_encode($reqestData) . ']'),
                array('field' => 'request_type', 'label' => 'request type', 'rules' => 'required'),
                array('field' => 'task_stage', 'label' => 'task stage', 'rules' => 'required'),
            );

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $this->Recruitment_task_action->add_applicant_in_available_interview($reqData, $reqestData->adminId);

                $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
                //logs
                $this->loges->setUserId($reqData->taskId);
                $txt = ($reqData->task_stage == 3) ? 'Group interview' : 'Cab Day';
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->setTitle('Reschedule ' . $txt . ' of applicant -  ' . $applicantName);
                $this->loges->setSpecific_title("Reschedule " . $txt);
                $this->loges->createLog();

                $return = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
            exit();
        }
    }

    function check_applicant_declined_group_interview($applicant_id, $req_data_json) {
        $requrest_data = json_decode($req_data_json);
        $req_data = $requrest_data->data;
        if ($applicant_id && $req_data->taskId) {

            //  check applicant already exist in given task
            $res = $this->basic_model->get_row('recruitment_task_applicant', ['id'], ['taskId' => $req_data->taskId, 'applicant_id' => $applicant_id, 'status' => 2]);
            if (!empty($res)) {
                $error = "Applicant already decliened given task id";
                $this->form_validation->set_message('check_applicant_declined_group_interview', $error);

                return false;
            }


            $extra_p['applicant_ids'] = [$applicant_id];

            // make custom request for according to required paramter for method of model (get_applicant_option_for_create_task)
            $cust_req = array('search' => '', 'task_stage' => $req_data->task_stage);

            // call function to verify applicant list
            $response = $this->Recruitment_task_action->get_applicant_option_for_create_task((object) $cust_req, $requrest_data->adminId, $extra_p);
//            last_query();
            if (empty($response)) {
                $error = "Applicant not eligible for this interview";
                $this->form_validation->set_message('check_applicant_declined_group_interview', $error);
                return false;
            }

            return true;
        }
    }

    function check_in_task_having_available_one_space($taskId, $task_stage) {
        if ($taskId) {
            // check available space in task id
            $extra['taskId'] = $taskId;
            $res = $this->Recruitment_task_action->get_available_group_or_cab_interview_for_applicant($task_stage, $extra);

            if (empty($res)) {
                $error = "Either Task slots are filled or Task has already started, Please select any other task.";
                $this->form_validation->set_message('check_in_task_having_available_one_space', $error);

                return false;
            }

            return true;
        }
    }

    function task_mark_as_decline_to_applicant() {
        $reqestData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqestData->adminId);
        $reqData = $reqestData->data;
        
        if (!empty($reqData)) {
            $reqData->taskId = isset($reqData->taskId) ? $reqData->taskId : "";
            $validation_rules = array(
                array('field' => 'taskId', 'label' => 'task Id', 'rules' => 'required'),
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required|callback_verify_applicant_with_task[' . $reqData->taskId . ']'),
            );
            
            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);
            
            if ($this->form_validation->run()) {
                $res = $this->Recruitment_task_action->task_mark_as_decline_to_applicant_by_recuirter($reqData->taskId, $reqData->applicant_id,$reqestData->adminId);
                
                //logs
                if($res['status']){
                    $adminName = $this->username->getName('admin', $reqestData->adminId);
                    $this->loges->setUserId($reqData->taskId);
                    $this->loges->setDescription(json_encode($reqData));
                    $this->loges->setTitle('Applincant invitaion marked as declied by recuiter ('.$adminName.') : Task Id ' . $reqData->taskId);
                    $this->loges->createLog();
                }
                $return = $res;
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
            exit();
        }
    }
	
	function get_create_task_form_option(){
		$reqestData = request_handler('access_recruitment');
        
		
		$result = $this->Recruitment_task_action->get_create_task_form_option($reqestData);
            

            echo json_encode(['status' => true, 'data' => $result]);
            exit();
        
	}

}
