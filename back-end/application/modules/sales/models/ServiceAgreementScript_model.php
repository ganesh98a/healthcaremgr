<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for interacting with `tbl_service_agreement` table
 */
class ServiceAgreementScript_model extends Basic_Model
{

    /**
     * Fetch all duplicate line items
     */
    public function get_duplicate_line_item_from_finance($type=''){
        $sql = "SELECT id, line_item_number, COUNT(*) FROM tbl_finance_line_item where category_ref = '' GROUP BY line_item_number HAVING COUNT(*) > 1";
        $query = $this->db->query($sql);

        $opp_ary = $query->result();

        
        $line_item_number = [];
        $line_item_id = [];
        foreach($opp_ary as $key=>$row) {
            $item_id = $this->get_rows('finance_line_item AS fli', ['fli.line_item_number', 'fli.id'], ['fli.line_item_number' => $row->line_item_number , 'fli.category_ref' => '']);           
            $line_item_number[$row->line_item_number] = $item_id;
        }   
        
        if(!empty($line_item_number)){
            foreach($line_item_number as $key=>$val) {            
                $item_id = array();
                foreach($line_item_number[$key] as $item) {               
                    array_push($item_id, $item->id);            
                }
                $line_item_id[$key] = $item_id;
            }
        }        
        $duplicateList=[];
        
        if(!empty($line_item_id)){
            if($type=='service_agreement'){
                $duplicateList=$this->get_line_item_duplicate_for_service_agreement($line_item_id);
            }else if($type=='opportunity'){
                $duplicateList=$this->get_line_item_duplicate_opportunity($line_item_id);
            }
            
        }      
        return $duplicateList;
    }

    //get duplicate items for service agreement
    public function get_line_item_duplicate_for_service_agreement($line_item_id){        
        $serviceAgreementList = [];
        $duplicateList=[];
        $update_id_only = [];
        if(!empty($line_item_id)){
            foreach($line_item_id as $key=>$line_item) { 
                $service_sql = "SELECT service_agreement_id, line_item_id,COUNT(service_agreement_id) as service_agreement_count FROM 
                tbl_service_agreement_items where line_item_id in (".implode(',', $line_item).") and archive=0 GROUP BY service_agreement_id HAVING COUNT(*) > 1";
                $query = $this->db->query($service_sql);
                $result = $query->result_array();
                if(!empty($result)){
                    foreach($result as $val){
                        $val['line_item_id'] = $line_item;
                        $serviceAgreementList[] = $val;
                     }
                 }else{
                    $line_item_arr = explode(",",implode(',', $line_item));
                    $max_line_item_id = max($line_item_arr);
                    $service_sql = "SELECT id, service_agreement_id, line_item_id FROM tbl_service_agreement_items where line_item_id in (".$max_line_item_id.") and archive=0 ";
                    $query = $this->db->query($service_sql);
                    $update_result = $query->result();
                    foreach($update_result as $val) {
                        $val->original_line_item_id = min($line_item_arr);
                        $update_id_only[] = $val;
                   }
                 }
            }

            
            if(!empty($serviceAgreementList)){
                foreach($serviceAgreementList as $key=>$service) {
                    $line_item_arr = explode(",",implode(',', $service['line_item_id']));
                    $max_line_item_id = max($line_item_arr);
                    $archive_Data = $this->get_row('service_agreement_items AS sai', ['sai.line_item_id', 'sai.id','sai.service_agreement_id'], ['sai.archive'=>0, 'sai.line_item_id' => $max_line_item_id , 'sai.service_agreement_id' => $service['service_agreement_id']]);           
                    $archive_Data->original_line_item_id = min($line_item_arr);;
                    $duplicateList[] = $archive_Data;
                }
            }
      }
        
      
      return ["duplicateList" => $duplicateList, "update_id_only" => $update_id_only, "line_item_id" => $line_item_id];
    }

//get duplicate items for opportunity
    public function get_line_item_duplicate_opportunity($line_item_id){        
        $opportunityList = [];
        $duplicateList=[];
        $update_id_only = [];
        if(!empty($line_item_id)){
            foreach($line_item_id as $key=>$line_item) {            
                $service_sql = "SELECT opportunity_id, line_item_id,COUNT(opportunity_id) as service_agreement_count FROM 
                tbl_opportunity_items where line_item_id in (".implode(',', $line_item).") and archive=0 GROUP BY opportunity_id HAVING COUNT(*) > 1";
                 $query = $this->db->query($service_sql);
                 $result = $query->result_array();
                 if(!empty($result)){
                    foreach($result as $val){
                        $val['line_item_id'] = $line_item;
                        $opportunityList[] = $val;
                     }
                 }else{
                    $line_item_arr = explode(",",implode(',', $line_item));
                    $max_line_item_id = max($line_item_arr);
                    $service_sql = "SELECT id, opportunity_id, line_item_id FROM tbl_opportunity_items where line_item_id in (".$max_line_item_id.") and archive=0 ";
                    $query = $this->db->query($service_sql);
                    $update_result = $query->result();
                    foreach($update_result as $val) {
                        $val->original_line_item_id = min($line_item_arr);
                        $update_id_only[] = $val;
                   }
                 }
                 
            }
            
            if(!empty($opportunityList)){
                foreach($opportunityList as $key=>$service) {
                     $line_item_arr = explode(",",implode(',', $service['line_item_id']));
                     $max_line_item_id = max($line_item_arr);
                     $archive_Data = $this->get_row('opportunity_items AS oi', ['oi.line_item_id', 'oi.id','oi.opportunity_id'], ['oi.archive'=>0, 'oi.line_item_id' => $max_line_item_id , 'oi.opportunity_id' => $service['opportunity_id']]);           
                     $archive_Data->original_line_item_id = min($line_item_arr);
                     $duplicateList[] = $archive_Data;
                }
            }
        }        
      
        return ["duplicateList" => $duplicateList, "update_id_only" => $update_id_only, "line_item_id" => $line_item_id];
    }

