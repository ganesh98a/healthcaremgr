<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'third_party/Xero/vendor/autoload.php';
require_once APPPATH . 'libraries/XeroCommonAtuh.php';

use XeroPHP\Models\Accounting\Contact;
use XeroPHP\Models\Accounting\Invoice;
use XeroPHP\Models\Accounting\Payment;
use XeroPHP\Models\Accounting\Account;
use XeroPHP\Models\Accounting\Report\ProfitLoss;
use XeroPHP\Models\Accounting\Report\Report;
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

class XeroInvoice extends XeroCommonAtuh
{
    function __construct($config)
    {
        // Call the XeroCommonAtuh constructor
        parent::__construct();

        if (isset($config['company_id'])) {

            $this->setCompanyId($config['company_id']);
        } else {
            exit('company_id field is required.');
        }
    }

    public function get_invoice_status_list()
    {
        $status = [
            \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_DRAFT,
            \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_SUBMITTED,
            \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_DELETED,
            \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_AUTHORISED,
            \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_PAID,
            \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_VOIDED
        ];
        return $status;
    }

    public function get_invoice_types_list()
    {
        $type = [
            \XeroPHP\Models\Accounting\Invoice::INVOICE_TYPE_ACCPAY,
            \XeroPHP\Models\Accounting\Invoice::INVOICE_TYPE_ACCREC
        ];
        return $type;
    }
    public function get_invoice_lineamount_list()
    {
        $type = [
            \XeroPHP\Models\Accounting\Invoice::LINEAMOUNT_TYPE_EXCLUSIVE,
            \XeroPHP\Models\Accounting\Invoice::LINEAMOUNT_TYPE_INCLUSIVE,
            \XeroPHP\Models\Accounting\Invoice::LINEAMOUNT_TYPE_NOTAX
        ];
        return $type;
    }

