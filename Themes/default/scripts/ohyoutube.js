/*
 Copyright (c) 2015 Jessica González
 @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

	function b(){
		$('.youtube').each(function() {

			imgsrc = getVidImage(this.id);
			$(this).css('background-image', 'url(http://i.ytimg.com/vi/' + this.id + '/sddefault.jpg)');

			// Overlay the Play icon to make it look like a video player
			$(this).append($('<div/>', {'class': 'play'}));

			$(document).delegate('#'+this.id, 'click', function() {
				// Create an iFrame with autoplay set to true
				var iframe_url = "https://www.youtube.com/embed/" + this.id + "?autoplay=1&autohide=1";
				if ($(this).data('params')) iframe_url+='&'+$(this).data('params');

				// The height and width of the iFrame should be the same as parent
				var iframe = $('<iframe/>', {'frameborder': '0', 'src': iframe_url, 'width': $(this).width(), 'height': $(this).height() })

				// Replace the YouTube thumbnail with YouTube HTML5 Player
				$(this).replaceWith(iframe);
			});
		});
	};

	function getVidImage(youtubeID)
	{
		var index, len;
		var imageTypes = ['default', 'hqdefault', 'mqdefault', 'sddefault', 'maxresdefault'];
		for (index = 0, len = imageTypes.length; index < len; ++index) {
			var imgsrc = 'http://i.ytimg.com/vi/'+ youtubeID +'/'+ imageTypes[index] +'.jpg';

			if (imgsrc.width !=0){
				break;
			}
		}

		return imgsrc;
	}
