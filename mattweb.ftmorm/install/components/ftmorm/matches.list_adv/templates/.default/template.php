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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="<?=$templateFolder?>/style.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=Loc::GetMessage('COMP_PAGE_TITLE', ['#OUR_TEAM_NAME#'=>$arResult['OUR_TEAM_NAME']])?></title>
</head>
<body>
    <div class="main-wrap">
        <div class="head">
            <h1><?=Loc::GetMessage('COMP_PAGE_HDR', 
            ['#OUR_TEAM_NAME#'=>$arResult['OUR_TEAM_NAME'], 
            '#DATE_MATCH_EARLIEST#'=>$arResult['DATE_MATCH_EARLIEST'], 
            '#DATE_MATCH_LATEST#'=>$arResult['DATE_MATCH_LATEST']]);?>            
            </h1>
        </div>
        <div class="main-cont">
            <?if(!empty($arResult['FILTER_DATA'])):?>
                <div class="filter-wrap">
                    <form class="filter-form" method="get" action="<?=$arResult['FILTER_ACTION_URL']?>">
                        <div class="filter-field-wrap">
                            <label for="match_city"><?=Loc::GetMessage('FILTER_CITY_LABEL')?></label>
                            <select name="match_city" id="match_city">
                                <option value="all"><?=Loc::GetMessage('FILTER_ALL_CITIES')?></option>
                                <?foreach($arResult['FILTER_DATA']['GM_CITY'] as $city):?>
                                <option value="<?=$city?>"
                                    <?if($arResult['FILTER_REQ_VALS']['match_city'] !== 'all' && $city == $arResult['FILTER_REQ_VALS']['match_city']):?>selected="selected"<?endif?>><?=$city?>
                                </option>
                                <?endforeach?>
                            </select>
                        </div>
                        <div class="filter-field-wrap">
                            <label for="game_dstart"><?=Loc::GetMessage('FILTER_GMD_START_LABEL')?></label>
                            <input 
                            type="date" 
                            id="game_dstart" 
                            name="game_dstart" 
                            value="<?if(isset($arResult['FILTER_REQ_VALS']['game_dstart'])):?><?=$arResult['FILTER_REQ_VALS']['game_dstart']?><?endif?>" 
                            min="" 
                            max="" />
                        </div>
                        <div class="filter-field-wrap">
                            <label for="game_dfinish"><?=Loc::GetMessage('FILTER_GMD_FINISH_LABEL')?></label>
                            <input 
                            type="date" 
                            id="game_dfinish" 
                            name="game_dfinish" 
                            value="<?if(isset($arResult['FILTER_REQ_VALS']['game_dfinish'])):?><?=$arResult['FILTER_REQ_VALS']['game_dfinish']?><?endif?>" 
                            min="" 
                            max="" />
                        </div>
                        <div class="filter-field-wrap">
                            <input type="submit" name="filter" value="<?=Loc::GetMessage('FILTER_SUBMIT_LABEL')?>" />
                        </div>
                        <?if(!empty($arResult['FILTER_REQ_VALS'])):?>
                        <div class="filter-field-wrap">
                            <a class="reset-btn" href="<?=$arResult['CUR_PAGE']?>"><?=Loc::GetMessage('FILTER_RESET_LABEL');?></a>
                        </div>
                        <?endif?>
                    </form>
                </div>
            <?endif?>
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
                    array(
                    "NAV_OBJECT" => $arResult['NAV_OBJECT'],
                        "SEF_MODE" => "N"
                    ),
                    $component,
                    array(
                        'HIDE_ICONS' => 'Y'
                    )
                );?>
            </div>
        </div>
    </div>

</body>
</html>