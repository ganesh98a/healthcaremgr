<?php
/** Files holds the document type related operation */
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * class Master extends MX_Controller
 * @property-read Attachment_model $Attachment_model
 */
class DocumentAttachmentMigration extends MX_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->helper('i_pad');
    $this->appserver_file_path = (getenv('ENV_OUTSIDE_PATH')) ? getenv('ENV_OUTSIDE_PATH') . '/': FCPATH;
    $this->load->model('sales/Attachment_model');
  }

  public function document_attachment_migration()
  {
    //Allow to hit from only authenticated request
    api_request_handler();
    //Step 1 - Migrate reference data into document type table
    $this->migrate_data_from_ref_to_document_type();

  }

  public function migrate_data_from_ref_to_document_type()
  {
    $this->db->select(["ref.id as ref_id", "type.id as doc_id","ref.display_name as ref_name","type.title as title"]);
    $this->db->from(TBL_PREFIX . "recruitment_applicant_stage_attachment as att");
    $this->db->join(TBL_PREFIX . "references as ref", "ref.id = att.doc_category", "inner");
    $this->db->join(TBL_PREFIX . "document_type as type", "type.title = ref.display_name", "inner");
    $this->db->where_in("ref.type", [4, 5]);
    $this->db->group_by("type.id, type.doc_category_id");

    $datas = $this->db->get()->result_array();

    foreach($datas as $data) {
      $this->db->where("doc_category", $data['ref_id']);
      $this->db->update(TBL_PREFIX . "recruitment_applicant_stage_attachment", ['doc_category' => $data['doc_id']]);
    }
  }

  public function migrate_recruit_stage_attach_to_doc_attach() {
      //Skip the migration if already completed
    api_request_handler();

    $this->db->select(["att.*"]);
    $this->db->from(TBL_PREFIX . "recruitment_applicant_stage_attachment as att");
    $result = $this->db->get()->result_array();

    foreach($result as $res) {
        // Chek user id if available or not available
        $created_by = ($this->check_user_availablity($res['created_by']) == "yes") ? $res['created_by'] : NULL;

        //Insert data with document attachment table
        $doc_att = $this->document_attachment_array($res, $created_by);

        $this->db->insert(TBL_PREFIX . 'document_attachment', $doc_att);

        //Get the last inserted id
        $insert_id = $this->db->insert_id();

        //Insert data with document attachment property table
        $doc_prop_att = $this->document_attachment_property_array($res, $insert_id, $created_by);

        $this->db->insert(TBL_PREFIX . 'document_attachment_property', $doc_prop_att);

    }
        echo "Migration Successfully completed!";
  }

  public function document_attachment_array($res, $created_by) {

    $doc_att['application_id'] = $res['application_id'];
    $doc_att['applicant_id'] = $res['applicant_id'];
    $doc_att['stage'] = $res['stage'];
    $doc_att['draft_contract_type'] = $res['draft_contract_type'];
    $doc_att['is_main_stage_label'] = $res['is_main_stage_label'];
    $doc_att['uploaded_by_applicant'] = $res['uploaded_by_applicant'];
    $doc_att['member_move_archive'] = $res['member_move_archive'];
    $doc_att['related_to'] = 1;
    $doc_att['doc_type_id'] = $res['doc_category'];
    $doc_att['issue_date'] = (isset($res['issue_date'])) ? date('Y-m-d', strtotime($res['issue_date'])) : NULL;
    $doc_att['expiry_date'] = (isset($res['expiry_date'])) ? date('Y-m-d', strtotime($res['expiry_date'])) : NULL;
    $doc_att['reference_number'] = $res['reference_number'];
    $doc_att['document_status'] = $res['document_status'];
    $doc_att['archive'] = $res['archive'];
    $doc_att['created_by'] = $created_by;
    $doc_att['created_at'] = $res['created'] ?? NULL;
    $doc_att['updated_at'] = $res['updated_at'] ?? NULL;

    return $doc_att;
  }

  public function document_attachment_property_array($res, $insert_id, $created_by) {
        $doc_att_prop['doc_id'] = $insert_id;
        $doc_att_prop['file_name'] = $res['attachment_title'];
        $doc_att_prop['file_type'] = $res['file_type'];
        $doc_att_prop['file_path'] = $res['file_path'];
        $doc_att_prop['raw_name'] = $res['attachment'];
        $doc_att_prop['file_size'] = $res['file_size'];
        $doc_att_prop['aws_object_uri'] = $res['aws_object_uri'];
        $doc_att_prop['aws_response'] = $res['aws_response'];
        $doc_att_prop['aws_uploaded_flag'] = $res['aws_uploaded_flag'];
        $doc_att_prop['archive'] = $res['archive'];
        $doc_att_prop['created_by'] = $created_by;
        $doc_att_prop['created_at'] = $res['created'] ?? NULL;
        $doc_att_prop['updated_at'] = $res['updated_at'] ?? NULL;

        return $doc_att_prop;
  }

  public function check_user_availablity($id) {
    $this->db->select("id");
    $this->db->from(TBL_PREFIX . "member");
    $this->db->where("id", $id);

    $query = $this->db->get();

    if($query->num_rows() > 0 ) {
        return "yes";
    }

    return "no";

  }

  /*
   * Update Entity types
   */
  public function migrate_data_for_attachment_entity()
  {
    //Allow to hit from only authenticated request
    // api_request_handler();

    $this->db->select(["*"]);
    $this->db->from(TBL_PREFIX . "document_attachment as tda");

    $datas = $this->db->get()->result_array();

    require_once APPPATH . 'Classes/document/DocumentAttachment.php';
    $docAttachObj = new DocumentAttachment();
    // Get constant entity type applicant
    $entityTypeApplicant = $docAttachObj->getConstant('ENTITY_TYPE_APPLICANT');
    // Get constant entity type applicant
    $entityTypeMember = $docAttachObj->getConstant('ENTITY_TYPE_MEMBER');
    $temp = [];
    foreach($datas as $id => $data) {
        $temp[$id]['exist'] = $data;
        if ($data['applicant_id'] != '' && $data['applicant_id'] != NULL) {
            $update_column = ['entity_id' => $data['applicant_id'], 'entity_type' => $entityTypeApplicant ];
            if ($data['updated_by'] != '' && $data['updated_by'] != NULL) {
                $update_column = ['entity_id' => $data['applicant_id'], 'entity_type' => $entityTypeApplicant, 'updated_by_type' => $entityTypeMember ];
            }
            $this->db->where("id", $data['id']);
            $this->db->update(TBL_PREFIX . "document_attachment", $update_column);
            $data['entity_id'] = $data['applicant_id'];
            $data['entity_type'] = $entityTypeApplicant;
            if ($data['updated_by'] != '' && $data['updated_by'] != NULL) {
                $data['updated_by_type'] = $entityTypeMember;
            }

        }

        if ($data['member_id'] != '' && $data['member_id'] != NULL) {
            $this->db->where("id", $data['id']);
            $update_column = ['entity_id' => $data['member_id'], 'entity_type' => $entityTypeMember ];
            if ($data['updated_by'] != '' && $data['updated_by'] != NULL) {
                $update_column = ['entity_id' => $data['member_id'], 'entity_type' => $entityTypeMember, 'updated_by_type' => $entityTypeMember ];
            }
            $this->db->update(TBL_PREFIX . "document_attachment", $update_column);
            $data['entity_id'] = $data['member_id'];
            $data['entity_type'] = $entityTypeMember;
            if ($data['updated_by'] != '' && $data['updated_by'] != NULL) {
                $data['updated_by_type'] = $entityTypeMember;
            }
        }
        $temp[$id]['new'] = $data;
    }
    echo json_encode($temp);
  }

  /**
   * Move the files from App server to S3
   *
   *  *** Conditions ***
   * 1. We can move specific content by passing $prop_id in POST call
   * 2. Fetch only aws_uploaded_flag = 0 and aws_object_uri is empty it's help to avoid fetch already processed data
   * 3. We can set the limit by default we fetch 100 records
   *
   * *** Errors ***
   * 1. Files not found in local server
   * 2. S3 upload fails
   *
   * *** Notes ***
   * 1. We can get the process property ID at the end the page
   * 2. We update the property records once document successfully processed
   * And we keep the aws_uploaded_flag = 0 will update flag status in compare_app_s3_file_size
   */
  public function copy_recruitment_server_document_data_to_s3() {
    $request = api_request_handler();

    $prop_id = $request->prop_id ?? '';
    $doc_id = $request->doc_id ?? '';
    $limit = $request->limit ?? 100;
    $start = $request->start ?? 0;
    $data = $logdata = [];

    require_once APPPATH . 'Classes/common/Aws_file_upload.php';
    $awsFileupload = new Aws_file_upload();

    $this->db->select(["att.id as doc_id", "att.applicant_id", "att.application_id",
      "att.member_id", "att.draft_contract_type", "prop.raw_name", "prop.id as prop_id"]);
    $this->db->from(TBL_PREFIX . "document_attachment as att");
    $this->db->join(TBL_PREFIX . "document_attachment_property as prop", "att.id = prop.doc_id", "inner");

    if(isset($doc_id) && $doc_id != '') {
      $condition = $request->condition ?? 'IN' ;
      $this->db->where("prop.doc_id $condition (" . $doc_id . ")", NULL, FALSE);

    }
    //If property id is available the fetch only document id related data
    if(isset($prop_id) && $prop_id != '') {
      //If any one propery ID got stucked then we can skip it manually
      $condition = $request->condition ?? 'IN' ;
      $this->db->where("prop.id $condition (" . $prop_id . ")", NULL, FALSE);

    }
    else if(!$doc_id) {

      $this->db->where("prop.aws_uploaded_flag", 0);
      $this->db->where("prop.aws_object_uri IS NULL");
      $this->db->or_where("prop.aws_object_uri", '');
      $this->db->order_by('prop.id', $request->order_by ?? 'ASC');
      $this->db->limit($limit, $start);

    }

    $items = $this->db->get()->result_array();
    $attachment = NULL;

    if(!count($items)) {
      echo "Nothing to process. All Done!";
      return;
    }

    $success_prop_id = $html = NULL;
    $success_count = 0;

    foreach($items as $item) {

      $attachment = $this->get_attachment_path($item);

      if(!is_file($attachment) || !file_exists($attachment)) {
        $html .= "<tr><td>Document Not found in $attachment</td><td>" . $item['applicant_id'] . "</td><td>" . $item['doc_id'] ."</td>
        <td>" . $item['prop_id'] ."</td><td>" . $item['raw_name'] ."</td>";

        $logdata['module_id'] = 1;
        $logdata['status'] = 0;
        $logdata['migration_step'] = 'Step1- Copying file';
        $logdata['draft_contract_type'] = $item['draft_contract_type'];
        $logdata['applicant_id'] = $item['applicant_id'];
        $logdata['property_id'] = $item['prop_id'];
        $logdata['file_name'] = $item['raw_name'];
        $logdata['message'] = 'Document Not found in App server path- ' . $attachment;

        $this->basic_model->insert_records("s3_migration_log" , $logdata);
        continue;
      }

      $config['file_name'] = $item['raw_name'];
      $config['upload_path'] = S3_APPLICANT_ATTACHMENT_UPLOAD_PATH;
      $config['directory_name'] = $item['applicant_id'];
      $config['allowed_types'] = DEFAULT_ATTACHMENT_UPLOAD_TYPE; //'jpg|jpeg|png|xlx|xls|doc|docx|pdf|pages';
      $config['max_size'] = DEFAULT_MAX_UPLOAD_SIZE;
      $config['uplod_folder'] = $this->appserver_file_path . './uploads/';
      $config['adminId'] = $item['applicant_id'];
      $config['title'] = "Recruitment Attachment Migration";
      $config['module_id'] = REQUIRMENT_MODULE_ID;
      $config['created_by'] = $item['applicant_id'];
      $config['from_doc_migration'] = TRUE;
      $config['attachment_path'] = $attachment;

      $s3documentAttachment = $awsFileupload->upload_from_app_to_s3($config, FALSE);

      if (isset($s3documentAttachment) || $s3documentAttachment['aws_uploaded_flag']) {

        $data['file_path'] = $s3documentAttachment['file_path'];
        $data['file_type'] = $s3documentAttachment['file_type'];
        $data['file_size'] = $s3documentAttachment['file_size'];
        $data['file_ext'] = $s3documentAttachment['file_ext'];
        $data['aws_response'] = $s3documentAttachment['aws_response'];
        $data['aws_object_uri'] = $s3documentAttachment['aws_object_uri'];
        $data['aws_file_version_id'] = $s3documentAttachment['aws_file_version_id'];
        $data['aws_uploaded_flag'] = 0;

        $this->db->where("id", $item['prop_id']);
        $this->db->update(TBL_PREFIX . "document_attachment_property", $data);

        $logdata['message'] = 'Document successfully uploaded';
        $logdata['status'] = 1;
        $success_prop_id .= $item['prop_id'] .",";
        $success_count++;

      }
      else {
        $html .="<tr><td>S3 upload failed</td><td>" . $item['applicant_id'] . "</td><td>" . $item['doc_id'] . "</td><td>" . $item['prop_id'] . "</td><td>". $item['raw_name'] . "</td></tr>";
        $propID .= $item['prop_id'] . ",";
        $logdata['message'] = 'Error' . $s3documentAttachment['aws_response'];
      }

      $logdata['module_id'] = 1;
      $logdata['migration_step'] = 'Step1- Copying file';
      $logdata['draft_contract_type'] = $item['draft_contract_type'];
      $logdata['applicant_id'] = $item['applicant_id'];
      $logdata['property_id'] = $item['prop_id'];
      $logdata['file_name'] = $item['raw_name'];

      $this->basic_model->insert_records("s3_migration_log" , $logdata);

    }
    if($html) {
      echo "<h2 align='center'>Copying files from app server to S3</h2>";
      echo '<table border="1" style="border: 1px solid #000;border-collapse: collapse;
        width: 100%"><tr><th>Error type</th><th>Applicant ID</th><th>Document ID</th><th>Propery ID</th><th>File name</th></tr>';
      echo $html;
      echo "</table>";
      echo $propID ?? NULL;
    }

    echo "<h3> Successful Property ID - "; echo $success_prop_id ?? "Nil";  echo"</h3>";
    echo "<p>Total List " . count($items) . "</p>";
    echo "<p>Success count $success_count </p>";
  }

  /**
   * This function has a feature to compare files size between app server and S3
   *
   * *** Conditions ***
   * 1. aws_uploaded_flag = 0
   * 2. We can pass specific property ID which is already compared successfully
   *  in order to avoid to checking the same ID again, if flag not changed after the update
   * 3. We can set the limitation by passing {$limit},{$start} value in POST call
   *
   * *** Type of errors ***
   * 1. File not found in app server
   * 2. If File doesn't has a file_path then it's consider as a not yet processed state
   * 3. If app and s3 file size equal to 0 or not equal to means then it will be consider as
   *  Size not equal
   *
   * *** Notes ***
   * 1. We can get the records from table format
   * 2. We can get the success ID at the end of page.
   */
  public function compare_recruitment_app_s3_file_size() {
    $request = api_request_handler();

    $prop_id = $request->prop_id ?? '';
    $doc_id = $request->doc_id ?? '';
    $limit = $request->limit ?? 100;
    $start = $request->start ?? 0;


    $this->db->select(["att.id as doc_id", "att.applicant_id", "att.application_id",
    "att.member_id", "att.draft_contract_type", "prop.raw_name", "prop.id as prop_id", "prop.aws_object_uri","prop.file_path"]);
    $this->db->from(TBL_PREFIX . "document_attachment as att");
    $this->db->join(TBL_PREFIX . "document_attachment_property as prop", "att.id = prop.doc_id", "inner");

    $this->db->where("prop.aws_uploaded_flag", 0);

    if(isset($doc_id) && $doc_id != '') {
      $condition = $request->condition ?? 'IN' ;
      $this->db->where("prop.doc_id $condition (" . $doc_id . ")", NULL, FALSE);

    }

    if(isset($prop_id) && $prop_id != '') {
      //If any one propery ID got stucked then we can skip it manually
      $condition = $request->condition ?? 'IN' ;

      $this->db->where("prop.id $condition (" . $prop_id . ")", NULL, FALSE);

    }
    $this->db->order_by('prop.id', $request->order_by ?? 'ASC');
    $this->db->limit($limit ?? 100, $start ?? 0);

    $items = $this->db->get()->result_array();

    if(!count($items)) {
      echo "Nothing to process. All Done!";
      return;
    }

    $attachment = $success_prop_id = $html = $s3_file_length = NULL;
    $success_count = 0;
    $data = $logdata = [];
    $this->load->library('AmazonS3');
    foreach($items as $item) {

      //Skip the memper portal data
      if(!$item['file_path']) {
        $html .= "<tr><td>Not Processed</td><td>" .$item['applicant_id'] . "</td><td>-</td><td>-</td><td>" .$item['prop_id'] . "</td><td>" .$item['raw_name'] . "</td></tr>";

        $logdata['module_id'] = 1;
        $logdata['status'] = 0;
        $logdata['migration_step'] = 'Step2- Compare file size';
        $logdata['draft_contract_type'] = $item['draft_contract_type'];
        $logdata['applicant_id'] = $item['applicant_id'];
        $logdata['property_id'] = $item['prop_id'];
        $logdata['file_name'] = $item['raw_name'];
        $logdata['message'] = 'Not processed yet';

        $this->basic_model->insert_records("s3_migration_log" , $logdata);
        continue;
      }

      $attachment = $this->get_attachment_path($item);

      if(!is_file($attachment) || !file_exists($attachment)) {
        $html .= "<tr><td>App server File not found</td><td>" .$item['applicant_id'] . "</td><td>-</td><td>-</td><td>" .$item['prop_id'] . "</td><td>" .$item['raw_name'] . "</td></tr>";

        $logdata['module_id'] = 1;
        $logdata['status'] = 0;
        $logdata['migration_step'] = 'Step2- Compare file size';
        $logdata['draft_contract_type'] = $item['draft_contract_type'];
        $logdata['applicant_id'] = $item['applicant_id'];
        $logdata['property_id'] = $item['prop_id'];
        $logdata['file_name'] = $item['raw_name'];
        $logdata['message'] = 'Document Not found in App server';

        $this->basic_model->insert_records("s3_migration_log" , $logdata);

        continue;
      }

      $this->amazons3->setFolderKey($item['file_path']);
      $amazons3_get = $this->amazons3->getDocument();

      if(isset($amazons3_get) && $amazons3_get['status'] == 200) {
        $s3_file_length = $amazons3_get['data']['ContentLength'];
        $server_file_size = filesize($attachment);
        if($s3_file_length != $server_file_size || $s3_file_length == "0") {
          $html .= "<tr><td>Size not match</td><td>" .$item['applicant_id'] . "</td><td>" .$s3_file_length . "</td><td>" . $server_file_size .
            "</td><td>" .$item['prop_id'] . "</td><td>" .$item['file_path'] . "</td></tr>";

            $logdata['status'] = 0;
            $logdata['server_file_size'] = $server_file_size;
            $logdata['s3_file_size'] = $s3_file_length;
            $logdata['message'] = 'Size not matched';
        }
        else {
          $success_prop_id .= $item['prop_id'] .",";
          $this->update_s3_flag($item['prop_id']);
          $logdata['status'] = 1;
          $logdata['message'] = 'Size matched successfully'. json_encode($amazons3_get['data']->toArray());
          $success_count++;
        }

        $logdata['module_id'] = 1;
        $logdata['migration_step'] = 'Step2- Compare file size';
        $logdata['draft_contract_type'] = $item['draft_contract_type'];
        $logdata['applicant_id'] = $item['applicant_id'];
        $logdata['property_id'] = $item['prop_id'];
        $logdata['file_name'] = $item['raw_name'];

        $this->basic_model->insert_records("s3_migration_log" , $logdata);
      }

    }

    if($html) {
      echo "<h2 align='center'>Compare file size between app server and S3</h2>";
      echo '<table border="1" style="border: 1px solid #000;border-collapse: collapse;width: 100%"><tr><th>Error Type</th><th>Applicant Id</th><th>S3 File size</th><th>App Server File Size</th>
      <th>Property ID</th><th>File Path / Name</th></tr>';

      echo $html; echo "</table>";
    }

    echo "<h3> Successful Property ID - "; echo $success_prop_id ?? "Nil";  echo"</h3>";
    echo "<p>Total List " . count($items) . "</p>";
    echo "<p>Success count $success_count </p>";
  }

