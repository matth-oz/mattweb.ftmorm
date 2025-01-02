<?php
namespace Mattweb\Ftmorm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class TeamsTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(50) mandatory
 * <li> FOUND_YEAR int optional
 * </ul>
 *
 * @package Mattweb\Ftmorm
 **/

 class TeamsTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'ot_teams';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('TEAMS_ENTITY_ID_FIELD'),
				]
			),
            (new OneToMany('GAMES', GamesTable::class, 'TEAM'))->configureJoinType('inner'),
			new StringField(
				'NAME',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 50),
						];
					},
					'title' => Loc::getMessage('TEAMS_ENTITY_NAME_FIELD'),
				]
			),
			new IntegerField(
				'FOUND_YEAR',
				[
					'title' => Loc::getMessage('TEAMS_ENTITY_FOUND_YEAR_FIELD'),
				]
			),
		];
	}
}