<?php

namespace TaskClass;

/*
 * Filename: Participant.php
 * Desc: Details of Participants
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * Class: Participant
 * Desc: Participants details like participant first name, last name, email, phone etc.
 * Variables and Getter and Setter Methods for participant class
 * Created: 02-08-2018
 */

class Task {
  public function __construct() {
      $CI = & get_instance();
  }
  /**
   * @var taskid
   * @access private
   * @vartype: int
   */
  private $taskid;

  /**
   * @var taskname
   * @access private
   * @vartype: varchar
   */
  private $taskname;

  /**
   * @var priority
   * @access private
   * @vartype: varchar
   */
  private $priority;

  /**
   * @var due_date
   * @access private
   * @vartype: varchar
   */
  private $due_date;

  /**
   * @var user
   * @access private
   * @vartype: varchar
   */
  private $user;

  /**
   * @var task_note
   * @access private
   * @vartype: varchar
   */
  private $task_note;

  /**
   * @function getTaskid
   * @access public
   * @returns $taskid int
   * Get task Id
   */
  public function getTaskid() {
      return $this->taskid;
  }

  /**
   * @function setTaskid
   * @access public
   * @param $taskid integer
   * Set task Id
   */
  public function setTaskid($taskid) {
      $this->taskid = $taskid;
  }

  /**
   * @function getParticipant
   * @access public
   * @returns $user int
   * Get task Id
   */
  public function getParticipant() {
      return $this->participant;
  }

  public function setParticipant($participant) {
      $this->participant = $participant;
  }
  public function getMember() {
      return $this->member;
  }

  public function setMember($member) {
      $this->member = $member;
  }
  
  public function getTaskName() {
      return $this->taskname;
  }

  public function setTaskName($taskname) {
      $this->taskname = $taskname;
  }
  public function getPriority() {
      return $this->priority;
  }

  public function setPriority($priority) {
      $this->priority = $priority;
  }
  public function getTasknote() {
      return $this->tasknote;
  }

  public function setTasknote($tasknote) {
      $this->tasknote = $tasknote;
  }
  public function getDuedate() {
      return $this->duedate;
  }

  public function setDuedate($duedate) {
      $this->duedate = $duedate;
  }

  public function AddTask() {
      // check Here server site validation
      // Insert record method
      $CI = & get_instance();
      $CI->load->model('CrmSchedule_model');
      return $CI->CrmTask_model->create_task($this);
  }
  public function AddsubTask() {
      // check Here server site validation
      // Insert record method
      $CI = & get_instance();
      $CI->load->model('CrmSchedule_model');
      return $CI->CrmTask_model->create_sub_task($this);
  }

}
?>
