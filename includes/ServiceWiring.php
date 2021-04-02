<?php

use Flow\DbFactory;
use Flow\FlowActions;
use MediaWiki\MediaWikiServices;

/**
 * Service wiring for Flow services
 *
 * Currently most services are defined in and retrieved
 * from the flow container, but they should be moved
 * here, see T170330.
 *
 * PHPUnit doesn't understand code coverage for code outside of classes/functions,
 * like service wiring files.
 * @codeCoverageIgnore
 *
 * @author DannyS712
 *
 * @phpcs-require-sorted-array
 */
return [
	'FlowActions' => static function ( MediaWikiServices $services ) : FlowActions {
		return new FlowActions(
			$services->getMainConfig()->get( 'FlowActions' )
		);
	},

	'FlowDbFactory' => static function ( MediaWikiServices $services ) : DbFactory {
		$config = $services->getMainConfig();
		return new DbFactory(
			$config->get( 'FlowDefaultWikiDb' ),
			$config->get( 'FlowCluster' )
		);
	},
];
