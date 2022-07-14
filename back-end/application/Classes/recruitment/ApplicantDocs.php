<?php

namespace ClassApplicantDocs;

/*
 * Filename: applicantDocs.php
 * Desc: Docs details of applicant like filename, type etc.
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * Class: ClassApplicantDocs
 * Desc: This Class is for maintaining docs details of applicant.
 * Created: 02-08-2018 
 */

class ApplicantDocs {

    const DOCUMENT_STATUS_SUBMITTED = 0;
    const DOCUMENT_STATUS_VALID = 1;
    const DOCUMENT_STATUS_INVALID = 2;
    const DOCUMENT_STATUS_EXPIRED = 3;

    /**
     * @var applicantdocsid
     * @access private
     * @vartype: int
     */
    private $applicantdocsid;

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
     * @var stageId
     * @access private
     * @vartype: int
     */
    private $isMainStage;

    /**
     * @var title
     * @access private
     * @vartype: varchar
     */
    private $title;

    /**
     * @var filename
     * @access private
     * @vartype: varchar
     */
    private $filename;

    /**
     * @var created
     * @access private
     * @vartype: varchar
     */
    private $created;
    private $createdBy;
    private $archive;
    private $awsObjectUri;
    private $awsResponse;
    private $awsFilePath;
    private $awsFileType;
    private $awsFileSize;

    /**
     * @var string|null
     */
    private $expiry_date = null;

    /**
     * @var string|null
     */
    private $updated_at = null;

    /**
     * @var int
     */
    private $document_status = self::DOCUMENT_STATUS_SUBMITTED;


    private $applicationId = 0;

    /**
     * @var string|null
     */
    private $issue_date;

    /**
     * @var string|null
     */
    private $reference_number;


    /**
     * @var categoryType
     * @access private
     * @vartype: int
     */
    private $categoryType;
    
    /**
     * @var awsUploadedFlag
     * @access private
     * @vartype: bool
     */    
    private $awsUploadedFlag;

    /**
     * @function getApplicantdocsid
     * @access public
     * @returns $oc_departments int
     * Get applicant Docs Id
     */
    public function getApplicantdocsid() {
        return $this->applicantdocsid;
    }

    /**
     * @function setApplicantdocsid
     * @access public
     * @param $applicantdocsid tinyint 
     * Set applicant Docs Id
     */
    public function setApplicantdocsid($applicantdocsid) {
        $this->applicantdocsid = $applicantdocsid;
    }

    /**
     * @function getApplicantid
     * @access public
     * @returns $applicantid int
     * Get applicant Id
     */
    public function getApplicantId() {
        return $this->applicantId;
    }

    /**
     * @function setApplicantid
     * @access public
     * @param $applicantid int 
     * Set applicant Id
     */
    public function setApplicantId($applicantId) {
        $this->applicantId = $applicantId;
    }

    /**
     * @function getStage
     * @access public
     * @returns $stageId tinyint
     * Get Type
     */
    public function getStage() {
        return $this->stageId;
    }

    /**
     * @function setStage
     * @access public
     * @param $stageId tinyint 
     * Set Type
     */
    public function setStage($stageId) {
        $this->stageId = $stageId;
    }
    /**
     * @function getIsMainStage
     * @access public
     * @returns $isMainStage tinyint
     * Get Type
     */
    public function getIsMainStage() {
        return (int)$this->isMainStage;
    }

    /**
     * @function setIsMainStage
     * @access public
     * @param $isMainStage tinyint 
     * Set Type
     */
    public function setIsMainStage($isMainStage) {
        $this->isMainStage = (int) $isMainStage;
    }

    /**
     * @function getTitle
     * @access public
     * @returns $title varchar
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
     * @function getFilename
     * @access public
     * @returns $filename varchar
     * Get Filename
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * @function setFilename
     * @access public
     * @param $filename varchar
     * Set Filename
     */
    public function setFilename($filename) {
        $this->filename = $filename;
    }

    /**
     * @function getCreated
     * @access public
     * @returns $created varchar
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

    function setArchive($archive) {
        $this->archive = $archive;
    }

    function getArchive() {
        return $this->archive;
    }

    /**
     * @function getCategoryType
     * @access public
     * @returns $categoryType tinyint
     * Get Type
     */
    public function getCategoryType() {
        return (int) $this->categoryType;
    }

    /**
     * @function setCategoryType
     * @access public
     * @param $categoryType tinyint 
     * Set Type
     */
    public function setCategoryType($categoryType) {
        $this->categoryType = (int) $categoryType;
    }

     /**
     * @function getCreated
     * @access public
     * @returns $created varchar
     * Get Created
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * @function setCreated
     * @access public
     * @param $created varchar
     * Set Created
     */
    public function setCreatedBy($created_by) {
        $this->createdBy = (int)$created_by;
    }


    /**
     * @param string|null $expiry_date 
     * @return void 
     */
    public function setExpiryDate($expiry_date)
    {
        $this->expiry_date = $expiry_date;
    }

