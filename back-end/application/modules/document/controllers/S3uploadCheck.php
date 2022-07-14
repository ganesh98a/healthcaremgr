<?php
/** Files holds the document type related operation */
defined('BASEPATH') OR exit('No direct script access allowed');

//class Master extends MX_Controller
class S3uploadCheck extends MX_Controller {
    public function __construct() {
        parent::__construct();
    }

    public function S3fileuploadcheck() {

        extract($_REQUEST);

        if(!isset($token) || $token != 'd9%4Mrxhx8nRDnWN$Hpn7A') {
            echo "Token Invalid!";
            return;
        }
        else if(!$_FILES) {
            echo "Please Upload atleast one File";
            return;
        }

        require_once APPPATH . 'Classes/common/Aws_file_upload.php';

        $applicantId = isset($applicant_id) ? $applicant_id .'/' : NULL;
        $applicationId = isset($application_id) ? $application_id : NULL;

        $awsFileupload = new Aws_file_upload();

        $upload_type = ($multipart == 'yes') ? 'multipart/' : 'putojbect/';

        $config['file_name'] = $_FILES['docsFile']['name'];
        $config['upload_path'] = "test-upload/" . $upload_type. S3_APPLICANT_ATTACHMENT_UPLOAD_PATH;
        $config['input_name'] = 'docsFile';
        $config['directory_name'] = $applicantId . $applicationId;
        $config['allowed_types'] = DEFAULT_ATTACHMENT_UPLOAD_TYPE; //'jpg|jpeg|png|xlx|xls|doc|docx|pdf|pages';
        $config['max_size'] = DEFAULT_MAX_UPLOAD_SIZE;

        // upload attachment to S3
        $s3documentAttachment = $awsFileupload->s3_common_upload_single_attachment_api($config, FALSE, $multipart);

        if (isset($s3documentAttachment) && !$s3documentAttachment['aws_uploaded_flag']) {
            echo json_encode(array('status' => FALSE, 'error' => 'Document Attachment is not created. something went wrong'));
            exit();
        }

        echo json_encode(array('status' => TRUE, 'message' => $s3documentAttachment));

    }
}