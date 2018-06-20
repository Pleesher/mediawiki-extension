# Installation

* Extract the extension folder to extensions/Pleesher/
* Add the following line to LocalSettings.php:

	wfLoadExtension( 'Pleesher' );

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

* run the MediaWiki update script in `maintenance/update.php`