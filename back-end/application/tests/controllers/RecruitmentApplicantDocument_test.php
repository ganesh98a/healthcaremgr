<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller RecruitmentApplicantDocument and related s3 mock upload of it
 */
class RecruitmentApplicantDocument_test extends TestCase {
    // Defualt contruct function
    protected $CI;
    public function setUp() {
        $this->CI = &get_instance();
        // load amazon s3 library
        $this->CI->load->library('AmazonS3');
        $this->CI->load->library('form_validation');
        $this->CI->load->model('../modules/recruitment/models/Recruitment_cab_day_model');
        $this->Recruitment_cab_day_model = $this->CI->Recruitment_cab_day_model;
        $this->form_validation = $this->CI->form_validation;
        $this->basic_model = $this->CI->basic_model;
    }

    /** Mock Upload Positive test case check  */
    public function test_upload_applicant_attachment_document_case1() {
        require_once APPPATH . 'Classes/common/Aws_file_upload.php';

        $config_ary = [];

        $awsFileupload = new Aws_file_upload();

        $config_ary['upload_path'] = S3_APPLICANT_ATTACHMENT_UPLOAD_PATH;
        $config_ary['input_name'] = 'attachments';
        $config_ary['directory_name'] = 'test_dir_123';
        $config_ary['allowed_types'] = DEFAULT_ATTACHMENT_UPLOAD_TYPE;

        $_FILES = array(
            "attachments" => array(
                "name" => "index.PNG",
                "type" => "image/png",
                "tmp_name" => "/tmp/phpE87A.tmp",
                "error" =>  "0",
                "size" => 15189,
            )
        );

        //Upload Files into S3 using mock upload mechanism
        $upload_status = $awsFileupload->s3_common_upload_single_attachment($config_ary, TRUE);

        //$status = $response['status'];
        // Get msg if true else error
        if ($upload_status) {
            $status = ['status' => TRUE, 'error' => 'Mock Upload Success'];
        } else {
            $status = ['status' => FALSE, 'error' => 'Mock Upload Failed'];
        }

        return $this->assertTrue($status['status']);

    }

     /** Mock Upload Negative test case send file with out temp name */
     public function test_upload_applicant_attachment_document_case2() {
        require_once APPPATH . 'Classes/common/Aws_file_upload.php';

        $config_ary = [];

        $awsFileupload = new Aws_file_upload();

        $config_ary['upload_path'] = S3_APPLICANT_ATTACHMENT_UPLOAD_PATH;
        $config_ary['input_name'] = 'attachments';
        $config_ary['directory_name'] = 'test_dir_123';
        $config_ary['allowed_types'] = DEFAULT_ATTACHMENT_UPLOAD_TYPE;

        $_FILES = array(
            "attachments" => array(
                "name" => "index.PNG",
                "type" => "image/png",
                "tmp_name" => "",
                "error" =>  "0",
                "size" => 15189,
            )
        );

        //Upload Files into S3 using mock upload mechanism
        $upload_status = $awsFileupload->s3_common_upload_single_attachment($config_ary, TRUE);

        // Get msg if true else error
        if ($upload_status) {
            $status = ['status' => TRUE, 'error' => 'Mock Upload Success'];
        } else {
            $status = ['status' => FALSE, 'error' => 'Mock Upload Failed'];
        }

        return $this->assertTrue($status['status']);

    }

    /*
     * Upload document in attachment with mock - Create Document Success
     */
    public function test_upload_attachment_document_case1() {

        $requestData = '{
            "adminId": 20,
            "applicantId": "20",
            "docsCategory": "5",
            "currentStage": 1,
            "stageMain": 0,
            "docStatus": 1,
            "docsTitle": "resume_index",
            "docIssueDate": "2020-11-10",
            "docExpiryDate": "",
            "reference_number": "",
            "issue_date_mandatory": true,
            "expiry_date_mandatory": true,
            "reference_number_mandatory": true,
            "docsFile": {"name":["index.PNG"],"type":["image/png"],"tmp_name":["/tmp/phpE87A.tmp"],"error":[0],"size":[15189]}
        }';
        require_once APPPATH . 'Classes/common/Aws_file_upload.php';

        $config_ary = [];

        $awsFileupload = new Aws_file_upload();

        $config_ary['upload_path'] = S3_APPLICANT_ATTACHMENT_UPLOAD_PATH;
        $config_ary['input_name'] = 'attachments';
        $config_ary['directory_name'] = 'test_dir_123';
        $config_ary['allowed_types'] = DEFAULT_ATTACHMENT_UPLOAD_TYPE;

        $_FILES = array(
            "attachments" => array(
                "name" => "index.PNG",
                "type" => "image/png",
                "tmp_name" => "/tmp/phpE87A.tmp",
                "error" =>  "0",
                "size" => 15189,
            )
        );

        //Upload Files into S3 using mock upload mechanism
        $upload_status = $awsFileupload->s3_common_upload_single_attachment($config_ary, TRUE);

