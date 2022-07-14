<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper class for Send the data to S3 Upload function
 */
class Aws_file_upload{

    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->library('S3Loges');
    }

    /**
     * Functions has the following features
     * 1. To form the relavent array of data and then call
     * 2. Call the S3 upload functions
     * 3. Return the response from S3
     *
     * @param $config_ary {array} required data for s3 upload
     * @param $testCase {bool} flag for testcase execution
     *
     * @see setSourceFile()
     * @see setFolderKey()
     * @see testUploadDocumentMock()
     * @see uploadDocument()
     *
     * @return $attachments {array} S3 Response with custimized data
     */
    public function s3_common_upload_single_attachment($config_ary, $testCase) {
        if(!$config_ary || empty($_FILES)){
            return;
        }

        $aws_response = $attachments = [];

        if (!empty($_FILES)) {

            $root_folder = $config_ary['upload_path'];
            $input_name = $config_ary['input_name'];
            $directory_name = $config_ary['directory_name'] ? $config_ary['directory_name'] .'/' : NULL;
            $files = $_FILES;
            $moduleid = array_key_exists('module_id', $config_ary) ? $config_ary['module_id'] : 0;
            $title =  array_key_exists('title', $config_ary) ? $config_ary['title'] :
                'S3 File transfer intitated!';
            $created_by = array_key_exists('created_by', $config_ary) ? $config_ary['created_by'] : NULL;

            // load amazon s3 library
            $this->CI->load->library('AmazonS3');

            $file_name = $files[$input_name]['name'];
            $file_type = $files[$input_name]['type'];
            $tmp_name = $files[$input_name]['tmp_name'];
            $file_size = $files[$input_name]['size'];
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

            $folder_key = $root_folder . $directory_name . $file_name;

            /**
             * set dynamic values
             * $tmp_name should be - Uploaded file tmp storage path
             * $folder_key should be - Saving path with file name into s3 bucket
             *      - you can add a folder like `folder/folder/filename.ext`
             */
            $this->CI->amazons3->setSourceFile($tmp_name);
            $this->CI->amazons3->setFolderKey($folder_key);

            $obj = new stdClass;
            $obj->file_name = $file_name;
            $obj->file_type = $file_type;
            $obj->file_size = $file_size;
            $obj->s3_folder_key = $folder_key;

            $this->CI->s3loges->setModuleId($config_ary['module_id'] ?? 0);
            $this->CI->s3loges->setCreatedAt(DATE_TIME);
            $this->CI->s3loges->setCreatedBy($created_by);

            // Upload file if testCase true use mock object
            if ($testCase == true) {
                $amazons3_updload = $this->CI->amazons3->testUploadDocumentMock();
            } else {

                $this->CI->s3loges->setModuleId($moduleid);
                $this->CI->s3loges->setTitle($title);
                $this->CI->s3loges->setLogType('init');
                $this->CI->s3loges->setDescription(json_encode($obj));
                $this->CI->s3loges->createS3Log();

                $amazons3_updload = $this->CI->amazons3->uploadDocument();
            }

            if ($amazons3_updload['status'] == 200) {
                // success
                $aws_uploaded_flag = 1;
                $aws_object_uri = '';
                $aws_file_version_id = '';
                if (isset($amazons3_updload['data']) == TRUE && empty($amazons3_updload['data']) == FALSE) {
                    $data = $amazons3_updload['data'];
                    $aws_object_uri = $data['ObjectURL'] ?? '';

                    if ($aws_file_version_id == '' && isset($data['VersionId']) == TRUE) {
                        $aws_file_version_id = $data['VersionId'] ?? '';
                    }

                    if ($aws_object_uri == '' && isset($data['@metadata']) == TRUE && isset($data['@metadata']['effectiveUri']) == true) {
                        $aws_object_uri = $data['@metadata']['effectiveUri'] ?? '';
                    }
                }

                $aws_response = json_encode($amazons3_updload['data']);
                $attachments['file_name'] = $file_name;
                $attachments['raw_name'] = $file_name;
                $attachments['file_path'] = $folder_key;
                $attachments['file_type'] = $file_type;
                $attachments['file_ext'] = $file_ext;
                $attachments['file_size'] = $file_size;
                $attachments['created_at'] = DATE_TIME;
                $attachments['created_by'] = $config_ary['adminId'] ?? NULL;
                $attachments['doc_id'] = $directory_name ?? NULL;
                $attachments['aws_object_uri'] = $aws_object_uri;
                $attachments['aws_uploaded_flag'] = $aws_uploaded_flag;
                $attachments['aws_file_version_id'] = $aws_file_version_id;

                $this->CI->s3loges->setTitle($file_name . ' - S3 File transfer Completed');
                $this->CI->s3loges->setLogType('success');
                $this->CI->s3loges->setDescription(json_encode($obj));
                $this->CI->s3loges->createS3Log();

            } else {
                // failed
                $aws_response['data'] = json_encode($amazons3_updload['data']);
                $aws_response['aws_uploaded_flag'] = 0;
                $aws_response['aws_object_uri'] = '';

                $this->CI->s3loges->setTitle($file_name . ' - S3 File transfer Completed with error!');
                $this->CI->s3loges->setLogType('failure');
                $this->CI->s3loges->setDescription($aws_response['data']);
                $this->CI->s3loges->createS3Log();
            }
            $attachments['aws_response'] = $aws_response;

        }

        return $attachments;
    }
    /**
     *
     *  Upload the files from local App server to S3
     *
     * Functions has the following features
     * 1. To form the relavent array of data and then call
     * 2. Call the S3 upload functions
     * 3. Return the response from S3
     *
     * @param $config_ary {array} required data for s3 upload
     * @param $testCase {bool} flag for testcase execution
     *
     * @see setSourceFile()
     * @see setFolderKey()
     * @see testUploadDocumentMock()
     * @see uploadDocument()
     *
     * @return $attachments {array} S3 Response with custimized data
     */
    public function upload_from_app_to_s3($config_ary, $testCase) {

        if(!$config_ary)
            return

        $attachments = [];
        $file_size = $file_type = '';

        $root_folder = $config_ary['upload_path'];

        $directory_name = $config_ary['directory_name'] ? $config_ary['directory_name'] .'/' : NULL;

        // load amazon s3 library
        $this->CI->load->library('AmazonS3');

        $file_name = $config_ary['file_name'];
        $app_server_upload_name = $config_ary['uplod_folder'] ?? NULL;
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $moduleid = array_key_exists('module_id', $config_ary) ? $config_ary['module_id'] : 0;
        $title =  array_key_exists('title', $config_ary) ? $config_ary['title'] :
            'S3 File transfer intitated!';
        $created_by = array_key_exists('created_by', $config_ary) ? $config_ary['created_by'] : NULL;

        $folder_key = $root_folder . $directory_name . $file_name;

        $source_file = $app_server_upload_name . $folder_key;

        if(array_key_exists('attachment_path', $config_ary)) {
            $source_file =  $config_ary['attachment_path'];
        }

        if(file_exists($source_file)) {
            $file_size = filesize($source_file);
            $file_type = mime_content_type($source_file);
        }

        /**
         * set dynamic values
         * $tmp_name should be - Uploaded file tmp storage path
         * $folder_key should be - Saving path with file name into s3 bucket
         *      - you can add a folder like `folder/folder/filename.ext`
         */
        $this->CI->amazons3->setSourceFile($source_file);
        $this->CI->amazons3->setFolderKey($folder_key);

        $obj = new stdClass;
        $obj->file_name = $file_name;
        $obj->file_type = $file_type;
        $obj->file_size = $file_size;
        $obj->s3_folder_key = $folder_key;

        $this->CI->s3loges->setModuleId($moduleid);
        $this->CI->s3loges->setCreatedAt(DATE_TIME);
        $this->CI->s3loges->setCreatedBy($created_by);

        // Upload file if testCase true use mock object
        if ($testCase) {
            $amazons3_updload = $this->CI->amazons3->testUploadDocumentMock();
        } else {
            //Skip to store s3 log while running migration script
            if(!array_key_exists('from_doc_migration', $config_ary))
            {
                $this->CI->s3loges->setTitle($title);
                $this->CI->s3loges->setLogType('init');
                $this->CI->s3loges->setDescription(json_encode($obj));
                $this->CI->s3loges->createS3Log();
            }
            #Upload files into custom bucket
            if(!empty($config_ary['bucket_name'])) {                
                $amazons3_updload = $this->CI->amazons3->uploadDocumentwithCustomBucketName($config_ary);
            } else {
                $amazons3_updload = $this->CI->amazons3->uploadDocument();
            }
        }

        if ($amazons3_updload['status'] == 200) {
            // success
            $aws_uploaded_flag = 1;
            $aws_object_uri = '';
            $aws_file_version_id = '';
            if (isset($amazons3_updload['data']) == TRUE && empty($amazons3_updload['data']) == FALSE) {
                $data = $amazons3_updload['data'];
                $aws_object_uri = $data['ObjectURL'] ?? '';

                if ($aws_file_version_id == '' && isset($data['VersionId']) == TRUE) {
                    $aws_file_version_id = $data['VersionId'] ?? '';
                }

                if ($aws_object_uri == '' && isset($data['@metadata']) == TRUE && isset($data['@metadata']['effectiveUri']) == true) {
                    $aws_object_uri = $data['@metadata']['effectiveUri'] ?? '';
                }
            }

            $aws_response = json_encode($amazons3_updload['data']);
            $attachments['file_name'] = $file_name;
            $attachments['raw_name'] = $file_name;
            $attachments['file_path'] = $folder_key;
            $attachments['file_type'] = $file_type;
            $attachments['file_ext'] = $file_ext;
            $attachments['file_size'] = $file_size;
            $attachments['created_at'] = DATE_TIME;
            $attachments['created_by'] = $config_ary['adminId'] ?? NULL;
            $attachments['doc_id'] = $directory_name;
            $attachments['aws_object_uri'] = $aws_object_uri;
            $attachments['aws_uploaded_flag'] = $aws_uploaded_flag;
            $attachments['aws_file_version_id'] = $aws_file_version_id;

            //Skip to store s3 log while running migration script
            if(!array_key_exists('from_doc_migration', $config_ary))
            {
                $this->CI->s3loges->setTitle($file_name . ' - S3 File transfer Completed');
                $this->CI->s3loges->setLogType('success');
                $this->CI->s3loges->setDescription(json_encode($obj));
                $this->CI->s3loges->createS3Log();
            }
        } else {
            // failed
            $aws_response['data'] = json_encode($amazons3_updload['data']);
            $aws_response['aws_uploaded_flag'] = 0;
            $aws_response['aws_object_uri'] = '';

            //Skip to store s3 log while running migration script
            if(!array_key_exists('from_doc_migration', $config_ary))
            {
                $this->CI->s3loges->setTitle($file_name . ' - S3 File transfer Completed with error!');
                $this->CI->s3loges->setLogType('failure');
                $this->CI->s3loges->setDescription($aws_response['data']);
                $this->CI->s3loges->createS3Log();
            }
        }

        $attachments['aws_response'] = $aws_response;

        return $attachments;

    }

    /** API for Performance Check */
    public function s3_common_upload_single_attachment_api($config_ary, $testCase, $multipart ='yes') {
        if(!$config_ary || empty($_FILES))
            return

        $aws_response = [];

        if (!empty($_FILES)) {

            $root_folder = $config_ary['upload_path'];
            $input_name = $config_ary['input_name'];
            $directory_name = $config_ary['directory_name'] ? $config_ary['directory_name'] .'/' : NULL;
            $files = $_FILES;
            $created_by = array_key_exists('created_by', $config_ary) ? $config_ary['created_by'] : NULL;

            // load amazon s3 library
            $this->CI->load->library('AmazonS3');

            $file_name = $files[$input_name]['name'];
            $file_type = $files[$input_name]['type'];
            $tmp_name = $files[$input_name]['tmp_name'];
            $file_size = $files[$input_name]['size'];
            $filename = pathinfo($file_name, PATHINFO_FILENAME);
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

            $folder_key = $root_folder . $directory_name . $filename . '_'. time() . '.'. $file_ext;


            /**
             * set dynamic values
             * $tmp_name should be - Uploaded file tmp storage path
             * $folder_key should be - Saving path with file name into s3 bucket
             *      - you can add a folder like `folder/folder/filename.ext`
             */
            $this->CI->amazons3->setSourceFile($tmp_name);
            $this->CI->amazons3->setFolderKey($folder_key);

            $obj = new stdClass;
            $obj->file_name = $file_name;
            $obj->file_type = $file_type;
            $obj->file_size = $file_size;
            $obj->s3_folder_key = $folder_key;

            $this->CI->s3loges->setModuleId($config_ary['module_id'] ?? 0);
            $this->CI->s3loges->setCreatedAt(DATE_TIME);
            $this->CI->s3loges->setCreatedBy($created_by);

            // Upload file if testCase true use mock object
            if ($testCase == true) {
                $amazons3_updload = $this->CI->amazons3->testUploadDocumentMock();
            } else {

                if($multipart == 'yes') {
                    $amazons3_updload = $this->CI->amazons3->uploadDocument();
                }else if($multipart == 'no') {
                    $amazons3_updload = $this->CI->amazons3->uploadDocumentBYPutObject();
                }
            }

            if ($amazons3_updload['status'] == 200) {
                // success
                $aws_uploaded_flag = 1;
                $aws_object_uri = '';
                $aws_file_version_id = '';
                if (isset($amazons3_updload['data']) == TRUE && empty($amazons3_updload['data']) == FALSE) {
                    $data = $amazons3_updload['data'];
                    $aws_object_uri = $data['ObjectURL'] ?? '';

                    if ($aws_file_version_id == '' && isset($data['VersionId']) == TRUE) {
                        $aws_file_version_id = $data['VersionId'] ?? '';
                    }

                    if ($aws_object_uri == '' && isset($data['@metadata']) == TRUE && isset($data['@metadata']['effectiveUri']) == true) {
                        $aws_object_uri = $data['@metadata']['effectiveUri'] ?? '';
                    }
                }

                $aws_response = json_encode($amazons3_updload['data']);
                $attachments['file_name'] = $file_name;
                $attachments['raw_name'] = $file_name;
                $attachments['file_path'] = $folder_key;
                $attachments['file_type'] = $file_type;
                $attachments['file_ext'] = $file_ext;
                $attachments['file_size'] = $file_size;
                $attachments['created_at'] = DATE_TIME;
                $attachments['created_by'] = $config_ary['adminId'] ?? NULL;
                $attachments['doc_id'] = $directory_name ?? NULL;
                $attachments['aws_object_uri'] = $aws_object_uri;
                $attachments['aws_uploaded_flag'] = $aws_uploaded_flag;
                $attachments['aws_file_version_id'] = $aws_file_version_id;

            } else {
                // failed
                $aws_response['data'] = $amazons3_updload['data'];
                $aws_response['aws_uploaded_flag'] = 0;
                $aws_response['aws_object_uri'] = '';
            }
            $attachments['aws_response'] = $aws_response;

        }

        return $attachments;
    }
    /**
     * Upload Multiple files
     * @param $config_ary {array} upload configuration details
     *
     * @see s3_common_upload_single_attachment - moves files to S3
     * @see is_image - check the uploaded file type is image or not image
     *
     * @return $response {array} Response from S3 and details
     */
    public function do_muliple_upload($config_ary)
    {

        $response = array();

        if (!empty($config_ary)) {

            $input_name = $config_ary['input_name'];

            $files = $_FILES;
            $cpt = count($_FILES[$input_name]['name']);

            for ($i = 0; $i < $cpt; $i++) {
                $_FILES[$input_name]['name'] = $files[$input_name]['name'][$i];
                //Change multiple file into single file
                $_FILES[$input_name]['type'] = $files[$input_name]['type'][$i];
                $_FILES[$input_name]['tmp_name'] = $files[$input_name]['tmp_name'][$i];
                $_FILES[$input_name]['error'] = $files[$input_name]['error'][$i];
                //Convert into KB
                $_FILES[$input_name]['size'] = $files[$input_name]['size'][$i];

                $s3documentAttachment = $this->s3_common_upload_single_attachment($config_ary, FALSE);

                if (!isset($s3documentAttachment) || !$s3documentAttachment['aws_uploaded_flag']) {
                    // return error comes in file uploading
                    $response[] = ['status' => FALSE, 'error' => 'Document Attachment is not created. something went wrong'];
                }
                else {
                    $s3documentAttachment['orig_name'] = pathinfo($s3documentAttachment['file_name'], PATHINFO_FILENAME);
                    $s3documentAttachment['client_name'] = $s3documentAttachment['file_name'];
                    $s3documentAttachment['full_path'] = $s3documentAttachment['file_name'];
                    $s3documentAttachment['is_image'] = $this->is_image($_FILES[$input_name]['tmp_name']);
                    $response[] = ['upload_data' => $s3documentAttachment];
                }
            }

            return $response;
        }
    }

    /**
     * Check the filetype is image or not
     *
     * @param $file_name {string} name of the file
     *
     * @return bool true/false
     */
    public function is_image($file_name) {
        $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
        $detectedType = exif_imagetype($file_name);

        return in_array($detectedType, $allowedTypes);
    }

    /** Copy files from s3 folder to another s3 folder with in same bucket */
    public function s3_copy_file($config) {

        if($config && array_key_exists('from', $config) && array_key_exists('to', $config)) {

            $file_name = array_key_exists('file_name', $config) ?  $config['file_name'] : NULL;

            $file_size = $file_type = '';
            $attachments = [];
            $title =  array_key_exists('title', $config) ? $config['title'] :
            'S3 File copy intitated!';

            $created_by = array_key_exists('created_by', $config) ? $config['created_by'] : NULL;
            $file_path = $config['file_path'] ?? NULL;
            $file_info = $this->get_document_details($file_path);

            if (isset($file_info) && $file_info['status'] == 200) {
                $file_size = $file_info['data']['ContentLength'] ?? '';
                $file_type = $file_info['data']['ContentType'] ?? '';
            }

            $this->CI->s3loges->setModuleId($config['module_id'] ?? 0);
            $this->CI->s3loges->setCreatedAt(DATE_TIME);
            $this->CI->s3loges->setCreatedBy($created_by);

            $obj = new stdClass;
            $obj->file_name = $file_name;
            $obj->file_type = $file_type;
            $obj->file_size = $file_size;
            $obj->s3_folder_key = $config['to'];

            $this->CI->s3loges->setTitle($title);
            $this->CI->s3loges->setLogType('init');
            $this->CI->s3loges->setDescription(json_encode($obj));
            $this->CI->s3loges->createS3Log();

            $this->CI->amazons3->setFolderKey($config['from']);
            $from = $this->CI->amazons3->getObjectUrl();

            if($from['status'] == 200) {
                $this->CI->amazons3->setSourceFile($from['url']);
                $this->CI->amazons3->setFolderKey($config['to']);
                //Copy file from one location to another location
                $amazons3_get = $this->CI->amazons3->moveToArchive();
            } else {
                return $attachments['aws_uploaded_flag'] = 0;
            }

            $aws_file_version_id = '';

            if ($amazons3_get['status'] == 200) {

                if (isset($amazons3_get['data']) && !empty($amazons3_get['data'])) {
                    $data = $amazons3_get['data'];
                    $aws_object_uri = $data['ObjectURL'] ?? '';

                    if ($aws_file_version_id == '' && isset($data['VersionId'])) {
                        $aws_file_version_id = $data['VersionId'] ?? '';
                    }

                    if ($aws_object_uri == '' && isset($data['@metadata']) && isset($data['@metadata']['effectiveUri'])) {
                        $aws_object_uri = $data['@metadata']['effectiveUri'] ?? '';
                    }

                }
                $aws_response = json_encode($amazons3_get['data']);
                $attachments['file_type'] = $file_type;
                $attachments['file_size'] = $file_size;
                $attachments['created_at'] = DATE_TIME;
                $attachments['aws_object_uri'] = $aws_object_uri;
                $attachments['aws_file_version_id'] = $aws_file_version_id;
                $attachments['aws_uploaded_flag'] = 1;
                $this->CI->s3loges->setTitle($file_name . ' - S3 File Copy Completed');
                $this->CI->s3loges->setLogType('success');
                $this->CI->s3loges->setDescription(json_encode($obj));
                $this->CI->s3loges->createS3Log();
            }
            else {
                // failed
                $aws_response['data'] = json_encode($amazons3_get['data']);
                $aws_response['aws_uploaded_flag'] = 0;
                $aws_response['aws_object_uri'] = '';

                $this->CI->s3loges->setTitle($config['from'] . ' - S3 File Copy Completed with error!');
                $this->CI->s3loges->setLogType('failure');
                $this->CI->s3loges->setDescription($aws_response['data']);
                $this->CI->s3loges->createS3Log();

                $attachments['aws_uploaded_flag'] = 0;
            }
            $attachments['aws_response'] = $aws_response;
        } else {
            $attachments['aws_uploaded_flag'] = 0;
        }

        return $attachments;
    }


    /** Get document details using file path */
    function get_document_details($file_path) {
        if($file_path) {
            $this->CI->load->library('AmazonS3');
            $this->CI->amazons3->setFolderKey($file_path);
            return $this->CI->amazons3->getDocument();
        }
    }
     /** Download document in app server temp folder sending email or any other operations */
    function downloadDocumentTemp($s3_file_path, $id, $subfolder) {

        $this->CI->load->library('AmazonS3');
        $this->CI->amazons3->setFolderKey($s3_file_path);
        $this->CI->amazons3->setSourceFile(NULL);
        $this->CI->amazons3->downloadDocumentTemp($id, $subfolder);
    }
}
