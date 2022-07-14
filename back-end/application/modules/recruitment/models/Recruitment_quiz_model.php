<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model file that act as both data provider
 * or data repository for RecruitmentForm controller actions
 */
class Recruitment_quiz_model extends \CI_Model {

    public $quiz_stage_status = [
        "0" => "Draft",
        "1" => "Scheduled",
        "2" => "Open",
        "3" => "In progress",
        "4" => "Submitted",
        "5" => "Expired",
        "6" => "Completed",
    ];

    public $quiz_stage_status_final = [
        "5" => "Expired",
        "6" => "Completed",
    ];

    public $quiz_stage_status_grouped = [
        "0" => "Draft",
        "1" => "Scheduled",
        "2" => "Open",
        "3" => "In progress",
        "4" => "Submitted",
        "7" => "Closed",
    ];


    public function __construct() {
        parent::__construct();

        $this->load->helper(['array']);
        $this->load->model('recruitment/Recruitmentformapplicant_model');
    }

    /**
     * Check if given user id is an admin in`tbl_member`.
     * User is an admin if user_type column is 1 (See `tbl_admin_user_type`)
     *
     * @param int $userId
     * @return bool
     */
    protected function is_admin($userId) {
        // @see tbl_admin_user_type
        $USER_TYPE_ADMIN = 1;

        $query = $this->db->get_where('tbl_member', [
            'id' => $userId,
            'archive' => 0,
            'user_type' => $USER_TYPE_ADMIN,
                ], 1);

        $foundUser = $query->num_rows();
        if (empty($foundUser)) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param array|null $array
     * @param string[] $keys
     * @return array|null
     */
    protected function only($array, $keys) {
        if (is_null($array)) {
            return null;
        }

        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Check if empty or white space.
     * Based on CI form validation 'required' method
     *
     * @param mixed $str
     * @return bool
     */
    protected function isEmptyOrWhiteSpace($str) {
        $hasValue = is_array($str) ? (empty($str) === FALSE) : (trim($str) !== '');
        return !$hasValue;
    }

    /**
     * gets the question details
     */
    public function get_question_details($qid, $form_applicant_id = null) {
        $select_column = array("raq.*");
        $this->db->select($select_column);
        if ($form_applicant_id)
            $this->db->from("tbl_recruitment_form_applicant_question as raq");
        else
            $this->db->from("tbl_recruitment_additional_questions as raq");
        $this->db->where('raq.id', $qid);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dataResult = $query->result();

        if (!empty($dataResult) && isset($dataResult[0])) {
            return $dataResult[0];
        }
        return false;
    }
    /**
     * to fetch the answer options of a given question
     */
    public function get_answer_details($form_applicant_id, $simple_array = false, $check_form = null) {
        if ($check_form)
            $tbl_question_answer = TBL_PREFIX . 'recruitment_form_applicant_question_answer';
        else
            $tbl_question_answer = TBL_PREFIX . 'recruitment_form_applicant_question_answer';

        $select_answer_column = array($tbl_question_answer . ".id as answer_id", $tbl_question_answer . ".answer as checked", $tbl_question_answer . ".question_option as value", $tbl_question_answer . ".serial as label", $tbl_question_answer . ".question as q_id");
        $this->db->select($select_answer_column);
        $this->db->from($tbl_question_answer);

        if (!$check_form)
        $this->db->where('form_applicant_id' , $form_applicant_id);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $res = $query->result_array();

        $complete_ary = [];
        if (!empty($res)) {
            $temp_hold = [];
            foreach ($res as $key => $value) {
                $q_id = $value['q_id'];

                # no need to provide the answer option if there is no value provided for it
                if (!$value['value'])
                    continue;

                if (!in_array($q_id, $temp_hold)) {
                    $temp_hold[] = $q_id;
                }
                $temp['q_id'] = $q_id;
                $temp['answer_id'] = $value['answer_id'];
                $temp['checked'] = (boolean) $value['checked'];
                $temp['value'] = $value['value'];
                $temp['label'] = $value['label'];

                if ($simple_array)
                    $complete_ary[] = $temp;
                else
                    $complete_ary[$q_id][] = $temp;
            }
        }
        return $complete_ary;
    }
    /**
    * returns the list of questions id based on quiz form_applicant_id
    */
   function get_question_id_by_form_applicant_id($form_applicant_id) {
       $this->db->select(["rfaa.id as question_id"]);
       $this->db->from("tbl_recruitment_form_applicant_question  as rfaa");
       $this->db->where(["rfaa.form_applicant_id" => $form_applicant_id]);
       $return  = $this->db->get()->result();
       return $return ;

   }

    /**
     * Create update quiz details
     */
    function create_update_quiz($post_data, $adminId) {
        $id = $post_data['quiz_id'] ?? 0;
        $save_task_data = array(
            'task_name'=> $post_data['title'],
            'owner'=> $post_data['owner'],
            'task_status' => $post_data['quiz_id'] ?  $post_data['task_status'] : 0 ,//0-draft,
            'form_id' => $post_data['form_id'],
            'start_datetime'=>date("Y-m-d H:i:s", strtotime($post_data['quiz_start_datetime'])),
            'end_datetime'=>date("Y-m-d H:i:s", strtotime($post_data['quiz_end_datetime'])),
            'created'=> DATE_TIME,
            'created_by' => $adminId
        );

        $quiz_id = '';
        if($id){
            // Update quiz data
            $save_task_data["updated"] = DATE_TIME;
            $save_task_data["updated_by"] = $adminId;
            $taskId = $this->Basic_model->update_records("recruitment_task", $save_task_data, ["id" => $id]);
            if(!empty($taskId)){
                $save_task_applicant_data = ['taskId' =>$id,'application_id'=> $post_data['related_to'],'applicant_id'=>$post_data['applicant_id'],'created'=>DATE_TIME];
                $quiz_id = $this->Basic_model->update_records('recruitment_task_applicant', $save_task_applicant_data, ["id" => $id]);
            }
        }else{
            // Create quiz data
            $save_task_data["created"] = DATE_TIME;
            $save_task_data["created_by"] = $adminId;
            $taskId = $this->Basic_model->insert_records('recruitment_task', $save_task_data);
            if(!empty($taskId)){
                $save_task_applicant_data = ['taskId' =>$taskId,'application_id'=> $post_data['related_to'],'applicant_id'=>$post_data['applicant_id'],'created'=>DATE_TIME];
                $quiz_id = $this->Basic_model->insert_records('recruitment_task_applicant', $save_task_applicant_data);
                if (!$quiz_id) {
                    return ['status' => false, 'error' => "Quiz is not created. something went wrong"];
                }
            }
        }

        if((strtotime($save_task_data['start_datetime'])) > (strtotime($save_task_data['end_datetime']))){
            return ['status' => false, 'error' => "Incorrect quiz start & end date/time"];
        }

        if($id){
            return ['status' => true, 'msg' => "Quiz updated successfully", 'quiz_id' => $quiz_id];
        }else{
            return ['status' => true, 'msg' => "Quiz created successfully", 'quiz_id' => $quiz_id];
        }

    }

    public function get_quiz_list_by_id($reqData) {
        if (!empty($reqData)) {
            $application_id = json_decode($reqData->application_id);
            $applicant_id = json_decode($reqData->applicant_id);

            $created_by_sub_query = $this->get_created_updated_by_sub_query('created_by','rta');

            $limit = $reqData->pageSize;
            $page = $reqData->page;
            $sorted = $reqData->sorted;
            $filter = $reqData->filtered;
            $orderBy = '';
            $direction = '';

            $src_columns = array("rta.id", "rta.title", "rta.status");
            $available_column = array('id', 'title', 'taskId', 'status');
            if (!empty($sorted)) {
                if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                    $orderBy = $sorted[0]->id == 'id' ? 'rta.id' : 'rta.' . $sorted[0]->id;
                    $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
                }
            } else {
                $orderBy = 'rta.id';
                $direction = 'DESC';
            }
            $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

            // Filter by status
            if (!empty($filter->filter_status)) {
                if ($filter->filter_status !== "all") {
                    $this->db->where('rt.task_status', $filter->filter_status);
                }
            }
            if(!empty($filter) && $filter->filter_status=='0') {
                    $this->db->where('rt.task_status', $filter->filter_status);
            }

            $select_column = array('rta.*', 'rt.*');

            $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

            // $this->db->select("(" . $created_by_sub_query . ") as created_by");
            $this->db->select("(
                CASE
                    WHEN rt.task_status = 0 THEN 'Draft'
                    WHEN rt.task_status = 1 THEN 'Scheduled'
                    WHEN rt.task_status = 2 THEN 'Open'
                    WHEN rt.task_status = 3 THEN 'In progress'
                    WHEN rt.task_status = 4 THEN 'Submitted'
                    WHEN rt.task_status = 5 THEN 'Expired'
                    WHEN rt.task_status = 6 THEN 'Completed'
                ELSE ''
                END

                ) as status");
            $this->db->from(TBL_PREFIX . 'recruitment_task_applicant as rta');
            $this->db->join(TBL_PREFIX . 'recruitment_task as rt', 'rt.id = rta.taskId', 'INNER');
            $this->db->where([ 'rta.applicant_id' => $applicant_id, 'rta.application_id' => $application_id,'rta.archive' => 0]);
            $this->db->where_in('rta.stage_label_id', ['0','3','6']);
            $this->db->order_by($orderBy, $direction);
            $this->db->limit($limit, ($page * $limit));

            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            #last_query();die;
            $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

            if ($dt_filtered_total % $limit == 0) {
                $dt_filtered_total = ($dt_filtered_total / $limit);
            } else {
                $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
            }

            $dataResult = $query->result();

            $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'status' => true, 'total_count' => $total_count);
            return $return;
        }
    }

