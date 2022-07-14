<?php

/*
 * DocumentAttachment is used to maintain the attachment of file
 * Desc: Docs details of attachment like doc_category, related_to etc.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class DocumentAttachment {

    const DOCUMENT_STATUS_SUBMITTED = 0;
    const DOCUMENT_STATUS_VALID = 1;
    const DOCUMENT_STATUS_INVALID = 2;
    const DOCUMENT_STATUS_EXPIRED = 3;
    const DOCUMENT_STATUS_DRAFT = 4;

    const ENTITY_TYPE_APPLICANT = 1;
    const ENTITY_TYPE_MEMBER = 2;
    const ENTITY_TYPE_PARTICIPANTS = 3;

    const CREATED_PORTAL_HCM = 1;
    const CREATED_PORTAL_MEMBER = 2;

    const RELATED_TO_RECRUITMENT = 1;
    const RELATED_TO_MEMBER = 2;
    const RELATED_TO_PARTICIPANTS = 3;

    const ARCHIVE_DOCMUENT = 1;

    /**
     * @var applicantionId
     * @access private
     * @vartype: int
     */
    private $applicationId;

    /**
     * @var memberId
     * @access private
     * @vartype: int
     */
    private $memberId;
   
    /**
     * @var participantId
     * @access private
     * @vartype: int
     */
    private $participantId;

    /**
     * @var applicantId
     * @access private
     * @vartype: int
     */
    private $applicantId;

    /**
     * @var stageId
     * @access private
     * @vartype: int
     */
    private $stageId;

    /**
     * @var isMainStage
     * @access private
     * @vartype: int
     */
    private $isMainStage;

    /**
     * @var draftContractType
     * @access private
     * @vartype: int
     */
    private $draftContractType;

    /**
     * @var uploadedByApplicant
     * @access private
     * @vartype: int
     */
    private $uploadedByApplicant;

    /**
     * @var memberMoveArchive
     * @access private
     * @vartype: int
     */
    private $memberMoveArchive;

    /**
     * @var archive
     * @access private
     * @vartype: int
     */
    private $archive;

    /**
     * @var entityId
     * @access private
     * @vartype: int
     */
    private $entityId;

    /**
     * @var entityType
     * @access private
     * @vartype: int
     */
    private $entityType;

    /**
     * @var relatedTo
     * @access private
     * @vartype: int
     */
    private $relatedTo;

    /**
     * @var docCategoryId
     * @access private
     * @vartype: int
     */
    private $docTypeId;

     /**
     * @var documentStatus
     * @access private
     * @vartype: int
     */
    private $documentStatus = self::DOCUMENT_STATUS_SUBMITTED;

    /**
     * @var issueDate
     * @access private
     * @vartype: date
     */
    private $issueDate = null;

    /**
     * @var expiryDate
     * @access private
     * @vartype: date
     */
    private $expiryDate = null;

    /**
     * @var referenceNumber
     * @access private
     * @vartype: string
     */
    private $referenceNumber;

    /**
     * @var createdPortal
     * @access private
     * @vartype: string
     */
    private $createdPortal;

    /**
     * @var createdAt
     * @access private
     * @vartype: date
     */
    private $createdAt = DATE_TIME;

    /**
     * @var createdBy
     * @access private
     * @vartype: int
     */
    private $createdBy;

    /**
     * @var updatedAt
     * @access private
     * @vartype: date|null
     */
    private $updatedAt = null;

    /**
     * @var updatedBy
     * @access private
     * @vartype: int
     */
    private $updatedBy;

    /**
     * @var docId
     * @access private
     * @vartype: int
     */
    private $docId;

    /**
     * @var fileName
     * @access private
     * @vartype: string
     */
    private $fileName;

    /**
     * @var fileType
     * @access private
     * @vartype: string
     */
    private $fileType;

    /**
     * @var filePath
     * @access private
     * @vartype: string
     */
    private $filePath;

    /**
     * @var rawName
     * @access private
     * @vartype: string
     */
    private $rawName;

    /**
     * @var fileExt
     * @access private
     * @vartype: string
     */
    private $fileExt;

    /**
     * @var fileSize
     * @access private
     * @vartype: string
     */
    private $fileSize;

    /**
     * @var awsResponse
     * @access private
     * @vartype: string
     */
    private $awsResponse;

    /**
     * @var awsFileVersionId
     * @access private
     * @vartype: string
     */
    private $awsFileVersionId;

    /**
     * @var awsObjectUri
     * @access private
     * @vartype: string
     */
    private $awsObjectUri;

    /**
     * @var awsUploadedFlag
     * @access private
     * @vartype: int
     */
    private $awsUploadedFlag;

    /**
     * @var member_id
     * @access private
     * @vartype: int
     */
    private $member_id;

    /**
     * @var updatedByType
     * @access private
     * @vartype: int
     */
    private $updatedByType;

    private $license_type;
    private $issuing_state;
    private $vic_conversion_date;
    private $applicant_specific;

    private $visa_category;
    private $visa_category_type;

    static function getConstant($var)
    {
        return constant('self::'. $var);
    }

    public function getApplicantId() {
        return $this->applicantId;
    }

    public function setApplicantId($applicantId) {
        $this->applicantId = $applicantId;
    }

     public function getApplicantionId() {
        return $this->applicationId;
    }

    public function setApplicationId($applicationId) {
        $this->applicationId = $applicationId;
    }
   
    public function getStage() {
        return $this->stageId;
    }

    public function setStage($stageId) {
        $this->stageId = $stageId;
    }
   
    public function getIsMainStage() {
        return (int)$this->isMainStage;
    }

    public function setIsMainStage($isMainStage) {
        $this->isMainStage = (int) $isMainStage;
    }

    public function getDraftContractType() {
        return (int) $this->draftContractType;
    }

    public function setDraftContractType($draftContractType) {
        $this->draftContractType = (int) $draftContractType;
    }

    public function getUploadedByApplicant() {
        return (int) $this->uploadedByApplicant;
    }

    public function setUploadedByApplicant($uploadedByApplicant) {
        $this->uploadedByApplicant = (int) $uploadedByApplicant;
    }

    public function getMemberMoveArchive() {
        return (int) $this->memberMoveArchive;
    }

    public function setMemberMoveArchive($memberMoveArchive) {
        $this->memberMoveArchive = (int) $memberMoveArchive;
    }

    public function getArchive() {
        return (int) $this->archive;
    }

    public function setArchive($archive) {
        $this->archive = (int) $archive;
    }

    public function getEntityId() {
        return (int) $this->entityId;
    }

    public function setEntityId($entityId) {
        $this->entityId = (int) $entityId;
    }

    public function getEntityType() {
        return (int) $this->entityType;
    }

    public function setEntityType($entityType) {
        $this->entityType = (int) $entityType;
    }

    public function getRelatedTo() {
        return (int) $this->relatedTo;
    }

    public function setRelatedTo($relatedTo) {
        $this->relatedTo = (int) $relatedTo;
    }

    public function getDocTypeId() {
        return (int) $this->docTypeId;
    }

    public function setDocTypeId($docTypeId) {
        $this->docTypeId = (int) $docTypeId;
    }

    public function getIssueDate() {
        return $this->issueDate;
    }

    public function setIssueDate($issueDate) {
        $this->issueDate = $issueDate;
    }

    public function getExpiryDate() {
        return $this->expiryDate;
    }

    public function setExpiryDate($expiryDate) {
        $this->expiryDate = $expiryDate;
    }

    public function getReferenceNumber() {
        return $this->referenceNumber;
    }

    public function setReferenceNumber($referenceNumber) {
        $this->referenceNumber = $referenceNumber;
    }

    public function getDocumentStatus() {
        return (int) $this->documentStatus;
    }

    public function setDocumentStatus($documentStatus) {
        $this->documentStatus = (int) $documentStatus;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;
    }

    public function getCreatedBy() {
        return (int) $this->createdBy;
    }

    public function setCreatedBy($createdBy) {
        $this->createdBy = (int) $createdBy;
    }
    
    public function getCreatedPortal() {
        return (int) $this->createdPortal;
    }

    public function setCreatedPortal($createdPortal) {
        $this->createdPortal = (int) $createdPortal;
    }

    public function getUpdatedBy() {
        return (int) $this->updatedBy;
    }

    public function setUpdatedBy($updatedBy) {
        $this->updatedBy = (int) $updatedBy;
    }

    public function getDocId() {
        return (int) $this->docId;
    }

    public function setDocId($docId) {
        $this->docId = (int) $docId;
    }

    public function getFileName() {
        return $this->fileName;
    }

    public function setFileName($fileName) {
        $this->fileName = $fileName;
    }

    public function getFileType() {
        return $this->fileType;
    }

    public function setFileType($fileType) {
        $this->fileType = $fileType;
    }

    public function getFilePath() {
        return $this->filePath;
    }

    public function setFilePath($filePath) {
        $this->filePath = $filePath;
    }

    public function getRawName() {
        return $this->rawName;
    }

    public function setRawName($rawName) {
        $this->rawName = $rawName;
    }

    public function getFileExt() {
        return $this->fileExt;
    }

    public function setFileExt($fileExt) {
        $this->fileExt = $fileExt;
    }

    public function getFileSize() {
        return $this->fileSize;
    }

    public function setFileSize($fileSize) {
        $this->fileSize = $fileSize;
    }

    public function getAwsObjectUri() {
        return $this->awsObjectUri;
    }

    public function setAwsObjectUri($awsObjectUri) {
        $this->awsObjectUri = $awsObjectUri;
    }

    public function getAwsResponse() {
        return $this->awsResponse;
    }

    public function setAwsResponse($awsResponse) {
        $this->awsResponse = $awsResponse;
    }

    public function getAwsFileVersionId() {
        return $this->awsFileVersionId;
    }

    public function setAwsFileVersionId($awsFileVersionId) {
        $this->awsFileVersionId = $awsFileVersionId;
    }

    public function getAwsUploadedFlag() {
        return $this->awsUploadedFlag;
    }

    public function setAwsUploadedFlag($awsUploadedFlag) {
        $this->awsUploadedFlag = $awsUploadedFlag;
    }

    public function getMemberId() {
        return $this->memberId;
    }

    public function setMemberId($memberId) {
        $this->memberId = $memberId;
    }

    public function getParticipantId() {
        return $this->participantId;
    }

    public function setParticipantId($participantId) {
        $this->participantId = $participantId;
    }

    public function getUpdatedByType() {
        return $this->updatedByType;
    }

    public function setUpdatedByType($updatedByType) {
        $this->updatedByType = $updatedByType;
    }

    public function getVisaCategory() {
        return $this->visaCategory;
    }

    public function setVisaCategory($visaCategory) {
        $this->visa_category = $visaCategory;
    }

    public function getVisaCategoryType() {
        return $this->VisaCategoryType;
    }

    public function setVisaCategoryType($visaCategoryType) {
        $this->visa_category_type = $visaCategoryType;
    }

    /*
     * Check duplicate document attachment
     */
    function checkDublicateDocAttachment() {
        $ci = & get_instance();
        $where = array(
            'tdap.raw_name' => $this->rawName,
            'tda.applicant_id' => $this->applicantId,
            'tda.archive' => $this->archive,
        );

        $ci->db->select(['*']);
        $ci->db->from('tbl_document_attachment as tda');
        $ci->db->join('tbl_document_attachment_property as tdap', 'tda.id = tdap.doc_id AND tdap.archive = 0', 'INNER');
        $ci->db->where($where);
        $query = $ci->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->row();

        if (!empty($result)) {
            $return = array('status' => false, 'warn' => 'Warning: Document with this name already exist, we are changing its name.');
        } else {
            $return = array('status' => true);
        }
        return $return;
    }

    /**
     * Create Document Attachment
     */
    public function createDocumentAttachment() {
        $ci = & get_instance();
        
        $member_id = NULL;
        if ($this->memberId != 'null' && $this->memberId != NULL && $this->memberId != '') {
            $member_id = $this->memberId; 
        }       
        $insert_ary = array(
            'application_id' => $this->applicationId ?? 0,
            'applicant_id' => $this->applicantId,
            'stage' => $this->stageId ?? 0,
            'archive' => $this->archive,
            'doc_type_id' => $this->docTypeId,
            'is_main_stage_label' => $this->isMainStage ?? 0,
            'uploaded_by_applicant' => $this->uploadedByApplicant ?? 0,
            'expiry_date' => $this->expiryDate,
            'document_status' => $this->documentStatus,
            'reference_number' => $this->referenceNumber,
            'entity_id' => $this->entityId,
            'entity_type' => $this->entityType,
            'issue_date' => $this->issueDate,
            'related_to' => $this->relatedTo,
            'created_portal' => $this->createdPortal,
            'created_at' => $this->createdAt,
            'created_by' => $this->createdBy,
            'member_id' => $member_id,
            'license_type' => $this->license_type,
            'issuing_state' => $this->issuing_state,
            'vic_conversion_date' => $this->vic_conversion_date,
            'applicant_specific' => $this->applicant_specific,
            'visa_category' => $this->visa_category,
            'visa_category_type' => $this->visa_category_type
        );
        
        return $documentId = $ci->basic_model->insert_records('document_attachment', $insert_ary, $multiple = FALSE);
    }

     /**
     * Create Document Attachment Property
     */
    public function createDocumentAttachmentProperty() {
        $ci = & get_instance();
        
        $insert_ary = array(
            'doc_id' => $this->docId,
            'file_name' => $this->fileName,
            'file_type' => $this->fileType,
            'file_path' => $this->filePath,
            'raw_name' => $this->rawName,
            'file_ext' => $this->fileExt,
            'file_size' => $this->fileSize,
            'aws_object_uri' => $this->awsObjectUri,
            'aws_response' => $this->awsResponse,
            'aws_file_version_id' => $this->awsFileVersionId,
            'aws_uploaded_flag' => $this->awsUploadedFlag,
            'created_at' => $this->createdAt,
            'created_by' => $this->createdBy,
        );

        return $rows = $ci->basic_model->insert_records('document_attachment_property', $insert_ary, $multiple = FALSE);
    }

    /**
     * Create Document Attachment
     */
    public function updateDocumentAttachment() {
        $ci = & get_instance();

        $update_ary = array(
            'doc_type_id' => $this->docTypeId,
            'expiry_date' => $this->expiryDate,
            'document_status' => $this->documentStatus,
            'reference_number' => $this->referenceNumber,
            'issue_date' => $this->issueDate,
            'updated_at' => $this->updatedAt,
            'updated_by' => $this->updatedBy,
            'updated_by_type' => $this->updatedByType,
            'license_type' => $this->license_type,
            'issuing_state' => $this->issuing_state,
            'vic_conversion_date' => $this->vic_conversion_date,
            'applicant_specific' => $this->applicant_specific,
            'visa_category' => $this->visa_category,
            'visa_category_type' => $this->visa_category_type
        );
        
        $where = array("id" => $this->docId);
        return $documentId = $ci->basic_model->update_records('document_attachment', $update_ary, $where);
    }

    /**
     * Create Document Attachment
     */
    public function archiveDocumentAttachment() {
        $ci = & get_instance();

        $update_ary = array(
            'archive' => self::ARCHIVE_DOCMUENT,
            'updated_at' => $this->updatedAt,
            'updated_by' => $this->updatedBy,
        );
        
        $where = array("id" => $this->docId);
        return $documentId = $ci->basic_model->update_records('document_attachment', $update_ary, $where);
    }

    /**
     * Get the value of license_type
     */ 
    public function getLicenseType()
    {
        return $this->license_type;
    }

    /**
     * Set the value of license_type
     *
     * @return  self
     */ 
    public function setLicenseType($license_type)
    {
        $this->license_type = $license_type;

        return $this;
    }

    /**
     * Get the value of issuing_state
     */ 
    public function getIssuingState()
    {
        return $this->issuing_state;
    }

    /**
     * Set the value of issuing_state
     *
     * @return  self
     */ 
    public function setIssuingState($issuing_state)
    {
        $this->issuing_state = $issuing_state;

        return $this;
    }

    /**
     * Get the value of vic_conversion_date
     */ 
    public function getVicConversionDate()
    {
        return $this->vic_conversion_date;
    }

    /**
     * Set the value of vic_conversion_date
     *
     * @return  self
     */ 
    public function setVicConversionDate($vic_conversion_date)
    {
        $this->vic_conversion_date = $vic_conversion_date;

        return $this;
    }

    /**
     * Get the value of applicant_specific
     */ 
    public function getApplicantApecific()
    {
        return $this->applicant_specific;
    }

    /**
     * Set the value of applicant_specific
     *
     * @return  self
     */ 
    public function setApplicantSpecific($applicant_specific)
    {
        $this->applicant_specific = $applicant_specific;

        return $this;
    }
}

