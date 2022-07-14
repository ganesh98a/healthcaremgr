<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Class : MemberDocument_model
 * Uses : for handle query operation of member
 *
 * Note: this model also used for member app portal
 */
class MemberDocument_model extends CI_Model {

    function __construct() {

        parent::__construct();
    }

    /*
     * It is used to get the member document list
     *
     * Operation:
     *  - searching
     *  - filter
     *  - sorting
     *
     * Return type Array
     */
    public function get_member_document_list($reqData) {
        // Get subqueries
        $document_name_sub_query = $this->get_document_name_sub_query('tmd');
        $member_name_sub_query = $this->get_member_sub_query('tmd');
        $created_by_sub_query = $this->get_created_updated_by_sub_query('created_by','tmd');
        $updated_by_sub_query = $this->get_created_updated_by_sub_query('updated_by','tmd');

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $member_id = $reqData->member_id ?? '';
        $orderBy = '';
        $direction = '';

        // Searching column
        $src_columns = array('file_name', 'file_type', 'status', 'reference_number', );
        if (isset($filter->search) && $filter->search != '') {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (!empty($filter->filter_by) && $filter->filter_by != 'all' && $filter->filter_by != $column_search) {
                    continue;
                }
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                } else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        // Sort by id
        $available_column = ["id", "document_id", "document_status", "member_id", "archive", "issue_date", "expiry_date", "reference_number", 
                             "created_by", "created_at", "updated_by", "updated_at", "file_name","file_type", "file_size",
                             "file_ext",  "attached_on",  "updated_on", "converted_name", "file_path"
                            ];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'tmd.id';
            $direction = 'DESC';
        }

        // Filter by status
        if (!empty($filter->filter_status)) {
            if ($filter->filter_status === "submitted") {
                $this->db->where('tmd.document_status', 0);
            } else if ($filter->filter_status === "valid") {
                $this->db->where('tmd.document_status', 1);
            } else if ($filter->filter_status === "invalid") {
                $this->db->where('tmd.document_status', 2);
            } else if ($filter->filter_status === "expired") {
                $this->db->where('tmd.document_status', 3);
            } else if ($filter->filter_status === "draft") {
                $this->db->where('tmd.document_status', 4);
            }
        }

        $base_url = base_url('mediaShow/m');

        $select_column = ["tmd.id", "tmd.id as document_id", "tmd.document_status", "tmd.member_id", "tmd.archive", "tmd.issue_date", "tmd.expiry_date", "tmd.reference_number", "tmd.created_by", "tmd.created_at", "tmd.updated_by", "tmd.updated_at", "tmda.file_name",
            "tmda.file_type",
            "tmda.file_size",
            "CONCAT('.',tmda.file_ext) AS file_ext",
            "tmda.created_at AS attached_on",
            "tmd.updated_at AS updated_on",
            "'".$base_url."' AS file_base_path",
            "TO_BASE64(tmda.file_name) AS converted_name",
            "'name=' AS uri_param_1",
            "CONCAT('".$base_url."', '/', tmd.id, '/', REPLACE(TO_BASE64(tmda.file_path), '=', '%3D%3D'), '?download_as=', REPLACE(tmda.file_name, ' ', ''), '&s3=true') AS file_path",
        ];
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(" . $document_name_sub_query . ") as document");
        $this->db->select("(" . $member_name_sub_query . ") as member");
        $this->db->select("(" . $created_by_sub_query . ") as created_by");
        $this->db->select("(" . $updated_by_sub_query . ") as updated_by");
        $this->db->select("(CASE
            WHEN tmd.document_status = 0 THEN 'Submitted'
            WHEN tmd.document_status = 1 THEN 'Valid'
            WHEN tmd.document_status = 2 THEN 'InValid'
            WHEN tmd.document_status = 3 THEN 'Expired'
			Else '' end
		) as status");
        $this->db->from('tbl_member_documents as tmd');
        $this->db->join('tbl_member_documents_attachment as tmda', 'tmda.doc_id = tmd.id AND tmda.archive = 0', 'left');
        $this->db->where('tmd.member_id', $member_id);
        $this->db->where('tmd.document_status !=', 4);
        $this->db->where('tmd.archive', 0);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // Get total rows inserted count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count')->row()->count;
        // If limit 0 return empty
        if ($limit == 0) {
            return array('count' => $dt_filtered_total, 'data' => array(), 'status' => false, 'error' => 'Pagination divide by zero');
        }

