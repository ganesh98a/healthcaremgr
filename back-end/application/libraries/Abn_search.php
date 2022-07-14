<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Abn_search {

    public function search_abn_by_name($name) {
        if (empty($name)) {
            echo json_encode(array('error' => 'Please provide Organisation Name.'));
            die;
        }
        $name = urlencode($name);
        error_reporting(E_ALL);
        ini_set("display_errors", 1);

        $look_up_domain = 'https://abr.business.gov.au/json/';
        $look_up_srch_url = "MatchingNames.aspx?name=$name&maxResults=10&callback=callback&guid=" . GUID;

        //setting url
        $url = $look_up_domain . $look_up_srch_url;
        //echo $url;
        $resp = '';
        try {
            $curl = curl_init();
            // Set some options - we are passing in a useragent too here
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url
                    //CURLOPT_USERAGENT => 'YDT'
            ));
            // Send the request & save response to $resp
            $resp = curl_exec($curl);
            //echo '<pre>';print_r($resp);
            if (FALSE === $resp)
                throw new Exception(curl_error($curl), curl_errno($curl));

            // ...process $resp now
        } catch (Exception $e) {
            $resp = trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }
        return $resp;
    }
    
    public function search_name_by_abn_number($name) {
        if (empty($name)) {
            echo json_encode(array('error' => 'Please provide abn number.'));
            die;
        }
        $name = urlencode($name);
        error_reporting(E_ALL);
        ini_set("display_errors", 1);

        $look_up_domain = 'https://abr.business.gov.au/json/';
        $look_up_srch_url = "AbnDetails.aspx?abn=$name&maxResults=10&callback=callback&guid=" . GUID;

        //setting url
        $url = $look_up_domain . $look_up_srch_url;
        //echo $url;
        $resp = '';
        try {
            $curl = curl_init();
            // Set some options - we are passing in a useragent too here
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url
                    //CURLOPT_USERAGENT => 'YDT'
            ));
            // Send the request & save response to $resp
            $resp = curl_exec($curl);
            //echo '<pre>';print_r($resp);
            if (FALSE === $resp)
                throw new Exception(curl_error($curl), curl_errno($curl));

            // ...process $resp now
        } catch (Exception $e) {
            $resp = trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }
        return $resp;
    }
    
    public function search_name_by_acn_number($name) {
        if (empty($name)) {
            echo json_encode(array('error' => 'Please provide abn number.'));
            die;
        }
        $name = urlencode($name);
        error_reporting(E_ALL);
        ini_set("display_errors", 1);

        $look_up_domain = 'https://abr.business.gov.au/json/';
        $look_up_srch_url = "AcnDetails.aspx?acn=$name&maxResults=10&callback=callback&guid=" . GUID;

        //setting url
        $url = $look_up_domain . $look_up_srch_url;
        //echo $url;
        $resp = '';
        try {
            $curl = curl_init();
            // Set some options - we are passing in a useragent too here
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url
                    //CURLOPT_USERAGENT => 'YDT'
            ));
            // Send the request & save response to $resp
            $resp = curl_exec($curl);
            //echo '<pre>';print_r($resp);
            if (FALSE === $resp)
                throw new Exception(curl_error($curl), curl_errno($curl));

            // ...process $resp now
        } catch (Exception $e) {
            $resp = trigger_error(sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }
        return $resp;
    }

}

?>