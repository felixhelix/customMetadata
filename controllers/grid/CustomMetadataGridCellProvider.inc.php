<?php

/**
 * @file controllers/grid/CustomMetadataGridCellProvider.inc.php
 *
 * Copyright (c) 2016-2023 Language Science Press
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CustomMetadataGridCellProvider
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');
import('lib.pkp.classes.linkAction.request.RedirectAction');

class CustomMetadataGridCellProvider extends GridCellProvider {

	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		$customMetadata = $row->getData();

		switch ($column->getId()) {
			case 'name':
				return array('label' => $customMetadata->getName());
			case 'type':
				return array('label' => $customMetadata->getType());
			case 'required':
				return array('label' => $customMetadata->getRequired());
			case 'section':
				return array('label' => implode(",",$customMetadata->getSectionIds()));				
		}

		return parent::getTemplateVarsFromColumn($row, $column);

	}
}

