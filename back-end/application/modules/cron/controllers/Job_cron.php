<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Job_cron extends MX_Controller 
{	
	function __construct() {
		parent::__construct();
		$this->load->model('Cron_Job_module_model');
		$this->load->model('Basic_model');		
	}
	
	public function update_job_status()
	{
		$result = $this->Cron_Job_module_model->get_all_jobs();
		echo json_encode($result);
	}
}