<?php

namespace Flow\RecentChanges;

use Flow\AbstractFormatter;
use Flow\Model\UUID;
use ChangesList;
use Html;
use RecentChange;

class Formatter extends AbstractFormatter {
	public function format( ChangesList $cl, RecentChange $rc ) {
		$params = unserialize( $rc->getAttribute( 'rc_params' ) );
		$changeData = $params['flow-workflow-change'];

		if ( !is_array( $changeData ) ) {
			wfWarn( __METHOD__ . ': Flow data missing in recent changes.' );
			return false;
		}

		$title = $rc->getTitle();
		$user = $cl->getUser();
		$lang = $cl->getLanguage();

		/*
		 * @todo:
		 * We should some day introduce a hook in core that allows us to hook
		 * into the full resultset, so we can first loop all entries and pre-
		 * load workflows & revisions all at once.
		 *
		 * Since that's not yet the case, multiple queries (up to $wgFeedLimit)
		 * may occur. Assuming revisions (yes) and workflow (less likely) are
		 * all different, there can be $wgFeedLimit * 2 queries to DB or cache.
		 */
		$workflow = $this->loadWorkflow( UUID::create( $changeData['workflow'] ) );
		if ( !$workflow ) {
			return false;
		}

		$revision = $this->loadRevision( UUID::create( $changeData['revision'] ), $changeData['revision_type'] );
		if ( !$revision ) {
			return false;
		}

		$links = (array) $this->buildActionLinks(
			$title,
			$revision->getChangeType(),
			$workflow->getId(),
			method_exists( $revision, 'getPostId' ) ? $revision->getPostId() : null
		);

		// Format links
		foreach ( $links as &$link ) {
			list( $url, $text ) = $link;
			$link = Html::element(
				'a',
				array(
					'href' => $url,
					'title' => $text
				),
				$text
			);
		}
		$linksContent = $lang->pipeList( $links );
		if ( $linksContent ) {
			$linksContent = wfMessage( 'parentheses' )->rawParams( $linksContent )->escaped()
				. $this->changeSeparator();
		}

		$dateFormats = $this->getDateFormats( $revision, $user, $lang );
		$formattedTime = '<span class="mw-changeslist-date">' . $dateFormats['time'] . '</span>';

		$workflowLink = $this->workflowLink( $title, $workflow->getId() );
		$workflowLink = Html::element(
			'a',
			array( 'href' => $workflowLink[0] ),
			$workflowLink[1]
		);

		return $linksContent
			. $workflowLink
			. wfMessage( 'semicolon-separator' )->escaped()
			. $formattedTime
			. ' '
			. $this->changeSeparator()
			. ' '
			. $this->getActionDescription( $workflow, $changeData['block'], $revision );
	}

	protected function changeSeparator() {
		return ' <span class="mw-changeslist-separator">. .</span> ';
	}
}
