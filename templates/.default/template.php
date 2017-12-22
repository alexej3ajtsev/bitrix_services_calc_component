<?php
use Bitrix\Main\Localization\Loc;
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
?>
<?php

dump($arParams);
if (!empty($arResult['SUCCESS']))
    dump($arResult['SUCCESS']);

?>

<div class="errors-block">
<?php foreach($arResult['ERRORS'] as $error): ?>
    <div class="error">
        <?= $error ?>
    </div>
<?php endforeach; ?>
</div>

<div class="calculator-wrapper">
    <div class="calc" id="calc">
        <div class="notifications">
        </div>
        <select name="category" class="form-control">
            <?php foreach($arResult['CATEGORIES'] as $code => $name):  ?>
                <option value="<?= $code ?>"><?= $name ?></option>
            <?php endforeach; ?>
        </select>

        <?php foreach($arResult['SERVICES'] as $code => $services): ?>
        <select name="<?= $code ?>" class="service form-control">
            <?php foreach($services as $service): ?>
            <option data-price="<?= $service['PRICE'] ?>"
                    data-terms="<?= $service['TERMS'] ?>"
                    data-name="<?= $service['CODE'] ?>"
                    value="<?= $service['CODE'] ?>">
                <?= $service['NAME'] ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php endforeach; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="info-service">
                    <span><b><?= Loc::getMessage('MAIN_CALC_PRICE_TITLE') ?>:</b></span> <span id="priceService"></span><br>
                    <span><b><?= Loc::getMessage('MAIN_CALC_TERMS_TITLE') ?>:</b></span> <span id="termsService"></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <a href="" id="addService" class="btn btn-default"><?= Loc::getMessage('MAIN_CALC_ADD_SERVICE_BTN') ?></a>
            </div>
            <div class="col-md-6">
                <a href="" id="confirmService" class="btn btn-success"><?= Loc::getMessage('MAIN_CALC_CONFIRM_SERVICE_BTN') ?></a>
            </div>
        </div>
        <div class="cart">
            <b>Корзина:</b> <span id="cost">0</span>
            <i class="fa fa-rub" aria-hidden="true"></i>
            <span id="reset">
                <i class="fa fa-refresh" aria-hidden="true"></i>
            </span>
        </div>
        <div class="row">
            <ul class="selected-services">

            </ul>
        </div>
    </div>
    <div  class="feedback-form">
        <div class="notifications">
        </div>
        <form id="calcSendForm" action="" method="post" data-errmsg="<?= $arParams['WARNING_MESSAGE'] ?>">
            <h3 class="watchword"><?= Loc::getMessage('MAIN_CALC_WATCHWORD') ?></h3>
            <?= bitrix_sessid_post() ?>
            <input type="hidden" id="servicesData" name="servicesData" value="">
            <input type="hidden" id="servicesCost" name="servicesCost" value="">
            <div class="input-wrapper">
                <input type="text"
                       name="clientName"
                       id="clientName"
                       class="required clientName form-control"
                       placeholder="<?= Loc::getMessage('MAIN_CALC_NAME_INPUT') ?>">
            </div>

            <div class="input-wrapper">
                <input type="tel" name="clientTel" id="clientTel" class="required mobileRu form-control">
            </div>

            <button type="submit"
                    class="btn btn-success btn-block"
                    name="sendService">
                <?= Loc::getMessage('MAIN_CALC_SEND_BTN') ?>
            </button>
        </form>
    </div>
</div>

