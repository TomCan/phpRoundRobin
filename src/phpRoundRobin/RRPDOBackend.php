<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 11/03/2015
 * Time: 0:21
 */

namespace phpRoundRobin;


class RRPDOBackend implements RRBackend {

    private $dbh;
    private $connectionParameters;

    private function connect() {
        if ($this->dbh === null) {
            $this->dbh = new \PDO($this->connectionParameters["dsn"], $this->connectionParameters["user"], $this->connectionParameters["pass"]);
        }
    }

    public function __construct($connectionParameters)
    {
        $this->connectionParameters = $connectionParameters;
    }

    public function persistDatasource(RRDataSource $dataSource)
    {

        if ($dataSource->getId() == 0) {

            // need to insert
            $this->dbh->exec("INSERT INTO `datasources` (`name`, `datatype`) VALUES('".$this->dbh->quote($dataSource->getName())."', '".$this->dbh->quote($dataSource->getDataType())."')");
            $dataSource->setId($this->dbh->lastInsertId());

        } else {

            // only name can be updated
            $this->dbh->exec("UPDATE `datasources` SET `name` = '".$this->dbh->quote($dataSource->getName())."' WHERE `id` = '" . $this->dbh->quote($dataSource->getId())."'");

        }

    }



    public function persistArchive(RRArchive $archive)
    {

        if ($archive->getId() == 0) {
            // need to insert
            $this->dbh->exec("INSERT INTO `archives` (`datasource`, `name`, `numberofsamples`, `interval`, `aggregationfunction`, `lastindex`, `lasttimestamp`) VALUES('".$this->dbh->quote($archive->getDatasource()->getId())."', '".$this->dbh->quote($archive->getName())."', '".$this->dbh->quote($archive->getNumberOfSamples())."', '".$this->dbh->quote($archive->getInterval())."', '".$this->dbh->quote($archive->getAggregationFunction())."', '".$this->dbh->quote($archive->getLastIndex())."', '".$this->dbh->quote($archive->getLastTimestamp())."')");
            $archive->setId($this->dbh->lastInsertId());

        } else {

            // need to update, only name, agg function, last index and last timestamp
            $this->dbh->exec("UPDATE `archives` SET `name` = '".$this->dbh->quote($archive->getName())."', `aggregationfunction` = '".$this->dbh->quote($archive->getAggregationFunction())."', `lastindex` = '".$this->dbh->quote($archive->getLastIndex())."', `lasttimestamp` = '".$this->dbh->quote($archive->getLastTimestamp())."' WHERE `id` = '" . $this->dbh->quote($archive->getId())."'");

        }

    }



    public function persistSample(RRSample $sample)
    {

        if ($sample->getId() == 0) {

            // need to insert
            $this->dbh->exec("INSERT INTO `samples` (`archive`, `index`, `numberofsamples`, `value`) VALUES('".$this->dbh->quote($sample->getArchive()->getId())."', '".$this->dbh->quote($sample->getIndex())."', '".$this->dbh->quote($sample->getNumberOfSamples())."', '".$this->dbh->quote($sample->getValue())."')");
            $sample->setId($this->dbh->lastInsertId());

        } else {

            // only value and numberofsamples be updated
            $this->dbh->exec("UPDATE `samples` SET `numberofsamples` = '".$this->dbh->quote($sample->getNumberOfSamples())."', `value` = '".$this->dbh->quote($sample->getValue())."' WHERE `id` = '" . $this->dbh->quote($sample->getId())."'");

        }

    }

}