<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model class for interacting with `tbl_service_agreement` table
 */
class ServiceAgreementContract_model extends CI_Model
{
    /**
     * Load dynamic data for contract
     * @param {int} $service_agreement_id
     * @param {array} $serviceAgreementContract
     * @param {int} $service_agreement_attachment_id
     * @return array
     */
    public function get_dynamic_data_for_contract ($service_agreement_id, $serviceAgreementContract, $service_agreement_attachment_id) {
        $this->load->model('ServiceAgreement_model');
        // get all line items associate with service agreement
        $SALineItems = $this->ServiceAgreement_model->get_sa_contract_line_items($service_agreement_id);
        // get all line items associate with service agreement additional fund
        $SAAdditionalFund = $this->ServiceAgreement_model->get_sa_additional_fund($service_agreement_id); 
        // get all line items associate with service agreement
        $SAGoals = $this->ServiceAgreement_model->get_sa_contract_goals($service_agreement_id);
        $SAPayments = $this->ServiceAgreement_model->get_sa_contract_payment($service_agreement_id);

        // To person detail
        $to = [];
        $to['id'] = $serviceAgreementContract['to'];
        $to['name'] = $serviceAgreementContract['to_name'];
        $to['email'] = $serviceAgreementContract['to_email'];

        $data['to'] = $to;

        // signed by as recipient details
        $recipient = [];
        $recipient['id'] = $serviceAgreementContract['signed_by'];
        $recipient['name'] = $serviceAgreementContract['recipient_name'];
        $recipient['email'] = $serviceAgreementContract['recipient_email'];
        $recipient['phone'] = $serviceAgreementContract['recipient_phone'];
        $recipient['date_of_birth'] = $serviceAgreementContract['date_of_birth'];
        $recipient['my_ndis_number'] = $serviceAgreementContract['my_ndis_number'];
        $recipient['address'] = $serviceAgreementContract['recipient_address'];

        $data['recipient'] = $recipient;

        // account details
        $account = [];
        $account['account_id'] = $serviceAgreementContract['account_id'];
        $account['account_type'] = $serviceAgreementContract['account_type'];
        $account['account_type_name'] = $serviceAgreementContract['account_type_name'];
        $account['name'] = $serviceAgreementContract['account_name'];
        $account['email'] = $serviceAgreementContract['account_email'];
        $account['phone'] = $serviceAgreementContract['account_phone'];
        $account['my_ndis_number'] = $serviceAgreementContract['account_my_ndis_number'];

        $data['account'] = $account;

        // get decision maker contact
        $opportunity_id = $serviceAgreementContract['opportunity_id'];
        $person_id = $serviceAgreementContract['to'];
        $decisionMakerContact = $this->ServiceAgreement_model->get_decision_maker_contact_by_id($opportunity_id, $person_id);
        $decision_maker = [];
        if ($decisionMakerContact == true) {
            $decision_maker['id'] = $serviceAgreementContract['to'];
            $decision_maker['name'] = $serviceAgreementContract['recipient_name'];
            $decision_maker['email'] = $serviceAgreementContract['recipient_email'];
            $decision_maker['phone'] = $serviceAgreementContract['recipient_phone'];
            $decision_maker['date_of_birth'] = $serviceAgreementContract['date_of_birth'];
            $decision_maker['my_ndis_number'] = $serviceAgreementContract['my_ndis_number'];
        }
        $data['decision_maker'] = $decision_maker;
        // service agreement
        $service_agreement = [];
        $service_agreement['service_agreement_id'] = $serviceAgreementContract['service_agreement_id'];
        $service_agreement['opportunity_id'] = $serviceAgreementContract['opportunity_id'];
        $service_agreement['contract_start_date'] = $serviceAgreementContract['contract_start_date'];
        $service_agreement['contract_end_date'] = $serviceAgreementContract['contract_end_date'];
        $service_agreement['plan_start_date'] = $serviceAgreementContract['plan_start_date'];
        $service_agreement['plan_end_date'] = $serviceAgreementContract['plan_end_date'];
        $service_agreement['additional_services'] = $serviceAgreementContract['additional_services'];
        $service_agreement['additional_services_custom'] = $serviceAgreementContract['additional_services_custom'];
        
        $data['service_agreement'] = $service_agreement;

        // get contact is participant or not
        $person_id = $serviceAgreementContract['account_id'];
        $participant = [];
        $participantContact = false;
        if ($serviceAgreementContract['account_type'] == 1) {
            $participantContact = $this->ServiceAgreement_model->get_participant_contact_by_id( $person_id);
        }
        if ($participantContact == true) {
            $participant['id'] = $serviceAgreementContract['account_id'];
            $participant['name'] = $serviceAgreementContract['account_name'];
            $participant['email'] = $serviceAgreementContract['account_email'];
            $participant['phone'] = $serviceAgreementContract['account_phone'];
            $participant['date_of_birth'] = $serviceAgreementContract['account_date_of_birth'];
            $participant['my_ndis_number'] = $serviceAgreementContract['account_my_ndis_number'];
        }
        $data['participant'] = $participant;

        $data['line_items'] = $SALineItems;
        $data['goals'] = $SAGoals;
        $data['payments'] = $SAPayments;
        $data['additional_funds'] = $SAAdditionalFund;
        return $data;
    }

