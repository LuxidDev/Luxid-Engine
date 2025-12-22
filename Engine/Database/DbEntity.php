<?php

namespace Luxid\Database;

use Luxid\ORM\Entity;
use Luxid\Foundation\Application;

/**
    This would be an entity which would be like an ORM
    and would map the User's Entity into the Database Table.
    Base Active Record Class (extends Luxid\ORM\Entity)
*/

abstract class DbEntity extends Entity
{
    abstract public static function tableName(): string;
    abstract public function attributes(): array; // -> all db column names
    abstract public static function primaryKey(): string;

    public function save(): bool
    {
        $tableName = $this->tableName();
        $attributes = $this->attributes();

        $params = array_map(fn($attr) => ":$attr", $attributes);

        $statement = self::prepare("
            INSERT INTO $tableName (".implode(',', $attributes).")
            VALUES(".implode(',', $params) . ")
        ");

        foreach ($attributes as $attribute) {
            $statement->bindValue(":$attribute", $this->{$attribute});
        }

        $statement->execute();
        return true;
    }

    public static function findOne($where): bool|object|null  // -> [email => jhay@gmail.com, firstname => jhay]
    {
        $tableName = static::tableName();
        $attributes = array_keys($where);
        $sql = implode(" AND ", array_map(fn($attr) => "$attr = :$attr", $attributes));

        // SELECT * FROM $tableName WHERE email = :email AND firstname = :firstname
        $statement = static::prepare("SELECT * FROM $tableName WHERE $sql");
        foreach ($where as $key => $item) {
            $statement->bindValue(":$key", $item);
        }

        $statement->execute();
        return $statement->fetchObject(static::class) ?: null;  // gives me an instance of the user class
    }

    public static function prepare($sqlStatement)
    {
        return Application::$app->db->pdo->prepare($sqlStatement);
    }
}
