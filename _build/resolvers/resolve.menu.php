<?php
/**
 * Resolve menu
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
    $modxversion = $modx->getVersionData();

    $menuObject = $modx->getObject('modMenu', [
        'text' => 'menukeeper.menu',
    ]);
    if ($menuObject) {
        if ($modxversion['version'] === '3' && $modxversion['major_version'] >= '1') {
            $menuObject->set('parent', 'admin');
            $menuObject->save();
            $modx->log(xPDO::LOG_LEVEL_INFO, 'Move menukeeper menu entry to the admin menu.');
        }
    }
}
return true;
