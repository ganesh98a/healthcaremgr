<?php

/*
 * Filename: Admin.php
 * Desc: Deatils of Admin
 * @author YDT <yourdevelopmentteam.com.au>
 */

namespace AdminClass;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use classRoles as adminRoles;

require_once APPPATH . 'Classes/crm/CrmRole.php';

/*
 * Class: Admin
 * Desc: 2 arrays($admin_email, $admin_phone) Variables and Getter and Setter Methods for Admin
 * Created: 07-08-2018
 */

class Admin extends adminRoles\Roles {

    /**
     * @var adminid
     * @access private
     * @vartype: integer
     */
    private $adminid;

    /**
     * @var companyid
     * @access private
     * @vartype: smallint
     */
    private $companyid;

    /**
     * @var username
     * @access private
     * @vartype: varchar
     */
    private $username;

    /**
     * @var password
     * @access private
     * @vartype: varchar
     */
    private $password;

    /**
     * @var email
     * @access private
     * @vartype: varchar
     */
    private $primary_email;
    private $secondary_emails = array();

    /**
     * @var pin
     * @access private
     * @vartype: varchar
     */
    private $pin;

    /**
     * @var firstname
     * @access private
     * @vartype: varchar
     */
    private $firstname;

    /**
     * @var lastname
     * @access private
     * @vartype: varchar
     */
    private $lastname;

    /**
     * @var phone
     * @access private
     * @vartype: varchar
     */
    private $primary_phone;
    private $secondary_phone = array();

    /**
     * @var position
     * @access private
     * @vartype: varchar
     */
    private $position;

    /**
     * @var department
     * @access private
     * @vartype: varchar
     */
    private $department;

    /**
     * @var status
     * @access private
     * @vartype: tinyint
     */
    private $status;

    /**
     * @var background
     * @access private
     * @vartype: varchar
     */
    private $background;

    /**
     * @var gender
     * @access private
     * @vartype: tinyint
     */
    private $gender;

    private $departmentId;

    /**
     * @array admin_email
     * @access private
     * @vartype: integer|varchar|tinyint
     */
    private $admin_email = array();

    /**
     * @array admin_phone
     * @access private
     * @vartype: integer|varchar|tinyint
     */
    private $admin_phone = array();
    private $token;

    /**
     * @function getDepartmentid
     * @access public
     * @returns $departmentId integer
     * Get department Id
     */
    public function getDepartmentid() {
        return $this->departmentId;
    }

    /**
     * @function setDepartmentid
     * @access public
     * @param $departmentId integer
     * Set department Id
     */
    public function setDepartmentid($adminid) {
        $this->adminid = $departmentId;
    }

    /**
     * @function getAdminid
     * @access public
     * @returns $adminid integer
     * Get Admin Id
     */
    public function getAdminid() {
        return $this->adminid;
    }

    /**
     * @function setAdminid
     * @access public
     * @param $adminid integer
     * Set Admin Id
     */
    public function setAdminid($adminid) {
        $this->adminid = $adminid;
    }

    /**
     * @function getCompanyid
     * @access public
     * @returns $companyid smallint
     * Get Company Id
     */
    public function getCompanyid() {
        return $this->companyid;
    }

    /**
     * @function setCompanyid
     * @access public
     * @param $companyid smallint
     * Set Company Id
     */
    public function setCompanyid($companyid) {
        $this->companyid = $companyid;
    }

    /**
     * @function getUsername
     * @access public
     * @returns $username varchar
     * Get Username
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @function setUsername
     * @access public
     * @param $username varchar
     * Set Username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * @function getPassword
     * @access public
     * @returns $password varchar
     * Get Password
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @function setPassword
     * @access public
     * @param $password varchar
     * Set Password
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * @function getEmail
     * @access public
     * @returns $email varchar
     * Get Email
     */
    public function getPrimaryEmail() {
        return $this->primary_email;
    }

    /**
     * @function setEmail
     * @access public
     * @param $email varchar
     * Set Email
     */
    public function setPrimaryEmail($email) {
        $this->primary_email = $email;
    }

