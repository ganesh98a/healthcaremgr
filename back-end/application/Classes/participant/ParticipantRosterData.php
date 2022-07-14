<?php

/*
 * Filename: ParticipantRosterData.php
 * Desc: Participant Scheduling details according to weeks. There start time and end time details  
 * @author YDT <yourdevelopmentteam.com.au>
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Class: ParticipantRosterData
 * Desc: Variables and methods for handling roster details.
 * Created: 02-08-2018
*/

class ParticipantRosterData
  {
    /**
     * @var participantrosterdataid
     * @access private
     * @vartype: integer
     */
    private $participantrosterdataid;

    /**
     * @var rosterid
     * @access private
     * @vartype: integer
     */
    private $rosterid;

    /**
     * @var week_day
     * @access private
     * @vartype: timestamp
     */
    private $week_day;

    /**
     * @var start_time
     * @access private
     * @vartype: timestamp
     */
    private $start_time;

    /**
     * @var end_time
     * @access private
     * @vartype: timestamp
     */
    private $end_time;

    /**
     * @var week_number
     * @access private
     * @vartype: tinyint
     */
    private $week_number;
	
    /**
     * @function getParticipantrosterdataid
	 * @access public
	 * @returns $participantrosterdataid integer
	 * Get Participant Roster Data Id
	 */
    public function getParticipantrosterdataid() {
        return $this->participantrosterdataid;
    }

    /**
	 * @function setParticipantrosterdataid
	 * @access public
	 * @param $participantrosterdataid integer 
	 * Set Participant Roster Data Id
	 */
    public function setParticipantrosterdataid($participantrosterdataid) {
        $this->participantrosterdataid = $participantrosterdataid;
    }

    /**
     * @function getRosterid
	 * @access public
	 * @returns $rosterid integer
	 * Get Roster Id
	 */
    public function getRosterid() {
        return $this->rosterid;
    }

    /**
	 * @function setRosterid
	 * @access public
	 * @param $rosterid integer 
	 * Set Roster Id
	 */
    public function setRosterid($rosterid) {
        $this->rosterid = $rosterid;
    }

    /**
     * @function getWeekDay
	 * @access public
	 * @returns $rosterid tinyint
	 * Get Week Day
	 */
    public function getWeekDay() {
        return $this->week_day;
    }

    /**
	 * @function setWeekDay
	 * @access public
	 * @param $weekDay tinyint 
	 * Set Week Day
	 */
    public function setWeekDay($weekDay) {
        $this->week_day = $weekDay;
    }

    /**
     * @function getStartTime
	 * @access public
	 * @returns $start_time timestamp
	 * Get Start Time
	 */
    public function getStartTime() {
        return $this->start_time;
    }

    /**
	 * @function setStartTime
	 * @access public
	 * @param $startTime timestamp 
	 * Set Start Time
	 */
    public function setStartTime($startTime) {
        $this->start_time = $startTime;
    }

    /**
     * @function getEndTime
	 * @access public
	 * @returns $end_time timestamp
	 * Get End Time
	 */
    public function getEndTime() {
        return $this->end_time;
    }

    /**
	 * @function setEndTime
	 * @access public
	 * @param $endTime timestamp 
	 * Set End Time
	 */
    public function setEndTime($endTime) {
        $this->end_time = $endTime;
    }

   
    /**
     * @function getWeekNumber
	 * @access public
	 * @returns $week_number tinyint
	 * Get Week Number
	 */
    public function getWeekNumber() {
        return $this->week_number;
    }

     /**
	 * @function setWeekNumber
	 * @access public
	 * @param $endTime tinyint 
	 * Set Week Number
	 */
    public function setWeekNumber($weekNumber) {
        $this->week_number = $weekNumber;
    }
}

