<?php

$wgAutoloadClasses += array(
	'Flow\Rendering\Timestamp' => __DIR__.'/includes/Rendering/Elements/Timestamp.php',
	'Flow\Rendering\Post' => __DIR__.'/includes/Rendering/Elements/Post.php',
);

$wgFlowUIElements = array(
	'timestamp' => array(
		'class' => 'Flow\Rendering\Timestamp',
	),
	'post' => array(
		'class' => 'Flow\Rendering\Post',
	),
);