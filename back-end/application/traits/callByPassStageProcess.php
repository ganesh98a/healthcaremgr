<?php
trait callByPassStageProcess {

    public function group_interview_stage_bypass($applicantId=0,$adminId=0, $application_id = 0)
    {
        $result=['status'=>false,'error'=>'Something went wrong.'];
        if(defined('RECRUITMENT_BY_PASS_DEMO_STAGE')){
            if(RECRUITMENT_BY_PASS_DEMO_STAGE){
                $res =$this->checkApplicantTaskAndCreateTask_bypass($applicantId,$adminId,'group_interview', $application_id);
                if($res){
                    $result=['status'=>$res,'msg'=>'Bypass stage successfully.'];
                }
            }
        }
        return $result;
    }

    public function cab_interview_stage_bypass($applicantId=0,$adminId=0, $application_id = 0)
    {   $result=['status'=>false,'error'=>'Something went wrong.'];
    if(defined('RECRUITMENT_BY_PASS_DEMO_STAGE')){
        if(RECRUITMENT_BY_PASS_DEMO_STAGE){
            $res =$this->checkApplicantTaskAndCreateTask_bypass($applicantId,$adminId,'cab_day', $application_id);
            if($res){
                $result=['status'=>$res,'msg'=>'Bypass stage successfully.'];
            }
        }
    }
    return $result;
}

public function checkApplicantTaskAndCreateTask_bypass($applicantId,$adminId,$type='cab_day', $application_id = 0){
    $recuirterData = $this->basic_model->get_row('recruitment_staff',['adminId'],['approval_permission'=>1,'status'=>1,'its_recruitment_admin'=>0,'archive'=>0]);
    $recuirterId =!$recuirterData ? 0: $recuirterData->adminId;
    $this->db->select(['task.id','taskapp.id as task_applicant_id','taskapp.id as task_applicant_id_id', 
]);
    $this->db->from('tbl_recruitment_task as task');
    $this->db->join("tbl_recruitment_task_stage as taskstage", "taskstage.id=task.task_stage and taskstage.key='".$type."' and taskstage.archive=0", "inner");    
    $this->db->join('tbl_recruitment_task_applicant as taskapp', "taskapp.taskId=task.id and taskapp.status=1 and taskapp.archive=0", 'inner');
    $this->db->where_in("task.status",[1,2]);    
    $this->db->where("taskapp.applicant_id",$applicantId);
    $this->db->where("taskapp.application_id",$application_id);

    $this->db->limit(1);
    $query = $this->db->get();
       //last_query(1);
    if($query->num_rows()>0){
        $row=$query->row_array();
        $taskId = $row['id'];
        $taskApplicantId = $row['task_applicant_id'];
        $taskApplicantIdId = $row['task_applicant_id_id'];
        if(!empty($taskApplicantIdId)){
            $this->update_applicant_bypass($taskApplicantIdId);
        }
    }else{
        $taskStageData = $this->basic_model->get_row('recruitment_task_stage',['id'],['key'=>$type,'archive'=>0]);
        $recuirterData = $this->basic_model->get_row('recruitment_staff',['adminId'],['approval_permission'=>1,'status'=>1,'its_recruitment_admin'=>0,'archive'=>0]);
        $recuirterId =$recuirterData->adminId;
        $taskStage =$taskStageData->id;
        $taskId =$this->create_task_bypass($taskStage,$adminId);
        $taskApplicantId =$this->create_task_applicant_bypass($taskId,$applicantId, $application_id);
            //$this->create_task_applicant_bypass($taskId,$recuirterId);
    }

    $this->create_task_recuirter_assign_bypass($taskId,$recuirterId);
    $this->create_group_or_cab_interview_detail_bypass($taskApplicantId,$type,$adminId);
    $this->recruitment_questions_apply_for_applicant_bypass($taskApplicantId,$type);
    $this->create_Stage_data_bypass($type,$applicantId,$adminId, $application_id);
    if($type=='cab_day'){
        $applicantData = $this->basic_model->get_row('recruitment_applicant',['status'],['id'=>$applicantId]);
        if(isset($applicantData->status) && $applicantData->status==1){

            
            /*
            $this->basic_model->update_records('recruitment_applicant',['status'=>'3'],['id'=>$applicantId]);
            require_once APPPATH . 'Classes/recruitment/ApplicantMoveToHCM.php';
            $applicantMoveObj = new ApplicantMoveToHCM();

            $this->load->model('Recruitment_applicant_model');
            $interview_stage_row = $this->Recruitment_applicant_model->get_applicant_job_stage($applicantId);
            if(!empty($interview_stage_row) && $interview_stage_row['key_name'] == 'individual_interview'){
                    //individual_interview create general admin user without any permission HCM-490
                $applicantMoveObj->setUser_type('internal_staff');
                $applicantMoveObj->setAdmin_User_type('3');
            }else{
                   //group_interview create member
             $applicantMoveObj->setUser_type('external_staff');
             $applicantMoveObj->setAdmin_User_type('0');
         }
         $applicantMoveObj->setApplicant_id($applicantId);
         $responseData = $applicantMoveObj->move_applicant();*/
     }

 }
 return true;
}

private function create_task_bypass($taskStage,$adminId) {
    $insTask=[
        'task_name'=>'By Pass Task - '.DATE_CURRENT,
        'created_by'=>$adminId,
        'task_stage'=>$taskStage,
        'start_datetime'=>DATE_TIME,
        'end_datetime'=>DATE_TIME,
        'training_location'=>1,
        'status'=>2,
        'mail_status'=>1,
        'created'=>DATE_TIME,
        'relevant_task_note'=>'By Pass Task',
        'max_applicant'=>'1',
        'task_piority'=>'1',
        'action_at'=>DATE_TIME,
        'commit_status'=>'1',
    ];
    $taskId = $this->basic_model->insert_records('recruitment_task',$insTask);
    return  $taskId;
}

private function create_task_applicant_bypass($taskId,$applicantId, $application_id = 0) {
    $insTaskApplicant=[
        'taskId'=>$taskId,
        'applicant_id'=>$applicantId,
        'application_id' => $application_id,
        'applicant_message'=>'',
        'email_status'=>1,
        'status'=>1,
        'archive'=>0,
        'token_email'=>'',
        'invitation_accepted_at'=>DATE_TIME,
        'invitation_send_at'=>DATE_TIME,
        'created'=>DATE_TIME
    ];
    $taskApplicantId = $this->basic_model->insert_records('recruitment_task_applicant',$insTaskApplicant);
        //print_r($insTaskApplicant);
    return  $taskApplicantId;
}

private function create_task_recuirter_assign_bypass($taskId,$recuirterId){
    $recuirterIdAssignPrimary = $this->basic_model->get_row('recruitment_task_recruiter_assign',['id'],['taskId'=>$taskId,'archive'=>0,'primary_recruiter'=>1]);

    if(!$recuirterIdAssignPrimary){
        $insRecruiterAssign=[
            'recruiterId'=>$recuirterId,
            'taskId'=>$taskId,
            'primary_recruiter'=>1,
            'archive'=>0,
            'created'=>DATE_TIME
        ];
        $taskApplicantId = $this->basic_model->insert_records('recruitment_task_recruiter_assign',$insRecruiterAssign);
    }
}

private function create_group_or_cab_interview_detail_bypass($taskApplicantId,$type='cab_day',$adminId){
    $interviewDetail = $this->basic_model->get_row('recruitment_applicant_group_or_cab_interview_detail',['id'],['recruitment_task_applicant_id'=>$taskApplicantId]);
    $interviewDetailContract = $this->basic_model->get_row('recruitment_applicant_contract',['id'],['archive'=>0,'task_applicant_id'=>$taskApplicantId]);
    $typeDetail = $this->basic_model->get_row('recruitment_interview_type',['id'],['key_type'=>$type]);
    $interview_type = !$typeDetail ? 0: $typeDetail->id;
    $interviewDetailId = !$interviewDetail ? 0: $interviewDetail->id;
    $interviewDetailContractId = !$interviewDetailContract ? 0: $interviewDetailContract->id;

    if(!$interviewDetail){
            //echo 'dss';
        $insInterviewDetail=[
            'deviceId' => 0,
            'device_pin' => random_genrate_password(8),
            'interview_type' => $interview_type,
            'recruitment_task_applicant_id' => $taskApplicantId,
            'quiz_status' => 1,
            'applicant_status' => 1,
            'contract_status' => 1,
            'allot_question' => 1,
            'quiz_submit_status' => 1,
            'mark_as_no_show' => 0,
            'quiz_status_overseen_by' => $adminId,
            'archive' => 0,
            'created' => DATE_TIME,
            'updated' => DATE_TIME
        ];
        if($type=='cab_day'){
            $insInterviewDetail['document_status']=1;
            $insInterviewDetail['app_orientation_status']=1;
            $insInterviewDetail['app_login_status']=1;
        }
        $interviewDetailId = $this->basic_model->insert_records('recruitment_applicant_group_or_cab_interview_detail',$insInterviewDetail);
        if($type=='cab_day'){
            $insInterviewDetailContarct=[
                'task_applicant_id'=>$taskApplicantId,
                'envelope_id'=>0,
                'archive'=>0,
                'signed_status'=>1,
                'send_date'=>DATE_TIME,
                'created'=>DATE_TIME,
                'signed_date'=>1,
                'unsigned_file'=>$taskApplicantId.'_bypassunsigned.pdf',
                'signed_file'=>$taskApplicantId.'_bypassunsigned.pdf'
            ];
            $taskApplicantId = $this->basic_model->insert_records('recruitment_applicant_contract',$insInterviewDetailContarct);
        }

        }else{// update interview details

            //echo 'dss2';
            $updInterviewDetail=[
                //'deviceId' => 0,
                //'device_pin' => random_genrate_password(8),
                //'interview_type' => $interview_type,
                //'recruitment_task_applicant_id' => $taskApplicantId,
                'quiz_status' => 1,
                'applicant_status' => 1,
                'contract_status' => 1,
                'allot_question' => 1,
                'quiz_submit_status' => 1,
                'mark_as_no_show' => 0,
                'quiz_status_overseen_by' => $adminId,
                'archive' => 0,
                'updated' => DATE_TIME
            ];

            $this->basic_model->update_records('recruitment_applicant_group_or_cab_interview_detail',$updInterviewDetail,['id'=>$interviewDetailId]);
            if($type=='cab_day' && $interviewDetailContractId==0){
                $insInterviewDetailContarct=[
                    'task_applicant_id'=>$taskApplicantId,
                    'envelope_id'=>0,
                    'archive'=>0,
                    'signed_status'=>1,
                    'send_date'=>DATE_TIME,
                    'created'=>DATE_TIME,
                    'signed_date'=>DATE_TIME,
                    'unsigned_file'=>$taskApplicantId.'_bypassunsigned.pdf',
                    'signed_file'=>$taskApplicantId.'_bypassunsigned.pdf'
                ];
                $this->basic_model->insert_records('recruitment_applicant_contract',$insInterviewDetailContarct);
            }else if($type=='cab_day' && $interviewDetailContractId>0){
                $this->basic_model->update_records('recruitment_applicant_contract',['signed_status'=>1,'signed_date'=>DATE_TIME],['id'=>$interviewDetailContractId]);
            }

            
        }
    }

