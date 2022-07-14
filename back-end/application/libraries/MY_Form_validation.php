<?php

class MY_Form_validation extends CI_Form_validation
{
    public $CI;

    /**
     * Validate date/datetime against given format
     * 
     * @param string $date 
     * @param string $format 
     * @return boolean 
     * 
     * @todo: Maybe write unit test
     */
    public function valid_date_format($date, $format = 'Y-m-d H:i:s')
    {
        $format = !!$format ? $format : 'Y-m-d H:i:s';

        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * Check if date preceeds or equal to the given date
     * Inspired by laravel `before_of_equal`
     * 
     * @param mixed $date
     * @param string $before  
     * @return boolean
     * 
     * @todo: Maybe write unit test 
     */
    public function before_or_equal($date, $before)
    {
        $datetime = strtotime($date);
        $beforetime = strtotime($before);

        return $datetime <= $beforetime;
    }


    /**
     * Exists.
     * 
     * Check if the input value exist in the specified database field.
     *
     * @param string $str
     * @param string $field
     * @return bool
     */
    public function exists($str, $field)
    {
        sscanf($field, '%[^.].%[^.]', $table, $field);

        $query = $this->CI->db->limit(1)->get_where($table, array($field => $str));
        if ($query->num_rows() > 0) {
            return true;
        }

        return false;
    }
}
