<?php

use Flow\Model\UUID;

class FlowBaseDump extends BaseDump {
	protected $atBoardEnd = false;
	protected $lastBoard = 0;

	/**
	 * Attempts to fetch the text of a particular board revision
	 * from the dump stream. May return null if the board is
	 * unavailable.
	 *
	 * @param hex $board ID number of board to read
	 * @param int $rev ID number of revision to read
	 * @return string|null
	 */
	function prefetch( $board, $rev ) {
		while ( $this->lastBoard < $board && !$this->atEnd ) {
			$this->debug( "BaseDump::prefetch at board $this->lastBoard, looking for $board" );
			$this->nextBoard();
		}
		if ( $this->lastBoard > $board || $this->atEnd ) {
			$this->debug( "BaseDump::prefetch already past board $board "
				. "looking for rev $rev  [$this->lastBoard, $this->lastRev]" );
			return null;
		}
		// while ( $this->lastRev < $rev && !$this->atEnd && !$this->atBoardEnd ) {
		// revs are not in ascending order. so just check the whole board,
		// most of the time this should not add too much extra processing
		// new revs can creep in where, though? in the middle of a board?
		// FIXME we may need to save topic numbers and make sure we're in the right one,
		// is that even enough?  what's the dump order?? we can make the dump order fixed, right?
		while ( $this->lastRev != $rev && !$this->atEnd && !$this->atBoardEnd ) {
			$this->debug( "BaseDump::prefetch at board $this->lastBoard, rev $this->lastRev, "
				. "looking for $board, $rev" );
			$this->nextRev();
		}
		if ( $this->lastRev == $rev && !$this->atEnd ) {
			$this->debug( "BaseDump::prefetch hit on $board, $rev [$this->lastBoard, $this->lastRev]" );

			return $this->nodeContents();
		} else {
			$this->debug( "BaseDump::prefetch already past rev $rev on board $board "
				. "[$this->lastBoard, $this->lastRev]" );

			return null;
		}
	}

	/**
	 * @access private
	 */
	function nextBoard() {
		if ( $this->skipTo( 'board', 'mediawiki' ) ) {
			$id = $this->reader->getAttribute( 'id');
			if ( $id ) {
                                $boardId = UUID::create( $id );
				$this->lastBoard = $boardId->getHex();
				$this->lastRev = 0;
				$this->atBoardEnd = false;
			}
		} else {
			$this->close();
			if ( count( $this->infiles ) ) {
				$infile = array_shift( $this->infiles );
				$this->reader->open( $infile );
				$this->atEnd = false;
			}
		}
	}

	/**
	 * @access private
	 */
	function nextRev() {
		if ( $this->skipTo( 'revision' ) ) {
			$id = $this->reader->getAttribute( 'id');
			if ( $id ) {
                                $revId = UUID::create( $id );
				$this->lastRev = $revId->getHex();
			}
		} else {
			$this->atBoardEnd = true;
		}
	}

}
