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

require_once APPPATH . 'Classes/admin/role.php';

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
     * @var timezone
     * @access private
     * @vartype: varchar
     */
    private $timezone;

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
    private $user_type;
    private $is_locked;
    private $date_unlocked;
    private $uuid_user_type;

    /**
     * @var pinType
     * @access private
     * @vartype: int
     */
    private $pinType;

    private $avatar = "";    

    

    /**
     * @var uuid
     * @access private
     * @vartype: integer
     */
    private $uuid;

    /**
     * @function getUuid
     * @access public
     * @returns $uuid integer
     * Get Uuid
     */
    public function getUuid() {
        return $this->uuid;
    }

    /**
     * @function setUuid
     * @access public
     * @param $uuid integer
     * Set Uuid
     */
    public function setUuid($uuid) {
        $this->uuid = $uuid;
    }
    /**
     * @var member_id
     * @access private
     * @vartype: integer
     */
    private $member_id;
    
    /**
     * @function getMemberId
     * @access public
     * @returns $member_id integer
     * Get Member id
     */
    public function getMemberId() {
        return $this->member_id;
    }

    /**
     * @function setMemberId
     * @access public
     * @param $member_id integer
     * Set Member id
     */
    public function setMemberId($member_id) {
        $this->member_id = $member_id;
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
     * Get is_locked
     */
    public function getIslocked() {
        return $this->is_locked;
    }

    /**
     * Set is_locked
     */
    public function setIslocked($is_locked) {
        $this->is_locked = $is_locked;
    }

    /**
     * Get date_unlocked
     */
    public function getDateUnlocked() {
        return $this->date_unlocked;
    }

    /**
     * Set access_role_id
     */
    public function setAccessRole($access_role_id) {
        $this->access_role_id = $access_role_id;
    }

    /**
     * Get access_role_id
     */
    public function getAccessRole() {
        return $this->access_role_id;
    }

    /**
     * Set date_unlocked
     */
    public function setDateUnlocked($date_unlocked) {
        $this->date_unlocked = $date_unlocked;
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
     * @function getTimezone
     * @access public
     * @returns $timezone varchar
     * Get Timezone
     */
    public function geTimzone() {
        return $this->timezone;
    }

    /**
     * @function setTimezone
     * @access public
     * @param $timezone varchar
     * Set Timezone
     */
    public function setTimezone($timezone) {
        $this->timezone = $timezone;
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

    function setUser_type($user_type) {
        $this->user_type = $user_type;
    }

    function getUser_type() {
        return $this->user_type;
    }

    function setUuid_user_type($uuid_user_type) {
        $this->uuid_user_type = $uuid_user_type;
    }

    function getUuid_user_type() {
        return $this->uuid_user_type;
    }

    public function genratePin() {
        $pin = mt_rand(100000, 999999);
        $this->setPin($pin);

        return $pin;
    }

    public function createUser() {
        $CI = &get_instance();
        $encry_password = password_hash($this->password, PASSWORD_BCRYPT);

        $user_data = array(
            "user_type" => $this->user_type,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'position' => $this->position,
            'department' => $this->department,
            'created' => DATE_TIME,
            'timezone' => $this->timezone,
            'profile_pic' => $this->avatar,
            'username' => $this->username,
            'access_role_id' => $this->access_role_id,
            'status' => $this->getStatus(),
        );
        $createUserUuid = $CI->basic_model->insert_records('users', ["username"=>$this->username,"status"=>1,"password_token"=>$this->token, "user_type"=>$this->getUuid_user_type()], $multiple = FALSE);

        $user_data['uuid'] = $createUserUuid;

        $adminID = $CI->basic_model->insert_records('member', $user_data, $multiple = FALSE);
        $this->setAdminid($createUserUuid);
        $this->setMemberId($adminID);
        return $adminID;
    }

    public function get_internal_staff_department_id() {
        $CI = &get_instance();
        $res = $CI->basic_model->get_row('department', ['id'], ['short_code' => "internal_staff", "archive" => 0]);

        return $res->id ?? 0;
    }

    public function updateUser() {
        $CI = &get_instance();

        $user_data = array('firstname' => $this->firstname, 'lastname' => $this->lastname, 'position' => $this->position, 'department' => $this->department, 'timezone' => $this->timezone, 'is_locked' => $this->is_locked, 'date_unlocked' => $this->date_unlocked, 'access_role_id' => $this->access_role_id, 'profile_pic' => $this->avatar, 'username' => $this->username);

        if (!empty($this->password)) {
            $user_data['password'] = $this->password;
            $user_data['password'] = password_hash($user_data['password'], PASSWORD_BCRYPT);
        }
        $CI->basic_model->update_records('users', ["username"=>$this->username], $where = array('id' => $this->uuid));
        
        $CI->basic_model->update_records('member', $user_data, $where = array('id' => $this->member_id, 'uuid'=>$this->uuid));

        $this->setAdminid($this->adminid);
        return $this->adminid;
    }

    public function insertPhone() {
        $CI = &get_instance();

        $CI->basic_model->delete_records('member_phone', $where = array('memberId' => $this->member_id));
        if (count($this->secondary_phones) > 0) {
            foreach ($this->secondary_phones as $key => $val) {
                if ($key == 0) {
                    $addional_phone_number[] = array('phone' => $val->name, 'memberId' => $this->member_id, 'primary_phone' => 1, 'created' => DATE_TIME);
                } else {
                    $addional_phone_number[] = array('phone' => $val->name, 'memberId' => $this->member_id, 'primary_phone' => 2, 'created' => DATE_TIME);
                }
            }

            $CI->basic_model->insert_records('member_phone', $addional_phone_number, $multiple = true);
        }
    }

    public function insertEmail() {
        $CI = &get_instance();

        $CI->basic_model->delete_records('member_email', $where = array('memberId' => $this->member_id));
        if (count($this->secondary_emails) > 0) {
            foreach ($this->secondary_emails as $key => $val) {
                if ($key == 0) {
                    $this->setPrimaryEmail($val->name);
                    $addional_email[] = array('email' => $val->name, 'memberId' => $this->member_id, 'primary_email' => 1, 'created' => DATE_TIME);
                } else {
                    $addional_email[] = array('email' => $val->name, 'memberId' => $this->member_id, 'primary_email' => 2, 'created' => DATE_TIME);
                }
            }

            $CI->basic_model->insert_records('member_email', $addional_email, $multiple = true);
        }
    }

    public function insertRoleToAdmin() {
        $CI = &get_instance();
        $pin_access = false;

        $recruiter_permission = false;
        $recruiter_admin_permission = false;

        if (!empty($this->getRoles())) {
            foreach ($this->getRoles() as $val) {
                if ((!empty($val->access)) && $val->access == 1) {
                    $pin_access = ($val->id == 1 || $val->id == 7 || $pin_access) ? true : false;
                    $temp_roles[] = array('roleId' => $val->id, 'adminId' => $this->adminid);
                }
                if ((!empty($val->access)) && $val->access == 1) {
                    if ($val->id == 11 || $val->id == 12) {
                        $temp_crm_roles[] = array('admin_id' => $this->adminid);
                        $department_allocation[] = array('admin_id' => $this->adminid, 'allocated_department' => $this->department, 'status' => 1);
                    }

                    if ($val->id == 10) {
                        $recruiter_permission = true;
                    } elseif ($val->id == 14) {
                        $recruiter_admin_permission = true;
                    }
                }
            }


            if (!empty($temp_roles)) {
                $CI->basic_model->insert_records('admin_role', $temp_roles, $multiple = true);
            }

            $this->addUpdateAsRecruiter($recruiter_admin_permission, $recruiter_permission);
        }
    }

    public function updateRoleToAdmin() {
        $CI = &get_instance();
        $pin_access = false;

        $list_roles = $CI->basic_model->get_record_where('role', ['id', 'role_key'], '');
        $list_roles = array_column($list_roles, 'role_key', 'id');

        $crm_permission = false;
        $crm_admin_permission = false;
        $recruiter_permission = false;
        $recruiter_admin_permission = false;
        $finance_permission = false;
        $finance_admin_permission = false;

        if (!empty($this->getRoles())) {
            foreach ($this->getRoles() as $val) {
                if ((!empty($val->access)) && $val->access == 1) {
                    $pin_access = ($list_roles[$val->id] == 'admin' || $list_roles[$val->id] == 'fms' || $pin_access) ? true : false;
                    $temp_roles[] = array('roleId' => $val->id, 'adminId' => $this->adminid);
                }

                if ((!empty($val->access)) && $val->access == 1) {
                    if ($list_roles[$val->id] == 'crm' || $list_roles[$val->id] == 'crm_admin') {
                        $temp_crm_roles[] = array('admin_id' => $this->adminid);
                        $department_allocation[] = array('admin_id' => $this->adminid, 'allocated_department' => $this->department, 'status' => 1);
                    }

                    if ($list_roles[$val->id] == 'crm') {
                        $crm_permission = true;
                    } elseif ($list_roles[$val->id] == 'crm_admin') {
                        $crm_admin_permission = true;
                    }

                    if ($list_roles[$val->id] == 'recruitment') {
                        $recruiter_permission = true;
                    } elseif ($list_roles[$val->id] == 'recruitment_admin') {
                        $recruiter_admin_permission = true;
                    }

                    if ($list_roles[$val->id] == 'finance') {
                        $finance_permission = true;
                    } elseif ($list_roles[$val->id] == 'finance_admin') {
                        $finance_admin_permission = true;
                    }
                }
            }

            $CI->basic_model->delete_records('admin_role', $where = array('adminId' => $this->adminid));

            // check user get any new permissions
            if (!empty($temp_roles)) {
                $CI->basic_model->insert_records('admin_role', $temp_roles, $multiple = true);
            }
            $this->addUpdateAsCrm($crm_permission, $crm_admin_permission);
            $this->addUpdateAsRecruiter($recruiter_admin_permission, $recruiter_permission);
            $this->addUpdateAsFinaceUser($finance_admin_permission, $finance_permission);
        }
    }

    public function addUpdateAsCrm($crm_permission, $crm_admin_permission) {
        $CI = &get_instance();
        // add data in recruitment staff table
        if ($crm_permission || $crm_admin_permission) {

            $crm_data = ['admin_id' => $this->getAdminid(), 'archive' => 0, 'status' => 1];

            // check already recruiter staff or admin
            $its_already_staff = $CI->basic_model->get_row('crm_staff', ['admin_id', 'id', 'its_crm_admin'], ['admin_id' => $this->getAdminid()]);

            if ($crm_admin_permission) {
                $crm_data['approval_permission'] = 1;
                $crm_data['its_crm_admin'] = 1;
            } else {
                $crm_data['approval_permission'] = 0;
                $crm_data['its_crm_admin'] = 0;
            }

            if (!empty($its_already_staff)) {
                $where = ['id' => $its_already_staff->id];
                $CI->basic_model->update_records('crm_staff', $crm_data, $where);
            } else {
                $CI->basic_model->insert_records('crm_staff', $crm_data);
            }
        } else {
            $where = ['admin_id' => $this->getAdminid()];
            $CI->basic_model->update_records('crm_staff', ['status' => 0, 'archive' => 1, 'its_crm_admin' => 0, 'approval_permission' => 0], $where);
        }
    }

    public function addUpdateAsRecruiter($recruiter_admin_permission, $recruiter_permission) {
        $CI = &get_instance();

        // add data in recruitment staff table
        if ($recruiter_admin_permission || $recruiter_permission) {

            $recruiter_data = ['adminId' => $this->getAdminid(), 'archive' => 0];

            // check already recruiter staff or admin
            $its_already_staff = $CI->basic_model->get_row('recruitment_staff', ['adminId', 'id'], ['adminId' => $this->getAdminid()]);

            if ($recruiter_admin_permission) {
                $recruiter_data['approval_permission'] = 1;
                $recruiter_data['its_recruitment_admin'] = 1;
                $recruiter_data['status'] = 1;
            } 
            if ($recruiter_permission && !$recruiter_admin_permission) {

                $recruiter_data['its_recruitment_admin'] = 0;
            }


            if (!empty($its_already_staff)) {
                $recruiter_data['approval_permission'] = 1;
                $where = ['id' => $its_already_staff->id];
                $CI->basic_model->update_records('recruitment_staff', $recruiter_data, $where);
            } else {
                if ($recruiter_permission && !$recruiter_admin_permission) {
                    $recruiter_data['approval_permission'] = 1;
                    $recruiter_data['status'] = 0;
                }

                $recruiter_data['created'] = DATE_TIME;
                $CI->basic_model->insert_records('recruitment_staff', $recruiter_data);
            }
        } else {
            $where = ['adminId' => $this->getAdminid()];
            $CI->basic_model->update_records('recruitment_staff', ['archive' => 1], $where);
        }
    }

    public function addUpdateAsFinaceUser($finance_admin_permission, $finance_permission) {
        $CI = &get_instance();

        // add data in recruitment staff table
        if ($finance_permission && !$finance_admin_permission) {

            $recruiter_data = ['adminId' => $this->getAdminid(), 'status' => 0, 'archive' => 0, 'approval_permission' => 0, 'userId' => null];

            // check already recruiter staff or admin
            $its_already_staff = $CI->basic_model->get_row('finance_staff', ['adminId', 'id'], ['adminId' => $this->getAdminid(), 'archive' => 0]);

            if (!$its_already_staff) {
                $CI->basic_model->insert_records('finance_staff', $recruiter_data);
            }
        } else {
            $where = ['adminId' => $this->getAdminid()];
            $CI->basic_model->update_records('finance_staff', ['archive' => 1], $where);
        }
    }

    public function updatePinAdmin() {
        $CI = &get_instance();
        $encrypted_pin = password_hash($this->pin, PASSWORD_BCRYPT);
        $CI->basic_model->update_records('member', array('pin' => $encrypted_pin), $where = array('uuid' => $this->adminid));
    }

    public function sendPinToAdmin() {
        $userdata = array('email' => $this->primary_email, 'pin' => $this->pin, 'fullname' => $this->firstname . ' ' . $this->lastname);
        send_pin_to_admin($userdata);
    }

    public function get_admin_details() {
        $CI = &get_instance();
        $CI->load->model('Admin_model');

        return $CI->Admin_model->get_admin_details($this->adminid);
    }

    public function get_admin_phone_number() {
        $CI = &get_instance();
        $CI->load->model('Admin_model');

        return $CI->Admin_model->get_admin_phone_number($this->adminid);
    }

    public function get_admin_email() {
        $CI = &get_instance();
        $CI->load->model('Admin_model');

        return $CI->Admin_model->get_admin_email($this->adminid);
    }

    public function send_welcome_mail() {
        $CI = &get_instance();
        $userData = array('email' => $this->primary_email, 'username' => $this->username, 'password' => $this->password, 'fullname' => $this->firstname . ' ' . $this->lastname, 'id' => $this->adminid, 'token' => $this->token, 'type'=>'create');
        welcome_mail_admin($userData);
    }

    public function checkExistingEmail() {
        $CI = &get_instance();
        return $CI->admin_model->check_dublicate_email($this->primary_email, $this->adminid);
    }

    function sendUpdatePasswordRecoveryEmail() {
        $CI = &get_instance();

        $userdata = array(
            'email' => $this->primary_email,
            'fullname' => $this->firstname . ' ' . $this->lastname,
            'url' => $CI->config->item('server_url') . 'admin/verify_email_update/' . $this->token,
        );

        send_Update_password_recovery_email($userdata);
    }

    function UpdatePrimaryEmail() {
        $CI = &get_instance();

        $where = array('memberId' => $this->getAdminid(), 'primary_email' => 1);

        $CI->basic_model->update_records('member_email', array('email' => $this->primary_email, 'updated' => DATE_TIME), $where);

        $CI->basic_model->update_records('users', array('password_token' => '', 'updated_at' => DATE_TIME, 'username' => $this->primary_email ), array('id' => $this->getAdminid()));
    }

    /**
     * @function setPinType
     * @access public
     * @param $pin int
     * Set PinType
     */
    public function setPinType($pinType) {
        $this->pinType = (int) $pinType;
    }

    /**
     * @function getPinType
     * @access public
     * @returns $pinType int
     * Get PinType
     */
    public function getPinType() {
        return (int) $this->pinType;
    }

    function sendResetPinMailToAdmin() {
        $userdata = array('email' => $this->primary_email, 'pin' => $this->pin, 'fullname' => $this->firstname . ' ' . $this->lastname, 'token' => $this->token, 'id' => $this->getAdminid());
        send_reset_pin_mail_to_admin($userdata);
    }


    /**
     * Get the value of avatar
     */ 
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set the value of avatar
     *
     * @return  self
     */ 
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get the value of avatar
     */ 
    public function getBusinessUnit()
    {
        return $this->business_unit;
    }

    /**
     * Set the value of avatar
     *
     * @return  self
     */ 
    public function setBusinessUnit($business_unit)
    {
        $this->business_unit = $business_unit;

        return $this;
    }

    public function updateProfilePic() {
        $CI = &get_instance();
        $user_data = array('profile_pic' => $this->avatar);
        $CI->basic_model->update_records('member', $user_data, array('id' => $this->adminid));
        $this->setAdminid($this->adminid);
        return $this->adminid;
    }

    public function updateBusinessUnit() {
        $CI = &get_instance();       
        $CI->basic_model->update_records('user_business_unit', array('bu_id' => $this->business_unit), ['user_id' => $this->adminid]);
    }
}
