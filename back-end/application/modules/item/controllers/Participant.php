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
class Participant extends MX_Controller {
    // Load custom validation traits function
    use formCustomValidation;

    // Defualt construct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('DocuSignEnvelope');
        $this->form_validation->CI = &$this;

        // load document model
        $this->load->model('Participant_model');
        $this->load->model('sales/ServiceAgreement_model');
        $this->load->model('common/List_view_controls_model');

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
    function get_participant_list() {
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
            // Call model for get participant list
            $filter_condition = str_replace(['updated_by'],
             ['tp.updated_by'], $filter_condition);
            $result = $this->Participant_model->get_participant_list($reqData->data, $filter_condition, $reqData->uuid_user_type);
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
    function get_contact_person_name_search() {
        $reqData = request_handler();
        $name = $reqData->data->query ?? '';
        $rows = $this->Participant_model->get_all_person_name_search($name);
        echo json_encode($rows);
    }

    /*
    * get all reference data of status while assigning members to participant
    */
    function get_participant_member_ref_data() {
        $reqData = request_handler('access_member');
        $rows = $this->basic_model->get_result('references', ['archive'=>0,'type'=>24],['id as value','display_name as label']);
        echo json_encode(["status" => true, "data" => $rows]);
    }

    /**
     * Mark participant member as archived.
     */
    public function archive_participant_member() {
        $reqData = request_handler();
        $adminId = $reqData->adminId;

        $data = object_to_array($reqData->data);
        $response = $this->Participant_model->archive_participant_member($data, $adminId);

        echo json_encode($response);
        exit();
    }

    /*
     * For getting participant members list
     */
    public function get_participant_member_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Participant_model->get_participant_member_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * For getting participant members list
     */
    public function get_participant_members() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Participant_model->get_participant_members(object_to_array($reqData->data));
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * controller function used for assigning one or more members to a participant
     */
    public function assign_participant_members() {
        $reqData = request_handler('');
        $adminId = $reqData->adminId;

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (empty($reqData->data))
        return $response;

        $data = object_to_array($reqData->data);
        $response = $this->Participant_model->assign_participant_members($data, $adminId);
        echo json_encode($response);
        exit();
    }

    /*
     * For creating participant
     * Validate request and return response according to request
     *
     * Return type json
     *  - status
     *  - msg
     *  - error if any error  occured
     */
    function create_participant() {
        // Get the requested data
        $reqData = request_handler('');
        $adminId = $reqData->adminId;

        if (empty($reqData->data)) {
            //  Response initialize
            $response = ['status' => false, 'error' => 'Requested data is null'];
        }
        else {
            $response = $this->Participant_model->create_update_participant((array) $reqData->data, $adminId);
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
     * Get participant detail
     *
     * Return type json
     * - data
     * - status
     * - msg
     */
    function get_participant_detail_by_id() {
        $reqData = request_handler();

        if (!empty($reqData->data)) {
            $data = $reqData->data;
            if (isset($data->participant_id) == true) {
                // Call model for get participant list
                $result = $this->Participant_model->get_participant_detail_by_id($reqData->data, $reqData->uuid_user_type);
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
     * For getting participant by id
     *
     * Return type json
     * - data
     * - status
     */
    function get_participant_data_by_id() {
        $reqData = request_handler('');
        if (!empty($reqData->data)) {
            // Call model for get paricipant
            $result = $this->Participant_model->get_participant_data_by_id($reqData->data);
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
    function edit_participant() {
        // Get the requested data
        $reqData = request_handler('');
        $adminId = $reqData->adminId;

        if (empty($reqData->data)) {
            //  Response initialize
            $response = ['status' => false, 'error' => 'Requested data is null'];
        }
        else {
            $response = $this->Participant_model->create_update_participant((array) $reqData->data, $adminId);
        }
        echo json_encode($response);
        exit();
    }

    /*
     * For getting participant document list
     *
     * Return type json
     * - count
     * - data
     * - status
     */
    function get_participant_document_list() {
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
                // Call model for get participant doucment list
                $result = $this->Participant_model->get_participant_document_list($reqData->data);
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

    /**
     * Get service agreement linked with participant
     */
    function get_service_agreement_linked() {
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
                // Call model for get participant SA list
                $participant_id = $data['participant_id'];
                $result = $this->Participant_model->get_service_agreement_linked($participant_id);
            } else {
                // If requested data is empty or null
                $result = ['status' => false, 'error' => 'Participant Id is null'];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Get service agreements documents linked with participant
     */
    function get_service_agreement_documents() {
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
                // Call model for get participant SA list
                $participant_id = $data['participant_id'];
                $filtered = $data['filtered']?? [];
                $result = $this->Participant_model->get_service_agreement_documents($participant_id, $filtered);
                $participant = $this->Participant_model->get_participant_data_by_id($reqData->data);
                $result['participant'] = $participant['data'];
            } else {
                // If requested data is empty or null
                $result = ['status' => false, 'error' => 'Participant Id is null'];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($result);
        exit();
    }

    //Get the Service agreement docu sign details
    function get_service_agreement_linked_docu_sign() {
        $reqData = request_handler();

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $participant_id = $data['participant_id'];
            $results = $this->Participant_model->get_service_agreement_linked($participant_id);

            if(!empty($results) && !empty($results['data'])) {
                $result = $this->Participant_model->get_service_agreement_linked_docu_sign($results['data']);
            } else {
                $result = $results;
            }

            echo json_encode($result);
        }
    }

    public function get_staff_safety_checklist() {
        $reqData = request_handler('access_crm');
        $this->load->model("sales/Opportunity_model");
        $result = $this->Opportunity_model->get_staff_safety_checklist($reqData);
        echo json_encode(['data' => $result, 'status' => true]);
    }
    function resend_doc() {
        $reqData = request_handler();
        $service_agreement_attachment_id = $reqData->data->id;
        $contract_data = $this->ServiceAgreement_model->get_sa_contract_details($service_agreement_attachment_id);
        if(empty($contract_data)){
            echo json_encode(array('status' => false,'error' => 'Service agreement document is missing'));
            exit();
        }
        
        $signerDetails=array();
        $signerDetails['name']= $contract_data['to_name'];
        $signerDetails['email_subject']='Please sign service agreement contract';
        $signerDetails['email']=$contract_data['to_email'];

              // Envelope position details
        $position=array();
        $position['position_x']=100;
        $position['position_y']=100;
        $position['document_id']=1;
        $position['page_number']=1;
        $position['recipient_id']=1;

        $envlopDetails=array();
        $envlopDetails['userdetails']=$signerDetails;
        $envlopDetails['position']=$position;
        $envlopDetails['envelopeId']=$contract_data['envelope_id'];
        $response=$this->docusignenvelope->ResendEnvelope($envlopDetails);
        echo json_encode($response);
        exit();
    }
}
