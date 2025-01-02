<?php
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){	die();}

//$this->setFrameMode(true);

if($arParams["USE_FILTER"]=="Y"):
    $APPLICATION->IncludeComponent(
        "ftmorm:ftmorm.filter", 
        ".default", 
        array(
            "ORM_CLASS_NAME" => "GamesTable",
            "COMPONENT_TEMPLATE" => ".default",
            "ORM_CLASS_S_FIELDS" => $arParams["ORM_CLASS_S_FIELDS"],
            "ORM_CLASS_R_FIELDS" => "",
            "FILTER_ACTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["matches"],
            "ORM_CLASS_S_FIELDS_TYPES" => str_replace('&quot;', '"', $arParams["ORM_CLASS_S_FIELDS_TYPES"]),
            "FILTER_DISPLAY_MODE" => $arParams["FILTER_DISPLAY_MODE"],
            "USE_TEXTFIELDS_TOOLTIPS" => $arParams["USE_TEXTFIELDS_TOOLTIPS"],
            "MIN_QUERY_LENGTH" => $arParams["MIN_QUERY_LENGTH"],
            "SHOW_BOOL_ONLY_TRUE" => $arParams["SHOW_BOOL_ONLY_TRUE"],
            'USE_ORM_ENTITY_ALIAS' => $arParams["USE_ORM_ENTITY_ALIAS"],
        ),
        $component
    );
endif;

$GLOBALS[$arParams["FILTER_NAME"]] = (isset($_REQUEST['filter'])) ? $USER->GetParam('ftmormfilter') : [];

$pagerBaseLink = (isset($_REQUEST['filter'])) ? $APPLICATION->GetCurPage()."?filter=y" : "";

if(!isset($_REQUEST['filter'])){
	$USER->SetParam('ftmormfilter', []);
}
?>
<br />
<?php
$APPLICATION->IncludeComponent(
    "ftmorm:matches.list_adv",
    "",
    [
        "USE_FILTER" => $arParams["USE_FILTER"],
        "FILTER_NAME" => $arParams["FILTER_NAME"],
        "DETAIL_PAGE_PATH" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["detail"],
        "EL_PAGE_COUNT" => $arParams["EL_PAGE_COUNT"],
        "SORT_COOKIE_LD" => $arParams["SORT_COOKIE_LD"],
    ],
    $component
);