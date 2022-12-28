<?php
/**
 * Abstract processor
 *
 * @package menukeeper
 * @subpackage processors
 */

namespace TreehillStudio\MenuKeeper\Processors;

use TreehillStudio\MenuKeeper\MenuKeeper;
use modProcessor;
use modX;

/**
 * Class Processor
 */
abstract class Processor extends modProcessor
{
    public $languageTopics = ['menukeeper:default'];

    /** @var MenuKeeper */
    public $menukeeper;

    /**
     * {@inheritDoc}
     * @param modX $modx A reference to the modX instance
     * @param array $properties An array of properties
     */
    function __construct(modX &$modx, array $properties = [])
    {
        parent::__construct($modx, $properties);

        $corePath = $this->modx->getOption('menukeeper.core_path', null, $this->modx->getOption('core_path') . 'components/menukeeper/');
        $this->menukeeper = $this->modx->getService('menukeeper', 'MenuKeeper', $corePath . 'model/menukeeper/');
    }

    abstract public function process();

    /**
     * Get a boolean property.
     * @param string $k
     * @param mixed $default
     * @return bool
     */
    public function getBooleanProperty($k, $default = null)
    {
        return ($this->getProperty($k, $default) === 'true' || $this->getProperty($k, $default) === true || $this->getProperty($k, $default) === '1' || $this->getProperty($k, $default) === 1);
    }
}
