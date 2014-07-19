<?php
/**
 * Translations of the namespaces introduced by Flow.
 *
 * @file
 */

$namespaceNames = array();

// For wikis where the Flow extension is not installed.
if( !defined( 'NS_TOPIC' ) ) {
	define( 'NS_TOPIC', 2600 );
}

/** English */
$namespaceNames['en'] = array(
	NS_TOPIC =>  'Topic',
);

/** Hebrew */
$namespaceNames['he'] = array(
	NS_TOPIC =>  'נושא',
);

/** Russian */
$namespaceNames['ru'] = array(
	NS_TOPIC =>  'Тема',
);
