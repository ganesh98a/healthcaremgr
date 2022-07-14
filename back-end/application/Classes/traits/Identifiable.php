<?php

namespace persisted;

trait Identifiable {

	/**
     * @var int id
     * @access private
     */
    private $id;

    /**
     * @method setId
     * @access public
     * @param int $id
     * Set Id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @method getId
     * @access public
     * @return int $id
     * Get Id
     */
    public function getId()
    {
        return $this->id;
    }
}