<?php
$EM_CONF[$_EXTKEY] = array(
	'title' => 'Simple calendar using Extbase - cal importer',
	'description' => 'Import events from the cal extension to cz_simple_cal.',
	'category' => 'plugin',
	'state' => 'alpha',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'author' => 'Alexander Stehlik',
	'author_email' => 'astehlik@intera.de',
	'author_company' => 'Intera GmbH',
	'version' => '1.0.0',
	'_md5_values_when_last_written' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.3-9.15.99',
		),
		'conflicts' => array(),
		'suggests' => array(
			'cz_simple_cal' => '',
		),
	),
);
