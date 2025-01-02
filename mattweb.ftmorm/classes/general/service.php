<?php
use Mattweb\Ftmorm;
use \Bitrix\Main\Localization\Loc;

class ServiceActions{

    protected $className;

    const MODULE_NAME = 'mattweb.ftmorm';
    const NS_PREFIX = "Mattweb\\Ftmorm\\";
    const FTMORM_CLASSES = [
        'TeamsTable' => 'Teams',
        'PlayersTable' => 'Players',
        'GamesTable' => 'Games',
        'LineupsTable' => 'Lineups',
        'CopyTable' => 'Copy',
    ];

    const FTMORM_CLASSES_PREFIXES = [
        'TeamsTable' => 'TM_',
        'PlayersTable' => 'PL_',
        'GamesTable' => 'GM_',
        'LineupsTable' => 'LU_',
    ];

    public function __construct($clName) {
        $this->className = trim(self::NS_PREFIX.$clName);
    }

    public static function getPrimaryKey($clName){
        $className = trim(self::NS_PREFIX.$clName);

        $pk = [];

        $pt = $className::getMap();
        foreach($pt as $elPt){
            $parentClass = get_class($elPt);
            if(str_contains($parentClass, 'IntegerField')){
                $ipr = $elPt->isPrimary();
                if($ipr){
                    $pk[] = $elPt->getName();
                }
            }
        }

        return $pk;
    }

    /** @var string $mode = basic | full */
    /** @var string $act = view | filter */
    public function getFieds($mode = 'basic'){
        $arExcludedTypes = ['ExpressionField', 'TextField', 'OneToMany', 'ManyToMany', 'Reference'];

        $arrRes = [];
        $pt = $this->className::getMap();

        foreach($pt as $elPt){
            $elName = $elPt->getName();
            $elTitle = $elPt->getTitle();

            $parentClass = $this->getParentClass($elPt);

            if(($parentClass == 'IntegerField' && $elPt->isPrimary()) || ($mode == 'basic' && in_array($parentClass, $arExcludedTypes))) continue;
          
            if($mode == 'basic'){
                $arrRes[$elName] = $elTitle;
            }

            if($mode == 'full'){

                $arrRes[$elName] = [
                    'TITLE' => $elTitle,
                    'PARENT_CLASS' => $parentClass, 
                ];
                
                if($parentClass == 'BooleanField' || $parentClass == 'EnumField'){
                    $arrRes[$elName]['VALUES'] = $elPt->getValues();
                }
            }
        }

        return $arrRes;
    }

    /** @var string $mode = basic | full */
    /** @var string $act = view | filter */
    public function getScalarFields($mode = 'basic', $act = 'view', $prefix = ''){
        $arScalarTypes = ['DateField', 'DatetimeField', 'BooleanField', 'IntegerField', 'FloatField', 'EnumField', 'StringField', 'TextField'];
        $arrRes = [];
        $pt = $this->className::getMap();
        foreach($pt as $elPt){
            $elName = $elPt->getName();
            $elTitle = $elPt->getTitle();

            $parentClass = $this->getParentClass($elPt);
            if(!in_array($parentClass, $arScalarTypes) || ($parentClass == 'IntegerField' && $elPt->isPrimary()) || ($parentClass == 'TextField' && $act = 'filter')) continue;
            
            if($mode == 'basic'){
                $arrRes[$elName] = $elTitle;
            }
            
            if($mode == 'full'){
                if(strlen($prefix) > 0){
                    $elName = $prefix.$elName;
                }
                
                $arrRes[$elName] = [
                    'TITLE' => $elTitle,
                    'PARENT_CLASS' => $parentClass, 
                ];
                
                if($parentClass == 'BooleanField' || $parentClass == 'EnumField'){
                    $arrRes[$elName]['VALUES'] = $elPt->getValues();
                }
            }
        }

        return $arrRes;
    }

    /** @var string $mode = basic | full */
    /** @var string $act = view | filter */
    public function getRelationFields($mode = 'basic', $act = 'view'){
        // view: Reference, OneToOne
        // filter: 
        // $arRelationTypes = ['ExpressionField', 'OneToMany', 'ManyToMany', 'Reference', 'OneToOne'];
        $arRelationTypes = ['Reference', 'OneToOne'];
        $arrRes = [];
        $pt = $this->className::getMap();
        
        foreach($pt as $elPt){
            $elName = $elPt->getName();
            $elTitle = $elPt->getTitle();

            $parentClass = $this->getParentClass($elPt);

            if(!in_array($parentClass, $arRelationTypes)) continue;
            
            if($mode == 'basic'){
                $arrRes[$elName] = $elTitle;
            }

            if($mode == 'full'){
                $arrRes[$elName] = [
                    'TITLE' => $elTitle,
                    'PARENT_CLASS' => $parentClass, 
                ];

                switch($parentClass){
                    //case 'OneToMany':
                    case 'OneToOne': 
                    case 'Reference': 
                        $curRefName = $elPt->getRefEntityName();
                        $arrRes[$elName]['REFERENCE_NAME'] = $curRefName;
                        $arrRes[$elName]['REFERENCE_NAME_FULL'] = $curRefName.'Table';

                        $arrCurRefName = explode('\\', $curRefName);
                        $className = end($arrCurRefName).'Table';

                        $arrRes[$elName]['REFERENCE_CLASS'] = $className;                        

                    break;
                }
            }
        }

        return $arrRes;
    }



