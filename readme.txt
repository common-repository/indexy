=== Indexy ===
Contributors:      MrBunnyFlop
Donate link:       http://www.damnleet.com/indexy
Tags:              blog, content, custom post type, glossary, term, highlight, free, page, post, shortcode, widget
Requires at least: 3.0
Tested up to:      4.4
Stable tag:        1.0.2
License:           GPLv2
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Indexy allows you to create and manage a glossary of terms, and can automatically highlight terms when you use them in
your posts.

== Description ==

Indexy is a plugin for WordPress that lets you create a glossary of terms, write definitions, and highlight those terms
inside your posts. Visitors can hover over any highlighted terms to see a short excerpt, or click them for the full
definition. As an author, you get several shiny [shortcodes](http://www.damnleet.com/indexy/using-shortcodes) to enhance
your website, as well as [widgets](http://www.damnleet.com/indexy/using-widgets) to show off your glossary and make
navigation easy.

Using Indexy, you can create glossary pages, which function similarly to regular posts or pages in WordPress. In
addition to the title of the article, you can define any number of synonyms. Whenever any of the terms come up in a page
or blog post, they will be highlighted according to the rules set in the plugin's options.

= Acknowledgements =

I would like to thank the following people:

* Thomas Griffin and Gary Jones, for the [TGM Plugin Activation](http://tgmpluginactivation.com/) library.
* The authors of the [Meta Box](https://wordpress.org/plugins/meta-box/) plugin.

== Installation ==

1. Install Indexy either via the WordPress.org plugin directory, or by manually uploading the files to your server.
2. Navigate to the Plugins screen in the WordPress administration panel and activate Indexy.
3. Go through the Indexy settings panel and configure the plugin as you like.
4. If you like, customize the look of the plugin by creating a custom indexy.css file in your theme's folder. You can
   also create a custom template for glossary pages.
5. That's it!

== Frequently Asked Questions ==

= Are there any special requirements to use Indexy? =

In order to run Indexy, you'll need a reasonably recent version of WordPress, on a server that runs PHP 5 or newer. With
most hosting companies, that shouldn't be a problem.

= Will Indexy work with WordPress version x? =

Indexy is developed and tested using the most recent version of WordPress, and any new release will, to the best of my
knowledge, always work with the latest version(s) of WordPress. Older releases in the same major version of WordPress
should generally work. For any versions older than that, Indexy may or not work; you'll have to test it to find out.

The plugin *should* work correctly on Multisite installations, however I don't specifically test for that.

= How can I customize Indexy? =

For information on customizing Indexy, please refer to
[the documentation](http://www.damnleet.com/indexy/customizing-indexy).

= Why does Indexy prompt me to install the Meta Box plugin? =

The [Meta Box plugin](https://wordpress.org/plugins/meta-box/) helps Indexy display boxes with settings on the add/edit
post page. If you don't have it installed, nothing will break, but you'll miss out on some of the plugin's features.

Other than that, Meta Box does not affect your WordPress website; it is not visible to your site's visitors, nor does it
do anything else. (You also don't need the premium versions of Meta Box.)

= Can Indexy be translated to language x? =

Yes, Indexy can be translated, however translations (other than English) are currently included.

A .po file is included in the plugins *languages/* folder; you can open this file using [Poedit](https://poedit.net/)
(or any other similar editor of your choice) and make your own translation of the plugin. If you would like to
contribute your translation to the plugin, I would love to include it in the plugin's distribution; please contact me at
**blah**.

== Screenshots ==

1. A sample glossary index page. Indexy can automatically insert a listing like this anywhere you use the [indexy_index]
   shortcode.
2. Terms are automatically highlighted in your articles, with optionally an excerpt of the main glossary article
   displayed when the user hovers over a term.
3. The Glossary Index widget provides a scrollable index of your glossary terms.
4. The options panel for each individual glossary article allows you to define synonyms, control highlighting, and
   choose whether or not to include that term on the index page (see screenshot 1) and the index widget (screenshot 3).
5. Indexy is highly customizable, with fine-grained control over how terms are highlighted.

== Changelog ==

= 1.0.2 =
* Fixed a minor issue which caused warnings in some cases (issue reported by KTS915).

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.2 =
* Fixed a minor issue which caused warnings in some cases (issue reported by KTS915).

= 1.0 =
Initial release.
