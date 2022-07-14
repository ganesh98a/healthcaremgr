<?php

use function PHPSTORM_META\type;

defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * controller name: common
 */

//class Master extends MX_Controller
class Common extends MX_Controller {

    function __construct() {

        parent::__construct();
        $this->load->model('Common_model');
        $this->load->helper('i_pad');
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

    public function index() {

    }

    /**
     * removing any access level locks taken by the admin user for optional object and object id
     */
    public function remove_access_lock() {
        $reqData = request_handler();

        if (empty($reqData->data)) {
            $return = ["status" => false, "error" => "No Data!"];
            echo json_encode($return);
            exit;
        }

        $return = $this->Common_model->remove_access_lock((array) $reqData->data, $reqData->adminId);
        echo json_encode($return);
        exit;
    }

    /**
     * Remove member access lock for shift
     * Called on logout from member portal
     */
    public function remove_member_access_lock() {
        $this->load->helper('i_pad_helper');

        $reqData = api_request_handler();
        log_message("message", null, "member-portal");
        if (empty($reqData)) {
            $return = ["status" => false, "error" => "No Data!"];
            echo json_encode($return);
            exit;
        }
        if (!empty($reqData->member_id)) {
            $return = $this->Common_model->remove_access_lock((array) $reqData, $reqData->member_id);
        }
        echo json_encode($return);
        exit;
    }

    /**
     * takes the lock of object & object id if not taken otherwise returns false
     */
    public function get_take_access_lock() {
        $reqData = request_handler();

        if (empty($reqData->data)) {
            $return = ["status" => false, "error" => "No Data!"];
            echo json_encode($return);
            exit;
        }

        $return = $this->Common_model->get_take_access_lock((array) $reqData->data, $reqData->adminId);
        echo json_encode($return);
        exit;
    }

    /**
     * Get all cost books from reference table
     */
    public function get_cost_book_options() {
        $reqData = request_handler();

        if (empty($reqData->adminId)) {
            $return = ["status" => false, "error" => "No Data!"];
            echo json_encode($return);
            exit;
        }

        $return = $this->Common_model->get_cost_book_options();
        echo json_encode($return);
        exit;
    }

    public function archive_all() {
        $reqData = request_handler();
        $this->loges->setCreatedBy($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            if (!empty($reqData->loges)) {
                $this->loges->setModule($reqData->loges->module);
                $this->loges->setTitle($reqData->loges->note);
                $this->loges->setUserId($reqData->loges->userId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();
            }

            $result = $this->basic_model->update_records($reqData->table, array('archive' => 1), $where = array('id' => $reqData->id));
            echo json_encode(array('status' => true));
        } else {
            echo json_encode(array('status' => false, 'error' => 'Invalid Request'));
        }
    }

    public function get_state() {
        $reqData = request_handler();
        $state = $this->Common_model->get_state();

        echo json_encode(array('status' => true, 'data' => $state));
    }

    public function get_case_primary_cat() {
        $reqData = request_handler();
        $response = $this->basic_model->get_record_where('fms_case_all_category', $column = array('name', 'id'), $where = array('archive' => '0'));
        foreach ($response as $val) {
            $state[] = array('label' => $val->name, 'value' => $val->id);
        }
        echo json_encode(array('status' => true, 'data' => $state));
    }

    public function get_shift_requirement() {
        $reqData = request_handler();
        $this->load->model('schedule/Listing_model');
        $this->load->model('schedule/Schedule_model');

        $reqDataObj = $reqData->data;
        if (!isset($reqDataObj->type)) {
            echo json_encode(array('status' => true, 'data' => ['shift_data' => [], 'org_shift_data' => []]));
            exit;
        }
        $type = $reqDataObj->type;
        $id = $reqDataObj->id;
        /* $response = $this->basic_model->get_record_where('shift_requirement', $column = array('name', 'id'), $where = array('archive' => 0));
          foreach ($response as $val) {
          $state['shift_data'][] = array('label' => $val->name, 'value' => $val->id);
          } */

        $query = $this->Schedule_model->get_shift_requirement_for_participant_by_participant_id_or_shift_id(4);
        $state['shift_data'] = $query->num_rows() > 0 ? $query->result_array() : [];

        $query = $this->Schedule_model->get_shift_requirement_for_participant_by_participant_id_or_shift_id(5);
        $state['shift_data_mobility'] = $query->num_rows() > 0 ? $query->result_array() : [];
        $selectMsg = 'Select Address';
        if ($type == 1) {
            $selectMsg = 'Select Site Address';
            $state['org_shift_data'] = $response_org = $this->Listing_model->get_organisation_requirements_by_site_id_or_shft_id('1', $id);
            /* //$response_org = $this->basic_model->get_record_where('organisation_requirements', $column = array('name', 'id'), $where = array('archive' => 0));
              foreach ($response_org as $val) {
              $state['org_shift_data'][] = array('label' => $val->name, 'value' => $val->id);
              } */
        } else if ($type == 2) {
            $selectMsg = 'Select House Address';
            $state['org_shift_data'] = $this->Listing_model->get_organisation_requirements_by_house_id_or_shft_id('1', $id);
            //$response_org = $this->basic_model->get_record_where('organisation_requirements', $column = array('name', 'id'), $where = array('archive' => 0));
            /* foreach ($response_org as $val) {
              $state['org_shift_data'][] = array('label' => $val->name, 'value' => $val->id);
              } */
        }

        $rowsAddress = !empty($type) ? $this->Schedule_model->get_site_or_hosue_address_by_site_house_id($id, $type) : [];
        $resDefault = [['value' => '', 'label' => $selectMsg]];
        $rowsAddressData = !empty($rowsAddress) ? array_merge($resDefault, $rowsAddress) : $resDefault;
        $state['shift_address'] = $rowsAddressData;
        echo json_encode(array('status' => true, 'data' => $state));
    }

    function get_suburb() {
        $reqData = request_handler();
        $reqData->data = json_decode($reqData->data);
        $suburb = isset($reqData->data->query) ? $reqData->data->query : '';
        $state = isset($reqData->data->state) ? $reqData->data->state : 0;
        $rows = $this->Common_model->get_suburb($suburb, $state);
        echo json_encode($rows);
    }


    /**
     * Search for suburbs without requiring state ID
     */
    public function get_suburb_no_state_needed()
    {
        $reqData = request_handler();
        $reqData->data = json_decode($reqData->data);
        $suburb = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->Common_model->get_suburb_no_state_needed($suburb);
        return $this->output->set_content_type('json')->set_output(json_encode($rows));
    }


    function cookie_set() {
        //Get the s3 flag from querystring
        $s3_flag = $this->input->get('s3');

        if ($this->input->get('tc')) {
            setcookie('hcfd', $this->input->get('tc'), time() + (86400 * 1), "/"); // 86400 = 1 day
            if ($this->input->get('rd')) {
                $rd = base64_decode(urldecode($this->input->get('rd')));

                //Adding s3 flag
                $s3 = 'false';
                if($s3_flag == 'true') {
                    $s3 = 'true';
                }
                redirect($rd . "&s3=$s3");
            } else {
                echo 'Access denied';
                exit;
            }
        }
    }

    function get_user_for_compose_mail() {
        $reqData = request_handler();

        $rows = $this->Common_model->get_user_for_compose_mail($reqData->data);

        echo json_encode($rows);
    }

    function get_admin_name() {
        $reqData = request_handler();
        $reqData->data = gettype($reqData->data) == 'string' ? json_decode($reqData->data) : $reqData->data;
       // $currentAdminId = $reqData->adminId;
        $rows = $this->Common_model->get_admin_name($reqData->data);
        echo json_encode($rows);
    }

    function get_admin_name_by_filter() {
        $reqData = request_handler();
        $reqData = $reqData->data;

        if (!empty($reqData)) {
            $result = $this->Common_model->get_admin_name_by_filter($reqData);
            echo json_encode(['status' => true, 'data' => $result]);
        }
    }

    function get_admin_team_department() {
        $reqData = request_handler();

        $rows = $this->Common_model->get_admin_team_department($reqData->data->search, $reqData->adminId);

        echo json_encode($rows);
    }

    function get_department() {
        request_handler();

        $rows = $this->basic_model->get_record_where('department', array('id as value', 'name as label', 'short_code'), ['archive' => 0, 'short_code' => 'internal_staff']);

        echo json_encode(array('status' => true, 'data' => $rows));
    }

    function get_global_search_option() {
        $reqData = request_handler();

        if ($reqData->data) {
            $search = $reqData->data->search;

            $data = $this->Common_model->get_global_search_data($search, $reqData->adminId);

            $rows = array('status' => true, 'data' => $data);
            echo json_encode($rows);
        }
    }

    public function get_org_requirement() {
        $reqData = request_handler();
        $data = $this->Common_model->get_org_requirement();
        echo json_encode(array('status' => true, 'data' => $data));
    }

    public function get_member_name() {
        $reqData = request_handler();
        $reqData->data = json_decode($reqData->data);
        $post_data = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->Common_model->get_member_name($post_data);
        echo json_encode($rows);
    }

    public function get_org_name() {
        $reqData = request_handler();
        $reqData->data = json_decode($reqData->data);
        $name = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->Common_model->get_org_name($name);
        echo json_encode($rows);
    }

     public function get_is_org_name() {
        $reqData = request_handler();
        $reqData->data = json_decode($reqData->data);
        $name = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->Common_model->get_is_org_name($name);
        echo json_encode($rows);
    }

    public function archive_all_primary() {

        $reqData = request_handler();

        $this->loges->setCreatedBy($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $this->load->library('Checkarchiveprimary', null, 'commonarchive');
            $this->commonarchive->setCurrentData($reqData->table);
            $result = $this->commonarchive->checkSecondaryDataExist($reqData, true);
            if (!empty($reqData->loges) && $result['status']) {
                $this->loges->setModule($reqData->loges->module);
                $this->loges->setTitle($reqData->loges->note);
                $this->loges->setUserId($reqData->loges->userId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();
            }
            echo json_encode($result);
        } else {
            echo json_encode(array('status' => false, 'error' => 'Invalid Request'));
        }
    }

    public function get_recruitment_staff() {
        $reqData = request_handler();
        $reqData->data = json_decode($reqData->data);
        $post_data = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->Common_model->get_recruitment_staff($post_data);
        echo json_encode($rows);
    }

    public function get_data() {
        $rows = $this->basic_model->get_record_where('participant_genral', '', '');
        echo json_encode($rows);
    }

    public function mediaShow($type, $file_name, $userId = '') { 
        $link_token = $this->input->get('link');
        
        $this->verify_link_token($link_token);

        $file_name = base64_decode(urldecode($file_name));
       
        //Get S3 status from url
        $s3_flag = $this->input->get('s3');

        /* To Checks and download available content from s3, if not available
           then download from local */
        if($s3_flag == 'true') {

            $response = $this->s3MediaDownload($file_name, $this->input->get('download_as'));
            
            if(isset($response) && $response['status'] == 400) {
                echo "File not found";
            }
            return;

        }
        
        $this->load->helper('file');
        $this->load->helper('cookie');
        $token = get_cookie('hcfd');
        $check_token = 1;

        # for any recruitment level files, we are bypassing the token functionality
        # because we are previewing the attachments through Google Preview and not possible with token
        if($type == 'r' || $type == 'rp' || $type == 'rc' || $type == 'rg' || $type == 'f') {
            $check_token = 0;
        }
        $resData = $this->Common_model->file_content_media($type, $file_name, $userId, $check_token, ['token' => $token]);

        // support updated file name
        $downloadAs = $this->input->get('download_as');
        if (!empty($downloadAs)) {
            $file_name = $downloadAs;
        }
        
        if ($resData['status']) {
            header('content-type: ' . $resData['mimetype']);

            // preview in browser (eg pdf, img) if possible, or download.
            // Use the decoded file name as the name of the file instead of using cryptic filename
            // Let preserve the prev behaviour by previewing the file instead of force-download (so don't use 'Content-Disposition: attachment')
            header("Content-Disposition: inline; filename=$file_name");
        }
        echo $resData['msg'];
        exit;
        // $data = FCPATH.APPLICANT_ATTACHMENT_UPLOAD_PATH.'100/n_e.mp4';
    }

    /** Helper function to Download and delete the temporary PDF from s3*/
    public function mediaShowDownloadForm($type, $file_name, $userId = '') { 
        $link_token = $this->input->get('link');
        
        $this->verify_link_token($link_token);

        $file_name = base64_decode(urldecode($file_name));
       
        //Get S3 status from url
        $s3_flag = $this->input->get('s3');

        /* To Checks and download available content from s3, if not available
           then download from local */
        if($s3_flag == 'true') {            
            $response = $this->s3MediaDownload($file_name, $this->input->get('download_as'));
            # Delete tempory PDF from S3
            //$this->s3MediaDelete($file_name);

            if(isset($response) && $response['status'] == 400) {
                echo "File not found";
            }            
        }
        return TRUE;
      
    }

    public function mediaShowProfile($type, $file_name, $userId = '', $genderType = '0') {
        $file_name = base64_decode(urldecode($file_name));
        $userId = !empty($userId) ? base64_decode(urldecode($userId)) : $userId;
        $genderType = !empty($genderType) ? base64_decode(urldecode($genderType)) : $genderType;
        $resData = $this->Common_model->file_content_media($type, $file_name, $userId, 0, ['genderType' => $genderType, 'defaultImageShow' => 1]);
        if ($resData['status']) {
            header('content-type: ' . $resData['mimetype']);
            header("Content-Disposition: inline; filename=$file_name");
        }
        echo $resData['msg'];
        exit;
    }

    public function mediaDownload($type = 'all', $file_name) {
        $this->load->helper('file');
        $filePath = FCPATH;
        $filePath .= ARCHIEVE_DIR . '/';
        /* if($type == 'r'){
          //path add if specail folder created inside dir
          } */
        $filePath .= $file_name;
        $temp_filename = $filePath;

        if (is_file($temp_filename) && file_exists($temp_filename)) {
            $filename = basename($temp_filename);
            header("Content-Type:application/zip");
            header("Content-Disposition: attachment; filename=$filename");
            header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
            header("Content-Length:" . filesize($temp_filename));

            if (readfile($temp_filename)) {
                unlink($temp_filename);
            }
        } else {
            echo "File not exists";
        }
        exit;
    }

    /** Download content from S3
     * @param $s3_file_path {string} S3 File Path
     * @param $file_name {string} filename which is stored in user machine
     * @return error status / File download
     * **/
    public function s3MediaDownload($s3_file_path, $file_name = NULL) {
        $this->load->library('AmazonS3');
        $this->amazons3->setFolderKey($s3_file_path);
        $this->amazons3->setSourceFile($file_name);
        return $this->amazons3->downloadDocument();
    }

    /** Delete file from S3 */
    public function s3MediaDelete($s3_file_path = null)
    {   
        if($s3_file_path) {
            $this->load->library('AmazonS3');
            $this->amazons3->setFolderKey($s3_file_path);        
            return $this->amazons3->deleteFolder();
        }
    }
    function get_all_public_holiday() {
        $reqData = request_handler();
        $res = $this->Common_model->get_all_public_holiday();

        echo json_encode(['data' => $res, 'status' => true]);
    }

    function get_document_category_by_user_type() {
        $reqData = request_handler();
        $res = $this->Common_model->get_document_category_by_user_type();

        echo json_encode(['data' => $res, 'status' => true]);
    }

    /**
     * Fetch all infographics by `module` and by `page_url`.
     *
     * `POST: /common/common/get_infographics`
     *
     * Expect these items from request data:
     * - `module` - usually suffixed with the word 'portal' (eg `org portal`, `admin portal`, `xxxxx portal`)
     * - `page_url` - current front-end url
     *
     * @return void
     */
    public function get_infographics() {
        $req = request_handler();
        $data = $req->data;
        $para['page_module'] = $data->module;
        $para['page_url'] = $data->page_url;

        if ($para["page_url"]) {
            $para["page_url"] = $this->stripOutNumericUrlSegments($para["page_url"]);
        }

        $res = $this->basic_model->get_record_where('infographics', '', $para);
        if ($res) {
            echo json_encode(['data' => $res, 'status' => true]);
        } else {
            echo json_encode(['data' => null, 'status' => false]);
        }
    }

    /**
     * Strip numeric url segments
     * Eg. `/admin/fms/case/123123/case_details` => `/admin/fms/case/case_details`
     *
     * @param mixed $page_url
     * @return string
     */
    protected function stripOutNumericUrlSegments($page_url) {
        $tokens = explode('/', $page_url);
        $newTokens = [];
        foreach ($tokens as $token) {
            if ($token && is_numeric($token)) {
                continue;
            }
            $newTokens[] = $token;
        }
        $newPageUrl = implode('/', $newTokens);
        return $newPageUrl;
    }

    /**
     * Controller action that supplies options for 'title' dropdown
     *
     * @return CI_Output
     */
    public function title_options()
    {
        request_handler();
        $this->output->set_content_type('json');
        $this->load->model('sales/Reference_model');
        $options = $this->Reference_model->get_title_reference_options();

        return $this->output->set_output(json_encode([
            'status' => true,
            'data' => $options,
        ]));
    }



    function create_pdf() {
        $pdf_data = ["applicant_name" => "Girish Bagmor", 'task_time' => "17-06-2020"];
         $html = $this->load->view("recruitment/cab_certificate_pdf", $pdf_data, true);

        require_once APPPATH . 'third_party/mpdf7/vendor/autoload.php';



        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/custom/font/directory',
            ]),
            'fontdata' => $fontData + [
        'gotham_light' => [
            'R' => 'GothamRnd-Light.ttf',
            'I' => 'GothamRnd-Light.ttf',

        ],
        'gotham_medium' => [
            'R' => 'GothamRnd-Medium.ttf',
            'I' => 'GothamRnd-Medium.ttf',

        ],
        'gotham_book' => [
            'R' => 'GothamRnd-Book.ttf',
            'I' => 'GothamRnd-Book.ttf',

        ]
            ],
            // 'default_font' => 'frutiger'
        ]);


