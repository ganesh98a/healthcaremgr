<?php

namespace classParticipantCareRequirement;

/*
 * Filename: ParticipantCareRequirement.php
 * Desc: Requirement details of Participants for care, communication, interpreter details etc.
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * Class: ParticipantCareRequirement
 * Desc: This class is for handling participant requirement
 * The variables like $participantid, $diagonis_primary, $diagonis_secondary, $communication are used.
 * Created: 02-08-2018
 */

class ParticipantCareRequirement {

    /**
     * @var int id
     * @access private
     * @vartype integer
     */
    private $id;

    /**
     * @var participantid
     * @access private
     * @vartype: integer
     */
    private $participantid;

    /**
     * @var diagnosis_primary
     * @access private
     * @vartype: text
     */
    private $diagnosis_primary;

    /**
     * @var diagnosis_secondary;
     * @access private
     * @vartype: text
     */
    private $diagnosis_secondary;

    /**
     * @var participant_care;
     * @access private
     * @vartype: text
     */
    private $participant_care;

    /**
     * @var cognition
     * @access private
     * @vartype: varchar
     */
    private $cognition;

    /**
     * @var communication
     * @access private
     * @vartype: varchar
     */
    private $communication;

    /**
     * @var communication
     * @access private
     * @vartype: tinyint
     */
    private $english;

    /**
     * @var preferred_language
     * @access private
     * @vartype: varchar
     */
    private $preferred_language;
    private $preferred_language_other;

    /**
     * @var linguistic_interpreter
     * @access private
     * @vartype: tinyint
     */
    private $linguistic_interpreter;

    /**
     * @var hearing_interpreter
     * @access private
     * @vartype: tinyint
     */
    private $hearing_interpreter;

    /**
     * @var$preferred_language
     * @access private
     * @vartype: varchar
     */
    private $require_assistance_other;

    /**
     * @var preferred_language
     * @access private
     * @vartype: varchar
     */
    private $support_require_other;
    private $require_mobility_other;

