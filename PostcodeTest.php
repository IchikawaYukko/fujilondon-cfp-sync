<?php
function _test_get_search_area_by_postcode() {
    // Test at http://rubular.com/
    $test_postcode = <<<TEST
N1
N2
N3
N4
N5
N6
N7
N8
N9
N10
NW1
NW2
NW3
NW4
NW5
NW6
NW7
NW8
NW9
NW10
NW11
W1 1NH
W2
W3
W4
W5
W6
W7
W8 6AJ
W9
W10 7KJ
W11
W12
W13
W14
EC1
EC2
WC7
WC5
SW1
SW2
SW3
SW4
SW5
SW6
SW7
S1
S2
E1
E2
TEST;

    $codes = explode(PHP_EOL, $test_postcode);
    $areas = [];

    foreach($codes as $code) {
        $areas[] = get_search_area_by_postcode($code);
    }

    foreach(array_combine($codes, $areas) as $code => $area) {
        echo $area.'-'.$code.PHP_EOL;
    }
}
//_test_get_search_area_by_postcode();