    public function get_invoice_list($extra_param = [])
    {
        $modifiedAfter = isset($extra_param['modifiedAfter']) && !empty($extra_param['modifiedAfter']) ? $extra_param['modifiedAfter'] : '';
        $page = isset($extra_param['page']) ? $extra_param['page'] : 1;
        $orderBy = isset($extra_param['order_by']['field_name']) ? $extra_param['order_by']['field_name'] : 'InvoiceID';
        $orderByArrow = isset($extra_param['order_by']['direction']) ? $extra_param['order_by']['direction'] : 'DESC';
        $xeroObj = $this->xeroAuth();
        if (!$xeroObj) {
            return ['status' => false, 'msg' => 'Something went wrong in config details.'];
        }
        try {
            $invoices = $xeroObj->load(Invoice::class);
            $queryWhere = $xeroObj->load(Query::class);
            $properties = \XeroPHP\Models\Accounting\Invoice::getProperties();
            $propertiesString = array_filter($properties, function ($v, $k) {
                if (isset($v[1]) && in_array($v[1], ['string', 'enum', 'bool', 'timestamp', 'float', 'bool', 'date'])) {
                    return true;
                } else {
                    return false;
                }
            }, ARRAY_FILTER_USE_BOTH);
            $propertiesObjectArray = array_diff_key($properties, $propertiesString);
         //   pr($properties);
            foreach ($extra_param as $k => $val) {
                //$queryWhere = $queryWhere->from('Accounting\Contact');
                if (in_array($k, array_keys($propertiesString))) {
                    if ($k == 'InvoiceID') {
                        $val = 'InvoiceID==GUID("' . $val . '")';
                        $queryWhere = $queryWhere->where($val);
                    } else if (isset($properties[$k][2]) && $properties[$k][2] == '\DateTimeInterface' && in_array($k,['Date','DueDate'])) {
                        if (is_array($val) && !isset($val['type'])) {
                            foreach ($val as $kr=>$row){
                                $queryWhere =$this->dateDataWhere($row,$queryWhere,$k);
                            }

                        }else{
                            $queryWhere= $this->dateDataWhere($val,$queryWhere,$k);
                        }
                    }else {
                        $valueData = is_string($val) ? XeroPHP\Remote\Model::castFromString($properties[$k][1], $val, 2) : $val;
                        $queryWhere = $queryWhere->where($k, $valueData);
                    }
                }
                if (in_array($k, array_keys($propertiesObjectArray))) {
                    foreach ($val as $key => $row) {
                        $keyData = $k . '.' . $key;
                        if (!is_array($row)) {
                            $query = $keyData . '=="' . $row . '"';
                            if (
                                preg_match('/^([a-z]+)\.\1ID$/i', $keyData)
                                && preg_match(
                                    '/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
                                    $row
                                )
                            ) {
                                $query = sprintf('%s=Guid("%s")', $keyData, $row);
                            }

                            $queryWhere = $queryWhere->where($query);
                        } else if (is_array($row) && isset($row['type']) && isset($row['value']) && in_array($row['type'], ['Contains', 'StartsWith', 'EndsWith']) && !empty($row['value'])) {

                            $query = $keyData . '!= null';
                            $queryWhere = $queryWhere->where($query);
                            $valueData = XeroPHP\Remote\Model::castFromString(gettype($row['value']), $row['value'], 2);
                            $query = $keyData . '.' . $row['type'] . '("' . $valueData . '")';
                            $queryWhere = $queryWhere->where($query);
                        } else if (is_array($row)) {
                            foreach ($row as $keyR => $rowV) {
                                $keyDataR = $keyData . '.' . $keyR;
                                $keyDataCheckGuid = $key . '.' . $keyR;
                                if (!is_array($rowV)) {
                                    $query = $keyDataR . '=="' . $rowV . '"';
                                    if (
                                        preg_match('/^([a-z]+)\.\1ID$/i', $keyDataCheckGuid)
                                        && preg_match(
                                            '/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
                                            $rowV
                                        )
                                    ) {
                                        $query = sprintf('%s=Guid("%s")', $keyDataR, $rowV);
                                    }
                                    //echo $query; die;
                                    $queryWhere = $queryWhere->where($query);
                                } else if (isset($rowV['type']) && isset($rowV['value']) && in_array($rowV['type'], ['Contains', 'StartsWith', 'EndsWith']) && !empty($rowV['value'])) {
                                    $query = $keyDataR . '!= null';
                                    $queryWhere = $queryWhere->where($query);
                                    $valueData = XeroPHP\Remote\Model::castFromString(gettype($rowV['value']), $rowV['value'], 2);
                                    $query = $keyDataR . '.' . $rowV['type'] . '("' . $valueData . '")';
                                    $queryWhere = $queryWhere->where($query);
                                }
                            }
                        }
                    }
                }
            }

            if ($queryWhere) {
                $invoices = $invoices->where($queryWhere->getWhere());
            }

            if (!empty($modifiedAfter)) {
                $invoices = $invoices->modifiedAfter($modifiedAfter);
            }

            if ($page) {
                $invoices = $invoices->page($page);
            }

            if (!empty($orderBy) && !empty($orderByArrow)) {
                $invoices = $invoices->orderBy($orderBy, $orderByArrow);
            }

            $invoices = $invoices->execute();

            $invoices =  $this->parserObjToArr($invoices);
            return ['status' => true, 'data' => $invoices, 'currentPage' => $page, 'nextPage' => count($invoices) >= 100 ? $page + 1 : $page];
        } catch (NotFoundException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (Exception $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        }
    }
    public function get_invoice_list_old($companyId, $extra_param = [])
    {
        $where = '';
        $status = isset($extra_param['Status']) ? strtoupper($extra_param['Status']) : '';
        $type = isset($extra_param['Type']) ? strtoupper($extra_param['Type']) : '';
        $invoiceNumber = isset($extra_param['InvoiceNumber']) ? $extra_param['InvoiceNumber'] : '';
        $invoiceID = isset($extra_param['InvoiceID']) ? $extra_param['InvoiceID'] : '';
        $contactContactID = isset($extra_param['Contact_ContactID']) ? $extra_param['Contact_ContactID'] : '';
        $contactName = isset($extra_param['Contact_Name']) ? $extra_param['Contact_Name'] : '';
        $contactContactNumber = isset($extra_param['Contact_ContactNumber']) ? $extra_param['Contact_ContactNumber'] : '';
        $reference = isset($extra_param['Reference']) ? $extra_param['Reference'] : '';
        $endDateString = isset($extra_param['endDate']) ? $extra_param['endDate'] : '';
        $startDateString = isset($extra_param['startDate']) ? $extra_param['startDate'] : '';
        $equalDateString = isset($extra_param['equalDate']) ? $extra_param['equalDate'] : '';
        $page = isset($extra_param['page']) ? $extra_param['page'] : 1;
        $orderBy = isset($extra_param['order_by']['field_name']) ? $extra_param['order_by']['field_name'] : 'InvoiceID';
        $orderByArrow = isset($extra_param['order_by']['direction']) ? $extra_param['order_by']['direction'] : 'DESC';
        $v = 0;
        if (!empty($endDateString)) {
            $endDateString = date('Y,m,d', strtotime($endDateString));
        }
        if (!empty($startDateString)) {
            $startDateString = date('Y,m,d', strtotime($startDateString));
        }

        if (!empty($equalDateString)) {
            $equalDateString = date('Y,m,d', strtotime($equalDateString));
        }
        if (!empty($status)) {
            $satausList = $this->get_invoice_status_list();
            if (in_array($status, $satausList)) {
                $where .= $this->addAnd($v);
                $where .= 'Status=="' . $status . '"';
                $v++;
            } else {
                return ['status' => false, 'msg' => 'filter status option does not exists.'];
            }
        }
        if (!empty($invoiceNumber)) {
            $where .= $this->addAnd($v);
            $where .= 'InvoiceNumber=="' . $invoiceNumber . '"';
            $v++;
        }

        if (!empty($contactContactID)) {
            $where .= $this->addAnd($v);
            $where .= 'Contact.ContactID==GUID("' . $contactContactID . '")';
            $v++;
        }
        if (!empty($contactName)) {

            if (is_string($contactName)) {
                $where .= $this->addAnd($v);
                $where .= 'Contact.Name=="' . $contactName . '"';
                $v++;
            } else if (isset($contactName['type']) && isset($contactName['value'])) {
                $where .= $this->addAnd($v);
                $where .= 'Contact.Name.' . $contactName['type'] . '("' . $contactName['value'] . '")';
                $v++;
            }
        }
        if (!empty($contactContactNumber)) {
            $where .= $this->addAnd($v);
            $where .= 'Contact.ContactNumber=="' . $contactContactNumber . '"';
            $v++;
        }

        if (!empty($reference)) {
            $where .= $this->addAnd($v);
            $where .= 'Reference=="' . $reference . '"';
            $v++;
        }

        if (!empty($invoiceID)) {
            $where .= $this->addAnd($v);
            $where .= 'InvoiceID==Guid("' . $invoiceID . '")';
            $v++;
        }
        if (!empty($endDateString) && !empty($startDateString)) {
            $where .= $this->addAnd($v);
            $where .= "Date >= DateTime(" . $startDateString . ") && Date < DateTime(" . $endDateString . ")";
            $v++;
        } else if (!empty($startDateString)) {
            $where .= $this->addAnd($v);
            $where .= "Date >= DateTime(" . $startDateString . ")";
            $v++;
        } else if (!empty($endDateString)) {
            $where .= $this->addAnd($v);
            $where .= "Date <= DateTime(" . $endDateString . ")";
            $v++;
        } else if (!empty($equalDateString)) {
            $where .= $this->addAnd($v);
            $where .= "Date >= DateTime(" . $equalDateString . ")";
            $v++;
        }

        if (!empty($type)) {
            $typeList = $this->get_invoice_types_list();
            if (in_array($type, $typeList)) {
                $where .= $this->addAnd($v);
                $where .= 'Type=="' . $type . '"';
                $v++;
            } else {
                return ['status' => false, 'msg' => 'filter type option does not exists.'];
            }
        }
        $xeroObj = $this->xeroAuth();
        if (!$xeroObj) {
            return ['status' => false, 'msg' => 'Something went wrong in config details.'];
        }
        try {
            $invoices = $xeroObj->load(Invoice::class);
            if ($v > 0) {
                $invoices = $invoices->where($where);
            }

            if ($page) {
                $invoices = $invoices->page($page);
            }

            if (!empty($orderBy) && !empty($orderByArrow)) {
                $invoices = $invoices->orderBy($orderBy, $orderByArrow);
            }

            $invoices = $invoices->execute();

            $invoices =  $this->parserObjToArr($invoices);
            return ['status' => true, 'data' => $invoices, 'currentPage' => $page, 'nextPage' => count($invoices) == 100 ? $page + 1 : $page];
        } catch (NotFoundException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (Exception $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        }
    }

    private function dateDataWhere($val,$queryWhere,$k){
        if(isset($val['type']) && isset($val['value']) && in_array($val['type'], ['fromDate', 'toDate']) && !empty($val['value'])){
            $dateData = !empty($val['value']) ? date('Y,m,d', strtotime($val['value'])) : '';
            if($val['type']=='fromDate'){
                $queryWhere = !empty($dateData) ? $queryWhere->where($k.">=DateTime(" . $dateData . ")") : $queryWhere;
            }
             if($val['type']=='toDate'){
                $queryWhere = !empty($dateData) ? $queryWhere->where($k."<=DateTime(" . $dateData . ")") : $queryWhere;
            }
        }else if(isset($val['type']) && isset($val['value']) && !empty($val['value'])){
            $dateData = !empty($val['value']) ? date('Y,m,d', strtotime($val['value'])) : '';
            $queryWhere = !empty($dateData) ? $queryWhere->where($k."=DateTime(" . $dateData . ")") : $queryWhere;
        }else{
            $dateData = !empty($val) ? date('Y,m,d', strtotime($val)) : '';
            $queryWhere = !empty($dateData) ? $queryWhere->where($k."=DateTime(" . $dateData . ")") : $queryWhere;
        }
        return $queryWhere;
    }
    public function create_inovice($companyId, $extraParam = [])
    {

        $type = isset($extraParam['Type']) ? $extraParam['Type'] : '';
        $type = !empty($type) && is_string($type) ? strtoupper($type) : $type;
        $getTypeData = $this->get_invoice_types_list();
        $contactId = isset($extraParam['ContactID']) ? $extraParam['ContactID'] : '';
        $lineItems = isset($extraParam['LineItems']) ? $extraParam['LineItems'] : '';
        $dateInvoice = isset($extraParam['Date']) ? $extraParam['Date'] : '';
        $dueInvoice = isset($extraParam['DueDate']) ? $extraParam['DueDate'] : '';
        $lineAmountTypes = isset($extraParam['LineAmountTypes']) ? $extraParam['LineAmountTypes'] : '';
        $reference = isset($extraParam['Reference']) ? $extraParam['Reference'] : '';
        $CurrencyCode = isset($extraParam['CurrencyCode']) ? $extraParam['CurrencyCode'] : '';
        $CurrencyRate = isset($extraParam['CurrencyRate']) ? $extraParam['CurrencyRate'] : '';
        $Status = isset($extraParam['Status']) ? $extraParam['Status'] : '';
        $SentToContact = isset($extraParam['SentToContact']) ? $extraParam['SentToContact'] : '';
        $ExpectedPaymentDate = isset($extraParam['ExpectedPaymentDate']) ? $extraParam['ExpectedPaymentDate'] : '';
        $PlannedPaymentDate = isset($extraParam['PlannedPaymentDate']) ? $extraParam['PlannedPaymentDate'] : '';
        $BrandingThemeID = isset($extraParam['BrandingThemeID']) ? $extraParam['BrandingThemeID'] : '';
        $Url = isset($extraParam['Url']) ? $extraParam['Url'] : '';



        if (empty($type) || (!empty($type) && !in_array($type, $getTypeData))) {
            return ['status' => false, 'msg' => 'Given type not match in invoice type list.'];
        }
        if (empty($contactId)) {
            return ['status' => false, 'msg' => 'Contact Guid is required.'];
        }
        if (empty($lineItems) || count($lineItems) <= 0 || !is_array($lineItems)) {
            return ['status' => false, 'msg' => 'atleast one LineItems is required.'];
        }
        $xeroObj = $this->xeroAuth();
        if (!$xeroObj) {
            return ['status' => false, 'msg' => 'Something went wrong in config details.'];
        }
        try {

            $invoice = new \XeroPHP\Models\Accounting\Invoice($xeroObj);
            $contactDta = $xeroObj->loadByGUID(Contact::class, $contactId);
            $invoice = $invoice->setContact($contactDta);
            foreach ($lineItems as $row) {
                $lineitem = $this->getLineItemForInvoice($xeroObj, $row);
                $invoice = $invoice->addLineItem($lineitem);
            }
            $invoice = $invoice->setType($type);
            if (!empty($dateInvoice)) {
                $invoice = $invoice->setDate(\DateTime::createFromFormat('Y-m-d', $dateInvoice));
            }

            if (!empty($dueInvoice)) {
                $invoice = $invoice->setDueDate(\DateTime::createFromFormat('Y-m-d', $dueInvoice));
            }

            if (!empty($lineAmountTypes)) {
                $invoice = $invoice->setLineAmountType($lineAmountTypes);
            }
            if (!empty($reference)) {
                $invoice = $invoice->setReference($reference);
            }
            if (!empty($CurrencyCode)) {
                $invoice = $invoice->setCurrencyCode($CurrencyCode);
            }
            if (!empty($CurrencyRate)) {
                $invoice = $invoice->setCurrencyRate($CurrencyRate);
            }
            if (!empty($Status)) {
                $invoice = $invoice->setStatus($Status);
            }
            if (!empty($ExpectedPaymentDate)) {
                $invoice = $invoice->setExpectedPaymentDate($ExpectedPaymentDate);
            }
            if (!empty($PlannedPaymentDate)) {
                $invoice = $invoice->setPlannedPaymentDate($PlannedPaymentDate);
            }
            if (!empty($BrandingThemeID)) {
                $invoice = $invoice->setBrandingThemeID($BrandingThemeID);
            }
            if (!empty($Url)) {
                $invoice = $invoice->setUrl($Url);
            }

            if ($SentToContact != '') {
                $invoice = $invoice->setSentToContact($SentToContact);
            }

            $invoiceNew = $invoice->save();
            if ($invoiceNew->getStatus() == '200') {
                return ['status' => true, 'data' => $invoiceNew->getElements(), 'warnings' => $invoiceNew->getElementWarnings()];
            } else {
                return ['status' => false, 'data' => $invoiceNew->getElementErrors(), 'msg' => 'Something went wrong in request.'];
            }
        } catch (NotFoundException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (Exception $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        }
    }

    public function getLineItemForInvoice($xeroObj, $row)
    {
        $lineitem = new \XeroPHP\Models\Accounting\Invoice\LineItem($xeroObj);
        $lineitem = $lineitem->setDescription($row['Description']);

        if (isset($row['Quantity'])) {
            $lineitem = $lineitem->setQuantity($row['Quantity']);
        }
        if (isset($row['LineItemID']) && !empty($row['LineItemID'])) {
            $lineitem = $lineitem->setLineItemID($row['LineItemID']);
        }

        if (isset($row['UnitAmount'])) {
            $lineitem = $lineitem->setUnitAmount($row['UnitAmount']);
        }

        if (isset($row['AccountCode'])) {
            $lineitem = $lineitem->setAccountCode($row['AccountCode']);
        }

        if (isset($row['ItemCode'])) {
            $lineitem = $lineitem->setItemCode($row['ItemCode']);
        }

        if (isset($row['DiscountRate'])) {
            $lineitem = $lineitem->setDiscountRate($row['DiscountRate']);
        }

        if (isset($row['TaxType'])) {
            $lineitem = $lineitem->setTaxType($row['TaxType']);
        }

        if (isset($row['TaxAmount'])) {
            $lineitem = $lineitem->setTaxAmount($row['TaxAmount']);
        }

        if (isset($row['LineAmount'])) {
            $lineitem = $lineitem->setLineAmount($row['LineAmount']);
        }
        if (isset($row['Tracking'])) {
            $tracking = new \XeroPHP\Models\Accounting\TrackingCategory($xeroObj);
            $tracking = $tracking->setName($row['Tracking']['Name']);
            $tracking = $tracking->setOption($row['Tracking']['Option']);
            $lineitem = $lineitem->addTracking($tracking);
        }

        return $lineitem;
    }

    public function update_inovice($companyId, $extraParam = [])
    {

        $invoiceID = isset($extraParam['InvoiceID']) ? $extraParam['InvoiceID'] : '';
        $type = isset($extraParam['Type']) ? $extraParam['Type'] : '';
        $type = !empty($type) && is_string($type) ? strtoupper($type) : $type;
        $getTypeData = $this->get_invoice_types_list();
        $contactId = isset($extraParam['ContactID']) ? $extraParam['ContactID'] : '';
        $lineItems = isset($extraParam['LineItems']) ? $extraParam['LineItems'] : '';
        $dateInvoice = isset($extraParam['Date']) ? $extraParam['Date'] : '';
        $dueInvoice = isset($extraParam['DueDate']) ? $extraParam['DueDate'] : '';
        $lineAmountTypes = isset($extraParam['LineAmountTypes']) ? $extraParam['LineAmountTypes'] : '';
        $reference = isset($extraParam['Reference']) ? $extraParam['Reference'] : '';
        $CurrencyCode = isset($extraParam['CurrencyCode']) ? $extraParam['CurrencyCode'] : '';
        $CurrencyRate = isset($extraParam['CurrencyRate']) ? $extraParam['CurrencyRate'] : '';
        $Status = isset($extraParam['Status']) ? $extraParam['Status'] : '';
        $SentToContact = isset($extraParam['SentToContact']) ? $extraParam['SentToContact'] : '';
        $ExpectedPaymentDate = isset($extraParam['ExpectedPaymentDate']) ? $extraParam['ExpectedPaymentDate'] : '';
        $PlannedPaymentDate = isset($extraParam['PlannedPaymentDate']) ? $extraParam['PlannedPaymentDate'] : '';
        $BrandingThemeID = isset($extraParam['BrandingThemeID']) ? $extraParam['BrandingThemeID'] : '';
        $Url = isset($extraParam['Url']) ? $extraParam['Url'] : '';

        if (empty($invoiceID)) {
            return ['status' => false, 'msg' => 'InvoiceID Guid is required.'];
        }

        if (empty($type) || (!empty($type) && !in_array($type, $getTypeData))) {
            return ['status' => false, 'msg' => 'Given type not match in invoice type list.'];
        }
        if (empty($contactId)) {
            return ['status' => false, 'msg' => 'Contact Guid is required.'];
        }
        /* if(empty($lineItems) || count($lineItems) <=0 || !is_array($lineItems)){
            return ['status'=>false,'msg'=>'atleast one LineItems is required.'];
        } */
        $xeroObj = $this->xeroAuth();
        if (!$xeroObj) {
            return ['status' => false, 'msg' => 'Something went wrong in config details.'];
        }
        try {
            $invoice = new \XeroPHP\Models\Accounting\Invoice($xeroObj);
            $contactDta = $xeroObj->loadByGUID(Contact::class, $contactId);
            $invoice = $invoice->setContact($contactDta);
            if (!empty($lineItems)) {
                foreach ($lineItems as $row) {
                    $lineitem = $this->getLineItemForInvoice($xeroObj, $row);
                    $invoice = $invoice->addLineItem($lineitem);
                }
            }

            $invoice = $invoice->setType($type);
            $invoice = $invoice->setInvoiceID($invoiceID);
            $currentInvoice = false;

            if (!empty($Status)) {
                $currentInvoice =  $xeroObj->loadByGUID(Invoice::class, $invoiceID);
            }
            if (!empty($Status)) {
                $checkedAllowStatus = [];
                if ($currentInvoice != false) {
                    $currentStatus = $currentInvoice->getStatus();
                    $checkedAllowStatus = $this->get_allow_change_inovice_status_current_to_modifiy($currentStatus);
                }
                if (!in_array($Status, $checkedAllowStatus)) {
                    return ['status' => false, 'msg' => 'given status not allowed to update.'];
                }
                $invoice = $invoice->setStatus($Status);
            }
            if (!empty($dateInvoice)) {
                $invoice = $invoice->setDate(\DateTime::createFromFormat('Y-m-d', $dateInvoice));
            }
            if (!empty($dueInvoice)) {
                $invoice = $invoice->setDueDate(\DateTime::createFromFormat('Y-m-d', $dueInvoice));
            }
            if (!empty($lineAmountTypes)) {
                $invoice = $invoice->setLineAmountType($lineAmountTypes);
            }
            if (!empty($reference)) {
                $invoice = $invoice->setReference($reference);
            }
            if (!empty($CurrencyCode)) {
                $invoice = $invoice->setCurrencyCode($CurrencyCode);
            }
            if (!empty($CurrencyRate)) {
                $invoice = $invoice->setCurrencyRate($CurrencyRate);
            }
            if (!empty($ExpectedPaymentDate)) {
                $invoice = $invoice->setExpectedPaymentDate($ExpectedPaymentDate);
            }
            if (!empty($PlannedPaymentDate)) {
                $invoice = $invoice->setPlannedPaymentDate($PlannedPaymentDate);
            }
            if (!empty($BrandingThemeID)) {
                $invoice = $invoice->setBrandingThemeID($BrandingThemeID);
            }
            if (!empty($Url)) {
                $invoice = $invoice->setUrl($Url);
            }
            if ($SentToContact != '') {
                $invoice = $invoice->setSentToContact($SentToContact);
            }

            $invoiceNew = $invoice->save();
            if ($invoiceNew->getStatus() == '200') {
                return ['status' => true, 'data' => $invoiceNew->getElements(), 'warnings' => $invoiceNew->getElementWarnings()];
            } else {
                return ['status' => false, 'data' => $invoiceNew->getElementErrors(), 'msg' => 'Something went wrong in request.'];
            }
        } catch (NotFoundException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (Exception $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        }
    }

    public function delete_inovice($companyId, $extraParam = [])
    {

        $invoiceID = isset($extraParam['InvoiceID']) ? $extraParam['InvoiceID'] : '';


        if (empty($invoiceID)) {
            return ['status' => false, 'msg' => 'InvoiceID Guid is required.'];
        }

        $xeroObj = $this->xeroAuth();
        if (!$xeroObj) {
            return ['status' => false, 'msg' => 'Something went wrong in config details.'];
        }
        try {
            $currentInvoice =  $xeroObj->loadByGUID(Invoice::class, $invoiceID);
            $currentStatus = $currentInvoice->getStatus();
            if (in_array($currentStatus, [\XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_DRAFT, \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_SUBMITTED])) {
                $invoice = new \XeroPHP\Models\Accounting\Invoice($xeroObj);
                $invoice = $invoice->setInvoiceID($invoiceID);
                $invoice = $invoice->setStatus(\XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_DELETED);
                $invoiceNew = $invoice->save();
                if ($invoiceNew->getStatus() == '200') {
                    return ['status' => true, 'data' => $invoiceNew->getElements(), 'warnings' => $invoiceNew->getElementWarnings()];
                } else {
                    return ['status' => false, 'data' => $invoiceNew->getElementErrors(), 'msg' => 'Something went wrong in request.'];
                }
            } else {
                return ['status' => false, 'msg' => 'You can delete a DRAFT or SUBMITTED invoice by updating the Status to DELETED. If an invoice has been AUTHORISED it cannot be deleted but you can set it\'s status to VOIDED'];
            }
        } catch (NotFoundException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (Exception $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        }
    }

    public function invoice_status_mark_as_voided($companyId, $extraParam = [])
    {
        $invoiceID = isset($extraParam['InvoiceID']) ? $extraParam['InvoiceID'] : '';
        if (empty($invoiceID)) {
            return ['status' => false, 'msg' => 'InvoiceID Guid is required.'];
        }
        $xeroObj = $this->xeroAuth();
        if (!$xeroObj) {
            return ['status' => false, 'msg' => 'Something went wrong in config details.'];
        }
        try {
            $currentInvoice =  $xeroObj->loadByGUID(Invoice::class, $invoiceID);
            $currentStatus = $currentInvoice->getStatus();
            if ($currentStatus == \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_AUTHORISED) {
                $invoice = new \XeroPHP\Models\Accounting\Invoice($xeroObj);
                $invoice = $invoice->setInvoiceID($invoiceID);
                $invoice = $invoice->setStatus(\XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_VOIDED);
                $invoiceNew = $invoice->save();
                if ($invoiceNew->getStatus() == '200') {
                    return ['status' => true, 'data' => $invoiceNew->getElements(), 'warnings' => $invoiceNew->getElementWarnings()];
                } else {
                    return ['status' => false, 'data' => $invoiceNew->getElementErrors(), 'msg' => 'Something went wrong in request.'];
                }
            } else {
                return ['status' => false, 'msg' => 'You can voided a AUTHORISED invoice by updating the Status to VOIDED.'];
            }
        } catch (NotFoundException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (Exception $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        }
    }
    protected function get_allow_change_inovice_status_current_to_modifiy($currentStatus)
    {
        $statusData = [
            \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_DRAFT => [
                \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_DRAFT,
                \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_SUBMITTED,
                \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_AUTHORISED,
                \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_DELETED,
            ],
            \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_SUBMITTED => [
                \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_DRAFT,
                \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_SUBMITTED,
                \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_AUTHORISED,
                \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_DELETED,
            ],
            \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_AUTHORISED => [
                \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_AUTHORISED,
                \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_VOIDED
            ]
        ];
        return isset($statusData[$currentStatus]) ? $statusData[$currentStatus] : [];
    }

    public function get_profit_loss(){
        $xeroObj = $this->xeroAuth();
        if (!$xeroObj) {
            return ['status' => false, 'msg' => 'Something went wrong in config details.'];
        }
        try {
            $profitLoss = $xeroObj->load(ProfitLoss::class);
            $queryWhere = $xeroObj->load(Query::class);
            $queryWhere->where('toDate',DATE_CURRENT);
            $queryWhere->where('fromDate',date('Y-m-01' ,strtotime(DATE_CURRENT)));
           

            if($queryWhere){
                $profitLoss = $profitLoss->where($queryWhere->getWhere());
            }
            $profitLoss = $profitLoss->execute();
            
            $rowsData['Rows'] = $this->parserObjToArr($profitLoss[0]['Rows']);
            $profitLoss =  $this->parserObjToArr($profitLoss);
            $profitLoss = array_merge($profitLoss[0],$rowsData);
            $r =serach_in_array($profitLoss,'RowType','Row');
            $cells = !empty($r) ? array_column($r,'Cells') :[];
            $profitLossDataDetails =!empty($cells)?  array_values(array_filter(array_column($r,'Cells'),function($row){
            $rk= serach_in_array($row,'Value','NET PROFIT');
            return !empty($rk) ? true : false;
            })) : [];
            $profitLossAmount = !empty($profitLossDataDetails) && isset($profitLossDataDetails[0][1]['Value']) ?  $profitLossDataDetails[0][1]['Value'] :0;
            return ['status'=>true,'data'=>$profitLossAmount];

        } catch (NotFoundException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (Exception $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        }
    }

    public function invoice_status_mark_as_Authorised($companyId, $extraParam = [])
    {
        $invoiceID = isset($extraParam['InvoiceID']) ? $extraParam['InvoiceID'] : '';
        if (empty($invoiceID)) {
            return ['status' => false, 'msg' => 'InvoiceID Guid is required.'];
        }
        $xeroObj = $this->xeroAuth();
        if (!$xeroObj) {
            return ['status' => false, 'msg' => 'Something went wrong in config details.'];
        }
        try {
            $currentInvoice =  $xeroObj->loadByGUID(Invoice::class, $invoiceID);
            $currentStatus = $currentInvoice->getStatus();
            $allowed = $this->get_allow_change_inovice_status_current_to_modifiy($currentStatus);
            if (!empty($allowed) && in_array(\XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_AUTHORISED,$allowed)) {
                $invoice = new \XeroPHP\Models\Accounting\Invoice($xeroObj);
                $invoice = $invoice->setInvoiceID($invoiceID);
                $invoice = $invoice->setStatus(\XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_AUTHORISED);
                $invoiceNew = $invoice->save();
                if ($invoiceNew->getStatus() == '200') {
                    return ['status' => true, 'data' => $invoiceNew->getElements(), 'warnings' => $invoiceNew->getElementWarnings()];
                } else {
                    return ['status' => false, 'data' => $invoiceNew->getElementErrors(), 'msg' => 'Something went wrong in request.'];
                }
            } else {
                return ['status' => false, 'msg' => 'You can not update current status to Authorised status'];
            }
        } catch (NotFoundException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (Exception $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        }
    }

    public function invoice_status_mark_as_Authorised_multiple($companyId, $extraParam = [])
    {
        $invoiceIDs=isset($extraParam['InvoiceIDs']) ? $extraParam['InvoiceIDs'] : '';
        if (empty($invoiceIDs)) {
            return ['status' => false, 'msg' => 'InvoiceID Guid is required.'];
        }
        $xeroObj = $this->xeroAuth();
        if (!$xeroObj) {
            return ['status' => false, 'msg' => 'Something went wrong in config details.'];
        }
        try {
            $sendInvoice=[]; 
            $invoicePos=[]; 
            $errorsFinal=[];
            $currentInvoices =  $xeroObj->loadByGUIDs(Invoice::class, implode(',', $invoiceIDs));
            //print_r($currentInvoices);
            foreach($currentInvoices as $currentInvoice){
                $currentStatus = $currentInvoice->getStatus();
                $invoiceID = $currentInvoice->getInvoiceID();
                $allowed = $this->get_allow_change_inovice_status_current_to_modifiy($currentStatus);
                if (!empty($allowed) && in_array(\XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_AUTHORISED,$allowed)) {
                    $invoice = new \XeroPHP\Models\Accounting\Invoice($xeroObj);
                    $invoice = $invoice->setInvoiceID($invoiceID);
                    $invoice = $invoice->setStatus(\XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_AUTHORISED);
                    $invoicePos[]=$invoiceID;
                    $sendInvoice[] = $invoice;
                }else{
                    $errorsFinal[$invoiceID] = 'You can voided a AUTHORISED invoice by updating the Status to VOIDED.';
                }  
            }
            if(!empty($sendInvoice)){
                $responses = $xeroObj->saveAll($sendInvoice);
                if(count($responses->getElementErrors())>0){
                    $errors = $responses->getElementErrors();
                        foreach($errors as $key=>$error){
                            $errorsFinal[$invoicePos[$key]] = $error;
                        }
                }
                return ['status' => true, 'data'=>count($responses->getElements())>0 ? pos_index_change_array_data($responses->getElements(),'InvoiceID'):[], 'error'=>$errorsFinal];
            }else{
                return ['status' => false, 'error'=>'record not found'];
            }
        } catch (NotFoundException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (Exception $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        }
    }

    public function invoice_status_mark_as_voided_multiple($companyId, $extraParam = [])
    {
        $invoiceIDs = isset($extraParam['InvoiceIDs']) ? $extraParam['InvoiceIDs'] : [];
        if (empty($invoiceIDs)) {
            return ['status' => false, 'msg' => 'InvoiceID Guid is required.'];
        }
        $xeroObj = $this->xeroAuth();
        if (!$xeroObj) {
            return ['status' => false, 'msg' => 'Something went wrong in config details.'];
        }
        try {
            $sendInvoice=[]; 
            $invoicePos=[]; 
            $errorsFinal=[];
            $currentInvoices =  $xeroObj->loadByGUIDs(Invoice::class, implode(',', $invoiceIDs));
            $i=0;
            foreach($currentInvoices as $currentInvoice){
                $currentStatus = $currentInvoice->getStatus();
                $invoiceID = $currentInvoice->getInvoiceID();
                if ($currentStatus == \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_AUTHORISED) {
                    $invoice = new \XeroPHP\Models\Accounting\Invoice($xeroObj);
                    $invoice = $invoice->setInvoiceID($invoiceID);
                    $invoice = $invoice->setStatus(\XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_VOIDED);
                    $invoicePos[]=$invoiceID;
                    $sendInvoice[]=$invoice;
                }else{
                    $errorsFinal[$invoiceID] = 'You can voided a AUTHORISED invoice by updating the Status to VOIDED.';
                } 
            }
            if(!empty($sendInvoice)){
                $responses = $xeroObj->saveAll($sendInvoice);
                
                if(count($responses->getElementErrors())>0){
                    $errors = $responses->getElementErrors();
                        foreach($errors as $key=>$error){
                            $errorsFinal[$invoicePos[$key]] = $error;
                        }
                }
                return ['status' => true, 'data'=>pos_index_change_array_data($responses->getElements(),'InvoiceID'), 'error'=>$errorsFinal];
            }else{
                return ['status' => false, 'error'=>'record not found'];
            }
        } catch (NotFoundException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (Exception $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        }
    }

    public function invoice_status_mark_as_paid_multiple($companyId, $extraParam = [])
    {
        $invoiceIDs=isset($extraParam['InvoiceIDs']) ? $extraParam['InvoiceIDs'] : '';
        if (empty($invoiceIDs)) {
            return ['status' => false, 'msg' => 'InvoiceID Guid is required.'];
        }
        $xeroObj = $this->xeroAuth();
        if (!$xeroObj) {
            return ['status' => false, 'msg' => 'Something went wrong in config details.'];
        }
        try {
            $sendInvoicePayment=[]; 
            $errorsFinal=[];
            $currentInvoices =  $xeroObj->loadByGUIDs(Invoice::class, implode(',', $invoiceIDs));
            //print_r($currentInvoices);
            foreach($currentInvoices as $currentInvoice){
                $currentStatus = $currentInvoice->getStatus();
                $invoiceID = $currentInvoice->getInvoiceID();
                $amountDue = $currentInvoice->getAmountDue();
                if ($currentStatus == \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_AUTHORISED) {
                    $invoice = new \XeroPHP\Models\Accounting\Invoice($xeroObj);
                    $invoice = $invoice->setInvoiceID($invoiceID);

                    $account = new \XeroPHP\Models\Accounting\Account($xeroObj);
                    $account = $account->setCode(XERO_DEFAULT_PAYMENT_PAID_ACCOUNT_CODE);
                    $invoicePayment = new \XeroPHP\Models\Accounting\Payment($xeroObj);
                    $invoicePayment = $invoicePayment->setInvoice($invoice);
                    $invoicePayment = $invoicePayment->setAccount($account);
                    $invoicePayment = $invoicePayment->setAmount($amountDue);
                    $invoicePayment = $invoicePayment->setDate(\DateTime::createFromFormat('Y-m-d', DATE_CURRENT));
                    $sendInvoicePayment[] = $invoicePayment;
                    $invoicePos[]=$invoiceID;
                }else{
                    $errorsFinal[$invoiceID] = 'You can paid only a AUTHORISED invoice.';
                }  
            }
            if(!empty($sendInvoicePayment)){
                $responses = $xeroObj->saveAll($sendInvoicePayment);
                if(count($responses->getElementErrors())>0){
                    $errors = $responses->getElementErrors();
                        foreach($errors as $key=>$error){
                            $errorsFinal[$invoicePos[$key]] = $error;
                        }
                }
                return ['status' => true, 'data'=>count($responses->getElements())>0 ? pos_index_change_array_data($responses->getElements(),'InvoiceID'):[], 'error'=>$errorsFinal];
            }else{
                return ['status' => false, 'error'=>'record not found'];
            }
        } catch (NotFoundException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (Exception $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        }
    }

    public function invoice_status_mark_as_paid($companyId, $extraParam = [])
    {
        $invoiceID = isset($extraParam['InvoiceID']) ? $extraParam['InvoiceID'] : '';
        if (empty($invoiceID)) {
            return ['status' => false, 'msg' => 'InvoiceID Guid is required.'];
        }
        $xeroObj = $this->xeroAuth();
        if (!$xeroObj) {
            return ['status' => false, 'msg' => 'Something went wrong in config details.'];
        }
        try {
            $currentInvoice =  $xeroObj->loadByGUID(Invoice::class, $invoiceID);
            $currentStatus = $currentInvoice->getStatus();
            $amountDue = $currentInvoice->getAmountDue();
            if ( $currentStatus == \XeroPHP\Models\Accounting\Invoice::INVOICE_STATUS_AUTHORISED) {
                $invoice = new \XeroPHP\Models\Accounting\Invoice($xeroObj);
                $invoice = $invoice->setInvoiceID($invoiceID);
                $account = new \XeroPHP\Models\Accounting\Account($xeroObj);
                $account = $account->setCode(XERO_DEFAULT_PAYMENT_PAID_ACCOUNT_CODE);
                $invoicePayment = new \XeroPHP\Models\Accounting\Payment($xeroObj);
                $invoicePayment = $invoicePayment->setInvoice($invoice);
                $invoicePayment = $invoicePayment->setAccount($account);
                $invoicePayment = $invoicePayment->setAmount($amountDue);
                $invoicePayment = $invoicePayment->setDate(\DateTime::createFromFormat('Y-m-d', DATE_CURRENT));
                $invoicePaymentNew = $invoicePayment->save();
                if ($invoicePaymentNew->getStatus() == '200') {
                    return ['status' => true, 'data' => $invoicePaymentNew->getElements(), 'warnings' => $invoicePaymentNew->getElementWarnings()];
                } else {
                    return ['status' => false, 'data' => $invoicePaymentNew->getElementErrors(), 'msg' => 'Something went wrong in request.'];
                }
            } else {
                return ['status' => false, 'msg' => 'You can paid only a AUTHORISED invoice.'];
            }
        } catch (NotFoundException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (RateLimitExceededException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (BadRequestException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotAvailableException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (UnauthorizedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (InternalErrorException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (NotImplementedException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (OrganisationOfflineException $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        } catch (Exception $exception) {
            return ['status' => false, 'msg' => $exception->getMessage()];
        }
    }
}