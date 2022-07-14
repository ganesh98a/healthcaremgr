<?php
/** Files holds the document type related operation */
defined('BASEPATH') OR exit('No direct script access allowed');
//class Master extends MX_Controller
class DocumentTypeMigration extends MX_Controller {

  function __construct() {
    parent::__construct();
    $this->load->helper('i_pad');
  }

  public function document_type_migration()
  {
    //Allow to hit from only authenticated request
    api_request_handler();

    //Step 1 - Migrate reference data into document type table
    $this->migrate_data_from_ref_to_document_type();

    //Step 2 - update related to table for both requirment and member module
    $this->update_related_to_table();

    //Step 3 - Document type migration
    $this->update_recruitment_job_table();

  }
  public function migrate_data_from_ref_to_document_type()
  {
    $doc_title = $this->get_document_title();

    $this->db->select(["ref.id", "ref.type", "ref.display_name", "ref.key_name", "ref.created",
    "ref.updated", "archive"]);
    $this->db->from(TBL_PREFIX . "references as ref");

    $this->db->where_in("ref.type", [4, 5]);

    $req_data = [];
    $datas = $this->db->get()->result_array();

    if(!count($datas)) {
      return;
    }

    foreach($datas as $data) {
      $cat_id = ($data['type'] == 5) ? 1 : 2;

      //Update the document Id If already added in documents table
      if(in_array($data['display_name'], $doc_title)) {
        //Update Auto Generated Document flag value as 1
        $this->db->where("title", $data['display_name']);
        $this->db->update('tbl_document_type', ['doc_category_id' => $cat_id]);
        continue;
      }

      $req_data['title'] = $data['display_name'];
      $req_data['issue_date_mandatory'] = $req_data['expire_date_mandatory'] =
      $req_data['expire_date_mandatory'] = $req_data['system_gen_flag'] = 0;
      $req_data['active'] = 1;
      $req_data['archive'] = $data['archive'];
      $req_data['doc_category_id'] = $cat_id;
      $req_data['created_at'] = strtotime($data['created']);
      $req_data['updated_at'] = strtotime($data['updated']);
      $record[] = $req_data;
    }

    if(isset($record)) {
      $this->basic_model->insert_records('document_type', $record, TRUE);

      //Update Auto Generated Document flag value as 1
      $this->db->where_in("title", ['CAB Certificate', 'Other']);
      $this->db->update('tbl_document_type', ['system_gen_flag' => 1]);
    }
  }

  public function get_document_title() {
    $this->db->select("title");
    $this->db->from(TBL_PREFIX . "document_type");
    $this->db->where("archive", 0);
    $this->db->group_by("id, doc_category_id");

    $datas = $this->db->get()->result_array();
    $title = [];
    if(isset($datas)) {
      foreach($datas as $data) {
        $title[] = $data['title'];
      }
    }
    return $title;
  }

  public function update_related_to_table() {
    $this->db->select("doc_type_id");
    $this->db->from(TBL_PREFIX . "document_type_related");
    $sub_query = $this->db->get_compiled_select();

    $this->db->select(["type.id", "type.archive"]);
    $this->db->from(TBL_PREFIX . "document_type as type");
    $this->db->join(TBL_PREFIX . "document_type_related as rel", "type.id = rel.doc_type_id", "left");
    $this->db->where("not exists (" . $sub_query . ")", NULL, false);
    $datas = $this->db->get()->result_array();

    if(!count($datas)) {
      return;
    }
    $req_data = [];
    foreach($datas as $data) {
      //For Recruitment
      $req_data['doc_type_id'] = $data['id'];
      $req_data['related_to'] = 1;
      $req_data['archive'] = $data['archive'];
      $req_data['created_at'] = time();
      $req_data['updated_at'] = time();
      $record[] = $req_data;

       //For Member
       $req_data['doc_type_id'] = $data['id'];
       $req_data['related_to'] = 2;
       $req_data['archive'] = $data['archive'];
       $req_data['created_at'] = time();
       $req_data['updated_at'] = time();
       $record[] = $req_data;
    }

    if(isset($record)) {
      $this->basic_model->insert_records('document_type_related', $record, TRUE);
    }

  }

