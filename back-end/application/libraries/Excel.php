<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');  
 
require_once APPPATH."/third_party/PhpSpreadsheet/vendor/autoload.php";

class Excel {
	
	Protected $tmp_file_name;
	Protected $worksheet;
	Protected $total_columns;
	Protected $total_rows;
	Protected $rows;
	Protected $alphabet;

    public function __construct() {
        // parent::__construct();
        $this->alphabet = range('A', 'Z');
    }

    # data set
    public function setTmpFileName($tmp_file_name) {
    	$this->tmp_file_name = $tmp_file_name;
    }

    public function getTmpFileName($tmp_file_name) {
    	return $this->tmp_file_name;
    }

    public function setWorkSheet($worksheet) {
    	$this->worksheet = $worksheet;
    }

    public function getWorkSheet($worksheet) {
    	return $this->worksheet;
    }

    public function setTotalColumns($columns) {
    	$this->total_columns = $columns;
    }

    public function getTotalColumns($columns) {
    	return $this->total_columns;
    }

    public function setTotalRows($rows) {
    	$this->total_rows = $rows;
    }

    public function getTotalRows($rows) {
    	return $this->total_rows;
    }

    public function setRows($rows) {
    	$this->rows = $rows;
    }

    public function getRows($rows) {
    	return $this->rows;
    }

    /**
     * Gether data from sheet
     */
    function read_data_from_file() {

    	# create reader IO
    	$excelReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($this->tmp_file_name);
        $excelObj = $excelReader->load($this->tmp_file_name);
        $worksheet = $excelObj->getSheet($this->worksheet);

        #  Get worksheet dimensions
        $sheet = $excelObj->getActiveSheet(); 
        $highestRow = $sheet->getHighestRow(); 
        $highestColumn = $sheet->getHighestColumn();

        # get rows
        $rows = $worksheet->toArray();

        $total_cloumns = array_search($highestColumn, $this->alphabet);
        
        # set data
        $this->setTotalColumns($total_cloumns);
        $this->setTotalRows($highestRow);
        $this->setRows($rows);
        
        $return = [
        	'rows' => $rows,
        	'total_row' => $highestRow,
        	'total_column' => $total_cloumns
        ];

        return $return;
    }
}