<?php

/*
 * Filename: ShiftLocation.php
 * Desc: Gives Location of Shift
 * @author YDT <yourdevelopmentteam.com.au>
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Class: ShiftLocation
 * Desc: Variables($address, $state, $postal) and Getter and Setter Methods for house
 * Created: 06-08-2018
*/

class ShiftLocation
   {
    /**
     * @var shiftlocationid
     * @access private
     * @vartype: integer
     */
    private $shiftlocationid;

    /**
     * @var shiftid
     * @access private
     * @vartype: integer
     */
    private $shiftid;

    /**
     * @var address
     * @access private
     * @vartype: varchar
     */
    private $address;

    /**
     * @var state
     * @access private
     * @vartype: tinyint
     */
    private $state;

    /**
     * @var postal
     * @access private
     * @vartype: varchar
     */
    private $postal;
	

    /**
     * @function getShiftlocationid
	 * @access public
	 * @returns $shiftlocationid integer
	 * Get Shiftlocation Id 
	 */
    public function getShiftlocationid() {
        return $this->shiftlocationid;
    }
	
    /**
	 * @function setShiftlocationid
	 * @access public
	 * @param $shiftlocationid integer 
	 * Set Shiftlocation Id
	 */
    public function setShiftlocationid($shiftlocationid) {
        $this->shiftlocationid = $shiftlocationid;
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
     * @function getAddress
	 * @access public
	 * @returns $address varchar
	 * Get Address
	 */
    public function getAddress() {
        return $this->address;
    }

    /**
	 * @function setAddress
	 * @access public
	 * @param $address varchar 
	 * Set Address
	 */
    public function setAddress($address) {
        $this->address = $address;
    }

    /**
     * @function getState
	 * @access public
	 * @returns $state tinyint
	 * Get State
	 */
    public function getState() {
        return $this->state;
    }

    /**
	 * @function setState
	 * @access public
	 * @param $state tinyint 
	 * Set State
	 */
    public function setState($state) {
        $this->state = $state;
    }

    /**
     * @function getPostal
	 * @access public
	 * @returns $postal varchar
	 * Get Postal
	 */
    public function getPostal() {
        return $this->postal;
    }

    /**
	 * @function setPostal
	 * @access public
	 * @param $postal varchar 
	 * Set Postal
	 */
    public function setPostal($postal) {
        $this->postal = $postal;
    }
}
