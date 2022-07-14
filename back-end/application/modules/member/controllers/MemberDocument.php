<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class : MemberDocument
 * Uses : Used for handle Member document request and response  
 
 * Response type - Json format
 * 
 * Library
 * form_validation - used for validating the form data
 * 
 * LogType - crm
 * 
 * @property-read \MemberDocument_model $MemberDocument_model
 */
class MemberDocument extends MX_Controller {
    // Load custom validation traits function
    use formCustomValidation;

    // Defualt construct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        
        // load document model 
        $this->load->model('MemberDocument_model');
        $this->load->model('document/Document_attachment_model');
        $this->load->model('recruitment/Recruitment_applicant_model');

        // load amazon s3 library
        $this->load->library('AmazonS3');

        // set the log
        $this->loges->setLogType('member');
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

    /**
     * Check the amazon s3 connection
     */    
    function check_amazons3() {
        // get file
        $this->amazons3->setFolderKey('member/index.png');
        $amazons3_get = $this->amazons3->getDocument();
        
        pr($amazons3_get);
    }
    
    /*
     * For getting member document list
     * 
     * Return type json
     * - count 
     * - data
     * - status
     */
    function get_member_document_list() {
        $reqData = request_handler('access_member');
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'member_id', 'label' => 'Member Id', 'rules' => 'required'),
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            $applicant_result = $this->Recruitment_applicant_model->auth_applicant_info_by_member_id($reqData->data->member_id);

