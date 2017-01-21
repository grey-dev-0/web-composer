(function(g, $, bootbox){
	/**
	 * Composer console output class.
	 *
	 * @param inputUrl string Full URL of the composer output fetching API.
	 * @constructor
	 */
	g.ComposerConsole = function(inputUrl){
		this.url = inputUrl;
		this.init();
	};

	/**
	 * Setting ComposerConsole static methods.
	 */
	g.ComposerConsole.prototype = {
		/**
		 * Initializing Console output data.
		 */
		init: function(){
			var composerConsole = this;
			$.ajax({
				url: composerConsole.url,
				type: 'GET',
				success: function(response){ composerConsole.content = response.content; },
				error: composerConsole.error
			});
		},
		/**
		 * Refreshing Console output data.
		 */
		refresh: function(){ this.init(); },
		/**
		 * Viewing Console output data in a custom bootbox dialog.
		 */
		view: function(){
			var consoleView = bootbox.dialog({
				title: 'Composer Console Output',
				message: '<i class="material-icons loader">rotate_right</i>',
				backdrop: true,
				onEscape: true,
				buttons: {
					ok: {
						label: 'OK',
						className: 'btn-outline-primary'
					}
				}
			});
			do{} while(this.content === undefined);
			if(this.content == '')
				this.content = 'Console output is empty.';
			consoleView.find('.modal-body').attr('id', 'console-output')
				.find('.bootbox-body').html('<pre><code>'+this.content+'</code></pre>');
		},
		/**
		 * Showing an error message while logging the error occurred in the developer console.
		 *
		 * @param xhr Object XHR object returned from server-side in case of error.
		 */
		error: function(xhr){
			bootbox.alert('Something went wrong please refer to the developer console for more info.');
			console.log(xhr.responseText);
		}
	};
})(window, jQuery, bootbox);