<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property-read \Recruitment_jobs_model $Recruitment_jobs_model
 */
class Recruitment_job extends MX_Controller {

    use formCustomValidation;

    function __construct() {

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Recruitment_jobs_model');
        $this->load->model('Basic_model');
        $this->form_validation->CI = & $this;
        $this->loges->setLogType('recruitment_job');
        $this->load->model('document/Document_type_model');
        $this->load->model('common/List_view_controls_model');
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

    public function insert_update_job_delete() {

        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            //$response_valid = $this->validate_jobs_data((array)$reqData);
            //if($response_valid['status']){

            require_once APPPATH . 'Classes/recruitment/Jobs.php';
            $objJobs = new JobsClass\Jobs();

            if ($reqData->mode == 'add') {
                $objJobs->setId(0);
            } else {
                $objJobs->setId($reqData->job_id);
            }
            $objJobs->setJobName($reqData->job_name);
            $objJobs->setStatus(0);
            $objJobs->setCreated(DATE_TIME);
            //$objJobs->setRecruiter(1);
            $objJobs->setJobPosition(1); //$reqData->job_position
            $objJobs->setPhone($reqData->job_phone);
            $objJobs->setEmail($reqData->job_email);
            $objJobs->setWebLink($reqData->job_weblink);
            $objJobs->setJobDescription($reqData->job_content);
            //$objJobs->setJobCategory($reqData->answers);
            //$objJobs->setJobTemplate($reqData->answers);
            $objJobs->setJobStartDate(date("Y-m-d", strtotime($reqData->PostDate)));
            $objJobs->setJobEndDate(date("Y-m-d", strtotime($reqData->EndDate)));
            //$objJobs->setPublish($reqData->answers);
            //$objJobs->setJobStatus($reqData->answers);


            $response = $objJobs->create_Jobs();
            echo json_encode(array('status' => true, 'data' => $response));
            //}else{
            //	echo json_encode($response_valid);
            //}
        } else {
            echo json_encode(array('status' => false));
        }
    }

