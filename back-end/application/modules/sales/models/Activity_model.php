<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * class: Activity_model
 * use for query operation of activity
 */

//class Master extends MX_Controller
class Activity_model extends CI_Model {

    function __construct() {

        parent::__construct();

    }

    /*
     * Get contact by sales id and source
     * @param {object} $reqData
     */
    function get_contact_name_search($reqData) {
        $salesId = $reqData->salesId ?? '';
        $sales_type = $reqData->sales_type ?? '';

        $source_data_type = false;
        if ($sales_type == "contact") {
            $this->db->where("p.id", $salesId);
        } else {
            // load contact model
            $this->load->model('Contact_model');
            $source_data_type = $this->Contact_model->give_id_of_entity_type($sales_type);
        }

        if ($source_data_type) {
            $this->db->join("tbl_sales_relation as sr", "sr.destination_data_id = p.id AND sr.destination_data_type = 1", "INNER");
            $this->db->where("sr.source_data_id", $salesId);
            $this->db->where("sr.source_data_type", $source_data_type);
        }

        $this->db->select(["concat_ws(' ',firstname,lastname) as label", "p.id as id","'contact' as type"]);
        $this->db->select("(select pe.email from tbl_person_email as  pe where pe.person_id = p.id and pe.archive = 0 and pe.primary_email = 1 limit 1) as subTitle");
        $this->db->from("tbl_person as p");
        $this->db->where("p.archive", 0);
        $this->db->where("p.status", 1);
        $this->db->group_by("p.id");

        return $this->db->get()->result();
    }

    /*
     * Get contact list by sales id and source
     * @param {object} $reqData
     */
    function get_all_contact_name_search($reqData) {
        $salesId = $reqData->salesId ?? '';
        $sales_type = $reqData->sales_type ?? '';
        // load contact model
        $this->load->model('Contact_model');
        $source_data_type = $this->Contact_model->give_id_of_entity_type($sales_type);
        if ($source_data_type) {
            $this->db->join("tbl_sales_relation as sr", "sr.destination_data_id = p.id AND sr.destination_data_type = 1", "INNER");
            $this->db->where("sr.source_data_id", $salesId);
            $this->db->where("sr.source_data_type", $source_data_type);
        }

        $this->db->select(["concat_ws(' ',firstname,lastname) as label", "p.id as id"]);
        $this->db->select("(select pe.email from tbl_person_email as  pe where pe.person_id = p.id and pe.archive = 0 and pe.primary_email = 1 limit 1) as subTitle");
        $this->db->from("tbl_person as p");
        $this->db->where("p.archive", 0);
        $this->db->where("p.status", 1);

        return $this->db->get()->result();
    }

    /*
     * Get all lead name
     * @param {object} $reqData
     */
    function get_option_of_lead_name($reqData) {
        $salesId = $reqData->salesId ?? '';
        $this->db->select(["concat(tbl_leads.firstname, ' ', tbl_leads.lastname) as label", "tbl_leads.id as id","'lead' as type", "tbl_leads.email as subTitle"]);
        $this->db->from("tbl_leads");
        $this->db->where("tbl_leads.id", $salesId);
        $query = $this->db->get();
        return $query->result();
    }

     /*
     * Get all contact  name
     * @param {object} $reqData
     */
    function get_option_of_contact_name_search($reqData) {
        $sales_type = $reqData->sales_type ?? '';

        $this->db->select(["concat(p.firstname, ' ', p.lastname) as label", "p.id as id","'contact' as type"]);
        $this->db->select("(select pe.email from tbl_person_email as  pe where pe.person_id = p.id and pe.archive = 0 and pe.primary_email = 1 limit 1) as subTitle");

        $this->db->from("tbl_person as p");
        $query = $this->db->get();
        return $query->result();

    }
    /*
     * Get all lead name
     * @param {object} $reqData
     */
    function get_option_of_lead_name_search($reqData) {
        $salesId = $reqData->salesId ?? '';
        $sales_type = $reqData->sales_type ?? '';

        $this->db->select(["concat(tbl_leads.firstname, ' ', tbl_leads.lastname) as label", "tbl_leads.id as id","'lead' as type", "tbl_leads.email as subTitle"]);
        $this->db->from("tbl_leads");
        $query = $this->db->get();
        return $query->result();
    }

    /*
     * Save & Send Email
     * @param {obj} $reqData
     * @param {int} $current_admin - logged in user id
     * @param {str} $adminName - logged in username
     */
    function send_new_mail($reqData, $current_admin, $adminName) {

        $dynamic_data = $this->dynamic_email_data($reqData, 'create', $current_admin);

        $reqData->content = $this->replace_msg_content($reqData->content, $dynamic_data);

        // create message activity
        $activity_Id = $this->save_email_data($reqData, 'create', $current_admin);

        // save mail recipient
        $contentId = $this->save_email_recipient($reqData, $activity_Id, $current_admin, 'create');
        //attachments coming from template
        $temp_attachments = "";
        if (!empty($reqData->temp_attachments)) {
            $temp_attachments = $reqData->temp_attachments;
        }

        // upload attachment to permanaent folder
        $response = $this->upload_content_attachement($activity_Id, $contentId, $temp_attachments);

        // send email
        $this->send_mail($reqData, $response, $adminName);
    }

