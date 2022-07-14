<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings_model extends CI_Model
{
    public function __construct()
    {
        // Call the CI_Model constructor
        // added dummy line 1
        parent::__construct();
    }

    /**
     * getting the general settings
     */
    public function get_general_settings() {
        $data = [
            'overtime_allowed' => get_setting(Setting::OVERTIME_ALLOWED, OVERTIME_ALLOWED),
            'privacy_idea_otp_enabled' => get_setting(Setting::PRIVACY_IDEA_OTP_ENABLED, PRIVACY_IDEA_OTP_ENABLED),
            'gap_between_shifts' => get_setting(Setting::GAP_BETWEEN_SHIFTS, GAP_BETWEEN_SHIFTS),
            'google_travel_duration' => get_setting(Setting::GOOGLE_DURATION_CHECK_ALLOWED, GOOGLE_DURATION_CHECK_ALLOWED),
            'sms_default_type' =>[ 
                'label' => get_setting(Setting::SMS_ATTRIBUTE_TYPE, SMS_ATTRIBUTE_TYPE),
                'value' => get_setting(Setting::SMS_ATTRIBUTE_TYPE, SMS_ATTRIBUTE_TYPE)
            ],
            'data_migration_access_token' => get_setting(Setting::DATA_MIGRATION_ACCESS_TOKEN, DATA_MIGRATION_ACCESS_TOKEN),
        ];
        // echo "1-".GAP_BETWEEN_SHIFTS."-2";
        return ['status' => true,'data' => $data];
    }

    /**
     * saving the general settings
     */
    public function save_general_settings($data, $adminId) {
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        $validation_rules = [
            [
                'field' => 'overtime_allowed',
                'label' => 'Overtime Allowed',
                'rules' => 'required' 
            ],
            [
                'field' => 'privacy_idea_otp_enabled',
                'label' => 'OTP allowed',
                'rules' => 'required' 
            ],
            [
                'field' => 'gap_between_shifts',
                'label' => 'Interval between shifts',
                'rules' => 'required|numeric'
            ],
            [
                'field' => 'sms_default_type',
                'label' => 'SMS default attribute type',
                'rules' => 'required' 
            ],
            [
                'field' => 'data_migration_access_token',
                'label' => 'Data Migration Access Token',
                'rules' => 'required' 
            ],
        ];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($validation_rules);

        // validation fails
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $result = ['status' => false, 'error' => implode(', ', $errors)];  
            return $result;
        }

        // save using this helper function
        save_setting(Setting::OVERTIME_ALLOWED, $data['overtime_allowed']);
        save_setting(Setting::PRIVACY_IDEA_OTP_ENABLED, $data['privacy_idea_otp_enabled']);
        save_setting(Setting::GAP_BETWEEN_SHIFTS, $data['gap_between_shifts']);
        save_setting(Setting::GOOGLE_DURATION_CHECK_ALLOWED, $data['google_travel_duration']);
        save_setting(Setting::SMS_ATTRIBUTE_TYPE, $data['sms_default_type']);
        save_setting(Setting::DATA_MIGRATION_ACCESS_TOKEN, $data['data_migration_access_token']);

        # change msg type
        $this->setDefaultMsgType($data['sms_default_type']);

        // apply logs
        $this->log($data, $adminId, [
            'title' => "Settings saved",
        ]);

        $result = ['status' => true, 'msg' => 'Settings successfully saved'];  
        return $result;
    }

    /** 
      * Change default msg type;
      * @param {str} $sms_type
      */
    public function setDefaultMsgType($sms_type) {
        $this->load->library('AmazonSms');

        # get sms type
        $result = $this->amazonsms->getSMSAttributes();

        if (!empty($result['status']) && $result['status'] == 200 && $result['data']['attributes']['DefaultSMSType'] != $sms_type)
        {
             # set sms detail
            $this->amazonsms->setMessage($msg_w_pref);
            $this->amazonsms->setPhoneNumber($pho_w_pref);
            $this->amazonsms->setDefaultSMSType($sms_type);
            # publish sms directly
            $result = $this->amazonsms->setSMSAttributes();
        }

        return $result;
    }

    /**
     * Create application log for this controller
     */
    protected function log($data, $adminId, array $mergeData = [])
    {
        $MODULE_ADMIN = 1;

        $defaults = [
            'user_id' => $adminId,
            'created_by' => $adminId,
            'title' => 'Settings updated',
            'description' => json_encode($data),
            'module' => $MODULE_ADMIN,
        ];

        $data = array_merge($defaults, $mergeData);

        // These items are override-able because it is in the $defaults var
        $this->loges->setUserId($data['user_id']);
        $this->loges->setCreatedBy($data['created_by']);
        $this->loges->setTitle($data['title']);
        $this->loges->setDescription($data['description']);
        $this->loges->setModule($data['module']);

        $this->loges->createLog();
    }
}
