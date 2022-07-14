<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

global $gl_request_body, $gl_execution_start;
//include APPPATH . 'Classes/admin/jwt_helper.php';
//use classPermission\Permission;

function encrypt_decrypt($action, $string)
{
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'This is my secret key';
    $secret_iv = 'This is my secret iv';
    $key = hash('sha256', $secret_key);
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if ($action == 'decrypt') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

function pr($data, $die = 1)
{
    print_r("<pre>");
    print_r($data);
    if ($die == 1) {
        die;
    }
}

/**
 * used in debugging to print the array/object output
 *
 * @param mixed $data
 */
function prt($data)
{
    print_r("<pre>");
    print_r($data);
    print_r("</pre>");
}

function last_query($die = 0)
{
    $ci = &get_instance();
    echo $ci->db->last_query();
    if ($die == 1) {
        die;
    }
}

function all_user_data()
{
    $ci = &get_instance();
    echo '<pre>';
    print_r($ci->session->all_userdata());
    exit;
}

/*
 *  add admin data in login history
 */

function add_login_history($adminId, $token, $status_msg, $status_id, $access_using = 1)
{
    $CI = &get_instance();
    $CI->load->library('Mobile_Detect');
    $details = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'CLI';
    $detect = new Mobile_Detect;
    $detect->setUserAgent($details);
    $application = 1; # desktop

    if ($detect->isTablet())
        $application = 3;
    else if ($detect->isMobile())
        $application = 2;

    $ip_address = get_client_ip_server();
    $location = file_get_contents('http://ip-api.com/json/' . $ip_address);
    $country = "";
    if (!empty($location)) {
        $location_dec = json_decode($location, true);
        if (!empty($location_dec) && isset($location_dec['country']))
            $country = $location_dec['country'];
    }
    $login_url = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'CLI';

    $check_previous = $CI->basic_model->update_records('member_login_history', array('status' => 2), $where = array('memberId' => $adminId, 'status' => 1, 'updated' => DATE_TIME));

    $data = array('ip_address' => $ip_address, 'details' => $details, 'location' => $location, 'memberId' => $adminId, 'status' => 1, 'status_msg' => $status_msg, 'login_time' => DATE_TIME, 'last_access' => DATE_TIME, 'token' => $token, 'status_id' => $status_id, 'country' => $country, 'application' => $application, 'login_url' => $login_url, 'access_using' => $access_using, 'created' => DATE_TIME);
    $CI->basic_model->insert_records('member_login_history', $data, $multiple = false);
}

/*
 *  update login history
 */

function update_login_history($adminId)
{
    $CI = &get_instance();
    $check_previous = $CI->basic_model->update_records('member_login_history', array('last_access' => DATE_TIME, 'updated' => DATE_TIME), $where = array('memberId' => $adminId, 'status' => 1));
}

/*
 *
 */

function logout_login_history($adminId)
{
    $CI = &get_instance();
    $check_previous = $CI->basic_model->update_records('member_login_history', array('status' => 2, 'updated' => DATE_TIME), $where = array('memberId' => $adminId, 'status' => 1));
}

function request_handler($permission_key = false, $check_token = 1, $pin = false)
{
    global $gl_request_body, $gl_execution_start;
    $request_body = file_get_contents('php://input');
    $request_body = json_decode($request_body);

    # HCM- 3485, tracking the execution start time and request body for later use
    $gl_request_body = $request_body;
    $date = new DateTime();
    $gl_execution_start = $date->format("Y-m-d H:i:s.u");
    if ($check_token && !empty($request_body->request_data)) {

        // here verify Domian request
        verify_server_request();

        // here check token , pin, permission
        $response = verifyAdminToken($request_body->request_data, $permission_key, $pin);

        if(!empty($response['adminId'])) {

            $response['business_unit'] = get_business_unit_role($response['adminId']);
        }
        if (!empty($response['status'])) {
            $request_body->request_data->adminId = $response['adminId'];
            $request_body->request_data->uuid_user_type = $response['uuid_user_type'];
            $request_body->request_data->business_unit =  $response['business_unit']??[];
            // update there login history
            update_login_history($response['adminId']);            
            return $request_body->request_data;
        } else {
            echo json_encode($response);
            exit();
        }
    } elseif (!empty($request_body->request_data)) {
        return $request_body->request_data->data;
    } else {
        echo json_encode(array('status' => false, 'token_status' => true, 'error' => system_msgs('verfiy_token_error')));
        exit();
    }
}

function request_handlerFile($permission_key = false, $check_token = 1, $pin = false,$check_file_type=false)
{
    $CI = &get_instance();
    $data = (object) $CI->input->post();
   
    if (!empty($data)) {
        // here verify Domian request
        verify_server_request();
        $response = verifyAdminToken($data, $permission_key, $pin);
        if ($response['status']) {
            $data->adminId = $response['adminId'];
            $data->uuid_user_type = $response['uuid_user_type'];
            // update there login history
            update_login_history($response['adminId']);
            if($check_file_type && !empty($_FILES))
            {
               
                $input_name = 'attachments';
                $files = $_FILES;
                $cpt = count($_FILES[$input_name]['name']);
                for ($i = 0; $i < $cpt; $i++) {
                    $file_name = $files[$input_name]['name'][$i];
                    $file_type = $files[$input_name]['type'][$i];
                    $file_size = $files[$input_name]['size'][$i];
                    $file_ext  = pathinfo($file_name, PATHINFO_EXTENSION);
                    $allowed_mime_type_arr = explode('|' , DEFAULT_ATTACHMENT_UPLOAD_TYPE);
                    if (!in_array($file_ext, $allowed_mime_type_arr)) {
                        $response = ['status' => false, 'error' => 'The filetype you are attempting to upload is not allowed.'];
                        echo json_encode($response);
                        exit();
                        }
                    
            }
         }
            return $data;
        } else {
            echo json_encode($response);
            exit();
        }
    } else {
        echo json_encode(array('status' => false, 'token_status' => true, 'error' => system_msgs('verfiy_token_error')));
        exit();
    }
}

/*
 *  verfiy token work on both option parameter and private varibale $ocs_token
 *  return true if token verify and return false if expire and on time out
 */

function verifyAdminToken($payload, $permission_key, $pin)
{
    require_once APPPATH . 'Classes/admin/Auth_token_admin.php';

    if (isset($_SERVER["HTTP_AUTHORIZATION"])) {
        list($type, $data) = explode(" ", $_SERVER["HTTP_AUTHORIZATION"], 2);
        if (strcasecmp($type, "Bearer") == 0) {
            $token_data = $data;
        }
    }

    $CI = &get_instance();
    if (!empty($token_data)) {
        $authObj = new Auth_token_admin();
        $authObj->setToken($token_data);

        // check auth token
        $response = $authObj->check_auth_token();

        if (!empty($response)) {

            $authObj->setAdminId($response->adminId);
            $authObj->setUuid_user_type($response->uuid_user_type);
            $diff = strtotime(DATE_TIME) - strtotime($response->updated);

            // here chack the current user ip address same as login time
            // if ($response->ip_address != get_client_ip_server()) {
            //   //  return array('status' => false, 'ip_address_status' => true, 'error' => system_msgs('ip_address_error'));
            // }

            // here check login time is not greater than 30 min.
            if ($diff > $CI->config->item('jwt_token_time')) {
                return ['status' => false, 'token_status' => true, 'error' => system_msgs('verfiy_token_error')];
            }

            // update time of ocs token
            $authObj->update_token_time();

            // check permisson of user to access this module

            if($response->uuid_user_type !=MEMBER_PORTAL){
                if (!empty($permission_key)) {
                    $authObj->setPermission($permission_key);
    
                    // check permission
                    $res = $authObj->check_permission();
    
                    if ($res === false) {
                        return ['status' => false, 'permission_status' => true, 'error' => system_msgs('permission_error')];
                    }
                }
            }
           

            // check restic area pin
            if ($pin) {
                $responsePinData = getPinDetailsByPermission($response->adminId, $permission_key);

                $responsePin = !empty($responsePinData) && isset($responsePinData->pin) && !is_null($responsePinData->pin) ? $responsePinData->pin : '';
                $responsePinType = !empty($responsePinData) && isset($responsePinData->pin_type) ? $responsePinData->pin_type : 0;
                $tokenGet = json_decode($payload->pin, TRUE);


                if ((empty($responsePin) || empty($tokenGet)) || !isset($tokenGet[$responsePinType]) || ($responsePin != $tokenGet[$responsePinType])) {
                    return ['status' => false, 'pin_status' => true, 'pin_type' => $responsePinType, 'error' => system_msgs('verfiy_token_error')];
                }
            }

            return ['status' => true, 'adminId' => $authObj->getAdminId(), 'uuid_user_type' => $authObj->getUuid_user_type()];
        } else {
            $res_l = $authObj->check_another_location_opened();

            if ($res_l) {
                return ['status' => false, 'another_location_opened' => true, 'error' => 'This account is opened at another location, you are being logged off.'];
            } else {
                return ['status' => false, 'token_status' => true, 'error' => system_msgs('verfiy_token_error')];
            }
        }
    } else {

        return ['status' => false, 'token_status' => true, 'error' => system_msgs('verfiy_token_error')];
    }
}

/*
 * check another user open current account from another locaiton
 */

function check_another_location_opened($token)
{
    $CI = &get_instance();

    $where_h = array('token' => $token, 'ip_address' => get_client_ip_server());
    $response = $CI->basic_model->get_row('member_login_history', $columns = array('last_access'), $where_h);

    if (!empty($response)) {
        $diff = strtotime(DATE_TIME) - strtotime($response->last_access);
        if ($diff < $CI->config->item('jwt_token_time')) {
            echo json_encode(array('status' => false, 'another_location_opened' => true, 'error' => 'This account is opened at another location, you are being logged off.'));
            exit();
        }
    }
}

function verify_server_request()
{
    $CI = &get_instance();
    $request_server = (!empty($_SERVER['HTTP_ORIGIN'])) ? $_SERVER['HTTP_ORIGIN'] : '';

    $servers = $CI->config->item('request_accept_server');

    if (in_array($request_server, $servers)) {
        return true;
    } else {
        echo json_encode(array('status' => false, 'server_status' => true, 'error' => system_msgs('server_error')));
        exit();
    }
}

function check_permission($adminId, $pemission_key)
{
    require_once APPPATH . 'Classes/admin/permission.php';

    $obj_permission = new classPermission\Permission();
    $result = $obj_permission->check_permission($adminId, $pemission_key);

    if (!$result) {
        echo json_encode(array('status' => false, 'permission_status' => true, 'error' => system_msgs('permission_error')));
        exit();
    }
}

function get_all_permission($token)
{
    require_once APPPATH . 'Classes/admin/permission.php';
    $obj_permission = new classPermission\Permission();
    return $obj_permission->get_all_permission($token);
}

function do_upload($config_ary)
{
    /**
     * @todo: This function ISN'T VERY FLEXIBLE enough because it
     * only processes upload_path, allowed_types, max_size, max_width etc
     * It should be able to merge the same params specified in Codeigniter docs
     */

    $CI = &get_instance();
    $response = array();
    if (!empty($config_ary)) {
        $directory_path = $config_ary['upload_path'] . $config_ary['directory_name'];
        $config['upload_path'] = $directory_path;
        $config['allowed_types'] = isset($config_ary['allowed_types']) ? $config_ary['allowed_types'] : '';
        $config['max_size'] = isset($config_ary['max_size']) ? $config_ary['max_size'] : '5120';
        $config['max_width'] = isset($config_ary['max_width']) ? $config_ary['max_width'] : '';
        $config['max_height'] = isset($config_ary['max_height']) ? $config_ary['max_height'] : '';

        if (array_key_exists('file_name', $config_ary) && !empty($config_ary['file_name'])) {
            $config['file_name'] = $config_ary['file_name'];
        }

        create_directory($directory_path);

        $CI->load->library('upload', $config);

        if (!$CI->upload->do_upload($config_ary['input_name'])) {
            $response = array('error' => $CI->upload->display_errors());
        } else {
            $response = array('upload_data' => $CI->upload->data());
        }
    }
    return $response;
}

function do_muliple_upload($config_ary)
{
    $CI = &get_instance();
    $CI->load->library('upload');
    $response = array();

    if (!empty($config_ary)) {
        $directory_path = $config_ary['upload_path'] . $config_ary['directory_name'];

        $config['upload_path'] = $directory_path;

        $config['allowed_types'] = isset($config_ary['allowed_types']) ? $config_ary['allowed_types'] : '';
        $config['max_size'] = isset($config_ary['max_size']) ? $config_ary['max_size'] : '';
        $config['max_width'] = isset($config_ary['max_width']) ? $config_ary['max_width'] : '';
        $config['max_height'] = isset($config_ary['max_height']) ? $config_ary['max_height'] : '';

        create_directory($directory_path);

        $input_name = $config_ary['input_name'];

        $files = $_FILES;
        $cpt = count($_FILES[$input_name]['name']);

        for ($i = 0; $i < $cpt; $i++) {
            $_FILES[$input_name]['name'] = $files[$input_name]['name'][$i];
            $_FILES[$input_name]['type'] = $files[$input_name]['type'][$i];
            $_FILES[$input_name]['tmp_name'] = $files[$input_name]['tmp_name'][$i];
            $_FILES[$input_name]['error'] = $files[$input_name]['error'][$i];
            $_FILES[$input_name]['size'] = $files[$input_name]['size'][$i];

            $CI->upload->initialize($config);

            if (!$CI->upload->do_upload($input_name)) {
                $response[] = array('error' => $CI->upload->display_errors());
            } else {
                $response[] = array('upload_data' => $CI->upload->data());
            }
        }

        return $response;
    }
}

function create_directory($directoryName)
{
    if (!is_dir($directoryName)) {
        mkdir($directoryName, 0755);
        fopen($directoryName . "/index.html", "w");
    }
}

function get_client_ip_server()
{
    $ipaddress = '';
    if (array_key_exists('HTTP_CLIENT_IP', @$_SERVER)) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (array_key_exists('HTTP_X_FORWARDED_FOR', @$_SERVER)) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (array_key_exists('HTTP_X_FORWARDED', @$_SERVER)) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } elseif (array_key_exists('HTTP_FORWARDED_FOR', @$_SERVER)) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } elseif (array_key_exists('HTTP_FORWARDED', @$_SERVER)) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } elseif (array_key_exists('REMOTE_ADDR', @$_SERVER)) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 'UNKNOWN';
    }
    $ipaddresses = explode(',', $ipaddress);
    $ipaddress = isset($ipaddresses[0]) ? $ipaddresses[0] : 0;
    return $ipaddress;
}

