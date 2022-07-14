<?php

namespace CrmParticipantClass;

/*
 * Filename: Participant.php
 * Desc: Details of Participants
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * Class: Participant
 * Desc: Participants details like participant first name, last name, email, phone etc.
 * Variables and Getter and Setter Methods for participant class
 * Created: 02-08-2018
 */

class CrmParticipant
{

    public function __construct()
    {
        $CI = &get_instance();
    }

    /**
     * @var participantid
     * @access private
     * @vartype: int
     */
    private $participantid;

    /**
     * @var username
     * @access private
     * @vartype: varchar
     */
    private $username;
    private $password;

    /**
     * @var firstname
     * @access private
     * @vartype: varchar
     */
    private $firstname;
    private $middlename;

    /**
     * @var middlename
     * @access private
     * @vartype: varchar
     */
    private $lastname;

    /**
     * @var lastname
     * @access private
     * @vartype: varchar
     */
    private $gender;

    /**
     * @var gender
     * @access private
     * @vartype: tinyint
     */
    private $preferredname;

    /**
     * @var preferredname
     * @access private
     * @vartype: varchar
     */
    private $dob;

    /**
     * @var dob
     * @access private
     * @vartype: varchar
     */
    private $ndis_num;

    /**
     * @var ndis_num
     * @access private
     * @vartype: varchar
     */
    private $medicare_num;

    /**
     * @var crn_num
     * @access private
     * @vartype: varchar
     */
    private $crn_num;

    /**
     * @var referral
     * @access private
     * @vartype: tinyint
     */
    private $referral;

    /**
     * @var participantid
     * @access private
     * @vartype: varchar
     */
    private $living_situation;

    /**
     * @var participantid
     * @access private
     * @vartype: varchar
     */
    private $aboriginal_tsi;

    /**
     * @var participantid
     * @access private
     * @vartype: tinyint
     */
    private $oc_departments;

    /**
     * @var participantid
     * @access private
     * @vartype: int
     */
    private $houseid;

    /**
     * @var participantid
     * @access private
     * @vartype: varchar
     */
    private $created;
    /**
     * @var participantid
     * @access private
     * @vartype: varchar
     */
    private $ndis_plan;
    /**
     * @var participantid
     * @access private
     * @vartype: varchar
     */
    private $other_relevent_plans;
    /**
     * @var participantid
     * @access private
     * @vartype: varchar
     */
    private $current_behavioural;
    /**
     * @var participantid
     * @access private
     * @vartype: varchar
     */
    private $martialstatus;


    /**
     * @var participantid
     * @access private
     * @vartype: tinyint
     */
    private $status;

    /**
     * @var participantid
     * @access private
     * @vartype: tinyint
     */
    private $portal_access;

    /**
     * @arrayname participant_email
     * @access private
     * @array int|varchar|char
     */
    private $participant_email = array();

    /**
     * @arrayname participant_phone
     * @access private
     * @array int|varchar|tinyint
     */
    private $participant_phone = array();

    /**
     * @arrayname participant_assistance
     * @access private
     * @array int|smallint
     */
    private $participant_assistance = array();

    /**
     * @arrayname participant_oc_services
     * @access private
     * @array int|smallint
     */
    private $participant_oc_services = array();

    /**
     * @arrayname participant_care_not_tobook
     * @access private
     * @array int|tinyint|varchar
     */
    private $participant_care_not_tobook = array();
    private $referralfirstname;
    private $referrallastname;
    private $referralemail;
    private $referralphone;
    private $referralorg;
    private $assign_to;
    private $Address;
    private $state;
    private $postal;
    /**
     * @function getAssignTo
     * @access public
     * @returns $assign_to int
     * Get assign_to
     */
    public function getAssignTo()
    {
        return $this->assign_to;
    }

    /**
     * @function setAssignTo
     * @access public
     * @param $assign_to integer
     * Set assign_to
     */
    public function setAssignTo($assign_to)
    {
        $this->assign_to = $assign_to;
    }
    /**
     * @function getParticipantid
     * @access public
     * @returns $participantid int
     * Get Participant Id
     */
    public function getParticipantid()
    {
        return $this->participantid;
    }

    /**
     * @function setParticipantid
     * @access public
     * @param $participantid integer
     * Set Participant Id
     */
    public function setParticipantid($participantid)
    {
        $this->participantid = $participantid;
    }
    /**
     * @function getOldParticipantid
     * @access public
     * @returns $oldparticipantid int
     * Get Old Participant Id
     */
    public function getOldParticipantid()
    {
        return $this->oldparticipantid;
    }

