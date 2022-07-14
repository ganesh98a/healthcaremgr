<?php

/*
 * Filename: ParticipantGoal.php
 * Desc: Participant Goal details like what to do, start date end end details
 * @author YDT <yourdevelopmentteam.com.au>
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Class: ParticipantGoal
 * Desc: Variables for Participant Goals like start-end date are handled by this class.
 * Created: 02-08-2018
*/

class ParticipantGoal 
  {
    /**
     * @var participantgoalid
     * @access private
     * @vartype: integer
     */
    private $participantgoalid;
   
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
     * @var end_date
     * @access private
     * @vartype: varchar
     */
    private $end_date;
	
	
    /**
     * @function getParticipantgoalid
	 * @access public
	 * @returns $participantgoalid integer
	 * Get Participant Goal Id
	 */
    public function getParticipantgoalid() {
        return $this->participantgoalid;
    }

    /**
	 * @function setParticipantgoalid
	 * @access public
	 * @param $participantgoalid integer 
	 * Set Participant Goal Id
	 */
    public function setParticipantgoalid($participantgoalid) {
        $this->participantgoalid = $participantgoalid;
    }

    /**
     * @function getParticipantgoalid
	 * @access public
	 * @returns $participantgoalid integer
	 * Get Participant Goal Id
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
     * @function getEndDate
	 * @access public
	 * @returns $title varchar
	 * Get End Date
	 */
    public function getEndDate() {
        return $this->end_date;
    }

    /**
	 * @function setEndDate
	 * @access public
	 * @param $endDate varchar 
	 * Set End Date
	 */
    public function setEndDate($endDate) {
        $this->end_date = $endDate;
    }
  }