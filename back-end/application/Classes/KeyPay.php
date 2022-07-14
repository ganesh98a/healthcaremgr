<?php

require_once APPPATH . 'Classes/AdminApiLog.php';

/*
 * To use KeyPay as employee payment services
 * creating employees, timesheets etc
 * 
 * @author Pranav Gajjar
 */

class KeyPay {

    private $host_url;
    private $api_function;
    private $business_id;
    private $kiosk_id = "hcm";
    private $error;
    private $api_key;
    private $company_id;
    private $admin_id;

    # used only for testing purpose
    private $member_id_tfn = [
        "23" => "865414088",
        "13" => "830721909",
        "2" => "656077298",
        "12" => "243494429",
        "119" => "946489031",
        "137" => "412042562",
        "175" => "459599230",
        "178" => "796801997"
    ];

    /**
     * setting the initial values to class variables
     */
    function __construct() {
        $this->CI = &get_instance();
        $this->company_id = KEYPAY_DEFAULT_COMPANY_ID;
        $this->host_url = "https://api.yourpayroll.com.au/api/v2/business/";
    }

    /**
     * setting the admin id
     */
    function set_admin_id($admin_id) {
        $this->admin_id = $admin_id;
    }

    /**
     * getting the error assigned
     */
    function get_error() {
        return $this->error;
    }

