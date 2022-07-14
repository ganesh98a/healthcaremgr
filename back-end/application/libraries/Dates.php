<?php

class Dates {

    private $date_format = 'Y-m-d';

    private $time_format = 'H:i:s';

    private $date = '';

    private $format = '';

    private $week_start_day = 'monday';

    private $week_end_day = 'sunday';

    private $thisday = '';

    private $today = [];

    private $yesterday = [];

    private $tomorrow = [];

    private $nextweek = [];

    private $lastweek = [];

    private $thismonth = [];

    private $lastmonth = [];

    private $nextmonth = [];

    private $thisyear = [];

    private $lastyear = [];

    private $nextyear = [];   

    private $sod = '00:00:00';

    private $eod = '23:59:59';

    public function __construct(String $date = 'today', String $format = 'Y-m-d H:i:s') {
        $this->date = $date;
        $this->format = $format;
        $this->init();
    }

    public function get($property) {
        return $this->$property?? null;
    }

    public function init() {
        $this->format = $this->format?? $this->date_format . ' ' . $this->time_format;
        $this->thisday = $this->toDate();
        $this->today = [$this->toDate($this->date_format . ' ' . $this->sod), $this->toDate($this->date_format . ' ' . $this->eod)];
        $this->yesterday = [$this->toDate('yesterday', $this->date_format . $this->sod), $this->toDate('yesterday', $this->date_format . ' ' . $this->eod)];
        $this->tomorrow = [$this->toDate( 'tomorrow', $this->date_format . $this->sod), $this->toDate('tomorrow', $this->date_format . ' ' . $this->eod)];
        $this->thisweek = [$this->toDate("$this->week_start_day this week"), $this->toDate("$this->week_end_day this week", $this->date_format . ' ' . $this->eod)];
        $this->nextweek = [$this->toDate("next $this->week_start_day"), $this->toDate("next $this->week_end_day", $this->date_format . ' ' . $this->eod)];
        $this->lastweek = [$this->toDate("last week $this->week_start_day"), $this->toDate("last week $this->week_end_day", $this->date_format . ' ' . $this->eod)];
        $this->thismonth = [$this->toDate("first day of this month", $this->date_format . $this->sod), $this->toDate("last day of this month", $this->date_format . ' ' . $this->eod)];
        $this->lastmonth = [$this->toDate("first day of last month", $this->date_format . $this->sod), $this->toDate("last day of last month", $this->date_format . ' ' . $this->eod)];
        $this->nextmonth = [$this->toDate("first day of next month"), $this->toDate("last day of next month", $this->date_format . ' ' . $this->eod)];
        $this->thisyear = [$this->toDate("first day of january this year"), $this->toDate("last day of december this year", $this->date_format . ' ' . $this->eod)];
        $this->lastyear = [$this->toDate("first day of january last year"), $this->toDate("last day of december last year", $this->date_format . ' ' . $this->eod)];
        $this->nextyear = [$this->toDate("first day of january next year"), $this->toDate("last day of december next year", $this->date_format . ' ' . $this->eod)];
    }

    public function today() {
        return $this->toDate($this->today[0]);
    }

    public function isDate(String $date) {
        $dt_format = $this->date_format;
        if (strpos($date, ':') !== false) {
            $dt_format = $this->format;
        }
        $d = DateTime::createFromFormat($dt_format, $date);
        return $d && $d->format($dt_format) == $date;
    }

    public function toDate($timestamp = '', $format = '') {
        if (strpos(strtolower($timestamp), 'y-') !== false) {
            return date($timestamp);
        }
        if (!empty($timestamp) && !is_numeric($timestamp)) {
            $timestamp = strtotime($timestamp);
        }
        if (empty($timestamp)) {
            return date($this->format);
        }
        $format = !empty($format)? $format : $this->format;
        return date($format, $timestamp);
    }

    /**
     * Get the value of thisday
     */ 
    public function getThisday()
    {
        return $this->thisday;
    }

    /**
     * Get the value of yesterday
     */ 
    public function getYesterday()
    {
        return $this->yesterday;
    }

    /**
     * Get the value of tomorrow
     */ 
    public function getTomorrow()
    {
        return $this->tomorrow;
    }

    /**
     * Get the value of nextweek
     */ 
    public function getNextweek()
    {
        return $this->nextweek;
    }

    /**
     * Get the value of lastweek
     */ 
    public function getLastweek()
    {
        return $this->lastweek;
    }

    /**
     * Get the value of thismonth
     */ 
    public function getThismonth()
    {
        return $this->thismonth;
    }

    /**
     * Get the value of lastmonth
     */ 
    public function getLastmonth()
    {
        return $this->lastmonth;
    }

    /**
     * Get the value of nextmonth
     */ 
    public function getNextmonth()
    {
        return $this->nextmonth;
    }

    /**
     * Get the value of thisyear
     */ 
    public function getThisyear()
    {
        return $this->thisyear;
    }

    /**
     * Get the value of lastyear
     */ 
    public function getLastyear()
    {
        return $this->lastyear;
    }

    /**
     * Get the value of nextyear
     */ 
    public function getNextyear()
    {
        return $this->nextyear;
    }

    /**
     * Get the value of sod
     */ 
    public function getSod()
    {
        return $this->sod;
    }

    /**
     * Set the value of sod
     *
     * @return  self
     */ 
    public function setSod($sod)
    {
        $this->sod = $sod;

        return $this;
    }

    /**
     * Get the value of eod
     */ 
    public function getEod()
    {
        return $this->eod;
    }

    /**
     * Set the value of eod
     *
     * @return  self
     */ 
    public function setEod($eod)
    {
        $this->eod = $eod;

        return $this;
    }
}