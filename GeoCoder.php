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
            return $xml->result->geometry->location;
        } else {
            return FALSE;
        }
    }

    public function coord2nearest_station($location, $station_count) {
        // ジオコーディング結果から最寄駅を抽出
        $url = $this->api_base_url.'place/nearbysearch/xml?key='
        .$this->api_key
        ."&location={$location->lat},{$location->lng}"
        .'&rankby=distance&type=subway_station&language=en-GB';

        $xml = simplexml_load_file($url) or die('XML parsing error on nearest station GeoCoder');
        if ($xml->status == 'OK') {
            $stations_data = array_slice($xml->xpath('result'), 0, $station_count);

            $stations = [];
            foreach ($stations_data as $s) {
                $stations[] = get_object_vars($s->name)[0];
            }
            return $stations;
        } else {
            return $false;
        }
    }
}

/*
test code
$x = new GeoCoder('AIzaSyAUqHUsUe5m7o2orkBr18QAu0vomDfe0TU');
$latlon = $x->address2coord('Parkside, Finchley, London,N3 2PJ');
$sta = $x->coord2nearest_station($latlon, 3);
*/