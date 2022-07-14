<?php

defined('BASEPATH') or exit('No direct script access allowed');

//class Master extends MX_Controller
class Common_model extends CI_Model
{
    # mapping of access object types and their ids
    private $access_object_types = [
        "shift" => 1
    ];

    function __construct()
    {
        parent::__construct();
        $this->appserver_file_path = (getenv('ENV_OUTSIDE_PATH')) ? getenv('ENV_OUTSIDE_PATH') . '/': FCPATH;
    }

    /**
     * fetching the reference list using the reference key name
     */
    public function get_reference_data_list($key_name, $return_key = false) {
        $this->db->select(["r.id AS value", "r.display_name AS label"]);
        $this->db->from(TBL_PREFIX . 'reference_data_type as rdt');
        $this->db->join(TBL_PREFIX . 'references as r','rdt.id = r.type AND r.archive = 0', 'INNER');
        $this->db->where(['rdt.archive' => 0, 'rdt.key_name' => $key_name]);
        $query = $this->db->get();
        if($query->num_rows() > 0 && $return_key == false)
            return $query->result_array();
        else if($query->num_rows() > 0 && $return_key == true) {
            $retrow = null;
            foreach($query->result_array() as $row) {
                $retrow[$row['label']] = $row['value'];
            }
            return $retrow;
        }
    }

    /**
     * Fetching all the pay level options
     */
    public function get_level_options() {
        return $this->get_reference_data_list('pay_levels');
    }

    /**
     * Fetching all the skill levels (pay points) options
     */
    public function get_pay_point_options() {
        return $this->get_reference_data_list('skills');
    }

    /**
     * Get all employment types from reference table
     */
    public function get_employment_type_options() {
        return $this->get_reference_data_list('employment_type');
    }

    /**
     * Get all cost books from reference table
     */
    public function get_cost_book_options() {
        $data = $this->get_reference_data_list('cost_book');
        return ["status" => true, "data" => $data];
    }

    /**
     * fetching a single reference data row
     */
    public function get_reference_data_row($data_type_key, $data_key) {
        $this->db->select(["r.*"]);
        $this->db->from(TBL_PREFIX . 'reference_data_type as rdt');
        $this->db->join(TBL_PREFIX . 'references as r','rdt.id = r.type AND r.archive = 0', 'INNER');
        $this->db->where(['rdt.archive' => 0, 'rdt.key_name' => $data_type_key, 'r.key_name' => $data_key]);
        $res = $this->db->get();
        if($res)
        return $res->result_array()[0];
    }

    /**
     * checking if the lock is taken by other user for same object type and object id
     * returns false, if taken by other user
     * returns true, if lock is taken by current user
     */
    public function get_take_access_lock($data, $adminId) {
        $object_type_id = $object_id = $access_level = $check_only = null;
        if(isset($data['object_type']))
            $object_type_id = $this->access_object_types[$data['object_type']];
        if(isset($data['object_id']))
            $object_id = $data['object_id'];
        if(isset($data['access_level']))
            $access_level = $data['access_level'];
        if(isset($data['check_only']))
            $check_only = $data['check_only'];

        if(!$object_type_id) {
            return ["status" => false, "error" => "Access object not found!"];
        }
        if(!$object_id) {
            return ["status" => false, "error" => "Access object id missing!"];
        }

        # checking if the object is currently being altered
        $where["object_type_id"] = $object_type_id;
        $where["object_id"] = $object_id;
        $where["archive"] = "0";
        $access_level_title = $access_level;
        if($access_level == "add")
            $where["add_lock"] = 1;
        else if($access_level == "edit")
            $where["edit_lock"] = 1;
        else if($access_level == "delete")
            $where["delete_lock"] = 1;
        else
            $access_level_title = "access";

        $this->db->select(["id"]);
        $this->db->from("tbl_access_lock");
        $this->db->where($where);
        $this->db->where("created_by != ".$adminId);
        $query = $this->db->get();
        $result = $query->result();
        if (!empty($result))
            return ["status" => false, "error" => "This record is locked for editing."];

        # make sure any existing ones for same object, object id and admin id is also archived first
        $this->remove_access_lock($where, $adminId);

        if($check_only)
            return ["status" => true, "msg" => "Successfully checked, no lock taken!"];

        # if not being altered, take the lock so other users cannot alter the same record
        $insert_data = $where;
        $insert_data["created"] = DATE_TIME;
        $insert_data["created_by"] = $adminId;
        $this->basic_model->insert_records('access_lock', $insert_data, $multiple = false);
        return ["status" => true, "msg" => "Successfully taken the lock"];
    }

    /**
     * removes the lock taken by the user for a given object and object id
     */
    public function remove_access_lock($data, $adminId) {
        $deldata = [];
        if(isset($data['object_type'])) {
            $object_type_id = $this->access_object_types[$data['object_type']];
            $upddata["object_type_id"] = $object_type_id;
            $deldata["object_type_id"] = $object_type_id;
        }

        if(isset($data['object_id'])) {
            $upddata["object_id"] = $data['object_id'];
            $deldata["object_id"] = $data['object_id'];
        }

        $upddata["archive"] = 1;
        $upddata["updated"] = DATE_TIME;
        $upddata["updated_by"] = $adminId;

        $this->basic_model->update_records("access_lock", $upddata, ["created_by" => $adminId]);

        # delete record
        if (!empty($deldata)) {
            $this->basic_model->delete_records("access_lock", $deldata);
        }
        
        return ["status" => true, "msg" => "Successfully removed the lock"];
    }

