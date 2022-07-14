<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'third_party/Xero/vendor/autoload.php';
require_once APPPATH.'libraries/XeroCommonAtuh.php';
use XeroPHP\Models\Accounting\Payment;
use XeroPHP\Models\Accounting\Contact;
use XeroPHP\Remote\Query;
use XeroPHP\Remote\Model;
use XeroPHP\Remote\Exception\BadRequestException;
use XeroPHP\Remote\Exception\NotFoundException;
use XeroPHP\Remote\Exception\RateLimitExceededException;
use XeroPHP\Remote\Exception\NotAvailableException;
use XeroPHP\Remote\Exception\UnauthorizedException;
use XeroPHP\Remote\Exception\InternalErrorException;
use XeroPHP\Remote\Exception\NotImplementedException;
use XeroPHP\Remote\Exception\OrganisationOfflineException;
use XeroCommonAtuh\XeroCommonAtuh;
class XeroPayment extends XeroCommonAtuh {

    protected $createContactObjectKeys = [
        'Invoice'=>'setInvoice',
        'CreditNote'=>'setCreditNote',
        'Prepayment '=>'setPrepayment ',
        'Overpayment'=>'setOverpayment',
        'Account'=>'setAccount'
    ];
    protected $createContactObjectOrStringExceptKey = [];

    function __construct($config) {
       // Call the XeroCommonAtuh constructor
        parent::__construct();

        if(isset($config['company_id'])){

            $this->setCompanyId($config['company_id']);
        }else{
            exit('company_id field is required.');
        }
    }

     function get_payment_status_list(){
        $status = [
            \XeroPHP\Models\Accounting\Payment::PAYMENT_STATUS_AUTHORISED,
            \XeroPHP\Models\Accounting\Payment::PAYMENT_STATUS_DELETED
        ];
        return $status;
    }

     function get_payment_type_list(){
        $status = [
            \XeroPHP\Models\Accounting\Payment::PAYMENT_TYPE_ACCRECPAYMENT,
            \XeroPHP\Models\Accounting\Payment::PAYMENT_TYPE_ACCPAYPAYMENT,
            \XeroPHP\Models\Accounting\Payment::PAYMENT_TYPE_ARCREDITPAYMENT,
            \XeroPHP\Models\Accounting\Payment::PAYMENT_TYPE_APCREDITPAYMENT,
            \XeroPHP\Models\Accounting\Payment::PAYMENT_TYPE_AROVERPAYMENTPAYMENT,
            \XeroPHP\Models\Accounting\Payment::PAYMENT_TYPE_ARPREPAYMENTPAYMENT,
            \XeroPHP\Models\Accounting\Payment::PAYMENT_TYPE_APPREPAYMENTPAYMENT,
            \XeroPHP\Models\Accounting\Payment::PAYMENT_TYPE_APOVERPAYMENTPAYMENT
        ];
        return $status;
    }

