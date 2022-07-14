<?php
namespace OrganisationClass; // if namespacce is not made in Classes(Member.php,MemberAddress.php [files]) folder, then it will give error of Cannot redeclare class 
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

class Organisation
{
    public $CI;
    function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->model('Org_model');
        $this->CI->load->model('Org_dashboard_model');
        $this->CI->load->model('Basic_model');
    }

    private $id;
    private $companyId;
    private $name;
    private $abn;
    private $logo_file;
    private $parent_org;
    private $website;
    private $payroll_tab;
    private $gst;
    private $status;
    private $text_search;
    private $limit;
    private $page;

    function setId($id) { $this->id = $id; }
    function getId() { return $this->id; }
    function setCompanyId($companyId) { $this->companyId = $companyId; }
    function getCompanyId() { return $this->companyId; }
    function setName($name) { $this->name = $name; }
    function getName() { return $this->name; }
    function setAbn($abn) { $this->abn = $abn; }
    function getAbn() { return $this->abn; }
    function setLogo_file($logo_file) { $this->logo_file = $logo_file; }
    function getLogo_file() { return $this->logo_file; }
    function setParent_org($parent_org) { $this->parent_org = $parent_org; }
    function getParent_org() { return $this->parent_org; }
    function setWebsite($website) { $this->website = $website; }
    function getWebsite() { return $this->website; }
    function setPayroll_tab($payroll_tab) { $this->payroll_tab = $payroll_tab; }
    function getPayroll_tab() { return $this->payroll_tab; }
    function setGst($gst) { $this->gst = $gst; }
    function getGst() { return $this->gst; }
    function setStatus($status) { $this->status = $status; }
    function getStatus() { return $this->status; }
    function settext_search($text_search) { $this->text_search = $text_search; }
    function gettext_search() { return $this->text_search; }
    function setLimit($limit) { $this->limit = $limit; }
    function getLimit() { return $this->limit; }
    function setPage($page) { $this->page = $page; }
    function getPage() { return $this->page; }

    function setNewest($new) { $this->new = $new; }
    function getNewest() { return $this->new; }


    public function get_organisation_profile()
    {
        return $this->CI->Org_model->get_organisation_profile($this);
    }

    public function get_organisation_about()
    {
        return $this->CI->Org_model->get_organisation_about($this);
    }
    
    public function get_sub_org()
    {
        return $this->CI->Org_dashboard_model->get_sub_org($this);
    }
    
}
