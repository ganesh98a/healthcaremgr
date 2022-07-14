<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends MX_Controller{

    function __construct($config = 'rest') {
        
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Recruitment_device_model');
        $this->load->model('Basic_model');
        $this->load->helper('i_pad');
        $this->form_validation->CI = & $this;      
        auth_login_status_file_type();        
    }

   

    function upload_user_profile_img() {
        $applicant_id = $this->input->post('applicant_id'); 
        if (empty($_FILES)) {
            $response = array('status' => false, 'error' => 'Please provide file to upload.');
            echo json_encode($response);
            exit();
        }

        if (!empty($_FILES) && $_FILES['file']['error'] == 0) {
            $config['upload_path'] = APPLICANT_ATTACHMENT_UPLOAD_PATH.$applicant_id.'/';
            $config['input_name'] = 'file';
            $config['directory_name'] = 'profile';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size']='5000';

            /* do_upload IS DEFINE IN COMMON HELPER */
            $is_upload = $this->do_upload($config);
            if (isset($is_upload['error'])) {
                echo json_encode(array('status' => false, 'error' => strip_tags($is_upload['error'])));
                exit();
            } else {
                $this->load->model('Basic_model');
                 $record = $this->basic_model->update_records('recruitment_applicant', array('profile_image' => $is_upload['upload_data']['file_name']), array('id' => $applicant_id));
                 echo json_encode(array('status' => true, 'filename' => $is_upload['upload_data']['file_name']));
                exit();
            }
        }
    }

    function do_upload($config_ary) {
        $CI = & get_instance();
        $response = array();
        if (!empty($config_ary)) {
            $directory_path = $config_ary['upload_path'] . $config_ary['directory_name'];

            $config['upload_path'] = $directory_path;
            $config['allowed_types'] = isset($config_ary['allowed_types']) ? $config_ary['allowed_types'] : '';
            $config['max_size'] = isset($config_ary['max_size']) ? $config_ary['max_size'] : '';
            $config['max_width'] = isset($config_ary['max_width']) ? $config_ary['max_width'] : '';
            $config['max_height'] = isset($config_ary['max_height']) ? $config_ary['max_height'] : '';

            $this->create_directory($directory_path);

            $CI->load->library('upload', $config);

            if (!$CI->upload->do_upload($config_ary['input_name'])) {
                $response = array('error' => $CI->upload->display_errors());
            } else {
                $response = array('upload_data' => $CI->upload->data());
            }
        }
        return $response;
    }

    function create_directory($directoryName) {
        if (!is_dir($directoryName)) {
            mkdir($directoryName, 0777, true);
        }
    }

    
    function upload_applicant_documents(){
        
        $this->validate_documents();
        
        $applicant_id = $this->input->post('applicant_id'); 
        $doc_type_id = $this->input->post('doc_type_id');
        $applicant_doc_id = $this->input->post('applicant_doc_id');
        $doc_title = $this->input->post('doc_title');
        
        $applicant_stage=$this->get_applicant_currunt_stage($applicant_id);        
        if($applicant_stage==0){
             $response = ['status' => false, 'error' => 'Getting issue in currunt Applicant stage.'];
            echo json_encode($response);
            exit();
        }
        if (!empty($_FILES) && $_FILES['file']['error'] == 0) {
            $config['upload_path'] = APPLICANT_ATTACHMENT_UPLOAD_PATH;
            $config['input_name'] = 'file';
            $config['directory_name'] =$applicant_id;
            $config['allowed_types'] = 'jpg|jpeg|png|xlx|xls|doc|docx|pdf';
            $config['max_size']='5000';
               
            $is_upload = $this->do_upload($config);
            if (isset($is_upload['error'])) {
                echo json_encode(array('status' => false, 'error' => strip_tags($is_upload['error'])));
                exit();
            } else {
                $this->load->model('Basic_model');
                $data = ["applicant_id"=> $applicant_id,"attachment_title"=>$doc_title , "attachment"=>$is_upload['upload_data']['file_name'],
                "archive"=>0,"created"=>DATE_TIME,'uploaded_by_applicant'=>1,'created_by'=>$applicant_id, 'document_status'=>0,
                'is_main_stage_label'=>0,'doc_category'=>$doc_type_id,'stage'=>$applicant_stage];
                $result=$this->basic_model->insert_records('recruitment_applicant_stage_attachment',$data);

                if($result>0){                    
                    echo json_encode(array('status' => true, 'filename' => $is_upload['upload_data']['file_name'] ,'document_id'=>$result));
                    exit();
                }
                    echo json_encode(array('status' => false, 'error' =>'Something went wrong.'));
                    exit();               
            }
        }        
    }
    
    private function get_applicant_currunt_stage($applicant_id){
        $this->load->model('Basic_model');
        //public function get_row($table_name = '', $columns = array(), $id_array = array())         
        $where_column=['id'=>$applicant_id];
        $res_array=$this->basic_model->get_row('recruitment_applicant',['current_stage'],$where_column);
        if(!empty($res_array)){
            return $res_array->current_stage;
        }else{
            return 0;
        }
    }
    
    private function validate_documents(){
        $memberData = (object) $this->input->post();        
        
        if(empty($memberData->doc_type_id)){            
            $response = ['status' => false, 'error' => 'Please provide document type first.'];
            echo json_encode($response);
            exit();
        }
        if(empty($memberData->applicant_doc_id)){            
            $response = ['status' => false, 'error' => 'Please provide applicant document id.'];
            echo json_encode($response);
            exit();
        }
        if(empty($memberData->doc_title)){            
            $response = ['status' => false, 'error' => 'Please provide document title first.'];
            echo json_encode($response);
            exit();
        }
        if(empty($_FILES)) {
            $response = array('status' => false, 'error' => 'Please provide file to upload.');
            echo json_encode($response);
            exit();
        }
    }

}
