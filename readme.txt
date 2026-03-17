=== wBuild Affiliate Links Sidebar ===
Contributors: mcnallen
Tags: affiliate, amazon, links, shortcode, sidebar
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Auto-detects affiliate links in your content and displays them in a sidebar widget or shortcode.

== Description ==

**wBuild Affiliate Links Sidebar** makes it easy to showcase the affiliate products you already mention in your content — without manual copy-pasting.

Unlike other affiliate plugins that require manual imports or API keys, this plugin automatically detects links you've already added to your content and displays them, no extra work needed.

The plugin scans the current post/page content for affiliate links (starting with your chosen prefix, e.g. `https://amzn.to/`) and automatically displays them in:

– A beautiful sidebar widget (Appearance → Widgets)
– Or an inline shortcode block `[wbuild-affiliate-links]`

Perfect for Amazon Associates, bloggers, reviewers, and content creators who want to highlight recommended products without extra work.

**Free version features**
– Single affiliate prefix (e.g. Amazon amzn.to)
– Up to 5 links displayed per page/post (configurable from 1–5)
– Customizable title, disclosure text, link behavior (new tab, sponsored, noopener, etc.)
– Clean, modern design with hover effects
– Mobile/desktop visibility control for shortcode
– Custom CSS options for widget and shortcode

**Pro version adds**
– Unlimited links per page
– Multiple affiliate programs/prefixes at once (Amazon, ShareASale, etc.)
– No display limit (or custom max)
– More link behavior options
– [View Pro details →](https://wbuild.dev/affiliate-links-sidebar/)

Great for product roundups, reviews, buying guides, comparison posts — anywhere you already link to affiliate products.

== Installation ==

1. Upload the `wbuild-affiliate-links-sidebar` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to **Settings → wBuild Affiliate Sidebar** to set your affiliate prefix (default: https://amzn.to/) and customize titles/disclosure
4. Add the widget to any sidebar via **Appearance → Widgets**
5. Or place the shortcode `[wbuild-affiliate-links]` anywhere in your post/page content

== Frequently Asked Questions ==

= What affiliate programs are supported? =
Any program that uses a consistent URL prefix (e.g. `https://amzn.to/`, `https://rstyle.me/`, etc.). The free version supports one prefix; Pro supports multiple at the same time.

= How does it find the links? =
It scans the current post/page content for any hyperlinks that begin with your prefix. It then shows the link text (or URL fallback) in a clean list.

= Can I use it without a sidebar? =
Yes — use the shortcode `[wbuild-affiliate-links]` anywhere: in the content, in a block, in a footer, etc.

= Is there a limit on links? =
Free version: maximum 5 links per page/post (configurable 1–5).
Pro version: unlimited or custom max.

= Does it work with block themes / Full Site Editing? =
Yes — the widget works in classic sidebars and the shortcode works everywhere.

= Can I style it differently? =
Yes — use the Custom CSS fields in settings, or override the classes `.affiliate-links-widget` and `.affiliate-links-shortcode` in your theme.

== Screenshots ==

1. Sidebar widget displaying affiliate links on a live page
2. Settings page – prefix, titles, disclosure, and link behavior
3. Settings page – custom CSS and shortcode visibility options
4. Shortcode output displayed inline within post content
5. Mobile responsive view of the affiliate links widget
6. Widget configuration in WordPress admin

== Changelog ==

= 1.8.0 =
* Integrated Freemius SDK for in-dashboard upgrades, licensing, and Pro delivery
* Merged free and Pro codebases into a single plugin with feature gating
* Pro features (unlimited links, multiple prefixes) now unlock via Freemius license or trial
* Added upgrade CTA in settings page for free users
* Added Pro badge on settings page for paying users
* Refactored link extraction and display limits into shared helper functions
* Added auto-deactivation support for seamless free-to-Pro version switching

= 1.7.1 =
* Security: CSS fields (sidebar_css, shortcode_css) now sanitized with wp_strip_all_tags() on save
* Security: Individual $_POST keys accessed directly instead of processing entire $_POST array
* Security: Added validation for credit_location against allowed values
* Prefixed shortcode name from `affiliate-links` to `wbuild-affiliate-links` for uniqueness
* Used absint() for max_links_display input

= 1.7.0 =
* Rebranded to wBuild Affiliate Links Sidebar under wBuild.dev
* All CSS now properly enqueued via wp_register_style and wp_add_inline_style (no more inline style tags)
* Admin CSS properly loaded via admin_enqueue_scripts hook
* Credit link now points to wBuild.dev plugin page (opt-in only, defaults to never)
* Updated text domain to wbuild-affiliate-links-sidebar
* All user-facing strings wrapped in translation functions
* Added rel="noopener noreferrer" to all external admin links
* Function prefixes updated to wbuild_als_ for consistency

= 1.6.12 =
* Security & standards improvements: proper escaping, wp_strip_all_tags(), PHPCS ignore comments for core widget args
* Version bump for resubmission with review fixes

= 1.6.11 =
* Added user-configurable Max Links to Display (1–5) in free version
* Added clear in-settings instructions for using the widget and shortcode
* Updated admin menu titles and page slugs for better consistency and discoverability
* Improved settings defaults merging with wp_parse_args for smoother upgrades
* Tested compatibility with WordPress 6.9.1

= 1.6.0 =
* Introduced link behavior controls (new tab, sponsored, nofollow, noopener)
* Added custom CSS fields for widget and shortcode styling
* Enhanced mobile/desktop visibility toggle for shortcode output

= 1.5.0 =
* Clarified plugin description and UI messaging: "Limited to 5 links per page in free version"
* Improved link extraction reliability and duplicate removal
* Minor security hardening and code cleanup

= 1.4.2 =
* Small UI improvements for better readability in settings
* Fixed minor styling edge cases on mobile

= 1.4.0 =
* Updated default disclosure text to include combined Amazon Associates + general affiliate statement
* Added optional plugin credit footer (configurable location)

= 1.3.0 =
* Default link attributes changed to rel="sponsored noopener" + target="_blank" for better compliance and UX
* Added disclosure text helpers and examples in settings

= 1.0.0 =
* Initial public release
* Core functionality: scan page content for affiliate links and display in widget or shortcode
* Single prefix support, basic styling, disclosure field

== Upgrade Notice ==

= 1.8.0 =
Freemius integration added for seamless in-dashboard upgrades to Pro. Free features unchanged. Pro users get unlimited links and multiple affiliate prefixes.

= 1.7.1 =
Security and standards fixes: proper input sanitization, individual $_POST access, validated options, and prefixed shortcode name. **Breaking change:** shortcode is now `[wbuild-affiliate-links]` — update any existing shortcode blocks.

= 1.7.0 =
Rebranded to wBuild Affiliate Links Sidebar. All CSS properly enqueued per WordPress standards. Credit links to wBuild.dev (opt-in). Updated text domain.

== Other Notes ==

This is the **free version** of wBuild Affiliate Links Sidebar.

Love the plugin and want unlimited links, multiple affiliate programs, no display cap, and more customization?
Check out the Pro version: https://wbuild.dev/affiliate-links-sidebar/
