( function ( ve ) {
	'use strict';

	ve.ui.commandRegistry.register(
		new ve.ui.Command(
			'flowMention',
			'window',
			'open',
			{ args: ['flowMention'] },
			{ supportedSelections: ['linear'] }
		)
	);
} ( ve ) );
