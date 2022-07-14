<?php


/**
 * Get configuration from DB or use default if not found
 * 
 * @param string $key 
 * @param string|null $default
 * @param int $filter Will pass through `filter_var` when the configuration was found in DB.
 * @return string|null
 */
function get_setting($key, $default = null, $filter = FILTER_DEFAULT)
{
    $CI =& get_instance();

    $query = $CI->db->get_where('tbl_settings', [ 'key' => $key ], 1);
    $result = $query->row_array();

    if (!$result) {
        return $default;
    }

    return filter_var($result['value'], $filter);
}

/**
 * Apply settings. If setting is not found in database, will insert it
 * 
 * @param mixed $key 
 * @param mixed $newValue 
 * @return void 
 * @throws \Exception When key is not given or was not saved
 */
function save_setting($key, $newValue = '') 
{
    if (empty(trim($key))) {
        throw new \Exception('Settings key must be provided');
    }

    $CI =& get_instance();
    
    $query = $CI->db->get_where('tbl_settings', [ 'key' => $key ], 1);
    $result = $query->row_array();

    // insert
    if (!$result) {
        $CI->db->insert('tbl_settings', [ 
            'key' => $key,
            'value' => $newValue,
        ]);
        
        return [
            'id' => $CI->db->insert_id(),
            'key' => $key,
            'value' => $newValue
        ];
    }

    // update
    $isUpdated = $CI->db->update('tbl_settings', [ 'value' => $newValue ], [
        'id' => $result['id'],
        'key' => $key,
    ]);

    if (!$isUpdated) {
        throw new \Exception("Settings was not updated");
    }

    return [
        'id' => $result['id'],
        'key' => $key,
        'value' => $newValue,
    ];
}