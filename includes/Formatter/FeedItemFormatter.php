<?php

namespace Flow\Formatter;

use FeedItem;
use Flow\Exception\FlowException;
use Flow\Model\PostRevision;
use IContextSource;

class FeedItemFormatter extends AbstractFormatter {
	protected function getHistoryType() {
		return 'feeditem';
	}

	/**
	 * @param FormatterRow $row With properties workflow, revision, previous_revision
	 * @param IContextSource $ctx
	 * @return FeedItem|false The requested format, or false on failure
	 */
	public function format( FormatterRow $row, IContextSource $ctx ) {
		try {
			if ( !$this->permissions->isAllowed( $row->revision, 'history' ) ) {
				return false;
			}
			if ( $row->revision instanceof PostRevision &&
				!$this->permissions->isAllowed( $row->rootPost, 'history' ) ) {
				return false;
			}

			return $this->createFeedItem( $row, $ctx );
		} catch ( FlowException $e ) {
			\MWExceptionHandler::logException( $e );
			return false;
		}
	}

	protected function createFeedItem( FormatterRow $row, IContextSource $ctx ) {
		$this->serializer->setIncludeHistoryProperties( true );
		$data = $this->serializer->formatApi( $row, $ctx );
		if ( !$data ) {
			throw new FlowException( 'Could not format data for row ' . $row->revision->getRevisionId()->getAlphadecimal() );
		}

		$preferredLinks = array(
			'header-revision',
			'post-revision', 'post',
			'topic-revision', 'topic',
			'board'
		);
		$url = '';
		foreach ( $preferredLinks as $link ) {
			if ( isset( $data['links'][$link] ) ) {
				$url = $data['links'][$link]->getFullURL();
				break;
			}
		}
		// If we didn't choose anything just take the first.
		// @todo perhaps just a convention that the first link
		// is always the most specific, we use a similar pattern
		// to above in TemplateHelper::historyTimestamp too.
		if ( $url === '' && $data['links'] ) {
			$link = reset( array_keys( $data['links'] ) );
			$url = $data['links'][$link]->getFullURL();
		}

		return new FeedItem(
			$row->workflow->getArticleTitle()->getPrefixedText(),
			$this->formatDescription( $data, $ctx ),
			$url,
			$data['timestamp'],
			$data['author']['name'],
			$row->workflow->getOwnerTitle()->getFullURL()
		);
	}
}
