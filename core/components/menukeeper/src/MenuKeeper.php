<?php
/**
 * MenuKeeper
 *
 * Copyright 2022-2023 by Thomas Jakobi <office@treehillstudio.com>
 *
 * @package menukeeper
 * @subpackage classfile
 */

namespace TreehillStudio\MenuKeeper;

use modMenu;
use modX;
use xPDO;
use xPDOCacheManager;

/**
 * Class MenuKeeper
 */
class MenuKeeper
{
    /**
     * A reference to the modX instance
     * @var modX $modx
     */
    public $modx;

    /**
     * The namespace
     * @var string $namespace
     */
    public $namespace = 'menukeeper';

    /**
     * The package name
     * @var string $packageName
     */
    public $packageName = 'MenuKeeper';

    /**
     * The version
     * @var string $version
     */
    public $version = '1.0.2';

    /**
     * The class options
     * @var array $options
     */
    public $options = [];

    /**
     * The class cache options
     * @var array $cacheOptions
     */
    public $cacheOptions;

    /**
     * MenuKeeper constructor
     *
     * @param modX $modx A reference to the modX instance.
     * @param array $options An array of options. Optional.
     */
    public function __construct(modX &$modx, $options = [])
    {
        $this->modx =& $modx;
        $this->namespace = $this->getOption('namespace', $options, $this->namespace);

        $corePath = $this->getOption('core_path', $options, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/' . $this->namespace . '/');
        $assetsPath = $this->getOption('assets_path', $options, $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/' . $this->namespace . '/');
        $assetsUrl = $this->getOption('assets_url', $options, $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/' . $this->namespace . '/');
        $modxversion = $this->modx->getVersionData();

        // Load some default paths for easier management
        $this->options = array_merge([
            'namespace' => $this->namespace,
            'version' => $this->version,
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'vendorPath' => $corePath . 'vendor/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'pagesPath' => $corePath . 'elements/pages/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'pluginsPath' => $corePath . 'elements/plugins/',
            'controllersPath' => $corePath . 'controllers/',
            'processorsPath' => $corePath . 'processors/',
            'templatesPath' => $corePath . 'templates/',
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'imagesUrl' => $assetsUrl . 'images/',
            'connectorUrl' => $assetsUrl . 'connector.php'
        ], $options);

        $lexicon = $this->modx->getService('lexicon', 'modLexicon');
        $lexicon->load($this->namespace . ':default');

        $this->packageName = $this->modx->lexicon('menukeeper');

        // Add default options
        $this->options = array_merge($this->options, [
            'debug' => (bool)$this->getOption('debug', $options, false),
            'modxversion' => $modxversion['version']
        ]);

        $this->cacheOptions = [
            xPDO::OPT_CACHE_KEY => $this->namespace,
            xPDO::OPT_CACHE_HANDLER => $this->modx->getOption('cache_resource_handler', null, $this->modx->getOption(xPDO::OPT_CACHE_HANDLER)),
            xPDO::OPT_CACHE_FORMAT => (integer)$this->modx->getOption('cache_resource_format', null, $this->modx->getOption(xPDO::OPT_CACHE_FORMAT, null, xPDOCacheManager::CACHE_PHP)),
        ];
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param string $key The option key to search for.
     * @param array $options An array of options that override local options.
     * @param mixed $default The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     * @return mixed The option value or the default value specified.
     */
    public function getOption(string $key, $options = [], $default = null)
    {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("$this->namespace.$key", $this->modx->config)) {
                $option = $this->modx->getOption("$this->namespace.$key");
            }
        }
        return $option;
    }

    /**
     * Cache the current parent and menuindex of eacht menu entry
     *
     * @param bool $reset
     * @return void
     */
    public function cacheMenu($reset = false)
    {
        $cacheManager = $this->modx->getCacheManager();
        $menu = $cacheManager->get('menu', $this->cacheOptions);

        if ($menu || $reset) {
            $result = [];

            $c = $this->modx->newQuery('modMenu');
            $c->sortby('parent');
            $c->sortby('menuindex');

            /** @var modMenu[] $menuObjects */
            $menuObjects = $this->modx->getIterator('modMenu', $c);

            foreach ($menuObjects as $menuObject) {
                $result[$menuObject->get('text')] = [
                    'parent' => $menuObject->get('parent'),
                    'menuindex' => $menuObject->get('menuindex')
                ];
            }

            $cacheManager->set('menu', $result, 0, $this->cacheOptions);
        }
    }

    /**
     * Restore the cached parent and menuindex of each menu entry
     *
     * @return void
     */
    public function restoreMenu()
    {
        // Don't rebuild the menu cache after saving each modMenu object
        $this->modx->config[xPDO::OPT_SETUP] = true;

        $cacheManager = $this->modx->getCacheManager();
        $menus = $cacheManager->get('menu', $this->cacheOptions);

        if (is_array($menus)) {
            foreach ($menus as $key => $menu) {
                /** @var modMenu $menuObject */
                $menuObject = $this->modx->getObject('modMenu', [
                    'text' => $key
                ]);
                if ($menuObject && (
                        $menuObject->get('parent') !== $menu['parent'] ||
                        $menuObject->get('menuindex') !== $menu['menuindex']
                    )
                ) {
                    $menuObject->set('parent', $menu['parent']);
                    $menuObject->set('menuindex', $menu['menuindex']);
                    if (!$menuObject->save()) {
                        $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Menu state of ' . $key . ' can\'t be restored!', '', 'MenuKeeper');
                    }
                }
            }
        }

        if (!empty($menuObject)) {
            $this->cacheModMenu($menuObject);
        }
    }

    /**
     * @return void
     */
    public function sortMenu()
    {
        // Don't rebuild the menu cache after saving each modMenu object
        $this->modx->config[xPDO::OPT_SETUP] = true;

        $this->resetMenuindex();

        $cacheManager = $this->modx->getCacheManager();
        $menus = $cacheManager->get('menu', $this->cacheOptions);

        if (is_array($menus)) {
            /** @var modMenu[] $menuObjects */
            $menuObjects = $this->modx->getIterator('modMenu');

            foreach ($menuObjects as $menuObject) {
                if (!array_key_exists($menuObject->get('text'), $menus)) {
                    $menuObject->set('menuindex', $this->modx->getCount('modMenu', [
                        'parent' => $menuObject->get('parent')
                    ]));
                    if (!$menuObject->save()) {
                        $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Menu entry ' . $menuObject->get('text') . ' can\'t be set to the last menuindex!', '', 'MenuKeeper');
                    }
                }
            }
        }

        if (!empty($menuObject)) {
            $this->cacheModMenu($menuObject);
        }
    }

