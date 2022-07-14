<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Finance_common_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    public function get_organisation_name_payroll_exemption($reqData) {
        $sub_query = $this->get_organisation_not_exists_sub_query();
        $search = $reqData->search;
        $this->db->select(array('o.name as label', 'o.id as value'));
        $this->db->from('tbl_organisation as o');
        $this->db->like('o.name', $search);
        $this->db->where('o.archive', 0);
        $this->db->where('o.payroll_tax', 1);
        $this->db->where("not exists (" . $sub_query . ")", NULL, false);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        return $result;
    }

    private function get_organisation_not_exists_sub_query() {
        $this->db->select('sub_fpeo.organisation_id');
        $this->db->from('tbl_finance_payroll_exemption_organisation sub_fpeo');
        $this->db->where("sub_fpeo.organisation_id=o.id", NULL, false);
        $this->db->where('sub_fpeo.archive', 0);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_organisation_email_sub_query() {
        $this->db->select('sub_oe.email');
        $this->db->from('tbl_organisation_email sub_oe');
        $this->db->where("sub_oe.organisationId=o.id AND sub_oe.archive=o.archive", NULL, false);
        $this->db->where('sub_oe.archive', 0);
        $this->db->where('sub_oe.primary_email', 1);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_organisation_phone_sub_query() {
        $this->db->select('sub_op.phone');
        $this->db->from('tbl_organisation_phone sub_op');
        $this->db->where("sub_op.organisationId=o.id AND sub_op.archive=o.archive", NULL, false);
        $this->db->where('sub_op.archive', 0);
        $this->db->where('sub_op.primary_phone', 1);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_organisation_contact_name_sub_query() {
        //$this->db->select("CONCAT_WS(' ',sub_oac.name,sub_oac.lastname)", false);
        $this->db->select("REPLACE(concat(COALESCE(sub_oac.name,''),' ',COALESCE(sub_oac.lastname,'')),'  ',' ')", false);
        $this->db->from('tbl_organisation_all_contact sub_oac');
        $this->db->where("sub_oac.organisationId=o.id AND sub_oac.archive=o.archive", NULL, false);
        $this->db->where('sub_oac.archive', 0);
        $this->db->where('sub_oac.type', 3);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_organisation_contact_email_sub_query() {
        $this->db->select("sub_oace.email");
        $this->db->from('tbl_organisation_all_contact sub_oac');
        $this->db->join("tbl_organisation_all_contact_email sub_oace", "sub_oace.contactId=sub_oac.id AND sub_oace.archive=sub_oac.archive AND sub_oac.archive=0 AND sub_oac.type=3 AND sub_oace.primary_email=1", "inner");
        $this->db->where("sub_oac.organisationId=o.id AND sub_oac.archive=o.archive", NULL, false);
        $this->db->where('sub_oac.archive', 0);
        $this->db->where('sub_oac.type', 3);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_organisation_contact_phone_sub_query() {
        $this->db->select("sub_oacp.phone");
        $this->db->from('tbl_organisation_all_contact sub_oac');
        $this->db->join("tbl_organisation_all_contact_phone sub_oacp", "sub_oacp.contactId=sub_oac.id AND sub_oacp.archive=sub_oac.archive AND sub_oac.archive=0 AND sub_oac.type=3 AND sub_oacp.primary_phone=1", "inner");
        $this->db->where("sub_oac.organisationId=o.id AND sub_oac.archive=o.archive", NULL, false);
        $this->db->where('sub_oac.archive', 0);
        $this->db->where('sub_oac.type', 3);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_organisation_name_sub_query() {
        $this->db->select('sub_o.name');
        $this->db->from('tbl_organisation sub_o');
        $this->db->where("sub_o.id=o.id AND sub_o.archive=o.archive", NULL, false);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_organisation_primary_address_sub_query() {
        $this->db->select("CONCAT_WS(', ',sub_oa.street,sub_oa.city,sub_s.name,sub_oa.postal)");
        $this->db->from('tbl_organisation_address sub_oa');
        $this->db->join("tbl_state sub_s", "sub_s.id=sub_oa.state AND sub_s.archive=0", "inner");
        $this->db->where("sub_oa.organisationId=o.id AND o.archive=0", NULL, false);
        //$this->db->where('sub_oa.archive',0);
        $this->db->where('sub_oa.primary_address', 1);
        $this->db->order_by('sub_oa.category ASC');
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    public function get_organisation_details_by_id($orgId) {
        $sub_query_organisation_email = $this->get_organisation_email_sub_query();
        $sub_query_organisation_phone = $this->get_organisation_phone_sub_query();
        $sub_query_organisation_contact_name = $this->get_organisation_contact_name_sub_query();
        $sub_query_organisation_contact_email = $this->get_organisation_contact_email_sub_query();
        $sub_query_organisation_contact_phone = $this->get_organisation_contact_phone_sub_query();
        $sub_query_organisation_primary_address = $this->get_organisation_primary_address_sub_query();
        $sub_query_organisation_name = $this->get_organisation_name_sub_query();
        $this->db->select(
                array(
            "(" . $sub_query_organisation_email . ") as email",
            "(" . $sub_query_organisation_phone . ") as phone",
            "(" . $sub_query_organisation_contact_name . ") as contact_name",
            "(" . $sub_query_organisation_contact_email . ") as contact_email",
            "(" . $sub_query_organisation_contact_phone . ") as contact_phone",
            "(" . $sub_query_organisation_primary_address . ") as address",
            "o.name as org_name",
            "o.name",
            "o.parent_org",
            "o.logo_file as profile_image",
            "CASE WHEN parent_org > 0 THEN (" . $sub_query_organisation_name . ") ELSE '' END as parent_org_name"
                ), false
        );
        $this->db->from('tbl_organisation as o');
        $this->db->where('o.id', $orgId);
        $this->db->where('o.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->num_rows() > 0 ? $query->row_array() : [];
        if (!empty($result) && isset($result['profile_image'])) {
            $result['profile_image'] = $this->get_org_profile_image($orgId, $result['profile_image']);
        }

        return $result;
    }

    public function get_org_profile_image($orgId, $profileImage = 'd.png') {
        return base_url('mediaShowProfile/o/' . urlencode(base64_encode($orgId)) . '/' . urlencode(base64_encode($profileImage)));
    }

    public function get_organisation_details_by_ids($orgIds = []) {
        $orgIds = !empty($orgIds) ? $orgIds : [0];
        $orgIds = is_array($orgIds) ? $orgIds : [$orgIds];
        $sub_query_organisation_email = $this->get_organisation_email_sub_query();
        $sub_query_organisation_phone = $this->get_organisation_phone_sub_query();
        $sub_query_organisation_contact_name = $this->get_organisation_contact_name_sub_query();
        $sub_query_organisation_contact_email = $this->get_organisation_contact_email_sub_query();
        $sub_query_organisation_contact_phone = $this->get_organisation_contact_phone_sub_query();
        $sub_query_organisation_primary_address = $this->get_organisation_primary_address_sub_query();
        $sub_query_organisation_name = $this->get_organisation_name_sub_query();
        $this->db->select(
                array(
            "(" . $sub_query_organisation_email . ") as email",
            "(" . $sub_query_organisation_phone . ") as phone",
            "(" . $sub_query_organisation_contact_name . ") as contact_name",
            "(" . $sub_query_organisation_contact_email . ") as contact_email",
            "(" . $sub_query_organisation_contact_phone . ") as contact_phone",
            "(" . $sub_query_organisation_primary_address . ") as address",
            "o.name as org_name",
            "o.parent_org",
            "o.id as org_id",
            "o.logo_file as profile_image",
            "CASE WHEN parent_org > 0 THEN (" . $sub_query_organisation_name . ") ELSE '' END as parent_org_name"
                ), false
        );
        $this->db->from('tbl_organisation as o');
        $this->db->where_in('o.id', $orgIds);
        $this->db->where('o.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->num_rows() > 0 ? $query->result_array() : [];
        return $result;
    }

    private function get_participant_email_sub_query() {
        $this->db->select('sub_pe.email');
        $this->db->from('tbl_participant_email sub_pe');
        $this->db->where("sub_pe.participantId=p.id", NULL, false);
        //$this->db->where('sub_pe.archive',0);
        $this->db->where('sub_pe.primary_email', 1);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_participant_phone_sub_query() {
        $this->db->select('sub_pp.phone');
        $this->db->from('tbl_participant_phone sub_pp');
        $this->db->where("sub_pp.participantId=p.id", NULL, false);
        //$this->db->where('sub_pp.archive',0);
        $this->db->where('sub_pp.primary_phone', 1);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_participant_primary_address_sub_query() {
        $this->db->select("CONCAT_WS(', ',sub_pa.street,sub_pa.city,sub_s.name,sub_pa.postal)");
        $this->db->from('tbl_participant_address sub_pa');
        $this->db->join("tbl_state sub_s", "sub_s.id=sub_pa.state AND sub_s.archive=0", "inner");
        $this->db->where("sub_pa.participantId=p.id AND p.archive=0", NULL, false);
        $this->db->where('sub_pa.primary_address', 1);
        $this->db->order_by('sub_pa.site_category ASC');
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_participant_funding_type_name_id_plan_sub_query() {
        $this->db->select("COALESCE(CONCAT_WS('@@__BREAKUP__@@',sub_ft.id,sub_ft.name),'')");
        $this->db->from("tbl_user_plan sub_ppl");
        $this->db->join("tbl_funding_type sub_ft", "sub_ft.id=sub_ppl.funding_type AND '" . DATE_CURRENT . "' between sub_ppl.start_date AND sub_ppl.end_date");
        $this->db->where('sub_ppl.userId=p.id', NULL, FALSE);
        $this->db->where('sub_ppl.user_type=2', NULL, FALSE);
        $this->db->where('sub_ppl.archive=0', NULL, FALSE);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    public function get_participant_details_by_id($patId) {
        $sub_query_participant_email = $this->get_participant_email_sub_query();
        $sub_query_participant_phone = $this->get_participant_phone_sub_query();
        $sub_query_participant_address = $this->get_participant_primary_address_sub_query();
        $sub_query_participant_plan_funding_type = $this->get_participant_funding_type_name_id_plan_sub_query();

        $this->db->select(
                array(
            "(" . $sub_query_participant_email . ") as email",
            "(" . $sub_query_participant_phone . ") as phone",
            "(" . $sub_query_participant_address . ") as address",
            "COALESCE((" . $sub_query_participant_plan_funding_type . "),'') as fund_type_name_id",
            //"CONCAT_WS(' ',p.firstname,p.middlename,p.lastname) as name",
            "REPLACE(concat(COALESCE(p.firstname,''),' ',COALESCE(p.middlename,''),' ',COALESCE(p.lastname,'')),'  ',' ') as name",
            "p.firstname",
            "p.lastname",
            "p.gender",
            "p.profile_image",
                ), false
        );
        $this->db->from('tbl_participant as p');
        $this->db->where('p.id', $patId);
        $this->db->where('p.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $result = $query->num_rows() > 0 ? $query->row_array() : [];
        if (!empty($result) && isset($result['profile_image'])) {

            if (empty($result['profile_image'])) {
                $result['profile_image'] = 'd.png';
            }
            $result['profile_image'] = base_url('mediaShowProfile/p/' . urlencode(base64_encode($patId)) . '/' . urlencode(base64_encode($result['profile_image'])) . '/' . urlencode(base64_encode((int) $result['gender'])));
        }
        if (!empty($result) && isset($result['fund_type_name_id'])) {
            $data = explode('@@__BREAKUP__@@', $result['fund_type_name_id']);
            $result['fund_type_id'] = $data[0] ?? 1;
            $result['fund_type_name'] = $data[1] ?? 'NDIS';
            unset($result['fund_type_name_id']);
        }

        return $result;
    }

    public function get_site_details_by_id(int $siteId = 0) {
        $this->load->model('organisation/Org_model');
        $result = $this->Org_model->get_organisation_site($siteId);
        if (!empty($result) && isset($result['profile_image'])) {

            if (empty($result['profile_image'])) {
                $result['profile_image'] = 'd.png';
            }
            $result['profile_image'] = base_url('mediaShowProfile/os/' . urlencode(base64_encode($siteId)));
            $result['email'] = $sub_query_site_email = $this->get_site_email_sub_query(1);
            $result['phone'] = $sub_query_site_phone = $this->get_site_phone_sub_query(1);
        }

        return $result;
    }

    private function get_site_email_sub_query($userType = 1) {
        $this->db->select('sub_ose.email');
        $this->db->from('tbl_house_and_site_email sub_ose');
        $this->db->where("sub_ose.siteId=os.id", NULL, false);
        $this->db->where('sub_ose.archive', 0);
        $this->db->where('sub_ose.user_type', $userType);
        $this->db->where('sub_ose.primary_email', 1);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_site_phone_sub_query($userType = 1) {
        $this->db->select('sub_osp.phone');
        $this->db->from('tbl_house_and_site_phone sub_osp');
        $this->db->where("sub_osp.siteId=os.id", NULL, false);
        $this->db->where('sub_osp.archive', 0);
        $this->db->where('sub_osp.user_type', $userType);
        $this->db->where('sub_osp.primary_phone', 1);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    /* $extraParm =['file_name'=>time().'export.csv','file_dir_path'=>FCPATH.ARCHIEVE_DIR.'/'] */

    public function export_csv($dataHearder = [], $dataRes = [], $extraParm = []) {
        $this->load->library("Excel");
        $object = new PHPExcel();
        $object->setActiveSheetIndex(0);
        $fileName = $extraParm['file_name'] ?? time() . 'export.csv';
        $fileDirPath = $extraParm['file_dir_path'] ?? FCPATH . ARCHIEVE_DIR . '/';
        $column = 0;
        foreach ($dataHearder as $field) {
            $object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
            $column++;
        }

        if (!empty($dataRes)) {
            $excel_row = 2;
            foreach ($dataRes as $row) {
                $columnRound = 0;
                foreach ($dataHearder as $key => $value) {
                    $object->getActiveSheet()->setCellValueByColumnAndRow($columnRound, $excel_row, $row[$key] ?? '');
                    $columnRound++;
                }
                $excel_row++;
            }
        }

        $lastColumn = getEcxcelColumnNameGetByIndex(count($dataHearder));
        $object->setActiveSheetIndex()
                ->getStyle('A1:' . $lastColumn . '1')
                ->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'C0C0C0:')
                            )
                        )
        );

        $object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
        $csv_fileFCpath = $fileDirPath . $fileName;
        $response = $object_writer->save($csv_fileFCpath);
        if (file_exists($csv_fileFCpath)) {
            return ['status' => true, 'filename' => $fileName];
        }
        return ['status' => false, 'error' => 'csv file not exist'];
    }

    public function get_house_details_by_id(int $houseId = 0) {
        $sub_query_house_email = $this->get_site_email_sub_query(2);
        $sub_query_house_phone = $this->get_site_phone_sub_query(2);
        $this->db->select([
            "os.name as name",
            "os.street as address_line_1",
            "os.suburb as city",
            "os.postal as postal_code",
            "(SELECT name FROM tbl_state as sub_s WHERE sub_s.id=os.state limit 1) as region",
            "CONCAT_WS(', ',os.street,os.suburb,(SELECT name FROM tbl_state as sub_s WHERE sub_s.id=os.state limit 1),os.postal) as address",
            "(" . $sub_query_house_email . ") as email",
            "(" . $sub_query_house_phone . ") as phone",
            "'' as profile_image"
        ]);
        $this->db->from('tbl_house as os');
        $this->db->where('os.id', $houseId);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->num_rows() > 0 ? $query->row_array() : [];
        if (!empty($result) && isset($result['profile_image'])) {

            if (empty($result['profile_image'])) {
                $result['profile_image'] = 'd.png';
            }
            $result['profile_image'] = base_url('mediaShowProfile/h/' . urlencode(base64_encode($houseId)));
        }

        return $result;
    }

    public function get_user_plan_items_by_ids($planLineItemId=[]) {
        $planLineItemId = !empty($planLineItemId) ? $planLineItemId : 0;
        $planLineItemId = !is_array($planLineItemId) ? [$planLineItemId] : $planLineItemId;
        $this->db->select(['upli.total_funding', 'upli.fund_used','upli.fund_remaining','upli.id','upli.line_itemId']);
        $this->db->select("(select sub_fli.line_item_name from tbl_finance_line_item as sub_fli where sub_fli.id = upli.line_itemId) as item_name");
        $this->db->from('tbl_user_plan_line_items upli');
        $this->db->where('upli.archive', 0);
        $this->db->where_in('upli.id', $planLineItemId);
        $query = $this->db->get();
        return $query->result_array();
    }

    private function get_member_email_sub_query() {
        $this->db->select('sub_me.email');
        $this->db->from('tbl_member_email sub_me');
        $this->db->where("sub_me.memberId=m.id", NULL, false);
        $this->db->where('sub_me.archive',0);
        $this->db->where('sub_me.primary_email', 1);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_member_phone_sub_query() {
        $this->db->select('sub_mp.phone');
        $this->db->from('tbl_member_phone sub_mp');
        $this->db->where("sub_mp.memberId=m.id", NULL, false);
        $this->db->where('sub_mp.archive',0);
        $this->db->where('sub_mp.primary_phone', 1);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    private function get_member_keypay_kiosk_mapping_sub_query_by_auth_id($authId,$extraParm=[]) {
        $this->db->select('sub_kkemfm.keypay_emp_id');
        $this->db->from('tbl_keypay_kiosks_emp_mapping_for_member sub_kkemfm');
        $this->db->where("sub_kkemfm.member_id=m.id", NULL, false);
        $this->db->where('sub_kkemfm.archive',0);
        $this->db->where('sub_kkemfm.keypay_auth_id', $authId);
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }
    
    public function call_specific_function($parms=[]){
        $functionName = $parms['function_name'] ?? "";
        $parameter = $parms['argument'] ?? [];
        if(!empty($functionName) && method_exists($this, $functionName)){
           return call_user_func_array([$this,$functionName],($parameter));
        }else{
            return false;
        }
    }

}
