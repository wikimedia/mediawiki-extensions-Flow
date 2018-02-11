<?php

use Flow\Model\UUID;

class FlowXmlStackElt {
	const TYPE_BOARD = 1;
	const TYPE_DESCRIPTION = 2;
	const TYPE_TOPIC = 3;
	const TYPE_SUMMARY = 4;
	const TYPE_POST = 5;
	const TYPE_REVISION = 6;
	const TYPES = [ 'b', 'd', 't', 's', 'p', 'r' ];

	const ZEROHEX = "000000000000000000000000";

	/*
	 * @param string or int $type   string in TYPES or one of int TYPE_*
	 * @param string $idHex         id as hex string
	 */
	public function __construct(
		$idHex,
		$type
	) {
		if ( is_string( $type ) ) {
			$type = FlowXmlStackElt::typeToInt( $type );
		}
		if ( !is_string( $idHex ) || strlen( $idHex ) != 22 ) {
			$idHex = $this->idToHex( $idHex );
		}
		$this->type = $type;
		$this->idHex = $idHex;
	}

	public static function idToAlnum( $id ) {
		if ( $id ) {
			$uuid = UUID::create( $id );
			return $uuid->getAlphadecimal();
		} else {
			// lower value than any alnum string we would get
			return self::ZEROHEX;
		}
	}

	public static function idToHex( $id ) {
		if ( $id ) {
			if ( strlen( $id ) != 22 ) {
				$uuid = UUID::create( $id );
				return $uuid->getHex();
			} else {
				return $id;
			}
		} else {
			return self::ZEROHEX;
		}
	}

	public static function checkType( $type ) {
		if ( $type < self::TYPE_BOARD || $type > self::TYPE_REVISION ) {
			return false;
		} else {
			return true;
		}
	}

	public static function compare( $first, $second ) {
		if ( $first->type != $second->type ) {
			// groan. undefined.
			return null;
		}
		if ( $first == null && $second == null ) {
			return 0;
		} elseif ( $first == null ) {
			return -1;
		} elseif ( $second == null ) {
			return 1;
		}
		return ( strcmp( $first->idHex, $second->idHex ) );
	}

	public static function typeToInt( $typestring ) {
		if ( is_int( $typestring ) ) {
			return $typestring;
		}
		// if they pass in 'description', convert to 'd'
		// and so on
		if ( strlen( $typestring ) > 1 ) {
			$typestring = $typestring[0];
		}
		$index = array_search( $typestring, self::TYPES );
		if ( $index === false ) {
			return null;
		} else {
			return $index + 1;
		}
	}

	public static function typeToString( $type ) {
		if ( !ctype_digit( $type ) && !FlowXmlStackElt::checkType( $type ) ) {
			return 'null';
		} else {
			return self::TYPES[ $type - 1 ];
		}
	}

	public function prettyformat() {
		return FlowXmlStackElt::idToAlnum( $this->idHex ) . ':' . self::typeToString( $this->type );
	}
}

class FlowXmlStack {
	const BOARD_CHILDREN = [ 'description', 'topic' ];

	/*
	 * @param array $entries   array of FlowXmlStackElt
	 */
	public function __construct( $entries ) {
		$this->entries = $entries;
	}

	/*
	 * @param string $entries   comma-separated list of pairs id:type where id is a hex string
	 *			    or alnum, and type is one of 'b','d', 't', 's', 'p', 'r' corresponding to
	 *                          FlowXmlStackElt types
	 */
	public static function newFromString( $entrystring ) {
		$entries = [];
		$pairs = explode( ',', $entrystring );
		foreach ( $pairs as $pair ) {
			$fields = explode( ':', $pair );
			if ( count( $fields ) != 2 ) {
				throw new MWException( "bad xml stack argument $entrystring" );
			}
			$entries[] = new FlowXmlStackElt( $fields[0], $fields[1] );
		}
		// fixme I should really check that the stack order is correct. oh well
		return new static( $entries );
	}

