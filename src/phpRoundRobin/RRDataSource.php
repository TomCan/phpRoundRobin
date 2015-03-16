<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10/03/2015
 * Time: 22:02
 */

namespace phpRoundRobin;


class RRDataSource {

    private $id = 0;
    private $name = "";
    private $dataType = 0;
    private $archives = array();

    private $changedArchives = array();

    private $persistor;

    function __construct($persistor)
    {
        $this->persistor = $persistor;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param mixed $dataType
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
    }

    /**
     * @return mixed
     */
    public function getArchives()
    {
        return $this->archives;
    }

    /**
     * @return RRArchive
     */
    public function getArchive($name)
    {
        return $this->archives[$name];
    }

    public function addArchive($name, $numberOfSamples, $interval, $aggregationFunction) {

        $this->archives[$name] = RRArchive::Create($this, $name, $numberOfSamples, $interval, $aggregationFunction);
        $this->changedArchives[$name] = $this->archives[$name];

    }

    public function registerValue($value, $timeStamp = 0) {
        foreach ($this->archives as $archive) {
            $archive->registerValue($value, $timeStamp);
        }
    }

    public function markChanged($archive) {
        // mark archive as changed, for persisting purposes
        $this->changedArchives[$archive->getName()] = $archive;
    }

    public function Save() {

        if ($this->id == 0) {
            // new datasource
            $this->persistor->persistDatasource($this);
        }

        foreach ($this->changedArchives as $archive) {
            $archive->Save();
        }


    }

}