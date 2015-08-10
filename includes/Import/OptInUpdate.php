<?php

namespace Flow\Import;

use DeferrableUpdate;
use MWExceptionHandler;
use Title;

class OptInUpdate implements DeferrableUpdate {

	public static $ENABLE = 'enable';
	public static $DISABLE = 'disable';

	/**
	 * @var string
	 */
	protected $action;

	/**
	 * @var Title
	 */
	protected $talkpage;

	/**
	 * @param string $action
	 * @param Title $talkpage
	 */
	public function __construct( $action, Title $talkpage ) {
		$this->action = $action;
		$this->talkpage = $talkpage;
	}

	/**
	 * Enable or disable Flow on a talk page
	 */
	function doUpdate()
	{
		$c = new OptInController();
		try {
			if ( $this->action === self::$ENABLE ) {
				$c->enable( $this->talkpage );
			} elseif ( $this->action === self::$DISABLE ) {
				$c->disable( $this->talkpage );
			} else {
				// warning: unrecognized action: $action
			}
		} catch ( \Exception $e ) {
			MWExceptionHandler::logException( $e );
		}
	}
}

