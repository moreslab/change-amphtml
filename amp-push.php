<?php
/**
 * Demonstrates our attempt to integrate with AMP Cache Update API.
 *
 * @package nbc
 * @see https://developers.google.com/amp/cache/update-cache
 */

/**
 * Base64url encode.
 *
 * @param string $data The data.
 *
 * @return string
 */
function base64url_encode( $data ) {
    return str_replace(
        [ '+', '/', '=' ],
        [ '-', '_', '' ],
        base64_encode( $data )
    );
}

/**
 * Creates a signature for the text you supply.
 *
 * @param string $data Data to sign.
 *
 * @return string Signature
 */
function sign_data( $data, $private_key ) {
    openssl_sign( $data, $signature, $private_key, OPENSSL_ALGO_SHA256 );
    return base64url_encode( $signature );
}

/**
 * Do a GET request and return the data and headers.
 *
 * @param string $url The URL.
 *
 * @return array
 */
function get_url( $url ) {
    $curl_handle = curl_init( $url );

    curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, true );

    $content     = curl_exec( $curl_handle );
    $status_code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

    curl_close( $curl_handle );

    return [
        'status_code' => $status_code,
        'content'     => $content,
    ];
}

/**
 * Formats a domain in the cache format.
 *
 * @see: https://developers.google.com/amp/cache/overview#amp-cache-url-format
 *
 * @param string $domain The domain.
 *
 * @return string
 */
function domain_cache_format( $domain ) {
	return str_replace(
		'.',
		'-',
		str_replace(
			'-',
			'--',
			$domain
		)
	);
}

/**
 * Updates cache for a URL.
 *
 * @param string $url The URL.
 *
 * @return array Response
 */
function update_cache( $url, $path ) {
    $private_key = openssl_pkey_get_private( 'file://private-key.pem' );
    $domain      = parse_url( $url, PHP_URL_HOST );

    $now       = \time();
    $path      = "/update-cache/c/s/${domain}${path}?amp_action=flush&amp_ts=${now}";
    $signature = sign_data( $path, $private_key );

    $url = sprintf(
        'https://%s.%s%s&amp_url_signature=%s',
        domain_cache_format( $domain ),
        'cdn.ampproject.org', // cache domain suffix.
        $path,
        $signature
    );

    echo "Sending request to: ${url}\n\n";

    return get_url( $url );
}

$res = update_cache( 'https://www.nbcnewyork.com/news/local/2nd-patient-in-nyc-tested-for-coronavirus-health-officials/2275847/', '/news/local/2nd-patient-in-nyc-tested-for-coronavirus-health-officials/2275847/' );

echo 'Response Status Code: ' . $res['status_code'] . "\n";
echo "Response Body:\n\n" . $res['content'] . "\n";
