<?php
namespace Organisation_site_all_contactClass; // if namespacce is not made in Classes(Member.php,MemberAddress.php [files]) folder, then it will give error of Cannot redeclare class 
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

class Organisation_all_contact
{
    public $CI;
    function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->model('Org_model');
        $this->CI->load->model('Basic_model');
    }

    private $id;
    private $siteId;
    private $firstname;
    private $lastname;
    private $position;
    private $department;
    private $type;
    private $archive;

    function setId($id) { $this->id = $id; }
    function getId() { return $this->id; }
    function setsiteId($siteId) { $this->siteId = $siteId; }
    function getsiteId() { return $this->siteId; }
    function setName($firstname) { $this->firstname = $firstname; }
    function getName() { return $this->firstname; }
    function setLastname($lastname) { $this->lastname = $lastname; }
    function getLastname() { return $this->lastname; }
    function setPosition($position) { $this->position = $position; }
    function getPosition() { return $this->position; }
    function setDepartment($department) { $this->department = $department; }
    function getDepartment() { return $this->department; }
    function setType($type) { $this->type = $type; }
    function getType() { return $this->type; }
    function setArchive($archive) { $this->archive = $archive; }
    function getArchive() { return $this->archive; }

    function add_site_contact()
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

    function update_site_contact()
    {
        $data = array('siteId'=>$this->getsiteId(),
            'firstname'=>$this->getName(),
            'lastname'=>$this->getLastname(),
            'position'=>$this->getPosition(),
            'department'=>$this->getDepartment(),
            'type'=>$this->getType()
        );
        $c = $this->CI->Basic_model->update_records('house_and_site_key_contact', $data,array('id'=>$this->getId()));
        return $c;
    }
}
