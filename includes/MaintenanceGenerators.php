<?php

namespace Flow\Maintenance;

require_once __DIR__ . '/../maintenance/FlowFixLog.php';
require_once __DIR__ . '/../maintenance/FlowExternalStoreMoveCluster.php';

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Exception\InvalidDataException;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Collection\PostCollection;
use Flow\Maintenance\LogRowUpdateGenerator;
use Flow\Repository\RootPostLoader;

use ExternalStoreMoveCluster;
use FlowFixLog;

use BatchRowIterator;
use BatchRowUpdate;
use BatchRowWriter;
use RowUpdateGenerator;
use Title;

/**
 * Used in FlowExternalStoreMoveCluster.php
 */
class ExternalStoreUpdateGenerator implements RowUpdateGenerator {
    /**
     * @var ExternalStoreMoveCluster
     */
    protected $script;

    /**
     * @var array
     */
    protected $stores = array();

    /**
     * @var array
     */
    protected $schema = array();

    /**
     * @param ExternalStoreMoveCluster $script
     * @param array $stores
     * @param array $schema
     */
    public function __construct( ExternalStoreMoveCluster $script, array $stores, array $schema ) {
        $this->script = $script;
        $this->stores = $stores;
        $this->schema = $schema;
    }

    /**
     * @param stdClass $row
     * @return array
     */
    public function update( $row ) {
        $url = $row->{$this->schema['content']};
        $flags = explode( ',', $row->{$this->schema['flags']} );

        try {
            $content = $this->read( $url, $flags );
            $data = $this->write( $content, $flags );
        } catch ( \Exception $e ) {
            // something went wrong, just output the error & don't update!
            $this->script->error( $e->getMessage(). "\n" );
            return array();
        }

        return array(
            $this->schema['content'] => $data['content'],
            $this->schema['flags'] => implode( ',', $data['flags'] ),
        );
    }

    /**
     * @param string $url
     * @param array $flags
     * @return string
     * @throws MWException
     */
    protected function read( $url, array $flags = array() ) {
        $content = ExternalStore::fetchFromURL( $url );
        if ( $content === false ) {
            throw new MWException( "Failed to fetch content from URL: $url" );
        }

        $content = \Revision::decompressRevisionText( $content, $flags );
        if ( $content === false ) {
            throw new MWException( "Failed to decompress content from URL: $url" );
        }

        return $content;
    }

    /**
     * @param string $content
     * @param array $flags
     * @return array New ExternalStore data in the form of ['content' => ..., 'flags' => array( ... )]
     * @throws MWException
     */
    protected function write( $content, array $flags = array() ) {
        // external, utf-8 & gzip flags are no longer valid at this point
        $oldFlags = array_diff( $flags, array( 'external', 'utf-8', 'gzip' ) );

        if ( $content === '' ) {
            // don't store empty content elsewhere
            return array(
                'content' => $content,
                'flags' => $oldFlags,
            );
        }

        // re-compress (if $wgCompressRevisions is enabled) the content & set flags accordingly
        $flags = array_filter( explode( ',', \Revision::compressRevisionText( $content ) ) );

        // ExternalStore::insertWithFallback expects stores with protocol
        $stores = array();
        foreach ( $this->stores as $store ) {
            $stores[] = 'DB://' . $store;
        }
        $url = ExternalStore::insertWithFallback( $stores, $content );
        if ( $url === false ) {
            throw new MWException( 'Failed to write content to stores ' . json_encode( $stores ) );
        }

        // add flag indicating content is external again, and restore unrelated flags
        $flags[] = 'external';
        $flags = array_merge( $flags, $oldFlags );

        return array(
            'content' => $url,
            'flags' => array_unique( $flags ),
        );
    }
}

/**
 * Used in FlowFixLog.php
 */
class LogRowUpdateGenerator implements RowUpdateGenerator {
	/**
	 * @var FlowFixLog
	 */
	protected $maintenance;

	/**
	 * @param FlowFixLog $maintenance
	 */
	public function __construct( FlowFixLog $maintenance ) {
		$this->maintenance = $maintenance;
	}

