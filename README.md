# Модуль «Football Team Matches ORM Study» (mattweb.ftmorm) для БУС
<p>Этот модуль был создан на основе тестовой базы данных в процессе изучения ORM Битрикс.</p>
<p>В тестовой БД хранится информация о матчах вымышленной футбольной команды <b>Cool Players</b> из города <b>Exeter</b> с соперниками.</p>
<p>База данных была найдена в сети и вполне подходит для изучения взаимодействия с ORM Битрикс на ее основе.</p>

<p>В таблицах БД хранится инофрмация об игроках (таблица <code>ot_players</code>), командах-соперниках (таблица <code>ot_teams</code>), матчах (таблица <code>ot_games</code>) и об участии игроков в определенных матчах (таблица <code>ot_lineups</code>).</p>
<p>Игроки не привязаны к командам-соперникам.</p>

<p>Обращаю внимание, что БД далека от идеала, так как о самой команде и ее игроках информациив БД нет.</p>

<p>Для установки модуля скопируйте папку <b>mattweb.ftmorm</b> в /local/modules/ в структуре вашего сайта. Далее установите модуль в админке: «Marketplace» → «Установленные решения».</p>

<h3>Компоненты модуля:</h3>
<ul>
    <li>ftmorm:matches.list - список матчей за весь период с постраничной навигацией, фильтром и сортировкой</li>
    <li>ftmorm:match.detail - детальная страница матча</li>
    <li>ftmorm:players.list - список игроков</li>
    <li>ftmorm:ftmorm.filter - фильтр элементов (например, списка игроков)</li>
    <li>ftmorm:player.detail - детальная страница игрока</li>
    <li>ftmorm:matches - комплексный компонент Матчи</li>
</ul>

<h3>Скрипты в административной части:</h3>

<ul>
    <li>Вывода списка команд (/admin/ftmorm_teams_list.php)</li>
    <li>Добавления и редактирование команды (/admin/ftmorm_team_edit.php)</li>
    <li>Вывода списка игроков (/admin/ftmorm_players_list.php)</li>
    <li>Добавления и редактирование игрока (/admin/ftmorm_player_edit.php)</li>
    <li>Вывода списка матчей (/admin/ftmorm_matches_list.php)</li>
    <li>Добавления нового матча, добавления и редактирование игроков, участвующих в матче (/admin/ftmorm_match_edit.php)</li>
</ul>

<p>Обращаю внимаин, что при добавлении нового матча <span class="required">сначала добавляются данные о нем</span>, а потом об игроках, участвующих в матче</p>
<p>Примеры использования модуля находятся в <a href="https://github.com/matth-oz/mattweb.ftmorm_examples">этом репозитории</a></p>