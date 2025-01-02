<?php
namespace Mattweb\Ftmorm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;

//use Bitrix\Main\ORM\Fields\Relations\ManyToMany;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class PlayersTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FIRST_NAME string(50) mandatory
 * <li> LAST_NAME string(50) mandatory
 * <li> NICKNAME string(50) mandatory
 * <li> CITIZENSHIP string(50) optional
 * <li> DOB date mandatory
 * <li> ROLE string(20) mandatory
 * </ul>
 *
 * @package Mattweb\Ftmorm
 **/

 class PlayersTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'ot_players';
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
					'title' => Loc::getMessage('PLAYERS_ENTITY_ID_FIELD'),
				]
			),
            (new OneToMany('PLAYERS', LineupsTable::class, 'PLAYER'))->configureJoinType('inner'),
			new StringField(
				'FIRST_NAME',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 50),
						];
					},
					'title' => Loc::getMessage('PLAYERS_ENTITY_FIRST_NAME_FIELD'),
				]
			),
			new StringField(
				'LAST_NAME',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 50),
						];
					},
					'title' => Loc::getMessage('PLAYERS_ENTITY_LAST_NAME_FIELD'),
				]
			),
			new StringField(
				'NICKNAME',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 50),
						];
					},
					'title' => Loc::getMessage('PLAYERS_ENTITY_NICKNAME_FIELD'),
				]
			),
			new StringField(
				'CITIZENSHIP',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 50),
						];
					},
					'title' => Loc::getMessage('PLAYERS_ENTITY_CITIZENSHIP_FIELD'),
				]
			),
			new DateField(
				'DOB',
				[
					'required' => true,
					'title' => Loc::getMessage('PLAYERS_ENTITY_DOB_FIELD'),
				]
			),
			new StringField(
				'ROLE',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 20),
						];
					},
					'title' => Loc::getMessage('PLAYERS_ENTITY_ROLE_FIELD'),
				]
			),
		];
	}
}