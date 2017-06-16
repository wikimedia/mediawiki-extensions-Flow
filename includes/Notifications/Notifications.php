<?php

$notificationTemplate = [
	'category' => 'flow-discussion',
	'group' => 'other',
	'immediate' => false, // Default
];

$newTopicNotification = [
	'presentation-model' => 'Flow\\NewTopicPresentationModel',
	'bundle' => [
		'web' => true,
		'email' => true,
		'expandable' => true,
	],
	'icon' => 'flow-new-topic'
] + $notificationTemplate;

$descriptionEditedNotification = [
	'presentation-model' => 'Flow\\HeaderEditedPresentationModel',
	'bundle' => [
		'web' => true,
		'email' => true,
	],
	'icon' => 'flow-topic-renamed',
] + $notificationTemplate;

$postEditedNotification = [
	'presentation-model' => 'Flow\\PostEditedPresentationModel',
	'bundle' => [
		'web' => true,
		'email' => true,
	],
	'icon' => 'flow-post-edited',
] + $notificationTemplate;

$postReplyNotification = [
	'presentation-model' => 'Flow\\PostReplyPresentationModel',
	'bundle' => [
		'web' => true,
		'email' => true,
		'expandable' => true,
	],
	'icon' => 'chat',
] + $notificationTemplate;

$topicRenamedNotification = [
	'presentation-model' => 'Flow\\TopicRenamedPresentationModel',
	'primary-link' => [
		'message' => 'flow-notification-link-text-view-post',
		'destination' => 'flow-post'
	],
	'title-message' => 'flow-notification-rename',
	'title-params' => [ 'agent', 'topic-permalink', 'old-subject', 'new-subject', 'flow-title', 'title' ],
	'email-subject-message' => 'flow-notification-rename-email-subject',
	'email-subject-params' => [ 'agent' ],
	'email-body-batch-message' => 'flow-notification-rename-email-batch-body',
	'email-body-batch-params' => [ 'agent', 'old-subject', 'new-subject', 'title' ],
	'icon' => 'flow-topic-renamed',
] + $notificationTemplate;

$summaryEditedNotification = [
	'presentation-model' => 'Flow\\SummaryEditedPresentationModel',
	'bundle' => [
		'web' => true,
		'email' => true,
	],
	'primary-link' => [
		'message' => 'flow-notification-link-text-view-topic',
		'destination' => 'flow-post'
	],
	'title-message' => 'notification-header-flow-summary-edited',
	'title-params' => [ 'subject', 'agent' ],
	'email-subject-message' => 'notification-email-subject-flow-summary-edited',
	'email-subject-params' => [ 'agent', 'subject' ],
	'email-body-batch-message' => 'notification-email-batch-body-flow-summary-edited',
	'email-body-batch-params' => [ 'agent', 'subject' ],
	'email-body-batch-bundle-message' => 'notification-email-batch-bundle-body-flow-summary-edited',
	'email-body-batch-bundle-params' => [ 'agent', 'subject', 'agent-other-display', 'agent-other-count' ],
	'icon' => 'flow-topic-renamed',
] + $notificationTemplate;

$topicResolvedNotification = [
	'presentation-model' => 'Flow\\TopicResolvedPresentationModel',
	'primary-link' => [
		'message' => 'flow-notification-link-text-view-topic',
		'destination' => 'flow-post'
	],
	'title-message' => 'notification-header-flow-topic-resolved',
	'title-params' => [ 'subject', 'agent' ],
	'email-subject-message' => 'notification-email-subject-flow-topic-resolved',
	'email-subject-params' => [ 'agent', 'subject' ],
	'email-body-batch-message' => 'notification-email-batch-body-flow-topic-resolved',
	'email-body-batch-params' => [ 'agent', 'subject' ],
	'icon' => 'flow-topic-resolved',
] + $notificationTemplate;

