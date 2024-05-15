{**
 * templates/customMetadataTab.tpl
 *
 * Copyright (c) 2024 SOCIOS
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Custom metadata plugin -- add a new tab to the settings interface.
 *}
<tab id="customMetadata" label="{translate key="plugins.generic.customMetadata.tabLabel"}">
	{capture assign=customMetadataGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.customMetadata.controllers.grid.CustomMetadataGridHandler" op="fetchGrid" escape=false}{/capture}
	{load_url_in_div id="customMetadataGridContainer" url=$customMetadataGridUrl}
</tab>
