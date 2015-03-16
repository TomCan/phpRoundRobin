<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 11/03/2015
 * Time: 0:21
 */

namespace phpRoundRobin;


class RRPDOBackend implements RRBackend {

    private $dbh = null;
    private $connectionParameters;

    private function connect() {
        if ($this->dbh === null) {
            $this->dbh = new \PDO($this->connectionParameters["dsn"], $this->connectionParameters["user"], $this->connectionParameters["pass"]);
        }
    }

    public function __construct($connectionParameters)
    {
        $this->connectionParameters = $connectionParameters;
        $this->connect();
    }

    public function persistDatasource(RRDataSource $dataSource)
    {

        if ($dataSource->getId() == 0) {

            // need to insert
            if ($this->checkAndCreateDatasourceTable()) {
                $this->dbh->exec("INSERT INTO `datasources` (`name`, `datatype`) VALUES(".$this->dbh->quote($dataSource->getName()).", ".$this->dbh->quote($dataSource->getDataType()).")");
                $dataSource->setId($this->dbh->lastInsertId());
                return true;
            } else {
                return false;
            }

        } else {

            // only name can be updated
            if ($this->dbh->exec("UPDATE `datasources` SET `name` = ".$this->dbh->quote($dataSource->getName())." WHERE `id` = " . $this->dbh->quote($dataSource->getId())) === false) {
                return false;
            } else {
                return true;
            }

        }

    }

    public function persistArchive(RRArchive $archive)
    {

        if ($archive->getId() == 0) {
            // need to insert
            if ($this->checkAndCreateArchiveTable()) {
                $this->dbh->exec("INSERT INTO `archives` (`datasource`, `name`, `numberofsamples`, `interval`, `aggregationfunction`, `lastindex`, `lasttimestamp`) VALUES(".$this->dbh->quote($archive->getDatasource()->getId()).", ".$this->dbh->quote($archive->getName()).", ".$this->dbh->quote($archive->getNumberOfSamples()).", ".$this->dbh->quote($archive->getInterval()).", ".$this->dbh->quote($archive->getAggregationFunction()).", ".$this->dbh->quote($archive->getLastIndex()).", ".$this->dbh->quote($archive->getLastTimestamp()).")");
                $archive->setId($this->dbh->lastInsertId());
                return true;
            } else {
                return false;
            }

        } else {

            // need to update, only name, agg function, last index and last timestamp
            if ($this->dbh->exec("UPDATE `archives` SET `name` = ".$this->dbh->quote($archive->getName()).", `aggregationfunction` = ".$this->dbh->quote($archive->getAggregationFunction()).", `lastindex` = ".$this->dbh->quote($archive->getLastIndex()).", `lasttimestamp` = ".$this->dbh->quote($archive->getLastTimestamp())." WHERE `id` = " . $this->dbh->quote($archive->getId())) === false) {
                return false;
            } else {
                return true;
            }

        }

    }



    public function persistSample(RRSample $sample)
    {

        if ($sample->getId() == 0) {

            // need to insert
            if ($this->checkAndCreateSamplesTable()) {
                $this->dbh->exec("INSERT INTO `samples` (`archive`, `index`, `numberofsamples`, `value`) VALUES(".$this->dbh->quote($sample->getArchive()->getId()).", ".$this->dbh->quote($sample->getIndex()).", ".$this->dbh->quote($sample->getNumberOfSamples()).", ".$this->dbh->quote($sample->getValue()).")");
                $sample->setId($this->dbh->lastInsertId());
                return true;
            } else {
                return false;
            }

        } else {

            // only value and numberofsamples be updated
            if ($this->dbh->exec("UPDATE `samples` SET `numberofsamples` = ".$this->dbh->quote($sample->getNumberOfSamples()).", `value` = ".$this->dbh->quote($sample->getValue())." WHERE `id` = " . $this->dbh->quote($sample->getId())) === false) {
                return false;
            } else {
                return true;
            }

        }

    }

    private function checkAndCreateTable($table, $schema) {

        // check if table exists
        $tableExists = false;

        if ($result = $this->dbh->query("SHOW TABLES LIKE '".addslashes($table)."'")) {
            if ($result->rowCount() == 1) {
                $tableExists = true;
            }
        }

        if ($tableExists == false) {
            // create table
            $create_parts = array();
            $pk = array();
            foreach ($schema as $fld => $def) {
                $create_parts[] = '`'.$fld.'` ' . $def[0] . (($def[1] == 'NO') ? ' NOT NULL' : '') . (($def[3] !== null) ? " DEFAULT '".addslashes($def[3])."'" : '') . (($def[4] != '') ? " " . $def[4] : '');
                if ($def[2] == 'PRI') $pk[] = $fld;
            }
            if (count($pk) > 0) $create_parts[] = "PRIMARY KEY (`".implode("`, `", $pk) ."`)";
            $create_sql = "CREATE TABLE `".addslashes($table)."` (".implode(", ", $create_parts) .")";
            if ($this->dbh->exec($create_sql) === false) {
                return false;
            }else {
                return true;
            }
        } else {
            // TODO: check schema for correctness
            return true;
        }

    }

    private function checkAndCreateDatasourceTable() {

        $schema = array(
            'id' => array('int(10)', 'NO', 'PRI', null, 'auto_increment'),
            'name' => array('varchar(50)', 'NO', '', '', ''),
            'datatype' => array('tinyint', 'NO', '', 0, ''),
        );

        return $this->checkAndCreateTable('datasources', $schema);

    }

    private function checkAndCreateArchiveTable() {

        $schema = array(
            'id' => array('int(10)', 'NO', 'PRI', null, 'auto_increment'),
            'datasource' => array('int(10)', 'NO', '', null, ''),
            'name' => array('varchar(255)', 'NO', '', '', ''),
            'numberofsamples' => array('int(10)', 'NO', '', null, ''),
            'interval' => array('int(10)', 'NO', '', null, ''),
            'aggregationfunction' => array('tinyint', 'NO', '', null, ''),
            'lastindex' => array('int(10)', 'NO', '', 0, ''),
            'lasttimestamp' => array('int(10)', 'NO', '', null, ''),
        );

        return $this->checkAndCreateTable('archives', $schema);

    }

    private function checkAndCreateSamplesTable() {

        $schema = array(
            'id' => array('int(10)', 'NO', 'PRI', null, 'auto_increment'),
            'archive' => array('int(10)', 'NO', '', null, ''),
            'index' => array('int(10)', 'NO', '', null, ''),
            'numberofsamples' => array('int(10)', 'NO', '', null, ''),
            'value' => array('float', 'NO', '', null, ''), // TODO: change float to appropriate type based on type of datasource (but keep in mind avg)
        );

        return $this->checkAndCreateTable('samples', $schema);

    }


}