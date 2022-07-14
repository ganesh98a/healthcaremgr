<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seek - Used for prefill the candidate details from Seek
 * Client-id = Username 
 * Client-secret = Password
 *
 * @author user
 */
class Seek {
    private $username;
    private $password;
    private $advertiser_account_name;
    private $advertiser_account_id;
    private $redirect_url;
    private $state;
    private $authorize_code;
    private $access_token;
    private $job_domain_url;
    private $prefilled_data;
    private $download_resume_url;
    private $resume_temp_url;
    private $resume_filename;
    private $temp_resume_fullpath;
    private $temp_folder_name;
    private $complete_application_url;
    private $client_access_token;
    private $auth_api_url;
    private $prefill_api_url;

    function __construct() {
        $this->CI = &get_instance();
        // Auth Api Url
        $auth_api_url = getenv('SEEK_AUTH_API_URL') ? getenv('SEEK_AUTH_API_URL') : 'https://www.seek.com.au/api/iam/oauth2/token';
        // Prefill Api Url
        $prefill_api_url = getenv('SEEK_PREFILL_API_URL') ? getenv('SEEK_PREFILL_API_URL') : 'https://api.seek.com.au/v2/applications/prefilled';
        // Redirect Url
        $redirected_url = getenv('SEEK_JOB_REDIRECT_URL') ? getenv('SEEK_JOB_REDIRECT_URL') : base_url().'job/getSeekDetails';
        // $job_domain = 'https://jobs.int.healthcaremgr.net/';
        # this is the url where apply job will be visible
        $job_domain = getenv('OCS_JOB_DOMAIN_URL') ? getenv('OCS_JOB_DOMAIN_URL') : base_url();
        // temp folder name
        $temp_folder = 'temp';
        // Assign seek detail to variable
        $this->setUsername(get_setting(Setting::SEEK_USERNAME));
        $this->setPassword(get_setting(Setting::SEEK_PASSWORD));
        $this->setAdvertiserName(get_setting(Setting::SEEK_ADVERTISER_NAME));
        $this->setAdvertiserId(get_setting(Setting::SEEK_ADVERTISER_ID));
        $this->setRedirectUrl($redirected_url);
        $this->setJobDomainUrl($job_domain);
        $this->setResumeTempUrlWithFolderCrete(ARCHIEVE_DIR, $temp_folder);
        $this->setTempFolderName($temp_folder);
        $this->SetAuthApiUrl($auth_api_url);
        $this->SetPrefillApiUrl($prefill_api_url);
    }

    public function setUsername($username) {
    	$this->username = $username;
    }

    public function getUsername() {
    	return $this->username;
    }

    public function setPassword($password) {
    	$this->password = $password;
    }

    public function getPassword() {
    	return $this->password;
    }

    public function setAdvertiserName($advertiser_name) {
    	$this->advertiser_name = $advertiser_name;
    }

    public function getAdvertiserName() {
    	return $this->advertiser_name;
    }

    public function setAdvertiserId($advertiser_id) {
    	$this->advertiser_id = $advertiser_id;
    }

    public function getAdvertiserId() {
    	return $this->advertiser_id;
    }

    public function setRedirectUrl($url) {
    	$this->redirect_url = $url;
    }

    public function getRedirectUrl() {
    	return $this->redirect_url;
    }

    public function setJobDomainUrl($job_domain_url) {
    	$this->job_domain_url = $job_domain_url;
    }

    public function getJobDomainUrl() {
    	return $this->job_domain_url;
    }

    public function setAuthorizeCode($authorize_code) {
    	$this->authorize_code = $authorize_code;
    }

    public function getAuthorizeCode() {
    	return $this->authorize_code;
    }

    public function setAccessToken($access_token) {
    	$this->access_token = $access_token;
    }

    public function getAccessToken() {
    	return $this->access_token;
    }

    public function setState($state) {
    	$this->state = $state;
    }

    public function getState() {
    	return $this->state;
    }

    public function setPrefilledData($prefilled_data) {
    	$this->prefilled_data = $prefilled_data;
    }

    public function getPrefilledData() {
    	return $this->prefilled_data;
    }

    public function setResumeUrl($download_resume_url) {
    	$this->download_resume_url = $download_resume_url;
    }

    public function getResumeUrl() {
    	return $this->download_resume_url;
    }

    public function setResumeTempUrl($resume_temp_url) {
    	$this->resume_temp_url = $resume_temp_url;
    }

