<?php

class Ndis_error_fix_model extends Basic_Model {

    public function __construct()
    {
        parent::__construct();        
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
    }
    /**
     * Update the shift ndis warning
     * - Async API call
     * @param {array} $data - service agreement id & update service booking or contract
     */
    public function update_shift_ndis_warning($data) {
        $this->load->model('schedule/Schedule_model');

        $participant_id = '';
        $service_agreement_id = $data['service_agreement_id'] ?? '';
        $SBUpdate = false;
        $SADUpdate = false;
        $SAUpdateLi = false;

        if (isset($data['update_service_booking']) && $data['update_service_booking'] == 1) {
            $SBUpdate = true;
        }
        if (isset($data['update_service_docusign']) && $data['update_service_docusign'] == 1) {
            $SADUpdate = true;
        }
        if (isset($data['update_sa_and_li']) && $data['update_sa_and_li'] == 1) {
            $SBUpdate = $SADUpdate = $SAUpdateLi = true;
        }
        # Service agreement id should not null
        if (!empty($service_agreement_id)) {
            # get service agreement 
            $this->db->select(['sa.participant_id']);
            $this->db->from('tbl_service_agreement as sa');
            $this->db->where('sa.archive', 0);
            $this->db->where('sa.status', 5); # status - 5 = Active
            $this->db->where('sa.id', $service_agreement_id);
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $saDetail = $query->row_array();
            # Get service agreement participant id
            if (isset($saDetail) && !empty($saDetail['participant_id'])) {
                $participant_id = $saDetail['participant_id'];
            }
        }

        # Get NDIS type id using keyname is equals to ndis
        $serviceType = $this->basic_model->get_row('member_role', ['id'], ['name' => 'ndis']);
        $service_type_id = $serviceType->id ?? '';
        
        # Participant id and service type id should not null
        if ($participant_id !='' && $participant_id != null && $service_type_id != '' && $service_type_id != null) {
            $this->db->select(['s.*']);
            $this->db->from('tbl_shift as s');
            $this->db->where('s.not_be_invoiced', 1);
            $this->db->where('s.archive', 0);
            $this->db->where('s.account_type', 1); # type = 1 participant
            $this->db->where('s.account_id', $participant_id);
            $this->db->where('s.role_id', $service_type_id);
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $ndisShift = $query->result_array();

            foreach ($ndisShift as $key => $shift) {
                $shiftUpData = [];
                $shift_id = $shift['id'];

                # Get scheduled ndis values
                $scheduledNDIS = [];
                $scheduledNDIS['scheduled_sa_id'] = $shift['scheduled_sa_id'];
                $scheduledNDIS['scheduled_sb_status'] = $shift['scheduled_sb_status'];
                $scheduledNDIS['scheduled_docusign_id'] = $shift['scheduled_docusign_id'];

                # Get scheduled service agreement
                $scheduledSAData = [];
                $account = [];
                $account['value'] = $shift['account_id'];
                $account['account_type'] = $shift['account_type'];
                $scheduledSAData['account'] = (object) $account;
                $scheduledSAData['start_date'] = date('Y-m-d', strtotime($shift['scheduled_start_datetime']));
                $scheduledSAData['end_date'] = date('Y-m-d', strtotime($shift['scheduled_end_datetime']));
                $scheduledSAData['section'] = 'scheduled';
                $getScheduledSA = $this->Schedule_model->get_service_agreement((object) $scheduledSAData);
                
                $scheduledNDISTemp = [];
                if (isset($getScheduledSA['status']) && isset($getScheduledSA['data']) && !empty($getScheduledSA['data'])) {
                    $sch_temp_data = $getScheduledSA['data'];
                    $scheduledNDISTemp['scheduled_sa_id'] = $sch_temp_data['service_agreement_id'];
                    $scheduledNDISTemp['scheduled_docusign_id'] = $sch_temp_data['docusign_id'];
                    $sch_sb_status = 0;
                    # Service booking is available or not
                    if ($sch_temp_data['service_booking_needed'] === true) {
                        $sch_sb_status = 1;
                    }
                    # Service booking is needed but not exist - 2 || Service booking is needed but not signed - 3
                    if ($sch_temp_data['rule'] == 2 || $sch_temp_data['rule'] == 3) {
                        $sch_sb_status = $sch_temp_data['rule'];
                    }

                    $scheduledNDISTemp['scheduled_sb_status'] = $sch_sb_status;
                }
                
                # Set Update values - scheduled
                if (($scheduledNDIS['scheduled_sa_id'] == '' || $scheduledNDIS['scheduled_sa_id'] == null) && (!empty($scheduledNDISTemp['scheduled_sa_id']))) {
                    $shiftUpData['scheduled_sa_id'] = $scheduledNDISTemp['scheduled_sa_id'];
                }

                if (($scheduledNDIS['scheduled_docusign_id'] == '' || $scheduledNDIS['scheduled_docusign_id'] == null) && (!empty($scheduledNDISTemp['scheduled_docusign_id'])) && $SADUpdate === true) {
                    $shiftUpData['scheduled_docusign_id'] = $scheduledNDISTemp['scheduled_docusign_id'];
                }
                
                if (!empty($scheduledNDISTemp['scheduled_sb_status']) && $scheduledNDIS['scheduled_sb_status'] != $scheduledNDISTemp['scheduled_sb_status'] && $SBUpdate === true) {
                    $shiftUpData['scheduled_sb_status'] = $scheduledNDISTemp['scheduled_sb_status'];
                }                
                
                # Get actual ndis values
                $actualNDIS = [];
                $actualNDIS['actual_sa_id'] = $shift['actual_sa_id'];
                $actualNDIS['actual_sb_status'] = $shift['actual_sb_status'];
                $actualNDIS['actual_docusign_id'] = $shift['actual_docusign_id'];

                # Get actual service agreement
                if (!empty($shift['actual_start_datetime']) && !empty($shift['actual_end_datetime']) && $shift['actual_start_datetime'] != '0000-00-00 00:00:00' && $shift['actual_end_datetime'] != '0000-00-00 00:00:00') {
                    $actualSAData = [];
                    $account = [];
                    $account['value'] = $shift['account_id'];
                    $account['account_type'] = $shift['account_type'];
                    $actualSAData['account'] = (object) $account;
                    $actualSAData['start_date'] = date('Y-m-d', strtotime($shift['actual_start_datetime']));
                    $actualSAData['end_date'] = date('Y-m-d', strtotime($shift['actual_end_datetime']));
                    $actualSAData['section'] = 'actual';
                    $getActualSA = $this->Schedule_model->get_service_agreement((object) $actualSAData);
                    
                    $actualNDISTemp = [];
                    if (isset($getActualSA['status']) && isset($getActualSA['data']) && !empty($getActualSA['data'])) {
                        $act_temp_data = $getActualSA['data'];
                        $actualNDISTemp['actual_sa_id'] = $act_temp_data['service_agreement_id'];
                        $actualNDISTemp['actual_docusign_id'] = $act_temp_data['docusign_id'];
                        $act_sb_status = 0;
                        # Service booking is available or not
                        if ($act_temp_data['service_booking_needed'] === true) {
                            $act_sb_status = 1;
                        }
                        # Service booking is needed but not exist - 2 || Service booking is needed but not signed - 3
                        if ($act_temp_data['rule'] == 2 || $act_temp_data['rule'] == 3) {
                            $act_sb_status = $act_temp_data['rule'];
                        }

                        $actualNDISTemp['actual_sb_status'] = $act_sb_status;
                    }

                    # Set Update values - actual
                    if (($actualNDIS['actual_sa_id'] == '' || $actualNDIS['actual_sa_id'] == null) && (!empty($actualNDISTemp['actual_sa_id']))) {
                        $shiftUpData['actual_sa_id'] = $actualNDISTemp['actual_sa_id'];
                    }

                    if (($actualNDIS['actual_docusign_id'] == '' || $actualNDIS['actual_docusign_id'] == null) && (!empty($actualNDISTemp['actual_docusign_id'])) && $SADUpdate === true) {
                        $shiftUpData['actual_docusign_id'] = $actualNDISTemp['actual_docusign_id'];
                    }

                    if (!empty($actualNDISTemp['actual_sb_status']) && $actualNDIS['actual_sb_status'] != $actualNDISTemp['actual_sb_status'] && $SBUpdate === true) {
                        $shiftUpData['actual_sb_status'] = $actualNDISTemp['actual_sb_status'];
                    }
                }
                
                if (!empty($shiftUpData)) {
                    $this->update_records('shift', $shiftUpData, array('id' => $shift_id));
                }
            }

            # check and update the invoice flag
            $this->update_invoice_flag($data);

            if($SAUpdateLi) {
                $this->update_ndis_line_item_missing($data);
            }
        }
    }

