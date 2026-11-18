<?php
/**
 * MenuKeeper
 *
 * Copyright 2022-2026 by Thomas Jakobi <office@treehillstudio.com>
 *
 * @package menukeeper
 * @subpackage classfile
 */

namespace TreehillStudio\MenuKeeper;

use modMenu;
use modX;
use PDO;
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
    public $version = '1.1.2-pl';

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
            'cachePermissions' => (bool)$this->getOption('cache_permissions', $options, false),
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
     * Cache the current parent, menuindex and permissions of each menu entry
     *
     * The 'menu' cache key holds the automatically tracked menu state, that is restored after a package
     * installation. The 'snapshot' cache key holds the menu state saved and loaded with the MenuKeeper menu.
     *
     * @param bool $reset Cache the menu state, even if it was not cached before
     * @param string $key The cache key
     * @return void
     */
    public function cacheMenu($reset = false, $key = 'menu')
    {
        $cacheManager = $this->modx->getCacheManager();
        $menu = $cacheManager->get($key, $this->cacheOptions);

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
                    'menuindex' => $menuObject->get('menuindex'),
                ];
                if ($this->getOption('cachePermissions')) {
                    $result[$menuObject->get('text')]['permissions'] = $menuObject->get('permissions');
                }
            }

            // Write the cache file only if the menu state has changed
            if ($result !== $menu) {
                $cacheManager->set($key, $result, 0, $this->cacheOptions);
                $this->debugLog('Menu state "' . $key . '" cached with ' . count($result) . ' menu entries.');
            }
        }
    }

    /**
     * Check if a menu state is cached
     *
     * @param string $key The cache key
     * @return bool
     */
    public function hasCachedMenu($key = 'menu')
    {
        return is_array($this->modx->getCacheManager()->get($key, $this->cacheOptions));
    }

    /**
     * Restore the cached parent, menuindex and permissions of each menu entry
     *
     * @param string $key The cache key
     * @return void
     */
    public function restoreMenu($key = 'menu')
    {
        // Don't rebuild the menu cache after saving each modMenu object
        $oldOptSetup = $this->modx->getOption(xPDO::OPT_SETUP);
        $this->modx->config[xPDO::OPT_SETUP] = true;

        $cacheManager = $this->modx->getCacheManager();
        $menus = $cacheManager->get($key, $this->cacheOptions);

        $changedMenuObject = null;
        $restored = 0;
        if (is_array($menus)) {
            foreach ($menus as $text => $menu) {
                /** @var modMenu $menuObject */
                $menuObject = $this->modx->getObject('modMenu', [
                    'text' => $text
                ]);
                if (!$menuObject) {
                    $this->debugLog('Menu entry "' . $text . '" of the menu state "' . $key . '" does not exist anymore and is skipped.');
                    continue;
                }
                $restorePermissions = $this->getOption('cachePermissions') && array_key_exists('permissions', $menu);
                if ($menuObject->get('parent') !== $menu['parent'] ||
                    $menuObject->get('menuindex') !== $menu['menuindex'] ||
                    ($restorePermissions && $menuObject->get('permissions') !== $menu['permissions'])
                ) {
                    $this->debugLog('Menu entry "' . $text . '" restored: parent "' . $menuObject->get('parent') . '" -> "' . $menu['parent'] . '", menuindex ' . $menuObject->get('menuindex') . ' -> ' . $menu['menuindex'] .
                        ($restorePermissions ? ', permissions "' . $menuObject->get('permissions') . '" -> "' . $menu['permissions'] . '"' : '') . '.');
                    $menuObject->set('parent', $menu['parent']);
                    $menuObject->set('menuindex', $menu['menuindex']);
                    if ($restorePermissions) {
                        $menuObject->set('permissions', $menu['permissions']);
                    }
                    if ($menuObject->save()) {
                        $changedMenuObject = $menuObject;
                        $restored++;
                    } else {
                        $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Menu state of ' . $text . ' can\'t be restored!', '', 'MenuKeeper');
                    }
                }
            }
            $this->debugLog($restored . ' menu entries restored from the menu state "' . $key . '".');
        } else {
            $this->debugLog('The menu state "' . $key . '" is not cached, so no menu entries are restored.');
        }

        $this->modx->config[xPDO::OPT_SETUP] = $oldOptSetup;

        if ($changedMenuObject) {
            $this->cacheModMenu($changedMenuObject);
        }
    }

    /**
     * Move the menu entries that are not cached to the end of their parent
     *
     * The menuindex of the other entries is only reset when such entries exist.
     *
     * @return void
     */
    public function sortMenu()
    {
        $cacheManager = $this->modx->getCacheManager();
        $menus = $cacheManager->get('menu', $this->cacheOptions);
        if (!is_array($menus)) {
            $this->debugLog('The menu state "menu" is not cached, so no menu entries are sorted.');
            return;
        }

        $newTexts = array_diff($this->getMenuTexts(), array_keys($menus));
        if (empty($newTexts)) {
            $this->debugLog('No new menu entries found, so no menu entries are sorted.');
            return;
        }

        // Don't rebuild the menu cache after saving each modMenu object
        $oldOptSetup = $this->modx->getOption(xPDO::OPT_SETUP);
        $this->modx->config[xPDO::OPT_SETUP] = true;

        $this->resetMenuindex();

        $c = $this->modx->newQuery('modMenu');
        $c->where(['text:IN' => array_values($newTexts)]);
        $c->sortby('parent');
        $c->sortby('menuindex');

        /** @var modMenu[] $menuObjects */
        $menuObjects = $this->modx->getIterator('modMenu', $c);

        // The next free menuindex of each parent, so that new entries don't share an index
        $nextIndex = [];
        $changedMenuObject = null;
        foreach ($menuObjects as $menuObject) {
            $parent = $menuObject->get('parent');
            if (!isset($nextIndex[$parent])) {
                $nextIndex[$parent] = $this->modx->getCount('modMenu', [
                    'parent' => $parent
                ]);
            }
            $menuObject->set('menuindex', $nextIndex[$parent]++);
            $changedMenuObject = $menuObject;
            $this->debugLog('New menu entry "' . $menuObject->get('text') . '" moved to the end of the parent "' . $parent . '" (menuindex ' . $menuObject->get('menuindex') . ').');
            if (!$menuObject->save()) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Menu entry ' . $menuObject->get('text') . ' can\'t be set to the last menuindex!', '', 'MenuKeeper');
            }
        }

        $this->modx->config[xPDO::OPT_SETUP] = $oldOptSetup;

        if ($changedMenuObject) {
            $this->cacheModMenu($changedMenuObject);
        }
    }

    /**
     * Get the text (the primary key) of all menu entries
     *
     * @return string[]
     */
    private function getMenuTexts()
    {
        $c = $this->modx->newQuery('modMenu');
        $c->select($this->modx->getSelectColumns('modMenu', 'modMenu', '', ['text']));
        if ($c->prepare() && $c->stmt->execute()) {
            return $c->stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'The menu entries can\'t be retrieved!', '', 'MenuKeeper');
        return [];
    }

    /**
     * Reset the menuindex of all menus
     *
     * @return void
     */
    public function resetMenuindex()
    {
        // Select only the grouped column, otherwise the query fails with ONLY_FULL_GROUP_BY
        $c = $this->modx->newQuery('modMenu');
        $c->select($this->modx->getSelectColumns('modMenu', 'modMenu', '', ['parent']));
        $c->groupby('parent');

        $parents = [];
        if ($c->prepare() && $c->stmt->execute()) {
            $parents = array_filter($c->stmt->fetchAll(PDO::FETCH_COLUMN));
        } else {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'The menu parents can\'t be retrieved!', '', 'MenuKeeper');
        }

        $count = 0;
        foreach ($parents as $parent) {
            $d = $this->modx->newQuery('modMenu');
            $d->where(['parent' => $parent]);
            $d->sortby('menuindex');

            $idx = 0;
            /** @var modMenu[] $menuObjects */
            $menuObjects = $this->modx->getIterator('modMenu', $d);
            foreach ($menuObjects as $menuObject) {
                $menuObject->set('menuindex', $idx);
                if (!$menuObject->save()) {
                    $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Menuindex of entry ' . $menuObject->get('text') . ' can\'t be saved!', '', 'MenuKeeper');
                }
                $idx++;
                $count++;
            }
        }
        $this->debugLog('Menuindex of ' . $count . ' menu entries in ' . count($parents) . ' parents reset.');
    }

    /**
     * Log a debug message in the MODX error log, if menukeeper.debug is enabled
     *
     * @param string $message
     * @return void
     */
    private function debugLog($message)
    {
        if ($this->getOption('debug')) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, $message, '', 'MenuKeeper');
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

    /**
     * Get the options used by the manager scripts, so that no server paths are output in the manager
     *
     * @param array|null $options Options to filter instead of the class options
     * @return array
     */
    public function getManagerOptions(?array $options = null): array
    {
        return array_intersect_key($options ?? $this->options, array_flip([
            'assetsUrl',
            'connectorUrl',
            'debug',
            'modxversion',
            'version',
        ]));
    }
}
