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
			if (rawImageUrl && rawImageUrl !== '') {
				const imageUrl = decodeURIComponent(rawImageUrl);

				$container.css({
					'background-image': 'url(' + imageUrl + ')',
					'background-size': 'cover',
					'background-position': 'center',
					'cursor': 'pointer',
					'position': 'relative'
				});
			} else {
				$container.css({
					'background-color': '#000000',
					'cursor': 'pointer',
					'position': 'relative'
				});
			}

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
		const embedUrl = decodeURIComponent($container.attr('data-ohara_embed_url'));

		if (!embedUrl || embedUrl === 'undefined') {
			return;
		}

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
	});

})(jQuery);