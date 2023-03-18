<?php

class Db {
    private mysqli $conn;

    function __construct($host, $user, $pass, $dbname) {
        $this->conn = new mysqli($host, $user, $pass, $dbname);
    }

    function insert($table, $values) {
        // handle subtables specially
        $escValues = [];
        $prequery = '';
        $i = 0;
        foreach ($values as $value) {
            if ($value instanceof SubtableExpression) {
                if ($value->value === '' || $value->value === null) {
                    $escValues[] = '0';
                } else {
                    $prequery .= 'INSERT IGNORE INTO ' . $value->table . ' (value) VALUES (' . $this->escape($value->value) . ");\n";
                    $escValues[] = '(SELECT id FROM ' . $value->table . ' WHERE value = ' . $this->escape($value->value) . ')';
                }
            } else {
                $escValues[] = $this->escape($value);
            }
            $i++;
        }

        $escTable = $this->escapeIdentifier($table);
        $escColumns = array_map([$this, 'escapeIdentifier'], array_keys($values));
        $escColumns = implode(', ', $escColumns);
        // $escValues = array_map([$this, 'escape'], $values);
        $escValues = implode(', ', $escValues);

        $q = "INSERT INTO $escTable ($escColumns) VALUES ($escValues)";
        if ($prequery) {
            $this->conn->multi_query($prequery . $q);
        } else {
            $this->queryUnsafe($q);
        }
    }

    function ip($ip) {
        return new Ip($ip);
    }

    function subtable($table, $value) {
        return new SubtableExpression($table, $value);
    }

    function query($q, ...$args) {
        // replace ? by escaped args
        foreach ($args as $arg) {
            $pos = strpos($q, '?');
            if ($pos === false) {
                throw new Exception('more parameters than ? symbols in query pattern');
            }
            $newQuery = substr($q, 0, $pos);
            $newQuery .= $this->escape($arg);
            $newQuery .= substr($q, $pos + 1);
            $q = $newQuery;
        }

        return $this->queryUnsafe($q);
    }

    /////////////
    // private //
    /////////////

    private function escape($arg) {
        if (is_int($arg)) {
            return $arg;
        } elseif ($arg instanceof Ip) {
            return $this->escape($arg->binary());
        } else {
            return '\'' . $this->conn->escape_string($arg) . '\'';
        }
    }

    private function escapeIdentifier($ident) {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $ident)) {
            throw new Exception('Ilegal identifier.');
        }
        return $ident;
    }

    private function queryUnsafe($q) {
        $result = $this->conn->query($q);
        if ($result === false) {
            throw new Exception($this->conn->error);
        }
        return $result;
    }
}

class Ip {
    function __construct(
        private readonly string $ip,
    ) {
    }

    function binary() {
        return inet_pton($this->ip);
    }
}

class SubtableExpression {
    function __construct(
        public readonly string $table,
        public readonly mixed $value,
    ) {
    }
}
