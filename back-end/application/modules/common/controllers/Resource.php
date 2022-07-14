<?php

use function PHPSTORM_META\type;

defined('BASEPATH') or exit('No direct script access allowed');
/*
 * controller name: Resource
 */

class Resource extends MX_Controller
{
    public function __construct() {
        $this->load->model('Basic_model');
        $this->request = request_handler();
        parent::__construct();
        $this->load->model('Common_model');
    }

    /**
     * Fetching all the pay level options
     */
    public function get_level_options() {
        $level_option = $this->Common_model->get_level_options();
        $this->printResponse($level_option, []);
    }

    /**
     * Fetching all the skill levels (pay points) options
     */
    public function get_pay_point_options() {
        $pay_point_options = $this->Common_model->get_pay_point_options();
        $this->printResponse($pay_point_options, []);
    }

    private function printResponse($data, $default = '') {
        if (empty($data)) {
            $data = $default;
        }
        ob_start();
        echo json_encode($data);
        $output = ob_get_clean();
        echo $output;
        exit();
    }

    /**
     * Get all employment types from reference table
     */
    public function get_employment_type_options() {
        $employment_type_option = $this->Common_model->get_employment_type_options();
        $this->printResponse($employment_type_option, []);
    }

    /**
     * Returns list of object properties
     */

    public function get_object() {
        $object = $this->request->data->object;
        $object_info = $this->Basic_model->getOrm()[$object];
        $model_class = $object_info['class_name'];
        $model = $object_info['module'] . '/' . $model_class;
        $this->load->model($model);
        $fields = $this->$model_class->getObjectFields(true, true);
        $this->printResponse(['data' => $fields, 'status' => true], []);
    }
}
