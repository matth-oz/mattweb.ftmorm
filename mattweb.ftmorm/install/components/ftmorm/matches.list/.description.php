<?
use Bitrix\Main\Localization\Loc;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => Loc::GetMessage('MATCHES_LIST_COMP_NAME'),
	"DESCRIPTION" => Loc::GetMessage('MATCHES_LIST_COMP_DESCR'),
	"ICON" => "/images/icon.gif",
	"SORT" => 10,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "ftmorm",		
	),
	"COMPLEX" => "N",
);

?>