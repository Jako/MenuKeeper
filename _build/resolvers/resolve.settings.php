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

if ($object->xpdo) {
    /** @var modX $modx */
    $modx = &$object->xpdo;

    // Remembers the value of package_installer_at_top before MenuKeeper changed it
    $originalKey = 'menukeeper.package_installer_at_top_original';

    /** @var modSystemSetting $settingObject */
    $settingObject = $modx->getObject('modSystemSetting', [
        'key' => 'package_installer_at_top'
    ]);
    /** @var modSystemSetting $originalObject */
    $originalObject = $modx->getObject('modSystemSetting', [
        'key' => $originalKey
    ]);

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            if ($settingObject) {
                if (!$originalObject) {
                    // Versions before 1.1.2 did not remember the original value and had already disabled the
                    // setting during an upgrade, so the MODX default is assumed then
                    $originalValue = ($options[xPDOTransport::PACKAGE_ACTION] == xPDOTransport::ACTION_INSTALL) ? $settingObject->get('value') : '1';
                    $originalObject = $modx->newObject('modSystemSetting');
                    $originalObject->fromArray([
                        'key' => $originalKey,
                        'value' => $originalValue ? '1' : '0',
                        'xtype' => 'combo-boolean',
                        'namespace' => 'menukeeper',
                        'area' => 'system',
                    ], '', true, true);
                    if ($originalObject->save()) {
                        $modx->log(xPDO::LOG_LEVEL_INFO, 'Original value of the package_installer_at_top system setting saved.');
                    } else {
                        $modx->log(xPDO::LOG_LEVEL_ERROR, 'Original value of the package_installer_at_top system setting can\'t be saved.');
                    }
                }
                if ($settingObject->get('value') == true) {
                    $settingObject->set('value', false);
                    $settingObject->save();
                    $modx->log(xPDO::LOG_LEVEL_INFO, 'package_installer_at_top system setting disabled.');
                }
            } else {
                $modx->log(xPDO::LOG_LEVEL_ERROR, 'package_installer_at_top system setting was not found, so the setting can\'t be changed.');
            }
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            // Without a saved original value (versions before 1.1.2), the MODX default is restored
            $originalValue = $originalObject ? (bool)$originalObject->get('value') : true;
            if ($settingObject) {
                if ($originalValue && $settingObject->get('value') == false) {
                    $settingObject->set('value', true);
                    $settingObject->save();
                    $modx->log(xPDO::LOG_LEVEL_INFO, 'package_installer_at_top system setting enabled.');
                }
            } else {
                $modx->log(xPDO::LOG_LEVEL_ERROR, 'package_installer_at_top system setting was not found, so the setting can\'t be changed.');
            }
            if ($originalObject) {
                $originalObject->remove();
            }
            break;
    }
}
return true;
