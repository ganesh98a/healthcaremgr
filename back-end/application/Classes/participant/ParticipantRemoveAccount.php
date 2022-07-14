<?php

/*
 * Filename: ParticipantRemoveAccount.php
 * Desc: Remove account of client 
 * @author YDT <yourdevelopmentteam.com.au>
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Class: Member
 * Desc: The Member Class is a class which holds infomation about members like memberid, firstname, lastname etc.
 * The class includes variables and some methods. The methods are used to get and store information of members.
 * The visibility mode of this variables are private and the methods are made public.
 * Created: 02-08-2018
*/

class ParticipantRemoveAccount
 {
   /**
     * @var participantremoveaccountid
     * @access private
     * @vartype: integer
     */
    private $participantremoveaccountid;

    /**
     * @var participantid
     * @access private
     * @vartype: integer
     */
    private $participantid;

    /**
     * @var reason
     * @access private
     * @vartype: varchar
     */
    private $reason;

    /**
     * @var contact
     * @access private
     * @vartype: tinyint
     */
    private $contact;

	
    /**
     * @function getParticipantremoveaccountid
	 * @access public
	 * @returns $getParticipantremoveaccountid integer
	 * Get Participant Remove Account Id
	 */
    public function getParticipantremoveaccountid() {
        return $this->participantremoveaccountid;
    }

    /**
	 * @function setParticipantremoveaccountid
	 * @access public
	 * @param $participantremoveaccountid integer 
	 * Set Participant Remove Account Id
	 */
    public function setParticipantremoveaccountid($participantremoveaccountid) {
        $this->participantremoveaccountid = $participantremoveaccountid;
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
     * @function getReason
	 * @access public
	 * @returns $reason integer
	 * Get Reason 
	 */
    public function getReason() {
        return $this->reason;
    }

    /**
	 * @function setReason
	 * @access public
	 * @param $reason varchar 
	 * Set Reason 
	 */
    public function setReason($reason) {
        $this->reason = $reason;
    }

    /**
     * @function getContact
	 * @access public
	 * @returns $contact tinyint
	 * Get Contact 
	 */
    public function getContact() {
        return $this->contact;
    }

    /**
	 * @function setContact
	 * @access public
	 * @param $contact tinyint 
	 * Set Contact 
	 */
    public function setContact($contact) {
        $this->contact = $contact;
    }
}
