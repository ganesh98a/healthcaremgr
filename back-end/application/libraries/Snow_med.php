<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Snow_med {

    #https://browser.ihtsdotools.org/snowstorm/snomed-ct/browser/MAIN/SNOMEDCT-AU/2019-07-31/concepts/425887005/children?form=inferred  //children
    #https://browser.ihtsdotools.org/snowstorm/snomed-ct/browser/MAIN/SNOMEDCT-AU/2019-07-31/concepts/445198003/parents?form=inferred //parent

    public function search_diagnosis($srch_value) {
        if (empty($srch_value)) {
            echo json_encode(array('error' => 'Please provide Diagnosis.'));
            die;
        }
        $srch_value = urlencode($srch_value);
        error_reporting(E_ALL);
        ini_set("display_errors", 1);

        #https://browser.ihtsdotools.org/snowstorm/snomed-ct/browser/MAIN/SNOMEDCT-AU/2019-07-31/descriptions?&limit=100&term=fever&active=true&conceptActive=true&lang=english&&semanticFilter=disorder

        /*
        /v2/snomed/{edition}/{release}/descriptions?query={query}&searchMode={mode}&lang={language}&statusFilter={statusFilter}&skipTo={skipTo}&returnLimit={returnLimit}&normalize={normalize}&semanticFilter={semanticFilter}&moduleFilter={moduleFilter}&refsetFilter={refsetFilter}
        */

        #semanticFilter = disorder
        #"isLeafStated" : true and we should display "pt" : {    "term" 

        $look_up_url = 'https://browser.ihtsdotools.org/snowstorm/snomed-ct/browser/MAIN/SNOMEDCT-AU/2019-07-31/';
        $srch_url = "descriptions?&limit=100&term=$srch_value&active=true&conceptActive=true&lang=english&semanticTag=disorder";

        //setting url
        $url = $look_up_url . $srch_url;
        #echo $url;
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