    /**
     * Reset the menuindex of all menus
     *
     * @return void
     */
    public function resetMenuindex()
    {
        $parents = [];

        $c = $this->modx->newQuery('modMenu');
        $c->groupby('parent');
        $menuObjects = $this->modx->getIterator('modMenu', $c);
        foreach ($menuObjects as $menuObject) {
            if ($menuObject->get('parent')) {
                $parents[] = $menuObject->get('parent');
            }
        }

        foreach ($parents as $parent) {
            $d = $this->modx->newQuery('modMenu');
            $d->where(['parent' => $parent]);
            $d->sortby('menuindex');

            $idx = 0;
            /** @var modMenu $menuObjects */
            $menuObjects = $this->modx->getIterator('modMenu', $d);
            foreach ($menuObjects as $menuObject) {
                $menuObject->set('menuindex', $idx);
                if (!$menuObject->save()) {
                    $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Menuindex of entry ' . $menuObject->get('text') . ' can\'t be saved!', '', 'MenuKeeper');
                }
                $idx++;
            }
        }
    }

    /**
     * @param modMenu $menuObject
     * @return void
     */
    private function cacheModMenu($menuObject)
    {
        $oldOptSetup = $this->modx->getOption(xPDO::OPT_SETUP);
        $this->modx->setOption(xPDO::OPT_SETUP, false);
        $menuObject->rebuildCache();
        $this->modx->setOption(xPDO::OPT_SETUP, $oldOptSetup);
    }
}
