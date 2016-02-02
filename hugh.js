/* globals Hugh, wp */
(function($){

	var tmpl = wp.template('color-template'),
		$wrap = $('.hugh__colorways');
	
	$wrap.html('');

	$.getJSON( Hugh.root + Hugh.namespace + '/colors', function( response ) {
		/*
		response = [{
				color: '#ff0000',
				label: 'foo'
			},{
				color: '#00ff00',
				label: 'bar'
			},{
				color: '#0000ff',
				label: 'baz'
			}];
		*/
		if ( response.length ) {
			_.each( response, function( item ) {
				$wrap.append( tmpl( item ) );
			} );
		}
		
	} );

})(jQuery);
