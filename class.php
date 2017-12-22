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
            $this->addError(Loc::getMessage('INVALID_IBLOCK_TYPE'));

        if (empty($arParams['EMAIL_TO']))
            $this->addError(Loc::getMessage('INVALID_EMAIL_TO'));

        if (empty($arParams['WARNING_MESSAGE']))
            $arParams['WARNING_MESSAGE'] = Loc::getMessage('DEFAULT_WARN_MESS');

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
        $encodedserviceData = base64_decode(htmlspecialcharsbx($request->getPost('servicesData')));
        $this->servicesList = explode("|", $encodedserviceData);

        if (empty($this->name) || empty($this->phone) || empty($this->servicesList)) {
            $this->addError();
        }
    }

    protected function send()
    {

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