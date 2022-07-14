<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Automatic_email
 *
 * @author user
 */
require_once APPPATH . 'Classes/CommunicationLog.php';

class Automatic_email {

    function __construct() {
        $this->CI = &get_instance();
        $this->CI->load->model('admin/Notification_model');
    }

    private $email_key;
    private $email;
    private $templateId;
    private $dynamic_data;
    private $userId;
    private $user_type;
    private $send_by;
    private $attach_file;

    function setEmail_key($email_key) {
        $this->email_key = $email_key;
    }

    function getEmail_key() {
        return $this->email_key;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function getEmail() {
        return $this->email;
    }

    function setDynamic_data($dynamic_data) {
        $this->dynamic_data = $dynamic_data;
    }

    function getDynamic_data() {
        return $this->dynamic_data;
    }

    function setUserId($userId) {
        $this->userId = $userId;
    }

    function getUserId() {
        return $this->userId;
    }

    function setUser_type($user_type) {
        $this->user_type = $user_type;
    }

    function getUser_type() {
        return $this->user_type;
    }

    function setSend_by($send_by) {
        $this->send_by = $send_by;
    }

    function getSend_by() {
        return $this->send_by;
    }

    function setAttach_file($attach_file) {
        $this->attach_file = $attach_file;
    }

    function getAttach_file() {
        return $this->attach_file;
    }

    function setTemplateId($templateId) {
        $this->templateId = $templateId;
    }

    function getTemplateId() {
        return $this->templateId;
    }

    function get_dynamic_fields() {
        return [
            ['key' => "firstname", 'value' => "%FIRSTNAME%", 'label' => "First Name"],
            ['key' => "lastname", 'value' => "%LASTNAME%", 'label' => "Last Name"],
            ['key' => "email", 'value' => "%EMAIL%", 'label' => "Email"],
            ['key' => "job_title", 'value' => "%JOB_TITLE%", 'label' => "Job Title"],
            ['key' => "task_start_time", 'value' => "%TASK_START_TIME%", 'label' => "Group Interview Start Date Time"],
            ['key' => "task_end_time", 'value' => "%TASK_END_TIME%", 'label' => "Group Interview End Date Time"],

            ['key' => "interview_start_datetime", 'value' => "%INTERVIEW_START_TIME%", 'label' => "Group Booking Start Time"],
            ['key' => "interview_end_datetime", 'value' => "%INTERVIEW_END_TIME%", 'label' => "Group Booking End Time"],
            ['key' => "meeting_link", 'value' => "%MEETING_INVITE%", 'label' => "Group Booking Meeting Invite"],
            ['key' => "interview_location", 'value' => "%INTERVIEW_LOCATION%", 'label' => "Group Booking Location"],

            ['key' => "feedback_id", 'value' => "%FEEDBACK_ID%", 'label' => "Feedback Id"],
            ['key' => "initiator_details", 'value' => "%INITIATOR_DETAILS%", 'label' => "Initiator Details"],
            ['key' => "against_details", 'value' => "%AGAINST_DETAILS%", 'label' => "Against Details"],
            ['key' => "created_date", 'value' => "%CREATED_DATE%", 'label' => "Created Date"],
            ['key' => "updated_by", 'value' => "%UPDATED_BY%", 'label' => "Updated By"],
            ['key' => "notes_reason", 'value' => "%NOTES_REASON%", 'label' => "Notes/Reason"],

            // Online Assessment
            ['key' => "job_type", 'value' => "%JOBTYPE%", 'label' => "Job Type"],
            ['key' => "assesment_duration", 'value' => "%TIMEMINUTES%", 'label' => "Assessment Duration"],
            ['key' => "assessment_link", 'value' => "%ASSESSMENT%", 'label' => "Assessment Link"],
            ['key' => "recruiter_name", 'value' => "%RECRUITMENTCONSULTANTNAME%", 'label' => "RECRUITMENTCONSULTANTNAME"],
            ['key' => "application_id", 'value' => "%APPLICATIONID%", 'label' => "Application Id"],
            ['key' => "scheduled_date", 'value' => "%SCHEDULEDDATE%", 'label' => "Scheduled Date"],
            ['key' => "duration_taken", 'value' => "%DURATIONTAKEN%", 'label' => "Duration taken for completion"],


            ['key' => "task_start_time", 'value' => "%TASK_START_TIME%", 'label' => "Individual Interview Start Date Time"],
            ['key' => "task_end_time", 'value' => "%TASK_END_TIME%", 'label' => "Individual Interview End Date Time"],
            ['key' => "task_start_time", 'value' => "%TASK_START_TIME%", 'label' => "CAB Start Date Time"],
            ['key' => "task_end_time", 'value' => "%TASK_END_TIME%", 'label' => "CAB End Date Time"],
            ['key' => "task_location", 'value' => "%TASK_LOCATION%", 'label' => "Location"],
            ['key' => "admin_firstname", 'value' => "%ADMIN_FIRSTNAME%", 'label' => "User FirstName"],
            ['key' => "admin_lastname", 'value' => "%ADMIN_LASTNAME%", 'label' => "User Last Name"],
            ['key' => "function_content", 'value' => "%FUNCTION_CONTENT%", 'label' => "Function Content"], // keep in last
        ];
    }

    function replace_msg_content($msg_content) {
        $user_data = $this->dynamic_data;
        $dynamic_field = $this->get_dynamic_fields();
        // replace <p></p> into <br/>
        $msg_content = str_replace("<p><br /></p>", "<p></p>", $msg_content);
        $replace_content = preg_replace("/<p[^>]*?>/", "", $msg_content);
        $msg_content = str_replace("</p>", "<br />", $replace_content);
        foreach ($dynamic_field as $val) {

            if (!empty($user_data[$val['key']])) {
                $msg_content = str_replace($val['value'], $user_data[$val['key']], $msg_content);
            }
        }
       
        return $msg_content;
    }

    function replace_msg_content_docusign($msg_content) {
        $user_data = $this->dynamic_data;
        $dynamic_field = $this->get_dynamic_fields();
        // replace <p></p> into <br/>
        $msg_content = str_replace("<p><br /></p>", "", $msg_content);
        foreach ($dynamic_field as $val) {
            if (!empty($user_data[$val['key']])) {
                $msg_content = str_replace($val['value'], $user_data[$val['key']], $msg_content);
            }
        }

        return $msg_content;
    }

    function automatic_email_send_to_user() {
        $CI = &get_instance();

        $CI->load->model('imail/Automatic_email_model');
        $CI->load->model('imail/Templates_model');

        $template_data = $CI->Automatic_email_model->get_template_content_details($this->email_key);

        if (!empty($template_data)) {
            $msg_content = $template_data['content'];


            $msg_content = $this->replace_msg_content($msg_content);
            $subject = $this->replace_msg_content($template_data['subject']);
            $email_data = [
                'content' => $msg_content,
                'from' => $template_data['from'],
                'subject' => $subject,
                'email' => $this->email,
            ];

            $attachment = [];
            if (!empty($this->attach_file)) {
                $attachment[] = $this->attach_file ?? '';
            }

            $template_attachment = $CI->Templates_model->get_template_attachment($template_data["id"]);
            $this->CI->load->library('AmazonS3');
            if (!empty($template_attachment)) {
                foreach ($template_attachment as $val) {
                    $valObj = (object) $val;
                    if ($valObj->aws_uploaded_flag == 0) {
                        $attachment[] = TEMPLATE_ATTACHMENT_UPLOAD_PATH . $template_data["id"] . '/' . $val['filename'];
                    } else {
                        $s3_file_path = $valObj->file_path;

                        $filename = ($valObj->filename) ?? basename($valObj->filename);
                        $subfolder = 'template';
                        $attachment[] = ARCHIEVE_DIR . '/' . $subfolder . '/' . $valObj->templateId . '/' . $filename;

                        $this->CI->amazons3->setFolderKey($s3_file_path);
                        $this->CI->amazons3->setSourceFile(NULL);
                        $this->CI->amazons3->downloadDocumentTemp($valObj->templateId, $subfolder);
                    }

                }
            }
            send_email_template_to_applicant($email_data, $cc_email_address = null, $attachment);
            $notification_data = [];

            if($this->email_key == 'cab_certificate_applicant') {

                $notification_data['title'] = 'CAB certificate';
                $notification_data['shortdescription'] = 'CAB certificate: Please check the email sent out to your mailbox';

                $notification_data['specific_admin_user'] = $this->send_by;
                $notification_data['entity_type'] = 9;
                $notification_data['entity_id'] = $this->dynamic_data['application_id'];
            }
            else if($this->email_key == 'new_applicant_login') {

                $notification_data['title'] = 'Applicant Portal Login';
                $notification_data['shortdescription'] = 'Login: Please check the credentials sent to your mailbox';

                $notification_data['specific_admin_user'] = $this->send_by;
                $notification_data['entity_type'] = 9;
                $notification_data['entity_id'] =  $this->userId;

            }

            if($notification_data) {
                $notification_data['userId'] = $this->userId;
                $notification_data['user_type'] = 5;
                $notification_data['status'] = 0;
                $notification_data['sender_type'] = 2;
                $notification_data['created'] = DATE_TIME;

                $this->CI->Notification_model->create_notification($notification_data);
            }
            // store in communication log
            $this->store_in_communication_log($email_data);
        }
    }

    function automatic_email_send_to_user_by_template_id() {
        $CI = &get_instance();

        $CI->load->model('imail/Automatic_email_model');
        $CI->load->model('imail/Templates_model');

        $template_data = $CI->Automatic_email_model->get_template_content_details_by_template_id($this->templateId);

        if (!empty($template_data)) {
            $msg_content = $template_data['content'];


            $msg_content = $this->replace_msg_content($msg_content);

            $email_data = [
                'content' => $msg_content,
                'from' => $template_data['from'],
                'subject' => $template_data['subject'],
                'email' => $this->email,
            ];

            $attachment = [];
            if (!empty($this->attach_file)) {
                $attachment[] = $this->attach_file ?? '';
            }

            $template_attachment = $CI->Templates_model->get_template_attachment($template_data["id"]);
            $this->CI->load->library('AmazonS3');
            if (!empty($template_attachment)) {
                foreach ($template_attachment as $val) {
                    $valObj = (object) $val;
                    if ($valObj->aws_uploaded_flag == 0) {
                        $attachment[] = TEMPLATE_ATTACHMENT_UPLOAD_PATH . $template_data["id"] . '/' . $val['filename'];
                    } else {
                        $s3_file_path = $valObj->file_path;

                        $filename = ($valObj->filename) ?? basename($valObj->filename);
                        $subfolder = 'template';
                        $attachment[] = ARCHIEVE_DIR . '/' . $subfolder . '/' . $valObj->templateId . '/' . $filename;

                        $this->CI->amazons3->setFolderKey($s3_file_path);
                        $this->CI->amazons3->setSourceFile(NULL);
                        $this->CI->amazons3->downloadDocumentTemp($valObj->templateId, $subfolder);
                    }

                }
            }

            send_email_template_to_applicant($email_data, $cc_email_address = null, $attachment);

            // store in communication log
            if (!empty($this->userId)) {
                $this->store_in_communication_log($email_data);
            }
        }
    }

    function store_in_communication_log($email_data, $logtype = 2) {
        $Comm = new CommunicationLog();

        $Comm->setUser_type($this->user_type ?? 0);
        $Comm->setUserId($this->userId ?? 0);
        $Comm->setFrom($email_data['from'] ?? '');
        $Comm->setTitle($email_data['subject'] ?? '');
        $Comm->setCommunication_text($email_data['content'] ?? '');
        $Comm->setSend_by($this->send_by ?? 0);
        $Comm->setLog_type($logtype);

        $Comm->createCommunicationLog();
    }

    function send_invitation_task_function_part($userdata) {
        $url = $userdata['url'];
        $call_for = $userdata['call_for'] ?? '';

        $body = '';

        if ($call_for != '' && $call_for == 'for_group_cab') {
            $body .= '<p style="margin:0px;">Your quiz test credential.</p>
			<p style="margin:0px;">Pin : ' . $userdata['device_pin'] . '</p>';
        }

        # removed the accept button as we are now automatically accepting the interview invitations
        # HCM-2757
        # also removed the reject button as no buttons are needed, HCM-2829
        $body .= '<p style="margin:0px;"></p>';

        return $body;
    }
    
    function getEmailContent(){
        $CI = &get_instance();
        $template_data = $CI->Automatic_email_model->get_template_content_details($this->email_key);
        $msg_content = $template_data['content'];
        return $this->replace_msg_content($msg_content);
    }

}
