# Feinwerk Live Dashboard

## Description

**See what happens on your website right now: who is on it, what they read, where they come from and what they buy.**

Matomo records every visit the moment it happens. Feinwerk Live Dashboard puts those visits onto one board that
updates itself every few seconds, for a team screen, a campaign launch or a quick look in between, and keeps the load
on your database small while many people watch.

- **Live at a glance:** visitors active now, new visits per minute as a bar chart, visits, pageviews, conversions and
  revenue, plus today's totals. Numbers that change light up briefly, so you notice them from the corner of your eye.
- **Your time window:** switch every list and key number between the last 3, 10, 30 or 60 minutes.
- **Live map:** the country with the most visits on Matomo's own country map, its regions shaded by their visits and
  its cities at their place. Click another country in the list beside it, or open the world map and click a country.
- **Top lists:** pages viewed now, top pages, channels, campaigns, campaign sources (with Marketing Campaigns
  Reporting), countries, device types, goals and orders, events, downloads, outlinks and site search. A small green or
  red number shows how many places a row moved since the last update.
- **Latest visits:** the newest visits with country, source, duration, current page and actions, and icons for a
  reached goal, an order, an abandoned cart and a returning visitor.
- **Dashboard widgets:** Live: Overview, Live: Pages, Live: Sources and goals, Live: Latest visits and Live: Map, so
  you put exactly the part you need on a Matomo dashboard. Full screen turns the page into a wall display.
- **Built for busy websites:** everyone who looks at the same website shares one calculation, the lists switch to a
  sample on heavy traffic while the key numbers stay exact, and the dashboard calculates less often by itself when the
  database is busy. In our load test with 1,000 new visits per minute, 50 people watching needed less than 2 % of one
  database CPU core.
- **Respects your privacy settings:** the latest visits follow Matomo's visits log and data rounding settings, and the
  board never shows IP addresses, visitor IDs or the city of a single visit.
- **Light and dark mode, in English, German, French, Spanish and Italian.**

**Works with Feinwerk Dashboard Sharing (free):** put the live widgets on a dashboard and share it with your team.

**Get started:** open *Visitors › Live Dashboard*, or add the widgets of the category *Live Dashboard* to a dashboard.

