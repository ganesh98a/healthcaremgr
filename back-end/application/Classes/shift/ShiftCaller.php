<?php

/*
 * Filename: ShiftCaller.php
 * Desc: To create Shift for Members by using ShiftCaller
 * @author YDT <yourdevelopmentteam.com.au>
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Class: ShiftCaller
 * Desc: Variables($shiftcallerid,$shiftid,$firstname,$lastname,$email,$phone)
 * Getter and Setter Methods of particular variables for ShiftCaller.php file
 * Created: 06-08-2018
*/

class ShiftCaller
  {
    /**
     * @var shiftcallerid
     * @access private
     * @vartype: integer
     */
    private $shiftcallerid;

    /**
     * @var shiftid
     * @access private
     * @vartype: integer
     */
    private $shiftid;

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
     * @var email
     * @access private
     * @vartype: varchar
     */
    private $email;

    /**
     * @var phone
     * @access private
     * @vartype: varchar
     */
    private $phone;
	

    /**
     * @function getShiftcallerid
	 * @access public
	 * @returns $shiftcallerid integer
	 * Get Shiftcaller Id 
	 */
    public function getShiftcallerid() {
        return $this->shiftcallerid;
    }

    /**
	 * @function setShiftcallerid
	 * @access public
	 * @param $shiftcallerid integer 
	 * Set Shiftcaller Id
	 */
    public function setShiftcallerid($shiftcallerid) {
        $this->shiftcallerid = $shiftcallerid;
    }

    /**
     * @function getShiftid
	 * @access public
	 * @returns $shiftcallerid integer
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
