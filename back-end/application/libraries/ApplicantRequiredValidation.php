<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApplicantRequiredValidation
 *
 * @author user YDT
 */
class ApplicantRequiredValidation {

    protected $CI;
    protected $applicantId;
    protected $status;
    protected $current_stage;
    protected $error;
    protected $error_stutus;
    protected $validation_rule = [
        'flagged' => false,
        'flagged_pending' => false,
        'not_flagged_pending' => false,
        'not_flagged' => false,
        'duplicate' => false,
        'duplicate_pending' => false,
        'not_duplicate_pending' => false,
        'not_duplicate' => false,
        'current_stage' => false,
        'in_progress' => false,
        'not_archive' => false,
        'not_hired' => false,
        'on_stage_complete_verified_details' => false,
        'cab_day_interview_permission_edit'=>false,
        'applicant_id_duplicate'=>false
    ];

    public function __construct() {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();
        $this->applicantId = 0;
    }

    function setApplicantId($applicantId) {
        $this->applicantId = (int) $applicantId;
    }

    function setStatus($status) {
        $this->status = (int) $status;
    }

    function setValidationRule($rule) {
        $this->validation_rule = array_replace($this->validation_rule, $rule);
    }

    function check_applicant_required_checks($extra_params = []) {
        $this->CI->db->select(['ra.id', 'ra.status', 'ra.archive', 'ra.flagged_status', 'ra.current_stage', 'ra.duplicated_status']);
        $this->CI->db->from('tbl_recruitment_applicant as ra');
        $this->CI->db->where('ra.id', $this->applicantId);

        if ($this->validation_rule['duplicate_pending'] || $this->validation_rule['not_duplicate_pending'] || $this->validation_rule['not_duplicate'] || $this->validation_rule['duplicate']) {
            $this->CI->db->select('(select status from tbl_recruitment_applicant_duplicate_status where applicant_id = ra.id) as duplicate_m_status');
        }

        if ($this->validation_rule['current_stage']) {
            $this->CI->db->select(['rs.stage']);
            $this->CI->db->join('tbl_recruitment_stage as rs', 'rs.id = ra.current_stage and rs.archive = 0', 'inner');
        }

        $query = $this->CI->db->get();
        $res = $query->row();

        if (empty($res)) {
            $this->error[] = 'Applicant id not valid.';
        } else {
            if ($this->validation_rule) {
                foreach ($this->validation_rule as $rule => $status) {
                    if (!$status) {
                        continue;
                    }

                    switch (strtolower($rule)) {
                        case 'flagged':
                            if ((int) $res->flagged_status !== 1) {
                                $this->error[] = 'Applicant not flagged.';
                            }
                            break;

                        case 'not_flagged':
                            if (in_array((int) $res->flagged_status, [2, 3])) {
                                $this->error[] = 'Applicant is flagged.';
                            }
                            break;

                        case 'not_flagged_pending':
                            if (in_array((int) $res->flagged_status, [1, 3])) {
                                $this->error[] = 'Applicant requested for the flag.';
                            }
                            break;

                        case 'flagged_pending':
                            if (in_array((int) $res->flagged_status, [0, 2])) {
                                $this->error[] = 'Applicant not requested for flagged.';
                            }
                            break;

                        case 'duplicate':
                            if ((int) $res->duplicated_status !== 1) {
                                $this->error[] = 'Applicant not duplicate.';
                            }
                            break;

                        case 'duplicate_pending':
                            if ((int) $res->duplicate_m_status !== 1) {
                                $this->error[] = 'Applicant is not pending stage of duplicate.';
                            }
                            break;
                        case 'not_duplicate_pending':
                            if ((int) $res->duplicate_m_status === 1) {
                                $this->error[] = 'Applicant is pending for mark as duplicate.';
                            }
                            break;

                        case 'not_duplicate':
                            if ($res->duplicated_status != 0) {
                                $this->error[] = 'Applicant is duplicate.';
                            }
                            break;

                        case 'current_stage':
                            if ((int) $res->current_stage !== $this->current_stage) {
                                $this->error[] = 'Applicant current stage not valid for this action.';
                            }
                            break;

                        case 'in_progress':
                            if ((int) $res->status !== 1) {
                                $this->error[] = 'Applicant should be in progress stage.';
                            }
                            break;
                        case 'not_archive':
                            if ($res->archive == 1) {
                                $this->error[] = 'Applicant already archived.';
                            }
                            break;
                        case 'not_hired':
                            if ($res->status == 3) {
                                $this->error[] = 'Applicant already hired.';
                            }
                            break;
                        case 'on_stage_complete_verified_details':
                            $response = $this->on_stage_complete_wise_details_verified($this->applicantId, $extra_params->stageId, $extra_params->application_id);

                            if (empty($response['status'])) {
                                $this->error[] = $response['error'];
                            }
                            break;
                        case 'cab_day_interview_permission_edit':
                            $response = $this->permission_user_task($extra_params->adminId,$extra_params->taskIdOrCabdayDetailId,$extra_params->type);
                            if (!$response['status']) {
                                $this->error[] = $response['error'];
                            }
                        break;
                        case 'applicant_id_duplicate':
                            if ((int) $res->duplicated_status === 1) {
                                $this->error[] = 'Applicant not found.';
                            }
                        break;

                    }
                }
            }
        }

        if (!empty($this->error)) {
            return ['status' => false, 'error' => $this->error];
        } else {
            return ['status' => true];
        }
    }

