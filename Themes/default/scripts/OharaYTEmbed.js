(function ($) {
	'use strict';

	const OharaEmbedPlayer = function () {
		this.masterSelector = '.oharaEmbed';
		this.init();
	};

	OharaEmbedPlayer.prototype.init = function () {
		$(this.masterSelector).each((index, element) => this.setupPreview($(element)));

		$(document).on('click', this.masterSelector, (e) => {
			e.preventDefault();

			const $mainContainer = $(e.target).closest(this.masterSelector);

			this.playVideo($mainContainer);
		});
	};

	OharaEmbedPlayer.prototype.setupPreview = function ($container) {
		const rawImageUrl = $container.attr('data-ohara_thumbnail_url');
		const aspectRatio = $container.attr('data-ohara_aspect_ratio') || '16 / 9';
		const originalWidth = parseInt($container.attr('data-ohara_width')) || 480;

		$container.css({
			'width': '100%',
			'max-width': originalWidth + 'px',
			'height': 'auto',
			'aspect-ratio': aspectRatio,
			'box-sizing': 'border-box'
		});

		if (rawImageUrl) {
			if (rawImageUrl !== '') {
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

	OharaEmbedPlayer.prototype.playVideo = function ($container) {
		const embedUrl = decodeURIComponent($container.attr('data-ohara_embed_url'));
		let aspectRatio = $container.attr('data-ohara_aspect_ratio') || '16 / 9';

		if (!embedUrl || embedUrl === 'undefined') {
			return;
		}

		const parts = aspectRatio.split('/');
		if (parts.length === 2) {
			const width = parseFloat(parts[0]);
			const height = parseFloat(parts[1]);
			if (!isNaN(width) && !isNaN(height)) {
				const ratio = width / height;
				if (ratio < 1.3) {
					const adjustedHeight = height + (height * 0.25);
					aspectRatio = width + ' / ' + adjustedHeight;
				}
			}
		}

		$container.css({
			'display': 'block',
			'position': 'relative',
			'width': '100%',
			'height': 'auto',
			'aspect-ratio': aspectRatio
		});

		const $iframe = $('<iframe/>', {
			'frameborder': '0',
			'src': embedUrl,
			'allowfullscreen': '',
			'allow': 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
			'class': 'oharaEmbedIframe',
			'style': 'display: block; width: 100%; height: 100%; border-radius: 4px; position: absolute; top: 0; left: 0; overflow: hidden;'
		});

		$container.empty().css('background-image', 'none').append($iframe);
	};

	$(function () {
		new OharaEmbedPlayer();
	});

})(jQuery);