<?php

namespace Flow\Import\LiquidThreadsApi;

use Iterator;

class ReplyIterator implements Iterator {
	protected ImportPost $post;
	protected array $threadReplies;
	protected int $replyIndex;
	protected ?ImportPost $current;

	public function __construct( ImportPost $post ) {
		$this->post = $post;
		$this->replyIndex = 0;

		$apiResponse = $post->getApiResponse();
		$this->threadReplies = array_values( $apiResponse['replies'] );
	}

	/**
	 * @return ImportPost|null
	 */
	public function current(): ?ImportPost {
		return $this->current;
	}

	/**
	 * @return int
	 */
	public function key(): int {
		return $this->replyIndex;
	}

	public function next(): void {
		while ( ++$this->replyIndex < count( $this->threadReplies ) ) {
			try {
				$replyId = $this->threadReplies[$this->replyIndex]['id'];
				$this->current = $this->post->getSource()->getPost( $replyId );

				return;
			} catch ( ApiNotFoundException $e ) {
				// while loop fall-through handles our error case
			}
		}

		// Nothing found, set current to null
		$this->current = null;
	}

	public function rewind(): void {
		$this->replyIndex = -1;
		$this->next();
	}

	public function valid(): bool {
		return $this->current !== null;
	}
}
