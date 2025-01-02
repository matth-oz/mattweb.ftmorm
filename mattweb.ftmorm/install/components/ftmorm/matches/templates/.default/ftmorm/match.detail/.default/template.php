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
<?if(!empty($arResult['ERRORS']) > 0):?>
    <div class="err-wrap">
        <?foreach($arResult['ERRORS'] as $err):?>
            <?=$err?><br />
        <?endforeach?>
    </div>
<?else:?>
    <div class="cont-detail">
        <div class="summary">
            <p><?=Loc::getMessage('MATCH_CITY')?> <span><?=$arResult['MATCH']['CITY']?></span></p>
            <p><?=Loc::getMessage('MATCH_DATE')?> <span><?=$arResult['MATCH']['GAME_DATE']?></span></p>
            <p><?=Loc::getMessage('MATCH_SCORE')?> <span class="score"><?=$arResult['MATCH_SCORE']?></span></p>
            <p><?=Loc::getMessage('MATCH_AUTOGOALS')?> <span <?if($arResult['MATCH']['AUTO_GOALS'] > 0):?>class="res-l"<?endif?>><?=$arResult['MATCH']['AUTO_GOALS']?></span></p>
            <p class="res-<?=strtolower($arResult['MATCH_RESULT_CODE'])?>"><?=$arResult['MATCH_RESULT_TXT']?></p>
        </div>
        <div class="players-list">
            <h3><?=Loc::getMessage('OPPONENT_TEAM_PLAYERS', ['#OPPONENT_TM_NAME#'=>$arrRes['MATCH']['TM_NAME']])?>:</h3>
            <table class="main-tbl">
                <tr>
                    <th><?=Loc::getMessage('PLAYER_NAME')?></th>
                    <th><?=Loc::getMessage('T_SHIRT_PLAYER_NAME')?></th>
                    <th><?=Loc::getMessage('PLAYER_ROLE')?></th>
                    <th><?=Loc::getMessage('TIME_ON_FIELD')?></th>
                    <th><?=Loc::getMessage('PLAYER_GOALS')?></th>
                    <th><?=Loc::getMessage('PLAYER_CARDS"')?></th>
                </tr>
                <tr>
                    <td colspan="6" class="hdr-row">
                        <?=Loc::getMessage('MAIN_TEAM')?></td>
                </tr>
                <?foreach($arResult['MATCH_PLAYERS']['BASE'] as $plId => $arrPlayer):?>
                    <tr>
                        <td><?=$arrPlayer['PLAYER_FN'].' '.$arrPlayer['PLAYER_LN']?></td>
                        <td><?=$arrPlayer['PLAYER_NN']?></td>
                        <td><?=$arrPlayer['PLAYER_ROLE']?></td>
                        <td><?=$arrPlayer['TIME_IN']?></td>
                        <td>
                            <?if(isset($arResult['MATCH_GOALS'][$plId])):?>
                                <?=$arResult['MATCH_GOALS'][$plId]?>
                            <?else:?>0<?endif?>
                        </td>
                        <td>
                            <?if(isset($arResult['MATCH_CARDS'][$plId])):?>
                                <?foreach($arResult['MATCH_CARDS'][$plId] as $card):?>
                                    <?if($card == 'Y'):?>
                                        <span class="card yell"></span>
                                    <?endif?>
                                    <?if($card == 'Y2'):?>
                                        <span class="card yell"></span>
                                        <span class="card yell"></span>
                                    <?endif?>
                                    <?if($card  == 'YR'):?>
                                        <span class="card yell"></span>
                                        <span class="card red"></span>
                                    <?endif?>
                                    <?if($card == 'R'):?>
                                        <span class="card red"></span>
                                    <?endif?>
                                <?endforeach?>
                            <?else:?>–<?endif?>
                        </td>
                    </tr>
                <?endforeach?>
                <tr>
                    <td colspan="6" class="hdr-row"><?=Loc::getMessage('RESERVE_TEAM_PLAYERS')?></td>
                </tr>
                <?foreach($arResult['MATCH_PLAYERS']['RESERVE'] as $plId => $arrPlayer):?>
                <tr>
                    <td><?=$arrPlayer['PLAYER_FN'].' '.$arrPlayer['PLAYER_LN']?></td>
                    <td><?=$arrPlayer['PLAYER_NN']?></td>
                    <td><?=$arrPlayer['PLAYER_ROLE']?></td>
                    <td><?if(!empty($arrPlayer['TIME_IN'])):?><?=$arrPlayer['TIME_IN']?><?else:?>–<?endif?></td>
                    <td>
                        <?if(isset($arResult['MATCH_GOALS'][$plId])):?>
                            <?=$arResult['MATCH_GOALS'][$plId]?>
                        <?else:?>0<?endif?>
                    </td>
                    <td>
                    <?if(isset($arResult['MATCH_CARDS'][$plId])):?>
                            <?foreach($arResult['MATCH_CARDS'][$plId] as $card):?>
                                <?if($card == 'Y'):?>
                                    <span class="card yell"></span>
                                <?endif?>
                                <?if($card == 'Y2'):?>
                                    <span class="card yell"></span>
                                    <span class="card yell"></span>
                                <?endif?>
                                <?if($card == 'R'):?>
                                    <span class="card red"></span>
                                <?endif?>
                            <?endforeach?>
                        <?else:?>–<?endif?>
                    </td>
                </tr>
                <?endforeach?>
            </table>
        </div>
    </div>
    <div class="foot">
        <p><a href="<?=$arParams['LIST_PAGE_PATH']?>"><?=Loc::getMessage('LIST_LINK_TTL')?></a></p>
    </div>
<?endif?>
