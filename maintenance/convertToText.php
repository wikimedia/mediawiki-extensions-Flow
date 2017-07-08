<?php

use Flow\Model\AbstractRevision;
use Flow\Import\LiquidThreadsApi\ApiBackend;
use Flow\Import\LiquidThreadsApi\RemoteApiBackend;
use Flow\Import\LiquidThreadsApi\LocalApiBackend;

require_once getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: __DIR__ . '/../../../maintenance/Maintenance.php';

class ConvertToText extends Maintenance {
	/**
	 * @var Title
	 */
	protected $pageTitle;

	/**
	 * @var ApiBackend
	 */
	protected $api;

	public function __construct() {
		parent::__construct();
		$this->mDescription = "Converts a specific Flow page to text";

		$this->addOption( 'page', 'The page to convert', true /*required*/ );
		$this->addOption( 'remoteapi', 'The api of the wiki to convert the page from (or nothing, for local wiki)', false /*required*/ );

		$this->requireExtension( 'Flow' );
	}

	public function execute() {
		$pageName = $this->getOption( 'page' );
		$this->pageTitle = Title::newFromText( $pageName );

		if ( !$this->pageTitle ) {
			$this->error( 'Invalid page title', true );
		}

		if ( $this->getOption( 'remoteapi' ) ) {
			$this->api = new RemoteApiBackend( $this->getOption( 'remoteapi' ) );
		} else {
			$this->api = new LocalApiBackend();
		}

		$headerContent = $this->processHeader();

		$continue = true;
		$pagerParams = [ 'vtllimit' => 1 ];
		$topics = [];
		while ( $continue ) {
			$continue = false;
			$flowData = $this->flowApi(
				$this->pageTitle,
				'view-topiclist',
				$pagerParams + [ 'vtlformat' => 'wikitext', 'vtlsortby' => 'newest' ],
				'topiclist'
			);

			$topicListBlock = $flowData['topiclist'];

			foreach ( $topicListBlock['roots'] as $rootPostId ) {
				$revisionId = reset( $topicListBlock['posts'][$rootPostId] );
				$revision = $topicListBlock['revisions'][$revisionId];

				$topics[] = $this->processTopic( $topicListBlock, $revision );
			}

			if ( isset( $topicListBlock['links']['pagination'] ) ) {
				$paginationLinks = $topicListBlock['links']['pagination'];
				if ( isset( $paginationLinks['fwd'] ) ) {
					list( $junk, $query ) = explode( '?', $paginationLinks['fwd']['url'] );
					$queryParams = wfCgiToArray( $query );

					$pagerParams = [
						'vtloffset-id' => $queryParams['topiclist_offset-id'],
						'vtloffset-dir' => 'fwd',
						'vtloffset-limit' => '1',
					];
					$continue = true;
				}
			}
		}

		print $headerContent . "\n\n" . implode( "\n", array_reverse( $topics ) );
	}

	/**
	 * @param Title $title
	 * @param string $submodule
	 * @param array $request
	 * @return array
	 * @throws MWException
	 */
	protected function flowApi( Title $title, $submodule, array $request ) {
		$result = $this->api->apiCall( $request + [
			'action' => 'flow',
			'submodule' => $submodule,
			'page' => $title->getPrefixedText(),
		] );

		return $result['flow'][$submodule]['result'];
	}

