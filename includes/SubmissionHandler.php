<?php

namespace Flow;

use Flow\Block\AbstractBlock;
use Flow\Block\Block;
use Flow\Data\ManagerGroup;
use Flow\Exception\FailCommitException;
use Flow\Exception\InvalidActionException;
use Flow\Exception\InvalidDataException;
use Flow\Model\Workflow;
use MediaWiki\Context\IContextSource;
use MediaWiki\Deferred\DeferredUpdates;
use MediaWiki\Json\FormatJson;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use SplQueue;

class SubmissionHandler {

	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var SplQueue Updates to add to DeferredUpdates post-commit
	 */
	protected $deferredQueue;

	public function __construct(
		ManagerGroup $storage,
		DbFactory $dbFactory,
		SplQueue $deferredQueue
	) {
		$this->storage = $storage;
		$this->dbFactory = $dbFactory;
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
		// since this is a submit force dbFactory to always return primary
		$this->dbFactory->forcePrimary();

		/** @var Block[] $interestedBlocks */
		$interestedBlocks = [];
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
			$type = [];
			foreach ( $blocks as $block ) {
				$type[] = get_class( $block );
			}
			// All blocks returned null, nothing knows how to handle this action
			throw new InvalidActionException( "No block accepted the '$action' action: " .
				implode( ',', array_unique( $type ) ), 'invalid-action' );
		}

		// Check mediawiki core permissions for title protection, blocked
		// status, etc.
		$errors = $workflow->getPermissionErrors( 'edit', $context->getUser(), 'secure' );
		if ( count( $errors ) ) {
			LoggerFactory::getInstance( 'Flow' )->debug( 'Got permission errors for user {user} attempting action "{action}".',
				[
					'action' => $action,
					'user' => $context->getUser()->getName(),
					'errors' => FormatJson::encode( $errors )
				]
			);
			foreach ( $errors as $errorMsgArgs ) {
				$msg = wfMessage( array_shift( $errorMsgArgs ) );
				if ( $errorMsgArgs ) {
					$msg->params( $errorMsgArgs );
				}
				// I guess this is the "user block" meaning of 'block'.  If
				// so, this is misleading, since it could be protection,
				// etc.  The specific error message (protect, block, etc.)
				// will still be output, though.
				// In theory, something could be relying on the string 'block',
				// since it's exposed to the API, but probably not.
				reset( $interestedBlocks )->addError( 'block', $msg );
			}
			return [];
		}

		$success = true;
		foreach ( $interestedBlocks as $block ) {
			$name = $block->getName();
			$data = $parameters[$name] ?? [];
			$success = $block->onSubmit( $data ) && $success;
		}

		return $success ? $interestedBlocks : [];
	}

	/**
	 * @param Workflow $workflow
	 * @param AbstractBlock[] $blocks
	 * @return array Map from committed block name to an array of metadata returned
	 *  about inserted objects.  This must be non-empty.  An empty block array
	 *  indicates there were errors, in which case this method should not be called.
	 * @throws \Exception
	 */
	public function commit( Workflow $workflow, array $blocks ) {
		$dbw = $this->dbFactory->getDB( DB_PRIMARY );

		/** @var OccupationController $occupationController */
		$occupationController = Container::get( 'occupation_controller' );
		$title = $workflow->getOwnerTitle();

		if ( count( $blocks ) === 0 ) {
			// This is a logic error in the code, but we need to preserve
			// consistent state.
			throw new FailCommitException(
				__METHOD__ . ' was called with $blocks set to an empty ' .
				'array or a falsy value.  This indicates the blocks are ' .
				'not able to commit, so ' . __METHOD__ . ' should not be ' .
				'called.',
				'fail-commit'
			);
		}

		try {
			$dbw->startAtomic( __METHOD__ );
			$services = MediaWikiServices::getInstance();
			// Create the occupation page/revision if needed
			$occupationController->ensureFlowRevision(
				$services->getWikiPageFactory()->newFromTitle( $title ),
				$workflow
			);
			// Create/modify each Flow block as requested
			$results = [];
			foreach ( $blocks as $block ) {
				$results[$block->getName()] = $block->commit();
			}
			$dbw->endAtomic( __METHOD__ );

			while ( !$this->deferredQueue->isEmpty() ) {
				DeferredUpdates::addCallableUpdate( $this->deferredQueue->dequeue() );
			}
			$htmlCache = $services->getHtmlCacheUpdater();
			$htmlCache->purgeTitleUrls( $workflow->getArticleTitle(), $htmlCache::PURGE_INTENT_TXROUND_REFLECTED );

			return $results;
		} finally {
			while ( !$this->deferredQueue->isEmpty() ) {
				$this->deferredQueue->dequeue();
			}
		}
	}
}
