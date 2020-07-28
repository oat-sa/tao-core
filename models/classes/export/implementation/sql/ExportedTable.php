<?php

namespace oat\tao\model\export\implementation\sql;

class ExportedTable
{
    /**@var ExportedColumn[] $columns */
    private $columns = [];

    private $rows = [];

    /**
     * @return ExportedColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return []
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    public function getColumn($name)
    {
        foreach ($this->columns as $column) {
            if ($column->getName() === $name) {
                return $column;
            }
        }

        return null;
    }

    public function addColumn(ExportedColumn $column)
    {
        $this->columns[] = $column;
    }

    /**
     * @param ExportedField[] $row
     */
    public function addRow(array $row)
    {
        $this->rows[] = $row;
    }

}