    public function getResumeTempUrl() {
    	return $this->resume_temp_url;
    }

    public function setResumeTempFullUrl($temp_resume_fullpath) {
    	$this->temp_resume_fullpath = $temp_resume_fullpath;
    }

    public function getResumeTempFullUrl() {
    	return $this->temp_resume_fullpath;
    }

    public function setResumeFileName($resume_filename) {
    	$this->resume_filename = $resume_filename;
    }

    public function getResumeFileName() {
    	return $this->resume_filename;
    }

    public function setTempFolderName($temp_folder_name) {
    	$this->temp_folder_name = $temp_folder_name;
    }

    public function getTempFolderName() {
    	return $this->temp_folder_name;
    }

    public function setCompleteApplicationUrl($complete_application_url) {
    	$this->complete_application_url = $complete_application_url;
    }

    public function getCompleteApplicationUrl() {
    	return $this->complete_application_url;
    }

    public function setClientAccessToken($client_access_token) {
    	$this->client_access_token = $client_access_token;
    }

    public function getClientAccessToken() {
    	return $this->client_access_token;
    }

    public function SetAuthApiUrl($auth_api_url) {
    	$this->auth_api_url = $auth_api_url;
    }

    public function getAuthApiUrl() {
    	return $this->auth_api_url;
    }

    public function SetPrefillApiUrl($prefill_api_url) {
    	$this->prefill_api_url = $prefill_api_url;
    }

    public function getPrefillApiUrl() {
    	return $this->prefill_api_url;
    }

    /*
     * Set applicant resume temp folder path
     * Create folder with write access if not exist   
     */
    public function setResumeTempUrlWithFolderCrete($temp_path, $temp_folder) {
    	// $temp_url = $temp_path . $temp_folder;
    	$temp_url = $temp_path;
    	if (!is_dir($temp_url)) {
            mkdir($temp_url, 0755);
        }
    	$this->resume_temp_url = $temp_url;
    }
    
