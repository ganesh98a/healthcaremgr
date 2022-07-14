<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property-read \Recruitmentform_model $Recruitmentform_model
 * @property-read \RecruitmentAppliedForJob_model $RecruitmentAppliedForJob_model
 * @property-read \Recruitmentformapplicant_model $Recruitmentformapplicant_model
 */
class RecruitmentAppliedForJob extends MX_Controller {

    use formCustomValidation;

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('seek');
        $this->load->model('common/Common_model');
        $this->form_validation->CI = & $this;

        $this->load->helper(['array_helper']);
        $this->load->model(['RecruitmentAppliedForJob_model', 'Recruitmentform_model','Recruitment_applicant_model']);
        $this->loges->setLogType('recruitment_applicant');
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

    public function index() {
        die('Invalid request.');
    }

	public function getSeekDetails() {
		$response = ['status' => true, 'page' => 'seek'];
        // Get authorize_code from url
        $authorize_code = $this->input->get('code', TRUE);
        // Get state from url
        $state = $this->input->get('state', TRUE);
        // Get state from url
        $error = $this->input->get('error', TRUE);
        // Set state
        $this->seek->setState($state);
        // allow if error not equal to access_denied
        if ($error != 'access_denied') {
            // Set Authorize code
            $this->seek->setAuthorizeCode($authorize_code);
            // get referrer url if redirected from thirt party server
            $reqData = $this->input->post(NULL, TRUE);
            // print_r($reqData);
            // Get Authorize code
            $authorize = $this->seek->getAccessTokenApi();
            // get job domain 
            $job_domain_url = $this->seek->getJobDomainUrl();
            if ($authorize == true) {
                // $authorize = $this->seek->getApplicantDeatilsApi();
                $job_id = $this->seek->getState();
                $access_token = $this->seek->getAccessToken();
                $redirect_job_url = 'jobs/'.$this->seek->getState().'?access_token='.$access_token.'&src=seek&prefilled=true';
                redirect($job_domain_url.$redirect_job_url);
            } else {
                $redirect_job_url = 'jobs/'.$this->seek->getState().'?error=something_went_wrong&src=seek';
                redirect($job_domain_url.$redirect_job_url);
            }
        } else {
            $redirect_job_url = 'jobs/'.$this->seek->getState().'?error=access_denied&src=seek';
            redirect($job_domain_url.$redirect_job_url);
        }
		echo json_encode($response);
	}