    /*
     * Send Email to selected contact
     * @param {obj} $reqData
     * @param {obj} $files - attachments
     * @param {str} $adminName - logged in user name
     */
    function send_mail($reqData, $files, $adminName) {
        // load helper
        $this->load->helper('email_template_helper');
        // pr($reqData->to_user);
        // variables
        $req_to_email = json_decode($reqData->to_user);
        $req_cc_email = json_decode($reqData->cc_user);
        $req_bcc_email = json_decode($reqData->bcc_user);
        $req_subject = $reqData->subject;
        $req_content = $reqData->content;
        $to_email = '';
        $cc_email = '';
        $bcc_email = '';

        // convert array to str format for to email
        $to_arr = [];
        foreach ($req_to_email as $key => $email) {
            $to_arr[] = $email->subTitle;
        }
        $to_email = implode(',', $to_arr);

        // convert array to str format for cc email
        $cc_arr = [];
        foreach ($req_cc_email as $key => $email) {
            $cc_arr[] = $email->subTitle;
        }
        $cc_email = implode(',', $cc_arr);

        // convert array to str format for bcc email
        $bcc_arr = [];
        foreach ($req_bcc_email as $key => $email) {
            $bcc_arr[] = $email->subTitle;
        }
        // implode array
        $bcc_email = implode(',', $bcc_arr);

        $extraParam = [];
        $extraParam['bcc'] = $bcc_email;

        $extraParam['from_label'] = $adminName;
        // Set Email Priority. 1 = highest. 5 = lowest. 3 = normal.
        $extraParam['priority'] = 3;

        // file attachment
        $file_attach = [];
        $file_path = [];
        if (empty($files) == false) {
            foreach ($files as $key => $file) {

                $path = ARCHIEVE_DIR . '/email_activity/' . $file['upload_data']['doc_id'] . '/' .
                        $file['upload_data']['file_name'];
                //if there are two slashes
                if (strpos($path, "//") !== false) {
                    $path = str_replace("//", "/", $path);
                }
                $file_attach[] =  $path;
                $file_path[] = $path;

            }
        }

        // replace <p></p> into <br/>
        $msg_content = str_replace("<p><br /></p>", "<p></p>", $req_content);
        $msg_content = str_replace("<p><br></p>", "<p></p>", $msg_content);
        $replace_content = preg_replace("/<p[^>]*?>/", "", $msg_content);
        $req_content = str_replace("</p>", "<br />", $replace_content);

        // send email
        $status = send_mail($to_email, $req_subject, $req_content, $cc_email, $file_attach, $extraParam);

        //Remove App server files once email sent
        if(getenv('IS_APPSERVER_UPLOAD') != 'yes' && $file_path) {

            foreach($file_path as $file) {
                //Remove all files inside the applicant folder
				array_map('unlink', glob(FCPATH . $file."/*.*"));
				//Delete Applicant directory
				@rmdir(FCPATH . $file);
            }
        }
        return $status;
    }

    /*
     * Save email activity
     * @param {obj} $reqData - posted values
     * @param {str} $mode - create or update
     * @param {int} $adminId - created by
     * return type int - activity id
     */
    function save_email_data($reqData, $mode, $adminId) {
        require_once APPPATH . 'Classes/sales/SalesActivity.php';
        // load contact model
        $this->load->model('Contact_model');
        // create activity log for it
        $act_ob = new SalesActivity();

        $act_ob->setActivity_type(2);
        $act_ob->setSubject($reqData->subject ?? null);
        $act_ob->setComment($reqData->content ?? null);
        $act_ob->setRelated_to($reqData->related_to ?? null);
        $act_ob->setRelated_type($reqData->related_type ?? null);
        $act_ob->setEntity_id($reqData->salesId);
        $act_ob->setEntity_type($this->Contact_model->give_id_of_entity_type($reqData->sales_type));
        $act_ob->setCreated_by($adminId);
        // create activity
        $activity_id = $act_ob->createActivity();

        return $activity_id;
    }

    /*
     * Save the email recipient
     * @param {obj} $reqData - posted values
     * @param {int} $activity_id
     * @param {int} $current_admin - created by
     * @param {str} $mode - create or update
     */
    function save_email_recipient($reqData, $activity_id, $current_admin, $mode) {
        $email_recipient = [];
        $req_to_email = json_decode($reqData->to_user);
        $req_cc_email = json_decode($reqData->cc_user);
        $req_bcc_email = json_decode($reqData->bcc_user);

        $inc_value = 0;

        // To email contact
        foreach ($req_to_email as $key => $email) {
            $email_recipient[$inc_value]['activity_type'] = 2;
            $email_recipient[$inc_value]['type'] = 2;
            $email_recipient[$inc_value]['recipient'] = $email->id;
            $email_recipient[$inc_value]['activity_id'] = $activity_id;
            $email_recipient[$inc_value]['created_at'] = DATE_TIME;
            $email_recipient[$inc_value]['entity_type'] = $this->give_id_of_entity_type($email->type);
            $inc_value ++;
        }

        // Cc email contact
        foreach ($req_cc_email as $key => $email) {
            $email_recipient[$inc_value]['activity_type'] = 2;
            $email_recipient[$inc_value]['type'] = 3;
            $email_recipient[$inc_value]['recipient'] = $email->id;
            $email_recipient[$inc_value]['activity_id'] = $activity_id;
            $email_recipient[$inc_value]['created_at'] = DATE_TIME;
            $email_recipient[$inc_value]['entity_type'] = $this->give_id_of_entity_type($email->type);
            $inc_value ++;
        }

        // Bcc email contact
        foreach ($req_bcc_email as $key => $email) {
            $email_recipient[$inc_value]['activity_type'] = 2;
            $email_recipient[$inc_value]['type'] = 4;
            $email_recipient[$inc_value]['recipient'] = $email->id;
            $email_recipient[$inc_value]['activity_id'] = $activity_id;
            $email_recipient[$inc_value]['created_at'] = DATE_TIME;
            $email_recipient[$inc_value]['entity_type'] = $this->give_id_of_entity_type($email->type);
            $inc_value ++;
        }

        if (!empty($email_recipient)) {
            $this->basic_model->insert_records('sales_activity_recipient', $email_recipient, true);
        }
    }

