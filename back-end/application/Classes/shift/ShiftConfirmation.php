<?php

/*
 * Filename: ShiftConfirmation.php
 * Desc: Confirmation of Shift by Members
 * @author YDT <yourdevelopmentteam.com.au>
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Class: ShiftConfirmation
 * Desc: Variables($confirm_with,$confirm_by,$firstname,$lastname) and Getter and Setter Methods for house
 * Created: 06-08-2018
*/

class ShiftConfirmation
   {
    /**
     * @var shiftid
     * @access private
     * @vartype: integer
     */
    private $shiftconfimationid;

    /**
     * @var shiftid
     * @access private
     * @vartype: integer
     */
    private $shiftid;
   
    /**
     * @var firstname
     * @access private
     * @vartype: tinyint
     */
    private $confirm_with;

    /**
     * @var firstname
     * @access private
     * @vartype: tinyint
     */
    private $confirm_by;

    /**
     * @var shiftid
     * @access private
     * @vartype: varchar
     */
    private $firstname;

    /**
     * @var shiftid
     * @access private
     * @vartype: varchar
     */
    private $lastname;

    /**
     * @var shiftid
     * @access private
     * @vartype: varchar
     */	 
    private $email;
	
    /**
     * @var shiftid
     * @access private
     * @vartype: varchar
     */
    private $phone;
	

    /**
     * @function getShiftconfimationid
	 * @access public
	 * @returns $shiftconfimationid integer
	 * Get Shiftconfimation Id 
	 */
    public function getShiftconfimationid() {
        return $this->shiftconfimationid;
    }
	
    /**
	 * @function setShiftconfimationid
	 * @access public
	 * @param $shiftconfimationid integer 
	 * Set Shiftconfimation Id
	 */
    public function setShiftconfimationid($shiftconfimationid) {
        $this->shiftconfimationid = $shiftconfimationid;
    }

    /**
     * @function getShiftid
	 * @access public
	 * @returns $shiftid integer
	 * Get Shift Id 
	 */
    public function getShiftid() {
        return $this->shiftid;
    }

    /**
	 * @function setShiftid
	 * @access public
	 * @param $shiftid integer 
	 * Set Shift Id
	 */
    public function setShiftid($shiftid) {
        $this->shiftid = $shiftid;
    }

    /**
     * @function getConfirmWith
	 * @access public
	 * @returns $shiftid tinyint
	 * Get ConfirmWith
	 */
    public function getConfirmWith() {
        return $this->confirm_with;
    }
	
    /**
	 * @function setConfirmWith
	 * @access public
	 * @param $confirmWith tinyint 
	 * Set ConfirmWith
	 */
    public function setConfirmWith($confirmWith) {
        $this->confirm_with = $confirmWith;
    }

    /**
     * @function getConfirmBy
	 * @access public
	 * @returns $confirm_by tinyint
	 * Get ConfirmBy
	 */
    public function getConfirmBy() {
        return $this->confirm_by;
    }

    /**
	 * @function setConfirmWith
	 * @access public
	 * @param $confirmBy tinyint 
	 * Set ConfirmBy
	 */
    public function setConfirmBy($confirmBy) {
        $this->confirm_by = $confirmBy;
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
}
