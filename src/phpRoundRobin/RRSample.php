<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10/03/2015
 * Time: 22:11
 */

namespace phpRoundRobin;


class RRSample {

    private $id;
    private $index;
    private $archive;
    private $numberOfSamples;
    private $value;

    function __construct($archive, $index)
    {
        $this->archive = $archive;
        $this->index = $index;
        $this->numberOfSamples = 0;
        $this->value = null;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @return mixed
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * @param mixed $archive
     */
    public function setArchive($archive)
    {
        $this->archive = $archive;
    }

    /**
     * @return int
     */
    public function getNumberOfSamples()
    {
        return $this->numberOfSamples;
    }

    /**
     * @param int $numberOfSamples
     */
    public function setNumberOfSamples($numberOfSamples)
    {
        if ($this->numberOfSamples !== $numberOfSamples) {
            $this->numberOfSamples = $numberOfSamples;
            $this->archive->markChanged($this);
        }
    }

    /**
     * @return null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param null $value
     */
    public function setValue($value)
    {
        if ($this->value !== $value) {
            $this->value = $value;
            $this->archive->markChanged($this);
        }
    }

    public function Save() {
        // save the sample
        $this->archive->getDatasource()->getPersistor()->persistSample($this);

    }

}