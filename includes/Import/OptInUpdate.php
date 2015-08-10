<?php

namespace Flow\Import;

use DeferrableUpdate;
use MWExceptionHandler;
use User;

class OptInUpdate implements DeferrableUpdate {

	public static $ENABLE = 'enable';
	public static $DISABLE = 'disable';

	protected $action;
	protected $user;

	public function __construct( $action, User $user ) {
		$this->action = $action;
		$this->user = $user;
	}

	/**
	 * Perform the actual work
	 */
	function doUpdate()
	{
		$c = new OptInController();
		try {
			if ( $this->action === self::$ENABLE ) {
				$c->enable( $this->user );
			} elseif ( $this->action === self::$DISABLE ) {
				$c->disable( $this->user );
			}
		} catch ( \Exception $e ) {
			MWExceptionHandler::logException( $e );
		}
	}
}

