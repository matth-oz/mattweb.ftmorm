<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

if(!Loader::IncludeModule("mattweb.ftmorm"))
	return;

$arComponentParameters = array(
	"PARAMETERS" => array(
		"DETAIL_PAGE_PATH" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("DETAIL_PAGE_PATH_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
        "EL_PAGE_COUNT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("EL_PAGE_COUNT_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "10",
		),
		"FILTER_NAME" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("FILTER_NAME_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"PAGENAV_TEMPLATE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("PAGENAV_TEMPLATE_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => ".default",
		),
	),
);
?>