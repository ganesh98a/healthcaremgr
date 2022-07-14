<?php

defined('BASEPATH') or exit('No direct script access allowed');

class OpportunityItemHistory_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create history item for each change field
     * @param array $existingItems Existing data
     * @param array $updatedItems updated data
     * @return void
     */
    public function updateHistory($oldItems, $newItems, $opportunityId, $adminId) {
        $existingItems = [];
        $updatedItems = [];
        unset($newItems['opportunity_id']);
        if (!empty($oldItems) || !empty($newItems)) {
            foreach($oldItems as $item) {
                $itemObj = new stdClass();
                $itemObj->id = $item->incr_id_opportunity_items;
                $itemObj->qty = $item->qty;
                $itemObj->amount = $item->amount;
                $itemObj->selected = true;
                $existingItems[$item->incr_id_opportunity_items] = $itemObj;
            }
            foreach($newItems as $item) {
                $itemObj = new stdClass();
                $itemObj->id = $item['incr_id_opportunity_items'];
                $itemObj->qty = $item['qty'];
                $itemObj->amount = $item['amount'];
                $itemObj->selected = $item['selected'];
                $updatedItems[$item['id']] = $itemObj;
            }
            //create history record for each field            
            foreach($updatedItems as $updatedItem) {
                $existing_item = '';
                $history_id = 0;
                //check if item is unchanged
                if (array_key_exists($updatedItem->id, $existingItems) && $existingItems[$updatedItem->id] == $updatedItem) {
                    continue;
                }
                if (array_key_exists($updatedItem->id, $existingItems)) {
                    $existing_item = $existingItems[$updatedItem->id];
                }
                $history = $this->db->insert(
                    TBL_PREFIX . 'opportunity_item_history',
                    [
                        'opportunity_id' => $opportunityId,
                        'opportunity_item_id' => $updatedItem->id,
                        'created_by' => $adminId,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                );
                if ($history) {
                    $history_id = $this->db->insert_id();
                }
                if (!empty($existing_item)) {    
                    if($updatedItem->qty != $existing_item->qty) {
                        $this->create_field_history($history_id, $updatedItem->id, 'quantity', $updatedItem->qty, $existing_item->qty);
                    }
                    if($updatedItem->amount != $existing_item->amount) {
                        $this->create_field_history($history_id, $updatedItem->id, 'amount', $updatedItem->amount, $existing_item->amount);
                    }
                    if($updatedItem->selected != $existing_item->selected) {
                        $this->create_field_history($history_id, $updatedItem->id, 'archive', date('Y-m-d H:i:s'), '');
                    }
                } else {
                    $this->create_field_history($history_id, $updatedItem->id, 'created', date('Y-m-d H:i:s'), '');
                }               
            }
        }
        return true;
    }

    /**
     * Create history record to be used for all history items in the update
     * @param int $historyId Id of related update history
     * @param int $opportunity_item_id
     * @param string $fieldName
     * @param string $new_value
     * @param string $oldValue
     * @return int Last insert id
     */
    public function create_field_history($historyId, $opportunityItemId, $fieldName, $newValue, $oldValue) {
        return $this->db->insert(TBL_PREFIX . 'opportunity_item_field_history', [
            'history_id' => $historyId,
            'opportunity_item_id' => $opportunityItemId,
            'field' => $fieldName,
            'prev_val' => $oldValue,
            'value' => $newValue
        ]);
    }
}
