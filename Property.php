<?php
class Property {
    private $data, $wordpress_post_value;
    private $post_id;
    const AUTHOR_ADMIN = 1;

    public function __construct(array $data) {
        $this->data = $data;

        // 検索用エリア情報を付与
        $this->set_search_area();
        // 物件カテゴリー名
        $this->set_prop_category_string();
        // 自動取込フラグ
        $this->data['Prop_Auto_reg'] = 1;

        // 日付の変換
        $date = strtotime( str_replace('/', ' ', $this->data['Prop_LastUpload']) );
        $this->data['Prop_LastUpload'] = date('Y-m-d H:i:s',$date);

        $this->data['Prop_Description'] = mb_convert_encoding($this->data['Prop_Description'], "UTF-8", "ISO-8859-1");
    }

    public function get_prop_id() {
        return $this->data['Prop_ID'];
    }

    public function get_title() {
        return $this->data['Prop_DisplayAddress'] .','. $this->data['Prop_DisplayPcode'];
    }

    private function set_search_area() {
        $postcode = $this->data['Prop_DisplayPcode'];

        $postcode_patterns = [
            ['^NW[2-9]|^NW1[0-9]',  'NW'],
            ['^N[0-9]+',            'N'],
            ['^W[3-7,9]|^W1[0,2-9]','W'],
            ['^S|^EC|^WC|^E|^NW1[^0-9]|^W[1-2,8][^0,2-9]',         'SW'],
        ];
        
        foreach($postcode_patterns as $pattern) {
            if(preg_match("/{$pattern[0]}/", $postcode)) {
                 $this->data['Prop_area'] = $pattern[1];
                 return;
            }
        }
    }

    private function set_prop_category_string() {
        $pattern = ['/1/','/2/','/5/'];
        $replace = ['buy', 'rent', 'tenant'];
        
        $this->data['Prop_Category_str'] = preg_replace($pattern, $replace, $this->data['Prop_Category']);
    }

    public function is_posted_wordpress() {
        global $wpdb;

        /*$results = $wpdb->get_results(
            'SELECT post_id FROM `'.$wpdb->prefix.'postmeta` WHERE meta_key = \'Prop_ID\' AND meta_value = '.$this->get_prop_id() , ARRAY_A
        );*/
        if (!is_null($this->get_wordpress_post_id())) {
            return true;
        }
        return false;
    }

    public function get_wordpress_post_id() {
        global $wpdb;

        if(isset($this->prop_id)) {
            return $this->prop_id;  // Use cache
        }

        $results = $wpdb->get_results(
            'SELECT post_id FROM `'.$wpdb->prefix.'postmeta` WHERE meta_key = \'Prop_ID\' AND meta_value = '.$this->get_prop_id(),
             ARRAY_A
        );
        $this->prop_id = $results[0]['post_id'];    // cache
        return $this->prop_id;
    }

    public function post_wordpress() {
        //除外フラグ立て
        if($this->is_ignored()) {
            return 'IGNORE';
        }

        $post_result = $this->upsert_wordpress();
        $this->update_wordpress_custom_fields();
        return $post_result;
    }

    private function upsert_wordpress() {
        // 物件情報
        $this->wordpress_post_value = [
            'post_author'   => self::AUTHOR_ADMIN,  // 投稿者のID。
            'post_type'     => 'properties',        // 物件情報
            'post_name'     => $this->get_prop_id(),     // 投稿のスラッグ。
            'post_title'    => $this->get_title(),  // 投稿のタイトル。
            'post_status'   => 'publish',           // 公開
        ];

        // 既に登録されている物件か確認
        //var_dump($this->is_posted_wordpress());
        if($this->is_posted_wordpress()) {
            return $this->update_wordpress();
        } else {
            return $this->insert_wordpress();
        }
    }
    private function insert_wordpress() {
        $post_success = wp_insert_post($this->wordpress_post_value);
        if($post_success != null) {
            $this->wordpress_post_value['ID'] = $post_success;
        }

        $result = $this->check_ok_ng('INSERT', $post_success);
        return $result;
    }
    private function update_wordpress() {
        $this->wordpress_post_value['ID'] = $this->get_wordpress_post_id();
        $post_success = wp_update_post($this->wordpress_post_value);
        //var_dump( is_wp_error( $post_id ));
        $result = $this->check_ok_ng('UPDATE', $post_success);

        return $result;
    }

    public function update_wordpress_custom_fields() {
        update_post_meta($this->get_wordpress_post_id(), 'Prop_ID', $this->get_prop_id());
        /*
        // カスタムフィールドを登録(既に同じID、同じkeyのレコードがあれば更新される)
        foreach ($property as $key => $value) {
            update_post_meta($insert_id, $key, $value);
        }

        // ジオコーディング結果を登録
        update_post_meta($insert_id, 'lat', (string)$lat);
        update_post_meta($insert_id, 'lng', (string)$lng);
        add_post_meta($insert_id, 'station', (string)$station->name);
        */
    }

    public function delete_wordpress_custom_fields() {
        //delete_post_meta($insert_id,'station');
    }

    private function check_ok_ng($update_type, $result) {
        if($result != null) {
            $ok_ng = 'OK';
        } else {
            $ok_ng = 'FAIL';
        }

        return "$update_type-$ok_ng";
    }

    public function is_ignored() {
        $set = new FujiSettings();
    
        if(FALSE !== array_search($this->get_prop_id(), $set->settings['ignore_prop_id'])) {
            return true;
        } else {
            return false;
        }
    }
}