<?php
class GeoCoder {
    private $api_base_url, $api_key;

    public function __construct($gmap_api_key) {
        $this->api_base_url = 'https://maps.googleapis.com/maps/api/';
        $this->api_key = $gmap_api_key;
    }

    public function address2coord($address) {
        $street_address = urlencode(trim($address));

        $url = $this->api_base_url
        ."geocode/xml?key={$this->api_key}&address={$street_address}";

        $xml = simplexml_load_file($url) or die('XML parsing error on Street address [GeoCoder]');
        if ($xml->status == 'OK') {
            return [
                'lat' => (string) $xml->result->geometry->location->lat,
                'lng' => (string) $xml->result->geometry->location->lng
            ];
        } else {
            return FALSE;
        }
    }

    public function coord2nearest_station($lat, $long, $station_count) {
        // ジオコーディング結果から最寄駅を抽出
        $url = $this->api_base_url.'place/nearbysearch/xml?key='
        .$this->api_key
        ."&location={$lat},{$long}"
        .'&rankby=distance&type=subway_station&language=en-GB';

        $xml = simplexml_load_file($url) or die('XML parsing error on nearest station GeoCoder');
        switch ($xml->status) {
            case 'OK':
                $stations_data = array_slice($xml->xpath('result'), 0, $station_count);

                $stations = [];
                foreach ($stations_data as $s) {
                    $station_name = (array) $s->name;
                    // if station name includes 'Station' or 'Underground Station' at the end, remove it.
                    $replace_pattern = [
                        '/ Underground Station$/',
                        '/ Station$/',
                    ];
                    $stations[] = preg_replace( $replace_pattern, '', $station_name[0]);
                }
                return $stations;
            case 'ZERO_RESULTS':
                return [];
            default:
                return false;
        }
    }
}