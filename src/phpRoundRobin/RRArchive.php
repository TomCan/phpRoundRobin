<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 10/03/2015
 * Time: 22:08
 */

namespace phpRoundRobin;


class RRArchive {

    private $datasource;
    private $id;
    private $name;
    private $numberOfSamples;
    private $interval;
    private $lastIndex;
    private $lastTimestamp;
    private $aggregationFunction;
    private $samples;

    private $changedSamples;

    /**
     * @return mixed
     */
    public function getDatasource()
    {
        return $this->datasource;
    }

    /**
     * @param mixed $datasource
     */
    public function setDatasource($datasource)
    {
        $this->datasource = $datasource;
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
    public function getNumberOfSamples()
    {
        return $this->numberOfSamples;
    }

    /**
     * @param mixed $numberOfSamples
     */
    public function setNumberOfSamples($numberOfSamples)
    {
        $this->numberOfSamples = $numberOfSamples;
    }

    /**
     * @return mixed
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param mixed $interval
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * @return mixed
     */
    public function getLastIndex()
    {
        return $this->lastIndex;
    }

    /**
     * @param mixed $lastIndex
     */
    public function setLastIndex($lastIndex)
    {
        $this->lastIndex = $lastIndex;
    }

    /**
     * @return mixed
     */
    public function getLastTimestamp()
    {
        return $this->lastTimestamp;
    }

    /**
     * @param mixed $lastTimestamp
     */
    public function setLastTimestamp($lastTimestamp)
    {
        $this->lastTimestamp = $lastTimestamp;
    }

    /**
     * @return mixed
     */
    public function getAggregationFunction()
    {
        return $this->aggregationFunction;
    }

    /**
     * @param mixed $aggregationFunction
     */
    public function setAggregationFunction($aggregationFunction)
    {
        $this->aggregationFunction = $aggregationFunction;
    }

    /**
     * @return mixed
     */
    public function getSamples()
    {
        return $this->samples;
    }

    public static function Create($datasource, $name, $numberOfSamples, $interval, $aggregationFunction) {

        $archive = new RRArchive();
        $archive->setDatasource($datasource);
        $archive->setName($name);
        $archive->setNumberOfSamples($numberOfSamples);
        $archive->setInterval($interval);
        $archive->setAggregationFunction($aggregationFunction);
        $archive->setLastIndex(0);
        $archive->setLastTimestamp(time());

        // create all samples
        $samples = array();
        for ($i=0;$i < $archive->getNumberOfSamples();$i++) {
            $samples[$i] = new RRSample($archive, $i);
        }
        $archive->samples = $samples;
        $archive->changedSamples = $samples;

        return $archive;

    }

    public function registerValue($value, $timeStamp) {

        // see if timestamp is specified. If not, use current time
        if ($timeStamp == 0) $timeStamp = time();

        // determine sample index based on lastTimestamp, timeStamp and number of samples
        $diffIndex = ($timeStamp / $this->interval) - ($this->lastTimestamp / $this->interval);

        if ($diffIndex == 0) {
            // same sample
            $this->addToSample($this->lastIndex, $value);
        } elseif ($diffIndex == 1) {
            // next sample
            $this->nextSample();
            $this->overwriteSample($this->lastIndex, $value);
        } elseif ($diffIndex > 1) {
            // samples have been skipped
            for ($i=1;$i<$diffIndex;$i++) {
                $this->nextSample();
                $this->overwriteSample($this->lastIndex, null, 0);
            }
            // register this sample
            $this->nextSample();
            $this->overwriteSample($this->lastIndex, $value);
        } else {
            // timestamp in past, disgard for now. Maybe do something useful here
            return;
        }

        $this->lastTimestamp = $timeStamp;

    }

    private function nextSample() {
        if ($this->lastIndex == $this->numberOfSamples - 1) {
            $this->lastIndex = 0;
        } else {
            $this->lastIndex++;
        }
    }

    private function addToSample($index, $value) {

        switch ($this->aggregationFunction) {

            case ARCHIVE_AGGREGATION_FUNCTION_AVG:
                // explode average, add value and recalculate average
                $this->samples[$index]->setValue(($this->samples[$index]->getValue() * $this->samples[$index]->getNumberOfSamples() + $value) / ($this->samples[$index]->getNumberOfSamples() + 1));
                break;

            case ARCHIVE_AGGREGATION_FUNCTION_MIN:
                // if smaller, register
                if ($value < $this->samples[$index]->getValue()) {
                    $this->samples[$index]->setValue($value);
                }
                break;

            case ARCHIVE_AGGREGATION_FUNCTION_MAX:
                // if larger, register
                if ($value > $this->samples[$index]->getValue()) {
                    $this->samples[$index]->setValue($value);
                }
                break;

            case ARCHIVE_AGGREGATION_FUNCTION_FIRST:
                // if no samples, register
                if ($this->samples[$index]->getNumberOfSamples() == 0) {
                    $this->samples[$index]->setValue($value);
                }
                break;

            case ARCHIVE_AGGREGATION_FUNCTION_LAST:
                // always register
                $this->samples[$index]->setValue($value);
                break;

        }

        $this->samples[$index]->setNumberOfSamples($this->samples[$index]->getNumberOfSamples() + 1);

    }

    private function overwriteSample($index, $value, $numberOfSamples = 1) {

        // overwrite sample and set number of values to 1
        $this->samples[$index]->setValue($value);
        $this->samples[$index]->setNumberOfSamples($numberOfSamples);

    }

    public function markChanged($sample) {
        // keep track of changed samples, for persisting purposes
        $this->changedSamples[$sample->getId()] = $sample;
        // let datasource know we changed
        $this->datasource->markChanged($this);
    }

    public function Save() {

        if ($this->id == 0) {
            // need to save this
            $this->datasource->getBackend()->persistArchive($this);
        }

        foreach ($this->changedSamples as $sample) {
            $sample->Save();
        }

    }

}