    // Update duplicate line item once get the data for duplicate list
    public function update_line_item_duplicate($type){
        // get duplicate data
        $updatingData = [];

        $updatingData = $this->get_duplicate_line_item_from_finance($type);
        $updatingData = object_to_array($updatingData);
      
        if(!empty($updatingData["duplicateList"])){
        foreach($updatingData["duplicateList"] as $key=>$service) { 
                $where = ["id" => $service['id'], "line_item_id"=>$service['line_item_id']];
                $data = ["line_item_id" => $service['original_line_item_id'], "archive" => 1, "updated" => DATE_TIME];
                if($type=='service_agreement'){
                    $this->basic_model->update_records("service_agreement_items", $data, $where);
                }else if($type=='opportunity'){
                    $this->basic_model->update_records("opportunity_items", $data, $where);
                }               
            }
            
        }

        if(!empty($updatingData["update_id_only"])){
            foreach($updatingData["update_id_only"] as $key=>$service) { 
                if(!empty($service)){
                    $where = ["id" => $service['id'], "line_item_id"=>$service['line_item_id']];
                    $data = ["line_item_id" => $service['original_line_item_id'],"updated" => DATE_TIME];                 
                    if($type=='service_agreement'){
                        $this->basic_model->update_records("service_agreement_items", $data, $where);
                    }else if($type=='opportunity'){
                        $this->basic_model->update_records("opportunity_items", $data, $where);
                    }   
                }
                                     
            }
                
        }
            if(!empty($updatingData["line_item_id"])){
                foreach($updatingData["line_item_id"] as $key=>$line_item) { 
                    if(!empty($line_item)){
                        
                        $line_item_arr = explode(",",implode(',', $line_item));
                        $max_line_item_id = max($line_item_arr);                        
                        
                        $where = ["id" => $service['id'], "line_item_id"=>$max_line_item_id];
                        $data = ["line_item_id" => min($line_item_arr),"updated" => DATE_TIME];
                      
                        if($type=='service_agreement'){
                            $this->basic_model->update_records("service_agreement_items", $data, $where);
                        }else if($type=='opportunity'){
                            $this->basic_model->update_records("opportunity_items", $data, $where);
                        }   
                    }
                                         
                }
                    
            }

        return $updatingData;
    }
    // delete duplicate line item
    public function delete_duplicate_line_item_number(){
        $sql = "SELECT id, line_item_number, COUNT(*) FROM tbl_finance_line_item where category_ref = '' GROUP BY line_item_number HAVING COUNT(*) > 1";
        $query = $this->db->query($sql);
        $opp_ary = $query->result();
        
        $line_item_number = [];
        $line_item_id = [];
        foreach($opp_ary as $key=>$row) {
            $item_id = $this->get_rows('finance_line_item AS fli', ['fli.line_item_number', 'fli.id'], ['fli.line_item_number' => $row->line_item_number , 'fli.category_ref' => '']);           
            $line_item_number[$row->line_item_number] = $item_id;
        }   
        
        if(!empty($line_item_number)){
            foreach($line_item_number as $key=>$val) {            
                $item_id = array();
                foreach($line_item_number[$key] as $item) {               
                    array_push($item_id, $item->id);            
                }
                $line_item_id[$key] = $item_id;
            }
        }        
        $deleted_id=[];
        if(!empty($line_item_id)){
            foreach($line_item_id as $key=>$service) { 
                 $line_item_arr = explode(",",implode(',', $service));
                 $max_line_item_id = max($line_item_arr);
                 // delete records
                 $where = array('id' => $max_line_item_id);
                 $archive_Data =  $this->basic_model->delete_records('finance_line_item', $where);
                 
                 $deleted_id[] = array('id' => $max_line_item_id);
            }
            
        }   
        $finance_list = $this->get_rows('finance_line_item AS fli', ['fli.id'], ['fli.category_ref' => '', 'fli.start_date!=' => '', 'fli.end_date!=' => '' ]);           
        
        if(!empty($finance_list)){
            foreach($finance_list as $finance) { 
                $where = ["id" => $finance->id];
                $data = ["start_date" => NULL,"end_date" => NULL];
                $this->basic_model->update_records("finance_line_item", $data, $where);
            }
        }            

        return $deleted_id;
    }
}