    /**
     * @return string|null In `Y-m-d H:i:s` format 
     */
    public function getExpiryDate()
    {
        return $this->expiry_date;
    }

    /**
     * @param string|null $updated_at 
     * @return void 
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    /**
     * @return string|null In `Y-m-d H:i:s` format 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @return int 
     */
    public function getDocumentStatus()
    {
        return $this->document_status;
    }

    /**
     * @param int $document_status 
     */
    public function setDocumentStatus($document_status)
    {
        $this->document_status = $document_status;
    }

    public function setApplicationId($applicationId)
    {
        $this->applicationId = $applicationId;
    }
    
    public function getApplicationId()
    {
        return $this->applicationId;
    }

    /**
     * @return string|null 
     */
    public function getIssueDate()
    {
        return $this->issue_date;
    }

    /**
     * @param string $issue_date 
     * @return void 
     */
    public function setIssueDate($issue_date)
    {
        $this->issue_date = $issue_date;
    }

    /**
     * @return string|null 
     */
    public function getReferenceNumber()
    {
        return $this->reference_number;
    }

    /**
     * 
     * @param string $reference_number 
     * @return void 
     */
    public function setReferenceNumber($reference_number)
    {
        $this->reference_number = $reference_number;
    }

    /**
     * 
     * @param string $uri 
     * @return void 
     */
    public function getAwsObjectUri($uri)
    {
        $this->awsObjectUri = $uri;
    }
    /**
     * 
     * @param string $uri 
     * @return void 
     */
    public function setAwsObjectUri($uri)
    {
        $this->awsObjectUri = $uri;
    }

    /**
     * 
     * @param string $response 
     * @return void 
     */
    public function getAwsResponse($response)
    {
        $this->awsResponse = $response;
    }

    /**
     * 
     * @param string $response 
     * @return void 
     */
    public function setAwsResponse($response)
    {
        $this->awsResponse = $response;
    }

    /**
     * 
     * @param string $filepath 
     * @return void 
     */
    public function getAwsFilePath($filepath)
    {
        $this->awsFilePath = $filepath;
    }

    /**
     * 
     * @param string $filepath 
     * @return void 
     */
    public function setAwsFilePath($filepath)
    {
        $this->awsFilePath = $filepath;
    }
        
    /**
     * 
     * @param string $filetype 
     * @return void 
     */
    public function setAwsFileType($filetype)
    {
        $this->awsFileType = $filetype;
    }
    
     /**
     * 
     * @param string $filetype 
     * @return void 
     */
    public function getAwsFileType($filetype)
    {
        $this->awsFileType = $filetype;
    }
    
    /**
     * 
     * @param string $filetype 
     * @return void 
     */
    public function setAwsFileSize($filesize)
    {
        $this->awsFileSize = $filesize;
    }
    
     /**
     * 
     * @param string $filesize 
     * @return void 
     */
    public function getAwsFileSize($filesize)
    {
        $this->awsFileType = $filesize;
    }

     /**
     * 
     * @param string $flag 
     * @return void 
     */
    public function getAwsUploadedFlag($flag)
    {
        $this->awsUploadedFlag = $flag;
    }

    /**
     * 
     * @param string $flag 
     * @return void 
     */
    public function setAwsUploadedFlag($flag)
    {
        $this->awsUploadedFlag = $flag;
    }

    function checkDublicateDocs() {
        $ci = & get_instance();
        $where = array('attachment' => $this->filename,
            'applicant_id' => $this->applicantId,
            'archive' => $this->archive,
        );

        $result = $ci->basic_model->get_row('recruitment_applicant_stage_attachment', array('attachment'), $where);
        if (!empty($result)) {
            $return = array('status' => false, 'warn' => 'Warning: Document with this name already exist, we are changing its name.');
        } else {
            $return = array('status' => true);
        }
        return $return;
    }

    function createFileData() {
        $ci = & get_instance();
        
        $insert_ary = array('attachment' => $this->filename,
            'applicant_id' => $this->applicantId,
            'attachment_title' => $this->title,
            'file_path' => $this->awsFilePath,
            'file_type' => $this->awsFileType,
            'stage' => $this->stageId,
            'created' => $this->created,
            'created_by' => $this->createdBy,
            'archive' => $this->archive,
            'doc_category' => $this->categoryType,
            'is_main_stage_label' => $this->isMainStage,
            'expiry_date' => $this->expiry_date,
            'updated_at' => $this->updated_at,
            'document_status' => $this->document_status,
            'application_id' => $this->applicationId,
            'reference_number' => $this->reference_number,
            'issue_date' => $this->issue_date,
            'aws_object_uri' => $this->awsObjectUri,
            'aws_response' => $this->awsResponse,
            'aws_uploaded_flag' => $this->awsUploadedFlag,
            'file_size' => $this->awsFileSize
        );
        
        return $rows = $ci->basic_model->insert_records('recruitment_applicant_stage_attachment', $insert_ary, $multiple = FALSE);
    }

}
