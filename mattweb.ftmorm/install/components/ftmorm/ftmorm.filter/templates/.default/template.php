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

<div class="main-wrap">
    <div class="filter-wrap">
        <?if(is_array($arResult["ERR_MESSAGES"])):?>
        <div class="filter-err-list">
            <?foreach($arResult["ERR_MESSAGES"] as $errMess):?>
            <p><?=ShowError($errMess);?></p>
            <?endforeach?>
        </div>
        <?endif?>
        <?if(is_countable($arResult['FIELDS']) && count($arResult['FIELDS']) > 0):?>
        <form class="filter-form<?=' '.$arResult['FILTER_DISPLAY_MODE']?>" method="get" action="<?=$arResult['ACTION_URL']?>">
            <?foreach($arResult['FIELDS'] as $arField):?>        
            <div class="filter-field-wrap">

                <?if($arField['FLTFIELD_TYPE'] == 'ISR'):?>
                    <?$labelFor = ($arField['PROP_TYPE'] == 'DateField' || $arField['PROP_TYPE'] == 'DatetimeField') ? $arField['FLD_HTML_ID'].'_e' : $arField['FLD_HTML_ID'];?>
                    <label for="<?=$labelFor?>"><?=$arField['PROP_NAME']?>: </label>
                    <?if($arField['PROP_TYPE'] == 'DateField' || $arField['PROP_TYPE'] == 'DatetimeField'):?>
                        <?=$arField['FLD_HTML']['EARLIEST']?>&nbsp;…&nbsp;<?=$arField['FLD_HTML']['LATEST']?>
                    <?else:?>
                        <?=$arField['FLD_HTML']?>
                    <?endif?>
                <?else:?>
                    <label for="<?=$arField['FLD_HTML_ID']?>"><?=$arField['PROP_NAME']?>: </label>
                    <?=$arField['FLD_HTML']?>
                <?endif?>
            </div>        
            <?endforeach?>
            <div class="filter-field-wrap">
                <input type="submit" name="filter" value="<?=Loc::GetMessage('FILTER_BTM_TITLE')?>" />
                <input type="submit" name="filter_reset" value="<?=Loc::GetMessage('FILTER_RESET_BTM_TITLE')?>" />
            </div>
        </form>
        <?endif?>
    </div>
</div>
<?if($arParams['USE_TEXTFIELDS_TOOLTIPS'] == 'Y'):?>
    <script>
        window.onload = function(){

            let textFieldTooltip = new TextFieldTooltip({
                'AJAX_HANDLER': '<?=$arParams['TOOLTIP_AJAX_HANDLER']?>',
                'FLT_TEXT_FIELD_SELECTOR': '.j-flt-text-field',
                'TOOLTIPS_WRAP_SELECTOR': '.j-tooltips',
                'TOOLTIP_RES_CSS_CLASS': 'j-tooltip-res',
                'TOOLTIP_RES_SELECTOR': '.j-tooltip-res',
                'MIN_QUERY_LENGTH': '<?=$arParams['MIN_QUERY_LENGTH']?>',
                'USE_ORM_ENTITY_ALIAS': '<?=$arParams['USE_ORM_ENTITY_ALIAS']?>',
                'HIDDEN_CSS_CLASS': 'dn',
            });
        }
    </script>
<?endif?>



