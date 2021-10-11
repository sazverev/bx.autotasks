<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$menu = array(
    array(
        'parent_menu' => 'global_menu_content',
        'sort' => 400,
        'text' => Loc::getMessage('BX_AUTOTASKS_MENU_TITLE'),
        'title' => Loc::getMessage('BX_AUTOTASKS_MENU_TITLE'),
        'url' => 'bxautotasks_index.php',
        'items_id' => 'menu_references',
        'items' => array(
            array(
                'text' => Loc::getMessage('BX_AUTOTASKS_SUBMENU_TITLE'),
                'url' => 'bx_autotasks_index.php?param1=paramval&lang=' . LANGUAGE_ID,
                'more_url' => array('bx_autotasks_index.php?param1=paramval&lang=' . LANGUAGE_ID),
                'title' => Loc::getMessage('BX_AUTOTASKS_SUBMENU_TITLE'),
            ),
        ),
    ),
);

return $menu;
