<?php

namespace Flow\Import;

use DeferrableUpdate;
use MWExceptionHandler;
use Title;

class OptInUpdate implements DeferrableUpdate {

	public static $ENABLE = 'enable';
	public static $DISABLE = 'disable';

	protected $action;
	protected $talkpage;

	public function __construct( $action, Title $talkpage ) {
		$this->action = $action;
		$this->talkpage = $talkpage;
	}

	/**
	 * Perform the actual work
	 */
	function doUpdate()
	{
		$c = new OptInController();
		try {
			if ( $this->action === self::$ENABLE ) {
				$c->enable( $this->talkpage );
			} elseif ( $this->action === self::$DISABLE ) {
				$c->disable( $this->talkpage );
			}
		} catch ( \Exception $e ) {
			MWExceptionHandler::logException( $e );
		}
	}
}

