<?php

namespace ClassParticipantDocs;

/*
 * Filename: ParticipantDocs.php
 * Desc: Docs details of participant like filename, type etc.
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * Class: ParticipantDocs
 * Desc: This Class is for maintaining docs details of participant.
 * Created: 02-08-2018 
 */

class ParticipantDocs {

    /**
     * @var participantdocsid
     * @access private
     * @vartype: int
     */
    private $participantdocsid;

    /**
     * @var participantid
     * @access private
     * @vartype: int
     */
    private $participantId;

    /**
     * @var type
     * @access private
     * @vartype: tinyint
     */
    private $type;

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
    private $archive;

     /**
     * @var expiryDate
     * @access private
     * @vartype: date
     */
    private $expiryDate;

    /**
     * @var categoryType
     * @access private
     * @vartype: int
     */
    private $categoryType;

    /**
     * @function getParticipantdocsid
     * @access public
     * @returns $oc_departments int
     * Get Participant Docs Id
     */
    public function getParticipantdocsid() {
        return $this->participantdocsid;
    }

    /**
     * @function setParticipantdocsid
     * @access public
     * @param $participantdocsid tinyint 
     * Set Participant Docs Id
     */
    public function setParticipantdocsid($participantdocsid) {
        $this->participantdocsid = $participantdocsid;
    }

    /**
     * @function getParticipantid
     * @access public
     * @returns $participantid int
     * Get Participant Id
     */
    public function getParticipantId() {
        return $this->participantId;
    }

    /**
     * @function setParticipantid
     * @access public
     * @param $participantid int 
     * Set Participant Id
     */
    public function setParticipantId($participantId) {
        $this->participantId = $participantId;
    }

    /**
     * @function getType
     * @access public
     * @returns $type tinyint
     * Get Type
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @function setType
     * @access public
     * @param $type tinyint 
     * Set Type
     */
    public function setType($type) {
        $this->type = $type;
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
     * @function getType
     * @access public
     * @returns $type tinyint
     * Get Type
     */
    public function getExpiryDate() {
        return $this->expiryDate;
    }

    /**
     * @function setType
     * @access public
     * @param $type tinyint 
     * Set Type
     */
    public function setExpiryDate($setExpiryDate) {
        $this->expiryDate = $setExpiryDate;
    }


    /**
     * @function getType
     * @access public
     * @returns $type tinyint
     * Get Type
     */
    public function getCategoryType() {
        return (int) $this->categoryType;
    }

    /**
     * @function setType
     * @access public
     * @param $type tinyint 
     * Set Type
     */
    public function setCategoryType($categoryType) {
        $this->categoryType = (int) $categoryType;
    }

    function checkDublicateDocs() {
        $ci = & get_instance();
        $where = array('filename' => $this->filename,
            'participantId' => $this->participantId,
            'archive' => $this->archive,
        );

        $result = $ci->basic_model->get_row('participant_docs', array('filename'), $where);
//        last_query();
        if (!empty($result)) {
            $return = array('status' => false, 'warn' => 'Warning: This name document already exist so we changed name');
        } else {
            $return = array('status' => true);
        }
        return $return;
    }

    function createFileData() {
        $ci = & get_instance();
        
        $insert_ary = array('filename' => $this->filename,
            'participantId' => $this->participantId,
            'title' => $this->title,
            'type' => $this->type,
            'created' => $this->created,
            'archive' => $this->archive,
            'expiry_date' => $this->expiryDate,
            'category' => $this->categoryType,
        );
        
        return $rows = $ci->basic_model->insert_records('participant_docs', $insert_ary, $multiple = FALSE);
    }

}
