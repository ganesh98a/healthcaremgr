<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class ServiceAgreementS3Migration extends MX_Controller
{	

	function __construct() {
		parent::__construct();
	    $this->load->helper('i_pad');
	    $this->appserver_file_path = (getenv('ENV_OUTSIDE_PATH')) ? getenv('ENV_OUTSIDE_PATH') . '/': FCPATH;
	}

  /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # HCM- 3485, adding all requests to backend in a log file
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }

	/**
	 * Get list of the file exist in app server
	 */
  	public function get_list_sa_docusign_for_migrate() {
    	$request = api_request_handler();
  		$limit = $request->limit ?? 100;
    	$g_offset = $offset = $request->offset ?? 1;
  		$offset = $offset > 0 ? $offset - 1 : $offset;
  		$current_from = $offset * $limit + 1;
  		$current_end = $g_offset * $limit;

  		$service_agreement_id = $request->service_agreement_id ?? '';
  		$service_agreement_attachment_id = $request->service_agreement_attachment_id ?? '';

    	$data = [];

    	$select = [ "saa.*" ];
    	$where = [];

    	if ($service_agreement_id != '') {
    		$where['service_agreement_id'] = $service_agreement_id;
    	}

    	if ($service_agreement_attachment_id != '') {
    		$where['id'] = $service_agreement_attachment_id;
    	}

    	if ($service_agreement_id == '' && $service_agreement_attachment_id == '') {
    		$this->db->where("saa.signed_status", 1);
    		$this->db->where("saa.aws_uploaded_flag", 0);
    		$this->db->where("( saa.aws_object_uri IS NULL OR saa.aws_object_uri = '' )");
    	}

        $this->db
            ->select($select)
            ->where($where)
            ->from('tbl_service_agreement_attachment as saa');
        $this->db->limit($limit, ($offset * $limit));
        $query = $this->db->get();
        // save result if nom_rows not equal to zero
        if ($query->num_rows() > 0) {
            $data = $query->result();
        }

        $list_count = $query->num_rows();
        // total record
        $query_tot = $this->db
            ->select($select)
            ->where($where)
            ->get('tbl_service_agreement_attachment as saa');
        
        $totalData = $query_tot->num_rows();

        $dataHtml = '';
        $tableThHtml = '';
        $tableTrHtml = '';

        if($query->num_rows() < 1) {
	      $tableTrHtml = "<tr><td colspan='7'>Nothing to process. All Done!</td></tr>";
	    }

	    $tableThHtml = <<<TABLEHTML
    	<tr>
    		<th align="left">S.No</th>
    		<th align="left">Service Agreement ID</th>
    		<th align="left">Attachment Id</th>
    		<th align="left">Related</th>
    		<th align="left">Envelope Id</th>
    		<th align="left">File Path</th>
    		<th align="left">Document</th>
    	</tr>
TABLEHTML;
		$i = 1;
		foreach($data as $item) {
			$attachment = FCPATH. SERVICE_AGREEMENT_CONTRACT_PATH . $item->id . '/' .$item->signed_file;
			$file = 'File Found';
			if(!file_exists($attachment)) {
				$file = 'File Not Exist.';
			}
			$tableTrHtml .= <<<TABLEHTML
	    	<tr>
	    		<td>$i</td>
	    		<td>$item->service_agreement_id</td>
	    		<td>$item->id</td>
	    		<td>$item->related</td>
	    		<td>$item->envelope_id</td>
	    		<td>$attachment</td>
	    		<td>$file</td>
	    	</tr>
TABLEHTML;
			$i++;
		}

		$total_offset = 0;
		if ($limit != 0) {
            if ($totalData % $limit == 0) {
                $total_offset = ($totalData / $limit);
            } else {
                $total_offset = ((int) ($totalData / $limit)) + 1;
            }
        }

		$dataHtml = <<<TABLEHTML
		<p>Total Record : $totalData</p>
		<p>Total Offset : $total_offset</p>
		<p>Listed Record : $list_count ($current_from - $current_end)</p>
		<p>Current Offset : $g_offset</p>
	    <table border="1" style="width: 100%; border-collapse: collapse;">
	    	$tableThHtml
	    	$tableTrHtml
	    </table>
TABLEHTML;
	    echo $dataHtml;
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
  	public function copy_service_agreement_server_document_data_to_s3() {
    	
  		$this->load->library('AmazonS3');
  		$request = api_request_handler();

  		$limit = $request->limit ?? 100;
    	$g_offset = $offset = $request->offset ?? 1;
  		$offset = $offset > 0 ? $offset - 1 : $offset;
  		$current_from = $offset * $limit + 1;
  		$current_end = $g_offset * $limit;

    	$service_agreement_id = $request->service_agreement_id ?? '';
  		$service_agreement_attachment_id = $request->service_agreement_attachment_id ?? '';

    	$data = [];

    	$select = [ "saa.*" ];
    	$where = [];

    	if ($service_agreement_id != '') {
    		$where['service_agreement_id'] = $service_agreement_id;
    	}

    	if ($service_agreement_attachment_id != '') {
    		$where['id'] = $service_agreement_attachment_id;
    	}

    	if ($service_agreement_id == '' && $service_agreement_attachment_id == '') {
    		$this->db->where("saa.signed_status", 1);
    		$this->db->where("saa.aws_uploaded_flag", 0);
    		$this->db->where("( saa.aws_object_uri IS NULL OR saa.aws_object_uri = '' )");
    	}

        $this->db
            ->select($select)
            ->where($where)
            ->from('tbl_service_agreement_attachment as saa');
        $this->db->limit($limit, ($offset * $limit));
        $query = $this->db->get();
        // save result if nom_rows not equal to zero
        if ($query->num_rows() > 0) {
            $data = $query->result();
        }

        $list_count = $query->num_rows();
        // total record
        $query_tot = $this->db
            ->select($select)
            ->where($where)
            ->get('tbl_service_agreement_attachment as saa');
        
        $totalData = $query_tot->num_rows();

        $dataHtml = '';
        $tableThHtml = '';
        $tableTrHtml = '';

        if($query->num_rows() < 1) {
	      $tableTrHtml = "<tr><td colspan='8'>Nothing to process. All Done!</td></tr>";
	    }

	    $tableThHtml = <<<TABLEHTML
    	<tr>
    		<th align="left">S.No</th>
    		<th align="left">Service Agreement ID</th>
    		<th align="left">Attachment Id</th>
    		<th align="left">Related</th>
    		<th align="left">Envelope Id</th>
    		<th align="left">File Path</th>
    		<th align="left">Document</th>
    		<th align="left">S3 Path</th>
    	</tr>
TABLEHTML;
		$i = 1;
		$success_count = 0;
		$folder_key = '';
		foreach($data as $item) {
			$attachment = FCPATH. SERVICE_AGREEMENT_CONTRACT_PATH . $item->id . '/' .$item->signed_file;
			
			if(!file_exists($attachment)) {
				$file = 'File Not Exist.';
			} else {
				$file = 'File Found';
				$s3_folder = S3_SERVICE_AGREEMENT_CONTRACT_DOCUSIGN_PATH;
				$directory_name = $item->id;
				$file_name = $item->signed_file;				

			    $folder_key = $s3_folder . $directory_name .'/'. $file_name;
				$this->amazons3->setSourceFile($attachment);
                $this->amazons3->setFolderKey($folder_key);

			    // Upload file if testCase true use mock object
			    $amazons3_updload = [];
                $amazons3_updload = $this->amazons3->uploadDocument();
                // success
                $aws_uploaded_flag = 1;
                $aws_object_uri = '';
                $aws_file_version_id = '';
                $aws_response = '';
                if (isset($amazons3_updload['status']) == true && $amazons3_updload['status'] == 200) {
                    // success
                    $aws_uploaded_flag = 0;
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

                    // update 
			        $update_data['file_path'] = $folder_key;
			        $update_data['aws_object_uri'] = $aws_object_uri;
	                $update_data['aws_response'] = json_encode($aws_response);
	                $update_data['aws_uploaded_flag'] = $aws_uploaded_flag;
	                $update_data['aws_file_version_id'] = $aws_file_version_id;

			        $this->db->where("id", $item->id);
			        $this->db->update(TBL_PREFIX . "service_agreement_attachment", $update_data);

			        $file = 'Contract successfully uploaded';
			        $success_count++;
			    }
			}
			$tableTrHtml .= <<<TABLEHTML
	    	<tr>
	    		<td>$i</td>
	    		<td>$item->service_agreement_id</td>
	    		<td>$item->id</td>
	    		<td>$item->related</td>
	    		<td>$item->envelope_id</td>
	    		<td>$attachment</td>
	    		<td>$file</td>
	    		<td>$folder_key</td>
	    	</tr>
TABLEHTML;
			$i++;
		}

		$total_offset = 0;
		if ($limit != 0) {
            if ($totalData % $limit == 0) {
                $total_offset = ($totalData / $limit);
            } else {
                $total_offset = ((int) ($totalData / $limit)) + 1;
            }
        }

		$dataHtml = <<<TABLEHTML
		<p>Total Record : $totalData</p>
		<p>Total Offset : $total_offset</p>
		<p>Listed Record : $list_count ($current_from - $current_end)</p>
		<p>Current Offset : $g_offset</p>
		<p>Upload Success : $success_count</p>
	    <table border="1" style="width: 100%; border-collapse: collapse;">
	    	$tableThHtml
	    	$tableTrHtml
	    </table>
TABLEHTML;
	    echo $dataHtml;
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
  public function compare_service_agreement_app_s3_file_size() {
   		$this->load->library('AmazonS3');
  		$request = api_request_handler();

  		$limit = $request->limit ?? 100;
    	$g_offset = $offset = $request->offset ?? 1;
  		$offset = $offset > 0 ? $offset - 1 : $offset;
  		$current_from = $offset * $limit + 1;
  		$current_end = $g_offset * $limit;

    	$service_agreement_id = $request->service_agreement_id ?? '';
  		$service_agreement_attachment_id = $request->service_agreement_attachment_id ?? '';

    	$data = [];

    	$select = [ "saa.*" ];
    	$where = [];

    	if ($service_agreement_id != '') {
    		$where['service_agreement_id'] = $service_agreement_id;
    	}

    	if ($service_agreement_attachment_id != '') {
    		$where['id'] = $service_agreement_attachment_id;
    	}

    	if ($service_agreement_id == '' && $service_agreement_attachment_id == '') {
    		$this->db->where("saa.signed_status", 1);
    		$this->db->where("saa.aws_uploaded_flag", 0);
    	}

        $this->db
            ->select($select)
            ->where($where)
            ->from('tbl_service_agreement_attachment as saa');
        $this->db->limit($limit, ($offset * $limit));
        $query = $this->db->get();
        // save result if nom_rows not equal to zero
        if ($query->num_rows() > 0) {
            $data = $query->result();
        }

        $list_count = $query->num_rows();
        // total record
        $query_tot = $this->db
            ->select($select)
            ->where($where)
            ->get('tbl_service_agreement_attachment as saa');
        
        $totalData = $query_tot->num_rows();

        $dataHtml = '';
        $tableThHtml = '';
        $tableTrHtml = '';

        if($query->num_rows() < 1) {
	      $tableTrHtml = "<tr><td colspan='7'>Nothing to process. All Done!</td></tr>";
	    }

	    $tableThHtml = <<<TABLEHTML
    	<tr>
    		<th align="left">S.No</th>
    		<th align="left">Service Agreement ID</th>
    		<th align="left">Attachment Id</th>
    		<th align="left">Related</th>
    		<th align="left">Envelope Id</th>
    		<th align="left">File Path</th>
    		<th align="left">Document</th>
    		<th align="left">S3 Path</th>
    	</tr>
TABLEHTML;
		$i = 1;
		$success_count = 0;
		$folder_key = '';
		foreach($data as $item) {
			$attachment = FCPATH. SERVICE_AGREEMENT_CONTRACT_PATH . $item->id . '/' .$item->signed_file;
			// Check file exist
			if(!file_exists($attachment)) {
				$file = 'File Not Exist.';
			}

			$folder_key = $item->file_path;
			// Check if file path have value
      		if(!$item->file_path) {
      			$file = 'File Not Uploaded.';
      		} else {
      			$file = 'File Uploaded.';
      			$this->amazons3->setFolderKey($item->file_path);
      			$amazons3_get = $this->amazons3->getDocument();

      			// check get attachment from s3
      			if(isset($amazons3_get) && $amazons3_get['status'] == 200) {
			        $s3_file_length = $amazons3_get['data']['ContentLength'];
			        $server_file_size = filesize($attachment);
			        if($s3_file_length != $server_file_size || $s3_file_length == "0") {
			        	$file = 'File Size Not Matched. S3 Size - '.$s3_file_length.' Org Size - '.$server_file_size;
			        } else {
			        	$file = 'File Size Matched.';
			        	 // update 
		                $update_data['aws_uploaded_flag'] = 1;

				        $this->db->where("id", $item->id);
				        $this->db->update(TBL_PREFIX . "service_agreement_attachment", $update_data);
				        $success_count++;
			        }
			    } else {
			    	$file = 'File Not Uploaded.';
			    }
      		}
      		
		$tableTrHtml .= <<<TABLEHTML
	    	<tr>
	    		<td>$i</td>
	    		<td>$item->service_agreement_id</td>
	    		<td>$item->id</td>
	    		<td>$item->related</td>
	    		<td>$item->envelope_id</td>
	    		<td>$attachment</td>
	    		<td>$file</td>
	    		<td>$folder_key</td>
	    	</tr>
TABLEHTML;
			$i++;
		}

		$total_offset = 0;
		if ($limit != 0) {
            if ($totalData % $limit == 0) {
                $total_offset = ($totalData / $limit);
            } else {
                $total_offset = ((int) ($totalData / $limit)) + 1;
            }
        }

		$dataHtml = <<<TABLEHTML
		<p>Total Record : $totalData</p>
		<p>Total Offset : $total_offset</p>
		<p>Listed Record : $list_count ($current_from - $current_end)</p>
		<p>Current Offset : $g_offset</p>
		<p>Update Success : $success_count</p>
	    <table border="1" style="width: 100%; border-collapse: collapse;">
	    	$tableThHtml
	    	$tableTrHtml
	    </table>
TABLEHTML;
	    echo $dataHtml;
    }
}