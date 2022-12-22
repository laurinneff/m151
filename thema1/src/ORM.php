<?php

declare(strict_types=1);

namespace App;

use App\Attribute\Column;
use App\Attribute\Model;

class ORM
{
    /**
     * Finds all rows matching a query. The query is matched using LIKE, so `['row' => 'test']` checks
     * if the value is equal to test, and `['row' => '%test']` checks if the value contains test.
     */
    public static function find(string $className, array $query = []): array
    {
        $query_dbnames = [];

        foreach ($query as $property => $value) {
            if (!self::isColumn($className, $property)) {
                throw new \Exception("Property $property is not a column");
            }

            $reflectionProperty = new \ReflectionProperty($className, $property);
            $type = $reflectionProperty->getType()->getName();
            $column = self::getColumnName($className, $property);

            if (class_exists($type) && self::isModel($type)) {
                $primaryKey = self::getPrimaryKey($type);
                $pkProperty = new \ReflectionProperty($type, $primaryKey);
                $value = $pkProperty->getValue($query[$property]);
            }

            $query_dbnames[$column] = $value;
        }

        $tableName = self::getTableName($className);

        $sql = "SELECT * FROM $tableName ";

        if (count($query) > 0) {
            $sql .= "WHERE ";
            $sql .= implode(' AND ', array_map(fn ($column) => "$column LIKE :$column", array_keys($query_dbnames)));
        }

        $db = Config::getConfig()->getDb();
        $stmt = $db->prepare($sql);
        $stmt->execute($query_dbnames);

        $out = [];
        $reflectionClass = new \ReflectionClass($className);
        $columns = self::getColumns($className);
        while (($row = $stmt->fetch()) !== false) {
            $instance = $reflectionClass->newInstanceWithoutConstructor();

            foreach ($columns as $property => $column) {
                $reflectionProperty = $reflectionClass->getProperty($property);
                $type = $reflectionProperty->getType()->getName();

                if (class_exists($type) && self::isModel($type)) {
                    $reflectionProperty->setValue(
                        $instance,
                        self::find(
                            $type,
                            [self::getPrimaryKey($type) => $row[$column]]
                        )[0]
                    );
                    continue;
                }

                $reflectionProperty->setValue($instance, $row[$column]);
            }

            $out[] = $instance;
        }

        return $out;
    }

    /**
     * Finds the first row matching a query. The query is matched using LIKE, so `['row' => 'test']` checks
     * if the value is equal to test, and `['row' => '%test']` checks if the value contains test.
     */
    public static function findOne(string $className, array $query = []): ?object
    {
        $result = self::find($className, $query);
        return count($result) > 0 ? $result[0] : null;
    }

    public static function insert($object): void
    {
        $className = get_class($object);
        if (!self::isModel($className)) {
            throw new \Exception("Class $className is not a model");
        }

        $tableName = self::getTableName($className);
        $columns = self::getColumns($className);

        $sql = "INSERT INTO $tableName (";
        $sql .= implode(', ', $columns);
        $sql .= ") VALUES (";
        $sql .= implode(', ', array_map(fn ($column) => ":$column", $columns));
        $sql .= ")";

        $params = [];
        $reflectionClass = new \ReflectionClass($className);
        foreach ($columns as $property => $column) {
            // We can't use $object->$property if it's private, so we use reflection
            $reflectionProperty = $reflectionClass->getProperty($property);
            $type = $reflectionProperty->getType()->getName();

            if (class_exists($type) && self::isModel($type)) {
                $primaryKey = self::getPrimaryKey($type);
                $pkProperty = new \ReflectionProperty($type, $primaryKey);
                $value = $pkProperty->getValue($reflectionProperty->getValue($object));
            } else {
                $value = $reflectionProperty->getValue($object);
            }

            $params[$column] = $value;
        }

        $db = Config::getConfig()->getDb();
        $db->prepare($sql)->execute($params);
    }

