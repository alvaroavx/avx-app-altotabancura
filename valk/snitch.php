<?php
if(constant('GTag_Id') != '' && constant('Plugin_GTag') != '0' ){
    echo  '<script async src="https://www.googletagmanager.com/gtag/js?id='.constant('GTag_Id').'"></script>'
        .'<script>'
        .'window.dataLayer = window.dataLayer || [];'
        .'function gtag(){dataLayer.push(arguments);}'
        .'gtag("js", new Date());'
        .'gtag("config", "'.constant('GTag_Id').'");'
        .'</script>';
}
if(constant('FbPixel_Id') != '' && constant('Plugin_FbPixel') != '0') {
	echo  '<script>'
        . '!function(f,b,e,v,n,t,s)'
        . '{if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};'
        . 'if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version="2.0";'
        . 'n.queue=[];t=b.createElement(e);t.async=!0;'
        . 't.src=v;s=b.getElementsByTagName(e)[0];'
        . 's.parentNode.insertBefore(t,s)}(window, document,"script",'
        . '"https://connect.facebook.net/en_US/fbevents.js");'
        . 'fbq("init", "'.constant('FbPixel_Id').'");'
        . 'fbq("track", "PageView");'
        . '</script>'
        . '<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id='.constant('FbPixel_Id').'&ev=PageView&noscript=1" /></noscript>';
}

