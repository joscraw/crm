## TIPS WHEN VERSIONING THE API

1. Create a nested folder for each Version. Example: V2/V2.1
2. You can add new Controllers that previous versions of the API didn't have
3. You can also overwrite functionality from controllers while still inheriting 
most of the basa functionality. Example: CustomObjectController extends CustomObjectController_V1
4. Last but not least, update your nelmio api docs to reflect the new api versioning have added.
For a full list of annotation options: https://github.com/zircote/swagger-php/tree/master/Examples