function add_hour_minute($times)
{
    // pr($times);
    error_reporting(0);
    $minutes = 0;
    foreach ($times as $time) {
        list($hour, $minute) = explode(':', $time);
        $minutes += $hour * 60;
        $minutes += $minute;
    }
    $hours = floor($minutes / 60);
    $minutes -= $hours * 60;
    return sprintf('%02d:%02d', $hours, $minutes);
}

function is_json($string)
{
    $data = json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE) ? true : FALSE;
}

function dateRangeBetweenDate($start_date, $end_date)
{
    $first = $start_date;
    $last = $end_date;

    $dates = array();
    $step = '+1 day';
    $format = 'Y-m-d';
    $current = strtotime($first);
    $last = strtotime($last);
    while ($current <= $last) {
        $date1 = date($format, $current);
        $dates[$date1] = date('D', $current);
        $current = strtotime($step, $current);
    }
    return $dates;
}

function dateRangeBetweenDateWithWeek($start_date, $end_date, $totalWeek)
{
    $first = $start_date;
    $last = $end_date;

    $dates = array();
    $step = '+1 day';
    $format = 'Y-m-d';
    $current = strtotime($first);
    $last = strtotime($last);
    $weekNumber = 0;
    $cnt = count($totalWeek);

    $weekCount = 1;
    while ($current <= $last) {
        $date1 = date($format, $current);
        if (($weekNumber) == $cnt) {
            $weekNumber = 0;
        }

        $dates[$totalWeek[$weekNumber]][$weekCount][$date1] = date('D', $current);

        if (date('D', $current) == 'Sun') {
            $weekNumber++;
            $weekCount++;
        }

        $current = strtotime($step, $current);
    }
    return $dates;
}

function dayDifferenceBetweenDate($fromDate, $toDate)
{
    $now = strtotime($toDate); //current date
    $your_date = strtotime($fromDate);
    $datediff = $now - $your_date;
    return round($datediff / (60 * 60 * 24));
}

/**
 * returns the number of minutes difference between two dates
 */
function minutesDifferenceBetweenDate($fromDate, $toDate)
{
    $now = strtotime($toDate); //current date
    $your_date = strtotime($fromDate);
    $datediff = $now - $your_date;
    return round($datediff / (60));
}

function hoursDifferenceBetweenDate($fromDate, $toDate)
{
    $now = strtotime($toDate); //current date
    $your_date = strtotime($fromDate);
    $datediff = $now - $your_date;
    return round($datediff / (3600));
}


