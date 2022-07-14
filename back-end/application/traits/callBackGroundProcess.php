<?php

trait callBackGroundProcess
{
    /* public function call_background_process_data($cronId=0)
      {
      $this->load->helper('mailchimp');
      $process = "SELECT SQL_CALC_FOUND_ROWS background_process.* FROM background_process
      INNER JOIN cron_status on cron_status.id='".$cronId."' and method_name='call_background_process' and cron_status.last_date_time> NOW() - INTERVAL 25 MINUTE and cron_status.status=0
      WHERE background_process.is_done = 0
      limit 30";
      $exe = $this->db->query($process);
      $record = $exe->result();
      if (!empty($record))
      {
      $dt_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
      $current_date = CURRENT_DATE_TIME;
      $body = '';
      $update_data = array();
      foreach ($record as $val)
      {
      $fun_name = $val->calling_method;
      $data = json_decode($val->complete_data,true);
      $data = $fun_name == 'cancel_single_order_mail'?$data:[$data];
      $return = call_user_func_array($fun_name,$data);
      #$return =  true;

      if($return)
      {
      $update_data[] = array('is_done'=>1,'id'=>$val->id);
      }
      else
      {
      $body .= $val->id.'__';
      $update_data[] = array('is_done'=>2,'id'=>$val->id);
      }
      }
      if(!empty($update_data))
      {
      $this->common_model->insert_update_batch('update','background_process',$update_data,'id');
      }

      if(!empty($body))
      {
      $subject = 'Back ground process of Bulk order update from store side is fail. ('.DEV_ENV.')';
      $mail_body = 'Following ids is failed from `background_process` table <br/><br/>'.rtrim($body,'__');
      send_mail('ritesh@cssindiaonline.com',$subject,$mail_body,'vipin@cssindiaonline.com');
      }
      if($dt_total>0){
      $this->call_background_process_data($cronId);
      }
      }else{
      $this->common_model->update('cron_status', array('status'=>'1'),['id'=>$cronId]);
      exit();
      return true;
      }
      }
     */

    protected function call_background_process($methodName = '', $extraParms = [])
    {
        $this->load->model('Basic_model');
        $methodNameAllowed = [
            'payroll_send_renewal_email',
            'finance_pending_invoice_create',
            'finance_invoice_status_update',
            'finance_invoice_status_update_after_credit_note_apply',
            'finance_payrate_send_to_keypay',
            'finance_payrate_create_increase_rate_type',
            'participant_with_less_fund_details',
            'participant_with_less_period_of_plan'
        ];
        $interValLimit = isset($extraParms['interval_minute']) && (int) $extraParms['interval_minute'] > 0 && (int) $extraParms['interval_minute'] <= 59 ? (int) $extraParms['interval_minute'] : 25;
        $methodCallName = isset($extraParms['method_call']) && !empty($extraParms['method_call']) ? $extraParms['method_call'] : '';
        $methodCallParms = isset($extraParms['method_params']) && !empty($extraParms['method_params']) && is_array($extraParms['method_params']) ? $extraParms['method_params'] : [];
        $startTime = DATE_TIME;
        $tableNameWithPreFix = TBL_PREFIX . 'cron_status';
        $tableName = 'cron_status';
        if (!empty($methodName) && in_array($methodName, $methodNameAllowed)) {
            $this->db->select('id');
            $this->db->from($tableNameWithPreFix);
            $this->db->where("last_date_time> NOW() - INTERVAL " . $interValLimit . " MINUTE", null, false);
            $this->db->where("status", 0);
            $this->db->where("method_name", $methodName);
            $result = $this->db->get();
            if ($result->num_rows() == 0) {
                $row = $this->Basic_model->insert_records($tableName, array('method_name' => $methodName, 'status' => 0, 'created_date' => $startTime));
                if (!empty($methodCallName) && method_exists($this, $methodCallName)) {
                    $methodCallParms = array_merge(['cron_id' => $row], ['extra' => $methodCallParms]);
                    $response = call_user_func_array(array($this, $methodCallName), $methodCallParms);
                } else {
                    $response = ['status' => false, 'error' => 'cron call method not exists.'];
                }
            } else {
                $response = ['status' => false, 'error' => 'cron process already run.'];
            }
        } else {
            $response = ['status' => false, 'error' => 'cron method not allowed.'];
        }
        return $response;
    }
}
