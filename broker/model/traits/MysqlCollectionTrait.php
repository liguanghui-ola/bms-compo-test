<?php

namespace Imee\Compo\Broker\Model\Traits;

use Phalcon\Di;

trait MysqlCollectionTrait
{
    public static $opMapping = [
        'EQ'          => '=',
        'NEQ'         => '!=',
        'GT'          => '>',
        'LT'          => '<',
        'EGT'         => '>=',
        'ELT'         => '<=',
        'IN'          => 'IN',
        'NOT IN'      => 'NOT IN',
        '='           => '=',
        '<>'          => '<>',
        '!='          => '!=',
        '>'           => '>',
        '<'           => '<',
        '>='          => '>=',
        '<='          => '<=',
        'LIKE'        => 'LIKE',
        'LLIKE'       => 'LIKE',
        'RLIKE'       => 'LIKE',
        'IS NULL'     => 'IS NULL',
        'IS NOT NULL' => 'IS NOT NULL',
        'FIND_IN_SET' => 'FIND_IN_SET',
    ];

    /**
     * 获取列表和总数
     * @param array $condition
     * $condition = [];
     * $condition[] = ['time', '>=', $endTime]
     * $condition[] = ['time', '=', $endTime]
     * @param string $field
     * @param string $order
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public static function getListAndTotal(array $condition, string $field = '*', string $order = '', int $page = 0, int $pageSize = 0): array
    {
        $model = static::query();
        $order = trim($order);

        // 设置字段
        $field && $model->columns($field);

        $bind = [];
        // region condition start
        if (!empty($condition)) {
            list($model, $bind) = self::parseCondition($model, $condition);
        }

        $total = self::count([
            'conditions' => $model->getConditions(),
            'bind'       => $bind,
        ]);

        if (!$total) {
            return ['data' => [], 'total' => 0];
        }

        if (!empty($order)) {
            $model->orderBy($order);
        }
        if ($page && $pageSize) {
            $startLimit = ($page - 1) * $pageSize;
            $model->limit($pageSize, $startLimit);
        }
        $data = $model->execute()->toArray();
        return ['data' => $data, 'total' => $total];
    }

    public static function getCount($condition = []): int
    {
        $model = static::query();
        $bind = [];
        if (!empty($condition)) {
            list($model, $bind) = self::parseCondition($model, $condition);
        }
        return self::count([
            'conditions' => $model->getConditions(),
            'bind'       => $bind,
        ]);
    }

    public static function findOne($id, $isMaster = false): array
    {
        if ($isMaster) {
            $db = self::SCHEMA;
        } else {
            $db = self::SCHEMA_READ;
        }
        $pk = static::$primaryKey ?? 'id';
        $info = static::useDb($db)->findFirst([
            'conditions' => "{$pk} = :{$pk}:",
            'bind'       => [$pk => $id],
        ]);

        if (!$info) {
            return [];
        }
        return $info->toArray();
    }

	public static function findOneByWhere($condition, $isMaster = false): array
	{
		if ($isMaster) {
			$model = static::useDb(self::SCHEMA)->query();
		} else {
			$model = static::query();
		}
        if (!empty($condition)) {
            list($model, $_) = self::parseCondition($model, $condition);
        }
        $model->limit(1, 0);
        return $model->execute()->toArray()[0] ?? [];
    }

    public static function findByIds($id, $columns = '*'): array
    {
        if (!$id) {
            return [];
        }
        if (!is_array($id)) {
            $id = [$id];
        } else {
            $id = array_filter($id);
            $id = array_unique($id);
            $id = array_values($id);
        }
        $pk = static::$primaryKey ?? 'id';
        return static::find([
        	'columns' => $columns,
            'conditions' => "{$pk} in ({{$pk}:array})",
            'bind'       => [$pk => $id],
        ])->toArray();
    }

    public static function findAll(): array
    {
        return static::find()->toArray();
    }

    /**
     * 批量插入
     * INSERT IGNORE 忽略错误插入
     * @param array $data
     * @param string $op
     * @return array
     */
    public static function addBatch(array $data, string $op = 'INSERT'): array
    {
        if (!$data) {
            return [false, 'data 不能为空', 0];
        }
        $table = (new static)->getSource();
        $schema = self::SCHEMA;
        $keyNames = array_keys($data[0]);
        $keys = array_map(function ($key) {
            return "`{$key}`";
        }, $keyNames);
        $keys = implode(',', $keys);
        $sql = "{$op} INTO {$table} ({$keys}) VALUES ";
        foreach ($data as $v) {
            $v = array_map(function ($value) {
                return "'" . addslashes(trim($value)) . "'";
            }, $v);
            $values = implode(',', array_values($v));
            $sql .= " ({$values}), ";
        }
        $sql = rtrim(trim($sql), ',');
        $rows = 0;
        try {
            $conn = Di::getDefault()->getShared($schema);
            if ($conn->execute($sql)) {
                $rows = $conn->affectedRows();
            }
        } catch (\PDOException $exception) {
            return [false, $exception->getMessage(), 0];
        }
        return [true, '', $rows];
    }

