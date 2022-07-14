<?php

defined('BASEPATH') or exit('No direct script access allowed');

class RecruitmentAppliedForJob_model extends CI_Model
{

	public function __construct()
	{
		// Call the CI_Model constructor
		parent::__construct();

		$this->load->helper(['array_helper']);
		$this->load->model('sales/Feed_model');
	}

	public function check_live_job($jobId)
	{
		$sub_query1 = $this->job_description_subquery($jobId);

		$tbl_job = TBL_PREFIX . 'recruitment_job';

		#$this->db->where($tbl_job . ".job_status".' =', 3);
		$this->db->where($tbl_job . ".archive" . ' !=', 1);

		$select_column = array($tbl_job . ".id", $tbl_job . ".created", $tbl_job . ".job_status", $tbl_job . ".is_cat_publish", $tbl_job . ".is_subcat_publish", $tbl_job . ".is_emptype_publish", $tbl_job . ".is_salary_publish", $tbl_job . ".employment_type");

		$this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

		$this->db->select("case when tbl_recruitment_job.position!=0 THEN (SELECT jp.title FROM tbl_recruitment_job_position as jp where tbl_recruitment_job.position=jp.id AND jp.archive=0) ELSE '' END as position", false);

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

		$this->db->where($tbl_job . ".id =", $jobId);
		#$this->db->where($tbl_job . ".from_date <", DATE_CURRENT);
		#$this->db->where($tbl_job . ".to_date>=", DATE_CURRENT);

		$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
		$dataResult = $query->row();
		if (!empty($dataResult)) {
			$location_detail = $this->job_location($jobId);
			$docs_detail = $this->job_required_docs($jobId);
			if (!empty($location_detail)) {
				$dataResult->address = isset($location_detail->complete_address) ? $location_detail->complete_address : 'N/A';
				$dataResult->phone = isset($location_detail->phone) ? $location_detail->phone : 'N/A';
				$dataResult->email = isset($location_detail->email) ? $location_detail->email : 'N/A';
				$dataResult->website = isset($location_detail->website) ? $location_detail->website : 'N/A';
			}

			if (!empty($docs_detail)) {
				$dataResult->docs = $docs_detail;
			}
		}
		return $dataResult;
	}

	public function job_description_subquery($jobId)
	{
		$this->db->flush_cache();
		$this->db->select(['template.layout_text']);
		$this->db->from('tbl_recruitment_selected_job_template as template');
		$this->db->join('tbl_recruitment_job as job', 'job.id = template.jobId AND template.archive = 0', 'INNER');
		$this->db->where(array('job.id' => $jobId));
		return $this->db->get_compiled_select();
	}

	public function job_location($jobId)
	{
		$this->load->model("recruitment/Recruitment_jobs_model");
		return $this->Recruitment_jobs_model->job_location($jobId);
	}

	public function job_required_docs($jobId)
	{
		$this->db->select(['jpd.id', 'jpd.is_required', 'docs.title as title', 'docs.id as docs_p_id']);
		$this->db->from('tbl_recruitment_job_posted_docs as jpd');
		$this->db->join('tbl_document_type as docs', "docs.id = jpd.requirement_docs_id AND docs.archive = 0 ", 'INNER');
		$this->db->join('tbl_document_category as docs_cat', 'docs.doc_category_id = docs_cat.id AND docs_cat.archive = 0', 'inner');
		$this->db->where('docs_cat.key_name', 'apply_job');
		$this->db->where(array('jpd.jobId' => $jobId, 'jpd.archive' => 0));
		$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

		return $query->result();

	}

