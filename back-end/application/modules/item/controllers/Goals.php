<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class : Participant
 * Uses : Used for handle Participant request and response  
 
 * Response type - Json format
 * 
 * Library
 * form_validation - used for validating the form data
 * 
 * LogType - crm
 * 
 * @property-read \Participant_model $Participant_model
 */
class Goals extends MX_Controller {
    // Load custom validation traits function
    use formCustomValidation;

    // Defualt construct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        
        // load document model 
        $this->load->model('Participant_model');
        $this->load->model('Goal_model');

        // set the log
        $this->loges->setLogType('item');
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
     * For getting participant list
     * 
     * Return type json
     * - count 
     * - data
     * - status
     */
    function get_goals_list() {
        // $reqData = request_handler('access_crm');
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            // Call model for get participant list
            $result = $this->Goal_model->get_goals_list($reqData->data);
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }

    /*
     * it is used for search participant (tbl_participant_master) in create participant
     * return type json
     */
    function get_participant_name_search() {
        $reqData = request_handler();
        $name = $reqData->data->query ?? '';
        $rows = $this->Goal_model->get_all_participant_name_search($name);
        echo json_encode($rows);
    }

    /*
     * it is used for search participant (tbl_participant_master) in create participant
     * return type json
     */
    function get_participant_details() {
        $reqData = request_handler();
        $id = $reqData->data->id ?? '';
        $rows = $this->Goal_model->get_all_participant_name_search(null, $id);
        if($rows)
        $retarr['participant'] = $rows[0];

        $return = array('data' => $retarr, 'status' => true);
        echo json_encode($return);
    }

    /**
     * Retrieve goal details.
     */
    public function get_goal_details() {
        $reqData = request_handler();
        $data = $reqData->data;

        if (empty($data->id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        $result = $this->Goal_model->get_goal_details($data->id);
        echo json_encode($result);
        exit();
    }

    /**
     * Mark goal as archived.
     */
    public function archive_goal() {
        $reqData = request_handler();
        $data = $reqData->data;
        $adminId = $reqData->adminId;
        $id = isset($data->goal_id) ? $data->goal_id : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        # does the goal exist?
        $result = $this->Goal_model->get_goal_details($id);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Goal does not exist anymore."];
            echo json_encode($response);
            exit();
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("goals_master", $upd_data, ["id" => $id]);

        # logging action
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully archived goal with ID of %s by %s", $id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully archived goal with ID of %s by %s", $id, $adminName));  // set title in log
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        $response = ['status' => true, 'msg' => "Successfully archived goal"];
        echo json_encode($response);
        exit();
    }

    /*
     * For creating updating goal
     * Validate request and return response according to request
     * 
     * Return type json
     *  - status
     *  - msg 
     *  - error if any error  occured
     */
    function create_update_goal() {
        // Get the request data
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
        $is_from_service_agreement=  $reqData->data->is_sa??false;
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;

            # validation rule
            $validation_rules = [
                array('field' => 'goal', 'label' => 'Goal', 'rules' => 'required'),
                array('field' => 'participant_master_id', 'label' => 'Participant', 'rules' => 'required'),
                array('field' => 'objective', 'label' => 'Objective', 'rules' => 'required'),
                array(
                    'field' => 'start_date', 'label' => 'Start date', 'rules' => 'required|valid_date_format[Y-m-d]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect start date',
                    ]
                ),
                array(
                    'field' => 'end_date', 'label' => 'End date', 'rules' => 'required|valid_date_format[Y-m-d]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect end date',
                    ]
                )
            ];
            if($is_from_service_agreement)
            {
                $validation_rules[1]=[];
                $validation_rules[1]= array('field' => 'service_agreement_id', 'label' => 'Service Agreement', 'rules' => 'required');
            }

            # set data in libray for validate
            $this->form_validation->set_data($data);

            # set validation rule
            $this->form_validation->set_rules($validation_rules);

            # check data is valid or not
            if ($this->form_validation->run()) {
                $this->load->model('Basic_model');

                // Call create / update goal model
                $goalId = $this->Goal_model->create_update_goal($data, $adminId);

                // Check $goalId is empty or not
                // According to that goal will be created
                if ($goalId) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    /**
                     * Create logs. it will represent the user action they have made.
                     */
                    if(!empty($data['id'])){
                        $this->loges->setTitle("Goal updated " . $data['goal'] ." by " . $adminName);  // Set title in log
                        $msg = 'Goal has been updated successfully.';
                    }
                    else{
                        $this->loges->setTitle("New Goal created " . $data['goal'] ." by " . $adminName);  // Set title in log
                        $msg = 'Goal has been created successfully.';
                    }
                    
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    // Create log
                    $this->loges->createLog(); 
                    $data = array('goalId' => $goalId);
                    $response = ['status' => true, 'msg' => $msg, 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
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
     * it is used for search participant (tbl_participant_master) in create participant
     * return type json
     */
    function get_participants_service_type() {
        $reqData = request_handler();
        $id = $reqData->data->participant->value?? '';
        $rows = $this->Goal_model->get_participants_service_type($id);
        echo json_encode($rows);
    }


    /*
     * For getting goal entered by member
     * 
     * Return type json
     */
    function get_tracked_goals_by_participant() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Goal_model->get_tracked_goals_by_participant($reqData->data);
            echo json_encode($result);
            exit();  
        }
    }

 /*
     * Get goals details by participant id
     * 
     * Return type json
     * - data
     * - status
     * - msg
     */
    function get_all_goals_and_shift_by_participant_id() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            // Call model for goal list
            $result = $this->Goal_model->get_all_goals_and_shift_by_participant_id($reqData->data);
            
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }
    /*
     * Get goals list by participant id
     * 
     * Return type json
     * - data
     * - status
     * - msg
     */
    function get_all_goals_list_by_participant()
    {
        $reqData = request_handler();
        if (!empty($reqData)) {
            // Call model for goal list
            $result = $this->Goal_model->get_all_goals_list_by_participant($reqData->data);
            
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }
}