    /**
     * @function setOldParticipantid
     * @access public
     * @param $oldparticipantid integer
     * Set Old Participant Id
     */
    public function setOldParticipantid($oldparticipantid)
    {
        $this->oldparticipantid = $oldparticipantid;
    }

    /**
     * @function getUserName
     * @access public
     * @returns $participantid int
     * Get Participant Id
     */
    public function getUserName()
    {
        return $this->username;
    }

    public function setUserName($username)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @function getFirstname
     * @access public
     * @returns $firstname varchar
     * Get Firstname
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @function setFirstname
     * @access public
     * @param $firstname varchar
     * Set Firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @function setMaritalStatus
     * @access public
     * @param $martialstatus varchar
     * Set martialstatus
     */
    public function setMaritalStatus($martialstatus)
    {
        $this->martialstatus = $martialstatus;
    }
    /**
     * @function getMaritalStatus
     * @access public
     * @returns $martialstatus varchar
     * Get martialstatus
     */
    public function getMaritalStatus()
    {
        return $this->martialstatus;
    }


    /**
     * @function setBehavioural
     * @access public
     * @param $current_behavioural varchar
     * Set current_behavioural
     */
    public function setBehavioural($current_behavioural)
    {
        $this->current_behavioural = $current_behavioural;
    }
    /**
     * @function getBehavioural
     * @access public
     * @returns current_behavioural varchar
     * Get current_behavioural
     */
    public function getBehavioural()
    {
        return $this->current_behavioural;
    }



    /**
     * @function getRelevantPlan
     * @access public
     * @param $other_relevent_plans varchar
     * Set other_relevent_plans
     */
    public function setRelevantPlan($other_relevent_plans)
    {
        $this->other_relevent_plans = $other_relevent_plans;
    }
    /**
     * @function getRelevantPlan
     * @access public
     * @returns RelevantPlan varchar
     * Get RelevantPlan
     */
    public function getRelevantPlan()
    {
        return $this->other_relevent_plans;
    }



    /**
     * @function getMiddlename
     * @access public
     * @returns $middlename varchar
     * Get Middlename
     */
    public function getMiddlename()
    {
        return $this->middlename;
    }

    /**
     * @function setMiddlename
     * @access public
     * @param $firstname varchar
     * Set Middlename
     */
    public function setMiddlename($middlename)
    {
        $this->middlename = $middlename;
    }

    /**
     * @function getLastname
     * @access public
     * @returns $lastname varchar
     * Get Lastname
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @function setLastname
     * @access public
     * @param $lastname varchar
     * Set Lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @function getGender
     * @access public
     * @returns $gender varchar
     * Get Gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @function setGender
     * @access public
     * @param $gender varchar
     * Set Gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }


    /**
     * @function setPreferredname
     * @access public
     * @param $preferredfirstname varchar
     * Set preferredfirstname
     */
    public function setPreferredname($preferredname)
    {
        $this->preferredname = $preferredname;
    }
    /**
     * @function getPreferredname
     * @access public
     * @returns $preferredfirstname varchar
     * Get preferredfirstname
     */
    public function getPreferredname()
    {
        return $this->preferredname;
    }
    /**
     * @function getDob
     * @access public
     * @returns $dob varchar
     * Get Dob
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * @function setDob
     * @access public
     * @param $dob varchar
     * Set Dob
     */
    public function setDob($dob)
    {
        $this->dob = $dob;
    }


    /**
     * @function setNdisPlan
     * @access public
     * @param $ndis_plan varchar
     * Set ndis_plan
     */
    public function setNdisPlan($ndis_plan)
    {

        $this->ndis_plan = $ndis_plan;
    }
    /**
     * @function getNdisPlan
     * @access public
     * @returns ndis_plan varchar
     * Get ndis_plan
     */
    public function getNdisPlan()
    {
        return $this->ndis_plan;
    }

    /**
     * @function getNdisNum
     * @access public
     * @returns $ndis_num varchar
     * Get NdisNum
     */
    public function getNdisNum()
    {
        return $this->ndis_num;
    }

    /**
     * @function setNdisNum
     * @access public
     * @param $ndisNum varchar
     * Set NdisNum
     */
    public function setNdisNum($ndisNum)
    {
        $this->ndis_num = $ndisNum;
    }

    /**
     * @function getMedicareNum
     * @access public
     * @returns $medicare_num varchar
     * Get Medicare_num
     */
    public function getMedicareNum()
    {
        return $this->medicare_num;
    }