    /**
     * Update NDIS missing line item flag
     * - While adding/updating service agreement line item
     * - Async API call 
     * @param {array} $data - service agreement id
     */
    public function update_ndis_line_item_missing($data) {
        $this->load->model('schedule/Schedule_model');

        $participant_id = '';
        $service_agreement_id = $data['service_agreement_id'] ?? '';
        
        # Service agreement id should not null
        if (!empty($service_agreement_id)) {
            # get service agreement 
            $this->db->select(['sa.participant_id', 'sa.id']);
            $this->db->from('tbl_service_agreement as sa');
            $this->db->where('sa.archive', 0);
            $this->db->where('sa.status', 5); # status - 5 = Active
            $this->db->where('sa.id', $service_agreement_id);
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $saDetail = $query->row_array();
            # Get service agreement participant id
            if (isset($saDetail) && !empty($saDetail['participant_id'])) {
                $participant_id = $saDetail['participant_id'];
            }
        }

        # Get NDIS type id using keyname is equals to ndis
        $serviceType = $this->basic_model->get_row('member_role', ['id'], ['name' => 'ndis']);
        $service_type_id = $serviceType->id ?? '';
        
        # Participant id and service type id should not null
        if (!empty($participant_id) && !empty($service_type_id)) {
            $this->db->select(['s.id', 's.scheduled_sa_id', 's.actual_sa_id']);
            $this->db->from('tbl_shift as s');
            $this->db->where('s.not_be_invoiced', 1);
            $this->db->where('s.archive', 0);
            $this->db->where('s.account_type', 1); # type = 1 participant
            $this->db->where('s.account_id', $participant_id);
            $this->db->where('s.role_id', $service_type_id);
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $ndisShift = $query->result_array();
            $updatedShift = [];
            foreach ($ndisShift as $key => $shiftItem) {
                $shiftItem = (object) $shiftItem;
                $shiftId = $shiftItem->id;                
                $ndisSchSAId = $shiftItem->scheduled_sa_id;
                $ndisActSAId = $shiftItem->actual_sa_id;
                $SAId = '';
                $updatedShift[$key]['shiftId'] = $shiftId;
                $updatedShift[$key]['update'] = false;

                # Get ndis line item
                $this->db->select(['snli.line_item_id', 'snli.id', 'snli.category', 'fli.category_ref']);
                $this->db->from('tbl_shift_ndis_line_item as snli');
                $this->db->join("tbl_finance_line_item as fli", "fli.id = snli.line_item_id", "inner");
                $this->db->where('snli.archive', 0);
                $this->db->where('snli.auto_insert_flag', 1);
                $this->db->where('snli.shift_id', $shiftId); 
                $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
                $ndisShiftLineItem = $query->result_array();
                
                foreach ($ndisShiftLineItem as $li_key => $item) {
                    $item = (object) $item;
                    $ndisCategory = $item->category;
                    $ndis_id = $item->id;
                    $line_item_id = $item->line_item_id;
                    $line_item_category_ref = $item->category_ref;
                    # If category equal 1 - scheduled
                    if ($ndisCategory == 1 && !empty($ndisSchSAId)) {
                        $SAId = $ndisSchSAId;
                    }

                    # If category equal 2 - actual
                    if ($ndisCategory == 2 && !empty($ndisActSAId)) {
                        $SAId = $ndisActSAId;
                    }

                    if (!empty($SAId)) {
                        $this->db->select(['sai.id']);
                        $this->db->from('tbl_service_agreement as sa');
                        $this->db->join('tbl_service_agreement_items as sai',' sai.service_agreement_id = sa.id AND sai.archive = 0', 'INNER');
                        $this->db->join("tbl_finance_line_item as fli", "fli.id = sai.line_item_id", "inner");
                        $this->db->where('sa.archive', 0);
                        $this->db->where('sa.id', $SAId);
                        $this->db->where("( sai.line_item_id = $line_item_id OR fli.line_item_number = '$line_item_category_ref' )");
                        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
                        $ndisSALineItem = $query->row_array();

                        # update auto insert flag id if line item or parent item is in service agreement
                        if (!empty($ndisSALineItem)) {
                            $updatedShift[$key]['update'] = true;
                            $updatedShift[$key]['update'] = $ndisCategory;
                            $updatedShift[$key]['SAId'] = $SAId;

                            $this->update_records('shift_ndis_line_item', array('auto_insert_flag' => '0'), array('id' => $ndis_id));
                        }
                    }
                }
                
            }

            # check and update the invoice flag
            $this->update_invoice_flag($data);
        }
    }

