<?php
function ip4_in_range( $ip, $range ) {
    /* Based on Gist by Thorsten Ott (tott): https://gist.github.com/tott/7684443 */
	list( $range, $netmask ) = explode( '/', $range, 2 );
	$range_decimal = ip2long( $range );
	$ip_decimal = ip2long( $ip );
	$wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
	$netmask_decimal = ~ $wildcard_decimal;
	return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
}

/* Based on answer by Snifff: https://stackoverflow.com/questions/7951061/matching-ipv6-address-to-a-cidr-subnet */
function inet_to_bits($inet) {
   $splitted = str_split($inet);
   $binaryip = '';
   foreach ($splitted as $char) {
             $binaryip .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
   }
   return $binaryip;
}    

function ip6_in_range( $ip, $range ) {
    $ip = inet_pton($ip);
    $binaryip=inet_to_bits($ip);

    list($net,$maskbits)=explode('/',$range);
    $net=inet_pton($net);
    $binarynet=inet_to_bits($net);

    $ip_net_bits=substr($binaryip,0,$maskbits);
    $net_bits   =substr($binarynet,0,$maskbits);

    return ( $ip_net_bits !== $net_bits );
}

function is_google( $ip ) {
    $check2 = FALSE;
    $is_google = FALSE;
    if (gethostbyaddr($ip) == $ip) {
        $check2 = TRUE;
    } else {
        if (strpos(gethostbyaddr($ip), "googlebot") !== false) {
            $is_google = TRUE;
        } else {
            $check2 = TRUE;
        }
    }

    if ($check2 == TRUE) {
        $bot_json = 'https://developers.google.com/static/search/apis/ipranges/googlebot.json';
        $json_file = file_get_contents($bot_json); //data read from json file
        $json_data = json_decode($json_file, true);  //data decoded
        $bot_ranges = $json_data['prefixes']; //ranges retrived

        unset($bot_json);
        unset($json_file);
        unset($json_data);

        if (strpos($ip,":") != FALSE) {
            $ip6 = TRUE;
        } else {
            $ip6 = FALSE;
        }

        foreach ($bot_ranges as $range) {
            if (isset($range['ipv4Prefix'])) {
                if(ip4_in_range($ip, $range['ipv4Prefix'])==TRUE) {
                    $is_google = TRUE;
                    break;
                }
            } elseif ($ip6 == TRUE) {
                if(ip6_in_range($ip, $range['ipv6Prefix'])==TRUE) {
                    $is_google = TRUE;
                    break;
                }
            }
        }

        unset($bot_ranges);
        unset($ip6);

    }
    unset($check2);

    return $is_google;
}
?>