    /**
     * @function setMedicareNum
     * @access public
     * @param $medicareNum varchar
     * Set MedicareNum
     */
    public function setMedicareNum($medicareNum)
    {
        $this->medicare_num = $medicareNum;
    }

    /**
     * @function getCrnNum
     * @access public
     * @returns $CrnNum varchar
     * Get CrnNum
     */
    public function getCrnNum()
    {
        return $this->crn_num;
    }

    /**
     * @function setCrnNum
     * @access public
     * @param $crnNum varchar
     * Set CrnNum
     */
    public function setCrnNum($crnNum)
    {
        $this->crn_num = $crnNum;
    }

    /**
     * @function getReferral
     * @access public
     * @returns $referral tinyint
     * Get Referral
     */
    public function getReferral()
    {
        return $this->referral;
    }

    /**
     * @function setReferral
     * @access public
     * @param $referral tinyint
     * Set Referral
     */
    public function setReferral($referral)
    {
        $this->referral = $referral;
    }

    /**
     * @function getLivingSituation
     * @access public
     * @returns $living_situation varchar
     * Get Living Situation
     */
    public function getLivingSituation()
    {
        return $this->living_situation;
    }

    /**
     * @function setLivingSituation
     * @access public
     * @param $livingSituation varchar
     * Set Living Situation
     */
    public function setLivingSituation($livingSituation)
    {
        $this->living_situation = $livingSituation;
    }

    /**
     * @function getAboriginalTsi
     * @access public
     * @returns $aboriginal_tsi varchar
     * Get Aboriginal Tsi
     */
    public function getAboriginalTsi()
    {
        return $this->aboriginal_tsi;
    }

    /**
     * @function setAboriginalTsi
     * @access public
     * @param $aboriginalTsi varchar
     * Set Aboriginal Tsi
     */
    public function setAboriginalTsi($aboriginalTsi)
    {
        $this->aboriginal_tsi = $aboriginalTsi;
    }

    /**
     * @function getOcDepartments
     * @access public
     * @returns $oc_departments tinyint
     * Get Oc Departments
     */
    public function getOcDepartments()
    {
        return $this->oc_departments;
    }

    /**
     * @function setOcDepartments
     * @access public
     * @param $ocDepartments tinyint
     * Set Oc Departments
     */
    public function setOcDepartments($ocDepartments)
    {
        $this->oc_departments = $ocDepartments;
    }

    /**
     * @function getHouseid
     * @access public
     * @returns $houseid tinyint
     * Get Houseid
     */
    public function getHouseid()
    {
        return $this->houseid;
    }

    /**
     * @function setHouseid
     * @access public
     * @param $houseid int
     * Set Houseid
     */
    public function setHouseid($houseid)
    {
        $this->houseid = $houseid;
    }




    /**
     * @function getCreated
     * @access public
     * @returns $houseid varchar
     * Get Houseid
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @function setCreated
     * @access public
     * @param $houseid varchar
     * Set Created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @function getStatus
     * @access public
     * @returns $houseid tinyint
     * Get Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @function setStatus
     * @access public
     * @param $status tinyint
     * Set Status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @function getPortalAccess
     * @access public
     * @returns $portal_access tinyint
     * Get Portal Access
     */
    public function getPortalAccess()
    {
        return $this->portal_access;
    }

    /**
     * @function setPortalAccess
     * @access public
     * @param $portalAccess tinyint
     * Set Portal Access
     */
    public function setPortalAccess($portalAccess)
    {
        $this->portal_access = $portalAccess;
    }

    /**
     * @function getParticipantEmail
     * @access public
     * returns  $participant_email integer|char|varchar
     * Get Participant Email
     */
    public function getParticipantEmail()
    {
        return $this->participant_email;
    }

    /**
     * @function setParticipantEmail
     * @access public
     * @param $participantEmail integer|char|varchar
     * Set Participant Email
     */
    public function setParticipantEmail($participantEmail)
    {
        $this->participant_email = $participantEmail;
    }

    /**
     * @function getParticipantPhone
     * @access public
     * returns  $participant_phone integer|varchar|tinyint
     * Get Participant Phone
     */
    public function getParticipantPhone()
    {
        return $this->participant_phone;
    }

    /**
     * @function setParticipantPhone
     * @access public
     * @param $participantPhone integer|varchar|tinyint
     * Set Participant Phone
     */
    public function setParticipantPhone($participantPhone)
    {
        $this->participant_phone = $participantPhone;
    }

    /**
     * @function getParticipantAssistance
     * @access public
     * returns  $participant_assistance integer|smallint
     * Get Participant Assistance
     */
    public function getParticipantAssistance()
    {
        return $this->participant_assistance;
    }

