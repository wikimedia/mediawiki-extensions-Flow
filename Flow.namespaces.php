<?php
/**
 * Translations of the namespaces introduced by MyExtension.
 *
 * @file
 */

$namespaceNames = array();

// For wikis where the MyExtension extension is not installed.
if( !defined( 'NS_TOPIC' ) ) {
	define( 'NS_TOPIC', 2600 );
}

/** English */
$namespaceNames['en'] = array(
	NS_TOPIC =>  'Topic',
);

