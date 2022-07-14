<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Recruitment_question_model extends CI_Model
{

    public function __construct()
    {
        // Call the CI_Model constructor
        parent::__construct();
    }

    public function create_Questions($objQuestion)
    {
        $tbl_question = TBL_PREFIX . 'recruitment_additional_questions';
        $arrQuestions = array();
        $arrQuestions['question'] = $objQuestion->getQuestion();
        $arrQuestions['status'] = $objQuestion->getStatus();
        $arrQuestions['created_by'] = $objQuestion->getCreated_by();

        if($objQuestion->getTrainingCategory()!=3 && $objQuestion->getTrainingCategory()!=4){
            $arrQuestions['question_topic'] = $objQuestion->getQuestionTopic();
        }else{
            $arrQuestions['question_topic'] = 0;
        }

        $arrQuestions['form_id'] = $objQuestion->getFormId();
        $arrQuestions['question_type'] = $objQuestion->getQuestion_Type();
        $arrQuestions['training_category'] = $objQuestion->getTrainingCategory();
        $arrQuestions['updated'] = $objQuestion->getCreated();
        $arrQuestions['is_answer_optional'] = $objQuestion->getAnswerOptional();
        $arrQuestions['is_required'] = $objQuestion->getIsRequired();
        
        if ($objQuestion->getId() > 0) {
            $this->db->where('id', $objQuestion->getId());
            $insert_query = $this->db->update($tbl_question, $arrQuestions);
            $this->update_answer($objQuestion->getId(), $objQuestion->getAnswer());
            $return_q_id = $objQuestion->getId();
        } else {
            $arrQuestions['created'] = $objQuestion->getCreated();
            $insert_query = $this->db->insert($tbl_question, $arrQuestions);
            $questionid = $return_q_id = $this->db->insert_id();
            $this->insert_answer($questionid, $objQuestion->getAnswer());

            $this->auto_update_display_order_when_create_new_question($questionid, $arrQuestions['training_category'], $arrQuestions['form_id'] ?? '');
        }
        return $return_q_id;
    }

    private function auto_update_display_order_when_create_new_question($questionId, $category, $form_id)
    {
        $this->db->select("(MAX(raq.display_order) + 1) as display_order", false);
        $this->db->from("tbl_recruitment_additional_questions as raq");
        $this->db->where("raq.training_category", $category);
        if ($form_id) {
            $this->db->where("raq.form_id", $form_id);
        }

        $display_order = $this->db->get()->row();

        $order = $display_order->display_order ?? 1;
        $this->basic_model->update_records("recruitment_additional_questions", ["display_order" => $order], ["id" => $questionId]);
    }

    private function insert_answer($question, $arrAnswer)
    {
        $tbl_question_answer = TBL_PREFIX . 'recruitment_additional_questions_answer';
        foreach ($arrAnswer as $key => $value) {
            if(($value->value!='') || ($value->value=='' && trim($value->lebel) == 'Key')){
                $arAnswer = array();
                $arAnswer['question'] = $question;
                $arAnswer['question_option'] = $value->value;
                $arAnswer['serial'] = $value->lebel;
                $arAnswer['answer'] = $value->checked;
                $this->db->insert($tbl_question_answer, $arAnswer);
            }
        }
    }

    private function update_answer($question, $arrAnswer)
    {
        $tbl_question_answer = TBL_PREFIX . 'recruitment_additional_questions_answer';
        $this->db->where('question', $question);
        $this->db->update('tbl_recruitment_additional_questions_answer', array('archive' => 1));

        foreach ($arrAnswer as $key => $value) {
            if(($value->value!='') || ($value->value=='' && trim($value->lebel) == 'Key')){
                $arAnswer = array();
                $arAnswer['question'] = $question;
                $arAnswer['question_option'] = $value->value;
                $arAnswer['serial'] = $value->lebel;
                $arAnswer['answer'] = $value->checked;
                $this->db->insert($tbl_question_answer, $arAnswer);
            }
        }
    }

    public function delete_Questions($objQuestion)
    {
        $tbl_question = TBL_PREFIX . 'recruitment_additional_questions';
        $this->db->where('id', $objQuestion->getId());
        $this->db->update($tbl_question, array('archive' => 1));
        return $this->db->affected_rows();
    }

    public function sub_query_for_get_form_value()
    {
        $this->db->select("rf.title");
        $this->db->from("tbl_recruitment_form as rf");
        $this->db->where("rf.id = raq.form_id", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    public function sub_query_for_get_created_by_name()
    {
        $this->db->select("concat_ws(' ',firstname,lastname)");
        $this->db->from("tbl_member as sub_m");
        $this->db->where("raq.created_by = sub_m.uuid", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    public function sub_query_for_get_question_topic_name()
    {
        $this->db->select("sub_rqt.topic");
        $this->db->from("tbl_recruitment_question_topic as sub_rqt");
        $this->db->where("sub_rqt.id = raq.question_topic", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    public function get_questions_list($reqData)
    {

        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';

		$default_form_filter = '';
       
        $response = $this->get_form_option_for_filter();
        $default_form_filter = $response['form_filter_option'][$filter->filter_category][0]->value ?? '';
       

        $sub_query_form = $this->sub_query_for_get_form_value();
        $sub_query_created_by = $this->sub_query_for_get_created_by_name();
        $sub_query_topic_name = $this->sub_query_for_get_question_topic_name();

        $available_column = array("id", "question", "status", 'created', 'training_category', 'question_type', "display_order", "view_id");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id)  && in_array($sorted[0]->id,$available_column)) {
                $orderBy = (isset($sorted[0]->id) && $sorted[0]->id == 'view_id' ? 'id' : (($sorted[0]->id != 'view_id') ? $sorted[0]->id : 'N/A'));
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'raq.id';
            $direction = 'DESC';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        if (!empty($filter)) {
            $src_columns = array("view_id", "question");
            if (isset($filter->srch_box) && $filter->srch_box != '') {
                $this->db->group_start();
                $srch_val =  $filter->srch_box;

                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        $this->db->or_like($serch_column[0], $srch_val);
                    } else {
                        $this->db->or_like($column_search, $srch_val);
                    }
                }
                $this->db->group_end();

                $queryHavingData = $this->db->get_compiled_select();
                $queryHavingData = explode('WHERE', $queryHavingData);
                $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

                $this->db->having($queryHaving);
            }
			
			if (isset($filter->filter_category)){
				$this->db->where("rit.key_type", $filter->filter_category);
			}
			
			if (isset($filter->filter_form) && $filter->filter_form) {
                    $this->db->where("raq.form_id", $filter->filter_form);
            } else {
					$filter->filter_form = $default_form_filter;
                    $this->db->where("raq.form_id", $default_form_filter);
            }
        }


        $select_column = array("raq.id", "raq.question", "raq.status", 'raq.created', 'raq.training_category', 'raq.question_type', "raq.display_order");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(" . $sub_query_form . ") as form");
        $this->db->select("(" . $sub_query_topic_name . ") as question_topic");
        $this->db->select("(" . $sub_query_created_by . ") as  created_by");

        $this->db->select("(CASE 
            WHEN raq.training_category = 1 THEN concat('GI-Q', raq.id)
            WHEN raq.training_category = 2 THEN concat('CAB-Q', raq.id)
            WHEN raq.training_category = 3 THEN concat('QTN', raq.id)
            WHEN raq.training_category = 4 THEN concat('QTN', raq.id)
            else raq.id END) as view_id");

        $this->db->from("tbl_recruitment_additional_questions as raq");
		$this->db->join("tbl_recruitment_interview_type as rit", "rit.id = raq.training_category", "INNER");
        $this->db->where('raq.archive', '0');

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        # last_query();
        $dataResult = $query->result();

        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        if (!empty($dataResult)) {

            $question_id = array_column(obj_to_arr($dataResult), 'id');
            $question_id = empty($question_id) ? [0] : $question_id;
            $question_data = $this->get_answer_details($question_id);

            foreach ($dataResult as $data) {
                $data->created = $data->created != '0000-00-00 00:00:00' ? date('d/m/Y', strtotime($data->created)) : '';
                $data->status = (int) $data->status;
                $data->answers = isset($question_data[$data->id])  ? $question_data[$data->id] : [];;
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count, "filter_form" => $filter->filter_form, "filter_option" => $response);
        return $return;
    }

    public function get_question_detail($reqData)
    {
        if (!empty($reqData)) {
            $questionId = $this->db->escape_str($reqData->questionId, true);
            $tbl_job = TBL_PREFIX . 'recruitment_additional_questions';

            $question_data = "SELECT  raq.id as question_id,raq.question,raq.status as question_status,raq.question_type as answer_type,raq.question_topic,raq.training_category as question_category,raq.form_id,raq.is_answer_optional,raq.is_required FROM `tbl_recruitment_additional_questions` as raq
            WHERE raq.archive = '0' AND raq.id = '" . $questionId . "' ";
            $que_data_ex = $this->db->query($question_data);
            $ques_data_ary = $que_data_ex->row_array();

            if (empty($ques_data_ary) || is_null($ques_data_ary)) {
                $return = array('status' => false);
                return $return;
            }

            $answer_data = "SELECT  raqa.answer,raqa.question_option,raqa.serial FROM `tbl_recruitment_additional_questions_answer` as raqa
            WHERE  raqa.question  = '" . $questionId . "' AND raqa.archive = 0";
            $answer_data_ex = $this->db->query($answer_data);
            $answer_ary = $answer_data_ex->result_array();
            $answer_data_ary = isset($answer_ary) && !empty($answer_ary) ? $answer_ary : array();
            $answers_ary = array();
            if (!empty($answer_data_ary)) {
                foreach ($answer_data_ary as $ans) {
                    $answers_ary[] = array('checked' => ($ans['answer'] == 1) ? true : false, 'value' => $ans['question_option'], 'lebel' => $ans['serial']);
                }
            }
            $ques_data_ary['answers'] = $answers_ary;
            $return = array('status' => true, 'data' => $ques_data_ary);
            return $return;
        }
    }

    public function get_applican_count($questionid)
    {
        if ($questionid != '') {
            $this->db->select("id");
            $this->db->from('tbl_recruitment_additional_questions_for_applicant');
            $this->db->where('question_id', $questionid);
            $this->db->where('archive', 0);
            $query = $this->db->get();
            return $query->num_rows();
        }
    }


    public function get_answer_details($qid)
    {
        $tbl_question_answer = TBL_PREFIX . 'recruitment_additional_questions_answer';
        $select_answer_column = array($tbl_question_answer . ".id as answer_id", $tbl_question_answer . ".answer as checked", $tbl_question_answer . ".question_option as value", $tbl_question_answer . ".serial as lebel", $tbl_question_answer . ".question as q_id");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_answer_column)), false);
        $this->db->from($tbl_question_answer);
        $this->db->where('archive', 0);
        $this->db->where_in('question', $qid);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $res = $query->result_array();

        $complete_ary = [];
        if (!empty($res)) {
            $temp_hold = [];
            foreach ($res as $key => $value) {
                $q_id = $value['q_id'];
                if (!in_array($q_id, $temp_hold)) {
                    $temp_hold[] = $q_id;
                }
                $temp['q_id'] = $q_id;

                $temp['answer_id'] = $value['answer_id'];
                $temp['checked'] = $value['checked'];
                $temp['value'] = $value['value'];
                $temp['lebel'] = $value['lebel'];
                $complete_ary[$q_id][] = $temp;
            }
        }
        return $complete_ary;
    }

    function get_form_option_for_filter()
    {
		$res["question_filter_option"] = $this->get_question_type_for_filter();

        $column = ["rf.id as value", "rf.title as label", "rit.key_type as interview_type"];
        $this->db->select($column);
        $this->db->from("tbl_recruitment_form as rf");
        $this->db->join("tbl_recruitment_interview_type as rit", "rit.id = rf.interview_type AND rit.archive = 0", "INNER");
        $this->db->where("rf.archive", 0);
		$response = $this->db->get()->result();

        $x = [];
        if (!empty($response)) {
            foreach ($response as $val) {
                $x[$val->interview_type][] = $val;
            }
        }

       $filter_x = $x;
	   
	    $form_option = [];
		if(!empty($res["question_filter_option"])){
                foreach($res["question_filter_option"] as $val){
                    $form_option[$val->value] = $filter_x[$val->value] ?? [];
                }
        }

        $res["form_filter_option"] = $form_option;
		return $res;
    }

    function get_question_type_for_filter()
    {
        $column = ["key_type as value", "name as label", "id"];
        return $this->basic_model->get_record_where_orderby("recruitment_interview_type", $column, ["archive" => 0], 'id', 'asc');
    }

    function update_display_order_question($reqData)
    {
        $questionId = ($reqData->questionId);
        $ques = $this->basic_model->get_row("recruitment_additional_questions", ["training_category", "form_id", "display_order"], ["id" => $questionId]);

        if (!empty($ques)) {
            $exact_questions = $lower_questions = $higher_questions = null;
            $changing_to_order = $reqData->order;
            $current_order = $ques->display_order;

            # finding total number of questions
            $query = $this->db->query("select * from tbl_recruitment_additional_questions where form_id = ".$ques->form_id);
            $total_questions = $query->num_rows();
            if($changing_to_order > $total_questions)
            $changing_to_order = $total_questions;

            # updating the current question to requested order first
            $this->db->where("id", $questionId);
            $this->db->set("display_order", $changing_to_order);
            $this->db->update("tbl_recruitment_additional_questions");

            # finding the questions lower than the currently being updated
            $this->db->select(["raq.id", "raq.display_order", "raq.training_category", "raq.form_id"]);
            $this->db->from("tbl_recruitment_additional_questions as raq");
            if($changing_to_order > $current_order)
                $this->db->where("raq.display_order <=", $changing_to_order);
            else
                $this->db->where("raq.display_order <", $changing_to_order);
            $this->db->where("raq.training_category", $ques->training_category);
            $this->db->where("raq.form_id", $ques->form_id);
            $this->db->where("raq.id !=", $questionId);
            $this->db->order_by("display_order","ASC");
            $query = $this->db->get();
            $lower_questions_res = $query->result_array();
            if (!empty($lower_questions_res)) {
                $lower_questions = array_column($lower_questions_res, "id");
            }

            # finding the questions higher than the currently being updated
            $this->db->select(["raq.id", "raq.display_order", "raq.training_category", "raq.form_id"]);
            $this->db->from("tbl_recruitment_additional_questions as raq");
            if($changing_to_order > $current_order)
                $this->db->where("raq.display_order >", $changing_to_order);
            else
            $this->db->where("raq.display_order >=", $changing_to_order);
            $this->db->where("raq.training_category", $ques->training_category);
            $this->db->where("raq.form_id", $ques->form_id);
            $this->db->where("raq.id != ", $questionId);
            $this->db->order_by("display_order","ASC");
            $query = $this->db->get();
            $higher_questions_res = $query->result_array();
            if (!empty($higher_questions_res)) {
                $higher_questions = array_column($higher_questions_res, "id");
            }

            # if no lower priority questions are found then starting from 1 increase their priority
            if(!empty($lower_questions)) {
                $last_order = 0;
                foreach ($lower_questions as $index => $qid) {
                    $last_order++;
                    $this->db->where("id", $qid);
                    $this->db->set("display_order", $last_order);
                    $this->db->update("tbl_recruitment_additional_questions");
                }
            }
            # if no higher priority questions are found then starting from what we set, increase their priority
            if(!empty($higher_questions)) {
                $last_order = $changing_to_order;

                foreach ($higher_questions as $index => $qid) {
                    $last_order++;
                    $this->db->where("id", $qid);
                    $this->db->set("display_order", $last_order, false);
                    $this->db->update("tbl_recruitment_additional_questions");
                }
            }
        }
        return true;
    }
    public function get_interview_type() {
        $tbl_int_type = TBL_PREFIX . 'recruitment_interview_type';
        $this->db->select(array($tbl_int_type . ".name as label", $tbl_int_type . ".id as value"));
        $this->db->from($tbl_int_type);
        $this->db->where(array($tbl_int_type . '.archive' => 0));
        $this->db->order_by($tbl_int_type . '.sort_order','ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_form_option($interview_type) {
        $tbl_form = TBL_PREFIX . 'recruitment_form';
        $this->db->select(array($tbl_form . ".title as label", $tbl_form . ".id as value"));
        $this->db->from($tbl_form);
        $this->db->where(array($tbl_form . '.interview_type' => $interview_type));
        $this->db->where($tbl_form.".archive", 0);
        $query = $this->db->get();
        return $query->result();
    }

	
	function get_question_topic_filter_option(){
		$result[] = ["value" => 'all', "label" => "ALL"];
		
		$column = ["id as value", "topic as label"];
		$where = '';
		$x = $this->basic_model->get_record_where("recruitment_question_topic", $column, $where);
		
		$result = array_merge($result, obj_to_arr($x));
		return $result;
	}
}
