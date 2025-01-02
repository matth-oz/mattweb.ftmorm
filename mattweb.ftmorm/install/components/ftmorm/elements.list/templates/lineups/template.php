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
?>

<?if(!empty($arResult['ITEMS'])):?>
    <table class="main-tbl">
        <thead>    
            <tr>
                <th><?=$arResult['HEADER_TITLES']['GAME_ID']?></th>
                <th><?=$arResult['HEADER_TITLES']['GAME__GAME_DATE']?></th>
                <th><?=$arResult['HEADER_TITLES']['GAME__CITY']?></th>
                <th><?=$arResult['HEADER_TITLES']['GAME__TEAM_ID']?></th>
                <th>Счет</th>
                <th><?=$arResult['HEADER_TITLES']['GAME__OWN']?></th>


            </tr>
        </thead>
        <tbody>
            <?foreach($arResult['ITEMS'] as $key => $arItem):?>
            <tr>
                <td><?=$arItem['GAME_ID']['VALUE']?></td>
                <td><?=$arItem['GAME__GAME_DATE']['VALUE']?></td>
                <td>
                    <?if(!empty($arItem['GAME__CITY']['VALUE'])):?>    
                    <?=$arItem['GAME__CITY']['VALUE']?>
                    <?else:?>
                        <?=$arResult['OUR_TEAM_CITY']?>
                    <?endif?>                
                </td>
                <td><span style="color: #f00;"><?=$arItem['GAME__TEAM_ID']['VALUE']?></span></td>
                <td><span style="color: #f00;"> – </span></td>
                <td><?=intval($arItem['GAME__OWN']['VALUE'])?></td>
                
            </tr>
            <?endforeach?>
        </tbody>
        

</table>

<?endif?>