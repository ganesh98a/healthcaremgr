<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'Classes/websocket/Websocket.php';

class Out_of_office_model extends CI_Model {

    function __construct() {

        parent::__construct();
    }

    function get_out_of_office_admin_name_for_create($reqData) {
        $this->db->select("concat_ws(' ',firstname,lastname) as label");
        $this->db->select("(select email from tbl_member_email as me where me.memberId = m.id AND me.archive = 0 AND me.primary_email = 1 limit 1) as email");
        $this->db->select("m.id as value");
        $this->db->from('tbl_member as m');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"');

        $this->db->like("concat_ws(' ',firstname,lastname)", $reqData->query);
        if (!empty($reqData->previous_selected)) {
            $this->db->where("m.id !=", $reqData->previous_selected);
        }

        $this->db->where("m.archive", 0);
        $this->db->where("m.status", 1);
        $query = $this->db->get();
        return $query->result();
    }

    function get_admin_name_by_filter($reqData) {
        $this->db->select("concat_ws(' ',firstname,lastname) as label");
        $this->db->select("m.id as value");
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

    function create_update_out_of_office_message($objMess) {
        $out_of_office = [
            "create_for" => $objMess->getCreate_for(),
            "from_date" => $objMess->getFrom_date(),
            "end_date" => $objMess->getEnd_date(),
            "replace_by" => $objMess->getReplace_by(),
            "additional_message" => $objMess->getAdditional_message(),
            "updated" => DATE_TIME,
            "archive" => 0,
        ];

        if (!empty($objMess->getId())) {
            return $this->basic_model->update_records("out_of_office_message", $out_of_office, ['id' => $objMess->getId()]);
        } else {
            $out_of_office["created"] = DATE_TIME;
            $this->basic_model->insert_records("out_of_office_message", $out_of_office, $multiple = FALSE);
            return $objMess->getId();
        }
    }

    function check_already_exist_out_of_office($objMess) {
        $from_date = $objMess->getFrom_date();
        $end_date = $objMess->getEnd_date();

        $this->db->select("from_date");
        $this->db->from("tbl_out_of_office_message");
        $this->db->where("create_for", $objMess->getCreate_for());
        $this->db->where("archive", 0);
        $this->db->where("id !=" . $objMess->getId(), null, false);
        $this->db->group_start();
        $this->db->where("'" . $from_date . "' between from_date AND end_date", null, false);
        $this->db->or_where("'" . $end_date . "' between from_date AND end_date", null, false);
        $this->db->or_where("from_date between '" . $from_date . "' AND '" . $end_date . "'", null, false);
        $this->db->or_where("end_date between '" . $from_date . "' AND '" . $end_date . "'", null, false);
        $this->db->group_end();

        $query = $this->db->get();
        return $query->result();
    }

    function get_out_of_office_calander_listing($reqData) {
        $reqData->date = $reqData->date ?? DATE_TIME;
        $date = DateFormate($reqData->date, "Y-m-d");

        if (!empty((array) $reqData->selected_admin)) {
            $adminId = array_keys((array) $reqData->selected_admin);
            $this->db->where_in("create_for", $adminId);
        }

        if (!empty($reqData->department_option)) {
            $departments = [];
            foreach ($reqData->department_option as $val) {
                if (!empty($val->checked)) {
                    $departments[] = $val->value;
                }
            }

            if (!empty($departments)) {
                $this->db->join("tbl_member as m", "m.id = ooom.create_for", "INNER");
                $this->db->join("tbl_department as d", "d.id = m.department", "INNER");
                $this->db->where_in("d.id", $departments);
            }
        }

        $this->db->select(["ooom.from_date as start", "ooom.from_date as end", "ooom.id"]);
        $this->db->select("(select concat_ws(' ',firstname,lastname) from tbl_member as m INNER JOIN tbl_department as d ON d.id = m.department AND d.short_code = 'internal_staff' where m.id = ooom.create_for) as title");
        $this->db->from("tbl_out_of_office_message as ooom");
        $this->db->where("ooom.archive", 0);
        $this->db->where("month(ooom.from_date) = month('" . $date . "')", null, false);
        $this->db->where("month(ooom.end_date) = month('" . $date . "')", null, false);

        $query = $this->db->get();

        return $query->result();
    }

    function get_out_of_office_details($reqData) {
        $outOfOfficeId = $reqData->outOfOfficeId;

        $this->db->select(["from_date", "end_date", "id", "additional_message", "replace_by as replace_byId", "create_for as create_forId"]);
        $this->db->select("(select concat(concat_ws(' ',firstname,lastname),'@@BRKR@@',me.email) from tbl_member as m 
INNER JOIN tbl_department as d ON d.id = m.department 
INNER JOIN tbl_member_email as me ON me.memberId = m.id AND me.archive = 0 AND me.primary_email where m.id = tbl_out_of_office_message.replace_by) as repl");

        $this->db->select("(select concat(concat_ws(' ',firstname,lastname),'@@BRKR@@',me.email) from tbl_member as m 
INNER JOIN tbl_department as d ON d.id = m.department 
INNER JOIN tbl_member_email as me ON me.memberId = m.id AND me.archive = 0 AND me.primary_email where m.id = tbl_out_of_office_message.create_for) as crt");

        $this->db->from("tbl_out_of_office_message");
        $this->db->where("archive", 0);
        $this->db->where("id", $outOfOfficeId);

        $query = $this->db->get();
        $result = $query->row();

        if (!empty($result)) {
            $x = explode("@@BRKR@@", $result->repl);
            $label = $x[0] ?? '';
            $email = $x[1] ?? '';

            $result->replace_by = ['label' => $label, "email" => $email, "value" => $result->replace_byId];
            unset($result->repl);

            $x = explode("@@BRKR@@", $result->crt);
            $label = $x[0] ?? '';
            $email = $x[1] ?? '';

            $result->create_for = ['label' => $label, "email" => $email, "value" => $result->create_forId];
            unset($result->crt);
            
            $result->its_editable =  (strtotime(DateFormate($result->end_date, "Y-m-d")) >= strtotime(DateFormate(DATE_TIME, "Y-m-d")) ? true : false);
        }

        return $result;
    }

}
