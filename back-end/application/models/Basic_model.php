<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Basic_model extends CI_Model
{

    private $created_at = "";

    private $updated_at = "";

    private $orm = [];

    protected $object_name = ""; //each model has an object name and that object is the key of $orm array

    protected $object_fields = []; // this array conatins fields related to that object with their defnition as value

    public $pk = ''; //used in process builder to fetch object data automatically based on this primary key

    public $table_name = ''; //used in process builder to fetch object data automatically based on this table name and pk

    protected $generic_recipients = [];

    protected $object_recipients = [];

    protected $recipient_types = [];

    public function __construct()
    {
        $this->pk = 'id'; //used to fetch a row data in automation
        // Call the CI_Model constructor
        parent::__construct();
        $this->load->database();    // Load database
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        //orm is used to establish relation between objects and module/model for process builder
        $this->orm = [
            'Application' => [
                'module' => 'recruitment',
                'class_name' => 'Application_model'
            ],
            'Applicant' => [
                'module' => 'recruitment',
                'class_name' => 'Recruitment_applicant_model'
            ],
            'Recruiter' => [
                'module' => 'member',
                'class_name' => 'Member_model'
            ],
            'Contact' => [
                'module' => 'sales',
                'class_name' => 'Contact_model'
            ],
            'Owner' => [
                'module' => 'member',
                'class_name' => 'Member_model'
            ],
            'Shift' => [
                'module' => 'schedule',
                'class_name' => 'Schedule_model'
            ],
            'Participant' => [
                'module' => 'item',
                'class_name' => 'Participant_model'
            ],
            'Account' => [
                'module' => 'sales',
                'class_name' => 'Person_model'
            ],
            'Creator' => [
                'module' => 'member',
                'class_name' => 'Member_model'
            ],
            'Member' => [
                'module' => 'member',
                'class_name' => 'Member_model'
            ],
            'Shift Member' => [
                'module' => 'schedule',
                'class_name' => 'Shift_member_model'
            ],
            'Person Email' => [
                'module' => 'sales',
                'class_name' => 'Person_email_model'
            ],
            'GroupBooking' => [
                'module' => 'recruitment',
                'class_name' => 'Recruitment_interview_model'
            ],
            'Applicants' => [
                'module' => 'recruitment',
                'class_name' => 'Recruitment_interview_applicant'
            ]
        ];
        $this->initRecipientTypes();
        $this->initObjectRecipients();
    }

    public function get_row($table_name = '', $columns = array(), $id_array = array(), $joins = array())
    {
        if (!empty($columns)) :
            $all_columns = implode(",", $columns);
            $this->db->select($all_columns);
        endif;
        if (!empty($id_array)) :
            foreach ($id_array as $key => $value) {
                $this->db->where($key, $value);
            }
        endif;
        if (!empty($joins)) {
            foreach ($joins as $type => $tables) {
                foreach ($tables as $join) {
                    $this->db->join(TBL_PREFIX . $join['table'], $join['on'], $type);
                }
            }
        }
        $query = $this->db->get(TBL_PREFIX . $table_name);

        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }

    public function get_rows($table_name = '', $columns = array(), $id_array = array(), $joins = array())
    {
        if (!empty($columns)) :
            $all_columns = implode(",", $columns);
            $this->db->select($all_columns);
        endif;
        if (!empty($id_array)) :
            foreach ($id_array as $key => $value) {
                $this->db->where($key, $value);
            }
        endif;
        if (!empty($joins)) {
            foreach ($joins as $type => $tables) {
                foreach ($tables as $join) {
                    $this->db->join(TBL_PREFIX . $join['table'], $join['on'], $type);
                }
            }
        }
        $query = $this->db->get(TBL_PREFIX . $table_name);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    // Function for getting records from table with where condition
    function get_record_where($table, $column = '', $where = '')
    {
        if ($column != '') {
            $this->db->select($column);
        } else {
            $this->db->select('*');
        }
        $this->db->from(TBL_PREFIX . $table);
        if ($where != '') {
            $this->db->where($where);
        }
        $query = $this->db->get();
        return $query->result();
    }

    // Function for getting records from table with where condition and order by
    function get_record_where_orderby($table, $column = '', $where = '', $orderby = '', $direction = 'ASC')
    {
        if ($column != '') {
            $this->db->select($column);
        } else {
            $this->db->select('*');
        }
        $this->db->from(TBL_PREFIX . $table);
        if ($where != '') {
            $this->db->where($where);
        }
        if ($orderby != '') {
            $this->db->order_by($orderby, $direction);
        }

        $query = $this->db->get();
        return $query->result();
    }

    // Function for inserting records
    function insert_records($table, $data, $multiple = FALSE)
    {
        if ($multiple) {
            $this->db->insert_batch(TBL_PREFIX . $table, $data);
        } else {
            $this->db->insert(TBL_PREFIX . $table, $data);
        }

        return $this->db->insert_id();
    }

    // Function for updating records
    function update_records($table, $data, $where)
    {
        $this->db->where($where);
        $this->db->update(TBL_PREFIX . $table, $data);
        return $this->db->affected_rows();
    }

    // Function for deleting records
    function delete_records($table, $where)
    {
        $this->db->delete(TBL_PREFIX . $table, $where);
        if (!$this->db->affected_rows()) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function get_result($table_name = '', $id_array = '', $columns = array(), $order_by = array(), $result_type = '')
    {
        if (!empty($columns)) :
            $all_columns = implode(",", $columns);
            $this->db->select($all_columns);
        endif;
        if (!empty($order_by)) :
            $this->db->order_by($order_by[0], $order_by[1]);
        endif;

        if (!empty($id_array)) :
            foreach ($id_array as $key => $value) {
                $this->db->where($key, $value);
            }
        endif;
        $query = $this->db->get(TBL_PREFIX . $table_name);
        if ($query->num_rows() > 0) {
            return !empty($result_type) ? $query->result_array() : $query->result();
        } else {
            return FALSE;
        }
    }

    public function insert_update_batch($action = 'insert', $table_name = '', $data = [], $update_base_column_key = '')
    {
        if ($action == 'insert' && !empty($table_name) && !empty($data) && is_array($data)) {
            $this->db->insert_batch(TBL_PREFIX . $table_name, $data);
            return true;
        } elseif ($action == 'update' && !empty($table_name) && !empty($update_base_column_key) && !empty($data) && is_array($data)) {
            $this->db->update_batch(TBL_PREFIX . $table_name, $data, $update_base_column_key);
            return true;
        } else {
            return false;
        }
    }

    public function save($object, $userId)
    {
        $properties = $object->getVars();
        $properties['updated_at'] = $this->updated_at;
        $properties['updated_by'] = $userId;
        $id = $object->getId();
        if (empty($id)) {
            $properties['created_at'] = $this->created_at;
            $properties['created_by'] = $userId;
            return $this->insert_records($this->table_name, $properties);
        } else {
            return $this->update_records($this->table_name, $properties, ["id" => $id]);
        }
    }

    function clone($table, $primary_key_field, $primary_key_val, $adminId, $modify_column = '')
    {
        /* generate the select query */
        $this->db->where($primary_key_field, $primary_key_val);
        $query = $this->db->get(TBL_PREFIX . $table);

        foreach ($query->result() as $row) {
            foreach ($row as $key => $val) {
                if (!empty($modify_column) && $key == $modify_column) {
                    $val = "Clone of $val";
                }
                if ($key == 'created_at' || $key == 'updated_at') {
                    $val = date('Y-m-d H:i:s');
                }
                if ($key == 'created_by' || $key == 'updated_by') {
                    $val = $adminId;
                }
                if ($key == 'status') {
                    $val = 0;
                }
                if ($key != $primary_key_field) {
                    $this->db->set($key, $val);
                }
            }
        }
        /* insert the new record into table*/
        return $this->db->insert(TBL_PREFIX . $table);
    }

    /**
     * Get the value of orm
     */
    public function getOrm($object = '')
    {
        if (!empty($object) && array_key_exists($object, $this->orm)) {
            return $this->orm[$object];
        }
        return $this->orm;
    }

    /**
     * Set the value of orm
     *
     * @return  self
     */
    public function setOrm($orm)
    {
        $this->orm = $orm;

        return $this;
    }

    /**
     * Get the value of object_fields
     */
    public function getObjectFields($resolve = false, $condition = false)
    {
        if (!empty($this->object_fields) && is_array($this->object_fields)) {
            foreach ($this->object_fields as $k => $v) {
                if ($condition && !is_callable($v) && isset($v['hide_in_condition']) && $v['hide_in_condition'] === true) {
                    unset($this->object_fields[$k]);
                    continue;
                }
                if (is_callable($v) && $resolve) {
                    $this->object_fields[$k] = $v();
                }
            }
        }
        return $this->object_fields;
    }

    /**
     * Set the value of object_fields
     *
     * @return  self
     */
    public function setObjectFields($object_fields)
    {
        $this->object_fields = $object_fields;

        return $this;
    }

    public function loadObject($id, $columns = ['*'], $object_field = '')
    {
        $ids = [$this->pk => $id];
        //iterate all object fields to load related objects
        $object_data = $this->get_row($this->table_name, $columns, $ids);
        $fields = [];
        $object_data->object_fields = [];
        if (!empty($this->object_fields)) {
            foreach($this->object_fields as $field => $value) {
                if (!empty($object_field) && $object_field != $field) {
                    continue;
                }
                //call the method assigned to the field
                if (is_array($value) && array_key_exists('value', $value) && is_callable($value['value'])) {
                    $prop_key = $value['field'];
                    if (!is_array($prop_key) && property_exists($object_data, $prop_key)) {
                        $method = $value['value'];
                        $object_data->$field = $method($object_data->$prop_key);
                    } else if (is_array($prop_key)) {
                        $arg1 = '';
                        $arg2 = '';
                        if (property_exists($object_data, $prop_key[0])) {
                            $k = $prop_key[0];
                            $arg1 = $object_data->$k;
                        }
                        if (property_exists($object_data, $prop_key[1])) {
                            $k = $prop_key[1];
                            $arg2 = $object_data->$k;
                        }
                        $method = $value['value'];
                        $object_data->$field = $method($arg1, $arg2);
                    }                    
                }
                if (is_array($value) && array_key_exists('object_fields', $value)) {
                    $field_object_fields = $value['object_fields'];
                    if (is_array($field_object_fields)) {
                        foreach($field_object_fields as $fkey => $fval) {
                            if (is_callable($fval)) {
                                $arg1 = null;
                                $arg2 = null;
                                if (is_array($value['field'])) {
                                    $key1 = $value['field'][0];
                                    $arg1 = $object_data->$key1;
                                    $key2 = $value['field'][1];
                                    $arg2 = $object_data->$key2;
                                }
                                $field_object_fields[$fkey] = $fval($arg1, $arg2);                                
                            }
                        }
                    }
                    $fields[$field] = $field_object_fields;
                }
            }
            $object_data->object_fields = $fields;
        }
        return $object_data;
    }

    public function getStatusOptions()
    {
        return []; //return like [0 => 'Inactive', 1 => 'Active'] in child classes
    }

    /**
     * Load the model class dynamically, used for process builder
     */
    public function loadObjectModel($object_name)
    {
        if (array_key_exists($object_name, $this->orm)) {
            $model_info = $this->orm[$object_name];
            //load model
            extract($model_info);
            if (!empty($module) && !empty($class_name)) {
                $this->load->model("$module/$class_name");
                try {
                    if (@$this->$class_name) {
                        return $class_name;
                    }
                } catch (Exception $e) {
                    return null;
                }
            }
        }
        return null;
    }

    /**
     * Set object recipients for process builder
     */

    public function setObjectRecipients($recipients = [])
    {
        $this->object_recipients = array_merge($this->getGeneric_recipients(), $recipients);
        return $this;
    }

    /**
     * Return object recipients for process builder
     */

    public function getObjectRecipients($recipient_type)
    {
        $recipients = [];
        if (array_key_exists($recipient_type, $this->object_recipients)) {
            $all_recipients = array_merge($this->object_recipients, $this->generic_recipients);
            $recipients = $all_recipients[$recipient_type];
        }
        return $recipients;
    }

    public function initRecipientTypes()
    {
        $this->recipient_types = [
            'Owner' => 'Record Owner',
            'Creator' => 'Created By',
            'hcm_users' => 'HCM Users'
        ];
    }

    public function setRecipientTypes($recipient_types = [])
    {
        $this->recipient_types = array_merge($recipient_types, $this->recipient_types);
        return $this->recipient_types;
    }

    public function getRecipientTypes()
    {
        return $this->recipient_types;
    }

    public function initObjectRecipients()
    {
        //every recipient has a object_field structure
        $recipients = [];
        $recipients['Owner'] = [['field' => 'Owner', 'label' => 'Record Owner']];
        $recipients['Creator'] = [['field' => 'Creator', 'label' => 'Creator']];
        $this->setGeneric_recipients($recipients);
    }

    protected function getAdminUsers()
    {
        $users = $this->get_record_where('member', ['firstname', 'lastname', 'username'], ['is_locked' => 0, 'user_type' => 1, 'status' => 1, 'archive' => 0]);
        $admin_users = [];
        foreach ($users as $user) {
            $admin_users[$user->username] = trim($user->firstname . ' ' . $user->lastname);
        }
        return $admin_users;
    }

    /**
     * Get the value of generic_recipients
     */
    public function getGeneric_recipients()
    {
        return $this->generic_recipients;
    }

    /**
     * Set the value of generic_recipients
     *
     * @return  self
     */
    public function setGeneric_recipients($generic_recipients)
    {
        $this->generic_recipients = $generic_recipients;

        return $this;
    }

    public function loadRows($where = [], $or_where = []) {
        if (!empty($where)) {
            $this->db->where($where);
        }
        if ($or_where) {
            $this->db->or_where($or_where);
        }
        $this->db->order_by('updated_at', 'desc');
        $this->db->limit(3);
        return $this->get_result($this->table_name, [], ['*'], [], true);
    }

    // Function for getting records from table with where condition and order by
    function get_row_where_orderby($table, $column = '', $where = '', $orderby = '', $direction = 'ASC')
    {
        if ($column != '') {
            $this->db->select($column);
        } else {
            $this->db->select('*');
        }
        $this->db->from(TBL_PREFIX . $table);
        if ($where != '') {
            $this->db->where($where);
        }
        if ($orderby != '') {
            $this->db->order_by($orderby, $direction);
        }

        $query = $this->db->get();
        return $query->row();
    }
	//common function to fetch viewed by and date for listing of records
    function getViewedBy($result, $entity_type) {
        if (!empty($result)) {
            //fetch viewed by data
            $vlogs = [];
            $entity_ids = array_map(function($item){
                return $item->id;
            }, $result);
            if (!empty($entity_ids)) {
                $this->db->select(['vl.entity_id', "concat(m.firstname,' ',m.lastname) as viewed_by", 'vl.viewed_date', 'vl.viewed_by as viewed_by_id']);
                $this->db->from('tbl_viewed_log as vl');
                $this->db->join('tbl_member as m', 'vl.viewed_by=m.id and m.archive=0');
                $this->db->where(['entity_type' => $entity_type]);
                $this->db->where_in('vl.entity_id', $entity_ids);
                $query2 = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
                //echo $s = $this->db->last_query();die;
                $result2 = $query2->result();
                foreach($result2 as $v) {
                    $vlogs[$v->entity_id] = $v;
                }
            }
            foreach ($result as $val) {
                if ( array_key_exists($val->id, $vlogs) ) {
                    $val->viewed_by_id = $vlogs[$val->id]->viewed_by_id;
                    $val->viewed_by = $vlogs[$val->id]->viewed_by;
                    $val->viewed_date = $vlogs[$val->id]->viewed_date;
                }
            }
        }
        return $result;
    }
}
