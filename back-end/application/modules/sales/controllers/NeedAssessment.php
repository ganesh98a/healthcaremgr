<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 *
 * its use for handle request of NeedAssessment
 *
 * @property-read \Need_assessment_model $Need_assessment_model
 */
class NeedAssessment extends MX_Controller {

    use formCustomValidation;

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        $this->loges->setLogType('crm');

        // load model @Need_assessment_model
        $this->load->model('Need_assessment_model');
        $this->load->helper('message');
        $this->load->model('../../common/models/List_view_controls_model');
        // load document model from member module
        $this->load->model('../../sales/models/Attachment_model');
        $this->load->model('../../schedule/models/Schedule_attachment_model');
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

    /*
     * its use for search ownder staff (admin) in create need_assessment
     * return type json
     */
    function get_owner_staff_search() {
        $reqData = request_handler();
        $ownerName = $reqData->data->query ?? '';
        $rows = $this->Need_assessment_model->get_owner_staff_by_name($ownerName);
        echo json_encode($rows);
    }

    /*
     * its use for get need assessment status option used in listing need assessment filter
     * return type josn
     */
    function get_need_assessment_status_option() {
        request_handler();

        $rows = $this->Need_assessment_model->get_need_assessment_status_option();
        echo json_encode(["status" => true, "data" => $rows]);
    }

    /*
     * its use for search account person (tbl_person) in create need assessment
     * return type json
     */
    function get_account_person_name_search() {
        $reqData = request_handler();
        $name = $reqData->data->query ?? '';
        $rows = $this->Need_assessment_model->get_account_person_name_search($name);
        echo json_encode($rows);
    }

