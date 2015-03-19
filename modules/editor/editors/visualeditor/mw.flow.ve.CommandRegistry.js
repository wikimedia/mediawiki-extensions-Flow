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

	ve.ui.commandRegistry.register(
		new ve.ui.Command(
			'flowSwitchEditor',
			'flowSwitchEditor', // probably doesnt work like this
			'switch',  // method to call on action
			{ args: [] } // arguments to pass to action
		)
	);
} ( ve ) );
