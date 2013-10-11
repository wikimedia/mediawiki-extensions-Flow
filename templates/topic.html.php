<?php

// treat title like unparsed (wiki)text
$title = $root->getContent( $user, 'wikitext' );

echo Html::openElement( 'div', array(
	'class' => 'flow-topic-container flow-topic-full',
	'id' => 'flow-topic-' . $topic->getId()->getHex(),
	'data-topic-id' => $topic->getId()->getHex(),
	'data-title' => $title,
) );
?>
<div class="flow-titlebar mw-ui-button">

	<?php
	echo Html::rawElement(
		'a',
		array(
			'href' => $this->generateUrl( $root->getPostId(), 'edit-title' ),
			'class' => 'flow-edit-topic-link flow-icon flow-icon-top-aligned',
		),
		wfMessage( 'flow-topic-action-edit-title' )
	);
	?>

	<div class="flow-topic-title">
		<h2 class="flow-realtitle">
			<?php echo htmlspecialchars( $title ); ?>
		</h2>
	</div>
	<div class="flow-actions">
		<a class="flow-actions-link" href="#"><?php echo wfMessage( 'flow-topic-actions' )->escaped(); ?></a>
		<div class="flow-actions-flyout">
			<ul>
				<li class="flow-action-hide">
					<a href="#" class="mw-ui-button mw-ui-destructive">@todo: Hide topic</a>
				</li>
				<li class="flow-action-close">
					<a href="#" class="mw-ui-button">@todo: Close topic</a>
				</li>
			</ul>
		</div>
	</div>

	<?php
		echo Html::element(
			'a',
			array(
				'class' => 'flow-icon-permalink flow-icon flow-icon-top-aligned',
				'title' => wfMessage( 'flow-topic-action-view' )->text(),
				'href' => $this->generateUrl( $topic ),
			),
			wfMessage( 'flow-topic-action-view' )->text()
		);
	?>

	<ul class="flow-topic-posts-meta">
		<li>@todo: participants</li>
		<li>@todo: # comments</li>
	</ul>

	<?php
		// @todo: afaik, there's no watchlist functionality yet; this blurb is just to position it correctly already

		$watchlistActive = false; // @todo: true if already watchlisted, false if not
		echo Html::element(
			'a',
			array(
				'class' => 'flow-icon-watchlist flow-icon flow-icon-bottom-aligned'
					. ( $watchlistActive ? ' flow-icon-watchlist-active' : '' ),
				'title' => wfMessage( 'flow-topic-action-watchlist' )->text(),
				'href' => '#',
				'onclick' => "alert( '@todo: Not yet implemented!' ); return false;"
			),
			wfMessage( 'flow-topic-action-watchlist' )->text()
		);
	?>

	<p class="flow-datestamp">
		<?php
			// timestamp html
			$content = '
				<span class="flow-agotime" style="display: inline">'. $topic->getLastModifiedObj()->getHumanTimestamp() .'</span>
				<span class="flow-utctime" style="display: none">'. $topic->getLastModifiedObj()->getTimestamp( TS_RFC2822 ) .'</span>';

			// build history button with timestamp html as content
			echo Html::rawElement( 'a',
				array(
					'class' => 'flow-action-history-link',
					'href' => $this->generateUrl( $root->getPostId(), 'topic-history' ),
				),
				$content
			);
		?>
	</p>
</div>

<?php
foreach( $root->getChildren() as $child ) {
	echo $this->renderPost( $child, $block, $root );
}
?>
</div>
