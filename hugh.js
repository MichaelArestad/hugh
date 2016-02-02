/* globals Hugh, wp */
(function($){

	var tmpl = wp.template('color-template'),
		styleTmpl = wp.template('style-template'),
		$wrap = $('.hugh__colorways'),
		$form = $('.hugh__form'),
		$form_color = $form.find('#hugh_color'),
		$form_label = $form.find('#hugh_label'),
		currColors = [],
		styleBlock = document.createElement('style');

	document.head.appendChild( styleBlock );

	function setCurrentColor( hex ) {
		styleBlock.innerText = styleTmpl( { color : hex } );
	}

	$wrap.on( 'click', 'a', function(e){
		e.preventDefault();
		setCurrentColor( $(this).data('color') );
	});

	function renderAnyNewColors( newColors ) {
		var colorColors = _.pluck( currColors, 'color' ),
			diff = _.filter( newColors, function( maybeNewColor ) {
				return ! _.contains( colorColors, maybeNewColor.color );
			} );

		if ( diff.length ) {
			// Add a way to update existing colors in case of a duplicate? Eh, we can just have duplicates when readded.
			_.each( diff, function( item ) {
				$wrap.prepend( tmpl( item ) );
				setCurrentColor( item.color );
			} );
		}

		currColors = newColors;
	}

	(function updateColors() {
		$.getJSON( Hugh.root + Hugh.namespace + '/colors', renderAnyNewColors );
		setTimeout( updateColors, 5000 );
	})();

	$form.submit(function(e){
		e.preventDefault();
		var data = {
			color : $form_color.val(),
			label : $form_label.val()
		};
		$.post(  Hugh.root + Hugh.namespace + '/colors/add', data, function( data, textStatus ) {
			if ( 'success' === textStatus ) {
				renderAnyNewColors( _.union( currColors, data ) );
				$form_label.val('');
			}
		}, 'json' );
	});

})(jQuery);
