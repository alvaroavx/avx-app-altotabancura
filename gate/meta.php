<?php
/**NJONG 25.11.18: META**/
{
	$Meta['Title']          = $Var['Title'];
	$Meta['Description']    = $Var['Description'];
	$Meta['Keyword']        = $Var['Keyword'];
    $Meta['Favicon']        = $Var['Root']['Base'] .constant('Root_Res'). 'favicon/favicon.gif';
	/*$Meta['Favicon']        = $Var['Root']['Base'] .$Constants['Root']['Res']. 'favicon/favicon.png';*/

	$Meta['List'] = [];

	$Meta['List'][] = ['name' => 'title', 'content' => $Var['Title']];
	$Meta['List'][] = ['name' => 'og:title', 'content' => $Var['Title']];
	$Meta['List'][] = ['name' => 'og:site_name', 'content' => $Var['Title']];

	$Meta['List'][] = ['name' => 'description', 'content' => $Var['Description']];
	$Meta['List'][] = ['name' => 'og:description', 'content' => $Var['Description']];

	$Meta['List'][] = ['name' => 'og:image', 'content' => $Meta['Favicon'] ];

	$Meta['List'][] = ['name' => 'keyword', 'content' => $Var['Keyword'] ];
	$Meta['List'][] = ['name' => 'og:url', 'content' => $Var['Root']['Base'] ];

	$Meta['List'][] = ['charset' => 'UTF-8', ];
	$Meta['List'][] = ['http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge,chrome=1'];
	$Meta['List'][] = ['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0'];
	$Meta['List'][] = ['name' => 'apple-mobile-web-app-capable', 'content' => 'yes'];
	$Meta['List'][] = ['name' => 'apple-touch-fullscreen', 'content' => 'yes'];
	$Meta['List'][] = ['name' => 'apple-mobile-web-app-status-bar-style', 'content' => 'default'];

	$Meta['List'][] = ['name' => 'og:image:type', 'content' => 'image/jpeg'];
	$Meta['List'][] = ['name' => 'og:image:width', 'content' => '300'];
	$Meta['List'][] = ['name' => 'og:image:height', 'content' => '300'];

}