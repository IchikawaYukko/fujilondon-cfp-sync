<?php
class Property {
    private $wordpress_post_value, $wordpress_postmeta;
    private $post_id, $title;
    const AUTHOR_ADMIN = 1;

    public function __construct(array $data) {
        // 物件タイトル＆投稿タイトル
        $this->title = $data['Prop_DisplayAddress'] .','. $data['Prop_DisplayPcode'];

        // 日付の変換
        $date = strtotime( str_replace('/', ' ', $data['Prop_LastUpload']) );
        $this->wordpress_postmeta = [
            'Prop_ID'           => $data['Prop_ID'],
            'Prop_Auto_reg'     => (int) true,  // 自動取込フラグ
            'Prop_LastUpload'   => date('Y-m-d H:i:s',$date),

            'Prop_Category'     => $data['Prop_Category'],
            'Prop_Category_str' => $this->prop_category2string($data['Prop_Category']), // 物件カテゴリー名
            'Prop_DisplayPcode' => $data['Prop_DisplayPcode'],  // Post Code
            'Prop_Price'        => $data['Prop_Price'],
            'Prop_PropType'     => $data['Prop_PropType'],
            'Prop_Bedrooms'     => $data['Prop_Bedrooms'],
            'Prop_Bathrooms'    => $data['Prop_Bathrooms'],
            'Prop_Receptions'   => $data['Prop_Receptions'],
            'Prop_Description'  => mb_convert_encoding($data['Prop_Description'], "UTF-8", "ISO-8859-1"),
            'Prop_Bullet1'      => $data['Prop_Bullet1'],
            'Prop_Bullet2'      => $data['Prop_Bullet2'],
            'Prop_Bullet3'      => $data['Prop_Bullet3'],
            'Prop_Bullet4'      => $data['Prop_Bullet4'],
            'Prop_Bullet5'      => $data['Prop_Bullet5'],
            'Prop_Bullet6'      => $data['Prop_Bullet6'],
            'Prop_Bullet7'      => $data['Prop_Bullet7'],
            'Prop_Bullet8'      => $data['Prop_Bullet8'],
            'Prop_area'         => $this->get_search_area($data['Prop_DisplayPcode'])   // 検索用エリア情報
        ];
    }

    public function get_prop_id() {
        return $this->wordpress_postmeta['Prop_ID'];
    }

    public function get_title() {
        return $this->title;
    }

    private function get_search_area($postcode) {
        $postcode_patterns = [
            ['^NW[2-9]|^NW1[0-9]',  'NW'],  // NORTH WEST (NW2, NW3, NW4, NW5, NW6, NW7, NW8, NW9, NW10)
            ['^N[0-9]+',            'N'],   // NORTH  (N1, N2, N3, N4, N5 etc)
            ['^W[3-7,9]|^W1[0,2-9]','W'],   // WEST includes (W3, W4, W5, W6, W7, W9, W10, W12, W13, W14)

            // NW1 and W1, W2, W8, W11 which are Central
            // CENTRAL includes (W1, W2, EC, WC, NW1, SW1, SW3, SW5, SW7, W8, W11)
            ['^S|^EC|^WC|^E|^NW1[^0-9]|^W[1-2,8][^0,2-9]',  'SW'],   // Why SW? not CTR?
        ];
        // OTHER includes South and East and everything else
        $default = 'SE';
        
        foreach($postcode_patterns as $pattern) {
            if(preg_match("/{$pattern[0]}/", $postcode)) {
                return $pattern[1];
            }
        }
        return $default;
    }

    private function prop_category2string($category) {
        $pattern = ['/1/','/2/','/5/'];
        $replace = ['buy', 'rent', 'tenant'];
        
        return preg_replace($pattern, $replace, $category);
    }

    public function is_posted_wordpress() {
        global $wpdb;

        if (!is_null($this->get_wordpress_post_id())) {
            return true;
        }
        return false;
    }

    public function get_wordpress_post_id() {
        global $wpdb;

        if(isset($this->post_id)) {
            return $this->post_id;  // Use cache
        }

        $results = $wpdb->get_results(
            'SELECT post_id FROM `'.$wpdb->prefix.'postmeta` WHERE meta_key = \'Prop_ID\' AND meta_value = '.$this->get_prop_id(),
             ARRAY_A
        );
        $this->post_id = $results[0]['post_id'];    // cache
        return $this->post_id;
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
            'post_name'     => $this->get_prop_id(),     // 投稿のスラッグ。
            'post_title'    => $this->get_title(),  // 投稿のタイトル。
        ];

        // 既に登録されている物件か確認
        if($this->is_posted_wordpress()) {
            return $this->update_wordpress();
        } else {
            return $this->insert_wordpress();
        }
    }
    private function insert_wordpress() {
        $this->wordpress_post_value += [
            'post_author'   => self::AUTHOR_ADMIN,  // 投稿者のID。
            'post_type'     => 'properties',        // カスタム投稿タイプ(物件情報)
            'post_status'   => 'publish'            // 公開
        ];

        $post_success = wp_insert_post($this->wordpress_post_value);
        if($post_success != null) {
            $this->post_id = $post_success;
        }

        $result = $this->check_ok_ng('INSERT', $post_success);
        return $result;
    }
    private function update_wordpress() {
        $this->wordpress_post_value['ID'] = $this->get_wordpress_post_id();
        $post_success = wp_update_post($this->wordpress_post_value);
        // TODO Error Handling
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