    public function job_preview() {
        $data['title'] = 'Recruitment Job';
        $data['form_action'] = base_url('recruitment/RecruitmentAppliedForJob/create_applicant');
        $data['saveAttachment'] = base_url('recruitment/RecruitmentAppliedForJob/saveAttachment');
        $this->load->model('common/Common_model');

        # get the job id from new URL re-written part
        $job_id = $this->uri->segment(2)??0;

        #$job_id = encrypt_decrypt('decrypt', $job_id);
        if($job_id <=0){
           echo json_encode(array('status'=>false,'msg'=>'Invalid request'));
       }
       $job_data = $this->RecruitmentAppliedForJob_model->check_live_job($job_id);
     #echo json_encode(array('status'=>true,'data'=>$job_data));

       if(!empty($job_data)){ 
        $data['job_data'] = $job_data;

        $data['form_data'] = [];

        $associatedForms = $this->db
        ->from('tbl_recruitment_form')
        ->join('tbl_recruitment_job_forms', 'tbl_recruitment_job_forms.form_id = tbl_recruitment_form.id')
        ->where([ 'tbl_recruitment_job_forms.job_id' => $job_id ])
        ->select('tbl_recruitment_form.*')
        ->get()
        ->result_array();

        if (!empty($associatedForms)) {
            foreach ($associatedForms as $associatedForm) {
                $data['form_data'][] = [
                    'form' => $associatedForm,
                    'questions' => $this->Recruitmentform_model->get_form_questions_list([ 'form_id' => $associatedForm['id'] ], [])
                ];
            }
        }
        /*
         * Seek Setting details
         */
        $config['client_id'] = $this->seek->getUsername();
        $config['redirect_uri'] = $this->seek->getRedirectUrl();
        $config['state'] = $job_id;
        $config['advertiser_id'] = $this->seek->getAdvertiserId();
        // get referrer url if redirected from thirt party server
        $reqData = $this->input->post(NULL, TRUE);
        if (isset($reqData) && isset($reqData['referrer_url'])) {
            $referrer_url = $reqData['referrer_url'];
            $referrer_url_domain = $referrer_url != '' ? getDomain($referrer_url) : '';
            // check the domain if seek
            $config['redirected_by'] = strtolower($referrer_url_domain) == 'seek' ? 'seek' : '';
        } else {
            $config['redirected_by'] = $this->input->get('src', TRUE);
        }        
        $data['seek_settings'] = $config;
        /*
         * Get applicant details from seek 
         */
        $access_token = $this->input->get('access_token', TRUE);
        $src = $this->input->get('src', TRUE);
        if ($access_token != '') {
            // Set access token
            $this->seek->setAccessToken($access_token);
            // Call applicant prefill api
            $call_prefilled_endpoint = $this->seek->getApplicantDeatilsApi();
            $getPrefilledData = array();
            $data['applicant_info'] = $getPrefilledData;
            $data['resume'] = $getPrefilledData;
            if ($call_prefilled_endpoint == true) {
                // Set applicant detail
                $getPrefilledData = $this->seek->getPrefilledData();
                if (isset($getPrefilledData['applicantInfo'])) {
                    $data['applicant_info'] = $getPrefilledData['applicantInfo'];
                }
                if (isset($getPrefilledData['complete'])) {
                    $this->seek->setCompleteApplicationUrl($getPrefilledData['complete']);
                    $data['applicant_info']['complete_url'] = $getPrefilledData['complete'];
                }
                if (isset($getPrefilledData['resume'])) {
                    $data['resume'] = $getPrefilledData['resume'];
                    // Set resume link
                    $this->seek->setResumeUrl($getPrefilledData['resume']['link']);
                    // Save resume in temp folder
                    $resume_temp = $this->seek->downloadApplicantResume();
                    if ($resume_temp == true) {
                        $temp_folder = $this->seek->getTempFolderName();
                        $resume_file_name = $this->seek->getResumeFileName();
                        $job_domain_url = $this->seek->getJobDomainUrl();
                        $resume_file_name_encoded = base64_encode($resume_file_name);
                        $data['resume']['file_name'] = $this->seek->getResumeFileName();
                        $data['resume']['temp_path'] = $this->seek->getResumeTempUrl().'/';
                        $data['resume']['full_path'] = $this->seek->getResumeTempFullUrl();
                        $data['resume']['view_path'] = $job_domain_url . 'mediaShowTemp/' . $temp_folder . '?filename=' . $resume_file_name_encoded;

                        // get document info
                        $file_full_path = FCPATH . $this->seek->getResumeTempFullUrl();
                        $getDocumentInfo = pathinfo($file_full_path);
                        $raw_name = isset($getDocumentInfo) && isset($getDocumentInfo['filename']) ? $getDocumentInfo['filename'] : '';
                        $file_extension = isset($getDocumentInfo) && isset($getDocumentInfo['extension']) ? $getDocumentInfo['extension'] : '';
                        // set resume data
                        $tempResumeData['file_name'] = $this->seek->getResumeFileName();
                        $tempResumeData['file_type'] = mime_content_type($file_full_path);
                        $tempResumeData['file_path'] = FCPATH . $this->seek->getResumeTempUrl().'/';
                        $tempResumeData['full_path'] = $file_full_path;
                        $tempResumeData['raw_name'] = $raw_name;
                        $tempResumeData['orig_name'] = $this->seek->getResumeFileName();
                        $tempResumeData['client_name'] = $this->seek->getResumeFileName();
                        $tempResumeData['file_ext'] = '.'.$file_extension;
                        $tempResumeData['file_size'] = filesize($file_full_path);
                        $data['resume']['temp_resume_data'] = ($tempResumeData);
                    }                    
                }
                $data['applicant_info']['timeout_error'] = false;
                $data['emailAddress'] = 'fasssslse';
            } else {
                $data['applicant_info']['timeout_error'] = true;
            }
        }
        $this->load->view('recruitment/preview_job_n_applied',$data); 
    }
}
public function get_applicant_existing_address()
{
    $reqData = $this->input->post(NULL, TRUE);
    $result =  $this->RecruitmentAppliedForJob_model->check_any_duplicate_applicant($reqData);
    echo json_encode($result);
}

public function create_applicant()
{
    $reqData = $this->input->post(NULL, TRUE);
   
    if (!empty($reqData)) {
        
        $reqData['bu_id'] = '';
        if(!empty($reqData['job_id'])) {
            $reqData['bu_id'] = $this->Common_model->get_business_units_dynamically('recruitment_job', ['id' => $reqData['job_id']]);
        }

        $validation_rules = array(
            array('field' => 'firstname', 'label' => 'firstname', 'rules' => 'required|max_length[30]'),
            array('field' => 'lastname', 'label' => 'lastname', 'rules' => 'required|max_length[30]'),
            array('field' => 'phone', 'label' => 'phone', 'rules' => 'callback_phone_number_check[phone,required,Please enter valid mobile number.]'),
            array('field' => 'email', 'label' => 'email', 'rules' => 'required|valid_email'),
            array('field' => 'address', 'label' => 'address', 'rules' => 'callback_check_string_google_address_is_valid'),
            array('field' => 'dob', 'label' => 'dob', 'rules' => 'required'),
            // HCM-102 - We'll not require addresses and references for now, might be needed in the future
            // array('field' => 'reference', 'label' => 'references', 'rules' => 'callback_check_applicant_references[' .json_encode($reqData['reference']) . ']'),
            // array('field' => 'address', 'label' => 'addresses', 'rules' => 'required'),
        );        
      

        if (!empty(element('forms', $reqData, []))) {
            $validation_rules = $this->validation_rules_for_forms($reqData['forms'], $validation_rules);
        }

        $this->form_validation->set_data($reqData);
        $this->form_validation->set_rules($validation_rules);

        if ($this->form_validation->run()) {
            // skip the resume required if attachment from seek
            $seek_resume_active = 0;
            $seek_resume_name = '';
            if (isset($reqData['seek_resume']) && isset($reqData['seek_resume']['active']) && $reqData['seek_resume']['active'] == 1) {
                $seek_resume_active = $reqData['seek_resume']['active'];
                $seek_resume_name = $reqData['seek_resume']['name'];
            }

            $response = $this->RecruitmentAppliedForJob_model->save_multiple_attachments($reqData['job_id'], $_FILES, $seek_resume_active, $seek_resume_name);
            if (!$response['status']) {
                return $this->output->set_output(json_encode($response));
            }

            // get resume attachment from seek and assign into uploaded files. 
            if (isset($reqData['seek_resume']) && isset($reqData['seek_resume']['active']) && $reqData['seek_resume']['active'] == 1) {
                $seek_resume_name = $reqData['seek_resume']['name'];
                $resumeData = $this->RecruitmentAppliedForJob_model->get_resume_details( $reqData['seek_resume']);
                $response['uploaded_files'][$seek_resume_name] = $resumeData;
            }

            $reqData['file_ary'] = json_encode($response['uploaded_files']);

            $return = $this->RecruitmentAppliedForJob_model->create_applicant($reqData);
            
            // Call the complete endpoint on the application API to complete your application
            if(isset($reqData['source']) && $reqData['source'] == 'seek'){
                if (isset($reqData['seek_complete_url']) && $reqData['seek_complete_url'] !='')
                {
                   $this->seek->setCompleteApplicationUrl($reqData['seek_complete_url']);
                }
                // Get client access token
                $this->seek->getClientAccessTokenApi();
                // Call end point
                $this->seek->completeApplicationApi();
            }

            if ($return['status'] == true && !empty(element('forms', $reqData, []))) {
                $this->capture_applicant_form_answers($reqData, $return);
            }

            if ($return['status'] == true) {
                $applicant_id = $return['applicant_id'];
                $this->loges->setTitle('Applicant is created from applicant form, id - : ' . $applicant_id);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();
            }
            
            $response = $return;
        } else {
            $errors = $this->form_validation->error_array();
            $response = ['status' => false, 'error' => implode(', ', $errors)];
        }
        echo json_encode($response);
    }
}

public function check_applicant_references($dd,$sourceData) 
{
    $sourceData = json_decode($sourceData);
    if (!empty($sourceData)) {
        foreach ($sourceData as $key => $value) {

            if (empty($value->name)) {
                $this->form_validation->set_message('check_applicant_references', "Please fill reference name.");
                return false;
            }
            if (empty($value->phone)) {
                $this->form_validation->set_message('check_applicant_references', "Please fill reference phone.");
                return false;
            }
            if (empty($value->email) || !filter_var($value->email, FILTER_VALIDATE_EMAIL)) {
                $this->form_validation->set_message('check_applicant_references', "Please fill valid reference email.");
                return false;
            }
        }
        return true;
    } else {
        $this->form_validation->set_message('check_recruitment_applicant_references', "Please fill reference details.");
        return false;
    }
}

