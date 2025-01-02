<?php

use \Bitrix\Main\UI;
use \Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Cookie;
use \Bitrix\Main\Localization\Loc;

use Mattweb\Ftmorm;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class MatchesListComponent extends CBitrixComponent
{
    protected $arFilter;
    public static function getTeamPlayerType(string $key) :string
    {
        $arTeamComposition = [
            'GOALKEEPER' => Loc::GetMessage('GOALKEEPER_TITLE'),
            'DEFENDER' => Loc::GetMessage('DEFENDER_TITLE'),
            'FORWARD' => Loc::GetMessage('FORWARD_TITLE'),
            'MIDFIELDER'=> Loc::GetMessage('MIDFIELDER_TITLE'),
        ];

        if(array_key_exists($key, $arTeamComposition)){
            return $arTeamComposition[$key];
        }

        return false;
    }
    
    protected function checkModules()
    {
        if (!Loader::includeModule('mattweb.ftmorm'))
        {
            throw new Main\LoaderException(Loc::getMessage('MATTWEB_FTMORM_MODULE_NOT_INSTALLED'));                        
        }
    }
    
    public function onPrepareComponentParams($arParams){
        $arParams["DETAIL_PAGE_PATH"] = trim($arParams["DETAIL_PAGE_PATH"]);
        
        $arParams["EL_PAGE_COUNT"] = intval($arParams["EL_PAGE_COUNT"]);

        if($arParams["EL_PAGE_COUNT"] <= 0){
            $arParams["EL_PAGE_COUNT"] = 10;
        }
        
		$arParams["PAGENAV_TEMPLATE"] = !empty($arParams["PAGENAV_TEMPLATE"]) ? $arParams["PAGENAV_TEMPLATE"] : '.default';

        if (!empty($arParams["FILTER_NAME"]) && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"])){
            $this->arFilter = $GLOBALS[$arParams["FILTER_NAME"]] ?? [];
            if(!is_array($this->arFilter)){
                $this->arFilter = [];
            }
        }

        return $arParams;
    }

    public function executeComponent()
    {        
        $this->includeComponentLang('class.php');

        try{
            $this->checkModules();
        }catch(\Exception $e){
            $this->arResult['ERR_MESSAGES'][] = $e->getMessage();
        }

        // создаем объект пагинации
        $nav = new UI\PageNavigation("pager");
        $nav->allowAllRecords(true)
                ->setPageSize($this->arParams["EL_PAGE_COUNT"])
                ->initFromUri();

        $offset = $nav->getOffset();

        $cacheID = '';

        if($this->startResultCache(false, $cacheID)){

            $gamesEntity = Ftmorm\GamesTable::getEntity();

            $query = new Entity\Query($gamesEntity);
            $query->setSelect(['LAST_GAME_DATE', 'FIRST_GAME_DATE']);
            $query->registerRuntimeField(null, new Entity\ExpressionField('LAST_GAME_DATE', 'MAX(%s)', ['GAME_DATE']));
            $query->registerRuntimeField(null, new Entity\ExpressionField('FIRST_GAME_DATE', 'MIN(%s)', ['GAME_DATE']));
        
            $gamesRes = $query->exec();
        
            $arGameDates = $gamesRes->fetch();

            $this->arResult['DATE_MATCH_EARLIEST'] = $arGameDates['FIRST_GAME_DATE']->format('d.m.Y');
            $this->arResult['DATE_MATCH_LATEST'] = $arGameDates['LAST_GAME_DATE']->format('d.m.Y');

            $this->arResult['OUR_TEAM_NAME'] = Option::get('mattweb.ftmorm', 'team_name_'.SITE_ID);
            $this->arResult['OUR_TEAM_CITY'] = Option::get('mattweb.ftmorm', 'team_city_name_'.SITE_ID);

            if(empty($this->arResult['OUR_TEAM_NAME']))
                $this->arResult['OUR_TEAM_NAME'] = Ftmorm\GamesTable::OUR_TEAM_NAME;

            if(empty($this->arResult['OUR_TEAM_CITY']))
                $this->arResult['OUR_TEAM_CITY'] = Ftmorm\GamesTable::OUR_TEAM_CITY;
            
            $playersEntity = Ftmorm\PlayersTable::getEntity();
            $query = new Entity\Query($playersEntity);
            $query->setSelect(['ID', 'FIRST_NAME', 'LAST_NAME', 'NICKNAME', 'CITIZENSHIP', 'DOB', 'ROLE']);
            $query->setFilter($this->arFilter);
            $query->setOffset($nav->getOffset());
            $query->setLimit($nav->getLimit());
            $query->countTotal(true);

            $playersRes = $query->exec();
            $nav->setRecordCount($playersRes->getCount());

            while($arR = $playersRes->fetch()){
                $this->arResult['PLAYERS'][$arR['ID']] = [
                    'PLAYER_ID' => $arR['ID'],
                    'PLAYER_FN' => $arR['FIRST_NAME'],
                    'PLAYER_LN' => $arR['LAST_NAME'],
                    'PLAYER_NN' => $arR['NICKNAME'],
                    'PLAYER_CITIZEN' => $arR['CITIZENSHIP'],
                    'PLAYER_DOB' => $arR['DOB']->format('d.m.Y'),
                    'PLAYER_ROLE' => self::getTeamPlayerType($arR['ROLE']),
                    'DETAIL_PAGE_URL' => str_replace('#PL_ID#', $arR['ID'], $this->arParams["DETAIL_PAGE_PATH"]), 
                ];
            }
            
            $this->arResult['NAV_OBJECT'] = $nav;
            $this->includeComponentTemplate();
        }    
    }
};