    /**
     * @function getId
     * @access public
     * @returns $id integer
     * Get Participant Care Requirement Id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @function setId
     * @access public
     * @param $id integer 
     * Set Participant Care Requirement Id
     */
    public function setId($id) {
        $this->id = $id;
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
     * @function setId
     * @access public
     * @param $id integer 
     * Set Participant Care Requirement Id
     */
    public function setParticipantid($participantid) {
        $this->participantid = $participantid;
    }

    /**
     * @function getDiagnosisPrimary
     * @access public
     * @returns $diagnosis_primary text
     * Get Diagnosis Primary
     */
    public function getDiagnosisPrimary() {
        return $this->diagnosis_primary;
    }

    /**
     * @function setId
     * @access public
     * @param $diagnosisPrimary text 
     * Set Diagnosis Primary
     */
    public function setDiagnosisPrimary($diagnosisPrimary) {
        $this->diagnosis_primary = $diagnosisPrimary;
    }

    /**
     * @function getDiagnosisPrimary
     * @access public
     * @returns $diagnosis_primary text
     * Get Diagnosis Primary
     */
    public function getDiagnosisSecondary() {
        return $this->diagnosis_secondary;
    }

    /**
     * @function setId
     * @access public
     * @param $diagnosisSecondary text 
     * Set Diagnosis Secondary
     */
    public function setDiagnosisSecondary($diagnosisSecondary) {
        $this->diagnosis_secondary = $diagnosisSecondary;
    }

    /**
     * @function getParticipantCare
     * @access public
     * @returns $participant_care text
     * Get Participant Care
     */
    public function getParticipantCare() {
        return $this->participant_care;
    }

    /**
     * @function setParticipantCare
     * @access public
     * @param $participantCare text 
     * Set Participant Care
     */
    public function setParticipantCare($participantCare) {
        $this->participant_care = $participantCare;
    }

    /**
     * @function getCognition
     * @access public
     * @returns $cognition text
     * Get Cognition 
     */
    public function getCognition() {
        return $this->cognition;
    }

    /**
     * @function setCognition
     * @access public
     * @param $cognition text 
     * Set Cognition
     */
    public function setCognition($cognition) {
        $this->cognition = $cognition;
    }

    /**
     * @function getCommunication
     * @access public
     * @returns $communication varchar
     * Get Communication
     */
    public function getCommunication() {
        return $this->communication;
    }

    /**
     * @function setCommunication
     * @access public
     * @param $communication varchar 
     * Set Communication
     */
    public function setCommunication($communication) {
        $this->communication = $communication;
    }

    /**
     * @function getEnglish
     * @access public
     * @returns $english tinyint
     * Get English
     */
    public function getEnglish() {
        return $this->english;
    }

    /**
     * @function setEnglish
     * @access public
     * @param $english tinyint 
     * Set English
     */
    public function setEnglish($english) {
        $this->english = $english;
    }

    /**
     * @function getPreferredLanguage
     * @access public
     * @returns $english varchar
     * Get Preferred Language
     */
    public function getPreferredLanguage() {
        return $this->preferred_language;
    }

    /**
     * @function setPreferredLanguage
     * @access public
     * @param $preferredLanguage varchar 
     * Set Preferred Language
     */
    public function setPreferredLanguage($preferredLanguage) {
        $this->preferred_language = $preferredLanguage;
    }

    public function getPreferredLanguageOther() {
        return $this->preferred_language_other;
    }

    /**
     * @function setPreferredLanguage
     * @access public
     * @param $preferredLanguage varchar 
     * Set Preferred Language
     */
    public function setPreferredLanguageOther($preferredLanguageOther) {
        $this->preferred_language_other = $preferredLanguageOther;
    }

    /**
     * @function getLinguisticInterpreter
     * @access public
     * @returns $linguistic_interpreter tinyint
     * Get Linguistic Interpreter
     */
    public function getLinguisticInterpreter() {
        return $this->linguistic_interpreter;
    }

    /**
     * @function setLinguisticInterpreter
     * @access public
     * @param $linguisticInterpreter tinyint 
     * Set Linguistic Interpreter
     */
    public function setLinguisticInterpreter($linguisticInterpreter) {
        $this->linguistic_interpreter = $linguisticInterpreter;
    }

    /**
     * @function getHearingInterpreter
     * @access public
     * @returns $hearing_interpreter tinyint
     * Get Hearing Interpreter
     */
    public function getHearingInterpreter() {
        return $this->hearing_interpreter;
    }

    /**
     * @function setLinguisticInterpreter
     * @access public
     * @param $hearingInterpreter tinyint 
     * Set Hearing Interpreter
     */
    public function setHearingInterpreter($hearingInterpreter) {
        $this->hearing_interpreter = $hearingInterpreter;
    }

    /**
     * @function getRequireAssistanceOther
     * @access public
     * @returns $require_assistance_other varchar
     * Get Require Assistance Other
     */
    public function getRequireAssistanceOther() {
        return $this->require_assistance_other;
    }

    /**
     * @function setRequireAssistanceOther
     * @access public
     * @param $requireAssistanceOther varchar 
     * Set Require Assistance Other
     */
    public function setRequireAssistanceOther($requireAssistanceOther) {
        $this->require_assistance_other = $requireAssistanceOther;
    }

    /**
     * @function getSupportRequireOther
     * @access public
     * @returns $support_require_other varchar
     * Get Support Require Other
     */
    public function getSupportRequireOther() {
        return $this->support_require_other;
    }

    /**
     * @function setRequireAssistanceOther
     * @access public
     * @param $requireAssistanceOther varchar 
     * Set Require Assistance Other
     */
    public function setSupportRequireOther($supportRequireOther) {
        $this->support_require_other = $supportRequireOther;
    }

    function setRequire_mobility_other($require_mobility_other) {
        $this->require_mobility_other = $require_mobility_other;
    }

    function getRequire_mobility_other() {
        return $this->require_mobility_other;
    }

    public function create_care_requirement() {
        $Ci = & get_instance();

        $care_data = array(
            'participantId' => $this->participantid,
            'cognition' => $this->cognition,
            'communication' => $this->communication,
            'english' => $this->english,
            'preferred_language' => $this->preferred_language,
            'linguistic_interpreter' => $this->linguistic_interpreter,
            'hearing_interpreter' => $this->hearing_interpreter,
            'require_assistance_other' => $this->require_assistance_other,
            'support_require_other' => $this->support_require_other,
            'require_mobility_other' => $this->require_mobility_other
        );

        if ($this->preferred_language == 11) {
            $care_data['preferred_language_other'] = $this->preferred_language_other;
        }

        $Ci->basic_model->insert_records('participant_care_requirement', $care_data, $multiple = FALSE);
    }

}
