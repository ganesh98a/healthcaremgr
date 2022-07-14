<?php

namespace persisted;

trait Archiveable
{
	private $archive;

    public function setArchive($isArchive)
    {
        $this->archive = $isArchive;
    }

    public function getArchive()
    {
        return $this->archive;
    }
}