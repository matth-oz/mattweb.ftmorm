<?php
namespace Mattweb\Ftmorm;

use Bitrix\Main\Localization\Loc;

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class LineupsTable
 * 
 * Fields:
 * <ul>
 * <li> START string(2) optional default '0'
 * <li> GAME_ID int mandatory
 * <li> PLAYER_ID int mandatory
 * <li> TIME_IN int optional
 * <li> GOALS int optional
 * <li> CARDS string(2) optional
 * </ul>
 *
 * @package Mattweb\Ftmorm
 **/

 class LineupsTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'ot_lineups';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new StringField(
				'START',
				[
					'default' => '0',
					'validation' => function()
					{
						return[
							new LengthValidator(null, 2),
						];
					},
					'title' => Loc::getMessage('LINEUPS_ENTITY_START_FIELD'),
					'description' => Loc::getMessage('LINEUPS_ENTITY_START_FIELD_DESC'),
				]
			),
			new IntegerField(
				'GAME_ID',
				[
					'primary' => true,
					'title' => Loc::getMessage('LINEUPS_ENTITY_GAME_ID_FIELD'),
				]
			),
            new Reference(
                'GAME',
                GamesTable::class,
                Join::on('this.GAME_ID', 'ref.ID')
            ),
			new IntegerField(
				'PLAYER_ID',
				[
					'primary' => true,
					'title' => Loc::getMessage('LINEUPS_ENTITY_PLAYER_ID_FIELD'),
				]
			),
            new Reference(
                'PLAYER',
                PlayersTable::class,
                Join::on('this.PLAYER_ID', 'ref.ID')
            ),
			new IntegerField(
				'TIME_IN',
				[
					'title' => Loc::getMessage('LINEUPS_ENTITY_TIME_IN_FIELD'),
					'description' => Loc::getMessage('LINEUPS_ENTITY_TIME_IN_FIELD_DESC'),
				]
			),
			new IntegerField(
				'GOALS',
				[
					'title' => Loc::getMessage('LINEUPS_ENTITY_GOALS_FIELD'),
					'description' => Loc::getMessage('LINEUPS_ENTITY_GOALS_FIELD_DESC'),
				]
			),
			new StringField(
				'CARDS',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 2),
						];
					},
					'title' => Loc::getMessage('LINEUPS_ENTITY_CARDS_FIELD'),
					'description' => Loc::getMessage('LINEUPS_ENTITY_CARDS_FIELD_DESC'),
				]
			),
		];
	}
}