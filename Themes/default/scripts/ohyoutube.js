/*
 Copyright (c) 2015 Jessica González
 @license http://www.mozilla.org/MPL/ MPL 2.0
*/

var _oh = function(){
	this.masterDiv = $('.oharaEmbed');
	this.videoFrame = $('.oharaEmbed > iframe');
	this.basedElement = this.masterDiv.parent();
	this.youtube = $('.youtube');
	this.defaultWidth = this.basedElement.width();
	this.defaultHeight = this.basedElement.height();
	this.aspectRatio = this.defaultHeight / this.defaultWidth;

	this.main();
	this.responsive();
};

_oh.prototype.main = function(){

	$this = this;

	this.youtube.each(function(){

		var videoID = this.id.replace('oh_',''),
			imgsrc = $this.getImage(videoID),
			imgHeight = $this.basedElement.height(),
			imgWidth = $this.basedElement.width();

		if (typeof imgsrc !== 'undefined'){
			$(this).css({'background-image': 'url('+ imgsrc +')', 'background-size': 'cover'});
		}

		$(this).append($('<div/>', {'class': 'youtube_play'}));

		$('#oh_'+videoID).one('click', function(){
			var iframe_url = '//www.youtube.com/embed/' + videoID + '?autoplay=1&autohide=1';

			if ($(this).data('params')){
				iframe_url+='&'+$(this).data('params');
			}

			// The height and width of the iFrame should be the same as parent
			var iframe = $('<iframe/>', {'frameborder': '0', 'src': iframe_url, 'width': imgWidth, 'height': imgHeight});

			// Append the YouTube HTML5 Player.
			$(this).css({'background-image': 'none'}).append(iframe);
			$(this).children('.youtube_play').css({'height': '0'});
		});
	});
};

_oh.prototype.responsive = function()
{
	$this = this;

	$(window).resize(function(){

		// Get the new width and height.
		var newWidth = $this.basedElement.width();
		var newHeight = (newWidth * $this.aspectRatio) <= $this.masterDiv.height() ? (newWidth * $this.aspectRatio) : $this.masterDiv.height();

		// If the new width is lower than the "default width" then apply some resizing. No? then go back to our default sizes
		var applyResize = (newWidth <= (typeof(_ohWidth) !== 'undefined' ? _ohWidth : 600));

			// Gotta resize the master div.
			$this.masterDiv.width(!applyResize ? $this.defaultWidth : newWidth).height(!applyResize ? $this.defaultWidth : newHeight);
			$this.videoFrame.each(function(){

				var $el = $(this);
				$el.width(!applyResize ? $this.defaultWidth : newWidth).height(!applyResize ? $this.defaultWidth : newHeight);
			});

	// Kick off one resize to fix all videos on page load
	}).resize();
};

_oh.prototype.refresh = function(){
	$this = this;
	setTimeout(function(){$this.main();},3E3);
	setTimeout(function(){$this.responsive();},3E3);
};

_oh.prototype.getImage = function(youtubeID)
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

(function( $ ) {
	$(function() {
		var _ohObject = new _oh();

		$('input[name=preview]').on('click',function(){
			_ohObject.refresh();
		});
	});
})(jQuery);
