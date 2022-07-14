<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MemberAction extends MX_Controller {

    use formCustomValidation;

    function __construct() {
        parent::__construct();
        $this->load->model('Member_action_model');
        $this->load->model('Basic_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
        $this->loges->setLogType('member');
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

    public function index() {
        request_handler('access_member');
        die('Invalid request');
    }

    public function get_member_testimonial() {
        request_handler('access_member');
        $result = $this->Member_action_model->get_testimonial();
        echo json_encode(array('status' => true, 'data' => $result));
    }

    public function contact_history_export() {
        $request = request_handler('access_member');
        $csv_data = $request->data->userSelectedList;
       #pr($csv_data);
        $this->load->library("excel");
        $object = new PHPExcel();
        if (!empty($csv_data)) {

            $object->setActiveSheetIndex(0);
            $object->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'Contact Type');
            $object->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'Time');
            $object->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Note');

            $var_row = 2;
            foreach ($csv_data as $one_row) {
                $object->getActiveSheet()->SetCellValue('A' . $var_row, $one_row->contact_type_str);
                $object->getActiveSheet()->SetCellValue('B' . $var_row, $one_row->time);
                $object->getActiveSheet()->SetCellValue('C' . $var_row, $one_row->note);
                $var_row++;
            }

            $object->setActiveSheetIndex()
            ->getStyle('A1:D1')
            ->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'C0C0C0:')
                    )
                )
            );

            $object->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
            #$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');            
            $object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
            $filename = time() . '_contact_history' . '.csv';
            /* header('Content-Type: application/vnd.ms-excel');
              header('Content-Disposition: attachment;filename='.$filename);
              $object_writer->save('php://output'); */
              $object_writer->save(ARCHIEVE_DIR . '/' . $filename);
              echo json_encode(array('status' => true, 'csv_url' => $filename));
              exit();
          } else {
            echo json_encode(array('status' => false, 'error' => 'No record to export'));
            exit();
        }
    }

    public function member_shift_export() {
        $request = request_handler('access_member');
        $csv_data = $request->data->userSelectedList;
        $active_tab = $request->data->active_tab;
        #pr($csv_data);
        $this->load->library("excel");
        $object = new PHPExcel();
        if (!empty($csv_data)) {

            $object->setActiveSheetIndex(0);
            $object->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'Shift Date');
            $object->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'Member name');
            $object->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Start');
            $object->getActiveSheet()->setCellValueByColumnAndRow(3, 1, 'End');
            $object->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'Duration');
            $object->getActiveSheet()->setCellValueByColumnAndRow(5, 1, 'Status');

            $var_row = 2;
            foreach ($csv_data as $one_row) {
                $object->getActiveSheet()->SetCellValue('A' . $var_row, $one_row->shift_date);
                $object->getActiveSheet()->SetCellValue('B' . $var_row, ucfirst($one_row->memberName));
                $object->getActiveSheet()->SetCellValue('C' . $var_row, $one_row->start_time);
                $object->getActiveSheet()->SetCellValue('D' . $var_row, $one_row->end_time);
                $object->getActiveSheet()->SetCellValue('E' . $var_row, $one_row->duration);
                $object->getActiveSheet()->SetCellValue('F' . $var_row, $one_row->status);
                $var_row++;
            }

            $object->setActiveSheetIndex()
            ->getStyle('A1:D1')
            ->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'C0C0C0:')
                    )
                )
            );

            $object->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
            $object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
            if ($active_tab == 6)
                $filename = time() . '_completed_shift' . '.csv';
            else
                $filename = time() . '_cancel_shift' . '.csv';
            $object_writer->save(ARCHIEVE_DIR . '/' . $filename);
            echo json_encode(array('status' => true, 'csv_url' => $filename));
            exit();
        }
        else {
            echo json_encode(array('status' => false, 'error' => 'No record to export'));
            exit();
        }
    }

    public function operate_contact_history() {
        $request = request_handler('access_member');
        $responseAry = $request->data;
        $responseData = (array) $request->data;
        #pr($request);
        if (!empty($responseData)) {
            $validation_rules = array(
                array('field' => 'contact_type', 'label' => 'Contact type', 'rules' => 'required'),
                array('field' => 'time', 'label' => 'Time', 'rules' => 'required'),
                array('field' => 'note', 'label' => 'Note', 'rules' => 'required'),
            );

            $this->form_validation->set_data($responseData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $insert_ary = array('memberId' => $responseAry->memberId,
                    'contact_type' => isset($responseAry->contact_type) ? $responseAry->contact_type : '',
                    'time' => isset($responseAry->time) ? DateFormate($responseAry->time) : '',
                    'note' => isset($responseAry->note) ? $responseAry->note : '',
                    'created_by' => isset($request->adminId) ? $request->adminId : 0,
                    'created' => DATE_TIME,
                );
                //die('jj');
                $rows = $this->basic_model->insert_records('member_contact_history', $insert_ary, $multiple = FALSE);

                if (!empty($rows)) {
                    $return = array('status' => TRUE,'msg'=>'Add successfully.');
                    echo json_encode($return);
                    exit();
                } else {
                    $return = array('status' => false,'error'=>'Error in add contact history.');
                    echo json_encode($return);
                    exit();
                }                
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($return);
        }
    }

    public function archive_history()
    {
        $reqData = request_handler('access_member');
        if (!empty($reqData->data)) 
        {
            $reqData =  $reqData->data;
            $id = $reqData->id;
            $update_data['archive'] = 1; $update_data['updated'] = DATE_TIME;
            $response = $this->basic_model->update_records('member_contact_history', $update_data, $where = array('id' => $id));
            if($response)
                echo json_encode(array('status'=>true));
            else
                echo json_encode(array('status'=>false));
        }  
    }

    public function get_workarea_list()
    {
     $rows = $this->basic_model->get_record_where('recruitment_applicant_work_area', $column = array('work_area as label', 'id as value'), $where = array('archived' => '0'));
     $data = isset($rows) && !empty($rows) ? $rows : array();
     echo json_encode(array('status'=>true,'data'=>$data));
 }

 public function get_all_skills()
 {
    $reqData = request_handler('access_member');
    if (!empty($reqData->data)) 
    {
        $memberId = $reqData->data->memberId;
        $memberAssistanceType = isset($reqData->data->type)?$reqData->data->type:'assistance';
        $data = $this->Member_action_model->get_all_skills($memberId,$memberAssistanceType);
        if($data){
            echo json_encode(array('status'=>true,'data'=>$data));
            exit();
        }else{
            echo json_encode(array('status'=>FALSE));
            exit();
        }
    }
}

public function save_member_skill()
{
    $reqData = request_handler('access_member');
    if(!empty($reqData))
    {
        $member_id = $reqData->data->memberId;
        $type = $reqData->data->type;
        $skill = [];

        if (!empty($reqData->data->skillList)) {
            foreach ($reqData->data->skillList as $val) {
                if (!empty($val->checked)) {
                    $x = ['skillId' => $val->id, "member_id" => $member_id, "created" => DATE_TIME];

                    if ($val->key_name === 'other') {
                        $x['other_title'] = $val->other_title;
                    } else {
                        $x['other_title'] = '';
                    }
                    $skill[] = $x;
                }
            }
        }            
        if (!empty($skill)) {
            #$this->basic_model->delete_records("member_skill", ["member_id" => $member_id]);
            $sql = "UPDATE tbl_member_skill as ms INNER JOIN tbl_participant_genral as pg ON ms.skillId= pg.id AND pg.status=1 AND pg.type=? SET ms.archive = 1 WHERE ms.archive = 0 AND ms.member_id = ? ";
            $this->db->query($sql, array($type,$member_id));
            $this->basic_model->insert_records('member_skill', $skill, true);
        }
        echo json_encode(array('status'=>TRUE,'msg'=>'Updated successfully.'));
    }
    else{
        echo json_encode(array('status'=>FALSE));
    }
}

public function update_bonus_training() 
{
    $request = request_handler('access_member');
    if (!empty($request->data->member_data)) 
    {
        $responseAry = $request->data->member_data;
        $responseData = (array) $request->data->member_data;

        $validation_rules = array(
            array('field' => 'title', 'label' => 'Title', 'rules' => 'required'),
            array('field' => 'date', 'label' => 'Date', 'rules' => 'required'),
            array('field' => 'hours', 'label' => 'Hour', 'rules' => 'required'),
            array('field' => 'note', 'label' => 'Note', 'rules' => 'required')
        );

        $this->form_validation->set_data($responseData);
        $this->form_validation->set_rules($validation_rules);

        if ($this->form_validation->run()) {

            $insert_ary = array('memberId' => $responseAry->memberId,
                'title' => $responseAry->title ?? '',
                'date' => DateFormate($responseAry->date) ?? '',
                'hour' => isset($responseAry->hours) ? DateFormate($responseAry->hours,'H:i') : '',
                'note' => isset($responseAry->note) ? $responseAry->note : '',
                'created_by' => isset($request->adminId) ? $request->adminId : 0,
                'created' => DATE_TIME,
            );
            #pr($insert_ary);
            $rows = $this->basic_model->insert_records('member_bonus_training', $insert_ary);

            if (!empty($rows)) {
                $return = array('status' => TRUE,'msg'=>'Add successfully.');
                echo json_encode($return);
                exit();
            } else {
                $return = array('status' => false,'error'=>'Error in add bonus training.');
                echo json_encode($return);
                exit();
            }                
        } else {
            $errors = $this->form_validation->error_array();
            $return = array('status' => false, 'error' => implode(', ', $errors));
            echo json_encode($return);
            exit();
        }
    }
}

public function get_bonus_training_detail() {
    $reqData = $reqData1 = request_handler('access_member');
    if (!empty($reqData->data)) {
        $reqData = json_decode($reqData->data);
        $result = $this->Member_action_model->get_bonus_training_detail($reqData);
        echo json_encode($result);
    }else{
        echo json_encode(array('data' => [],'status'=>true));
    }
}

public function get_bonus_point()
{
    $reqData = $reqData1 = request_handler('access_member');
    if (!empty($reqData->data)) {
        $reqData = $reqData->data;
        $result = $this->Member_action_model->get_bonus_point($reqData);
        echo json_encode(array('status'=>true,'data'=>$result));
    }else{
        echo json_encode(array('data' => [],'status'=>true));
    }
}

}
