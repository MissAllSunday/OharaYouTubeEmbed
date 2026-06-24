[b]Ohara youtube embed[/b], http://missallsunday.com

The software is licensed under MPL 2.0 license (https://www.mozilla.org/MPL/).


[b]###### Description:[/b]

You can auto-embed any valid youtube, vimeo or imgur/gifv urls and the mod will automatically convert them to videos.

This mod will also add a BBC tag: [nobbc][youtube][/youtube], [vimeo][/vimeo] and [gifv][/gifv][/nobbc] where you can post your video urls or bare IDs and it will be converted to a video/player directly in the message.

You can enable/disable the mod globally as well as set the default width and height for the videos. Currently, the mod supports the following URL structures out of the box:

[code]
http://www.youtube.com/watch?v={ID}
http://www.youtube.com/watch?v={ID}&{Parameters}
http://youtu.be/{ID}
http://www.youtube.com/watch?feature=player_embedded&v=[ID]
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


[b]###### Developer Guide: Adding New Sites[/b]

The mod uses a fully declarative and modular architecture. The SiteRegistry automatically discovers and instantiates new embedding plugins at runtime without modifying any core files.

To add a new embedding site, follow these steps:

1. Create a new PHP file inside [i]Sources/OharaYTEmbed/Sites/[/i] (e.g., [i]TikTokSite.php[/i]).
2. Make your class [b]final[/b], extend [b]OharaYTEmbed\Site\VideoProvider[/b] and define the required configuration constants.

Here is a clean boilerplate implementation:

[code]
<?php

declare(strict_types=1);

namespace OharaYTEmbed\Sites;

use OharaYTEmbed\Site\VideoProvider;

/**
 * Custom Embed Site Plugin Provider.
 */
final class MyNewSite extends VideoProvider
{
    /** @var string Unique lowercase key matching the BBC tag [mynewsite] */
    public const IDENTIFIER = 'mynewsite';

    /** @var string Regex used to extract the ID. Must isolate the video ID in the full match ($m[0]) via \K */
    public const REGEX = '%https://mynewsite\.com/video/\K\d+%i';

    /** @var string Regex to capture full URLs within a post for the auto-embedding feature. */
    public const AUTO_REGEX = '%https://mynewsite\.com/video/\d+%i';

    /** @var string The dynamic iframe player path (use '{video_id}' token) */
    public const EMBED_URL = 'https://mynewsite.com/embed/{video_id}';

    /** @var string Canonical URL to the platform's video watch page */
    public const REQUEST_URL = 'https://mynewsite.com/video/{video_id}';

    /** @var string Endpoint URL if the site utilizes an external API oEmbed JSON pipeline. Leave empty if not needed */
    public const OEMBED_URL = 'https://mynewsite.com/api/oembed.json?url={url}';

    /** @var string Base64 string or asset URI path for the 16x16 editor toolbar icon */
    public const BUTTON_IMAGE = 'data:image/gif;base64,...';
}
[/code]

[b]Architectural Guidelines:[/b]
[list]
[li][b]No-op Assets by Default:[/b] By extending VideoProvider, your site inherits the shared asynchronous event-driven jQuery component (ohvideos.js).[/li]
[li][b]Custom Enhancements:[/b] If your new plugin requires specific JavaScript SDKs, third-party styles, or specific context mutations within SMF, you can override the placeholder method:
[code]
public function registerAssets(): void
{
    loadJavaScriptFile('https://platform.mynewsite.com/embed.js', ['defer' => true]);
}
[/code][/li]
[/list]


[b]###### Changelog:[/b]

[code]
2.2 - June 2026
- Refactored entire backend codebase into a modern, strongly-typed PSR-4 compliant architecture.
- Replaced manual array mappings with an immutable DTO wrapper (EmbedParams).
- Introduced SiteRegistry utilizing reflection for automatic, drop-in plugin discovery.
- Fully separated URL validation.
- Added native Imgur [gifv] player support directly via HTML5 <video> templates.
- Complete decoupled frontend refactor (ohvideos.js) leveraging jQuery event delegation and dynamic configuration iteration.
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
- Added a minified version of ohvideos.js.
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