<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Data provider model for 'Template' controller actions
 */
class Templates_model extends CI_Model {

    public function create(array $post_data, $admin_id) {
        if (!empty($post_data)) {
            $save_as = 0;
            $is_edit = $post_data['is_edit'] ?? 0;
            $msg_content = $post_data['content'];
            $msg_content = str_replace("<p></p>", "<p><br /></p>", $msg_content);
            $template_data = array(
                'name' => $post_data['name'],
                'description' => $post_data['description']?? '',
                'from' => $post_data['from'],
                'subject' => $post_data['subject'],
                'content' => $msg_content,
                'status' => 1,
                'created_by' => $admin_id,
                'updated_at' => DATE_TIME,
                'folder' => $post_data['folder']?? "private"
            );

            if ($is_edit) {
                $template_id = $post_data['template_id'];
                $this->basic_model->update_records('email_templates', $template_data, array('id' => $template_id));
            } else {
                $template_data['created_at'] = DATE_TIME;
                $template_id = $this->basic_model->insert_records('email_templates', $template_data);
            }

            $this->save_attachment_of_template($post_data, $template_id, $is_edit);
            $this->archive_attachment_of_template($post_data);

            if ($is_edit)
                $msg = 'Template updated successfully.';
            else
                $msg = 'Template created successfully.';

            return array('template_id' => $template_id, 'msg' => $msg, 'status' => true);
        }
    }

    function archive_attachment_of_template($post_data) {
        if (!empty($post_data['existing_attachment'])) {
            foreach ($post_data['existing_attachment'] as $val) {
                if (!empty($val["is_deleted"])) {
                    $this->basic_model->update_records('email_templates_attachment', ["archive" => 1], array('id' => $val["id"]));
                }
            }
        }
    }

    function save_attachment_of_template($post_data, $templateId, $is_edit = false) {
        if (!empty($post_data['attachments']) || !empty($post_data['existing_attachment'])) {
            $attachments = [];
            require_once APPPATH . 'Classes/common/Aws_file_upload.php';
            $awsFileupload = new Aws_file_upload();
            foreach ($post_data['attachments'] as $val) {
                if (!empty($val['updated_filename'])) {
                        $file = ARCHIEVE_DIR . '/' . $val['updated_filename'];
                        $filename_save = $val['updated_filename'];

                        if (!empty(file_exists($file))) {
                        create_directory(TEMPLATE_ATTACHMENT_UPLOAD_PATH . $templateId . '/');
                        $target = TEMPLATE_ATTACHMENT_UPLOAD_PATH . $templateId . '/' . $val['updated_filename'];
                        // delete file in App server if Appser upload is not enabled
                        $ARCHIEVE_DIR = ARCHIEVE_DIR;
                        if(getenv('IS_APPSERVER_UPLOAD') == 'yes' && is_dir($ARCHIEVE_DIR) == true) {
                            if (file_exists($file)) {
                                copy($file, $target);
                            }
                        }
                        

                        /* S3 Upload
                        * load amazon s3 library
                        */
                        $isUser = 'Imail Template';
                        $module_id = 4;
                        $s3_folder = S3_TEMPLATE_ATTACHMENT_UPLOAD_PATH;
                        $tmp_name = $file;
                        $directory_name = $templateId;
                        $file_path = $s3_folder . $templateId . '/' . $filename_save;

                        $path_parts = pathinfo($tmp_name);
                        $filename_wo_ext =  $path_parts['filename'];
                        $filename_ext =  $path_parts['extension'];
                        
                        $folder_key = $s3_folder . $directory_name .'/'. $filename_wo_ext .'.'. $filename_ext;

                        $config['file_name'] = $filename_save;
                        $config['upload_path'] = $s3_folder;
                        $config['directory_name'] = $directory_name;
                        $config['allowed_types'] = TEMPLATE_ATTACHMENT_UPLOAD_PATH; //'jpg|jpeg|png|xlx|xls|doc|docx|pdf|pages';
                        $config['max_size'] = DEFAULT_MAX_UPLOAD_SIZE;
                        $config['uplod_folder'] = FCPATH.'uploads/';
                        $config['adminId'] = 0;
                        $config['title'] = $isUser." Attachment Migration";
                        $config['module_id'] = 4;
                        $config['created_by'] = 0;
                        $config['from_doc_migration'] = TRUE;
                        $config['attachment_path'] = $file;
                        $s3documentAttachment = $awsFileupload->upload_from_app_to_s3($config, FALSE);
                        
                        // check here file is uploaded
                        if (isset($s3documentAttachment) == true && isset($s3documentAttachment['aws_uploaded_flag']) == true && $s3documentAttachment['aws_uploaded_flag'] == 1) {
                            $attachments[] = [
                                'file_path' => $s3documentAttachment['file_path'],
                                'aws_object_uri' => $s3documentAttachment['aws_object_uri'],
                                'aws_response' => $s3documentAttachment['aws_response'],
                                'aws_uploaded_flag' => $s3documentAttachment['aws_uploaded_flag'],
                                'aws_file_version_id' => $s3documentAttachment['aws_file_version_id'],
                                'templateId' => $templateId,
                                'filename' => $val['updated_filename'],
                                'created' => DATE_TIME,
                                'archive' => 0,
                            ];
                        } else {
                            $attachments[] = [
                                'templateId' => $templateId,
                                'filename' => $val['updated_filename'],
                                'created' => DATE_TIME,
                                'archive' => 0,
                            ];
                        }
                    }
                } else {
                    $attachments[] = [
                        'templateId' => $templateId,
                        'filename' => $val['name'],
                        'file_path' => $val['file_path'],
                        'aws_object_uri' => $val['aws_object_uri'],
                        'aws_response' => "",
                        'aws_uploaded_flag' => 1,
                        'aws_file_version_id' => 0,
                        'created' => DATE_TIME,
                        'archive' => 0,
                    ];
                }
            }
            if (!empty($post_data['existing_attachment']) && !$is_edit) {
                foreach($post_data['existing_attachment'] as $ea) {
                    if (empty($ea)) {
                        continue;
                    }
                    $attachments[] = [
                        'templateId' => $templateId,
                        'filename' => $ea['filename'],
                        'file_path' => $ea['file_path'],
                        'aws_uploaded_flag' => $ea['aws_uploaded_flag'],
                        'aws_object_uri' => $ea['aws_object_uri'],
                        'aws_response' => "",
                        'aws_file_version_id' => 0,
                        'created' => DATE_TIME,
                        'archive' => 0,
                    ];
                }
            }
            if (!empty($attachments)) {
                $this->basic_model->insert_records('email_templates_attachment', $attachments, true);
            }
        }
    }