	protected function processTopic( array $context, array $revision ) {
		$topicOutput = $this->processTopicTitle( $revision );
		$summaryOutput = isset( $revision['summary'] ) ? $this->processSummary( $context, $revision['summary'] ) : '';
		$postsOutput = $this->processPostCollection( $context, $revision['replies'] ) . "\n\n";
		$resolved = isset( $revision['moderateState'] ) && $revision['moderateState'] === AbstractRevision::MODERATED_LOCKED;

		// check if "resolved" templates exist
		$archiveTemplates = $this->pageExists( 'Template:Archive_top' ) && $this->pageExists( 'Template:Archive_bottom' );
		$hatnoteTemplate = $this->pageExists( 'Template:Hatnote' );

		if ( $archiveTemplates && $resolved ) {
			return '{{Archive top|result=' . $summaryOutput . "|status=resolved}}\n\n" .
				$topicOutput . $postsOutput . "{{Archive bottom}}\n\n";
		} elseif ( $hatnoteTemplate && $summaryOutput ) {
			return $topicOutput . '{{Hatnote|' . $summaryOutput . "}}\n\n" . $postsOutput;
		} else {
			// italicize summary, if there is any, to set it apart from posts
			$summaryOutput = $summaryOutput ? "''" . $summaryOutput . "''\n\n" : '';
			return $topicOutput . $summaryOutput . $postsOutput;
		}
	}

	protected function loadUser( $id, $name ) {
		$row = new stdClass;
		$row->user_name = $name;
		$row->user_id = $id;

		return User::newFromRow( $row );
	}

	protected function processSummary( array $context, array $summary ) {
		$topicTitle = Title::newFromText( $summary[ 'revision' ][ 'articleTitle' ] );
		return $this->processMultiRevisions(
			$this->getAllRevisions( $topicTitle, 'view-topic-summary', 'vts', 'topicsummary' )
		);
	}

	protected function processPostCollection( array $context, array $collection, $indentLevel = 0 ) {
		$indent = str_repeat( ':', $indentLevel );
		$output = '';

		foreach ( $collection as $postId ) {
			$revisionId = reset( $context['posts'][$postId] );
			$revision = $context['revisions'][$revisionId];

			// Skip moderated posts
			if ( $revision['isModerated'] ) {
				continue;
			}

			$thisPost = $indent . $this->processPost( $revision );

			if ( $indentLevel > 0 ) {
				$thisPost = preg_replace( "/\n+/", "\n$indent", $thisPost );
			}
			$output .= $thisPost . "\n";

			if ( isset( $revision['replies'] ) ) {
				$output .= $this->processPostCollection( $context, $revision['replies'], $indentLevel + 1 );
			}

			if ( $indentLevel == 0 ) {
				$output .= "\n";
			}
		}

		return $output;
	}

	protected function getSignature( array $user, $timestamp = false ) {
		global $wgParser;

		// Force unstub
		StubObject::unstub( $wgParser );

		if ( $user ) {
			// create a bogus user for whom username & id is known, so we
			// can generate a correct signature
			$user = $this->loadUser( $user['id'], $user['name'] );

			// nickname & fancysig are user options: unless we're on local wiki,
			// we don't know these & can't load them to generate the signature
			$nickname = $this->getOption( 'remoteapi' ) ? null : false;
			$fancysig = $this->getOption( 'remoteapi' ) ? false : null;

			// Parser::getUserSig can end calling `getCleanSignatures` on
			// mOptions, which may not be set. Set a dummy options object so it
			// doesn't fail (it'll initialise the requested value from a global
			// anyway)
			$options = new ParserOptions();
			$old = $wgParser->Options( $options );
			$wgParser->startExternalParse( $this->pageTitle, $options, Parser::OT_WIKI );
			$signature = $wgParser->getUserSig( $user, $nickname, $fancysig );
			$signature = $wgParser->mStripState->unstripBoth( $signature );
			if ( $timestamp ) {
				$signature .= ' ' . $this->formatTimestamp( $timestamp );
			}
			$wgParser->Options( $old );
			return $signature;
		} else {
			return "[Unknown user]" . $timestamp ? ' ' . $this->formatTimestamp( $timestamp ) : '';
		}
	}

	private function formatTimestamp( $timestamp ) {
		global $wgContLang;

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

		return $wgContLang->timeanddate( $ts, false, false ) . " ($tzMsg)";
	}

