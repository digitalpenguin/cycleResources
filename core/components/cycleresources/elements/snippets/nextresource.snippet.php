<?php
$tpl = $modx->getOption('tpl', $scriptProperties, 'cycleResourcesDefaultTpl');
$cycleResources = $modx->getService(
    'cycleresources',
    'cycleResources',
    $modx->getOption('cycleresources.core_path',
        null, $modx->getOption('core_path').'components/cycleresources/').'model/cycleresources/'
);

$cycleResources->initialize($tpl,0);

try {
    echo $cycleResources->getNextResource();
} catch (Exception $e) {
    $modx->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage());
}