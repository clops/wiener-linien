Wiener Linien CSV Data Parser
=============================

Server-side CSV Data parser which creates an always current combined-data JSON File

### Preamble ###

The Wiener Linien (Vienna Public Transport Company) is providing real-time departure data via its API. However, making use of this data requires the use of several data sources as the data is provided for different Platforms and not Stations (makes sense).

However, the end users are usually interested in Station data first, making a reverse lookup of the data necessary and rather uncomfortable for the developers working with the API.

The Wiener Linien provide a dump of its lookup tables in form of three separate CSV files. One for the stations, one for the transport lines and one for the platforms table linking the first two. In short — lots of overhead work to do for anyone wishing to play with the API.

### What does this tool do? ###

This script generates a combined JSON file from the remote Wiener Linien data which is easy to query and use inside your application. The script is content-change aware, i.e. it will regenerate the JSON file once the remote CSV data changes (and it happens every once in a while as stations get renamed or changed).

Here is a sample of the generated output data:
```json
{
    "HALTESTELLEN_ID":"214460106",
    "TYP":"stop",
    "DIVA":"60200001",
    "NAME":"Absberggasse",
    "GEMEINDE":"Wien",
    "GEMEINDE_ID":"90000",
    "WGS84_LAT":"48.1737831466666",
	"WGS84_LON":"16.3897519115987",
	"STAND":"2013-10-04 11:33:28",
	"PLATFORMS":[
        {
            "LINIE":"6",
            "ECHTZEIT":"1",
            "VERKEHRSMITTEL":"ptTram",
            "RBL_NUMMER":"406",
            "BEREICH":"0",
            "RICHTUNG":"H",
            "REIHENFOLGE":"16",
            "STEIG":"6-H",
            "STEIG_WGS84_LAT":"48.1738228489303",
            "STEIG_WGS84_LON":"16.3894437290326"
        },
        {
            "LINIE":"6",
            "ECHTZEIT":"1",
            "VERKEHRSMITTEL":"ptTram",
            "RBL_NUMMER":"420",
            "BEREICH":"0",
            "RICHTUNG":"R",
            "REIHENFOLGE":"19",
            "STEIG":"6-R",
            "STEIG_WGS84_LAT":"48.1740700345048",
            "STEIG_WGS84_LON":"16.3898268991941"
        },
        {
            "LINIE":"N6",
            "ECHTZEIT":"1",
            "VERKEHRSMITTEL":"ptBusNight",
            "RBL_NUMMER":"406",
            "BEREICH":"0",
            "RICHTUNG":"H",
            "REIHENFOLGE":"13",
            "STEIG":"N6-H",
            "STEIG_WGS84_LAT":"48.1737785459418",
            "STEIG_WGS84_LON":"16.3893887476896"
        },
        {
        "LINIE":"N6",
        "ECHTZEIT":"1",
        "VERKEHRSMITTEL":"ptBusNight",
        "RBL_NUMMER":"420",
        "BEREICH":"0",
        "RICHTUNG":"R",
        "REIHENFOLGE":"6",
        "STEIG":"N6-R",
        "STEIG_WGS84_LAT":"48.1740358606221",
        "STEIG_WGS84_LON":"16.3896780693297"
        }
	],
	"LINES":["6","N6"]
}
```

### How do I use it? ###

  * Upload the script to your server to a web-accessible directory
  * Chmod 777 the `cache` folder
  * Call the script either from a web browser or a remote client, it will generate the JSon file and send it to the client.

The script can be called from the console, following options are supported
  * `--force` — force re-creation of json file
  * `--verbose` — display log output
  
When calling the script from the console, the JSon output will not be sent to the client. Check the generated file under `cache/current.json`

### Any working examples? ###

Sure, try it out here — http://code.clops.at/wiener-linien/ (approx 1.7 Mb Download)

### Links & References ###

Script inspired by [Hactar's](https://github.com/hactar) OSX Tool for doing approximately the same task. One will need to track CSV changes @ Wiener Linien however and regenerate the file manualy. My service is automated.