	/*
	 * check that items on stack are consistent
	 */
	public function checkStack() {
		$count = count( $this->entries );
		if ( $count == 0 ) {
			return true;
		}
		if ( $count && $this->entries[ 0 ]->type != self::TYPE_BOARD ) {
			return false;
		}
		if ( $count > 1 &&
			$this->entries[ 1 ]->type != self::TYPE_DESCRIPTION &&
			$this->entries[ 1 ]->type != self::TYPE_TOPIC
		) {
			return false;
		}
		if ( $count > 2 ) {
			// maybe put the revid here too? well...
			for ( $i = 2; $i < $count; $i++ ) {
				if ( $this->entries[ $i ]->type != self::TYPE_POST &&
					$this->entries[ 1 ]->type != self::TYPE_SUMMARY
				) {
					return false;
				}
			}
		}
		for ( $i = 0; $i < $count; $i++ ) {
			if ( ! ctype_xdigit( $this->entries[ $i ]->idHex ) ) {
				return false;
			}
		}
		return true;
	}

	public function clear() {
		$this->entries = [];
	}

	/*
	 * Set the id of the first item in the stack with the
	 * specified type, and toss the rest of the stack
	 * If there is no such object, set one now
	 */
	public function setItem( $id, $type ) {
		$index = $this->getDepthType( $type );
		if ( $index !== null ) {
			$index--;
			$this->entries[ $index ]->idHex = FlowXmlStackElt::idToHex( $id );
			$this->truncate( $type );
		} else {
			// no such element. set one at end of stack.
			$this->entries[] = new FlowXmlStackElt( $id, $type );
			$this->truncate( $type );
		}
	}

	public function setBoard( $idHex ) {
		$this->setItem( $idHex, FlowXmlStackElt::TYPE_BOARD );
	}

	public function setBoardChild( $idHex, $type ) {
		if ( $type == 'description' || $type == FlowXmlStackElt::TYPE_DESCRIPTION ) {
			$this->setItem( $idHex, FlowXmlStackElt::TYPE_DESCRIPTION );
		} elseif ( $type == 'topic' || $type == FlowXmlStackElt::TYPE_TOPIC ) {
			$this->setItem( $idHex, FlowXmlStackElt::TYPE_TOPIC );
		} elseif ( $type == 'summary' || $type == FlowXmlStackElt::TYPE_SUMMARY ) {
			$this->setItem( $idHex, FlowXmlStackElt::TYPE_SUMMARY );
		}
	}

	public function hasItem( $type ) {
		if ( is_string( $type ) ) {
			$type = FlowXmlStackElt::typeToInt( $type );
		}
		$length = count( $this->entries );
		for ( $i = 0; $i < $length; $i++ ) {
			if ( $this->entries[ $i ]->type === $type ) {
				return true;
			}
		}
		return false;
	}

	/*
	 * Truncate the stack after the first element of the given type
	 * Takes no action if the type is bad or if there is no element
	 * of the specified type in the stack
	 *
	 * @param int $type   FlowXmlStackElt TYPE  or one of 'board', 'description', etc
	 */
	public function truncate( $type ) {
		$intType = FlowXmlStackElt::typeToInt( $type );
		if ( $intType !== null ) {
			$type = $intType;
		}
		if ( FlowXmlStackElt::checkType( $type ) ) {
			$length = count( $this->entries );
			for ( $depth = 0; $depth < $length; $depth++ ) {
				if ( $this->entries[ $depth ]->type == $type ) {
					$this->entries = array_slice( $this->entries, 0, $depth + 1 );
					break;
				}
			}
		}
	}

	public function appendPost( $idHex ) {
		$this->entries[] = new FlowXmlStackElt( $idHex, FlowXmlStackElt::TYPE_POST );
	}

	public function popPost() {
		// find the post farthest down on the stack, truncate the stack just before it.
		// if no post entries, do nothing.
		$length = count( $this->entries );
		for ( $i = $length - 1; $i >= 0; $i-- ) {
			if ( $this->entries[ $i ]->type == FlowXmlStackElt::TYPE_POST ) {
				$this->entries = array_slice( $this->entries, 0, $i );
				return;
			}
		}
	}

	public function getDepthType( $type ) {
		$length = count( $this->entries );
		for ( $i = 0; $i < $length; $i++ ) {
			if ( $this->entries[ $i ]->type === $type ) {
				return ( $i + 1 );
			}
		}
		return null;
	}

