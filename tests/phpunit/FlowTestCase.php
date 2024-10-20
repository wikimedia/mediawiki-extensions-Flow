<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Data\FlowObjectCache;
use Flow\DbFactory;
use Flow\Model\UUID;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWikiIntegrationTestCase;
use Wikimedia\ObjectCache\HashBagOStuff;
use Wikimedia\ObjectCache\WANObjectCache;

class FlowTestCase extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		Container::reset();
		parent::setUp();
	}

	/**
	 * @param mixed $data
	 * @return string
	 */
	protected function dataToString( $data ) {
		foreach ( $data as $key => $value ) {
			if ( $value instanceof UUID ) {
				$data[$key] = 'UUID: ' . $value->getAlphadecimal();
			}
		}

		return parent::dataToString( $data );
	}

	protected function getCache( DbFactory $dbFactory = null ) {
		global $wgFlowCacheTime;
		$wanCache = new WANObjectCache( [
			'cache' => new HashBagOStuff(),
			'pool' => 'testcache-hash',
		] );

		return new FlowObjectCache( $wanCache, $dbFactory ?? Container::get( 'db.factory' ), $wgFlowCacheTime );
	}

	protected function resetPermissions() {
		static $perms = null;

		if ( !$perms ) {
			$registry = new ExtensionRegistry();
			$data = $registry->readFromQueue( [ __DIR__ . '/../../extension.json' => 1 ] );

			if ( isset( $data['config']['GroupPermissions'] ) ) {
				// new extension info structure
				$perms = $data['config']['GroupPermissions'];
			} else {
				// old extension info structure
				$perms = $data['globals']['wgGroupPermissions'];
			}
			unset( $perms[$registry::MERGE_STRATEGY] );
		}

		$this->setGroupPermissions( $perms );
	}
}