**More Plugins for Matomo by Feinwerk:** install [Feinwerk Connector](https://plugins.matomo.org/FeinwerkMarketplaceConnector)
from the Matomo Marketplace to discover, install and update all Feinwerk plugins, free and paid, right in your Matomo.
Documentation: [plugins.feinwerk.io](https://plugins.feinwerk.io/en/plugins/FeinwerkLiveDashboard).

## Requirements

- Matomo 5.0 or later (Matomo 5 and Matomo 6), PHP 8.1 or later
- Versions: one version 5.x of this plugin for Matomo 5 and Matomo 6

## Installation

With one click through the Feinwerk connector (**Administration → Feinwerk Plugins → Discover**) or as a ZIP
file, see [Installing plugins](https://plugins.feinwerk.io/en/docs/installing-plugins). To install the ZIP
by hand, copy the folder to `plugins/FeinwerkLiveDashboard` and activate it under
**Administration → Plugins → Manage Plugins** or on the command line:

```
./console plugin:activate FeinwerkLiveDashboard
```

## Usage

Open **Visitors → Live Dashboard**. The page updates by itself; there is nothing to set up. Choose the time window at
the top, switch pages between URL and title, and use **Full screen** for a wall display. On a dashboard, add the
widgets of the category **Live Dashboard**.

Everybody with view access to a website sees its live dashboard. The list of latest visits follows Matomo's own
settings: it is not shown when the visits log is switched off for the website or the whole Matomo, or when Matomo
rounds report numbers for privacy. The map shows a city only when it has at least 3 visits in the time window.

### Options for administrators

In Matomo's **Administration › System › General settings**, section **Feinwerk Live Dashboard**: **Show calculation
time and sampling** (on by default) adds a small note after the status of the live dashboard and its widgets, for
example "5 ms, lists 12 ms · no sample": how long the last calculation of the newest numbers and of the lists took, and
whether the lists are estimated from a sample.

Optional, in `config/config.ini.php`:

```ini
[FeinwerkLiveDashboard]
; how often the newest visits, the lists and today's totals are calculated (seconds)
fast_seconds = 10
slow_seconds = 60
today_seconds = 300
; at most this many calculations at the same time for the whole Matomo
max_concurrent = 2
; a city appears on the map from this many visits in the time window
city_min_visits = 3
; read from the reader database when Matomo has one configured (1) or always from the main database (0)
use_reader = 1
```

"Visitors active now" uses Matomo's own setting `[General] live_widget_visitor_count_last_minutes` (3 minutes by
default), the same as Matomo's real-time widget.

### Large instances

The live dashboard is built to keep the database load small at any size. It calculates one shared result per website
for all viewers, so ten or fifty people looking at the same website cost about as much as one. Lists read at most a
fixed number of rows per update; above that they are estimated from a sample, while key numbers stay exact.
Today's hits and conversions are counted only for the minutes since the last calculation and counted again from
midnight once an hour. Every database read has a time limit, and under load the dashboard calculates less often by
itself and shows how old its numbers are. In our load test with 3,000 new visits per minute on one website (about 4
million visits a day), the live dashboard used about 3 % of one CPU core of the database.

The defaults suit most instances. For very large ones these options help:

```ini
[FeinwerkLiveDashboard]
; most rows a list reads per time window before it samples (lower: less load, estimated lists from fewer visits)
visit_row_cap = 50000
action_row_cap = 100000
; recalculate the lists and today's totals less often (seconds)
slow_seconds = 60
today_seconds = 300
; time limit of one database read (seconds) and of all reads of one update (seconds)
statement_seconds = 3
budget_seconds = 8
; how many websites at most get the 10 second updates at the same time (others still show lists and today's totals)
max_active_keys = 20
```

If your Matomo has a reader database (`[database_reader]`), the live dashboard reads from it (`use_reader = 1`) and
keeps the load off the main database.

## Feedback and support

Under the live dashboard you can tell us with one click whether it shows your visitors correctly. Your vote goes from
your Matomo server to Feinwerk together with the plugin and Matomo version, your browser family and the interface
language, nothing else. To switch the question off:

```ini
[FeinwerkLiveDashboard]
feedback = 0
```

**Support** next to it opens the [Feinwerk contact form](https://feinwerk.io/en/contact?service=plugins).

## Installing the Feinwerk Connector

The free Feinwerk Connector installs, updates and licenses Feinwerk plugins inside Matomo. The easiest way to get it is
the Matomo Marketplace. While it is not active, super users also find a section "Feinwerk Plugins" in Matomo's
Administration with the entry "Install Feinwerk Connector" ("Activate Feinwerk Connector" when its files are already
there). One click installs it from the Matomo Marketplace and activates it; as with every plugin install, Matomo first
asks for your password. Where Matomo cannot install from its Marketplace (Marketplace, internet features or plugin
administration switched off), the page explains the manual way instead. In Matomo for WordPress the connector is
installed as a WordPress plugin through Matomo Analytics, Marketplace. The connector needs PHP 8.1. Nothing is
downloaded before you click. To hide the entry, add to `config/config.ini.php`:

```ini
[FeinwerkLiveDashboard]
connector_installer = 0
```

The same line in the section `[FeinwerkMarketplaceConnector]` hides it for all Feinwerk plugins.

## Tips from Feinwerk

The plugin shows short tips on other Feinwerk offers, for example Feinwerk Campaigns or Matomo hosting. Only users with
admin access to at least one website and super users see them, and only in Matomo's interface: never in exports, API
results, email reports or embedded widgets. You see at most one tip per place and page:

- one line under the live dashboard, after the feedback question,
- one small card in the Live Dashboard widgets on a Matomo dashboard (one card per dashboard),
- one banner at the top of the start page of Matomo's administration.

A tip for a Feinwerk plugin you already have installed is not shown. "×" hides a tip for 30 days in your browser. The
tips are part of the plugin: nothing is loaded from outside and nothing is sent. Links to feinwerk.io pages open in a
new tab and carry only the campaign parameters `mtm_campaign`, `mtm_source` and `mtm_content`. The tip for Feinwerk
Marketplace Connector takes a super user to the connector in Matomo's own Marketplace (same tab, no campaign
parameters) and is shown to super users only when this Matomo can install plugins from there; admins go to its page
on plugins.matomo.org. This tip is not shown inside Matomo for WordPress.

To switch the tips off for this plugin, add to `config/config.ini.php`:

```ini
[FeinwerkLiveDashboard]
promotions = 0
```

The same line in the section `[FeinwerkMarketplaceConnector]` switches them off for all Feinwerk plugins. Recent
versions of the Feinwerk Marketplace Connector (install it from Matomo's Marketplace) also have the setting "Show notes
on other Feinwerk offers".

## License

GPL v3 or later, see `LICENSE`.

## Author

Peter Boehlke, [Feinwerk.io](https://feinwerk.io) · support@feinwerk.io