    /**
     * @function getPin
     * @access public
     * @returns $pin varchar
     * Get Pin
     */
    public function getPin() {
        return $this->pin;
    }

    /**
     * @function setPin
     * @access public
     * @param $pin varchar
     * Set Pin
     */
    public function setPin($pin) {
        $this->pin = $pin;
    }

    /**
     * @function getFirstname
     * @access public
     * @returns $firstname varchar
     * Get Firstname
     */
    public function getFirstname() {
        return $this->firstname;
    }

    /**
     * @function setFirstname
     * @access public
     * @param $firstname varchar
     * Set Firstname
     */
    public function setFirstname($firstname) {
        $this->firstname = $firstname;
    }

    /**
     * @function getLastname
     * @access public
     * @returns $lastname varchar
     * Get Lastname
     */
    public function getLastname() {
        return $this->lastname;
    }

    /**
     * @function setLastname
     * @access public
     * @param $lastname varchar
     * Set Lastname
     */
    public function setLastname($lastname) {
        $this->lastname = $lastname;
    }

    /**
     * @function getPhone
     * @access public
     * @returns $phone varchar
     * Get Phone
     */
    public function getPrimaryPhone() {
        return $this->primary_phone;
    }

    /**
     * @function setPhone
     * @access public
     * @param $phone varchar
     * Set Phone
     */
    public function setPrimaryPhone($phone) {
        $this->primary_phone = $phone;
    }

    public function setSecondaryEmails($seconday_email) {
        $this->secondary_emails = $seconday_email;
    }

    public function setSecondaryPhone($secondary_phone) {
        $this->secondary_phones = $secondary_phone;
    }

    /**
     * @function getPosition
     * @access public
     * @returns $position varchar
     * Get Position
     */
    public function getPosition() {
        return $this->position;
    }

    /**
     * @function setPosition
     * @access public
     * @param $position varchar
     * Set Position
     */
    public function setPosition($position) {
        $this->position = $position;
    }

    /**
     * @function getDepartment
     * @access public
     * @returns $department varchar
     * Get Department
     */
    public function getDepartment() {
        return $this->department;
    }

    /**
     * @function setDepartment
     * @access public
     * @param $department varchar
     * Set Department
     */
    public function setDepartment($department) {
        $this->department = $department;
    }

    /**
     * @function getStatus
     * @access public
     * @returns $status tinyint
     * Get Status
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @function setStatus
     * @access public
     * @param $status tinyint
     * Set Status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @function getBackground
     * @access public
     * @returns $background varchar
     * Get Background
     */
    public function getBackground() {
        return $this->background;
    }

    /**
     * @function setBackground
     * @access public
     * @param $background varchar
     * Set Background
     */
    public function setBackground($background) {
        $this->background = $background;
    }

    /**
     * @function getGender
     * @access public
     * @returns $gender tinyint
     * Get Gender
     */
    public function getGender() {
        return $this->gender;
    }

    /**
     * @function setGender
     * @access public
     * @param $gender tinyint
     * Set Gender
     */
    public function setGender($gender) {
        $this->gender = $gender;
    }

    /**
     * @function getAdminEmail
     * @access public
     * @returns $admin_email integer|varchar|tinyint
     * Get AdminEmail
     */
    public function getAdminEmail() {
        return $this->admin_email;
    }

    /**
     * @function setAdminEmail
     * @access public
     * @param $adminEmail integer|varchar|tinyint
     * Set AdminEmail
     */
    public function setAdminEmail($adminEmail) {
        $this->admin_email = $adminEmail;
    }

    /**
     * @function getAdminPhone
     * @access public
     * @returns $admin_phone integer|varchar|tinyint
     * Get AdminPhone
     */
    public function getAdminPhone() {
        return $this->admin_phone;
    }

    /**
     * @function setAdminPhone
     * @access public
     * @param $adminPhone integer|varchar|tinyint
     * Set AdminPhone
     */
    public function setAdminPhone($adminPhone) {
        $this->admin_phone = $adminPhone;
    }

    public function getToken() {
        return $this->token;
    }

    public function setToken($token) {
        $this->token = $token;
    }

    public function genratePin() {
        $pin = mt_rand(100000, 999999);
        $this->setPin($pin);

        return $pin;
    }

