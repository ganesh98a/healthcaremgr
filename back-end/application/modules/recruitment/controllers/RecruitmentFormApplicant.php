<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Job forms controller
 * 
 * @property-read \Recruitmentformapplicant_model $Recruitmentformapplicant_model
 */
class RecruitmentFormApplicant extends \MX_Controller
{
    use formCustomValidation;
    public function __construct()
    {
        parent::__construct();

        $this->load->library('Form_validation');
        $this->form_validation->CI =& $this;
		
		$this->load->library('UserName');
		 $this->loges->setLogType('recruitment');

        $this->load->helper(['array']);
        $this->load->model('Recruitmentform_model');
        $this->load->model('Recruitmentformapplicant_model');
        $this->load->model('Recruitment_applicant_model');
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # HCM- 3485, adding all requests to backend in a log file
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }

    /**
     * submitting the form. following format needs to be submitted as array
     *   application_id
     *   applicant_id
     *   form_id
     *   question_answers = arrray(
     *       0 => array(
     *           "question_id" => "3"
     *           "answer_id" => array("3","4","5")
     *           "answer_text => "text"
     *       )
     *   )
     */
    function submit_interview_form() {
        $request = request_handler('access_recruitment');
        $answer_data = json_decode(json_encode($request->data), true);

        if (empty($answer_data)) {
            $return = array('status' => false, 'error' => "Error submitting data");
            echo json_encode($return);
            exit;
        }

        # checking if the same form by applicant for same application submitted?
        $form_applicant_id = $this->Recruitmentformapplicant_model->applicant_form_submitted($answer_data);
        $inserted_id = null;

        $answer_data['question_answers_validate'] = (object) $answer_data;
        if($form_applicant_id)
        $answer_data['question_answers_validate']->form_applicant_id = $form_applicant_id;

        # doing server validations of submitted data 
        $validation_rules = array(
            array('field' => 'question_answers_validate[]', 'label' => 'Answers', 'rules' => 'callback_validate_submitted_answer'),
            array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required'),
            array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required'),
            array('field' => 'form_id', 'label' => 'Form Id', 'rules' => 'required')
        );

        $this->form_validation->set_data($answer_data);
        $this->form_validation->set_rules($validation_rules);

        # return back if validation fails
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $return = array('status' => false, 'error' => implode(', ', $errors));
            echo json_encode($return);
            exit;
        }

        # updating or inserting form applicant accordingly
        if($form_applicant_id)
            $this->Recruitmentformapplicant_model->update($form_applicant_id, $answer_data, $request->adminId);
        else
            $form_applicant_id = $this->Recruitmentformapplicant_model->create($answer_data, $request->adminId);

        # saving the questions and their answers submitted
        $answer_data["form_applicant_id"] = $form_applicant_id; 
        $this->Recruitmentformapplicant_model->save_interview_answer_of_applicant($answer_data);    

        # fetching applicant's info
        $applicant_info = $this->Recruitment_applicant_model->get_applicant_info($answer_data['applicant_id'], $request->adminId);
        # fetching job applicantion's info
        $application_info = null;#ToDo
        # fetching form information
        $form_info = null; #ToDo
        
        # adding a log entry
        $formname = "ToDo";
        $jobtitle = "ToDo";
        $log_title = "Form name: {$formname}, applicant: ".$applicant_info['fullname']."(".$applicant_info['appId']."), Job title: {$jobtitle}";
        
        if ($form_applicant_id)
            $log_title = "Edit, " . $log_title;
        else
            $log_title = "Add, " . $log_title;

        $this->loges->setTitle($log_title);
        $this->loges->setUserId($answer_data['applicant_id']);
        $this->loges->setDescription(json_encode($answer_data));
        $this->loges->setCreatedBy($request->adminId);
        $this->loges->createLog();
        $return = array('status' => true, 'msg' => "Applicant form successfully submitted");
        echo json_encode($return);
        exit;
    }
	
	// archive interview
	function archive_form_interview(){
		$request = request_handler('access_recruitment');
	
		if(!empty($request->data->interview_applicant_form_id)){
			$res = $this->basic_model->get_row("recruitment_form_applicant", ["applicant_id"], ["id" => $request->data->interview_applicant_form_id, "archive" => 0]);
			
			if(!empty($res)){
				$this->Recruitmentformapplicant_model->archive_form_interview($request->data->interview_applicant_form_id);
				
				
				$applicantName = $this->username->getName('applicant', $res->applicant_id);
                $admin = $this->username->getName('admin', $request->adminId);
				
				$log_title = $request->data->interview_applicant_form_id.' interview '. $applicantName . ' is archived by '.$admin;
				
				$this->loges->setTitle($log_title);
				$this->loges->setUserId($res->applicant_id);
				$this->loges->setDescription(json_encode($request->data));
				$this->loges->setCreatedBy($request->adminId);
				$this->loges->createLog();
				
				$return = array('status' => true, 'msg' => "Interview is successfully deleted");
			}else{
				$return = array('status' => false, 'msg' => "Interview form id not found");
			}
		
		}else{
			 $return = array('status' => false, 'error' => "Interview form_id is missing");
		}
      
        echo json_encode($return);
        exit;
	}
}