$notifications = [
	'flow-new-topic' => [
		'section' => 'message',
		'user-locators' => [
			'EchoUserLocator::locateUsersWatchingTitle',
		],
		'user-filters' => [
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
			'EchoUserLocator::locateTalkPageOwner',
		],
	] + $newTopicNotification,
	'flowusertalk-new-topic' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => [
			'EchoUserLocator::locateTalkPageOwner',
		],
		'user-filters' => [
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		],
	] + $newTopicNotification,
	'flow-post-reply' => [
		'section' => 'message',
		'user-locators' => [
			'Flow\\NotificationsUserLocator::locateUsersWatchingTopic',
		],
		'user-filters' => [
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
			'EchoUserLocator::locateTalkPageOwner',
		],
	] + $postReplyNotification,
	'flowusertalk-post-reply' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => [
			'EchoUserLocator::locateTalkPageOwner',
		],
		'user-filters' => [
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		],
	] + $postReplyNotification,
	'flow-post-edited' => [
		'section' => 'alert',
		'user-locators' => [
			'Flow\\NotificationsUserLocator::locatePostAuthors',
		],
		'user-filters' => [
			'EchoUserLocator::locateTalkPageOwner',
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		],
	] + $postEditedNotification,
	'flowusertalk-post-edited' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => [
			'EchoUserLocator::locateTalkPageOwner',
		],
		'user-filters' => [
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		],
	] + $postEditedNotification,
	'flow-topic-renamed' => [
		'section' => 'message',
		'user-locators' => [
			'Flow\\NotificationsUserLocator::locateUsersWatchingTopic',
		],
		'user-filters' => [
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
			'EchoUserLocator::locateTalkPageOwner',
		],
	] + $topicRenamedNotification,
	'flowusertalk-topic-renamed' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => [
			'EchoUserLocator::locateTalkPageOwner',
		],
		'user-filters' => [
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		],
	] + $topicRenamedNotification,
	'flow-summary-edited' => [
		'section' => 'message',
		'user-locators' => [
			'Flow\\NotificationsUserLocator::locateUsersWatchingTopic',
		],
		'user-filters' => [
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
			'EchoUserLocator::locateTalkPageOwner',
		],
	] + $summaryEditedNotification,
	'flowusertalk-summary-edited' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => [
			'EchoUserLocator::locateTalkPageOwner',
		],
		'user-filters' => [
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		],
	] + $summaryEditedNotification,
	'flow-description-edited' => [
		'section' => 'message',
		'user-locators' => [
			'EchoUserLocator::locateUsersWatchingTitle',
		],
		'user-filters' => [
			'EchoUserLocator::locateTalkPageOwner',
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		],
	] + $descriptionEditedNotification,
	'flowusertalk-description-edited' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => [
			'EchoUserLocator::locateTalkPageOwner',
		],
		'user-filters' => [
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		],
	] + $descriptionEditedNotification,
	'flow-mention' => [
		'category' => 'mention',
		'presentation-model' => 'Flow\\MentionPresentationModel',
		'section' => 'alert',
		'user-locators' => [
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		],
		'primary-link' => [
			'message' => 'notification-link-text-view-mention',
			'destination' => 'flow-post'
		],
		'title-message' => 'flow-notification-mention',
		'title-params' => [ 'agent', 'post-permalink', 'subject', 'title', 'user' ],
		'email-subject-message' => 'flow-notification-mention-email-subject',
		'email-subject-params' => [ 'agent', 'flow-title', 'user' ],
		'email-body-batch-message' => 'flow-notification-mention-email-batch-body',
		'email-body-batch-params' => [ 'agent', 'subject', 'title', 'user' ],
		'icon' => 'mention',
	] + $notificationTemplate,
	'flow-enabled-on-talkpage' => [
		'category' => 'system',
		'presentation-model' => 'Flow\\FlowEnabledOnTalkpagePresentationModel',
		'section' => 'message',
		'user-locators' => [
			'EchoUserLocator::locateTalkPageOwner'
		],
		'primary-link' => [
			'message' => 'flow-notification-link-text-enabled-on-talkpage',
			'destination' => 'title'
		],
		'title-message' => 'flow-notification-enabled-on-talkpage-title',
		'title-params' => [ 'agent', 'title' ],
		'email-subject-message' => 'flow-notification-enabled-on-talkpage-email-subject-message',
		'email-subject-params' => [ 'agent', 'title' ],
		'email-body-batch-message' => 'flow-notification-enabled-on-talkpage-email-batch-body',
		'email-body-batch-params' => [ 'agent', 'title' ],
		'icon' => 'chat',
	] + $notificationTemplate,
	'flow-topic-resolved' => [
		'section' => 'message',
		'user-locators' => [
			'Flow\\NotificationsUserLocator::locateUsersWatchingTopic',
		],
		'user-filters' => [
			'EchoUserLocator::locateTalkPageOwner',
		],
	] + $topicResolvedNotification,
	'flowusertalk-topic-resolved' => [
		'category' => 'edit-user-talk',
		'section' => 'alert',
		'user-locators' => [
			'EchoUserLocator::locateTalkPageOwner',
		],
	] + $topicResolvedNotification,
	'flow-mention-failure-too-many' => [
		'user-locators' => [
			'EchoUserLocator::locateEventAgent'
		],
		'section' => 'alert',
		'presentation-model' => 'Flow\\MentionStatusPresentationModel'
	] + $notificationTemplate,
];

return $notifications;
