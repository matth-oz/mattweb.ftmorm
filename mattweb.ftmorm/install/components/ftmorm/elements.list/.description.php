<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$arComponentDescription = array(
	"NAME" => GetMessage("FTMORM_ELEMENTS_LIST_COMP_NAME"),
	"DESCRIPTION" => Loc::GetMessage("FTMORM_ELEMENTS_LIST_COMP_DESC"),
	"ICON" => "/images/news_line.gif",
	"SORT" => 20,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "ftmorm",
		//"NAME" => Loc::GetMessage('FTMORM_COMPS_PARENT_TITLE')
	),
);
?>