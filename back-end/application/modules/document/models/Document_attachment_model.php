<?php

/** Files holds the document attachment related operation */
defined('BASEPATH') or exit('No direct script access allowed');

class Document_attachment_model extends CI_Model
{

    public function __construct()
    {
        // Call the CI_Model constructor
        parent::__construct();
        require_once APPPATH . 'Classes/document/DocumentAttachment.php';
    }

    /*
     * It is used to get the document list only submitted and draft
     * - for member app portal
     * Operation:
     *  - searching
     *  - filter
     *  - sorting
     *
     * Return type Array
     */
    public function get_document_list_for_portal($reqData, $is_portal = false)
    {
        // Get subqueries
        $document_name_sub_query = $this->get_document_name_sub_query('tda');
        $member_name_sub_query = $this->get_member_sub_query('tda', 'entity_id');
        $applicant_name_sub_query = $this->get_applicant_sub_query('tda', 'entity_id');
        $member_created_by_sub_query = $this->get_created_updated_by_sub_query('created_by', 'tda');
        $member_updated_by_sub_query = $this->get_created_updated_by_sub_query('updated_by', 'tda');
        $applicant_updated_by_sub_query = $this->get_applicant_sub_query('tda', 'updated_by');
        $applicant_created_by_sub_query = $this->get_applicant_sub_query('tda', 'created_by');

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $member_id = $reqData->member_id ?? '';
        $applicant_id = $reqData->applicant_id ?? '';
        $orderBy = '';
        $direction = '';

        // where document type is
        $where_entity_type = [];
        $where_entity_id = [];

        $docAttachObj = new DocumentAttachment();

        // Get constant
        $relatedToRecruitment = $docAttachObj->getConstant('RELATED_TO_RECRUITMENT');
        $relatedToMember = $docAttachObj->getConstant('RELATED_TO_MEMBER');
        $entityTypeApplicant = $docAttachObj->getConstant('ENTITY_TYPE_APPLICANT');
        $entityTypeMember = $docAttachObj->getConstant('ENTITY_TYPE_MEMBER');
        $draftStatus = $docAttachObj->getConstant('DOCUMENT_STATUS_DRAFT');

        if ($member_id != '') {
            $where_entity_type[] = $entityTypeApplicant;
            $where_entity_id[] = $member_id;
        }

        if ($applicant_id != '') {
            $where_entity_type[] = $entityTypeApplicant;
            $where_entity_id[] = $applicant_id;
        }

        // Searching column
        $src_columns = array('file_name', 'file_type', 'status', 'reference_number', 'DATE(issue_date)', 'DATE(expiry_date)', 'DATE(attached_on)', 'DATE(updated_on)');
        if (isset($filter->search) && $filter->search != '') {
            $filter->search = strVal($filter->search);
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (!empty($filter->filter_by) && $filter->filter_by != 'all' && $filter->filter_by != $column_search) {
                    continue;
                }
                if (
                    $column_search == 'DATE(issue_date)' || $column_search == 'DATE(expiry_date)'
                    || $column_search == 'DATE(attached_on)' || $column_search == 'DATE(updated_on)'
                ) {
                    $formated_date = date('Y-m-d', strtotime(str_replace('/', '-', $filter->search)));
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $formated_date);
                    } else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $formated_date);
                    }
                } else {
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $filter->search);
                    } else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $filter->search);
                    }
                }
            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
        
        // Sort by id
        $available_column = [
            "id", "doc_type_id", "entity_id", "entity_type", "document_id", "document_status", "member_id", "archive", "issue_date", "expiry_date", 
            "reference_number", "created_by", "created_at", "updated_by", "updated_at", "file_name", "draft_contract_type", "applicant_id",
            "file_type", "file_size", "raw_name", "file_ext","attached_on","updated_on", "aws_uploaded_flag", "converted_name",
            "uri_param_1", "file_path", "system_gen_flag", "signed_status"
        ];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'tda.id';
            $direction = 'DESC';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        // Filter by status
        if (!empty($filter->filter_status)) {
            if ($filter->filter_status === "submitted") {
                $this->db->where('tda.document_status', 0);
            } else if ($filter->filter_status === "valid") {
                $this->db->where('tda.document_status', 1);
            } else if ($filter->filter_status === "invalid") {
                $this->db->where('tda.document_status', 2);
            } else if ($filter->filter_status === "expired") {
                $this->db->where('tda.document_status', 3);
            } else if ($filter->filter_status === "draft") {
                $this->db->where('tda.document_status', 4);
            }
        }

        $base_url = base_url('mediaShow/m');
        $query_file_path = ($is_portal) ? "CONCAT( tda.id, '/', REPLACE(TO_BASE64(tdap.file_path), '=', '%3D%3D'), '?download_as=', REPLACE(tdap.raw_name, ' ', ''), '&s3=true') AS file_path" :
        
        "CONCAT('mediaShow/m/', tda.id, '/', REPLACE(TO_BASE64(tdap.file_path), '=', '%3D%3D'), '?download_as=', REPLACE(tdap.raw_name, ' ', ''), '&s3=true') AS file_path";

        $select_column = [
            "tda.id", "tda.doc_type_id", "tda.entity_id", "tda.entity_type", "tda.id as document_id", "tda.document_status", "tda.member_id", "tda.archive", "tda.issue_date", "tda.expiry_date", "tda.reference_number", "tda.created_by", "tda.created_at", "tda.updated_by", "tda.updated_at", "tdap.file_name", "tda.draft_contract_type", "tda.applicant_id",
            "tdap.file_type",
            "tdap.file_size",
            "tdap.raw_name",
            "CONCAT('.',tdap.file_ext) AS file_ext",
            "tdap.created_at AS attached_on",
            "tda.updated_at AS updated_on",
            "tdap.aws_uploaded_flag",
            "'" . $base_url . "' AS file_base_path",
            "TO_BASE64(tdap.file_name) AS converted_name",
            "'name=' AS uri_param_1",
            $query_file_path,
            "tdt.system_gen_flag",
            "tdap.signed_status",
        ];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        // Query
        $this->db->select("(CASE
          WHEN tda.draft_contract_type = 1 THEN CONCAT('Draft Contract_', ra.firstname,' ', ra.lastname)
          WHEN tda.draft_contract_type = 2 THEN CONCAT('Employment Contract_', ra.firstname,' ', ra.lastname)
                else 
                    CASE
                        WHEN tdt.title IS NOT NULL THEN
                            CONCAT(tdt.title,'_',, ra.firstname,' ', ra.lastname) 
                        ELSE 
                            CONCAT(ra.firstname,' ', ra.lastname) 
                        END
                end) as download_as
        ");

        $this->db->select("
          CASE
            WHEN tda.entity_type = 1 THEN
            (
              CASE
              WHEN tda.draft_contract_type = 1 THEN CONCAT('Draft Contract_', ra.firstname,' ', ra.lastname)
              WHEN tda.draft_contract_type = 2 THEN CONCAT('Employment Contract_', ra.firstname,' ', ra.lastname)
              ELSE 
                  CASE
                      WHEN tdt.title IS NOT NULL THEN
                          CONCAT(tdt.title,'_',, ra.firstname,' ', ra.lastname) 
                      ELSE 
                          CONCAT(ra.firstname,' ', ra.lastname) 
                      END
              END
            )
            ELSE
              tdap.file_name 
            END
           as file_name
        ");

        $this->db->select("(" . $document_name_sub_query . ") as document");
        $this->db->select("
          (
            CASE 
              WHEN tda.entity_type = 2 THEN
                (" . $member_name_sub_query . ")
              ELSE
                (" . $applicant_name_sub_query . ")
            END
          )
         as member");
        $this->db->select("
          (
            CASE 
              WHEN tda.uploaded_by_applicant != 0 AND tda.uploaded_by_applicant IS NOT NULL THEN
                (" . $member_created_by_sub_query . ")
              ELSE
                (" . $applicant_created_by_sub_query . ")
            END
          )
         as created_by");
        $this->db->select("
          (
            CASE 
              WHEN tda.updated_by_type = 2 THEN
                (" . $member_updated_by_sub_query . ")
              ELSE
                (" . $applicant_updated_by_sub_query . ")
            END
          )
         as updated_by");
        $this->db->select("(CASE
            WHEN tda.document_status = 0 THEN 'Submitted'
            WHEN tda.document_status = 1 THEN 'Valid'
            WHEN tda.document_status = 2 THEN 'InValid'
            WHEN tda.document_status = 3 THEN 'Expired'
            WHEN tda.document_status = 4 THEN 'Draft'
          Else '' end
        ) as status");
        $this->db->from('tbl_document_attachment as tda');
        $this->db->join('tbl_document_attachment_property as tdap', 'tdap.doc_id = tda.id AND tdap.archive = 0', 'left');
        $this->db->join('tbl_document_type as tdt', 'tdt.id = tda.doc_type_id AND tdt.archive = 0', 'left');
        $this->db->join("tbl_recruitment_applicant as ra", "ra.id = tda.entity_id AND tda.entity_type=" . $entityTypeApplicant, "LEFT");
        $this->db->where('tda.archive', 0);
        $this->db->where('tdap.signed_status = (
          CASE 
          WHEN tda.draft_contract_type = 2 THEN 1
          ELSE 0
          END
        )');
        if ($is_portal == false) {
            $this->db->where('tda.document_status !=', $draftStatus);
        }
        
        if ($member_id != '' && $member_id != 'null') {
            $this->db->group_start();
            $this->db->where("tda.entity_type", $entityTypeMember);
            $this->db->where("tda.entity_id", $member_id);
            $this->db->group_end();
        }
        if ($applicant_id != '' && $applicant_id != 'null') {            
            if ($member_id != '' && $member_id != 'null') {
                $this->db->or_group_start();
                $this->db->where("tda.entity_type", $entityTypeApplicant);
            } else {
                $this->db->group_start();
                $this->db->where("tda.entity_type", $entityTypeApplicant);
            }
            $this->db->where("tda.entity_id", $applicant_id);
            $this->db->group_end();
        }
        
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

        if (!empty($result)) {
            foreach ($result as $val) {
                //Assign the file name to s3 file path
                $filename = $val->file_path;

                if (!$val->aws_uploaded_flag) {
                    $filename = $val->raw_name;
                }

                if ($val->entity_type == 1 && $val->aws_uploaded_flag == 0) {
                    if ($val->draft_contract_type == 1) {
                        $folder_url = $val->aws_uploaded_flag == 0 ? GROUP_INTERVIEW_CONTRACT_PATH : APPLICANT_ATTACHMENT_UPLOAD_PATH;
                        $val->file_path = base_url('mediaShow/rg/' . $val->applicant_id . '/' . urlencode(base64_encode($filename)));
                        $docPath = $folder_url . '/' . $filename;
                    } else if ($val->draft_contract_type == 2) {
                        if ($val->aws_uploaded_flag == 0) {
                            $folder_url = CABDAY_INTERVIEW_CONTRACT_PATH;
                            $val->file_path = base_url('mediaShow/rc/' . $val->applicant_id . '/' . urlencode(base64_encode($val->applicant_id . '/' . $filename)));
                        } else {
                            $folder_url = APPLICANT_ATTACHMENT_UPLOAD_PATH;
                            $val->file_path = base_url('mediaShow/rc/' . $val->applicant_id . '/' . urlencode(base64_encode($filename)));
                        }
                        $docPath = $folder_url . $val->applicant_id . '/' . $filename;
                    } else {
                        $val->file_path = base_url('mediaShow/r/' . $val->applicant_id . '/' . urlencode(base64_encode($filename)));
                        $docPath = APPLICANT_ATTACHMENT_UPLOAD_PATH . $val->applicant_id . '/' . $filename;
                    }

                    // Export with derived filename instead
                    // of goiing into trouble of renaming the file during upload process
                    //$download_as = $this->determine_filename_by_doc_category($val->attachment, $val->doc_category, $val->applicant_id);
                    $download_name =  $val->download_as;
                    $download_name = preg_replace('/\s+/', '_', $download_name);
                    $extension = pathinfo($val->raw_name, PATHINFO_EXTENSION);
                    $http_build_query = http_build_query(['download_as' => $download_name . '.' . $extension]);

                    if ($val->aws_uploaded_flag == 1) {
                        $http_build_query = http_build_query(['download_as' => $download_name . '.' . $extension, 's3' => 'true']);
                    }

                    $val->file_path .= '?' . $http_build_query;
                    $val->file_path = base_url('mediaShowView?tc=' . $val->id . '&rd=') . urlencode(base64_encode($val->file_path));
                }
            }
        }
        
        $this->db->select("COUNT(*) as count");
        $this->db->from("tbl_document_attachment as tda");
        $this->db->join("tbl_document_attachment_property as tdap", " tda.id = tdap.doc_id", "INNER");
        $this->db->where("tda.archive",0);
        $this->db->where("tdap.signed_status = (CASE WHEN tda.draft_contract_type = 2 THEN 1 ELSE 0 END)", NULL, false);
        if ($member_id != '' && $member_id != 'null') {
            $this->db->group_start();
            $this->db->where("tda.entity_type", $entityTypeMember);
            $this->db->where("tda.entity_id", $member_id);
            $this->db->group_end();
        }
        if ($applicant_id != '' && $applicant_id != 'null') {            
            if ($member_id != '' && $member_id != 'null') {
                $this->db->or_group_start();
                $this->db->where("tda.entity_type", $entityTypeApplicant);
            } else {
                $this->db->group_start();
                $this->db->where("tda.entity_type", $entityTypeApplicant);
            }
            $this->db->where("tda.entity_id", $applicant_id);
            $this->db->group_end();
        }
        // Get total rows inserted count
        $document_row = $this->db->get()->row_array();
        $document_count = intVal($document_row['count']);
        return array('count' => $dt_filtered_total, 'document_count' => $document_count, 'data' => $result, 'status' => true, 'msg' => 'Fetch member document list successfully');
    }

    /*
     * it is used for making sub query of document type name
     * return type sql
     */
    private function get_document_name_sub_query($tbl_alais)
    {
        $this->db->select("td.title");
        $this->db->from(TBL_PREFIX . 'document_type as td');
        $this->db->where("td.id", $tbl_alais . ".doc_type_id", false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for making sub query created by (who creator|updated of member)
     * return type sql
     */
    private function get_created_updated_by_sub_query($column_by, $tbl_alais)
    {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid", $tbl_alais . "." . $column_by, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for making sub query of member name
     * return type sql
     */
    private function get_member_sub_query($tbl_alais, $column_alias)
    {
        $this->db->select("tm.fullname");
        $this->db->from(TBL_PREFIX . 'member as tm');
        $this->db->where("tm.id", $tbl_alais . "." . $column_alias, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for making sub query of member name
     * return type sql
     */
    private function get_applicant_sub_query($tbl_alais, $column_alias)
    {
        $this->db->select("CONCAT_WS(' ', tra.firstname,tra.lastname)");
        $this->db->from(TBL_PREFIX . 'recruitment_applicant as tra');
        $this->db->where("tra.id", $tbl_alais . "." . $column_alias, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * Save Document Attachment
     */
    public function save_document_attachment($data, $userId, $testCase = false, $isUser = 'member')
    {

        $data = (object) $data;
        // pr($data);

        $docAttachObj = new DocumentAttachment();

        if (property_exists($data, 'member_id') && !empty($data->member_id)) {
            $docAttachObj->setMemberId($data->member_id);
        }

        if (property_exists($data, 'application_id') && !empty($data->application_id)) {
            $docAttachObj->setApplicationId($data->application_id);
        }

        if (property_exists($data, 'applicant_id') && !empty($data->applicant_id)) {
            $docAttachObj->setApplicantId($data->applicant_id);
        }

        $docAttachObj->setDocTypeId($data->doc_type_id);

        $docAttachObj->setArchive(0);

        if (property_exists($data, 'currentStage') && !empty($data->currentStage)) {
            $docAttachObj->setStage($data->currentStage);
        }

        if (property_exists($data, 'stageMain') && !empty($data->stageMain)) {
            $docAttachObj->setIsMainStage(($data->stageMain == 'true' ? 1 : 0));
        }

        $docAttachObj->setCreatedAt(DATE_TIME);



        $entity_id = '';
        if ($isUser == 'member') {
            if (property_exists($data, 'member_id') && !empty($data->member_id)) {
                $docAttachObj->setEntityId($data->member_id);
                $entity_id = $data->member_id;
            }
        } else if ($isUser == 'participants') {
            if (property_exists($data, 'participant_id') && !empty($data->participant_id)) {
                $docAttachObj->setEntityId($data->participant_id);
                $entity_id = $data->participant_id;
            }
        } else {
            if (property_exists($data, 'applicant_id') && !empty($data->applicant_id)) {
                $docAttachObj->setEntityId($data->applicant_id);
                $entity_id = $data->applicant_id;
            }
            $docAttachObj->setUploadedByApplicant(1);
            $userId = $entity_id;
        }

        $docAttachObj->setCreatedBy($userId);

        if (property_exists($data, 'reference_number') && !empty($data->reference_number)) {
            $docAttachObj->setReferenceNumber(isset($data->reference_number) && $data->reference_number != '' ? $data->reference_number : '');
        }

        if (property_exists($data, 'status') && !empty($data->status)) {
            $docAttachObj->setDocumentStatus($data->status);
        }

        // Get constant entity type
        if ($isUser == 'member') {
            $entityType = $docAttachObj->getConstant('ENTITY_TYPE_MEMBER');
        } else if ($isUser == 'participants') {
            $entityType = $docAttachObj->getConstant('ENTITY_TYPE_PARTICIPANTS');
        } else {
            $entityType = $docAttachObj->getConstant('ENTITY_TYPE_APPLICANT');
        }

        $docAttachObj->setEntityType($entityType);

        // Get constant related to
        if ($isUser == 'member') {
            $relatedTo = $docAttachObj->getConstant('RELATED_TO_MEMBER');
        } else {
            $relatedTo = $docAttachObj->getConstant('RELATED_TO_RECRUITMENT');
        }

        $docAttachObj->setRelatedTo($relatedTo);

        // Created Portal
        $docAttachObj->setCreatedPortal(isset($data->created_portal) && $data->created_portal != '' ? $data->created_portal : 1);

        if (property_exists($data, 'expiry_date')) {
            $docAttachObj->setExpiryDate(empty(trim($data->expiry_date)) ? null : date('Y-m-d', strtotime($data->expiry_date)));
        }

        if (property_exists($data, 'issue_date')) {
            $docAttachObj->setIssueDate(empty(trim($data->issue_date)) ? null : date('Y-m-d', strtotime($data->issue_date)));
        }

        if (property_exists($data, 'license_type')) {
            $docAttachObj->setLicenseType(trim($data->license_type)?? null);
        }
        if (property_exists($data, 'issuing_state')) {
            $docAttachObj->setIssuingState(trim($data->issuing_state)?? null);
        }
        if (property_exists($data, 'vic_conversion_date')) {
            $docAttachObj->setVicConversionDate(trim($data->vic_conversion_date)?? null);
        }
        if (property_exists($data, 'applicant_specific')) {
            $docAttachObj->setApplicantSpecific(trim($data->applicant_specific)?? null);
        }

        if (!empty($data->visa_category)) {
            $docAttachObj->setVisaCategory($data->visa_category?? null);
        }

        if (!empty($data->visa_category_type)) {
            $docAttachObj->setVisaCategoryType($data->visa_category_type ?? null);
        }

        // Insert the data using basic model function
        $documentId = $docAttachObj->createDocumentAttachment();

        if ($entity_id == '') {
            $entity_id = $documentId;
        }

        if (!$documentId) {
            return ['status' => false, 'error' => "Document is not created. something went wrong"];
        }

        // upload attachment to permanaent folder
        $documentAttachment = $this->s3_upload_content_document_attachement($documentId, $userId, $testCase, $isUser, $entity_id);

        if (!$documentAttachment) {
            return ['status' => false, 'error' => "Document Attachment is not created. something went wrong"];
        }

        return ['status' => true, 'msg' => "Document created successfully", 'document_id' => $documentId];
    }

    /*
     * Save the uploaded attachment to permanent folder
     * @param {int} $document_id
     */
    public function s3_upload_content_document_attachement($document_id, $admin_id, $testCase, $isUser, $directory_name)
    {
        $error = array();
        $attachments = array();
        if (!empty($_FILES)) {
            // Initialize variables
            $attachments = array();
            if ($isUser == 'member') {
                $module_id = 2;
                $member_folder = S3_MEMBER_DOCUMENT_FILE_PATH;
                $absoluteUploadPath = realpath(adminPortalPath() . ltrim(MEMBER_DOCUMENT_FILE_PATH, './')) . DIRECTORY_SEPARATOR; // user here constact for specific path
                // create folder
                $parent_folder = FCPATH . './uploads/member/';
                if (!is_dir($parent_folder) && getenv('IS_APPSERVER_UPLOAD') == 'yes') {
                    mkdir($parent_folder, 0766, true);
                }

                $child_folder = FCPATH . MEMBER_DOCUMENT_FILE_PATH;
                if (!is_dir($child_folder) && getenv('IS_APPSERVER_UPLOAD') == 'yes') {
                    mkdir($child_folder, 0766, true);
                }
            } else  if ($isUser == 'participants') {
                $module_id = 2;
                $member_folder = S3_PARTICIPANTS_DOCUMENT_FILE_PATH;
                $absoluteUploadPath = realpath(adminPortalPath() . ltrim(S3_PARTICIPANTS_DOCUMENT_FILE_PATH, './')) . DIRECTORY_SEPARATOR; // user here constact for specific path
                // create folder
                $parent_folder = FCPATH . './uploads/member/';
                if (!is_dir($parent_folder) && getenv('IS_APPSERVER_UPLOAD') == 'yes') {
                    mkdir($parent_folder, 0766, true);
                }

                $child_folder = FCPATH . S3_PARTICIPANTS_DOCUMENT_FILE_PATH;
                if (!is_dir($child_folder) && getenv('IS_APPSERVER_UPLOAD') == 'yes') {
                    mkdir($child_folder, 0766, true);
                }
            } else {
                $module_id = 1;
                $member_folder = S3_APPLICANT_ATTACHMENT_UPLOAD_PATH;
                $absoluteUploadPath = realpath(adminPortalPath() . ltrim(APPLICANT_ATTACHMENT_UPLOAD_PATH, './')) . DIRECTORY_SEPARATOR; // user here constact for specific path
                // create folder
                $parent_folder = FCPATH . './uploads/recruitment/';
                if (!is_dir($parent_folder) && getenv('IS_APPSERVER_UPLOAD') == 'yes') {
                    mkdir($parent_folder, 0766, true);
                }

                $child_folder = FCPATH . APPLICANT_ATTACHMENT_UPLOAD_PATH;
                if (!is_dir($child_folder) && getenv('IS_APPSERVER_UPLOAD') == 'yes') {
                    mkdir($child_folder, 0766, true);
                }
            }

            if ($directory_name == '') {
                $directory_name = $document_id;
            }

            $input_name = 'attachments';

            $allowed_types = 'jpg|jpeg|png|xlx|xls|doc|docx|pdf|csv|odt|rtf';
            $files = $_FILES;
            $cpt = count($_FILES[$input_name]['name']);

            // load amazon s3 library
            $this->load->library('AmazonS3');
            $this->load->library('S3Loges');

            // for loop
            for ($i = 0; $i < $cpt; $i++) {
                $file_name = $files[$input_name]['name'][$i];
                $file_type = $files[$input_name]['type'][$i];
                $tmp_name = $files[$input_name]['tmp_name'][$i];
                $file_size = $files[$input_name]['size'][$i];
                $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $path_parts = pathinfo($files[$input_name]['name'][$i]);
                $filename_ext =  $path_parts['extension'];
                $filename_wo_ext =  $path_parts['filename'];
                $folder_key = $member_folder . $directory_name . '/' . $filename_wo_ext . '_' . time() . '.' . $filename_ext;

                //  Save the doc in app server
                $config = [];
                $config['file_name'] = $file_name;
                $config['input_name'] = $input_name;
                $config['remove_spaces'] = false;
                $config['directory_name'] = $directory_name;
                $config['allowed_types'] = DEFAULT_ATTACHMENT_UPLOAD_TYPE; //'jpg|jpeg|png|xlx|xls|doc|docx|pdf|pages';
                $config['max_size'] = DEFAULT_MAX_UPLOAD_SIZE;

                //Upload file in App server if Appser upload enabled
                if (getenv('IS_APPSERVER_UPLOAD') == 'yes' && is_dir($child_folder) == true) {
                    $config['upload_path'] = $absoluteUploadPath;
                    do_muliple_upload($config); // upload file in local
                }

                /**
                 * set dynamic values
                 * $tmp_name should be - Uploaded file tmp storage path
                 * $folder_key shoulb - Saving path with file name into s3 bucket
                 *      - you can add a folder like `folder/folder/filename.ext`
                 */
                $this->amazons3->setSourceFile($tmp_name);
                $this->amazons3->setFolderKey($folder_key);

                $this->s3loges->setModuleId($module_id ?? 0);
                $this->s3loges->setCreatedAt(DATE_TIME);
                $this->s3loges->setCreatedBy($admin_id);
                $this->s3loges->setTitle('File Transfer Initiated for Add Document against ' . $isUser . ' ' . $directory_name);
                $this->s3loges->setLogType('init');
                $this->s3loges->setDescription(json_encode($config));
                $this->s3loges->createS3Log();

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

                    $this->s3loges->setTitle($isUser . ' ' . $directory_name . ' - ' . $file_name . ' - S3 File transfer Completed');
                    $this->s3loges->setLogType('success');
                    $this->s3loges->setDescription(json_encode($aws_response));
                    $this->s3loges->createS3Log();
                } else {
                    // failed
                    $this->s3loges->setTitle($isUser . ' ' . $directory_name . ' - ' . $file_name . ' - S3 File transfer Completed with error!');
                    $this->s3loges->setLogType('failure');
                    $this->s3loges->setDescription(json_encode($aws_response));
                    $this->s3loges->createS3Log();
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

                $docAttachPropertyObj = new DocumentAttachment();
                $docAttachPropertyObj->setDocId($document_id);
                $docAttachPropertyObj->setFilePath($folder_key);
                $docAttachPropertyObj->setFileType($file_type);
                $docAttachPropertyObj->setFileExt($file_ext);
                $docAttachPropertyObj->setFileSize($file_size);
                $docAttachPropertyObj->setAwsResponse($aws_response);
                $docAttachPropertyObj->setAwsObjectUri($aws_object_uri);
                $docAttachPropertyObj->setAwsFileVersionId($aws_file_version_id);
                $docAttachPropertyObj->setFileName($file_name);
                $docAttachPropertyObj->setRawName($file_name);
                $docAttachPropertyObj->setAwsUploadedFlag($aws_uploaded_flag);
                $docAttachPropertyObj->setArchive(0);
                $docAttachPropertyObj->setCreatedAt(DATE_TIME);
                $docAttachPropertyObj->setCreatedBy($admin_id);

                $documentAttachId = $docAttachPropertyObj->createDocumentAttachmentProperty();
            }
        }
        return $attachments;
    }
    /*
     *
     * @params {object} $reqData
     *
     * Return type Array - $result
     */
    public function get_doucment_attachment_data_by_id($reqData)
    {
        if (isset($reqData) && isset($reqData->document_id)) {
            // Document Attachment
            $docAttachObj = new DocumentAttachment();
            // Get constant
            $relatedToRecruitment = $docAttachObj->getConstant('RELATED_TO_RECRUITMENT');
            $relatedToMember = $docAttachObj->getConstant('RELATED_TO_MEMBER');
            $entityTypeApplicant = $docAttachObj->getConstant('ENTITY_TYPE_APPLICANT');
            $entityTypeMember = $docAttachObj->getConstant('ENTITY_TYPE_MEMBER');

            $document_id = $reqData->document_id;
            $base_url = base_url('mediaShow/m');

            $select_column = [
                "tmd.id", "tmd.entity_id", "tmd.entity_type", "tmd.id as document_id", "tmd.document_status", "tmd.member_id", "tmd.archive", "tmd.issue_date", "tmd.expiry_date", "tmd.reference_number", "tmd.created_by", "tmd.created_at", "tmd.updated_by", "tmd.updated_at", "tmda.file_name",  "tmda.raw_name", "tmd.draft_contract_type", "tmd.applicant_id",

                "tmda.file_type",
                "tmda.file_size",
                "tmda.file_ext",
                "tmda.created_at AS attached_on",
                "tmda.aws_uploaded_flag",
                "tmd.updated_at AS updated_on",
                "'" . $base_url . "' AS file_base_path",
                "TO_BASE64(tmda.file_name) AS converted_name",
                "'name=' AS uri_param_1",
                "CONCAT(tmd.id, '/', REPLACE(TO_BASE64(tmda.file_path), '=', '%3D%3D'), '?download_as=', REPLACE(tmda.raw_name, ' ', ''), '&s3=true') AS file_path",
                "tmd.doc_type_id",
                "td.title AS document",
                "td.issue_date_mandatory",
                "td.expire_date_mandatory",
                "td.reference_number_mandatory",
                "tmd.license_type",
                "tmd.issuing_state",
                "tmd.vic_conversion_date",
                "tmd.applicant_specific",
                "tmd.visa_category",
                "tmd.visa_category_type"
            ];
            // Query
            $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
            $this->db->select("
              CASE
                WHEN tmd.entity_type = 1 THEN
                (
                  CASE
                  WHEN tmd.draft_contract_type = 1 THEN CONCAT('Draft Contract_', ra.firstname,' ', ra.lastname)
                  WHEN tmd.draft_contract_type = 2 THEN CONCAT('Employment Contract_', ra.firstname,' ', ra.lastname)
                  ELSE 
                      CASE
                          WHEN td.title IS NOT NULL THEN
                              CONCAT(td.title,'_',, ra.firstname,' ', ra.lastname) 
                          ELSE 
                              CONCAT(ra.firstname,' ', ra.lastname) 
                          END
                  END
                )
                ELSE
                  tmda.file_name 
                END
               as file_name
            ");
            $this->db->select("(CASE
              WHEN tmd.draft_contract_type = 1 THEN CONCAT('Draft Contract_', ra.firstname,' ', ra.lastname)
              WHEN tmd.draft_contract_type = 2 THEN CONCAT('Employment Contract_', ra.firstname,' ', ra.lastname)
                    else 
                        CASE
                            WHEN td.title IS NOT NULL THEN
                                CONCAT(td.title,'_',, ra.firstname,' ', ra.lastname) 
                            ELSE 
                                CONCAT(ra.firstname,' ', ra.lastname) 
                            END
                    end) as download_as
            ");
            $this->db->from('tbl_document_attachment as tmd');
            $this->db->join('tbl_document_attachment_property as tmda', 'tmda.doc_id = tmd.id AND tmda.archive = 0', 'left');
            $this->db->join('tbl_document_type as td', 'td.id = tmd.doc_type_id', 'left');
            $this->db->join("tbl_recruitment_applicant as ra", "ra.id = tmd.entity_id AND tmd.entity_type=" . $entityTypeApplicant, "LEFT");
            $this->db->where('tmd.id', $document_id);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->num_rows() > 0 ? $query->result_array() : [];
            $result = $query->num_rows() > 0 ? $result[0] : [];

            if (!empty($result)) {
                $result = (object) $result;
                //Assign the file name to s3 file path
                $filename = $result->file_path;

                if (!$result->aws_uploaded_flag) {
                    $filename = $result->raw_name;
                }

                // if ($result->entity_type == 1 && $result->aws_uploaded_flag == 0) {
                //     if ($result->draft_contract_type == 1) {
                //         $folder_url = $result->aws_uploaded_flag == 0 ? GROUP_INTERVIEW_CONTRACT_PATH : APPLICANT_ATTACHMENT_UPLOAD_PATH;
                //         $result->file_path = base_url('mediaShow/rg/' . $result->applicant_id . '/' . urlencode(base64_encode($filename)));
                //         $docPath = $folder_url . '/' . $filename;
                //     } else if ($result->draft_contract_type == 2) {
                //         if ($result->aws_uploaded_flag == 0) {
                //             $folder_url = CABDAY_INTERVIEW_CONTRACT_PATH;
                //             $result->file_path = base_url('mediaShow/rc/' . $result->applicant_id . '/' . urlencode(base64_encode($result->applicant_id . '/' . $filename)));
                //         } else {
                //             $folder_url = APPLICANT_ATTACHMENT_UPLOAD_PATH;
                //             $result->file_path = base_url('mediaShow/rc/' . $result->applicant_id . '/' . urlencode(base64_encode($filename)));
                //         }
                //         $docPath = $folder_url . $result->applicant_id . '/' . $filename;
                //     } else {
                //         $result->file_path = base_url('mediaShow/r/' . $result->applicant_id . '/' . urlencode(base64_encode($filename)));
                //         $docPath = APPLICANT_ATTACHMENT_UPLOAD_PATH . $result->applicant_id . '/' . $filename;
                //     }

                //     // Export with derived filename instead
                //     // of goiing into trouble of renaming the file during upload process
                //     //$download_as = $this->determine_filename_by_doc_category($result->attachment, $result->doc_category, $result->applicant_id);
                //     $download_name =  $result->download_as;
                //     $download_name = preg_replace('/\s+/', '_', $download_name);
                //     $extension = pathinfo($result->raw_name, PATHINFO_EXTENSION);
                //     $http_build_query = http_build_query(['download_as' => $download_name . '.' . $extension]);

                //     if ($result->aws_uploaded_flag == 1) {
                //         $http_build_query = http_build_query(['download_as' => $download_name . '.' . $extension, 's3' => 'true']);
                //     }

                //     $result->file_path .= '?' . $http_build_query;
                //     $result->file_path = base_url('mediaShowView?tc=' . $result->id . '&rd=') . urlencode(base64_encode($result->file_path));
                // }
                
            }
            return ["status" => true, 'data' => $result, 'msg' => 'Fetch Document detail successfully'];
        } else {
            return ["status" => false, 'error' => 'Document Id is missing'];
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
    public function edit_document_attachment($data, $adminId, $testCase = false, $isUser)
    {
        // Check the document data
        if ($data && $data['document_id']) {

            $docAttachObj = new DocumentAttachment();

            if ($isUser == 'member') {
                $entity_id = $data['member_id'];
                $updated_type = $docAttachObj->getConstant('ENTITY_TYPE_MEMBER');
            } else if ($isUser == 'participants') {
                $entity_id = $data['participant_id'];
                $updated_type = $docAttachObj->getConstant('ENTITY_TYPE_PARTICIPANTS');
            } else {
                $entity_id = $data['applicant_id'];
                $adminId = $data['applicant_id'];
                $updated_type = $docAttachObj->getConstant('ENTITY_TYPE_APPLICANT');
            }
            /**
             * Update document
             */


            $docAttachObj->setDocTypeId($data['doc_type_id']);
            $docAttachObj->setDocumentStatus($data['status']);
            $docAttachObj->setReferenceNumber(!empty($data['reference_number']) && $data['reference_number'] != 'null' ? $data['reference_number'] : NULL);
            $docAttachObj->setExpiryDate($data["expiry_date"] != ''  ? date('Y-m-d', strtotime($data["expiry_date"])) : NULL);
            $docAttachObj->setIssueDate($data["issue_date"] != '' ? date('Y-m-d', strtotime($data["issue_date"])) : NULL);
            $docAttachObj->setUpdatedAt(DATE_TIME);
            $docAttachObj->setUpdatedBy($entity_id);
            $docAttachObj->setUpdatedByType($updated_type);
            $docAttachObj->setDocId($data['document_id']);
            $docAttachObj->setLicenseType(trim($data['license_type'])?? null);
            $docAttachObj->setIssuingState(trim($data['issuing_state'])?? null);
            $docAttachObj->setVicConversionDate(trim($data['vic_conversion_date'])?? null);
            $docAttachObj->setApplicantSpecific(trim($data['applicant_specific'])?? null);
            $docAttachObj->setVisaCategory(trim($data['visa_category'])?? null);
            $docAttachObj->setVisaCategoryType($data['visa_category_type']?? null);
            $docAttachObj->updateDocumentAttachment();

            $documentId = $data['document_id'];

            if (!$documentId) {
                return ['status' => false, 'error' => "Document is not updated. something went wrong"];
            }

            // if files not empty update attachment
            if (!empty($_FILES)) {
                $this->archive_document_attachment_v1($adminId, $data['document_id'], $updated_type);

                // upload attachment to permanaent folder
                $documentAttachment = $this->s3_upload_content_document_attachement($documentId, $adminId, $testCase, $isUser, $entity_id);

                if (!$documentAttachment) {
                    return ['status' => false, 'error' => "Document Attachment is not created. something went wrong"];
                }
            }

            return ['status' => true, 'msg' => "Document updated successfully", 'document_id' => $documentId];
        } else {
            return ["status" => false, 'error' => 'Document Id is missing'];
        }
    }

    /**
     * Archve document using document id
     * @param {int} $adminId
     * @param {int} $id
     */
    public function archive_document_attachment_v1($adminId, $id, $updated_type)
    {

        // Check document is exist. Using id
        $where = array('id' => $id);
        $colown = array('id');
        $check_document = $this->basic_model->get_record_where('document_attachment', $colown, $where);

        if (empty($check_document)) {
            $response = ['status' => false, 'error' => "Document does not exist anymore."];
            echo json_encode($response);
            exit();
        }

        // Get document atachment is exist. if yes get the recent attachments id
        $where = array('doc_id' => $id, "archive" => 0, "aws_uploaded_flag" => 1);
        $colown = array('*');
        $this->db->select($colown);
        $this->db->from(TBL_PREFIX . 'document_attachment_property', $colown, $where);
        $this->db->where($where);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $get_attachment = $query->result();

        // update member documents
        $upd_data["updated_at"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["updated_by_type"] = $updated_type;
        $upd_data["archive"] = 1;
        $result = ["id" => $id];
        $result = $this->basic_model->update_records("document_attachment", $upd_data, ["id" => $id]);

        // Check document atachment is exist. if yes get the recent attachments id
        $where = array('doc_id' => $id, "archive" => 0);
        $colown = array('group_concat(id) AS id');
        $this->db->select($colown);
        $this->db->from(TBL_PREFIX . 'document_attachment_property', $colown, $where);
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
            $document_row = $this->db->query('UPDATE tbl_document_attachment_property SET updated_at = "' . DATE_TIME . '", updated_by = ' . $adminId . ', archive = 1 where id IN (' . $ids . ')');
        }

        // move to archive

        // load amazon s3 library
        $this->load->library('AmazonS3');
        foreach ($get_attachment as $key => $attachment) {
            $attachment = (array) $attachment;
            if (isset($attachment['aws_object_uri']) && $attachment['aws_object_uri'] != '') {
                $file_path = $attachment['file_path'];
                $copySource = $attachment['aws_object_uri'];
                $folder_key = 'archive/' . $file_path;
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
    /** Verify the document download access call like proper document against proper user
     *  
     * @param $applicant_id {int} applicant id 
     * @param $member_id {int} member id
     * @param $doc_id {int} document id
     * @param $file_path {str} aws file path to download
     * 
     * @return $result {array} true/false with message
    */
    public function verifydocumentDownload($applicant_id, $member_id, $doc_id, $file_path) {
        $this->db->select("tda.id");

        $docAttachObj = new DocumentAttachment();
        
        // Get constant       
        $entityTypeApplicant = $docAttachObj->getConstant('ENTITY_TYPE_APPLICANT');
        $entityTypeMember = $docAttachObj->getConstant('ENTITY_TYPE_MEMBER');

       $this->db->from('tbl_document_attachment as tda');       
       $this->db->join('tbl_document_attachment_property as tdap', 'tdap.doc_id = tda.id AND tdap.archive = 0 AND tdap.file_path = "' . $this->db->escape_str($file_path) .'" AND tdap.doc_id = "'. $this->db->escape_str($doc_id) .'"', 'INNER', FALSE);
        $this->db->join("tbl_recruitment_applicant as ra", "ra.id = tda.entity_id AND tda.entity_type=" . $entityTypeApplicant, "LEFT");
        
        $whereEntity = '';
        if ($member_id != '' && $member_id != 'null') {
            $whereEntity .= 'tda.entity_type = ' . $this->db->escape_str($entityTypeMember) . ' AND tda.entity_id = ' . $this->db->escape_str($member_id);
        }
        if ($applicant_id != '' && $applicant_id != 'null') {
            if ($whereEntity != '') {
                $whereEntity .= ' OR ';
            }
            $whereEntity .= 'tda.entity_type = ' . $this->db->escape_str($entityTypeApplicant) . ' AND tda.entity_id = ' . $this->db->escape_str($applicant_id);
        }
        if ($whereEntity != '') {
            $this->db->where('(' . $this->db->escape_str($whereEntity) . ')');
        }

        $this->db->where('tda.archive', 0);

        $query = $this->db->get();
        $result = ['status' => FALSE, 'msg' => 'Document not matched!'];
        if($query->num_rows() > 0) {
            $result = ['status' => TRUE, 'msg' => 'Document matched!'];
        }
        return $result;
    }
}
