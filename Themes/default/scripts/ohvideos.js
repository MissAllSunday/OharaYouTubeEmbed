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

_oh.prototype.getIframe = function(site)
{
	iframe = $('<iframe/>', {'frameborder': '0', 'src': site.baseUrl, 'width': this.defaultWidth, 'height': this.defaultHeight, 'allowfullscreen': '', 'class': 'oharaEmbedIframe'});

	return iframe;
};

_oh.prototype.main = function(){

	$this = this;

	// Get all registered sites.
	$.each(_ohSites, function(index, site) {
			$('.' + site.identifier).each(function(index, video){

				video.domElement = $(this);

				// Get and gather all we need!
				video = $.extend(video, video.domElement.data('ohara_'+ site.identifier));
				video.baseUrl = site.baseUrl.replace('{video_id}', video.video_id);

				// Check if the site has a custom function to use.
				getImage = typeof site.getImage === 'string' ? $this[site.getImage] : site.getImage;

				// Now get an image preview and perhaps a title too!
				getImage(video);

				// Finally, create the actual video's HTML. @todo make it possible for sites to use their own create function.
				$this.videoCreate(video);
			});
	});

	// Gotta make sure the new iframe gets resized if needed.
	$this.responsive();
};

_oh.prototype.videoCreate = function(video){

	$this = this;
	video.domElement.append($('<div/>', {'class': 'oharaEmbed_play'}));

	$(video.domElement).on('click', function() {

		// Replace the video thumbnail with a HTML5 Player.
		$(this).html($this.getIframe(video));

		$this.responsive();
	});
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
		$('iframe.oharaEmbedIframe').each(function(){
			$(this).width(applyWidth).height(applyHeight);
		});

	// Kick off one resize to fix all videos on page load.
	}).resize();
};

_oh.prototype.refresh = function(){
	$this = this;
	setTimeout(function(){$this.main();},3E3);
	setTimeout(function(){$this.responsive();},3E3);
};


_oh.prototype.getVimeoImage = function(video)
{
	// The image url is already included in site or at least thats the expected behaviour.
	if (typeof video.imageUrl !== 'undefined'){
		video.domElement.css({'background-image': 'url('+ video.imageUrl +')', 'background-size': 'cover'});
	}
};

_oh.prototype.getYoutubeImage = function(video)
{
	// The image url is already included in video or at least thats the expected behaviour.
	if (typeof video.imageUrl !== 'undefined'){
		video.domElement.css({'background-image': 'url('+ video.imageUrl +')', 'background-size': 'cover'});

		return;
	}

	var imgsrc = '',
		index, len,
		imageTypes = ['hqdefault', 'mqdefault', 'sddefault', 'maxresdefault'];
	for (index = 0, len = imageTypes.length; index < len; ++index) {
		imgsrc = '//i.ytimg.com/vi/'+ video.video_id +'/'+ imageTypes[index] +'.jpg';

		if (imgsrc.width !=0){
			break;
		}
	}

	// Still no image, show the default one
	if (imgsrc.width ==0){
		imgsrc = '//i.ytimg.com/vi/'+ video.video_id +'/default.jpg';
	}

	if (typeof imgsrc !== 'undefined'){
		video.domElement.css({'background-image': 'url('+ imgsrc +')', 'background-size': 'cover'});
	}
};

// Add support for the editor.
$.sceditor.plugins.bbcode.bbcode.set(
	'youtube', {
		tags: {
			attach: {
				src: null
			}
		},
		allowsEmpty: true,
		quoteType: $.sceditor.BBCodeParser.QuoteType.never,
		html: function (token, attrs, content) {
			if (match = content.match(/(^[a-zA-z0-9_-]{11})$/) || content.match(/(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/watch\?.+&v=))([\w-]{11})(?:.+)?$/) || content.match(/(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/)) {

				return '<iframe frameborder="0" src="https://www.youtube.com/embed/'+ match[1] +'?autoplay=0&autohide=1" allowfullscreen="" class="oharaEmbedIframe" style="width: 420px; height: 315px;"></iframe>';
			}

			else{
				return content;
			}
		}
	}
);

(function( $ ) {
	$(function() {
		var _ohObject = new _oh();

		$('input[name=preview]').on('click',function(){
			_ohObject.refresh();
		});
	});
})(jQuery);