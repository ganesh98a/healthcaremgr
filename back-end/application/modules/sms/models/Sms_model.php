<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_model extends CI_Model  {
    
    private $sleep_time = 1000000;

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
        $this->load->model('basic/Basic_model');
        $this->load->model('sales/Feed_model');
        $this->load->model('recruitment/Online_assessment_model');
        $this->load->model('sales/Activity_model');
    }
    
    /**
     * Send bulk sms to applicants
     * @param {array} $applicants - applicant details
     * @param {object} request
     * Used in
     *   - Job Wise Application List
     *   - Activity SMS
     */
    public function send_bulk_sms($request, $applicants, $oa = false) {
        
        $orig_msg = $request->data->msg ?? '';       
        $msg_wo_vars = str_replace(['{Applicant.firstname}', '{Applicant.lastname}'], ["", ""], $orig_msg);        
        $sms_template_id = $request->data->sms_template_id ?? 0;
        if (!empty($request->data->template)) {
            //for activity block
            $sms_template_id = $request->data->template;
        }
        $template_title = $request->data->template_title ?? '';
        if (!empty($oa) && !empty($sms_template_id)) {
            $res = $this->Online_assessment_model->get_online_assessments_list($request, $request->adminId,  ['roa.id' => $sms_template_id]);
            if ($res['status']) {
                $row = $res['data'][0];
                if (!empty($row)) {
                    $template_title = $row->title;
                }
            }
        }
         
        if (!empty($orig_msg) && !empty($applicants)) {
            $sms_entity_type = '';
            // save GB feed
            $entity_type = $request->data->entity_type?? '';
            if ($entity_type === 'interview' || $entity_type === 'application') {
               
                $applicant = $applicants[0];
                $msg = $this->get_message_text($orig_msg, $applicant);
                $template_title = empty($template_title)? $this->get_sms_template_title($sms_template_id) : $template_title;
                $applicant_name = trim($applicant->firstname . ' ' . $applicant->lastname);
                $feed_content = json_encode(['content' => "<b>SMS SENT</b> - " . $orig_msg, 'title' => $template_title, 'applicant_name' => $applicant_name, 'applicants' => $applicants]);
                $feedData = ['source_id' => $request->data->entity_id, 'feed_title' => $feed_content, 'related_type' => 5, 'feed_type' => 1];
                if ($entity_type === 'interview') {
                    $sms_entity_type = SMS_GB;
                    $this->Feed_model->save_feed($feedData, $request->adminId);
                }                

                # save activity
                if ($entity_type === 'application') {
                    $content_act = $msg;
                    $sms_entity_type = SMS_APPLICATION;
                } else {
                    if (count($applicants) > 1) {
                        $content_act = $orig_msg;
                    } else {
                        $content_act = $msg;
                    }
                    
                }
                $activityData = array('entity_type' => $entity_type, 'recipients' => $applicants, 'content' => "<b>SMS SENT</b>" . $content_act, 'template_id'=> $sms_template_id, 'recipient_entity_type' => 'application', 'entity_id' => $request->data->entity_id, 'subject' => $template_title);
                $adminId = $request->adminId; 
                $this->Activity_model->save_sms((object) $activityData, $adminId);
            }
            $this->load->library('Asynclibrary');
            
            $sms_data = new stdclass();
            $sms_type = $request->message_sent_type ?? '';
            $status_desc = 'SMS tracking created for this bulk operation';
        
            foreach($applicants as $applicant) {
                // echo  "first phoo". $applicant->phone; die;
                //Prepare SMS logs
                $sms_data->message_type = $sms_type;
                $sms_data->phone_number = $applicant->phone;
                $sms_data->message = $orig_msg;
                $sms_data->applicant_id = $applicant->applicant_id;
                $sms_data->application_id = $applicant->application_id;
                $sms_data->status_desc = $status_desc;
                $sms_data->created_by = $request->adminId??NULL;
                $sms_data->entity_type = $sms_entity_type;
                
                $this->create_sms_log($sms_data);
                
                $url = base_url()."sms/Sms/send_sms";
                $msg = $this->get_message_text($orig_msg, $applicant);
                $params = array('phone_number' => $applicant->phone,'msg' => $msg, 'applicant' => json_encode($applicant), 'adminId' => $request->adminId, 'sms_template_id' => $sms_template_id, 'applicants' => json_encode($applicants), 'template_title' => $template_title, 'online_assessment' => !empty($oa)? 1 : 0, 'message_sent_type' => $sms_type, 'entity_type' => $sms_entity_type);   
                $sms_tracking_id = $this->create_sms_tracking($params, $request->adminId);
                $params['sms_tracking_id'] = $sms_tracking_id;               
                $this->asynclibrary->do_in_background($url, $params);
                 //To avoid too Many Request
                 usleep($this->sleep_time);

            }            
        }
        echo json_encode(["status" => true, "message" => "SMS is being sent. Please refresh the SMS logs and feed after some time."]);
        exit();
    }
   
    public function create_sms_tracking($params, $adminId) {

        if(empty($params)) {
            return TRUE;
        }
        return $this->basic_model->insert_records('bulk_sms_track', ['sms_params' => json_encode($params), 'created_at' => DATE_TIME, 'created_by' => $adminId]);
    }

    public function get_sms_data($request) {
        $limit = $request->data->limit ?? 10;
        $time = $request->data->time ?? '';
        $trackingIds =$request->data->trackingIds ?? 0;

        $this->db->select(['id','sms_params']);
        $this->db->from(TBL_PREFIX . 'bulk_sms_track');
        $this->db->where('status', 0);
        $this->db->where('archive', 0);
        
        //Process specific ids
        if($trackingIds) {
            $this->db->where_in('id', $trackingIds); 
        } else if($time){
            $this->db->where('created_at >=', date('Y-m-d h:i', strtotime($time)));
        }
        $this->db->limit($limit);
        $this->db->order_by('id', 'asc');
        $query = $this->db->get();
        
        return $query->result_array();
    }
    public function update_sms_currpted_data($request) {

        $this->basic_model->update_records('bulk_sms_track', ['status' => '3'], ['status' => 1 , 'created_at >=' => date('Y-m-d h:i', strtotime($time))]);
    
    }

    public function send_sms_from_tracking_queue($req_data) {
        if(empty($req_data)) {
            return TRUE;
        }
        $this->load->library('Asynclibrary');

        foreach($req_data as $data) {
            if(!empty($data['sms_params'])) { 
                $url = base_url()."sms/Sms/send_sms";
                $data['sms_params'] = json_decode($data['sms_params'], TRUE);
                $data['sms_params']['sms_tracking_id'] = $data['id'];                
                $this->asynclibrary->do_in_background($url, $data['sms_params']);
                //To avoid too Many Request
                usleep($this->sleep_time);
            }
        }

        return ['status' => TRUE, 'message' => 'SMS Sent to async call'];

    }

    public function get_message_text($orig_msg, $applicant) {
        $msg_wo_vars = str_replace(['{Applicant.firstname}', '{Applicant.lastname}'], ["", ""], $orig_msg);
        $msg = $orig_msg;
        // if length is less than 160 then only try to replce dynamic variables otherwise remove them
        if (strlen($msg_wo_vars) < 160) {
            $msg = str_replace('{Applicant.firstname}', $applicant->firstname, $msg);
            $msg_wo_vars = str_replace('{Applicant.lastname}', '', $msg);
            // after replacing first name if length is still less than 160 then replace last name otherwise remove first and last name both
            if (strlen($msg_wo_vars) < 160) {
                $msg = str_replace('{Applicant.lastname}', $applicant->lastname, $msg);
                if (strlen($msg) > 160) {
                    $msg = str_replace(['{Applicant.firstname}', '{Applicant.lastname}'], [$applicant->firstname, ''], $orig_msg);
                }
            } else {
                $msg = str_replace(['{Applicant.firstname}', '{Applicant.lastname}'], ['', ''], $orig_msg);
            }
        } else {
            $msg = str_replace(['{Applicant.firstname}', '{Applicant.lastname}'], ['', ''], $msg);
        }
        // remove any double space by single space
        $msg = str_replace('  ', ' ', $msg);
        return $msg;
    }

    public function get_sms_template_title($sms_template_id) {        
        $template_title = '';
        if (!empty($sms_template_id)) {                    
            $filter_condition = "id=" . $sms_template_id;
            $res = $this->Sms_template_model->get_sms_templates(new stdClass(), $filter_condition);
            if ($res['status']) {
                $template_title = $res['data'][0]->name;
            }
        }
        return $template_title;
    } 

    public function create_sms_log($data) {
        
        if(empty($data)) {
            return TRUE;
        }

        $this->load->library('SMSLogs');       
        $this->smslogs->setMessageType($data->message_type??'');
        $this->smslogs->setApplicationId($data->application_id??'');
        $this->smslogs->setApplicantId($data->applicant_id??'');
        $this->smslogs->setphoneNumber($data->phone_number??'');
        $this->smslogs->setEntityType($data->entity_type??'');
        $this->smslogs->setStatusDescription($data->status_desc??'');
        $this->smslogs->setMessage($data->message??'');
        $this->smslogs->setAwsResponse($data->aws_response??'');
        $this->smslogs->setCreatedBy($data->created_by??'d');        
        $this->smslogs->createSMSLog(); 
    }

    public function update_sms_tracking_data($id, $status, $error_response = '') {
        $set = ['status' => $status, 'updated_at' => DATE_TIME];

        if(!empty($error_response)) {
            $set = ['status' => $status, 'aws_response' => $error_response, 'updated_at' => DATE_TIME];
        }
        return $this->basic_model->update_records('bulk_sms_track', $set, ['id' => $id]);
    }
    
}