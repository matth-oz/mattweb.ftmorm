<?php

use \Bitrix\Main\Application;
use \Bitrix\Main\UI;
use \Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Cookie;
use \Bitrix\Main\Localization\Loc;

use Mattweb\Ftmorm;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
class MatchesComponent extends CBitrixComponent{
    protected array $arComponentVariables = [];

	public function onPrepareComponentParams($arParams) {
		$arParams['ELEMENT_ID_REQ_VAR'] = strtoupper($arParams['ELEMENT_ID_REQ_VAR']);
		return $arParams;
	}

	public function executeComponent()
    {
		global $APPLICATION;
		$this->includeComponentLang('class.php');
		
		Loader::includeModule('mattweb.ftmorm');

		if ($this->arParams["SEF_MODE"] === "Y") {
			$componentPage = $this->sefMode();
		}

		if ($this->arParams["SEF_MODE"] != "Y") {
			$componentPage = $this->noSefMode();
		}

		if(!$componentPage){
			if (!defined("ERROR_404")) {
				define("ERROR_404", "Y");
			}
		
			\CHTTP::setStatus("404 Not Found");
						
			if ($APPLICATION->RestartWorkarea()) {
				require(Application::getDocumentRoot()."/404.php");
			}
		}

		
        $this->IncludeComponentTemplate($componentPage);

	}

	
	protected function sefMode()
    {

		global $APPLICATION;

		$arDefaultVariableAliases404 = [];

		$arDefaultUrlTemplates404 = [];

		$arVariables = [];

		$engine = new CComponentEngine($this);

		$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates(
			$arDefaultUrlTemplates404,
			$this->arParams["SEF_URL_TEMPLATES"]
		);

		$arVariableAliases = CComponentEngine::makeComponentVariableAliases(
			$arDefaultVariableAliases404,
			$this->arParams["VARIABLE_ALIASES"]
		);

		$componentPage = $engine->guessComponentPath(
			$this->arParams["SEF_FOLDER"],
			$arUrlTemplates,
			$arVariables
		);

		if ($componentPage == FALSE) {
			$componentPage = 'matches';
		}

		 CComponentEngine::initComponentVariables(
            $componentPage,
            $this->arComponentVariables,
            $arVariableAliases,
            $arVariables
        );

        $this->arResult = [
			"FOLDER" => $this->arParams["SEF_FOLDER"],
			"URL_TEMPLATES" => [
				"matches" => '',
				"detail" => $this->arParams["SEF_URL_TEMPLATES"]['detail']
			],
            "VARIABLES" => $arVariables,
            "ALIASES" => $arVariableAliases
        ];



        return $componentPage;
	}

	 protected function noSefMode()
	 {	
		
		global $APPLICATION;

		$componentPage = "";
        
		$arDefaultVariableAliases = [];
        
        $arVariableAliases = CComponentEngine::makeComponentVariableAliases(
        
            $arDefaultVariableAliases,
        
            $this->arParams["VARIABLE_ALIASES"]
        );
		

        $arVariables = [];
        
        CComponentEngine::initComponentVariables(
            false,
            $this->arComponentVariables,
            $arVariableAliases,
            $arVariables
        );

        $context = Application::getInstance()->getContext();

        $request = $context->getRequest();

        $rDir = $request->getRequestedPageDirectory();

		if ((isset($arVariables["ELEMENT_ID"]) && intval($arVariables["ELEMENT_ID"]) > 0)) {
			$componentPage = "detail";
		}
		else{
			$componentPage = 'matches';
		}

		$this->arResult = [
			"FOLDER" => "",
			"URL_TEMPLATES" => [
				"matches" => htmlspecialcharsbx($APPLICATION->GetCurPage()),
				"detail" => htmlspecialcharsbx($APPLICATION->GetCurPage())."?".$arVariableAliases["ELEMENT_ID"]."=#GM_ID#",
			],
			"VARIABLES" => $arVariables,
			"ALIASES" => $arVariableAliases
		];
		return $componentPage;

	 }
}