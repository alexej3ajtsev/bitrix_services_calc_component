<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class MainCalc extends CBitrixComponent
{
    protected $errors = [];
    protected $categories = [];
    protected $name = NULL;
    protected $phone = NULL;
    protected $servicesList = NULL;
    protected $cost = NULL;

    const MAIL_EVENT_ID = 33;
    const MAIN_CALC_TEMPLATE = "MAIN_CALC_EVENT";

    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        Loc::loadMessages(__FILE__);
        Loader::includeModule('iblock');
    }

    protected function addError($err)
    {
        $this->errors[] = $err;
    }

    protected function getErrors()
    {
        return $this->errors;
    }

    protected function errorsIsset()
    {
        return count($this->errors) > 0 ? true : false;
    }

    public function onPrepareComponentParams($arParams)
    {
        if (empty($arParams['IBLOCK_TYPE_ID']))
            $this->addError(Loc::getMessage('MAIN_CALC_INVALID_IBLOCK_TYPE'));

        if (empty($arParams['EMAIL_TO']))
            $this->addError(Loc::getMessage('MAIN_CALC_INVALID_EMAIL_TO'));

        if (empty($arParams['WARNING_MESSAGE']))
            $arParams['WARNING_MESSAGE'] = Loc::getMessage('MAIN_CALC_DEFAULT_WARN_MESS');

        return $arParams;
    }

    protected function fillData()
    {
        $iblocksRes = CIBlock::GetList([],[
            "TYPE" => $this->arParams['IBLOCK_TYPE_ID'],
            "ACTIVE" => "Y",
        ]);

        while ($iblock = $iblocksRes->GetNext()) {
            $this->arResult["CATEGORIES"][$iblock['CODE']] = $iblock['NAME'];
            $dbResult = CIBlockElement::GetList(
                $by = [
                    "SORT"=>"ASC"
                ],
                $filter = [
                    'IBLOCK_CODE' => $iblock['CODE']
                ],
                $arGroupBy = false,
                $arNavStartParams = false,
                $arSelectFields = [
                    "ID",
                    "NAME",
                    "CODE",
                    "ACTIVE",
                    "ACTIVE_FROM",
                    "PREVIEW_TEXT",
                    "PROPERTY_PRICE",
                    "PROPERTY_TERMS",
                ]
            );

            while($row = $dbResult->Fetch()) {
                $this->arResult['SERVICES'][$iblock['CODE']][] = [
                    "PRICE" => $row["PROPERTY_PRICE_VALUE"],
                    "TERMS" => $row["PROPERTY_TERMS_VALUE"],
                    "NAME" => $row["NAME"],
                    "CODE" => $row["CODE"],
                ];
            }
        }
    }

    protected function fillPost()
    {
        $request = Context::getCurrent()->getRequest();
        $this->name = htmlspecialcharsbx($request->getPost("clientName"));
        $this->phone = htmlspecialcharsbx($request->getPost("clientTel"));
        $serviceData = base64_decode(htmlspecialcharsbx($request->getPost('servicesData')));
        $this->servicesList = explode("|", $serviceData);
        $this->cost = (int)$request->getPost("servicesCost");

        if (empty($this->name) ||
            empty($this->phone) ||
            empty($this->cost) ||
            empty($this->servicesList))
        {
            $this->addError(Loc::getMessage('MAIN_CALC_INVALID_REQUEST'));
        }
    }

    protected function send()
    {
        $arEventFields = [
            "EMAIL_TO" => $this->arParams['EMAIL_TO'],
            "NAME"     => $this->name,
            "PHONE"    => $this->phone,
            "COST"     => $this->cost,
            "SERVICES" => implode(', ', $this->servicesList),
            "TIME"     => date('d.m.Y H:i', time()),
        ];

        if (!CEvent::Send(
            self::MAIN_CALC_TEMPLATE, // TODO : брать из настроек модуля
            SITE_ID,
            $arEventFields,
            "Y",
            self::MAIL_EVENT_ID // TODO : брать из настроек модуля
        )) {
            $this->addError(Loc::getMessage('MAIN_CALC_SEND_ERROR'));
        }

        if (!$this->errorsIsset())
            $this->arResult["SUCCESS"] = Loc::getMessage('MAIN_CALC_SUCCESS_MESSAGE');

    }

    public function executeComponent()
    {
        $this->arResult['ERRORS'] = $this->getErrors();
        $this->arResult["CATEGORIES"] = [];
        $this->arResult['SERVICES'] = [];
        $request = Context::getCurrent()->getRequest();

        if (!$this->errorsIsset()) {
            $this->fillData();
        }

        if ($request->isPost() && check_bitrix_sessid()) {
            $this->fillPost();

            if (!$this->errorsIsset())
                $this->send();
        }

        $this->includeComponentTemplate();
    }
}