<?php
/**
 * This Library file holds S3 related logs creation
 */
class SMSLogs {
    protected $entityType;
    protected $messageType;
    protected $phoneNumber;
    protected $applicationID;
    protected $applicantID;
    protected $statusDesc;
    protected $created_by;
    protected $message;
    protected $aws_response;

    public function __construct() {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();
    }

    function setMessageType($type) {
        $this->messageType = $type;
    }
    function setApplicationId($id) {
        $this->applicationId = $id;
    }
    function setApplicantId($id) {
        $this->applicantId = $id;
    }
    function setphoneNumber($num) {
        $this->phoneNumber = $num;
    }

    function setEntityType($type) {
        $this->entityType = $type;
    }
    
    function setStatusDescription($status_desc) {
        $this->statusDesc = $status_desc;
    }

    function setMessage($message) {
        $this->message = $message;
    }    
    function setAwsResponse($response) {
        $this->awsResponse = $response;
    }
    function setCreatedBy($createdBy) {
        $this->createdBy = $createdBy;
    }

    function setCreatedAT($createdAt) {
        $this->createdAt = $createdAt;
    }

    function getEntityType() {
        return $this->entityType;
    }

    function getMessageType() {
        return $this->messageType;
    }

    function getApplicationId() {
        return $this->applicationId;
    }
    function getApplicantId() {
        return $this->applicantId;
    }
    function getphoneNumber() {
        return $this->phoneNumber;
    }
    function getAwsResponse() {
        return $this->awsResponse;
    }

    function getMessage() {
        return $this->message;
    }
    

    function getStatusDescription() {
        return $this->status_desc;
    }

    function getCreatedBy() {
        return $this->createdBy;
    }

    function getCreatedAT() {
        return $this->createdAt;
    }

    function createSMSLog() {
        $data = array(
            'message_sent_type' => $this->messageType,
            'entity_type' => $this->entityType,
            'phone_number' => $this->phoneNumber,
            'message' => $this->message,
            'applicant_id' => $this->applicantId,
            'application_id' => $this->applicationId,
            'status_desc' => $this->statusDesc,
            'aws_response' => $this->awsResponse ?? NULL,
            'created_by' => $this->createdBy ?? NULL,
            'created_at' => DATE_TIME
        );        
        $this->CI->db->insert(TBL_PREFIX . 'sms_logs', $data);
    }

}
