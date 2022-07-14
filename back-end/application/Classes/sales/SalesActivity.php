<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SalesActivity
 * its use for action of sales activity
 * 
 *
 * @author user YDT
 */
class SalesActivity {

    private $id;
    private $contactId;
    private $lead_id;
    private $related_to;
    private $related_type;
    private $activity_type;
    private $participant_type;
    private $entity_id;
    private $entity_type;
    private $taskId = null;
    private $subject;
    private $comment;
    private $created_by;
    private $created = DATE_TIME;
    private $archive;
    private $note_type;
    private $confidential;
    private $template_id;

    function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }

    function setContactId($contactId) {
        $this->contactId = $contactId;
    }

    function getContactId() {
        return $this->contactId;
    }

    function setLead_id($lead_id) {
        $this->lead_id = $lead_id;
    }

    function getLead_id() {
        return $this->lead_id;
    }

    function setRelated_to($related_to) {
        $this->related_to = $related_to;
    }

    function getRelated_to() {
        return $this->related_to;
    }

    function setRelated_type($related_type) {
        $this->related_type = $related_type;
    }

    function getRelated_type() {
        return $this->related_type;
    }

    function setActivity_type($activity_type) {
        $this->activity_type = $activity_type;
    }

    function getActivity_type() {
        return $this->activity_type;
    }

    function setParticipantType($participant_type) {
        $this->participant_type = $participant_type;
    }

    function getParticipantType() {
        return $this->participant_type;
    }

    function setEntity_id($entity_id) {
        $this->entity_id = $entity_id;
    }

    function getEntity_id() {
        return $this->entity_id;
    }

    function setEntity_type($entity_type) {
        $this->entity_type = $entity_type;
    }

    function getEntity_type() {
        return $this->entity_type;
    }

    function setTaskId($taskId) {
        $this->taskId = $taskId;
    }

    function getTaskId() {
        return $this->taskId;
    }

    function setSubject($subject) {
        $this->subject = $subject;
    }

    function getSubject() {
        return $this->subject;
    }

    function setComment($comment) {
        $this->comment = $comment;
    }

    function getComment() {
        return $this->comment;
    }

    function setCreated_by($created_by) {
        $this->created_by = $created_by;
    }

    function getCreated_by() {
        return $this->created_by;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function getCreated() {
        return $this->created;
    }

    function setArchive($archive) {
        $this->archive = $archive;
    }

    function getArchive() {
        return $this->archive;
    }

    function setTemplateId($template_id) {
        $this->template_id = $template_id;
    }

    function createActivity() {
        $CI = &get_instance();

        $activity = [
            "contactId" => $this->contactId,
            "lead_id" => $this->lead_id,
            "related_to" => $this->related_to,
            "related_type" => $this->related_type,
            "entity_id" => $this->entity_id,
            "activity_type" => $this->activity_type,
            "participant_type" => $this->participant_type,
            "template_id" => $this->template_id,
            "entity_type" => $this->entity_type,
            "taskId" => $this->taskId,
            "subject" => $this->subject,
            "comment" => $this->comment,
            "created_by" => $this->created_by,
            "created" => $this->created,
            "archive" => 0,
            "note_type" => $this->note_type,
            "confidential" => $this->confidential,
        ];

        return $CI->basic_model->insert_records("sales_activity", $activity, false);
    }

    function updateActivityTaskId() {
        $CI = &get_instance();

        $activity = [
            "contactId" => $this->contactId,
            "lead_id" => $this->lead_id,
            "related_to" => $this->related_to,
            "related_type" => $this->related_type,
            "entity_id" => $this->entity_id,
            "activity_type" => $this->activity_type,
            "entity_type" => $this->entity_type,
            "subject" => $this->subject,
            "comment" => $this->comment,
            "created_by" => $this->created_by,
            "created" => $this->created,
            "archive" => 0,
        ];


        return $CI->basic_model->update_records("sales_activity", $activity, ['taskId' => $this->taskId] , false);
    }



    /**
     * Get the value of note_type
     */ 
    public function getNoteType()
    {
        return $this->note_type;
    }

    /**
     * Set the value of note_type
     *
     * @return  self
     */ 
    public function setNoteType($note_type)
    {
        $this->note_type = $note_type;

        return $this;
    }

    /**
     * Get the value of Confidential
     */ 
    public function getConfidential()
    {
        return $this->confidential;
    }

    /**
     * Set the value of Confidential
     *
     * @return  self
     */ 
    public function setConfidential($confidential)
    {
        $this->confidential = $confidential;

        return $this;
    }
}
