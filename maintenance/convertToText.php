<?php

namespace Flow\Maintenance;

use Flow\Import\LiquidThreadsApi\ApiBackend;
use Flow\Import\LiquidThreadsApi\LocalApiBackend;
use Flow\Import\LiquidThreadsApi\RemoteApiBackend;
use Flow\Model\AbstractRevision;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\Utils\MWTimestamp;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

class ConvertToText extends Maintenance {
	/**
	 * @var Title
	 */
	private $pageTitle;

	/**
	 * @var ApiBackend
	 */
	private $api;

	/** @inheritDoc */
	public function __construct() {
		parent::__construct();
		$this->addDescription( "Converts a specific Flow page to text" );

		$this->addOption( 'page', 'The page to convert', true, true );
		$this->addOption( 'remoteapi', 'URL to api.php (leave unset for local wiki)', false, true );

		$this->requireExtension( 'Flow' );
	}

	/** @inheritDoc */
	public function execute() {
		$pageName = $this->getOption( 'page' );
		$this->pageTitle = Title::newFromText( $pageName );

		if ( !$this->pageTitle ) {
			$this->fatalError( 'Invalid page title' );
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
				$pagerParams + [ 'vtlformat' => 'wikitext', 'vtlsortby' => 'newest' ]
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
					[ , $query ] = explode( '?', $paginationLinks['fwd']['url'] );
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
	 */
	private function flowApi( Title $title, $submodule, array $request ) {
		$result = $this->api->apiCall( $request + [
			'action' => 'flow',
			'submodule' => $submodule,
			'page' => $title->getPrefixedText(),
		] );

		if ( isset( $result['error'] ) ) {
			$this->fatalError( $result['error']['info'] );
		}
		return $result['flow'][$submodule]['result'];
	}

	/**
	 * @param array $context
	 * @param array $revision
	 * @return string
	 */
	private function processTopic( array $context, array $revision ) {
		$topicOutput = $this->processTopicTitle( $revision );
		$summaryOutput = isset( $revision['summary'] ) ? $this->processSummary( $revision['summary'] ) : '';
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

	/**
	 * @param int $id
	 * @param string $name
	 * @return User
	 */
	private function loadUser( $id, $name ) {
		return User::newFromRow( (object)[ 'user_name' => $name, 'user_id' => $id ] );
	}

	/**
	 * @param array $summary
	 * @return string
	 */
	private function processSummary( array $summary ) {
		$topicTitle = Title::newFromText( $summary['revision']['articleTitle'] );
		return $this->processMultiRevisions(
			$this->getAllRevisions( $topicTitle, 'view-topic-summary', 'vts', 'topicsummary' )
		);
	}

	/**
	 * @param array $context
	 * @param int[] $collection
	 * @param int $indentLevel
	 * @return string
	 */
	private function processPostCollection( array $context, array $collection, $indentLevel = 0 ) {
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

	/**
	 * @param array $user
	 * @param string|false $timestamp
	 * @return string
	 */
	private function getSignature( array $user, $timestamp = false ) {
		if ( !$user ) {
			$signature = '[Unknown user]';
			if ( $timestamp ) {
				$signature .= ' ' . $this->formatTimestamp( $timestamp );
			}
			return $signature;
		}

		// create a bogus user for whom username & id is known, so we
		// can generate a correct signature
		$user = $this->loadUser( $user['id'], $user['name'] );

		// nickname & fancysig are user options: unless we're on local wiki,
		// we don't know these & can't load them to generate the signature
		$nickname = $this->getOption( 'remoteapi' ) ? null : false;
		$fancysig = $this->getOption( 'remoteapi' ) ? false : null;

		$parser = $this->getServiceContainer()->getParserFactory()->create();
		// Parser::getUserSig can end calling `getCleanSignatures` on
		// mOptions, which may not be set. Set a dummy options object so it
		// doesn't fail (it'll initialise the requested value from a global
		// anyway)
		$options = ParserOptions::newFromAnon();
		$parser->startExternalParse( $this->pageTitle, $options, Parser::OT_WIKI );
		$signature = $parser->getUserSig( $user, $nickname, $fancysig );
		$signature = $parser->getStripState()->unstripBoth( $signature );
		if ( $timestamp ) {
			$signature .= ' ' . $this->formatTimestamp( $timestamp );
		}
		return $signature;
	}

	/**
	 * @param string $timestamp
	 * @return string
	 */
	private function formatTimestamp( $timestamp ) {
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

		return $this->getServiceContainer()->getContentLanguage()
			->timeanddate( $ts, false, false ) . " ($tzMsg)";
	}

	/**
	 * @param string $pageName
	 * @return bool
	 */
	private function pageExists( $pageName ) {
		static $pages = [];

		if ( !isset( $pages[$pageName] ) ) {
			$result = $this->api->apiCall( [ 'action' => 'query', 'titles' => $pageName ] );
			$pages[$pageName] = !isset( $result['query']['pages'][-1] );
		}

		return $pages[$pageName];
	}

	/**
	 * @param Title $pageTitle
	 * @param string $submodule
	 * @param string $prefix
	 * @param string $responseRoot
	 * @return array[]
	 */
	private function getAllRevisions( Title $pageTitle, $submodule, $prefix, $responseRoot ) {
		$params = [ $prefix . 'format' => 'wikitext' ];
		$headerRevisions = [];

		do {
			$headerData = $this->flowApi( $pageTitle, $submodule, $params );
			if ( !isset( $headerData[$responseRoot]['revision']['revisionId'] ) ) {
				break;
			}

			$headerRevision = $headerData[$responseRoot]['revision'];
			$headerRevisions[] = $headerRevision;

			$revId = $headerRevision['previousRevisionId'];
			$params[$prefix . 'revId'] = $revId;
		} while ( $revId );

		return $headerRevisions;
	}

	/**
	 * @return string
	 */
	private function processHeader() {
		return $this->processMultiRevisions(
			$this->getAllRevisions( $this->pageTitle, 'view-header', 'vh', 'header' ),
			false,
			'flow-edited-by-header'
		);
	}

	/**
	 * @param array[] $allRevisions
	 * @param bool $sigForFirstAuthor
	 * @param string $msg
	 * @param string $glueAfterContent
	 * @param string $glueBeforeAuthors
	 * @return string
	 */
	private function processMultiRevisions(
		array $allRevisions,
		$sigForFirstAuthor = true,
		$msg = 'flow-edited-by',
		$glueAfterContent = '',
		$glueBeforeAuthors = ' '
	) {
		if ( !$allRevisions ) {
			return '';
		}

		$firstRevision = end( $allRevisions );
		$latestRevision = reset( $allRevisions );

		// take the content from the first (most recent) revision
		$content = $latestRevision['content']['content'];
		$firstContributor = $firstRevision['author'];

		// deduplicate authors
		$otherContributors = [];
		foreach ( $allRevisions as $revision ) {
			$name = $revision['author']['name'];
			$otherContributors[$name] = $revision['author'];
		}

		$formattedAuthors = '';
		if ( $sigForFirstAuthor ) {
			$formattedAuthors .= $this->getSignature( $firstContributor, $firstRevision['timestamp'] );
			// remove first contributor from list of previous contributors
			unset( $otherContributors[$firstContributor['name']] );
		}

		if ( $otherContributors &&
			( count( $otherContributors ) > 1 || !isset( $otherContributors[$firstContributor['name']] ) )
		) {
			$signatures = array_map( [ $this, 'getSignature' ], $otherContributors );
			$formattedAuthors .= ( $sigForFirstAuthor ? ' ' : '' ) . '(' .
				wfMessage( $msg )->inContentLanguage()->params(
					$this->getServiceContainer()->getContentLanguage()->commaList( $signatures )
				)->text() . ')';
		}

		return $content . $glueAfterContent . ( $formattedAuthors === '' ? '' : $glueBeforeAuthors . $formattedAuthors );
	}

	/**
	 * @param array $revision
	 * @return array[]
	 */
	private function getAllPostRevisions( array $revision ) {
		$topicTitle = Title::newFromText( $revision['articleTitle'] );
		$response = $this->flowApi( $topicTitle, 'view-post-history', [ 'vphpostId' => $revision['postId'], 'vphformat' => 'wikitext' ] );
		return $response['topic']['revisions'];
	}

	/**
	 * @param array $revision
	 * @return string
	 */
	private function processPost( array $revision ) {
		return $this->processMultiRevisions( $this->getAllPostRevisions( $revision ) );
	}

	/**
	 * @param array $revision
	 * @return string
	 */
	private function processTopicTitle( array $revision ) {
		return '==' . $this->processMultiRevisions(
			$this->getAllPostRevisions( $revision ),
			false,
			'flow-edited-by-topic-title',
			'==',
			"\n\n"
		) . "\n\n";
	}

}

$maintClass = ConvertToText::class;
require_once RUN_MAINTENANCE_IF_MAIN;