/** Get Attachment Path */
  public function get_attachment_path($item) {
    switch ($item['draft_contract_type']) {
      case '1':
        $attachment = GROUP_INTERVIEW_CONTRACT_PATH . $item['raw_name'];
        break;
      case '2':
        $attachment = CABDAY_INTERVIEW_CONTRACT_PATH . $item['applicant_id'] . '/' . $item['raw_name'];
        break;

      default:
        $attachment = APPLICANT_ATTACHMENT_UPLOAD_PATH . $item['applicant_id'] .'/'. $item['raw_name'];
        break;
    }

    return $this->appserver_file_path . $attachment;
  }

  /**
   * Update S3 flag status
   *
   * 1. we can pass the specific property ID to update the record.
   * both Post Request or parameter
   * 2. We have to pass the update_confirmation yes if it is single updates
   *
   */
  public function update_s3_flag($prop_id = NULL) {
    $request = api_request_handler();

    if(isset($request->prop_id)) {
      $prop_id = $request->prop_id;
    }

    if(isset($prop_id)) {

      $this->db->where("id in (" . $prop_id . ")", NULL, FALSE);

    }
    if(!isset($prop_id) && !isset($request->update_confirmation)) {
      echo "Please pass the confirmation parameter yes to Update whole record!";
      return;
    }
    $aws_uploaded_flag = ($request->aws_uploaded_flag) ?? 1;
    $this->db->update(TBL_PREFIX . "document_attachment_property",  ['aws_uploaded_flag' => $aws_uploaded_flag,
      'updated_at' => DATE_TIME, 'updated_by' => 9999]); //Add 9999 for the hidden reference of script update.
  }

  public function copy_sales_document_attachment_migration() {
    $request = api_request_handler();

    $config = [];

    $attachment_id = $request->attachment_id ?? '';
    $limit = $request->limit ?? 100;
    $start = $request->start ?? 0;

    $this->db->select(["sales.*"]);
    $this->db->from(TBL_PREFIX . "sales_attachment as sales");

    if(isset($attachment_id) && $attachment_id != '') {
      $condition = $request->condition ?? 'IN' ;
      $this->db->where("sales.id $condition (" . $attachment_id . ")", NULL, FALSE);

    }
    else if(!$attachment_id) {

      $this->db->where("sales.aws_uploaded_flag", 0);
      $this->db->where("sales.aws_object_uri IS NULL");
      $this->db->or_where("sales.aws_object_uri", '');
      $this->db->order_by('sales.id', $request->order_by ?? 'ASC');
      $this->db->limit($limit, $start);

    }

    $items = $this->db->get()->result_array();

    if(!count($items)) {
      echo "Nothing to process. All Done!";
      return;
    }

    $success_prop_id = $html = NULL;
    $success_count = 0;

    foreach($items as $item) {

      $fullpath = $item['full_path'];

      if(!is_file($fullpath) || !file_exists($fullpath)) {
        $html .= "<tr><td>Document Not found </td><td>" . $item['id'] . "</td><td>" . $item['file_name'] ."</td>
        <td>" . $item['full_path'] ."</td><td>";

        $logdata['module_id'] = 3;
        $logdata['status'] = 0;
        $logdata['migration_step'] = 'Step1- Copying file';
        $logdata['property_id'] = $item['id'];
        $logdata['file_name'] = $item['file_name'];
        $logdata['message'] = 'Document Not found in App server path- ' . $item['full_path'];

        $this->basic_model->insert_records("s3_migration_log" , $logdata);
        continue;
      }

      $attachment = $this->Attachment_model->find_attachment_by_id($item['id']);

      $relationship = ($attachment['relationships'] ?? [])[0] ?? null;
      if (!empty($relationship)) {
          $object_type = $relationship['object_type'];
          $object_id = $relationship['object_id'];

          $name = $this->Attachment_model->get_module_folder_name($object_type);

          require_once APPPATH . 'Classes/common/Aws_file_upload.php';
          $awsFileupload = new Aws_file_upload();
          $config['upload_path'] = S3_SALES_ATTACHMENT_UPLOAD_PATH . $name . "/$object_id/";

          $config['file_name'] = $item['file_name'];
          $config['attachment_path'] = $item['full_path'];
          $config['directory_name'] = NULL;
          $config['allowed_types'] = DEFAULT_ATTACHMENT_UPLOAD_TYPE; //'jpg|jpeg|png|xlx|xls|doc|docx|pdf|pages';
          $config['max_size'] = DEFAULT_MAX_UPLOAD_SIZE;

          $config['adminId'] = NULL;
          $config['title'] = "Sales Attachment Migration";
          $config['module_id'] = 3;
          $config['created_by'] = NULL;
          $config['from_doc_migration'] = TRUE; //Skip to store the S3 logs

          $s3documentAttachment = $awsFileupload->upload_from_app_to_s3($config, FALSE);

          if (isset($s3documentAttachment) || $s3documentAttachment['aws_uploaded_flag']) {

            $data['file_path'] = $s3documentAttachment['file_path'];
            $data['file_type'] = $s3documentAttachment['file_type'];
            $data['file_size'] = $s3documentAttachment['file_size'];
            $data['file_ext'] = $s3documentAttachment['file_ext'];
            $data['aws_response'] = $s3documentAttachment['aws_response'];
            $data['aws_object_uri'] = $s3documentAttachment['aws_object_uri'];
            $data['aws_file_version_id'] = $s3documentAttachment['aws_file_version_id'];
            $data['aws_uploaded_flag'] = 0;

            $this->db->where("id", $item['id']);
            $this->db->update(TBL_PREFIX . "sales_attachment", $data);

            $logdata['message'] = 'Document successfully uploaded';
            $logdata['status'] = 1;
            $success_prop_id .= $item['id'] .",";
            $success_count++;

          }
          else {
            $html .="<tr><td>S3 upload failed</td><td>" . $item['id'] . "</td><td>" . $item['file_name'] . "</td><td>" . $item['full_path'] . "</td></tr>";
            $propID .= $item['id'] . ",";
            $logdata['message'] = 'Error' . $s3documentAttachment['aws_response'];
          }

          $logdata['module_id'] = 3;
          $logdata['migration_step'] = 'Step1- Copying file';
          $logdata['property_id'] = $item['id'];
          $logdata['file_name'] = $item['file_name'];

          $this->basic_model->insert_records("s3_migration_log" , $logdata);

      }

    }

    if($html) {
      echo "<h2 align='center'>Copying Sales attachment files from app server to S3</h2>";
      echo '<table border="1" style="border: 1px solid #000;border-collapse: collapse;
        width: 100%"><tr><th>Error type</th><th>Document ID</th><th>File name</th><th>File Path</th></tr>';
      echo $html;
      echo "</table>";
      echo $propID ?? NULL;
    }

    echo "<h3> Successful Attachment ID - "; echo $success_prop_id ?? "Nil";  echo"</h3>";
    echo "<p>Total List " . count($items) . "</p>";
    echo "<p>Success count $success_count </p>";

  }

  /**
   * Compare app server files and S3 files and update the status 1
   */
  public function compare_sales_app_s3_file_size() {
    $request = api_request_handler();

    $attachment_id = $request->attachment_id ?? '';
    $limit = $request->limit ?? 100;
    $start = $request->start ?? 0;


    $this->db->select(["sales.*"]);
    $this->db->from(TBL_PREFIX . "sales_attachment as sales");
    $this->db->where("sales.aws_uploaded_flag", 0);

    if(isset($attachment_id) && $attachment_id != '') {
      $condition = $request->condition ?? 'IN' ;
      $this->db->where("sales.id $condition (" . $attachment_id . ")", NULL, FALSE);

    }

    $this->db->order_by('sales.id', $request->order_by ?? 'ASC');
    $this->db->limit($limit ?? 100, $start ?? 0);

    $items = $this->db->get()->result_array();

    if(!count($items)) {
      echo "Nothing to process. All Done!";
      return;
    }

    $attachment = $success_prop_id = $html = $s3_file_length = NULL;
    $success_count = 0;
    $logdata = [];
    $this->load->library('AmazonS3');
    foreach($items as $item) {

      $attachment = getenv('ENV_OUTSIDE_PATH') ? getenv('ENV_OUTSIDE_PATH') : $item['full_path'];

      if(!is_file($attachment) || !file_exists($attachment)) {
        $html .= "<tr><td>App server File not found</td><td>" .$item['id'] . "</td><td>-</td><td>-</td><td>" .$item['file_name'] . "</td><td>" .$item['full_path'] . "</td></tr>";

        $logdata['module_id'] = 3;
        $logdata['status'] = 0;
        $logdata['migration_step'] = 'Step2- Compare file size';
        $logdata['property_id'] = $item['id'];
        $logdata['file_name'] = $item['file_name'];
        $logdata['message'] = 'Document Not found in App server';

        $this->basic_model->insert_records("s3_migration_log" , $logdata);

        continue;
      }

      $this->amazons3->setFolderKey($item['file_path']);
      $amazons3_get = $this->amazons3->getDocument();

      if(isset($amazons3_get) && $amazons3_get['status'] == 200) {
        $s3_file_length = $amazons3_get['data']['ContentLength'];
        $server_file_size = filesize($attachment);
        if($s3_file_length != $server_file_size || $s3_file_length == "0") {
          $html .= "<tr><td>Size not matched</td><td>" .$item['id'] . "</td><td>" .$s3_file_length . "</td><td>" . $server_file_size .
            "</td><td>" .$item['file_name'] . "</td><td>" .$item['full_path'] . "</td></tr>";

            $logdata['status'] = 0;
            $logdata['server_file_size'] = $server_file_size;
            $logdata['s3_file_size'] = $s3_file_length;
            $logdata['message'] = 'Size not matched';
        }
        else {
          $success_prop_id .= $item['id'] .",";

          $this->db->where("id", $item['id']);
          $this->db->update(TBL_PREFIX . "sales_attachment", ['aws_uploaded_flag' => 1]);
          $logdata['status'] = 1;
          $logdata['message'] = 'Size matched successfully'. json_encode($amazons3_get['data']->toArray());
          $success_count++;
        }

        $logdata['module_id'] = 3;
        $logdata['migration_step'] = 'Step2- Compare file size';
        $logdata['property_id'] = $item['id'];
        $logdata['file_name'] = $item['file_name'];

        $this->basic_model->insert_records("s3_migration_log" , $logdata);
      }

    }

    if($html) {
      echo "<h2 align='center'>Compare file size between app server and S3</h2>";
      echo '<table border="1" style="border: 1px solid #000;border-collapse: collapse;width: 100%"><tr><th>Error Type</th><th>Attachment Id</th><th>S3 File size</th><th>App Server File Size</th>
      <th>Filename </th><th>Full Path</th></tr>';

      echo $html; echo "</table>";
    }

    echo "<h3> Successful Attachment ID - "; echo $success_prop_id ?? "Nil";  echo"</h3>";
    echo "<p>Total List " . count($items) . "</p>";
    echo "<p>Success count $success_count </p>";
  }

  /** Step 1 - Copy Imail attachment from app server to S3 */
  public function copy_imail_attachment_migration() {
    $request = api_request_handler();

    $config = [];
    $tbl_name = $request->mail_type ?? '';

    if(!$tbl_name) {
      echo "Please Specify I-mail type internal or external";
      return;
    }

    $attachment_id = $request->attachment_id ?? '';
    $limit = $request->limit ?? 100;
    $start = $request->start ?? 0;

    $this->db->select(["imail.*"]);
    $this->db->from(TBL_PREFIX . $tbl_name . "_message_attachment as imail");

    if(isset($attachment_id) && $attachment_id != '') {
      $condition = $request->condition ?? 'IN' ;
      $this->db->where("imail.id $condition (" . $attachment_id . ")", NULL, FALSE);

    }
    else if(!$attachment_id) {

      $this->db->where("imail.aws_uploaded_flag", 0);
      $this->db->where("imail.aws_object_uri IS NULL");
      $this->db->or_where("imail.aws_object_uri", '');
      $this->db->order_by('imail.id', $request->order_by ?? 'ASC');
      $this->db->limit($limit, $start);

    }

    $items = $this->db->get()->result_array();

    if(!count($items)) {
      echo "Nothing to process. All Done!";
      return;
    }

    $success_prop_id = $html = NULL;
    $success_count = 0;
    $s3_file_path = S3_EXTERNAL_IMAIL_PATH;
    $file_path = EXTERNAL_IMAIL_PATH;

    if($tbl_name == 'internal') {
      $file_path = INTERNAL_IMAIL_PATH;
      $s3_file_path = S3_INTERNAL_IMAIL_PATH;
    }
    foreach($items as $item) {

      $fullpath = FCPATH . $file_path .'/'. $item['messageContentId'] . '/' . $item['filename'];

      if(!is_file($fullpath) || !file_exists($fullpath)) {
        $html .= "<tr><td>Document Not found </td><td>" . $item['id'] . "</td><td>" . $item['filename'] ."</td>
        <td>" . $fullpath ."</td><td>";

        $logdata['module_id'] = 4;
        $logdata['status'] = 0;
        $logdata['migration_step'] = 'Step1- Copying file';
        $logdata['property_id'] = $item['id'];
        $logdata['file_name'] = $item['filename'];
        $logdata['message'] = 'Document Not found in App server path- ' . $fullpath;

        $this->basic_model->insert_records("s3_migration_log" , $logdata);
        continue;
      }

      require_once APPPATH . 'Classes/common/Aws_file_upload.php';
      $awsFileupload = new Aws_file_upload();
      $config['attachment_path'] = $fullpath;

      $config['file_name'] = $item['filename'];
      $config['upload_path'] = $s3_file_path;
      $config['directory_name'] = $item['messageContentId'];
      $config['allowed_types'] = DEFAULT_ATTACHMENT_UPLOAD_TYPE; //'jpg|jpeg|png|xlx|xls|doc|docx|pdf|pages';
      $config['max_size'] = DEFAULT_MAX_UPLOAD_SIZE;

      $config['adminId'] = NULL;
      $config['title'] = "Imail " . $tbl_name . " Attachment Migration";
      $config['module_id'] = 4;
      $config['created_by'] = NULL;
      $config['from_doc_migration'] = TRUE; //Skip to store the S3 logs

      $s3documentAttachment = $awsFileupload->upload_from_app_to_s3($config, FALSE);

      if (isset($s3documentAttachment) || $s3documentAttachment['aws_uploaded_flag']) {

        $data['file_path'] = $s3documentAttachment['file_path'];
        $data['file_type'] = $s3documentAttachment['file_type'];
        $data['file_size'] = $s3documentAttachment['file_size'];
        $data['file_ext'] = $s3documentAttachment['file_ext'];
        $data['aws_response'] = $s3documentAttachment['aws_response'];
        $data['aws_object_uri'] = $s3documentAttachment['aws_object_uri'];
        $data['aws_file_version_id'] = $s3documentAttachment['aws_file_version_id'];
        $data['aws_uploaded_flag'] = 0;

        $this->db->where("id", $item['id']);
        $this->db->update(TBL_PREFIX . $tbl_name .'_message_attachment', $data);

        $logdata['message'] = 'Document successfully uploaded';
        $logdata['status'] = 1;
        $success_prop_id .= $item['id'] .",";
        $success_count++;

      }
      else {
        $html .="<tr><td>S3 upload failed</td><td>" . $item['id'] . "</td><td>" . $item['filename'] . "</td><td>" . $full_path . "</td></tr>";
        $propID .= $item['id'] . ",";
        $logdata['message'] = 'Error' . $s3documentAttachment['aws_response'];
      }

      $logdata['module_id'] = 4;
      $logdata['migration_step'] = 'Step1- Copying file';
      $logdata['property_id'] = $item['id'];
      $logdata['file_name'] = $item['filename'];

      $this->basic_model->insert_records("s3_migration_log" , $logdata);

    }

    if($html) {
      echo "<h2 align='center'>Copying " . $tbl_name . "  Imail attachment files from app server to S3</h2>";
      echo '<table border="1" style="border: 1px solid #000;border-collapse: collapse;
        width: 100%"><tr><th>Error type</th><th>Attachment ID</th><th>File name</th><th>File Path</th></tr>';
      echo $html;
      echo "</table>";
      echo $propID ?? NULL;
    }

    echo "<h3> Successful Attachment ID - "; echo $success_prop_id ?? "Nil";  echo"</h3>";
    echo "<p>Total List " . count($items) . "</p>";
    echo "<p>Success count $success_count </p>";

  }

  /**
   * Compare app server files and S3 files and update the status 1
   */
  public function compare_imail_s3_file_size() {
    $request = api_request_handler();

    $tbl_name = $request->mail_type ?? '';

    if(!$tbl_name) {
      echo "Please Specify I-mail type internal or external";
      return;
    }

    $attachment_id = $request->attachment_id ?? '';
    $limit = $request->limit ?? 100;
    $start = $request->start ?? 0;


    $this->db->select(["imail.*"]);
    $this->db->from(TBL_PREFIX . $tbl_name . "_message_attachment as imail");
    $this->db->where("imail.aws_uploaded_flag", 0);

    if(isset($attachment_id) && $attachment_id != '') {
      $condition = $request->condition ?? 'IN' ;
      $this->db->where("imail.id $condition (" . $attachment_id . ")", NULL, FALSE);

    }

    $this->db->order_by('imail.id', $request->order_by ?? 'ASC');
    $this->db->limit($limit ?? 100, $start ?? 0);

    $items = $this->db->get()->result_array();

    if(!count($items)) {
      echo "Nothing to process. All Done!";
      return;
    }

    $success_prop_id = $html = $s3_file_length = NULL;
    $success_count = 0;
    $logdata = [];
    $this->load->library('AmazonS3');

    $file_path = EXTERNAL_IMAIL_PATH;

    if($tbl_name == 'internal') {
      $file_path = INTERNAL_IMAIL_PATH;
    }

    foreach($items as $item) {

      $fullpath = FCPATH . $file_path .'/'. $item['messageContentId'] . '/' . $item['filename'];

      if(!is_file($fullpath) || !file_exists($fullpath)) {
        $html .= "<tr><td>App server File not found</td><td>" .$item['id'] . "</td><td>-</td><td>-</td><td>" .$item['filename'] . "</td><td>" .$fullpath . "</td</tr>";

        $logdata['module_id'] = 3;
        $logdata['status'] = 0;
        $logdata['migration_step'] = 'Step2- Compare file size';
        $logdata['property_id'] = $item['id'];
        $logdata['file_name'] = $item['filename'];
        $logdata['message'] = 'Document Nt found in App server';

        $this->basic_model->insert_records("s3_migration_log" , $logdata);

        continue;
      }

      $this->amazons3->setFolderKey($item['file_path']);
      $amazons3_get = $this->amazons3->getDocument();

      if(isset($amazons3_get) && $amazons3_get['status'] == 200) {
        $s3_file_length = $amazons3_get['data']['ContentLength'];
        $server_file_size = filesize($fullpath);
        if($s3_file_length != $server_file_size || $s3_file_length == "0") {
          $html .= "<tr><td>Size not matched</td><td>" .$item['id'] . "</td><td>" .$s3_file_length . "</td><td>" . $server_file_size .
            "</td><td>" .$item['filename'] . "</td><td>" .$fullpath . "</td</tr>";

            $logdata['status'] = 0;
            $logdata['server_file_size'] = $server_file_size;
            $logdata['s3_file_size'] = $s3_file_length;
            $logdata['message'] = 'Size not matched';
        }
        else {
          $success_prop_id .= $item['id'] .",";

          $this->db->where("id", $item['id']);
          $this->db->update(TBL_PREFIX . $tbl_name . "_message_attachment", ['aws_uploaded_flag' => 1]);
          $logdata['status'] = 1;
          $logdata['message'] = 'Size matched successfully'. json_encode($amazons3_get['data']->toArray());
          $success_count++;
        }

        $logdata['module_id'] = 3;
        $logdata['migration_step'] = 'Step2- Compare file size';
        $logdata['property_id'] = $item['id'];
        $logdata['file_name'] = $item['filename'];

        $this->basic_model->insert_records("s3_migration_log" , $logdata);
      }

    }

    if($html) {
      echo "<h2 align='center'>Compare file size between app server and S3</h2>";
      echo '<table border="1" style="border: 1px solid #000;border-collapse: collapse;width: 100%"><tr><th>Error Type</th><th>Attachment Id</th><th>S3 File size</th><th>App Server File Size</th>
      <th>Filename </th><th>Full Path</th></tr>';

      echo $html; echo "</table>";
    }

    echo "<h3> Successful Attachment ID - "; echo $success_prop_id ?? "Nil";  echo"</h3>";
    echo "<p>Total List " . count($items) . "</p>";
    echo "<p>Success count $success_count </p>";
  }

}
