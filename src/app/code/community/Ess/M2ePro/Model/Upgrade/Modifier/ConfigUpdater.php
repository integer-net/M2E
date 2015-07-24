<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Modifier_ConfigUpdater extends Ess_M2ePro_Model_Upgrade_Modifier_Abstract
{
    //####################################

    public function insert($group, $key, $value = NULL, $notice = NULL)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTableName();

        $preparedData = array(
            'group' => $group,
            'key' => $key,
        );

        !is_null($value) && $preparedData['value'] = $value;
        !is_null($notice) && $preparedData['notice'] = $notice;
        $preparedData['update_date'] = $this->getCurrentDateTime();
        $preparedData['create_date'] = $this->getCurrentDateTime();

        return $connection->insert($tableName, $preparedData);
    }

    //####################################

    public function updateGroup($value, $where)
    {
        return $this->update('group', $value, $where);
    }

    public function updateKey($value, $where)
    {
        return $this->update('key', $value, $where);
    }

    public function updateValue($value, $where)
    {
        return $this->update('value', $value, $where);
    }

    private function update($field, $value, $where)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTableName();

        $preparedData = array(
            $field => $value,
            'update_date' => $this->getCurrentDateTime()
        );

        return $connection->update($tableName, $preparedData, $where);
    }

    //####################################

    public function delete($group, $key = NULL)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTableName();

        $where = array(
            '`group` = ?' => $group
        );

        if (!is_null($key)) {
            $where['`key` = ?'] = $key;
        }

        return $connection->delete($tableName, $where);
    }

    //####################################

    public function isExists($group, $key = NULL)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTableName();

        $query = $connection->select()
            ->from($tableName)
            ->where($connection->quoteInto('`group` = ?', $group));

        if (!is_null($key)) {
            $query->where($connection->quoteInto('`key` = ?', $key));
        }

        $result = $connection->fetchOne($query);
        return (bool) $result;
    }

    //####################################

    private function getCurrentDateTime()
    {
        return date('Y-m-d H:i:s', gmdate('U'));
    }

    //####################################
}