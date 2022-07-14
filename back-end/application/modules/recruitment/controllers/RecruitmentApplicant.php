<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read \Recruitment_applicant_model $Recruitment_applicant_model
 * @property-read \Recruitment_applicant_stages_model $stage_model
 * @package
 */
class RecruitmentApplicant extends MX_Controller
{
    use formCustomValidation;
    var $application_status = [
        "0" => "Applied",
        "1" => "In Progress",
        "2" => "Rejected",
        "3" => "Hired",
    ];

    const  DRAFT_STATUS = 4;

    function __construct()
    {
        parent::__construct();
        $this->load->model('Recruitment_group_interview_model');
        $this->load->model('Recruitment_applicant_model');
        $this->load->model('Recruitment_applicant_stages_model', 'stage_model');
        $this->load->library('form_validation');
        $this->load->library('UserName');
        $this->load->model('common/List_view_controls_model');
        $this->form_validation->CI = &$this;
        $this->loges->setLogType('recruitment_applicant');
        $this->load->model('document/Document_type_model');
        $this->load->model('sales/Feed_model');
        $this->load->model('Recruitment_applicant_move_to_hcm');
        $this->load->model('../../admin/models/Notification_model');
        $this->load->library('Asynclibrary');
    }

    private function sendResponse($data, $succes_msg = '') {
        if ($succes_msg) {
            $response = ['status' => true, 'data' => $data, 'msg' => $succes_msg];
        } else {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        }
        echo json_encode($response);
        exit();
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

    /**
     * fetches all the application statuses
     */
    function get_application_statuses() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $data = null;
            foreach($this->application_status as $value => $label) {
                $newrow = null;
                $newrow['label'] = $label;
                $newrow['value'] = $value;
                $data[] = $newrow;
            }
            $response = array('status' => true, 'data' => $data);

            echo json_encode($response);
        }
    }

    /**
     * fetches all the recruitment stages and sub-stages
     */
    function get_all_recruitment_stages()
    {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $response = $this->Recruitment_applicant_model->get_all_recruitment_stages();

            echo json_encode($response);
        }
    }

    /**
     * fetches history of application
     */

    public function get_field_history() {
        $reqData = request_handler('access_recruitment');
        //print_r($reqData);exit();
        if (empty($reqData)) {
            return;
        }
        $items = $this->Recruitment_applicant_model->get_field_history($reqData->data,$reqData);
        $this->sendResponse($items, 'Success');
    }

    /**
     * fetches list of applications of applicants
     */
    function get_applications()
    {
        $reqData = request_handler('access_recruitment');
        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            if (!empty($filter_condition)) {
                $filter_condition = str_replace(['id','job_position', 'firstname', 'lastname', 'oa_status','process_status_label', 'created','hired_as_member'], ['raa.id','rjp.title', 'ra.firstname', 'ra.lastname', 'rjaa.status','raa.application_process_status', 'raa.created','ra.hired_as'], $filter_condition);
            }
        }
        if (!empty($reqData->data)) {
            $response = $this->Recruitment_applicant_model->get_applications($reqData->data, $reqData->adminId, $filter_condition,$reqData);
            echo json_encode($response);
        }
    }

     /**
     * fetches list of applications of applicants by applicantion_id
     */
    function get_applications_by_id()
    {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $validation_rules = array(
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required'),
            );

            $this->form_validation->set_data((array) $reqData->data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $response = $this->Recruitment_applicant_model->get_applications($reqData->data, $reqData->adminId,'',$reqData);
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
            echo json_encode($response);
        }
    }

    /**
     * fetches list of applicants
     */
    function get_applicants()
    {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $response = $this->Recruitment_applicant_model->get_applicants($reqData->data, $reqData->adminId);

            echo json_encode($response);
        }
    }

    /**
     * fetches list of recruiters already associated with applications
     */
    function get_application_recruiters()
    {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $response = $this->Recruitment_applicant_model->get_application_recruiters($reqData);

            echo json_encode($response);
        }
    }

    function get_job_postion_by_create_job()
    {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $s = $this->Recruitment_applicant_model->get_job_postion_by_create_job();

            $response = ['status' => true, 'data' => $s];
            echo json_encode($response);
        }
    }

    /**
     * fetches all the common flagging reasons
     */
    public function get_flag_reasons()
    {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $s = $this->Recruitment_applicant_model->get_flag_reasons();

            $response = ['status' => true, 'data' => $s];
            echo json_encode($response);
        }
    }

    function flage_applicant()
    {
        require_once APPPATH . 'Classes/recruitment/ActionNotification.php';

        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);
        $reqData = $reqData->data;

        if (!empty($reqData)) {
            $validation_rules = array(
                array('field' => 'reason_title', 'label' => 'reason title', 'rules' => 'required'),
                array('field' => 'reason_id', 'label' => 'reason', 'rules' => 'required'),
                array('field' => 'reason_note', 'label' => 'reason note', 'rules' => 'required'),
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required'),
            );

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $res = $this->Recruitment_applicant_model->check_already_flag_applicant($reqData->applicant_id);

                if (!empty($res)) {
                    $response = ['status' => false, 'error' => 'Applicant already flaged'];
                } else {
                    $this->Recruitment_applicant_model->flag_applicant($reqData, $adminId);
                    // set action notificaiton.
                    $objAction = new ActionNotification();
                    $objAction->setApplicant_id($reqData->applicant_id);
                    $objAction->setRecruiterId($adminId);
                    $objAction->setAction_type(1);
                    $objAction->createAction();

                    // create log
                    $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
                    $admin = $this->username->getName('admin', $adminId);
                    $this->loges->setLogType('recruitment_applicant');
                    $this->loges->setSpecific_title('Applicant Marked as flag by ' . $admin);
                    $this->loges->setTitle('Mark as flag applicant : ' . $applicantName . ' and applicant id:- ' . $reqData->applicant_id);
                    $this->loges->setUserId($reqData->applicant_id);
                    $this->loges->setDescription(json_encode($reqData));
                    $this->loges->createLog();

                    $response = ['status' => true];
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
            exit();
        }
    }

    function archive_applicant()
    {
        $reqData = request_handler('access_recruitment_admin');
        $this->loges->setCreatedBy($reqData->adminId);
        $adminName = $this->username->getName('admin', $reqData->adminId);
        $reqData = $reqData->data;

        if (!empty($reqData->applicant_id)) {

            $this->basic_model->update_records('recruitment_applicant', ['archive' => 1], ['id' => $reqData->applicant_id]);

            // create log
            $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
            $this->loges->setTitle('Archive applicant: ' . $applicantName);
            $this->loges->setSpecific_title('Archive applicant by: ' . $adminName);
            $this->loges->setUserId($reqData->applicant_id);
            $this->loges->setDescription(json_encode($reqData));
            $this->loges->createLog();

            echo json_encode(['status' => true]);
            exit();
        }
    }

    function get_requirement_flaged_applicants()
    {
        $reqData = request_handler('access_recruitment_admin');

        if (!empty($reqData->data)) {
            $response = $this->Recruitment_applicant_model->get_requirement_flaged_applicants($reqData->data, $reqData->adminId);

            echo json_encode($response);
        }
    }

    function dont_flag_applicant()
    {
        $reqData = request_handler('access_recruitment_admin');
        $this->loges->setCreatedBy($reqData->adminId);
        $adminId = $reqData->adminId;
        $reqData = $reqData->data;

        if (!empty($reqData->applicant_id)) {

            // dont flag applicant handle
            $this->Recruitment_applicant_model->dont_flag_applicant($reqData, $adminId);

            // set log data
            $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
            $adminName = $this->username->getName('admin', $adminId);

            $this->loges->setTitle('Flag request denieded of applicant: ' . $applicantName);
            $this->loges->setSpecific_title('Flag request denieded of applicant by: ' . $adminName);
            $this->loges->setUserId($reqData->applicant_id);
            $this->loges->setDescription(json_encode($reqData));
            $this->loges->createLog();

            echo json_encode(['status' => true]);
        }
    }

    function flag_applicant_approve()
    {
        $reqData = request_handler('access_recruitment_admin');
        $this->loges->setCreatedBy($reqData->adminId);
        $adminId = $reqData->adminId;
        $reqData = $reqData->data;

        if (!empty($reqData->applicant_id)) {

            // flag applicant
            $this->Recruitment_applicant_model->flag_applicant_approve($reqData, $adminId);

            // set log data
            $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
            $adminName = $this->username->getName('admin', $adminId);

            $this->loges->setTitle('Flag request approve of applicant: ' . $applicantName);
            $this->loges->setSpecific_title('Flag request approve by: ' . $adminName);
            $this->loges->setUserId($reqData->applicant_id);
            $this->loges->setDescription(json_encode($reqData));
            $this->loges->createLog();

            echo json_encode(['status' => true]);
        }
    }

    function get_duplicate_requirement_applicants()
    {
        $reqData = request_handler('access_recruitment_admin');
        if (!empty($reqData->data)) {
            $response = $this->Recruitment_applicant_model->get_recruitment_duplicate_applicants($reqData->data, $reqData->adminId);
            echo json_encode($response);
        }
    }

    function update_duplicate_application_relevant_note()
    {
        $reqData = request_handler('access_recruitment_admin');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($reqData->adminId);
        $reqData = $reqData->data;

        if (!empty($reqData)) {
            $validation_rules = array(
                array('field' => 'id', 'label' => 'Application Id', 'rules' => 'required'),
                array('field' => 'relevant_note', 'label' => 'Relevant Note', 'rules' => 'required'),
                array('field' => 'applicationNumber', 'label' => 'Application Number', 'rules' => 'required')
            );
            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run() == TRUE) {
                $return = $this->Recruitment_applicant_model->update_duplicate_application_relevant_note($reqData);
                if (isset($return['status']) && $return['status']) {

                    // set log details
                    $applicantName = $this->username->getName('applicant', $reqData->id);
                    $adminName = $this->username->getName('admin', $adminId);

                    $this->loges->setUserId($reqData->id);
                    $this->loges->setDescription(json_encode($reqData));
                    $this->loges->setTitle('Applicant - ' . $applicantName . ' Relevant note added on Duplicate applicant application');
                    $this->loges->setSpecific_title('Relevant note added on Duplicate applicant application by ' . $adminName);
                    $this->loges->createLog();
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

    function update_duplicate_application_status()
    {
        $reqData = request_handler('access_recruitment_admin');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($reqData->adminId);
        $statusType = [
            'reject' => 'Pending to Rejected',
            'accept_addnem' => 'Pending to Accepted with add to current applications',
            'accept_editexisting' => 'pending to Accepted with modify existing applications',
        ];
        $reqData = $reqData->data;

        if (!empty($reqData)) {
            $validation_rules = array(
                array('field' => 'id', 'label' => 'Application Id', 'rules' => 'required'),
                array('field' => 'status', 'label' => 'Application Status', 'rules' => 'required'),
                array('field' => 'applicationNumber', 'label' => 'Application Number', 'rules' => 'required')
            );
            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run() == TRUE) {
                $return = $this->Recruitment_applicant_model->update_duplicate_application_status($reqData, $statusType);
                if (isset($return['status']) && $return['status']) {

                    // set log details
                    $applicantName = $this->username->getName('applicant', $reqData->id);
                    $adminName = $this->username->getName('admin', $adminId);

                    $this->loges->setUserId($reqData->id); // set applicant id in log
                    $this->loges->setDescription(json_encode($reqData));   // set all request data in participant in log
                    $statusText = isset($statusType[$reqData->status]) ? $statusType[$reqData->status] : ' pending to ' . $reqData->status;

                    $this->loges->setTitle('Applicant - ' . $applicantName . ' Duplicate application status change from ' . $statusText);  // set title in log
                    $this->loges->setSpecific_title('Duplicate application status change from ' . $statusText . ' by ' . $adminName);
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

    /**
     * fetching the list of job applications of an applicant for selection
     */
    function get_applicant_job_application() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = $reqData->data;
        if (!empty($reqData->applicant_id)) {
            $result = $this->Recruitment_applicant_model->get_applicant_job_application($reqData->applicant_id);
            $data = null;
            if($result) {
                foreach($result as $row) {
                    $data[] = ["label" => $row->position_applied, "value" => $row->id];
                }
            }
            echo json_encode(['status' => true, 'data' => $data]);
        }
    }

    function get_applicant_info()
    {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;

        $reqData = $reqData->data;

        if (!empty($reqData->applicant_id)) {
            $this->load->library('ApplicantRequiredValidation');
            $this->applicantrequiredvalidation->setApplicantId($reqData->applicant_id);
            $this->applicantrequiredvalidation->setValidationRule(['applicant_id_duplicate' => true]);
            $res = $this->applicantrequiredvalidation->check_applicant_required_checks();
            if (!$res['status']) {
                echo json_encode(['status' => false, 'data' => 'Applicant not found']);
                exit();
            }

            // get applicant details
            $result['details'] = $this->Recruitment_applicant_model->get_applicant_info($reqData->applicant_id, $adminId);

            if (!empty($result['details'])) {
                // get applicant phone number
                $result['phones'] = $this->Recruitment_applicant_model->get_applicant_phone($reqData->applicant_id);

                // get applicant emails
                $result['emails'] = $this->Recruitment_applicant_model->get_applicant_email($reqData->applicant_id);

                // get applicant reference
                $result['references'] = $this->Recruitment_applicant_model->get_applicant_reference($reqData->applicant_id);

                // get applicant address
                $applicant_address = $this->Recruitment_applicant_model->get_applicant_address($reqData->applicant_id);        

                $result['applicant_address']['unit_number'] = isset($applicant_address->unit_number) ? $applicant_address->unit_number : null;
                $result['applicant_address']['address'] = isset($applicant_address->address) ? $applicant_address->address : null;
                $result['applicant_address']['manual_address'] = isset($applicant_address->manual_address) ? $applicant_address->manual_address : null;
                $result['applicant_address']['is_manual_address'] = isset($applicant_address->is_manual_address) ? $applicant_address->is_manual_address : null;


                $result['applications'] = $this->Recruitment_applicant_model->get_applicant_job_application($reqData->applicant_id);

                $result['last_update'] = $this->Recruitment_applicant_model->get_applicant_last_update($reqData->applicant_id);

                echo json_encode(['status' => true, 'data' => $result]);
            } else {

                echo json_encode(['status' => false, 'data' => 'Applicant not found']);
            }
        }
    }

    function get_applicant_main_stage_details()
    {
        $reqData = request_handler('access_recruitment');
        $reqData = $reqData->data;

        if (!empty($reqData->applicant_id) && !empty($reqData->application_id)) {

            $result['applicant_progress'] = $this->Recruitment_applicant_model->get_applicant_progress($reqData->applicant_id, $reqData->application_id);

            $result['application_current_stage'] = $this->stage_model->get_application_current_stage($reqData->application_id);
            $result['stage_details'] = $this->Recruitment_applicant_model->get_applicant_stage_details($reqData->applicant_id, $reqData->application_id);
            $result['stage_status_details'] = $this->Recruitment_applicant_model->get_last_action_complete_unsucess_stage_by_applicant_Id($reqData->applicant_id, $reqData->application_id);
            $result['stage_status_details'] = $this->get_prev_stage_status($result['stage_status_details'], $reqData->application_id, $reqData->applicant_id);
            echo json_encode(['status' => true, 'data' => $result]);
        }
    }

    /*
     * Get previous stage status by stageId
     * param {array} prevStage
     * return {array} prevStage
     */
    function get_prev_stage_status($prevStage, $application_id, $applicant_id) {
        $stageData = $this->basic_model->get_row('recruitment_applicant_stage', ['stageId', 'status'], ['stageId' => $prevStage['previous_stage'], 'application_id' => $application_id, 'applicant_id' => $applicant_id, 'archive' => 0]);
        if (!empty($stageData) && isset($stageData->status)) {
            $prevStage['prev_stage_status'] = $stageData->status;
        } else {
            $prevStage['prev_stage_status'] = 0;
        }
        return $prevStage;
    }

    function update_assign_recruiter()
    {
        $reqData = request_handler('access_recruitment_admin');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($reqData->adminId);
        $reqData = $reqData->data;

        if (!empty($reqData)) {

            $validation_rules = array(
                array('field' => 'recruiter', 'label' => 'Staff user id', 'rules' => 'required|callback_check_its_recruiter_admin_or_user'),
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'application id', 'rules' => 'required'),
            );

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $createTaskReview = false;

                # Following code needs to be removed when recruiter column is removed from
                # table tbl_recruitment_applicant
                # we now having that column at the application level
                $applicantData = $this->basic_model->get_row('recruitment_applicant', ['current_stage', 'recruiter'], ['id' => $reqData->applicant_id]);
                if (!empty($applicantData) && $applicantData->current_stage == 1 && (empty($applicantData->recruiter) || is_null($applicantData->recruiter))) {
                    $createTaskReview = true;
                }
                $this->basic_model->update_records('recruitment_applicant', ['recruiter' => $reqData->recruiter], ['id' => $reqData->applicant_id]);
                ####
                $applicationData = $this->basic_model->get_row('recruitment_applicant_applied_application', ['current_stage', 'recruiter'], ['id' => $reqData->applicant_id]);
                if (!empty($applicationData) && $applicationData->current_stage == 1 && (empty($applicationData->recruiter) || is_null($applicationData->recruiter))) {
                    $createTaskReview = true;
                }
                $this->basic_model->update_records('recruitment_applicant_applied_application', ['recruiter' => $reqData->recruiter], ['id' => $reqData->application_id]);

                # set log details
                $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
                $adminName = $this->username->getName('admin', $adminId);

                $this->loges->setTitle('Assign staff user updated of applicant: ' . $applicantName . ' application id: ' . $reqData->application_id);
                $this->loges->setSpecific_title('Assign staff user updated by ' . $adminName);
                $this->loges->setUserId($reqData->applicant_id);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();

                $response = ['status' => true];
                if ($createTaskReview) {
                    $this->load->model('RecruitmentAppliedForJob_model');
                    $this->RecruitmentAppliedForJob_model->createStageTask(['application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id, 'recruiter_id' => $reqData->recruiter, 'task_stage' => 1, 'applicant_name' => $applicantName, 'training_location' => DEFAULT_RECRUITMENT_LOCATION_ID]);
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
            exit();
        }
    }

    function check_its_recruiter($recruiterId)
    {
        $res = check_its_recruiter_admin($recruiterId);
        if ($res) {
            return true;
        } else {

            $this->form_validation->set_message('check_its_recruiter', 'Please provide valid recruiter id');
            return false;
        }
    }

    function check_its_recruiter_admin_or_user($recruiterId)
    {
        $res_admin = check_its_recruiter_admin($recruiterId);
        $res_user = check_its_recruiter_user($recruiterId);
        if ($res_admin == true || $res_user == true) {
            return true;
        } else {

            $this->form_validation->set_message('check_its_recruiter_admin_or_user', 'Please provide valid recruiter id');
            return false;
        }
    }

    function check_applicant_phone_interview_already_exist($applicantId)
    {
        $res = $this->Recruitment_applicant_model->get_applicant_phone_interview_classification($applicantId);

        if (!empty($res)) {
            $this->form_validation->set_message('check_applicant_phone_interview_already_exist', 'Phone interview classification already exist');
            return false;
        } else {
            return true;
        }
    }

    /**
     * when resend login details is requested from the applicant info page
     * setting the temp password and emailing the loging details to the applicant
     */
    function send_applicant_login() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = (array) $reqData->data;

        echo $this->Recruitment_applicant_model->send_applicant_login($reqData, $adminId);
        return true;
    }

    function update_applicant_stage_status()
    {
        $reqestData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqestData->adminId);
        $reqData = $reqestData->data;

        if (!empty($reqData)) {
            $reqData->applicant_rule = json_encode([
                'type_of_checks' => ['not_flagged', 'not_archive', 'on_stage_complete_verified_details'],
                'applicant_id' => $reqData->applicant_id,
                'status' => $reqData->status,
                'extra_params' => [
                    'stageId' => $reqData->stageId,
                    'application_id' => isset($reqData->application_id) && !empty($reqData->application_id) ? $reqData->application_id : 0,
                ]
            ]);

            $validation_rules = array(
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required'),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required'),
                array('field' => 'stageId', 'label' => 'stage Id', 'rules' => 'required'),
                array('field' => 'applicant_rule', 'label' => 'addresses', 'rules' => 'callback_check_applicant_required_checks'),
            );

            $this->form_validation->set_data(obj_to_arr($reqData));
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $resStatus = $this->Recruitment_applicant_model->update_applicant_stage_status($reqData, $reqestData->adminId);
                if ($resStatus['status']) {
                    $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
                    $stage_label = $this->username->getName('stage_label', $reqData->stageId);

                    $this->loges->setUserId($reqData->applicant_id);
                    $this->loges->setDescription(json_encode($reqData));

                    $txtData = ['2' => 'In progress', '3' => 'Completed', '4' => 'Unsuccessfull'];
                    $txt = $txtData[$reqData->status] ?? 'Unsuccessfull';
                    $this->loges->setTitle('Applicant - ' . $applicantName . ' ' . $stage_label . ' ' . $txt);  // set title in log
                    $this->loges->setSpecific_title($stage_label . ' ' . $txt);
                    $this->loges->createLog(); // create log

                    $response = $resStatus;
                } else {
                    $response = $resStatus;
                }
                // set log details
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
    }

    function update_applicant_phone_interview_classification()
    {
        $reqestData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqestData->adminId);
        $reqData = $reqestData->data;

        if (!empty($reqData)) {
            $validation_rules = array(
                //array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'callback_check_applicant_phone_interview_already_exist'),
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'application id', 'rules' => 'required'),
                array('field' => 'classificaiton', 'label' => 'phone classificaiton', 'rules' => 'required'),
            );

            $this->form_validation->set_data(obj_to_arr($reqData));
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                // set log details
                $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
                $adminName = $this->username->getName('admin', $reqestData->adminId);

                $res = $this->basic_model->get_row('recruitment_applicant_phone_interview_classification', ['id'], ['applicant_id' => $reqData->applicant_id, 'application_id' => $reqData->application_id, 'archive' => 0]);
                if (!empty($res)) {
                    $this->loges->setTitle('Update phone classification of applicant - ' . $applicantName . ' - application_id - ' . $reqData->application_id);  // set title in log
                    $this->loges->setSpecific_title('Update phone classification by - ' . $adminName);
                    $classification_data = array('classfication' => $reqData->classificaiton, 'updated_by' => $reqestData->adminId);
                    $this->basic_model->update_records('recruitment_applicant_phone_interview_classification', $classification_data, ['id' => $res->id]);
                } else {
                    $this->loges->setTitle('Add phone classification of applicant - ' . $applicantName . ' - application_id - ' . $reqData->application_id);  // set title in log
                    $this->loges->setSpecific_title('Add phone classification by - ' . $adminName);
                    $classification_data = array('applicant_id' => $reqData->applicant_id, 'application_id' => $reqData->application_id, 'classfication' => $reqData->classificaiton, 'created' => DATE_TIME, 'updated_by' => $reqestData->adminId);
                    $this->basic_model->insert_records('recruitment_applicant_phone_interview_classification', $classification_data, FALSE);
                }

                $this->loges->setUserId($reqData->applicant_id);
                $this->loges->setDescription(json_encode($reqData));

                $this->loges->createLog(); // create log

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
    }

    function get_applicant_applicant_option()
    {
        $reqestData = request_handler('access_recruitment');
        $reqData = $reqestData->data;

        if (!empty($reqData)) {

            $data['employmentTypes'] = $this->basic_model->get_record_where('recruitment_job_employment_type', $column = array('title as label', 'id as value'), $where = array('archive' => '0'));

            $data['jobPositions'] = $this->basic_model->get_record_where('recruitment_job_position', $column = array('title as label', 'id as value'), $where = array('archive' => '0'));

            $data['channels'] = $this->basic_model->get_record_where('recruitment_channel', $column = array('channel_name as label', 'id as value'), $where = array('archive' => '0'));

            $data['recruitmentAreas'] = $this->basic_model->get_record_where('recruitment_department', $column = array('name as label', 'id as value'), $where = array('archive' => '0'));

            echo json_encode(['status' => true, 'data' => $data]);
        }
    }

    function update_applied_application_of_application()
    {
        $reqestData = request_handler('access_recruitment_admin');
        $this->loges->setCreatedBy($reqestData->adminId);
        $reqData = $reqestData->data;

        if (!empty($reqData)) {

            $validation_rules = array(
                array('field' => 'applications[]', 'label' => 'applications', 'rules' => 'required'),
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required'),
            );

            $this->form_validation->set_data(obj_to_arr($reqData));
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $this->Recruitment_applicant_model->update_applied_application_of_application($reqData);

                // set log details
                $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
                $adminName = $this->username->getName('admin', $reqestData->adminId);

                $this->loges->setTitle('Update application on applicant: ' . $applicantName);
                $this->loges->setSpecific_title('Update application by ' . $adminName);
                $this->loges->setUserId($reqData->applicant_id);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();

                $response = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
        }
    }

    function get_applicant_logs()
    {
        $reqestData = request_handler('access_recruitment');
        $reqData = $reqestData->data;

        if (!empty($reqData->applicant_id)) {

            $result = $this->Recruitment_applicant_model->get_applicant_logs($reqData);

            echo json_encode($result);
        } else {
            echo json_encode(['status' => false, 'error' => 'Applicant id is required']);
        }
    }

    function get_applicant_stage_wise_details()
    {
        $reqestData = request_handler('access_recruitment');
        $reqData = $reqestData->data;
        #pr($reqData);
        if (!empty($reqData->applicant_id && $reqData->stage_number)) {
            $data = [];

            if ($reqData->stage_number == 1) {

                $data['question_answer'] = $this->Recruitment_applicant_model->get_applicant_job_question_answer($reqData->applicant_id);
            } elseif ($reqData->stage_number == 2) {

                $data['phone_interview_classification'] = $this->Recruitment_applicant_model->get_applicant_phone_interview_classification($reqData->applicant_id, $reqData->application_id);
                $x = $this->Recruitment_applicant_model->get_applicant_form_interview_status($reqData->applicant_id, "phone_interview", $reqData->application_id);
                $data['phont_interview_status'] = $x["interview_status"];
                $data['phont_interview_form_id'] = $x["interview_form_id"];
                $data['phone_interview_applicant_form_id'] = $x["interview_applicant_form_id"];
            } elseif ($reqData->stage_number == 3) {
                $applicant_all_stage = $this->stage_model->get_all_stage_key_for_applicant($reqData->applicant_id, $reqData->application_id);
                $db_stages = [];
                if (!empty($applicant_all_stage))
                    $db_stages = array_column($applicant_all_stage, 'key_name');
                if (!empty($db_stages) && in_array('individual_interview', $db_stages)) {
                    //individual_interview
                    $individual_interview = $this->Recruitment_applicant_model->get_applicant_individual_interview_details($reqData->applicant_id, $reqData->application_id, $reqData->stage_label_id);
                    $data['individual_interview'] = $individual_interview['current_interview'];
                    $data['history_individual_interview'] = $individual_interview['history_interview'];
                } else {
                    // group_interview
                    $group_interview = $this->Recruitment_applicant_model->get_applicant_group_or_cab_interview_details($reqData->applicant_id, $reqData->application_id, 3);
                    $data['group_interview'] = $group_interview['current_interview'];
                    $data['history_group_interview'] = $group_interview['history_interview'];
                }
            } elseif ($reqData->stage_number == 4) {
                $data['applicant_document_cat'] = $this->Recruitment_applicant_model->get_applicant_mandatory_doucment_list($reqData->applicant_id, $reqData->application_id);

                // for pay rates
                $this->load->model('Recruitment_dashboard_model');
                $res = $this->Recruitment_dashboard_model->pay_scale_approval_work_area_options();

                $data['work_area_option'] = array_merge([['value' => '', 'label' => 'Select Work Area']], ($res['work_area']));
                $data['pay_point_option'] = array_merge([['value' => '', 'label' => 'Select Paypoint']], ($res['pay_point']));
                $data['pay_level_option'] = array_merge([['value' => '', 'label' => 'Select Level']], ($res['pay_level']));

                // get applicant pay rates
                $pay_scl = $this->Recruitment_applicant_model->get_applicant_pay_scale_approval_data($reqData->applicant_id);
                $data['pay_scale_details'] = $pay_scl['pay_scale_details'];
                $data['pay_scale_approval'] = $pay_scl['pay_scale_approval'];
            } elseif ($reqData->stage_number == 6) {
                $cab_day = $this->Recruitment_applicant_model->get_applicant_group_or_cab_interview_details($reqData->applicant_id, $reqData->application_id, 6);

                $data['cab_day_interview'] = $cab_day['current_interview'];
                $data['history_cab_day_interview'] = $cab_day['history_interview'];
            }


            echo json_encode(['status' => true, 'data' => $data]);
        } else {
            echo json_encode(['status' => false, 'error' => 'Applicant id is required or stage_number is required']);
        }
    }

    function request_for_pay_scal_approval()
    {
        require_once APPPATH . 'Classes/recruitment/ActionNotification.php';
        $reqestData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqestData->adminId);
        $reqData = $reqestData->data;

        if (!empty($reqData)) {

            $validation_rules = array(
                array('field' => 'pay_scale', 'label' => 'applications', 'rules' => 'callback_check_pay_scal_approval_data[' . json_encode($reqData) . ']'),
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required'),
            );

            $this->form_validation->set_data(obj_to_arr($reqData));
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $this->Recruitment_applicant_model->request_for_pay_scal_approval($reqData, $reqestData->adminId);

                // set log details
                $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
                $adminName = $this->username->getName('admin', $reqestData->adminId);

                $this->loges->setTitle('Recruiter send data for pay scale approval of applicant: ' . $applicantName);
                $this->loges->setSpecific_title($adminName . ' recruiter requested for pay scale approval');
                $this->loges->setUserId($reqData->applicant_id);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();

                // set action notificaiton.
                $objAction = new ActionNotification();
                $objAction->setApplicant_id($reqData->applicant_id);
                $objAction->setRecruiterId($reqestData->adminId);
                $objAction->setAction_type(2);
                $objAction->createAction();

                $response = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
        }
    }

    function check_pay_scal_approval_data($d, $reqData)
    {
        $reqData = json_decode($reqData, true);
        $reqData['pay_scale_approval'] = !empty($reqData['pay_scale_approval']) ? array_filter(array_map('array_filter', $reqData['pay_scale_approval'])) : $reqData['pay_scale_approval'];
        // first check pay scale approval aready submitted
        $res = $this->basic_model->get_row('recruitment_applicant_pay_point_approval', ['id', 'status'], ['applicant_id' => $reqData['applicant_id']]);
        if (!empty($res)) {
            $this->form_validation->set_message('check_pay_scal_approval_data', 'Pay scale data already requested for approval');
            return false;
        }

        // check pay scal approval not empty
        if (!empty($reqData['pay_scale_approval'])) {
            foreach ($reqData['pay_scale_approval'] as $val) {
                if (empty($val['work_area'])) {
                    $this->form_validation->set_message('check_pay_scal_approval_data', 'Please provide work area');
                    return false;
                } elseif (empty($val['pay_point'])) {
                    $this->form_validation->set_message('check_pay_scal_approval_data', 'Please provide pay point');
                    return false;
                } elseif (empty($val['pay_level'])) {
                    $this->form_validation->set_message('check_pay_scal_approval_data', 'Please provide pay level');
                    return false;
                }
            }

            // get all work area for check unique
            $work_areas = array_column($reqData['pay_scale_approval'], 'work_area');
            $uniqe_work_areas = array_unique($work_areas);

            // check existing work area or unique work area array is equal or not
            if (count($work_areas) != count($uniqe_work_areas)) {
                $this->form_validation->set_message('check_pay_scal_approval_data', 'Please select unique work area');
                return false;
            }
        } else {
            $this->form_validation->set_message('check_pay_scal_approval_data', 'pay scal approval data is required');
            return false;
        }
    }

    function update_applicant_reference_status_note()
    {
        require_once APPPATH . 'Classes/recruitment/ActionNotification.php';
        $reqestData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqestData->adminId);
        $reqData = $reqestData->data;

        if (!empty($reqData)) {

            $validation_rules = array(
                array('field' => 'reference_id', 'label' => 'reference id', 'rules' => 'required'),
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required'),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required'),
            );

            $this->form_validation->set_data(obj_to_arr($reqData));
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $this->Recruitment_applicant_model->update_applicant_reference_status_note($reqData);

                // set log details
                $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
                $adminName = $this->username->getName('admin', $reqestData->adminId);

                $this->loges->setTitle('Update applicant reference of applicant: ' . $applicantName);
                $this->loges->setSpecific_title('Reference updated by admin ' . $adminName);
                $this->loges->setUserId($reqData->applicant_id);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();

                $response = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
        }
    }

    function update_applicant_details()
    {
        require_once APPPATH . 'Classes/recruitment/ActionNotification.php';
        $reqestData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqestData->adminId);
        $reqData = $reqestData->data;

        if (!empty($reqData)) {
            $reqData->applicant_rule = json_encode(['type_of_checks' => ['not_flagged', 'not_flagged_pending', 'not_duplicate', 'not_duplicate_pending'], 'applicant_id' => $reqData->id, 'extra_params' => []]);

            $validation_rules = array(
                array('field' => 'id', 'label' => 'applicant id', 'rules' => 'required'),
                array('field' => 'firstname', 'label' => 'firstname', 'rules' => 'required|max_length[30]'),
                array('field' => 'lastname', 'label' => 'lastname', 'rules' => 'required|max_length[30]'),
                array('field' => 'phones[]', 'label' => 'phone', 'rules' => 'callback_phone_number_check[phone,required,Please enter valid mobile number.]|callback_check_phone_already_exist_to_another_applicant[' . $reqData->id . ']'),
                array('field' => 'emails[]', 'label' => 'email', 'rules' => 'callback_check_valid_email_address[email]|callback_check_email_already_exist_to_another_applicant[' . $reqData->id . ']'),
                // array('field' => 'references[]', 'label' => 'references', 'rules' => 'callback_check_recruitment_applicant_references'),
                array('field' => 'address', 'label' => 'addresses', 'rules' => 'callback_check_string_google_address_is_valid'),
                array('field' => 'applicant_rule', 'label' => 'addresses', 'rules' => 'callback_check_applicant_required_checks'),
                array('field' => 'ex', 'label' => 'addresses', 'rules' => 'callback_check_applicant_duplicate_profile[' . $reqData->id . ']'),
                array(
                    'field' => 'dob', 'label' => 'date of birth', 'rules' => 'valid_date_format[Y-m-d]|before_or_equal[' . date(DB_DATE_FORMAT) . ']',
                    'errors' => [
                        'valid_date_format' => 'Incorrect date format',
                        'before_or_equal' => 'Date of birth must come before today',
                    ]
                ),
                array('field' => 'preferred_name', 'label' => 'preferred name', 'rules' => 'max_length[255]'), // because the column schema says 255
                array('field' => 'previous_name', 'label' => 'previous name', 'rules' => 'max_length[255]'), // because the column schema says 255
            );

            // validation rules for title
            // are you submitting 0 for the title? Dont do it!
            if (isset($data->title) && !empty($data->title)) {
                $title_ids = $this->get_title_ids();
                $validation_rules[] = [
                    'field' => 'title',
                    'label' => 'title',
                    'rules' => [
                        'trim',
                        'in_list[' . implode(',', $title_ids) . ']',
                    ],
                    'errors' => [
                        'in_list' => "The {label} you submitted does not exist anymore or marked as archived.\nPlease refresh the page"
                    ],
                ];
            }

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $this->Recruitment_applicant_model->update_applicant_details($reqData);

                // set log details
                $applicantName = $this->username->getName('applicant', $reqData->id);
                $adminName = $this->username->getName('admin', $reqestData->adminId);

                $this->loges->setTitle('Applicant updated - : ' . $applicantName);
                $this->loges->setSpecific_title('Applicant updated by ' . $adminName);
                $this->loges->setUserId($reqData->id);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();

                $response = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
        }
    }

    /*
     * function upload_attachment_docs
     * return type json
     */

    public function upload_attachment_docs()
    {
        // handle request and check permission to upload applicant docs
        $data = request_handlerFile('access_recruitment');

        // include participant docs class
        require_once APPPATH . 'Classes/recruitment/ApplicantDocs.php';
        require_once APPPATH . 'Classes/document/DocumentAttachment.php';
        require_once APPPATH . 'Classes/recruitment/ActionNotification.php';
        require_once APPPATH . 'Classes/common/Aws_file_upload.php';
        $docAttachObj = new DocumentAttachment();

        $awsFileupload = new Aws_file_upload();
        // set requested data for validate
        $this->form_validation->set_data((array) $data);

        // make validation rule
        $validation_rules = array(
            array('field' => 'docsTitle', 'label' => 'Title', 'rules' => 'required'),
            array('field' => 'applicantId', 'label' => 'Applicant id', 'rules' => 'required'),
            array('field' => 'currentStage', 'label' => 'Stage', 'rules' => 'required'),
            array('field' => 'docsCategory', 'label' => 'Docs Category', 'rules' => 'required'),
            array('field' => 'stageMain', 'label' => 'Stage', 'rules' => 'required'),
            array('field' => 'docStatus', 'label' => 'Document status', 'rules' => 'required'),
            array('field' => 'docsFile', 'label' => 'Document status', 'rules' => 'callback_aws_file_type_check'),

            // trim is a little trick to mark fields as optional and make it avail to next validation rules if provided
            // `before_or_equal` will check both the date and time.
            // If you are validating only against dates, make sure the client side submits times as 00:00:00 (eg. 31-12-2021 00:00:00)
            array('field' => 'docIssueDate', 'label' => 'issue date', 'rules' => 'trim|valid_date_format|before_or_equal[' . date(DB_DATE_FORMAT) . ']'),
        );
        if (empty($_FILES['docsFile']['name'])) {
            $validation_rules[] = array('field' => 'docsFile', 'label' => 'Document File', 'rules' => 'required');
        }

        if (!empty($data->doc_name)  && $data->doc_name==VISA_DETAILS) {
            $validation_rules[] = array('field' => 'visa_category', 'label' => 'Visa Category', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Category id" ]);
            $validation_rules[] = array('field' => 'visa_category_type', 'label' => 'Visa type', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Type id" ]);
        }

        // set rules form validation
        $this->form_validation->set_rules($validation_rules);

        // check requested data return true or false
        if ($this->form_validation->run()) {
            if (!empty($_FILES) && $_FILES['docsFile']['error'] == 0) {

                $docAttachObj->setApplicantId($data->applicantId);
                $docAttachObj->setDocTypeId($data->docsCategory);
                $docAttachObj->setArchive(0);
                $docAttachObj->setStage($data->currentStage);
                $docAttachObj->setIsMainStage(($data->stageMain == 'true' ? 1 : 0));
                $docAttachObj->setCreatedAt(DATE_TIME);
                $docAttachObj->setCreatedBy($data->adminId);
                $docAttachObj->setEntityId($data->applicantId);
                $docAttachObj->setReferenceNumber($data->reference_number);
                $docAttachObj->setVisaCategory($data->visa_category ?? NULL);
                $docAttachObj->setVisaCategoryType($data->visa_category_type ?? NULL);
                // Get constant staus
                $docAttachObj->setDocumentStatus($data->docStatus);
                // Get constant entity type
                $entityType = $docAttachObj->getConstant('ENTITY_TYPE_APPLICANT');
                $docAttachObj->setEntityType($entityType);
                // Get constant related to
                $relatedTo = $docAttachObj->getConstant('RELATED_TO_RECRUITMENT');
                $docAttachObj->setRelatedTo($relatedTo);
                // Get constant Created Portal
                $createdPortal = $docAttachObj->getConstant('CREATED_PORTAL_HCM');
                $docAttachObj->setCreatedPortal($createdPortal);

                if (property_exists($data, 'application_id') && !empty($data->application_id)) {
                    $docAttachObj->setApplicationId($data->application_id);
                }

                if (property_exists($data, 'docExpiryDate')) {
                    $docAttachObj->setExpiryDate(empty(trim($data->docExpiryDate)) ? null : $data->docExpiryDate);
                }

                if (property_exists($data, 'docIssueDate')) {
                    $docAttachObj->setIssueDate(empty(trim($data->docIssueDate)) ? null : $data->docIssueDate);
                }
                $docAttachObj->setLicenseType(trim($data->license_type)?? null);
                $docAttachObj->setIssuingState(trim($data->issuing_state)?? null);
                if ($data->license_type !== "3") {
                    $data->vic_conversion_date = null;
                }
                $docAttachObj->setVicConversionDate(trim($data->vic_conversion_date)?? null);
                // check its duplicate
                $docAttachDubObj = new DocumentAttachment();
                $docAttachDubObj->setRawName($_FILES['docsFile']['name']);
                $docAttachDubObj->setApplicantId($data->applicantId);
                $docAttachDubObj->setArchive(0);
                $dub_result = $docAttachDubObj->checkDublicateDocAttachment();

                $absoluteUploadPath = realpath(adminPortalPath() . ltrim(APPLICANT_ATTACHMENT_UPLOAD_PATH, './')) . DIRECTORY_SEPARATOR; // user here constact for specific path

                $config['file_name'] = $_FILES['docsFile']['name'];
                $config['input_name'] = 'docsFile';
                $config['remove_spaces'] = false;
                $config['directory_name'] = $data->applicantId;
                $config['allowed_types'] = DEFAULT_ATTACHMENT_UPLOAD_TYPE; //'jpg|jpeg|png|xlx|xls|doc|docx|pdf|pages';
                $config['max_size'] = DEFAULT_MAX_UPLOAD_SIZE;
                $config['module_id'] = REQUIRMENT_MODULE_ID;
                $config['title'] = "File Transfer Initiated for Add Attachment against Applicant - $data->applicantId";
                $config['created_by'] = $data->applicantId;

                //Upload file in App server if Appser upload enabled
                if(getenv('IS_APPSERVER_UPLOAD') == 'yes') {
                    $config['upload_path'] = $absoluteUploadPath;
                    do_upload($config); // upload file in local
                }

                $config['upload_path'] = S3_APPLICANT_ATTACHMENT_UPLOAD_PATH;

                // upload attachment to S3
                $s3documentAttachment = $awsFileupload->s3_common_upload_single_attachment($config, FALSE);
                // check here file is uploaded or not return key error true
                if (!isset($s3documentAttachment) || !$s3documentAttachment['aws_uploaded_flag']) {
                    // return error comes in file uploading
                    echo json_encode(array('status' => false, 'error' => 'Document Attachment is not created. something went wrong'));
                    exit();
                } else {
                    $documentId = $docAttachObj->createDocumentAttachment();

                    if ($documentId != '') {
                        $docAttachPropertyObj = new DocumentAttachment();
                        $docAttachPropertyObj->setDocId($documentId);
                        $docAttachPropertyObj->setFilePath($s3documentAttachment['file_path']);
                        $docAttachPropertyObj->setFileType($s3documentAttachment['file_type']);
                        $docAttachPropertyObj->setFileExt($s3documentAttachment['file_ext']);
                        $docAttachPropertyObj->setFileSize($s3documentAttachment['file_size']);
                        $docAttachPropertyObj->setAwsResponse($s3documentAttachment['aws_response']);
                        $docAttachPropertyObj->setAwsObjectUri($s3documentAttachment['aws_object_uri']);
                        $docAttachPropertyObj->setAwsFileVersionId($s3documentAttachment['aws_file_version_id']);
                        $docAttachPropertyObj->setFileName($s3documentAttachment['file_name']);
                        $docAttachPropertyObj->setRawName($_FILES['docsFile']['name']);
                        $docAttachPropertyObj->setAwsUploadedFlag($s3documentAttachment['aws_uploaded_flag']);
                        $docAttachPropertyObj->setArchive(0);
                        $docAttachPropertyObj->setCreatedAt(DATE_TIME);
                        $docAttachPropertyObj->setCreatedBy($data->adminId);

                        $docAttachPropertyObj->createDocumentAttachmentProperty();
                    }

                    $this->loges->setCreatedBy($data->adminId);
                    $applicantName = $this->username->getName('applicant', $data->applicantId);
                    $adminName = $this->username->getName('admin', $data->adminId);
                    $this->loges->setTitle('Applicant attachment added - : ' . $applicantName);
                    $this->loges->setSpecific_title('Applicant attachment added by ' . $adminName);
                    $this->loges->setUserId($data->applicantId);
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->createLog();

                    // check here file is uploaded or not return key error true
                    if (!$dub_result['status']) {
                        $return = array('status' => true, 'warn' => $dub_result['warn']);
                    } else {
                        $return = array('status' => true);
                    }
                }
            } else {
                $return = array('status' => false, 'error' => 'Please select a file to upload');
            }
        } else {
            // return error else data data not valid
            $errors = $this->form_validation->error_array();
            $return = array('status' => false, 'error' => implode(', ', $errors));
        }
        echo json_encode($return);
        exit();
    }

    /*
     * Custom validation function for checking file type
     *
     *  @param $str {string} uploded file value
     */
    public function aws_file_type_check($str){
        $allowed_mime_type_arr = explode('|' , DEFAULT_ATTACHMENT_UPLOAD_TYPE);

        if(isset($_FILES['docsFile']['name']) && $_FILES['docsFile']['name']!="") {

            $file_type = pathinfo($_FILES['docsFile']['name'] , PATHINFO_EXTENSION);

            if(!in_array($file_type, $allowed_mime_type_arr)) {

                $this->form_validation->set_message('aws_file_type_check',
                  'The filetype you are attempting to upload is not allowed.');

                return FALSE;
            }
        }
    }

    /**
     * Action to update attachment information
     *
     * @todo: Currently updating files aren't supported
     *
     * @param int $id
     */
    public function update_attachment_docs($id)
    {
        // handle request and check permission to upload applicant docs
        $data = $api_response = request_handlerFile('access_recruitment');
        // check attachment id exists
        $existingAttachment = $this->db->get_where('tbl_document_attachment', ['id' => $id])->row_array();
        if (empty($existingAttachment)) {
            return show_404("Attachment with ID of $id does not exists anymore");
        }

        $data = json_decode(json_encode($data), true);
        $this->form_validation->set_data($data);

        $rules = [
            ['field' => 'docsTitle', 'label' => 'Title', 'rules' => 'required'],
            ['field' => 'applicantId', 'label' => 'Applicant id', 'rules' => 'required'],
            ['field' => 'currentStage', 'label' => 'Stage', 'rules' => 'required'],
            ['field' => 'docsCategory', 'label' => 'Docs Category', 'rules' => 'required'],
            ['field' => 'stageMain', 'label' => 'Stage', 'rules' => 'required'],
            ['field' => 'docStatus', 'label' => 'Document status', 'rules' => 'required'],
            ['field' => 'docIssueDate', 'label' => 'issue date', 'rules' => 'trim|valid_date_format|before_or_equal[' . date(DB_DATE_FORMAT) . ']']
        ];

        if (!empty($data['doc_name']) && $data['doc_name']==VISA_DETAILS) {
            $validation_rules[] = array('field' => 'visa_category', 'label' => 'Visa Category', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Category id" ]);
            $validation_rules[] = array('field' => 'visa_category_type', 'label' => 'Visa type', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Type id" ]);
        }


        $this->form_validation->set_rules($rules);

        // display validation errors
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return $this->output->set_content_type('json')->set_output(json_encode([
                'status' => false,
                'error' => implode(', ', $errors)
            ]));
        }

        $expiry_date = !empty($data['docExpiryDate']) ? $data['docExpiryDate'] : null;
        if (!is_null($expiry_date) && trim(strtolower($expiry_date)) === 'invalid date') {
            $expiry_date = null;
        }

        $issue_date = !empty($data['docIssueDate']) ? $data['docIssueDate'] : null;
        if (!is_null($issue_date) && trim(strtolower($issue_date)) === 'invalid date') {
            $issue_date = null;
        }

        require_once APPPATH . 'Classes/document/DocumentAttachment.php';

        $docAttachObj = new DocumentAttachment();

        $docAttachObj->setDocTypeId($data['docsCategory']);
        $docAttachObj->setDocumentStatus($data['docStatus']);
        $docAttachObj->setReferenceNumber(!empty($data['reference_number']) ? $data['reference_number'] : null);
        $docAttachObj->setExpiryDate($expiry_date);
        $docAttachObj->setIssueDate($issue_date);
        $docAttachObj->setUpdatedAt(DATE_TIME);
        $docAttachObj->setUpdatedBy($api_response->adminId);
        $docAttachObj->setUpdatedByType(2);
        $docAttachObj->setDocId($id);
        $docAttachObj->setLicenseType(trim($data['license_type'])?? null);
        $docAttachObj->setIssuingState(trim($data['issuing_state'])?? null);
        if ($data['license_type'] !== "3") {
            $data['vic_conversion_date'] = null;
        }
        $docAttachObj->setVicConversionDate(trim($data['vic_conversion_date'])?? null);
        $docAttachObj->setApplicantSpecific(trim($data['applicant_specific'])?? null);
        $docAttachObj->updateDocumentAttachment();

        $applicantName = $this->username->getName('applicant', $api_response->applicantId);
        $adminName = $this->username->getName('admin', $api_response->adminId);
        $this->loges->setCreatedBy($api_response->adminId);
        $this->loges->setTitle('Applicant attachment added - : ' . $applicantName);
        $this->loges->setSpecific_title('Applicant attachment Updated by ' . $adminName);
        $this->loges->setUserId($api_response->adminId);
        $this->loges->setDescription(json_encode($api_response));
        $this->loges->createLog();

        return $this->output->set_content_type('json')->set_output(json_encode([
            'status' => true,
            'msg' => 'Successfully updated document'
        ]));
    }

    public function get_attachment_category_details()
    {
        $reqData = request_handler('access_recruitment');
        /*if (!empty($reqData->adminId)) {
            $data = $reqData->data;

            $hasJobId = property_exists($data, 'jobId') && is_numeric($data->jobId);

            // job id may be passed optionally to retrieve certain doc categories only
            if ($hasJobId) {
                $res = $this->Recruitment_applicant_model->find_all_doctypes_needed_by_job_id($data->jobId);
                $res = array_map(function($r) {
                    return json_decode(json_encode([
                        'value' => $r['id'],
                        'label' => $r['title'],
                    ]));
                }, $res);
            }
            // retrieve all category types
            else {
                $res = $this->basic_model->get_record_where('recruitment_job_requirement_docs', ['id as value', 'title as label'], ['archive' => 0]);
            }

            $codes = $this->document_codes();
            foreach ($res as $i => $catObj) {
                $cat = (array) $catObj;
                $catId = intval($cat['value']);
                if (array_key_exists($catId, $codes)) {
                    $res[$i]->code = $codes[$catId];
                }
            }
        } else {
            $res = [];
        }*/

        //Get document type details from document type table
        $data['doc_category'] = ['apply_job', 'recruitment_stage'];
        $data['col1'] = 'label';
        $data['col2'] = 'value';
        $data['module_id'] = REQUIRMENT_MODULE_ID;

        $new_req = $this->Document_type_model->get_document_type($data , TRUE);

        echo json_encode(['status' => true, 'data' => $new_req]);
        exit();
    }

    public function get_all_attachments_by_applicant_id()
    {
        $reqestData = request_handler('access_recruitment');
        if (!empty($reqestData->data)) {
            $applicantId = $reqestData->data->id;
            $res = $this->Recruitment_applicant_model->get_all_attachments_by_applicant_id($applicantId);
        } else {
            $res = [];
        }
        echo json_encode(['status' => true, 'data' => $res]);
        exit();
    }

    public function download_selected_file()
    {
        $request = request_handler('access_recruitment');
        $responseAry = $request->data;
        if (!empty($responseAry)) {
            $this->load->library('zip');

            $applicantId = $responseAry->applicantId;
            $download_data_id = (array) $responseAry->downloadData;
            $download_data = $this->Recruitment_applicant_model->get_attachment_details_by_ids(array_keys($download_data_id), $applicantId);
            $this->zip->clear_data();

            $x = '';
            $file_count = 0;
            if (!empty($download_data)) {
                $zip_name = time() . '_' . $applicantId . '.zip';
                foreach ($download_data as $file) {
                    if ($file->draft_contract_type == 1) {
                        $file_path = GROUP_INTERVIEW_CONTRACT_PATH . $file->filename;
                    } else if ($file->draft_contract_type == 2) {
                        $file_path = CABDAY_INTERVIEW_CONTRACT_PATH . $file->filename;
                    } else {
                        $file_path = APPLICANT_ATTACHMENT_UPLOAD_PATH . $applicantId . '/' . $file->filename;
                    }
                    $this->zip->read_file($file_path, FALSE);
                    $file_count = 1;
                }
                $x = $this->zip->archive('archieve/' . $zip_name);
            }


            if ($x && $file_count == 1) {
                echo json_encode(array('status' => true, 'zip_name' => $zip_name));
                exit();
            } else {
                echo json_encode(array('status' => false, 'error' => 'Please select atleast one file to continue.'));
                exit();
            }
        }
    }

    public function archive_selected_file()
    {
        $request = request_handler('access_recruitment');
        $responseAry = $request->data;
        if (!empty($responseAry)) {
            $applicantId = $responseAry->applicantId;
            $archived_data_id = array_keys((array) $responseAry->archiveData);

            require_once APPPATH . 'Classes/document/DocumentAttachment.php';

            foreach ($archived_data_id as $doc_id) {
                $docAttachObj = new DocumentAttachment();
                $entity_type = $docAttachObj->getConstant('ENTITY_TYPE_MEMBER');

                // Call archive document model
                $this->load->model('document/Document_attachment_model');
                $result = $this->Document_attachment_model->archive_document_attachment_v1($request->adminId, $doc_id, $entity_type);

            }

            $applicantName = $this->username->getName('applicant', $applicantId);
            $adminName = $this->username->getName('admin', $request->adminId);
            $this->loges->setCreatedBy($request->adminId);
            $this->loges->setTitle('Applicant attachment removed - : ' . $applicantName);
            $this->loges->setSpecific_title('Applicant attachment Updated by ' . $adminName);
            $this->loges->setUserId($request->adminId);
            $this->loges->setDescription(json_encode($request));
            $this->loges->createLog();

            if ($result) {
                echo json_encode(array('status' => true, 'msg' => 'Selected attachment has been successfully archived.'));
                exit();
            } else {
                echo json_encode(array('status' => false, 'error' => 'Please select atleast one file to continue.'));
                exit();
            }
        }
    }

    public function approve_deny_applicant_doc_category()
    {
        $request = request_handler('access_recruitment');
        $this->loges->setCreatedBy($request->adminId);
        $reqData = $request->data;

        if (!empty($reqData)) {

            $validation_rules = array(
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'application id', 'rules' => 'required'),
                array('field' => 'recruitment_doc_id', 'label' => 'recruitment doc catogory Id', 'rules' => 'required'),
                array('field' => 'action', 'label' => 'action', 'rules' => 'required'),
                array('field' => 'doc_cat', 'label' => 'doc cat', 'rules' => 'required'),
            );
            if (isset($reqData->detailId)) {
                $reqData->applicant_rule = json_encode([
                    'type_of_checks' => ['cab_day_interview_permission_edit'],
                    'applicant_id' => $reqData->applicant_id,
                    'extra_params' => ['taskIdOrCabdayDetailId' => $reqData->detailId, 'adminId' => $request->adminId, 'type' => 'detailId']
                ]);
                $validation_rules[] = array('field' => 'applicant_rule', 'label' => 'addresses', 'rules' => 'callback_check_applicant_required_checks');
            }

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $action = $reqData->action == 2 ? 2 : 1;

                $this->db->select('id')->from('tbl_recruitment_applicant_doc_category')->where(array('application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id, 'recruitment_doc_id' => $reqData->recruitment_doc_id, 'archive' => 0));
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $update_data = ['is_approved' => $action];
                    $this->basic_model->update_records('recruitment_applicant_doc_category', $update_data, ['application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id, 'recruitment_doc_id' => $reqData->recruitment_doc_id, 'archive' => 0]);
                } else {
                    $update_data = ['is_approved' => $action, 'application_id' => $reqData->application_id, 'applicant_id' => $reqData->applicant_id, 'recruitment_doc_id' => $reqData->recruitment_doc_id, 'created' => DATE_TIME, 'is_required' => (isset($reqData->is_required) ? $reqData->is_required : 0)];
                    $this->basic_model->insert_records('recruitment_applicant_doc_category', $update_data);
                }

                if (isset($reqData->attachment_id) && !empty($reqData->attachment_id)) {
                    $update_data_attachment = ['document_status' => $action];
                    if ($action == 2) {
                        $update_data_attachment['archive'] = 1;
                    }
                    $this->basic_model->update_records('recruitment_applicant_stage_attachment', $update_data_attachment, ['applicant_id' => $reqData->applicant_id, 'uploaded_by_applicant' => 1, 'id' => $reqData->attachment_id]);
                }

                // set log details
                $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
                $adminName = $this->username->getName('admin', $request->adminId);


                $txt = $reqData->doc_cat . " document is " . (($action == 1) ? "approved" : "Rejected");
                $this->loges->setTitle($applicantName . ' - ' . 'Applicant ' . $txt . 'document');
                $this->loges->setSpecific_title($txt . ' updated by ' . $adminName);
                $this->loges->setUserId($reqData->applicant_id);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();

                $response = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
        }
    }

    public function update_mandatory_optional_docs_for_applicant()
    {
        $request = request_handler('access_recruitment');
        $this->loges->setCreatedBy($request->adminId);
        $reqData = $request->data;

        if (!empty($reqData)) {
            $applicant_document_cat = isset($reqData->applicant_document_cat) ? json_encode($reqData->applicant_document_cat) : [];
            $validation_rules = array(
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'application id', 'rules' => 'required'),
                array('field' => 'applicant_document', 'label' => 'applicant_document_cat', 'rules' => 'callback_check_applicant_assign_document_category[' . $applicant_document_cat . ']'),
            );

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $this->Recruitment_applicant_model->update_mandatory_optional_docs_for_applicant($reqData);

                // set log details
                $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
                $adminName = $this->username->getName('admin', $request->adminId);



                $this->loges->setTitle('Updated Mandatory document category list of applicant - ' . $applicantName . ' application id: ' . $reqData->application_id);
                $this->loges->setSpecific_title('Updated Mandatory document category list by ' . $adminName);
                $this->loges->setUserId($reqData->applicant_id);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();

                $response = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
        }
    }

    function save_application_stage_note()
    {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($reqData->adminId);
        $reqData = $reqData->data;
        $reqData->recruiterId = $adminId;

        if (!empty($reqData)) {
            $validation_rules = array(
                array('field' => 'is_main_stage', 'label' => 'Stage', 'rules' => 'required'),
                array('field' => 'current_stage', 'label' => 'Stage', 'rules' => 'required'),
                array('field' => 'stage', 'label' => 'Stage', 'rules' => 'required'),
                array('field' => 'note', 'label' => 'Note', 'rules' => 'required'),
                array('field' => 'applicantId', 'label' => 'Applicant', 'rules' => 'required')
            );
            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run() == TRUE) {
                $return = $this->Recruitment_applicant_model->save_application_stage_note($reqData);
                if (isset($return['status']) && $return['status']) {

                    // set log details
                    $applicantName = $this->username->getName('applicant', $reqData->applicantId);
                    $adminName = $this->username->getName('admin', $adminId);

                    $this->loges->setUserId($reqData->applicantId);
                    $this->loges->setDescription(json_encode($reqData));
                    $this->loges->setTitle('Applicant - ' . $applicantName . ' stage-' . $reqData->stage . ' note added on applicant info screen');
                    $this->loges->setSpecific_title('Stage-' . $reqData->stage . ' note added on applicant info by ' . $adminName);
                    $this->loges->createLog();
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

    public function get_attachment_stage_notes_by_applicant_id()
    {
        $reqestData = request_handler('access_recruitment');
        if (!empty($reqestData->data)) {
            $applicantId = $reqestData->data->id;

            $application_id = 0;
            if (isset($reqestData->data->application_id) && !empty($reqestData->data->application_id)) {
                $application_id = 0;
            }

            $res = $this->Recruitment_applicant_model->get_attachment_stage_notes_by_applicant_id($applicantId, $application_id);
        } else {
            $res = [];
        }
        echo json_encode(['status' => true, 'data' => $res]);
        exit();
    }

    public function get_lastupdate_by_applicant_id()
    {
        $reqestData = request_handler('access_recruitment');
        if (!empty($reqestData->data)) {
            $applicantId = $reqestData->data->id;
            $res = $this->Recruitment_applicant_model->get_applicant_last_update($applicantId);
        } else {
            $res = [];
        }
        echo json_encode(['status' => true, 'data' => $res]);
        exit();
    }

    /**
     * when employment contract sending is requested from the applicant info
     * using the existing modal functionality by passing the applicant_id and application_id
     * without task_applicant_id to generate the PDF and send the DocuSign envelop
     */
    function generate_employment_contract() {
        $reqestData = request_handler('access_recruitment');

        if (!empty($reqestData->data)) {
            $reqData = $reqestData->data;
            $this->load->model(['Recruitment_group_interview_model', 'Recruitment_applicant_docusign_model']);
            $filename = $reqData->applicant_id."_".$reqData->application_id."_unsigned.pdf";

            $resDocusign = $this->Recruitment_applicant_docusign_model->generate_docusign_contract($reqData->applicant_id, 0, ['type' => 'cabday_interview', 'file_name' => $filename, 'adminId' => $reqestData->adminId], $reqData->application_id, $reqData);

            if($resDocusign) {
                echo json_encode(['status' => true, 'msg' => "Successfully sent the employment contract"]);
            }
            else {
                echo json_encode(['status' => false, 'error' => "Error sending the employment contract"]);
            }
            exit();
        }
    }

    function docusign_employment_contract() {
        $this->load->model(['Recruitment_group_interview_model', 'Recruitment_applicant_docusign_model']);

        $reqData = $this->input->post('reqData');
        $reqestData = $this->input->post('reqestData');
        $index = 0;
        foreach($reqestData['data']['applications'] as $val) {
        $filename = $val['applicant_id']."_".$val['application_id']."_unsigned.pdf";
        $resDocusign = $this->Recruitment_applicant_docusign_model->generate_docusign_contract($val['applicant_id'], 0, ['type' => 'cabday_interview', 'file_name' => $filename, 'adminId' => $reqestData['adminId']], $val['application_id'], $reqData);

           if($resDocusign) {
                //Adding Notification
                $notification_data['title'] = 'Employment contract';
                $notification_data['shortdescription'] = 'Employment Contract: Please check the email sent to your mailbox';
                $notification_data['userId'] = $val['applicant_id'];
                $notification_data['user_type'] = 5;
                $notification_data['status'] = 0;
                $notification_data['sender_type'] = 2;
                $notification_data['created'] = DATE_TIME;
                $notification_data['specific_admin_user'] = $reqestData['adminId'];
                $notification_data['entity_type'] = 9;
                $notification_data['entity_id'] = $val['application_id'];

                $this->Notification_model->create_notification($notification_data);
            }
            $index ++;

            }
            exit();

    }

    /**
     * when employment contract sending is requested from the applicant info
     * using the existing modal functionality by passing the applicant_id and application_id
     * without task_applicant_id to generate the PDF and send the DocuSign envelop for bulk applications
     */
    function generate_bulk_employment_contract() {
        error_reporting(1);
        $this->load->library('session');
        $reqestData = request_handler('access_recruitment');

        if (!empty($reqestData->data)) {
            error_reporting(1);
            $reqData = $reqestData->data;
            $this->load->model(['Recruitment_group_interview_model', 'Recruitment_applicant_docusign_model']);
            foreach($reqData->applications as $val) {
                $filename = $val->applicant_id."_".$val->applicant_id."_unsigned.pdf";

                $url = base_url()."recruitment/RecruitmentApplicant/docusign_employment_contract";
                $param = array('reqData' => $reqData,'reqestData' => $reqestData );
                 $this->asynclibrary->do_in_background($url, $param);
                 $resDocusign = true;
                if($resDocusign) {
                    //Adding Notification
                    $notification_data['title'] = 'Employment contract';
                    $notification_data['shortdescription'] = 'Employment Contract: Please check the email sent to your mailbox';
                    $notification_data['userId'] = $val->applicant_id;
                    $notification_data['user_type'] = 5;
                    $notification_data['status'] = 0;
                    $notification_data['sender_type'] = 2;
                    $notification_data['created'] = DATE_TIME;
                    $notification_data['specific_admin_user'] = $reqestData->adminId;
                    $notification_data['entity_type'] = 9;
                    $notification_data['entity_id'] = $val->application_id;

                    $this->Notification_model->create_notification($notification_data);
                }
            }


            if($resDocusign) {
                echo json_encode(['status' => true, 'msg' => "Successfully sent the employment contract"]);
            }
            else {
                echo json_encode(['status' => false, 'error' => "Error sending the employment contract"]);
            }
            exit();
        }
    }

    public function resend_applicant_docusign_contract()
    {
        $reqestData = request_handler('access_recruitment');

        if (!empty($reqestData->data)) {
            $reqData = $reqestData->data;

            $validation_rules = array(
                array('field' => 'task_applicant_id', 'label' => 'task applicant id', 'rules' => 'required'),
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required')
            );
            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run() == TRUE) {

                $res_send_contract = $this->resend_applicant_docusign_user_contract($reqData);
                if ($res_send_contract['status']) {
                    $request_params['to_email'] = $res_send_contract['data']['email'];
                    $request_params['subject'] = $res_send_contract['data']['applicant_name'] . ' Applicant cabday contract document send successfully';
                    $request_params['body'] = $res_send_contract['data']['applicant_name'] . ' Applicant cabday contract document send successfully';
                    $request_params['fullname'] = $res_send_contract['data']['applicant_name'];
                    $request_params['filename'] = $res_send_contract['data']['unsigned_file'] ?? '';

                    require_once APPPATH . 'Classes/CommunicationLog.php';
                    $logObj = new CommunicationLog();

                    $email_log_data[] = [
                        'from' => 'OnCall',
                        'communication_text' => $request_params['body'],
                        'userId' => $reqData->applicant_id,
                        'send_by' => $reqestData->adminId,
                        'log_type' => 2,
                        'created' => DATE_TIME,
                        'title' => 'CAB day interview DocuSign document re-send',
                        'user_type' => 1
                    ];

                    $logObj->createMuitipleCommunicationLog($email_log_data);

                    // send_msg_mail
                    echo json_encode(['status' => true, 'msg' => 'Applicant contract docusign sent successfully.']);
                    exit();
                } else {
                    echo json_encode($res_send_contract);
                    exit();
                }
            } else {
                $errors = $this->form_validation->error_array();
                echo json_encode(['status' => false, 'error' => implode(', ', $errors)]);
                exit();
            }
        } else {
            echo json_encode(['status' => false, 'error' => 'Something went wrong.']);
            exit();
        }
    }

    function send_reminder_sms_for_docusign()
    {
        $reqestData = request_handler('access_recruitment');

        if (!empty($reqestData->data)) {
            $reqData = $reqestData->data;

            $validation_rules = array(
                array('field' => 'task_applicant_id', 'label' => 'task applicant id', 'rules' => 'required'),
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required')
            );
            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run() == TRUE) {

                $res_send_contract = $this->resend_applicant_docusign_user_contract($reqData);
                if ($res_send_contract['status']) {

                    $request_params['to_email'] = $res_send_contract['data']['email'];
                    $request_params['subject'] = 'HCM - Contract Reminder';
                    $request_params['body'] = $res_send_contract['data']['applicant_name'] . ' Please check your email, contract has been sent';
                    $request_params['fullname'] = $res_send_contract['data']['applicant_name'];
                    $request_params['filename'] = $res_send_contract['data']['unsigned_file'] ?? '';
                    $request_params['number'] = $res_send_contract['data']['phone'] ?? '';

                    // send_msg_mail
                    $this->load->library('Sms');
                    $msg_response = $this->sms->send_msg_mail($request_params);
                    require_once APPPATH . 'Classes/CommunicationLog.php';
                    $logObj = new CommunicationLog();

                    // only for log purpose
                    $sms_log_data[] = [
                        'title' => 'HCM - Contract Reminder',
                        'communication_text' => $request_params['body'],
                        'userId' => $reqData->applicant_id,
                        'user_type' => 1,
                        'send_by' => $reqestData->adminId,
                        'log_type' => 1,
                        'created' => DATE_TIME,
                        'from' => APPLICATION_NAME
                    ];

                    $logObj->createMuitipleCommunicationLog($sms_log_data);

                    if ($msg_response) {
                        echo json_encode(['status' => true, 'msg' => 'Reminder to sign Applicant contract sent successfully.']);
                        exit();
                    } else {
                        // sms funcnality close send only mail
                        echo json_encode(['status' => true, 'msg' => 'Reminder to sign Applicant contract sent successfully.']);
                        exit();
                    }
                } else {
                    echo json_encode($res_send_contract);
                    exit();
                }
            } else {
                $errors = $this->form_validation->error_array();
                echo json_encode(['status' => false, 'error' => implode(', ', $errors)]);
                exit();
            }
        } else {
            echo json_encode(['status' => false, 'error' => 'Something went wrong.']);
            exit();
        }
    }

    function add_applicant_app_onboarding()
    {
        $reqestData = request_handler('access_recruitment');
        $reqData = $reqestData->data;
        $type = $reqData->type;
        $status = $reqData->status;
        $text = $type == 'orientation' ? 'orientation' : 'onboarding';

        if (!empty($reqData->task_applicant_id) && in_array($type, ['orientation', 'onboarding']) && in_array($status, ['1', '2'])) {
            if (isset($reqData->requestBy) && $reqData->requestBy == 'cabday' && isset($reqData->applicant_id)) {
                $reqData->applicant_rule = json_encode([
                    'type_of_checks' => ['cab_day_interview_permission_edit'],
                    'applicant_id' => $reqData->applicant_id,
                    'extra_params' => ['type' => 'taskApplicantId', 'taskIdOrCabdayDetailId' => $reqData->task_applicant_id, 'adminId' => $reqestData->adminId]
                ]);
                $validation_rules = array(
                    array('field' => 'task_applicant_id', 'label' => 'Task applicant id', 'rules' => 'required'),
                    array('field' => 'applicant_rule', 'label' => 'addresses', 'rules' => 'callback_check_applicant_required_checks')
                );

                $this->form_validation->set_data((array) $reqestData->data);
                $this->form_validation->set_rules($validation_rules);

                if (!$this->form_validation->run()) {
                    $errors = $this->form_validation->error_array();
                    $response = ['status' => false, 'error' => implode(', ', $errors)];
                    echo json_encode($response);
                    exit;
                }
            }

            $app_orientation = $this->Recruitment_applicant_model->check_applicant_onboarding_eligibility($reqData->task_applicant_id, $reqData->type);

            if (empty($app_orientation) && $app_orientation != 0) {

                $return = ['status' => false, 'error' => 'Applicant not on stage for add ' . $text];
            } elseif ($app_orientation > 0) {

                $return = ['status' => false, 'error' => 'Already update app ' . $text];
            } elseif ($app_orientation == 0) {

                if ($type == 'orientation') {
                    $this->Recruitment_applicant_model->add_applicant_orientation($reqData->task_applicant_id, $status);
                    $return = ['status' => true];
                } else if ($type == 'onboarding') {
                    $this->Recruitment_applicant_model->add_applicant_onboarding($reqData->task_applicant_id, $status);
                    $return = ['status' => true];
                } else {
                    $return = ['status' => false, 'error' => 'invalid request.2'];
                }
            }
        } else {
            $return = ['status' => false, 'error' => 'Task applicant id not found'];
        }

        echo json_encode($return);
    }

    private function docuSign_sign_position()
    {
        $position = array();
        $position['position_x'] = 100;
        $position['position_y'] = 100;
        $position['document_id'] = 1;
        $position['page_number'] = 1;
        $position['recipient_id'] = 1;
        return $position;
    }

    function resend_applicant_docusign_user_contract($Data)
    {
        $position = $this->docuSign_sign_position();
        $response_user_details = $this->Recruitment_applicant_model->resend_docusign_enevlope($Data);

        if (!empty($response_user_details)) {
            $validation_rules_user = array(
                array('field' => 'applicant_name', 'label' => 'applicant name', 'rules' => 'required'),
                array('field' => 'email', 'label' => 'email id', 'rules' => 'required'),
                array('field' => 'envelope_id', 'label' => 'envelope id', 'rules' => 'required')
            );

            $this->form_validation->set_data($response_user_details);
            $this->form_validation->set_rules($validation_rules_user);

            if ($this->form_validation->run() == TRUE) {

                $this->load->library('DocuSignEnvelope');
                $signerDetails = array();
                $signerDetails['name'] = $response_user_details['applicant_name'];
                $signerDetails['email_subject'] = 'OGA Employment Contract';
                $signerDetails['email'] = $response_user_details['email'];

                $envlopDetails = array();
                $envlopDetails['userdetails'] = $signerDetails;
                $envlopDetails['position'] = $position;
                $envlopDetails['envelopeId'] = $response_user_details['envelope_id'];

                $statusDocuSign = $this->docusignenvelope->ResendEnvelope($envlopDetails);
                // var_dump($statusDocuSign);
                if (isset($statusDocuSign['status']) && $statusDocuSign['status']) {
                    // $envId = $response_user_details['envelope_id'];
                    //$this->basic_model->update_records('recruitment_applicant_contract', array('signed_status'=>1),array('envelope_id'=>$envId,'archive'=>0));
                    return ['status' => true, 'msg' => 'applicant contract send successfully', 'data' => $response_user_details];
                } else {
                    return ['status' => false, 'error' => json_encode($statusDocuSign)]; // 'applicant contract not send.'];
                }
            } else {
                $errors_details = $this->form_validation->error_array();
                return ['status' => false, 'error' => implode(', ', $errors_details)];
            }
        } else {
            return ['status' => false, 'error' => 'Employment Contract not exist.'];
        }
    }

    function get_applicant_skill()
    {
        $reqestData = request_handler('access_recruitment');
        if (!empty($reqestData->data)) {
            $applicantId = $reqestData->data->applicant_id;
            $res = $this->Recruitment_applicant_model->get_applicant_skill($applicantId);

            echo json_encode(['status' => true, 'data' => $res]);
            exit();
        }
    }

    function update_applicant_skill()
    {
        $reqestData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqestData->adminId);

        if (!empty($reqestData->data)) {
            $reqData = $reqestData->data;
            $this->Recruitment_applicant_model->update_applicant_skill($reqestData->data);

            // set log details
            $applicantName = $this->username->getName('applicant', $reqData->applicant_id);
            $adminName = $this->username->getName('admin', $reqestData->adminId);

            $this->loges->setUserId($reqData->applicant_id);
            $this->loges->setDescription(json_encode($reqData));
            $this->loges->setTitle('Applicant - ' . $applicantName . ' skill updated');
            $this->loges->setSpecific_title('Updated skill by ' . $adminName);
            $this->loges->createLog();

            echo json_encode(['status' => true]);
            exit();
        }
    }

    function get_json()
    {
        $re = $this->basic_model->get_record_where("participant_genral", $column = '', $where = '');
        echo json_encode($re);
    }


    /**
     * Get document docs and return them as `id => code`
     * @return array<int, string>
     */
    protected function document_codes()
    {
        return $this->Recruitment_applicant_model->document_codes();
    }

    /**
     * Retrieve allowed IDs for 'title' ref
     *
     * @return int[]
     */
    protected function get_title_ids()
    {
        $this->load->model('sales/Reference_model');
        $title_options = $this->Reference_model->get_title_reference_options();
        $title_ids = array_values(array_column($title_options, 'value'));
        return $title_ids;
    }

    /**
     *  Change Owner
     *
     */
    public function change_owner() {
        $reqData = request_handler('access_recruitment');
        $owner_id = $reqData->data->owner->value;
        $applicants = $reqData->data->applicants;
        $this->Recruitment_applicant_model->change_owner($applicants,$owner_id,$reqData->adminId);
        echo json_encode(['status' => true]);
        exit();
    }

    /**
     * Post Feed
     */
    public function feed_migrate() {
        try {
            // Get the request data
            $feed_detail = $this->Recruitment_applicant_model->get_feed();
            foreach ($feed_detail as $feed) {

                $this->Recruitment_applicant_model->delete_data($feed->history_id,'application_history');
                $this->Recruitment_applicant_model->delete_data($feed->id,'application_history_feed');
            }
            $results = $this->Recruitment_applicant_model->get_stage_notes();

            foreach ($results as $row) {
               $stage_id = $this->Recruitment_applicant_model->getStageId($row->stage);
               $feedId = $this->Recruitment_applicant_model->save_feed_migrate($row,$stage_id);

            }
            $response = ['status' => true];

        } catch (Exception $e) {

            $response = ['status' => false, 'error' => $e];
        }

        $response = json_encode($response);
        echo $response;
        exit();
    }

    /**
     * splitting applicants data into new tables HCM-911
     * tbl_person, tbl_person_email, tbl_person_phone
     * tbl_recruitment_applicant_applied_application
     */
    public function split_applicants_data()
    {
        $this->db->from('tbl_recruitment_applicant');
        $this->db->order_by("id", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $results = $query->result();

        foreach ($results as $row) {

            # check if the same rows exist?
            $person_id = null;
            $person_data = $this->basic_model->get_record_where('person', ["id"], ['firstname' => $row->firstname, 'lastname' => $row->lastname, 'created' => $row->created]);
            if (isset($person_data) && isset($person_data[0]))
                $person_id = $person_data[0]->id;

            # insert record into tbl_person table and get the last inserted id
            if (!$person_id) {
                $person_data = array('firstname' => $row->firstname, 'lastname' => $row->lastname, 'created' => $row->created, 'updated' => $row->updated);
                $person_id = $this->basic_model->insert_records('person', $person_data, FALSE);

                #fetching and inserting emails
                $emails_res = $this->Recruitment_applicant_model->get_applicant_email($row->id);
                $emails_data = [];
                if ($emails_res) {
                    foreach ($emails_res as $email_row) {
                        $emails_data[] = array('person_id' => $person_id, 'email' => $email_row->email, 'created' => $row->created, 'updated' => $row->updated, 'primary_email' => $email_row->primary_email, 'archive' => 0);
                    }
                    $this->basic_model->insert_records('person_email', $emails_data, TRUE);
                }

                #fetching and inserting phones
                $phones_res = $this->Recruitment_applicant_model->get_applicant_phone($row->id);
                $phones_data = [];
                if ($phones_res) {
                    foreach ($phones_res as $phone_row) {
                        $phones_data[] = array('person_id' => $person_id, 'phone' => $phone_row->phone, 'created' => $row->created, 'updated' => $row->updated, 'primary_phone' => $phone_row->primary_phone, 'archive' => 0);
                    }
                    $this->basic_model->insert_records('person_phone', $phones_data, TRUE);
                }
            }

            # updating applicant table with personid
            $applicant_data = array("person_id" => $person_id);
            $this->basic_model->update_records('recruitment_applicant', $applicant_data, ['id' => $row->id]);

            #inserting following columns into tbl_recruitment_applicant_applied_application
            # jobid, channelid, referrer_url, current_stage, recruiter
            $application_data = array("jobid" => $row->jobId, "channelid" => $row->channelId, "referrer_url" => (isset($row->referrer_url) ? $row->referrer_url : ''), "current_stage" => $row->current_stage, "recruiter" => $row->recruiter);
            $this->basic_model->update_records('recruitment_applicant_applied_application', $application_data, ['applicant_id' => $row->id]);
        }
    }

    function get_application_by_application_id()
    {
        $reqData = $applicant_data = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = $reqData->data;
       
        if (!empty($reqData->applicant_id)) {
            $this->load->library('ApplicantRequiredValidation');
            $this->applicantrequiredvalidation->setApplicantId($reqData->applicant_id);
            $this->applicantrequiredvalidation->setValidationRule(['applicant_id_duplicate' => true]);
            $res = $this->applicantrequiredvalidation->check_applicant_required_checks();
            if (!$res['status']) {
                echo json_encode(['status' => false, 'data' => 'Applicant not found']);
                exit();
            }

            // get applicant details
            if(isset($reqData->application_id) == true){
                $result['details'] = $this->Recruitment_applicant_model->get_application_info($reqData->applicant_id,$reqData->application_id, $adminId,$applicant_data);
            }else{
                $result['details'] = $this->Recruitment_applicant_model->get_applicant_info($reqData->applicant_id, $adminId,$applicant_data);
            }


            if (!empty($result['details'])) {
                // get applicant phone number
                $result['phones'] = $this->Recruitment_applicant_model->get_applicant_phone($reqData->applicant_id);

                // get applicant emails
                $result['emails'] = $this->Recruitment_applicant_model->get_applicant_email($reqData->applicant_id);

                // get applicant reference
                $result['references'] = $this->Recruitment_applicant_model->get_applicant_reference($reqData->applicant_id);

                // get applicant addresses
                $applicant_address = $this->Recruitment_applicant_model->get_applicant_address($reqData->applicant_id);               

                $result['applicant_address']['unit_number'] = isset($applicant_address->unit_number) ? $applicant_address->unit_number : null;
                $result['applicant_address']['address'] = isset($applicant_address->address) ? $applicant_address->address : null;
                $result['applicant_address']['manual_address'] = isset($applicant_address->manual_address) ? $applicant_address->manual_address : null;
                $result['applicant_address']['is_manual_address'] = isset($applicant_address->is_manual_address) ? $applicant_address->is_manual_address : null;

                $result['applications'] = $this->Recruitment_applicant_model->get_applicant_job_application_by_job_id($reqData->applicant_id,$reqData->application_id);

                $result['last_update'] = $this->Recruitment_applicant_model->get_applicant_last_update($reqData->application_id);

                echo json_encode(['status' => true, 'data' => $result]);
            } else {

                echo json_encode(['status' => false, 'data' => 'Applicant not found']);
            }
        }
    }

     /*
     * fetches all the application statuses 
     * 
     * as of now common for all BU's
     */
    function get_application_stage_status() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->adminId)) {
            $result = $this->Recruitment_applicant_model->get_application_stage_status();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetches all the final application statuses for application details page
     */
    function get_application_statuses_final() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $response = $this->Recruitment_applicant_model->get_application_statuses_final();
            echo json_encode($response);
        }
        exit(0);
    }

     /**
     * Updating the application status.
     */
    public function update_application_status() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->Recruitment_applicant_model->update_application_status($data, $reqData->adminId,false, $reqData);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * For document list
     *
     * Return type json
     * - count
     * - data
     * - status
     */
    function get_document_list() {
        $reqData = request_handler('access_recruitment');
        // pr($reqData);
        if (!empty($reqData)) {
            $data = $reqData->data;
            $error_check_id = false;

            $applicant_id = '';
            if (isset($data->applicant_id) == true && empty($data->applicant_id) == false) {
                $applicant_id = $data->applicant_id;
            }

            if ($applicant_id == '' && $applicant_id == 'null') {
                $error_check_id = true;
            }

            // Check data is valid or not
            if ($error_check_id == false) {
                // Call model for get doucment list
                $result = $this->Recruitment_applicant_model->get_document_list($data, true,$reqData);
            } else {
                // If requested data is empty or null
                $result = ['status' => false, 'error' => 'Applicant Id is null'];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Get document type list by search
     */
    public function get_attachment_category_details_search()
    {
        $reqData = request_handler('access_recruitment');

        //Get document type details from document type table
        $data['doc_category'] = ['apply_job', 'recruitment_stage'];
        $data['col1'] = 'label';
        $data['col2'] = 'value';
        $data['module_id'] = REQUIRMENT_MODULE_ID;
        $data['query_label'] = $reqData->data->query ?? '';

        $new_req = $this->Document_type_model->get_document_type_search($data , TRUE);

        echo json_encode($new_req);
        exit();
    }

    /*
     * To save document
     * Validate request and return response according to request
     *
     * Return type json
     *  - status
     *  - msg
     *  - error if any error  occured
     */
    function save_applicant_document() {
        // draft const value
        $draft_status = self::DRAFT_STATUS;
        // Get the request data
        $reqData = request_handlerFile('access_recruitment',true,false,true);
        $adminId = $reqData->adminId;

        if (!empty($reqData)) {
            $data = (array) $reqData;

            // Validation rules set
            $validation_rules = [
                array('field' => 'doc_type_id', 'label' => 'Document Type', 'rules' => 'required'),
                array('field' => 'is_member', 'label' => 'Is Member', 'rules' => 'required'),
                array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required', "errors" => [ "required" => "Missing member id" ]),
                array('field' => 'issue_date_mandatory', 'label' => ' Issue Date Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Issue Date Validation" ]),
                array('field' => 'expiry_date_mandatory', 'label' => 'Expiry Date ', 'rules' => 'required', "errors" => [ "required" => "Missing Expiry Date Validation" ]),
                array('field' => 'reference_number_mandatory', 'label' => 'Reference Number Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Reference Number Validation" ])
            ];

            if(!empty($data['doc_name']) && $data['doc_name']==VISA_DETAILS) {
                $validation_rules[] = array('field' => 'visa_category', 'label' => 'Visa Category', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Category id" ]);
                $validation_rules[] = array('field' => 'visa_category_type', 'label' => 'Visa type', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Type id" ]);
            }

            $status = '';
            if (isset($data['status']) == true && $data['status'] != '') {
                $status = $data['status'];
            }

            $action = 'created';
            if (isset($data['action']) == true && $data['action'] != '') {
                $action = $data['action'];
            }
            /**
              * Dynamic validation fields related with document type if status not draft
              * - Issue Date
              * - Expiry Date
              * - Reference Number
              */
            $issue_date_mandatory = filter_var($data['issue_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($issue_date_mandatory == true && ($data['issue_date'] == '' || $data['issue_date'] == null) && $status != $draft_status) {
                $validation_rules[] = array(
                    'field' => 'issue_date', 'label' => 'Issue Date', 'rules' => 'required'
                );
            }

            $expiry_date_mandatory = filter_var($data['expiry_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($expiry_date_mandatory == true && ($data['expiry_date'] == '' || $data['expiry_date'] == null) && $status != $draft_status) {
                $validation_rules[] = array(
                    'field' => 'expiry_date', 'label' => 'Expiry Date', 'rules' => 'required'
                );
            }

            $reference_number_mandatory = filter_var($data['reference_number_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($reference_number_mandatory == true && ($data['reference_number'] == '' || $data['reference_number'] == null) && $status != $draft_status) {
                $validation_rules[] = array(
                    'field' => 'reference_number', 'label' => 'Reference Number', 'rules' => 'required'
                );
            }

            if ($action == 'update') {
                $validation_rules[] = array(
                    'field' => 'document_id', 'label' => 'Document', 'rules' => 'required', "errors" => [ "required" => "Missing Document Id" ]
                );
            }

            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {

                $this->load->model('Basic_model');

                // Set Portal By
                $data['created_portal'] = 2;

                $is_member = filter_var($data['is_member'], FILTER_VALIDATE_BOOLEAN);
                if ($is_member == true) {
                    $module = 'member';
                } else {
                    $module = 'applicant';
                }
                $this->load->model('document/Document_attachment_model');
                if ($action != 'update') {
                    $data['id'] = 0;
                    // Call create document model
                    $document = $this->Document_attachment_model->save_document_attachment($data, $adminId, false, $module);
                    $msgTxt = 'created';
                } else {
                     // Call edit document model
                    $document = $this->Document_attachment_model->edit_document_attachment($data, $adminId, false, $module);
                    $msgTxt = 'updated';
                }

                // According to that document will be created
                if ($document['status'] == true) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    /**
                     * Create logs. it will represent the user action they have made.
                     */
                    $this->loges->setTitle("New document ".$msgTxt." for " . $data['doc_name'] ." by " . $adminName);  // Set title in log
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    // Create log
                    $this->loges->createLog();
                    $data = array('document_id' => $document['document_id']);
                    $response = ['status' => true, 'msg' => 'Document has been '.$msgTxt.' successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $document['error']];
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

    public function generate_and_mail_cab_day_certificate()
    {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $validation_rules = [
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', "errors" => [ "required" => "Missing Applicant Id" ]),
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required', "errors" => [ "required" => "Missing Application Id" ]),
            ];

            $this->form_validation->set_data((array) $reqData->data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $response = $this->Recruitment_applicant_model->generate_and_mail_cab_day_certificate($data, $adminId);
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
            echo json_encode($response);
            exit();
        }
    }

    public function generate_bulk_mail_cab_day_certificate()
    {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;

            if(empty($data['applications'])) {
                $errors = $this->form_validation->error_array();
                $response = ['status' => FALSE, 'error' => implode(', ', $errors)];
            } else {
                //Check the validation for group booking Applicant Request
                if(isset($data['group_booking_applicant']) && isset($data['applications']) && $this->check_applicant_status($data)) {
                    $response = ['status' => FALSE, 'error' =>
                        'Verify applicant(s) selected. Certificate cannot be generated for unsuccessful applicants'];
                } else {
                 $response = $this->Recruitment_applicant_model->generate_bulk_mail_cab_day_certificate($data, $adminId);
                }
            }
            echo json_encode($response);
            exit();
        }
    }

    /**
     * @param $data{array} applicant related info
     * @return {bool} true/false
     * Check applicant status success or unsuccessful
     */
    public function check_applicant_status($data) {
        foreach($data['applications'] as $val)
        {
            if(isset($val->interview_meeting_status) && $val->interview_meeting_status == 2)
            {
                return TRUE;
            }
        }
    }
    /**
     * create member for hired applicant
     */
    function create_member_for_hired_applicant()
    {
        $reqestData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqestData->adminId);
        $reqData = $reqestData->data;

        if (!empty($reqData)) {

            $validation_rules = array(
                array('field' => 'applicant_id', 'label' => 'applicant id', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'Application id', 'rules' => 'required'),
                array('field' => 'member_status', 'label' => 'member_status', 'rules' => 'required'),
            );

            $this->form_validation->set_data(obj_to_arr($reqData));
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $application_data = $this->Recruitment_applicant_model->get_application_details($reqData->application_id);
                if($application_data['data']->application_process_status == 7){
                    $resStatus = $this->Recruitment_applicant_model->create_member_for_hired_applicant($reqData, $reqestData->adminId);
                    if ($resStatus['status']) {
                        $applicantName = $this->username->getName('applicant', $reqData->applicant_id);

                        $this->loges->setUserId($reqData->applicant_id);
                        $this->loges->setDescription(json_encode($reqData));

                        $txt = 'success';
                        $this->loges->setTitle('Applicant - ' . $applicantName  . ' ' . $txt);  // set title in log
                        $this->loges->createLog(); // create log

                        $response = ['status' =>  $resStatus['status'], 'msg' => "Member created successfully"];
                    } else {
                        $response = $resStatus;
                    }
                }else{
                    $response = ['status' => false, 'error' =>"Application not hired"];
                }

                // set log details
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
    }

    /**
     * create member for bulk hired applicant
     */
    function create_member_for_bulk_hired_applicant()
    {
        $reqestData = request_handler('access_recruitment');
        $this->loges->setCreatedBy($reqestData->adminId);
        $reqData = $reqestData->data;

        if (!empty($reqData)) {

            $validation_rules = array(
                array('field' => 'member_status', 'label' => 'member_status', 'rules' => 'required'),
            );

            $this->form_validation->set_data(obj_to_arr($reqData));
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                    $check_application_status = true;
                    foreach ($reqData->applicants_details as $app) {
                     $application_data = $this->Recruitment_applicant_model->get_application_details($app->application_id);
                        if($application_data['data']->application_process_status != 7){
                            $check_application_status = false;
                        }
                    }
                     if($check_application_status){
                            $response = $this->Recruitment_applicant_model->create_member_for_bulk_hired_applicant($reqData->applicants_details, $reqData->member_status, $reqestData->adminId);

                            $requested_count = count($reqData->applicants_details);
            	            $updated_count = count($response);

                            // Msg handling
                            $msg = 'Created as member for '.$updated_count.' applicant out of '.$requested_count;
                            $status = true;
                            if ($requested_count == 1 && $updated_count == 1) {
                                $msg = 'Applicant created as member successfully';
                                $status = true;
                            }
                            if ($requested_count == 1 && $updated_count == 0) {
                                $msg = 'Applicant already converted as member';
                                $status = false;
                            }

                            if ($requested_count > 1 && $updated_count == 0) {
                                $msg = 'Applicant already converted as member';
                                $status = false;
                            }

                            if ($requested_count > 1 && $updated_count < $requested_count) {
                                $msg = 'Created as member for '.$updated_count.' applicant out of '.$requested_count;
                                $status = true;
                            }
                            if ($requested_count > 1 && $updated_count == $requested_count) {
                                $msg = 'Applicant created as member successfully';
                                $status = true;
                            }
                            if ($status == true) {
                                $response = array('status' => $status, 'data' => $response, 'updated_count' => $updated_count, 'msg' => $msg);
                            } else {
                                $response = array('status' => $status, 'data' => $response, 'updated_count' => $updated_count, 'error' => $msg);
                            }
                        }else{
                            $response = ['status' => false, 'error' => "Application not hired"];
                        }
                } else {
                    $errors = $this->form_validation->error_array();
                    $response = ['status' => false, 'error' => implode(', ', $errors)];
                }
          }

        echo json_encode($response);
    }

   /**
     * check applicant is already converted to member
     */
    public function check_applicant_already_active()
    {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData)) {
            $data = $reqData->data;

            $error_check_id = false;

            $applicant_id = '';
            if (isset($data->applicant_id) == true && empty($data->applicant_id) == false) {
                $applicant_id = $data->applicant_id;
            }

            if ($applicant_id == '' && $applicant_id == 'null') {
                $error_check_id = true;
            }
            $result = ['status' => false, 'is_member' =>'false','error' => 'Applicant Id is null'];
            // Check data is valid or not
            if ($error_check_id == false) {
                // Call model for get doucment list
                $appl_check = $this->Recruitment_applicant_move_to_hcm->check_applicant_already_active($applicant_id,'',$reqData);
                if (!empty($appl_check)) {
                    $txt = 'Applicant already active in HCM';
                    // set log
                    $result = ['status' => true, 'is_member' =>'true' , 'error' => $txt];
                }else{
                    $result = ['status' => true, 'is_member' =>'false'];
                }
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'is_member' =>'false','error' => "Requested data is null"];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Mark document as verified or not.
     */
    public function mark_document_status() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $data = $reqData->data;

            $validation_rules = array(
                array('field' => 'applicant_id', 'label' => 'Reference Id', 'rules' => 'required', 'errors'=> [ 'required' => 'Applicant Id is missing'] ),
                array('field' => 'application_id', 'label' => 'Status', 'rules' => 'required', 'errors'=> [ 'required' => 'Application Id is missing']),
                array('field' => 'mark_as', 'label' => 'Marked As', 'rules' => 'required'),
            );

            $this->form_validation->set_data((array) $data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $applicant_id = $data->applicant_id;
                $application_id = $data->application_id;
                $status = $data->mark_as;
                $list = $this->Recruitment_applicant_model->update_document_marked($applicant_id, $application_id, $status, $reqData->adminId);
                if ($status == false) {
                    $response = ['status' => true, 'msg' => "Undo verification successfully"];
                } else {
                    $response = ['status' => true, 'msg' => "Reference marked as verified successfully"];
                }

            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

        } else {
            $response = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($response);
    }

    /**
     * Get all mandatory list by applicant id
     */
    function get_applicant_mandatory_doucment_list() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $data = $reqData->data;

            $validation_rules = array(
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required')
            );

            $this->form_validation->set_data((array) $data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $applicant_id = $data->applicant_id;
                $application_id = $data->application_id;
                $list = $this->Recruitment_applicant_model->get_applicant_mandatory_doucment_list($applicant_id, $application_id, true,$reqData);

                $response = ['status' => true, 'msg' => "List fetched successfully", 'data' => $list];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

        } else {
            $response = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($response);
    }

    function send_rejection_email_template_to_email() {
        // template options
        $reqData = $this->input->post('reqData');
        if (!empty($reqData && $reqData['selected_template'])) {
            $res= $this->Recruitment_applicant_model->send_rejection_email_template_to_email($reqData['selected_template'], $reqData['applicant_id'],  $reqData['adminId'], $reqData['id']);
            echo json_encode(["status" => true, "data" => $res]);
        }
    }
}
