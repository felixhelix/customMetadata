<?php

/**
 * @file plugins/generic/customMetadata/classes/CustomMetadata.inc.php
 *
 * Copyright (c) 2024 SOCIOS
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CustomMetadata
 */

class CustomMetadata extends DataObject {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}	

	//
	// Get/set methods
	//

	function setId($Id) {
		return $this->setData('customMetadataId', $Id);
	}	

	function getId() {
		return $this->getData('customMetadataId');
	}

	function getContextId() {
		return $this->getData('contextId');
	}

	function setContextId($contextId) {
		return $this->setData('contextId', $contextId);
	}

	function getSectionIds() {
		return $this->getData('sectionIds');
	}

	function setSectionIds($sectionIds) {
		return $this->setData('sectionIds', $sectionIds);
	}
	
	function getType() {
		return $this->getData('type');
	}

	function setType($type) {
		return $this->setData('type', $type);
	}

	function getName() {
		return $this->getData('name');
	}

	function setName($name) {
		return $this->setData('name', $name);
	}

	function getRequired() {
		return $this->getData('required');
	}

	function setRequired($required) {
		return $this->setData('required', $required);
	}

}

