<?php
/**
 * Save Menu
 *
 * @package menukeeper
 * @subpackage processors
 */

use TreehillStudio\MenuKeeper\Processors\Processor;

class MenuKeeperSaveMenuProcessor extends Processor
{
    public $languageTopics = ['menukeeper:default'];

    /**
     * {@inheritDoc}
     * @return array|mixed|string
     */
    function process()
    {
        $this->menukeeper->cacheMenu(true);
        return $this->success($this->modx->lexicon('menukeeper.menu_saved'));
    }
}

return 'MenuKeeperSaveMenuProcessor';
