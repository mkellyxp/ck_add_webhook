<?php
    /*************************************************************
    * Script that uses a CK Tag as a trigger to call a webhook   *
    **************************************************************/

    $l_sApiKey = '';
    $l_sApiSecret = '';

    $l_sTagName = '';
    $l_sWebhook = '';

    //*****  Get list of tags and find the ID of the tag name we're looking for  *****

    $l_sUrl = 'https://api.convertkit.com/v3/tags?api_key=' . $l_sApiKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, 'Content-Type: application/json'); 
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_URL, $l_sUrl );
    $l_sReturn = curl_exec($ch);
    curl_close($ch);

    $l_nTagId = 0;
    $l_asTags = json_decode( $l_sReturn );
    foreach( $l_asTags->tags as $l_xTag ) {
        if ( $l_xTag->name == $l_sTagName ) {
            $l_nTagId = $l_xTag->id;
        }
    }
    
    //*****  If we didn't find the tag, let's add it and get the ID  *****

    if ( ! $l_nTagId ) {
            
            $l_asJson = array();
            $l_asJson['api_key'] = $l_sApiKey;
            $l_asJson['tag'] = array( 'name' => $l_sTagName );

            $l_sUrl = 'https://api.convertkit.com/v3/tags';
            $l_sRequest = json_encode( $l_asJson );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) ); 
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            curl_setopt($ch, CURLOPT_URL, $l_sUrl );
            curl_setopt($ch, CURLOPT_POST, 1 );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $l_sRequest ); 
            $l_sReturn = curl_exec($ch);
            curl_close($ch);

            $l_asResults = json_decode( $l_sReturn );
            $l_nTagId = $l_asResults->id;

    }

    //*****  Add the webhook rule to fire when the tag is added  *****

    $l_asJson = array();
    $l_asJson['api_secret'] = $l_sApiSecret;
    $l_asJson['target_url'] = $l_sWebhook;
    $l_asJson['event'] = array( 'name' => 'subscriber.tag_add', 'tag_id' => $l_nTagId );

    $l_sUrl = 'https://api.convertkit.com/v3/automations/hooks';
    $l_sRequest = json_encode( $l_asJson );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) ); 
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_URL, $l_sUrl );
    curl_setopt($ch, CURLOPT_POST, 1 );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $l_sRequest ); 
    $l_sReturn = curl_exec($ch);
    curl_close($ch);

    //*****  DONE:  Check Automations / Rules in ConvertKit to confirm the webhook rule was added  *****
?>