/* globals Hugh, wp */
(function($){

	var tmpl = wp.template('color-template'),
		$wrap = $('.hugh__colorways'),
		$form = $('.hugh__form'),
		$form_color = $form.find('#hugh_color'),
		$form_label = $form.find('#hugh_label');

	$.getJSON( Hugh.root + Hugh.namespace + '/colors', function( response ) {
		if ( response.length ) {
			_.each( response, function( item ) {
				$wrap.append( tmpl( item ) );
			} );
		}
	});

	$form.submit(function(e){
		e.preventDefault();
		var data = {
			color : $form_color.val(),
			label : $form_label.val()
		};
		$.post(  Hugh.root + Hugh.namespace + '/colors/add', data, function( data, textStatus ) {
			if ( 'success' === textStatus ) {
				$wrap.prepend( tmpl( data ) );
			}
		}, 'json' );
	})

})(jQuery);
