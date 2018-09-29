<?php
/**
 * Created by PhpStorm.
 * User: cronfy
 * Date: 16.11.17
 * Time: 18:16
 */

namespace cronfy\cdek\console\controllers;

use cronfy\cdek\common\models\CdekCity;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;
use yii\console\Controller;

class InitController extends Controller
{
    public $update;

    public function options($actionID)
    {
        return ['update'];
    }

    /**
     * @param string $xlsFile вида @var/import/cdek/City_RUS_20171111.xls . Берется
     * с сайта https://www.cdek.ru/clients/integrator.html , называется Реестры городов по базе СДЭК
     * @return \Generator
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function getCdekCitiesFromXls($xlsFile)
    {
        $inputFileName = Yii::getAlias($xlsFile);

        /** Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = IOFactory::load($inputFileName);

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $data = $worksheet->toArray();
            $header = $data[0];
            unset($data[0]);
            foreach ($data as $row) {
                $city = array_combine($header, $row);

                yield $city;
            }
        }
    }

    public function actionCities($xlsFile)
    {
        echo "Loading cities:";

        foreach ($this->getCdekCitiesFromXls($xlsFile) as $city) {
            $existing = CdekCity::findOne(['city_code' => $city['ID']]);
            if ($existing) {
                if (!$this->update) {
                    echo '.';
                    continue;
                }
                $cdekCity = $existing;
            } else {
                $cdekCity = new CdekCity();
                $cdekCity->name = (string) $city['CityName'];
                $cdekCity->city_code = $city['ID'];
            }

            $cdekCity->data = $city;

            if (!$cdekCity->validate()) {
                echo "\nSkipping city, not valid:\n";
                print_r($cdekCity->attributes);
                print_r($cdekCity->errors);
//                D();
                continue;
            }

            $isNew = $cdekCity->isNewRecord;

            if (!$cdekCity->save()) {
                E($cdekCity->errors);
                D($cdekCity->attributes);
                throw new \Exception('Failed to save cdekCity model');
            }

            if ($isNew) {
                echo '+';
            } else {
                echo "U";
            }
        }
        echo "\n";
    }

}
