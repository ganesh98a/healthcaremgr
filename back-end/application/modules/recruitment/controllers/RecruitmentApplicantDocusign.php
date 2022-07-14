<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read \Recruitment_applicant_docusign_model $Recruitment_applicant_docusign_model
 * @package
 */
class RecruitmentApplicantDocusign extends MX_Controller
{
    use formCustomValidation;

    const  DRAFT_STATUS = 4;

    function __construct()
    {
        parent::__construct();
        $this->load->model('Recruitment_group_interview_model');
        $this->load->model('Recruitment_applicant_model');
        $this->load->model('Recruitment_applicant_docusign_model');
        $this->load->model('Recruitment_applicant_stages_model', 'stage_model');
        $this->load->library('form_validation');
        $this->load->library('UserName');
        $this->load->model('common/List_view_controls_model');
        $this->form_validation->CI = &$this;
        $this->loges->setLogType('recruitment_applicant');
        $this->load->model('document/Document_type_model');
        $this->load->model('../../admin/models/Notification_model');
        $this->load->library('Asynclibrary');
    }

    /*
     * For Docusign document list
     *
     * Return type json
     * - count
     * - data
     * - status
     */
    function get_docusign_document_list() {
        $reqData = request_handler('access_recruitment');
        // pr($reqData);
        if (!empty($reqData)) {
            $data = $reqData->data;

            $error_check_id = false;

            $applicant_id = '';
            if (isset($data->applicant_id) == true && empty($data->applicant_id) == false) {
                $applicant_id = $data->applicant_id;
            }

            if ($applicant_id == '' && $applicant_id == 'null') {
                $error_check_id = true;
            }

            // Check data is valid or not
            if ($error_check_id == false) {
                // Call model for get doucment list
                $result = $this->Recruitment_applicant_docusign_model->get_docusign_document_list($data, true,$reqData);
            } else {
                // If requested data is empty or null
                $result = ['status' => false, 'error' => 'Applicant Id is null'];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }
        echo json_encode($result);
        exit();
    }

     /**
     * fetching the list of job applications of an applicant for selection
     */
    function get_applicant_job_application_by_id() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = $reqData->data;
        if (!empty($reqData->applicant_id)) {
            $result = $this->Recruitment_applicant_model->get_applicant_job_application($reqData->applicant_id);
            $data = null;
            if($result) {
                foreach($result as $row) {
                    $data[] = ["label" => $row->id, "value" => $row->id];
                }
            }
            echo json_encode(['status' => true, 'data' => $data]);
        }
    }

    /**
     * fetching the emplyement contract type
     */
    function get_employment_contract_document_type() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = $reqData->data;
        $result = $this->Recruitment_applicant_docusign_model->get_document_type_employment_contract();
        $data = null;
        if($result) {
            foreach($result as $row) {
                $data[] = ["label" => $row->label, "value" => $row->value];
            }
        }
        echo json_encode(['status' => true, 'data' => $data]);
    }

    /**
     * when employment contract sending is requested from the applicant info
     * using the existing modal functionality by passing the applicant_id and application_id
     * without task_applicant_id to generate the PDF and send the DocuSign envelop
     */
    function generate_employment_contract_docusign() {
        $reqestData = request_handler('access_recruitment');
        $adminId = $reqestData->adminId;
        if (!empty($reqestData->data)) {
            $reqData = $reqestData->data;
            $this->load->model(['Recruitment_group_interview_model']);
            $filename = $reqData->applicant_id."_".$reqData->application_id."_unsigned.pdf";

            $resDocusign = $this->Recruitment_applicant_docusign_model->generate_docusign_contract($reqData->applicant_id, 0, ['type' => 'cabday_interview', 'file_name' => $filename, 'adminId' => $reqestData->adminId], $reqData->application_id, $reqData);

            if($resDocusign) {

                //Adding Notification
                $notification_data['title'] = $reqData->doc_type_name;
                $notification_data['shortdescription'] = $reqData->doc_type_name.': Please check the email sent to your mailbox';
                $notification_data['userId'] = $reqData->applicant_id;
                $notification_data['user_type'] = 5;
                $notification_data['status'] = 0;
                $notification_data['sender_type'] = 2;
                $notification_data['created'] = DATE_TIME;
                $notification_data['specific_admin_user'] = $adminId;
                $notification_data['entity_type'] = 9;
                $notification_data['entity_id'] = $reqData->application_id;

                $this->Notification_model->create_notification($notification_data);

                echo json_encode(['status' => true, 'msg' => "Successfully sent the employment contract"]);
            }
            else {
                echo json_encode(['status' => false, 'error' => "Error sending the employment contract"]);
            }
            exit();
        }
    }


    

    /**
     * fetching the emplyement contract email content
     */
    function get_email_content_employment_contract() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;

         // Get content from views
        $email_content =$this->load->view('docusign_template_request_sign', true);
        $email_content = trim($email_content);

        echo $email_content;
    }

    /**
     * when employment contract sending is requested from the applicant info
     * using the existing modal functionality by passing the applicant_id and application_id
     * without task_applicant_id to generate the PDF and send the DocuSign envelop for bulk applications
     */
    function generate_bulk_docusign_contract() {
        error_reporting(1);
        $this->load->library('session');
        $reqestData = request_handler('access_recruitment');

        if (!empty($reqestData->data)) {
            error_reporting(1);
            $reqData = $reqestData->data;
            $this->load->model(['Recruitment_group_interview_model', 'Recruitment_applicant_docusign_model']);
            $url = base_url()."recruitment/RecruitmentApplicantDocusign/bulk_docusign_employment_contract";
            $param = array('reqData' => $reqData,'reqestData' => $reqestData );
            $this->asynclibrary->do_in_background($url, $param);
            echo json_encode(['status' => true, 'msg' => "Successfully sent the employment contract"]);
            
            exit();
        }
    }


    function bulk_docusign_employment_contract() {
        $this->load->model(['Recruitment_group_interview_model', 'Recruitment_applicant_docusign_model']);

        $reqData = $this->input->post('reqData');
        $reqestData = $this->input->post('reqestData');
        $index = 0;
        foreach($reqestData['data']['applicants'] as $val) {
      
        $val = (object) $val;
        $reqObj = (object) $reqData;

        $filename = $val->applicant_id."_".$val->application_id."_unsigned.pdf";
        $resDocusign = $this->Recruitment_applicant_docusign_model->generate_docusign_contract($val->applicant_id, 0, ['type' => 'cabday_interview', 'file_name' => $filename, 'adminId' => $reqestData['adminId']], $val->application_id, $reqObj);

           if($resDocusign) {
                //Adding Notification
                $notification_data['title'] = 'Employment contract';
                $notification_data['shortdescription'] = 'Employment Contract: Please check the email sent to your mailbox';
                $notification_data['userId'] = $val->applicant_id;
                $notification_data['user_type'] = 5;
                $notification_data['status'] = 0;
                $notification_data['sender_type'] = 2;
                $notification_data['created'] = DATE_TIME;
                $notification_data['specific_admin_user'] = $reqestData['adminId'];
                $notification_data['entity_type'] = 9;
                $notification_data['entity_id'] = $val->application_id;

                $this->Notification_model->create_notification($notification_data);
            }
            $index ++;

            }
            exit();

    }
}



