/*
 Copyright (C) 2017 Jessica González
 @license http://www.mozilla.org/MPL/ MPL 2.0
 @version 1.2.9
*/

var _oh = function(){
	this.masterDiv = $('.oharaEmbed');
	this.basedElement = this.masterDiv.parent();
	this.defaultWidth = typeof(_ohWidth) !== 'undefined' ? _ohWidth : 480;
	this.defaultHeight = typeof(_ohHeight) !== 'undefined' ? _ohHeight : 270;
	this.aspectRatio = this.defaultHeight / this.defaultWidth;
	this.basedWidth = this.basedElement.width() >= this.defaultWidth ? this.defaultWidth : this.basedElement.width();
	this.basedHeight = this.basedElement.height() >= this.defaultHeight ? this.defaultHeight : this.basedElement.height();

	this.main();
	this.responsive();
};

_oh.prototype.main = function(){

	var $this = this;

	$('.youtube').each(function(){

		var _element = $(this);
			_element.videoID = this.id.replace('oh_','');
			_element.imgsrc = $this.getImage(_element.videoID);
			_element.imgHeight = $this.basedHeight;
			_element.imgWidth = $this.basedWidth;

		if (typeof _element.imgsrc !== 'undefined'){
			_element.css({'background-image': 'url('+ _element.imgsrc +')', 'background-size': 'cover'});
		}

		_element.append($('<div/>', {'class': 'youtube_play'}));

		_element.one('click', function(){
			var iframe_url = '//www.youtube.com/embed/' + _element.videoID + '?autoplay=1&autohide=1';

			if (_element.data('params')){
				iframe_url+='&'+ _element.data('params');
			}

			// The height and width of the iFrame should be the same as parent
			var iframe = $('<iframe/>', {'frameborder': '0', 'src': iframe_url, 'width': _element.imgWidth, 'height': _element.imgHeight, 'allowfullscreen': 'allowfullscreen'});

			// Append the YouTube HTML5 Player.
			_element.css({'background-image': 'none'}).append(iframe);
			_element.children('.youtube_play').css({'height': '0'});
		});
	});

	// Gotta make sure the new iframe gets resized if needed.
	$this.responsive();
};

_oh.prototype.responsive = function()
{
	$this = this;

	$(window).resize(function(){

		var newWidth = $this.basedElement.width();
			newHeight = (newWidth * $this.aspectRatio) <= $this.defaultHeight ? (newWidth * $this.aspectRatio) : $this.defaultHeight;

		// If the new width is lower than the "default width" then apply some resizing. No? then go back to our default sizes
		var applyResize = (newWidth <= $this.defaultWidth),
			applyWidth = !applyResize ? $this.defaultWidth : newWidth,
			applyHeight = !applyResize ? $this.defaultHeight : newHeight;

		// Gotta check the applied width and height is actually something!
		if (applyWidth <= 0 && applyHeight <= 0) {
			applyWidth = $this.defaultWidth;
			applyHeight = $this.defaultHeight;
		}

		// Gotta resize the master div.
		$this.masterDiv.width(applyWidth).height(applyHeight);
		$('.oharaEmbed > iframe').each(function(){
			$(this).width(applyWidth).height(applyHeight);
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
