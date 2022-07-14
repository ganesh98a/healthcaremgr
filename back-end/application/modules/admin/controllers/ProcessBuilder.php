<?php

use classRoles as adminRoles;
use AdminClass as AdminClass;

defined('BASEPATH') or exit('No direct script access allowed');

class ProcessBuilder extends Base_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Process_management_model');
        $this->load->model('admin/Admin_model');
        $this->loges->setLogType('user_admin');
        $this->load->library('form_validation');
        $this->request = request_handler('access_admin', 1, 1);
        $this->request_data = $this->request->data;
    }

    

    public function get_process_list()
    {
        $data = $this->Process_management_model->get_process_list($this->request->data, $this->request->adminId);
        echo json_encode(['status' => true, "data" => $data, "message" => "List loaded"]);
    }

    public function create_update_event()
    {
        $return = $this->Process_management_model->create_update_event($this->request->data, $this->request->adminId);
        $action = 'added';
        if ($this->request->data->id) {
            $action = 'updated';
        }
        if (is_object($return) && $return->status === false) {
            echo json_encode(['status' => false, "data" => null, "error" => $return->error]);
        } else {
            echo json_encode(['status' => true, "data" => $return, "message" => "Event $action successfully"]);
        }
    }

    public function get_process_event()
    {
        $data = $this->request->data;
        $event_data = [];
        if (!empty($data) && !empty($this->request->data->id)) {
            $data = $this->Process_management_model->get_process_event($this->request->data->id);
            if (!empty($data)) {
                $event_data = $data;
            }
        }
        echo json_encode(['status' => true, "data" => $event_data, "message" => "Event loaded successfully"]);
    }

    public function enable_process_event()
    {
        $response = ['status' => false, "data" => [], "error" => "Event could not be updated"];
        $id = $this->request_data->id;
        if (!empty($id)) {
            $data = $this->Process_management_model->update_process_event($id, ['status' => 1], $this->request->adminId);
            if (!empty($data)) {
                $response = ['status' => true, "data" => $data, "message" => "Event disabled successfully"];
            }
        }
        echo json_encode($response);
    }

    public function disable_process_event()
    {
        $response = ['status' => false, "data" => [], "error" => "Event could not be updated"];
        $id = $this->request_data->id;
        if (!empty($id)) {
            $data = $this->Process_management_model->update_process_event($id, ['status' => 0], $this->request->adminId);
            if (!empty($data)) {
                $response = ['status' => true, "data" => $data, "message" => "Event disabled successfully"];
            }
        }
        echo json_encode($response);
    }

    public function clone_process_event()
    {
        $response = ['status' => false, "data" => [], "error" => "Event could not be cloned"];
        $id = $this->request_data->id;
        if (!empty($id)) {
            $data = $this->Process_management_model->clone_process_event($id, $this->request->adminId);
            if (!empty($data)) {
                $response = ['status' => true, "data" => $data, "message" => "Event cloned successfully"];
            }
        }
        echo json_encode($response);
    }

    /**
     * Return recipient types based on selected object for an event
     */
    public function get_recipient_types()
    {
        $object_name = $this->request_data->object_name;
        $model = $this->loadObjectModel($object_name);
        $recipient_types = $this->$model->getRecipientTypes();
        $recipient_types_options = [];
        foreach($recipient_types as $key => $value) {
            $recipient_types_options[] = ['label' => $value, 'value' => $key];
        }        
        $response = ['status' => true, "data" => $recipient_types_options, "message" => ""];
        echo json_encode($response);
    }

    /** 
     * Return available recipients for given object
     */

    public function get_object_recipients()
    {
        $recipient_type = $this->request_data->recipient_type;
        $object_name = $this->request_data->object_name;
        $model = $this->loadObjectModel($object_name);
        $data = [];
        if ($recipient_type === "hcm_users") {
            $reqData = new stdClass();
            $reqData->pageSize = 9999;
            $response = $this->Admin_model->list_admins_dataquery($reqData);
            if (!empty($response) && !empty($response['data'])) {
                foreach($response['data'] as $row) {
                    if (empty($row->username)) {
                        continue;
                    }
                    $data[] = ['field' => $row->username, 'label' => trim($row->firstname . ' ' . $row->lastname)];
                }
            }
        } else {
            $data = $this->$model->getObjectRecipients($recipient_type);
        }
        $response = ['status' => true, "data" => $data, "message" => ""];
        echo json_encode($response);
    }

    private function loadObjectModel($object_name) {        
        $model_info = $this->Process_management_model->getOrm($object_name);
        $this->load->model($model_info['module'] . "/" . $model_info['class_name']);
        return $model_info['class_name'];
    }
}