    public function get_suburb($post_data, $state)
    {
        $tbl_suburb_state = TBL_PREFIX . 'suburb_state';

        $this->db->select(array('suburb as value', 'suburb as label', 'postcode'));
        $this->db->from($tbl_suburb_state);
        $this->db->where(array('stateId' => $state));
        $this->db->group_by('suburb');
        $this->db->like('suburb', $post_data);
        $query = $this->db->get();

        return $result = $query->result();
    }

    /**
     * Find suburb without requiring state ID
     * Warning: This might be SQL intensive
     *
     * @param string $post_data
     * @return \stdClass[]
     */
    public function get_suburb_no_state_needed($post_data)
    {
        $q = $this->db
            ->from('tbl_suburb_state')
            ->like('suburb', $post_data)
            ->select([
                'suburb as value',
                'suburb as label',
                'postcode',
                'stateId',
            ])
            ->get();

        return $q->result();
    }

    public function get_user_for_compose_mail($reqData)
    {

        $name = $this->db->escape_str($reqData->search);
        $sql = array();

        $admin = $participant = $member = $org = $applicant = [];
        if (!empty($reqData->previous)) {
            foreach ($reqData->previous as $val) {
                if ($val->type == 2) {
                    $participant[] = $val->value;
                } elseif ($val->type == 3) {
                    $member[] = $val->value;
                } elseif ($val->type == 4) {
                    $org[] = $val->value;
                } elseif ($val->type == 5) {
                    $applicant[] = $val->value;
                }
            }
        }

        $this->db->select(["concat(firstname, ' ', middlename, ' ', lastname,' - ', pe.email) as  label", "'2'  as type", "p.id as value"]);
        $this->db->from("tbl_participant as p");
        $this->db->join("tbl_participant_email as pe","pe.participantId=p.id");
        $this->db->where("p.archive", 0);
        $this->db->like("concat(firstname, ' ', middlename, '', lastname)", $name);
        if (!empty($participant)) {
            $this->db->where_not_in("p.id", $participant);
        }
        $sql[] = $this->db->get_compiled_select();

        $this->db->select(["concat(firstname, ' ', middlename, ' ', lastname,' - ', me.email) as  label", "'3'  as type", "m.id as value"]);
        $this->db->from("tbl_member as m");
        $this->db->join("tbl_member_email as me","me.memberId=m.id");
        $this->db->join("tbl_department as d", "m.department = d.id AND d.short_code = 'external_staff'", "INNER");
        $this->db->where("m.archive", 0);
        $this->db->like("concat(firstname, ' ', middlename, '', lastname)", $name);
        if (!empty($member)) {
            $this->db->where_not_in("m.id", $member);
        }
        $sql[] = $this->db->get_compiled_select();

        $this->db->select(["concat(name,' - ', oe.email) as  label", "'4'  as type", "o.id as value"]);
        $this->db->from("tbl_organisation as o");
        $this->db->join("tbl_organisation_email as oe","oe.organisationId=o.id");
        $this->db->where("o.archive", 0);
        $this->db->like("name", $name);
        if (!empty($org)) {
            $this->db->where_not_in("o.id", $org);
        }
        $sql[] = $this->db->get_compiled_select();

        $this->db->select(["concat_ws(' ',firstname,lastname,' - ', rae.email) as  label, '5'  as type", "ra.id as value"]);
        $this->db->from("tbl_recruitment_applicant as ra");
        $this->db->join("tbl_recruitment_applicant_email as rae","rae.applicant_id=ra.id");
        $this->db->like("concat_ws(' ',firstname,lastname)", $name);
        $this->db->where("ra.archive", 0);
        if (!empty($applicant)) {
            $this->db->where_not_in("ra.id", $applicant);
        }
        $sql[] = $this->db->get_compiled_select();

        $sql = implode(' union ', $sql);
        $query = $this->db->query($sql);

        return $result = $query->result();
    }

    public function get_admin_name($reqData)
    {
        if (!empty($reqData->selected_user)) {
            $users = obj_to_arr($reqData->selected_user);
            $pre_select = array_column($users, "value");
            $this->db->where_not_in("m.id", $pre_select);
        }

        $this->db->select("concat(firstname,' ', lastname) as label");
        $this->db->select("m.id as value");
        $this->db->from('tbl_member as m');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"');
        $this->db->like('m.firstname', $reqData->search);
        $query = $this->db->get();
        return $query->result();
    }

    function get_admin_name_by_filter($reqData)
    {
        $this->db->select("concat_ws(' ',firstname,lastname) as label");
        $this->db->select("m.uuid as value");
        $this->db->from('tbl_member as m');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"');
        if (!empty($reqData->search)) {
            $this->db->like("concat_ws(' ',firstname,lastname)", $reqData->search);
        }

        $this->db->where("m.archive", 0);
        //        $this->db->where("m.status", 1);
        $query = $this->db->get();
        return $query->result();
    }

