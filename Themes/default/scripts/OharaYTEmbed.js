(function ($) {
	'use strict';

	/**
	 * Class representing an OharaEmbedPlayer.
	 */
	const OharaEmbedPlayer = function () {
		this.masterSelector = '.oharaEmbed';
		this.init();
	};

	/**
	 * Initialize the player by setting up previews and event listeners.
	 */
	OharaEmbedPlayer.prototype.init = function () {
		$(this.masterSelector).each((index, element) => this.setupPreview($(element)));

		$(document).on('click', this.masterSelector, (e) => {
			e.preventDefault();

			const $mainContainer = $(e.target).closest(this.masterSelector);

			this.playVideo($mainContainer);
		});
	};

	/**
	 * Set up the preview for a container.
	 * @param {jQuery} $container - The jQuery object representing the container.
	 */
	OharaEmbedPlayer.prototype.setupPreview = function ($container) {
		const rawImageUrl = $container.attr('data-ohara_thumbnail_url');

		if (rawImageUrl) {
			const imageUrl = decodeURIComponent(rawImageUrl);

			$container.css({
				'background-image': 'url(' + imageUrl + ')',
				'background-size': 'cover',
				'background-position': 'center',
				'cursor': 'pointer',
				'position': 'relative'
			});

			if ($container.find('.ohara-play-btn').length === 0) {
				$container.append('<div class="ohara-play-btn" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:64px; height:64px; background:rgba(0,0,0,0.7); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px;">▶</div>');
			}
		}
	};

	/**
	 * Play a video in the specified container.
	 * @param {jQuery} $container - The jQuery object representing the container.
	 */
	OharaEmbedPlayer.prototype.playVideo = function ($container) {
		let siteInfo = null;

		$.each(_ohSites, (index, site) => {
			if ($container.hasClass(site.identifier)) {
				siteInfo = site;
				return false;
			}
		});

		if (!siteInfo) {
			return;
		}

		const rawData = $container.attr('data-ohara_' + siteInfo.identifier);

		if (!rawData) {
			return;
		}

		let videoData = {};

		try {
			const cleanRawData = decodeURIComponent(rawData);
			videoData = (cleanRawData.indexOf('{') === 0 || cleanRawData.indexOf('[') === 0)
				? $.parseJSON(cleanRawData)
				: { video_id: cleanRawData };
		} catch (e) {
			videoData = { video_id: decodeURIComponent(rawData) };
		}

		const videoId = videoData.video_id || rawData;

		if (!videoId) {
			return;
		}

		const embedUrl = decodeURIComponent($container.attr('data-ohara_embed_url'));

		if (!embedUrl || embedUrl === 'undefined') {
			return;
		}

	const width = $container.width() || videoData.width || 480;
		const height = $container.height() || videoData.height || 270;

		const $iframe = $('<iframe/>', {
			'frameborder': '0',
			'src': embedUrl,
			'width': '100%',
			'height': '100%',
			'allowfullscreen': '',
			'allow': 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
			'class': 'oharaEmbedIframe'
		});

		$container.empty().css('background-image', 'none').append($iframe);
	};

	$(function () {
		new OharaEmbedPlayer();

		if (typeof $.sceditor === 'undefined' || _ohSites === 'undefined') {
			return;
		}

		$.each(_ohSites, (index, site) => {

			$.sceditor.plugins.bbcode.bbcode.set(site.identifier, {
				allowsEmpty: true,
				tags: {
					iframe: {}
				},

				format: function (element, content) {
					const $el = $(element);
					const videoId = $el.attr('data-' + site.identifier + '-id');
					return videoId ? '[' + site.identifier + ']' + videoId + '[/' + site.identifier + ']' : content;
				},

				html: function (token, attrs, content) {
					const cleanContent = content.trim();
					let match = null;

					if (site.regex) {
						const regexPattern = new RegExp(site.regex.replace(/^\/|\/[a-z]*$/g, ''), 'i');
						match = cleanContent.match(regexPattern);
					}

					const videoId = match ? match[0] : cleanContent;

					if (videoId !== '') {
						return '<iframe frameborder="0" src="' + site.embedUrl.replace('{video_id}', videoId) + '" data-' + site.identifier + '-id="' + videoId + '" class="oharaEmbedIframe" style="width: 420px; height: 315px;"></iframe>';
					}

					return content;
				}
			});
		});
	});

})(jQuery);