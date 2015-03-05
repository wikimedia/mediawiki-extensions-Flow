<?php

$notificationTemplate = array(
	'category' => 'flow-discussion',
	'group' => 'other',
	'section' => 'message',
	'formatter-class' => 'Flow\NotificationFormatter',
	'icon' => 'flow-discussion',
	'use-jobqueue' => true,
);

$notifications = array(
	'flow-new-topic' => array(
		'formatter-class' => 'Flow\NewTopicFormatter',
		'user-locators' => array(
			'EchoUserLocator::locateUsersWatchingTitle',
			'EchoUserLocator::locateTalkPageOwner'
		),
		'primary-link' => array(
			'message' => 'flow-notification-link-text-view-topic',
			'destination' => 'flow-new-topics'
		),
		'title-message' => 'flow-notification-newtopic',
		'title-params' => array( 'agent', 'flow-title', 'title', 'subject', 'topic-permalink' ),
		'bundle' => array(
			'web' => true,
			'email' => true,
		),
		'bundle-type' => 'event',
		'bundle-message' => 'flow-notification-newtopic-bundle',
		'bundle-params' => array( 'event-count', 'title', 'new-topics-permalink' ),
		'email-subject-message' => 'flow-notification-newtopic-email-subject',
		'email-subject-params' => array( 'agent', 'title' ),
		'email-body-batch-message' => 'flow-notification-newtopic-email-batch-body',
		'email-body-batch-params' => array( 'agent', 'subject', 'title' ),
		'email-body-batch-bundle-message' => 'flow-notification-newtopic-bundle',
		'email-body-batch-bundle-params' => array( 'event-count', 'title' ),
	) + $notificationTemplate,
	'flow-post-reply' => array(
		'user-locators' => array(
			'Flow\\NotificationsUserLocator::locateUsersWatchingTopic',
		),
		'primary-link' => array(
			'message' => 'flow-notification-link-text-view-post',
			'destination' => 'flow-post'
		),
		'title-message' => 'flow-notification-reply',
		'title-params' => array( 'agent', 'subject', 'flow-title', 'title', 'post-permalink' ),
		'bundle' => array(
			'web' => true,
			'email' => true,
		),
		'bundle-message' => 'flow-notification-reply-bundle',
		'bundle-params' => array( 'agent', 'subject', 'title', 'post-permalink', 'agent-other-display', 'agent-other-count' ),
		'email-subject-message' => 'flow-notification-reply-email-subject',
		'email-subject-params' => array( 'agent', 'subject', 'title' ),
		'email-body-batch-message' => 'flow-notification-reply-email-batch-body',
		'email-body-batch-params' => array( 'agent', 'subject', 'title' ),
		'email-body-batch-bundle-message' => 'flow-notification-reply-email-batch-bundle-body',
		'email-body-batch-bundle-params' => array( 'agent', 'subject', 'title', 'agent-other-display', 'agent-other-count' ),
	) + $notificationTemplate,
	'flow-post-edited' => array(
		'primary-link' => array(
			'message' => 'flow-notification-link-text-view-post',
			'destination' => 'flow-post'
		),
		'title-message' => 'flow-notification-edit',
		'title-params' => array( 'agent', 'subject', 'flow-title', 'title', 'post-permalink', 'topic-permalink' ),
		'bundle' => array(
			'web' => true,
			'email' => true,
		),
		'bundle-message' => 'flow-notification-edit-bundle',
		'bundle-params' => array( 'agent', 'subject', 'title', 'post-permalink', 'agent-other-display', 'agent-other-count' ),
		'email-subject-message' => 'flow-notification-edit-email-subject',
		'email-subject-params' => array( 'agent' ),
		'email-body-batch-message' => 'flow-notification-edit-email-batch-body',
		'email-body-batch-params' => array( 'agent', 'subject', 'title' ),
		'email-body-batch-bundle-message' => 'flow-notification-edit-email-batch-bundle-body',
		'email-body-batch-bundle-params' => array( 'agent', 'subject', 'title', 'agent-other-display', 'agent-other-count' ),
	) + $notificationTemplate,
	'flow-topic-renamed' => array(
		'primary-link' => array(
			'message' => 'flow-notification-link-text-view-post',
			'destination' => 'flow-post'
		),
		'title-message' => 'flow-notification-rename',
		'title-params' => array( 'agent', 'topic-permalink', 'old-subject', 'new-subject', 'flow-title', 'title' ),
		'email-subject-message' => 'flow-notification-rename-email-subject',
		'email-subject-params' => array( 'agent' ),
		'email-body-batch-message' => 'flow-notification-rename-email-batch-body',
		'email-body-batch-params' => array( 'agent', 'old-subject', 'new-subject', 'title' ),
	) + $notificationTemplate,
	'flow-mention' => array(
		'primary-link' => array(
			'message' => 'notification-link-text-view-mention',
			'destination' => 'flow-post'
		),
		'title-message' => 'flow-notification-mention',
		'title-params' => array( 'agent', 'post-permalink', 'subject', 'title', 'user' ),
		'email-subject-message' => 'flow-notification-mention-email-subject',
		'email-subject-params' => array( 'agent', 'flow-title', 'user' ),
		'email-body-batch-message' => 'flow-notification-mention-email-batch-body',
		'email-body-batch-params' => array( 'agent', 'subject', 'title', 'user' ),
	) + $notificationTemplate,
);