	public function create_applicant($reqData)
	{ 
		$this->load->library('EventDispatcher');
		$log_ary = [];
		$taskCreateApplicantData = [];
		$taskCreateForReview = false;
		$log_ary['request_body'] = $reqData;
		$job_id = $reqData['job_id'] ?? 0;
		$referred_by = $reqData['referred_by'] ?? NULL;
		$referred_phone = $reqData['referred_phone'] ?? NULL;
		$referred_email = $reqData['referred_email'] ?? NULL;

		$is_valid_job_id = $this->check_live_job($job_id);

		$channelId = 2;
		if (isset($reqData['source']) && $reqData['source'] == 'seek') {
			$channelId = 1;
		}

		if (!empty($is_valid_job_id)) {
			$referrer_url = $reqData['referrer_url'] ?? '';
			$bu_id = $reqData['bu_id']??'';
			$applicant_det = [
				'firstname' => $reqData['firstname'],
				'lastname' => $reqData['lastname'],
				'middlename'=>$reqData['middlename'],
				'previous_name'=>$reqData['previousname'],
				'bu_id'=> $bu_id,
				'dob'=>  date('Y-m-d', strtotime($reqData['dob'])) ,
				'status'=>1,
				'jobId'=>$job_id,
				'channelId'=>$channelId,
				'current_stage'=>1,
				'recruiter'=>0,
				'date_applide'=>DATE_TIME,
				'created'=>DATE_TIME
			];

			// to change the address
           $address_data = (object) ['address' => !empty($reqData['address'])  ? $reqData['address'] : '', 'unit_number' => $reqData['unit_number'], 'is_manual_address' => !empty($reqData['is_manual_address']) ?$reqData['is_manual_address'] : 0, 'manual_address' => $reqData['manual_address'] ?? NULL];
			
		   $applicantId = null;

			// reuse existing applicant if was found by querying by email
			// If found, we'll discard first name and lastname submitted by the user
			$applicant_data = $this->check_any_duplicate_applicant($reqData);
			if(!empty($applicant_data)){
				$applicantId = $applicant_data['id'];
				$address_data->person_id =  $applicant_data['person_id'];
			}

			// recruiter data will be used for creating applications
			// $recruiter_data = $this->find_recruiter_by_round_robin();
			$recruiter_data = $this->find_owner_by_job_id($job_id);
			if (!empty($recruiter_data)) {
				$recruiter_id = $recruiter_data['owner'];
				$applicant_det['recruiter'] = $recruiter_id;
				$log_ary['recruiter_id'] = $recruiter_id;
				$taskCreateForReview = true;
			}
			$new_applicant = false;
			$person_username = $reqData['email'];
			// $person_password = "123456"; #random_genrate_password(9);

			// insert applicant, also insert new person
			if (!$applicantId) {
				$new_applicant = true;

				// Capture these for the new 'Sales' module (as of 2020-05-05)
				// insert person, phones and emails into tbl_person, tbl_person_email, tbl_person_phone
				$person_id = $this->create_applicant_person($applicant_det,
					[
						[ 'email' => $reqData['email'], 'primary_email' => 1 ]
					],
					[
						[ 'phone' => $reqData['phone'], 'primary_phone' => 1 ]
					],
					$person_username
				);

				$applicant_det['person_id'] = $person_id;
				$address_data->person_id =  $person_id;
				$applicantId = $this->basic_model->insert_records('recruitment_applicant', $applicant_det);
				
				
			}

			if(!empty($applicantId)){
				$this->Recruitment_applicant_model->update_applicant_address($address_data, $applicantId);
			}
			

			// if(!empty($applicant_data))
			// {
			// 	$duplicate_ary = ['applicant_id' => $applicantId, 'status' => 1,'created'=>DATE_TIME];
			// 	$this->basic_model->insert_records('recruitment_applicant_duplicate_status', $duplicate_ary);
			// }

			$log_ary['applicantId'] = $applicantId;
			$log_ary['applicant_name'] = $reqData['firstname'];

			// if $applicant_data is empty, it means applicant does not exists initially
			if (empty($applicant_data)) {
				if(!empty($reqData['email'])){
					$mail_data = ['email' => $reqData['email'],'applicant_id'=>$applicantId,'created'=>DATE_TIME,'primary_email'=>1];
					$this->basic_model->insert_records('recruitment_applicant_email', $mail_data);
				}

				if (!empty($reqData['phone'])) {

					$ph_data = ['phone' => $reqData['phone'],'applicant_id'=>$applicantId,'created'=>DATE_TIME,'primary_phone'=>1];
					$this->basic_model->insert_records('recruitment_applicant_phone',$ph_data );
				}
			}

			// insert application even if applicant exist or not
			$job_row = $this->basic_model->get_row('recruitment_job', array('employment_type','position','type'), $where = array('id' => $job_id));
			$applicant_applied_data = ['applicant_id'=>$applicantId,'created'=>DATE_TIME,'channelId'=>$channelId,'status'=>1];

			if(!empty($job_row))
			{
				$applicant_applied_data['position_applied'] = !empty($job_row)?$job_row->position:0;
				# to make all the sql joins work, let's pass a value instead of 0
				# we don't require job type HCM-221
				$applicant_applied_data['recruitment_area'] = 1;
				$applicant_applied_data['employement_type'] = !empty($job_row) ? $job_row->employment_type : 0;
				$applicant_applied_data['referrer_url'] = $referrer_url;

				$applicant_applied_data['jobId'] = $job_id;
				$applicant_applied_data['bu_id'] = $bu_id;
				$applicant_applied_data['current_stage'] = 1; // Review answer stage (stage 1)
				$applicant_applied_data['channelId'] = $channelId; // Website (id: 2)

				$applicant_applied_data['referred_by'] = $referred_by; // referred by details
				$applicant_applied_data['referred_phone'] = $referred_phone;
				$applicant_applied_data['referred_email'] = $referred_email;
				$applicant_applied_data['campaign_source'] = $reqData['campaign_source'] ?? NULL;
				$applicant_applied_data['ad_name'] = $reqData['ad_name'] ?? NULL; 
				$applicant_applied_data['campaign_name'] = $reqData['campaign_name'] ?? NULL; 

				// set recruiter for application or use null if not given
				if (!empty($recruiter_data)) {
					$applicant_applied_data['recruiter'] = $recruiter_data['owner'];
				}
				//Flagged applicant
				if(!empty($applicant_data) && $applicant_data['flagged_status'] == 2) {
					$applicant_applied_data['application_process_status'] = 8;
				}
			}

			$application_id = $this->basic_model->insert_records('recruitment_applicant_applied_application',$applicant_applied_data );

			//Flagged applicant
			if(!empty($applicant_data) && $applicant_data['flagged_status'] == 2) {

				//Get the existing flagged data for generating feed
				$this->db->select(['flaged_approve_by', 'created']);
				$this->db->from('tbl_recruitment_flag_applicant');
				$this->db->where(['applicant_id' => $applicantId]);
				$this->db->order_by('id', 'desc');

				$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
				$flag_details =  $query->row();

				//Store feed values
				$feed_data = [];
				$feed_data['feed_title'] = 'Applicant and related applications are flagged';
				$feed_data['related_type'] = 4;
				$feed_data['source_id'] = $application_id;

				$this->Feed_model->save_feed($feed_data, $flag_details->flaged_approve_by,
					date('Y-m-d h:i:s', strtotime($flag_details->created)));
			}

			$log_ary['application_id'] = $application_id;
			$this->update_applicant_address($reqData, $applicantId);
			#$this->update_applicant_references($reqData, $applicantId);
			#$this->get_job_question_n_update($job_id,$applicantId);

			# setting up initial stages for job application
			$applicant_stage = array(
				['applicant_id' => $applicantId, 'application_id' => $application_id, 'stageId' => 1, 'status' => 2, 'created' => DATE_TIME],
				['applicant_id' => $applicantId, 'application_id' => $application_id, 'stageId' => 6, 'status' => 2, 'created' => DATE_TIME]
			);

			$this->basic_model->insert_records('recruitment_applicant_stage', $applicant_stage, true);

			// TODO: Does the attachment belongs to applicant or application?
			$this->save_docs($reqData,$applicantId, $application_id);

			// sending login details for newly created applicant to access applicant/member portal
			// if($new_applicant) {
			// 	$this->send_new_applicant_login_email([
			// 		'firstname' => $reqData['firstname'],
			// 		'lastname' => $reqData['lastname'],
			// 		'email' => $reqData['email'],
			// 		'userId' => $applicantId,
			// 		'function_content' => $this->config->item('member_webapp_url')."<br>Username/Email: ".$person_username."<br>Password:".$person_password
			// 	]);
			// }
			//Execute applicant created event
			$email_data = [
				'firstname' => $reqData['firstname'],
				'lastname' => $reqData['lastname'],
				'email' => $reqData['email'],
				'userId' => $applicantId,
				'function_content' => $this->config->item('member_webapp_url')."/"."<br>Username/Email: ".$person_username
			];
			$this->eventdispatcher->dispatch('onAfterApplicationCreated', $application_id, $email_data);
			$this->eventdispatcher->dispatch('onAfterApplicantCreated', $applicantId, $email_data);
			// sends application submitted email notification
			$this->send_application_submitted_email([
				'firstname' => $reqData['firstname'],
				'lastname' => $reqData['lastname'],
				'email' => $reqData['email'],
				'userId' => $applicantId,
				'application_id' => $application_id,
				// 'email_subject' => "My sample subject", // you may overrides email subject, atm set to 'Thank you for applying: ONCALL'
				// 'email_cc' => ['developer@yourdevelopmentteam.com.au'] // you may override carbon-copy, currently sets to empty array
			]);


			$this->create_log($log_ary);
			if ($channelId == 2 && !empty($referrer_url)) {
				$source =  getDomain($referrer_url);
			}
			else {
				$source = (isset($channelId) && $channelId == 1 ? 'Seek' : (($channelId == 2) ? 'Website' : (($channelId == 3) ? 'HCM Software' : 'N/A')));
			}

			// if ($taskCreateForReview) {
			// 	$this->createStageTask(['applicant_id' => $applicantId,
			// 		'recruiter_id' => $recruiter_id,
			// 		'task_stage' => 1,
			// 		'applicant_name' => $reqData['firstname'] . ' ' . $reqData['lastname'],
			// 		'training_location' => DEFAULT_RECRUITMENT_LOCATION_ID,
			// 		'application_id' => $application_id
			// 	]);
			// }
			
			return ['status' => true, 'application_id' => $application_id, 'applicant_id' => $applicantId, 'source' => $source];
		} else {
			return ['status' => false, 'error' => 'Either job is expired or closed.'];
		}
	}