	public function getDepthId( $idHex, $type ) {
		$length = count( $this->entries );
		for ( $i = 0; $i < $length; $i++ ) {
			if ( $this->entries[ $i ]->idHex === $idHex && $this->entries[ $i ]->type === $type ) {
				return ( $i + 1 );
			}
		}
		return null;
	}

	public function getBoard() {
		foreach ( $this->entries as $entry ) {
			if ( $entry->type == FlowXmlStackElt::TYPE_BOARD ) {
				return ( $entry->idHex );
			}
		}
		return null;
	}

	/*
	 * @param array $first      array of FlowXmlStackElt
	 * @param array $second     array of FlowXmlStackElt
	 * @param int $depth        check if equal up to and including this depth (starts from 1)
	 *                          -1 means we check that both stacks have same entries for entire length(s)
	 * @param int $checklength  bool, true if stacks must be the same length in order to match
	 */
	public function compare( $first, $second, $depth = -1, $checklength = false ) {
		if ( $depth == -1 ) {
			$depth = count( $first->entries );
		}
		$upto = min( [ $depth, count( $first->entries ), count( $second->entries ) ] );
		for ( $i = 0; $i < $upto; $i++ ) {
			$result = FlowXmlStackElt::compare( $first->entries[ $i ], $second->entries[ $i ] );
			if ( $result === null || $result != 0 ) {
				return $result;
			}
		}
		if ( $upto < $depth ) {
			// one of the stacks is shorter than requested depth check.
			return ( count( $first->entries ) - count( $second->entries ) );
		} elseif ( $checklength ) {
			return ( count( $first->entries ) - count( $second->entries ) );
		} else {
			return 0;
		}
	}

	public function prettyformat() {
		$pairs = [];
		$length = count( $this->entries );
		for ( $i = 0; $i < $length; $i++ ) {
			$pairs[] = $this->entries[ $i ]->prettyformat();
		}
		return implode( ',', $pairs );
	}

	public function getPostStack() {
		// make a copy of the post items only and return as list of hex string ids
		$entries = [];
		$length = count( $this->entries );
		for ( $i = 0; $i < $length; $i++ ) {
			if ( $this->entries[ $i ]->type == FlowXmlStackElt::TYPE_POST ) {
				$entries[] = $this->entries[ $i ]->idHex;
			}
		}
		return $entries;
	}
}

class FlowBaseDump extends BaseDump {
	protected $atEnd = false;
	protected $atBoardEnd = false;
	protected $atRevisionsEnd = false;
	protected $childrenDepth = 0;

	protected $lastStack = null;
	protected $lastRev = FlowXmlStackElt::ZEROHEX;
	protected $readsDone = false;

	public function __construct( $infile ) {
		parent::__construct( $infile );
		$this->lastStack = new FlowXmlStack( [] );
	}

	/*
	 * @param string $thisStack    stack of elements (board, description/summary/topic, posts, etc) including item we want to find
	 * @param int $depth           depth in stack of element type/id we want to find in prefetch file
	 * @param callable $getNextId  function to get the next element of the desired type and stash its id in lastStack appropriately
	 */
	function findContainingElt( $thisStack, $depth, callable $getNextId ) {
		$this->readsDone = true;
		while ( FlowXmlStack::compare( $this->lastStack, $thisStack, $depth ) < 0 &&
			!$this->atEnd &&
			$this->readsDone ) {
			$this->readsDone = false;
			call_user_func( $getNextId );
		}
		if ( FlowXmlStack::compare( $this->lastStack, $thisStack, $depth ) > 0 || $this->atEnd ) {
			return false;
		} else {
			return true;
		}
	}

	function readPrefetch() {
		if ( $this->atEnd ) {
			return;
		}
		if ( !$this->reader->read() ) {
			$this->atEnd = true;
			$this->close();
		}
		$this->readsDone = true;
		$this->setLastElementId();
		$this->clearLastElementIds();
	}

	function readPrefetchUptoTag() {
		$this->readPrefetch();
		while ( $this->reader->nodeType != XMLReader::ELEMENT &&
			$this->reader->nodeType != XMLReader::END_ELEMENT &&
			!$this->atEnd
		) {
			$this->readPrefetch();
		}
	}

