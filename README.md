# Installation

* Extract the extension folder to extensions/LiquipediaMediaWikiMessages/
* create the database table from the sql file in the repository
* Add the following line to LocalSettings.php:

	wfLoadExtension( 'LiquipediaMediaWikiMessages' );

* Add the following to your composer.local.json:
```{
    "extra": {
        "merge-plugin": {
            "include": [
                "extensions/Pleesher/composer.json"
            ]
        }
    }
}```