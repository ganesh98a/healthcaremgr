<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Job forms controller
 * 
 * @property-read \Recruitmentform_model $Recruitmentform_model
 */
class RecruitmentForm extends \MX_Controller {

    public function __construct() {
        parent::__construct();

        // tell form validation library to point to this 
        // subclass of MX_Controller when using callback validations
        // @see https://bitbucket.org/wiredesignz/codeigniter-modular-extensions-hmvc/src/codeigniter-3.x/
        $this->load->library('Form_validation');
        $this->form_validation->CI = & $this;
        $this->load->library('UserName');
        $this->load->helper(['array']);
        $this->load->model('Recruitmentform_model');
        $this->load->model('Recruitment_form_applicant_history_model');
        $this->load->model('sales/Feed_model');
        $this->load->model('Online_assessment_model');
        $this->loges->setLogType('recruitment_forms');
        $this->load->helper('i_pad');
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
     * Find all interview forms created by current user
     * 
     * `POST: /recruitment/RecruitmentForm/index`
     */
    public function index() {
        $reqData = request_handler('access_recruitment');
        $request = json_decode(json_encode($reqData->data), true);
        $userId = (int) $reqData->adminId;
        $response = $this->Recruitmentform_model->find_all_forms($request, $userId);
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }

    /**
     * Action to create new forms
     * 
     * @return CI_Output 
     */
    public function create() {
        $request = request_handler('access_recruitment');
        $data = json_decode(json_encode($request->data), true);

        $interviewTypes = $this->find_all_interview_type_categories();
        $interviewTypesIds = array_column($interviewTypes, 'id');

        $this->form_validation->set_data($data)->set_rules([
            [
                'field' => 'title',
                'label' => 'title',
                'rules' => [
                    'required',
                    'max_length[255]', // mysql says length is 255
                    "callback_is_unique_form[" . implode(',', [$request->adminId, element('category', $data, 0), 0]) . "]",
                ]
            ],
            [
                'field' => 'category',
                'label' => 'interview category',
                'rules' => [
                    'required',
                    'trim', // in_list doesn't like validating against integers, so convert to string first
                    'in_list[' . implode(',', $interviewTypesIds) . ']',
                ],
                'errors' => [
                    'in_list' => 'The selected category does not exists in our system',
                ]
            ],
        ]);

        if (!$this->form_validation->run()) {
            $validationErrors = $this->form_validation->error_array();
            return $this->output->set_content_type('json')->set_output(json_encode([
                        'status' => false, 'error' => implode(', ', $validationErrors)
            ]));
        }

        $results = $this->Recruitmentform_model->create($data, (int) $request->adminId);
        $this->loges->setTitle('New form is created - : ' . $results['data']['insert_id']);
        $this->loges->setUserId((int) $request->adminId);
        $this->loges->setCreatedBy((int) $request->adminId);
        $this->loges->setDescription(json_encode($request));
        $this->loges->createLog();
        return $this->output->set_content_type('json')->set_output(json_encode($results));
    }

    /**
     * Update form
     * 
     * `POST: /recruitment/RecruitmentForm/update/5`
     */
    public function update($id) {
        $request = request_handler('access_recruitment');
        $data = json_decode(json_encode($request->data), true);

        // dont update if it doesn't exist anymore
        $existingForm = $this->db->get_where('tbl_recruitment_form', [
            'id' => $id,
            'archive' => 0,
        ]);

        if ($existingForm->num_rows() === 0) {
            return show_404("Form with ID of {$id} does not exist anymore");
        }

        // validation
        // both insert and update have same validation rules
        $interviewTypes = $this->find_all_interview_type_categories();
        $interviewTypesIds = array_column($interviewTypes, 'id');

        $this->form_validation->set_data($data)->set_rules([
            [
                'field' => 'title',
                'label' => 'title',
                'rules' => [
                    'required',
                    'max_length[255]', // mysql says length is 255
                    "callback_is_unique_form[" . implode(',', [$request->adminId, element('category', $data, 0), (int) $id]) . "]",
                ]
            ],
            [
                'field' => 'category',
                'label' => 'interview category',
                'rules' => [
                    'required',
                    'trim', // in_list doesn't like validating against integers, so convert to string first
                    'in_list[' . implode(',', $interviewTypesIds) . ']',
                ],
                'errors' => [
                    'in_list' => 'The selected category does not exists in our system',
                ]
            ],
        ]);

        if (!$this->form_validation->run()) {
            $validationErrors = $this->form_validation->error_array();
            return $this->output->set_content_type('json')->set_output(json_encode([
                        'status' => false, 'error' => implode(', ', $validationErrors)
            ]));
        }

        // update if valid
        $response = $this->Recruitmentform_model->update((int) $id, $data);
        $this->loges->setTitle('Form is updated - : ' . $id);
        $this->loges->setUserId((int) $request->adminId);
        $this->loges->setCreatedBy((int) $request->adminId);
        $this->loges->setDescription(json_encode($request));
        $this->loges->createLog();
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }

    /**
     * Fetch all interview types
     * 
     * @return CI_Output 
     */
    public function interview_types() {
        request_handler('access_recruitment');
        $results = $this->find_all_interview_type_categories();
        return $this->output->set_content_type('json')->set_output(json_encode($results));
    }

    /**
     * Helper method to find all interview type category ids
     * 
     * @return array[]
     */
    protected function find_all_interview_type_categories() {
        return $this->db
                        ->get_where('tbl_recruitment_interview_type')
                        ->result_array();
    }

    /**
     * Check if given title, created_by and category already in database
     * @param string $title
     * @param string $created_by_and_category created_by and category id in a comma separated string
     */
    public function is_unique_form($title, $created_by_and_category) {
        list($created_by, $category, $id) = explode(',', $created_by_and_category);

        $query = $this->db->get_where('tbl_recruitment_form', [
            'title' => trim($title),
            'created_by' => (int) $created_by,
            'interview_type' => $category,
            'id !=' => $id,
        ]);

        if ($query->num_rows() === 0) {
            return true;
        }

        $this->form_validation->set_message('is_unique_form', 'Form with the same title and category already exists');
        return false;
    }

    /**
     * post parameters: interview_type of the form
     * result: array of all the forms data
     */
    public function get_question_form_option() {
        $request = request_handler('access_recruitment');
        $data = json_decode(json_encode($request->data), true);

        if (isset($data['interview_type']) && $data['interview_type']) {

            $res = $this->Recruitmentform_model->get_question_form_option($data);
            echo json_encode(["status" => true, "data" => $res]);
        } else {
            echo json_encode(['status' => false, 'data' => 'interview_type not supplied']);
        }
    }

    /**
     * post parameters: form-id specific to a category
     * result: list of questions added inside that form
     */
    function get_question_list_and_details() {
        $reqData = request_handler('access_recruitment');

        if ($reqData->data) {
            $this->form_validation->set_data((array) $reqData->data);

            $validation_rules = array(
                array('field' => 'applicantId', 'label' => 'applicant id', 'rules' => 'required'),
                array('field' => 'form_id', 'label' => 'form id', 'rules' => 'required'),
                array('field' => 'interview_type', 'label' => 'interview type', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'application id', 'rules' => 'required'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $shouldQueryAgainstApplicationID = !!$reqData->data->application_id;
                $res = [];

                # fetching the details of current form selection, if it is submitted by the applicant for his application
                $res = !$shouldQueryAgainstApplicationID ? [] : $this->Recruitmentform_model->get_applicant_job_regarding_details($reqData->data);
                if (empty($res["id"])) {
                    $res["completed_by"] = $this->username->getName('admin', $reqData->adminId);
                }

                $res['questionList'] = $this->Recruitmentform_model->get_form_questions_list((array) $reqData->data, $res);

                if ($reqData->data->interview_type === "reference_check" && $shouldQueryAgainstApplicationID) {
                    $res["reference_details"] = $this->basic_model->get_row("recruitment_applicant_reference", ["id", "name", "email", "phone"], ["id" => $reqData->data->reference_id]);
                }
                $response = ["status" => true, "data" => $res];
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    /*
     * post parameters: form-id 
     * result: status true or false 
     */
    public function archive_form() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data->formId)) {
            $formId = $reqData->data->formId;
            // check alreay active form anywgere
            $res = $this->Recruitmentform_model->check_form_already_active_in_interview($formId);

            if (!empty($res["status"])) {
                
                $this->Recruitmentform_model->archive_form($formId);
                $this->Recruitmentform_model->check_form_already_active_in_interview($formId);
                $return = ["status" => true, "msg" => "Form is archived successfully"];
            } else {
                $return = $res;
            }
        } else {
            $return = ["status" => false, "error" => "form id is missing"];
        }
        
        echo json_encode($return);
    }

    /**
     * post parameters: search of the form
     * result: array of all the forms data
     */
    public function get_question_form_template() {
        $request = request_handler('access_recruitment');
        $data = json_decode(json_encode($request->data), true);
        $result = $this->Recruitmentform_model->get_question_form_template($data);
        echo json_encode($result);
    }

    /**
     * post parameters: interview_type of the form
     * result: array of all the forms data
     */
    public function get_all_applications() {
        $request = request_handler('access_recruitment');
        $data = json_decode(json_encode($request->data), true);
        $result = $this->Recruitmentform_model->get_all_applications($data);
        echo json_encode($result);
    }

    public function create_form() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        if (!empty($reqData)) {
            $data = (array) $reqData->data;

            # appending timings into date fields
            $data['form_start_datetime'] = '';
            $data['form_end_datetime'] = '';

            if ($data['form_start_date'] != '') {
                $data['form_start_datetime'] = $data['form_start_date']." ".$data['form_start_time'];
            }
            if ($data['form_end_date'] != '') {
                $data['form_end_datetime'] = $data['form_end_date']." ".$data['form_end_time']; 
            }

            // Validation rules set
            $validation_rules = [
                array('field' => 'title', 'label' => 'Title', 'rules' => 'required'),
                array('field' => 'form_id', 'label' => 'Form Template', 'rules' => 'required', "errors" => [ "required" => "Form Template is required" ]),
                array('field' => 'related_to', 'label' => 'Related To', 'rules' => 'required', "errors" => [ "required" => "Related to application is required" ]),
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required', "errors" => [ "required" => "Missing Application Id" ]),
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', "errors" => [ "required" => "Missing Applicant Id" ]),
            ];

            # date time msg validation
            if ($data['form_start_date'] != '' && $data['form_start_time'] == '') {
                $validation_rules[] = array(
                    'field' => 'form_start_time', 'label' => 'Form start time', 'rules' => 'required'
                );
            }

            if ($data['form_end_date'] != '' && $data['form_end_time'] == '') {
                $validation_rules[] = array(
                    'field' => 'form_end_time', 'label' => 'Form end time', 'rules' => 'required'
                );
            }

            if ($data['form_start_datetime'] != '' && $data['form_start_date'] != '' && $data['form_start_time'] != '') {
                $validation_rules[] = array(
                    'field' => 'form_start_datetime', 'label' => 'Form start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect form start date & time',
                    ]
                );
            }

            if ($data['form_end_datetime'] != '' && $data['form_start_datetime'] == '' ) {
                $validation_rules[] = array(
                    'field' => 'form_start_datetime', 'label' => 'Form start date & time', 'rules' => 'required'
                );
            }

            if ($data['form_start_datetime'] != '' && $data['form_end_datetime'] == '' ) {
                $validation_rules[] = array(
                    'field' => 'form_end_datetime', 'label' => 'Form end date & time', 'rules' => 'required'
                );
            }

            if ($data['form_end_datetime'] != '' && $data['form_end_date'] != '' && $data['form_end_time'] != '') {
                $validation_rules[] = array(
                    'field' => 'form_end_datetime', 'label' => 'Form end date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect form end date & time',
                    ]
                );
            }
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                $this->load->model('Basic_model');

                // Call create form model
                $form = $this->Recruitmentform_model->save_form($data, $adminId);
                // According to that form will be created
                if ($form['status'] == true) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    /**
                     * Create logs. it will represent the user action they have made.
                     */
                    $this->loges->setTitle("New form created for " . $data['title'] ." by " . $adminName);  // Set title in log
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    // Create log
                    $this->loges->createLog(); 
                    $data = array('form_id' => $form['form_id']);
                    $response = ['status' => true, 'msg' => 'Form has been created successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $form['error']];
                }
            } else {
                // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => 'Requested data is null'];
        }

