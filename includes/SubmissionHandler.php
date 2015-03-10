<?php

namespace Flow;

use DeferredUpdates;
use Flow\Block\AbstractBlock;
use Flow\Block\Block;
use Flow\Data\BufferedCache;
use Flow\Data\ManagerGroup;
use Flow\Exception\InvalidDataException;
use Flow\Exception\InvalidActionException;
use Flow\Model\Workflow;
use IContextSource;
use SplQueue;

class SubmissionHandler {

	/**
	 * @var ManagerGroup $storage
	 */
	protected $storage;

	/**
	 * @var DbFactory $dbFactory
	 */
	protected $dbFactory;

	/**
	 * @var BufferedCache $bufferedCache
	 */
	protected $bufferedCache;

	/**
	 * @var SplQueue Updates to add to DeferredUpdates post-commit
	 */
	protected $deferredQueue;

	public function __construct(
		ManagerGroup $storage,
		DbFactory $dbFactory,
		BufferedCache $bufferedCache,
		SplQueue $deferredQueue
	) {
		$this->storage = $storage;
		$this->dbFactory = $dbFactory;
		$this->bufferedCache = $bufferedCache;
		$this->deferredQueue = $deferredQueue;
	}

	/**
	 * @param Workflow $workflow
	 * @param IContextSource $context
	 * @param AbstractBlock[] $blocks
	 * @param string $action
	 * @param array $parameters
	 * @return AbstractBlock[]
	 * @throws InvalidActionException
	 * @throws InvalidDataException
	 */
	public function handleSubmit(
		Workflow $workflow,
		IContextSource $context,
		array $blocks,
		$action,
		array $parameters
	) {
		// since this is a submit force dbFactory to always return master
		$this->dbFactory->forceMaster();

		/** @var Block[] $interestedBlocks */
		$interestedBlocks = array();
		foreach ( $blocks as $block ) {
			// This is just a check whether the block understands the action,
			// Doesn't consider permissions
			if ( $block->canSubmit( $action ) ) {
				$block->init( $context, $action );
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

		// Check mediawiki core permissions for title protection, blocked
		// status, etc.
		if ( !$workflow->userCan( 'edit', $context->getUser() ) ) {
			reset( $interestedBlocks )->addError( 'block', wfMessage( 'blockedtitle' ) );
			return array();
		}

		$success = true;
		foreach ( $interestedBlocks as $block ) {
			$name = $block->getName();
			$data = isset( $parameters[$name] ) ? $parameters[$name] : array();
			$success &= $block->onSubmit( $data );
		}

		return $success ? $interestedBlocks : array();
	}

	/**
	 * @param Workflow $workflow
	 * @param AbstractBlock[] $blocks
	 * @return array Map from committed block name to an array of metadata returned
	 *  about inserted objects.
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

			// Now commit to cache. If this fails, cache keys should have been
			// invalidated, but still log the failure.
			if ( !$cache->commit() ) {
				wfDebugLog( 'Flow', __METHOD__ . ': Committed to database but failed applying to cache' );
			}
		} catch ( \Exception $e ) {
			while( !$this->deferredQueue->isEmpty() ) {
				$this->deferredQueue->dequeue();
			}
			$dbw->rollback();
			$cache->rollback();
			throw $e;
		}

		while( !$this->deferredQueue->isEmpty() ) {
			DeferredUpdates::addCallableUpdate( $this->deferredQueue->dequeue() );
		}

		$workflow->getArticleTitle()->purgeSquid();

		return $results;
	}
}