	/**
	 * Find applications by `email` and `job_id`. Common use-case of this method is to reject applications if email and job ID
	 *
	 * @param mixed $email
	 * @param mixed $job_id
	 * @return array
	 */
	public function find_applications_by_email_and_job_id($email, $job_id)
	{
		$query = $this->db
			->from('tbl_recruitment_applicant_applied_application application')
			->join('tbl_recruitment_applicant applicant', 'applicant.id = application.applicant_id')
			->join('tbl_person_email person_email', 'person_email.person_id = applicant.person_id AND person_email.primary_email = 1')
			->where([
				'person_email.email' => $email,
				'application.jobId' => $job_id,
				'application.archive' => 0
			])
			->select([ 'application.*' ])
			->get();

		return $query->result_array();
	}

	/**
	 * Check if applicant exists by running query by `email` against `tbl_person_email`.
	 *
	 * @param array $reqData
	 * @return array
	 */
	public function check_any_duplicate_applicant($reqData)
	{
		// find applicant by fname, lname and email by using applicant and persons table for searching
		$foundApplicantByUsingPersonRelatedTables = $this->db
			->from('tbl_recruitment_applicant applicant')
			->join('tbl_person person', 'applicant.person_id = person.id', 'INNER')
			->join('tbl_person_email person_email', 'person_email.person_id = person.id AND person_email.primary_email = 1', 'INNER')
			// ->join('tbl_person_phone person_phone', 'person_phone.person_id = person.id AND person_phone.primary_phone = 1', 'INNER')
			->where([
				'person_email.email' => $reqData['email'],
				'person.archive' => 0,
				'applicant.archive' => 0,
				// 'phone' => $reqData['phone'],
			])
			->select([
				'applicant.*',
			])
			->get()
			->row_array();

		if ($foundApplicantByUsingPersonRelatedTables) {
			$get_address = $this->Recruitment_applicant_model->get_applicant_address($foundApplicantByUsingPersonRelatedTables['id']);
			$foundApplicantByUsingPersonRelatedTables['address'] = $get_address;
			return $foundApplicantByUsingPersonRelatedTables;
		}

		// fallback to the old way of searching duplicate applicant
		$profile_data = $phone_data = $email_data = [];
		if((isset($reqData['firstname']) && !empty($reqData['firstname'])) && (isset($reqData['lastname']) && !empty($reqData['lastname'])) && (isset($reqData['phone']) && !empty($reqData['phone']))){
			$this->db->select('ra.id as applicant_id');
			$this->db->from('tbl_recruitment_applicant as ra');
			$this->db->where(array('ra.firstname' => $reqData['firstname'], 'ra.lastname' => $reqData['lastname'], 'ra.archive' => 0, 'ra.duplicated_status' => 0));
			$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
			$profile_data =  $query->row();
			

			$phone_data = $email_data = [];
			if ($reqData['email']) {
				$this->db->select('rmail.applicant_id');
				$this->db->from('tbl_recruitment_applicant_email as rmail');
				$this->db->where(array('rmail.email' => $reqData['email'], 'rmail.archive' => 0));
				$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
				$email_data =  $query->row();
			}

			if ($reqData['phone']) {
				$this->db->select('rph.applicant_id');
				$this->db->from('tbl_recruitment_applicant_phone as rph');
				$this->db->where(array('rph.phone' => $reqData['phone'], 'rph.archive' => 0));
				$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
				$phone_data =  $query->row();
			}

			if(!empty($profile_data) && !empty($email_data) && !empty($phone_data))
			{
				$existing_applicant = $this->db->get_where('tbl_recruitment_applicant', ['id' => $email_data->applicant_id])->row_array();
				return $existing_applicant;
			}
		}	
	}