    /**
     * main function that calls the KeyPay api
     * mostly uses the post method for provided KeyPay function calls
     */
    function call_api($data = null, $post = true) {
        $ch = curl_init();
        $url = $this->host_url.$this->api_function;
        if ($data && !$post)
            $url = sprintf("%s?%s", $url, http_build_query($data));
        
        curl_setopt($ch, CURLOPT_URL, $url);
        if($post) {
            curl_setopt($ch, CURLOPT_POST, $post);
            if($data)
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        else {
            curl_setopt($ch, CURLOPT_POST, 0);
        }

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Basic '.base64_encode($this->api_key);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $startCall = round(microtime(true) * 1000);
        $result = curl_exec($ch);
        $time_taken = round(microtime(true) * 1000) - $startCall;
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $return = json_decode($result);
        
        if(isset($return->message) && !empty($return->message)) {
            $this->error = $return->message;

            # adding an error log
            $this->AddApiLog($httpcode, json_encode($data), null, $url, $time_taken, json_encode($return));
            return false;
        }
        else {
            # adding a success log
            $this->AddApiLog($httpcode, json_encode($data), json_encode($return), $url, $time_taken);

            if($return)
                return $return;
            else
                return true;
        }
    }

    /**
     * adding the api call details into the log table
     */
    function AddApiLog($httpcode, $data_in, $data_out, $api_url, $time_taken, $error_response = null) {
        if (!isset($error_repsonse)) {
            $payload = $data_in.','.$data_out;
        } else {
            $payload = $data_in;
        }
        log_msg('Called KeyPay API', $httpcode, $payload, $error_response, $api_url, 'KeyPay', $time_taken, $this->api_key);
    }

    /**
     * authenticating the KeyPay and sets the main variables
     */
    function AuthenticateDetails() {
        $row = $this->CI->basic_model->get_row('keypay_auth_details',['api_key','business_id','kiosks_id','location_id','id'],['companyId'=>$this->company_id,'status'=>1]);
		if(!$row){
            $this->error = 'KeyPay configuration data is missing';
            return false;
        }
        if(!isset($row->api_key) || !isset($row->business_id)  ||  empty($row->api_key) || empty($row->business_id)){
			$this->error = 'KeyPay configuration data is missing';
            return false;
        }
        
        $this->host_url .= $row->business_id."/";
        $this->business_id = $row->business_id;
        $this->api_key = $row->api_key;
        return true;
    }

    /**
     * creating employee inside KeyPay if not found
     */
    function create_employee($employee_details) {
        # preparing array of employee details
        $data_params= [
            "dateCreated"=> $employee_details['created'],
            "dateOfBirth"=> !empty($employee_details['date_of_birth']) ? $employee_details['date_of_birth'] : "0000-00-00",
            "emailAddress"=> $employee_details['username'],
            "externalId"=> $employee_details['id'],
            "firstName"=> $employee_details['firstname'],
            "gender"=> $employee_details['gender_label'],
            "hoursPerWeek"=> "38.00",
            "locations"=> "HCM",
            "middleName"=> "",
            "mobilePhone"=> $employee_details['phone'],
            "postalAddressLine2"=> "",
            "postalCountry"=> "Australia",
            "postalPostCode"=> $employee_details['postcode'],
            "postalState"=> $employee_details['state_label'],
            "postalStreetAddress"=> $employee_details['street'],
            "postalSuburb"=> $employee_details['suburb'],
            "rate"=> "20.00",
            "rateUnit"=> "hourly",
            "startDate"=> $employee_details['created'],
            "status"=> "Active",
            "surname"=> $employee_details['lastname'],
            "title"=> $employee_details['fullname'],
            "taxFileNumber"=>$this->member_id_tfn[$employee_details['id']],
            "employmentType" => "Full Time",
            "paySchedule" => "Weekly",
            "primaryPayCategory" => "5947314", # default pay category configured in the KeyPay account
            "primaryLocation" => "HCM", # location configured in the KeyPay account
            "bankAccount1_AccountName" => $employee_details['fullname'],
            "bankAccount1_AccountNumber" => "14444488",
            "bankAccount1_BSB" => "083114",
            "bankAccount1_AllocatedPercentage" => "100.00",
        ];

        $this->api_function = "employee/unstructured";
        $return = $this->call_api($data_params, true);
        if($return) {
            if(isset($return->id) && !empty($return->id))
                return $return->id;
            else {
                $this->error = "Couldn't fetched the employee id";
                return false;
            }
        }
        else
            return false;
    }

    /**
     * creating timesheet lines in keypay for each timesheet line items found in HCM
     */
    function create_timesheet_line($from_date, $to_date, $keypay_emp_line_items) {

        # preparing array of data to submit
        $timesheet_params = null;
        $index = 0;
        foreach($keypay_emp_line_items as $keypay_emp_id => $emp_rows) {
            $index++;
            foreach($emp_rows as $item) {
                $timesheet_params[$keypay_emp_id][] = [
                    "employeeId" => $keypay_emp_id,
                    "externalId" => $item['id'],
                    "startTime" => date("Y-m-d", strtotime($item['actual_start_date'])),
                    "endTime" => date("Y-m-d", strtotime($item['actual_start_date'])),
                    "payCategoryId" => (isset($item['external_reference']) ? $item['external_reference'] : ''),
                    "units" => $item['units'],
                    "rate" => $item['unit_rate'],
                ];
            }
        }

        $data_params = [
            "approved" => "true",
            "replaceExisting" => "true",
            "fromDate" => date("Y-m-d", strtotime($from_date)),
            "toDate" => date("Y-m-d", strtotime($to_date)),
            "timesheets" => $timesheet_params,
        ];

        $this->api_function = "timesheet/bulk";
        $return = $this->call_api($data_params, true);
        if($return) {
            return true;
        }
        else
            return false;
    }

    /**
     * getting keypay leave categories
     */
    function get_keypay_leave_category() {
        $this->api_function = "leavecategory";
        $return = $this->call_api(null, false);
        if($return) {
            return true;
        }
        else
            return false;
    }

    /**
     * fetching leaves from keypay to mark them as unavailable in HCM
     */
    function get_keypay_employee_leaves() {

        # preparing array of data to submit
        $from_date = date("Y-m-d")."T00:00:00";
        $to_date = date('Y-m-d', strtotime($from_date . '+30 day'))."T00:00:00";

        $data_params = [
            "filter.status" => "Approved",
            "filter.groupBy" => "Employee",
            "filter.fromDate" => $from_date,
            "filter.toDate" => $to_date
        ];

        $this->api_function = "leaverequest";
        $return = $this->call_api($data_params, false);
        if($return) {
            return object_to_array($return);
        }
        else
            return false;
    }

    /**
     * Fetching the timesheet line items from keypay which are payrun finalised
     */
    function get_paid_keypay_timesheets_by_payrun() {
    
        # preparing array of data to submit
        $to_date = date("Y-m-d")."T00:00:00";
        $from_date = date('Y-m-d', strtotime($to_date . '-14 day'))."T00:00:00";       

        $data_params = [
            '$filter' => "PayPeriodStarting ge datetime'{$from_date}' and PayPeriodStarting le datetime'{$to_date}'",
            "isFinalised" => true,
        ];
        
        $this->api_function = "payrun";
        
        $payrundata = $this->call_api($data_params, false);
        $return = [];
        if(!empty($payrundata) && gettype($payrundata) == 'array') {
            foreach(object_to_array($payrundata) as $data) {
                if($data['isFinalised']) {
                    $timesheet_id = $this->get_paid_keypay_earning_lines($data['id']);
                    //sleep(1);
                    if(!empty($timesheet_id) && gettype($payrundata) == 'array'){
                        $return = array_merge($return, $this->get_paid_keypay_earning_lines($data['id']));
                    }

                }
            }            
        }
        return $return;
        
    }

    /**
     * Fetching timesheet external id from the earning line items of finalised payrun
     */
    function get_paid_keypay_earning_lines($payrunId) {

        $this->api_function = "payrun/$payrunId/earningsLines";
    
        $earningdata = $this->call_api([], false);
        $timesheets = [];
        if(!empty($earningdata) && gettype($earningdata) == 'object') {
            $earningdata = object_to_array($earningdata);

            foreach($earningdata['earningsLines'] as $edata) {
                foreach($edata as $data) {                
                    if($data['timesheetLineExternalId']) {
                        $timesheets[] = $data['timesheetLineExternalId'];                    
                    }
                }                
            }
            return $timesheets;            
        }
        else {
            return [];
        }
    }

    /**
     * Fetching the timesheet line items from keypay which are payment processed
     */
    function get_paid_keypay_timesheets() {
        # preparing array of data to submit
        $to_date = date("Y-m-d")."T00:00:00";
        $from_date = date('Y-m-d', strtotime($to_date . '-14 day'))."T00:00:00";       

        $data_params = [
            '$filter' => "StartTime ge '{$from_date}' and StartTime lt '{$to_date}' and Status eq 4"
        ];
        $this->api_function = "timesheet";
        $return = $this->call_api($data_params, false);
        if($return) {
            return object_to_array($return);
        }
        else
            return false;
    }

     /**
     * creating employee inside KeyPay if not found
     */
    function check_keypay_employee_id_availablity($emp_id) {
        # preparing array of employee details       
        if(!$emp_id) {
            return false;
        }
        $this->api_function = "employee/unstructured/$emp_id";
        $return = $this->call_api(NULL, false);
        
        if($return) {
            if(isset($return->id) && !empty($return->id))
                return $return->id;
            else {
                $this->error = "Couldn't fetched the employee id";
                return false;
            }
        }
        else
            return false;
    }

}
