<?php

namespace CrmIntakeClass;

/*
 * Filename: Participant.php
 * Desc: Details of Participants
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * Class: Participant
 * Desc: Participants details like participant first name, last name, email, phone etc.
 * Variables and Getter and Setter Methods for participant class
 * Created: 02-08-2018
 */

class CrmStage {

    public function __construct() {
        $CI = & get_instance();
    }

    /**
     * @var participantid
     * @access private
     * @vartype: int
     */
    private $participantid;

    /**
     * @var intakeid
     * @access private
     * @vartype: varchar
     */
    private $intakeid;

    /**
     * @var intakenote
     * @access private
     * @vartype: varchar
     */
    private $intakenote;


    /**
     * @var stageid
     * @access private
     * @vartype: varchar
     */
    private $stageid;


    /**
     * @function getParticipantid
     * @access public
     * @returns $participantid int
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
     * @function note
     * @access public
     * @returns $note string
     * Get note
     */
    public function getNote() {
        return $this->note;
    }

    public function setNote($note) {
        $this->note = $note;
    }


    /**
     * @function getSatge
     * @access public
     * @returns $stage varchar
     * Get stage
     */
    public function getSatgeId() {
        return $this->stageId;
    }

    /**
     * @function setStage
     * @access public
     * @param $stage varchar
     * Set stage
     */
    public function setStageId($stageId) {
        $this->stageId = $stageId;
    }

    /**
    * @function getSatge
    * @access public
    * @returns $stage varchar
    * Get stage
    */
   public function getParticipantStageId() {
       return $this->stageId;
   }

   /**
    * @function setStage
    * @access public
    * @param $stage varchar
    * Set stage
    */
   public function setParticipantStageId($stageId) {
       $this->stageId = $stageId;
   }
    /**
     * @function getPortalAccess
     * @access public
     * @returns $portal_access tinyint
     * Get Portal Access
     */
    public function getPortalAccess() {
        return $this->portal_access;
    }

    /**
     * @function setPortalAccess
     * @access public
     * @param $portalAccess tinyint
     * Set Portal Access
     */
    public function setPortalAccess($portalAccess) {
        $this->portal_access = $portalAccess;
    }

    private $archive;

    public function getArchive() {
        return $this->archive;
    }

    public function setArchive($archive) {
        $this->archive = $archive;
    }

    public function AddIntakeInformation() {
        // check Here server site validation
        // Insert record method
        $CI = & get_instance();
        $CI->load->model('CrmStage_model');
        return $CI->CrmStage_model->create_intake_info($this);
    }

	public function getStageName($stage_id){
        $where = array('id'=>$stage_id);
        $CI = & get_instance();
        $CI->load->model('basic_model');
        $getStageName =  $CI->basic_model->get_record_where('crm_stage', 'name', $where);
        if(!empty($getStageName))
        return $getStageName[0]->name;
        else {
          return false;
        }
    }

    public function getParticipantName($participant_id){
        $CI = & get_instance();
        $CI->load->model('basic_model');

        $where = array('id'=>$participant_id);
        $getParticipantName =  $CI->basic_model->get_record_where('crm_participant', 'firstname,lastname', $where);
        return $getParticipantName[0]->firstname.' '.$getParticipantName[0]->lastname;
    }




}
