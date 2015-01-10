<?php

use Flow\Parsoid\Utils;

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

		if ( ! $this->pageTitle ) {
			$this->error( 'Invalid page title', true );
		}

		$continue = true;
		$pagerParams = array( 'vtllimit' => 1 );
		$topics = array();
		$headerContent = '';

		$headerData = $this->flowApi( $this->pageTitle, 'view-header', array( 'vhcontentFormat' => 'wikitext' ), 'header' );

		$headerRevision = $headerData['header']['revision'];
		if ( isset( $headerRevision['content'] ) ) {
			$headerContent = $headerRevision['content'];
		}

		while( $continue ) {
			$continue = false;
			$flowData = $this->flowApi( $this->pageTitle, 'view-topiclist', $pagerParams, 'topiclist' );

			$topicListBlock = $flowData['topiclist'];

			foreach( $topicListBlock['roots'] as $rootPostId ) {
				$revisionId = reset( $topicListBlock['posts'][$rootPostId] );
				$revision = $topicListBlock['revisions'][$revisionId];

				$topicOutput = '==' . $revision['content']['content'] . '==' . "\n";
				$topicOutput .= $this->processPostCollection( $topicListBlock, $revision['replies'] );

				$topics[] = $topicOutput;
			}

			$paginationLinks = $topicListBlock['links']['pagination'];
			if ( isset( $paginationLinks['fwd'] ) ) {
				list( $junk, $query ) = explode( '?', $paginationLinks['fwd']['url'] );
				$queryParams = wfCGIToArray( $query );

				$pagerParams = array(
					'vtloffset-id' => $queryParams['topiclist_offset-id'],
					'vtloffset-dir' => 'fwd',
					'vtloffset-limit' => '1',
				);
				$continue = true;
			}
		}

		print $headerContent . implode( "\n", array_reverse( $topics ) );
	}

	/**
	 * @param Title $title
	 * @param string $submodule
	 * @param array $request
	 * @param bool $requiredBlock
	 * @return array
	 * @throws Exception
	 */
	public function flowApi( Title $title, $submodule, array $request, $requiredBlock = false ) {
		$request = new FauxRequest( $request + array(
			'action' => 'flow',
			'submodule' => $submodule,
			'page' => $title->getPrefixedText(),
		) );

		$api = new ApiMain( $request );
		$api->execute();

		$apiResponse = $api->getResult()->getData();

		if ( ! isset( $apiResponse['flow'] ) ) {
			throw new Exception( "API response has no Flow data" );
		}

		$flowData = $apiResponse['flow'][$submodule]['result'];

		if( $requiredBlock !== false && ! isset( $flowData[$requiredBlock] ) ) {
			throw new Exception( "No $requiredBlock block in API response" );
		}

		return $flowData;
	}

	public function processPostCollection( array $context, array $collection, $indentLevel = 0 ) {
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
			$postId = Flow\Model\UUID::create( $postId );

			$content = $revision['content']['content'];
			$contentFormat = $revision['content']['format'];

			if ( $contentFormat !== 'wikitext' ) {
				$content = Utils::convert( $contentFormat, 'wikitext', $content, $this->pageTitle );
			}

			$thisPost = $indent . trim( $content ) . ' ' .
				$this->getSignature( $user, $postId->getTimestamp() ) . "\n";

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

$maintClass = "ConvertToText";
require_once( RUN_MAINTENANCE_IF_MAIN );
