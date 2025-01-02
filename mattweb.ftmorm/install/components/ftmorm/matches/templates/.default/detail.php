<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){	
    die();
}

$this->setFrameMode(true);

$APPLICATION->IncludeComponent(
	"ftmorm:match.detail",
	".default",
	array(
		"ELEMENT_ID" => $arResult['VARIABLES'][$arParams['ELEMENT_ID_REQ_VAR']],
		"ELEMENT_ID_REQ_VAR" => $arParams['ELEMENT_ID_REQ_VAR'],
        "DETAIL_URL" => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['detail'],
		"LIST_PAGE_PATH" => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['matches']
	),
	$component
);