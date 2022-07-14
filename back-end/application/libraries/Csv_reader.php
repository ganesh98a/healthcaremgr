<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

use League\Csv\Reader;
use League\Csv\Writer;

class csv_reader{ 
	public function __construct()
    {        
        require_once APPPATH.'/third_party/csv/vendor/autoload.php';    
        
    }


    function read_csv_data($tmpName){
        $reader = Reader::createFromPath($tmpName, 'r');            
        $results = $reader->fetchAll();        
        return $results;
    }

}