	function update_applicant_address($reqData, $applicantId)
	{
		$addres = !empty($reqData['address_component']) ? json_decode($reqData['address_component']) : [];

		if (!empty($addres)) {
			$adr_ary = obj_to_arr($addres);

			$address = $this->get_address_from_google_response($adr_ary);
			$insert_add = array(
				'street' => (isset($address['street_no']) ? $address['street_no']. ' ' : '').(isset($address['street']) ? $address['street'] : ''),
				'city' => isset($address['city']) ? $address['city'] : '',
				'state' => isset($address['state_id']) ? $address['state_id'] : '',
				'postal' => isset($address['postal_code']) ? $address['postal_code'] : '',
				'primary_address' => 1,
				'applicant_id' => $applicantId
			);

			if (!empty($insert_add)) {
				$this->load->model("recruitment/Recruitment_applicant_model");
            	$this->Recruitment_applicant_model->create_applicant_address($insert_add);
			}
		}
	}

	function update_applicant_references($reqData, $applicantId)
	{
		if (!empty($reqData['reference'])) {
			$insert_ref = [];
			foreach ($reqData['reference'] as $val) {
				$reference = ['name' => $val['name'], 'email' => $val['email'], 'phone' => $val['phone'], 'updated' => DATE_TIME, 'applicant_id' => $applicantId];
				$reference['created'] = DATE_TIME;
				$insert_ref[] = $reference;
			}
			if (!empty($insert_ref)) {
				$this->basic_model->insert_records('recruitment_applicant_reference', $insert_ref, true);
			}
		}
	}

	function checked_applicant_for_flagged($duplicate_applicant_id)
	{
		$this->db->select(['id']);
		$this->db->where(['applicant_id' => $duplicate_applicant_id, 'archive' => 0, 'flag_status' => 2]);
		$query = $this->db->get('tbl_recruitment_flag_applicant');
		$num = $query->num_rows();
		if ($num >= 5) {
			return ['status' => false, 'error' => 'You are not allowed to submit a job application anymore.', 'count' => $num];
		} else {
			return ['status' => true, 'count' => $num];
		}
	}

	function find_recruiter_by_round_robin($job_id = '')
	{
		$this->db->select(['m.id as recruiter_id', "(select count(raaa.recruiter) from tbl_recruitment_applicant_applied_application raaa where raaa.recruiter = m.id and raaa.archive = 0) as application_count"]);
		$this->db->from('tbl_member as m');
		$this->db->join('tbl_recruitment_staff as rs', 'rs.adminId = m.id and rs.archive=m.archive AND rs.approval_permission = 1 AND rs.status = 1 AND ((rs.round_robin_status = 1 AND rs.its_recruitment_admin = 1) OR (rs.round_robin_status = 0 AND rs.its_recruitment_admin = 0))', 'inner');
		$this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"', 'inner');

		$this->db->where(array('m.status' => 1, 'm.archive' => 0));
		$this->db->group_by('m.id');
		$this->db->order_by('application_count', 'ASC');
		$this->db->limit(1);
		$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
		$dataResult = $query->result();
		// last_query();exit;
		if (!empty($dataResult)) {
			$dataResult = $query->row_array();
			return $dataResult;
		} else {
			return [];
		}
	}

	function find_owner_by_job_id($job_id = '')
	{
		$this->db->select('rj.owner as owner');
		$this->db->from('tbl_recruitment_job as rj');

		$this->db->where("id",$job_id);
		$this->db->limit(1);
		$query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
		$dataResult = $query->result();
		// last_query();exit;
		if (!empty($dataResult)) {
			$dataResult = $query->row_array();
			return $dataResult;
		}else {
			return [];
		}
	}

	function get_address_from_google_response($response)
	{
		$name_arr = [];
		$state_id = '';
		$postal_code = '';
		$street = '';
		$street_no = '';
		$city = '';
		$state_name = '';

		if (!empty($response)) {
			if (!empty($response)) {
				foreach ($response as $addr) {
					$name_arr[] = $addr['short_name'];

					if (in_array('postal_code', $addr['types']))
						$postal_code = $addr['long_name'];

					if (in_array('street_number', $addr['types']))
						$street_no = $addr['long_name'];

					if (in_array('route', $addr['types']))
						$street = $addr['long_name'];

					if (in_array('locality', $addr['types']))
						$city = $addr['long_name'];

					if (in_array('administrative_area_level_1', $addr['types']))
						$state_name = $addr['short_name'];
				}
			} else {
				$postal_code = '';
				$street_no = '';
				$street = '';
				$city = '';
				$state_name = '';
			}
		}
		$state_row = $this->basic_model->get_row('state', array('id'), $where = array('name' => $state_name));
		$state_id = isset($state_row->id) ? $state_row->id : '';
		return ['state_id' => $state_id, 'city' => $city, 'street' => $street_no . ' ' . $street, 'postal_code' => $postal_code];
	}

	public function get_job_question_n_update($job_id, $applicantId)
	{

		$get_job_detail = $this->basic_model->get_row('recruitment_job_published_detail', $columns = array('id'), $id_array = array('jobId' => $job_id, 'archive' => 0, 'channel' => 2));
		$get_job_detail_id = !empty($get_job_detail) ? $get_job_detail->id : 0;

		$where = array('archive' => '0', 'jobId' => $job_id);
		if ($get_job_detail_id > 0) {
			$where['published_detail_id'] = $get_job_detail_id;
		}

		$responseQuestion = $this->basic_model->get_record_where('recruitment_job_question', $column = array('id'), $where);

		if (!empty($responseQuestion)) {
			foreach ($responseQuestion as $val) {
				$all_details[] = array('applicantId' => $applicantId, 'jobId' => $job_id, 'questionId' => $val->id, 'answer' => 'Dummy text', 'answer_status' => 1, 'created' => DATE_TIME);
			}

			if ($all_details) {
				$this->basic_model->insert_records('recruitment_applicant_seek_answer', $all_details, true);
			}
		}
	}

