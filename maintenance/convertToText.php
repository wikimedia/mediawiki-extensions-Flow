<?php

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

class ConvertToText extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Converts a specific Flow page to text";

		$this->addArg( 'page', 'The page to convert', true /*required*/ );
	}

	public function execute() {
		$pageName = $this->getArg( 0 );
		$pageTitle = Title::newFromText( $pageName );

		if ( ! $pageTitle ) {
			$this->error( 'Invalid page title', true );
		}

		$continue = true;
		$pagerParams = array();
		$topics = array();
		$headerContent = '';

		while( $continue ) {
			$continue = false;
			$request = new FauxRequest( array(
				'action' => 'query',
				'list' => 'flow',
				'flowpage' => $pageTitle->getPrefixedText(),
				'flowparams' => FormatJson::encode( array(
					'topiclist' => $pagerParams + array(
						'limit' => 1,
						'contentFormat' => 'wikitext',
					),
					'header' => array(
						'contentFormat' => 'wikitext',
					),
				) ),
			) );

			$api = new ApiMain( $request );
			$api->execute();

			$apiResponse = $api->getResult()->getData();

			if ( ! isset( $apiResponse['query']['flow'] ) ) {
				throw new MWException( "API response has no Flow data" );
			}

			$flowData = $apiResponse['query']['flow'];

			if( $flowData["element"] !== "block" ) {
				throw new MWException( "No block data in API response" );
			}

			$topicListBlock = false;
			$headerBlock = false;

			foreach( $flowData as $key => $block ) {
				if ( is_numeric( $key ) && $block['block-name'] === 'header' ) {
					$headerBlock = $block;
				} elseif ( is_numeric( $key ) && $block['block-name'] === 'topiclist' ) {
					$topicListBlock = $block;
				}
			}

			if( $headerBlock === false ) {
				throw new MWException( "No header block in API response" );
			}

			$header = $headerBlock[0];
			if ( ! isset( $header['missing'] ) ) {
				$headerContent = trim( $header['*'] ) . "\n\n";
			} else {
				$headerContent = '';
			}

			if( $topicListBlock === false ) {
				throw new MWException( "No topic list block in API response" );
			}
			foreach( $topicListBlock as $key => $topic ) {
				if ( ! is_numeric( $key ) ) {
					continue;
				}

				if ( ! isset( $topic["topic-id"] ) ) {
					throw new MWException( "Could not find topic ID in topic block" );
				}

				$topicOutput = '==' . $topic['title'] . '==' . "\n";
				$topicOutput .= $this->processPostCollection( $topic );

				$topics[] = $topicOutput;
			}

			if ( isset( $topicListBlock['paging']['fwd'] ) ) {
				$continue = true;
				$pagerParams = $topicListBlock['paging']['fwd'];

				// Silly inconsistency
				$pagerParams['offset-id'] = $pagerParams['offset'];
				unset( $pagerParams['offset'] );
			} else {
				$pagerParams = array();
			}
		}

		print $headerContent . implode( "\n", array_reverse( $topics ) );
	}

	public function processPostCollection( $collection, $indentLevel = 0 ) {
		if ( $collection["element"] !== "post" ) {
			throw new MWException( "Attempt to process post collection on non-list-of-posts" );
		}

		$indent = str_repeat( ':', $indentLevel );
		$output = '';

		foreach( $collection as $key => $post ) {
			if ( ! is_numeric( $key ) ) {
				continue;
			}

			// Skip moderated posts
			if ( isset( $post['moderated'] ) ) {
				continue;
			}

			$user = User::newFromName( $post['user'], false );
			$postId = Flow\Model\UUID::create( $post['post-id'] );

			$thisPost = $indent . trim( $post['content']['*'] ) . ' ' .
				$this->getSignature( $user, $postId->getTimestamp() ) . "\n";

			if ( $indentLevel > 0 ) {
				$thisPost = preg_replace( "/\n+/", "\n", $thisPost );
			}
			$output .= str_replace( "\n", "\n$indent", trim( $thisPost ) ) . "\n";

			if ( isset( $post['replies'] ) ) {
				$output .= $this->processPostCollection( $post['replies'], $indentLevel + 1 );
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
		$wgParser->getRandomString();

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
			return $wgParser->getUserSig( $user ) . ' ' . $d;
		} else {
			return "[Unknown user] $d";
		}
	}
}

$maintClass = "ConvertToText";
require_once( RUN_MAINTENANCE_IF_MAIN );
