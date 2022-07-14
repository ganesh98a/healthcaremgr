<?php

namespace MemberAvailabilityClass;
/*
 * Filename: MemberAvailability.php
 * Desc: This file is used to hold information about the resedential details of members like street, city etc.
 * @author YDT <yourdevelopmentteam.com.au>
*/

if(!defined('BASEPATH')) exit("No direct script access allowed");

/*
 * Class: MemberAvailability
 * Desc: The class is about availability of members. The variables memberavailabilityid, memberid, title etc are used. 
 * Created: 01-08-2018 
*/
class MemberAvailability
{
    public $CI;
    function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->model('Member_model');
        $this->CI->load->model('Basic_model');
    }
    
    private $memberavailabilityid;

    /**
	 * @var memberavailabilityid
     * @access private
     * @vartype: int
     */
    private $memberid;

    /**
	 * @var memberid
     * @access private
     * @vartype: varchar
     */
    private $title;

     /**
	 * @var title
     * @access private
     * @vartype: tinyint
     */
    private $is_default;

    /**
	 * @var is_default
     * @access private
     * @vartype: tinyint
     */
    private $status;

    /**
	 * @var status
     * @access private
     * @vartype: timestamp
     */
    private $start_date;

    /**
	 * @var start_date
     * @access private
     * @vartype: timestamp
     */
    private $end_date;

    /**
	 * @var memberavailabilityid
     * @access private
     * @vartype: timestamp
     */
    private $first_week;
	
    /**
	 * @var first_week
     * @access private
     * @vartype: varchar
     */
    private $second_week;

     /**
	 * @var second_week
     * @access private
     * @vartype: tinyint
     */
    private $flexible_availability;

     /**
	 * @var flexible_availability
     * @access private
     * @vartype: smallint
     */
    private $flexible_km;

    /**
	 * @function getMemberavailabilityid
	 * @access public
	 * @return $memberavailabilityid integer
	 * Get Memberavailabilityid Id
	 */

    private $travel_km;
    public function getMemberavailabilityid() {
        return $this->memberavailabilityid;
    }

    /**
	 * @function setMemberavailabilityid
     * @param $memberavailabilityid integer 
     * @access public
	 * Set Member Availability Id
     */
    public function setMemberavailabilityid($memberavailabilityid) {
        $this->memberavailabilityid = $memberavailabilityid;
    }

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
        $this->memberid = $memberid;
    }

    /**
	 * @function getTitle
	 * @access public
	 * @return $title varchar
	 * Get Title 
	 */
    public function getTitle() {
        return $this->title;
    }

    /**
	 * @function setTitle
     * @param $title varchar 
     * @access public
	 * Set Title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
	 * @function getIsDefault
	 * @access public
	 * @return $is_default tinyint
	 * Get is_default 
	 */
    public function getIsDefault() {
        return $this->is_default;
    }

    /**
	 * @function setIsDefault
     * @param $title tinyint 
     * @access public
	 * Set setIsDefault
     */
    public function setIsDefault($isDefault) {
        $this->is_default = $isDefault;
    }

    /**
	 * @function getStatus
	 * @access public
	 * @return $status integer
	 * Get Status 
	 */
    public function getStatus() {
        return $this->status;
    }

    /**
	 * @function setStatus
     * @param $status tinyint 
     * @access public
	 * Set Status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
	 * @function getStartDate
	 * @access public
	 * @return $start_date timestamp
	 * Get Start_date 
	 */
    public function getStartDate() {
        return $this->start_date;
    }

    /**
	 * @function setStartDate
     * @param $startDate timestamp 
     * @access public
	 * Set startDate
     */
    public function setStartDate($startDate) {
        $this->start_date = $startDate;
    }

    /**
	 * @function getEndDate
	 * @access public
	 * @return $end_date timestamp
	 * Get end_date 
	 */
    public function getEndDate() {
        return $this->end_date;
    }

    /**
	 * @function setEndDate
     * @param $end_date timestamp 
     * @access public
	 * Set EndDate
     */
    public function setEndDate($endDate) {
        $this->end_date = $endDate;
    }

    /**
	 * @function getFirstWeek
	 * @access public
	 * @return $first_week varchar
	 * Get first_week 
	 */
    public function getFirstWeek() {
        return $this->first_week;
    }

    /**
	 * @function setFirstWeek
     * @param $firstWeek varchar 
     * @access public
	 * Set firstWeek
     */
    public function setFirstWeek($firstWeek) {
        $this->first_week = $firstWeek;
    }

    /**
	 * @function getSecondWeek
	 * @access public
	 * @return $second_week varchar
	 * Get second_week 
	 */
    public function getSecondWeek() {
        return $this->second_week;
    }

    /**
	 * @function setSecondWeek
     * @param $secondWeek varchar 
     * @access public
	 * Set secondWeek
     */
    public function setSecondWeek($secondWeek) {
        $this->second_week = $secondWeek;
    }

    /**
	 * @function getFlexibleAvailability
	 * @access public
	 * @return $flexible_availability tinyint
	 * Get flexible_availability 
	 */
    public function getFlexibleAvailability() {
        return $this->flexible_availability;
    }

    /**
	 * @function setFlexibleAvailability
     * @param $flexible_availability tinyint 
     * @access public
	 * Set flexible_availability
     */
    public function setFlexibleAvailability($flexibleAvailability) {
        $this->flexible_availability = $flexibleAvailability;
    }

    /**
	 * @function getFlexibleKm
	 * @access public
	 * @return $flexible_km smallint
	 * Get flexible_km 
	 */
    public function getFlexibleKm() {
        return $this->flexible_km;
    }

    /**
	 * @function setFlexibleKm
     * @param $flexible_km smallint 
     * @access public
	 * Set flexible_km
     */
    public function setFlexibleKm($flexibleKm) {
        $this->flexible_km = $flexibleKm;
    }


    public function getTravelKm() {
        return $this->travel_km;
    }

  
    public function setTravelKm($travelKm) {
        $this->travel_km = $travelKm;
    }

    
}