        $mpdf->WriteHTML($html);
        $mpdf->Output();
    }

//    function create_pdf(){
//        $pdf_data = ["applicant_name" => "Girish Bagmor", 'task_time' => "12/05/2019"];
//        $html = $this->load->view("recruitment/cab_certificate_pdf", $pdf_data, true);
//
//        require_once APPPATH . 'third_party/mpdf7/vendor/autoload.php';
//
//        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
//        $fontDirs = $defaultConfig['fontDir'];
//
//        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
//        $fontData = $defaultFontConfig['fontdata'];
//
//        $mpdf = new \Mpdf\Mpdf([
//            'fontDir' => array_merge($fontDirs, [
//                __DIR__ . '/custom/font/directory',
//            ]),
//            'fontdata' => $fontData + [
//        'frutiger' => [
//            'R' => 'GothamRnd-Light.ttf',
//            'I' => 'GothamRnd-Light.ttf',
//        ]
//            ],
//            'default_font' => 'frutiger'
//        ]);
//
//
//        $mpdf->WriteHTML($html);
//        $filename = "CAB_Certificate_" . rand(100000, 999999) . '.pdf';
//
//
//        $mpdf->Output($filename);
//    }

    function get_json(){
        $res = $this->basic_model->get_record_where("opportunity_status", [], []);
        echo json_encode($res);
    }

    /*
     * preview or download the temp attachment from seek
     * param {str} temp_folder
     * param {str} filemane (base64_encode)
     */
    public function mediaShowTemp($temp_folder) {
        $file_name = $this->input->get('filename');
        $file_name = base64_decode(urldecode($file_name));
        $this->load->helper('file');
        $this->load->helper('cookie');
        $filePath = FCPATH . ARCHIEVE_DIR . '/' . $file_name;
        $download_as = preg_replace('/\s+/', '_', $file_name);;
        if (is_file($filePath)) {
            $mimeType = get_mime_by_extension($filePath);
            header('content-type: ' . $mimeType);
            $msg = read_file($filePath);
            $status = true;
        } else {
            $msg = 'File not found';
            $status = false;
        }

        if ($status) {
            // preview in browser (eg pdf, img) if possible, or download.
            // Use the decoded file name as the name of the file instead of using cryptic filename
            // Let preserve the prev behaviour by previewing the file instead of force-download (so don't use 'Content-Disposition: attachment')
            header("Content-Disposition: inline; filename=$download_as");
        }
        echo $msg;
        exit;
    }

    /*
     * preview or download the temp and delete
     * param {str} temp_folder
     * param {str} filemane (base64_encode)
     */
    public function mediaShowTempAndDelete($temp_folder) {
        $file_name = $this->input->get('filename');
        $file_name = base64_decode(urldecode($file_name));
        $this->load->helper('file');
        $this->load->helper('cookie');
        $filePath = FCPATH . ARCHIEVE_DIR . '/' . $file_name;
        $download_as = preg_replace('/\s+/', '_', $file_name);
        if (is_file($filePath)) {
            $mimeType = get_mime_by_extension($filePath);
            header('content-type: ' . $mimeType);
            $msg = read_file($filePath);
            $status = true;
        } else {
            $msg = 'Link Expired';
            $status = false;
        }

        if ($status) {
            // preview in browser (eg pdf, img) if possible, or download.
            // Use the decoded file name as the name of the file instead of using cryptic filename
            // Let preserve the prev behaviour by previewing the file instead of force-download (so don't use 'Content-Disposition: attachment')
            header("Content-Disposition: inline; filename=$download_as");
        }
        echo $msg;

        if (read_file($filePath)) {
            //unlink($filePath);
        }

        exit;
    }

    /*
     * preview or download the service agreement attachment
     * param {str} attachment_id
     * param {str} filemane (base64_encode)
     */
    public function mediaShowSA($attachment_id) {
        $file_name = $this->input->get('filename');
        $file_name = base64_decode(urldecode($file_name));
        $this->load->helper('file');
        $this->load->helper('cookie');
        $filePath = FCPATH . SERVICE_AGREEMENT_CONTRACT_PATH . $attachment_id. '/' . $file_name;
        $download_as = preg_replace('/\s+/', '_', $file_name);;
        if (is_file($filePath)) {
            $mimeType = get_mime_by_extension($filePath);
            header('content-type: ' . $mimeType);
            $msg = read_file($filePath);
            $status = true;
        } else {
            $msg = 'File not found';
            $status = false;
        }

        if ($status) {
            // preview in browser (eg pdf, img) if possible, or download.
            // Use the decoded file name as the name of the file instead of using cryptic filename
            // Let preserve the prev behaviour by previewing the file instead of force-download (so don't use 'Content-Disposition: attachment')
            header("Content-Disposition: inline; filename=$download_as");
        }
        echo $msg;
        exit;
    }

    /*
     * preview or download the service agreement attachment
     * param {str} attachment_id
     * param {str} filemane (base64_encode)
     */
    public function mediaShowEA($attachment_id) {
        $link_token = $this->input->get('link');
        $this->verify_link_token($link_token);
       
        $file_path = base64_decode(urldecode($this->input->get('filename')));

        //Get S3 status from url
        $s3_flag = $this->input->get('s3');
        $file_name = $this->input->get('download_as');
        /* To Checks and download available content from s3, if not available
           then download from local */
        if($s3_flag == 'true') {
           
            
            $response = $this->s3MediaDownload( $file_path, $file_name );

            if(isset($response) && $response['status'] == 400) {
                echo "File not found";
            }
            return;

        }
        $this->load->helper('file');
        $this->load->helper('cookie');
        $filePath = FCPATH . EMAIL_ACTIVITY_FILE_PATH . $attachment_id. '/' . $file_name;
        $download_as = preg_replace('/\s+/', '_', $file_name);
        $status = false;
        $msg = 'File not found';

        if (is_file($filePath) && $s3_flag == "false") {
            $mimeType = get_mime_by_extension($filePath);
            header('content-type: ' . $mimeType);
            $msg = read_file($filePath);
            $status = true;
        }else if($s3_flag == "true") {
            $response = $this->s3MediaDownload(S3_EMAIL_ACTIVITY_FILE_PATH . $attachment_id. '/' . $file_name);

            if(isset($response) && $response['status'] == 400) {
                $msg = "File not found";
            }
        }

        if ($status) {
            // preview in browser (eg pdf, img) if possible, or download.
            // Use the decoded file name as the name of the file instead of using cryptic filename
            // Let preserve the prev behaviour by previewing the file instead of force-download (so don't use 'Content-Disposition: attachment')
            header("Content-Disposition: inline; filename=$download_as");
        }
        echo $msg;
        exit;
    }


    /*
     * preview or download the Imail
     * param {str} contentID
     * param {str} filemane (base64_encode)
     */
    public function mediaImailShowEA($contentID) {
        
        $link_token = $this->input->get('link');
        $this->verify_link_token($link_token);

        $file_name = $this->input->get('filename');
        $file_name = base64_decode(urldecode($file_name));
        
        $mail_type = $this->input->get('mailtype');
       
        $path = ($mail_type == 'internal') ? S3_INTERNAL_IMAIL_PATH : S3_EXTERNAL_IMAIL_PATH;
        
        $response = $this->s3MediaDownload($path . $contentID. '/' . $file_name);

            if(isset($response) && $response['status'] == 400) {
                echo "File not found";
            }

    }

    /*
     * preview or download the attachment
     * param {str} attachment_id
     * param {str} filemane (base64_encode)
     */
    public function mediaShowView($module, $attachment_id) {
        
        $file_name = $this->input->get('filename');
        $file_name = base64_decode(urldecode($file_name));
        $this->load->helper('file');
        $this->load->helper('cookie');

        switch($module) {
            case 'MA':
                $module_path = MEMBER_DOCUMENT_FILE_PATH;
                break;
            default:
                $module_path = '';
                break;
        }

        $filePath = FCPATH . $module_path . $attachment_id. '/' . $file_name;
        $download_as = preg_replace('/\s+/', '_', $file_name);;
        if (is_file($filePath)) {
            $mimeType = get_mime_by_extension($filePath);
            header('content-type: ' . $mimeType);
            $msg = read_file($filePath);
            $status = true;
        } else {
            $msg = 'File not found';
            $status = false;
        }

        if ($status) {
            // preview in browser (eg pdf, img) if possible, or download.
            // Use the decoded file name as the name of the file instead of using cryptic filename
            // Let preserve the prev behaviour by previewing the file instead of force-download (so don't use 'Content-Disposition: attachment')
            header("Content-Disposition: inline; filename=$download_as");
        }
        echo $msg;
        exit;
    }

    /**
     * Save viewed log
     */
    public function save_viewed_log() {
        $reqData = request_handler();
        $adminId = $reqData->adminId;

        if (!empty($reqData->data)) {

            $entity_type = $reqData->data->entity_type;
            $entity_id = $reqData->data->entity_id;

            $this->load->file(APPPATH.'Classes/common/ViewedLog.php');
            $viewedLog = new ViewedLog();
            // get entity type value
            $entity_type = $viewedLog->getEntityTypeValue($entity_type);

            // Set data
            $viewedLog->setEntityType($entity_type);
            $viewedLog->setEntityId($entity_id);
            $viewedLog->setViewedDate(DATE_TIME);
            $viewedLog->setViewedBy($adminId);

            // Create log
            $viewedLog = $viewedLog->createViewedLog();

            if ($viewedLog) {
                $reqData = $reqData->data;

                echo json_encode(array('status' => true));
            } else {
                echo json_encode(array('status' => false, 'error' => 'Something went wrong. please try again'));
            }
        } else {
            echo json_encode(array('status' => false, 'error' => 'Invalid Request'));
        }
        exit(0);
    }

    public function storeCMScontent() {
        $reqData = request_handler();
        $this->Common_model->storeCMScontent($reqData);
    }

    public function getAdminCMScontent() {
        request_handler();

        $data = $this->Common_model->getCMScontent();
        if($data) {
            echo json_encode(['status' => true, 'url' => $data[0]->url]);
        } else {
            echo json_encode(['status' => false, 'error' => 'Data not found']);
        }

        exit();
    }
    public function getMemberCMScontent() {
        $this->load->helper('i_pad');
        api_request_handler();

        $data = $this->Common_model->getCMScontent();
        if($data) {
            echo json_encode(['status' => true, 'url' => $data[0]->url]);
        } else {
            echo json_encode(['status' => false, 'error' => 'Data not found']);
        }

        exit();
    }
    //Download import logs
    public function download_import_stats() {
        $import_id = $this->input->get('id');
        $file_name = $this->input->get('file_name');
        if(!empty($import_id)) {
            $result = $this->Common_model->download_import_stats($import_id, $file_name);

            # plain print without json encoding
            echo $result;
            exit();
        }
    }

    /*
    * Get max file upload size from php.ini file
    * upload_max_filesize - upload max file size
    * post_max_size - poset max data size
    * memory_limit - execution memory limit
    * 
    * return - array
    */
    function max_upload_file_limit() {
        // get allowed limits
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $memory_limit = (int)(ini_get('memory_limit'));
        $upload_mb = min($max_upload, $max_post, $memory_limit);
        $result['max_upload'] = $max_upload;
        $result['max_post'] = $max_post;
        $result['memory_limit'] = $memory_limit;
        $result['upload_mb'] = $upload_mb;
        // response
        $response = ["status" => true, "data" => $result];
        echo json_encode($response);
        exit;
    }

    /**
     * removing any access level locks taken by the admin user for optional object and object id
     */
    public function remove_access_lock_portal() {
        $reqData = request_handler();
        
        if (empty($reqData)) {
            $return = ["status" => false, "error" => "No Data!"];
            echo json_encode($return);
            exit;
        }

        $return = $this->Common_model->remove_access_lock((array) $reqData->data, $reqData->data->member_id);
        echo json_encode($return);
        exit;
    }

    /**
     * takes the lock of object & object id if not taken otherwise returns false
     */
    public function get_take_access_lock_portal() {
        $reqData = request_handler();
        $reqData = $reqData->data;

        if (empty($reqData)) {
            $return = ["status" => false, "error" => "No Data!"];
            echo json_encode($return);
            exit;
        }

        $return = $this->Common_model->get_take_access_lock((array) $reqData, $reqData->member_id);
        echo json_encode($return);
        exit;
    }

    public function upload_user_avatar() {
        $reqData = request_handler();               
        if (!empty($reqData) && !empty($reqData->data)) {
            $adminId = $reqData->adminId; 
            $avatar = $reqData->data->avatar;
            $update = $this->basic_model->update_records('member', ['profile_pic' => $avatar], ['id' => $adminId]);
            if (!empty($update)) {
                $return = ["status" => true, "message" => "Profile pic updated successfully"];
                echo json_encode($return);
                exit;
            }
        }
    }
    
	
	/* fetch title info from a given url
		Used for editor
	*/
    public function get_url_info() {
        error_reporting(0);
        $agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
        $reqData = request_handler();
        $url = $reqData->data->url;
        $title = $url;
        try {
            $htm = file_get_contents($url);
            $doc = new DOMDocument();
            $doc->loadHTML($htm);
            $title = $doc->getElementsByTagName('title')->item(0)->nodeValue;
            if (empty($title)) {
                // try curl
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_USERAGENT, $agent);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
                curl_setopt($ch, CURLOPT_TIMEOUT, 400); //timeout in seconds
                try {
                    $HTML_DOCUMENT = curl_exec($ch);
                    curl_close($ch);
                    $doc = new DOMDocument();
                    $doc->loadHTML($HTML_DOCUMENT);
                    $title = $doc->getElementsByTagName('title')->item(0)->nodeValue;
                    if (empty($title)) {
                        $title = $url;
                    }
                } catch (Exception $e) {
                    $m = $e->getMessage();
                }
            }
        } catch (Exception $e) {
            $m = $e->getMessage();
        } 
        $return = ["status" => true, "message" => "Success", 'data' => ['title' => $title]];
        echo json_encode($return);
        exit;
    }

    /** Verify link token for downloading files */
    public function verify_link_token($link_token) {
        #Fetch the token while creating the token on file download click
        $result = $this->basic_model->get_row('file_download_validation', ['token'], ['token' => $link_token]);
        if(empty($result)) {
            echo '<h2 align="center">Link Expired!</h2>'; die;
        }
        # Delete the record once file got downloaded
        //$this->basic_model->delete_records('file_download_validation',['token' => $link_token]);
        return TRUE;        
    }
    public function verifydocumentDownload() {
       
        $reqData = request_handler('');
        $result = ['status' => FALSE, 'msg' => 'Something went wrong!'];
       
        if (!empty($reqData) && !empty($reqData->data)) {            
            $request = request_handler('access_' . $reqData->data->module_name);
            if(!empty($request)) {
                $token = random_genrate_password(10);
                #Set the token for download verification
               $this->Common_model->insert_file_download_validation_token($token);
                $result = ['status' => TRUE, 'token' => $token, 'msg' => 'Request Access Success!'];
            }
        }
       
        echo json_encode($result);
        exit();
    }    
    
    /** Uploads files into local server and then upload into S3 **/
    public function upload_editor_assets() {
        $reqData = request_handlerFile();        
        $response = [];        
        if (!empty($_FILES)) {
          $response = $this->Common_model->upload_editor_assets($reqData);
        }
        echo json_encode($response);
    }
    
    public function get_file()
    {
        //$this->load->library('AmazonS3');
        $token = $this->uri->segment(2)??0;
        $fileName = $this->uri->segment(3)??0;
        $fileName = str_replace("=","", $fileName);
        
        $existingToken = get_setting(Setting::DATA_MIGRATION_ACCESS_TOKEN, DATA_MIGRATION_ACCESS_TOKEN);
        if($existingToken != $token || empty($fileName)){
            return false;
        }
      
        $fileName = base64_decode( urldecode( $fileName ) );

        $response = $this->s3MediaDownload($fileName);
        
        if (isset($response) && $response['status'] == 400) {
            echo "File not found";
        }
        return;
    }

    public function get_business_unit_options()
    {
        $reqData = request_handler();
        $response = ['status' => FALSE, 'msg' => 'Data not found'];
        $data = $this->basic_model->get_record_where('business_units
        ', array('id as value', 'business_unit_name as label'));
        
        if(!empty($data)) {
            $response = ['status' => TRUE, 'data' => $data,'business_unit' => $reqData->business_unit ?? NULL];
        }

        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }
}