    /*
     * it is used for making sub query created by (who creator|updated of member)
     * return type sql
     */
    private function get_created_updated_by_sub_query($column_by, $tbl_alais) {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = ".$tbl_alais.".".$column_by, null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for making sub query form id (who creator|updated of form)
     * return type sql
     */
    private function get_form_data_by_sub_query($column_by, $tbl_alais) {
        // $this->db->select(["rf.title as label", "rf.id as value"]);
        $this->db->select("rf.title as label");
        $this->db->from("tbl_recruitment_form as rf");
        $this->db->where("rf.id = ".$tbl_alais.".".$column_by, null, false);
        return $this->db->get_compiled_select();
    }

    /**
     * Get quiz status details by id and update the status based on time
     */
    function get_quiz_status_detail_by_id($reqData) {

        $quiz_id = $reqData->quiz_id;

        $this->db->select([ "rt.*"]);
        $this->db->from("tbl_recruitment_task as rt");
        $this->db->where("rt.id =" , $quiz_id);
        $query = $this->db->get();
        $quiz = $query->row();
        $dataResult = $quiz;
        if (!$quiz) {
            return ['status' => false, 'error' => "Quiz is not exist. something went wrong"];
        }
        if($dataResult->task_status ==1){
            if((DATE_TIME >= $dataResult->start_datetime) && (DATE_TIME >= $dataResult->end_datetime)
            && $dataResult->task_status!=4 and $dataResult->task_status!=3) {
                    $upd_data["task_status"] = 2; // Open;
                    $where = array("id"=> $dataResult->id);
                    $this->basic_model->update_records("recruitment_task", $upd_data, $where);
            }
        }
        if($dataResult->task_status ==2 || $dataResult->task_status ==3 ){
            if(DATE_TIME > $dataResult->end_datetime) {
                    $upd_data["task_status"] = 5; // Expired;
                    $where = array("id"=> $dataResult->id);
                    $this->basic_model->update_records("recruitment_task", $upd_data, $where);
            }
        }

        return ['status' => true, 'msg' => "Quiz status updated successfully", 'data' => $dataResult];
    }

    /**
     * Get quiz details
     */
    function get_quiz_detail_by_id($reqData) {

        $quiz_id = $reqData->quiz_id;

        $created_by_sub_query = $this->get_created_updated_by_sub_query('created_by','rt');
        $updated_by_sub_query = $this->get_created_updated_by_sub_query('updated_by','rt');
        $owner_sub_query = $this->get_created_updated_by_sub_query('owner','rt');
        $form_sub_query = $this->get_form_data_by_sub_query('form_id','rt');

        $this->db->select([ "rt.*", "CONCAT('',rta.application_id) as application","rta.applicant_id","rta.applicant_task_status"]);
        $this->db->from("tbl_recruitment_task as rt");
        $this->db->join(TBL_PREFIX . 'recruitment_task_applicant rta', 'rt.id = rta.taskId', 'LEFT');
        $this->db->select("(" . $created_by_sub_query . ") as created_by");
        $this->db->select("(" . $updated_by_sub_query . ") as updated_by");
        $this->db->select("(" . $owner_sub_query . ") as owner_name");
        $this->db->select("(" . $form_sub_query . ") as form_name");
        $this->db->select("(
            CASE
                WHEN rt.task_status = 0 THEN 'Draft'
                WHEN rt.task_status = 1 THEN 'Scheduled'
                WHEN rt.task_status = 2 THEN 'Open'
                WHEN rt.task_status = 3 THEN 'In progress'
                WHEN rt.task_status = 4 THEN 'Submitted'
                WHEN rt.task_status = 5 THEN 'Expired'
                WHEN rt.task_status = 6 THEN 'Completed'
            ELSE ''
            END

            ) as status_label");
        $this->db->where("rt.id =" , $quiz_id);
        $query = $this->db->get();
        $quiz = $query->row();
        $dataResult = $quiz;
        if(!empty($quiz->start_datetime) && $quiz->start_datetime != "0000-00-00 00:00:00"){
                $dataResult->quiz_start_time = get_time_id_from_series($quiz->start_datetime);
        }
            else {
                $quiz->start_datetime = '';
                $quiz->quiz_start_time = '';
            }

            if(!empty($quiz->end_datetime) && $quiz->end_datetime != "0000-00-00 00:00:00"){
                $dataResult->quiz_end_time = get_time_id_from_series($quiz->end_datetime);
            }
            else {
                $quiz->quiz_end_date = '';
                $quiz->quiz_end_time = '';
            }

        if (!$quiz) {
            return ['status' => false, 'error' => "Quiz is not exist. something went wrong"];
        }
        return ['status' => true, 'msg' => "Quiz detail fetched successfully", 'data' => $dataResult];
    }
    /**
     * fetches list of questions for a given form and all the answer options provided for
     * each question
     */
    public function get_questions_list_by_applicant_quiz_and_form_id($reqData) {
        $quiz_id = $reqData->quiz_id;
        $form_id = $reqData->form_id;

        // Check form_applicant_id is exist. Using title
        $where = array('applicant_id' => $reqData->applicant_id, 'application_id' => $reqData->application_id, 'form_id' => $reqData->form_id, 'task_id'=>$reqData->quiz_id);
        $colown = array('id');
        $check_form = $this->basic_model->get_record_where('recruitment_form_applicant', $colown, $where);
        // $check_form = false;
        if ($quiz_id != '') {

            $limit = $reqData->pageSize;
            $page = $reqData->page;
            $sorted = $reqData->sorted;
            $filter = $reqData->filtered;
            $orderBy = '';
            $direction = '';

            $src_columns = array("raq.question");
            if (!$check_form){
                $available_column = array("id","question", "status", 'training_category', 'question_type', "display_order", "is_answer_optional", "is_required");
            }else{
                $available_column = array("id", "question_id","question","form_applicant_id", "status", 'training_category', 'question_type', "display_order", "is_answer_optional", "is_required");
            }
            if (!empty($sorted)) {
                if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                    $orderBy = $sorted[0]->id == 'id' ? 'raq.id' :  $sorted[0]->id;
                    $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
                }
            } else {
                $orderBy = 'raq.display_order';
                $direction = 'ASC';
            }
            $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

            // Filter by status
            if (!empty($filter->filter_status)) {
                if ($filter->filter_status === "draft") {
                    $this->db->where('raq.status', 1);
                } else if ($filter->filter_status === "completed") {
                    $this->db->where('raq.status', 2);
                }
            }

            if (!$check_form){
                $select_column = array("raq.id","raq.question", "raq.status", 'raq.training_category', 'raq.question_type', "raq.display_order", "raq.is_answer_optional", "raq.is_required");
            }else{
                $select_column = array("raq.id", "rfaq.id as question_id","rfaq.question","rfa.id as form_applicant_id", "raq.status", 'raq.training_category', 'rfaq.question_type', "raq.display_order", "raq.is_answer_optional", "raq.is_required");
            }


            $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

            $this->db->select("(concat('QTN', raq.id)) as view_id");

            $this->db->from("tbl_recruitment_additional_questions as raq");

            $this->db->join("tbl_recruitment_task as rt","rt.form_id = raq.form_id","INNER");
            if ($check_form){
                $this->db->join("tbl_recruitment_form_applicant as rfa  ", "rt.form_id = rfa.form_id","INNER");
                $this->db->join("tbl_recruitment_form_applicant_question as rfaq  ", "rfa.id = rfaq.form_applicant_id","INNER");
            }

            $this->db->where('raq.archive', '0');
            $this->db->where('raq.status', 1);
            $this->db->where('rt.id', $quiz_id);
            if ($check_form){
            $this->db->where('rfa.applicant_id', $reqData->applicant_id);
            $this->db->where('rfa.application_id', $reqData->application_id);
            $this->db->group_by('rfaq.id');
            }else{
                $this->db->group_by('raq.id');
            }

            $this->db->limit($limit, ($page * $limit));
            $this->db->order_by($orderBy, $direction);
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

            if ($dt_filtered_total % $limit == 0) {
                $dt_filtered_total = ($dt_filtered_total / $limit);
            } else {
                $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
            }

            $dataResult = $query->result();
            if($check_form){
                if (!empty($dataResult)) {
                    $question_id = array_column(obj_to_arr($dataResult), 'id');
                    $question_id = empty($question_id) ? [0] : $question_id;
                    $question_data = $this->get_answer_details($dataResult[0]->form_applicant_id, false, $check_form);

                    foreach ($dataResult as $data) {
                        # fetching the answer if the form was previously submitted
                        # for short answer
                        if ($data->form_applicant_id && $data->question_type == 4) {
                            $applicant_answer_text = $this->Recruitmentformapplicant_model->fetch_answer_provided_of_question($data->form_applicant_id, $data->question_id, "0");
                            if (empty($applicant_answer_text))
                                $answer_text = '';
                            $data->answer_text = $applicant_answer_text;
                        }
                        # for choice questions
                        else if ($data->form_applicant_id && $data->question_type != 4) {
                            $applicant_answer_id = $this->Recruitmentformapplicant_model->fetch_answer_provided_of_question($data->form_applicant_id, $data->question_id, null);
                            $data->answer_id = $applicant_answer_id;
                        }
                        $data->status = (int) $data->status;
                        $data->answers = isset($question_data[$data->question_id]) ? $question_data[$data->question_id] : [];
                        ;
                    }
                }
            }

            $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'status' => true, 'total_count' => $total_count);
            return $return;
        } else {
            $return = array('count' => 0, 'data' => [], 'status' => false, 'total_count' => 0);
            return $return;
        }
    }

    /**
     * fetching the quiz information
     */
    public function get_quiz_applicant_answer($answer_id = null) {
        if (empty($answer_id)) return;


        $this->db->select("rfaa.*");
        $this->db->from('tbl_recruitment_form_applicant_answer as rfaa');


        if($answer_id)
            $this->db->where("rfaa.id", $answer_id);

        $this->db->where("rfaa.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Applicant answer not found!", 'status' => false);
            return $return;
        }
        foreach ($query->result() as $val) {
            $dataResult = $val;
        }

        $return = array('data' => $dataResult, 'status' => true, 'last_query' => $last_query);
        return $return;
    }

    function get_curent_staff_detail_by_id($reqData, $adminId)
    {
        $this->db->select("(CASE WHEN a.is_super_admin = 1 THEN 1
            WHEN ((select id from tbl_recruitment_staff as rs where rs.adminId = a.id and rs.archive= 0 AND rs.status = 1 AND rs.approval_permission = 1) > 0) THEN 1
            ELSE 0 end) as is_recruitment_user");
        $this->db->select(array('concat(a.firstname," ",a.lastname) as label', 'a.id as value', '"2" as primary_recruiter'), false);
        $this->db->from('tbl_member as a');
        $this->db->join('tbl_department as d', 'd.id = a.department AND d.short_code = "internal_staff"', 'inner');

        $this->db->where('a.archive', 0);
        $this->db->where('a.id', $adminId);
        $this->db->where('a.status', 1);
        $this->db->having("is_recruitment_user", 1);

        $query = $this->db->get();

        $result = $query->result_array();
        $result = $query->num_rows() > 0 ? $result[0] : '';
        $return = array('count' => 0, 'data' => $result, 'status' => true, 'total_count' => 0);
        return $return;
    }


     /**
     * fetching the quiz information
     */
    public function get_quiz_details($quiz_id = null) {
        if (empty($quiz_id)) return;


        $this->db->select("rt.*");
        $this->db->from('tbl_recruitment_task as rt');
        $this->db->join(TBL_PREFIX . 'recruitment_task_applicant rta', 'rt.id = rta.taskId', 'LEFT');


        if($quiz_id)
            $this->db->where("rt.id", $quiz_id);

        $this->db->where("rta.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Quiz not found!", 'status' => false);
            return $return;
        }
        foreach ($query->result() as $val) {
            $dataResult = $val;
        }

        $return = array('data' => $dataResult, 'status' => true, 'last_query' => $last_query);
        return $return;
    }

    /**
     * fetches all the quiz statuses
     */
    public function get_quiz_stage_status() {
        $data = null;
        foreach($this->quiz_stage_status_grouped as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * fetches all the final quiz statuses
     */
    public function get_quiz_statuses_final() {
        $data = null;
        foreach($this->quiz_stage_status_final as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * Updating the quiz status.
     */
    function update_quiz_status($data, $adminId) {

        $id = isset($data['id']) ? $data['id'] : 0;
        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the quiz exist?
        $result = $this->get_quiz_details($data['id']);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Quiz does not exist anymore."];
            return $response;
        }

        # updating status
        $upd_data["task_status"] = $data['quiz_task_status'];
        $upd_data["updated"] = DATE_TIME;
        $this->basic_model->update_records("recruitment_task", $upd_data, ["id" => $id]);
        // update member quiz to schedule
        if($data['quiz_task_status']==1){
            $this->basic_model->update_records("recruitment_task_applicant",["applicant_task_status"=>1], ["taskId"=> $id]);
        }

        # adding a log entry
        $msg = "Application status is updated successfully";
        $this->add_create_update_quiz_log($upd_data, $msg, $adminId, $id);

        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /*
	 * used by the create_update_application function to insert a log entry on
     * application adding / updating
     */

    public function add_create_update_quiz_log($data, $title, $adminId, $application_id) {
    	$this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle($title);
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($application_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
    }


    public function update_applicant_quiz_status($task_id, $status, $applicant_info = NULL){
        $where_app_id_columns = ["id"=>$task_id];
        $data = ["task_status"=>$status,"updated"=>DATE_TIME];
        $this->db->update('tbl_recruitment_task',$data, $where_app_id_columns);

        if($applicant_info) {
            $this->basic_model->insert_records('notification',
            ['userId'=> $applicant_info['applicantID'], 'user_type'=> 0, 'title' =>
            "Quiz Completion - ". $applicant_info['fullname'] ." - App ID:
            ". $applicant_info['appId'], 'shortdescription' => ucfirst($applicant_info['fullname']) .
            " has successfully submitted the quiz for your review",
            'created' => DATE_TIME, 'status' => 0, 'sender_type' => 1, 'specific_admin_user' => $applicant_info['owner'],
            'entity_type' => 7, 'entity_id' => $task_id] );
        }
        return $this->db->update('tbl_recruitment_task_applicant',["applicant_task_status"=>$status], ["taskId"=>$task_id]);
    }

    public function get_applicant_question_answer_details($reqData) {

    $limit = $reqData->data->pageSize;	
    $page = $reqData->data->page;	
    $orderBy = '';	
    $direction = '';

    $applicant_id = isset($reqData->data->applicant_id) && $reqData->data->applicant_id != '' ? $reqData->data->applicant_id : '';
    $taskId = isset($reqData->data->quiz_id) && $reqData->data->quiz_id != '' ? $reqData->data->quiz_id : '';
    $type = isset($reqData->data->type) && $reqData->data->type != '' ? $reqData->data->type : 'none';

    if ($applicant_id == '' && $taskId == '') {
        echo json_encode(array('status' => false, 'msg' => 'Invalid request.'));
        exit();
    }


    // Check form_applicant_id is exist. Using title
    $where = array('applicant_id' => $applicant_id, 'application_id' => $reqData->data->application_id, 'form_id' => $reqData->data->form_id, 'task_id'=>$taskId);
    $colown = array('id');
    $check_form = $this->basic_model->get_record_where('recruitment_form_applicant', $colown, $where);
    if (!$check_form){
        $question_db_table = $this->get_questions_list_by_applicant_quiz_and_form_id($reqData->data);
        return $question_db_table;
    }else{
        $param = ['taskId'=>$taskId,'applicant_id'=>$applicant_id];
        $data_row = $this->get_form_applicant_id($param);
        $data = $this->basic_model->get_row($table_name = 'recruitment_task_applicant', $columns = array('id'), $id_array = array('taskId'=>$taskId,'applicant_id'=>$applicant_id));

        $this->db->select(array('rfaq.question',  'rfaq.question_type', 'rfaq.training_category','rfaq.id as question_id','rfaq.question_topic'));

        $this->db->select("case when rfaq.created!='0000-00-00 00:00:00' THEN
            (DATE_FORMAT(rfaq.created,'%d/%m/%y - %h:%i %p')) ELSE '' END as created_date", false);

        $this->db->select("case when rfaq.question_topic!=0 THEN
            (SELECT topic FROM tbl_recruitment_question_topic as rqt where rqt.id=rfaq.question_topic) ELSE '' END as topic", false);

        $this->db->select("case when rfaq.created_by!=0 THEN
            (SELECT concat(m.firstname,' ',m.middlename,' ',m.lastname) FROM tbl_member as m where m.id=rfaq.created_by) ELSE '' END as created_by", false);

        $this->db->select("case when rfaq.question_type!=4 THEN
            (SELECT concat_ws('#__BREAKER__#', GROUP_CONCAT(raqa.answer), GROUP_CONCAT(raqa.question_option SEPARATOR '#_BR_#'), GROUP_CONCAT(raqa.serial)) as data
            FROM tbl_recruitment_form_applicant_question_answer as raqa where rfaq.id = raqa.question) ELSE (SELECT question_option as data FROM tbl_recruitment_form_applicant_question_answer as raqa where  rfaq.id = raqa.question) END as job_details");

        $this->db->select("case when rfaq.question_type!=4 THEN
            (SELECT concat_ws('#__BREAKER__#',GROUP_CONCAT(raqa.answer),GROUP_CONCAT(raqa.question_option SEPARATOR '#_BR_#'), GROUP_CONCAT(raqa.serial)) as data FROM tbl_recruitment_form_applicant_question_answer as raqa RIGHT JOIN tbl_recruitment_form_applicant_answer as raa ON raa.answer_id = raqa.id and raa.question_id = raqa.question and raa.archive = 0 where raqa.question = rfaq.id) ELSE (SELECT question_option as data FROM tbl_recruitment_form_applicant_question_answer as raqa where  rfaq.id = raqa.question) END as job_details_ans");

        $this->db->select("case when rfaq.question_type!=4 THEN
            coalesce((SELECT answer FROM tbl_recruitment_form_applicant_question_answer as raqa inner join tbl_recruitment_form_applicant_answer  as rfaa ON rfaa.question_id = raqa.question AND rfaa.answer_id=raqa.id AND rfaa.archive=0 where rfaq.id = raqa.question limit 1),0) ELSE (SELECT answer FROM tbl_recruitment_form_applicant_question_answer as raqa inner join tbl_recruitment_form_applicant_answer  as rfaa ON rfaa.question_id = raqa.question AND rfaa.archive=0 where rfaq.id = raqa.question limit 1) END as is_answer_correct");

        $this->db->select("case when rfaq.question_type=4 THEN
            (SELECT answer_text FROM tbl_recruitment_form_applicant_answer as rfaa where rfaq.id = rfaa.question_id AND rfaa.archive=0 limit 1) ELSE '' END as answer_text");

        $this->db->from('tbl_recruitment_form_applicant_question as rfaq');
        $this->db->join('tbl_recruitment_task as rt', 'rt.id = '.$this->db->escape_str($taskId, true), 'inner');
        $this->db->join('tbl_recruitment_form_applicant as rfa', 'rfa.id = rfaq.form_applicant_id AND rfaq.archive=0 AND rfa.form_id = rt.form_id AND rfa.task_id = rt.id AND rfa.applicant_id='.$this->db->escape_str($applicant_id, true), 'inner');
        $this->db->join('tbl_recruitment_task_applicant as rta', 'rta.applicant_id=rfa.applicant_id AND rta.taskId=rt.id and rta.application_id = rfa.application_id', 'inner');

        $this->db->limit($limit, ($page * $limit));
        $this->db->order_by($orderBy, $direction);

        $this->db->limit($limit, ($page * $limit));
        $this->db->order_by($orderBy, $direction);

        $exe_query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $total_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        error_reporting(0);
        #last_query();
        #raqa.form_applicant_id=rfaq.form_applicant_id AND
        $rows = $exe_query->num_rows();

        if ($rows > 0) {
            $records = $exe_query->result_array();
            $wrong_ans = 0;
            $right_ans = 0;
            if (!empty($records)) {
                $select_param_ary = [];
                $main_aray = [];
                $question_ans_array = [];

                foreach ($records as $key => $value) {
                    if (!in_array($value['question_topic'], $select_param_ary)) {
                        $select_param_ary[] = $value['question_topic'];

                        if ($value['is_answer_correct'] == 1) {
                            $right_ans = $right_ans + 1;
                        } else if ($value['is_answer_correct'] == 0) {
                            $wrong_ans = $wrong_ans + 1;
                        }
                    } else {
                        if ($value['is_answer_correct'] == 1) {
                            $right_ans = $right_ans + 1;
                        } else if ($value['is_answer_correct'] == 0) {
                            $wrong_ans = $wrong_ans + 1;
                        }
                    }

                    $temp['question'] = $value['question'];
                    $temp['question_type'] = $value['question_type'];
                    $temp['question_topic'] = $value['question_topic'];
                    $temp['training_category'] = $value['training_category'];
                    $temp['question_id'] = $value['question_id'];
                    $temp['form_applicant_id'] = !empty($data_row)?$data_row['form_applicant_id']:'';

                    $temp_ary = [];

                    if ($value['question_type'] != 4) {
                        $temp_detail = explode('#__BREAKER__#', $value['job_details']);
                        $temp_ary['is_answer'] = !empty($temp_detail[0]) ? explode(',', $temp_detail[0]) : [];
                        $temp_ary['option_value'] = !empty($temp_detail[1]) ? explode('#_BR_#', $temp_detail[1]) : [];
                        $temp_ary['serial'] = !empty($temp_detail[2]) ? explode(',', $temp_detail[2]) : [];
                        $new_applicant_ans = $new_applicant_key = [];

                        //  Get applicant answers
                        $temp_detail_ans = explode('#__BREAKER__#', $value['job_details_ans']);
                        $temp_ary_ans['is_answer'] = !empty($temp_detail_ans[0]) ? explode(',', $temp_detail[0]) : [];
                        $temp_ary_ans['option_value'] = !empty($temp_detail_ans[1]) ? explode('#_BR_#', $temp_detail[1]) : [];
                        $temp_ary_ans['serial'] = !empty($temp_detail_ans[2]) ? explode(',', $temp_detail[2]) : [];

                        $x_is_answer = isset($temp_detail_ans[0])  ? explode(',', $temp_detail_ans[0]) : [];
                        $y_option_value = !empty($temp_detail_ans[1]) ? explode('#_BR_#', $temp_detail_ans[1]) : [];
                        $z_option_key = !empty($temp_detail_ans[2]) ? explode(',', $temp_detail_ans[2]) : [];

                        if(!empty($x_is_answer)){
                            foreach ($x_is_answer as $key => $val1) {
                                $new_applicant_ans[] = (!empty($y_option_value))?$y_option_value[$key]:'';
                                $new_applicant_key[] = (!empty($z_option_key))?$z_option_key[$key]:'';
                            }
                        }

                        $temp['applicant_answer_key'] = implode(',',$new_applicant_key);
                        $temp['applicant_answer_val'] = implode(',',$new_applicant_ans);
                    } else {
                        $temp_ary['answer_key'] = $value['job_details'];
                        $temp['applicant_answer'] = $value['answer_text'];
                    }

                    $temp['is_correct'] = $value['is_answer_correct'];
                    $temp['answer_details'] = $temp_ary;

                    $created_date = ($value['created_date']!='')?' (' . $value['created_date'] . ')':'';
                    $temp['created_by'] = $value['created_by'] .$created_date;
                    $question_ans_array[] = $temp;
                }
                    #pr($main_aray);
                if (Count($graph_ary) == 1) {
                    $graph_ary[] = array('', 0, 0);
                }
                echo json_encode(array('status' => true, 'data' => $question_ans_array, 'right_ans' => $right_ans, 'wrong_ans' => $wrong_ans, 'total_row' => count($records), 'total_count'=>$total_count));
                exit();
            }
        } else {
            echo json_encode(array('status' => false));
            exit();
        }
    }
}

public function get_form_applicant_id($param){
    $taskId = !empty($param) && $param['taskId'] != '' ? $param['taskId'] : '';
    $applicant_id = !empty($param) && $param['applicant_id'] != '' ? $param['applicant_id'] : '';
    $form_applicant_id = $application_id = 0;
    if ($applicant_id != '' && $taskId != '') {
        $this->db->select('application_id');
        $this->db->from('tbl_recruitment_task_applicant as rta');
        $this->db->where(array('taskId'=>$taskId,'applicant_id'=>$applicant_id));
        $query = $this->db->get();
        $row = $query->row_array();
        $application_id = !empty($row)?$row['application_id']:'';

        $data = $this->basic_model->get_row('recruitment_task', $columns = array('form_id'), $id_array = array('id' => $taskId));
        $form_id = !empty($data) ? $data->form_id : '';

        if($application_id!='' && $form_id!=''){
            $data = $this->basic_model->get_row('recruitment_form_applicant', $columns = array('id'), $id_array = array('applicant_id' => $applicant_id,'application_id' => $application_id,'form_id' => $form_id,'archive' => 0));
            $form_applicant_id = !empty($data) ? $data->id : '';
        }
    }
    return ['form_applicant_id'=>$form_applicant_id,'application_id'=>$application_id];
}

public function update_answer($reqData) {
    $applicant_id = isset($reqData->data->form_applicant_id) && $reqData->data->form_applicant_id != '' ? $reqData->data->form_applicant_id : '';
    $taskId = isset($reqData->data->taskId) && $reqData->data->taskId != '' ? $reqData->data->taskId : '';
    $type = isset($reqData->data->type) && $reqData->data->type != '' ? $reqData->data->type : 'none';
    $form_applicant_id = isset($reqData->data->form_applicant_id) && $reqData->data->form_applicant_id != '' ? $reqData->data->form_applicant_id : 'none';

    if ($applicant_id == '' && $taskId == '' && $type == 'none') {
        echo json_encode(array('status' => false, 'msg' => 'Invalid request.'));
        exit();
    }

    $questionId = isset($reqData->data->questionId) && $reqData->data->questionId != '' ? $reqData->data->questionId : '-1';
    $answer_val = isset($reqData->data->answer_val) && $reqData->data->answer_val != '' ? $reqData->data->answer_val : '';
    $this->db->select(["id"]);
    $this->db->from('tbl_recruitment_form_applicant_question_answer rfaqa');
    $this->db->where(array('question'=> $questionId,'form_applicant_id'=>$form_applicant_id));
    $query = $this->db->get();
    #last_query();die;
    $data = $query->num_rows() > 0 ? $query->row() : [];

    if (!empty($data)) {
        $id = $data->id;
        $is_update = $this->basic_model->update_records('recruitment_form_applicant_question_answer', array('answer' => $answer_val), array('id' => $id));
        echo json_encode(array('status' => true, 'msg' => 'Updated successfully.'));
        exit();

    } else {
       $ids = $this->basic_model->insert_records('recruitment_form_applicant_question_answer', ['form_applicant_id'=>$form_applicant_id,'question'=> $questionId,'answer'=>$answer_val]);
       echo json_encode(array('status' => true, 'msg' => 'Updated successfully.'));
       exit();
   }
}

    /*
     * input quiz_id
     * return true
     */
    function archive_quiz($quiz_id){
        // archive form
        $update = ["archive" => 1];
        $where= ["taskId" => $quiz_id];
        $this->basic_model->update_records("recruitment_task_applicant", $update, $where);

        return true;
    }

    public function get_quiz_list(){
        $reqData=(array) api_request_handler();
        $response = $this->validate_task_data($reqData);
        $currtent_date_time = DATE_TIME;
        if($response['status']){
            $applicant_id=$reqData['applicant_id'];
            $interview_type=$reqData['interview_type_id'];
                $this->db->select(['rt.id','rt.start_datetime','rt.end_datetime','rt.task_name','rta.application_id']);
                $this->db->select("(CASE
                WHEN (select id from tbl_recruitment_form_applicant rfa where rfa.applicant_id = rta.applicant_id and rfa.application_id = rta.application_id and rfa.form_id = rt.form_id) and rt.task_status=4 THEN 'Submitted'
                WHEN ('".$currtent_date_time."' >= rt.start_datetime AND '".$currtent_date_time."' <= rt.end_datetime) and  rt.task_status!=4 and rt.task_status!=3 THEN 'Open'
                WHEN ('".$currtent_date_time."' >= rt.start_datetime AND '".$currtent_date_time."' <= rt.end_datetime) and  rt.task_status=3 THEN 'In-Progress'
                WHEN ('".$currtent_date_time."' > rt.end_datetime) THEN 'Expired'
                Else 'Scheduled' end
            ) as int_status");
                $this->db->from('tbl_recruitment_task rt');
                $this->db->join('tbl_recruitment_task_applicant as rta', 'rt.id = rta.taskId AND rta.archive = 0 and rta.applicant_id = ' . $this->db->escape_str($applicant_id, true), 'inner');
                $this->db->where_in('task_status', array(1,2,3,4,5));
                $res= $this->db->get();
                $data=$res->result_array();
                // 2-open , 3-inprogress, 4-submitted ,5-Expired
                foreach ($data as $val) {
                    if($val['int_status']=='Open'){
                        $upd_data["task_status"] = 2; // Open;
                        $where = array("id"=> $val['id']);
                        $this->basic_model->update_records("recruitment_task", $upd_data, $where);
                    }
                    if($val['int_status']=='Expired'){
                        $upd_data["task_status"] = 5; // Expired;
                        $where = array("id"=> $val['id']);
                        $this->basic_model->update_records("recruitment_task", $upd_data, $where);
                    }
                }

                $response = array('status' => true, 'data' => $data);
                echo json_encode($response);
                exit();
            }
            else{
                $response = array('status' => false, 'data' => "Some thing went wrong");
                echo json_encode($response);
                exit();
            }
        }




}
