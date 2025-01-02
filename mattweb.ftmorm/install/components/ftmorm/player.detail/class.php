<?php
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Entity;

use Mattweb\Ftmorm;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class PlayerDetail extends CBitrixComponent
{   
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
        $arParams['ELEMENT_ID_REQ_VAR'] = trim($arParams['ELEMENT_ID_REQ_VAR']);
        
        if(strlen($arParams['ELEMENT_ID_REQ_VAR']) == 0){
            $arParams['ELEMENT_ID_REQ_VAR'] = 'pl_id';
        }        
        
        $arParams['LIST_PAGE_PATH'] = trim($arParams['LIST_PAGE_PATH']);
        if(strlen($arParams['LIST_PAGE_PATH']) == 0){
            $request = Context::getCurrent()->getRequest();
            $curDir  = $request->getRequestedPageDirectory();
            $arParams['LIST_PAGE_PATH'] = $curDir;
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
        
        $request = Context::getCurrent()->getRequest();
        $reqValues = $request->getQueryList();
        
        if(!empty($reqValues[$this->arParams['ELEMENT_ID_REQ_VAR']])){ 
            $plID = intval($reqValues[$this->arParams['ELEMENT_ID_REQ_VAR']]);

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


            $query = new Entity\Query(Ftmorm\PlayersTable::getEntity());
            $query->setSelect(['*']);
            $query->setFilter(['ID' => $plID]);

            $arPlayer = $query->exec()->fetch();

            $this->arResult['PLAYER'] = [
                'ID' => $arPlayer['ID'],
                'FN' => $arPlayer['FIRST_NAME'],
                'LN' => $arPlayer['LAST_NAME'],
                'NN' => $arPlayer['NICKNAME'],
                'CITIZEN' => $arPlayer['CITIZENSHIP'],
                'DOB' => $arPlayer['DOB']->format('d.m.Y'),
                'ROLE' => self::getTeamPlayerType($arPlayer['ROLE']),
            ];

            $query = new Entity\Query(Ftmorm\LineupsTable::getEntity());
            $query->setSelect(['GOAL_SUMM', 'CARDS', 'GM_'=>'GAME', 'TM_NAME'=>'GAME.TEAM.NAME']);
            $query->setGroup('GM_ID');
            $query->setFilter(['PLAYER_ID'=>$plID]);
            $query->registerRuntimeField(null, new Entity\ExpressionField('GOAL_SUMM', 'SUM(%s)', ['GOALS']));

            $gamesRes = $query->exec();

            $this->arResult['PLAYER_TOTAL_GOALS'] = 0;
            $this->arResult['PLAYER_TOTAL_CARDS'] = 0;

            while($arGameRes = $gamesRes->fetch()){
                $this->arResult['PLAYER_GAMES'][$arGameRes['GM_ID']] = [
                    'GM_ID' => $arGameRes['GM_ID'],
                    'GM_TEAM' => $arGameRes['TM_NAME'],
                    'GM_CITY' => (!empty($arR['GM_CITY']) ? $arGameRes['GM_CITY'] : $this->arResult['OUR_TEAM_CITY']),
                    'GM_DATE' => $arGameRes['GM_GAME_DATE']->format('d.m.Y'),
                    'GM_PLAYER_GOALS' => intval($arGameRes['GOAL_SUMM']),
                    'GM_PLAYER_CARDS' => $arGameRes['CARDS'],
                ];
    
                if(intval($arGameRes['GOAL_SUMM']) > 0){
                    $this->arResult['PLAYER_GOALS_INFO'][$arGameRes['GM_ID']] = intval($arGameRes['GOAL_SUMM']);
                }
    
                $this->arResult['PLAYER_TOTAL_GOALS'] += intval($arGameRes['GOAL_SUMM']);
    
                if(!empty($arGameRes['CARDS'])){
                    $gmCardQuant = ($arGameRes['CARDS'] == 'Y2') ? 2 : 1;
                    $this->arResult['PLAYER_TOTAL_CARDS'] += $gmCardQuant;
                    $this->arResult['PLAYER_CARDS_INFO'][$arGameRes['GM_ID']] = $arGameRes['CARDS'];
                }
    
                $this->arResult['PAGE_TITLE'] = Loc::GetMessage('PAGE_TITLE_TMPL', [
                    '#PLAYER_FN#' => $this->arResult['PLAYER']['FN'],
                    '#PLAYER_LN#' => $this->arResult['PLAYER']['LN'],
                    '#DATE_MATCH_EARLIEST#' => $this->arResult['DATE_MATCH_EARLIEST'],
                    '#DATE_MATCH_LATEST#' => $this->arResult['DATE_MATCH_LATEST'],
                ]);

                //dump($arGameRes);
            }

        }else{
            $this->arResult['ERRORS'][] = Loc::GetMessage('MATTWEB_FTMORM_EMPTY_REQUIRED_PARAM');
            $this->arResult['MATCH_TITLE'] = Loc::GetMessage('MATTWEB_FTMORM_ERR_TTL');
        }
        
        $this->includeComponentTemplate();
    }
}

