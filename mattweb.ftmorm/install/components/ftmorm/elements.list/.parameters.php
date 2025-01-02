<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Mattweb\Ftmorm;

if(!Loader::IncludeModule("mattweb.ftmorm") || !Loader::IncludeModule("iblock"))
	return;

$arClassesList = ServiceActions::getFtmOrmClasses();
$arClassesList = ['none' => '-'] + $arClassesList;

$arScalarFields = $arScalarFieldsFull = $arRelationFields = [];
$arSortFields = [];

if(isset($arCurrentValues['ORM_CLASS_NAME']) && $arCurrentValues['ORM_CLASS_NAME'] != 'none'){
	$servObj = new ServiceActions($arCurrentValues['ORM_CLASS_NAME']);
	$arScalarFields = $servObj->getScalarFields();
	
	// Первичный ключ (их может быть > 1)
	$pk = ServiceActions::getPrimaryKey($arCurrentValues['ORM_CLASS_NAME']);
	$arPkTtl = [];
	if(count($pk) > 1){		
		foreach($pk as $pkVal){
			$arPkTtl[$pkVal] = $pkVal;
		}		
	}
	else{
		$arPkTtl[0] = Loc::GetMessage('ELEMENTS_ID_TITLE');		
	}

	$arSortFields = $arPkTtl + $arScalarFields;
	
	$arScalarFieldsFull = $servObj->getScalarFields('full');
	$arRelationFields = $servObj->getRelationFields();


	//dump($arRelationFields);
}

$arSorts = [
	"ASC"=>GetMessage("FTMORM_SORT_DESC_ASC"),
	"DESC"=>GetMessage("FTMORM_SORT_DESC_DESC"),
];

$arPagerTemplates = ServiceActions::getPaginationTemplatesList();

$arComponentParameters = [
	"GROUPS" => [
		"PAGER_SETTINGS" => [
			"NAME" => Loc::getMessage("DESC_PAGER_SETTINGS"),
		],
	],
    "PARAMETERS" => [
		// "ORM_CLASS_NAME" - Имя ORM-класса
		"ORM_CLASS_NAME" => [
			"PARENT" => "BASE",
			"NAME" => Loc::GetMessage("ORM_CLASS_NAME_TITLE"),
			"TYPE" => "LIST",
			"VALUES" => $arClassesList,
			"REFRESH" => "Y",
		],
		// "ORM_CLASS_S_FIELDS" - Поля ORM-класса для отображения
		"ORM_CLASS_S_FIELDS" => [
			"PARENT" => "BASE",
			"NAME" => Loc::GetMessage("ORM_CLASS_S_FIELDS_TITLE"),
			"TYPE" => "LIST",
			"VALUES" => $arScalarFields,
			"MULTIPLE" => "Y",			
			"HIDDEN" => (empty($arScalarFields) ? 'Y' : 'N')
		],
		// "ORM_CLASS_R_FIELDS" - Relation-поля ORM-класса для фильтра
		"ORM_CLASS_R_FIELDS" => [
			"PARENT" => "BASE",
			"NAME" => Loc::GetMessage("ORM_CLASS_R_FIELDS_TITLE"),
			"TYPE" => "LIST",
			"VALUES" => $arRelationFields,
			"MULTIPLE" => "Y",
			//"HIDDEN" => (empty($arRelationFields) ? 'Y' : 'N')
		],
		// SHOW_ELEMENT_ID - Вывести идентификаторы записей
		"SHOW_ELEMENT_ID" => [
			"PARENT" => "DATA_SOURCE",
			"NAME" => Loc::GetMessage("SHOW_ELEMENT_ID_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		// "ELEMENTS_COUNT" - Количество записей на странице
		"ELEMENTS_COUNT" => [
			"PARENT" => "DATA_SOURCE",
			"NAME" => Loc::GetMessage("ELEMENTS_COUNT_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "20",
		],
		// "FILTER_NAME" - Фильтр
		"FILTER_NAME" => [
			"PARENT" => "DATA_SOURCE",
			"NAME" => Loc::GetMessage("FILTER_NAME_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		],
		// "SORT_BY" - Поле для сортировки записей
		"SORT_BY" => [
			"PARENT" => "DATA_SOURCE",
			"NAME" => Loc::GetMessage("SORT_BY_TITLE"),
			"TYPE" => "LIST",
			"DEFAULT" => "ACTIVE_FROM",
			"VALUES" => $arSortFields,
			//"ADDITIONAL_VALUES" => "Y",
		],
		// "SORT_ORDER" - Направление для сортировки записей
		"SORT_ORDER" => [
			"PARENT" => "DATA_SOURCE",
			"NAME" => Loc::GetMessage("SORT_ORDER_TITLE"),
			"TYPE" => "LIST",
			"DEFAULT" => "DESC",
			"VALUES" => $arSorts,
			//"ADDITIONAL_VALUES" => "Y",
		],
		// "DETAIL_URL" - URL страницы детального просмотра
		"DETAIL_URL" => [
			"PARENT" => "URL_TEMPLATES",
			"NAME" => Loc::GetMessage("DETAIL_URL_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		],
		// DEBUG_MODE - Включить режим отладки
		"DEBUG_MODE" => [
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => Loc::GetMessage("DEBUG_MODE_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		],
		// PAGE_TITLE - заголовок страницы
		"PAGE_HEADER" => [
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => Loc::GetMessage("PAGE_HEADER_TITLE"),
			"TYPE" => "STRING",
		],
		// PAGER_TEMPLATE
		"PAGER_TEMPLATE" => [
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => Loc::GetMessage("PAGER_TEMPLATE_TITLE"),
			"TYPE" => "LIST",
			"DEFAULT" => "DESC",
			"VALUES" => $arPagerTemplates,
		],
		// PAGER_TITLE
		"PAGER_TITLE" => [
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => Loc::GetMessage("PAGER_TITLE_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => Loc::GetMessage("PAGER_ELEMENTS_DESC"),
		],		
		// PAGER_SHOW_ALWAYS
		"PAGER_SHOW_ALWAYS" => [
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => Loc::GetMessage("PAGER_SHOW_ALWAYS_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		],
		// PAGER_SHOW_ALL
		"PAGER_SHOW_ALL" => [
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => Loc::GetMessage("PAGER_SHOW_ALL_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		],
		// DISPLAY_TOP_PAGER
		"DISPLAY_TOP_PAGER" => [
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => Loc::GetMessage("DISPLAY_TOP_PAGER_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		],
		// DISPLAY_BOTTOM_PAGER
		"DISPLAY_BOTTOM_PAGER" => [
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => Loc::GetMessage("DISPLAY_BOTTOM_PAGER_TITLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],
		// ACTIVE_DATE_FORMAT - Формат показа даты 
		"ACTIVE_DATE_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage("ACTIVE_DATE_FORMAT_DESC"), "ADDITIONAL_SETTINGS"),
		// настройки кеширования
		"CACHE_TIME" => [
			"DEFAULT"=>36000000
		],
    ],    
];

// Настройки 404
\CIBlockParameters::Add404Settings($arComponentParameters, $arCurrentValues);