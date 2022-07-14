<?php

class Schedule_ndis_model extends Basic_Model {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Save ndis support type duration
     * @param {array} $data
     * @param {int} $shift_id
     * @param {int} $adminId
     */
    public function populate_ndis_suport_type_duration($data, $shift_id, $adminId, $portal) {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] != 0) {
            $id = $data['id'];
        } else {
            $id = $shift_id;
        }
        
        $updateWhere = [ 'shift_id' => $id ];
        if ($portal) {
            $updateWhere  = ['shift_id' => $id, 'category' => 2];
        }

        $this->update_records('shift_ndis_support_duration', array('archive' => '1'), $updateWhere);

        // Scheduled line items
        if (array_key_exists('scheduled_support_type_duration', $data) == true && count($data['scheduled_support_type_duration']) > 0) {
            $ndis_support_duration = $data['scheduled_support_type_duration'];

            $this->update_save_duration($ndis_support_duration, $adminId, $id);
        }
        
        // Scheduled line items
        if (array_key_exists('actual_support_type_duration', $data) == true && count($data['actual_support_type_duration']) > 0) {
            $ndis_support_duration = $data['actual_support_type_duration'];

            $this->update_save_duration($ndis_support_duration, $adminId, $id);
        }
    }

    /**
     * Save update save duration
     */
    function update_save_duration($ndis_support_duration, $adminId, $id) {
        $insData = [];
        $updData = [];
        foreach ($ndis_support_duration as $item) {
            $duration = $item['duration'];
            foreach ($duration as $value) {
                $value = (array) $value;
                $dur_item = [];
                $dur_item['shift_id'] = $id;
                $dur_item['category'] = $value['category'];
                $dur_item['support_type'] = $value['support_type'];
                $dur_item['duration'] = $value['duration'] ?? null;
                $dur_item['date'] = $value['date'] ?? null;
                $dur_item['day'] = $value['day'] ?? null;
                $dur_item['order'] = $value['order'] ?? 0;
                $dur_item['archive'] = 0;                

                if ( isset($value['id']) && !empty($value['id']) && $value['id']) {
                    $dur_item['id'] = $value['id'];
                    $dur_item['updated_by'] = $adminId;
                    $dur_item['updated_at'] = DATE_TIME;
                    $updData[] = $dur_item;
                } else {
                    $dur_item['created_by'] = $adminId;
                    $dur_item['created_at'] = DATE_TIME;
                    $insData[] = $dur_item;
                }
            }
            
        }
        if (!empty($insData)) {
            $this->basic_model->insert_records('shift_ndis_support_duration', $insData, true);
        }
        
        if (!empty($updData)) {
            $this->basic_model->insert_update_batch('update', 'shift_ndis_support_duration', $updData, 'id' );
        }
    }

    /**
     * Get list of ndis support type duration
     * @param {obj} reqData
     */
    public function get_support_duration_by_shift_id($shift_id, $category, $stDuration, $data) {
        $item_ary = [];
        if ($shift_id && $shift_id != '') {

            $this->db->from(TBL_PREFIX . 'shift_ndis_support_duration as snsd');
            $this->db->select(array(
            'snsd.id', 
            'snsd.duration as duration',
            '"" as duration_txt',
            'snsd.category as category',
            'snsd.support_type as support_type',
            'snsd.order as order',
            'snsd.date as date',
            'snsd.day as day',
            ));

            $this->db->where(array('snsd.archive' => 0, 'snsd.shift_id' => $shift_id, 'snsd.category' => $category));
            $this->db->order_by("snsd.order", "ASC");
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $item_ary = $query->result();

            $sorted = [];
            $required = true;
            $get_day_count = dayDifferenceBetweenDate($data['start_date'], $data['end_date']);
            if ($get_day_count > 0) {
                $required = false;
            }

            foreach ($item_ary as $key => $li) {
                
                $li->duration_raw = $li->duration;
                $hr_check = explode(":", $li->duration);                
                $hour = (!empty($hr_check) && $hr_check[0] == "00") ? "00" : date('H', strtotime($li->duration));                
                $minutes = date('i', strtotime($li->duration));
                if ($hour != '00') {
                    $hourTxt = 'h';

                    $li->duration_txt = intVal($hour).$hourTxt;
                } else {
                    $li->duration_txt = '';
                }

                if ($minutes != '00') {
                    $li->duration_txt = $li->duration_txt.' '.intVal($minutes).'m';
                }

                $li->duration =  date('H:i', strtotime($li->duration));
                $li->error = false;
                $li->errorTxt = ''; 
                $li->required = $required;
                $item_ary[$key] = $li;
                $li->date;
                $sorted[$li->date][] = $li;
            }            

            # array form
            foreach($stDuration as $s_key => $s_item) {
                $date = $s_item['date'];
                $day = $s_item['day'];
                if (isset($sorted) && isset($sorted[$date])) {
                    $reData = $sorted[$date];
                    $stDuration[$s_key]['duration'] = $sorted[$date];
                }
            }
        }
        return $stDuration;
    }    
}