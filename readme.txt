[center][color=purple][size=5][b]Ohara YouTube Embed [/b][/size][/color]
[b]Author:[/b] [url=http://missallsunday.com]Suki[/url][/center]

[color=red][b][size=16pt]Attention:[/size][/b][/color]
On install make sure to mark the "[b]Install in Other Themes[/b]" checkbox.


[color=purple][b][size=12pt]Description[/size][/b][/color]

[b]For SMF 2.0.x only[/b]

[b]Version 1.2 and above requires [color=purple]PHP 5.3[/color] or greater[/b]

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
 * This SMF modification is subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this SMF modification except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 [/pre]

 
[color=purple][b][size=12pt]Settings[/size][/b][/color]

- Admin->Configuration->Modifications


[color=purple][b][size=12pt]Languages[/size][/b][/color]

-English/utf8
-Spanish_latin/utf8
-Spanish_es/utf8


[color=purple][b][size=12pt]Changelog[/size][/b][/color]

[b]1.3 - August 23, 2014,[/b]
- Added an enable/disable setting for auto-embedding.
- Regex updates.
- Usage of closures
- The auto embed feature now directly replaces the url with an iframe.
- Using the universal embed code for vimeo urls, fall back to the oembed API.
- $txt['OYTE_unvalid_link'] now uses  sprintf().
- Added an extra file edit to make sure both places gets edited.

[b]1.2 - March 05, 2014,[/b]
- Added auto-embed feature.
- Add support for old [nobbc][yt][/yt][/nobbc] tags
- Add support for [nobbc][vimeo][/vimeo][/nobbc]

[b]1.1 - April 19, 2013,[/b]
- Fix the http/https url issue.
- Fixed the parsing smiles after a video issue.
- Fixed the pass by reference issue.
- Updated the regex to include more valid urls.

[b]1.0.1 - Dic 28, 2011,[/b]
-Fix the youtube redirect page error if you use the initial tag alone: [youtube]

[b]1.0 - Sep 25, 2011,[/b]
-Initial Release
