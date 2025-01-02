<?
global $arTemplateParams;

$arTemplateParams = array(
    //Main settings
    //Основные настройки
    'META_VIEWPORT' => '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">',
    'META_AUTHOR' => 'Веб студия Vn',
    'ENABLE_JQUERY' => true,                    //Подключает JQuery с Google CDN

    //Social images
    //Изображения для социальных сетей
    'SOCIAL_IMAGE' => SITE_TEMPLATE_PATH . '/images/Valentin_logo.png',
    'SOCIAL_IMAGE_BIG' => SITE_TEMPLATE_PATH . '/images/Valentin_logo.png',

	//Google
	'LATITUDE' => 0,							//Широта
	'LONGITUDE' => 0,							//Долгота
	'COUNTRY_NAME' => 'Россия',					//Страна
	'POSTAL_CODE' => '654000',					//Почтовый индекс
	'LOCALITY' => 'Новокузнецк',				//Город
	'STREET_ADDRESS' => 'проспект Ермакова',	//Улица'
	'PHONE_NUMBER' => '+7(123)123-123',		//Номер телефона

	//Twitter
	'CREATOR' => '@unnamed'						//Аккаунт в твиттере
);
?>