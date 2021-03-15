# Custom Field Types

See Craft's documentation for more information:
https://craftcms.com/docs/3.x/extend/field-types.html

Important functions implemented by the custom field type class:

```php
    /**
     * Normalizes the field’s value for use. The value returned by this method
     * should be compatible with the type defined in `this->valueType()`
     *
     * For example, if we want to support setting the field's value to an array,
     * like so: `element->mixedCategoriesField = [ 123, 5341 ];`, than this method
     * should translate this array into a CategoryQuery object.
     *
     */

    public function normalizeValue( $value )
    {
        if ($value instanceof CategoryQuery) {
            return $value;
        }

        // let's support an array of category ids
        // e.g. [ 123, 5341 ]
        else if (is_array($value))
        {
            // just in case this is an associative array:
            // e.g. [ 'first' => 123, 'last' => 5341 ]
            $categoryIds array_values($value);

            $categoryQuery = new CategoryQuery();
            $categoryQuery->id($categoryIds);

            return $categoryQuery;
        }

        // let's support a single id as a number, or wrapped in quotes
        // e.g. 123 OR '5341'
        else if (is_numeric($value))
        {
            $categoryId = (int)$value;

            $categoryQuery = new CategoryQuery();
            $categoryQuery->id($categoryId);

            return $categoryQuery;
        }

        // support an id expression such as 'not 123'
        else if (is_string($value) && strpos($value, 'not') === 0)
        {
            $categoryQuery = new CategoryQuery();
            $categoryQuery->id($value);

            return $categoryQuery;
        }

        else {
            throw new Exception('Given value is not supported');
        }
    }

    /**
     * Prepares the field’s value to be stored somewhere, like the content table.
     * This is also used when the field's owner element is transformed into an array
     *
     * It is safe for this method to return data types that are JSON-encodable
     * (i.e. arrays, integers, strings, booleans, etc).
     * Whatever this returns should be something normalizeValue() can handle.
     */

    public function serializeValue( $value )
    {
        if (is_object($value)) {
            return (array)$value;
        }

        return $value;
    }

    /**
     * Renders the field's html in the control panel
     * (e.g. on the owner element's edit screen)
     *
     * Note: If your field extends \craft\base\Fields, you will want to
     * override the `inputHtml()` method instead as this will make it
     * possible for other plugins to modify your field's input html
     */

    public function getInputHtml( $value, ElementInterface $element ): string
    {

    }

    /**
     *
     */

    protected function inputHtml( $value, ElementInterface $element ): string
    {
        if ($element instanceof Entry)
        {
            // return some html
        }

        // return a different html
    }

    /**
     * Renders the html for the field's settings in the control panel
     * (i.e. on craft's create or edit field screen)
     */

    public function getSettingsHtml( $value, $element ): string
    {

    }
```
