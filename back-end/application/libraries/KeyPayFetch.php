<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH.'libraries/KeyPayCommonAtuh.php';

use DocuSign\eSign\Model\Date;
use KeyPayCommonAtuh\KeyPayCommonAtuh;
class KeyPayFetch extends KeyPayCommonAtuh {

    function __construct($config) {
       // Call the KeyPayCommonAtuh constructor
       parent::__construct();

        if(isset($config['company_id'])){

            $this->setCompanyId($config['company_id']);
        }else{
            exit('company_id field is required.');
        }
        defined('KEYPAY_PAYRATE_DEFAULT_SUPER_RATE') OR define('KEYPAY_PAYRATE_DEFAULT_SUPER_RATE', 9.50); 
        defined('KEYPAY_PAYRATE_SUPER_THRESHOLD_AMOUNT') OR define('KEYPAY_PAYRATE_SUPER_THRESHOLD_AMOUNT', 100000); 
    }

    public function get_payroll_txt(){
        $keyPayData = $this->keyPayAuth();
        if(!isset($keyPayData['status'])){
            return ['status'=>$keyPayData['status'],'msg'=>$keyPayData['error']];
        }
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
        $keyPayResponse = $keyPayObject->get($urlData.'report/grosstonet',['fromDate'=>'2019-07-01','toDate'=>'2020-06-30','groupBy'=>'DefaultLocation']);
        pr([$keyPayResponse]);

    }
    /* $params = ['fromDate'=>'2019-07-01','toDate'=>'2020-06-30','groupBy'=>'DefaultLocation'] */
    public function get_payroll_gross_to_net_tax($params=array()){
        
        $keyPayData = $this->keyPayAuth();
        if(!isset($keyPayData['status'])){
            return ['status'=>$keyPayData['status'],'error'=>$keyPayData['error']];
        }
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];

        $keyPayResponse = $keyPayObject->get($urlData.'report/grosstonet',$params);
        $keyPayResponseData = $this->parserObjToArr($keyPayResponse);
        if(empty($keyPayResponseData)){
            return ['status'=>false,'error'=>"give date range data not found."];
        }
        if(isset($keyPayResponseData['message'])){
            return ['status'=>false,'error'=>$keyPayResponseData['message']];
        }
        $totalNetEarningsData = array_sum(array_column($keyPayResponseData,'netEarnings'));
        $totalHoursData = array_sum(array_column($keyPayResponseData,'totalHours'));
        $totalGrossPlusSuperData = array_sum(array_column($keyPayResponseData,'totalGrossPlusSuper'));
        $totalSuperContributionData = array_sum(array_column($keyPayResponseData,'sgc')) + array_sum(array_column($keyPayResponseData,'employerContribution'));
        $totalGrossEarningsData = array_sum(array_column($keyPayResponseData,'totalGrossEarnings'));
        
        $grossEarningData = array_column($keyPayResponseData,'grossEarnings');

        $totalAdoData = array_sum(array_column($grossEarningData,'ado Taken'));
        $totalOpenningBalanceData = array_sum(array_column($grossEarningData,'additional Payments (Opening Balance)'));
        $totalAnnualLeaveTakenData = array_sum(array_column($grossEarningData,'annual Leave Taken'));
        $totalBonusData = array_sum(array_column($grossEarningData,'bonus'));
        $totalCasualEveningShiftData = array_sum(array_column($grossEarningData,'casual - Evening Shift'));
        $totalCasualOrdinaryHoursData = array_sum(array_column($grossEarningData,'casual - Ordinary Hours'));
        $totalCommunityServiceLeaveTakenData = array_sum(array_column($grossEarningData,'community Service Leave Taken'));
        $totalLeaveLoadingData = array_sum(array_column($grossEarningData,'leave Loading'));
       
