<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'Classes/websocket/Websocket.php';

class Internal_model extends CI_Model {

    function __construct() {

        parent::__construct();
    }

    public function get_internal_messages($filter, $currentAdminId) {

        $type = $filter->type;

        $src_columns = array('im.title', 'imc.content', 'admin.firstname', 'rc_admin.firstname', 'admin.lastname', 'rc_admin.lastname', 'concat(admin.firstname," ",admin.lastname)', 'concat(rc_admin.firstname," ",rc_admin.lastname)');


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
            $this->db->group_start();
            $this->db->where("imr.recipientId = " . $currentAdminId);
            $this->db->group_end();

            $this->db->where('imc.is_draft', 0);
            $this->db->where('ima.archive', 0);
        } elseif ($type == 'sent') {
            $this->db->group_start();
            $this->db->where("imc.senderId = " . $currentAdminId);
            $this->db->group_end();

            $this->db->where('imc.is_draft', 0);
            $this->db->where('ima.archive', 0);
        } elseif ($type == 'draft') {
            $this->db->where("imc.senderId = " . $currentAdminId);

            $this->db->where('ima.archive', 0);
            $this->db->where('imc.is_draft', 1);
        } elseif ($type == 'archive') {
            $this->db->group_start();
            $this->db->where("imc.senderId = " . $currentAdminId . " Or imr.recipientId = " . $currentAdminId);
            $this->db->group_end();

            $this->db->where('ima.archive', 1);
        }


        $this->db->select(array('im.id', 'im.title', 'im.is_block', 'ima.is_fav', 'ima.is_flage', 'ima.archive', 'concat(admin.firstname," ",admin.lastname) as user_name', 'admin.gender', 'admin.profile_image', 'imc.senderId', 'imc.content', 'imc.is_priority', 'imc.id as contentId', 'imc.created'));


        $this->db->from('tbl_internal_message as im');
        $this->db->join('tbl_internal_message_content as imc', 'im.id = imc.messageId', 'left');
        $this->db->join('tbl_internal_message_action as ima', 'ima.messageId = im.id AND ima.userId = ' . $currentAdminId, 'left');
        $this->db->join('tbl_internal_message_recipient as imr', 'imr.messageContentId = imc.id', 'left');
        $this->db->join('tbl_member as admin', 'imc.senderId = admin.id', 'inner');

        # $this->db->join('tbl_internal_message_recipient as ms_rc', 'ms_rc.recipientId = admin.id', 'left');
        // add when join user search something
        if (isset($filter->search_box) && $filter->search_box != "") {
            $this->db->join('tbl_member as rc_admin', 'imr.recipientId = rc_admin.id', 'inner');
        }

        if (!empty($filter->select)) {
            if ($filter->select == 'unread') {
                $this->db->where('imr.is_read', 0);
                $this->db->where('imr.recipientId', $currentAdminId);
            } elseif ($filter->select == 'flagged') {
                $this->db->where('ima.is_flage', 1);
            } elseif ($filter->select == 'favourite') {
                $this->db->where('ima.is_fav', 1);
            } elseif ($filter->select == 'priority') {
                $this->db->where('imc.is_priority', 1);
            } elseif ($filter->select == 'attachment') {
                $this->db->join('tbl_internal_message_attachment as imattach', 'imattach.messageContentId = imc.id', 'left');
                $this->db->where('imattach.filename !=', '');
            }
        }

        $this->db->order_by('imc.created', 'desc');
        $this->db->where('imc.id IN (SELECT MAX(sub_imc.id) FROM tbl_internal_message_content as sub_imc INNER join tbl_internal_message_recipient as sub_imr ON sub_imr.messageContentId = sub_imc.id where ((sub_imc.senderId = ' . $currentAdminId . ') Or (sub_imr.recipientId = ' . $currentAdminId . ')) GROUP BY sub_imc.messageId )');
        $this->db->group_by('imc.messageId');


        $query = $this->db->get();
        $result = $query->result();

