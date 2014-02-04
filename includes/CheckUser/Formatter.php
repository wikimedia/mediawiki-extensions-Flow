<?php

namespace Flow\CheckUser;

use Language;
use Html;
use Linker;
use Title;
use Flow\AbstractFormatter;
use Flow\Model\UUID;

class Formatter extends AbstractFormatter {

	public function format( Language $lang, $row ) {
		if ( $row->cuc_type != RC_FLOW || !$row->cuc_comment ) {
			return false;
		}

		$title = Title::makeTitle( $row->cuc_namespace, $row->cuc_title );
		if ( !$title ) {
			return false;
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
				return false;
		}

		$links = $this->buildActionLinks( $title, $action, $workflow, $post );
		if ( $links === false ) {
			return false;
		}

		$output = '';
		foreach ( $links as $i => $link ) {
			// @todo these $text strings are using $wgLang instead of the provided lang
			list( $url, $text ) = $link;
			$output .= wfMessage( 'parentheses' )
				->inLanguage( $lang )
				->rawParams( Html::element(
					'a',
					array(
						'href' => $url,
						'title' => $text,
					),
					$text
				) ) . ' ';
		}

		return "$output . . " . Linker::link( $title );
	}
}
