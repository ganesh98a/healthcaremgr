<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class TvDisplay extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
        $this->load->model('Tv_display_model');
        $this->loges->setLogType('user_admin');
    }

    // using this method get admin list
    public function get_admin_tv_slide() {
        $reqData = request_handler('access_admin', 1, 1);

        if (!empty($reqData->data->adminId)) {
            $reqData = $reqData->data;
            if (empty($reqData->only_slide)) {
                $response = $this->Tv_display_model->get_edit_slide_options();
            }

            $response["admin_tv_slide"] = $this->Tv_display_model->get_admin_tv_slide($reqData->adminId);

            echo json_encode(['status' => true, "data" => $response]);
        }
    }

    function check_admin_tv_slide_data($x, $reqdata) {
        $reqdata = json_decode($reqdata);
        if ($reqdata->admin_tv_slide) {
            foreach ($reqdata->admin_tv_slide as $index => $val) {
                $slide = $index + 1;
                if (empty($val->moduleId)) {
                    $this->form_validation->set_message('check_admin_tv_slide_data', "Slide - " . $slide . " module is required");
                    return FALSE;
                }
                if (empty($val->module_graphId)) {
                    $this->form_validation->set_message('check_admin_tv_slide_data', "Slide - " . $slide . " module graph is required");
                    return FALSE;
                }
            }
        } else {
            $this->form_validation->set_message('check_admin_tv_slide_data', "Tv slide is required field");
            return FALSE;
        }

        return true;
    }

    public function save_admin_tv_slide() {
        $reqData = request_handler('access_admin', 1, 1);
        $this->loges->setCreatedBy($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $this->form_validation->set_data((array) $reqData);
            $validation_rules = array(
                array('field' => 'adminId', 'label' => 'adminId', 'rules' => 'required'),
                array('field' => 'admin_tv_slide', 'label' => 'tv slide', 'rules' => 'callback_check_admin_tv_slide_data[' . json_encode($reqData) . ']'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $this->Tv_display_model->save_admin_tv_slide($reqData);

                // SET LOG  
                $this->loges->setTitle('Save set tv display of : ' . $reqData->adminId);
                $this->loges->setUserId($reqData->adminId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

}