    public function getParentClass($obj){
        $fpc = $this->getFullParentClass($obj);

        $arFpc = explode('\\', $fpc);

        return end($arFpc);

    }

    public static function getFtmOrmClassesPrefixes(){
        return self::FTMORM_CLASSES_PREFIXES;
    }

    public static function getFtmOrmClasses(){
        return self::FTMORM_CLASSES;
    }


    public static function getFtmOrmClass($clName){
        return trim(self::NS_PREFIX.$clName);
    }

    public static function getTeamPlayerType(string $key = 'all') : string | array | bool
    {
        $arTeamComposition = [
            'GOALKEEPER' => Loc::GetMessage('GOALKEEPER_TITLE'),
            'DEFENDER' => Loc::GetMessage('DEFENDER_TITLE'),
            'FORWARD' => Loc::GetMessage('FORWARD_TITLE'),
            'MIDFIELDER'=> Loc::GetMessage('MIDFIELDER_TITLE'),
        ];

        if($key == 'all'){
            return $arTeamComposition;
        }
        
        if(array_key_exists($key, $arTeamComposition)){
            return $arTeamComposition[$key];
        }

        return false;
    }


    public static function getTeamName(){
        $teamName = \Bitrix\Main\Config\Option::get(
            self::MODULE_NAME,        
            "team_name_".SITE_ID,        
            "",        
            false
        );

        return $teamName;
    }

    public static function getTeamCity(){
        $teamCity = \Bitrix\Main\Config\Option::get(
            self::MODULE_NAME,        
            "team_city_name_".SITE_ID,        
            "",        
            false
        );

        return $teamCity;
    }

    // https://bxapi.ru/src/?module_id=iblock&name=CIBlockParameters::AddPagerSettings
    public static function getPaginationTemplatesList(){
        $arTemplateList['.default'] = '.default';
        $arTemplateInfo = CComponentUtil::GetTemplatesList('bitrix:main.pagenavigation');
        
        if(!empty($arTemplateInfo)){
            sortByColumn($arTemplateInfo, array('TEMPLATE' => SORT_ASC, 'NAME' => SORT_ASC));
            foreach($arTemplateInfo as $arTemplate){
                if($arTemplate['NAME'] == '.default') continue;
                $arTemplateList[$arTemplate['NAME']] = $arTemplate['NAME'];
            }
        }

        return $arTemplateList;
    } 

    public static function getAllFieldsHTMLTypes()
    {
        
        /**
         * тип поля в HTML форме-фильтре
         * 
         * IT - text; 
         * IS - select; 
         * ISM - select multiple; 
         * NMB - number; 
         * ISL - slider; 
         * ISR - ranges; 
         * ICH - checkbox; 
         * IR - radiobutton;
         */

        return Array(
            'DateField' => [
                'ISR' => ['TYPE'=>'ISR', 'TITLE'=>'ranges']
            ],
            'DatetimeField' => [
                'ISR' => ['TYPE'=>'ISR', 'TITLE'=>'ranges']
            ],
            'BooleanField' => [
                'IR' => ['TYPE'=>'IR', 'TITLE'=>'radiobutton', 'TRUE_VALUE'=>''],
                'ICH' => ['TYPE'=>'ICH', 'TITLE'=>'checkbox', 'TRUE_VALUE'=>''],
                'IS' => ['TYPE'=>'IS', 'TITLE'=>'select', 'TRUE_VALUE'=>'']
            ],
            'IntegerField' => [
                'IT' => ['TYPE'=>'IT', 'TITLE'=>'text'],
                'ISR' => ['TYPE'=>'ISR', 'TITLE'=>'ranges'],
                'NMB' => ['TYPE'=>'NMB', 'TITLE'=>'number'],
            ],
            'FloatField' => [
                'IT' => ['TYPE'=>'IT', 'TITLE'=>'text'],
                'ISR' => ['TYPE'=>'ISR', 'TITLE'=>'ranges']
            ],
            'EnumField' => [
                'IS' => ['TYPE'=>'IS', 'TITLE'=>'select'],
                'ISM' => ['TYPE'=>'ISM', 'TITLE'=>'select multiple']
            ],
            'StringField' => [
                'IT' => ['TYPE'=>'IT', 'TITLE'=>'text'],
                'IS' => ['TYPE'=>'IS', 'TITLE'=>'select'],
                'ISM' => ['TYPE'=>'ISM', 'TITLE'=>'select multiple']
            ]
        );
    }


    protected function getFullParentClass($obj){
        return get_class($obj);
    }


}