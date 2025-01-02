<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
use Bitrix\Main,
Bitrix\Main\Loader,
Mattweb\Ftmorm;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule("mattweb.ftmorm");

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");
// CJSCore::Init(["jquery"]);
$APPLICATION->SetTitle('Изучаем ORM');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<div class="adm-info-message">
    <p>В процессе изучения ORM в Битриксе на основе тестовой базы данных был создан этот модуль.</p>
    <p>В тестовой БД хранится информация о матчах вымышленной футбольной команды <b>Cool Players</b> из города <b>Exeter</b> с соперниками.</p>
    <p>База данных была найдена в сети и вполне подходит для изучения взаимодействия с ORM Битрикс на ее основе.</p>

    <p>В таблицах БД хранится инофрмация об игроках (таблица <code>ot_players</code>), командах-соперниках (таблица <code>ot_teams</code>), матчах (таблица <code>ot_games</code>) и об участии игроков в определенных матчах (таблица <code>ot_lineups</code>).</p>
    <p>Игроки не привязаны к командам-соперникам.</p>

    <p>Обращаю внимание, что БД далека от идеала, так как о самой команде и ее игроках информациив БД нет.</p>

    <p>В административной части для взаимодействия с БД написаны скрипты для:</p>

    <ul>
        <li>Вывода списка команд (/admin/ftmorm_teams_list.php)</li>
        <li>Добавления и редактирование команды (/admin/ftmorm_team_edit.php)</li>
        <li>Вывода списка игроков (/admin/ftmorm_players_list.php)</li>
        <li>Добавления и редактирование игрока (/admin/ftmorm_player_edit.php)</li>
        <li>Вывода списка матчей (/admin/ftmorm_matches_list.php)</li>
        <li>Добавления нового матча, добавления и редактирование игроков, участвующих в матче (/admin/ftmorm_match_edit.php)</li>
    </ul>

    <p>Обращаю внимаин, что при добавлении нового матча <span class="required">сначала добавляются данные о нем</span>, а потом об игроках, участвующих в матче</p>
</div>




<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>