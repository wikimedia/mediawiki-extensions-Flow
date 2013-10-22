<?php

use Flow\View\History;
use Flow\Model\PostRevision;
use Flow\Block\Block;
use Flow\UrlGenerator;

/**
 * $wgFlowHistoryActions contains information on all logged change types. All
 * keys are the stored change types. The corresponding values are arrays, which
 * should have following info:
 * * i18n-message: the i18n message key for this change type
 * * i18n-params: array of i18n parameters for the provided message (see
 *   HistoryRecord::buildMessage phpdoc for more details)
 * * class: classname to be added to the list-item for this changetype
 * * bundle: array with, again, all of the above information if multiple types
 *   should be bundled (then the bundle i18n & class will be used to generate
 *   the list-item & clicking on it will reveal the individual history entries)
 */
$wgFlowHistoryActions = array(
	'edit-post' => array(
		'i18n-message' => 'flow-rev-message-edit-post',
		'i18n-params' => array(
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getUserText( $user );
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				$data = array( $block->getName() . '[postId]' => $revision->getPostId()->getHex() );
				return $urlGenerator->generateUrl( $block->getWorkflowId(), 'view', $data );
			},
		),
		'class' => 'flow-history-edit-post',
	),
	'reply' => array(
		'i18n-message' => 'flow-rev-message-reply',
		'i18n-params' => array(
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getUserText( $user );
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				$data = array( $block->getName() . '[postId]' => $revision->getPostId()->getHex() );
				return $urlGenerator->generateUrl( $block->getWorkflowId(), 'view', $data );
			},
		),
		'class' => 'flow-history-reply',
		'bundle' => array(
			'i18n-message' => 'flow-rev-message-reply-bundle',
			'i18n-params' => array(
				function ( array $revisions, UrlGenerator $urlGenerator, User $user, Block $block ) {
					return array( 'num' => count( $revisions ) );
				}
			),
			'class' => 'flow-history-bundle',
		),
	),
	'new-post' => array(
		'i18n-message' => 'flow-rev-message-new-post',
		'i18n-params' => array(
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getUserText( $user );
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $urlGenerator->generateUrl( $revision->getPostId() );
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getContent( $user, 'wikitext' );
			},
		),
		'class' => 'flow-history-new-post',
	),
	'edit-title' => array(
		'i18n-message' => 'flow-rev-message-edit-title',
		'i18n-params' => array(
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getUserText( $user );
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $urlGenerator->generateUrl( $revision->getPostId() );
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getContent( $user, 'wikitext' );
			},
			// @todo: find previous revision & return title of that revision
		),
		'class' => 'flow-history-edit-title',
	),
	'create-header' => array(
		'i18n-message' => 'flow-rev-message-create-header',
		// @todo: AFAIK, we don't have a board history yet, where this will be surfaced
		'class' => 'flow-history-create-header',
	),
	'edit-header' => array(
		'i18n-message' => 'flow-rev-message-edit-header',
		// @todo: AFAIK, we don't have a board history yet, where this will be surfaced
		'class' => 'flow-history-edit-header',
	),
	'restore-post' => array(
		'i18n-message' => 'flow-rev-message-restored-post',
		'i18n-params' => array(
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getModeratedByUserText();
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getUserText( $user );
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				$data = array( $block->getName() . '[postId]' => $revision->getPostId()->getHex() );
				return $urlGenerator->generateUrl( $block->getWorkflowId(), 'view', $data );
			},
		),
		'class' => 'flow-history-restored-post',
	),
	'hide-post' => array(
		'i18n-message' => 'flow-rev-message-hid-post',
		'i18n-params' => array(
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getModeratedByUserText();
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getUserText( $user );
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				$data = array( $block->getName() . '[postId]' => $revision->getPostId()->getHex() );
				return $urlGenerator->generateUrl( $block->getWorkflowId(), 'view', $data );
			},
		),
		'class' => 'flow-history-hid-post',
	),
	'delete-post' => array(
		'i18n-message' => 'flow-rev-message-deleted-post',
		'i18n-params' => array(
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getModeratedByUserText();
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getUserText( $user );
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				$data = array( $block->getName() . '[postId]' => $revision->getPostId()->getHex() );
				return $urlGenerator->generateUrl( $block->getWorkflowId(), 'view', $data );
			},
		),
		'class' => 'flow-history-deleted-post',
	),
	'censor-post' => array(
		'i18n-message' => 'flow-rev-message-censored-post',
		'i18n-params' => array(
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getModeratedByUserText();
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getUserText( $user );
			},
			function ( PostRevision $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				$data = array( $block->getName() . '[postId]' => $revision->getPostId()->getHex() );
				return $urlGenerator->generateUrl( $block->getWorkflowId(), 'view', $data );
			},
		),
		'class' => 'flow-history-censored-post',
	),
);
