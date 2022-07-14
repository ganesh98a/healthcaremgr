<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class : Document
 * Uses : Used for handle Document request and response

 * Response type - Json format
 *
 * Library
 * form_validation - used for validating the form data
 *
 * LogType - item
 *
 * @property-read \Document_model $Document_model
 */
class Document extends MX_Controller {
    // Load custom validation traits function
    use formCustomValidation;

    var $mandatory_labels = [
        "1" => "Yes",
        "2" => "No",
    ];

    // Defualt construct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        // load document model
        $this->load->model('Document_model');

        $this->load->model('document/Document_type_model');

        // set the log
        $this->loges->setLogType('item');
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

    /**
     * fetches mandatory values and their label
     */
    function get_mandatory_labels() {
        $reqData = request_handler();

        if (!empty($reqData->data)) {
            $data = null;
            foreach($this->mandatory_labels as $value => $label) {
                $newrow = null;
                $newrow['label'] = $label;
                $newrow['value'] = $value;
                $data[] = $newrow;
            }
            $response = array('status' => true, 'data' => $data);

            echo json_encode($response);
        }
    }

    /*
     * For getting document list
     *
     * Return type json
     * - count
     * - data
     * - status
     */
    function get_document_list() {
        $reqData = request_handler();
        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
        }
        if (!empty($reqData->data)) {
            // Call model for get_document_list

            $filter_condition = str_replace(["title", 'active', 'issue_date_mandatory', 'expire_date_mandatory', 'reference_number_mandatory'], 
            ["td.title", "active", "issue_date_mandatory", "expire_date_mandatory", "reference_number_mandatory"], $filter_condition);
            $result = $this->Document_model->get_document_list($reqData->data, $filter_condition);
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
       
    }

