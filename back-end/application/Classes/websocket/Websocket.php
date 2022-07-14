<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InternalMessageWebsocket
 *
 * @author user
 */
class Websocket {

    public $CI;

    function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->model('imail/Internal_model');
    }

    function check_webscoket_on() {
        if (WEBSOCKET_SERVER_ON) {
            return true;
        }

        return false;
    }

    function get_token() {
        return encrypt_decrypt('encrypt', WEBSOCKET_SERVER_KEY);
    }

    function send_message($msg, $socketId) {
        $msg = json_encode($msg);
        $encrypted_text = $this->mask($msg);

        @socket_write($socketId, $encrypted_text, strlen($encrypted_text));
        return true;
    }

//Unmask incoming framed message
    function unmask($text) {
        $length = ord($text[1]) & 127;
        if ($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif ($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }
        $text = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }

        return $text;
    }

//Encode message for transfer to client.
    function mask($text) {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);

        if ($length <= 125)
            $header = pack('CC', $b1, $length);
        elseif ($length > 125 && $length < 65536)
            $header = pack('CCn', $b1, 126, $length);
        elseif ($length >= 65536)
            $header = pack('CCNN', $b1, 127, $length);
        return $header . $text;
    }

    function check_token($token) {
        $colown = array('memberId as adminId');
        $where = array('token' => $token);
        $response = $this->CI->basic_model->get_row('member_login', $colown, $where);

        if (!empty($response)) {

            return $response->adminId;
        } else {
            return false;
        }
    }
    
    function check_participant_token($token) {
        $colown = array('participantId');
        $where = array('token' => $token);
        $response = $this->CI->basic_model->get_row('participant_login', $colown, $where);

        if (!empty($response)) {

            return $response->participantId;
        } else {
            return false;
        }
    }

    /*
     * function : check_server_token
     * 
     * use: check server token, token store in constant file and constant is common for every module
     */

    function check_server_token($token) {
        $key = encrypt_decrypt('decrypt', $token);
        if ($key === WEBSOCKET_SERVER_KEY) {
            return true;
        } else {
            return false;
        }
    }

    function get_admin_details($adminId) {
        return $this->CI->Internal_model->get_admin_details($adminId);
    }

    function handshake($host, $port, $client_conn, $headers) {

        $secKey = $headers['Sec-WebSocket-Key'];
        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

        //hand shaking header
        $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "WebSocket-Origin: $host\r\n" .
                "WebSocket-Location: ws://$host:$port\r\n" .
                "Sec-WebSocket-Accept:$secAccept\r\n\r\n";

        socket_write($client_conn, $upgrade, strlen($upgrade));
        
//        print_r($upgrade);
    }

    /**
     * Generate a random string for WebSocket key.
     * @return string Random string
     */
    function generateKey() {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"$&/()=[]{}0123456789';
        $key = '';
        $chars_length = strlen($chars);
        for ($i = 0; $i < 16; $i++)
            $key .= $chars[mt_rand(0, $chars_length - 1)];
        return base64_encode($key);
    }

    function hybi10Decode($data) {
        $bytes = $data;
        $dataLength = '';
        $mask = '';
        $coded_data = '';
        $decodedData = '';
        $secondByte = sprintf('%08b', ord($bytes[1]));
        $masked = ($secondByte[0] == '1') ? true : false;
        $dataLength = ($masked === true) ? ord($bytes[1]) & 127 : ord($bytes[1]);

        if ($masked === true) {
            if ($dataLength === 126) {
                $mask = substr($bytes, 4, 4);
                $coded_data = substr($bytes, 8);
            } elseif ($dataLength === 127) {
                $mask = substr($bytes, 10, 4);
                $coded_data = substr($bytes, 14);
            } else {
                $mask = substr($bytes, 2, 4);
                $coded_data = substr($bytes, 6);
            }
            for ($i = 0; $i < strlen($coded_data); $i++) {
                $decodedData .= $coded_data[$i] ^ $mask[$i % 4];
            }
        } else {
            if ($dataLength === 126) {
                $decodedData = substr($bytes, 4);
            } elseif ($dataLength === 127) {
                $decodedData = substr($bytes, 10);
            } else {
                $decodedData = substr($bytes, 2);
            }
        }

        return $decodedData;
    }

    function hybi10Encode($payload, $type = 'text', $masked = true) {
        $frameHead = array();
        $frame = '';
        $payloadLength = strlen($payload);

        switch ($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;

            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;

            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;

            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }

            // most significant bit MUST be 0 (close connection if frame too big)
            if ($frameHead[2] > 127) {
                $this->close(1004);
                return false;
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }

        if ($masked === true) {
            // generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);
        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }

    function send_data_on_socket($data) {
        $origin = "http://" . WEBSOCKET_HOST_NAME . "";  //url where this script run
        // Generate the WebSocket key.
        $key = $this->generateKey();

        $enc_data = $this->hybi10Encode(json_encode($data));

        $reqData = '?chanel=server&req_type=server&token=' . $this->get_token() . '&blk=blank';

        $head = "GET /" . $reqData . " HTTP/1.1\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "Host: " . WEBSOCKET_HOST_NAME . ":" . WEBSOCKET_HOST_PORT . "\r\n" .
                "Origin: $origin\r\n" .
                "Sec-WebSocket-Key: $key\r\n" .
                "Sec-WebSocket-Version: 13\r\n\r\n";

        if ($sock = @fsockopen(WEBSOCKET_HOST_NAME, WEBSOCKET_HOST_PORT, $errno, $errstr)) {

            fwrite($sock, $head);
            $header = fread($sock, 2000);

            $headers = array();
            $lines = preg_split("/\r\n/", $header);

            foreach ($lines as $line) {
                $line = chop($line);
                if (preg_match('/\A(\S+):(.*)\z/', $line, $matches)) {
                    $headers[$matches[1]] = $matches[2];
                }
            }

            if (!empty($headers['Sec-WebSocket-Accept'])) {
                if ($headers['Sec-WebSocket-Accept'] === false) {
                    
                } else {
                    $keyAccept = $headers['Sec-WebSocket-Accept'];
                    $expectedResonse = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

                    if ($keyAccept !== $expectedResonse) {
                        
                    } else {
                        return fwrite($sock, $enc_data) or die('error:' . $errno . ':' . $errstr); //Server ignores this message
//                    fclose($sock);
                    }
                }
            }
        } else {
            return false;
        }
    }

}
