<?php
/**
 * MenuKeeper Plugin
 *
 * @package menukeeper
 * @subpackage plugin
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$className = 'TreehillStudio\MenuKeeper\Plugins\Events\\' . $modx->event->name;

$corePath = $modx->getOption('menukeeper.core_path', null, $modx->getOption('core_path') . 'components/menukeeper/');
/** @var MenuKeeper $menukeeper */
$menukeeper = $modx->getService('menukeeper', 'MenuKeeper', $corePath . 'model/menukeeper/', [
    'core_path' => $corePath
]);

if ($menukeeper) {
    if (class_exists($className)) {
        $handler = new $className($modx, $scriptProperties);
        if (get_class($handler) == $className) {
            $handler->run();
        } else {
            $modx->log(xPDO::LOG_LEVEL_ERROR, $className . ' could not be initialized!', '', 'MenuKeeper Plugin');
        }
    } else {
        $modx->log(xPDO::LOG_LEVEL_ERROR, $className . ' was not found!', '', 'MenuKeeper Plugin');
    }
}

return;