	public function create_log($log_ary)
	{
		// set log details
		$recruiter_id = $log_ary['recruiter_id'] ?? 0;
		if ($recruiter_id > 0) {
			$applicantId = $log_ary['applicantId'];
			$reqData = $log_ary['request_body'];
			require_once APPPATH . 'Classes/recruitment/ActionNotification.php';
			$this->load->library('UserName');
			$this->loges->setCreatedBy($recruiter_id);

			$applicantName = $this->username->getName('applicant', $applicantId);
			$adminName = $this->username->getName('admin', $recruiter_id);
			$this->loges->setTitle('Applicant Created - : ' . $applicantName);
			$this->loges->setSpecific_title('Applicant updated by ' . $adminName);
			$this->loges->setUserId($applicantId);
			$this->loges->setDescription(json_encode($reqData));
			$this->loges->createLog();
		}
	}

	/**
	 * sending an automatic email using template to newly created applicant
	 * it will contain the login details to access applicant/member portal
	 */
	public function send_new_applicant_login_email(array $data) {
		require_once APPPATH . 'Classes/Automatic_email.php';

		$obj = new Automatic_email();
		$obj->setEmail_key('new_applicant_login');
		$obj->setEmail($data['email']);
		$obj->setDynamic_data($data);
		$obj->setUserId($data['userId']);
		$obj->setUser_type(1);
		$obj->setSend_by($data['admin_id']);
		$obj->automatic_email_send_to_user();
	}

	/**
	 * Sends application submitted email
	 * @param string $email Email address
	 * @param array<string, mixed> $data Additional data. Must have at least the `first_name` key.
	 * You may supply `email_subject` and `email_cc`
	 */
	public function send_application_submitted_email(array $data)
	{
		/*$msg = $this->load->view('recruitment/application_submitted_email', $data, true);
		$subject = element('email_subject', $data, 'Thank you for applying: ONCALL');
		$cc = element('email_cc', $data, []);*/
		$this->load->model("recruitment/Recruitment_task_action");
		$data['job_title'] = $this->Recruitment_task_action->get_application_job_title($data['application_id']);

		require_once APPPATH . 'Classes/Automatic_email.php';

		$obj = new Automatic_email();
		$obj->setEmail_key('thank_apply_for_job');
		$obj->setEmail($data['email']);
		$obj->setDynamic_data($data);
		$obj->setUserId($data['userId']);
		$obj->setUser_type(1);

		$obj->automatic_email_send_to_user();

		/*try {
			send_mail($email, $subject, $msg, $cc);
		} catch (Exception $e) {
			log_message('error', "Unable to send application submitted email to '{$email}'. " .  $e->getMessage());
		}*/
	}

