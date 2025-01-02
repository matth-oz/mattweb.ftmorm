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

echo'<pre>';
if(function_exists('dump')){dump($arResult);}else{var_dump($arResult);}
echo'</pre>';
?>
<h1>template 12121132323</h1>
<?

if(is_countable($arResult['FIELDS']) && count($arResult['FIELDS']) > 0):?>
<div class="main-wrap">
    <div class="filter-wrap">
        <form class="filter-form" method="get" action="<?=$arResult['ACTION_URL']?>">
            <?foreach($arResult['FIELDS'] as $arField):?>        
            <div class="filter-field-wrap">
                <label for="<?=$arField['FLD_HTML_ID']?>_e"><?=$arField['PROP_NAME']?>: </label>

                <?if($arField['FLTFIELD_TYPE'] == 'ISR'):?>
                    <?=$arField['FLD_HTML']['EARLIEST']?>&nbsp;…&nbsp;<?=$arField['FLD_HTML']['LATEST']?>
                <?else:?>
                    <?=$arField['FLD_HTML']?>
                <?endif?>
            </div>        
            <?endforeach?>
            <div class="filter-field-wrap">
                <input type="submit" name="filter" value="<?=Loc::GetMessage('FILTER_BTM_TITLE')?>" />
            </div>
        </form>
    </div>
</div>
<?endif?>



