<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Process_management_model extends Basic_model {

    public function __construct() { 
        //load Dates library
        $this->load->library('Dates'); 
        $this->load->library('form_validation');      
        $this->table_name = 'admin_process_event';
        $this->load->file(APPPATH.'Classes/user/event.php');
        $this->model = new ProcessBuilder\Event();
        $this->operators = [
            'equals' => function($v1, $v2){return $this->equals($v1, $v2);},
            'notequalto' => function($v1, $v2){return $v1 != $v2;},
            'lessthan' => function($v1, $v2){return $v1 < $v2;},
            'greaterthan' => function($v1, $v2){return $v1 > $v2;},
            'lessorequal' => function($v1, $v2){return $v1 <= $v2;},
            'greaterorequal' => function($v1, $v2){return $v1 >= $v2;},
            'contains' => function($v1, $v2){return strpos($v1, $v2) !== false;},
            'doesnotcontains' => function($v1, $v2){return strpos($v1, $v2) === false;},
            'startswith' => function($v1, $v2){return strpos($v1, $v2) === 0;},
            'changesto' => function($v1, $v2, $previousValue){
                return ($v1 != $previousValue && $v1 == $v2  && $previousValue != null);
            },
        ];
        $this->form_validation->CI = & $this;
        parent::__construct();
    }

    public function equals($v1, $v2) {
        if ($this->dates->isDate($v1)) {
            $value = strtotime($v1);
            if (is_array($v2)) {                
                list($value1, $value2) = array_map('strtotime', $v2);
                return $value1 <= $value && $value <= $value2;
            } else {
                $v1 = $value;
                $v2 = strtotime($v2);
            }
        }        
        return $v1 == $v2;
    }

    public function get_process_list($reqData, $adminId) {
        $page = $reqData->page?? 0;
        $limit = $reqData->pageSize?? 99999;
        $start = $page * $limit;
        $sorted = $reqData->sorted;
        $search = $reqData->search?? '';
        $orderBy = '';
        $direction = '';
        $available_column = ['id', 'name', 'description', "created_by", 'status', 'created_at'];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id == 'id' ? 'ape.id' : 'ape.'.$sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'ape.created_at';
            $direction = 'DESC';
        }
        $this->db->select(['ape.id', 'ape.name', 'ape.description', "CONCAT_WS(' ', m.firstname, m.lastname) AS created_by", 'ape.status', 'ape.created_at']);
        $this->db->from(TBL_PREFIX . $this->table_name . " AS ape");
        $this->db->join(TBL_PREFIX . 'member m', 'ape.created_by = m.uuid', 'LEFT');
        if (!empty($search)) {
            $this->db->like('ape.name', $search);
            $this->db->or_like('ape.description', $search);
        }
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, $start);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        return $query->result();
    }

    public function create_update_event($data, $adminId) {
        require_once APPPATH . 'Classes/user/event.php';
        $errors = $this->validateEventData($data);
        if ($errors !== true) {
            $return = new stdClass();
            $return->status = false;
            $return->error = implode(". ", $errors);
            return $return;
        }
        if ($data->criteria == 'no_criteria' || (property_exists($data, 'expression') && property_exists($data->expression, 'triggerType') && $data->expression->triggerType == 'always')) {
            $data->expression = '';
            $data->expression_inputs = '';
            $data->condition_logic = '1';
            $data->conditions = '';
        }
        if (!empty($data->expression) && property_exists($data->expression, 'conditions') && !empty($data->expression->conditions)) {
            foreach($data->expression->conditions as $i => $condition) {
                if (is_object($data->conditions->$i)) {
                    $condition->field = $data->conditions->$i->field;
                }
                if (is_array($data->conditions->$i) && !empty($data->expression->conditions[$i]->conditions)) {
                    foreach($data->expression->conditions[$i]->conditions as $j => $sub_condition) {
                        $sub_condition->field = $data->conditions->$i[$j]->field;
                    }                
                }
            }
            $conditions = $data->expression->conditions;
            $op = $data->expression->triggerType == 'any'? 'OR' : 'AND';
            $logic = '';
            $j = 0;
            $all_conditions = [];
            if ($data->expression->triggerType != 'custom') {
                foreach($conditions as $i => $condition) {
                    $j++;
                    if (empty($condition->isGroup)) {
                        $logic .= $j;
                        if ($i != count($conditions) - 1) {
                            $logic .= ' ' . $op . ' ';
                        }
                        $all_conditions[$j] = $condition;
                    } else {
                        $op2 = $condition->triggerType == 'any'? 'OR' : 'AND';
                        $keys = array_keys($condition->conditions);
                        $con_indexes = [];
                        foreach($keys as $key) {
                            $all_conditions[$j] = $condition->conditions[$key];
                            $con_indexes[$j] = $j;
                            $j++;
                        }
                        $logic .= ' (' . implode(" $op2 ", $con_indexes) . ') ';
                    }
                }
            } else {
                foreach($conditions as $condition) {
                    $j++;
                    $all_conditions[$j] = $condition;
                }
            }
            $data->conditions = $all_conditions;
            if (empty($data->expression->customLogic)) {
                $data->condition_logic = $logic;
            } else {                
                $data->condition_logic = $data->expression->customLogic;
            }
            $data->condition_logic = strtolower($data->condition_logic);
            $is_valid = true;
            if ($is_valid && !empty($data->condition_logic)) {
                $cons = (array) $data->conditions;
                preg_match_all('!\d+!', $data->condition_logic, $matches);
                if (count($matches[0]) != count($cons)) {
                    $is_valid = false;
                }
                //count the number of and/or
                $and_count = substr_count($data->condition_logic, 'and');
                $or_count = substr_count($data->condition_logic, 'or');
                $condition_count = count($matches[0]);
                if ( $condition_count - 1 != $and_count + $or_count ) {
                    $is_valid = false;
                }
            } else {
                $is_valid = false;
            }
            if (!$is_valid) {
                $return = new stdClass();
                $return->status = false;
                $return->error = empty($data->condition_logic) ? "Custom Logic can not be empty" : "Invalid Custom Logic";
                return $return;
            }
        }
        if (!empty($data->event_action) && $data->event_action == 'send_sms') {
            $data->email_template = null;
        } else {
            $data->sms_template = null;
        }
        $event = new ProcessBuilder\Event($data);        
        return $this->save($event, $adminId);
    }

    public function executeEvent($eventObject, $triggers, $id, $data, $previousValues) {

        $this->db->select(['ape.*']);
        $this->db->from(TBL_PREFIX . $this->table_name . " AS ape");
        $this->db->where(['object_name' => $eventObject, 'status' => 1]);
        $this->db->where_in('event_trigger', $triggers);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $q = $this->db->last_query();
        $events = $query->result();
        if (!empty($events)) {
            $valid_events = $this->validateEvents($events, $id, $data, $previousValues);
            foreach($valid_events as $valid_event) {
                if ($valid_event->event_action == 'send_email') {
                    $this->sendEmails($valid_event, $id, $data);
                }
                if ($valid_event->event_action == 'send_sms') {
                    $this->sendSms($valid_event, $id, $data);
                }
            }
        }
    }

    public function evaluateCondition($fields, $operator, $value, $id, $data, $previousValues) {
        $result = false;
        $orm = $this->getOrm();
        $field_arr = explode('.', $fields);
        $parent_mod_name = $field_arr[0];
        $parent_mod_info = $orm[$parent_mod_name];
        $parent_class = $parent_mod_info['class_name'];
        $parent_model_path = $parent_mod_info['module'] . '/' . $parent_class;
        $this->load->model($parent_model_path);
        $fields = $this->$parent_class->getObjectfields();
        $last_index = count($field_arr)-1;
        $field = $field_arr[$last_index];
        unset($field_arr[$last_index]);
        $field_value = '';
        $model_class = '';
        foreach($field_arr as $i => $model) {
            $model_info = $orm[$model];
            $model_class = $model_info['class_name'];
            $model_path = $model_info['module'] . '/' . $model_class;
            $this->load->model($model_path);
            if (!empty($object_data) && !empty($object_fields)) {
                //get current model object's id
                $field_info = $object_fields[$model];
                $field_name = $field_info['field'];
                if (!empty($field_info['foreign_key'])) {
                    $foreign_key = $field_info['foreign_key'];
                    $this->$model_class->pk = $foreign_key;
                }
                $id = $object_data->$field_name;
            }
            $object_fields = $this->$model_class->getObjectfields();
            $object_data = $this->$model_class->loadObject($id);          
        }
        if (!empty($object_data)) {
            if (is_callable($object_fields[$field])) {
                $field_value = $object_fields[$field]($id);
            } else {
                $field_value = $object_data->$field;
            }
            $field_value = strtolower(trim($field_value));
            //convert this week to date parameters
            $text = strtolower(trim($value));
            $value = $this->getTextFieldValue($text, $field_value);
            if ($field == 'status' && !empty($model_class) && method_exists($this->$model_class, 'getStatusOptions')) {
                $status_options = array_flip(array_map('strtolower', $this->$model_class->getStatusOptions()));
                if (!empty($status_options) && is_array($status_options)) {
                    $text = str_replace(' ', '', $text);
                    $value = !empty($status_options[$text])? $status_options[$text] : '';
                }
            }

            # get previous value 
            $previousValue = null;
            if (isset($previousValues) && property_exists($previousValues, $field) && !empty($previousValues->$field)) {
                $previousValue = $previousValues->$field;
            }

            $result = $this->operators[$operator]($field_value, $value, $previousValue);
        }
        $bit = empty($result)? 0 : 1;
        return $bit;
    }

    public function getTextFieldValue($text, &$field_value) {
        //handle thismonth, this week etc
        if (property_exists($this->dates, $text)) {
            return $this->dates->get($text);
        }
        //custom date like 31/12/2020
        $comps = explode(' ', $text);    
        $text = $comps[0];    
        if (strpos($text, '/') !== false) {
            $parts = explode('/', $text);
            if (is_array($parts) && count($parts) == 3) {
                $formatted_date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                if ($this->dates->isDate($formatted_date)) {
                    $field_value = $this->dates->toDate(strtotime($field_value), 'Y-m-d');
                    $text = $formatted_date;
                }
            }
        }
        return $text;
    }

    public function applyConditions($conditions, $id, $data, $previousValues) {
        $evals = [];
        if (!empty($conditions)) {
            $conditions = json_decode($conditions, true);
            foreach($conditions as $condition_row) {
                $evals[] = $this->evaluateCondition($condition_row['field'], $condition_row['operator'], $condition_row['value'], $id, $data, $previousValues);
            }
        }
        return $evals;
    }

    public function validateEvents($events, $id, $data, $previousValues) {
        $valid_events = [];
        foreach($events as $event) {
            if ($event->criteria == 'no_criteria') {
                $valid_events[$event->id] = $event;
            } else {
                $conditions = $this->applyConditions($event->conditions, $id, $data, $previousValues);
                if (!empty($conditions)) {
                    $condition_logic = $event->condition_logic;
                    $elogic = str_replace([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 'AND', 'OR', 'and', 'or'], [@$conditions[0], @$conditions[1], @$conditions[2], @$conditions[3], @$conditions[4], @$conditions[5], @$conditions[6], @$conditions[7], @$conditions[8], @$conditions[9], '&&', '||', '&&', '||'], $condition_logic);
                    $output = 0;
                    eval("\$output = $elogic;");
                    if ($output) {
                        $valid_events[$event->id] = $event;
                    }
                }
            }
        }
        return $valid_events;
    }

    public function sendEmails($eventData, $id, $reqData, $current_admin = 0) {
        require_once APPPATH . 'Classes/Automatic_email.php';
        $emails = $this->getRecipientEmails($eventData->object_name, $id, $eventData->recipient);
        if (!empty($emails)) {
            //refine emails if there is any data assigned to email key
            foreach($emails as $k => $v) {
                if (is_array($v) && is_array($reqData) && !empty($reqData)) {
                    foreach($v as $em => $d) {
                        $emails[] = $em;
                        $t = (array) array_values($reqData)[0];
                        //unset userId for other recipients
                        $t['userId'] = 0;
                        $t['firstname'] = $d->firstname;
                        $t['lastname'] = $d->lastname;
                        $reqData[$em] = (object) $t;
                        unset($emails[$k]);
                    }
                }
            }
            foreach($emails as $email) {
                if (!empty($email) && is_string($email)) {
                    $obj = new Automatic_email();
                    $obj->setTemplateId($eventData->email_template);
                    $obj->setEmail($email);
                    $emdata = (array) $reqData;
					// if data is for multiple recipients then fetch data by email 
                    if (is_array($reqData) && array_key_exists($email, $reqData)) {
                        $emdata = (array) $reqData[$email];
                    }
                    $obj->setDynamic_data($emdata);
                    $userId = $emdata['userId'];
                    $obj->setUserId($userId);
                    $obj->setUser_type(1);        
                    $obj->automatic_email_send_to_user_by_template_id();
                }
            }
        }        
    }

    public function get_process_event($id) {
        $joins = [
            'left' => [
                [
                    'table' => 'email_templates as et',
                    'on' => 'ape.email_template = et.id'
                ],
                [
                    'table' => 'sms_template as st',
                    'on' => 'ape.sms_template = st.id'
                ]
            ]
        ];
        $select_array = [
            'ape.*',
            '(CASE 
                WHEN event_action = "send_sms" THEN st.name
                ELSE et.name
                END) as template_label',
            '(CASE 
                WHEN event_action = "send_sms" THEN ape.sms_template
                ELSE ape.email_template
                END) as template'
        ];
        return $this->get_row($this->table_name.' as ape', $select_array, ['ape.id' => $id], $joins);
    }

    public function update_process_event($id, $fields = [], $adminId) {
        if (!empty($fields )) {
            $fields['updated_by'] = $adminId;
            $fields['updated_at'] = date('Y-m-d H:i:s');
            return $this->update_records($this->table_name, $fields, ['id' => $id]);
        }
        return false;
    }

    public function clone_process_event($id, $adminId) {
        if (!empty($id) && !empty($adminId)) {
            return $this->clone($this->table_name, 'id', $id, $adminId, 'name');
        }
        return false;
    }

    private function validateEventData($reqData = []) {
        $val_rules = array(
            array('field' => 'name', 'label' => 'name', 'rules' => 'required')
        );
        
        if (!empty($reqData->event_action) && $reqData->event_action == 'send_sms') {
            $val_rules[] = array('field' => 'sms_template', 'label' => 'SMS Template', 'rules' => 'required');
        } else {
            $val_rules[] = array('field' => 'email_template', 'label' => 'Email Tempalte', 'rules' => 'required');
        }

        $this->form_validation->set_data((array) $reqData);
        $this->form_validation->set_rules($val_rules);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return $errors;
        }
        return true;
    }

    public function getRecipientEmails($object, $objectId, $recipient = "") {
        $emails = [];
        if (!empty($recipient)) {
            $model = $this->loadObjectModel($object);
            if (!empty($model)) {
                $object_data = $this->$model->loadObject($objectId);
                $field_data = new stdClass();
                //check if recipient is object fields
                $recipient_arr = json_decode($recipient, true);
                $recipients = [];
                foreach($recipient_arr as $obj) {
                    $recipients[] = $obj["value"];
                }
                foreach($recipients as $field) {                
                    //if field is a table field
                    if (array_key_exists($field, $this->$model->object_fields)) {
                        $field_info = $this->$model->object_fields[$field];
                        $field_name = $field_info['field'];
                        if (array_key_exists('field', $field_info)) {                        
                            if (empty((array) $field_data)) {
                                $field_data = $object_data;
                            } else {
                                $fid = $object_data->$field_name;                            
                                $field_model = $this->loadObjectModel($field);
                                $foreign_key = '';
                                if (!empty($field_info['foreign_key'])) {
                                    $foreign_key = $field_info['foreign_key'];
                                    $this->$field_model->pk = $foreign_key;
                                }
                                $field_data = $this->$field_model->loadObject($fid);
                                $field_name = 'id';
                                if (!empty($foreign_key)) {
                                    $field_name = $foreign_key;
                                }
                            }
                            $email_field = $field_info['object_fields']['email'];
                            if (!empty($field_data) && is_object($field_data) && property_exists($field_data, $field_name) && !empty($field_data->$field_name)) {
                                $field_id = $field_data->$field_name;
                                if (is_callable($email_field)) {
                                    $t_emails = $email_field($field_id);                                
                                    if (is_array($t_emails)) {
                                        $emails = array_merge($emails, $t_emails);
                                    } else {
                                        $emails[] = $t_emails;
                                    }
                                } else {
                                    $field_data = $this->$field_model->loadObject($field_id);
                                    if (property_exists($field_data, 'email')) {
                                        $emails[] = $field_data->email;
                                    }
                                }
                            }
                        }
                    }  else {
                        //load members data
                        $result = $this->get_row('member AS m', ['m.firstname, m.lastname'], ['m.username' => $field, 'm.archive' => 0]);
                        $emails[] = [$field => $result];
                    }              
                }
            }
        }
        return $emails;
    }

    /**
     * Send Sms
     * @param {obj} $eventData
     * @param {int} $id
     * @param {obj} $reqData
     * @param {int} $current_admin
     */
    public function sendSms($eventData, $id, $reqData, $current_admin = 0) {
        require_once APPPATH . 'Classes/Automatic_sms.php';
        # get recipient phone no
        $memPhone = $this->getRecipientPhone($eventData->object_name, $id, $eventData->recipient);
        $model = $this->loadObjectModel($eventData->object_name);
        $objectData = $this->$model->loadObject($id);
        
        $adminId = '';       
        if (!empty($reqData['created_by'])) {
            $adminId = $reqData['created_by'];
        }  else if (!empty($reqData['updated_by'])) {
            $adminId = $reqData['updated_by'];
        }
        
        if (!empty($memPhone)) {
            foreach($memPhone as $row) {
                if (!empty($row) && is_object($row) && property_exists($row, 'phone') && !empty($row->phone)) {
                    # set sms data values
                    $obj = new Automatic_sms();
                    $obj->setTemplateId($eventData->sms_template);
                    $obj->setSendTo($row->phone);
                    $response = $obj->automatic_sms_send_to_user($objectData, $eventData->object_name);

                    # save log
                    $log_array = [];
                    $log_array['source_id'] = $id;
                    $log_array['sender_id'] = $adminId;
                    $log_array['recipient_id'] = $row->id ?? '';
                    $log_array['response'] = $response ?? '';
                    $log_array['content'] = $obj->getTemplateContent();
                    $log_array['created_by'] = $adminId;
                    
                    $obj->setSMSLogData($log_array);
                    $obj->createSMSLog($eventData->object_name);

                    # custom log
                    $obj->SMSCustomeLog($response, $eventData->object_name, $id);
                }
            }
        }
    }

    /**
     * Get Recipient phone number
     * @param {obj} $object
     * @param {int} $objectId
     * @param {str} $recipient
     */
    public function getRecipientPhone($object, $objectId, $recipient = "") {
        $phone = [];
        if (!empty($recipient)) {
            $model = $this->loadObjectModel($object);
            $object_data = $this->$model->loadObject($objectId);
            $field_data = new stdClass();
            //check if recipient is object fields
            $recipient_arr = json_decode($recipient, true);
            $recipients = [];
            foreach($recipient_arr as $obj) {
                $recipients[] = $obj["value"];
            }

            foreach($recipients as $field) {               
                //if field is a table field
                if (array_key_exists($field, $this->$model->object_fields)) {
                    $field_info = $this->$model->object_fields[$field];
                    $field_name = $field_info['field'];
                    if (array_key_exists('field', $field_info)) {                        
                        if (empty((array) $field_data)) {
                            $field_data = $object_data;
                        } else {
                            $fid = $object_data->$field_name;                            
                            $field_model = $this->loadObjectModel($field);
                            $foreign_key = '';
                            if (!empty($field_info['foreign_key'])) {
                                $foreign_key = $field_info['foreign_key'];
                                $this->$field_model->pk = $foreign_key;
                            }
                            $field_data = $this->$field_model->loadObject($fid);
                            $field_name = 'id';
                            if (!empty($foreign_key)) {
                                $field_name = $foreign_key;
                            }
                        }
                        $phone_field = $field_info['object_fields']['phone'];
                        if (!empty($field_data) && is_object($field_data) && property_exists($field_data, $field_name) && !empty($field_data->$field_name)) {
                            $field_id = $field_data->$field_name;
                            if (is_callable($phone_field)) {
                                $t_phone = $phone_field($field_id);                                
                                if (is_array($t_phone)) {
                                    $phone = array_merge($phone, $t_phone);
                                } else {
                                    $phone[] = $t_phone;
                                }
                            } else {
                                $field_data = $this->$field_model->loadObject($field_id);
                                if (property_exists($field_data, 'phone')) {
                                    $phone[] = $field_data->phone;
                                }
                            }
                        }
                    }
                }  else {
                    //load members data
                    $joins = [
                        ['left' => ['table' => 'member AS m', 'on' => 'sm.member_id = m.id']],
                        ['left' => ['table' => 'person AS p', 'on' => 'm.person_id = p.id']],
                        ['left' => ['table' => 'person_phone AS pp', 'on' => 'm.person_id = pp.person_id AND pp.archive = 0 AND pp.primary_phone = 1']]
                    ];
                    $result = $this->get_rows('shift_member AS sm', ['pp.phone'], ['sm.shift_id' => $objectId], $joins );

                    $phone[] = [$field => $result];
                }              
            }
        }
        return $phone;
    }
    
}
