<?php

$notificationTemplate = [
	'category' => 'flow-discussion',
	'group' => 'other',
];

$newTopicNotification = [
	'presentation-model' => \Flow\NewTopicPresentationModel::class,
	'bundle' => [
		'web' => true,
		'email' => true,
		'expandable' => true,
	],
] + $notificationTemplate;

$descriptionEditedNotification = [
	'presentation-model' => \Flow\HeaderEditedPresentationModel::class,
	'bundle' => [
		'web' => true,
		'email' => true,
	],
] + $notificationTemplate;

$postEditedNotification = [
	'presentation-model' => \Flow\PostEditedPresentationModel::class,
	'bundle' => [
		'web' => true,
		'email' => true,
	],
] + $notificationTemplate;

$postReplyNotification = [
	'presentation-model' => \Flow\PostReplyPresentationModel::class,
	'bundle' => [
		'web' => true,
		'email' => true,
		'expandable' => true,
	],
] + $notificationTemplate;

$topicRenamedNotification = [
	'presentation-model' => \Flow\TopicRenamedPresentationModel::class,
	'primary-link' => [
		'message' => 'flow-notification-link-text-view-post',
		'destination' => 'flow-post'
	],
] + $notificationTemplate;

$summaryEditedNotification = [
	'presentation-model' => \Flow\SummaryEditedPresentationModel::class,
	'bundle' => [
		'web' => true,
		'email' => true,
	],
] + $notificationTemplate;

$topicResolvedNotification = [
	'presentation-model' => \Flow\TopicResolvedPresentationModel::class,
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
		'presentation-model' => \Flow\MentionPresentationModel::class,
		'section' => 'alert',
		'user-locators' => [
			'Flow\\NotificationsUserLocator::locateMentionedUsers',
		],
	] + $notificationTemplate,
	'flow-enabled-on-talkpage' => [
		'category' => 'system',
		'presentation-model' => \Flow\FlowEnabledOnTalkpagePresentationModel::class,
		'section' => 'message',
		'user-locators' => [
			'EchoUserLocator::locateTalkPageOwner'
		],
		'canNotifyAgent' => true,
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
		'canNotifyAgent' => true,
		'section' => 'alert',
		'presentation-model' => \Flow\MentionStatusPresentationModel::class,
	] + $notificationTemplate,
];

return $notifications;