    /*public function get_state_list()
    {
        $this->load->model('common/Common_model');
        $state_list = $this->Common_model->get_state();
        echo json_encode($state_list);
    }

    public function get_suburb_list()
    {
        $srch_str = $this->input->get('name',true);
        $this->load->model('common/Common_model');
        $state_list = $this->Common_model->get_suburb($srch_str,1);
        echo json_encode($state_list);
    }*/

    public function saveAttachment()
    {
        if($_FILES) {          
            $return_ary = ['selected_name'=>key($_FILES)];
            $config['upload_path'] = FCPATH . ARCHIEVE_DIR;
            $config['directory_name'] = '';
            $config['max_size'] = '100000';
            $config['input_name'] = key($_FILES);
            #$config['allowed_types'] = 'jpg|png|jpeg|JPEG|pdf';
            $config['allowed_types'] = DEFAULT_ATTACHMENT_UPLOAD_TYPE;         
            
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload($config['input_name'])) {
                $status = false;
                $response = array('error' => $this->upload->display_errors());
            } else {
                $status = true;
                $response = array('upload_data' => $this->upload->data());
            }
            echo json_encode(array('status' => $status, 'data' => array_merge($response,$return_ary)));
            exit();
        }
    }

    /**
     * Method that processes applicant's answers from job ad.
     * 
     * Although the request can support accepting multiple forms, 
     * it will only process the first one. 
     * 
     * This is extracted to a separate method to make the calling method/ctrl action
     * less daunting (less lines of code at glance)
     * 
     * @todo If there's a need to prcess multple forms, refactor this method
     * 
     * @param mixed $reqData 
     * @param mixed $applicantResult 
     */
    protected function capture_applicant_form_answers($reqData, $applicantResult)
    {
        $application_id = $applicantResult['application_id'];
        $applicant_id = $applicantResult['applicant_id'];
        $form_id = null;
        $firstForm = null;

        if($reqData['forms']){
            foreach ($reqData['forms'] as $id => $form) {
                $form_id = $id;
                $firstForm = $form;
                break;
            }
        }

        // these ids are required
        if (!$form_id || !$application_id || !$applicant_id) {
            return;
        }

        $form_applicant_req = [
            'application_id' => $application_id,
            'applicant_id' => $applicant_id,
            'form_id' => $form_id,
            "interview_type" => null, // this is just to prevent array access error
            // reference_id is not needed because the form is answered in job ad
        ];

        // form applicant id is the ID of junction table for these 3 PKs above
        // as for why application_id is required, I don't know.
        // Maybe application_id is the resulting ID of many-to-many relation between applicant and jobs
        $form_applicant_id = $this->Recruitmentformapplicant_model->applicant_form_submitted($form_applicant_req);

        if ($form_applicant_id) {
            // Note: this probably wont execute at all because the 
            // form was from the job ad
            $this->Recruitmentformapplicant_model->update($form_applicant_id, $form_applicant_req, 0);
        } else {
            $form_applicant_id = $this->Recruitmentformapplicant_model->create($form_applicant_req, 0);
        }

        $answer_data = array_merge($form_applicant_req, [
            'question_answers' => $this->create_question_answers_from_request_data($firstForm, $form_id),
            'form_applicant_id' => $form_applicant_id,
        ]);

        $this->Recruitmentformapplicant_model->save_interview_answer_of_applicant($answer_data);
    }

    /**
     * Maps the values of `$form` param to some array which will be 'processable' by method
     * `Recruitmentformapplicant_model::save_interview_answer_of_applicant`
     * 
     * The parameter `$form` is shaped as below:
     * ```php
     * <?php
     * $form = [
     * 
     *     // question_id => array
     * 
     *     '122' => [
     *          // usually checkbox
     *          'answer_id' => ['234', '235', '237'], // multiple values selected
     *     ],
     *     '122' => [
     *          // unfilled checkboxes or radio button
     *      ],
     *     '124' => [
     *          // usually radio button
     *          'answer_id' => 256      // answer id, only 1 selected
     *     ],
     *     '126' => [
     *          // for textarea
     *          'answer_text' => 'australia', 
     *     ],
     *     '127' => [ 
     *          // for textarea unfilled
     *          'answer_text' => '' 
     *      ],
     * ]
     * 
     * ```
     * 
     * @param array<int, array[]> key as question ID, value as assoc array shaped like `['question_type' => '1', ]`
     * @return array 
     */
    protected function create_question_answers_from_request_data($form, $form_id)
    {
        $original_questions = $this->Recruitmentform_model->get_form_questions_list([ 'form_id' => $form_id ], []);
        $original_questions = json_decode(json_encode($original_questions), true);

        $questions_map = [];
        foreach ($original_questions as $i => $s) {
            $questions_map[$s['id']] = array_merge($s, [
                'answer_id' => [],
            ]);
        }

        foreach ($form as $question_id => $submitted_question) {
            $question_type = $questions_map[$question_id]['question_type'];

            $answerIds = [];

            if (in_array($question_type, [1,2,3])) {
                $answers = element('answer_id', $submitted_question, []);
                $answerIds = is_array($answers) ? $answers : [$answers];
                $questions_map[$question_id]['answer_id'] = $answerIds;

                if (empty($answerIds)) {
                    continue;
                }

                foreach ($questions_map[$question_id]['answers'] as $i => $choice) { 
                    $checked = in_array($choice['answer_id'], $answerIds);
                    $questions_map[$question_id]['answers'][$i]['checked'] = $checked;
                }

            } else if (in_array($question_type, [4])) {
                $questions_map[$question_id]['answer_text'] = element('answer_text', $submitted_question, '');
            }
        }

        $array_vals = array_values($questions_map);

        return $array_vals;
    }

    /**
     * Helper method for auditing data
     * 
     * @param array $data 
     * @return void 
     */
    protected function log(array $data)
    {
        if (array_key_exists('title', $data)) {
            $this->loges->setTitle($data['title']);
        }
        
        if (array_key_exists('user_id', $data)) {
            $this->loges->setUserId($data['user_id']);
        }

        if (array_key_exists('description', $data)) {
            $this->loges->setDescription(json_encode($data['description']));
        }
        
        if (array_key_exists('created_by', $data)) {
            $this->loges->setCreatedBy($data['created_by']);
        }

        $this->loges->createLog();
    }


    /**
     * Apply additional server side validation for forms in the job ad.
     * 
     * Tip: Good rule of thumb in form validation is ALWAYS VALIDATE submitted input in server-side. 
     * The client-side validation is just to improve UX.
     * 
     * @param array $forms 
     * @param array $existing_validation_rules 
     * @return mixed|array 
     */
    protected function validation_rules_for_forms(array $forms, array $existing_validation_rules)
    {
        $form = null;
        $form_id = null;
        foreach ($forms as $id => $f) {
            $form_id = $id;
            $form = $f;
        }

        if (!$form) {
            return $existing_validation_rules;
        }

        $STATUS_ACTIVE = 1;
        $required_questions = $this->db->get_where('tbl_recruitment_additional_questions', [
            'form_id' => $form_id,
            'is_required' => 1,
            'archive' => 0,
            'status' => $STATUS_ACTIVE,
        ])->result_array();

        foreach ($required_questions as $required_question) {
            if (in_array($required_question['question_type'], [1,2,3])) { // if checkboxes/radio
                $existing_validation_rules[] = [
                    'label' => $required_question['question'],
                    'field' => "forms[{$form_id}][{$required_question['id']}][answer_id]",
                    'rules' => [ 
                        'required',
                    ],
                    'errors' => [
                        'required' => "You need to select at least one of the choices in question '{$required_question['question']}'"
                    ]
                ];
            } else if (in_array($required_question['question_type'], [4])) {
                $existing_validation_rules[] = [
                    'label' => $required_question['question'],
                    'field' => "forms[{$form_id}][{$required_question['id']}][answer_text]",
                    'rules' => [ 
                        'required'
                    ],
                    'errors' => [
                        'required' => "You need to answer this question '{$required_question['question']}'"
                    ]
                ];
            }
        }

        return $existing_validation_rules;

    }
}
