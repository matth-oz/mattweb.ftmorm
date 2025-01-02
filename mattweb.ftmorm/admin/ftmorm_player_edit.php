<?php
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
use Bitrix\Main,
Bitrix\Main\Type,
Bitrix\Main\Loader,
Mattweb\Ftmorm;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/prolog.php');

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");

if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Loader::includeModule("mattweb.ftmorm");

$arPlayerType = ServiceActions::getTeamPlayerType();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

// form
$aTabs = array(
	array(
		'DIV' => 'edit1',
		'TAB' => GetMessage('FTMORM_ADMIN_PLAYER_TITLE'),
		'TITLE' => GetMessage('FTMORM_ADMIN_PLAYER_TITLE')
	)
);

$tabControl = new CAdminTabControl('tabControl', $aTabs);


// init vars
$is_create_form = true;
$is_update_form = false;
$isEditMode = true;
$errors = array();
$localization = array();

$ID = (int)$request->get('ID');
$save = trim((string)$request->get('save'));
$apply = trim((string)$request->get('apply'));
$action = trim((string)$request->get('action'));
$requestMethod = $request->getRequestMethod();

if($ID > 0){
    $filter = array(
        'select' => array(
            'ID', 'FIRST_NAME', 'LAST_NAME', 'NICKNAME', 'CITIZENSHIP', 'DOB', 'ROLE'
        ),
        'filter' => array(
            '=ID' => $ID
        )
    );

    $player = Ftmorm\PlayersTable::getList($filter)->fetch();
    if (!empty($player))
	{
		$is_update_form = true;
		$is_create_form = false;
	}
}

// default values for create form / page title
if ($is_create_form){
	$player = array_fill_keys(array('ID', 'FIRST_NAME', 'LAST_NAME', 'NICKNAME', 'CITIZENSHIP', 'DOB', 'ROLE'), '');
	$APPLICATION->SetTitle(GetMessage('FTMORM_ADMIN_PLAYER_EDIT_PAGE_TITLE_NEW'));
}
else{
    $APPLICATION->SetTitle(GetMessage('FTMORM_ADMIN_PLAYER_EDIT_PAGE_TITLE_EDIT', array('#FIRST_NAME#' => $player['FIRST_NAME'], '#LAST_NAME#' => $player['LAST_NAME'])));
    $rsPlayer = Ftmorm\PlayersTable::getList([
        'select' => array('ID', 'FIRST_NAME', 'LAST_NAME', 'NICKNAME', 'CITIZENSHIP', 'DOB', 'ROLE'),
        'filter' => array('=ID' => $ID),
        'count_total' => true,
    ]);

    $player['ROWS_COUNT'] = $rsPlayer->getCount();
}

// delete action
if ($is_update_form && $action === 'delete' && check_bitrix_sessid()){
    $result = Ftmorm\PlayersTable::delete($player['ID']);
	if ($result->isSuccess())
	{
		\LocalRedirect('ftmorm_players_list.php?lang='.LANGUAGE_ID);
	}
	else
	{
		$errors = $result->getErrorMessages();
	}
}

