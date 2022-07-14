<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model that fetches and persist data into `tbl_references` table.
 * 
 * Note: There's also a specialized model `Recruitment_reference_data_model` inside 
 * recruitment module solely for displaying 'reference' items. 
 * 
 * You may want to take a look on that as well
 */
class Reference_model extends CI_Model
{
    const TYPE_LEAD_SOURCE = 1;
    const TYPE_HOBBIES = 2;
    const TYPE_TITLE = 3;
    

    /**
     * Finds all title references and then 
     * pick `id` and `display` from each of the results and 
     * map as `value` and `label` respectively
     * 
     * @return array[]
     */
    public function get_title_reference_options()
    {
        $refs = $this->find_all_title_references();

        return array_map(function ($ref) {
            return [
                'value' => $ref['id'],
                'label' => $ref['display_name'],
            ];
        }, $refs);
    }


    /**
     * Find all reference with type of 'title' (id: 3).
     * 
     * @return array[]
     */
    public function find_all_title_references()
    {
        $query = $this->db
            ->from('tbl_references AS ref')
            ->join('tbl_reference_data_type AS ref_type', 'ref_type.id = ref.type AND ref_type.archive = 0', 'INNER')
            ->where([
                'ref.type' => self::TYPE_TITLE,
                'ref.archive' => 0,
            ])
            ->select(['ref.*'])
            ->get();

        $results = $query->result_array();
        return $results;
    }

}