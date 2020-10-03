# rijkswaterstaatstrooide
Scrapes salt spreading data from rijkswaterstaatstrooit.nl to create historical data

It queries https://rijkswaterstaatstrooit.nl/api/statistics and stores "dailySaltUsed" in MySQL.

More data is available on for example, https://rijkswaterstaatstrooit.nl/geoserver/strooi/ows?service=WFS&request=GetCapabilities (see https://pdok-ngr.readthedocs.io/ for more information about this specific service).
