<?php

/**
 * @file plugins/generic/customMetadata/CustomMetadataPlugin.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CustomMetadataPlugin
 * @ingroup plugins_generic_customMetadata
 *
 * @brief CustomMetadata plugin class
 */

# TODO: Use $customField->getType() to switch between templates input/textarea 
# TODO: support multilingual input. Would require custom_metadata_settings table and some changes
# TODO: UI in the backend
# TODO: Field labels and description only showing a translation string
# TODO: input validation
 
import('lib.pkp.classes.plugins.GenericPlugin');
import('lib.pkp.classes.form.FormBuilderVocabulary');
import('plugins.generic.customMetadata.classes.components.forms.WorkflowMetaForm');

const LOC_KEY_PREFIX = "plugins.generic.customMetadata.";

class CustomMetadataPlugin extends GenericPlugin {

	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True if plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */

	public function register($category, $path, $mainContextId = NULL) {

		$success = parent::register($category, $path, $mainContextId);
		
		if ($success && $this->getEnabled($mainContextId)) {	
			
			import('plugins.generic.customMetadata.classes.CustomMetadataDAO');
			$customMetadataDao = new CustomMetadataDAO();
			DAORegistry::registerDAO('CustomMetadataDAO', $customMetadataDao);			

			// Add newCustomFields to publication schema
			HookRegistry::register('Schema::get::submission', array($this, 'addToSchema'));			

			// Add custom metadata fields to the submission wizard metadata form (submission step 3) 
			// ops3/lib/pkp/templates/submission/submissionMetadataFormFields.tpl
			HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', array($this, 'metadataFieldEditWizard'));
			// Hook for readUserVars -- consider the new field entries
			HookRegistry::register('submissionsubmitstep3form::readuservars', array($this, 'metadataReadUserVars'));
			// Hook for execute -- consider the new fields in the article settings
			HookRegistry::register('submissionsubmitstep3form::execute', array($this, 'metadataExecute'));
			// Hook for save  -- add validation for the new fields
			HookRegistry::register('submissionsubmitstep3form::Constructor', array($this, 'addCheck'));
			// Consider the new fields for ArticleDAO for storage
			// HookRegistry::register('articledao::getAdditionalFieldNames', array($this, 'articleSubmitGetFieldNames'));


			// Add custom metadata fields to the submission tab
			// Insert a new sub-tab with the custom metadata fields (PKP stopped using the template with the hook)
			HookRegistry::register('Template::Workflow::Publication', array($this, 'metadataFieldEdit'));
			// Add the API handler
			HookRegistry::register('Dispatcher::dispatch', array($this, 'setupAPIHandler'));

			// Add custom metadata tab for settings
			HookRegistry::register('Template::Settings::website', array($this, 'callbackShowWebsiteSettingsTabs'));
			HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));
			
