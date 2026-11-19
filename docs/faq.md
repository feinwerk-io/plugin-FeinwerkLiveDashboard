__Is Live Dashboard free?__

Yes. Live Dashboard is free. Admins and super users see short tips on other Feinwerk offers in Matomo's interface; one line in Matomo's configuration file switches them off.

__Where do I find it?__

Under Visitors › Live Dashboard for every website you can view. For your dashboards, add the widgets of the category Live Dashboard.

__How current are the numbers?__

Visitors active now, the latest visits and the numbers of the last minutes update every 10 seconds, the lists every minute and today's totals every 5 minutes. When the database is busy and numbers get older, the dashboard says how old they are.

__Does Live Dashboard slow down my Matomo?__

Everyone who watches the same website shares one calculation, so ten or fifty viewers cost about as much as one. Every database read has a time limit, and on heavy traffic the lists use a sample. In our load test with 1,000 new visits per minute, 50 viewers needed less than 2 % of one database CPU core, and tracking was not slower.

__What does "estimated" next to a list mean?__

On very busy websites the lists of the longer time windows are calculated from a sample of the visits and scaled up. The key numbers above them stay exact.

__Who can see the live dashboard?__

Everybody with view access to a website. The latest visits follow Matomo's own settings: they are hidden when the visits log is switched off for the website or the whole Matomo, or when Matomo rounds report numbers for privacy.

__Why is a city missing on the map?__

The map shows a city only from 3 visits in the time window, so single visits cannot be traced. Regions and cities also need Matomo's geolocation with a location database.

__Which time zone does "today" use?__

The time zone of the website in Matomo: today starts at midnight there.

__Can I show it on a wall display?__

Yes. Full screen on the Live Dashboard page shows the board without Matomo's menus, with the name of the website at the top.

__Does it work on large Matomo instances?__

Yes. If your Matomo uses a reader database, Live Dashboard reads from it. For very large instances, the documentation lists the options that reduce the load further.

__Where do I find more Feinwerk plugins?__

Install Feinwerk Connector from the Matomo Marketplace. It lists all Plugins for Matomo by Feinwerk, free and paid, and installs and updates them with one click.
