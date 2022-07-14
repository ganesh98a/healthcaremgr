<?php

namespace KeyPayCommonAtuh;

require_once APPPATH.'third_party/KeyPay/http_client.class.php';
use HttpClient;

class KeyPayCommonAtuh {
    protected $CI;
    protected $table;
    protected $tableWithPreFIx;
    private $companyId = 0;
     public function __construct() {
        // Call the CI_Model constructor
        $this->CI = & get_instance();
        $this->CI->load->model('Basic_model');
        $this->table = 'keypay_auth_details';
        if(!defined('TBL_PREFIX')){
            defined("TBL_PREFIX",'tbl_');
        }
        $this->tableWithPreFIx = TBL_PREFIX.$this->table;
    }

    protected function getCompanyId(){
        return (int)$this->companyId;
    }

   protected function setCompanyId($companyIdData){
       $this->companyId = (int) $companyIdData;
    }

    public function addAnd($v=0){
        return $v>0 ? ' AND ' : '';
    }

    protected function keyPayAuth()
	{
		$row = $this->CI->Basic_model->get_row('keypay_auth_details',['api_key','business_id','kiosks_id','location_id','id'],['companyId'=>$this->companyId,'status'=>1]);

		if(!$row){
			return ['status'=>false,'error'=>'configuration data is missing'];
		}

		if(!isset($row->api_key) || !isset($row->business_id)  ||  empty($row->api_key) || empty($row->business_id)){
			return ['status'=>false,'error'=>'configuration data is missing'];

        }
        
        //header('Content-Type: text/plain');

        $http = new HttpClient();
        $http->set_curlopt(CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        $http->set_curlopt(CURLOPT_USERPWD, $row->api_key . ':');

        return ['status'=>true,'business_id'=>$row->business_id,'keyPayObject'=>$http,'url'=>"https://api.yourpayroll.com.au/api/v2/business/",'url_with_id'=>"https://api.yourpayroll.com.au/api/v2/business/".$row->business_id."/",'auth_details'=>(array)$row];
    }

	protected function parserObjToArr($data=[])
    {
       return (!empty($data) && ( is_object($data) || is_array($data))) ? json_decode(json_encode($data),True):(!empty($data) && is_string($data)? json_decode(json_encode(json_decode($data)),True):[]);
    }


}
