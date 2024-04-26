<?php

use PKP\components\forms\FieldTextarea;
use PKP\components\forms\FieldText;
use \PKP\components\forms\FormComponent;

define("FORM_WORKFLOW_CUSTOM_META", "customMetadata");

class WorkflowMetaForm extends FormComponent {
	/** @copydoc FormComponent::$id */
	public $id = FORM_WORKFLOW_CUSTOM_META;

	/** @copydoc FormComponent::$method */
	public $method = 'POST';

	/**
	 * Constructor
	 *
	 * @param $action string URL to submit the form toed locales
	 * @param $publication publication to change settings for
	 */
	public function __construct($action, $submission) {
		/**
		 * @var $submissionFile SubmissionFile
		 */
		$this->action = $action;
		$this->successMessage = __('plugins.generic.jatsParser.publication.jats.fulltext.success');

			$this->addField(new FieldText("submissionId", array(
				'inputType' => 'hidden',	
				'value' => $submission->getId(),
			)));

			// Get the custom meta data fields for this submission
			$contextId = $submission->getData("contextId");
			$customMetadataDao = DAORegistry::getDAO('CustomMetadataDAO');
			$customFields = $customMetadataDao->getByContextId($contextId);			 
			while ($customField = $customFields->next()){
				if ($customField->getSectionId() == $submission->getSectionId() or $customField->getSectionId() == 0) {
					// Get the setting_name of the field
					$customValueField = "customValue".$customField->getId();
					if ($customField->getType() == "text") {
						$this->addField(new FieldText($customValueField, [
							'label' => __(LOC_KEY_PREFIX . $customField->getName() . ".label"),
							'description' => __(LOC_KEY_PREFIX . $customField->getName() . ".description"),
							'groupId' => 'default',
							'isRequired' => false,
							'value' => $submission->getData($customValueField),
							'size' => 'large'
						]));			
					} else if ($customField->getType() == "textarea") {
						$this->addField(new FieldTextarea($customValueField, [
							'label' => __(LOC_KEY_PREFIX . $customField->getName() . ".label"),
							'description' => __(LOC_KEY_PREFIX . $customField->getName() . ".description"),
							'groupId' => 'default',
							'isRequired' => false,
							'value' => $submission->getData($customValueField),
						]));			
					}
				}
			}
	}
}
