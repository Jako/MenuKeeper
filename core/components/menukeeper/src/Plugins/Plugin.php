<?php
/**
 * Abstract plugin
 *
 * @package menukeeper
 * @subpackage plugin
 */

namespace TreehillStudio\MenuKeeper\Plugins;

use modX;
use MenuKeeper;

/**
 * Class Plugin
 */
abstract class Plugin
{
    /** @var modX $modx */
    protected $modx;
    /** @var MenuKeeper $menukeeper */
    protected $menukeeper;
    /** @var array $scriptProperties */
    protected $scriptProperties;

    /**
     * Plugin constructor.
     *
     * @param $modx
     * @param $scriptProperties
     */
    public function __construct($modx, &$scriptProperties)
    {
        $this->scriptProperties = &$scriptProperties;
        $this->modx =& $modx;
        $corePath = $this->modx->getOption('menukeeper.core_path', null, $this->modx->getOption('core_path') . 'components/menukeeper/');
        $this->menukeeper = $this->modx->getService('menukeeper', 'MenuKeeper', $corePath . 'model/menukeeper/', [
            'core_path' => $corePath
        ]);
    }

    /**
     * Run the plugin event.
     */
    public function run()
    {
        $init = $this->init();
        if ($init !== true) {
            return;
        }

        $this->process();
    }

    /**
     * Initialize the plugin event.
     *
     * @return bool
     */
    public function init()
    {
        return true;
    }

    /**
     * Process the plugin event code.
     *
     * @return mixed
     */
    abstract public function process();
}
