# customMetadata

OJS3 plugin for creating custom metadata fields to submission metadata. This is a fork from https://github.com/ajnyga/customMetadata.

NOTICE: this is just a concept with **a lot** of loose ends not a ready to use plugin. Requires inserting values directly into a database table to work.

To create custom metadata fields you have to add them to the table and provide translation values for label and description in the .loc file.
Currently text, textarea and richtextarea fields are supported.
Note that this plugin does only handle the input of the custom metadata in the backend. There is no code included to display the values on i.e. the article details page or add them to export files.

Changes to original version:
- updated the code to work with OJS/OPS 3.3
- added a filter to allow for metadata only to apply to certain sections (incl. a corresponding database field)
- automatic creation of the required database table
- Use $customField->getType() to switch between templates input/textarea/richtextarea
- added a required option (incl. a corresponding database field)
- added a tab under "website" which shows the custom metadata settings
- added logging of custom metadata changes after submission
- removed fields from the database table used for localization, label and description

As for the localization: OJS/OPS 3.3 interprets label and description values in the metadata template as translation keys. So field labels and descriptions have to go in the .loc file. Examples are included.

TODO: support multilingual input
TODO: backend UI to setup fields
