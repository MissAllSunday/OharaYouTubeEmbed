[center][color=purple][size=5][b]Ohara YouTube Embed [/b][/size][/color]
[b]Author:[/b] [url=https://missallsunday.com]Suki[/url][/center]

[color=red][b][size=16pt]Attention:[/size][/b][/color]
When installing make sure to mark the "[b]Install in Other Themes[/b]" checkbox.


[color=purple][b][size=12pt]Description[/size][/b][/color]

[b]For SMF 2.0.x only[/b]

[b]Version 1.2.15 and above requires [color=purple]PHP 7.1[/color] or greater[/b] [color=purple]curl ext[/color] and [color=purple]ECMAScript 2015[/color] or greater

You can auto-embed any valid youtube or vimeo urls and the mod will automatically convert them to videos.

This mod will also add a BBC tag:  [nobbc][youtube][/youtube] and [vimeo][/vimeo][/nobbc]  where you can post your youtube or vimeo urls and it will be converted to a video directly in the message.

You can enable/disable the mod as well as set the width and height for the videos, currently the mod support the following youtube and vimeo urls:

[code]
http://www.youtube.com/watch?v={ID}
http://www.youtube.com/watch?v={ID}&{Parameters}
http://www.m.youtube.com/watch?v={ID}
http://youtu.be/{ID}
http://www.youtube.com/watch?feature=player_embedded&v=[ID]
http://vimeo.com/[ID]
http://vimeo.com/channels/[channel name]/[ID]
http://vimeo.com/group/[ID]
http://vimeo.com/album/[ID]
[/code]

[color=purple][b][size=12pt]License[/size][/b][/color]
[pre]
This Source Code Form is subject to the terms of the Mozilla Public
License, v. 2.0. If a copy of the MPL was not distributed with this
file, You can obtain one at http://mozilla.org/MPL/2.0/.
 [/pre]


[color=purple][b][size=12pt]Settings[/size][/b][/color]

- Admin->Configuration->Modifications


[color=purple][b][size=12pt]Languages[/size][/b][/color]

- English/utf8
- Spanish_latin/utf8
- Spanish_es/utf8
- Polish/utf8

I welcome translations, please post them on the mod's support topic.


[color=purple][b][size=12pt]Changelog[/size][/b][/color]
[code]
1.2.15 - Dec 18, 2023
- Fix setting for allowFullScreen
- Don't need to parse new lines and spaces
- Add phpunit tests
- Mod now requires PHP 7.1

1.2.14 - May 06, 2023
- Remove imgur support
- Fix correctly displaying smileys after posting a video
- Use curl to make request to vimeo for getting the oembed info.

1.2.13 - April 08, 2022
- Add min screen size setting

1.2.12 - Feb 13, 2022
- Remove jQuery dependency
- Add support for ECMAScript 2015

1.2.11 - May 07, 2020
- Add French translation, thanks to BrunoR
- Correctly delete language files

1.2.10 - March 10, 2019
- Add Polish translation, thanks to jsx and FishingManMatt
- Correctly delete language files on uninstall
- Spanish word corrections

1.2.9 - March 12, 2017
- Fix the "/>" appearing on autoembed
- Fix support for gifv
- Add a link to the video if JavaScript is disabled

1.2.8 - Oct 25, 2016
- Improve regex for gifv and youtube urls.
- Pass the ID when using url auto parsing, prevents using another regex to get it.
- Use global scope inside closures.

1.2.7 - Aug 12, 2016,
- Set basedWidth and basedHeight to prevent weird behavior on non responsive themes.

1.2.6 - May 29, 2016,
- Add support for full screen videos.
- Remove multiple calls to this.responsive().
- Check the width and height of parent div before applying changes.

1.2.5 - February 18, 2016,
- Added support for imgur gifv format
- Move the css and js calls to integrate_load_theme hook.

1.2.4 - August 2, 2015,
- Added responsiveness to videos. This will only work on responsive themes.
- Fixed an issue when two or more instances of the same video where posted, none of them would be played.
- Re-wrote the JS code to use prototypes.
- Change the default size values to 480 x 270.

1.2.3 - June 26, 2015,
- Added a minified js file.
- Added support for using the youtube ID as param.
- License change to MPL 2.0.

1.2.2 - April 05, 2015,
- Force loading the oharaEmbed.css file from the default theme.
- Add more options to show video preview images
- Add support for previewing a message
- Improve handling of youtube thumbnails and overall JS improvements.
- Add a setting to enable/disable the autoembed feature. This was suppose to be added a long time ago but dunno why I forgot to do it...
- Missed to check the master setting.

1.2.1 - March 09, 2015,
- Added the option to disable the autoembed feature via $context['ohara_disable'].
- Use closures instead of create_function()
- Change the way youtube videos are displayed, faster pages specially with multiple videos, thanks to Infernuza for the tip.

1.2 - March 05, 2014,
- Added auto-embed feature.
- Add support for old [nobbc][yt][/yt][/nobbc] tags
- Add support for [nobbc][vimeo][/vimeo][/nobbc]

1.1 - April 19, 2013,
- Fix the http/https url issue.
- Fixed the parsing smiles after a video issue.
- Fixed the pass by reference issue.
- Updated the regex to include more valid urls.

1.0.1 - Dic 28, 2011,
-Fix the youtube redirect page error if you use the initial tag alone: [youtube]

1.0 - Sep 25, 2011,
-Initial Release
[/code]
