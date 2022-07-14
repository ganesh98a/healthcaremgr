<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Class : Document_model
 * Uses : for handle query operation of document
 *
 */
class Document_model extends CI_Model {

    function __construct() {

        parent::__construct();
        $this->load->model('document/Document_type_model');
    }

    /*
     * It is used to get the document list
     *
     * Operation:
     *  - searching
     *  - filter
     *  - sorting
     *
     * Return type Array
     */
    public function get_document_list($reqData,$filter_condition='') {
        // Get subqueries
        if (empty($reqData)) return;

        $limit = $reqData->pageSize?? 99999;
        $page = $reqData->page?? 1;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';
        

        // Searching column
        $src_columns = array('title', 'active');
        if (isset($filter->search) && $filter->search != '') {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (!empty($filter->filter_by) && $filter->filter_by != 'all' && $filter->filter_by != $column_search) {
                    continue;
                }
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                } else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $available_column = ["document_id", "title", "active", "issue_date_mandatory", "expire_date_mandatory", "reference_number_mandatory", "archive"];
        // Sort by id
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column) ) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'td.id';
            $direction = 'DESC';
        }

        // Filter by status
        if (!empty($filter->filter_status)) {
            if ($filter->filter_status === "active") {
                $this->db->where('td.active', 1);
            } else if ($filter->filter_status === "inactive") {
                $this->db->where('td.active', 0);
            }
        }

        $select_column = ["td.id as document_id", "td.title", "td.active", "td.issue_date_mandatory", "td.expire_date_mandatory", "td.reference_number_mandatory", "td.archive"];
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(CASE
            WHEN td.issue_date_mandatory = 1 THEN 'Yes'
            WHEN td.issue_date_mandatory = 0 THEN 'No'
            Else '' end
        ) as issue_date_mandatory");
        $this->db->select("(CASE
            WHEN td.expire_date_mandatory = 1 THEN 'Yes'
            WHEN td.expire_date_mandatory = 0 THEN 'No'
            Else '' end
        ) as expire_date_mandatory");
        $this->db->select("(CASE
            WHEN td.reference_number_mandatory = 1 THEN 'Yes'
            WHEN td.reference_number_mandatory = 0 THEN 'No'
            Else '' end
        ) as reference_number_mandatory");
        $this->db->select("(CASE
            WHEN td.active = 1 THEN 'Yes'
            WHEN td.active = 0 THEN 'No'
			Else '' end
		) as active");
        $this->db->from('tbl_document_type as td');
        $this->db->where('td.archive', 0);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        //pr(last_query());
        // Get total rows inserted count
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // If limit 0 return empty
        if ($limit == 0) {
            $return = array('count' => $dt_filtered_total, 'data' => array(), 'status' => false, 'error' => 'Pagination divide by zero');
            return $return;
        }

        // Get the count per page and total page
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        // Get the query result
        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetch document list successfully', 'total_item' => $total_item );
        return $return;
    }

    /**
     * fetching a single role document details
     */
    public function get_role_doc_details($id) {
        $select_column = ["dr.id", "dr.document_id", "dr.role_id", "dr.mandatory"];
        $this->db->select($select_column);
        $this->db->select("(CASE
            WHEN dr.start_date = '0000-00-00 00:00:00' THEN ''
            Else DATE_FORMAT(dr.start_date, '%Y-%m-%d')  end
        ) as start_date");
        $this->db->select("(CASE
            WHEN dr.end_date = '0000-00-00 00:00:00' THEN ''
            Else DATE_FORMAT(dr.end_date, '%Y-%m-%d')  end
        ) as end_date");
        $this->db->from('tbl_document_role as dr');
        $this->db->where('dr.archive', 0);
        $this->db->where('dr.id', $id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        // last_query();
        if (empty($result)) {
            $return = array('msg' => "Member not found!", 'status' => false);
            return $return;
        }
        $doc_details = $this->get_document_name_search(null, $result[0]->document_id);
        if($doc_details)
        $result[0]->doc_details = $doc_details[0];

        $role_details = $this->get_role_name_search(null, $result[0]->role_id);
        if($doc_details)
        $result[0]->role_details = $role_details[0];

        $return = array('data' => $result[0], 'status' => true);
        return $return;
    }

    /**
     * fetches only role specific documents. Need to have 'role_id' in the data element
     */
    public function get_role_documents($reqData) {

        $searchData = null;
        if(isset($reqData->request) && !empty($reqData->request))
        $searchData = json_decode($reqData->request);

        // pr($searchData);

        $limit = $searchData->pageSize ?? 6;
        $page = $searchData->page ?? 0;
        $sorted = $searchData->sorted ?? [];
        $filter = $searchData->filtered ?? [];
        $orderBy = '';
        $direction = '';
        $available_column = ["id", "role_id", "document_id","name", "title", "start_date", "end_date", "created"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id == 'id' ? 'm.id' : $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'dr.id';
            $direction = 'DESC';
        }

        # new lightening filters
        if(isset($filter->filters)) {
            foreach($filter->filters as $filter_obj) {
                if(empty($filter_obj->select_filter_value)) continue;

                $sql_cond_part = GetSQLCondPartFromSymbol($filter_obj->select_filter_operator_sym, $filter_obj->select_filter_value);
                if($filter_obj->select_filter_field == "title") {
                    $this->db->where('d.title '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "name") {
                    $this->db->where('mr.name '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "mandatory_label") {
                    if($filter_obj->select_filter_value == 1)
                    $this->db->where('dr.mandatory = 1');
                    else if($filter_obj->select_filter_value == 2)
                    $this->db->where('dr.mandatory = 0');
                }
                if($filter_obj->select_filter_field == "created" || $filter_obj->select_filter_field == "start_date" || $filter_obj->select_filter_field == "end_date") {
                    $this->db->where('DATE_FORMAT(dr.'.$filter_obj->select_filter_field.', "%Y-%m-%d") '.GetSQLOperator($filter_obj->select_filter_operator_sym), DateFormate($filter_obj->select_filter_value, 'Y-m-d'));
                }
            }
        }

        $select_column = ["dr.id", "dr.role_id", "d.id as document_id","mr.name", "d.title", "dr.start_date", "dr.end_date", "dr.created"];
        // $this->db->select($select_column);
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(CASE
            WHEN dr.mandatory = 1 THEN 'Yes'
            WHEN dr.mandatory = 0 THEN 'No'
            Else '' end
        ) as mandatory_label");
        $this->db->select("(CASE
            WHEN dr.start_date = '0000-00-00 00:00:00' THEN ''
            Else DATE_FORMAT(dr.start_date, '%Y-%m-%d')  end
        ) as start_date");
        $this->db->select("(CASE
            WHEN dr.end_date = '0000-00-00 00:00:00' THEN ''
            Else DATE_FORMAT(dr.end_date, '%Y-%m-%d')  end
        ) as end_date");
        $this->db->from('tbl_document_type as d');
        $this->db->join('tbl_document_role as dr', 'd.id = dr.document_id', 'inner');
        $this->db->join('tbl_member_role as mr', 'mr.id = dr.role_id', 'inner');
        $this->db->where('d.archive', 0);
        $this->db->where('dr.archive', 0);

        if(isset($reqData->role_id) && $reqData->role_id > 0)
        $this->db->where('dr.role_id', $reqData->role_id);

        if(isset($reqData->doc_id) && $reqData->doc_id > 0)
        $this->db->where('dr.document_id', $reqData->doc_id);

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // Get total rows inserted count
        // last_query();
        $total_rows = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        # Get the count per page and total page
        if ($total_rows % $limit == 0) {
            $dt_filtered_total = ($total_rows / $limit);
        } else {
            $dt_filtered_total = ((int) ($total_rows / $limit)) + 1;
        }
        # Get the query result
        $result = $query->result();
        $return = array('total_rows' => $total_rows, 'count' => count($result), 'data' => $result, 'status' => true, 'msg' => 'Fetched role documents list successfully');
        return $return;
    }

    /*
     * its used for fetching list of role names based on @param $name
     *
     * @params
     * $name search parameter
     *
     * return type array
     */
    public function get_role_name_search($name = '', $id = '') {

        $queryHaving = null;
        if(!$id) {
            $this->db->like('label', $name);
            $queryHavingData = $this->db->get_compiled_select();
            $queryHavingData = explode('WHERE', $queryHavingData);
            $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
        }

        $this->db->select(["mr.name as label", "mr.id as value"]);
        $this->db->from(TBL_PREFIX . 'member_role as mr');
        $this->db->where(['mr.archive' => 0]);

        if($id)
            $this->db->where(['mr.id' => $id]);
        else
            $this->db->having($queryHaving);

        $sql = $this->db->get_compiled_select();
        $query = $this->db->query($sql);

        return $result = $query->result();
    }

    /** Get the service type name from member role */
    public function get_role_by_name($name) {

        $this->db->select(["mr.name as label", "mr.id as value"]);
        $this->db->from(TBL_PREFIX . 'member_role as mr');
        $this->db->where(['mr.archive' => 0]);
        $this->db->where('name', $name);

        $query = $this->db->get();

        return $query->result();
    }

    /*
     * its used for fetching list of role names based on @param $name related to account
     *
     * @params
     * $name search parameter
     *
     * return type array
     */
    public function get_rolename_for_account($reqData) {
        //print_r($reqData);exit();
        if (isset($reqData) && isset($reqData->data->value)) 
        {
            $account_type = $reqData->data->account_type;
            $id = $reqData->data->value;
            if($account_type == "1") {
                $this->db->select(["mr.name as label", "mr.id as value"]);        
                $this->db->from(TBL_PREFIX . 'member_role as mr');
                $this->db->join('tbl_participants_master as pt', 'pt.role_id = mr.id', 'inner');
                $this->db->where(['mr.archive' => 0]);
                $this->db->where(['pt.id' => $id]);
                $query = $this->db->get();
                $result = $query->result_array();
                return array('status' => true, 'data' => $result);
            }
            else if($account_type == "2") {
                $this->db->select(["mr.name as label", "mr.id as value"]);        
                $this->db->from(TBL_PREFIX . 'member_role as mr');
                $this->db->join('tbl_organisation as pt', 'pt.role_id = mr.id', 'inner');
                $this->db->where(['mr.archive' => 0]);
                $this->db->where(['pt.id' => $id]);
                $query = $this->db->get();
                $result = $query->result_array();
                return array('status' => true, 'data' => $result);
            }
        }
    }

    /*
     * its used for fetching list of document names based on @param $name
     *
     * @params
     * $name search parameter
     *
     * return type array
     */
    public function get_document_name_search($name = '', $id = '') {

        $queryHaving = null;
        if(!$id) {
            $this->db->like('label', $name);
            $queryHavingData = $this->db->get_compiled_select();
            $queryHavingData = explode('WHERE', $queryHavingData);
            $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
        }

        $this->db->select(["d.title as label", "d.id as value"]);
        $this->db->from(TBL_PREFIX . 'document_type as d');
        $this->db->join('tbl_document_type_related as tdt', 'd.id = tdt.doc_type_id AND tdt.related_to=2 and tdt.archive=0 ', 'right');
        $this->db->where(['d.archive' => 0]);

        if($id)
            $this->db->where(['d.id' => $id]);
        else
            $this->db->having($queryHaving);

        $sql = $this->db->get_compiled_select();
        $query = $this->db->query($sql);

        return $result = $query->result();
    }

    /**
     * checks if an entry exists for a role & document
     */
    public function check_role_doc_already_exist($role_id,$doc_id,$id=0) {
        $this->db->select(array('d.title'));
        $this->db->from('tbl_document_role as dr');
        $this->db->join('tbl_document_type as d', 'd.id = dr.document_id', 'inner');
        $this->db->where('dr.archive', 0);
        if($id>0)
            $this->db->where('dr.id != ', $id);

        $this->db->where("dr.role_id", $role_id);
        $this->db->where("dr.document_id", $doc_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return $result;
    }

    /**
     * creates a new document role record or updates an existing one
     */
    function attach_document_and_role($data, $adminId) {
        $role_doc_id = $data['id'] ?? 0;

        $data = [
            "document_id" => $data['doc_id'],
            "role_id" => $data['role_id'],
            "start_date" => $data['start_date'],
            "end_date" => $data['end_date'],
            "mandatory" => $data["mandatory"] ?? '0',
            "archive" => 0
        ];

        if ($role_doc_id) {
            $data["updated"] = DATE_TIME;
            $data["updated_by"] = $adminId;
            $this->basic_model->update_records("document_role", $data, ["id" => $role_doc_id]);
            return $role_doc_id;
        } else {
            $data["created"] = DATE_TIME;
            $data["created_by"] = $adminId;
            return $this->basic_model->insert_records("document_role", $data, $multiple = FALSE);
        }

        return $role_doc_id;
    }

    /*
     * To create document
     *
     * @params {array} $data
     * @params {int} $adminId
     *
     * return type documentId
     */
    function create_document($data, $adminId) {
        // Assign the data
        $insData = [
            'title' => $data["title"],
            'issue_date_mandatory' => $data["issue_date_mandatory"],
            'expire_date_mandatory' => $data["expire_date_mandatory"],
            'reference_number_mandatory' => $data["reference_number_mandatory"],
            'doc_category_id' => $data['doc_category'],
            'active' => $data["active"],
            'created_by' => $adminId,
            'created_at' => DATE_TIME,
        ];
        // Insert the data using basic model function
        $documentId = $this->basic_model->insert_records('document_type', $insData);

        //Insert Document related to data
        if($documentId && !empty($data['doc_related_to_selection'])) {

            $related = $rel = [];

            foreach($data['doc_related_to_selection'] as $val) {
                $rel['doc_type_id'] = $documentId;
                $rel['related_to'] = $val->id;
                $rel['created_by'] = $rel['updated_by'] = $adminId;
                $rel['created_at'] = $rel['updated_at'] = DATE_TIME;
                $related[] = $rel;
            }

            $this->basic_model->insert_records('document_type_related', $related, TRUE);
        }

        return $documentId;
    }

    /*
     *
     * @params {object} $reqData
     *
     * Return type Array - $result
     */
    public function get_document_detail_by_id($reqData) {
        if (isset($reqData) && isset($reqData->document_id)) {
            // Get subquery of cerated & updated by
            $created_by_sub_query = $this->get_created_updated_by_sub_query('created_by','td');
            $updated_by_sub_query = $this->get_created_updated_by_sub_query('updated_by','td');

            $document_id = $reqData->document_id;
            $column = ["td.id as document_id", "td.title", "td.active", "td.issue_date_mandatory", "td.expire_date_mandatory", "td.reference_number_mandatory", "td.archive", "td.created_by", "td.created_at", "td.updated_by", "td.updated_at", "doc_category_id"];
            $orderBy = "td.id";
            $orderDirection = "DESC";
            $this->db->select($column);

            $this->db->select("(" . $created_by_sub_query . ") as created_by");
            $this->db->select("(" . $updated_by_sub_query . ") as updated_by");
            $this->db->select("(CASE
                WHEN td.issue_date_mandatory = 1 THEN 'Yes'
                WHEN td.issue_date_mandatory = 0 THEN 'No'
                Else '' end
            ) as issue_date_mandatory");
            $this->db->select("(CASE
                WHEN td.expire_date_mandatory = 1 THEN 'Yes'
                WHEN td.expire_date_mandatory = 0 THEN 'No'
                Else '' end
            ) as expire_date_mandatory");
            $this->db->select("(CASE
                WHEN td.reference_number_mandatory = 1 THEN 'Yes'
                WHEN td.reference_number_mandatory = 0 THEN 'No'
                Else '' end
            ) as reference_number_mandatory");
            $this->db->select("(CASE
                WHEN td.active = 1 THEN 'Yes'
                WHEN td.active = 0 THEN 'No'
                Else '' end
            ) as active");
            $this->db->from(TBL_PREFIX . 'document_type as td');
            $this->db->order_by($orderBy, $orderDirection);
            $this->db->where(['td.id' => $document_id]);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->num_rows() > 0 ? $query->result_array() : [];
            $result = $query->num_rows() > 0 ? $result[0] : [];
            $cat = $this->Document_type_model->get_document_category_by_id($result['doc_category_id']);

            $result['document_category'] = isset($cat) ? $cat[0]['label'] : 'N/A';

            $rel = $this->Document_type_model->get_document_related_to_by_id($document_id, 'related_to');

            if($rel) {
                $cons = DOC_RELATED_TO_LIST;

                foreach($rel as $related) {
                    $related_to[] = $cons[$related['related_to'] - 1]['label'];
                }

                $result['document_related_to'] = ($related_to) ? implode(',' , $related_to) : 'N/A';
            }

            return [ "status" => true, 'data' => $result, 'msg' => 'Fetch document detail successfully' ];
        } else {
            return [ "status" => false, 'error' => 'Document Id is null'];
        }
    }

    /*
     * it is used for making sub query created by (who creator|updated of document)
     * return type sql
     */
    private function get_created_updated_by_sub_query($column_by, $tbl_alais) {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = ".$tbl_alais.".".$column_by, null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     *
     * @params {object} $reqData
     *
     * Return type Array - $result
     */
    public function get_document_data_by_id($reqData) {
        if (isset($reqData) && isset($reqData->document_id)) {

            $document_id = $reqData->document_id;
            $column = ["td.id as document_id", "td.title", "td.active", "td.issue_date_mandatory", "td.expire_date_mandatory", "td.reference_number_mandatory", "td.archive", "td.created_by", "td.created_at", "td.updated_by", "td.updated_at", "doc_category_id"];
            $orderBy = "td.id";
            $orderDirection = "DESC";
            $this->db->select($column);

            $this->db->from(TBL_PREFIX . 'document_type as td');
            $this->db->order_by($orderBy, $orderDirection);
            $this->db->where(['td.id' => $document_id]);
            $this->db->limit(1);
            $query = $this->db->get();
            $result = $query->num_rows() > 0 ? $query->result_array() : [];
            $result = $query->num_rows() > 0 ? $result[0] : [];

            $cat = $this->Document_type_model->get_document_category_by_id($result['doc_category_id']);

            $result['doc_category'] = isset($cat) ? $cat[0]['label'] : '';

            $rel = $this->Document_type_model->get_document_related_to_by_id($document_id, '*');

            $result['doc_related_to_id'] = [];
            $result['doc_related_to_selection'] = [];

            if($rel) {
                $cons = DOC_RELATED_TO_LIST;
                $related_to = [];
                $related_to_ids = [];
                foreach($rel as $rel_key => $related) {
                    $related_to[$rel_key]['label'] = $cons[$related['related_to'] - 1]['label'];
                    $related_to[$rel_key]['value'] = $cons[$related['related_to'] - 1]['id'];
                    $related_to[$rel_key]['id'] = $cons[$related['related_to'] - 1]['id'];
                    $related_to_ids[] = $cons[$related['related_to'] - 1]['id'];
                }

                $result['doc_related_to_id'] = ($related_to_ids) ? implode(',' , $related_to_ids) : '';
                $result['doc_related_to_selection'] = $related_to;
            }

            return [ "status" => true, 'data' => $result, 'msg' => 'Fetch document detail successfully' ];
        } else {
            return [ "status" => false, 'error' => 'Document Id is null'];
        }
    }

    /*
     * To Update document
     *
     * @params {array} $data
     * @params {int} $adminId
     *
     * return type documentId
     */
    function update_document($data, $adminId) {
        // Assign the data
        $documentId = $data["document_id"];
        $upData = [
            'title' => $data["title"],
            'issue_date_mandatory' => $data["issue_date_mandatory"] == true ? 1 : 0,
            'expire_date_mandatory' => $data["expire_date_mandatory"] == true ? 1 : 0,
            'reference_number_mandatory' => $data["reference_number_mandatory"] == true ? 1 : 0,
            'doc_category_id' => $data['doc_category'] ?? '',
            'active' => $data["active"] == true ? 1 : 0,
            'updated_by' => $adminId,
            'updated_at' => DATE_TIME,
        ];

        $where = array( "id" => $data["document_id"]); 
        // Insert the data using basic model function
        $updatedDocumentId = $this->basic_model->update_records('document_type', $upData, $where);

        // Update existing document related to
        $updDataRel["updated_at"] = DATE_TIME;
        $updDataRel["updated_by"] = $adminId;
        $updDataRel["archive"] = 1;
        $whereRel = array( "doc_type_id" => $documentId); 
        $documentTypeRelated = $this->basic_model->update_records('document_type_related', $updDataRel, $whereRel);

        if($documentId && !empty($data['doc_related_to_selection'])) {
            $related = $rel = [];

            foreach($data['doc_related_to_selection'] as $val) {
                $val = (array) $val;
                // Check document is exist. Using title
                $column = array('id');
                $where = array('doc_type_id' => $documentId, 'related_to' => $val['id']);
                $check_document = $this->basic_model->get_record_where('document_type_related', $column, $where);
                if (isset($check_document) == true && empty($check_document) == false ) {
                    // Update existing document related to archive status
                    $docRelId = $check_document[0]->id;
                    $updDataRel["updated_at"] = DATE_TIME;
                    $updDataRel["updated_by"] = $adminId;
                    $updDataRel["archive"] = 0;
                    $whereRel = array( "id" => $docRelId); 
                    $documentTypeRelated = $this->basic_model->update_records('document_type_related', $updDataRel, $whereRel);
                } else {
                    $rel['doc_type_id'] = $documentId;
                    $rel['related_to'] = $val['id'];
                    $rel['created_by'] = $rel['updated_by'] = $adminId;
                    $rel['created_at'] = $rel['updated_at'] = DATE_TIME;
                    $related[] = $rel;
                }
            }
            if (isset($related) == true && empty($related) == false) {
                $this->basic_model->insert_records('document_type_related', $related, TRUE);
            }
        }

        return $documentId;
    }

    /*
     * it is used for get visa category
     *    
     * return type array
     *
     */
    public function get_all_visa_category() {       
        $this->db->select(["vc.category as label", 'vc.id as value']);
        $this->db->from(TBL_PREFIX . 'member_visa_type_category as vc');
        $this->db->where(['vc.archive' => 0]);
        $sql = $this->db->get_compiled_select();

        $query = $this->db->query($sql);
        return $query->result();
    }

    /*
     * it is used for get visa type name on base of @param $visa_category
     *
     * @params
     * $visa_category search parameter
     *
     * return type array
     *
     */
    public function get_all_visa_type_by_visa_category($visa_category = '') {
        
        $this->db->select(["vt.visa_type as label", 'vt.id as value', 'vt.visa_type_no as visa_type_no']);
        $this->db->from(TBL_PREFIX . 'member_visa_type_category as vc');
        $this->db->join('tbl_member_visa_type as vt', 'vc.id = vt.visa_type_category_id and vt.archive=0 ', 'left');
        $this->db->where(['vc.archive' => 0, 'vt.visa_type_category_id' => $visa_category]);
        $sql = $this->db->get_compiled_select();

        $query = $this->db->query($sql);
        return $query->result();
    }
}


