<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tv_display_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    public function get_edit_slide_options() {
        $this->db->select(["id as value", "title as label"]);
        $this->db->from("tbl_module_title");
        $this->db->where("its_tv_module", 1);
        $this->db->where("archive", 0);
        $tv_module = $this->db->get()->result();

        $this->db->select(["mg.id as value", "mg.name as label", "mg.moduleId"]);
        $this->db->from("tbl_module_graph as mg");
        $this->db->where("archive", 0);
        $module_graph = $this->db->get()->result();

        $module_gr = [];
        if (!empty($module_graph)) {
            foreach ($module_graph as $val) {
                $module_gr[$val->moduleId][] = $val;
            }
        }

        if (!empty($tv_module)) {
            foreach ($tv_module as $val) {
                $module_gr[$val->value] = $module_gr[$val->value] ?? [];
            }
        }

        return ["tv_module" => $tv_module, "module_graph" => $module_gr];
    }

    public function get_admin_tv_slide($adminId) {
        $this->db->select(["moduleId", "module_graphId", "id"]);
        $this->db->from("tbl_module_tv_slide as mts");
        $this->db->where("mts.memberId", $adminId);
        $this->db->where("mts.archive", 0);
        $this->db->order_by("mts.slide_position", "ASC");

        $query = $this->db->get();
        $result = $query->result();

        return $result;
    }

    public function save_admin_tv_slide($reqData) {
        if (!empty($reqData->admin_tv_slide)) {
            $tv_slide = [];

            foreach ($reqData->admin_tv_slide as $index => $val) {
                $position = $index + 1;

                if (!empty($val->remove) && !empty($val->id)) {
                    $this->basic_model->update_records("module_tv_slide", ["archive" => 1], ["id" => $val->id]);
                    continue;
                }

                $up = [
                    'memberId' => $reqData->adminId,
                    'moduleId' => $val->moduleId,
                    'module_graphId' => $val->module_graphId,
                    'slide_position' => $position,
                    'created' => DATE_TIME,
                    'archive' => 0,
                ];

                if (!empty($val->id)) {
                    $this->basic_model->update_records("module_tv_slide", $up, ["id" => $val->id]);
                } else {
                    $tv_slide[] = $up;
                }
            }

            if (!empty($tv_slide)) {
                $this->basic_model->insert_records("module_tv_slide", $tv_slide, $multiple = true);
            }
        }
    }

}
