<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_template extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        $this->load->model('Sms_template_model');
        $this->load->model('../../common/models/List_view_controls_model');
        
    }
    
     /*
     * its used for create sms_template from user input
     * return type json
     *
     */
    function create_sms_template() {
       $reqData = request_handler();
       $template_data = (array) obj_to_arr($reqData->data);
        $this->form_validation->set_data($template_data)->set_rules([
            [
                'field' => 'name', 'label' => 'Name', 'rules' => 'required'
            ],
            ['field' => 'sms_content', 'label' => 'Content', 'rules' => 'required']
        ]);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $results = array('status' => false, 'error' => implode(', ', $errors));
            echo json_encode($results);
            exit();
        }else{
            $data = $this->Sms_template_model->create_sms_template(
            $reqData->data,$reqData->{'adminId'});
            echo json_encode($data);
            exit();
        }
        
    }

     /*
     * its used for get sms_template  as a list
     * @params
     * $reqData request data like special opration like filter,search, sort
     * return type json
     *
     */
    function get_sms_templates() {
        $reqData = request_handler();
        $filter_condition ='';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData,true);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            if (!empty($filter_condition)) {
                $filter_condition = 
                str_replace(['sms_content','created_by'],
                ['content','s.created_by'], $filter_condition);
            }  
        } else if (!empty($reqData->data->id)) {
            $filter_condition = "id=" . $reqData->data->id;
        }
        $data = $this->Sms_template_model->get_sms_templates($reqData, $filter_condition);
        echo json_encode($data);
        exit();
    }
   
     /*
     * its used for get sms_template by it's id
     * @params
     * $reqData request data will include id of the template
     * return type json
     *
     */
    function get_sms_template_by_id(){
        $reqData = request_handler();
        $data = $this->Sms_template_model->get_sms_template_by_id( $reqData->data);
        echo json_encode($data);
        exit();
    }

      /*
     * its used for updating the sms template by it's id
     * @params
     * $reqData request data will include id of the template
     * and content need to be updated
     * return type json
     *
     */
    function update_sms_template(){
        $reqData = request_handler();
        $template_data = (array) obj_to_arr($reqData->data);
        $this->form_validation->set_data($template_data)->set_rules([
            [
                'field' => 'name', 'label' => 'Name', 'rules' => 'required'
            ],
            ['field' => 'sms_content', 'label' => 'Content', 'rules' => 'required']
            
        ]);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $results = array('status' => false, 'error' => implode(', ', $errors));
            echo json_encode($results);
            exit();

        }
        else{
            $data = $this->Sms_template_model->update_sms_template(
            $reqData->data,$reqData->{'adminId'});
            echo json_encode($data);
            exit();
        }    
    }


      /*
     * its used for deleting the sms template by it's id
     * @params
     * $reqData request data will include id of the template
     * return type json
     *
     */
    function delete_sms_template_by_id()
    {
        $reqData = request_handler();
        if (empty($reqData->data)) 
         {
          return false;
         }
         $data = $this->Sms_template_model->delete_sms_template_by_id($reqData->data,$reqData->{'adminId'});
         echo json_encode($data);
         exit();

    }

    /*
     * get_active_sms_template used in activity tab
     * return type json
     *
     */
    function get_active_sms_templates() {
        $reqData = request_handler();
        $data = $this->Sms_template_model->get_active_sms_templates($reqData);
        return $this->output->set_content_type('json')->set_output(json_encode($data));
    }
} 
