<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use Admin\Auth;

/**
 * returns the second.millisecond (4 decimal) difference between two timestamps
 */
function calc_seconds_diff($startDate, $endDate)
{
    $startDateFormat = new DateTime($startDate);
    $EndDateFormat = new DateTime($endDate);
    // the difference through one million to get micro seconds
    $diff = $startDateFormat->diff($EndDateFormat);
    $ms = (int) ($diff->format('%f') / 1000); // convert micro seconds into milli seconds
    $s = (int) ($diff->format('%s') * 1000); // convert seconds into milli seconds
    $i = (int) ($diff->format('%i') * 60 * 1000); // convert minutes into milli seconds
    $h = (int) ($diff->format('%h') * 60 * 60 * 1000); // convert hours into milli seconds
    return round(abs($h + $i + $s + $ms),0); // return total duration in seconds
}

/*
 * log application level message.
 */
function log_msg($message, $httpcode, $payload, $exception, $api_url, $module, $time_taken, $userId) {
    $date = new DateTime();
    $headers = [];
    
    $headers = get_nginx_headers('getallheaders');

    $debug_arr = [
        "status" => $httpcode,
        "message" => $message,
        "headers" => $headers,
        "payload" => $payload,
        "exception" => $exception,
        "serverIp" => gethostname()."/".(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR']:''),
        "operation" => $api_url,
        "module" => $module,
        "version" => RELEASE_VERSION,
        "timeTaken" => $time_taken,
        "userId" => $userId,
        "timestamp" => $date->format("Y-m-d H:i:s.u")
    ];

    # writing to a log file
    # currently resides in application/log folder
    $filepath = APPPATH.'logs/custom_log.log';
    if (!file_exists($filepath)) {
        touch($filepath);
    }
    if (!$fp = @fopen($filepath, 'ab'))
    {
        die("Couldn't open log file:".$filepath);
    }
    flock($fp, LOCK_EX);
    fwrite($fp, json_encode($debug_arr)."\r\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

/**
 * it logs the backend requests with some technical data for CloudWatch
 */
function log_message($level, $message = null, $module = null) {
    
    # turn off info and debug messages
    if($level == "info" || $level == "debug")
    return;

    global $gl_request_body, $gl_execution_start;

    # getting execution time in sec.milliseconds.
    $date = new DateTime();
    $execution_end = $date->format("Y-m-d H:i:s.u");
    $diff = calc_seconds_diff($gl_execution_start, $execution_end);
    $userId = getUserIdFromJWT();

    # fetching the reply message
    $request_body = ob_get_contents();
    $output_json = json_decode($request_body);
    if(!$message && isset($output_json->success))
        $message = $output_json->success;
    else if(!$message && isset($output_json->error))
        $message = $output_json->error;

    # creating server info string, operation and version no
    $method = "GET";
    if(!empty($gl_request_body))
    $method = "POST";

    $server_string = gethostname()."/".(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR']:'');
    $req_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $operation = $method." ".$req_uri;
    $version = RELEASE_VERSION;

    # fetching the module name from request uri if not provided
    # needed when there is any error generated and control comes in here
    if(!$module) {
        if(substr_count($req_uri, "admin/") > 0) {
            $module = "admin";
        }
    }

    # getting the status code and error brack trace
    $status_code = 200;
    $exception = '';
    if($level == "error") {
        $status_code = 500;
        $exception = debug_backtrace(null,5);
    }

    $headers = get_nginx_headers();

    $debug_arr = [
        "status" => $status_code,
        "message" => $message,
        "headers" => $headers,
        "payload" => maskPII($gl_request_body),
        "exception" => $exception,
        "serverIp" => $server_string,
        "operation" => $operation,
        "module" => $module,
        "version" => $version,
        "timeTaken" => $diff,
        "userId" => $userId,
        "timestamp" => $execution_end
    ];

    # writing to a log file
    # currently resides in application/log folder
    $filepath = APPPATH.'logs/custom_log.log';
    if (!file_exists($filepath)) {
        touch($filepath);
    }
	if (!$fp = @fopen($filepath, 'ab'))
    {
        die("Couldn't open log file:".$filepath);
    }
    flock($fp, LOCK_EX);
    fwrite($fp, json_encode($debug_arr)."\r\n");
    flock($fp, LOCK_UN);
	fclose($fp);
}

/*
 * Mask sensitive and personally identifiable information
 */
function maskPII($payload) {
    $starMask = '********';
    if (isset($payload)) {
        if (isset($payload->request_data->data)) {
            // mask password
            if (isset($payload->request_data->data->password)) {
                $payload->request_data->data->password = $starMask;
            }
        }
    }
    return $payload;
}

/**
 *
 */
function getUserIdFromJWT() {
    require_once(APPPATH . 'Classes/admin/auth.php');
    $adminAuth = new Admin\Auth\Auth();
    $jwtPayload = $adminAuth->getJWT();
    if (isset($jwtPayload)) {
        return $jwtPayload->sub;
    }

    return "";
}

function get_nginx_headers($function_name='getallheaders'){
    $all_headers=array();
    if(function_exists($function_name)){ 
        $all_headers=$function_name();
    }
    else{
        foreach($_SERVER as $name => $value){
            if(substr($name,0,5)=='HTTP_'){
                $name=substr($name,5);
                $name=str_replace('_',' ',$name);
                $name=strtolower($name);
                $name=ucwords($name);
                $name=str_replace(' ', '-', $name);
                $all_headers[$name] = $value;
            } elseif ($function_name=='apache_request_headers') {
                $all_headers[$name] = $value;
            }
        }
    }

    return $all_headers;
}

?>