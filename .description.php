<?php
use Bitrix\Main\Localization\Loc;
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
    die();

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
    "NAME" => Loc::getMessage("MAIN_CALC_COMP_NAME"),
    "DESCRIPTION" => Loc::getMessage("MAIN_CALC_COMP_DESCR"),
    "PATH" => array(
        "ID" => "service",
        "NAME" => Loc::getMessage("MAIN_CALC_COMP_NAME")
    ),
    "CACHE_PATH" => "Y",
    "COMPLEX" => "N"
);
?>