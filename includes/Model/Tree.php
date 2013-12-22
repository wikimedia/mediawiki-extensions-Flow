<?php

namespace Flow\Model;

interface TreeNode {
	function getChildren();
}

class TreeProcessor {

	protected $commandToLabel;
	protected $labelToNode;
	protected $queued;
	protected $results;

	public function __construct() {
		$this->labelToNode = array();
		$this->queued = new \SplObjectStore;
	}

	/**
	 * Runs all registered callback on every descendant of this post.
	 *
	 * Used to defer going recursive more than once: if all recursive
	 * functionality is first registered, we can fetch all results in one go.
	 *
	 * @return int Identifier to pass to getRecursiveResult() to retrieve
	 * the callback's result
	 */
	public function register( TreeNode $node, $command ) {
		$label = count( $this->labelToNode );
		$this->labelToNode[$label] = $node;
		$this->queued[$node][$label] = $command;

		return $label;
	}

	/**
	 * Returns the result of a specific callback, after having iterated over
	 * all children.
	 *
	 * @param string $label The identifier that was returned when registering
	 * the callback via TreeProcessor::register()
	 * @return mixed
	 */
	public function getResult( $label ) {
		if ( !isset( $this->results[$label] ) ) {
			if ( !isset( $this->labelToNode[$label] ) ) {
				throw new \MWException( 'Unregistered label' );
			}

			$node = $this->labelToNode[$label];
			$this->descend( $node, $this->queued[$node] );
			$this->results += $this->queued[$node];
			unset( $this->queued[$node] );
		}
		return $this->results[$label]->getResult();
	}

	public function registerAndGet( TreeNode $node, $command ) {
		return $this->getResult( $this->register( $node, $command ) );
	}

	/**
	 * Runs all registered callback on this post and all descendants to a
	 * maximum depth of $maxDepth
	 *
	 * @param TreeNode      $node     Node to run commands against
	 * @param array         $commands Array of commands to execute
	 * @param int[optional] $maxDepth The maximum depth to travel
	 * @return array $commands The remaining commands to run
	 */
	protected function descend( TreeNode $node, array $commands, $maxDepth = 10 ) {
		if ( !$commands || $maxDepth <= 0 ) {
			return $commands;
		}

		foreach ( $commands as $i => $command ) {
			$command->execute( $node );
			if ( $command->isFinished() ) {
				unset( $commands[$i] );
			}
		}

		foreach ( $node->getChildren() as $child ) {
			$commands = $this->descend( $child, $commands, $maxDepth - 1 );
		}

		return $commands;
	}
}

class DescendantCount implements NodeFunction {

	/**
	 * We start at -1 because the root post (topic title)
	 * does not count.
	 */
	protected $result = -1;

	/**
	 * Adds 1 to the total value per post that's iterated over.
	 *
	 * @param PostRevision $post
	 * @param int $result
	 * @return array Return array in the format of [result, continue]
	 */
	public function execute( PostRevision $post ) {
		$this->result++;
	}

	public function isFinished() {
		return false;
	}

	public function getResult() {
		return $this->result;
	}
}

class Participants {
	protected $result = array();

	/**
	 * Adds the user object of this post's creator.
	 *
	 * @param PostRevision $post
	 * @param int $result
	 */
	public function execute( PostRevision $post ) {
		$creator = $post->getCreator();
		if ( $creator instanceof User ) {
			$this->result[$post->getCreatorName()] = $creator;
		}
	}

	public function isFinished() {
		return false;
	}

	public function getResult() {
		return $this->result;
	}
}

class LocatePost {

	protected $result;

	public function __construct( $postId ) {
		if ( $postId instanceof UUID ) {
			$this->postId = $postId;
		} else {
			$this->postId = UUID::create( $postId );
		}
	}

	/**
	 * Returns the found post.
	 *
	 * @param PostRevision $post
	 * @param int $result
	 * @return array Return array in the format of [result, continue]
	 */
	public function execute( PostRevision $post ) {
		if ( $post->getPostId()->equals( $this->postId ) ) {
			$this->result = $post;
		}
	}

	public function isFinished() {
		return $this->result !== null;
	}

	public function getResult() {
		return $this->result;
	}
}
