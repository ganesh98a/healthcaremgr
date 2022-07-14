<?php

/*
 * Filename: MemberQualification.php
 * Desc: This file describes about qualification of members, and files and docs. of members, title and expiry of certification of the members.
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * class: MemberQualification
 * Desc: The class has variables like memberid, qualification details, title and expiry of certification of the members.  
 * Created: 01-08-2018
 */

class MemberQualification {

    public $CI;

    function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->model('Member_model');
    }

    /**
     * @var memberqualificationid
     * @access private
     * @vartype: int
     */
    private $memberqualificationid;

    /**
     * @var memberid
     * @access private
     * @vartype: int
     */
    private $memberid;

    /**
     * @var type
     * @access private
     * @vartype: int
     */
    private $type;

    /**
     * @var title
     * @access private
     * @vartype: varchar
     */
    private $title;

    /**
     * @var expiry
     * @access private
     * @vartype: varchar
     */
    private $expiry;

    /**
     * @var filename
     * @access private
     * @vartype: varchar
     */
    private $filename;

    /**
     * @var archive
     * @access private
     * @vartype: tinyint
     */
    private $archive;

    /**
     * @var created
     * @access private
     * @vartype: varchar
     */
    private $created;
    private $start_date;
    private $end_date;

    /**
     * @function getMemberqualificationid
     * @access public
     * @return $memberqualification integer
     * Get getMemberqualification Id
     */
    public function getMemberqualificationid() {
        return $this->memberqualificationid;
    }

    /**
     * @function setMemberqualificationid
     * @param $memberqualificationid integer 
     * @access public
     * Set Member Qualification Id
     */
    public function setMemberqualificationid($memberqualificationid) {
        $this->memberqualificationid = $memberqualificationid;
    }

    /**
     * @function getMemberid
     * @access public
     * @return $memberid integer
     * Get Member Id
     */
    public function getMemberid() {
        return $this->memberid;
    }

    /**
     * @function setMemberid
     * @param $memberid integer 
     * @access public
     * Set Memberid Id
     */
    public function setMemberid($memberid) {
        $this->memberid = $memberid;
    }

    /**
     * @function getType
     * @access public
     * @return $type tinyint
     * Get Type 
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @function setType
     * @param $type tinyint 
     * @access public
     * Set Type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * @function getTitle
     * @access public
     * @return $title varchar
     * Get Title
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @function setTitle
     * @param $title varchar 
     * @access public
     * Set Title 
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @function getExpiry
     * @access public
     * @return $expiry varchar
     * Get Expiry Id
     */
    public function getExpiry() {
        return $this->expiry;
    }

    /**
     * @function setExpiry
     * @param $expiry varchar 
     * @access public
     * Set Expiry
     */
    public function setExpiry($expiry) {
        $this->expiry = $expiry;
    }

    /**
     * @function getFilename
     * @access public
     * @return $filename varchar
     * Get Filename
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * @function setFilename
     * @param $filename varchar 
     * @access public
     * Set filename
     */
    public function setFilename($filename) {
        $this->filename = $filename;
    }

    /**
     * @function getArchive
     * @access public
     * @return $archive varchar
     * Get Archive
     */
    public function getArchive() {
        return $this->archive;
    }

    /**
     * @function setArchive
     * @access public
     * @param $archive tinyint
     * Set Archive
     */
    public function setArchive($archive) {
        $this->archive = $archive;
    }

    /**
     * @function getCreated
     * @access public
     * @return $archive varchar
     * Get Created
     */
    public function getCreated() {
        return $this->created;
    }

    function setStart_date($start_date) {
        $this->start_date = $start_date;
    }

    function getStart_date() {
        return $this->start_date;
    }

    function setEnd_date($end_date) {
        $this->end_date = $end_date;
    }

    function getEnd_date() {
        return $this->end_date;
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

    public function get_member_qualification() {
        return $this->CI->Member_model->member_qualification($this);
    }

    public function archive_member_qualification() {
        $CI = & get_instance();
        $memberid = $this->getMemberid();
        $id = $this->getMemberqualificationid();
        $arr = array("id" => $id, "memberId" => $memberid);
        return $CI->Basic_model->update_records('member_qualification', array('archive'=>1),$arr);
    }

}
