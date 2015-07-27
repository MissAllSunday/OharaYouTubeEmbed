/*
 Copyright (c) 2015 Jessica González
 @license http://www.mozilla.org/MPL/ MPL 2.0
*/

	function oh_main(){

		$('.youtube').each(function() {

			var videoID = this.id.replace('oh_',''),
				imgsrc = oh_getImage(videoID),
				imgHeight = $(this).height(),
				imgWidth = $(this).width();

			if (typeof imgsrc !== 'undefined'){
				$(this).css({'background-image': 'url('+ imgsrc +')', 'background-size': 'cover'});
			}

			$(this).append($('<div/>', {'class': 'youtube_play'}));

			$('#oh_'+videoID).one('click', function() {
				var iframe_url = '//www.youtube.com/embed/' + videoID + '?autoplay=1&autohide=1';
				if ($(this).data('params')) iframe_url+='&'+$(this).data('params');

				// The height and width of the iFrame should be the same as parent
				var iframe = $('<iframe/>', {'frameborder': '0', 'src': iframe_url, 'width': imgWidth, 'height': imgHeight});

				// Append the YouTube HTML5 Player.
				$(this).css({'background-image': 'none'}).append(iframe);
				$(this).children('.youtube_play').css({'height': '0'});
				oh_responsive();
			});
		});
	};

	function oh_getImage(youtubeID)
	{
		var imgsrc = '',
			index, len,
			imageTypes = ['hqdefault', 'mqdefault', 'sddefault', 'maxresdefault'];
		for (index = 0, len = imageTypes.length; index < len; ++index) {
			imgsrc = '//i.ytimg.com/vi/'+ youtubeID +'/'+ imageTypes[index] +'.jpg';

			if (imgsrc.width !=0){
				break;
			}
		}

		// Still no image, show the default one
		if (imgsrc.width ==0){
			imgsrc = '//i.ytimg.com/vi/'+ youtubeID +'/default.jpg';
		} 

		return imgsrc;
	};

	function oh_refresh()
	{
		setTimeout(function(){oh_main()},3E3);
		setTimeout(function(){oh_responsive()},3E3);
	}

	function oh_responsive()
	{
		var allVideos = $('.oharaEmbed > iframe'),
			fluidEl = $('.oharaEmbed').parent();

		allVideos.each(function() {

			$(this).data('aspectRatio', this.height / this.width);
		});

		$(window).resize(function() {

			var newWidth = fluidEl.width();

			if (newWidth <= 600)
				allVideos.each(function() {

					var $el = $(this);
					$el.width(newWidth).height(newWidth * $el.data('aspectRatio'));
					$el.closest('.oharaEmbed').width(newWidth).height(newWidth * $el.data('aspectRatio'));

				});

		// Kick off one resize to fix all videos on page load
		}).resize();
	}

$(function() {
	oh_main();
	oh_responsive();
	$('input[name=preview]').on('click',function(){
			oh_refresh();
		});
});