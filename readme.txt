[center][color=purple][size=5][b]Ohara YouTube Embed [/b][/size][/color]
[b]Author:[/b] [url=http://missallsunday.com]Miss All Sunday[/url][/center]

[color=red][b][size=16pt]Attention:[/size][/b][/color]
On install make sure to mark the "[b]Install in Other Themes[/b]" checkbox.


[color=purple][b][size=12pt]Description[/size][/b][/color]

[b]For SMF 2.0.x only[/b]

[b]Version 1.2.x and above requires [color=purple]PHP 5.3[/color] or greater[/b]

You can auto-embed any valid youtube or vimeo urls and the mod will automatically convert them to videos.

This mod will also add a BBC tag:  [nobbc][youtube][/youtube] and [vimeo][/vimeo][/nobbc]  where you can post your youtube or vimeo urls and it will be converted to a video directly in the message.

You can enable/disable the mod as well as set the width and height for the videos, currently the mod support the following youtube and vimeo urls:

[code]
http://www.youtube.com/watch?v={ID}
http://www.youtube.com/watch?v={ID}&{Parameters}
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

-English/utf8
-Spanish_latin/utf8
-Spanish_es/utf8


[color=purple][b][size=12pt]Changelog[/size][/b][/color]
[code]
1.2.4 - August 2, 2015,
- Added responsiveness to videos.
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