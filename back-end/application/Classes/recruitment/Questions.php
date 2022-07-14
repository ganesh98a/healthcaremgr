<?php
namespace QuestionsClass; 

if (!defined("BASEPATH")) exit("No direct script access allowed");



class Questions
{
    public $CI;
    function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->model('Recruitment_model');
		$this->CI->load->model('Recruitment_question_model');        
    }
   private $id;
   private $question;
   private $status;
   private $created;
   private $created_by;
   private $answer_a;
   private $answer_b;
   private $answer_c;
   private $answer_d;
   private $answer=array();
   private $question_type;
   private $question_topic;
   private $training_category;
   private $form_id;
   private $is_answer_optional;
   private $is_required;

	function setId($id) { $this->id = $id; }
    function getId() { return $this->id; }
	
	function setQuestion($question) { $this->question = $question; }
    function getQuestion() { return $this->question; }
	
	function setStatus($status) { $this->status = $status; }
    function getStatus() { return $this->status; }
	
	function setCreated($created) { $this->created = $created; }
    function getCreated() { return $this->created; }
	
	function setCreated_by($created_by) { $this->created_by = $created_by; }
    function getCreated_by() { return $this->created_by; }
	
	function setAnswer_A($answer_a) { $this->answer_a = $answer_a; }
    function getAnswer_A() { return $this->answer_a; }
	
	function setAnswer_B($answer_b) { $this->answer_b = $answer_b; }
    function getAnswer_B() { return $this->answer_b; }
	
	function setAnswer_C($answer_c) { $this->answer_c = $answer_c; }
    function getAnswer_C() { return $this->answer_c; }
	
	function setAnswer_D($answer_d) { $this->answer_d = $answer_d; }
    function getAnswer_D() { return $this->answer_d; }
	
	function setAnswer($answer) { $this->answer = $answer; }
    function getAnswer() { return $this->answer; }
	
	function setQuestion_Type($question_type) { $this->question_type = $question_type; }
    function getQuestion_Type() { return $this->question_type; }
	
	function setQuestionTopic($question_topic) { $this->question_topic = $question_topic; }
    function getQuestionTopic() { return $this->question_topic; }
	
	function setTrainingCategory($training_category) { $this->training_category = $training_category; }
    function getTrainingCategory() { return $this->training_category; }

    function setFormId($form_id) { $this->form_id = $form_id; }
    function getFormId() { return $this->form_id; }

    function setAnswerOptional($is_answer_optional) { $this->is_answer_optional = $is_answer_optional; }
    function getAnswerOptional() { return $this->is_answer_optional; }


    function setIsRequired($is_required) { $this->is_required = $is_required; }
    function getIsRequired() { return $this->is_required; }
	
    /* Get functions */	
	public function recruitment_topic_list(){
        return $this->CI->Recruitment_model->Recruitment_topic_list();
    }
	
	/* insert functions */
	public function create_Questions(){
        return $this->CI->Recruitment_question_model->create_Questions($this);
    }
	
	public function update_Question(){
		return $this->CI->Recruitment_question_model->update_Question($this);
	}
	
	public function delete_Question(){
		return $this->CI->Recruitment_question_model->delete_Questions($this);
	}
	
	public function Questions_list(){
        return $this->CI->Recruitment_question_model->question_list($this);
    }
	
	
	
}