    /**
     * Update NDIS not be invoice flag
     * - While adding/updating service agreement line item / docusign
     * - Async API call 
     * @param {array} $data - service agreement id
     */
    public function update_invoice_flag($data) {
        $this->load->model('schedule/Schedule_model');

        $participant_id = '';
        $service_agreement_id = $data['service_agreement_id'] ?? '';
        
        # Service agreement id should not null
        if (!empty($service_agreement_id)) {
            # get service agreement 
            $this->db->select(['sa.participant_id']);
            $this->db->from('tbl_service_agreement as sa');
            $this->db->where('sa.archive', 0);
            $this->db->where('sa.status', 5); # status - 5 = Active
            $this->db->where('sa.id', $service_agreement_id);
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $saDetail = $query->row_array();
            # Get service agreement participant id
            if (isset($saDetail) && !empty($saDetail['participant_id'])) {
                $participant_id = $saDetail['participant_id'];
            }
        }

        # Get NDIS type id using keyname is equals to ndis
        $serviceType = $this->basic_model->get_row('member_role', ['id'], ['name' => 'ndis']);
        $service_type_id = $serviceType->id ?? '';
        
        # Participant id and service type id should not null
        if (!empty($participant_id) && !empty($service_type_id)) {
            $this->db->select(['s.id', 's.actual_start_datetime', 's.actual_end_datetime', 's.actual_docusign_id', 's.actual_sb_status', 's.not_be_invoiced']);
            $this->db->from('tbl_shift as s');
            $this->db->where('s.not_be_invoiced', 1);
            $this->db->where('s.archive', 0);
            $this->db->where('s.account_type', 1); # type = 1 participant
            $this->db->where('s.account_id', $participant_id);
            $this->db->where('s.role_id', $service_type_id);
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $ndisShift = $query->result_array();
            $updateShift = [];
            foreach ($ndisShift as $key => $shift) {
                $shift_id = $shift['id'];

                if (!empty($shift['actual_start_datetime']) && !empty($shift['actual_end_datetime']) && $shift['actual_start_datetime'] != '0000-00-00 00:00:00' && $shift['actual_end_datetime'] != '0000-00-00 00:00:00') {
                    # IF NDIS Shift doesn't have any line items then its marked as a invoice eligible as false
                    # IF NDIS Shift doesn't have signed service booking then mark it as not to be invoiced
                    $not_be_invoiced = 0;
                    if (empty($shift['actual_docusign_id']) || $shift['actual_docusign_id'] == '' || $shift['actual_sb_status'] == 2 || $shift['actual_sb_status'] == 3) {
                        $not_be_invoiced = 1;
                        continue;
                    }

                    # Get actual line items
                    $act_sa_line_item = $this->Schedule_model->get_service_agreement_line_item_by_shift_id($shift_id, 2);
                    foreach($act_sa_line_item as $act_key => $li_item) {
                        if ($li_item->auto_insert_flag == 1 || $li_item->line_item_price_id == '') {
                            $not_be_invoiced = 1;
                            break;
                        }
                    }

                    # update shift invoice flag
                    if ($not_be_invoiced == 0) {
                        $updateShift[$key]['shift_id'] = $shift_id;
                        $this->update_records('shift', array('not_be_invoiced'=> $not_be_invoiced), array('id' => $shift_id));
                    }
                }
            }
        }
    }
}