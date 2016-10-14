<?php

$notificationTemplate = array(
	'category' => 'flow-discussion',
	'group' => 'other',
	'immediate' => false, // Default
);

$newTopicNotification = array(
	'presentation-model' => 'Flow\\NewTopicPresentationModel',
	'bundle' => array(
		'web' => true,
		'email' => true,
		'expandable' => true,
	),
	'icon' => 'flow-new-topic'
) + $notificationTemplate;

$descriptionEditedNotification = array(
	'presentation-model' => 'Flow\\HeaderEditedPresentationModel',
	'bundle' => array(
		'web' => true,
		'email' => true,
	),
	'icon' => 'flow-topic-renamed',
) + $notificationTemplate;

$postEditedNotification = array(
	'presentation-model' => 'Flow\\PostEditedPresentationModel',
	'bundle' => array(
		'web' => true,
		'email' => true,
	),
	'icon' => 'flow-post-edited',
) + $notificationTemplate;

$postReplyNotification = array(
	'presentation-model' => 'Flow\\PostReplyPresentationModel',
	'bundle' => array(
		'web' => true,
		'email' => true,
		'expandable' => true,
	),
	'icon' => 'chat',
) + $notificationTemplate;

$topicRenamedNotification = array(
	'presentation-model' => 'Flow\\TopicRenamedPresentationModel',
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
	'icon' => 'flow-topic-renamed',
) + $notificationTemplate;

$summaryEditedNotification = array(
	'presentation-model' => 'Flow\\SummaryEditedPresentationModel',
	'bundle' => array(
		'web' => true,
		'email' => true,
	),
	'primary-link' => array(
		'message' => 'flow-notification-link-text-view-topic',
		'destination' => 'flow-post'
	),
	'title-message' => 'notification-header-flow-summary-edited',
	'title-params' => array( 'subject', 'agent' ),
	'email-subject-message' => 'notification-email-subject-flow-summary-edited',
	'email-subject-params' => array( 'agent', 'subject' ),
	'email-body-batch-message' => 'notification-email-batch-body-flow-summary-edited',
	'email-body-batch-params' => array( 'agent', 'subject' ),
	'email-body-batch-bundle-message' => 'notification-email-batch-bundle-body-flow-summary-edited',
	'email-body-batch-bundle-params' => array( 'agent', 'subject', 'agent-other-display', 'agent-other-count' ),
	'icon' => 'flow-topic-renamed',
) + $notificationTemplate;

$topicResolvedNotification = array(
	'presentation-model' => 'Flow\\TopicResolvedPresentationModel',
	'primary-link' => array(
		'message' => 'flow-notification-link-text-view-topic',
		'destination' => 'flow-post'
	),
	'title-message' => 'notification-header-flow-topic-resolved',
	'title-params' => array( 'subject', 'agent' ),
	'email-subject-message' => 'notification-email-subject-flow-topic-resolved',
	'email-subject-params' => array( 'agent', 'subject' ),
	'email-body-batch-message' => 'notification-email-batch-body-flow-topic-resolved',
	'email-body-batch-params' => array( 'agent', 'subject' ),
	'icon' => 'flow-topic-resolved',
) + $notificationTemplate;

