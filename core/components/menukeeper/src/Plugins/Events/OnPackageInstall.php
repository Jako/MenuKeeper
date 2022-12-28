<?php
/**
 * @package menukeeper
 * @subpackage plugin
 */

namespace TreehillStudio\MenuKeeper\Plugins\Events;

use TreehillStudio\MenuKeeper\Plugins\Plugin;

class OnPackageInstall extends Plugin
{
    public function process()
    {
        $this->menukeeper->restoreMenu();
        $this->menukeeper->sortMenu();
        $this->menukeeper->cacheMenu(true);
    }
}
