[b]Ohara youtube embed[/b], http://missallsunday.com

The software is licensed under MPL 2.0 license (https://www.mozilla.org/MPL/).


[b]###### Description:[/b]

You can auto-embed any valid youtube, vimeo or imgur/gifv urls and the mod will automatically convert them to videos.

This mod will also add a BBC tag: [nobbc][youtube][/youtube], [vimeo][/vimeo] and [gifv][/gifv][/nobbc] where you can post your video urls or bare IDs and it will be converted to a video/player directly in the message.

You can enable/disable the mod globally as well as set the default width and height for the videos. Currently, the mod supports the following URL structures out of the box:

[code]
www.youtube.com/watch?v={ID}
www.youtube.com/watch?v={ID}&{Parameters}
youtu.be/{ID}
www.youtube.com/watch?feature=player_embedded&v=[ID]
vimeo.com/[ID]
vimeo.com/channels/[channel name]/[ID]
vimeo.com/groups/[Group name]/videos/[ID]
i.imgur.com/[ID].gifv
i.imgur.com/[ID].webm
[/code]


[b]###### Requirements[/b]

[list]
[li]SMF 2.1.x.[/li]
[li]PHP 8.0 or greater.[/li]
[/list]


[b]###### Languages:[/b]

[list]
[li]English[/li]
[li]Spanish_latin[/li]
[li]Spanish_es[/li]
[/list]

This mod uses the following icons:

- Imgur icon by [url="https://icons8.com"]Icons8[/url]
- YouTube icon by [url="https://freepik.com"]Freepik[/url]
- Vimeo icon by [url="https://www.flaticon.com/authors/fathema-khanom"]Fathema Khanom[/url]


[b]###### Changelog:[/b]

[code]
2.2 - June 2026
- Refactored entire backend codebase into a modern, strongly-typed PSR-4 compliant architecture.
- Replaced manual array mappings with an immutable DTO wrapper (EmbedParams).
- Introduced SiteRegistry utilizing reflection for automatic, drop-in plugin discovery.
- Fully separated URL validation.
- Added native Imgur [gifv] player support directly via HTML5 <video> templates.
- Complete decoupled frontend refactor (OharaYTEmbed.js) leveraging jQuery event delegation and dynamic configuration iteration.
- Standardized full PHPUnit test-driven regression suites via dataProviders for default integrations.
- Added Sources/OharaYTEmbed/Contracts/EmbedSiteInterface.php to allow adding more sites easily via contract/drop down.

2.1 - Nov 2015,
- Shows video titles when available.
- Works with SMF's 2.1 WYSIWYG editor.
- Use an updated Ohara class.
- JS no longer checks the url, it just looks for any preview button field.
- Site classes no longer extend the main class, they now implement iOharaYTEmbed.php interface.
- Don't return an empty or false var as it causes issues with SMF's parser.
- Added support for SMF 2.1 Beta 3.
- Added a minified version of OharaYTEmbed.js.
- Added responsiveness to videos. This will only work on responsive themes.
- Fixed an issue when two or more instances of the same video were posted, none of them would be played.
- Re-wrote the JS code to use prototypes.
- Changed the default size values to 480 x 270.

2.0 - March 2015,
- Full OOP approach using the Ohara helper class.
- Dropped compatibility with SMF 2.0.x.
- Added compatibility with SMF 2.1.
- Added a setting to disable auto-embedding.
- Auto-embed regex improved.
- Added the option to disable the auto-embed feature via $context['ohara_disable'].
- Schema-less urls.
- Changed the way youtube videos are displayed, faster pages especially with multiple videos, thanks to Infernuza for the tip.
- No file edits.

1.0 - Sep 25, 2011,
- Initial Release.
[/code]