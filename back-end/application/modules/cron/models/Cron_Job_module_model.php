<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_Job_module_model extends CI_Model
{
	public function __construct() {
		parent::__construct();
	}

	public function get_all_jobs()
	{		
		$this->db->where(array('archive' => 0,'is_recurring' => 0, 'job_status' => 5));
		//$this->db->where_not_in('job_status', '0,2,4',false);
		$yesterday = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( date('Y-m-d')) ) ));

		$this->db->group_start();
		$this->db->where('from_date <=', date('Y-m-d'));
		$this->db->or_where('to_date <=', $yesterday);
		$this->db->group_end();

		$select_column = array("id","created","is_recurring","job_status","DATE_FORMAT(from_date,'%Y-%m-%d') as from_date","DATE_FORMAT(to_date,'%Y-%m-%d') as to_date");
		$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
		$this->db->from("tbl_recruitment_job");	
		$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
		$dataResult = $query->result();	
		#pr($dataResult);	
		$update_Ary = array();
		$update_log_Ary = array();
		if(!empty($dataResult))
		{
			$temp_Ary = [];
			foreach ($dataResult as $key => $value)
			{
				$log_ary = ['companyId'=>1,'module'=>'9','sub_module'=>'17','created' => DATE_TIME,'created_type'=>1];
				if(strtotime($value->from_date) == strtotime(date('Y-m-d')))
				{
					$temp_Ary['id'] = $value->id; 
					$temp_Ary['job_status'] = 3; 
					$log_ary['title'] = 'Update job status to live via CRON, job id : '.$value->id;
				}

				if($value->to_date!='0000-00-00' && strtotime($value->to_date) < strtotime(date('Y-m-d')))
				{
					$temp_Ary['id'] = $value->id; 	
					$temp_Ary['job_status'] = 2;
					$log_ary['title'] = 'Update job status to closed via CRON, job id : '.$value->id;
				}
				$update_Ary[] = $temp_Ary;
				$update_log_Ary[] = $log_ary;
			}
			
			if(!empty($update_Ary))
				$this->basic_model->insert_update_batch($action='update',$table_name='recruitment_job',$update_Ary,$update_base_column_key='id');

			if(!empty($update_log_Ary))
				$this->basic_model->insert_records('logs',$update_log_Ary,true);
		}
		return $update_Ary;
	}
}