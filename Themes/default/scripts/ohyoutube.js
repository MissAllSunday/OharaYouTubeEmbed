/*
 Copyright (c) 2015 Jessica González
 @license http://www.mozilla.org/MPL/ MPL 2.0
 @version 2.1
*/

var _oh = function(){
	this.masterDiv = $('.oharaEmbed');
	this.basedElement = this.masterDiv.parent();
	this.defaultWidth = typeof(_ohWidth) !== 'undefined' ? _ohWidth : 480;
	this.defaultHeight = typeof(_ohHeight) !== 'undefined' ? _ohHeight : 270;
	this.aspectRatio = this.defaultHeight / this.defaultWidth;

	this.main();
	this.responsive();
};

_oh.prototype.getIframe(site)
{
	return $('<iframe/>', {'frameborder': '0', 'src': site.baseUrl, 'width': this.defaultWidth, 'height': this.defaultHeight, 'title': (typeof site.title !== 'undefined' ? site.title : '')});
};

_oh.prototype.createVideo(site, videoElement)
{
	$this = this;
	videoElement.append($('<div/>', {'class': 'oharaEmbed_play'}));

	videoElement.one('click', function(){

		// Append the YouTube HTML5 Player.
		videoElement.children('.oharaEmbed_play').css({'height': '0'});
		videoElement.css({'background-image': 'none'}).append($this.getIframe(site);
	});
}

_oh.prototype.main = function(){

	$this = this;

	// Get all registered sites.
	$.each(_ohSites, function(index, site) {
			$(site.cssID).each(function(){

				site.videoElement = $(this);

				// Get and gather all we need!
				site = $.extend(site, videoElement.data('ohara_'+ site.identifier));
				site.baseUrl = site.baseUrl.replace('{video_id}', site.video_id);

				// Check if the site has a custom function to use.
				getImage = typeof site.getImage === 'string' ? _oh[site.getImage] : site.getImage;

				// Now get an image preview and perhaps a title too!
				getImage(site, videoElement);

				// Finally, create the actual video's HTML. @todo make it possible for sites to use their own create function.
				$this.videoCreate(site, videoElement;
			});
	});

	// Gotta make sure the new iframe gets resized if needed.
	$this.responsive();
};

_oh.prototype.responsive = function()
{
	$this = this;

	$(window).resize(function(){

		// Get the new width and height.
		var newWidth = $this.basedElement.width();
		var newHeight = (newWidth * $this.aspectRatio) <= $this.defaultHeight ? (newWidth * $this.aspectRatio) : $this.defaultHeight;

		// If the new width is lower than the "default width" then apply some resizing. No? then go back to our default sizes
		var applyResize = (newWidth <= $this.defaultWidth),
			applyWidth = !applyResize ? $this.defaultWidth : newWidth,
			applyHeight = !applyResize ? $this.defaultHeight : newHeight;

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


_oh.prototype.getVimeoImage(site, videoElement)
{
	// The thumbnail url is already included in site or at leasts thats the expected behaviour.
	if (typeof site.thumbnail_url !== 'undefined'){
		videoElement.css({'background-image': 'url('+ site.thumbnail_url +')', 'background-size': 'cover'});
	}
};

_oh.prototype.getYoutubeImage(site, videoElement)
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

	if (typeof imgsrc !== 'undefined'){
		videoElement.css({'background-image': 'url('+ imgsrc +')', 'background-size': 'cover'});
	}
};

(function( $ ) {
	$(function() {
		var _ohObject = new _oh();

		$('input[name=preview]').on('click',function(){
			_ohObject.refresh();
		});
	});
})(jQuery);