			// Install database tables
			// $migration = $this->getInstallMigration();
			// $migration->up();
			// Need to manually add data for each custom field in the table and also 
			// provide the translation entries in the locale files
			// keys are plugins.generic.customMetadata.FIELDNAME.label and 
			// plugins.generic.customMetadata.FIELDNAME.description
		}
		
		return $success;;
	}

	/**
	 * @copydoc Plugin::getInstallMigration()
	 */
	function getInstallMigration() {
		$this->import('CustomMetadataSchemaMigration');
		return new CustomMetadataSchemaMigration();
	}

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.customMetadata.displayName');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.customMetadata.description');
	}

	/**
	 * @copydoc Plugin::getActions()
	 */
	function getActions($request, $verb) {
		$router = $request->getRouter();
		$dispatcher = $request->getDispatcher();
		import('lib.pkp.classes.linkAction.request.RedirectAction');		
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled()?[
				new LinkAction(
					'settings',
					new RedirectAction($dispatcher->url(
						$request, ROUTE_PAGE,
						null, 'management', 'settings', 'website',
						array('uid' => uniqid()), // Force reload
						'customMetadata' // Anchor for tab
					)),
					__('plugins.generic.customMetadata.settings'),
					null
				),				
			]:[],
			parent::getActions($request, $verb)
		);
	}

	/**
	 * Extend the website settings tabs to include custom locale
	 * @param $hookName string The name of the invoked hook
	 * @param $args array Hook parameters
	 * @return boolean Hook handling status
	 */
	function callbackShowWebsiteSettingsTabs($hookName, $args) {
		$templateMgr = $args[1];
		$output =& $args[2];

		$output .= $templateMgr->fetch($this->getTemplateResource('customMetadataTab.tpl'));

		// Permit other plugins to continue interacting with this hook
		return false;
	}

	/**
	 * Permit requests to the custom metadata grid handler
	 * @param $hookName string The name of the hook being invoked
	 * @param $args array The parameters to the invoked hook
	 */
	function setupGridHandler($hookName, $args) {
		$component = $args[0];
		if ($component == 'plugins.generic.customMetadata.controllers.grid.CustomMetadataGridHandler') {
			// Allow the custom locale grid handler to get the plugin object
			import($component);
			CustomMetadataGridHandler::setPlugin($this);
			return true;
		}
		return false;
	}

	/**
	 * @copydoc Plugin::setupAPIHandler
	 * We need this handler to update the data during the submission process
	 */
    public function setupAPIHandler($hookName, $request)
    {
        $router = $request->getRouter();
        if (!($router instanceof \APIRouter)) {
            return;
        }

        if (str_contains($request->getRequestPath(), 'api/v1/customMetadata')) {
            $this->import('api.v1.customMetadata.customMetadataHandler');
            $handler = new CustomMetadataHandler();
        }

        if (!isset($handler)) {
            return;
        }
		
        $router->setHandler($handler);	
        $handler->getApp()->run();	
        exit;
    }

	/*
	 * Metadata
	 */

    /**
     * Extend the articles entity's schema with a preprint id property
     */
    public function addToSchema(string $hookName, array $args) {
		$schema = $args[0]; /** @var stdClass */
		$contextId = $this->getCurrentContextId();		

		$customMetadataDao = DAORegistry::getDAO('CustomMetadataDAO');
		$customFields = $customMetadataDao->getByContextId($contextId);			 
		while ($customField = $customFields->next()){
			$propertyName = "customValue".$customField->getId();
			$schema->properties->$propertyName = (object) [
				'type' => 'string',
				'apiSummary' => true,
				'multilingual' => false,
				'validation' => ['nullable']
			];
		}
		return false;
    }  

	 
	/**
	 * Insert custom metadata fields into author submission step 3
	 */
	function metadataFieldEditWizard($hookName, $params) {
		$smarty =& $params[1];
		$output =& $params[2];
		
		$fbv = $smarty->getFBV();
		$form = $fbv->getForm();
		
		$submission = $form->submission;
		
		$contextId = $this->getCurrentContextId();
		$customMetadataDao = DAORegistry::getDAO('CustomMetadataDAO');
		$customFields = $customMetadataDao->getByContextId($contextId);			 

		while ($customField = $customFields->next()){
			if ($customField->getSectionId() == $submission->getSectionId() or $customField->getSectionId() == 0) {
				// Get the setting_name of the field
				$customValueField = $this->getcustomValueField($customField->getId());
				// Get the submission custom meta-data setting_value
				$smarty->assign('customValue', $submission->getData($customValueField));
				
				$smarty->assign(array(
					'type' => $customField->getType(),				
					'customValueId' => $customField->getId(),
					'fieldLabel' => LOC_KEY_PREFIX . $customField->getName() . ".label",
					'fieldDescription' => LOC_KEY_PREFIX . $customField->getName() . ".description",
					'required' => $customField->getRequired()
				));
				
				if ($customField->getType() == "text") {
					$output .= $smarty->fetch($this->getTemplateResource('textinput.tpl'));
				} else if ($customField->getType() == "textarea") {
					$output .= $smarty->fetch($this->getTemplateResource('textareainput.tpl'));
				}
			}
		}				

		return false;
	}

	/**
	 * Add custome metadata elements to the article
	 * hook: articledao::getAdditionalFieldNames
	 */
	function articleSubmitGetFieldNames($hookName, $params) {
		$fields =& $params[1];
		$contextId = $this->getCurrentContextId();
	
		$customMetadataDao = DAORegistry::getDAO('CustomMetadataDAO');
		$customFields = $customMetadataDao->getByContextId($contextId);		 
		while ($customField = $customFields->next()){
			$customValueField = "customValue".$customField->getId();
			$fields[] = $customValueField;
		}
		
		return false;
	}
	
	
	/**
	 * Concern custom metadata fields in the form
	 * hook: submissionsubmitstep3form::readuservars
	 */
	function metadataReadUserVars($hookName, $params) {
		$userVars =& $params[1];
		$contextId = $this->getCurrentContextId();
		
		$customMetadataDao = DAORegistry::getDAO('CustomMetadataDAO');
		$customFields = $customMetadataDao->getByContextId($contextId); 
		while ($customField = $customFields->next()){
			$customValueField = "customValue".$customField->getId();
			$userVars[] = $customValueField;
		}
		
		return false;
	}

	/**
	 * Set custom metadata field values
	 * hook: submissionsubmitstep3form::execute
	 */
	function metadataExecute($hookName, $params): void {
		$form =& $params[0];
		$contextId = $this->getCurrentContextId();
		
		$form =& $params[0];
		$article =& $form->submission;
		
		$customMetadataDao = DAORegistry::getDAO('CustomMetadataDAO');
		$customFields = $customMetadataDao->getByContextId($contextId);			 
		while ($customField = $customFields->next()){
			$customValueField = "customValue".$customField->getId();
			$customValue = $form->getData($customValueField);
			$article->setData($customValueField, $customValue);
		}
	}
	
	/**
	 * Add check/validation
	 */
	function addCheck($hookName, $params) {
		$form =& $params[0];
		
		# Requires some changes to the plugin database
		
		return false;
	}	

	/**
	 * Add custom metadata fields to the submission page
	 * @param string $hookname
	 * @param array $params [string, TemplateManager]
	 */	
	function metadataFieldEdit($hookName, $params): void {

		$request = $this->getRequest();
		$context = $request->getContext();
		$templateMgr = $params[1];

		$submission = $templateMgr->getTemplateVars('submission');
		$latestPublication = $submission->getLatestPublication();
		$latestPublicationApiUrl = $request->getDispatcher()->url($request, ROUTE_API, $context->getData('urlPath'), 'customMetadata/update/' . $submission->getId());

		$form = new WorkflowMetaForm($latestPublicationApiUrl, $submission);
		$state = $templateMgr->getTemplateVars('state');
		$state['components'][FORM_WORKFLOW_CUSTOM_META] = $form->getConfig();
		$templateMgr->assign(['state'=> $state,
		]);	

		$templateMgr->display($this->getTemplateResource("workflowTab.tpl"));	
	}

	/**
	 * Helper functions
	 */

	function getCurrentContextId() {
		$contextId = null;
		$request = $this->getRequest();
		$context = $request->getContext();
		if ($context) $contextId = $context->getId();		
		return $contextId;
		
	}
	
	function getcustomValueField($customValueId) {
			return "customValue".$customValueId;
	}		
	
	

}
?>
