<?php
/**
 * @package menukeeper
 * @subpackage plugin
 */

namespace TreehillStudio\MenuKeeper\Plugins\Events;

use TreehillStudio\MenuKeeper\Plugins\Plugin;

class OnManagerPageBeforeRender extends Plugin
{
    public function process()
    {
        if ($this->modx->user && $this->modx->user->hasSessionContext('mgr')) {
            $assetsUrl = $this->menukeeper->getOption('assetsUrl');
            $jsUrl = $this->menukeeper->getOption('jsUrl') . 'mgr/';
            $jsSourceUrl = $assetsUrl . '../../../source/js/mgr/';

            $this->modx->controller->addLexiconTopic('menukeeper:default');

            if ($this->menukeeper->getOption('debug') && ($this->menukeeper->getOption('assetsUrl') != MODX_ASSETS_URL . 'components/menukeeper/')) {
                $this->modx->controller->addJavascript($jsSourceUrl . 'menukeeper.js?v=v' . $this->menukeeper->version);
                $this->modx->controller->addJavascript($jsSourceUrl . 'helper/util.js?v=v' . $this->menukeeper->version);
            } else {
                $this->modx->controller->addJavascript($jsUrl . 'menukeeper.min.js?v=v' . $this->menukeeper->version);
            }
            $this->modx->controller->addHtml(
                '<script type="text/javascript">
                    Ext.onReady(function() {
                        MenuKeeper.config = ' . json_encode($this->menukeeper->options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ';
                    });
                </script>'
            );
            $this->menukeeper->cacheMenu();
        }
    }
}