	public function save_docs($reqData, $applicantId, $application_id = null)
	{

		$ary = !empty($reqData['file_ary']) ? json_decode($reqData['file_ary']) : [];

		$directoryName = FCPATH . APPLICANT_ATTACHMENT_UPLOAD_PATH_JOB_APPLIED_FORM . $applicantId;

		//Create directory for move archieve files into applicant folder
		create_directory($directoryName);

		if (!empty($ary)) {

			$stage_row = $this->basic_model->get_row('recruitment_stage', array('id'), $where = array('stage_key' => 'document_checklist', '	archive' => 0));
			$stage = isset($stage_row->id) ? $stage_row->id : 6;

			/* Troublesome code that exists in development branch but not in staging/production */
			// $applicant_document_cat = [];
			require_once APPPATH . 'Classes/document/DocumentAttachment.php';

			//Include support files for aws upload
			require_once APPPATH . 'Classes/common/Aws_file_upload.php';
			$awsFileupload = new Aws_file_upload();
			$config = [];
			foreach ($ary as $name => $value) {
				/**
				 * @var string $value->selected_name If $name = 'Resume_1' then 'Resume' is the category, and 1 is the category ID
				 * @var stdClass $value->upload_data
				 * @var bool $value->status
				 */
				$upload_data = $value->upload_data;

				$temp = $value->selected_file_name ?? $name ?? '';
				$doc_category_temp = !empty($temp) ? explode('_', $temp) : '';
				$doc_category = !empty($doc_category_temp) ? end($doc_category_temp) : '';
				$attachment_title = !empty($temp) ? preg_replace('/_d+$/', '', $temp) : '';
				$attachment = $value->copied_file_name ?? $upload_data->file_name;

				if (file_exists(FCPATH . ARCHIEVE_DIR . '/' . $attachment)) {
					$if_copy = @rename(FCPATH . ARCHIEVE_DIR . '/' . $attachment, FCPATH . APPLICANT_ATTACHMENT_UPLOAD_PATH_JOB_APPLIED_FORM . $applicantId . '/' . $attachment);
					if ($if_copy) {
						@unlink(FCPATH . ARCHIEVE_DIR . '/' . $attachment);
					}
				}

				$config['file_name'] = $attachment;
                $config['upload_path'] = S3_APPLICANT_ATTACHMENT_UPLOAD_PATH;
                $config['directory_name'] = $applicantId;
                $config['allowed_types'] = DEFAULT_ATTACHMENT_UPLOAD_TYPE; //'jpg|jpeg|png|xlx|xls|doc|docx|pdf|pages';
				$config['max_size'] = DEFAULT_MAX_UPLOAD_SIZE;
				$config['uplod_folder'] = './uploads/';
				$config['adminId'] = $applicantId;
				$config['title'] = "Apply Job - ApplicanID- $applicantId ";
				$config['title'] .= ($application_id) ? "ApplicationID  $application_id" : NULL;
				$config['module_id'] = REQUIRMENT_MODULE_ID;
				$config['created_by'] = $applicantId ?? NULL;
				

				$s3documentAttachment = $awsFileupload->upload_from_app_to_s3($config, FALSE);

				if (!isset($s3documentAttachment) || !$s3documentAttachment['aws_uploaded_flag']) {
                    // return error comes in file uploading
                    echo json_encode(array('status' => false, 'error' => 'Document Attachment is not created. something went wrong'));
					$s3documentAttachment['file_path'] = $s3documentAttachment['file_type'] = $s3documentAttachment['file_ext'] = $s3documentAttachment['file_size'] = $s3documentAttachment['aws_response'] = $s3documentAttachment['aws_object_uri'] =
					$s3documentAttachment['aws_file_version_id'] = $s3documentAttachment['file_name'] = NULL;
					$s3documentAttachment['aws_uploaded_flag'] = 0;
				}

				/*
				 * Document Attachment
				 */
				$docAttachObj = new DocumentAttachment();

				$docAttachObj->setApplicationId($application_id);
				$docAttachObj->setApplicantId($applicantId);
				$docAttachObj->setUploadedByApplicant(1);
				$docAttachObj->setDocTypeId($doc_category);
				$docAttachObj->setArchive(0);
				$docAttachObj->setStage($stage);
				$docAttachObj->setIsMainStage(0);
				$docAttachObj->setCreatedAt(DATE_TIME);
				$docAttachObj->setCreatedBy($applicantId);
				$docAttachObj->setEntityId($applicantId);
				// Get constant staus
				$documentStaus = $docAttachObj->getConstant('DOCUMENT_STATUS_SUBMITTED');
				$docAttachObj->setDocumentStatus($documentStaus);
				// Get constant entity type
				$entityType = $docAttachObj->getConstant('ENTITY_TYPE_APPLICANT');
				$docAttachObj->setEntityType($entityType);
				// Get constant related to
				$relatedTo = $docAttachObj->getConstant('RELATED_TO_RECRUITMENT');
				$docAttachObj->setRelatedTo($relatedTo);
				// Get constant Created Portal
				$createdPortal = $docAttachObj->getConstant('CREATED_PORTAL_HCM');
				$docAttachObj->setCreatedPortal($createdPortal);

				$documentId = $docAttachObj->createDocumentAttachment();

				/*
				 * Create Document Attachment Property
				 */
				if ($documentId != '') {
					$docAttachPropertyObj = new DocumentAttachment();
					$docAttachPropertyObj->setDocId($documentId);
					$docAttachPropertyObj->setFilePath($s3documentAttachment['file_path']);
					$docAttachPropertyObj->setFileType($s3documentAttachment['file_type']);
					$docAttachPropertyObj->setFileSize($s3documentAttachment['file_size']);
					$docAttachPropertyObj->setAwsResponse($s3documentAttachment['aws_response']);
					$docAttachPropertyObj->setAwsObjectUri($s3documentAttachment['aws_object_uri']);
					$docAttachPropertyObj->setAwsFileVersionId($s3documentAttachment['aws_file_version_id']);
					$docAttachPropertyObj->setFileName($attachment);
					$docAttachPropertyObj->setRawName($attachment);
					$docAttachPropertyObj->setAwsUploadedFlag($s3documentAttachment['aws_uploaded_flag']);
					$docAttachPropertyObj->setArchive(0);
					$docAttachPropertyObj->setCreatedAt(DATE_TIME);
					$docAttachPropertyObj->setCreatedBy($applicantId);

					$docAttachPropertyObj->createDocumentAttachmentProperty();
				}

			}
			//Remove app server data if appserver upload flag disabled
			if(getenv('IS_APPSERVER_UPLOAD') != 'yes') {
				//Remove all files inside the applicant folder
				array_map('unlink', glob(FCPATH . APPLICANT_ATTACHMENT_UPLOAD_PATH_JOB_APPLIED_FORM . $applicantId."/*.*"));
				//Delete Applicant directory
				@rmdir(FCPATH . APPLICANT_ATTACHMENT_UPLOAD_PATH_JOB_APPLIED_FORM . $applicantId);

			}
		}
	}

	/**
	 * Saves applicant doc stage status. Doc stage is ID-ed as `6`
	 *
	 * You can update to any status or it updates to `2` (in progress) by default if 2nd param is not given
	 *
	 * @param int $applicantId
	 * @param int $status Default to in-progress (2)
	 * @return bool
	 */
	protected function update_applicant_doc_stage_status($applicantId, $status = 2)
	{
		// The `Recruitment_applicant_model::update_applicant_stage_status()` is NOT a reliable way to
		// update applicant stage statuses because it will almost reject the applicant when that method is called
		// So let's upsert (update or insert) instead

		$STAGE_DOC_STAGE = 6;
		$STATUS_IN_PROGRESS = 2;

		$stageId = $STAGE_DOC_STAGE;
		$status = $STATUS_IN_PROGRESS;
		$adminId = 0; // @todo. How can I get this value?

		$where = [
			'applicant_id' => $applicantId,
			'stageId' => $stageId,
		];

		$query = $this->db->get_where('tbl_recruitment_applicant_stage', $where);

		$success = false;
		if ($query->num_rows() === 0) {
			$success = $this->db->insert('tbl_recruitment_applicant_stage', [
				'applicant_id' => $applicantId,
				'stageId' => $stageId,
				'action_by' => $adminId,
				'status' => $status,
				'created' => DATE_TIME,
				// action_at => DATE_TIME, // @todo: What's action_at?
			]);
		} else {
			$success = $this->db->update('tbl_recruitment_applicant_stage', [
				'status' => $status,
			], $where);
		}

		return $success;
	}

