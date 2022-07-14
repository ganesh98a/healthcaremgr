<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MobilePushNotification
 *
 * @author user
 */
class MobilePushNotification {
    /*
      function name: send_ios_notification
      @param: deviceToken
      @param: message
     */

    function send_ios_notification($deviceToken, $message) {

        $passphrase = 'PushChat';

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', path_of_file . '/your_pem_file.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        // Open a connection to the APNS server
        $fp = stream_socket_client(
                'ssl://gateway.sandbox.push.apple.com:2195', $err, // For development
                // 'ssl://gateway.push.apple.com:2195', $err, // for production
                $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp)
            exit("Failed to connect: $err $errstr" . PHP_EOL);

        //echo 'Connected to APNS' . PHP_EOL;
        // Create the payload body
        $body['aps'] = array('alert =&amp;amp;amp;gt;' . trim($message),
            'sound =&amp;amp;amp;gt; default'
        );

        // Encode the payload as JSON
        $payload = json_encode($body);

        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', trim($deviceToken)) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));

        if (!$result) {
            //echo 'Message not delivered' . PHP_EOL;
        } else {
            //echo 'Message successfully delivered' . PHP_EOL;
            return $result;
        }

        // Close the connection to the server
        fclose($fp);
    }

    function push_notification_android($device_ids, $message) {

        //API URL of FCM
        $url = 'https://fcm.googleapis.com/fcm/send';

        /* api_key available in:
          Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key */
        $api_key = 'AAAASNlnmco:APA91bEagYKcAwNFdH5p8HaCzv-xKl3ie5TXVf4rjmBitK0O4lNZnSo8Yp-ppZ4M1TAzWXvzdKbDD-yZrJuzWJTrWxqTBi9RYAvyKOEj0vSfdFvUmkp-r6DQ1YipamsU6L034msjnQHC';

        $fields = array(
            'registration_ids' => $device_ids,
            'data' => array
                (
                'message' => $message,
                'title' => 'TestTitle',
                'subtitle' => 'TestSubtitle',
                'tickerText' => 'TestTicker',
                'vibrate' => 1,
                'sound' => 1,
                'largeIcon' => 'large_icon',
                'smallIcon' => 'small_icon'
            )
        );

        //header includes Content type and api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . $api_key
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

}
