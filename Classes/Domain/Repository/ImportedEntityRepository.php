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

use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

/**
 * Utility functions for handling imported entities.
 */
class ImportedEntityRepository implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
	 * @inject
	 */
	protected $dataMapper;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * Searches in the database for an entity that is related to the UID of the given imprted record.
	 *
	 * If a matching record is found, it is returned.
	 *
	 * If no matching record is found a new entity will be added to the database and related to the
	 * given imported record.
	 *
	 * @param RepositoryInterface $repository
	 * @param string $entityClass
	 * @param array $importedRecord
	 * @return \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
	 */
	public function getEntityForImportedUid(RepositoryInterface $repository, $entityClass, array $importedRecord) {

		$existingUid = $this->getEntityUidForImportedUid($entityClass, $importedRecord['uid']);

		if (empty($existingUid)) {
			return $this->createEntityAndStoreImportedUid($repository, $entityClass, $importedRecord);
		} else {
			return $repository->findByUid($existingUid);
		}
	}

	/**
	 * Creates a new entity of the given type and relates it with the given imported record.
	 *
	 * @param RepositoryInterface $repository
	 * @param string $entityClass
	 * @param array $importedRecord
	 * @return \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
	 */
	protected function createEntityAndStoreImportedUid($repository, $entityClass, array $importedRecord) {

		$entity = $this->objectManager->get($entityClass);
		if (!$entity instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {
			throw new \RuntimeException('The entity class must be an instance of AbstractEntity');
		}

		if (isset($importedRecord['pid'])) {
			$entity->setPid($importedRecord['pid']);
		}
		$repository->add($entity);
		$this->persistenceManager->persistAll();

		$entityTableName = $this->dataMapper->getDataMap($entityClass)->getTableName();
		$this->getDatabaseConnection()->exec_UPDATEquery(
			$entityTableName,
			'uid=' . (int)$entity->getUid(),
			['tx_czsimplecalimportercal_imported_uid' => (int)$importedRecord['uid']]
		);

		return $entity;
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Tries to fetch the UID of the entity from the database that is related to
	 * the given imported record UID.
	 *
	 * @param string $entityClass
	 * @param int $importedUid
	 * @return int|null
	 */
	protected function getEntityUidForImportedUid($entityClass, $importedUid) {

		$entityTableName = $this->dataMapper->getDataMap($entityClass)->getTableName();

		$row = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
			'uid',
			$entityTableName,
			'tx_czsimplecalimportercal_imported_uid=' . (int)$importedUid
		);

		if (empty($row)) {
			return NULL;
		} else {
			return (int)$row['uid'];
		}
	}
}