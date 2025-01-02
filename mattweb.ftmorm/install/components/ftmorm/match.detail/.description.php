<?
use Bitrix\Main\Localization\Loc;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => Loc::GetMessage('MATCH_DETAIL_COMP_NAME'),
	"DESCRIPTION" => Loc::GetMessage('MATCH_DETAIL_COMP_DESC'),
	"ICON" => "/images/icon.gif",
	"SORT" => 10,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "ftmorm"
	),
	"COMPLEX" => "N",
);

?>