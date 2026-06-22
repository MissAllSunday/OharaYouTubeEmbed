/**
 * Ohara YouTube Embed
 */

(function ($) {
	'use strict';
	const OharaEmbedPlayer = function () {
		this.masterSelector = '.oharaEmbed';
		this.init();
	};

	OharaEmbedPlayer.prototype.init = function () {
		const self = this;

		// Iterar sobre cada contenedor para inicializar su estado visual (Vista Previa)
		$(this.masterSelector).each(function () {
			self.setupPreview($(this));
		});

		// DELEGACIÓN DE EVENTOS: Un solo listener global para todo el foro
		$(document).on('click', this.masterSelector, function (e) {
			e.preventDefault();
			self.playVideo($(this));
		});
	};

	/**
	 * Configura la miniatura (thumbnail) de fondo obtenida por oEmbed
	 */
	OharaEmbedPlayer.prototype.setupPreview = function ($container) {
		// Recuperar la imagen resuelta nativamente por tu DTO de PHP
		const imageUrl = $container.data('ohara_image_url') || $container.attr('data-ohara_image_url');

		if (imageUrl) {
			$container.css({
				'background-image': 'url(' + decodeURIComponent(imageUrl) + ')',
				'background-size': 'cover',
				'background-position': 'center',
				'cursor': 'pointer',
				'position': 'relative'
			});

			// Inyectar un botón de play genérico flotante si no existe
			if ($container.find('.ohara-play-btn').length === 0) {
				$container.append('<div class="ohara-play-btn" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:64px; height:64px; background:rgba(0,0,0,0.7); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px;">▶</div>');
			}
		}
	};

	/**
	 * Reemplaza el contenedor/miniatura por el Iframe real en tiempo de ejecución
	 */
	OharaEmbedPlayer.prototype.playVideo = function ($container) {
		// Encontrar qué configuración de sitio le pertenece leyendo las clases
		let siteInfo = null;
		$.each(_ohSites, function (index, site) {
			if ($container.hasClass(site.identifier)) {
				siteInfo = site;
				return false; // Break
			}
		});

		if (!siteInfo) return;

		// Leer los parámetros sanitizados que el DTO inyectó en el HTML
		const rawData = $container.data('ohara_' + siteInfo.identifier) || $container.attr('data-ohara_' + siteInfo.identifier);
		let videoData = {};

		try {
			// Intentar parsear el JSON de datos. Si falla (porque es un ID plano), construir el objeto básico
			videoData = typeof rawData === 'object' ? rawData : $.parseJSON(decodeURIComponent(rawData));
		} catch (e) {
			videoData = { video_id: rawData };
		}

		const videoId = videoData.video_id || rawData;
		if (!videoId) return;

		// Construir la URL final del iframe reemplazando el token dinámico
		const embedUrl = siteInfo.embedUrl.replace('{video_id}', videoId);

		// Resolver dimensiones calculadas
		const width = $container.width() || videoData.width || 480;
		const height = $container.height() || videoData.height || 270;

		// Crear el elemento Iframe puro
		const $iframe = $('<iframe/>', {
			'frameborder': '0',
			'src': embedUrl,
			'width': '100%',
			'height': '100%',
			'allowfullscreen': '',
			'allow': 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
			'class': 'oharaEmbedIframe'
		});

		// Limpiar el contenedor (quitar botón e imagen) e inyectar el reproductor activo
		$container.empty().css('background-image', 'none').append($iframe);
	};


	// =========================================================================
	// 2. Extensión Dinámica Automatizada para SCEditor (BBCode)
	// =========================================================================
	$(function () {
		// Inicializar el reproductor envolvente en el DOM
		new OharaEmbedPlayer();

		if (typeof $.sceditor === 'undefined' || _ohSites === 'undefined') {
			return;
		}

		// Bucle declarativo: Registra de forma automática CUALQUIER sitio nuevo que agregues al Backend
		$.each(_ohSites, function (index, site) {

			$.sceditor.plugins.bbcode.bbcode.set(site.identifier, {
				allowsEmpty: true,
				tags: {
					iframe: {}
				},
				// Convierte el elemento HTML del editor de vuelta a la etiqueta BBCode [tag]id[/tag]
				format: function (element, content) {
					const $el = $(element);
					const videoId = $el.attr('data-' + site.identifier + '-id');
					return videoId ? '[' + site.identifier + ']' + videoId + '[/' + site.identifier + ']' : content;
				},
				// Renderiza el BBCode dentro del área WYSIWYG del editor usando una vista previa limpia
				html: function (token, attrs, content) {
					const cleanContent = content.trim();
					let match = null;

					// Si pasaron una URL completa, intentar extraer el ID usando la Regex que mandó el backend
					if (site.regex) {
						const regexPattern = new RegExp(site.regex.replace(/^\/|\/[a-z]*$/g, ''), 'i');
						match = cleanContent.match(regexPattern);
					}

					const videoId = match ? match[0] : cleanContent;

					if (videoId !== '') {
						// Pintamos una simulación responsiva dentro del editor de texto
						return '<iframe frameborder="0" src="' + site.embedUrl.replace('{video_id}', videoId) + '" data-' + site.identifier + '-id="' + videoId + '" class="oharaEmbedIframe" style="width: 420px; height: 315px;"></iframe>';
					}

					return content;
				}
			});
		});
	});

})(jQuery);
