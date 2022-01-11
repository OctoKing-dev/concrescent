<?php

namespace CM3_Lib\database;

class ColumnIndex
{
    //string => bool array, true means add DESC
    public function __construct(public array $Columns, public string $IndexType = '')
    {
    }
    public function GetCreateString($indexName): string
    {
        //Preamble
        switch (strtolower($ix->IndexType)) {
            case 'primary key':
                $sqlText = 'CONSTRAINT PRIMARY KEY ';
                break;
            case 'unique key':
                $sqlText = 'CONSTRAINT `' . $indexName . '` UNIQUE KEY ';
                break;
            case 'unique':
                $sqlText = 'CONSTRAINT `' . $indexName . '` UNIQUE ';
                break;
            default:
                $sqlText = 'INDEX `' . $indexName . '` ';
                break;
        }
        //Column definitions
        $sqlText .= '(';
        foreach ($ix->$Columns as $columnName => $isDesc) {
            $sqlText .= '`' . $columnName .'` ' .
            ($isDesc ? 'DESC ' : '') . ', ';
        }
        //Snip the trailing comma and add in a closing parenthesis...
        $sqlText = substr($sqlText, 0, -2) . ') ';
    }
}