	function setLastElementId() {
		// expects XMLReader cursor to be at an appropriate start element.
		if ( $this->reader->nodeType != XMLReader::ELEMENT ) {
			return;
		}

		if ( $this->reader->name == 'children' ) {
			$this->childrenDepth++;
		} elseif ( in_array( $this->reader->name, array_merge( [ 'board' ], FlowXmlStack::BOARD_CHILDREN ) ) ) {
			$this->childrenDepth = 0;
		}

		$id = $this->reader->getAttribute( 'id' );
		if ( $id == null ) {
			return;
		}
		$idHex = FlowXmlStackElt::idToHex( $id );

		$this->atRevisionsEnd = false;
		$this->lastRev = FlowXmlStackElt::ZEROHEX;

		if ( $this->reader->name == 'board' ) {
			$this->lastStack = FlowXmlStack::newFromString( "$idHex:b" );
			$this->atBoardEnd = false;
		} elseif ( in_array( $this->reader->name, FlowXmlStack::BOARD_CHILDREN ) ) {
			$this->lastStack->truncate( FlowXmlStackElt::TYPE_BOARD );
			$this->lastStack->setBoardChild( $idHex, $this->reader->name );
		} elseif ( $this->reader->name == 'post' ) {
			if ( $this->childrenDepth >= count( $this->lastStack->getPostStack() ) ) {
				// child of last post on stack
				$this->lastStack->appendPost( $idHex );
			} else {
				// peer to last post on stack if any
				$this->lastStack->popPost();
				$this->lastStack->appendPost( $idHex );
			}
		} elseif ( $this->reader->name == 'revision' ) {
			$this->lastRev = $idHex;
		}
	}

	function clearLastElementIds() {
		// expects XMLReader cursor to be at an appropriate end element.
		if ( $this->reader->nodeType != XMLReader::END_ELEMENT ) {
			return;
		}

		$this->atRevisionsEnd = false;
		$this->lastRev = FlowXmlStackElt::ZEROHEX;

		if ( in_array( $this->reader->name, array_merge( [ 'board' ], FlowXmlStack::BOARD_CHILDREN ) ) ) {
			$this->lastStack->truncate( $this->reader->name );
			$this->childrenDepth = 0;
		} elseif ( $this->reader->name == 'children' ) {
			// posts, children of children...
			$this->childrenDepth--;
		}

		if ( $this->reader->name == 'board' ) {
			$this->atBoardEnd = true;
		} elseif ( $this->reader->name == 'post' ) {
			$this->lastStack->popPost();
		} elseif ( $this->reader->name == 'revisions' ) {
			$this->atRevisionsEnd = true;
		}
	}

	function closeFiles() {
		if ( count( $this->infiles ) ) {
			$infile = array_shift( $this->infiles );
			$this->reader->open( $infile );
			$this->atEnd = false;
		}
	}

	function nextBoard() {
		$this->skipStartElementTag( 'board' );
		$this->skipTo( 'board', [ 'mediawiki' ] );
	}

	function findBoard( $stack ) {
		return $this->findContainingElt(
			$stack,
			$stack->getDepthType( FlowXMLStackElt::TYPE_BOARD ),
			'self::nextBoard' );
	}

	function findDescription( $stack ) {
		return $this->findContainingElt(
			$stack,
			$stack->getDepthType( FlowXMLStackElt::TYPE_DESCRIPTION ),
			'self::nextDescription' );
	}

	function findSummary( $stack ) {
		return $this->findContainingElt(
			$stack,
			$stack->getDepthType( FlowXMLStackElt::TYPE_SUMMARY ),
			'self::nextSummary' );
	}

	function findTopic( $stack ) {
		return $this->findContainingElt(
			$stack,
			$stack->getDepthType( FlowXMLStackElt::TYPE_TOPIC ),
			'self::nextTopic' );
	}

