<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class DocuSignEnvelope extends DocuSignAuthConfig { 
	public function __construct()
    {
        require_once APPPATH.'third_party/docusign/vendor/autoload.php';
		require_once APPPATH.'libraries/DocuSignAuthConfig.php';
    }
	
	/*
	Method CreateEnvelope (this method send single document for create envelop)
	
	Request 
	Array([userdetails] => Array ( [name] => Developer Test [email_subject] => Please sign the test document  [email] => developer@yourdevelopmentteam.com.au )
    [document] => Array([doc_id] => 1  [doc_name] => Dev Test  [doc_path] => D:\xampp\htdocs\ocs_admin\admin\back-end\uploads/docs/SignTest122.pdf , [web_hook_url]='https://adminapi.dev.healthcaremgr.net/test_mail.php' )
	[position] => Array ([position_x] => 100 [position_y] => 100 [document_id] => 1 [page_number] => 1 [recipient_id] => 1))
	
	Response 
	Array( [status] => 1 [msg] => Envelope created successfully
    [response] => Array([envelopeId] => e65c7b47-b240-4283-be37-a64b943d437c [status] => sent [statusDateTime] => 2019-09-12T09:59:02.5338440Z [uri] => /envelopes/e65c7b47-b240-4283-be37-a64b943d437c )) */



	public function CreateEnvelope($envlopeDetails){
			$validate_response=$this->validateCreateEnvelope($envlopeDetails);
			if(!$validate_response['status']){
				return $validate_response;
				exit();
			}		
			
			try{
				$userDetails=$envlopeDetails['userdetails'];
				$userDocument=$envlopeDetails['document'];
				$DocumentSignPosition=$envlopeDetails['position'];
				$DocumentTextPosition=$envlopeDetails['position_text']??[];
				$DocumentCheckBoxPosition=$envlopeDetails['position_checkbox']??[];
				$DocumentRadioPosition=$envlopeDetails['position_radio']??[];
				$DocumentDateSignedPosition=$envlopeDetails['position_date_signed']??[];
				$ccUser=$envlopeDetails['cc_user']??[];
				$DocumentNumberPosition=$envlopeDetails['position_number']??[];

				$config=$this->getConfig();			
				$apiClient = new DocuSign\eSign\ApiClient($config);			
            	$authenticationApi = new DocuSign\eSign\Api\AuthenticationApi($apiClient);            
            	$options = new \DocuSign\eSign\Api\AuthenticationApi\LoginOptions();
            	$loginInformation = $authenticationApi->login($options);			
				$loginAccount = $loginInformation->getLoginAccounts()[0];
            	$accountId = $loginAccount->getAccountId();

				if($this->validateAccountId($accountId)){
					return ['status' => false, 'msg' => 'Something went wrong in config details.'];
					exit();
				}
			
				$event_notification=$this->SetEventNotificationDetails($userDocument['web_hook_url']);		

				$document = new \DocuSign\eSign\Model\Document();
				$document->setDocumentId($userDocument['doc_id']);
				$document->setName($userDocument['doc_name']);
				$document->setDocumentBase64(base64_encode(file_get_contents($userDocument['doc_path'])));


				// Here set sign position 
				$signPosition=$this->SignPosition($DocumentSignPosition);
				$textPosition=!empty($DocumentTextPosition) ? $this->textBoxPosition($DocumentTextPosition):[];
				$checkBoxPosition=!empty($DocumentCheckBoxPosition) ? $this->checkBoxPosition($DocumentCheckBoxPosition):[];
				$radioPosition=!empty($DocumentRadioPosition) ? $this->radioBoxPosition($DocumentRadioPosition):[];
				$dateSignedFields=!empty($DocumentDateSignedPosition) ? $this->signerDatePosition($DocumentDateSignedPosition):[];
				$ccUserDetails=!empty($ccUser) ? $this->recipientCc($ccUser):[];
				$numberBoxPosition=!empty($DocumentNumberPosition) ? $this->numberBoxPosition($DocumentNumberPosition):[];
				#pr($dateSignedFields);
				$tabs = new DocuSign\eSign\Model\Tabs();
				$tabs->setSignHereTabs($signPosition);
				if(!empty($textPosition)){
					$tabs->setTextTabs($textPosition);	
				}
				if(!empty($checkBoxPosition)){
					$tabs->setCheckboxTabs($checkBoxPosition);
				}
				if(!empty($radioPosition)){
					$tabs->setRadioGroupTabs($radioPosition);
				}
				if(!empty($dateSignedFields)){
					$tabs->setDateSignedTabs($dateSignedFields);
				}
				if(!empty($numberBoxPosition)){
					$tabs->setNumberTabs($numberBoxPosition);
				}
				// Here details of signer users
				$signerDetails=$this->SignerDetails($tabs,$userDetails);
				$recipients = new \DocuSign\eSign\Model\Recipients();
				$recipients->setSigners($signerDetails);

				if (isset($ccUserDetails) == true && !empty($ccUserDetails) == true) {
					$recipients->setCarbonCopies($ccUserDetails);
				}

				$envelope_definition = new \DocuSign\eSign\Model\EnvelopeDefinition();
				// We want to use the most friendly email subject line.
				// The regexp below removes the suffix from the file name. 
				$envelope_definition->setEmailSubject($userDetails['email_subject']);
                                $envelope_definition->setEmailBlurb($userDetails['email_message'] ?? "");
                                $envelope_definition->setAllowMarkup(false);
                                $envelope_definition->setBrandId(DS_BRAND_ID);
				$envelope_definition->setDocuments([$document]);
				$envelope_definition->setRecipients($recipients);
				$envelope_definition->setEventNotification($event_notification);
				$envelope_definition->setStatus("sent");
				// Send the envelope:
				$envelopesApi = new \DocuSign\eSign\Api\EnvelopesApi($apiClient);
				$envelope_summary = $envelopesApi->createEnvelope($accountId, $envelope_definition, null);
				// Send Envelope sign pdf to mail code
				
				/*$envelopeId='116dacdc-a4c8-446c-a1ab-09287c59c85c'; //$envelope_summary->getEnvelopeId();
				$recipientViewRequest = new DocuSign\eSign\Model\RecipientViewRequest([
					'authentication_method' => $authenticationMethod, 
					'recipient_id' => '1', 'return_url' => 'https://www.docusign.com/devcenter',
					'user_name' => $userDetails['name'], 'email' => $userDetails['email']
				]);
				//'client_user_id' => $clientUserId,
				$results = $envelopesApi->createRecipientView($accountId, $envelopeId,$recipientViewRequest); */

				if ( !isset($envelope_summary) || $envelope_summary->getEnvelopeId() == null ) {
					return ['status' => false, 'msg' => 'Error calling DocuSign'];
				}else{
					return ['status' => true, 'msg' => 'Envelope created successfully','response'=>json_decode((string)$envelope_summary, true)];
				}			
			}catch (DocuSign\eSign\ApiException $e) {
				$error_code = $e->getResponseBody()->errorCode;
				$error_message = $e->getResponseBody()->message;			
				return ['status' => false, 'msg' => $error_message,'error_code' => $error_code];
				exit();
			}
	}
	
	/*
		Method GetEnvelopeInformation (This method get envelope information using envelope id)
		Request 
		$envelopeId (set parameter envelope id only)

		Response 
		Array([status] => 1 [msg] => successfully
    	[response] => Array([allowMarkup] => false [allowReassign] => true [attachmentsUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/attachments
            [autoNavigation] => true [certificateUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/documents/certificate
            [completedDateTime] => 2019-09-12T06:02:06.0830000Z [createdDateTime] => 2019-09-12T05:58:55.0800000Z
            [customFieldsUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/custom_fields
            [deliveredDateTime] => 2019-09-12T06:01:58.6930000Z [documentsCombinedUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/documents/combined
            [documentsUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/documents [emailSubject] => Please sign the test document
            [enableWetSign] => true [envelopeId] => ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0 [envelopeIdStamping] => true
            [envelopeUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0 [initialSentDateTime] => 2019-09-12T05:58:55.7370000Z
            [is21CFRPart11] => false [isSignatureProviderEnvelope] => false [lastModifiedDateTime] => 2019-09-12T05:58:55.0970000Z
            [notificationUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/notification [purgeState] => unpurged
            [recipientsUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/recipients [sentDateTime] => 2019-09-12T05:58:55.7370000Z
            [status] => completed [statusChangedDateTime] => 2019-09-12T06:02:06.0830000Z [templatesUri] => /envelopes/ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0/templates))
	*/

	public function GetEnvelopeInformation($envelopeId) {

		if(empty($envelopeId)){
			return ['status' => false, 'msg' => 'please provide envelope Id!'];
			exit();
		}


		try {
			$config=$this->getConfig();			
			$apiClient = new DocuSign\eSign\ApiClient($config);			
			$authenticationApi = new DocuSign\eSign\Api\AuthenticationApi($apiClient);            
			$options = new \DocuSign\eSign\Api\AuthenticationApi\LoginOptions();
			$loginInformation = $authenticationApi->login($options);			
			$loginAccount = $loginInformation->getLoginAccounts()[0];
			$accountId = $loginAccount->getAccountId();
			if($this->validateAccountId($accountId)){
				return ['status' => false, 'msg' => 'Something went wrong in config details.'];
				exit();
			}

			$envelopeApi = new DocuSign\eSign\Api\EnvelopesApi($apiClient);
			$options = new \DocuSign\eSign\Api\EnvelopesApi\GetEnvelopeOptions();			
			$envelope = $envelopeApi->getEnvelope($accountId,$envelopeId);
			$results = json_decode((string)$envelope, true);
			if(!empty($results)) {
				return ['status' => true, 'msg' => 'successfully','response' => $results];
				exit();	
			}else{
				return ['status' => false, 'msg' => 'something went wrong'];
				exit();	
			}
			
		} catch (\DocuSign\eSign\ApiException $e) {
			$error_code = $e->getResponseBody()->errorCode;
			$error_message = $e->getResponseBody()->message;			
			return ['status' => false, 'msg' => $error_message,'error_code' => $error_code];
			exit();
		}
	}

	/*
		Method DownloadEnvelopeDocuments (This method download envelope document using envelope id, filepath and filename)
		Request 
		Array([envelopeid] => ac1706e1-3b1e-4b98-a51a-fdc5147fc0c0 [filepath] => D:\xampp\htdocs\ocs_admin\admin\back-end\uploads/docs/  [filename] => mytest123.pdf)

		Response 
		Array([status] => 1 [msg] => successfully)
	*/

	public function DownloadEnvelopeDocuments($envlopDetails)
    {
		
		try {
			$filepath=$envlopDetails['filepath'].$envlopDetails['filename'];
			$document_id=1;
			$config=$this->getConfig();			
			$apiClient = new DocuSign\eSign\ApiClient($config);			
			$authenticationApi = new DocuSign\eSign\Api\AuthenticationApi($apiClient);            
			$options = new \DocuSign\eSign\Api\AuthenticationApi\LoginOptions();
			$loginInformation = $authenticationApi->login($options);			
			$loginAccount = $loginInformation->getLoginAccounts()[0];
			$accountId = $loginAccount->getAccountId();
			if($this->validateAccountId($accountId)){
				return ['status' => false, 'msg' => 'Something went wrong in config details.'];
				exit();
			}
			$envelopeApi = new DocuSign\eSign\Api\EnvelopesApi($apiClient);
					
			$results = $envelopeApi->getDocument($accountId, $document_id, $envlopDetails['envelopeid']);							
			if(!empty($results->getPathname())){
							
				if(copy($results->getPathname(), $filepath)){
					return ['status' => true, 'msg' => 'successfully'];
					exit();	
				}else{
					return ['status' => false, 'msg' => 'file not exist'];
					exit();	
				}		
				
			}else{
				return ['status' => false, 'msg' => 'something went wrong'];
				exit();	
			}
			
		} catch (\DocuSign\eSign\ApiException $e) {
			$error_code = $e->getResponseBody()->errorCode;
			$error_message = $e->getResponseBody()->message;			
			return ['status' => false, 'msg' => $error_message,'error_code' => $error_code];
			exit();
		}


	}
	
	public function setDocumentDetails(){
		$document = new \DocuSign\eSign\Model\Document();
		$document->setDocumentId("1");
		$document->setName('Test Doc');
		$document->setDocumentBase64(base64_encode(file_get_contents($documentFileName)));
		return [$document];
	}

	public function SetEventNotificationDetails($web_hook_url){
		
		$envelope_events = [
			(new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("sent"),
			(new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("delivered"),
			(new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("completed"),
			(new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("declined"),
			(new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("voided"),
			(new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("sent"),
			(new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("sent")
		];

		$recipient_events = [
			(new \DocuSign\eSign\Model\RecipientEvent())->setRecipientEventStatusCode("Sent"),
			(new \DocuSign\eSign\Model\RecipientEvent())->setRecipientEventStatusCode("Delivered"),
			(new \DocuSign\eSign\Model\RecipientEvent())->setRecipientEventStatusCode("Completed"),
			(new \DocuSign\eSign\Model\RecipientEvent())->setRecipientEventStatusCode("Declined"),
			(new \DocuSign\eSign\Model\RecipientEvent())->setRecipientEventStatusCode("AuthenticationFailed"),
			(new \DocuSign\eSign\Model\RecipientEvent())->setRecipientEventStatusCode("AutoResponded")
		];
		
		$event_notification = new \DocuSign\eSign\Model\EventNotification();
		$event_notification->setUrl($web_hook_url);
		$event_notification->setLoggingEnabled("true");
		$event_notification->setRequireAcknowledgment("true");
		$event_notification->setUseSoapInterface("false");
		$event_notification->setIncludeCertificateWithSoap("false");
		$event_notification->setSignMessageWithX509Cert("false");
		$event_notification->setIncludeDocuments("true");
		$event_notification->setIncludeEnvelopeVoidReason("true");
		$event_notification->setIncludeTimeZone("true");
		$event_notification->setIncludeSenderAccountAsCustomField("true");
		$event_notification->setIncludeDocumentFields("true");
		$event_notification->setIncludeCertificateOfCompletion("true");
		$event_notification->setEnvelopeEvents($envelope_events);
		$event_notification->setRecipientEvents($recipient_events);
		return $event_notification;

	}
	public function SignerDetails($tabs,$signUser){
		$signer = new \DocuSign\eSign\Model\Signer();
		//$signerDetails['name']='Developer Test';
		//$signerDetails['email_subject']='Please sign the test document';
		//$signerDetails['email']='developer@yourdevelopmentteam.com.au';

		$signer->setEmail($signUser['email']);
		$signer->setName($signUser['name']);
		$signer->setRecipientId($signUser['recipient_id'] ?? "1");
		$signer->setRoutingOrder($signUser['routing_order'] ?? "1");
		$signer->setTabs($tabs);
		return [$signer];
	}
	public function SignPosition($SignPosition=array()){
		// Create a |SignHere| tab somewhere on the document for the recipient to sign		
		$isMultipleSignPOsition = $this->check_data_multidimensional_array($SignPosition);
		$sinPosition = [];
		$SignPositionData = $isMultipleSignPOsition ? $SignPosition : [$SignPosition];
		foreach($SignPositionData as $SignPosition){
		$signHere = new \DocuSign\eSign\Model\SignHere();
		$signHere->setXPosition($SignPosition['position_x']);
		$signHere->setYPosition($SignPosition['position_y']);
		$signHere->setDocumentId($SignPosition['document_id']);
		$signHere->setPageNumber($SignPosition['page_number']);
		$signHere->setRecipientId($SignPosition['recipient_id']);
		$sinPosition[]=$signHere;
		}
		return $sinPosition;
	}

	// Add document signed date position
	public function signerDatePosition($dateSignedPosition)
	{
		$dateSigned = [];
		$index = 0;
		foreach($dateSignedPosition as $SignPosition){		    
		    $signerId = $SignPosition['recipient_id'];
			$xPos = $SignPosition['position_x'];
			$yPos = $SignPosition['position_y'];
			$documentId = $SignPosition['document_id'];
			$pageNumber = $SignPosition['page_number'];

		    $dateSignedFields = new DocuSign\eSign\Model\DateSigned();

		    $dateSignedFields->setPageNumber($pageNumber);
		    $dateSignedFields->setDocumentId($documentId);
		    $dateSignedFields->setRecipientId($signerId);
		    $dateSignedFields->setName('Date Signed');
		    $dateSignedFields->setTabLabel('Date Signed');
		    $dateSignedFields->setXPosition($xPos);
		    $dateSignedFields->setYPosition($yPos);
		    $dateSigned[] = $dateSignedFields;
		}
	    return ($dateSigned);
	}
	
	// Add document signed date position
	public function signerDatePositionOld($dateSignedPosition)
	{
	    $index = 0;
	    $signerId = $dateSignedPosition[0]['recipient_id'];
		$xPos = $dateSignedPosition[0]['position_x'];
		$yPos = $dateSignedPosition[0]['position_y'];
		$documentId = $dateSignedPosition[0]['document_id'];
		$pageNumber = $dateSignedPosition[0]['page_number'];

	    $dateSignedFields = new DocuSign\eSign\Model\DateSigned();

	    $dateSignedFields->setPageNumber($pageNumber);
	    $dateSignedFields->setDocumentId($documentId);
	    $dateSignedFields->setRecipientId($signerId);
	    $dateSignedFields->setName('Date Signed');
	    $dateSignedFields->setTabLabel('Date Signed');
	    $dateSignedFields->setXPosition($xPos);
	    $dateSignedFields->setYPosition($yPos);

	    return array($dateSignedFields);
	}
	/*
	Method validateAccountId
	this method check account id is empty 
	*/

	public function validateAccountId($accountid){
		return $response=isset($accountid)? false:true;		
	}

	/*
	Method validateCreateEnvelope
	this method validate create envelope data is valid or not	
	*/
	public function validateCreateEnvelope($envlopeDetails){
		if(empty($envlopeDetails)){
			return ['status' => false, 'msg' =>'Given Array is empty'];
			exit();
		}
		
		//validate Signer details

		if(!isset($envlopeDetails['userdetails']['name']) || empty($envlopeDetails['userdetails']['name'])){
			return ['status' => false, 'msg' =>'Please provide signer details name !'];
			exit();
		}
		if(!isset($envlopeDetails['userdetails']['email_subject']) || empty($envlopeDetails['userdetails']['email_subject'])){
			return ['status' => false, 'msg' =>'Please provide signer email subject !'];
			exit();
		}
		if(!isset($envlopeDetails['userdetails']['email']) || empty($envlopeDetails['userdetails']['email'])){
			return ['status' => false, 'msg' =>'Please provide signer email !'];
			exit();
		}

		
		// Validate position details

		$postion['position'] = $this->check_data_multidimensional_array($envlopeDetails['position']) ? $envlopeDetails['position'][0] : $envlopeDetails['position'];
		if(!isset($postion['position']['position_x']) || empty($postion['position']['position_x'])){
			return ['status' => false, 'msg' =>'Please provide envelope X position !'];
			exit();
		}
		if(!isset($postion['position']['position_y']) || empty($postion['position']['position_y'])){
			return ['status' => false, 'msg' =>'Please provide envelope Y position !'];
			exit();
		}
		if(!isset($postion['position']['document_id']) || empty($postion['position']['document_id'])){
			return ['status' => false, 'msg' =>'Please provide envelope document id !'];
			exit();
		}
		if(!isset($postion['position']['page_number']) || empty($postion['position']['page_number'])){
			return ['status' => false, 'msg' =>'Please provide envelope document page number !'];
			exit();
		}
		if(!isset($postion['position']['recipient_id']) || empty($postion['position']['recipient_id'])){
			return ['status' => false, 'msg' =>'Please provide envelope recipient id !'];
			exit();
		}

		// Validate document details

		if(!isset($envlopeDetails['document']['doc_id']) || empty($envlopeDetails['document']['doc_id'])){
			return ['status' => false, 'msg' =>'Please provide document id !'];
			exit();
		}
		if(!isset($envlopeDetails['document']['doc_name']) || empty($envlopeDetails['document']['doc_name'])){
			return ['status' => false, 'msg' =>'Please provide document name !'];
			exit();
		}
		if(!isset($envlopeDetails['document']['web_hook_url']) || empty($envlopeDetails['document']['web_hook_url'])){
			return ['status' => false, 'msg' =>'Please provide web hook url !'];
			exit();
		}
		

		if(!isset($envlopeDetails['document']['doc_path']) || empty($envlopeDetails['document']['doc_path'])){
			return ['status' => false, 'msg' =>'Please provide document path !'];
			exit();
		}else{
			$absPath=realpath($envlopeDetails['document']['doc_path']);
			if($absPath === false) {
				return ['status' => false, 'msg' =>'Please provide valid document path !'];
				exit();
			}
		}
		return ['status' => true];
		exit();
	}
	
	public function ResendEnvelope($envlopeDetails){

		#pr($envlopeDetails);

		$validate_response=$this->validateResendEnvelope($envlopeDetails);
		if(!$validate_response['status']){
			return $validate_response;
			exit();
		}		
		
		try{
			
			$userDetails=$envlopeDetails['userdetails'];
			$envelope_id=$envlopeDetails['envelopeId'];
			//$userDocument=$envlopeDetails['document'];
			$DocumentSignPosition=$envlopeDetails['position'];

			$config=$this->getConfig();			
			$apiClient = new DocuSign\eSign\ApiClient($config);			
			$authenticationApi = new DocuSign\eSign\Api\AuthenticationApi($apiClient);            
			$options = new \DocuSign\eSign\Api\AuthenticationApi\LoginOptions();
			$loginInformation = $authenticationApi->login($options);			
			$loginAccount = $loginInformation->getLoginAccounts()[0];
			$accountId = $loginAccount->getAccountId();

			if($this->validateAccountId($accountId)){
				return ['status' => false, 'msg' => 'Something went wrong in config details.'];
				exit();
			}
		
			
			// Here set sign position 
			$signPosition=$this->SignPosition($DocumentSignPosition);
			$tabs = new DocuSign\eSign\Model\Tabs();
			//$tabs->setSignHereTabs($signPosition); set position here
		
			// Here details of signer users
			$signerDetails=$this->SignerDetails($tabs,$userDetails);

			$recipients = new \DocuSign\eSign\Model\Recipients();
			$recipients->setSigners($signerDetails);
			
			$envelopesApi = new \DocuSign\eSign\Api\EnvelopesApi($apiClient);
						
			$updateRecipientsOptions = new \DocuSign\eSign\Api\EnvelopesApi\UpdateRecipientsOptions();				
			$updateRecipientsOptions->setResendEnvelope('true');
			$envelope_summary = $envelopesApi->updateRecipients($accountId,$envelope_id,$recipients,$updateRecipientsOptions); 
			
			$error_details=$envelope_summary->getRecipientUpdateResults();
						
			if (isset($error_details[0]['error_details']['error_code']) || $error_details[0]['error_details']['error_code'] == 'SUCCESS' ) {
				return ['status' => true, 'msg' => 'Resend Envelope successfully','response'=>json_decode((string)$envelope_summary, true)];
				exit();
			}else{
				return ['status' => false, 'msg' => 'Error calling DocuSign'];
				exit();
			}			
		}catch (DocuSign\eSign\ApiException $e) {
			$error_code = $e->getResponseBody()->errorCode;
			$error_message = $e->getResponseBody()->message;			
			return ['status' => false, 'msg' => $error_message,'error_code' => $error_code];
			exit();
		}
}

public function validateResendEnvelope($envlopeDetails){
	if(empty($envlopeDetails)){
		return ['status' => false, 'msg' =>'Given Array is empty'];
		exit();
	}
	
	//validate Signer details

	if(!isset($envlopeDetails['userdetails']['name']) || empty($envlopeDetails['userdetails']['name'])){
		return ['status' => false, 'msg' =>'Please provide signer details name !'];
		exit();
	}
	//if(!isset($envlopeDetails['userdetails']['email_subject']) || empty($envlopeDetails['userdetails']['email_subject'])){
	//	return ['status' => false, 'msg' =>'Please provide signer email subject !'];
	//	exit();
	//}
	if(!isset($envlopeDetails['userdetails']['email']) || empty($envlopeDetails['userdetails']['email'])){
		return ['status' => false, 'msg' =>'Please provide signer email !'];
		exit();
	}

	
	// Validate position details

	if(!isset($envlopeDetails['position']['position_x']) || empty($envlopeDetails['position']['position_x'])){
		return ['status' => false, 'msg' =>'Please provide envelope X position !'];
		exit();
	}
	if(!isset($envlopeDetails['position']['position_y']) || empty($envlopeDetails['position']['position_y'])){
		return ['status' => false, 'msg' =>'Please provide envelope Y position !'];
		exit();
	}
	if(!isset($envlopeDetails['position']['document_id']) || empty($envlopeDetails['position']['document_id'])){
		return ['status' => false, 'msg' =>'Please provide envelope document id !'];
		exit();
	}
	if(!isset($envlopeDetails['position']['page_number']) || empty($envlopeDetails['position']['page_number'])){
		return ['status' => false, 'msg' =>'Please provide envelope document page number !'];
		exit();
	}
	if(!isset($envlopeDetails['position']['recipient_id']) || empty($envlopeDetails['position']['recipient_id'])){
		return ['status' => false, 'msg' =>'Please provide envelope recipient id !'];
		exit();
	}

	// Validate document details
	
	if(!isset($envlopeDetails['envelopeId']) || empty($envlopeDetails['envelopeId'])){
		return ['status' => false, 'msg' =>'Please provide envelope id !'];
		exit();
	}
	
	return ['status' => true];
	exit();
}
function check_data_multidimensional_array($data=[]){
	return !empty($data) && is_array($data) && is_array(current($data)) ? true :false;
}

public function textBoxPosition($textPosition=array()){
	// Create a |SignHere| tab somewhere on the document for the recipient to sign		
	$isMultipleTextPosition = $this->check_data_multidimensional_array($textPosition);
	$textDataPosition = [];
	$textPositionData = $isMultipleTextPosition ? $textPosition : [$textPosition];
	foreach($textPositionData as $textPosition){
	$textHere = new \DocuSign\eSign\Model\Text();
	$textHere->setXPosition($textPosition['position_x']??0);
	$textHere->setYPosition($textPosition['position_y']??0);
	$textHere->setDocumentId($textPosition['document_id']??1);
	$textHere->setName($textPosition['name'] ?? '');
	$textHere->setRequired($textPosition['required']??false);
	$textHere->setPageNumber($textPosition['page_number']??0);
	$textHere->setRecipientId($textPosition['recipient_id']??1);
	$textHere->setHeight($textPosition['height']??20);
	$textHere->setMaxLength($textPosition['max_length']??1000);
	$textHere->setValue($textPosition['value']??'');
	$textHere->setWidth($textPosition['width']??100);
	$textHere->setTabOrder($textPosition['tab_order']??1);
	$textHere->setTabLabel($textPosition['tab_label']??null);
	$textHere->setTabId($textPosition['tab_label']??null);
 	$textHere->setRequireInitialOnSharedChange($textPosition['Require_initial']??'false');
	$textHere->setShared($textPosition['shared']??'false');
	$textHere->setTabLabel($textPosition['tab_label']??null);
	$textHere->setConditionalParentValue($textPosition['conditional_parent_value']??null);
	$textHere->setConditionalParentLabel($textPosition['conditional_parent_label']??null);
	$textDataPosition[]=$textHere;
	}
	return $textDataPosition;
}

/*
	Method numberBoxPosition
	this method will create numberbox and will set its properties	
	*/	
public function numberBoxPosition($numberPosition=array()){
	$isMultipleNumberPosition = $this->check_data_multidimensional_array($numberPosition);
	$numberDataPosition = [];
	$numberPositionData = $isMultipleNumberPosition ? $numberPosition : [$numberPosition];
	foreach($numberPositionData as $numberPosition){
	    $numberBoxHere = new \DocuSign\eSign\Model\Number();
	    $numberBoxHere->setXPosition($numberPosition['position_x']??0);
	    $numberBoxHere->setYPosition($numberPosition['position_y']??0);
	    $numberBoxHere->setDocumentId($numberPosition['document_id']??1);
	    $numberBoxHere->setName($numberPosition['name'] ?? '');
	    $numberBoxHere->setRequired($numberPosition['required']??false);
	    $numberBoxHere->setPageNumber($numberPosition['page_number']??0);
	    $numberBoxHere->setRecipientId($numberPosition['recipient_id']??1);
	    $numberBoxHere->setValue($numberPosition['value']??'');
	    $numberBoxHere->setWidth($numberPosition['width']??100);
	    $numberBoxHere->setMaxLength($numberPosition['max_length']??1000);
	    $numberBoxHere->setTabOrder($numberPosition['tab_order']??1);
	    $numberBoxHere->setTabLabel($numberPosition['tab_label']??null);
	    $numberBoxHere->setTabId($numberPosition['tab_label']??null);
 	    $numberBoxHere->setRequireInitialOnSharedChange($numberPosition['Require_initial']??'false');
	    $numberBoxHere->setShared($numberPosition['shared']??'false');
	    $numberBoxHere->setTabLabel($numberPosition['tab_label']??null);
	    $numberBoxHere->setConditionalParentValue($numberPosition['conditional_parent_value']??null);
	    $numberBoxHere->setConditionalParentLabel($numberPosition['conditional_parent_label']??null);
	    $numberDataPosition[]=$numberBoxHere;
	}
	return $numberDataPosition;
}

public function checkBoxPosition($checkBoxPosition=array()){
	// Create a |SignHere| tab somewhere on the document for the recipient to sign		
	$isMultipleCheckBoxPosition = $this->check_data_multidimensional_array($checkBoxPosition);
	$checkBoxDataPosition = [];
	$checkBoxPositionData = $isMultipleCheckBoxPosition ? $checkBoxPosition : [$checkBoxPosition];
	foreach($checkBoxPositionData as $checkBoxPosition){
	$checkBox = new \DocuSign\eSign\Model\Checkbox();
	$checkBox->setXPosition($checkBoxPosition['position_x']);
	$checkBox->setYPosition($checkBoxPosition['position_y']);
	$checkBox->setDocumentId($checkBoxPosition['document_id']??1);
	$checkBox->setPageNumber($checkBoxPosition['page_number']??0);
	$checkBox->setRecipientId($checkBoxPosition['recipient_id']??1);
	$checkBox->setName($checkBoxPosition['name']);
	$checkBox->setRequired($checkBoxPosition['required']??false);
	$checkBox->setSelected($checkBoxPosition['selected']??false);
	$checkBox->setTabOrder($checkBoxPosition['tab_order']??1);
	$checkBox->setRequireInitialOnSharedChange($checkBoxPosition['Require_initial']??false);
	$checkBox->setShared($checkBoxPosition['shared']??'false');
	$checkBox->setConditionalParentValue($checkBoxPosition['conditional_parent_value']??null);
	$checkBox->setConditionalParentLabel($checkBoxPosition['conditional_parent_label']??null);
	$checkBox->setTabLabel($checkBoxPosition['tab_label']??null);

	$checkBoxDataPosition[]=$checkBox;
	}
	return $checkBoxDataPosition;
}

public function radioBoxPosition($checkBoxPosition=array()){
	$isMultipleCheckBoxPosition = $this->check_data_multidimensional_array($checkBoxPosition);
	$checkBoxDataPosition = [];
	$checkBoxPositionData = $isMultipleCheckBoxPosition ? $checkBoxPosition : [$checkBoxPosition];
	foreach($checkBoxPositionData as $checkBoxPosition){
		$radio = [];
		foreach ($checkBoxPosition['radios'] as $val) {
			$radio[] = new \DocuSign\eSign\Model\Radio($val);
		}
		
		$checkBoxPosition['radios'] = $radio;
	$checkBox = new \DocuSign\eSign\Model\RadioGroup($checkBoxPosition);
	

	$checkBoxDataPosition[]=$checkBox;
	}
	return $checkBoxDataPosition;
}

	/**
	 * Add recipient as a carborn copy in email
	 * @param {array} recipient
	 */
	public function recipientCc($recipients)
	{
		$recipientCc = [];
		$index = 0;
		foreach($recipients as $ind => $recipient){
			$routing_order = $ind + 1;
			$carbon_copy = new \DocuSign\eSign\Model\CarbonCopy();
			$carbon_copy->setEmail($recipient['email']);
			$carbon_copy->setName($recipient['name']);
			$carbon_copy->setRecipientId($recipient['recipient_id']);
			$carbon_copy->setRoutingOrder($recipient['routing_order']);
			$recipientCc[] = $carbon_copy;

		}
		return $recipientCc;
	}
}
?>