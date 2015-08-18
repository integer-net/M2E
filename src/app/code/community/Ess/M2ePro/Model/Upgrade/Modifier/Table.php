<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Modifier_Table extends Ess_M2ePro_Model_Upgrade_Modifier_Abstract
{
    const COMMIT_KEY_ADD_COLUMN    = 'add_column';
    const COMMIT_KEY_DROP_COLUMN   = 'drop_column';
    const COMMIT_KEY_CHANGE_COLUMN = 'change_column';
    const COMMIT_KEY_ADD_INDEX     = 'add_index';
    const COMMIT_KEY_DROP_INDEX    = 'drop_index';

    protected $sqlForCommit = array();

    //####################################

    public function addColumn($nick, $type, $default = NULL, $after = NULL, $addIndex = false, $autoCommit = true)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTableName();
        $definition = $this->getDefinition($type, $default, $after);

        if ($this->isColumnExists($nick) === false && !empty($definition)) {
            if (!$autoCommit) {
                $this->addQueryToCommit(self::COMMIT_KEY_ADD_COLUMN, sprintf('ADD COLUMN %s %s',
                    $connection->quoteIdentifier($nick),
                    $definition
                ));
            } else {
                $connection->addColumn($tableName, $nick, $definition);
            }

            $addIndex && $this->addIndex($nick, $autoCommit);
        }

        return $this;
    }

    //####################################

    public function dropColumn($nick, $dropIndex = true, $autoCommit = true)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTableName();

        if ($this->isColumnExists($nick) !== false) {
            if (!$autoCommit) {
                $this->addQueryToCommit(self::COMMIT_KEY_DROP_COLUMN, sprintf('DROP COLUMN %s',
                    $connection->quoteIdentifier($nick)
                ));
            } else {
                $connection->dropColumn($tableName, $nick);
            }

            $dropIndex && $this->dropIndex($nick, $autoCommit);
        }

        return $this;
    }

    //####################################

    public function changeColumn($nick, $type, $default = NULL, $after = NULL, $autoCommit = true)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTableName();
        $definition = $this->getDefinition($type, $default, $after);

        if ($this->isColumnExists($nick) !== false && !empty($definition)) {
            if (!$autoCommit) {
                $this->addQueryToCommit(self::COMMIT_KEY_CHANGE_COLUMN, sprintf('MODIFY COLUMN %s %s',
                    $connection->quoteIdentifier($nick),
                    $definition)
                );
            } else {
                $connection->modifyColumn($tableName, $nick ,$definition);
            }
        }

        return $this;
    }

    //####################################

    public function renameColumn($from, $to, $renameIndex = true, $autoCommit = true)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTableName();
        $definition = $this->getColumnDefinition($from);

        if ($this->isColumnExists($from) !== false && $this->isColumnExists($to) === false && !empty($definition)) {
            if (!$autoCommit) {
                $this->addQueryToCommit(self::COMMIT_KEY_CHANGE_COLUMN, sprintf('CHANGE COLUMN %s %s %s',
                    $connection->quoteIdentifier($from),
                    $connection->quoteIdentifier($to),
                    $definition
                ));
            } else {
                $connection->changeColumn($tableName, $from, $to, $definition);
            }

            if ($renameIndex) {
                $this->dropIndex($from, $autoCommit)
                     ->addIndex($to, $autoCommit);
            }
        }

        return $this;
    }

    //####################################

    public function addIndex($nick, $autoCommit = true)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTableName();

        if (!$this->isIndexExists($nick)) {
            if (!$autoCommit) {
                $this->addQueryToCommit(self::COMMIT_KEY_ADD_INDEX, sprintf('ADD INDEX %s (%s)',
                    $connection->quoteIdentifier($nick),
                    $connection->quoteIdentifier($nick)
                ));
            } else {
                $connection->addKey($tableName, $nick, $nick);
            }
        }

        return $this;
    }

    //####################################

    public function dropIndex($nick, $autoCommit = true)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTableName();

        if ($this->isIndexExists($nick)) {
            if (!$autoCommit) {
                $this->addQueryToCommit(self::COMMIT_KEY_DROP_INDEX, sprintf('DROP KEY %s',
                    $connection->quoteIdentifier($nick)
                ));
            } else {
                $connection->dropKey($tableName, $nick);
            }
        }

        return $this;
    }

    //####################################

    public function truncate()
    {
        $connection = $this->getConnection();
        $tableName = $this->getTableName();

        if ($connection->isTableExists($tableName)) {
            $connection->truncateTable($tableName);
        }

        return $this;
    }

    //####################################

    public function commit()
    {
        if (empty($this->sqlForCommit)) {
            return $this;
        }

        $connection = $this->getConnection();
        $tableName = $this->getTableName();

        $order = array(
            self::COMMIT_KEY_ADD_COLUMN,
            self::COMMIT_KEY_CHANGE_COLUMN,
            self::COMMIT_KEY_DROP_COLUMN,
            self::COMMIT_KEY_ADD_INDEX,
            self::COMMIT_KEY_DROP_INDEX
        );

        $tempSql = '';
        $sep = '';
        foreach ($order as $orderKey) {
            foreach ($this->sqlForCommit as $key => $sqlData) {
                if ($orderKey != $key || !is_array($sqlData)) {
                    continue;
                }

                $tempSql .= $sep . implode(', ', $sqlData);
                $sep = ', ';
            }
        }

        $resultSql = sprintf('ALTER TABLE %s %s',
            $connection->quoteIdentifier($tableName),
            $tempSql
        );

        $this->runQuery($resultSql);
        $this->sqlForCommit = array();
        return $this;
    }

    //####################################

    private function getColumnDefinition($nick)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTableName();
        $columnDefinition = '';

        if ($this->isColumnExists($nick) !== false) {
            $tableColumns = $connection->describeTable($tableName);

            if (isset($tableColumns[$nick])) {
                $columnDefinition = $connection->getColumnDefinitionFromDescribe($tableColumns[$nick]);
            }
        }

        return $columnDefinition;
    }

    private function getDefinition($type, $default = NULL, $after = NULL)
    {
        $definition = $type;

        if (!is_null($default)) {
            if ($default == 'NULL') {
                $definition .= ' DEFAULT NULL';
            } else {
                $definition .= ' DEFAULT ' . $this->getConnection()->quote($default);
            }
        }

        if (!empty($after) && $this->isColumnExists($after)) {
            $definition .= ' AFTER ' . $this->getConnection()->quoteIdentifier($after);
        }

        return $definition;
    }

    private function addQueryToCommit($key, $query)
    {
        if (isset($this->sqlForCommit[$key]) && in_array($query, $this->sqlForCommit[$key])) {
            return $this->sqlForCommit;
        }

        $this->sqlForCommit[$key][] = $query;
        return $this->sqlForCommit;
    }

    //####################################

    private function isColumnExists($nick)
    {
        return $this->getConnection()->tableColumnExists($this->getTableName(), $nick);
    }

    private function isIndexExists($nick)
    {
        $indexList = $this->getConnection()->getIndexList($this->getTableName());
        return isset($indexList[strtoupper($nick)]);
    }

    //####################################
}