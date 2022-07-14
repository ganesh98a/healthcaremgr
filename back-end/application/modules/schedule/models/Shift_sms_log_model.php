<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Shift_sms_log_model extends Basic_model {
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'shift_sms_log';
        $this->replace_column = [
            'source_id' => 'shift_id'
        ];
        $this->save_fields = [
            'shift_id',
            'sender_id',
            'recipient_id',
            'content',
            'response',
            'created_by',
            'created_at'
        ];
    }

    /**
     * Save log - shift sms
     */
    public function save_log($data) {

        $insData = [];
        $insArray = [];
        
        $insData['shift_id'] = $data['source_id'];
        $insData['sender_id'] = $data['sender_id'];
        $insData['recipient_id'] = $data['recipient_id'];
        $insData['response'] = json_encode($data['response']);
        $insData['content'] = $data['content'];
        $insData['created_by'] = $data['created_by'];
        $insData['created_at'] = DATE_TIME;
        $insArray[] = $insData;

        $this->basic_model->insert_records($this->table_name, $insArray, true);
    }
}