	public function update( $row ) {
		$updates = array();

		$params = unserialize( $row->log_params );
		if ( !$params ) {
			$this->maintenance->error( "Failed to unserialize log_params for log_id {$row->log_id}" );
			return array();
		}

		$topic = false;
		$post = false;
		if ( isset( $params['topicId'] ) ) {
			$topic = $this->loadTopic( UUID::create( $params['topicId'] ) );
		}
		if ( isset( $params['postId'] ) ) {
			$post = $this->loadPost( UUID::create( $params['postId'] ) );
			$topic = $topic ?: $post->getRoot();
		}

		if ( !$topic ) {
			$this->maintenance->error( "Missing topicId & postId for log_id {$row->log_id}" );
			return array();
		}

		try {
			// log_namespace & log_title used to be board, should be topic
			$updates['log_namespace'] = $topic->getTitle()->getNamespace();
			$updates['log_title'] = $topic->getTitle()->getDBkey();
		} catch ( \Exception $e ) {
			$this->maintenance->error( "Couldn't load Title for log_id {$row->log_id}" );
			$updates = array();
		}

		if ( isset( $params['postId'] ) && $post ) {
			// posts used to save revision id instead of post id, let's make
			// sure it's actually the post id that's being saved!...
			$params['postId'] = $post->getId();
		}

		if ( !isset( $params['topicId'] ) ) {
			// posts didn't use to also store topicId, but we'll be using it to
			// enrich log entries' output - might as well store it right away
			$params['topicId'] = $topic->getId();
		}

		// we used to save (serialized) UUID objects; now we just save the
		// alphanumeric representation
		foreach ( $params as $key => $value ) {
			$params[$key] = $value instanceof UUID ? $value->getAlphadecimal() : $value;
		}

		// re-serialize params (UUID used to serialize more verbose; might
		// as well shrink that down now that we're updating anyway...)
		$updates['log_params'] = serialize( $params );

		return $updates;
	}

	/**
	 * @param UUID $topicId
	 * @return PostCollection
	 */
	protected function loadTopic( UUID $topicId ) {
		return PostCollection::newFromId( $topicId );
	}

	/**
	 * @param UUID $postId
	 * @return PostCollection
	 */
	protected function loadPost( UUID $postId ) {
		try {
			$collection = PostCollection::newFromId( $postId );

			// validate collection by attempting to fetch latest revision - if
			// this fails (likely will for old data), catch will be invoked
			$collection->getLastRevision();
			return $collection;
		} catch ( InvalidDataException $e ) {
			// posts used to mistakenly store revision ID instead of post ID

			/** @var ManagerGroup $storage */
			$storage = Container::get( 'storage' );
			$result = $storage->find(
				'PostRevision',
				array( 'rev_id' => $postId ),
				array( 'LIMIT' => 1 )
			);

			if ( $result ) {
				/** @var PostRevision $revision */
				$revision = reset( $result );

				// now build collection from real post ID
				return $this->loadPost( $revision->getPostId() );
			}
		}

		return false;
	}
}


/**
 * Used in FlowUpdateWorkflowPageId.php
 * Looks at rows from the flow_workflow table and returns an update
 * for the workflow_page_id field if necessary.
 */
class WorkflowPageIdUpdateGenerator implements RowUpdateGenerator {
	/**
	 * @var Language|StubUserLang
	 */
	protected $lang;
	protected $fixedCount = 0;
	protected $failed = array();

	/**
	 * @param Language|StubUserLang $lang
	 */
	public function __construct( $lang ) {
		$this->lang = $lang;
	}

	public function update( $row ) {
		$title = Title::makeTitleSafe( $row->workflow_namespace, $row->workflow_title_text );
		if ( $title === null ) {
			throw new Exception( sprintf(
				'Could not create title for %s at %s:%s',
				UUID::create( $row->workflow_id )->getAlphadecimal(),
				$this->lang->getNsText( $row->workflow_namespace ) ?: $row->workflow_namespace,
				$row->workflow_title_text
			) );
		}

		// at some point, we failed to create page entries for new workflows: only
		// create that page if the workflow was stored with a 0 page id (otherwise,
		// we could mistake the $title for a deleted page)
		if ( $row->workflow_page_id === 0 && $title->getArticleID() === 0 ) {
			// build workflow object (yes, loading them piecemeal is suboptimal, but
			// this is just a one-time script; considering the alternative is
			// creating a derivative BatchRowIterator that returns workflows,
			// it doesn't really matter)
			$storage = Container::get( 'storage' );
			$workflow = $storage->get( 'Workflow', UUID::create( $row->workflow_id ) );

			try {
				/** @var OccupationController $occupationController */
				$occupationController = Container::get( 'occupation_controller' );
				$occupationController->allowCreation( $title, $occupationController->getTalkpageManager() );
				$occupationController->ensureFlowRevision( new Article( $title ), $workflow );

				// force article id to be refetched from db
				$title->getArticleID( Title::GAID_FOR_UPDATE );
			} catch ( \Exception $e ) {
				// catch all exception to keep going with the rest we want to
				// iterate over, we'll report on the failed entries at the end
				$this->failed[] = $row;
			}
		}

		// re-associate the workflow with the correct page; only if a page exists
		if ( $title->getArticleID() !== 0 && $title->getArticleID() !== (int) $row->workflow_page_id ) {
			// This makes the assumption the page has not moved or been deleted?
			++$this->fixedCount;
			return array(
				'workflow_page_id' => $title->getArticleID(),
			);
		} elseif ( !$row->workflow_page_id ) {
			// No id exists for this workflow?
			$this->failed[] = $row;
		}

		return array();
	}

