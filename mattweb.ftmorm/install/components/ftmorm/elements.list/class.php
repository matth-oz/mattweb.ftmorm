<?
use \Bitrix\Main\Loader,
    \Bitrix\Main\UI,
    \Bitrix\Main\Entity,
    \Bitrix\Main\Config\Option,
    \Bitrix\Main\Context,
    \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\ErrorCollection,
    \Bitrix\Iblock;

use Mattweb\Ftmorm;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class ElementsListComponent extends CBitrixComponent
{
    /** @var ErrorCollection */
    protected $errorCollection;
    protected $fieldSuffix = '__';
    protected $arrFilter;
    protected $arrAllFieldsDesc;
    protected $primaryKey;
    
    /**
     * проверяет подключение необходиимых модулей
     * @throws LoaderException
     */
    protected function checkModules()
    {
        if (!Loader::includeModule('mattweb.ftmorm'))
            throw new Main\LoaderException(Loc::getMessage('MATTWEB_FTMORM_MODULE_NOT_INSTALLED'));
    }

    public static function dump($var, $die = false){
        global $USER;   
        
        echo '<pre>';
        var_export($var);
        echo '</pre>';      
    
        if ($die) die();
    }

    public function onPrepareComponentParams($arParams)
    {        
        global $DB;
        $this->errorCollection = new ErrorCollection();

        try{
            $this->checkModules();
        }catch(\Exception $e){
            $this->arResult['ERR_MESSAGES'][] = $e->getMessage();
        }

        $arParams['ORM_CLASS_NAME'] = trim($arParams['ORM_CLASS_NAME']);
        if(strlen($arParams['ORM_CLASS_NAME']) == 0 || $arParams['ORM_CLASS_NAME'] == 'none'){
            $this->errorCollection->setError(
                new Error(Loc::getMessage('ORM_CLASS_NAME_IS_EMPTY'))
            );  
        }

        $this->primaryKey = ServiceActions::getPrimaryKey($arParams['ORM_CLASS_NAME']);

        if(!is_array($arParams['ORM_CLASS_S_FIELDS'])){
            $this->errorCollection->setError(
                new Error(Loc::getMessage('ORM_CLASS_S_FIELDS_IS_EMPTY'))
            );
        }

        $arParams['SHOW_ELEMENT_ID'] = ($arParams['SHOW_ELEMENT_ID'] ?? '') === 'Y';

        $arParams["ELEMENTS_COUNT"] = (int)($arParams["ELEMENTS_COUNT"] ?? 0);
        if ($arParams["ELEMENTS_COUNT"] <= 0){
	        $arParams["ELEMENTS_COUNT"] = 20;
        }

        $arrFilter = [];
        $this->arrFilter = [];
        if (!empty($arParams["FILTER_NAME"]) && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
        {
            $this->arrFilter = $GLOBALS[$arParams["FILTER_NAME"]] ?? [];
            if (!is_array($arrFilter))
            {
                $arrFilter = [];
            }
        }

        $arParams["SORT_BY"] = trim($arParams["SORT_BY"] ?? '');
        if (empty($arParams["SORT_BY"])){
            $arParams["SORT_BY"] = $this->primaryKey[0];
        }

        if(!in_array($this->primaryKey[0], $arParams['ORM_CLASS_S_FIELDS'])){
            $arParams['ORM_CLASS_S_FIELDS'][] = $this->primaryKey[0];
        }

        if (
            !isset($arParams["SORT_ORDER"])
            || !preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER"])
        )
        {
            $arParams["SORT_ORDER"]="DESC";
        }

        $arParams["DETAIL_URL"] = trim($arParams["DETAIL_URL"] ?? '');
        $arParams["PAGER_TEMPLATE"] = trim($arParams["PAGER_TEMPLATE"] ?? '');
        $arParams["PAGER_SHOW_ALL"] = ($arParams["PAGER_SHOW_ALL"] ?? '') === "Y";

        $arParams["ACTIVE_DATE_FORMAT"] = trim($arParams["ACTIVE_DATE_FORMAT"] ?? '');
        if (empty($arParams["ACTIVE_DATE_FORMAT"]))
        {
            $arParams["ACTIVE_DATE_FORMAT"] = $DB->DateFormatToPHP(\CSite::GetDateFormat("SHORT"));
        }

        $arParams["DISPLAY_TOP_PAGER"] = ($arParams["DISPLAY_TOP_PAGER"] ?? '') === "Y";
        $arParams["DISPLAY_BOTTOM_PAGER"] = ($arParams["DISPLAY_BOTTOM_PAGER"] ?? '') !== "N";
        $arParams["PAGER_TITLE"] = trim($arParams["PAGER_TITLE"] ?? '');
        $arParams["PAGER_SHOW_ALWAYS"] = ($arParams["PAGER_SHOW_ALWAYS"] ?? '') === "Y";

        $arParams["MESSAGE_404"] ??= '';
        $arParams["SET_STATUS_404"] ??= 'N';
        $arParams["SHOW_404"] ??= 'N';
        $arParams["FILE_404"] ??= '';

        $arParams["PAGE_HEADER"] ??= '';
        
        return $arParams;
    }

    public function executeComponent()
    {
        // подключаем языковой файл
        $this->includeComponentLang('class.php');
        global $APPLICATION;

        try{
            $this->checkModules();
        }catch(\Exception $e){
            $this->arResult['ERR_MESSAGES'][] = $e->getMessage();
        }

        $offset = '';
        if($this->arParams["DISPLAY_TOP_PAGER"] || $this->arParams["DISPLAY_BOTTOM_PAGER"]){
            // создаем объект пагинации
            $nav = new UI\PageNavigation("pager");
            $nav->allowAllRecords(true)
                    ->setPageSize($this->arParams["ELEMENTS_COUNT"])
                    ->initFromUri();

            $offset = $nav->getOffset();
        }


        $cacheID = md5(serialize($this->arParams).strval($offset));
        if($this->startResultCache(false, $cacheID)){           

            $arSelect = [$this->primaryKey[0]];
            foreach($this->arParams['ORM_CLASS_S_FIELDS'] as $fVal){
                $arSelect[] = $fVal;
            }
            

            if(!empty($this->arParams['ORM_CLASS_R_FIELDS'])){
                foreach($this->arParams['ORM_CLASS_R_FIELDS'] as $rField){
                    $rFieldKey = $rField.$this->fieldSuffix;
                    $rFieldVal = $rField.'.*';
                    $arSelect[$rFieldKey] = $rFieldVal;
                }
                
            }

            $arOrder = [
                $this->arParams["SORT_BY"] => $this->arParams["SORT_ORDER"]
            ];

            $servObj = new ServiceActions($this->arParams['ORM_CLASS_NAME']);
            $arRelationFields = $servObj->getRelationFields('full');

            $this->arrAllFieldsDesc = $servObj->getScalarFields('full');

            foreach($arRelationFields as $key => $arRelationField){
               if(!empty($arRelationField['REFERENCE_CLASS']) && in_array($key, $this->arParams['ORM_CLASS_R_FIELDS'])){
                    $refObj = new ServiceActions($arRelationField['REFERENCE_CLASS']);
                    $prefix = $key.$this->fieldSuffix;
                    $arRefScalarFields = $refObj->getScalarFields('full', 'view', $prefix);
               }
               
               if(is_array($arRefScalarFields)){
                    $this->arrAllFieldsDesc += $arRefScalarFields;
               }               

            }

            $arHeaderTitles = [];            
            foreach($this->arrAllFieldsDesc as $key=>$arField){
                $arHeaderTitles[$key] = $arField['TITLE'];
            }

            $this->arResult['HEADER_TITLES'] = $arHeaderTitles;
            
            $curClassName = ServiceActions::getFtmOrmClass($this->arParams['ORM_CLASS_NAME']);
            $curEntity = $curClassName::getEntity();
            $query = new Entity\Query($curEntity);

            $query->setSelect($arSelect);      
            $query->setOrder($arOrder);

            $query->setFilter($this->arrFilter);

            $query->setOffset($nav->getOffset());
            $query->setLimit($nav->getLimit());

            $query->countTotal(true);

            $elementsRes = $query->exec();
            $countRes = $elementsRes->getCount();

            if($countRes <= 0){
                $this->abortResultCache();
                Iblock\Component\Tools::process404(
                    trim($this->arParams["MESSAGE_404"]) ?: GetMessage("T_NEWS_NEWS_NA")
                    ,true
                    ,$this->arParams["SET_STATUS_404"] === "Y"
                    ,$this->arParams["SHOW_404"] === "Y"
                    ,$this->arParams["FILE_404"]
                );
                return;
            }
            
            $this->arResult['OUR_TEAM_NAME'] = ServiceActions::getTeamName();
            $this->arResult['OUR_TEAM_CITY'] = ServiceActions::getTeamCity();

            $nav->setRecordCount($countRes);

            $arItems = [];

            $this->arResult['ITEMS'] = [];

            while($arR = $elementsRes->fetch()){
               

                $id = (int) $arR[$this->primaryKey[0]];
                if(count($this->primaryKey) > 1){
                    $arItemsMulti[$id][] = $arR;
                }
                else{
                    $arItems[$id] = $arR;
                }              
            }

            if(count($this->primaryKey) > 1){
                $i = 0;
                foreach($arItemsMulti as $key=>$arItems){
                    foreach($arItems as $key=>$arItem){
                        $arFields = [];
                        foreach($arItem as $fieldName => $fieldValue){
                            $arField = [];
                            $arField['NAME'] = $fieldName;
                            if(!is_object($fieldValue)){                       
                                $arField['VALUE'] = $fieldValue;
                                if(array_key_exists($fieldName, $this->arrAllFieldsDesc)){                            
                                    $arField['TITLE'] = $this->arrAllFieldsDesc[$fieldName]['TITLE'];                            
                                }
                            }
                            else{
                                if(array_key_exists($fieldName, $this->arrAllFieldsDesc)){
                                    $arField['TITLE'] = $this->arrAllFieldsDesc[$fieldName]['TITLE'];
    
                                    $curFieldsParentClass = $this->arrAllFieldsDesc[$fieldName]['PARENT_CLASS'];
    
                                    if($curFieldsParentClass == 'DatetimeField' || $curFieldsParentClass == 'DateField'){
                                        $arField['VALUE'] =  $fieldValue->format($this->arParams["ACTIVE_DATE_FORMAT"]);
                                    }
                                }                       
                            }
                            $arFields[$fieldName] = $arField;
                            
                        }                         
                        
                        $this->arResult['ITEMS'][$i] = $arFields;
                        $this->arResult['ITEMS'][$i]['DETAIL_PAGE_URL'] = str_replace('#ELEMENT_ID#', $key, $this->arParams['DETAIL_URL']);                       
                    } 
                    $i++;
                }
            }
            else{
                $i = 0;
                foreach($arItems as $key=>$arItem){
                    $arFields = [];
                    foreach($arItem as $fieldName => $fieldValue){
                        $arField = [];
                        $arField['NAME'] = $fieldName;
                        if(!is_object($fieldValue)){                       
                            $arField['VALUE'] = $fieldValue;
                            if(array_key_exists($fieldName, $this->arrAllFieldsDesc)){                            
                                $arField['TITLE'] = $this->arrAllFieldsDesc[$fieldName]['TITLE'];                            
                            }
                        }
                        else{
                            if(array_key_exists($fieldName, $this->arrAllFieldsDesc)){
                                $arField['TITLE'] = $this->arrAllFieldsDesc[$fieldName]['TITLE'];

                                $curFieldsParentClass = $this->arrAllFieldsDesc[$fieldName]['PARENT_CLASS'];

                                if($curFieldsParentClass == 'DatetimeField' || $curFieldsParentClass == 'DateField'){
                                    $arField['VALUE'] =  $fieldValue->format($this->arParams["ACTIVE_DATE_FORMAT"]);
                                }
                            }                       
                        }
                        $arFields[$fieldName] = $arField;
                        
                    }                
                    $this->arResult['ITEMS'][$i] = $arFields;
                    $this->arResult['ITEMS'][$i]['DETAIL_PAGE_URL'] = str_replace('#ELEMENT_ID#', $key, $this->arParams['DETAIL_URL']);
                    $i++;
                } 
            }
            
            $this->arResult['NAV_OBJECT'] = $nav;

            // передаем эти данные в шаблон
            $this->includeComponentTemplate();
        }

        if(!empty($this->arResult['ITEMS']) && !empty($this->arParams['PAGE_HEADER'])){            
            $APPLICATION->SetTitle($this->arParams['PAGE_HEADER']);
        }

    }
}