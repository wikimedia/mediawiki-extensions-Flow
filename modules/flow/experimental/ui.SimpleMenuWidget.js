mw.flowExperimental.ui.SimpleMenuWidget = function MwFlowSimpleMenuWidget( config ) {
	var items, classes;

	config = config || {};
	config.menuItems = config.menuItems || [];
	// Parent
	mw.flowExperimental.ui.SimpleMenuWidget.parent.call( this, config );

	this.$overlay = config.$overlay || this.$element;

	this.menu = new OO.ui.MenuSelectWidget( {
		classes: [ 'mw-flow-ui-simpleMenuWidget-menu' ]
	} );
	this.button = new OO.ui.PopupButtonWidget( {
		events: { click: 'menuItemClick' },
		classes: [ 'mw-flow-ui-simpleMenuWidget-trigger' ],
		icon: 'ellipsis',
		$overlay: this.$overlay,
		popup: {
			width: 300,
			anchor: false,
			align: 'backwards',
			$autoCloseIgnore: this.$overlay,
			$content: this.menu.$element
		}
	} );

	// Add items
	items = [];
	classes = [];
	config.menuItems.forEach( function ( itemData ) {
		if ( itemData === 'separator' ) {
			classes.push( 'mw-flow-ui-simpleMenuWidget-separator' );
		} else {
			classes.push( 'mw-flow-ui-simpleMenuWidget-item' );
			items.push(
				new OO.ui.MenuOptionWidget( $.extend( {
					classes: classes
				}, itemData ) )
			);
			classes = [];
		}
	} );
	this.menu.addItems( items );

	// Events
	this.menu.connect( this, {
		choose: 'onMenuChoose'
	} );

	this.$element
		.append( this.button.$element )
		.addClass( 'mw-flow-ui-simpleMenuWidget' );
};

/* Initialization */
OO.inheritClass( mw.flowExperimental.ui.SimpleMenuWidget, OO.ui.PopupButtonWidget );


mw.flowExperimental.ui.SimpleMenuWidget.prototype.onMenuChoose = function ( item ) {
	if ( item.getData() ) {
		this.emit( 'clicked', item.getData() );
	}
};
