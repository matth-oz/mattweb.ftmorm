<?php
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
use Bitrix\Main,
Bitrix\Main\Localization\Loc,
Bitrix\Main\Loader,
Bitrix\Main\Type\DateTime,
Mattweb\Ftmorm;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");

if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Loader::includeModule("mattweb.ftmorm");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$APPLICATION->AddHeadScript('/local/modules/mattweb.ftmorm/admin/js/ftmorm_match_edit.js');
$APPLICATION->SetAdditionalCSS("/local/modules/mattweb.ftmorm/admin/css/ftmorm_match_edit.css");

$session = \Bitrix\Main\Application::getInstance()->getSession();

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

// init vars
// is it game create
$is_create_form = true;
// is it game edit
$is_update_form = false;
// were players already added
$is_players_exists = false;

$isEditMode = true;
$arErrors = [];
$localization = array();

$GAME_ID = (int)$request->get('GAME_ID');

$save = trim((string)$request->get('save'));
$apply = trim((string)$request->get('apply'));
$action = trim((string)$request->get('action'));
$requestMethod = $request->getRequestMethod();

if($GAME_ID > 0 && $requestMethod == 'GET'){ // edit game
    $gameFilter = [
        'select' => [
            'ID', 'TEAM_ID', 'TM_NAME'=>'TEAM.NAME', 'CITY', 'GOALS', 'GAME_DATE', 'OWN'
        ],
        'filter' => [
            '=ID' => $GAME_ID,
        ]        
    ];

    $arGameData = Ftmorm\GamesTable::getList($gameFilter)->fetch();

    if(!empty($arGameData)){
        $matchDataForSession = [];

        $matchData['GAME_DATA'] = $arGameData;
        $matchDataForSession['GAME_DATA'] = $arGameData;
        
        // save current data about the game to the session
        $session->set('GAME_DATA_OLD', $arGameData);

        $filter = array(
            'select' => array(
                'START', 'GAME_ID', 'PL_'=>'PLAYER', 'PLAYER_ID', 'TIME_IN', 'GOALS', 'CARDS' 	
            ),
            'filter' => array(
                '=GAME_ID' => $GAME_ID,
            )
        );
    
        $matchData['GAME_ID'] = $GAME_ID;
        
        // get data about players playing the game
        $rsMatchData = Ftmorm\LineupsTable::getList($filter);

        while($arMatchItem = $rsMatchData->fetch()){
            $matchData['ITEMS'][] = $arMatchItem;
            
            $sk = $arMatchItem['GAME_ID'].'_'.$arMatchItem['PL_ID'];
            $matchDataForSession['ITEMS'][$sk] = [
                'START' => $arMatchItem['START'],
                'GAME_ID' => $arMatchItem['GAME_ID'],
                'PLAYER_ID' => $arMatchItem['PL_ID'],
                'TIME_IN' => (int)$arMatchItem['TIME_IN'],
                'GOALS' => (int)$arMatchItem['GOALS'],
                'CARDS' => (int)$arMatchItem['CARDS'],            
            ];
        }

        if (!empty($matchData['GAME_DATA']))
        {
            $is_update_form = true;
            $is_create_form = false;
        }
        
        $is_players_exists = !empty($matchData['ITEMS']);
        
        // if players exists save their data to the session
        if($is_players_exists){
            $session->set('ITEMS_OLD', $matchDataForSession['ITEMS']);
        }
    }
    else{
        $arErrors[] = 'Матч с id='.$GAME_ID.' не найден';
    } 
}

// form
$aTabs = [
    [
        'DIV' => 'edit1',
		'TAB' => GetMessage('FTMORM_ADMIN_MATCH_TITLE'),
		'TITLE' => GetMessage('FTMORM_ADMIN_MATCH_TITLE')
    ]
];

if($is_update_form){
    $aTabs[] =[
        'DIV' => 'edit2',
        'TAB' => GetMessage('FTMORM_ADMIN_MATCH_DETAIL_TITLE'),
        'TITLE' => GetMessage('FTMORM_ADMIN_MATCH_DETAIL_TITLE')
    ];
}


