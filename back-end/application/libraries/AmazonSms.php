<?php
/**
 * Class: AmazonSms
 * This library used to publish the text message to phone number directly
 */

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'third_party/aws/aws-autoloader.php';


use Aws\Common\Aws;
use Aws\S3\S3Client;
use Aws\Sns\SnsClient; 
use Aws\Exception\AwsException;
use Aws\Sns\Exception\SnsException;
use Aws\S3\Exception\S3Exception;

class AmazonSms {

	Protected $version = '2010-03-31';
    Protected $location_region = 'ap-southeast-2';
    
    Public $sms_type_promo = 'Promotional';    
    Public $sms_type_trans = 'Transactional';
    Public $country_code_aus = '+61';

    Private $phone_number;
    Private $message;
    Private $default_sms_type = 'Transactional';
    
    /**
     * Set phone number
     */
    public function setPhoneNumber($phone_number) {
        $country_code = getenv('SMS_COUNTRY_CODE')?? $this->country_code_aus;
        return $this->phone_number =  $country_code . $phone_number;
    }

    /**
     * Set message
     */
    public function setMessage($message) {
        return $this->message = $message;
    }

    /**
     * Set phone number
     */
    public function setDefaultSMSType($default_sms_type) {
        return $this->default_sms_type = $default_sms_type;
    }

    /**
     * Set AWS config using Sns Client
     */
    public function getConfig() {

        // Instantiate the Sns client
        $SnSclient = new SnsClient([
	    	'region' => $this->location_region,
	    	'version' => $this->version,
            'credentials' => [
                'key'    => getenv('AWS_ACCESS_KEY_ID')?? '',
                'secret' => getenv('AWS_SECRET_ACCESS_KEY')?? '',
            ],
		]);
        $SnSclient->SetSMSAttributes([
            'attributes' => [
                'DefaultSMSType' => $this->default_sms_type,
            ],
        ]);
        return $SnSclient;
    }

    /**
     * Check the phone number
     */
    public function checkIfPhoneNumberIsOptedOut() {
    	try {
            $validate_res = $this->validate();
            $status = $validate_res['status'];

            if ($status == 200) {
				# get config
                $SnSclient = $this->getConfig();

                $result = $SnSclient->checkIfPhoneNumberIsOptedOut([
			        'phoneNumber' => $this->phone_number,
			    ]);
                
				return [ 'status' => 200, 'msg' => 'Check phone number successfully.', 'data' => $result ];
            } else {
                return $validate_res;
            }
        } catch (AwsException $e) {
		    // Catch an Aws specific exception.
            return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e->toArray() ];
		}
	}

	/**
     * List the phone number
     */
    public function listPhoneNumbersOptedOut() {
	try {
			# get config
            $SnSclient = $this->getConfig();

            $result = $SnSclient->listPhoneNumbersOptedOut([]);
            
			return [ 'status' => 200, 'msg' => 'List phone number successfully.', 'data' => $result ];
        } catch (AwsException $e) {
		    // Catch an Aws specific exception.
            return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e->toArray() ];
		}
	}

	/**
	 * Get default sms type
	 */
	public function setSMSAttributes() {
		try {
			# get config
            $SnSclient = $this->getConfig();

            $result = $SnSclient->SetSMSAttributes([
		        'attributes' => [
		            'DefaultSMSType' => $this->default_sms_type,
		        ],
		    ]);
            
			return [ 'status' => 200, 'msg' => 'Set Sms Attribute default sms type successfully.', 'data' => $result ];

        } catch (AwsException $e) {
		    // Catch an Aws specific exception.
            return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e->toArray() ];
		}
	}

	/**
	 * Get default sms type
	 */
	public function getSMSAttributes() {
		try {
			# get config
            $SnSclient = $this->getConfig();

            $result = $SnSclient->getSMSAttributes([
		        'attributes' => ['DefaultSMSType'],
		    ]);
            
			return [ 'status' => 200, 'msg' => 'Get Sms Attribute default sms type successfully.', 'data' => $result ];

        } catch (AwsException $e) {
		    // Catch an Aws specific exception.
            return [ 'status' => 400, 'error' =>  $e->getMessage(), 'data' => $e->toArray() ];
		}
	}

    /**
     * Pubslish sms with default attribute type
     */
    public function publishSms() {
    	try {
            $validate_res = $this->validate();
            $status = $validate_res['status'];

            if ($status == 200) {
				# get config
                $SnSclient = $this->getConfig();

                $data = [
			        'Message' => $this->message,
			        'PhoneNumber' =>$this->phone_number,
			    ];

                $result = $SnSclient->publish($data);
                
				return [ 'status' => 200, 'msg' => 'Sms sent successfully.', 'data' => $result->toArray() ];
            } else {
                return $validate_res;
            }
        } catch (SnsException $e) {
		    // Catch an Aws specific exception.
            return [ 'status' => $e->getStatusCode(), 'error' =>  $e->getAwsErrorMessage(), 'error_type' => $e->getAwsErrorCode(), 'data'=> $e->getResult()];
		} 
    }

    /**
     * Validate before publish
     */
    public function validate() {
        try {
            if (!$this->phone_number) {
                return [ 'status' => 400, 'error' => 'PhoneNumber is Missing', 'data' => [] ];
            }
            if (!$this->message) {
                return [ 'status' => 400, 'error' => 'Message is empty', 'data' => [] ];
            }
            return [ 'status' => 200, 'msg' => 'Good to send sms', 'data' => [] ];
        } catch (Exception $e) {
            // Catch an Aws specific exception.
            return [ 'status' => 400, 'error' => 'Something went wrong', 'data' => $e->toArray() ];
        }
    }

}