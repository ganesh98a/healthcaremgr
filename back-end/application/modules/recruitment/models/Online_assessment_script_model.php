<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Online_assessment_script_model extends Basic_model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('recruitment/Recruitment_applicant_model');
        $this->load->model('recruitment/Recruitment_jobs_model');
        $this->load->model('recruitment/Online_assessment_model');
        $this->load->model('recruitment/Recruitment_oa_template_model');
        $this->load->model('imail/Automatic_email_model');
        $this->load->model('Basic_model');
    }

    /** To sends assessment reminder email 
     * @param $adminId {int} User id Who initiated the cron job
     * 
     * @see pull_job_assessment_by_dynamic_var
     * @see get_applicant_info
     * @see get_jobs_by_id
     * @see get_assement_duration_by_type
     * @see format_time
     * @see get_applicant_phone
     * @see get_applicant_email
     * @see create_log
     * @see trigger_OA_email
     * @see update_application_feed_history
     * 
     * @return $return {array} return with the List of processed UUID or nothing to process message
    */
    function assessment_reminder_email($reqData, $adminId) {       
       
        $expiry_time = $reqData->expiry_time ?? OA_REMINDER_EMAIL_TIME;
        
        $prev_date_time = date('Y/m/d H:i', strtotime($expiry_time));
        $prev_date = date('Y/m/d', strtotime($expiry_time));

        $select_col = ["rja.created_at", "rja.applicant_id", "rja.application_id" , "rja.uuid", "rja.job_id", "rja.created_by"];

        $where_conditions = ['rja.is_reminder_sent' => 0, 'rja.status'=> 1, 'rja.created_at <=' => $prev_date_time, 'DATE(rja.created_at)' => $prev_date];
       
        $query = $this->Online_assessment_model->pull_job_assessment_by_dynamic_var($where_conditions, $select_col);
        $result = $query->result_array();
       
        if(!empty($result)) {
            $uuid_list = [];
            foreach($result as $res) {
                $application_id = $res['application_id'];
                $applicant_id = $res['applicant_id'];                
                $uuid = $res['uuid'];
                $app_details = $this->Recruitment_applicant_model->get_applicant_info
                ($applicant_id, NULL);
                
                $jobDetails = $this->Recruitment_jobs_model->get_jobs_by_id($res['job_id']);
        
                $job_type_id = $jobDetails->value??'';
                $duration = $this->Online_assessment_model->get_assement_duration_by_type($job_type_id);
                $duration = $this->Online_assessment_model->format_time($duration);

                if(!empty($app_details)) {
                    $firstname = $app_details['firstname'] ? ucfirst($app_details['firstname']) : '';
                    $lastname = $app_details['lastname'] ? ucfirst($app_details['lastname']) : '';

                    $app_phone = $this->Recruitment_applicant_model->get_applicant_phone($applicant_id);
                    $user_phone = $app_phone[0]->phone ?? '';

                    $app_email = $this->Recruitment_applicant_model->get_applicant_email($applicant_id, TRUE);

                    $user_email = $app_email[0]->email;

                    if(!empty($user_email)) {
                                      
                        $applicant = [];
                        $applicant['email'] = $user_email;
                        $applicant['firstname'] = $firstname;
                        $applicant['lastname'] = $lastname;
                        $applicant['job_type'] = $jobDetails->label??'';
                        $applicant['assesment_duration'] = $duration;
                        $applicant['assessment_link'] = "<a href='" . ASSESSMENT_HOST . "online_assessment/".$uuid."' style='color:#0b64e8;text-decoration:underline;font-weight:bold;'>" . ASSESSMENT_HOST ."online_assessment/$uuid</a>";
                        $applicant['application_id'] = $application_id;
                        $applicant['applicant_id'] = $applicant_id;
                        $this->load->library('UserName');
                        $applicant['recruiter_name'] = $this->username->getName('admin', $res['created_by']);

                        $this->Recruitment_oa_template_model->create_log("Reminder Email Sent for OA- $uuid to $user_email ",  $adminId, []);

                        $this->Online_assessment_model->trigger_OA_email($applicant_id, $applicant, 'send_assessment_initiate_reminder_details', $adminId, $application_id);                        
                        
                        $data = [
                            'application_id' => $application_id,
                            'created_by' => $adminId,
                            'created_at' => DATE_TIME,
                        ];
                        
                        #Tracking in the application feed
                        #$this->update_application_feed_history($data, "OA Reminder Email");

                        //Set reminder sent value true for avoiding duplication of sending Email reminder
                        $this->basic_model->update_records('recruitment_job_assessment', ['is_reminder_sent' => 1, 'updated_at' => DATE_TIME, 'updated_by' => $adminId], ['uuid' => $uuid]);

                        //To sends reminder SMS
                        if(!empty($user_phone)) {
                            $message = "Hello. This is a reminder to complete your Online Assessment in order to be considered for a role with us at ONCALL Group Australia. If you are having difficulty finding the link please be in touch 1300 962 468";
                            $this->Online_assessment_model->send_sms($message, $user_phone, $applicant, $adminId);
                            
                            $this->Recruitment_oa_template_model->create_log("Reminder SMS Sent for OA- $uuid to $user_email ",  $adminId, []);
                        }
                        
                    }
                    $uuid_list[] = $uuid;
                }
                
            }
            
            $return  = ['status' => TRUE, 'data' => $uuid_list, 'msg' => 'Reminder Sent Successfully'];
        } else {
            $return  = ['status' => TRUE, 'msg' => 'Nothing to send for reminder!'];
        }
        return $return;
    }

    /**
     * Update application feed history for sending reminders and etc
     * 
     * @param $data {array} data's history update
     * @param $des {string} Description
     * 
     * @param {bool} TRUE/FALSE 
     */
    function update_application_feed_history($data, $desc) {

        if(empty($data)) {
            return TRUE;
        }

        $data['history_id'] = $this->basic_model->insert_records('application_history', $data);
        //Unset application id since history feed table doesn't have a application_id col
        unset($data['application_id']);        
        $data['desc'] = $desc;

        return $this->basic_model->insert_records('application_history_feed', $data);
    }
    /**
     * Helper function to update assessment status Link expired/error
     * 
     * @param $adminId {int} admin id user id which is running the job
     * 
     * @return $return {array} return with the List of processed link_expired/error UUID or nothing to process message
     */
    function assessment_auto_status_update($adminId) {        
        $this->load->library('UserName');   
        $this->db->select(['job_id','uuid', 'status', 'expiry_date','start_date_time','completed_date_time','application_id','applicant_id']);
        $this->db->from(TBL_PREFIX . 'recruitment_job_assessment');
        $this->db->where_in('status' , [OA_SENT, OA_INPROGRESS]);     
        $query  = $this->db->get();
        $result = $query->result();

        $return  = ['status' => TRUE, 'msg' => 'Nothing to process!'];

        if(!empty($result)) {
            $current_time = DATE_TIME;
            $uuid_list = [];
            foreach($result as $res) {
                $applicant_name = $this->username->getName('applicant', $res->applicant_id);

                if($res->status == OA_SENT && $res->expiry_date <= $current_time) {
                    $status = OA_LINK_EXPIRED;
                    $message = 'Link Expired';
                } elseif ($res->status == OA_INPROGRESS) {
                   $jobDetails = $this->Recruitment_jobs_model->get_jobs_by_id($res->job_id);        
                   $job_type_id = $jobDetails->value??'';
                   $duration = $this->Online_assessment_model->get_assement_duration_by_type($job_type_id);
                  
                   $asssement_completed_duration = date('Y-m-d H:i:s', strtotime($res->start_date_time. " +$duration seconds"));
                  
                   //If Assesment start time exceeded assessment duration then mark it as error
                   if($asssement_completed_duration <= $current_time && empty($res->completed_date_time)) {
                        $status = OA_ERROR;
                        $message = 'Error';
                    } else {
                        continue;
                    }
                } else {
                    continue;                   
                }               
               
                $uuid_list[$message][] = $res->uuid;

                $this->Recruitment_oa_template_model->create_log("OA Status updated for - $res->uuid as $message ",  $adminId, []);
                //Update the status 
                $this->basic_model->update_records('recruitment_job_assessment', 
                    ['status' => $status, 'updated_at' => DATE_TIME, 'updated_by' => $adminId], ['uuid' => $res->uuid]);
                
                    // create feed for OA error/link expired status
                    $dataToBeUpdated = [
                        'oa_status' => $status,           
                        'application_id'=> $res->application_id,
                        'applicant_name'=>$applicant_name
                    ];                     
                    $this->load->model('Online_assessment_model');
                    $this->Online_assessment_model->updateHistory($dataToBeUpdated, $res->application_id, $adminId);
                
            }
            if(!empty($uuid_list)) {
                $return  = ['status' => TRUE, 'data' => $uuid_list, 'msg' => 'OA Status updated Successfully'];
            }
        }

        return $return;   
    }

}