<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 11/03/2015
 * Time: 0:24
 */

namespace phpRoundRobin;


interface RRBackend {

    public function __construct($connectionParameters);
    public function persistDatasource(RRDataSource $dataSource);
    public function persistArchive(RRArchive $archive);
    public function persistSample(RRSample $sample);

    public function loadArchive(RRDataSource $dataSource, $name, $lazy = false);
    public function loadArchives(RRDataSource $datasource, $lazy = false);

    public function loadSample(RRArchive $archive, $index);
    public function loadSamples(RRArchive $archive);

}