<?php namespace Konvertagency\ListSwitchPlus;

use Backend\Classes\ListColumn;
use Lang;

/**
 * ListSwitchField renders the inetis-list-switch column as a Bootstrap switch
 * and signs the toggle request (model|id|field) with an HMAC.
 */
class ListSwitchField
{
    /**
     * @var string COLUMN_TYPE is kept from Inetis.ListSwitch so columns.yaml stay unchanged.
     */
    public const COLUMN_TYPE = 'inetis-list-switch';

    /**
     * @var array<string, mixed> defaultFieldConfig, every option can be overridden by the column config.
     */
    private static array $defaultFieldConfig = [
        'inverse' => false,
        'icon' => true,
        'titleTrue' => 'konvertagency.listswitchplus::lang.listswitch.title_true',
        'titleFalse' => 'konvertagency.listswitchplus::lang.listswitch.title_false',
        'textTrue' => 'konvertagency.listswitchplus::lang.listswitch.text_true',
        'textFalse' => 'konvertagency.listswitchplus::lang.listswitch.text_false',
        'request' => 'onSwitchInetisListField',
    ];

    private string $name;

    private mixed $value;

    private mixed $record;

    /**
     * @var array<string, mixed>
     */
    private array $config;

    /**
     * render is the list column type callback.
     */
    public static function render(mixed $value, ListColumn $column, mixed $record): string
    {
        $field = new self($value, $column, $record);

        return '
<a href="javascript:;"
    data-request="' . e($field->getConfig('request')) . '"
    data-request-data="' . e($field->getRequestData()) . '"
    data-stripe-load-indicator
    title="' . e($field->getButtonTitle()) . '">
    ' . $field->getButtonValue() . '
</a>
';
    }

    /**
     * sign returns the HMAC signature for a model field toggle.
     */
    public static function sign(string $modelClass, string $id, string $fieldName): string
    {
        return hash_hmac('sha256', $modelClass . '|' . $id . '|' . $fieldName, (string) config('app.key'));
    }

    public function __construct(mixed $value, ListColumn $column, mixed $record)
    {
        $this->name = (string) $column->columnName;
        $this->value = $value;
        $this->record = $record;
        $this->config = array_merge(self::$defaultFieldConfig, (array) $column->config);
    }

    private function getConfig(?string $key = null): mixed
    {
        return data_get($this->config, $key);
    }

    /**
     * getRequestData returns data-request-data params for the switch button (unescaped).
     */
    public function getRequestData(): string
    {
        $modelClass = get_class($this->record);
        $id = (string) $this->record->getKey();

        $data = [
            'id: ' . $this->jsString($id),
            'field: ' . $this->jsString($this->name),
            'model: ' . $this->jsString($modelClass),
            'sig: ' . $this->jsString(self::sign($modelClass, $id, $this->name)),
        ];

        if (post('page')) {
            $data[] = 'page: ' . (int) post('page');
        }

        return implode(', ', $data);
    }

    /**
     * getButtonValue returns button text or the Bootstrap switch.
     * With inverse the switch position is opposite to the field value.
     */
    public function getButtonValue(): string
    {
        if (!$this->getConfig('icon')) {
            return e(Lang::get($this->getConfig($this->value ? 'textTrue' : 'textFalse')));
        }

        $isChecked = $this->getConfig('inverse') ? !$this->value : (bool) $this->value;

        return '<div class="form-check form-switch"><input class="form-check-input" type="checkbox"'
            . ($isChecked ? ' checked="checked"' : '') . '></div>';
    }

    /**
     * getButtonTitle returns button hover title.
     */
    public function getButtonTitle(): string
    {
        return Lang::get($this->getConfig($this->value ? 'titleTrue' : 'titleFalse'));
    }

    private function jsString(string $value): string
    {
        return "'" . addcslashes($value, "\\'") . "'";
    }
}
