<?php

namespace Flow\Formatter;

use Flow\Model\Anchor;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Parsoid\Utils;
use ChangesList;
use IContextSource;

class RecentChanges extends AbstractFormatter {
	protected function getHistoryType() {
		return 'recentchanges';
	}

	/**
	 * @param RecentChangesRow $row
	 * @param IContextSource $ctx
	 * @param bool $linkOnly
	 * @return string|bool Output line, or false on failure
	 */
	public function format( RecentChangesRow $row, IContextSource $ctx, $linkOnly = false ) {
		if ( !$this->permissions->isAllowed( $row->revision, 'recentchanges' ) ) {
			return false;
		}
		if ( $row->revision instanceof PostRevision &&
			!$this->permissions->isAllowed( $row->rootPost, 'recentchanges' ) ) {
			return false;
		}

		$this->serializer->setIncludeHistoryProperties( true );
		$data = $this->serializer->formatApi( $row, $ctx );
		// @todo where should this go?
		$data['size'] = array(
			'old' => $row->recentChange->getAttribute( 'rc_old_len' ),
			'new' => $row->recentChange->getAttribute( 'rc_new_len' ),
		);

		if ( $linkOnly ) {
			return $this->getTitleLink( $data, $row, $ctx );
		}

		// The ' . . ' text between elements
		$separator = $this->changeSeparator();

		$links = array();
		$links[] = $this->getDiffAnchor( $data['links'], $ctx );
		$links[] = $this->getHistAnchor( $data['links'], $ctx );

		$description = $this->formatDescription( $data, $ctx );

		return $this->formatAnchorsAsPipeList( $links, $ctx ) .
			' ' .
			$this->getTitleLink( $data, $row, $ctx ) .
			$ctx->msg( 'semicolon-separator' )->escaped() .
			' ' .
			$this->formatTimestamp( $data, 'time' ) .
			$separator .
			ChangesList::showCharacterDifference(
			  $data['size']['old'],
			  $data['size']['new'],
			  $ctx
			) .
			( Utils::htmlToPlaintext( $description ) ? $separator . $description : '' );
	}

	/**
	 * This overrides the default title link to include highlights for the posts
	 * that have not yet been seen.
	 *
	 * @param array $data
	 * @param FormatterRow $row
	 * @param IContextSource $ctx
	 * @return string
	 */
	protected function getTitleLink( array $data, FormatterRow $row, IContextSource $ctx ) {
		if ( !$row instanceof RecentChangesRow ) {
			// actually, this should be typehint, but can't because this needs
			// to match the parent's more generic typehint
			return parent::getTitleLink( $data, $row, $ctx );
		}

		$watched = $row->recentChange->watched;
		if ( is_bool( $watched ) ) {
			// RC & watchlist share most code; the latter is unaware of when
			// something was watched though, so we'll ignore that here
			return parent::getTitleLink( $data, $row, $ctx );
		}

		if ( $watched === null ) {
			// there is no data for unread posts - they've all been seen
			return parent::getTitleLink( $data, $row, $ctx );
		}

		// filter down the changeData array to only the first unread post
		$unread = $this->firstUnreadPost( $row->changeData, $watched );
		if ( !$unread ) {
			// there are no unread posts
			return parent::getTitleLink( $data, $row, $ctx );
		}

		// get data from existing link to topic so we can build a new anchor
		$topic = $data['links']['topic'];
		$title = $topic->resolveTitle();
		$topic = $topic->toArray();

		// figure out if the entry is a post or topic, for highlight fragment
		$type = $unread['workflow'] == $unread['post'] ? 'topic' : 'post';

		// finally, build that new link already!
		$data['links']['topic'] = new Anchor(
			$topic['title'],
			$title,
			array( 'fromnotif' => '1' ),
			'#flow-' . $type . '-' . $unread['post']
		);

		// now pass it on to parent with the new, updated, link ;)
		return parent::getTitleLink( $data, $row, $ctx );
	}

	/**
	 * This method will accept an array of multiple $changeData and return only
	 * the oldest one, that is still more recent that $watched (or null, if
	 * there is no such entry)
	 *
	 * @param array $changeData Array of multiple changeData stored for RC
	 * @param string $watched Timestamp in any format accepted by wfTimestamp
	 * @return array|null
	 */
	protected function firstUnreadPost( $changeData, $watched ) {
		$watched = wfTimestamp( TS_MW, $watched );

		$filterUnread = function ( $carry, $changeData ) use ( $watched ) {
			// we can only highlight posts, so ignore all others
			if ( !isset( $changeData['post'] ) ) {
				return $carry;
			}

			// ignore posts older than last watch time
			$postUuid = UUID::create( $changeData['post'] );
			$postTimestamp = $postUuid->getTimestamp();
			if ( $postTimestamp <= $watched ) {
				return $carry;
			}

			// if this is the first unread we encounter, save that one
			if ( $carry === null ) {
				return $changeData;
			}

			// we already have an unread, compare to find oldest
			$currentUuid = UUID::create( $carry['post'] );
			$currentTimestamp = $currentUuid->getTimestamp();
			if ( $currentTimestamp <= $postTimestamp ) {
				// already stored timestamp is still the oldest
				return $carry;
			}

			// new post is the oldest
			return $changeData;
		};

		return array_reduce( $changeData, $filterUnread );
	}
}