function hoursDiffBwnDates($fromDate, $toDate, $format = false)
{
    // Declare and define two dates
    $date1 = strtotime($fromDate);
    $date2 = strtotime($toDate);

    // Formulate the Difference between two dates
    $diff = abs($date2 - $date1);

    // To get the year divide the resultant date into
    // total seconds in a year (365*60*60*24)
    $years = floor($diff / (365 * 60 * 60 * 24));

    // To get the month, subtract it with years and
    // divide the resultant date into
    // total seconds in a month (30*60*60*24)
    $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));


    // To get the day, subtract it with years and 
    // months and divide the resultant date into
    // total seconds in a days (60*60*24)
    $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

    // To get the hour, subtract it with years, months & seconds and divide the resultant date into total seconds in a hours (60*60
    $hours = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));

    // To get the minutes, subtract it with years, months, seconds and hours and divide the  resultant date into total seconds i.e. 60
    $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);

    $diff_time = '';
    if ($hours > 0) {
        $diff_time .= $hours . 'h';
    }
    if ($minutes > 0) {
        $diff_time .= ' ' . $minutes . 'm';
    }

    if ($format == true) {
        $h_raw = str_pad($hours, 2, "0", STR_PAD_LEFT);
        $min_raw = str_pad($minutes, 2, "0", STR_PAD_LEFT);
        $diff_time = $h_raw . ':' . $min_raw;
    }
    return $diff_time;
}

function getStateById($stateId)
{
    $ci = &get_instance();
    $result = $ci->basic_model->get_row('state', array('name'), $where = array('id' => $stateId));

    if (!empty($result)) {
        return $result->name;
    } else {
        return false;
    }
}

/**
 * uses Google Maps Distance Matrix to calculate the road distance between two addresses
 * need to enable Matrix API under the Google account
 */
function getDistanceBetweenTwoAddress($address1, $address2)
{
    if (empty($address1) || empty($address2))
        return;
    //fixed HCM-7671; address may be an object e.g. Org_model::get_organisation_address
    if (is_object($address1) && property_exists($address1, 'address')) {
        $address1 = $address1->address;
    }
    if (is_object($address2) && property_exists($address2, 'address')) {
        $address2 = $address2->address;
    }
    //if still $address1 and $address2 are not string, return null
    if (!is_string($address1) || !is_string($address2)) {
        return [null, null];
    }
    $address1 = str_replace(' ', '+', $address1);
    $address2 = str_replace(' ', '+', $address2);

    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$address1}&destinations={$address2}&language=en-EN&sensor=false&key=" . GOOGLE_MAP_KEY;
    $buffer = file_get_contents($url);

    $distance = $duration = 0;
    if (empty($buffer)) {
        return false;
    }
    $output = object_to_array(json_decode($buffer));

    if (empty($output['status']) || $output['status'] != "OK")
        return false;

    if (isset($output['rows']) && isset($output['rows']['0']) && isset($output['rows']['0']['elements']) && isset($output['rows']['0']['elements']['0']) && isset($output['rows']['0']['elements']['0']['distance']) && isset($output['rows']['0']['elements']['0']['distance']['value'])) {
        $distance = $output['rows']['0']['elements']['0']['distance']['value'];
        $distance = round($distance / 1000, 1);
    }

    if (isset($output['rows']) && isset($output['rows']['0']) && isset($output['rows']['0']['elements']) && isset($output['rows']['0']['elements']['0']) && isset($output['rows']['0']['elements']['0']['duration']) && isset($output['rows']['0']['elements']['0']['duration']['value'])) {
        $duration = abs($output['rows']['0']['elements']['0']['duration']['value'] / 60);
    }

    return [$distance, $duration];
}

