<?php
include('./_auth.php');

error_reporting(E_ALL);

$tableList = array('bus_country','bus_route','bus_route_additional','bus_stop','bus_type');

/**
 * Instantiate Backup_Database and perform backup
 */
$backupDatabase = new Backup_Database();
$status = $backupDatabase->backupTables($tableList) ? 'OK' : 'FAIL';
echo "<br>DB 생성 결과: ".$status;

/**
 * The Backup_Database class
 */
class Backup_Database {

    /**
     * Database charset
     */
    var $charset = '';
    public static $connect_db;

    /**
     * Constructor initializes database
     */
    function Backup_Database($charset = 'utf8')
    {
        $this->charset  = $charset;

        $this->initializeDatabase();
    }

    protected function initializeDatabase()
    {
        $this::$connect_db = mysqli_connect('localhost', 'gbappdata', 'SLzHGUvhyQYkgf3N', 'gbappdata');
        if (!mysqli_set_charset ($this::$connect_db, $this->charset))
        {
            mysqli_query($this::$connect_db,'SET NAMES '.$this->charset);
        }
    }

    /**
     * Backup the whole database or just some tables
     * Use '*' for whole database or 'table1 table2 table3...'
     * @param string $tables
     */
    public function backupTables($tables = '*')
    {
        try
        {
            /**
            * Tables to export
            */
            if($tables == '*')
            {
                $tables = array();
                $result = mysqli_query($this::$connect_db, 'SHOW TABLES');
                while($row = mysqli_fetch_row($result))
                {
                    $tables[] = $row[0];
                }
            }
            else
            {
                $tables = is_array($tables) ? $tables : explode(',',$tables);
            }

            //$sql = 'CREATE DATABASE IF NOT EXISTS '.$this->dbName.";\n\n";
            //$sql .= 'USE '.$this->dbName.";\n\n";

			$sql = "";

            /**
            * Iterate tables
            */
            foreach($tables as $table)
            {
                echo $table." 테이블 담는 중...";

                $result = mysqli_query($this::$connect_db, 'SELECT * FROM '.$table);
                $numFields = mysqli_num_fields($result);

                $sql .= 'DROP TABLE IF EXISTS '.$table.';';
                mysqli_query($this::$connect_db, 'SET sql_mode = \'MAXDB\'');
                $row2 = mysqli_fetch_row(mysqli_query($this::$connect_db, 'SHOW CREATE TABLE '.$table));
				$row_rep = preg_replace("/([a-z]{2})_idx/i","_id",$row2[1]);
                $sql.= "\n\n".$row_rep.";\n";

                for ($i = 0; $i < $numFields; $i++)
                {
                    while($row = mysqli_fetch_row($result))
                    {
                        $sql .= 'INSERT INTO '.$table.' VALUES(';
                        for($j=0; $j<$numFields; $j++)
                        {
                            $row[$j] = addslashes($row[$j]);
                            //$row[$j] = ereg_replace("\n","\\n",$row[$j]);
                            $row[$j] = preg_replace("/\n/","\\n",$row[$j]);
                            if (isset($row[$j]))
                            {
                                $sql .= '"'.$row[$j].'"' ;
                            }
                            else
                            {
                                $sql.= '""';
                            }

                            if ($j < ($numFields-1))
                            {
                                $sql .= ',';
                            }
                        }

                        $sql.= ");\n";
                    }
                }

                $sql.="\n";

                echo " OK<br>" . "";
            }
        }
        catch (Exception $e)
        {
            var_dump($e->getMessage());
            return false;
        }

		//여기서 버전정보만 더 만들어 보자

        return $this->saveFile($sql);
    }

    /**
     * Save SQL to file
     * @param string $sql
     */
    protected function saveFile(&$sql)
    {
        if (!$sql) return false;

        try {
            date_default_timezone_set("Asia/Seoul");
            $version = date("Ymd-His", time());
            $db = new PDO("sqlite:../database/android/$version.sqlite");
            $db->setAttribute(PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION);
            $db->exec($sql);
            $db->exec("CREATE TABLE version ('_id' INTEGER, 'version' VARCHAR);");
            $db->exec("INSERT INTO version VALUES('0','$version');");
            mysqli_query($this::$connect_db, "update bus_app set app_db_version = '$version' WHERE `app_platform` = 'android' AND `app_version` = '2.1.0'");
            $db = null;
            return true;
        } catch(PDOException $e) {
            print 'Exception : '.$e->getMessage();
            return false;
        }
    }
}

?>
