<?php

namespace Flow;

use Flow\Content\BoardContent;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidInputException;
use Flow\Model\Workflow;
use Article;
use Revision;
use Title;
use User;

// I got the feeling NinetyNinePercentController was a bit much.
interface OccupationController {
	/**
	 * @param Title $title
	 * @return bool
	 */
	public function isTalkpageOccupied( $title, $checkContentModel = true );

	/**
	 * @param Article $title
	 * @param Workflow $workflow
	 * @return Revision|null
	 */
	public function ensureFlowRevision( Article $title, Workflow $workflow );

	/**
	 * @param Title $title
	 * @param User $user
	 * @return bool Returns true when the provided user has the rights to
	 *  convert $title from whatever it is now to a flow board.
	 */
	public function isCreationAllowed( Title $title, User $user );

	/**
	 * Gives a user object used to manage talk pages
	 *
	 * @return User User to manage talkpages
	 * @throws MWException If a user cannot be created.
	 */
	public function getTalkpageManager();
}
