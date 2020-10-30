# ExportImportTree
## Description

Package that allows you to export bean data with all related records and save it as a JSON file. If you have previously exported JSON file you can also import the data into your instance.

Note:
> By default, the mechanism will export up to 3 levels of related records
> | 3  | 2  | 1  | 0 - Not exported  | -1  |
> |---|---|---|---|---|
> | Accounts  | Contacts  | Calls  | Notes  | Teams  |


## How to use it?

* Go to record you want to export
* In your Profile Menu you can see two new options:
    * Export
    * Import
* By clicking Export JSON file will start downloading (you will be asked to select a location on your disk)
* By clicking Import you are able to select previously downloaded JSON file and import it into your instance