<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recruitment_reference_data_model extends CI_Model {

	public function __construct() {
        // Call the CI_Model constructor
		parent::__construct();
	}

	public function check_display_name_already_exist($name, $id,$type) {
        $this->db->select("id");
        $this->db->from("tbl_references");
		$this->db->where("display_name", trim(strtolower($name)));
		$this->db->where("type = ", $type);
        if ($id > 0) {
            $this->db->where("id != ", $id);
        }
        return $this->db->get()->row();
    }

	public function save_ref_data($post_data) 
	{
		if (!empty($post_data)) 
		{
			$operation = $post_data['operation'];
			
			$organisation_data = array('type' => $post_data['type']??'',
				'code' => $post_data['code']??'',
				'display_name' => $post_data['display_name']??'',
				'definition' => $post_data['definition']??'',
				'parent_id' => $post_data['parent_id']??'',
				'source' => $post_data['source']??'',
				'start_date' => (isset($post_data['start_date']) && $post_data['start_date']!='0000-00-00')?date('Y-m-d', strtotime($post_data['start_date'])):'0000-00-00',
				'end_date' => $post_data['end_date']??'0000-00-00',
				'created'=>DATE_TIME,
			);
			
			if($operation == 'E'){
				$ref_id = $post_data['ref_id'];
				$this->Basic_model->update_records('references', $organisation_data,array('id'=>$ref_id));
			}
			else{
				$ref_id = $this->Basic_model->insert_records('references', $organisation_data);
			}
						
			if($operation == 'E')
				$msg = 'Reference data updated successfully.';
			else
				$msg = 'Reference data saved successfully.';

			return array('ref_id' => $ref_id,'msg'=>$msg);
		}
	}

	public function get_all_ref_data($reqData)
	{	
        $limit = $reqData->pageSize ?? 20;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
		$orderBy = '';
		$direction = '';
		$tbl_job = TBL_PREFIX . 'references';
        $available_column = array("data_id","created", "code","display_name","definition","parent_id","source","start_date","end_date","type");
		if (!empty($sorted)) {
			if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
				$orderBy = $sorted[0]->id;
				$direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
			}
		} else {
			$orderBy = $tbl_job . '.id';
			$direction = 'DESC';
		}
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

		$this->db->where($tbl_job . ".archive".' =', 0);  

		if (!empty($reqData->filtered->search)) {
			$this->db->group_start();
			$src_columns = array($tbl_job . ".code",$tbl_job . ".display_name",$tbl_job . ".definition",$tbl_job . ".source","rdt.title as type");
			for ($i = 0; $i < count($src_columns); $i++) {
				$column_search = $src_columns[$i];
				if (strstr($column_search, "as") !== false) {
					$serch_column = explode(" as ", $column_search);
					$this->db->or_like($serch_column[0], $filter->search);
				} else {
					$this->db->or_like($column_search, $filter->search);
				}
			}
			$this->db->group_end();
		}

		$select_column = array($tbl_job . ".id as data_id",$tbl_job . ".created", $tbl_job . ".code",$tbl_job . ".display_name",$tbl_job . ".definition",$tbl_job . ".parent_id",$tbl_job . ".source",$tbl_job . ".start_date",$tbl_job . ".end_date","rdt.title as type");

		$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
		$this->db->from($tbl_job);
		$this->db->join('tbl_reference_data_type as rdt', 'rdt.id = ' . $tbl_job . '.type AND rdt.archive = 0', 'inner');				
		$this->db->order_by($orderBy, $direction);
		$this->db->limit($limit, ($page * $limit));

		$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // last_query();exit; 
		$dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
		if ($dt_filtered_total % $limit == 0) {
			$dt_filtered_total = ($dt_filtered_total / $limit);
		} else {
			$dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
		}

		$dataResult = $query->result();
		$return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count,'status'=>true);
		return $return;
	}

	/**
	* Create or Update reference
	*/
	function create_update_reference($data, $adminId) {
		$id = $data->id ?? 0;
		$postdata = [
            "applicant_id" => ($data->applicant_id) ?? null,
            "name" => ($data->full_name) ?? '',
            "email" => ($data->email) ?? '',
            "phone" => ($data->phone) ?? '',
            "status" => ($data->status) ?? '',
            "written_reference" => ($data->written_reference_check) ?? 0,
            "relevant_note" => $data->notes ?? '',
        ];

        if ($id) {
            $postdata["updated"] = DATE_TIME;
            // $postdata["updated_by"] = $adminId;
            $this->basic_model->update_records("recruitment_applicant_reference", $postdata, ["id" => $id]);
        } else {
            $postdata["created"] = DATE_TIME;
            // $postdata["created_by"] = $adminId;
            $id = $this->basic_model->insert_records("recruitment_applicant_reference", $postdata, $multiple = FALSE);
        }
        # adding a log entry
        $this->add_create_update_reference_log($data, $adminId, $id);

        return $id;
	}

	/**
     * used by the create_update_reference function to insert a log entry on reference adding / updating
     */
    public function add_create_update_reference_log($data, $adminId, $reference_id) {
        $this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);

        # create log setter getter
        if (!empty($reference_id)) {
            $this->loges->setTitle("Updated reference:".$reference_id." by " . $adminName);
        } else {
            $this->loges->setTitle("New reference created by " . $adminName);
        }
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($reference_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
    }

    function get_reference_data_by_id($applicant_id, $reference_id = '') {

        $where = array('rar.applicant_id' => $applicant_id, 'rar.archive' => 0);
        if ($reference_id != '') {
        	$where['rar.id'] = $reference_id;
        }

        $column = array('rar.id', 'rar.name', 'rar.email', 'rar.phone', 'rar.status', 'rar.relevant_note', 'rar.written_reference');

        $this->db->select("(select form_id from tbl_recruitment_form_applicant as rfa where rfa.reference_id = rar.id AND rfa.archive = 0) as form_id");
        $this->db->select("(select id from tbl_recruitment_form_applicant as rfa where rfa.reference_id = rar.id  AND rfa.archive = 0) as interview_applicant_form_id");
        $this->db->select("(
            CASE 
                WHEN rar.status = 1 THEN 'Approved'
                ELSE 'Rejected'
                END
        ) as status_value");
        $this->db->select("(
            CASE 
                WHEN rar.written_reference = 1 THEN 'Yes'
                ELSE 'No'
                END
        ) as written_reference_check");

        $this->db->select($column);
        $this->db->from("tbl_recruitment_applicant_reference as rar");
        $this->db->where($where);

       	$res = $this->db->get()->result();        

        if (!empty($res)) {
            foreach ($res as $val) {
                $val->interview_status = $val->form_id > 0 ? true : false;
                $val->interview_form_id = $val->form_id > 0 ? $val->form_id : false;
                $val->written_reference = $val->written_reference > 0 ? true : false;
                $val->interview_applicant_form_id = $val->interview_applicant_form_id ?? false;
            }
        }

        return $res;
    }

    public function get_applicant_reference($reqData, $reference_id = '') {
        if (!empty($reqData)) {
            $applicant_id = json_decode($reqData->applicant_id);

            $limit = $reqData->pageSize;
            $page = $reqData->page;
            $sorted = $reqData->sorted;
            $filter = $reqData->filtered;
            $orderBy = '';
            $direction = '';

            $src_columns = array('rar.id', 'rar.name', 'rar.email', 'rar.phone', 'rar.status', 'rar.relevant_note', 'rar.written_reference','rar.created','rar.updated');
            $available_column = array('id', 'name', 'email', 'phone', 'status', 'relevant_note', 'written_reference','created','updated');
            if (!empty($sorted)) {
                if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                    $orderBy = $sorted[0]->id == 'id' ? 'rar.id' : $sorted[0]->id;
                    $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
                }
            } else {
                $orderBy = 'rar.id';
                $direction = 'DESC';
            }
            $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

            // Filter by status
            if (!empty($filter->filter_status)) {
                if ($filter->filter_status === "approved") {
                    $this->db->where('rar.status', 1);
                } else if ($filter->filter_status === "rejected") {
                    $this->db->where('rar.status', 2);
                }
            }

            if (!empty($filter->search)) {
	            $this->db->group_start();
	            for ($i = 0; $i < count($src_columns); $i++) {
	                $column_search = $src_columns[$i];
	                if (strstr($column_search, "as") !== false) {
	                    $serch_column = explode(" as ", $column_search);
	                    if ($serch_column[0] != 'null')
	                        $this->db->or_like($serch_column[0], $filter->search);
	                }
	                else if ($column_search != 'null') {
	                    $this->db->or_like($column_search, $filter->search);
	                }
	            }
	            $this->db->group_end();
	        }

            $column = array('rar.id', 'rar.name', 'rar.email', 'rar.phone', 'rar.status', 'rar.relevant_note', 'rar.written_reference','DATE_FORMAT(rar.created,"%d/%m/%Y %H:%i") as created','DATE_FORMAT(rar.updated,"%d/%m/%Y %H:%i") as updated');

            $this->db->select("(select form_id from tbl_recruitment_form_applicant as rfa where rfa.reference_id = rar.id AND rfa.archive = 0) as form_id");
	        $this->db->select("(select id from tbl_recruitment_form_applicant as rfa where rfa.reference_id = rar.id  AND rfa.archive = 0) as interview_applicant_form_id");
	        $this->db->select("(
	            CASE 
	                WHEN rar.status = 1 THEN 'Approved'
	                ELSE 'Rejected'
	                END
	        ) as status_value");
	        $this->db->select("(
	            CASE 
	                WHEN rar.written_reference = 1 THEN 'Yes'
	                ELSE 'No'
	                END
	        ) as written_reference_check");

	        $this->db->select($column);
	        $this->db->from("tbl_recruitment_applicant_reference as rar");
	        $where = array('rar.applicant_id' => $applicant_id, 'rar.archive' => 0);
	        if ($reference_id != '') {
	        	$where['rar.id'] = $reference_id;
	        }

	        $this->db->where($where);
            $this->db->order_by($orderBy, $direction);
            $this->db->limit($limit, ($page * $limit));

            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            #last_query();die;
            $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

            if ($dt_filtered_total % $limit == 0) {
                $dt_filtered_total = ($dt_filtered_total / $limit);
            } else {
                $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
            }

            $dataResult = $query->result();
            if (!empty($dataResult)) {
                foreach ($dataResult as $val) {
                    $str = $val->updated;
                    preg_match_all("/\d+/", $str, $matches);
                    if($matches[0][0]<1){
                        $val->updated=$val->created;
                    }
            }

            return $dataResult;
        }
    }
   }

    /**
     * Archive Reference
     * @param {int} reference_id
     */
    function archive_reference($reference_id, $adminId) {
    	// update member documents
        $upd_data["updated"] = DATE_TIME;
        $upd_data["archive"] = 1;
        $result = [ "id" => $reference_id];
        $result = $this->basic_model->update_records("recruitment_applicant_reference", $upd_data, ["id" => $reference_id]);
        # logging action
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully archived reference with ID of %s by %s", $reference_id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully archived reference ID of %s by %s", $reference_id, $adminName));  // set title in log
        $this->loges->setDescription($result);
        $this->loges->setUserId($reference_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        return $result;
    }

     /**
     * Update status Reference
     * @param {int} reference_id
     */
    function update_reference_status($reference_id, $status, $adminId) {
    	// update member documents
        $upd_data["updated"] = DATE_TIME;
        $upd_data["status"] = $status;
        $result = $this->basic_model->update_records("recruitment_applicant_reference", $upd_data, ["id" => $reference_id]);

        return $result;
    }

    /**
     * Mark as Reference verfied
     * @param {int} reference_id
     */
    function update_reference_marked($applicant_id, $application_id, $status, $adminId) {
    	// update member documents
        $upd_data["updated"] = DATE_TIME;
        $upd_data["is_reference_marked"] = $status;
        $result = $this->basic_model->update_records("recruitment_applicant_applied_application", $upd_data, [ "applicant_id" => $applicant_id, "id" => $application_id]);

        return $result;
    }
}