        echo json_encode($response);
        exit();
    }

    /*
     * For getting form list
     * 
     * Return type json
     * - count 
     * - data
     * - status
     */
    function get_form_list_by_id() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required', "errors" => [ "required" => "Missing Application Id" ]),
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', "errors" => [ "required" => "Missing Applicant Id" ]),
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get form list
                $result = $this->Recruitmentform_model->get_form_list_by_id($reqData->data,$reqData);
            } else {
               // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }      
        echo json_encode($result);
        exit();  
    }
    
    public function get_oa_list_by_id(){
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $validation_rules = [
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required', "errors" => [ "required" => "Missing Application Id" ]),
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', "errors" => [ "required" => "Missing Applicant Id" ]),
            ];

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $result = $this->Recruitmentform_model->get_oa_list_by_id($reqData->data,$reqData);
            } else {
                $errors = $this->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            $result = ['status' => false, 'error' => "Requested data is null"];
        }      
        echo json_encode($result);
        exit(); 
    }

    /**
     * Get form details by form id
     */
    public function get_form_detail_by_id() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'form_id', 'label' => 'Form Id', 'rules' => 'required', "errors" => [ "required" => "Missing Form Id" ]),
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get form list
                $result = $this->Recruitmentform_model->get_form_detail_by_id($reqData->data);
            } else {
               // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }      
        echo json_encode($result);
        exit(); 
    }

    /**
     * Update Applicant form status
     */
    public function update_form_status() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'form_id', 'label' => 'Form Id', 'rules' => 'required', "errors" => [ "required" => "Missing Form Id" ]),
                array('field' => 'status', 'label' => 'Status', 'rules' => 'required', "errors" => [ "required" => "Missing Status" ]),
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get form list
                $result = $this->Recruitmentform_model->update_form_status($reqData->data, $adminId);
            } else {
               // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($result);
        exit(); 
    }
	
	public function get_questions_list_in_pdf(){
		$reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
			$applicantInfo = $this->Recruitmentform_model->get_applicant_info($data);

            $questionAppParam = new stdClass();
            $questionAppParam->form_id = $data['form_id'];
            $questionAppParam->form_applicant_id = $data['form_applicant_id'];
            $questionAppParam->pageSize = 90000;
            $questionAppParam->page     = 0;
            $questionAppParam->sorted   = array();
            $questionAppParam->filtered = array();
            
            //$questionList = $this->Recruitmentform_model->get_question_list_for_pdf($data);
            $questionListArray = $this->Recruitmentform_model->get_questions_list_by_applicant_form_id($questionAppParam);
            $questionList = $questionListArray['data']; 

			$result = $this->Recruitmentform_model->generate_download_form_pdf($data, $questionList, $applicantInfo);

        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
		echo json_encode($result);
        exit();
		
	}

    /*
     * To get form question list
     * 
     * Return type json
     * - count 
     * - data
     * - status
     */
    function get_questions_list_by_applicant_form_id() {
        $reqData = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'form_applicant_id', 'label' => 'Form Applicant Id', 'rules' => 'required', "errors" => [ "required" => "Missing Form Id" ])
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get form list
                $result = $this->Recruitmentform_model->get_questions_list_by_applicant_form_id($reqData->data);
            } else {
               // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }      
        echo json_encode($result);
        exit();  
    }

	public function get_oa_list_in_pdf(){
		$reqData = request_handler();
        $adminId = (integer) $reqData->adminId ?? 1;
        
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $assesmentData = array();
            $assesmentData['job_assessment_id'] = $data['oa_id'];
            $assesmentData['application_id'] = 3414;
		    
            $response = $this->Online_assessment_model->print_online_assessment((object) $assesmentData, $adminId);
            return $this->output->set_content_type('json')->set_output(json_encode($response));

        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
		echo json_encode($result);
        exit();
		
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
        $form_applicant_id = $answer_data['interview_applicant_form_id'];
        $inserted_id = null;

        $answer_data['question_answers_validate'] = (object) $answer_data;
        if($form_applicant_id)
        $answer_data['question_answers_validate']->form_applicant_id = $form_applicant_id;

        # doing server validations of submitted data 
        $validation_rules = array(
            array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required'),
            array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required'),
            array('field' => 'form_id', 'label' => 'Form Id', 'rules' => 'required')
        );

                
        $this->form_validation->set_data((array)$answer_data);
        $this->form_validation->set_rules($validation_rules);

        # return back if validation fails
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $return = array('status' => false, 'error' => implode(', ', $errors));
            echo json_encode($return);
            exit;
        }
        $this->load->model('Recruitment_applicant_model');
        # updating or inserting form applicant accordingly
        if($form_applicant_id)
            $this->Recruitmentformapplicant_model->update($form_applicant_id, $answer_data, $request->adminId);
        else
            $form_applicant_id = $this->Recruitmentformapplicant_model->create($answer_data, $request->adminId);
        // add form notes
        if(!empty($answer_data['notes'])){
            $upd_data["notes"] = $answer_data['notes'];           
            $where = [ "id" => $form_applicant_id,"application_id" => $answer_data['application_id'], "applicant_id" => $answer_data['applicant_id']];
            $this->basic_model->update_records("recruitment_form_applicant", $upd_data, $where);
            #update application form history
            $testds = $this->Recruitment_form_applicant_history_model->updateNotesHistory($form_applicant_id,$answer_data['notes'], $request->adminId);
        }

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
        $msg = 'Applicant form successfully submitted';
        if(!empty($answer_data) && isset($answer_data['form_name']) && !empty($answer_data['form_name'])){
            $msg = $answer_data['form_name'].' form successfully submitted';
        }
        $return = array('status' => true, 'msg' => $msg);
        echo json_encode($return);
        exit;
    }

    /*
     * To get staff detail
     * 
     * Return type json
     * - count 
     * - data
     * - status
     */
    function get_curent_staff_detail_by_id() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'user_id', 'label' => 'User Id', 'rules' => 'required', "errors" => [ "required" => "Missing User Id" ])
            ];
            // Set data in libray for validation
            $this->form_validation->set_data($data);

            // Set validation rule
            $this->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->form_validation->run()) {
                // Call model for get form list
                $result = $this->Recruitmentform_model->get_curent_staff_detail_by_id($reqData->data, $adminId);
            } else {
               // If requested data isn't valid
                $errors = $this->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }      
        echo json_encode($result);
        exit();  
    }

    // migrate existing form data's
    public function migrate_form_data() {
        api_request_handler();
        $result = $this->db->query('update tbl_recruitment_form_applicant rfa 
        INNER JOIN (SELECT concat(tbl_recruitment_applicant.firstname," ",tbl_recruitment_applicant.lastname) as name, tbl_recruitment_applicant.id FROM `tbl_recruitment_applicant` ) ra ON rfa.applicant_id = ra.id 
        INNER JOIN (SELECT tbl_recruitment_form.title as form_title, tbl_recruitment_form.id, tbl_recruitment_form.interview_type, rit.name as title FROM `tbl_recruitment_form`
        INNER JOIN tbl_recruitment_interview_type rit ON rit.id = tbl_recruitment_form.interview_type ) rf ON rfa.form_id = rf.id
        set rfa.title = concat(ra.name, " ", rf.title), rfa.status = 2 WHERE 1');
        
        if ($result) {
            $result = ['status' => true, 'msg' => "Migrated successfully"];
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Something went wrong"];
        } 
        echo json_encode($result);
        exit();
    }
    /**
     * send field history response
     */

    private function sendResponse($data, $succes_msg = '') {
        if ($succes_msg) {
            $response = ['status' => true, 'data' => $data, 'msg' => $succes_msg];
        } else {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        }
        echo json_encode($response);
        exit();
    }


    /**
     * fetches history of application
     */

    public function get_field_history() {
        $reqData = request_handler('access_recruitment');
        if (empty($reqData)) {
            return;
        }
        $items = $this->Recruitment_form_applicant_history_model->get_field_history($reqData->data);
        $this->sendResponse($items, 'Success');
    }

    /*
     * post parameters: form-id 
     * result: status true or false 
     */
    public function archive_form_applicant() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        if (!empty($reqData->data->form_id)) {
            $form_id = $reqData->data->form_id;
            // check alreay active form anywgere
            $this->Recruitmentform_model->archive_form_applicant($form_id, $adminId );
            $return = ["status" => true, "msg" => "Form is archived successfully"];
          
        } else {
            $return = ["status" => false, "error" => "form id is missing"];
        }
        
        echo json_encode($return);
    }
}
