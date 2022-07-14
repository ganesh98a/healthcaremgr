<?php 
class CheckArchivePrimary {
    protected  $CI;
    private $primaryKeyData =array( 
                'participant_about_care' => array( 
                    'column_name' => 'primary_key',  // column name for check
                    'primary_key_value' => 1, // primary_key_value value type for update exists first secondary row
                    'secondary_key_value' =>2, // secondary value type for checking row exists or not 
                    'table_primary_column_name' => 'id' , // auto increment column name
                    'unique_column_name' => 'participantId', // unique user row getting
                    'error_msg' => '',
                    'other_column_match' => 'categories' // it matches secondary column type exist row or not
                )
            );
    protected $table;
    protected $errormsg = '';
    protected $defaultMsg = 'Secondary data not exist here.';
    protected $currentData = array();

    public function __construct() {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();
    }

    function setCurrentData($tableName = '') {
        $this->table = !empty($tableName) && isset($this->primaryKeyData[$tableName]) ? $tableName : ''; // without prefix
        $this->currentData = !empty($tableName) && isset($this->primaryKeyData[$tableName]) ? $this->primaryKeyData[$tableName] : array();
    }

    function getCurrentData($tableName) {
        return  $this->currentData;
    }

    function checkSecondaryDataExist($reqData, $withInsert = false){
        if(empty($this->table) || empty($this->currentData)){
            return array('status' => false, 'msg' => 'Something went wrong');
        }
        $column = array($this->currentData['column_name'] , $this->currentData['table_primary_column_name'], $this->currentData['unique_column_name']);
        if(isset($this->currentData['other_column_match']) && !empty($this->currentData['other_column_match'])){
            $column[] = $this->currentData['other_column_match'];
        }
        $whereCurrent = array('id' => $reqData->id, 'archive'=>0);
        $getResult = $this->CI->basic_model->get_row($this->table, $column, $whereCurrent );
        if(!$getResult){
            return array('status' => false, 'msg' => 'row not found for this action');
        }
        $getResult = (array) $getResult;
        
        $where = array(
            $this->currentData['column_name'] => $this->currentData['secondary_key_value'] ,
            'archive' => 0,
            $this->currentData['unique_column_name'] => $getResult[$this->currentData['unique_column_name']]
        );
        if(isset($this->currentData['other_column_match']) && !empty($this->currentData['other_column_match']) ){
            $where[$this->currentData['other_column_match']] = $getResult[$this->currentData['other_column_match']];
        }

        $resultDataExists = $this->CI->basic_model->get_row($this->table, $column, $where);
        $sts = false;
        $msg = '';
        if(!$resultDataExists){
            $msg = isset($this->currentData['error_msg']) && !empty($this->currentData['error_msg']) ? $this->currentData['error_msg'] : $this->defaultMsg;
        }

        if($withInsert && $resultDataExists){
            $resultDataExists = (array) $resultDataExists;
            $whereSecondary = array($this->currentData['table_primary_column_name'] => $resultDataExists[$this->currentData['table_primary_column_name']],  $this->currentData['unique_column_name'] => $getResult[$this->currentData['unique_column_name']] );
            $updateData = array($this->currentData['column_name'] => $this->currentData['primary_key_value']);
            if(!empty($whereSecondary) && !empty($updateData)){
                $resultUpdate = $this->CI->basic_model->update_records($this->table, $updateData , $whereSecondary);
                $resultInsert = $this->CI->basic_model->update_records($this->table, array('archive' => 1), $where = array('id' => $reqData->id, $this->currentData['unique_column_name'] => $getResult[$this->currentData['unique_column_name']]));
                $sts = true;
            }
        }
        return array('status' => $sts,'msg' => $msg);
    }
}