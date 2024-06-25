<?php
/**
 * Resolve system settings
 *
 * @package menukeeper
 * @subpackage build
 *
 * @var array $options
 * @var xPDOObject $object
 */

$success = true;

if ($object->xpdo) {
    /** @var modX $modx */
    $modx = &$object->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            /** @var modSystemSetting $settingObject */
            $settingObject = $modx->getObject('modSystemSetting', ['key' => 'package_installer_at_top']);
            if ($settingObject) {
                if ($settingObject->get('value') == true) {
                    $settingObject->set('value',false);
                    $settingObject->save();
                    $modx->log(xPDO::LOG_LEVEL_INFO, 'package_installer_at_top disabled');
                }
            } else {
                $modx->log(xPDO::LOG_LEVEL_ERROR, 'package_installer_at_top setting was not found, so the setting can\'t be changed.');
                $success = false;
            }
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            /** @var modSystemSetting $settingObject */
            $settingObject = $modx->getObject('modSystemSetting', ['key' => 'package_installer_at_top']);
            if ($settingObject) {
                if ($settingObject->get('value') == false) {
                    $settingObject->set('value',true);
                    $settingObject->save();
                    $modx->log(xPDO::LOG_LEVEL_INFO, 'package_installer_at_top disabled');
                }
            } else {
                $modx->log(xPDO::LOG_LEVEL_ERROR, 'package_installer_at_top setting was not found, so the setting can\'t be changed.');
                $success = false;
            }
            break;
    }
}
return $success;
