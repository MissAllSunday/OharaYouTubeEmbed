[center][color=purple][size=5][b]Ohara YouTube Embed [/b][/size][/color]
[b]Author:[/b] [url=http://missallsunday.com]Suki[/url][/center]

[color=red][b][size=16pt]Attention:[/size][/b][/color]
On install make sure to mark the "[b]Install in Other Themes[/b]" checkbox.


[color=purple][b][size=12pt]Description[/size][/b][/color]

[b]For SMF 2.1.x only[/b]

[b]Versions 1.2 and above requires [color=purple]PHP 5.3[/color] or greater[/b]

You can auto-embed any valid youtube or vimeo urls and the mod will automatically convert them to videos.

This mod will also add a BBC tag:  [nobbc][youtube][/youtube] and [vimeo][/vimeo][/nobbc]  where you can post your youtube or vimeo urls and it will be converted to a video directly in the message.

You can enable/disable the mod as well as set the width and height for the videos, currently the mod support the following youtube and vimeo urls:

[code]
http://www.youtube.com/watch?v={ID}
http://www.youtube.com/watch?v={ID}&{Parameters}
http://youtu.be/{ID}
http://www.youtube.com/watch?feature=player_embedded&v=[ID]
vimeo.com/[ID]
vimeo.com/channels/[channel name]/[ID]
vimeo.com/groups/[Group name]/videos/[ID]
[/code]


[color=purple][b][size=12pt]Settings[/size][/b][/color]

- Admin->Configuration->Modifications


[color=purple][b][size=12pt]Languages[/size][/b][/color]

-English/utf8
-Spanish_latin/utf8
-Spanish_es/utf8


[color=purple][b][size=12pt]Changelog[/size][/b][/color]

[b]2.0 - June, 2015,[/b]
- Full OOP approach using the Ohara helper class.
- Drop compatibility with SMF 2.0.x
- Add compatibility with SMF 2.1
- Add a setting to disable auto-embedding.
- Auto-embed regex improved
- Added the option to disable the auto-embed feature via $context['ohara_disable'].
- Schema-less urls
- Change the way youtube videos are displayed, faster pages specially with multiple videos, thanks to Infernuza for the tip.
- No file edits.

[b]1.2.1 - March, 2015,[/b]
- Added an enable/disable setting for auto-embedding.
- Regex updates.
- Usage of closures
- The auto embed feature now directly replaces the url with an iframe.
- Using the universal embed code for vimeo urls, fall back to the oembed API.
- $txt['OYTE_unvalid_link'] now uses  sprintf().
- Added an extra file edit to make sure both places gets edited.
- Schema-less urls.
- Responsive iframes, only works if you're using a fully responsive theme.

[b]1.2 - March 05, 2014,[/b]
- Added auto-embed feature.
- Add support for old [nobbc][yt][/yt][/nobbc] tags
- Add support for [nobbc][vimeo][/vimeo][/nobbc]
- Requires PHP 5.3 or greater.

[b]1.1 - April 19, 2013,[/b]
- Fix the http/https url issue.
- Fixed the parsing smiles after a video issue.
- Fixed the pass by reference issue.
- Updated the regex to include more valid urls.

[b]1.0.1 - Dic 28, 2011,[/b]
-Fix the youtube redirect page error if you use the initial tag alone: [youtube]

[b]1.0 - Sep 25, 2011,[/b]
-Initial Release