            if (isset($applicant_result) == true && empty($applicant_result) == false && isset($applicant_result->applicant_id)) {
                $reqData->data->applicant_id = $applicant_result->applicant_id;
            }
            
            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get member doucment list
                $result = $this->Document_attachment_model->get_document_list_for_portal($reqData->data);
            } else {
                // If requested data is empty or null
                $result = ['status' => false, 'error' => 'Member Id is null'];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];           
        }      
        echo json_encode($result);
        exit();  
    }

    /*
     * it is used for search docuement (tbl_person) in create member documebt
     * return type json
     */
    function get_document_name_search() {
        $reqData = request_handler('access_member');
        $name = $reqData->data->query ?? '';
        $rows = $this->MemberDocument_model->get_all_document_name_search($name);
        echo json_encode($rows);
        exit();
    }

    /*
     * For creating member document
     * Validate request and return response according to request
     * 
     * Return type json
     *  - status
     *  - msg 
     *  - error if any error  occured
     */
    function create_document_by_user_page() {
        // Get the request data
        $reqData = request_handlerFile('access_member',true,false,true);
        $adminId = $reqData->adminId;

        if (!empty($reqData)) {
            $data = (array) $reqData;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                array('field' => 'doc_type_id', 'label' => 'Document Type', 'rules' => 'required'),
                array('field' => 'issue_date_mandatory', 'label' => ' Issue Date Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Issue Date Validation" ]),
                array('field' => 'expiry_date_mandatory', 'label' => 'Expiry Date ', 'rules' => 'required', "errors" => [ "required" => "Missing Expiry Date Validation" ]),
                array('field' => 'reference_number_mandatory', 'label' => 'Reference Number Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Reference Number Validation" ])
            ];

            // validate id based on user page
            if($data['user_page']=='member'){
                $validation_rules[] = array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required', "errors" => [ "required" => "Missing member id" ]);               
            }

            if(!empty($data['doc_name']) &&  $data['doc_name']==VISA_DETAILS && $data['user_page']=='member'){
                $validation_rules[] = array('field' => 'visa_category', 'label' => 'Visa Category', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Category id" ]);
                $validation_rules[] = array('field' => 'visa_category_type', 'label' => 'Visa type', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Type id" ]);
            }

            if($data['user_page']=='participants'){
                $validation_rules[] = array('field' => 'participant_id', 'label' => 'Member', 'rules' => 'required', "errors" => [ "required" => "Missing participant id" ]);
            }
            /**
              * Dynamic validation fields related with document type
              * - Issue Date
              * - Expiry Date
              * - Reference Number
              */
            $issue_date_mandatory = filter_var($data['issue_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($issue_date_mandatory == true && ($data['issue_date'] == '' || $data['issue_date'] == null)) {
                $validation_rules[] = array(
                    'field' => 'issue_date', 'label' => 'Issue Date', 'rules' => 'required'
                );
            }

            $expiry_date_mandatory = filter_var($data['expiry_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($expiry_date_mandatory == true && ($data['expiry_date'] == '' || $data['expiry_date'] == null)) {
                $validation_rules[] = array(
                    'field' => 'expiry_date', 'label' => 'Expiry Date', 'rules' => 'required'
                );
            }

            $reference_number_mandatory = filter_var($data['reference_number_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($reference_number_mandatory == true && ($data['reference_number'] == '' || $data['reference_number'] == null)) {
                $validation_rules[] = array(
                    'field' => 'reference_number', 'label' => 'Reference Number', 'rules' => 'required'
                );
            }

            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {

                $this->load->model('Basic_model');

                // Call create document model
                $document = $this->Document_attachment_model->save_document_attachment($data, $adminId, false, $data['user_page']);
                // According to that document will be created
                if ($document['status'] == true) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    /**
                     * Create logs. it will represent the user action they have made.
                     */
                    $this->loges->setTitle("New document created for " . $data['doc_name'] ." by " . $adminName);  // Set title in log
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    // Create log
                    $this->loges->createLog(); 
                    $data = array('document_id' => $document['document_id']);
                    $response = ['status' => true, 'msg' => 'Document has been created successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $document['error']];
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
        if (! preg_match('/^[a-zA-Z\s]+$/', $str)) {
            // Set error msg
            $this->form_validation->set_message('alpha_dash_space', 'Name must contain only alphabetical & numeral characters');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /*
     * For getting document by id
     * 
     * Return type json
     * - data
     * - status
     */
    function get_member_doucment_data_by_id() {
        $reqData = request_handler('');
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'document_id', 'label' => 'Document Id', 'rules' => 'required'),
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get membver document list
            	$result = $this->Document_attachment_model->get_doucment_attachment_data_by_id($reqData->data);
            } else {
                // If requested data is empty or null
                $result = ['status' => false, 'error' => 'Document Id is null'];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }

    /*
     * For edit document
     * Validate request and return respone according to request
     * 
     * Return type json
     *  - status
     *  - msg 
     *  - error if any error will be occur
     */
    function edit_document_by_user_page() {
        // Get the request data
        $reqData = request_handlerFile('access_member');
        $adminId = $reqData->adminId;

        if (!empty($reqData)) {
            $data = (array) $reqData;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                 array('field' => 'document_id', 'label' => 'Document', 'rules' => 'required', "errors" => [ "required" => "Missing Document Id" ]),
                array('field' => 'doc_type_id', 'label' => 'Document Type', 'rules' => 'required'),
                // array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required', "errors" => [ "required" => "Missing member id" ]),
                array('field' => 'issue_date_mandatory', 'label' => ' Issue Date Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Issue Date Validation" ]),
                array('field' => 'expiry_date_mandatory', 'label' => 'Expiry Date ', 'rules' => 'required', "errors" => [ "required" => "Missing Expiry Date Validation" ]),
                array('field' => 'reference_number_mandatory', 'label' => 'Reference Number Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Reference Number Validation" ])
            ];

            // validate id based on user page
            if($data['user_page']=='member'){
                $validation_rules[] = array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required', "errors" => [ "required" => "Missing member id" ]);             
            }

            if($data['user_page']=='member' && $data['doc_name']==VISA_DETAILS){
                $validation_rules[] = array('field' => 'visa_category', 'label' => 'Visa Category', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Category id" ]);
                $validation_rules[] = array('field' => 'visa_category_type', 'label' => 'Visa type', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Type id" ]);
            }

            if($data['user_page']=='participants'){
                $validation_rules[] = array('field' => 'participant_id', 'label' => 'Participant', 'rules' => 'required', "errors" => [ "required" => "Missing participant id" ]);
            }

            /**
              * Dynamic validation fields related with document type
              * - Issue Date
              * - Expiry Date
              * - Reference Number
              */
            $issue_date_mandatory = filter_var($data['issue_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($issue_date_mandatory == true && ($data['issue_date'] == '' || $data['issue_date'] == null)) {
                $validation_rules[] = array(
                    'field' => 'issue_date', 'label' => 'Issue Date', 'rules' => 'required'
                );
            }

            $expiry_date_mandatory = filter_var($data['expiry_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($expiry_date_mandatory == true && ($data['expiry_date'] == '' || $data['expiry_date'] == null)) {
                $validation_rules[] = array(
                    'field' => 'expiry_date', 'label' => 'Expiry Date', 'rules' => 'required'
                );
            }

            $reference_number_mandatory = filter_var($data['reference_number_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($reference_number_mandatory == true && ($data['reference_number'] == '' || $data['reference_number'] == null)) {
                $validation_rules[] = array(
                    'field' => 'reference_number', 'label' => 'Reference Number', 'rules' => 'required'
                );
            }

            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {

                $this->load->model('Basic_model');

                // Call create member document model
                $document = $this->Document_attachment_model->edit_document_attachment($data, $adminId, false, $data['user_page']);
                // According to that document will be created
                if ($document['status'] == true) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    /**
                     * Create logs. it will represent the user action they have made.
                     */
                    $this->loges->setTitle("New document updated for " . $data['doc_name'] ." by " . $adminName);  // Set title in log
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    // Create log
                    $this->loges->createLog(); 
                    $data = array('document_id' => $document['document_id']);
                    $response = ['status' => true, 'msg' => 'Document has been created successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $document['error']];
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
     * Mark document as archived.
     */
    public function archive_document() {
        $reqData = request_handler();
        $data = $reqData->data;
        $adminId = $reqData->adminId;
        $id = isset($data->id) ? $data->id : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing Document ID"];
            echo json_encode($response);
            exit();
        }  

        require_once APPPATH . 'Classes/document/DocumentAttachment.php';
        
        $docAttachObj = new DocumentAttachment();
        
        $entity_type = $docAttachObj->getConstant('ENTITY_TYPE_MEMBER');

        // Call archive member document model
        $document = $this->Document_attachment_model->archive_document_attachment_v1($adminId, $id, $entity_type);

        # logging action
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully archived document with ID of %s by %s", $id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully archived Document ID of %s by %s", $id, $adminName));  // set title in log
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        $response = ['status' => true, 'msg' => "Successfully archived document"];
        echo json_encode($response);
        exit();
    }
}
