<?php

namespace Flow;

use DerivativeContext;
use MediaWiki\MessagePoster\IMessagePoster;
use MediaWiki\Linker\LinkTarget;
use MWException;
use RequestContext;
use Status;
use Title;
use User;

class FlowMessagePoster implements IMessagePoster {
	/**
	 * @var WorkflowLoaderFactory
	 */
	protected $workflowLoaderFactory;

	public function __construct() {
		$this->workflowLoaderFactory = Container::get( 'factory.loader.workflow' );
	}

	public function postTopic( LinkTarget $linkTarget, User $user, $subject, $body, $flags = 0 ) {
		$title = Title::newFromLinkTarget( $linkTarget );
		$status = Status::newGood();

		$derivativeContext = new DerivativeContext( RequestContext::getMain() );
		$derivativeContext->setUser( $user );

		$loader = $this->workflowLoaderFactory->createWorkflowLoader( $title );
		$blocks = $loader->getBlocks();

		$action = 'new-topic';
		$params = [
			'topiclist' => [
				'topic' => $subject,
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

		if ( !$status->isOK() ) {
			throw new MWException( $status->getMessage()->text() );
		}

		return $status;
	}
}
