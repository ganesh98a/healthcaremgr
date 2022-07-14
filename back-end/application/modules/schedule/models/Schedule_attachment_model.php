<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * class: Schedule_attachment_model
 * use for query operation of activity
 */

//class Master extends MX_Controller
class Schedule_attachment_model extends CI_Model {

    function __construct() {

        parent::__construct();

    }   

    /*
     * Save the uploaded attachment to permanent folder
     * @param {int} $shift_id
     */
    function upload_shift_attachement($shift_id, $template_attachments = [], $member_id = NULL) {
        // archive if already exist
        if(!empty($member_id) && !empty($_FILES)){
            $is_attachment_exist = $this->basic_model->get_row('shift_timesheet_attachment', ['id'], ['shift_id' => $shift_id, 'member_id' => $member_id]);

            if($is_attachment_exist) { //Update record
                $this->basic_model->update_records("shift_timesheet_attachment", ["archive" => 1], ['shift_id' => $shift_id, 'member_id' => $member_id]);             
            } 
        }        
        
        $error = array();
        $response = array();
        require_once APPPATH . 'Classes/common/Aws_file_upload.php';
        $awsFileupload = new Aws_file_upload();
        if (!empty($_FILES)) {

            $config['input_name'] = 'attachments';
            $config['directory_name'] = $shift_id;
            $config['allowed_types'] = 'jpg|jpeg|png|xlx|xls|doc|docx|pdf|csv|odt|rtf';

            //Upload files in appserver for adding email attachments
            $files = $_FILES;
            // file path create if not exist
            $fileParDir = FCPATH . SHIFT_TIMESHEET;
            if (!is_dir($fileParDir)) {
                mkdir($fileParDir, 0755);
            }
            $config['upload_path'] = SHIFT_TIMESHEET;

            if(getenv('IS_APPSERVER_UPLOAD') == 'yes') {
                do_muliple_upload($config);
            }

            //Assign variable again into $_FILES because do_upload method will cleared the values
            $_FILES = $files;            
            $response = $awsFileupload->do_muliple_upload($config, FALSE);

            $attachments = array();

            if (!empty($response)) {
                foreach ($response as $key => $val) {
                    if (isset($val['error'])) {
                        $error[]['file_error'] = $val['error'];
                    } else {
                        $val = $val['upload_data'];
                        $attachments[$key]['filename'] = $val['file_name'];
                        $attachments[$key]['file_path'] = $val['file_path'];
                        $attachments[$key]['file_type'] = $val['file_type'];
                        $attachments[$key]['created_at'] = DATE_TIME;
                        $attachments[$key]['shift_id'] = $shift_id;
                        $attachments[$key]['member_id'] = $member_id;
                        $attachments[$key]['file_ext'] = "." . $val['file_ext'];
                        $attachments[$key]['file_size'] = $val['file_size'];
                        $attachments[$key]['aws_object_uri'] = array_key_exists('aws_object_uri', $val) ? $val['aws_object_uri'] : NULL;
                        $attachments[$key]['aws_uploaded_flag'] = array_key_exists('aws_uploaded_flag', $val) ? $val['aws_uploaded_flag'] : 0;
                        $attachments[$key]['aws_file_version_id'] = array_key_exists('aws_file_version_id', $val) ? $val['aws_file_version_id'] : NULL;
                        $attachments[$key]['aws_object_uri'] = array_key_exists('aws_object_uri', $val) ? $val['aws_object_uri'] : NULL;
                        $attachments[$key]['aws_response'] = array_key_exists('aws_response', $val) ? $val['aws_response'] : NULL;

                        //Download attachment into local for sending email
                        $awsFileupload->downloadDocumentTemp($attachments[$key]['file_path'], $shift_id,
                            'email_activity');
                    }
                }

                if (!empty($attachments)) {
                    $this->basic_model->insert_records('shift_timesheet_attachment', $attachments, true);
                }
            }
        }
        return $response;
    }

    /**
     * fetching need assessment attachment details
     */
    public function get_need_assessment_attachment_details($object_id, $object_type='') {


        $q = $this->db
            ->from('tbl_sales_attachment att')
            ->join('tbl_sales_attachment_relationship rel', 'rel.sales_attachment_id = att.id', 'INNER')
            ->where([
                'rel.object_id' => $object_id,
                'rel.object_type' => $object_type,
                'att.archive' => 0
            ])
            ->select(['att.id','att.file_name','att.file_path','att.file_size','att.client_name','att.orig_name','att.file_ext','att.created','rel.object_name'])
            ->order_by('att.created','DESC')
            ->get();

        $results = $q->result();
   
        $result = [];
        foreach ($results as $val) {       
            $file_url = '/' . urlencode(base64_encode($val->file_path)) . '?s3=true&download_as=' . $val->file_name;
            $val->file_show_url = $file_url;
            $result[] = $val;
        }    
       
        return array('data' => $results, 'status' => true);
        
    }

   
}
