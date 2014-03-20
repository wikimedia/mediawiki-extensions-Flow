<?php
if ( ! isset( $tag ) ) {
	$tag = 'p';
}

$user = RequestContext::getMain()->getUser();
$lang = RequestContext::getMain()->getLanguage();

$agoTime = $timestamp->getHumanTimestamp();

$timestamp->offsetForUser($user);
$absoluteTime = $lang->userTimeAndDate($timestamp, $user);

$html =
	Html::element( 'span', array( 'class' => 'flow-agotime' ), $agoTime ) . "\n" .
	Html::element( 'span', array( 'class' => 'flow-abstime' ), $absoluteTime ) . "\n";

if ( isset( $historicalLink ) ) {
	$html = Html::rawElement( 'a', array( 'href' => $historicalLink ), $html );
}

echo Html::rawElement( $tag, array( 'class' => 'flow-datestamp' ), $html );
