<?php

namespace Flow;

use Exception;
use Flow\Utils\InconsistentBoardFixer;
use Job;
use MediaWiki\MediaWikiServices;
use Psr\Log\LoggerInterface;

class FixInconsistentFlowWorkflowJob extends Job {

	public function __construct( $title, $params ) {
		parent::__construct( 'fixInconsistentFlowWorkflow', $title, $params );
	}

	public function ignoreDuplicates() {
		return true;
	}

	/**
	 * Execute the job
	 *
	 * @return bool
	 */
	public function run() {
		$boardFixer = new InconsistentBoardFixer(
			Container::get( 'db.factory' ),
			Container::get( 'factory.loader.workflow' ),
			Container::get( 'board_mover' ),
			Container::get( 'storage' ),
			MediaWikiServices::getInstance()->getRevisionStore()
		);
		/** @var LoggerInterface $logger */
		$logger = Container::get( 'default_logger' );
		try {
			$result = $boardFixer->fix(
				$this->title->getNamespace(),
				$this->title->getDBkey(),
				$this->title->getLatestRevID()
			);
			foreach ( $result['output'] as $output ) {
				$logger->info( $output );
			}
		}
		catch ( Exception $exception ) {
			$logger->error( $exception->getMessage() );
			return false;
		}
		return true;
	}
}