	public function report() {
		return "Updated {$this->fixedCount}  workflows\nFailed: " . count( $this->failed ) . "\n\n" . print_r( $this->failed, true );
	}
}

/**
 * Used in FlowFixWorkflowLastUpdateTimestamp.php
 */
class UpdateWorkflowLastUpdateTimestampGenerator implements RowUpdateGenerator {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var RootPostLoader
	 */
	protected $rootPostLoader;

	/**
	 * @param ManagerGroup $storage
	 * @param RootPostLoader $rootPostLoader
	 */
	public function __construct( ManagerGroup $storage, RootPostLoader $rootPostLoader ) {
		$this->storage = $storage;
		$this->rootPostLoader = $rootPostLoader;
	}

	/**
	 * @param stdClass $row
	 * @return array
	 * @throws TimestampException
	 * @throws \Flow\Exception\FlowException
	 * @throws \Flow\Exception\InvalidInputException
	 */
	public function update( $row ) {
		$uuid = UUID::create( $row->workflow_id );

		switch ( $row->workflow_type ) {
			case 'discussion':
				$revision = $this->storage->get( 'Header', $uuid );
				break;

			case 'topic':
				// fetch topic (has same id as workflow) via RootPostLoader so
				// all children are populated
				$revision = $this->rootPostLoader->get( $uuid );
				break;

			default:
				throw new FlowException( 'Unknown workflow type: ' . $row->workflow_type );
		}

		if ( !$revision) {
			return array();
		}

		$timestamp = $this->getUpdateTimestamp( $revision )->getTimestamp( TS_MW );
		if ( $timestamp === $row->workflow_last_update_timestamp ) {
			// correct update timestamp already, nothing to update
			return array();
		}

		return array( 'workflow_last_update_timestamp' => $timestamp );
	}

	/**
	 * @param AbstractRevision $revision
	 * @return MWTimestamp
	 * @throws Exception
	 * @throws TimestampException
	 * @throws \Flow\Exception\DataModelException
	 */
	protected function getUpdateTimestamp( AbstractRevision $revision ) {
		$timestamp = $revision->getRevisionId()->getTimestampObj();

		if ( !$revision instanceof PostRevision ) {
			return $timestamp;
		}

		foreach ( $revision->getChildren() as $child ) {
			// go recursive, find timestamp of most recent child post
			$comparison = $this->getUpdateTimestamp( $child );
			$diff = $comparison->diff( $timestamp );

			// invert will be 1 if the diff is a negative time period from
			// child timestamp ($comparison) to $timestamp, which means that
			// $comparison is more recent than our current $timestamp
			if ( $diff->invert ) {
				$timestamp = $comparison;
			}
		}

		return $timestamp;
	}
}

/**
 * Used in FlowFixWorkflowLastUpdateTimestamp.php
 */
class UpdateWorkflowLastUpdateTimestampWriter extends BatchRowWriter {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @param ManagerGroup $storage
	 * @param bool $clusterName
	 */
	public function __construct( ManagerGroup $storage, $clusterName = false ) {
		$this->storage = $storage;
		$this->clusterName = $clusterName;
	}

	/**
	 * Overwriting default writer because I want to use Flow storage methods so
	 * the updates also affect cache, not just DB.
	 *
	 * @param array[] $updates
	 */
	public function write( array $updates ) {
		/*
		 * from:
		 * array(
		 *     'primaryKey' => array( 'workflow_id' => $id ),
		 *     'updates' => array( 'workflow_last_update_timestamp' => $timestamp ),
		 * )
		 * to:
		 * array( $id => $timestamp );
		 */
		$timestamps = array_combine(
			$this->arrayColumn( $this->arrayColumn( $updates, 'primaryKey' ), 'workflow_id' ),
			$this->arrayColumn( $this->arrayColumn( $updates, 'changes' ), 'workflow_last_update_timestamp' )
		);

		/** @var UUID[] $uuids */
		$uuids = array_map( array( 'Flow\\Model\\UUID', 'create' ), array_keys( $timestamps ) );

		/** @var Workflow[] $workflows */
		$workflows = $this->storage->getMulti( 'Workflow', $uuids );
		foreach ( $workflows as $workflow ) {
			$timestamp = $timestamps[$workflow->getId()->getBinary()->__toString()];
			$workflow->updateLastUpdated( UUID::getComparisonUUID( $timestamp ) );
		}

		$this->storage->multiPut( $workflows );

		// prevent memory from filling up
		$this->storage->clear();

		wfWaitForSlaves( false, false, $this->clusterName );
	}

	/**
	 * PHP<5.5-compatible array_column alternative.
	 *
	 * @param array $array
	 * @param string $key
	 * @return array
	 */
	protected function arrayColumn( array $array, $key ) {
		return array_map( function( $item ) use ( $key ) {
			return $item[$key];
		}, $array );
	}
}
