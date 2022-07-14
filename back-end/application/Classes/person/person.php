<?php

/*
 * Filename: Person.php
 * Desc: Deatils of Person
 * @author YDT <yourdevelopmentteam.com.au>
 */

namespace PersonClass;

use stdClass;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Person {

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * @var personId
     * @access private
     * @vartype: integer
     */
    private $personId;

    /**
     * @var personType
     * @access private
     * @vartype: integer
     */
    private $personType = 0;

    /**
     * @var firstName
     * @access private
     * @vartype: varchar
     */
    private $firstName;

    /**
     * @var lastName
     * @access private
     * @vartype: varchar
     */
    private $lastName;

    /**
     * @array personEmail
     * @access private
     *
     */
    private $personEmail = [];

    /**
     * @array personPhone
     * @access private
     */
    private $personPhone = [];

    /**
     * @array $personAddress
     * @access private
     */
    private $personAddress = [];

    /**
     * @array $person_source
     * @access private
     */
    private $person_source;

    /**
     * @array $created_by
     * @access private
     */
    private $created_by;

    /**
     * @array $status
     * @access private
     */
    private $status = 1;

    /**
     * @var $person_account
     * @access private
     * @vartype: integer
     */
    private $person_account = 0;

    /**
     * @var $contact_is_account
     * @access private
     * @vartype: integer
     */
    private $contact_is_account = 0;

    /**
     * @var $owner
     * @access private
     * @vartype: integer
     */
    private $owner;


    /**
     * @var string|null
     */
    private $ndis_number = null;

    /**
     * @var int|null
     */
    private $gender = null;

    private $aboriginal = 0;
    private $communication_method = 0;
    private $date_of_birth = null;
    private $religion = null;
    private $cultural_practices = null;

    private $interpreter = 0;
    private $avatar = "";

    /**
     * @function getPersonIdinid
     * @access public
     * @returns $personId integer
     * Get Admin Id
     */
    public function getPersonId() {
        return $this->personId;
    }

    /**
     * @function setPersonId
     * @access public
     * @param $personId integer
     * Set Admin Id
     */
    public function setPersonId($personId) {
        $this->personId = $personId;
    }

    /**
     * @function getFirstName
     * @access public
     * @returns $firstName varchar
     * Get Firstname
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * @function setFirstName
     * @access public
     * @param $firstName varchar
     * Set FirstName
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    /**
     * @function getLastName
     * @access public
     * @returns $lastName varchar
     * Get LastName
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * @function setLastName
     * @access public
     * @param $lastName varchar
     * Set LastName
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    /**
     * @function getPersonPhone
     * @access public
     * @returns $personPhone object array
     * Get phone
     */
    public function getPersonPhone() {
        return $this->personPhone;
    }

    /**
     * @function setPersonPhone
     * @access public
     * @param $phones object array
     * Set phones
     */
    public function setPersonPhone($phones = []) {
        if (!empty($phones)) {
            $phones = array_map(function($phone) {
                $ph = new stdClass();
                $ph->phone = trim($phone->phone?? null);
                return $ph;
            }, $phones);
        }
        $this->personPhone = $phones;
    }

    /**
     * @function getPersonEmail
     * @access public
     * @returns $personEmail object array
     * Get eamils
     */
    public function getPersonEmail() {
        return $this->personEmail;
    }

    /**
     * @function setPersonEmail
     * @access public
     * @param $emails object array
     * Set eamils
     */
    public function setPersonEmail($emails = []) {
        if (!empty($emails)) {
            $emails = array_map(function($email) {
                $em = new stdClass();
                $em->email = is_object($email) && property_exists($email, 'email')? trim($email->email) : '';
                return $em;
            }, $emails);
        }
        $this->personEmail = $emails;
    }

    /**
     * @function setPersonAddress
     * @access public
     * @param $personAddress object array
     * Set personAddress
     */
    function setPersonAddress($personAddress) {
        $this->personAddress = $personAddress;
    }

    /**
     * @function getPersonAddress
     * @access public
     * @returns $personAddress object array
     * Get personAddress
     */
    function getPersonAddress() {
        return $this->personAddress;
    }

    /**
     * @function getPersonType
     * @access public
     * @param $personType varchar
     * Set personType
     */
    public function getPersonType() {
        $this->personType;
    }

    /**
     * @function setPersonType
     * @access public
     * @param $personType varchar
     * Set personType
     */
    public function setPersonType($personType) {
        $this->personType = (int) $personType;
    }

    /**
     * @function setPerson_source
     * @access public
     * @param $person_source varchar
     * Set person_source
     */
    function setPerson_source($person_source) {
        $this->person_source = $person_source;
    }

    /**
     * @function getPerson_source
     * @access public
     * get person_source
     */
    function getPerson_source() {
        return $this->person_source;
    }

    /**
     * @function setDateOfBirth
     * @access public
     * @param $val varchar
     * Set date_of_birth
     */
    function setDateOfBirth($val) {
        $this->date_of_birth = $val;
    }

    /**
     * @function getDateOfBirth
     * @access public
     * get date_of_birth
     */
    function getDateOfBirth() {
        return $this->date_of_birth;
    }

    /**
     * @function setReligion
     * @access public
     * @param $val varchar
     * Set religion
     */
    function setReligion($val) {
        $this->religion = $val;
    }

    /**
     * @function getReligion
     * @access public
     * get religion
     */
    function getReligion() {
        return $this->religion;
    }

    /**
     * @function setCulturalPractices
     * @access public
     * @param $val varchar
     * Set cultural_practices
     */
    function setCulturalPractices($val) {
        $this->cultural_practices = $val;
    }

    /**
     * @function getCulturalPractices
     * @access public
     * get cultural_practices
     */
    function getCulturalPractices() {
        return $this->cultural_practices;
    }

    /* @function interpreter
    * @access public
    * @param $val varchar
    * Set interpreter
    */
   function setInterpreter($val) {
       $this->interpreter = $val;
   }

   /**
    * @function getInterpreter
    * @access public
    * get interpreter
    */
   function getInterpreter() {
       return $this->interpreter;
   }

    /**
     * @function setAboriginal
     * @access public
     * @param $val varchar
     * Set aboriginal
     */
    function setAboriginal($val) {
        $this->aboriginal = $val;
    }

    /**
     * @function getAboriginal
     * @access public
     * get aboriginal
     */
    function getAboriginal() {
        return $this->aboriginal;
    }

    /**
     * @function setCommunicationMethod
     * @access public
     * @param $val varchar
     * Set communication_method
     */
    function setCommunicationMethod($val) {
        $this->communication_method = $val;
    }

    /**
     * @function getCommunicationMethod
     * @access public
     * get communication_method
     */
    function getCommunicationMethod() {
        return $this->communication_method;
    }


    /**
     * @function setCreated_by
     * @access public
     * @param $created_by varchar
     * Set created_by
     */
    function setCreated_by($created_by) {
        $this->created_by = $created_by;
    }

    /**
     * @function getCreated_by
     * @access public
     * get created_by
     */
    function getCreated_by() {
        return $this->created_by;
    }

    /**
     * @function setStatus
     * @access public
     * @param $status varchar
     * Set status
     */
    function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @function getStatus
     * @access public
     * get status
     */
    function getStatus() {
        return $this->status;
    }

    /**
     * @function setContact_is_account
     * @access public
     * @param $contact_is_account varchar
     * Set contact_is_account
     */
    function setContact_is_account($contact_is_account) {
        $this->contact_is_account = $contact_is_account;
    }

    /**
     * @function getContact_is_account
     * @access public
     * get contact_is_account
     */
    function getContact_is_account() {
        return $this->contact_is_account;
    }

    /**
     * @function setPerson_account
     * @access public
     * @param $person_account varchar
     * Set person_account
     */
    function setPerson_account($person_account) {
        $this->person_account = $person_account;
    }

    /**
     * @function getPerson_account
     * @access public
     * get person_account
     */
    function getPerson_account() {
        return $this->person_account;
    }

    /**
     * @function setUsername
     * Set username
     */
    function setUsername($username) {
        $this->username = $username;
    }

    /**
     * @function getUsername
     * get username
     */
    function getUsername() {
        return $this->username;
    }

    /**
     * @function setPassword
     * Set password
     */
    function setPassword($password) {
        $this->password = $password;
    }

    /**
     * @function getPassword
     * get password
     */
    function getPassword() {
        return $this->password;
    }

    /**
     * @function setOwner
     * @access public
     * @param $owner varchar
     * Set owner
     */
    function setOwner($owner) {
        $this->owner = $owner;
    }

    /**
     * @function getOwner
     * @access public
     * get owner
     */
    function getOwner() {
        return $this->owner;
    }

    /**
     * @return string|null
     */
    public function getNdisNumber()
    {
        return $this->ndis_number;
    }

    /**
     * @param string $ndis_number
     * @return
     */
    public function setNdisNumber($ndis_number)
    {
        if (!$ndis_number) {
            $this->ndis_number = null;
        } else {
            $this->ndis_number = preg_replace("/\s+/", "", $ndis_number);
        }
    }

     /**
     * @return int|null
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @function setGender
     * @access public
     * @param $val int
     * Set Gender
     */
    function setGender($val) {
        $this->gender = $val;
    }

    /*
     * create person
     *
     * input set all value of person
     *
     * return type integer
     * return personId
     */

    public function createPerson() {
        $CI = & get_instance();
        $personData = array(
            'firstname' => $this->firstName,
            'lastname' => $this->lastName,
            'username' => (!empty($this->username)) ? $this->username : null,
            'password' => (!empty($this->password)) ? $this->password : null,
            'type' => (!empty($this->personType)) ? $this->personType : null,
            'person_source' => (!empty($this->person_source)) ? $this->person_source : null,
            'created_by' => (!empty($this->created_by)) ? $this->created_by : null,
            'aboriginal' => $this->aboriginal,
            'communication_method' => $this->communication_method,
            'date_of_birth' => $this->date_of_birth,
            'religion' => $this->religion,
            'cultural_practices' => $this->cultural_practices,
            'status' => $this->status,
            'created' => DATE_TIME,
            'updated' => DATE_TIME,
            'archive' => 0,
            'person_account' => $this->person_account,
            'contact_is_account' => $this->contact_is_account,
            'owner' => $this->owner,
            'ndis_number' => $this->ndis_number,
            'gender' => $this->gender,
            'interpreter' => $this->interpreter,
            'profile_pic' => $this->avatar,
            'middlename'=>$this->middlename,
            'previous_name'=>$this->previousname,
        );

        $personId = $CI->basic_model->insert_records('person', $personData);
        $this->setPersonId($personId);
        return $personId;
    }

    /*
     * update person
     *
     * input set all value of person
     *
     * return type integer
     * return personId
     */

    public function updatePerson() {
        $CI = & get_instance();
        $personData = array(
            'firstname' => $this->firstName,
            'lastname' => $this->lastName,
            'type' => (!empty($this->personType)) ? $this->personType : null,
            'person_source' => (!empty($this->person_source)) ? $this->person_source : null,
            'aboriginal' => $this->aboriginal,
            'communication_method' => $this->communication_method,
            'date_of_birth' => $this->date_of_birth,
            'religion' => $this->religion,
            'cultural_practices' => $this->cultural_practices,
            'status' => $this->status,
            'updated' => DATE_TIME,
            'archive' => 0,
            'ndis_number' => $this->ndis_number,
            'gender' => $this->gender,
            'interpreter' => $this->interpreter,
            'profile_pic' => $this->avatar,
            'middlename'=>$this->middlename,
            'previous_name'=>$this->previousname,
        );

        # only update username and password if it were provided
        if(!empty($this->username))
        $personData['username'] = $this->username;

        if(!empty($this->password))
        $personData['password'] = $this->password;

        $update = $CI->basic_model->update_records('person', $personData, ["id" => $this->personId]);
        return $this->personId;
    }

    /*
     * its use for inster address of person
     *
     * input set value of @personAddress
     *
     * return type non
     */

    public function insertAddress() {
        $CI = & get_instance();
        $CI->basic_model->update_records('person_address', ['archive' => 1], ['person_id' => $this->personId]);
        if (!empty($this->personAddress)) {
            $address = [];
            $i = 0;
            foreach ($this->personAddress as $key => $val) {
                $val = (object) $val;
                $address_type = ($i == 0) ? 1 : 2;

                $address[] = [
                    'unit_number' => $val->unit_number ?? '',
                    'street' => $val->street,
                    'person_id' => $this->personId,
                    'primary_address' => $address_type,
                    'suburb' => $val->suburb,
                    'postcode' => $val->postcode,
                    'state' => $val->state,
                    'lat' => $val->lat ?? '',
                    'long' => $val->long ?? '',
                    'manual_address' => $val->manual_address ?? 0,
                    'is_manual_address' => $val->is_manual_address ?? '',
                    'archive' => 0,
                ];

                $i++;
            }

            if (!empty($address)) {
                $CI->basic_model->insert_records('person_address', $address, true);
            }
        }
    }

    /*
     * insert person email
     *
     * @params
     * $inputName = 'name' // here name is default params
     * in array key name of email
     *
     * return type boolean
     */

    public function insertEmail($inputName = 'name') {
        $CI = & get_instance();
        $CI->basic_model->update_records('person_email', ['archive' => 1], ['person_id' => $this->personId]);
        if (count($this->personEmail) > 0) {
            $addional_email = [];
            $i = 0;
            foreach ($this->personEmail as $key => $val) {
                $val = (object) $val;
                if (empty($val->{$inputName})) {
                    continue;
                }
                if ($i == 0) {
                    $addional_email[] = ['email' => $val->{$inputName}, 'person_id' => $this->personId, 'primary_email' => 1, 'archive' => 0];
                } else {
                    $addional_email[] = ['email' => $val->{$inputName}, 'person_id' => $this->personId, 'primary_email' => 2, 'archive' => 0];
                }
                $i++;
            }
            if (!empty($addional_email)) {
                $CI->basic_model->insert_records('person_email', $addional_email, $multiple = true);
            }
        }
    }

    /*
     * insert phone number of person
     *
     * @$inputName = 'name' // here defualt key name
     * in array key name of phone
     */

    public function insertPhone($inputName = 'name') {
        $CI = & get_instance();
        $CI->basic_model->update_records('person_phone', ['archive' => 1], ['person_id' => $this->personId]);

        if (count($this->personPhone) > 0) {
            $addional_phone_number = [];
            $i = 0;
            foreach ($this->personPhone as $key => $val) {
                $val = (object) $val;
                if (empty($val->{$inputName})) {
                    continue;
                }
                if ($i == 0) {
                    $addional_phone_number[] = ['phone' => $val->{$inputName}, 'person_id' => $this->personId, 'primary_phone' => 1, 'archive' => 0];
                } else {
                    $addional_phone_number[] = ['phone' => $val->{$inputName}, 'person_id' => $this->personId, 'primary_phone' => 2, 'archive' => 0];
                }
                $i++;
            }
            if (!empty($addional_phone_number)) {
                $CI->basic_model->insert_records('person_phone', $addional_phone_number, true);
            }
        }
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

    public function updatePersonAvatar() {
        $CI = & get_instance();
        return $CI->basic_model->update_records('person', ['profile_pic' => $this->avatar], ['id' => $this->personId]);
    }
    /** 
     * Get the value of middlename
     */ 
    public function getMiddleName()
    {
        return $this->middlename;
    }

    /**
     * Set the value of middlename
     *
     * @return  self
     */ 
    public function setMiddleName($middlename)
    {
        $this->middlename = $middlename;

        return $this;
    }
/**
     * Get the value of middlename
     */ 
    public function getPreviousName()
    {
        return $this->previousname;
    }

    /**
     * Set the value of middlename
     *
     * @return  self
     */ 
    public function setPreviousName($previousname)
    {
        $this->previousname = $previousname;

        return $this;
    }    
}
