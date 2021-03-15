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

namespace yoannisj\mixedcategories;

use yii\base\Event;

use Craft;
use craft\base\Plugin;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;

use yoannisj\mixedcategories\fields\MixedCategoriesField;

/**
 * Entry point class for the Craft CMS plugin (initiliazed on each incoming request)
 */

class MixedCategories extends Plugin
{
    // =Static
    // ========================================================================

    // =Properties
    // ========================================================================

    // =Public Methods
    // ========================================================================

    /**
     * @inheritdoc
     */

    public function init()
    {
        parent::init();

        // Register plugin's custom field(s)
        Event::on(
            Fields::class, // The class that triggers the event
            Fields::EVENT_REGISTER_FIELD_TYPES, // the name of the event
            function(RegisterComponentTypesEvent $event) // the callback function
            {
                $event->types[] = MixedCategoriesField::class;
            }
        );
    }

    // =Protected Methods
    // ========================================================================

    // =Private Methods
    // ========================================================================
}
