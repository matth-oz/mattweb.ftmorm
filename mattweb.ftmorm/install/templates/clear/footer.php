<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

	</div>
</div>
<?IncludeTemplateLangFile(__FILE__);?>
	<? include_once('meta.php');?>

<?php $cuDir =  $APPLICATION->GetCurDir();?>

<?php if($cuDir == '/test_orm/extended/t3_1_js/getlist/' || $cuDir == '/test_orm/extended/t3_1_js/query/'):?>
	<script src="/test_orm/extended/t3_1_js/getlist/script.js"></script>
<?php endif?>
	</body>
</html>