  public function add_related_to_table_data() {
    //Allow to hit from only authenticated request
     api_request_handler();

    $this->db->select("doc_type_id as re");
    $this->db->from(TBL_PREFIX . "document_type_related");
    $sub_query = $this->db->get_compiled_select();

    $this->db->select(["type.id", "type.archive"]);
    $this->db->from(TBL_PREFIX . "document_type as type");
    $this->db->where("type.id not in (" . $sub_query . ")", NULL, false);
    $datas = $this->db->get()->result_array();

    if(!count($datas)) {
      echo "Nothing to migrate";
      return;
    }
    $req_data = [];
    foreach($datas as $data) {
      //For Recruitment
      $req_data['doc_type_id'] = $data['id'];
      $req_data['related_to'] = 1;
      $req_data['archive'] = $data['archive'];
      $req_data['created_at'] = time();
      $req_data['updated_at'] = time();
      $record[] = $req_data;

       //For Member
       $req_data['doc_type_id'] = $data['id'];
       $req_data['related_to'] = 2;
       $req_data['archive'] = $data['archive'];
       $req_data['created_at'] = time();
       $req_data['updated_at'] = time();
       $record[] = $req_data;
    }

    if(isset($record)) {
      $this->basic_model->insert_records('document_type_related', $record, TRUE);
      echo "Migration successfully completed";
    }

  }

  public function update_recruitment_job_table() {

    $this->db->select(["ref.id as ref_id", "type.id as doc_id","ref.display_name as ref_name","type.title as title"]);
    $this->db->from(TBL_PREFIX . "recruitment_job_posted_docs as job");
    $this->db->join(TBL_PREFIX . "references as ref", "ref.id = job.requirement_docs_id", "inner");
    $this->db->join(TBL_PREFIX . "document_type as type", "type.title = ref.display_name", "inner");
    $this->db->where_in("ref.type", [4, 5]);
    $this->db->group_by("type.id, type.doc_category_id");

    $datas = $this->db->get()->result_array();

    foreach($datas as $data) {
      $this->db->where("requirement_docs_id", $data['ref_id']);
      $this->db->update(TBL_PREFIX . "recruitment_job_posted_docs", ['requirement_docs_id' => $data['doc_id']]);
    }
    echo "Migration Successfully completed!";
  }

  /**
   * Update recruitment document type status as archive in reference table
   */
  public function update_recruitment_doc_type_ref_data_as_archive() {
      //Allow to hit from only authenticated request
      api_request_handler();
      $this->db->where("key_name", "docs_required_recruitment_stages");
      $this->db->update(TBL_PREFIX . "reference_data_type", ['archive' => 1]);
      $this->db->where("key_name", "docs_required_apply_Job");
      $this->db->update(TBL_PREFIX . "reference_data_type", ['archive' => 1]);
      echo 'Reference Document Type status successfully updated';
  }


  /**
   * Update doc type for all employment contract
   */
  public function update_emplyment_doc_type() {
    //Allow to hit from only authenticated request
    api_request_handler();
    
    // get document type id
    $this->db->select(['id']);
    $this->db->from(TBL_PREFIX . 'document_type');
    $this->db->where('title', EMPLOYMENT_CONTRACT_VALUE);

    $query = $this->db->get();
    $row = $query->num_rows() > 0 ? $query->row() : NULL;

    if (isset($row) == true && empty($row) == false) {
      $contract_type_id = $row->id;
      $this->db->where("draft_contract_type", "2");
      $this->db->update(TBL_PREFIX . "document_attachment", ['doc_type_id' => $contract_type_id]);
      echo 'Updated SuccessFully';
    } else {
      echo 'Something went wrong!';
    }
    exit;
  }

