<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Mattweb\Ftmorm;

if(!Loader::IncludeModule("mattweb.ftmorm"))
	return;


// получаем список всех HTML-полей формы, 
// которые могут соответствовать типам полей ORM-модели 
$arrAllFieldsHTMLTypes = ServiceActions::getAllFieldsHTMLTypes();

$arClassesList = ServiceActions::getFtmOrmClasses();
$arClassesList = ['none' => '-'] + $arClassesList;

$arScalarFields = $arScalarFieldsFull = [];
if($arCurrentValues['ORM_CLASS_NAME'] != 'none'){
	$servObj = new ServiceActions($arCurrentValues['ORM_CLASS_NAME']);
	$arScalarFields = $servObj->getScalarFields();

	$arScalarFieldsFull = $servObj->getScalarFields('full');
}

$arrAllowedHtmlCurFields = [];
$arrChosenFieldKeys = $arrChosenFieldTitles =  [];

$chosenOrmScalarFields = $arCurrentValues['ORM_CLASS_S_FIELDS'];

$arrChosenSFieldsTypes = json_decode($arCurrentValues['ORM_CLASS_S_FIELDS_TYPES'], true);
	
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
}

$jsOptions = (!empty($arrChosenFieldKeys)) ? implode('||', $arrChosenFieldKeys) : '';

$arFilterDispMode = [
    'horizontal'=> Loc::GetMessage('FILTER_DISPLAY_MODE_HORIZONTAL'),
    'vertical'=> Loc::GetMessage('FILTER_DISPLAY_MODE_VERTICAL'),
];


dump($arCurrentValues);

$arComponentParameters = array(
	"PARAMETERS" => array(
        "ORM_CLASS_NAME" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("ORM_CLASS_NAME_TITLE"),
			"TYPE" => "LIST",
			"VALUES" => $arClassesList,
			"REFRESH" => "Y",
		),
		"ORM_CLASS_S_FIELDS" =>array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("ORM_CLASS_S_FIELDS_TITLE"),
			"TYPE" => "LIST",
			"VALUES" => $arScalarFields,
			"MULTIPLE" => "Y",
            "REFRESH" => "Y",
		),
		"ORM_CLASS_S_FIELDS_TYPES"=> array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("ORM_CLASS_S_FIELDS_TYPES_TITLE"),
			"TYPE" => "CUSTOM",
			"JS_FILE" => '/local/components/ftmorm/ftmorm.filter/settings/settings.js',
			"JS_EVENT" => 'OnOrmClassSFieldsTypesEdit',
			"JS_DATA" => $jsOptions,
			"DEFAULT" =>json_encode($arJsOptionsData),
			"CUR_VALUES" => $arCurrentValues["ORM_CLASS_S_FIELDS_TYPES"],
			"REFRESH" => "Y",
		),
		"FILTER_ACTION_URL" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("FILTER_ACTION_URL_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",	
		),
        "FILTER_DISPLAY_MODE" => array(
            "PARENT" => "VISUAL",
            "NAME" => GetMessage("FILTER_DISPLAY_MODE_TITLE"),
            "TYPE" => "LIST",
            "VALUES" => $arFilterDispMode,
            "DEFAULT" => "horizontal",
        ),
        "USE_TEXTFIELDS_TOOLTIPS" => [
            "PARENT" => "VISUAL",
            "NAME" => GetMessage("USE_TEXTFIELDS_TOOLTIPS_TITLE"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ],
		"MIN_QUERY_LENGTH" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("MIN_QUERY_LENGTH_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => "3",	
		),
		"SHOW_BOOL_ONLY_TRUE" => [
            "PARENT" => "VISUAL",
            "NAME" => GetMessage("SHOW_BOOL_ONLY_TRUE_TITLE"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ],
		"USE_ORM_ENTITY_ALIAS" => [
			"PARENT" =>"FILTER_SETTINGS",
		   "NAME" => Loc::GetMessage("USE_ORM_ENTITY_ALIAS_TITLE"),
		   "TYPE" => "CHECKBOX",
		   "DEFAULT" => "N",
		],
	),
);
?>