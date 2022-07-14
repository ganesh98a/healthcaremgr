<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class : RiskAssessment
 * Uses : Used for handle RiskAssessment request and response  
 * Getting request data - request_handler('access_crm')
 * Response type - Json format
 * 
 * Library
 * form_validation - used for validating the form data
 * 
 * LogType - crm
 * 
 * @property-read \RiskAssessment_model $RiskAssessment_model
 */
class RiskAssessment extends MX_Controller {
    // Load custom validation traits function
    use formCustomValidation;

    // Defualt construct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        
        // load risk assessment model 
        $this->load->model('RiskAssessment_model');

        // set the log
        $this->loges->setLogType('crm');
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

    /*
     * For getting reference id of create risk assessment
     * return type json
     */
    function get_create_reference_id() {
        request_handler();

        // get reference id 
        $rows = $this->RiskAssessment_model->get_reference_id();

        if ($rows) {
            $previousRID = $rows[0]['reference_id'];
            // split the id as prefix and value
            $splitPos = 2;
            $prefix = substr($previousRID, 0, $splitPos);
            $value = substr($previousRID, $splitPos);
            $incValue = intVal($value) + 1;
             // Add  a preceeding 0 in a 8 digit
            $strPadDigits = 8;
            $str = 0;
            $incValueWPad = str_pad($incValue, $strPadDigits, $str, STR_PAD_LEFT);
            // Join two variable
            $reference_id = $prefix.$incValueWPad;
        } else {
            $reference_id = 'RA00000001';
        }
        $result = ["status" => true, "data" => ['reference_id' => $reference_id]];
        echo json_encode($result);
     }

