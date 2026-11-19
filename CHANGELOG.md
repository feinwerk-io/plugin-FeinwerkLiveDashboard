# Changelog

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
