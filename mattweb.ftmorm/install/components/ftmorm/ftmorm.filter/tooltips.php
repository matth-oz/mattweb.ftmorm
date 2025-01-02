<?php
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity;
use Mattweb\Ftmorm;
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

$_POST = json_decode(file_get_contents('php://input'), true);

$arResult = $arErrors = [];

if(isset($_POST['field_value']) && isset($_POST['model_name']) && isset($_POST['model_field_name'])){

    $curPrefix = '';
    $modelName = base64_decode($_POST['model_name']);
    $modelFieldName = base64_decode($_POST['model_field_name']);

    Loader::IncludeModule('mattweb.ftmorm');

    if(!empty($_POST['model_alias'])){
         $classesPrefixes = ServiceActions::getFtmOrmClassesPrefixes();

         if(array_key_exists($modelName, $classesPrefixes)){
            $curPrefix = $classesPrefixes[$modelName];

            $modelFieldName = str_replace($curPrefix, '', $modelFieldName);
        }
    }

    $fieldVal = trim($_POST['field_value']);

    if(!empty($modelName) && !empty($modelFieldName) && !empty($fieldVal)){

        $modelName = 'Mattweb\\Ftmorm\\'.$modelName;

        $curEntity = $modelName::getEntity();
        $query = new Entity\Query($curEntity);

        $arSelect = [$modelFieldName];
        
        $filterCond = '%='.$modelFieldName;
        $filterVal = '%'.$fieldVal.'%';

        $query->setSelect($arSelect);
        $query->setFilter([
            [$filterCond => $filterVal]
        ]);
        $curEntityRes = $query->exec();

        $arResultTmp = [];
        while($arCurRes = $curEntityRes->fetch()){
            if(!in_array($arCurRes[$modelFieldName], $arResultTmp)){
                $arResultTmp[] = $arCurRes[$modelFieldName];
            }            
        }

        if(count($arResultTmp) > 0){
            $arResult['result'] = 'success';
            $arResult['elements'] = $arResultTmp;
        }
    }
    else{
        $arErrors['NO_PARAM_CART_ACTION'] = 'Все параметры должны быть заполнены';
    }
}
else{
    $arErrors['NO_PARAM_CART_ACTION'] = 'Скрипт не может запускаться без параметров';
}

if(count($arErrors) > 0){
    $arResult['result'] = 'error';
    $arResult['messages'] = $arErrors;
}

header('Content-Type: application/json; charset='.LANG_CHARSET);
echo \Bitrix\Main\Web\Json::encode($arResult);
die();
