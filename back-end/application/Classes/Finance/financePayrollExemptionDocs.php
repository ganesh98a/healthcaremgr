<?php

namespace ClassFinancePayrollExemptionDocs;

/*
 * Filename: financePayrollExemptionDocs.php
 * Desc: Docs details of applicant like filename, type etc.
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * Class: ClassFinancePayrollExemptionDocs
 * Desc: This Class is for maintaining docs details of finance payroll Exemption.
 * Created: 17-10-2019 
 */

class FinancePayrollExemptionDocs {

    /**
     * @var financePayrollId
     * @access private
     * @vartype: int
     */
    private $financePayrollId;

    /**
     * @var organisationId
     * @access private
     * @vartype: int
     */
    private $organisationId;
    /**
     * @var validFrom
     * @access private
     * @vartype: varchar
     */
    private $validFrom;

    /**
     * @var validTo
     * @access private
     * @vartype: varchar
     */
    private $validTo;



    /**
     * @var filePath
     * @access private
     * @vartype: varchar
     */
    private $filePath;

    /**
     * @var fileTitle
     * @access private
     * @vartype: varchar
     */
    private $fileTitle;

    /**
     * @var status
     * @access private
     * @vartype: int
     */
    private $status;

    /**
     * @var created
     * @access private
     * @vartype: varchar
     */
    private $created;
    private $createdBy;
    private $archive;



    /**
     * @function getOrganisationId
     * @access public
     * @returns $organisationId int
     * Get organisationId
     */
    public function getOrganisationId() {
        return $this->organisationId;
    }

    /**
     * @function setOrganisationId
     * @access public
     * @param $organisationId tinyint 
     * Set organisationId
     */
    public function setOrganisationId(int $organisationId) {
        $this->organisationId = $organisationId;
    }

    /**
     * @function getFinancePayrollId
     * @access public
     * @returns $financePayrollId int
     * Get financePayrollId
     */
    public function getFinancePayrollId() {
        return $this->financePayrollId;
    }

    /**
     * @function setFinancePayrollId
     * @access public
     * @param $financePayrollId int 
     * Set financePayrollId
     */
    public function setFinancePayrollId(int $financePayrollId) {
        $this->financePayrollId = $financePayrollId;
    }

    /**
     * @function getFileTitle
     * @access public
     * @returns $fileTitle varchar
     * Get fileTitle
     */
    public function getFileTitle() {
        return $this->fileTitle;
    }

    /**
     * @function setFileTitle
     * @access public
     * @param $fileTitle tinyint 
     * Set fileTitle
     */
    public function setFileTitle($fileTitle) {
        $this->fileTitle = $fileTitle;
    }
    /**
     * @function getStatus
     * @access public
     * @returns $status int
     * Get status
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @function setStatus
     * @access public
     * @param $status int 
     * Set status
     */
    public function setStatus(int $status) {
        $this->status = $status;
    }

    /**
     * @function getFilePath
     * @access public
     * @returns $filePath varchar
     * Get filePath
     */
    public function getFilePath() {
        return $this->filePath;
    }

    /**
     * @function setFilePath
     * @access public
     * @param $filePath varchar
     * Set filePath
     */
    public function setFilePath($filePath) {
        $this->filePath = $filePath;
    }

    /**
     * @function getValidFrom
     * @access public
     * @returns $validFrom varchar
     * Get validFrom
     */
    public function getValidFrom() {
        return $this->validFrom;
    }

    /**
     * @function setValidFrom
     * @access public
     * @param $validFrom varchar
     * Set validFrom
     */
    public function setValidFrom($validFrom) {
        $this->validFrom = $validFrom;
    }


    /**
     * @function getValidTo
     * @access public
     * @returns $validTo varchar
     * Get validTo
     */
    public function getValidTo() {
        return $this->validTo;
    }

    /**
     * @function setValidTo
     * @access public
     * @param $validTo varchar
     * Set validTo
     */
    public function setValidTo($validTo) {
        $this->validTo = $validTo;
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

    function checkDublicateDocs() {
        $ci = & get_instance();
        $where = array('file_path' => $this->filePath,
            'organisation_id' => $this->organisationId,
            'archive' => $this->archive,
        );

        $result = $ci->basic_model->get_row('finance_payroll_exemption_organisation', array('file_path'), $where);
        if (!empty($result)) {
            $return = array('status' => false, 'warn' => 'Warning: Document with this name already exist, we are changing its name.');
        } else {
            $return = array('status' => true);
        }
        return $return;
    }

    function createFileData() {
        $ci = & get_instance();
        
        $insert_ary = array(
            'file_path' => $this->filePath,
            'organisation_id' => $this->organisationId,
            'file_title' => $this->fileTitle,
            'valid_from' => $this->validFrom,
            'valid_to' => $this->validTo,
            'created_by' => $this->createdBy,
            'archive' => $this->archive,
            'status' => $this->status,
            'created' => $this->created
        );
        return $rows = $ci->basic_model->insert_records('finance_payroll_exemption_organisation', $insert_ary, $multiple = FALSE);
    }

}
