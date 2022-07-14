<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Finance_cron_model extends CI_Model
{
	public function __construct() {
		parent::__construct();
	}

	/*
	*Get all payrate and update status and marked archieve
	*/
	public function update_payrate_status()
	{		
		$this->db->where(array('archive'=>0));
		$yesterday = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( date('Y-m-d')) ) ));

		$this->db->group_start();
		$this->db->where('start_date =', date('Y-m-d'));
		$this->db->or_where('end_date =', $yesterday);
		$this->db->group_end();

		$select_column = array("id","created","DATE_FORMAT(start_date,'%Y-%m-%d') as start_date","DATE_FORMAT(end_date,'%Y-%m-%d') as end_date");
		$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
		$this->db->from("tbl_finance_payrate");	
		$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
		#last_query(); die;
		$dataResult = $query->result();	
		#pr($dataResult);	
		$update_Ary = array();
		if(!empty($dataResult))
		{
			$temp_Ary = [];
			foreach ($dataResult as $key => $value)
			{
				if(strtotime($value->start_date) == strtotime(date('Y-m-d')))
				{
					$temp_Ary['id'] = $value->id; 
					$temp_Ary['status'] = 1; 
				}

				if(strtotime($value->end_date) < strtotime(date('Y-m-d')))
				{
					$temp_Ary['id'] = $value->id; 	
					$temp_Ary['status'] = 2;
				}
				$update_Ary[] = $temp_Ary;
			}
			//pr($update_Ary);
			if(!empty($update_Ary))
				$this->basic_model->insert_update_batch($action='update',$table_name='finance_payrate',$update_Ary,$update_base_column_key='id');
		}
		return $update_Ary;
	}
}