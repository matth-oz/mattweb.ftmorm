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
}?>

<?if(!empty($arResult['ITEMS'])):?>
    <?if($arParams['DISPLAY_TOP_PAGER']):?>
        <div class="nav-wrap">
            <?$APPLICATION->IncludeComponent(
                "bitrix:main.pagenavigation",
                $arParams['PAGER_TEMPLATE'],
                Array(
                "NAV_OBJECT" => $arResult['NAV_OBJECT'],
                    "SEF_MODE" => "N"
                ),
                false
            );?>
        </div>
    <?endif?>

    <table class="main-tbl">
        <thead>    
            <tr>
                <?if($arParams['SHOW_ELEMENT_ID']):?>
                <th>ID</th>
                <?endif?>
                <th><?=$arResult['HEADER_TITLES']['GAME_DATE']?></th>
                <th><?=$arResult['HEADER_TITLES']['CITY']?></th>
                <th><?=$arResult['HEADER_TITLES']['TEAM__NAME']?></th>
                <th><?=$arResult['HEADER_TITLES']['TEAM__FOUND_YEAR']?></th>
                <th><?=$arResult['HEADER_TITLES']['GOALS']?></th>
                <th><?=$arResult['HEADER_TITLES']['OWN']?></th>        
            </tr>
        </thead>
        <tbody>
            <?foreach($arResult['ITEMS'] as $key => $arItem):?>
            <tr>
                <?if($arParams['SHOW_ELEMENT_ID']):?>
                <td>
                    <?if(!empty($arItem['DETAIL_PAGE_URL'])):?>
                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>"><?=$key?></a>
                    <?else:?>
                    <?=$key?>
                    <?endif?>
                </td>
                <?endif?>
                <td><?=$arItem['GAME_DATE']['VALUE']?></td>
                <td>
                    <?if(!empty($arItem['CITY']['VALUE'])):?>
                    <?=$arItem['CITY']['VALUE']?>
                    <?else:?>
                        <?=$arResult['OUR_TEAM_CITY']?>
                    <?endif?>
                </td>
                <td><?=$arItem['TEAM__NAME']['VALUE']?></td>
                <td><?=$arItem['TEAM__FOUND_YEAR']['VALUE']?></td>
                <td><?=$arItem['GOALS']['VALUE'] ?? 0;?></td>
                <td><?=$arItem['OWN']['VALUE'] ?? 0;?></td>
            </tr>
            <?endforeach?>
        </tbody>
    </table>
    <?if($arParams['DISPLAY_BOTTOM_PAGER']):?>
        <div class="nav-wrap">
            <?$APPLICATION->IncludeComponent(
                "bitrix:main.pagenavigation",
                $arParams['PAGER_TEMPLATE'],
                Array(
                "NAV_OBJECT" => $arResult['NAV_OBJECT'],
                    "SEF_MODE" => "N"
                ),
                false
            );?>
        </div>
    <?endif?>
<?endif?>