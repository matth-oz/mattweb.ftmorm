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
echo'</pre>';*/
?>       

<table class="main-tbl">
    <tr>
        <th><?=Loc::GetMessage('TH_NUMB_COL_LABEL')?></th>
        <th><?=Loc::GetMessage('TH_DATE_COL_LABEL')?>
            <?
            $addClass = '';
            if($arResult['DATA_COOKIE']['SORT'] == 'game_date' && $arResult['DATA_COOKIE']['ORD'] == 'desc'){
                $addClass = ' active';
                $sortParams = [];
            }
            else{
                $sortParams = ['game_date'=>'desc'];                            
            }?>                        
            <a href="<?=$component->buildSortParamUrl($arResult['CUR_PAGE'], $sortParams)?>" class="sort-btn<?=$addClass?>">▼</a>
            <?
            $addClass = '';
            if($arResult['DATA_COOKIE']['SORT'] == 'game_date' && $arResult['DATA_COOKIE']['ORD'] == 'asc'){
                $addClass = ' active';
                $sortParams = [];
            }
            else{
                $sortParams = ['game_date'=>'asc'];
            }?>                        
            <a href="<?=$component->buildSortParamUrl($arResult['CUR_PAGE'], $sortParams)?>" class="sort-btn<?=$addClass?>">▲</a>
        </th>
        <th><?=Loc::GetMessage('TH_CITY_COL_LABEL')?>
            <?
            $addClass = '';
            if($arResult['DATA_COOKIE']['SORT'] == 'gm_city' && $arResult['DATA_COOKIE']['ORD'] == 'desc'){
                $addClass = ' active';
                $sortParams = [];
            }
            else{
                $sortParams = ['gm_city'=>'desc'];
            }?>                        
            <a href="<?=$component->buildSortParamUrl($arResult['CUR_PAGE'], $sortParams)?>" class="sort-btn<?=$addClass?>">▼</a>
            <?
            $addClass = '';
            if($arResult['DATA_COOKIE']['SORT'] == 'gm_city' && $arResult['DATA_COOKIE']['ORD'] == 'asc'){
                $addClass = ' active';
                $sortParams = [];
            }
            else{
                $sortParams = ['gm_city'=>'asc'];
            }?>                        
            <a href="<?=$component->buildSortParamUrl($arResult['CUR_PAGE'], $sortParams)?>" class="sort-btn<?=$addClass?>">▲</a>
        </th>
        <th><?=Loc::GetMessage('TH_TEAM_COL_LABEL')?>
            <?
            $addClass = '';
            if($arResult['DATA_COOKIE']['SORT'] == 'tm_name' && $arResult['DATA_COOKIE']['ORD'] == 'desc'){
                $addClass = ' active';
                $sortParams = [];
            }
            else{
                $sortParams = ['tm_name'=>'desc'];
            }?>                        
            <a href="<?=$component->buildSortParamUrl($arResult['CUR_PAGE'], $sortParams)?>" class="sort-btn<?=$addClass?>">▼</a>
            <?
            $addClass = '';
            if($arResult['DATA_COOKIE']['SORT'] == 'tm_name' && $arResult['DATA_COOKIE']['ORD'] == 'asc'){
                $addClass = ' active';
                $sortParams = [];
            }
            else{
                $sortParams = ['tm_name'=>'asc'];
            }?>                        
            <a href="<?=$component->buildSortParamUrl($arResult['CUR_PAGE'], $sortParams)?>" class="sort-btn<?=$addClass?>">▲</a>
        </th>
        <th><?=Loc::GetMessage('TH_SCORE_COL_LABEL')?></th>
        <th><?=Loc::GetMessage('TH_AUTO_GOALS_COL_LABEL')?></th>
    </tr>
    <?
    foreach($arResult['MATCHES'] as $arMatch):?>
    <?if($arMatch['GAME_RESULT'] == 'D'){
        $rowCSSClass = 'd-row';
    }
    elseif($arMatch['GAME_RESULT'] == 'W'){
        $rowCSSClass = 'w-row';
    }else{
        $rowCSSClass = 'l-row';
    }?>
    <tr class="<?=$rowCSSClass?>">
        <td>
        <a href="<?=$arMatch['DETAIL_PAGE_URL']?>"><?=$arMatch['ORD_NUMBER']?></a>
        </td>
        <td>
        <a href="<?=$arMatch['DETAIL_PAGE_URL']?>"><?=$arMatch['GAME_DATE']?></a>
        </td>
        <td>
        <a href="<?=$arMatch['DETAIL_PAGE_URL']?>"><?=$arMatch['CITY']?></a>
        </td>
        <td>
        <a href="<?=$arMatch['DETAIL_PAGE_URL']?>"><?=$arMatch['TM_NAME']?></a>
        </td>
        <td>
        <a href="<?=$arMatch['DETAIL_PAGE_URL']?>"><?=$arMatch['GAME_SCORE']?></a>
        </td>
        <td <?if($arMatch['AUTO_GOALS'] > 0):?>class="ag-alert"<?endif?>>
        <a href="<?=$arMatch['DETAIL_PAGE_URL']?>"><?=$arMatch['AUTO_GOALS']?></a>
        </td>
    </tr>
    <?endforeach?>
</table>
<div class="nav-wrap">
    <?$APPLICATION->IncludeComponent(
        "bitrix:main.pagenavigation",
        ".default",
        Array(
        "NAV_OBJECT" => $arResult['NAV_OBJECT'],
            "SEF_MODE" => "N"
        ),
        false
    );?>
</div>
