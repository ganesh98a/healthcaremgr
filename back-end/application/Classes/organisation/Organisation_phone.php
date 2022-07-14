<?php
namespace Organisation_phoneClass; // if namespacce is not made in Classes(Member.php,MemberAddress.php [files]) folder, then it will give error of Cannot redeclare class 
/*
 * Filename: Organisation_phone.php
 * Desc: ''
 * @author YDT <yourdevelopmentteam.com.au>
*/

if (!defined("BASEPATH")) exit("No direct script access allowed");
/*
 * Class: Organisation_phone
 * Desc: ''
 * Created: 06-12-2018
*/

class Organisation_phone
{
    public $CI;
    function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->model('Org_model');
        $this->CI->load->model('Basic_model');
    }

    private $organisationId;
    private $phone;
    private $primary_phone;
    private $Id;

    function setId($Id) { $this->Id = $Id; }
    function getId() { return $this->Id; }
    function setOrganisationId($organisationId) { $this->organisationId = $organisationId; }
    function getOrganisationId() { return $this->organisationId; }
    function setPhone($phone) { $this->phone = $phone; }
    function getPhone() { return $this->phone; }
    function setPrimary_phone($primary_phone) { $this->primary_phone = $primary_phone; }
    function getPrimary_phone() { return $this->primary_phone; }

    function add_contact()
    {
        $data = array('siteId'=>$this->getsiteId(),
            'firstname'=>$this->getName(),
            'lastname'=>$this->getLastname(),
            'position'=>$this->getPosition(),
            'department'=>$this->getDepartment(),
            'type'=>$this->getType()
        );
        return $this->CI->Basic_model->insert_records('house_and_site_key_contact', $data);
    }

    function update_contact()
    {
        $data = array('siteId'=>$this->getsiteId(),
            'firstname'=>$this->getName(),
            'lastname'=>$this->getLastname(),
            'position'=>$this->getPosition(),
            'department'=>$this->getDepartment(),
            'type'=>$this->getType()
        );
        $c = $this->CI->Basic_model->update_records('tbl_', $data,array('id'=>$this->getId()));
        return $c;
    }
}
