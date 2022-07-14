<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property-read \Recruitment_jobs_model $Recruitment_jobs_model
 */
class RecruitmentReferenceData extends MX_Controller {

    use formCustomValidation;

    function __construct() {

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Recruitment_reference_data_model','reference_model');
        $this->load->model('Recruitment_applicant_model');
        $this->load->library('UserName');
        $this->load->model('Basic_model');
        $this->form_validation->CI = & $this;
        $this->loges->setLogType('recruitment');
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

    public function get_ref_master_list() {
        $reqData = request_handler();
        $data = [];
        $response = $this->basic_model->get_record_where('reference_data_type', $column = array('title as label', 'id as value'), $where = array('archive' => '0'));
        $data['data_type'] = isset($response) && !empty($response) ? $response : array();
        echo json_encode(array('status' => true, 'data' => $data));
    }

    public function save_ref_data() {
        $request = request_handler();
        $post_data = (array) $request->data;
        #pr($post_data);

        $ref_id = $post_data["ref_id"] ?? "";
        $type = $post_data['type'];
        $display_name = $post_data['display_name'];
        $input_data = array('ref_id'=>$ref_id,'type'=> $type);
        $input_json = json_encode($input_data);


        if (!empty($post_data)) {
            $validation_rules = array(
                array('field' => 'type', 'label' => 'Type', 'rules' => 'required'),
                array('field' => 'code', 'label' => 'Code'),
                array('field' => 'display_name', 'label' => 'Display Name','rules' => 'callback_check_display_name_already_exist['. $input_json . ']'),

            );

            $this->form_validation->set_data($post_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $operation = $post_data['operation'];
                $ocs_row = $this->reference_model->save_ref_data($post_data);
                /* logs */
                if ($operation == 'E')
                    $title = "Edit Reference Data: " . $ocs_row['ref_id'];
                else
                    $title = "Add Reference Data: " . $ocs_row['ref_id'];

                $this->loges->setTitle($title);
                $this->loges->setUserId($request->adminId);
                $this->loges->setDescription(json_encode($post_data));
                $this->loges->setCreatedBy($request->adminId);
                $this->loges->createLog();
                $return = array('status' => true, 'msg' => $ocs_row['msg'], 'id' => $ocs_row['ref_id']);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
        }
    }

    function check_display_name_already_exist($name, $input_json) {

        $input_array = json_decode($input_json);
       // print_r($input_array);exit();
        $type = $input_array->type;
        $id = $input_array->ref_id;
       // echo "Type222=".$input_array->type;exit();
        if (!empty($name)) {
            $res = $this->reference_model->check_display_name_already_exist($name, $id,$type);

            if (!empty($res)) {
                $this->form_validation->set_message('check_display_name_already_exist', 'Display name is already exist');
                return false;
            } else {

                return true;
            }
        } else {
            $this->form_validation->set_message('check_display_name_already_exist', 'Display name is required');
            return false;
        }

        return true;
    }

    public function get_all_ref_data() {
        $reqData = $reqData1 = request_handler();
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $result = $this->reference_model->get_all_ref_data($reqData);
            echo json_encode($result);
        }
    }

    /**
     * Get all reference list by applicant id
     */
    function get_referece_list_by_id() {
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

                $list = $this->reference_model->get_applicant_reference($data);
                
                if(!empty($list[0]->status)){
                    // get reference ver
                    $where = ['applicant_id' => $applicant_id, 'id' => $application_id];
                    if($this->Common_model->check_is_bu_unit($reqData)){
                        $where = ['applicant_id' => $applicant_id, 'id' => $application_id,'bu_id' =>  $reqData->business_unit['bu_id']];
                    }
	                $res = $this->basic_model->get_record_where('recruitment_applicant_applied_application', ['is_reference_marked', 'application_process_status'], ['applicant_id' => $applicant_id, 'id' => $application_id]);
	                $is_reference_marked = false;

	                $app_pros_hired_status = false;
	                if (isset($res) == true && isset($res[0]->is_reference_marked) == true) {
	                    $is_reference_marked = $res[0]->is_reference_marked == 0 ? false : true;
	                    $app_pros_hired_status = $res[0]->application_process_status == 7 || $res[0]->application_process_status == 8 ? true : false;
	                }

	                // Get total rows inserted count
	                $ref_row = $this->db->query('SELECT COUNT(*) as count from tbl_recruitment_applicant_reference where applicant_id = '.$this->db->escape_str($applicant_id, true).' AND archive = 0')->row_array();
	                $record_count = intVal($ref_row['count']);

                    $response = ['status' => true, 'msg' => "List fetched successfully", 'rows' => $list, 'count' => $record_count, 'record_count' => $record_count, 'is_reference_marked' => $is_reference_marked, 'app_pros_hired_status' => $app_pros_hired_status ];
                }else{
                    $response = ['status' => true, 'msg' => "List fetched successfully", 'rows' => [], 'count' => 0, 'record_count' => 0 ];
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
     * Get all reference list by applicant id
     */
    function create_update_reference() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $data = $reqData->data;

            $validation_rules = array(
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', 'errors'=> [ 'required' => 'Applicant Id is missing']),
                array('field' => 'phone', 'label' => 'Phone', 'rules' => 'required|callback_phone_number_check[phone,required, Enter valid phone number.]'),
                array('field' => 'email', 'label' => 'Email', 'rules' => 'valid_email'),
                array('field' => 'status', 'label' => 'Status', 'rules' => 'required'),
            );

            $this->form_validation->set_data((array) $data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $applicant_id = $data->applicant_id;
                $list = $this->reference_model->create_update_reference($data, $reqData->adminId);

                $id = $data->id ?? 0;
                if ($id != '' && $id != 0) {
                    $msg = 'updated';
                } else {
                    $msg = 'created';
                }
                $response = ['status' => true, 'msg' => "Reference ".$msg." successfully" ];
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
     * Get reference data
     */
    function get_reference_data_by_id() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $data = $reqData->data;

            $validation_rules = array(
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', 'errors'=> [ 'required' => 'Applicant Id is missing']),
                array('field' => 'reference_id', 'label' => 'Reference Id', 'rules' => 'required', 'errors'=> [ 'required' => 'Reference Id is missing'] ),
            );

            $this->form_validation->set_data((array) $data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $reference_id = $data->reference_id;
                $applicant_id = $data->applicant_id;
                $list = $this->reference_model->get_reference_data_by_id($applicant_id, $reference_id, $reqData->adminId);
                $response = ['status' => true, 'msg' => "Reference list fetched successfully", 'data' => $list ];
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
     * Mark reference as archived.
     */
    public function archive_reference() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $data = $reqData->data;

            $validation_rules = array(
                array('field' => 'reference_id', 'label' => 'Reference Id', 'rules' => 'required', 'errors'=> [ 'required' => 'Reference Id is missing'] ),
            );

            $this->form_validation->set_data((array) $data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $reference_id = $data->reference_id;
                $list = $this->reference_model->archive_reference($reference_id, $reqData->adminId);
                $response = ['status' => true, 'msg' => "Reference archived successfully"];
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
     * Mark reference as status.
     */
    public function update_reference_status() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $data = $reqData->data;

            $validation_rules = array(
                array('field' => 'reference_id', 'label' => 'Reference Id', 'rules' => 'required', 'errors'=> [ 'required' => 'Reference Id is missing'] ),
                array('field' => 'status', 'label' => 'Status', 'rules' => 'required'),
            );

            $this->form_validation->set_data((array) $data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $reference_id = $data->reference_id;
                $status = $data->status;
                $list = $this->reference_model->update_reference_status($reference_id, $status, $reqData->adminId);
                $response = ['status' => true, 'msg' => "Reference status updated successfully"];
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
     * Mark reference as status.
     */
    public function mark_reference_status() {
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
                $list = $this->reference_model->update_reference_marked($applicant_id, $application_id, $status, $reqData->adminId);
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
}
