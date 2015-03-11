<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 11/03/2015
 * Time: 0:24
 */

namespace myrrd;


interface MyRRPersistor {

    public function __construct($connectionParameters);
    public function persistDatasource(MyRRDataSource $dataSource);
    public function persistArchive(MyRRArchive $archive);
    public function persistSample(MyRRSample $sample);

}