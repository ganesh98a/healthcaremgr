<?php

use XeroPHP\Models\Accounting\Receipt;

defined('BASEPATH') OR exit('No direct script access allowed');

require_once(__DIR__."/ServiceAgreementContract.php");

/**
 * Controller for 'Serivce Agreement' objects
 * 
 * @property-read ServiceAgreement_model $ServiceAgreement_model
 */
class ServiceAgreement extends ServiceAgreementContract
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->load->model('ServiceAgreement_model');
        $this->load->model('sales/Lead_model');
        $this->loges->setLogType('crm');
        $this->load->helper('message');
        $this->load->library('Asynclibrary');
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
     * Fetch all service agreement items
     */
    public function get_service_agreement_list()
    {
        $reqData = request_handler('access_crm');
        
        $this->output->set_content_type('json');
        $result = $this->ServiceAgreement_model->get_service_agreement_list($reqData->data);
        return $this->output->set_output(json_encode($result));
    }


    public function get_service_agreement_details() {
        $reqData = request_handler('access_crm');
        $this->output->set_content_type('json');
        $result = $this->ServiceAgreement_model->get_service_agreement_details($reqData->data->id);

        if ($result["status"]) {
            $res = $result["data"];
                    
            //pass key name for type which type option need
            $this->load->model("Common/common_model");
            $result["data"]["declined_reason_option"] = $this->common_model->get_central_reference_data_option("declined_reason_service_agreement");
//            last_query();
            if ($res['status'] == 4) {
                $result["data"]["declined_reason_det"] = $this->ServiceAgreement_model->get_declined_reason_of_nots($res['id'], 1);
            }
        }


        return $this->output->set_output(json_encode($result));
    }

    /*
     * its use for update service agreement status
     * 
     * return type json
     * return object status: true
     */

    function update_status_service_agreement() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;

            $validation_rules = [
                array('field' => 'service_agreement_id', 'label' => 'service agreement id', 'rules' => "required"),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required'),
            ];

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $res = $this->ServiceAgreement_model->update_status_service_agreement($data, $adminId);

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
     * Marks service agreement as archived
     */
    public function archive_service_agreement()
    {
        $reqData = request_handler('access_crm');
        $this->output->set_content_type('json');
        $result = $this->ServiceAgreement_model->archive_service_agreement($reqData->data->id);

        if ($result['status']) {
            $adminId = $reqData->adminId;
            $code = $result['code'];
            
            $this->load->library('UserName');
            $adminName = $this->username->getName('admin', $adminId);
            $this->loges->setTitle(sprintf("Successfully archived service agreement (id: %s) by %s", $code, $adminName));
            $this->loges->setSpecific_title(sprintf("Successfully archived service agreement (id: %s) by %s", $code, $adminName)); 
            $this->loges->setDescription(json_encode($reqData->data));
            $this->loges->setUserId($reqData->data->id);
            $this->loges->setCreatedBy($adminId);
            $this->loges->createLog();
        }

        return $this->output->set_output(json_encode($result));

    }

    /*
     * Get all active line items
     */
    function get_finance_active_line_item_listing() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData)) {
            $result = $this->ServiceAgreement_model->get_finance_active_line_item_listing($reqData);
            echo json_encode($result);
        }
    }

    /*
     * Get all active line items
     */
    function get_line_items_additional_funding_detail() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData)) {
            $result = $this->ServiceAgreement_model->get_line_items_additional_funding_detail($reqData->data);
            
            echo json_encode($result);
        }
    }
    /*
     * Save opportunity items
     * with opportunity id
     */
     function save_service_agreement_item() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        
        if (!empty($reqData->data->rows)) {
            $data = obj_to_arr($reqData->data->rows);
            $data['service_agreement_id'] = $reqData->data->service_agreement_id;
            $data['additional_rows'] = obj_to_arr($reqData->data->additional_rows);            
            $serviceAgreementItemId = $this->ServiceAgreement_model->save_service_agreement_item($data, $adminId, $reqData);

            if ($serviceAgreementItemId) {
                $this->load->library('UserName');
                $adminName = $this->username->getName('admin', $adminId);                
                $succes_msg = 'Service Agreement item saved successfully.';
                $this->loges->setTitle("Service Agreement item saved " . " by " . $adminName);  
                $this->loges->setDescription(json_encode($data));
                $this->loges->setUserId($adminId);
                $this->loges->createLog(); // create log

                $response = ['status' => true, 'msg' => $succes_msg];
            } else {
                $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            }
        }
        echo json_encode($response);
        exit();
    }

    /*
     * Validate service agreement line item
     */
    public function validate_item_data($data, $requestData)
    {
        $lineItems = json_decode($requestData);
        if (!empty($lineItems)) 
        {
            foreach ($lineItems as $val) {
                if ($val->selected != 1) continue;

                if($val->qty == ''){
                    $this->form_validation->set_message('validate_item_data', 'Quantity is required for Item - ' . $val->line_item_name);
                    return false;  
                }

                if($this->IsAmountRequired($val, $lineItems) && !($val->amount > 0)) {
                    $this->form_validation->set_message('validate_item_data', 'Amount is required for Item - ' . $val->line_item_name);
                    return false;  
                }              
            } 
        }     
    }

    /** 
     * Determines if an Service Agreement Line Item Value is required.
     * 
     * If the parent Category of this Line Item is present in the list,
     * and it has a value, a value is optional;
     * else, a value is required. 
     * @param array $lineItems
     * */
    public function IsAmountRequired($item, $lineItems) {
        $isCategory = empty($item->category_ref);

        if (!$isCategory) {

            foreach ($lineItems as $row) {
                if ($row->selected != 1) continue;
                
                if ($item->category_ref == $row->line_item_number)
                    return false;
            }
        
        }

        return true;
    }
    function save_sa_payment(){
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;
        if (!empty($reqData->data)) {
            // Check if court action is exist
            $getCourtAction = $this->basic_model->get_row('sa_payments', array("*"), ['service_agreement_id' => $data['service_agreement_id']]);
            $ca_action = !empty($getCourtAction) ? 'update' : 'create';            
            $sa_payment_id = $this->ServiceAgreement_model->save_sa_payment($data, $adminId, $ca_action);
                if ($sa_payment_id) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    $this->loges->createLog();
                    $return = ['status'=>true, 'msg'=>'Payment is updated successfully'];
                } else {
                    $return = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                }   
            
            } else {
                // If requested data is empty or null
                $return = ['status' => false, 'error' => 'Requested data is null']; 
            }
        return $this->output->set_output(json_encode($return));
    }

    /*
     * Save service agreement contract
     */
    function save_add_newdocusign(){
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;
        if (!empty($reqData->data)) {
            $sa_action = 'create';
            $dataObj = (array) $reqData->data;

            $validation_rules = [
                array('field' => 'to', 'label' => 'To', 'rules' => 'required'),
                array('field' => 'signed_by', 'label' => 'Signed By', 'rules' => 'required'),
                array('field' => 'subject', 'label' => 'Email Subject', 'rules' => 'required'),
                array('field' => 'email_content', 'label' => 'Email Content', 'rules' => 'required'),
                array('field' => 'document_type', 'label' => 'Document type', 'rules' => 'required'),
            ];
            if (!empty($data['opporunity_id'])) {
                $validation_rules[] = array('field' => 'account_id', 'label' => 'Account', 'rules' => 'required', 'errors' => [
                    'required' => "The Account Id is required"
                ]);
                $validation_rules[] = array('field' => 'account_type', 'label' => 'Account Type', 'rules' => 'required', 'errors' => [
                    'required' => "The Account Type is required"
                ]);
                $validation_rules[] = array('field' => 'service_agreement_id', 'label' => 'Service Agreement Id', 'rules' => "required", 'errors' => [
                    'required' => "The Service Agreement Id is required"
                ]);
            }

            $this->form_validation->set_data($dataObj);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $sa_newdocusign_id = $this->ServiceAgreement_model->save_add_newdocusign($data, $adminId, $sa_action);
                if ($sa_newdocusign_id) {
                    // generate contract 1 Consent / 2 _ Service Agreement
                    $dcomuent_type = intVal($data['document_type']);
                    switch($dcomuent_type) {
                        // 1 Consent
                        case 1:
                            if (!empty($data['lead_id'])) {
                                $this->generate_lead_consent($sa_newdocusign_id, $data['lead_id']);
                            } else {
                                $this->generate_service_agreement_consent($sa_newdocusign_id);
                            }
                            break;
                        // NDIS
                        case 2:
                            $this->generate_service_agreement_contract($sa_newdocusign_id);
                            break;
                        // Support Coordination
                        case 3:
                            $this->generate_service_agreement_sc_contract($sa_newdocusign_id);
                            break;
                        // Private Travel SA
                        case 4:
                            $this->generate_private_travel_agreement_contract($sa_newdocusign_id);
                            break;
                        default:
                            break;
                    }
                    
                    // create log
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    $this->loges->createLog();
                    $return = ['status'=>true, 'msg'=>'New Docu Sign is Added successfully'];
                } else {
                    $return = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                } 
            } else {
                $errors = $this->form_validation->error_array();
                $return = ['status' => false, 'error' => implode(', ', $errors)];
            }
              
        } else {
            // If requested data is empty or null
            $return = ['status' => false, 'error' => 'Requested data is null']; 
        }
        return $this->output->set_output(json_encode($return));
    }

    /*
     * Get contact list associated with opportunity
     * return - array
     */
    public function get_contacts_by_opportunity() {
        $reqData = request_handler('access_crm');
        $data = json_decode($reqData->data);
        $adminId = $reqData->adminId;
        $result = [];
        if (!empty($data) && isset($data->opportunity_id)) {
            $result = $this->ServiceAgreement_model->get_opportunity_contacts($data->opportunity_id);
            // If requested data is empty or null
            $return = ['status' => false, 'msg' => 'Contact searched successfully']; 
        } else {
            // If requested data is empty or null
            $return = ['status' => false, 'error' => 'Requested data is null']; 
        }
        return $this->output->set_output(json_encode($result));
    }

    /*
     * Console preview doucment of service agreement
     */
    public function preview_consent_service_agreement(){
        $id = $this->input->get('id', TRUE);
        $type = $this->input->get('type', TRUE);
        if ($type == 1) {
            echo $this->generate_service_agreement_consent($id);
        } else {
            // $this->load->controller('ServiceAgreementContract');
            echo $this->generate_private_travel_agreement_contract($id);    
        }
    }

    /*
     * Generate service agreement contract and envelop
     * @param {int} $service_agreement_attachment_id
     * return array
     */
    public function generate_service_agreement_contract($service_agreement_attachment_id) {
        $this->call_generate_docusign($service_agreement_attachment_id,'ndis_sa');
    }

    /*
     * Generate service agreement - support coordination contract and envelop
     * @param {int} $service_agreement_attachment_id
     * return array
     */
    public function generate_service_agreement_sc_contract($service_agreement_attachment_id) {            
            $this->call_generate_docusign($service_agreement_attachment_id,'support_coordination');        
    }
    
    /*
     * Generate service agreement contract and envelop
     * @param {int} $service_agreement_attachment_id
     * return array
     */
    public function generate_service_agreement_consent($service_agreement_attachment_id) {       
        $this->call_generate_docusign($service_agreement_attachment_id,'consent');                 
    }
    // call the async generate docusign
    function call_generate_docusign($service_agreement_attachment_id,$sa_name='',$lead_id=0) {
        $this->load->model(['ServiceAgreement_model']);            
        $url = base_url()."sales/ServiceAgreement/generate_docusign_envelope";            
        $param = array('service_agreement_attachment_id' => $service_agreement_attachment_id, 'sa_name'=>$sa_name, 'lead_id'=>$lead_id );
        
        $this->asynclibrary->do_in_background($url, $param);
        echo json_encode(['status' => true, 'msg' => "Contract generated successfully"]);           
        exit();
    }

    function generate_docusign_envelope() {
        $this->load->model(['ServiceAgreement_model']);
        $service_agreement_attachment_id = $_POST['service_agreement_attachment_id'];
        $lead_id = $_POST['lead_id'] ?? 0;
        $sa_name = $_POST['sa_name'];
        if($sa_name == 'consent'){
            $resSendEmail = $this->ServiceAgreement_model->consent_service_aggreement_docusign($service_agreement_attachment_id);
        }else if($sa_name == 'ndis_sa'){
            $resSendEmail = $this->ServiceAgreement_model->ndis_service_aggreement_docusign($service_agreement_attachment_id);
        }else if($sa_name == 'support_coordination'){
            $resSendEmail = $this->ServiceAgreement_model->support_coordination_service_aggreement_docusign($service_agreement_attachment_id);
        }else if($sa_name == 'private_travel'){
            $resSendEmail = $this->ServiceAgreement_model->private_travel_service_aggreement_docusign($service_agreement_attachment_id);
        }else if($sa_name == 'lead_consent'){
            $resSendEmail = $this->ServiceAgreement_model->lead_consent_service_aggreement_docusign($service_agreement_attachment_id,$lead_id);
        }
         
        exit();
    }

    /*
     * Callback from docusign once document signed
     */
    public function callback_service_agreement_docusign()
    {
        // get input data
        $data = file_get_contents('php://input');
        file_put_contents(FCPATH . 'serviceagreement_response.txt', $data);
        $xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_PARSEHUGE);
        // check values are exist
        if (!$xml) {
            echo "null content response";
            exit();
        }
        if (empty($xml->EnvelopeStatus->EnvelopeID)) {
            echo "Envelope Id not exist";
            exit();
        }
        if (empty($xml->EnvelopeStatus->TimeGenerated)) {
            echo "Generated time not exist";
            exit();
        }

        $envelope_id = (string) $xml->EnvelopeStatus->EnvelopeID;
        $time_generated = (string) $xml->EnvelopeStatus->TimeGenerated;
        // create service agreement folder if not exist
        $envelope_dir = FCPATH . SERVICE_AGREEMENT_CONTRACT_PATH;
        if (!is_dir($envelope_dir)) {
            mkdir($envelope_dir, 0777);
        }

        // get status of service_agreement_attachment
        $column = ["id", "type", "envelope_id", "signed_status", "service_agreement_id", "signed_by"];
        $where = $where = ["envelope_id" => $envelope_id];
        $response = $this->basic_model->get_row("service_agreement_attachment", $column, $where);
        // exit if envelop id not found in service_agreement_attachment table
        if (empty($response->id)) {
            echo "Service Agreement not exist";
            exit();
        }
        // get values
        $attachmentId = $response->id;
        $attachmentType = $response->type;
        $service_agreement_id = $response->service_agreement_id;
        $type = $response->type;
        $signed_by = $response->signed_by;
        if ($attachmentType == 1) {
            $attachmentType = 'Consent';
        } else {
            $attachmentType = 'Service Agreement';
        }

        // check & create folder if not exist witn attachment_id
        $envelope_dir = $envelope_dir . $attachmentId. '/';
        if (!is_dir($envelope_dir)) {
            mkdir($envelope_dir, 0777);
        }
        
        $filename = $envelope_dir . "docuSignresponse" . str_replace(':', '_', $time_generated) . ".xml";
        $ok = file_put_contents($filename, $data);
        if ($ok === false) {
            // Here to handel if file not generated
            echo "Xml file content not available";
            exit();
        }        

        $data_test = [
            'envelope_id' => $envelope_id,
            'response' => $response,
            'EnvelopeStatus' => (string) $xml->EnvelopeStatus->Status
        ];
        file_put_contents(FCPATH . 'serviceagrement_response_qury.txt', print_r($data_test, true), FILE_APPEND | LOCK_EX);

        // check webhook is not called twice once signed
        if (!empty($response) && (int) $response->signed_status == 0) {
            $filename = '';
            $signed_file = '';
            if ((string) $xml->EnvelopeStatus->Status === "Completed") {
                // Loop through the DocumentPDFs element, storing each document.
                $filename_save = $attachmentType. ' Contract.pdf';
                $signed_file = $filename_save;
                // To create folder with applicant_id
                // $envelope_dir = $envelope_dir.$applicantId.'/';
                if (!is_dir($envelope_dir)) {
                    mkdir($envelope_dir, 0777, true);
                }
                foreach ($xml->DocumentPDFs->DocumentPDF as $pdf) {
                    //$filename = $envelope_id.'_'.(string)$pdf->DocumentID.'.pdf';
                    $filename = (string) $pdf->Name . '.pdf';
                    $full_filename = $envelope_dir . $filename;
                    file_put_contents($full_filename, base64_decode((string) $pdf->PDFBytes));
                    if (isset($pdf->DocumentID) && (string) $pdf->DocumentID > 0 && strtolower((string) $pdf->PDFBytes) == strtolower('CONTENT')) {
                        $filename_save = $filename;
                    }
                }

                /* S3 Upload
                 * load amazon s3 library
                 */
                $this->load->library('AmazonS3');
                $this->load->library('S3Loges');
                $isUser = 'Service Agreement';
                $module_id = 3;
                $s3_folder = S3_SERVICE_AGREEMENT_CONTRACT_DOCUSIGN_PATH;
                $tmp_name = $envelope_dir . $signed_file;
                $directory_name = $attachmentId;
                $file_path = $s3_folder . $attachmentId . '/' . $filename_save;

                $path_parts = pathinfo($tmp_name);
                $filename_wo_ext =  $path_parts['filename'];
                $filename_ext =  $path_parts['extension'];
                
                $folder_key = $s3_folder . $directory_name .'/'. $filename_wo_ext .'.'. $filename_ext;

                /**
                 * set dynamic values
                 * $tmp_name should be - Uploaded file tmp storage path
                 * $folder_key shoulb - Saving path with file name into s3 bucket
                 *      - you can add a folder like `folder/folder/filename.ext`
                 */
                $this->amazons3->setSourceFile($tmp_name);
                $this->amazons3->setFolderKey($folder_key);

                $this->s3loges->setModuleId($module_id ?? 0);
                $this->s3loges->setCreatedAt(DATE_TIME);
                $this->s3loges->setTitle('File Transfer Initiated for Service Agreement Document against '.$isUser.' '.$directory_name);
                $this->s3loges->setCreatedBy(0);
                $this->s3loges->setLogType('init');
                $this->s3loges->createS3Log();

                // Upload file if testCase true use mock object
                $amazons3_updload = $this->amazons3->uploadDocument();

                if ($amazons3_updload['status'] == 200) {
                    // success
                    $aws_uploaded_flag = 1;
                    $aws_object_uri = '';
                    $aws_file_version_id = '';
                    if (isset($amazons3_updload['data']) == true && empty($amazons3_updload['data']) == false) {
                        $data = $amazons3_updload['data'];
                        $aws_object_uri = $data['ObjectURL'] ?? '';

                        if ($aws_object_uri == '' && isset($data['@metadata']) == true && isset($data['@metadata']['effectiveUri']) == true) {
                            $aws_object_uri = $data['@metadata']['effectiveUri'] ?? '';
                        }

                        if ($aws_file_version_id == '' && isset($data['VersionId']) == TRUE) {
                          $aws_file_version_id = $data['VersionId'] ?? '';
                        }
                    }
                    $aws_response = $amazons3_updload['data'];

                    $this->s3loges->setTitle($isUser.' '.$directory_name.' - '.$filename_save . ' - S3 File transfer Completed');
                    $this->s3loges->setLogType('success');
                    $this->s3loges->setDescription(json_encode($aws_response));
                    $this->s3loges->createS3Log();

                } else {
                    // failed
                    $this->s3loges->setTitle($isUser.' '.$directory_name.' - '.$filename_save . ' - S3 File transfer Completed with error!');
                    $this->s3loges->setLogType('failure');
                    $this->s3loges->setDescription(json_encode($aws_response));
                    $this->s3loges->createS3Log();
                    $aws_object_uri = '';
                    $aws_response = $amazons3_updload['data'];
                    $aws_uploaded_flag = 0;
                }

                // update contract 
                // marked signed status = 1 , signed_date = current date time
                $update_data = [];
                $update_data['file_path'] = $file_path;
                $update_data['signed_file'] = $filename_save;
                $update_data['signed_status'] = 1;
                $update_data['signed_date'] = DATE_TIME;
                $update_data['aws_object_uri'] = $aws_object_uri;
                $update_data['aws_response'] = json_encode($aws_response);
                $update_data['aws_uploaded_flag'] = $aws_uploaded_flag;
                $update_data['aws_file_version_id'] = $aws_file_version_id;
                $where_update = ['envelope_id' => $envelope_id, 'id' => $attachmentId];
                $this->basic_model->update_records('service_agreement_attachment', $update_data, $where_update);
                file_put_contents(FCPATH . 'serviceagrement_response_qury2.txt', print_r($update_data, true));

                // delete file in App server if Appser upload is not enabled
                if(getenv('IS_APPSERVER_UPLOAD') != 'yes' && is_dir($envelope_dir) == true) {
                    if (readfile($tmp_name)) {
                        unlink($tmp_name);
                    }
                }

                // get service_agreement
                $column = ["id", "owner"];
                $where = $where = ["id" => $service_agreement_id];
                $response = $this->basic_model->get_row("service_agreement", $column, $where);
                // exit if id not found in service_agreement table
                if (empty($response->id)) {
                    echo "Service Agreement not exist";
                    exit();
                }
                $sent_owner = $response->owner;

                // get signed_by contact person
                $column = ["CONCAT(firstname,' ',lastname) AS name", "id"];
                $where  = ["id" => $signed_by];
                $response = $this->basic_model->get_row("person", $column, $where);
                if (isset($response->name)) {
                    $owner_name = $response->name;
                } else {
                    $owner_name = '';
                }

                // insert notification data 
                $insert_data = [];
                $insert_data['userId'] = $sent_owner;
                $insert_data['title'] = $attachmentType." Contract";
                $insert_data['shortdescription'] = 'Signed By '.$owner_name;
                $insert_data['status'] = 0;
                $insert_data['sender_type'] = 2;
                $insert_data['specific_admin_user'] = $sent_owner;
                $insert_data['created'] = DATE_TIME;
                $insert_data['entity_type'] = 6;
                $insert_data['entity_id'] = $attachmentId;
                $this->basic_model->insert_records('notification', $insert_data, false);

                # Async API used to update the shift service agreement contract warning .
                $this->load->library('Asynclibrary');
                $url = base_url()."schedule/NdisErrorFix/update_shift_ndis_warning";
                $param = array('service_agreement_id' => $service_agreement_id, 'update_service_docusign' => true);
                $param['requestData'] = $param;
                $this->asynclibrary->do_in_background($url, $param);

            }
        } else {
            file_put_contents(FCPATH . 'serviceagrement_response_qury2.txt', 'envelope id not found in service agreement contratct');
            echo "envelope id not found in service agreement contratct";
            exit();
        }
    }

    public function can_create_docusign_agreement() {        
        $reqData = request_handler('access_crm');

        $serviceAgreementId =$reqData->data->id;
        $serviceAgreementTempTypeId =$reqData->data->template_type;
        
        $missingFields = [];

        $sa = $this->db->select('*')
            ->from('tbl_service_agreement as sa')            
            ->where('sa.id =', $serviceAgreementId)
            ->get()->row();
            
        $SAPayments = $this->ServiceAgreement_model->get_sa_contract_payment($serviceAgreementId);

        if (isset($SAPayments) == true && isset($SAPayments['managed_type']) == true && $SAPayments['managed_type'] != 3 && intval($serviceAgreementTempTypeId) == 3) {
            $missingFields[] = 10;
        }
        
        if (!empty($sa)) {
            $acc = new stdClass();

            if ($sa->account_type = 1) { // person
                $acc = $this->db->select('*')
                        ->from('tbl_person')
                        ->where('tbl_person.id =', $sa->account)
                        ->where('tbl_person.archive = 0')
                        ->get()->row();                        
                                                
            } else if ($sa->account_type = 2) { // org - todo
                // $acc = $this->db->select('*')
                //         ->from('tbl_person')
                //         ->join('tbl_organisation as org', 'org.id = ')
                //         ->where('tbl_person.id =', $sa->account)
                //         ->get()->row();            
            }

            if (empty($acc)) $missingFields[] = 0;
            else {
                if (empty($acc) || empty(trim($acc->ndis_number))) $missingFields[] = 1;
                if (empty($acc) || empty(trim($acc->date_of_birth)) || !validateDateWithFormat($acc->date_of_birth)) $missingFields[] = 2;
            }

            $goals = $this->db->select('COUNT(*) as count')
                ->from('tbl_goals_master as sag')
                ->where('sag.service_agreement_id', $serviceAgreementId)
                ->where('nullif(ltrim(rtrim(sag.goal)),\'\') != ', 'NULL')
                ->get()->row();
                
            $items = $this->db->select('COUNT(*) as count')
                ->from('tbl_service_agreement_items as sai')
                ->where('sai.service_agreement_id = ' . $serviceAgreementId)
                ->where('sai.archive', 0)
                ->get()->row();

            $paymentInfo = $this->db->select('COUNT(*) as count')
                ->from('tbl_sa_payments as sap')
                ->where('sap.service_agreement_id', $serviceAgreementId)
                ->get()->row();
            
            if ($goals->count == 0) $missingFields[] = 9;
            if ($items->count == 0) $missingFields[] = 7;
            if ($paymentInfo->count == 0) $missingFields[] = 8;

            // recipient - email
            $recipient = $this->db->select('email')
                        ->from('tbl_person')
                        ->join('tbl_person_email', 'tbl_person_email.person_id = tbl_person.id AND tbl_person_email.primary_email = 1 AND tbl_person_email.archive = 0')
                        ->where('tbl_person.id =', $reqData->data->recipientId)
                        ->where('tbl_person.archive = 0')
                        ->get()->row();

            $signer_addr = $this->db->select('street, suburb, postcode, state')
                        ->from('tbl_person')
                        ->join('tbl_person_address', 'tbl_person_address.person_id = tbl_person.id AND tbl_person_address.primary_address = 1 AND tbl_person_address.archive = 0')
                        ->where('tbl_person.id =', $reqData->data->signerId)
                        ->where('tbl_person.archive = 0')
                        ->get()->row();

            $signer_ph = $this->db->select('phone')
                        ->from('tbl_person')                        
                        ->join('tbl_person_phone', 'tbl_person_phone.person_id = tbl_person.id AND tbl_person_phone.primary_phone = 1 AND tbl_person_phone.archive = 0')
                        ->where('tbl_person.id =', $reqData->data->signerId)
                        ->where('tbl_person.archive = 0')
                        ->get()->row();

            if (empty($recipient) || empty(trim($recipient->email))) $missingFields[] = 4;
            if (empty($signer_ph) || empty(trim($signer_ph->phone))) $missingFields[] = 5;
            if (empty($signer_addr) || empty(trim($signer_addr->street)) || empty(trim($signer_addr->suburb)) || empty(trim($signer_addr->postcode)) || empty(trim($signer_addr->state))) $missingFields[] = 6;
        }

        echo json_encode([ "status" => true, 'info' => $missingFields ]);
    }

    /*
     * Get list of account by search str
     * return json
     */
    public function get_account_list_names() {
        $reqData = request_handler('access_crm');
        $reqData->data = json_decode($reqData->data);
        $post_data = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->ServiceAgreement_model->get_account_list_names($post_data);
        echo json_encode($rows);
    }

    /*
     * Get list of contact by search str
     * return json 
     */
    public function get_account_contacts() {
        $reqData = request_handler('access_crm');
        $reqData->data = $reqData->data;
        // $post_data = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->ServiceAgreement_model->get_account_contacts($reqData->data);
        echo json_encode($rows);
    }
    /**
     * Get the field history for service agreement item
     * @param void
     * @return string
     */
    function get_field_history() {
        $reqData = request_handler('access_crm');
        if (empty($reqData)) return;

        return $this->ServiceAgreement_model->get_field_history($reqData->data);
    }

    /**
     * Get person list with account if it's a person
     * @param void
     * @return string
     */
    public function get_opp_contacts_with_ac() {
        $reqData = $reqData1 = request_handler('access_crm');
        $getdata = json_decode($reqData->data);
        $rows = array();
        $result = $this->ServiceAgreement_model->get_oppunty_contacts_with_account($getdata->opporunity_id);
        echo json_encode($result);
    }

    /**
     * Fetch all service agreements by service agreeemnt type
     */
    public function get_service_agreement_contracts()
    {
        $reqData = request_handler('access_crm');    
        $data = $reqData->data;    
        $this->output->set_content_type('json');
        $result = [];
        $rows = $this->ServiceAgreement_model->get_service_agreement_contracts($data);            
        if (!empty($rows)) {
            foreach($rows as $row) {
                $obj = new stdClass();
                $obj->label = $row->contract_id."-".date('d/m/Y', strtotime($row->signed_date));
                $obj->value = $row->id;
                $result[] = $obj;
            }
        }
        return $this->output->set_output(json_encode($result));
    }

    /**
     * Fetch all service agreements by service agreeemnt type
     */
    public function get_service_contract_funding()
    {
        $reqData = request_handler('access_crm');    
        $data = $reqData->data;
        $amount = $this->ServiceAgreement_model->get_service_contract_funding($data);            
        return $this->output->set_output(json_encode(['amount' => $amount]));
    }
    
    /*
     * Generate lead contract and envelop
     * @param {int} $service_agreement_attachment_id
     * return array
     */
    public function generate_lead_consent($service_agreement_attachment_id, $lead_id = 0) {
        // call docusign api
        $this->call_generate_docusign($service_agreement_attachment_id,'lead_consent',$lead_id );        
    }
}
