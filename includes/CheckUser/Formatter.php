<?php

namespace Flow\CheckUser;

use CheckUser;
use Html;
use Linker;
use Title;
use Flow\AbstractFormatter;
use Flow\Model\UUID;

class Formatter extends AbstractFormatter {

	/**
	 * @param CheckUser $checkUser
	 * @param object $row
	 * @return array|null
	 */
	public function format( CheckUser $checkUser, $row ) {
		if ( $row->cuc_type != RC_FLOW || !$row->cuc_comment ) {
			return null;
		}

		$data = explode( ',', $row->cuc_comment );
		$post = null;
		switch( count( $data ) ) {
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
		$ctx = $checkUser->getContext();
		foreach ( $links as $key => $link ) {
			list( $url, $message ) = $link;
			$text = $message->setContext( $ctx )->text();
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
