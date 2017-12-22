<?php
use Bitrix\Main\Localization\Loc;
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

CModule::IncludeModule("iblock");
Loc::loadMessages(__FILE__);

$dbIBlockType = CIBlockType::GetList();

while ($row = $dbIBlockType->GetNext()) {
    if ($arIBlockTypeLang = CIBlockType::GetByIDLang($row["ID"], LANGUAGE_ID))
        $arIblockType[$row["ID"]] = "[".$row["ID"]."] ".$arIBlockTypeLang["NAME"];
}

$arComponentParameters = array(
    "GROUPS" => array(
        "SETTINGS" => array(
            "NAME" => Loc::getMessage("MAIN_CALC_SETTINGS_NAME")
        ),
        "PARAMS" => array(
            "NAME" => Loc::getMessage("MAIN_CALC_PARAMS_NAME")
        ),
    ),
    "PARAMETERS" => array(
        "IBLOCK_TYPE_ID" => array(
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("MAIN_CALC_TYPES"),
            "TYPE" => "LIST",
            "MULTIPLE" => "N",
            "VALUES" => $arIblockType,
            "REFRESH" => "Y"
        ),
        "EMAIL_TO" => array(
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("MAIN_CALC_EMAIL_TO"),
            "TYPE" => "STRING",
            "REFRESH" => "Y",
            "DEFAULT" => Loc::getMessage("MAIN_CALC_EMAIL_TO_DEFAULT"),
            "COLS" => 25,
        ),
        "WARNING_MESSAGE" => array(
            "PARENT" => "SETTINGS",
            "NAME" => Loc::getMessage("MAIN_CALC_WARN_MESS_TITLE"),
            "TYPE" => "STRING",
            "REFRESH" => "Y",
            "DEFAULT" => Loc::getMessage("MAIN_CALC_WARN_MESS"),
            "COLS" => 25,
        ),
    ),
);
?>