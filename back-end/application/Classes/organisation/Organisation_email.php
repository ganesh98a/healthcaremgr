<?php
namespace Organisation_emailClass; // if namespacce is not made in Classes(Member.php,MemberAddress.php [files]) folder, then it will give error of Cannot redeclare class 
/*
 * Filename: Organisation_email.php
 * Desc: ''
 * @author YDT <yourdevelopmentteam.com.au>
*/

if (!defined("BASEPATH")) exit("No direct script access allowed");
/*
 * Class: Organisation_email
 * Desc: ''
 * Created: 06-12-2018
*/

class Organisation_email
{
    public $CI;
    function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->model('Org_model');
        $this->CI->load->model('Basic_model');
    }

    private $organisationId;
    private $email;
    private $primary_email;
    private $Id;

    function setId($Id) { $this->Id = $Id; }
    function getId() { return $this->Id; }
    function setOrganisationId($organisationId) { $this->organisationId = $organisationId; }
    function getOrganisationId() { return $this->organisationId; }
    function setEmail($email) { $this->email = $email; }
    function getEmail() { return $this->email; }
    function setPrimary_email($primary_email) { $this->primary_email = $primary_email; }
    function getPrimary_email() { return $this->primary_email; }
}
