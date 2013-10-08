<?php

namespace Flow\RecentChanges;

use Flow\Container;
use Flow\UrlGenerator;
use ChangesList;
use Language;
use Linker;
use Html;
use RecentChange;
use Title;
use User;

class Formatter {
	public function __construct( UrlGenerator $urlGenerator, Language $lang ) {
		$this->urlGenerator = $urlGenerator;
		$this->lang = $lang;
	}

	public function format( ChangesList $cl, RecentChange $rc ) {
		$params = unserialize( $rc->getAttribute( 'rc_params' ) );
		$changeData = $params['flow-workflow-change'];

		if ( !is_array( $changeData ) ) {
			wfWarn( __METHOD__ . ': Flow data missing in recent changes.' );
			return false;
		}

		// used in $this->buildActionLinks()
		if ( !array_key_exists( 'type', $changeData ) ) {
			wfWarn( __METHOD__ . ': Flow change type missing' );
			return false;
		}

		$line = '';
		$title = $rc->getTitle();
		$links = $this->buildActionLinks( $title, $changeData );
		
		if ( $links ) {
			$linksContent = $cl->getLanguage()->pipeList( $links );
			$line .= wfMessage( 'parentheses' )->rawParams( $linksContent )->text()
				. $this->changeSeparator();
		}

		$line .= $this->workflowLink( $title, $changeData )
			. wfMessage( 'semicolon-separator' )->text()
			. $this->getTimestamp( $cl, $rc )
			. ' '
			. $this->changeSeparator()
			. ' '
			. $this->userLinks( $cl, $rc->getAttribute( 'rc_user_id' ), $rc->getAttribute( 'rc_user_text' ) )
			. ' '
			. $this->getActionDescription( $changeData );

		return $line;
	}

	protected function buildActionLinks( Title $title, array $changeData ) {
		$links = array();
		switch( $changeData['type'] ) {
		case 'flow-rev-message-reply':
			$links[] = $this->topicLink( $title, $changeData );
			break;

		case 'flow-rev-message-new-post': // fall through
		case 'flow-rev-message-edit-post':
			$links[] = $this->topicLink( $title, $changeData );
			$links[] = $this->postLink( $title, $changeData );
			break;

		case 'flow-rev-message-hid-comment':
			$links[] = $this->topicLink( $title, $changeData );
			$links[] = $this->postHistoryLink( $title, $changeData );
			break;

		case 'flow-rev-message-edit-title':
			$links[] = $this->topicLink( $title, $changeData );
			// This links to the history of the topic title
			$links[] = $this->postHistoryLink( $title, $changeData );
			break;

		case 'flow-rev-message-create-summary': // fall through
		case 'flow-rev-message-edit-summary':
			//$links[] = $this->workflowLink( $title, $changeData );
			break;

		case null:
			wfWarn( __METHOD__ . ': Flow change has null change type' );
			return false;

		default:
			wfWarn( __METHOD__ . ': Unknown Flow change type: ' . $changeData['type'] );
			return false;
		}

		return $links;
	}

	protected function changeSeparator() {
		return ' <span class="mw-changeslist-separator">. .</span> ';
	}

	protected function getTimestamp( $cl, $rc ) {
		return '<span class="mw-changeslist-date">'
				. $cl->getLanguage()->userTime( $rc->mAttribs['rc_timestamp'], $cl->getUser() )
			. '</span> ';
	}

	public function postHistoryLink( Title $title, array $changeData ) {
		return Html::rawElement(
			'a',
			array(
				'href' => $this->urlGenerator->buildUrl(
					$title,
					'post-history',
					array(
						'workflow' => $changeData['workflow'],
						'topic' => array( 'postId' => $changeData['post'] ),
					)
				),
			),
			wfMessage( 'flow-link-history' )->text()
		);
	}

	public function topicLink( Title $title, array $changeData ) {
		return Html::rawElement(
			'a',
			array(
				'href' => $this->urlGenerator->buildUrl(
					$title,
					'view',
					array( 'workflow' => $changeData['workflow'] )
				),
			),
			wfMessage( 'flow-link-topic' )->text()
		);
	}

	public function postLink( Title $title, array $changeData ) {
		return Html::rawElement(
			'a',
			array(
				'href' => $this->urlGenerator->buildUrl(
					$title,
					'view',
					array(
						'workflow' => $changeData['workflow'],
						'topic' => array( 'postId' => $changeData['post'] ),
					)
				),
			),
			wfMessage( 'flow-link-post' )
		);
	}

	public function userLinks( $cl, $userId, $userName ) {
		return Linker::userLink( $userId, $userName ) . Linker::userToolLinks( $userId, $userName );
	}

	protected function workflowLink( Title $title, array $changeData ) {
		list( $linkTitle, $query ) = $this->urlGenerator->buildUrlData(
			$title,
			'view',
			array( 'workflow' => $changeData['workflow'] )
		);

		return Html::element(
			'a',
			array( 'href' => $linkTitle->getFullUrl( $query ) ),
			$linkTitle->getPrefixedText()
		);
	}

	public function getActionDescription( array $changeData ) {
		$msg = wfMessage( $changeData['type'] )->text();

		if ( isset( $changeData['topic'] ) ) {
			$msg .= ' ' . wfMessage( 'parentheses' )->rawParams( $changeData['topic'] );
		}

		return $msg;
	}
}
