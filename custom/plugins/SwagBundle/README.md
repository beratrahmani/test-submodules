# SwagBundle

## Run tests

Run `./psh.phar`:

```
- cleanup: Removes all files which was created for the tests
- init: Initilizaes the test environment, i.e. creating database, moving config file
- reinstall: Reinstalls the plugin in the test environment
```

Example execution: `$ ./psh.phar -init`
Further all tests which are executed with the `phpunit` cli command will be executed in the environment you configured.

## sw-zip-blacklist
Exclude files and/or directories in `.sw-zip-blacklist`, which should not be in the release package of the plugin. List them separated by a new line