     function get_payment_list($extra_param=[]){

        //$page=isset($extra_param['page']) && $extra_param['page']>0 ? $extra_param['page']: 1;
        $modifiedAfter=isset($extra_param['modifiedAfter']) && !empty($extra_param['modifiedAfter']) ? $extra_param['modifiedAfter']: '';
        $orderBy=isset($extra_param['order_by']['field_name']) ? $extra_param['order_by']['field_name'] :'ContactID';
        $orderByArrow=isset($extra_param['order_by']['direction']) ? $extra_param['order_by']['direction'] :'DESC';
        $xeroObj = $this->xeroAuth();
        if(!$xeroObj){
            return ['status'=>false,'msg'=>'Something went wrong in config details.'];
        }
        try{
            $payments = $xeroObj->load(Payment::class);
            $queryWhere = $xeroObj->load(Query::class);
            $properties = \XeroPHP\Models\Accounting\Payment::getProperties();
            $propertiesString = array_filter($properties,function($v,$k){
                if(isset($v[1]) && in_array($v[1],['string','enum','bool','timestamp','float','bool','date'])){
                    return true;
                }else{
                    return false;
                }
            },ARRAY_FILTER_USE_BOTH);
            $propertiesObjectArray = array_diff_key($properties,$propertiesString);
    //pr($properties);
            foreach($extra_param as $k => $val){
                //$queryWhere = $queryWhere->from('Accounting\Contact');
                if(in_array($k,array_keys($propertiesString))){
                    if($k=='PaymentID'){
                        $val='PaymentID==GUID("'.$val.'")';
                        $queryWhere = $queryWhere->where($val);

                    }else if (isset($properties[$k][2]) && $properties[$k][2]=='\DateTimeInterface' && $k=='Date'){
                        $dateData = !empty($val) ? date('Y,m,d',strtotime($val)):'';
                        $queryWhere = !empty($dateData) ? $queryWhere->where("Date=DateTime(".$dateData.")"): $queryWhere;

                    }else{
                        if(is_array($val)){
                            if(isset($val['type']) && isset($val['value']) && in_array($val['type'],['Contains','StartsWith','EndsWith']) && !empty($val['value'])){
                                $query = $k.'!= null';
                                $queryWhere = $queryWhere->where($query);
                                $valueData = XeroPHP\Remote\Model::castFromString($properties[$k][1],$val['value'],2);
                                $query = $k.'.'.$val['type'].'("'.$valueData.'")';
                                $queryWhere = $queryWhere->where($query);
                            }

                        }elseif($properties[$k][1]=='float'){
                            $query=$k.'='.$val;
                            $queryWhere = $queryWhere->where($query);
                            //pr($queryWhere );
                        }else{
                            $valueData = is_string($val) ? XeroPHP\Remote\Model::castFromString($properties[$k][1],$val,2):$val;
                            $queryWhere = $queryWhere->where($k,$valueData);
                        }

                    }
                }
                if(in_array($k,array_keys($propertiesObjectArray))){
                    foreach($val as $key => $row){
                        $keyData = $k.'.'.$key;
                        if(!is_array($row)){
                            $query = $keyData.'=="'.$row.'"';
                            if (preg_match('/^([a-z]+)\.\1ID$/i', $keyData)
                                && preg_match(
                                    '/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
                                    $row
                                )
                            ) {
                                $query = sprintf('%s=Guid("%s")', $keyData, $row);
                            }

                            $queryWhere = $queryWhere->where($query);
                        }else if(is_array($row) && isset($row['type']) && isset($row['value']) && in_array($row['type'],['Contains','StartsWith','EndsWith']) && !empty($row['value'])){

                            $query = $keyData.'!= null';
                            $queryWhere = $queryWhere->where($query);
                            $valueData = XeroPHP\Remote\Model::castFromString(gettype($row['value']),$row['value'],2);
                            $query = $keyData.'.'.$row['type'].'("'.$valueData.'")';
                            $queryWhere = $queryWhere->where($query);

                        }else if(is_array($row)){
                            foreach($row as $keyR => $rowV){
                                $keyDataR = $keyData.'.'.$keyR;
                                $keyDataCheckGuid = $key.'.'.$keyR;
                                if(!is_array($rowV)){
                                    $query = $keyDataR.'=="'.$rowV.'"';
                                    if (preg_match('/^([a-z]+)\.\1ID$/i', $keyDataCheckGuid)
                                            && preg_match(
                                        '/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
                                        $rowV
                                        )
                                    ) {
                                    $query = sprintf('%s=Guid("%s")', $keyDataR, $rowV);
                                    }
                                    //echo $query; die;
                                    $queryWhere = $queryWhere->where($query);
                                }else if(isset($rowV['type']) && isset($rowV['value']) && in_array($rowV['type'],['Contains','StartsWith','EndsWith']) && !empty($rowV['value'])){
                                    $query = $keyDataR.'!= null';
                                    $queryWhere = $queryWhere->where($query);
                                    $valueData = XeroPHP\Remote\Model::castFromString(gettype($rowV['value']),$rowV['value'],2);
                                    $query = $keyDataR.'.'.$rowV['type'].'("'.$valueData.'")';
                                    $queryWhere = $queryWhere->where($query);
                                }

                            }

                        }

                    }

                }


            }


            if($queryWhere){
               $payments = $payments->where($queryWhere->getWhere());
               //pr([$queryWhere->getWhere()]);
            }

            if(!empty($modifiedAfter)){
                $payments = $payments->modifiedAfter($modifiedAfter);
            }
            /* if($page){
                $payments = $payments->page($page);
            } */

           if(!empty($orderBy) && !empty($orderByArrow)){
                $payments = $payments->orderBy($orderBy,$orderByArrow);
            }

            $payments = $payments->execute();

            $payments =  $this->parserObjToArr($payments);
            return ['status'=>true,'data'=>$payments];
        } catch ( NotFoundException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( BadRequestException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( RateLimitExceededException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        }   catch ( NotAvailableException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( UnauthorizedException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( InternalErrorException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( NotImplementedException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( OrganisationOfflineException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( Exception $exception){
            return ['status'=>false,'msg'=>$exception->getMessage()];
        }
    }

     function create_payment($parms=[]){
        $xeroObj = $this->xeroAuth();
        if(!$xeroObj){
            return ['status'=>false,'msg'=>'Something went wrong in config details.'];
        }

        try{
            $payment= new \XeroPHP\Models\Accounting\Payment($xeroObj);
            $properties = \XeroPHP\Models\Accounting\Payment::getProperties();
         #pr($properties);
            if(!empty($parms) && is_array($parms)){
                foreach($parms as $key => $val) {
                    if(isset($properties[$key])){
                        $objectType = isset($properties[$key][1]) ? $properties[$key][1]:'string';
                        if($objectType=='object' && $properties[$key][2]!='\DateTimeInterface'){
                            if(!empty($val) && is_array($val)){

                                foreach($val as $keyT=>$rows){

                                    $tempClass = '\XeroPHP\Models\\'.$properties[$key][2];
                                    $tempClass = new $tempClass();
                                    if(!empty($rows) && is_array($rows)){
                                        foreach($rows as $k => $row){
                                            $keyData = isset($this->createContactObjectOrStringExceptKey[$k]) ? $this->createContactObjectOrStringExceptKey[$k] : $k;
                                            $tempClass = call_user_func_array([$tempClass,'set'.$keyData],[$row]);
                                        }
                                        $payment= call_user_func_array([$payment,$this->createContactObjectKeys[$key]],[$tempClass]);
                                    }else{
                                        $keyData = isset($this->createContactObjectOrStringExceptKey[$keyT]) ? $this->createContactObjectOrStringExceptKey[$keyT] : $keyT;
                                        $tempClass = call_user_func_array([$tempClass,'set'.$keyData],[$rows]);
                                        $payment= call_user_func_array([$payment,$this->createContactObjectKeys[$key]],[$tempClass]);
                                    }
                                }
                            }
                        }else{
                            $keyData = isset($this->createContactObjectOrStringExceptKey[$key]) ? $this->createContactObjectOrStringExceptKey[$key] : $key;
                            $valData =is_string($val) ? XeroPHP\Remote\Model::castFromString($properties[$key][1],$val,2):$val;
                            $payment= call_user_func_array([$payment,'set'.$keyData],[$valData]);
                        }
                    }
                }
            }
           // pr($payment);
        $paymentNew= $payment->save();
           if($paymentNew->getStatus()=='200'){
               return ['status'=>true,'data'=>$paymentNew->getElements(),'warnings' =>$paymentNew->getElementWarnings()];
           }else{
            return ['status'=>false,'data'=>$paymentNew->getElementErrors(),'msg'=>'Something went wrong in request.'];
           }

        } catch ( NotFoundException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( BadRequestException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( RateLimitExceededException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        }   catch ( NotAvailableException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( UnauthorizedException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( InternalErrorException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( NotImplementedException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( OrganisationOfflineException $exception) {
            return ['status'=>false,'msg'=>$exception->getMessage()];
        } catch ( Exception $exception){
            return ['status'=>false,'msg'=>$exception->getMessage()];
        }

    }


}
