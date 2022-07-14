<?php
namespace RecruitmentClass; 

if (!defined("BASEPATH")) exit("No direct script access allowed");



class Recruitment
{
    public $CI;
    function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->model('Recruitment_model');
        $this->CI->load->model('Basic_model');
    }

    public function recruitment_topic_list()
    {
        return $this->CI->Recruitment_model->Recruitment_topic_list();
    }
}