$tabControl = new CAdminTabControl('tabControl', $aTabs);


// default values for create form / page title
if ($is_create_form){
	$match = array_fill_keys(array('GAME_ID'), '');
	$APPLICATION->SetTitle(GetMessage('FTMORM_ADMIN_MATCH_EDIT_PAGE_TITLE_NEW'));
}
else{
    $APPLICATION->SetTitle(GetMessage('FTMORM_ADMIN_MATCH_EDIT_PAGE_TITLE_EDIT', array('#NAME#' => $matchData['GAME_ID'])));

    $rsMatch = Ftmorm\LineupsTable::getList([
        'select' => array('START', 'GAME_ID', 'PLAYER_ID', 'TIME_IN', 'GOALS', 'CARDS'),
        'filter' => array('=GAME_ID' => $GAME_ID),
        'count_total' => true,
    ]);

    $matchData['ROWS_COUNT'] = $rsMatch->getCount();
}


// delete action
if ($is_update_form && $action === 'delete' && check_bitrix_sessid()){

    $totalResult = false;   

    foreach($matchData['ITEMS'] as $matchDataItem){
        $result = Ftmorm\LineupsTable::delete(
            ['GAME_ID' => $matchDataItem['GAME_ID'], 'PLAYER_ID' => $matchDataItem['PLAYER_ID']]
        );
        
        if (!$result->isSuccess()){
            $arErrors[] = $result->getErrorMessages();
        }
    }

    if(empty($arErrors)){
        $gmResult = Ftmorm\GamesTable::delete($matchData['GAME_ID']);

        if (!$gmResult->isSuccess()){
            $arErrors[] = $gmResult->getErrorMessages();
        }
    }

    if(count($arErrors) == 0){
        \LocalRedirect('ftmorm_matches_list.php?lang='.LANGUAGE_ID);
    }

}

