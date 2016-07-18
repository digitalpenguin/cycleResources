<?php

/**
 * The main cycleResources service class.
 *
 * @package cycleresources
 */
class cycleResources {
    public $modx = null;
    public $namespace = 'cycleresources';
    public $cache = null;
    public $options = array();
    public $removeUnpublished = true;
    public $reverse = 0; // 1 for next, 0 for prev.

    public $resourceIds = array();
    public $resources = array();
    public $nextResource = null;
    public $prevResource = null;

    public $currentMenuIndex = null;
    public $lowestMenuIndex = null;
    public $highestMenuIndex = null;
    public $nextMenuIndex = null;
    public $prevMenuIndex = null;

    public $tpl = 'cycleResourcesDefaultTpl';
    public $outputArray = array();

    public function __construct(modX &$modx, array $options = array()) {
        $this->modx =& $modx;
        $this->namespace = $this->getOption('namespace', $options, 'cycleresources');

        $corePath = $this->getOption('core_path', $options, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/cycleresources/');
        $assetsPath = $this->getOption('assets_path', $options, $this->modx->getOption('assets_path', null, MODX_ASSETS_PATH) . 'components/cycleresources/');
        $assetsUrl = $this->getOption('assets_url', $options, $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/cycleresources/');

        /* loads some default paths for easier management */
        $this->options = array_merge(array(
            'namespace' => $this->namespace,
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'templatesPath' => $corePath . 'templates/',
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'connectorUrl' => $assetsUrl . 'connector.php'
        ), $options);

        $this->modx->addPackage('cycleresources', $this->getOption('modelPath'));
        $this->modx->lexicon->load('cycleresources:default');
    }

    public function initialize($tpl, $reverse) {
        if (isset($tpl)) {
            $this->tpl = $tpl;
         }

        if (isset($reverse)) {
            $this->reverse = $reverse;
        }
        return true;
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
    public function getOption($key, $options = array(), $default = null) {
        $option = $default;
        if (!empty($key) && is_string($key)) {
            if ($options != null && array_key_exists($key, $options)) {
                $option = $options[$key];
            } elseif (array_key_exists($key, $this->options)) {
                $option = $this->options[$key];
            } elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}.{$key}");
            }
        }
        return $option;
    }

    /**
     * Get an array of resource ids that included in the resource cycle.
     *
     * @return array The resource ids to be sorted.
     */
    public function getResourceList() {
        // Get array of parent ids from current resource
        $parentIds = $this->modx->getParentIds($this->modx->resource->id);
        // First in array will be closest parent
        $parentId = $parentIds[0];
        // Get all the ids under this parent
        return $this->modx->getChildIds($parentId, 1);
    }

    /**
     * @return array Returns array of resource objects.
     * @throws Exception If the id array parameter is empty
     */
    public function removeUnpublished() {
        $publishedResources = array();
        if (!empty($this->resourceIds)) {
            // Remove unpublished resources from list
            foreach ($this->resourceIds as $childId) {
                $childResource = $this->modx->getObject('modResource', $childId);
                if($childResource->published === 1) {
                    array_push($publishedResources, $childResource);
                }
            }
            $this->resources = $publishedResources;
            return true;
        } else {
            throw new Exception("Unable to remove unpublished resources from an empty array.");
        }
    }

    /**
     * @return bool Returns success on completion.
     */
    public function getMenuIndexRange() {
        // Find highest and lowest menu indexes
        $this->lowestMenuIndex = $this->currentMenuIndex = $this->modx->resource->menuindex;
        $this->highestMenuIndex = 0;
        foreach($this->resources as $res) {
            if ($res->menuindex > $this->highestMenuIndex) {
                $this->highestMenuIndex = $res->menuindex;
            }
            if ($res->menuindex < $this->lowestMenuIndex) {
                $this->lowestMenuIndex = $res->menuindex;

            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function defineNextResource()
    {
        // Make an array of all the resource objects with higher than current menuindex
        $higherIndexedResources = array();
        foreach ($this->resources as $res) {
            if ($this->currentMenuIndex === $this->highestMenuIndex) {
                $this->nextMenuIndex = $this->lowestMenuIndex;
                $this->nextResource = $res;
                break;
            } else if ($res->menuindex > $this->currentMenuIndex) {
                array_push($higherIndexedResources, $res);
            }
        }

        // Find the lowest menuindex from the higherIndexedResources array. This will be the next resource unless current resource is highest
        if (count($higherIndexedResources)) {
            $this->nextMenuIndex = $this->highestMenuIndex;
            foreach ($higherIndexedResources as $higherIndexedResource) {
                if ($higherIndexedResource->menuindex <= $this->nextMenuIndex) {
                    $this->nextMenuIndex = $higherIndexedResource->menuindex;
                    $this->nextResource = $higherIndexedResource;
                }
            }
        }

        // Convert all the resource fields into an array
        if ($this->outputArray = $this->nextResource->toArray())
            return true;
        else
            return false;

    }

    /**
     * @return bool
     */
    public function definePrevResource() {
        // Make an array of all the resource objects with lower than current menuindex
        $lowerIndexedResources = array();
        foreach ($this->resources as $res) {
            if($this->currentMenuIndex === $this->lowestMenuIndex) {
                $this->prevMenuIndex = $this->highestMenuIndex;
                $this->prevResource = $res;
            } else if($res->menuindex < $this->currentMenuIndex) {
                array_push($lowerIndexedResources, $res);
            }
        }

        // Find the highest menuindex from the lowerIndexedResources array. This will be the previous resource unless current resource is lowest
        if(count($lowerIndexedResources)) {
            foreach($lowerIndexedResources as $lowerIndexedResource) {
                if ($lowerIndexedResource->menuindex >= $this->prevMenuIndex) {
                    $this->prevMenuIndex = $lowerIndexedResource->menuindex;
                    $this->prevResource = $lowerIndexedResource;
                }
            }
        }
        // Convert all the resource fields into an array
        if ($this->outputArray = $this->prevResource->toArray())
            return true;
        else
            return false;
    }

    /**
     * @return string
     */
    public function getNextResource() {
        return $this->process();
    }

    /**
     * @return string
     */
    public function getPrevResource() {
        return $this->process(1); // Argument of 1 triggers previous resource.
    }

    /**
     * @param int $prev
     * @return string
     * @throws Exception
     */
    public function process($prev = 0) {
        $this->resourceIds = $this->getResourceList();
        if ($this->removeUnpublished) $this->removeUnpublished();
        $this->getMenuIndexRange();
        if($this->reverse == 0 && $prev == 0)
            $this->defineNextResource();
        else
            $this->definePrevResource();
        // Link the array to the chunk
        $output = $this->modx->getChunk($this->tpl,$this->outputArray);

        return $output;
    }
}