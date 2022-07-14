<?php
namespace FmsClass;
/*
 * Filename: Fms.php
 * Desc: Fms of Members, end and start time of shift
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * Class: Fms
 * Desc: Class Has 5 Arrays, variables ans setter and getter methods of shifts
 * Created: 25-10-2018
 */

class Fms {
	public $CI;

	function __construct() {
		$this->CI = & get_instance();
		$this->CI->load->model('Fms_model');
		$this->CI->load->model('Basic_model');
	}

	private $id;
	private $companyId;
	private $event_date;
	private $shiftId;
	private $initiated_by;
	private $initiated_type;
	private $created;
	private $escalate_to_incident;
	private $address;
	private $suburb;
	private $postal;
	private $state;
	private $status;

	function setId($id) { $this->id = $id; }

	function getId() { return $this->id; }

	function setCompanyId($companyId) { $this->companyId = $companyId; }

	function getCompanyId() { return $this->companyId; }

	function setEvent_date($event_date) { $this->event_date = $event_date; }

	function getEvent_date() { return $this->event_date; }

	function setShiftId($shiftId) { $this->shiftId = $shiftId; }

	function getShiftId() { return $this->shiftId; }

	function setInitiated_by($initiated_by) { $this->initiated_by = $initiated_by; }

	function getInitiated_by() { return $this->initiated_by; }

	function setInitiated_type($initiated_type) { $this->initiated_type = $initiated_type; }

	function getInitiated_type() { return $this->initiated_type; }

	function setCreated($created) { $this->created = $created; }

	function getCreated() { return $this->created; }

	function setEscalate_to_incident($escalate_to_incident) { $this->escalate_to_incident = $escalate_to_incident; }

	function getEscalate_to_incident() { return $this->escalate_to_incident; }

	function setAddress($address) { $this->address = $address; }

	function getAddress() { return $this->address; }

	function setSuburb($suburb) { $this->suburb = $suburb; }

	function getSuburb() { return $this->suburb; }

	function setPostal($postal) { $this->postal = $postal; }

	function getPostal() { return $this->postal; }

	function setState($state) { $this->state = $state; }

	function getState() { return $this->state; }

	function setStatus($status) { $this->status = $status; }

	function getStatus() { return $this->status; }

    function create_case($reqData) {
      return $this->CI->Fms_model->create_case($reqData);
    }

}