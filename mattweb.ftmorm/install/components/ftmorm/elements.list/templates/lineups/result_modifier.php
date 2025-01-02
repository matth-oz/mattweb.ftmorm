<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!empty($arResult['ITEMS'])){

    $arFirstItem = $arResult['ITEMS'][0];

    $arItemKeys = array_keys($arFirstItem);

    foreach($arItemKeys as $key){    
        if(!array_key_exists($key, $arResult['HEADER_TITLES'])){
            if(is_array($arFirstItem[$key])){
                $arResult['HEADER_TITLES'][$key] = $arFirstItem[$key]['NAME'];
            }
            
        }
    }
}