<?php


namespace api;


use PDO;
use PDOException;
use SimpleXMLElement;

class ApiModel
{
    const DB = 'mysql';
    const DB_HOST = 'localhost';
    const DB_NAME = 'e96914b7_parser';
    const CHARSET = 'utf8';
    const DB_USER = 'e96914b7_parser';
    const DB_PASS = '246508';

    /** @var PDO */
    private $oPdo = ' ';
    /** @var array */
    private $colNames = ['id', 'name', 'rate'];
    /** @var string */
    private $url = 'http://www.cbr.ru/scripts/XML_daily.asp';

    public function __construct()
    {
        if ($this->oPdo) {
            $dsn = sprintf('%s:host=%s;dbname=%s;charset=%s', self::DB, self::DB_HOST, self::DB_NAME, self::CHARSET);

            try {
                $oPDO = new PDO($dsn, self::DB_USER, self::DB_PASS);
                $this->oPdo = $oPDO;
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }
    }

    public function getPDO()
    {
        return $this->oPdo;
    }

    public function update()
    {
        $oXML = $this->parserXml();

        $arr = [];
        $updateCols = array();
        $dataToInsert = array();

        for ($i = 0; $i < count($oXML->Valute); $i++) {
            $arr[$i][$this->colNames[0]] = (string)$oXML->Valute[$i]->attributes();
            $arr[$i][$this->colNames[1]] = (string)$oXML->Valute[$i]->Name;
            $arr[$i][$this->colNames[2]] = str_replace(',', '.', (string)$oXML->Valute[$i]->Value->__toString());
        }

        foreach ($arr as $row => $data) {
            foreach ($data as $val) {
                $dataToInsert[] = $val;
            }
        }

        foreach ($this->colNames as $curCol) {
            $updateCols[] = $curCol . " = VALUES($curCol)";
        }

        $onDup = implode(', ', $updateCols);

        $rowPlaces = '(' . implode(', ', array_fill(0, count($this->colNames), '?')) . ')';

        $allPlaces = implode(', ', array_fill(0, count($arr), $rowPlaces));


        $sql = "INSERT INTO `currency` (" . implode(', ', $this->colNames) .
            ") VALUES " . $allPlaces . " ON DUPLICATE KEY UPDATE $onDup";


        $stmt = $this->oPdo->prepare($sql);

        return $stmt->execute($dataToInsert);

    }

    public function dataAll($limit, $offset)
    {
        $sth = $this->oPdo->prepare("SELECT * FROM `currency` LIMIT :limit OFFSET :offset ");
        $sth->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $sth->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $sth->execute();
        $array = $sth->fetchAll(PDO::FETCH_ASSOC);

        return json_encode($array);
    }

    public function dateOne($id)
    {
        $sth = $this->oPdo->prepare("SELECT * FROM `currency` WHERE `id` = ?");
        $sth->execute([$id]);
        $array = $sth->fetch(PDO::FETCH_ASSOC);
        return json_encode($array);
    }

    public function parserXml()
    {
        $ageInSeconds = 3600;
        $cacheName =  $_SERVER['DOCUMENT_ROOT'] . 'parserCB.xml.cache';

        if (!file_exists($cacheName) || filemtime($cacheName) > time() + $ageInSeconds) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $retValue = curl_exec($ch);
            curl_close($ch);
            file_put_contents($cacheName, $retValue);
        }

        $oXML = new SimpleXMLElement($cacheName, null, true);

        return $oXML;
    }
}