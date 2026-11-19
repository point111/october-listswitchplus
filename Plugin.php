<?php namespace Konvertagency\ListSwitchPlus;

use Lang;
use Event;
use ApplicationException;
use Backend\Classes\Controller;
use Backend\Behaviors\RelationController;
use System\Classes\PluginBase;

/**
 * Plugin adds the inetis-list-switch list column type: a Bootstrap switch that toggles
 * a boolean model field via an HMAC-signed AJAX request.
 *
 * Standalone replacement for Inetis.ListSwitch (MIT, inetis.ch) — keeps its column type
 * and handler names so existing columns.yaml work unchanged. Do not run both plugins:
 * the column type registered by the first plugin in load order wins.
 */
class Plugin extends PluginBase
{
    /**
     * @return array<string, string>
     */
    public function pluginDetails(): array
    {
        return [
            'name' => 'konvertagency.listswitchplus::lang.plugin.name',
            'description' => 'konvertagency.listswitchplus::lang.plugin.description',
            'author' => 'Konvertagency',
            'icon' => 'icon-toggle-on',
        ];
    }

    /**
     * @return array<string, array{0: class-string, 1: string}>
     */
    public function registerListColumnTypes(): array
    {
        return [
            ListSwitchField::COLUMN_TYPE => [ListSwitchField::class, 'render'],
        ];
    }

    public function boot(): void
    {
        // The switch handles the click itself — the row must not open the record.
        Event::listen('backend.list.extendColumns', function ($widget) {
            foreach ($widget->getColumns() as $name => $listColumn) {
                if (data_get($listColumn, 'config.type') !== ListSwitchField::COLUMN_TYPE) {
                    continue;
                }

                $widget->addColumns([
                    $name => array_merge($listColumn->config, ['clickable' => false]),
                ]);
            }
        });

        Controller::extend(function ($controller) {
            $controller->addDynamicMethod('index_onSwitchInetisListField', function () use ($controller) {
                $this->processInvertField($controller);

                return $controller->listRefresh($controller->primaryDefinition);
            });

            $controller->addDynamicMethod('onSwitchInetisListField', function ($recordId = null) use ($controller) {
                $this->processInvertField($controller);

                if ($controller->isClassExtendedWith(RelationController::class)) {
                    $controller->initForm($controller->formFindModelObject($recordId));

                    return $controller->relationRefresh(post('_relation_field'));
                }
            });
        });
    }

    /**
     * processInvertField checks the request signature and inverts the model field.
     * The record is looked up via the controller's listSwitchFindRecord($modelClass, $id)
     * when it exists (it throws itself if access is denied), otherwise findOrFail().
     */
    protected function processInvertField(Controller $controller): void
    {
        $modelClass = (string) post('model');
        $id = (string) post('id');
        $fieldName = (string) post('field');
        $signature = (string) post('sig');

        if ($modelClass === '' || $id === '' || $fieldName === '') {
            throw new ApplicationException(Lang::get('konvertagency.listswitchplus::lang.error.params_required'));
        }

        if ($signature === '' || !hash_equals(ListSwitchField::sign($modelClass, $id, $fieldName), $signature)) {
            throw new ApplicationException(Lang::get('konvertagency.listswitchplus::lang.error.invalid_signature'));
        }

        $item = $controller->methodExists('listSwitchFindRecord')
            ? $controller->listSwitchFindRecord($modelClass, $id)
            : $modelClass::findOrFail($id);

        $item->{$fieldName} = !$item->{$fieldName};
        $item->save();
    }
}
