<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once(dirname(__FILE__) . '/Lead_model.php');
class Leadhistory_model extends Lead_model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Return history items of a Lead
     * @param $data object 
     * @return array
     */
    public function get_field_history($data)
    {
        $items = $this->db->select(['h.id', 'h.id as history_id', 'f.id as field_history_id', 'f.field', 'f.value', 'f.prev_val', 'h.created_at', 'CONCAT(m.firstname, \' \', m.lastname) as created_by', 'h.created_at', 'hf.desc as feed_title', 'hf.id as feed_id'])
            ->from(TBL_PREFIX . 'lead_history as h')
            ->where(['h.lead_id' => $data->lead_id])
            ->join(TBL_PREFIX . 'lead_field_history as f', 'f.history_id = h.id', 'left')
            ->join(TBL_PREFIX . 'lead_history_feed as hf', 'hf.history_id = h.id', 'left')
            ->join(TBL_PREFIX . 'leads as l', 'l.id = h.lead_id', 'left')
            ->join(TBL_PREFIX . 'member as m', 'm.uuid = h.created_by', 'left')
            // ->join(TBL_PREFIX . 'references as r', 'r.id = h.lead_id', 'left')
            ->order_by('h.id', 'DESC')
            ->get()->result();
            // print_r($this->db->last_query());
        $lead_statuses = $this->get_lead_status();

        $this->load->model('Feed_model');
        $related_type = $this->Feed_model->get_related_type('lead');

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
                case 'lead_status':
                    $accessor = function ($field) {
                        return $field['value'];
                    };
                    $item->value = isset($item->value) ? ($this->map_history_field($lead_statuses, $item->value,$accessor)['label']) ?? 'Open' : 'Open';
                    $item->prev_val = isset($item->prev_val) ? ($this->map_history_field($lead_statuses, $item->prev_val,$accessor)['label']) ?? 'Open' : 'Open';
                    break;

                case 'lead_owner':
                    $owner = $this->db->from(TBL_PREFIX . 'member as m')->select('CONCAT(m.firstname, \' \', m.lastname) as user')->where(['id' => $item->value])->get()->result();
                    $prev_owner = $this->db->from(TBL_PREFIX . 'member as m')->select('CONCAT(m.firstname, \' \', m.lastname) as user')->where(['id' => $item->prev_val])->get()->result();
                    $item->value = !empty($owner) ? $owner[0]->user : 'N/A';
                    $item->prev_val = !empty($prev_owner) ? $prev_owner[0]->user : 'N/A';
                break;
                case 'lead_source_code':
                    $sources = $this->get_lead_source();
                    $accessor = function ($field) {
                        return $field['value'];
                    };
                    $item->value = isset($item->value) ? ($this->map_history_field($sources, $item->value,$accessor)['label']) ?? 'N/A' : 'N/A';
                    $item->prev_val = isset($item->prev_val) ? ($this->map_history_field($sources, $item->prev_val,$accessor)['label']) ?? 'N/A' : 'N/A';
                    break;
                case 'lead_service_type':
                    $owner = $this->db->from(TBL_PREFIX . 'references as r')->select('r.display_name')->where(['id' => $item->value])->get()->result();
                    $prev_owner = $this->db->from(TBL_PREFIX . 'references as r')->select('r.display_name')->where(['id' => $item->prev_val])->get()->result();
                    $item->value = !empty($owner) ? $owner[0]->display_name : 'N/A';
                    $item->prev_val = !empty($prev_owner) ? $prev_owner[0]->display_name : 'N/A';
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
        
        krsort($feed);
        $feed = array_values($feed);
        return $feed;
    }
    /**
     * Create history item for each change field
     * @param array $existingLead Existing lead data
     * @param array $dataToBeUpdated Modified data of Lead
     * @return void
     */
    public function updateHistory($existingLead, $dataToBeUpdated, $adminId) {
        if (!empty($dataToBeUpdated)) {
            $new_history = $this->db->insert(
                TBL_PREFIX . 'lead_history',
                [
                    'lead_id' => $existingLead['id'],
                    'created_by' => $adminId,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            );
            $history_id = $this->db->insert_id();
            foreach($dataToBeUpdated as $field => $new_value) {
                if (array_key_exists($field, $existingLead) && $existingLead[$field] != $new_value && !empty($new_history)) {
                    $this->create_field_history($history_id, $existingLead['id'], $field, $new_value, $existingLead[$field]);
                }
            }
        }
    }

    /**
     * Create history record to be used for all history items in the update
     * @param int $history_id Id of related update history
     * @param int $leadId
     * @param string $fieldName
     * @param string $new_value
     * @param string $oldValue
     * @return int Last insert id
     */
    public function create_field_history($history_id, $leadId, $fieldName, $newValue, $oldValue) {
        return $this->db->insert(TBL_PREFIX . 'lead_field_history', [
            'history_id' => $history_id,
            'lead_id' => $leadId,
            'field' => $fieldName,
            'prev_val' => $oldValue,
            'value' => $newValue ?? ''
        ]);
    }

    /**
     * Map id to related value
     * @param array $types
     * @param int $id
     * @param callable $accessor
     */
    private function map_history_field($types, $id, $accessor)
    {
        $map_fn = function ($id) use (&$types, $accessor) {
            return current(array_filter(
                $types,
                function ($src_type) use ($id, $accessor) {
                    return $accessor($src_type) == $id;
                }
            ));
        };

        return $map_fn($id);
    }
}
