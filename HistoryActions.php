<?php

use Flow\View\History;
use Flow\Model\PostRevision;
use Flow\Model\Header;
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
	'flow-edit-header' => array(
		'i18n-message' => 'flow-rev-message-edit-header',
		'i18n-params' => array(
			function ( Header $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getUserText( $user );
			},
		),
		'class' => 'flow-rev-message-edit-header',
	),
	'flow-create-header' => array(
		'i18n-message' => 'flow-rev-message-create-header',
		'i18n-params' => array(
			function ( Header $revision, UrlGenerator $urlGenerator, User $user, Block $block ) {
				return $revision->getUserText( $user );
			},
		),
		'class' => 'flow-rev-message-create-header',
	),
	'flow-rev-message-edit-post' => array(
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
		'class' => 'flow-rev-message-edit-post',
	),
	'flow-rev-message-reply' => array(
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
		'class' => 'flow-rev-message-reply',
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
	'flow-rev-message-new-post' => array(
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
		'class' => 'flow-rev-message-new-post',
	),
	'flow-rev-message-edit-title' => array(
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
		'class' => 'flow-rev-message-edit-title',
	),
	'flow-rev-message-restored-post' => array(
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
		'class' => 'flow-rev-message-restored-post',
	),
	'flow-rev-message-hid-post' => array(
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
		'class' => 'flow-rev-message-hid-post',
	),
	'flow-rev-message-deleted-post' => array(
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
		'class' => 'flow-rev-message-deleted-post',
	),
	'flow-rev-message-censored-post' => array(
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
		'class' => 'flow-rev-message-censored-post',
	),
);
