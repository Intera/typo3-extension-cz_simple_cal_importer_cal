<?php
namespace Tx\CzSimpleCalImporterCal\Command;

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

use Tx\CzSimpleCal\Domain\Model\Address;
use Tx\CzSimpleCal\Domain\Model\Event;

/**
 * Command for importing cal events to cz_simple_cal.
 */
class CzCalImportCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

	/**
	 * @var \Tx\CzSimpleCalImporterCal\Domain\Repository\CalRepository
	 * @inject
	 */
	protected $calRepository;

	/**
	 * @var \Tx\CzSimpleCal\Domain\Repository\EventRepository
	 * @inject
	 */
	protected $eventRepository;

	/**
	 * @var \Tx\CzSimpleCalImporterCal\Import\FieldMapping
	 * @inject
	 */
	protected $fieldMapping;

	/**
	 * @var \Tx\CzSimpleCalImporterCal\Domain\Repository\ImportedEntityRepository
	 * @inject
	 */
	protected $importedEntityRepository;

	/**
	 * @var string
	 */
	protected $originalTable = '';

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * @var string
	 */
	protected $targetTable = '';

	/**
	 * Import cz_simple_cal data from cal
	 */
	public function importFromCalCommand() {

		$this->originalTable = 'tx_cal_event';
		$this->targetTable = 'tx_czsimplecal_domain_model_event';
		$table = $this->originalTable;

		foreach ($this->calRepository->findAllEvents() as $calEventRow) {

			$this->outputLine('Migrating event %s with UID %d', array($calEventRow['title'], $calEventRow['uid']));

			$event = $this->importedEntityRepository->getEntityForImportedUid($this->eventRepository, Event::class, $calEventRow);

			$fieldMapping = $this->fieldMapping->getMappingForEvents();

			foreach ($fieldMapping['properties'] as $calField => $eventFieldConfig) {
				if (empty($eventFieldConfig)) {
					continue;
				}
				if (is_string($eventFieldConfig)) {
					\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($event, $eventFieldConfig, $calEventRow[$calField]);
					continue;
				}
				if (!is_array($eventFieldConfig)) {
					throw new \RuntimeException(sprintf('Field config must be empty, a string or an array but was %s for %s field %s', gettype($eventFieldConfig), $table, $calField));
				}
				if (empty($eventFieldConfig['migrationMethod'])) {
					throw new \RuntimeException(sprintf('The migrationMethod method is missing for %s field %s', $table, $calField));
				}
				if (!array_key_exists($calField, $calEventRow)) {
					throw new \RuntimeException(sprintf('A non existing field is configured for %s: %s', $table, $calField));
				}
				$migrationMethod = 'migrate' . ucfirst($eventFieldConfig['migrationMethod']);
				if (!method_exists($this, $migrationMethod)) {
					throw new \RuntimeException(sprintf('The configured migration method %s does not exist for %s field %s', $migrationMethod, $table, $calField));
				}
				$this->$migrationMethod($event, $calEventRow, $calField, $eventFieldConfig);
			}

			$updateArray = [];
			foreach ($fieldMapping['tableFields'] as $calField => $newField) {
				$updateArray[$newField] = $calEventRow[$calField];
			}
			$this->getDatabaseConnection()->exec_UPDATEquery($this->targetTable, 'uid=' . (int)$event->getUid(), $updateArray);

			$this->eventRepository->update($event);
		}
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Migrates the data of inline addresses (location and organizer).
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $model
	 * @param array $originalRow
	 * @param string $originalField
	 * @param array $targetConfig
	 */
	protected function migrateAddressInline($model, array $originalRow, $originalField, array $targetConfig) {

		if (empty($targetConfig['field'])) {
			throw new \RuntimeException(sprintf('The field config is missing in target config for %s field %s', $this->originalTable, $originalField));
		}

		if (empty($originalRow[$originalField])) {
			\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($model, $targetConfig['field'], NULL);
			return;
		}

		$currentAddress = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getProperty($model, $targetConfig['field']);
		if (!isset($currentAddress)) {
			$currentAddress = $this->objectManager->get(Address::class);
			\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($model, $targetConfig['field'], $currentAddress);
		}

		$currentAddress->setName($originalRow[$originalField]);

		if (!empty($targetConfig['homepageFields'])) {
			foreach ($targetConfig['homepageFields'] as $homepageField) {
				if (!empty($originalRow[$homepageField])) {
					$currentAddress->setHomepage($originalRow[$homepageField]);
					break;
				}
			}
		}
	}

	/**
	 * Makes sure that start and end time are set to NULL if the current event is an allday event.
	 *
	 * @param Event $model
	 * @param array $originalRow
	 * @param string $originalField
	 * @param array $targetConfig
	 */
	protected function migrateEventAllDay(
		/** @noinspection PhpUnusedParameterInspection */
		$model, array $originalRow, $originalField, array $targetConfig
	) {
		if (empty($originalRow[$originalField])) {
			return;
		}
		$model->setStartTime(NULL);
		$model->setEndTime(NULL);
	}

	/**
	 * Converts the recurrance subtype.
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $model
	 * @param array $originalRow
	 * @param string $originalField
	 * @param array $targetConfig
	 */
	protected function migrateEventRecurranceSubtype($model, array $originalRow, $originalField, array $targetConfig) {
		// TODO: implement if needed!
	}

	/**
	 * Copies the FAL relations of the given field.
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $model
	 * @param array $originalRow
	 * @param string $originalField
	 * @param array $targetConfig
	 */
	protected function migrateFalField($model, array $originalRow, $originalField, array $targetConfig) {
		if (empty($targetConfig['tableField'])) {
			throw new \RuntimeException(sprintf('tableField config is missing for fal migration for %s field %s', $this->originalTable, $originalField));
		}
		$db = $this->getDatabaseConnection();
		$currentReferences = $db->exec_SELECTgetRows(
			'*',
			'sys_file_reference',
			'tablenames=' . $db->fullQuoteStr($this->originalTable, 'sys_file_reference') . ' AND fieldname=' . $db->fullQuoteStr($originalField, 'sys_file_reference') . ' AND uid_foreign=' . (int)$originalRow['uid']
		);
		foreach ($currentReferences as $reference) {

			$existingReferenceCount = $db->exec_SELECTcountRows(
				'uid', 'sys_file_reference',
				'tx_czsimplecalimportercal_imported_uid=' . (int)$reference['uid']
			);
			if ($existingReferenceCount > 0) {
				continue;
			}

			$reference['tablenames'] = $this->targetTable;
			$reference['fieldname'] = $targetConfig['tableField'];
			$reference['uid_foreign'] = (int)$model->getUid();
			$reference['tx_czsimplecalimportercal_imported_uid'] = $reference['uid'];
			unset($reference['uid']);

			$db->exec_INSERTquery('sys_file_reference', $reference);
		}
	}

	/**
	 * Migrates the value of the original field by using a configured value map.
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $model
	 * @param array $originalRow
	 * @param string $originalField
	 * @param array $targetConfig
	 */
	protected function migrateValueMap($model, array $originalRow, $originalField, array $targetConfig) {
		if (!is_array($targetConfig['valueMap']) || empty($targetConfig['valueMap'])) {
			throw new \RuntimeException(sprintf('No value map is configured for %s field %s', $this->originalTable, $originalField));
		}
		if (empty($targetConfig['field'])) {
			throw new \RuntimeException(sprintf('The field config is missing in target config for %s field %s', $this->originalTable, $originalField));
		}
		if (empty($originalRow[$originalField])) {
			return;
		}
		$valueMap = $targetConfig['valueMap'];
		$oldValue = $originalRow[$originalField];
		if (!array_key_exists($oldValue, $valueMap)) {
			throw new \RuntimeException('No value mapping was configured for value %s for %s field %s', $oldValue, $this->originalTable, $originalField);
		}
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($model, $targetConfig['field'], $valueMap[$oldValue]);
	}
}