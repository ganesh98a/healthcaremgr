<?php

/**
 * Description of Sendsms
 *
 * @author Ritesh Paliwal
 * @from CSS
 */
//namespace Esendex;
//use Esendex;
class Sms {
    /*
     * All constant is defined in required files
     * MESSAGE_SEND_FROM = Send from
     * ESENDEX_ACCOUNT_REFERENCE = Your Esendex Account Reference
     * SMS_LOGIN_EMAIL_ADDRESS = Your login email address
     * SMS_LOGIN_PASSWORD = Your password
     * 61433706380
     */

    public function sendsms($number, $message_body) {
        require_once APPPATH . 'third_party/EsendexMessageSrc/src/autoload.php';
        $message = new \Esendex\Model\DispatchMessage(MESSAGE_SEND_FROM, $number, $message_body, \Esendex\Model\Message::SmsType);
        $authentication = new \Esendex\Authentication\LoginAuthentication(ESENDEX_ACCOUNT_REFERENCE, SMS_LOGIN_EMAIL_ADDRESS, SMS_LOGIN_PASSWORD);
        $service = new \Esendex\DispatchService($authentication);
        $result = $service->send($message);
        if (!empty($result))
            return ['success' => true, 'id' => $result->id(), 'uri' => $result->uri(), 'complete_response' => $result];
        else
            return ['success' => false];
    }

    public function send_msg_mail($request_params) {
        $response = '';
        if (!empty($request_params)) {
            if (SMS_CHANNEL == 'MAIL') {
                // die('calling mail');
                //send mail
                $to_email = isset($request_params['to_email']) ? $request_params['to_email'] : '';
                $subject = $request_params['subject'];
                $cc_email = isset($request_params['cc_email']) ? $request_params['cc_email'] : '';
                $body = $request_params['body'];
                if (!empty($to_email)) {
                    send_mail($to_email, $subject, $body, $cc_email = null);
                    return true;
                } else {
                    return false;
                }
            } else if (SMS_CHANNEL == 'SMS') {
                #die('calling msg');
                //send message
                $number = isset($request_params['number']) ? $request_params['number'] : '';
                $message_body = $request_params['body'];
                if (!empty($number)) {
                    $response = $this->sendsms($number, $message_body);
                }
                return $response;
            }
        }
    }

}
