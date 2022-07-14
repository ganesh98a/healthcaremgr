<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Finance_pay_rate_model extends CI_Model
{
    public function __construct()
    {
        // Call the CI_Model constructor
        parent::__construct();
    }

    public function add_payrate($reqData) 
    {
        $callKeyPayType =false;
        $callKeyPayPayRateId =0;
        if (!empty($reqData))
        {
            $pay_rate_data = array('category'=>isset($reqData->category)?$reqData->category:'',
                'type'=>isset($reqData->type)?$reqData->type:'',
                'level_number'=>isset($reqData->level_number)?$reqData->level_number:'',
                'paypoint'=>isset($reqData->paypoint)?$reqData->paypoint:'',
                'start_date'=>isset($reqData->start_date)?DateFormate($reqData->start_date):'',
                'end_date'=>isset($reqData->end_date)?DateFormate($reqData->end_date):'',
                'comments'=>isset($reqData->comments)?$reqData->comments:'',
                'status'=>isset($reqData->status)?$reqData->status:2,
                'created'=>DATE_TIME,
            );

            if(strtotime(DateFormate($reqData->start_date)) == strtotime(date('Y-m-d')))
                $status = 1;
            else if(strtotime(DateFormate($reqData->start_date)) > strtotime(date('Y-m-d')))
                $status = 2;
            else
                $status = 0;

            $pay_rate_data['status'] = $status; 

            if(isset($reqData->category) && $reqData->category ==5)
            {
                $pay_rate_data['name'] = $reqData->allowance_name;
            }
            else
            {
                $pay_rate_data['name'] = $this->generate_payrate_name($reqData);
            }

            if(isset($reqData->payrate_id) && $reqData->payrate_id > 0)
            {
                if(isset($reqData->category) && $reqData->category ==5)
                {
                    $pay_rate_data['name'] = $reqData->allowance_name;
                }

                $opertion_type = 'edit';
                $payrate_id = $reqData->payrate_id;
                $row = $this->basic_model->get_row('finance_payrate',array('status','end_date','key_pay_archive'),array('id'=>$payrate_id));
                
                $update_old_record = [];
                if(strtotime(DateFormate($row->end_date)) < strtotime(date('Y-m-d')))
                    $update_old_record['archive'] = 0;

                if(!empty($row))
                {
                    if(strtotime($reqData->start_date) > strtotime(date('Y-m-d')))
                    {
                        /*If payrate is in-active mode then update only*/
                        $this->basic_model->update_records('finance_payrate', $pay_rate_data, ['id' => $payrate_id]);
                    }else{
                        /*if payrate is active then set todays date as end date in edit payrate and create new payrate with tomarrow as start date 
                        *and end date is same as user provide or edit payrate date*/
                        $update_old_record['start_date'] = DateFormate($reqData->start_date);
                        $update_old_record['end_date'] = date('Y-m-d');
                        $update_old_record['key_pay_archive'] = 2; //marked archive on key pay
                        $old_payrate_id = $payrate_id;
                        $this->basic_model->update_records('finance_payrate', $update_old_record, ['id' => $payrate_id]);  
                        $opertion_type = 'insert';
                        $pay_rate_data['status'] = $row->status;
                        $pay_rate_data['parent_pay_rate'] = $old_payrate_id;
                        $pay_rate_data['start_date'] = date('Y-m-d',(strtotime ( '+1 day' , strtotime ( date('Y-m-d')) ) ));
                        $payrate_id = $this->basic_model->insert_records('finance_payrate', $pay_rate_data, $multiple = FALSE);
                        if(strtotime(DateFormate($reqData->start_date))==strtotime(date('Y-m-d')) &&  $row->key_pay_archive==0){
                            $callKeyPayType = 'add';
                            $callKeyPayPayRateId = $old_payrate_id;
                        }
                    }
                }
                else{
                    return FALSE;
                    #$this->basic_model->update_records('finance_payrate', $pay_rate_data, ['id' => $payrate_id]);              
                }
            }         
            else
            {
                $payrate_id = $this->basic_model->insert_records('finance_payrate', $pay_rate_data, $multiple = FALSE);
                if(strtotime(DateFormate($reqData->start_date))==strtotime(date('Y-m-d'))){
                    $callKeyPayType = 'add';
                    $callKeyPayPayRateId =$payrate_id;
                }
            }

            if(isset($reqData->category) && $reqData->category == 5)
            {
                $ins_ary = array('payrate_id'=>$payrate_id,
                    'dollar_value'=>isset($reqData->allowance_rate)?$reqData->allowance_rate:'',
                    'increased_by'=>0,
                    'rate_type'=>isset($reqData->allowance_rate_type)?$reqData->allowance_rate_type:'',
                    'created'=>DATE_TIME
                );

                if(isset($reqData->mode) && $reqData->mode=='edit' && $opertion_type == 'edit')
                    $this->basic_model->update_records('finance_payrate_paypoint', ['archive'=>1], ['payrate_id' => $payrate_id]);

                $this->basic_model->insert_records('finance_payrate_paypoint', $ins_ary);
            }
            else
            {
                if(!empty($reqData->payPoints))
                {
                    $ins_ary = [];
                    foreach ($reqData->payPoints as $key => $value)
                    {
                        $ins_ary[] = array('payrate_id'=>$payrate_id,
                            'rate_type'=>$value->rate,
                            'increased_by'=>$value->increasedBy,
                            'dollar_value'=>$value->dollarValue,
                            'created'=>DATE_TIME
                        );
                    }

                    if(!empty($ins_ary)){
                        if(isset($reqData->mode) && $reqData->mode=='edit' && $opertion_type == 'edit')
                            $this->basic_model->update_records('finance_payrate_paypoint', ['archive'=>1], ['payrate_id' => $payrate_id]);

                        $this->basic_model->insert_records('finance_payrate_paypoint', $ins_ary, $multiple = TRUE);
                    }
                }
            }

            if($callKeyPayType =='add' && !empty($callKeyPayPayRateId)){
                $this->add_payrate_in_keypay_by_payrate_id($callKeyPayPayRateId,$callKeyPayType);
            }

            if($payrate_id>0)
                return TRUE;
            else
                return FALSE;
        }
        return FALSE;
    }

    public function get_payrate_list($reqData)
    {        
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $tbl_payrate = TBL_PREFIX . 'finance_payrate';
        $available_column = array("id", "level_number","start_date", "end_date", "paypoint","status","archive","level_number","category","category_int","payrate_name");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = $tbl_payrate . '.id';
            $direction = 'DESC';
        }

        $select_column = array($tbl_payrate . ".id", $tbl_payrate . ".level_number", $tbl_payrate . ".start_date", $tbl_payrate . ".end_date", $tbl_payrate . ".paypoint",$tbl_payrate . ".status",$tbl_payrate . ".archive",$tbl_payrate . ".level_number","fpc.name as category",$tbl_payrate . ".category as category_int",$tbl_payrate . ".name as payrate_name");

        
        if (isset($filter->start_date) && $filter->start_date != '')
            $this->db->where("DATE(tbl_finance_payrate.start_date) >= '" . date('Y-m-d', strtotime($filter->start_date)) . "'");

        if(isset($filter->end_date) && $filter->end_date != '')
            $this->db->where("DATE(tbl_finance_payrate.start_date) <= '" . date('Y-m-d', strtotime($filter->end_date)) . "'");

        if (!empty($filter->srch_box) && !empty($filter->search_key)) {
            $search_key = $filter->search_key;

            
            if($search_key == 'rate_name'){

                /*$digits = explode(",", $filter->srch_box);
                $explode = filter_var_array($digits, FILTER_SANITIZE_NUMBER_INT);
                $explode = array_filter($explode);
                if(!empty($explode)){
                    $explode_0 = isset($explode[0]) && $explode[0]!=''?$explode[0]:0;
                    $explode_1 = isset($explode[1]) && $explode[1]!=''?$explode[1]:0;

                    $this->db->where("(tbl_finance_payrate.level_number=$explode_0 and tbl_finance_payrate.paypoint=$explode_1)",NULL,FALSE);
                }else{
                    $this->db->or_like($tbl_payrate . ".name ", $filter->srch_box);
                }*/
                $this->db->or_like($tbl_payrate . ".name ", $filter->srch_box);
            }
            else if($search_key == 'status' && strtolower($filter->srch_box) == 'active'){
                $this->db->where($tbl_payrate . ".start_date <", DATE_CURRENT);
                $this->db->where($tbl_payrate . ".end_date>=", DATE_CURRENT);
            }
            else if($search_key == 'status' && strtolower($filter->srch_box) == 'inactive'){
                $this->db->where($tbl_payrate . ".start_date>", DATE_CURRENT);
            }
            else if( ($search_key == 'status' && strtolower($filter->srch_box) == 'archive') || ($search_key == 'status' && strtolower($filter->srch_box) == 'archived') ){
                $this->db->where($tbl_payrate . ".end_date<", DATE_CURRENT);
            }
            else if( ($search_key == 'status' && ((strtolower($filter->srch_box) != 'archive') || (strtolower($filter->srch_box) != 'active') ||  (strtolower($filter->srch_box) != 'inactive') )  )){ 
               $this->db->or_like($tbl_payrate . ".status", $filter->srch_box);
            }
            else if($search_key == 'category'){
                $this->db->or_like("fpc.name", $filter->srch_box);
            }
            else if($search_key == 'type'){  
                $this->db->join('tbl_finance_payrate_type as fpt', 'fpt.id =  ' . $tbl_payrate . '.type AND fpt.archive=0', 'INNER');
                $this->db->or_like("fpt.name", $filter->srch_box);
            }
            else if($search_key == 'hourly_rate'){
             $this->db->join('tbl_finance_payrate_paypoint as fpp', 'fpp.payrate_id =  ' . $tbl_payrate . '.id AND fpp.rate_type=1 AND fpp.archive=0', 'INNER');
             $this->db->or_like("fpp.dollar_value", $filter->srch_box);                
             $select_column[] = 'fpp.dollar_value';
         }
         else if ($search_key == 'default') {
            $this->db->group_start();

            $src_columns = array($tbl_payrate . ".id","level_number", $tbl_payrate . ".paypoint","fpc.name as category",$tbl_payrate . ".name as payrate_name");

            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $this->db->or_like($serch_column[0], $filter->srch_box);
                } else {
                    $this->db->or_like($column_search, $filter->srch_box);
                }
            }
            $this->db->group_end();
        }
    }
        /*$queryHavingData = $this->db->get_compiled_select();
        #pr($queryHavingData);
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        if (!empty($queryHaving)) {
        $this->db->having($queryHaving);
        }
        */

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("case when $tbl_payrate.level_number!=0 THEN (SELECT level_name FROM tbl_classification_level as cl where cl.id=$tbl_payrate.level_number AND cl.archive=0) ELSE '' END as level_name", false);
        $this->db->select("case when $tbl_payrate.paypoint!=0 THEN (SELECT point_name FROM tbl_classification_point as cp where cp.id=$tbl_payrate.paypoint AND cp.archive=0) ELSE '' END as point_name", false);
        $this->db->select("case when $tbl_payrate.paypoint!=0 THEN (SELECT name FROM tbl_finance_payrate_type as fpt where fpt.id=$tbl_payrate.type AND fpt.archive=0) ELSE '' END as payrate_type", false);

        $this->db->from($tbl_payrate);
        $this->db->join('tbl_finance_payrate_category as fpc', 'fpc.id =  ' . $tbl_payrate . '.category AND fpc.archive=0', 'INNER');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $this->db->where(array($tbl_payrate.".archive"=>0));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query();
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();
        $data = [];

        if (!empty($dataResult)) {

            $payrateIds = array_column(obj_to_arr($dataResult), 'id');
            $payrateIds = empty($payrateIds) ? [0] : $payrateIds;
            $hourly_rate = $this->get_addtitonal_paypoint_ratetype($payrateIds);

            $color_class= '';
            foreach ($dataResult as $val) 
            {
                if($val->category_int == 5){
                    $val->ratename = isset($val->payrate_name)?$val->payrate_name:'';
                    $val->title = $val->category;
                }
                else{
                    $level_name =  $val->level_name??'';
                    $pay_point = $val->point_name??'';

                    $val->ratename = $level_name.' - '.$pay_point;
                    $val->title = $val->category.' - '.$val->payrate_type;
                }

                $next_date = date('Y-m-d', strtotime( ' +1 day')); 
                 $val->is_edit = 0;
                 $val->archive = 0;
                if( strtotime($val->start_date) == strtotime(date('Y-m-d')) || strtotime($val->start_date) < strtotime(date('Y-m-d')) &&  strtotime($val->end_date) >= strtotime(date('Y-m-d')) ){
                    $color_class = 'btn_color_avaiable short_buttons_01';
                    $status_title = 'Active';
                    $val->is_edit = 1;
                    $val->archive = 1;
                }
                else if( strtotime($val->start_date) > strtotime(date('Y-m-d'))){
                    $color_class = 'btn_color_unavailable short_buttons_01';
                    $status_title = 'Inactive';
                    $val->is_edit = 1;
                }
                else if( strtotime($val->end_date) < strtotime(date('Y-m-d'))){
                    $color_class = ' btn_color_archive short_buttons_01';
                    $status_title = 'Archive';
                }
                else{
                    $color_class = '';
                    $status_title = '';
                }

                $val->color_class = $color_class;
                $val->status_title = $status_title;

                $val->start_date = date('d/m/Y',strtotime($val->start_date));
                $val->end_date = date('d/m/Y',strtotime($val->end_date));

                $val->payrateDetail = isset($hourly_rate[$val->id]) ? $hourly_rate[$val->id] : [];
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count);
        return $return;
    }

    public function get_addtitonal_paypoint_ratetype($payrateIds) 
    {
        $this->db->select(['fpp.id','fpp.payrate_id','fpp.rate_type','fpp.increased_by','fpp.dollar_value']);
        $this->db->select("case when fpp.rate_type!=0 THEN (SELECT name FROM tbl_finance_addtitonal_paypoint_ratetype as fapr where fapr.id=fpp.rate_type AND fapr.archive=0) ELSE '' END as name", false);        
        $this->db->from('tbl_finance_payrate_paypoint as fpp');
        $this->db->join('tbl_finance_payrate as fp', 'fp.id = fpp.payrate_id AND fpp.archive=0', 'inner');
         #$this->db->join('tbl_finance_addtitonal_paypoint_ratetype as fapr', 'fapr.id = fpp.rate_type', 'inner');
        $this->db->where_in('fpp.payrate_id', $payrateIds);
        $this->db->order_by('fpp.id','ASC');
        $query = $this->db->get();

        $res = $query->result_array();
        $complete_ary = [];
        if (!empty($res)) {
            $temp_hold = [];
            foreach ($res as $key => $value) {
                $payrate_id = $value['payrate_id'];
                if (!in_array($payrate_id, $temp_hold)) {
                    $temp_hold[] = $payrate_id;
                }
                $temp['payrate_id'] = $payrate_id;
                $temp['rate_type'] = $value['rate_type'];
                $temp['increased_by'] = $value['increased_by'];
                $temp['dollar_value'] = $value['dollar_value'];
                $temp['name'] = $value['name'];
                $complete_ary[$payrate_id][] = $temp;
            }
        }
        return $complete_ary;
    }

    public function get_payrate_data($reqData)
    {  
        if(!empty($reqData))
        {
            $payrate_id = $reqData->data->id;
            $tbl_payrate = TBL_PREFIX . 'finance_payrate'; 
            $select_column = array($tbl_payrate . ".id", $tbl_payrate . ".status", $tbl_payrate . ".start_date", $tbl_payrate . ".end_date", $tbl_payrate . ".paypoint",$tbl_payrate . ".status",$tbl_payrate . ".archive",$tbl_payrate . ".level_number",$tbl_payrate . ".category",$tbl_payrate . ".type",$tbl_payrate . ".name",$tbl_payrate . ".comments","(select group_concat(rate_type) from tbl_finance_payrate_paypoint where payrate_id = $payrate_id AND archive=0  ) as selected_payrate_paypoint");

            $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
            $this->db->from($tbl_payrate);
            $this->db->where('id', $payrate_id);
            $this->db->group_start();
            $this->db->or_where('end_date > ', DATE_CURRENT);
            $this->db->or_where("'".DATE_CURRENT."' BETWEEN $tbl_payrate.start_date AND $tbl_payrate.end_date",NULL,false);
            $this->db->group_end();
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            
            $payrate_data = $query->row_array();
            #last_query();die;
            if(empty($payrate_data) || is_null($payrate_data))
            {
                $return = array('status' => false,'data' => []);
                return $return;
            }

            $main_aray = array(); 

            $this->db->select(['fpp.rate_type as rate','fpp.increased_by as increasedBy','fpp.dollar_value as dollarValue']);
            $this->db->from('tbl_finance_payrate_paypoint as fpp');
            $this->db->where('fpp.payrate_id', $payrate_id);
            $this->db->where('fpp.archive', 0);

            $this->db->order_by('fpp.rate_type','ASC');
            $query = $this->db->get();

            $res = $query->result_array();
            $main_aray['payPoints'] = !empty($res)?$res:[];

            $response = $this->basic_model->get_record_where('finance_addtitonal_paypoint_ratetype', $column = array('name as label', 'id as value'), $where = array('archive' => 0));
            if (!empty($response)) {
                foreach ($response as $val) {
                    $disabled = false;
                    if(in_array($val->value, explode(',', $payrate_data['selected_payrate_paypoint'])))
                        $disabled = true;
                    $paypoint_ratetype[] = array('label' => $val->label, 'value' => $val->value, 'disabled' => $disabled);
                }
            }

            $main_aray['addtitonalPaypointRatetype'] = isset($paypoint_ratetype) && !empty($paypoint_ratetype) ? $paypoint_ratetype : array();

            if( strtotime($payrate_data['start_date']) == strtotime(date('Y-m-d')) || strtotime($payrate_data['start_date']) < strtotime(date('Y-m-d')) &&  strtotime($payrate_data['end_date']) >= strtotime(date('Y-m-d')) ){
                $status_title = 'Active';
                $payrate_status = 1;
            }
            else if( strtotime($payrate_data['start_date']) > strtotime(date('Y-m-d'))){
                $status_title = 'Inactive';
                $payrate_status = 0;
            }
            else if( strtotime($payrate_data['end_date']) < strtotime(date('Y-m-d'))){
                $status_title = 'Archive';
                $payrate_status = 2;
            }
            else{
                $status_title = 'Inactives';
                $payrate_status = 0;
            }

            $name = $payrate_data['name']!=''?$payrate_data['name']:'';
            $main_aray['payrate_status'] = $payrate_status;
            $main_aray['start_date'] = $payrate_data['start_date']!=''?$payrate_data['start_date']:'';
            $main_aray['end_date'] = $payrate_data['end_date']!=''?$payrate_data['end_date']:'';
            $main_aray['paypoint'] = $payrate_data['paypoint']!=''?$payrate_data['paypoint']:'';
            $main_aray['level_number'] = $payrate_data['level_number']!=''?$payrate_data['level_number']:'';
            $main_aray['category'] = $payrate_data['category']!=''?$payrate_data['category']:'';
            $main_aray['category'] = $payrate_data['category']!=''?$payrate_data['category']:'';
            $main_aray['type'] = $payrate_data['type']!=''?$payrate_data['type']:'';
            $main_aray['isAllowance'] = ($payrate_data['category'] == 5)?TRUE:FALSE;
            $main_aray['status_title'] = $status_title;
            if($payrate_data['category'] == 5)
            {
                $main_aray['allowance_name'] = $name; 
                $main_aray['allowance_rate'] = isset($res[0]['dollarValue'])?$res[0]['dollarValue']:''; 
                $main_aray['allowance_rate_type'] = isset($res[0]['rate'])?$res[0]['rate']:''; 
            }
            $main_aray['page_title'] = 'Edit '.$name;
            $main_aray['comments'] = $payrate_data['comments']!=''?$payrate_data['comments']:'';
            $return = array('status' => true, 'data' => $main_aray);
            return $return;
        }
    }

    public function generate_payrate_name($reqData)
    {
        $name = '';
        if(!empty($reqData) && $reqData->category && count($reqData->payrateCategory) > 0 && $reqData->level_number && count($reqData->payrateClassificationLevel) > 0 && $reqData->paypoint && ($reqData->payrateClassificationPoint) > 0 && $reqData->payrateType && count($reqData->payrateType) > 0)
        {
            foreach ($reqData->payrateCategory as $valcat) {
                if($valcat->value == $reqData->category)
                    $name =  $valcat->label;
            }

            foreach ($reqData->payrateType as $valueType) {
                if($valueType->value == $reqData->type)
                    $name = $name.', '.$valueType->label;
            }
            
            foreach ($reqData->payrateClassificationLevel as $valueLabel) {
                if($valueLabel->value == $reqData->level_number)
                    $name = $name.', '.$valueLabel->label;
            }

            foreach ($reqData->payrateClassificationPoint as $valuePp) {
                if($valuePp->value == $reqData->paypoint)
                    $name = $name.', '.$valuePp->label;
            }
        }
        return $name;
    }

    public function add_payrate_in_keypay_by_payrate_id(int $payrateId,$callType='add'){
        $params = array('company_id' => KEYPAY_DEFAULT_COMPANY_ID);
        $this->load->library('KeyPayFetch', $params);
        $res = $this->get_pay_rate_details_for_key_pay_by_payrate_id($payrateId);
        if(!empty($res)){
            $payrateTemplate =[];
            $responseData=[];
            $keyPayData = $this->keypayfetch->getKeyPayAuth();
            $i=1;
            foreach ($res as $key=>$row){
                if($i%3===0){
                    // sleep(1);
                }
                $uniqid = uniqid();
                $payrateTemplate['name']=$row['name'];
                $payCategoryDetails=[
                    'name'=>$row['name'].' RATE'.$key,
                    'rate_type_name'=>$row['rate_type_unit'],
                    'externalId'=>$uniqid
                ];
                $responseData[$row['payrate_paypoint_id']]['response']=$this->keypayfetch->create_pay_category($payCategoryDetails,$keyPayData);
                $responseData[$row['payrate_paypoint_id']]['detials']=$row;
                $responseData[$row['payrate_paypoint_id']]['external_id']=$uniqid;
                $responseData[$row['payrate_paypoint_id']]['is_primary_category']=$key==0?1:0;
                $i++;
            }
            
            if(!empty($responseData)){
                $insData=[];
                foreach($responseData as $key=>$row){
                    $rowResponse = $row['response'];
                    $external_id = $row['external_id'];
                    $rowDetails = $row['detials'];
                    $isPrimaryCategory = $row['is_primary_category'];
                    if($rowResponse['status'] && isset($rowResponse['data']) &&!empty($rowResponse['data'])){
                        $insData[]=[
                            'payrate_id' =>$payrateId,
                            'keypay_pay_category_id' =>$rowResponse['data']['id'],
                            'external_id' =>$external_id,
                            'rate_type' =>$rowDetails['rate_type'],
                            'is_primary_category' =>$isPrimaryCategory,
                            'created' =>DATE_TIME,
                            'archive'=>0
                        ];
                    }
                }
                if(!empty($insData)){
                    $this->basic_model->insert_update_batch('insert','finance_payrate_keypay_payratecategory',$insData);
                    $payCategories=[];
                    foreach ($res as $key=>$row){
                        $payCategories[]=[
                            'calculatedRate'=>(float) $row['dollar_value'],
                            'payCategoryId'=>$responseData[$row['payrate_paypoint_id']]['response']['data']['id'],
                            'standardWeeklyHours'=>KEYPAY_PAYRATE_STANDARD_WEEKLY_HOUR,
                            'userSuppliedRate'=>(float) $row['dollar_value'],
                        ];
                        //$responseData[$row['payrate_paypoint_id']]=$this->keypayfetch->create_pay_category($payCategoryDetails,$keyPayData);
                    }
        
                    $payrateTemplate['payCategories']=$payCategories;
                    $payrateTemplateUniqid = uniqid();
                    $payrateTemplate['externalId']=$payrateTemplateUniqid;
                    $payrateTemplate['primaryPayCategoryId']=$responseData[$res[0]['payrate_paypoint_id']]['response']['data']['id']??'';
                    $responseTemp = $this->keypayfetch->create_pay_rate_template($payrateTemplate,$keyPayData);
                    if($responseTemp['status']){
                        $insTempData = [
                            'payrate_id'=>$payrateId,
                            'keypay_payratetemplate_id'=> $responseTemp['data']['id'],
                            'external_id'=>$payrateTemplateUniqid,
                            'created' =>DATE_TIME,
                            'archive'=>0
                        ];
                        $this->basic_model->insert_records('finance_payrate_keypay_payratetemplate',$insTempData);
                    }
                }
            }

        }
    }

    public function get_pay_rate_details_for_key_pay_by_payrate_id(int $payrateId){
        $this->db->select(['name']);
        $this->db->from("tbl_finance_addtitonal_paypoint_ratetype sub_fapr");
        $this->db->where("sub_fapr.id=fpp.rate_type",NULL,FALSE);
        $this->db->limit(1);
        $subQuery = $this->db->get_compiled_select();
        $this->db->select(['fp.id as payrate_id','fpp.id as payrate_paypoint_id','fp.name','fp.start_date','fp.end_date','fp.status',' fpp.rate_type','fpp.dollar_value']);
        $this->db->select("case WHEN fpp.rate_type>0 THEN (".$subQuery.")ELSE ''END as rate_type_unit", false);
        $this->db->from("tbl_finance_payrate as fp");
        $this->db->join("tbl_finance_payrate_paypoint as fpp","fpp.payrate_id=fp.id AND fpp.archive=0","inner");
        $this->db->where("fp.id",$payrateId);
        $this->db->order_by("fpp.id","ASC");
        $query = $this->db->get();
        return $query->num_rows()>0 ? $query->result_array() :[];
    }

    public function get_payrate_id_for_data_send_to_keypay(int $limit=1,$extraParms=[]){
        $startDate = $extraParms['start_data'] ?? DATE_CURRENT;
        $this->db->select(['fp.id','fp.parent_pay_rate','fp.start_date','fp.end_date','fp.start_date','fp.key_pay_archive']);
        $this->db->from("tbl_finance_payrate fp");
        $this->db->where("fp.archive",0);
        $this->db->where("fp.start_date",$startDate);
        $this->db->where("fp.end_date>=fp.start_date",NULL,FALSE);
        $this->db->where_in("fp.key_pay_archive",[0,2]);
        $this->db->where("NOT EXISTS (SELECT id FROM tbl_finance_payrate_keypay_payratecategory as fpkpc where fpkpc.payrate_id=fp.id and fpkpc.archive=0)",NULL,FALSE);
        if(!empty($limit)){
            $this->db->limit($limit);
        }
        $query = $this->db->get();
        return $query->num_rows()>0 ? $query->result_array():[];
        /* select fp.* from tbl_finance_payrate as fp
        where fp.start_date='2020-01-30' 
        and fp.archive=0  
        and fp.key_pay_archive in (0,2)
        and fp.end_date>=fp.start_date 
        and not exists ( select id from tbl_finance_payrate_keypay_payratecategory as fpkpc where fpkpc.payrate_id=fp.id and fpkpc.archive=0) */
    }

    public function get_payrate_category_details_by_payrate_id(int $payrateId){
        $this->db->select(['fpkpc.id','fpkpc.keypay_pay_category_id']);
        $this->db->from('tbl_finance_payrate_keypay_payratecategory fpkpc');
        $this->db->where('archive',0);
        $this->db->where('keypay_archive_date',0);
        $this->db->where('payrate_id',$payrateId);
        $query = $this->db->get();
        return $query->num_rows()>0 ? $query->result_array():[];

    }

    public function get_payratetemplate_details_by_payrate_id(int $payrateId){
        $this->db->select(['fpkpt.id','fpkpt.keypay_payratetemplate_id']);
        $this->db->from('tbl_finance_payrate_keypay_payratetemplate fpkpt');
        $this->db->where('archive',0);
        $this->db->where('keypay_archive_date',0);
        $this->db->where('payrate_id',$payrateId);
        $query = $this->db->get();
        return $query->num_rows()>0 ? $query->result_array():[];

    }
    public function delete_payrate_in_keypay_by_payrate_id($payrateId,$callType='add'){
        $params = array('company_id' => KEYPAY_DEFAULT_COMPANY_ID);
        $this->load->library('KeyPayFetch', $params);
        $payCategoryDetails = $this->get_payrate_category_details_by_payrate_id($payrateId);
        $payRateTemplateDetails = $this->get_payratetemplate_details_by_payrate_id($payrateId);
       
    
        $payCategoryIds = !empty($payCategoryDetails) ? array_column($payCategoryDetails,'keypay_pay_category_id') :[];
        $payRateTemplateIds = !empty($payRateTemplateDetails) ? array_column($payRateTemplateDetails,'keypay_payratetemplate_id') :[];
        //pr([$payCategoryIds,$payCategoryDetails]);
        if(empty($payCategoryIds) && empty($payRateTemplateIds)){
            return ['status' => false,'error'=>'payrate category and payrate templeate not created in keypay for given payrate id or already delated on hcm application.'];
        }
        $keyPayData = $this->keypayfetch->getKeyPayAuth();
        if(!empty($payRateTemplateIds)){
            $updateTemplateData=[];
            $responsePayRateTemplateIds = $this->keypayfetch->delete_payrate_template($payRateTemplateIds);
            foreach($payRateTemplateDetails as $row){
                $temp = ['id'=>$row['id'],'keypay_archive_date'=>DATE_TIME];
                $responseData = $responsePayRateTemplateIds['data'][$row['keypay_payratetemplate_id']] ?? [];
                if(!empty($responseData) && isset($responseData['message'])){
                    $temp['keypay_template_archive']=2;
                }else{
                    $temp['keypay_template_archive']=1;
                }
                $updateTemplateData[] = $temp;
            }
            if(!empty($updateTemplateData)){
                $this->basic_model->insert_update_batch('update','finance_payrate_keypay_payratetemplate',$updateTemplateData,'id');
            }
        }
        $updateCategoryData=[];
        if(!empty($payCategoryIds)){
            $responsePayCategoryIds = $this->keypayfetch->delete_pay_category($payCategoryIds);
            foreach($payCategoryDetails as $row){
                $temp = ['id'=>$row['id'],'keypay_archive_date'=>DATE_TIME];
                $responseData = $responsePayCategoryIds['data'][$row['keypay_pay_category_id']] ?? [];
                if(!empty($responseData) && isset($responseData['message'])){
                    $temp['keypay_category_archive']=2;
                }else{
                    $temp['keypay_category_archive']=1;
                }
                $updateCategoryData[] = $temp;
            }
            if(!empty($updateCategoryData)){
                $this->basic_model->insert_update_batch('update','finance_payrate_keypay_payratecategory',$updateCategoryData,'id');
            }
        }
    }

    public function get_payrate_for_create_new_rate_type(int $limit=1,$extraParms=[]){
        $endDate = $extraParms['end_date'] ?? DATE_CURRENT;
        $this->db->select(['fp.id','fp.parent_pay_rate','fp.start_date','fp.end_date','fp.start_date','fp.key_pay_archive',"DATEDIFF(fp.end_date,fp.start_date) as duration_days"]);
        $this->db->from("tbl_finance_payrate fp");
        $this->db->where(["fp.archive"=>0,"created_increased_payrate"=>0]);
        $this->db->where("fp.end_date",date(DB_DATE_FORMAT,strtotime($endDate. ' - 1 days')));
        $this->db->where("fp.end_date>=fp.start_date",NULL,FALSE);
        $this->db->where_in("fp.key_pay_archive",[0]);
        $this->db->where("EXISTS (SELECT id FROM tbl_finance_payrate_paypoint as fpp where fpp.payrate_id=fp.id and fpp.archive=0 and fpp.increased_by>0)",NULL,FALSE);
        if(!empty($limit)){
            $this->db->limit($limit);
        }
        $query = $this->db->get();
        #last_query(1);

       $data= $query->num_rows()>0 ? $query->result_array():[];
       if(!empty($data)){
            $payRateIds = array_column($data,'id');
            $this->db->select(['payrate_id','id','rate_type','increased_by','dollar_value','archive']);
            $this->db->from('tbl_finance_payrate_paypoint');
            $this->db->where_in('payrate_id',$payRateIds);
            $this->db->where('archive',0);
            $query = $this->db->get();
            $rows = $query->num_rows()>0 ? $query->result_array():[];
            $rows = merge_multidimensional_array_values_by_key_in_array_format($rows,'payrate_id');
            return ['data'=>$data,'rows_data'=>$rows];
       }
       return $data;
        /* select fp.* from tbl_finance_payrate as fp
        where fp.start_date='2020-01-30' 
        and fp.archive=0  
        and fp.key_pay_archive in (0,2)
        and fp.end_date>=fp.start_date 
        and not exists ( select id from tbl_finance_payrate_keypay_payratecategory as fpkpc where fpkpc.payrate_id=fp.id and fpkpc.archive=0) */
    }

    public function calculate_increase_payrate_value(float $amount,float $increase){
        $amountIncrease = ($amount*$increase)/100;
        $increaseAmountValue = $amount + $amountIncrease;
        return custom_round($increaseAmountValue);
    }

    public function create_new_payrate_by_payrate_ids($ids=[]){
        $ids = !empty($ids) ? $ids : 0;
        $ids = is_array($ids) ? $ids : [$ids];
        $created_date = DATE_TIME;
        $this->db->select(["DATE_ADD(end_date, INTERVAL 1 DAY) as start_date" ,"DATE_ADD(DATE_ADD(end_date, INTERVAL 1 DAY)", "INTERVAL DATEDIFF(end_date,start_date) DAY) as end_date", "category","type","level_number","paypoint","name","status","comments","'0' as created_increased_payrate","id as increased_payrate_id_parent","archive", "'0' as key_pay_archive","'0' as parent_pay_rate", "'".$created_date."' as 'created'" , "now() as 'updated'"],false);
        $this->db->from('tbl_finance_payrate');
        $this ->db->where_in('id',$ids);
        $subQuery = $this->db->get_compiled_select();
        $this->db->query("INSERT INTO `tbl_finance_payrate` (`start_date`, `end_date`,`category`, `type`, `level_number`, `paypoint`, `name`,  `status`, `comments`, `created_increased_payrate`, `increased_payrate_id_parent`, `archive`, `key_pay_archive`, `parent_pay_rate`, `created`, `updated`) ".$subQuery);

        $this->db->select(['id','increased_payrate_id_parent']);
        $this->db->from('tbl_finance_payrate');
        $this->db->where_in('increased_payrate_id_parent',$ids);
        $this->db->where('created',$created_date);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() :[];
    }
}
