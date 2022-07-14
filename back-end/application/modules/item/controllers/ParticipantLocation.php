<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class : ParticipantLocation
 * Uses : Used for handle Participant location request and response  
 
 * Response type - Json format
 * 
 * Library
 * form_validation - used for validating the form data
 * 
 * LogType - crm
 * 
 * @property-read \Participant_location_model $Participant_location_model
 */
class ParticipantLocation extends MX_Controller {
    // Load custom validation traits function
    use formCustomValidation;

    // Defualt construct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        
        // load document model 
        $this->load->model('Participant_location_model');

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
    function get_participant_location_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'participant_id', 'label' => 'Participant Id', 'rules' => 'required'),
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get participant location list
                $result = $this->Participant_location_model->get_participant_location_list($reqData->data);
            } else {
                // If requested data is empty or null
                $result = ['status' => false, 'error' => 'Participant Id is null'];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }

    /*
     * it is used for search person (tbl_person) in create participant
     * return type json
     */
    function get_participant_name_search() {
        $reqData = request_handler();
        $name = $reqData->data->query ?? '';
        $rows = $this->Participant_location_model->get_all_participant_name_search($name);
        echo json_encode($rows);
        exit();
    }

    /*
     * For creating participant location
     * Validate request and return response according to request
     * 
     * Return type json
     *  - status
     *  - msg 
     *  - error if any error  occured
     */
    function create_participant_location() {
        // Get the request data
        $reqData = request_handler();
        $adminId = $reqData->adminId;
        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                array('field' => 'name', 'label' => 'Name', 'rules' => 'required'),
                array('field' => 'active', 'label' => 'Active', 'rules' => 'required', "errors" => [ "required" => "Active value is null"]),
                array('field' => 'participant_id', 'label' => 'Participant', 'rules' => 'required'),
                array('field' => 'location', 'label' => 'location', 'rules' => 'required'),
            ];

            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {

                $this->load->model('Basic_model');

                // Call create document model
                $location = $this->Participant_location_model->create_participant_location($data, $adminId);
                // According to that document will be created
                if ($location['status'] == true) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    /**
                     * Create logs. it will represent the user action they have made.
                     */
                    $this->loges->setTitle("New participant location created for " . $data['name'] ." by " . $adminName);  // Set title in log
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    // Create log
                    $this->loges->createLog(); 
                    $data = array('location_id' => $location['location_id']);
                    $response = ['status' => true, 'msg' => 'Location has been created successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $location['error']];
                }
            } else {
                // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => 'Requested data is null'];
        }

        echo json_encode($response);
        exit();
    }

     /*
     * Validate the string contain oly alphabets and space only
     * @param {str} $str
     * return type - bool
     */
    function alpha_dash_space($str)
    {
        # return ( ! preg_match("/^([-a-z_ ])+$/i", $str)) ? FALSE : TRUE;
        if (! preg_match('/^[a-zA-Z\s]+$/', $str)) {
            // Set error msg
            $this->form_validation->set_message('alpha_dash_space', 'Name field contain only alphabetical characters');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /*
     * For getting participant by id
     * 
     * Return type json
     * - data
     * - status
     */
    function get_participant_location_data_by_id() {
        $reqData = request_handler('');
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'location_id', 'label' => 'Location Id', 'rules' => 'required'),
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get participant location list
                $result = $this->Participant_location_model->get_participant_location_data_by_id($reqData->data);
            } else {
                // If requested data is empty or null
                $result = ['status' => false, 'error' => 'Location Id is null'];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }

    /*
     * For edit participant
     * Validate request and return respone according to request
     * 
     * Return type json
     *  - status
     *  - msg 
     *  - error if any error will be occur
     */
    function edit_participant_location() {
        // Get the request data
        $reqData = request_handler();
        $adminId = $reqData->adminId;
        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                array('field' => 'location_id', 'label' => 'Location Id is missing', 'rules' => 'required'),
                array('field' => 'location_address_id', 'label' => 'Location Addresss Id is missing', 'rules' => 'required'),
                array('field' => 'name', 'label' => 'Name', 'rules' => 'required'),
                array('field' => 'active', 'label' => 'Active', 'rules' => 'required', "errors" => [ "required" => "Active value is null"]),
                array('field' => 'participant_id', 'label' => 'Participant', 'rules' => 'required'),
                array('field' => 'location', 'label' => 'Location', 'rules' => 'required'),
            ];

            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {

                $this->load->model('Basic_model');

                // Call create participant location model
                $location = $this->Participant_location_model->edit_participant_location($data, $adminId);
                // According to that document will be created
                if ($location['status'] == true) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    /**
                     * Create logs. it will represent the user action they have made.
                     */
                    $this->loges->setTitle("New participant location created for " . $data['name'] ." by " . $adminName);  // Set title in log
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    // Create log
                    $this->loges->createLog(); 
                    $data = array('location_id' => $location['location_id']);
                    $response = ['status' => true, 'msg' => 'Location has been created successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $location['error']];
                }
            } else {
                // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => 'Requested data is null'];
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Mark location as archived.
     */
    public function archive_location() {
        $reqData = request_handler();
        $data = $reqData->data;
        $adminId = $reqData->adminId;
        $id = isset($data->location_id) ? $data->location_id : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing Location ID"];
            echo json_encode($response);
            exit();
        }

        // Check location is exist. Using id
        $where = array('id' => $id);
        $colown = array('id', 'name');
        $check_location = $this->basic_model->get_record_where('locations_master', $colown, $where);

        if (empty($check_location)) {
            $response = ['status' => false, 'error' => "Location does not exist anymore."];
            echo json_encode($response);
            exit();
        }

        $upd_data["updated_at"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("locations_master", $upd_data, ["id" => $id]);

        # logging action
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully archived participant location with ID of %s by %s", $id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully archived location ID of %s by %s", $id, $adminName));  // set title in log
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        $response = ['status' => true, 'msg' => "Successfully archived location"];
        echo json_encode($response);
        exit();
    }
}
