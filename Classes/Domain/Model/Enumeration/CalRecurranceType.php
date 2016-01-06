<?php
namespace Tx\CzSimpleCalImporterCal\Domain\Model\Enumeration;

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

use TYPO3\CMS\Core\Type\Enumeration;

/**
 * The recurrance types used in the cal Extension.
 */
class CalRecurranceType extends Enumeration {

	const DAY = 'day';

	const MONTH = 'month';

	const NONE = 'none';

	const WEEK = 'week';

	const YEAR = 'year';
}