<?php
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
use \Bitrix\Main\Entity;

use Mattweb\Ftmorm;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class MatchDetail extends CBitrixComponent
{
    protected function checkModules()
    {
        if (!Loader::includeModule('mattweb.ftmorm'))
        {
            throw new Main\LoaderException(Loc::getMessage('MATTWEB_FTMORM_MODULE_NOT_INSTALLED'));
        }
    }
    
    
    public function onPrepareComponentParams($arParams){
        $arParams['ELEMENT_ID'] = intval($arParams['ELEMENT_ID']);
        
        $arParams['ELEMENT_ID_REQ_VAR'] = trim($arParams['ELEMENT_ID_REQ_VAR']);
        
        if(strlen($arParams['ELEMENT_ID_REQ_VAR']) == 0){
            $arParams['ELEMENT_ID_REQ_VAR'] = 'gm_id';
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

        if(!empty($reqValues[$this->arParams['ELEMENT_ID_REQ_VAR']]) || $this->arParams['ELEMENT_ID'] != 0){

            if($this->arParams['ELEMENT_ID'] != 0){
                $gmID = $this->arParams['ELEMENT_ID'];
            }
            elseif(!empty($reqValues[$this->arParams['ELEMENT_ID_REQ_VAR']])){
                $gmID = intval($reqValues[$this->arParams['ELEMENT_ID_REQ_VAR']]);
            }

            $this->arResult['OUR_TEAM_NAME'] = Ftmorm\GamesTable::OUR_TEAM_NAME;
            $this->arResult['OUR_TEAM_CITY'] = Ftmorm\GamesTable::OUR_TEAM_CITY;

            $this->arResult['MATCH_GOALS'] = [];
            
            $arFilter = ['GAME_ID'=> $gmID];

            $LineUpsRes = Ftmorm\LineupsTable::getList([
                'select' => ['GAME_ID', 'PL_'=>'PLAYER', 'START', 'TIME_IN', 'GOALS', 'CARDS', 'GM_'=>'GAME', 'GM_OPPONENT_NAME'=>'GAME.TEAM.NAME'],
                'filter' => $arFilter,
                'count_total' => true,
            ]);

            $resQuant = $LineUpsRes->getCount();

            if($resQuant > 0){

            }
            else{
                $this->arResult['ERRORS'][] = Loc::GetMessage('MATTWEB_FTMORM_EMPTY_RESULT'); 
            }
            $i=0;
            $opponentGoals = 0;
            while($arR = $LineUpsRes->fetch()){
                if($i == 0){
                    $this->arResult['MATCH'] = [
                        'ID' => $arR['GAME_ID'],
                        'TM_NAME' => $arR['GM_OPPONENT_NAME'],
                        'CITY' => (!empty($arR['GM_CITY']) ? $arR['GM_CITY'] : $this->arResult['OUR_TEAM_CITY']),
                        'GAME_DATE' => $arR['GM_GAME_DATE']->format("d.m.Y"),
                        'OUR_GOALS' => intval($arR['GM_GOALS']),
                        'OPPONENT_GOALS' => 0,
                        'AUTO_GOALS' => intval($arR['GM_OWN']),
                    ];
    
                    if($this->arResult['MATCH']['CITY'] == $this->arResult['OUR_TEAM_CITY']){
                        $this->arResult['MATCH_TITLE'] = 'Матч '.$this->arResult['OUR_TEAM_NAME'].' - '.$this->arResult['MATCH']['TM_NAME'];
                    }
                    else{
                        $this->arResult['MATCH_TITLE'] = 'Матч '.$this->arResult['MATCH']['TM_NAME'].' - '.$this->arResult['OUR_TEAM_NAME'];
                    }
    
                }
    
                $startType = ($arR['START'] == 'B') ? 'BASE' : 'RESERVE';
    
                $this->arResult['MATCH_PLAYERS'][$startType][$arR['PL_ID']] = [
                    'PLAYER_ID' => $arR['PL_ID'],
                    'PLAYER_FN' => $arR['PL_FIRST_NAME'],
                    'PLAYER_LN' => $arR['PL_LAST_NAME'],
                    'PLAYER_NN' => $arR['PL_NICKNAME'],
                    'PLAYER_CITIZEN' => $arR['PL_CITIZENSHIP'],
                    'PLAYER_DOB' => $arR['PL_DOB']->format('d.m.Y'),
                    'START' => $arR['START'],
                    'TIME_IN' => $arR['TIME_IN'],
                    'PLAYER_ROLE' => ServiceActions::getTeamPlayerType($arR['PL_ROLE']),
                ];
                
                
                if($arR['GOALS'] > 0){
                    $opponentGoals += $arR['GOALS'];
                    if(array_key_exists($arR['PL_ID'], $this->arResult['MATCH_GOALS'])){
                        $this->arResult['MATCH_GOALS'][$arR['PL_ID']] = $this->arResult['MATCH_GOALS'][$arR['PL_ID']] + $arR['GOALS'];
                    }
                    else{
                        $this->arResult['MATCH_GOALS'][$arR['PL_ID']] = $arR['GOALS'];
                    }
                }
    
                if(!empty($arR['CARDS'])){
                    $this->arResult['MATCH_CARDS'][$arR['PL_ID']][] = $arR['CARDS'];
                }

                $i++;
            }

            $this->arResult['MATCH']['OPPONENT_GOALS'] = $opponentGoals + $this->arResult['MATCH']['AUTO_GOALS'];

            if($this->arResult['MATCH']['CITY'] == $this->arResult['OUR_TEAM_CITY']){
                $this->arResult['MATCH_SCORE'] = $this->arResult['MATCH']['OUR_GOALS'].' : '.$this->arResult['MATCH']['OPPONENT_GOALS'];
            }
            else{
                $this->arResult['MATCH_SCORE'] = $this->arResult['MATCH']['OPPONENT_GOALS'].' : '.$this->arResult['MATCH']['OUR_GOALS'];
            }
    
            if($this->arResult['MATCH']['OUR_GOALS'] > $this->arResult['MATCH']['OPPONENT_GOALS']){
                $this->arResult['MATCH_RESULT_TXT'] = Loc::getMessage('MATCH_RESULT_TXT_WIN');
                $this->arResult['MATCH_RESULT_CODE'] = 'W';
            }
            elseif($this->arResult['MATCH']['OUR_GOALS'] < $this->arResult['MATCH']['OPPONENT_GOALS']){
                $this->arResult['MATCH_RESULT_TXT'] = Loc::getMessage('MATCH_RESULT_LOSE');
                $this->arResult['MATCH_RESULT_CODE'] = 'L';
            }
            else{
                $this->arResult['MATCH_RESULT_TXT'] = Loc::getMessage('MATCH_RESULT_DRAW');
                $this->arResult['MATCH_RESULT_CODE'] = 'D';
            }
        }
        else{
            $this->arResult['ERRORS'][] = Loc::GetMessage('MATTWEB_FTMORM_EMPTY_REQUIRED_PARAM');
            $this->arResult['MATCH_TITLE'] = Loc::GetMessage('MATTWEB_FTMORM_ERR_TTL');
        }

        $this->includeComponentTemplate();
        
        global $APPLICATION;
        $APPLICATION->SetTitle($this->arResult['MATCH_TITLE']);

    }
};