    /**
     * @function setParticipantAssistance
     * @access public
     * @param $participantAssistance integer|smallint
     * Set Participant Participant Assistance
     */
    public function setParticipantAssistance($participantAssistance)
    {
        $this->participant_assistance = $participantAssistance;
    }

    /**
     * @function getParticipantOcServices
     * @access public
     * returns  $participant_oc_services integer|smallint
     * Get Participant Oc Services
     */
    public function getParticipantOcServices()
    {
        return $this->participant_oc_services;
    }

    /**
     * @function setParticipantGoalResult
     * @access public
     * @param $participantOcServices integer|smallint
     * Set Participant Oc Services
     */
    public function setParticipantOcServices($participantOcServices)
    {
        $this->participant_oc_services = $participantOcServices;
    }

    /**
     * @function getParticipantCareNotTobook
     * @access public
     * returns  $participant_care_not_tobook integer|tinyint|varchar
     * Get Participant Care Not Tobook Services
     */
    public function getParticipantCareNotTobook()
    {
        return $this->participant_care_not_tobook;
    }

    /**
     * @function setParticipantCareNotTobook
     * @access public
     * @param $participantCareNotTobook integer|tinyint|varchar
     * Set Participant Care Not To book
     */
    public function setParticipantCareNotTobook($participantCareNotTobook)
    {
        $this->participant_care_not_tobook = $participantCareNotTobook;
    }

    public function getReferralFirstName()
    {
        return $this->referralfirstname;
    }

    public function setReferralFirstName($referralfirstname)
    {
        $this->referralfirstname = $referralfirstname;
    }

    public function getReferralLastName()
    {
        return $this->referrallastname;
    }

    public function setReferralLastName($referrallastname)
    {
        $this->referrallastname = $referrallastname;
    }

    public function getReferralEmail()
    {
        return $this->referralemail;
    }
    public function setReferralOrg($referralorg)
    {
        $this->referralorg = $referralorg;
    }
    public function getReferralOrg()
    {
        return $this->referralorg;
    }

    public function setReferralEmail($referralemail)
    {
        $this->referralemail = $referralemail;
    }

    public function getReferralPhone()
    {
        return $this->referralphone;
    }

    public function setReferralPhone($referralphone)
    {
        $this->referralphone = $referralphone;
    }

    private $Referralparticipantrelation;
    public function getReferralParticipantRelation()
    {
        return $this->Referralparticipantrelation;
    }

    public function setReferralParticipantRelation($Referralparticipantrelation)
    {
        $this->Referralparticipantrelation = $Referralparticipantrelation;
    }

    private $participantrelation;

    public function getParticipantRelation()
    {
        return $this->participantrelation;
    }

    public function setParticipantRelation($participantrelation)
    {
        $this->participantrelation = $participantrelation;
    }
    public function setAddress($Address)
    {

        $this->Address = $Address;
    }

    public function getAddress()
    {

        return $this->Address;
    }
    public function getProvidePlan()
    {
        return $this->provide_plan;
    }
    public function setProvidePlan($provide_plan)
    {
        $this->provide_plan = $provide_plan;
    }
    public function getProvideEmail()
    {
        return $this->provide_email;
    }
    public function setProvideEmail($provide_email)
    {
        $this->provide_email = $provide_email;
    }
    public function getProvideState()
    {
        return $this->provide_state;
    }
    public function setProvideState($provide_state)
    {
        $this->provide_state = $provide_state;
    }
    public function getProvideAddress()
    {
        return $this->provide_address;
    }
    public function setProvideAddress($provide_address)
    {
        $this->provide_address = $provide_address;
    }
    public function getProvidePostcode()
    {
        return $this->provide_postcode;
    }
    public function setProvidePostcode($provide_postcode)
    {
        $this->provide_postcode = $provide_postcode;
    }

    public function getState()
    {
        return $this->state;
    }
    public function setState($state)
    {
        $this->state = $state;
    }
    public function getCity()
    {
        return $this->city;
    }
    public function setCity($city)
    {
        $this->city = $city;
    }
    public function setHearingDocId($hearing_file_id)
    {
        $this->hearing_file_id = $hearing_file_id;
    }
    public function getHearingDocId()
    {
        return $this->hearing_file_id;
    }

    public function setNdisDocId($ndis_file_id)
    {
        $this->ndis_file_id = $ndis_file_id;
    }
    public function getNdisDocId()
    {
        return $this->ndis_file_id;
    }

    public function getPostcode()
    {
        return $this->postal;
    }


    public function setPostcode($postal)
    {
        $this->postal = $postal;
    }
    private $archive;