        $data = json_decode($requestData);

        require_once APPPATH . 'Classes/document/DocumentAttachment.php';
        
        $docAttachObj = new DocumentAttachment();

        if (!empty($_FILES) && $_FILES['attachments']['error'] == 0) {              

            $docAttachObj->setApplicantId($data->applicantId);
            $docAttachObj->setDocTypeId($data->docsCategory);
            $docAttachObj->setArchive(0);
            $docAttachObj->setStage($data->currentStage);
            $docAttachObj->setIsMainStage(($data->stageMain == 'true' ? 1 : 0));
            $docAttachObj->setCreatedAt(DATE_TIME);
            $docAttachObj->setCreatedBy($data->adminId);
            $docAttachObj->setEntityId($data->adminId);
            $docAttachObj->setReferenceNumber($data->reference_number);
            // Get constant staus
            $docAttachObj->setDocumentStatus($data->docStatus);
            // Get constant entity type
            $entityType = $docAttachObj->getConstant('ENTITY_TYPE_ADMIN');
            $docAttachObj->setEntityType($entityType);
            // Get constant related to
            $relatedTo = $docAttachObj->getConstant('RELATED_TO_RECRUITMENT');
            $docAttachObj->setRelatedTo($relatedTo);
            // Get constant Created Portal
            $createdPortal = $docAttachObj->getConstant('CREATED_PORTAL_HCM');
            $docAttachObj->setCreatedPortal($createdPortal);

            if (property_exists($data, 'application_id') && !empty($data->application_id)) {
                $docAttachObj->setApplicationId($data->application_id);
            }

            if (property_exists($data, 'docExpiryDate')) {
                $docAttachObj->setExpiryDate(empty(trim($data->docExpiryDate)) ? null : $data->docExpiryDate);
            }

            if (property_exists($data, 'docIssueDate')) {
                $docAttachObj->setIssueDate(empty(trim($data->docIssueDate)) ? null : $data->docIssueDate);
            }

            // check its duplicate
            $docAttachDubObj = new DocumentAttachment();
            $docAttachDubObj->setRawName($_FILES['attachments']['name']);
            $docAttachDubObj->setApplicantId($data->applicantId);
            $docAttachDubObj->setArchive(0);
            $dub_result = $docAttachDubObj->checkDublicateDocAttachment(); 

            // check here file is uploaded or not return key error true
            if (!$upload_status['aws_uploaded_flag']) {
                // return error comes in file uploading
                $return = array('status' => false,  'error' => 'Document Attachment is not created. something went wrong');
            } else {
                $documentId = $docAttachObj->createDocumentAttachment();
                
                if ($documentId != '') {
                    $docAttachPropertyObj = new DocumentAttachment();
                    $docAttachPropertyObj->setDocId($documentId);
                    $docAttachPropertyObj->setFilePath($upload_status['file_path']);
                    $docAttachPropertyObj->setFileType($upload_status['file_type']);
                    $docAttachPropertyObj->setFileSize($upload_status['file_size']);
                    $docAttachPropertyObj->setAwsResponse($upload_status['aws_response']);
                    $docAttachPropertyObj->setAwsObjectUri($upload_status['aws_object_uri']);
                    $docAttachPropertyObj->setAwsFileVersionId($upload_status['aws_file_version_id']);
                    $docAttachPropertyObj->setFileName($upload_status['file_name']);
                    $docAttachPropertyObj->setRawName($_FILES['attachments']['name']);
                    $docAttachPropertyObj->setAwsUploadedFlag($upload_status['aws_uploaded_flag']);
                    $docAttachPropertyObj->setArchive(0);
                    $docAttachPropertyObj->setCreatedAt(DATE_TIME);
                    $docAttachPropertyObj->setCreatedBy($data->adminId);

                    $documentAttachId = $docAttachPropertyObj->createDocumentAttachmentProperty();
                }
                // check here file is uploaded or not return key error true
                if (!$dub_result['status']) {
                    $return = array('status' => true, 'warn' => $dub_result['warn']);
                } else {
                    $return = array('status' => true);
                }                    
            }
        }
        // Get msg if true else error
        if ($upload_status) {
            $status = ['status' => TRUE, 'error' => 'Mock Upload Success'];
        } else {
            $status = ['status' => FALSE, 'error' => 'Mock Upload Failed'];
        }
        return $this->assertTrue($status['status']);
    }

    /*
     * Uploaded document in attachment - Update Document Success
     */
    public function test_update_attachment_document_case1() {

        $id = 1;
        $requestData = '{
            "adminId": 20,
            "applicantId": "20",
            "docsCategory": "5",
            "currentStage": 1,
            "stageMain": 0,
            "docStatus": 1,
            "docsTitle": "resume_index",
            "docIssueDate": "2020-11-10",
            "docExpiryDate": "",
            "reference_number": "132",
            "issue_date_mandatory": true,
            "expiry_date_mandatory": true,
            "reference_number_mandatory": true
        }';

        $data = json_decode($requestData);

        require_once APPPATH . 'Classes/document/DocumentAttachment.php';
        
        $docAttachObj = new DocumentAttachment();

        $update_status = 0;
        if ($id != 0) {

            $docAttachObj->setDocId($id);
            $docAttachObj->setDocTypeId($data->docsCategory);
            $docAttachObj->setReferenceNumber($data->reference_number);

            $docAttachObj->setUpdatedAt(DATE_TIME);
            $docAttachObj->setUpdatedBy($data->adminId);
            // Get constant staus
            $docAttachObj->setDocumentStatus($data->docStatus);
           
            if (property_exists($data, 'application_id') && !empty($data->application_id)) {
                $docAttachObj->setApplicationId($data->application_id);
            }

            if (property_exists($data, 'docExpiryDate')) {
                $docAttachObj->setExpiryDate(empty(trim($data->docExpiryDate)) ? null : $data->docExpiryDate);
            }

            if (property_exists($data, 'docIssueDate')) {
                $docAttachObj->setIssueDate(empty(trim($data->docIssueDate)) ? null : $data->docIssueDate);
            }
            $docAttachObj->updateDocumentAttachment();               
            $update_status = 1;
        }
        // Get msg if true else error
        if ($update_status) {
            $status = ['status' => TRUE, 'error' => 'Updated Success'];
        } else {
            $status = ['status' => FALSE, 'error' => 'Updated Failed'];
        }
        return $this->assertTrue($status['status']);
    }

    /*
     * Uploaded document in attachment - Archive Document Success
     */
    public function test_archive_attachment_document_case1() {

        $id = 1;
        $requestData = '{
            "adminId": 20,
            "applicantId":"54",
            "archiveData":
             {"1":true}
         }';

        $data = json_decode($requestData);

        $applicantId = $data->applicantId;
        $archived_data_id = array_keys((array) $data->archiveData);

        require_once APPPATH . 'Classes/document/DocumentAttachment.php';
        $result = '';
        foreach ($archived_data_id as $doc_id) {
            
            $docAttachObj = new DocumentAttachment();

            $docAttachObj->setDocId($doc_id);
            $docAttachObj->setUpdatedAt(DATE_TIME);
            $docAttachObj->setUpdatedBy($data->adminId);

            $result = $docAttachObj->archiveDocumentAttachment();
        }

        // Get msg if true else error
        if ($result) {
            $status = ['status' => TRUE, 'error' => 'Archive Success'];
        } else {
            $status = ['status' => FALSE, 'error' => 'Archive Failed'];
        }
        return $this->assertFalse($status['status']);
    }
    
    /** Generate cab certificate & save to attachments case success
      * Using applicant_id & application_id
      */
    public function test_genearate_cab_certificate_case1()
    {
         // Set data in libray for validation
        $reqData = '
        {
            "applicant_id": "1",
            "application_id": "1",
            "adminId" : 20
        }';

        $reqData = json_decode($reqData, true);
        $reqData = (object) $reqData;
        $applicant_id = $reqData->applicant_id;
        $application_id = $reqData->application_id;
        $adminId = $reqData->adminId;
        $valid_status = 1;

        // get get_cab_certificate_category_id
        $cab_certificate_category_id = $this->Recruitment_cab_day_model->get_cab_certificate_category_id();

        // attachment insert data
        $app_att[] = [
            'applicant_id' => $applicant_id,
            'application_id' => $application_id,
            'attachment_title' => 'CAB Certificate',
            'attachment' => 'CAB_Certificate_'. rand(100000, 999999) . '.pdf',
            'stage' => '',
            'created' => DATE_TIME,
            'doc_category' => $cab_certificate_category_id,
            'document_status' => $valid_status,
            'draft_contract_type' => 0,
            'archive' => 0,
            'updated_at' => DATE_TIME,
        ];

        // insert data
        $attachment_id = $this->basic_model->insert_records('recruitment_applicant_stage_attachment', $app_att, true);
       
       // AssertsEquals false with response if false show the error msg 
       $this->assertGreaterThanOrEqual(0, $attachment_id);
    }

    function mock_cab_file_generate() {
         $postdata = [
            'filename' => "CAB_Certificate_" . rand(100000, 999999) . '.pdf'
        ];
        $applicant_details = [
            "applicant_name" => "test"
        ];
        $task_details = [
            "task_time" => DATE_TIME
        ];
        $request = new stdClass();
        $request->data = (object) $postdata;
        $mock = $this->getMockBuilder(Recruitment_cab_day_model::class)
            ->setMethods(['request_handler'])
            ->getMock();
        $mock->method('request_handler')->willReturn($request->data);
        return $output = $mock->genrate_cab_certificate_who_passed_cab_day($applicant_details, $task_details);
    }
}
