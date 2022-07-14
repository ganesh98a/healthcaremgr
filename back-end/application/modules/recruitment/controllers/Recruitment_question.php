<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Recruitment_question extends MX_Controller
{
    const QUESTION_CATEGORY_GROUP_INTERVIEW = 1;
    const QUESTION_CATEGORY_CAB_DAY = 2;
    const QUESTION_CATEGORY_REFERENCE_CHECK = 3;
    const QUESTION_CATEGORY_PHONE_INTERVIEW = 4;
    const QUESTION_CATEGORY_JOB_QUESTIONS = 5;

    function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Recruitment_question_model');
        $this->load->model('Basic_model');
        $this->form_validation->CI = &$this;
        $this->loges->setModule(2);
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

    public function insert_update_question()
    {
        $reqData = $log_data = request_handler('access_recruitment');
        if (!empty($reqData->data)) {
            #pr($reqData->data);die;
            $admin_id = $reqData->adminId;
            $reqData =  $reqData->data;
            $data =(array) $reqData;

            #HCM-433 is implemeted in callback
            $validation_rules = array(
                array('field' => 'question', 'label' => 'Question', 'rules' => 'required'),
                array('field' => 'question_category', 'label' => 'Training Category', 'rules' => 'required'),
                array('field' => 'question_status', 'label' => 'Question Status', 'rules' => 'required'),
                array('field' => 'answer_type', 'label' => 'Answer type', 'rules' => 'required'),
                array('field' => 'phone', 'label' => 'Phone', 'rules' => 'callback_check_question_data[' . json_encode($reqData) . ']'),  
                array('field' => 'answers', 'label' => 'Answer option', 'rules' => 'callback_check_answer_data['.json_encode($reqData->answers).']'),
                array('field' => 'form_id', 'label' => 'Forn name', 'rules' => 'required'),  
            );

            $temp = [];
            if(isset($data['question_category']) && ($data['question_category'] !=3 && $data['question_category'] !=4 && $data['question_category'] != self::QUESTION_CATEGORY_JOB_QUESTIONS))
            {
                $temp = array(
                    array('field' => 'question_topic', 'label' => 'Question Topic', 'rules' => 'required'),
                );
            }

            $validation_rules = array_merge($temp,$validation_rules);
            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                require_once APPPATH . 'Classes/recruitment/Questions.php';
                $objQuestions = new QuestionsClass\Questions();

                $other_msg = '';
                if ($reqData->question_status == 2)
                    $other_msg = 'But the question is not active, it will not be used in assessments, please change the status to ‘Active’ for it to be used.';

                if (isset($reqData->question_id) && $reqData->question_id > 0) {
//                    $count =  $this->Recruitment_question_model->get_applican_count($reqData->question_id);
//                    if ($count > 0) {
//                        $objQuestions->setId(0);
//                        $this->Basic_model->update_records('recruitment_additional_questions', array('status' => 2), array('id' => $reqData->question_id));
//                    } else {
//                        $objQuestions->setId($reqData->question_id);
//                    }
                    
                     $objQuestions->setId($reqData->question_id);
                    $msg = 'Question is updated successfully.' . $other_msg;
                    $log_title = 'Question is updated successfully, question id : '.$reqData->question_id;
                } else {
                    $objQuestions->setId(0);
                    $msg = 'New Question is submitted successfully.' . $other_msg;
                    $log_title = 'New Question is submitted successfully, question id : ';
                }

                $objQuestions->setQuestion($reqData->question);
                $objQuestions->setStatus($reqData->question_status);
                $objQuestions->setCreated(DATE_TIME);
                $objQuestions->setCreated_by($admin_id);

                if($reqData->question_category!=3 && $reqData->question_category!=4){
                    $objQuestions->setQuestionTopic($reqData->question_topic);
                }

                $objQuestions->setFormId($reqData->form_id);
                $objQuestions->setAnswerOptional($reqData->is_answer_optional);
                $objQuestions->setIsRequired($reqData->is_required);
                $objQuestions->setTrainingCategory($reqData->question_category);
                $objQuestions->setQuestion_Type($reqData->answer_type);
                $objQuestions->setAnswer($reqData->answers);
                $response = $objQuestions->create_Questions();

                if($response){
                    $log_title = $log_title.$response;
                }
                $this->loges->setLogType('recruitment_question');
                $this->loges->setTitle($log_title);
                $this->loges->setUserId($admin_id);
                $this->loges->setCreatedBy($admin_id);
                $this->loges->setDescription(json_encode($log_data));
                $this->loges->createLog();

                $return = array('status' => true, 'msg' => $msg);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
        }
    }

    public function check_answer_data($data, $questionData)
    {
        if ($questionData != '') {
            $questionData = json_decode($questionData);
            $a = [];
            foreach ($questionData as $key => $opt) {
                if($opt->value!='')
                   $a[] = $opt->value;
           }
           if (!empty($a) && count($a) !== count(array_unique($a))) {
            $this->form_validation->set_message('check_answer_data', 'Please write distinct answer option.');
            return false;
        } else {
            return true;
        }
    }
}

public function update_question()
{
    $reqData = request_handler();
    if (!empty($reqData->data)) {
        $reqData = $reqData->data;
        require_once APPPATH . 'Classes/recruitment/Questions.php';
        $objQuestions = new QuestionsClass\Questions();

        $objQuestions->setId(3);
        $objQuestions->setQuestion($reqData->question);
        $objQuestions->setStatus($reqData->question_status);
        $objQuestions->setCreated(DATE_TIME);
        $objQuestions->setCreated_by(1);
        $objQuestions->setQuestionTopic($reqData->question_topic);
        $objQuestions->setTrainingCategory($reqData->question_category);
        $objQuestions->setQuestion_Type($reqData->answer_type);
        $anser_array = array();
        foreach ($reqData->answers as $answer) {
            if ($answer->type == 'answer_a') {
                $objQuestions->setAnswer_A($answer->value);
            }
            if ($answer->type == 'answer_b') {
                $objQuestions->setAnswer_B($answer->value);
            }
            if ($answer->type == 'answer_c') {
                $objQuestions->setAnswer_C($answer->value);
            }
            if ($answer->type == 'answer_d') {
                $objQuestions->setAnswer_D($answer->value);
            }

            if ($answer->checked) {
                $anser_array[] = $answer->lebel;
            }
        }
        $objQuestions->setAnswer($anser_array);
        $response = $objQuestions->update_Questions();

        echo json_encode(array('status' => true, 'data' => $response));
    } else {
        echo json_encode(array('status' => false));
    }
}

public function delete_Question()
{
    $reqData = request_handler('access_recruitment');
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

public function get_recruitment_topic_list()
{
    require_once APPPATH . 'Classes/recruitment/Recruitment.php';
    $objRequirment = new RecruitmentClass\Recruitment();
    $requirment = $objRequirment->recruitment_topic_list();
    $result = $this->Recruitment_question_model->get_interview_type();
    echo json_encode(array('status' => true, 'data' => $requirment,'interview_type_data'=>$result));
}

public function get_questions_list()
{
    $reqData = request_handler('access_recruitment');
    if (!empty($reqData->data)) {
        $result = $this->Recruitment_question_model->get_questions_list($reqData->data);
        echo json_encode($result);
    }
}

public function check_question_data($data, $questionData)
{
    $question_Data = json_decode($questionData);
        #pr($question_Data->answers);
    if (!empty($question_Data)) 
    {

        if ($question_Data->answer_type == 1) {
            $cntAnswer = 0;
            foreach ($question_Data->answers as $answer) {
                if($answer->checked && $answer->value==''){
                  $this->form_validation->set_message('check_question_data', 'You can\'t select blank answer option to be a correct answer.');
                  return false;  
              }

              if ($answer->checked) {
                $cntAnswer++;
            }
        }
        if($cntAnswer <= 0 && !$question_Data->is_answer_optional){
            $this->form_validation->set_message('check_question_data', 'Please select answer for the question.');
            return false;
        }
    } else if ($question_Data->answer_type == 2 || $question_Data->answer_type == 3) {
        $cntAnswer = 0;
        foreach ($question_Data->answers as $answer) {

            if($answer->checked && $answer->value==''){
              $this->form_validation->set_message('check_question_data', 'You can\'t select blank answer option to be a correct answer.');
              return false;  
          }

          if ($answer->checked) {
            $cntAnswer++;
        }
    }

    if($cntAnswer <= 0 && !$question_Data->is_answer_optional){
        $this->form_validation->set_message('check_question_data', 'Please select answer for the question.');
        return false;
    }
}
} else {
    $this->form_validation->set_message('check_question_data', 'Please select Correct answer.');
    return false;
}
}

public function get_question_detail()
{
    $reqData = $reqData1 = request_handler('access_recruitment');
    if (!empty($reqData->data)) {
        $reqData = $reqData->data;
        $result = $this->Recruitment_question_model->get_question_detail($reqData);
        echo json_encode($result);
    }
}

public function update_question_status()
{
    $reqData = $reqData1 = request_handler('access_recruitment');
    if (!empty($reqData)) {
        $reqData = $reqData->data;
        $question_id = $reqData->queId;
        $updated_val = $reqData->updatedVal;
        $status_str = ($updated_val == 1)?'Active':'In-active';
        $this->loges->setLogType('recruitment_question');
        $this->loges->setTitle('Status of Question id '.$question_id.' is changed to '.$status_str);
        $this->loges->setUserId($reqData1->adminId);
        $this->loges->setCreatedBy($reqData1->adminId);
        $this->loges->setDescription(json_encode($reqData1));
        $this->loges->createLog();

        $id = $this->Basic_model->update_records('recruitment_additional_questions', array('status' => $updated_val), array('id' => $question_id));
        if ($id) {
            echo json_encode(array('status' => true));
            exit();
        } else {
            echo json_encode(array('status' => false));
            exit();
        }
    }
}

function get_form_option_for_filter()
{
    $reqData = request_handler('access_recruitment');
    if ($reqData->data) {
        $category = $reqData->data->category ?? '';
        $res["question_fliter_option"] = $this->Recruitment_question_model->get_question_type_for_filter();

			//$res["question_topic_fliter_option"] = $this->Recruitment_question_model->get_question_topic_filter_option();

        $filter_x = $this->Recruitment_question_model->get_form_option_for_filter();

        $form_option = [];
        if(!empty($res["question_fliter_option"])){
            foreach($res["question_fliter_option"] as $val){
                $form_option[$val->value] = $filter_x[$val->value] ?? [];
            }
        }

        $res["form_fliter_option"] = $form_option;

    }

    echo json_encode(["status" => true, "data" => $res]);
}

function update_display_order_question()
{
    $reqData = request_handler('access_recruitment');

    if ($reqData->data) {
        $this->form_validation->set_data((array) $reqData->data);

        $validation_rules = array(
            array('field' => 'questionId', 'label' => 'question id', 'rules' => 'required'),
            array('field' => 'order', 'label' => 'order', 'rules' => 'required'),
        );

            // set rules form validation
        $this->form_validation->set_rules($validation_rules);

        if ($this->form_validation->run()) {
            $questionId = ($reqData->data->questionId);
            $this->loges->setLogType('recruitment_question');
            $this->loges->setTitle('Display order Question id '.$questionId.' is updated to.');
            $this->loges->setUserId($reqData->adminId);
            $this->loges->setCreatedBy($reqData->adminId);
            $this->loges->setDescription(json_encode($reqData));
            $this->loges->createLog();

            $this->Recruitment_question_model->update_display_order_question($reqData->data);
            $response = array('status' => true);
        } else {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
        }

        echo json_encode($response);
    }
}

public function get_form_option() {
    $reqData = request_handler('access_recruitment');
    $result = [];
    if(!empty($reqData->data)){
        $category_id = $reqData->data->category_id;
        $result = $this->Recruitment_question_model->get_form_option($category_id);
    }
    echo json_encode(array('status' => true, 'data' => $result));
}
 public function create_update_assessment_template()
  {
    $reqData = $log_data = request_handler('access_recruitment');
    if (!empty($reqData->data)) {
        pr($reqData->data);
    }
}
}
