<?php
/**
 * MenuKeeper connector
 *
 * @package menukeeper
 * @subpackage connector
 *
 * @var modX $modx
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('menukeeper.core_path', null, $modx->getOption('core_path') . 'components/menukeeper/');
/** @var MenuKeeper $menukeeper */
$menukeeper = $modx->getService('menukeeper', 'MenuKeeper', $corePath . 'model/menukeeper/', [
    'core_path' => $corePath
]);

// Handle request
$modx->request->handleRequest([
    'processors_path' => $menukeeper->getOption('processorsPath'),
    'location' => ''
]);
