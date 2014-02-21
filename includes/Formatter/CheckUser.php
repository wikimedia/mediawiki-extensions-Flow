<?php

namespace Flow\Formatter;

use CheckUser as CheckUserSpecialPage;
use Html;
use Linker;
use Title;
use Flow\Model\UUID;

class CheckUser extends AbstractFormatter {

	/**
	 * @param CheckUserSpecialPage $checkUser
	 * @param object $row
	 * @return array|null
	 */
	public function format( CheckUserSpecialPage $checkUser, $row ) {
		if ( $row->cuc_type != RC_FLOW || !$row->cuc_comment ) {
			return null;
		}

		// @todo: this currently only implements CU links
		// we'll probably want to add a hook to CheckUser that lets us blank out
		// the entire line for entries that !isRevisionAllowed( <this-revision>, 'checkuser' )

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
		$links = $this->buildActionLinks( $title, $action, $workflow, $post );
		if ( $links === false ) {
			return null;
		}

		$result = array();
		foreach ( $links as $key => $link ) {
			// @todo these $text strings are using $wgLang instead of $checkUser->getLanguage()
			list( $url, $text ) = $link;
			$result[$key] = $checkUser->msg( 'parentheses' )
				->rawParams( Html::element(
					'a',
					array(
						'href' => $url,
						'title' => $text,
					),
					$text
				) );
		}
		$result['title'] = '. . ' . Linker::link( $title );

		return $result;
	}
}
