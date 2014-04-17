<?php

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Data\RevisionStorage;
use Flow\DbFactory;
use Flow\Model\UUID;
use Flow\Model\PostRevision;
use Flow\Search\Connection;
use Flow\Search\Updater;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Similar to CirrusSearch's forceSearchIndex, this will force indexing of Flow
 * data in ElasticSearch.
 *
 * @ingroup Maintenance
 */
class FlowForceSearchIndex extends Maintenance {
	// @todo: will need to steal a lot of code from Cirrus' ForceSearchIndex
	// @todo: this script has stolen some code from ContributionsQuery - refactor so it can use same code

	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	public function execute() {
		$this->dbFactory = Container::get( 'db.factory' );

		$fromId = $this->getOption( 'fromId', null );
		$fromId = $fromId ? UUID::create( $fromId ) : null;
		$toId = $this->getOption( 'toId', null );
		$toId = $toId ? UUID::create( $toId ) : null;
		$namespace = $this->getOption( 'namespace', null );
		$limit = $this->getOption( 'limit', null );

		// get query conditions & options based on given parameters
		$conditions = $this->buildConditions( $fromId, $toId, $namespace );
		$options = array();
		if ( $limit ) {
			$options['LIMIT'] = $limit;
		}

		// @todo: figure out these params
		$flags = array();
		$shardTimeout = $clientSideTimeout = 0;

		$updaters = array( // @todo: get from Container
			new \Flow\Search\TopicUpdater(),
			new \Flow\Search\HeaderUpdater()
		);
		foreach ( $updaters as $updater ) {
			$revisions = $updater->getRevisions( $conditions, $options );
			$updater->updateRevisions( $revisions, $shardTimeout, $clientSideTimeout, $flags );
		}
	}

	/**
	 * @param UUID|null $fromId
	 * @param UUID|null $toId
	 * @param int|null $namespace
	 * @return array
	 */
	protected function buildConditions( UUID $fromId = null, UUID $toId = null, $namespace = null ) {
		$dbr = $this->dbFactory->getDB( DB_SLAVE );

		$conditions = array();

		// only find entries in a given range
		if ( $fromId !== null ) {
			$conditions[] = 'rev_id > ' . $dbr->addQuotes( $fromId->getBinary() );
		}
		if ( $toId !== null ) {
			$conditions[] = 'rev_id <= ' . $dbr->addQuotes( $toId->getBinary() );
		}

		// find only within requested wiki/namespace
		$conditions['workflow_wiki'] = wfWikiId();
		if ( $namespace !== null ) {
			$conditions['workflow_namespace'] = $namespace;
		}

		return $conditions;
	}
}

$maintClass = 'FlowForceSearchIndex';
require_once RUN_MAINTENANCE_IF_MAIN;
