<?php

/**
 * @file plugins/generic/customMetadata/classes/CustomMetadataDAO.inc.php
 *
 * Copyright (c) 2024 SOCIOS
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CustomMetadataDAO
 * @ingroup plugins_generic_customMetadata
 *
 * Operations for retrieving and modifying customMetadata objects.
 */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.customMetadata.classes.CustomMetadata');

class CustomMetadataDAO extends DAO {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}	

	/**
	 * Get a object for customMetadata by ID
	 * @param $objectId int CustomMetadata ID
	 */
	function getById(int $objectId) {
		$params = [(int) $objectId];

		$result = $this->retrieve(
			'SELECT * FROM custom_metadata WHERE custom_metadata_id = ?',
			$params
		);

		$row = $result->current();
		return $row ? $this->_fromRow((array) $row) : null;
	}

	/**
	 * Get all customMetadat objects for a given context
	 * @param $contextId int (optional) context ID
	 */
	function getByContextId($contextId = null) {
		$params = [];
		if ($contextId) $params[] = (int) $contextId;

		$result = $this->retrieve(
			'SELECT * FROM custom_metadata'
			. ($contextId?' WHERE context_id = ?':''),
			$params
		);

		return new DAOResultFactory($result, $this, '_fromRow');
	}



	/**
	 * Insert a custom metadata field.
	 * @param $customMetadata customMetadata
	 * @return int Inserted customMetadata ID
	 */
	function insertObject($customMetadata) {

		$this->update(
			'INSERT INTO custom_metadata (context_id, section_ids, name, type, required) VALUES (?, ?, ?, ?, ?)',
			array(
				(int) $customMetadata->getContextId(),
				(int) json_encode($customMetadata->getSectionIds()),
				(string) $customMetadata->getName(),
				(string) $customMetadata->getType(),
				(bool) $customMetadata->getRequired(),
			)
		);
		
		$customMetadata->setId($this->getInsertId());
		// $this->updateLocaleFields($customMetadata);
		return $customMetadata->getId();

	}

	/**
	 * Update the database with a customMetadata object
	 * @param $customMetadata customMetadata entity
	 */
	function updateObject($customMetadata) {
		$this->update(
			'UPDATE custom_metadata 
			SET context_id = ?, 
			section_id = ?,
			type = ?,
			name = ?,
			required = ?,
			WHERE custom_metadata_id = ?',
			array(
				(int) $customMetadata->getContextId(),
				(string) json_encode($customMetadata->getSectionIds()),				
				(string) $customMetadata->getType(),
				(string) $customMetadata->getName(),
				(bool) $customMetadata->getRequired(),
				(int) $customMetadata->getId()
			)
		);

	}

	/**
	 * Delete a customMetadata field by ID.
	 * @param $customMetadata int
	 */
	function deleteById($objectId) {
		$this->update(
			'DELETE FROM custom_metadata WHERE custom_metadata_id = ?',
			[(int) $objectId]
		);
	}

	/**
	 * Delete a customMetadata object.
	 * @param $customMetadata userComment
	 */
	function deleteObject($customMetadata) {
		$this->deleteById($customMetadata->getId());
	}

	/**
	 * Generate a new funder object.
	 * @return customMetadata
	 */
	function newDataObject() {
		return new customMetadata();
	}

	/**
	 * Return a new funder object from a given row.
	 * @return customMetadata
	 */
	function _fromRow($row) {
		$customMetadata = $this->newDataObject();
		$customMetadata->setId($row['custom_metadata_id']);
		$customMetadata->setContextId($row['context_id']);
		$customMetadata->setSectionIds(json_decode($row['section_ids'], true));
		$customMetadata->setName($row['name']);
		$customMetadata->setType($row['type']);		
		$customMetadata->setRequired($row['required']);		

		return $customMetadata;
	}

	/**
	 * Get the insert ID for the last inserted userComment.
	 * @return int
	 */
	function getInsertId() {
		return $this->_getInsertId('custom_metadata', 'custom_metadata_id');
	}

}

?>
