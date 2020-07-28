<?php


namespace oat\tao\model\export\implementation\sql;

/**
 * Class ExportedField
 * @package oat\tao\model\export\implementation\sql
 */
class ExportedField
{
    /**
     * @var ExportedColumn $column
     */
    private $column;

    /**
     * @var mixed $value
     */
    private $value;

    public function __construct(ExportedColumn $column, $value)
    {
        if (strpos($value, '|') !== false) {
            $column->setType(ExportedColumn::TYPE_VARCHAR);
        }
        $this->column = $column;
        $this->value = $value;
    }

    /**
     * @return ExportedColumn
     */
    public function getColumn(): ExportedColumn
    {
        return $this->column;
    }

    /**
     * @return string
     */
    public function getFormattedValue()
    {
        if (is_null($this->value) || ($this->value === '' && $this->getColumn()->getType() !== ExportedColumn::TYPE_VARCHAR)) {
            return 'null';
        }

        switch ($this->getColumn()->getType()) {
            case ExportedColumn::TYPE_BOOLEAN:
            case ExportedColumn::TYPE_INTEGER:
                return "$this->value";
            case ExportedColumn::TYPE_TIMESTAMP:
                $date = (\DateTime::createFromFormat('d/m/Y H:i:s', $this->value))->format('Y-m-d H:i:s');
                return "'$date'";
            case ExportedColumn::TYPE_VARCHAR:
            case ExportedColumn::TYPE_DECIMAL:
                return "'$this->value'";
        }
    }
}
