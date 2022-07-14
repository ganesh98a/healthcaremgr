<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model file that act as both data provider 
 * or data repository for RecruitmentForm controller actions
 */
class Recruitment_form_applicant_history_model extends CI_Model {

    public function __construct() {
        parent::__construct();

        $this->load->helper(['array']);
    }

    public $form_applicant_stage_status = [
        "1" => "Draft",
        "2" => "Completed",
    ];
    // Update only notes if its phone interview
    public function updateNotesHistory($form_applicant_id,$notes,$adminId) {
        $new_history = $this->db->insert(
            TBL_PREFIX . 'form_applicant_history',
            [
                'form_applicant_id' => $form_applicant_id,
                'created_by' => $adminId,
                'created_at' => date('Y-m-d H:i:s')
            ]
        );
        $history_id = $this->db->insert_id();

        if ($notes != '' && $history_id!='') {
            $field = 'notes';
            $new_value = 'updated';
            $field_value = 'updated';
            $this->create_field_history($history_id, $form_applicant_id, $field, $new_value, $field_value);
        }
    }


    /**
     * Create history item for each change field
     * @param array $existingFormApplicant Existing form data
     * @param array $dataToBeUpdated Modified data of Lead
     * @return void
     */
    public function updateHistory($existingFormApplicant, $dataToBeUpdated, $adminId) {
        if (!empty($dataToBeUpdated)) {
            $new_history = $this->db->insert(
                TBL_PREFIX . 'form_applicant_history',
                [
                    'form_applicant_id' => $existingFormApplicant['id'],
                    'created_by' => $adminId,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            );
            $history_id = $this->db->insert_id();
            foreach($dataToBeUpdated as $field => $new_value) {               
                    if (array_key_exists($field, $existingFormApplicant) && $existingFormApplicant[$field] != $new_value && !empty($new_history)) {
                        $this->create_field_history($history_id, $existingFormApplicant['id'], $field, $new_value, $existingFormApplicant[$field]);
                }

            }
        }
    }

    /**
     * Create history record to be used for all history items in the update
     * @param int $history_id Id of related update history
     * @param int $form_applicant_id
     * @param string $fieldName
     * @param string $new_value
     * @param string $oldValue
     * @return int Last insert id
     */
    public function create_field_history($history_id, $form_applicant_id, $fieldName, $newValue, $oldValue) {
        return $this->db->insert(TBL_PREFIX . 'form_applicant_field_history', [
            'history_id' => $history_id,
            'form_applicant_id' => $form_applicant_id,
            'field' => $fieldName,
            'prev_val' => $oldValue,
            'value' => $newValue ?? ''
        ]);
    }

    /**
     * Return history items of a Applications
     * @param $data object
     * @return array
     */
    public function get_field_history($data)
    {
        $items = $this->db->select(['h.id','hf.created_at', 'h.id as history_id', 'f.id as field_history_id', 'f.field', 'f.value', 'f.prev_val', 'h.created_at', 'CONCAT(m.firstname, \' \', m.lastname) as created_by', 'h.created_at', 'hf.desc as feed_title', 'hf.id as feed_id'])
            ->from(TBL_PREFIX . 'form_applicant_history as h')
            ->where(['h.form_applicant_id' => $data->form_id])
            ->join(TBL_PREFIX . 'form_applicant_field_history as f', 'f.history_id = h.id', 'left')
            ->join(TBL_PREFIX . 'form_applicant_history_feed as hf', 'hf.history_id = h.id', 'left')
            //->join(TBL_PREFIX . 'leads as l', 'l.id = h.lead_id', 'left')
            ->join(TBL_PREFIX . 'member as m', 'm.id = h.created_by', 'left')
            ->order_by('h.id', 'DESC')
            ->get()->result();
        $form_statuses = $this->get_form_applicant_stage_status_history();

        $this->load->model('Feed_model');
        $related_type = $this->Feed_model->get_related_type('form_applicant');
        
        $feed = [];
        // map fields to rendered values
        foreach ($items as $item) {

            $item->related_type = $related_type;
            $item->expanded = true;
            $item->feed = false;
            $item->comments = [];
            $history_id = $item->history_id;
            // history comments
            $comments = $this->Feed_model->get_comment_by_history_id($item->history_id, $related_type);
            $item->comments = $comments;
            $item->comments_count = count($comments);
            $item->comment_create = false;
            $item->comment_post = false;
            $item->comment_desc = '';

           
            switch ($item->field) {
               
                case 'status':
                    foreach ($form_statuses['data'] as $key => $val){                        
                        if($val['value'] == $item->value){
                            $item->value = $val['label'] ?? 'Draft';
                            continue;
                        }
                        if($val['value'] == $item->prev_val){
                            $item->prev_val = $val['label'] ?? 'Draft';
                            continue;
                        }
                    }
                break;
                case 'NULL':
                case '':
                    $item->feed = true;
                    break;
                default:
                    $item->value = !empty($item->value) ? $item->value : 'N/A';
                    $item->prev_val = !empty($item->prev_val) ? $item->prev_val : 'N/A';
                    break;
            }
            if ($item->feed_id == '' || $item->feed_id == NULL) {
                $item->feed = false;
            }
            if (($item->field_history_id != '' && $item->field_history_id != NULL) || ($item->feed_id != '' && $item->feed_id != NULL)) {
                $feed[$history_id][] = $item;
            }
        }
        $feed = array_values($feed);
        return $feed;
    }

    /**
     * fetches all the application form  statuses
     */
    public function get_form_applicant_stage_status_history() {
        $data = null;
        foreach($this->form_applicant_stage_status as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }
}
