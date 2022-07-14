<?php

/*
 * Filename: MemberKin.php
 * Desc: This file is created to hold and provide the information of the relatives of members. 
 * @author YDT <yourdevelopmentteam.com.au>
*/

if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Class: MemberKin
 * Desc: The class is used to get and store the information of relatives of the members. There fisrtname, lastname, relation, phone no. etc are used. 
 * Created: 01-08-2018
*/

class MemberKin
  {
    /**
	 * @var memberkinid
     * @access private
     * @vartype: int
     */
    private $memberkinid;

    /**
	 * @var memberid
     * @access private
     * @vartype: int
     */
    private $memberid;

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
	 * @var relation
     * @access private
     * @vartype: varchar
     */
    private $relation;

    /**
	 * @var phone
     * @access private
     * @vartype: varchar
     */
    private $phone;

    /**
	 * @var email
     * @access private
     * @vartype: varchar
     */
    private $email;

    /**
	 * @var primary_kin
     * @access private
     * @vartype: varchar
     */
    private $primary_kin;

	 
	/**
	 * @function getMemberkinid
	 * @access public
	 * @return $memberkinid integer
	 * Get $memberkinid Id
	 */
    public function getMemberkinid() {
        return $this->memberkinid;
    }

    /**
	 * @function setMemberkinid
     * @param $memberkinid integer 
     * @access public
	 * Set Member Kin Id
     */
    public function setMemberkinid($memberkinid) {
        $this->memberkinid = $memberkinid;
    }

    /**
	 * @function getMemberId
	 * @access public
	 * @return $memberId integer
	 * Get Member Id
	 */
    public function getMemberId() {
        return $this->memberId;
    }

    /**
     * @function getMemberId
     * @param $memberId integer 
     * @access public
     * Set Member Id
     */
    public function setMemberId($memberId) {
        $this->memberId = $memberId;
    }

    /**
	 * @function getFirstname
	 * @access public
	 * @return $firstname varchar
	 * Get $firstname
	 */
    public function getFirstname() {
        return $this->firstname;
    }

    /**
	 * @function setFirstname
     * @param $firstname varchar 
     * @access public
	 * Set First Name
     */
    public function setFirstname($firstname) {
        $this->firstname = $firstname;
    }

    
    /**
	 * @function getLastname
	 * @access public
	 * @return $lastname varchar
	 * Get Last Name
	 */
    public function getLastname() {
        return $this->lastname;
    }

    /**
	 * @function setLastname
     * @param $lastname varchar 
     * @access public
	 * Set Last Name
     */
    public function setLastname($lastname) {
        $this->lastname = $lastname;
    }

    /**
	 * @function getRelation
	 * @access public
	 * @return $relation varchar
	 * Get Relation
	 */
    public function getRelation() {
        return $this->relation;
    }

    /**
	 * @function setRelation
     * @param $relation varchar 
     * @access public
	 * Set Relation
     */
    public function setRelation($relation) {
        $this->relation = $relation;
    }

    /**
	 * @function getPhone
	 * @access public
	 * @return $phone varchar
	 * Get Phone
	 */
    public function getPhone() {
        return $this->phone;
    }

    /**
	 * @function setPhone
     * @param $phone varchar 
     * @access public
	 * Set Phone
     */
    public function setPhone($phone) {
        $this->phone = $phone;
    }

    /**
	 * @function getEmail
	 * @access public
	 * @return $email varchar
	 * Get Email
	 */
    public function getEmail() {
        return $this->email;
    }

    /**
	 * @function setEmail
     * @param $email varchar 
     * @access public
	 * Set Email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
	 * @function getPrimaryKin
	 * @access public
	 * @return $primary_kin varchar
	 * Get Primary_Kin
	 */
    public function getPrimaryKin() {
        return $this->primary_kin;
    }

    /**
	 * @function setPrimaryKin
     * @param $primarykin varchar 
     * @access public
	 * Set Primary Kin
     */
    public function setPrimaryKin($primaryKin) {
        $this->primary_kin = $primaryKin;
    }
}
