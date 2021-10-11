<?php
/**
 * KVANTIX
 * @site https://www.kvantix.ru
 * @email kvantix@bk.ru
 * @copyright 2016-2021 ALTASIB
 **/

namespace Bx\Autotasks;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Crm\DealTable;
use Bitrix\Crm\ActivityTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\Category\DealCategory;

Loc::loadMessages(__FILE__);

class Agent
{
    public static $moduleId = 'bx.autotasks';

    /**
     * Event::completingTasks()
     *
     * @return
     */
    public static function completingTasks()
    {
        //\Bitrix\Main\Loader::includeModule('crm');
        //\CModule::IncludeModule('crm');




        $rsCategory = DealCategory::getList(array());
        //AddMessage2Log($rsCategory);
        $ownerTypeID = '2';//DEAL
        while ($category = $rsCategory->Fetch()) {
            //AddMessage2Log($category['ID']);
            $arOptionStage = Option::get('bx.autotasks', "select_stage_".$category["ID"], "");
            $arStage = unserialize($arOptionStage);
            //AddMessage2Log($arStage);
            $rsDeal = \Bitrix\Crm\DealTable::getList(array(
                'select' => array('ID'),
                'filter' => array(
                    'STAGE_ID' => $arStage,
                ),
            ));
            //AddMessage2Log($rsDeal);
            while($arDeal = $rsDeal->Fetch()){
                AddMessage2Log($arDeal);
                //$rsActivity = \CCrmActivity::GetList(array(), array('OWNER_ID' => $arDeal['ID'], 'OWNER_TYPE_ID' => $ownerTypeID), false, false, array('ID', 'COMPLETED', 'STATUS'));
                $rsActivity = \Bitrix\Crm\ActivityTable::getList(array(
                    'select' => array('ID', 'ASSOCIATED_ENTITY_ID', 'COMPLETED'),
                    'filter' => array(
                        '=BINDINGS.OWNER_ID' => $arDeal['ID'], 
                        '=BINDINGS.OWNER_TYPE_ID' => $ownerTypeID),
                ));
                
                while ($arActivity = $rsActivity->Fetch()) {
                    AddMessage2Log($arActivity);
                    \Bitrix\Main\Loader::includeModule('tasks');
                    
                    $assosiatedTask = \Bitrix\Tasks\Internals\TaskTable::getById($arActivity['ASSOCIATED_ENTITY_ID'])->fetch();
                    AddMessage2Log($assosiatedTask);
                    \Bitrix\Tasks\Internals\TaskTable::update($assosiatedTask["ID"], array("STATUS" => 5));
                    if ($arActivity['COMPLETED'] != 'N') \Bitrix\Crm\ActivityTable::update($arActivity['ID'], array('COMPLETED' => 'Y'));
                }
            }
        }
        return 'Bx\Autotasks\Agent::completingTasks();';
    }

    public static function getDeal()
    {
        //\Bitrix\Main\Loader::includeModule('crm');
        //\CModule::IncludeModule('crm');

        $owner = \Bitrix\Crm\DealTable::getList(array(
            'select' => array('ID'),
            'filter' => array(
                '=ID' => 9405,
            ),
        ))->fetch(); 
            AddMessage2Log($owner);
            $rsActivity = \Bitrix\Crm\ActivityTable::getList(array(
                'select' => array('ID'),
                'filter' => array(
                    '=BINDINGS.OWNER_ID' => $owner['ID'], 
                    '=BINDINGS.OWNER_TYPE_ID' => 2),
            ));
            while ($arActivity = $rsActivity->Fetch()) {
                AddMessage2Log($arActivity);
                //\Bitrix\Crm\ActivityTable::update($arActivity['ID'], array('COMPLETED' => 'Y', 'STATUS' => 3));

            }

        return 'Bx\Autotasks\Agent::getDeal();';
    }
}