<?php
namespace Organisation_addressClass; // if namespacce is not made in Classes(Member.php,MemberAddress.php [files]) folder, then it will give error of Cannot redeclare class 
/*
 * Filename: Organisation_all_contact.php
 * Desc: ''
 * @author YDT <yourdevelopmentteam.com.au>
*/

if (!defined("BASEPATH")) exit("No direct script access allowed");
/*
 * Class: Organisation_all_contact
 * Desc: ''
 * Created: 06-12-2018
*/

class Organisation_address
{
    public $CI;
    function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->model('Org_model');
        $this->CI->load->model('Basic_model');
    }

    private $id;
    private $organisationId;
    private $street;
    private $city;
    private $postal;
    private $state;
    private $category;
    private $primary_address;

    function setId($id) { $this->id = $id; }
    function getId() { return $this->id; }
    function setOrganisationId($organisationId) { $this->organisationId = $organisationId; }
    function getOrganisationId() { return $this->organisationId; }
    function setStreet($street) { $this->street = $street; }
    function getStreet() { return $this->street; }
    function setCity($city) { $this->city = $city; }
    function getCity() { return $this->city; }
    function setPostal($postal) { $this->postal = $postal; }
    function getPostal() { return $this->postal; }
    function setState($state) { $this->state = $state; }
    function getState() { return $this->state; }
    function setCategory($category) { $this->category = $category; }
    function getCategory() { return $this->category; }
    function setPrimary_address($primary_address) { $this->primary_address = $primary_address; }
    function getPrimary_address() { return $this->primary_address; }

}
