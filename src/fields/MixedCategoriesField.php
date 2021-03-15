<?php

/**
 * Mixed Categories plugin for Craft CMS 3.x
 *
 * Craft CMS field type allowing editors to select categories from multiple groups
 *
 * @author Yoannis Jamar
 * @copyright Copyright (c) 2021 Yoannis Jamar
 * @link https://github.com/yoannisj
 * @package yoannisj/craft-mixed-categories
 */

namespace yoannisj\mixedcategories\fields;

use yii\base\Exception;

use Craft;
use craft\base\ElementInterface;
use craft\fields\Categories as CategoriesField;
use craft\elements\Category;
use craft\elements\db\CategoryQuery;

/**
 * Custom field type allowing editors to select categories from multiple category groups
 *
 * @todo: Verify that parent Categories Field's GraphQL functionality was not broken.
 */

class MixedCategoriesField extends CategoriesField
{
    // =Static
    // ========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('mixed-categories', 'Categories (Mixed)');
    }

    // =Properties
    // ========================================================================

    /**
     * @inheritdoc
     */

    public $allowMultipleSources = true;

    /**
     * @var string Template to use for settings rendering
     */

    protected $settingsTemplate = 'mixed-categories/fieldtype/settings';

    /**
     * @inheritdoc
     */

    protected $inputTemplate = 'mixed-categories/fieldtype/input';

    // /**
    //  * @inheritdoc
    //  */

    // protected $inputJsClass = 'Craft.CategorySelectInput';

    /**
     * @inheritdoc
     */

    // protected $sortable = false;

    /**
     * Whether this field should allow selecting categories from different
     * sources (i.e. category groups).
     */

    public $allowMixedSources = true;

    // =Public Methods
    // ========================================================================


    // =Protected Methods
    // ========================================================================

    /**
     * @inheritdoc
     */

    protected function inputHtml($value, ElementInterface $element = null): string
    {
        // make sure the field sources are valid category groups
        if (is_array($this->sources))
        {
            $sources = [];

            foreach ($this->sources as $source)
            {
                $foundSource = ElementHelper::findSource(
                    static::elementType(), $this->source, 'field');

                if ($foundSource) $sources[] = $foundSource;
            }
        }

        else {
            $sources = $this->sources;
        }

        if ($sources != '*' && empty($sources)) {
            return '<p class="error">' . Craft::t('mixed-categories', "None of this field's category groups is valid.") . '</p>';
        }

        // avoid Category::inputHtml() to render empty source message
        $this->source = 1;

        return parent::inputHtml($value, $element);
    }
}