	/**
	 *
	 * @param array[] $_files
	 * @param string $upload_path
	 * @return array<string, array>
	 */
	public function save_multiple_attachments($job_id, $_files, $seek_resume_active, $seek_resume_name)
	{
		// selected_file_name: myObj.data.selected_name,
		// copied_file_name:myObj.data.upload_data.file_name
		$required_doc_ids = $this->determine_required_doc_ids($job_id);


		$uploaded_files = [];

		foreach ($_files as $name => $_file) {
			$_file_name = $_file['name'] ?? '';
			$file_size = $_file['size'] ?? 0;
			
			$doc_category_temp = !empty($name) ? explode('_', $name) : '';
			$doc_category = !empty($doc_category_temp) ? end($doc_category_temp) : '';
			$is_required = in_array($doc_category, $required_doc_ids);
			// if seek_resume_active == 1 & seek_resume_name == Resume_{dynamic_id} skip to validate and upload
			if($seek_resume_active == 1 && $name == $seek_resume_name){
				continue;
			}
			// if empty, check if required, and if so, exit early and throw error
			if (empty($_file_name)) {
				if ($is_required) {
					return [
						'status' => false,
						'error' => 'Required file was not uploaded',
						'selected_name' => $name,
					];
				}

				continue;
			}

			$config['upload_path'] = FCPATH . ARCHIEVE_DIR;
			$config['directory_name'] = '';
			$config['max_size'] = '100000';
			$config['input_name'] = $name;
			$config['allowed_types'] = DEFAULT_ATTACHMENT_UPLOAD_TYPE;

			$this->load->library('upload', $config);

			if (!$this->upload->do_upload($config['input_name'])) {

				// server address
				$ip_server = $_SERVER['SERVER_ADDR'];
				
				# error log
				$responseDate = [];
				$responseDate['job_id'] = $job_id;
				$responseDate['file'] = $_files[$name] ?? '';
				$error_de = $this->upload->display_errors();
				$error_de = preg_replace("/<p[^>]*?>/", "", $error_de);
				$error_de = str_replace("</p>", "", $error_de);

				if ($file_size == 0) {
					$error_de = "There is an issue specific to uploading file from iOS devices which we are working to resolve. In the meantime, as a workaround, please refresh the screen, retry and upload will be successful. Our apologies for the inconvenience caused.";
				} else {
					$error_de = strip_tags($this->upload->display_errors());
				}

				$error = [];
				$error['status'] = 400;
				$error['message'] = $error_de;
				$error['payload'] = [ 'request_data' => $responseDate ];
				$error['exception'] = 'apply_job_upload_error';
				$error['operation'] = base_url('recruitment/RecruitmentAppliedForJob/create_applicant');
				$error['serverIp'] = $ip_server;
				$error['module'] = 'Apply Job';
				$error['version'] = false;
				$error['timeTaken'] = '';
				$error['userId'] = '';
				$error['timestamp'] = DATE_TIME;
				
				log_msg($error_de, 400, $error['payload'], $error['exception'], $error['operation'], $error['module'], '', '');

				return [
					'status' => false,
					'error' => $error_de,
					'selected_name' => $name,
				];
			} else {
				$upload_data = $this->upload->data();

				$uploaded_files[$name] = [
					'status' => true,
					'upload_data' => $upload_data,
					'selected_name' => $name,

					// these 2 are needed by `$this->save_docs`
					'selected_file_name' => $name,
					'copied_file_name' => $upload_data['file_name'],
				];
			}
		}

		return [
			'status' => true,
			'uploaded_files' => $uploaded_files,
		];
	}

	/**
     *
     * @param mixed $job_id
     */
    protected function determine_required_doc_ids($job_id)
    {
        $job_posted_docs = $this->job_required_docs($job_id);
        $job_posted_docs = obj_to_arr($job_posted_docs);

        $required_doc_ids = [];
        foreach ($job_posted_docs as $i => $job_posted_doc) {
            if ($job_posted_doc['is_required']) {
                $required_doc_ids[] = $job_posted_doc['docs_p_id'];
            }
        }

        return $required_doc_ids;
    }




	public function find_all_persons_by_name_and_primary_email(array $details)
	{
		$firstname = $details['firstname'];
		$lastname = $details['lastname'];
		$primary_email = $details['email'];

		$q = $this->db
			->from('tbl_person p')
			->join('tbl_person_email e', 'e.person_id = p.id AND e.primary_email = 1', 'INNER')
			->where([
				'p.firstname' => $firstname,
				'p.lastname' => $lastname,
				'e.email' => $primary_email
			])
			->select([
				'p.*',
				'e.email as email'
			])
			->get();

		$results = $q->result_array();
		return $results;
	}


	/**
	 * Inserts personal details (eg firstname, lastname, emails and phones) into
	 *
	 * `tbl_person`,
	 * `tbl_person_email`
	 * `tbl_person_phone`
	 *
	 * @return int ID of inserted row in `tbl_person`
	 */
	public function create_applicant_person(array $applicant_det, array $emails = [], array $phones = [],$person_username)
	{
		$STATUS_ACTIVE = 1;
		$TYPE_APPLICANT = 1;

		$personDataToBeInserted = [
			'firstname' => $applicant_det['firstname'],
			'middlename'=>$applicant_det['middlename'],
			'previous_name'=>$applicant_det['previous_name'],
			'lastname' => $applicant_det['lastname'],
			'date_of_birth' => date('Y-m-d', strtotime($applicant_det['dob'])),
			'status' => $STATUS_ACTIVE,
			'type' => $TYPE_APPLICANT,
			'username' => $person_username,
			'bu_id' => $applicant_det['bu_id']??'',
			// 'password' => password_hash($person_password, PASSWORD_BCRYPT)
		];
		$this->db->insert('tbl_person', $personDataToBeInserted);
		$person_id = $this->db->insert_id();


		// insert emails
		$emailsToBeInserted = [];
		foreach ($emails as $email) {
			$emailsToBeInserted[] = [
				'email' => $email['email'],
				'person_id' => $person_id,
				'primary_email' => $email['primary_email'],
			];
		}
		if (!empty($emailsToBeInserted)) {
			$this->db->insert_batch('tbl_person_email', $emailsToBeInserted);
		}

		// phones
		$phonesToBeInserted = [];
		foreach ($phones as $phone) {
			$phonesToBeInserted[] = [
				'phone' => $phone['phone'],
				'person_id' => $person_id,
				'primary_phone' => $phone['primary_phone'],
			];
		}
		if (!empty($phonesToBeInserted)) {
			$this->db->insert_batch('tbl_person_phone', $phonesToBeInserted);
		}

		return $person_id;
	}

