<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

if(!Loader::IncludeModule("mattweb.ftmorm"))
	return;

$arComponentParameters = array(
	"PARAMETERS" => array(
		"ELEMENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("ELEMENT_ID_TITLE"),
			"TYPE" => "STRING",
		),
        "ELEMENT_ID_REQ_VAR" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("ELEMENT_ID_REQ_VAR_TITLE"),
			"TYPE" => "STRING",
		),
		"LIST_PAGE_PATH" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("LIST_PAGE_PATH_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
	),
);
?>