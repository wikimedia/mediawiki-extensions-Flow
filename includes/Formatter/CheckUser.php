<?php

namespace Flow\Formatter;

use Flow\Model\UUID;
use IContextSource;
use Linker;
use Title;

class CheckUser extends AbstractFormatter {

	/**
	 * Create an array of links to be used when formatting the row in checkuser
	 *
	 * @param \stdClass $row Row of data provided by the check user special page
	 * @param IContextSource $ctx
	 * @return array|null
	 */
	public function format( $row, IContextSource $ctx ) {
		if ( $row->cuc_type != RC_FLOW || !$row->cuc_comment ) {
			return null;
		}
		// anything not prefixed v1 is a pre-versioned check user comment
		// if it changes again the prefix can be updated.
		if ( substr( $row->cuc_comment, 0, 3 ) !== 'v1,' ) {
			return null;
		}

		// @todo: this currently only implements CU links
		// we'll probably want to add a hook to CheckUser that lets us blank out
		// the entire line for entries that !isAllowed( <this-revision>, 'checkuser' )
		// @todo: we probably want to get the action description (generated revision comment)
		// into checkuser sooner or later as well.

		$data = explode( ',', $row->cuc_comment );
		// remove the version specifier
		array_shift( $data );
		$post = null;
		switch( count( $data ) ) {
			/** @noinspection PhpMissingBreakStatementInspection */
			case 4:
				$post = UUID::create( $data[3] );
				// fall-through to 3 parameter case
			case 3:
				$revision = UUID::create( $data[2] );
				$workflow = UUID::create( $data[1] );
				$action = $data[0];
				break;
			default:
				wfDebugLog( 'Flow', __METHOD__ . ': Invalid number of parameters received from cuc_comment.  Expected 2 or 3 but received ' . count( $data ) );
				return null;
		}

		$title = Title::makeTitle( $row->cuc_namespace, $row->cuc_title );
		$links = $this->serializer->buildActionLinks( $title, $action, $workflow, $revision, $post );

		if ( count( $links ) === 0 ) {
			wfDebugLog( 'Flow', __METHOD__ . ': No links were generated for revision ' . $revision->getAlphadecimal() );
			return null;
		}

		$result = array();
		foreach ( $links as $key => $link ) {
			$result[$key] = $ctx->msg( 'parentheses' )
				// @todo these text strings are using $wgLang instead of $ctx->getLanguage()
				->rawParams( $this->anchorToHtml( $link ) );
		}
		$result['title'] = '. . ' . Linker::link( $title );

		return $result;
	}
}
