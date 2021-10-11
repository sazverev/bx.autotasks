<?php
/**
 * KVANTIX
 * @site https://www.kvantix.ru
 * @email kvantix@bk.ru
 * @copyright 2016-2021 ALTASIB
 **/

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\COption;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;


Loc::loadMessages(__FILE__);

class bx_autotasks extends CModule
{
    public function __construct()
    {
        $arModuleVersion = array();
        
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }
        
        $this->MODULE_ID = 'bx.autotasks';
        $this->MODULE_NAME = Loc::getMessage('BX_AUTOTASKS_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('BX_AUTOTASKS_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = Loc::getMessage('BX_AUTOTASKS_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'https://kvantix.ru';
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->installDB();
    }

    public function doUninstall()
    {
        $this->uninstallDB();
        Option::delete($this->MODULE_ID);
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function installDB()
    {
        $ID = CAgent::AddAgent("Bx\Autotasks\Agent::completingTasks();", $this->MODULE_ID, "N", 0,
            date('d.m.Y H:i:s'), "N", date('d.m.Y H:i:s'), 999);
        Option::set(
            $this->MODULE_ID,
            'agent_id',
            $ID
        );
    }

    public function uninstallDB()
    {
        CAgent::RemoveModuleAgents($this->MODULE_ID);
        
    }
}
