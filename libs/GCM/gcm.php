<?php

class GCM {

    private $GCM_API_KEY;
    private $GCM_URL;
    private $HEADERSGCM;

    function __construct() {
        $this->GCM_API_KEY  = "AAAAZItK1Ts:APA91bG5atGEmP9EJBJm0a_IU0pojmrQBK3-peHmhLou1ovX-aYkKf-w_bTIbESzKlF8L9maeXe8lZbwTR6_bCbqthV7eNl-iCgmwRFl62UoalQ6t5dq9X3KwzugEFaKwR5VG_jS-Pmg3XUGMXRftLq839pGKLhu9g";
        $this->GCM_URL      = "https://android.googleapis.com/gcm/send";
        $this->HEADERSGCM   = array(
            "Authorization: key={$this->GCM_API_KEY}",
            "Content-Type: application/json"
        );
    }

    public function sendNotificationAndroid($fields=null){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->GCM_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->HEADERSGCM);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }
}
