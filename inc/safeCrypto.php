<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
 * Generate a random key
 * 
 * Generate a random key based on the specified hashing algorithm and given length.
 * The algorithm defaults to "sha256" and the length defaults to 256.
 * See PHP's hash_algos() function for a list of supported hashing algorithms.
 * 
 * @param	string	$algo	The hashing algorithm to use
 * @param	int	$length		Length of desired string of bytes to create the hash
 * @return	string			Random hash
 * @access	public
 */
function WooEnc_generateRandomKey($algo = 'sha256', $length = 256)
{
	$key = '';
	
	// Check if the openssl_random_pseudo_bytes() function exists
	if ( function_exists('openssl_random_pseudo_bytes') )
	{ // It does exist so let's use it to generate a string of random bytes to hash
		
		$data = openssl_random_pseudo_bytes($length, $cstrong) . mt_rand() . microtime();
		$key = hash($algo, $data);
	} else
	{ // It doesn't exist, but have no fear... We will attempt to read from /dev/urandom instead
	
		// Even if the file_get_contents() function returns FALSE, a hash will still be generated based on
		// a random number from mt_rand() and the microtime()
		$data = mt_rand() . microtime() . file_get_contents('/dev/urandom', $length) . mt_rand() . microtime();
		$key = hash($algo, $data);
	}
	
	return $key;
}

/**
 * Encrypt a message
 * 
 * @param string $data - message to encrypt
 * @param string $key - encryption key
 * @return string
 */
function WooEnc_safeEncrypt( $data, $key ) {
    $iv = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'aes-256-cbc' ) );
    $encrypted = openssl_encrypt( $data, 'aes-256-cbc', $key, 0, $iv );
    return base64_encode( $encrypted . '::' . $iv );
}

/**
 * Decrypt a message
 * 
 * @param string $data - message encrypted with WooEnc_safeEncrypt()
 * @param string $key - encryption key
 * @return string
 */
function WooEnc_safeDecrypt( $data, $key ) {
    list( $encrypted_data, $iv ) = explode( '::', base64_decode( $data ), 2 );
    return openssl_decrypt( $encrypted_data, 'aes-256-cbc', $key, 0, $iv );
}