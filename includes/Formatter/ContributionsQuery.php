<?php

namespace Flow\Formatter;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Data\RawSql;
use Flow\Exception\FlowException;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use BagOStuff;
use ContribsPager;
use User;

class ContributionsQuery extends AbstractQuery {

	/**
	 * @var BagOStuff
	 */
	protected $cache;

	public function __construct( ManagerGroup $storage, TreeRepository $treeRepository, BagOStuff $cache ) {
		parent::__construct( $storage, $treeRepository );
		$this->cache = $cache;
	}

	/**
	 * @param ContribsPager $pager Object hooked into
	 * @param string $offset Index offset, inclusive
	 * @param int $limit Exact query limit
	 * @param bool $descending Query direction, false for ascending, true for descending
	 * @return string|bool false on failure
	 */
	public function getResults( ContribsPager $pager, $offset, $limit, $descending ) {
		$target = $pager->target;
		$conditions = array( 'rev_user_wiki' => wfWikiId() );

		// Work out user condition
		if ( $pager->contribs == 'newbie' ) {
			list( $minUserId, $excludeUserIds ) = $this->getNewbieConditionInfo( $pager );

			$conditions[] = new RawSql( 'rev_user_id > '. (int) $minUserId );
			if ( $excludeUserIds ) {
				// better safe than sorry - make sure everything's an int
				$excludeUserIds = array_map( 'intval', $excludeUserIds );
				$conditions[] = new RawSql( 'rev_user_id NOT IN ( ' . implode( ',', $excludeUserIds ) . ' )' );
			}
		} else {
			$uid = User::idFromName( $target );
			if ( $uid ) {
				$conditions['rev_user_id'] = $uid;
			} else {
				$conditions['rev_user_ip'] = $target;
			}
		}

		// Make offset parameter.
		if ( $offset ) {
			$offsetUUID = UUID::getComparisonUUID( $offset );
			$direction = $descending ? '>' : '<';
			$offsetCondition = function( $db ) use ( $direction, $offsetUUID ) {
				return "rev_id $direction " . $db->addQuotes( $offsetUUID->getBinary() );
			};
			$conditions[] = new RawSql( $offsetCondition );
		}

		$types = array(
			// revision class => block type
			'PostRevision' => 'topic',
			'Header' => 'header',
		);

		$results = array();
		foreach ( $types as $revisionClass => $blockType ) {
			$revisions = $this->storage->find( $revisionClass, $conditions, array(
				'LIMIT' => $limit,
			) );
			$this->loadMetadataBatch( $revisions );
			foreach ( $revisions as $revision ) {
				try {
					$result = $this->buildResult( $revision, $blockType, $pager->getIndexField() );
					$result->flow_contribution = 'flow';
				} catch ( FlowException $e ) {
					$result = false;
					\MWExceptionHandler::logException( $e );
				}
				if ( $result ) {
					$results[] = $result;
				}
			}
		}

		return $results;
	}

	/**
	 * @param ContribsPager $pager
	 * @return array [minUserId, excludeUserIds]
	 */
	protected function getNewbieConditionInfo( ContribsPager $pager ) {
		// unlike most of Flow, this one doesn't use wfForeignMemcKey; needs
		// to be wiki-specific
		$key = wfMemcKey( 'flow', '', 'maxUserId', Container::get( 'cache.version' ) );
		$max = $this->cache->get( $key );
		if ( $max === false ) {
			// max user id not present in cache; fetch from db & save to cache for 1h
			$max = (int) $pager->getDatabase()->selectField( 'user', 'MAX(user_id)', '', __METHOD__ );
			$this->cache->set( $key, $max, 60 * 60 );
		}
		$minUserId = (int) ( $max - $max / 100 );

		// exclude all users withing groups with bot permission
		$excludeUserIds = array();
		$groupsWithBotPermission = User::getGroupsWithPermission( 'bot' );
		if ( count( $groupsWithBotPermission ) ) {
			$rows = $pager->getDatabase()->select(
				array( 'user', 'user_groups' ),
				'user_id',
				array(
					'user_id > ' . $minUserId,
					'ug_group' => $groupsWithBotPermission
				),
				__METHOD__,
				array(),
				array(
					'user_groups' => array(
						'INNER JOIN',
						array( 'ug_user = user_id' )
					)
				)
			);

			$excludeUserIds = array();
			foreach ( $rows as $row ) {
				$excludeUserIds[] = $row->user_id;
			}
		}

		return array( $minUserId, $excludeUserIds );
	}
}