    public function get_admin_team_department($name, $currentAdminId)
    {
        $name = $this->db->escape_str($name);

        $query = $this->db->query("select concat(firstname,' ', lastname,' - (Staff)') as  label, '1'  as type, id as value from tbl_member as m INNER JOIN tbl_department as d on d.id = m.department AND d.short_code = 'internal_staff' WHERE archive = 0 and concat(firstname, ' ', lastname) LIKE '%" . $name . "%' and id != " . $currentAdminId . "
            union

            select concat(team_name,' - (My Team)') as  label, '2'  as type, id as value from tbl_internal_message_team
            WHERE archive = 0 and team_name LIKE '%" . $name . "%' and adminId =" . $currentAdminId . "
            union

            select concat(name,' - (Department)') as  label, '3'  as type, id as value from tbl_department
            WHERE archive = 0 and name LIKE '%" . $name . "%'");

        return $query->result();
    }

    public function get_global_search_data($search, $adminId)
    {
        $all_permission = get_all_permission($adminId);


        $search = $this->db->escape_str($search);

        $sql = array();
        if (array_key_exists('access_admin', $all_permission)) {
            $sql[] = "select concat(firstname,' ', lastname) as  label, 'a' as type, tbl_member.id as value, concat('/admin/user/update/', tbl_member.id) as url from tbl_member INNER JOIN tbl_department on tbl_member.department = tbl_department.id AND tbl_department.short_code = 'internal_staff'
            WHERE tbl_member.archive = 0 and (concat(firstname, ' ', lastname) LIKE '%" . $search . "%' OR tbl_member.id = '" . $search . "')";
        }

        if (array_key_exists('access_participant', $all_permission)) {
            $sql[] = "select concat(firstname, ' ',middlename,' ',lastname) as label, 'p'  as type, tbl_participant.id as value, concat('/admin/participant/about/', tbl_participant.id) as url from tbl_participant
            WHERE archive = 0 and (concat(firstname, ' ',middlename,' ',lastname) LIKE '%" . $search . "%' OR tbl_participant.id LIKE '" . $search . "')";
        }

        if (array_key_exists('access_member', $all_permission)) {
            $sql[] = "select concat(firstname, ' ',middlename,' ',lastname) as label, 'm'  as type, tbl_member.id as value, concat('/admin/member/about/', tbl_member.id) as url from tbl_member INNER JOIN tbl_department on tbl_member.department = tbl_department.id AND tbl_department.short_code = 'external_staff' left join tbl_member_email on tbl_member_email.memberId = tbl_member.id
            WHERE tbl_member.archive = 0 and (concat(firstname, ' ',middlename,' ',lastname) LIKE '%" . $search . "%' OR tbl_member.id LIKE '" . $search . "')";
        }

        if (array_key_exists('access_schedule', $all_permission)) {
            $sql[] = "select 'Shift' as label, 's'  as type, id as value, concat('/admin/schedule/details/', id) as url from tbl_shift
            WHERE status != 8 and (id LIKE '" . $search . "')";
        }

        if (array_key_exists('access_fms', $all_permission)) {
            $sql[] = "select 'Fms' as label, 'f'  as type, id as value, concat('/admin/fms/case/', id) as url from tbl_fms_case
            WHERE (id LIKE '" . $search . "') ";
        }

        if (array_key_exists('access_organization', $all_permission)) {
            $sql[] = "select name as label, 'o'  as type, id as value, concat('/admin/organisation/overview/', id) as url from tbl_organisation WHERE archive = '0' AND parent_org = 0  AND (name LIKE '%" . $search . "%' OR id LIKE '" . $search . "' OR abn LIKE '" . $search . "') ";
        }

        if (array_key_exists('access_organization', $all_permission)) {
            $sql[] = "select name as label, 'h'  as type, id as value, concat('/admin/house/details/', id) as url from tbl_house WHERE archive = '0' AND (name LIKE '%" . $search . "%' OR id LIKE '" . $search . "' OR abn LIKE '" . $search . "') ";
        }

        if (array_key_exists('access_recruitment', $all_permission)) {
            $x = "select concat_ws(' ',firstname,middlename,lastname) as label, 'r'  as type, id as value, concat('/admin/recruitment/applicant/', id) as url from tbl_recruitment_applicant WHERE archive = '0' AND duplicated_status='0' AND (concat_ws(' ',firstname,lastname) LIKE '%" . $search . "%' OR id LIKE '" . $search . "' OR appId LIKE '" . $search . "')";

            if (array_key_exists('access_recruitment', $all_permission) && !array_key_exists('access_recruitment_admin', $all_permission)) {
                $x .= "AND recruiter LIKE  '" . $adminId . "'";
            }

            $sql[] = $x;
        }

        if (array_key_exists('access_crm', $all_permission)) {
            $x = "select concat_ws(' ',firstname,middlename,lastname) as label, 'c'  as type, id as value, concat('/admin/crm/participantdetails/', id) as url from tbl_crm_participant WHERE archive = '0' AND (concat_ws(' ',firstname,lastname) LIKE '%" . $search . "%' OR id LIKE '" . $search . "')";

            if (array_key_exists('access_crm', $all_permission) && !array_key_exists('access_crm_admin', $all_permission)) {
                $x .= "AND assigned_to =  '" . $adminId . "'";
            }

            $sql[] = $x;
        }

        if (array_key_exists('access_organization', $all_permission)) {
            $sql[] = "select line_item_name as label, 'finance'  as type, id as value, concat('/admin/finance/line_item/edit/', id) as url from tbl_finance_line_item WHERE (line_item_name LIKE '%" . $search . "%' OR line_item_number = '" . $search . "') ";
        }

        $sql = implode(' union ', $sql);
        $query = $this->db->query($sql);


        $result = $query->result();
        //        last_query();
        return $result;
    }

    public function get_member_name($post_data)
    {
        $this->db->like("m.fullname", $post_data);

        $this->db->where('m.archive', 0);
        $this->db->where('m.status', 1);
        $this->db->select("fullname as label");
        $this->db->select(array('m.id as value'));
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "external_staff"');
        $query = $this->db->get(TBL_PREFIX . 'member as m');

        //last_query();
        return $query->result();
    }

    public function get_org_name($post_data)
    {
        $this->db->like('name', $post_data, 'both');
        $this->db->where('archive', '0');
        $this->db->where('status', '1');
        #$this->db->where('parent_org', '0');
        $this->db->select(array('name', 'id', 'abn'));
        $query = $this->db->get(TBL_PREFIX . 'organisation');
        #last_query();
        $org_rows = array();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $org_rows[] = array('label' => $val->name, 'value' => $val->id, 'abn' => $val->abn);
            }
        }
        return $org_rows;
    }

    //Get Org name with out site
     public function get_is_org_name($post_data)
    {
        $this->db->like('name', $post_data, 'both');
        $this->db->where('archive', '0');
        $this->db->where('status', '1');
         $this->db->where(['is_site' => 0]);
        #$this->db->where('parent_org', '0');
        $this->db->select(array('name', 'id', 'abn'));
        $query = $this->db->get(TBL_PREFIX . 'organisation');

        #last_query();
        $org_rows = array();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $org_rows[] = array('label' => $val->name, 'value' => $val->id, 'abn' => $val->abn);
            }
        }
        return $org_rows;
    }

    public function get_recruitment_staff($post_data)
    {

        $this->db->select(array($tbl_1 . '.id', "CONCAT(m.firstname,' ',m.lastname) AS name"));
        $this->db->join('tbl_recruitment_staff', 'tbl_recruitment_staff.adminId = m.id', 'inner');
        $this->db->where('m.archive', "0");
        $this->db->like("CONCAT(m.firstname,' ',m.lastname)", $post_data);
        $query = $this->db->get('tbl_member as m');
        $staff_rows = array();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $staff_rows[] = array('label' => $val->name, 'value' => $val->id);
            }
        }
        return $staff_rows;
    }

    public function file_content_media($type, $file_name, $userId, $checkToken = 0, $extratParm = [])
    {
        $this->load->helper('file');
        $token = isset($extratParm['token']) ? $extratParm['token'] : '';
        $genderType = isset($extratParm['genderType']) ? $extratParm['genderType'] : '1';
        $defaultImageShow = isset($extratParm['defaultImageShow']) ? $extratParm['defaultImageShow'] : '0';
        $filePath = FCPATH;
        $permission_key = 0;
        if ($type == 'r') {
            $permission_key = 'access_recruitment';
            $filePath .= APPLICANT_ATTACHMENT_UPLOAD_PATH . $userId . '/' . $file_name;
        }
        if ($type == 'p') {
            $permission_key = 'access_participant';
        } else if ($type == 'o') {
            $permission_key = 'access_organization';
            $filePath .= ORG_UPLOAD_PATH . $userId . '/' . $file_name;
        } else if ($type == 'fq') {
            $permission_key = 'access_finance_quote';
            $filePath .= QUOTE_FILE_PATH . $file_name;
        } else if ($type == 'fe') {
            $permission_key = 'access_finance_shift_and_payroll';
            $filePath .= FINANCE_PAYROLL_EXEMPTION_ORG_UPLOAD_PATH . $userId . '/' . $file_name;
        } else if ($type == 'rp') {
            // Ipad api view presentation slides
            $permission_key = 'access_finance_shift_and_payroll';
            $filePath .= IPAD_DEVICE_PRESENTATION_PATH . $file_name;
        } else if ($type == 'rc') {
            // Ipad api view recruitment cab day draft contract
            $permission_key = 'access_finance_shift_and_payroll';
            $filePath .= CABDAY_INTERVIEW_CONTRACT_PATH . $file_name;
        } else if ($type == 'rg') {
            // Ipad api view recruitment group intreview draft contract
            $permission_key = 'access_finance_shift_and_payroll';
            $filePath .= GROUP_INTERVIEW_CONTRACT_PATH . $file_name;
        } else if ($type == 'fi') {
            $permission_key = 'access_finance_invoice';
            $filePath .= FINANCE_INVOICE_FILE_PATH . $file_name;
        } else if ($type == 'fs') {
            $permission_key = 'access_finance_invoice';
            $filePath .= FINANCE_STATEMENT_FILE_PATH . $file_name;
        }

        if ($checkToken == 1) {
            $response = verifyAdminToken((object) ['token' => $token], $permission_key, 0);

            if (empty($response['status'])) {
                return ['status' => false, 'msg' => 'Access denied'];
            }
        }
        $mimeType = '';
        $string = '';
        $status = true;
        if ($type == 'p') {
            $url = get_participant_img($userId, $file_name, $genderType);
            $mimeType = get_mime_by_extension($url);
            $string = file_get_contents($url);
        } else if (is_file($filePath) && file_exists($filePath)) {
            $mimeType = get_mime_by_extension($filePath);
            header('content-type: ' . $mimeType);
            $string = read_file($filePath);
        } else if (!is_file($filePath) && $defaultImageShow == 1) {
            $filePath = FCPATH . NO_IMAGE_PATH;
            $mimeType = get_mime_by_extension($filePath);
            header('content-type: ' . $mimeType);
            $string = read_file($filePath);
        } else {
            $status = false;
            $string = 'File not found';
        }

        return ['status' => $status, 'msg' => $string, 'mimetype' => $mimeType];
    }

    function get_all_public_holiday()
    {
        $this->db->select(['holiday_date', 'title', 'stateId']);
        $this->db->from('tbl_public_holiday');
        $this->db->where('date(holiday_date) > current_date()');
        $res = $this->db->get()->result();

        $result = [];
        if (!empty($res)) {
            foreach ($res as $val) {
                $result[$val->stateId][$val->holiday_date] = $val->title;
            }
        }
        return $result;
    }

    function get_state()
    {
        $column = array('name as label', 'id as value');
        $where = array('archive' => 0);
        $response = $this->basic_model->get_record_where('state', $column, $where);

        return $response;
    }

    function get_org_requirement()
    {
        $column = array('name as label', 'id as value');
        $where = array('archive' => 0);
        $response = $this->basic_model->get_record_where('organisation_requirement', $column, $where);

        return $response;
    }

    function get_document_category_by_user_type()
    {
        $column = array("concat(title,' >> ', name) as label", 'id as value');
        #$where = array('archive' => 0, 'user_type' => $user_type);
        $where = array('archive' => 0);
        $response = $this->basic_model->get_record_where('user_doc_category_list', $column, $where);
        return $response;
    }

    function get_central_reference_data_option($key_name)
    {
        $this->db->select(["r.display_name as label", 'r.id as value']);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.archive = 0 And r.archive=0", "INNER");
        $this->db->where("rdt.key_name", $key_name);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    public function validate_filter_logic($filter_logic)
    {
        $str = $filter_logic;
        $operator_array = array("or", "and");
        $valid_str = array("(", ")", "or", "and");
        $str_temp1 = trim(preg_replace('/\s+/', ' ', $str));
        $str_temp2 = preg_replace('/(?<!\ )[()]/', ' $0', $str_temp1);
        $str_temp = trim(preg_replace('/\s+/', ' ', $str_temp2));

        $res = explode(" ", $str_temp);
        $res = $this->clean_parenthesis($res);

        $valid_parenthesis = 0;
        if (count(array_keys($res, "(")) != count(array_keys($res, ")")))
            return false;

        for ($i = 0; $i < sizeof($res); $i++) {
            $source = strtolower($res[$i]);
            $valid_parenthesis = 0;
            if ((is_numeric($source) && $source <= 10) or in_array($source, $valid_str)) {
                if ($source == "(") {
                    $valid_parenthesis = $this->validate_parenthesis($i + 1, $res);

                    if ($valid_parenthesis == 0)
                        return false;
                }
            } else if (is_numeric($source) and $i > 0) {
                $valid_operand = $this->validate_operand($i, $res, $operator_array);
                if ($valid_operand == 0)
                    return false;
                else if (in_array($source, $operator_array)) {
                    if ($i + 1 < sizeof($res)) {
                        $valid_operator = $this->validate_operator($i, $res);
                        if ($valid_operator == 0)
                            return false;
                    } else
                        return false;
                }
            } else
                return false;
        } //for

        return true;
    }

    // Validate the filter logic for parenthesis
    public function validate_parenthesis($index, $source_str)
    {
        for ($j = $index; $j < sizeof($source_str); $j++) {
            $index_actual = $j + 1;
            if ($source_str[$j] == ")" && (($index_actual - $index) % 4) == 0) {
                return 1;
            }
        } //for J
        return 0;
    }

    public function clean_parenthesis($source_arr)
    {
        $source_temp = $source_arr;
        $start_index = 0;
        $end_index = 0;
        for ($k = 0; $k < sizeof($source_arr); $k++) {
            // check whether we found parenthesis with chars without spaces
            if ((strpos($source_arr[$k], "(") !== false or strpos($source_arr[$k], ")") !== false) and strlen($source_arr[$k]) > 1) {
                $index = 1;
                $end_index++;
                $delimeter = "(";
                if (strpos($source_arr[$k], "(") !== false) {
                    $delimeter = "(";
                    $index = 1;
                } else if (strpos($source_arr[$k], ")") !== false) {
                    $delimeter = ")";
                    $index = 0;
                }

                //split the chars from ) or )
                $par_array_temp = explode($delimeter, $source_arr[$k]);

                // form the correct string with parenthesis
                if (strpos($source_arr[$k], ")") !== false) {
                    if (!empty($par_array_temp[1]))
                        $par_array = array($par_array_temp[$index], $delimeter, $par_array_temp[1]);
                    else
                        $par_array = array($par_array_temp[$index], $delimeter);
                } else {

                    if (!empty($par_array_temp[0]))
                        $par_array = array($par_array_temp[0], $delimeter, $par_array_temp[$index]);
                    else
                        $par_array = array($delimeter, $par_array_temp[$index]);
                }

                $temp_end = array_slice($source_temp, $k + $end_index, sizeof($source_temp), true);

                if ($k == 0 and strpos($source_arr[$k], ")") == false)
                    $res_array = array_merge($par_array, $temp_end);
                //if Paraenthesis at mid of the string
                else {
                    $temp_start = array_slice($source_temp, 0, $k + $start_index, true);
                    $res_array_1 = array_merge($temp_start, $par_array);
                    $res_array = array_merge($res_array_1, $temp_end);
                }
                $source_temp = $res_array;

                $start_index++;
            }
        }
        return $source_temp;
    }

    // Validate the filter logic for operand
    public function validate_operand($index, $source_str, $operator_array)
    {
        $next_pos = $index + 1;
        $prev_pos = $index - 1;
        if ($next_pos < sizeof($source_str)) {
            if (in_array($source_str[$next_pos], $operator_array) || in_array($source_str[$prev_pos], $operator_array) || $source_str[$next_pos] == "(")
                return 1;
            else
                return 0;
        } else {
            if (in_array($source_str[$prev_pos], $operator_array))
                return 1;
            else
                return 0;
        }
    }

    // Validate the filter logic for operator
    public function validate_operator($index, $source_str)
    {
        $next_pos =  $index + 1;
        $prev_pos = $index - 1;
        if (($source_str[$next_pos] == "(" or  $source_str[$prev_pos] == ")" or  is_numeric($source_str[$next_pos]) or is_numeric($source_str[$prev_pos])) and $next_pos !=  sizeof($source_str))
            return 1;
        else {

            return 0;
        }
    }

    public function get_selected_filter_condition($filterdatas, $limit, $page, $filter_logic, $filter_operand_length)
    {
        $forhavingappend = [];
        $whereArrData = [];
        foreach ($filterdatas  as $filterdata) {
            $wherecondition = null;
            $filter_field = $filterdata['select_filter_field_val'];
            $filter_value = $filterdata['select_filter_value'];
            $filter_operator = $filterdata['select_filter_operator'];
            if ($filter_field && $filter_value && $filter_operator) {
                $wherecondition = $this->getoperator($filterdata, $forhavingappend, $filter_logic);
                array_push($whereArrData, $wherecondition);
                if (empty($filter_logic)) {
                    $this->db->where($wherecondition);
                }
            }
            if ($filter_field == "created_by") {
                $forhavingappend[$filterdata['select_filter_field_val']][] = $wherecondition;
            }
        }

        // validating the filter logic ex (1 OR 2) AND 3
        $whereLogic =  $filter_logic;
        if (!empty($filter_logic)) {
            if ($filter_operand_length == count($whereArrData)) {
                for ($i = 1; $i <= 10; $i++) {
                    $whereLogic = str_replace($i, "operand-" . $i, $whereLogic);
                }
                for ($i = 1; $i <= 10; $i++) {
                    $searchVal = "operand-" . $i;
                    if ($i <= count($whereArrData)) {
                        $whereLogic = str_replace($searchVal, $whereArrData[$i - 1], $whereLogic);
                    }
                }
                $this->db->where($whereLogic);
            } else {
                $return = array('data' => [], 'status' => false, 'msg' => 'filter_error', 'error' => 'Filter value is null');
                return $return;
            }
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        $result = $query->result();
        $return = array('data' => $result, 'status' => true);
        return $return;
    }

    function getoperator($filter,$forhavingappend,$filter_logic){
        $cond = null;
        $select_filter_field_val = $filter["select_filter_field_val"];
        $select_filter_value = $filter["select_filter_value"];
        $select_filter_operator = $filter["select_filter_operator"];

        $contact_type_array = array('1'=>"Applicant",'2'=>"Lead",'3'=>"Participant",'4'=>"Booker",'5'=>"Agent",'6'=>"Organisation");
        $status_option_array = array('1'=>"Active",'0'=>"In Active");

        // Check Created Date Query
        if($filter["select_filter_field_val"] == 'created'){
            $date_operator = $filter["select_filter_operator"];
            $date_opertr_sym = $filter["select_filter_operator_sym"];
            if($filter["selected_date_range"] && $filter["selected_date_range"]!="" && !empty($filter["selected_date_range"])){
                $start_end_date = explode("," , $filter["selected_date_range"]);
                if(count($start_end_date)>1){
                    return $this->createdDateConditionsCustomField($date_operator,$start_end_date[0],$start_end_date[1],$date_opertr_sym);
                }else{
                    $formated_date = date('Y-m-d',strtotime(str_replace('/', '-', $filter["select_filter_value"])));
                    return $this->createdDateConditionsForSpecificField($date_operator,$formated_date,$date_opertr_sym);
                }
            }else{
                $formated_date = date('Y-m-d',strtotime(str_replace('/', '-', $filter["select_filter_value"])));
                return $this->createdDateConditionsForSpecificField($date_operator,$formated_date,$date_opertr_sym);

            }


        }

        // Check Type Cast Query

        if($select_filter_field_val == "type"){
            $type_operator = $filter["select_filter_operator"];
            $type_opertr_sym = $filter["select_filter_operator_sym"];
            if (in_array($select_filter_value,$contact_type_array))
            {
            $select_filter_value= array_search($select_filter_value, $contact_type_array);
            }
            return $this->typeConditions($type_operator,$select_filter_value,$select_filter_field_val,$type_opertr_sym);
         }

         // Check Status Types Integer Type Query

        else if($select_filter_field_val == "status"){
            $status_operator = $filter["select_filter_operator"];
            $status_opertr_sym = $filter["select_filter_operator_sym"];
            if (in_array($select_filter_value,$status_option_array))
            {
            $select_filter_value = array_search($select_filter_value, $status_option_array);
            }
            return $this->statusConditions($status_operator,$select_filter_value,$select_filter_field_val,$status_opertr_sym);
        }

        // Check Full Names CONCAT Type Query

        if($select_filter_field_val == "fullname"){
            $setsymbol = $filter["select_filter_operator_sym"];
            $fullname_operator = $filter["select_filter_operator"];
            return $this->fullnameConditions($setsymbol,$fullname_operator,$select_filter_value);
        }

        if($select_filter_field_val == "created_by"){
            $setsymbol = $filter["select_filter_operator_sym"];
            $creby_operator = $filter["select_filter_operator"];
            return $this->createdByConditions($setsymbol,$creby_operator,$select_filter_value,$forhavingappend,$filter_logic);
        }
        $operator =  $select_filter_operator ?? "";
        $filter_value =  $select_filter_value ?? "";
        $field_val =  $select_filter_field_val ?? "";
        if($operator && $filter_value && $field_val){
         return $this->switchCaseOperators($operator,$filter_value,$field_val);
        }

    }

    // validate the custome filed date ex: this week, this month
    function createdDateConditionsCustomField($operator,$start_date,$end_date,$opertr_sym){
        $cond = null;

        if($operator == "equals" || $operator == "contains" || $operator == "starts with"){
            $cond .= "DATE(created) BETWEEN '".$start_date."' AND '".$end_date."' ";
        }else if($operator == "not equal to" || $operator == "does not contains"){
            $cond .= "DATE(created) NOT BETWEEN '".$start_date."' AND '".$end_date."' ";
        }else{
            $cond .= "DATE(created) ".$opertr_sym." '".$start_date."'";
        }
        return $cond;
    }

    // validate the specific filed date
    function createdDateConditionsForSpecificField($operator,$formated_date,$opertr_sym){
        $cond = null;
        if($operator == "does not contains"){
            $cond .= "DATE(created) NOT LIKE '%".$formated_date."'";
        }else if($operator == "contains"){
            $cond .= "DATE(created) LIKE '%".$formated_date."'";
        }else if($operator == "starts with"){
            $cond .= "DATE(created) LIKE '".$formated_date."%'";
        }else{
            $cond .= "DATE(created) ".$opertr_sym." '".$formated_date."'";
        }
        return $cond;
    }

    //Store CMS JSON URL and it's versioning
    function storeCMScontent($reqData) {
        if(!$reqData || !(array)$reqData->data) {
            return TRUE;
        }

        $version = 1.0;
        $data = $this->getCMScontent();
        if($data) {
            $version = $data[0]->version + 0.1;
        }

        $insert_data = [];
        $insert_data['url'] = $reqData->data->url;
        $insert_data['version'] = $version;
        $insert_data['created_by'] = $reqData->adminId;
        $insert_data['created_at'] = DATE_TIME;

        $this->basic_model->insert_records('cms_content', $insert_data, false);

        echo json_encode(["status" => true, "msg" => "Content Import Success"]);
        exit();

    }
    //Get CMS Content
    function getCMScontent() {

        $this->db->select(["url","version"]);
        $this->db->from(TBL_PREFIX . "cms_content");
        $this->db->order_by('id', 'DESC');
        $this->db->where('url is NOT NULL', NULL, FALSE);
        $this->db->limit(1);

        $res = $this->db->get();
        return $res->result_object();
    }
    function download_import_stats($import_id, $file_name) {
        $filename = $file_name. "_" . $import_id.".txt";
        header("Content-type: text/plain");
        header("Content-Disposition: attachment; filename=".$filename);
        $return="";
        $details = $this->basic_model->get_row('admin_bulk_import', ["error_text"], ["id" => $import_id]);
        if(!empty($details) && isset($details->error_text)) {
            $return = $details->error_text;
        }
        echo $return;
    }

    /**
     * Common function to get the viewed log in listing page
     * @param $result {array} array of values
     */
    public function get_viewed_log($result, $entity_type) {
        //fetch viewed by data
        $vlogs = [];
        $ids = array_map(function($item){
            return $item->lead_id;
        }, $result);
        
        if (!empty($ids)) {
            $this->db->select(['vl.entity_id', "concat(m.firstname,' ',m.lastname) as viewed_by", 'vl.viewed_date', 'vl.viewed_by as viewed_by_id']);
            $this->db->from('tbl_viewed_log as vl');
            $this->db->join('tbl_users as u', 'vl.viewed_by=u.id and u.archive=0 and vl.entity_type=' . $entity_type);
            $this->db->join('tbl_member as m', 'm.uuid=u.id and m.archive=0','inner');
            $this->db->where_in('vl.entity_id', $ids);
            $query2 = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $result2 = $query2->result();

            foreach($result2 as $v) {
                $vlogs[$v->entity_id] = $v;
            }
        }
        foreach ($result as $val) {
            if ( array_key_exists($val->id, $vlogs) ) {
                $val->viewed_by_id = $vlogs[$val->id]->viewed_by_id;
                $val->viewed_by = $vlogs[$val->id]->viewed_by;
                $val->viewed_date = $vlogs[$val->id]->viewed_date;
            }                
        }
   }

   /*
     * its use for making sub query created by (who creator of by module)
     * return type sql
     */
     function get_created_by_updated_by_sub_query($alias , $uuid_user_type, $column_name="created_by")
    {
        if($uuid_user_type==ADMIN_PORTAL || $uuid_user_type==MEMBER_PORTAL){
            $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
            $this->db->from(TBL_PREFIX . 'users as u');
            $this->db->join(TBL_PREFIX . 'member as sub_m', "u.id = sub_m.uuid", "INNER");
            $this->db->where("sub_m.uuid = ".$alias.".".$column_name, null, false);
            $this->db->limit(1);
            return $this->db->get_compiled_select();
        }else if ($uuid_user_type==ORGANISATION_PORTAL){
            $this->db->select("CONCAT_WS(' ', p.firstname,p.lastname)");
            $this->db->from(TBL_PREFIX . 'users as u');
            $this->db->join(TBL_PREFIX . 'person as p', "u.id = p.uuid", "INNER");
            $this->db->where("p.uuid = ".$alias.".".$column_name, null, false);
            $this->db->limit(1);
            return $this->db->get_compiled_select();
        }
    }

    /** Uploads files into local server and then upload into S3
     * 
     * @param $reqData {obj} request data
     * 
     * @see mkdir
     * @see move_uploaded_file
     * @see upload_from_app_to_s3
     * 
     * @return {$response} {array} reponse with
     */
    public function upload_editor_assets($reqData) {
        $response = ['status' => FALSE, 'message' => 'Something went wrong'];

        $adminId = $reqData->adminId ?? 0;
        
        if (!empty($_FILES['file']['name'])) {
            $dir_name = $reqData->dir_name ?? NULL;
            
            $this->load->library('AmazonS3');
            require_once APPPATH . 'Classes/common/Aws_file_upload.php';
            $awsFileupload = new Aws_file_upload();
            $target_dir = './uploads/' . S3_TINYMCE_ATTACHMENT_UPLOAD_PATH . $dir_name;
            $target_file = $target_dir .'/'. basename($_FILES["file"]["name"]);
            
            #Create directory if its not available
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            #Upload files into local app server
            move_uploaded_file($_FILES['file']['tmp_name'], $target_file);

            $config['upload_path'] = S3_TINYMCE_ATTACHMENT_UPLOAD_PATH;
            $config['directory_name'] = $dir_name;
            $config['file_name'] =  basename($_FILES["file"]["name"]);
            $config['uplod_folder'] = $this->appserver_file_path . './uploads/';            
            $config['bucket_name'] = getenv("AWS_EDITOR_S3_BUCKET") ?? 'hcm-development-cms';

            #Upload files into S3 with custom s3 bucket name
            $s3documentAttachment = $awsFileupload->upload_from_app_to_s3($config, FALSE);

            if (isset($s3documentAttachment) || $s3documentAttachment['aws_uploaded_flag']) {

                $data['file_name'] = $s3documentAttachment['file_name'];
                $data['file_path'] = $s3documentAttachment['file_path'];
                $data['file_type'] = $s3documentAttachment['file_type'];
                $data['file_size'] = $s3documentAttachment['file_size'];
                $data['file_ext'] = $s3documentAttachment['file_ext'];
                $data['aws_response'] = $s3documentAttachment['aws_response'];
                $data['aws_object_uri'] = $s3documentAttachment['aws_object_uri'];
                $data['aws_file_version_id'] = $s3documentAttachment['aws_file_version_id'];
                $data['aws_uploaded_flag'] = 0;
                $data['created_by'] = $adminId;
                $data['updated_by'] = $adminId;
                $data['created'] = DATE_TIME;
                $data['updated'] = DATE_TIME;

                #Insert the email attachment property into table
                $this->basic_model->insert_records('editor_attachments_property', $data);
                
                $response = ["status" => TRUE,'location' => $data['aws_object_uri']];
                
                #Remove the files from server once its uploaded
                if(getenv('IS_APPSERVER_UPLOAD') != 'yes' && file_exists($target_file)) {
                    
                    unlink($target_file);
                    
                }
            }

                     
        }

        return $response;
    }

    /**
     * Get viewed by log data based on entity type
     * 
     * @param $entityId {int} Entity id Listing record id
     * @param $entityType {int} Example [1 - Application / 2 - Applicant / 3 - Leads etc]
     *
     * @return $return {array} return list viewed log list
     * 
     */
    public function getViewedLogByEntityIDs($entityId, $entityType) {
        if(!$entityId) {
            return [];
        }

        $this->db->select(['vl.entity_id', "concat(m.firstname,' ',m.lastname) as viewed_by", 'vl.viewed_date', 'vl.viewed_by as viewed_by_id']);
        $this->db->from(TBL_PREFIX . 'viewed_log as vl');
        $this->db->join(TBL_PREFIX . 'member as m', 'vl.viewed_by = m.uuid  and m.archive = 0');
        $this->db->where_in('vl.entity_id', $entityId);
        $this->db->where('vl.entity_type', $entityType);

        $result = $this->db->get();
        return $result->result();        
    }
              
    // Insert file download token for download validation
    function insert_file_download_validation_token($token) {
        $this->basic_model->insert_records('file_download_validation', ['token' => $token]);
        return TRUE;
    }

    function check_is_bu_unit($data){
        if(empty($data->business_unit['is_super_admin']) && !empty($data->business_unit['bu_id'])){
            return true;
        }
        return false;
    }
    function get_business_units_dynamically($table_name, $where, $column ='') {

        $bu_data =  $this->basic_model->get_record_where($table_name, $column??'bu_id', $where);
        if(!empty($bu_data[0]->bu_id)) {
            return $bu_data[0]->bu_id;
        }
        return FALSE;
	}
}
