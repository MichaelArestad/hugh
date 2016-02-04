/* globals Hugh, wp */
(function($){

	var tmpl = wp.template('color-template'),
		styleTmpl = wp.template('style-template'),
		$wrap = $('.hugh__colorways'),
		$form = $('.hugh__form'),
		$form_color = $form.find('#hugh_color'),
		$form_label = $form.find('#hugh_label'),
		currColors = [],
		styleBlock = document.createElement('style'),
		prevColor = window.sessionStorage.getItem('hugoPrevColor');

	document.head.appendChild( styleBlock );

	function getContrastingColor( hex ) {
		var colors = hex.match( /#([\da-f]{2})([\da-f]{2})([\da-f]{2})/ ),
			r = parseInt( colors[1], 16 ),
			g = parseInt( colors[2], 16 ),
			b = parseInt( colors[3], 16 ),
			brightness = Math.sqrt( 0.241 * r * r 
									+ 0.691 * g * g 
									+ 0.068 * b * b );

			if ( brightness < 130 ) {
				return '#ffffff';
			}

			return '#000000'
	}

	// removeTransitions must be set to true in the call to remove them, else they stay.
	function setCurrentColor( hex, removeTransitions ) {
		var css = styleTmpl( {
				color    : hex,
				contrast : getContrastingColor( hex )
			} );

		if ( removeTransitions ) {
			css = css.replace( /transition:[^;}]+([;}])/, "$1" );
		}

		styleBlock.innerText = css;

		window.sessionStorage.setItem( 'hugoPrevColor', hex );
	}

	$form_color.on( 'change', function() {
		setCurrentColor( $form_color.val(), true );
	});

	$wrap.on( 'click', 'a', function(e){
		e.preventDefault();
		var color = $(this).data('color');
		setCurrentColor( color );
		$form_color.val( color );
	});

	function renderAnyNewColors( newColors, removeTransitions ) {
		var colorColors = _.pluck( currColors, 'color' ),
			diff = _.filter( newColors, function( maybeNewColor ) {
				return ! _.contains( colorColors, maybeNewColor.color );
			} );

		if ( diff.length ) {
			// Add a way to update existing colors in case of a duplicate? Eh, we can just have duplicates when readded.
			_.each( diff, function( item ) {
				item.contrast = getContrastingColor( item.color );
				$wrap.prepend( tmpl( item ) );
				setCurrentColor( item.color, removeTransitions );
			} );
		}

		currColors = newColors;
	}

	renderAnyNewColors( Hugh.colors, true );

	if ( prevColor && prevColor.match( /^#[\da-f]{6}$/ ) ) {
		setCurrentColor( prevColor, true );
	}

	function updateColors() {
		$.getJSON( Hugh.root + Hugh.namespace + '/colors', renderAnyNewColors );
		setTimeout( updateColors, 5000 );
	}

	setTimeout( updateColors, 10000 );

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
