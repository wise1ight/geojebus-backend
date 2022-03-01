<?php
namespace db_storage;

class db {
    function connect() {
        return mysqli_connect('localhost', 'gbappdata', 'SLzHGUvhyQYkgf3N', 'gbappdata');
    }
}
?>
