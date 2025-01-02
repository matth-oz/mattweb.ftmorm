<?php
use Bitrix\Main,
Bitrix\Main\Loader,
Mattweb\Ftmorm;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

IncludeModuleLangFile(__FILE__);

/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
IncludeModuleLangFile(__FILE__);

if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Loader::includeModule("mattweb.ftmorm");

// fid - id поля html-формы, кукда должно возвращаться выбранное значение
// sid - id элемента (span) для отображения названиея команды

if(isset($_GET['fid']) && isset($_GET['sid'])){
	$fId = trim($_GET['fid']);
	$sId = trim($_GET['sid']);

    if(!empty($fId) && !empty($sId)){
        $APPLICATION->SetTitle(GetMessage('FTMORM_PLAYERS_SEARCH_TITLE'));

        $sTableID = "tbl_teams_search";
		$oSort = new CAdminSorting($sTableID, "NAME", "asc");
		$lAdmin = new CAdminList($sTableID, $oSort);

        $arHeaders = array(
			array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
			array("id"=>"FIRST_NAME", "content"=>GetMessage('FTMORM_PLAYERS_ENTITY_FIRST_NAME_FIELD'), "sort"=>"FIRST_NAME", "default"=>true),	
            array("id"=>"LAST_NAME", "content"=>GetMessage('FTMORM_PLAYERS_ENTITY_LAST_NAME_FIELD'), "sort"=>"LAST_NAME", "default"=>true),		
		);

        $lAdmin->AddHeaders($arHeaders);

		$by = mb_strtoupper($oSort->getField());
		$order = mb_strtoupper($oSort->getOrder());

        $getListOrder = ["ID" => "ASC"];

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
			$row = $lAdmin->AddRow(
				$f_ID, 
				$arRes,				
			);

			
            $playerFullName = $arRes["FIRST_NAME"].' '.$arRes["LAST_NAME"];
            $arActions = [];
			$arActions[] = [
				"ICON" => "",
				"TEXT" => GetMessage("FTMORM_PLAYERS_CHOOSE_PLAYER"),
				"ACTION" => "javascript:SelEl('".CUtil::JSEscape($f_ID)."', '".htmlspecialcharsbx(CUtil::JSEscape($playerFullName), ENT_QUOTES)."')",
			]; 

			$row->AddActions($arActions);
		}

    }
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
?>

<?if(!empty($fId) && !empty($sId)):?>
<script>
	function SelEl(id, name)
	{
		let el, snEl;
		el = window.opener.document.getElementById('<?echo $fId?>');

		if(el){
			el.value = id;
		}

		snEl = window.opener.document.getElementById('<?echo $sId?>');
		if(snEl){
			snEl.innerHTML = name;
		}

		window.close();
	}	
</script>
<?
$lAdmin->CheckListMode();
$lAdmin->DisplayList();
?>
<?else:?>
	<?showError(GetMessage('FTMORM_PLAYERS_SEARCH_EMPTY_PARAMS'));?>
<?endif?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
