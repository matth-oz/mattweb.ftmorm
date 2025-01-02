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
    <link rel="stylesheet" type="text/css" href="/test_orm/css/styles.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$arResult['PAGE_TITLE']?></title>
</head>
    <body>
        <div class="main-wrap">
            <div class="head">
                <h1><?=$arResult['PAGE_TITLE']?></h1>
            </div>
            <div class="main-cont">
                <div class="cont-detail">
                    <div class="summary">
                        <p><?=Loc::getMessage('PLAYER_FLN')?>
                            &nbsp;<span><?=$arResult['PLAYER']['FN'].' '.$arResult['PLAYER']['LN'];?></span></p>
                        <p><?=Loc::getMessage('PLAYER_DOB')?> <span><?=$arResult['PLAYER']['DOB']?></span></p>
                        <p><?=Loc::getMessage('PLAYER_NN')?> <span><?=$arResult['PLAYER']['NN']?></span></p>
                        <p><?=Loc::getMessage('PLAYER_CITIZEN')?> <span><?=$arResult['PLAYER']['CITIZEN']?></span></p>
                        <p><?=Loc::getMessage('PLAYER_ROLE')?> <span><?=$arResult['PLAYER']['ROLE']?></span></p>
                    </div>
                    <div class="players-list">
                        <h3><?=Loc::getMessage('MATCHES_LIST_TITLE', ['#PLAYER_FN#' => $arResult['PLAYER']['FN'], '#PLAYER_LN#' => $arResult['PLAYER']['LN']]);?></h3>
                        <table class="main-tbl">
                            <tr>
                                <th><?=Loc::getMessage('GM_DATE')?></th>
                                <th><?=Loc::getMessage('GM_TEAM')?></th>
                                <th><?=Loc::getMessage('GM_CITY')?></th>
                                <th><?=Loc::getMessage('GM_PLAYER_GOALS_H3')?></th>
                                <th><?=Loc::getMessage('GM_PLAYER_CARDS')?></th>
                            </tr>
                            <?foreach($arResult['PLAYER_GAMES'] as $gmId => $arrGame):?>
                            <tr>
                                <td><?=$arrGame['GM_DATE']?></td>
                                <td><?=$arrGame['GM_TEAM']?></td>
                                <td><?=$arrGame['GM_CITY']?></td>
                                <td><?=$arrGame['GM_PLAYER_GOALS']?></td>
                                <td>
                                    <?if($arrGame['GM_PLAYER_CARDS'] == 'Y'):?>
                                        <span class="card yell"></span>
                                    <?endif?>
                                    <?if($arrGame['GM_PLAYER_CARDS'] == 'Y2'):?>
                                        <span class="card yell"></span>
                                        <span class="card yell"></span>
                                    <?endif?>
                                    <?if($arrGame['GM_PLAYER_CARDS'] == 'YR'):?>
                                        <span class="card yell"></span>
                                        <span class="card red"></span>
                                    <?endif?>
                                    <?if($arrGame['GM_PLAYER_CARDS'] == 'R'):?>
                                        <span class="card red"></span>
                                    <?endif?>
                                    <?if(empty($arrGame['GM_PLAYER_CARDS'])):?>0<?endif?>
                                </td>
                            </tr>
                            <?endforeach?>
                        </table>
                    </div>
                    <div class="result-details">
                        <div class="result-detail">
                            <h3><?=Loc::getMessage('GM_PLAYER_GOALS_H3')?></h3>
                            <?if($arResult['PLAYER_GOALS_INFO'] == 0):?>
                                <p><?=Loc::getMessage('GM_PLAYER_NO_GOALS_TTL')?></p>
                            <?else:?>
                            <table class="main-tbl">
                                <tr>
                                    <th><?=Loc::getMessage('GM_DATE')?></th>
                                    <th><?=Loc::getMessage('GM_TEAM')?></th>
                                    <th><?=Loc::getMessage('GM_CITY')?></th>
                                </tr>
                            <?foreach($arResult['PLAYER_GOALS_INFO'] as $key=>$goalVal):?>
                                <tr>
                                    <td>
                                        <?=$arResult['PLAYER_GAMES'][$key]['GM_DATE']?>
                                    </td>
                                    <td>
                                        <?=$arResult['PLAYER_GAMES'][$key]['GM_TEAM']?>
                                    </td>
                                    <td><?=$goalVal?></td>
                                </tr>
                            <?endforeach?>
                                <tr>
                                    <td class="ttl-info" colspan="2"><?=Loc::getMessage('GM_PLAYER_GOALS_TTL')?></td>
                                    <td><span class="ttl-qnt"><?=$arResult['PLAYER_TOTAL_GOALS']?></span></td>
                                </tr>
                            </table>
                            <?endif?>
                        </div>
                        <div class="result-detail">
                            <h3><?=Loc::getMessage('GM_PLAYER_CARDS_H3')?></h3>
                            <?if($arResult['PLAYER_TOTAL_CARDS'] == 0):?>
                                <p><?=Loc::getMessage('GM_PLAYER_NO_CARDS_TTL')?></p>
                            <?else:?>
                            <table class="main-tbl">
                                <tr>
                                    <th><?=Loc::getMessage('GM_DATE')?></th>
                                    <th><?=Loc::getMessage('GM_PLAYER_CARDS')?></th>
                                </tr>
                                <?foreach($arResult['PLAYER_CARDS_INFO'] as $key=>$cardVal):?>
                                <tr>
                                    <td>
                                    <?=$arResult['PLAYER_GAMES'][$key]['GM_DATE']?>
                                    </td>
                                    <td>
                                        <?if($cardVal == 'Y'):?>
                                            <span class="card yell"></span>
                                        <?endif?>
                                        <?if($cardVal == 'Y2'):?>
                                            <span class="card yell"></span>
                                            <span class="card yell"></span>
                                        <?endif?>
                                        <?if($cardVal == 'YR'):?>
                                            <span class="card yell"></span>
                                            <span class="card red"></span>
                                        <?endif?>
                                        <?if($cardVal == 'R'):?>
                                            <span class="card red"></span>
                                        <?endif?>
                                    </td>
                                </tr>
                                <?endforeach?>
                                <tr>
                                    <td class="ttl-info"><?=Loc::getMessage('GM_PLAYER_CARDS_TTL')?></td>
                                    <td><span class="ttl-qnt"><?=$arResult['PLAYER_TOTAL_CARDS']?></span></td>
                                </tr>
                            </table>
                            <?endif?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="foot">
                <p><a href="/test_orm/basic/t5/query/"><?=Loc::getMessage('LIST_LINK_TTL')?></a></p>
            </div>
        </div>
    </body>
</html>