function getLatLong($address)
{
    $ci = &get_instance();
    if (!empty($address)) {
        $formattedAddr = str_replace(' ', '+', $address);

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $formattedAddr . '&sensor=false&key=' . GOOGLE_MAP_KEY;

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);

        if (empty($buffer)) {
            return false;
        } else {
            $output = json_decode($buffer);
            $data = array();

            if (!empty($output->results)) {
                $data['lat'] = $output->results[0]->geometry->location->lat;
                $data['long'] = $output->results[0]->geometry->location->lng;

                if (!empty($data)) {
                    return $data;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }
}

function getAvailabilityType($shift_time)
{
    $current_time = date('h:i a', strtotime($shift_time));
    $allow_str = array('all');
    $start_time = "10:00 pm";
    $end_time = "6:00 am";

    $date1 = DateTime::createFromFormat('H:i a', $current_time);
    $date2 = DateTime::createFromFormat('H:i a', $start_time);
    $date3 = DateTime::createFromFormat('H:i a', $end_time);

    if ($date1 > $date2 && $date1 < $date3) {
        array_push($allow_str, 'ao');
    }

    $start_time = "12:00 pm";
    $end_time = "10:00 pm";
    $date2 = DateTime::createFromFormat('H:i a', $start_time);
    $date3 = DateTime::createFromFormat('H:i a', $end_time);

    if ($date1 > $date2 && $date1 < $date3) {

        array_push($allow_str, 'pm');
    }

    $start_time = "06:00 am";
    $end_time = "12:00 pm";
    $date2 = DateTime::createFromFormat('H:i a', $start_time);
    $date3 = DateTime::createFromFormat('H:i a', $end_time);

    if ($date1 > $date2 && $date1 < $date3) {

        array_push($allow_str, 'am');
    }
    return $allow_str;
}



function numberToDay($key = false)
{
    $myDayAry = array(1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun');
    if ($key)
        return $myDayAry[$key];
    else
        return $myDayAry;
}

function DateFormate($date, $formate = '')
{
    if ($formate != '')
        return date($formate, strtotime($date));
    else
        return date('Y-m-d H:i:s', strtotime($date));
}

function strTime($time)
{
    return DateTime::createFromFormat('H:i', date('H:i', strtotime($time)));
}

function strTimes($time)
{
    return (date('H:i', strtotime($time)));
}

function get_profile_complete($user_type, $user_id)
{
    /* Name = 5,DOB = 5,Gender = 5,NDIS = 5,Email = 10,Phone = 10,address = 10, Medicare = 5,CRN = 5, preffered lang = 5, language inter = 5, Hearing intrepe = 5, support required = 5,assistance required = 5, oc service = 5,Preference (Places and activity) 15 */
    $CI = &get_instance();
    /* $user_type ='MEMBER';
      $user_id = '3'; */
    $initial_per = 0;
    if ($user_type == 'PARTICIPANT') {
        $colown = array("tbl_participant.gender", "tbl_participant.gender", "tbl_participant.dob", "tbl_participant.crn_num", "tbl_participant.ndis_num", "tbl_participant.medicare_num", "tbl_participant_email.email", "tbl_participant_phone.phone", "tbl_participant_address.street as address", "tbl_participant_care_requirement.preferred_language", "tbl_participant_care_requirement.linguistic_interpreter", "tbl_participant_care_requirement.hearing_interpreter", "tbl_participant_care_requirement.require_assistance_other", "tbl_participant_care_requirement.support_require_other");

        $CI->db->select($colown);
        $CI->db->select(array("concat(tbl_participant.firstname,' ',tbl_participant.middlename,' ',tbl_participant.lastname) as full_name", false));
        $CI->db->from('tbl_participant');
        $CI->db->join('tbl_participant_email', 'tbl_participant_email.participantId = tbl_participant.id AND tbl_participant_email.primary_email = 1', 'LEFT');
        $CI->db->join('tbl_participant_phone', 'tbl_participant_phone.participantId = tbl_participant.id AND tbl_participant_phone.primary_phone = 1', 'LEFT');
        $CI->db->join('tbl_participant_address', 'tbl_participant_address.participantId = tbl_participant.id AND tbl_participant_address.primary_address = 1', 'LEFT');
        $CI->db->join('tbl_participant_care_requirement', 'tbl_participant_care_requirement.participantId = tbl_participant.id', 'LEFT');
        $CI->db->where(array('tbl_participant.id' => $user_id));
        $query = $CI->db->get();
        $row = $query->row_array();

        if (!empty($row)) {
            isset($row['full_name']) ? $initial_per = $initial_per + 5 : '';
            isset($row['dob']) ? $initial_per = $initial_per + 5 : '';
            isset($row['gender']) ? $initial_per = $initial_per + 5 : '';
            isset($row['email']) ? $initial_per = $initial_per + 10 : '';
            isset($row['phone']) ? $initial_per = $initial_per + 10 : '';
            isset($row['address']) ? $initial_per = $initial_per + 10 : '';
            isset($row['medicare_num']) ? $initial_per = $initial_per + 5 : '';
            isset($row['crn_num']) ? $initial_per = $initial_per + 5 : '';
            isset($row['preferred_language']) ? $initial_per = $initial_per + 5 : '';
            isset($row['linguistic_interpreter']) ? $initial_per = $initial_per + 5 : '';
            isset($row['hearing_interpreter']) ? $initial_per = $initial_per + 5 : '';
            isset($row['support_require_other']) ? $initial_per = $initial_per + 5 : '';
            isset($row['require_assistance_other']) ? $initial_per = $initial_per + 5 : '';
        }



        /* for OC services */
        $CI->db->from('tbl_participant_oc_services');
        $CI->db->select("oc_service");
        $where_ary = array('participantId' => $user_id);
        $CI->db->where($where_ary, null, false);
        $query = $CI->db->get() or die('MySQL Error: ' . $CI->db->_error_number());
        $oc_service_row = $query->row_array();

        if (!empty($oc_service_row))
            $initial_per = $initial_per + 5;


        /* For places and activity */
        $CI->db->from('tbl_participant_place');
        $CI->db->select("placeId");
        $where_ary = array('participantId' => $user_id);
        $CI->db->where($where_ary, null, false);
        $query = $CI->db->get() or die('MySQL Error: ' . $CI->db->_error_number());
        $place_row = $query->row_array();

        if (!empty($place_row))
            $initial_per = $initial_per + 7.5;


        $CI->db->from('tbl_participant_activity');
        $CI->db->select("activityId");
        $where_ary = array('participantId' => $user_id);
        $CI->db->where($where_ary, null, false);
        $query = $CI->db->get() or die('MySQL Error: ' . $CI->db->_error_number());
        $activity_row = $query->row_array();

        if (!empty($activity_row))
            $initial_per = $initial_per + 7.5;
    } else if ($user_type == 'MEMBER') {
        /* Name = 10,DOB = 10,Gender = 10,Email = 10,Phone = 10,address = 10, docs = 10
          Preference (Places and activity) 30 */

        $CI->db->select("CONCAT(tbl_member.firstname,' ',tbl_member.middlename, ' ', tbl_member.lastname) AS full_name", FALSE);
        $dt_query = $CI->db->select(array('tbl_member.dob', 'tbl_member.gender', 'tbl_member_email.email', 'tbl_member_phone.phone', 'tbl_member_address.street as address'));
        $CI->db->from('tbl_member');
        $CI->db->join('tbl_member_email', 'tbl_member_email.memberId = tbl_member.id AND tbl_member_email.primary_email = 1', 'LEFT');
        $CI->db->join('tbl_member_phone', 'tbl_member_phone.memberId = tbl_member.id AND tbl_member_phone.primary_phone = 1', 'LEFT');
        $CI->db->join('tbl_member_address', 'tbl_member_address.memberId = tbl_member.id AND tbl_member_address.primary_address = 1', 'LEFT');

        $sWhere = array('tbl_member.id' => $user_id);
        $CI->db->where($sWhere, null, false);
        $query = $CI->db->get();
        $row_member = $query->row_array();

        if (!empty($row_member)) {
            isset($row_member['full_name']) ? $initial_per = $initial_per + 10 : '';
            isset($row_member['dob']) ? $initial_per = $initial_per + 10 : '';
            isset($row_member['gender']) ? $initial_per = $initial_per + 10 : '';
            isset($row_member['email']) ? $initial_per = $initial_per + 10 : '';
            isset($row_member['phone']) ? $initial_per = $initial_per + 10 : '';
            isset($row_member['address']) ? $initial_per = $initial_per + 10 : '';
        }

        /* For places and activity */
        $CI->db->from('tbl_member_place');
        $CI->db->select("placeId");
        $where_ary = array('memberId' => $user_id);
        $CI->db->where($where_ary, null, false);
        $query = $CI->db->get() or die('MySQL Error: ' . $CI->db->_error_number());
        $place_row = $query->row_array();

        if (!empty($place_row))
            $initial_per = $initial_per + 15;

        $CI->db->from('tbl_member_activity');
        $CI->db->select("activityId");
        $where_ary = array('memberId' => $user_id);
        $CI->db->where($where_ary, null, false);
        $query = $CI->db->get() or die('MySQL Error: ' . $CI->db->_error_number());
        $activity_row = $query->row_array();

        if (!empty($activity_row))
            $initial_per = $initial_per + 15;

        /* For docs */
        $CI->db->from('tbl_member_qualification');
        $CI->db->select("id");
        $where_ary = array('memberId' => $user_id, 'archive' => 0);
        $CI->db->where($where_ary, null, false);
        $query = $CI->db->get() or die('MySQL Error: ' . $CI->db->_error_number());
        $docs_row = $query->row_array();

        if (!empty($docs_row))
            $initial_per = $initial_per + 10;
    }
    return $initial_per;
}

function time_ago_in_php($timestamp)
{
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes = round($seconds / 60); // value 60 is seconds
    $hours = round($seconds / 3600); //value 3600 is 60 minutes * 60 sec
    $days = round($seconds / 86400); //86400 = 24 * 60 * 60;
    $weeks = round($seconds / 604800); // 7*24*60*60;
    $months = round($seconds / 2629440); //((365+365+365+365+366)/5/12)*24*60*60
    $years = round($seconds / 31553280); //(365+365+365+365+366)/5 * 24 * 60 * 60
    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        if ($minutes == 1) {
            return "one minute ago";
        } else {
            return "$minutes minutes ago";
        }
    } else if ($hours <= 24) {
        if ($hours == 1) {
            return "an hour ago";
        } else {
            return "$hours hrs ago";
        }
    } else if ($days <= 7) {
        if ($days == 1) {
            return "yesterday";
        } else {
            return "$days days ago";
        }
    } else if ($weeks <= 4.3) {
        if ($weeks == 1) {
            return "a week ago";
        } else {
            return "$weeks weeks ago";
        }
    } else if ($months <= 12) {
        if ($months == 1) {
            return "a month ago";
        } else {
            return "$months months ago";
        }
    } else {
        if ($years == 1) {
            return "one year ago";
        } else {
            return "$years years ago";
        }
    }
}

function timeZoneDevidation($date, $timeZone, $formate = "Y-m-d H:i:s")
{
    return date($formate, strtotime($date) + ($timeZone * 60));
}

function concatDateTime($date, $time)
{
    $newData = date('Y-m-d', strtotime($date));
    $newTime = date('H:i:s', strtotime($time));

    $dateTime = date('Y-m-d H:i:s', strtotime("$newData . $newTime"));

    return $dateTime;
}

function shift_type_interval($term)
{
    $return = false;

    $time_intervals = array(
        'am' => array('start_time' => '06:00', 'end_time' => '12:00', 'spacial' => false),
        'pm' => array('start_time' => '12:00', 'end_time' => '22:00', 'spacial' => false),
        'so' => array('start_time' => '22:00', 'end_time' => '06:00', 'spacial' => true),
    );

    if (array_key_exists($term, $time_intervals)) {
        $return = $time_intervals[$term];
    }

    return $return;
}

function get_participant_img($participantId, $img, $gender)
{
    $CI = &get_instance();
    if ($img) {
        $ch = curl_init(PARTICIPANT_PROFILE_PATH . $participantId . '/' . $img);

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($retcode == 200) {
            return PARTICIPANT_PROFILE_PATH . $participantId . '/' . $img;
        }
    }

    if ($gender == 1) {
        return $CI->config->item('server_url') . 'assets/images/admin/boy.svg';
    } else {
        return $CI->config->item('server_url') . 'assets/images/admin/girls.svg';
    }
}

function get_admin_img($adminId, $img, $gender)
{
    $CI = &get_instance();

    if ($img) {
        $path = ADMIN_PROFILE_PATH . $adminId . '/' . $img;
        if (file_exists($path)) {
            return base_url() . $path;
        }
    }


    if ($gender == 2) {
        return $CI->config->item('server_url') . 'assets/images/admin/girls.svg';
    } else {
        return $CI->config->item('server_url') . 'assets/images/admin/boy.svg';
    }
}

function get_house_img($houseId, $img)
{
    $CI = &get_instance();
    return '/assets/images/admin/House_icons.jpg';
}

function get_fms_initiated_by_name($initiated_type, $initiated_by)
{
    $CI = &get_instance();
    if ($initiated_type == 1) {
        $tbl = 'tbl_member';
        $CI->db->select("CONCAT(m.firstname,' ',m.middlename, ' ', m.lastname) AS name", FALSE);
        $CI->db->where('m.id', $initiated_by);
        $CI->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "external_staff"');
        $emp_qry = $CI->db->get('tbl_member as m');
        $row = $emp_qry->row_array();
        return $row['name'];
    } else if ($initiated_type == 2) {
        $tbl = 'tbl_participant';
        $CI->db->select("CONCAT($tbl.firstname,' ',$tbl.middlename, ' ', $tbl.lastname) AS name", FALSE);
        $CI->db->where('id', $initiated_by);
        $emp_qry = $CI->db->get($tbl);
        $row = $emp_qry->row_array();
        return $row['name'];
    } else if ($initiated_type == 7) {
        $CI->db->select("CONCAT(m.firstname,' ', m.lastname) AS name", FALSE);
        $CI->db->where('m.id', $initiated_by);
        $CI->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"');
        $emp_qry = $CI->db->get('tbl_member as m');
        $row = $emp_qry->row_array();
        return $row['name'];
    } else if ($initiated_type == 3) {
        $CI->db->select("name", FALSE);
        $CI->db->where('o.id', $initiated_by);
        $emp_qry = $CI->db->get('tbl_organisation as o');
        $row = $emp_qry->row_array();
        return $row['name'];
    } else if ($initiated_type == 8) {
        $CI->db->select("site_name", FALSE);
        $CI->db->where('os.id', $initiated_by);
        $emp_qry = $CI->db->get('tbl_organisation_site as os');
        $row = $emp_qry->row_array();
        return $row['site_name'];
    }
}

//Get FMS Feedback Category name by category type and id
function get_fms_dropdown_category_by_name($cat_name, $id)
{
    if (!$cat_name || !$id) {
        return;
    }

    $CI = &get_instance();
    $result = '';
    if ($cat_name == "init_hcm_member" || $cat_name == "aga_hcm_member") {
        $CI->db->select("m.fullname AS name", FALSE);
        $CI->db->where('m.id', $id);
        $CI->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "external_staff"');
        $emp_qry = $CI->db->get('tbl_member as m');
        $row = $emp_qry->row_array();
        $result = $row['name'];
    } else if ($cat_name == "init_hcm_participant" || $cat_name == "aga_hcm_participant") {
        $tbl = 'tbl_participants_master';
        $CI->db->select("$tbl.name AS name", FALSE);
        $CI->db->where('id', $id);
        $emp_qry = $CI->db->get($tbl);
        $row = $emp_qry->row_array();
        $result = $row['name'];
    } else if ($cat_name == "init_hcm_user_admin" || $cat_name == "aga_hcm_user_admin") {
        $CI->db->select("CONCAT(m.firstname,' ', m.lastname) AS name", FALSE);
        $CI->db->where('m.id', $id);
        $CI->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"');
        $emp_qry = $CI->db->get('tbl_member as m');
        $row = $emp_qry->row_array();
        $result = $row['name'];
    }
    else if ($cat_name == "init_hcm_organisation" || $cat_name == "aga_hcm_organisation" ) {
        $CI->db->select("name", FALSE);
        $CI->db->where('o.id', $id);
        $emp_qry = $CI->db->get('tbl_organisation as o');
        $row = $emp_qry->row_array();
        $result = $row['name'];
    } else if ($cat_name == "init_hcm_site" || $cat_name == "aga_hcm_site") {
        $CI->db->select("name", FALSE);
        $CI->db->where('os.id', $id);
        $emp_qry = $CI->db->get('tbl_organisation as os');
        $row = $emp_qry->row_array();
        $result = $row['name'];
    }

    return $result;
}

function get_org_img($orgId, $img)
{
    $CI = &get_instance();
    if ($img) {
        $path = ORG_UPLOAD_PATH . '/' . $orgId . '/' . $img;
        if (file_exists($path)) {
            return $path;
        }
    }

    return $CI->config->item('server_url') . 'assets/images/admin/boy.svg';
}

function get_fms_against_name($case_id)
{
    $CI = &get_instance();
    $CI->db->select("against_category,against_by,against_first_name,against_last_name", FALSE);
    $CI->db->where('caseId', $case_id);
    $emp_qry = $CI->db->get('tbl_fms_case_against_detail');
    $rows = $emp_qry->result();

    $name = '';
    if (!empty($rows)) {
        foreach ($rows as $key => $value) {
            $initiated_type = $value->against_category;

            if ($initiated_type == 1 || $initiated_type == 4) {
                $name .= $value->against_first_name . ' ' . $value->against_last_name . ', ';
            }

            if ($initiated_type == 2) {
                $tbl = 'tbl_member';
                $CI->db->select("CONCAT(m.firstname,' ',m.middlename, ' ', m.lastname) AS name", FALSE);
                $CI->db->where('m.id', $value->against_by);
                $CI->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "external_staff"');
                $emp_qry = $CI->db->get('tbl_member as m');
                $row = $emp_qry->row_array();
                $name .= $row['name'] . ', ';
            }

            if ($initiated_type == 3) {
                $tbl = 'tbl_participant';
                $CI->db->select("CONCAT($tbl.firstname,' ',$tbl.middlename, ' ', $tbl.lastname) AS name", FALSE);
                $CI->db->where('id', $value->against_by);
                $emp_qry = $CI->db->get($tbl);
                $row = $emp_qry->row_array();
                $name .= $row['name'] . ', ';
            }

            if ($initiated_type == 5) {
                $CI->db->select("CONCAT(m.firstname,' ', m.lastname) AS name", FALSE);
                $CI->db->where('m.id', $value->against_by);
                $CI->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"');
                $emp_qry = $CI->db->get('tbl_member as m');
                $row = $emp_qry->row_array();
                $name .= $row['name'] . ', ';
            }

            if ($initiated_type == 6) {
                $CI->db->select("name", FALSE);
                $CI->db->where('o.id', $value->against_by);
                $emp_qry = $CI->db->get('tbl_organisation as o');
                $row = $emp_qry->row_array();
                $name .= $row['name'] . ', ';
            }

            if ($initiated_type == 7) {
                $CI->db->select("site_name", FALSE);
                $CI->db->where('id', $value->against_by);
                $emp_qry = $CI->db->get('tbl_organisation_site');

                $row = $emp_qry->row_array();
                $name .= $row['site_name'] . ', ';
            }
        }
        $name = rtrim($name, ', ');
    }
    return $name;
}

function setting_length($x, $length)
{
    if (strlen($x) <= $length) {
        return $x;
    } else {
        $y = substr($x, 0, $length) . '...';
        return $y;
    }
}

function getPinDetailsByPermission($adminId = 0, $permission_key = '')
{
    $result = [];
    if (!empty($permission_key) && !empty($adminId)) {
        $CI = &get_instance();
        $table = TBL_PREFIX . 'permission';
        $subtable = 'subquery_table';
        $tablePinToken = TBL_PREFIX . 'admin_pin_token';
        $tableToken = TBL_PREFIX . 'member_login';
        $CI->db->select(array($tablePinToken . '.pin', $tablePinToken . '.adminId', $tablePinToken . '.token_type'));
        $CI->db->from($tablePinToken);
        $CI->db->join($tableToken, $tablePinToken . ".token_id=" . $tableToken . ".id AND " . $tableToken . ".memberId='" . $adminId . "'", "inner");
        $subQuery = $CI->db->get_compiled_select();

        $CI->db->select(array($subtable . '.pin', $subtable . '.adminId', $table . '.pin_type', $table . '.permission'));
        $CI->db->from($table);
        $CI->db->join("(" . $subQuery . ") as " . $subtable, $subtable . ".token_type=" . $table . ".pin_type AND " . $table . ".pin_type!=0 AND " . $subtable . ".adminId='" . $adminId . "'", "left");
        $CI->db->where($table . ".permission", $permission_key);
        $query = $CI->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
        }
    }

    return $result;
}

function check_its_recruiter_admin($adminId)
{
    require_once APPPATH . 'Classes/admin/permission.php';

    $obj_permission = new classPermission\Permission();
    $result = $obj_permission->check_permission($adminId, 'access_recruitment_admin');

    return $result;
}

function check_its_recruiter_user($adminId)
{
    require_once APPPATH . 'Classes/admin/permission.php';

    $obj_permission = new classPermission\Permission();
    $result = $obj_permission->check_permission($adminId, 'access_recruitment');

    return $result;
}

function get_address_from_google_response($response)
{
    $CI = &get_instance();
    $name_arr = [];
    $state_id = '';
    $postal_code = '';
    $lat = '';
    $long = '';
    $street = '';
    $street_no = '';
    $city = '';
    $state_name = '';

    if (!empty($response)) {
        if (!empty($response['address_components'])) {
            foreach ($response['address_components'] as $addr) {
                $name_arr[] = $addr['short_name'];

                if (in_array('postal_code', $addr['types']))
                    $postal_code = $addr['long_name'];

                if (in_array('street_number', $addr['types']))
                    $street_no = $addr['long_name'];

                if (in_array('route', $addr['types']))
                    $street = $addr['long_name'];

                if (in_array('locality', $addr['types']))
                    $city = $addr['long_name'];

                if (in_array('administrative_area_level_1', $addr['types']))
                    $state_name = $addr['short_name'];
            }
        } else {
            $postal_code = '';
            $street_no = '';
            $street = '';
            $city = '';
            $state_name = '';
        }
        $lat = ($response['geometry']['location']['lat']) ? $response['geometry']['location']['lat'] : '';
        $long = ($response['geometry']['location']['lng']) ? $response['geometry']['location']['lng'] : '';
    }

    $state_row = $CI->basic_model->get_row('state', array('id'), $where = array('name' => $state_name));
    $state_id = isset($state_row->id) ? $state_row->id : '';
    return ['lat' => $lat, 'long' => $long, 'state_id' => $state_id, 'city' => $city, 'street' => $street_no . ' ' . $street, 'postal_code' => $postal_code, 'full_address' => $response['formatted_address']];
}

function obj_to_arr($obj)
{
    return (!empty($obj) && !is_string($obj)) ? json_decode(json_encode($obj), true) : [];
}

function re_map_arr_index($val, $keys)
{
    if (isset($val[$keys]) && strtolower($keys) == 'date') {
        return !empty($val[$keys]) ? date('Y-m-d', strtotime($val[$keys])) : $val[$keys];
    } elseif (isset($val[$keys])) {
        return $val[$keys];
    }
}

function pos_index_change_array_data($arr, $keys)
{
    $data = json_decode(json_encode($arr), true);
    $fill_data = array_map('re_map_arr_index', $data, array_fill(0, count($data), $keys));
    if (!empty($fill_data)) {
        $fill_data = array_combine($fill_data, $data);
    }
    return $fill_data;
}

function merge_multidimensional_array_values_by_key($arr, $keys)
{
    $data_return = array();
    if (!empty($arr)) {
        foreach ($arr as $key => $value) {
            if (array_key_exists($keys, $value)) {
                $pay_point_approval_id = $value->$keys;
                $data_return[$pay_point_approval_id][] = $value;
            }
        }
    }
    return $data_return;
}

function random_genrate_password($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; ++$i) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

function get_super_admins()
{
    $CI = &get_instance();

    $res = $CI->basic_model->get_record_where('member', ['id'], ["is_super_admin" => 1]);

    $admins_its = [];
    if (!empty($res)) {
        $admins_its = array_column(obj_to_arr($res), 'id');
    }
    //$admins_its = $this->config->item('super_admins');

    return $admins_its;
}

function validateDateWithFormat($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function get_shfit_payroll_filter($viewType = 'week', $extraParm = [])
{
    switch ($viewType) {
        case 'month':
            $fromDate = date('Y-m-01', strtotime('-2 month'));
            $toDate = DATE_CURRENT;
            break;
        case 'year':
            $fromDate = date('Y-01-01', strtotime('-2 year'));
            $toDate = DATE_CURRENT;
            break;
        case 'week':
            $fromDate = date(DB_DATE_FORMAT, strtotime("this monday - 4 week"));
            $toDate = DATE_CURRENT;
            break;
    }
    return ['fromDate' => $fromDate, 'toDate' => $toDate];
}

function getNameFromNumber($num)
{
    $numeric = $num % 26;
    $letter = chr(65 + $numeric);
    $num2 = intval($num / 26);
    if ($num2 > 0) {
        return getNameFromNumber($num2 - 1) . $letter;
    } else {
        return $letter;
    }
}

function columnLetter($c)
{
    $c = intval($c);
    if ($c <= 0)
        return '';
    $letter = '';
    while ($c != 0) {
        $p = ($c - 1) % 26;
        $c = intval(($c - $p) / 26);
        $letter = chr(65 + $p) . $letter;
    }
    return $letter;
}

function genrateColumnLetterRange($from = 1, $to = 900)
{
    $data = range($from, $to);
    $letterData = array_map(function ($v) {
        return columnLetter($v);
    }, $data);
    return $letterData;
}

function getEcxcelColumnNameGetByIndex($index = 0)
{
    $ci = &get_instance();
    $ci->load->library("excel");
    if ($index > 0) {
        $index--;
    }
    return PHPExcel_Cell::stringFromColumnIndex($index);
}

function get_interval_month_wise($fromDate, $toDate)
{
    $startDate = new DateTime($fromDate);
    $endDate = new DateTime($toDate);
    $dateInterval = new DateInterval('P1M');
    $datePeriod = new DatePeriod($startDate, $dateInterval, $endDate);
    $monthData = [];
    foreach ($datePeriod as $date) {
        $temp = ['from_date' => $date->format('Y-m-01'), 'to_date' => $date->format('Y-m-t')];
        $monthData[] = $temp;
    }
    return $monthData;
}

function get_finacial_year_data(int $numberofPastYear)
{
    $numberofPastYear = !empty($numberofPastYear) ? $numberofPastYear - 1 : $numberofPastYear;
    $currentYear = (int) date('m') < 6 ? date('Y') - 1 : date('Y');
    $fromYear = $currentYear - $numberofPastYear;
    $number = range($fromYear, $currentYear, 1);
    return $number;
}

function get_finacial_year_between_from_to_year(int $numberofPastYear)
{
    $number = get_finacial_year_data($numberofPastYear);
    $financeYear = [];
    asort($number);
    foreach ($number as $row) {
        $temp = ['from_date' => $row . '-07-01', 'to_date' => ($row + 1) . '-06-30'];
        $financeYear[] = $temp;
    }
    return $financeYear;
}

function get_current_finacial_year_data()
{
    return get_finacial_year_between_from_to_year(0);
}

if (!function_exists('serach_in_array')) {

    function serach_in_array($array, $key, $value)
    {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, serach_in_array($subarray, $key, $value));
            }
        }

        return $results;
    }
}

