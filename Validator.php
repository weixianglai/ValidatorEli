<?php

class Validator
{
    protected static $camelCache = [];

    public static function validate(array $data, array $rules, $messages = [])
    {
         foreach ($rules as $filed => $rule) {
             $realRules = explode('|', $rule);
             foreach ($realRules as $realRule) {
                 if (strpos($realRule, ':') !== false) {
                     list($method, $params) = explode(':', $realRule);
                 } else {
                     $method = $realRule;
                     $params = '';
                 }
                 $method = static::camel($method) . 'Validator';
                 if (method_exists(__CLASS__, $method)) {
                     static::$method($data, $filed, $params, $messages);
                 }
             }
         }
    }

    protected static function requiredValidator(array $data, $field, $params, $messages = [])
    {
        if (!isset($data[$field]) || $data[$field] === '') {
            $msg = $messages[$field . '.required'] ?? $field . '不能为空';
            throw new RuntimeException($msg);
        }
    }

    protected static function requiredIfExists(array $data, $field, $params, $message = [])
    {
        if (isset($data[$field])) {
            if ($data[$field] === '') {
                $msg = $message[$field . '.required_if_exists'] ?? $field . '不能为空';
                throw new RuntimeException($msg);
            }
        }
    }

    protected static function numericValidator(array $data, $field, $params, $messages = [])
    {
        if (isset($data[$field]) && ! is_numeric($data[$field])) {
            $msg = $messages[$field . '.numeric'] ?? $field . '必须是数字';
            throw new RuntimeException($msg);
        }
    }

    protected static function dateValidator(array $data, $field, $params, $messages = [])
    {
        if (isset($data[$field]) && ! static::checkDateFormat($data[$field])) {
            $msg = $messages[$field . '.date'] ?? $field . '时间格式错误';
            throw new RuntimeException($msg);
        }
    }

    protected static function minValidator(array $data, $field, $params, $messages = [])
    {
        if (isset($data[$field]) && $data[$field] < $params) {
            $msg = $messages[$field . '.min'] ?? $field . '不能小于' . $params;
            throw new RuntimeException($msg);
        }
    }

    protected static function maxValidator(array $data, $field, $params, $messages = [])
    {
        if (isset($data[$field]) && $data[$field] > $params) {
            $msg = $messages[$field . '.max'] ?? $field . '不能大于' . $params;
            throw new RuntimeException($msg);
        }
    }

    protected static function emailValidator(array $data, $field, $params, $messages = [])
    {
        if (isset($data[$field]) && filter_var($data[$field], FILTER_VALIDATE_EMAIL) === false) {
            $msg = $messages[$field . '.email'] ?? $field . '不是正确的email格式';
            throw new RuntimeException($msg);
        }
    }

    protected static function urlValidator(array $data, $field, $params, $messages = [])
    {
        if (isset($data[$field])) {
            $url = strpos($data[$field], 'http') === false ? 'http://' . $data[$field] : $data[$field];
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $msg = $messages[$field . '.email'] ?? $field . '不是正确的url格式';
                throw new RuntimeException($msg);
            }
        }
    }

    protected static function inValidator(array $data, $field, $params, $messages = [])
    {
        $params = explode(',', $params);
        if (isset($data[$field]) && ! in_array($data[$field], $params)) {
            $msg = $messages[$field . '.in'] ?? $field . '值不正确';
            throw new RuntimeException($msg);
        }
    }

    protected static function mobileValidator(array $data, $field, $params, $messages = [])
    {
        if (isset($data[$field])) {
            $mobile = str_replace(' ', '', $data[$field]);
            $msg = $messages[$field . '.mobile'] ?? $field . '格式不正确';
            if (strpos($mobile, '886') === 0 && strlen($mobile) !== 12) {
                throw new RuntimeException($msg);
            }
            if (strpos($mobile, '9') === 0 && strlen($mobile) !== 9) {
                throw new RuntimeException($msg);
            }

            if (! preg_match('/1[35678]\d{9}/', $mobile)) {
                throw new RuntimeException($msg);
            }
        }
    }

    protected static function parseDb($field, $params)
    {
        $connection = 'db';
        if (strpos($params, ',') !== false) {
            list($table, $params) = explode(',', $params, 2);
            if (strpos($params, ',') != false) {
                $connection = $table;
                list($table, $column) = explode(',', $params);
            } else {
                $column = $params;
            }
        } else {
            $table = $params;
            $column = $field;
        }

        return [$connection, $table, $column];
    }

    protected static function checkDateFormat($date)
    {
        if (date('Y-m-d H:i:s', strtotime($date)) == $date) {
            return true;
        }

        return false;
    }

    protected static function camel($value)
    {
        $key = $value;

        if (isset(static::$camelCache[$key])) {
            return static::$camelCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return static::$camelCache[$key] = lcfirst(str_replace(' ', '', $value));
    }

    protected static function lengthValidator(array $data, $field, $params, $messages = [])
    {
        if (isset($data[$field]) && mb_strlen($data[$field]) > $params) {
            $msg = $messages[$field . '.length'] ?? $field . '长度不能大于' . $params;
            throw new RuntimeException($msg);
        }
    }
}
