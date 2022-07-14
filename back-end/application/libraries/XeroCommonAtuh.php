<?php

namespace XeroCommonAtuh;

require_once APPPATH.'third_party/Xero/vendor/autoload.php';
use XeroPHP\Models\Accounting\Item;
use XeroPHP\Models\Accounting\Account;
use XeroPHP\Models\Accounting\BrandingTheme;
use XeroPHP\Models\Accounting\Currency;

class XeroCommonAtuh {
    protected $CI;
    private $companyId = 0;
     public function __construct() {
        // Call the CI_Model constructor
        $this->CI = & get_instance();
        $this->CI->load->model('Basic_model');
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

    protected function xeroAuth()
	{
		$row = $this->CI->Basic_model->get_row('xero_auth_details',['xero_consumer_key','xero_consumer_secret','xero_rsa_private_key'],['companyId'=>$this->companyId,'status'=>1]);

		if(!$row){
			return $row;
		}

		if(!isset($row->xero_consumer_key) || !isset($row->xero_consumer_secret) || !isset($row->xero_rsa_private_key) ||  empty($row->xero_consumer_key) || empty($row->xero_consumer_secret) || empty($row->xero_rsa_private_key) || (isset($row->xero_rsa_private_key) && !empty($row->xero_rsa_private_key) && !file_exists(APPPATH.'third_party/Xero/'.$row->xero_rsa_private_key))){
			return false;
		}


		$config = [
			'oauth' => [
				'callback' => base_url(),
				'consumer_key' => $row->xero_consumer_key,
				'consumer_secret' => $row->xero_consumer_secret,
				'rsa_private_key' => file_get_contents(APPPATH.'third_party/Xero/'.$row->xero_rsa_private_key),
			],
		];
		//$xero = new PrivateApplication($config);
		$xero = new \XeroPHP\Application\PrivateApplication($config);
		return $xero;
    }

    public function getRandNum()
	{
		$randNum = strval(rand(1000,100000));
		return $randNum;
	}

	public function get_item_code(){
        $xeroObj = $this->xeroAuth();
        if(!$xeroObj){
            return ['status'=>false,'msg'=>'Something went wrong in config details.'];
        }
        try{
            $Item = $xeroObj->load(Item::class)->execute();
            $Item =  $this->parserObjToArr($Item);
            return ['status'=>true,'data'=>$Item];
        } catch ( NotFoundException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        }
	}

	public function get_accounts_code(){
        $xeroObj = $this->xeroAuth();
        if(!$xeroObj){
            return ['status'=>false,'msg'=>'Something went wrong in config details.'];
        }
        try{
            $account = $xeroObj->load(Account::class)->execute();
            $account =  $this->parserObjToArr($account);
            return ['status'=>true,'data'=>$account];
        } catch ( NotFoundException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        }catch (Exception $exception){
            return ['status'=>false,'msg'=>$exception->getMessage()];
        }
	}

	public function get_brandingthemes(){

        $xeroObj = $this->xeroAuth();
        if(!$xeroObj){
            return ['status'=>false,'msg'=>'Something went wrong in config details.'];
        }
        try{
            $brandingTheme = $xeroObj->load(BrandingTheme::class)->execute();
            $brandingTheme =  $this->parserObjToArr($brandingTheme);
            return ['status'=>true,'data'=>$brandingTheme];
        } catch ( NotFoundException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        }catch (Exception $exception){
            return ['status'=>false,'msg'=>$exception->getMessage()];
        }
	}

	public function get_currency_code(){
        $xeroObj = $this->xeroAuth();
        if(!$xeroObj){
            return ['status'=>false,'msg'=>'Something went wrong in config details.'];
        }
        try{
            $currency = $xeroObj->load(Currency::class)->execute();
            $currency =  $this->parserObjToArr($currency);
            return ['status'=>true,'data'=>$currency];
        } catch ( NotFoundException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        }catch (Exception $exception){
            return ['status'=>false,'msg'=>$exception->getMessage()];
        }
    }




    protected function parserObjToArr($data=[])
    {
       return (!empty($data) && ( is_object($data) || is_array($data))) ? json_decode(json_encode($data),True):[];
    }


}
