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
//$APPLICATION->SetTitle(GetMessage('FTMORM_TEAMS_LIST_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

// form
$aTabs = array(
	array(
		'DIV' => 'edit1',
		'TAB' => GetMessage('FTMORM_ADMIN_TEAM_TITLE'),
		'TITLE' => GetMessage('FTMORM_ADMIN_TEAM_TITLE')
	)
);
$tabControl = new CAdminTabControl('tabControl', $aTabs);

// init vars
$is_create_form = true;
$is_update_form = false;
$isEditMode = true;
$errors = array();
$localization = array();

/*$currentRights = array();
$currentRightsName = array();
$access = new \CAccess;*/

$ID = (int)$request->get('ID');
$save = trim((string)$request->get('save'));
$apply = trim((string)$request->get('apply'));
$action = trim((string)$request->get('action'));
$requestMethod = $request->getRequestMethod();

if($ID > 0){
    $filter = array(
        'select' => array(
            'ID', 'NAME', 'FOUND_YEAR'
        ),
        'filter' => array(
            '=ID' => $ID
        )
    );

    $team = Ftmorm\TeamsTable::getList($filter)->fetch();
    if (!empty($team))
	{
		$is_update_form = true;
		$is_create_form = false;
	}
}

// default values for create form / page title
if ($is_create_form){
	$team = array_fill_keys(array('ID', 'NAME', 'FOUND_YEAR'), '');
	$APPLICATION->SetTitle(GetMessage('FTMORM_ADMIN_TEAM_EDIT_PAGE_TITLE_NEW'));
}
else{
    $APPLICATION->SetTitle(GetMessage('FTMORM_ADMIN_TEAM_EDIT_PAGE_TITLE_EDIT', array('#NAME#' => $team['NAME'])));
    $rsTeam = Ftmorm\TeamsTable::getList([
        'select' => array('ID', 'NAME', 'FOUND_YEAR'),
        'filter' => array('=ID' => $ID),
        'count_total' => true,
    ]);

    $team['ROWS_COUNT'] = $rsTeam->getCount();
}

// delete action
if ($is_update_form && $action === 'delete' && check_bitrix_sessid()){
    $result = Ftmorm\TeamsTable::delete($team['ID']);
	if ($result->isSuccess())
	{
		\LocalRedirect('ftmorm_teams_list.php?lang='.LANGUAGE_ID);
	}
	else
	{
		$errors = $result->getErrorMessages();
	}
}

// save action
if (($save != '' || $apply != '') && $requestMethod == 'POST' && check_bitrix_sessid())
{
    $data = array(
        'NAME' => trim($request['NAME']),
        'FOUND_YEAR' => intval($request['FOUND_YEAR']),
    );

    if ($is_update_form){
        $result = Ftmorm\TeamsTable::update($ID, $data);
    }
    else{
        $result = Ftmorm\TeamsTable::add($data);
        $ID = $result->getId();
    }

    if ($result->isSuccess())
	{
        if ($save != '')
		{
			\LocalRedirect('ftmorm_teams_list.php?lang='.LANGUAGE_ID);
		}
		else
		{
			\LocalRedirect('ftmorm_team_edit.php?ID='.$ID.'&lang='.LANGUAGE_ID.'&'.$tabControl->ActiveTabParam());
		}
    }
    else
	{
		$errors = $result->getErrorMessages();
	}

    // rewrite original value by form value to restore form
	foreach ($data as $k => $v)
	{
		$team[$k] = $v;
	}

}
// view
if ($request->get('mode') == 'list')
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_js.php');
}
else
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
}

// menu
$aMenu = array(
	array(
		'TEXT'	=> GetMessage('FTMORM_ADMIN_ROWS_RETURN_TO_LIST_BUTTON'),
		'TITLE'	=> GetMessage('FTMORM_ADMIN_ROWS_RETURN_TO_LIST_BUTTON'),
		'LINK'	=> 'ftmorm_teams_list.php?lang='.LANGUAGE_ID,
		'ICON'	=> 'btn_list',
	)
);

$adminContextMenu = new CAdminContextMenu($aMenu);
$adminContextMenu->Show();

if (!empty($errors))
{
	CAdminMessage::ShowMessage(join("\n", $errors));
}
?>
<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="ID" value="<?= htmlspecialcharsbx($team['ID'])?>">
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID?>">
	<?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
    <tr>
        <td width="40%"><strong><?=GetMessage('FTMORM_ADMIN_TEAMS_ENTITY_NAME_FIELD')?></strong></td>
        <td><?
			if (!$isEditMode):
				?><?=htmlspecialcharsEx($team['NAME'])?><?
			else:
				?><input type="text" name="NAME" size="30" value="<?= htmlspecialcharsbx($team['NAME'])?>"><?
			endif;
		?></td>
    </tr>
    <tr>
        <td width="40%"><strong><?=GetMessage('FTMORM_ADMIN_TEAMS_ENTITY_FOUND_YEAR_FIELD')?></strong></td>
        <td><?
			if (!$isEditMode):
				?><?=htmlspecialcharsEx($team['FOUND_YEAR'])?><?
			else:
				?><input type="text" name="FOUND_YEAR" size="30" value="<?= htmlspecialcharsbx($team['FOUND_YEAR'])?>"><?
			endif;
		?></td>
    </tr>

    <?
	$tabControl->Buttons(array('disabled' => !$isEditMode, 'back_url' => 'ftmorm_teams_list.php?lang='.LANGUAGE_ID));
	$tabControl->End();
	?>
</form>
<?php
if ($request->get('mode') == 'list')
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_js.php');
}
else
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
}
?>
ftmorm_team_edit.php


