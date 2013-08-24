<?php

namespace Flow\RecentChanges;

use Flow\Container;
use ChangesList;
use Linker;
use RecentChange;
use Title;
use User;

class ExternalChangesLines {
	static public function changesLine( ChangesList $cl, RecentChange $rc ) {
		$params = unserialize( $rc->getAttribute( 'rc_params' ) );
		$changeData = $params['flow-workflow-change'];

		if ( !is_array( $changeData ) ) {
			wfDebugLog( __CLASS__, __FUNCTION__ . ': Flow data missing in recent changes.' );
			return false;
		}

		if ( !array_key_exists( 'type', $changeData ) ) {
			wfDebugLog( __CLASS__, __FUNCTION__ . ': Flow change type missing' );
			return false;
		}

		$line = '';
		$title = self::buildTitle( $rc, $changeData );
		$links = self::buildActionLinks( $title, $changeData );
		if ( $links === false ) {
			return;
		}
		if ( $links ) {
			$line .= wfMessage( 'parentheses' )->rawParams(
					$cl->getLanguage()->pipeList( $links )
				)->text()
				. self::changeSeparator();
		}

		$line .= Linker::link( $title )
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
		case 'new-topic':
			$links[] = self::topicLink( $title, $changeData );
			break;

		case 'new-post':
			$links[] = self::postLink( $title, $changeData );
			$links[] = self::topicLink( $title, $changeData );
			break;

		case 'moderate-post':
			// TODO: this only happens when oversighting (updating an existing
			// revision rather than creating a new revision of the content).
			$links[] = self::postHistoryLink( $title, $changeData );
			$links[] = self::topicLink( $title, $changeData );

		case 'edit-topic-title':
			break;

		default:
			wfDebugLog( __CLASS__, __FUNCTION__ . ': Invalid Flow change type.' );
			return false;
		}

		return $links;
	}

	protected static function changeSeparator() {
		return ' <span class="mw-changeslist-separator">. .</span> ';
	}

	protected static function getTimestamp( $cl, $rc ) {
		return wfMessage( 'semicolon-separator' )->text()
			. '<span class="mw-changeslist-date">'
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
				. '</span>';
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

	public static function getComment( array $changeData ) {
		if ( isset( $changeData['comment'] ) ) {
			return wfMessage( $changeData['comment'] );
		} else {
			return wfMessage( 'flow-no-comment' );
		}
	}
}
