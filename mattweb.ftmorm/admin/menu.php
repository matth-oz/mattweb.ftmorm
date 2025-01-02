<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('mattweb.ftmorm'))
{
	return false;
}

return array(
    "parent_menu" => "global_menu_services",
    'section' => '',
	"sort" => 10,
    "text" => Loc::GetMessage("ADM_MENU_HEADER_TXT"),
    "title" => Loc::GetMessage("ADM_MENU_HEADER_TITLE"),
    "url" => "ftmorm_admin_index.php?lang=".LANGUAGE_ID,
	"icon" => "util_menu_icon",
	"page_icon" => "util_page_icon",
	"items_id" => "menu_ftmormadmin",
    "items" => array(
        /*array(
            "text" => Loc::GetMessage("ADM_MENU_FIELDS_SETTINGS_TXT"),
		    "url" => "fields_settings.php?lang=".LANGUAGE_ID,
			"more_url" => array(),
			"title" => Loc::GetMessage("ADM_MENU_FIELDS_SETTINGS_TITLE"), 
        ),*/
        array(
             "text" => Loc::GetMessage("ADM_MENU_TEAMS_LIST_TXT"),
             "url" => "ftmorm_teams_list.php?lang=".LANGUAGE_ID,
             "more_url" => array(),
             "title" => Loc::GetMessage("ADM_MENU_TEAMS_LIST_TXT"), 
         ),
         array(
             "text" => Loc::GetMessage("ADM_MENU_PLAYERS_LIST_TXT"),
             "url" => "ftmorm_players_list.php?lang=".LANGUAGE_ID,
             "more_url" => array(),
             "title" => Loc::GetMessage("ADM_MENU_PLAYERS_LIST_TXT"), 
         ),
         array(
             "text" => Loc::GetMessage("ADM_MENU_GAMES_LIST_TXT"),
             "url" => "ftmorm_matches_list.php?lang=".LANGUAGE_ID,
             "more_url" => array(),
             "title" => Loc::GetMessage("ADM_MENU_GAMES_LIST_TXT"), 
         ),
    ),
);