<?php

namespace Flow\Data;

use Flow\Repository\TreeRepository;

class IndexFactory {

	/**
	 * @var BufferedCache
	 */
	protected $cache;

	/**
	 * @var string
	 */
	protected $cacheVersion;

	/**
	 * @var ObjectStorage
	 */
	protected $storage;

	public function __construct( BufferedCache $cache, $cacheVersion ) {
		$this->cache = $cache;
		$this->cacheVersion = $cacheVersion;
	}

	public function withStorage( ObjectStorage $storage ) {
		$this->storage = $storage;

		return $this;
	}

	public function createUniqueFeatureIndex( $prefix, array $columns ) {
		return new Index\UniqueFeatureIndex(
			$this->cache,
			$this->storage,
			$this->cacheVersion,
			$prefix,
			$columns
		);
	}

	public function createTopKIndex( $prefix, array $columns, array $options ) {
		return new Index\TopKIndex(
			$this->cache,
			$this->storage,
			$this->cacheVersion,
			$prefix,
			$columns,
			$options
		);
	}

	public function createTopicHistoryIndex( TreeRepository $treeRepo, $prefix, array $columns, array $options ) {
		return new Index\TopicHistoryIndex(
			$this->cache,
			$this->storage,
			$this->cacheVersion,
			$treeRepo,
			$prefix,
			$columns,
			$options
		);
	}

	public function createBoardHistoryIndex( $prefix, array $columns, array $options ) {
		return new Index\BoardHistoryIndex(
			$this->cache,
			$this->storage,
			$this->cacheVersion,
			$prefix,
			$columns,
			$options
		);
	}
}
