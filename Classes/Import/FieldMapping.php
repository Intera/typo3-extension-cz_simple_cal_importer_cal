<?php
namespace Tx\CzSimpleCalImporterCal\Import;

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

use Tx\CzSimpleCal\Domain\Model\Enumeration\RecurranceType;
use Tx\CzSimpleCalImporterCal\Domain\Model\Enumeration\CalRecurranceType;

/**
 * Configuration class containing the mapping configuration for the cal tables.
 */
class FieldMapping implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Returns the mapping configuration for the cal events table.
	 *
	 * @return array
	 */
	public function getMappingForEvents() {
		return [
			'properties' => [
				'start_date' => 'startDay',
				'end_date' => 'endDay',
				'start_time' => 'startTime',
				'end_time' => 'endTime',
				'allday' => [
					'migrationMethod' => 'eventAllDay',
				],
				'timezone' => 'timezone',
				'title' => 'title',
				'calendar_id' => NULL,
				'category_id' => NULL,
				// Organizers need to be handled seperately
				'organizer' => [
					'field' => 'organizerInline',
					'migrationMethod' => 'addressInline',
					'homepageFields' => ['organizer_pid', 'organizer_link'],
				],
				'organizer_id' => NULL,
				'organizer_pid' => NULL,
				'organizer_link' => NULL,
				// Locations need to be handled seperately
				'location' => [
					'field' => 'locationInline',
					'migrationMethod' => 'addressInline',
					'homepageFields' => ['location_pid', 'location_link'],
				],
				'location_id' => NULL,
				'location_pid' => NULL,
				'location_link' => NULL,
				'teaser' => 'teaser',
				'description' => 'description',
				'freq' => [
					'field' => 'recurranceType',
					'migrationMethod' => 'valueMap',
					'valueMap' => [
						CalRecurranceType::NONE => RecurranceType::NONE,
						CalRecurranceType::DAY => RecurranceType::DAILY,
						CalRecurranceType::WEEK => RecurranceType::WEEKLY,
						CalRecurranceType::MONTH => RecurranceType::MONTHLY,
						CalRecurranceType::YEAR => RecurranceType::YEARLY,
					]
				],
				'until' => 'recurranceUntil',
				// Count is not supported by current recurrance model.
				'cnt' => NULL,
				'byday' => NULL,
				'bymonthday' => NULL,
				'bymonth' => NULL,
				// Interval is only supported by some recurrance types.
				'intrval' => [
					'migrationMethod' => 'eventRecurranceSubtype',
				],
				// Repetitions are not supported by cz_simple_cal.
				'rdate' => NULL,
				'rdate_type' => NULL,
				// Deviations are inline elements.
				'deviation' => NULL,
				'monitor_cnt' => NULL,
				'exception_cnt' => NULL,
				'fe_cruser_id' => 'cruserFe',
				'fe_crgroup_id' => NULL,
				'shared_user_cnt' => NULL,
				// We only have one event type.
				'type' => NULL,
				'page' => 'showPageInstead',
				'ext_url' => NULL,
				'isTemp' => NULL,
				'icsUid' => NULL,
				'image' => [
					'tableField' => 'images',
					'migrationMethod' => 'falField',
				],
				'attachment' => [
					'tableField' => 'files',
					'migrationMethod' => 'falField',
				],
				'ref_event_id' => NULL,
				'send_invitation' => NULL,
				'attendee' => NULL,
				'status' => 'status',
				'priority' => NULL,
				// Needs to be merged with status.
				'completed' => NULL,
				'no_auto_pb' => NULL,
			],
			'tableFields' => [
				'tstamp' => 'tstamp',
				'crdate' => 'crdate',
				'deleted' => 'deleted',
				'hidden' => 'hidden',
				'endtime' => 'enable_endtime',
				'cruser_id' => 'cruser_id',
				'sys_language_uid' => 'sys_language_uid',
				'l18n_parent' => 'l18n_parent',
				'l18n_diffsource' => 'l18n_diffsource',
			],
			// Versioning / Workspaces currently not supported.
			'unmigratedFields' => [
				't3ver_oid' => 't3ver_oid',
				't3ver_id' => 't3ver_id',
				't3ver_wsid' => 't3ver_wsid',
				't3ver_label' => 't3ver_label',
				't3ver_state' => 't3ver_state',
				't3ver_stage' => 't3ver_stage',
				't3ver_count' => 't3ver_count',
				't3ver_tstamp' => 't3ver_tstamp',
				't3_origuid' => 't3_origuid',
			]
		];
	}
}