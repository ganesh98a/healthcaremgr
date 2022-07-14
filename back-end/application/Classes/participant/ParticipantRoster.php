<?php

/*
 * Filename: ParticipantRoster.php
 * Desc: This file is for creating parrticipant roster and status details like confirm or cancelled status of participant.
 * @author YDT <yourdevelopmentteam.com.au>
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Class: ParticipantRoster
 * Desc: Variables and methods for maintaining roster details of particpant
 * Created: 02-08-2018
*/

class ParticipantRoster
 {
    /**
     * @var participantrosterid
     * @access private
     * @vartype: integer
     */
    private $participantrosterid;

    /**
     * @var participantid
     * @access private
     * @vartype: integer
     */
    private $participantid;

    /**
     * @var title
     * @access private
     * @vartype: varchar
     */
    private $title;

    /**
     * @var is_default
     * @access private
     * @vartype: tinyint
     */
    private $is_default;

    /**
     * @var start_date
     * @access private
     * @vartype: timestamp
     */
    private $start_date;

    /**
     * @var end_date
     * @access private
     * @vartype: timestamp
     */
    private $end_date;

    /**
     * @var status
     * @access private
     * @vartype: tinyint
     */
    private $status;
	

    /**
     * @function getParticipantrosterid
	 * @access public
	 * @returns $participantrosterid integer
	 * Get Participant Roster Id
	 */
    public function getParticipantrosterid() {
        return $this->participantrosterid;
    }

    /**
	 * @function setParticipantrosterid
	 * @access public
	 * @param $participantrosterid integer 
	 * Set Participant Roster Id
	 */
    public function setParticipantrosterid($participantrosterid) {
        $this->participantrosterid = $participantrosterid;
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
	 * @function setParticipantrosterid
	 * @access public
	 * @param $participantid integer 
	 * Set Participant Id
	 */
    public function setParticipantid($participantid) {
        $this->participantid = $participantid;
    }

    /**
     * @function getTitle
	 * @access public
	 * @returns $title integer
	 * Get Title
	 */
    public function getTitle() {
        return $this->title;
    }

    /**
	 * @function setTitle
	 * @access public
	 * @param $title varchar 
	 * Set Title
	 */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @function getIsDefault
	 * @access public
	 * @returns $is_default tinyint
	 * Get is_default
	 */
    public function getIsDefault() {
        return $this->is_default;
    }

    /**
	 * @function setTitle
	 * @access public
	 * @param $isDefault tinyint 
	 * Set isDefault
	 */
    public function setIsDefault($isDefault) {
        $this->is_default = $isDefault;
    }

   
    public function getStartDate() {
        return $this->start_date;
    }

    /**
     * @function setStartDate
	 * @access public
	 * @returns $start_date timestamp
	 * Get Start Date
	 */
    public function setStartDate($startDate) {
        $this->start_date = $startDate;
    }

    /**
	 * @function getEndDate
	 * @access public
	 * @param $isDefault timestamp 
	 * Set End Date
	 */
    public function getEndDate() {
        return $this->end_date;
    }

    /**
     * @function setStartDate
	 * @access public
	 * @returns $start_date timestamp
	 * Get Start Date
	 */
    public function setEndDate($endDate) {
        $this->end_date = $endDate;
    }

     /**
	 * @function getStatus
	 * @access public
	 * @param $isDefault tinyint 
	 * Set Status
	 */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @function setStatus
	 * @access public
	 * @returns $status tinyint
	 * Get Status
	 */
    public function setStatus($status) {
        $this->status = $status;
    }
}
