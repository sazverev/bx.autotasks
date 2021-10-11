<?php
/**
 * KVANTIX
 * @site https://www.kvantix.ru
 * @email kvantix@bk.ru
 * @copyright 2016-2021 ALTASIB
 **/

global $APPLICATION, $DBType;
IncludeModuleLangFile(__FILE__);
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arClassesList = array(
    "Bx\Autotasks\Agent" => "lib/agent.php",
);

CModule::AddAutoloadClasses(
        "bx.autotasks",
        $arClassesList
);