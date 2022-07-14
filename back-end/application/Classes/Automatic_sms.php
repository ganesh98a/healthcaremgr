<?php

/*
 * Class - Automatic_sms
 * Used to send the sms to user directly
 */

class Automatic_sms {

    function __construct() {
        $this->CI = &get_instance();

        $this->object_orm = [
            'Shift' => [
                'module' => 'schedule',
                'class_name' => 'Shift_sms_log_model'
            ],
        ];
    }

    private $templateId;
    private $templateContent;
    private $send_to;
    private $sms_log_data;

    function setSendTo($send_to) {
        $this->send_to = $send_to;
    }

    function getSendTo() {
        return $this->send_to;
    }

    function setTemplateId($templateId) {
        $this->templateId = $templateId;
    }

    function getTemplateId() {
        return $this->templateId;
    }

    function getTemplateContent() {
        return $this->templateContent;
    }

    function setSMSLogData($sms_log_data) {
        $this->sms_log_data = $sms_log_data;
    }

    function getSMSLogData() {
        return $this->sms_log_data;
    }

    /**
     * Send SMS to user directly
     */
    function automatic_sms_send_to_user($objectData = null, $objectName = '') {

        $result = [];

        # get instance
        $CI = &get_instance();

        # load model & library
        $CI->load->model('sms/Sms_template_model');
        $CI->load->library('AmazonSms');

        # get template content
        $template_data = $CI->Sms_template_model->get_template_content_details_by_template_id($this->templateId);
        # check the template have value
        if (isset($template_data) == true && isset($template_data['content']) == true) {

            $this->templateContent = $template_data['content'];

            $msg_content = $template_data['content'];
            //extract dynamic vars from sms template
            preg_match_all("/\{([^\}]*)\}/", $msg_content, $matches);
            if (!empty($matches[1])) {
                $vars = $matches[1];
                $object_vars = $objectData->object_fields;
                $values = [];
                foreach($vars as $var) {
                    $parts = explode('.', $var);
                    $tobject = $parts[0];
                    $tfield = $parts[1];
                    $key = "{".$var."}";
                    if ($tobject == $objectName && property_exists($objectData, $tfield)) {
                        $values[$key] = $objectData->$tfield;
                    } else if(array_key_exists($tobject, $object_vars)) {
                        $values[$key] = $object_vars[$tobject][$tfield];
                    }
                }
                $msg_content = str_replace(array_keys($values), array_values($values), $msg_content);
                $this->templateContent = $msg_content;
            }
            $send_to = $this->send_to;

            # prepend australia country code
            $send_to = $CI->amazonsms->country_code_aus.$send_to;

            # set sms detail
            $CI->amazonsms->setMessage($msg_content);
            $CI->amazonsms->setPhoneNumber($send_to);
            # publish sms directly
            $result = $CI->amazonsms->publishSms();
        }
        return $result;
    }

    /**
     * Load the model class dynamically, used for process builder
     */
    public function createSMSLog($object_name)
    {
        if (array_key_exists($object_name, $this->object_orm)) {
            $model_info = $this->object_orm[$object_name];
            //load model
            extract($model_info);
            if (!empty($module) && !empty($class_name)) {
                $this->CI->load->model("$module/$class_name");
                # call function
                if (method_exists($this->CI->$class_name, 'save_log')) {
                    return $this->CI->$class_name->save_log($this->sms_log_data);
                }
            }
        }
        return false;
    }

    /**
     * Save custom log
     * @param {array} $response
     * @param {str} $object_name,
     * @param {int} $source_id
     */
    public function SMSCustomeLog($response, $object_name, $source_id) {
        # custom log
        $error = [];
        $error['status'] = 200;
        $error['message'] = 'SMS Sent Successfully';
        $error['payload'] = [ 'request_data' => [
            'source_id' => $source_id,
            'send_to' => $this->send_to, 
            'content' => $this->templateContent,
            'response' => json_encode($response)
        ] ];
        $error['exception'] = '';
        $error['operation'] = base_url('');
        $error['module'] = $object_name;
        log_msg($error['message'], 200, $error['payload'], $error['exception'], $error['operation'], $error['module'], '', '');
    }
}
