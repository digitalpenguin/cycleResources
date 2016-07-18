---------------------------------------
cycleResources
---------------------------------------
Version: 1.0.0-pl
Author: Murray Wood @ Digital Penguin
www.hkwebdeveloper.com
www.digitalpenguin.hk
---------------------------------------

DESCRIPTION
===========
MODX Revolution snippets designed to provide a link to the next or previous resource.
The current version will only link to resources at the same level.
Resources are sorted by the 'menuindex' property of the resources.

PROPERTIES
==========
&tpl string optional. Default cycleResourcesDefaultTpl

USAGE
=====
The newest and easiest way is to simply call [[nextResource]] for the next resource
and [[prevResource]] from the previous resource.

Use [[nextResource? &tpl=`myCustomTplChunk`]] to specify your own chunk for formatting.

------------------------------------------------------------------------------
For backwards compatibility, the [[cycleResources]] snippet is also included.
To use the default template, and provide the "next" resource simply copy and paste:
[[cycleResources]]

To use your own chunk for the template:
[[cycleResources? &tpl=`myCustomTplChunk`]]

If you want the generated link to be for the "previous" resource instead of the next:
[[cycleResources? &reverse=`1`]]
------------------------------------------------------------------------------

KNOWN ISSUES
============
On duplicating a resource, at the time of writing MODX core does not assign a new menuindex to the new resource,
so it's quite likely you'll end up with two resources that have the same menu index.
This can cause cycleResources to skip the original resource and only display the new resource.
The workaround for this is to make sure each resource has a unique menuindex.






