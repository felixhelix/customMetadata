# customMetadata

OJS3 plugin for creating custom metadata fields to submission metadata. This is a fork from https://github.com/ajnyga/customMetadata.

NOTICE: this is just a concept with **a lot** of loose ends not a ready to use plugin. Requires inserting values directly into a database table to work.

To create custom metadata fields you have to add them to the table and provide translation values for label and description in the .loc file.
Currently text and textarea fields are supported.
Note that this plugin does only handle the input of the custom metadata in the backend. There is no code to display the values on i.e. the article details page or include them in export files.

Changes to original version:
- updated the code to work with OJS/OPS 3.3
- added a filter to allow for metadata only to apply to certain sections
- automatic creation of the required database table with schema.xml
- Use $customField->getType() to switch between templates input/textarea 
- Field labels and description only showing a translation string

As for the last one: This was due to the software interpreting label and description values in the metadata template as translation keys. The solution was to treat the field values as such and add the translations to the .loc file.

TODO: support multilingual input. Would require custom_metadata_settings table and some changes
TODO: UI in the backend
TODO: input validation