    public function listing_template($reqData, $adminId = 0, $filter_condition = '') {
        $limit = $reqData->pageSize?? 99999;
        $page = $reqData->page?? 0;
        $sorted = $reqData->sorted?? null;
        $filter = $reqData->filtered?? null;

        $orderBy = '';
        $direction = '';

        $src_columns = array("id", "name");
        $available_column = array("id", "name", "content", "created_at", "status", "subject", "folder", 'description', 'archive');
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'et.id';
            $direction = 'desc';
        }
        $search_text = $filter->search_box?? $filter->search?? '';
        if (!empty($search_text)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];

                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $search_text);
                } else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $search_text);
                }
            }
            $this->db->group_end();

            $queryHavingData = $this->db->get_compiled_select();
            $queryHavingData = explode('WHERE', $queryHavingData);
            $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
            $this->db->having($queryHaving, null, false);
        }

        if (isset($filter->filter_status)) {
            if ($filter->filter_status == "active") {
                $this->db->where("et.status", 1);
                $this->db->where('et.archive', 0);
            } elseif ($filter->filter_status == "archive") {
                $this->db->where("et.archive", 1);
            }
        }
        if (isset($filter->template_author) && $filter->template_author === 'my') {
            $this->db->where("et.created_by", $adminId);
        }
        if (isset($filter->template_folder) && $filter->template_folder !== 'all') {
            $this->db->where("et.folder", $filter->template_folder);
        }
        $select_column = array("et.id", "et.name", "et.content", "DATE_FORMAT(et.created_at, '%d/%m/%Y') as created_at", "et.status", "et.subject", "et.folder", 'et.description', 'et.archive');

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(select concat_ws(' ',firstname,lastname) from tbl_member as m where m.uuid = et.created_by) as created_by");
        $this->db->from('tbl_email_templates as et');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }


        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        $return = array('status' => true, 'count' => $dt_filtered_total, 'data' => $dataResult, 'total_count' => $total_count, 'total_item' => $total_count);
        return $return;
    }

    function get_template_details($template_id) {
        $this->db->select(["et.id", "et.name", "et.content", "et.from", "et.subject", "et.description", "et.folder"]);
        $this->db->from("tbl_email_templates as et");
        $this->db->where("et.id", $template_id);
        $this->db->where("et.archive", 0);

        return $this->db->get()->row();
    }

    function get_template_attachment($template_id) {
        $this->db->select(["eta.id", "eta.filename", "eta.templateId", "eta.file_path", "eta.aws_uploaded_flag", "eta.aws_object_uri"]);
        $this->db->from("tbl_email_templates_attachment as eta");

        if (is_array($template_id)) {
            $this->db->where_in("eta.templateId", $template_id);
        } else {
            $this->db->where("eta.templateId", $template_id);
        }

        $this->db->where("eta.archive", 0);

        return $this->db->get()->result_array();
    }

    function archive_template($template_id) {

        // Get document atachment is exist. if yes get the recent attachments id
        $where = array('templateId' => $template_id, "archive" => 0);
        $colown = array('*');
        $this->db->select($colown);
        $this->db->from(TBL_PREFIX.'email_templates_attachment', $colown, $where);
        $this->db->where($where);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $get_attachment = $query->result();

        // move to archive

        // load amazon s3 library
        $this->load->library('AmazonS3');
        foreach($get_attachment as $key => $attachment) {
            $attachment = (array) $attachment;
            if (isset($attachment['aws_object_uri']) && $attachment['aws_object_uri'] != '') {
                $file_path = $attachment['file_path'];
                $copySource = $attachment['aws_object_uri'];
                $folder_key = 'archive/'. $file_path;
                /**
                 * set dynamic values
                 * $key should be - Uploaded file tmp storage path
                 * $folder_key shoulb - Moving path with file name archive
                 *      - you can add a folder like `folder/folder/filename.ext`
                 */
                $this->amazons3->setSourceFile($copySource);
                $this->amazons3->setFolderKey($folder_key);
                $amazons3_get = $this->amazons3->moveToArchive();
                // delete copy source file
                if ($amazons3_get['status'] == 200) {
                    $this->amazons3->setFolderKey($file_path);
                    $amazons3_delete = $this->amazons3->deleteFolder();

                    $attach_id = $attachment['id'];
                    $where = ["id" => $attach_id];
                    $update = ["archive" => 1, "file_path" => $folder_key];

                    $this->basic_model->update_records("email_templates_attachment", $update, $where);
                }
            }
        }

        $where = ["id" => $template_id];
        $update = ["archive" => 1];

        return $this->basic_model->update_records("email_templates", $update, $where);
    }

    function check_template_name_already_exist($template_name, $templateId) {
        $this->db->select("id");
        $this->db->from("tbl_email_templates as et");
        $this->db->where("name", trim($template_name));

        if ($templateId > 0) {
            $this->db->where("id != ", $templateId);
        }

        return $this->db->get()->row();
    }

    function get_email_template_name() {
        $where = ['archive' => 0, "status" => 1];
        $column = ["name as label", "content", "id as value", "subject"];
        return $this->basic_model->get_record_where("email_templates", $column, $where);
    }

    function get_dynamic_email_field_name() {
        require_once APPPATH . "Classes/Automatic_email.php";

        $obj = new Automatic_email();

        $fields = $obj->get_dynamic_fields();
        return $fields;
    } 

    /*
     * input : null
     * use: get template option with attachment
     * return option of template with attachment
     */

    function get_email_template_with_attachment_option() {
        // get all template
        $templates = $this->get_email_template_name();

        if (!empty($templates)) {
            $templateIds = array_column(obj_to_arr($templates), "value");
            $template_attachment = $this->get_template_attachment($templateIds);
            $template_attachment = obj_to_arr($template_attachment);
            
            
            $new_template_attachment = [];
            if(!empty($template_attachment)){
                foreach($template_attachment as $val){
                    $val["its_template_attachment"] = true;
                    $new_template_attachment[$val['templateId']][] = $val;
                }
            }
            
            foreach($templates as $val){
                $val->attachments = $new_template_attachment[$val->value] ?? [];
            }
        }
        
        return $templates;
    }

}
