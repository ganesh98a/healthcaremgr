<?php

/**
 *  Class name : Auth
 *  Create date : 18-07-2018,
 *  author : Corner stone solution
 *  Description : this class used for set cookie, check authentication, and set session
 * 
 */

namespace Admin\Auth;

require_once APPPATH . 'Classes/admin/admin.php';
require_once  APPPATH . 'Classes/admin/jwt_helper.php';

use AdminClass;
use stdClass;

class Auth extends AdminClass\admin {

    public function __construct() {
        $adminClass = new AdminClass\admin();
    }

    private $ocs_token;

    /*
     * Check admin is logged in
     */

    public function getOcsToken() {
        return $this->ocs_token;
    }

    public function setOcsToken($token) {
        $this->ocs_token = $token;
    }

    /*
     * check email and password authentication
     */

    public function check_auth() {
        $CI = & get_instance();
        $Obj_JWT = new \JWT();

        $CI->load->model('Admin_model');
        $result = $CI->Admin_model->get_member_details_by_uuid($this);
        if (!empty($result)) {
            # if this member locked?
            
            if(isset($result->is_locked) && $result->is_locked == 1) {
                $response = array('status' => 'false', 'error' => "Your account is locked!");
                return $response;
            }      
            
            if(isset($result->status) && $result->status == 0){
                return array('status' => 'false', 'error' => "Your account is Inactive!");
            }
           
            // check password using PASSWORD_BCRYPT method
            if (password_verify($this->getPassword(), $result->password)) {
                // check user active or not 
                if ($result->status) {
                    //set token
                    $this->setAdminid($result->id);
                    $this->setAvatar($result->avatar ?? '');
                    $uuid_user_type = $this->getUuid_user_type();
                    $token = new stdClass;
                    $token->iss = $CI->config->item('server_url');
                    $token->sub = $result->username;
                    $token->iat = DATE_TIME;

                    $JWT_Token = $Obj_JWT->encode($token, JWT_SECRET_KEY);

                    $this->setOcsToken($JWT_Token);
                    $this->setAdminLogin();
                    
                    if($uuid_user_type == ORGANISATION_PORTAL){ 
                        $response = array('token' => $JWT_Token, 'data' => $result, 'status' => true, 'success' => system_msgs('success_login'));
                    }else if($uuid_user_type == MEMBER_PORTAL){
                         $response = $this->check_member_auth_login($JWT_Token);
                    }else{
                        $response = array('token' => $JWT_Token, 'username' => $result->username, 'fullname' => $result->full_name, 'status' => true, 'success' => system_msgs('success_login'), 'avatar' => $result->avatar);
                    }

                } else {
                    $response = array('status' => false, 'error' => system_msgs('account_not_active'));
                }
            } else {
                $response = array('status' => false, 'error' => system_msgs('wrong_email_password'));
            }
        } else {
            $response = array('status' => false, 'error' => system_msgs('wrong_email_password'));
        }

        return $response;
    }

    // /*
    //  *  here verify reset password token
    //  */

    // public function verify_token() {
    //     $CI = & get_instance();
    //     $where = array('id' => $this->getAdminid(), 'otp' => $this->getOcsToken());
    //     $result = $CI->basic_model->get_record_where('member', array('firstname', 'lastname'), $where);
    //     return $result;
    // }

    /*
    *  here verify reset password token
    */

   public function verify_token($type='') {
       if (empty($type)) {
            $CI = & get_instance();
            $where = array('id' => $this->getAdminid(), 'password_token' => $this->getOcsToken(), 'user_type' => $this->getUuid_user_type() ?? ADMIN_PORTAL);
            $result = $CI->basic_model->get_record_where('users', array('id', 'username','user_type'), $where);
            return $result;
       } else {
            $CI = & get_instance();
            $where = array('uuid' => $this->getAdminid(), 'otp' => $this->getOcsToken());
            $result = $CI->basic_model->get_record_where('member', array('uuid', 'username'), $where);
            return $result;
       }
       
   }

    /*
     * reset password of admin
     */

    // public function reset_password() {
    //     $CI = & get_instance();
    //     $encry_password = password_hash($this->getPassword(), PASSWORD_BCRYPT);

    //     $userData = array('password' => $encry_password, 'otp' => '');
    //     $result = $CI->basic_model->update_records('member', $userData, $where = array('id' => $this->getAdminid()));
    //     return $result;
    // }

    public function reset_password() {
        $CI = & get_instance();
        $encry_password = password_hash($this->getPassword(), PASSWORD_BCRYPT);
        
        $memberData = array('status' => 1);
        $result = $CI->basic_model->update_records('member', $memberData, $where = array('uuid' => $this->getAdminid() ));
        $userData = array('password' => $encry_password, 'password_token' => '');
        $result = $CI->basic_model->update_records('users', $userData, $where = array('id' => $this->getAdminid(), 'user_type'=>$this->getUuid_user_type()));
        return $result;
    }

    /*
     * here insert entry of login
     */

