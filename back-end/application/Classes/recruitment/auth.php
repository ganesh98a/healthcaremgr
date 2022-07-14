<?php

/**
 *  Class name : Auth
 *  Create date : 18-07-2018,
 *  author : Corner stone solution
 *  Description : this class used for set cookie, check authentication, and set session
 * 
 */

namespace Admin\Auth;

include APPPATH . 'Classes/admin/admin.php';
include APPPATH . 'Classes/admin/jwt_helper.php';

use AdminClass;

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
     * check username and password authentication
     */

    public function check_auth() {
        $CI = & get_instance();
        $Obj_JWT = new \JWT();

        $where = array('username' => $this->getUsername(), 'archive' => 0);

        // using query check username
        $result = $CI->basic_model->get_row('admin', $column = array('id','concat(firstname," ",lastname) as full_name', 'password', 'gender', 'status'), $where);

        if (!empty($result)) {

            // check password using PASSWORD_BCRYPT method
            if (password_verify($this->getPassword(), $result->password)) {

                // check user active or not 
                if ($result->status) {
                    //set token
                    $this->setAdminid($result->id);

                    $token = array(DATE_TIME . $result->id);
                    $JWT_Token = $Obj_JWT->encode($token, JWT_SECRET_KEY);

                    $this->setOcsToken($JWT_Token);
                    $this->setAdminLogin();

                    $response = array('token' => $JWT_Token, 'fullname' => $result->full_name, 'status' => true, 'success' => system_msgs('success_login'));
                
                } else {
                    $response = array('status' => false, 'error' => system_msgs('account_not_active'));
                }
            } else {
                $response = array('status' => false, 'error' => system_msgs('wrong_username_password'));
            }
        } else {
            $response = array('status' => false, 'error' => system_msgs('wrong_username_password'));
        }

        return $response;
    }

    /*
     *  here verify reset password token
     */

    public function verify_token() {
        $CI = & get_instance();
        $where = array('id' => $this->getAdminid(), 'token' => $this->getOcsToken());
        $result = $CI->basic_model->get_record_where('admin', array('firstname', 'lastname'), $where);
        return $result;
    }

    /*
     * reset password of admin
     */

    public function reset_password() {
        $CI = & get_instance();
        $encry_password = password_hash($this->getPassword(), PASSWORD_BCRYPT);

        $userData = array('password' => $encry_password, 'token' => '');
        $result = $CI->basic_model->update_records('admin', $userData, $where = array('id' => $this->getAdminid()));
        return $result;
    }

    /*
     * here insert entry of login
     */

    public function setAdminLogin() {
        $CI = & get_instance();

        $response = $CI->basic_model->get_row('admin_login', $columns = array('updated', 'adminId', 'token'), $where = array('adminId' => $this->getAdminid()));
        if (!empty($response)) {
            $CI->basic_model->update_records('admin_login', $columns = array('updated' => DATE_TIME, 'pin' => '', 'token' => $this->ocs_token, 'ip_address' => get_client_ip_server() ), $where = array('adminId' => $this->getAdminid()));
        } else {
            $CI->basic_model->insert_records('admin_login', $data = array('token' => $this->ocs_token, 'adminId' => $this->getAdminid(), 'updated' => DATE_TIME, 'pin' => '', 'ip_address' => get_client_ip_server()), $multiple = FALSE);
        }
    }

    /*
     * unset user login token in db
     */

    public function unsetAdminLogin($token = false) {
        $CI = & get_instance();

        // check optional paramter and private ocs_token
        $token = (!empty($token)) ? $token : $this->ocs_token;

        $CI->basic_model->delete_records('admin_login', $where = array('token' => $token));
    }

    public function checkCurrentPin() {
        $CI = & get_instance();

        $result = $CI->basic_model->get_row('admin', $column = array('pin'), $where = array('id' => $this->getAdminid()));
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

        $pin = $this->checkCurrentPin();

        if (!empty($pin)) {
            $token = array(DATE_TIME . $pin);
            $JWT_Token = $Obj_JWT->encode($token, JWT_SECRET_KEY);

            // update token
            $where = array('adminId' => $this->getAdminid());
            $CI->basic_model->update_records('admin_login', $columns = array('pin' => $JWT_Token), $where);
            $response = array('token' => $JWT_Token, 'status' => true, 'success' => system_msgs('token_verfied'));
        } else {
            $response = array('status' => false, 'error' => system_msgs('encorrect_pin'));
        }

        return $response;
    }

    public function verifyCurrentPassword() {
        $CI = & get_instance();

        $where = array('id' => $this->getAdminid());
        $result = $CI->basic_model->get_row('admin', $column = array('id', 'password'), $where);

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

        $where = array('id' => $this->getAdminid(), 'token' => $this->getToken());
        return $result = $CI->basic_model->get_row('admin', $column = array('id', 'token'), $where);
    }

}