        $ext_msg = array();
        if (!empty($result)) {
            foreach ($result as $key => $val) {
                $x['id'] = $val->id;
                $x['title'] = $val->title;
                $x['mail_date'] = $val->created;
                $x['is_flage'] = $val->is_flage;
                $x['is_block'] = $val->is_block;
                $x['is_fav'] = $val->is_fav;
                $x['is_priority'] = $val->is_priority;
                $x['user_name'] = $val->user_name;
                $x['user_img'] = get_admin_img($val->senderId, $val->profile_image, $val->gender);
                $x['content'] = setting_length($val->content, 100);

                $x['have_attachment'] = $this->check_attachment($val->contentId);

                $ext_msg[] = $x;
            }
        }

        return $ext_msg;
    }

    function check_attachment($messageContentId) {
        $this->db->select(array('id'));

        $this->db->from('tbl_internal_message_attachment');
        $this->db->where('messageContentId', $messageContentId);

        $query = $this->db->get();
        $result = $query->num_rows();

        if ($result > 0) {
            return true;
        }
        return false;
    }

    public function get_single_chat($reqData, $currentAdminId) {
        $messageId = $reqData->messageId;

        if ($reqData->type == 'inbox') {
            $this->db->where('tbl_internal_message_action.archive', 0);
        } elseif ($reqData->type == 'draft') {
            $this->db->where('tbl_internal_message_action.archive', 0);
        } elseif ($reqData->type == 'archive') {

            $this->db->where('tbl_internal_message_action.archive', 1);
        }

        $this->db->select(array('tbl_internal_message.id', 'tbl_internal_message.title', 'tbl_internal_message.is_block', 'tbl_internal_message_action.is_fav', 'tbl_internal_message_action.is_flage', 'tbl_internal_message_action.is_flage'));
        $this->db->from('tbl_internal_message');

        $this->db->join('tbl_internal_message_action', 'tbl_internal_message_action.messageId = tbl_internal_message.id AND userId = ' . $currentAdminId, 'left');
        $this->db->where('tbl_internal_message_action.messageId', $messageId);


        $query = $this->db->get();
        $messageData = $query->row();
        $messageData->is_read = 1;

        if (!empty($messageData)) {
            // get all content of message
            $content_details = $this->get_internal_mail_all_content($messageId, $reqData->type, $currentAdminId);


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

    function get_internal_mail_all_content($messageId, $type, $currentAdminId) {

        if ($type == 'inbox') {
            $this->db->group_start();
            $this->db->where("imc.senderId = " . $currentAdminId . " Or imr.recipientId = " . $currentAdminId);
            $this->db->group_end();

            $this->db->where('imc.is_draft', 0);
        } elseif ($type == 'draft') {
            $this->db->where("imc.senderId = " . $currentAdminId);
            $this->db->where('imc.is_draft', 1);
        }

        $this->db->select(array('imc.id', 'imc.created', 'is_priority', 'content', 'imr.is_read', 'imc.is_draft', 'is_reply', 'concat(firstname," ",lastname) as user_name', 'admin.gender', 'admin.profile_image', 'imc.senderId', 'imr.is_notify'));

        $this->db->from('tbl_internal_message_content as imc');
        $this->db->join('tbl_internal_message_recipient as imr', 'imr.messageContentId = imc.id', 'left');
        $this->db->join('tbl_member as admin', 'imc.senderId = admin.id', 'left');
        $this->db->order_by('imc.created', 'asc');
        $this->db->group_by('imc.id');
        $this->db->where('imc.messageId', $messageId);

        $query = $this->db->get();
        $result = $query->result();

//        last_query();
        $ext_msg = array();
        if (!empty($result)) {
            foreach ($result as $key => $val) {
                $x['id'] = $val->id;
                $x['mail_date'] = $val->created;
                $x['content'] = $val->content;
                $x['is_priority'] = $val->is_priority;
                $x['is_read'] = $val->is_read;
                $x['is_draft'] = $val->is_draft;
                $x['is_reply'] = $val->is_reply;
                $x['user_name'] = $val->user_name;
                $x['is_notify'] = $val->is_notify;
                $x['user_img'] = get_admin_img($val->senderId, $val->profile_image, $val->gender);


                $sp = array('label' => $x['user_name'], 'value' => $val->senderId);
                $x['attachments'] = $this->get_mail_attachment($val->id);

                $ext_msg[] = $x;
            }
        }

        return $ext_msg;
    }

    function get_admin_by_department($departemtnId, $curretAdminId) {

        $this->db->select("id as adminId");

        $this->db->from('tbl_member as m');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"');

        $this->db->where('m.department', $departemtnId);

        // here current login user to send mail
        $this->db->where('m.id !=', $curretAdminId);


        $query = $this->db->get();
        return $query->result();
    }

    function get_admin_name($reqData, $currentAdminId) {
        $prevSel[] = $currentAdminId;
        if (!empty($reqData->previous_selected)) {
            foreach ($reqData->previous_selected as $val) {
                if ($val->member) {
                    $prevSel[] = $val->member->value;
                }
            }
        }
        if (!empty($reqData->exist)) {
            foreach ($reqData->exist as $val) {
                if ($val->adminId) {
                    $prevSel[] = $val->adminId;
                }
            }
        }


        $this->db->select("concat(firstname,' ', lastname) as label");
        $this->db->select("id as value");

        $this->db->from('tbl_member');
        $this->db->like('firstname', $reqData->search);
        $this->db->where_not_in('id', $prevSel);
        $this->db->where('department !=', 7);

        $query = $this->db->get();
        return $query->result();
    }

    function get_composer_admin_name($reqData, $currentAdminId) {
        $prevSel[] = $currentAdminId;
        if (!empty($reqData->previous_selected)) {
            foreach ($reqData->previous_selected as $val) {
                if ($val->value) {
                    $prevSel[] = $val->value;
                }
            }
        }


        $this->db->select("concat(firstname,' ', lastname) as label");
        $this->db->select("m.id as value");

        $this->db->from('tbl_member as m');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"');
        $this->db->like('firstname', $reqData->search);
        $this->db->where_not_in('m.id', $prevSel);

        $query = $this->db->get();

        return $query->result();
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
            return $messageId = $this->basic_model->insert_records('internal_message', $messageData, FALSE);
        } else {

            // update titie here
            $where_ttl = array('id' => $reqData->messageId);
            $this->basic_model->update_records('internal_message', $messageData, $where_ttl);
            return $reqData->messageId;
        }
    }

    public function compose_new_mail($reqData, $current_admin) {

        // set default it is intial message
        $reqData->is_reply = 0;

        // create message
        $messageId = $this->save_title_data($reqData, 'create');

        // create message content (message body)
        $contentId = $this->set_content_mail($reqData, $messageId, $current_admin, 'create');

        // upload attachment if have any attachement
        $this->upload_content_attachement($messageId, $contentId);

        // if forword its forword mail then copy its attachment if have any attachments
        $this->copy_forword_attachment($reqData, $messageId, $contentId);

        // set recipent data of user
        $this->set_reciepent_data($reqData, $messageId, $contentId);

        // set message action
        $this->set_message_action($reqData, $messageId, $current_admin);
    }

    function set_content_mail($reqData, $messageId, $current_admin, $mode) {
        $msg_content = $reqData->content;
        // replace <p></p> into <p><br /></p>
        $msg_content = str_replace("<p></p>", "<p><br /></p>", $msg_content);
        $messageContent = array(
            'messageId' => $messageId,
            'senderId' => $current_admin,
            'is_priority' => ($reqData->is_priority === 'true') ? 1 : 0,
            'created' => DATE_TIME,
            'content' => $msg_content,
            'is_draft' => ($reqData->submit_type == 'is_draft') ? 1 : 0,
            'is_reply' => $reqData->is_reply,
        );

        if ($mode === 'create') {

            return $this->basic_model->insert_records('internal_message_content', $messageContent, false);
        } else {
            // update content here
            $where_ttl = array('id' => $reqData->contentId);
            $this->basic_model->update_records('internal_message_content', $messageContent, $where_ttl);
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
                $config['upload_path'] = INTERNAL_IMAIL_PATH;
                //Upload files in appserver for adding email attachments
                do_muliple_upload($config);
                //Assign variable again into $_FILES because do_upload method will cleared the values
                $_FILES = $files;
            }

            require_once APPPATH . 'Classes/common/Aws_file_upload.php';
            $awsFileupload = new Aws_file_upload();

            $config['upload_path'] = S3_INTERNAL_IMAIL_PATH;
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
                    $this->basic_model->insert_records('internal_message_attachment', $attachments, true);
                }
            }
        }

        return $error;
    }

    function copy_forword_attachment($reqData, $messageId, $contentId) {
        if (!empty($reqData->forword_attachments)) {
            $config = [];
            $attachments = json_decode($reqData->forword_attachments);
            $file_path = $file_type = $file_ext = $aws_object_uri = $aws_file_version_id
            = $aws_response = '';
            $aws_uploaded_flag = 0;

            require_once APPPATH . 'Classes/common/Aws_file_upload.php';
            $awsFileupload = new Aws_file_upload();

            if (!empty($attachments)) {
                $insert_att = array();
                $file_path = $file_type = $file_ext = $aws_object_uri = $aws_file_version_id
                 = $aws_response =
                 $aws_uploaded_flag = $file_size = 0;

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

                        } else {
                            $from = INTERNAL_IMAIL_PATH . '/' . $contentId . '/' . $val->filename;
                            $att_details = $this->get_imail_attachment_details($val->id);
                            $config['from'] = $att_details->file_path;
                            $config['file_path'] = $att_details->file_path;
                        }

                        $config['to'] = S3_INTERNAL_IMAIL_PATH . $contentId . '/' . $val->filename;

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
                            $directoryName = INTERNAL_IMAIL_PATH . '/' . $contentId;

                            create_directory($directoryName);

                            $to = $directoryName . '/' . $val->filename;
                            copy($from, $to);
                        }
                    }
                }

                if (!empty($insert_att)) {
                    $this->basic_model->insert_records('internal_message_attachment', $insert_att, true);
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

        return $this->basic_model->get_row("internal_message_attachment", ["file_path", "aws_uploaded_flag", "aws_object_uri"], ["id" => $id]);
    }

    function set_reciepent_data($reqData, $messageId, $contentId) {
        $wbObj = new Websocket();
        $adminIds = array();

        // first delete all recipent data then inster new data
        $where_dlt = ['messageId' => $messageId, 'messageContentId' => $contentId];
        $this->basic_model->delete_records('internal_message_recipient', $where_dlt);

        $to_users = json_decode($reqData->to_user);
        $cc_user = json_decode($reqData->cc_user);
        $bcc_user = json_decode($reqData->bcc_user);

        $recipient_data = array();
        if(!empty($to_users)){
            foreach ($to_users as $val) {
                $recipient_data[] = array(
                    'messageContentId' => $contentId,
                    'messageId' => $messageId,
                    'recipientId' => $val->value,
                    'is_read' => 0,
                    'cc' => 0,
                );

                $adminIds[] = $val->value;

                // remove from archive
                $this->remove_mail_from_archive($messageId, $val->value);
            }
        }

        if(!empty($cc_user)){
            foreach ($cc_user as $val) {
                $recipient_data[] = array(
                    'messageContentId' => $contentId,
                    'messageId' => $messageId,
                    'recipientId' => $val->value,
                    'is_read' => 0,
                    'cc' => 1,
                );

                $adminIds[] = $val->value;

                // remove from archive
                $this->remove_mail_from_archive($messageId, $val->value, 1);
            }
        }

        if(!empty($bcc_user)){
            foreach ($bcc_user as $val) {
                $recipient_data[] = array(
                    'messageContentId' => $contentId,
                    'messageId' => $messageId,
                    'recipientId' => $val->value,
                    'is_read' => 0,
                    'cc' => 2,
                );

                $adminIds[] = $val->value;

                // remove from archive
                $this->remove_mail_from_archive($messageId, $val->value, 1);
            }
        }

        if (!empty($recipient_data)) {
            $this->basic_model->insert_records('internal_message_recipient', $recipient_data, $multiple = true);

            // check websoket here send and alert
            if ($wbObj->check_webscoket_on() && $reqData->submit_type !== 'is_draft') {
                $data = array('chanel' => 'server', 'req_type' => 'admin_internal_imail_notification', 'token' => $wbObj->get_token(), 'data' => $adminIds);
                $wbObj->send_data_on_socket($data);
            }
        }
    }

    function remove_mail_from_archive($messageId, $userId) {
        $where = array('userId' => $userId, 'messageId' => $messageId);
        $data = array('archive' => 0);
        $this->basic_model->update_records('internal_message_action', $data, $where);
    }

    function set_message_action($reqData, $messageId, $current_admin) {
        $this->db->select('userId');
        $this->db->from("tbl_internal_message_action");
        $this->db->where(array('messageId' => $messageId));
        $query = $this->db->get();
        $res = $query->result_array();

        $previous_act = array_column($res, 'userId');

        $cc_user = json_decode($reqData->cc_user, true);
        $to_user = json_decode($reqData->to_user, true);
        $bcc_user = json_decode($reqData->bcc_user, true);

        $merge_array = array_merge($cc_user, $to_user);
        $merge_array = array_merge($merge_array, $bcc_user);

        $merge_array[]['value'] = $current_admin;

        $action_data = array();
        if (!empty($merge_array)) {
            foreach ($merge_array as $val) {
                if (!in_array($val['value'], $previous_act)) {
                    $action_data[] = array(
                        'messageId' => $messageId,
                        'userId' => $val['value'],
                        'is_fav' => 0,
                        'is_flage' => 0,
                        'archive' => 0
                    );
                }
            }


            if (!empty($action_data)) {
                $this->basic_model->insert_records('internal_message_action', $action_data, true);
            }
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
        $this->set_reciepent_data($reqData, $messageId, $contentId);

        // set message action
        $this->set_message_action($reqData, $messageId, $current_admin);
    }

    function mark_read_unread($messageId, $userId, $status) {
        if ($status == 0 || $status == 1) {
            $where = array('messageId' => $messageId, 'recipientId' => $userId);

            $data = array('is_read' => $status);
            if ($status == 1) {
                $data['is_notify'] = 1;
            }

            $this->basic_model->update_records('internal_message_recipient', $data, $where);
        }
    }

    function get_reply_to_person($messageContentId, $currentAdminId, $action_type) {

        $users = array('cc_user' => [], 'to_user' => []);

        $reply_person = $this->get_mail_sender_person($messageContentId);

        if ($reply_person->value != $currentAdminId || ($action_type == 'reply')) {
            $users['to_user'][] = $reply_person;
        }

        if ($action_type == 'reply_all') {

            $this->db->select(array('imr.recipientId as value', 'imr.cc as type', 'concat(m.firstname," ",m.lastname) as label'));
            $this->db->from('tbl_internal_message_recipient as imr');
            $this->db->join('tbl_member as m', 'imr.recipientId = m.id', 'left');
            $this->db->where('imr.messageContentId', $messageContentId);
            $this->db->where('imr.recipientId !=', $currentAdminId);
            $this->db->group_by('imr.recipientId', $messageContentId);
            $this->db->where_in('imr.cc', [0,1]);

            $query = $this->db->get();
            $result = $query->result();


            if (!empty($result)) {
                foreach ($result as $val) {
                    if ($val->type == 1) {
                        $users['cc_user'][] = $val;
                    }elseif($val->type == 0) {
                        $users['to_user'][] = $val;
                    }
                }
            }
        }


        return $users;
    }

    function get_mail_sender_person($messageContentId) {
        $this->db->select(array('senderId as value', 'concat(firstname," ",lastname) as label'));
        $this->db->from('tbl_internal_message_content');
        $this->db->join('tbl_member', 'tbl_internal_message_content.senderId = tbl_member.id', 'left');
        $this->db->where('tbl_internal_message_content.id', $messageContentId);

        $query = $this->db->get();
        return $query->row();
    }

    function get_mail_attachment($messageContentId) {
        $this->db->select(array('id', 'filename','file_path', 'aws_uploaded_flag'));

        $this->db->from('tbl_internal_message_attachment');
        $this->db->where('tbl_internal_message_attachment.messageContentId', $messageContentId);

        $query = $this->db->get();
        $result = $query->result();

        if (!empty($result)) {
            foreach ($result as $val) {
                if($val->aws_uploaded_flag == 0)
                {
                    $val->file_path =  'mediaImailShowEA/EA/' . INTERNAL_IMAIL_PATH . $messageContentId . '/' . $val->filename . '&s3=false';
                } else {
                    $val->file_path = 'mediaImailShowEA/EA/' . $messageContentId . '?filename=' . urlencode(base64_encode($val->filename)) . '&mailtype=internal&s3=true';
                }
                $val->contentId = $messageContentId;

            }
        }

        return $result;
    }

    function get_admin_details($adminId) {
        $this->db->select(array('concat(firstname," ",lastname) as user_name', 'gender', 'id as adminId', 'profile_image'));

        $this->db->from('tbl_member');
        $this->db->where('id', $adminId);

        $query = $this->db->get();
        $result = $query->row();

        $user_data = array();
        if (!empty($result)) {
            $user_data['user_name'] = $result->user_name;
            $user_data['user_img'] = get_admin_img($result->adminId, $result->profile_image, $result->gender);
        }

        return $user_data;
    }

    function save_or_send_draft_mail($reqData, $current_admin) {
        // check its first reply or not
        $where_ck_ttl = ['id' => $reqData->contentId, 'messageId' => $reqData->messageId, 'is_draft' => 0];
        $message_titie = $this->basic_model->get_row('internal_message_content', array('content'), $where_ck_ttl);

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


        // first check any removal attachment and if its remove by user then also remove from database other wise only need to upload attachment
        if (!empty($reqData->forword_attachments)) {
            $attachments = json_decode($reqData->forword_attachments);

            if (!empty($attachments)) {
                foreach ($attachments as $val) {
                    if (!empty($val->removed) && $val->removed == true) {

                        // delete attachment here
                        $where_dl_att = array('id' => $val->id);
                        $this->basic_model->delete_records('internal_message_attachment', $where_dl_att);
                    }
                }
            }
        }

        // upload attachment if have any attachement
        $this->upload_content_attachement($reqData->messageId, $contentId);

        // update recipent data of user
        $this->set_reciepent_data($reqData, $reqData->messageId, $contentId);

        // update message action
        $this->set_message_action($reqData, $reqData->messageId, $current_admin);
    }

    function get_mail_content($contentId) {
        $where = ['id' => $contentId];
        $mail_data = $this->basic_model->get_row('internal_message_content', ['content', 'is_priority'], $where);
        return $mail_data;
    }

    function get_mail_title($messageId) {
        $where = ['id' => $messageId];
        $mail_data = $this->basic_model->get_row('internal_message', ['title'], $where);
        return $mail_data;
    }

    function get_mail_pre_filled_data($reqData, $currentAdminId) {

        if (!empty($reqData->action_type)) {
            $return_data = [
                'cc_user' => [],
                'to_user' => [],
                'title' => '',
                'content' => '',
                'forword_attachments' => [],
                'is_priority' => false,
                'titleFixed' => false,
            ];

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

}
