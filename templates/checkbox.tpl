{**
 * plugins/generic/customMetadata/checkbox.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Edit text input element 
 *
 *}
{assign var="customValueField" value="customValue_`$customValueName`"}

{fbvFormArea id=$customValueField}
	{fbvFormSection label=$fieldLabel for="source" description=$fieldDescription required=$required list=true}
		{fbvElement 
		type="checkbox" 
		name=$customValueField
		id=$customValueField 
		checked=$customValue
		required=$required
		label=$fieldOption
		translate=true}
	{/fbvFormSection}
{/fbvFormArea}