    public function getArchive()
    {
        return $this->archive;
    }

    public function setArchive($archive)
    {
        $this->archive = $archive;
    }


    public function AddCrmParticipant($action, $adminId)
    {
        // check Here server site validation
        // Insert record method
        $CI = &get_instance();
        $CI->load->model('CrmParticipant_model');
        return $CI->CrmParticipant_model->create_crm_participant($this, $action, $adminId);
    }
    public function checkNdisNumber()
    {

        $CI = &get_instance();
        $CI->load->model('Basic_model');
        $where = array("ndis_num" => $this->getNdisNum());
        return $CI->Basic_model->get_record_where("crm_participant", "*", $where);
    }
    public function checkEmailId()
    {

        $CI = &get_instance();
        $CI->load->model('Basic_model');
        $where = array("email" => $this->getParticipantEmail());
        return $CI->Basic_model->get_record_where("crm_participant_email", "*", $where);
    }
    // get participant details through ndis number
    public function getParticipantDetails()
    {
        $CI = &get_instance();
        $CI->load->model('Basic_model');
        $where = array("ndis_num" => $this->getNdisNum());
        return $CI->Basic_model->get_record_where("crm_participant", "*", $where);
    }

    public function genratePassword($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $this->setPassword($randomString);
        return $randomString;
    }

    public function encryptPassword()
    {
        $encrypted_password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->setPassword($encrypted_password);
    }

    public function WelcomeMailParticipant()
    {
        $userData['fullname'] = $this->firstname . ' ' . $this->lastname;
        $userData['password'] = $this->password;
        $userData['username'] = $this->username;
        $userData['email'] = $this->participant_email[0]["email"];
        welcome_mail_participant($userData, $cc_email_address = null);
    }

    public function RenewPlanMailParticipant()
    {
        $userData['fullname'] = $this->firstname;
        $userData['email'] = $this->participant_email[0]["email"];
        renew_plan_mail_participant($userData, $cc_email_address = null);
    }

    public function LessFundsPlanMailParticipant()
    {
        $userData['fullname'] = $this->firstname;
        $userData['email'] = $this->participant_email[0]["email"];
        less_funds_mail_participant($userData, $cc_email_address = null);
    }

    public function SendPlanRenewalOrModifiedMail($intake_type)
    {
        $userData['fullname'] = $this->firstname . ' ' . $this->lastname;
        $userData['password'] = $this->password;
        $userData['username'] = $this->username;
        $userData['email'] = $this->participant_email[0]["email"];
        $subject = "HCM: Participant plan is Renewed/Modified";
        send_plan_renew_or_modify_mail($userData, $subject, $cc_email_address = null);
    }


    /**
     * Get the recent CRM participant plan details
     * @return array|null 
     */
    public function findLastPlan($crmParticipantId)
    {
        $CI = &get_instance();
        $query = $CI->db
            ->from("tbl_crm_participant_plan")
            ->where(['crm_participant_id' => $crmParticipantId])
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get();

        return $query->row_array();
    }


    /**
     * Send an on-boarding email (or a 'welcome' email) to 
     * participant with plan's start and end dates.
     *  
     * @param string $planStartDate Date in `Y-m-d` format
     * @param string $planEndDate Date in `Y-m-d` format. Must be after the `$planStartDate`
     */
    public function sendOnBoardingEmailWithPlanDates()
    {
        $userData['fullname'] = $this->firstname . ' ' . $this->lastname;
        $userData['email'] = $this->participant_email[0]["email"];

        $crmParticipantPlan = $this->findLastPlan($this->participantid);

        $planStartDate = '';
        $planEndDate = '';
        if ($crmParticipantPlan) {
            $planStartDate = \DateTime::createFromFormat('Y-m-d', $crmParticipantPlan['start_date'])->format('jS \of F, Y'); // eg 2020-03-18 -> 18th of March, 2020
            $planEndDate = \DateTime::createFromFormat('Y-m-d', $crmParticipantPlan['end_date'])->format('jS \of F, Y');
        }

        $userData['plan_start_date'] = $planStartDate;
        $userData['plan_end_date'] = $planEndDate;

        // Credentials aren't needed for now (PIMSD-34)
        // $userData['password'] = $this->password;
        // $userData['username'] = $this->username;

        welcome_mail_participant_with_plan_dates($userData, null);
    }




    //    public function sendWelcomeMail() {
    //        $CI = & get_instance();
    //        $CI->load->model('Participant_model');
    //        return $CI->Participant_model->Check_UserName($this);
    //    }
}