    public function setAdminLogin() {
        $CI = & get_instance();
        $uuid_user_type = $this->getUuid_user_type();
            $response = $CI->basic_model->get_row('member_login', $columns = array('updated', 'memberId', 'token'), $where = array('memberId' => $this->getUuid()));
            if (!empty($response)) {
                $CI->basic_model->update_records('member_login', $columns = array('updated' => DATE_TIME, 'pin' => '', 'token' => $this->ocs_token, 'ip_address' => get_client_ip_server()), $where = array('memberId' => $this->getUuid()));
            } else {
                $CI->basic_model->insert_records('member_login', $data = array('token' => $this->ocs_token, 'memberId' => $this->getUuid(), 'updated' => DATE_TIME, 'pin' => '', 'ip_address' => get_client_ip_server()), $multiple = FALSE);
            }

        $response = $CI->basic_model->get_row('users', $columns = array('updated_at', 'id', 'token'), $where = array('id' => $this->getUuid()));
            if (!empty($response)) {
                $CI->basic_model->update_records('users', $columns = array('updated_at' => DATE_TIME, 'token' => $this->ocs_token), $where = array('id' => $this->getUuid()));
            } 
        
    }

    /*
     * unset user login token in db
     */

    public function unsetAdminLogin($token = false) {
        $CI = & get_instance();

        // check optional paramter and private ocs_token
        $token = (!empty($token)) ? $token : $this->ocs_token;
        $where = array('token' => $token);
        $tokenDetails = $CI->basic_model->get_row('member_login', array('id', 'memberId'), $where);
        if ($tokenDetails) {
            $wherePinRemove = array('token_id' => $tokenDetails->id, 'adminId' => $tokenDetails->memberId);
            $CI->basic_model->delete_records('admin_pin_token', $wherePinRemove);
        }
        $CI->basic_model->delete_records('member_login', $where);
    }