    /*
     * Save the uploaded attachment to permanent folder
     * @param {int} $activity_id
     */
    function upload_content_attachement($activity_id, $contentId, $template_attachments = []) {
        $error = array();
        $response = array();
        require_once APPPATH . 'Classes/common/Aws_file_upload.php';
        $awsFileupload = new Aws_file_upload();
        if (!empty($_FILES)) {

            $config['input_name'] = 'attachments';
            $config['directory_name'] = $activity_id;
            $config['allowed_types'] = 'jpg|jpeg|png|xlx|xls|doc|docx|pdf|csv|odt|rtf';

            //Upload files in appserver for adding email attachments
            $files = $_FILES;
            // file path create if not exist
            $fileParDir = FCPATH . EMAIL_ACTIVITY_FILE_PATH;
            if (!is_dir($fileParDir)) {
                mkdir($fileParDir, 0755);
            }
            $config['upload_path'] = EMAIL_ACTIVITY_FILE_PATH;

            if(getenv('IS_APPSERVER_UPLOAD') == 'yes') {
                $config['upload_path'] = EMAIL_ACTIVITY_FILE_PATH;
                do_muliple_upload($config);
            }

            //Assign variable again into $_FILES because do_upload method will cleared the values
            $_FILES = $files;
            
            $config['upload_path'] = S3_EMAIL_ACTIVITY_FILE_PATH;
            $response = $awsFileupload->do_muliple_upload($config, FALSE);

            $attachments = array();

            if (!empty($response)) {
                foreach ($response as $key => $val) {
                    if (isset($val['error'])) {
                        $error[]['file_error'] = $val['error'];
                    } else {
                        $val = $val['upload_data'];
                        $attachments[$key]['filename'] = $val['file_name'];
                        $attachments[$key]['file_path'] = $val['file_path'];
                        $attachments[$key]['file_type'] = $val['file_type'];
                        $attachments[$key]['created_at'] = DATE_TIME;
                        $attachments[$key]['activity_id'] = $activity_id;
                        $attachments[$key]['file_ext'] = "." . $val['file_ext'];
                        $attachments[$key]['file_size'] = $val['file_size'];
                        $attachments[$key]['aws_object_uri'] = array_key_exists('aws_object_uri', $val) ? $val['aws_object_uri'] : NULL;
                        $attachments[$key]['aws_uploaded_flag'] = array_key_exists('aws_uploaded_flag', $val) ? $val['aws_uploaded_flag'] : 0;
                        $attachments[$key]['aws_file_version_id'] = array_key_exists('aws_file_version_id', $val) ? $val['aws_file_version_id'] : NULL;
                        $attachments[$key]['aws_object_uri'] = array_key_exists('aws_object_uri', $val) ? $val['aws_object_uri'] : NULL;
                        $attachments[$key]['aws_response'] = array_key_exists('aws_response', $val) ? $val['aws_response'] : NULL;

                        //Download attachment into local for sending email
                        $awsFileupload->downloadDocumentTemp($attachments[$key]['file_path'], $activity_id,
                            'email_activity');
                    }
                }

                if (!empty($attachments)) {
                    $this->basic_model->insert_records('sales_activity_email_attachment', $attachments, true);
                }
            }
        }
		//handle attachment coming from insert template
        if (!empty($template_attachments)) {
            $temp_attachments = [];
            foreach($template_attachments as $key => $attachment) {
                $file = array(); 
                $attachment_data = json_decode($attachment);
                $awsFileupload->downloadDocumentTemp($attachment_data->file_path, $activity_id,
                            'email_activity');
                $file['upload_data']['doc_id'] = $activity_id;
                $file['upload_data']['file_name'] = $attachment_data->name;
                $response[] = $file;
                $temp_attachments[$key]['filename'] = $attachment_data->name;
                $temp_attachments[$key]['created_at'] = DATE_TIME;
                $temp_attachments[$key]['activity_id'] = $activity_id;
                $temp_attachments[$key]['aws_uploaded_flag'] = 1;
                $temp_attachments[$key]['aws_object_uri'] = $attachment_data->aws_object_uri;
                $temp_attachments[$key]['file_path'] = $attachment_data->file_path;
            }
            $this->basic_model->insert_records('sales_activity_email_attachment', $temp_attachments, true);
        }

        return $response;
    }