    function on_stage_complete_wise_details_verified($applicantId, $stageId, $application_id = 0) {
        $this->CI->load->model('recruitment/Recruitment_applicant_model');
        $this->CI->load->model('recruitment/Recruitment_task_action');

        $response = ['status' => true];

        $stage_key = '';
        $res = $this->CI->basic_model->get_row('recruitment_stage', ['stage_key'], ['id' => $stageId]);
        #pr($res);
        if (!empty($res)) {
            $stage_key = $res->stage_key;
        }

        if ($stage_key == 'phone_interview') {
            $res = $this->CI->Recruitment_applicant_model->get_applicant_phone_interview_classification($applicantId, $application_id);

            if (empty($res)) {
                $response = ['status' => false, 'error' => 'Phone interview classificaiton not completed.'];
            }
        } elseif ($stage_key == 'group_schedule_interview') {
            $res = $this->CI->Recruitment_task_action->check_applicant_already_exist_in_interview($applicantId, 3);
            $status_check = array(2,4);

            if ($this->status == '') {
                $this->status = 0;
            }

            if (empty($res) && in_array($this->status, $status_check) == false) {
                $response = ['status' => false, 'error' => 'Applicant is not added in group interview task'];
            }
        } elseif ($stage_key == 'group_applicant_responses') {
            $res = $this->CI->Recruitment_task_action->check_applicant_already_exist_in_interview($applicantId, 3);

            if (!empty($res) && $res->approve_deny_status == 0) {
                $response = ['status' => false, 'error' => 'Applicant is not accepted invitation of interview'];
            }
        } elseif ($stage_key == 'group_interview_result') {
            $res = $this->CI->Recruitment_task_action->check_applicant_already_exist_in_interview($applicantId, 3);

            if (!empty($res) && ($res->quiz_status == 0)) {
                $response = ['status' => false, 'error' => 'Applicant quiz result is not completed yet'];
            }
        } elseif ($stage_key == 'position_and_award_level') {
            $res = $this->CI->basic_model->get_row('recruitment_applicant_pay_point_approval', ['id', 'status'], ['applicant_id' => $applicantId]);

            if (!empty($res) && ($res->status == 0)) {
                $response = ['status' => false, 'error' => 'Pay scale not approved yet'];
            }
        } elseif ($stage_key == 'schedule_cab_day') {
            $res = $this->CI->Recruitment_task_action->check_applicant_already_exist_in_interview($applicantId, 6);

            $status_check = array(2,4);

            if ($this->status == '') {
                $this->status = 0;
            }

            if (empty($res) && in_array($this->status, $status_check) == false) {
                $response = ['status' => false, 'error' => 'Applicant is not added in cab day task'];
            }
        } elseif ($stage_key == 'cab_applicant_responses') {
            $res = $this->CI->Recruitment_task_action->check_applicant_already_exist_in_interview($applicantId, 6);

            if (!empty($res) && $res->approve_deny_status == 0) {
                $response = ['status' => false, 'error' => 'Applicant is not accepted invitation of cab day interview'];
            }
        } elseif ($stage_key == 'cab_day_result') {
            $res = $this->CI->Recruitment_task_action->check_applicant_already_exist_in_interview($applicantId, 6);

            if (!empty($res) && ($res->quiz_status == 0)) {
                $response = ['status' => false, 'error' => 'Applicant cab day quiz is not completed yet'];
            }
        } elseif ($stage_key == 'employment_contract') {
            $res = $this->CI->Recruitment_task_action->check_applicant_docusign_signed($applicantId, 6);
            $status_check = array(2,4);

            if ($this->status == '') {
                $this->status = 0;
            }

            if (!empty($res) && ($res->signed_status == 0) && in_array($this->status, $status_check) == false) {
                $response = ['status' => false, 'error' => 'Applicant ducument not siged'];
            }
        } elseif ($stage_key == 'member_app_onboarding') {
            $res = $this->CI->Recruitment_task_action->check_applicant_already_exist_in_interview($applicantId, 6);

            if (!empty($res) && ($res->app_orientation_status == 0 || $res->app_login_status == 0)) {
                $response = ['status' => false, 'error' => 'Applicant not completed app login or app orientation'];
            }
        } elseif (empty($stage_key)) {
            $response = ['status' => false, 'error' => 'Stage not found'];
        }


        return $response;
    }

    //ragocidId= 'recruitment_applicant_group_or_cab_interview_detail'
    function permission_user_task($adminId=0,$taskIdOrCabdayDetailId=0,$type='taskId'){ 
        $this->CI->load->model('recruitment/Recruitment_task_action');
        $superAdminIds=get_super_admins(); 
        
        $res =in_array($adminId,$superAdminIds)? 1: $this->CI->Recruitment_task_action->check_applicant_task_allocated_check_assign_requiter($adminId,$taskIdOrCabdayDetailId,$type);
        if (!empty($res)) {
            $response = ['status' => true];
        }else{
            $response = ['status' => false, 'error' => 'Sorry you do not have access to perform this action.'];
        }
        return $response;
    }

}
