<?php

defined('BASEPATH') OR exit('No direct script access allowed');

//require_once APPPATH . 'traits/formCustomValidation.php';
class RecruitmentDashboard extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Recruitment_model');
        $this->load->model('Recruitment_dashboard_model');
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
        $reqData = request_handler();
        $allotedData = $reqData->data;
        if (!empty($allotedData)) {
            #pr($reqData);
            $update_dept = $this->Recruitment_model->update_alloted_department($allotedData);
            $response = array('status' => true, 'update_dept' => $update_dept);
            echo json_encode($response);
        }
    }

    public function get_applicant_list() {
        $reqData = $reqData1 = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $reqData = json_decode($reqData->data);
            $result = $this->Recruitment_model->get_applicant_list($reqData);
            echo json_encode($result);
        }
    }

    public function get_recruiter_listing_for_create_task() {
        $reqData = $reqData1 = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $result = $this->Recruitment_model->get_recruiter_listing_for_create_task($reqData);
            echo json_encode($result);
        }
    }

    public function archive_department() {
        $reqData = request_handler('delete_recruitment');
        $reqData = $reqData->data;

        if (!empty($reqData->id)) {
            $this->basic_model->update_records('recruitment_department', ['archived' => 1], ['id' => $reqData->id]);

            echo json_encode(['status' => true]);
        }
    }

    public function get_task_list_dashboard() {
        $reqData = $reqData1 = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $result = $this->Recruitment_dashboard_model->get_task_list_dashboard($reqData);
            echo json_encode($result);
        }
    }

    public function get_latest_action() {
        $reqData = $reqData1 = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $result = $this->Recruitment_dashboard_model->get_latest_action($reqData);
            echo json_encode($result);
        }
    }

    public function get_pay_scale_approval_applicant_list() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {

            $result = $this->Recruitment_dashboard_model->pay_scale_approval_applicant_list($reqData);
            echo json_encode($result);
        }
    }

    public function get_new_assigned_applicant() {
        $reqData = request_handler('access_recruitment');
        //pr($reqData);
        if (!empty($reqData->data)) {
            $result = $this->Recruitment_dashboard_model->get_new_assigned_applicant($reqData);
            echo json_encode($result);
        }
    }

    public function get_pay_scale_approval_work_area_options() {
        $result = $this->Recruitment_dashboard_model->pay_scale_approval_work_area_options();
        echo json_encode(['status' => true, 'data' => $result]);
    }

    public function update_pay_scale_approval_applicant_work_area() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'work_area_id', 'label' => 'work area', 'rules' => 'callback_check_multiple_work_area_details[' . json_encode($reqData) . ']'),
            );
            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                // $data=$reqData->data;
                $result = $this->Recruitment_dashboard_model->update_pay_scale_approval_applicant_work_area($reqData);
                echo json_encode($result);
                exit();
            } else {
                $errors = $this->form_validation->error_array();
                $str = implode('<br/> ', $errors);
                echo json_encode(array('status' => false, 'msg' => $str));
                exit();
            }
        }
    }

    public function check_multiple_work_area_details($data, $reqData) {
        $result = $this->Recruitment_dashboard_model->multiple_work_area_details($reqData);
        if ($result > 0) {
            $this->form_validation->set_message('check_multiple_work_area_details', 'Please select different work area.');
            return false;
        }
    }

    public function save_approved_pay_scale_approval_applicant() {
        $reqData = request_handler('access_recruitment');
        $approval_id = $reqData->data->id;

        $result = array('status' => false);
        if ($approval_id > 0 && $reqData->adminId > 0) {
            $validate_approved = $this->Recruitment_dashboard_model->get_pay_scale_approval_status($approval_id);
            if ($validate_approved == 1) {
                echo json_encode(['status' => false, 'data' => [], 'error' => 'this pay scale all ready approved!']);
                exit;
            } else {
                $result = $this->Recruitment_dashboard_model->save_approved_pay_scale_approval_applicant($reqData);
                if ($result) {
                    echo json_encode(['status' => true, 'data' => [], 'error' => 'pay scale approved successfully.']);
                    exit;
                } else {
                    echo json_encode(['status' => false, 'data' => [], 'error' => 'Something went wrong.']);
                    exit;
                }
            }
        } else {
            echo json_encode(['status' => false, 'data' => [], 'error' => 'Something went wrong.']);
            exit;
        }
    }

    public function save_approved_pay_scale_approval_applicant_relevant_notes() {
        $reqData = request_handler('access_recruitment');
        $approval_id = $reqData->data->id;
        $result = array('status' => false);
        if ($approval_id > 0) {
            $result = $this->Recruitment_dashboard_model->save_approved_pay_scale_approval_applicant_relevant_notes($reqData);

            echo json_encode(['status' => true]);
            exit;
        } else {
            echo json_encode(['status' => false, 'data' => [], 'error' => 'Something went wrong.']);
            exit;
        }
    }

    public function get_latest_status_updates_notification() {
        $reqData = $reqData1 = request_handler('access_recruitment_admin');
        if (!empty($reqData->adminId)) {
            $result = $this->Recruitment_dashboard_model->get_latest_status_updates_notification();
            echo json_encode($result);
        }
    }

    public function get_applicant_recruitment_dashboard_graph() {
        $reqData = request_handler('access_recruitment');
        $rtype = $reqData->data->mode;
        $result = [];
        if (!empty($reqData)) {
            $type = isset($reqData->data->type) && !empty($reqData->data->type) ? $reqData->data->type : 'week';
            if ($rtype == 'all') {
                $resultRecruitment = $this->Recruitment_dashboard_model->get_applicant_recruitment_dashboard_graph($type);
                $resultProspective = $this->Recruitment_dashboard_model->get_applicant_recruitment_dashboard_prospective_graph($type);
                $resultHired = $this->Recruitment_dashboard_model->get_applicant_recruitment_dashboard_applicant_hired_graph($type);
                $result = ['prospective_app' => $resultProspective, 'hired_app' => $resultHired, 'recruitment_app' => $resultRecruitment];
            } else if ($rtype == 'prospective_app') {
                $result['prospective_app'] = $this->Recruitment_dashboard_model->get_applicant_recruitment_dashboard_prospective_graph($type);
            } else if ($rtype == 'hired_app') {
                $result['hired_app'] = $this->Recruitment_dashboard_model->get_applicant_recruitment_dashboard_applicant_hired_graph($type);
            } else if ($rtype == 'recruitment_app') {
                $result['recruitment_app'] = $this->Recruitment_dashboard_model->get_applicant_recruitment_dashboard_graph($type);
            }
            echo json_encode(['status' => true, 'data' => $result]);
        } else {
            echo json_encode(['status' => false, 'data' => [], 'msg' => 'Something went wrong.']);
            exit;
        }
    }

    public function action_updates_notification() {
        $reqData = $reqData1 = request_handler('access_recruitment_admin');
        if (!empty($reqData)) {
            $result = $this->Recruitment_dashboard_model->action_updates_notification($reqData);
            echo json_encode($result);
        } else {
            echo json_encode(['status' => false, 'data' => [], 'msg' => 'Something went wrong.']);
            exit;
        }
    }

    public function get_communication_log() {
        $reqestData = request_handler('access_recruitment');
        if (!empty($reqestData->data)) {
            $res = $this->Recruitment_dashboard_model->get_communication_log($reqestData->data);
            echo json_encode($res);
        }
    }

    public function export_communication_log() {
        $request = request_handler('access_recruitment');
        $csv_data = $request->data->userSelectedList;
        # pr($csv_data);
        require_once APPPATH."/third_party/PHPExcel.php";
        $object = new PHPExcel();
        if (!empty($csv_data)) {

            $object->setActiveSheetIndex(0);
            $object->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'From');
            $object->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'To');
            $object->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Type');
            $object->getActiveSheet()->setCellValueByColumnAndRow(3, 1, 'Title');
            $object->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'Sent');
            $object->getActiveSheet()->setCellValueByColumnAndRow(5, 1, 'Description');


            $var_row = 2;
            foreach ($csv_data as $one_row) {
                if ($one_row->log_type == 1) {
                    $description = '';
                } else if ($one_row->log_type == 2) {
                    #1 sms/2 - email/3 - phone
                    $x = $one_row->communication_text ?? '';
                    if ($x != '') {
                        $json_ary = json_decode($x);
                        $data_ = $json_ary->data;
                    }
                    $fullname = $data_->fullname ?? '';
                    $task_date = $data_->task_date ?? '';
                    $task_time = $data_->task_time ?? '';
                    $device_pin = $data_->device_pin ?? '';
                    $description = "Hello, $fullname ,Your task schedule at $task_date  $task_time Please click below to confirm. Your quiz test credential.Pin : $device_pin";
                } else if ($one_row->log_type == 3) {
                    $description = '';
                }
                $object->getActiveSheet()->SetCellValue('A' . $var_row, $one_row->from);
                $object->getActiveSheet()->SetCellValue('B' . $var_row, $one_row->to_email);
                $object->getActiveSheet()->SetCellValue('C' . $var_row, $one_row->log_type);
                $object->getActiveSheet()->SetCellValue('D' . $var_row, $one_row->title);
                $object->getActiveSheet()->SetCellValue('E' . $var_row, $one_row->created);
                $object->getActiveSheet()->SetCellValue('F' . $var_row, str_replace(['&nbsp;'], [" "], strip_tags($one_row->communication_text)));
                $var_row++;
            }

            $object->setActiveSheetIndex()
                    ->getStyle('A1:D1')
                    ->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'C0C0C0:')
                                )
                            )
            );

            $object->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
            $object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
            $filename = time() . '_communication_log' . '.csv';
            $object_writer->save(ARCHIEVE_DIR . '/' . $filename);
            echo json_encode(array('status' => true, 'csv_url' => $filename));
            exit();
        } else {
            echo json_encode(array('status' => false, 'error' => 'No record to export'));
            exit();
        }
    }

}
