<?php

/**
 * Class CBRCurrency
 *
 * Пример использования
 * CBRCurrency::getInstance('-3 day')->get('USD');
 * CBRCurrency::getInstance()->get('EUR')
 */
class CBRCurrency
{
    protected $arList = array();
    protected $sDate  = '';
    protected static $_instance;

    private function __clone() {}

    public static function getInstance($sDate = null)
    {
        if (
            self::$_instance === null
            || (self::$_instance instanceof CBRCurrency && self::$_instance->sDate !== $sDate)
        ){
            self::$_instance = new CBRCurrency($sDate);
        }

        return self::$_instance;
    }

    private function __construct($sDate)
    {
        $this->sDate = $sDate;
        $obXML = new DOMDocument();

        if ($obXML->load($this->getUrl())) {
            $this->arList = array();

            $root = $obXML->documentElement;
            $items = $root->getElementsByTagName('Valute');

            foreach ($items as $item) {
                $sCode = $item->getElementsByTagName('CharCode')->item(0)->nodeValue;
                $iCurs = $item->getElementsByTagName('Value')->item(0)->nodeValue;
                $this->arList[$sCode] = floatval(str_replace(',', '.', $iCurs));
            }

        } else {
            throw new Exception('Невозможно загрузить XML');
        }
    }

    public function getUrl()
    {
        if (empty($this->sDate)) {
            $iTime = time();
        } else {
            $iTime = strtotime($this->sDate);
        }

        $sDate  = date('d/m/Y', $iTime);

        $sUrl = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=' . $sDate;

        return $sUrl;
    }

    public function get($sCurrencyName)
    {
        if (isset($this->arList[$sCurrencyName])) {
            return  round($this->arList[$sCurrencyName], 2);
        } else {
            throw new Exception('Не найдено значение валюты ' . $sCurrencyName);
        }

    }
}
