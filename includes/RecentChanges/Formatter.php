<?php

namespace Flow\RecentChanges;

use Flow\Container;
use ChangesList;
use Linker;
use RecentChange;
use Title;
use User;

class Formatter {
	const MAX_TOPIC_LENGTH = 50;

	static public function format( ChangesList $cl, RecentChange $rc ) {
		$params = unserialize( $rc->getAttribute( 'rc_params' ) );
		$changeData = $params['flow-workflow-change'];

		if ( !is_array( $changeData ) ) {
			wfWarn( __METHOD__ . ': Flow data missing in recent changes.' );
			return false;
		}

		// used in self::buildActionLinks()
		if ( !array_key_exists( 'type', $changeData ) ) {
			wfWarn( __METHOD__ . ': Flow change type missing' );
			return false;
		}

		$line = '';
		$title = self::buildTitle( $rc, $changeData );
		$links = self::buildActionLinks( $title, $changeData );
		if ( $links === false ) {
			return;
		}
		if ( $links ) {
			$linksContent = $cl->getLanguage()->pipeList( $links );
			$line .= wfMessage( 'parentheses' )->rawParams( $linksContent )->text()
				. self::changeSeparator();
		}

		$line .= Linker::link( $title )
			. wfMessage( 'semicolon-separator' )->text()
			. self::getTimestamp( $cl, $rc )
			. ' '
			. self::changeSeparator()
			. ' '
			. self::userLinks( $cl, $rc->getAttribute( 'rc_user_text' ) )
			. ' '
			. self::getComment( $changeData );

		return $line;
	}

	protected static function buildTitle( RecentChange $rc, array $changeData ) {
		$title = $rc->getTitle();
		return $title;
	}

	protected static function buildActionLinks( Title $title, array $changeData ) {
		$links = array();
		switch( $changeData['type'] ) {
		case 'flow-rev-message-reply':
			$links[] = self::topicLink( $title, $changeData );
			break;

		case 'flow-rev-message-new-post': // fall through
		case 'flow-rev-message-edit-post':
			$links[] = self::topicLink( $title, $changeData );
			$links[] = self::postLink( $title, $changeData );
			break;

		case 'flow-rev-message-hid-comment':
			$links[] = self::topicLink( $title, $changeData );
			$links[] = self::postHistoryLink( $title, $changeData );
			break;

		case 'flow-rev-message-edit-title':
			$links[] = self::topicLink( $title, $changeData );
			// This links to the history of the topic title
			$links[] = self::postHistoryLink( $title, $changeData );
			break;

		case 'flow-rev-message-create-summary': // fal through
		case 'flow-rev-message-edit-summary':
			$links[] = self::workflowLink( $title, $changeData );
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

	protected static function changeSeparator() {
		return ' <span class="mw-changeslist-separator">. .</span> ';
	}

	protected static function getTimestamp( $cl, $rc ) {
		return '<span class="mw-changeslist-date">'
				. $cl->getLanguage()->userTime( $rc->mAttribs['rc_timestamp'], $cl->getUser() )
			. '</span> ';
	}

	public static function postHistoryLink( Title $title, array $changeData ) {
		return Linker::link(
			$title,
			wfMessage( 'flow-link-history' ),
			array(),
			array(
				'action' => 'post-history',
				'workflow' => $changeData['workflow'],
				'topic' => array(
					'postId' => $changeData['post'],
				),
			)
		);
	}

	public static function topicLink( Title $title, array $changeData ) {
		return Linker::link(
			$title,
			wfMessage( 'flow-link-topic' ),
			array(),
			array(
				'action' => 'view',
				'workflow' => $changeData['workflow'],
			)
		);
	}

	public static function postLink( Title $title, array $changeData ) {
		return Linker::link(
			$title,
			wfMessage( 'flow-link-post' ),
			array(),
			array(
				'action' => 'view',
				'workflow' => $changeData['workflow'],
				'topic' => array(
					'postId' => $changeData['post'],
				),
			)
		);
	}

	public static function userLinks( $cl, $userName ) {
		if ( User::isIP( $userName ) ) {
			$links = self::userContribsLink( $userName, $userName )
				. wfMessage( 'word-separator' )->plain()
				. wfMessage( 'parentheses' )->rawParams( self::userTalkLink( $userName ) )->text();
		} else {
			$tools = array(
				self::userTalkLink( $userName ),
				self::userContribsLink( $userName )
			);

			$links = self::userLink( $userName )
				. wfMessage( 'word-separator' )->plain()
				. '<span class="mw-usertoollinks">'
				. wfMessage( 'parentheses' )->rawParams( $cl->getLanguage()->pipeList( $tools ) )->text()
				. '</span> ';
		}
		return $links;
	}

	protected static function userContribsLink( $userName, $text = null ) {
		if ( $text === null ) {
			$text = wfMessage( 'contribslink' );
		}
		return Linker::link( Title::newFromText( "Contributions/$userName", NS_SPECIAL ), $text );
	}

	protected static function userTalkLink( $userName ) {
		return Linker::link(
			Title::newFromText( $userName, NS_USER_TALK ),
			wfMessage( 'talkpagelinktext' )->text()
		);
	}

	protected static function userLink( $userName ) {
		return Linker::link(
			Title::newFromText( $userName, NS_USER ),
			$userName,
			array( 'class' => 'mw-userlink' )
		);
	}

	protected static function workflowLink( Title $title, array $changeData ) {
		return Linker::link(
			$title,
			wfMessage( 'flow-link-topic' ), // TODO: What should this be?
			array(),
			array(
				'action' => 'view',
				'workflow' => $changeData['workflow'],
			)
		);
	}

	public static function getComment( array $changeData ) {
		$msg = wfMessage( $changeData['type'] )->text();

		if ( isset( $changeData['topic'] ) ) {
			$msg .= ' ' . wfMessage( 'parentheses' )->rawParams( self::withMaxStrlen( $changeData['topic'], self::MAX_TOPIC_LENGTH ) );
		}

		return $msg;
	}

	protected static function withMaxStrlen( $string, $length, $ellipsis = '...' ) {
		if ( strlen( $string ) <= $length ) {
			return $string;
		}

		return substr( $string, 0, $length - strlen( $ellipsis ) ) . $ellipsis;
	}

}
