<?
use \Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

/*echo'<pre>';
if(function_exists('dump')){dump($arResult);}else{var_dump($arResult);}
echo'</pre>';
die();*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="<?=$templateFolder?>/style.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=Loc::GetMessage('COMP_PAGE_TITLE', [
            '#DATE_MATCH_EARLIEST#'=>$arResult['DATE_MATCH_EARLIEST'], 
            '#DATE_MATCH_LATEST#'=>$arResult['DATE_MATCH_LATEST']
            ])?></title>
</head>
<body>
    <div class="main-wrap">
        <div class="head">
            <h1><?=Loc::GetMessage('COMP_PAGE_HDR', 
            ['#DATE_MATCH_EARLIEST#'=>$arResult['DATE_MATCH_EARLIEST'], 
            '#DATE_MATCH_LATEST#'=>$arResult['DATE_MATCH_LATEST']]);?>            
            </h1>
        </div>
        <div class="main-cont">
            <table class="main-tbl">
                <tr>
                    <th><?=Loc::GetMessage('TH_PLAYER_FLN_COL_LABEL')?></th>
                    <th><?=Loc::GetMessage('TH_PLAYER_NN_COL_LABEL')?></th>
                    <th><?=Loc::GetMessage('TH_PLAYER_DOB_LABEL')?></th>
                    <th><?=Loc::GetMessage('TH_PLAYER_CITIZEN_LABEL')?></th>
                    <th><?=Loc::GetMessage('TH_PLAYER_ROLE_LABEL')?></th>
                </tr>
                <?
                foreach($arResult['PLAYERS'] as $arPlayer):?>
                <tr class="<?=$rowCSSClass?>">
                    <td>
                    <a href="<?=$arPlayer['DETAIL_PAGE_URL']?>"><?=$arPlayer['PLAYER_FN'].' '.$arPlayer['PLAYER_LN']?></a>
                    </td>
                    <td>
                    <a href="<?=$arPlayer['DETAIL_PAGE_URL']?>"><?=$arPlayer['PLAYER_NN']?></a>
                    </td>
                    <td>
                    <a href="<?=$arPlayer['DETAIL_PAGE_URL']?>"><?=$arPlayer['PLAYER_DOB']?></a>
                    </td>
                    <td>
                    <a href="<?=$arPlayer['DETAIL_PAGE_URL']?>"><?=$arPlayer['PLAYER_CITIZEN']?></a>
                    </td>
                    <td>
                    <a href="<?=$arPlayer['DETAIL_PAGE_URL']?>"><?=$arPlayer['PLAYER_ROLE']?></a>
                    </td>
                </tr>
                <?endforeach?>
            </table>

            <div class="nav-wrap">
                <?$APPLICATION->IncludeComponent(
                    "bitrix:main.pagenavigation",
                    "orm_filter",
                    Array(
                    "NAV_OBJECT" => $arResult['NAV_OBJECT'],
                        "SEF_MODE" => "N"
                    ),
                    false
                );?>
            </div>

        </div>
    </div>

</body>
</html>