<?php
namespace SitesClass; // if namespacce is not made in Classes(Member.php,MemberAddress.php [files]) folder, then it will give error of Cannot redeclare class 
/*
 * Filename: Member.php
 * Desc: The member file defines a module which checks the details and updates of members, upcoming shifts, create new availability, cases(FMS), create cases. 
 * @author YDT <yourdevelopmentteam.com.au>
*/

if (!defined("BASEPATH")) exit("No direct script access allowed");

/*
 * Class: Member
 * Desc: The Member Class is a class which holds infomation about members like memberid, firstname, lastname etc.
 * The class includes variables and some methods. The methods are used to get and store information of members.
 * The visibility mode of this variables are private and the methods are made public.
 * Created: 01-08-2018
*/

class Sites
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
    private $site_name;
    private $street;
    private $city;
    private $postal;
    private $state;
    private $key_contact;
    private $position;
    private $archive;
    private $email;
    private $phone;
    private $text_search;
    private $abn;
    private $limit;
    private $page;

    function setId($id) { $this->id = $id; }
    function getId() { return $this->id; }
    function setOrganisationId($organisationId) { $this->organisationId = $organisationId; }
    function getOrganisationId() { return $this->organisationId; }
    function setSite_name($site_name) { $this->site_name = $site_name; }
    function getSite_name() { return $this->site_name; }
    function setStreet($street) { $this->street = $street; }
    function getStreet() { return $this->street; }
    function setCity($city) { $this->city = $city; }
    function getCity() { return $this->city; }
    function setPostal($postal) { $this->postal = $postal; }
    function getPostal() { return $this->postal; }
    function setState($state) { $this->state = $state; }
    function getState() { return $this->state; }
    function setKey_contact($key_contact) { $this->key_contact = $key_contact; }
    function getKey_contact() { return $this->key_contact; }
    function setPosition($position) { $this->position = $position; }
    function getPosition() { return $this->position; }
    function setArchive($archive) { $this->archive = $archive; }
    function getArchive() { return $this->archive; }
    function setEmail($email) { $this->email = $email; }
    function getEmail() { return $this->email; }
    function setPhone($phone) { $this->phone = $phone; }
    function getPhone() { return $this->phone; }
    function settext_search($text_search) { $this->text_search = $text_search; }
    function gettext_search() { return $this->text_search; }
    function setAbn($abn) { $this->abn = $abn; }
    function getAbn() { return $this->abn; }
    function setLimit($limit) { $this->limit = $limit; }
    function getLimit() { return $this->limit; }
    function setPage($page) { $this->page = $page; }
    function getPage() { return $this->page; }


    public function get_org_sites()
    {
        return $this->CI->Org_model->get_org_sites($this);
    } 

    function add_org_site()
    {
        $data = array('site_name'=>$this->getSite_name(),
            'street'=>$this->getStreet(),
            'city'=>$this->getCity(),
            'postal'=>$this->getPostal(),
            'abn'=>$this->getAbn(),
            'state'=>$this->getState(),
            'organisationId'=>$this->getOrganisationId(),
            // 'key_contact'=>$this->getKey_contact(),
            // 'position'=>$this->getPosition(),
            // 'email'=>$this->getEmail(),
            // 'phone'=>$this->getPhone()
        );
        $site_id = $this->CI->Org_model->create_organisation_site($data);

        $this->CI->Basic_model->insert_records('house_and_site_email', array('siteId'=>$site_id,'email'=>$this->getEmail(),'primary_email'=>'2'));
        $this->CI->Basic_model->insert_records('house_and_site_phone', array('siteId'=>$site_id,'phone'=>$this->getPhone(),'primary_phone'=>'2'));
        return $site_id;
    }

    function update_org_site()
    {
        $org_data = array('site_name'=>$this->getSite_name(),
            'street'=>$this->getStreet(),
            'city'=>$this->getCity(),
            'postal'=>$this->getPostal(),
            'state'=>$this->getState(),
            'abn'=>$this->getAbn(),
            //'key_contact'=>$this->getKey_contact(),
           // 'position'=>$this->getPosition(),
          //  'email'=>$this->getEmail(),
           // 'phone'=>$this->getPhone()
        );
        $x = $this->CI->Org_model->update_organisation_site($org_data, array('id'=>$this->getId());
        return $x;
    } 

    public function get_house_profile()
    {
        return $this->CI->Org_model->get_house_profile($this);
    }  
}
