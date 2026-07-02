<?php

if (!function_exists('h')) {
    function h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('db_available')) {
    function db_available($conn)
    {
        return $conn instanceof PDO;
    }
}

if (!function_exists('table_exists')) {
    function table_exists($conn, $table)
    {
        if (!db_available($conn)) {
            return false;
        }

        try {
            $stmt = $conn->prepare('SHOW TABLES LIKE ?');
            $stmt->execute([$table]);
            return (bool) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('table_columns')) {
    function table_columns($conn, $table)
    {
        static $cache = [];

        if (!db_available($conn)) {
            return [];
        }

        if (isset($cache[$table])) {
            return $cache[$table];
        }

        try {
            $columns = [];
            $stmt = $conn->query('DESCRIBE `' . str_replace('`', '``', $table) . '`');
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
                $columns[$column['Field']] = true;
            }
            $cache[$table] = $columns;
        } catch (Throwable $e) {
            $cache[$table] = [];
        }

        return $cache[$table];
    }
}

if (!function_exists('has_column')) {
    function has_column($conn, $table, $column)
    {
        $columns = table_columns($conn, $table);
        return isset($columns[$column]);
    }
}

if (!function_exists('field')) {
    function field($row, $key, $default = '')
    {
        if (is_array($row) && array_key_exists($key, $row)) {
            return $row[$key];
        }

        if (is_object($row) && isset($row->{$key})) {
            return $row->{$key};
        }

        return $default;
    }
}

if (!function_exists('format_date')) {
    function format_date($value, $format = 'M j, Y')
    {
        if (!$value) {
            return 'Not set';
        }

        $timestamp = strtotime((string) $value);
        return $timestamp ? date($format, $timestamp) : 'Not set';
    }
}

if (!function_exists('excerpt')) {
    function excerpt($value, $length = 150)
    {
        $text = trim(strip_tags(html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8')));
        if (strlen($text) <= $length) {
            return $text;
        }

        return rtrim(substr($text, 0, $length - 3)) . '...';
    }
}

if (!function_exists('approved_jobs_where')) {
    function approved_jobs_where($conn, &$params)
    {
        if (has_column($conn, 'jobs', 'status')) {
            return 'status = :approved_status';
        }

        return '1 = 1';
    }
}

