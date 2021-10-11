<?php
/**
 * KVANTIX
 * @site https://www.kvantix.ru
 * @email kvantix@bk.ru
 * @copyright 2016-2021 ALTASIB
 **/

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Crm\Category\DealCategory;

defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'bx.autotasks');

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loc::loadMessages($context->getServer()->getDocumentRoot()."/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

$tabControl = new CAdminTabControl("tabControl", array(
    array(
        "DIV" => "edit",
        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
    ),
));

$arAgent = CAgent::GetList(array(), array('MODULE_ID' => ADMIN_MODULE_NAME, 'SORT' => 999))->Fetch();

$agent_id = $arAgent["ID"];
$arAgent = CAgent::GetById($agent_id)->Fetch();

if (!$arAgent) {
    
    $agent_id = CAgent::AddAgent("Bx\Autotasks\Agent::completingTasks();", ADMIN_MODULE_NAME, "N", 0,
    date('d.m.Y H:i:s'), "N", date('d.m.Y H:i:s'), 999);
    //print_r($agent_id);
    Option::set(
        ADMIN_MODULE_NAME,
        'agent_id',
        $agent_id
    );
}




if ((!empty($save) || !empty($restore)) && $request->isPost() && check_bitrix_sessid()) {
    if (!empty($restore)) {
        Option::delete(ADMIN_MODULE_NAME);
        CAdminMessage::showMessage(array(
            "MESSAGE" => Loc::getMessage("REFERENCES_OPTIONS_RESTORED"),
            "TYPE" => "OK",
        ));
        CAgent::Update($agent_id, array("ACTIVE" => 'N', 'AGENT_INTERVAL' => 0));
    } else {
        foreach ($request->getValues() as $key => $setting) {
            if (strpos($key, 'select_stage') !==false){
                Option::set(
                    ADMIN_MODULE_NAME,
                    $key,
                    serialize($request->getPost($key))
                );
            }
        }

        if ($request->getPost('interval') != 0) {
            Option::set(
                ADMIN_MODULE_NAME,
                'interval',
                $request->getPost('interval')
            );
            CAgent::Update($agent_id, array("ACTIVE" => 'Y', 'AGENT_INTERVAL' => $request->getPost('interval')));
        } else {
            Option::set(
                ADMIN_MODULE_NAME,
                'interval',
                $request->getPost('interval')
            );
            CAgent::Update($agent_id, array("ACTIVE" => 'N', 'AGENT_INTERVAL' => $request->getPost('interval')));
        }

        if ($request->getPost('next_exec')){
            Option::set(
                ADMIN_MODULE_NAME,
                'next_exec',
                $request->getPost('next_exec')
            );
            CAgent::Update($agent_id, array('NEXT_EXEC' => $request->getPost('next_exec')));
        }

        CAdminMessage::showMessage(array(
            "MESSAGE" => Loc::getMessage("REFERENCES_OPTIONS_SAVED"),
            "TYPE" => "OK",
        ));
    }
}
$arAgent = CAgent::GetById($agent_id)->Fetch();

$tabControl->begin();




?>

<form method="post" action="<?=sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID)?>">
    <?php
    echo bitrix_sessid_post();
    $tabControl->beginNextTab();
    ?>


    <tr class="heading">
        <td colspan="2"><?=Loc::getMessage("CATEGORY_AND_STAGES_TITLE") ?></td>
    </tr>
    <?$res = \Bitrix\Crm\Category\DealCategory::getList(array());
    $interval = COption::GetOptionString(ADMIN_MODULE_NAME, "interval", "");
    $next_exec = COption::GetOptionString(ADMIN_MODULE_NAME, "next_exec", "");
    while ($category = $res->Fetch()) {

        $arOptionStage = COption::GetOptionString(ADMIN_MODULE_NAME, "select_stage_".$category["ID"], "");
    
        if($arOptionStage <> '') { $arOptionStage = unserialize($arOptionStage); }
        else { $arOptionStage = array(); }
        ?>

        <tr>
            <td width="40%">
                <label for="max_image_size"><?=$category["NAME"];?>:</label>
            <td width="60%">
                <select name="select_stage_<?=$category["ID"];?>[]" multiple size="5"><?
                    $arStage = array();
                    $StageList = DealCategory::getStageList($category["ID"]);
                    foreach ($StageList as $id => $name) {
                        $arStage[$key] = $stage." [".$key."]";

                        ?><option value="<?=$id?>" <?=(in_array($id, $arOptionStage) ? "selected" : "")?>>
                            <?=htmlspecialcharsEx($name)?>
                        </option><?
                    }
                ?></select>
            </td>
        </tr><?
    }?>
    <tr class="heading">
        <td colspan="2"><?=Loc::getMessage("AGENT_SETTINGS_TITLE") ?></td>
    </tr>
    <tr>
        <td width="40%">
        <?=Loc::getMessage("INTERVAL_TITLE") ?>:
		</td>
        
		<td width="60%">
			<select name="interval">
                <option value="0" <?=(($interval) ? "selected" : "")?>><?=Loc::getMessage("INTERVAL_OPTION_0") ?></option>
				<option value="86400" <?=(($interval == '86400') ? "selected" : "")?>><?=Loc::getMessage("INTERVAL_OPTION_86400") ?></option>
				<option value="604800" <?=(($interval == '604800') ? "selected" : "")?>><?=Loc::getMessage("INTERVAL_OPTION_604800") ?></option>
			</select>
		</td>
	</tr>
    <tr>
		<td width="40%"><?=Loc::getMessage("NEXT_EXEC_TITLE") ?>:</td>
        <td width="60%">
            <div class="adm-input-wrap adm-input-wrap-calendar">
                <input class="adm-input adm-input-calendar" type="text" name="next_exec" size="23" value="<?=(($arAgent['NEXT_EXEC']) ? $arAgent['NEXT_EXEC'] : date('d.m.Y H:i:s'))?>">
                <span class="adm-calendar-icon" title="Нажмите для выбора даты" onclick="BX.calendar({node:this, field:'next_exec', form: '', bTime: true, bHideTime: false});"></span>
            </div>
        </td>
	</tr>
    <tr>
        <td width="40%"><?=Loc::getMessage("AGENT_ID_TITLE") ?>:</td>
		<td width="60%"><?=$agent_id;?></td>
	</tr>
    <tr>
        <td width="40%"><?=Loc::getMessage("LAST_EXEC_TITLE") ?>:</td>
		<td width="60%"><?=($arAgent['LAST_EXEC']) ? $arAgent['LAST_EXEC'] : "Не запускался";?></td>
	</tr>
    <?php
    $tabControl->buttons();
    ?>
    <input type="submit"
           name="save"
           value="<?=Loc::getMessage("MAIN_SAVE") ?>"
           title="<?=Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>"
           class="adm-btn-save"
           />
    <input type="submit"
           name="restore"
           title="<?=Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
           onclick="return confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
           value="<?=Loc::getMessage("MAIN_RESTORE_DEFAULTS") ?>"
           />
    <?php
    $tabControl->end();
    ?>
</form>

<div class="adm-info-message-wrap"><div class="adm-info-message">		
    <strong>Автозавершение задач по сделкам</strong><br><br>
        Запуск основного скрипта происходит на Агенте, который создается при установке модуля. 
        Чтобы Агент начал свою работу, необходимо установить параметр Частота запуска, а также Дата и время следующего запуска.
	</div>
</div>
