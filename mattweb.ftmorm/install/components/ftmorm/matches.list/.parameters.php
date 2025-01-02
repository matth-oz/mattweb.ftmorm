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
		"SORT_COOKIE_LD" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SORT_COOKIE_LD_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "5",
		),
		"FILTER_NAME" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("FILTER_NAME_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		)
	),
);
?>