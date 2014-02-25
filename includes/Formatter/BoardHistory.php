<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Html;
use IContextSource;

class BoardHistory extends AbstractFormatter {

	/**
	 * @param \stdClass $row A result row from BoardHistoryQuery
	 * @param IContextSource $ctx
	 * @return string|false HTML string representing $row
	 */
	public function format( $row, IContextSource $ctx ) {
		try {
			return $this->formatReal( $row, $ctx );
		} catch ( FlowException $e ) {
			\MWExceptionHandler::logException( $e );
			return false;
		}
	}

	protected function formatReal( $row, IContextSource $ctx ) {
		$revision = $row->revision;
		$workflow = $row->workflow;
		$workflowId = $workflow->getId();
		$title = $workflow->getArticleTitle();

		$user = $ctx->getUser();
		$lang = $ctx->getLanguage();
		$perm = $this->getPermissions( $user );

		if ( !$perm->isAllowed( $revision, 'board-history' ) ) {
			return false;
		}

		if ( $revision instanceof PostRevision ) {
			if ( !$perm->isAllowed( $row->root_post, 'board-history' ) ) {
				return false;
			}
			$blockType = 'header';
		} elseif ( $revision instanceof Header ) {
			$blockType = 'topic';
		} else {
			throw new \Exception( 'Unknown revision type' );
		}

		$description = $this->getActionDescription( $workflowId, $blockType, $revision );
		$dateFormats = $this->getDateFormats( $revision, $user, $lang );

		list( $href, $msg ) = $this->topicLink( $title, $workflowId );

		$formattedTime = Html::element(
			'a',
			array(
				'href' => $href,
				'title' => $msg->text(),
			),
			$dateFormats['timeAndDate']
		);

		$linksContent = ''; // @todo
		$charDiff = $this->getCharDiff(
			$row->revision,
			$row->previous_revision
		);


		return $formattedTime
			. ' . . '
			. $description
			. ( $charDiff ? " . . $charDiff" : '' )
			. $linksContent;
	}

}
