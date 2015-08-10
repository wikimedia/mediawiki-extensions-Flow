<?php

namespace Flow\Import;

use DeferrableUpdate;
use MWExceptionHandler;
use Title;
use User;

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
	 * @var User
	 */
	protected $user;

	/**
	 * @param string $action
	 * @param Title $talkpage
	 * @param User $user
	 */
	public function __construct( $action, Title $talkpage, User $user ) {
		$this->action = $action;
		$this->talkpage = $talkpage;
		$this->user = $user;
	}

	/**
	 * Enable or disable Flow on a talk page
	 */
	function doUpdate() {
		$c = new OptInController();
		try {
			if ( $this->action === self::$ENABLE ) {
				$c->enable( $this->talkpage, $this->user );
			} elseif ( $this->action === self::$DISABLE ) {
				$c->disable( $this->talkpage );
			} else {
				wfLogWarning( 'OptInUpdate: unrecognized action: ' . $this->action );
			}
		} catch ( \Exception $e ) {
			MWExceptionHandler::logException( $e );
		}
	}
}

