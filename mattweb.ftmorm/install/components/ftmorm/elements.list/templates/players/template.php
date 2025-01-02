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
                <th><?=$arResult['HEADER_TITLES']['FIRST_NAME']?></th>
                <th><?=$arResult['HEADER_TITLES']['LAST_NAME']?></th>
                <th><?=$arResult['HEADER_TITLES']['NICKNAME']?></th>
                <th><?=$arResult['HEADER_TITLES']['CITIZENSHIP']?></th>
                <th><?=$arResult['HEADER_TITLES']['DOB']?></th>
                <th><?=$arResult['HEADER_TITLES']['ROLE']?></th>
            </tr>
        </thead>
        <tbody>
            <?foreach($arResult['ITEMS'] as $key => $arItem):?>
                <tr>
                    <?if($arParams['SHOW_ELEMENT_ID']):?>
                    <td>
                        <?if(!empty($arItem['DETAIL_PAGE_URL'])):?>
                            <a href="<?=$arItem['DETAIL_PAGE_URL']?>"><?=$arItem['ID']['VALUE']?></a>
                        <?else:?>
                            <?=$arItem['ID']['VALUE']?>
                        <?endif?>
                    </td>
                    <?endif?>
                    <td><?=$arItem['FIRST_NAME']['VALUE']?></td>
                    <td><?=$arItem['LAST_NAME']['VALUE']?></td>
                    <td><?=$arItem['NICKNAME']['VALUE']?></td>
                    <td><?=$arItem['CITIZENSHIP']['VALUE']?></td>
                    <td><?=$arItem['DOB']['VALUE']?></td>
                    <td><?=$arItem['ROLE']['VALUE']?></td>
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
