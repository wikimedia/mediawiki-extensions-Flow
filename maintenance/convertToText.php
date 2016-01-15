<?php

use Flow\Model\AbstractRevision;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

class ConvertToText extends Maintenance {
	/**
	 * @var Title
	 */
	protected $pageTitle;

	public function __construct() {
		parent::__construct();
		$this->mDescription = "Converts a specific Flow page to text";

		$this->addArg( 'page', 'The page to convert', true /*required*/ );
	}

	public function execute() {
		$pageName = $this->getArg( 0 );
		$this->pageTitle = Title::newFromText( $pageName );

		if ( !$this->pageTitle ) {
			$this->error( 'Invalid page title', true );
		}

		$headerContent = '';

		$headerData = $this->flowApi(
			$this->pageTitle,
			'view-header',
			array( 'vhformat' => 'wikitext' ),
			'header'
		);

		$headerRevision = $headerData['header']['revision'];
		if ( isset( $headerRevision['content'] ) ) {
			$headerContent = $headerRevision['content']['content'];
		}

		$continue = true;
		$pagerParams = array( 'vtllimit' => 1 );
		$topics = array();
		while( $continue ) {
			$continue = false;
			$flowData = $this->flowApi(
				$this->pageTitle,
				'view-topiclist',
				$pagerParams + array( 'vtlformat' => 'wikitext' ),
				'topiclist'
			);

			$topicListBlock = $flowData['topiclist'];

			foreach( $topicListBlock['roots'] as $rootPostId ) {
				$revisionId = reset( $topicListBlock['posts'][$rootPostId] );
				$revision = $topicListBlock['revisions'][$revisionId];

				$topics[] = $this->processTopic( $topicListBlock, $revision );
			}

			$paginationLinks = $topicListBlock['links']['pagination'];
			if ( isset( $paginationLinks['fwd'] ) ) {
				list( $junk, $query ) = explode( '?', $paginationLinks['fwd']['url'] );
				$queryParams = wfCGIToArray( $query );

				$pagerParams = array(
					'vtloffset' => $queryParams['topiclist_offset'],
					'vtloffset-dir' => 'fwd',
					'vtloffset-limit' => '1',
				);
				$continue = true;
			}
		}

		print $headerContent . "\n\n" . implode( "\n", array_reverse( $topics ) );
	}

	/**
	 * @param Title $title
	 * @param string $submodule
	 * @param array $request
	 * @param bool $requiredBlock
	 * @return array
	 * @throws MWException
	 */
	protected function flowApi( Title $title, $submodule, array $request, $requiredBlock = false ) {
		$request = new FauxRequest( $request + array(
			'action' => 'flow',
			'submodule' => $submodule,
			'page' => $title->getPrefixedText(),
		) );

		$api = new ApiMain( $request );
		$api->execute();

		$flowData = $api->getResult()->getResultData( array( 'flow', $submodule, 'result' ) );
		if ( $flowData === null ) {
			throw new MWException( "API response has no Flow data" );
		}
		$flowData = ApiResult::stripMetadata( $flowData );

		if( $requiredBlock !== false && ! isset( $flowData[$requiredBlock] ) ) {
			throw new MWException( "No $requiredBlock block in API response" );
		}

		return $flowData;
	}

	protected function processTopic( array $context, array $revision ) {
		$topicOutput = '==' . $revision['content']['content'] . '==' . "\n";
		$summaryOutput = isset( $revision['summary'] ) ? $this->processSummary( $context, $revision['summary'] ) : '';
		$postsOutput = $this->processPostCollection( $context, $revision['replies'] );
		$resolved = isset( $revision['moderateState'] ) && $revision['moderateState'] === AbstractRevision::MODERATED_LOCKED;

		// check if "resolved" templates exist
		static $archiveTemplates = null;
		static $hatnoteTemplate = null;
		if ( $archiveTemplates === null ) {
			$archiveTemplates = Title::newFromDBkey( 'Template:Archive_top' )->exists() &&
				Title::newFromDBkey( 'Template:Archive_bottom' )->exists();
		}
		if ( $hatnoteTemplate === null ) {
			$hatnoteTemplate = Title::newFromDBkey( 'Template:Hatnote' )->exists();
		}

		if ( $archiveTemplates && $resolved ) {
			return '{{Archive top|result=' . $summaryOutput . "|status=resolved}}\n\n" .
				$topicOutput . $postsOutput . "{{Archive bottom}}\n\n";
		} elseif ( $hatnoteTemplate && $summaryOutput ) {
			return $topicOutput . '{{Hatnote|' . $summaryOutput . "}}\n\n" . $postsOutput;
		} else {
			// italicize summary, if there is any, to set it apart from posts
			$summaryOutput = $summaryOutput ? "''" . $summaryOutput . "''" : '';
			return $topicOutput . $summaryOutput . $postsOutput;
		}
	}

	protected function processSummary( array $context, array $summary ) {
		$user = User::newFromName( $summary['revision']['author']['name'], false );

		return trim( $summary['revision']['content']['content'] ) . ' ' .
			$this->getSignature( $user, $summary['revision']['timestamp'] );
	}

	protected function processPostCollection( array $context, array $collection, $indentLevel = 0 ) {
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

			$thisPost = $indent . trim( $revision['content']['content'] ) . ' ' .
				$this->getSignature( $user, $revision['timestamp'] ) . "\n";

			if ( $indentLevel > 0 ) {
				$thisPost = preg_replace( "/\n+/", "\n", $thisPost );
			}
			$output .= str_replace( "\n", "\n$indent", trim( $thisPost ) ) . "\n";

			if ( isset( $revision['replies'] ) ) {
				$output .= $this->processPostCollection( $context, $revision['replies'], $indentLevel + 1 );
			}

			if ( $indentLevel == 0 ) {
				$output .= "\n";
			}
		}

		return $output;
	}

	protected function getSignature( $user, $timestamp ) {
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

$maintClass = "ConvertToText";
require_once( RUN_MAINTENANCE_IF_MAIN );