        // Get the count per page and total page
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        // Get the query result
        $result = $query->result();

        // Get total rows inserted count
        $document_row = $this->db->query('SELECT COUNT(*) as count from tbl_member_documents where member_id = '.$member_id.' AND archive = 0 AND document_status NOT IN (4)')->row_array();
        $document_count = intVal($document_row['count']);

        return array('count' => $dt_filtered_total, 'document_count' => $document_count, 'data' => $result, 'status' => true, 'msg' => 'Fetch member document list successfully');
    }

    /*
     * it is used for making sub query of member name
     * return type sql
     */
    private function get_member_sub_query($tbl_alais) {
        $this->db->select("tm.fullname");
        $this->db->from(TBL_PREFIX . 'member as tm');
        $this->db->where("tm.id = ".$tbl_alais.".member_id", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for making sub query of document type name
     * return type sql
     */
    private function get_document_name_sub_query($tbl_alais) {
        $this->db->select("td.title");
        $this->db->from(TBL_PREFIX . 'document_type as td');
        $this->db->where("td.id = ".$tbl_alais.".doc_type_id", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for making sub query of document type name
     * return type sql
     */
    private function get_document_detail_sub_query($tbl_alais) {
        $this->db->select(["td.title AS document", "td.issue_date_mandatory", "td.expire_date_mandatory", "td.reference_number_mandatory"]);
        $this->db->from(TBL_PREFIX . 'document_type as td');
        $this->db->where("td.id = ".$tbl_alais.".doc_type_id", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for making sub query created by (who creator|updated of member)
     * return type sql
     */
    private function get_created_updated_by_sub_query($column_by, $tbl_alais) {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = ".$tbl_alais.".".$column_by, null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for get document name on base of @param $documentName
     *
     * @params
     * $documentName search parameter
     *
     * return type array
     *
     */
    public function get_all_document_name_search($documentName = '', $user = '') {
        $this->db->like('label', $documentName);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
        $this->db->select(["td.title as label", 'td.id as value', 'td.issue_date_mandatory', 'td.expire_date_mandatory', 'td.reference_number_mandatory']);
        $this->db->from(TBL_PREFIX . 'document_type as td');
        $this->db->join('tbl_document_type_related as tdt', 'td.id = tdt.doc_type_id and tdt.archive=0 ', 'left');
        $this->db->where(['td.archive' => 0]);
        $this->db->where(['td.active' => 1]);
        if ($user == 'applicant') {
            $this->db->where_in('tdt.related_to',[1]);
        } else {
            $this->db->where_in('tdt.related_to',[1,2]);
            $this->db->group_by('td.id');
        }
        
        $this->db->having($queryHaving);
        $sql = $this->db->get_compiled_select();

        $query = $this->db->query($sql);
        return $query->result();
    }

    /*
     * To create member document & attachment
     *
     * @params {array} $data
     * @params {int} $adminId
     * @param {bool} $testCase - denotate call from testCase or not.
     *
     * return type documentId
     */
    function create_member_document($data, $adminId, $testCase = false) {
        /**
         * Insert document
         */
        $insData = [
            'member_id' => $data["member_id"],
            'doc_type_id' => $data["doc_type_id"],
            'document_status' => isset($data["status"]) && $data["status"] != '' ? $data["status"] : '',
            'issue_date' => isset($data["issue_date"]) && $data["issue_date"] !='' ? date('Y-m-d', strtotime($data["issue_date"])) : NULL,
            'expiry_date' => isset($data["expiry_date"]) && $data["expiry_date"] != '' ? date('Y-m-d', strtotime($data["expiry_date"])) : NULL,
            'reference_number' =>isset($data["reference_number"]) && $data["reference_number"] != '' ? $data["reference_number"] : '',
            'created_portal' =>isset($data["created_portal"]) && $data["created_portal"] != '' ? $data["created_portal"] : 1,
            'created_by' => $adminId,
            'created_at' => DATE_TIME,
        ];
        // Insert the data using basic model function
        $documentId = $this->basic_model->insert_records('member_documents', $insData);

        if (!$documentId) {
            return ['status' => false, 'error' => "Document is not created. something went wrong"];
        }

        // upload attachment to permanaent folder
        $documentAttachment = $this->s3_upload_content_attachement($documentId, $adminId, $testCase);

        if (!$documentAttachment) {
            return ['status' => false, 'error' => "Document Attachment is not created. something went wrong"];
        }

        return ['status' => true, 'msg' => "Document created successfully", 'document_id' => $documentId];
    }

    /*
     * Save the uploaded attachment to permanent folder
     * @param {int} $document_id
     */
    public function s3_upload_content_attachement($document_id, $admin_id, $testCase) {
        $error = array();
        $attachments = array();
        if (!empty($_FILES)) {
            // Initialize variables
            $attachments = array();
            $member_folder = S3_MEMBER_DOCUMENT_FILE_PATH;
            $input_name = 'attachments';
            $directory_name = $document_id;
            $allowed_types = 'jpg|jpeg|png|xlx|xls|doc|docx|pdf|csv|odt|rtf';
            $files = $_FILES;
            $cpt = count($_FILES[$input_name]['name']);

            // load amazon s3 library
            $this->load->library('AmazonS3');

            // for loop
            for ($i = 0; $i < $cpt; $i++) {
                $file_name = $files[$input_name]['name'][$i];
                $file_type = $files[$input_name]['type'][$i];
                $tmp_name = $files[$input_name]['tmp_name'][$i];
                $file_size = $files[$input_name]['size'][$i];
                $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $folder_key = $member_folder . $directory_name .'/'. $file_name;

                /**
                 * set dynamic values
                 * $tmp_name should be - Uploaded file tmp storage path
                 * $folder_key shoulb - Saving path with file name into s3 bucket
                 *      - you can add a folder like `folder/folder/filename.ext`
                 */
                $this->amazons3->setSourceFile($tmp_name);
                $this->amazons3->setFolderKey($folder_key);

                // Upload file if testCase true use mock object
                if ($testCase == true) {
                    $amazons3_updload = $this->amazons3->testUploadDocumentMock();
                } else {
                    $amazons3_updload = $this->amazons3->uploadDocument();
                }

                if ($amazons3_updload['status'] == 200) {
                    // success
                    $aws_uploaded_flag = 1;
                    $aws_object_uri = '';
                    if (isset($amazons3_updload['data']) == true && empty($amazons3_updload['data']) == false) {
                        $data = $amazons3_updload['data'];
                        $aws_object_uri = $data['ObjectURL'] ?? '';

                        if ($aws_object_uri == '' && isset($data['@metadata']) == true && isset($data['@metadata']['effectiveUri']) == true) {
                            $aws_object_uri = $data['@metadata']['effectiveUri'] ?? '';
                        }
                    }
                    $aws_response = $amazons3_updload['data'];
                } else {
                    // failed
                    $aws_object_uri = '';
                    $aws_response = $amazons3_updload['data'];
                    $aws_uploaded_flag = 0;
                }

                // Insert Data
                $aws_response = json_encode($aws_response);
                $attachments[$i]['file_name'] = $file_name;
                $attachments[$i]['raw_name'] = $file_name;
                $attachments[$i]['file_path'] = $folder_key;
                $attachments[$i]['file_type'] = $file_type;
                $attachments[$i]['file_ext'] = $file_ext;
                $attachments[$i]['file_size'] = $file_size;
                $attachments[$i]['created_at'] = DATE_TIME;
                $attachments[$i]['created_by'] = $admin_id;
                $attachments[$i]['doc_id'] = $document_id;
                $attachments[$i]['aws_object_uri'] = $aws_object_uri;
                $attachments[$i]['aws_response'] = $aws_response;
                $attachments[$i]['aws_uploaded_flag'] = $aws_uploaded_flag;
            }

            if (!empty($attachments)) {
                $this->basic_model->insert_records('member_documents_attachment', $attachments, true);
            }
        }

        return $attachments;
    }

    /*
     * Save the uploaded attachment to permanent folder
     * @param {int} $document_id
     */
    public function upload_content_attachement($document_id, $admin_id) {
        $error = array();
        $response = array();
        if (!empty($_FILES)) {
            // file path create if not exist
            $fileParDir = FCPATH . MEMBER_DOCUMENT_FILE_PATH;
            if (!is_dir($fileParDir)) {
                mkdir($fileParDir, 0755);
            }
            $config['upload_path'] = MEMBER_DOCUMENT_FILE_PATH;
            $config['input_name'] = 'attachments';
            $config['directory_name'] = $document_id;
            $config['allowed_types'] = 'jpg|jpeg|png|xlx|xls|doc|docx|pdf|csv|odt|rtf';

            $response = do_muliple_upload($config);

            $attachments = array();

            if (!empty($response)) {
                foreach ($response as $key => $val) {
                    if (isset($val['error'])) {
                        $error[]['file_error'] = $val['error'];
                    } else {
                        $attachments[$key]['file_name'] = $val['upload_data']['file_name'];
                        $attachments[$key]['raw_name'] = $val['upload_data']['file_name'];
                        $attachments[$key]['file_path'] = $val['upload_data']['file_path'];
                        $attachments[$key]['file_type'] = $val['upload_data']['file_type'];
                        $attachments[$key]['file_ext'] = $val['upload_data']['file_ext'];
                        $attachments[$key]['file_size'] = $val['upload_data']['file_size'];
                        $attachments[$key]['created_at'] = DATE_TIME;
                        $attachments[$key]['created_by'] = $admin_id;
                        $attachments[$key]['doc_id'] = $document_id;
                    }
                }

                if (!empty($attachments)) {
                    $this->basic_model->insert_records('member_documents_attachment', $attachments, true);
                }
            }
        }

        return $response;
    }

    /*
     *
     * @params {object} $reqData
     *
     * Return type Array - $result
     */
    public function get_member_doucment_data_by_id($reqData) {
        if (isset($reqData) && isset($reqData->document_id)) {
            // Get subquery of cerated & updated by
            $member_name_sub_query = $this->get_member_sub_query('tmd');
            $created_by_sub_query = $this->get_created_updated_by_sub_query('created_by','tmd');
            $updated_by_sub_query = $this->get_created_updated_by_sub_query('updated_by','tmd');

            $document_id = $reqData->document_id;
            $base_url = base_url('mediaShowView/MA');

            $select_column = ["tmd.id", "tmd.id as document_id", "tmd.document_status", "tmd.member_id", "tmd.archive", "tmd.issue_date", "tmd.expiry_date", "tmd.reference_number", "tmd.created_by", "tmd.created_at", "tmd.updated_by", "tmd.updated_at", "tmda.file_name",
                "tmda.file_type",
                "tmda.file_size",
                "tmda.file_ext",
                "tmda.created_at AS attached_on",
                "tmd.updated_at AS updated_on",
                "'".$base_url."' AS file_base_path",
                "TO_BASE64(tmda.file_name) AS converted_name",
                "'name=' AS uri_param_1",
                // "CONCAT('".$base_url."', '/', tmd.id, '?filename', '=', TO_BASE64(tmda.file_name)) AS file_path",
                "tmda.aws_object_uri AS file_path",
                "tmd.doc_type_id",
                "td.title AS document",
                "td.issue_date_mandatory",
                "td.expire_date_mandatory",
                "td.reference_number_mandatory",
            ];
            // Query
            $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
            $this->db->select("(" . $member_name_sub_query . ") as member");
            $this->db->select("(" . $created_by_sub_query . ") as created_by");
            $this->db->select("(" . $updated_by_sub_query . ") as updated_by");
            $this->db->from('tbl_member_documents as tmd');
            $this->db->join('tbl_member_documents_attachment as tmda', 'tmda.doc_id = tmd.id AND tmda.archive = 0', 'left');
            $this->db->join('tbl_document_type as td', 'td.id = tmd.doc_type_id', 'left');
            $this->db->where('tmd.id', $document_id);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->num_rows() > 0 ? $query->result_array() : [];
            $result = $query->num_rows() > 0 ? $result[0] : [];

            return [ "status" => true, 'data' => $result, 'msg' => 'Fetch Document detail successfully' ];
        } else {
            return [ "status" => false, 'error' => 'Document Id is missing'];
        }
    }

    /*
     * To edit member doucment
     *
     * @params {array} $data
     * @params {int} $adminId
     *
     * return type document_id
     */
    public function edit_member_document($data, $adminId) {
        // Check the document data
        if ($data && $data['document_id']) {
            /**
             * Update document
             */
            $updateData = [
                'member_id' => $data["member_id"],
                'doc_type_id' => $data["doc_type_id"],
                'document_status' => $data["status"],
                'issue_date' => $data["issue_date"] != '' ? date('Y-m-d', strtotime($data["issue_date"])) : NULL,
                'expiry_date' => $data["expiry_date"] != ''  ? date('Y-m-d', strtotime($data["expiry_date"])) : NULL,
                'reference_number' => $data["reference_number"],
                'updated_by' => $adminId,
                'updated_at' => DATE_TIME,
            ];

            // Insert the data using basic model function
            $where = array('id' => $data['document_id']);
            $documentId = $this->basic_model->update_records('member_documents', $updateData, $where);

            if (!$documentId) {
                return ['status' => false, 'error' => "Document is not updated. something went wrong"];
            }

            // if files not empty update attachment
            if (!empty($_FILES)) {
                $this->archive_document_attachment($adminId, $data['document_id']);

                // upload attachment to permanaent folder
                $documentAttachment = $this->s3_upload_content_attachement($data['document_id'], $adminId);

                if (!$documentAttachment) {
                    return ['status' => false, 'error' => "Document Attachment is not created. something went wrong"];
                }
            }

            return ['status' => true, 'msg' => "Document updated successfully", 'document_id' => $documentId];

        } else {
            return [ "status" => false, 'error' => 'Document Id is missing'];
        }
    }

    /**
     * Archve document using document id
     * @param {int} $adminId
     * @param {int} $id
     */
    public function archive_document($adminId, $id) {

        // Check document is exist. Using id
        $where = array('id' => $id);
        $colown = array('id');
        $check_document = $this->basic_model->get_record_where('member_documents', $colown, $where);

        if (empty($check_document)) {
            $response = ['status' => false, 'error' => "Document does not exist anymore."];
            echo json_encode($response);
            exit();
        }

        // Get document atachment is exist. if yes get the recent attachments id
        $where = array('doc_id' => $id, "archive" => 0);
        $colown = array('*');
        $this->db->select($colown);
        $this->db->from(TBL_PREFIX.'member_documents_attachment', $colown, $where);
        $this->db->where($where);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $get_attachment = $query->result();

        // update member documents
        $upd_data["updated_at"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result= [ "id" => $id];
        $result = $this->basic_model->update_records("member_documents", $upd_data, ["id" => $id]);

        // Check document atachment is exist. if yes get the recent attachments id
        $where = array('doc_id' => $id, "archive" => 0);
        $colown = array('group_concat(id) AS id');
        $this->db->select($colown);
        $this->db->from(TBL_PREFIX.'member_documents_attachment', $colown, $where);
        $this->db->where($where);
        $this->db->group_by('doc_id');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $check_attachment = $query->row_array();

        $ids = 0;
        if (isset($check_attachment)) {
            $ids = $check_attachment['id'];
        }

        if ($ids != 0) {
            // update member documents attachment
            $upd_data["updated_at"] = DATE_TIME;
            $upd_data["updated_by"] = $adminId;
            $upd_data["archive"] = 1;
            $document_row = $this->db->query('UPDATE tbl_member_documents_attachment SET updated_at = "'.DATE_TIME.'", updated_by = '.$adminId.', archive = 1 where id IN ('.$ids.')');
        }

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
                }
            }
        }

        return $result;
    }

    /**
     * Archve document attachement using document id
     * @param {int} $adminId
     * @param {int} $id
     */
    public function archive_document_attachment($adminId, $id) {

        // Check document is exist. Using id
        $where = array('id' => $id);
        $colown = array('id');
        $check_document = $this->basic_model->get_record_where('member_documents', $colown, $where);

        if (empty($check_document)) {
            $response = ['status' => false, 'error' => "Document does not exist anymore."];
            echo json_encode($response);
            exit();
        }

        // Check document atachment is exist. if yes get the recent attachments id
        $where = array('doc_id' => $id, "archive" => 0);
        $colown = array('group_concat(id) AS id');
        $this->db->select($colown);
        $this->db->from(TBL_PREFIX.'member_documents_attachment', $colown, $where);
        $this->db->where($where);
        $this->db->group_by('doc_id');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $check_attachment = $query->row_array();

        $ids = 0;
        if (isset($check_attachment)) {
            $ids = $check_attachment['id'];
        }

        if ($ids != 0) {
            // update member documents attachment
            $upd_data["updated_at"] = DATE_TIME;
            $upd_data["updated_by"] = $adminId;
            $upd_data["archive"] = 1;
            $this->db->query('UPDATE tbl_member_documents_attachment SET updated_at = "'.DATE_TIME.'", updated_by = '.$adminId.', archive = 1 where id IN ('.$ids.')');
        }

        return $ids;
    }

    /*
     * It is used to get the member document list only submitted and draft
     * - for member app portal
     * Operation:
     *  - searching
     *  - filter
     *  - sorting
     *
     * Return type Array
     */
    public function get_member_document_list_for_portal($reqData) {
        // Get subqueries
        $document_name_sub_query = $this->get_document_name_sub_query('tmd');
        $member_name_sub_query = $this->get_member_sub_query('tmd');
        $created_by_sub_query = $this->get_created_updated_by_sub_query('created_by','tmd');
        $updated_by_sub_query = $this->get_created_updated_by_sub_query('updated_by','tmd');

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $member_id = $reqData->member_id ?? '';
        $orderBy = '';
        $direction = '';

        // Searching column
        $src_columns = array('file_name', 'file_type', 'status', 'reference_number', );
        if (isset($filter->search) && $filter->search != '') {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (!empty($filter->filter_by) && $filter->filter_by != 'all' && $filter->filter_by != $column_search) {
                    continue;
                }
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                } else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        // Sort by id
        $available_column = ["id", "document_id", "document_status", "member_id", "archive", "issue_date", "expiry_date", "reference_number", 
                             "created_by", "created_at", "updated_by", "updated_at", "file_name",
                             "file_type", "file_size", "file_ext", "attached_on", "updated_on",  "file_path"
                            ];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'tmd.id';
            $direction = 'DESC';
        }

        // Filter by status
        if (!empty($filter->filter_status)) {
            if ($filter->filter_status === "submitted") {
                $this->db->where('tmd.document_status', 0);
            } else if ($filter->filter_status === "valid") {
                $this->db->where('tmd.document_status', 1);
            } else if ($filter->filter_status === "invalid") {
                $this->db->where('tmd.document_status', 2);
            } else if ($filter->filter_status === "expired") {
                $this->db->where('tmd.document_status', 3);
            } else if ($filter->filter_status === "draft") {
                $this->db->where('tmd.document_status', 4);
            }
        }

        $base_url = base_url('mediaShow/m');

        $select_column = ["tmd.id", "tmd.id as document_id", "tmd.document_status", "tmd.member_id", "tmd.archive", "tmd.issue_date", "tmd.expiry_date", "tmd.reference_number", "tmd.created_by", "tmd.created_at", "tmd.updated_by", "tmd.updated_at", "tmda.file_name",
            "tmda.file_type",
            "tmda.file_size",
            "CONCAT('.',tmda.file_ext) AS file_ext",
            "tmda.created_at AS attached_on",
            "tmd.updated_at AS updated_on",
            "'".$base_url."' AS file_base_path",
            "TO_BASE64(tmda.file_name) AS converted_name",
            "'name=' AS uri_param_1",
            "CONCAT('".$base_url."', '/', tmd.id, '/', REPLACE(TO_BASE64(tmda.file_path), '=', '%3D%3D'), '?download_as=', REPLACE(tmda.file_name, ' ', ''), '&s3=true') AS file_path",
        ];
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(" . $document_name_sub_query . ") as document");
        $this->db->select("(" . $member_name_sub_query . ") as member");
        $this->db->select("(" . $created_by_sub_query . ") as created_by");
        $this->db->select("(" . $updated_by_sub_query . ") as updated_by");
        $this->db->select("(CASE
            WHEN tmd.document_status = 0 THEN 'Submitted'
            WHEN tmd.document_status = 1 THEN 'Valid'
            WHEN tmd.document_status = 2 THEN 'InValid'
            WHEN tmd.document_status = 3 THEN 'Expired'
            WHEN tmd.document_status = 4 THEN 'Draft'
			Else '' end
		) as status");
        $this->db->from('tbl_member_documents as tmd');
        $this->db->join('tbl_member_documents_attachment as tmda', 'tmda.doc_id = tmd.id AND tmda.archive = 0', 'left');
        $this->db->where('tmd.member_id', $member_id);
        $this->db->where('tmd.archive', 0);
        $this->db->where('tmd.document_status IN (0,4)');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // Get total rows inserted count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count')->row()->count;
        // If limit 0 return empty
        if ($limit == 0) {
            return array('count' => $dt_filtered_total, 'data' => array(), 'status' => false, 'error' => 'Pagination divide by zero');
        }

        // Get the count per page and total page
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        // Get the query result
        $result = $query->result();

        // Get total rows inserted count
        $document_row = $this->db->query('SELECT COUNT(*) as count from tbl_member_documents where member_id = '.$member_id.' AND archive = 0 AND document_status IN (0,4)')->row_array();
        $document_count = intVal($document_row['count']);

        return array('count' => $dt_filtered_total, 'document_count' => $document_count, 'data' => $result, 'status' => true, 'msg' => 'Fetch member document list successfully');
    }
}