	protected function pageExists( $pageName ) {
		static $pages = [];
		if ( !isset( $pages[$pageName] ) ) {
			$result = $this->api->apiCall( [ 'action' => 'query', 'titles' => $pageName ] );
			$pages[$pageName] = !isset( $result['query']['pages'][-1] );
		}

		return $pages[$pageName];
	}

	private function getAllRevisions( Title $pageTitle, $submodule, $prefix, $responseRoot, $params = [] ) {
		$headerRevisions = [];
		$revId = false;
		do {
			$params[ $prefix . 'format' ] = 'wikitext';
			if ( $revId ) {
				$params[ $prefix .  'revId' ] = $revId;
			}
			$headerData = $this->flowApi(
				$pageTitle,
				$submodule,
				$params
			);
			if ( isset( $headerData[ $responseRoot ][ 'revision' ][ 'revisionId' ] ) ) {
				$headerRevisions[] = $headerRevision = $headerData[ $responseRoot ][ 'revision' ];
				$revId = $headerRevision[ 'previousRevisionId' ];
			} else {
				$revId = false;
			}
		} while ( $revId );
		return $headerRevisions;
	}

	private function processHeader() {
		return $this->processMultiRevisions(
			$this->getAllRevisions( $this->pageTitle, 'view-header', 'vh', 'header' ),
			false,
			'flow-edited-by-header'
		);
	}
	private function processMultiRevisions(
		$allRevisions, $sigForFirstAuthor = true, $msg = 'flow-edited-by',
		$glueAfterContent = '', $glueBeforeAuthors = ' '
	) {
		global $wgContLang;
		if ( count( $allRevisions ) ) {
			$firstRevision = end( $allRevisions );
			$latestRevision = reset( $allRevisions );

			// take the content from the first (most recent) revision
			$content = $latestRevision['content']['content'];
			$firstContributor = $firstRevision['author'];

			// deduplicate authors
			$otherContributors = [];
			foreach ( $allRevisions as $revision ) {
				$name = $revision['author']['name'];
				$otherContributors[ $name ] = $revision['author'];
			}

			$formattedAuthors = '';
			if ( $sigForFirstAuthor ) {
				$formattedAuthors .= $this->getSignature( $firstContributor, $firstRevision['timestamp'] );
				// remove first contributor from list of previous contributors
				if ( isset( $otherContributors[ $firstContributor['name'] ] ) ) {
					unset( $otherContributors[ $firstContributor['name'] ] );
				}
			}

			if (
				count( $otherContributors ) > 0 &&
				( count( $otherContributors ) > 1 || !isset( $otherContributors[ $firstContributor['name'] ] ) )
			) {
				$signatures = array_map( [ $this, 'getSignature' ], $otherContributors );
				$formattedAuthors .= ( $sigForFirstAuthor ? ' ' : '' ) . '(' .
					wfMessage( $msg )->inContentLanguage()->params(
						$wgContLang->commaList( $signatures )
					)->text() . ')';
			}

			return $content . $glueAfterContent .  ( $formattedAuthors === '' ? '' : $glueBeforeAuthors . $formattedAuthors );
		}
		return '';
	}

	private function getAllPostRevisions( $revision ) {
		$topicTitle = Title::newFromText( $revision[ 'articleTitle' ] );
		$response = $this->flowApi( $topicTitle, 'view-post-history', [ 'vphpostId' => $revision['postId'], 'vphformat' => 'wikitext' ] );
		return $response['topic']['revisions'];
	}

	private function processPost( $revision ) {
		return $this->processMultiRevisions( $this->getAllPostRevisions( $revision ) );
	}

	private function processTopicTitle( $revision ) {
		return '==' . $this->processMultiRevisions(
			$this->getAllPostRevisions( $revision ),
			false,
			'flow-edited-by-topic-title',
			'==',
			"\n\n"
		) . "\n\n";
	}

}

$maintClass = "ConvertToText";
require_once RUN_MAINTENANCE_IF_MAIN;
