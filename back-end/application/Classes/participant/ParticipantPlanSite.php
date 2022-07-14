<?php

/*
 * Filename: ParticipantPlanSite.php
 * Desc: Participants plan site details like street, city, postal etc.  
 * @author YDT <yourdevelopmentteam.com.au>
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Class: ParticipantPlanSite
 * Desc: Visibility mode of variales are private and methods are public.
 * Varibles and methods for handling plan site details of participants
 * Created: 02-08-2018 
*/

class ParticipantPlanSite
{
    /**
     * @var participantplanid
     * @access private
     * @vartype: integer
     */
    private $participantplanid;

    /**
     * @var participantid
     * @access private
     * @vartype: integer
     */
    private $participantid;

    /**
     * @var address
     * @access private
     * @vartype: varchar
     */
    private $address;

    /**
     * @var city
     * @access private
     * @vartype: varchar
     */
    private $city;
	
    /**
     * @var postal
     * @access private
     * @vartype: varchar
     */
    private $postal;

    /**
     * @var state
     * @access private
     * @vartype: tinyint
     */
    private $state;
	

    /**
     * @function getParticipantplanid
	 * @access public
	 * @returns $participantplanid integer
	 * Get Participant Plan Id
	 */
    public function getParticipantplanid() {
        return $this->participantplanid;
    }
  
    /**
	 * @function setParticipantplanid
	 * @access public
	 * @param $participantplanid integer 
	 * Set Participant Plan Id
	 */
    public function setParticipantplanid($participantplanid) {
        $this->participantplanid = $participantplanid;
    }

    /**
     * @function getParticipantid
	 * @access public
	 * @returns $participantid integer
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

   
    public function getCity() {
        return $this->city;
    }

    /**
     * @function setCity
	 * @access public
	 * @returns $city varchar
	 * Get City
	 */
    public function setCity($city) {
        $this->city = $city;
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
}
