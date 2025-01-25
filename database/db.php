<?php
class DBConnect
{
    private static $instance=NULL;
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $db = new PDO("sqlite:database/tables.sqlite3");
            $db->query("PRAGMA foreign_keys = '1';");
            self::$instance = $db;
        }
        return self::$instance;
    }

    public static function closeInstance()
    {
        if (isset(self::$instance)) {
            self::$instance=NULL;
        }
    }
}
