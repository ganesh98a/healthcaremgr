<?php

namespace ParticipantClass;

/*
 * Filename: Participant.php
 * Desc: Details of Participants
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * Class: Participant
 * Desc: Participants details like participant first name, last name, email, phone etc.
 * Variables and Getter and Setter Methods for participant class
 * Created: 02-08-2018
 */

class Participant {

    /**
     * @var participantid
     * @vartype: int
     */
    private $participantid;

    /**
     * @var username
     * @vartype: varchar
     */
    private $username;
    private $password;

    /**
     * @var firstname
     * @vartype: varchar
     */
    private $firstname;
    private $middlename;

    /**
     * @var middlename
     * @vartype: varchar
     */
    private $lastname;

    /**
     * @var lastname
     * @vartype: varchar
     */
    private $gender;

    /**
     * @var gender
     * @vartype: tinyint
     */
    private $preferredname;

    /**
     * @var preferredname
     * @vartype: varchar
     */
    private $dob;

    /**
     * @var dob
     * @vartype: varchar
     */
    private $ndis_num;

    /**
     * @var ndis_num
     * @vartype: varchar
     */
    private $medicare_num;

    /**
     * @var crn_num
     * @vartype: varchar
     */
    private $crn_num;

    /**
     * @var referral
     * @vartype: tinyint
     */
    private $referral;

    /**
     * @var participantid
     * @vartype: varchar
     */
    private $living_situation;

    /**
     * @var participantid
     * @vartype: varchar
     */
    private $aboriginal_tsi;

    /**
     * @var participantid
     * @vartype: tinyint
     */
    private $oc_departments;

    /**
     * @var participantid
     * @vartype: int
     */
    private $houseid;

    /**
     * @var participantid
     * @vartype: varchar
     */
    private $created;

    /**
     * @var participantid
     * @vartype: tinyint
     */
    private $status;

    /**
     * @var participantid
     * @vartype: tinyint
     */
    private $portal_access;

    /**
     * @arrayname participant_email
     * @array int|varchar|char
     */
    private $participant_email = [];

    /**
     * @arrayname participant_phone
     * @array int|varchar|tinyint
     */
    private $participant_phone = [];

    /**
     * @arrayname participant_assistance
     * @array int|smallint
     */
    private $participant_assistance = [];

    /**
     * @arrayname participant_oc_services
     * @array int|smallint
     */
    private $participant_oc_services = [];

    /**
     * @arrayname participant_care_not_tobook
     * @array int|tinyint|varchar
     */
    private $participant_care_not_tobook = [];
    private $referralfirstname;
    private $referrallastname;
    private $referralemail;
    private $referralphone;
    private $prefer_contact;
    private $archive;

    /**
     * @function getParticipantid
     * @returns $participantid int
     * Get Participant Id
     */
    public function getParticipantid() {
        return $this->participantid;
    }

    /**
     * @function setParticipantid
     *
     * @param $participantid integer
     * Set Participant Id
     */
    public function setParticipantid($participantid) {
        $this->participantid = $participantid;
    }

    /**
     * @function getUserName
     * @returns $participantid int
     * Get Participant Id
     */
    public function getUserName() {
        return $this->username;
    }

