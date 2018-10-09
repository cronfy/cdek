<?php
/**
 * Created by PhpStorm.
 * User: cronfy
 * Date: 09.10.18
 * Time: 13:57
 */

namespace cronfy\cdek\common\models;


class CdekCityDTO
{
    public $ID;
    public $PostCodeList;
    public $CountryCode;
    public $CityName;
    public $OblName;


    public function getPostCodes() {
        return explode(',', $this->PostCodeList);
    }
}