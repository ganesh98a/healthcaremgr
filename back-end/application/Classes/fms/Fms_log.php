<?php
namespace FmsLogClass;
/*
 * Filename: Fms_log.php
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * Class: Fms_log
 * Created: 19-11-2018
 */

class Fms_log {
	public $CI;

	function __construct() {
		$this->CI = & get_instance();
		$this->CI->load->model('Fms_model');
		$this->CI->load->model('Basic_model');
	}

	private $id;
	private $caseId;
	private $title;
	private $created_by;
	private $created_type;
	private $created;

	function setId($id) { $this->id = $id; }
	function getId() { return $this->id; }
	function setCaseId($caseId) { $this->caseId = $caseId; }
	function getCaseId() { return $this->caseId; }
	function setTitle($title) { $this->title = $title; }
	function getTitle() { return $this->title; }
	function setCreated_by($created_by) { $this->created_by = $created_by; }
	function getCreated_by() { return $this->created_by; }
	function setCreated_type($created_type) { $this->created_type = $created_type; }
	function getCreated_type() { return $this->created_type; }
	function setCreated($created) { $this->created = $created; }
	function getCreated() { return $this->created; }

	function createFmsLog() {
        $data = array(
            'caseId' => $this->caseId,
            'title' => $this->title,
            'created_by' => $this->created_by,
            'created_type' => $this->created_type,
            'created_type' => $this->created_type,
            'created'=> DATE_TIME
        );
        $this->CI->db->insert(TBL_PREFIX . 'fms_case_log', $data);
    }
}