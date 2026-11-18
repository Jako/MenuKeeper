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
    public $permission = 'settings';

    /**
     * {@inheritDoc}
     * @return array|mixed|string
     */
    function process()
    {
        if (!$this->menukeeper->hasCachedMenu('snapshot')) {
            return $this->failure($this->modx->lexicon('menukeeper.menu_not_saved'));
        }
        $this->menukeeper->restoreMenu('snapshot');
        // The restored menu state is the new state that is restored after a package installation
        $this->menukeeper->cacheMenu(true);
        return $this->success($this->modx->lexicon('menukeeper.menu_loaded'));
    }
}

return 'MenuKeeperLoadMenuProcessor';
