QUL People Counter
==================
Used by the people counter on the Queen's University Library homepage, this app queries the API from the vendor (Flownomics), calculates the current capacity of the building, and writes that value to a JSON file.

This query is run via cron on the server every 15 minutes.

The front end (in our case Drupal 7) then uses the JSON file to update the widget on the library homepage. It uses a JavaScript function that regularly checks the capacity file and updates the UI in real time. 
