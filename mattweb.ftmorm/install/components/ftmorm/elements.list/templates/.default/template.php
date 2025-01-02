<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $component */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
?>

<?if($arParams['DEBUG_MODE'] == "Y"){
    ?><div><code>$arParams:</code></div><?
    $component::dump($arParams);
    ?><div><code>$arResult:</code></div><?
    $component::dump($arResult);
}
else{
    echo '<p style="color:#f00;">Для отображения <code>$arParams</code> и <code>$arResult</code> включите режим отладки в параметрах компонента</p>';
}?>

