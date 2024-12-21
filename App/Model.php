<?php

namespace App;

use App\Db;
use App\Errors;
use App\Exceptions\ModelException;

abstract class Model
{
    public const TABLE = '';

    public static function findAll()
    {
        $db = new Db();
        $query = 'SELECT * FROM ' . static::TABLE;
        return $db->query($query,
            static::class, null);
    }

    public static function findById($id)
    {
        $db = new Db();

        $query = 'SELECT * FROM ' . static::TABLE . ' WHERE id=:id';
        $data = $db->query($query, static::class, [':id' => $id]);
        if ($data) {
            return $data[0];
        } else {
            throw new \App\Exceptions\Error404('Запись не найдена.');
        }
    }

    public function insert()
    {
        $fields = get_object_vars($this);
        $cols = [];
        $data = [];

        foreach ($fields as $name => $value) {
            if ('id' === $name) {
                continue;
            }
            $cols[] = $name;
            $data[':' . $name] = $value;
        }

        $query = 'INSERT INTO ' . static::TABLE . '
        ('. implode(', ', $cols) .')
        VALUES
        ('. implode(', ', array_keys($data)) .')';

        $db = new Db();
        $db->execute($query, $data);
        $this->id = $db->getLastId();
    }

    public function update()
    {
        $fields = get_object_vars($this);

        $data = [];
        foreach ($fields as $name => $value) {
            $data[':' . $name] = $value;
        }

        $query = 'UPDATE ' . static::TABLE . ' SET ';

        foreach ($fields as $name => $value) {
            if ('id' === $name) {
                continue;
            }
            $query .= $name . '=:' . $name . ', ';
        }

        $query = substr($query, 0, -2);
        $query .= ' WHERE id=:id';

        $db = new Db();
        $db->execute($query, $data);
    }

    public function save()
    {
        $query = 'SELECT EXISTS(SELECT id FROM ' . static::TABLE . ' WHERE id=:id);';

        $db = new Db();
        if (1 === $db->checkID($query, [':id' => $this->id])) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    public function delete()
    {
        $query = 'DELETE FROM ' . static::TABLE . ' WHERE id=:id';
        $db = new Db();
        $db->execute($query, [':id' => $this->id]);
    }

    public function fill(array $data)
    {
        $errors = new Errors;

        $cols = get_object_vars($this);
        foreach ($cols as $col => $value) {
            $cols[$col] = $col;
        }

        /**
         * Смотрим, одинаковое ли количество свойств объекта и элементов массива,
         * чтобы сработал метод array_combine
         */

        if (count($cols) != count($data)) {
            $errors->add(new \App\Exceptions\ModelException('Количество данных не соответствует количеству свойств объекта'));
            throw $errors;
        }

        $data1 = array_combine($cols, $data);

        foreach ($data1 as $col => $value) {
            /**
             * Проверяем значение на пустоту
             */
            if (empty($value)) {
                $errors->add(new \App\Exceptions\ModelException('Что-то незаполнено.'));
                break;
            }

            /**
             * Если текст, то проверка на размер передаваемого текста
             */

            if (is_string($value) && strlen($value) > 1000) {
                $errors->add(new \App\Exceptions\ModelException('Слишком длинный текст.'));
                break;
            }

            /**
             * У новостной статьи может быть только числовой айди
             */

            if ('id' === $col && !is_numeric($value)) {
                $errors->add(new \App\Exceptions\ModelException('ID может быть только числом.'));
                break;
            }

            $this->$col = $value;

        }

        if (!$errors->empty()) {
            throw $errors;
        }
    }

    public static function getColumns()
    {
        return array_keys(get_class_vars(static::class));
    }

    public function unpacking($data)
    {
        foreach ($data as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

}