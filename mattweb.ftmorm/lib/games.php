<?php
namespace Mattweb\Ftmorm;

use Bitrix\Main\Localization\Loc;

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;

//use Bitrix\Main\ORM\Fields\Relations\ManyToMany;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class GamesTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TEAM_ID int mandatory
 * <li> CITY string(30) optional
 * <li> GOALS int optional
 * <li> GAME_DATE datetime mandatory
 * <li> OWN int optional
 * </ul>
 *
 * @package Mattweb\Ftmorm
 **/

 class GamesTable extends DataManager
{
	
    const OUR_TEAM_NAME = 'Cool Players';
    const OUR_TEAM_CITY = 'Exeter';

    /**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'ot_games';
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
					'title' => Loc::getMessage('GAMES_ENTITY_ID_FIELD'),
				]
			),
            (new OneToMany('GAMES', LineupsTable::class, 'GAME'))->configureJoinType('inner'),
            new IntegerField(
				'TEAM_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('GAMES_ENTITY_TEAM_ID_FIELD'),
				]
			),
            new Reference(
                'TEAM',
                TeamsTable::class,
                Join::on('this.TEAM_ID', 'ref.ID')
            ),
			new StringField(
				'CITY',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 30),
						];
					},
					'title' => Loc::getMessage('GAMES_ENTITY_CITY_FIELD'),
                    'deafult_value' => self::OUR_TEAM_CITY,
				]
			),
			new IntegerField(
				'GOALS',
				[
					'title' => Loc::getMessage('GAMES_ENTITY_GOALS_FIELD'),
				]
			),
			new DatetimeField(
				'GAME_DATE',
				[
					'required' => true,
					'title' => Loc::getMessage('GAMES_ENTITY_GAME_DATE_FIELD'),
				]
			),
			new IntegerField(
				'OWN',
				[
					'title' => Loc::getMessage('GAMES_ENTITY_OWN_FIELD'),
				]
			),
			new IntegerField(
				'PLAYERS_ADDED',
				[
					'title' => Loc::getMessage('GAMES_ENTITY_PLAYERS_ADDED_FIELD'),
					'size' => 1,
				]
			),
		];
	}
}