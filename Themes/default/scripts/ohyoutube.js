/*
 Copyright (c) 2015 Jessica González
 @license http://www.mozilla.org/MPL/MPL-1.1.html
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

			$('#oh_'+videoID).on('click', function() {
				var iframe_url = 'https://www.youtube.com/embed/' + videoID + '?autoplay=1&autohide=1';
				if ($(this).data('params')) iframe_url+='&'+$(this).data('params');

				// The height and width of the iFrame should be the same as parent
				var iframe = $('<iframe/>', {'frameborder': '0', 'src': iframe_url, 'width': imgWidth, 'height': imgHeight })

				// Replace the YouTube thumbnail with YouTube HTML5 Player
				$(this).replaceWith(iframe);
			});
		});
	};

	function oh_getImage(youtubeID)
	{
		var imgsrc = '',
			index, len,
			imageTypes = ['hqdefault', 'mqdefault', 'sddefault', 'maxresdefault'];
		for (index = 0, len = imageTypes.length; index < len; ++index) {
			imgsrc = 'http://i.ytimg.com/vi/'+ youtubeID +'/'+ imageTypes[index] +'.jpg';

			if (imgsrc.width !=0){
				break;
			}
		}

		// Still no image, show the default one
		if (imgsrc.width ==0){
			imgsrc = 'http://i.ytimg.com/vi/'+ youtubeID +'/default.jpg';
		}

		return imgsrc;
	};

	function oh_getUrl(sParam)
	{
		var sPageURL = window.location.search.substring(1);

		// SimpleSEF or pretty urls?
		if (sPageURL.indexOf(sParam) > -1) {
			return true;
		}

		var sURLVariables = sPageURL.split(';');
		for (var i = 0; i < sURLVariables.length; i++)
		{
			var sParameterName = sURLVariables[i].split('=');
			if (sParameterName[0] == sParam)
			{
				return true;
			}
		}

		return false;
	}

	function oh_refresh()
	{
		setTimeout(function(){oh_main()},3E3);
	}

$(function(){
	oh_main();

	if (oh_getUrl('post'))
		$('input[name=preview]').on('click',function(){
			oh_refresh();
		});
});