    public function createUser() {
        $CI = & get_instance();
        $encry_password = password_hash($this->password, PASSWORD_BCRYPT);

        $user_data = array('firstname' => $this->firstname, 'lastname' => $this->lastname, 'position' => $this->position, 'department' => isset($this->department)?$this->department:'', 'username' => $this->username, 'password' => $encry_password,'created'=>DATE_TIME);

        $adminID = $CI->basic_model->insert_records('member', $user_data, $multiple = FALSE);
        $this->setAdminid($adminID);

        return $adminID;
    }

    public function updateUser() {
        $CI = & get_instance();

        $user_data = array('firstname' => $this->firstname, 'lastname' => $this->lastname, 'position' => $this->position, 'department' => $this->department);

        if (!empty($this->password)) {
            $user_data['password'] = $this->password;
            $user_data['password'] = password_hash($user_data['password'], PASSWORD_BCRYPT);
            $user_data['updated_date'] = DATE_TIME;
        }

        $CI->basic_model->update_records('member', $user_data, $where = array('id' => $this->adminid));

        $this->setAdminid($this->adminid);
        return $this->adminid;
    }

    public function insertPhone() {
        $CI = & get_instance();

        $CI->basic_model->delete_records('member_phone', $where = array('memberId' => $this->adminid));
        if (count($this->secondary_phones) > 0) {
            foreach ($this->secondary_phones as $key => $val) {
                if ($key == 0) {
                    $addional_phone_number[] = array('phone' => $val->name, 'memberId' => $this->adminid, 'primary_phone' => 1, 'created' => DATE_TIME);
                } else {
                    $addional_phone_number[] = array('phone' => $val->name, 'memberId' => $this->adminid, 'primary_phone' => 2, 'created' => DATE_TIME);
                }
            }

            $CI->basic_model->insert_records('member_phone', $addional_phone_number, $multiple = true);
        }
    }

    public function insertEmail() {
        $CI = & get_instance();

        $CI->basic_model->delete_records('member_email', $where = array('memberId' => $this->adminid));
        if (count($this->secondary_emails) > 0) {
            foreach ($this->secondary_emails as $key => $val) {
                if ($key == 0) {
                    $this->setPrimaryEmail($val->name);
                    $addional_email[] = array('email' => $val->name, 'memberId' => $this->adminid, 'primary_email' => 1, 'created' => DATE_TIME);
                } else {
                    $addional_email[] = array('email' => $val->name, 'memberId' => $this->adminid, 'primary_email' => 2, 'created' => DATE_TIME);
                }
            }

            $CI->basic_model->insert_records('member_email', $addional_email, $multiple = true);
        }
    }

    public function insertRoleToAdmin() {
        $CI = & get_instance();
        $pin_access = false;

        if (!empty($this->getRoles())) {
            $val = (object)$this->getRoles();

            if ($val->access) {
                $pin_access = ($val->id == 1 || $val->id == 7 || $pin_access) ? true : false;
                $temp_roles[] = array('roleId' => $val->id, 'adminId' => $this->adminid);
            }

            if ($pin_access) {
                // first genrate pin
                $this->genratePin();
                // assing pin to admin and update pin in database
                $this->updatePinAdmin();
                // send pin using email to admin user
                $this->sendPinToAdmin();
            }

            if (!empty($temp_roles))
                $CI->basic_model->insert_records('admin_role', $temp_roles, $multiple = true);
        }
    }

    public function updateRoleToAdmin() {
        $CI = & get_instance();
        $pin_access = false;
        $temp_roles = array();
        if (!empty($this->getRoles())) {
            foreach ($this->getRoles() as $val) {
                if ((!empty($val->access)) && $val->access == 1) {
                    $pin_access = ($val->id == 1 || $val->id == 7 || $pin_access) ? true : false;
                    $temp_roles[] = array('roleId' => $val->id, 'adminId' => $this->adminid);
                }
            }

            // check have already pin
            $check_pin = $CI->basic_model->get_row('member', array('pin'), $where = array('id' => $this->adminid));

            if (empty($check_pin->pin) && $pin_access) {
                // first genrate pin
                $this->genratePin();
                // assing pin to admin and update pin in database
                $this->updatePinAdmin();
                // send pin using email to admin user
                $this->sendPinToAdmin();
            }

            $CI->basic_model->delete_records('admin_role', $where = array('adminId' => $this->adminid));
            if (!empty($temp_roles))
                $CI->basic_model->insert_records('admin_role', $temp_roles, $multiple = true);
        }
    }