    /*
     * For creating risk assessment
     * Validate request and return response according to request
     * 
     * Return type json
     *  - status
     *  - msg 
     *  - error if any error  occured
     */
    function create_risk_assessment() {
        // Get the request data
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = 0;
            // Validation rules set
            $validation_rules = [
                array('field' => 'topic', 'label' => 'Topic', 'rules' => 'required'),
                array('field' => 'owner_id', 'label' => 'Owner', 'rules' => 'required'), 
                array('field' => 'account_id', 'label' => 'Account (Person/Org) Name', 'rules' => 'required'),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required|in_list[1,2,3]','errors' => [
                    'in_list' => 'The status must be one of: Draft, Final, InActive'
                ],),
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);
            // Set validation rule
            $this->form_validation->set_rules($validation_rules);
            // Check data is valid or not
            if ($this->form_validation->run()) {
                    $this->load->model('Basic_model');
                    $riskAssessmentId = $this->RiskAssessment_model->create_risk_assessment($data, $adminId);
                    if ($riskAssessmentId) {
                        $this->load->library('UserName');
                        $adminName = $this->username->getName('admin', $adminId);
                        $this->loges->setTitle("New risk Assessment created for " .  $riskAssessmentId ." by " . $adminName);  // Set title in log
                        $this->loges->setDescription(json_encode($data));
                        $this->loges->setUserId($adminId);
                        $this->loges->setCreatedBy($adminId);
                        // Create log
                        $this->loges->createLog(); 
                        $data = array('risk_assessment_id' => $riskAssessmentId);
                        $response = ['status' => true, 'msg' => 'Risk Assessment has been created successfully.', 'data' => $data ];
                    } else {
                        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
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
     * For getting risk assessment list
     * 
     * Return type json
     * - count 
     * - data
     * - status
     */
    function get_risk_assessment_list() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData->data)) {
            // Call model for get risk assessment list
            $filter_condition = '';
            if (!empty($reqData->data->tobefilterdata)) {
                $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
                $filter_condition = str_replace(['status'], ['ra.status'], $filter_condition);
            }
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
            $result = $this->RiskAssessment_model->get_risk_assessment_list($reqData->data, $filter_condition, $contact_id, $reqData->uuid_user_type);
            $result['participant_name'] = $participant_name;
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }
    
    /*
     * For getting risk assessment by id
     * 
     * Return type json
     * - data
     * - status
     */
    function get_risk_assessment_data_by_id() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData->data)) {
            // Call model for get risk assessment list
            $result = $this->RiskAssessment_model->get_risk_assessment_by_id($reqData->data);
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }

    /*
     * For edit risk assessment
     * Validate request and return respone according to request
     * 
     * Return type json
     *  - status
     *  - msg 
     *  - error if any error will be occur
     */
    function edit_risk_assessment() {
        // Get the requested data
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                array('field' => 'topic', 'label' => 'Topic', 'rules' => 'required'),
                array('field' => 'owner_id', 'label' => 'Owner', 'rules' => 'required'), 
                array('field' => 'account_id', 'label' => 'Account (Person/Org) Name', 'rules' => 'required'),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required|in_list[1,2,3]','errors' => [
                    'in_list' => 'The status must be one of: Draft, Final, InActive'
                ],),
            ];

            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {

                $this->load->model('Basic_model');
                 
                // Call update risk assessment model
                $riskAssessmentId = $this->RiskAssessment_model->update_risk_assessment($data, $adminId);

                // Check $riskAssessmentId is not empty 
                // According to that got risk assessment is created or not
                if ($riskAssessmentId) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    /**
                     * Create logs. it will represent the user action they have made.
                     */
                    $this->loges->setTitle("Risk Assessment updated for " . $data['risk_assessment_id'] ." by " . $adminName);  // Set title in log
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    // Create log
                    $this->loges->createLog(); 

                    $response = ['status' => true, 'msg' => 'Risk Assessment has been updated successfully.'];
                } else {
                    $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
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
     * Delete risk assessment
     * Set `is_deleted` = 1
     */
    public function delete_risk_assessment()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;
        // Check the data is exist or not
        $result = $this->RiskAssessment_model->get_risk_assessment_by_id((object)$data);        
        if (empty($result)) {
            $response = [
                'status' => false,
                'error' => 'Risk Assessment does not exist anymore. Please refresh your page',
            ];
            echo json_encode($response);
            exit();
        }

        // Call delete risk assessment model
        $result = $this->RiskAssessment_model->delete_risk_assessment($data, $adminId);        
        if (!$result) {            
            $response = [
                'status' => false,
                'error' => $result['error'] ?? system_msgs('something_went_wrong'),
            ];
            echo json_encode($response);
            exit();
        }

        // Create log
        $risk_assessment_id = $data['risk_assessment_id'];

        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);

        $this->loges->setTitle(sprintf("Successfully deleted risk assessment with ID of %s by %s", $risk_assessment_id, $adminName));
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($risk_assessment_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        $response = [
            'status' => true,
            'msg' => "Risk Assessment successfully deleted"
        ];
        echo json_encode($response);
        exit();
    }
    
    /*
     * It is used for search owner staff
     * return type json
     */
    function get_owner_staff_search() {
        $reqData = request_handler();
        $ownerName = $reqData->data->query ?? '';
        $rows = $this->RiskAssessment_model->get_owner_staff_by_name($ownerName);
        echo json_encode($rows);
        exit();
    }

    /*
     * It used for search account person
     * return type json
     */
    function get_account_person_name_search() {
        $reqData = request_handler();
        $accountName = $reqData->data->query ?? '';
        $rows = $this->RiskAssessment_model->get_account_person_name_search($accountName);
        echo json_encode($rows);
        exit();
    }

    /*
     * Get risk assessment detail
     * 
     * Return type json
     * - data
     * - status
     * - msg
     */
    function get_risk_assessment_detail_by_id() {
        $reqData = request_handler('access_crm');
        // echo '<pre>';print_r($reqData->data);die;

        if (!empty($reqData->data)) {
            // Call model for get risk assessment list
            $result = $this->RiskAssessment_model->get_risk_assessment_detail_by_id($reqData->data);
            
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }
    
    /*
     * its use for update risk assessment status
     * 
     * return type josn
     * return object status: true
     */
    function update_status_risk_assessment(){
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;

            $validation_rules = [
                array('field' => 'risk_assessment_id', 'label' => 'risk assessment id', 'rules' => "required"),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required'),
            ];

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $res = $this->RiskAssessment_model->update_status_risk_assessment($data, $adminId);

                $response = $res;
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }
        
        echo json_encode($response);
        exit();
    }


    /**
     * Create or update risk matrix evaluation.
     * 
     * This is used to synchronize risk matrix evaluation with the ones in client side, 
     * therefore this will also archive existing
     */
    public function save_all_risk_matrices()
    {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        

        $data = obj_to_arr($reqData->data);
        $this->output->set_content_type('json');

        $probability_options = $this->RiskAssessment_model->find_all_risk_probabilities();
        $probability_ids = array_column($probability_options, 'id');
        
        $impact_options = $this->RiskAssessment_model->find_all_risk_impacts();
        $impact_ids = array_column($impact_options, 'id');

        
        $validation_rules = [
            [
                'field' => 'risk_assessment_id',
                'label' => 'Risk assessment ID',
                'rules' => ['required', 'exists[tbl_crm_risk_assessment.id]'],
                'errors' => ['exists' => 'This risk assessment does not exist anymore']
            ],
        ];

        if (!empty($data['rows'])) {

            // trim risk values
            foreach ($data['rows'] as $i => $row) {
                if (isset($row['risk']) && is_string($row['risk'])) {
                    $data['rows'][$i]['risk'] = trim($row['risk']);
                }
            }

            foreach ($data['rows'] as $i => $row) {
                $n = $i + 1;
                $risk = $row['risk'] ?? null;

                if (!$risk) {
                    continue;
                }

                $differsRules = [];
                foreach ($data['rows'] as $j => $row2) {
                    if ($i === $j) {
                        continue;
                    }

                    $differsRules[] = "differs[rows[$j][risk]]";
                }

                $validation_rules[] = [
                    'field' => "rows[$i][risk]",
                    'label' => "Risk for row " . $n,
                    'rules' => $differsRules,
                    'errors' => [ 
                        'differs' => sprintf("The risk for row #%s already been used in one of the other rows", $n) 
                    ]
                ];
                
                $validation_rules[] = [
                    'field' => "rows[$i][probability_id]",
                    'label' => "Probability for row " . $n,
                    'rules' => [ "required", "trim", sprintf("in_list[%s]", implode(',', $probability_ids)) ],
                    'errors' => [ 'in_list' => sprintf("The impact for row #%s does not exist anymore", $n) ]
                ];
                $validation_rules[] = [
                    'field' => "rows[$i][impact_id]",
                    'label' => "Impact for row " . $n,
                    'rules' => [ "required", "trim", sprintf("in_list[%s]", implode(',', $impact_ids)) ],
                    'errors' => [ 'in_list' => sprintf("The impact for row #%s does not exist anymore", $n) ]
                ];
            }
        }

        $this->form_validation->set_data($data)->set_rules($validation_rules);

        if ( ! $this->form_validation->run()) {
            $error_array =  $this->form_validation->error_array();
            return $this->output->set_output(json_encode([
                'status' => false,
                'error' => implode(', ', $error_array)
            ]));
        }

        $risk_assessment_id = $data['risk_assessment_id'];
        $result = $this->RiskAssessment_model->save_all_risk_matrices($risk_assessment_id, $data['rows'], $adminId);

        // if success log this action
        if ($result['status']) {
            $adminId = $reqData->adminId;
            $riskAssessmentCode = $result['code'] ?? null;

            $this->load->library('UserName');
            $adminName = $this->username->getName('admin', $adminId);
            $this->loges->setTitle(sprintf("Risk matrix of risk assessment %s successfully updated by %s", $riskAssessmentCode, $adminName));
            $this->loges->setSpecific_title(sprintf("Risk matrix of risk assessment with code %s successfully updated by %s", $risk_assessment_id, $adminName));  // set title in log
            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($risk_assessment_id);
            $this->loges->setCreatedBy($adminId);
            $this->loges->createLog(); // create log
        }


        return $this->output->set_output(json_encode([
            'status' => true,
            'msg' => 'Risk matrix successfully saved',
        ]));
    }
     public function save_all_behavsupport_matrices()
    {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        // echo '<pre>';print_r( $reqData );
        $data = obj_to_arr($reqData->data);
        $this->output->set_content_type('json');
        $result = [];
        if (!empty($data['rows'])) {
            $risk_assessment_id = $data['risk_assessment_id'];
            $result = $this->RiskAssessment_model->save_all_behavsupport_matrices($risk_assessment_id, $data['rows'], $adminId);    
        }
        // if success log this action
        if ( !empty($result) && $result['status']) {
            $adminId = $reqData->adminId;
            $this->load->library('UserName');
            $adminName = $this->username->getName('admin', $adminId);
            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($risk_assessment_id);
            $this->loges->setCreatedBy($adminId);
            $this->loges->createLog(); // create log
            return $this->output->set_output(json_encode([
                'status' => true,
                'msg' => 'Behaviour Support matrix successfully saved',
            ]));
        }else{        
        return $this->output->set_output(json_encode([
            'status' => false,
            'error' => 'Please enter details to save!',
        ]));
        }

    }

    
    /*
     * its use for save living situation
     * 
     * return type josn
     * return object status: true
     */
    function save_living_situation(){
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;

            $validation_rules = [
                array('field' => 'risk_assessment_id', 'label' => 'risk assessment id', 'rules' => "required"),
                array('field' => 'living_situation', 'label' => 'living situation', 'rules' => 'required'),
                array('field' => 'informal_support', 'label' => 'informal support', 'rules' => 'required'),
                array('field' => 'lack_of_informal_support', 'label' => 'lack of informal support', 'rules' => 'required'),
            ];

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $res = $this->RiskAssessment_model->save_living_situation($data, $adminId);

                $response = ['status' => true, 'msg' => "Living situation has been updated successfully."];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }
        
        echo json_encode($response);
        exit();
    }
    
    /*
     * its use for get living situation details
     * 
     * return type josn
     * return object status: true
     */
    function get_living_situation_details(){
        $reqData = request_handler('access_crm');
        if (!empty($reqData->data)) {
            // Call model for get risk assessment list
            $data = $this->RiskAssessment_model->get_living_situation_of_risk_assessment($reqData->data->risk_assessment_id);
            $result = ['status' => true, 'data' => $data];
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit(); 
    }

    /**
     * Get the court action information
     */
    function get_court_action_by_id()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        // Check if court action is exist
        $row = $this->basic_model->get_row('crm_risk_assessment_court_actions', array("*"), ['risk_assessment_id' => $data['risk_assessment_id']]);
        if(!empty($row)) $status =  true; else $status = false;       
        // return data
        $return = [
            'status' => $status,
            'data'=> $row,
        ];

        return $this->output->set_output(json_encode($return));
    }

    /*
    * Save court action
    */
    function save_court_action(){
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;
        if (!empty($reqData->data)) {
            // Check if court action is exist
            $getCourtAction = $this->basic_model->get_row('crm_risk_assessment_court_actions', array("*"), ['risk_assessment_id' => $data['risk_assessment_id']]);
            $ca_action = !empty($getCourtAction) ? 'update' : 'create';            
            
            if (isset($data['not_applicable']) && $data['not_applicable'] != '2') {
                // Validation rules set
                $validation_rules = [
                    array('field' => 'risk_assessment_id', 'label' => 'Risk Assessment ID', 'rules' => 'required'),
                    array('field' => 'not_applicable', 'label' => 'Not Applicable', 'rules' => 'required'),
                    array('field' => 'inter_order', 'label' => 'Intervention Orders', 'rules' => 'required'),
                    array('field' => 'com_ser_order', 'label' => 'Community Service Orders', 'rules' => 'required'), 
                    array('field' => 'com_cor_order', 'label' => 'Community Correction Orders', 'rules' => 'required'),
                ];
            } else {
                // Validation rules set
                $validation_rules = [
                    array('field' => 'risk_assessment_id', 'label' => 'Risk Assessment ID', 'rules' => 'required'),
                    array('field' => 'not_applicable', 'label' => 'Not Applicable', 'rules' => 'required'),
                ];
            }
            

            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
               // Save court action
                $court_action_id = $this->RiskAssessment_model->save_court_action($data, $adminId, $ca_action);
                if ($court_action_id) {
                    // Log
                    $risk_assessment_id = $data['risk_assessment_id'];
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);
                    $this->loges->setTitle(sprintf("Order is updated under RiskAssessment ID %s by %s", $risk_assessment_id, $adminName));
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    $this->loges->createLog();

                    $return = ['status'=>true, 'msg'=>'Order is updated successfully'];
                } else {
                    $return = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                }
            } else {
                 // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $return = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $return = ['status' => false, 'error' => 'Requested data is null']; 
        }
       
        return $this->output->set_output(json_encode($return));
    }

    //

    function get_ra_behavsupport_by_id()
    {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        // Check if court action is exist
        $row = $this->basic_model->get_row('ra_behavioursupport', array("*"), ['risk_assessment_id' => $data['risk_assessment_id']]);
        if(!empty($row)) $status =  true; else $status = false;       
        // return data
        $return = [
            'status' => $status,
            'data'=> $row,
        ];

        return $this->output->set_output(json_encode($return));
    }
    //

    //save_behaviuor_support
    function save_behaviuor_support(){
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;
        if (!empty($reqData->data)) {
            // Check if court action is exist
            $getCourtAction = $this->basic_model->get_row('ra_behavioursupport', array("*"), ['risk_assessment_id' => $data['risk_assessment_id']]);
            $ca_action = !empty($getCourtAction) ? 'update' : 'create';            
             $court_action_id = $this->RiskAssessment_model->save_behaviuor_support($data, $adminId, $ca_action);
                if ($court_action_id) {
                    $risk_assessment_id = $data['risk_assessment_id'];
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    $this->loges->createLog();
                    $return = ['status'=>true, 'msg'=>'Behaviuor Support is updated successfully'];
                } else {
                    $return = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                }   
            
            } else {
                // If requested data is empty or null
                $return = ['status' => false, 'error' => 'Requested data is null']; 
            }
        return $this->output->set_output(json_encode($return));
    }
    function printra()
    {
        $data['risk_assessment_id'] =   $this->input->get('risk_assessment_id');
        // echo '<pre>';print_r($data['risk_assessment_id']);die;

        $page_title = $this->input->get('page_title');
        $riskassdatas = $this->RiskAssessment_model->get_risk_assessment_detail_by_id(json_decode(json_encode($data)));
        $livingsituationdata = $this->RiskAssessment_model->get_living_situation_of_risk_assessment($data['risk_assessment_id']);
        $behavioursupportdata = $this->basic_model->get_row('ra_behavioursupport', array("*"), ['risk_assessment_id' => $data['risk_assessment_id']]);
        $orderdata = $this->basic_model->get_row('crm_risk_assessment_court_actions', array("*"), ['risk_assessment_id' => $data['risk_assessment_id']]);
        $allDatas['riskdatas'] = $riskassdatas['data'];
        $allDatas['riskmatricesdatas'] = $riskassdatas['data']['matrices'];
        $allDatas['behaviour_support_matricesdatas'] = $riskassdatas['data']['behaviour_support_matrices'];
        $allDatas['livingsituationdata'] = $livingsituationdata;
        $allDatas['behavioursupportdata'] = $behavioursupportdata;
        $allDatas['orderdata'] = $orderdata;
        
        $print_datas = json_decode(json_encode($allDatas),true);
        $riskdatas_print = $print_datas['riskdatas'];
        $riskmatricesdatas_print = $print_datas['riskmatricesdatas'];
        $livingsituationdata_print = $print_datas['livingsituationdata'];
        $behavioursupportdata_print = $print_datas['behavioursupportdata'];
        $behavioursupportdatamatrix_print = $print_datas['behaviour_support_matricesdatas'];
        $orderdata_print = $print_datas['orderdata'];;

        // echo '<pre>';print_r($behavioursupportdatamatrix_print);die;

        $printHtml="";
        $printHtml="<style>
            @page Section1 {size:595.45pt 841.7pt; margin:1.0in 1.25in 1.0in 1.25in;mso-header-margin:.5in;mso-footer-margin:.5in;mso-paper-source:0;}
            div.Section1 {page:Section1;}
            @page Section2 {size:841.7pt 595.45pt;mso-page-orientation:landscape;margin:1.25in 1.0in 1.25in 1.0in;mso-header-margin:.5in;mso-footer-margin:.5in;mso-paper-source:0;}
            div.Section2 {page:Section2;}
            </style>";
            $printHtml.="<div class=Section2>";
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
        $printHtml.="<h3 style='text-align:center'>Risk Assessments - ".$page_title."</h3>";
        // Details Start
        $printHtml.="<h4>Assessments Details</h4>";
        $printHtml.="<table>
        <tr>
        <th>ID</th>
        <th>Status</th>
        <th>Created By</td>
        <th>Created Date</td>
       </tr>";
        $printHtml.="<td>".$riskdatas_print['reference_id']."</td>"; 
        $printHtml.="<td>".$riskdatas_print['created_by']."</td>";
        $printHtml.="<td>".$riskdatas_print['status']."</td>";
        $printHtml.="<td>".date("d/m/Y", strtotime($riskdatas_print['created_date']))."</td>";
        $printHtml.="</tr>";      
        $printHtml.="</table>";
        // Details End
 
        // Living Situation Start

        $printHtml.="<h4>Living Situation</h4>";
        $printHtml.="<table>
        <tr>
        <th>Living Situation</th>
        <th>Informal Support</td>
        <th>Risk due to lack of informal support</td>
       </tr>";
        $living_situation_array = array('1'=>"Lives Alone",'2'=>"Lives with family",'3'=>"Lives with other",'4'=>"SDA");
        $living_situation = "";
        if(array_key_exists($livingsituationdata_print['living_situation'],$living_situation_array)){
            $living_situation = $living_situation_array[$livingsituationdata_print['living_situation']];
        }
         if($livingsituationdata_print['living_situation'] != 4){
            $printHtml.="<td>".$living_situation."</td>"; 
         }
         if($livingsituationdata_print['living_situation'] == 4){
            $printHtml.="<td>"; 
            $printHtml.="<p>".$living_situation."</p>";
            $printHtml.="<div><b>Agency:</b></div></br>";
            $printHtml.= $livingsituationdata_print['living_situation_agency'];
            $printHtml.="</td>";
        
        }
        if($livingsituationdata_print['informal_support'] == 1){
            $printHtml.="<td>No</td>";
        }
        if($livingsituationdata_print['informal_support'] == 2){
            $printHtml.="<td>";
            $printHtml.="<p>Yes</p>";
            $printHtml.="<div><b>Description:</b></div><br>";
            $printHtml.= $livingsituationdata_print['informal_support_describe'];
            $printHtml.="</td>";
        }else{
            $printHtml.="<td></td>"; 
        }
        if($livingsituationdata_print['lack_of_informal_support'] == 1){
            $printHtml.="<td>No</td>";
        }
        if($livingsituationdata_print['lack_of_informal_support'] == 2){
            $printHtml.="<td>";
            $printHtml.="<p>Yes</p>";
            $printHtml.="<div><b>Description:</b></div><br>";
            $printHtml.= $livingsituationdata_print['lack_of_informal_support_describe'];
            $printHtml.="</td>";
        } 
        $printHtml.="</tr>";      
        $printHtml.="</table>";
        // Living Situation End

        //Behaviour Support Start
        $printHtml.="<br>";
        $printHtml.="<h4>Behaviour Support</h4>";
        $printHtml.="<table>
        <tr>
        <th>Not applicable</th>
        <th>Behaviour Support Plan (last 12m)</th>
        <th>When will the plan be available?</th>
        <th>No Plan</th>
        </tr>
        <tr>";
          $not_applicable='No';
          if($behavioursupportdata_print['bs_not_applicable'] == 2)
          {
             $not_applicable ="Yes";  
          }
          $printHtml.="<td>".$not_applicable."</td>";

          if($not_applicable == 'No'){

 

          if($behavioursupportdata_print['bs_plan_status'] == 1)
          {
            $printHtml.="<td>No</td>";
          }
          if($behavioursupportdata_print['bs_plan_status'] == 2)
          {
            $printHtml.="<br>";
            $printHtml.="<td>";
            $printHtml.="<p>Yes</p>";
            $printHtml.="<ul>";
            if($behavioursupportdata_print['seclusion'] == 2){
                $printHtml.="<li>Seclusion</li>";
            }
            if($behavioursupportdata_print['chemical_constraint'] == 2){
                $printHtml.="<li>Chemical Constraint</li>";
            }
            if($behavioursupportdata_print['mechanical_constraint'] == 2){
                $printHtml.="<li>Mechanical Constraint</li>";
            }
            if($behavioursupportdata_print['physical_constraint'] == 2){
                $printHtml.="<li>Physical Constraint</li>";
            }
            if($behavioursupportdata_print['environmental'] == 2){
                $printHtml.="<li>Environmental</li>";
            }
         }
         if($behavioursupportdata_print['bs_plan_available_date'] != null){
            $printHtml.="<td>".date("d/m/Y", strtotime($behavioursupportdata_print['bs_plan_available_date']))."</td>";
        }else{
            $printHtml.="<td></td>"; 
            }
          $bs_noplan_status='No';
          if($behavioursupportdata_print['bs_noplan_status'] == 2)
          {
             $bs_noplan_status ="Yes";  
          }
          $printHtml.="<td>".$bs_noplan_status."</td>";
         }else{
            $printHtml.="<td></td>";
            $printHtml.="<td></td>";
            $printHtml.="<td></td>";
         }
          $printHtml.="</tr>";
          $printHtml.="</table>";
          //Behaviour Support End

           // Behaviour Support Matrix Start
           $printHtml.="<h4>Behaviour Support Matrix</h4>";
           $printHtml.="<table>
           <tr>
           <th>Behaviour</th>
           <th>Likelyhood</th>
           <th>Trigger</td>
           <th>Prevention Startegy</td>
           <th>De-escalation Startegies</td>
          </tr>";
          if((!empty($behavioursupportdatamatrix_print)) && count($behavioursupportdatamatrix_print) > 0){
             foreach ($behavioursupportdatamatrix_print as $behavioursupportdatamatrix) {
           $likelyhood_id_val = $behavioursupportdatamatrix['likelyhood_id'];
           $likelyhood_array = array('1'=>"Likely",'2'=>"Very likely",'3'=>"Un likely");
           $likelyhood = "";
           if(array_key_exists($likelyhood_id_val,$likelyhood_array)){
            $likelyhood = $likelyhood_array[$likelyhood_id_val];
           }
           $printHtml.="<tr>";
           $printHtml.="<td>".$behavioursupportdatamatrix['behaviuor']."</td>"; 
           $printHtml.="<td>".$likelyhood."</td>";
           $printHtml.="<td>".$behavioursupportdatamatrix['trigger']."</td>"; 
           $printHtml.="<td>".$behavioursupportdatamatrix['prevention_strategy']."</td>"; 
           $printHtml.="<td>".$behavioursupportdatamatrix['descalation_strategy']."</td>"; 
           $printHtml.="</tr>";
           }
           }else{
           $printHtml.="<tr><td></td><td></td><td></td><td></td><td></td></tr>";
           }           
           $printHtml.="</table>";

          // Behaviour Support Matrix End
         // Orders Start
         $printHtml.="<br>";
         $printHtml.="<h4>Orders</h4>";
         $printHtml.="<table>
         <tr>
         <th>Not applicable</th>
         <th>Intervention Orders </th>
         <th>Community Service Orders</th>
         <th>Community Correction Orders</th>
         </tr>
         <tr>";
         $not_applicable='No';
         if($orderdata_print['not_applicable'] == 2)
         {
           $not_applicable ="Yes";  
         }
        $printHtml.="<td>".$not_applicable."</td>";
        $orderdata_print_check = 0;
         $order_yesno_array = [];
         if($orderdata_print){
          $orderdata_print_check = 1;
         }
         if($orderdata_print_check == 1){
            $order_yesno_array = array('1'=>"No",'2'=>"Yes");
         }
         $inter_order = "";
         if(array_key_exists($orderdata_print['inter_order'],$order_yesno_array)){
         $inter_order = $order_yesno_array[$orderdata_print['inter_order']];
         }
         $com_ser_order = "";
         if(array_key_exists($orderdata_print['com_ser_order'],$order_yesno_array)){
         $com_ser_order = $order_yesno_array[$orderdata_print['com_ser_order']];
         }
         $com_cor_order = "";
         if(array_key_exists($orderdata_print['com_cor_order'],$order_yesno_array)){
         $com_cor_order = $order_yesno_array[$orderdata_print['com_cor_order']];
         }
         if($not_applicable=='No'){
            $printHtml.="<td>".$inter_order."</td>";
            $printHtml.="<td>".$com_ser_order."</td>";
            $printHtml.="<td>".$com_cor_order."</td>";
         }else{
            $printHtml.="<td></td>";
            $printHtml.="<td></td>";
            $printHtml.="<td></td>";
         }
       
         $printHtml.="</tr>";
         $printHtml.="</table>";
       // Orders  End

       // Risk Matrix Start
       $printHtml.="<h4> Risk Matrix</h4>";
       $printHtml.="<table>
       <tr>
       <th>Risk</th>
       <th>Probability</th>
       <th>Impact</td>
       <th>Score</td>
      </tr>";
      if((!empty($riskmatricesdatas_print)) && count($riskmatricesdatas_print) > 0){
         foreach ($riskmatricesdatas_print as $riskmatricesdata) {
       $propbality_id_val = $riskmatricesdata['probability_id'];
       $propbality_array = array('1'=>"Rare",'2'=>"Unlikely",'3'=>"Moderate",'4'=>"Likely","5"=>"Almost certain");
       $propbality_multiplier_array = array('Rare'=>"1",'Unlikely'=>"2",'Moderate'=>"3",'Likely'=>"4","Almost certain"=>"5");
       $propbality = "";
       if(array_key_exists($propbality_id_val,$propbality_array)){
        $propbality = $propbality_array[$propbality_id_val];
       }
       $impact_id_val = $riskmatricesdata['impact_id'];
       $impact_array = array('6'=>"Insignificant",'7'=>"Minor",'8'=>"Significant",'9'=>"Major","10"=>"Severe");
       $impact_multiplier_array = array('Insignificant'=>"1",'Minor'=>"2",'Significant'=>"3",'Major'=>"4","Severe"=>"5");
       $impact = "";
       if(array_key_exists($impact_id_val,$impact_array)){
        $impact = $impact_array[$impact_id_val];
       }
       $printHtml.="<tr>";
       $printHtml.="<td>".$riskmatricesdata['risk']."</td>"; 
       $printHtml.="<td>".$propbality."</td>";
       $printHtml.="<td>".$impact."</td>"; 
       $printHtml.="<td>".$impact_multiplier_array[$impact]*$propbality_multiplier_array[$propbality]."</td>"; 
       $printHtml.="</tr>";
       }
       }else{
       $printHtml.="<tr><td></td><td></td><td></td><td></td></tr>";
       }           
       $printHtml.="</table>";
       $printHtml.="</div>";
        // Risk Matrix  End
        $filename = 'Risk-Assessments-'.$this->input->get('risk_assessment_id').'.doc';
        header('Pragma: public');
        header("Cache-Control: private");
        header("Content-type: application/vnd.ms-word");
        header("Content-Disposition: attachment;filename=".$filename."");
        echo $printHtml;
    }
    
}