function serach_in_double_array($array, $key, $value)
{
    for ($i = 0; $i < count($array); $i++) {
        if (isset($array[$i][$key]) && $array[$i][$key] == $value)
            return true;
    }
    return false;
}

function serach_double_array_in_double_array($array, $key, $compare_arr)
{
    $check_arr = null;
    for ($i = 0; $i < count($array); $i++) {
        if (isset($array[$i][$key]))
            $check_arr[] = $array[$i][$key];
    }

    foreach ($compare_arr as $value) {
        $found_key = array_search($value, $check_arr);
        if ($found_key === FALSE)
            return false;
    }
    return true;
}

/**
 * converts objects and its elements into an array recursively
 */
function object_to_array($obj)
{
    if (is_object($obj)) $obj = (array) $obj;
    if (is_array($obj)) {
        $new = array();
        foreach ($obj as $key => $val) {
            $new[$key] = object_to_array($val);
        }
    } else $new = $obj;
    return $new;
}

/**
 * compares two dates and if the second date is lower or equal to first date, returns false
 * doesn't check the seconds,
 */
function check_dates_lower_to_other($firstdate, $seconddate, $check_seconds = false)
{
    $date1 = date_create(DateFormate($firstdate, 'Y-m-d H:i:s'));
    $date2 = date_create(DateFormate($seconddate, 'Y-m-d H:i:s'));
    $diff = date_diff($date1, $date2);

    if (isset($diff->invert) && ($diff->invert > 0 || ($diff->invert == 0 && $diff->y == 0 && $diff->m == 0 && $diff->d == 0 && $diff->h == 0 && $diff->i == 0 && ($check_seconds && $diff->s == 0))))
        return false;
    else
        return true;
}

