<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FinancePayrate extends MX_Controller 
{
    function __construct() 
    {
        parent::__construct();
        $this->load->model('Finance_pay_rate_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;       
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # HCM- 3485, adding all requests to backend in a log file
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }

    public function get_payrate_dropdown_value()
    {
        $reqData = request_handler('access_finance_payrate');
        $data = [];
        $response = $this->basic_model->get_record_where('finance_payrate_category', $column = array('name as label', 'id as value'), $where = array('archive' => 0));
        $data['payrateCategory'] = isset($response) && !empty($response) ? $response : array();

        $response = $this->basic_model->get_record_where('finance_payrate_type', $column = array('name as label', 'id as value'), $where = array('archive' => 0));
        $data['payrateType'] = isset($response) && !empty($response) ? $response : array();

        $response = $this->basic_model->get_record_where('classification_level', $column = array('level_name as label', 'id as value'), $where = array('archive' => 0,'status'=>1));
        $data['payrateClassificationLevel'] = isset($response) && !empty($response) ? $response : array();

        $response = $this->basic_model->get_record_where('classification_point', $column = array('point_name as label', 'id as value'), $where = array('archive' => 0,'status'=>1));
        $data['payrateClassificationPoint'] = isset($response) && !empty($response) ? $response : array();       

        $response = $this->basic_model->get_record_where('finance_addtitonal_paypoint_ratetype', $column = array('name as label', 'id as value'), $where = array('archive' => 0));
        if (!empty($response)) {
            foreach ($response as $val) {
                $disabled = false;
                if($val->value == 1)
                    $disabled = true;

                $paypoint_ratetype[] = array('label' => $val->label, 'value' => $val->value);
            }
        }
        
        $data['addtitonalPaypointRatetype'] = isset($paypoint_ratetype) && !empty($paypoint_ratetype) ? $paypoint_ratetype : array();

        echo json_encode(array('status' => true, 'data' => $data));
    }

    public function add_payrate() 
    {
        $reqData = request_handler('access_finance_payrate');
        if (!empty($reqData->data)) 
        {
            $admin_id = $reqData->adminId;
            $reqData =  $reqData->data;
            $data =(array) $reqData;

            $temp_rule = array(
                array('field' => 'category', 'label' => 'Payrate Category', 'rules' => 'required'),
                array('field' => 'start_date', 'label' => 'PayRate Start Date', 'rules' => 'required'),
                array('field' => 'end_date', 'label' => 'PayRate End Date', 'rules' => 'required'),
            );

            if(isset($data['isAllowance']) && $data['isAllowance'] ==1)
            {
                $a = array(
                    array('field' => 'allowance_name', 'label' => 'Allowance Name', 'rules' => 'required'),
                    array('field' => 'allowance_rate', 'label' => 'Allowance Rate', 'rules' => 'required'),
                    array('field' => 'allowance_rate_type', 'label' => 'Allowance Rate Type', 'rules' => 'required')
                );
            }
            else
            {
                $a = array(
                    array('field' => 'type', 'label' => 'Payrate Type', 'rules' => 'required'),
                    array('field' => 'level_number', 'label' => 'Level Number', 'rules' => 'required'),
                    array('field' => 'paypoint', 'label' => 'Paypoint', 'rules' => 'required'),
                    array('field' => 'payPoints[]', 'label' => 'payPoints', 'rules' => 'callback_check_pay_rate[' . json_encode($reqData->payPoints) . ']'),
                ); 
            }

            if(strtotime(DateFormate($reqData->start_date)) > strtotime(DateFormate($reqData->end_date)))
            {
                echo json_encode(array('status' => false, 'msg' => 'Start date can\'t be greater than end date.'));
                exit();
            }

            $if_rate_type = $this->check_unique_rate_type($data['payPoints']);           
            if(!$if_rate_type)
            {
                echo json_encode(array('status' => false, 'msg' => 'Please select distinct Rate type.'));
                exit();
            }

            $if_exists = $this->check_payrate_exists($reqData);
            if($if_exists)
            {
                echo json_encode(array('status' => false, 'msg' => 'Same payrate already available for given input.'));
                exit();
            }


            $validation_rules = array_merge($a,$temp_rule);
            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) 
            {
                if (isset($reqData->payrate_id) && $reqData->payrate_id > 0) {
                    $msg = 'Payrate Updated successfully.';
                    if(strtotime(DateFormate($reqData->end_date)) == strtotime(DATE_CURRENT)){
                        $return = array('status' => false, 'msg' => 'You can\'t edit this payrate because this will be inactive by today.');
                    }else{
                     $this->Finance_pay_rate_model->add_payrate($reqData);
                     $return = array('status' => true, 'msg' => $msg);
                 }        
             } else {
                $msg = 'Payrate added successfully.';    
                $this->Finance_pay_rate_model->add_payrate($reqData);    
                $return = array('status' => true, 'msg' => $msg);               
            }        
        } else {
            $errors = $this->form_validation->error_array();
            $return = array('status' => false, 'msg' => implode("\n", $errors));
        }
        echo json_encode($return);
    }
} 

public function check_unique_rate_type($data)
{
    $ary = (array)$data;
    $xx = array_column($ary, 'rate');
        #echo count($ary) .'============'. count(array_unique($xx));die;

    if(!empty($ary) && count($ary) !== count(array_unique($xx)))
    {
        return false; 
    }
    else
    {
        return true; 
    }
}

# 

public function check_pay_rate($paypnt_data) 
{
    if (!empty($paypnt_data)) {
        if (empty($paypnt_data->rate) || !is_numeric($paypnt_data->rate)) {
            $this->form_validation->set_message('check_pay_rate', 'Hourly rate can not be empty and should be a valid number');
            return false;
        } elseif ((string)$paypnt_data->increasedBy==''   ) {
            $this->form_validation->set_message('check_pay_rate', 'Increased by can not be empty and should be a valid number');
            return false;
        } elseif ( ((1 > $paypnt_data->dollarValue) || ($paypnt_data->dollarValue > 9999)) ) {
            $this->form_validation->set_message('check_pay_rate', 'Dollar value can not be empty and should be a valid number between 1 to 999');
            return false; 
        }
    } else {
        $this->form_validation->set_message('check_pay_rate', 'Please add Paypoints');
        return false;
    }
    return true;
}  

public function get_payrate_list()
{
    $reqData = $reqData1 = request_handler('access_finance_payrate');
    if (!empty($reqData->data)) {
        $reqData = json_decode($reqData->data);
        $result = $this->Finance_pay_rate_model->get_payrate_list($reqData);
        echo json_encode($result);
        exit();
    }
}

public function archive_payrate()
{
    $reqData = request_handler('access_finance_payrate');
    if (!empty($reqData->data)) 
    {
        $admin_id = $reqData->adminId;
        $reqData =  $reqData->data;
        $payrate_id = $reqData->id;
        $end_date = DATE_TIME;

        $row = $this->basic_model->get_row($table_name = 'finance_payrate', array('end_date'),$columns = array('id'=>$payrate_id));
        if(!empty($row))
        {
            $next_date = date('Y-m-d', strtotime( ' +1 day')); 
            if(strtotime($row->end_date) == strtotime($next_date) || strtotime($row->end_date) == strtotime(DATE_CURRENT) )
            {
                echo json_encode(array('status'=>false,'msg'=>'This Payrate will be automatically archived '));
                exit();
            }
        }

        $update_data['end_date'] = date('Y-m-d H:i:s', strtotime($end_date . ' +1 day')); 
        $update_data['key_pay_archive'] = 1; 
        $response = $this->basic_model->update_records('finance_payrate', $update_data, $where = array('id' => $payrate_id));
        if($response)
            echo json_encode(array('status'=>true));
        else
            echo json_encode(array('status'=>false));
    }  
}

public function get_payrate_data()
{
    $reqData = $reqData1 = request_handler('access_finance_payrate');
    if (!empty($reqData->data)) {
        $result = $this->Finance_pay_rate_model->get_payrate_data($reqData);
        echo json_encode($result);
    } 
}

public function check_payrate_exists($reqData)
{
    $row = [];
    if (!empty($reqData))
    {
        $start_date = isset($reqData->start_date)?date('Y-m-d',strtotime($reqData->start_date)):'';
        $end_date = isset($reqData->end_date)?date('Y-m-d',strtotime($reqData->end_date)):'';


        if(isset($reqData->isAllowance) && $reqData->isAllowance !=1){
            $pay_rate_data = array('category'=>isset($reqData->category)?$reqData->category:'',     
               'level_number'=>isset($reqData->level_number)?$reqData->level_number:'',
               'paypoint'=>isset($reqData->paypoint)?$reqData->paypoint:'',
           );
            $this->db->where($pay_rate_data);
        }else{
         $pay_rate_data = array(
            'name'=>isset($reqData->allowance_name)?$reqData->allowance_name:'',                
        );
         $this->db->or_where($pay_rate_data);
     }

     if($start_date && $end_date){
       $where = "
       (('" . $start_date . "' BETWEEN date(start_date) AND date(end_date))  or ('" . $end_date . "' BETWEEN date(start_date) AND date(end_date)) OR
       (date(start_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "')  or (date(end_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "')) ";
       $this->db->where($where);
   }

   if (isset($reqData->payrate_id) && $reqData->payrate_id > 0) {              
    $this->db->where('id!=',$reqData->payrate_id,null,false);
}        
$row = $this->basic_model->get_row($table_name = 'finance_payrate', $columns = array('id'), []);
}
return $row;
}



}

