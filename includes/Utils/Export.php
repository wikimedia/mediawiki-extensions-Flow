<?php

namespace Flow\Utils;

use APIMain;
use FauxRequest;
use Flow\Model\UUID;
use Flow\Parsoid\Utils;
use MWException;
use MWTimestamp;
use StubObject;
use Title;
use User;

class Export {
	/**
	 * @param Title $title
	 * @return string wikitext
	 */
	public function export( Title $title ) {
		return $this->getHeaderWikitext( $title ) . "\n" . $this->getTopicsWikitext( $title );
	}

	/**
	 * @param Title $title
	 * @return string wikitext
	 */
	public function getHeaderWikitext( Title $title ) {
		$headerData = $this->flowApi(
			$title,
			'view-header',
			array( 'vhcontentFormat' => 'wikitext' ),
			'header'
		);

		if ( !isset( $headerData['header']['revision'] ) ) {
			return '';
		}

		$headerRevision = $headerData['header']['revision'];
		if ( !isset( $headerRevision['content'] ) ) {
			return '';
		}

		return $headerRevision['content']['content'];
	}

	/**
	 * @param Title $title
	 * @return string wikitext
	 */
	public function getTopicsWikitext( $title ) {
		$pagerParams = array( 'vtllimit' => 1 );
		$topics = array();

		do {
			$flowData = $this->flowApi(
				$title,
				'view-topiclist',
				$pagerParams,
				'topiclist'
			);

			$topicListBlock = $flowData['topiclist'];

			foreach( $topicListBlock['roots'] as $rootPostId ) {
				$revisionId = reset( $topicListBlock['posts'][$rootPostId] );
				$revision = $topicListBlock['revisions'][$revisionId];

				$topics[] = "=={$revision['content']['content']}==\n"
					. $this->processPostCollection( $title, $topicListBlock, $revision['replies'] );
			}
		} while( $pagerParams = $this->getNextPageParams( $topicListBlock ) );

		// The topics were received from newest to oldest, flip that aroud so it matches
		// wikitext where the oldest topic is at the top.
		return implode( "\n", array_reverse( $topics ) );
	}

	protected function getNextPageParams( $topicListBlock ) {
		// No forward pagination exists, we have reached the end
		if ( !isset( $topicListBlock['links']['pagination']['fwd']['url'] ) ) {
			return null;
		}

		$query = parse_url( $topicListBlock['links']['pagination']['fwd']['url'] , PHP_URL_QUERY );
		if ( !$query ) {
			throw new FlowException( __METHOD__ . ': Forward pagination has no url' );
			return null;
		}

		parse_str( $query, $queryParams );
		if ( !isset( $queryParams['topiclist_offset-id'] ) ) {
			throw new FlowException( __METHOD__ . ': expected query parameters to include topiclist_offset-id' );
			return null;
		}

		return array(
			'vtloffset-id' => $queryParams['topiclist_offset-id'],
			'vtloffset-dir' => 'fwd',
			'vtloffset-limit' => '1',
		);
	}

	/**
	 * @param Title $title
	 * @param string $submodule
	 * @param array $request
	 * @param bool $requiredBlock
	 * @return array
	 * @throws MWException
	 * @todo its much harder to test with this embedded inside, should add an abstraction
	 *  for calling ApiMain through an instantiated class instead of newing it up.
	 */
	public function flowApi( Title $title, $submodule, array $request, $requiredBlock = false ) {
		$request = new FauxRequest( $request + array(
			'action' => 'flow',
			'submodule' => $submodule,
			'page' => $title->getPrefixedText(),
		) );

		$api = new ApiMain( $request );
		$api->execute();


		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$flowData = $api->getResult()->getResultData( array( 'flow', $submodule, 'result' ) );
			if ( $flowData === null ) {
				throw new MWException( "API response has no Flow data" );
			}
		} else {
			$apiResponse = $api->getResult()->getData();
			if ( ! isset( $apiResponse['flow'] ) ) {
				throw new MWException( "API response has no Flow data" );
			}
			$flowData = $apiResponse['flow'][$submodule]['result'];
		}

		if( $requiredBlock !== false && ! isset( $flowData[$requiredBlock] ) ) {
			throw new MWException( "No $requiredBlock block in API response" );
		}

		return $flowData;
	}

	public function processPostCollection( Title $title, array $context, array $collection, $indentLevel = 0 ) {
		$indent = str_repeat( ':', $indentLevel );
		$output = '';

		foreach( $collection as $postId ) {
			$revisionId = reset( $context['posts'][$postId] );
			$revision = $context['revisions'][$revisionId];

			// Skip moderated posts
			if ( $revision['isModerated'] ) {
				continue;
			}

			$user = User::newFromName( $revision['author']['name'], false );
			$postId = UUID::create( $postId );

			$content = $revision['content']['content'];
			$contentFormat = $revision['content']['format'];

			if ( $contentFormat !== 'wikitext' ) {
				$content = Utils::convert( $contentFormat, 'wikitext', $content, $title );
			}

			$thisPost = $indent . trim( $content ) . ' ' .
				$this->getSignature( $user, $postId->getTimestamp() ) . "\n";

			if ( $indentLevel > 0 ) {
				$thisPost = preg_replace( "/\n+/", "\n", $thisPost );
			}
			$output .= str_replace( "\n", "\n$indent", trim( $thisPost ) ) . "\n";

			if ( isset( $revision['replies'] ) ) {
				$output .= $this->processPostCollection( $title, $context, $revision['replies'], $indentLevel + 1 );
			}

			if ( $indentLevel == 0 ) {
				$output .= "\n";
			}
		}

		return $output;
	}

	public function getSignature( $user, $timestamp ) {
		global $wgContLang, $wgParser;

		// Force unstub
		StubObject::unstub( $wgParser );

		$timestamp = MWTimestamp::getLocalInstance( $timestamp );
		$ts = $timestamp->format( 'YmdHis' );
		$tzMsg = $timestamp->format( 'T' );  # might vary on DST changeover!

		# Allow translation of timezones through wiki. format() can return
		# whatever crap the system uses, localised or not, so we cannot
		# ship premade translations.
		$key = 'timezone-' . strtolower( trim( $tzMsg ) );
		$msg = wfMessage( $key )->inContentLanguage();
		if ( $msg->exists() ) {
			$tzMsg = $msg->text();
		}

		$d = $wgContLang->timeanddate( $ts, false, false ) . " ($tzMsg)";

		if ( $user ) {
			return $wgParser->getUserSig( $user, false, false ) . ' ' . $d;
		} else {
			return "[Unknown user] $d";
		}
	}
}

