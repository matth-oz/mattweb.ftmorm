<?
use Bitrix\Main\Localization\Loc;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//ftmorm.filter
//FTMORM_FILTER

$arComponentDescription = array(
	"NAME" => Loc::GetMessage('FTMORM_FILTER_COMP_NAME'),
	"DESCRIPTION" => Loc::GetMessage('FTMORM_FILTER_COMP_DESC'),
	"ICON" => "/images/icon.gif",
	"SORT" => 10,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "ftmorm"
	),
	"COMPLEX" => "N",
);

?>