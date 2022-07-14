<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class DS_dashboard extends MX_Controller {

	function __construct() {

		parent::__construct();
		$this->load->library(['form_validation']);
		//it is comming from tbl_company auto increment id
		$params = array('company_id' => 1);
		$this->load->library('DocuSignEnvelope');

		$this->form_validation->CI = & $this;
		$this->load->model(['Basic_model']);
	}

	public function CreateEnvelope($documentPath,$recipient_email,$name,$document_name){
		// Signer user details
		$signerDetails=array();
		$signerDetails['name']=$name;
		$signerDetails['email_subject']='Please sign the test document';
		$signerDetails['email']=$recipient_email;

		// document path details
		// $documentPath=FCPATH.'uploads/sample.pdf';
		// $web_hook_url="http://localhost/ocs_admin/admin/back-end/docusign/DS_dashboard/callback_docusign";
		$web_hook_url="https://adminapi.dev.healthcaremgr.net/docusign/DS_dashboard/callback_docusign";


		$documents=array();
		$documents['doc_id']='1';
		$documents['doc_name']=$document_name;
		$documents['doc_path']=$documentPath;
		$documents['web_hook_url']=$web_hook_url;

		// Envelope position details
		$position=array();
		$position['position_x']=100;
		$position['position_y']=100;
		$position['document_id']=1;
		$position['page_number']=1;
		$position['recipient_id']=1;

		$envlopDetails=array();
		$envlopDetails['userdetails']=$signerDetails;
		$envlopDetails['document']=$documents;
		$envlopDetails['position']=$position;

		// echo "<pre>";

		$config=$this->docusignenvelope->CreateEnvelope($envlopDetails);

		return $config['response']['envelopeId'];
		/*
			Array ( [status] => 1 [msg] => Envelope created successfully [envelope_details] => Array ( [envelopeId] => 71db8ac5-21e9-4a9b-acaa-5c54c5ee56ba [status] => sent [statusDateTime] => 2019-09-11T12:44:53.7727652Z [uri] => /envelopes/71db8ac5-21e9-4a9b-acaa-5c54c5ee56ba ) )
		*/

	}

	public function GetEnvelopeInformation(){
		$envelopeId= 'c5ad11ee-2025-4374-853a-8968d09c12be';
		$config=$this->docusignenvelope->GetEnvelopeInformation($envelopeId);
		echo "<pre>";
		print_r($config);
		/*
		Array
(
    [status] => 1
    [msg] => successfully
    [response] => Array
        (
            [allowMarkup] => false
            [allowReassign] => true
            [attachmentsUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/attachments
            [autoNavigation] => true
            [certificateUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/documents/certificate
            [completedDateTime] => 2019-09-12T06:02:06.0830000Z
            [createdDateTime] => 2019-09-12T05:58:55.0800000Z
            [customFieldsUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/custom_fields
            [deliveredDateTime] => 2019-09-12T06:01:58.6930000Z
            [documentsCombinedUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/documents/combined
            [documentsUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/documents
            [emailSubject] => Please sign the test document
            [enableWetSign] => true
            [envelopeId] => ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0
            [envelopeIdStamping] => true
            [envelopeUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0
            [initialSentDateTime] => 2019-09-12T05:58:55.7370000Z
            [is21CFRPart11] => false
            [isSignatureProviderEnvelope] => false
            [lastModifiedDateTime] => 2019-09-12T05:58:55.0970000Z
            [notificationUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/notification
            [purgeState] => unpurged
            [recipientsUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/recipients
            [sentDateTime] => 2019-09-12T05:58:55.7370000Z
            [status] => completed
            [statusChangedDateTime] => 2019-09-12T06:02:06.0830000Z
            [templatesUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/templates
        )

)
		*/

	}


	public function DownloadEnvelopeDocuments(){

		$envelopeId= 'c5ad11ee-2025-4374-853a-8968d09c12be';
		$documentPath=FCPATH.'uploads/docs/';
		$documentName='signed.pdf';

		$envlopDetails=array();
		$envlopDetails['envelopeid']=$envelopeId;
		$envlopDetails['filepath']=$documentPath;
		$envlopDetails['filename']=$documentName;

		echo "<pre>";
		$config=$this->docusignenvelope->DownloadEnvelopeDocuments($envlopDetails);
		print_r($config);

// in progress then response is
//SplFileObject Object ( [pathName:SplFileInfo:private] => C:\Users\user\AppData\Local\Temp\C75B.tmp [fileName:SplFileInfo:private] => C75B.tmp [openMode:SplFileObject:private] => w [delimiter:SplFileObject:private] => , [enclosure:SplFileObject:private] => " )

// after signed
// SplFileObject Object ( [pathName:SplFileInfo:private] => C:\Users\user\AppData\Local\Temp\C62A.tmp [fileName:SplFileInfo:private] => C62A.tmp [openMode:SplFileObject:private] => w [delimiter:SplFileObject:private] => , [enclosure:SplFileObject:private] => " )

// if delete
		//print_r($config);
	}

	public function callback_docusign(){
		$data = file_get_contents('php://input');

		$xml = simplexml_load_string ($data, "SimpleXMLElement", LIBXML_PARSEHUGE);
		if(!$xml){
			echo "null content response";
			exit();
		}
		if(empty($xml->EnvelopeStatus->EnvelopeID)){
			echo "Envelope Id not exist";
			exit();
		}
		if(empty($xml->EnvelopeStatus->TimeGenerated)){
			echo "Generated time not exist";
			exit();
		}

		$envelope_id = (string)$xml->EnvelopeStatus->EnvelopeID;
		$time_generated = (string)$xml->EnvelopeStatus->TimeGenerated;
		$where = array('envelope_id' => $envelope_id);
		$response = $this->Basic_model->get_row('crm_participant_stage_docs', "crm_participant_id", $where);
		$crm_participant_id = $response->crm_participant_id;
		$uploades='uploads/crmparticipant/'.$crm_participant_id.'/'.'Stage3'.'/';
		$envelope_dir = FCPATH.$uploades;
		if(! is_dir($envelope_dir)) {mkdir ($envelope_dir, 0755);}

		$filename = $envelope_dir . "/T" . str_replace (':' , '_' , $time_generated) . ".xml";
		$ok = file_put_contents ($filename, $data);
		if ($ok === false) {
			// Here to handel if file not generated
			echo "Xml file content not available";
			exit();
		}
		$filename = '';
		if ((string)$xml->EnvelopeStatus->Status === "Completed") {
			// Loop through the DocumentPDFs element, storing each document.
			foreach ($xml->DocumentPDFs->DocumentPDF as $pdf) {
				$filename = $envelope_id.'_'.(string)$pdf->DocumentID.'.pdf';
				$full_filename = $envelope_dir."/". $filename;
				file_put_contents($full_filename, base64_decode ( (string)$pdf->PDFBytes ));

			}

			$data2['signed_file_path'] = $uploades.$filename;
			$data2['document_signed'] = 2;
			$this->Basic_model->update_records('crm_participant_stage_docs', $data2,$where);
		}
	}



		


		public function ResendEnvelope(){
			// Signer user details
			$signerDetails=array();
			$signerDetails['name']='This is test name';
			$signerDetails['email_subject']='Please sign the test document';
			$signerDetails['email']='testteam.developer@gmail.com';
				
			// Envelope position details
			$position=array();
			$position['position_x']=100;
			$position['position_y']=100;
			$position['document_id']=1;
			$position['page_number']=1;
			$position['recipient_id']=1;

			$envelope_id='bb696d4d-fc9b-4902-8968-c518dac1b92e';

			$envlopDetails=array();
			$envlopDetails['userdetails']=$signerDetails;
			
			$envlopDetails['position']=$position;			
			$envlopDetails['envelopeId']=$envelope_id;	
			$config=$this->docusignenvelope->ResendEnvelope($envlopDetails);
			//print_r($config);
			//return $config['response']['envelopeId'];
			/*
				Array ( [status] => 1 [msg] => Envelope created successfully [envelope_details] => Array ( [envelopeId] => 71db8ac5-21e9-4a9b-acaa-5c54c5ee56ba [status] => sent [statusDateTime] => 2019-09-11T12:44:53.7727652Z [uri] => /envelopes/71db8ac5-21e9-4a9b-acaa-5c54c5ee56ba ) )
			*/
	
		}

}
