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
class MemberDocumentMemberPortal extends MX_Controller {
    // Load custom validation traits function
    use formCustomValidation;

    const  DRAFT_STATUS = 4;

    // Defualt construct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        
        // load document model 
        $this->load->model('MemberDocument_model');
        $this->load->model('document/Document_attachment_model');
        $this->load->model('recruitment/Recruitment_applicant_model');

        $this->load->model('member/models/Member_model');
        $this->load->model('schedule/Schedule_model');

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
        $reqData = request_handler();
        // pr($reqData);
        if (!empty($reqData)) {
            $data = $reqData->data;

            $error_check_id = false;

            $member_id = '';
            if (isset($data->member_id) == true && empty($data->member_id) == false) {
                $member_id = $data->member_id;
            }

            $applicant_id = '';
            if (isset($data->applicant_id) == true && empty($data->applicant_id) == false) {
                $applicant_id = $data->applicant_id;
            }
            // pr($data->applicant_id);
            // pr($data->member_id);
            if (($member_id == '' || $member_id == 'null') && ($applicant_id == '' || $applicant_id == 'null')) {
                $error_check_id = true;
            }
            
            // Check data is valid or not
            if ($error_check_id == false) {
                // Call model for get member doucment list
                $result = $this->Document_attachment_model->get_document_list_for_portal($data, true);
            } else {
                // If requested data is empty or null
                $result = ['status' => false, 'error' => 'Member or Applicant Id is null'];
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
        $reqData = request_handler();
        $name = $reqData->data->query ?? '';
        $is_member = $reqData->data->is_member ?? '';
        $is_portal = $reqData->data->portal ?? '';
        $is_member = filter_var($is_member, FILTER_VALIDATE_BOOLEAN);
        if ($is_member == true) {
            $module = 'member';
        } else {
            $module = 'applicant';
        }
        $rows = $this->MemberDocument_model->get_all_document_name_search($name, $module);
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
    function create_member_document() {
        // draft const value
        $draft_status = self::DRAFT_STATUS;
        // Get the request data
        $reqData = request_handlerFile('access_member',true,false,true);
        $adminId = $reqData->member_id;
        // API hit other than frontend
    
        
        if (!empty($reqData)) {
            $data = (array) $reqData;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                array('field' => 'doc_type_id', 'label' => 'Document Type', 'rules' => 'required'),
                array('field' => 'is_member', 'label' => 'Is Member', 'rules' => 'required'),
                array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required', "errors" => [ "required" => "Missing member id" ]),
                array('field' => 'issue_date_mandatory', 'label' => ' Issue Date Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Issue Date Validation" ]),
                array('field' => 'expiry_date_mandatory', 'label' => 'Expiry Date ', 'rules' => 'required', "errors" => [ "required" => "Missing Expiry Date Validation" ]),
                array('field' => 'reference_number_mandatory', 'label' => 'Reference Number Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Reference Number Validation" ])
            ];

            $status = '';
            if (isset($data['status']) == true && $data['status'] != '') {
                $status = $data['status'];
            }

            if(!empty($data['doc_name']) && $data['doc_name']==VISA_DETAILS) {
                $validation_rules[] = array('field' => 'visa_category', 'label' => 'Visa Category', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Category id" ]);
                $validation_rules[] = array('field' => 'visa_category_type', 'label' => 'Visa type', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Type id" ]);
            }
            
            /**
              * Dynamic validation fields related with document type if status not draft
              * - Issue Date
              * - Expiry Date
              * - Reference Number
              */
            $issue_date_mandatory = filter_var($data['issue_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($issue_date_mandatory == true && ($data['issue_date'] == '' || $data['issue_date'] == null) && $status != $draft_status) {
                $validation_rules[] = array(
                    'field' => 'issue_date', 'label' => 'Issue Date', 'rules' => 'required'
                );
            }

            $expiry_date_mandatory = filter_var($data['expiry_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($expiry_date_mandatory == true && ($data['expiry_date'] == '' || $data['expiry_date'] == null) && $status != $draft_status) {
                $validation_rules[] = array(
                    'field' => 'expiry_date', 'label' => 'Expiry Date', 'rules' => 'required'
                );
            }

            $reference_number_mandatory = filter_var($data['reference_number_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($reference_number_mandatory == true && ($data['reference_number'] == '' || $data['reference_number'] == null) && $status != $draft_status) {
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

                // Set Portal By
                $data['created_portal'] = 2;
                
                $is_member = filter_var($data['is_member'], FILTER_VALIDATE_BOOLEAN);
                if ($is_member == true) {
                    $module = 'member';
                } else {
                    $module = 'applicant';
                }
                // Call create document model
                $document = $this->Document_attachment_model->save_document_attachment($data, $adminId, false, $module);
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
            $this->form_validation->set_message('alpha_dash_space', 'Name field contain only alphabetical characters');
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
        $reqData = request_handler();
        if (!empty($reqData)) {
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
    function edit_member_document() {
         // draft const value
        $draft_status = self::DRAFT_STATUS;
        
        // Get the request data
        $reqData = request_handlerFile();
        $adminId = $reqData->member_id;

        if (!empty($reqData)) {
            $data = (array) $reqData;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                 array('field' => 'document_id', 'label' => 'Document', 'rules' => 'required', "errors" => [ "required" => "Missing Document Id" ]),
                array('field' => 'doc_type_id', 'label' => 'Document Type', 'rules' => 'required'),
                array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required', "errors" => [ "required" => "Missing member id" ]),
                array('field' => 'issue_date_mandatory', 'label' => ' Issue Date Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Issue Date Validation" ]),
                array('field' => 'expiry_date_mandatory', 'label' => 'Expiry Date ', 'rules' => 'required', "errors" => [ "required" => "Missing Expiry Date Validation" ]),
                array('field' => 'reference_number_mandatory', 'label' => 'Reference Number Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Reference Number Validation" ])
            ];

            $status = '';
            if (isset($data['status']) == true && $data['status'] != '') {
                $status = $data['status'];
            }

            if(!empty($data['doc_name']) && $data['doc_name']==VISA_DETAILS) {
                $validation_rules[] = array('field' => 'visa_category', 'label' => 'Visa Category', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Category id" ]);
                $validation_rules[] = array('field' => 'visa_category_type', 'label' => 'Visa type', 'rules' => 'required', "errors" => [ "required" => "Missing Visa Type id" ]);
            }
            
            /**
              * Dynamic validation fields related with document type if status not draft
              * - Issue Date
              * - Expiry Date
              * - Reference Number
              */
            $issue_date_mandatory = filter_var($data['issue_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($issue_date_mandatory == true && ($data['issue_date'] == '' || $data['issue_date'] == null) && $status != $draft_status) {
                $validation_rules[] = array(
                    'field' => 'issue_date', 'label' => 'Issue Date', 'rules' => 'required'
                );
            }

            $expiry_date_mandatory = filter_var($data['expiry_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($expiry_date_mandatory == true && ($data['expiry_date'] == '' || $data['expiry_date'] == null) && $status != $draft_status) {
                $validation_rules[] = array(
                    'field' => 'expiry_date', 'label' => 'Expiry Date', 'rules' => 'required'
                );
            }

            $reference_number_mandatory = filter_var($data['reference_number_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($reference_number_mandatory == true && ($data['reference_number'] == '' || $data['reference_number'] == null) && $status != $draft_status) {
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

                $is_member = filter_var($data['is_member'], FILTER_VALIDATE_BOOLEAN);
                if ($is_member == true) {
                    $module = 'member';
                } else {
                    $module = 'applicant';
                }
                // Call create member document model
                $document = $this->Document_attachment_model->edit_document_attachment($data, $adminId, false, $module);
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
                    $response = ['status' => true, 'msg' => 'Document has been updated successfully.', 'data' => $data ];
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
        $data = $reqData;
        $adminId = $reqData->member_id;
        $id = isset($data->document_id) ? $data->document_id : 0;
        $applicant_id = isset($data->applicant_id) ? $data->applicant_id : 0;
        $is_member = isset($data->is_member) ? $data->is_member : 'false';

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing Document ID"];
            echo json_encode($response);
            exit();
        }

        require_once APPPATH . 'Classes/document/DocumentAttachment.php';

        $docAttachObj = new DocumentAttachment();

        $is_member = filter_var($is_member, FILTER_VALIDATE_BOOLEAN);
        if ($is_member == true) {
            $module = 'member';
            $entity_type = $docAttachObj->getConstant('ENTITY_TYPE_MEMBER');
        } else {
            $module = 'applicant';
            $adminId = $applicant_id;
            $entity_type = $docAttachObj->getConstant('ENTITY_TYPE_APPLICANT');
        }

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

    /**
     * Retrieve member details.
     */
    public function get_member_details() {
        $reqData = request_handler();
        $data = $reqData;

        if (empty($data->id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        $result = $this->Member_model->get_member_details($data->id);
        echo json_encode($result);
        exit();
    }

    public function get_state() {
        $reqData = request_handler();
        $state = $this->Common_model->get_state();

        echo json_encode(array('status' => true, 'data' => $state));
    }

    /** Verify document download like proper document against proper user */
    function verifydocumentDownload() {
        $reqData = request_handler();
        $result = ['status' => FALSE, 'msg' => 'Invalid Request!'];
        if (!empty($reqData)) {
            $data = $reqData->data;
            switch ($data->page) {
                case 'document':
                    $result = $this->Document_attachment_model->verifydocumentDownload($data->applicant_id, $data->member_id, $data->doc_id, base64_decode(str_replace('%3D','', $data->file_path)));
                    break;
                case 'shift':
                    $result = $this->Schedule_model->get_shift_member_details_frm_member_id($data->member_id,  $data->doc_id);
                    if(empty($result['status'])) {
                        $result = ['status' => FALSE, 'msg' => 'Document not matched!'];                        
                    } 
                    break;
                case 'NA':                    
                    $result = $this->Schedule_model->check_shift_NA_assets($data->member_id, $data->doc_id, $data->ac);

                    if(empty($result['status'])) {
                        $result = ['status' => FALSE, 'msg' => 'Document not matched!'];
                    }
                    break;
                default:
                     $result = ['status' => FALSE, 'msg' => 'Something Went wrong!'];
                    break;
            }
          
        }
        if(!empty($result['status'])) {
            $token = random_genrate_password(10);
            #Set the token for download verification
            $this->basic_model->insert_records('file_download_validation', ['token' => $token]);
            $result = ['status' => TRUE, 'token' => $token, 'msg' => 'Document matched!'];
        }
        echo json_encode($result);
        exit();
    }

}
