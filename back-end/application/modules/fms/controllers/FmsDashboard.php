<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class FmsDashboard extends MX_Controller {

    use formCustomValidation;

    function __construct() {
        parent::__construct();
        $this->load->model('Fms_model');
        $this->load->model('Basic_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
        $this->loges->setLogType('fms');
        $this->load->model('common/List_view_controls_model');
        $this->load->model('sales/Feed_model');
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

    public function create_case() {
        $reqData = request_handler();
        require_once APPPATH . 'Classes/fms/Fms.php';
        $objFmsCase = new FmsClass\Fms();
        $req_data = (array) $reqData->data;

        if (!empty($req_data)) {
            $validation_rules = array(
                array('field' => 'againstDetail[]', 'label' => 'Notes Description', 'rules' => "callback_phone_number_check[againstPhone]"),
                array('field' => 'initiator_phone', 'label' => 'Initiator Details', 'rules' => "callback_phone_number_check['initiator_phone,reuired']"),
                array('field' => 'event_date', 'label' => 'Event Date', 'rules' => 'required'),
                array('field' => 'title', 'label' => 'Reason Title', 'rules' => 'required'),
                array('field' => 'notes_title', 'label' => 'Notes Title', 'rules' => 'required'),
                array('field' => 'CaseCategory', 'label' => 'Case Category', 'rules' => 'required'),
                array('field' => 'description', 'label' => 'Reason Description', 'rules' => 'required'),
                array('field' => 'notes', 'label' => 'Notes Description', 'rules' => 'required'),
                array('field' => 'completeAddress[]', 'label' => 'Address', 'rules' => 'callback_check_address|callback_postal_code_check[postal]'),
            );

            $this->form_validation->set_data($req_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $rows = $objFmsCase->create_case($reqData);
                //logs
                $case_id = $rows['case_id'];
                $this->loges->setCreatedBy($reqData->adminId);
                $this->loges->setUserId($case_id);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->setTitle('FMS Case created : Case Id ' . $case_id);
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

    public function create_update_feed() {
        $reqData = request_handler('access_fms');
        $adminId = $reqData->adminId;

        if (empty($reqData->data)) {
            echo json_encode(['status' => false, 'error' => system_msgs('something_went_wrong')]);
            die;
        }

        if(!empty($reqData)) {
            $data = object_to_array($reqData->data);
            $validation_rules = array(
                array('field' => 'alert_type', 'label' => 'Initiator Details', 'rules' => 'reuired'),
                array('field' => 'initiator_phone', 'label' => 'Initiator Details', 'rules' => "callback_phone_number_check['initiator_phone,reuired']"),
                array('field' => 'InitiatorCategory', 'label' => 'Initiator Category', 'rules' => 'required'),
                array('field' => 'FeedCategory', 'label' => 'Feedback Category', 'rules' => 'required'),
                array('field' => 'address', 'label' => 'Address', 'rules' => 'callback_check_string_google_address_is_valid'),
            );

            if(!empty($data['initCatOption']) && $data['initCatOption'] == 'init_member_of_public' || $data['initCatOption'] == 'init_hcm_general') {
                $validation_rules[] =
                       array('field' => 'initFirstName', 'label' => 'Initiator First Name', 'rules' => 'required');
            }

            if(!empty($data['initCatOption']) && $data['initCatOption'] == 'init_member_of_public') {
                $validation_rules[] =
                    array('field' => 'initEmail', 'label' => 'Initiator Email', 'rules' => 'valid_email|required');
                    $validation_rules[] = array('field' => 'initPhone', 'label' => 'Initiator Phone Number', 'rules' => 'callback_phone_number_check["initPhone, required"]');
            }

            if(!empty($data['agCatOption']) && $data['agCatOption'] == 'aga_member_of_public' || $data['agCatOption'] == 'aga_hcm_general') {
                $validation_rules[] =
                       array('field' => 'agFirstName', 'label' => 'Against First Name', 'rules' => 'required');
            }

            if(!empty($data['agCatOption']) && $data['agCatOption'] == 'aga_member_of_public') {
                $validation_rules[] =
                    array('field' => 'agEmail', 'label' => 'Against Email', 'rules' => 'valid_email|required');
                    $validation_rules[] = array('field' => 'agPhone', 'label' => 'Against Phone Number', 'rules' => 'callback_phone_number_check["agPhone, required"]');
            }


            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);
            if ($this->form_validation->run()) {
                $result = $this->Fms_model->create_update_feed($data, $adminId);

            }else {
                $errors = $this->form_validation->error_array();
                $result = array('status' => false, 'error' => implode(', ', $errors));
            }

            if(!empty($result)) {
                echo json_encode($result);
            }
        }

    }

    //Load address for feedback screen
    public function get_address_for_fms() {
        $reqData = request_handler('access_fms');
        if (!empty($reqData->data)) {
            $response = $this->Fms_model->get_address_for_fms($reqData->data);
            echo json_encode($response);
        }
    }

     /**
     * fetches the next number in creating feedback
     */
    public function get_next_feedback_id() {
        $reqData = request_handler('access_fms');
        if (!empty($reqData->data)) {
            $response = $this->Fms_model->get_next_feedback_id();
            echo json_encode($response);
        }
        exit(0);
    }

    function check_address($address) {
        #pr($address);
        if (!empty($address)) {
            if (empty($address->street)) {
                $this->form_validation->set_message('check_address', 'Address can not be empty');
                return false;
            } if (empty($address->state)) {
                $this->form_validation->set_message('check_address', 'State can not be empty');
                return false;
            } if (empty($address->postal)) {
                $this->form_validation->set_message('check_address', 'Pincode can not be empty');
                return false;
            }
            if (!isset($address->suburb)   ) {
                $this->form_validation->set_message('check_address', 'Suburb can not be empty');
                return false;
            }
        } else {
            $this->form_validation->set_message('check_address', 'Address can not empty');
            return false;
        }
        return true;
    }



    public function get_fms_cases() {
        $reqData = $reqData1 = request_handler('access_fms');
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $result = $this->Fms_model->get_fms_cases($reqData);
            //pr($result);
            if (!empty($result) && isset($result['data'][0]) && $result['data'][0]->fms_type == 1) {
                check_permission($reqData1->adminId, 'incident_fms');
            }
            echo json_encode($result);
        }
    }

    //Action for getting feedback details
    public function get_fms_feedback_details() {

        $reqData = $reqData1 = request_handler('access_fms');

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $result = $this->Fms_model->get_fms_feedback_details($reqData->id);
            if (!empty($result) && isset($result['data']->fms_type) && $result['data']->fms_type == 1) {
                check_permission($reqData1->adminId, 'incident_fms');
            }
            echo json_encode($result);
        }
    }

    public function get_fms_feedback() {
        $reqData = $reqData1 = request_handler('access_fms');

        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            if (!empty($filter_condition)) {
                $filter_condition = str_replace(['status','feedback_id','created', 'event_date', 'updated'],
                    ['tbl_fms_feedback.status', 'tbl_fms_feedback.feedback_id',
                     'tbl_fms_feedback.created', 'tbl_fms_feedback.event_date', 'tbl_fms_feedback.updated'], $filter_condition);
            }
        }
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $result = $this->Fms_model->get_fms_feedback($reqData, $filter_condition);

            if (!empty($result) && isset($result['data'][0]) && $result['data'][0]->fms_type == 1) {
                check_permission($reqData1->adminId, 'incident_fms');
            }
            echo json_encode($result);
        }

    }


    public function get_member_feedback_list() {
        $reqData = $reqData1 = request_handler('access_fms');

        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            if (!empty($filter_condition)) {
                $filter_condition = str_replace(['status','feedback_id'], ['tbl_fms_feedback.status',
                    'tbl_fms_feedback.feedback_id'], $filter_condition);
            }
        }
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $result = $this->Fms_model->get_member_feedback_list($reqData, $filter_condition);

            echo json_encode($result);
        }

    }

    //Archive feedback
    public function fms_archive_feedback() {

        $reqData = request_handler('access_fms');
        $adminId = $reqData->adminId;
        if (!empty($reqData->data)) {

            $reqData = (array) $reqData->data;
            $result = $this->Fms_model->fms_archive_feedback($reqData,  $adminId);

            echo json_encode($result);
        }
    }

    public function get_case_detail() {
        $reqData = $reqData1 = request_handler('access_fms');

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $result = $this->Fms_model->get_case_detail($reqData);
            if (!empty($result) && isset($result['data']->fms_type) && $result['data']->fms_type == 1) {
                check_permission($reqData1->adminId, 'incident_fms');
            }
            echo json_encode($result);
        }
    }

    public function update_reason_notes() {
        $request = request_handler('update_fms');
        if (!empty($request->data)) {
            $reqData = $request->data;
            $tbl = $reqData->actionTbl;
            $tbl = 'fms_' . $tbl;
            if ($tbl == 'fms_case_notes')
                $string = 'Case';
            else
                $string = 'Reason';

            $msg = $reqData->popName;
            if (!empty($tbl)) {
                require_once APPPATH . 'Classes/fms/Fms_log.php';
                $objFmsLog = new FmsLogClass\Fms_log();

                $case_reason = array('caseId' => $reqData->caseId,
                    'title' => $reqData->title,
                    'description' => $reqData->description,
                    'created_by' => $request->adminId,
                    'created_type' => 2, //admin
                );

                if ($reqData->mode == 'Add') {
                    $log_title = $string . ' Created';
                    $case_reason['created'] = DATE_TIME;
                    $fms_case_reason_id = $this->Basic_model->insert_records($tbl, $case_reason, $multiple = FALSE);
                    /**/
                    $objFmsLog->setCaseId($reqData->caseId);
                    $objFmsLog->setTitle($log_title);
                    $objFmsLog->setCreated_by($request->adminId);
                    $objFmsLog->setCreated_type(2);
                    $objFmsLog->createFmsLog();
                    echo json_encode(array('status' => true, 'msg' => $msg . ' Add successfully.', 'actionTbl' => $tbl));
                    exit();
                } else {
                    $log_title = $string . ' Updated';
                    $fms_case_reason_id = $reqData->id;
                    $this->basic_model->update_records($tbl, $case_reason, $where = array('id' => $reqData->id));
                    /**/
                    $objFmsLog->setCaseId($reqData->caseId);
                    $objFmsLog->setTitle($log_title);
                    $objFmsLog->setCreated_by($request->adminId);
                    $objFmsLog->setCreated_type(2);
                    $objFmsLog->createFmsLog();
                    echo json_encode(array('status' => true, 'msg' => $msg . ' Updated successfully.', 'actionTbl' => $tbl));
                    exit();
                }
            } else {
                echo json_encode(array('status' => false, 'msg' => 'Something went wrong.'));
                exit();
            }
        }
    }

    public function get_result_array() {
        $reqData = request_handler('access_fms');
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $tbl = $reqData->actionTbl;

            if ($tbl == 'fms_case_docs')
                $result = $this->Basic_model->get_result($tbl, $id_array = array('caseId' => $reqData->caseId), $columns = array('id', 'title', 'filename'), $order_by = array('id', 'DESC'));
            else if ($tbl == 'fms_case_reason')
                $result = $this->Basic_model->get_result('fms_case_reason', $id_array = array('caseId' => $reqData->caseId), $columns = array('id', 'title', 'description', 'created_by', 'created'), $order_by = array('id', 'DESC'));
            else
                $result = $this->Basic_model->get_result('fms_case_notes', $id_array = array('caseId' => $reqData->caseId), $columns = array('id', 'title', 'description', 'created_by', 'created'), $order_by = array('id', 'DESC'));

            echo json_encode(array('status' => true, 'data' => $result));
        }
    }

    public function upload_case_docs() {
        $request = request_handlerFile('update_fms');
        $member_data = (array) $request;

        if (!empty($member_data)) {
            $validation_rules = array(
                array('field' => 'docsTitle', 'label' => 'Title', 'rules' => 'required'),
            );

            $this->form_validation->set_data($member_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                if (!empty($_FILES) && $_FILES['myFile']['error'] == 0) {
                    $config['upload_path'] = CASE_UPLOAD_PATH;
                    $config['input_name'] = 'myFile';
                    $config['directory_name'] = $this->input->post('caseId');
                    $config['allowed_types'] = 'jpg|jpeg|png|xlx|xls|doc|docx|pdf';

                    $is_upload = do_upload($config);

                    if (isset($is_upload['error'])) {
                        echo json_encode(array('status' => false, 'error' => strip_tags($is_upload['error'])));
                        exit();
                    } else {
                        $file_name = $is_upload['upload_data']['file_name'];
                        $title = preg_replace('/.[^.s]{3,4}$/', '', $file_name);

                        $insert_ary = array('filename' => $file_name,
                            'caseId' => $this->input->post('caseId'),
                            'title' => $this->input->post('docsTitle'),
                            'created' => DATE_TIME,
                        );
                        $rows = $this->Basic_model->insert_records('fms_case_docs', $insert_ary, $multiple = FALSE);
                        /* Fms separate log */
                        require_once APPPATH . 'Classes/fms/Fms_log.php';
                        $objFmsLog = new FmsLogClass\Fms_log();
                        $objFmsLog->setCaseId($this->input->post('caseId'));
                        $objFmsLog->setTitle('New Case Docs uploaded');
                        $objFmsLog->setCreated_by($request->adminId);
                        $objFmsLog->setCreated_type(2);
                        $objFmsLog->createFmsLog();

                        if (!empty($rows)) {
                            echo json_encode(array('status' => true));
                            exit();
                        } else {
                            echo json_encode(array('status' => false));
                            exit();
                        }
                    }
                } else {
                    echo json_encode(array('status' => false, 'error' => 'Please select a file to upload'));
                    exit();
                }

                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode("\n", $errors));
            }
            echo json_encode($return);
        }
    }

    public function download_selected_file() {
        $request = request_handler('access_fms');
        $responseAry = $request->data;
        $this->load->library('zip');
        $caseId = $responseAry->caseId;
        $download_data = $responseAry->downloadData;
        $this->zip->clear_data();
        $x = '';
        $file_count = 0;
        if (!empty($download_data)) {
            $zip_name = time() . '_' . $caseId . '.zip';
            foreach ($download_data as $file) {
                if (isset($file->is_active) && $file->is_active) {
                    $file_path = CASE_UPLOAD_PATH . $caseId . '/' . $file->filename;
                    $this->zip->read_file($file_path, FALSE);
                    $file_count = 1;
                }
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

    public function move_case_to_incident() {
        $reqData = request_handler('update_fms');
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $caseId = $reqData->id;
            $status = 2;
            $casetype = 1;
            //$result = $this->Basic_model->update_records('fms_case', $columns=array('status'=>$status), array('id'=>$caseId));
            $result = $this->Basic_model->update_records('fms_case', $columns = array('fms_type' => '1'), array('id' => $caseId));
            echo json_encode(array('status' => true, 'casetype' => $casetype));
        }
    }

    public function update_case_status() {
        //$reqData = request_handler('update_fms', 1, 1); //pin checked
        $reqData = request_handler('update_fms');

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $caseId = $reqData->caseId;
            $status = $reqData->status;
            $update_type = $reqData->update_type;
            if ($update_type == 'status') {
                $result = $this->Basic_model->update_records('fms_case', $columns = array('status' => $status,'completed_date'=>DATE_TIME), array('id' => $caseId));
            } else {
                if ($caseId > 0) {
                    $row = $this->Basic_model->get_row('fms_case_category', $columns = array('categoryId'), $id_array = array('caseId' => $caseId));
                    if ($row)
                        $this->Basic_model->update_records('fms_case_category', $columns = array('categoryId' => $status), array('caseId' => $caseId));
                    else
                        $this->Basic_model->insert_records('fms_case_category', array('caseId' => $caseId, 'categoryId' => $status));
                }
            }
            echo json_encode(array('status' => true, 'caseStatus' => $status));
        }
    }

    public function get_link_fms_cases() {
        $reqData = request_handler('access_fms');
        if (!empty($reqData->data)) {
            $reqData = json_decode($reqData->data);
            $result = $this->Fms_model->get_link_fms_cases($reqData);
            echo json_encode($result);
        }
    }

    public function link_unlink_case() {
        $reqData = request_handler('access_fms');
        #pr($reqData);
        if (!empty($reqData->data)) {
            $post_data = $reqData->data;
            if ($post_data->action == 'Unlink') {
                $result = $this->Basic_model->update_records('fms_case_link', $columns = array('archive' => 1), array('link_case' => $post_data->unLinkcaseId, 'caseId' => $post_data->mainCaseId));
                echo json_encode(array('status' => true, 'msg' => 'Case unlink successfully.'));
            } else {
                $data_ary = array('caseId' => $post_data->mainCaseId, 'link_case' => $post_data->unLinkcaseId);
                /* Fms case log */
                require_once APPPATH . 'Classes/fms/Fms_log.php';
                $objFmsLog = new FmsLogClass\Fms_log();
                $objFmsLog->setCaseId($post_data->mainCaseId);
                $objFmsLog->setTitle('Case linked');
                $objFmsLog->setCreated_by($reqData->adminId);
                $objFmsLog->setCreated_type(2);
                $objFmsLog->createFmsLog();
                $result = $this->Basic_model->insert_records('fms_case_link', $data_ary);
                echo json_encode(array('status' => true, 'msg' => 'Case link successfully.'));
            }
        }
    }

    public function get_srch_fms_cases() {
        $reqData = request_handler('access_fms');
        if (!empty($reqData->data)) {
            $reqData = json_decode($reqData->data);
            $result = $this->Fms_model->get_srch_fms_cases($reqData);
            echo json_encode($result);
        }
    }

    public function get_fms_log() {
        $reqData = request_handler('access_fms');
        if (!empty($reqData->data)) {
            $reqData = json_decode($reqData->data);
            $result = $this->Fms_model->get_fms_log($reqData);
            echo json_encode($result);
        }
    }

    public function search_address_book() {
        $reqData = request_handler('access_fms');
        if (!empty($reqData->data)) {
            $result = $this->Fms_model->search_address_book($reqData);
            echo json_encode(array('status' => true, 'data' => $result));
        }
    }

    public function get_contact_list() {
        $reqData = request_handler('access_fms');
        if (!empty($reqData->data)) {
            $result = $this->Fms_model->get_contact_list($reqData);
            echo json_encode(array('status' => true, 'data' => $result));
        }
    }

    public function save_contact() {
        $reqData = request_handler('access_fms');
        if (!empty($reqData->data)) {
            $result = $this->Fms_model->save_contact($reqData);
            echo json_encode($result);
        }
    }

    //Get Refrence data values for FMS feedback form
    public function get_fms_feedback_options() {
        $reqData = request_handler('access_fms');

        if (!empty($reqData->data)) {
            $rows = $this->Fms_model->get_fms_feedback_options();
           echo json_encode($rows);
        }
    }

    /**
     * fetches history of interview
     */

    public function get_field_history() {
        $reqData = request_handler('access_fms');
        if (empty($reqData)) {
            return;
        }
        $items = $this->Fms_model->get_field_history($reqData->data);
        $this->sendResponse($items, 'Success');
    }

    /*
     * fetches all the fms feedback statuses
     */
    function get_fms_feedback_status() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Fms_model->get_fms_feedback_status();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Updating the feedback status.
     */
    public function update_feedback_status() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->Fms_model->update_feedback_status($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Get profile note list for member
     */

    public function get_member_profile_note_feedback_list() {
        $reqData = request_handler('access_fms');

        if (!empty($reqData->data) && !empty($reqData->data->member_id)) {
            $reqData = $reqData->data;
            $result = $this->Fms_model->get_member_profile_note_feedback_list($reqData);
        }else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();

    }

}
