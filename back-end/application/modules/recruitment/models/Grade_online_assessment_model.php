<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Grade_online_assessment_model extends Basic_model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function save_oa_results_from_recruiter($reqData, $adminId){

    /**
     * @todo:form validation check ,log ,Api documentation
     */
      $manual_eval_answers=$this->filter_question_by_type($reqData->{'question_answers_list'},array(4,6));
      $assessment_id = $reqData->{'assessment_details'}->{'id'};
      $applicant_id  = $reqData->{'assessment_details'}->{'applicant_id'};
      $application_id  = $reqData->{'assessment_details'}->{'application_id'};
      $questions_list = $reqData->{'question_answers_list'};

      # Auto insert answer by admin user
      $temp = [];
      for ($i = 0; $i < count($questions_list); $i++){ 
        $question_id = $questions_list[$i]->{'id'};
        $answer_type = $questions_list[$i]->{'answer_type'};
        $answer = $questions_list[$i]->{'answer'};
        $result = $questions_list[$i]->{'result'};

        $row = $this->basic_model->get_row('recruitment_oa_applicant_answer', ['id'], [ 'question_id'=> $question_id,'archive'=> 0 ]);
        
        if (empty($row)) {           
            $insertData = [];           
            $insertData['applicant_id'] = $applicant_id;
            $insertData['application_id'] = $application_id;
            $insertData['job_assessment_id'] = $assessment_id;
            $insertData['question_id'] = $question_id;
            $insertData['answer_id'] = $answer;
            
            $insertData['is_correct'] = $result;
            $insertData['archive'] = 0;
            $insertData['answer_type'] = $answer_type;
            $insertData['created_at'] = Date('Y-m-d H:i:s');

            # blank_question_type
            if ($answer_type == 6 && $questions_list[$i]->{'blank_question_type'} == 2) {
                $fill_up_formatted_question = $questions_list[$i]->{'fill_up_formatted_question'} ;
                $option_count = substr_count($fill_up_formatted_question, '{{SELECT_OPTION}}');
                $blank_position_arr = range(0, ($option_count-1));
                $insertData['answer_text'] = NULL;
                $insertData['is_correct'] =  json_encode(array_fill(0, $option_count, 0));
                $insertData['blank_question_position'] = json_encode($blank_position_arr) ?? NULL;
            } else if ($answer_type == 6 && $questions_list[$i]->{'blank_question_type'} == 1) {
                $fill_up_formatted_question = $questions_list[$i]->{'fill_up_formatted_question'} ;
                $option_count = substr_count($fill_up_formatted_question, '{{INPUT_OPTION}}');
                $blank_position_arr = range(0, ($option_count-1));
                $insertData['blank_question_position'] = json_encode($blank_position_arr) ?? NULL;
                $insertData['answer_text'] =  json_encode(array_fill(0, $option_count, ''));
                $insertData['answer_id'] = NULL;
            } else {
                $insertData['answer_text'] = NULL;
            }

            if (($answer_type == 1 || $answer_type == 2 || $answer_type == 3) && ($insertData['is_correct'] == NULL || $insertData['is_correct'] == '')) {
                $insertData['is_correct'] = 0;
            }

            # grade
            if ($answer_type != 6 && $answer_type != 4 ) {
                $manual_grade = 0;
            } else if ($answer_type == 6 && $questions_list[$i]->{'blank_question_type'} == 2) {
                $manual_grade = 0;
            } else {
                $manual_grade = $questions_list[$i]->{'score'} ?? NULL;
            }
            $insertData['grade'] = $manual_grade;
            $insertData['recruiter_comments'] = $questions_list[$i]->{'comments'} ?? NULL;
            $insertData['updated_at'] = DATE_TIME;
            $insertData['updated_by'] = $adminId;
            $insertData['insert_by_admin'] = 1;

            $this->basic_model->insert_records('recruitment_oa_applicant_answer', $insertData);
        }
      }

      for ($i = 0; $i < count($manual_eval_answers); $i++){
        $question_id = $manual_eval_answers[$i]->{'id'};
        $updateData = [
                      'recruiter_comments' => $manual_eval_answers[$i]->{'comments'} ?? '', 
                      'is_correct' => $manual_eval_answers[$i]->{'result'},
                      'grade'  => $manual_eval_answers[$i]->{'score'},
                      'updated_at' => DATE_TIME,
                      'updated_by' =>$adminId
                     ];

        $where = ['job_assessment_id' => $assessment_id ,'question_id' => $question_id,'application_id'=> $application_id, 'applicant_id' => $applicant_id];
        $this->basic_model->update_records('recruitment_oa_applicant_answer', $updateData, $where);
      }
      $assessment_update =[
        'total_grade'  => $reqData->{'grade'},
        'marks_scored'  => $reqData->{'score'},
        'percentage'  => $reqData->{'percentage'},
        'status' => OA_COMPLETED
      ];
      $this->basic_model->update_records('recruitment_job_assessment', $assessment_update, ['id' => $assessment_id]);

        // create feed for OA completion
        $dataToBeUpdated = [
            'oa_grade' => $assessment_update['marks_scored'].'/'.$assessment_update['total_grade'] . '('. $assessment_update['percentage'].'%)',
            'oa_marks_scored' => $assessment_update['marks_scored'],
            'oa_total_grade' => $assessment_update['total_grade'],
            'oa_percentage' => $assessment_update['percentage'],
            'oa_status' => OA_COMPLETED,           
            'applicant_name' => $reqData->{'applicant_name'},
            'application_id'=> $application_id,
            'admin_name'=> $reqData->{'admin_name'}
        ];
        
        $this->load->model('Online_assessment_model');
        $this->Online_assessment_model->updateHistory($dataToBeUpdated, $application_id, $adminId);
    

      return ['status'=>true, 'msg' => 'Assessment updated successfully'];
    }


     /**
     * Get applicant assessment by uuid
     * @param {str} $uuid
     */
    public function filter_question_by_type($question_answers_list, $question_type = [4,6])
    {
        return array_values(
            array_filter(
                $question_answers_list,
                function ($item) use (&$question_type) {
                    if($item->{'answer_type'}==4){
                        return  in_array( $item->{'answer_type'}, $question_type);
                    }
                    else if($item->{'answer_type'}==6){
                        return  $item->{'blank_question_type'}==1;
                    }
                    
                },
                0
            )
        );
    }

        /**
     * Get applicant assessment by uuid
     * @param {str} $uuid
     */
    function update_questions_answer_by_id($assessment_id,$question_id,$application_id,$applicant_id, $updateData) {
        $where = ['job_assessment_id' => $assessment_id ,'question_id' => $question_id,'application_id'=> $application_id, 'applicant_id' => $applicant_id];
        $updateAssessment = $this->basic_model->update_records('recruitment_oa_applicant_answer', $updateData, $where);
      
       
    }
}
