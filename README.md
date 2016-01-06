# cal importer for cz_simple_cal 

This is a TYPO3 Extension for importing events from the cal extension to cz_simple_cal.

It is a very early draft and can only convert events and their file relations.

Recurrance is also not yet supported.

To use it, install the extension and run the importer script:

```bash
cd /my/typo3/dir
./typo3/cli_dispatch.phpsh extbase czcalimport:importfromcal
```
