(function(g, $, bootbox){
	/**
	 * Composer console output class.
	 *
	 * @param inputUrl string Full URL of the composer output fetching API.
	 * @constructor
	 */
	g.ComposerConsole = function(inputUrl){
		this.url = inputUrl;
		this.interval = null;
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
		 * Toggles console output auto-refresh functionality
		 */
		autoRefresh: function(){
			var composerConsole = this;
			if(this.interval === null)
				this.interval = setInterval(function(){
					var consoleContent = $('#console-content');
					var oldText = consoleContent.find('code').text();
					composerConsole.init();
					if(oldText != composerConsole.content){
						consoleContent.find('code').text(composerConsole.content);
						consoleContent.scrollTop(consoleContent.find('pre').height());
					}
				}, 2500);
			else{
				clearInterval(this.interval);
				this.interval = null;
			}
		},
		/**
		 * Viewing Console output data in a custom bootbox dialog.
		 */
		view: function(){
			var consoleView = $('#console-content').html('<i class="material-icons loader">rotate_right</i>').toggleClass('hidden-xs-up hidden-xs-down');
			$('#open-console').toggleClass('active');
			do{} while(this.content === undefined);
			if(this.content == '')
				this.content = 'Console output is empty.';
			consoleView.html('<pre><code>'+this.content+'</code></pre>');
		},
		/**
		 * Clearing console output file.
		 */
		clear: function(){
			$.ajax({
				url: urls.clearConsole,
				type: 'GET',
				success: function(){ $('#console-content').find('code').text('Console is cleared.'); },
				error: this.error
			});
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