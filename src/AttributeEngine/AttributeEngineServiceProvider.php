<?php


namespace OpenDialogAi\AttributeEngine;

use Carbon\Laravel\ServiceProvider;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\AttributeEngine\AttributeTypeService\AttributeTypeService;
use OpenDialogAi\AttributeEngine\AttributeTypeService\AttributeTypeServiceInterface;
use OpenDialogAi\Core\Components\ODComponentTypes;

class AttributeEngineServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/opendialog-attributeengine-custom.php' => config_path('opendialog/attribute_engine.php'),
        ], 'opendialog-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/opendialog-attributeengine.php', 'opendialog.attribute_engine');

        $this->app->singleton(AttributeResolver::class, function () {
            $attributeResolver = new AttributeResolver();

            $attributeResolver->registerAttributes(
                config('opendialog.attribute_engine.supported_attributes'),
                ODComponentTypes::CORE_COMPONENT_SOURCE
            );

            // Gets custom attributes if they have been set
            if (is_array(config('opendialog.attribute_engine.custom_attributes'))) {
                $attributeResolver->registerAttributes(
                    config('opendialog.attribute_engine.custom_attributes'),
                    ODComponentTypes::APP_COMPONENT_SOURCE
                );
            }

            return $attributeResolver;
        });

        $this->app->singleton(AttributeTypeServiceInterface::class, function () {
            $service = new AttributeTypeService();

            $service->registerAttributeTypes(config('opendialog.attribute_engine.supported_attribute_types'));

            // Gets custom attribute types if they have been set
            if (is_array(config('opendialog.attribute_engine.custom_attribute_types'))) {
                $service->registerAttributeTypes(config('opendialog.attribute_engine.custom_attribute_types'));
            }

            return $service;
        });
    }
}
