<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Sms extends MX_Controller {
    private $not_send = 0;
    private $inprogress = 1;
    private $sms_sent = 2;
    private $sms_send_error = 3;

	 function __construct() {
        parent::__construct();        
        $this->load->helper('i_pad');
        $this->load->model('recruitment/Recruitment_applicant_model');
        $this->load->model('sms/Sms_template_model');
        $this->load->model('sms/Sms_model');
    }

    public function getApplicants($request) {        
        $applicants = [];
        $response = $this->Recruitment_applicant_model->get_applications($request->data, $request->adminId);
        $existing_applications = [];
        if ($response['status']) {
            foreach($response['data'] as $applicant) {
                $existing_applications[$applicant->application_id] = $applicant;
            }
        }
        foreach($request->data->applicants as $applicant) {
            if (array_key_exists($applicant->application_id, $existing_applications)) {
                $existing_applicant = $existing_applications[$applicant->application_id];
                if ($existing_applicant->application_process_status != 8 && !empty($existing_applicant->phone)) {
                    $applicants[] = $existing_applicant;
                }
            }
        }
        return $applicants;
    }
    
    /**
     * Send bulk sms
     */
    public function send_bulk_sms_queue() {
        $request = request_handler('access_recruitment');

        # Get applicant details
        $applicants = $this->getApplicants($request); 
        $sms_data = new stdClass;
        $request->message_sent_type = $sms_data->message_sent_type = BULK_SMS;
        $sms_data->status_desc = "Bulk SMS initiated for " . count($applicants) . " Applicants";       
        $sms_data->created_by = $request->adminId;       
        
        $this->Sms_model->create_sms_log($sms_data);
       
        # Send bulk sms
        $this->Sms_model->send_bulk_sms($request, $applicants);

        $response = ["status" => true, "message" => "SMS sent successfully"];
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }       

    public function log($message, $payload) {
        log_msg(print_r($message, true), 200, $payload, '', base_url()."sms/Sms/send_sms", 'sms', 0, 1); 
    }

    /**
     * Send sms - Activity Block
     * - GroupBooking
     */
    public function send_activity_sms_queue() {
        $request = request_handler('access_recruitment');
        # Get applicant details
        $applicants_detail = $this->get_applicant_details($request);
        $entity_type = $request->data->entity_type ?? '';
        
        $flagged = "SMS cannot be sent to a flagged applicant";
        $unsuccessful = "SMS cannot be sent to an unsuccessful applicant";
        $flaggedName = [];
        $unsuccessfulName = [];
        if(count($applicants_detail)){
            foreach($applicants_detail as $key => $applicant){
                if($applicant->flag_status == 2){
                    $flaggedName[] = $applicant->name;
                } else if($applicant->application_process_status == 8){
                    $unsuccessfulName[] = $applicant->name;
                }
            }
        }
        $error = [];
        if(count($flaggedName)) {
            if($entity_type == 'interview'){
                $error[] = $flagged . " - " . implode(',', $flaggedName);
            } else {
                $error[] = $flagged;
            }
        }
        
        if(count($unsuccessfulName)) {
            if($entity_type == 'interview'){
                $error[] = $unsuccessful . " - " . implode(',', $unsuccessfulName);
            } else {
                $error[] = $unsuccessful;
            }
        }
        
        if(count($flaggedName) || count($unsuccessfulName)) {
            $response = ["status" => false, "over_check" => true, "error" => implode('<br/>', $error)];
            return $this->output->set_content_type('json')->set_output(json_encode($response));
        }
        
        # Validate applicants
        /*
        $validate_applicant = $this->validate_applicants($request, $applicants_detail);
        if (!empty($validate_applicant)) {
            $applicant_in = implode(', ', $validate_applicant);
            $in_app_coount = count($validate_applicant);
            if ($in_app_coount == 1) {
                $response = ["status" => false, "over_check" => true, "error" => "Selected applicant is flagged or unsuccessful - $applicant_in"];
            } else {
                $response = ["status" => false, "over_check" => true, "error" => "Selected applicants is flagged or unsuccessful - $applicant_in"];
            }            
            return $this->output->set_content_type('json')->set_output(json_encode($response));
        }*/

        $sms_data = new stdClass;
        $request->message_sent_type = $sms_data->message_sent_type = BULK_SMS;
        $sms_data->status_desc = "Activity Block Bulk SMS initiated for " . count($applicants_detail) . " Applicants";       
        $sms_data->created_by = $request->adminId;       
        
        $this->Sms_model->create_sms_log($sms_data);

        # Send sms
        $this->Sms_model->send_bulk_sms($request, $applicants_detail);

        $response = ["status" => true, "message" => "SMS sent successfully"];
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }

    /**
     * Validate applicant is active
     * @param {array} $applicants - applicant details
     * @param {object} request
     */
    function validate_applicants($request, $applicants_detail) {
        $applicants = $request->data->applicants ?? '';
        $applicant_inactive = [];
        foreach($applicants as $applicant) {
            $search_value = $applicant->value;
            $name = $applicant->label;
            $key = array_search($search_value, array_column($applicants_detail, 'applicant_id'));
            if ($key === false) {
                $applicant_inactive[] = $name;
            }
        }
        return $applicant_inactive;
    }

    /**
     * Get applicant details
     * @param {object} $request
     */
    function get_applicant_details($request) {
        $this->load->model('recruitment/Group_booking_model');
        $entity_type = $request->data->entity_type ?? '';
        $entity_id = $request->data->entity_id ?? '';
        $applicants = $request->data->applicants ?? '';
        $applicants_detail = [];
        $applicant_ids = [];
        foreach($applicants as $applicant) {
            $applicant_ids[] = $applicant->value;
        }
        switch($entity_type) {
            case 'interview':                
                # get applicant details
                $applicants_detail = $this->Group_booking_model->get_applicant_by_applicant_id($applicant_ids, $entity_id);
                break;
            case 'application':
                # get applicant details
                $applicants_detail = $this->Group_booking_model->get_applicant_by_application_id($applicant_ids, $entity_id);
                break;
            default:
                break;
        }

        return $applicants_detail;
    }    

    public function send_bulk_sms_oa() {
        $request = json_decode($this->input->post('reqData'));
        $applicants = $request->data->applicants;

        
        $sms_data = new stdClass;
        $request->message_sent_type = $sms_data->message_sent_type = BULK_SMS;
        $sms_data->status_desc = "OA Bulk SMS initiated for " . count($applicants) . " Applicants";       
        $sms_data->created_by = $request->adminId;       
        
        $this->Sms_model->create_sms_log($sms_data);

        # Send bulk sms
        $this->Sms_model->send_bulk_sms($request, $applicants, true);
        $response = ["status" => true, "message" => "SMS sent successfully"];
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }

     /**
     * Send sms using AmazonSms Library
     * - Asynclibrary Function
     */
    public function send_sms() {  

        $response = ["status" => false, "data" => []];
        
        $phone_number = $this->input->post('phone_number', true);
        $msg = $this->input->post('msg', true);
        $online_assessment = $this->input->post('online_assessment', true);         

        //capture the feed
        $applicant = $this->input->post('applicant');
        $applicant = !empty($applicant) ? json_decode($applicant) : '';
        $adminId = $this->input->post('adminId', true);
        $entity_type = $this->input->post('entity_type', true);
        $template_title = $this->input->post('template_title');
        $sms_tracking_id = $this->input->post('sms_tracking_id');
        
        if($sms_tracking_id) {
            $this->Sms_model->update_sms_tracking_data($sms_tracking_id, $this->inprogress);
        }

        $this->load->library('AmazonSms');
        # set sms detail
        $this->amazonsms->setMessage($msg);
        $this->amazonsms->setPhoneNumber($phone_number);

        # publish sms directly
        $result = $this->amazonsms->publishSms();  
        $message_sent_type = $this->input->post('message_sent_type', true);

        $sms_template_id = $this->input->post('sms_template_id', true); 
        if (empty($online_assessment) && !empty($sms_template_id)) {
            $template_title = $this->Sms_model->get_sms_template_title($sms_template_id);
        }

        if ($result['status'] == 200) {
            $response = ["status" => true, "data" => $result];            
            if (!empty($applicant) && !empty($adminId)) {
                
                $feed_content = json_encode(['content' => '<b>SMS SENT </b> - ' . $msg, 'title' => $template_title, 'applicant_name' => trim($applicant->firstname . ' ' . $applicant->lastname)]);
                $feedData = ['source_id' => $applicant->application_id, 'feed_title' => $feed_content, 'related_type' => 4, 'feed_type' => 1];
                $this->Feed_model->save_feed($feedData, $adminId);
                require_once APPPATH . 'Classes/Automatic_email.php';
                $sms_msg_data = [
                    'subject' => !empty($template_title)? $template_title : "Free Text",
                    'from' => 'ONCALL Group Australia - Recruitment Team',
                    'content' => $msg
                ];                

                $obj = new Automatic_email();
                
                $obj->setSend_by($adminId);
                $obj->setUser_type(1);
                $obj->setUserId($applicant->applicant_id);
                $obj->store_in_communication_log($sms_msg_data, 1);
                
                if($sms_tracking_id) {
                    $this->Sms_model->update_sms_tracking_data($sms_tracking_id, $this->sms_sent, 'NULL');
                }
                
                /** Prepare SMS logs **/
                $sms_data = new stdclass();
                $sms_data->message_type = $message_sent_type;
                $sms_data->phone_number = $phone_number;
                $sms_data->message = $msg;
                $sms_data->applicant_id = $applicant->applicant_id;
                $sms_data->application_id = $applicant->application_id;
                $sms_data->status_desc = "SMS Sent successfully";
                $sms_data->aws_response = json_encode($result);
                $sms_data->created_by = $adminId;
                $sms_data->entity_type = $entity_type??'';
            
                $this->Sms_model->create_sms_log($sms_data);
            }
        } else {
            /** Prepare SMS logs **/
            $sms_data = new stdclass();
            $sms_data->message_type = $message_sent_type;
            $sms_data->phone_number = $phone_number;
            $sms_data->message = $msg;
            $sms_data->applicant_id = $applicant->applicant_id;
            $sms_data->application_id = $applicant->application_id;
            $sms_data->status_desc = "SMS Failed";
            $sms_data->aws_response = json_encode($result);
            $sms_data->created_by = $adminId;
            $sms_data->entity_type = $entity_type??'';
        
            $this->Sms_model->create_sms_log($sms_data);
            if($result['error_type'] == 'Throttling') {
                //Update tracking status not to be send for throttling errors
                $this->Sms_model->update_sms_tracking_data($sms_tracking_id, $this->not_send, json_encode($result));
                //Stop the execution if Throttling error occured
                exit();
            } else {

                $feed_content = json_encode(['content' => "<b>SMS FAILED </b> - " . $msg, 'title' =>  $template_title, 'applicant_name' =>  trim($applicant->firstname . ' ' . $applicant->lastname)]);
                $feedData = ['source_id' => $applicant->application_id, 'feed_title' => $feed_content, 'related_type' => 4, 'feed_type' => 1];

                $this->Feed_model->save_feed($feedData, $adminId);

                //Update tracking status not to be send for throttling errors
                $this->Sms_model->update_sms_tracking_data($sms_tracking_id, $this->sms_send_error, json_encode($result));

                $sms_msg_data = [
                    'subject' => !empty($template_title)? $template_title : "Free Text",
                    'from' => 'ONCALL Group Australia - Recruitment Team',
                    'content' =>  '<b>SMS FAILED</b> - ' . $msg
                ];                
                require_once APPPATH . 'Classes/Automatic_email.php';
                $obj = new Automatic_email();
                
                $obj->setSend_by($adminId);
                $obj->setUser_type(1);
                $obj->setUserId($applicant->applicant_id);
                $obj->store_in_communication_log($sms_msg_data, 1);

            }
            $this->log("publishSms api response", [$result, $phone_number, $msg]);
        }
        return $response;
    }

    /**
     * Save log in Communication log
     * @param {array} $data
     */
    function save_communication_log($data) {
        $data = (array) $data;
        require_once APPPATH . 'Classes/CommunicationLog.php';

        $from = APPLICATION_NAME;
        $content = $data['content'] ?? '';
        $applicant_id = $data['applicant_id'] ?? '';
        $send_by = $data['send_by'] ?? '';
        $title = $data['title'] ?? '';
        $title = ($title != '' ? ' ' : '').'SMS Sent';
        # Create communication log
        $obj_comm = new CommunicationLog();

        $obj_comm->setUser_type(1);
        $obj_comm->setUserId($applicant_id);
        $obj_comm->setFrom($from);
        $obj_comm->setTitle($title ?? NULL);
        $obj_comm->setCommunication_text($content ?? NULL);
        $obj_comm->setSend_by($send_by ?? 0);
        $obj_comm->setLog_type(1);
        $obj_comm->setCreated(DATE_TIME);

        $obj_comm->createCommunicationLog();
    }

    /**
     * Sending OA reminder SMS using Async call
     */
    public function send_oa_reminder_sms() {
        $this->log('send_oa_reminder_sms', $this->input->post());
        $phone_number = $this->input->post('phone_number', true);
        $msg = $this->input->post('msg', true);
        $online_assessment = $this->input->post('online_assessment', true);
        $applicant = json_decode($this->input->post('applicant'));
        $adminId = $this->input->post('adminId', true);
        $template_title = $this->input->post('template_title');
        $request = new stdClass();
        $request->data = new stdClass();
        $request->adminId = $adminId;
        $request->data->template_title = $template_title;
        $request->data->entity_type = 'application';
        $request->data->msg = $msg;
        $request->data->entity_id = $applicant->application_id;
        $applicant->phone = $phone_number;


        $sms_data = new stdClass;
        $request->message_sent_type = $sms_data->message_sent_type = BULK_SMS;
        $sms_data->status_desc = "Reminder SMS initiated for " . count($applicant) . " Applicants";       
        $sms_data->created_by = $request->adminId;       
        
        $this->Sms_model->create_sms_log($sms_data);

        $this->Sms_model->send_bulk_sms($request, [$applicant]);
    }
    
    public function send_sms_from_tracking_queue() {
        $request = request_handler('access_recruitment');
        
        $data = $this->Sms_model->get_sms_data($request);
       
        $response = ['status'=> TRUE, 'messgage' => 'Nothing to Send'];
        if(!empty($data)) {
           $response = $this->Sms_model->send_sms_from_tracking_queue($data);
           $response['total_records'] = count($data);
           $response['data'] = $data;
        }

        return $this->output->set_content_type('json')->set_output(json_encode($response));

    }
   
}