<?php

/**
 * @file controllers/grid/CustomMetadataGridHandler.inc.php
 *
 * Copyright (c) 2016-2023 Language Science Press
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CustomMetadataGridHandler
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('plugins.generic.customMetadata.controllers.grid.CustomMetadataGridCellProvider');
import('plugins.generic.customMetadata.classes.CustomMetadata');

class CustomMetadataGridHandler extends GridHandler {

	/** @var $form Form */
	var $form;

	/** The custom locale plugin */
	static $plugin;

	/**
	 * Set the custom locale plugin.
	 */
	static function setPlugin($plugin) {
		self::$plugin = $plugin;
	}

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_SITE_ADMIN),
			array('fetchGrid', 'editLocaleFile', 'updateLocale')
		);
	}

	/**
	 * @copydoc PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.ContextAccessPolicy');
		$this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	//
	// Overridden template methods
	//
	/**
	 * @copydoc Gridhandler::initialize()
	 */
	function initialize($request, $args=null) {

		parent::initialize($request, $args);

		// Set the grid details.
		$this->setTitle('plugins.generic.customMetadata.tabLabel');
		$this->setEmptyRowText('plugins.generic.customMetadata.noneCreated');

		// Columns
		$cellProvider = new CustomMetadataGridCellProvider();

		$this->addColumn(new GridColumn(
			'name',
			'plugins.generic.customMetadata.metadataFieldName',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));

		$this->addColumn(new GridColumn(
			'type',
			'plugins.generic.customMetadata.metadataFieldType',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));

		$this->addColumn(new GridColumn(
			'section',
			'plugins.generic.customMetadata.metadataFieldSection',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));		

		$this->addColumn(new GridColumn(
			'required',
			'plugins.generic.customMetadata.metadataFieldRequired',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));

	}

	/**
	 * @copydoc GridHandler::loadData()
	 */
	function loadData($request, $filter) {
		$context = $request->getContext();
		$customMetadataDao = DAORegistry::getDAO('CustomMetadataDAO');
		return $customMetadataDao->getByContextId()->toArray();
	}

}

