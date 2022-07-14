<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Service_booking_model extends Basic_Model  {

    function __construct() {
        parent::__construct();
        $this->load->model('../../sales/models/ServiceAgreement_model');
    }

    
    /**
     * used to get service agreement from tbl_reference
     */
   function get_service_agreement_type_list(){
      $this->db->select(array(
            'ref.display_name as value',
            'ref.display_name as label'
        ));
        $this->db->from('tbl_references as ref');
        $this->db->join('tbl_reference_data_type as reft', 'reft.id = ref.type', 'inner');
        $this->db->where('reft.title', 'Service Agreement Type');
        $this->db->where('ref.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        if($query->result()) {
            return $query->result();            
        }
    }
   
     /**
     * used to create service booking
     * @param userdate ,adminID
     */
    function create_update_service_booking($reqData, $adminId){
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $id_to_update= $data['isUpdate']?$data['id_to_update']:'';
            if(!empty($this->check_service_booking_number_already_exist($data['service_booking_number'],$id_to_update)))
            {
                return ["status" => false, "error" => "Service booking number already exists"];
             }
           
            $validation_rules = [
                array('field' => 'service_agreement_type', 'label' => 'Service Booking Number', 'rules' => 'required|max_length[40]', 'errors' => ['max_length' => "%s field cannot exceed 40 characters."]),
                array('field' => 'service_booking_creator', 'label' => 'Service Booking Creator', 'rules' => 'required|numeric'),
                array('field'=>'funding','label'=>'Funding','rules' => 'required'),
                array('field' => 'status', 'label' => 'Status', 'rules' => 'required'),
                array('field' => 'date_submitted', 'label' => 'Date Submitted', 'rules' => 'required'),
                array('field'=>'service_booking_number','label'=>'Service Booking Number','rules' => 'required'),
                array('field'=>'related_service_agreement_id','label'=>'Related Service Agreement','rules' => 'required'),
                array('field'=>'service_agreement_contract','label'=>'Service Agreement Contract','rules' => 'required')
            ];
            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);
            if ($this->form_validation->run()) {
                $insData = [
                    'service_booking_number' => $data['service_booking_number'],
                    'service_booking_creator' => $data['service_booking_creator'],
                    'funding' => $data['funding'],
                    'service_agreement_type' => $data['service_agreement_type'],
                    'status' => $data['status'],
                    'date_submitted' => $data['date_submitted'],
                    'related_service_agreement_id'=>$data['related_service_agreement_id'],
                    'service_agreement_attachment_id'=>$data['service_agreement_contract'],
                    'is_received_signed_service_booking'=>$data['is_received_signed_service_booking']
                    
                ];
                $insData["created_at"] = DATE_TIME;
                $insData["created_by"] =$adminId;
               
                $ServiceBookingId='';
                if(!empty($id_to_update))
                {
                    $where = ["id" => $id_to_update,'archive'=> 0];
                    $ServiceBookingId = $this->basic_model->update_records('service_booking',  $insData, $where);
                }else
                {
                    $ServiceBookingId = $this->basic_model->insert_records('service_booking', $insData);
                }
              

                if (!empty($ServiceBookingId)) {
                    $action=$data['is_portal_managed']?'update':'create';
                        $payment_data=[
                            "managed_type" => 1,
                            'service_agreement_id'=> $data['related_service_agreement_id'],
                            "service_booking_creator"=>$data['service_booking_creator']];
                   
                    
                    $this->ServiceAgreement_model->save_sa_payment($payment_data,$adminId,$action);
                    
                    $service_agreement_id = $data['related_service_agreement_id'];
                    
                    # Async API used to update the shift service booking warning .
                    $this->load->library('Asynclibrary');
                    $url = base_url()."schedule/NdisErrorFix/update_shift_ndis_warning";
                    $param = array('service_agreement_id' => $service_agreement_id, 'update_service_booking' => true, 'adminId' => $adminId);
                    $param['requestData'] = $param;
                    $this->asynclibrary->do_in_background($url, $param);

                    if(!empty($id_to_update))
                    {
                        return $response = ['status' => true, 'msg' => 'Service Booking has been updated successfully.'];
                    }else
                    {
                        return $response = ['status' => true, 'msg' => 'Service Booking has been created successfully.'];
                    }
                   
                } else {
                    return $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                }
            } else {
                $errors = $this->form_validation->error_array();
                return $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }
    }
     /**
     * used to check service booking number already exist in db
     */
    function check_service_booking_number_already_exist($service_booking_number,$sb_id='') {
        $this->db->select("id");
        $this->db->from("tbl_service_booking as s");
        $this->db->where("service_booking_number", trim($service_booking_number));
        $this->db->where('s.archive', 0);
        if ($sb_id > 0) {
            $this->db->where("id != ", $sb_id);
        }
        return $this->db->get()->row();
    }
    /**
     * used to find funding total grouped by service_agreement_type
     */
    function get_funding_sum_by_service_agreement_type($reqData, $adminId){
        $data = $reqData->data;
        $service_agreement_id=$data->{'service_agreement_id'};
        $this->db->select("SUM(funding) AS total");
        $this->db->select("service_agreement_type");
        $this->db->from("tbl_service_booking as s");
        $this->db->where('s.archive', 0);
        $this->db->where("s.status != ",'inactive');
        $this->db->where('s.related_service_agreement_id',  $service_agreement_id);
        $this->db->group_by('service_agreement_type');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        if($query->result()) {
            return $query->result();            
        }
        return [];
    }
    /**
     * used to get service booking existing in db
     */
    function get_service_booking_list($related_service_agreement_id){
        $columns=['id','service_booking_number','service_agreement_type','funding','DATE_FORMAT(date_submitted,"%d/%m/%Y") AS date_submitted','status','related_service_agreement_id','is_received_signed_service_booking'];
        $this->db->select($columns);
        $this->db->select("(
            CASE WHEN s.is_received_signed_service_booking = 1  THEN 'Yes'
                 WHEN s.is_received_signed_service_booking = 0 THEN 'No'
                 END) as is_received_signed_service_booking");
        $this->db->from("tbl_service_booking as s");
        $this->db->where('s.archive', 0);
        $this->db->where('related_service_agreement_id',$related_service_agreement_id);
        $this->db->limit(6);
        $this->db->order_by('id','DESC');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        if($query->result()) {
            return $query->result();            
        }else{
            return [];
        }

    }

    /**
     * used to delete existing service booking 
     */
    function delete_service_booking($data,$adminId){
        $where = ["id" => $data->{'id'}];
        $update = ["archive" => 1,"updated_at" => DATE_TIME,"updated_by"=>$adminId];
         $this->basic_model->update_records("service_booking", $update, $where);
         return ["status" => true, "msg" => "Archived successfully"];
    }

     /**
     * used to get existing single service booking by id
     */
    function get_service_booking_by_id( $reqData){
        $data = $reqData->data;
        $service_booking_id = $data->{'service_booking_id'};
        $columns=['id','service_booking_number','service_agreement_type',
        'funding','date_submitted',
        'status','related_service_agreement_id',
        'service_booking_creator','is_received_signed_service_booking', 'service_agreement_attachment_id'];
        $this->db->select($columns);
        $this->db->from("tbl_service_booking as s");
        $this->db->where('s.archive', 0);
        $this->db->where('id',$service_booking_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        if($query->result()) {
            return $query->result();            
        }else{
            return [];
        }
    }
    
    /**
     * Get service booking by service id with active status and signed 
     */
    function get_service_booking_with_status_by_id($service_agreement_id, $start_date, $docusign_id){

        $columns=[
            'id',
            'service_booking_number',
            'service_agreement_type',
            'funding',
            'DATE_FORMAT(date_submitted,"%d/%m/%Y") AS date_submitted',
            'status',
            'related_service_agreement_id',
            'is_received_signed_service_booking',
            'is_received_signed_service_booking as is_received_signed',
            'date_submitted',
            'status'
        ];

        $this->db->select($columns);
        $this->db->select("(
            CASE WHEN s.is_received_signed_service_booking = 1  THEN 'Yes'
                 WHEN s.is_received_signed_service_booking = 0 THEN 'No'
                 END) as is_received_signed_service_booking");
        $this->db->from("tbl_service_booking as s");
        $this->db->where('s.archive', 0);
        $this->db->where('s.service_agreement_type', 'NDIS');
        // $this->db->where('s.is_received_signed_service_booking', 1);
        $this->db->where('related_service_agreement_id', $service_agreement_id);
        $this->db->where('service_agreement_attachment_id', $docusign_id);
        // $this->db->where("(s.date_submitted < '{$start_date}')");
            $this->db->limit(1);
        $this->db->order_by(' (CASE 
            WHEN s.updated_at IS NOT NULL THEN s.updated_at 
            WHEN s.updated_at IS NULL THEN s.created_at
            END )
         DESC');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        if($query->result_array()) {
            return $query->result_array();            
        }else{
            return [];
        }
    }

}