// save action
if (($save != '' || $apply != '') && $requestMethod == 'POST' && check_bitrix_sessid())
{
   
    $arGameDataOld = $session->get('GAME_DATA_OLD');
    $arPlayersOld = $session->get('ITEMS_OLD');   

/**
 * 1 - добавляем информацию о матче ($_POST['TEAM_ID'])
 * 2 - обновляем информацию о матче, когда нет игроков ($_POST['TEAM_ID'] && $_POST['GAME_ID'])
 * 3 - добавляем информацию об игроках ($_POST['TEAM_ID'] && $_POST['GAME_ID'] && $_POST['match_detail_mode'] == 'add' && !empty($_POST['PLAYER_ID']['n1']))
 * 4 - обновляем информацию об игроках ($_POST['TEAM_ID'] && $_POST['GAME_ID'] && $_POST['match_detail_mode'] == 'edit')
 * 4.1 - при обновлении может быть добавлены новые игроки (!empty($_POST['PLAYER_ID']['n1']))
 * 4.2 - при обновлении могут быть удалены существующие игроки (isset($_POST['REMOVE_GM_PL']))
 */

    // проверям параметры - есть ли информация об игроках
    
    $teamID = intval($request->getPost('TEAM_ID'));
    if($teamID > 0){
        
        $gameId = intval($request->getPost('GAME_ID'));        
        $arGameData = [];

        $arGameData['TEAM_ID'] = $teamID;
        $arGameData['CITY'] = trim($request->getPost('CITY'));
        $arGameData['GOALS'] = intval($request->getPost('GOALS'));
        $arGameData['GAME_DATE'] = new DateTime(trim($request->getPost('GAME_DATE')));
        $arGameData['OWN'] = intval($request->getPost('OWN'));  
        

        if($gameId <= 0){ // пункт 1 
            // добавляем данные о матче
            $resAdd = Ftmorm\GamesTable::add($arGameData);
            if(!$resAdd->isSuccess())
                $arErrors[] = $resAdd->getErrorMessages();

            $gameId = $resAdd->getPrimary();
        }
        else{ // пункт 2

            // проверяем нужно ли обновлять данные о матче
            $arGameDataOld['GAME_DATE'] = $arGameDataOld['GAME_DATE']->format('d.m.Y H:i:s');

            if($arGameData['TEAM_ID'] == $arGameDataOld['TEAM_ID']) unset($arGameData['TEAM_ID']);
            if($arGameData['CITY'] == $arGameDataOld['CITY']) unset($arGameData['CITY']);
            if($arGameData['GOALS'] == $arGameDataOld['GOALS']) unset($arGameData['GOALS']);
            if($arGameData['GAME_DATE'] == $arGameDataOld['GAME_DATE']) unset($arGameData['GAME_DATE']);
            if($arGameData['OWN'] == $arGameDataOld['OWN']) unset($arGameData['OWN']);

            if(!empty($arGameData)){
                $arGameData['GAME_DATE'] = new DateTime(trim($request->getPost('GAME_DATE')));
                $resUpd = Ftmorm\GamesTable::update($gameId, $arGameData);
                if(!$resUpd->isSuccess())
                    $arErrors[] = $resUpd->getErrorMessages();
            }


            if($request->getPost('match_detail_mode')){
   
                $requestMode = $request->getPost('match_detail_mode');              
                
                // для сохранения в session
                $arLineUpsAdded = [];

                switch ($requestMode){
                    case 'add': // пункт 3
                        $arPlayerId = $request->getPost('PLAYER_ID');
                        $arStart = $request->getPost('START');
                        $arTimeIn = $request->getPost('TIME_IN');
                        $arGoals = $request->getPost('GOALS');
                        $arCards = $request->getPost('CARDS');

                        if(!empty($arPlayerId['n1'])){
                            $arAddedRows = [];
                            foreach($arPlayerId as $key => $val){
                                $arLineUpsNewFields = [];

                                $itemKey = $gameId.'_'.$arPlayerId[$key];

                                if(!in_array($itemKey, $arAddedRows)){
                                    $arLineUpsNewFields['GAME_ID'] = $gameId;
                                    $arLineUpsNewFields['PLAYER_ID'] = $arPlayerId[$key];
                                    $arLineUpsNewFields['TIME_IN'] = $arTimeIn[$key];
                                    $arLineUpsNewFields['GOALS'] = $arGoals[$key];
                                    $arLineUpsNewFields['CARDS'] = strval($arCards[$key]);
                                    $arLineUpsNewFields['START'] = $arStart[$key];
                                
                                    $resAdd = Ftmorm\LineupsTable::add($arLineUpsNewFields);
                                    if($resAdd->isSuccess()){
                                        $arLineUpsAdded[] = $arLineUpsNewFields;
                                        $newPlayersAdded = true;
                                        $arAddedRows[] = $itemKey;
                                    }
                                    else{
                                        $arErrors[] = $resAdd->getErrorMessages();
                                    } 
                                }
                            }                        
                        }
                        break;
                    case 'edit': // пункт 4
                        
                        // пункт 4.2
                        $arRemoveItems = [];
                        if($request->getPost('REMOVE_GM_PL')){
                            $arRemoveItems = $request->getPost('REMOVE_GM_PL');
                            
                            foreach($arRemoveItems as $removeItemPk){
                                $arRemoveItemPk = explode('_', $removeItemPk);

                                $result = Ftmorm\LineupsTable::delete(
                                    ['GAME_ID' => $arRemoveItemPk[0], 'PLAYER_ID' => $arRemoveItemPk[1]]
                                );                                
                            }
                        }

                        $arPlayerId = $request->getPost('PLAYER_ID');
                        $arStart = $request->getPost('START');
                        $arTimeIn = $request->getPost('TIME_IN');
                        $arGoals = $request->getPost('GOALS');
                        $arCards = $request->getPost('CARDS');

                        if(array_key_exists('n1', $arPlayerId) && !empty($arPlayerId['n1'])){ // пункт 4.1
                            $arKeys = array_keys($arPlayerId);
                            $arFilteredKeys = array_filter($arKeys, function($k){return strpos($k, 'n') !== false;});
                            
                            if(count($arFilteredKeys) > 0){
                                $arAddedRows = [];
                                foreach($arFilteredKeys as $recKeyVal){                                    

                                    $itemKey = $gameId.'_'.$arPlayerId[$recKeyVal];
                                    
                                    if(array_key_exists($itemKey, $arPlayersOld) && !in_array($itemKey, $arRemoveItems)) continue;

                                    if(!in_array($itemKey, $arAddedRows)){
                                        $arLineUpsNewFields = [];

                                        $arLineUpsNewFields['GAME_ID'] = $gameId;
                                        $arLineUpsNewFields['PLAYER_ID'] = $arPlayerId[$recKeyVal];
                                        unset($arPlayerId[$recKeyVal]);
                                        $arLineUpsNewFields['TIME_IN'] = $arTimeIn[$recKeyVal];
                                        unset($arTimeIn[$recKeyVal]);
                                        $arLineUpsNewFields['GOALS'] = $arGoals[$recKeyVal];
                                        unset($arTimeIn[$recKeyVal]);
                                        $arLineUpsNewFields['CARDS'] = strval($arCards[$recKeyVal]);
                                        unset($arCards[$recKeyVal]);
                                        $arLineUpsNewFields['START'] = $arStart[$recKeyVal];
                                        unset($arStart[$recKeyVal]);

                                        $resAdd = Ftmorm\LineupsTable::add($arLineUpsNewFields);
                                        if($resAdd->isSuccess()){
                                            $arLineUpsAdded[] = $arLineUpsNewFields;
                                            $arAddedRows[] = $itemKey;
                                        }
                                        else{
                                            $arErrors[] = $resAdd->getErrorMessages();
                                        }
                                    }
                                }
                            }
                        }
                        else{ // пункт 4
                            $arrPostItems = [];
                            foreach($arPlayerId as $key => $playerId){
                                $nk = $gameId.'_'.$playerId;

                                if($key == 'n1' || in_array($nk, $arRemoveItems)) continue;                                

                                if(!array_key_exists($nk, $arRemoveItems)){                                
                                    $arrPostItems[$nk]['PLAYER_ID'] = $playerId;
                                    $arrPostItems[$nk]['GAME_ID'] = $gameId;
                                    $arrPostItems[$nk]['START'] = $arStart[$key];
                                    $arrPostItems[$nk]['TIME_IN'] = $arTimeIn[$key];
                                    $arrPostItems[$nk]['GOALS'] = $arGoals[$key];
                                    $arrPostItems[$nk]['CARDS'] = $arCards[$key];
                                }
                            }

                            if(!empty($arPlayersOld)){

                                foreach($arrPostItems as $key => $arrPostItem){
                                    $arDiff = array_diff_assoc($arrPostItem, $arPlayersOld[$key]);

                                    if(!empty($arDiff)){
                                        $arPlayerData = [];
                                        $pPk = ['GAME_ID' => $arrPostItem['GAME_ID'], 'PLAYER_ID' => $arrPostItem['PLAYER_ID']];
                                    
                                        foreach($arDiff as $k => $v){                                            
                                            $arPlayerData[$k] = $v;
                                        } 
                                        
                                        $resUpd = Ftmorm\LineupsTable::update($pPk, $arPlayerData);
                                        if(!$resUpd->isSuccess())
                                            $arErrors[] = $resUpd->getErrorMessages();
                                    }
                                    
                                }

                            }
                        }

                        break;
                }
                
                if(count($arErrors) <= 0 && $newPlayersAdded){
                    $arGamePlayersAdded = ['PLAYERS_ADDED' => 1];
                    $resUpd = Ftmorm\GamesTable::update($gameId, $arGamePlayersAdded);
                    if(!$resUpd->isSuccess())
                        $arErrors[] = $resUpd->getErrorMessages();
                }
            }
        }

        if(empty($arErrors)){

            // remove from session
            $session->remove('GAME_DATA_OLD');
            $session->remove('ITEMS_OLD');

            if($save != ''){
                \LocalRedirect('ftmorm_matches_list.php?lang='.LANGUAGE_ID);
            } 
            else{
                $gameId = isset($gameId['ID']) ? $gameId['ID'] : $gameId;
                \LocalRedirect('ftmorm_match_edit.php?GAME_ID='.$gameId.'&lang='.LANGUAGE_ID);
            }           
        }

    }else{
        $arErrors[] = Loc::GetMessage('FTMORM_ADMIN_TEAM_ID_EMPTY');
    }
}

