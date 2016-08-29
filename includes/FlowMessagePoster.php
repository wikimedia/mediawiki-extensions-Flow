<?php

namespace Flow;

use DerivativeContext;
use MediaWiki\MessagePoster\IMessagePoster;
use MWException;
use RequestContext;
use Status;
use User;

class FlowMessagePoster implements IMessagePoster {
	/**
	 * @var Title
	 */
	protected $boardTitle;

	/**
	 * @var WorkflowLoaderFactory
	 */
	protected $workflowLoaderFactory;

	public function __construct( $boardTitle ) {
		$this->boardTitle = $boardTitle;

		$this->workflowLoaderFactory = Container::get( 'factory.loader.workflow' );
	}

	public function post( User $user, $topicTitle, $body, $bot = false ) {
		$status = Status::newGood();

		$derivativeContext = new DerivativeContext( RequestContext::getMain() );
		$derivativeContext->setUser( $user );

		$loader = $this->workflowLoaderFactory->createWorkflowLoader( $this->boardTitle );
		$blocks = $loader->getBlocks();

		$action = 'new-topic';
		$params = [
			'topiclist' => [
				'topic' => $topicTitle,
				'content' => $body,
				'format' => 'wikitext',
			],
		];

		$blocksToCommit = $loader->handleSubmit(
			$derivativeContext,
			$action,
			$params
		);

		foreach( $blocks as $block ) {
			if ( $block->hasErrors() ) {
				$errors = $block->getErrors();

				foreach( $errors as $errorKey ) {
					$status->fatal( $block->getErrorMessage( $errorKey ) );
				}
			}
		}

		$loader->commit( $blocksToCommit );

		if ( !$status->isGood() ) {
			throw new MWException( $status->getMessage()->text() );
		}
	}
}
