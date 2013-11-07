<?php
if ( ! isset( $tag ) ) {
	$tag = 'p';
}

$agoTime = $timestamp->getHumanTimestamp();
$utcTime = $timestamp->getTimestamp( TS_RFC2822 );

$html =
	Html::element( 'span', array( 'class' => 'flow-agotime' ), $agoTime ) . "\n" .
	Html::element( 'span', array( 'class' => 'flow-utctime' ), $utcTime ) . "\n";

if ( isset( $historicalLink ) ) {
	$html = Html::rawElement( 'a', array( 'href' => $historicalLink ), $html );
}

echo Html::rawElement( $tag, array( 'class' => 'flow-datestamp' ), $html );