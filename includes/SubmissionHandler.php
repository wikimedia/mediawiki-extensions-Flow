<?php

namespace Flow;

use Flow\Block\AbstractBlock;
use Flow\Model\Workflow;
use Flow\Data\BufferedCache;
use Flow\Data\ManagerGroup;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidActionException;
use WebRequest;

class SubmissionHandler {

	public function __construct( ManagerGroup $storage, DbFactory $dbFactory, BufferedCache $bufferedCache ) {
		$this->storage = $storage;
		$this->dbFactory = $dbFactory;
		$this->bufferedCache = $bufferedCache;
	}

	/**
	 * @param Workflow $workflow
	 * @param string $action
	 * @param AbstractBlock[] $blocks
	 * @param \User $user
	 * @param WebRequest $request
	 * @return AbstractBlock[]
	 * @throws InvalidActionException
	 * @throws InvalidDataException
	 */
	public function handleSubmit( Workflow $workflow, $action, array $blocks, $user, WebRequest $request ) {
		$success = true;
		$interestedBlocks = array();

		foreach ( $blocks as $block ) {
			// This is just a check whether the block understands the action,
			// Doesn't consider permissions
			if ( $block->canSubmit( $action ) ) {
				$interestedBlocks[] = $block;
			}
		}

		if ( !$interestedBlocks ) {
			if ( !$blocks ) {
				throw new InvalidDataException( 'No Blocks?!?', 'fail-load-data' );
			}
			$type = array();
			foreach ( $blocks as $block ) {
				$type[] = get_class( $block );
			}
			// All blocks returned null, nothing knows how to handle this action
			throw new InvalidActionException( "No block accepted the '$action' action: " .  implode( ',', array_unique( $type ) ), 'invalid-action' );
		}


		$params = $this->extractBlockParameters( $request, $blocks );
		foreach ( $interestedBlocks as $block ) {
			$data = $params[$block->getName()];
			$result = $block->onSubmit( $action, $user, $data );
			if ( $result !== null ) {
				$interestedBlocks[] = $block;
				$success &= $result;
			}
		}

		return $success ? $interestedBlocks : array();
	}

	/**
	 * @param Workflow $workflow
	 * @param AbstractBlock[] $blocks
	 * @return array
	 * @throws \Exception
	 */
	public function commit( Workflow $workflow, array $blocks ) {
		$cache = $this->bufferedCache;
		$dbw = $this->dbFactory->getDB( DB_MASTER );

		try {
			$dbw->begin();
			$cache->begin();
			// @todo doesn't feel right to have this here
			$this->storage->getStorage( 'Workflow' )->put( $workflow );
			$results = array();
			foreach ( $blocks as $block ) {
				$results[$block->getName()] = $block->commit();
			}
			$dbw->commit();
		} catch ( \Exception $e ) {
			$dbw->rollback();
			$cache->rollback();
			throw $e;
		}

		try {
			$cache->commit();
		} catch ( \Exception $e ) {
			wfWarn( __METHOD__ . ': Commited to database but failed applying to cache' );
			\MWExceptionHandler::logException( $e );
		}

		return $results;
	}

	/**
	 * Helper function extracts parameters from a WebRequest.
	 s
	 * @param WebRequest $request
	 * @param AbstractBlock[] $blocks
	 * @return array
	 */
	public function extractBlockParameters( WebRequest $request, array $blocks ) {
		$result = array();
		// BC for old parameters enclosed in square brackets
		foreach ( $blocks as $block ) {
			$name = $block->getName();
			$result[$name] = $request->getArray( $name, array() );
		}
		// BC for topic_list renamed to topiclist
		if ( isset( $result['topiclist'] ) && !$result['topiclist'] ) {
			$result['topiclist'] = $request->getArray( 'topic_list', array() );
		}
		// between urls only allowing [-_.] as unencoded special chars and
		// php mangling all of those into '_', we have to split on '_'
		$globalData = array();
		foreach ( $request->getValues() as $name => $value ) {
			if ( false !== strpos( $name, '_' ) ) {
				list( $block, $var ) = explode( '_', $name, 2 );
				// flow_xxx is global data for all blocks
				if ( $block === 'flow' ) {
					$globalData[$var] = $value;
				} else {
					$result[$block][$var] = $value;
				}
			}
		}

		foreach ( $blocks as $block ) {
			$result[$block->getName()] += $globalData;
		}

		return $result;
	}
}
