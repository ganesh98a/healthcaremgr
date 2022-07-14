<?php

/*
 * Filename: ParticipantPlan.php
 * Desc: There are plans for services, total fund details, fund used, plan start date and end date
 * @author YDT <yourdevelopmentteam.com.au>
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Class: ParticipantPlan
 * Desc: Variables and methods for Participant Plan
 * Created: 02-08-2018
*/

class ParticipantPlan
 {
    /**
     * @var participantplanid
     * @access private
     * @vartype: integer
     */
    private $participantplanid;

    /**
     * @var participantid
     * @access private
     * @vartype: integer
     */
    private $participantid;

    /**
     * @var plan_type
     * @access private
     * @vartype: tinyint
     */
    private $plan_type;

    /**
     * @var plan_id
     * @access private
     * @vartype: varchar
     */
    private $plan_id;

    /**
     * @var start_date
     * @access private
     * @vartype: varchar
     */
    private $start_date;

    /**
     * @var end_date
     * @access private
     * @vartype: varchar
     */
    private $end_date;

    /**
     * @var total_funding
     * @access private
     * @vartype: double
     */
    private $total_funding;
	
    /**
     * @var fund_used
     * @access private
     * @vartype: double
     */
    private $fund_used;

    /**
     * @var remaining_fund
     * @access private
     * @vartype: double
     */
    private $remaining_fund;

	
    /**
     * @function getParticipantplanid
	 * @access public
	 * @returns $participantplanid integer
	 * Get Participant Plan Id 
	 */
    public function getParticipantplanid() {
        return $this->participantplanid;
    }

    /**
	 * @function setParticipantplanid
	 * @access public
	 * @param $participantplanid integer 
	 * Set Participant Plan Id 
	 */
    public function setParticipantplanid($participantplanid) {
        $this->participantplanid = $participantplanid;
    }

    /**
     * @function getParticipantid
	 * @access public
	 * @returns $participantid integer
	 * Get Participant Id 
	 */
    public function getParticipantid() {
        return $this->participantid;
    }

    /**
	 * @function setParticipantplanid
	 * @access public
	 * @param $participantplanid integer 
	 * Set Participant Id 
	 */
    public function setParticipantid($participantid) {
        $this->participantid = $participantid;
    }

    /**
     * @function getPlanType
	 * @access public
	 * @returns $participantid tinyint
	 * Get Plan Type
	 */
    public function getPlanType() {
        return $this->plan_type;
    }

    /**
	 * @function setPlanType
	 * @access public
	 * @param $planType integer 
	 * Set Plan Type
	 */
    public function setPlanType($planType) {
        $this->plan_type = $planType;
    }

    /**
     * @function getPlanId
	 * @access public
	 * @returns $getPlanId varchar
	 * Get Plan Id
	 */
    public function getPlanId() {
        return $this->plan_id;
    }

    /**
	 * @function setPlanId
	 * @access public
	 * @param $planid varchar 
	 * Set Plan Id
	 */
    public function setPlanId($planId) {
        $this->plan_id = $planId;
    }

    /**
     * @function getStartDate
	 * @access public
	 * @returns $start_date varchar
	 * Get Start Date
	 */
    public function getStartDate() {
        return $this->start_date;
    }

    /**
	 * @function setStartDate
	 * @access public
	 * @param $startDate varchar 
	 * Set Start Date
	 */
    public function setStartDate($startDate) {
        $this->start_date = $startDate;
    }

    /**
     * @function getEndDate
	 * @access public
	 * @returns $end_date varchar
	 * Get End Date
	 */
    public function getEndDate() {
        return $this->end_date;
    }

    /**
	 * @function setEndDate
	 * @access public
	 * @param $endDate varchar 
	 * Set End Date
	 */
    public function setEndDate($endDate) {
        $this->end_date = $endDate;
    }

    /**
     * @function total_funding
	 * @access public
	 * @returns $total_funding double
	 * Get Total Funding
	 */
    public function getTotalFunding() {
        return $this->total_funding;
    }

	/**
	 * @function setTotalFunding
	 * @access public
	 * @param $endDate double 
	 * Set Total Funding
	 */
    public function setTotalFunding($totalFunding) {
        $this->total_funding = $totalFunding;
    }

    /**
     * @function getFundUsed
	 * @access public
	 * @returns $fund_used double
	 * Get Fund Used
	 */
    public function getFundUsed() {
        return $this->fund_used;
    }

    /**
	 * @function setFundUsed
	 * @access public
	 * @param $fund_used double 
	 * Set Fund Used
	 */
    public function setFundUsed($fundUsed) {
        $this->fund_used = $fundUsed;
    }
	
    /**
     * @function getRemainingFund
	 * @access public
	 * @returns $remaining_fund double
	 * Get Remaining Fund
	 */
    public function getRemainingFund() {
        return $this->remaining_fund;
    }

    /**
	 * @function setRemainingFund
	 * @access public
	 * @param $remainingFund double 
	 * Set Remaining Fund
	 */
    public function setRemainingFund($remainingFund) {
        $this->remaing_fund = $remainingFund;
    }
}
