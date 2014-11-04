<?php

namespace Flow\Formatter;

use Flow\Data\Pager\PagerPage;
use Flow\Model\Anchor;
use Flow\Model\Workflow;

class BaseTopicListFormatter {
	/**
	 * Builds the results for an empty topic.
	 *
	 * @param Workflow $workflow Workflow for topic list
	 * @return array Associative array with the the result
	 */
	public function buildEmptyResult( Workflow $workflow ) {
		return array(
			'type' => 'topiclist',
			'roots' => array(),
			'posts' => array(),
			'revisions' => array(),
			'links' => array( 'pagination' => array() ),
		);
	}

	/**
	 * @param Workflow $workflow Topic list workflow
	 * @param array $links pagination link data
	 *
	 * @return array link structure
	 */
	protected function buildPaginationLinks( Workflow $workflow, array $links ) {
		$res = array();
		$title = $workflow->getArticleTitle();
		foreach ( $links as $key => $options ) {
			// prefix all options with topiclist_
			$realOptions = array();
			foreach ( $options as $k => $v ) {
				$realOptions["topiclist_$k"] = $v;
			}
			$res[$key] = new Anchor(
				$key, // @todo i18n
				$title,
				$realOptions
			);
		}

		return $res;
	}
}