    /**
     * 批量更新
     *
     * $tmp = [];
     * $tmp['state_check'] = 1;
     * $tmp['resource_type'] = 1;
     * $list[$val['id']] = $tmp;//切记每个tmp数据结构一致
     *
     * @param $list
     * @return array
     */
    public static function updateBatch($list): array
    {
        $rows = 0;
        if (!$list) {
            return [false, '更新内容不能为空', $rows];
        }
        $table = (new static)->getSource();
        $schema = self::SCHEMA;
        $pk = static::$primaryKey ?? 'id';

        $sqlSet = [];
        $pkVals = [];
        foreach ($list as $pkVal => $val) {
            $pkVals[] = $pkVal;
            foreach ($val as $updateKey => $updateVal) {
                $sqlSet[$updateKey][] = " WHEN {$pkVal} THEN '{$updateVal}'";
            }
        }
        if ($pkVals && $sqlSet) {
            $sqlSetNew = [];
            foreach ($sqlSet as $updateKey => $updateList) {
                $sqlSetNew[] = "{$updateKey} = CASE {$pk} " . implode('', $updateList);
            }
            $sqlSetStr = implode('END,', $sqlSetNew);
            $sql = "UPDATE {$table} SET {$sqlSetStr}";
            $pkValStr = implode(',', $pkVals);
            $sql .= " END WHERE {$pk} IN ( {$pkValStr} )";
            try {
                $conn = Di::getDefault()->getShared($schema);
                if ($conn->execute($sql)) {
                    $rows = $conn->affectedRows();
                }
            } catch (\PDOException $exception) {
                return [false, $exception->getMessage(), $rows];
            }
        }
        return [true, '', $rows];
    }

    /**
     * 批量删除
     * @param array $condition
	 * @param int $limit
     * $condition = [];
     * $condition[] = ['time', '>=', $endTime]
     * $condition[] = ['time', '=', $endTime]
     * @return array
     */
    public static function deleteByWhere(array $condition, int $limit = 100000): array
    {
        if (!$condition) {
            return [false, 'condition 不能为空', 0];
        }
        $model = static::query();
        list($model, $bind) = self::parseCondition($model, $condition);
        $where = $model->getConditions();
		foreach ($bind as $key => $val) {
			if (is_array($val)) {
				$key = "{{$key}:array}";
				$val = array_map(function ($v) {
					return "'{$v}'";
				}, $val);
				$val = implode(',', $val);
			} else {
				$key = ":{$key}:";
				$val = "'{$val}'";
			}
			$where = str_replace($key, $val, $where);
		}

        $table = (new static)->getSource();
        $schema = self::SCHEMA;
        $count = 0;
        try {
            $conn = Di::getDefault()->getShared($schema);
			$sql = "DELETE FROM {$table} WHERE {$where} LIMIT {$limit}";
			while (true){
				$rows = 0;
				if ($conn->execute($sql)) {
					$rows = $conn->affectedRows();
				}
				$count += $rows;
				if($rows < $limit){
					break;
				}
				usleep(1000);
			}
        } catch (\PDOException $exception) {
            return [false, $exception->getMessage(), 0];
        }
        return [true, '', $count];
    }

    /**
     * 获取列表
     * @param array $condition
     * $condition = [];
     * $condition[] = ['time', '>=', $endTime]
     * $condition[] = ['time', '=', $endTime]
     * @param string $field
     * @param string $order
     * @param int $limit
     * @return array
     */
    public static function getListByWhere(array $condition, string $field = '*', string $order = '', int $limit = 0): array
    {
        $model = static::query();
        $order = trim($order);

        // 设置字段
        $field && $model->columns($field);

        // region condition start
        if (!empty($condition)) {
            list($model, $_) = self::parseCondition($model, $condition);
        }

        if (!empty($order)) {
            $model->orderBy($order);
        }

        if ($limit) {
            $startLimit = 0;
            $model->limit($limit, $startLimit);
        }
        return $model->execute()->toArray();
    }

	/**
	 * 迭代返回list
	 * @param array $condition
	 * @param string $field
	 * @param string $order
	 * @param int $limit
	 * @return \Generator
	 */
    public static function getGeneratorListByWhere(array $condition, string $field = '*', string $order = '', int $limit = 1000): \Generator
    {
        $pk = static::$primaryKey ?? 'id';
        $pkValue = 0;
        while (true) {
            $model = static::query();
            $order = trim($order);
            $field && $model->columns($field);
            $model->andWhere("{$pk} > :{$pk}:", [$pk => $pkValue]);
            if (!empty($condition)) {
                list($model, $_) = self::parseCondition($model, $condition);
            }
            if (!empty($order)) {
                $model->orderBy($order);
            }
            if ($limit) {
                $startLimit = 0;
                $model->limit($limit, $startLimit);
            }
            yield $data = $model->execute()->toArray();
            if (count($data) < $limit) {
                break;
            }
            $max = end($data);
            $pkValue = $max[$pk];
        }
    }

