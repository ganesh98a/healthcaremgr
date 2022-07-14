<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read \Recruitment_application_model $Recruitment_applicant_model
 * @property-read \Recruitment_applicant_stages_model $stage_model
 * @package
 */
class RecruitmentApplication extends MX_Controller
{
    use formCustomValidation;

    function __construct()
    {
        parent::__construct();
        $this->load->model('Recruitment_applicant_model');
        $this->load->model('Recruitment_application_model');
        $this->load->model('Recruitment_interview_model');
        $this->load->model('Recruitment_applicant_stages_model', 'stage_model');
        $this->load->library('form_validation');
        $this->load->library('UserName');
        $this->load->model('common/List_view_controls_model');
        $this->form_validation->CI = &$this;
        $this->load->model('Basic_model');
        $this->load->helper('i_pad');
        $this->loges->setLogType('recruitment_applicant');
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
     * fetches all the recruitment stages and sub-stages
     */
    function get_all_recruitment_stages()
    {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $response = $this->Recruitment_applicant_model->get_application_status_for_update();
        } else {
        	$response = array('status' => false, 'error' => 'Somwthing went wrong! Try again');
        }
        echo json_encode($response);
    }

    /**
     * Update application stage to selected stage by application & applicant id
     */
    function update_stage() {
    	$reqData = request_handler('access_recruitment');
    	$data = $reqData->data;
    	$adminId = $reqData->adminId;
    	if (!empty($data)) {
            $validation_rules = array(
                array('field' => 'selected_stage', 'label' => 'Stage', 'rules' => 'required')
            );
            if (empty($data->applications) == true || isset($data->applications) == false) {
            	$validation_rules[] = array('field' => 'applications', 'label' => 'Application', 'rules' => 'required');
            }
            $this->form_validation->set_data((array) $data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run() == true) {
            	$response = $this->Recruitment_application_model->update_application_status($reqData);
            	$requested_count = count($data->applications);
            	$updated_count = count($response);

            	// Msg handling
            	$msg = 'Stage updated successfully for '.$updated_count.' applications out of '.$requested_count;
            	$status = true;
            	if ($requested_count == 1 && $updated_count == 1) {
            		$msg = 'Stage updated successfully';
            		$status = true;
            	}
            	if ($requested_count == 1 && $updated_count == 0) {
            		$msg = 'Selected Status is not match with applications status or applicant has been hired|rejected already';
            		$status = false;
            	}

            	if ($requested_count > 1 && $updated_count == 0) {
            		$msg = 'Selected Status is not match with applications status or applicant has been hired|rejected already';
            		$status = false;
            	}

            	if ($requested_count > 1 && $updated_count < $requested_count) {
            		$msg = 'Stage updated successfully for '.$updated_count.' applications out of '.$requested_count;
            		$status = true;
            	}
            	if ($requested_count > 1 && $updated_count == $requested_count) {
            		$msg = 'Stage updated successfully';
            		$status = true;
            	}
            	if ($status == true) {
            		$return = array('status' => $status, 'data' => $response, 'updated_count' => $updated_count, 'msg' => $msg);
            	} else {
            		$return = array('status' => $status, 'data' => $response, 'updated_count' => $updated_count, 'error' => $msg);
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
     * Update application status to selected stage by application & applicant id
     */
    function update_application_status() {
        $reqData = request_handler('access_recruitment');
        $data = $reqData->data;
        $adminId = $reqData->adminId;
        if (!empty($data)) {
            $validation_rules = array(
                array('field' => 'selected_stage', 'label' => 'Stage', 'rules' => 'required')
            );
            if (empty($data->applications) == true || isset($data->applications) == false) {
                $validation_rules[] = array('field' => 'applications', 'label' => 'Application', 'rules' => 'required');
            }
            $this->form_validation->set_data((array) $data);
            $this->form_validation->set_rules($validation_rules);

            $validateApplicationStatus = false;
            // Msg handling
            $msg = '';
            $status = true;

            // 2- interview , 5-CAB

            if(!empty($data->interview_id) == true &&  isset($data->interview_id)){

                if($data->selected_stage==2 || $data->selected_stage==6){
                    $applicant = $this->Recruitment_interview_model->create_bulk_applicant_interview($data, $adminId);
                    $msg = $applicant['msg'];
                    $status = $applicant['status'];
                    if($applicant['status']){
                        $validateApplicationStatus = true;
                    }
                }else{
                    $msg = 'Please select Interview or CAB status';
                    $applicant['status'] = false;
                    $status = false;
                }

            }else{
                $validateApplicationStatus = true;
            }

            if ($this->form_validation->run() == true && $validateApplicationStatus == true) {
                $response = $this->Recruitment_application_model->update_application_status_by_id($reqData);
                $requested_count = count($data->applications);
                $updated_count = count($response);

                // Msg handling

                if ($requested_count == 1 && $updated_count == 1) {
                    $msg = 'Status updated successfully';
                    $status = true;
                }
                if ($requested_count == 1 && $updated_count == 0) {
                    $msg = 'Verify applicant(s) selected. Applicants Hired (or) pending with Reference check, Document check, and Unsigned Contract cannot be selected';
                    $status = false;
                }

                if ($requested_count > 1 && $updated_count == 0) {
                    $msg = 'Verify applicant(s) selected. Applicants Hired (or) pending with Reference check, Document check, and Unsigned Contract cannot be selected';
                    $status = false;
                }

                if ($requested_count > 1 && $updated_count < $requested_count && $updated_count != 0) {
                    $msg = 'Status updated successfully for '.$updated_count.' applications out of '.$requested_count;
                    $status = true;
                }
                if ($requested_count > 1 && $updated_count == $requested_count && $updated_count != 0) {
                    $msg = 'Status updated successfully';
                    $status = true;
                }
                if ($status == true) {
                    $return = array('status' => $status, 'data' => $response, 'updated_count' => $updated_count, 'msg' => $msg);
                } else {
                    $return = array('status' => $status, 'data' => $response, 'updated_count' => $updated_count, 'error' => $msg);
                }

            } else {
                if($applicant['status']){
                    $errors = $this->form_validation->error_array();
                    $return = array('status' => false, 'error' => implode(', ', $errors));
                }else{
                    $return = array('status' => $status, 'error' => $msg);
                }

            }
        } else {
            $return = array('status' => false, 'error' => 'Something went wrong.');
        }
        echo json_encode($return);
    }

    /**
     * Update status
     * return array
     */
    function migrate_script_for_interview() {
        // api_request_handler();

        $select_column = array("raaa.*");

        $this->db->select($select_column);
        $this->db->from('tbl_recruitment_applicant_applied_application as raaa');
        $this->db->where('raaa.archive', 0);
        $this->db->where_in('raaa.application_process_status', [3,4]);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        $interview_status = 2;
        foreach($result as $key => $application) {
            $id = $application->id;
            $this->basic_model->update_records('recruitment_applicant_applied_application', ['updated' => DATE_TIME, 'application_process_status' => $interview_status ], ['id' => $id ]);
        }
        echo 'Updated Successfully';
    }

    function get_email_template_option() {
        // template options
        $reqData = request_handler('access_recruitment');
        $res= $this->Recruitment_application_model->get_template_option($reqData);
        echo json_encode(["status" => true, "data" => $res]);
    }
}