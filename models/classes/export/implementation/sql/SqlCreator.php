<?php

namespace oat\tao\model\export\implementation\sql;

class SqlCreator
{
    /**
     * @var ExportedTable
     */
    private $table;

    public function __construct(ExportedTable $table)
    {
        $this->table = $table;
    }

    private function getSqlCreateTable(): string
    {
        $columnsCreatingStringArray = [];

        foreach ($this->table->getColumns() as $column) {
            $columnsCreatingStringArray[] = $column->getColumnCreatingString();
        }

        return sprintf("CREATE TABLE tests_result (\n\t%s\n);", implode(",\n\t", $columnsCreatingStringArray));
    }

    private function getSqlInsert()
    {
        $columnNamesArray = [];
        foreach ($this->table->getColumns() as $column)
        {
            $columnNamesArray[] = $column->getName();
        }

        $fieldInsertArray = [];
        foreach ($this->table->getRows() as $row) {

            $rowValuesArray = [];

            /**@var $field ExportedField */
            foreach ($row as $key => $field) {
                $rowValuesArray[] = $field->getFormattedValue();
            }

            $rowValuesString = implode(",\n\t   ", $rowValuesArray);
            $fieldInsertArray[] = "(\n\t   $rowValuesString\n\t)";;
        }

        $columnNamesString = implode(",\n\t   ", $columnNamesArray);
        $fieldInsertString = implode(",\n\t", $fieldInsertArray);

        return "INSERT INTO tests_result (\n\t   $columnNamesString\n) VALUES $fieldInsertString;";
    }

    public function getExportSql()
    {
        $sqlInsert = $this->getSqlInsert();
        $sqlCreateTable = $this->getSqlCreateTable();

        return "$sqlCreateTable\n\n$sqlInsert";
    }

}



//                // if there are several values in the field, override the type to VARCHAR
//                if (strpos($value, '|') !== false) {
//                    $columnTypes[$fieldName] = 'VARCHAR(16000)';
//                }