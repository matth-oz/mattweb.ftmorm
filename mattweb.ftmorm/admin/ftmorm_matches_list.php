<?php
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
use Bitrix\Main,
\Bitrix\Main\Entity,
Bitrix\Main\Loader,
Mattweb\Ftmorm;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");

if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Loader::includeModule("mattweb.ftmorm");

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

$pagen = (int) $request->get('PAGEN_1');
$sizen = (int) $request->get('SIZEN_1');

$pagenGm = (int) $request->get('PAGEN_2');
$sizenGm = (int) $request->get('SIZEN_2');

$ourTeamName = Ftmorm\GamesTable::OUR_TEAM_NAME;
$ourTeamCity = Ftmorm\GamesTable::OUR_TEAM_CITY;

$arGameDates = $arrRes = [];

// вычисляем правильные даты первого и последнего матча для заголовка при пагинации
$gamesRes = Ftmorm\GamesTable::getList([
    'select' => ['ID', 'CITY', 'GAME_DATE', 'TEAM_NAME'=>'TEAM.NAME', 'OWN', 'PLAYERS_ADDED'],
    'order' => ['ID' => 'DESC'],
    'filter' => ['PLAYERS_ADDED' => ''],
]);

/*$k=0;
while($arGmData = $gamesRes->fetch()){
    //dump($arGmData);
    $arGameDates[$k] = $arGmData['GAME_DATE']->format("d.m.Y");
    $k++;
}

usort($arGameDates, function($a, $b) {
    return strtotime($a) - strtotime($b);
});

$arMatchesFK = array_key_first($arGameDates);
$arMatchesLK = array_key_last($arGameDates);

//$APPLICATION->SetTitle(GetMessage('FTMORM_MATCHES_LIST_TITLE', ['#OUR_TEAM_NAME#' => $ourTeamName, '#FIRST_DATE#' => $arGameDates[$arMatchesFK], '#LAST_DATE#' => $arGameDates[$arMatchesLK],]));
*/
$APPLICATION->SetTitle(GetMessage('FTMORM_MATCHES_LIST_TITLE', ['#OUR_TEAM_NAME#' => $ourTeamName]));

$sTableID = "tbl_matches_entity";
$oSort = new CAdminSorting($sTableID, "GAME_ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arHeaders = array(
    array("id"=>"GM_NUM", "content"=>GetMessage("FTMORM_MATCHES_LIST_ROW_NUMBER"), "sort"=>"GM_NUM", "default"=>true),
    array("id"=>"GAME_ID", "content"=>GetMessage("FTMORM_MATCHES_LIST_MATCH_ID"), "sort"=>"GAME_ID", "default"=>true),
    array("id"=>"GM_GAME_DATE", "content"=>GetMessage('FTMORM_MATCHES_LIST_MATCH_DATE'), "sort"=>"GM_GAME_DATE", "default"=>true),
    array("id"=>"GM_CITY", "content"=>GetMessage('FTMORM_MATCHES_LIST_CITY'), "sort"=>"GM_CITY", "default"=>true),
    array("id"=>"GM_OPPONENT_NAME", "content"=>GetMessage('FTMORM_MATCHES_LIST_TM_NAME'), "sort"=>"GM_OPPONENT_NAME", "default"=>true),
    array("id"=>"GAME_SCORE", "content"=>GetMessage('FTMORM_MATCHES_LIST_GAME_SCORE'), "sort"=>"GAME_SCORE", "default"=>true),
    array("id"=>"GM_OWN", "content"=>GetMessage('FTMORM_MATCHES_LIST_AUTO_GOALS'), "sort"=>"GM_OWN", "default"=>true),
);

$lAdmin->AddHeaders($arHeaders);

$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());

if ($by !== 'GAME_ID') $by = 'GAME_ID';

$getListOrder = [
	$by => $order,
];

$LineUpsRes = Ftmorm\LineupsTable::getList([   
    'select' => ['GAME_ID', 'GOAL_SUMM', 'GM_'=>'GAME', 'GM_OPPONENT_NAME'=>'GAME.TEAM.NAME'],
    'order' => $getListOrder,
    'group' => ['GAME_ID'],
    'runtime' => [new Entity\ExpressionField('GOAL_SUMM', 'SUM(%s)', ['GOALS'])],
    'count_total' => true,
]);

$LineUpsRes = new CAdminResult($LineUpsRes, $sTableID);

$LineUpsRes->NavStart();

// build list
$lAdmin->NavText($LineUpsRes->GetNavPrint(GetMessage("PAGES")));

if($pagen <=1) $i = 1;
if($pagen > 1 && $sizen > 0) $i = ($pagen - 1) * $sizen + 1;

//dump($arGameDates);

