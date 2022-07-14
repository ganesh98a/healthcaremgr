<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Finance_cron extends MX_Controller 
{
	use callBackGroundProcess;
	function __construct() {
		parent::__construct();
		$this->load->model(['Finance_cron_model','finance/Finanace_shift_payroll_model','finance/Finance_common_model','finance/Finance_invoice_model','finance/Finance_pay_rate_model']);
		$this->load->model('Basic_model');
		
	}
	
	public function update_payrate_status()
	{
		$result = $this->Finance_cron_model->update_payrate_status();
		echo json_encode($result);
	}
	private function payroll_exemption_send_renewal_email_data($cronId,$extraParms=[]){
		
		$record = $this->Finanace_shift_payroll_model->get_org_payroll_exemption_current_status_by_current_date(0,$extraParms);	
		
		if (!empty($record)) 
		{ 
			$orgIds = array_column($record,'organisation_id');
			$orgDetails = $this->Finance_common_model->get_organisation_details_by_ids($orgIds);
			$orgDetailsData = !empty($orgDetails) ? pos_index_change_array_data($orgDetails,'org_id') :[];
			foreach($record as $row){
				$orgId = $row['organisation_id'];
				$orgDetails = isset($orgDetailsData[$orgId])? $orgDetailsData[$orgId] :[];
				if(empty($orgDetails)){
					continue;
				}
				$contactName = !empty( $orgDetails['contact_name']) ?  $orgDetails['contact_name']:$orgDetails['org_name'];
           		$email =  $orgDetails['email'];
            	$expiredOn =  DateFormate($row['fin_valid_until'],'d/m/Y');
				send_reminder_renewal_payroll_exemption_email((object)['to_email'=>$email,'org_contact_name'=>$contactName,'expire_untill'=>$expiredOn]);
			}
			$extraParms['current_page'] = $extraParms['current_page']+1;
			return $this->payroll_exemption_send_renewal_email_data($cronId,$extraParms);
			
		}else{
			$this->Basic_model->update_records('cron_status', array('status'=>'1'),['id'=>$cronId]);
			return ['status'=>true,'msg'=>'cron run successfully','parms'=>$extraParms];
		}

	}

	public function payroll_exemption_send_renewal_email(){
		$parms = [
			'cron_call'=>'1',
			'return_type'=>'2',
			'limit_size'=>'30',
			'current_page'=>'0',
		];
		$response = $this->call_background_process('payroll_send_renewal_email',['interval_minute'=>25,'method_call'=>'payroll_exemption_send_renewal_email_data','method_params'=>$parms]);
		echo json_encode($response);
		exit;
	}

	public function create_finance_pending_invoice(){
		ini_set('display_errors', '1');
		error_reporting(E_ALL);
		$parms = ['limit'=>5];
		$response = $this->call_background_process('finance_pending_invoice_create',['interval_minute'=>25,'method_call'=>'finance_pending_invoice_create','method_params'=>$parms]);
		echo json_encode($response);
		exit;

	}

	private function finance_pending_invoice_create($cronId,$extraParms=[]){
		$limit = $extraParms['limit'] ?? 5;
		$record = $this->Finance_invoice_model->get_pending_invoice_create($limit);	
		
		if (!empty($record)) 
		{ 
			$i=0;
			foreach($record as $row){
				if($i%5==0){
					// sleep(0.5);
				}
				$invoiceId = $row['id'];
				if(empty($invoiceId)){
					continue;
				}
				$res = $this->Finance_invoice_model->invoice_send_to_xero_by_invoice_id($invoiceId);
				echo $invoiceId.''.PHP_EOL; 
			}
			
			$this->finance_pending_invoice_create($cronId,$extraParms);
			
		}else{
			$this->Basic_model->update_records('cron_status', array('status'=>'1'),['id'=>$cronId]);
			return ['status'=>true,'msg'=>'cron run successfully','parms'=>$extraParms];
		}

	}

	public function update_finance_invoice_status(){
		$parms = ['limit'=>25];
		$response = $this->call_background_process('finance_invoice_status_update',['interval_minute'=>25,'method_call'=>'finance_invoice_status_update','method_params'=>$parms]);
		echo json_encode($response);
		exit;

	}

	private function finance_invoice_status_update($cronId,$extraParms=[]){
		$companyId = XERO_DEFAULT_COMPANY_ID;
		$params = array('company_id' => $companyId);
		$this->load->library('XeroInvoice', $params);

		$limit = $extraParms['limit'] ?? 25;
		$record = $this->Finance_invoice_model->get_finance_invoice_status_update($limit);	
		
		if (!empty($record)) 
		{ 
			//pr($record);
			$updateData=[];
			$i=0;
			$dataCallWithVoided =[];
			$dataCall=[];
			$dataUpdateById=[];
			$record = pos_index_change_array_data($record,'id');
			foreach($record as $row){
				$invoiceData = !empty($row['invoice_data']) && !is_null($row['invoice_data']) ? json_decode($row['invoice_data'],true):[];
				if(empty($invoiceData)){
					continue;
				}
				//$res = $this->Finance_invoice_model->invoice_update_status($invoiceData['xero_invoice_id'],$invoiceData['status']);
				$dataUpdateById[$invoiceData['xero_invoice_id']] = $row['id'];
				if($invoiceData['status']=='2'){
					$dataCallWithVoided[] = $invoiceData['xero_invoice_id'];
				}else{
					$dataCall[] = $invoiceData['xero_invoice_id'];
				}
			}
			$parms = ['InvoiceIDs'=>(!empty($dataCall) && !empty($dataCallWithVoided)) ? array_merge($dataCall,$dataCallWithVoided) : (!empty($dataCall) ? $dataCall:[])];
			$parmsVoided = ['InvoiceIDs'=>$dataCallWithVoided];
			$parmsPaid = ['InvoiceIDs'=>$dataCall];
			$res= $this->xeroinvoice->invoice_status_mark_as_Authorised_multiple($companyId,$parms);
			$res2 = $this->xeroinvoice->invoice_status_mark_as_voided_multiple($companyId,$parmsVoided);
			$res3 = $this->xeroinvoice->invoice_status_mark_as_paid_multiple($companyId,$parmsPaid);
			foreach($dataUpdateById as $key =>$row){
				$temp=['id'=>$row];
				$invoice_data = json_decode($record[$row]['invoice_data'],true);
				if($invoice_data['status']=='2'){
					$temp['log_status'] = isset($res['data'][$key]) && isset($res2['data'][$key]) ? 1:2;
					$authorized = isset($res['data'][$key]) ? $res['data'][$key] :[];
					$authorizedError = isset($res['error'][$key]) ? $res['error'][$key] :[];
					$voided = isset($res2['data'][$key]) ? $res2['data'][$key] :$res2;
					$voidedError = isset($res2['error'][$key]) ? $res2['error'][$key] :$res2;
					$temp['log_response'] = json_encode(['authorized'=>$authorized,'authorizedError'=>$authorizedError,'voided'=>$voided,'voidedError'=>$voidedError]);
				}else{
					$authorized = isset($res['data'][$key]) ? $res['data'][$key] :[];
					$authorizedError = isset($res['error'][$key]) ? $res['error'][$key] :[];
					$temp['log_response'] = json_encode(['authorized'=>$authorized,'authorizedError'=>$authorizedError,'res']);
					$temp['log_status'] = isset($res['data'][$key]) ? 1 : 2;
				}
				$updateData[]=$temp;
			}

			if(!empty($updateData)){
                $this->basic_model->insert_update_batch('update', 'finance_invoice_import_status_update_log', $updateData,'id');
			}
			return $this->finance_invoice_status_update($cronId,$extraParms);
			
		}else{
			$this->Basic_model->update_records('cron_status', array('status'=>'1'),['id'=>$cronId]);
			return ['status'=>true,'msg'=>'cron run successfully','parms'=>$extraParms];
		}

	}

	public function update_finance_invoice_status_after_credit_note_apply(){
		$parms = ['limit'=>25];
		$response = $this->call_background_process('finance_invoice_status_update_after_credit_note_apply',['interval_minute'=>25,'method_call'=>'finance_invoice_status_update_after_credit_notes_apply','method_params'=>$parms]);
		echo json_encode($response);
		exit;

	}

	private function finance_invoice_status_update_after_credit_notes_apply($cronId,$extraParms=[]){
		$companyId = XERO_DEFAULT_COMPANY_ID;
		$params = array('company_id' => $companyId);
		$this->load->library('XeroInvoice', $params);

		$limit = $extraParms['limit'] ?? 25;
		$record = $this->Finance_invoice_model->invoice_status_update_credit_notes_amount_apply($limit);	
		
		if (!empty($record)) 
		{ 
			$dataCall = !empty($record) ? array_column($record,'xero_invoice_id') :[];
			if(!empty($dataCall)){
				$updateData = [];
				$parms = ['InvoiceIDs'=>$dataCall];
				$res= $this->xeroinvoice->invoice_status_mark_as_Authorised_multiple($companyId,$parms);
				$res2= $this->xeroinvoice->invoice_status_mark_as_paid_multiple($companyId,$parms);
				foreach($record as $key =>$row){
					$temp=['id'=>$row['id']];
					$xeroKey = $row['xero_invoice_id'];
					$statusUpdatedData = isset($res['data'][$xeroKey]) ? $res['data'][$xeroKey] :[];
					if(!empty($statusUpdatedData)){
						$temp['status']=1;
						$updateData[]=$temp;
					}
				}

				if(!empty($updateData)){
					$this->basic_model->insert_update_batch('update', 'finance_invoice', $updateData,'id');
				}
			}
			return $this->finance_invoice_status_update_after_credit_notes_apply($cronId,$extraParms);
			
			
		}else{
			$this->Basic_model->update_records('cron_status', array('status'=>'1'),['id'=>$cronId]);
			return ['status'=>true,'msg'=>'cron run successfully','parms'=>$extraParms];
		}

	}

	public function finance_payrate_data_send_to_keypay(){
		$parms = ['limit'=>25,'start_date'=>DATE_CURRENT];
		$response = $this->call_background_process('finance_payrate_send_to_keypay',['interval_minute'=>25,'method_call'=>'finance_payrate_data_send_to_keypay_data','method_params'=>$parms]);
		echo json_encode($response);
		exit;

	}

	private function finance_payrate_data_send_to_keypay_data($cronId,$extraParms=[]){

		$limit = $extraParms['limit'] ?? 25;
		$startDate = $extraParms['start_date'] ?? DATE_CURRENT;
		$record = $this->Finance_pay_rate_model->get_payrate_id_for_data_send_to_keypay($limit,['start_date'=>$startDate]);	
		if (!empty($record)) 
		{ 
			foreach($record as $row){
				$this->Finance_pay_rate_model->add_payrate_in_keypay_by_payrate_id($row['id'],'cron');
				if(!empty($row['parent_pay_rate'])){
					$this->Finance_pay_rate_model->delete_payrate_in_keypay_by_payrate_id($row['parent_pay_rate'],'cron');
				}
			}
			return $this->finance_payrate_data_send_to_keypay_data($cronId,$extraParms);
				
		}else{
			$this->Basic_model->update_records('cron_status', array('status'=>'1'),['id'=>$cronId]);
			return ['status'=>true,'msg'=>'cron run successfully','parms'=>$extraParms];
		}

	}

	public function finance_payrate_create(){
		$parms = ['limit'=>25,'end_date'=>DATE_CURRENT];
		$response = $this->call_background_process('finance_payrate_create_increase_rate_type',['interval_minute'=>25,'method_call'=>'finance_payrate_create_increase_rate_type_data','method_params'=>$parms]);
		echo json_encode($response);
		exit;

	}

	private function finance_payrate_create_increase_rate_type_data($cronId,$extraParms=[]){

		$limit = $extraParms['limit'] ?? 25;
		$endDate = $extraParms['end_date'] ?? DATE_CURRENT;
		$record = $this->Finance_pay_rate_model->get_payrate_for_create_new_rate_type($limit,['end_date'=>$endDate]);	
		if (!empty($record)) 
		{ 
			
			$recordData = $record['data'] ??[];
			$recordRowData = $record['rows_data'] ??[];
			$payrateIdS = !empty($recordRowData)? array_unique(array_keys($recordRowData)):[0];
			$newParateIds = $this->Finance_pay_rate_model->create_new_payrate_by_payrate_ids($payrateIdS);
			$newParateIds = !empty($newParateIds) ? pos_index_change_array_data($newParateIds,'increased_payrate_id_parent') : [];
			if(!empty($recordData)){
				foreach($recordData as $row){
					$paypointData = [];
					$updatePayrate = [];
					if(isset($recordRowData[$row['id']])){
						$payRateId =$newParateIds[$row['id']]['id'] ?? 0;
						$updatePayrate[] =['id'=>$row['id'],'created_increased_payrate'=>1]; 
						foreach($recordRowData[$row['id']] as $rowPoint) {
							$temp =[
								'rate_type'=>$rowPoint['rate_type'],
								'increased_by'=>0.00,
								'archive'=>0,
								'created'=>DATE_TIME,
								'payrate_id'=>$payRateId,
								'dollar_value' => $this->Finance_pay_rate_model->calculate_increase_payrate_value($rowPoint['dollar_value'],$rowPoint['increased_by']),
							];
							$paypointData[] = $temp;
						}
					} 
				}

				if(!empty($paypointData)){
					$this->basic_model->insert_update_batch('insert','finance_payrate_paypoint',$paypointData);
				}
				if(!empty($updatePayrate)){
					$this->basic_model->insert_update_batch('update','finance_payrate',$updatePayrate,'id');
				}


			}
			return $this->finance_payrate_create_increase_rate_type_data($cronId,$extraParms);
				
		}else{
			$this->Basic_model->update_records('cron_status', array('status'=>'1'),['id'=>$cronId]);
			return ['status'=>true,'msg'=>'cron run successfully','parms'=>$extraParms];
		}

	}

}