	/**
	 * Determine if the type of a job document is mandatory or optional
	 *
	 * @param int $job_id
	 * @param int $doctype_id
	 * @param int $applicant_id
	 * @return bool
	 */
	protected function is_job_doc_required($job_id, $doctype_id, $applicant_id)
	{
		$q = $this->db->get_where('tbl_recruitment_job_posted_docs', [
			'jobId' => $job_id,
			'requirement_docs_id' => $doctype_id,
		]);

		$row = $q->row_array();

		if ($row) {
			return $row['is_required'] == 1;
		}

		return false;
	}

	public function createStageTask($taskDetails=[]){
		$applicantId = $taskDetails['applicant_id'] ?? 0;
		$applicationId = $taskDetails['application_id'] ?? 0;
		$applicantName = $taskDetails['applicant_name'] ?? '';
		$taskStage = $taskDetails['task_stage'] ?? 1;
		$recruiterId = $taskDetails['recruiter_id'] ?? 0;
		$trainingLocation = $taskDetails['training_location'] ?? DEFAULT_RECRUITMENT_LOCATION_ID;
		$taskDate = $taskDetails['task_date'] ?? DATE_TIME;
		$taskStartTime = $taskDetails['task_start_time'] ?? DATE_TIME;
		$taskEndTime = $taskDetails['task_end_time'] ?? date(DB_DATE_TIME_FORMAT, strtotime(date(DB_DATE_FORMAT) . ' 23:59:59'));
		$createdBy = $taskDetails['created_by'] ?? $recruiterId;
		if (!empty($applicantId) && !empty($recruiterId) && !empty($applicationId)) {
			$dataReq = array(
				'applicant_list' => [
					[
						'label' => $applicantName,
						'value' => $applicantId,
						'applicant_id' => $applicantId,
						'application_id' => $applicationId,
					]
				],

				'assigned_user' => [
					[
						'is_recruitment_user' => '1',
						'value' => $recruiterId,
						'primary_recruiter' => '1',
						'assigned_user' => false,
					]
				],
				'max_applicant' => 10,
				'presurb_primary' => [],
				'task_stage' => $taskStage,
				'form_option' => [],
				'task_name' => 'Review online application for applicant ' . $applicantName . ' application id '.$applicationId,
				'task_date' => $taskDate,
				'start_time' => $taskStartTime,
				'end_time' => $taskEndTime,
				'training_location' => $trainingLocation,
				'relevant_task_note' => 'Review online application assign to recruiter',
				'form_id' => 0,
				'application_id' => $applicationId
			);
			$this->load->model('Recruitment_task_action');
			$taskDataReq = json_decode(json_encode($dataReq), FALSE);
			$this->Recruitment_task_action->create_task($taskDataReq, $createdBy);
		}
	}
	/*
	 * Determine seek attachment resume detail
	 * param {array|null} $resumeDetail
	 */
	public function get_resume_details($resumeDetail){
		$file_full_path = FCPATH . $resumeDetail['full_path'];
		$name = $resumeDetail['name'];
        $getDocumentInfo = pathinfo($file_full_path);
        $raw_name = isset($getDocumentInfo) && isset($getDocumentInfo['filename']) ? $getDocumentInfo['filename'] : '';
        $file_extension = isset($getDocumentInfo) && isset($getDocumentInfo['extension']) ? $getDocumentInfo['extension'] : '';
        $mime_type = mime_content_type($file_full_path);
		// set resume data
        $tempResumeData['file_name'] = $resumeDetail['file_name'];
        $tempResumeData['file_type'] = $mime_type;
        $tempResumeData['file_path'] = FCPATH . $resumeDetail['temp_path'];
        $tempResumeData['full_path'] = $file_full_path;
        $tempResumeData['raw_name'] = $raw_name;
        $tempResumeData['orig_name'] = $resumeDetail['full_path'];
        $tempResumeData['client_name'] = $resumeDetail['full_path'];
        $tempResumeData['file_ext'] = '.'.$file_extension;
        $tempResumeData['file_size'] = filesize($file_full_path);
        // if image
        $file_full_path = realpath($file_full_path);
        $size = getimagesize($file_full_path);
        if (@is_array($size)) {
        	$tempResumeData['is_image'] = 1;
	        $tempResumeData['image_width'] = $size[0];
	        $tempResumeData['image_height'] = $size[1];
	        $tempResumeData['image_type'] = $size['mime'];
	        $tempResumeData['image_size_str'] = '';
        } else {
        	$tempResumeData['is_image'] = '';
	        $tempResumeData['image_width'] = '';
	        $tempResumeData['image_height'] = '';
	        $tempResumeData['image_type'] = '';
	        $tempResumeData['image_size_str'] = '';
        }
        $upload_data = ($tempResumeData);
        $uploaded_files = [
			'status' => true,
			'upload_data' => $upload_data,
			'selected_name' => $name,

			// these 2 are needed by `$this->save_docs`
			'selected_file_name' => $name,
			'copied_file_name' => $resumeDetail['file_name'],
		];
        return $uploaded_files;
	}
	
}
