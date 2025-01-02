<?php
use Bitrix\Main,
Bitrix\Main\Loader,
Mattweb\Ftmorm;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

IncludeModuleLangFile(__FILE__);
//IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");

/** @var CMain $APPLICATION */
/** @var CDatabase $DB */
IncludeModuleLangFile(__FILE__);

if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

Loader::includeModule("mattweb.ftmorm");

// fid - id поля html-формы, кукда должно возвращаться выбранное значение
// scl - css класс элемента (span) для отображения названиея команды
if(isset($_GET['fid']) && isset($_GET['scl'])){
	$fId = trim($_GET['fid']);
	$sCl = trim($_GET['scl']);

	if(!empty($fId) && !empty($sCl)){
		$APPLICATION->SetTitle(GetMessage('FTMORM_TEAMS_SEARCH_TITLE'));

		$sTableID = "tbl_teams_search";
		$oSort = new CAdminSorting($sTableID, "NAME", "asc");
		$lAdmin = new CAdminList($sTableID, $oSort);

		$arHeaders = array(
			array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
			array("id"=>"NAME", "content"=>GetMessage('FTMORM_TEAMS_ENTITY_NAME_FIELD'), "sort"=>"NAME", "default"=>true),			
		);

		$lAdmin->AddHeaders($arHeaders);

		$by = mb_strtoupper($oSort->getField());
		$order = mb_strtoupper($oSort->getOrder());
		
		//$getListOrder = ($by === "ID"? array($by => $order): array($by => $order, "ID" => "ASC"));

		$getListOrder = ["ID" => "ASC"];

		// select data
		$rsData = Ftmorm\TeamsTable::getList([
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

			$arActions = [];
			$arActions[] = [
				"ICON" => "",
				"TEXT" => GetMessage("FTMORM_TEAMS_CHOOSE_TEAM"),
				"ACTION" => "javascript:SelEl('".CUtil::JSEscape($f_ID)."', '".htmlspecialcharsbx(CUtil::JSEscape($arRes["NAME"]), ENT_QUOTES)."')",
			]; 

			$row->AddActions($arActions);
		}
	}		
}


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
?>

<?if(!empty($fId) && !empty($sCl)):?>
<script>
	function SelEl(id, name)
	{
		let el, snEl;
		el = window.opener.document.getElementById('<?echo $fId?>');

		if(el){
			el.value = id;
		}

		snEl = window.opener.document.querySelector('.<?=$sCl?>');
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
	<?showError(GetMessage('FTMORM_TEAMS_SEARCH_EMPTY_PARAMS'));?>
<?endif?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");