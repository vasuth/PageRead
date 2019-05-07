<?php

// Read the content of a webpage
    function getPage( $url ){

	if (!function_exists('curl_init')) {	// checking curl installation
		die("cURL is not installed. Please install and try again.");
	}

	$options = array(
		CURLOPT_HEADER => 1,  		// return headers also
		CURLOPT_RETURNTRANSFER => 1,	// return content page as a string
		CURLOPT_FOLLOWLOCATION => 1,	// follow redirects
		CURLOPT_ENCODING       => "",   // handle all encodings
	);

	$ch = curl_init( $url );
        curl_setopt_array( $ch, $options );	// set up curl

    // store cookies
	$cookie_file = "cookie.txt";
	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);

    // read content
	$content = curl_exec( $ch );

    // separate header and body
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$header = substr($content, 0, $header_size);
	$body = substr($content, $header_size);

    // Downloaded page size in KB
        $size = round(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD) / 1024, 2);
	
    // close session and returns the array of [body, page size]
	curl_close($ch);

    return $pagecontent = array(0 => $body, 1 => $size);
}


$pagename = "https://www.sainsburys.co.uk/webapp/wcs/stores/servlet/CategoryDisplay?listView=true&orderBy=FAVOURITES_FIRST&parent_category_rn=12518&top_category=12518&langId=44&beginIndex=0&pageSize=20&catalogId=10137&searchTerm=&categoryId=185749&listId=&storeId=10151&promotionId=#langId=44&storeId=10151&catalogId=10137&categoryId=185749&parent_category_rn=12518&top_category=12518&pageSize=20&orderBy=FAVOURITES_FIRST&searchTerm=&beginIndex=0&hideFilters=true";

$result = getPage($pagename);

echo $result[1];
echo "<br>";

$page1 = preg_replace('/&(?![A-Za-z0-9#]{1,7};)/','&amp;', $result[0]);

$dom = new \DOMDocument();
@$dom->loadHTML($page1);

$xpath = new \DOMXpath($dom);
$productsinfo = $xpath->query('//div[@class="productInfo"]');

// collect all links in class "productinfo"
$products = [];
foreach($productsinfo as $container) {
   $links = [];
   $aitem = $container->getElementsByTagName("a");
     foreach($aitem as $item) {
       $href =  $item->getAttribute("href");
       $text = trim(preg_replace("/[\r\n]+/", " ", $item->nodeValue));
       $links[] = array('href' => $href, 'text' => $text);
     }
   array_push($products, $links);
}

$hreftext = array_column($products, '0');
//print_r($hreftext);

//$table = [];
//foreach($products as $counter) {
//   while (sizeof($counter) > 1) {
//      
//   }
//}



$unitprice = $xpath->query('//p[@class="pricePerUnit"]');

// collect all unit prices in class "pricePerUnit"
$uprices = [];
foreach($unitprice as $count) {
   $uprices[] = substr($count->nodeValue,14,4);
}

//foreach($uprices as $count) {
//   echo $count." , ".substr($count,14,4)."<br>";
//}

print_r($uprices);


?>