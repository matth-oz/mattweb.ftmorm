<?php
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
use Bitrix\Main,
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


// CJSCore::Init(["jquery"]);
$APPLICATION->SetTitle(GetMessage('FTMORM_PLAYERS_LIST_TITLE'));

$sTableID = "tbl_players_entity";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arHeaders = array(
    array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
    array("id"=>"FIRST_NAME", "content"=>GetMessage('PLAYERS_ENTITY_FIRST_NAME_FIELD'), "sort"=>"FIRST_NAME", "default"=>true),
    array("id"=>"LAST_NAME", "content"=>GetMessage('PLAYERS_ENTITY_LAST_NAME_FIELD'), "sort"=>"LAST_NAME", "default"=>true),
    array("id"=>"NICKNAME", "content"=>GetMessage('PLAYERS_ENTITY_NICKNAME_FIELD'), "sort"=>"NICKNAME", "default"=>true),
    array("id"=>"CITIZENSHIP", "content"=>GetMessage('PLAYERS_ENTITY_CITIZENSHIP_FIELD'), "sort"=>"CITIZENSHIP", "default"=>true),
    array("id"=>"DOB", "content"=>GetMessage('PLAYERS_ENTITY_DOB_FIELD'), "sort"=>"DOB", "default"=>true),
    array("id"=>"ROLE", "content"=>GetMessage('PLAYERS_ENTITY_ROLE_FIELD'), "sort"=>"ROLE", "default"=>true),
);

$lAdmin->AddHeaders($arHeaders);

$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$getListOrder = [
	$by => $order,
];
if ($by !== 'ID')
{
	$getListOrder['ID'] = 'ASC';
}

// select data
$rsData = Ftmorm\PlayersTable::getList([
	"select" => $lAdmin->GetVisibleHeaderColumns(),
	"order" => $getListOrder,
]);

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// build list
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PAGES")));

while($arRes = $rsData->NavNext(true, "f_"))
{    
	$arRes['ROLE'] = ServiceActions::getTeamPlayerType($arRes['ROLE']);
	
	$row = $lAdmin->AddRow($f_ID, $arRes);

	$can_edit = true;

	$arActions = Array();

    $arActions[] = array(
		"ICON"=>"edit",
		"TEXT"=>GetMessage($can_edit ? "MAIN_ADMIN_MENU_EDIT" : "MAIN_ADMIN_MENU_VIEW"),
		"ACTION"=>$lAdmin->ActionRedirect("ftmorm_player_edit.php?ID=".$f_ID)
	);

	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"ACTION" => "if(confirm('".GetMessageJS('FTMORM_ADMIN_DELETE_PLAYER_CONFIRM')."')) ".
			$lAdmin->ActionRedirect("ftmorm_player_edit.php?action=delete&ID=".$f_ID.'&'.bitrix_sessid_get())
	);

	$row->AddActions($arActions);
}

// view
if ($lAdmin->isListMode())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
}
else
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	// menu
	$aMenu = [];
	$aMenu[] = [
		"TEXT" => GetMessage('FTMORM_PLAYERS_ADD_TITLE'),
		"TITLE" => GetMessage('FTMORM_PLAYERS_ADD_TITLE'),
		"LINK" => "ftmorm_player_edit.php?lang=" . LANGUAGE_ID,
		"ICON" => "btn_new",
	];

	$adminContextMenu = new CAdminContextMenu($aMenu);
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



















