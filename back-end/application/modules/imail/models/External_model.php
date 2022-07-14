<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'Classes/websocket/Websocket.php';

class External_model extends CI_Model {

    function __construct() {

        parent::__construct();
    }

    public function get_external_messages($filter, $currentAdminId) {
        $type = $filter->type;
        $check_attachment_sub_query = $this->check_attachment_sub_query();
        $src_columns = array('em.title', 'emc.content', "concat(admin.firstname,' ',admin.lastname)", "concat(part.firstname,' ',part.middlename,' ',part.lastname)", "concat(mem.firstname,' ',mem.lastname)");


        if (isset($filter->search_box) && $filter->search_box != "") {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search_box);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search_box);
                }
            }

            $this->db->group_end();
        }

        if ($type == 'inbox') {
            $this->db->where("((emr.recipinent_type = 1 AND emr.recipinentId = " . $currentAdminId . "))");

            $this->db->where('emc.is_draft', 0);
            $this->db->where('ema.archive', 0);
        } elseif ($type == 'sent') {
            $this->db->where("((emc.sender_type = 1 AND emc.userId = " . $currentAdminId . "))");

            $this->db->where('emc.is_draft', 0);
            $this->db->where('ema.archive', 0);
        } elseif ($type == 'draft') {
            $this->db->where("emc.sender_type = 1 AND emc.userId = " . $currentAdminId);

            $this->db->where('ema.archive', 0);
            $this->db->where('emc.is_draft', 1);
        } elseif ($type == 'archive') {
            $this->db->where("((emc.sender_type = 1 AND emc.userId = " . $currentAdminId . ") Or (emr.recipinent_type = 1 AND emr.recipinentId = " . $currentAdminId . "))");

            $this->db->where('ema.archive', 1);
        }

        if (!empty($filter->select)) {
            if ($filter->select == 'unread') {
                $this->db->where('emr.is_read', 0);
                $this->db->where('emr.recipinent_type', 1);
                $this->db->where('emr.recipinentId', $currentAdminId);
            } elseif ($filter->select == 'flagged') {
                $this->db->where('ema.is_flage', 1);
            } elseif ($filter->select == 'favourite') {
                $this->db->where('ema.is_fav', 1);
            } elseif ($filter->select == 'priority') {
                $this->db->where('emc.is_priority', 1);
            } elseif ($filter->select == 'attachment') {
                $this->db->having('have_attachment', 1);
            }
        }


        $this->db->select(array('em.id', 'em.title', 'em.is_block', 'ema.is_fav', 'ema.is_flage', 'emc.content', 'emc.created', 'emc.is_priority'));

        $this->db->select("CASE emc.sender_type
            WHEN 1 THEN 'a'
            WHEN 2 THEN 'p'
            WHEN 3 THEN 'm'
            WHEN 4 THEN 'o'
            WHEN 5 THEN 'r'
            ELSE NULL
            END as sender_type");

        $this->db->select("(CASE WHEN (" . $check_attachment_sub_query . ") > 0 THEN 1 ELSE 0 end) as have_attachment");


        $this->db->select("CASE emc.sender_type
            WHEN 1 THEN (select concat_ws(' ', firstname,lastname) from tbl_member as c_m INNER JOIN tbl_department as c_d on c_d.id = c_m.department AND c_d.short_code = 'internal_staff' where c_m.id = emc.userId)
            WHEN 2 THEN (select concat_ws(' ',firstname,middlename,lastname) from tbl_participant where id = emc.userId)
            WHEN 3 THEN (select concat_ws(' ',firstname,middlename,lastname) from tbl_member as c_m INNER JOIN tbl_department as c_d on c_d.id = c_m.department AND c_d.short_code = 'external_staff' where c_m.id = emc.userId)
            WHEN 4 THEN (select concat(name)  from tbl_organisation where id = emc.userId)
            WHEN 5 THEN (select concat_ws(' ',firstname,lastname) from tbl_recruitment_applicant where id = emc.userId)
            ELSE NULL
            END as user_name");

        $this->db->from('tbl_external_message as em');
        $this->db->join('tbl_external_message_content as emc', 'em.id = emc.messageId', 'INNER');
        $this->db->join('tbl_external_message_action as ema', 'ema.messageId = em.id AND ema.userId = ' . $currentAdminId . ' AND ema.user_type = 1', 'INNER');
        $this->db->join('tbl_external_message_recipient as emr', 'emr.messageContentId = emc.id', 'INNER');


        // add when join user search something
        if (isset($filter->search_box) && $filter->search_box != "") {
            $this->db->join('tbl_member as admin', 'emr.recipinentId = admin.id AND emr.recipinent_type = 1', 'left');
            $this->db->join('tbl_participant as part', 'emr.recipinentId = part.id AND emr.recipinent_type = 2', 'left');
            $this->db->join('tbl_member as mem', 'emr.recipinentId = mem.id AND emr.recipinent_type = 3', 'left');
            $this->db->join('tbl_organisation as org', 'emr.recipinentId = org.id AND emr.recipinent_type = 4', 'left');
        }

        $this->db->where('emc.id IN (SELECT MAX(emc.id) FROM tbl_external_message_content as emc INNER join tbl_external_message_recipient as emr ON emr.messageContentId = emc.id where ((emc.sender_type = 1 AND emc.userId = ' . $currentAdminId . ') Or (emr.recipinent_type = 1 AND emr.recipinentId = ' . $currentAdminId . ')) GROUP BY emc.messageId )');
        $this->db->group_by('emc.messageId');
        $this->db->order_by('emc.created', 'desc');

        $query = $this->db->get();
        $result = $query->result();

        $ext_msg = array();
        if (!empty($result)) {
            foreach ($result as $val) {
                $x['id'] = $val->id;
                $x['title'] = $val->title;
                $x['mail_date'] = $val->created;

                $x['is_flage'] = $val->is_flage;
                $x['is_block'] = $val->is_block;
                $x['is_fav'] = $val->is_fav;
                $x['user_name'] = $val->user_name;
                $x['is_priority'] = $val->is_priority;
                $x['content'] = setting_length($val->content, 100);
                $x['have_attachment'] = $val->have_attachment;

                $x['sender_type'] = $val->sender_type;

                $ext_msg[] = $x;
            }
        }

        return $ext_msg;
    }

    public function get_single_chat($reqData, $currentAdminId) {
        $messageId = $reqData->messageId;

        if ($reqData->type == 'inbox') {
            $this->db->where('ema.archive', 0);
        }if ($reqData->type == 'sent') {
            $this->db->where('ema.archive', 0);
        } elseif ($reqData->type == 'draft') {
            $this->db->where('ema.archive', 0);
        } elseif ($reqData->type == 'archive') {
            $this->db->where('ema.archive', 1);
        }

        $this->db->select(array('em.id', 'em.title', 'em.is_block', 'ema.is_fav', 'ema.is_flage', 'ema.is_flage'));
        $this->db->from('tbl_external_message as em');

        $this->db->join('tbl_external_message_action as ema', 'ema.messageId = em.id AND ema.userId = ' . $currentAdminId . ' AND ema.user_type = 1', 'left');
        $this->db->where('ema.messageId', $messageId);


        $query = $this->db->get();
        $messageData = $query->row();



        if (!empty($messageData)) {
            $messageData->is_read = 1;

            // get all content of message
            $content_details = $this->get_external_mail_all_content($messageId, $reqData->type, $currentAdminId);

            // mark as read message
            $this->mark_read_unread($messageId, $currentAdminId, 1);

            // if its comes blank mean this user not have permission to mail
            if (!empty($content_details)) {
                $messageData->content = $content_details;

                $return = array('status' => true, 'data' => $messageData);
            } else {
                $return = array('status' => false, 'error' => 'No mail found');
            }
        } else {
            $return = array('status' => false, 'error' => 'No mail found');
        }

        return $return;
    }

    function get_mail_sender_person($messageContentId) {
        $this->db->select("CASE sender_type
            WHEN 1 THEN (select concat(firstname,' ',lastname) from tbl_member as c_m INNER JOIN tbl_department as c_d on c_m.department = c_d.id AND c_d.short_code = 'internal_staff' where c_m.id = userId)
            WHEN 2 THEN (select concat(firstname,' ', middlename,' ',lastname) from tbl_participant where id = userId)
            WHEN 3 THEN (select concat(firstname,' ', middlename,' ',lastname)  from tbl_member as c_m INNER JOIN tbl_department as c_d on c_m.department = c_d.id AND c_d.short_code = 'external_staff' where c_m.id = userId)
            WHEN 4 THEN (select concat(name)  from tbl_organisation where id = userId)
            ELSE NULL
            END as label");

        $this->db->select(array('userId as value', 'sender_type as type'));
        $this->db->from('tbl_external_message_content');
        $this->db->where('tbl_external_message_content.id', $messageContentId);

        $query = $this->db->get();
        return $query->row();
    }

    function get_reply_to_person($messageContentId, $currentAdminId, $action_type) {

        $users = array('cc_user' => [], 'to_user' => [], 'bcc_user' => []);

        $reply_person = $this->get_mail_sender_person($messageContentId);

        if ($reply_person->value != $currentAdminId || ($action_type == 'reply')) {
            $users['to_user'][] = $reply_person;
        }

        if ($action_type == 'reply_all') {

            $this->db->select("CASE tbl_external_message_recipient.recipinent_type
            WHEN 1 THEN (select concat(firstname,' ',lastname) from tbl_member as c_m INNER JOIN tbl_department as c_d on c_m.department = c_d.id AND c_d.short_code = 'internal_staff' where c_m.id = tbl_external_message_recipient.recipinentId)
            WHEN 2 THEN (select concat(firstname,' ', middlename,' ',lastname) from tbl_participant where id = tbl_external_message_recipient.recipinentId)
            WHEN 3 THEN (select concat(firstname,' ', middlename,' ',lastname)  from tbl_member as c_m INNER JOIN tbl_department as c_d on c_m.department = c_d.id AND c_d.short_code = 'external_staff' where c_m.id = tbl_external_message_recipient.recipinentId)
            WHEN 4 THEN (select concat(name)  from tbl_organisation where id = tbl_external_message_recipient.recipinentId)
            WHEN 5 THEN (select concat_ws(' ',firstname,lastname) from tbl_recruitment_applicant where id = tbl_external_message_recipient.recipinentId)
            ELSE NULL
            END as label");

            $this->db->select(array('tbl_external_message_recipient.recipinentId as value', 'tbl_external_message_recipient.recipinent_type as type', 'tbl_external_message_recipient.cc'));
            $this->db->from('tbl_external_message_recipient');
            $this->db->where('tbl_external_message_recipient.messageContentId', $messageContentId);

            $this->db->group_by('tbl_external_message_recipient.recipinentId');
            $this->db->group_by('tbl_external_message_recipient.recipinent_type');
            $this->db->where_in('tbl_external_message_recipient.cc', [0, 1, 2]);

            $query = $this->db->get();
            $result = $query->result();


            if (!empty($result)) {
                foreach ($result as $val) {
                    if ($val->cc == 1) {
                        if ($val->value != $currentAdminId) {
                            $users['cc_user'][] = $val;
                        }
                    } elseif ($val->cc == 2) {
                        if ($val->value != $currentAdminId) {
                            $users['bcc_user'][] = $val;
                        }
                    } else {
                        $users['to_user'][] = $val;
                    }
                }
            }
        }

        return $users;
    }

    function get_external_mail_all_content($messageId, $type, $currentAdminId) {
        $this->db->select("CASE emc.sender_type
            WHEN 1 THEN (select concat(firstname,' ',lastname,'||',profile_image,'||',gender,'||',c_m.id) from tbl_member as c_m INNER JOIN tbl_department as c_d on c_m.department = c_d.id AND c_d.short_code = 'internal_staff' where c_m.id = emc.userId)
            WHEN 2 THEN (select concat(firstname,' ', middlename,' ',lastname,'||', profile_image,'||',gender,'||',id) from tbl_participant where id = emc.userId)
            WHEN 3 THEN (select concat(firstname,' ', middlename,' ',lastname,'||', profile_image,'||',gender,'||',c_m.id)  from tbl_member as c_m INNER JOIN tbl_department as c_d on c_m.department = c_d.id AND c_d.short_code = 'external_staff' where c_m.id = emc.userId)
            WHEN 4 THEN (select concat(name,'||', logo_file,'||',id)  from tbl_organisation where id = emc.userId)
            ELSE NULL
            END as user_data");

        if ($type == 'inbox' || $type == 'sent') {
            $this->db->where("((emc.sender_type = 1 AND emc.userId = " . $currentAdminId . ") Or (emr.recipinent_type = 1 AND emr.recipinentId = " . $currentAdminId . "))");
            $this->db->where('emc.is_draft', 0);
        } elseif ($type == 'draft') {
            $this->db->where("emc.sender_type = 1 AND emc.userId = " . $currentAdminId);
            $this->db->where('emc.is_draft', 1);
        } elseif ($type == 'archive') {
            $this->db->where("((emc.sender_type = 1 AND emc.userId = " . $currentAdminId . ") Or (emr.recipinent_type = 1 AND emr.recipinentId = " . $currentAdminId . "))");
        } else {
            return false;
        }

        $this->db->select(array('emc.id', 'emc.created', 'emc.sender_type', 'is_priority', 'content', 'emr.is_read', 'emc.is_draft', 'is_reply', 'emr.is_notify'));

        $this->db->from('tbl_external_message_content as emc');
        $this->db->join('tbl_external_message_recipient as emr', 'emr.messageContentId = emc.id AND emr.recipinent_type = 1', 'left');
        $this->db->order_by('emc.created', 'asc');
        $this->db->group_by('emc.id');
        $this->db->where('emc.messageId', $messageId);

        $query = $this->db->get();
        $result = $query->result();

        $ext_msg = array();
        if (!empty($result)) {
            foreach ($result as $key => $val) {
                $x['id'] = $val->id;
                $x['mail_date'] = $val->created;
                $x['content'] = $val->content;
                $x['is_priority'] = $val->is_priority;
                $x['is_read'] = $val->is_read;
                $x['is_notify'] = $val->is_notify;
                $x['is_draft'] = $val->is_draft;
                $x['is_reply'] = $val->is_reply;

                $user_data = explode('||', $val->user_data);

                $sender_type = $val->sender_type;

                if (count($user_data) > 1) {
                    // here 0 = user name
                    $x['user_name'] = $user_data[0];

                    if ($sender_type == 1) {

                        // here 3 = user id // 1 = profile img name // 2 = gender
                        $x['user_img'] = get_admin_img($user_data[3], $user_data[1], $user_data[2]);
                    } else if ($sender_type == 2) {

                        // here 3 = user id  // 1 = profile img name  // 2 = gender
                        $x['user_img'] = get_participant_img($user_data[3], $user_data[1], $user_data[2]);
                    } else if ($sender_type == 3) {

                        // here 3 = user id  // 1 = profile img name  // 2 = gender
                        $x['user_img'] = get_admin_img($user_data[3], $user_data[1], $user_data[2]);
                    } else if ($sender_type == 4) {

                        // here 2 = user id  // 1 = profile img name
                        $x['user_img'] = get_org_img($user_data[2], $user_data[1]);
                    }
                }

                // get all attachments
                $x['attachments'] = $this->get_mail_attachment($val->id);

                $ext_msg[] = $x;
            }
        }

        return $ext_msg;
    }

    function check_attachment_sub_query() {
        $this->db->select(array('count(sub_ema.id)'));

        $this->db->from('tbl_external_message_attachment as sub_ema');
        $this->db->where("sub_ema.messageContentId = emc.id");

        $query = $this->db->get_compiled_select();

        return $query;
    }

    function get_mail_attachment($messageContentId, $send_mail = false) {
        $this->db->select(array('id', 'filename','file_path', 'aws_uploaded_flag', 'messageContentId'));

        $this->db->from('tbl_external_message_attachment');
        $this->db->where('tbl_external_message_attachment.messageContentId', $messageContentId);

        $query = $this->db->get();
        $result = $query->result();
        $this->load->library('AmazonS3');
        if (!empty($result)) {
            foreach ($result as $val) {

                $val->file_dir = EXTERNAL_IMAIL_PATH . '/' . $messageContentId . '/' . $val->filename;

                if($val->aws_uploaded_flag == 0)
                {
                    $val->file_path = EXTERNAL_IMAIL_PATH . '/' . $messageContentId . '/' . $val->filename . '&s3=false';

                } else {
                    $s3_file_path = $val->file_path;
                    $val->file_path = 'mediaImailShowEA/EA/' . $messageContentId . '?filename=' . urlencode(base64_encode($val->filename)) . '&mailtype=external&s3=true';

                    $filename = ($val->filename) ?? basename($val->filename);
                    $subfolder = 'external_imail';
                    $val->file_dir = ARCHIEVE_DIR . '/' . $subfolder . '/' . $messageContentId . '/' . $filename;
                    if ($send_mail == true) {
                        $this->amazons3->setFolderKey($s3_file_path);
                        $this->amazons3->setSourceFile(NULL);
                        $this->amazons3->downloadDocumentTemp($messageContentId, $subfolder);
                    }
                }
                $val->contentId = $messageContentId;
            }
        }

        return $result;
    }

    function clear_all_imail_notification() {
        $tbl_external_message_content = TBL_PREFIX . 'external_message_content';
        $this->db->where('sender_type', '2');
        $this->db->update($tbl_external_message_content, array("is_read" => 1));
        return array('status' => true);
    }

    function save_title_data($reqData, $mode) {
        $messageData = array(
            'companyId' => 1,
            'title' => $reqData->title,
            'created' => DATE_TIME,
            'is_block' => 0,
        );


        // create message
        if ($mode === 'create') {
            return $messageId = $this->basic_model->insert_records('external_message', $messageData, FALSE);
        } else {

            // update titie here
            $where_ttl = array('id' => $reqData->messageId);
            $this->basic_model->update_records('external_message', $messageData, $where_ttl);
            return $reqData->messageId;
        }
    }

    function compose_new_mail($reqData, $current_admin) {

        // set default it is intial message
        $reqData->is_reply = 0;

        if ($reqData->submit_type !== 'is_draft') {
            $validateToUserEmail = $this->validateToUserEmail($reqData, $current_admin);
        }

        // save title data
        $messageId = $this->save_title_data($reqData, 'create');

        // create message content (message body)
        $contentId = $this->set_content_mail($reqData, $messageId, $current_admin, 'create');

        // upload attachment if have any attachement
        $this->upload_content_attachement($messageId, $contentId);

        // if forword its forword mail then copy its attachment if have any attachments
        $this->copy_forword_attachment($reqData, $messageId, $contentId);

        // set recipent data of user
        $this->set_reciepent_data($reqData, $messageId, $contentId, $current_admin);

        // set message action
        $this->set_message_action($reqData, $messageId, $current_admin);
    }

    function set_content_mail($reqData, $messageId, $current_admin, $mode) {
        require_once APPPATH . 'Classes/Automatic_email.php';

        $updated_content = $reqData->content;
        // replace <p></p> into <p><br /></p>
        $updated_content = str_replace("<p></p>", "<p><br /></p>", $updated_content);
        if ($reqData->submit_type !== 'is_draft') {

            $this->load->model("recruitment/Recruitment_task_action");
            $admin_name = $this->Recruitment_task_action->get_admin_firstname_lastname($current_admin);


            $obj = new Automatic_email();

            $all_user_data = $this->get_to_username_firstname_lastname($reqData);
            $to_users = obj_to_arr(json_decode($reqData->to_user));

            $userId = $to_users[0]["value"] ?? 0;
            $user_type = $to_users[0]["type"] ?? 0;


            $user_data = (array) $all_user_data[$user_type][$userId];
            $user_data['admin_firstname'] = $admin_name['firstname'] ?? '';
            $user_data['admin_lastname'] = $admin_name['lastname'] ?? '';

            $obj->setDynamic_data($user_data);
            $updated_content = $obj->replace_msg_content($reqData->content);
        }

        $messageContent = array(
            'messageId' => $messageId,
            'userId' => $current_admin,
            'sender_type' => 1,
            'is_priority' => ($reqData->is_priority == 1) ? 1 : 0,
            'created' => DATE_TIME,
            'content' => $updated_content,
            'is_reply' => $reqData->is_reply,
            'is_draft' => ($reqData->submit_type == 'is_draft') ? 1 : 0,
        );

        if ($mode === 'create') {

            return $this->basic_model->insert_records('external_message_content', $messageContent, false);
        } else {
            // update content here
            $where_ttl = array('id' => $reqData->contentId);
            $this->basic_model->update_records('external_message_content', $messageContent, $where_ttl);
            return $reqData->contentId;
        }
    }

    function upload_content_attachement($messageId, $contentId) {
        $error = array();

        if (!empty($_FILES)) {
            $config['input_name'] = 'attachments';
            $config['directory_name'] = $contentId;
            $config['allowed_types'] = 'jpg|jpeg|png|xlx|xls|doc|docx|pdf|csv|odt|rtf';

            if(getenv('IS_APPSERVER_UPLOAD') == 'yes') {
                $files = $_FILES;
                $config['upload_path'] = EXTERNAL_IMAIL_PATH;
                //Upload files in appserver for adding email attachments
                do_muliple_upload($config);
                //Assign variable again into $_FILES because do_upload method will cleared the values
                $_FILES = $files;
            }

            require_once APPPATH . 'Classes/common/Aws_file_upload.php';
            $awsFileupload = new Aws_file_upload();

            $config['upload_path'] = S3_EXTERNAL_IMAIL_PATH;
            $config['module_id'] = 4;
            $response = $awsFileupload->do_muliple_upload($config, FALSE);

            $attachments = array();

            if (!empty($response)) {
                foreach ($response as $key => $val) {
                    if (isset($val['error'])) {
                        $error[]['file_error'] = $val['error'];
                    } else {
                        $val = $val['upload_data'];
                        $attachments[$key]['messageContentId'] = $contentId;
                        $attachments[$key]['filename'] = $val['file_name'];
                        $attachments[$key]['file_path'] = $val['file_path'];
                        $attachments[$key]['file_type'] = $val['file_type'];
                        $attachments[$key]['file_ext'] = "." . $val['file_ext'];
                        $attachments[$key]['file_size'] = $val['file_size'];
                        $attachments[$key]['aws_object_uri'] = array_key_exists('aws_object_uri', $val) ? $val['aws_object_uri'] : NULL;
                        $attachments[$key]['aws_uploaded_flag'] = array_key_exists('aws_uploaded_flag', $val) ? $val['aws_uploaded_flag'] : 0;
                        $attachments[$key]['aws_file_version_id'] = array_key_exists('aws_file_version_id', $val) ? $val['aws_file_version_id'] : NULL;
                        $attachments[$key]['aws_object_uri'] = array_key_exists('aws_object_uri', $val) ? $val['aws_object_uri'] : NULL;
                        $attachments[$key]['aws_response'] = array_key_exists('aws_response', $val) ? $val['aws_response'] : NULL;
                        $attachments[$key]['created'] = DATE_TIME;
                        $attachments[$key]['updated_at'] = DATE_TIME;
                    }
                }

                if (!empty($attachments)) {
                    $this->basic_model->insert_records('external_message_attachment', $attachments, true);
                }
            }
        }

        return $error;
    }

    function copy_forword_attachment($reqData, $messageId, $contentId) {
        if (!empty($reqData->forword_attachments)) {
            $config = [];
            $attachments = json_decode($reqData->forword_attachments);

            if (!empty($attachments)) {
                $insert_att = array();
                $file_path = $file_type = $file_ext = $aws_object_uri = $aws_file_version_id
                 = $aws_response = '';
                 $aws_uploaded_flag = $file_size = 0;

                 require_once APPPATH . 'Classes/common/Aws_file_upload.php';
                 $awsFileupload = new Aws_file_upload();

                foreach ($attachments as $key => $val) {
                    if (empty($val->removed)) {

                        $config['module_id'] = 4;
                        $config['file_name'] = $val->filename;

                        if (isset($val->its_template_attachment) == true && $val->its_template_attachment == true) {
                            $temp_details = $this->get_template_attachment_details($val->id);

                            $from = TEMPLATE_ATTACHMENT_UPLOAD_PATH . '/' . $val->templateId . '/' . $val->filename;

                            if(isset($temp_details) && $temp_details->aws_uploaded_flag == 1) {

                                $config['from'] = $temp_details->file_path;
                                $config['file_path'] = $temp_details->file_path;

                            }

                        } else { //Forwarded documents
                            $from = INTERNAL_IMAIL_PATH . '/' . $contentId . '/' . $val->filename;
                            $att_details = $this->get_imail_attachment_details($val->id);
                            $config['from'] = $att_details->file_path;
                            $config['file_path'] = $att_details->file_path;
                        }

                        $config['to'] = S3_EXTERNAL_IMAIL_PATH . $contentId . '/' . $val->filename;

                        $response = $awsFileupload->s3_copy_file($config);

                        if($response && array_key_exists('aws_uploaded_flag', $response) &&
                            $response['aws_uploaded_flag'] == 1) {

                            $file_ext = pathinfo($val->filename, PATHINFO_EXTENSION);
                            $file_path = $config['to'];
                            $file_type = $response['file_type'];
                            $file_size = $response['file_size'];
                            $aws_object_uri = $response['aws_object_uri'];
                            $aws_file_version_id = $response['aws_file_version_id'];
                            $aws_response = $response['aws_response'];
                            $aws_uploaded_flag = $response['aws_uploaded_flag'];
                        }

                        $insert_att[$key] = array('filename' => $val->filename, 'messageContentId' =>
                            $contentId, 'created' => DATE_TIME, 'updated_at' => DATE_TIME,
                            'file_path' => $file_path,
                            'file_type' => $file_type,
                            'file_size' => $file_size,
                            'file_ext' => '.'. $file_ext,
                            'aws_object_uri' => $aws_object_uri,
                            'aws_file_version_id' => $aws_file_version_id,
                            'aws_response' => $aws_response,
                            'aws_uploaded_flag' => $aws_uploaded_flag,
                        );

                        if(getenv('IS_APPSERVER_UPLOAD') == 'yes') {
                            if (!file_exists($from)) {
                                continue;
                            }
                            $directoryName = EXTERNAL_IMAIL_PATH . '/' . $contentId;

                            create_directory($directoryName);

                            $to = $directoryName . '/' . $val->filename;
                            copy($from, $to);
                        }


                    }
                }

                if (!empty($insert_att)) {
                    $this->basic_model->insert_records('external_message_attachment', $insert_att, true);
                }
            }
        }
    }

    //Get template details
    function get_template_attachment_details($templateAttachmentId) {

        return $this->basic_model->get_row("email_templates_attachment", ["file_path", "aws_uploaded_flag", "aws_object_uri"], ["id" => $templateAttachmentId]);
    }

    //Get Imail attachment details
    function get_imail_attachment_details($id) {

        return $this->basic_model->get_row("external_message_attachment", ["file_path", "aws_uploaded_flag", "aws_object_uri"], ["id" => $id]);
    }

    function send_external_email_to_user_on_email_address($reqData, $contentId, $collect_user, $current_admin) {
        $this->load->model("recruitment/Recruitment_task_action");
        require_once APPPATH . 'Classes/Automatic_email.php';
        require_once APPPATH . 'Classes/CommunicationLog.php';

        $obj = new Automatic_email();


        // get applicant details for replace content
        $x = $this->get_user_wise_email_details($collect_user);
        $user_details = $x["users_emails"];
        $applicants = $x["applicants"];

        if (isset($user_details['to']) == false || isset($user_details['to']) == false) {
            $response = array('status' => false, 'error' => 'Selected To user does not having email address');
            echo json_encode($response);
            exit();
        }

        $res = $this->basic_model->get_row("external_message_content", ["content"], ["id" => $contentId]);
        $msg_content = $res->content ?? $reqData->content;

        // get current admin name for set from of email
        $this->load->library('UserName');
        $admin = $this->username->getName('admin', $current_admin);

        $userdata = [
            'content' => $msg_content,
            'subject' => $reqData->title,
            'from' => $admin,
            'to' => $user_details['to'],
            'cc' => $user_details["cc"] ?? '',
            'bcc' => $user_details["bcc"] ?? ''
        ];

        $imail_attach = $this->get_mail_attachment($contentId, true);
        $attachement = '';

        if (!empty($imail_attach)) {
            $attachement = array_column($imail_attach, 'file_dir');
        }

        send_external_mail_to_user($userdata, $attachement);

        if (!empty($applicants)) {
            foreach ($applicants as $applicantId) {

                $obj_comm = new CommunicationLog();

                $obj_comm->setUser_type(1);
                $obj_comm->setUserId($applicantId);
                $obj_comm->setFrom($userdata['from']);
                $obj_comm->setTitle($userdata['subject']);
                $obj_comm->setCommunication_text($userdata['content']);
                $obj_comm->setSend_by($current_admin ?? 0);
                $obj_comm->setLog_type(2);

                $obj_comm->createCommunicationLog();
            }
        }
    }

    function set_reciepent_data($reqData, $messageId, $contentId, $current_admin) {
        $wbObj = new Websocket();

        // first delete all recipent data then inster new data
        $where_dlt = ['messageId' => $messageId, 'messageContentId' => $contentId];
        $this->basic_model->delete_records('external_message_recipient', $where_dlt);

        $to_users = json_decode($reqData->to_user);
        $cc_user = json_decode($reqData->cc_user);
        $bcc_user = json_decode($reqData->bcc_user);

        $userIds = array();

        $collect_user = [];
        if (!empty($to_users)) {
            foreach ($to_users as $val) {
                $recipient_data[] = array(
                    'messageContentId' => $contentId,
                    'messageId' => $messageId,
                    'recipinent_type' => $val->type,
                    'recipinentId' => $val->value,
                    'is_read' => 0,
                    'cc' => 0,
                );

                $userIds[$val->type][] = $val->value;
                $collect_user["to"][$val->type][] = $val->value;

                // remove from archive
                $this->remove_mail_from_archive($messageId, $val->value, $val->type);
            }
        }

        if (!empty($cc_user)) {
            foreach ($cc_user as $val) {
                $recipient_data[] = array(
                    'messageContentId' => $contentId,
                    'messageId' => $messageId,
                    'recipinent_type' => $val->type,
                    'recipinentId' => $val->value,
                    'is_read' => 0,
                    'cc' => 1,
                );

                $userIds[$val->type][] = $val->value;
                $collect_user["cc"][$val->type][] = $val->value;

                // remove from archive
                $this->remove_mail_from_archive($messageId, $val->value, 1);
            }
        }

        if (!empty($bcc_user)) {
            foreach ($bcc_user as $val) {
                $recipient_data[] = array(
                    'messageContentId' => $contentId,
                    'messageId' => $messageId,
                    'recipinent_type' => $val->type,
                    'recipinentId' => $val->value,
                    'is_read' => 0,
                    'cc' => 2,
                );

                $userIds[$val->type][] = $val->value;
                $collect_user["bcc"][$val->type][] = $val->value;

                // remove from archive
                $this->remove_mail_from_archive($messageId, $val->value, 1);
            }
        }

        if (!empty($collect_user) && $reqData->submit_type !== 'is_draft') {
            $this->send_external_email_to_user_on_email_address($reqData, $contentId, $collect_user, $current_admin);
        }

        if (!empty($recipient_data)) {
            $this->basic_model->insert_records('external_message_recipient', $recipient_data, true);

            // check websoket here send and alert
            if ($wbObj->check_webscoket_on() && $reqData->submit_type !== 'is_draft') {
                $data = array('chanel' => 'server', 'req_type' => 'user_external_imail_notification', 'token' => $wbObj->get_token(), 'data' => $userIds);
                $wbObj->send_data_on_socket($data);
            }
        }
    }

    function remove_mail_from_archive($messageId, $userId, $user_type) {
        $where = array('user_type' => $user_type, 'userId' => $userId, 'messageId' => $messageId);
        $data = array('archive' => 0);
        $this->basic_model->update_records('external_message_action', $data, $where);
    }

    function set_message_action($reqData, $messageId, $current_admin) {

        $cc_user = json_decode($reqData->cc_user, true);
        $to_user = json_decode($reqData->to_user, true);
        $bcc_user = json_decode($reqData->bcc_user, true);

        $cc_user[]['value'] = $current_admin;
        $bcc_cc_user = array_merge($cc_user, $bcc_user);


        $action_data = array();
        if (!empty($bcc_cc_user)) {
            foreach ($bcc_cc_user as $val) {
                $where_ck = ['messageId' => $messageId, 'user_type' => 1, 'userId' => $val['value']];
                $ch_res = $this->basic_model->get_row('external_message_action', ['userId'], $where_ck);

                if (empty($ch_res)) {
                    $action_data[] = array(
                        'messageId' => $messageId,
                        'user_type' => 1,
                        'userId' => $val['value'],
                        'is_fav' => 0,
                        'is_flage' => 0,
                        'archive' => 0
                    );
                }
            }
        }

        if (!empty($to_user)) {
            foreach ($to_user as $val) {
                $where_ck = ['messageId' => $messageId, 'user_type' => $val['type'], 'userId' => $val['value']];
                $ch_res = $this->basic_model->get_row('external_message_action', ['userId'], $where_ck);
                if (empty($ch_res)) {
                    $action_data[] = array(
                        'messageId' => $messageId,
                        'user_type' => $val['type'],
                        'userId' => $val['value'],
                        'is_fav' => 0,
                        'is_flage' => 0,
                        'archive' => 0
                    );
                }
            }
        }

        if (!empty($action_data)) {
            $this->basic_model->insert_records('external_message_action', $action_data, true);
        }
    }

    function reply_mail($reqData, $current_admin) {
        $messageId = $reqData->messageId;

        // set default it is reply message
        $reqData->is_reply = 1;

        // create message content (message body)
        $contentId = $this->set_content_mail($reqData, $messageId, $current_admin, 'create');

        // upload attachment if have any attachement
        $this->upload_content_attachement($messageId, $contentId);

        // set recipent data of user
        $this->set_reciepent_data($reqData, $messageId, $contentId, $current_admin);

        // set message action
        $this->set_message_action($reqData, $messageId, $current_admin);
    }

    function mark_read_unread($messageId, $userId, $status) {
        if ($status == 0 || $status == 1) {

            $where = array('messageId' => $messageId, 'recipinent_type' => 1, 'recipinentId' => $userId);
            $data = array('is_read' => $status);

            if ($status == 1) {
                $data['is_notify'] = 1;
            }
            $this->basic_model->update_records('external_message_recipient', $data, $where);
        }
    }

    function save_or_send_draft_mail($reqData, $current_admin) {
        // check its first reply or not
        $where_ck_ttl = ['id' => $reqData->contentId, 'messageId' => $reqData->messageId, 'is_draft' => 0];
        $message_titie = $this->basic_model->get_row('external_message_content', array('content'), $where_ck_ttl);

        if ($reqData->submit_type !== 'is_draft') {
            $validateToUserEmail = $this->validateToUserEmail($reqData, $current_admin);
        }

        if (empty($message_titie)) {
            // save title here
            $this->save_title_data($reqData, 'update');

            // set default it is intial message
            $reqData->is_reply = 0;
        } else {
            // set default it is second message
            $reqData->is_reply = 1;
        }


        // update message content (message body)
        $contentId = $this->set_content_mail($reqData, $reqData->messageId, $current_admin, 'update');

        // first check any removal attachment and if its remove by user then also remove from database
        if (!empty($reqData->forword_attachments)) {
            $attachments = json_decode($reqData->forword_attachments);

            if (!empty($attachments)) {
                foreach ($attachments as $val) {
                    if (!empty($val->removed) && $val->removed == true) {

                        // delete attachment here
                        $where_dl_att = array('id' => $val->id);
                        $this->basic_model->delete_records('external_message_attachment', $where_dl_att);
                    }
                }
            }
        }

        // upload attachment if have any new attachement
        $this->upload_content_attachement($reqData->messageId, $contentId);

        // update recipent data of user
        $this->set_reciepent_data($reqData, $reqData->messageId, $contentId, $current_admin);

        // update message action
        $this->set_message_action($reqData, $reqData->messageId, $current_admin);
    }

    function get_mail_title($messageId) {
        $where = ['id' => $messageId];
        $mail_data = $this->basic_model->get_row('external_message', ['title'], $where);
        return $mail_data;
    }

    function get_mail_content($contentId) {
        $where = ['id' => $contentId];
        $mail_data = $this->basic_model->get_row('external_message_content', ['content', 'is_priority'], $where);
        return $mail_data;
    }

    function get_mail_pre_filled_data($reqData, $currentAdminId) {
        $return_data = [
            'cc_user' => [],
            'to_user' => [],
            'title' => '',
            'content' => '',
            'forword_attachments' => [],
            'is_priority' => false,
            'titleFixed' => false,
        ];

        if (!empty($reqData->action_type)) {


            if ($reqData->action_type === 'reply') {
                $message_data = $this->get_mail_title($reqData->messageId);
                $return_data['title'] = 'RE: ' . $message_data->title;

                $x = $this->get_reply_to_person($reqData->contentId, $currentAdminId, $reqData->action_type);
                $return_data['to_user'] = $x['to_user'];

                $return_data['titleFixed'] = true;
            } elseif ($reqData->action_type === 'reply_all') {


                $message_data = $this->get_mail_title($reqData->messageId);
                $return_data['title'] = 'RE: ' . $message_data->title;

                $x = $this->get_reply_to_person($reqData->contentId, $currentAdminId, $reqData->action_type);

                $return_data['to_user'] = $x['to_user'];
                $return_data['cc_user'] = $x['cc_user'];
                $return_data['bcc_user'] = $x['bcc_user'];

                $return_data['titleFixed'] = true;
            } elseif ($reqData->action_type === 'forword_mail') {

                $mail_data = $this->get_mail_content($reqData->contentId);

                $return_data['is_priority'] = $mail_data->is_priority;
                $return_data['content'] = $mail_data->content;

                $return_data['forword_attachments'] = $this->get_mail_attachment($reqData->contentId);

                $message_data = $this->get_mail_title($reqData->messageId);
                $return_data['title'] = $message_data->title;
            } elseif ($reqData->action_type === 'open_draft') {
                $x = $this->get_reply_to_person($reqData->contentId, $currentAdminId, 'reply_all');

                $return_data['to_user'] = $x['to_user'];
                $return_data['cc_user'] = $x['cc_user'];
                $return_data['bcc_user'] = $x['bcc_user'];

                $mail_data = $this->get_mail_content($reqData->contentId);
                $return_data['is_priority'] = $mail_data->is_priority;
                $return_data['content'] = $mail_data->content;

                $return_data['forword_attachments'] = $this->get_mail_attachment($reqData->contentId);

                $message_data = $this->get_mail_title($reqData->messageId);
                $return_data['title'] = $message_data->title;
            }
        }

        return $return_data;
    }

    function get_external_imail_cc_and_bcc_opton($reqData, $adminId) {
        $name = $this->db->escape_str($reqData->search);
        $sql = array();

        $admin = $participant = $member = $org = $applicant = [];
        if (!empty($reqData->previous)) {
            foreach ($reqData->previous as $val) {

                if(empty($val)) {
                    continue;
                }
                if ($val->type == 1) {
                    $admin[] = $val->value;
                } elseif ($val->type == 2) {
                    $participant[] = $val->value;
                } elseif ($val->type == 3) {
                    $member[] = $val->value;
                } elseif ($val->type == 4) {
                    $org[] = $val->value;
                } elseif ($val->type == 5) {
                    $applicant[] = $val->value;
                }
            }
        }

        $this->db->select(["concat(firstname, ' ', middlename, ' ', lastname,' - ', me.email) as  label", "'1'  as type", "m.id as value"]);
        $this->db->from("tbl_member as m");
        $this->db->join("tbl_member_email as me","me.memberId=m.id");
        $this->db->join("tbl_department as d", "m.department = d.id AND d.short_code = 'internal_staff'", "INNER");
        $this->db->where("m.archive", 0);
        $this->db->where_not_in("m.id", $adminId);
        $this->db->like("concat(firstname, ' ', middlename, '', lastname)", $name);
        if (!empty($admin)) {
            $this->db->where_not_in("m.id", $admin);
        }
        $sql[] = $this->db->get_compiled_select();

        $this->db->select(["concat(firstname, ' ', middlename, ' ', lastname,' - ', pe.email) as  label", "'2'  as type", "p.id as value"]);
        $this->db->from("tbl_participant as p");
        $this->db->join("tbl_participant_email as pe","pe.participantId=p.id");
        $this->db->where("p.archive", 0);
        $this->db->like("concat(firstname, ' ', middlename, '', lastname)", $name);
        if (!empty($participant)) {
            $this->db->where_not_in("p.id", $participant);
        }
        $sql[] = $this->db->get_compiled_select();

        $this->db->select(["concat(firstname, ' ', middlename, ' ', lastname,' - ', me1.email) as  label", "'3'  as type", "m.id as value"]);
        $this->db->from("tbl_member as m");
        $this->db->join("tbl_member_email as me1","me1.memberId=m.id");
        $this->db->join("tbl_department as d", "m.department = d.id AND d.short_code = 'external_staff'", "INNER");
        $this->db->where("m.archive", 0);
        $this->db->like("concat(firstname, ' ', middlename, '', lastname)", $name);
        if (!empty($member)) {
            $this->db->where_not_in("m.id", $member);
        }
        $sql[] = $this->db->get_compiled_select();

        $this->db->select(["concat(name ,' - ', oe.email) as  label", "'4'  as type", "o.id as value"]);
        $this->db->from("tbl_organisation as o");
        $this->db->join("tbl_organisation_email as oe","oe.organisationId=o.id");
        $this->db->where("o.archive", 0);
        $this->db->like("name", $name);
        if (!empty($org)) {
            $this->db->where_not_in("o.id", $org);
        }
        $sql[] = $this->db->get_compiled_select();

        $this->db->select(["concat_ws(firstname,' ',lastname,' - ', rae.email) as  label, '5'  as type", "ra.id as value"]);
        $this->db->from("tbl_recruitment_applicant as ra");
        $this->db->join("tbl_recruitment_applicant_email as rae","rae.applicant_id=ra.id");
        $this->db->like("concat_ws(' ',firstname,lastname)", $name);
        $this->db->where("ra.archive", 0);
        if (!empty($applicant)) {
            $this->db->where_not_in("ra.id", $applicant);
        }
        $sql[] = $this->db->get_compiled_select();

        $sql = implode(' union ', $sql);
        $query = $this->db->query($sql);

        return $result = $query->result();
    }

    function get_user_wise_email_details($collected_users) {
        $applicant = [];
        $participant = [];
        $members = [];
        $org = [];
        $admins = [];

        if (!empty($collected_users)) {
            foreach ($collected_users as $users_type) {
                foreach ($users_type as $u_type => $val) {
                    if ($u_type == 5) {
                        $applicant = array_merge($applicant, $val);
                    } else if ($u_type == 4) {
                        $org = array_merge($org, $val);
                    } else if ($u_type == 3) {
                        $members = array_merge($members, $val);
                    } else if ($u_type == 2) {
                        $participant = array_merge($participant, $val);
                    } else if ($u_type == 1) {
                        $admins = array_merge($admins, $val);
                    }
                }
            }
        }

        $sql = [];
        if (!empty($applicant)) {
            $this->db->select(['rae.email', "'5' as type", "rae.applicant_id as userId"]);
            $this->db->from("tbl_recruitment_applicant_email as rae");
            $this->db->where("rae.primary_email", 1);
            $this->db->where_in("rae.applicant_id", $applicant);
            $this->db->group_by("rae.applicant_id");
            $sql[] = $this->db->get_compiled_select();
        }

        if (!empty($participant)) {
            $this->db->select(['pe.email', "'2' as type", "pe.participantId as userId"]);
            $this->db->from("tbl_participant_email as pe");
            $this->db->where("pe.primary_email", 1);
            $this->db->where_in("pe.participantId", $participant);
            $this->db->group_by("pe.participantId");
            $sql[] = $this->db->get_compiled_select();
        }

        if (!empty($members)) {
            $this->db->select(['me.email', "'3' as type", "me.memberId as userId"]);
            $this->db->from("tbl_member_email as me");
            $this->db->where("me.primary_email", 1);
            $this->db->where_in("me.memberId", $members);
            $this->db->group_by("me.memberId");
            $sql[] = $this->db->get_compiled_select();
        }

        if (!empty($org)) {
            $this->db->select(['oe.email', "'4' as type", "oe.organisationId as userId"]);
            $this->db->from("tbl_organisation_email as oe");
            $this->db->where("oe.primary_email", 1);
            $this->db->where_in("oe.organisationId", $org);
            $this->db->group_by("oe.organisationId");
            $sql[] = $this->db->get_compiled_select();
        }

        if (!empty($admins)) {
            $this->db->select(['me.email', "'1' as type", "me.memberId as userId"]);
            $this->db->from("tbl_member_email as me");
            $this->db->where("me.primary_email", 1);
            $this->db->where_in("me.memberId", $admins);
            $this->db->group_by("me.memberId");
            $sql[] = $this->db->get_compiled_select();
        }

        $sql = implode(' union ', $sql);
        $query = $this->db->query($sql);

        $result = $query->result();

        $user_emails = [];
        if ($result) {
            foreach ($result as $val) {
                $user_emails[$val->type][$val->userId] = $val->email;
            }
        }

        $return_users = [];
        if (!empty($collected_users)) {
            foreach ($collected_users as $mail_type => $users_type_details) {
                foreach ($users_type_details as $user_type => $user_d) {
                    foreach ($user_d as $userId) {
                        if (isset($user_emails[$user_type]) == true && isset($user_emails[$user_type][$userId]) == true) {
                            $return_users[$mail_type][$userId] = $user_emails[$user_type][$userId];
                        }
                    }
                }
            }
        }

        return ['users_emails' => $return_users, 'applicants' => $applicant];
    }

    function get_to_username_firstname_lastname($reqData) {
        $to_users = json_decode($reqData->to_user);

        if (!empty($to_users)) {
            foreach ($to_users as $val) {
                $val = (object) $val;

                if ($val->type == 1) {
                    $admin[] = $val->value;
                } elseif ($val->type == 2) {
                    $participant[] = $val->value;
                } elseif ($val->type == 3) {
                    $member[] = $val->value;
                } elseif ($val->type == 4) {
                    $org[] = $val->value;
                } elseif ($val->type == 5) {
                    $applicant[] = $val->value;
                }
            }
        }

        if (!empty($admin)) {
            $this->db->select("(select email from tbl_member_email as me where me.memberId = m.id AND me.primary_email = 1 AND me.archive = 0 limit 1) as email");
            $this->db->select(["firstname", "lastname", "'1'  as type", "m.id"]);
            $this->db->from("tbl_member as m");
            $this->db->join("tbl_department as d", "m.department = d.id AND d.short_code = 'internal_staff'", "INNER");
            $this->db->where("m.archive", 0);

            $this->db->where_in("m.id", $admin);

            $sql[] = $this->db->get_compiled_select();
        }

        if (!empty($participant)) {
            $this->db->select("(select email from tbl_participant_email as pe where pe.participantId = p.id AND pe.primary_email = 1 limit 1) as email");
            $this->db->select(["firstname", "lastname", "'2'  as type", "p.id"]);
            $this->db->from("tbl_participant as p");
            $this->db->where("p.archive", 0);
            $this->db->where_in("p.id", $participant);

            $sql[] = $this->db->get_compiled_select();
        }

        if (!empty($member)) {
            $this->db->select("(select email from tbl_member_email as me where me.memberId = m.id AND me.primary_email = 1 AND me.archive = 0 limit 1) as email");
            $this->db->select(["firstname", "lastname", "'3'  as type", "m.id"]);
            $this->db->from("tbl_member as m");
            $this->db->join("tbl_department as d", "m.department = d.id AND d.short_code = 'external_staff'", "INNER");
            $this->db->where("m.archive", 0);
            $this->db->where_in("m.id", $member);

            $sql[] = $this->db->get_compiled_select();
        }

        if (!empty($org)) {
            $this->db->select("(select email from tbl_organisation_email as oe where oe.organisationId = o.id AND oe.primary_email = 1 AND ee.archive = 0 limit 1) as email");
            $this->db->select(["name as firstname", "'' as lastname", "'4'  as type", "id"]);
            $this->db->from("tbl_organisation as o");
            $this->db->where("o.archive", 0);
            $this->db->where_in("o.id", $org);

            $sql[] = $this->db->get_compiled_select();
        }

        if (!empty($applicant)) {
             $this->db->select("(select email from tbl_recruitment_applicant_email as rae where rae.applicant_id = ra.id AND rae.primary_email = 1 AND rae.archive = 0 limit 1) as email");
            $this->db->select(["firstname", "lastname", "'5'  as type", "id"]);
            $this->db->from("tbl_recruitment_applicant as ra");
            $this->db->where("ra.archive", 0);
            $this->db->where_in("ra.id", $applicant);

            $sql[] = $this->db->get_compiled_select();
        }

        $sql = implode(' union ', $sql);
        $query = $this->db->query($sql);

        $result = $query->result();

        $return_user = [];
        if (!empty($result)) {
            foreach ($result as $val) {
                $return_user[$val->type][$val->id] = $val;
            }
        }

        return $return_user;
    }

    /**
     * Validate selected user email
     */
    function validateToUserEmail($reqData, $current_admin) {
        $to_users = json_decode($reqData->to_user);
        $cc_user = json_decode($reqData->cc_user);
        $bcc_user = json_decode($reqData->bcc_user);

        $userIds = array();

        $collect_user = [];
        if (!empty($to_users)) {
            foreach ($to_users as $val) {
                $recipient_data[] = array(
                    'recipinent_type' => $val->type,
                    'recipinentId' => $val->value,
                    'is_read' => 0,
                    'cc' => 0,
                );

                $userIds[$val->type][] = $val->value;
                $collect_user["to"][$val->type][] = $val->value;
            }
        }

        if (!empty($cc_user)) {
            foreach ($cc_user as $val) {
                $recipient_data[] = array(
                    'recipinentId' => $val->value,
                    'is_read' => 0,
                    'cc' => 1,
                );

                $userIds[$val->type][] = $val->value;
                $collect_user["cc"][$val->type][] = $val->value;
            }
        }

        if (!empty($bcc_user)) {
            foreach ($bcc_user as $val) {
                $recipient_data[] = array(
                    'recipinent_type' => $val->type,
                    'recipinentId' => $val->value,
                    'is_read' => 0,
                    'cc' => 2,
                );

                $userIds[$val->type][] = $val->value;
                $collect_user["bcc"][$val->type][] = $val->value;
            }
        }

        // get applicant details for replace content
        $x = $this->get_user_wise_email_details($collect_user);
        $user_details = $x["users_emails"];

        $error = [];
        $to_err = 0;
        if (!empty($to_users)) {
            foreach ($to_users as $val) {
                $userIds[$val->type][] = $val->value;
                if (isset($user_details['to']) == false || isset($user_details['to'][$val->value]) == false) {
                    $error[] = $to_err == 0 ? ' TO - ' . $val->label : $val->label;
                    $to_err ++;
                }
            }
        }

        $cc_err = 0;
        if (!empty($cc_user)) {
            foreach ($cc_user as $val) {
                if (isset($user_details['cc']) == false || isset($user_details['cc'][$val->value]) == false) {
                    $error[] = $cc_err == 0 ? ' CC - ' . $val->label : $val->label;
                    $cc_err ++;
                }
            }
        }

        $bcc_err = 0;
        if (!empty($bcc_user)) {
            foreach ($bcc_user as $val) {
                if (isset($user_details['bcc']) == false || isset($user_details['bcc'][$val->value]) == false) {
                    $error[] = $bcc_err == 0 ? ' BCC - ' . $val->label : $val->label;
                    $bcc_err ++;
                }
            }
        }

        $err_string = implode(', ', $error);
        if (isset($error) == true && count($error) > 0) {
            $response = array('status' => false, 'error' => $err_string . ' - does not having email address');
            echo json_encode($response);
            exit();
        }
    }
}
