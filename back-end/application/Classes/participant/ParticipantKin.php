<?php

/*
 * Filename: ParticipantKin.php
 * Desc: Participant Relation Details 
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * Class: Member
 * Desc: Variables and Methods for Participant Relation Details
 * Created: 02-08-2018
 */

class ParticipantKin {

    /**
     * @var participantkinid
     * @access private
     * @vartype: integer
     */
    private $participantkinid;

    /**
     * @var participantid
     * @access private
     * @vartype: integer
     */
    private $participantid;

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
     * @function getParticipantkinid
     * @access public
     * @returns $participantkinid integer
     * Get Participant Kin Id
     */
    public function getParticipantkinid() {
        return $this->participantkinid;
    }

    /**
     * @function setParticipantkinid
     * @access public
     * @param $street integer 
     * Set Participant Kin Id
     */
    public function setParticipantkinid($participantkinid) {
        $this->participantkinid = $participantkinid;
    }

    /**
     * @function getParticipantid
     * @access public
     * @returns $getParticipantid integer
     * Get Participant Id
     */
    public function getParticipantid() {
        return $this->participantid;
    }

    /**
     * @function setParticipantid
     * @access public
     * @param $participantid integer 
     * Set Participant Id
     */
    public function setParticipantid($participantid) {
        $this->participantid = $participantid;
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
     * @param $firstname integer 
     * Set Firstname
     */
    public function setFirstname($firstname) {
        $this->firstname = $firstname;
    }

    /**
     * @function getLastname
     * @access public
     * @returns $firstname varchar
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
     * @function getRelation
     * @access public
     * @returns $relation varchar
     * Get Relation
     */
    public function getRelation() {
        return $this->relation;
    }

    /**
     * @function setRelation
     * @access public
     * @param $relation varchar 
     * Set Relation
     */
    public function setRelation($relation) {
        $this->relation = $relation;
    }

    /**
     * @function getPhone
     * @access public
     * @returns $phone varchar
     * Get Phone
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * @function setPhone
     * @access public
     * @param $phone varchar 
     * Set Phone
     */
    public function setPhone($phone) {
        $this->phone = $phone;
    }

    /**
     * @function getEmail
     * @access public
     * @returns $email varchar
     * Get Email
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @function setEmail
     * @access public
     * @param $email varchar 
     * Set Email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * @function getPrimaryKin
     * @access public
     * @returns $primary_kin tinyint
     * Get Primary Kin
     */
    public function getPrimaryKin() {
        return $this->primary_kin;
    }

    /**
     * @function setPrimaryKin
     * @access public
     * @param $primaryKin tinyint 
     * Set Primary Kin
     */
    public function setPrimaryKin($primaryKin) {
        $this->primary_kin = $primaryKin;
    }

}
