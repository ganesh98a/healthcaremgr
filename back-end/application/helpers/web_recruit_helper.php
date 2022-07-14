<?php
if (!defined('BASEPATH'))
exit('No direct script access allowed');

include APPPATH . 'Classes/admin/jwt_helper.php';

use classPermission\Permission;

if (!function_exists('generate_token')) {
    function generate_token($username) {
        global $CI;
        $token = new stdClass;
        $token->iss = $CI->config->item('server_url');
        $token->sub = $username;
        $token->iat = DATE_TIME;
        $Obj_JWT = new \JWT();
        $JWT_Token = $Obj_JWT->encode($token, JWT_SECRET_KEY);
        return $JWT_Token;
    }
}
if (!function_exists('auth_login_status')) {

    function auth_login_status($login_detail) {
     
        $CI = & get_instance();
        $CI->load->model('Recruitment_member_model');
        try {
            $response = array();
            if (empty($login_detail)) {
                $response = array('status' => false, 'error' => system_msgs('something_went_wrong'));
                echo json_encode($response);
                exit;
            } elseif (isValidJson($login_detail)) {                		
                $applicant_id = '';
                $member_id = '';
                $device_token = '';
                if (isset($login_detail->applicant_id)) {
                    $applicant_id = $login_detail->applicant_id;
                }
                if (isset($login_detail->token)) {
                    $device_token = $login_detail->token;
                }
                if (isset($login_detail->member_id)) {
                    $member_id = $login_detail->member_id;
                }
                $is_member = false;
                if(!empty($member_id) && empty($applicant_id)){
                    $is_member = true;
                }

                $currunt_date = date("Y-m-d H:i:s");

                if (empty($device_token) && (empty($applicant_id) || empty($member_id))) {
                    $response = array('status' => false, 'error' => system_msgs('empty_token_applicant'));
                    echo json_encode($response);
                    exit;
                } else {                    
                    $response=$CI->Recruitment_member_model->verify_ipad_token((array)$login_detail, $is_member);
                   
                    if (!$response) {                        
                        $response = array('status' => false, 'error' => 'Token expired or Mismatch','another_location_opened' => true);
                        echo json_encode($response);
                        exit;
                    } 
                } 
            } else {
                $response = array('status' => false, 'error' => system_msgs('invalid_json'));
                echo json_encode($response);
                exit;
            }
        } catch (Exception $e) {
            
        }
    }

}
if (!function_exists('check_array_equal')) {
    function check_array_equal($a, $b) {
        return (
            is_array($a) 
            && is_array($b) 
            && count($a) == count($b) 
            && array_diff($a, $b) === array_diff($b, $a)
        );
    }
}

if(!function_exists('auth_login_status_file_type')){

    function auth_login_status_file_type(){
        
        $CI = & get_instance();
        $token = $CI->input->post('token');
        $applicant_id = $CI->input->post('applicant_id');
        $member_id = $CI->input->post('member_id');       
        
        if (empty($token) || empty($applicant_id) || empty($member_id)){
            $response = array('status' => false, 'error' => system_msgs('empty_token_applicant'));
            echo json_encode($response);
            exit;
        } else {
            $login_detail=['token'=>$token,'applicant_id'=>$applicant_id];
            $response=$CI->Recruitment_member_model->verify_ipad_token($login_detail);            
            if (!$response) {                        
                $response = array('status' => false, 'error' => 'Token expired or Mismatch');
                echo json_encode($response);
                exit;
            } 
        }
    }

}

if (!function_exists('isValidJson')) {

    function isValidJson($data = NULL) {
        if (!empty($data)) {
            @json_decode($data);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }

}
if (!function_exists('api_request_handler')) {

    function api_request_handler() {
        global $gl_request_body, $gl_execution_start;
        $request_body = file_get_contents('php://input');
        $request_body = json_decode($request_body);
        
        # tracking the execution start time and request body for later use
        $gl_request_body = $request_body;
        $date = new DateTime();
        $gl_execution_start = $date->format("Y-m-d H:i:s.u");
        
        if (empty($request_body) || empty($request_body->data)) {
            echo json_encode(array('status' => false, 'error' => system_msgs('INVALID_INPUT')));
            exit;
        } elseif (!isValidJson($request_body)) {
            echo json_encode(array('status' => false, 'error' => system_msgs('INVALID_JSON')));
            exit;
        } else {

            return $request_body->data;
        }
    }

}


function api_request_handlerFile()
{
    $CI = &get_instance();
    $data = (object) $CI->input->post();

    if (!empty($data)) {
         return $data;
    } else {
        echo json_encode(array('status' => false, 'token_status' => true, 'error' => system_msgs('verfiy_token_error')));
        exit();
    }
}