    /*
     * Get email detail by activity id
     * @param {array} $activityIds
     */
    function get_email_details_by_activity_ids($activityIds) {

        $this->db->select("(CASE
            when related_type = 1 THEN (select topic from tbl_opportunity as o where o.id = sa.related_to)
            when related_type = 2 THEN (select lead_topic from tbl_leads as l where l.id = sa.related_to)
            when related_type = 3 THEN (select service_agreement_id from tbl_service_agreement as sa where sa.id = sa.related_to)
            when related_type = 4 THEN (select title from tbl_need_assessment as na where na.id = sa.related_to)
            when related_type = 5 THEN (select topic from tbl_crm_risk_assessment as cra where cra.id = sa.related_to)
            ELSE ''
            end
        ) as related_to_label");
        $this->db->select("(CASE
            when type = 2 THEN
                CASE
                    WHEN saer.entity_type = 4 THEN
                    (select concat_ws(' ',firstname,lastname) from tbl_leads as lds where lds.id = saer.recipient)
                ELSE
                    (select concat_ws(' ', firstname, lastname) as label from tbl_person as pt where pt.id = saer.recipient)
                END
            ELSE ''
            end
        ) as email_to");
        $this->db->select("(CASE
            when type = 3 THEN
                CASE
                    WHEN saer.entity_type = 4 THEN
                    (select concat_ws(' ',firstname,lastname) from tbl_leads as lds where lds.id = saer.recipient)
                ELSE
                    (select concat_ws(' ', firstname, lastname) as label from tbl_person as pt where pt.id = saer.recipient)
                END
            ELSE ''
            end
        ) as email_cc");
        $this->db->select("(CASE
            when type = 4 THEN
                CASE
                    WHEN saer.entity_type = 4 THEN
                    (select concat_ws(' ',firstname,lastname) from tbl_leads as lds where lds.id = saer.recipient)
                ELSE
                    (select concat_ws(' ', firstname, lastname) as label from tbl_person as pt where pt.id = saer.recipient)
                END
            ELSE ''
            end
        ) as email_bcc");
        $this->db->select(["sa.id as activity_id", "saer.recipient", "saer.type as recipient_type", "saer.entity_type as recipient_entity_type","saer.recipient as recipient_id", "sa.comment", "sa.subject", "sa.related_to", "sa.related_type"]);
        $this->db->from('tbl_sales_activity as sa');
        $this->db->join('tbl_sales_activity_recipient as saer', 'saer.activity_id = sa.id',"INNER");
        $this->db->where_in("sa.id", $activityIds);
        $query = $this->db->get();
        $result = $query->result();

        $null_date = strtotime('0000-00-00');
        $unix_epoch = strtotime('1970-01-01');

        $return = [];
        if (!empty($result)) {
            $to_arr = [];
            $cc_arr = [];
            $bcc_arr = [];
            foreach ($result as $val) {

                $return[$val->activity_id]['to_user'] = [];
                $return[$val->activity_id]['cc_user'] = [];
                $return[$val->activity_id]['bcc_user'] = [];
                $return[$val->activity_id]['attachment'] = [];

                $return[$val->activity_id]['comment'] = $val->comment;
                $return[$val->activity_id]['subject'] = $val->subject;
                $return[$val->activity_id]['comment'] = $val->comment;
                $return[$val->activity_id]['comment'] = $val->comment;

                // check if type == 2 then to_user
                if ($val->recipient_type == 2 ) {
                    $to_arr[$val->activity_id][$val->recipient_id]['recipient_id'] = $val->recipient_id;
                    $to_arr[$val->activity_id][$val->recipient_id]['recipient'] = $val->email_to;
                    $to_arr[$val->activity_id][$val->recipient_id]['recipient_entity_type'] = $val->recipient_entity_type;
                }
                // check if type == 3 then cc_user
                if ($val->recipient_type == 3 ) {
                    $cc_arr[$val->activity_id][$val->recipient_id]['recipient_id'] = $val->recipient_id;
                    $cc_arr[$val->activity_id][$val->recipient_id]['recipient'] = $val->email_cc;
                    $cc_arr[$val->activity_id][$val->recipient_id]['recipient_entity_type'] = $val->recipient_entity_type;
                }
                // check if type == 4 then bcc_user
                if ($val->recipient_type == 4 ) {
                    $bcc_arr[$val->activity_id][$val->recipient_id]['recipient_id'] = $val->recipient_id;
                    $bcc_arr[$val->activity_id][$val->recipient_id]['recipient'] = $val->email_bcc;
                    $bcc_arr[$val->activity_id][$val->recipient_id]['recipient_entity_type'] = $val->recipient_entity_type;
                }

                if (isset($to_arr) && isset($to_arr[$val->activity_id]) && empty($to_arr[$val->activity_id]) == false) {
                    $return[$val->activity_id]['to_user'] = array_values($to_arr[$val->activity_id]);
                }

                if (isset($cc_arr) && isset($cc_arr[$val->activity_id]) && empty($cc_arr[$val->activity_id]) == false) {
                    $return[$val->activity_id]['cc_user'] = array_values($cc_arr[$val->activity_id]);
                }

                if (isset($bcc_arr) && isset($bcc_arr[$val->activity_id]) && empty($bcc_arr[$val->activity_id]) == false) {
                    $return[$val->activity_id]['bcc_user'] = array_values($bcc_arr[$val->activity_id]);
                }
            }
        }
        return $return;
    }

