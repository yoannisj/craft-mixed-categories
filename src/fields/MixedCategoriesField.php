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
use craft\helpers\ArrayHelper;
use craft\helpers\ElementHelper;
use craft\helpers\Json as JsonHelper;

/**
 * Custom field type allowing editors to select categories from multiple category groups
 *
 * @todo: Verify that parent Categories Field's GraphQL functionality was not broken.
 * @todo: Implement option to allow only categories in a selected group
 * @todo: Make selected categories sortable on a per-field basis
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
        return Craft::t('mixed-categories-field', 'Categories (Mixed)');
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

    protected $settingsTemplate = 'mixed-categories-field/fieldtype/settings';

    /**
     * @inheritdoc
     */

    protected $inputTemplate = 'mixed-categories-field/fieldtype/input';

    // /**
    //  * @inheritdoc
    //  */

    protected $inputJsClass = 'Craft.CategorySelectInput';

    /**
     * @inheritdoc
     */

    // protected $sortable = false;

    /**
     * Whether this field allows selecting categories from different sources
     * (i.e. category groups).
     */

    public $allowMixedSources = true;

    // =Public Methods
    // ========================================================================

    /**
     *
     */

    // public function normalizeValue( $value, ElementInterface $element = null )
    // {
    //     if (is_string($value)) {
    //         $value = JsonHelper::decode($value);
    //     }

    //     $selectedSource = $value['selectedSource'];
    //     $ids = $value['selectedIds'][$selectedSource];

    //     $query = new CategoryQuery();
    //     $query->id($ids);

    //     return $query;
    // }

    // =Protected Methods
    // ========================================================================

    /**
     * @inheritdoc
     */

    protected function inputHtml( $value, ElementInterface $element = null): string
    {
        $sources = $this->getSources();

        if (empty($sources)) {
            return '<p class="error">' . Craft::t('mixed-categories-field', "None of this field's category groups is valid.") . '</p>';
        }

        // if ($this->allowMixedSources == false) {
        //     return $this->selectSourceInputHtml($sources, $value, $element);
        // }

        return $this->mixedSourcesInputHtml($sources, $value, $element);
    }

    /**
     * Render input html for field that allows categories from different sources
     *
     * @param array[] $sources
     * @param CategoryQuery $value
     * @return string
     */

    protected function mixedSourcesInputHtml( array $sources, CategoryQuery $value, ElementInterface $element = null ): string
    {
        // Avoid parent::inputHtml() from rendering an "invalid source" error message,
        // by giving it the first of this field's sources
        $firstSource = ArrayHelper::firstValue($sources);
        $this->source = $firstSource['key'];

        $html = parent::inputHtml($value, $element);

        // set back to null in case another field method relies on this value
        $this->source = null;

        return $html;
    }

    /**
     * Renders input html for field that does not allow categories from different sources
     *
     * @param array[] $sources
     * @param CategoryQuery $value
     * @return string
     */

    protected function selectSourceInputHtml( array $sources, CategoryQuery $value, ElementInterface $element = null ): string
    {
        $sources = $this->getSources();

        // act as if we were rendering the input for one source only
        $this->allowMultipleSources = false;

        $sourceOptions = $this->getSourceOptions();
        $selectOptions = [];

        foreach ($sourceOptions as $index => $option)
        {
            $selectOptions[] = [
                'value' => str_replace('group:', '', $option['value']),
                'label' => $option['label'],
            ];
        }

        $html = Craft::$app->getView()->renderTemplate('_includes/forms/select', [
            'id' => 'selectedSource',
            'name' => 'selectedSource',
            'options' => $selectOptions,
            'toggle' => true,
            'targetPrefix' => 'source-input-',
        ]);

        // render a new input for each source
        foreach ($sources as $index => $source)
        {
            $this->source = $source['key'];
            $inputHtml = parent::inputHtml($value, $element);

            // wrap input in a toggable div
            $selectValue = str_replace('group:', '', $source['key']);
            $wrapperClass = $index == 1 ? 'hidden' : '';
            $html .= '<div id="source-input-'.$selectValue.'" class="'.$wrapperClass.'">'.$inputHtml.'</div>';
        }

        // stop lying ;)
        $this->allowMultipleSources = true;

        return $html;
    }

    /**
     * Returns list of sources based on sources available and field's `sources` setting
     *
     * @return array
     */

    protected function getSources(): array
    {
        $sources = [];

        if ($this->sources == '*') {
            $sources = $this->availableSources();
        }

        // make sure the field sources are valid category groups
        else if (is_array($this->sources))
        {
            $sources = [];
            $elementType = static::elementType();

            foreach ($this->sources as $source)
            {
                $foundSource = ElementHelper::findSource(
                    $elementType, $source, 'field');

                if ($foundSource) $sources[] = $foundSource;
            }
        }

        return $sources;
    }

}
