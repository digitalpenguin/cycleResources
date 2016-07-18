<?php
/**
 * Cycle Resources Snippet
 * -------------------------
 *
 * Author: Murray Wood @ Digital Penguin
 * www.hkwebdeveloper.com
 * www.digitalpenguin.hk
 *
 * DESCRIPTION
 *
 * A MODX snippet designed to provide a link to the next or previous resource.
 * The current version will only link to resources at the same level.
 * Resources are sorted by the 'menuindex' property of the resources.
 *
 * PROPERTIES
 *
 * &tpl string optional. Default cycleResourcesDefaultTpl
 * &reverse integer optional. Default 0
 *
 * USAGE
 *
 * To use the default template, and provide the "next" resource simply copy and paste:
 * [[cycleResources]]
 *
 * To use your own chunk for the template:
 * [[cycleResources? &tpl=`myCustomTplChunk`]]
 *
 * If you want the generated link to be for the "previous" resource instead of the next:
 * [[cycleResources? &reverse=`1`]]
 *
 *
 **/

$tpl = $modx->getOption('tpl', $scriptProperties, 'cycleResourcesDefaultTpl');
$reverse = (int)$modx->getOption('reverse',$scriptProperties, 0);
$cycleResources = $modx->getService(
    'cycleresources',
    'cycleResources',
    $modx->getOption('cycleresources.core_path',
        null, $modx->getOption('core_path').'components/cycleresources/').'model/cycleresources/'
);

$cycleResources->initialize($tpl,$reverse);

try {
    return $cycleResources->process();

} catch (Exception $e) {
    $modx->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage());
}