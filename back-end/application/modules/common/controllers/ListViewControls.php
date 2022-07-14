<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * class : Contact
 * use : use for handle List_view_controls request and response
 *
 * @property-read \List_view_controls_model $List_view_controls_model
 */
class ListViewControls extends MX_Controller {

    // load custon validation traits function
    use formCustomValidation;

    // defualt contruct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        $this->loges->setLogType('crm');
        $this->load->library('UserName');
        // load List_view_controls  model
        $this->load->model('List_view_controls_model');
        $this->load->helper('message');
        $this->load->helper('i_pad');
    }

    function create_update_list_view_controls() {
        $reqData = request_handler();
        $filter_id = '';
        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'user_view_by', 'label' => 'List View', 'rules' => 'required'),
            ];
            if(!$reqData->data->shareSettings){
                $validation_rules[] = array('field' => 'list_name', 'label' => 'List Name', 'rules' => 'required');
            }

            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {

                $list_name = $reqData->data->list_name;
                if(!empty($reqData->data->filter_list_id)){
                    $where = array('list_name' => $list_name, 'id!=' => $reqData->data->filter_list_id, 'related_type'=> $reqData->data->related_type,'created_by'=> $reqData->adminId, 'archive'=>0);
                }else{
                    $where = array('list_name' => $list_name, 'related_type'=> $reqData->data->related_type,'created_by'=> $reqData->adminId, 'archive'=>0);
                }
                $colown = array('id', 'list_name');
                // check list name already exist or not
                $check_filter = $this->basic_model->get_record_where('list_view_controls', $colown, $where);

                // If not exist
                if (!$check_filter) {
                    $filterId = $this->List_view_controls_model->create_update_list_view_controls($reqData->data, $reqData->adminId);
                    if(!empty($reqData->data->filter_list_id)){
                        $filter_id = $reqData->data->filter_list_id;
                    }else{
                        $filter_id = $filterId;
                    }
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $reqData->adminId);
                    if(!empty($reqData->data->filter_list_id)){
                        $this->loges->setTitle("Updated list view " . $filterId . " by " . $adminName);
                    }else{
                        $this->loges->setTitle("Added list view " . $filterId . " by " . $adminName);
                    }

                    $this->loges->setDescription(json_encode($reqData->data));
                    $this->loges->setUserId($filterId);
                    $this->loges->setCreatedBy($reqData->adminId);
                    $this->loges->createLog();
                    if(!empty($reqData->data->filter_list_id)){
                        $response = ["status" => true, "msg" => "List view controls has been updated successfully.", "list_id" => $filter_id];
                    }else{
                        $response = ["status" => true, "msg" => "List view controls has been created successfully.", "list_id" => $filter_id];
                    }
                }else{
                        $response = ['status' => false, 'error' => 'Filter name already exist '];
                }

            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
    }

    /*
     * its use for get list control view by related_type
     *
     * return type json
     */
    function get_list_view_controls_by_related_type() {
        $reqData = request_handler();
        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'related_type', 'label' => 'Related Type', 'rules' => 'required'),
            ];

            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {
                $result["list_control_option"] = $this->List_view_controls_model->get_list_view_controls_by_related_type($reqData->data, $reqData->adminId, $reqData->uuid_user_type);
                $pinned_data = $this->List_view_controls_model->check_list_has_default_pinned($reqData->data, $reqData->adminId );
                $filter_list = [];
                foreach($result['list_control_option'] as $refobj) {
                    $singarr['label'] =  $refobj->label;
                    $singarr['value'] = $refobj->value;
                    $singarr['is_private_and_own_filter']= $refobj->is_created_by_current_user;
                    if($pinned_data && $pinned_data->pinned_id==$refobj->value){
                        $singarr['pinned_id'] = $pinned_data->pinned_id;
                    }else{
                        $singarr['pinned_id'] = 0;
                    }
                    $filter_list[] = $singarr;
                }

                $result["list_control_option"] = $filter_list;
                $response = ["status" => true, "data" => $result];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            $response = ["status" => false, "error" => "Related type is required"];
        }

        echo json_encode($response);
    }

     /*
     * its use for get list control view by related_type
     *
     * return type json
     */
    function get_member_list_view_controls_by_related_type() {
        $reqData = new stdclass;
        $reqData->data = api_request_handler();
        print_r($reqData->data); die;
        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'related_type', 'label' => 'Related Type', 'rules' => 'required'),
            ];

            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {
                $result["list_control_option"] = $this->List_view_controls_model->get_list_view_controls_by_related_type($reqData->data, $reqData->adminId );
                $pinned_data = $this->List_view_controls_model->check_list_has_default_pinned($reqData->data, $reqData->adminId );
                $filter_list = [];
                foreach($result['list_control_option'] as $refobj) {
                    $singarr['label'] =  $refobj->label;
                    $singarr['value'] = $refobj->value;
                    if($pinned_data && $pinned_data->pinned_id==$refobj->value){
                        $singarr['pinned_id'] = $pinned_data->pinned_id;
                    }else{
                        $singarr['pinned_id'] = 0;
                    }
                    $filter_list[] = $singarr;
                }

                $result["list_control_option"] = $filter_list;
                $response = ["status" => true, "data" => $result];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            $response = ["status" => false, "error" => "Related type is required"];
        }

        echo json_encode($response);
    }
    /*
     * its use for get list control view by id
     *
     * return type json
     */
    function get_list_view_controls_by_id() {
        $reqData = request_handler();
        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'related_type', 'label' => 'Related Type', 'rules' => 'required'),
                array('field' => 'filter_list_id', 'label' => 'Filter Id', 'rules' => 'required'),
            ];

            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {
                $result = $this->List_view_controls_model->get_list_view_controls_by_id($reqData->data->related_type,$reqData->data->filter_list_id,$reqData->adminId);
                $pinned_data = $this->List_view_controls_model->check_list_has_default_pinned($reqData->data, $reqData->adminId);
                if($pinned_data && $pinned_data->pinned_id==$result->value){
                    $result->pinned_id = $pinned_data->pinned_id;
                }else{
                    $result->pinned_id = 0;
                }
                if($result->created_by==$reqData->adminId){
                    $response = ["status" => true, "data" => $result,"isOwnList" => true];
                }else{
                    $response = ["status" => true, "data" => $result,"isOwnList" => false];
                }

            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            $response = ["status" => false, "error" => "Related type is required"];
        }

        echo json_encode($response);
    }

    /*
     * its use for archive filter
     * return type json
     */

    function archive_filter_list() {
        $reqData = request_handler();
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;


        if (!empty($data["id"])) {
            $filter_list_id = $data['id'];

            $this->List_view_controls_model->archive_filter_list($filter_list_id);

            $this->load->library('UserName');
            $adminName = $this->username->getName('admin', $adminId);

            $this->loges->setTitle(sprintf("Successfully archived filter list with ID of %s by %s", $filter_list_id, $adminName));
            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($filter_list_id);
            $this->loges->setCreatedBy($adminId);
            $this->loges->createLog();

            $response = ['status' => true, 'msg' => "Filter list successfully archived"];
        } else {
            $response = ['status' => false, 'error' => "Filter list id not found"];
        }

        echo json_encode($response);
    }

    function pin_unpin_filter() {
        $reqData = request_handler();
        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'related_type', 'label' => 'Related type', 'rules' => 'required'),
                array('field' => 'pin_list_id', 'label' => 'Pin id', 'rules' => 'required'),
            ];

            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {
                $filterId = $this->List_view_controls_model->pin_unpin_filter($reqData->data, $reqData->adminId);
                if($filterId){
                    $response = ["status" => true, "msg" => "success"];
                }else{
                    $response = ["status" => false, "msg" => "failed"];
                }

            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
    }
    /*
     * its set the default pin
     * return type json
     */
    function default_pin_filter() {
        $reqData = request_handler();
        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'related_type', 'label' => 'Related type', 'rules' => 'required'),
            ];

            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {
                $filterId = $this->List_view_controls_model->default_pin_filter($reqData->data, $reqData->adminId);
                if($filterId){
                    $response = ["status" => true, "msg" => "success"];
                }else{
                    $response = ["status" => false, "msg" => "failed"];
                }

            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
    }

    /*
     * its use for get list control view by id
     *
     * return type json
     */
    function get_list_view_controls_by_default_pinned() {
        $reqData = request_handler();
        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'related_type', 'label' => 'Related Type', 'rules' => 'required'),
            ];

            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {
                $pinned_data = $this->List_view_controls_model->check_list_has_default_pinned($reqData->data, $reqData->adminId );
                if((!empty($pinned_data) && $pinned_data->pinned_id) || (!empty($reqData->data->list_id) && $reqData->data->list_id != 'all')) {
                    $list_id = $pinned_data->pinned_id?? 0;
                    if (!empty($reqData->data->list_id)) {
                        $list_id = $reqData->data->list_id;
                    }
                    $result = $this->List_view_controls_model->get_list_view_controls_by_id($reqData->data->related_type, $list_id, $reqData->adminId );
                    if (!empty($result)) {
                        if(empty($reqData->data->list_id) && !empty($pinned_data) && $pinned_data->pinned_id==$result->value){
                            $result->pinned_id = $pinned_data->pinned_id;
                        }else{
                            if (!empty($reqData->data->list_id) &&  !empty($pinned_data) && $reqData->data->list_id == $pinned_data->pinned_id) {
                                $result->pinned_id = $reqData->data->list_id;
                            } else {
                                $result->pinned_id = 0;
                            }
                        }
                        if($result->created_by==$reqData->adminId){
                            $response = ["status" => true, "data" => $result,"isOwnList" => true];
                        }else{
                            $response = ["status" => true, "data" => $result,"isOwnList" => false];
                        }
                    } else {
                        $response = ["status" => true, "data" => null];
                    }
                }else{
                    $response = ["status" => true, "data" => null];
                }

            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            $response = ["status" => false, "error" => "Related type is required"];
        }

        echo json_encode($response);
    }

    function get_member_list_view_controls_by_default_pinned() {
        $reqData = new stdclass;
        $reqData->data = api_request_handler();

        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'related_type', 'label' => 'Related Type', 'rules' => 'required'),
            ];

            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {
                $pinned_data = $this->List_view_controls_model->check_list_has_default_pinned($reqData->data, $reqData->adminId );
                if((!empty($pinned_data) && $pinned_data->pinned_id) || (!empty($reqData->data->list_id) && $reqData->data->list_id != 'all')) {
                    $list_id = $pinned_data->pinned_id?? 0;
                    if (!empty($reqData->data->list_id)) {
                        $list_id = $reqData->data->list_id;
                    }
                    $result = $this->List_view_controls_model->get_list_view_controls_by_id($reqData->data->related_type, $list_id, $reqData->adminId );
                    if (!empty($result)) {
                        if(empty($reqData->data->list_id) && !empty($pinned_data) && $pinned_data->pinned_id==$result->value){
                            $result->pinned_id = $pinned_data->pinned_id;
                        }else{
                            if (!empty($reqData->data->list_id) &&  !empty($pinned_data) && $reqData->data->list_id == $pinned_data->pinned_id) {
                                $result->pinned_id = $reqData->data->list_id;
                            } else {
                                $result->pinned_id = 0;
                            }
                        }
                        if($result->created_by==$reqData->adminId){
                            $response = ["status" => true, "data" => $result,"isOwnList" => true];
                        }else{
                            $response = ["status" => true, "data" => $result,"isOwnList" => false];
                        }
                    } else {
                        $response = ["status" => true, "data" => null];
                    }
                }else{
                    $response = ["status" => true, "data" => null];
                }

            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            $response = ["status" => false, "error" => "Related type is required"];
        }

        echo json_encode($response);
    }
}
