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

_oh.prototype.getIframe = function(video)
{
	iframe = $('<iframe/>', {'frameborder': '0', 'src': video.embedUrl, 'width': this.defaultWidth, 'height': this.defaultHeight, 'allowfullscreen': '', 'class': 'oharaEmbedIframe'});

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
				video.embedUrl = site.embedUrl.replace('{video_id}', video.video_id);
				video.requestUrl = site.requestUrl.replace('{video_id}', video.video_id);

				// Allow each site to use their own function.
				if (typeof site.getData == 'function'){
					site.getData($this, video);
				}

				else{
					$this.getData(video);
				}

				// Finally, create the actual video's HTML.
				if (typeof site.createVideo == 'function'){
					site.createVideo($this, video);
				}

				else{
					video.domElement.append($('<div/>', {'class': 'oharaEmbed_play'}));

					$(video.domElement).on('click', function() {

						// Replace the video thumbnail with a HTML5 Player.
						$(this).html($this.getIframe(video));

						$this.responsive();
					});
				}
			});
	});

	// Gotta make sure the new iframe gets resized if needed.
	$this.responsive();
};

_oh.prototype.getData = function(video){
	$.getJSON('https://noembed.com/embed',
		{format: 'json', url: video.requestUrl}, function (data) {

		title = $('<div/>', {'class': 'oharaEmbed_title'}).html(data.title);

		video.domElement.css({'background-image': 'url('+ data.thumbnail_url +')', 'background-size': 'cover'});
		video.domElement.append(title);
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

// Add support for the editor.
if (typeof $.sceditor !== 'undefined')
{
	$.sceditor.plugins.bbcode.bbcode.set(
		'youtube', {
				allowsEmpty: true,
				tags: {
					iframe: {
						'data-youtube-id': null
					}
				},
				format: function (element, content) {
					element = element.attr('data-youtube-id');

					return element ? '[youtube]' + element + '[/youtube]' : content;
				},
				html: function (token, attrs, content) {
					if (match = content.match(/(^[a-zA-z0-9_-]{11})$/) || content.match(/(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/watch\?.+&v=))([\w-]{11})(?:.+)?$/) || content.match(/(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/)) {

						return '<iframe frameborder="0" src="//www.youtube.com/embed/'+ match[1] +'?autoplay=0&autohide=1" data-youtube-id="'+ content +'" allowfullscreen class="oharaEmbedIframe" style="width: 420px; height: 315px;"></iframe>';
					}

					else{
						return content;
					}
				}
			}
	);

	$.sceditor.plugins.bbcode.bbcode.set(
		'vimeo', {
				allowsEmpty: true,
				tags: {
					iframe: {
						'data-vimeo-id': null
					}
				},
				format: function (element, content) {
					element = element.attr('data-vimeo-id');

					return element ? '[vimeo]' + element + '[/vimeo]' : content;
				},
				html: function (token, attrs, content) {
					if (match = content.match(/(^\d+$)/) || content.match(/(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:[a-z]*\/)*([0-9]{6,11})[?]?.*/)) {

						return '<iframe frameborder="0" src="//player.vimeo.com/video/'+ match[1] +'?autoplay=0" data-vimeo-id="'+ content +'" allowfullscreen class="oharaEmbedIframe" style="width: 420px; height: 315px;"></iframe>';
					}

					else{
						return content;
					}
				}
			}
	);
}
(function( $ ) {
	$(function() {
		var _ohObject = new _oh();

		$('input[name=preview]').on('click',function(){
			_ohObject.refresh();
		});
	});
})(jQuery);