    /**
     * Retrieve role document details.
     */
    public function get_role_doc_details() {
        $reqData = request_handler();
        $data = $reqData->data;

        if (empty($data->id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        $result = $this->Document_model->get_role_doc_details($data->id);
        echo json_encode($result);
        exit();
    }

    /**
     * fetches only role specific documents. Need to have 'role_id' or 'doc_id' in the data element
     */
    function get_role_documents() {
        $reqData = request_handler();
        if (!empty($reqData->data) || !(empty($reqData->data->role_id) && empty($reqData->data->doc_id))) {
            // Call model for get roles & documents list
            $result = $this->Document_model->get_role_documents($reqData->data);
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * its used for fetching list of document names based on @param $name
     * return type json
     */
    function get_document_name_search() {
        $reqData = request_handler();
        $name = $reqData->data->query ?? '';
        $rows = $this->Document_model->get_document_name_search($name);
        echo json_encode($rows);
    }

    /*
     * its used for fetching list of role names based on @param $name
     * return type json
     */
    function get_role_name_search() {
        $reqData = request_handler();
        $name = $reqData->data->query ?? '';
        $rows = $this->Document_model->get_role_name_search($name);
        echo json_encode($rows);
    }

    /*
     * its used for fetching list of role names based on @param $name related to account
     * return type json
     */
    function get_rolename_for_account() {
        $reqData = request_handler();
        $name = $reqData->data->query ?? '';
        $id = $reqData->data->id ?? '';
        $account_type = $reqData->data->account_type ?? '';
        $rows = $this->Document_model->get_rolename_for_account($reqData);
        echo json_encode($rows);
    }

    /*
     * its used for create/update role & document attachment
     */
    function attach_document_and_role() {
        $reqData = request_handler('');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $doc_role_id = $data['id'] ?? 0;

            # validation rule
            $validation_rules = [
                array('field' => 'doc_id', 'label' => 'Document', 'rules' => 'required'),
                array('field' => 'role_id', 'label' => 'Role', 'rules' => 'required'),
                array(
                    'field' => 'start_date', 'label' => 'start date', 'rules' => 'required|valid_date_format[Y-m-d]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect start date',
                    ]
                ),
            ];

            # checking the end date in correct format
            if (!empty($data['end_date'])) {
                $validation_rules[] = array(
                    'field' => 'end_date', 'label' => 'end date', 'rules' => 'valid_date_format[Y-m-d]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect end date',
                    ]
                );
            }

            # checking if the document and role attachment is not previously added
            if(!empty($data['doc_id']) && !empty($data['role_id']))
            {
                $rows = $this->Document_model->check_role_doc_already_exist($data['role_id'],$data['doc_id'],$doc_role_id);
                if(!empty($rows))
                {
                    $errors = 'Document "'.$rows[0]['title'].'" already attached to this role';
                    $return = array('status' => false, 'error' => $errors);
                    echo json_encode($return);exit();
                }
            }

            # set data in libray for validate
            $this->form_validation->set_data($data);

            # set validation rule
            $this->form_validation->set_rules($validation_rules);

            # check data is valid or not
            if ($this->form_validation->run()) {

                # call create member modal function
                $doc_role_id = $this->Document_model->attach_document_and_role($data, $adminId);

                # check $member_id is not empty
                if ($doc_role_id) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    # create log setter getter
                    if (!empty($data['id'])) {
                        $msg = 'Role & document attachment has been updated successfully.';
                        $this->loges->setTitle("Updated role & document " . $data['id'] . " by " . $adminName);
                    } else {
                        $msg = 'Role & document attachment has been created successfully.';
                        $this->loges->setTitle("New role & document created by " . $adminName);
                    }
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($doc_role_id);
                    $this->loges->setCreatedBy($adminId);
                    $this->loges->createLog();

                    $response = ['status' => true, 'msg' => $msg];
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

    /**
     * Mark role document as archived.
     */
    public function archive_role_document() {
        $reqData = request_handler();
        $data = $reqData->data;
        $adminId = $reqData->adminId;
        $id = isset($data->role_doc_id) ? $data->role_doc_id : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        # does the role document exist?
        $result = $this->Document_model->get_role_doc_details($id);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Role document does not exist anymore."];
            echo json_encode($response);
            exit();
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("document_role", $upd_data, ["id" => $id]);

        # logging action
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully archived role document with ID of %s by %s", $id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully archived role document with ID of %s by %s", $id, $adminName));  // set title in log
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        $response = ['status' => true, 'msg' => "Successfully archived role document"];
        echo json_encode($response);
        exit();
    }

    /*
     * For creating document
     * Validate request and return response according to request
     *
     * Return type json
     *  - status
     *  - msg
     *  - error if any error  occured
     */
    function create_document() {
        $reqData = request_handler();
        // Get the request data
        $adminId = $reqData->adminId;
        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                array('field' => 'title', 'label' => 'Document Name', 'rules' => 'required|callback_alpha_dash_space'),
                array('field' => 'active', 'label' => 'Active', 'rules' => 'required', "errors" => [ "required" => "Active value is null"]),
                array('field' => 'issue_date_mandatory', 'label' => 'Issue Date Mandatory', 'rules' => 'required', "errors" => [ "required" => "Issue Date Mandatory value is null"]),
                array('field' => 'expire_date_mandatory', 'label' => 'Expire Date Mandatory', 'rules' => 'required', "errors" => [ "required" => "Expire Date Mandatory value is null"]),
                array('field' => 'reference_number_mandatory', 'label' => 'Reference Number Mandatory', 'rules' => 'required', "errors" => [ "required" => "Reference Number Mandatory value is null"]),
            ];

            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {

                $this->load->model('Basic_model');

                // Check document is exist. Using title
                $title = $data['title'];
                $where = array('title' => $title);
                $colown = array('id', 'title');
                $check_document = $this->basic_model->get_record_where('document_type', $colown, $where);

                // If not exist
                if (!$check_document) {
                    // Call create document model
                    $documentId = $this->Document_model->create_document($data, $adminId);

                    // Check $documentId is empty or not
                    // According to that document will be created
                    if ($documentId) {
                        $this->load->library('UserName');
                        $adminName = $this->username->getName('admin', $adminId);

                        /**
                         * Create logs. it will represent the user action they have made.
                         */
                        $this->loges->setTitle("New document created for " . $data['title'] ." by " . $adminName);  // Set title in log
                        $this->loges->setDescription(json_encode($data));
                        $this->loges->setUserId($adminId);
                        $this->loges->setCreatedBy($adminId);
                        // Create log
                        $this->loges->createLog();
                        $data = array('document_id' => $documentId);
                        $response = ['status' => true, 'msg' => 'Document has been created successfully.', 'data' => $data ];
                    } else {
                        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                    }
                } else {
                     $response = ['status' => false, 'error' => 'Document already exist '];
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
        if (! preg_match('/^[a-zA-Z\s_\-0-9]+$/', $str)) {
            // Set error msg
            $this->form_validation->set_message('alpha_dash_space', 'Name must contain only alphabetical & numeral characters');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /*
     * Get document detail
     *
     * Return type json
     * - data
     * - status
     * - msg
     */
    function get_document_detail_by_id() {
        $reqData = request_handler();

        if (!empty($reqData->data)) {
            $data = $reqData->data;
            if (isset($data->document_id) == true) {
                // Call model for get risk assessment list
                $result = $this->Document_model->get_document_detail_by_id($reqData->data);
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

    /** Get document type related datas */
    function get_document_details() {
        request_handler('access_crm');

        $res["doc_category_options"] = $this->Document_type_model->get_document_category();
        $res["doc_related_to_options"] = DOC_RELATED_TO_LIST;
        echo json_encode(["status" => true, "data" => $res]);

    }

    /*
     * Get document data for edit
     *
     * Return type json
     * - data
     * - status
     * - msg
     */
    function get_document_data_by_id() {
        $reqData = request_handler();

        if (!empty($reqData->data)) {
            $data = $reqData->data;
            if (isset($data->document_id) == true) {
                // Call model for get risk assessment list
                $result = $this->Document_model->get_document_data_by_id($reqData->data);
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
     * For updating document
     * Validate request and return response according to request
     *
     * Return type json
     *  - status
     *  - msg
     *  - error if any error  occured
     */
    function update_document() {
        $reqData = request_handler();
        // Get the request data
        $adminId = $reqData->adminId;
        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                array('field' => 'document_id', 'label' => 'Document Id', 'rules' => 'required', "errors" => [ "required" => "Document Id is missing"]),
                array('field' => 'title', 'label' => 'Document Name', 'rules' => 'required'),
                array('field' => 'active', 'label' => 'Active', 'rules' => 'required', "errors" => [ "required" => "Active value is null"]),
                array('field' => 'issue_date_mandatory', 'label' => 'Issue Date Mandatory', 'rules' => 'required', "errors" => [ "required" => "Issue Date Mandatory value is null"]),
                array('field' => 'expire_date_mandatory', 'label' => 'Expire Date Mandatory', 'rules' => 'required', "errors" => [ "required" => "Expire Date Mandatory value is null"]),
                array('field' => 'reference_number_mandatory', 'label' => 'Reference Number Mandatory', 'rules' => 'required', "errors" => [ "required" => "Reference Number Mandatory value is null"]),
            ];

            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {

                $this->load->model('Basic_model');

                // Call create document model
                $documentId = $this->Document_model->update_document($data, $adminId);

                // Check $documentId is empty or not
                // According to that document will be created
                if ($documentId) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    /**
                     * Create logs. it will represent the user action they have made.
                     */
                    $this->loges->setTitle("New document updated for " . $data['title'] ." by " . $adminName);  // Set title in log
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    // Create log
                    $this->loges->createLog();
                    $data = array('document_id' => $documentId);
                    $response = ['status' => true, 'msg' => 'Document has been updated successfully.', 'data' => $data ];
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
     * it is used to get the visa category list
     * return type json
     */
    function get_all_visa_category() {
        $reqData = request_handler();
        $name = $reqData->data->query ?? '';
        $rows = $this->Document_model->get_all_visa_category();
        echo json_encode($rows);
        exit();
    }

    /*
     * it is used to get the visa type list by visa category id
     * return type json
     */
    function get_all_visa_type_by_visa_category() {
        $reqData = request_handler();
        $visa_category = $reqData->data->visa_category ?? '';
        $rows = $this->Document_model->get_all_visa_type_by_visa_category($visa_category);
        echo json_encode($rows);
        exit();
    }
}
