<?php

namespace MemberClass; // if namespacce is not made in Classes(Member.php,MemberAddress.php [files]) folder, then it will give error of Cannot redeclare class 

/*
 * Filename: Member.php
 * Desc: The member file defines a module which checks the details and updates of members, upcoming shifts, create new availability, cases(FMS), create cases. 
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined("BASEPATH"))
    exit("No direct script access allowed");

/*
 * Class: Member
 * Desc: The Member Class is a class which holds infomation about members like memberid, firstname, lastname etc.
 * The class includes variables and some methods. The methods are used to get and store information of members.
 * The visibility mode of this variables are private and the methods are made public.
 * Created: 01-08-2018
 */

class Member {

    public $CI;

    function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->model('Member_model');
        $this->CI->load->model('Basic_model');
    }

    /**
     * @var memberid
     * @access private
     * @vartype: int
     */
    private $memberid;

    /**
     * @var companyid 
     * @access private
     * @vartype: smallint
     */
    private $companyid;

    /**
     * @var firstname 
     * @access private
     * @vartype: varchar
     */
    private $firstname;

    /**
     * @var middlename
     * @access private
     * @vartype: varchar
     */
    private $middlename;

    /**
     * @var lastname
     * @access private
     * @vartype: varchar
     */
    private $lastname;

    /**
     * @var preferredname
     * @access private
     * @vartype: varchar
     */
    private $preferredname;

    /**
     * @var pin
     * @access private
     * @vartype: varchar
     */
    private $pin;

    /**
     * @var profile_image
     * @access private
     * @vartype: varchar
     */
    private $profile_image;

    /**
     * @var usertype
     * @access private
     * @vartype: tinyint
     */
    private $usertype;

    /**
     * @var deviceid
     * @access private
     * @vartype: varchar
     */
    private $deviceid;

    /**
     * @var status
     * @access private
     * @vartype: tinyint
     */
    private $status;

    /**
     * @var status
     * @access private
     * @vartype: varchar
     */
    private $prefer_contact;

    /**
     * @arrayname member_contact
     * @access private
     * @array int|varchar|char
     */
    private $member_contact = array();

    /**
     * @arrayname member_place
     * @access private
     * @array mediumint
     */
    private $member_place = array();

    /**
     * @arrayname member_email
     * @access private
     * @array int|varchar|char
     */
    private $member_email = array();

    /**
     * @arrayname member_phone
     * @access private
     * @array int|varchar|char
     */
    private $member_phone = array();

    /**
     * @arrayname member_activity
     * @access private
     * @array int|varchar|char
     */
    private $member_actvity = array();

    /**
     * @var dob
     * @access private
     * @vartype: varchar
     */
    private $dob;

    /**
     * var push_notification_enable
     * @access private
     * @vartype: tinyint
     */
    private $push_notification_enable;

    /**
     * @var failed_login
     * @access private
     * @vartype: tinyint
     */
    private $failed_login;

    /**
     * @var reset_password
     * @access private
     * @vartype: tinyint
     */
    private $reset_password;

    /**
     * @var created
     * @access private
     * @vartype: varchar
     */
    private $created;

    /**
     * @function getMemberid
     * @access public
     * @return $memberid integer
     * Get Member Id
     */
    public function getMemberid() {
        return $this->memberid;
    }

    /**
     * @function archive
     * @param $archive integer 
     * @access public
     * Set archive
     */
    public function setMemberArchive($archive) {
        $this->archive = $archive;
    }

    /**
     * @function archive
     * @access public
     * @return $archive integer
     * Get archive
     */
    public function getMemberArchive() {
        return $this->archive;
    }

    /**
     * @function setMemberid
     * @param $memberid integer 
     * @access public
     * Set Member Id
     */
    public function setMemberid($memberid) {
        $this->memberid = $memberid;
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
     * @param $companyid integer 
     * Set Company Id
     */
    public function setCompanyid($companyid) {
        $this->companyid = $companyid;
    }

    /**
     * @function getFirstname
     * @access public
     * returns $firstname varchar
     * Get First Name
     */
    public function getFirstname() {
        return $this->firstname;
    }

    /**
     * @function setFirstname
     * @access public
     * @param $firstname varchar
     * Set First name
     */
    public function setFirstname($firstname) {
        $this->firstname = $firstname;
    }

    /**
     * @function getMiddlename 
     * @access public 
     * returns $middlename varchar
     * Get Middle Name
     */
    public function getMiddlename() {
        return $this->middlename;
    }

    /**
     * @function setMiddlename
     * @access public
     * @param $middlename varchar
     * Set Middle Name
     */
    public function setMiddlename($middlename) {
        $this->middlename = $middlename;
    }

    /**
     * @function getLastname
     * @access public
     * returns $lastname varchar
     * Get Last Name
     */
    public function getLastname() {
        return $this->lastname;
    }

    /**
     * @function setLastname
     * @access public
     * @param $lastname varchar
     * Set Last Name
     */
    public function setLastname($lastname) {
        $this->lastname = $lastname;
    }

    /**
     * @function getPreferredName
     * @access public
     * returns $PreferredName varchar
     * Get Preferred Name
     */
    public function getPreferredName() {
        return $this->PreferredName;
    }

    /**
     * @function setPreferredName
     * @access public
     * @param $Preferredname varchar 
     * Set Preferred Name
     */
    public function setPreferredName($PreferredName) {
        $this->PreferredName = $PreferredName;
    }

    /**
     * @function getPin
     * @access public
     * returns $pin varchar
     * Get Pin
     */
    public function getPin() {
        return $this->pin;
    }

    /**
     * @function setPin
     * @access public
     * @param $Preferredname varchar 
     * Set Preferred Name
     */
    public function setPin($pin) {
        $this->pin = $pin;
    }

    /**
     * @function getProfileImage
     * @access public
     * returns $profile_image varchar
     * Get Pin
     */
    public function getProfileImage() {
        return $this->profile_image;
    }

    /**
     * @function setProfileImage
     * @access public
     * @param $ProfileImage varchar
     * Set Profile Image
     */
    public function setProfileImage($profileImage) {
        $this->profile_image = $profileImage;
    }

    /**
     * @function getUserType
     * @access public
     * returns $userType tinyint
     * Gets User Type
     */
    public function getUserType() {
        return $this->userType;
    }

    /**
     * @function setUserType
     * @access public
     * @param $UserType varchar 
     * Set User Type
     */
    public function setUserType($userType) {
        $this->userType = $userType;
    }

    /**
     * @function getDeviceId
     * @access public
     * returns $devideId varchar
     * Get Device Id
     */
    public function getDeviceId() {
        return $this->deviceId;
    }

    /**
     * @function setDeviceId
     * @access public
     * @param $DeviceId varchar 
     * Set User Type
     */
    public function setDeviceId($deviceId) {
        $this->deviceId = $deviceId;
    }

    /**
     * @function getStatus
     * @access public
     * returns $status tinyint
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
     * @function getPreferContact
     * @access public
     * returns $prefer_contact varchar
     * Get Prefer Contact
     */
    public function getPreferContact() {
        return $this->prefer_contact;
    }

    /**
     * @function setStatus
     * @access public
     * @param $PreferContact varchar
     * Set Prefer Contact
     */
    public function setPreferContact($preferContact) {
        $this->prefer_contact = $preferContact;
    }

    /**
     * @function getMemberContact
     * @access public
     * returns  $preferContact integer|char|varchar
     * Get Prefer Contact
     */
    public function getMemberContact() {
        return $this->member_contact;
    }

    /**
     * @function setMemberContact
     * @access public
     * @param $MemberContact integer|char|varchar
     * Set Member Contact
     */
    public function setMemberContact($MemberContact) {
        $this->member_contact = $MemberContact;
    }

    /**
     * @function getMemberEmail
     * @access public
     * returns $member_email integer|char|varchar|tinyint
     * Get Member Email
     */
    public function getMemberEmail() {
        return $this->member_email;
    }

    /**
     * @function setMemberEmail
     * @access public
     * @param $MemberEmail integer|char|varchar|tinyint
     * Set Member Email
     */
    public function setMemberEmail($MemberEmail) {
        $this->member_email = $MemberEmail;
    }

    /**
     * @function getMemberPhone
     * @access public
     * returns $member_phone integer|char|tinyint
     * Get Member Phone
     */
    public function getMemberPhone() {
        return $this->member_phone;
    }

    /**
     * @function setMemberPhone
     * @access public
     * @param $MemberPhone integer|char|tinyint
     * Set Member Phone
     */
    public function setMemberPhone($MemberPhone) {
        $this->member_phone = $MemberPhone;
    }

    /**
     * @function getMemberPlace
     * @access public
     * returns $member_place mediumint
     * Get Member Place
     */
    public function getMemberPlace() {
        return $this->member_place;
    }

    /**
     * @function setMemberPlace
     * @access public
     * @param $MemberPlace mediumint
     * Set Member Place
     */
    public function setMemberPlace($MemberPlace) {
        $this->member_place = $MemberPlace;
    }

    /**
     * @function getMemberActivity
     * @access public
     * returns $member_activity integer|mediumint|tinyint 
     * Get Member Place
     */
    public function getMemberActivity() {
        return $this->member_activity;
    }

    /**
     * @function setMemberActivity
     * @access public
     * @param $MemberPlace integer|mediumint|tinyint
     * Set Member Place
     */
    public function setMemberActivity($MemberActivity) {
        $this->member_activity = $MemberActivity;
    }

    /**
     * @function getDob
     * @access public
     * returns $dob varchar
     * Get Dob
     */
    public function getDob() {
        return $this->dob;
    }

    /**
     * @function setDob
     * @access public
     * @param $Dob varchar
     * Set Dob
     */
    public function setDob($dob) {
        $this->dob = $dob;
    }

    /**
     * @function getPushNotificationEnable
     * @access public
     * returns $push_notification_enable tinyint
     * Get PushNotificationEnable
     */
    public function getPushNotificationEnable() {
        return $this->push_notification_enable;
    }

    /**
     * @function setPushNotificationEnable
     * @access public
     * @param $PushNotificationEnable tinyint
     * Set Push Notification Enable
     */
    public function setPushNotificationEnable($pushNotificationEnable) {
        $this->push_notification_enable = $pushNotificationEnable;
    }

    /**
     * @function getFailedLogin
     * @access public
     * returns $failed_login tinyint
     * Get getFailedLogin
     */
    public function getFailedLogin() {
        return $this->failed_login;
    }

    /**
     * @function setFailedLogin
     * @access public
     * @param $FailedLogin tinyint
     * Set Failed Login
     */
    public function setFailedLogin($failedLogin) {
        $this->failed_login = $failedLogin;
    }

    /**
     * @function getResetPassword
     * @access public
     * returns $reset_password tinyint
     * Get Reset Password
     */
    public function getResetPassword() {
        return $this->reset_password;
    }

    /**
     * @function setResetPassword
     * @access public
     * @param $ResetPassword tinyint
     * Set Reset Password
     */
    public function setResetPassword($resetPassword) {
        $this->reset_password = $resetPassword;
    }

    /**
     * @function getCreated
     * @access public
     * returns $created varchar
     * Get Created
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @function setCreated
     * @access public
     * @param $created varchar
     * Set Created
     */
    public function setCreated($created) {
        $this->created = $created;
    }

    /*
      public function primarymemberemail($member_email)
      //{
      //$primarymemberemail = $member_email["primary"];
      //  return $primarymemberemail;
      //}

      public function primarymemberphone()
      {
      //$primarymememberphone=$this->member_phone;
      //return $primarymememberphone;
      }

      public function primarymemberaddress()
      {
      //$primarymememberaddress=$this->member_address;
      //return $primarymememberaddress;
      }
     */

    public function createMember() {
        $CI = & get_instance();
        $fname = $this->getFirstname();
        $middlename = $this->getMiddlename();
        $lname = $this->getLastname();
        $prename = $this->getPreferredName();
        $usertype = $this->getUserType();
        $precont = $this->getPreferContact();
        $status = $this->getStatus();
        $arr = array("firstname" => $fname, "middlename" => $middlename, "lastname" => $lname, "preferredname" => $prename, "user_type" => $usertype, "prefer_contact" => $precont, "status" => $status);
        $CI->Basic_model->insert_data($arr, $multiple = FALSE);
        //OR
        //  $CI->Basic_model->insert_data($fname,$middlename,$lname,$prename,$usertype,$precont,$status, $multiple = FALSE);
    }

    public function load_member_list($reqData) {
        return $this->CI->Member_model->member_list($reqData);
    }

    public function get_member_profile() {
        return $this->CI->Member_model->get_member_profile($this);
    }

    public function get_member_about() {
        return $this->CI->Member_model->get_member_about($this);
    }

   

    public function get_member_preference() {
        return $this->CI->Member_model->get_member_preference($this);
    }

    public function get_member_availability() {
        $where_array = array('memberId' => $this->getMemberid(), 'status' => $this->getStatus(), 'archive' => 0);
        $columns = array('title,is_default,status,start_date,end_date,first_week,second_week,flexible_availability,flexible_km,id,DATE_FORMAT(start_date, "%e/%m/%Y") as startDate,DATE_FORMAT(end_date, "%e/%m/%Y") as endDate,travel_km,id');
        $x = $this->CI->Basic_model->get_result('member_availability', $where_array, $columns, array('id', 'DESC'));
        //last_query();
        return $x;
    }

    

}