  /**
   * Update all employment contract table to document table
   */
  public function update_emplyment_contract_to_document_table() {
    //Allow to hit from only authenticated request
    api_request_handler();
    
    // get document type id
    $this->db->select(['id']);
    $this->db->from(TBL_PREFIX . 'document_type');
    $this->db->where('title', EMPLOYMENT_CONTRACT_VALUE);

    $query = $this->db->get();
    $row = $query->num_rows() > 0 ? $query->row() : NULL;
    $contract_type_id = 0;
    if (isset($row) == true && empty($row) == false) {
      $contract_type_id = $row->id;
    } else {
      echo 'Document type is required';
      exit;
    }
    // get document type id
    $this->db->select(['*']);
    $this->db->from(TBL_PREFIX . 'recruitment_applicant_contract');
    $this->db->where('signed_status', 0);

    $query = $this->db->get();
    $row = $query->num_rows() > 0 ? $query->result() : NULL;
    
    foreach($row as $contract) {
      $app_att['application_id'] = $contract->application_id ?? 0;
      $app_att['applicant_id'] = $contract->applicant_id;
      $app_att['draft_contract_type'] = 2;
      $app_att['stage'] = 0;
      $doc_att['is_main_stage_label'] = $doc_att['uploaded_by_applicant'] ='';
      $doc_att['member_move_archive'] = 0;
      $app_att['related_to'] = 1;
      $app_att['doc_type_id'] = $contract_type_id;
      $app_att['document_status'] = 1;
      $app_att['entity_id'] = $contract->applicant_id;
      $app_att['entity_type'] = 1;
      $app_att['archive'] = 0;
      $app_att['created_by'] = '';
      $app_att['created_at'] = $contract->created;
      $app_att['updated_at'] = $contract->updated;
      $app_att['task_applicant_id'] = $contract->task_applicant_id;

      // insert data
      $this->db->insert(TBL_PREFIX . 'document_attachment', $app_att);

      //Get the last inserted id
      $insert_id = $this->db->insert_id();

      $doc_prop_att['doc_id'] = $insert_id;
      $doc_prop_att['file_type'] = 'application/pdf';
      $doc_prop_att['file_ext'] = 'pdf';
      $doc_prop_att['file_name'] = $contract->unsigned_file;
      $doc_prop_att['file_size'] = 0;
      $doc_prop_att['raw_name'] = $contract->unsigned_file;
      $doc_prop_att['aws_uploaded_flag'] = 0;
      $doc_prop_att['aws_response'] = '';
      $doc_prop_att['aws_object_uri'] = '';
      $doc_prop_att['aws_file_version_id'] = '';
      $doc_prop_att['file_path'] = '';
      $doc_prop_att['archive'] = 0;
      $doc_prop_att['sent_by'] = '';
      $doc_prop_att['created_by'] = '';
      $doc_prop_att['envelope_id'] = $contract->envelope_id;;
      $doc_prop_att['unsigned_file'] = $contract->unsigned_file;;
      $doc_prop_att['created_at'] = $contract->created;

      $this->db->insert(TBL_PREFIX . 'document_attachment_property', $doc_prop_att, true);

      $insert_perperty_id = $this->db->insert_id();
    }
  }