    /*
     * Exchange your authorization code for an access token
     */
    public function getAccessTokenApi() {
    	// create a new cURL resource
		$curl = curl_init();
		// Post url		
		$auth_url = $this->auth_api_url;
		$page = "/api/iam/oauth2/token";
		// credentials
		$credentials = $this->username.":".$this->password;
		// post data
        $data = array(
        	"code=" . $this->authorize_code,
        	"redirect_uri=" . $this->redirect_url,
        	"grant_type=authorization_code"
        );
        $post_data = implode("&", $data);
		$headers = array(
			"Authorization: Basic " . base64_encode($credentials),
            "Content-type: application/x-www-form-urlencoded",            
            "Content-Length: " . strlen($post_data),
        );

		// set URL and other appropriate options
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $auth_url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $post_data,
		  CURLOPT_HTTPHEADER => $headers,
		));

		// grab URL and pass it to the browser
		$response = curl_exec($curl);
		// Get response state code
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		// close cURL resource, and free up system resources
		curl_close($curl);

		$responseData = json_decode($response, true);
		if (isset($responseData) && empty($responseData) == false && isset($responseData['access_token']) && $http_status == 200) {
			$this->setAccessToken($responseData['access_token']);
			return true;
		} else {
			return false;
		}
    }

    /*
     * Get applicant details for prefill
     * Api call through cUrl
     */
    public function getApplicantDeatilsApi() {
    	// create a new cURL resource
		$curl = curl_init();
		// Initial varaible
		$prefill_url = $this->prefill_api_url;
		$page = "/v2/applications/prefilled";
		$access_token = $this->access_token;
		$advertiser_id = $this->advertiser_id;
		$application_form_url = $this->job_domain_url . 'jobs/' . $this->state;
		// Post data
        $data = array(
        	"applicationFormUrl" => $application_form_url,
        	"advertiserId" => $advertiser_id
        );
        $post_data = json_encode($data);
		// Header data 
		$headers = array(
            "Authorization: Bearer ".$access_token,
		    "Content-Type: application/json",
		    "Accept: application/json",
		    "Content-Length: " . strlen($post_data),
        );

		curl_setopt_array($curl, array(
			CURLOPT_URL => $prefill_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $post_data,
			CURLOPT_HTTPHEADER => $headers,
		));

		// grab URL and pass it to the browser
		$response = curl_exec($curl);
		// print_r($response);
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		// close cURL resource, and free up system resources
		curl_close($curl);
		// json decode
		$responseData = json_decode($response, true);

		if (isset($responseData) && empty($responseData) == false && isset($responseData['applicantInfo']) && $http_status == 200) {
			// Set applicant details
			$this->setPrefilledData($responseData);
			return true;
		} else {
			return false;
		}
    }

    /*
     * Ger the resume and save to temp folder
     */
    public function downloadApplicantResume() {
    	
    	$curl = curl_init();
    	// Initial varaible
    	$access_token = $this->access_token;

		$resume_download_url = $this->download_resume_url;
    	// Header data
		$headers = array(
            "Authorization: Bearer ".$access_token,
		    "Content-Type: application/json",
		    "Accept: application/octet-stream",
        );
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $resume_download_url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => $headers,
		  CURLOPT_HEADER => true,
		  CURLINFO_HEADER_OUT => true,
		));
		// this function is called by curl for each header received
		$resume_filename = '';
		$matches = [];
		curl_setopt($curl, CURLOPT_HEADERFUNCTION,
		  function($curl, $header) use (&$resume_filename, $matches)
		  {
		    $len = strlen($header);
		  	if (preg_match('/Content-Disposition:.*?filename="(.+?)"/', $header, $matches)) {
			  // Content-Disposition: attachment; filename="FILE NAME HERE"
			  $resume_filename = $matches[1];
			} elseif (preg_match('/Content-Disposition:.*?filename=([^; ]+)/', $header, $matches)) {
			  // Content-Disposition: attachment; filename=file.ext
			  $resume_filename = $matches[1];
			}
		    return $len;
		  }
		);
		
		$response = curl_exec($curl);
		// Get response http status code
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		// Get response header length
		$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		// Then, after your curl_exec call:
		curl_close($curl);
		if (isset($response) && empty($response) == false && $http_status == 200 && $resume_filename != '') {
			// header content
			$header = substr($response, 0, $header_size);
			// resume documet content
			$content = substr($response, $header_size);
			// Set resume filename with extension
			$this->resume_filename = $resume_filename;
			// temp save path
			$destination = $this->resume_temp_url. '/' .$resume_filename;
			// Set resume temp full path
			$this->temp_resume_fullpath = $destination;
			// open file with write permision
			$file = fopen($destination, "w+");
			// put the content content into file
			fputs($file, $content);
			// close the file
			fclose($file);
			return true;
		} else {
			return false;
		}
    }

    /*
     * get an client access token
     */
    public function getClientAccessTokenApi() {
    	// create a new cURL resource
		$curl = curl_init();
		// Post url		
		$auth_url = $this->auth_api_url;
		$page = "/api/iam/oauth2/token";
		// credentials
		$credentials = $this->username.":".$this->password;
		// post data
        $data = array(
        	"grant_type=client_credentials"
        );
        $post_data = implode("&", $data);
		$headers = array(
			"Authorization: Basic " . base64_encode($credentials),
            "Content-type: application/x-www-form-urlencoded",            
            "Content-Length: " . strlen($post_data),
        );

		// set URL and other appropriate options
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $auth_url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $post_data,
		  CURLOPT_HTTPHEADER => $headers,
		));

		// grab URL and pass it to the browser
		$response = curl_exec($curl);
		// Get response state code
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		// close cURL resource, and free up system resources
		curl_close($curl);

		$responseData = json_decode($response, true);
		if (isset($responseData) && empty($responseData) == false && isset($responseData['access_token']) && $http_status == 200) {
			$this->setClientAccessToken($responseData['access_token']);
			return true;
		} else {
			return false;
		}
    }

    /*
     * Call the complete endpoint on the application API to complete your application
     */
    public function completeApplicationApi() {
    	// create a new cURL resource
		$curl = curl_init();
		// Post url		
		$auth_url = $this->complete_application_url;
		$client_access_token = $this->client_access_token;
		// post data
        $data = array(
        	"CompletionDate=".DATE_TIME
        );
        $post_data = implode("&", $data);
		$headers = array(
			"Authorization: Bearer " . $client_access_token,
            "Content-type: application/json",            
            "Content-Length: " . strlen($post_data),
        );

		// set URL and other appropriate options
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $auth_url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $post_data,
		  CURLOPT_HTTPHEADER => $headers,
		));

		// grab URL and pass it to the browser
		$response = curl_exec($curl);
		// Get response state code
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		// close cURL resource, and free up system resources
		curl_close($curl);

		$responseData = json_decode($response, true);
		if (isset($responseData) && empty($responseData) == false && $http_status == 204) {
			return true;
		} else {
			return false;
		}
    }
}
