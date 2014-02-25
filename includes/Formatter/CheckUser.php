<?php

namespace Flow\Formatter;

use Flow\Model\UUID;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use Html;
use IContextSource;
use Linker;
use Title;

class CheckUser {

	/**
	 * Create an array of links to be used when formatting the row in checkuser
	 *
	 * @param object $row
	 * @param IContextSource $ctx
	 * @return array|null
	 */
	public function format( $row, IContextSource $ctx ) {
		if ( $row->cuc_type != RC_FLOW || !$row->cuc_comment ) {
			return null;
		}

		// @todo: this currently only implements CU links
		// we'll probably want to add a hook to CheckUser that lets us blank out
		// the entire line for entries that !isAllowed( <this-revision>, 'checkuser' )
		// @todo: we probably want to get the action description (generated revision comment)
		// into checkuser sooner or later as well.

		$data = explode( ',', $row->cuc_comment );
		$post = null;
		switch( count( $data ) ) {
			/** @noinspection PhpMissingBreakStatementInspection */
			case 3:
				$post = UUID::create( $data[2] );
				// fall-through to 2 parameter case
			case 2:
				$workflow = UUID::create( $data[1] );
				$action = $data[0];
				break;
			default:
				wfDebugLog( __CLASS__, __FUNCTION__ . ': Invalid number of parameters received from cuc_comment.  Expected 2 or 3 but received ' . count( $data ) );
				return null;
		}

		$title = Title::makeTitle( $row->cuc_namespace, $row->cuc_title );
		$links = $this->serializer->buildActionLinks( $title, $action, $workflow, null, $post );
		if ( $links === false ) {
			wfWarn( 'NO LINKS' );
			return null;
		}

		$result = array();
		foreach ( $links as $key => $link ) {
			$result[$key] = $ctx->msg( 'parentheses' )
				// @todo these text strings are using $wgLang instead of $ctx->getLanguage()
				->rawParams( $this->apiLinkToAnchor( $link ) );
		}
		$result['title'] = '. . ' . Linker::link( $title );

		return $result;
	}
}