  /**
   * Update all employment contract table to document table envelope id
   */
  public function update_emplyment_contract_envelope_id() {
    //Allow to hit from only authenticated request
    api_request_handler();
    
    // get document type id
    $this->db->select(['*']);
    $this->db->from(TBL_PREFIX . 'recruitment_applicant_contract');
    $this->db->where('signed_status', 1);

    $query = $this->db->get();
    $row = $query->num_rows() > 0 ? $query->result() : NULL;
    
    $result = [];
    foreach($row as $con_ind => $contract) {
      $recruitment_task_applicant_id = $contract->task_applicant_id;

      $application_id = $contract->application_id;
      if (($application_id == '' || $application_id == 0) && ($recruitment_task_applicant_id != 0 && $recruitment_task_applicant_id != NULL)) {
        $application_id = $this->get_application_id_by_task_applicant_id($recruitment_task_applicant_id);
      }

      $applicant_id = $contract->applicant_id;
      if (($applicant_id == '' || $applicant_id == 0) && ($recruitment_task_applicant_id != 0 && $recruitment_task_applicant_id != NULL)) {
        $applicant_id = $this->get_applicant_id_by_task_id($recruitment_task_applicant_id);
      } 
      

      // get document type id
      $this->db->select(['tda.*', 'tdap.*', 'tdap.id as prop_id', 'tda.id as attachment_id']);
      $this->db->from(TBL_PREFIX . 'document_attachment as tda');
      $this->db->join(TBL_PREFIX . 'document_attachment_property as tdap', 'tda.id = tdap.doc_id', 'inner');
      $this->db->where('tda.draft_contract_type', 2);
      $this->db->where('tdap.envelope_id IS NULL');

      if ($applicant_id != '' && $applicant_id != NULL) {
        $this->db->where('tda.applicant_id', $applicant_id);
      }
      if ($application_id != '' && $application_id != NULL) {
        $this->db->where('tda.application_id', $application_id);
      }
      
      $this->db->limit(1);
      $query = $this->db->get();
      $row = $query->num_rows() > 0 ? $query->result_array() : NULL;
      $result[$con_ind]['applicant_id'] = $applicant_id;
      $result[$con_ind]['application_id'] = $application_id;
      $result[$con_ind]['contract_res'] = $contract;
      $result[$con_ind]['query_res'] = $row;
      if (isset($row) == true && empty($row) == false && isset($row[0]['attachment_id']) == true) {
        $doc_att['task_applicant_id'] = $contract->task_applicant_id;
        $this->db->where('id', $row[0]['attachment_id']);
        $this->db->update(TBL_PREFIX . 'document_attachment', $doc_att);
      }

      if (isset($row) == true && empty($row) == false && isset($row[0]['prop_id']) == true) {
        $doc_prop_att['envelope_id'] = $contract->envelope_id;
        $doc_prop_att['unsigned_file'] = $contract->unsigned_file;
        $doc_prop_att['created_at'] = $contract->created;
        $doc_prop_att['signed_status'] = 1;
        $doc_prop_att['signed_date'] = $contract->signed_date;
        $this->db->where('id', $row[0]['prop_id']);
        $this->db->update(TBL_PREFIX . 'document_attachment_property', $doc_prop_att);
      }
      
    }
    echo json_encode($result);
  }

  function get_application_id_by_task_applicant_id($task_applicant_id){
    $this->db->select("rta.application_id");
    $this->db->from("tbl_recruitment_task_applicant as rta");
    $this->db->where("rta.id", $task_applicant_id);

    return $this->db->get()->row("application_id");
  }
  function get_applicant_id_by_task_id($task_applicant_id){
      $this->db->select("rta.applicant_id");
      $this->db->from("tbl_recruitment_task_applicant as rta");
      $this->db->where("rta.id", $task_applicant_id);

      return $this->db->get()->row("applicant_id");
  }