/**
 * compares two dates and if the second date is lower or equal to first date, returns false
 * doesn't check the seconds, but has option to skip the ending minute
 */
function check_dates_lower_to_other_exc($firstdate, $seconddate, $minute_skip = false)
{
    $date1 = date_create(DateFormate($firstdate, 'Y-m-d H:i:s'));
    $date2 = date_create(DateFormate($seconddate, 'Y-m-d H:i:s'));
    $diff = date_diff($date1, $date2);

    if (
        isset($diff->invert) &&
        ($diff->invert > 0 ||
            ($diff->invert == 0 && $diff->y == 0 && $diff->m == 0 && $diff->d == 0 && $diff->h == 0 &&
                (
                    ($diff->i == 0 && !$minute_skip) || ($diff->i < 0 && $minute_skip))))
    )
        return false;
    else
        return true;
}

/**
 * checks if any given datetime if falling between two datetimes inclusive
 */
function check_dates_between_two_dates($firstdate, $seconddate, $checkdate, $inc = false, $first_inc = true, $second_inc = true)
{
    $date1 = date('Y-m-d H:i:s', strtotime($firstdate));
    $date2 = date('Y-m-d H:i:s', strtotime($seconddate));
    $date_compare = date('Y-m-d H:i:s', strtotime($checkdate));
    if ($inc == false && ($date_compare >= $date1) && ($date_compare <= $date2))
        return true;
    else if ($inc == true && $first_inc == true && $second_inc == false && ($date_compare >= $date1) && ($date_compare < $date2))
        return true;
    else if ($inc == true && $first_inc == false && $second_inc == true && ($date_compare > $date1) && ($date_compare <= $date2))
        return true;
    else if ($inc == true && $first_inc == true && $second_inc == true && ($date_compare >= $date1) && ($date_compare <= $date2))
        return true;
    else
        return false;
}

/**
 * converts an array into UTF-8 friendly array
 */
function convert_to_utf($arr)
{
    $newarr = null;
    foreach ($arr as $val) {
        $newarr[] = str_replace("?", "", (utf8_decode($val)));
    }
    return $newarr;
}