	function findPostThisDepth( $stack, $depth ) {
		$this->readsDone = true;
		while ( FlowXmlStack::compare( $this->lastStack, $stack, $depth ) < 0 &&
			!$this->atEnd &&
			!$this->atElementEnd( [ 'children' ] &&
			$this->readsDone )
		) {
			$this->readsDone = false;
			$this->skipRestOfPost();  // leaves us at beginning of next post same level, if any
		}
		if ( FlowXmlStack::compare( $this->lastStack, $stack, $depth ) > 0 || $this->atEnd ) {
			// passed the post number we wanted, it's not there
			return false;
		} elseif ( FlowXmlStack::compare( $this->lastStack, $stack, $depth ) < 0 ) {
			// ran out of posts at this depth
			return false;
		} else {
			return true;
		}
	}

	function skipToEndElement( $name, $parents = [ 'board' ] ) {
		// leaves cursor at end element if present
		if ( $this->atEnd ) {
			return false;
		}
		while ( !$this->atEnd ) {
			$this->readPrefetch();
			if ( $this->reader->nodeType == XMLReader::END_ELEMENT ) {
				if ( in_array( $this->reader->name, $parents ) ) {
					return false;
				} elseif ( $this->reader->name == $name ) {
					return true;
				}
			}
		}
		return false;
	}

	function skipRevisionList() {
		if ( $this->atEnd ) {
			return;
		}
		if ( ! $this->atElementStart( 'revisions' ) &&
			!$this->atElementStart( 'revision' ) &&
			!$this->atElementEnd( [ 'revision', 'revisions' ] )
		) {
			return;
		}
		if ( $this->atElementEnd( [ 'revisions' ] ) ) {
			$this->readPrefetchUptoTag();
		} elseif ( $this->skipToEndElement( 'revisions', array_merge( FlowXmlStack::BOARD_CHILDREN, [ 'post' ] ) ) ) {
			if ( $this->atElementEnd( [ 'revisions' ] ) ) {
				$this->readPrefetchUptoTag();
			}
		}
	}

	function atElementEnd( $names ) {
		if ( $this->atEnd ) {
			return false;
		}
		return ( $this->reader->nodeType == XMLReader::END_ELEMENT &&
			in_array( $this->reader->name, $names ) );
	}

	function atElementStart( $name ) {
		if ( $this->atEnd ) {
			return false;
		}
		return ( $this->reader->nodeType == XMLReader::ELEMENT && $this->reader->name == $name );
	}

	function skipChildrenList() {
		if ( $this->atEnd ) {
			return;
		}
		if ( !$this->atElementStart( 'children' ) ) {
			return;
		}
		$this->skipTo( 'post', [ 'children' ] );
		$this->readsDone = true;
		while ( !$this->atElementEnd( [ 'children' ] ) && !$this->atEnd && $this->readsDone ) {
			$this->readsDone = false;
			$this->skipPost();
		}
		$this->skipEndElementTag( 'children' );
	}

	function skipStartElementTag( $name ) {
		// get the next line, if we are at the start element tag
		if ( !$this->atEnd && $this->atElementStart( $name ) ) {
			$this->readPrefetchUptoTag();
		}
	}

	function skipEndElementTag( $name ) {
		// get the next line, if we are at end element tag
		if ( $this->atElementEnd( [ $name ] ) ) {
			$this->readPrefetchUptoTag();
		}
	}

	function skipRestOfPost() {
		// finish up whatever post we are in, read its
		// end post tag, return next line (or run out
		// and return some other containing element end line, or EOF)
		$this->skipStartElementTag( 'post' );
		$this->skipRevisionList();
		$this->skipChildrenList();
		$this->skipEndElementTag( 'post' );
	}

	/*
	 * skip the current post including all children, leaves cursor at line after end post tag
	 * if successful, or at end of parent element (children, topic) in case of broken xml, or eof
	 */
	function skipPost() {
		if ( !$this->atElementStart( 'post' ) ) {
			return;
		}
		$this->skipStartElementTag( 'post' );
		$this->skipRevisionList();  // returns line after end revisions
		$this->skipChildrenList();  // returns line after end children
		$this->skipEndElementTag( 'post' );  // returns line after end post: end (children, topic) or eof

	}