// view
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

// menu
$aMenu = [
    [
        'TEXT'	=> GetMessage('FTMORM_ADMIN_ROWS_RETURN_TO_LIST_BUTTON'),
		'TITLE'	=> GetMessage('FTMORM_ADMIN_ROWS_RETURN_TO_LIST_BUTTON'),
		'LINK'	=> 'ftmorm_matches_list.php?lang='.LANGUAGE_ID,
		'ICON'	=> 'btn_list',
    ]
];

if($GAME_ID > 0){
    $aMenu[] = [
        'SEPARATOR' => 'Y'
    ];

    $aMenu[] =[
        'TEXT' => GetMessage('FTMORM_ADMIN_FORM_ADD_NEW_MATCH_BUTTON'),
        'TITLE' => GetMessage('FTMORM_ADMIN_FORM_ADD_NEW_MATCH_BUTTON'),
        'LINK' => 'ftmorm_match_edit.php?lang=' . LANGUAGE_ID,
        'ICON' => 'btn_new',
    ];

    $aMenu[] = [
		'TEXT' => GetMessage('FTMORM_ADMIN_FORM_DELETE_MATCH_BUTTON'),
		'TITLE' => GetMessage('FTMORM_ADMIN_FORM_DELETE_MATCH_BUTTON'),
		'LINK' => "javascript:if(confirm('" . Loc::GetMessage('FTMORM_ADMIN_GAME_ENTITY_DELETE_CONFIRM') . "'))window.location='ftmorm_match_edit.php?GAME_ID=" . $GAME_ID . '&action=delete&lang=' . LANGUAGE_ID . '&' . bitrix_sessid_get() . "';",
		'ICON' => 'btn_delete',
	];
}