    /**
     * Generate PDF Template
     * @param {array} $data
     * @param {int} $service_agreement_attachment_id
     * @param {str} $attachment_type
     */
    function genreatePdfTemplate($data, $service_agreement_attachment_id, $attachment_type) {
        // set error reporting 0 to avoid warning and deprecated msg
        error_reporting(0);
        // Load library file
        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load();
        // set margin type
        $pdf->setAutoTopMargin='pad';
        // get cover page header with background image
        $data['type']='header';
        $header = $this->load->view('private_travel_agreemnet_v1',$data,true);
        // get footer image
        $data['type']='footer';
        $footer = $this->load->view('private_travel_agreemnet_v1',$data,true);
        // get cover page content
        $data['type']='content_1';
        $content_1 = $this->load->view('private_travel_agreemnet_v1',$data,true);
        // get second page header alone logo
        $data['type']='content_2_header';
        $content_2_header = $this->load->view('private_travel_agreemnet_v1',$data,true);
        // get second page footer
        $data['type']='footer_2_content';
        $footer_2_content = $this->load->view('private_travel_agreemnet_v1',$data,true);
        // get second page content
        $data['type']='content_2';
        $content_2 = $this->load->view('private_travel_agreemnet_v1',$data,true);
        // get page style css
        $styleContent = $this->load->view('service_agreemnet_v1_style',[],true);

        // set header & footer line
        $pdf->defaultfooterline = 0;
        $pdf->defaultheaderline = 0;
        // set header
        $pdf->SetHeader($header);
        $pdf->SetFooter($footer);
        // set page layout
        $pdf->AddPage('P','','','','',0,0,0,0,0,0);
        // write page content
        $pdf->WriteHTML($styleContent);
        $pdf->WriteHTML($content_1);
        $pdf->SetHeader($content_2_header);
        $pdf->SetFooter($footer_2_content);
        // add additional page with layout        
        $pdf->AddPage('P','','','','',10,10,0,20,5,0);
        // write page content
        $pdf->WriteHTML($content_2);
        // service agreement file path create if not exist
        $fileParDir = FCPATH . SERVICE_AGREEMENT_CONTRACT_PATH;
        if (!is_dir($fileParDir)) {
            mkdir($fileParDir, 0755);
        }
        // create folder with service agreement attachment id
        $fileDir = $fileParDir . $service_agreement_attachment_id;
        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0755);
        }
        // file name
        $filename = $attachment_type . ' ' . 'Contract unsigned';
        $filenameWithExtension = $filename.'.pdf';
        $pdfFilePath =  $fileDir . '/' . $filenameWithExtension;
        // write file into the folder
        $page_count = $pdf->page;

        $service_agreement_pdf = $pdf->Output($pdfFilePath, 'F');

        return [ "page_count" => $page_count, "pdfFilePath" => $pdfFilePath, $filenameWithExtension ];
    }

    /*
     * Get service agreement contract details by id
     * @param {int} service_agreement_attachment_id
     * return array
     */
    public function get_sa_details($data)
    {
        $return = [];
        $service_agreement_id = $data['service_agreement_id'];
        $signed_by = $data['signed_by'];

        // service agreement details
        $saa_col_select = [
            "sa.id AS service_agreement_id",
            "sa.contract_start_date",
            "sa.contract_end_date",
            "sa.plan_start_date",
            "sa.plan_end_date",
            "sa.additional_services",
            "sa.additional_services_custom",
            "sa.opportunity_id"
        ];
        $where_col = [
            "sa.id" => $service_agreement_id
        ];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $saa_col_select)), false);
        $this->db->from(TBL_PREFIX . 'service_agreement as sa');
        $this->db->where($where_col);
        $query = $this->db->get();
        $account_details = $query->num_rows() > 0 ? $query->row_array() : [];

        // contract type
        if (isset($data['type']) == true && $data['type'] == 1) {
            $account_details['contract_type'] = 'Consent';
        } else {
            $account_details['contract_type'] = 'Service Agreement';
        }
        $account_details['signed_by'] = $data['signed_by'];
        $account_details['account_id'] = $data['account_id'];
        $account_details['account_type'] = $data['account_type'];
        // account_type
        $account_details['account_type_name'] = '';
        $account_details['account_name'] = '';
        $account_details['account_date_of_birth'] = '';
        $account_details['account_my_ndis_number'] = '';
        $account_details['account_email'] = '';
        $account_details['account_phone'] = '';

        if (isset($data['account_type']) == true && $data['account_type'] == 1) {
            $account_details['account_type_name'] = 'Person';
            // account name 
            $saa_col_select = [
                "person.id",
                "CONCAT_WS(' ', person.firstname, person.lastname) AS account_name",
                "person.date_of_birth AS account_date_of_birth",
                "person.ndis_number as account_my_ndis_number",
                "pe.email AS account_email",
                "pp.phone AS account_phone"
            ];
            $where_col = [
                "person.id" => $data['account_id']
            ];
            $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $saa_col_select)), false);
            $this->db->from(TBL_PREFIX . 'person AS person');
            $this->db->join(TBL_PREFIX . 'person_email AS pe', 'pe.person_id = person.id', 'LEFT');
            $this->db->join(TBL_PREFIX . 'person_phone as pp', 'pp.person_id = person.id', 'LEFT');
            $this->db->where($where_col);
            $query = $this->db->get();
            $account_person = $query->num_rows() > 0 ? $query->row_array() : [];
            if (isset($account_person) == true && isset($account_person['account_name']) == true) {
                $account_details['account_name'] = $account_person['account_name'];
            }
            if (isset($account_person) == true && isset($account_person['account_date_of_birth']) == true) {
                $account_details['account_date_of_birth'] = $account_person['account_date_of_birth'];
            }
            if (isset($account_person) == true && isset($account_person['account_my_ndis_number']) == true) {
                $account_details['account_my_ndis_number'] = $account_person['account_my_ndis_number'];
            }
            if (isset($account_person) == true && isset($account_person['account_email']) == true) {
                $account_details['account_email'] = $account_person['account_email'];
            }
            if (isset($account_person) == true && isset($account_person['account_phone']) == true) {
                $account_details['account_phone'] = $account_person['account_phone'];
            }
        } else {
            $account_details['account_type_name'] = 'Organization';
            // account name 
            $saa_col_select = [
                "org.id",
                "org.name AS account_name",
            ];
            $where_col = [
                "org.id" => $data['account_id']
            ];
            $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $saa_col_select)), false);
            $this->db->from(TBL_PREFIX . 'organisation AS org');
            $this->db->where($where_col);
            $query = $this->db->get();
            $account_name = $query->num_rows() > 0 ? $query->row_array() : [];
            if (isset($account_name) == true && isset($account_name['account_name']) == true) {
                $account_details['account_name'] = $account_name['account_name'];
            }
        }
        $return = $account_details;

        // signer details
        $saa_col_select = [
            "CONCAT_WS(' ', p.firstname, p.lastname) AS recipient_name",
            '(
                SELECT
                    email 
                FROM tbl_person_email
                WHERE primary_email = 1 AND archive = 0 AND person_id = p.id
            ) as recipient_email',
            '(
                SELECT
                    phone 
                FROM tbl_person_phone
                WHERE primary_phone = 1 AND archive = 0 AND person_id = p.id limit 1
            ) as recipient_phone',
            "(
                SELECT
                    concat(pt.street,', ',pt.suburb,' ',(select s.name from tbl_state as s where s.id = pt.state),' ',pt.postcode) as address
                FROM tbl_person_address as pt
                WHERE primary_address = 1 AND archive = 0 AND person_id = p.id
            ) as recipient_address",
            "p.ndis_number as my_ndis_number",
            "p.date_of_birth",
        ];
        $where_col = [
            "p.id" => $signed_by
        ];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $saa_col_select)), false);
        $this->db->from(TBL_PREFIX . 'person as p');
        $this->db->where($where_col);
        $query = $this->db->get();
        $personDetails = $query->num_rows() > 0 ? $query->row_array() : [];

        $return = array_merge($return, $personDetails);
        return $return;
    }

    /*
     * Generate service agreement contract for preview
     * return array
     */
    public function preview_ndis_document($data, $template, $template_style) {
        $serviceAgreementContract = $this->get_sa_details($data);
        // pr($serviceAgreementContract);
        // return status with false if service agreement not exist
        if (isset($serviceAgreementContract) == false && !isset($serviceAgreementContract['id']) ==false) {
            return [ "status" => false, "error" => "Service Agreement contract not exist" ];
        }
        // gather details
        $service_agreement_id = $serviceAgreementContract['service_agreement_id'];
        // get all line items associate with service agreement
        $SALineItems = $this->ServiceAgreement_model->get_sa_contract_line_items($service_agreement_id);
        // get all line items associate with service agreement additional fund
        $SAAdditionalFund = $this->ServiceAgreement_model->get_sa_additional_fund($service_agreement_id);      
        // get all line items associate with service agreement
        $SAGoals = $this->ServiceAgreement_model->get_sa_contract_goals($service_agreement_id);
        $SAPayments = $this->ServiceAgreement_model->get_sa_contract_payment($service_agreement_id);

        // signed by as recipient details
        $recipient = [];
        $recipient['id'] = $serviceAgreementContract['signed_by'];
        $recipient['name'] = $serviceAgreementContract['recipient_name'];
        $recipient['email'] = $serviceAgreementContract['recipient_email'];
        $recipient['phone'] = $serviceAgreementContract['recipient_phone'];
        $recipient['date_of_birth'] = $serviceAgreementContract['date_of_birth'];
        $recipient['my_ndis_number'] = $serviceAgreementContract['my_ndis_number'];
        $recipient['address'] = $serviceAgreementContract['recipient_address'];

        $data['recipient'] = $recipient;

        // account details
        $account = [];
        $account['account_id'] = $serviceAgreementContract['account_id'];
        $account['account_type'] = $serviceAgreementContract['account_type'];
        $account['account_type_name'] = $serviceAgreementContract['account_type_name'];
        $account['name'] = $serviceAgreementContract['account_name'];
        $account['email'] = $serviceAgreementContract['account_email'];
        $account['phone'] = $serviceAgreementContract['account_phone'];
        $account['my_ndis_number'] = $serviceAgreementContract['account_my_ndis_number'];

        $data['account'] = $account;

        // Set data for doucment 
        $data['data']['name'] = $recipient['name'];
        $data['data']['to'] = $account['name'];

        // get decision maker contact
        $opportunity_id = $serviceAgreementContract['opportunity_id'];
        $person_id = $serviceAgreementContract['signed_by'];
        $decisionMakerContact = $this->ServiceAgreement_model->get_decision_maker_contact_by_id($opportunity_id, $person_id);
        $decision_maker = [];
        if ($decisionMakerContact == true) {
            $decision_maker['id'] = $serviceAgreementContract['signed_by'];
            $decision_maker['name'] = $serviceAgreementContract['recipient_name'];
            $decision_maker['email'] = $serviceAgreementContract['recipient_email'];
            $decision_maker['phone'] = $serviceAgreementContract['recipient_phone'];
            $decision_maker['date_of_birth'] = $serviceAgreementContract['date_of_birth'];
            $decision_maker['my_ndis_number'] = $serviceAgreementContract['my_ndis_number'];
        }
        $data['decision_maker'] = $decision_maker;
        // service agreement
        $service_agreement = [];
        $service_agreement['service_agreement_id'] = $serviceAgreementContract['service_agreement_id'];
        $service_agreement['opportunity_id'] = $serviceAgreementContract['opportunity_id'];
        $service_agreement['contract_start_date'] = $serviceAgreementContract['contract_start_date'];
        $service_agreement['contract_end_date'] = $serviceAgreementContract['contract_end_date'];
        $service_agreement['plan_start_date'] = $serviceAgreementContract['plan_start_date'];
        $service_agreement['plan_end_date'] = $serviceAgreementContract['plan_end_date'];
        $service_agreement['additional_services'] = $serviceAgreementContract['additional_services'];
        $service_agreement['additional_services_custom'] = $serviceAgreementContract['additional_services_custom'];
        
        $data['service_agreement'] = $service_agreement;

        // get contact is participant or not
        $person_id = $serviceAgreementContract['account_id'];
        $participant = [];
        $participantContact = false;
        if ($serviceAgreementContract['account_type'] == 1) {
            $participantContact = $this->ServiceAgreement_model->get_participant_contact_by_id( $person_id);
        }
        if ($participantContact == true) {
            $participant['id'] = $serviceAgreementContract['account_id'];
            $participant['name'] = $serviceAgreementContract['account_name'];
            $participant['email'] = $serviceAgreementContract['account_email'];
            $participant['phone'] = $serviceAgreementContract['account_phone'];
            $participant['date_of_birth'] = $serviceAgreementContract['account_date_of_birth'];
            $participant['my_ndis_number'] = $serviceAgreementContract['account_my_ndis_number'];
        }
        $data['participant'] = $participant;

        $data['line_items'] = $SALineItems;
        $data['goals'] = $SAGoals;
        $data['payments'] = $SAPayments;
        $data['additional_funds'] = $SAAdditionalFund;
        
        $data['generated_date'] = date('d/m/Y');
        $attachment_type = $serviceAgreementContract['contract_type'];
        
        // set error reporting 0 to avoid warning and deprecated msg
        error_reporting(0);
        // Load library file
        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load();
        // set margin type
        $pdf->setAutoTopMargin='pad';
        // get cover page header with background image
        $data['type']='header';
        $header = $this->load->view($template,$data,true);
        // get footer image
        $data['type']='footer';
        $footer = $this->load->view($template,$data,true);
        // get cover page content
        $data['type']='content_1';
        $content_1 = $this->load->view($template,$data,true);
        // get second page header alone logo
        $data['type']='content_2_header';
        $content_2_header = $this->load->view($template,$data,true);
        // get second page footer
        $data['type']='footer_2_content';
        $footer_2_content = $this->load->view($template,$data,true);
        // get second page content
        $data['type']='content_2';
        $content_2 = $this->load->view($template,$data,true);
        // get page style css
        $styleContent = $this->load->view($template_style,[],true);

        // set header & footer line
        $pdf->defaultfooterline = 0;
        $pdf->defaultheaderline = 0;
        // set header
        $pdf->SetHeader($header);
        $pdf->SetFooter($footer);
        // set page layout
        $pdf->AddPage('P','','','','',0,0,0,0,0,0);
        // write page content
        $pdf->WriteHTML($styleContent);
        $pdf->WriteHTML($content_1);
        $pdf->SetHeader($content_2_header);
        $pdf->SetFooter($footer_2_content);
        // add additional page with layout        
        $pdf->AddPage('P','','','','',10,10,0,20,5,0);
        // write page content
        $pdf->WriteHTML($content_2);
        // service agreement file path create if not exist
        $fileParDir = FCPATH . ARCHIEVE_DIR;
        if (!is_dir($fileParDir)) {
            mkdir($fileParDir, 0755);
        }
        // create folder with service agreement attachment id
        $fileDir = $fileParDir . $service_agreement_attachment_id;
        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0755);
        }
        // file name
        $filename = $attachment_type . ' ' . 'Contract unsigned_'. $service_agreement_id;
        $filenameWithExtension = $filename.'.pdf';
        $pdfFilePath =  $fileDir . '/' . $filenameWithExtension;
        // write file into the folder
        $page_count = $pdf->page;

        $service_agreement_pdf = $pdf->Output($pdfFilePath, 'F');

        $preview_url = base_url('mediaShowTemp/' . $service_agreement_id . '?filename=' . urlencode(base64_encode($filenameWithExtension)));
        // generate & send envelope if file exist
        if(file_exists($pdfFilePath))
        {   
            return [ "status" => true, "msg" => "Preview contract generated successfully", "data" => $preview_url ];
        } else {
            return [ "status" => false, "error" => "file not being generated" ];
        }
    }
}
