# Changelog

## 5.1.1

- Full screen is now a small icon in the top right corner instead of a button with text.
- The caption of the bar chart stands centred under the bars.
- Narrow dashboard columns: the lists, the map and the key numbers stay inside the widget, and in Live: Overview the
  bar chart moves under the number of visitors active now.
- The note with the calculation time stands in a very small line of its own right under the status.
- The time window sits on the right: on the live dashboard page next to the full screen icon, and in the dashboard
  widgets too.
- Installing the Feinwerk Connector: where its files are already there but plugin administration is switched off, the
  page names the command that activates it. The tip about the connector appears only where Matomo can install it with
  one click.

## 5.1.0

- In French, Spanish and Italian too, besides English and German.
- The key numbers stand in cards, like those of Matomo's Visits Overview; "Visitors active now" is centred next to the
  bar chart.
- A small note after the status shows how long the last calculation took and whether the lists use a sample; it can be
  switched off in Matomo's General settings, section Feinwerk Live Dashboard.
- For super users while the Feinwerk Connector is not active: Administration, Feinwerk Plugins, "Install Feinwerk
  Connector" installs it from the Matomo Marketplace and activates it with one click (after Matomo's password check);
  where Matomo cannot install by itself, the page explains the manual way. `connector_installer = 0` in
  `[FeinwerkLiveDashboard]` hides the entry.

## 5.0.1

- New screenshots for the Matomo Marketplace: the whole live dashboard on one page and every widget in its full width.

## 5.0.0

First stable version, for Matomo 5 and Matomo 6.

- Live dashboard under Visitors: visitors active now, new visits per minute, key numbers and today's totals.
- Time window of 3, 10, 30 or 60 minutes for every list and key number.
- Latest visits with country, source, duration and actions, and fixed icons for goals, orders, abandoned carts and
  returning visitors (when the visits log is on).
- Live map: the country with the most visits with its regions and cities, and the world map to open any other
  country; city names never cover each other.
- Top lists for pages, channels, campaigns, campaign sources, countries, devices, goals and orders, events, downloads,
  outlinks and site search, with rank changes.
- Five dashboard widgets: Overview, Pages, Sources and goals, Latest visits and Map.
- Works on phones, in narrow dashboard columns, in full screen and in dark mode.
- Fast on busy websites: one shared calculation for all viewers, lists from a sample on heavy traffic while the key
  numbers stay exact, today's totals counted only for the minutes since the last update, and less detail by itself
  when the database is busy. The README section "Large instances" lists the options for very large Matomo instances.
- Under the live dashboard, one click tells us whether it shows your visitors correctly; `feedback = 0` in
  `[FeinwerkLiveDashboard]` switches the question off.
- Short tips on other Feinwerk offers for admins and super users: one line under the live dashboard, one small card
  in the Live Dashboard widgets on a dashboard and one banner on the start page of the administration. "×" hides a tip
  for 30 days; `promotions = 0` in `[FeinwerkLiveDashboard]` switches them off.
- The README describes the plugin for the Matomo Marketplace, with a FAQ and screenshots.