    private function get_stageid_by_key_bypass($typeDataStage=[0]){
        $this->db->select(['id']);
        $this->db->from('tbl_recruitment_stage');
        $this->db->where_in('stage_key',$typeDataStage);
        $this->db->where('archive',0);
        $this->db->order_by('stage_order');
        $query = $this->db->get();
        $res =[];
        if($query->num_rows()>0){
            $res = $query->result_array();
            $res = array_map('current',$res);
        }
        return $res;
    }
    
    private function create_Stage_data_bypass($type='cab_day',$applicantId,$adminId, $application_id){
        $typeDataStage = $type=='cab_day' ?['schedule_cab_day','cab_applicant_responses','cab_day_result','employment_contract','member_app_onboarding'] : ['group_schedule_interview','group_applicant_responses','group_interview_result'];

        $typeDataCurrentStage = $type=='cab_day' ?['recruitment_complete'] : ['document_checklist'];
        $interViewRes=$this->get_stageid_by_key_bypass($typeDataStage);
        $interViewCurrentRes=$this->get_stageid_by_key_bypass($typeDataCurrentStage); 
        $insData= [];
        if(!empty($interViewRes)){
            foreach($interViewRes as $row){
                $temp=[
                    'application_id' => $application_id,
                    'applicant_id'=> $applicantId,
                    'stageId' =>$row,
                    'status' =>3,
                    'archive' =>0,
                    'created' =>DATE_TIME,
                    'action_at' =>DATE_TIME,
                    'action_by' =>$adminId,
                ];
                $insData[] =$temp;
            }
            if(!empty($insData)){
                $this->db->where_in('stageId',$interViewRes);
                $this->db->where('applicant_id',$applicantId);
                $this->db->where('application_id', $application_id);
                $this->db->set('archive',1);
                $this->db->update('tbl_recruitment_applicant_stage');
                $this->basic_model->insert_update_batch('insert','recruitment_applicant_stage',$insData);
            }
        }
        if(!empty($interViewCurrentRes)){
            foreach($interViewCurrentRes as $row){              
                $this->db->where_in('id',$applicantId);
                $this->db->set('current_stage',$row);
                $this->db->update('tbl_recruitment_applicant');
                //last_query();
                //$this->basic_model->update_records('recruitment_applicant',['current_stage'=>$row],['id'=>$applicantId]);
            }
        }
        if($type=='cab_day'){
            $typeDataCurrentStage = ['recruitment_complete'];
            $interViewCurrentRes=$this->get_stageid_by_key_bypass($typeDataCurrentStage);
            if(!empty($interViewCurrentRes)){
                $last_stage_id = $interViewCurrentRes[0];
            }else{
                $last_stage_id = 14;
            }
            $this->basic_model->insert_records('recruitment_applicant_stage',['applicant_id'=>$applicantId,'stageId'=>$last_stage_id,'status'=>2,'created'=>DATE_TIME,'action_at'=>DATE_TIME,'action_by' =>$adminId,
                'application_id' => $application_id
            ]);
        }
    }