    public function delete_job() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            require_once APPPATH . 'Classes/recruitment/Questions.php';
            $objQuestions = new QuestionsClass\Questions();
            $objQuestions->setId($reqData->id);
            $objQuestions->delete_Question();
            echo json_encode(array('status' => true));
        } else {
            echo json_encode(array('status' => false));
        }
    }

    public function get_recruitment_job_position_list() {

    }

    public function get_job_questions_list() {

    }

    /* Question Validation start */

    function validate_jobs_data($rosterData) {
        try {
            $validation_rules = array(
                array('field' => 'start_date', 'label' => 'validate_jobs_data', 'rules' => 'callback_check_jobs_data[' . json_encode($rosterData) . ']')
            );
            $this->form_validation->set_data($rosterData);
            $this->form_validation->set_rules($validation_rules);
            if ($this->form_validation->run()) {
                $return = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
        } catch (Exception $e) {
            $return = array('status' => false, 'error' => system_msgs('something_went_wrong'));
        }
        return $return;
    }

    function check_jobs_data($data, $questionData) {

        $question_Data = json_decode($questionData);
        if (!empty($question_Data)) {
            if (empty($question_Data->question)) {
                $this->form_validation->set_message('check_question_data', 'Question can not empty');
                return false;
            }
            if (empty($question_Data->question_topic)) {
                $this->form_validation->set_message('check_question_data', 'Question topic can not empty');
                return false;
            }
            if (empty($question_Data->question_category)) {
                $this->form_validation->set_message('check_question_data', 'Question category can not empty');
                return false;
            }
            if (empty($question_Data->answers)) {
                $this->form_validation->set_message('check_question_data', 'Answer can not empty');
                return false;
            }
            $cntAnswer = 0;
            foreach ($question_Data->answers as $answer) {
                if ($answer->checked) {
                    $cntAnswer++;
                }
            }
        } else {
            $this->form_validation->set_message('check_roster_data', 'Roster details can not empty');
            return false;
        }
        return true;
    }

    /* Question Validation end */

    public function get_job_master_list() {
        $reqData = request_handler('access_recruitment_admin');
        $data = [];
        
        $data["employmentType"] = $this->Recruitment_jobs_model->get_employement_type_ref_list("employment_type");
        
        $responsePosition = $this->basic_model->get_record_where('recruitment_job_position', $column = array('title as label', 'id as value'), $where = array('archive' => '0'));
        $data['position'] = isset($responsePosition) && !empty($responsePosition) ? $responsePosition : array();

        $responseJobType = $this->basic_model->get_record_where('recruitment_job_type', $column = array('title as label', 'id as value'), $where = array('archive' => '0'));
        $data['jobType'] = isset($responseJobType) && !empty($responseJobType) ? $responseJobType : array();


        $responseSalaryRange = $this->basic_model->get_record_where('recruitment_job_salary_range', $column = array('title as label', 'id as value'), $where = array('archive' => '0'));
        $data['salaryRange'] = isset($responseSalaryRange) && !empty($responseSalaryRange) ? $responseSalaryRange : array();

        $responseCategory = $this->basic_model->get_record_where('recruitment_job_category', $column = array('parent_id', 'id as value', 'name as label'), $where = array('archive' => '0', 'parent_id' => 0));
        $data['category'] = isset($responseCategory) && !empty($responseCategory) ? $responseCategory : array();

        $responseJobTemplate = $this->basic_model->get_record_where('recruitment_job_template', $column = array('id', 'template', 'name', 'thumb'), $where = array('archive' => '0'));
        $data['jobTemplate'] = isset($responseJobTemplate) && !empty($responseJobTemplate) ? $responseJobTemplate : array();       
        
        $data['business_unit_options'] = get_business_unit_options($reqData);
       
        $data['is_super_admin'] = FALSE;

        if(!empty($reqData->business_unit['is_super_admin'])) {
            $data['is_super_admin'] = TRUE;
            $data['bu_id'] = '';            
        }else {
            $data['bu_id'] = $data['business_unit_options'][0]->value;
        }
        echo json_encode(array('status' => true, 'data' => $data));
    }    

    /**
     * getting all the document categories which are required at the time of applying job
     */
    public function get_req_documents_job_apply() {
        $reqData = request_handler('access_recruitment_admin');
        $data = $all_documents = [];

        $data['doc_category'] = 'apply_job';
        $data['col1'] = 'display_name';
        $data['col2'] = 'id';
        $data['module_id'] = REQUIRMENT_MODULE_ID;

        $response = $this->Document_type_model->get_document_type($data);

        if (!empty($response)) {
            foreach ($response as $val) {
                $all_documents[] = array('label' => $val->display_name, 'value' => $val->id, 'optional' => false, 'mandatory' => false, 'clickable' => false, 'selected' => false);
            }
        }
        echo json_encode(array('status' => true, 'data' => $all_documents));
    }

    /**
     * getting all the document categories which are required during the recruitment stage
     */
    public function get_req_documents_recruit_stages() {
        $reqData = request_handler('access_recruitment_admin');
        $data = $all_documents = [];

        $data['doc_category'] = 'recruitment_stage';
        $data['col1'] = 'display_name';
        $data['col2'] = 'id';
        $data['module_id'] = REQUIRMENT_MODULE_ID;

        $response = $this->Document_type_model->get_document_type($data);

        if (!empty($response)) {
            foreach ($response as $val) {
                $all_documents[] = array('label' => $val->display_name, 'value' => $val->id, 'optional' => false, 'mandatory' => false, 'clickable' => false, 'selected' => false);
            }
        }
        echo json_encode(array('status' => true, 'data' => $all_documents));
    }

    /*
    * Method not in used
    */
    public function get_job_apply_documents() {}

    public function get_job_channel_details() {
        $reqData = request_handler('access_recruitment_admin');
        $data = [];
        $response = $this->basic_model->get_record_where('recruitment_channel', $column = array('id', 'channel_name'), $where = array('archive' => '0'));
        if (!empty($response)) {
            foreach ($response as $val) {
                $all_details[] = array('drp_dwn' => array('label' => $val->channel_name, 'value' => $val->id), 'question' => [], 'channel_name' => $val->channel_name, 'question_tab' => false, 'drp_dwn_val' => $val->id);
            }
        }
        echo json_encode(array('status' => true, 'data' => $all_details));
    }

    public function get_subcategory_from_seek() {
        $reqData = request_handler('access_recruitment_admin');
        $PostDate = $reqData->data;
        $category_id = $PostDate->category_id;
        $responseCategory = $this->basic_model->get_record_where('recruitment_job_category', $column = array('parent_id', 'id as value', 'name as label'), $where = array('archive' => '0', 'parent_id' => $category_id));
        $sub_cat = isset($responseCategory) && !empty($responseCategory) ? $responseCategory : array();
        echo json_encode(array('status' => true, 'data' => $sub_cat));
    }

    public function get_seek_ques_by_cat_subcat() {
        $reqData = request_handler('access_recruitment_admin');
        $PostDate = $reqData->data;
        $category_id = $PostDate->category_id;
        $sub_category = $PostDate->sub_category;
        $all_details = [];

        $responseQuestion = $this->basic_model->get_record_where('recruitment_seek_question', $column = array('question', 'id'), $where = array('archive' => '0', 'category' => $category_id, 'sub_category' => $sub_category));

        if (!empty($responseQuestion)) {
            foreach ($responseQuestion as $val) {
                $all_details[] = array('question' => $val->question, 'id' => $val->id, 'question_edit' => false, 'editable_class' => '', 'btn_txt' => 'Edit');
            }
        }
        echo json_encode(array('status' => true, 'data' => $all_details));
    }

    public function save_job() {
        $request = request_handler('access_recruitment',true,false,true);//pr($request);
        $jobs_data = (array) json_decode($request->data, true);

        $jobs_data['all_docs_job_apply1'][] = (object) $jobs_data['all_docs_job_apply'];
        $jobs_data['all_docs_recruit1'][] = (object) $jobs_data['all_docs_recruit'];
        if (!empty($jobs_data)) {
            $validation_rules = array(
                array('field' => 'all_docs_job_apply1[]', 'label' => 'Required Documents', 'rules' => 'callback_check_required_job_apply_docs'),
                array('field' => 'all_docs_recruit1[]', 'label' => 'Required Documents', 'rules' => 'callback_check_required_recruit_docs'),
                array('field' => 'type', 'label' => 'Job Type'),
                array('field' => 'title', 'label' => 'Title'),
                array('field' => 'bu_id', 'label' => 'Business Unit', 'rules' => 'required'),
                array('field' => 'category', 'label' => 'Job Category', 'rules' => 'required'),
                array('field' => 'sub_category', 'label' => 'Job Sub Category', 'rules' => 'required'),
                array('field' => 'interview_job_stage_id', 'label' => 'Interview Stage', 'rules' => 'required'),
                array('field' => 'job_sub_stage_id', 'label' => 'Interview Sub Stage', 'rules' => 'required'),
                array('field' => 'position', 'label' => 'Job Position', 'rules' => ''),
                array('field' => 'employment_type', 'label' => 'Employment Type', 'rules' => 'required'),
                array('field' => 'salary_range', 'label' => 'Salary Range', 'rules' => ''),
                // array('field' => 'job_location', 'label' => 'Job Location', 'rules' => 'required'),
                array('field' => 'phone', 'label' => 'Phone', 'rules' => 'callback_phone_number_check[phone]'),
                array('field' => 'email', 'label' => 'Email', 'rules' => 'valid_email'),
                array('field' => 'website', 'label' => 'Website', 'rules' => 'valid_url'), //
                array('field' => 'job_content', 'label' => 'Job Content', 'rules' => 'required'),
                array('field' => 'activeTemplate', 'label' => 'Job Style Template', 'rules' => 'required'),
                array('field' => 'form_id', 'label' => 'Form', 'rules' => 'required'),
            );

            $other_validation_ary = [];
            if(isset($jobs_data['interview_job_stage_id']) && $jobs_data['interview_job_stage_id'] == 8){
                $validation_rules = array(
                    array('field' => 'individual_interview_count', 'label' => 'Count of Individual Interview', 'rules' => 'required|numeric|callback_check_min_max_number'),
                    array('field' => 'all_docs_job_apply1[]', 'label' => 'Required Documents', 'rules' => 'callback_check_required_job_apply_docs'),
                    array('field' => 'all_docs_recruit1[]', 'label' => 'Required Documents', 'rules' => 'callback_check_required_recruit_docs'),
                );
            }

            $validation_rules = array_merge($validation_rules,$other_validation_ary);
            $this->form_validation->set_data($jobs_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $job_operation = $jobs_data['job_operation'];

                $ocs_row = $this->Recruitment_jobs_model->save_job($jobs_data);
                // pr($jobs_data);

                $this->create_job_template_for_apply_job($ocs_row['jobId']);
                /* logs */
                if ($job_operation == 'E'){
                    $title = "Edit job: " . $ocs_row['jobId'];
                }else if ($job_operation == 'D'){
                    $title = "Duplicate job is created from Job id ". $jobs_data['job_id']." and New job id is : " . $ocs_row['jobId'];
                }else{
                    $title = "Add job: " . $ocs_row['jobId'];
                }

                $this->loges->setTitle($title);
                $this->loges->setUserId($request->adminId);
                $this->loges->setDescription(json_encode($jobs_data));
                $this->loges->setCreatedBy($request->adminId);
                $this->loges->createLog();
                $return = array('status' => true, 'msg' => $ocs_row['msg']);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
        }
    }

    /**
     * callback validation function to validate the documents required during job apply category
     */
    public function check_required_job_apply_docs($docs) {
        if (!empty($docs)) {
            $count = count((array) $docs);
            $ii = 0;
            foreach ($docs as $key => $value) {

                if (isset($value['clickable']) && $value['clickable'] != 1) {
                    $ii = $ii + 1;
                }

                if ($ii == $count) {
                    $this->form_validation->set_message('check_required_job_apply_docs', 'Please select atleast one document from "Docs required (Apply Job)"');
                    return false;
                }
            }
        } else {
            $this->form_validation->set_message('check_required_job_apply_docs', 'Please select atleast one document from "Docs required (Apply Job)"');
            return false;
        }
        return true;
    }

    /**
     * callback validation function to validate the documents required during the recruitment stages
     */
    public function check_required_recruit_docs($docs) {
        if (!empty($docs)) {
            $count = count((array) $docs);
            $ii = 0;
            foreach ($docs as $key => $value) {

                if (isset($value['clickable']) && $value['clickable'] != 1) {
                    $ii = $ii + 1;
                }

                if ($ii == $count) {
                    $this->form_validation->set_message('check_required_recruit_docs', 'Please select atleast one document from "Docs required (Recruitment Stages)"');
                    return false;
                } else {
                    return true;
                }
            }
        } else {
            $this->form_validation->set_message('check_required_recruit_docs', 'Please select atleast one document from "Docs required (Recruitment Stages)"');
            return false;
        }
        return true;
    }

    public function get_all_jobs() {
            $reqData = $reqData1 = request_handler('access_recruitment');
            #pr($reqData);
            if (!empty($reqData->data)) {
                $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
                $filter_condition = str_replace(['job_category'], ['job_category_id'], $filter_condition);
                $result = $this->Recruitment_jobs_model->get_all_jobs($reqData, $filter_condition);
                echo json_encode($result);
            }
    }

    public function get_job_detail() {
        $reqData = $reqData1 = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $result = $this->Recruitment_jobs_model->get_job_detail($reqData);
            echo json_encode($result);
        }
    }

    /*
    * This method is call from create job when add new document type is added.
    * This requirement is closed now and method is not in used.
    */
    public function save_job_required_documents() {
        $request = request_handler('access_recruitment');
        $docs_data = (array) $request->data;
        if (!empty($docs_data)) {
            $validation_rules = array(
                array('field' => 'document_name', 'label' => 'Document name', 'rules' => 'required'),
            );

            $this->form_validation->set_data($docs_data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $ocs_row = $this->Recruitment_jobs_model->save_job_required_documents($docs_data);
                if (!empty($ocs_row) && $ocs_row['status'])
                    $return = array('status' => true, 'msg' => 'Documents save successfully.', 'id' => isset($ocs_row['id']) ? $ocs_row['id'] : '', 'data' => $ocs_row['data']);
                else
                    $return = array('status' => false, 'error' => 'Error, Please try again.');
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
        }
    }

    public function update_job_status() {
        $reqData = $reqData1 = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $title = 'Job status has been changed to '.$reqData->status.' for JobId '.$reqData->job_id;
            $this->loges->setTitle($title);
            $this->loges->setUserId($reqData1->adminId);
            $this->loges->setDescription(json_encode($reqData));
            $this->loges->setCreatedBy($reqData1->adminId);
            $this->loges->createLog();

            $result = $this->Recruitment_jobs_model->update_job_status($reqData);
            echo json_encode($result);
        }
    }

    /**
     * the purpose of this function is changed now, previously it was saving a static file on server
     * now it only adds a db table entry into recruitment_job_published_detail table
     */
    public function create_job_template_for_apply_job($job_id='35')
    {
        if($job_id!='')
        {
            $job_domain = getenv('OCS_JOB_SUB_DOMAIN') ? "https://".getenv('OCS_JOB_SUB_DOMAIN') : base_url();
            # this is the url where apply job will be visible
            $channel_url = $job_domain.'jobs/'.$job_id;
            $this->basic_model->update_records('recruitment_job_published_detail', array('channel_url'=>$channel_url),array('jobId'=>$job_id,'channel'=>2,'archive'=>0));
        }
    }

    public function get_job_stage() {
        $reqData = request_handler('access_recruitment_admin');
        $data = [];
        $response = $this->basic_model->get_record_where('recruitment_stage_label', $column = array('label_used_in_create_job as label', 'id as value','key_name'), $where = array('archive' => '0','used_in_create_job'=>'1'));
        $job_stage = isset($response) && !empty($response) ? $response : array();
        echo json_encode(array('status' => true, 'data' => $job_stage));
    }

    /**
     * Get list of forms under 'job question' category.
     * If `jobId` is part of the request, will send the selected `form_id` too
     * @return \CI_Output
     */
    public function job_question_forms()
    {
        $reqData = request_handler('access_recruitment');
        $data = $reqData->data;

        $results = $this->Recruitment_jobs_model->job_question_forms_options($reqData);

        $response = [
            'status' => true,
            'data' => $results,
        ];

        // send form_id if `jobId` requested
        if (isset($data->jobId) && !empty($data->jobId)) {
            $form_id_by_job_id = $this->db->from('tbl_recruitment_job_forms')
            ->where(['job_id' => $data->jobId])
            ->select()
            ->get()
            ->row_array();

            $response = array_merge($response, [
                'form_id' => $form_id_by_job_id['form_id'] ?? '',
            ]);
        }

        return $this->output->set_content_type('json')->set_output(json_encode($response));

    }

    /**
     * Get all active job application
     * return - json format
     */
    public function get_job_application() {
        $reqData = request_handler('access_recruitment');
        $data = $reqData->data;
        if (!empty($data)) {
            $validation_rules = array(
                array('field' => 'application_id', 'label' => 'Application id', 'rules' => 'required'),
            );

            $data_array = (array) $data->data;

            $this->form_validation->set_data($data_array);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $results = $this->Recruitment_jobs_model->job_options($data->data);
                $return = array('status' => true, 'data' => $results, 'msg'=> 'Job list successfull');
            } else {
                $results = [];
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
        } else {
            $results = [];
            $return = array('status' => false, 'error' => 'Request data is null');
        }

        return $this->output->set_content_type('json')->set_output(json_encode($results));
    }

    /*
     * Transfer Application
     */
    public function transfer_application() {
        $reqData = request_handler('access_recruitment');
        $data = $reqData->data;
        $adminId = $reqData->adminId;
        if (!empty($data)) {
            $validation_rules = array(
                array('field' => 'applicant_id', 'label' => 'Applicant id', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'Application id', 'rules' => 'required'),
            );

            $data_array = (array) $data;
            $this->form_validation->set_data($data_array);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $result = $this->Recruitment_jobs_model->transfer_application($data, $adminId);
                $job = $data->selected_job;
                $job_label = '';
                if (isset($job) == true && empty($job) == false) {
                    $job_label = $job->label;
                }
                $return = array('status' => true, 'data' => $result, 'msg'=> 'The application has been transferred to '.$job_label);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
        } else {
            $return = array('status' => false, 'error' => 'Request data is null');
            echo json_encode($return);
        }
    }
}