$notifications = array(
	'flow-new-topic' => array(
		'section' => 'message',
		'user-locators' => array(
			'EchoUserLocator::locateUsersWatchingTitle',
		),
		'user-filters' => array(
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
			'EchoUserLocator::locateTalkPageOwner',
		),
	) + $newTopicNotification,
	'flowusertalk-new-topic' => array(
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => array(
			'EchoUserLocator::locateTalkPageOwner',
		),
		'user-filters' => array(
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		),
	) + $newTopicNotification,
	'flow-post-reply' => array(
		'section' => 'message',
		'user-locators' => array(
			'Flow\\NotificationsUserLocator::locateUsersWatchingTopic',
		),
		'user-filters' => array(
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
			'EchoUserLocator::locateTalkPageOwner',
		),
	) + $postReplyNotification,
	'flowusertalk-post-reply' => array(
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => array(
			'EchoUserLocator::locateTalkPageOwner',
		),
		'user-filters' => array(
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		),
	) + $postReplyNotification,
	'flow-post-edited' => array(
		'section' => 'alert',
		'user-locators' => array(
			'Flow\\NotificationsUserLocator::locatePostAuthors',
		),
		'user-filters' => array(
			'EchoUserLocator::locateTalkPageOwner',
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		),
	) + $postEditedNotification,
	'flowusertalk-post-edited' => array(
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => array(
			'EchoUserLocator::locateTalkPageOwner',
		),
		'user-filters' => array(
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		),
	) + $postEditedNotification,
	'flow-topic-renamed' => array(
		'section' => 'message',
		'user-locators' => array(
			'Flow\\NotificationsUserLocator::locateUsersWatchingTopic',
		),
		'user-filters' => array(
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
			'EchoUserLocator::locateTalkPageOwner',
		),
	) + $topicRenamedNotification,
	'flowusertalk-topic-renamed' => array(
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => array(
			'EchoUserLocator::locateTalkPageOwner',
		),
		'user-filters' => array(
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		),
	) + $topicRenamedNotification,
	'flow-summary-edited' => array(
		'section' => 'message',
		'user-locators' => array(
			'Flow\\NotificationsUserLocator::locateUsersWatchingTopic',
		),
		'user-filters' => array(
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
			'EchoUserLocator::locateTalkPageOwner',
		),
	) + $summaryEditedNotification,
	'flowusertalk-summary-edited' => array(
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => array(
			'EchoUserLocator::locateTalkPageOwner',
		),
		'user-filters' => array(
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		),
	) + $summaryEditedNotification,
	'flow-description-edited' => array(
		'section' => 'message',
		'user-locators' => array(
			'EchoUserLocator::locateUsersWatchingTitle',
		),
		'user-filters' => array(
			'EchoUserLocator::locateTalkPageOwner',
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		),
	) + $descriptionEditedNotification,
	'flowusertalk-description-edited' => array(
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => array(
			'EchoUserLocator::locateTalkPageOwner',
		),
		'user-filters' => array(
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		),
	) + $descriptionEditedNotification,
	'flow-mention' => array(
		'category' => 'mention',
		'presentation-model' => 'Flow\\MentionPresentationModel',
		'section' => 'alert',
		'user-locators' => array(
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		),
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
		'icon' => 'mention',
	) + $notificationTemplate,
	'flow-enabled-on-talkpage' => array(
		'category' => 'system',
		'presentation-model' => 'Flow\\FlowEnabledOnTalkpagePresentationModel',
		'section' => 'message',
		'user-locators' => array(
			'EchoUserLocator::locateTalkPageOwner'
		),
		'primary-link' => array(
			'message' => 'flow-notification-link-text-enabled-on-talkpage',
			'destination' => 'title'
		),
		'title-message' => 'flow-notification-enabled-on-talkpage-title',
		'title-params' => array( 'agent', 'title' ),
		'email-subject-message' => 'flow-notification-enabled-on-talkpage-email-subject-message',
		'email-subject-params' => array( 'agent', 'title' ),
		'email-body-batch-message' => 'flow-notification-enabled-on-talkpage-email-batch-body',
		'email-body-batch-params' => array( 'agent', 'title' ),
		'icon' => 'chat',
	) + $notificationTemplate,
	'flow-topic-resolved' => array(
		'section' => 'message',
		'user-locators' => array(
			'Flow\\NotificationsUserLocator::locateUsersWatchingTopic',
		),
		'user-filters' => array(
			'EchoUserLocator::locateTalkPageOwner',
		),
	) + $topicResolvedNotification,
	'flowusertalk-topic-resolved' => array(
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => array(
			'EchoUserLocator::locateTalkPageOwner',
		),
	) + $topicResolvedNotification,
);

return $notifications;
