<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read \Templates_model $Templates_model
 */
class Templates extends MX_Controller {

    use formCustomValidation;

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Templates_model');
        $this->form_validation->CI = &$this;
        $this->loges->setLogType('imail');
        $this->load->model('../../common/models/List_view_controls_model');
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
        
    }

    public function details() {
        $request = request_handler();

        if (!empty($request->data->id)) {
            $x = (array) $this->Templates_model->get_template_details($request->data->id);
            $x["existing_attachment"] = $this->Templates_model->get_template_attachment($request->data->id);
            $response = ["status" => true, "data" => $x];
        } else {
            $response = ["status" => false, "error" => "Please provide template id"];
        }

        echo json_encode($response);
    }

    function check_template_name_already_exist($template_name, $templateId) {
        if (!empty($template_name)) {
            $res = $this->Templates_model->check_template_name_already_exist($template_name, $templateId);

            if (!empty($res)) {
                $this->form_validation->set_message('check_template_name_already_exist', 'Template name is already exist');
                return false;
            } else {

                return true;
            }
        } else {
            $this->form_validation->set_message('check_template_name_already_exist', 'Template name is required');
            return false;
        }

        return true;
    }

    public function create() {
        $request = request_handler();
        $template_data = (array) obj_to_arr($request->data);

        $tempId = $template_data["id"] ?? "";
        $this->form_validation->set_data($template_data)->set_rules([
            [
                'field' => 'name', 'label' => 'Name', 'rules' => 'callback_check_template_name_already_exist[' . $tempId . ']',
            ],
            ['field' => 'content', 'label' => 'Content', 'rules' => 'required'],
            ['field' => 'from', 'label' => 'From', 'rules' => 'required'],
            ['field' => 'subject', 'label' => 'Subject', 'rules' => 'required'],
        ]);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $results = array('status' => false, 'error' => implode(', ', $errors));
        } else {
            $results = $this->Templates_model->create($template_data, $request->adminId);

            if ($template_data["is_edit"]) {
                $title = "New Template Created : " . $results['template_id'];
            } else {
                $title = "Template Updated : " . $results['template_id'];
            }

            $this->loges->setTitle($title);
            $this->loges->setUserId($results['template_id']);
            $this->loges->setDescription(json_encode($request->data));
            $this->loges->setCreatedBy($request->adminId);
            $this->loges->createLog();
        }
        echo json_encode($results);
    }

    public function archive_template() {
        $request = request_handler();

        if (!empty($request->data->template_id)) {
            $x = $this->Templates_model->archive_template($request->data->template_id);
            $response = ["status" => true, "msg" => "Archive Successfully"];
        } else {
            $response = ["status" => false, "error" => "Please provide template id"];
        }

        echo json_encode($response);
    }

    public function template_listing() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $adminId = $reqData->adminId;
            $data = $reqData->data;
            $result = $this->Templates_model->listing_template($data, $adminId);
            echo json_encode($result);
        }
    }
    /**
     * Function for new template listing with DataTableListView
     */
    public function template_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $adminId = $reqData->adminId;
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);            
            $data = $reqData->data;            
            $filter_condition = str_replace(['folder', 'status=Archive', 'status!=Archive'], ['et.folder', 'et.archive=1', 'et.archive!=1'], $filter_condition);
            if (empty($filter_condition)) {
                $filter_condition = 'et.archive!=1';
            }
            $result = $this->Templates_model->listing_template($data, $adminId, $filter_condition);
            echo json_encode($result);
        }
    }

    function get_email_template_name() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $result = $this->Templates_model->get_email_template_name($reqData);
            echo json_encode(['status' => true, 'data' => $result]);
        }
    }

    function get_dynamic_email_field_name() {
        $reqData = request_handler();

        $reqData = $reqData->data;
        $result = $this->Templates_model->get_dynamic_email_field_name($reqData);
        echo json_encode(['status' => true, 'data' => $result]);
    }

    function upload_template_attachment_tempory() {
        $data = request_handlerFile();


        if (!empty($_FILES)) {
            $config['upload_path'] = ARCHIEVE_DIR; // user here constact for specific path
            $config['input_name'] = 'file';
            $config['directory_name'] = '';
            $config['allowed_types'] = 'jpg|jpeg|png|xlx|xls|doc|docx|pdf|odt|rtf';
            if (is_array($_FILES['file']['name'])) {
                $files = $_FILES;
                $_FILES = array();
                $response = [];
                foreach($files['file']['name'] as $i => $file_name) {
                    if ($files['file']['error'][$i] == 0) {
                        $_FILES['file'] = [
                                            'name' => $files['file']['name'][$i],
                                            'type' => $files['file']['type'][$i],
                                            'tmp_name' => $files['file']['tmp_name'][$i],
                                            'error' => $files['file']['error'][$i],
                                            'size' => $files['file']['size'][$i]
                        ];
                        $is_upload = do_upload($config); // upload file
                        // check here file is uploaded or not return key error true
                        if (isset($is_upload['error'])) {
                            $response[] = array('status' => false, 'error' => strip_tags($is_upload['error']));
                        } else {
                            $response[] = array('status' => true, 'filename' => $is_upload['upload_data']['file_name']);
                        }
                    } else {
                        $response[] = array('status' => false, 'error' => 'Please select a file to upload');
                    }
                }
                $return = array('status' => true, 'response' => $response);
            } else {
                $is_upload = do_upload($config); // upload file
                if (isset($is_upload['error'])) {
                    $return = array('status' => false, 'error' => strip_tags($is_upload['error']));
                } else {
                    $return = array('status' => true, 'filename' => $is_upload['upload_data']['file_name']);
                }
            }
        } else {
            $return = array('status' => false, 'error' => 'Please select a file to upload');
        }

        echo json_encode($return);
        exit();
    }

    function get_email_template_with_attachment_option() {
        request_handler();

        $result = $this->Templates_model->get_email_template_with_attachment_option();
        echo json_encode(['status' => true, 'data' => $result]);
    }

}