    /*
     * Get email attachment by activity id
     * @param {array} $activityIds
     */
    function get_email_attachment_by_activity_ids($activityIds) {

        $this->db->select(["sa.id as activity_id", "saea.id as attachment_id", "saea.filename", "saea.file_type","saea.file_path", "saea.aws_uploaded_flag"]);
        $this->db->from('tbl_sales_activity as sa');
        $this->db->join('tbl_sales_activity_email_attachment as saea', 'saea.activity_id = sa.id',"INNER");
        $this->db->where_in("sa.id", $activityIds);
        $query = $this->db->get();
        $result = $query->result();

        $return = [];
        if (!empty($result)) {
            $attach_arr = [];
            foreach ($result as $val) {
                $return[$val->activity_id] = [];
                $s3 = ($val->aws_uploaded_flag == 1) ? "true" : "false";
                $file_url = 'mediaShow/EA/' . $val->activity_id . '?filename=' . urlencode(base64_encode($val->file_path)) . '&s3=' . $s3 . '&download_as=' . $val->filename;
                $val->file_show_url = $file_url;
                $attach_arr[$val->activity_id][$val->attachment_id] = $val;
                $return[$val->activity_id] = array_values($attach_arr[$val->activity_id]);
            }
        }
        return $return;
    }

    /**
     * Determine the entity type
     * @param {str} $sales_type
     * @return {int}
     */
    function give_id_of_entity_type($sales_type) {
        switch($sales_type) {
            case "contact":
                $source_data_type = 1;
                break;
            case "organisation":
                $source_data_type = 2;
                break;
            case "opportunity":
                $source_data_type = 3;
                break;
            case "lead":
                $source_data_type = 4;
                break;
            case "service":
                $source_data_type = 5;
                break;
            case "shift":
                $source_data_type = 6;
                break;
            case "timesheet":
                $source_data_type = 7;
                break;
            case "application":
                $source_data_type = 8;
                break;
            case "interview":
                $source_data_type = 9;
                break;
            default:
                $source_data_type = 0;
                break;
        }

        return $source_data_type;
    }

    /*
     * Get Dynamic email data
     * @param {obj} $reqData - posted values
     * @param {str} $mode - create or update
     * @param {int} $adminId - created by
     * return type array
     */
    function dynamic_email_data($reqData, $mode, $adminId) {
        $email_dynamic_data = [];
        $req_to_email = json_decode($reqData->to_user);

        $inc = 1;
        $get_lead_details = [];
        $get_contact_details = [];
        $get_contact_address = [];
        // To email contact
        foreach ($req_to_email as $key => $email) {
            if ($inc < 1) {
                break;
            }
            $entity_type = $this->give_id_of_entity_type($email->type);
            // if lead get lead detail
            if ($email->type == 'lead' && $inc == 1) {
                $lead_id = $email->id;
                $get_lead_details = $this->get_lead_details_by_id($lead_id);
                $get_lead_details['Recipient_Address'] = '';
                $inc--;
                // update email dynamic data
                $email_dynamic_data = $get_lead_details;
                if (isset($get_contact_details) == true && empty($get_contact_details) == false) {
                    $email_dynamic_data = array_merge($email_dynamic_data, $get_contact_details);
                }
            }

            if ($email->type == 'contact' && $inc == 1) {
                $contact_id = $email->id;
                $get_contact_details = $this->get_contact_details($contact_id);
                $get_contact_address = $this->get_contact_address($contact_id);
                $inc--;
                // update email dynamic data
                $email_dynamic_data = $get_contact_details;
                if (isset($get_contact_address) == true && empty($get_contact_address) == false) {
                    $email_dynamic_data = array_merge($email_dynamic_data, $get_contact_address);
                }
            }
        }

        // sender details
        $get_sender_details = $this->get_sender_contact($adminId);
        if (isset($get_sender_details) == true && empty($get_sender_details) == false) {
            $email_dynamic_data = array_merge($email_dynamic_data, $get_sender_details);
        }

        return $email_dynamic_data;
    }

    /**
     * Get contact details
     * @params $id
     */
    function get_lead_details_by_id($id) {
        $query = $this->db
                ->select(["l.firstname", "l.lastname", "l.email", "l.phone", "DATE_FORMAT(l.created, '%d/%m/%Y') as created_date", "l.created_by", "l.archive", "l.id"])
                ->select("(select concat_ws(' ',m.firstname,m.lastname) from tbl_member as m where m.id = l.created_by) as created_by_name")
                ->from('tbl_leads AS l')
                ->where([
                    'l.id' => $id,
                    'l.archive' => 0,
                ])
                ->get();
        $result = $query->row_array();
        $recipient_data = [];
        $recipient_data['Recipient_FirstName'] = $result['firstname'] ?? '';
        $recipient_data['Recipient_LastName'] = $result['lastname'] ?? '';
        $recipient_data['Recipient_Email'] = $result['email'] ?? '';
        $recipient_data['Recipient_Phone'] = $result['phone'] ?? '';
        $recipient_data['Recipient_CreatedDate'] = $result['created_date'] ?? '';
        $recipient_data['Recipient_CreatedBy'] = $result['created_by_name'] ?? '';

        return $recipient_data;
    }

