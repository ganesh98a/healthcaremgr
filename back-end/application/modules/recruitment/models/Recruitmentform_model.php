<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model file that act as both data provider 
 * or data repository for RecruitmentForm controller actions
 */
class Recruitmentform_model extends \CI_Model {

    public function __construct() {
        parent::__construct();

        $this->load->helper(['array']);
        $this->load->model('recruitment/Recruitmentformapplicant_model');
        $this->load->model('common/Common_model');
    }

    /**
     * Finds all interview forms
     * 
     * @param array $request 
     * @param int $adminId
     * @return array 
     */
    public function find_all_forms(array $request, $adminId) {
        $limit = (int) element('pageSize', $request, 10);
        $page = (int) element('page', $request, 0);
        $sorted = element('sorted', $request, []);
        $filter = element('filtered', $request, []);

        $orderBy = $direction = '';

        $canViewAllForms = $this->is_admin($adminId);

        $orderBy = 'tbl_recruitment_form.id';
        $direction = 'DESC';

        // setup ORDER BY
        // keys of this var will correspond to react table's column 'accessor' or 'id' if accessor if a function
        $whitelistOrderBy = [
            'title' => 'tbl_recruitment_form.title',
            'category' => 'category',
            'question_count' => 'question_count',
            'author' => 'created_by_name',
            'date_created' => 'tbl_recruitment_form.date_created'
        ];
        if (!empty($sorted) && !empty($sorted[0]['id'])) {
            $orderBy = element($sorted[0]['id'], $whitelistOrderBy, 'title');
            $direction = !!$sorted[0]['desc'] ? 'DESC' : 'ASC';
        }

        // setup search
        $whitelistedFilters = [
            'srch_box' => 'tbl_recruitment_form.title',
        ];
        if (!empty($filter)) {
            $cat = element('category', $filter);
            if (!empty($cat)) {
                $this->db->where('interview_type', $cat);
            }

            $srch_box = element('srch_box', $filter);
            if (!$this->isEmptyOrWhiteSpace($srch_box)) {
                $this->db->group_start();
                foreach ($whitelistedFilters as $k => $col) {
                    $this->db->or_like($col, $srch_box);
                }
                $this->db->group_end();
            }
        }

        $columns = [
            'tbl_recruitment_form.*',
            "(
                SELECT 
                    name 
                FROM tbl_recruitment_interview_type 
                WHERE archive = 0 
                AND id = tbl_recruitment_form.interview_type
            ) 
            as category",
            "(
                SELECT 
                    COUNT(id) 
                FROM tbl_recruitment_additional_questions 
                WHERE archive = 0 
                AND form_id = tbl_recruitment_form.id
            ) 
            as question_count",
            "(
                SELECT 
                    CONCAT_WS(' ', m.firstname, m.lastname) 
                FROM tbl_member m WHERE archive = 0 
                AND uuid = tbl_recruitment_form.created_by
            ) 
            as created_by_name",
            "DATE_FORMAT(tbl_recruitment_form.date_created, '%d/%m/%Y') as date_created_d_m_y",
        ];

        $where = [
            'created_by' => $adminId,
            'archive' => 0,
        ];

        if ($canViewAllForms) {
            unset($where['created_by']);
        }

        // query builder
        $query = $this->db
                ->from('tbl_recruitment_form')
                ->where($where)
                ->order_by($orderBy, $direction)
                ->limit($limit, ($page * $limit))
                ->select('SQL_CALC_FOUND_ROWS ' . implode(', ', $columns), false)
                ->get();

        // $last_query = $this->db->last_query();

        $results = $query->result_array();

        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count')->row()->count;
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        // eager-load tbl_recruitment_interview_type
        $interviewTypes = array_reduce($this->db->get_where('tbl_recruitment_interview_type')->result_array(), function($acc, $curr) {
            $acc[$curr['id']] = $curr;
            return $acc;
        }, []);


        // add additional data
        foreach ($results as $i => $result) {
            $created_by = $this->db->get_where('tbl_member', ['uuid' => $result['created_by']])->row_array();

            $results[$i] = array_merge($result, [
                'interview_category' => element($result['interview_type'], $interviewTypes),
                'questions' => $this->db->get_where('tbl_recruitment_additional_questions', ['form_id' => $result['id']])->result_array(),
                'author' => $this->only($created_by, ['id', 'firstname', 'lastname', 'preferredname']),
            ]);
        }

        return [
            'count' => $dt_filtered_total,
            'data' => $results,
            'all_count' => $all_count,
        ];
    }

    /**
     * 
     * @param array $formData 
     * @param int $adminId 
     * @return array 
     */
    public function create(array $formData, $adminId) {
        $dataToBeInserted = [
            'title' => element('title', $formData, ''),
            'created_by' => (int) $adminId,
            'interview_type' => (int) element('category', $formData, 0),
            'date_created' => DATE_TIME,
        ];

        $this->db->insert('tbl_recruitment_form', $dataToBeInserted);
        $insertId = $this->db->insert_id();

        return [
            'status' => true,
            'msg' => 'Successfully created form',
            'data' => [
                'insert_id' => $insertId
            ]
        ];
    }

    /**
     * 
     * @param mixed $id 
     * @param mixed $formData 
     * @return void 
     * @todo Not yet implemented
     */
    public function update($id, $formData) {
        $formData = $this->only($formData, [
            'title',
            'category',
        ]);

        $dataToBeUpdated = [
            'title' => $formData['title'],
            'interview_type' => $formData['category'],
        ];

        $isUpdated = $this->db->update('tbl_recruitment_form', $dataToBeUpdated, [
            'id' => $id
        ]);

        if (!$isUpdated) {
            return [
                'status' => $isUpdated,
                'error' => 'Failed to update form',
                'data' => array_merge($formData, [
                    'id' => $id
                ])
            ];
        }

        return [
            'status' => $isUpdated,
            'msg' => 'Successfully updated form',
            'data' => array_merge($formData, [
                'id' => $id
            ])
        ];
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
     * fetches list of questions for a given form and all the answer options provided for
     * each question
     */
    public function get_form_questions_list($reqData, $submitted_res) {
        $form_id = $reqData['form_id'];
        $form_applicant_id = null;

        # checking if the same form by applicant for same application submitted?
        if ($submitted_res) {
            $dataToBeChecked = [
                'applicant_id' => element('applicant_id', $submitted_res, ''),
                'application_id' => element('application_id', $submitted_res, ''),
                'form_id' => $form_id
            ];

            if (isset($submitted_res['reference_id']) && $submitted_res['reference_id'])
                $dataToBeChecked['reference_id'] = $submitted_res['reference_id'];
            $form_applicant_id = $this->Recruitmentformapplicant_model->applicant_form_submitted($dataToBeChecked);
        }

        $question_db_table = "tbl_recruitment_additional_questions";
        if ($form_applicant_id)
            $question_db_table = "tbl_recruitment_form_applicant_question";

        $select_column = array("raq.id", "raq.question", "raq.status", 'raq.training_category', 'raq.question_type', "raq.display_order", "raq.is_answer_optional", "raq.is_required");
        $this->db->select($select_column);
        $this->db->select("(concat('QTN', raq.id)) as view_id");

        $this->db->from("{$question_db_table} as raq");
        $this->db->join("tbl_recruitment_interview_type as rit", "rit.id = raq.training_category", "INNER");

        $this->db->where('raq.archive', '0');
        $this->db->where('raq.status', 1);

        if ($form_applicant_id)
            $this->db->where('raq.form_applicant_id', $form_applicant_id);
        else
            $this->db->where('raq.form_id', $form_id);

        $this->db->order_by("display_order", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        # last_query();
        $dataResult = $query->result();

        if (!empty($dataResult)) {
            $question_id = array_column(obj_to_arr($dataResult), 'id');
            $question_id = empty($question_id) ? [0] : $question_id;
            $question_data = $this->get_answer_details($question_id, false, $form_applicant_id);

            foreach ($dataResult as $data) {
                # fetching the answer if the form was previously submitted
                # for short answer
                if ($form_applicant_id && $data->question_type == 4) {
                    $answer_text = $this->Recruitmentformapplicant_model->fetch_answer_provided_of_question($form_applicant_id, $data->id, "0");
                    if (empty($answer_text))
                        $answer_text = '';
                    $data->answer_text = $answer_text;
                }
                # for choice questions
                else if ($form_applicant_id && $data->question_type != 4) {
                    $answer_id = $this->Recruitmentformapplicant_model->fetch_answer_provided_of_question($form_applicant_id, $data->id, null);
                    $data->answer_id = $answer_id;
                }
                $data->status = (int) $data->status;
                $data->answers = isset($question_data[$data->id]) ? $question_data[$data->id] : [];
                ;
            }
        }

        return $dataResult;
    }

    /**
     * to fetch the answer options of a given question
     */
    public function get_answer_details($qid, $simple_array = false, $form_applicant_id = null) {
        if ($form_applicant_id)
            $tbl_question_answer = TBL_PREFIX . 'recruitment_form_applicant_question_answer';
        else
            $tbl_question_answer = TBL_PREFIX . 'recruitment_additional_questions_answer';

        $select_answer_column = array($tbl_question_answer . ".id as answer_id", $tbl_question_answer . ".answer as checked", $tbl_question_answer . ".question_option as value", $tbl_question_answer . ".serial as label", $tbl_question_answer . ".question as q_id");
        $this->db->select($select_answer_column);
        $this->db->from($tbl_question_answer);

        if (!$form_applicant_id)
            $this->db->where('archive', 0);
        $this->db->where_in('question', $qid);

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
     * fetches the details of any form submitted for a given applicant and his application
     * inputs: form_id, applicant_id and application_id
     */
    function get_applicant_job_regarding_details($reqData) {
        $this->db->select(["rfa.id", "rfa.form_id", "rfa.applicant_id", "rfa.application_id", "reference_id"]);
        $this->db->select("(select rf.title from tbl_recruitment_form as rf where rf.id = rfa.form_id) as form");
        $this->db->select("(select concat_ws(' ',firstname,lastname) from tbl_member as m where m.id = rfa.completed_by) as completed_by");
        $this->db->from("tbl_recruitment_form_applicant as rfa");
        $this->db->where("rfa.form_id", $reqData->form_id);
        $this->db->where("rfa.archive", 0);
        $this->db->where("rfa.applicant_id", $reqData->applicantId);

        #FIXTHIS, application_id is required because form submittion is for each applicant & his application
        $this->db->where("rfa.application_id", $reqData->application_id);

        if ($reqData->interview_type == "reference_check") {
            $this->db->where("rfa.reference_id", $reqData->reference_id);
        }


        $res = $this->db->get()->row_array();

        if (!empty($res)) {
            $res['form_id'] = ["value" => $res['form_id'], "label" => $res['form']];
        }

        return $res;
    }

    /**
     * returns the list of forms for a given interview type key
     */
    function get_question_form_option($data) {
        $this->db->select(["rf.title as label", "rf.id as value"]);
        $this->db->from("tbl_recruitment_form as rf");
        $this->db->join("tbl_recruitment_interview_type as rit", "rit.id = rf.interview_type AND rit.archive = 0", "INNER");
        $this->db->where(["rf.archive" => 0, "rit.key_type" => $data['interview_type']]);

        return $this->db->get()->result();
    }

    /*
     * check form is active in any interview 
     * like job and group/cab
     */
    function check_form_already_active_in_interview($formId) {
        $sql = [];
                
        // query for check form
        $this->db->select(["id", "start_datetime", "end_datetime", "'1' as type"]);
        $this->db->from("tbl_recruitment_task");
        $this->db->where("form_id", $formId);
        $this->db->where("status", 1);
        $this->db->where("(NOW() between start_datetime AND end_datetime or  start_datetime > NOW())", null, null);
        $sql[] = $this->db->get_compiled_select();

        $this->db->select(["id, from_date as start_datetime, to_date as end_datetime, '2' as type"]);
        $this->db->from("tbl_recruitment_job");
        $this->db->where("archive", 0);
        $this->db->where("(CURDATE() between from_date AND to_date or  from_date > CURDATE())", null, false);
        $this->db->where("id IN (select job_id from tbl_recruitment_job_forms where form_id = " . addslashes($formId) . ")", null, false);
        $sql[] = $this->db->get_compiled_select();
        
        $sql = implode(' union ', $sql);
        $query = $this->db->query($sql);

        $result = $query->result();
        
        if(!empty($result)){
            $res = ["status" => false, "error" => "Form is active either in job or Group interview/CAB interview."];
        }else{
            $res = ["status" => true];
        }
        return $res;
    }
    
    /*
     * input formId
     * return true
     */
    function archive_form($formId){
        // archive form
        $update = ["archive" => 1, "date_updated" => DATE_TIME];
        $where= ["id" => $formId];
        $this->basic_model->update_records("recruitment_form", $update, $where);
        
        return true;
    }
    
    /*
     * input formId
     * use : archive all question who come in given formId
     * return true
     */
    function archive_all_question_of_given_form_id($formId){
        $update = ["archive" => 0, "updated" => DATE_TIME];
        $where= ["form_id" => $formId];
        $this->basic_model->update_records("recruitment_additional_questions", $update, $where);
        
        return true;
    }

     /**
     * returns the list of forms
     */
    function get_question_form_template($data) {
        $search = $data['search'];
        $this->db->select(["rf.title as label", "rf.id as value", "rf.interview_type as interview_type"]);
        $this->db->from("tbl_recruitment_form as rf");
        $this->db->like('rf.title', $search);
        $this->db->where(["rf.archive" => 0]);

        return $this->db->get()->result();
    }

    /**
     * returns the list of applications
     */
    function get_all_applications($data) {
        $search = $data['search'];
        $this->db->select([ "raaa.id as value", "CONCAT('',raaa.id) as label"]);
        $this->db->from("tbl_recruitment_applicant_applied_application as raaa");
        $this->db->like("CONCAT('APPs',raaa.id)", $search);
        $this->db->where("raaa.status !=" , 3);

        return $this->db->get()->result();
    }

    /**
     * Save form
     */
    function save_form($post_data, $adminId) {
        $applicant_id = $post_data['applicant_id'];
        $related_applicant_id = $applicant_id;
        $applicant_form_id = $post_data['applicant_form_id'] ?? 0;
        $currtent_date_time = DATE_TIME;
        // Check applicat_id is exist. Using id
        $where = array('id' => $post_data['related_to']);
        $colown = array('id', 'applicant_id');
        $check_application = $this->basic_model->get_record_where('recruitment_applicant_applied_application', $colown, $where);
        if (isset($check_application) == true && empty($check_application) == false) {
            $related_applicant_id = $check_application[0]->applicant_id;
        }

        $save_data = array(
            'application_id'=> $post_data['related_to'],
            'applicant_id'=> $related_applicant_id,
            'title'=> $post_data['title'],
            'owner'=> $post_data['owner'] && $post_data['owner'] != '' ?  $post_data['owner'] : Null,
            'form_id' => $post_data['form_id'],
            'status' => 1 ,//draft,
            'start_datetime'=>$post_data['form_start_datetime'] != '' ? date("Y-m-d H:i:s", strtotime($post_data['form_start_datetime'])) : '',
            'end_datetime'=>$post_data['form_end_datetime'] != '' ? date("Y-m-d H:i:s", strtotime($post_data['form_end_datetime'])) : '',
            'date_created'=> DATE_TIME,
            'created_by' => $adminId
        );

        if($save_data['start_datetime'] !='' && $save_data['end_datetime']!=''){
            if((strtotime($save_data['start_datetime'])) > (strtotime($save_data['end_datetime']))){
                return ['status' => false, 'error' => "Incorrect form start & end date/time"];
            } 
                    
            if($save_data['start_datetime'] == $save_data['end_datetime']){
                return ['status' => false, 'error' => "End time should be greater than start time"];
            }    
        }

        if($post_data['interview_type_id']==4){
            $upd_data["referred_by"] = $post_data['referred_by'];           
            $where = [ "id" => $post_data['application_id'], "applicant_id" => $post_data['applicant_id']];
            $this->basic_model->update_records("recruitment_applicant_applied_application", $upd_data, $where);
        }
        if($applicant_form_id){
            // Update applicant form data
            $form_id = $applicant_form_id;
            $save_task_data["date_updated"] = DATE_TIME;
            $save_task_data["updated_by"] = $adminId;
            $this->Basic_model->update_records("recruitment_form_applicant", $save_data, ["id" => $applicant_form_id]);
        }else{
            $form_id = $this->Basic_model->insert_records('recruitment_form_applicant', $save_data);
        }

        if (!$form_id) {
            return ['status' => false, 'error' => "Form is not created. something went wrong"];
        }

        return ['status' => true, 'msg' => "Form created successfully", 'form_id' => $form_id];
    }

    public function get_form_list_by_id($reqData,$bu_data) {
        if (!empty($reqData)) {
            $application_id = json_decode($reqData->application_id);
            $applicant_id = json_decode($reqData->applicant_id);

            $created_by_sub_query = $this->get_created_updated_by_sub_query('created_by','raaa');

            $limit = $reqData->pageSize ?? 0;
            $page = $reqData->page ?? 0;
            $sorted = $reqData->sorted;
            $filter = $reqData->filtered;
            $orderBy = '';
            $direction = '';

            $src_columns = array("raaa.id", "raaa.title", "raaa.status", "raaa.created_by");
            $available_columns = array("id", "title", "status", "created_by");
            if (!empty($sorted)) {
                if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_columns) ) {
                    $orderBy = $sorted[0]->id == 'id' ? 'raaa.id' : 'raaa.'.$sorted[0]->id;
                    $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
                }
            } else {
                $orderBy = 'raaa.id';
                $direction = 'DESC';
            }
            $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

            // Filter by status
            if (!empty($filter->filter_status)) {
                if ($filter->filter_status === "draft") {
                    $this->db->where('raaa.status', 1);
                } else if ($filter->filter_status === "completed") {
                    $this->db->where('raaa.status', 2);
                }
            }

            $select_column = array('raaa.*', 'rf.title as form_template');

            $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

            $this->db->select("(" . $created_by_sub_query . ") as created_by");
            $this->db->select("(
                CASE 
                    WHEN raaa.status = 1 THEN 'Draft'
                    WHEN raaa.status = 2 THEN 'Completed'
                ELSE ''
                END

                ) as status");
            $this->db->from(TBL_PREFIX . 'recruitment_form_applicant as raaa');
            $this->db->join(TBL_PREFIX . 'recruitment_form as rf', 'rf.id = raaa.form_id', 'INNER');
            if($this->Common_model->check_is_bu_unit($bu_data)){
                $this->db->where([ 'applicant_id' => $applicant_id, 'application_id' => $application_id, 'raaa.task_id'=>0 , 'raaa.archive'=>0,'rf.bu_id' => $bu_data->business_unit['bu_id']]);
            }else{
                $this->db->where([ 'applicant_id' => $applicant_id, 'application_id' => $application_id, 'raaa.task_id'=>0 , 'raaa.archive'=>0]);
            }
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

    /**
     * Get form details
     */
    function get_form_detail_by_id($reqData) {

        $form_id = $reqData->form_id;

        $created_by_sub_query = $this->get_created_updated_by_sub_query('created_by','rfa');
        $updated_by_sub_query = $this->get_created_updated_by_sub_query('updated_by','rfa');
        $owner_sub_query = $this->get_created_updated_by_sub_query('owner','rfa');

        $this->db->select([ "rfa.*", "CONCAT('',rfa.application_id) as application","raaa.referred_by","raaa.referred_phone","raaa.referred_email","rf.interview_type","rf.title as form_title"]);
        $this->db->from("tbl_recruitment_form_applicant as rfa");
        $this->db->join('tbl_recruitment_form as rf', 'rf.id = rfa.form_id', 'inner');
        $this->db->join('tbl_recruitment_applicant_applied_application as raaa', 'raaa.id = rfa.application_id AND raaa.applicant_id = rfa.applicant_id', 'inner');
        $this->db->select("(" . $created_by_sub_query . ") as created_by");
        $this->db->select("(" . $updated_by_sub_query . ") as updated_by");
        $this->db->select("(" . $owner_sub_query . ") as owner_name");
        $this->db->where("rfa.id" , $form_id);
        $query = $this->db->get();
        $form = $query->result_array();
        $form = $query->num_rows() > 0 ? $form[0] : [];

        if(!empty($form['start_datetime']) && $form['start_datetime'] != "0000-00-00 00:00:00"){
            $form['form_start_time'] = get_time_id_from_series($form['start_datetime']);
            $form['form_start_date'] = $form['start_datetime'];
    }
        else {
            $form['start_datetime'] = '';
            $form['form_start_time'] = '';
            $form['form_start_date'] = '';
        }

        if(!empty($form['end_datetime']) && $form['end_datetime'] != "0000-00-00 00:00:00"){
            $form['form_end_time'] = get_time_id_from_series($form['end_datetime']);
            $form['form_end_date'] = $form['end_datetime'];
        }
        else {
            $form['form_end_datetime'] = '';
            $form['form_end_time'] = '';
            $form['form_end_date'] = '';
        }

        if (!$form) {
            return ['status' => false, 'error' => "Form is not exist. something went wrong"];
        }

        return ['status' => true, 'msg' => "Form detail fetched successfully", 'data' => $form];
    }

    /**
     * Updae applicant form status
     */
    function update_form_status($reqData, $adminId) {

        $form_id = $reqData->form_id;
        $status = $reqData->status;

        // update applicant form status
        $upd_data["date_updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["status"] = $status;
        $where = [ "id" => $form_id];
        $form_data = $this->get_form_detail_by_id($reqData);
        if (empty($form_data)) {
            $response = ['status' => false, 'error' => "Form does not exist anymore."];
            return $response;
        }
        $result = $this->basic_model->update_records("recruitment_form_applicant", $upd_data, $where);
        // Feed updating
        $dataToBeUpdated = [
            'status' =>$status
        ];
        
        $this->load->model('recruitment/Recruitment_form_applicant_history_model');
        $this->Recruitment_form_applicant_history_model->updateHistory($form_data['data'], $dataToBeUpdated, $adminId);

        if (!$result) {
            return ['status' => false, 'error' => "Form status is not updated. something went wrong"];
        }

        return ['status' => true, 'msg' => "Form status updated successfully", 'data' => $result];
    }

    /**
     * fetches list of questions for a given form and all the answer options provided for
     * each question
     */
    public function get_questions_list_by_applicant_form_id($reqData) {
        $form_applicant_id = $reqData->form_applicant_id;
        $form_id = $reqData->form_id?? 0;
        // Check form_applicant_id is exist. Using title
        $where = array('form_applicant_id' => $form_applicant_id);
        $colown = array('id', 'question');
        $check_form = $this->basic_model->get_record_where('recruitment_form_applicant_question', $colown, $where);
        // pr($check_form);
        $question_db_table = "tbl_recruitment_additional_questions";
        if ($check_form){
            $question_db_table = "tbl_recruitment_form_applicant_question";
        }

        if ($form_applicant_id != '') {

            $limit = $reqData->pageSize;
            $page = $reqData->page;
            $sorted = $reqData->sorted;
            $filter = $reqData->filtered;
            $orderBy = '';
            $direction = '';

            $src_columns = array("raq.question");
            $available_column = array("id", "question", "status", 'training_category', 'question_type', "display_order", "is_answer_optional", "is_required", "start_datetime", "end_datetime");
            if (!empty($sorted)) {
                if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_column) ) {
                    $orderBy = $sorted[0]->id == 'id' ? 'raq.id' : $sorted[0]->id;
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

            $select_column = array("raq.id", "raq.question", "raq.status", 'raq.training_category', 'raq.question_type', "raq.display_order", "raq.is_answer_optional", "raq.is_required", "rfa.start_datetime", "rfa.end_datetime");

            $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

            $this->db->select("(concat('QTN', raq.id)) as view_id");

            $this->db->from("{$question_db_table} as raq");
            if ($check_form)
            {
                $joinField = "rfa.id = raq.form_applicant_id";
            } else {
                if ($form_id != 0 && $form_id != '') {
                    $joinField = "rfa.form_id = raq.form_id AND rfa.form_id = $form_id";
                } else {
                    $joinField = "rfa.form_id = raq.form_id";
                }
                
            }
            $this->db->join("tbl_recruitment_form_applicant as rfa", $joinField,"INNER");
            $this->db->where('raq.archive', '0');
            $this->db->where('raq.status', 1);
            $this->db->where('rfa.id', $form_applicant_id);
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
            if (!empty($dataResult)) {
                $question_id = array_column(obj_to_arr($dataResult), 'id');
                $question_id = empty($question_id) ? [0] : $question_id;
                $question_data = $this->get_answer_details($question_id, false, $check_form);

                foreach ($dataResult as $data) {
                    # fetching the answer if the form was previously submitted
                    # for short answer
                    if ($form_applicant_id && $data->question_type == 4) {
                        $answer_text = $this->Recruitmentformapplicant_model->fetch_answer_provided_of_question($form_applicant_id, $data->id, "0");
                        if (empty($answer_text))
                            $answer_text = '';
                        $data->answer_text = $answer_text;
                    }
                    # for choice questions
                    else if ($form_applicant_id && $data->question_type != 4) {
                        $answer_id = $this->Recruitmentformapplicant_model->fetch_answer_provided_of_question($form_applicant_id, $data->id, null);
                        $data->answer_id = $answer_id;
                    }
                    $data->status = (int) $data->status;
                    $data->answers = isset($question_data[$data->id]) ? $question_data[$data->id] : [];
                    ;
                }
            }

            $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'status' => true, 'total_count' => $total_count);
            return $return;
        } else {
            $return = array('count' => 0, 'data' => [], 'status' => false, 'total_count' => 0);
            return $return;
        }
    }

    function get_curent_staff_detail_by_id($reqData, $adminId)
    {
        $this->db->select("(CASE WHEN a.is_super_admin = 1 THEN 1 
            WHEN ((select id from tbl_recruitment_staff as rs where rs.adminId = a.uuid and rs.archive= 0 AND rs.status = 1 AND rs.approval_permission = 1) > 0) THEN 1
            ELSE 0 end) as is_recruitment_user");
        $this->db->select(array('concat(a.firstname," ",a.lastname) as label', 'a.uuid as value', '"2" as primary_recruiter'), false);
        $this->db->from('tbl_member as a');
        $this->db->join('tbl_department as d', 'd.id = a.department AND d.short_code = "internal_staff"', 'inner');

        $this->db->where('a.archive', 0);
        $this->db->where('a.uuid', $adminId);
        $this->db->where('a.status', 1);
        $this->db->having("is_recruitment_user", 1);

        $query = $this->db->get();

        $result = $query->result_array();
        $result = $query->num_rows() > 0 ? $result[0] : '';
        $return = array('count' => 0, 'data' => $result, 'status' => true, 'total_count' => 0);
        return $return;
    }

    /*
     * input formId
     * return true
     */
    function archive_form_applicant($form_id, $adminId){
        // archive form
        $update = ["archive" => 1, "date_updated" => DATE_TIME, "updated_by"=>$adminId];
        $where= ["id" => $form_id];
        $this->basic_model->update_records("recruitment_form_applicant", $update, $where);
        
        return true;
    }
	
	public function get_applicant_info($data){
        $application_id =  $data['data']['application']??0;
        $result = '';
        if($application_id){
            $this->db->select(array('a.firstname', 'a.lastname', 'b.email', 'd.title'));
            $this->db->from('tbl_recruitment_applicant as a');
            $this->db->join('tbl_recruitment_applicant_email as b', ' a.id = b.applicant_id AND b.primary_email = 1', 'INNER');
            $this->db->join('tbl_recruitment_applicant_applied_application as c', ' c.applicant_id = a.id', 'INNER');
            $this->db->join('tbl_recruitment_job as d', ' d.id = c.jobId', 'INNER');
            $this->db->where('c.id', $application_id );
            $query  = $this->db->get();
            $result = $query->result_array();
            $result = $query->num_rows() > 0 ? $result[0] : '';
            
        }
        return $result;
	}
	
	public function get_question_list_for_pdf($data){
		$form_applicant_id = $data['data']['id'];
		
		$where = array('form_applicant_id' => $form_applicant_id);
        $colown = array('id', 'question');
        $check_form = $this->basic_model->get_record_where('recruitment_form_applicant_question', $colown, $where);
        
		if($check_form) {
			$this->db->select(array('a.question', 'd.answer_text', 'c.question_option'));
			$this->db->from('tbl_recruitment_form_applicant_question as a');
			$this->db->join('tbl_recruitment_form_applicant_answer as d', ' d.question_id = a.id ', 'LEFT');
            $this->db->join('tbl_recruitment_form_applicant_question_answer as c', ' c.id = d.answer_id AND c.answer = 1 ', 'LEFT');
			
			$this->db->where('a.form_applicant_id', $form_applicant_id );
			$query = $this->db->get();
		} else {
			$form_id = $data['data']['form_id'];
            $select_column = array("raq.id", "raq.question", "raq.status");
            $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
            $this->db->select("(concat('QTN', raq.id)) as view_id");

            $this->db->from("tbl_recruitment_additional_questions as raq");

            if ($form_id != 0 && $form_id != '') {
                $joinField = "rfa.form_id = raq.form_id AND rfa.form_id = $form_id";
            } else {
                $joinField = "rfa.form_id = raq.form_id";
            }

            $this->db->join("tbl_recruitment_form_applicant as rfa", $joinField,"INNER");
            $this->db->where('raq.archive', '0');
            $this->db->where('raq.status', 1);
            $this->db->where('rfa.id', $form_applicant_id);
            $this->db->order_by(' raq.display_order ', ' ASC ');
            $query = $this->db->get();
		}
		return $query->result_array();
	}
	
	public function generate_download_form_pdf($data, $questionList, $applicantInfo){
        $view = 'recruitment/applicant_download_form';

        $pdf_data = [
            "base_url" => base_url(),
			"data" => $data,
			"questionList" => $questionList,
			"applicantInfo" => $applicantInfo
        ];
        # loading the html content from base PDF files
        $main_html = $this->load->view($view, $pdf_data, true);        

        # mpdf initializing
        require_once APPPATH . 'third_party/mpdf7/vendor/autoload.php';
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_top' => '10',
            'margin_bottom' => '10',
            'margin_left' => '10',
            'margin_right' => '10',
            'setAutoBottomMargin' => 'stretch',
            'orientation' => 'P'
        ]);
		
		$mpdf->SetHTMLFooter('<div style="text-align: center">{PAGENO}</div>');
        $mpdf->WriteHTML($main_html);
        
        $applicantId = $data['data']['applicant_id'];

		$fname = 'AttachmentForm' . $applicantInfo['firstname'].$applicantInfo['lastname'].'_'.date('dmY');
		$filename = $fname  .'.pdf';
        $pdfFilePath =  APPLICANT_ATTACHMENT_UPLOAD_PATH . $filename;

        $mpdf->Output($pdfFilePath, "F");
		
        require_once APPPATH . 'Classes/common/Aws_file_upload.php';        
        $awsFileupload = new Aws_file_upload();

        $config['upload_path'] =  S3_DOWNLOAD_FORM_ATTACHMENT_UPLOAD_PATH . $applicantId. '/' . $filename;

        $config['file_name'] = $filename;
        $config['attachment_path'] = $pdfFilePath;
        $config['directory_name'] = NULL;
       
        $config['adminId'] = $applicantId;
        $config['title'] = "Download Form - $applicantId ";       
        $config['module_id'] = REQUIRMENT_MODULE_ID;
        $config['created_by'] = $applicantId ?? NULL;

        $s3documentAttachment = $awsFileupload->upload_from_app_to_s3($config, FALSE);
        
        if (!isset($s3documentAttachment) || !$s3documentAttachment['aws_uploaded_flag']) {
            // return error comes in file uploading
            echo json_encode(array('status' => FALSE, 'error' => 'Something went wrong'));
            exit();
        } else {
            $file_path = str_replace('=', '%3D%3D', base64_encode($s3documentAttachment['file_path']));

            $file_path = 'mediaShowForm/m/' . $applicantId. '/' . $file_path. "?download_as=$filename&s3=true";
            #Remove files from local server after files moved to S3
            unlink($pdfFilePath);
        return ['status' => TRUE, 'msg' => "Successfully created PDF", 'preview_url' => $file_path];
        }
	}
    
    
    public function get_oa_list_by_id($reqData,$app_data) {
        if (!empty($reqData)) {
            $application_id = json_decode($reqData->application_id);
            $applicant_id = json_decode($reqData->applicant_id);

            $limit = $reqData->pageSize;
            $page = $reqData->page;
            $sorted = $reqData->sorted;
            $filter = $reqData->filtered;
            $orderBy = '';
            $direction = '';

            $src_columns = array("rja.id", "rja.status", "rja.created_by");

            if (!empty($sorted)) {
                if (!empty($sorted[0]->id)) {
                    $orderBy = $sorted[0]->id == 'id' ? 'rja.id' : $sorted[0]->id;
                    $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
                }
            } else {
                $orderBy = 'rja.id';
                $direction = 'DESC';
            }

            // Filter by status
            if (!empty($filter->filter_status)) {
                if ($filter->filter_status === "Sent") {
                    $this->db->where('rja.status', 1);
                } else if ($filter->filter_status === "In progress") {
                    $this->db->where('rja.status', 2);
                } else if ($filter->filter_status === "Submitted") {
                    $this->db->where('rja.status', 3);
                } else if ($filter->filter_status === "Completed") {
                    $this->db->where('rja.status', 4);
                } else if ($filter->filter_status === "Link Expired") {
                    $this->db->where('rja.status', 5);
                } else if ($filter->filter_status === "Error") {
                    $this->db->where('rja.status', 6);
                }else if ($filter->filter_status === "Moodle") {
                    $this->db->where('rja.status', 7);
                }
                else if ($filter->filter_status === "Session Expired") {
                    $this->db->where('rja.status', 8);
                }
            }

            $select_column = array('rja.*');

            $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

            $this->db->select("(
                CASE 
                    WHEN rja.status = 1 THEN 'Sent'
                    WHEN rja.status = 2 THEN 'In progress'
                    WHEN rja.status = 3 THEN 'Submitted'
                    WHEN rja.status = 4 THEN  (concat( 'Completed',' (',`rja`.`percentage`,'%',')') )
                    WHEN rja.status = 5 THEN 'Link Expired'
                    WHEN rja.status = 6 THEN 'Error'
                    WHEN rja.status = 7 THEN 'Moodle'
                    WHEN rja.status = 8 THEN 'Session Expired'
                ELSE ''
                END

                ) as status_msg");
            
            $this->db->select("(concat(rja.marks_scored, ' / ' ,  rja.total_grade)) as total_marks");
            $this->db->from(TBL_PREFIX . 'recruitment_job_assessment as rja');
            $this->db->where([ 'rja.applicant_id' => $applicant_id, 'rja.application_id' => $application_id]);
            $this->db->join('tbl_recruitment_applicant_applied_application as c', ' c.id = rja.application_id', 'INNER');
            if($this->Common_model->check_is_bu_unit($app_data)){
                $this->db->where([ 'c.bu_id' => $app_data->business_unit['bu_id']]);
            }
            $this->db->order_by($orderBy, $direction);
            $this->db->limit($limit, ($page * $limit));

            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

            if ($dt_filtered_total % $limit == 0) {
                $dt_filtered_total = ($dt_filtered_total / $limit);
            } else {
                $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
            }

            $dataResult = $query->result();

            return array('count' => $dt_filtered_total, 'data' => $dataResult, 'status' => true, 'total_count' => $total_count);
        }
    }
}
