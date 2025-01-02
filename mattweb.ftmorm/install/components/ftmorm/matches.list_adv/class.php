<?php

use \Bitrix\Main\UI;
use \Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Cookie;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

use Mattweb\Ftmorm;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class MatchesListComponent extends CBitrixComponent
{
    protected $arFilter = [];

    protected function checkModules()
    {
        if (!Loader::includeModule('mattweb.ftmorm'))
        {
            throw new Main\LoaderException(Loc::getMessage('MATTWEB_FTMORM_MODULE_NOT_INSTALLED'));
        }
    }
    
    public function buildSortParamUrl(string $curPage, array $sort_params = array()): string
    {
        $resUrl = '';
        $queryString = $_SERVER['QUERY_STRING'];
        $pattern = '/sort=(game_date|gm_city|tm_name)&ord=(asc|desc)/';
        
        if(!empty($sort_params)){
            $sort = key($sort_params);
            $ord = $sort_params[$sort];

            if(strlen($queryString) == 0){
                $resUrl .= $curPage.'?sort='.$sort.'&ord='.$ord;
            }
            else{
                preg_match($pattern, $queryString, $matches, PREG_OFFSET_CAPTURE);
            
                if(!empty($matches[0][0])){
                    $replaceStr = 'sort='.$sort.'&ord='.$ord;
                    $replacePattern = '/'.$matches[0][0].'/';
                
                    if($matches[0][0] != $replaceStr){
                        $queryString = preg_replace($replacePattern, $replaceStr, $queryString);
                    }
                    $resUrl .= $curPage.'?'.$queryString;
                }
                else{
                    $resUrl .= $curPage.'?'.$queryString.'&sort='.$sort.'&ord='.$ord;
                }
            }
        }
        else{
            if(strlen($queryString) == 0){
                $resUrl .= $curPage.'?sort=clear';
            }
            else{
                preg_match($pattern, $queryString, $matches, PREG_OFFSET_CAPTURE);
                if(!empty($matches[0][0])){
                    $replaceStr = 'sort=clear';
                    $replacePattern = '/'.$matches[0][0].'/';
                    
                    $queryString = preg_replace($replacePattern, $replaceStr, $queryString);
                    $resUrl .= $curPage.'?'.$queryString;
                }
            }
        }

        return $resUrl;
    }
    
    
    public function clearSortParamUrl(string $curPage): string
    {
        $resUrl = '';
        $queryString = $_SERVER['QUERY_STRING'];
        $pattern = '/&?sort=clear&?(pager=page\-[0-9]{1,})?/';

        preg_match($pattern, $queryString, $matches, PREG_OFFSET_CAPTURE);

        if(!empty($matches[0][0])){
            $replaceStr = '';
            $replacePattern = '/'.$matches[0][0].'/';

            $queryString = preg_replace($replacePattern, $replaceStr, $queryString);

            if(strlen($queryString) > 0){
                $resUrl .= "{$curPage}?{$queryString}";
            }
            else{
                $resUrl .= $curPage;
            }

        }

        return $resUrl;
    }
    
    public function onPrepareComponentParams($arParams){
        $arParams["DETAIL_PAGE_PATH"] = trim($arParams["DETAIL_PAGE_PATH"]);
        
        $arParams["EL_PAGE_COUNT"] = intval($arParams["EL_PAGE_COUNT"]);

        if($arParams["EL_PAGE_COUNT"] <= 0){
            $arParams["EL_PAGE_COUNT"] = 10;
        }

        $arParams["SORT_COOKIE_LD"] = intval($arParams["SORT_COOKIE_LD"]);
        if($arParams["SORT_COOKIE_LD"] <= 0){
            $arParams["SORT_COOKIE_LD"] = 5;
        }

        if (!empty($arParams["FILTER_NAME"]) && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"])){
            $this->arFilter = $GLOBALS[$arParams["FILTER_NAME"]] ?? [];
        }

        define('SORT_COOKIE_LT', time() + 86400 * $arParams["SORT_COOKIE_LD"]);

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

        global $APPLICATION;
        $this->arResult['CUR_PAGE'] = $APPLICATION->getCurPage(); //$curPage

        $server = Context::getCurrent()->getServer();
        $request = Context::getCurrent()->getRequest();
        $reqValues = $request->getQueryList();


        // расчитываем правильные номера в таблице на всех страницах кроме первой
        $pager = $reqValues['pager'];
        $pageNum = 0;
        if(!empty($pager) && $pager != 'page-all'){
            $arPager = explode('-', $pager);
            $pageNum = intval($arPager[1]) - 1;
        }
        
        // сортировка
        $this->arResult['DATA_COOKIE']['SORT'] = $request->getCookie('sortData'); // $sortDataCookie
        $this->arResult['DATA_COOKIE']['ORD'] = $request->getCookie('sortOrd'); // $sortOrdCookie
        
        $response = Context::getCurrent()->getResponse();

        // удаляем сортировку из COOKIE
        if($reqValues['sort'] == 'clear'
            && !empty($this->arResult['DATA_COOKIE']['SORT']) && !empty($this->arResult['DATA_COOKIE']['ORD'])){
            $response->addCookie(
                new Cookie('sortData', $this->arResult['DATA_COOKIE']['SORT'], time()-3600)
            );

            $response->addCookie(
                new Cookie('sortOrd', $this->arResult['DATA_COOKIE']['ORD'], time()-3600)
            );

            $locHref = $this->clearSortParamUrl($this->arResult['CUR_PAGE']);

            $response->addHeader('Location', $locHref);
            $response->flush();
        }

        // сохраняем сортировку в COOKIE
        // обязательно перезагружаем страницу
        if((!empty($reqValues['sort'] && !empty($reqValues['ord']))
        && ($this->arResult['DATA_COOKIE']['SORT'] != $reqValues['sort'] || $this->arResult['DATA_COOKIE']['ORD'] != $reqValues['ord']))){

            $response->addCookie(
                new Cookie('sortData', $reqValues['sort'], SORT_COOKIE_LT)
            );

            $response->addCookie(
                new Cookie('sortOrd', $reqValues['ord'], SORT_COOKIE_LT)
            );

            $response->addHeader('Location', $server->getRequestUri());
            $response->flush();
        }
        
        if(!is_null($this->arResult['DATA_COOKIE']['SORT']) && !is_null($this->arResult['DATA_COOKIE']['ORD'])){
            $filterKey = match($this->arResult['DATA_COOKIE']['SORT']){
                'game_date' => 'GM_GAME_DATE',
                'gm_city' => 'GM_CITY',
                'tm_name' => 'GM_OPPONENT_NAME'
            };            
    
            $arOrder[$filterKey] = $this->arResult['DATA_COOKIE']['ORD'];
        }
        else{
            $arOrder = ['GAME_ID' => 'desc'];
        }

        // создаем объект пагинации
        $nav = new UI\PageNavigation("pager");
        $nav->allowAllRecords(true)
                ->setPageSize($this->arParams["EL_PAGE_COUNT"])
                ->initFromUri();

        $offset = $nav->getOffset();

        $cacheID = md5(serialize($this->arParams).serialize($arOrder).strval($offset));

        if($this->startResultCache(false, $cacheID)){

            $this->arResult['OUR_TEAM_NAME'] = Option::get('mattweb.ftmorm', 'team_name_'.SITE_ID);
            $this->arResult['OUR_TEAM_CITY'] = Option::get('mattweb.ftmorm', 'team_city_name_'.SITE_ID);

            if(empty($this->arResult['OUR_TEAM_NAME']))
                $this->arResult['OUR_TEAM_NAME'] = Ftmorm\GamesTable::OUR_TEAM_NAME;

            if(empty($this->arResult['OUR_TEAM_CITY']))
                $this->arResult['OUR_TEAM_CITY'] = Ftmorm\GamesTable::OUR_TEAM_CITY;

            // формируем данные для заполнения полей фильтра в форме
            // вычисляем правильные даты первого и последнего матча для заголовка при пагинации
            $this->arResult['FILTER_DATA']['GM_CITY']['team_city'] = $this->arResult['OUR_TEAM_CITY'];


            $gamesRes = Ftmorm\GamesTable::getList([
                'select' => ['CITY', 'GAME_DATE'],
            ]);

            $arGameDates = [];

            $k=0;
            while($arGmData = $gamesRes->fetch()){
                if(!empty($arGmData['CITY']) && !in_array($arGmData['CITY'], $this->arResult['FILTER_DATA']['GM_CITY'])){
                    $nk = 'city_'.$k;
                    $this->arResult['FILTER_DATA']['GM_CITY'][$nk] = $arGmData['CITY'];
                }

                $arGameDates[$k] = $arGmData['GAME_DATE']->format("d.m.Y");
                $k++;
            }

            usort($arGameDates, function($a, $b) {
                return strtotime($a) - strtotime($b);
            });


            $arMatchesFK = array_key_first($arGameDates);
            $arMatchesLK = array_key_last($arGameDates);

            $this->arResult['DATE_MATCH_EARLIEST'] = $arGameDates[$arMatchesFK];
            $this->arResult['DATE_MATCH_LATEST'] = $arGameDates[$arMatchesLK];

            $LineUpsRes = Ftmorm\LineupsTable::getList([
                'select' => ['GAME_ID', 'GOAL_SUMM', 'GM_'=>'GAME', 'GM_OPPONENT_NAME'=>'GAME.TEAM.NAME'],
                'order' => $arOrder,
                'group' => ['GAME_ID'],
                'filter' => $this->arFilter,
                'runtime' => [new Entity\ExpressionField('GOAL_SUMM', 'SUM(%s)', ['GOALS'])],
                'offset' => $nav->getOffset(),
                'limit' => $nav->getLimit(),
                'count_total' => true,
            ]);
            
            $nav->setRecordCount($LineUpsRes->getCount());

            $i = ($pageNum > 0) ? ($this->arParams["EL_PAGE_COUNT"] * $pageNum + 1) : 1;

            while($arR = $LineUpsRes->fetch()){
                
                $this->arResult['MATCHES'][$arR['GM_ID']] = [
                    'ORD_NUMBER' => $i,
                    'ID' => $arR['GM_ID'],
                    'TM_NAME' => $arR['GM_OPPONENT_NAME'],
                    'CITY' => (!empty($arR['GM_CITY']) ? $arR['GM_CITY'] : $this->arResult['OUR_TEAM_CITY']),
                    'GAME_DATE' => $arR['GM_GAME_DATE']->format("d.m.Y"),
                    'OUR_GOALS' => intval($arR['GM_GOALS']),
                    'OPPONENT_GOALS' => (intval($arR['GOAL_SUMM']) + intval($arR['GM_OWN'])),
                    'AUTO_GOALS' => intval($arR['GM_OWN']),
                    'DETAIL_PAGE_URL' => str_replace('#GM_ID#', $arR['GM_ID'], $this->arParams["DETAIL_PAGE_PATH"]), 
                ];

                $opponentGoals = intval($arR['GOAL_SUMM']) + intval($arR['GM_OWN']);
                $ourCommandGoals = intval($arR['GM_GOALS']);

                if($opponentGoals != $ourCommandGoals){
                    $gmRes = ($opponentGoals > $ourCommandGoals) ? 'L' : 'W';
                }
                else{
                    $gmRes = 'D'; // Draw - ничья
                }

                $this->arResult['MATCHES'][$arR['GM_ID']]['GAME_RESULT'] = $gmRes;

                $score = '';
                if(!empty($arR['GM_CITY']))
                    $score .= $opponentGoals.' : '.$ourCommandGoals;
                else
                    $score .= $ourCommandGoals.' : '.$opponentGoals;

                $this->arResult['MATCHES'][$arR['GM_ID']]['GAME_SCORE'] = $score;

                $i++;
            }

            $this->arResult['FILTER_ACTION_URL'] = $server->getRequestUri();
            $this->arResult['NAV_OBJECT'] = $nav;

            $this->includeComponentTemplate();

        }

        global $APPLICATION;
        $APPLICATION->SetTitle(Loc::GetMessage(
            'COMP_PAGE_HDR', 
            ['#OUR_TEAM_NAME#' => $this->arResult['OUR_TEAM_NAME'], 
            '#DATE_MATCH_EARLIEST#' => $this->arResult['DATE_MATCH_EARLIEST'],
            '#DATE_MATCH_LATEST#' => $this->arResult['DATE_MATCH_LATEST']]
        ));

        $APPLICATION->SetPageProperty( 
            'title',
            Loc::GetMessage(
                'COMP_PAGE_HDR', 
                ['#OUR_TEAM_NAME#' => $this->arResult['OUR_TEAM_NAME'], 
                '#DATE_MATCH_EARLIEST#' => $this->arResult['DATE_MATCH_EARLIEST'],
                '#DATE_MATCH_LATEST#' => $this->arResult['DATE_MATCH_LATEST']]
            )
        );
    }
};