<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class JobReadyTemplateDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "oa_job_ready_template.json");
        $queryData = (array) json_decode($json, true);
        $adminId = 20;
        $current_time = date('Y-m-d H:i:s');
        $parent_question_id = $follow_up_count = 0;

        foreach ($queryData as $obj) {
           
            //Start Template creation
            $assessment_template = [
                'title' => $obj['assessment_template']['title'] ?? null,
                'job_type' => $obj['assessment_template']['job_type'] ?? null,
                'location' => $obj['assessment_template']['location'] ?? null,
                'status' => $obj['assessment_template']['status'] ?? null,
                'archive' => 0,
                'created_by' => $adminId,
                'created_at' => $current_time,
            ];
            
            $assessment_template_id = DB::table('tbl_recruitment_oa_template')->insertGetId($assessment_template);           

            for ($i = 0; $i < count($obj['question_answers_list']); $i++) {
                $current_ques_follow_up_count = 0;
                if($obj['question_answers_list'][$i]['answer_type'] == 5)
                {
                    $follow_up_count = $current_ques_follow_up_count =  $obj['question_answers_list'][$i]['follow_up_questions_crp'];
                }
               
                $assessment_questions = [
                    'parent_question_id' => $parent_question_id,
                    'serial_no' => $obj['question_answers_list'][$i]['serial_no'] ?? null,
                    'question' => $obj['question_answers_list'][$i]['question'] ?? null,
                    'suggest_answer' => $obj['question_answers_list'][$i]['suggest_answer'] ?? null,
                    'follow_up_questions_crp' => $current_ques_follow_up_count,
                    'blank_question_type' => $obj['question_answers_list'][$i]['blank_question_type'] ?? 0,
                    'answer_type' => $obj['question_answers_list'][$i]['answer_type'] ?? null,
                    'is_mandatory' => $obj['question_answers_list'][$i]['is_mandatory'] ?? null,
                    'grade' => $obj['question_answers_list'][$i]['grade'] ?? null,
                    'oa_template_id' => $assessment_template_id ?? null,
                    'fill_up_formatted_question' => $obj['question_answers_list'][$i]['fill_up_formatted_question'] ?? null,
                    'created_by' => $adminId,
                    'created_at' => $current_time,
                ];
                $assessment_question_id = DB::table('tbl_recruitment_oa_questions')->insertGetId($assessment_questions);


                if(!empty($follow_up_count) && $follow_up_count == $current_ques_follow_up_count) {
                    $parent_question_id = $assessment_question_id;
                }else if($follow_up_count > 0) {
                    $follow_up_count = $follow_up_count-1;
                    if($follow_up_count == 0) {
                        $parent_question_id = 0; 
                    }
                }
                else{
                    $parent_question_id = 0;
                }
                
                if (!empty($assessment_question_id)) {
                    for ($j = 0; $j < count($obj['question_answers_list'][$i]['options']); $j++) {
                        //Skip to store options for comprehensive type
                        if(empty($obj['question_answers_list'][$i]['options']) || 
                        $obj['question_answers_list'][$i]['answer_type'] == 5 || 
                        ($obj['question_answers_list'][$i]['answer_type'] == 6 && 
                        $obj['question_answers_list'][$i]['blank_question_type'] == "0")) {
                            continue;
                        }

                        $assessment_qa_options = [
                            'oa_template_id' => $assessment_template_id ?? null,                            
                            'question_id' => $assessment_question_id ?? null,
                            'option' => $obj['question_answers_list'][$i]['options'][$j]['option'] ?? null,
                            'is_correct' => $obj['question_answers_list'][$i]['options'][$j]['is_correct'] ?? null,
                            'created_by' => $adminId,
                            'created_at' => $current_time,
                            'blank_question_position' => 
                                ($obj['question_answers_list'][$i]['options'][$j]['blank_question_position'] != null) ? $obj['question_answers_list'][$i]['options'][$j]['blank_question_position'] : null
                        ];                       
                        DB::table('tbl_recruitment_oa_answer_options')                  
                        ->insert($assessment_qa_options);                        
                    }
                }
            }
            //End Template creation
        }
    }
}