$adminContextMenu = new CAdminContextMenu($aMenu);
$adminContextMenu->Show();

if (!empty($arErrors))
{
	CAdminMessage::ShowMessage(join("\n", $arErrors));
}
?>

<form name="form1" id="match_form" method="POST" action="<?=$APPLICATION->GetCurPage()?>">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="GAME_ID" value="<?= htmlspecialcharsbx($matchData['GAME_ID'])?>">
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID?>">
	<?
	$tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>
    <tr>       
        <td width="40%"><strong><?=GetMessage('FTMORM_ADMIN_GAME_ENTITY_TEAM_ID_FIELD')?></strong></td>
        <td>
            <?if (!$isEditMode):?>
                <?=htmlspecialcharsEx($matchData['GAME_DATA']['TEAM_ID'])?>
            <?else:?>

            <?if($is_players_exists):?>
                <input type="hidden" name="TEAM_ID" value="<?= htmlspecialcharsbx($matchData['GAME_DATA']['TEAM_ID'])?>">
            <?else:?>
                <input type="text" name="TEAM_ID" id="F_TEAM_ID" size="3" value="<?= htmlspecialcharsbx($matchData['GAME_DATA']['TEAM_ID'])?>">
                <input type="button" title="<?=Loc::GetMessage('FTMORM_ADMIN_GAME_ENTITY_TEAM_ID_CHOOSE')?>" onclick="jsUtils.OpenWindow('/bitrix/admin/ftmorm_team_search.php?fid=F_TEAM_ID&scl=j-team-name', 900, 700);" name="F_TEAM_ID_BTN" id="F_TEAM_ID_BTN" value="..." data-propid="n0">
            <?endif?>
        <?endif;?>
        <span class="j-team-name" ><?=$matchData['GAME_DATA']['TM_NAME']?></span>
        </td>
    </tr>

    <tr>       
        <td width="40%"><strong><?=GetMessage('FTMORM_ADMIN_GAME_ENTITY_CITY_FIELD')?></strong></td>
        <td>
        <?if (!$isEditMode):?>
            <?=htmlspecialcharsEx($matchData['GAME_DATA']['CITY'])?>
        <?else:?>
            <input type="text" name="CITY" size="30" value="<?= htmlspecialcharsbx($matchData['GAME_DATA']['CITY'])?>">
        <?endif;?>
        </td>
    </tr>

    <tr>       
        <td width="40%"><strong><?=GetMessage('FTMORM_ADMIN_GAME_ENTITY_GOALS_FIELD')?></strong></td>
        <td>
        <?if (!$isEditMode):?>
            <?=htmlspecialcharsEx($matchData['GAME_DATA']['GOALS'])?>
        <?else:?>
            <input type="text" name="GOALS" size="30" value="<?= htmlspecialcharsbx($matchData['GAME_DATA']['GOALS'])?>">
        <?endif;?>
        </td>
    </tr>

    <tr>       
        <td width="40%"><strong><?=GetMessage('FTMORM_ADMIN_GAME_ENTITY_GAME_DATE_FIELD')?></strong></td>
        <td>
            <?
            $matchDate = '';
            if(!empty($matchData['GAME_DATA']['GAME_DATE'])){
                $matchDate = htmlspecialcharsbx($matchData['GAME_DATA']['GAME_DATE']->format('d.m.Y H:i:s'));
            }
            ?>
        <?if (!$isEditMode):?>            
            <?=$matchDate?>
        <?else:?>
            <?=CalendarDate("GAME_DATE", $matchDate, "form1", "27")?>            
        <?endif;?>
        </td>
    </tr>

    <tr>       
        <td width="40%"><strong><?=GetMessage('FTMORM_ADMIN_GAME_ENTITY_OWN_FIELD')?></strong></td>
        <td>
        <?if (!$isEditMode):?>
            <?=htmlspecialcharsEx($matchData['GAME_DATA']['OWN'])?>
        <?else:?>
            <input type="text" name="OWN" size="30" value="<?= htmlspecialcharsbx($matchData['GAME_DATA']['OWN'])?>">
        <?endif;?>
        </td>
    </tr>

    <?   
    if($is_update_form){
	$tabControl->BeginNextTab();
	?>
    <tr>
        <td>  
            <table class="match-details j-match-details">
                <tr>
                    <th><?=Loc::GetMessage('TH_PLAYER_ID_TITLE')?></th>
                    <th><?=Loc::GetMessage('TH_START_TITLE')?></th>
                    <th><?=Loc::GetMessage('TH_TIME_IN_TITLE')?></th>
                    <th><?=Loc::GetMessage('TH_GOALS_TITLE')?></th>
                    <th><?=Loc::GetMessage('TH_CARDS_TITLE')?></th>
                    <?if(isset($matchData['ITEMS']) && $matchData['ROWS_COUNT'] > 0):?>
                    <th><?=Loc::GetMessage('TH_REMOVE_ROW_TITLE')?></th>
                    <?endif?>
                </tr>
            <?if(isset($matchData['ITEMS']) && $matchData['ROWS_COUNT'] > 0):?>
                <input type="hidden" name="match_detail_mode" value="edit" />
                <?
                $rowNum = 1;
                foreach($matchData['ITEMS'] as $matchDataItem):?>
                    <tr>
                        <td style="vertical-align: middle; padding: 2px 5px;">
                            <input type="hidden" name="PLAYER_ID[<?=$rowNum?>]" value="<?=$matchDataItem['PLAYER_ID']?>" />
                            <span style="display: block;"><?=$matchDataItem['PL_FIRST_NAME'].' '.$matchDataItem['PL_LAST_NAME']?></span>
                        </td>
                        <td style="vertical-align: top; padding: 5px;">
                            <select name="START[<?=$rowNum?>]">
                                <option value="0">--</option>
                                <option value="B"<?if($matchDataItem['START'] == 'B'):?> selected="selected"<?endif?>><?=Loc::GetMessage('BASE_PLAYER_TITLE')?></option> <!--regular player-->
                                <option value="S"<?if($matchDataItem['START'] == 'S'):?> selected="selected"<?endif?>><?=Loc::GetMessage('SPARE_PLAYER_TITLE')?></option> <!--spare player-->
                            </select>
                        </td>
                        <td style="vertical-align: top; padding: 5px;"><input type="text" size="5" name="TIME_IN[<?=$rowNum?>]" value="<?=$matchDataItem['TIME_IN']?>" /></td>
                        <td style="vertical-align: top; padding: 5px;"><input type="text" size="5" name="GOALS[<?=$rowNum?>]" value="<?=$matchDataItem['GOALS']?>" /></td>
                        <td style="vertical-align: top; padding: 5px;">
                            <select name="CARDS[<?=$rowNum?>]">
                                <option value="0">--</option>
                                <option value="Y"<?if($matchDataItem['CARDS'] == 'Y'):?> selected="selected"<?endif?>><?=Loc::GetMessage('CARD_Y_TITLE')?></option>
                                <option value="Y2"<?if($matchDataItem['CARDS'] == 'Y2'):?> selected="selected"<?endif?>><?=Loc::GetMessage('CARD_Y2_TITLE')?></option>
                                <option value="R"<?if($matchDataItem['CARDS'] == 'R'):?> selected="selected"<?endif?>><?=Loc::GetMessage('CARD_R_TITLE')?></option>
                            </select>                        
                        </td>
                        <td style="vertical-align: middle; padding: 5px;">
                            <? 
                            $curRowItemId = $matchData['GAME_ID'].'_'.$matchDataItem['PLAYER_ID'];
                            echo InputType("checkbox", "REMOVE_GM_PL[$rowNum]", $curRowItemId, '');
                            ?>
                        </td>
                    </tr>                   
                <?
                $rowNum++;
                endforeach?>
                <?if(count($matchData['ITEMS']) < 25):?>
                    <tr>
                        <td style="vertical-align: middle; padding: 2px 5px;">
                            <input type="text" name="PLAYER_ID[n1]" id="PLAYER_ID[n1]" value="" />
                            <input type="button" title="<?=Loc::GetMessage('FTMORM_ADMIN_PLAYER_ENTITY_CHOOSE')?>" onclick="jsUtils.OpenWindow('/bitrix/admin/ftmorm_player_search.php?fid=PLAYER_ID[n1]&sid=PLAYER_NAME[n1]', 900, 700);" name="PLAYER_ID_n1_BTN" id="PLAYER_ID_n1_BTN" value="..." data-propid="n0">
                            <span id="PLAYER_NAME[n1]" style="display: block;"></span>
                        </td>
                        <td style="vertical-align: top; padding: 5px;">
                            <select name="START[n1]">
                                <option value="0">--</option>
                                <option value="B"><?=Loc::GetMessage('BASE_PLAYER_TITLE')?></option> <!--regular player-->
                                <option value="S"><?=Loc::GetMessage('SPARE_PLAYER_TITLE')?></option> <!--spare player-->
                            </select>
                        </td>
                        <td style="vertical-align: top; padding: 5px;"><input type="text" size="5" name="TIME_IN[n1]" value="" /></td>
                        <td style="vertical-align: top; padding: 5px;"><input type="text" size="5" name="GOALS[n1]" value="" /></td>
                        <td style="vertical-align: top; padding: 5px;">
                            <select name="CARDS[n1]">
                                <option value="">--</option>
                                <option value="Y"><?=Loc::GetMessage('CARD_Y_TITLE')?></option>
                                <option value="Y2"><?=Loc::GetMessage('CARD_Y2_TITLE')?></option>
                                <option value="R"><?=Loc::GetMessage('CARD_R_TITLE')?></option>
                            </select>                        
                        </td>
                    </tr>
                <?endif?>
            <?else:?>
                <input type="hidden" name="match_detail_mode" value="add" />
                <tr>
                    <td style="vertical-align: middle; padding: 2px 5px;">
                        <input type="text" name="PLAYER_ID[n1]" id="PLAYER_ID[n1]" value="" />
                        <input type="button" title="<?=Loc::GetMessage('FTMORM_ADMIN_PLAYER_ENTITY_CHOOSE')?>" onclick="jsUtils.OpenWindow('/bitrix/admin/ftmorm_player_search.php?fid=PLAYER_ID[n1]&sid=PLAYER_NAME[n1]', 900, 700);" name="PLAYER_ID_n1_BTN" id="PLAYER_ID_n1_BTN" value="..." data-propid="n0">
                        <span id="PLAYER_NAME[n1]" style="display: block;"></span>
                    </td>
                    <td style="vertical-align: top; padding: 5px;">
                        <select name="START[n1]">
                            <option value="0">--</option>
                            <option value="B"><?=Loc::GetMessage('BASE_PLAYER_TITLE')?></option> <!--regular player-->
                            <option value="S"><?=Loc::GetMessage('SPARE_PLAYER_TITLE')?></option> <!--spare player-->
                        </select>
                    </td>
                    <td style="vertical-align: top; padding: 5px;"><input type="text" size="5" name="TIME_IN[n1]" value="" /></td>
                    <td style="vertical-align: top; padding: 5px;"><input type="text" size="5" name="GOALS[n1]" value="" /></td>
                    <td style="vertical-align: top; padding: 5px;">
                        <select name="CARDS[n1]">
                            <option value="">--</option>
                            <option value="Y"><?=Loc::GetMessage('CARD_Y_TITLE')?></option>
                            <option value="Y2"><?=Loc::GetMessage('CARD_Y2_TITLE')?></option>
                            <option value="R"><?=Loc::GetMessage('CARD_R_TITLE')?></option>
                        </select>                        
                    </td>
                </tr>
            <?endif?>
            </table>
            
            <?if($matchData['ROWS_COUNT'] == 0 || (isset($matchData['ITEMS']) && count($matchData['ITEMS']) < 24)):?>
            <div class="add-btn-wrap">
                <input 
                    class="adm-btn-big j-add-btn" 
                    type="button" 
                    value="<?=Loc::GetMessage('FTMORM_ADMIN_TEAM_MORE_TITLE')?>" 
                    title="<?=Loc::GetMessage('FTMORM_ADMIN_TEAM_PROP_ADD')?>">
            </div>
            <?endif?>            
        </td>
    </tr>
    <?}?>
    <?
	$tabControl->Buttons(array('disabled' => !$isEditMode, 'back_url' => 'ftmorm_matches_list.php?lang='.LANGUAGE_ID));
	$tabControl->End();
	?>
</form>
<script>
window.onload = function(){
 let matchEditFrm = new matchEditForm({
    rootElementId: 'match_form',
    addRowBtnSelector: '.j-add-btn',
    detailsTableSelector: '.j-match-details tbody',
    playersRowsCount: <?=$matchData['ROWS_COUNT']?>,
    MESSAGES:{
        FTMORM_ADMIN_PLAYER_ENTITY_CHOOSE: '<?=Loc::GetMessage('FTMORM_ADMIN_PLAYER_ENTITY_CHOOSE')?>',
        BASE_PLAYER_TITLE: '<?=Loc::GetMessage('BASE_PLAYER_TITLE')?>',
        SPARE_PLAYER_TITLE: '<?=Loc::GetMessage('SPARE_PLAYER_TITLE')?>',
        CARD_Y_TITLE: '<?=Loc::GetMessage('CARD_Y_TITLE')?>',
        CARD_Y2_TITLE: '<?=Loc::GetMessage('CARD_Y2_TITLE')?>',
        CARD_R_TITLE: '<?=Loc::GetMessage('CARD_R_TITLE')?>',
    }

 });
}
</script>