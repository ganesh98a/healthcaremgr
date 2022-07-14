<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Recruitment_jobs_model extends CI_Model {

	public function __construct() {
        // Call the CI_Model constructor
		parent::__construct();
	}

	public function create_Jobs_delete($objJobs) {
		$tbl_recruitment_jobs= TBL_PREFIX.'recruitment_jobs';

		$arrJobs = array();
		$arrJobs['jobname']=$objJobs->getJobName();
		$arrJobs['status']=$objJobs->getStatus();
		$arrJobs['job_position']=$objJobs->getJobPosition();
		$arrJobs['phone']=$objJobs->getPhone();
		$arrJobs['email']=$objJobs->getEmail();
		$arrJobs['weblink']=$objJobs->getWebLink();
		$arrJobs['job_description']=$objJobs->getJobDescription();
		$arrJobs['job_start_date']=$objJobs->getJobStartDate();
		$arrJobs['job_end_date']=$objJobs->getJobEndDate();

		if($objJobs->getId()>0){
			$this->db->where('id', $objJobs->getId());
			$insert_query =$this->db->update($tbl_recruitment_jobs,$arrJobs);
			$this->db->last_query();

		}else{
			$arrJobs['created'] = $objJobs->getCreated();
			$insert_query = $this->db->insert($tbl_recruitment_jobs, $arrJobs);
			$questionid=$this->db->insert_id();
			//$this->insert_answer($questionid,$objQuestion->getAnswer());
		}
		return $insert_query;
	}

	private function insert_answer($question,$arrAnswer){
		$tbl_question_answer= TBL_PREFIX.'recruitment_additional_questions_answer';
		foreach($arrAnswer as $key=>$value){
			$arAnswer = array();
			$arAnswer['question'] = $question;
			$arAnswer['question_option'] = $value->value;
			$arAnswer['serial'] = $value->lebel;
			$arAnswer['answer'] = $value->checked;
			$this->db->insert($tbl_question_answer, $arAnswer);
		}
	}

	private function update_answer($question,$arrAnswer){
		$tbl_question_answer= TBL_PREFIX.'recruitment_additional_questions_answer';
		foreach($arrAnswer as $key=>$value){
			$arAnswer = array();
			$arAnswer['question'] = $question;
			$arAnswer['question_option'] = $value->value;
			$arAnswer['serial'] = $value->lebel;
			$arAnswer['answer'] = $value->checked;
			$this->db->where('id', $value->answer_id);
			$this->db->update($tbl_question_answer,$arAnswer);
		}
	}

	public function delete_Questions($objQuestion){
		$tbl_question= TBL_PREFIX.'recruitment_additional_questions';
		$this->db->where('id', $objQuestion->getId());
		$this->db->update($tbl_question,array('archive'=>1));
		return $this->db->affected_rows();
	}

	private function remove_answer(){

	}

	public function question_list(){
		$tbl_question= TBL_PREFIX.'recruitment_additional_questions';
		$tbl_question_topic= TBL_PREFIX.'recruitment_question_topic';
		$tbl_question_answer= TBL_PREFIX.'recruitment_additional_questions_answer';

		$select_column = array($tbl_question . ".id", $tbl_question . ".question", $tbl_question . ".status", $tbl_question . '.created', $tbl_question . '.training_category',$tbl_question . '.question_type',$tbl_question . '.question_topic',$tbl_question . ".created_by" ,$tbl_question . ".updated" ,$tbl_question_topic . ".topic");

		$dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
		$this->db->from($tbl_question);
		$this->db->where('archive', '0');
		$this->db->join($tbl_question_topic,$tbl_question.'.question_topic =' . $tbl_question_topic . '.id', 'inner');
		$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
		$dataResult = $query->result();
		$dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

		foreach($dataResult as $data){

			$select_answer_column = array($tbl_question_answer . ".id as answer_id",$tbl_question_answer . ".answer as checked",$tbl_question_answer . ".question_option as value", $tbl_question_answer . ".serial as lebel");
			$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_answer_column)), false);
			$this->db->from($tbl_question_answer);
			$this->db->where('question',$data->id);
			$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
			$dataAnsResult = $query->result_array();
			$data->answers=$dataAnsResult;
		}
		$return = array('count' => $dt_filtered_total, 'data' => $dataResult);
		return $return;
	}

	public function save_job($post_data)
	{
		if (!empty($post_data))
		{
			$save_as = 0;
			$job_operation = $post_data['job_operation'];
            
            $currentDate = strtotime(date('Y-m-d'));

            $from_date   = strtotime(date('Y-m-d', strtotime($post_data['from_date'])));
            $to_date     = strtotime(date('Y-m-d', strtotime($post_data['to_date'])));

			if(isset($post_data['save_as_draft']) && $post_data['save_as_draft'] == 1 && $post_data['post_job'] == '' && empty($post_data['job_status']) ){
				$save_as = 0;
			}
			else if(isset($post_data['post_job']) && $post_data['post_job'] == 1 && $post_data['save_as_draft'] == '' ){
				if(($from_date == $currentDate) || ($currentDate > $from_date && $currentDate < $to_date ) ) {
					$save_as = 3;
                }
				else if($from_date > $currentDate){
					$save_as = 5;
                }
			}
            else if( $post_data['job_status'] == 5 &&  
            ($currentDate >= $from_date && $currentDate < $to_date)  ) {
                $save_as = 3;
            }
            else if($post_data['job_status'] == 3 && ($from_date > $currentDate )   ){
                $save_as = 5;
            }
            else if($currentDate >= $from_date && $currentDate <= $to_date){
                $save_as = 3;
            } else {
                $save_as = $post_data['job_status'];
            }

			if(isset($post_data['is_recurring']) && $post_data['is_recurring'] ==1){
				$to_date = '';
				$recurring_type = isset($post_data['recurring_type'])?$post_data['recurring_type']:0;
			}else{
				$to_date = DateFormate($post_data['to_date']);
				$recurring_type = 0;
			}

			# adding/updating a job position first.
			# Instead of fixed list of position, we will have job title getting used as position
			if(isset($post_data['position']) && $post_data['position'] && $job_operation == 'E')
				$this->Basic_model->update_records('recruitment_job_position', ["title" => $post_data['title'], 'updated'=>DATE_TIME, "archive" => 0],array('id'=>$post_data['position']));
			else
				$post_data['position'] = $this->Basic_model->insert_records('recruitment_job_position', ["title" => $post_data['title'], 'created'=>DATE_TIME, "archive" => 0]);

			$organisation_data = array('title' => '',
				'type' => isset($post_data['type'])?$post_data['type']:'',
				'title' => $post_data['title'],
				'category' => $post_data['category'],
				'sub_category' => $post_data['sub_category'],
				'position' => isset($post_data['position'])?$post_data['position']:'0',
				'employment_type' => $post_data['employment_type'],
				'salary_range' => isset($post_data['salary_range'])?$post_data['salary_range']:'0',
				'is_salary_publish' => isset($post_data['is_salary_publish']) ? $post_data['is_salary_publish'] : '0',
				'is_cat_publish' => isset($post_data['is_cat_publish']) ? $post_data['is_cat_publish'] : '0',
				'is_subcat_publish' => isset($post_data['is_subcat_publish']) ? $post_data['is_subcat_publish'] : '0',
				'is_emptype_publish' => isset($post_data['is_emptype_publish']) ? $post_data['is_emptype_publish'] : '0',
				'template' => $post_data['activeTemplate'],
				'created'=>DATE_TIME,
				'from_date' => isset($post_data['from_date'])?DateFormate($post_data['from_date']):'',
				'to_date' => $to_date,
				'is_recurring' => isset($post_data['is_recurring'])?$post_data['is_recurring']:0,
				'recurring_type' => $recurring_type,
				'individual_interview_count' => $post_data['individual_interview_count']??0,
				'owner' => $post_data['owner_id'],
				'bu_id' => $post_data['bu_id'] ?? NULL
			);

			if($job_operation == 'E'){
				$jobId = $post_data['job_id'];
				if(isset($post_data['job_status']) ){ // && ($post_data['job_status']!=3
					$organisation_data['job_status'] = $save_as;
				}else{
					$save_as = $post_data['job_status'];
				}
				$this->Basic_model->update_records('recruitment_job', $organisation_data,array('id'=>$jobId));
			}
			else{
				$organisation_data['job_status'] = $save_as;
				$jobId = $this->Basic_model->insert_records('recruitment_job', $organisation_data);
			}

			$address = [];
			$street = ' ';
			$complete_address = null;
			if(isset($post_data['complete_address']) && !empty($post_data['complete_address']))
			{
				$address = get_address_from_google_response($post_data['complete_address']);
				$street = (isset($address['street_no']) ? $address['street_no']. ' ' : '').(isset($address['street']) ? $address['street'] : '');
				$complete_address = $post_data['complete_address'];
			}

			$organisation_adr = array('jobId' => $jobId,
				'street' => $street,
				'city' => isset($address['city'])?$address['city']:'',
				'state' => isset($address['state_id'])?$address['state_id']:'',
				'postal' => isset($address['postal_code'])?$address['postal_code']:'',
				'lat' => isset($address['lat'])?$address['lat']:'',
				'long' => isset($address['long'])?$address['long']:'',
				'complete_address' => isset($address['full_address'])?$address['full_address']:'',
				'phone' => isset($post_data['phone'])?$post_data['phone']:'',
				'email' => isset($post_data['email'])?$post_data['email']:'',
				'website' => isset($post_data['website'])?$post_data['website']:'',
				'created'=>DATE_TIME,
				'google_response'=>json_encode($complete_address)
			);

			if (!empty($organisation_adr)){
				if($job_operation == 'E'){
					$this->update_recruitment_job_location($organisation_adr, array('jobId'=>$jobId,'id'=>$post_data['job_location_id']));
				}
				else{
					$this->create_recruitment_job_location($organisation_adr);
				}
			}

			//if (!empty($post_data['publish_to']) ) 
            if( $save_as == 3)
			{
				$job_published = array();
                $response = $this->basic_model->get_record_where('recruitment_channel', $column = array('id', 'channel_name'), $where = array('archive' => '0'));
                if (!empty($response)) {
                    foreach ($response as $val) {
                        $all_details[] = array('drp_dwn' => array('label' => $val->channel_name, 'value' => $val->id), 'question' => [], 'channel_name' => $val->channel_name, 'question_tab' => false, 'drp_dwn_val' => $val->id);
                    }
                }

				if($job_operation == 'E'){
					$this->Basic_model->update_records('recruitment_job_published_detail', array('archive'=>1),array('jobId'=>$jobId));
					$this->Basic_model->update_records('recruitment_job_question', array('archive'=>1),array('jobId'=>$jobId));
				}

				foreach ($all_details as $key => $val)
				{
					$job_published = array('jobId' => $jobId,
						'channel' => isset($val['drp_dwn'])?$val['drp_dwn']['value']:'',
						'created'=>DATE_TIME,
					);
					$published_detail_id = $this->Basic_model->insert_records('recruitment_job_published_detail', $job_published);

					if(!empty($val['question']))
					{
						foreach ($val['question'] as $que)
						{
							$ar = array('question'=>$que['question'],'created'=>DATE_TIME,'jobId' => $jobId,'published_detail_id'=>$published_detail_id);
							$this->Basic_model->insert_records('recruitment_job_question', $ar);
						}
					}
				}
			} else {
                $this->Basic_model->delete_records('recruitment_job_published_detail', $where = array(
                    'jobId' => $jobId
                ));
            }

			if(!empty($post_data['all_docs_job_apply']) || !empty($post_data['all_docs_recruit']))
			{
				if($job_operation == 'E') {
					$archiveResult =  $this->Basic_model->update_records('recruitment_job_posted_docs', array('archive'=>1),array('jobId'=> $jobId));
					if (!empty($archiveResult)) {
						$this->create_update_job_recruit_docs($post_data['all_docs_job_apply'], $post_data['all_docs_recruit'], $jobId);
					}
				} else {
					$this->create_update_job_recruit_docs($post_data['all_docs_job_apply'], $post_data['all_docs_recruit'], $jobId);
				}
			}


			$template_detail = array('jobId' => $jobId,
				'template' => $post_data['activeTemplate'],
				'layout_text' => $post_data['job_content'],
				'created'=>DATE_TIME
			);

			if($job_operation == 'E'){
				$this->Basic_model->update_records('recruitment_selected_job_template', array('archive'=>1),array('jobId'=>$jobId));
				#$this->Basic_model->update_records('recruitment_job_stage', array('archive'=>1),array('jobId'=>$jobId));
			}
			$this->Basic_model->insert_records('recruitment_selected_job_template', $template_detail);

			if($job_operation != 'E'){
				$this->save_job_stage($post_data,$jobId);
			}
			// insert or update associated form id
			if (isset($post_data['form_id']) && !empty($post_data['form_id'])) {
				$associatedFormIdsQuery = $this->db
				->from('tbl_recruitment_job_forms')
				->where([ 'job_id' => $jobId ])
				->select()
				->get();

				$associatedForm = $associatedFormIdsQuery->row_array();
				if (empty($associatedForm)) {
					$this->db->insert('tbl_recruitment_job_forms', [ 'job_id' => $jobId, 'form_id' => $post_data['form_id'] ]);
				} else {
					$this->db->update('tbl_recruitment_job_forms',
						[ 'form_id' => $post_data['form_id'] ],
						[ 'id' => $associatedForm['id'] ]
					);
				}
			}

			$msg = '';
			$save_as = (int)$save_as;
			$job_status_str = ($save_as === 0)?'Draft':(($save_as === 5)?'Scheduled':(($save_as === 3)?'Live':''));
			/**@todo to update the status message */
			if($job_operation == 'E')
				$msg = 'Job updated as '.$job_status_str.' successfully.';
			else
				$msg = 'Job saved as '.$job_status_str.' successfully.';

			return array('jobId' => $jobId,'msg'=> $msg);
		}
	}

	/**
	 * @param jobs docs
	 * @param recruit docs
	 * insert recruit job docs records
	 */
	public function create_update_job_recruit_docs($docs_job, $docs_recruit, $jobId) {
		if(!empty($docs_job)) {
			$job_ar = [];
			foreach ($docs_job as $docs) {
				if(isset($docs['clickable']) && ($docs['clickable'] || $docs['clickable'] == 1)) {
					$job_ar[] = array('requirement_docs_id' => $docs['value'],'created' => DATE_TIME,'jobId' => $jobId,'is_required' => isset($docs['mandatory']) && $docs['mandatory'] ==true ?1:0);
				}
			}
			if(!empty($job_ar)) {
				$this->Basic_model->insert_records('recruitment_job_posted_docs', $job_ar,true);
			}
		}

		if(!empty($docs_recruit)) {
			$recruit_ar = [];
			foreach ($docs_recruit as $docs) {
				if(isset($docs['clickable']) && ($docs['clickable'] || $docs['clickable'] == 1)) {
					$recruit_ar[] = array('requirement_docs_id' => $docs['value'], 'created'=>DATE_TIME, 'jobId' => $jobId,'is_required' => isset($docs['mandatory']) && $docs['mandatory'] ==true ?1:0);
				}
			}
			if(!empty($recruit_ar)) {
				$this->Basic_model->insert_records('recruitment_job_posted_docs', $recruit_ar,true);
			}
		}
	}
	
	public function get_all_jobs($reqData, $filter_condition = '')
	{
		$business_unit = $reqData->business_unit ?? NULL;
		
		$reqData = $reqData->data;
		$bu_id_filter_value = $reqData->bu_id_filter_value ?? NULL;
		$limit = $reqData->pageSize?? 20;
		$page = $reqData->page?? 1;
		$sorted = $reqData->sorted?? [];
		$filter = $reqData->filtered?? null;		
        $filter_by = '';
        if (!empty($filter) && is_object($filter) && property_exists($filter, 'filterBy')) {
     		$filter_by = $filter->filterBy;
        }
        $srch_value = '';
        if (!empty($filter) && is_object($filter) && property_exists($filter, 'srch_box')) {
            $srch_value = $filter->srch_box;
        }
		$orderBy = '';
		$direction = '';
		$tbl_job = TBL_PREFIX . 'recruitment_job';

		$owner_sub_query = $this->get_owner_by_sub_query('owner',$tbl_job);

        $available_columns = array("id", "job_id","job_status", "position", "job_category","employment_type","bu.business_unit_name");
		if (!empty($sorted)) {
			if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_columns) ) {
				$orderBy = $sorted[0]->id;
				$direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
			}
		} else {
			$orderBy = $tbl_job . '.id';
			$direction = 'DESC';
		}
		$orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        if ((!empty($filter_by) || $filter_by == 0) && $filter_by != 'all') {			
		    $this->db->where($tbl_job . ".job_status".' =', $filter_by);
        }
		$this->db->where($tbl_job . ".archive".' !=', 1);  //deleted job

		if (!empty($srch_value)) {
			$this->db->group_start();
			$src_columns = array("CONCAT(tbl_recruitment_applicant.firstname,' ',tbl_recruitment_applicant.middlename,' ',tbl_recruitment_applicant.lastname)", "CONCAT(tbl_recruitment_applicant.firstname,' ',tbl_recruitment_applicant.lastname)", "tbl_recruitment_job_position.title as position", "tbl_recruitment_job_category.name as job_category","tbl_references.display_name as employment_type", "bu.business_unit_name");

			for ($i = 0; $i < count($src_columns); $i++) {
				$column_search = $src_columns[$i];
				if (strstr($column_search, "as") !== false) {
					$serch_column = explode(" as ", $column_search);
					$this->db->or_like($serch_column[0], $srch_value);
				} else {
					$this->db->or_like($column_search, $srch_value);
				}
			}
			$this->db->group_end();
		}

		$select_column = array($tbl_job . ".id",$tbl_job . ".id as job_id",$tbl_job . ".created", $tbl_job . ".job_status", "tbl_recruitment_job_position.title as position","tbl_recruitment_job_category.name as job_category",
        "(select count('id') from tbl_recruitment_applicant_applied_application raaa where raaa.`jobId` = `tbl_recruitment_job`.`id` AND raaa.`archive` = 0 ) as applicant_cnt",
        "(select count('id') from tbl_recruitment_applicant_applied_application raaa where raaa.`jobId` = `tbl_recruitment_job`.`id` AND raaa.`archive` = 0 AND raaa.application_process_status = 0 ) as new_applicant",
        "tbl_references.display_name as employment_type",$tbl_job . ".owner","tbl_recruitment_job_category.id as job_category_id", "bu.business_unit_name");

		$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
		$this->db->from($tbl_job);

		$this->db->join('tbl_recruitment_job_position', 'tbl_recruitment_job_position.id = ' . $tbl_job . '.position AND tbl_recruitment_job_position.archive = 0', 'inner');
		$this->db->join('tbl_recruitment_job_category', 'tbl_recruitment_job_category.id = ' . $tbl_job . '.category AND tbl_recruitment_job_category.archive = 0', 'inner');
		$this->db->join('tbl_references', 'tbl_references.id = ' . $tbl_job . '.employment_type AND tbl_references.archive = 0', 'inner');
        #$this->db->join('tbl_recruitment_job_published_detail', 'tbl_recruitment_job_published_detail.jobId = ' . $tbl_job . '.id AND tbl_recruitment_job_published_detail.archive = 0', 'inner');
		$this->db->join('tbl_recruitment_applicant', 'tbl_recruitment_applicant.jobId = ' . $tbl_job . '.id AND tbl_recruitment_applicant.archive = 0 AND tbl_recruitment_applicant.status = 1', 'left');
		$this->db->join('tbl_business_units bu', 'bu.id = ' . $tbl_job . '.bu_id', 'left');
		
		$this->db->select("(" . $owner_sub_query . ") as owner_name");
		
		//Check Business unit for non super admin user
		if(empty($business_unit['is_super_admin'])) {
			$this->db->where('tbl_recruitment_job.bu_id', $business_unit['bu_id']);
		} else if(!empty($bu_id_filter_value)){
			$this->db->where('tbl_recruitment_job.bu_id', $bu_id_filter_value);
		}

		$this->db->group_by($tbl_job . ".id");
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
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

		$status = false;
		if (!empty($dataResult)) {
			$status = true;
			foreach ($dataResult as $val) {
				$val->job_status = (isset($val->job_status) && $val->job_status == 0 ?'Draft':(($val->job_status == 5)?'Scheduled':(($val->job_status == 2)?'Closed':(($val->job_status == 3)?'Live':'Canceled'))));

				$row = $this->basic_model->get_row('recruitment_job_published_detail', $columns = array('channel_url'), array('jobId'=>$val->job_id,'channel'=>2,'archive'=>0));

				$val->website_url = !empty($row) && $row->channel_url!=null ?$row->channel_url:'';
				$val->seek_url = 'https://www.seek_1.com';
				$val->position = $val->position;
				$val->created = date('d/m/Y',strtotime($val->created));
			}
		}
		
		return array('status'=>true, 'count' => $dt_filtered_total, 'data' => $dataResult, 'total_item' => $all_count);
		
	}

	/*
     * it is used for making sub query owner (who owner of member)
     * return type sql
     */
    private function get_owner_by_sub_query($column_by, $tbl_alais) {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = ".$tbl_alais.".".$column_by, null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

	public function get_job_detail($reqData)
	{
		if(!empty($reqData))
		{
			$job_id = $this->db->escape_str($reqData->jobId, true);
			$tbl_job = TBL_PREFIX . 'recruitment_job';

			$job_data = "SELECT rj.title,rj.is_recurring,DATE_FORMAT(rj.from_date,'%Y/%m/%d') as from_date,DATE_FORMAT(rj.to_date,'%Y/%m/%d') as to_date,rj.type,rj.category,rj.sub_category,rj.position,rj.employment_type,rj.salary_range,rj.is_salary_publish,rj.is_cat_publish,rj.is_subcat_publish,rj.is_emptype_publish,rj.owner,jl.google_response,jl.complete_address as job_location,jl.phone,jl.email,jl.website,jl.id as job_location_id,sjt.layout_text,sjt.template as activeTemplate,rj.job_status,rj.recurring_type,rj.individual_interview_count, (Select CONCAT_WS(' ', m.firstname,m.lastname) from tbl_member as m where m.uuid=rj.owner ) as owner_name, rj.bu_id as bu_id  FROM `tbl_recruitment_job` as rj
			LEFT JOIN `tbl_recruitment_job_location` as jl ON jl.jobId = rj.id AND jl.archive = '0'
			LEFT JOIN `tbl_business_units` as bu ON bu.id = rj.bu_id
			LEFT JOIN `tbl_recruitment_selected_job_template` as sjt ON sjt.jobId = rj.id AND sjt.archive = '0' 			
			WHERE rj.archive = '0' AND rj.id = '".$job_id."' AND rj.job_status!=4 ";
			$job_data_ex = $this->db->query($job_data);

			$job_data_ary = $job_data_ex->row_array();
			
			if(empty($job_data_ary) || is_null($job_data_ary))
			{
				return array('status' => false);				
			}

			$publish_data = "SELECT  jpd.channel as drp_dwn_val,rc.channel_name,rc.id as channel_id FROM `tbl_recruitment_job_published_detail` as jpd
			INNER JOIN `tbl_recruitment_channel` as rc ON jpd.channel = rc.id AND rc.archive = '0'
			WHERE  jpd.archive = '0'  AND jpd.jobId  = '".$job_id."'";
			$publish_data_ex = $this->db->query($publish_data);
			$publish_ary = $publish_data_ex->result_array();
			$publish_data_ary =  isset($publish_ary) && !empty($publish_ary)?$publish_ary:array();
			if(!empty($publish_data_ary))
			{
				$jobs_chaneel_question = $this->get_job_question($job_id);
				if(!empty($jobs_chaneel_question))
				{
					$publish_data_ary= array_map(function ($val) use ($jobs_chaneel_question,$publish_data_ary) {
						$val['question']= array_key_exists($val['channel_id'], $jobs_chaneel_question)?$jobs_chaneel_question[$val['channel_id']]:array();
						$val['drp_dwn']= array('label'=>$val['channel_name'],'value'=>$val['channel_id']);
						return $val;
					}, $publish_data_ary);
				}
				else
				{
					$publish_data_ary= array_map(function ($val) use ($publish_data_ary) {
						$val['drp_dwn']= array('label'=>$val['channel_name'],'value'=>$val['channel_id']);
						return $val;
					}, $publish_data_ary);
				}
			}
			
			$emp_type_rows = $this->get_employement_type_ref_list("employment_type");

			$responsePosition = $this->basic_model->get_record_where('recruitment_job_position', $column = array('title as label', 'id as value'), $where = array('archive' => '0'));
			$pos_rows  = isset($responsePosition) && !empty($responsePosition) ? $responsePosition:array();

			$responseCategory = $this->basic_model->get_record_where('recruitment_job_category', $column = array('parent_id', 'id as value','name as label'), $where = array('archive' => '0'));
			$data_category = isset($responseCategory) && !empty($responseCategory) ? $responseCategory:array();

			$responseSubCategory = $this->basic_model->get_record_where('recruitment_job_category', $column = array('parent_id', 'id as value','name as label'), $where = array('archive' => '0','parent_id'=>$job_data_ary['category']));
			$data_sub_category = isset($responseSubCategory) && !empty($responseSubCategory) ? $responseSubCategory:array();

			$responseSalaryRange = $this->basic_model->get_record_where('recruitment_job_salary_range', $column = array('title as label', 'id as value'), $where = array('archive' => '0'));
			$data_salaryRange = isset($responseSalaryRange) && !empty($responseSalaryRange) ? $responseSalaryRange:array();

			$responseJobType = $this->basic_model->get_record_where('recruitment_job_type', $column = array('title as label', 'id as value'), $where = array('archive' => '0'));
			$job_type_rows = isset($responseJobType) && !empty($responseJobType) ? $responseJobType:array();

			//$all_docs_job_apply = $this->basic_model->get_record_where('references', $column = array('display_name as title', 'id'), $where = array('archive' => '0','type' => 5));
			$data['doc_category'] = 'apply_job';
			$data['col1'] = 'title';
			$data['col2'] = 'id';
			$data['module_id'] = REQUIRMENT_MODULE_ID;

			$all_docs_job_apply = $this->Document_type_model->get_document_type($data);

			$data['doc_category'] = 'recruitment_stage';
			$all_docs_recruit = $this->Document_type_model->get_document_type($data);
			//$all_docs_recruit = $this->basic_model->get_record_where('references', $column = array('display_name as title', 'id'), $where = array('archive' => '0','type' => 4));

			$documnets = "SELECT  tbl_recruitment_job_posted_docs.requirement_docs_id ,tbl_recruitment_job_posted_docs.is_required FROM `tbl_recruitment_job_posted_docs`
			WHERE jobId = '".$job_id."' AND tbl_recruitment_job_posted_docs.archive=0 ";
			$wsql = $this->db->query($documnets);
			$rows = $wsql->result_array();
			if(!empty($rows))
			{
				$rows = pos_index_change_array_data($rows, 'requirement_docs_id');
			}

			if (!empty($all_docs_job_apply))
			{
				foreach ($all_docs_job_apply as $val)
				{
					$val->label = $val->title;
					$val->value = $val->id;
					if(array_key_exists($val->id, $rows))
					{
						$val->clickable = true;
						$val->selected = true;
						if($rows[$val->id]['is_required'] == 1)
						{
							$val->optional = false;
							$val->mandatory = true;
						}
						else
						{
							$val->optional = true;
							$val->mandatory = false;
						}
					}
					else
					{
						$val->clickable = false;
						$val->selected = false;
						$val->optional = false;
						$val->mandatory = false;
					}
				}
			}

			if (!empty($all_docs_recruit))
			{
				foreach ($all_docs_recruit as $val)
				{
					$val->label = $val->title;
					$val->value = $val->id;
					if(array_key_exists($val->id, $rows))
					{
						$val->clickable = true;
						$val->selected = true;
						if($rows[$val->id]['is_required'] == 1)
						{
							$val->optional = false;
							$val->mandatory = true;
						}
						else
						{
							$val->optional = true;
							$val->mandatory = false;
						}
					}
					else
					{
						$val->clickable = false;
						$val->selected = false;
						$val->optional = false;
						$val->mandatory = false;
					}
				}
			}

			$main_aray = array();
			$main_aray['all_docs_job_apply'] = $all_docs_job_apply;
			$main_aray['all_docs_recruit'] = $all_docs_recruit;
			$main_aray['job_employment_type'] = $emp_type_rows;
			$main_aray['job_position'] = $pos_rows;
			$main_aray['job_sub_category'] = $data_sub_category;
			$main_aray['job_category'] = $data_category;
			$main_aray['job_type'] = $job_type_rows;
			$main_aray['publish_to'] = $publish_data_ary;
			$main_aray['job_salary_range'] = $data_salaryRange;

			$main_aray['check_documents'] = true;
			$main_aray['mode'] = 'view';
			$main_aray['from_date'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['from_date']!=''?$job_data_ary['from_date']:'';
			$main_aray['is_recurring'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['is_recurring']!=''?$job_data_ary['is_recurring']:false;
			$main_aray['recurring_type'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['recurring_type']!=''?$job_data_ary['recurring_type']:false;
			$main_aray['to_date'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['to_date']!=''?$job_data_ary['to_date']:'';
			$main_aray['job_status'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['job_status']!=''?$job_data_ary['job_status']:'';
			$main_aray['individual_interview_count'] = $job_data_ary['individual_interview_count']??0;

			$main_aray['is_salary_publish'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['is_salary_publish']== 1?true:false;
			$main_aray['is_cat_publish'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['is_cat_publish']== 1?true:false;
			$main_aray['is_subcat_publish'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['is_subcat_publish']== 1?true:false;
			$main_aray['is_emptype_publish'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['is_emptype_publish']== 1?true:false;
			$main_aray['category'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['category']!=''?$job_data_ary['category']:'';
			$main_aray['sub_category'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['sub_category']!=''?$job_data_ary['sub_category']:'';
			$main_aray['position'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['position']!=''?$job_data_ary['position']:'';
			$main_aray['employment_type'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['employment_type']!=''?$job_data_ary['employment_type']:'';
			$main_aray['salary_range'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['salary_range']!=''?$job_data_ary['salary_range']:'';
			$main_aray['complete_address'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['job_location']!=''?array('formatted_address'=>$job_data_ary['job_location']):'';
			$main_aray['phone'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['phone']!=''?$job_data_ary['phone']:'';
			$main_aray['email'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['email']!=''?$job_data_ary['email']:'';
			$main_aray['website'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['website']!=''?$job_data_ary['website']:'';
			$main_aray['job_content'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['layout_text']!=''?$job_data_ary['layout_text']:'';
			$main_aray['type'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['type']!=''?$job_data_ary['type']:'';
			$main_aray['activeTemplate'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['activeTemplate']!=''?$job_data_ary['activeTemplate']:'';
			$main_aray['google_response'] = isset($job_data_ary) && !empty($job_data_ary) && $job_data_ary['google_response']!=''?json_decode($job_data_ary['google_response']):'';
			$main_aray['job_location_id'] = $job_data_ary['job_location_id'];
			$main_aray['title'] = $job_data_ary['title'];
			$main_aray['owner_id'] = $job_data_ary['owner'];			
			$main_aray['owner'] = array('value' => $job_data_ary['owner'], 'label' => $job_data_ary['owner_name']);
			$interview_stage = $this->get_job_stage($job_id);
			$main_aray['interview_stage'] = $interview_stage;
			
			$main_aray['bu_id'] = $job_data_ary['bu_id'];
			$temp['parentState'] = $main_aray;
			return array('status' => true, 'data' => $temp);
		}
	}

	/*
     * fetching the reference list of one reference type of pay rates
     */
    public function get_employement_type_ref_list($keyname, $return_key = false) {
        if($return_key)
            $this->db->select(["r.key_name as label", 'r.id as value']);
        else
            $this->db->select(["r.display_name as label", 'r.id as value']);
        
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = '{$keyname}' AND rdt.archive = 0", "INNER");
        $this->db->where("r.archive", 0);
        $query = $this->db->get();
        if($query->num_rows() > 0 && $return_key == false)
            return $query->result_array();
        else if($query->num_rows() > 0 && $return_key == true) {
            $retrow = null;
            foreach($query->result_array() as $row) {
                $retrow[$row['label']] = $row['value'];
            }
            return $retrow;
        }
    }

	public function get_job_question($job_id)
	{
		$job_id = $this->db->escape_str($job_id, true);
		$sql = "SELECT rjq.id,rjq.question,rjpd.channel as value FROM tbl_recruitment_job_question as rjq
		inner join tbl_recruitment_job_published_detail as rjpd on rjpd.jobId = rjq.jobId AND rjpd.id=rjq.published_detail_id and rjpd.archive=0
		WHERE rjq.jobId='".$job_id."' AND rjq.archive=0";
		$sql_ex = $this->db->query($sql);
		$output = $sql_ex->result_array();
		$outpt_ary = isset($output) && !empty($output)?$output:array();
		$temp_hold = array();
		$sub_ary = array();
		$main_aray = array();
		if(!empty($output))
		{
			foreach ($output as $key => $ot)
			{
				$index = $ot['value'];
				if(!in_array($ot['value'], $temp_hold))
				{
					$temp_hold[] = $index;
				}
				$sub_ary['question'] = $ot['question'];
				$sub_ary['value'] = $index;
				$sub_ary['btn_txt'] = 'Edit';
				$sub_ary['editable_class'] = '';
				$sub_ary['id'] = '';
				$sub_ary['question_edit'] = false;
				$main_aray[$index][] = $sub_ary;
			}
		}
		#pr($main_aray);
		return $main_aray;
	}

	/*
    * This method is call from create job when add new document type is added.
    * This requirement is closed now and method is not in used.
    */
	public function save_job_required_documents($post_data)
	{
		if (!empty($post_data))
		{
			$template_detail = array('title' => $post_data['document_name'],
				'created'=>DATE_TIME
			);
			$id = $this->Basic_model->insert_records('recruitment_job_requirement_docs', $template_detail);
			if($id)
				return array('status' => true,'id'=>$id,'data'=>array('label' => $post_data['document_name'], 'value' => $id,'optional'=>false,'mandatory'=>true,'clickable'=>false,'selected'=>false));
			else
				return array('status' => false);
		}
	}

	public function update_job_status($post_data)
	{
		if (!empty($post_data))
		{
			$update_ary = array();

			if($post_data->status == 1)
				$update_ary['archive'] = 1;
			else
				$update_ary['job_status'] = $post_data->status;

			$id = $this->Basic_model->update_records('recruitment_job', $update_ary,array('id'=>$post_data->job_id));
			if($id)
				return array('status' => true);
			else
				return array('status' => false);
		}
	}

	public function check_live_job($jobId)
	{
		$sub_query1 = $this->job_description_subquery($jobId);

		$tbl_job = TBL_PREFIX . 'recruitment_job';

		#$this->db->where($tbl_job . ".job_status".' =', 3);
		$this->db->where($tbl_job . ".archive".' !=', 1);

		$select_column = array($tbl_job . ".id",$tbl_job . ".created", $tbl_job . ".job_status", $tbl_job . ".title", $tbl_job . ".is_cat_publish", $tbl_job . ".is_subcat_publish", $tbl_job . ".is_emptype_publish", $tbl_job . ".is_salary_publish", $tbl_job . ".employment_type");

		$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

		$this->db->select("case when tbl_recruitment_job.employment_type!=0 THEN
			(SELECT tr.display_name FROM tbl_references as tr where tbl_recruitment_job.employment_type=tr.id AND tr.archive=0) ELSE '' END as employment_type", false);

		$this->db->select("case when tbl_recruitment_job.type!=0 THEN
			(SELECT rjt.title FROM tbl_recruitment_job_type as rjt where tbl_recruitment_job.type=rjt.id AND rjt.archive=0) ELSE '' END as job_type", false);

		$this->db->select("case when tbl_recruitment_job.category!=0 THEN
			(SELECT name FROM tbl_recruitment_job_category as rjc where tbl_recruitment_job.category=rjc.id AND rjc.archive=0) ELSE '' END as job_category", false);

		$this->db->select("case when tbl_recruitment_job.sub_category!=0 THEN
			(SELECT name FROM tbl_recruitment_job_category as rjc where tbl_recruitment_job.sub_category=rjc.id AND rjc.archive=0) ELSE '' END as job_sub_category", false);

		$this->db->select("case when tbl_recruitment_job.salary_range!=0 THEN
			(SELECT jsl.title FROM tbl_recruitment_job_salary_range as jsl where tbl_recruitment_job.salary_range=jsl.id AND jsl.archive=0) ELSE '' END as salary_range", false);

		$this->db->select('(' . $sub_query1 . ') as description');
		$this->db->from($tbl_job);

		$this->db->where($tbl_job . ".id", $jobId);
		#$this->db->where($tbl_job . ".from_date <", DATE_CURRENT);
		#$this->db->where($tbl_job . ".to_date>=", DATE_CURRENT);

		$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
		# last_query();
		$dataResult = $query->row();
		if(!empty($dataResult)){
			$location_detail = $this->job_location($jobId);
			$docs_detail = $this->job_required_docs($jobId);
			if(!empty($location_detail)){
				$dataResult->address = isset($location_detail->complete_address) ? $location_detail->complete_address:'N/A';
				$dataResult->phone = isset($location_detail->phone) ? $location_detail->phone:'N/A';
				$dataResult->email = isset($location_detail->email) ? $location_detail->email:'N/A';
				$dataResult->website = isset($location_detail->website) ? $location_detail->website:'N/A';
			}

			if(!empty($docs_detail)){
				$dataResult->docs = $docs_detail;
			}
		}
		return $dataResult;
	}

	public function job_description_subquery($jobId) {
		$this->db->flush_cache();
		$this->db->select(['template.layout_text']);
		$this->db->from('tbl_recruitment_selected_job_template as template');
		$this->db->join('tbl_recruitment_job as job', 'job.id = template.jobId AND template.archive = 0', 'INNER');
		$this->db->where(array('job.id' => $jobId));
		return $this->db->get_compiled_select();
	}

	public function job_location($jobId) {
		$this->db->select(['location.complete_address','location.phone','location.email','location.website']);
		$this->db->from('tbl_recruitment_job_location as location');
		$this->db->join('tbl_recruitment_job as job', 'job.id = location.jobId AND location.archive = 0', 'INNER');
		$this->db->where(array('job.id' => $jobId));
		$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
		$data_location = $query->row();
		return $data_location;
	}

	public function job_required_docs($jobId) {
		$this->db->select(['jpd.id','jpd.is_required','jrd.display_name as title','jrd.id as docs_p_id']);
		$this->db->from('tbl_recruitment_job_posted_docs as jpd');
		$this->db->join('tbl_references as jrd', "jrd.id = jpd.requirement_docs_id AND jrd.archive = 0 AND jrd.type = 5", 'INNER');
		$this->db->where(array('jpd.jobId' => $jobId,'jpd.archive'=>0));
		$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
		$data_docs = $query->result();
		return $data_docs;
	}


    /**
     *
     * @return array[]
     */
    public function job_question_forms_options($reqData = '')
    {
    	$INTERVIEW_TYPE_JOB_QUESTION = 5;
    	$where = [
    		'archive' => 0,
    		'interview_type' => $INTERVIEW_TYPE_JOB_QUESTION,
    	];		
		
		if(empty($reqData->business_unit['is_super_admin'])) {
			$where['bu_id'] = $reqData->business_unit['bu_id'];
		} else if(!empty($reqData->data->bu_id)) {
			//If business unit id is coming from create/update job page BU dropdown selection
			$where['bu_id'] = $reqData->data->bu_id;
		}

    	$query = $this->db
    	->from('tbl_recruitment_form')
    	->where($where)
    	->select([
    		'id as value',
    		'title as label'
    	])
    	->get();

    	return $query->result_array();
    }

    public function save_job_stage($reqData,$jobId)
    {
    	if(!empty($reqData)){
    		$this->db->select(['id']);
    		$this->db->from('tbl_recruitment_stage_label as sl');
    		$this->db->where(array('sl.archive' =>0));

    		if($reqData['interview_job_stage_id'] == 3){
    			$this->db->where('key_name!=','individual_interview');
    		}else if($reqData['interview_job_stage_id'] == 8){
    			$this->db->where('key_name!=','group_interview');
    			/*Logic to repeat individual interview as per count of job*/
    			$individual_interview_count = $reqData['individual_interview_count']??0;
    			if($individual_interview_count == 1){
    				$this->db->where_not_in('display_stage_number',array('3b','3c'));
    			}else if($individual_interview_count == 2){
    				$this->db->where_not_in('display_stage_number',array('3c'));
    			}else if($individual_interview_count == 3){

    			}
    		}

    		if($reqData['job_sub_stage_id'] == 6){
    			$this->db->where('key_name!=','offer');
    		}else if($reqData['job_sub_stage_id'] == 9){
    			$this->db->where('key_name!=','cab_day');
    		}
    		$this->db->order_by('stage_number');
    		$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
    		#last_query();
    		$stage_rows = $query->result_array();
    		if(!empty($stage_rows)){
    			$stage_ary = [];
    			foreach ($stage_rows as $key => $val) {
    				$temp['jobId']  = $jobId;
    				$temp['stage_id']  = $val['id'];
    				$temp['created']  = DATE_TIME;
    				$stage_ary[] = $temp;
    			}
    		}
    		if(!empty($stage_ary))
    			$this->Basic_model->insert_records('recruitment_job_stage', $stage_ary,true);
    	}
    }

    /*
    *get dynamic stage of job
    *@by job id
    */
    public function get_job_stage($job_id){
    	$this->db->select(['jps.id','jps.stage_id','rsl.key_name']);
    	$this->db->from('tbl_recruitment_job_stage as jps');
    	$this->db->join('tbl_recruitment_stage_label as rsl', 'rsl.id = jps.stage_id AND rsl.archive = 0', 'INNER');
    	$this->db->where(array('jps.jobId' => $job_id,'jps.archive'=>0));
    	$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
    	$data_docs = $query->result_array();
    	$return_ary = [];
    	if(!empty($data_docs)){
    		foreach ($data_docs as $value) {
    			if(isset($value['key_name']) && ($value['key_name'] == 'individual_interview' || $value['key_name'] == 'group_interview')){
    				$return_ary['interview_job_stage_id'] = $value['stage_id'];
    			}else if(isset($value['key_name']) && ($value['key_name'] == 'offer' || $value['key_name'] == 'cab_day')){
    				$return_ary['job_sub_stage_id'] = $value['stage_id'];
    			}
    		}
    	}
    	return $return_ary;
    }

    function create_recruitment_job_location($recruitment_job_location) {
    	$this->Basic_model->insert_records('recruitment_job_location', $recruitment_job_location);
    }

    function update_recruitment_job_location($recruitment_job_location, $where) {
    	$this->Basic_model->update_records('recruitment_job_location', $recruitment_job_location, $where);
    }

    /*
     * Get all jobs list
     */
    public function job_options($data) {
    	$application_id = $data->application_id;
    	$applicant_id = $data->applicant_id;
    	// get job position applied
    	$row = $this->Basic_model->get_result('recruitment_applicant_applied_application as raaa', array('applicant_id'=>$applicant_id), $columns = array('raaa.*'));
    	$position_current = [];
    	if (isset($row) == true && empty($row) ==false) {
    		foreach ($row as $key => $job) {
    			$position_current[] = $job->jobId;
    		}
    	}
    	$this->db->select(['title as label', 'id as value']);
    	$this->db->from('tbl_recruitment_job as rj');
    	$this->db->where(array('job_status'=> 3 , 'archive'=>0));
    	if (empty($position_current) == false) {
    		$this->db->where_not_in('id', $position_current);
    	}    	
    	$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
    	$result = $query->result_array();

    	return $result;
    }

    public function transfer_application($data, $adminId) {
    	$job = $data->selected_job;
    	$application_id = $data->application_id;
    	$job_id = '';

    	if (isset($job) == true && empty($job) == false) {
    		$job_id = $job->value;
    	}
    	// get job position applied
    	$row = $this->basic_model->get_row('recruitment_job', $columns = array('*'), array('id'=>$job_id));
    	$position = '';
    	if (isset($row) == true && empty($row) ==false) {
    		$position = $row->position;
    	}
    	
    	// get job position applied
    	$row = $this->basic_model->get_row('recruitment_applicant_applied_application', $columns = array('*'), array('id'=>$application_id));
    	$position_current = '';
    	if (isset($row) == true && empty($row) ==false) {
    		$position_current = $row->position_applied;
    	}
    	
    	// update application
    	if ($position != '') {
    		$tbl_question= TBL_PREFIX.'recruitment_applicant_applied_application';
			$this->db->where('id', $application_id);
			$this->db->update($tbl_question, array('position_applied' => $position, 'jobId' => $job_id, 'updated' => DATE_TIME));
			$updated_id = $this->db->affected_rows();
    	}
    	// job transfer
    	$new_history = $this->db->insert(
            TBL_PREFIX . 'application_history',
            [
                'application_id' => $application_id,
                'created_by' => $adminId,
                'created_at' => date('Y-m-d H:i:s')
            ]
        );
        $history_id = $this->db->insert_id();

    	if ($position != '') {
	        $field = 'job_transfer';
	        $new_value = $position;
	        $field_value = $position_current;
	        $this->load->model('recruitment/Recruitment_applicant_model');
	        $this->Recruitment_applicant_model->create_field_history($history_id, $application_id, $field, $new_value, $field_value);
	    }
    	return $position;    		
    }
	
	/** Get jobs details by job id
	 * 
	 * @param $jobId {int} job id
	 * @return {object}
	 */
	function get_jobs_by_id($jobId) {

		$this->db->select(['jrd.name as label', 'jrd.id as value']);
    	$this->db->from(TBL_PREFIX . 'recruitment_job as rj');
		$this->db->join(TBL_PREFIX . 'recruitment_job_category as jrd', "jrd.id = rj.sub_category", 'INNER');
    	$this->db->where('rj.id', $jobId);
		$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
    	return $query->row();
	}
}