	function readPostList() {
		$this->skipRestOfPost();
		$this->readsDone = true;
		while ( !$this->atEnd &&
			!$this->atElementEnd( array_merge( [ 'board' ], FlowXmlStack::BOARD_CHILDREN, [ 'children' ] ) &&
			$this->readsDone )
		) {
			$this->readsDone = false;
			$this->skipPost();
		}
	}

	function popPostStack( $depth ) {
		// read entries from the prefetch file starting with the
		// current 'leaf' of the post tree, reading the remaining 'leaves'
		// (posts at the same level), until we reach the end of the parent
		// element.
		// at this point the stack will be one element shorter. repeat
		// until we have gotten to the specfied stack depth.
		$length = count( $this->lastStack->getPostStack() );

		// for the first wrong entry in the stack, we want to just skip the rest of current post
		// for everything lower down, we want to read past all the entries at that level
		for ( $i = $length; $i > $depth; $i-- ) {
			$idAlnum = FlowXmlStackElt::idToAlnum( $this->lastStack->getPostStack()[ $i - 1 ] );
			$this->readPostList(); // returns end (children or topic or summary or description or board) or eof
			if ( $this->atElementEnd( array_merge( [ 'board' ], FlowXmlStack::BOARD_CHILDREN ) ) || $this->atEnd ) {
				return;
			}
			$this->skipEndElementTag( 'children' );
			$this->skipEndElementTag( 'post' );
		}
	}

	/*
	 * for each post id in the stack, locate it in the prefetch file, not moving
	 * past its parent element
	 *
	 * return true if found, false otherwise
	 *
	 * @param array $thisStack   list of post ids, hex string format or alnum,
	 *                           starting from top of post tree (has topic
	 *                           for parent) to the descendant post we want
	 *                           (has the desired revid for child)
	 */
	function walkPostTree( $thisStack ) {
		$postStack = $thisStack->getPostStack();
		$stackDepth = 0;
		foreach ( $postStack as $thisId ) {
			$stackDepth++;
			$idHex = FlowXmlStackElt::idToHex( $thisId );

			$lastPostStack = $this->lastStack->getPostStack();
			if ( count( $lastPostStack ) >= $stackDepth ) {
				if ( $lastPostStack[ $stackDepth - 1 ] != $idHex ) {
					if ( $lastPostStack[ $stackDepth - 1 ] > $idHex ) {
						// we're already past the desired post
						return false;
					}
					// we're not yet at the desired post at the stack depth, move past
					// the children in the tree at this point and toss that stuff off of stack
					$this->popPostStack( $stackDepth );
					$lastPostStack = $this->lastStack->getPostStack();
					if ( !$lastPostStack ) {
						// ran out of posts in topic, summary, board or file. oops.
						return false;
					}
					// try to find the desired post at the current stack depth
					if ( !$this->findPostThisDepth(
						$thisStack,
						$thisStack->getDepthId( $postStack[ $stackDepth - 1 ],
						FlowXmlStackElt::TYPE_POST ) )
					) {
						return false;
					}
				}
			} else {
				// no more stack to walk down on the prefetch side.
				// starting at the beginning of a post list.
				if ( !$this->descendPostTree() ) {
					return false;
				}
				if ( !$this->findPostThisDepth(
					$thisStack,
					$thisStack->getDepthId( $postStack[ $stackDepth - 1 ],
					FlowXmlStackElt::TYPE_POST ) )
				) {
					return false;
				}
			}

		}
		return true;
	}

