<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 *
 * its use for handle request of Opportunity
 * /*
     * Get all active line items
     */
    function get_line_items_additional_funding_detail() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData)) {
            $result = $this->ServiceAgreement_model->get_line_items_additional_funding_detail($reqData->data);
            
            echo json_encode($result);
        }
    }
/** @property-read \Opportunity_model $Opportunity_model
 */
class Opportunity extends MX_Controller {

    use formCustomValidation;

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        $this->loges->setLogType('crm');

        $this->load->database();    // Load database
        // load model @Opportunity_model
        $this->load->model('Opportunity_model');
        $this->load->model('ServiceAgreement_model');
        $this->load->helper('message');
        $this->load->model('common/List_view_controls_model');
        $this->load->model('item/Participant_model');
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # HCM- 3485, adding all requests to backend in a log file
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }

    function get_field_history() {
        $reqData = request_handler('access_crm');
        if (empty($reqData)) return;

        return $this->Opportunity_model->get_field_history($reqData->data);
    }

    /*
    * get all active line items
    */
    function get_finance_active_line_item_listing() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData)) {
            $result = $this->Opportunity_model->get_finance_active_line_item_listing($reqData);
            echo json_encode($result);
        }
    }

    /*
    *Save opportunity items
    *with opportunity id
    */
     function save_opportunity_item() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data->rows)) {
            $data = obj_to_arr($reqData->data->rows);            
            $data['opportunity_id'] = $reqData->data->opp_id;
            $data['additional_rows'] = obj_to_arr($reqData->data->additional_rows);
            $opportunityItemId = $this->Opportunity_model->save_opportunity_item($data, $adminId, $reqData);

            if ($opportunityItemId) {
                $this->load->library('UserName');
                $adminName = $this->username->getName('admin', $adminId);
                $succes_msg = 'Opportunity item saved successfully.';
                $this->loges->setTitle("Opportunity item saved " . " by " . $adminName);
                $this->loges->setDescription(json_encode($data));
                $this->loges->setUserId($adminId);
                $this->loges->createLog(); // create log

                $response = ['status' => true, 'msg' => $succes_msg];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }
        echo json_encode($response);
        exit();
    }

    /*
     * its use for search ownder staff (admin) in create opportunity
     * return type json
     */

    function get_owner_staff_search() {
        $reqData = request_handler();
        $ownerName = $reqData->data->query ?? '';
        $rows = $this->Opportunity_model->get_owner_staff_by_name($ownerName);
        echo json_encode($rows);
    }

    /*
     * its use for get opportunity status option used in listing opportunity filter
     * return type josn
     */

    function get_opportunity_status_option() {
        request_handler();

        $rows = $this->Opportunity_model->get_opportunity_status_option();
        echo json_encode(["status" => true, "data" => $rows]);
    }

    /*
     * its use for search account person (tbl_person) in create opportunity
     * return type json
     */

    function get_account_person_name_search() {
        $reqData = request_handler();
        $name = $reqData->data->query ?? '';
        $rows = $this->Opportunity_model->get_account_person_name_search($name);
        echo json_encode($rows);
    }

    /*
     * its use for search lead number use in create opportunity
     * return type json
     * in form of option [{label : 'LD0001': value: 1}]
     */

    function get_lead_number_search() {
        $reqData = request_handler();
        $name = $reqData->data->query ?? '';
        $rows = $this->Opportunity_model->get_lead_number_search($name);
        echo json_encode($rows);
    }

    /*
     * its use for get option of create opportunity
     * return type json
     */

    function get_create_opportunity_option() {
        request_handler();

        // get source option
        $this->load->model("Lead_model");
        $rows["lead_source_code_option"] = $this->Lead_model->get_lead_source();


        // get opportunity type option
        $rows["opportunity_type_options"] = $this->Opportunity_model->get_opportunity_type_option();

        echo json_encode(["status" => true, "data" => $rows]);
    }

    /*
     * its use for get opportunity option
     * return opportunity list
     *
     *
     * opration in listing
     * searching, filter and sorting
     *
     * return type json
     */

    function get_opportunity_list() {
        $reqData = request_handler('access_crm');
        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            if (!empty($filter_condition)) {
                $filter_condition = str_replace(['status'],
                ['op_status'], $filter_condition);
            }
        }
        if (!empty($reqData->data)) {

            // call query model of opportunity
            $result = $this->Opportunity_model->get_opportunity_list($reqData->data, $reqData->adminId, $filter_condition, $reqData->uuid_user_type);
            echo json_encode($result);
            exit();
        }
    }

    /*
     * its use for create opportunity
     * handle request of create opportunity
     *
     * @todo: MISLEADING CONTROLLER ACTION NAME!
     * this is not  create' opportunity anymore, it is more like 'save' opportunity
     *
     * return type json
     * ['status' => true, 'msg' => 'Opportunity has been created successfully.']
     */

    function create_opportunity() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        #pr($reqData->data);
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = 0;
            $validation_rules = [
                array('field' => 'topic', 'label' => 'topic', 'rules' => "required"),
                array('field' => 'opportunity_type', 'label' => 'opportunity type', 'rules' => 'required')
            ];

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            $this->load->model("Lead_model");
            $check_service_type = $this->Lead_model->check_service_type_exist_ref('lead_service_type',$data['topic']);
            if (!$check_service_type) {
                return $this->output->set_output(json_encode([
                            'status' => false,
                            'error' => 'Please select a service type',
                ]));
            }

            if ($this->form_validation->run()) {

                $opportunityId = $this->Opportunity_model->create_opportunity($data, $adminId);

                if ($opportunityId) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    if (isset($data['opportunity_id'])) {
                        $this->loges->setTitle("Opportunity updated " . $data['topic'] . " by " . $adminName);  //
                        $succes_msg = 'Opportunity has been updated successfully.';
                    } else {
                        $succes_msg = 'Opportunity has been created successfully.';
                        $this->loges->setTitle("New opportunity created " . $data['topic'] . " by " . $adminName);  //
                    }

                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($opportunityId);
                    $this->loges->createLog(); // create log

                    $response = ['status' => true, 'msg' => $succes_msg];
                } else {
                    $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
        exit();
    }

    /*
     * get all detail of opportunity
     * by passing id
     */

    public function get_opportunity_detail() {
        $reqData = $reqData1 = request_handler('access_crm');
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $result = $this->Opportunity_model->get_opportunity_detail($reqData);
            echo json_encode($result);
        }
    }

     /*
     * Get all active line items
     */
    public function get_line_items_additional_funding_detail() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData)) {
            $result = $this->Opportunity_model->get_line_items_additional_funding_detail($reqData->data);
            
            echo json_encode($result);
        }
    }

    /**
     *
     * Archived opportunity will be excluded in the list of opportunity.
     */
    public function archive_opportunity() {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;

        $this->output->set_content_type('json');

        $result = $this->Opportunity_model->get_opportunity_detail((object) $data);
        if (empty($result)) {
            return $this->output->set_output(json_encode([
                        'status' => false,
                        'error' => 'Opportunity does not exist anymore. Please refresh your page',
            ]));
        }

        $result = $this->basic_model->update_records('opportunity', array('archive' => 1), array('id' => $data['opportunity_id']));
        if (!$result) {
            return $this->output->set_output(json_encode([
                        'status' => false,
                        'error' => $result['error'] ?? system_msgs('something_went_wrong'),
            ]));
        }
        // Log
        $opportunity_id = $data['opportunity_id'];

        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully archived opportunity with ID of %s by %s", $opportunity_id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully archived opportunity with ID of %s by %s", $opportunity_id, $adminName));  // set title in log
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($opportunity_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        return $this->output->set_output(json_encode([
                    'status' => true,
                    'msg' => "Opportunity successfully archived"
        ]));
    }

    /*
     * get all detail of opportunity for view
     * by passing id
     */

    public function view_opportunity() {
        $reqData = $reqData1 = request_handler('access_crm');
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $result = $this->Opportunity_model->view_opportunity($reqData);

            echo json_encode($result);
        }
    }

    /*
     * its use for search `Contact` in Manage contact Roles pages
     * return type json
     */

    function get_contact_list_for_opportunity() {
        $reqData = request_handler('access_crm');
        $name = $reqData->data->query ?? '';
        $limit = $reqData->data->limit ?? '';
        $allow_new_contact = $reqData->data->new_contact ?? true;
        $allow_new_contact = filter_var($allow_new_contact, FILTER_VALIDATE_BOOLEAN);
        $rows = $this->Opportunity_model->get_contact_list_for_opportunity($name, $limit, $allow_new_contact);
        echo json_encode($rows);
    }

    /*
     * its use for search `Roles` in Manage contact Roles pages
     * return type json
     */

    function get_roles_for_opportunity() {
        $reqData = request_handler('access_crm');
        $rows = $this->Opportunity_model->get_roles_for_opportunity();
        echo json_encode(array('status' => true, 'data' => $rows));
    }

    /*
     * Save contact detail of opportuity
     * call from `Manage contact role`
     */

    function update_opportunity_contact_role() {
        $reqData = request_handler('access_crm');
        $rows = $this->Opportunity_model->update_opportunity_contact_role($reqData);
        echo json_encode(array('status'=>$rows));
    }

    /*
     * get opportunity status list
     * for dropdown
     */

    function get_opportunity_option() {
        request_handler();
        $rows["opportunity_status"] = $this->basic_model->get_result('opportunity_status', array('archive' => 0), ["name as label", 'id']);
        echo json_encode(["status" => true, "data" => $rows]);
    }

    function get_opportunity_service_agreement_summary() {
        $reqData = request_handler('access_crm')->data;

        $result = $this->Opportunity_model->get_opportunity_service_agreements_summary($reqData->opportunity_id);

        echo json_encode(["status" => true, "data" => $result]);
    }

    public function save_service_agreement() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = 0;
            $encode_data = json_encode($data);
            $validation_rules = [
                array('field' => 'owner', 'label' => 'topic', 'rules' => "required"),
                array('field' => 'account', 'label' => 'opportunity type', 'rules' => 'required'),
                array('field' => 'contract_start_date', 'label' => 'Contract start date', 'rules' => 'required'),
                array('field' => 'contract_end_date', 'label' => 'Contract end date', 'rules' => 'required'),
                array('field' => 'plan_start_date', 'label' => 'Plan start date', 'rules' => 'required'),
                array('field' => 'plan_end_date', 'label' => 'Plan end date', 'rules' => 'required'),
                array('field' => 'opp_id', 'label' => 'NDIS number', 'rules' => 'trim|callback_check_service_agreement_contract['.$encode_data.']')
            ];

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);
            
            if ($this->form_validation->run()) {

                $service_id = $this->Opportunity_model->save_service_agreement($data, $adminId);

                if ($service_id) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    if (isset($data['service_id'])) {
                        $this->loges->setTitle("Service agreement updated " . " by " . $adminName);  //
                        $succes_msg = 'Service agreement has been updated successfully.';
                    } else {
                        $succes_msg = 'Service agreement has been created successfully.';
                        $this->loges->setTitle("New Service agreement is created " . " by " . $adminName);  //

                        // new service agreement, add line-items sourced from the opp
                        $lineItems = $this->Opportunity_model->get_opportunity_items_detail($data["opp_id"]);
                        $this->ServiceAgreement_model->save_line_items($lineItems, $service_id, $adminId);
                   
                        // new service agreement, add additional funding sourced from the opp
                        $addFund = $this->Opportunity_model->get_opportunity_additional_fund($data["opp_id"]);
                        $this->ServiceAgreement_model->save_additional_funding($addFund, $service_id, $adminId);
                    
                    }

                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->createLog(); // create log

                    $response = ['status' => true, 'msg' => $succes_msg];
                } else {
                    $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }
        echo json_encode($response);
        exit();
    }

    /**
     * Check if service agreement contract date is overlap with existing contract
     * @param array $data 
     * @return bool 
     */
    public function check_service_agreement_contract($id, $param_data)
    {
        $data = json_decode($param_data, true);
        $opp_id = (integer) $data['opp_id'] ?? '';
        $contract_start_date = $data['contract_start_date'] ?? '';
        $contract_end_date = $data['contract_end_date'] ?? '';
        $agreement_id = (integer) $data['agreement_id'] ?? '';

        $this->db->from(TBL_PREFIX . 'service_agreement as sa');
        $this->db->join(TBL_PREFIX . 'opportunity as o', 'sa.opportunity_id = o.id', 'left');
        $this->db->where("(
        STR_TO_DATE('{$contract_start_date}', '%Y-%m-%d') BETWEEN DATE_FORMAT(`sa`.`contract_start_date`, '%Y-%m-%d') AND DATE_FORMAT(`sa`.`contract_end_date`, '%Y-%m-%d') OR
        STR_TO_DATE('{$contract_end_date}', '%Y-%m-%d') BETWEEN DATE_FORMAT(`sa`.`contract_start_date`, '%Y-%m-%d') AND DATE_FORMAT(`sa`.`contract_end_date`, '%Y-%m-%d') OR
        DATE_FORMAT(`sa`.`contract_start_date`, '%Y-%m-%d') BETWEEN STR_TO_DATE('{$contract_start_date}', '%Y-%m-%d') AND STR_TO_DATE('{$contract_end_date}', '%Y-%m-%d') OR
        DATE_FORMAT(`sa`.`contract_end_date`, '%Y-%m-%d') BETWEEN STR_TO_DATE('{$contract_start_date}', '%Y-%m-%d') AND STR_TO_DATE('{$contract_end_date}', '%Y-%m-%d')
        )
        ");
        $this->db->where('sa.opportunity_id', $opp_id);
        if (!empty($agreement_id) && $agreement_id > 0) {
            $this->db->where('sa.id !=', $agreement_id);
        }
        $this->db->where('sa.archive',0);
        $this->db->select(['sa.id']);
        $query = $this->db->get();
        
        $numRows = $query->num_rows();
        if ($numRows > 0) {
            $this->form_validation->set_message(__FUNCTION__, sprintf("The contract date is overlapping with existing service agreement. Please choose a different date"));
            return false;
        }

        return true;
    }

    function update_status_opportunity() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;

            $validation_rules = [
                array('field' => 'opportunity_id', 'label' => 'opportunity id', 'rules' => "required"),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required'),
            ];

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                // get former state
                $former_value = $this->db->from(TBL_PREFIX . 'opportunity')
                ->select('opportunity_status')
                ->where(['id' => $data['opportunity_id']])
                ->limit(1)
                ->get()
                ->result();

                $res = $this->Opportunity_model->update_status_opportunity($data, $adminId);

                if ($res['status']) { // if the actual update succeeded

                    if (empty($former_value)) $former_value = '0';
                    else $former_value = $former_value[0]->opportunity_status;

                    // create an OpportunityHistory entry
                    $this->db->insert(TBL_PREFIX . 'opportunity_history', [
                                        'opportunity_id' => $data['opportunity_id'],
                                        'created_by' => $adminId,
                                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    // create an OpportunityHistoryField entry
                    $bOppHistoryItemIns = $this->db->insert(TBL_PREFIX . 'opportunity_field_history', [
                                                                    'history_id' => $this->db->insert_id(),
                                                                    'opportunity_id' => $data['opportunity_id'],
                                                                    'field' => 'status',
                                                                    'prev_val' => $former_value,
                                                                    'value' => $data['status']]);
                    if (!$bOppHistoryItemIns) {
                        $response = ['status' => false, 'error' => 'could not insert opp history item'];
                    }
                }

                $response = $res;
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }
        echo json_encode($response);
        exit();
    }

    public function validate_item_data($data, $requestData)
    {
        $lineItems = json_decode($requestData);
        if (!empty($lineItems))
        {
            foreach ($lineItems as $val) {
                if ($val->selected != 1) continue;

                if($val->qty == ''){
                    $this->form_validation->set_message('validate_item_data', 'Quantity is required for Item - ' . $val->line_item_name);
                    return false;
                }

                if($this->IsAmountRequired($val, $lineItems) && !($val->amount > 0)) {
                    $this->form_validation->set_message('validate_item_data', 'Amount is required for Item - ' . $val->line_item_name);
                    return false;
                }
          }
      }
    }

    /**
     * Determines if an Opportunity Line Item Value is required.
     *
     * If the parent Category of this Line Item is present in the list,
     * and it has a value, a value is optional;
     * else, a value is required.
     * @param array $lineItems
     * */
    public function IsAmountRequired($item, $lineItems) {
        $isCategory = empty($item->category_ref);

        if (!$isCategory) {

            foreach ($lineItems as $row) {
                if ($row->selected != 1) continue;

                if ($item->category_ref == $row->line_item_number)
                    return false;
            }

        }

        return true;
    }
    public function get_opp_contacts() {
        $reqData = $reqData1 = request_handler('access_crm');
        $getdata = json_decode($reqData->data);
        $rows = array();
        $result = $this->Opportunity_model->get_oppunty_contacts($getdata->opporunity_id);
        echo json_encode($result);
    }
    public function delete_opp_contacts() {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        // echo '<pre>';print_r($data);die;
        $result = $this->Opportunity_model->delete_oppurunity_contacts($data);
        if($result) {
            $response = [
            'status' => true,
            'msg' => "Oppurunity Contact successfully deleted"
        ];
        }else{
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        }
        echo json_encode($response);
        exit();
    }

    public function get_staff_safety_checklist() {
        $reqData = request_handler('access_crm');
        $result = $this->Opportunity_model->get_staff_safety_checklist($reqData);
        echo json_encode(['data' => $result, 'status' => true]);
    }

    public function get_staff_safety_checklist_items() {
        $reqData = request_handler('access_crm');
        $result = $this->Opportunity_model->get_staff_safety_checklist_items($reqData);
        //get opportunity details
        $opportunity = $this->Opportunity_model->view_opportunity($reqData->data);
        $participant = '';
        $participant_id = $reqData->data->participant_id?? 0;
        if ($participant_id) {
            $res = $this->Participant_model->get_participant_detail_by_id($reqData->data, 1);
            if (!empty($res) && !empty($res['data'])) {
                $participant = $res['data'];
            }
        }
        echo json_encode(['data' => $result['items'], 'opportunity' => @$opportunity['data'], 'updated_by_name' =>  @$result['updated_by_name'], 'updated_by' =>  @$result['updated_by'], 'updated_at' => @$result['updated_at'], 'participant_id' => @$result['participant_id'], 'status' => true, 'participant' => $participant]);
    }

    public function save_staff_safety_checklist_items() {
        $reqData = request_handler('access_crm');
        $result = $this->Opportunity_model->save_staff_safety_checklist_items($reqData);
        echo json_encode(['data' => $result, 'status' => true]);
    }

    public function printSafetyCheckList() {
        $html = '<html><head><style>
        @page Section1 {size:595.45pt 841.7pt; margin:1.0in 1.25in 1.0in 1.25in;mso-header-margin:.5in;mso-footer-margin:.5in;mso-paper-source:0;}
        div.Section1 {page:Section1;}
        @page Section2 {size:841.7pt 595.45pt;mso-page-orientation:landscape;margin:1.25in 1.0in 1.25in 1.0in;mso-header-margin:.5in;mso-footer-margin:.5in;mso-paper-source:0;}
        div.Section2 {page:Section2;}
        </style></head><body><div class="Section2"><h3 style="text-align:center">Need-Assessments - JessNT Test Need Assessment</h3><h4>Diagnosis</h4><style>
        table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
        }

        td, th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
        }

        tr:nth-child(even) {
        background-color: #dddddd;
        }
        </style><table>
        <tbody><tr>
        <th>Diagnosis</th>
        <th>Level Of Support</th>
        <th>Current Plan
        </th><th>Plan End Date
        </th><th>Impact On Participant</th>
       </tr><tr><td></td><td></td><td></td><td></td><td></td></tr></tbody></table></div></body></html>';
       $filename = 'Safety_Checklist.doc';
       header('Content-type: application/ms-word');
       header('Content-Disposition: attachement;filename="'.$filename.'"');
       header('Content-Transfer-Encoding: binary');
       echo $html;
       die;
    }
}
