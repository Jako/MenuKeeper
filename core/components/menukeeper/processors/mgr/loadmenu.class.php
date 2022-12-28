<?php
/**
 * Load Menu
 *
 * @package menukeeper
 * @subpackage processors
 */

use TreehillStudio\MenuKeeper\Processors\Processor;

class MenuKeeperLoadMenuProcessor extends Processor
{
    public $languageTopics = ['menukeeper:default'];

    /**
     * {@inheritDoc}
     * @return array|mixed|string
     */
    function process()
    {
        $this->menukeeper->restoreMenu();
        $this->menukeeper->cacheMenu(true);
        return $this->success($this->modx->lexicon('menukeeper.menu_loaded'));
    }
}

return 'MenuKeeperLoadMenuProcessor';