	function descendPostTree() {
		// this finds the first child post of the current post, if any.
		if ( !$this->skipTo( 'children', array_merge( FlowXmlStack::BOARD_CHILDREN, [ 'post' ] ) ) ) {
			return false;
		}
		if ( !$this->atElementStart( 'children' ) ) {
			return false;
		}
		if ( !$this->skipTo( 'post', array_merge( [ 'board' ], FlowXmlStack::BOARD_CHILDREN ) ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Attempts to fetch the text of a particular revision
	 * from the dump stream. May return null if the containing
	 * board or description/summary/post is unavailable.
	 *
	 * The below parameters are all b36 encoded UUIDs as written to/read from xml files
	 * Note that a revision has either a post, a description or a summary as a parent.
	 * A post has either a post or a topic as parent.  Topics, summaries, descriptions
	 * all have boards as parents.
	 *
	 * @param string $thisRev              ID number of revision to read
	 * @param FlowXmlStack $thisStack      array of ids and element types
	 *
	 * @return string|null
	 */
	function prefetch( $thisRev, $thisStack ) {
		$this->debug( "stack to follow: " . $thisStack->prettyformat()
			. " with thisRev: <" . print_r( FlowXmlStackElt::idToAlnum( $thisRev ), true ) . ">" );

		if ( !$this->findBoard( $thisStack ) ) {
			return null;
		}
		$this->debug( "board found." );
		if ( $thisStack->hasItem( 'description' ) ) {
			if ( ! $this->findDescription( $thisStack ) ) {
				return null;
			}
		} elseif ( $thisStack->hasItem( 'topic' ) ) {
			if ( ! $this->findTopic( $thisStack ) ) {
				return null;
			}
		}
		$this->debug( "description or topic found." );

		if ( $thisStack->hasItem( 'topic' ) ) {
			if ( $thisStack->hasItem( 'summary' ) ) {
				if ( ! $this->findSummary( $thisStack ) ) {
					return null;
				}
			} else {
				// move past start topic if we are positioned there
				// in prefetch file.
				// then walk through posts as needed
				if ( $this->atElementStart( 'topic' ) ) {
					$this->readPrefetchUpToTag();
				}
				if ( ! $this->walkPostTree( $thisStack ) ) {
					return null;
				}
			}
		}

		$this->debug( "walked post tree ok." );

		// now we should be at a revision list at the proper tree level,
		// go through the rev list and see if ours is there, stopping if we hit a rev id
		// greater than the one wanted. We check we haven't gone past the end of
		// the board in case of broken xml.
		$this->readsDone = true;
		while ( $this->lastRev != $thisRev && !$this->atEnd &&
			!$this->atRevisionsEnd && !$this->atBoardEnd &&
			$this->readsDone
		) {
			$this->readsDone = false;
			$this->nextRev();
		}
		if ( $this->lastRev == $thisRev && !$this->atEnd ) {
			return $this->nodeContents();
		} else {
			return null;
		}
	}

	/**
	 * search for the next description within a board.
	 * There Can Be Only One... in theory.  But, you know.
	 */
	function nextDescription() {
		$this->skipStartElementTag( 'description' );
		$this->skipTo( 'description', [ 'board' ], [ 'topic', 'summary' ] );
	}

	/**
	 * search for the next summary within a board.
	 */
	function nextSummary() {
		$this->skipStartElementTag( 'summary' );
		$this->skipTo( 'summary', [ 'board' ], [ 'topic', 'description' ] );
	}

	/**
	 * search for the next topic within a board.
	 */
	function nextTopic() {
		$this->skipStartElementTag( 'topic' );
		$this->skipTo( 'topic', [ 'board' ] ); // stops at topic tag, end board, or eof
	}

	function nextRev() {
		$this->skipStartElementTag( 'revision' );
		$this->skipTo( 'revision',
				array_merge( FlowXmlStack::BOARD_CHILDREN, [ 'post', 'children', 'revisions' ] ),
				array_merge( [ 'board' ], FlowXmlStack::BOARD_CHILDREN, [ 'post', 'revisions' ] )
		);
	}

	/**
	 * stops at end tag of parent, or start tag of any stopper
	 * @access private
	 * @param string $name
	 * @param string[] $stoppers
	 * @return bool|null
	 */
	function skipTo( $name, $parents = [ 'board' ], $stoppers = [] ) {
		// if already there, just return.
		if ( $this->atElementStart( $name ) ) {
			return true;
		}
		while ( !$this->atEnd ) {
			$this->readPrefetch();
			if ( $this->atElementStart( $name ) ) {
				return true;
			} elseif ( $this->reader->nodeType == XMLReader::END_ELEMENT
				&& in_array( $this->reader->name, $parents )
			) {
				return false;
			} elseif ( $this->reader->nodeType == XMLReader::ELEMENT
				&& in_array( $this->reader->name, $stoppers )
			) {
				return false;
			}
		}
		return false;
	}
}
