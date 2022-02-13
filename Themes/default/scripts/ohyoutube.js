/*
 Copyright (C) 2022 Michel Mendiola
 @license http://www.mozilla.org/MPL/ MPL 2.0
 @version 1.2.12
*/

class OharaYouTubeEmbed {
	constructor() {
	}

	main() {
		let rawElements = document.getElementsByClassName('youtube')
		rawElements = Array.from(rawElements);

		rawElements.map(element => {
			this.getImage(element);
			this.addPlayFrame(element)
			this.onClickEvent(element)
		});
	}

	onClickEvent(element) {
		element.onclick = () => {
			element.style.backgroundImage = 'none'

			Array.from(element.children).map(childElement => {
				childElement.style.height = '0'
			});

			this.addIframe(element)
		}
	}

	addPlayFrame(element) {
		let divPlay = document.createElement('div')

		divPlay.classList.add('youtube_play');

		element.append(divPlay)
	}

	addIframe(element) {
		let iframe = document.createElement('iframe')

		let iframeUrl = '//www.youtube.com/embed/' + element.videoID + '?autoplay=1&autohide=1';

		iframe.frameBorder = '0'
		iframe.src = iframeUrl
		// iframe.width = element.clientWidth
		// iframe.height = element.clientHeight
		iframe.allowfullscreen = 'allowfullscreen'

		element.append(iframe)
	}

	getImage(element)
	{
		let imgSrc = ''
		let index
		let len
		let imageTypes = ['hqdefault', 'mqdefault', 'sddefault', 'maxresdefault', 'default'];

		element.videoID = element.id.replace('oh_','');

		for (index = 0, len = imageTypes.length; index < len; ++index) {
			element.imgSrc = '//i.ytimg.com/vi/'+ element.videoID +'/'+ imageTypes[index] +'.jpg';

			if (element.imgSrc.width !== 0) {
				break;
			}
		}

		element.style.backgroundImage = 'url('+ element.imgSrc +')'
		element.style.backgroundSize = 'cover'

		return element
	}
}


function docReady(fnCallback) {
	if (document.readyState === "complete" || document.readyState === "interactive") {
		setTimeout(fnCallback, 1);
	} else {
		document.addEventListener("DOMContentLoaded", fnCallback);
	}
}

docReady(() => {
	const _ohObject = new OharaYouTubeEmbed();

	_ohObject.main()
})
