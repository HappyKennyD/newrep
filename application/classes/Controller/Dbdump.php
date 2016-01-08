<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Dbdump extends Controller_Core
{

    public function action_index()
    {
        $bd_host = '192.168.1.11';
        $bd_user = 'history';
        $bd_pass = '5inZCQAVTZhVmqI8ss15';
        $bd_name = 'history';
        $bd_count = 10; //сколько бэкапов хранить до удаления
        $dump_dir = APPPATH . 'dbdump'; // директория, куда будем сохранять резервную копию БД

        try {
            mkdir($dump_dir, 02777);
        } catch (Exception $e) {
            var_dump('эксэпшн случилсо');
        }

        $dump_name = date("Y-m-d_H-i-s").'_history'. ".sql"; //имя файла
        $insert_records = 100; //записей в одном INSERT
        $gzip = false; //упаковать файл дампа
        $stream = false; //вывод файла в поток

        $link = mysql_connect($bd_host, $bd_user, $bd_pass) or die("Сервер базы данных не доступен");
        $db = mysql_select_db($bd_name) or die("База данных не доступна");
        $res = mysql_query("SHOW TABLES") or die("Ошибка при выполнении запроса: " . mysql_error());
        $fp = fopen($dump_dir . "/" . $dump_name, "w");
        while ($table = mysql_fetch_row($res)) {
            $query = "";
            if ($fp) {
                $res1 = mysql_query("SHOW CREATE TABLE " . $table[0]);
                $row1 = mysql_fetch_row($res1);
                $query = "\nDROP TABLE IF EXISTS `" . $table[0] . "`;\n" . $row1[1] . ";\n";
                fwrite($fp, $query);
                $query = "";
                $r_ins = mysql_query('SELECT * FROM `' . $table[0] . '`') or die("Ошибка при выполнении запроса: " . mysql_error());
                if (mysql_num_rows($r_ins) > 0) {
                    $query_ins = "\nINSERT INTO `" . $table[0] . "` VALUES ";
                    fwrite($fp, $query_ins);
                    $i = 1;
                    while ($row = mysql_fetch_row($r_ins)) {
                        $query = "";
                        foreach ($row as $field) {
                            if (is_null($field)) $field = "NULL";
                            else $field = "'" . mysql_escape_string($field) . "'";
                            if ($query == "") $query = $field;
                            else $query = $query . ', ' . $field;
                        }
                        if ($i > $insert_records) {
                            $query_ins = ";\nINSERT INTO `" . $table[0] . "` VALUES ";
                            fwrite($fp, $query_ins);
                            $i = 1;
                        }
                        if ($i == 1) {
                            $q = "(" . $query . ")";
                        } else $q = ",(" . $query . ")";
                        fwrite($fp, $q);
                        $i++;
                    }
                    fwrite($fp, ";\n");
                }
            }
        }
        fclose($fp);

        if ($gzip || $stream) {
            $data = file_get_contents($dump_dir . "/" . $dump_name);
            $ofdot = "";
            if ($gzip) {
                $data = gzencode($data, 9);
                unlink($dump_dir . "/" . $dump_name);
                $ofdot = ".gz";
            }

            if ($stream) {
                header('Content-Disposition: attachment; filename=' . $dump_name . $ofdot);
                if ($gzip) header('Content-type: application/x-gzip'); else header('Content-type: text/plain');
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
                header("Pragma: public");
                echo $data;
            } else {
                $fp = fopen($dump_dir . "/" . $dump_name . $ofdot, "w");
                fwrite($fp, $data);
                fclose($fp);
            }
        }

        $files = scandir($dump_dir, 1);
        $spisok_del = array();
        foreach ($files as $key => $file) {
            if ($file != '.' and $file != '..') {
                if ($key > $bd_count - 1) {
                    $spisok_del[] = $file;
                }
            }
        }

        if (count($spisok_del) > 0) {
            foreach ($spisok_del as $item) {
                unlink($dump_dir . '/' . $item);
            }
        }

        echo ('</br>');

        $files = scandir($dump_dir, 1);
        foreach ($files as $key => $file) {
            echo $file.'</br>';
        }
        exit;

    }


}
