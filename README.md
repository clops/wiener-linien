Wiener Linien CSV Data Parser
=============================

CSV Data parser which creates a combined-data JSON File

### Preamble ###

The Wiener Linien (Vienna Public Transport Company) is providing real-time departure data via its API. However, making use of this data required the use of several data sources as the data is provided for different Platforms and not Stations (makes sense).

However, the end users are usually interested in Station data first, making a reverse lookup of the data necessary and rather uncomfortable for the developers working with the API.

The Wiener Linien provide a dump of its lookup tables in form of three separate CSV files. One for the stations, one for the transport lines and platforms table linking the first two. In short -- lots of overhead work to do for anyone wishing to play with the API.

### What does this tool do? ###

This script generates a combined JSON file from the remote Wiener Linien data which is easy to query and use inside your applicate. The script is content-change aware, i.e. it will regenerate the JSON file once the remove CSV data changes (and it happens every once in a while as stations get renamed or changed).

Here is a sample of the generated output data:

### How do I use it? ###

  * Upload the script to your server to a web-accessible directory
  * Chmod 777 the __cache__ folder
  * Call the script either from a web browser, it will generate the JSon file and send it to the client.

The script can be called from the console, following options are supported
  * __--force__ -- force re-creation of json file
  * __--verbose__ -- display log output
  
When calling the script from the console, the JSon output will not be sent to the client. Check the generated file under __cache/current.json__

### Any working examples? ###

Sure, try it out here -- http://code.clops.at/wiener-linien/

### Links & References ###

  * Script inspired by [Hactar's](https://github.com/hactar) OSX Tool for doing approximately the same task. One will need to track CSV changes @ Wiener Linien however and regenerate the file manualy. My service is automated. 