<?php
/** Files holds the document type related operation */
defined('BASEPATH') OR exit('No direct script access allowed');

class Document_type_model extends CI_Model {

	public function __construct() {
        // Call the CI_Model constructor
    parent::__construct();
  }

  /**
   * Get document type from tbl_document_type table
   * @param $reqData {array} it has col1, col2, document category, module id
   * @param $multiple {bool} TRUE/FALSE set true for fetch more than one category list
   *  and pass more than one value in reqData['doc_category] as a array format
   * @param $active {bool} TRUE/FALSE
   * @param $archive {bool} TRUE/FALSE
   *
   * @return {ojbect} document types
   */
  public function get_document_type($reqData, $multiple = NULL, $active = 1, $archive = 0) {

    if(!$reqData) {
      return;
    }

    $this->db->select(['docs.title as '. $reqData['col1'], 'docs.id as ' . $reqData['col2'], 'docs.issue_date_mandatory', 'docs.expire_date_mandatory', 'docs.reference_number_mandatory']);

    $this->db->from(TBL_PREFIX . 'document_type as docs');
    $this->db->join(TBL_PREFIX . 'document_type_related as docs_rel', 'docs.id = docs_rel.doc_type_id', 'inner');
    $this->db->join(TBL_PREFIX . 'document_category as docs_cat', 'docs.doc_category_id = docs_cat.id', 'inner');

    if($multiple) {
      $this->db->where_in('docs_cat.key_name', $reqData['doc_category']);
    }
    else {
      $this->db->where('docs_cat.key_name', $reqData['doc_category']);
    }

    $this->db->where('docs_rel.related_to', $reqData['module_id']);
    $this->db->where('docs.archive', $archive);
    $this->db->where('docs.active', $active);

    $query = $this->db->get();

    return $query->result_object();

  }
  /**
   * @param $archive {bool} TRUE/FALSE
   * @return {array} title and id
   *
   * To gets the document category label and value
   */
  public function get_document_category( $archive = 0) {

    $this->db->select(['title as label', 'id as value']);
    $this->db->from(TBL_PREFIX . 'document_category');
    $this->db->where('archive', $archive);

    $query = $this->db->get();
    return $query->num_rows() > 0 ? $query->result_array() : [];

  }

  /**
   * @param $id {int} category id
   * @return {array} document category list
   * To fetch the document category by its id
   */
  public function get_document_category_by_id($id) {
    if(!$id)
      return;

    $this->db->select(['title as label', 'id as value']);
    $this->db->from(TBL_PREFIX . 'document_category');
    $this->db->where('id', $id);

    $query = $this->db->get();
    return $query->num_rows() > 0 ? $query->result_array() : [];

  }

  /**
   * @param $id {int} category id
   * @param $column {array} column name
   * @return {array} document category list
   * To fetch the document related to by its id
   */
  public function get_document_related_to_by_id($id, $column) {
    if(!$id)
      return;

    $this->db->select([$column]);
    $this->db->from(TBL_PREFIX . 'document_type_related');
    $this->db->where('doc_type_id', $id);
    $this->db->where('archive', 0);

    $query = $this->db->get();
    return $query->num_rows() > 0 ? $query->result_array() : [];

  }

  /**
   * @param $id {int} category id
   * @param $column {array} column name
   * @return {array} document category list
   * To fetch the document related to by its id
   */
  public function get_document_not_related_to_by_id($id, $column) {
    if(!$id)
      return;

    $this->db->select([$column]);
    $this->db->from(TBL_PREFIX . 'document_type_related');
    $this->db->where('doc_type_id !=', $id);

    $query = $this->db->get();
    return $query->num_rows() > 0 ? $query->result_array() : [];

  }

  /**
   * @param $title {string} value of the document title
   * @param $column {string} name of the single column/columns
   *
   * @return {obj} single row value
   *
   * Fetch the auto generated document ivalue
   */
  public function get_auto_generated_doc_by_title($title, $column) {
    $this->db->select([$column]);
    $this->db->from(TBL_PREFIX . 'document_type');
    $this->db->where('title', $title);

    $query = $this->db->get();
    return $query->num_rows() > 0 ? $query->row() : NULL;
  }

   /**
   * Get document type from tbl_document_type table
   * @param $reqData {array} it has col1, col2, document category, module id
   * @param $multiple {bool} TRUE/FALSE set true for fetch more than one category list
   *  and pass more than one value in reqData['doc_category] as a array format
   * @param $active {bool} TRUE/FALSE
   * @param $archive {bool} TRUE/FALSE
   *
   * @return {ojbect} document types
   */
  public function get_document_type_search($reqData, $multiple = NULL, $active = 1, $archive = 0) {

    if(!$reqData) {
      return;
    }

    $this->db->select(['docs.title as '. $reqData['col1'], 'docs.id as ' . $reqData['col2'], 'docs.issue_date_mandatory', 'docs.expire_date_mandatory', 'docs.reference_number_mandatory']);
    
    $this->db->from(TBL_PREFIX . 'document_type as docs');
    $this->db->join(TBL_PREFIX . 'document_type_related as docs_rel', 'docs.id = docs_rel.doc_type_id', 'inner');
    $this->db->join(TBL_PREFIX . 'document_category as docs_cat', 'docs.doc_category_id = docs_cat.id', 'inner');

    if($multiple) {
      $this->db->where_in('docs_cat.key_name', $reqData['doc_category']);
    }
    else {
      $this->db->where('docs_cat.key_name', $reqData['doc_category']);
    }

    $this->db->where('docs_rel.related_to', $reqData['module_id']);
    $this->db->where('docs.archive', $archive);
    $this->db->where('docs.active', $active);
    $this->db->like('docs.title', $reqData['query_label']);
    $query = $this->db->get();

    return $query->result_object();

  }
}
