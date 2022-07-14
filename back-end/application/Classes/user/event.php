<?php

namespace ProcessBuilder;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Event {

    private $id = null;

    private $name = "";

    private $description = "";

    private $event_trigger = "";

    private $criteria = "";

    private $conditions = "";

    private $condition_logic = "";

    private $expression_inputs = "";

    private $object_name = "";

    private $event_action = "";

    private $email_template = null;

    private $sms_template = null;

    private $recipient = "";

    private $status = 1;    

    private $archive = 0;

    public function __construct($vars = [])
    {
        $vars = (array) $vars;
        if (!empty($vars)) {
            foreach($vars as $key => $value) {
                $setter = 'set' . ucfirst($key);
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                }
            }
        }
    }
    
    public function getVars() {
        return get_object_vars($this);
    }
    
    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of description
     */ 
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */ 
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of event_trigger
     */ 
    public function getEvent_trigger()
    {
        return $this->event_trigger;
    }

    /**
     * Set the value of event_trigger
     *
     * @return  self
     */ 
    public function setEvent_trigger($event_trigger)
    {
        $this->event_trigger = $event_trigger;

        return $this;
    }

    /**
     * Get the value of object_name
     */ 
    public function getObject_name()
    {
        return $this->object_name;
    }

    /**
     * Set the value of object_name
     *
     * @return  self
     */ 
    public function setObject_name($object_name)
    {
        $this->object_name = $object_name;

        return $this;
    }

    /**
     * Get the value of event_action
     */ 
    public function getEvent_action()
    {
        return $this->event_action;
    }

    /**
     * Set the value of event_action
     *
     * @return  self
     */ 
    public function setEvent_action($event_action)
    {
        $this->event_action = $event_action;

        return $this;
    }

    /**
     * Get the value of email_template
     */ 
    public function getEmail_template()
    {
        return $this->email_template;
    }

    /**
     * Set the value of email_template
     *
     * @return  self
     */ 
    public function setEmail_template($email_template)
    {
        $this->email_template = $email_template;

        return $this;
    }

    /**
     * Get the value of sms_template
     */ 
    public function getSms_template()
    {
        return $this->sms_template;
    }

    /**
     * Set the value of sms_template
     *
     * @return  self
     */ 
    public function setSms_template($sms_template)
    {
        $this->sms_template = $sms_template;

        return $this;
    }

    /**
     * Get the value of recipient
     */ 
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set the value of recipient
     *
     * @return  self
     */ 
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of archive
     */ 
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * Set the value of archive
     *
     * @return  self
     */ 
    public function setArchive($archive)
    {
        $this->archive = $archive;

        return $this;
    }

    /**
     * Get the value of criteria
     */ 
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Set the value of criteria
     *
     * @return  self
     */ 
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * Get the value of conditions
     */ 
    public function getConditions()
    {
        return json_decode($this->conditions);
    }

    /**
     * Set the value of conditions
     *
     * @return  self
     */ 
    public function setConditions($conditions)
    {
        $this->conditions = json_encode($conditions);

        return $this;
    }

    /**
     * Get the value of condition_logic
     */ 
    public function getCondition_logic()
    {
        return $this->condition_logic;
    }

    /**
     * Set the value of condition_logic
     *
     * @return  self
     */ 
    public function setCondition_logic($condition_logic)
    {
        $this->condition_logic = $condition_logic;

        return $this;
    }

    /**
     * Get the value of expression_inputs
     */ 
    public function getExpression_inputs()
    {
        return json_decode($this->expression_inputs);
    }

    /**
     * Set the value of expression_inputs
     *
     * @return  self
     */ 
    public function setExpression_inputs($expression_inputs)
    {
        $this->expression_inputs = json_encode($expression_inputs);

        return $this;
    }
}