while($arRes = $LineUpsRes->NavNext(true, "f_"))
{
    
    //dump($arRes);

    $arRes['GM_NUM'] = $i;
    $arRes['GM_OWN'] = intval($arRes['GM_OWN']);

    $opponentGoals = intval($arRes['GOAL_SUMM']) + intval($arRes['GM_OWN']);
    $ourCommandGoals = intval($arRes['GM_GOALS']);

    $gameId = intval($arRes['GAME_ID']);

    if($opponentGoals != $ourCommandGoals){
        $gmRes = ($opponentGoals > $ourCommandGoals) ? 'L' : 'W';
    }
    else{
        $gmRes = 'D'; // Draw - ничья
    }

    $score = '';

    if($arRes['GM_CITY'] != $ourTeamCity)
        $score .= $opponentGoals.' : '.$ourCommandGoals;
    else
        $score .= $ourCommandGoals.' : '.$opponentGoals;    

    $arRes['GAME_SCORE'] = $score;

    /*echo '<pre>';
    var_export($arRes);
    echo '<br/>';
    echo '<pre>';*/

    $row = $lAdmin->AddRow($f_ID, $arRes, false, $gmRes);

	$can_edit = true;

	$arActions = Array();

    $arActions[] = array(
		"ICON"=>"edit",
		"TEXT"=>GetMessage($can_edit ? "MAIN_ADMIN_MENU_EDIT" : "MAIN_ADMIN_MENU_VIEW"),
		"ACTION"=>$lAdmin->ActionRedirect("ftmorm_match_edit.php?GAME_ID=".$gameId)
	);

	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"ACTION" => "if(confirm('".GetMessageJS('FTMORM_ADMIN_DELETE_TEAM_CONFIRM')."')) ".
			$lAdmin->ActionRedirect("ftmorm_match_edit.php?action=delete&GAME_ID=".$gameId."&".bitrix_sessid_get())
	);

	$row->AddActions($arActions);
    $i++;
}


$gmTableId = "tbl_games_entity";
$oGmSort = new CAdminSorting($gmTableId, "ID", "desc");
$lGmAdmin = new CAdminList($gmTableId, $oGmSort);
$arGmHeaders = [
    //["id"=>"GM_NUM", "content"=>GetMessage("FTMORM_MATCHES_LIST_ROW_NUMBER"), "sort"=>"GM_NUM", "default"=>true],
    ["id"=>"ID", "content"=>GetMessage("FTMORM_MATCHES_LIST_ROW_NUMBER"), "sort"=>"ID", "default"=>true],
    ["id"=>"CITY", "content"=>GetMessage('FTMORM_MATCHES_LIST_CITY'), "sort"=>"CITY", "default"=>true],
    ["id"=>"GAME_DATE", "content"=>GetMessage('FTMORM_MATCHES_LIST_MATCH_DATE'), "sort"=>"GAME_DATE", "default"=>true],
    ["id"=>"TEAM_NAME", "content"=>GetMessage('FTMORM_MATCHES_LIST_TM_NAME'), "sort"=>"TEAM_NAME", "default"=>true],
    ["id"=>"OWN", "content"=>GetMessage('FTMORM_MATCHES_LIST_AUTO_GOALS'), "sort"=>"OWN", "default"=>true],
];

$lGmAdmin->AddHeaders($arGmHeaders);

$gmBy = mb_strtoupper($oGmSort->getField());
$gmOrder = mb_strtoupper($oGmSort->getOrder());

$gamesRes = new CAdminResult($gamesRes, $gmTableId);

$gamesRes->NavStart();

// build list
$lGmAdmin->NavText($gamesRes->GetNavPrint(GetMessage("PAGES")));

if($pagenGm <=1) $gi = 1;
if($pagenGm > 1 && $sizenGm > 0) $gi = ($pagenGm - 1) * $sizenGm + 1;

//dump($arFilledGames);
while($arGmRes = $gamesRes->NavNext(true, "f_")){

    if(intval($arGmRes['PLAYERS_ADDED']) == 0){

        $gameId = intval($arGmRes['ID']);

        $can_edit = true;
        $arGmActions = Array();

        $row = $lGmAdmin->AddRow($f_ID, $arGmRes, false);

        $arGmActions[] = array(
            "ICON"=>"edit",
            "TEXT"=>GetMessage($can_edit ? "MAIN_ADMIN_MENU_EDIT" : "MAIN_ADMIN_MENU_VIEW"),
            "ACTION"=>$lGmAdmin->ActionRedirect("ftmorm_match_edit.php?GAME_ID=".$gameId)
        );

        $arGmActions[] = array(
            "ICON"=>"delete",
            "TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
            "ACTION" => "if(confirm('".GetMessageJS('FTMORM_ADMIN_DELETE_TEAM_CONFIRM')."')) ".
                $lGmAdmin->ActionRedirect("ftmorm_match_edit.php?action=delete&GAME_ID=".$gameId."&".bitrix_sessid_get())
        );

        $row->AddActions($arGmActions);
    }

    $gi++;
}

// view
if ($lAdmin->isListMode() || $lGmAdmin->isListMode())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
}
else
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

    $lGmAdmin->CheckListMode();

    echo '<h3>'.GetMessage('FTMORM_MATCHES_LIST_WO_PLAYERS').'</h3>'; 

    $lGmAdmin->DisplayList();

	// menu
	$aMenu = [];
	$aMenu[] = [
		"TEXT" => GetMessage('FTMORM_TEAMS_ADD_TITLE'),
		"TITLE" => GetMessage('FTMORM_TEAMS_ADD_TITLE'),
		"LINK" => "ftmorm_match_edit.php?lang=" . LANGUAGE_ID,
		"ICON" => "btn_new",
	];

	$adminContextMenu = new CAdminContextMenu($aMenu);

    echo '<h3>'.GetMessage('FTMORM_MATCHES_LIST_W_PLAYERS').'</h3>'; 

	$adminContextMenu->Show();
}

$lAdmin->CheckListMode();

$lAdmin->DisplayList();


if ($lAdmin->isListMode())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}
else
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}