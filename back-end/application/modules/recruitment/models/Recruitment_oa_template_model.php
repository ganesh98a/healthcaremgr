<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Recruitment_oa_template_model extends Basic_model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function create_update_oa_template($reqData, $adminId)
    {
      if (!empty($reqData['assessment_template'])) {
           $this->assessment_template_validation($reqData, $adminId);
           $title    =  $reqData['assessment_template']->{'title'};
           $job_type =  $reqData['assessment_template']->{'job_type'};
           $assessment_template = [
            'title' => $title ?? null,
            'job_type' => $reqData['assessment_template']->{'job_type'} ?? null,
            'location' => $reqData['assessment_template']->{'location'} ?? null,
            'status' => $reqData['assessment_template']->{'status'} ?? null,
            'archive' => 0,
            'created_by' => $adminId,
            'created_at' => DATE_TIME,
        ];
            if($reqData['id'] == 0)
            {
                $is_template_name_exist=$this->check_template_name_already_exist($title,$job_type);
                if(!empty($is_template_name_exist))
                {
                    return ["status" => false, "msg" => "Title already exists"];
                }
                $assessment_template_id = $this->basic_model->insert_records("recruitment_oa_template", $assessment_template);
            }
            else if($reqData['id'] > 0){
                 $archive_response = $this->archive_existing_template($reqData['id'], $adminId);
                if($archive_response['status']){
                   $assessment_template_id = $this->basic_model->insert_records("recruitment_oa_template", $assessment_template);
                 }
            }
           
            if (!empty($assessment_template_id)) {
                $response = $this->insert_oa_questions_and_options($reqData, $adminId, $assessment_template_id);
                if (!$response) {
                    $status = false;
                    $msg    = 'something went wrong';
                } else {
                    $status = true;
                    $msg    = $reqData['id'] == 0 ? 'Online Assessment Template Created successfully' :'Online Assessment Template Updated successfully';
                    $title  = $reqData['id'] == 0 ? 'Online Assessment Template Id :  ' .$assessment_template_id.'Created successfully':' Online Assessment Template Id :  '.$assessment_template_id. ' Updated successfully';
                    $this->create_log($title,$adminId,$reqData);
                }
                return ['status' => $status, 'msg' => $msg];
            }
            /**
           * @todo:need to get job_type from backend
           *   need to do field wise validation
           */
        }
        return ['status' => false, 'msg' => 'something went wrong'];
    }


    
     /*
     * its used to check  the sms template is already present or not
     * @param
     * $template_name,$template_id of the template
     * return type json
     *
     */
    function check_template_name_already_exist($title,$job_type,$template_id=0) {
        $this->db->select("id");
        $this->db->from("tbl_recruitment_oa_template as ro");
        $this->db->where("title", trim($title));
        $this->db->where("job_type", trim($job_type));
        $this->db->where('ro.archive', 0);
        if ($template_id > 0) {
            $this->db->where("id != ", $template_id);
        }
        return $this->db->get()->row();
    }
 
     /*
     * its used to insert questions and options 
     * @param
     * $reqData, $adminId, $assessment_template_id
     * return type json
     *
     */

    public function insert_oa_questions_and_options($reqData, $adminId, $assessment_template_id)
    {
       
        for ($i = 0; $i < count($reqData['question_answers_list']); $i++) {
            $assessment_questions = [
                'question' => $reqData['question_answers_list'][$i]->{'question'} ?? null,
                'answer_type' => $reqData['question_answers_list'][$i]->{'answer_type'} ?? null,
                'is_mandatory' => $reqData['question_answers_list'][$i]->{'is_mandatory'} ?? null,
                'grade' => $reqData['question_answers_list'][$i]->{'grade'} ?? null,
                'oa_template_id' => $assessment_template_id ?? null,
                'created_by' => $adminId,
                'created_at' => DATE_TIME,
            ];
            if($this->assessment_questions_validation($assessment_questions)){
                $assessment_question_id = $this->basic_model->insert_records("recruitment_oa_questions", $assessment_questions);
                if (!empty($assessment_question_id)) {
                    for ($j = 0; $j < count($reqData['question_answers_list'][$i]->{'options'}); $j++) {
                        $assessment_qa_options_id = [
                            'oa_template_id' => $assessment_template_id ?? null,
                            'question_id' => $assessment_question_id ?? null,
                            'option' => $reqData['question_answers_list'][$i]->{'options'}[$j]->{'option'} ?? null,
                            'is_correct' => $reqData['question_answers_list'][$i]->{'options'}[$j]->{'is_correct'} ?? null,
                            'created_by' => $adminId,
                            'created_at' => DATE_TIME,
                        ];
                        if( $reqData['question_answers_list'][$i]->{'answer_type'} >3 || $this->assessment_options_validation($assessment_qa_options_id)){
                        $assessment_qa_option_id = $this->basic_model->insert_records("recruitment_oa_answer_options", $assessment_qa_options_id);
                        }
                    }
                }
            }
        }
        return $assessment_qa_option_id;
    }
    
     /*
     * its used to get the existing template
     * @param
     * $reqData, $adminId,$retrieve
     * return type json
     *
     */
    public function get_oa_template($reqData, $adminId,$retrieve=FALSE)
    {
        $result_data = new stdClass();
        $template_id = $reqData['id'];
        $tbl_question_topic = TBL_PREFIX . 'recruitment_oa_template';
        $this->db->select('*');
        $this->db->from($tbl_question_topic);
        $where = $retrieve ? ['id' => $template_id] : ['archive' => 0 ,'id' => $template_id];
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->result()) {
            $result_data->assessment_template = $query->result()[0];
        } else {
            return ['data' => [], 'msg' => 'template is not available', 'status' => false];
        }
        if (count($query->result()) > 0) {
            $result_data->question_answers_list = [];
            $questions    = $this->get_oa_questions_by_template_id($reqData['id']);
            $options_list = $this->get_oa_question_options_by_template_id($reqData['id']);
            for ($i = 0; $i < count($questions); $i++) {
                $result_data->question_answers_list[$i] = $questions[$i];
                $option = $this->filter_option_by_question_id($options_list, $questions[$i]->{'id'});
                $result_data->question_answers_list[$i]->{'options'} = $option;
            }
        }

        return ['data' => $result_data, 'msg' => 'template retrieved successfully', 'status' => true];
    }

    /**
     * Get question list by template id exclude answer_type 5 (passage)
     * @param {int} $template_id
     */
    function get_oa_question_list_by_template_id($template_id)
    {
        $tbl_question_topic = TBL_PREFIX . 'recruitment_oa_questions as roaq';
        $this->db->select(['roaq.id', 'roaq.question', 'roaq.suggest_answer', 'roaq.answer_type', 'roaq.is_mandatory', 'roaq.parent_question_id', 'roaq.blank_question_type', 'roaq.fill_up_formatted_question as quesion_raw', 'roaq.grade', 'roaq.serial_no']);
        $this->db->select("(
            CASE 
                WHEN parent_question_id IS NOT NULL THEN ( SELECT question FROM tbl_recruitment_oa_questions WHERE id = roaq.parent_question_id)
            ELSE '' 
            END) as question_passage
        ");
        $this->db->select("(
            CASE 
                WHEN parent_question_id IS NOT NULL THEN ( SELECT answer_type FROM tbl_recruitment_oa_questions WHERE id = roaq.parent_question_id)
            ELSE '' 
            END) as parent_anwser_type
        ");
        $this->db->select("(
            CASE 
                WHEN parent_question_id IS NOT NULL AND parent_question_id != 0 THEN true
            ELSE false
            END) as is_passage
        ");
        $this->db->from($tbl_question_topic);
        $this->db->where(['roaq.oa_template_id' => $template_id]);
        $this->db->where(['roaq.answer_type !=' => 5]);
        $query = $this->db->get();
        return $query->result();
    }

     /*
     * its used to questions by template_id
     * @param
     * $template_id
     * return type json
     *
     */

    public function get_oa_questions_by_template_id($template_id)
    {
        $tbl_question_topic = TBL_PREFIX . 'recruitment_oa_questions';
        $this->db->select('*');
        $this->db->from($tbl_question_topic);
        $this->db->where(['oa_template_id' => $template_id ]);
        $query = $this->db->get();
        return $query->result();
    }

     /*
     * its used to options by template_id
     * @param
     * $template_id
     * return type json
     *
     */

    public function get_oa_question_options_by_template_id($template_id)
    {
        $tbl_question_topic = TBL_PREFIX . 'recruitment_oa_answer_options';
        $this->db->select('*');
        $this->db->from($tbl_question_topic);
        $this->db->where(['oa_template_id' => $template_id ]);
        $query = $this->db->get();
        return $query->result();
    }

    /*
     * its used to filter option by question_id
     * @param
     * $options_list, $question_id
     * return array
     *
     */
    public function filter_option_by_question_id($options_list, $question_id)
    {
        return array_values(
            array_filter(
                $options_list,
                function ($item) use (&$question_id) {
                    return $item->{'question_id'} == $question_id;
                },
                0
            )
        );
    }

     /*
     * its used to archive existing template
     * @param
     * template_id, $adminId
     * return array
     *
     */

    public function archive_existing_template($template_id, $adminId)
    {
        $data = array('archive' => 1);
        $where = array('id' => $template_id);
        $template_resp = $this->basic_model->update_records('recruitment_oa_template', $data, $where);
        $questions_resp = $this->basic_model->update_records('recruitment_oa_questions', $data, $where);
        $options_resp  = $this->basic_model->update_records('recruitment_oa_answer_options', $data, $where);
        if (empty($questions_resp) || empty($template_resp)|| empty($options_resp)) {
            return ['msg' => 'template is not available', 'status' => false];
        } else {
            $title="Online Assessment Template Id: ".$template_id. " Archived Successfully";
            $this->create_log($title,$adminId,['id'=>$template_id]);
            return ['msg' => 'template archived successfully', 'status' => true];
        }
    }

      /*
     * its used to create log
     * @param
     * $title,$adminId,$data
     * return array
     *
     */
    public function create_log($title,$adminId,$data)
    {
        $this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle($title." by " . $adminName);
        $this->loges->setModule(9);
        $this->loges->createLog();
    } 

     /*
     * its used to do validate assessment template before insertion into db
     * @param
     * $reqData,$adminId,$backend_validation
     * return array
     *
     */
    public function assessment_template_validation($reqData,$adminId,$backend_validation=TRUE)
    {
        $title    =  $reqData['assessment_template']->{'title'};
        $job_type =  $reqData['assessment_template']->{'job_type'};
         $this->load->library('form_validation');
         $this->form_validation->reset_validation();
         
        $assessment_template = [
            'title' => $title ?? null,
            'job_type' => $reqData['assessment_template']->{'job_type'} ?? null,
            'location' => $reqData['assessment_template']->{'location'} ?? null,
            'status' => $reqData['assessment_template']->{'status'} ?? null,
            'archive' => 0,
            'created_by' => $adminId,
            'created_at' => DATE_TIME,
        ];

        $validation_rules = array(
            array('field' => 'title',   'label' => 'Title', 'rules' => 'required'),
            array('field' => 'job_type', 'label' => 'Job Type', 'rules' => 'required'),
            array('field' => 'location', 'label' => 'Location', 'rules' => 'required'),
            array('field' => 'status', 'label' => 'Status', 'rules' => 'required'),
            );
           
        $this->form_validation->set_data($assessment_template);
        $this->form_validation->set_rules($validation_rules);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return array('status' => false, 'error' => implode(', ', $errors));
        }
   
       
    }
   /*
     * its used to do validate assessment questions before insertion into db
     * @param
     * $assessment_template
     * return array
     *
     */
    public function assessment_questions_validation($assessment_template){
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $validation_rules = array(
            array('field' => 'question',   'label' => 'Question', 'rules' => 'required'),
            array('field' => 'answer_type', 'label' => 'Answer Type', 'rules' => 'required'),
            array('field' => 'is_mandatory', 'label' => 'Mandatory', 'rules' => 'required'),
            array('field' => 'grade', 'label' => 'Status', 'rules' => 'required'),
            array('field' => 'oa_template_id', 'label' => 'Template Id', 'rules' => 'required')
            );
        $this->form_validation->set_data($assessment_template);
        $this->form_validation->set_rules($validation_rules);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return array('status' => false, 'error' => implode(', ', $errors));
        }
        return true;
    }

    /*
     * its used to do validate assessment options before insertion into db
     * @param
     * $assessment_template
     * return array
     *
     */

    public function assessment_options_validation($assessment_template){
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $validation_rules = array(
            array('field' => 'oa_template_id',   'label' => 'Template Id', 'rules' => 'required'),
            array('field' => 'question_id', 'label' => 'Question Id', 'rules' => 'required'),
            array('field' => 'is_correct', 'label' => 'Correct Answer', 'rules' => 'required'),
            array('field' => 'option', 'label' => 'Option', 'rules' => 'required')
            );
        $this->form_validation->set_data($assessment_template);
        $this->form_validation->set_rules($validation_rules);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return array('status' => false, 'error' => implode(', ', $errors));
        }
        return true;
    }

    /*
     * its used to get assessment result by id
     * @param
     *$assessment_id,$application_id
     * return array
     *
     */
    public function get_assessment_result_by_id($assessment_id,$application_id,$adminId){

        $result_data = new stdClass();
        $assessment_details = $this->get_assessment_details_by_id($application_id,$assessment_id,$adminId);
        $result_data->assessment_details = $assessment_details;
        $template_id = $assessment_details['template_id'];
        $tbl_question_topic = TBL_PREFIX . 'recruitment_oa_template';
        $this->db->select('*');
        $this->db->from($tbl_question_topic);
        /**
         * @todo:need to log assement_template view,
         * need to do db escape to avoid sql injection
         */
        $where = ['id' => $template_id];
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->result()) {
            $result_data->assessment_template = $query->result()[0];
        } else {
            return ['data' => [], 'msg' => 'template is not available', 'status' => false];
        }
        if (count($query->result()) > 0) {
            $result_data->question_answers_list = [];
            $questions    = $this->get_oa_questions_by_template_id($template_id);
            $options_list = $this->get_oa_question_options_by_template_id($template_id);
            for ($i = 0; $i < count($questions); $i++) {
                $result_data->question_answers_list[$i] = $questions[$i];
                $applicant_answer = $this->get_question_answer_by_id($assessment_id,$questions[$i]->{'id'},$questions[$i]->{'answer_type'},$questions[$i]->{'blank_question_type'});

                $is_correct_result = NULL;
                $grade = NULL;
                $questions[$i]->{'not_answered'} = false;
                if(empty($applicant_answer))
                {
                  $is_correct_result = 0;
                  $grade = 0;
                  $questions[$i]->{'not_answered'} = true;
                }
               
                # Evalute result for blank - select option
                if (!empty($applicant_answer) && $questions[$i]->{'answer_type'} == 6 && $questions[$i]->{'blank_question_type'} == 2) {
                    $is_correct_arr = json_decode($applicant_answer['is_correct']);
                    $answer_legth = count($is_correct_arr);
                    $repeatedCount = array_count_values($is_correct_arr);
                    # 0 - incorrect / 1 - correct / 2 - partial
                    if (!empty($repeatedCount[1]) && $answer_legth == $repeatedCount[1]) {
                        $is_correct_result = 1;
                    } else if (!empty($repeatedCount[0]) && $answer_legth == $repeatedCount[0]) {
                        $is_correct_result = 0;
                    } else {
                        $is_correct_result = 2;
                    }
                } else if (!empty($applicant_answer['is_correct']) && $applicant_answer['is_correct'] > 0) {
                    $is_correct_result =  $applicant_answer['is_correct'];
                } else if (isset($applicant_answer['is_correct']) && $applicant_answer['is_correct'] == 0 && $applicant_answer['is_correct'] != NULL) {
                    $is_correct_result = 0;
                }

                $questions[$i]->{'result'} =  $is_correct_result;
                
                if (isset($applicant_answer['grade'])) {
                    $grade = $applicant_answer['grade'];
                }

                $questions[$i]->{'score'} = $grade;
                if( $questions[$i]->{'answer_type'}==4 )
                {
                    $questions[$i]->{'answer'} = $applicant_answer['answer_text'] ?? '';
                    $questions[$i]->{'comments'} = $applicant_answer['recruiter_comments'] ?? '';
                }
                else if(!empty($applicant_answer)&& $questions[$i]->{'answer_type'}==7 )
                {
                    $questions[$i]->{'answer'} = $applicant_answer['answer_text'] ?? '';
                    $questions[$i]->{'result'} =  $applicant_answer['is_correct'];
                }
                else if( $questions[$i]->{'answer_type'} == 6)
                {
                    $question_txt_raw = $questions[$i]->fill_up_formatted_question ?? '';
                    if (intVal($questions[$i]->blank_question_type) === 2) {                        
                        $option_count = substr_count($question_txt_raw, '{{SELECT_OPTION}}');
                    } else if (intVal($questions[$i]->blank_question_type) === 1) {
                        $option_count = substr_count($question_txt_raw, '{{INTPUT_OPTION}}');
                    } else {
                        $option_count = 0;
                    }
                    $questions[$i]->{'answer'} = [];
                    if (!empty($applicant_answer)) {
                        $questions[$i]->{'answer'} =  $questions[$i]->{'blank_question_type'}==2 ? $applicant_answer['answer_id'] : $applicant_answer['answer_text'];
                    } else {
                        $empty_arr = $questions[$i]->{'blank_question_type'}==2 ? array_fill(0, $option_count, 0) : array_fill(0, $option_count, '');
                        $questions[$i]->{'answer'} = json_encode($empty_arr); 
                    }
                    $questions[$i]->{'blank_question_position'} = $applicant_answer['blank_question_position'] ?? '';
                    $questions[$i]->{'comments'} = $applicant_answer['recruiter_comments'] ?? '';         
                    if($questions[$i]->{'blank_question_type'}==2){
                        if (empty($applicant_answer)) {
                            $questions[$i]->{'score'} = 0;
                            $questions[$i]->{'result'} = 0;
                        }
                        $questions[$i]->{'fill_up_answers'} = $this->form_fillup_answer_object($applicant_answer,$options_list, $option_count);
                    }
                }
                else{
                    $questions[$i]->{'answer'} = $applicant_answer['answer_id'] ?? '';
                } 
                $option =array();
                if($questions[$i]->{'answer_type'} != 5 )
                {
                    $option = $this->filter_option_by_question_id($options_list, $questions[$i]->{'id'});
                }
                
               
                  /**
                * @todo:after the completion of assessment submission card it will be removed added for testing purpose
                 */
                for($j = 0; $j < count($option); $j++)
                {
                  if($questions[$i]->{'answer_type'}==1 ){
                    if (!empty($applicant_answer['answer_id'])) {
                        $option[$j]->is_selected = in_array($option[$j]->{'id'},json_decode($applicant_answer['answer_id']));
                    } else {
                        $option[$j]->is_selected = false;
                    }                    
                  }else{
                    $is_selected = false;
                    if (!empty($applicant_answer['answer_id']) && $option[$j]->{'id'} == $applicant_answer['answer_id']) {
                        $is_selected = true;
                    }
                    $option[$j]->is_selected = $is_selected;
                  }
                    
                }
                $result_data->question_answers_list[$i]->{'options'} = $option;
            }
        }

        return ['data' => $result_data, 'msg' => 'template retrieved successfully', 'status' => true];

    }

    /*
     * its used to get assessment result by id
     * @param
     *$assessment_id,$application_id
     * return array
     *
     */
    public function get_assessment_result_by_id_print($assessment_id,$application_id,$adminId){

        $result_data = new stdClass();
        $assessment_details = $this->get_assessment_details_by_id($application_id,$assessment_id,$adminId);
        $result_data->assessment_details = $assessment_details;
        $template_id = $assessment_details['template_id'];
        $tbl_question_topic = TBL_PREFIX . 'recruitment_oa_template';
        $this->db->select('*');
        $this->db->from($tbl_question_topic);
        /**
         * @todo:need to log assement_template view,
         * need to do db escape to avoid sql injection
         */
        $where = ['id' => $template_id];
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->result()) {
            $result_data->assessment_template = $query->result()[0];
        } else {
            return ['data' => [], 'msg' => 'template is not available', 'status' => false];
        }
        if (count($query->result()) > 0) {
            $result_data->question_answers_list = [];
            $questions    = $this->get_oa_question_list_by_template_id($template_id);
            $options_list = $this->get_oa_question_options_by_template_id($template_id);
            $question_list = [];

            for ($i = 0; $i < count($questions); $i++) {
                $question_id = $questions[$i]->{'id'};

                $result_data->question_answers_list[$i] = $questions[$i];
                $applicant_answer = $this->get_question_answer_by_id($assessment_id,$questions[$i]->{'id'},$questions[$i]->{'answer_type'},$questions[$i]->{'blank_question_type'});

                $is_correct_result = NULL;
                # Evalute result for blank - select option
                if (!empty($applicant_answer) && $questions[$i]->{'answer_type'} == 6 && $questions[$i]->{'blank_question_type'} == 2) {
                    $is_correct_arr = json_decode($applicant_answer['is_correct']);
                    $answer_legth = count($is_correct_arr);
                    $repeatedCount = array_count_values($is_correct_arr);
                    # 0 - incorrect / 1 - correct / 2 - partial
                    if (!empty($repeatedCount[1]) && $answer_legth == $repeatedCount[1]) {
                        $is_correct_result = 1;
                    } else if (!empty($repeatedCount[0]) && $answer_legth == $repeatedCount[0]) {
                        $is_correct_result = 0;
                    } else {
                        $is_correct_result = 2;
                    }
                } else if (!empty($applicant_answer['is_correct']) && $applicant_answer['is_correct'] > 0) {
                    $is_correct_result = $applicant_answer['is_correct'];
                } else if (isset($applicant_answer['is_correct']) && $applicant_answer['is_correct'] == 0 && $applicant_answer['is_correct'] != NULL) {
                    $is_correct_result = 0;
                }

                $questions[$i]->{'result'} =  $is_correct_result;
                $grade = NULL;
                if (isset($applicant_answer['grade'])) {
                    $grade = $applicant_answer['grade'];
                }

                $questions[$i]->{'score'} = $grade;
                
                if (empty($applicant_answer) && $questions[$i]->{'answer_type'} == 6 && $questions[$i]->{'blank_question_type'} == 2) {
                    $questions[$i]->{'result'} = 0;
                    $questions[$i]->{'score'} = 0;
                }

                $questions[$i]->{'answer_array'} = [];
                if( $questions[$i]->{'answer_type'}==4 || $questions[$i]->{'answer_type'}==7)
                {
                    $questions[$i]->{'answer'} = $applicant_answer['answer_text'] ?? '';
                    $questions[$i]->{'comments'} = $applicant_answer['recruiter_comments'] ?? '';
                } else if (!empty($applicant_answer) && $questions[$i]->{'answer_type'} == 6) {
                    $questions[$i]->{'answer'} = $applicant_answer['answer_id'] ?? '';
                    if ($questions[$i]->{'blank_question_type'} == 2) {
                        $questions[$i]->{'answer_array'} = json_decode($applicant_answer['answer_id']) ?? '';
                    } else {
                        $questions[$i]->{'answer_array'} = json_decode($applicant_answer['answer_text']) ?? '';
                    }
                    $questions[$i]->{'blank_question_position'} = json_decode($applicant_answer['blank_question_position']) ?? '';
                } else {
                    $questions[$i]->{'answer'} =  isset($applicant_answer['answer_id']) ? $applicant_answer['answer_id'] : NULL;
                }
                $option = $this->filter_option_by_question_id($options_list, $questions[$i]->{'id'});

                  /**
                * @todo:after the completion of assessment submission card it will be removed added for testing purpose
                 */
                for($j = 0; $j < count($option); $j++)
                {
                  if($questions[$i]->{'answer_type'}==1){
                    if (!empty($applicant_answer['answer_id'])) {
                        $option[$j]->is_selected = in_array($option[$j]->{'id'},json_decode($applicant_answer['answer_id']));
                    } else {
                        $option[$j]->is_selected = false;
                    }                    
                  }else{
                    $is_selected = false;
                    if (!empty($applicant_answer['answer_id']) && $option[$j]->{'id'} == $applicant_answer['answer_id']) {
                        $is_selected = true;
                    }
                    $option[$j]->is_selected = $is_selected;
                  }
                    
                }
                $result_data->question_answers_list[$i]->{'options'} = $option;

                if (intVal($questions[$i]->{'is_passage'}) === 1 && !empty($questions[$i]->{'parent_question_id'}) && $questions[$i]->{'parent_question_id'} != 0) {
                    $parent_question_id = $questions[$i]->{'parent_question_id'};
                    if (array_key_exists($parent_question_id, $question_list) === false) {
                        $question_list[$parent_question_id]['question_passage'] = $questions[$i]->{'question_passage'};
                        $question_list[$parent_question_id]['is_passage'] = '1';
                        $question_list[$parent_question_id]['questions'][] = $result_data->question_answers_list[$i];  
                    } else {
                        $question_list[$question_id] = $result_data->question_answers_list[$i];
                        $question_list[$question_id]->{'is_passage'} = '0';
                    }
                } else {
                    $question_list[$question_id] = $result_data->question_answers_list[$i];
                }
            }
            $question_list = array_values($question_list);
            $result_data->question_answers_list =(object) $question_list;
        }

        return ['data' => $result_data, 'msg' => 'template retrieved successfully', 'status' => true];

    }

     /**
     * /*
     * its used to get assessment details by id
     * @param
     *$assessment_id,$application_id
     * return array
     *
     */
    function get_assessment_details_by_id($application_id,$assessment_id,$adminId) {
        $this->load->library('UserName');
        $tbl = TBL_PREFIX . 'recruitment_job_assessment';
        $this->db->select('*');
        $this->db->from($tbl);
        $this->db->where(['id' => $assessment_id ,'application_id' => $application_id]);
        $query  = $this->db->get();
        $result = $query->row_array();
        $result['applicant_name'] = $this->username->getName('applicant', $result['applicant_id']);
        $result['admin_name'] = $this->username->getName('admin', $adminId);
        $response = $this->get_job_details_by_id($result['job_id']);
        $result['sub_category_name'] = $response['sub_category_name'];
        $result['title'] = $response['title'];
        
        $result['duration'] = "0 Min";

        if(!empty($result['completed_date_time']) && !empty($result['start_date_time'])) {
            $to_time = date('y-m-d H:i', strtotime($result['completed_date_time']));
            $from_time = date('y-m-d H:i', strtotime($result['start_date_time']));

            $result['duration'] = (integer) abs(strtotime($to_time) - strtotime($from_time)) / 60 . " Min";
        }

        return $result;
    }

     
      /*
     * its used to get assessment details by id
     * @param
     * $assessment_id,$application_id
     * return array
     *
     */
    function get_question_answer_by_id($assessment_id,$question_id,$question_type,$blank_question_type=0) {
        $tbl = TBL_PREFIX . 'recruitment_oa_applicant_answer';
        $this->db->select('*');
        $this->db->from($tbl);
        $this->db->where(['job_assessment_id' => $assessment_id ,'question_id' => $question_id]);
        $query = $this->db->get();
       /*  if($question_type == 6 && $blank_question_type == 2){
            return $query->result();
        }  */
       
        return $query->row_array();
    }
  
       /*
     * its used to get job details by id
     *  @param
     *$job_id
     * return array
     *
     */

    function get_job_details_by_id($job_id){
        $job_details= $this->get_job_sub_category_by_id($job_id);
        if(count($job_details)) {
            $tbl = TBL_PREFIX . 'recruitment_job_category';
            $this->db->select('name');
            $this->db->from($tbl);
            $this->db->where(['id' => $job_details['sub_category']]);
            $query = $this->db->get();
            $result = $query->row_array();
            return [ 'sub_category_name' => $result['name'] , 'title' => $job_details['title'] ];
        }
        return [ 'sub_category_name' => '' , 'title' => '' ];
    }
    
       /*
     * its used to get job sub category and title by id
     *  @param
     *$job_id
     * return array
     *
     */
    function get_job_sub_category_by_id($job_id){
        $tbl = TBL_PREFIX . 'recruitment_job';
        $this->db->select(['sub_category','title']);
        $this->db->from($tbl);
        $this->db->where(['id' => $job_id]);
        $query = $this->db->get();
        $result = $query->row_array();
        /**
         * @todo:need to ask sindhu about header section
         * title or job sub category
         * what is meant by batch if needed means need to work 
         */
        if(isset($result['sub_category']) && isset($result['title'])) {
            return [ 'sub_category' => $result['sub_category'] , 'title' => $result['title']];
        }
        return [];
    }
    /**
     * will change the status code tbl_recruitment_oa_template
     */
 
    public function change_oa_template_status($template_id, $status,$adminId)
    {
        $data = array('status' => $status);
        $where = array('id' => $template_id);
        $template_resp = $this->basic_model->update_records('recruitment_oa_template', $data, $where);
        if (empty($template_resp)) {
            return ['msg' => 'template is not available', 'status' => false];
        } else {
            $status =  1 ? 'Active':'Inactive';
            $title="Online Assessment Template Id: ".$template_id. " Changed To " .$status.  " Status Successfully";
            $this->create_log($title,$adminId,['id'=>$template_id]);
            return ['msg' => 'template status changed successfully', 'status' => true];
        }
    }


    
    /*
     * its used to filter answers of fillup by question_id
     * @param
     * $options_list, $question_id
     * return array
     *
     */
   public function form_fillup_answer_object($applicant_answer,$options_list, $option_count = 0){
        $result_data =  array();         
        if (!empty($applicant_answer)) {
            $applicant_answer =(object)$applicant_answer;
            $answer_id_arr = json_decode($applicant_answer->{'answer_id'});
            $evaluvate_count = count($answer_id_arr);
        } else {
            $evaluvate_count = $option_count;
        }

        for($j = 0; $j < $evaluvate_count; $j++)
        {          
          $option_value = NULL;
          $is_correct = 0;
          if(isset($answer_id_arr) && $answer_id_arr[$j] > 0 ){
            $option = $this->filter_option_by_option_id($options_list, $answer_id_arr[$j]);
            $option_value = $option[0]->{'option'};
            $is_correct   =  $option[0]->{'is_correct'};
          }
          $result_data[$j] = new stdClass();
          $result_data[$j]->{'option'}      =  $option_value;
          $result_data[$j]->{'is_correct'}  =  $is_correct;
        }
      return $result_data;
   }


       /*
     * its used to filter option by question_id
     * @param
     * $options_list, $question_id
     * return array
     *
     */
    public function filter_option_by_option_id($options_list, $option_id)
    {
        return array_values(
            array_filter(
                $options_list,
                function ($item) use (&$option_id) {
                    return $item->{'id'} == $option_id;
                },
                0
            )
        );
    }
   
}