    protected static function parseCondition($model, $condition): array
    {
        $func = function ($symbol, $key, $value) use (&$model, &$bind) {
            $symbolMap = self::_comparisonSymbolMap($symbol);
            $bindKey = str_replace('.', '_', $key);
            switch (strtoupper($symbol)) {
                case 'IN':
                    $bindKey .= "_IN";
                    $bindValue = !empty($value) ? $value : [-99999];
                    $model->andWhere("{$key} IN ({{$bindKey}:array})", [$bindKey => $bindValue]);
                    break;
                case 'NOT IN':
                    $bindKey .= "_NIN";
                    $bindValue = !empty($value) ? $value : [-99999];
                    $model->andWhere("{$key} NOT IN ({{$bindKey}:array})", [$bindKey => $bindValue]);
                    break;
                case 'LIKE':
                    $bindKey .= "_LIKE";
                    $bindValue = "%{$value}%";
                    $model->andWhere("{$key} LIKE :{$bindKey}:", [$bindKey => $bindValue]);
                    break;
                case 'LLIKE':
                    $bindKey .= "_LLIKE";
                    $bindValue = "{$value}%";
                    $model->andWhere("{$key} LIKE :{$bindKey}:", [$bindKey => $bindValue]);
                    break;
                case 'RLIKE':
                    $bindKey .= "_RLIKE";
                    $bindValue = "%{$value}";
                    $model->andWhere("{$key} LIKE :{$bindKey}:", [$bindKey => $bindValue]);
                    break;
                case 'IS NULL':
                    $model->andWhere("{$key} IS NULL");
                    break;
                case 'IS NOT NULL':
                    $model->andWhere("{$key} IS NOT NULL");
                    break;
                case 'FIND_IN_SET':
                    $bindValue = (string)$value;
                    $model->andWhere("FIND_IN_SET(:{$bindKey}:, {$key})", [$bindKey => $bindValue]);
                    break;
                default:
                    $bindKey .= uniqid();
                    $bindValue = $value;
                    $model->andWhere("{$key} {$symbolMap} :{$bindKey}:", [$bindKey => $bindValue]);
                    break;
            }
            isset($bindValue) && $bind[$bindKey] = $bindValue;
        };

        foreach ($condition as $item) {
            if (!is_array($item)) {
                continue;
            }
            list($key, $symbol, $value) = $item;
            $func($symbol, $key, $value);
        }

        return [$model, $bind];
    }

    public static function add($data, $logAttr = []): array
    {
        $pk = static::$primaryKey ?? 'id';
        $model = static::useDb(self::SCHEMA);
        if (!empty($logAttr)) {
            $model->setLogAttr($logAttr);
        }
        //兼容字段里有source报错问题
        if (isset($data['source'])) {
            try {
                foreach ($data as $k => $v) {
                    $model->{$k} = $v;
                }
                $model->save();
                return [true, $model->$pk];
            } catch (\PDOException $e) {
                return [false, $e->getMessage()];
            }
        }

        try {
            $insertId = 0;
            if ($model->create($data)) {
                $insertId = $model->$pk;
            }
            return [true, $insertId];
        } catch (\PDOException $e) {
            return [false, $e->getMessage()];
        }
    }

    public static function edit($id, $data, $logAttr = []): array
    {
        $pk = static::$primaryKey ?? 'id';
        $rec = static::useDb(self::SCHEMA)
            ->findFirst([
                "conditions" => "{$pk} = :id:",
                "bind"       => ["id" => $id],
            ]);

        if (!$rec) {
            return [false, '未查到该id记录'];
        }

        foreach ($data as $key => $value) {
            $rec->$key = $value;
        }

        try {
            if (!empty($logAttr)) {
                $rec->setLogAttr($logAttr);
            }
            $result = $rec->save();
            return [true, $result];
        } catch (\PDOException $e) {
            return [false, $e->getMessage()];
        }
    }

    public static function getBatchCommonNew($ids, $fields = [], $searchKey = null): array
    {
        if (empty($ids)) {
            return [];
        }

        if (!$searchKey) {
            $searchKey = static::$primaryKey ?? 'id';
        }

        if (!in_array($searchKey, $fields)) {
            $fields [] = $searchKey;
        }
        $result = static::find(["{$searchKey} in ({id:array})", 'bind' => ['id' => $ids], 'columns' => $fields])->toArray();
        return array_column($result, null, $searchKey);
    }

    public static function deleteById($id, $logAttr = []): bool
    {
        $pk = static::$primaryKey ?? 'id';
        $rec = static::useDb(self::SCHEMA)
            ->findFirst([
                "conditions" => "{$pk} = :id:",
                "bind"       => ["id" => $id],
            ]);

        if (!$rec) {
            return false;
        }

        if (!empty($logAttr)) {
            $rec->setLogAttr($logAttr);
        }

        return $rec->delete();
    }

    /**
     * 获取比较符号
     * @param null $symbol
     * @return string
     */
    private static function _comparisonSymbolMap($symbol = null): string
    {
        $symbol = strtoupper($symbol);
        $map = self::$opMapping;
        return $map[$symbol] ?? $map['EQ'];
    }
}