    public function updatePinAdmin() {
        $CI = & get_instance();
        $encrypted_pin = password_hash($this->pin, PASSWORD_BCRYPT);
        $CI->basic_model->update_records('member', array('pin' => $encrypted_pin,'updated_date' => DATE_TIME), $where = array('id' => $this->adminid));
    }

    public function sendPinToAdmin() {
        $userdata = array('email' => $this->primary_email, 'pin' => $this->pin, 'fullname' => $this->firstname.' '.$this->lastname);
        send_pin_to_admin($userdata);
    }

    public function get_admin_details() {
        $CI = & get_instance();
        $CI->load->model('Admin_model');

        return $CI->Admin_model->get_admin_details($this->adminid);
    }

    public function get_admin_phone_number() {
        $CI = & get_instance();
        $CI->load->model('Admin_model');

        return $CI->Admin_model->get_admin_phone_number($this->adminid);
    }

    public function get_admin_email() {
        $CI = & get_instance();
        $CI->load->model('Admin_model');

        return $CI->Admin_model->get_admin_email($this->adminid);
    }

    public function send_welcome_mail() {
        $CI = & get_instance();
        $userData = array('email' => $this->primary_email, 'username' => $this->username, 'password' => $this->password, 'fullname' => $this->firstname . ' ' . $this->lastname);
        welcome_mail_admin($userData);
    }

    public function checkExistingEmail() {
        $CI = & get_instance();
        return $CI->admin_model->check_dublicate_email($this->primary_email, $this->adminid);
    }

    function sendUpdatePasswordRecoveryEmail() {
        $CI = & get_instance();

        $userdata = array(
            'email' => $this->primary_email,
            'fullname' => $this->firstname . ' ' . $this->lastname,
            'url' => $CI->config->item('server_url') . 'admin/verify_email_update/' . $this->token,
        );

        send_Update_password_recovery_email($userdata);
    }

    function UpdatePrimaryEmail() {
        $CI = & get_instance();

        $where = array('memberId' => $this->getAdminid(), 'primary_email' => 1);

        $CI->basic_model->update_records('member_email', array('email' => $this->primary_email,'updated' => DATE_TIME), $where);

        $CI->basic_model->update_records('member', array('token' => '','updated_date' => DATE_TIME), array('id' => $this->getAdminid()));
    }

    function insertStaffDetail() {
        $CI = & get_instance();
      $staff_detail = array('admin_id' => $this->adminid,'status'=>1 /*'departmentId' => $this->getDepartmentid()*/);
        $CI->basic_model->insert_records('crm_staff', $staff_detail, $multiple = false);
    }


    // Notification Mail for assigned user when add contract or renew Contract
    public function send_notification_mail_for_assigned_user($participantTd,$msg,$participant_email) {
      $CI = & get_instance();
      $participantData = $CI->basic_model->get_row('crm_participant', array('firstname','lastname'), array('id' => $participantTd));
      $fullmsg =  'Participant ('.$participantData->firstname.' '.$participantData->lastname.') has '.$msg;
      $where = array('memberId' => $this->getAdminid(), 'primary_email' => 1);
      $memeberEmailData  = $CI->basic_model->get_row('member_email', array('email' => $this->primary_email), $where);
      $memeberProfileData  = $CI->basic_model->get_row('member', array('username','firstname','lastname'),  array('id' => $this->getAdminid()));
      $userData = array('email' => $memeberEmailData->email, 'username' => $memeberProfileData->username, 'msg' => $fullmsg, 'fullname' => $memeberProfileData->firstname . ' ' . $memeberProfileData->lastname,'participant_email'=>$participant_email);
      notification_mail_for_user($userData,$cc_email_address = null);
    }

}
