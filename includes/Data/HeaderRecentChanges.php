<?php

namespace Flow\Data;

use Flow\Exception\FlowException;
use Flow\FlowActions;
use Flow\Model\Header;
use Flow\Parsoid\Utils;
use Language;

/**
 * Create mediawiki recent change rows for newly created Header revisions
 */
class HeaderRecentChanges extends RecentChanges {
	/**
	 * @var Language Content Language
	 */
	protected $contLang;

	public function __construct( FlowActions $actions, UserNameBatch $usernames, Language $contLang ) {
		parent::__construct( $actions, $usernames );
		$this->contLang = $contLang;
	}

	/**
	 * @param Header $object
	 * @param string[] $row
	 */
	public function onAfterInsert( $object, array $row, array $metadata ) {
		if ( !isset( $metadata['workflow'] ) ) {
			throw new FlowException( 'Missing required metadata: workflow' );
		}

		$this->insert(
			$object,
			'header',
			'Header',
			$row,
			$metadata['workflow'],
			array(
				'content' => Utils::htmlToPlaintext(
					$object->getContent(),
					self::TRUNCATE_LENGTH,
					$this->contLang
				),
			)
		);
	}
}
