<?php

/*
 * To use PrivacyIDEA as token/otp service provider
 * creating users list, setting up tokens, assigning and emailing the token
 * and finally validating tokens
 * 
 * @author Pranav Gajjar
 */

class PrivacyIdea {

    private $host_url;
    private $api_function;
    private $api_user;
    private $api_password;

    private $genkey = 1;
    private $keysize = 20;
    private $realm = "vpn";
    private $resolver = "hcm";
    private $hashlib = "sha1";
    private $otp_type = "EMail";
    private $description = 'HCM OTP';
    private $otp_validity; # number of minutes
    private $otp_len;
    private $otp_max_fail_count;

    private $user;
    private $password;
    private $email;
    private $name;
    private $mobile;
    private $phone;
    private $validity_period_start;
    private $validity_period_end;
    private $serial;
    private $token;
    private $error;
    private $pin;

    /**
     * setting the initial values to class variables
     */
    function __construct() {
        $this->CI = &get_instance();
        $this->host_url = PRIVACY_IDEA_HOST_URL;
        $this->otplen = OTP_LENGTH;
        $this->otp_validity = OTP_VALIDITY;
        $this->otp_max_fail_count = OTP_MAX_FAIL_COUNT;
        $this->api_user = OTP_USER;
        $this->api_pass = OTP_PASS;
    }

    /**
     * getting the error assigned
     */
    function get_error() {
        return $this->error;
    }

    /**
     * getting the serial number assigned
     */
    function get_serial() {
        return $this->serial;
    }

    /**
     * main function that calls the PI api
     * mostly uses the post method for provided PI function calls
     */
    function call_api($data, $post = true) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host_url.$this->api_function);
        if($post) {
        curl_setopt($ch, CURLOPT_POST, $post);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        else {
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $headers[] = 'Content-Type: application/json';
        if($this->token)
        $headers[] = 'Authorization: '.$this->token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        $return = json_decode($result);

        if(isset($return->result) && !empty($return->result->status) && isset($return->detail->message) && isset($return->result->value) && $return->result->value == false) {
            $this->error = $return->detail->message;
        }
        else if(isset($return->result) && isset($return->result->status) && $return->result->status == 1) {
            if(isset($return->result->value) && isset($return->result->value->token))
            $this->token = $return->result->value->token;
            return $return;
        }
        else if(isset($return->result) && empty($return->result->status)) {
            if(isset($return->result->error) && isset($return->result->error->message))
            $this->error = $return->result->error->message;
        }
        return false;
    }

    /**
     * authenticating the PI and sets the token
     */
    function AuthenticateDetails() {
        $this->api_function = "auth";
        $data['username'] = $this->api_user;
        $data['password'] = $this->api_pass;
        return $this->call_api($data);
    }

    /**
     * getting the user information from username/email
     */
    function GetUser($username) {
        $this->api_function = "user/";
        $this->username = $username;
        $data['username'] = $username;
        $data['realm'] = $this->realm;
        $data['resolver'] = $this->resolver;
        $return = $this->call_api($data, false);
        if($return && isset($return->result->value) && isset($return->result->value[0]) && isset($return->result->value[0]->username) && $return->result->value[0]->username == $username) {
            $this->email = $username;
            $this->resolver = $return->result->value[0]->resolver;
            return true;
        }
        else
            return false;
    }

    /**
     * creates a new user using username/email, temp password, name and phone
     */
    function CreateUser($username, $password, $name, $phone) {
        $this->api_function = "user/";
        $this->username = $username;
        $this->password = $password;
        $this->email = $username;
        $this->name = $name;
        $this->phone = $phone;

        $data['user'] = $username;
        $data['password'] = $password;
        $data['email'] = $username;
        $data['givenname'] = $name;
        $data['phone'] = $phone;
        $data['realm'] = $this->realm;
        $data['resolver'] = $this->resolver;
        $return = $this->call_api($data, true);
        if($return)
            return true;
        else
            return false;
    }

    /**
     * validating the OTP against the optional serial number
     */
    public function ValidateOTP($username, $pin, $serial = null) {
        $this->api_function = "validate/check";
        $this->username = $username;
        $this->pin = $pin;
        if($serial)
        $this->serial = $serial;

        $data['user'] = $this->username;
        $data['realm'] = $this->realm;
        $data['serial'] = $this->serial;
        $data['pass'] = $this->pin;
        $data['otponly'] = 1;
        $return = $this->call_api($data, true);
        if($return && isset($return->result->value) && isset($return->result->status) && !empty($return->detail->value) && !empty($return->detail->status)) {
            return true;
        }
        else
            return false;
    }

    /**
     * emails the OTP to the user email using the optional serial
     */
    public function EmailOTP($username, $serial = null) {
        $this->api_function = "validate/triggerchallenge";
        $this->username = $username;
        if($serial)
        $this->serial = $serial;

        $data['user'] = $this->username;
        $data['realm'] = $this->realm;
        $data['serial'] = $this->serial;
        $return = $this->call_api($data, true);
        if($return && isset($return->result->value) && isset($return->result->status) && !empty($return->detail->value) && !empty($return->detail->status)) {
            return true;
        }
        else
            return false;
    }

    /**
     * generating the OTP for a given username/email
     */
    public function GenerateOTP($username) {
        $this->api_function = "token/init";
        $this->username = $username;
        $this->email = $username;

        $dt = new DateTime(date("Y-m-d H:i:s"));
        $dt->setTimezone(new DateTimeZone('Australia/Melbourne'));
        $validity_period_start = $dt->format('Y-m-d H:i:00P');
        $dt->modify('+'.$this->otp_validity.' minutes');
        $validity_period_end = $dt->format('Y-m-d H:i:00P');
        $this->validity_period_start = str_replace(" ","T",$validity_period_start);
        $this->validity_period_end = str_replace(" ","T",$validity_period_end);

        $data['genkey'] = $this->genkey;
        $data['keysize'] = $this->keysize;
        $data['user'] = $username;
        $data['email'] = $username;
        $data['otplen'] = $this->otp_len;
        $data['type'] = $this->otp_type;
        $data['hashlib'] = $this->hashlib;
        $data['validity_period_start'] = $this->validity_period_start;
        $data['validity_period_end'] = $this->validity_period_end;
        $data['description'] = $this->description;
        $data['realm'] = $this->realm;
        $data['max_failcount'] = $this->otp_max_fail_count;
        $return = $this->call_api($data, true);
        if($return && isset($return->result->value) && isset($return->detail->serial) && !empty($return->detail->serial)) {
            $this->serial = $return->detail->serial;
            return true;
        }
        else
            return false;
    }
}
