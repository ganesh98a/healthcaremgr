<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Recruitment_cab_day_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
        $this->load->model('Document/Document_type_model');
    }

    public function get_cab_day_interview_list($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';

        $src_columns = array();
        $available_column = array("id", "task_name", "start_datetime", "end_datetime", "status", "commit_status", "location");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'rt.id';
            $direction = 'DESC';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        if (!empty($filter->srch_box)) {
            $this->db->group_start();
            $src_columns = array("rt.id", "rt.task_name");

            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $this->db->or_like($serch_column[0], $filter->srch_box);
                } else {
                    $this->db->or_like($column_search, $filter->srch_box);
                }
            }
            $this->db->group_end();
        }


        if (!empty($filter->filter_by)) {

            if ($filter->filter_by == 1) {
                $this->db->where('rt.start_datetime>', DATE_TIME);
                $this->db->where('rt.commit_status', 0);
            } else if ($filter->filter_by == 2) {
                $this->db->where('rt.commit_status', 0);
                $this->db->where('rt.start_datetime<=', DATE_TIME);
            } else if ($filter->filter_by == 3) {
                $this->db->where('rt.commit_status', 1);
            }
        }

        $select_column = array("rt.id", "rt.task_name", "rt.start_datetime", "rt.end_datetime", "rt.status", "rt.commit_status", "rl.name as location");

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        //$this->db->select("(select count('id') from tbl_recruitment_task_applicant as rta Inner join tbl_recruitment_applicant as ra on ra.id = rta.applicant_id where rta.taskId = rt.id AND rta.status = 1 AND rta.archive=0 AND ra.status=1 AND ra.flagged_status=0) as applicant_cnt");

        $this->db->from('tbl_recruitment_task as rt');
        $this->db->join('tbl_recruitment_location as rl', 'rl.id = rt.training_location AND rl.archive = 0', 'INNER');
        $this->db->where("rt.task_stage", '6');
        $this->db->where_in("rt.status", ['1', '2']);

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        //last_query(1);
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $status = 'in progress';
                if (strtotime($val->start_datetime) > strtotime(DATE_TIME) && $val->commit_status == 0) {
                    $status = 'pending';
                } else if ($val->commit_status == 1) {
                    $status = 'completed';
                }
                $val->task_overall_status = $status;
            }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count);
        return $return;
    }

    public function get_cab_day_task_applicant_details($taskId) {
        $tableMember = TBL_PREFIX . 'member';
        $tableApplicant = TBL_PREFIX . 'recruitment_applicant';
        $tableTaskApplicant = TBL_PREFIX . 'recruitment_task_applicant';
        $tableGroupCabDay = TBL_PREFIX . 'recruitment_applicant_group_or_cab_interview_detail';
        $tableApplicantEmail = TBL_PREFIX . 'recruitment_applicant_email';
        $this->db->select([
            //'Regexp_Replace(concat(ra.firstname," ",ra.middlename," ",ra.lastname),"( ){2,}", " ") as applicant_name',
            'REPLACE(concat(COALESCE(ra.firstname,"")," ",COALESCE(ra.middlename," ")," ",COALESCE(ra.lastname," ")),"  "," ")  as applicant_name',
            /* "DATE_FORMAT(ra.dob,'%d/%m/%Y') as dob" ,*/ 'ra.status as applicant_status', 'ra.flagged_status', 'ra.id as applicant_id', 'rta.application_id',
            'ragc.device_pin',
            'ragc.mark_as_no_show',
            'ragc.id as details_id',
            'ragc.app_orientation_status as app_orientation',
            'ragc.app_login_status as app_login',
            'ragc.recruitment_task_applicant_id as applicant_task_id'
        ]);
        $this->db->select(['CASE WHEN ragc.quiz_status_overseen_by>0 THEN (SELECT concat(m.firstname," ",m.middlename," ",m.lastname) as overseen_by From ' . $tableMember . ' as m where m.id=ragc.quiz_status_overseen_by) ELSE "N/A" END as overseen_by']);
        $this->db->select("(CASE
				WHEN ragc.cab_certificate_status = 0 THEN 'pending'
				WHEN ragc.cab_certificate_status = 1 THEN 'successful'
				else '' END) as cab_certificate_status");
        $this->db->select(["ragc.genrate_cab_certificate", "ragc.email_cab_certificate"]);
        $this->db->select([
            'CASE WHEN ragc.quiz_status=1 THEN "successful" WHEN ragc.quiz_status=2 THEN "unsuccessful" ELSE "pending" END as quiz_result',
            'CASE WHEN ragc.deviceId>0 THEN "1" ELSE "0" END as device_allocated',
            'quiz_submit_status as quiz_allocated',
            'CASE WHEN ragc.document_status=1 THEN "successful" WHEN ragc.document_status=2 THEN "unsuccessful" ELSE "pending" END as document_result',
            'CASE WHEN ragc.contract_status=1 THEN "in progress" ELSE "pending" END as contract_result',
            'CASE WHEN ragc.contract_status=1 THEN (SELECT CASE WHEN tdap.signed_status=0 THEN "in progress" WHEN signed_status=1 THEN "successful" WHEN tdap.signed_status=2 THEN "unsuccessful" ELSE "pending" END as contract_status_result FROM tbl_document_attachment as tda inner join tbl_document_attachment_property as tdap ON tda.id = tdap.doc_id where tda.task_applicant_id=ragc.recruitment_task_applicant_id and tda.archive=ragc.archive LIMIT 1) ELSE "pending" END as contract_result_other',
            'CASE WHEN ragc.applicant_status=1 THEN "successful" WHEN ragc.applicant_status=2 THEN "unsuccessful" ELSE "pending" END as applicant_status',
            'CASE WHEN ragc.quiz_status=1 && ragc.document_status=1 && ragc.contract_status=1 && app_login_status=1 && app_orientation_status=1 THEN "successful" ELSE "pending" END as app_on_boarding_result',
            'CASE WHEN ra.id>0 THEN (SELECT rae.email FROM ' . $tableApplicantEmail . ' as rae where rae.applicant_id=ra.id and primary_email=1 and archive=0 LIMIT 1) ELSE "N/A" END as applicant_email',
            "(SELECT CONCAT(COUNT(CASE WHEN sub_raqfa.is_answer_correct=1 THEN 1 ELSE null END),'/',COUNT(DISTINCT sub_raqfa.id)) FROM tbl_recruitment_additional_questions_for_applicant as sub_raqfa
            INNER JOIN tbl_recruitment_applicant_group_or_cab_interview_detail as sub_ragcid ON  sub_ragcid.recruitment_task_applicant_id = sub_raqfa.recruitment_task_applicant_id AND sub_raqfa.archive=sub_ragcid.archive
            INNER JOIN tbl_recruitment_interview_type as sub_riy ON  sub_riy.id = sub_ragcid.interview_type and sub_riy.key_type='cab_day'
            INNER JOIN `tbl_recruitment_additional_questions` as `sub_raq` ON `sub_raq`.`id` = `sub_raqfa`.`question_id`  AND `sub_raq`.`training_category`= sub_riy.id
            where sub_raqfa.recruitment_task_applicant_id=rta.id and sub_raqfa.archive=0) as quiz_marked"
                ], false);

        $this->db->from($tableTaskApplicant . ' as rta');
        $this->db->join($tableApplicant . ' as ra', 'ra.id = rta.applicant_id AND ra.duplicated_status=0', 'inner');
        $this->db->join($tableGroupCabDay . ' as ragc', 'ragc.recruitment_task_applicant_id = rta.id and ragc.archive=rta.archive', 'inner');
        $this->db->where('rta.taskId', $taskId);
        $this->db->where('rta.status', 1);
        $this->db->where('rta.archive', 0);
        $query = $this->db->get();
        $res = $query->result();
        return $res;
    }

    public function get_cab_day_task_applicant_specific_details($applicantId, $application_id = 0)
    {
        $this->db->select(['radc.id', 'radc.is_approved', 'rjrd.display_name as title']);
        $this->db->select([
            'CASE WHEN radc.is_approved=2 THEN 1 WHEN radc.is_approved=0 THEN 0 ELSE 2 END as outstanding_doc',
            'CASE WHEN radc.is_approved=2 THEN (SELECT attachment FROM tbl_recruitment_applicant_stage_attachment WHERE applicant_id=radc.applicant_id AND archive=0 AND uploaded_by_applicant=1 AND doc_category=radc.recruitment_doc_id AND document_status=0  ORDER BY id DESC LIMIT 1) ELSE 0 END as attachment_data',
            'CASE WHEN radc.is_approved=2 THEN (SELECT id FROM tbl_recruitment_applicant_stage_attachment WHERE applicant_id=radc.applicant_id AND archive=0 AND uploaded_by_applicant=1 AND doc_category=radc.recruitment_doc_id AND document_status=0  ORDER BY id DESC LIMIT 1) ELSE 0 END as attachment_id'
                ], false);
        $this->db->from('tbl_recruitment_applicant_doc_category as radc');
        $this->db->join('tbl_references as rjrd', 'radc.recruitment_doc_id=rjrd.id AND radc.archive=rjrd.archive AND radc.archive=0', 'inner');
        //$this->db->where('radc.is_required',1);
        $this->db->where_in("rjrd.type", ['5', '4']);
        $this->db->where('radc.applicant_id', $applicantId);
        if ($application_id) {
            $this->db->where('radc.application_id', $application_id);
        }
        $query = $this->db->get();
        $res = $query->result();
        return ['status' => true, 'data' => ['documentInfo' => $res]];
    }

    public function check_pending_review_doc_or_outstaning_document_left($applicantId, $application_id)
    {
        $data = $this->get_cab_day_task_applicant_specific_details($applicantId, $application_id);
        $data = $data['data']['documentInfo'];

        $dataReview = array_filter($data, function ($val) {
            if ($val->is_approved == 2 && $val->outstanding_doc == 1 && !empty($val->attachment_data) && !empty($val->attachment_id)) {
                return true;
            }
            return false;
        });
        $dataOutStanding = array_filter($data, function ($val) {
            if ($val->is_approved == 2 && $val->outstanding_doc == 1 && empty($val->attachment_id)) {
                return true;
            }
            return false;
        });

        if (!empty($dataReview) || !empty($dataOutStanding)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * returns the contract details based on the envelop id
     * modified to not include task_recruitment_id and its details in HCM-2947
     */
    public function get_applicant_details_by_envelope_id($envId) {
        $res = [];
        $this->db->select(['tda.task_applicant_id', 'tda.applicant_id', 'tda.id as contract_id', 'ra.status as applicant_status', "tdap.signed_status", "tda.application_id", "0 as recruiterId", "0 as stageId","dt.title","tdap.file_name"]);
        $this->db->from(TBL_PREFIX . 'document_attachment as tda');
        $this->db->join(TBL_PREFIX . 'document_attachment_property as tdap', 'tda.id = tdap.doc_id', 'inner');
        $this->db->join(TBL_PREFIX . 'recruitment_applicant as ra', 'ra.id=tda.applicant_id and ra.archive=0', 'inner');
        $this->db->join(TBL_PREFIX . 'document_type as dt', 'dt.id=tda.doc_type_id  ', 'inner');
        $this->db->where('tdap.envelope_id', $envId);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $res = $query->row();
        }
        return $res;
    }

    public function get_sub_query_interViewType() {
        /* $this->db->from('tbl_recruitment_interview_type as sub_rit');
          $this->db->select(['sub_rit.id']);
          $this->db->where('sub_rit.key_type','cab_day');
          $this->db->where('sub_rit.archive',0);
          $this->db->limit(1);
          $subQuery =$this->db->get_compiled_select(); */
        $subQuery = "SELECT `sub_rit`.`id` FROM `tbl_recruitment_interview_type` as `sub_rit` WHERE `sub_rit`.`key_type` = 'cab_day' AND `sub_rit`.`archive` = 0 LIMIT 1";
        return $subQuery;
    }

    public function commit_cabday_interview($reqData) {
        require_once APPPATH . 'Classes/RenamingFileTemporary.php';
        require_once APPPATH . 'Classes/Automatic_email.php';

        $adminId = $reqData->adminId;
        $taskId = isset($reqData->data->taskId) && $reqData->data->taskId != '' ? $reqData->data->taskId : '';
        if ($taskId == '') {
            echo json_encode(array('status' => false, 'msg' => 'Invalid request.'));
            exit();
        }

        $this->db->select(['rt.id', "rt.task_name", "rt.start_datetime"]);
        $this->db->from("tbl_recruitment_task as rt");
        $this->db->join("tbl_recruitment_task_stage as rts", "rts.id=rt.task_stage and rts.key='cab_day' and rts.archive=0 and rt.commit_status=0 and rt.status=1 and rt.id='" . $this->db->escape_str($taskId, true) . "'");
        $query = $this->db->get();
        $task_details = $query->row();
        if ($query->num_rows() <= 0) {
            echo json_encode(array('status' => false, 'msg' => 'Invalid request.'));
            exit();
        }

        $subQuery = $this->get_sub_query_interViewType();



        $this->db->select([
            'rta.id as recruitment_task_applicant_id', 'ragid.quiz_status', 'ragid.applicant_status', 'ragid.contract_status', 'ragid.genrate_cab_certificate', 'ragid.email_cab_certificate',
            'ragid.device_pin', 'rta.applicant_id',
            'ragid.app_orientation_status',
            'ragid.app_login_status',
            'ragid.document_status',
            'CASE WHEN ragid.contract_status=1 then (SELECT tdap.signed_status FROM tbl_document_attachment as tda inner join tbl_document_attachment_property as tdap on tda.id = tdap.doc_id where tda.task_applicant_id=rta.id AND tda.archive=0 order by tda.id desc limit 1 ) ELSE "0" END as contract_final_status',
            'ragid.device_pin',
            'ragid.id as applicant_cab_id',
            'rta.application_id'
        ]);
        $this->db->select("concat_ws(' ',firstname,lastname) as applicant_name");
        $this->db->select(["ra.firstname", "ra.lastname", "rta.application_id"]);
        $this->db->select("rae.email as applicant_email");
        $this->db->from('tbl_recruitment_task_applicant as rta');
        $this->db->join('tbl_recruitment_applicant as ra', 'ra.id = rta.applicant_id', 'inner');
        $this->db->join('tbl_recruitment_applicant_email as rae', 'ra.id = rae.applicant_id AND rae.primary_email = 1', 'inner');
        $this->db->join('tbl_recruitment_applicant_group_or_cab_interview_detail as ragid', 'ragid.recruitment_task_applicant_id = rta.id AND ragid.interview_type =(' . $subQuery . ')  AND ragid.archive=0', 'inner');
        $this->db->where('rta.taskId', $taskId);
        $this->db->where('rta.status', 1);
        $this->db->where('rta.archive', 0);
        $query = $this->db->get();
        $applicant_ques = $query->result_array();

        $is_update = false;
        //Getting Cab certificate id
        $cab_certificate_category = $this->Document_type_model->get_auto_generated_doc_by_title(CAB_CERTIFICATE_VALUE, 'id');

        if (!empty($applicant_ques)) {
            $update_arr = [];
            foreach ($applicant_ques as $key => $value) {
                $temp = [];
                $temp['id'] = $value['applicant_cab_id'];

                if ($value['quiz_status'] == 1 && $value['document_status'] == 1 && $value['contract_status'] == 1 && $value['contract_final_status'] == 1 /*  && $value['app_login_status'] == 1 && $value['app_orientation_status'] == 1 */) {
                    $temp['applicant_status'] = 1;

                    if ($value['genrate_cab_certificate'] == 1) {
                        $filename = $this->genrate_cab_certificate_who_passed_cab_day($value, $task_details);

                        $pdfFilePath = (FCPATH . APPLICANT_ATTACHMENT_UPLOAD_PATH . '/' . $value['applicant_id'] . '/' . $filename);

                        $filesize = filesize($pdfFilePath);

                        $member_folder = S3_APPLICANT_ATTACHMENT_UPLOAD_PATH;
                        $directory_name = $value['applicant_id'];
                        $path_parts = pathinfo($filename);
                        $filename_ext =  $path_parts['extension'];
                        $filename_wo_ext =  $path_parts['filename'];
                        $folder_key = $member_folder . $directory_name .'/'. $filename_wo_ext . '_'. time() . '.'. $filename_ext;

                        // load amazon s3 library
                        $this->load->library('AmazonS3');
                        $this->load->library('S3Loges');

                        /**
                         * set dynamic values
                         * $tmp_name should be - Uploaded file tmp storage path
                         * $folder_key shoulb - Saving path with file name into s3 bucket
                         *      - you can add a folder like `folder/folder/filename.ext`
                         */
                        $this->amazons3->setSourceFile($pdfFilePath);
                        $this->amazons3->setFolderKey($folder_key);

                        // s3 log
                        $config['file_name'] = $filename;
                        $config['raw_name'] = $filename;
                        $config['file_size'] = $filesize;
                        $config['file_path'] = $pdfFilePath;
                        $config['file_ext'] = $filename_ext;

                        $this->s3loges->setModuleId(1);
                        $this->s3loges->setCreatedAt(DATE_TIME);
                        $this->s3loges->setCreatedBy($adminId);
                        $this->s3loges->setTitle('File Transfer Initiated for Add attachment against applicant '.$directory_name);
                        $this->s3loges->setLogType('init');
                        $this->s3loges->setDescription(json_encode($config));
                        $this->s3loges->createS3Log();

                        $amazons3_updload = $this->amazons3->uploadDocument();

                        if ($amazons3_updload['status'] == 200) {
                            // success
                            $aws_uploaded_flag = 1;
                            $aws_object_uri = '';
                            $aws_file_version_id = '';
                            if (isset($amazons3_updload['data']) == true && empty($amazons3_updload['data']) == false) {
                                $data = $amazons3_updload['data'];
                                $aws_object_uri = $data['ObjectURL'] ?? '';

                                if ($aws_object_uri == '' && isset($data['@metadata']) == true && isset($data['@metadata']['effectiveUri']) == true) {
                                    $aws_object_uri = $data['@metadata']['effectiveUri'] ?? '';
                                }

                                if ($aws_file_version_id == '' && isset($data['VersionId']) == TRUE) {
                                  $aws_file_version_id = $data['VersionId'] ?? '';
                                }
                            }
                            $aws_response = $amazons3_updload['data'];
                            $this->s3loges->setTitle('Applicant '.$directory_name.' - '.$filename . ' - S3 File transfer Completed');
                            $this->s3loges->setLogType('success');
                            $this->s3loges->setDescription(json_encode($aws_response));
                            $this->s3loges->createS3Log();

                        } else {
                            // failed
                            $this->s3loges->setTitle('Applicant '.$directory_name.' - '.$filename . ' - S3 File transfer Completed with error!');
                            $this->s3loges->setLogType('failure');
                            $this->s3loges->setDescription(json_encode($aws_response));
                            $this->s3loges->createS3Log();

                            $aws_file_version_id = '';
                            $aws_object_uri = '';
                            $aws_response = $amazons3_updload['data'];
                            $aws_uploaded_flag = 0;
                        }


                        // Set default status as valid
                        $valid_status = 1;

                        $app_att['application_id'] = $value['application_id'];
                        $app_att['applicant_id'] = $value['applicant_id'];
                        $app_att['draft_contract_type'] = $app_att['is_main_stage_label'] = $app_att['stage'] =
                        $app_att['uploaded_by_applicant'] = $app_att['member_move_archive'] = 0;
                        $app_att['related_to'] = 1;
                        $app_att['entity_id'] = $value['applicant_id'];
                        $app_att['entity_type'] = 1;
                        $app_att['doc_type_id'] = (isset($cab_certificate_category)) ? $cab_certificate_category->id : 0;
                        $app_att['document_status'] = $valid_status;
                        $app_att['archive'] = 0;
                        $app_att['created_at'] = DATE_TIME;
                        $app_att['updated_at'] = DATE_TIME;

                        // send certificate on mail
                        if ($value['email_cab_certificate'] == 1) {
                            $this->load->model("Recruitment_task_action");
                            $user_data["job_title"] = $this->Recruitment_task_action->get_application_job_title($value['application_id']);

                            // get admin name
                            $admin_name = $this->Recruitment_task_action->get_admin_firstname_lastname($adminId);

                            $user_data['admin_firstname'] = $admin_name['firstname'] ?? '';
                            $user_data['admin_lastname'] = $admin_name['lastname'] ?? '';
                            $user_data['firstname'] = $value['firstname'];
                            $user_data['lastname'] = $value['lastname'];
                            $user_data['email'] = $value['applicant_email'];
                            $user_data['attach_file'] = APPLICANT_ATTACHMENT_UPLOAD_PATH . $value['applicant_id'] . '/' . $filename;

                            // use for rename the filename according to document category and applicant name
                            $objRen = new RenamingFileTemporary();
                            $objRen->setFilename($user_data['attach_file']);
                            $objRen->setRequired_filename("CAB Certificate_".$value["applicant_name"]);
                            $user_data['attach_file'] = $objRen->rename_file();

                            $obj = new Automatic_email();

                            $obj->setEmail_key("cab_certificate_applicant");
                            $obj->setEmail($user_data['email']);
                            $obj->setDynamic_data($user_data);
                            $obj->setUserId($value['applicant_id']);
                            $obj->setAttach_file($user_data['attach_file']);
                            $obj->setUser_type(1);

                            $obj->automatic_email_send_to_user();

                            // after send it to mail delete file
                            $objRen->delete_temp();
                        }

                        if(getenv('IS_APPSERVER_UPLOAD') != 'yes') {
                            unlink($pdfFilePath);
                        }
                    }
                } else {
                    $temp['applicant_status'] = 2;
                }
                $update_arr[] = $temp;
            }

            if (!empty($update_arr)) {
                if (!empty($app_att)) {
                    $this->db->insert(TBL_PREFIX . 'document_attachment', $app_att, true);
                    //Get the last inserted id
                    $insert_id = $this->db->insert_id();

                    $doc_prop_att['doc_id'] = $insert_id;
                    $doc_prop_att['file_name'] = 'CAB Certificate';
                    $doc_prop_att['file_type'] = 'application/pdf';
                    $doc_prop_att['file_ext'] = 'pdf';
                    $doc_prop_att['raw_name'] = $filename;
                    $doc_prop_att['file_size'] = $filesize;
                    $doc_prop_att['aws_uploaded_flag'] = $aws_uploaded_flag;
                    $doc_prop_att['aws_response'] = json_encode($aws_response);
                    $doc_prop_att['aws_object_uri'] = $aws_object_uri;
                    $doc_prop_att['aws_file_version_id'] = $aws_file_version_id;
                    $doc_prop_att['file_path'] = $folder_key;
                    $doc_prop_att['archive'] = 0;
                    $doc_prop_att['created_at'] = DATE_TIME;
                    $doc_prop_att['created_by'] = $adminId;
                    $doc_prop_att['created_at'] = DATE_TIME;
                    $doc_prop_att['updated_at'] = DATE_TIME;

                    $this->db->insert(TBL_PREFIX . 'document_attachment_property', $doc_prop_att);

                }

                $this->basic_model->insert_update_batch('update', 'recruitment_applicant_group_or_cab_interview_detail', $update_arr, 'id');
                $this->basic_model->update_records('recruitment_task', array('commit_status' => 1, 'action_at' => DATE_TIME, 'status' => 2), array('id' => $taskId));
                $is_update = true;
            }
        }

        if ($is_update) {
            echo json_encode(array('status' => true, 'msg' => 'Updated successfully.'));
            exit();
        } else {
            echo json_encode(array('status' => false, 'msg' => 'Error in update.'));
            exit();
        }
    }

    function get_cab_certificate_category_id() {
        $this->db->select("rjr.id");
        $this->db->from("tbl_references as rjr");
        $this->db->where("rjr.key_name", "cab_certificate");

        return $this->db->get()->row('id');
    }

    function add_applicant_cab_certificate_status($reqData) {
        // check which type of status
        if ($reqData->type === "genrate_cab_certificate") {
            $update_data["genrate_cab_certificate"] = $reqData->status;

            if ($reqData->status == 2) {
                $update_data["email_cab_certificate"] = 2;
            }
        } elseif ($reqData->type === "email_cab_certificate") {
            $update_data["email_cab_certificate"] = $reqData->status;
        }

        $where = ["recruitment_task_applicant_id" => $reqData->task_applicant_id];
        $this->basic_model->update_records("recruitment_applicant_group_or_cab_interview_detail", $update_data, $where);

        // get status of both #genrate_cab_certificate and #genrate_cab_certificate
        $column = ["genrate_cab_certificate", "email_cab_certificate"];
        $where = $where = ["recruitment_task_applicant_id" => $reqData->task_applicant_id];
        $res = $this->basic_model->get_row("recruitment_applicant_group_or_cab_interview_detail", $column, $where);

        // if genrate_cab_certificate && genrate_cab_certificate both are marked then update main status
        #print_r($res);
        if ((int) $res->genrate_cab_certificate > 0 && (int) $res->email_cab_certificate > 0) {
            $update_data = ["cab_certificate_status" => 1];
            $where = ["recruitment_task_applicant_id" => $reqData->task_applicant_id];
            $this->basic_model->update_records("recruitment_applicant_group_or_cab_interview_detail", $update_data, $where);
        }


        return true;
    }

    function genrate_cab_certificate_who_passed_cab_day($applicant_details, $task_details) {
        $pdf_data = ["applicant_name" => $applicant_details['applicant_name'], 'task_time' => $task_details->start_datetime];
        $html = $this->load->view("cab_certificate_pdf", $pdf_data, true);

        require_once APPPATH . 'third_party/mpdf7/vendor/autoload.php';

         $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/custom/font/directory',
            ]),
            'fontdata' => $fontData + [
        'gotham_light' => [
            'R' => 'GothamRnd-Light.ttf',
            'I' => 'GothamRnd-Light.ttf',

        ],
        'gotham_medium' => [
            'R' => 'GothamRnd-Medium.ttf',
            'I' => 'GothamRnd-Medium.ttf',

        ],
        'gotham_book' => [
            'R' => 'GothamRnd-Book.ttf',
            'I' => 'GothamRnd-Book.ttf',

        ]
            ],
            // 'default_font' => 'frutiger'
        ]);


        $mpdf->WriteHTML($html);
        $filename = "CAB_Certificate_" . rand(100000, 999999) . '.pdf';

        $pdfFilePath = APPLICANT_ATTACHMENT_UPLOAD_PATH . '/' . $applicant_details['applicant_id'] . '/' . $filename;
        create_directory(APPLICANT_ATTACHMENT_UPLOAD_PATH . '/' . $applicant_details['applicant_id']);

        $mpdf->Output($pdfFilePath, "F");



        return $filename;
    }

    function get_applicant_id_by_contractId($contractId) {
        $this->db->select(["tda.applicant_id", "tdap.signed_file", "tda.application_id", "tda.created_by as adminId"]);
        $this->db->from(TBL_PREFIX . 'document_attachment as tda');
        $this->db->join(TBL_PREFIX . 'document_attachment_property as tdap', 'tda.id = tdap.doc_id', 'inner');
        $this->db->join("tbl_recruitment_task_applicant as rta", "rta.id = tda.task_applicant_id", "INNER");
        $this->db->join("tbl_recruitment_task as rt", "rt.id = rta.taskId", "INNER");
        $this->db->where("rac.id", $contractId);

        return $this->db->get()->row();
    }

    /**
      * Generate cab certicate and save to attachment
      * @param {obj} reqData
      */
    function genearate_cab_certificate($reqData) {
        $data = $reqData->data;
        $applicant_id = $data->applicant_id;
        $application_id = $data->application_id;
        $adminId = $reqData->adminId;
        $applicant = [];
        // get applicant name by appicant id
        $this->db->select([ "concat_ws(' ',firstname,lastname) as applicant_name", "id as applicant_id" ]);
        $this->db->from('tbl_recruitment_applicant as ra');
        $this->db->where('ra.id', $applicant_id);
        $query = $this->db->get();
        $applicant = $query->row_array();

        $applicant_name = '';
        if (isset($applicant) == true && empty($applicant) == false) {
            $applicant_name = $applicant['applicant_name'];
        } else {
            $applicant['applicant_name'] = '';
            $applicant['applicant_id'] = 'temp';
            return ['status' => false, 'error' => 'Applicant not found.'];
        }

        // generate cab certificate id
        $cab_certificate_category = $this->Document_type_model->get_auto_generated_doc_by_title(CAB_CERTIFICATE_VALUE, 'id');

        // if null return empty
        if (!isset($cab_certificate_category)) {
            return ['status' => false, 'error' => 'Cab certificate not getting generate.'];
        }

        $task_details = [];
        $task_details['start_datetime'] = date('Y-m-d');
        $task_details = (object) $task_details;
        // geneate cab certificate
        $filename = $this->genrate_cab_certificate_who_passed_cab_day($applicant, $task_details);

        $pdfFilePath = (FCPATH . APPLICANT_ATTACHMENT_UPLOAD_PATH . '/' . $applicant['applicant_id'] . '/' . $filename);

        $filesize = filesize($pdfFilePath);

        $member_folder = S3_APPLICANT_ATTACHMENT_UPLOAD_PATH;
        $directory_name = $applicant['applicant_id'];
        $path_parts = pathinfo($filename);
        $filename_ext =  $path_parts['extension'];
        $filename_wo_ext =  $path_parts['filename'];
        $folder_key = $member_folder . $directory_name .'/'. $filename_wo_ext . '_'. time() . '.'. $filename_ext;


        // load amazon s3 library
        $this->load->library('AmazonS3');
        $this->load->library('S3Loges');

        /**
         * set dynamic values
         * $tmp_name should be - Uploaded file tmp storage path
         * $folder_key shoulb - Saving path with file name into s3 bucket
         *      - you can add a folder like `folder/folder/filename.ext`
         */
        $this->amazons3->setSourceFile($pdfFilePath);
        $this->amazons3->setFolderKey($folder_key);
        // s3 log
        $config['file_name'] = $filename;
        $config['raw_name'] = $filename;
        $config['file_size'] = $filesize;
        $config['file_path'] = $pdfFilePath;
        $config['file_ext'] = $filename_ext;

        $this->s3loges->setModuleId(1);
        $this->s3loges->setCreatedAt(DATE_TIME);
        $this->s3loges->setCreatedBy($adminId);
        $this->s3loges->setTitle('File Transfer Initiated for Add attachment against applicant '.$directory_name);
        $this->s3loges->setLogType('init');
        $this->s3loges->setDescription(json_encode($config));
        $this->s3loges->createS3Log();

        $amazons3_updload = $this->amazons3->uploadDocument();

        if ($amazons3_updload['status'] == 200) {
            // success
            $aws_uploaded_flag = 1;
            $aws_object_uri = '';
            $aws_file_version_id = '';
            if (isset($amazons3_updload['data']) == true && empty($amazons3_updload['data']) == false) {
                $data = $amazons3_updload['data'];
                $aws_object_uri = $data['ObjectURL'] ?? '';

                if ($aws_object_uri == '' && isset($data['@metadata']) == true && isset($data['@metadata']['effectiveUri']) == true) {
                    $aws_object_uri = $data['@metadata']['effectiveUri'] ?? '';
                }

                if ($aws_file_version_id == '' && isset($data['VersionId']) == TRUE) {
                  $aws_file_version_id = $data['VersionId'] ?? '';
                }
            }
            $aws_response = $amazons3_updload['data'];
            $this->s3loges->setTitle('Applicant '.$directory_name.' - '.$filename . ' - S3 File transfer Completed');
            $this->s3loges->setLogType('success');
            $this->s3loges->setDescription(json_encode($aws_response));
            $this->s3loges->createS3Log();

            //Delete file in App server if Appser upload is no
            if(getenv('IS_APPSERVER_UPLOAD') != 'yes') {
                unlink($pdfFilePath);
            }
        } else {
            // failed
            $this->s3loges->setTitle('Applicant '.$directory_name.' - '.$filename . ' - S3 File transfer Completed with error!');
            $this->s3loges->setLogType('failure');
            $this->s3loges->setDescription(json_encode($aws_response));
            $this->s3loges->createS3Log();

            $aws_file_version_id = '';
            $aws_object_uri = '';
            $aws_response = $amazons3_updload['data'];
            $aws_uploaded_flag = 0;
        }

        // Set default status as valid
        $valid_status = 1;
        //attachment insert data
        $app_att['application_id'] = $application_id;
        $app_att['applicant_id'] = $applicant_id;
        $app_att['draft_contract_type'] = $doc_att['is_main_stage_label'] = $app_att['stage'] =
        $doc_att['uploaded_by_applicant'] = $doc_att['member_move_archive'] = 0;
        $app_att['related_to'] = 1;
        $app_att['doc_type_id'] = (isset($cab_certificate_category)) ? $cab_certificate_category->id : 0;
        $app_att['document_status'] = $valid_status;
        $app_att['archive'] = 0;
        $app_att['entity_id'] = $applicant_id;
        $app_att['entity_type'] = 1;
        $app_att['created_at'] = DATE_TIME;
        $app_att['created_by'] = $adminId;
        $app_att['updated_at'] = DATE_TIME;

        // insert data
        $this->db->insert(TBL_PREFIX . 'document_attachment', $app_att);

        //Get the last inserted id
        $insert_id = $this->db->insert_id();

        $doc_prop_att['doc_id'] = $insert_id;
        $doc_prop_att['file_name'] = 'CAB Certificate';
        $doc_prop_att['file_type'] = 'application/pdf';
        $doc_prop_att['file_ext'] = 'pdf';
        $doc_prop_att['file_size'] = $filesize;
        $doc_prop_att['raw_name'] = $filename;
        $doc_prop_att['aws_uploaded_flag'] = $aws_uploaded_flag;
        $doc_prop_att['aws_response'] = json_encode($aws_response);
        $doc_prop_att['aws_object_uri'] = $aws_object_uri;
        $doc_prop_att['aws_file_version_id'] = $aws_file_version_id;
        $doc_prop_att['file_path'] = $folder_key;        
        $doc_prop_att['archive'] = 0;
        $doc_prop_att['created_at'] = DATE_TIME;
        $doc_prop_att['created_by'] = $adminId;
        $doc_prop_att['updated_at'] = DATE_TIME;

        $this->db->insert(TBL_PREFIX . 'document_attachment_property', $doc_prop_att, true);

        return ['status' => true, 'msg' => 'Cab certificate generated successfully.'];
    }
}
