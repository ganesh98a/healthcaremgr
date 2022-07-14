<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'third_party/Xero/vendor/autoload.php';
require_once APPPATH.'libraries/XeroCommonAtuh.php';
use XeroPHP\Models\Accounting\Contact;
use XeroPHP\Remote\Query;
use XeroPHP\Remote\Exception\BadRequestException;
use XeroPHP\Remote\Exception\NotFoundException;
use XeroPHP\Remote\Exception\RateLimitExceededException;
use XeroPHP\Remote\Exception\NotAvailableException;
use XeroPHP\Remote\Exception\UnauthorizedException;
use XeroPHP\Remote\Exception\InternalErrorException;
use XeroPHP\Remote\Exception\NotImplementedException;
use XeroPHP\Remote\Exception\OrganisationOfflineException;
use XeroCommonAtuh\XeroCommonAtuh;
class XeroContact extends XeroCommonAtuh {
    protected $createContactObjectKeys = [
        'Addresses'=>'addAddress',
        'Phones'=>'addPhone',
        'SalesTrackingCategories'=>'addSalesTrackingCategory',
        'PurchasesTrackingCategories'=>'addPurchasesTrackingCategory',
        'PaymentTerms'=>'addPaymentTerm',
        'ContactGroups'=>'addContactGroup',
        'ContactPersons'=>'addContactPerson'
    ];
    protected $createContactObjectOrStringExceptKey = [
        'IncludeInEmails' => 'IncludeInEmail',
        'BankAccountDetails' => 'BankAccountDetail',
        'TrackingCategoryOption' => 'TrackingOptionName'
    ];
     public function __construct($config) {
        // Call the XeroCommonAtuh constructor
        parent::__construct();
        if(isset($config['company_id'])){

            $this->setCompanyId($config['company_id']);
        }else{
            exit('company_id field is required.');
        }

    }

    public function get_contact_status_list(){
        $status = [
            \XeroPHP\Models\Accounting\Contact::CONTACT_STATUS_ACTIVE,
            \XeroPHP\Models\Accounting\Contact::CONTACT_STATUS_ARCHIVED
        ];
        return $status;
    }

    public function get_contact_list($companyId,$extra_param=[]){
        $page=isset($extra_param['page']) && $extra_param['page']>0 ? $extra_param['page']: 1;
        $modifiedAfter=isset($extra_param['modifiedAfter']) && !empty($extra_param['modifiedAfter']) ? $extra_param['modifiedAfter']: '';
        $orderBy=isset($extra_param['order_by']['field_name']) ? $extra_param['order_by']['field_name'] :'ContactID';
        $orderByArrow=isset($extra_param['order_by']['direction']) ? $extra_param['order_by']['direction'] :'DESC';
        $xeroObj = $this->xeroAuth();
        if(!$xeroObj){
            return ['status'=>false,'msg'=>'Something went wrong in config details.'];
        }
        try{
            $contacts = $xeroObj->load(Contact::class);
            $queryWhere = $xeroObj->load(Query::class);
            $properties = \XeroPHP\Models\Accounting\Contact::getProperties();
            $propertiesString = array_filter($properties,function($v,$k){
                if(isset($v[1]) && in_array($v[1],['string','enum','bool','timestamp','float','bool'])){
                    return true;
                }else{
                    return false;
                }
            },ARRAY_FILTER_USE_BOTH);
            $propertiesObjectArray = array_diff_key($properties,$propertiesString);
        #pr($propertiesString);
            foreach($extra_param as $k => $val){
                //$queryWhere = $queryWhere->from('Accounting\Contact');
                if(in_array($k,array_keys($propertiesString))){
                    if($k=='ContactID'){
                        $val='ContactID==GUID("'.$val.'")';
                        $queryWhere = $queryWhere->where($val);

                    }else{
                        if(is_array($val)){
                            if(isset($val['type']) && isset($val['value']) && in_array($val['type'],['Contains','StartsWith','EndsWith']) && !empty($val['value'])){
                                $query = $k.'!= null';
                                $queryWhere = $queryWhere->where($query);
                                $query = $k.'.'.$val['type'].'("'.$val['value'].'")';
                                $queryWhere = $queryWhere->where($query);
                            }

                        }else{
                            $queryWhere = $queryWhere->where($k,$val);
                        }

                    }
                }

            }

            if($queryWhere){
               $contacts = $contacts->where($queryWhere->getWhere());
            }

            if(!empty($modifiedAfter)){
                $contacts = $contacts->modifiedAfter($modifiedAfter);
            }
            if($page){
                $contacts = $contacts->page($page);
            }

           if(!empty($orderBy) && !empty($orderByArrow)){
                $contacts = $contacts->orderBy($orderBy,$orderByArrow);
            }

            $contacts = $contacts->execute();

            $contacts =  $this->parserObjToArr($contacts);
            return ['status'=>true,'data'=>$contacts,'currentPage'=>$page,'nextPage'=>count($contacts)==100 ? $page+1:$page];
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

    public function create_contact($companyId,$parms=[]){
        $xeroObj = $this->xeroAuth();
        if(!$xeroObj){
            return ['status'=>false,'msg'=>'Something went wrong in config details.'];
        }

        try{
            $contact= new \XeroPHP\Models\Accounting\Contact($xeroObj);
            $properties = \XeroPHP\Models\Accounting\Contact::getProperties();
            if(!empty($parms) && is_array($parms)){
                foreach($parms as $key => $val) {
                    if(isset($properties[$key])){
                        $objectType = isset($properties[$key][1]) ? $properties[$key][1]:'string';
                        if($objectType=='object' && isset($properties[$key][3]) && $properties[$key][3]){
                            if(!empty($val) && is_array($val)){

                                foreach($val as $rows){
                                    $tempClass = '\XeroPHP\Models\\'.$properties[$key][2];
                                    $tempClass = new $tempClass();
                                    if(!empty($rows) && is_array($rows)){
                                        foreach($rows as $k => $row){
                                            $keyData = isset($this->createContactObjectOrStringExceptKey[$k]) ? $this->createContactObjectOrStringExceptKey[$k] : $k;
                                            $tempClass = call_user_func_array([$tempClass,'set'.$keyData],[$row]);
                                        }
                                        $contact= call_user_func_array([$contact,$this->createContactObjectKeys[$key]],[$tempClass]);
                                    }
                                }
                            }
                        }else{
                            $keyData = isset($this->createContactObjectOrStringExceptKey[$key]) ? $this->createContactObjectOrStringExceptKey[$key] : $key;
                            $contact= call_user_func_array([$contact,'set'.$keyData],[$val]);
                        }
                    }
                }
            }
        $contactNew= $contact->save();
           if($contactNew->getStatus()=='200'){
               return ['status'=>true,'data'=>$contactNew->getElements(),'warnings' =>$contactNew->getElementWarnings()];
           }else{
            return ['status'=>false,'data'=>$contactNew->getElementErrors(),'msg'=>'Something went wrong in request.'];
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
