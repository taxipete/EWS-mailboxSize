Folder Sizes using - PhpEws - PHP Exchange Web Services
===================================

__This is a forked version__ of the original GitHub project at [jamesiarmes/php-ews](/jamesiarmes/php-ews) to bring Composer installation,
namespaces and PSR-0 autoloading of classes.

This is not intended to replace the original work but is an example of how to use EWS to retrieve mailbox sizes for users.

The critical part is an extended property query

```php
$status = new EWSType_PathToExtendedFieldType();
$status->PropertyTag = "0x0E08";
$status->PropertyType = EWSType_MapiPropertyTypeType::LONG;
$request->FolderShape->AdditionalProperties->ExtendedFieldURI = array($status);
```
Full details on the extend property here:https://msdn.microsoft.com/en-us/library/office/cc842471.aspx

### Usage

__mailboxClass__ holds the class for getting the mailbox sizes and a couple of extra functions for processing the returned data.
__mailbox.php__ is a simple example of using the class.  