// save action
if (($save != '' || $apply != '') && $requestMethod == 'POST' && check_bitrix_sessid())
{	
	
	$dobObj = new DateTime($request['DOB']);
	$dateStringToDB = $dobObj->format("Y-m-d");

	$data = array(
        'FIRST_NAME' => trim($request['FIRST_NAME']),
        'LAST_NAME' => trim($request['LAST_NAME']),
        'NICKNAME' => trim($request['NICKNAME']),
        'CITIZENSHIP' => trim($request['CITIZENSHIP']),
        'DOB' => new Type\Date($dateStringToDB, 'Y-m-d'),
        'ROLE' => trim($request['ROLE']),        
    );

    if ($is_update_form){
        $result = Ftmorm\PlayersTable::update($ID, $data);
    }
    else{
        $result = Ftmorm\PlayersTable::add($data);
        $ID = $result->getId();
    }

    if ($result->isSuccess())
	{
        if ($save != '')
		{
			\LocalRedirect('ftmorm_players_list.php?lang='.LANGUAGE_ID);
		}
		else
		{
			\LocalRedirect('ftmorm_player_edit.php?ID='.$ID.'&lang='.LANGUAGE_ID.'&'.$tabControl->ActiveTabParam());
		}
    }
    else
	{
		$errors = $result->getErrorMessages();
	}

    // rewrite original value by form value to restore form
	foreach ($data as $k => $v)
	{
		$player[$k] = $v;
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
		'LINK'	=> 'ftmorm_players_list.php?lang='.LANGUAGE_ID,
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
	<input type="hidden" name="ID" value="<?= htmlspecialcharsbx($player['ID'])?>">
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID?>">
	<?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td width="40%"><strong>ID</strong></td>
		<td><?=htmlspecialcharsEx($player['ID'])?></td>
	</tr>
	<tr>
		<td width="40%"><strong><?= GetMessage('FTMORM_ADMIN_PLAYERS_ENTITY_FIRST_NAME_FIELD')?></strong></td>
		<td><?
			if (!$isEditMode):
				?><?=htmlspecialcharsEx($player['FIRST_NAME'])?><?
			else:
				?><input type="text" name="FIRST_NAME" size="30" value="<?= htmlspecialcharsbx($player['FIRST_NAME'])?>"><?
			endif;
		?></td>
	</tr>
	<tr>
		<td width="40%"><strong><?= GetMessage('FTMORM_ADMIN_PLAYERS_ENTITY_LAST_NAME_FIELD')?></strong></td>
		<td><?
			if (!$isEditMode):
				?><?=htmlspecialcharsEx($player['LAST_NAME'])?><?
			else:
				?><input type="text" name="LAST_NAME" size="30" value="<?= htmlspecialcharsbx($player['LAST_NAME'])?>"><?
			endif;
		?></td>
	</tr>
	<tr>
		<td width="40%"><strong><?= GetMessage('FTMORM_ADMIN_PLAYERS_ENTITY_NICKNAME_FIELD')?></strong></td>
		<td><?
			if (!$isEditMode):
				?><?=htmlspecialcharsEx($player['NICKNAME'])?><?
			else:
				?><input type="text" name="NICKNAME" size="30" value="<?= htmlspecialcharsbx($player['NICKNAME'])?>"><?
			endif;
		?></td>
	</tr>
	<tr>
		<td width="40%"><strong><?= GetMessage('FTMORM_ADMIN_PLAYERS_ENTITY_CITIZENSHIP_FIELD')?></strong></td>
		<td><?
			if (!$isEditMode):
				?><?=htmlspecialcharsEx($player['CITIZENSHIP'])?><?
			else:
				?><input type="text" name="CITIZENSHIP" size="30" value="<?= htmlspecialcharsbx($player['CITIZENSHIP'])?>"><?
			endif;
		?></td>
	</tr>
	<tr>
		<td width="40%"><strong><?= GetMessage('FTMORM_ADMIN_PLAYERS_ENTITY_DOB_FIELD')?></strong></td>
		<td>
			<?if (!$isEditMode):?>
				<?=htmlspecialcharsEx($player['DOB'])?>
			<?else:?>
				<?echo CAdminCalendar::CalendarDate("DOB", htmlspecialcharsbx($player['DOB']), 19, true)?>				
			<?endif;?>
		</td>
	</tr>
	<tr>
		<td width="40%"><strong><?= GetMessage('FTMORM_ADMIN_PLAYERS_ENTITY_ROLE_FIELD')?></strong></td>
		<td>
			<?
			if (!$isEditMode):
				?><?=htmlspecialcharsEx($player['ROLE'])?><?
			else:
				?>	
				<select name="ROLE">
					<option value="">---</option>
					<?foreach($arPlayerType as $key=>$value):?>
						<?$key?>=><?=$player['ROLE']?>
						<option value="<?=$key?>"<?if($key == htmlspecialcharsbx($player['ROLE'])):?> selected="selected"<?endif?>><?=htmlspecialcharsbx($value)?></option>
					<?endforeach?>
				</select>				
			<?endif;?>
		</td>
	</tr>
	<?
	$tabControl->Buttons(array('disabled' => !$isEditMode, 'back_url' => 'ftmorm_players_list.php?lang='.LANGUAGE_ID));
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