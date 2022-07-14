<?php

/*
 * Filename: MemberAddress.php
 * Desc: This file is used to hold information about the resedential details of members like street, city etc.
 * @author YDT <yourdevelopmentteam.com.au>
*/

if (!defined('BASEPATH')) exit('No direct script access allowed');

use location\Address;

require_once APPPATH . 'Classes/location/Address.php';

/*
 * class: MemberAddress
 * Desc:  The accessbility mode is private for variables, and accessibility mode of members are public.
 * The class holds variables like $memberaddressid, $street, $city and setter and getter methods (getcity, setcity) are used to describe about address of member.
 * Created: 01-08-2018
 */
class MemberAddress extends location\Address
  {
    /**
	 * @var memberid
     * @access private
     * @vartype: int
     */
    private $memberid;
    private $memberid_dirty = FALSE;

    /**
	 * @function getMemberid
	 * @access public
	 * @return $memberid integer
	 * Get Member Id
	 */
    public function getMemberid() {
        return $this->memberid;
    }

    /**
	 * @function setMemberid
     * @param $memberid integer 
     * @access public
	 * Set Member Id
     */
    public function setMemberid($memberid) {
        $this->memberid_dirty = TRUE;
        $this->memberid = $memberid;
    }

    public function flat_array() {
        $flat_array = parent::flat_array();
        
        if ($this->memberid_dirty) {
            $flat_array['memberId'] = $this->getMemberid();
        }
        
        return $flat_array;
    }
}