/**
 * returns HH:MM format from the total minutes
 */
function get_hour_minutes_from_int($total_minutes, $alt_format = false)
{
    $minutes = sprintf("%02d", $total_minutes % 60);
    $hours = sprintf("%02d", intval($total_minutes / 60));

    if (!$alt_format)
        return "$hours:$minutes";

    if ($total_minutes < 60)
        return $minutes . "m";
    else
        return $hours . "h " . $minutes . "m";
}

/**
 * fetches time portion from the datetime string
 */
function get_time_id_from_series($datetime)
{
    list($d, $t) = explode(" ", $datetime);
    list($h, $m, $s) = explode(":", $t);
    $time_label = date('h:i A', strtotime($h . ":" . $m)); // format the time
    return $time_label;
}

/**
 * for any two dates, finding total number of minutes falling in mon-fri, saturday and sunday
 */
function get_weekday_sat_sun_minutes($start_datetime, $end_datetime)
{

    # setting the dates of that week and next week
    $monday_start = date("Y-m-d 00:00:00", strtotime('monday this week', strtotime($start_datetime)));
    $friday_end = date("Y-m-d 23:59:59", strtotime('friday this week', strtotime($start_datetime)));
    $monday_start_next = date("Y-m-d 00:00:00", strtotime('monday this week', strtotime($end_datetime)));
    $friday_end_next = date("Y-m-d 23:59:59", strtotime('friday this week', strtotime($end_datetime)));

    $saturday_start = date("Y-m-d 00:00:00", strtotime('saturday this week', strtotime($start_datetime)));
    $saturday_end = date("Y-m-d 23:59:59", strtotime('saturday this week', strtotime($start_datetime)));
    $sunday_start = date("Y-m-d 00:00:00", strtotime('sunday this week', strtotime($start_datetime)));
    $sunday_end = date("Y-m-d 23:59:59", strtotime('sunday this week', strtotime($start_datetime)));

    # checking anything falling in weekday, saturday or sunday
    $weekday_range_start = check_dates_between_two_dates($monday_start, $friday_end, $start_datetime);
    $weekday_range_end = check_dates_between_two_dates($monday_start, $friday_end, $end_datetime);
    $weekday_range_end_next = check_dates_between_two_dates($monday_start_next, $friday_end_next, $end_datetime);
    $sunday_range_start = check_dates_between_two_dates($sunday_start, $sunday_end, $start_datetime);
    $sunday_range_end = check_dates_between_two_dates($sunday_start, $sunday_end, $end_datetime);
    $saturday_range_start = check_dates_between_two_dates($saturday_start, $saturday_end, $start_datetime);
    $saturday_range_end = check_dates_between_two_dates($saturday_start, $saturday_end, $end_datetime);

    $weekday_minutes = $saturday_minutes = $sunday_minutes = 0;
    $total_minutes = minutesDifferenceBetweenDate($start_datetime, $end_datetime);

    # calculating the weekday, saturday and sunday minutes
    if ($weekday_range_start && $weekday_range_end) {
        $weekday_minutes = $total_minutes;
    } else if ($saturday_range_start && $saturday_range_end) {
        $saturday_minutes = $total_minutes;
    } else if ($sunday_range_start && $sunday_range_end) {
        $sunday_minutes = $total_minutes;
    } else if ($weekday_range_start && $saturday_range_end) {
        $saturday_minutes = minutesDifferenceBetweenDate($saturday_start, $end_datetime);
        $weekday_minutes = minutesDifferenceBetweenDate($start_datetime, $friday_end);
    } else if ($saturday_range_start && $sunday_range_end) {
        $sunday_minutes = minutesDifferenceBetweenDate($sunday_start, $end_datetime);
        $saturday_minutes = minutesDifferenceBetweenDate($start_datetime, $saturday_end);
    } else if ($sunday_range_start && $weekday_range_end_next) {
        $weekday_minutes = minutesDifferenceBetweenDate($monday_start_next, $end_datetime);
        $sunday_minutes = minutesDifferenceBetweenDate($start_datetime, $sunday_end);
    }

    return [$weekday_minutes, $saturday_minutes, $sunday_minutes];
}

/**
 * for any two dates, finding total number of minutes falling in mon-fri, saturday and sunday
 */
function get_weekday_sat_sun_minutes_v1($start_datetime, $end_datetime)
{
    # setting the dates of that week and next week
    $monday_start = date("Y-m-d 00:00:00", strtotime('monday this week', strtotime($start_datetime)));
    $friday_end = date("Y-m-d 23:59:59", strtotime('friday this week', strtotime($start_datetime)));
    $friday_end_next = date("Y-m-d 00:00:00", strtotime('saturday this week', strtotime($start_datetime)));

    $saturday_start = date("Y-m-d 00:00:00", strtotime('saturday this week', strtotime($start_datetime)));
    $saturday_end = date("Y-m-d 23:59:59", strtotime('saturday this week', strtotime($start_datetime)));
    $saturday_end_next = date("Y-m-d 00:00:00", strtotime('sunday this week', strtotime($start_datetime)));

    $sunday_start = date("Y-m-d 00:00:00", strtotime('sunday this week', strtotime($start_datetime)));
    $sunday_end = date("Y-m-d 23:59:59", strtotime('sunday this week', strtotime($start_datetime)));
    $sunday_end_next = date("Y-m-d 00:00:00", strtotime('monday this week', strtotime($end_datetime)));


    # checking anything falling in weekday, saturday or sunday
    $weekday_range_start = check_dates_between_two_dates($monday_start, $friday_end, $start_datetime);
    $weekday_range_end = check_dates_between_two_dates($monday_start, $friday_end, $end_datetime);
    $weekday_range_end_next = check_dates_between_two_dates($monday_start, $friday_end_next, $end_datetime);

    $sunday_range_start = check_dates_between_two_dates($sunday_start, $sunday_end, $start_datetime);
    $sunday_range_end = check_dates_between_two_dates($sunday_start, $sunday_end, $end_datetime);
    $sunday_range_end_next = check_dates_between_two_dates($sunday_start, $sunday_end_next, $end_datetime);

    $saturday_range_start = check_dates_between_two_dates($saturday_start, $saturday_end, $start_datetime);
    $saturday_range_end = check_dates_between_two_dates($saturday_start, $saturday_end, $end_datetime);
    $saturday_range_end_next = check_dates_between_two_dates($saturday_start, $saturday_end_next, $end_datetime);

    $weekday_minutes = $saturday_minutes = $sunday_minutes = 0;
    $total_minutes = minutesDifferenceBetweenDate($start_datetime, $end_datetime);

    # calculating the weekday, saturday and sunday minutes
    if ($weekday_range_start && ($weekday_range_end || $weekday_range_end_next)) {
        $weekday_minutes = $total_minutes;
    }
    if ($saturday_range_start && ($saturday_range_end || $saturday_range_end_next)) {
        $saturday_minutes = $total_minutes;
    }
    if ($sunday_range_start && ($sunday_range_end || $sunday_range_end_next)) {
        $sunday_minutes = $total_minutes;
    }

    return [$weekday_minutes, $saturday_minutes, $sunday_minutes];
}

if (!function_exists('change_one_timezone_to_another_timezone')) {
    function change_one_timezone_to_another_timezone($dateString, $timeZoneSource = null, $timeZoneTarget = null, $returnFormat = 'Y-m-d H:i:s')
    {
        if (empty($timeZoneSource)) {
            $timeZoneSource = date_default_timezone_get();
        }
        if (empty($timeZoneTarget)) {
            $timeZoneTarget = date_default_timezone_get();
        }

        $dt = new DateTime($dateString, new DateTimeZone($timeZoneSource));
        $dt->setTimezone(new DateTimeZone($timeZoneTarget));

        return $dt->format($returnFormat);
    }
}

function merge_multidimensional_array_values_by_key_in_array_format($arr, $keys)
{
    $data_return = array();
    if (!empty($arr)) {
        foreach ($arr as $key => $value) {
            if (isset($value[$keys])) {
                $data_return[$value[$keys]][] = $value;
            }
        }
    }
    return $data_return;
}

/**
 * used in finding what SQL operator to be used based on the symbol used in the front-end
 * returns the SQL query operator
 */
function GetSQLOperator($symbol)
{
    $operator = null;
    if ($symbol == "%.%") { // contains
        $operator = "LIKE";
    } else if ($symbol == "!%.%") { // contains
        $operator = "NOT LIKE";
    } else if ($symbol == "%") { // starts with
        $operator = "LIKE";
    } else {
        $operator = $symbol;
    }
    return $operator;
}

/**
 * returns the part of SQL condition with operator and value wrapped in quotes
 */
