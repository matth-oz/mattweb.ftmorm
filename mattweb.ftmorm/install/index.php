<?

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config as Conf;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity\Base;
use \Bitrix\Main\Application;
use \Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);
Class mattweb_ftmorm extends CModule
{
    var $exclusionAdminFiles;
    var $errors;

    function __construct(){
        $arModuleVersion = array();
        include(__DIR__."/version.php");

        $this->exclusionAdminFiles=array(
            '..',
            '.',
            'menu.php',
            'operation_description.php',
            'task_description.php'
        );

        $this->MODULE_ID = 'mattweb.ftmorm';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('MATTWEB_FTMORM_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MATTWEB_FTMORM_MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('MATTWEB_FTMORM_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('MATTWEB_FTMORM_PARTNER_URI');

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';

    }

    //Проверяем что система поддерживает D7
    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    //Определяем место размещения модуля
    public function GetPath($notDocumentRoot=false)
    {
        if($notDocumentRoot)
            return str_ireplace(Application::getDocumentRoot(),'',dirname(__DIR__));
        else
            return dirname(__DIR__);
    }

    function GetModuleRightList()
    {
        return array(
            "reference_id" => array("D","K","S","W"),
            "reference" => array(
                "[D] ".Loc::getMessage("MATTWEB_FTMORM_DENIED"),
                "[K] ".Loc::getMessage("MATTWEB_FTMORM_READ_COMPONENT"),
                "[S] ".Loc::getMessage("MATTWEB_FTMORM_WRITE_SETTINGS"),
                "[W] ".Loc::getMessage("MATTWEB_FTMORM_FULL"))
        );
    }

    function InstallDB(){
        global $DB, $APPLICATION;
        $this->errors = false;

        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/local/modules/mattweb.ftmorm/install/db/mysql/install.sql");

        if(!$this->errors){

            $query = "SELECT COUNT(`ID`) AS GAMES_ROWS FROM ot_games";
            $res = $DB->Query($query);
            $arRes = $res->Fetch();

            if($arRes['GAMES_ROWS'] <= 0){
                $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/local/modules/mattweb.ftmorm/install/db/mysql/install_data.sql");
            }            
        }

        if($this->errors !== false){
            $APPLICATION->ThrowException(implode('', $this->errors));
            return false;
        }

        return true;
    
    }

    function UnInstallDB(){
        global $DB, $APPLICATION;
		$this->errors = false;
        
        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/local/modules/mattweb.ftmorm/install/db/mysql/uninstall.sql");

        if($this->errors !== false){
            $APPLICATION->ThrowException(implode('', $this->errors));
            return false;
        }

        return true;
    }

    function InstallEvents(){return true;}
    function UnInstallEvents(){return true;}

    function InstallFiles($arParams = array()){

        if(!is_dir($_SERVER["DOCUMENT_ROOT"]."/local/components"))
            mkdir($_SERVER["DOCUMENT_ROOT"]."/local/components", 0777, true);
        
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/mattweb.ftmorm/install/components", $_SERVER["DOCUMENT_ROOT"]."/local/components", true, true);

        if(!is_dir($_SERVER["DOCUMENT_ROOT"]."/local/templates"))
        mkdir($_SERVER["DOCUMENT_ROOT"]."/local/templates", 0777, true);

        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/mattweb.ftmorm/install/templates", $_SERVER["DOCUMENT_ROOT"]."/local/templates", true, true);

        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/mattweb.ftmorm/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);

        return true;    
    }

    function UnInstallFiles(){
        if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/components/ftmorm")){
			DeleteDirFilesEx("/local/components/ftmorm");
        }

        if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/templates/clear")){
            DeleteDirFilesEx("/local/templates/clear");
        }

        return true;
    }

    function DoInstall(){
        global $USER, $APPLICATION;

        if ($USER->IsAdmin())
		{
            if ($this->isVersionD7()){
                // создание таблиц и загрузка данных
                $this->InstallDB();
                // создание и регистрация событий
                $this->InstallEvents();
                // копирование файлов
                $this->InstallFiles();

                // регистрация модуля в системе
                \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
            }
            else
            {
                $APPLICATION-ThrowException(Loc::getMessage('MATTWEB_FTMORM_INSTALL_ERROR_VERSION'));
            }
        }       

        $APPLICATION->IncludeAdminFile(Loc::getMessage("MATTWEB_FTMORM_INSTALL_TITLE"), $this->GetPath()."/install/step.php");
    }

    function DoUninstall(){
        global $USER, $APPLICATION;

        if ($USER->IsAdmin())
		{
            $context = Application::getInstance()->getContext();
            $request = $context->getRequest();

            //var_dump($request['step'] < 2);

            if($request['step'] < 2){
                $APPLICATION->IncludeAdminFile(Loc::getMessage("MATTWEB_FTMORM_UNINSTALL_TITLE"), $this->GetPath()."/install/unstep1.php");
            }
            elseif ($request['step'] == 2){
                $this->UnInstallFiles();
                $this->UnInstallEvents();

                if($request['savedata'] !=  'Y')
                    $this->UnInstallDB();

                \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

                $APPLICATION->IncludeAdminFile(Loc::getMessage("MATTWEB_FTMORM_UNINSTALL_TITLE"), $this->GetPath()."/install/unstep2.php");
            }
        }       
    }

}