        return ['status'=>true,'data'=>[
            'totalHours'=>$totalHoursData,
            'totalGrossEarnings'=>$totalGrossEarningsData,
            'totalOpenningBalance'=>$totalOpenningBalanceData,
            'totalAdo'=>$totalAdoData,
            'totalAnnualLeaveTaken'=>$totalAnnualLeaveTakenData,
            'totalBonus'=>$totalBonusData,
            'totalCasualEveningShift'=>$totalCasualEveningShiftData,
            'totalCasualOrdinaryHours'=>$totalCasualOrdinaryHoursData,
            'totalCommunityServiceLeaveTaken'=>$totalCommunityServiceLeaveTakenData,
            'totalLeaveLoading'=>$totalLeaveLoadingData,
            'totalSuperContribution'=>$totalSuperContributionData,
            'totalGrossPlusSuper'=>$totalGrossPlusSuperData,
            'totalNetEarnings'=>$totalNetEarningsData,
            ]];
            

    }

    private function get_interval_month_wise($fromDate,$toDate){
        $startDate = new DateTime($fromDate);
        $endDate = new DateTime($toDate);
        $dateInterval = new DateInterval('P1M');
        $datePeriod   = new DatePeriod($startDate, $dateInterval, $endDate);
        $monthData = [];
            foreach ($datePeriod as $date) {
              $temp= ['from_date'=>$date->format('Y-m-01'),'to_date'=>$date->format('Y-m-t')];
              $monthData[]=$temp;
            }
        return  $monthData;
    }
    public function get_finacial_year_data(int $numberofPastYear){
        $numberofPastYear= !empty($numberofPastYear)? $numberofPastYear-1:$numberofPastYear;
        $currentYear = (int) date('m')<6 ? date('Y')-1:date('Y');
        $fromYear = $currentYear - $numberofPastYear;
        $number = range($fromYear,$currentYear,1);
        return $number;
    }
    private function get_finacial_year_between_from_to_year(int $numberofPastYear){
        $number = $this->get_finacial_year_data($numberofPastYear);
        $financeYear =[];
        asort($number);
        foreach($number as $row){
            $temp = ['from_date'=> $row.'-07-01','to_date'=> ($row+1).'-06-30'];
            $financeYear[] = $temp;
        }
        return $financeYear;
    }
    public function get_current_finacial_year_data(int $numberofPastYear=0){
           return $this->get_finacial_year_between_from_to_year($numberofPastYear);
    }
    
    public function get_payroll_gross_to_net_tax_by_last_three_year(){
        $currentDateTime = DATE_TIME;
        $keyPayData = $this->keyPayAuth();
        if(!isset($keyPayData['status'])){
            return ['status'=>$keyPayData['status'],'error'=>$keyPayData['error']];
        }
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
        $getMonthsData = [];
        $insertData = [];
        $financeYearData = $this->get_finacial_year_between_from_to_year(3);
        if(!empty($financeYearData)){
            foreach($financeYearData as $rowYear){
               $temp= $this->get_interval_month_wise($rowYear['from_date'],$rowYear['to_date']);
               $getMonthsData = array_merge($getMonthsData, $temp);
            }
        }
       
        if(!empty($getMonthsData)){
            $insertData =[];
            foreach($getMonthsData as $rowKey=>$rowMonth){
                if($rowKey%4===0){
                    sleep(1);
                }
                $params=['fromDate'=>$rowMonth['from_date'],'toDate'=>$rowMonth['to_date'],'groupBy'=>'DefaultLocation'];
                $keyPayResponse = $keyPayObject->get($urlData.'report/grosstonet',$params);
                $keyPayResponseData = $this->parserObjToArr($keyPayResponse);
                if(empty($keyPayResponseData)){
                    continue;
                }
                if(isset($keyPayResponseData['message'])){
                    continue;
                }
                if(is_string($keyPayResponseData) && !empty($keyPayResponseData)){
                    sleep(1);
                    $keyPayResponse = $keyPayObject->get($urlData.'report/grosstonet',$params);
                    $keyPayResponseData = $this->parserObjToArr($keyPayResponse);
                    if(empty($keyPayResponseData)){
                        continue;
                    }
                    if(isset($keyPayResponseData['message'])){
                        continue;
                    }
                }
                $totalWagesData = array_sum(array_column($keyPayResponseData,'taxableEarnings'));
                $totalExpenses = array_sum(array_column($keyPayResponseData,'totalExpenses'));
                $totalGrossPlusSuper = array_sum(array_column($keyPayResponseData,'totalGrossPlusSuper'));
                $totalSuper = array_sum(array_column($keyPayResponseData,'sgc')) + array_sum(array_column($keyPayResponseData,'employerContribution'));
                $insertData[]=[
                    'month_date'=>$rowMonth['from_date'],
                    'total_wages' =>$totalWagesData,
                    'total_expenses' =>$totalExpenses,
                    'total_super' =>$totalSuper,
                    'total_gross'=> $totalGrossPlusSuper,
                    'archive'=>0,
                    'created'=>$currentDateTime
                ];
            }
            if(!empty($insertData)){
                $this->CI->Basic_model->update_records('finance_payroll_keypay_graph_data',['archive'=>1],['archive'=>0]);
                $this->CI->Basic_model->insert_update_batch('insert','finance_payroll_keypay_graph_data',$insertData);
            }
        }

        return ['status'=>true];
    }

    public function get_total_payroll_current_finacial_year_data(){
        $keyPayData = $this->keyPayAuth();
        if(!isset($keyPayData['status'])){
            return ['status'=>$keyPayData['status'],'msg'=>$keyPayData['error']];
        }
        $yearData = $this->get_finacial_year_between_from_to_year(0);
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
        $keyPayResponse = $keyPayObject->get($urlData.'report/grosstonet',['fromDate'=>$yearData[0]['from_date'],'toDate'=>$yearData[0]['to_date'],'groupBy'=>'DefaultLocation']);
        $keyPayResponseData = $this->parserObjToArr($keyPayResponse);
        $totalGrossPlusSuper = 0;
        if(empty($keyPayResponseData)){
            $totalGrossPlusSuper = 0;
        }else if(isset($keyPayResponseData['message'])){
            $totalGrossPlusSuper = 0;
        }else if(!empty($keyPayResponseData) && is_array($keyPayResponseData)){
            $totalGrossPlusSuper = array_sum(array_column($keyPayResponseData,'totalGrossPlusSuper'));
        }
        return ['status'=>true,'total_data'=>$totalGrossPlusSuper];
    }

    public function get_total_payroll_current_finacial_year_to_current_date_data(){
        $keyPayData = $this->keyPayAuth();
        if(!isset($keyPayData['status'])){
            return ['status'=>$keyPayData['status'],'msg'=>$keyPayData['error']];
        }
        $yearData = $this->get_finacial_year_between_from_to_year(0);
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
      /* $keyPayResponse = $keyPayObject->get($urlData.'report/grosstonet',['fromDate'=>$yearData[0]['from_date'],'toDate'=>DATE_CURRENT,'groupBy'=>'DefaultLocation']);
        $keyPayResponseData = $this->parserObjToArr($keyPayResponse);
        $totalGrossPlusSuper = 0;
        if(empty($keyPayResponseData)){
            $totalGrossPlusSuper = 0;
        }else if(isset($keyPayResponseData['message'])){
            $totalGrossPlusSuper = 0;
        }else if(!empty($keyPayResponseData) && is_array($keyPayResponseData)){
            $totalGrossPlusSuper = array_sum(array_column($keyPayResponseData,'totalGrossPlusSuper'));
        } */
        $keyPayResponse = $keyPayObject->get($urlData.'report/payg',['fromDate'=>$yearData[0]['from_date'],'toDate'=>DATE_CURRENT,'groupBy'=>'DefaultLocation']);
        $keyPayResponseData = $this->parserObjToArr($keyPayResponse);
        $totalGrossPlusSuper = 0;
        if(empty($keyPayResponseData)){
            $totalGrossPlusSuper = 0;
        }else if(isset($keyPayResponseData['message'])){
            $totalGrossPlusSuper = 0;
        }else if(!empty($keyPayResponseData) && is_array($keyPayResponseData)){
            $totalGrossPlusSuper = array_sum(array_column($keyPayResponseData,'grossEarnings'));
        }
        return ['status'=>true,'total_data'=>$totalGrossPlusSuper];
    }
    public function get_total_payrun_out($viewType='week'){
        
        $keyPayData = $this->keyPayAuth();
        if(!isset($keyPayData['status'])){
            return ['status'=>$keyPayData['status'],'msg'=>$keyPayData['error']];
        }
        $viewType = !empty($viewType) ? strtolower($viewType):'';
        $viewType = in_array($viewType,['week','month','year']) ? $viewType : 'week';
        /* switch($viewType){
            case 'month':
            $fromDate = date('Y-m-01', strtotime('-2 month'));
            $toDate = DATE_CURRENT;
            break;
            case 'year':
            $fromDate = date('Y-01-01', strtotime('-2 year'));
            $toDate = DATE_CURRENT;
            break;
            case 'week': 
            $fromDate = date(DB_DATE_FORMAT,strtotime("this monday - 4 week"));
            $toDate = DATE_CURRENT;
            break;
        } */
        $viewTypeData = get_shfit_payroll_filter($viewType);
        $fromDate = $viewTypeData['fromDate'];
        $toDate = $viewTypeData['toDate'];
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
        $keyPayResponse = $keyPayObject->get($urlData.'report/payg',['fromDate'=>$fromDate,'toDate'=> $toDate]);
        $keyPayResponseData = $this->parserObjToArr($keyPayResponse);
        $totalGrossEarnings = 0;
        if(empty($keyPayResponseData)){
            $totalGrossEarnings = 0;
        }else if(isset($keyPayResponseData['message'])){
            $totalGrossEarnings = 0;
        }else if(!empty($keyPayResponseData) && is_array($keyPayResponseData)){
            $totalGrossEarnings = array_sum(array_column($keyPayResponseData,'grossEarnings'));
        }
        return ['status'=>true,'total_data'=>$totalGrossEarnings];
       /*  $totalGrossEarnings = 0;
        $totalSuperContributions = 0;
        if(empty($keyPayResponseData)){
            $totalGrossEarnings = 0;
            $totalSuperContributions = 0;
        }else if(isset($keyPayResponseData['message'])){
            $totalGrossEarnings = 0;
            $totalSuperContributions = 0;
        }else if(!empty($keyPayResponseData) && is_array($keyPayResponseData)){
            $totalGrossEarnings = array_sum(array_column($keyPayResponseData,'grossEarnings'));
            $totalSuperContributions = array_sum(array_column($keyPayResponseData,'superContributions'));
        }
        return ['status'=>true,'total_data'=>$totalGrossEarnings+$totalSuperContributions]; */
    }

    public function test ()
    {
        $keyPayData = $this->keyPayAuth();
        if(!isset($keyPayData['status'])){
            return ['status'=>$keyPayData['status'],'msg'=>$keyPayData['error']];
        }
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
        $keyPayResponse = $keyPayObject->get($urlData.'report/payg',['fromDate'=>'2019-07-01','toDate'=>'2020-06-30']);
        pr([$keyPayResponse]);

    }

    public function create_employee ($employeeDataParms=[])
    {
        $employeeDataParms= [(object)[
            "dateCreated"=> "2019-12-20",
            "dateOfBirth"=> "1997-12-20",
            "emailAddress"=> "tes@tes.com",
            "externalId"=> "12",
            "firstName"=> "test keypay api",
            "gender"=> "male",
            "hoursPerWeek"=> "48.00",
            "locations"=> "42 Eurack Court",
            "middleName"=> "test",
            "mobilePhone"=> "94556464664",
            "postalAddressLine2"=> "string",
            "postalCountry"=> "Australia",
            "postalPostCode"=> "2594",
            "postalState"=> "New South Wales",
            "postalStreetAddress"=> "42 Eurack Court",
            "postalSuburb"=> "KIKIAMAH",
            "rate"=> "20.00",
            "rateUnit"=> "hourly",
            "startDate"=> "2019-12-20",
            "status"=> "Active",
            "surname"=> "testapi",
            "title"=> "keypay api test",
            "taxFileNumber"=>"12121111"
        ]];
        $keyPayData = $this->keyPayAuth();
        if(!isset($keyPayData['status'])){
            return ['status'=>$keyPayData['status'],'msg'=>$keyPayData['error']];
        }
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
        $keyPayResponse = $keyPayObject->post($urlData.'employee/unstructured',$employeeDataParms);
        pr([$keyPayResponse]);

    }

    public function check_kiosk_exists_and_create(){
        $keyPayData = $this->keyPayAuth();
        if(!isset($keyPayData['status'])){
            return ['status'=>$keyPayData['status'],'msg'=>$keyPayData['error']];
        }
        if(!empty($keyPayData['auth_details']['kiosks_id']) && !is_null($keyPayData['auth_details']['kiosks_id'])){
            return ['status'=>$keyPayData['status'],'data'=>$keyPayData];
        }else if(is_null($keyPayData['auth_details']['kiosks_id']) || empty($keyPayData['auth_details']['kiosks_id'])){
            $urlData = $keyPayData['url_with_id'];
            $keyPayObject = $keyPayData['keyPayObject'];
            $locationId = $keyPayData['auth_details']['location_id'];
            if(empty($locationId) || is_null($locationId)){
                $locationDataParms =[
                    "parentId"=> null,
                    "name"=> "HCM-Location",
                    "externalId"=> uniqid(),//$keyPayData['auth_details']['id'],
                    "source"=> "None",
                    "fullyQualifiedName"=> "HCM-Location",
                    "state"=> null,
                    "isGlobal"=> "false",
                    "isRollupReportingLocation"=> "false",
                    "generalLedgerMappingCode"=> "HCM"
                ];
                
                $keyPayResponse = $keyPayObject->post($urlData.'location',$locationDataParms);
                $keyPayResponseData = $this->parserObjToArr($keyPayResponse);
                if(!isset($keyPayResponseData['id'])){
                    return ['status'=>false,'msg'=>"location not created",'response'=>$keyPayResponseData];
                }
                $locationId = $keyPayResponseData['id'];
            }
            $kioskDataParms = ["timeZone"=>"AUS Eastern Standard Time","allowEmployeeShiftSelection"=>"true","allowHigherClassificationSelection"=>"true","canAddEmployees"=>"true","externalId"=>$keyPayData['business_id'],"isLocationRequired"=>"false","LocationId"=>$locationId,"isPhotoRequired"=>"false","name"=>"HCM-MEMBER-KIOSKS"];
            $keyPayResponseKiosk = $keyPayObject->post($urlData.'kiosk',($kioskDataParms));
            $keyPayResponseKioskData = $this->parserObjToArr($keyPayResponseKiosk);
            if(!isset($keyPayResponseKioskData['id'])){
                return ['status'=>false,'msg'=>"kiosk not created."];
            }
            $koiskId = $keyPayResponseKioskData['id'];
             if(!empty($koiskId) && !empty($locationId)){
                $this->CI->Basic_model->update_records($this->table,['kiosks_id'=>$koiskId,'location_id'=>$locationId],['id'=>$keyPayData['auth_details']['id']]);
                $keyPayData = $this->keyPayAuth();
                return ['status'=>true,'msg'=>"kiosk has been created successfully.",'data'=>$keyPayData];
             }
        }else{
            return ['status'=>true,'msg'=>"kiosk alredy created.",'data'=>$keyPayData];
        }
    }

    public function kiosk_employee_create($employeeDetails,$keyPayData=[]) {
        $keyPayData = !empty($keyPayData) ? array_filter($keyPayData) :[];
        if(empty($keyPayData) || !isset($keyPayData['url_with_id']) || !isset($keyPayData['keyPayObject']) || !isset($keyPayData['auth_details'])){
            $keyPayData = $this->keyPayAuth();
        }

        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
        $kioskId = $keyPayData['auth_details']['kiosks_id'];
        $empDetailsData = [
            "email"=> $employeeDetails['email'],
            "firstName"=> $employeeDetails['firstname'],
            "mobileNumber"=> $employeeDetails['phone'],
            "pin"=> KEYPAY_EMPLOYEE_PIN,
            "surname"=> $employeeDetails['lastname'],
        ];
        $keyPayResponseKioskEmp = $keyPayObject->post($urlData.'kiosk/'.$kioskId.'/staff',$empDetailsData);
        $keyPayResponseKioskEmpData = $this->parserObjToArr($keyPayResponseKioskEmp);
        if(!isset($keyPayResponseKioskEmpData['employeeId'])){
            return ['status'=>false,'msg'=>"kiosk emp not created."];
        }
        $keypay_emp_id = $keyPayResponseKioskEmpData['employeeId'];

        if(!empty($keypay_emp_id)){
            return ['status'=>true,'msg'=>"kiosk emp has been created successfully.","emp_id"=>$keypay_emp_id];
        }else{
            return ['status'=>false,'msg'=>"kiosk emp not created."];
        }
    }
    public function kiosk_complete_shift_create($shiftDetails,$keyPayData=[]) {
        $keyPayData = !empty($keyPayData) ? array_filter($keyPayData) :[];
        if(empty($keyPayData) || !isset($keyPayData['url_with_id']) || !isset($keyPayData['keyPayObject']) || !isset($keyPayData['auth_details'])){
            $keyPayData = $this->keyPayAuth();
        }

        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
        $kioskId = $keyPayData['auth_details']['kiosks_id'];
        $locationId = $keyPayData['auth_details']['location_id'];
        $shiftDetailsData = [
            "breaks"=> [],
            "employeeId"=> $shiftDetails['keypay_emp_id'],
            "ipAddress"=> $shiftDetails['ip_address'],
            "isAdminInitiated"=> "TRUE",
            "kioskId"=> $kioskId,
            "latitude"=> $shiftDetails['latitude'],
            "locationId"=> $locationId,
            "longitude"=> $shiftDetails['longitude'],
            "note"=> $shiftDetails['notes'],
            "noteVisibility"=> 'Visible',
            "recordedEndTimeUtc"=> $shiftDetails['end_time'],
            "recordedStartTimeUtc"=> $shiftDetails['start_time'],
            "recordedTimeUtc"=> $shiftDetails['current_time'],
            "shiftConditions"=>[['id'=> $shiftDetails['hcm_shift_id'],'name'=>'HCM-shift-id-'. $shiftDetails['hcm_shift_id']]]
        ];
        $keyPayResponseKioskEmp = $keyPayObject->post($urlData.'kiosk/'.$kioskId.'/addshift',$shiftDetailsData);
        $keyPayResponseKioskEmpData = $this->parserObjToArr($keyPayResponseKioskEmp);
        if(isset($keyPayResponseKioskEmpData['message'])){
            return ['status'=>false,'error'=>$keyPayResponseKioskEmpData['message'],'data'=>$keyPayResponseKioskEmpData['message']];
        }else{
            $res = $this->kiosk_get_shift_create($shiftDetails,$keyPayData);
            $id ='';
            if($res['status']){
                $dataObj = obj_to_arr($res['data']);
                $id= $dataObj[0]['id'];
            }
            return ['status'=>true,'msg'=>'kiosk shift added sucessfully.','data'=>$keyPayResponseKioskEmpData, 'keypay_shift_id'=>$id]; 
        }
    }

    public function kiosk_get_shift_create($shiftDetails=[],$keyPayData=[]) {
        $keyPayData = !empty($keyPayData) ? array_filter($keyPayData) :[];
        if(empty($keyPayData) || !isset($keyPayData['url_with_id']) || !isset($keyPayData['keyPayObject']) || !isset($keyPayData['auth_details'])){
            $keyPayData = $this->keyPayAuth();
        }
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
        $kioskId = $keyPayData['auth_details']['kiosks_id'];
        $locationId = $keyPayData['auth_details']['location_id'];
        $empDetailsData = [
            "employeeId"=> $shiftDetails['keypay_emp_id'],
            "fromDateUtc"=> $shiftDetails['start_time'],
            "kioskId"=> $kioskId,
            "locationId"=> $locationId,
            "toDateUtc"=> $shiftDetails['end_time']
        ];
        $keyPayResponseKioskEmp = $keyPayObject->post($urlData.'kiosk/shifts',$empDetailsData);
        $keyPayResponseKioskEmpData = $this->parserObjToArr($keyPayResponseKioskEmp);
        if(isset($keyPayResponseKioskEmpData['message'])){
            return ['status'=>false,'error'=>$keyPayResponseKioskEmpData['message'],'data'=>$keyPayResponseKioskEmpData['message']];
        }else{
            return ['status'=>true,'msg'=>'kiosk get shift fetch successfully.','data'=>$keyPayResponseKioskEmpData];
        }
        
    }
    public function create_pay_category($payCategoryDetails=[],$keyPayData=[]) {
        $keyPayData = !empty($keyPayData) ? array_filter($keyPayData) :[];
        if(empty($keyPayData) || !isset($keyPayData['url_with_id']) || !isset($keyPayData['keyPayObject']) || !isset($keyPayData['auth_details'])){
            $keyPayData = $this->keyPayAuth();
        }
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];

        $createDetailsData = [
            "accruesLeave"=> "true",
            "defaultSuperRate"=> KEYPAY_PAYRATE_DEFAULT_SUPER_RATE,
            "isPayrollTaxExempt"=> "false",
            "isSystemPayCategory"=> "false",
            "isTaxExempt"=> "false",
            "name"=> $payCategoryDetails['name'],
            "externalId"=> $payCategoryDetails['externalId'],
            "numberOfDecimalPlaces"=> 2,
            "payCategoryType"=>"Standard",
            "penaltyLoadingPercent"=>0.00,
            "rateLoadingPercent"=>0.00,
            "rateUnit"=>$payCategoryDetails['rate_type_name'],
            "source"=>"HCM Finance",
            "superExpenseMappingCode"=>"HCM Finance",
            "superLiabilityMappingCode"=>"HCM Finance"
        ];
        $keyPayResponse = $keyPayObject->post($urlData.'paycategory',$createDetailsData);
        $keyPayResponseData = $this->parserObjToArr($keyPayResponse);
        if(isset($keyPayResponseData['message'])){
            return ['status'=>false,'error'=>$keyPayResponseData['message'],'data'=>$keyPayResponseData['message']];
        }else{
            return ['status'=>true,'msg'=>'Pay Category has been created successfully.','data'=>$keyPayResponseData];
        }
        
    }

    public function create_pay_rate_template($payRateTemplateDetails=[],$keyPayData=[]) {
        $keyPayData = !empty($keyPayData) ? array_filter($keyPayData) :[];
        if(empty($keyPayData) || !isset($keyPayData['url_with_id']) || !isset($keyPayData['keyPayObject']) || !isset($keyPayData['auth_details'])){
            $keyPayData = $this->keyPayAuth();
        }
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];

        $createDetailsData = [
            "name"=> $payRateTemplateDetails['name'],
            "payCategories"=> $payRateTemplateDetails['payCategories'],
            "externalId"=> $payRateTemplateDetails['externalId'],
            "primaryPayCategoryId"=> $payRateTemplateDetails['primaryPayCategoryId'],
            "reapplyToLinkedEmployees"=> "true",
            "source"=> "HCM Finance",
            "superThresholdAmount"=> KEYPAY_PAYRATE_SUPER_THRESHOLD_AMOUNT
        ];
        $keyPayResponse = $keyPayObject->post($urlData.'payratetemplate',$createDetailsData);
        $keyPayResponseData = $this->parserObjToArr($keyPayResponse);
        if(isset($keyPayResponseData['message'])){
            return ['status'=>false,'error'=>$keyPayResponseData['message'],'data'=>$keyPayResponseData['message']];
        }else{
            return ['status'=>true,'msg'=>'Payrate Template has been created successfully.','data'=>$keyPayResponseData];
        }
        
    }

    public function getKeyPayAuth(){
        return $this->keyPayAuth();
    }

    public function delete_pay_category($payCategoryIds=[],$keyPayData=[]) {
        $keyPayData = !empty($keyPayData) ? array_filter($keyPayData) :[];
        if(empty($keyPayData) || !isset($keyPayData['url_with_id']) || !isset($keyPayData['keyPayObject']) || !isset($keyPayData['auth_details'])){
            $keyPayData = $this->keyPayAuth();
        }
        $payCategoryIds = !empty($payCategoryIds) ? $payCategoryIds : 0;
        $payCategoryIds = is_array($payCategoryIds) ? $payCategoryIds :[$payCategoryIds];
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
        $responseData =[];
        if(!empty($payCategoryIds)){
            $i=1;
            foreach($payCategoryIds as $row){
                if($i%3===0){
                    sleep(1);
                }
                $catIds = $row;
                $keyPayResponse = $keyPayObject->delete($urlData.'paycategory/'.$catIds);
                $keyPayResponseData =  $this->parserObjToArr($keyPayResponse);
                $responseData[$catIds] = $keyPayResponseData;
                $i++;
            }
            return ['status'=>true,'msg'=>'Pay Category has been deleted successfully.','data'=>$responseData];
        }else{
            return ['status'=>false,'error'=>'Pay Category id is missing.','data'=>$responseData];
        }
    }

    public function delete_payrate_template($payrateTemplateIds=[],$keyPayData=[]) {
        $keyPayData = !empty($keyPayData) ? array_filter($keyPayData) :[];
        if(empty($keyPayData) || !isset($keyPayData['url_with_id']) || !isset($keyPayData['keyPayObject']) || !isset($keyPayData['auth_details'])){
            $keyPayData = $this->keyPayAuth();
        }
        $payrateTemplateIds = !empty($payrateTemplateIds) ? $payrateTemplateIds : 0;
        $payrateTemplateIds = is_array($payrateTemplateIds) ? $payrateTemplateIds :[$payrateTemplateIds];
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
        $responseData =[];
        if(!empty($payrateTemplateIds)){
            $i=1;
            foreach($payrateTemplateIds as $row){
                if($i%3===0){
                    sleep(1);
                }
                $tempIds = $row;
                $keyPayResponse = $keyPayObject->delete($urlData.'payratetemplate/'.$tempIds);
                $keyPayResponseData = $this->parserObjToArr($keyPayResponse);
                $responseData[$tempIds] = $keyPayResponseData;
                $i++;
            }
            return ['status'=>true,'msg'=>'Pay Rate Template has been deleted successfully.','data'=>$responseData];
        }else{
            return ['status'=>false,'error'=>'Pay Rate Template id is missing.','data'=>$responseData];
        }
    }

    public function get_pay_run(){

        $keyPayData = $this->keyPayAuth();
        if(!isset($keyPayData['status'])){
            return ['status'=>$keyPayData['status'],'msg'=>$keyPayData['error']];
        }
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
        $keyPayResponse = $keyPayObject->get($urlData.'payrun?$filter='.urlencode("PayPeriodStarting ge datetime'2019-10-01T00:00:00'").urlencode(' and ').urlencode("PayPeriodStarting le datetime'2019-10-31T00:00:00'"),[]);
        $keyPayResponseData = $this->parserObjToArr($keyPayResponse);
        pr($keyPayResponseData);

    }

    public function get_pay_run_by_empid(){

        $keyPayData = $this->keyPayAuth();
        if(!isset($keyPayData['status'])){
            return ['status'=>$keyPayData['status'],'msg'=>$keyPayData['error']];
        }
        $payRunId = '5734447';
        $employeeData = '2261500';
        $urlData = $keyPayData['url_with_id'];
        $keyPayObject = $keyPayData['keyPayObject'];
        $keyPayResponse = $keyPayObject->get($urlData.'payrun/'.$payRunId.'/payslips/'.$employeeData);
        $keyPayResponseData = $this->parserObjToArr($keyPayResponse);
        pr($keyPayResponseData);

    }

}