    public function checkCurrentPin() {
        $CI = & get_instance();

        $CI->load->model('Admin_model');
        $result = $CI->Admin_model->check_pin($this);

        if (!empty($result->pin)) {
            if (password_verify($this->getPin(), $result->pin)) {

                return $result->pin;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function verfiy_pin() {
        $Obj_JWT = new \JWT();
        $CI = & get_instance();
        $tableWithoutPrefix = 'admin_pin_token';

        $pin = $this->checkCurrentPin();
        $pinType = $this->getPinType();
        $ocsTokenDetail = $this->checkAuthAdminLoginToken();      
        if (!empty($pin) && !empty($pinType) && !empty($ocsTokenDetail)) {
            $token = array(DATE_TIME . $pin . $pinType);
            $JWT_Token = $Obj_JWT->encode($token, JWT_SECRET_KEY);
            $ocsTokenId = $ocsTokenDetail->id;

            //check  pintype get rows exits for this user 
            $row_exists = $this->pinTokenTypeRowExists(array('token_type' => $pinType, 'token_id' => $ocsTokenId));
            
            if (!empty($row_exists)) {
                $where = array('adminId' => $this->getAdminid(), 'token_type' => $pinType, 'token_id' => $ocsTokenId, 'id' => $row_exists->id);
                $CI->basic_model->update_records($tableWithoutPrefix, array('pin' => $JWT_Token, 'updated' => DATE_TIME), $where);
            } else {
                $CI->basic_model->insert_records($tableWithoutPrefix, array('pin' => $JWT_Token, 'adminId' => $this->getAdminid(), 'token_id' => $ocsTokenId, 'token_type' => $pinType, 'created' => DATE_TIME, 'updated' => DATE_TIME, 'ip_address' => get_client_ip_server()), $multiple = FALSE);
            }

            // update token
            $response = array('token' => $JWT_Token, 'status' => true, 'success' => system_msgs('token_verfied'));
        } else {
            $response = array('status' => false, 'error' => system_msgs('encorrect_pin'));
        }

        return $response;
    }

    public function verifyCurrentPassword() {
        $CI = & get_instance();

        $where = array('id' => $this->getAdminid(), 'user_type'=>$this->getUuid_user_type());
        $result = $CI->basic_model->get_row('users', $column = array('id', 'password'), $where);
            
        if (!empty($result)) {
            // check password using PASSWORD_BCRYPT method
            if (password_verify($this->getPassword(), $result->password)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function checkAuthToken() {
        $CI = & get_instance();

        $where = array('id' => $this->getAdminid(), 'password_token' => $this->getToken());
        return $result = $CI->basic_model->get_row('users', $column = array('id', 'password_token'), $where);
    }

    public function checkAuthAdminLoginToken($extaColumn = array()) {
        $CI = & get_instance();
        $column = array('id', 'token');
        $column = !empty($extaColumn) && is_array($extaColumn) ? array_merge($column, $extaColumn) : $column;
        $where = array('memberId' => $this->getAdminid(), 'token' => $this->getToken());

        return $result = $CI->basic_model->get_row('member_login', $column, $where);
    }

    public function pinTokenTypeRowExists($dataArr = array('token_type' => 0), $extaColumn = array()) {
        $CI = & get_instance();
        $tableWithoutPrefix = 'admin_pin_token';
        $adminId = $this->getAdminid();
        $tokenType = isset($dataArr['token_type']) ? (int) $dataArr['token_type'] : 0;
        $tokenId = isset($dataArr['token_id']) ? (int) $dataArr['token_id'] : 0;
        $column = array('id', 'pin');
        $column = !empty($extaColumn) && is_array($extaColumn) ? array_merge($column, $extaColumn) : $column;
        $where = array('adminId' => $adminId, 'token_type' => $tokenType, 'token_id' => $tokenId);

        return $result = $CI->basic_model->get_row($tableWithoutPrefix, $column, $where);
    }

    public function getJWT() {
        $data = $this->getEncodedJWT();
        if ($data) {
            $Obj_JWT = new \JWT();
            $jwtPayload = $Obj_JWT->decode($data, JWT_SECRET_KEY);
            return $jwtPayload;
        }
    }

    public function getEncodedJWT() {
        if (isset($_SERVER["HTTP_AUTHORIZATION"])) {
            $parts = explode(" ", $_SERVER["HTTP_AUTHORIZATION"], 2);
            if (count($parts) === 2) {
                list($type, $data) = $parts;
                if (strcasecmp($type, "Bearer") == 0) {
                    return $data;
                }
            }
        }
    }


    public function verifyCurrentUsersPassword() {
        $CI = & get_instance();

        $where = array('id' => $this->getAdminid());
        $result = $CI->basic_model->get_row('users', $column = array('id', 'password'), $where);

        if (!empty($result)) {
            // check password using PASSWORD_BCRYPT method
            if (password_verify($this->getPassword(), $result->password)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * reset password of admin
     */

    public function reset_users_password() {
        $CI = & get_instance();
        $encry_password = password_hash($this->getPassword(), PASSWORD_BCRYPT);

        $userData = array('password' => $encry_password);
        $result = $CI->basic_model->update_records('users', $userData, $where = array('id' => $this->getAdminid()));
        return $result;
    }

    // Check member and applicant auth login
    public function check_member_auth_login($JWT_Token){
        $CI = & get_instance();

        $CI->load->model('recruitment/Recruitment_applicant_model');
        $result = $CI->Recruitment_applicant_model->auth_applicant_info($this->getAdminEmail());  

        $is_member = false;   
        if (empty($result)) {
            $result = $CI->Recruitment_applicant_model->auth_member_info($this->getAdminEmail());
            $is_member = true;
            if(empty($result)){
                $is_member = false;
                return array(["status"=>false,"error"=>"Login failed"]);
            } else {
                if(empty($result->member_status)){
                    return array("status"=>false,"error"=>"Thanks for your assistance during the Pilot Phase. Your login is now deactivated by admin.");
                }
            }        
        } else {
            if(!empty($result->member_id) && empty($result->member_status)){
                return array("status"=>false,"error"=>"Thanks for your assistance during the Pilot Phase. Your login is now deactivated by admin.");
            }
        }


        $applicant_info = (array) $result;
        $response_data['token'] =  $JWT_Token;
        if($is_member){
            $response_data['member_id'] = $applicant_info['member_id'];
        }else{
            //check the applicant is flagged or not
            $flagged_result = $CI->Recruitment_applicant_model->check_the_applicant_is_flagged($applicant_info['id']);
            if(!empty($flagged_result)){
                return array("status"=>false,"error"=>"Thanks for your assistance during the Pilot Phase. Your login is now deactivated by admin.");
            }
            $response_data['applicant_id'] = $applicant_info['id'];
        }

        # fetching the next available interview details, this is needed due to old way of storing token
        # and validating it. If the next interview details are not found, passing all data points as blank.
        if(!$is_member){
            $int_details = $CI->Recruitment_applicant_model->get_next_interview_details($applicant_info['id']);

            if($int_details) {
                $response_data['task_applicant_id'] = $int_details['task_applicant_id'];
                $response_data["interview_type_id"] = $int_details['interview_type_id'];
                $response_data['task_id'] = strval($int_details['task_id']);
                $response_data['task_name'] = $int_details['task_name'];
                $response_data["interview_type"] = $int_details['interview_type'];
                $response_data['task_start_time'] = $int_details['task_start_time'];
                $response_data['ipad_last_stage'] = $int_details['ipad_last_stage'];
                $response_data["is_signed"] = $int_details['is_signed'];
                $response_data['contract_file_id'] = $int_details['contract_file_id'];
                $response_data['time_zone'] = $int_details['time_zone'];
            }else {
                $response_data['task_applicant_id'] = 0;
                $response_data["interview_type_id"] = 0;
                $response_data["task_id"] = "0";
                $response_data['task_name'] ="";
                $response_data["interview_type"] = "";
                $response_data['task_start_time'] = "";
                $response_data['ipad_last_stage'] = 0;
                $response_data["is_signed"] = 0;
                $response_data['contract_file_id'] = 0;
                $response_data['time_zone'] = "Australia/Melbourne";
            }           
        }
        $response_data['applicant_info'] = $applicant_info; 
        $response = array("token"=> $JWT_Token, "status"=>true,"success"=>"Login successfully","data" => $response_data); 
        return $response;
    }
}
