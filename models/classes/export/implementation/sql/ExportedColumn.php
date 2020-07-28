<?php


namespace oat\tao\model\export\implementation\sql;


class ExportedColumn
{
    const TYPE_BOOLEAN = 'BOOLEAN';
    const TYPE_INTEGER = 'INT';
    const TYPE_DECIMAL = 'DECIMAL';
    const TYPE_VARCHAR = 'VARCHAR(16000)';
    const TYPE_TIMESTAMP = 'TIMESTAMP';

    const POSTFIX = '_col';

    /**@var string $name */
    private $name;

    /**@var string $type */
    private $type;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name . self::POSTFIX;
    }

//    /**
//     * @param string $type
//     */
//    public function setType(string $type): void
//    {
//        $this->type = $type;
//    }

    public function getColumnCreatingString() {
        return $this->getName() . ' ' . $this->getType();
    }
}
