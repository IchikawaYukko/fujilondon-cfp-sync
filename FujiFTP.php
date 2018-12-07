<?php
class FujiFTP {
    private $server, $user, $pass;
    const CFP_DIR = 'cfp-temp/';

    public function __construct() {
        $this->server = 'VEBRA_SERVER';
        $this->user   = 'USERNAME';
        $this->pass   = 'PASSWORD';
    }

    public function download_property() {
        $target_filename = [
            'files.txt',
            'vebraproperties.txt'
        ];

        $conn = ftp_connect($this->server) or die('ftp connection failed');
        ftp_login($conn, $this->user, $this->pass) or die('ftp login failed');
        ftp_pasv($conn, true);

        foreach($target_filename as $file) {
            ftp_get($conn, self::CFP_DIR.$file, $file, FTP_BINARY) or die("fit get $file failed");
        }
        ftp_close($conn);
    }
}