  /**
   * compare contract employment contract with docment table
   */
  public function compare_employment_contract_with_doc_by_envelope_id() {
    //Allow to hit from only authenticated request
    api_request_handler();
    
    // get document type id
    $this->db->select(['*']);
    $this->db->from(TBL_PREFIX . 'recruitment_applicant_contract');

    $query = $this->db->get();
    $row = $query->num_rows() > 0 ? $query->result() : NULL;
    
    $contract_count = $query->num_rows();

    $doc_count = 0;
    $result = [];
    $table_head = <<<HTML
      <tr>
        <td colspan="6">Contract Table</td>
        <td colspan="7">Document Table</td>
      </tr>
      <tr>
        <td>Contract ID</td>
        <td>Applicant ID</td>
        <td>Application ID</td>
        <td>Task Applicant ID</td>
        <td>Envelope ID</td>
        <td>Signed Status</td>
        <td>Attachment ID</td>
        <td>Attach Prop ID</td>
        <td>Applicant ID</td>
        <td>Application ID</td>
        <td>Task Applicant ID</td>
        <td>Envelope ID</td>
        <td>Signed Status</td>
      </tr>
HTML;
    $table_body ='';
    $envelope_count = 0;
    $miss_doc_count = 0;
    foreach($row as $con_ind => $contract) {

      $recruitment_task_applicant_id = $contract->task_applicant_id;
      $application_id = $contract->application_id;
      if (($application_id == '' || $application_id == 0) && ($recruitment_task_applicant_id != 0 && $recruitment_task_applicant_id != NULL)) {
        $application_id = $this->get_application_id_by_task_applicant_id($recruitment_task_applicant_id);
      }

      $applicant_id = $contract->applicant_id;
      if (($applicant_id == '' || $applicant_id == 0) && ($recruitment_task_applicant_id != 0 && $recruitment_task_applicant_id != NULL)) {
        $applicant_id = $this->get_applicant_id_by_task_id($recruitment_task_applicant_id);
      }

      $contract->application_id = $application_id;
      $contract->applicant_id = $applicant_id;

      // get document type id
      $this->db->select(['tda.*', 'tdap.*', 'tdap.id as prop_id', 'tda.id as attachment_id']);
      $this->db->from(TBL_PREFIX . 'document_attachment as tda');
      $this->db->join(TBL_PREFIX . 'document_attachment_property as tdap', 'tda.id = tdap.doc_id', 'inner');
      $this->db->where('tda.draft_contract_type', 2);
      $this->db->where('tdap.envelope_id', $contract->envelope_id);
      $this->db->limit(1);
      $query = $this->db->get();
      $doc = $query->num_rows() > 0 ? $query->row() : NULL;
      $doc_body = <<<HTML
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
HTML;
      if (isset($doc) == true && empty($doc) == false && isset($doc->attachment_id) == true) {
        if($doc->envelope_id == $contract->envelope_id) {
          $envelope_count++;
        }

        $app_bg = 'trasparent';
        $appc_bg = 'trasparent';

        if ($contract->application_id != $doc->application_id) {
          $appc_bg = '#AAA';
        }

        if ($contract->applicant_id != $doc->applicant_id) {
          $app_bg = '#AAA';
        }
        $doc_count++;

        $doc_body = <<<HTML
        <td>$doc->attachment_id</td>
        <td>$doc->prop_id</td>
        <td style="background: $app_bg;">$doc->applicant_id</td>
        <td style="background: $appc_bg;">$doc->application_id</td>
        <td>$doc->task_applicant_id</td>
        <td>$doc->envelope_id</td>
        <td>$doc->signed_status</td>
HTML;
      } else {
        $miss_doc_count ++;
      }

      $table_body .= <<<HTML
      <tr>
        <td>$contract->id</td>
        <td>$contract->applicant_id</td>
        <td>$contract->application_id</td>
        <td>$contract->task_applicant_id</td>
        <td>$contract->envelope_id</td>
        <td>$contract->signed_status</td>
        $doc_body
      </tr>
HTML;
    }

    $table = <<<HTML
    <table style="width:100%; border-collapse: collapse; border: 1px solid #000;" border="1">
      <thead>
        $table_head
      </thead>
      <tbody>
        $table_body
      </tbody>
    <table>
HTML;
    echo 'Contract Count = '.$contract_count.'<br>';
    echo 'Document Count = '.$doc_count.'<br>';
    echo 'Envelope Id Matched = '.$envelope_count.'<br>';
    echo 'Doccument Missing count= '.$miss_doc_count.'<br>';
    echo $table;
  }
}
