<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Recruitment_application_model extends MX_Controller
{

    public function __construct() {
        $this->load->model('common/Common_model');
    }
    /**
     * fetches all the recruitment stages and sub-stages
     * return array
     */
    function get_recruitment_stages() {
        $select_column = array("rsl.title as label", "GROUP_CONCAT(rs.id) as value", "GROUP_CONCAT(rs.stage_label_id) as label_value");

        $this->db->select($select_column);
        $this->db->from('tbl_recruitment_stage as rs');
        $this->db->join('tbl_recruitment_stage_label as rsl', 'rsl.id = rs.stage_label_id', 'inner');
        $this->db->where('rsl.archive', 0);
        $this->db->where('rs.archive', 0);
        $this->db->group_by("rsl.title");
        $this->db->order_by("rsl.id", "ASC");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        $obj = new stdClass();
        $obj->label = "Hired";
        $obj->value = "-1";
        $obj->label_value = "-1";
        $result[] = $obj;

        $return = array('status' => true, 'data' => $result, 'msg' => 'Stage list successful');
        return $return;
    }

    /**
     * Update application stages
     * @param {obj} $reqData
     */
    function update_application_stage($reqData) {
        $data = $reqData->data;
        $adminId = $reqData->adminId;
        $applications =(array) $data->applications;
        $stage = $data->selected_stage;
        $stage_option = $data->selected_option;
        $stage_label = [];
        if (isset($stage_option) == true && isset($stage_option[0]) == true && isset($stage_option[0]->value) == true) {
            $stage_label = explode(',',$stage_option[0]->label_value);
        }
        $stageArr= explode(',',$stage);
        $applicant_res = [];
        foreach ($applications as $key => $applicant) {
            $applicant = (array) $applicant;
            $applicant_id = $applicant['applicant_id'];
            $application_id = $applicant['application_id'];

            // Check applicant have selected stage
            $applicantAvail = $this->get_applicant_stage_avail_by_id($applicant_id, $application_id, $stage_label);
            if ((isset($applicantAvail) == false || empty($applicantAvail) == true) && isset($stageArr) == true && isset($stageArr[0]) == true && $stageArr[0] != '-1') {
                continue;
            }

            // Check applicant have selected stage
            $applicantStage = $this->get_applicant_stage_status_by_id($applicant_id, $application_id, $stage_label);
            if (isset($applicantStage) == false || empty($applicantStage) == true) {
                continue;
            }

            $stageDetails = $this->get_applicant_stage_details_by_id($applicant_id, $application_id);
            $applicant_res[$application_id]['stage_details'] = $stageDetails;

            // Update stage status
            $markApplication = $this->markApplicantStage($applicant_id, $application_id, $applicant, $stage, $stageArr, $stageDetails, $stage_label, $adminId);

            // Mark applicant status as hired
            if (isset($stageArr) == true && isset($stageArr[0]) == true && $stageArr[0] == '-1') {
                // mark as recruitment completed
                $typeDataCurrentStage = ['recruitment_complete'];
                $interViewCurrentRes = $this->Recruitment_applicant_model->get_stageid_by_key_bypass($typeDataCurrentStage);
                if (!empty($interViewCurrentRes)) {
                    $last_stage_id = $interViewCurrentRes[0];
                } else {
                    $last_stage_id = 14;
                }

                $hired_as = 2;

                // update application when finalized
                $STATUS_COMPLETED = 3;
                $this->basic_model->update_records('recruitment_applicant_applied_application', ['updated' => DATE_TIME, 'status' => $STATUS_COMPLETED, 'current_stage' => $last_stage_id], ['id' => $application_id, 'applicant_id' => $applicant_id]
                );

                $this->basic_model->update_records('recruitment_applicant', ['current_stage' => $last_stage_id, 'hired_as' => $hired_as, 'hired_date' => DATE_TIME, 'status' => '3'], ['id' => $applicant_id]);

                $this->loges->setCreatedBy($adminId);
                $this->loges->setUserId($applicant_id);
                $this->loges->setDescription(json_encode($stageArr));

                $applicantName = $this->username->getName('applicant', $applicant_id);
                $stage_label = $this->username->getName('stage_label', $last_stage_id);
                $txtData = ['1' => 'Pending', '2' => 'In progress', '3' => 'Completed', '4' => 'Unsuccessfull'];
                $txt = $txtData[$STATUS_COMPLETED] ?? 'Unsuccessfull';
                $this->loges->setTitle('Applicant - ' . $applicantName . ' ' . $stage_label . ' ' . $txt);  // set title in log
                $this->loges->setSpecific_title($stage_label . ' ' . $txt);
                $this->loges->createLog(); // create log
            }
        }
        return $applicant_res;
    }

    /**
     * Mark application past stage completed of selected stage & mark selected stage as in-progress
     */
    function markApplicantStage($applicant_id, $application_id, $applicant, $stage, $stageArr, $stageDetails, $stage_label, $adminId) {
        $temp_up_stage = [];
        $key_in = 0;
        $stage_key_match = false;
        $selected_stage_updated = false;
        // get all completeld stage
        $applicant_substage = $this->get_all_substage_by_application_id($applicant_id, $application_id);
        $flow = '';
        foreach ($stageDetails as $key => $stage) {
            $subStage = $stage['sub_stage'];
            $currentStage = $stage['current_stage'];
            foreach ($subStage as $sub_key => $sub_stage) {
                if ($stage_key_match == true) {
                    // continue;
                }
                $stage_number = $sub_stage['stage_number'];
                $stage_key = $sub_stage['stage_key'];
                $stage_id = $sub_stage['id'];
                $stage_label_id = $sub_stage['stage_label_id'];

                $temp_up_stage[$key_in]['stage_id'] = $stage_id;
                $temp_up_stage[$key_in]['stage_key'] = $stage_key;
                $temp_up_stage[$key_in]['stage_number'] = $stage_number;

                if (in_array($stage_id, $stageArr) == true && $stage_key_match == false) {
                    $stage_key_match = true;
                    $selected_stage_pos = $this->searcharray($stage_id, 'id', $applicant_substage);
                    $current_stage_pos = $this->searcharray($currentStage, 'id', $applicant_substage);
                    // update flow
                    if ($selected_stage_pos != '' && $current_stage_pos != '' && $selected_stage_pos > $current_stage_pos) {
                        $flow = 'forward';
                    }
                    if ($selected_stage_pos != '' && $current_stage_pos != '' && $selected_stage_pos < $current_stage_pos) {
                        $flow = 'backward';
                    }
                    if ($selected_stage_pos != '' && $current_stage_pos != '' && $selected_stage_pos == $current_stage_pos) {
                        $flow = 'nutual';
                    }
                    if ($selected_stage_pos == '') {
                        $flow = 'forward-new';
                    }
                }
                if ($stage_key_match == true && $selected_stage_updated == true) {
                    $flow = 'forward-pending';
                }
                // check stage data already exist
                $stage_res = $this->basic_model->get_row('recruitment_applicant_stage', ['*'], ['application_id' => $application_id, 'applicant_id' => $applicant_id, 'archive' => 0, 'stageId' => $stage_id]);
                // completed status
                if ($stage_key_match == true && $selected_stage_updated == false) {
                    $update_status = 2;
                } else {
                    switch ($flow) {
                        case 'forward':
                            $update_status = 3;
                            break;
                        case 'nutual':
                            $update_status = 2;
                            break;
                        case 'backward':
                        case 'forward-new':
                        case 'forward-pending':
                            $update_status = 1;
                            break;
                        default:
                            $update_status = 3;
                            break;
                    }
                }

                // Mark task & quiz completed if scheduled
                $taskApllicantArchiveData = ['group_schedule_interview' => 'group_interview', 'schedule_cab_day' => 'cab_day'];
                if (array_key_exists($stage_key, $taskApllicantArchiveData) && $update_status == 3) {
                    $stage_wise_details_call = true;
                    $taskApplicantData = $this->Recruitment_applicant_model->get_last_task_applicant_id_by_stage_key_and_applicant_id($applicant_id, ['task_stage_key' => $taskApllicantArchiveData[$stage_key]]);
                    if (!empty($taskApplicantData) && !empty($taskApplicantData['task_applicant_id'])) {
                        $task_stage_key = $taskApllicantArchiveData[$stage_key];
                        $recruitment_task_applicant_id = $taskApplicantData['task_applicant_id'];
                        $quiz_status = 1;
                        $is_update = $this->load->Basic_model->update_records('recruitment_applicant_group_or_cab_interview_detail', array('quiz_status' => $quiz_status), array('recruitment_task_applicant_id' => $recruitment_task_applicant_id));

                        // commit_status
                        $this->commit_status($recruitment_task_applicant_id, $task_stage_key, $applicant_id, $application_id, '');
                    }
                }

                if (empty($stage_res)) {
                    // create next stage data and mark as pending
                    $in_data = array(
                        'application_id' => $application_id,
                        'applicant_id' => $applicant_id,
                        'status' => $update_status,
                        'created' => DATE_TIME,
                        'action_at' => DATE_TIME,
                        'archive' => 0,
                        'stageId' => $stage_id,
                        'action_by' => $adminId
                    );
                    $this->basic_model->insert_records('recruitment_applicant_stage', $in_data, false);
                    $this->loges->setCreatedBy($adminId);
                    $this->loges->setUserId($applicant_id);
                    $this->loges->setDescription(json_encode($stageArr));

                    $applicantName = $this->username->getName('applicant', $applicant_id);
                    $stage_label = $this->username->getName('stage_label', $stage_id);
                    $txtData = ['1' => 'Pending', '2' => 'In progress', '3' => 'Completed', '4' => 'Unsuccessfull'];
                    $txt = $txtData[$update_status] ?? 'Unsuccessfull';
                    $this->loges->setTitle('Applicant - ' . $applicantName . ' ' . $stage_label . ' ' . $txt);  // set title in log
                    $this->loges->setSpecific_title($stage_label . ' ' . $txt);
                    $this->loges->createLog(); // create log
                } else {
                    // update the status if status not 3 - completed
                    if (isset($stage_res->status) && $stage_res->status != $update_status) {

                        $stage_data = array('status' => $update_status, 'action_at' => DATE_TIME, 'action_by' => $adminId);
                        $stage_where = array('application_id' => $application_id, 'applicant_id' => $applicant_id, 'stageId' => $stage_id, "archive" => 0);
                        $this->basic_model->update_records('recruitment_applicant_stage', $stage_data, $stage_where);

                        // Update log
                        $this->loges->setCreatedBy($adminId);
                        $this->loges->setUserId($applicant_id);
                        $this->loges->setDescription(json_encode($stageArr));

                        $applicantName = $this->username->getName('applicant', $applicant_id);
                        $stage_label = $this->username->getName('stage_label', $stage_id);
                        $txtData = ['1' => 'Pending', '2' => 'In progress', '3' => 'Completed', '4' => 'Unsuccessfull'];
                        $txt = $txtData[$update_status] ?? 'Unsuccessfull';
                        $this->loges->setTitle('Applicant - ' . $applicantName . ' ' . $stage_label . ' ' . $txt);  // set title in log
                        $this->loges->setSpecific_title($stage_label . ' ' . $txt);
                        $this->loges->createLog(); // create log
                    }
                }
                if ($stage_key_match == true && $selected_stage_updated == false) {
                    // update application when finalized
                    $STATUS_COMPLETED = 3;
                    $updateArr = ['updated' => DATE_TIME, 'current_stage' => $stage_id];
                    $this->basic_model->update_records('recruitment_applicant_applied_application', $updateArr, ['id' => $application_id, 'applicant_id' => $applicant_id]);
                    $selected_stage_updated = true;
                }

                $key_in++;
            }
            $key_in++;
        }

    }


    function get_applicant_stage_details_by_id($applicant_id, $application_id = 0) {
        $applicant_id = $this->db->escape_str($applicant_id, true);
        $application_id = $this->db->escape_str($application_id, true);
        $this->db->select(['rsl.id', 'rsl.title', 'rsl.stage_number', 'rsl.key_name', 'raaa.current_stage']);
        $this->db->join('tbl_recruitment_job_stage as rjs', 'rjs.stage_id = rsl.id AND rjs.archive = 0', 'inner');
        $this->db->join('tbl_recruitment_applicant_applied_application as raaa', 'raaa.jobId = rjs.jobId AND raaa.id =' . $application_id, 'inner');
        $this->db->from("tbl_recruitment_stage_label as rsl");
        $this->db->where(["rsl.archive" => 0]);
        $this->db->order_by('stage_order_by', 'asc');
        $query = $this->db->get();
        $main_stage = $query->result();

        $this->db->select(['rs.title', 'rs.stage', 'ras.action_by', 'ras.action_at', 'rs.stage_label_id', 'rs.id', 'rs.stage_key', 'trsl.stage_number', 'trsl.display_stage_number']);
        $this->db->select("CASE
        WHEN ras.status is NULL THEN (1)
        ELSE ras.status
        END as stage_status", false);

        $this->db->select("CASE
        WHEN ras.action_by > 0 THEN (select concat(firstname,' ',lastname) as fullname from tbl_member where id = ras.action_by)
        ELSE ''
        END as action_username", false);

        $this->db->from('tbl_recruitment_stage as rs');
        $this->db->join('tbl_recruitment_applicant_stage as ras', 'ras.stageId = rs.id AND ras.archive = 0 AND ras.applicant_id = ' . $applicant_id . ' AND ras.application_id = ' . $application_id, 'LEFT');
        $this->db->join('tbl_recruitment_stage_label as trsl', 'trsl.id = rs.stage_label_id AND trsl.archive = 0', 'inner');

        $this->db->join('tbl_recruitment_job_stage as rjs', "rjs.stage_id = trsl.id AND rjs.archive = 0 AND rjs.jobId=(select jobId from tbl_recruitment_applicant_applied_application where id = '$application_id' AND applicant_id = '$applicant_id' AND archive = 0 limit 1)", 'inner');

        $this->db->where('rs.archive', 0);

        $this->db->order_by('rs.stage_order', 'asc');
        $this->db->group_by('rs.id');

        $query = $this->db->get();
        #last_query();
        $stage_details = $query->result();

        $mapping_coponent = get_all_stage_mapping_component();
        $sub_stages = [];
        $main_stage_status = [];
        if (!empty($stage_details)) {
            foreach ($stage_details as $val) {
                $val->component_name = $mapping_coponent[$val->stage_key];
                if ($val->stage_status)
                    $main_stage_status[$val->stage_label_id][$val->stage] = array('stage_status' => $val->stage_status, 'action_username' => $val->action_username, 'action_at' => $val->action_at, 'display_stage_number' => $val->display_stage_number);
                $sub_stages[$val->stage_label_id][] = (array) $val;
            }
        }
        if (!empty($main_stage)) {
            foreach ($main_stage as $key => $val) {
                $x = $main_stage_status[$val->id];
                $x = $this->Recruitment_applicant_model->get_main_stage_status($x);

                $val->sub_stage = $sub_stages[$val->id];
                $main_stage[$key] = array_merge((array) $val, (array) $x);
            }
        }
        return $main_stage;
    }

    /**
     * Get applicant have stelected stage
     * return array
     */
    function get_applicant_stage_avail_by_id($applicant_id, $application_id, $stage) {

        $select_column = array("rjs.*");

        $this->db->select($select_column);
        $this->db->from('tbl_recruitment_applicant_applied_application as raaa');
        $this->db->join('tbl_recruitment_job_stage as rjs', 'rjs.jobId = raaa.jobId', 'INNER');
        $this->db->where_in('rjs.stage_id', $stage);
        $this->db->where('raaa.applicant_id', $applicant_id);
        $this->db->where('raaa.id', $application_id);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        return $result;
    }

    /**
     * Check applicant is hired / rejected
     * return array
     */
    function get_applicant_stage_status_by_id($applicant_id, $application_id, $stage) {

        $select_column = array("raaa.*");

        $this->db->select($select_column);
        $this->db->from('tbl_recruitment_applicant_applied_application as raaa');
        $this->db->where('raaa.applicant_id', $applicant_id);
        $this->db->where('raaa.id', $application_id);
        $this->db->where_not_in('raaa.status', [2,3]);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        return $result;
    }

    /**
     * Get all sub stages only completed|Progress by id
     * return array
     */
    function get_all_substage_by_application_id($applicant_id, $application_id) {
        $applicant_id = $this->db->escape_str($applicant_id, true);
        $application_id = $this->db->escape_str($application_id, true);
        $this->db->select(['rs.title', 'rs.stage', 'ras.action_by', 'ras.action_at', 'rs.stage_label_id', 'rs.id', 'rs.stage_key', 'trsl.stage_number', 'trsl.display_stage_number']);
        $this->db->select("CASE
        WHEN ras.status is NULL THEN (1)
        ELSE ras.status
        END as stage_status", false);

        $this->db->select("CASE
        WHEN ras.action_by > 0 THEN (select concat(firstname,' ',lastname) as fullname from tbl_member where id = ras.action_by)
        ELSE ''
        END as action_username", false);

        $this->db->from('tbl_recruitment_stage as rs');
        $this->db->join('tbl_recruitment_applicant_stage as ras', 'ras.stageId = rs.id AND ras.archive = 0 AND ras.applicant_id = ' . $applicant_id . ' AND ras.application_id = ' . $application_id, 'LEFT');
        $this->db->join('tbl_recruitment_stage_label as trsl', 'trsl.id = rs.stage_label_id AND trsl.archive = 0', 'inner');

        $this->db->join('tbl_recruitment_job_stage as rjs', "rjs.stage_id = trsl.id AND rjs.archive = 0 AND rjs.jobId=(select jobId from tbl_recruitment_applicant_applied_application where id = '$application_id' AND applicant_id = '$applicant_id' AND archive = 0 limit 1)", 'inner');

        $this->db->where('rs.archive', 0);
        $this->db->where_in('ras.status', [2,3]);
        $this->db->order_by('rs.stage_order', 'asc');
        $this->db->group_by('rs.id');

        $query = $this->db->get();
        #last_query();
        $stage_details = $query->result();
        return $stage_details;
    }

    // Update commit status
    public function commit_status($recruitment_task_applicant_id, $task, $applicant_id, $application_id, $contract_id) {
        $this->db->select(['rta.id as recruitment_task_applicant_id', 'rta.taskId', 'ragid.quiz_status', 'ragid.applicant_status', 'ragid.contract_status', 'ragid.device_pin', 'ragid.device_pin']);
        $this->db->from('tbl_recruitment_task_applicant as rta');
        $this->db->join('tbl_recruitment_applicant as ra', 'ra.id = rta.applicant_id', 'inner');
        $this->db->join('tbl_recruitment_applicant_group_or_cab_interview_detail as ragid', 'ragid.recruitment_task_applicant_id = rta.id AND ragid.archive=0', 'inner');
        $this->db->join('tbl_recruitment_interview_type rit', 'rit.id = ragid.interview_type and rit.key_type="'.$this->db->escape_str($task, true).'" AND rit.archive= 0', 'inner');
        $this->db->where('rta.id', $recruitment_task_applicant_id);
        $this->db->where('rta.status', 1);
        $this->db->where('rta.archive', 0);
        $query = $this->db->get();
        $applicant_ques = $query->result_array();
        $is_update = false;
        if (!empty($applicant_ques)) {
            $update_arr = [];
            foreach ($applicant_ques as $key => $value) {
                $temp=[];
                $temp['recruitment_task_applicant_id'] = $value['recruitment_task_applicant_id'];
                $taskId = $value['taskId'];
                $temp['applicant_status'] = 1;
                $temp['contract_status'] = 1;

                if ($task == 'cab_day') {
                    $temp['contract_status'] = 1;
                    $temp['cab_certificate_status'] = 1;
                    $temp['document_status'] = 1;
                    $temp['genrate_cab_certificate'] = 1;
                    $temp['email_cab_certificate'] = 1;

                    $dataCB = $this->load->Basic_model->get_row($table_name = 'recruitment_applicant_contract', $columns = array('id','application_id'), $id_array = array('applicant_id' => $applicant_id, 'application_id' => $application_id, 'signed_status' => 1, 'task_applicant_id' => 0, 'id' => $contract_id));
                    if (!empty($dataCB) && $contract_id != '') {
                        $recruitment_task_applicant_id = $value['recruitment_task_applicant_id'];
                        // $contract_id = $data->id;
                        $quiz_status = 1;
                        $is_update = $this->load->Basic_model->update_records('recruitment_applicant_contract', array('task_applicant_id' => $recruitment_task_applicant_id), array('id' => $contract_id));
                    }
                }
                $update_arr[] = $temp;
            }

            if (!empty($update_arr)) {
                $this->Basic_model->insert_update_batch($action = 'update', $table_name = 'recruitment_applicant_group_or_cab_interview_detail', $update_arr, 'recruitment_task_applicant_id');

                $this->Basic_model->update_records('recruitment_task', array('commit_status' => 1, 'action_at' => DATE_TIME, 'status' => 2), array('id' => $taskId));
                $is_update = true;
            }
        }
    }

    /**
     * Find Index of array value
     */
    function searcharray($value, $key, $array) {
       foreach ($array as $k => $val) {
           if ($val->{$key} == $value) {
               return $k;
           }
       }
       return null;
    }

    /**
     * Update application stages
     * @param {obj} $reqData
     */
    function update_application_status_by_id($reqData, $testcase = false) {
        $reqData = $testcase ? json_decode($reqData) : $reqData;

        $data = $reqData->data;
        $adminId = $reqData->adminId;
        $applications =(array) $data->applications;
        $stage = $data->selected_stage;
        $stage_option = $data->selected_option;
        $selected_template = $data->selected_template ?? '';
        $stage_label = [];
        if (isset($stage_option) == true && isset($stage_option[0]) == true && isset($stage_option[0]->value) == true) {
            $stage_label = explode(',',$stage_option[0]->label);
        }
        $stageArr= explode(',',$stage);
        $applicant_res = [];
        foreach ($applications as $key => $applicant) {
            $applicant = (array) $applicant;
            $applicant_id = $applicant['applicant_id'];
            $application_id = $applicant['application_id'];
            $applicant['application_process_status'] = $stage;
            // Update status of application
            $response = $this->mark_application_status($applicant, $adminId,$reqData);
            // if rejected means send the email using selected template id
            if($applicant["application_process_status"] == 8){
                if(!empty($applicant['uuid'])){
                    $this->basic_model->update_records('users', ['status' => 0, 'token' => NULL, 'updated_at' => DATE_TIME], ['id' => $applicant['uuid'], 'user_type'=> MEMBER_PORTAL]);
                    $member_details = $this->basic_model->get_row('member', ['id','uuid'], ['uuid' => $applicant['uuid']]);
                    if(!empty($member_details)){
                        $this->basic_model->update_records('member', ['status' => 0, 'updated_date' => DATE_TIME], ['uuid' =>$applicant['uuid']]);
                    }
                }
                if(!empty($selected_template)){
                    $this->Recruitment_applicant_model->send_rejection_email_template_to_email($selected_template,$applicant['applicant_id'], $adminId, $applicant['id']);
                }
            }
            if ($response != '') {
                $applicant_res[] = $response;
            }

        }
        return $applicant_res;
    }

    /**
     * Updating the application status.
     */
    function mark_application_status($data, $adminId,$reqData=null) {

        $application_id = $data['application_id'];
        $applicant_id = $data['applicant_id'];

        # does the application exist?
        $this->load->model('Recruitment_applicant_model');
        $result = $this->Recruitment_applicant_model->get_application_details($application_id);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Application does not exist anymore."];
            return '';
        }

        // Check applicant have selected stage
        $applicantStatus = $this->get_applicant_status_by_id($applicant_id, $application_id);
        if (isset($applicantStatus) == false || empty($applicantStatus) == true) {
            return '';
        }

        // Check applicant have selected stage
        $applicantVerified = $this->get_applicantion_verified_docref($applicant_id, $application_id);

        $applicantContractVerified = $this->check_emp_contract_signed($applicant_id, $application_id,$reqData);

        if ((isset($applicantVerified) == false || empty($applicantVerified) == true
            || !$applicantContractVerified) && $data["application_process_status"] == 7) {
            return '';
        }

        $dataToBeUpdated = [
            'status' =>$data['application_process_status']
        ];

        # updating status
        $upd_data["application_process_status"] = $data['application_process_status'];
        //fetching prev application process status
        $upd_data["prev_application_process_status"] = $result['data']->application_process_status;
        $upd_data["updated"] = DATE_TIME;
        $this->basic_model->update_records("recruitment_applicant_applied_application", $upd_data, ["id" => $application_id]);

        //if hired means update recruitment status as completed
        if($data["application_process_status"] == 7){
            $applicant_status['status'] = 3;
            $this->basic_model->update_records("recruitment_applicant", $applicant_status, ["id" => $applicant_id]);
        }
        // Update history
        $this->Recruitment_applicant_model->updateHistory($result, $dataToBeUpdated, $adminId);

        // Adding a log entry
        $msg = "Application status is updated successfully";
        $response = ['status' => true, 'msg' => $msg];

        $this->Recruitment_applicant_model->add_create_update_application_log($upd_data, $msg, $adminId, $application_id);

        return $result;
    }

    /**
     * Check applicant is hired / rejected
     * return array
     */
    function get_applicant_status_by_id($applicant_id, $application_id) {

        $select_column = array("raaa.*");

        $this->db->select($select_column);
        $this->db->from('tbl_recruitment_applicant_applied_application as raaa');
        $this->db->where('raaa.applicant_id', $applicant_id);
        $this->db->where('raaa.id', $application_id);
        $this->db->where_not_in('raaa.application_process_status', [7]);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        return $result;
    }

    /**
     * Check applicant is hired / rejected
     * return array
     */
    function get_applicantion_verified_docref($applicant_id, $application_id) {

        $select_column = array("raaa.*");

        $this->db->select($select_column);
        $this->db->from('tbl_recruitment_applicant_applied_application as raaa');
        $this->db->where('raaa.applicant_id', $applicant_id);
        $this->db->where('raaa.id', $application_id);
        $this->db->where('raaa.is_reference_marked', 1);
        $this->db->where('raaa.is_document_marked', 1);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        return $result;
    }

     /*
     * its use for get email template options
     * return type Array object
     */

    public function get_template_option($reqData) {
        try{
            $this->db->select(["et.name as label", 'et.id as value','et.subject']);
            $this->db->from(TBL_PREFIX . 'email_templates as et');
            $where = ['et.archive' => 0];
            if($this->Common_model->check_is_bu_unit($reqData)) {
               $where = ['et.archive' => 0,'et.bu_id' => $reqData->business_unit['bu_id']];
            }
            $this->db->where($where);
            $this->db->order_by('et.id', 'ASC');
             # Throw exception if db error occur
             if (!$query = $this->db->get()) {               
                $db_error = $this->db->error();
                throw new Exception('Something went wrong!');
            }
            return $query->num_rows() > 0 ? $query->result_array() : [];
        }catch(\Exception $e){
            return array('status' => false, 'error' => 'Something went wrong');            
        }
       
    }

    /** Check Employment contract available or signed */
    function check_emp_contract_signed($applicant_id, $application_id,$reqData=NULL) {
        $reqSignData = new stdclass();
        $reqSignData->applicant_id = $applicant_id;
        $reqSignData->application_id = $application_id;
        $reqSignData->page = 0;
        $reqSignData->pageSize = 1;

        $status = true;

        $this->load->model('Recruitment_applicant_docusign_model');

        $docu_data = $this->Recruitment_applicant_docusign_model->get_docusign_document_list($reqSignData, false,$reqData);

        if(empty($docu_data['data'])) {
            $status = false;
        } else {
            foreach($docu_data['data'] as $doc_data) {
                if($doc_data->signed_status == 0) {
                    return false;
                }
            }
        }
        return $status;
    }

}
