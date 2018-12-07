<?php
class FujiLogger {
    private $filename;
    public function __construct($log_filename) {
        $this->filename = $log_filename;
    }

    public function log_write_postresult($insert, $update, $errors, $ignores) {
        $date = date('Y/m/d-H:i:s');
        $result = <<<HEREDOC
$date
$insert inserted
$update updated
$errors error
$ignores ignore
-----------------------------------------

HEREDOC;
        $this->log_write($result);
    }

    public function log_write2($result, $property) {
        $this->log_write("[$result] " . $property->get_prop_id() . ' : ' . $property->get_title() . PHP_EOL);
    }

    private function log_write($message) {
        echo $message;
        file_put_contents($this->filename, $message, FILE_APPEND | LOCK_EX);
    }
}

/*
    echo <<<HEREDOC
$suc_count 件登録完了
$err_count 件登録失敗
$ign_count 件無視
HEREDOC;
}
*/