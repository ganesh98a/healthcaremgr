<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Recruitment_applicant_docusign_model extends CI_Model {

    /*
     * It is used to get the docusign document list
     * Operation:
     *  - searching
     *  - filter
     *  - sorting
     *
     * Return type Array
     */
    public function get_docusign_document_list($reqData, $is_portal = false,$bu_data=null) {
        // Get subqueries
        try {
        $this->load->model('common/Common_model');
        $document_name_sub_query = $this->get_document_name_sub_query('tda');
        $applicant_name_sub_query = $this->get_applicant_sub_query('tda', 'entity_id');
        $member_created_by_sub_query = $this->get_created_updated_by_sub_query('created_by','tda');
        $member_updated_by_sub_query = $this->get_created_updated_by_sub_query('updated_by','tda');
        $applicant_updated_by_sub_query = $this->get_applicant_sub_query('tda', 'updated_by');
        $applicant_created_by_sub_query = $this->get_applicant_sub_query('tda', 'created_by');

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $member_id = $reqData->member_id ?? '';
        $applicant_id = $reqData->applicant_id ?? '';
        $application_id = $reqData->application_id ?? '';
        $orderBy = '';
        $direction = '';

        // where document type is
        $where_entity_type = [];
        $where_entity_id = [];

        require_once APPPATH . 'Classes/document/DocumentAttachment.php';

        $docAttachObj = new DocumentAttachment();

        // Get constant
        $relatedToRecruitment = $docAttachObj->getConstant('RELATED_TO_RECRUITMENT');
        $relatedToMember = $docAttachObj->getConstant('RELATED_TO_MEMBER');
        $entityTypeApplicant = $docAttachObj->getConstant('ENTITY_TYPE_APPLICANT');
        $entityTypeMember = $docAttachObj->getConstant('ENTITY_TYPE_MEMBER');
        $draftStatus = $docAttachObj->getConstant('DOCUMENT_STATUS_DRAFT');

        if ($applicant_id != '') {
          $where_entity_type[] = $entityTypeApplicant;
          $where_entity_id[] = $applicant_id;
        }

        // Searching column
        $src_columns = array('file_name', 'file_type', 'status', 'reference_number', 'created_by', 'created_at', 'signed_date');
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
        $available_column = ["id", "doc_type_id", "entity_id", "entity_type", "document_id", "document_status", "member_id", "archive", "issue_date", 
                             "expiry_date", "reference_number", "created_by", "created_at", "updated_by", "updated_at", "file_name", "draft_contract_type", 
                             "applicant_id", "file_type","file_size", "raw_name","signed_status","sent_by","signed_date",
                             "file_ext","attached_on","updated_on","aws_uploaded_flag","converted_name","uri_param_1", "system_gen_flag",
                            ];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
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
            if ($filter->filter_status === "unsigned") {
                $this->db->where('tdap.signed_status', 0);
            } else if ($filter->filter_status === "signed") {
                $this->db->where('tdap.signed_status', 1);
            }
        }

        $base_url = base_url('mediaShow/m');

        $select_column = ["tda.id", "tda.doc_type_id", "tda.entity_id", "tda.entity_type", "tda.id as document_id", "tda.document_status", "tda.member_id", "tda.archive", "tda.issue_date", "tda.expiry_date", "tda.reference_number", "tda.created_by", "tda.created_at", "tda.updated_by", "tda.updated_at", "tdap.file_name", "tda.draft_contract_type", "tda.applicant_id",
            "tdap.file_type",
            "tdap.file_size",
            "tdap.raw_name",
            "tdap.signed_status",
            "tdap.sent_by",
            "tdap.signed_date",
            "CONCAT('.',tdap.file_ext) AS file_ext",
            "tdap.created_at AS attached_on",
            "tda.updated_at AS updated_on",
            "tdap.aws_uploaded_flag",
            "'".$base_url."' AS file_base_path",
            "TO_BASE64(tdap.file_name) AS converted_name",
            "'name=' AS uri_param_1",
            "CONCAT('mediaShow/m/', tda.id, '/', REPLACE(TO_BASE64(tdap.file_path), '=', '%3D%3D'), '?download_as=', REPLACE(CONCAT(tdt.title,'.', tdap.file_ext), ' ', ''), '&s3=true') AS file_path",
            "tdt.system_gen_flag",
        ];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        // Query
        $this->db->select("(CASE
          WHEN tda.draft_contract_type = 1 THEN CONCAT('Draft Contract_', ra.firstname,' ', ra.lastname)
                else 
                    CASE
                        WHEN tdt.title IS NOT NULL THEN
                            CONCAT(tdt.title,'_',, ra.firstname,' ', ra.lastname) 
                        ELSE 
                            CONCAT(ra.firstname,' ', ra.lastname) 
                        END
                end) as download_as
        ");
        $this->db->select(" CONCAT(ra.firstname,' ', ra.lastname) as applicant");
        $this->db->select("
          CASE
            WHEN tda.entity_type = 1 THEN
            (
              CASE
              WHEN tda.draft_contract_type = 1 THEN CONCAT('Draft Contract_', ra.firstname,' ', ra.lastname)
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
            (" . $applicant_name_sub_query . ")
         as applicant");
        $this->db->select("
            (" . $member_created_by_sub_query . ")
         as created_by");
         $this->db->select("
            (" . $applicant_updated_by_sub_query . ")
         as updated_by");
        $this->db->select("(CASE
            WHEN tdap.signed_status = 0 THEN 'Not Signed'
            WHEN tdap.signed_status = 1 THEN 'Signed'
          Else '' end
        ) as status");
        $this->db->from('tbl_document_attachment as tda');
        $this->db->join('tbl_document_attachment_property as tdap', 'tdap.doc_id = tda.id AND tdap.archive = 0', 'left');
        $this->db->join('tbl_document_type as tdt', 'tdt.id = tda.doc_type_id AND tdt.archive = 0', 'left');
        $this->db->join("tbl_recruitment_applicant as ra", "ra.id = tda.entity_id AND tda.entity_type=".$entityTypeApplicant, "LEFT");
        $this->db->where('tda.archive', 0);
        $this->db->where('tda.draft_contract_type', 2);
        if ($is_portal == false) {
          $this->db->where('tda.document_status !=', $draftStatus);
        }
        if(isset($application_id)) {
            $this->db->where("tda.application_id in ($application_id,0)");
        }
        if($bu_data && $this->Common_model->check_is_bu_unit($bu_data)) {
           $this->db->where('ra.bu_id', $bu_data->business_unit['bu_id']);
        }
        $whereEntity = '';
        if ($applicant_id != '' && $applicant_id != 'null') {
          if ($whereEntity != '') {
            $whereEntity .= ' OR ';
          }
          $whereEntity .= 'tda.entity_type = '.$entityTypeApplicant.' AND tda.entity_id = '.$this->db->escape_str($applicant_id);
        }
        if (!empty($applicant_id)) {
            $this->db->group_start();
            $this->db->where("tda.entity_type", $entityTypeApplicant);
            $this->db->where("tda.entity_id", $this->db->escape_str($applicant_id, true));
            $this->db->group_end();
        }
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        // echo last_query();
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

                if(!$val->aws_uploaded_flag) {
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
                        $val->file_path = base_url('mediaShow/rc/' . $val->applicant_id . '/' . urlencode(base64_encode($val->applicant_id . '/' .$filename)));
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
                  $val->file_path = base_url('mediaShowView?tc='.$val->id.'&rd=').urlencode(base64_encode($val->file_path));
                } 
            }
        }
        $this->db->select("COUNT(*) as count");
        $this->db->from("tbl_document_attachment as tda");
        $this->db->where("tda.archive", 0);
        $this->db->where("tda.draft_contract_type", 2);
        if (!empty($applicant_id)) {
            $this->db->group_start();
            $this->db->where("tda.entity_type", $entityTypeApplicant);
            $this->db->where("tda.entity_id", $this->db->escape_str($applicant_id, true));
            $this->db->group_end();
        }
        if(isset($application_id)) {
            $this->db->where("tda.application_id", $this->db->escape_str($application_id, true));
        }
        
        // Get total rows inserted count
        $document_row = $this->db->get()->row_array();
        $document_count = intVal($document_row['count']);

        return array('count' => $dt_filtered_total, 'document_count' => $document_count, 'data' => $result, 'status' => true, 'msg' => 'Fetch member document list successfully');
        } catch(Exception $e){
            return array('status' => false, 'error' => 'Something went wrong');            
        }
        
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
     * it is used for making sub query of member name
     * return type sql
     */
    private function get_applicant_sub_query($tbl_alais, $column_alias) {
        $this->db->select("CONCAT_WS(' ', tra.firstname,tra.lastname)");
        $this->db->from(TBL_PREFIX . 'recruitment_applicant as tra');
        $this->db->where("tra.id = ".$tbl_alais.".".$column_alias, null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

   /**
   * Get document type from tbl_document_type table
   * @param $reqData {array} it has col1, col2, document category, module id
   * @param $multiple {bool} TRUE/FALSE set true for fetch more than one category list
   *  and pass more than one value in reqData['doc_category] as a array format
   * @param $active {bool} TRUE/FALSE
   * @param $archive {bool} TRUE/FALSE
   *
   * @return {ojbect} document types
   */
  public function get_document_type_employment_contract() {

    $this->db->select(['docs.title as label', 'docs.id as value', 'docs.issue_date_mandatory', 'docs.expire_date_mandatory', 'docs.reference_number_mandatory']);

    $this->db->from(TBL_PREFIX . 'document_type as docs');
    $this->db->join(TBL_PREFIX . 'document_type_related as docs_rel', 'docs.id = docs_rel.doc_type_id', 'inner');
    $this->db->where('docs_rel.related_to', 1);
    $this->db->group_start();
    $this->db->like('docs.title', 'Employment Contract');
    $this->db->or_like('docs.title', 'DSW');
    $this->db->or_like('docs.title', 'Payroll Documents');
    $this->db->group_end();
    $this->db->where('docs.archive', 0);
    $this->db->where('docs.active', 1);
    $this->db->order_by('docs.title', 'ASC');
    $query = $this->db->get();
    return $query->result_object();

  }

  /**
   * Generate Employment Contract
   */
  public function generate_docusign_contract($applicantId, $recruitment_task_applicant_id, $extraParms = ['type' => 'group_interview','file_name' => ''], $application_id = "", $reqData = null)
  {
    $contractALlowedFor = [
        'group_interview'=> [
            'file_path'=> GROUP_INTERVIEW_CONTRACT_PATH
        ],
        'cabday_interview'=> [
            'file_path'=>CABDAY_INTERVIEW_CONTRACT_PATH
        ]
    ];
    $file_name = isset($extraParms['file_name']) ? $extraParms['file_name'] :'';
    $adminId = isset($extraParms['adminId']) ? $extraParms['adminId'] : 0;
    $type = isset($extraParms['type']) ? $extraParms['type'] : 'group_interview';
    $file_name = !empty($file_name) ? $file_name : $recruitment_task_applicant_id.'.pdf';
    error_reporting(0);
    $file = '';
    $fileHeader = '';
    $fileFooter = '';
    if($reqData->doc_type_name==DSW_CASUAL_VIC){
        $pdfFilePath = $this->generateContractForDswVicPDF($applicantId, $type, $file_name, $contractALlowedFor); 
    }else if($reqData->doc_type_name==DSW_CASUAL_QLD){
        $pdfFilePath = $this->generateContractForDswQldPDF($applicantId, $type, $file_name, $contractALlowedFor);
    }else if($reqData->doc_type_name==DSW_CASUAL_NSW){        
        $pdfFilePath = $this->generateContractForDswNswPDF($applicantId, $type, $file_name, $contractALlowedFor);
    }
    //payroll & superannunation form are same
    else if($reqData->doc_type_name==PAYROLL_DOCUMENTS){
       
        $pdfFilePath='./assets/docusign_template/New_PayRoll_Form.pdf';
    }
    else  if($reqData->doc_type_name=='Employment Contract'){
        $pdfFilePath = $this->generateContractPDF($applicantId, $type, $file_name, $contractALlowedFor);
    }
    
   
    $contract_generate = false;
    if(file_exists($pdfFilePath))
    {
        $contract_generate = true;
        
        // Save as attachment
        $attachment_ids = $this->Recruitment_group_interview_model->group_or_docusign_signed_contract_save_as_attachment($application_id, $applicantId, $file_name, $type, $adminId, $recruitment_task_applicant_id,$reqData->doc_type_name, false);
        $attachment_property_id = '';
        $attachment_id = '';
     
        if (isset($attachment_ids) == true && isset($attachment_ids['attachment_id']) ==true) {
            $attachment_id = $attachment_ids['attachment_id'];
            $data = (array) $reqData;
            if (isset($data["cc_email"]) == true && empty($data["cc_email"]) == false) {
                $cc_email = (array) ($data["cc_email"]);
                $insData = [];
                $temp = [
                    'doc_attach_id' => $attachment_id,
                    'subject' => $data['subject'],
                    'email_content' => $data['email_content'],
                    'cc_email_flag' => isset($data["cc_email_flag"]) ? $data["cc_email_flag"] : 0,
                    'cc_email' => json_encode($cc_email),
                    'created_at' => DATE_TIME,
                    'created_by' => $adminId,
                ];
                $insData[] = $temp;
            
                $this->basic_model->insert_update_batch('insert', 'document_attachment_email', $insData);
            }
        }
        if (isset($attachment_ids) == true && isset($attachment_ids['attachment_property_id']) ==true) {
            $attachment_property_id = $attachment_ids['attachment_property_id'];
        }

        if($type=='cabday_interview'){
            
            // generate and send docusign contract
            $statusDocuSign = $this->generateAndSendContract($applicantId, $adminId, $pdfFilePath, $reqData);
            if(isset($statusDocuSign['status']) && $statusDocuSign['status']) {
                $envId = $statusDocuSign['response']['envelopeId'];
                if ($attachment_property_id !='' && $attachment_property_id != 0) {
                    $this->basic_model->update_records('document_attachment_property', array('envelope_id'=>$envId),array('id'=>$attachment_property_id,'archive'=>0));

                }
                
                $applicantName = $info['applicant_name'] ?? '';
                $request_params = [];
                $request_params['to_email'] = $info['email'];
                $request_params['subject'] = $applicantName . ' Applicant Employment contract ' . $reqData->doc_type_name . ' sent successfully';
                $request_params['body'] = $applicantName. ' Applicant Employment contract ' . $reqData->doc_type_name . ' sent successfully';
                $request_params['fullname'] = $applicantName;
                $request_params['filename'] = $file_name;

                require_once APPPATH . 'Classes/CommunicationLog.php';
                $logObj = new CommunicationLog();

                // only for log purpose
                $sms_log_data[] = [
                    'from' => APPLICATION_NAME,
                    'communication_text' => $request_params['body'],
                    'userId' => $applicantId,
                    'user_type' => 1,
                    'send_by' => $adminId,
                    'log_type' => 2,
                    'created' => DATE_TIME,
                    'title' => 'Employment DocuSign ' . $reqData->doc_type_name . ' Sent',
                ];

                $logObj->createMuitipleCommunicationLog($sms_log_data);

            }else{
                $contract_generate = false;
            }
        }

        //Delete file in App server if Appser upload is no
        if($reqData->doc_type_name!=PAYROLL_DOCUMENTS){
            if(getenv('IS_APPSERVER_UPLOAD') != 'yes') {
                unlink($pdfFilePath);
            }
        }
        if($contract_generate && $recruitment_task_applicant_id)
            $this->basic_model->update_records('recruitment_applicant_group_or_cab_interview_detail', array('contract_status'=>1),array('recruitment_task_applicant_id'=>$recruitment_task_applicant_id,'archive'=>0));
        return $contract_generate;
        }
    }


    /**
   * Generate Employment Contract for Bulk applicants
   */
    public function generate_bulk_docusign_contract($applicantId, $recruitment_task_applicant_id, $extraParms = ['type' => 'group_interview','file_name' => ''], $application_id = "", $reqData = [])
    {
        $contractALlowedFor = [
            'group_interview'=> [
                'file_path'=> GROUP_INTERVIEW_CONTRACT_PATH
            ],
            'cabday_interview'=> [
                'file_path'=>CABDAY_INTERVIEW_CONTRACT_PATH
            ]
        ];
        $file_name = isset($extraParms['file_name']) ? $extraParms['file_name'] :'';
        $adminId = isset($extraParms['adminId']) ? $extraParms['adminId'] : 0;
        $type = isset($extraParms['type']) ? $extraParms['type'] : 'group_interview';
        $file_name = !empty($file_name) ? $file_name : $recruitment_task_applicant_id.'.pdf';
        error_reporting(0);
        $file = '';
        $fileHeader = '';
        $fileFooter = '';
        $pdfFilePath = $this->generateContractPDF($applicantId, $type, $file_name, $contractALlowedFor);

        /** modified for alternate approach */

        $contract_generate = false;
       if(file_exists($pdfFilePath))
       {
            $contract_generate = true;
            
            // Save as attachment
            $ins_data = array('task_applicant_id'=>$recruitment_task_applicant_id,'unsigned_file'=>$file_name, 'applicant_id' => $applicantId, 'application_id' => $application_id, 'send_date'=>DATE_TIME,'created'=>DATE_TIME);
            $ids = $this->basic_model->insert_records('recruitment_applicant_contract', $ins_data);

            $attachment_property_id = '';
            $attachment_id = '';
            
            if (isset($attachment_ids) == true && isset($attachment_ids['attachment_id']) ==true) {
                $attachment_id = $attachment_ids['attachment_id'];
                $data = (array) $reqData;
                if (isset($data["cc_email"]) == true && empty($data["cc_email"]) == false) {
                    $cc_email = (array) ($data["cc_email"]);
                    $insData = [];
                    $temp = [
                        'doc_attach_id' => $attachment_id,
                        'subject' => $data['subject'],
                        'email_content' => $data['email_content'],
                        'cc_email_flag' => isset($data["cc_email_flag"]) ? $data["cc_email_flag"] : 0,
                        'cc_email' => json_encode($cc_email),
                        'created_at' => DATE_TIME,
                        'created_by' => $adminId,
                    ];
                    $insData[] = $temp;
                    $this->basic_model->insert_update_batch('insert', 'document_attachment_email', $insData);
                }
            }
            if (isset($attachment_ids) == true && isset($attachment_ids['attachment_property_id']) ==true) {
                $attachment_property_id = $attachment_ids['attachment_property_id'];
            }

            if($type=='cabday_interview'){

    
                // generate and send docusign contract
                $statusDocuSign = $this->generateAndSendContract($applicantId, $adminId, $pdfFilePath, $reqData);
                if(isset($statusDocuSign['status']) && $statusDocuSign['status']) {
                    $envId = $statusDocuSign['response']['envelopeId'];
                        $this->basic_model->update_records('recruitment_applicant_contract', array('envelope_id'=>$envId),array('id'=>$ids,'archive'=>0));
                    
                    $applicantName = $info['applicant_name'] ?? '';
                    $request_params = [];
                    $request_params['to_email'] = $info['email'];
                    $request_params['subject'] = $applicantName . ' Applicant Employment contract document send successfully';
                    $request_params['body'] = $applicantName. ' Applicant Employment contract document send successfully';
                    $request_params['fullname'] = $applicantName;
                    $request_params['filename'] = $file_name;

                    require_once APPPATH . 'Classes/CommunicationLog.php';
                    $logObj = new CommunicationLog();

                    // only for log purpose
                    $sms_log_data[] = [
                        'from' => APPLICATION_NAME,
                        'communication_text' => $request_params['body'],
                        'userId' => $applicantId,
                        'user_type' => 1,
                        'send_by' => $adminId,
                        'log_type' => 2,
                        'created' => DATE_TIME,
                        'title' => 'Employment DocuSign document Send',
                    ];

                    $logObj->createMuitipleCommunicationLog($sms_log_data);

                }else{
                    $contract_generate = false;
                }
            }

            //Delete file in App server if Appser upload is no
            if(getenv('IS_APPSERVER_UPLOAD') != 'yes') {
                unlink($pdfFilePath);
            } 

            if($contract_generate && $recruitment_task_applicant_id)
            $this->basic_model->update_records('recruitment_applicant_group_or_cab_interview_detail', array('contract_status'=>1),array('recruitment_task_applicant_id'=>$recruitment_task_applicant_id,'archive'=>0));
            return $contract_generate;
        }
        
        //return $pdfFilePath;
    }

      

    /**
    * Generate PDF file path with local path
    * return - string (filepath)
    */
    public function generateContractPDF($applicantId, $type, $file_name, $contractALlowedFor) {
        // load model
        $this->load->model(['Recruitment_cab_day_model', 'Recruitment_group_interview_model']);
        $info = $this->Recruitment_group_interview_model->get_draft_contract_data($applicantId);

        $data['complete_data'] = isset($info) && !empty($info) ? $info : [];

        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load();

        if($type=='group_interview'){
            $pdf->SetWatermarkText('DRAFT');
            $pdf->showWatermarkText = true;
        }

        $pdf->setAutoTopMargin='pad';
        $styleContent = $this->load->view('cabday_interview_contract_style_css',[],true);
        $data['type']='employment_content';
        $employmentHtml =$this->load->view('cabday_interview_contract_v1',$data,true);
        $data['type']='staff_content';
        $staffDetailsHtml =$this->load->view('cabday_interview_contract_v1',$data,true);
        $data['type']='position_content';
        $positionDescriptionHtml =$this->load->view('cabday_interview_contract_v1',$data,true);
        $data['type']='header';
        $fileHeader =$this->load->view('cabday_interview_contract_v1',$data,true);
        $data['type']='footer';
        $fileFooter = $this->load->view('cabday_interview_contract_v1',$data,true);
        $logoUrl = base_url('assets/img/oncall_logo_multiple_color.jpg');

        $pdf->defaultfooterline = 0;
        $pdf->defaultheaderline = 0;
        $pdf->SetHeader('');
        $pdf->AddPage('P','','','','',15,15,0,10,0,0);
        $pdf->SetFooter($fileFooter);
        $pdf->WriteHTML($styleContent);
        $pdf->WriteHTML($employmentHtml);
        $pdf->AddPage('P','','','','',15,15,0,10,0,0);
        $pdf->WriteHTML($staffDetailsHtml);
        $pdf->SetHeader('');
        $pdf->AddPage('P','','','','',15,15,0,10,0,0);
        $pdf->WriteHTML($positionDescriptionHtml);
        $fileHeader='';
        $fileFooter='';
        $file='';

        $str = ($type == 'cabday_interview') ? 'Cab day' : 'Group interview';
        $log_title = $str.' draft contract is generated for the applicant :- '.$applicantId;
        $this->loges->setLogType('recruitment_applicant');
        $this->loges->setTitle($log_title);
        $this->loges->setUserId($adminId);
        $this->loges->setCreatedBy($adminId);
        $this->loges->setDescription(json_encode($reqData1));
        $this->loges->createLog();

        if(!empty($fileHeader))
            $pdf->SetHTMLHeader($fileHeader);

        if(!empty($fileFooter))
            $pdf->SetHTMLFooter($fileFooter);

        if(!empty($file))
            $pdf->WriteHTML($file);

        $fileDir = isset($contractALlowedFor[$type]['file_path']) ? $contractALlowedFor[$type]['file_path'] : GROUP_INTERVIEW_CONTRACT_PATH;

        $pdfFilePath =  $fileDir. $file_name;
        $xx = $pdf->Output($pdfFilePath, 'F');

        return $pdfFilePath;
    }

    /**
    * Generate PDF file path with local path
    * return - string (filepath)
    */
    public function generateContractForDswVicPDF($applicantId, $type, $file_name, $contractALlowedFor) {
        // load model
        $this->load->model(['Recruitment_cab_day_model', 'Recruitment_group_interview_model']);
        $info = $this->Recruitment_group_interview_model->get_draft_contract_data($applicantId);
        $data['complete_data'] = isset($info) && !empty($info) ? $info : [];
        
        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load();

        if($type=='group_interview'){
            $pdf->SetWatermarkText('DRAFT');
            $pdf->showWatermarkText = true;
        }

        $pdf->setAutoTopMargin='pad';
        $styleContent = $this->load->view('cabday_interview_contract_style_css',[],true);
        $data['type']='employment_content';
        $employmentHtml =$this->load->view('dsw_casual_vic',$data,true);
        $data['type']='staff_content';
        $staffDetailsHtml =$this->load->view('dsw_casual_vic',$data,true);
        $data['type']='position_content';
        $positionDescriptionHtml =$this->load->view('dsw_casual_vic',$data,true);
        $data['type']='header';
        $fileHeader =$this->load->view('dsw_casual_vic',$data,true);
        $data['type']='footer';
        $fileFooter = $this->load->view('dsw_casual_vic',$data,true);
        $logoUrl = base_url('assets/img/oncall_logo_multiple_color.jpg');

        $pdf->defaultfooterline = 0;
        $pdf->defaultheaderline = 0;
        $pdf->SetHeader('');
        $pdf->AddPage('P','','','','',15,15,0,10,0,0);
        $pdf->SetFooter($fileFooter);
        $pdf->WriteHTML($styleContent);
        $pdf->WriteHTML($employmentHtml);
        $pdf->AddPage('P','','','','',15,15,0,10,0,0);
        $pdf->WriteHTML($staffDetailsHtml);
        $pdf->SetHeader('');
        $pdf->AddPage('P','','','','',15,15,0,10,0,0);
        $pdf->WriteHTML($positionDescriptionHtml);
        $fileHeader='';
        $fileFooter='';
        $file='';

        $str = ($type == 'cabday_interview') ? 'Cab day' : 'Group interview';
        $log_title = $str.' draft contract is generated for the applicant :- '.$applicantId;
        $this->loges->setLogType('recruitment_applicant');
        $this->loges->setTitle($log_title);
        $this->loges->setUserId($adminId);
        $this->loges->setCreatedBy($adminId);
        $this->loges->setDescription(json_encode($reqData1));
        $this->loges->createLog();

        if(!empty($fileHeader))
            $pdf->SetHTMLHeader($fileHeader);

        if(!empty($fileFooter))
            $pdf->SetHTMLFooter($fileFooter);

        if(!empty($file))
            $pdf->WriteHTML($file);

        $fileDir = isset($contractALlowedFor[$type]['file_path']) ? $contractALlowedFor[$type]['file_path'] : GROUP_INTERVIEW_CONTRACT_PATH;

        $pdfFilePath =  $fileDir. $file_name;
        $xx = $pdf->Output($pdfFilePath, 'F');

        return $pdfFilePath;
    }


    /**
    * Generate PDF file path with local path
    * return - string (filepath)
    */
    public function generateContractForDswQldPDF($applicantId, $type, $file_name, $contractALlowedFor) {
        // load model
        $this->load->model(['Recruitment_cab_day_model', 'Recruitment_group_interview_model']);
        $info = $this->Recruitment_group_interview_model->get_draft_contract_data($applicantId);

        $data['complete_data'] = isset($info) && !empty($info) ? $info : [];

        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load();

        if($type=='group_interview'){
            $pdf->SetWatermarkText('DRAFT');
            $pdf->showWatermarkText = true;
        }

        $pdf->setAutoTopMargin='pad';
        $styleContent = $this->load->view('cabday_interview_contract_style_css',[],true);
        $data['type']='employment_content';
        $employmentHtml =$this->load->view('dsw_casual_qld',$data,true);
        $data['type']='staff_content';
        $staffDetailsHtml =$this->load->view('dsw_casual_qld',$data,true);
        $data['type']='position_content';
        $positionDescriptionHtml =$this->load->view('dsw_casual_qld',$data,true);
        $data['type']='header';
        $fileHeader =$this->load->view('dsw_casual_qld',$data,true);
        $data['type']='footer';
        $fileFooter = $this->load->view('dsw_casual_qld',$data,true);
        $logoUrl = base_url('assets/img/oncall_logo_multiple_color.jpg');

        $pdf->defaultfooterline = 0;
        $pdf->defaultheaderline = 0;
        $pdf->SetHeader('');
        $pdf->AddPage('P','','','','',15,15,0,10,0,0);
        $pdf->SetFooter($fileFooter);
        $pdf->WriteHTML($styleContent);
        $pdf->WriteHTML($employmentHtml);
        $pdf->AddPage('P','','','','',15,15,0,10,0,0);
        $pdf->WriteHTML($staffDetailsHtml);
        $pdf->SetHeader('');
        $pdf->AddPage('P','','','','',15,15,0,10,0,0);
        $pdf->WriteHTML($positionDescriptionHtml);
        $fileHeader='';
        $fileFooter='';
        $file='';

        $str = ($type == 'cabday_interview') ? 'Cab day' : 'Group interview';
        $log_title = $str.' draft contract is generated for the applicant :- '.$applicantId;
        $this->loges->setLogType('recruitment_applicant');
        $this->loges->setTitle($log_title);
        $this->loges->setUserId($adminId);
        $this->loges->setCreatedBy($adminId);
        $this->loges->setDescription(json_encode($reqData1));
        $this->loges->createLog();

        if(!empty($fileHeader))
            $pdf->SetHTMLHeader($fileHeader);

        if(!empty($fileFooter))
            $pdf->SetHTMLFooter($fileFooter);

        if(!empty($file))
            $pdf->WriteHTML($file);

        $fileDir = isset($contractALlowedFor[$type]['file_path']) ? $contractALlowedFor[$type]['file_path'] : GROUP_INTERVIEW_CONTRACT_PATH;

        $pdfFilePath =  $fileDir. $file_name;
        $xx = $pdf->Output($pdfFilePath, 'F');

        return $pdfFilePath;
    }

    /**
    * Generate PDF file path with local path for NSW
    * return - string (filepath)
    */
    public function generateContractForDswNswPDF($applicantId, $type, $file_name, $contractALlowedFor) {
        // load model
        $this->load->model(['Recruitment_cab_day_model', 'Recruitment_group_interview_model']);
        $info = $this->Recruitment_group_interview_model->get_draft_contract_data($applicantId);

        $data['complete_data'] = isset($info) && !empty($info) ? $info : [];

        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load();

        if($type=='group_interview'){
            $pdf->SetWatermarkText('DRAFT');
            $pdf->showWatermarkText = true;
        }

        $pdf->setAutoTopMargin='pad';
        $styleContent = $this->load->view('cabday_interview_contract_style_css',[],true);
        $data['type']='employment_content';
        $employmentHtml =$this->load->view('dsw_casual_nsw',$data,true);
        $data['type']='staff_content';
        $staffDetailsHtml =$this->load->view('dsw_casual_nsw',$data,true);
        $data['type']='position_content';
        $positionDescriptionHtml =$this->load->view('dsw_casual_nsw',$data,true);
        $data['type']='header';
        $fileHeader =$this->load->view('dsw_casual_nsw',$data,true);
        $data['type']='footer';
        $fileFooter = $this->load->view('dsw_casual_nsw',$data,true);
        $logoUrl = base_url('assets/img/oncall_logo_multiple_color.jpg');

        $pdf->defaultfooterline = 0;
        $pdf->defaultheaderline = 0;
        $pdf->SetHeader('');
        $pdf->AddPage('P','','','','',15,15,0,10,0,0);
        $pdf->SetFooter($fileFooter);
        $pdf->WriteHTML($styleContent);
        $pdf->WriteHTML($employmentHtml);
        $pdf->AddPage('P','','','','',15,15,0,10,0,0);
        $pdf->WriteHTML($staffDetailsHtml);
        $pdf->SetHeader('');
        $pdf->AddPage('P','','','','',15,15,0,10,0,0);
        $pdf->WriteHTML($positionDescriptionHtml);
        $fileHeader='';
        $fileFooter='';
        $file='';

        $str = ($type == 'cabday_interview') ? 'Cab day' : 'Group interview';
        $log_title = $str.' draft contract is generated for the applicant :- '.$applicantId;
        $this->loges->setLogType('recruitment_applicant');
        $this->loges->setTitle($log_title);
        $this->loges->setUserId($adminId);
        $this->loges->setCreatedBy($adminId);
        $this->loges->setDescription(json_encode($reqData1));
        $this->loges->createLog();

        if(!empty($fileHeader))
            $pdf->SetHTMLHeader($fileHeader);

        if(!empty($fileFooter))
            $pdf->SetHTMLFooter($fileFooter);

        if(!empty($file))
            $pdf->WriteHTML($file);

        $fileDir = isset($contractALlowedFor[$type]['file_path']) ? $contractALlowedFor[$type]['file_path'] : GROUP_INTERVIEW_CONTRACT_PATH;

        $pdfFilePath =  $fileDir. $file_name;
        $xx = $pdf->Output($pdfFilePath, 'F');

        return $pdfFilePath;
    }

    /**
     * Generate Docusign contract
     * return array - docusing response
     */
    public function generateAndSendContract($applicantId, $adminId, $pdfFilePath, $reqData) {
        $contractFileNameWithoutextension = "OGA Employment Contract";

        if($reqData->doc_type_name==DSW_CASUAL_VIC){
            $contractFileNameWithoutextension = DSW_VIC_FILE_NAME; 
        }else if($reqData->doc_type_name==DSW_CASUAL_QLD){
            $contractFileNameWithoutextension = DSW_QLD_FILE_NAME;
        }else if($reqData->doc_type_name==PAYROLL_DOCUMENTS){           
            $contractFileNameWithoutextension=PAYROLL_DOCUMENTS_FILE_NAME;
        }else if($reqData->doc_type_name==DSW_CASUAL_NSW){
            $contractFileNameWithoutextension = DSW_NSW_FILE_NAME;
        }
        
        // load model
        $this->load->model(['Recruitment_cab_day_model', 'Recruitment_group_interview_model']);
        $info = $this->Recruitment_group_interview_model->get_draft_contract_data($applicantId);

        $message_content = $this->load->view("docusign_template_request_sign", $info, true);

        $user_data = array();
        $user_data['firstname'] = $info['firstname'] ?? '';
        $user_data['lastname'] = $info['lastname'] ?? '';
        // Load modal
        $this->load->model("recruitment/Recruitment_task_action");
        $this->load->model('imail/Automatic_email_model');
        // Get admin name
        $admin_name = $this->Recruitment_task_action->get_admin_firstname_lastname($adminId);
        // Get job title
        $user_data["job_title"] = $this->Recruitment_task_action->get_application_job_title($applicantId);
        // Set dynamic data content
        $user_data['admin_firstname'] = $admin_name['firstname'] ?? '';
        $user_data['admin_lastname'] = $admin_name['lastname'] ?? '';
        $user_data['firstname'] = $info['firstname'];
        $user_data['lastname'] = $info['lastname'];
        $user_data['email'] = $info['email'];
        // Get template content
        $template_data = $this->Automatic_email_model->get_template_content_details('employment_contract_cabday');
        // Automatic email loaded to replace the content with dynamic data
        require_once APPPATH . 'Classes/Automatic_email.php';
        $obj = new Automatic_email();
        $obj->setDynamic_data($user_data);
        // Email content
        $dataHtml = array();
        // Get content from views
        $email_content =$this->load->view('docusign_template_request_sign_v1', $dataHtml, true);
        $email_content = trim($email_content);
        $email_content = $obj->replace_msg_content_docusign($email_content);
        $email_subject = 'OGA Employment Contract';
        if (isset($template_data) && isset($template_data['content'])) {
            $email_content = $template_data['content'];
            $email_content = $obj->replace_msg_content_docusign($email_content);
        }
        if (isset($template_data) && isset($template_data['subject'])) {
            $email_subject = $template_data['subject'];
        }
        


        $email = (array) $reqData;
      
        // Email subject
        if (isset($email) && isset($email['subject']) && $email['subject'] !='') {
            $email_subject = $email['subject'];
        }
        // Email body content
        if (isset($email) && isset($email['email_content']) && $email['email_content'] !='') {
            $email_content = $email['email_content'];
            $email_content = $obj->replace_msg_content_docusign($email_content);
            $msg_content = str_replace("<p><br /></p>", "", $email_content);
            $msg_content = str_replace("<p><br></p>", "<p></p>", $msg_content);
            $replace_content = preg_replace("/<p[^>]*?>/", "", $msg_content);
            $email_content = str_replace("</p>", "", $replace_content);
        }

        // signer recipient id
        $recipient_id = 1;
        
        // Add cc user
        $cc_user = [];
        if (isset($email) && isset($email['cc_email']) && $email['cc_email'] !='') {
            $cc_temp = [];
            // get email details
            $email_cc = $email['cc_email'];
            if(isset($email_cc) && empty($email_cc) == false) {
                foreach($email_cc as $cc_index => $email_temp) {
                    $cc_recipient_id = $cc_index + 1;
                    $cc_temp['name'] = strstr($email_temp, '@', true);                
                    $cc_temp['email'] = $email_temp;
                    $cc_temp['recipient_id'] = $recipient_id + $cc_recipient_id;
                    $cc_temp['routing_order'] = 1;
                    $cc_user[] = $cc_temp;        
                }
            }
        }
       
        $position = [];


        $this->load->model("recruitment/Super_annunation_form_fields");
        if($reqData->doc_type_name==DSW_CASUAL_VIC){
            $position = [
                ['position_x' => 140, 'position_y' => 182, 'document_id' => 1, 'page_number' => 8, 'recipient_id' => 1],
                ['position_x' => 350, 'position_y' => 395, 'document_id' => 1, 'page_number' => 10, 'recipient_id' => 1],
                ['position_x' => 70, 'position_y' => 310, 'document_id' => 1, 'page_number' => 12, 'recipient_id' => 1],
                ['position_x' => 100, 'position_y' => 207, 'document_id' => 1, 'page_number' => 25, 'recipient_id' => 1],
                ['position_x' => 140, 'position_y' => 578, 'document_id' => 1, 'page_number' => 27, 'recipient_id' => 1],
            ];
           
        }else if($reqData->doc_type_name==DSW_CASUAL_QLD){
            $position = [
                ['position_x' => 125, 'position_y' => 640, 'document_id' => 1, 'page_number' => 7, 'recipient_id' => 1],
                ['position_x' => 350, 'position_y' => 390, 'document_id' => 1, 'page_number' => 9, 'recipient_id' => 1],
                ['position_x' => 90, 'position_y' => 310, 'document_id' => 1, 'page_number' => 11, 'recipient_id' => 1],
                ['position_x' => 100, 'position_y' => 207, 'document_id' => 1, 'page_number' => 24, 'recipient_id' => 1],
                ['position_x' => 140, 'position_y' => 578, 'document_id' => 1, 'page_number' => 26, 'recipient_id' => 1],
            ];
           
        }else if($reqData->doc_type_name==DSW_CASUAL_NSW){
            $position = [
                ['position_x' => 140, 'position_y' => 640, 'document_id' => 1, 'page_number' => 7, 'recipient_id' => 1],
                ['position_x' => 350, 'position_y' => 390, 'document_id' => 1, 'page_number' => 9, 'recipient_id' => 1],
                ['position_x' => 90, 'position_y' => 310, 'document_id' => 1, 'page_number' => 11, 'recipient_id' => 1],
                ['position_x' => 100, 'position_y' => 200, 'document_id' => 1, 'page_number' => 24, 'recipient_id' => 1],
                ['position_x' => 140, 'position_y' => 570, 'document_id' => 1, 'page_number' => 26, 'recipient_id' => 1],
            ];
           
        }else if($reqData->doc_type_name=='Employment Contract'){
            $position =  [
                ['position_x' => 110, 'position_y' => 187, 'document_id' => 1, 'page_number' => 7, 'recipient_id' => 1],
                ['position_x' => 290, 'position_y' => 395, 'document_id' => 1, 'page_number' => 9, 'recipient_id' => 1],
                ['position_x' => 60, 'position_y' => 312, 'document_id' => 1, 'page_number' => 11, 'recipient_id' => 1],
                ['position_x' => 60, 'position_y' => 600, 'document_id' => 1, 'page_number' => 24, 'recipient_id' => 1]
            ];
        }
        else if($reqData->doc_type_name==PAYROLL_DOCUMENTS){
            $position = [
                ['position_x' => 161, 'position_y' => 500, 'document_id' => 1, 'page_number' => 2, 'recipient_id' => 1],
                ['position_x' => 335, 'position_y' => 370, 'document_id' => 1, 'page_number' => 8, 'recipient_id' => 1],
               /* Employer form
                ['position_x' => 156, 'position_y' => 178, 'document_id' => 1, 'page_number' => 7, 'recipient_id' => 1] */
            ];
        }


        $position_radio = [];
        $position_checkbox=[];

        if($reqData->doc_type_name==DSW_CASUAL_VIC){
            $position_radio = [
                [
                    'document_id'=>'1',
                    'group_name'=>'RadioGroupTest',
                    'shared'=>true,
                    'radios'=>[
                        [
                            'page_number'=>11,
                            'required'=>'true',
                            'value'=>'Y',
                            'x_position'=>'43',
                            'y_position'=>'452',
                        ],
                        [
                            'page_number'=>11,
                            'required'=>'true',
                            'value'=>'N',
                            'x_position'=>'43',
                            'y_position'=>'477'
                        ]

                    ],
                    'recipient_id'=>'1',
                    'require_initial_on_shared_change'=>'true',
                    'require_all'=>true
                ],
                [
                    'document_id'=>'1',
                    'group_name'=>'VaccinatedConstent',
                    'shared'=>true,
                    'radios'=>[
                        [
                            'page_number'=>27,
                            'required'=>'true',
                            'value'=>'Y',
                            'x_position'=>'37',
                            'y_position'=>'553'
                        ],
                        [
                            'page_number'=>27,
                            'required'=>'true',
                            'value'=>'N',
                            'x_position'=>'37',
                            'y_position'=>'578'
                        ],

                    ],
                    'recipient_id'=>'1',
                    'require_initial_on_shared_change'=>'true',
                    'require_all'=>true
                ]
                ];

                $position_checkbox=[                  

                        ['document_id' => 1, 'page_number' => 27,'height'=>50,'width'=>50,'required'=>true,
                        'position_x' => '37', 'position_y' => '170',  'recipient_id' => 1,'tab_label'=>'field@906'],

                        ['document_id' => 1, 'page_number' => 27,'height'=>50,'width'=>50,'required'=>true,
                        'position_x' => '37', 'position_y' => '195',  'recipient_id' => 1,'tab_label'=>'field@907'],
                    
                        ['document_id' => 1, 'page_number' => 27,'height'=>50,'width'=>50,'required'=>true,
                        'position_x' => '37', 'position_y' => '256',  'recipient_id' => 1,'tab_label'=>'field@908'],

                        ['document_id' => 1, 'page_number' => 27,'height'=>50,'width'=>50,'required'=>true,
                        'position_x' => '37', 'position_y' => '282',  'recipient_id' => 1,'tab_label'=>'field@909'],                    
                    
                        ['document_id' => 1, 'page_number' => 27,'height'=>50,'width'=>50,'required'=>true,
                        'position_x' => '37', 'position_y' => '343',  'recipient_id' => 1,'tab_label'=>'field@910'],

                        ['document_id' => 1, 'page_number' => 27,'height'=>50,'width'=>50,'required'=>true,
                        'position_x' => '37', 'position_y' => '367',  'recipient_id' => 1,'tab_label'=>'field@911'],
                    

                    ['document_id' => 1, 'page_number' => 27,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '440', 'recipient_id' => 1,'tab_label'=>'field@903'],

                    ['document_id' => 1, 'page_number' => 27,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '465', 'recipient_id' => 1,'tab_label'=>'field@904'],

                    ['document_id' => 1, 'page_number' => 27,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '490', 'recipient_id' => 1,'tab_label'=>'field@905'],
        
        
                ];
           
        }else if($reqData->doc_type_name==DSW_CASUAL_QLD){
            $position_radio = [
                [
                    'document_id'=>'1',
                    'group_name'=>'RadioGroupTest',
                    'shared'=>true,
                    'radios'=>[
                        [
                            'page_number'=>10,
                            'required'=>'true',
                            'value'=>'Y',
                            'x_position'=>'43',
                            'y_position'=>'452',
                        ],
                        [
                            'page_number'=>10,
                            'required'=>'true',
                            'value'=>'N',
                            'x_position'=>'43',
                            'y_position'=>'477'
                        ]
                    ],
                    'recipient_id'=>'1',
                    'require_initial_on_shared_change'=>'true',
                    'require_all'=>true
                ],
                [
                    'document_id'=>'1',
                    'group_name'=>'VaccinatedConstent',
                    'shared'=>true,
                    'radios'=>[
                        [
                            'page_number'=>26,
                            'required'=>'true',
                            'value'=>'Y',
                            'x_position'=>'37',
                            'y_position'=>'553'
                        ],
                        [
                            'page_number'=>26,
                            'required'=>'true',
                            'value'=>'N',
                            'x_position'=>'37',
                            'y_position'=>'578'
                        ],

                    ],
                    'recipient_id'=>'1',
                    'require_initial_on_shared_change'=>'true',
                    'require_all'=>true
                ]
                ];

                $position_checkbox=[

                    ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '170',  'recipient_id' => 1,'tab_label'=>'field@906'],

                    ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '195',  'recipient_id' => 1,'tab_label'=>'field@907'],
                
                    ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '256',  'recipient_id' => 1,'tab_label'=>'field@908'],

                    ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '280',  'recipient_id' => 1,'tab_label'=>'field@909'],                
                
                    ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '341',  'recipient_id' => 1,'tab_label'=>'field@910'],

                    ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '367',  'recipient_id' => 1,'tab_label'=>'field@911'],
                    

                    ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '440', 'recipient_id' => 1,'tab_label'=>'field@903'],

                    ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '465', 'recipient_id' => 1,'tab_label'=>'field@904'],

                    ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '490', 'recipient_id' => 1,'tab_label'=>'field@905'],
        
        
                ];
           
        }else if($reqData->doc_type_name==DSW_CASUAL_NSW){
            $position_radio = [
                [
                    'document_id'=>'1',
                    'group_name'=>'RadioGroupTest',
                    'shared'=>true,
                    'radios'=>[
                        [
                            'page_number'=>10,
                            'required'=>'true',
                            'value'=>'Y',
                            'x_position'=>'44',
                            'y_position'=>'440',
                        ],
                        [
                            'page_number'=>10,
                            'required'=>'true',
                            'value'=>'N',
                            'x_position'=>'44',
                            'y_position'=>'465'
                        ]
                    ],
                    'recipient_id'=>'1',
                    'require_initial_on_shared_change'=>'true',
                    'require_all'=>true
                ],
                [
                    'document_id'=>'1',
                    'group_name'=>'VaccinatedConstent',
                    'shared'=>true,
                    'radios'=>[
                        [
                            'page_number'=>26,
                            'required'=>'true',
                            'value'=>'Y',
                            'x_position'=>'37',
                            'y_position'=>'540'
                        ],
                        [
                            'page_number'=>26,
                            'required'=>'true',
                            'value'=>'N',
                            'x_position'=>'37',
                            'y_position'=>'565'
                        ],

                    ],
                    'recipient_id'=>'1',
                    'require_initial_on_shared_change'=>'true',
                    'require_all'=>true
                ]
                ];

                $position_checkbox=[
                    // ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    // 'position_x' => '37', 'position_y' => '145',  'recipient_id' => 1,'tab_label'=>'field@900'],

                        ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                        'position_x' => '37', 'position_y' => '170',  'recipient_id' => 1,'tab_label'=>'field@906'],

                        ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                        'position_x' => '37', 'position_y' => '195',  'recipient_id' => 1,'tab_label'=>'field@907'],

                    // ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    // 'position_x' => '37', 'position_y' => '195', 'recipient_id' => 1,'tab_label'=>'field@901'],
                    
                        ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                        'position_x' => '37', 'position_y' => '244',  'recipient_id' => 1,'tab_label'=>'field@908'],

                        ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                        'position_x' => '37', 'position_y' => '269',  'recipient_id' => 1,'tab_label'=>'field@909'],
                    
                    // ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    // 'position_x' => '37', 'position_y' => '245', 'recipient_id' => 1,'tab_label'=>'field@902'],
                    
                        ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                        'position_x' => '37', 'position_y' => '330',  'recipient_id' => 1,'tab_label'=>'field@910'],

                        ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                        'position_x' => '37', 'position_y' => '355',  'recipient_id' => 1,'tab_label'=>'field@911'],
                    

                    ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '430', 'recipient_id' => 1,'tab_label'=>'field@903'],

                    ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '455', 'recipient_id' => 1,'tab_label'=>'field@904'],

                    ['document_id' => 1, 'page_number' => 26,'height'=>50,'width'=>50,'required'=>true,
                    'position_x' => '37', 'position_y' => '480', 'recipient_id' => 1,'tab_label'=>'field@905'],
        
        
                ];
           
        }else if($reqData->doc_type_name=='Employment Contract'){
            $position_radio =  [
                [
                    'document_id'=>'1',
                    'group_name'=>'RadioGroupTest',
                    'shared'=>true,
                    'radios'=>[
                        [
                            'page_number'=>10,
                            'required'=>'true',
                            'value'=>'Y',
                            'x_position'=>'58',
                            'y_position'=>'447',
                        ],
                        [
                            'page_number'=>10,
                            'required'=>'true',
                            'value'=>'N',
                            'x_position'=>'58',
                            'y_position'=>'478'
                        ]
                    ],
                    'recipient_id'=>'1',
                    'require_initial_on_shared_change'=>'true',
                    'require_all'=>true
                ]
                ];
        }
    
        
        $position_text = [];
        if($reqData->doc_type_name=='Employment Contract'){
            $position_text = [
                    ['height'=>288,'width'=>580,'conditional_parent_value'=>'Y','conditional_parent_label'=>'RadioGroupTest','required'=>false,'max_length'=>1000,'tab_order'=>3,'position_x' => 40, 'position_y' => 65, 'document_id' => 1, 'page_number' => 11, 'recipient_id' => 1]
            ];
        }

        if($reqData->doc_type_name==DSW_CASUAL_VIC ){
            $position_text = [
                    ['height'=>288,'width'=>581,'conditional_parent_value'=>'Y','conditional_parent_label'=>'RadioGroupTest','required'=>false,'max_length'=>1000,'tab_order'=>3,'position_x' => 40, 'position_y' => 68, 'document_id' => 1, 'page_number' => 12, 'recipient_id' => 1]
            ];
        }
        
        if($reqData->doc_type_name==DSW_CASUAL_QLD ){
            $position_text = [
                    ['height'=>288,'width'=>581,'conditional_parent_value'=>'Y','conditional_parent_label'=>'RadioGroupTest','required'=>false,'max_length'=>1000,'tab_order'=>3,'position_x' => 40, 'position_y' => 68, 'document_id' => 1, 'page_number' => 11, 'recipient_id' => 1]
            ];
        }
        if($reqData->doc_type_name==DSW_CASUAL_NSW){
            $position_text = [
                    ['height'=>288,'width'=>581,'conditional_parent_value'=>'Y','conditional_parent_label'=>'RadioGroupTest','required'=>false,'max_length'=>1000,'tab_order'=>3,'position_x' => 40, 'position_y' => 68, 'document_id' => 1, 'page_number' => 11, 'recipient_id' => 1]
            ];
        }

        $position_number=[];
        if($reqData->doc_type_name==PAYROLL_DOCUMENTS){
            $position_radio = $this->Super_annunation_form_fields->get_superannunation_radiobox_fields(); 
             $position_checkbox = $this->Super_annunation_form_fields->get_superannunation_checkbox_fields();
             $position_number   = $this->Super_annunation_form_fields->get_superannunation_number_fields();
             $position_text     = $this->Super_annunation_form_fields->get_superannunation_text_fields();
          }
        // base url of callback
      $base_url = base_url(DS_STAGING_WEBHOOK_URL_CABDAY);
    //   $base_url = "https://localhost/recruitment/RecruitmentCabDayInterview/callback_cabday_docusign";
        $data = [
            'userdetails' => [
                'name' => (isset($info['applicant_name']) ? $info['applicant_name'] : 'Applicant name'),
                'email_subject' => $email_subject,
                'email_message' => $email_content,
                'email' => $info['email'],
                'recipient_id' => $recipient_id,
                'routing_order' => 2,
            ],
            'cc_user' => $cc_user,
            'document' => ['doc_id' => 1 , 'doc_name' => $contractFileNameWithoutextension,  'doc_path' => $pdfFilePath , 'web_hook_url'=>$base_url],
            'position' => $position,
            'position_text' => $position_text,
            'position_radio'=>$position_radio,
            'position_checkbox'=>$position_checkbox,
            'position_number'=>$position_number
        ];
        $this->load->library('DocuSignEnvelope'); 
        $statusDocuSign = $this->docusignenvelope->CreateEnvelope($data);
        return $statusDocuSign;
        
    }
}