    private function recruitment_questions_apply_for_applicant_bypass($taskApplicantId,$type='cab_day'){
        $applicantQuestions = $this->basic_model->get_row('recruitment_additional_questions_for_applicant',['id'],['recruitment_task_applicant_id'=>$taskApplicantId,'archive'=>0]);
        if(!$applicantQuestions){
           // echo 'dfdf';
            $typeDetail = $this->basic_model->get_row('recruitment_interview_type',['id'],['key_type'=>$type]);
            $interview_type = !$typeDetail ? 0: $typeDetail->id;
            $res= $this->basic_model->get_record_where('recruitment_additional_questions',['id'],['status'=>1,'training_category'=>$interview_type,'archive'=>0]);
            if(!empty($res)){
                $insApplicantQuestions = [];
                foreach($res as $row){
                    $temp=[
                        'question_id'=>$row->id,
                        'recruitment_task_applicant_id'=>$taskApplicantId,
                        'answer'=>'',
                        'is_answer_correct'=>0,
                        'archive'=>0
                    ];
                    $insApplicantQuestions[] =$temp;
                }
                $this->basic_model->insert_update_batch('insert','recruitment_additional_questions_for_applicant',$insApplicantQuestions);
                //last_query();
            }

        }


    }

    private function update_applicant_bypass($id){
        $this->basic_model->update_records('recruitment_task_applicant',['status'=>1,'invitation_accepted_at'=>DATE_TIME],['id'=>$id]);
    }
}
?>