    /*
     * its use for get need_assessment option
     * return need_assessment list
     *
     *
     * opration in listing
     * searching, filter and sorting
     *
     * return type json
     */
    function get_need_assessment_list() {
        $reqData = request_handler('access_crm');
        $filter_condition='';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData,true);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            if (!empty($filter_condition)) {
                $filter_condition = 
                str_replace(['title','need_assessment_number','status','created','created_by'], 
                ['o.title','need_assessment_number','o.status','created','o.created_by'], $filter_condition);
            }  
        }
        if (!empty($reqData->data)) {
            $contact_id = 0;
            $participant_name = "";
            if (!empty($reqData->data->participant_id)) {
                $column = array('contact_id', 'name');
                $where["id"] = $reqData->data->participant_id;
				$where["archive"] = 0;
                $participant = $this->basic_model->get_record_where('participants_master', $column, $where);
                if (!empty($participant)) {
                    $contact_id = $participant[0]->contact_id;
                    $participant_name = $participant[0]->name;
                }
            }
            $result = $this->Need_assessment_model->get_need_assessment_list($reqData->data, $filter_condition, $contact_id, $reqData->uuid_user_type);
            $result['participant_name'] = $participant_name;
            echo json_encode($result);
            exit();
        }
    }

    /*
     * its use for create need assessment
     * handle request of create need assessment
     *
     * @todo: MISLEADING CONTROLLER ACTION NAME!
     * this is not  create' need assessment anymore, it is more like 'save' need assessment
     *
     * return type json
     * ['status' => true, 'msg' => 'Need assessment has been created successfully.']
     */
    function create_need_assessment() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = 0;
            $validation_rules = [
                array('field' => 'title', 'label' => 'Title', 'rules' => "required"),
                array('field' => 'owner', 'label' => 'Owner', 'rules' => 'required'),
                array('field' => 'account_person', 'label' => 'Account', 'rules' => 'required'),
                array('field' => 'account_type', 'label' => 'Account Type', 'rules' => 'required')
            ];

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $need_assessmentId = $this->Need_assessment_model->save_need_assessment($data, $adminId);

                if ($need_assessmentId) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    if(isset($data['need_assessment_id'])){
                        $this->loges->setTitle("Need Assessment updated " . $data['title'] . " by " . $adminName);  //
                        $succes_msg = 'Need assessment has been updated successfully.';
                    }else{
                        $succes_msg = 'Need assessment has been created successfully.';
                        $this->loges->setTitle("New need assessment created " . $data['title'] . " by " . $adminName);  //
                    }

                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($need_assessmentId);
                    $this->loges->createLog(); // create log

                    $response = ['status' => true, 'msg' => $succes_msg, 'id' => $need_assessmentId];
                } else {
                    $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
        exit();
    }

    /*
    * get all detail of need assessment
    * by passing id
    */
    public function get_need_assessment_detail() {
        $reqData = $reqData1 = request_handler('access_crm');
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $result = $this->Need_assessment_model->get_need_assessment_detail($reqData);
            //get participant details
            $participant_id = 0;
            if (!empty($result) && !empty($result['data']['person_id'])) {
                $person_id = $result['data']['person_id'];
                $column = array('id');
                $where["contact_id"] = $person_id;
				$where["archive"] = 0;
                $participant = $this->basic_model->get_record_where('participants_master', $column, $where);
                if (!empty($participant)) {
                   $participant_id = $participant[0]->id;
                }
                $result['data']['participant_id'] = $participant_id;
            }
            echo json_encode($result);
        }
    }

    /**
     *
     * Archived need assessment will be excluded in the list of need assessment.
     */
    public function archive_need_assessment()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;

        $this->output->set_content_type('json');

        $result = $this->Need_assessment_model->get_need_assessment_detail((object)$data);
        if (empty($result)) {
            return $this->output->set_output(json_encode([
                'status' => false,
                'error' => 'Need assessment does not exist anymore. Please refresh your page',
            ]));
        }

        $result = $this->basic_model->update_records('need_assessment', array('archive'=>1),array('id'=>$data['need_assessment_id']));
        if (!$result) {
            return $this->output->set_output(json_encode([
                'status' => false,
                'error' => $result['error'] ?? system_msgs('something_went_wrong'),
            ]));
        }
        // Log
        $need_assessment_id = $data['need_assessment_id'];

        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully archived need assessment with ID of %s by %s", $need_assessment_id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully archived need assessment with ID of %s by %s", $need_assessment_id, $adminName));  // set title in log
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($need_assessment_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        return $this->output->set_output(json_encode([
            'status' => true,
            'msg' => "Need assessment successfully archived"
        ]));
    }

    /*
     * its use for get option of create opportunity
     * return type json
     */
    function get_option_needassessment() {
        request_handler();
        $this->db->select("false as active",false);
        $rows["assessment_assistance"] = $this->basic_model->get_result('assessment_assistance',['archive'=>0], ["title as label", 'id as value','key_name']);
        echo json_encode(["status" => true, "data" => $rows]);
    }

    /*
    * Add/Update medication
    */
    function save_medication(){
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;

        if((!isset($data['not_applicable']) || $data['not_applicable'] != 1) &&
            ((!isset($data['medication_administration']) || !$data['medication_administration']) ||
                (!isset($data['medication_emergency']) || !$data['medication_emergency']) ||
                (!isset($data['reduce_concern']) || !$data['reduce_concern']) ||
                (!isset($data['medication_vitamins_counter']) || !$data['medication_vitamins_counter'])) ) {
            $return = ['status'=>false,'msg'=>'Please select all the options to continue.'];
    }
    else {
        $result = $this->Need_assessment_model->save_medication($data,$adminId);
        $return = ['status'=>true,'msg'=>'Medication updated successfully.'];
    }

        // Log
    $need_assessment_id = $data['need_assessment_id']??0;
    $this->load->library('UserName');
    $adminName = $this->username->getName('admin', $adminId);
    $this->loges->setTitle(sprintf("Medication is updated under Assessment ID %s by %s", $need_assessment_id, $adminName));
    $this->loges->setDescription(json_encode($data));
    $this->loges->setUserId($adminId);
    $this->loges->setCreatedBy($adminId);
    $this->loges->createLog();
    echo json_encode($return);
    exit();
}

    /*
    * Add/Update meal time assisstance
    */
    function save_mealtime_assisstance(){
        $reqData = request_handlerFile('access_crm');
        $data = obj_to_arr($reqData);
        $adminId = $data['adminId'];       
        $this->load->model('Attachment_model');
        $attached_file = $this->Attachment_model->find_all_attachments_by_object_id_and_name($data);
        if($data['mealtime_assistance_plan'] == "2" && empty($attached_file) && empty($_FILES)){
            $return = ['status'=>false,'msg'=>'Please upload mealtime assistance plan file in attachments'];
            echo json_encode($return);
            exit();
        }else{
            if((!isset($data['not_applicable'])) || (!isset($data['risk_choking']) || !isset($data['risk_aspiration']) 
            || !isset($data['mealtime_assistance_plan']) || !isset($data['require_assistance_plan']) )){
                $return = ['status'=>false,'msg'=>'Please select all the options to continue.'];
            }else{
                $result = $this->Need_assessment_model->save_mealtime_assisstance($data,$adminId);
                if (!empty($_FILES)){
                    $attach_result = $this->Attachment_model->save_multiple_attachments_for_need_assesment((array) $data, $_FILES, $adminId);
                }

               
                $return = ['status'=>true,'msg'=>'Mealtime updated successfully.'];
                
                // Log
                $need_assessment_id = $data['need_assessment_id']??0;
                $this->load->library('UserName');
                $adminName = $this->username->getName('admin', $adminId);
                $this->loges->setTitle(sprintf("Meal time assistance is updated under Assessment ID %s by %s", $need_assessment_id, $adminName));
                $this->loges->setDescription(json_encode($data));
                $this->loges->setUserId($adminId);
                $this->loges->setCreatedBy($adminId);
                $this->loges->createLog();                             
            } 
            
        }
        echo json_encode($return);
        exit();
        
    }

    /*
    * Add/Update nutritional support
    */
    function save_nutritional_support(){
        $reqData = request_handlerFile('access_crm');
        $data = obj_to_arr($reqData);
        $adminId = $data['adminId'];       
        $this->load->model('Attachment_model');
        $attached_file = $this->Attachment_model->find_all_attachments_by_object_id_and_name($data);        
            if((!isset($data['support_with_eating']) || !isset($data['risk_aspiration']) 
            || !isset($data['risk_choking']) || !isset($data['peg_assistance_plan']) || !isset($data['pej_assistance_plan']) || !isset($data['selected_food_preferences']) )){
                $return = ['status'=>false,'msg'=>'Please select all the options to continue.'];
            }else{
                $result = $this->Need_assessment_model->save_nutritional_support($data,$adminId);
                if (!empty($_FILES)){
                    $attach_result = $this->Attachment_model->save_multiple_attachments_for_nutritional_support((array) $data, $_FILES, $adminId);
                }
               
                $return = ['status'=>true,'msg'=>'Nutritional Support updated successfully.'];
                
                // Log
                $need_assessment_id = $data['need_assessment_id']??0;
                $this->load->library('UserName');
                $adminName = $this->username->getName('admin', $adminId);
                $this->loges->setTitle(sprintf("Meal time assistance is updated under Assessment ID %s by %s", $need_assessment_id, $adminName));
                $this->loges->setDescription(json_encode($data));
                $this->loges->setUserId($adminId);
                $this->loges->setCreatedBy($adminId);
                $this->loges->createLog();                             
            }             

        echo json_encode($return);
        exit();
        
    }

    /*
    * Add/Update communication
    */
    function save_communication(){
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;

        if(
            (!isset($data['cognition']) || !$data['cognition']) ||
            (!isset($data['instructions']) || !$data['instructions']) ||
            (!isset($data['hearing_impared']) || !$data['hearing_impared']) ||
            (!isset($data['visually_impared']) || !$data['visually_impared'])
        ){
            $return = ['status'=>false,'msg'=>'Please select all the options to continue.'];
        }
        else if(isset($data['communication_other']) && $data['communication_other'] == 1 && (!isset($data['communication_other_desc']) || !$data['communication_other_desc'])) {
            $return = ['status'=>false,'msg'=>'Please describe other way of communication'];
        }
        else if(isset($data['instructions']) && $data['instructions'] == 1 && (!isset($data['instructions_desc']) || !$data['instructions_desc'])) {
            $return = ['status'=>false,'msg'=>'Please describe method of instructions'];
        }
        else if(isset($data['hearing_impared']) && $data['hearing_impared'] == 2 && (!isset($data['hearing_impared_desc']) || !$data['hearing_impared_desc'])) {
            $return = ['status'=>false,'msg'=>'Please describe hearing impaired'];
        }
        else if(isset($data['visually_impared']) && $data['visually_impared'] == 2 && (!isset($data['visually_impared_desc']) || !$data['visually_impared_desc'])) {
            $return = ['status'=>false,'msg'=>'Please describe visually impaired'];
        }
        else if(isset($data['instructions']) && $data['instructions'] == 2 && (!isset($data['yes_verbal_instruction']) || !$data['yes_verbal_instruction'] || $data['yes_verbal_instruction']==0)) {
            $return = ['status'=>false,'msg'=>'Please select verbal instruction'];
        }
        else {
            $result = $this->Need_assessment_model->save_communication($data,$adminId);
            $return = ['status'=>true,'msg'=>'Community access assistance updated successfully.'];

            // Log
            $need_assessment_id = $data['need_assessment_id']??0;
            $this->load->library('UserName');
            $adminName = $this->username->getName('admin', $adminId);
            $this->loges->setTitle(sprintf("Community access assistance is updated under Assessment ID %s by %s", $need_assessment_id, $adminName));
            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($adminId);
            $this->loges->setCreatedBy($adminId);
            $this->loges->createLog();
        }

        echo json_encode($return);
        exit();
    }

    /*
    * Add/Update community access
    */
    function save_community_access(){
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;

        if((!isset($data['not_applicable']) || !$data['not_applicable']) && (
            (!isset($data['bowelcare']) || !$data['bowelcare']) ||
            (!isset($data['bladdercare']) || !$data['bladdercare']) ||
            (!isset($data['using_money']) || !$data['using_money']) ||
            (!isset($data['grocessary_shopping']) || !$data['grocessary_shopping']) ||
            (!isset($data['paying_bills']) || !$data['paying_bills']) ||
            (!isset($data['swimming']) || !$data['swimming']) ||
            (!isset($data['road_safety']) || !$data['road_safety']) ||
            (!isset($data['companion_cart']) || !$data['companion_cart']) ||
            (!isset($data['method_transport']) || !$data['method_transport']) ||
            (!isset($data['support_taxis']) || !$data['support_taxis'])
        )){
            $return = ['status'=>false,'msg'=>'Please select all the options to continue.'];
        }
        else if($data['support_taxis'] == 1 && (!isset($data['support_taxis_desc']) || !$data['support_taxis_desc'])) {
            $return = ['status'=>false,'msg'=>'Please explain support required for Taxis/Ubers'];
        }
        else {
            $result = $this->Need_assessment_model->save_community_access($data,$adminId);
            $return = ['status'=>true,'msg'=>'Community access assistance updated successfully.'];

            // Log
            $need_assessment_id = $data['need_assessment_id']??0;
            $this->load->library('UserName');
            $adminName = $this->username->getName('admin', $adminId);
            $this->loges->setTitle(sprintf("Community access assistance is updated under Assessment ID %s by %s", $need_assessment_id, $adminName));
            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($adminId);
            $this->loges->setCreatedBy($adminId);
            $this->loges->createLog();
        }

        echo json_encode($return);
        exit();
    }

    //Save/Update personal Care and daily living
    function save_personal_daily_living() {

        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;
        // print_r($data); die;
        $living = $data['living'];
        $personal = $data['personal'];

        if(
            (!isset($personal['not_applicable']) || !$personal['not_applicable']) && (
                (!isset($personal['showercare']) || !$personal['showercare']) ||
                (!isset($personal['dressing']) || !$personal['dressing']) ||
                (!isset($personal['teethcleaning']) || !$personal['teethcleaning']) ||
                (!isset($personal['cooking']) || !$personal['cooking']) ||
                (!isset($personal['lighthousework']) || !$personal['lighthousework'])
            ) ||
            (!isset($living['not_applicable_living']) || !$living['not_applicable_living']) &&
            (
            (!isset($living['grocessary_shopping']) || !$living['grocessary_shopping']) ||
            (!isset($living['road_safety']) || !$living['road_safety']) ||
            (!isset($living['companion_cart']) || !$living['companion_cart']) ||
            (!isset($living['method_transport']) || !$living['method_transport']) ||
            (!isset($living['support_taxis']) || !$living['support_taxis'])
            )
            ){
            $return = ['status'=>false,'msg'=>'Please select all the options to continue.'];
        }
        else if((isset($living['support_taxis']) && $living['support_taxis'] == 1)
            && (!isset($living['support_taxis_desc']) || !$living['support_taxis_desc']))
        {
            $return = ['status'=>false,'msg'=>'Please explain support required for Taxis/Ubers'];
        } else {
            $living['not_applicable'] = isset($living['not_applicable_living'])??0;
            //Save/Update Personal care
            $this->save_update_personalcare($personal, $adminId);
            //Save/Update Daily saving
            $this->Need_assessment_model->save_community_access($living, $adminId);

            $return = ['status'=> true,'msg'=>'Personal Care and Daily Living updated successfully.'];
        }

        echo json_encode($return);
        exit();
    }

    /**
     * @param $data {array} List of data
     * @param $adminId {int} Admin id
     *
     * Save or update personal care record
     */
    function save_update_personalcare($data, $adminId) {

        $this->Need_assessment_model->save_personalcare($data,$adminId);

        // Log
        $need_assessment_id = $data['need_assessment_id']??0;
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Personal care assistance is updated under Assessment ID %s by %s", $need_assessment_id, $adminName));
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($adminId);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
    }

    /*
    * Add/Update personal care
    */
    function save_personalcare(){
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;

        if((!isset($data['not_applicable']) || !$data['not_applicable']) && (
            (!isset($data['bowelcare']) || !$data['bowelcare']) ||
            (!isset($data['bladdercare']) || !$data['bladdercare']) ||
            (!isset($data['showercare']) || !$data['showercare']) ||
            (!isset($data['dressing']) || !$data['dressing']) ||
            (!isset($data['teethcleaning']) || !$data['teethcleaning']) ||
            (!isset($data['cooking']) || !$data['cooking']) ||
            (!isset($data['eating']) || !$data['eating']) ||
            (!isset($data['drinking']) || !$data['drinking']) ||
            (!isset($data['lighthousework']) || !$data['lighthousework'])
        )){
            $return = ['status'=>false,'msg'=>'Please select all the options to continue.'];
        } else {
            $result = $this->Need_assessment_model->save_personalcare($data,$adminId);
            $return = ['status'=>true,'msg'=>'Personal care assistance updated successfully.'];

            // Log
            $need_assessment_id = $data['need_assessment_id']??0;
            $this->load->library('UserName');
            $adminName = $this->username->getName('admin', $adminId);
            $this->loges->setTitle(sprintf("Personal care assistance is updated under Assessment ID %s by %s", $need_assessment_id, $adminName));
            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($adminId);
            $this->loges->setCreatedBy($adminId);
            $this->loges->createLog();
        }

        echo json_encode($return);
        exit();
    }

    /*
    * Add/Update mobility
    */
    function save_mobility(){
        $reqData = request_handlerFile('access_crm');
        $data = obj_to_arr($reqData);
        $adminId = $data['adminId'];        
        
        if((!isset($data['not_applicable'])) && (!isset($data['inout_bed']) || !isset($data['inout_shower']) || !isset($data['onoff_toilet']) || !isset($data['inout_chair']) || !isset($data['inout_vehicle'])
        || !isset($data['can_mobilize']) || !isset($data['short_distances'])
        || !isset($data['long_distances']) || !isset($data['up_down_stairs'])
        || !isset($data['uneven_surfaces'])
        )){
            $return = ['status'=>false,'msg'=>'Please select all the options to continue.'];
        } else {
            $result = $this->Need_assessment_model->save_mobility($data,$adminId);
            if (!empty($_FILES)){
                $this->load->model('Attachment_model');
                $attach_result = $this->Attachment_model->save_multiple_attachments((array) $data, $_FILES, $adminId);
            }
            $return = ['status'=>true,'msg'=>'Mobility assistance updated successfully.'];
        }

        // Log
        $need_assessment_id = $data['need_assessment_id']??0;
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Mobility assistance is updated under Assessment ID %s by %s", $need_assessment_id, $adminName));
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($adminId);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
        echo json_encode($return);
        exit();
    }

    /*
    *
    * get data from medication by passing
    * need_assessment_id
    */
    function get_selected_medication()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $row = $this->basic_model->get_row('need_assessment_medication', array('*'),['archive'=>0,'need_assessment_id'=>$data['need_assessment_id']]);
        if(!empty($row)) $status =  true; else $status = false;
        echo json_encode(["status" => $status, "data" => $row]);
    }

    /*
    *
    * get data from meal assistance by passing
    * need_assessment_id
    */
    function get_selected_meal_assistance()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $dataResult = null;
        $row = $this->basic_model->get_row('need_assessment_mealtime', array("*"),['archive'=>0,'need_assessment_id'=>$data['need_assessment_id']]);
        $dataResult['mealtime_data'] = $row;
        if(!empty($data) && $reqData->uuid_user_type==ADMIN_PORTAL){
            $this->load->model('Attachment_model');
            $dataResult['attachment_data'] = $this->Attachment_model->find_all_attachments_by_object_id_and_like_obj_name($data);            
        }
        if(!empty($row)) $status =  true; else $status = false;       
        echo json_encode(["status" => $status, "data" => $dataResult]);
    }

    /*
    *
    * get data from selected nutritional support by passing
    * need_assessment_id
    */
    function get_selected_nutritional_support()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $dataResult = null;
        $row = $this->basic_model->get_row('need_assessment_ns', array("*"),['archive'=>0,'need_assessment_id'=>$data['need_assessment_id']]);
        $food_preferences =[];
        if(!empty($row)){
             //join for food ref data
         $joins = [
            ['left' => ['table' => 'references AS re', 'on' => 'nfa.food_preferences_ref_id = re.id']],
        ];
        $food_preferences = $this->basic_model->get_rows("ns_food_preferences as nfa", ["nfa.food_preferences_ref_id as id ", "re.display_name as label"], ['na_nutritional_support_id	'=>$row->id, "nfa.archive" => 0 ], $joins);
        }
        
        $dataResult['nutritional_support'] = $row;
        $dataResult['food_preferences']= $food_preferences==false ? [] : $food_preferences;
        if(!empty($data) && $reqData->uuid_user_type==ADMIN_PORTAL){
            $this->load->model('Attachment_model');
            $dataResult['attachment_data'] = $this->Attachment_model->find_all_attachments_by_object_id_and_like_obj_name($data);            
        }
        if(!empty($row)) $status =  true; else $status = false;       
        echo json_encode(["status" => $status, "data" => $dataResult]);
    }

    /**
     * fetching the get_selected_communication information for any need assistant
     */
    function get_selected_communication()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $row = $this->basic_model->get_row('need_assessment_communication', array("*"),['archive'=>0,'need_assessment_id'=>$data['need_assessment_id']]);
        if(!empty($row)) $status =  true; else $status = false;
        echo json_encode(["status" => $status, "data" => $row]);
    }

    /**
     * fetching the personal care information for any need assistant
     */
    function get_selected_personalcare()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $row = $this->basic_model->get_row('need_assessment_personalcare', array("*"),['archive'=>0,'need_assessment_id'=>$data['need_assessment_id']]);
        if(!empty($row)) $status =  true; else $status = false;
        echo json_encode(["status" => $status, "data" => $row]);
    }

    /**
     * fetching the mobility information for any need assistant
     */
    function get_selected_mobility()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $dataResult = null;
        $row = $this->basic_model->get_row('need_assessment_mobility', array("*"),['archive'=>0,'need_assessment_id'=>$data['need_assessment_id']]);        
        $dataResult['mobility_data'] = $row;
        if(!empty($data) && $reqData->uuid_user_type==ADMIN_PORTAL){
          $dataResult['attachment_data'] = $this->get_assessment_attachment_details($data);
        }        
        if(!empty($row)) $status =  true; else $status = false;
        echo json_encode(["status" => $status, "data" => $dataResult]);
    }

    /*
    * Add/Update health assisstance
    */
    function save_health_assisstance(){
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;

        if(!isset($data['not_applicable']) && (!isset($data['diabetes']) &&
            !isset($data['asthma']) && !isset($data['dietry_requirements']) && !isset($data['alergies']) && !isset($data['bladder_bowel_care']) &&
             !isset($data['pressure_care']) && !isset($data['other']) &&
             !isset($data['epilepsy']) && !isset($data['stoma']) && !isset($data['peg_pej']) && !isset($data['anaphylaxis']) && !isset($data['breath_assist']) && !isset($data['mental_health']) && !isset($data['nursing_service']))) {

            $return = ['status'=>false,'msg'=>'Please select atleast one option to continue.'];
        } else if ($data['other_label'] != '' && (!isset($data['other'])) ) {
            $return = ['status'=>false,'msg'=> 'Please select others field option to continue.'];
        }
        else if(isset($data['nursing_service']) && $data['nursing_service'] == 2 && (!isset($data['nursing_service_reason']) || !$data['nursing_service_reason'])) {
            $return = ['status'=>false,'msg'=>'Please provide the community nursing services'];
        }
        else{
            $result = $this->Need_assessment_model->save_health_assisstance($data,$adminId);
            $return = ['status'=>true,'msg'=>'Health updated successfully.'];
        }
        // Log
        $need_assessment_id = $data['need_assessment_id']??0;
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Health assistance is updated under Assessment ID %s by %s", $need_assessment_id, $adminName));
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($adminId);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
        echo json_encode($return);
        exit();
    }

    /*
    * get data from health assistance by passing
    * need_assessment_id
    */
    function get_selected_health_assistance()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $row = $this->basic_model->get_row('need_assessment_health', ["not_applicable", 'diabetes','epilepsy','asthma','dietry_requirements','alergies','bladder_bowel_care',
            'pressure_care','stoma','other_label','other','peg_pej', 'anaphylaxis',
            'breath_assist', 'mental_health', 'nursing_service_reason',
            'nursing_service'],['archive'=>0,'need_assessment_id'=>$data['need_assessment_id']]);
        if(!empty($row)) $status =  true; else $status = false;
        echo json_encode(["status" => $status, "data" => $row]);
    }

    /**
     * fetching the community access information for any need assistant
     */
    function get_selected_community_access()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $row = $this->basic_model->get_row('need_assessment_community_access', array("*"),['archive'=>0,'need_assessment_id'=>$data['need_assessment_id']]);
        if(!empty($row)) $status =  true; else $status = false;
        echo json_encode(["status" => $status, "data" => $row]);
    }

    /*
    * Add/Update equipment assisstance
    */
    function save_equipment_assisstance(){
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;
        //
        if((!isset($data['not_applicable'])) && (!isset($data['walking_stick']) && !isset($data['wheel_chair']) && !isset($data['model_brand']) && !isset($data['shower_chair']) && !isset($data['transfer_aides']) && !isset($data['daily_safety_aids']) && !isset($data['walking_frame']) && !isset($data['type']) && !isset($data['weight']) && !isset($data['toilet_chair']) && !isset($data['hoist_sling']) && !isset($data['other'])  )){
            $return = ['status'=>false,'msg'=>'Please select atleast one option to continue.'];
        }else{
            $result = $this->Need_assessment_model->save_equipment_assisstance($data,$adminId);
            $return = ['status'=>true,'msg'=>'Equipment updated successfully.'];
        }
        // Log
        $need_assessment_id = $data['need_assessment_id']??0;
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Equipment assistance is updated under Assessment ID %s by %s", $need_assessment_id, $adminName));
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($adminId);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
        echo json_encode($return);
        exit();
    }

    /*
    * get data from equipment assistance by passing
    * need_assessment_id
    */
    function get_selected_equipment_assistancei()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $row = $this->basic_model->get_row('need_assessment_equipment', ["not_applicable", 'walking_stick','wheel_chair','model_brand','shower_chair','transfer_aides','daily_safety_aids','walking_frame','type','weight','toilet_chair','hoist_sling','other','daily_safety_aids_description','other_description','hoist_sling_description', 'transfer_aides_description'],['archive'=>0,'need_assessment_id'=>$data['need_assessment_id']]);
        if(!empty($row)) $status =  true; else $status = false;
        echo json_encode(["status" => $status, "data" => $row]);
    }

    /*
    * get Diagnosis using Snow mad API
    * conceptId and id are same
    *
    */
    public function get_diagnosis() {
        $reqData = request_handler('access_crm');
        $post_data = isset($reqData->data->srch_box) ? $reqData->data->srch_box : '';
        if(empty($post_data)){
            echo json_encode(['status'=>false,'msg'=>'Please type something to search.']);
            exit();
        }
        $this->load->library('snow_med');
        $this->loges->setTitle('Time Before Library Search For Get Diagnosis'  . ' ' . $reqData->data->need_assessment_id .' '. date('Y-m-d H:i:s'));
        $this->loges->setDescription(json_encode($reqData->data));
        $this->loges->setUserId($reqData->adminId);
        $this->loges->setCreatedBy($reqData->adminId);
        $this->loges->createLog();
        $srch_record = $this->snow_med->search_diagnosis($post_data);
        $this->loges->setTitle('Time After Library Search For Search Diagnosis' . ' '. $reqData->data->need_assessment_id .' '. date('Y-m-d H:i:s'));
        $this->loges->setDescription(json_encode($reqData->data));
        $this->loges->setUserId($reqData->adminId);
        $this->loges->setCreatedBy($reqData->adminId);
        $this->loges->createLog();
        $rows = array();
        $object = json_decode($srch_record);
        if (isset($object) && empty($object->items)) {
            echo json_encode(['status'=>false,'msg'=>'No result found']);
            exit();
        }
        else{
            $row_data = $this->basic_model->get_result('need_assessment_diagnosis', ['archive'=>0,'need_assessment_id'=>$reqData->data->need_assessment_id],["concept_id as conceptId"],'',true);
            $already_selected_concept = [];
            if(!empty($row_data)){
                $already_selected_concept = array_column($row_data, 'conceptId');
            }

            if ($object->items) {
                foreach ($object->items as $val) {
                    if(!in_array($val->concept->conceptId, $already_selected_concept)){
                        $uid = $val->concept->conceptId.'_'.preg_replace('/s+/', '',$val->concept->pt->term);
                        $rows[] = array('label' => $val->concept->pt->term, 'id' => $val->concept->id, 'conceptId' => $uid,'parent'=>'parent','child_count'=>'1','search_term'=>$val->term,'selected'=>false,'plan_end_Date'=>'');
                    }
                }

                $rows = my_unique_array($rows,'id');
            }
            echo json_encode(['status'=>true,'data'=>$rows]);
            exit();
        }
    }

    /*
    *Save diagnosis
    *with needassessment id
    */
    function save_diagnosis() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;

        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        #pr($reqData->data->rows);
        if (!empty($reqData->data->rows)) {
            $data = obj_to_arr($reqData->data->rows);
            $user_id = $adminId;
            if(property_exists($reqData->data, 'user_id')){
                $user_id =  $reqData->data->user_id;
            }
            $validation_rules = [
                array('field' => 'required_field', 'label' => 'required_field', 'rules' => 'callback_validate_diagnosis[' . json_encode($data) . ']'),
            ];

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $data['need_assessment_id'] = $reqData->data->need_assessment_id;
                $needAssessmentId = $this->Need_assessment_model->save_diagnosis($data, $adminId,$user_id);

                if ($needAssessmentId) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    $succes_msg = 'Diagnosis saved successfully.';
                    $this->loges->setTitle("Diagnosis saved " . " by " . $adminName);

                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->createLog(); // create log

                    $response = ['status' => true, 'msg' => $succes_msg];
                } else {
                    $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }
        echo json_encode($response);
        exit();
    }

    public function validate_diagnosis($data, $requestData)
    {
        $dataAry = json_decode($requestData);
        if (!empty($dataAry))
        {
            foreach ($dataAry as $val) {
                if ($val->selected == 1) {
                    if($val->current_plan == 1)
                    {
                        if(!property_exists($val,'plan_end_date') || $val->plan_end_date==''){
                            $this->form_validation->set_message('validate_diagnosis', 'Plan End date is required for Diagnosis - '.$val->label);
                            return false;
                        }
                    }
                }
            }
        }
    }

    /*
     * its use for update need assessment status
     *
     * return type josn
     * return object status: true
     */
    function update_status_need_assessment(){
        $reqData = request_handler('access_crm');

	    $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;

            $validation_rules = [
                array('field' => 'need_assessment_id', 'label' => 'need assessment id', 'rules' => "required"),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required'),
            ];

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $res = $this->Need_assessment_model->update_status_need_assessment($data, $adminId);

                $response = $res;
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }
        echo json_encode($response);
        exit();
    }

    /*
    * get diagnosis data by passing
    * need_assessment_id
    */
    function get_selected_diagnosis_assistance_old()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $this->db->select("true as selected", false);
        $row = $this->basic_model->get_result('need_assessment_diagnosis', ['archive'=>0,'need_assessment_id'=>$data['need_assessment_id']],["concept_id as conceptId","concept_id as id", 'diagnosis as label','support_level','current_plan','plan_end_date','impact_on_participant','id as incr_id_diagnosis','search_term', 'primary_disability']);
        if(!empty($row)) $status =  true; else $status = false;
        echo json_encode(["status" => $status, "data" => $row]);
    }

    /*
    * get diagnosis data by passing
    * need_assessment_id
    */
    function get_selected_diagnosis_assistance()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $this->db->select("true as selected", false);
        $this->db->select(["nsd.concept_id as conceptId","nsd.concept_id as id", 'nsd.diagnosis as label','nsd.support_level','nsd.current_plan','nsd.plan_end_date','nsd.impact_on_participant','nsd.id as incr_id_diagnosis','nsd.search_term','nsd.updated','nsd.updated_by', 'primary_disability']);
        $this->db->from("tbl_need_assessment_diagnosis as nsd");
        $this->db->select("(select concat_ws(' ',firstname,lastname) from tbl_member as sub_m where sub_m.uuid = nsd.updated_by) as admin_name");

        $this->db->where(['nsd.need_assessment_id' => $data['need_assessment_id'] , 'nsd.archive'=>0]);
        $row = $this->db->get()->result();
        if(!empty($row)) $status =  true; else $status = false;
        echo json_encode(["status" => $status, "data" => $row, "user_id"=>$reqData->adminId]);
    }

    /*
    * get all reference data for need assestment
    * Preferences Assistance
    */
    function get_reference_data()
    {
        $reqData = request_handler('access_crm');
        $rows["likes"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>2],['id','display_name as label']);
        $rows["dislikes"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>2],['id','display_name as label']);
        $rows["language"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>14],['id','display_name as label']);
        $rows["culture"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>15],['id','display_name as label']);
        $rows["intrest"] = $this->basic_model->get_result('references', ['archive'=>0,'type'=>2],['id','display_name as label']);
        echo json_encode(["status" => true, "data" => $rows]);
    }

    /*
    * its use for update need assessment status
    */
    function save_preference_assisstance(){
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;

            if(!isset($data['prefered_start_date']) && !isset($data['support_worker_gender']) && !isset($data['known_unknown_worker']) && !isset($data['meet_greet_required']) && !isset($data['shadow_shift']) && !isset($data['cancel_shift']) && !isset($data['worker_available']) && !isset($data['hs_weekday']) && !isset($data['hs_saturday']) && !isset($data['hs_sunday']) && !isset($data['hs_sleep_over']) && !isset($data['hs_active_night']) && !isset($data['hs_public_holiday']) && !isset($data['as_weekday']) && !isset($data['as_saturday']) && !isset($data['as_sunday']) && !isset($data['as_sleep_over']) && !isset($data['as_active_night']) && !isset($data['as_public_holiday']) && empty($data['likeSelection']) && empty($data['disLikesSelection']) && empty($data['langSelection']) && empty($data['cultureSelection']) && empty($data['intrestSelection']) && !isset($data['preferences_not_applicable']) ){
                $response = ['status'=>false,'msg'=>'Please select atleast one option to continue.'];
            }else{
                 $res = $this->Need_assessment_model->save_preference_assisstance($data, $adminId);
                $response = ($res)?['status' => true,'msg'=>'Preferences save successfully.']:['status' => false,'msg'=>'Something went wrong.'];
            }
        }
        echo json_encode($response);
        exit();
    }

    /*
    * get data from Preference assistance by passing
    * need_assessment_id
    */
    function get_selected_preference_assistance()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);

        $result = $this->Need_assessment_model->get_selected_preference_assistance($data);
        if($result) $status = true; else $status = false;
        echo json_encode(['status'=>$status,'data'=>$result]);
        exit();
    }
    function printna(){
            $data['need_assessment_id'] = $this->input->get('need_assessment_id');
            $page_title = $this->input->get('page_title');
            // echo '<pre>';print_r($this->input->get());die;
            $diagnosisdatas = $this->basic_model->get_result('need_assessment_diagnosis', ['archive'=>0,'need_assessment_id'=>$this->input->get('need_assessment_id')],["concept_id as conceptId","concept_id as id", 'diagnosis as label','support_level','current_plan','plan_end_date','impact_on_participant','id as incr_id_diagnosis','search_term']);
            $preferencedatas =$this->Need_assessment_model->get_selected_preference_assistance($data);
            $communicationdatas  = $this->basic_model->get_row('need_assessment_communication', array("*"),['archive'=>0,'need_assessment_id'=>$this->input->get('need_assessment_id')]);
            $mealtimedatas = $this->basic_model->get_row('need_assessment_mealtime', ["not_applicable", 'risk_choking','risk_aspiration','mealtime_assistance_plan'],['archive'=>0,'need_assessment_id'=>$this->input->get('need_assessment_id')]);
            $mobilitydatas = $this->basic_model->get_row('need_assessment_mobility', array("*"),['archive'=>0,'need_assessment_id'=>$this->input->get('need_assessment_id')]);
            $equipmentdatas = $this->basic_model->get_row('need_assessment_equipment', ["not_applicable", 'walking_stick','wheel_chair','model_brand','shower_chair','transfer_aides','daily_safety_aids','walking_frame','type','weight','toilet_chair','hoist_sling','other','daily_safety_aids_description','other_description','hoist_sling_description'],['archive'=>0,'need_assessment_id'=>$this->input->get('need_assessment_id')]);
            $healthdatas = $this->basic_model->get_row('need_assessment_health', ["not_applicable", 'diabetes','epilepsy','asthma','dietry_requirements','alergies','bladder_bowel_care','pressure_care','stoma','other_label','other'],['archive'=>0,'need_assessment_id'=>$this->input->get('need_assessment_id')]);
            $medicationdatas = $this->basic_model->get_row('need_assessment_medication', ["not_applicable", 'medication_administration','medication_emergency','reduce_concern'],['archive'=>0,'need_assessment_id'=>$this->input->get('need_assessment_id')]);
            $personalcaredatas = $this->basic_model->get_row('need_assessment_personalcare', array("*"),['archive'=>0,'need_assessment_id'=>$this->input->get('need_assessment_id')]);
            $communityaccessdatas = $this->basic_model->get_row('need_assessment_community_access', array("*"),['archive'=>0,'need_assessment_id'=>$this->input->get('need_assessment_id')]);
            $allDatas['diagnosisdatas'] = $diagnosisdatas;
            $allDatas['preferencedatas'] = $preferencedatas;
            $allDatas['communicationdatas'] = $communicationdatas;
            $allDatas['mealtimedatas'] = $mealtimedatas;
            $allDatas['mobilitydatas'] =  $mobilitydatas;
            $allDatas['equipmentdatas'] = $equipmentdatas;
            $allDatas['healthdatas'] = $healthdatas;
            $allDatas['medicationdatas'] = $medicationdatas;
            $allDatas['personalcaredatas'] = $personalcaredatas;
            $allDatas['communityaccessdatas'] = $communityaccessdatas;
            $print_datas = json_decode(json_encode($allDatas),true);
            $diagnosisdatas_print = $print_datas['diagnosisdatas'];
            $preferencedatas_print = $print_datas['preferencedatas'];
            $communicationdatas_print = $print_datas['communicationdatas'];
            $mealtimedatas_print = $print_datas['mealtimedatas'];
            $mobilitydatas_print = $print_datas['mobilitydatas'];
            $equipmentdatas_print = $print_datas['equipmentdatas'];
            $healthdatas_print = $print_datas['healthdatas'];
            $medicationdatas_print = $print_datas['medicationdatas'];
            $personalcaredatas_print = $print_datas['personalcaredatas'];
            $communityaccessdatas_print = $print_datas['communityaccessdatas'];

            if(!empty($preferencedatas_print['selected_preference'])){
              $selected_pref_datas = $preferencedatas_print;
            }else{
              $selected_pref_datas = [];
            }
            // echo '<pre>';print_r($selected_pref_datas['selected_preference']);die;
            $printHtml="";
            $printHtml="<style>
            @page Section1 {size:595.45pt 841.7pt; margin:1.0in 1.25in 1.0in 1.25in;mso-header-margin:.5in;mso-footer-margin:.5in;mso-paper-source:0;}
            div.Section1 {page:Section1;}
            @page Section2 {size:841.7pt 595.45pt;mso-page-orientation:landscape;margin:1.25in 1.0in 1.25in 1.0in;mso-header-margin:.5in;mso-footer-margin:.5in;mso-paper-source:0;}
            div.Section2 {page:Section2;}
            </style>";
            $printHtml.="<div class=Section2>";
            $printHtml.="<h3 style='text-align:center'>Need-Assessments - ".$page_title."</h3>";
            // Diagnosis Start
            $printHtml.="<h4>Diagnosis</h4>";
            $printHtml.="<style>
            table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
            }

            td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
            }

            tr:nth-child(even) {
            background-color: #dddddd;
            }
            </style>";
            $printHtml.="<table>
            <tr>
            <th>Diagnosis</th>
            <th>Level Of Support</th>
            <th>Current Plan</td>
            <th>Plan End Date</td>
            <th>Impact On Participant</th>
           </tr>";
           if((!empty($diagnosisdatas_print)) && count($diagnosisdatas_print) > 0){
              foreach ($diagnosisdatas_print as $diagnosisdata) {
            $support_level_val = $diagnosisdata['support_level'];
            $support_level_array = array('1'=>"High",'2'=>"Medium",'3'=>"Low");
            $impact_on_participant_val = $diagnosisdata['impact_on_participant'];
            $current_plan_val = $diagnosisdata['current_plan'];
            if($current_plan_val == 1) $current_plan = "Yes";else $current_plan = "No";
            $impact_on_participant_array = array('1'=>"Severe",'2'=>"Moderate",'3'=>"Mild",'4'=>'Managed by medication');
            $printHtml.="<tr>";
            $support_level = "";
            if(array_key_exists($support_level_val,$support_level_array)){
                $support_level = $support_level_array[$support_level_val];
            }
            $impact_on_participant = "";
            if(array_key_exists($impact_on_participant_val,$impact_on_participant_array)){
                $impact_on_participant = $impact_on_participant_array[$impact_on_participant_val];
            }
            $printHtml.="<td>".$diagnosisdata['label']."</td>";
            $printHtml.="<td>".$support_level."</td>";
            $printHtml.="<td>".$current_plan."</td>";
            $printHtml.="<td>".date("d/m/Y", strtotime($diagnosisdata['plan_end_date']))."</td>";
            $printHtml.="<td>".$impact_on_participant."</td>";
            $printHtml.="</tr>";
            }
            }else{
            $printHtml.="<tr><td></td><td></td><td></td><td></td><td></td></tr>";
            }
            $printHtml.="</table>";
            // Diagnosis End
           // Preferences Start

            $printHtml.="<h4>Preference</h4>";
            $printHtml.="<table>
            <tr>
            <th>Likes</th>
            <th>Dislikes</th>
            <th>Support Worker Language</th>
            <th>Support Worker Culture</th>
            <th>Support Worker Interests</th>
            <th>Prefered Start Date</th>
            <th>Support Worker Gender</th>
            </tr>
            <tr>";
            // Likes Start
            if(count($selected_pref_datas)>0 && array_key_exists("1",$selected_pref_datas['selected_preference'])){
                $selected_preference_likes_prints = $selected_pref_datas['selected_preference'][1];
                $printHtml.="<td>";
                $printHtml.= "<ul>";
                foreach ($selected_preference_likes_prints as $selected_preference_like) {
                $printHtml.= "<li>".$selected_preference_like['label']."</li>";
                }
                $printHtml.="</ul>";
                $printHtml.="</td>";
                }else{
                $printHtml.="<td>";
                $printHtml.="";
                $printHtml.="</td>";
                }
                // Likes End
                // DisLikes Start
                if(count($selected_pref_datas)>0 &&  array_key_exists("2",$selected_pref_datas['selected_preference'])){
                $selected_preference_dislikes_prints = $selected_pref_datas['selected_preference'][2];
                $printHtml.="<td>";
                $printHtml.= "<ul>";
                foreach ($selected_preference_dislikes_prints as $selected_preference_dislikes) {
                $printHtml.= "<li>".$selected_preference_dislikes['label']."</li>";
                }
                $printHtml.="</ul>";
                $printHtml.="</td>";
                }else{
                $printHtml.="<td>";
                $printHtml.="";
                $printHtml.="</td>";
            }
            // DisLikes End
            // Lang Start
            if(count($selected_pref_datas)>0 &&  array_key_exists("3",$selected_pref_datas['selected_preference'])){
                $selected_preference_lang_prints = $selected_pref_datas['selected_preference'][3];
                $printHtml.="<td>";
                $printHtml.= "<ul>";
                foreach ($selected_preference_lang_prints as $selected_preference_lang) {
                $printHtml.= "<li>".$selected_preference_lang['label']."</li>";
                }
                $printHtml.="</ul>";
                $printHtml.="</td>";
                }else{
                $printHtml.="<td>";
                $printHtml.="";
                $printHtml.="</td>";
                }
            // Lang End
            // Culture Start
            if(count($selected_pref_datas)>0 &&  array_key_exists("4",$selected_pref_datas['selected_preference'])){
                $selected_preference_culture_prints = $selected_pref_datas['selected_preference'][4];
                $printHtml.="<td>";
                $printHtml.= "<ul>";
                foreach ($selected_preference_culture_prints as $selected_preference_culture) {
                $printHtml.= "<li>".$selected_preference_culture['label']."</li>";
                }
                $printHtml.="</ul>";
                $printHtml.="</td>";
            }else{
                $printHtml.="<td>";
                $printHtml.="";
                $printHtml.="</td>";
            }
            // Culture End
            // Interest Start
            if(count($selected_pref_datas)>0 && array_key_exists("5",$selected_pref_datas['selected_preference'])){
                $selected_preference_interest_prints = $selected_pref_datas['selected_preference'][5];
                $printHtml.="<td>";
                $printHtml.= "<ul>";
                foreach ($selected_preference_interest_prints as $selected_preference_interest) {
                $printHtml.= "<li>".$selected_preference_interest['label']."</li>";
                }
                $printHtml.="</ul>";
                $printHtml.="</td>";
            }
            else{
                $printHtml.="<td>";
                $printHtml.="";
                $printHtml.="</td>";
            }
            // Interest End
            // Start Date
            if($preferencedatas_print){
                if($preferencedatas_print['prefered_start_date'] != "0000-00-00"){
                $printHtml.="<td>".date("m/d/Y", strtotime($preferencedatas_print['prefered_start_date']))."</td>";
                }else{
                $printHtml.="<td></td>";
                }
            }
            else{
                $printHtml.="<td></td>";
            }
            // Gender Start
            $genderarray = array('1'=>"Female",'2'=>"Male",'3'=>"Either");
            $gender = "";
            if(array_key_exists($preferencedatas_print['support_worker_gender'],$genderarray)){
            $gender = $genderarray[$preferencedatas_print['support_worker_gender']];
            }
            $printHtml.="<td>".$gender."</td>";
            $printHtml.="</tr>";
            $printHtml.="</table>";
            // Gender End


            //Vacant Shifts Start
            $printHtml.="<table>
            <tr>
            <th>Vacant Shifts</th>
            <th>In Home Support</th>
            <th>In Home Shift Tasks</th>
            <th>Community Access Support</th>
            <th>Community Access Shift Tasks</th>
            <th>Sleep Over Details</th>
            <th>Active Night Details</th>
            </tr>";
            $printHtml.="<tr>";
            $printHtml.="<td>";
            $printHtml.="<ul>";
            if($preferencedatas_print['known_unknown_worker'] == 1){
            $printHtml.="<li>Can send known and unknown workers</li>";
            }
            if($preferencedatas_print['meet_greet_required'] == 1){
            $printHtml.="<li>Meet and Greet required for all new staff</li>";
            }
            if($preferencedatas_print['shadow_shift'] == 1){
            $printHtml.="<li>Shadow Shift required for all new staff</li>";
            }
            if($preferencedatas_print['worker_available'] == 1){
            $printHtml.="<li>Contact if no known worker available</li>";
            }
            if($preferencedatas_print['cancel_shift'] == 1){
            $printHtml.="<li>Cancel shift if no known worker available</li>";
            }
            $printHtml.="</ul>";
            $printHtml.="</td>";
            //Vacant Shifts End

            //Home Support Start
            $printHtml.="<td>";
            $printHtml.="<ul>";
            if($preferencedatas_print['hs_weekday'] == 1){
            $printHtml.="<li>Weekday</li>";
            }
            if($preferencedatas_print['hs_saturday'] == 1){
                $printHtml.="<li>Saturday</li>";
            }
            if($preferencedatas_print['hs_sunday'] == 1){
                $printHtml.="<li>Sunday</li>";
            }
            if($preferencedatas_print['hs_sleep_over'] == 1){
                $printHtml.="<li>Sleep Over</li>";
            }
            if($preferencedatas_print['hs_active_night'] == 1){
                $printHtml.="<li>Active Night</li>";
            }
            if($preferencedatas_print['hs_public_holiday'] == 1){
                $printHtml.="<li>Public Holiday</li>";
            }
            $printHtml.="</ul>";
            $printHtml.="</td>";
            $printHtml.="<td>".$preferencedatas_print['in_home_shift_tasks']."</td>";
            //Home Support SEnd

            // Community Access Support Start
            $printHtml.="<td>";
            $printHtml.="<ul>";
            if($preferencedatas_print['as_weekday'] == 1){
            $printHtml.="<li>Weekday</li>";
            }
            if($preferencedatas_print['as_saturday'] == 1){
                $printHtml.="<li>Saturday</li>";
            }
            if($preferencedatas_print['as_sunday'] == 1){
                $printHtml.="<li>Sunday</li>";
            }
            if($preferencedatas_print['as_sleep_over'] == 1){
                $printHtml.="<li>Sleep Over</li>";
            }
            if($preferencedatas_print['as_active_night'] == 1){
                $printHtml.="<li>Active Night</li>";
            }
            if($preferencedatas_print['as_public_holiday'] == 1){
                $printHtml.="<li>Public Holiday</li>";
            }
            $printHtml.="</ul>";
            $printHtml.="</td>";
            $printHtml.="<td>".$preferencedatas_print['community_access_shift_tasks']."</td>";
            // Community Access Support End
            $printHtml.="<td>".$preferencedatas_print['sleep_over_details']."</td>";
            $printHtml.="<td>".$preferencedatas_print['active_night_details']."</td>";
            $printHtml.="</tr>";
            $printHtml.="</table>";
            $printHtml.="</div>";


        // Preferences End

        //Communication start
            $printHtml.="<h4>Communication</h4>";
            $printHtml.="<table>
            <tr>
            <th>Communication Type</th>
            <th>Interpreter</th>
            <th>Cognition and Comprehension</th>
            <th>Can the participant follow verbal instructions?</th>
            <th>Hearing impaired? </th>
            <th>Visually impaired?</th>
            </tr>";
            $printHtml.="<tr>";
            //Communication Type Start
            $printHtml.="<td>";
            $printHtml.="<ul>";
            if($communicationdatas_print['communication_verbal'] == 1){
                $printHtml.="<li>Verbal</li>";
            }
            if($communicationdatas_print['communication_book'] == 1){
                $printHtml.="<li>Communication book/board</li>";
            }
            if($communicationdatas_print['communication_nonverbal'] == 1){
                $printHtml.="<li>Non-verbal</li>";
            }
            if($communicationdatas_print['communication_electric'] == 1){
                $printHtml.="<li>Electronic device</li>";
            }
            if($communicationdatas_print['communication_vocalization'] == 1){
                $printHtml.="<li>Vocalization / Gestures</li>";
            }
            if($communicationdatas_print['communication_sign'] == 1){
                $printHtml.="<li>Sign</li>";
            }
            if($communicationdatas_print['communication_other'] == 1){
                $printHtml.="<li>Others</li>";
                $printHtml.="<p><b>Others Description:</b></p>";
                $printHtml.= $communicationdatas_print['communication_other_desc'];
            }
            $printHtml.="</ul>";
            $printHtml.="</td>";
            //Communication Type End
            //Interpreter Start
            $interpreterarray = array('1'=>"No",'2'=>"Yes");
            $interpreter = "";
            if(array_key_exists($communicationdatas_print['interpreter'],$interpreterarray)){
             $interpreter = $interpreterarray[$communicationdatas_print['interpreter']];
            }
             $printHtml.="<td>".$interpreter."</td>";
            //Interpreter End

            //cognition Start
            $cognitionarray = array('1'=>"Very good",'2'=>"Good",'3'=>"Fair",'4'=>"Poor");
            $cognition = "";
            if(array_key_exists($communicationdatas_print['cognition'],$cognitionarray)){
             $cognition = $cognitionarray[$communicationdatas_print['cognition']];
            }
             $printHtml.="<td>".$cognition."</td>";
             //cognition End

            //Verbal Inst Start
            if($communicationdatas_print['instructions'] == 1)
            {
            $printHtml.="<td>";
            $printHtml.="<p>No</p>";
            $printHtml.="<div><b>Description:</b></div><br>";
            $printHtml.= $communicationdatas_print['instructions_desc'];
            $printHtml.="</td>";

            }
            if($communicationdatas_print['instructions'] == 2)
            {
                $printHtml.="<td>Yes</td>";
            }
            //Verbal Inst End

            //Hearing impaired? Start
            if($communicationdatas_print['hearing_impared'] == 1)
            {
                $printHtml.="<td>No</td>";
            }
            if($communicationdatas_print['hearing_impared'] == 2)
            {
                $printHtml.="<td>";
                $printHtml.="<p>Yes</p>";
                $printHtml.="<div><b>Description:</b></div><br>";
                $printHtml.= $communicationdatas_print['hearing_impared_desc'];
                $printHtml.="</td>";

            }
            //Hearing impaired? End

            //Visually impaired? Start
            if($communicationdatas_print['visually_impared'] == 1)
            {
                $printHtml.="<td>No</td>";
            }
            if($communicationdatas_print['visually_impared'] == 2)
            {
            $printHtml.="<td>";
            $printHtml.="<p>Yes</p>";
            $printHtml.="<div><b>Description:</b></div><br>";
            $printHtml.= $communicationdatas_print['visually_impared_desc'];
            $printHtml.="</td>";

            }
            //Visually impaired? End
            $printHtml.="</tr>";
            $printHtml.="</table>";
            //Communication End

            //Mealtime Start

            $printHtml.="<br>";
            $printHtml.="<h4>Mealtime</h4>";
            $printHtml.="<table>
            <tr>
            <th>Not applicable</th>
            <th>Risk of choking</th>
            <th>Risk of aspiration</th>
            <th>Mealtime assisstance plan</th>
            </tr>
            <tr>";
         $not_applicable='No';
         if($mealtimedatas_print['not_applicable'] == 1)
         {
           $not_applicable ="Yes";
         }
        $printHtml.="<td>".$not_applicable."</td>";

        $mealtime_risk_chokingarray = array('1'=>"No",'2'=>"Yes");
        $mealtime_risk_choking = "";
         if(array_key_exists($mealtimedatas_print['risk_choking'],$mealtime_risk_chokingarray)){
          $mealtime_risk_choking = $mealtime_risk_chokingarray[$mealtimedatas_print['risk_choking']];
         }
         $printHtml.="<td>".$mealtime_risk_choking."</td>";

        $mealtime_risk_aspirationarray = array('1'=>"No",'2'=>"Yes");
        $mealtime_risk_aspiration = "";
         if(array_key_exists($mealtimedatas_print['risk_aspiration'],$mealtime_risk_aspirationarray)){
          $mealtime_risk_aspiration = $mealtime_risk_aspirationarray[$mealtimedatas_print['risk_aspiration']];
         }
         $printHtml.="<td>".$mealtime_risk_aspiration."</td>";

         $mealtime_assistance_plannarray = array('1'=>"No",'2'=>"Yes");
          $mealtime_assistance_plan = "";
          if(array_key_exists($mealtimedatas_print['mealtime_assistance_plan'],$mealtime_assistance_plannarray)){
           $mealtime_assistance_plan = $mealtime_assistance_plannarray[$mealtimedatas_print['mealtime_assistance_plan']];
          }
          $printHtml.="<td>".$mealtime_assistance_plan."</td>";

         $printHtml.="</tr>";
         $printHtml.="</table>";
        //Mealtime End
        //Mobility Start
        $printHtml.="<br>";
        $printHtml.="<h4>Mobility</h4>";
        $printHtml.="<table>
         <tr>
        <th>Not applicable</th>
        <th>In/out of bed</th>
        <th>In/out of shower/bath</th>
        <th>On/off toilet</th>
        <th>In/out of chair/wheelchair</th>
        <th>In/out of vehicle</th>
        </tr>
        <tr>";
        $not_applicable='No';
        if($mobilitydatas_print['not_applicable'] == 1)
        {
           $not_applicable ="Yes";
        }
        $printHtml.="<td>".$not_applicable."</td>";

        $mobility_all_options_array = array('1'=>"Not applicable",'2'=>"With assistance",'3'=>"With supervision",'4'=>"Independant");
        $mobility_inout_bed = "";
         if(array_key_exists($mobilitydatas_print['inout_bed'],$mobility_all_options_array)){
          $mobility_inout_bed = $mobility_all_options_array[$mobilitydatas_print['inout_bed']];
         }
         $printHtml.="<td>".$mobility_inout_bed."</td>";

         $mobility_inout_shower = "";
         if(array_key_exists($mobilitydatas_print['inout_shower'],$mobility_all_options_array)){
          $mobility_inout_shower = $mobility_all_options_array[$mobilitydatas_print['inout_shower']];
         }
         $printHtml.="<td>".$mobility_inout_shower."</td>";

         $mobility_onoff_toilet = "";
         if(array_key_exists($mobilitydatas_print['onoff_toilet'],$mobility_all_options_array)){
          $mobility_onoff_toilet = $mobility_all_options_array[$mobilitydatas_print['onoff_toilet']];
         }
         $printHtml.="<td>".$mobility_onoff_toilet."</td>";

         $mobility_inout_chair = "";
         if(array_key_exists($mobilitydatas_print['inout_chair'],$mobility_all_options_array)){
          $mobility_inout_chair = $mobility_all_options_array[$mobilitydatas_print['inout_chair']];
         }
         $printHtml.="<td>".$mobility_inout_chair."</td>";

         $mobility_inout_vehicle = "";
         if(array_key_exists($mobilitydatas_print['inout_vehicle'],$mobility_all_options_array)){
          $mobility_inout_vehicle = $mobility_all_options_array[$mobilitydatas_print['inout_vehicle']];
         }
          $printHtml.="<td>".$mobility_inout_vehicle."</td>";
          $printHtml.="</tr>";
          $printHtml.="</table>";
           //Mobility End
            // Equipment/Aides Start
            $printHtml.="<br>";
            $printHtml.="<h4>Equipment/Aides</h4>";
            $printHtml.="<table>
            <tr>
            <th>Not applicable</th>
            <th>Walking Stick</th>
            <th>Walking frame</th>
            <th>Wheel chair</th>
            <th>Type</th>
            <th>Model/Brand</th>
            <th>Weight</th>
            </tr>
             <tr>";
            $not_applicable='No';
            if($equipmentdatas_print['not_applicable'] == 1)
            {
                $not_applicable ="Yes";
            }
          $printHtml.="<td>".$not_applicable."</td>";

          $equipmentdatas_yesno_array = array('1'=>"No",'2'=>"Yes");
          //equpment walking stick start
          $equipmentdatas_walking_stick = "";
          if(array_key_exists($equipmentdatas_print['walking_stick'],$equipmentdatas_yesno_array)){
           $equipmentdatas_walking_stick = $equipmentdatas_yesno_array[$equipmentdatas_print['walking_stick']];
          }
          $printHtml.="<td>".$equipmentdatas_walking_stick."</td>";
          //equpment walking stick end

         //equipment  walking_frame  start
          $equipmentdatas_walking_frame = "";
          if(array_key_exists($equipmentdatas_print['walking_frame'],$equipmentdatas_yesno_array)){
          $equipmentdatas_walking_frame = $equipmentdatas_yesno_array[$equipmentdatas_print['walking_frame']];
          }
          $printHtml.="<td>".$equipmentdatas_walking_frame."</td>";
          //equipment walking_frame stick end

           //equipment  wheel_chair  start
          $equipmentdatas_wheel_chair = "";
          if(array_key_exists($equipmentdatas_print['wheel_chair'],$equipmentdatas_yesno_array)){
           $equipmentdatas_wheel_chair = $equipmentdatas_yesno_array[$equipmentdatas_print['wheel_chair']];
          }
          $printHtml.="<td>".$equipmentdatas_wheel_chair."</td>";
          //equipment wheel_chair stick end

           //equipment  type  start
           $equipmentdatas_type_array = array('1'=>"Electric",'2'=>"Motorised");
           $equipmentdatas_type = "";
           if(array_key_exists($equipmentdatas_print['type'],$equipmentdatas_type_array)){
            $equipmentdatas_type = $equipmentdatas_type_array[$equipmentdatas_print['type']];
           }
           $printHtml.="<td>".$equipmentdatas_type."</td>";

            //equipment type end
            //equipment  model_brand  start
           $printHtml.="<td>".$equipmentdatas_print['model_brand']."</td>";
            //equipment  model_brand   end
            //equipment  weight  start
           $printHtml.="<td>".$equipmentdatas_print['weight']."</td>";
           $printHtml.="</tr>";
           $printHtml.="</table>";
             //equipment  weight   end

             //equipment  Shower chair Start
            $printHtml.="<table>
            <tr>
            <th>Shower chair</th>
            <th>Toilet Chair</th>
            <th>Transfer Aides</th>
            <th>Hoist or Sling </th>
            <th>Daily safety aids</th>
            <th>Other</th>
            </tr>
            <tr>";
            $equipmentdatas_shower_chair = "";
             if(array_key_exists($equipmentdatas_print['shower_chair'],$equipmentdatas_yesno_array)){
              $equipmentdatas_shower_chair = $equipmentdatas_yesno_array[$equipmentdatas_print['shower_chair']];
             }
             $printHtml.="<td>".$equipmentdatas_shower_chair."</td>";

             //equipment Shower chair End

            //equipment  Toilet chair Start
            $equipmentdatas_toilet_chair = "";
            if(array_key_exists($equipmentdatas_print['toilet_chair'],$equipmentdatas_yesno_array)){
             $equipmentdatas_toilet_chair = $equipmentdatas_yesno_array[$equipmentdatas_print['toilet_chair']];
            }
            $printHtml.="<td>".$equipmentdatas_toilet_chair."</td>";
            //equipment Toilet chair End

             //equipment  transfer_aides Start
            $equipmentdatas_transfer_aides = "";
            if(array_key_exists($equipmentdatas_print['transfer_aides'],$equipmentdatas_yesno_array)){
              $equipmentdatas_transfer_aides = $equipmentdatas_yesno_array[$equipmentdatas_print['transfer_aides']];
             }
             $printHtml.="<td>".$equipmentdatas_transfer_aides."</td>";

             //equipment transfer_aides  End

             //equipment  hoist_sling Start
            $equipmentdatas_hoist_sling = "";
             if(array_key_exists($equipmentdatas_print['hoist_sling'],$equipmentdatas_yesno_array)){
               $equipmentdatas_hoist_sling = $equipmentdatas_yesno_array[$equipmentdatas_print['hoist_sling']];
              }
             $printHtml.="<td>";
             $printHtml.="<p>".$equipmentdatas_hoist_sling."</p>";
             $printHtml.="<div><b>Description:</b></div><br>";
             $printHtml.= $equipmentdatas_print['hoist_sling_description'];
             $printHtml.="</td>";
             //equipment hoist_sling  End

             //equipment  Daily safety aids Start
             $equipmentdatas_daily_safety_aids = "";
             if(array_key_exists($equipmentdatas_print['daily_safety_aids'],$equipmentdatas_yesno_array)){
               $equipmentdatas_daily_safety_aids = $equipmentdatas_yesno_array[$equipmentdatas_print['daily_safety_aids']];
              }
             $printHtml.="<td>";
             $printHtml.="<p>".$equipmentdatas_daily_safety_aids."</p>";
             $printHtml.="<div><b>Description:</b></div><br>";
             $printHtml.= $equipmentdatas_print['daily_safety_aids_description'];
             $printHtml.="</td>";
             //equipment Daily safety End

              //equipment other Start
              $equipmentdatas_other = "";
             if(array_key_exists($equipmentdatas_print['other'],$equipmentdatas_yesno_array)){
               $equipmentdatas_other = $equipmentdatas_yesno_array[$equipmentdatas_print['other']];
              }
              $printHtml.="<td>";
              $printHtml.="<p>".$equipmentdatas_other."</p>";
              $printHtml.="<div><b>Description:</b></div><br>";
              $printHtml.= $equipmentdatas_print['other_description'];
              $printHtml.="</td>";
              //equipment other End
              $printHtml.="</tr>";
              $printHtml.="</table>";
              // Equipment/Aides End

              //Health Start
            $printHtml.="<br>";
            $printHtml.="<h4>Health Support</h4>";
            $printHtml.="<table>
            <tr>
            <th>Not applicable</th>
            <th>Diabetes</th>
            <th>Epilepsy</th>
            <th>Asthma</th>
            <th>Dietry Requirements</th>
            <th>Alergies</th>
            <th>Bladder/Bowel care</th>
            <th>Pressure care</th>
            <th>Stoma</th>
            <th>Other</th>
            </tr>
            <tr>";
              $not_applicable='No';
              if($healthdatas_print['not_applicable'] == 1)
              {
                 $not_applicable ="Yes";
              }
              $printHtml.="<td>".$not_applicable."</td>";
              $healthdatas_print_data = 0;
              $health_options_array = [];
              if($healthdatas_print){
              $healthdatas_print_data = 1;
              }
              if($healthdatas_print_data == 1){
                $health_options_array = array('1'=>"No",'2'=>"Yes with health plan",'3'=>"Yes without health plan");
              }
              $diabetes = "";
              if(array_key_exists($healthdatas_print['diabetes'],$health_options_array)){
              $diabetes = $health_options_array[$healthdatas_print['diabetes']];
              }
              $epilepsy = "";
              if(array_key_exists($healthdatas_print['epilepsy'],$health_options_array)){
              $epilepsy = $health_options_array[$healthdatas_print['epilepsy']];
              }
              $asthma = "";
              if(array_key_exists($healthdatas_print['asthma'],$health_options_array)){
              $asthma = $health_options_array[$healthdatas_print['asthma']];
              }
              $dietry_requirements = "";
              if(array_key_exists($healthdatas_print['dietry_requirements'],$health_options_array)){
              $dietry_requirements = $health_options_array[$healthdatas_print['dietry_requirements']];
              }
              $alergies = "";
              if(array_key_exists($healthdatas_print['alergies'],$health_options_array)){
               $alergies = $health_options_array[$healthdatas_print['alergies']];
              }
              $bladder_bowel_care = "";
              if(array_key_exists($healthdatas_print['bladder_bowel_care'],$health_options_array)){
               $bladder_bowel_care = $health_options_array[$healthdatas_print['bladder_bowel_care']];
              }
              $pressure_care = "";
              if(array_key_exists($healthdatas_print['pressure_care'],$health_options_array)){
               $pressure_care = $health_options_array[$healthdatas_print['pressure_care']];
              }
              $stoma = "";
              if(array_key_exists($healthdatas_print['stoma'],$health_options_array)){
               $stoma = $health_options_array[$healthdatas_print['stoma']];
              }
              $other = "";
              if(array_key_exists($healthdatas_print['other'],$health_options_array)){
               $other = $health_options_array[$healthdatas_print['other']];
              }

              $printHtml.="<td>".$diabetes."</td>";
              $printHtml.="<td>".$epilepsy."</td>";
              $printHtml.="<td>".$asthma."</td>";
              $printHtml.="<td>".$dietry_requirements."</td>";
              $printHtml.="<td>".$alergies."</td>";
              $printHtml.="<td>".$bladder_bowel_care."</td>";
              $printHtml.="<td>".$pressure_care."</td>";
              $printHtml.="<td>".$stoma."</td>";
              $printHtml.="<td>";
              $printHtml.="<p>".$other."</p>";
              $printHtml.="<div><b>Description:</b></div><br>";
              $printHtml.= $healthdatas_print['other_label'];
              $printHtml.="</td>";
              $printHtml.="</tr>";
              $printHtml.="</table>";
              //Health Support End

             //Medication Start

            $printHtml.="<br>";
            $printHtml.="<h4>Medication</h4>";
            $printHtml.="<table>
            <tr>
            <th>Not Applicable</th>
            <th>Medication administration</th>
            <th>Is any of the medication prescribed as PRN/Emergency?</th>
            <th>Is this given to the participant to reduce behaviours of concern?</th>
            </tr>
            <tr>";
            $not_applicable='No';
            if($medicationdatas_print['not_applicable'] == 1)
            {
                $not_applicable ="Yes";
            }
            $printHtml.="<td>".$not_applicable."</td>";

            $medicationdatas_medication_ad = 0;
            $medicationdatas_medication_ad_options = [];
            $medication_yesno_array = array('1'=>"No",'2'=>"Yes");

            if($medicationdatas_print){
            $medicationdatas_medication_ad = 1;
            }
            if($medicationdatas_medication_ad == 1){
                $medicationdatas_medication_ad_options = array('1'=>"No medication administration",'2'=>"No medication is self administered",'3'=>"Yes medication assistance required");
            }
            $medication_administration = "";
            if(array_key_exists($medicationdatas_print['medication_administration'],$medicationdatas_medication_ad_options)){
            $medication_administration = $medicationdatas_medication_ad_options[$medicationdatas_print['medication_administration']];
            }
            $medication_emergency = "";
            if(array_key_exists($medicationdatas_print['medication_emergency'],$medication_yesno_array)){
                $medication_emergency = $medication_yesno_array[$medicationdatas_print['medication_emergency']];
            }
            $reduce_concern = "";
            if(array_key_exists($medicationdatas_print['reduce_concern'],$medication_yesno_array)){
                $reduce_concern = $medication_yesno_array[$medicationdatas_print['reduce_concern']];
            }
            $printHtml.="<td>".$medication_administration."</td>";
            $printHtml.="<td>".$medication_emergency."</td>";
            $printHtml.="<td>".$reduce_concern."</td>";
            $printHtml.="</tr>";
            $printHtml.="</table>";

            //Medication End

            //  Personal Care
            $printHtml.="<br>";
            $printHtml.="<h4>Personal Care</h4>";
            $printHtml.="<table>
            <tr>
            <th>Not Applicable</th>
            <th>Bowel care</th>
            <th>Bladder care</th>
            <th>Shower/bath care</th>
            <th>Dressing/grooming</th>
            </tr>
            <tr>";
            $not_applicable='No';
            if($personalcaredatas_print['not_applicable'] == 1)
            {
                $not_applicable ="Yes";
            }
            $printHtml.="<td>".$not_applicable."</td>";
            $personalcaredatas_print_data = 0;
            $personalcare_options_array = [];
            if($personalcaredatas){
            $personalcaredatas_print_data = 1;
            }
            if($personalcaredatas_print_data == 1){
                $personalcare_options_array = array('1'=>"Not applicable",'2'=>"With assistance",'3'=>"With supervision",'4'=>"Independant");
            }
            $bowelcare = "";
            if(array_key_exists($personalcaredatas_print['bowelcare'],$personalcare_options_array)){
            $bowelcare = $personalcare_options_array[$personalcaredatas_print['bowelcare']];
            }
            $bladdercare = "";
            if(array_key_exists($personalcaredatas_print['bladdercare'],$personalcare_options_array)){
            $bladdercare = $personalcare_options_array[$personalcaredatas_print['bladdercare']];
            }
            $showercare = "";
            if(array_key_exists($personalcaredatas_print['showercare'],$personalcare_options_array)){
            $showercare = $personalcare_options_array[$personalcaredatas_print['showercare']];
            }
            $dressing = "";
            if(array_key_exists($personalcaredatas_print['dressing'],$personalcare_options_array)){
            $dressing = $personalcare_options_array[$personalcaredatas_print['dressing']];
            }
            $teethcleaning = "";
            if(array_key_exists($personalcaredatas_print['teethcleaning'],$personalcare_options_array)){
            $teethcleaning = $personalcare_options_array[$personalcaredatas_print['teethcleaning']];
            }
            $cooking = "";
            if(array_key_exists($personalcaredatas_print['cooking'],$personalcare_options_array)){
            $cooking = $personalcare_options_array[$personalcaredatas_print['cooking']];
            }
            $eating = "";
            if(array_key_exists($personalcaredatas_print['eating'],$personalcare_options_array)){
            $eating = $personalcare_options_array[$personalcaredatas_print['eating']];
            }
            $drinking = "";
            if(array_key_exists($personalcaredatas_print['drinking'],$personalcare_options_array)){
            $drinking = $personalcare_options_array[$personalcaredatas_print['drinking']];
            }
            $lighthousework = "";
            if(array_key_exists($personalcaredatas_print['lighthousework'],$personalcare_options_array)){
            $lighthousework = $personalcare_options_array[$personalcaredatas_print['lighthousework']];
            }

            $printHtml.="<td>".$bowelcare."</td>";
            $printHtml.="<td>".$bladdercare."</td>";
            $printHtml.="<td>".$showercare."</td>";
            $printHtml.="<td>".$dressing."</td>";
            $printHtml.="</tr>";
            $printHtml.="</table>";
            $printHtml.="<table>
            <tr>
            <th>Teeth cleaning</th>
            <th>Cooking/meal preperation</th>
            <th>Eating</th>
            <th>Drinking</th>
            <th>Light housework/cleaning</th>
            </tr><tr>";
            $printHtml.="<td>".$teethcleaning."</td>";
            $printHtml.="<td>".$cooking."</td>";
            $printHtml.="<td>".$eating."</td>";
            $printHtml.="<td>".$drinking."</td>";
            $printHtml.="<td>".$lighthousework."</td>";
            $printHtml.="</tr>";
            $printHtml.="</table>";

            //Medication End

            //Community Start
            $printHtml.="<br>";
            $printHtml.="<h4>Community Access</h4>";
            $printHtml.="<table>
            <tr>
            <th>Not Applicable</th>
            <th>Bowel care</th>
            <th>Bladder care</th>
            <th>Using money</th>
            <th>Grocery shopping</th>
            <th>Paying bills</th>
           </tr>
            <tr>";
            $not_applicable='No';
            if($communityaccessdatas_print['not_applicable'] == 1)
            {
                $not_applicable ="Yes";
            }
            $printHtml.="<td>".$not_applicable."</td>";
            $communityaccessdatas_print_data = 0;
            $communityaccess_options_array = [];
            if($communityaccessdatas){
            $communityaccessdatas_print_data = 1;
            }
            if($communityaccessdatas_print_data == 1){
                $communityaccess_options_array = array('1'=>"Not applicable",'2'=>"With assistance",'3'=>"With supervision",'4'=>"Independant");
            }
            $bowelcare = "";
            if(array_key_exists($communityaccessdatas_print['bowelcare'],$communityaccess_options_array)){
            $bowelcare = $communityaccess_options_array[$communityaccessdatas_print['bowelcare']];
            }
            $bladdercare = "";
            if(array_key_exists($communityaccessdatas_print['bladdercare'],$communityaccess_options_array)){
            $bladdercare = $communityaccess_options_array[$communityaccessdatas_print['bladdercare']];
            }
            $using_money = "";
            if(array_key_exists($communityaccessdatas_print['using_money'],$communityaccess_options_array)){
            $using_money = $communityaccess_options_array[$communityaccessdatas_print['using_money']];
            }
            $grocessary_shopping = "";
            if(array_key_exists($communityaccessdatas_print['grocessary_shopping'],$communityaccess_options_array)){
            $grocessary_shopping = $communityaccess_options_array[$communityaccessdatas_print['grocessary_shopping']];
            }
            $paying_bills = "";
            if(array_key_exists($communityaccessdatas_print['paying_bills'],$communityaccess_options_array)){
            $paying_bills = $communityaccess_options_array[$communityaccessdatas_print['paying_bills']];
            }
            $swimming = "";
            if(array_key_exists($communityaccessdatas_print['swimming'],$communityaccess_options_array)){
            $swimming = $communityaccess_options_array[$communityaccessdatas_print['swimming']];
            }
            $road_safety = "";
            if(array_key_exists($communityaccessdatas_print['road_safety'],$communityaccess_options_array)){
            $road_safety = $communityaccess_options_array[$communityaccessdatas_print['road_safety']];
            }
            $printHtml.="<td>".$bowelcare."</td>";
            $printHtml.="<td>".$bladdercare."</td>";
            $printHtml.="<td>".$using_money."</td>";
            $printHtml.="<td>".$grocessary_shopping."</td>";
            $printHtml.="<td>".$paying_bills."</td>";
            $printHtml.="</tr>";
            $printHtml.="</table>";
            $printHtml.="<table>
            <tr>
            <th>Swimming / Activity</th>
            <th>Road safety</th>
            <th>Is there a companion card available for support workers?</th>
            <th>Method of transport</th>
            <th>Is support required to book and pay for Taxis / Ubers?</th>
            </tr><tr>";
            $printHtml.="<td>".$swimming."</td>";
            $printHtml.="<td>".$road_safety."</td>";
            $community_yesno_array = array('1'=>"Yes",'2'=>"No");
            $companion_cart = "";
            if(array_key_exists($communityaccessdatas_print['companion_cart'],$community_yesno_array)){
            $companion_cart = $community_yesno_array[$communityaccessdatas_print['companion_cart']];
            }
            $printHtml.="<td>".$companion_cart."</td>";

            $communityaccessdatas_print_data = 0;
            $communityaccess_methodtransport_options = [];
            if($communityaccessdatas){
            $communityaccessdatas_print_data = 1;
            }
            if($communityaccessdatas_print_data == 1){
            $communityaccess_methodtransport_options = array('1'=>"Public transport",'2'=>"Support worker vehicle",'3'=>"No paid transport");
            }
            $method_transport = "";
            if(array_key_exists($communityaccessdatas_print['method_transport'],$communityaccess_methodtransport_options)){
            $method_transport = $communityaccess_methodtransport_options[$communityaccessdatas_print['method_transport']];
            }
            $printHtml.="<td>".$method_transport."</td>";

             $support_taxis = "";
             if(array_key_exists($communityaccessdatas_print['support_taxis'],$community_yesno_array)){
             $support_taxis = $community_yesno_array[$communityaccessdatas_print['support_taxis']];
             }

             $printHtml.="<td>";
             $printHtml.="<p>".$support_taxis."</p>";
             $printHtml.="<div><b>Description:</b></div><br>";
             $printHtml.= $communityaccessdatas_print['support_taxis_desc'];
             $printHtml.="</td>";
             $printHtml.="</tr>";
             $printHtml.="</table>";
             //Community End
            $printHtml.="</div>";
            $filename = 'Need-Assessments-'.$this->input->get('need_assessment_id').'.doc';
            header('Content-type: application/ms-word');
            header('Content-Disposition: attachement;filename="'.$filename.'"');
            header('Content-Transfer-Encoding: binary');
            echo $printHtml;
            die;
    }

    public function get_assessment_attachment_details($data){
        if(!empty($data)){
            $this->load->model('Attachment_model');
            $attachment_data = $this->Attachment_model->find_all_attachments_by_object_id_and_name($data);            
            return $attachment_data;
        }
    }
    
    /*
    * get data from equipment assistance by passing
    * need_assessment_id
    */
    function get_selected_equipment_assistance()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $row = $this->basic_model->get_row('need_assessment_equipment', ["not_applicable", 'walking_stick','wheel_chair','model_brand','shower_chair','transfer_aides','daily_safety_aids','walking_frame','type','weight','toilet_chair','hoist_sling','other','daily_safety_aids_description','other_description','hoist_sling_description', 'transfer_aides_description'],['archive'=>0,'need_assessment_id'=>$data['need_assessment_id']]);
        if(!empty($row)) $status =  true; else $status = false;
        echo json_encode(["status" => $status, "data" => $row]);
    }     

    function get_need_and_risk_details(){
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $resultData = [];
        $this->db->select(array('na.id'));
        $this->db->from('tbl_need_assessment as na');
        $this->db->where(['na.account_person'=> $data['account_person_id'] , 'na.account_type'=> $data['account_type'] ,'na.archive'=> 0]);
        $this->db->order_by("id", "DESC");
        $this->db->limit(1);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        
        $result = $query->row('id');

        $resultData['need_assessment_id']=$result;

        $this->db->select(array('cra.id'));
        $this->db->from('tbl_crm_risk_assessment as cra');
        $this->db->where(['cra.account_id'=> $data['account_person_id'] , 'cra.account_type'=> $data['account_type'] ,'cra.is_deleted'=> 0]);
        $this->db->order_by("id", "DESC");
        $this->db->limit(1);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        
        $result = $query->row('id');
        $resultData['risk_assessment_id'] = $result;

        if(!empty($row)) $status =  true; else $status = false;
        echo json_encode(["status" => true, "data" => $resultData]);
    }

    /**
     * Find all attachment by object ID and object type
     */
    public function get_all_related_attachments()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $this->output->set_content_type('json');

        $object_id = $data['object_id'] ?? 0;
        $object_type = $data['object_type'] ?? 0;

        $results = $this->Schedule_attachment_model->get_need_assessment_attachment_details($object_id, $object_type);
        echo json_encode($results);
    }


    public function get_food_reference_data(){
        $reqData = request_handler('access_crm');
        if(!empty($reqData)){
            $results = $this->Need_assessment_model->get_food_preferences_ref_list("food_preferences");
            echo json_encode(["status" => true, "data" => $results]);
        }
   }   
    public function archive_need_assessment_doc(Type $var = null) {
        $reqData = request_handler('access_crm');
        $row = $this->basic_model->get_row('sales_attachment', array("*"),['archive'=>0,'id'=>$reqData->data->id]);
        if (!empty($row)) {
            $remove = $this->basic_model->update_records('sales_attachment', array('archive'=> 1, 'updated_by' => $reqData->adminId, 'updated' => DATE_TIME),array('id'=>$reqData->data->id));
            echo json_encode(["status" => true, "msg" => 'Nutritional support successfully archived']);
        } else {
            echo json_encode(["status" => false, "error" => system_msgs('something_went_wrong')]);
        }
    }

}
