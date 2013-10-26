<?php

namespace Flow\Model;

/**
 * This is a virutal model for Board history entry, which is a wrapper for
 * Header and PostRevision
 */
class BoardHistoryEntry {

	/**
	 * The revision for a board history entry
	 * @var Header/PostRevision
	 */
	protected $revision;

	/**
	 * Wrapper function for Header/PostRevision fromStorageRow method
	 */
	public static function fromStorageRow( array $row, $obj = null ) {
		if ( $row['rev_type'] === 'header' ) {
			return Header::fromStorageRow( $row );
		} elseif ( $row['rev_type'] === 'post' ) {
			return PostRevision::fromStorageRow( $row );
		} else {
			throw new \MWException( 'Invalid rev_type for board history entry: ' . $row['rev_type'] );	
		}
	}

	/**
	 * Wrapper function for toStoragerow method
	 */
	public static function toStorageRow( $rev ) {
		return $rev->toStorageRow( $rev );
	}

}