    /*
     * its use for get contact details
     *
     * @params $contactId
     *
     * return type string
     * complete address string
     */

    function get_contact_details($contactId) {
        $this->db->select(["p.firstname", "p.lastname", "pe.email", "pp.phone", "p.created"]);
        $this->db->select("(select concat_ws(' ',m.firstname,m.lastname) from tbl_member as m where m.id = p.created_by) as created_by_name");
        $this->db->from("tbl_person as p");
        $this->db->join('tbl_person_email pe', 'pe.person_id = p.id AND pe.primary_email = 1 AND pe.archive = 0', 'left');
        $this->db->join('tbl_person_phone pp', 'pp.person_id = p.id AND pp.primary_phone = 1 AND pp.archive = 0', 'left');
        $this->db->where("p.id", $contactId);
        $this->db->where("p.archive", 0);

        $query = $this->db->get();
        $result = $query->row_array();
        $recipient_data = [];
        $recipient_data['Recipient_FirstName'] = $result['firstname'] ?? '';
        $recipient_data['Recipient_LastName'] = $result['lastname'] ?? '';
        $recipient_data['Recipient_Email'] = $result['email'] ?? '';
        $recipient_data['Recipient_Phone'] = $result['phone'] ?? '';
        $recipient_data['Recipient_CreatedDate'] = $result['created'] ?? '';
        $recipient_data['Recipient_CreatedBy'] = $result['created_by_name'] ?? '';

        return $recipient_data;
    }

    /*
     * its use for get contact address
     *
     * @params $contactId
     *
     * return type string
     * complete address string
     */

    function get_contact_address($contactId) {
        $this->db->select(["concat(pt.street,', ',pt.suburb,' ',(select s.name from tbl_state as s where s.id = pt.state),' ',pt.postcode) as address"]);
        $this->db->from("tbl_person_address as pt");
        $this->db->where("pt.person_id", $contactId);
        $this->db->where("pt.primary_address", 1);
        $this->db->where("pt.archive", 0);

        $address = $this->db->get()->row("address");
        $recipient_con_addr = [];
        $recipient_con_addr['Recipient_Address'] = $address ?? '';
        return $recipient_con_addr;
    }

    /*
     * its use for get sender details
     *
     * @params $contactId
     *
     * return type string
     * complete address string
     */

    function get_sender_contact($memberId) {
        $column = array('firstname', 'middlename', 'lastname');
        $where = array('id' => $memberId);
        $result = $this->basic_model->get_row('member', $column, $where);

        $column = array('phone', 'primary_phone as isprimary');
        $where = array('memberId' => $memberId,'archive'=>0);
        $result_ph = $this->basic_model->get_row('member_phone', $column,$where);

        $column = array('email', 'primary_email as isprimary');
        $where = array('memberId' => $memberId,'archive'=>0, 'primary_email' => 1);
        $result_mail = $this->basic_model->get_row('member_email', $column,$where);

        $sender_con = [];
        $sender_con['Sender_FirstName'] = $result->firstname ?? '';
        $sender_con['Sender_LastName'] = $result->lastname ?? '';
        $sender_con['Sender_Email'] = $result_mail->email ?? '';

        return $sender_con;
    }

    /**
     * Get dynamic fields
     */
    function get_dynamic_fields() {
        $dynamic_field = [
            ['key' => "Sender_FirstName", 'value' => "{{{Sender_FirstName}}}", 'label' => "First Name"],
            ['key' => "Sender_LastName", 'value' => "{{{Sender_LastName}}}", 'label' => "Last Name"],
            ['key' => "Sender_Email", 'value' => "{{{Sender_Email}}}", 'label' => "Email"],
            ['key' => "Recipient_FirstName", 'value' => "{{{Recipient_FirstName}}}", 'label' => "First Name"],
            ['key' => "Recipient_LastName", 'value' => "{{{Recipient_LastName}}}", 'label' => "Last Name"],
            ['key' => "Recipient_Email", 'value' => "{{{Recipient_Email}}}", 'label' => "Email"],
            ['key' => "Recipient_Phone", 'value' => "{{{Recipient_Phone}}}", 'label' => "Phone"],
            ['key' => "Recipient_Address", 'value' => "{{{Recipient_Address}}}", 'label' => "Address"],
            ['key' => "Recipient_CreatedBy", 'value' => "{{{Recipient_CreatedBy}}}", 'label' => "Created By"],
            ['key' => "Recipient_CreatedDate", 'value' => "{{{Recipient_CreatedDate}}}", 'label' => "Created Date"],

        ];

        return $dynamic_field;
    }

