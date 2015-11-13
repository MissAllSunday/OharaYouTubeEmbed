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

[code]
2.1 - Nov, 2015,
- Shows video titles when available.
- Works with SMF's 2.1 WYSIWYG editor.
- Use an updated Ohara class.
- JS no longer checks the url, it just looks for any preview button field
- Site classes no longer extends the main class, they now implements iOharaYTEmbed.php interface
- Don't return an empty or false var as it causes issues with SMF's parser
- Added support for SMF 2.1 Beta 3
- Added a minified version of ohvideos.js
- Added responsiveness to videos. This will only work on responsive themes.
- Fixed an issue when two or more instances of the same video where posted, none of them would be played. 
- Re-wrote the JS code to use prototypes.
- Change the default size values to 480 x 270.

2.0 - March, 2015,
- Full OOP approach using the Ohara helper class.
- Drop compatibility with SMF 2.0.x
- Add compatibility with SMF 2.1
- Add a setting to disable auto-embedding.
- Auto-embed regex improved
- Added the option to disable the auto-embed feature via $context['ohara_disable'].
- Schema-less urls
- Change the way youtube videos are displayed, faster pages specially with multiple videos, thanks to Infernuza for the tip.
- No file edits.

1.0 - Sep 25, 2011,
-Initial Release
[/code]
