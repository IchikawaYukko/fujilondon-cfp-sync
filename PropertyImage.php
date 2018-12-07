<?php
class PropertyImage {
    private $data;

    public function __construct(array $data) {
        $this->data = $data;
        $this->convert_filetype();
        $this->convert_url();
    }

    private function convert_url() {
        // 物件画像の参照先をs1(サムネイル)からs2(本画像)に変える
        // http:// -> https://にも変える (Mixed Content解消)
        $pattern = ['/\/image\/s1\//','/\/floorplan\/s1\//','/http:\/\//'];
        $replace = ['/image/s2/',     '/floorplan/s2/',     'https://'   ];

        $this->data['Prop_FirmID'] = preg_replace($pattern, $replace, $this->data['Prop_FirmID']);
    }

    private function convert_filetype() {
        // File_typeを文字列に変換(wordpress側の管理画面項目でセレクトボックスに数値が設定できないため)
        $convert_table = [
            2 => 'floorplan',   //間取り図
            9 => 'chart',       //EPCチャート
            'default' => 'prop' //物件画像
        ];
        
        $index = $this->data['File_type'];
        $this->data['File_type'] = $convert_table['default'];
        if (isset($convert_table[$index])) {
            $this->data['File_type'] = $convert_table[$index];
        }
    }
}