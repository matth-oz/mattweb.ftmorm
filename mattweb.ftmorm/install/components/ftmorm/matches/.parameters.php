<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arCurrentValues */

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Mattweb\Ftmorm;


if(!Loader::IncludeModule("mattweb.ftmorm")){
	return;
}

$arComponentParameters = [
	"GROUPS" => [
		"FILTER_SETTINGS" => [
			"SORT" => 150,
			"NAME" => Loc::GetMessage("GPF_FILTER_SETTINGS_TITLE"),
		],
		"LIST_SETTINGS" => [
			"NAME" => Loc::GetMessage("GPN_LIST_SETTINGS_TITLE"),
		],
		"DETAIL_SETTINGS" => [
			"NAME" => Loc::GetMessage("GPN_DETAIL_SETTINGS_TITLE"),
		],
	],
	"PARAMETERS" => [
		"VARIABLE_ALIASES" => [
			// "matches" => ["NAME" => Loc::GetMessage("MATCH_SECTION_ID_DESC")],
			"ELEMENT_ID" => ["NAME" => Loc::GetMessage("MATCH_ELEMENT_ID_DESC")],
		],
		"SEF_MODE" => [
			"matches" => [
				"NAME" => Loc::GetMessage("SEF_PAGE_MATCHES"),
				"DEFAULT" => "",
				"VARIABLES" => [],
			],
			"detail" => [
				"NAME" => Loc::GetMessage("SEF_PAGE_MATCHES_DETAIL"),
				"DEFAULT" => "#GM_ID#/",
				"VARIABLES" => ["GM_ID"],
			],
		],
		"DETAIL_PAGE_PATH" => [
			"PARENT" => "LIST_SETTINGS",
			"NAME" => Loc::GetMessage("DETAIL_PAGE_PATH_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		],
        "EL_PAGE_COUNT" => [
			"PARENT" => "LIST_SETTINGS",
			"NAME" => Loc::GetMessage("EL_PAGE_COUNT_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "10",
		],
		"SORT_COOKIE_LD" => [
			"PARENT" => "LIST_SETTINGS",
			"NAME" => Loc::GetMessage("SORT_COOKIE_LD_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "5",
		],
		"USE_FILTER" => [
			"PARENT" => "FILTER_SETTINGS",
			"NAME" => Loc::GetMessage("DESC_USE_FILTER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		],
	],
];

if (($arCurrentValues['USE_FILTER'] ?? 'N') === 'Y'){

	// добавить параметры из ftmorm.filter
	$arrAllFieldsHTMLTypes = ServiceActions::getAllFieldsHTMLTypes();

	$arComponentParameters["PARAMETERS"]["FILTER_NAME"] = [
		"PARENT" => "FILTER_SETTINGS",
		"NAME" => Loc::GetMessage("FILTER_NAME_TITLE"),
		"TYPE" => "STRING",
		"DEFAULT" => "arrGamesFilter",
	];

	$ormClassNames = ['GamesTable', 'LineupsTable'];

	$arFilterDispMode = [
		'horizontal'=> Loc::GetMessage('FILTER_DISPLAY_MODE_HORIZONTAL'),
		'vertical'=> Loc::GetMessage('FILTER_DISPLAY_MODE_VERTICAL'),
	];

	$ormClassName = 'GamesTable';

	$servObj = new ServiceActions($ormClassName);
	$arScalarFields = $servObj->getScalarFields();
	$arScalarFieldsFull = $servObj->getScalarFields('full');
	
	$arComponentParameters["PARAMETERS"]["ORM_CLASS_S_FIELDS"] = [
		"PARENT" => "FILTER_SETTINGS",
		"NAME" => Loc::GetMessage("ORM_CLASS_S_FIELDS_TITLE"),
		"TYPE" => "LIST",
		"VALUES" => $arScalarFields,
		"MULTIPLE" => "Y",
		"REFRESH" => "Y",
	];
	
	if(!empty($arCurrentValues['ORM_CLASS_S_FIELDS'])){

		$chosenOrmScalarFields = $arCurrentValues['ORM_CLASS_S_FIELDS'];

		if(!empty($chosenOrmScalarFields) && !empty($arScalarFieldsFull)){
			foreach($chosenOrmScalarFields as $chosenFieldKey){

				$chosenFieldType = $arScalarFieldsFull[$chosenFieldKey]['PARENT_CLASS'];
		
				if(array_key_exists($chosenFieldType, $arrAllFieldsHTMLTypes)){
					$arrAllowedHtmlCurFields[$chosenFieldKey] = $arrAllFieldsHTMLTypes[$chosenFieldType];
					$arrChosenFieldKeys[] = $chosenFieldKey;
					$arrChosenFieldTitles[$chosenFieldKey] = $arScalarFields[$chosenFieldKey];
				}
			}
		
			$arJsOptionsData = [
				0 => $arrAllowedHtmlCurFields,
				1 => $arrChosenFieldTitles,
				2 => $arrChosenSFieldsTypes
			];
			
			$jsOptions = (!empty($arrChosenFieldKeys)) ? implode('||', $arrChosenFieldKeys) : '';
			
			$arComponentParameters["PARAMETERS"]["ORM_CLASS_S_FIELDS_TYPES"] = [
				"PARENT" => "FILTER_SETTINGS",
				"NAME" => Loc::GetMessage("ORM_CLASS_S_FIELDS_TYPES_TITLE"),
				"TYPE" => "CUSTOM",
				"JS_FILE" => '/local/components/ftmorm/ftmorm.filter/settings/settings.js',
				"JS_EVENT" => 'OnOrmClassSFieldsTypesEdit',
				"JS_DATA" => $jsOptions,
				"DEFAULT" =>json_encode($arJsOptionsData),
				"CUR_VALUES" => $arCurrentValues["ORM_CLASS_S_FIELDS_TYPES"],
				"REFRESH" => "Y",
			];
		}

	}	
	
    $arComponentParameters["PARAMETERS"]["FILTER_DISPLAY_MODE"] = [
		"PARENT" => "FILTER_SETTINGS",
		"NAME" => Loc::GetMessage("FILTER_DISPLAY_MODE_TITLE"),
		"TYPE" => "LIST",
		"VALUES" => $arFilterDispMode,
		"DEFAULT" => "horizontal",
	]; 

	$arComponentParameters["PARAMETERS"]["USE_TEXTFIELDS_TOOLTIPS"] = [
		"PARENT" => "FILTER_SETTINGS",
		"NAME" => Loc::GetMessage("USE_TEXTFIELDS_TOOLTIPS_TITLE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	];

	$arComponentParameters["PARAMETERS"]["MIN_QUERY_LENGTH"] = [
		"PARENT" => "FILTER_SETTINGS",
		"NAME" => Loc::GetMessage("MIN_QUERY_LENGTH_TITLE"),
		"TYPE" => "STRING",
		"DEFAULT" => "3",
	];
	
	$arComponentParameters["PARAMETERS"]["SHOW_BOOL_ONLY_TRUE"] = [
 		"PARENT" =>"FILTER_SETTINGS",
        "NAME" => Loc::GetMessage("SHOW_BOOL_ONLY_TRUE_TITLE"),
        "TYPE" => "CHECKBOX",
        "DEFAULT" => "N",
	];

	$arComponentParameters["PARAMETERS"]["USE_ORM_ENTITY_ALIAS"] = [
		"PARENT" =>"FILTER_SETTINGS",
	   "NAME" => Loc::GetMessage("USE_ORM_ENTITY_ALIAS_TITLE"),
	   "TYPE" => "CHECKBOX",
	   "DEFAULT" => "N",
   ];

}