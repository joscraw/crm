When you want to add a transformer on a field that is dynamically added
on a form event you need to create your own Field Type that sets the parent
equal to the form type you want to inherit from. Then you can add 
data transformers. 