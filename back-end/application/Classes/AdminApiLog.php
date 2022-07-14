<?php

/*
 * To use as place holder for managing api log throughout the HCM
 * adding, updating, deleting and viewing api log
 * 
 * @author Pranav Gajjar
 */

class AdminApiLog
{
    private $data;

    function __construct()
    {
        $this->CI = &get_instance();
    }

    /**
     * setting the data set
     */
    function setData($data)
    {
        $this->data = $data;
    }

    /**
     * getting the data set
     */
    function getData()
    {
        return $this->data;
    }

    /**
     * insert function to insert the data into log table
     */
    function createAdminApiLog()
    {
        $postdata = $this->getData();

        $result = $this->CI->basic_model->insert_records('admin_api_log', $postdata, $multiple = FALSE);
        return $result;
    }
}
