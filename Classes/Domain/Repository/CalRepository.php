<?php
namespace Tx\CzSimpleCalImporterCal\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Extension                             *
 * "cz_simple_cal_importer_cal".                                          *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Repository for handling cal records.
 */
class CalRepository implements SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $db;

	/**
	 * Initialize the database connection.
	 */
	public function __construct() {
		$this->db = $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Returns all events from the database.
	 * @return array
	 */
	public function findAllEvents() {
		return $this->db->exec_SELECTgetRows('*', 'tx_cal_event', '');
	}
}