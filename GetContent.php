<?php

//
// Read webpage content with cURL
// Return the array of body and page size (in kb)
//
    function getPage( $url ){

	// Checking cURL installation
	if (!function_exists('curl_init')) {
	    echo "cURL is not installed. Please install and try again.";
	    return 0;
	}

	$options = array(
		CURLOPT_HEADER => 1,  		// return headers also
		CURLOPT_RETURNTRANSFER => 1,	// return content page as a string
		CURLOPT_FOLLOWLOCATION => 1,	// follow redirects
		CURLOPT_ENCODING       => "",   // handle all encodings
	);

	$ch = curl_init( $url );
        curl_setopt_array( $ch, $options );	// set up curl

	// Store cookies
	$cookie_file = "cookie.txt";
	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);

	// Read content and error check
	$content = curl_exec( $ch );

	if(curl_errno($ch)){
	    echo "Request Error: " . curl_error($ch);
	    return 0;
	}

	// Separate header and body
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$header = substr($content, 0, $header_size);
	$body = substr($content, $header_size);

	// Store page size in KB
        $size = round(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD) / 1024, 1);

	// Close session
	curl_close($ch);

	// Return the array of [body, page size]
	return $pagecontent = array('body' => $body, 'size' => $size);
    }

//
// Collect data from the webpage
// Return the JSON array of collected info
//
    function getData( $url ){

	// Read page content and size
	$readresult = getPage($url);

	// Convert & to &amp
	$pagebody = preg_replace('/&(?![A-Za-z0-9#]{1,7};)/','&amp;', $readresult['body']);

	// Initiate DOMdocument for processing the page
	$dom = new \DOMDocument();
	@$dom->loadHTML($pagebody);

	// Select div classes "products " with the individual items
	$xpath = new \DOMXpath($dom);
	$products = $xpath->query('//div[@class="product "]');

        if ($products->length == 0) {	// Check whether product fields were found
	    echo "No products found.";
	    return 0;
        };

	// Collect data of individual items

	$results = [];		// Initiate results array
	$total = 0;		// Initiate total price

	foreach($products as $elements) {	// Read single products

	    $table = [];

	    $item = $elements->getElementsByTagName("a");	// HTML references with tag "a"
	    $title = trim(preg_replace("/[\r\n\xC2\xA0]+/", " ", $item[0]->nodeValue));	// Description of the HTML tag
											// without special characters
	    $href = $item[0]->getAttribute("href");		// Web address of the individual items

	    // Collect unit prices with tag "p"
	    $pricePerUnit = $elements->getElementsByTagName("p");

            if ($pricePerUnit->length == 0) {	// Check whether fields with unitprice were found
	        echo "No unitprice found.";
	        return 0;
            };

	    $unitprice = 0.;
	    foreach($pricePerUnit as $number) {
		if ( strlen($number->nodeValue) == 25 ) {
		    $unitprice = floatval(substr($number->nodeValue,14,4));
		    break;
		}
	    }

	    // Sum of unit prices
	    $total += $unitprice;

	    // Read information from the individual link of items
	    $link = getPage($href);
	    $refpage = preg_replace('/&(?![A-Za-z0-9#]{1,7};)/','&amp;', $link['body']);
	    $ref = new \DOMDocument();		// Initiate a new DOM document
	    @$ref->loadHTML($refpage);
	    $xpath = new \DOMXpath($ref);

	    // Select div class "productText" or "itemTypeGroupContainer productText"
	    // for the detailed description of individal items
	    $producttext = $xpath->query('//div[@class="productText"]');
	    if ( $producttext->length == 0) {
		$producttext = $xpath->query('//div[@class="itemTypeGroupContainer productText"]');
	    }
	    $description = trim(preg_replace("/[\r\n\xC2\xA0]+/", " ", $producttext[0]->nodeValue));

	    // Create table of results
	    $table = array('title' => $title, 'size' => $link['size']."kb", 'unit_price' => $unitprice, 'description' => $description);

	    array_push($results, $table);
	}

    // Return JSON array
    return json_encode( array(
				"results" => $results,
				"total" => $total
			));

    }

?>