    public function setUserName($username) {
        $this->username = $username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * @function getFirstname
     * @returns $firstname varchar
     * Get Firstname
     */
    public function getFirstname() {
        return $this->firstname;
    }

    /**
     * @function setFirstname
     *
     * @param $firstname varchar
     * Set Firstname
     */
    public function setFirstname($firstname) {
        $this->firstname = $firstname;
    }

    /**
     * @function getMiddlename
     * @returns $middlename varchar
     * Get Middlename
     */
    public function getMiddlename() {
        return $this->middlename;
    }

    /**
     * @function setMiddlename
     *
     * @param $firstname varchar
     * Set Middlename
     */
    public function setMiddlename($middlename) {
        $this->middlename = $middlename;
    }

    /**
     * @function getLastname
     * @returns $lastname varchar
     * Get Lastname
     */
    public function getLastname() {
        return $this->lastname;
    }

    /**
     * @function setLastname
     *
     * @param $lastname varchar
     * Set Lastname
     */
    public function setLastname($lastname) {
        $this->lastname = $lastname;
    }

    /**
     * @function getGender
     * @returns $gender varchar
     * Get Gender
     */
    public function getGender() {
        return $this->gender;
    }

    /**
     * @function setGender
     *
     * @param $gender varchar
     * Set Gender
     */
    public function setGender($gender) {
        $this->gender = $gender;
    }

    /**
     * @function getPreferredname
     * @returns $preferredname varchar
     * Get Preferredname
     */
    public function getPreferredname() {
        return $this->preferredname;
    }

    /**
     * @function setPreferredname
     *
     * @param $gender varchar
     * Set Preferredname
     */
    public function setPreferredname($preferredname) {
        $this->preferredname = $preferredname;
    }

    /**
     * @function getDob
     * @returns $dob varchar
     * Get Dob
     */
    public function getDob() {
        return $this->dob;
    }

    /**
     * @function setDob
     *
     * @param $dob varchar
     * Set Dob
     */
    public function setDob($dob) {
        $CI = &get_instance();
        $this->dob = DateFormate($dob, 'Y-m-d');
    }

    /**
     * @function getNdisNum
     * @returns $ndis_num varchar
     * Get NdisNum
     */
    public function getNdisNum() {
        return $this->ndis_num;
    }

    /**
     * @function setNdisNum
     *
     * @param $ndisNum varchar
     * Set NdisNum
     */
    public function setNdisNum($ndisNum) {
        $this->ndis_num = $ndisNum;
    }

    /**
     * @function getMedicareNum
     * @returns $medicare_num varchar
     * Get Medicare_num
     */
    public function getMedicareNum() {
        return $this->medicare_num;
    }

    /**
     * @function setMedicareNum
     *
     * @param $medicareNum varchar
     * Set MedicareNum
     */
    public function setMedicareNum($medicareNum) {
        $this->medicare_num = $medicareNum;
    }

    /**
     * @function getCrnNum
     * @returns $CrnNum varchar
     * Get CrnNum
     */
    public function getCrnNum() {
        return $this->crn_num;
    }

    /**
     * @function setCrnNum
     *
     * @param $crnNum varchar
     * Set CrnNum
     */
    public function setCrnNum($crnNum) {
        $this->crn_num = $crnNum;
    }

    /**
     * @function getReferral
     * @returns $referral tinyint
     * Get Referral
     */
    public function getReferral() {
        return $this->referral;
    }

    /**
     * @function setReferral
     *
     * @param $referral tinyint
     * Set Referral
     */
    public function setReferral($referral) {
        $this->referral = $referral;
    }

    /**
     * @function getLivingSituation
     * @returns $living_situation varchar
     * Get Living Situation
     */
    public function getLivingSituation() {
        return $this->living_situation;
    }

    /**
     * @function setLivingSituation
     *
     * @param $livingSituation varchar
     * Set Living Situation
     */
    public function setLivingSituation($livingSituation) {
        $this->living_situation = $livingSituation;
    }

    /**
     * @function getAboriginalTsi
     * @returns $aboriginal_tsi varchar
     * Get Aboriginal Tsi
     */
    public function getAboriginalTsi() {
        return $this->aboriginal_tsi;
    }

    /**
     * @function setAboriginalTsi
     *
     * @param $aboriginalTsi varchar
     * Set Aboriginal Tsi
     */
    public function setAboriginalTsi($aboriginalTsi) {
        $this->aboriginal_tsi = $aboriginalTsi;
    }

    /**
     * @function getOcDepartments
     * @returns $oc_departments tinyint
     * Get Oc Departments
     */
    public function getOcDepartments() {
        return $this->oc_departments;
    }

    /**
     * @function setOcDepartments
     *
     * @param $ocDepartments tinyint
     * Set Oc Departments
     */
    public function setOcDepartments($ocDepartments) {
        $this->oc_departments = $ocDepartments;
    }

    /**
     * @function getHouseid
     * @returns $houseid tinyint
     * Get Houseid
     */
    public function getHouseid() {
        return $this->houseid;
    }

    /**
     * @function setHouseid
     *
     * @param $houseid int
     * Set Houseid
     */
    public function setHouseid($houseid) {
        $this->houseid = $houseid;
    }

    /**
     * @function getCreated
     * @returns $houseid varchar
     * Get Houseid
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @function setCreated
     *
     * @param $houseid varchar
     * Set Created
     */
    public function setCreated($created) {
        $this->created = $created;
    }

    /**
     * @function getStatus
     * @returns $houseid tinyint
     * Get Status
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @function setStatus
     *
     * @param $status tinyint
     * Set Status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @function getPortalAccess
     * @returns $portal_access tinyint
     * Get Portal Access
     */
    public function getPortalAccess() {
        return $this->portal_access;
    }

    /**
     * @function setPortalAccess
     *
     * @param $portalAccess tinyint
     * Set Portal Access
     */
    public function setPortalAccess($portalAccess) {
        $this->portal_access = $portalAccess;
    }

    /**
     * @function getParticipantEmail
     */
    public function getParticipantEmail() {
        return $this->participant_email;
    }

    /**
     * @function setParticipantEmail
     *
     * @param $participantEmail integer|char|varchar
     * Set Participant Email
     */
    public function setParticipantEmail($participantEmail) {
        $this->participant_email = $participantEmail;
    }

    /**
     * @function getParticipantPhone
     */
    public function getParticipantPhone() {
        return $this->participant_phone;
    }

    /**
     * @function setParticipantPhone
     *
     * @param $participantPhone integer|varchar|tinyint
     * Set Participant Phone
     */
    public function setParticipantPhone($participantPhone) {
        $this->participant_phone = $participantPhone;
    }

    /**
     * @function getParticipantAssistance
     */
    public function getParticipantAssistance() {
        return $this->participant_assistance;
    }

    /**
     * @function setParticipantAssistance
     *
     * @param $participantAssistance integer|smallint
     * Set Participant Participant Assistance
     */
    public function setParticipantAssistance($participantAssistance) {
        $this->participant_assistance = $participantAssistance;
    }

    /**
     * @function getParticipantOcServices
     */
    public function getParticipantOcServices() {
        return $this->participant_oc_services;
    }

    /**
     * @function setParticipantGoalResult
     *
     * @param $participantOcServices integer|smallint
     * Set Participant Oc Services
     */
    public function setParticipantOcServices($participantOcServices) {
        $this->participant_oc_services = $participantOcServices;
    }

    /**
     * @function getParticipantCareNotTobook
     */
    public function getParticipantCareNotTobook() {
        return $this->participant_care_not_tobook;
    }

    /**
     * @function setParticipantCareNotTobook
     *
     * @param $participantCareNotTobook integer|tinyint|varchar
     * Set Participant Care Not To book
     */
    public function setParticipantCareNotTobook($participantCareNotTobook) {
        $this->participant_care_not_tobook = $participantCareNotTobook;
    }

    public function getReferralFirstName() {
        return $this->referralfirstname;
    }

    public function setReferralFirstName($referralfirstname) {
        $this->referralfirstname = $referralfirstname;
    }

    public function getReferralLastName() {
        return $this->referrallastname;
    }

    public function setReferralLastName($referrallastname) {
        $this->referrallastname = $referrallastname;
    }

    public function getReferralEmail() {
        return $this->referralemail;
    }

    public function setReferralEmail($referralemail) {
        $this->referralemail = $referralemail;
    }

    public function getReferralPhone() {
        return $this->referralphone;
    }

    public function setReferralPhone($referralphone) {
        $this->referralphone = $referralphone;
    }

    private $participantrelation;

    public function getParticipantRelation() {
        return $this->participantrelation;
    }

    public function setParticipantRelation($participantrelation) {
        $this->participantrelation = $participantrelation;
    }

    function setPrefer_contact($prefer_contact) {
        $this->prefer_contact = $prefer_contact;
    }

    function getPrefer_contact() {
        return $this->prefer_contact;
    }

    public function getArchive() {
        return $this->archive;
    }

    public function setArchive($archive) {
        $this->archive = $archive;
    }

    public function AddParticipant() {
        // check Here server site validation
        // Insert record method
        $CI = &get_instance();
        $CI->load->model('Participant/Participant_profile_model');

        return $CI->Participant_profile_model->create_participant($this);
    }

    public function genratePassword($length = 10) {
        $randomString = random_genrate_password(10);

        $this->setPassword($randomString);

        return $randomString;
    }

    public function encryptPassword() {
        $encrypted_password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->setPassword($encrypted_password);
    }

    public function WelcomeMailParticipant() {
        $userData['fullname'] = $this->firstname . ' ' . $this->lastname;
        $userData['password'] = $this->password;
        $userData['username'] = $this->username;
        $userData['email'] = $this->participant_email[0]['email'];
        welcome_mail_participant($userData, $cc_email_address = null);
    }

    public function UpdateParticipant() {
        
    }

    public function DeleteParticipant() {
        
    }

    public function getParticipant() {
        
    }

    public function getParticipantList() {
        
    }

    public function participant_UserName() {
        $CI = &get_instance();
        $CI->load->model('Participant_profile_model');

        return $CI->Participant_profile_model->Check_UserName($this);
    }

}