    /**
     * Replace dynamic fields
     */
    function replace_msg_content($msg_content, $dynamic_data) {
        $user_data = $dynamic_data;
        $dynamic_field = $this->get_dynamic_fields();
        // replace <p></p> into <br/>
        foreach ($dynamic_field as $val) {

            if (isset($user_data[$val['key']]) == true) {
                $msg_content = str_replace($val['value'], $user_data[$val['key']], $msg_content);
            }
        }

        return $msg_content;
    }
    /*
     * get the list of notes by related type     
     */
    function get_acitvity_notes_by_related_type($reqData, $adminId) {
        // activity type 4 for notes
        $related_type = $reqData->related_type;        
        $limit = $reqData->pageSize ?? 20;
        $page = $reqData->page ?? 0;
        $filter = $reqData->filtered?? null;
        $prior_query = '';
        $res = "";
        
        $src_columns = array('subject', 'DATE(sa.created)','comment', 'm.firstname','m.lastname','concat(m.firstname," ",m.lastname) as created_by', 'note_type', 'confidential');
        $entity_ids = [];
        $applicant = '';
        if (!property_exists($reqData, "entity_id") && $reqData->related_type == 8) {
            $applicant_id = $reqData->entity_parent;
            $applications = $this->Recruitment_applicant_model->get_applicant_job_application($applicant_id);
            $applicant = $this->Recruitment_applicant_model->get_applicant_info($applicant_id, $adminId);
            if (!empty($applications)) {
                foreach($applications as $application) {
                    $entity_ids[] = $application->id;
                }
            }
        }
        # text search
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                $formated_date = '';
                if($column_search=='DATE(created)'){
                    $formated_date = date('Y-m-d', strtotime(str_replace('/', '-', $filter->search)));
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $formated_date);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $formated_date);
                    }
                }
                else{
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $filter->search);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $filter->search);
                    }
                }


            }
            $this->db->group_end();
        }


        $this->db->select("(CASE
            WHEN activity_type = 4 THEN sa.subject                
            else ''
            end) as title
            ");
            
                
        $this->db->select(["activity_type", "entity_id", "entity_type", "taskId", "sa.created", 
        "sa.subject", "sa.comment", "sa.related_type", "sa.related_to", "sa.contactId", "sa.lead_id", "sa.id as activity_id",
        "concat_ws(' ',m.firstname,m.lastname) as created_by", "note_type", "confidential"]);
        
        $this->db->from("tbl_sales_activity as sa");
        $this->db->join('tbl_member as m', 'sa.created_by = m.uuid', 'left');
        $this->db->where("sa.activity_type", "4");
        if (property_exists($reqData, 'entity_id')) {
            $prior_query .= "(sa.related_to = ". $reqData->entity_id." and sa.related_type = ".$related_type.")";
            $prior_query .= " OR (sa.entity_id = ". $reqData->entity_id." and sa.entity_type = ".$related_type.")";
        } else {
            $prior_query .= "(sa.related_to IN (' " . implode("', '", $entity_ids) . "') and sa.related_type = ".$related_type.")";
            $prior_query .= " OR (sa.entity_id IN (' " . implode("', '", $entity_ids) . "') and sa.entity_type = ".$related_type.")";
        }        
     
        $this->db->where("(".$prior_query.")");
        $this->db->order_by('sa.created', 'DESC');
        $this->db->limit($limit, ($page * $limit));

        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $res = $this->db->get()->result();         
        $return = array('count' => $dt_filtered_total, 'data' => $res, 'status' => true, 'total_item' => $total_item, 'applicant' => $applicant);
       
        return $return;
    }

    /*
     * Save sms
     * @param {obj} $reqData
     * @param {int} $current_admin - logged in user id
     */
    function save_sms($reqData, $adminId) {
        # create message activity
        $activity_Id = $this->save_sms_data($reqData, 'create', $adminId);

        if ($activity_Id) {
            # save recipient
            $recipients = $reqData->recipients ?? [];
            $recipient_entity_type = $reqData->recipient_entity_type ?? '';
            $contentId = $this->save_sms_recipient($recipients, $activity_Id, $recipient_entity_type, 'create');
        }     

        return $activity_Id;
    }


    /*
     * Save sms activity
     * @param {obj} $reqData - posted values
     * @param {str} $mode - create or update
     * @param {int} $adminId - created by
     * return type int - activity id
     */
    function save_sms_data($reqData, $mode, $adminId) {
        require_once APPPATH . 'Classes/sales/SalesActivity.php';

        $subject = (string) $reqData->subject;
        $content = (string) $reqData->content;
        $template_id = (integer) $reqData->template_id;
        $entity_id = (integer) $reqData->entity_id;
        $entity_type = (string) $reqData->entity_type;

        # create sms activity
        $act_ob = new SalesActivity();
        $act_ob->setActivity_type(5);
        $act_ob->setSubject($subject ?? null);
        $act_ob->setComment($content ?? null);
        $act_ob->setRelated_to($entity_id);
        $act_ob->setRelated_type($this->give_id_of_entity_type($entity_type));
        $act_ob->setParticipantType(null);
        $act_ob->setEntity_id($entity_id);
        $act_ob->setTemplateId($template_id);
        $act_ob->setEntity_type($this->give_id_of_entity_type($entity_type));
        $act_ob->setCreated_by($adminId);

        return $act_ob->createActivity();
    }

    /*
     * Save the sms recipient
     * @param {obj} $reqRec - applications
     * @param {int} $activity_id
     * @param {int} $recipient_entity_type 
     * @param {str} $mode - create or update
     */
    function save_sms_recipient($reqRec, $activity_id, $recipient_entity_type, $mode) {
        $sms_recipient = [];
        $recipient = 0;
        $inc_value = 0;

        # To sms
        foreach ($reqRec as $key => $sms) {
            $sms_recipient[$inc_value]['activity_type'] = 5;
            $sms_recipient[$inc_value]['type'] = 2;
            $sms_recipient[$inc_value]['recipient'] = $sms->application_id;
            $sms_recipient[$inc_value]['activity_id'] = $activity_id;
            $sms_recipient[$inc_value]['created_at'] = DATE_TIME;
            $sms_recipient[$inc_value]['entity_type'] = $this->give_id_of_entity_type($recipient_entity_type);
            $inc_value ++;
        }

        if (!empty($sms_recipient)) {
            $recipient = $this->basic_model->insert_records('sales_activity_recipient', $sms_recipient, true);
        }

        return $recipient;
    }

    /*
     * Get sms detail by activity id
     * @param {array} $activityIds
     */
    function get_sms_details_by_activity_ids($activityIds, $entity_id, $entity_type) {

        $this->db->select("(CASE
            when related_type = 1 THEN (select topic from tbl_opportunity as o where o.id = sa.related_to)
            when related_type = 2 THEN (select lead_topic from tbl_leads as l where l.id = sa.related_to)
            when related_type = 3 THEN (select service_agreement_id from tbl_service_agreement as sa where sa.id = sa.related_to)
            when related_type = 4 THEN (select title from tbl_need_assessment as na where na.id = sa.related_to)
            when related_type = 5 THEN (select topic from tbl_crm_risk_assessment as cra where cra.id = sa.related_to)
            ELSE ''
            end
        ) as related_to_label");
        $this->db->select("(CASE
            when type = 2 THEN
                CASE
                    WHEN saer.entity_type = 8 THEN
                    (select concat_ws(' | ', ra.firstname, ra.lastname) as label from tbl_recruitment_applicant_applied_application as raa INNER join tbl_recruitment_applicant as ra ON ra.id = raa.applicant_id where raa.id = saer.recipient)
                ELSE ''                    
                END
            ELSE ''
            end
        ) as sms_to");
        $this->db->select("(CASE
            when type = 2 THEN
                CASE
                    WHEN saer.entity_type = 8 THEN
                    (select ra.id as applicant_id from tbl_recruitment_applicant_applied_application as raa INNER join tbl_recruitment_applicant as ra ON ra.id = raa.applicant_id where raa.id = saer.recipient)
                ELSE ''                    
                END
            ELSE ''
            end
        ) as applicant_id");
        $this->db->select(["sa.id as activity_id", "saer.recipient", "saer.type as recipient_type", "saer.entity_type as recipient_entity_type","saer.recipient as recipient_id", "sa.comment", "sa.subject", "sa.related_to", "sa.related_type"]);
        $this->db->from('tbl_sales_activity as sa');
        $this->db->join('tbl_sales_activity_recipient as saer', 'saer.activity_id = sa.id',"INNER");
        $this->db->where_in("sa.id", $activityIds);
        $query = $this->db->get();
        $result = $query->result();

        $null_date = strtotime('0000-00-00');
        $unix_epoch = strtotime('1970-01-01');

        $return = [];
        if (!empty($result)) {
            $to_arr = [];
            $to_content = [];
            foreach ($result as $val) {
                $val->recipient_entity_type = (integer) $val->recipient_entity_type;
                $val->recipient_id = (integer) $val->recipient_id;

                # replace dynamic variable
                if ($val->recipient_entity_type === $entity_type && $val->recipient_id === $entity_id) {
                    $origin_msg = $val->comment;
                    $name = explode(' | ', $val->sms_to);
                    $applicant = [];
                    if (!empty($name) && count($name) > 1) {
                        $applicant['firstname'] = $name[0];
                        $applicant['lastname'] = $name[1];
                    }
                    $applicant = (object) $applicant;
                    # Get content to dynamic variable replace with data
                    $content = modules::run('sms/Sms/get_message_text', $origin_msg, $applicant);
                    $val->comment = $content;
                    $to_content[$val->activity_id] = $content;
                }

                $sms_to = str_replace('|','',$val->sms_to);

                $return[$val->activity_id]['to_user'] = [];
                $return[$val->activity_id]['comment'] = $to_content[$val->activity_id] ?? $val->comment;
                $return[$val->activity_id]['subject'] = $val->subject;
                
                // check if type == 2 then to_user
                if ($val->recipient_type == 2 ) {
                    $to_arr[$val->activity_id][$val->recipient_id]['recipient_id'] = $val->recipient_id;
                    $to_arr[$val->activity_id][$val->recipient_id]['recipient_applicant_id'] = $val->applicant_id;
                    $to_arr[$val->activity_id][$val->recipient_id]['recipient'] = $sms_to;
                    $to_arr[$val->activity_id][$val->recipient_id]['recipient_entity_type'] = $val->recipient_entity_type;
                }                

                if (isset($to_arr) && isset($to_arr[$val->activity_id]) && empty($to_arr[$val->activity_id]) == false) {
                    $return[$val->activity_id]['to_user'] = array_values($to_arr[$val->activity_id]);
                }
            }
        }
        return $return;
    }
}
