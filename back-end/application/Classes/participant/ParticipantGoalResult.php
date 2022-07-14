<?php

/*
 * Filename: ParticipantGoalResult.php
 * Desc: The Particiapnt Goal Result file shows that the goal of Particiapant is achieved or not.
 * @author YDT <yourdevelopmentteam.com.au>
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Class: ParticipantGoalResult
 * Desc: The ParticipantGoalResult class has paricipant id, their goal id, created and rating and its stter and getter methods.
 * Created: 01-08-2018
*/
class ParticipantGoalResult
 {
    /**
     * @var participantid
     * @access private
     * @vartype: int
     */
    private $participantid;

   /**
     * @var participantid
     * @access private
     * @vartype: int
     */
    private $goalid;

    /**
     * @var participantid
     * @access private
     * @vartype: varchar
     */
    private $created;

    /**
     * @var participantid
     * @access private
     * @vartype: smallint
     */
    private $rating;

    /**
     * @function getParticipantid
     * @access public
	 * returns $PreferredName integer
     * Get getParticipant Id
     */
    public function getParticipantid() {
        return $this->participantid;
    }

    /**
	 * @function setParticipantid
     * @access public
	 * @param $participantid varchar 
	 * Set Participant Id
     */ 
    public function setParticipantid($participantid) {
        $this->participantid = $participantid;
    }

    /**
     * @function getGoalid
     * @access public
	 * returns $goalid integer
     * Get Goal Id
     */
    public function getGoalid() {
        return $this->goalid;
    }

    /**
	 * @function setGoalid
     * @access public
	 * @param $goalid integer 
	 * Set Goal Id
     */ 
    public function setGoalid($goalid) {
        $this->goalid = $goalid;
    }

    /**
     * @function getCreated
     * @access public
	 * returns $PreferredName varchar
     * Get Created    
	 */
    public function getCreated() {
        return $this->created;
    }
	
    /**
	 * @function setCreated
     * @access public
	 * @param $created varchar 
	 * Set Created
     */ 
    public function setCreated($created) {
        $this->created = $created;
    }

    /**
     * @function getRating
     * @access public
	 * returns $rating integer
     * Get Rating    
	 */
    public function getRating() {
        return $this->rating;
    }

   /**
	 * @function setRating
     * @access public
	 * @param $rating smallint 
	 * Set
	 */
    public function setRating($rating) {
        $this->rating = $rating;
    }
}