    public static function update($object): void
    {
        $className = get_class($object);
        if (!self::isModel($className)) {
            throw new \Exception("Class $className is not a model");
        }

        $tableName = self::getTableName($className);
        $columns = self::getColumns($className);
        $primaryKey = self::getPrimaryKey($className);

        $sql = "UPDATE $tableName SET ";
        $sql .= implode(', ', array_map(fn ($key) => "$key = :$key", $columns));
        $sql .= " WHERE $primaryKey = :$primaryKey";

        $params = [];
        $reflectionClass = new \ReflectionClass($className);
        foreach ($columns as $property => $column) {
            // We can't use $object->$property if it's private, so we use reflection
            $reflectionProperty = $reflectionClass->getProperty($property);
            $type = $reflectionProperty->getType()->getName();

            if (class_exists($type) && self::isModel($type)) {
                $primaryKey = self::getPrimaryKey($type);
                $pkProperty = new \ReflectionProperty($type, $primaryKey);
                $value = $pkProperty->getValue($reflectionProperty->getValue($object));
            } else {
                $value = $reflectionProperty->getValue($object);
            }

            $params[$column] = $value;
        }

        $db = Config::getConfig()->getDb();
        $db->prepare($sql)->execute($params);
    }

    public static function delete($object): void
    {
        $className = get_class($object);
        if (!self::isModel($className)) {
            throw new \Exception("Class $className is not a model");
        }

        $tableName = self::getTableName($className);
        $primaryKey = self::getPrimaryKey($className);

        $sql = "DELETE FROM $tableName WHERE $primaryKey = :$primaryKey";

        $params = [];
        $reflectionClass = new \ReflectionClass($className);
        $reflectionProperty = $reflectionClass->getProperty($primaryKey);
        $value = $reflectionProperty->getValue($object);
        $params[$primaryKey] = $value;

        $db = Config::getConfig()->getDb();
        $db->prepare($sql)->execute($params);
    }

    private static function isModel(string $className): bool
    {
        $reflectionClass = new \ReflectionClass($className);
        $attributes = $reflectionClass->getAttributes(Model::class);
        return count($attributes) > 0;
    }

    private static function isColumn(string $className, string $propertyName): bool
    {
        $reflectionClass = new \ReflectionClass($className);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $attributes = $reflectionProperty->getAttributes(Column::class);
        return count($attributes) > 0;
    }

    private static function getTableName(string $className): string
    {
        if (!self::isModel($className)) {
            throw new \Exception("Class $className is not a model");
        }

        $reflectionClass = new \ReflectionClass($className);
        $attributes = $reflectionClass->getAttributes(Model::class);

        $attribute = $attributes[0]->newInstance();
        $tableName = $attribute->name;
        if ($tableName === null) {
            $tableName = strtolower($reflectionClass->getShortName() . 's');
        }
        return $tableName;
    }

    private static function getColumns(string $className): array
    {
        $reflectionClass = new \ReflectionClass($className);
        $columns = [];
        foreach ($reflectionClass->getProperties() as $property) {
            if (!self::isColumn($className, $property->getName())) {
                continue;
            }

            $columnName = self::getColumnName($className, $property->getName());
            $columns[$property->getName()] = $columnName;
        }
        return $columns;
    }

    private static function getColumnName(string $className, string $propertyName): string
    {
        if (!self::isColumn($className, $propertyName)) {
            throw new \Exception("Property $propertyName is not a column");
        }

        $reflectionClass = new \ReflectionClass($className);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $attributes = $reflectionProperty->getAttributes(Column::class);

        $attribute = $attributes[0]->newInstance();
        $columnName = $attribute->name;
        if ($columnName === null) {
            $columnName = strtolower($propertyName);
        }
        return $columnName;
    }

    private static function getPrimaryKey(string $className): string
    {
        if (!self::isModel($className)) {
            throw new \Exception("Class $className is not a model");
        }

        $reflectionClass = new \ReflectionClass($className);

        $primaryKey = null;

        foreach ($reflectionClass->getProperties() as $property) {
            if (!self::isColumn($className, $property->getName())) {
                continue;
            }

            $attributes = $property->getAttributes(Column::class);

            $attribute = $attributes[0]->newInstance();
            if ($attribute->primaryKey) {
                if ($primaryKey !== null) {
                    throw new \Exception("Multiple primary keys found for class $className. Remove the `primaryKey` parameter from `$primaryKey` or `{$property->getName()}`.");
                }
                $primaryKey= $property->getName();
            }
        }

        if ($primaryKey === null) {
            throw new \Exception("No primary key found for class $className. Create a column with the parameter `primaryKey` set to true.");
        }

        return $primaryKey;
    }
}
