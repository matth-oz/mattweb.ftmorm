<?php
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Context;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;

use Mattweb\Ftmorm;
//use Mattweb\Ftmorm\Service;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class FtmormFilter extends CBitrixComponent
{
    /** @var ErrorCollection */
    protected $errorCollection;
    protected $classesPrefixes;

    protected function checkModules()
    {
        if (!Loader::includeModule('mattweb.ftmorm'))
        {
            throw new Main\LoaderException(Loc::getMessage('MATTWEB_FTMORM_MODULE_NOT_INSTALLED'));
        }
    }

    protected function processOrmQuery($ormClassName, $propId){
        $curClassName = ServiceActions::getFtmOrmClass($ormClassName);
        $curEntity = $curClassName::getEntity();
        $query = new Entity\Query($curEntity);

        $query->setSelect([$propId]);
        $curEntityRes = $query->exec();

        return $curEntityRes;
    }

    protected function generateFieldHtmlID($propId, $curValue = null, $length = 5){
       $res = 'f_';
       $res .= strtolower(substr(sha1($propId), 0, $length));

       if(!is_null($curValue)){
           $valRes = substr(sha1($curValue), 0, $length);
           $res .= strtolower('_'.$valRes);
       }

       return $res;
    }

    public function onPrepareComponentParams($arParams){
        $this->errorCollection = new ErrorCollection();

        $arParams['ORM_CLASS_NAME'] = trim($arParams['ORM_CLASS_NAME']);
        if(strlen($arParams['ORM_CLASS_NAME']) == 0 || $arParams['ORM_CLASS_NAME'] == 'none'){
            $this->errorCollection->setError(
                new Error(Loc::getMessage('ORM_CLASS_NAME_IS_EMPTY'))
            );           
        }

        if(!is_array($arParams['ORM_CLASS_S_FIELDS'])){
            $this->errorCollection->setError(
                new Error(Loc::getMessage('ORM_CLASS_S_FIELDS_IS_EMPTY'))
            );
        }

        if(!empty($arParams['ORM_CLASS_S_FIELDS_TYPES'])){

          
            $arParams['ORM_CLASS_S_FIELDS_TYPES'] = json_decode($arParams['ORM_CLASS_S_FIELDS_TYPES'], true);

            $arSFieldsTypesKeys = array_keys($arParams['ORM_CLASS_S_FIELDS_TYPES']);

            $sFielsdDiffForward = array_diff($arParams['ORM_CLASS_S_FIELDS'], $arSFieldsTypesKeys);
            $sFielsdDiffBack = array_diff($arSFieldsTypesKeys, $arParams['ORM_CLASS_S_FIELDS']);

            if(count($sFielsdDiffForward) > 0 || count($sFielsdDiffBack) > 0){
                $this->errorCollection->setError(
                    new Error(Loc::getMessage('ORM_CLASS_S_FIELDS_TYPES_IS_EMPTY'))
                );
            }
        }
        else{
            $this->errorCollection->setError(
                new Error(Loc::getMessage('ORM_CLASS_S_FIELDS_TYPES_IS_EMPTY'))
            );
        }
        
        $arParams['FILTER_ACTION_URL'] = trim($arParams['FILTER_ACTION_URL']);
        if(strlen($arParams['FILTER_ACTION_URL']) == 0){
            global $APPLICATION;
            $arParams['FILTER_ACTION_URL'] = $APPLICATION->GetCurDir();
        }

        $arParams['SHOW_BOOL_ONLY_TRUE'] = trim($arParams['SHOW_BOOL_ONLY_TRUE']);
        if($arParams['SHOW_BOOL_ONLY_TRUE'] == ''){
            $arParams['SHOW_BOOL_ONLY_TRUE'] = 'N';
        }

        $arParams['USE_TEXTFIELDS_TOOLTIPS'] = trim($arParams['USE_TEXTFIELDS_TOOLTIPS']);
        if($arParams['USE_TEXTFIELDS_TOOLTIPS'] == ''){
            $arParams['USE_TEXTFIELDS_TOOLTIPS'] = 'N';
        }
        
        $arParams['MIN_QUERY_LENGTH'] = intval($arParams['MIN_QUERY_LENGTH']);
        if($arParams['MIN_QUERY_LENGTH'] == 0){
            $arParams['MIN_QUERY_LENGTH'] = 3;
        }

        $arParams['TOOLTIP_AJAX_HANDLER'] = $this->GetPath().'/tooltips.php';

        $arParams['USE_ORM_ENTITY_ALIAS'] = $arParams['USE_ORM_ENTITY_ALIAS'] == 'Y';
        return $arParams;
    }

    public function executeComponent()
    {        

        if (is_countable($this->errorCollection) && count($this->errorCollection)) {
            /** @var Error $error */
            foreach ($this->errorCollection as $error) {
                ShowError($error->getMessage());               
            }

            return;
        }
        
        $this->includeComponentLang('class.php');

        try{
            $this->checkModules();
        }catch(\Exception $e){
            $this->arResult['ERR_MESSAGES'][] = $e->getMessage();
        }
                
        if($this->arParams['ORM_CLASS_NAME'] == 'PlayersTable'){
           $this->arResult['PLAYERS_TYPES_TITLES'] = ServiceActions::getTeamPlayerType();
        }

        $this->classesPrefixes = ServiceActions::getFtmOrmClassesPrefixes();

        $curClassName = $this->arParams['ORM_CLASS_NAME'];

        $curPrefix = (array_key_exists($curClassName, $this->classesPrefixes)) ? $this->classesPrefixes[$curClassName] : '';

        /**
            * PROP_NAME - Название свойства
            * PROP_ID	- ИД свойства		
            * PROP_TYPE - тип свойства (S - строка, N - число, F - файл, L - список, E - привязка к элементам, G - привязка к группам)
            * LNKD_IBLOCK_ID  - ИД инфоблока со связанными элементами (если PROP_TYPE == E)
            * FLTFIELD_TYPE - тип поля в форме-фильтре (IT - text; IS - select; ISM - select multiply; ISL - slider; ISR - ranges; ICH - checkbox; IR - radiobutton;
        */

        $this->arResult['FILTER_DISPLAY_MODE'] = $this->arParams['FILTER_DISPLAY_MODE'];

        global $USER;
        $arPropsParams = [];

        $servObj = new ServiceActions($this->arParams['ORM_CLASS_NAME']);

        $arFieldsInfo = $servObj->getScalarFields('full');

        $useTooltips = $this->arParams['USE_TEXTFIELDS_TOOLTIPS'] == 'Y';

        foreach ($this->arParams['ORM_CLASS_S_FIELDS'] as $fieldName){
            if(array_key_exists($fieldName, $arFieldsInfo)){
                $propID = ($this->arParams['USE_ORM_ENTITY_ALIAS'] && !empty($curPrefix)) ? $curPrefix.$fieldName : $fieldName;
                
                
                // PREFIX: создаем массив с префиксами или без них
                $arPropsParams[$fieldName] = [
                    'PROP_NAME' => $arFieldsInfo[$fieldName]['TITLE'],
                    'PROP_ID' => $propID,
                    'PROP_TYPE' => $arFieldsInfo[$fieldName]['PARENT_CLASS'],
                    'FLTFIELD_TYPE' =>  $this->arParams['ORM_CLASS_S_FIELDS_TYPES'][$fieldName],
                ];

                if(array_key_exists('VALUES', $arFieldsInfo[$fieldName]))
                    $arPropsParams[$fieldName]['VALUES'] = $arFieldsInfo[$fieldName]['VALUES'];

                if($arFieldsInfo[$fieldName]['PARENT_CLASS'] == 'BooleanField')
                    $arPropsParams[$fieldName]['FIELD_TRUE_VALUE'] = $arFieldsInfo[$fieldName]['VALUES'][1];
            }
        }

        $request = Context::getCurrent()->getRequest();
        $reqValues = $request->getQueryList();

        $this->arResult['arFieldsFilter'] = [];

        if(isset($reqValues['filter']) || isset($reqValues['filter_reset'])){
            $USER->SetParam('ftmormfilter', []);
            $USER->SetParam('curFilterParams', ''); // ? нужно ли это ?
        }

        if(isset($reqValues['filter_reset'])){
            global $APPLICATION;
            $curPageWop = $APPLICATION->GetCurPage();

            LocalRedirect($curPageWop);            
        }

        $arRequestFilter = [];
        if(isset($reqValues['filter'])){            
             // фильтра в объект Entity\Query
            $arRequestFilter = $reqValues->toArray();

            foreach($arRequestFilter as $fKey => $fVal){
            // здесь мы формируем массив фильтр для передачи его в качестве

                $curPrefix = (array_key_exists($curClassName, $this->classesPrefixes)) ? $this->classesPrefixes[$curClassName] : '';

                $fKeyClear = str_replace(strtolower($curPrefix), '', $fKey);
                
                $fKeyClear = strtoupper($fKeyClear);
                $fKey = strtoupper($fKey);
                if(gettype($fVal) == 'string') $fVal = trim($fVal);

                if(array_key_exists($fKeyClear, $arPropsParams)){
                    $fieldType = $arPropsParams[$fKeyClear]['PROP_TYPE'];

                    try{                       

                        switch($fieldType){
                            case 'DateField':
                            case 'DatetimeField':
                                if(is_array($fVal)){

                                    if(!empty($fVal[0])){
                                        $stts = strtotime($fVal[0]);
                                        $dtstts = date('d.m.Y H:i:s', $stts);
                                        $this->arResult['arFieldsFilter']['>='.$fKey] = new \Bitrix\Main\Type\DateTime($dtstts);
                                    }

                                    if(!empty($fVal[1])){
                                        $stts = strtotime($fVal[1]);
                                        $dtstts = date('d.m.Y H:i:s', $stts);
                                        $this->arResult['arFieldsFilter']['<='.$fKey] = new \Bitrix\Main\Type\DateTime($dtstts);
                                    }
                                }
                                else{
                                    throw new Exception(Loc::GetMessage('DATE_PARAMETER_ERROR'));
                                }
                                break;
                            case 'BooleanField':
                                $this->arResult['arFieldsFilter'][$fKey] = $fVal;
                                break;
                            case 'IntegerField':
                                $this->arResult['arFieldsFilter'][$fKey] = $fVal;
                                break;
                            case 'FloatField':
                                $this->arResult['arFieldsFilter'][$fKey] = $fVal;
                                break;
                            case 'EnumField':
                            case 'StringField':
                                // 'ISM'=>'ISM'
                                if(is_array($fVal) && $fVal[0] == 'all'){
                                    array_shift($fVal);
                                    if(!empty($fVal)) $this->arResult['arFieldsFilter'][$fKey] = $fVal;
                                }
                                elseif(!empty($fVal) && $fVal != 'all'){
                                   $this->arResult['arFieldsFilter'][$fKey] = $fVal;
                                }
                                break;
                            default:
                                throw new Exception(Loc::GetMessage('UNKNOWN_PARAMETER_TYPE', ['#FIELD_TYPE#'=>$fieldType]));
                        }
                    }catch(\Exception $e){
                        $this->arResult['ERR_MESSAGES'][] = $e->getMessage();
                    }
                }
            }

            // массив для передачи в другие компоненты
            $USER->SetParam('ftmormfilter', $this->arResult['arFieldsFilter']);
            // массив текущих значений фильтра для отображения в форме
            $USER->SetParam('curFilterParams', $reqValues->toArray()); // ? нужно ли это ?

        }

        // URL для отправки формы
        $this->arResult["ACTION_URL"] = $this->arParams['FILTER_ACTION_URL'];

        if(count($arPropsParams) > 0 && count($this->arParams['ORM_CLASS_S_FIELDS']) > 0){
            // здесь формируется массив для отображения HTML-формы фильтра
            $this->arResult["FIELDS"] = [];

            foreach($this->arParams['ORM_CLASS_S_FIELDS'] as $fieldName){

                $fldFldID = strtolower($fieldName);
                $fldHtmlId = $this->generateFieldHtmlID($arPropsParams[$fieldName]['PROP_ID']);

                $fldHtmlType = $arPropsParams[$fieldName]['FLTFIELD_TYPE'];
                $curFieldType = $arPropsParams[$fieldName]['PROP_TYPE'];

                $this->arResult["FIELDS"][$fieldName] = [
                    'PROP_NAME' => $arPropsParams[$fieldName]['PROP_NAME'],
                    'PROP_TYPE' => $curFieldType,
                    'PROP_ID' => $fldFldID,
                    'FLTFIELD_TYPE' => $fldHtmlType,
                    'FLD_HTML_ID' => $fldHtmlId,
                ];

                $fld_html = '';

                if($curFieldType == 'DateField' || $curFieldType == 'DatetimeField'){
                    // 'ISR'
                    // получаем уникальные значения текущего поля
                    $curEntityRes = $this->processOrmQuery($this->arParams['ORM_CLASS_NAME'], $fieldName);

                    $arFieldDates = [];

                    $this->arResult["FIELDS"][$fieldName]['VALUES'] = [];

                    $k = 0;
                    while($arR = $curEntityRes->fetch()){
                        $arFieldDates[$k] = $arR[$fieldName]->format("d.m.Y");
                        $k++;  
                    }

                    // вычисляем минимальное и максимальное значение
                    usort($arFieldDates, function($a, $b) {
                        return strtotime($a) - strtotime($b);
                    });

                    // EARLIEST
                    $earliest = array_key_first($arFieldDates);
                    $this->arResult["FIELDS"][$fieldName]['VALUES']['EARLIEST'] = $arFieldDates[$earliest];
                    
                    // LATEST
                    $latest = array_key_last($arFieldDates);
                    $this->arResult["FIELDS"][$fieldName]['VALUES']['LATEST'] = $arFieldDates[$latest];


                    if(!empty($this->arResult["FIELDS"][$fieldName]['VALUES'])){
                        $curFldValues = $this->arResult["FIELDS"][$fieldName]['VALUES'];

                        $fldFldID = strtolower($arPropsParams[$fieldName]['PROP_ID']);
                        $fldHtmlType = $arPropsParams[$fieldName]['FLTFIELD_TYPE'];

                        if(array_key_exists($fldFldID, $arRequestFilter)){
                            if(!empty($arRequestFilter[$fldFldID][0]))
                                $curFldValues['EARLIEST'] = $arRequestFilter[$fldFldID][0];

                            if(!empty($arRequestFilter[$fldFldID][1]))
                                $curFldValues['LATEST'] = $arRequestFilter[$fldFldID][1];
                        }

                        // 'ISR'=>'ISR'
                        if($fldHtmlType == 'ISR'){
                            $fld_html = '<input type="date" id="'.$fldHtmlId.'_e" name="'.$fldFldID.'[]" value="'.$curFldValues['EARLIEST'].'" min="'.$curFldValues['EARLIEST'].'" max="'.$curFldValues['LATEST'].'" />';
                            $this->arResult["FIELDS"][$fieldName]['FLD_HTML']['EARLIEST'] = $fld_html;

                            $fld_html = '<input type="date" id="'.$fldHtmlId.'_l" name="'.$fldFldID.'[]" value="'.$curFldValues['LATEST'].'" min="'.$curFldValues['EARLIEST'].'" max="'.$curFldValues['LATEST'].'" />';
                            $this->arResult["FIELDS"][$fieldName]['FLD_HTML']['LATEST'] = $fld_html;
                        }
                    }
                }
                elseif($curFieldType == 'StringField'){

                    // получаем уникальные значения текущего поля
                    $curEntityRes = $this->processOrmQuery($this->arParams['ORM_CLASS_NAME'], $fieldName);

                    $this->arResult["FIELDS"][$fieldName]['VALUES'] = [];

                    if($this->arParams['ORM_CLASS_NAME'] == 'GamesTable'){
                        $teamCityName = Option::get('mattweb.ftmorm', 'team_city_name_'.SITE_ID);
                        $this->arResult["FIELDS"][$fieldName]['VALUES'][$teamCityName] = $teamCityName;
                    }                    

                    while($arR = $curEntityRes->fetch()){

                        if(!in_array($arR[$fieldName], $this->arResult["FIELDS"][$fieldName]['VALUES']) && !empty($arR[$fieldName])){
                            $this->arResult["FIELDS"][$fieldName]['VALUES'][$arR[$fieldName]] = $arR[$fieldName];
                        }                    
                    }

                    if(!empty($this->arResult["FIELDS"][$fieldName]['VALUES'])){

                        $curFldValues = $this->arResult["FIELDS"][$fieldName]['VALUES'];
                        
                        // сортируем
                        asort($curFldValues);

                        $fldFldID = strtolower($arPropsParams[$fieldName]['PROP_ID']);
                        $fldHtmlType = $arPropsParams[$fieldName]['FLTFIELD_TYPE'];

                        // 'IT'=>'IT', 'IS'=>'IS', 'ISM'=>'ISM'
                        if($fldHtmlType == 'IT'){
                            $fld_val = array_key_exists($arPropsParams[$fieldName]['PROP_ID'], $this->arResult['arFieldsFilter']) ? $this->arResult['arFieldsFilter'][$arPropsParams[$fieldName]['PROP_ID']] : '';
                            
                            if($useTooltips){
                                 $modelName = base64_encode($this->arParams['ORM_CLASS_NAME']); 
                                 $curFieldName = base64_encode($arPropsParams[$fieldName]['PROP_ID']);

                                 $fld_html .= '<div class="str-fld-wrap">';                                  
                                 $fld_html .= '<input type="text" class="flt-text-field j-flt-text-field" id="'.$fldHtmlId.'" name="'.$fldFldID.'" value="'.$fld_val.'" data-model="'.$modelName.'" data-field="'.$curFieldName.'" autocomplete="off" />';
                                 $fld_html .= '<div class="str-fld-tooltip j-tooltips dn">';
                                 $fld_html .= '<span class="tooltip-res j-tooltip-res" data-ftarget="'.$fldHtmlId.'">результат 1</span>';
                                 $fld_html .= '<span class="tooltip-res j-tooltip-res" data-ftarget="'.$fldHtmlId.'">результат 2</span>';
                                 $fld_html .= '<span class="tooltip-res j-tooltip-res" data-ftarget="'.$fldHtmlId.'">результат 3</span>';
                                 $fld_html .= '</div>';
                                 $fld_html .= '</div>';                                
                               
                            }else{
                                $fld_html .= '<input type="text" id="'.$fldHtmlId.'" name="'.$fldFldID.'" value="'.$fld_val.'" autocomplete="off" />';
                            }
                            
                            
                        }

                        if($fldHtmlType == 'IS' || $fldHtmlType == 'ISM'){
                            $isMulti = $fldHtmlType == 'ISM';

                            $fld_html .= '<select name="'.$fldFldID;

                            if($isMulti)
                                $fld_html .= '[]" id="'.$fldHtmlId.'" multiple = "multiple" size="5">';
                            else
                                $fld_html .= '" id="'.$fldHtmlId.'">';

                            if(!$isMulti)
                                $fld_html .= '<option value="all">'.Loc::GetMessage('OPT_ALL_TITLE').'</option>';

                            foreach($curFldValues as $key => $value){
                                $fld_html .= '<option value="'.$key.'"';


                                if(array_key_exists($arPropsParams[$fieldName]['PROP_ID'], $this->arResult['arFieldsFilter'])){
                                    if($isMulti && is_array($this->arResult['arFieldsFilter'][$arPropsParams[$fieldName]['PROP_ID']])
                                        && in_array($key, $this->arResult['arFieldsFilter'][$arPropsParams[$fieldName]['PROP_ID']])){
                                        $fld_html .= ' selected="selected"';
                                    }
                                    else if($this->arResult['arFieldsFilter'][$arPropsParams[$fieldName]['PROP_ID']] == $key){
                                           $fld_html .= ' selected="selected"';
                                    }
                                }
                                

                                if($this->arParams['ORM_CLASS_NAME'] == 'PlayersTable' && array_key_exists($value, $this->arResult['PLAYERS_TYPES_TITLES'])){
                                    
                                    $value = $this->arResult['PLAYERS_TYPES_TITLES'][$value];
                                }                               

                                $fld_html .= '>'.$value.'</option>';
                            }
                            
                            $fld_html .= '</select>';                            
                        } 
                        
                        $this->arResult["FIELDS"][$fieldName]['FLD_HTML'] = $fld_html;
                    }

                }
                elseif($curFieldType == 'BooleanField'){
                    // public метод getValues() -->  $arProp['VALUES']
                    // 'IR'=>'IR', 'ICH'=>'ICH', 'IS'=>'IS'

                    if(array_key_exists($fieldName, $arPropsParams) && isset($arPropsParams[$fieldName]['VALUES'])){
                        $this->arResult["FIELDS"][$fieldName]['FIELD_TRUE_VALUE'] = $arPropsParams[$fieldName]['FIELD_TRUE_VALUE'];

                        $fieldTrueValue = $arPropsParams[$fieldName]['FIELD_TRUE_VALUE'];

                        $curFldValues = $arPropsParams[$fieldName]['VALUES'];
                        $fldHtmlType = $arPropsParams[$fieldName]['FLTFIELD_TYPE'];

                        $fldFldID = strtolower($fieldName);

                        if($fldHtmlType == 'IR'){
                            foreach($curFldValues as $curValue){
                                if($this->arParams['SHOW_BOOL_ONLY_TRUE'] == 'Y' && $curValue != $fieldTrueValue) continue;

                                $htmlInputID = $this->generateFieldHtmlID($fldFldID, $curValue);
                                $fld_html .= '<input type="radio"';

                                if($this->arResult['arFieldsFilter'][$arPropsParams[$fieldName]['PROP_ID']] == $curValue) $fld_html .= ' checked="checked"';

                                $fld_html .= ' name="'.$fldFldID.'" value="'.$curValue.'" id="'.$htmlInputID.'" />';
                                $fld_html .= "&nbsp;";
                                $curValueTitle = ($curValue == $fieldTrueValue) ? Loc::GetMessage('BOOL_FIELD_TRUE_TITLE') : Loc::GetMessage('BOOL_FIELD_FALSE_TITLE');
                                $fld_html .= '<label for="'.$htmlInputID.'">'.$curValueTitle.'</label>';
                            }
                        }
                        if($fldHtmlType == 'ICH'){
                            foreach($curFldValues as $curValue){
                                if($this->arParams['SHOW_BOOL_ONLY_TRUE'] == 'Y' && $curValue != $fieldTrueValue) continue;
                                $htmlInputID = $this->generateFieldHtmlID($fldFldID, $curValue);
                                $fld_html .= '<input type="checkbox"';

                                if($this->arResult['arFieldsFilter'][$arPropsParams[$fieldName]['PROP_ID']] == $curValue) $fld_html .= ' checked="checked"';

                                $fld_html .= ' name="'.$fldFldID.'" value="'.$curValue.'" id="'.$htmlInputID.'" />';
                                $fld_html .= "&nbsp;";
                                $curValueTitle = ($curValue == $fieldTrueValue) ? Loc::GetMessage('BOOL_FIELD_TRUE_TITLE') : Loc::GetMessage('BOOL_FIELD_FALSE_TITLE');
                                $fld_html .= '<label for="'.$htmlInputID.'">'.$curValueTitle.'</label>';
                            }

                        }
                        if($fldHtmlType == 'IS'){
                            $fld_html .= '<select name="'.$fldFldID.'" id="'.$fldHtmlId.'">';

                            foreach($curFldValues as $curValue){
                                if($this->arParams['SHOW_BOOL_ONLY_TRUE'] == 'Y' && $curValue != $fieldTrueValue) continue;

                                $curValueTitle = ($curValue == $fieldTrueValue) ? Loc::GetMessage('BOOL_FIELD_TRUE_TITLE') : Loc::GetMessage('BOOL_FIELD_FALSE_TITLE');
                                $fld_html .= '<option value="'.$curValue.'"';

                                if($this->arResult['arFieldsFilter'][$arPropsParams[$fieldName]['PROP_ID']] == $curValue) $fld_html .= ' selected="selected"';

                                $fld_html .= '>'.$curValueTitle.'</option>';
                            }

                            $fld_html .= '</select>';
                        }

                        $this->arResult["FIELDS"][$fieldName]['FLD_HTML'] = $fld_html;

                    }
                    
                }
                elseif($curFieldType == 'IntegerField'){
                    // 'IT'=>'IT', 'ISR'=>'ISR', 'NMB'=>'NMB'
                    $fld_val = array_key_exists($arPropsParams[$fieldName]['PROP_ID'], $this->arResult['arFieldsFilter']) ? $this->arResult['arFieldsFilter'][$arPropsParams[$fieldName]['PROP_ID']] : '';
                    $fldFldID = strtolower($arPropsParams[$fieldName]['PROP_ID']);
                    
                    if($fldHtmlType == 'ISR' || $fldHtmlType == 'NMB' ){
                        $curEntityRes = $this->processOrmQuery($this->arParams['ORM_CLASS_NAME'], $fieldName);

                        $this->arResult["FIELDS"][$fieldName]['VALUES'] = [];

                        while($arR = $curEntityRes->fetch()){

                            if(!in_array($arR[$fieldName], $this->arResult["FIELDS"][$fieldName]['VALUES']) && !empty($arR[$fieldName])){
                                $this->arResult["FIELDS"][$fieldName]['VALUES'][$arR[$fieldName]] = $arR[$fieldName];

                            }
                        }
                        sort($this->arResult["FIELDS"][$fieldName]['VALUES']);
                        $lastElemIndex = count($this->arResult["FIELDS"][$fieldName]['VALUES']) - 1;
                        $minVal =  $this->arResult["FIELDS"][$fieldName]['VALUES'][0];
                        $maxVal = $this->arResult["FIELDS"][$fieldName]['VALUES'][$lastElemIndex];

                        if($fldHtmlType == 'ISR'){
                            $fld_html .= '<div class="isr-input-wrap">';
                            if(strlen($fld_val) == 0) $fld_val = floor($minVal + (($maxVal - $minVal) / 2));
                            $fld_html .= '<input type="range" id="'.$fldHtmlId.'" name="'.$fldFldID.'" min="'.$minVal.'" max="'.$maxVal.'" step="1" value="'.$fld_val.'">';                            
                            $fld_html .= '<div class="isr-input-vals"><span class="min-val">'.$minVal.'</span><span class="max-val">'.$maxVal.'</span></div>';                            
                            $fld_html .= '</div>';
                            $fld_html .= '<span class="isr-input-cur-val" id="'.$fldHtmlId.'_val"></span>';
                            ?>
                            <script>
                                window.onload = function(){
                                    const value = document.querySelector('#<?=$fldHtmlId.'_val'?>');
                                    const input = document.querySelector('#<?=$fldHtmlId?>');
                                    value.textContent = input.value;
                                    input.addEventListener('input', (event) => {
                                        value.textContent = event.target.value;
                                    });
                                }                                
                            </script>
                            <?
                        }

                        if($fldHtmlType == 'NMB'){
                            $fld_html .= '<input type="number" id="'.$fldHtmlId.'" name="'.$fldFldID.'" min="'.$minVal.'" max="'.$maxVal.'" value="'.$fld_val.'" />';
                        }
                    }

                    if($fldHtmlType == 'IT'){
                        if($useTooltips){
                            $fld_html .= '<input type="text" id="'.$fldHtmlId.'" name="'.$fldFldID.'" value="'.$fld_val.'" autocomplete="off" />';
                        }else{
                            $fld_html .= '<input type="text" id="'.$fldHtmlId.'" name="'.$fldFldID.'" value="'.$fld_val.'" autocomplete="off" />';
                        }
                    }

                    $this->arResult["FIELDS"][$fieldName]['FLD_HTML'] = $fld_html;
                }
                elseif($curFieldType == 'FloatField'){
                    // 'IT'=>'IT', 'ISR'=>'ISR'
                    $fld_val = array_key_exists($arPropsParams[$fieldName]['PROP_ID'], $this->arResult['arFieldsFilter']) ? $this->arResult['arFieldsFilter'][$arPropsParams[$fieldName]['PROP_ID']] : '';
                    $fldFldID = strtolower($arPropsParams[$fieldName]['PROP_ID']);

                    if($fldHtmlType == 'ISR'){
                        $curEntityRes = $this->processOrmQuery($this->arParams['ORM_CLASS_NAME'], $fieldName);

                        $this->arResult["FIELDS"][$fieldName]['VALUES'] = [];

                        while($arR = $curEntityRes->fetch()){

                            if(!in_array($arR[$fieldName], $this->arResult["FIELDS"][$fieldName]['VALUES']) && !empty($arR[$fieldName])){
                                $this->arResult["FIELDS"][$fieldName]['VALUES'][$arR[$fieldName]] = $arR[$fieldName];
                            }
                        }
                        sort($this->arResult["FIELDS"][$fieldName]['VALUES']);

                        $lastElemIndex = count($this->arResult["FIELDS"][$fieldName]['VALUES']) - 1;
                        $minVal =  $this->arResult["FIELDS"][$fieldName]['VALUES'][0];
                        $maxVal = $this->arResult["FIELDS"][$fieldName]['VALUES'][$lastElemIndex];

                        $fld_html .= '<input type="range" id="'.$fldHtmlId.'" name="'.$fldFldID.'" min="'.$minVal.'" max="'.$maxVal.'" step="" value="'.$fld_val.'">';
                    }

                    if($fldHtmlType == 'IT'){
                        if($useTooltips){
                            $fld_html .= '<input type="text" id="'.$fldHtmlId.'" name="'.$fldFldID.'" value="'.$fld_val.'" autocomplete="off" />';
                        }else{
                           $fld_html .= '<input type="text" id="'.$fldHtmlId.'" name="'.$fldFldID.'" value="'.$fld_val.'" autocomplete="off" />'; 
                        }                       
                    }

                    $this->arResult["FIELDS"][$fieldName]['FLD_HTML'] = $fld_html;
                }
                elseif($curFieldType == 'EnumField'){
                    // 'IS'=>'IS', 'ISM'=>'ISM'
                    // public метод getValues() --> $arProp['VALUES']

                    if(array_key_exists($fieldName, $arPropsParams) && isset($arPropsParams[$fieldName]['VALUES'])){
                        $curFldValues = $arPropsParams[$fieldName]['VALUES'];
                        $fldHtmlType = $arPropsParams[$fieldName]['FLTFIELD_TYPE'];
                        $fldFldID = strtolower($arPropsParams[$fieldName]['PROP_ID']);

                        if($fldHtmlType == 'IS' || $fldHtmlType == 'ISM'){
                            $isMulti = $fldHtmlType == 'ISM';

                            $fld_html .= '<select name="'.$fldFldID;

                            if($isMulti)
                                $fld_html .= '[]" id="'.$fldHtmlId.'" multiple = "multiple" size="5">';
                            else
                                $fld_html .= '" id="'.$fldHtmlId.'">';

                            if(!$isMulti)
                                $fld_html .= '<option value="all">'.Loc::GetMessage('OPT_ALL_TITLE').'</option>';

                            foreach($curFldValues as $key => $value){
                                $fld_html .= '<option value="'.$key.'"';

                                if(array_key_exists($arPropsParams[$fieldName]['PROP_ID'], $this->arResult['arFieldsFilter']) && in_array($key, $this->arResult['arFieldsFilter'][$arPropsParams[$fieldName]['PROP_ID']])){
                                    $fld_html .= ' selected="selected"';
                                }

                                $fld_html .= '>'.$value.'</option>';
                            }

                            $fld_html .= '</select>';

                            $this->arResult["FIELDS"][$fieldName]['FLD_HTML'] = $fld_html;
                        }

                    }
                }

            }


        }
        else{
            $this->arResult["ERR_MESSAGES"][] = Loc::GetMessage('EMPTY_PROPS_FILTER_PARAMS');
        }

        $this->includeComponentTemplate();
    }

}