function GetSQLCondPartFromSymbol($symbol, $value)
{
    $cond = null;
    if (empty($value))
        return;

    $operator = GetSQLOperator($symbol);

    if ($symbol == "%.%") { // contains
        $cond = "{$operator} '%{$value}%'";
    } else if ($symbol == "!%.%") { // contains
        $cond = "{$operator} '%{$value}%'";
    } else if ($symbol == "%") { // starts with
        $cond = "{$operator} '%{$value}'";
    } else {
        $cond = "{$operator} '{$value}'";
    }

    return $cond;
}


/**
 * Determines the folder that contains the `index.php` file of the admin portal
 * based on multiple assumptions.
 *
 * This function Will return the path with trailing slash.
 *
 * Common use case of this helper method is to check if file exist or
 * as a reference point when uploading files.
 *
 * If the server is hosted publicly (by getting TLDs), this function will
 * assume that it lives in a special directory `/var/www/html/{OCS_ADMIN_URL}/public_html/`
 *
 * Otherwise it will assume that it is being served using the standard project
 * folder structure (ie. FCPATH/../../admin/back-end/)
 *
 * @return string
 */
function adminPortalPath()
{
    return FCPATH . DIRECTORY_SEPARATOR;

    // assume admin portal is located on '/var/www/html/{hostname}/public_html' or 'FCPATH/../../admin/back-end/'
    $admin_url = getenv('OCS_ADMIN_URL') ?: 'http://localhost:8888/';
    $admin_url = rtrim($admin_url, '/');
    $tokens = explode(".", $admin_url);
    $public_tlds = [".net", ".com", ".net.au", ".com.au"];
    $tld = "." . array_pop($tokens);
    $admin_path = implode(DIRECTORY_SEPARATOR, [realpath(dirname(dirname(FCPATH))), "admin", "back-end"]) . DIRECTORY_SEPARATOR;
    $parsed_url = parse_url($admin_url);
    if (in_array($tld, $public_tlds) && array_key_exists("host", $parsed_url)) {
        $admin_path = "/var/www/html/" . $parsed_url['host'] . "/public_html/";
    }

    return $admin_path;
}

function get_person_type($key)
{
    $personType = ['lead' => PERSON_TYPE_LEAD];
    return  $personType[$key] ?? 0;
}

function hoursandmins($time, $format = '%02d:%02d')
{
    if ($time < 1) {
        return;
    }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf($format, $hours, $minutes);
}

/**
 * format hours and mintues
 * @param {$time} str
 */
function formatHoursAndMinutes($time, $format = false)
{
    $hours = '00';
    $minutes = '00';
    $diff_time = '';
    $time_ex = explode(':', $time);
    if (!empty($time_ex[0])) {
        $hours = intVal($time_ex[0]);
    }
    if (!empty($time_ex[1])) {
        $minutes = intVal($time_ex[1]);
    }

    $h_label = 'h';
    $m_label = 'm';
    if ($format) {
        $h_label = 'hrs';
        $m_label = 'mins';
    }

    if ($hours > 0) {
        $diff_time .= $hours . $h_label;
    }
    if ($minutes > 0) {
        $diff_time .= ' ' . $minutes . $m_label;
    }

    return $diff_time;
}

function formatError($field, $message, $type = 'error', $encode = true) {
    $err = [
        'field' => $field,
        'type' => $type,
        'msg' => $message
    ];
    if ($encode) {
        return json_encode($err);
    }
    return $err;
}

function formatErrors($errors) {
    $field_errors = [];
    foreach($errors as $field => $err) {
        $errObj = json_decode($err);
        if (is_object($errObj) && property_exists($errObj, "field")) {
            $field_errors[$field] = formatError($errObj->field, $errObj->message);
        }
    }
    return $field_errors;
}
function fetchAsynchronously($urls = [])
{
    $multi = curl_multi_init();
    $channels = array();
    $contents = [];
    // Loop through the URLs, create curl-handles
    // and attach the handles to our multi-request
    foreach ($urls as $idx => $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_multi_add_handle($multi, $ch);

        $channels[$idx] = $ch;
    }

    // While we're still active, execute curl
    $active = null;
    do {
        $mrc = curl_multi_exec($multi, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active && $mrc == CURLM_OK) {
        // Wait for activity on any curl-connection
        if (curl_multi_select($multi) == -1) {
            continue;
        }

        // Continue to exec until curl is ready to
        // give us more data
        do {
            $mrc = curl_multi_exec($multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }

    // Loop through the channels and retrieve the received
    // content, then remove the handle from the multi-handle
    foreach ($channels as $idx => $channel) {
        $contents[$idx] = curl_multi_getcontent($channel);
        curl_multi_remove_handle($multi, $channel);
    }

    // Close the multi-handle and return our results
    curl_multi_close($multi);
    return $contents;
}

/**
 * uses Google Maps Distance Matrix to calculate the road distance between two addresses
 * need to enable Matrix API under the Google account
 */
function getDistanceBetweenTwoAddresses($addresses = null)
{
    $urls = [];
    $distances = [];
    if (!empty($addresses)) {        
        foreach($addresses as $obj) {
            $id = $obj->id;
            $address1 = $obj->address1;
            $address2 = $obj->address2;
            if (empty($address1) || empty($address2)) {
                $distances[$id] = [null, null];
                continue;
            }
            //fixed HCM-7671; address may be an object e.g. Org_model::get_organisation_address
            if (is_object($address1) && property_exists($address1, 'address')) {
                $address1 = $address1->address;
            }
            if (is_object($address2) && property_exists($address2, 'address')) {
                $address2 = $address2->address;
            }
            //if still $address1 and $address2 are not string, return null
            if (!is_string($address1) || !is_string($address2)) {
                $distances[$id] = [null, null];
                continue;
            }
            $address1 = str_replace(' ', '+', $address1);
            $address2 = str_replace(' ', '+', $address2);

            $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$address1}&destinations={$address2}&language=en-EN&sensor=false&key=" . GOOGLE_MAP_KEY;
            $urls[$id] = $url;
        }
        if (!empty($urls)) {
            $buffers = fetchAsynchronously($urls);            
            foreach($buffers as $id => $buffer) {
                $distance = $duration = 0;
                if (empty($buffer)) {
                    $distances[$id] = [null, null];
                    continue;
                }
                $output = object_to_array(json_decode($buffer));
                if (empty($output['status']) || $output['status'] != "OK") {
                    $distances[$id] = [null, null];
                    continue;
                }                    

                if (isset($output['rows']) && isset($output['rows']['0']) && isset($output['rows']['0']['elements']) && isset($output['rows']['0']['elements']['0']) && isset($output['rows']['0']['elements']['0']['distance']) && isset($output['rows']['0']['elements']['0']['distance']['value'])) {
                    $distance = $output['rows']['0']['elements']['0']['distance']['value'];
                    $distance = round($distance / 1000, 1);
                }

                if (isset($output['rows']) && isset($output['rows']['0']) && isset($output['rows']['0']['elements']) && isset($output['rows']['0']['elements']['0']) && isset($output['rows']['0']['elements']['0']['duration']) && isset($output['rows']['0']['elements']['0']['duration']['value'])) {
                    $duration = abs($output['rows']['0']['elements']['0']['duration']['value'] / 60);
                }
                $distances[$id] = [$distance, $duration];
            }
        }
    }

    return $distances;
}

/**
 * Helper function to pull the Business unit details and check Super admin access
 */
function get_business_unit_role($adminId) {
    $CI = &get_instance();
    $CI->db->select(['bu.id as bu_id','bu.business_unit_name as bu_name','r.id as role_id']);
    $CI->db->from(TBL_PREFIX . 'business_units as bu');
    $CI->db->join(TBL_PREFIX . 'user_business_unit as ubu', "ubu.bu_id = bu.id", 'inner');
    $CI->db->join(TBL_PREFIX . 'admin_role as ar', "ar.adminId = ubu.user_id", 'left');
    $CI->db->join(TBL_PREFIX . 'role as r', "r.id = ar.roleId and role_key = 'super_admin' ", 'left');
    $CI->db->where('ubu.user_id' , $adminId);
    
    $query = $CI->db->get();
    $result = (array) $query->row();
    $data = [];
    if(!empty($result)) {
        $data['is_super_admin'] = (!empty($result['role_id'])) ? TRUE : FALSE;
        $data['bu_id'] = $result['bu_id'] ?? '';
        $data['bu_name'] = $result['bu_name'] ?? '';
    }

    return $data;   

}

/** Get the Business units for dropdown */
function get_business_unit_options($reqData = '') {
    $bu_id = '';
    if(empty($reqData->business_unit['is_super_admin'])) {
        $bu_id = $reqData->business_unit['bu_id'];            
    } 

    $CI = &get_instance();
    $where = [];
    $where['archive'] = 0;
    if($bu_id) {
        $where['id'] = $bu_id;
    }
    return $CI->basic_model->get_record_where('business_units
        ', array('id as value', 'business_unit_name as label'), $where);
}