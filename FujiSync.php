<?php
class FujiSync {
    private $properties_array;
    private $images_array;
    private $setting;
    private $logger;

    public function __construct() {
        $this->setting = new FujiSettings();
        $this->logger = new FujiLogger($this->setting->settings['log_filename']);
    }

    public function sync() {
        // TODO uncomment later
        //$ftp = new FujiFTP();
        //$ftp->download_property();

        $this->properties_array = $this->parse_csv(__DIR__.'/cfp-temp/vebraproperties.txt', 'Property');
        $this->images_array     = $this->parse_csv(__DIR__.'/cfp-temp/files.txt', 'PropertyImage');;

        $this->post();
    }

    private function post() {
        $upd_count = 0;
        $ins_count = 0;
        $err_count = 0;
        $ign_count = 0;
    
        foreach($this->properties_array as $property) {
            $result = $property->post_wordpress();
            echo $result;
            $this->logger->log_write2($result, $property);
            switch($result) {
                case 'IGNORE':
                    $ign_count++;
                    break;
                case 'INSERT-OK':
                    $ins_count++;
                    break;
                case 'UPDATE-OK':
                    $upd_count++;
                    break;
                default:
                    echo 'ERROR';
                    var_dump($property);
                    $err_count++;
                    break;
            }
        }

        $this->logger->log_write_postresult($ins_count, $upd_count, $err_count, $ign_count);
    }

    private function parse_csv_old($filename) { // TODO must be delete
        //$file_array = file($filename);
        //$handle = fopen($filename, "r");
        $file = new SplFileObject($filename);
        $file->setFlags(SplFileObject::READ_CSV);
    
        $properties = [];
        //foreach ($file_array as $line_number => $line){
        foreach ($file as $line_number => $line){
            //$values_arr = fgetcsv($handle, 4096, ',', '"');
            //$set_values = "";
    
            if(!is_null($line[0])) {
                //$properties[] = array2assoc($line);
                $properties[] = new Property($this->array2assoc($line));
            }
        }
        return $properties;
    }

    private function parse_csv($filename, $objname) {
        $file = new SplFileObject($filename);
        $file->setFlags(SplFileObject::READ_CSV);
    
        $objects = [];
        foreach ($file as $line_number => $line){
            if(!is_null($line[0])) {
                $objects[] = new $objname($this->array2assoc($line));
            }
        }
        return $objects;
    }

    private function array2assoc(array $ary) {
        global $custom_field_name;

        $search = ["\\","/","'",     "%","<",   ">",   "&",    "\"",    "\n",    "\r"];
        $replace = ["/","/","&#039;","", "&lt;","&gt;","&amp;","&quot;","<br />","<br />"];

        foreach ($ary as $key => $val) {
            $assoc[$custom_field_name[$key]] = str_replace($search, $replace, $val);
        }
        return $assoc;
    }
}