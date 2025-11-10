<?php
/**
 * Maintenance script to apply LQT porting fixes from a JSON file. (T397426)
 * Usage: php implementLqtFixes.php [--dryrun] < fixes.json
 */

use MediaWiki\Content\ContentHandler;
use MediaWiki\Content\TextContent;
use MediaWiki\Title\Title;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

class ImplementLqtFixes extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addOption( 'dryrun', 'Dry run mode (do not perform edits)' );
	}

	public function execute() {
		$dryRun = $this->hasOption( 'dryrun' );

		// Read JSON proposals from stdin
		$json = stream_get_contents( STDIN );
		$proposals = json_decode( $json, true );
		if ( !is_array( $proposals ) ) {
			$this->fatalError( "Invalid JSON on stdin" );
		}

		// Use FlowTalkpageManager user for edits, as in convertLqtPageOnLocalWiki.php
		$occupationController = \MediaWiki\MediaWikiServices::getInstance()->getService( 'FlowTalkpageManager' );
		$user = $occupationController->getTalkpageManager();
		$summary = 'Automated LQT porting fix';

		foreach ( $proposals as $proposal ) {
			$titleText = $proposal['title'];
			$expectedRevision = intval( $proposal['expected_revision'] );
			$content = $proposal['content'];
			$pageId = intval( $proposal['id'] );

			$title = Title::newFromText( $titleText );
			if ( !$title || !$title->exists() ) {
				$this->output( "!!! Skipping: $titleText (page does not exist)\n" );
				continue;
			}

			$wikiPage = \MediaWiki\MediaWikiServices::getInstance()
				->getWikiPageFactory()
				->newFromTitle( $title );
			$dbPageId = $wikiPage->getId();
			if ( $dbPageId != $pageId ) {
				$this->output( "!!! Skipping: $titleText (pageid mismatch: JSON $pageId, DB $dbPageId)\n" );
				continue;
			}
			$currentRevision = $wikiPage->getLatest();
			$contentObj = $wikiPage->getContent();
			if ( !( $contentObj instanceof TextContent ) ) {
				$this->output( "!!! Skipping: $titleText (not text content)\n" );
				continue;
			}
			$currentContent = $contentObj->getText();

			if ( $currentRevision === $expectedRevision ) {
				if ( $dryRun ) {
					$this->output( "+++ Would edit: $titleText\n" );
				} else {
					$contentObj = ContentHandler::makeContent( $content, $title );
					$status = $wikiPage->doUserEditContent(
						$contentObj,
						$user,
						$summary,
						EDIT_FORCE_BOT | EDIT_SUPPRESS_RC,
						$expectedRevision
					);
					if ( $status->isOK() ) {
						$this->output( "+++ Edited: $titleText\n" );
					} else {
						$this->output( "!!! Error editing $titleText: " . $status->getWikiText() . "\n" );
					}
				}
			} else {
				if ( $currentContent === $content ) {
					$this->output( "--- Complete: $titleText\n" );
				} else {
					$this->output(
						"!!! Conflict: $titleText," .
						" expected revision: $expectedRevision," .
						" current revision: $currentRevision\n"
					);
				}
			}
		}
	}
}

$maintClass = ImplementLqtFixes::class;
require_once RUN_MAINTENANCE_IF_MAIN;
