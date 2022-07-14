<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model file that act as both data provider 
 * or data repository for RecruitmentFormApplicant controller actions
 */
class Recruitmentformapplicant_model extends \CI_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper(['array']);
    }

    /**
     * check if the form is already submitted by an applicant for the same application
     */
    function applicant_form_submitted($formData)
    {
        $dataToBeChecked = [
            'applicant_id' => element('applicant_id', $formData, ''),
            'application_id' => element('application_id', $formData, ''),
            'form_id' => element('form_id', $formData, ''),
            'task_id' => element('task_id', $formData, ''),
			'archive' => 0,
        ];
        if(isset($formData['reference_id']) && $formData['reference_id'])
        $dataToBeChecked['reference_id'] = $formData['reference_id'];

        $this->db->from('tbl_recruitment_form_applicant');
        $this->db->where($dataToBeChecked);
        $row = $this->db->get()->row();
        if (isset($row)) {
            return $row->id;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param array $formData 
     * @param int $adminId 
     * @return inserted id 
     */
    public function create(array $formData, $adminId)
    {
        $title = '';
        $applicant_id = element('applicant_id', $formData, '');
        $this->db->select([ 'concat(firstname," ",lastname) as name' ]);
        $applicant = $this->db
            ->get_where('tbl_recruitment_applicant', [ 'id' => $applicant_id])
            ->result_array();
        if (isset($applicant) == true && isset($applicant[0]) == true ) {
            $title = $applicant[0]['name'];
        }

        $form_id = element('form_id', $formData, '');
        $interview_type = $this->db->query('SELECT tbl_recruitment_form.title as form_title, tbl_recruitment_form.id, tbl_recruitment_form.interview_type, rit.name as title FROM `tbl_recruitment_form`
        INNER JOIN tbl_recruitment_interview_type rit ON rit.id = tbl_recruitment_form.interview_type  WHERE tbl_recruitment_form.id = '.$this->db->escape_str($form_id, true));
        $interview_type = $interview_type->result_array();
        if (isset($interview_type) == true && isset($interview_type[0]) == true ) {
            $title .= ' '. $interview_type[0]['form_title'];
        }
        $cur_date_time = DATE_TIME;
        $dataToBeProcessed = [
            'title' => $title,
            'applicant_id' => element('applicant_id', $formData, ''),
            'application_id' => element('application_id', $formData, ''),
            'form_id' => element('form_id', $formData, ''),
            'completed_by' => (int) $adminId,
            'date_created' => DATE_TIME,
            'date_updated' => DATE_TIME,
            'status' => 2,
            'start_datetime' => DATE_TIME,
            'end_datetime' => date("Y-m-d H:i:s", strtotime("{$cur_date_time} +1 hour")),
            'is_sys_generater' => 1,
        ];
		
		if($formData["interview_type"] === "reference_check"){
			$dataToBeProcessed["reference_id"] = element('reference_id', $formData, '');
		}
		
        $this->db->insert('tbl_recruitment_form_applicant', $dataToBeProcessed);
        return $this->db->insert_id();
    }

    /**
     * 
     * @param array $formData 
     * @param int $adminId 
     * @return inserted id 
     */
    public function form_applicant_create(array $formData, $adminId)
    {
        $title = '';
        $applicant_id = element('applicant_id', $formData, '');
        $this->db->select([ 'concat(firstname," ",lastname) as name' ]);
        $applicant = $this->db
            ->get_where('tbl_recruitment_applicant', [ 'id' => $applicant_id])
            ->result_array();
        if (isset($applicant) == true && isset($applicant[0]) == true ) {
            $title = $applicant[0]['name'];
        }

        $form_id = element('form_id', $formData, '');
        $interview_type = $this->db->query('SELECT tbl_recruitment_form.title as form_title, tbl_recruitment_form.id, tbl_recruitment_form.interview_type, rit.name as title FROM `tbl_recruitment_form`
        INNER JOIN tbl_recruitment_interview_type rit ON rit.id = tbl_recruitment_form.interview_type  WHERE tbl_recruitment_form.id = '.$this->db->escape_str($form_id, true));
        $interview_type = $interview_type->result_array();
        if (isset($interview_type) == true && isset($interview_type[0]) == true ) {
            $title .= ' '. $interview_type[0]['form_title'];
        }

        $dataToBeProcessed = [
            'title' => $title,
            'applicant_id' => element('applicant_id', $formData, ''),
            'application_id' => element('application_id', $formData, ''),
            'form_id' => element('form_id', $formData, ''),
            'task_id' => element('task_id', $formData, ''),
            'completed_by' => (int) $adminId,
            'date_created' => DATE_TIME,
            'date_updated' => DATE_TIME,
            'status' => 2,
        ];
		
        $this->db->insert('tbl_recruitment_form_applicant', $dataToBeProcessed);
        return $this->db->insert_id();
    }

    /**
     * returns either the short answer text or the choice answer's id depending upon the question type
     * uses the form_applicant_id if the form was already submitted and queries "tbl_recruitment_form_applicant_answer" table
     */
    public function fetch_answer_provided_of_question($form_applicant_id, $qid, $aid=null) {
        $dataToBeChecked = [
            'form_applicant_id' => $form_applicant_id,
            'question_id' => $qid,
            'archive' => "0"
        ];
        if(isset($aid))
            $dataToBeChecked['answer_id'] = $aid;

        $this->db->from('tbl_recruitment_form_applicant_answer');
        $this->db->where($dataToBeChecked);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        
        # last_query();
        $dataResult = $query->result_array();;

        if (!empty($dataResult)) {
            if($dataResult[0]['answer_id'] == 0)
            return $dataResult[0]['answer_text'];

            $return_ids = null;
            foreach ($dataResult as $value) {
                $return_ids[] = $value['answer_id'];
            }
            return $return_ids;
        }
        return false;
    }
    /**
     * 
     * @param array $formData 
     * @param int $adminId 
     * @return 
     */
    public function update($id, array $formData, $adminId)
    {
        $dataToBeProcessed = [
            'applicant_id' => element('applicant_id', $formData, ''),
            'application_id' => element('application_id', $formData, ''),
            'form_id' => element('form_id', $formData, ''),
            'task_id' => element('task_id', $formData, ''),
            'completed_by' => (int) $adminId,
            'date_updated' => DATE_TIME,
        ];
        $this->db->update('tbl_recruitment_form_applicant', $dataToBeProcessed, ['id' => $id]);
        return true;
    }

    /**
     * inserts the combinations of questions and answers into tbl_recruitment_form_applicant_answer table
     * if the form was previously submitted then marking those entries as archive
     */
    function save_interview_answer_of_applicant($reqData)
    {
        $new_aid_mapping = null;
        
        # removing existing answers if they were previously submitted
        $this->basic_model->update_records("recruitment_form_applicant_answer", ["archive" => true], ["form_applicant_id" => $reqData["form_applicant_id"]]);
        $this->basic_model->delete_records("recruitment_form_applicant_question", ["form_applicant_id" => $reqData["form_applicant_id"]]);
        $this->basic_model->delete_records("recruitment_form_applicant_question_answer", ["form_applicant_id" => $reqData["form_applicant_id"]]);

        $question_ans = null;
        if (!empty($reqData['question_answers'])) {
            foreach ($reqData['question_answers'] as $val) {

                # adding a copy of the question for the submitted form
                $sub_qid = $this->basic_model->insert_records("recruitment_form_applicant_question", ["form_applicant_id" => $reqData["form_applicant_id"],"question" => $val['question'], "training_category" => $val['training_category'], "question_type" => $val['question_type'], "display_order" => $val['display_order'], "is_answer_optional" => $val['is_answer_optional'], "is_required" => $val['is_required'], "question_topic" => (isset($val['question_topic'])?$val['question_topic']:''), "created_by" => (isset($val['created_by'])?$val['created_by']:'0'), "status" => 1, "archive" => 0]);

                # are there answer options? let's store them separately
                if(isset($val['answers']) && !empty($val['answers'])) {
                    foreach ($val['answers'] as $anwer_option) {
                        $sub_aid = $this->basic_model->insert_records("recruitment_form_applicant_question_answer", ["form_applicant_id" => $reqData["form_applicant_id"],"question" => $sub_qid, "answer" => $anwer_option['checked'], "question_option" => $anwer_option['value'], "serial" => $anwer_option['label']]);

                        $new_aid_mapping[$anwer_option['answer_id']] = $sub_aid;
                    }
                }

                if (($val['question_type'] == 1 || $val['question_type'] == 2 || $val['question_type'] == 3) && !empty($val['answer_id'])) {
                    foreach ($val['answer_id'] as $answer_id) {
                        if(empty($answer_id)) continue;
                        
                        $question_ans[] = [
                            "form_applicant_id" => $reqData['form_applicant_id'],
                            "question_id" => $sub_qid,
                            "answer_id" => $new_aid_mapping[$answer_id],
                            "answer_text" => '',
                            "created" => DATE_TIME,
                            "archive" => 0,
                        ];
                    }
                } else if($val['question_type'] == 4 && !empty($val['answer_text'])) {
                    $question_ans[] = [
                        "form_applicant_id" => $reqData['form_applicant_id'],
                        "question_id" => $sub_qid,
                        "answer_id" => '',
                        "answer_text" => $val['answer_text'],
                        "created" => DATE_TIME,
                        "archive" => 0,
                    ];
                }
            }

            if(!empty($question_ans))
            $this->basic_model->insert_records("recruitment_form_applicant_answer", $question_ans, true);
        }
    } 
	
	function archive_form_interview($interview_form_id){
		$update_data = ["archive" => 1, "date_updated" => DATE_TIME];
		$where = ["id" => $interview_form_id];
		
		$this->basic_model->update_records("recruitment_form_applicant", $update_data, $where);
		return true;
	}
}
