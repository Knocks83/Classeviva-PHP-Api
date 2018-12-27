<?php
class Classeviva {
    private $baseUrl = 'https://web.spaggiari.eu/rest/v1';

    private function Request ($dir, $data = []) {
        print ($this->baseUrl.$dir);
        curl_setopt_array ($this->curl, [
            CURLOPT_POST       => true,
            CURLOPT_URL        => $this->baseUrl.$dir,
            CURLOPT_POSTFIELDS => $data,
        ]);

        return curl_exec ($this->curl);
    }

    public function __construct ($username, $password, $ident = null) {
        $this->ident = $ident;
        $this->username = $username;
        $this->password = $password;

        // Setup cUrl to make requests
        $this->curl = curl_init();
        curl_setopt_array($this->curl, [
            CURLOPT_POST           => true,
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Z-Dev-Apikey: +zorro+',
                'User-Agent: zorro/1.0',
            ),
        ]);
    }

    public function login () {
        $json = "{
            \"ident\":\"$this->ident\",
            \"pass\":\"$this->password\",
            \"uid\":\"$this->username\"
        }";
        $response = json_decode($this->Request('/auth/login',$json));

        print_r($response);

        if(isset($response->token)) {
            if($this->ident == null) {
                $this->ident = $response->ident;
            }
            $this->firstName = $response->firstName;
            $this->lastName = $response->lastName;
            $this->token = $response->token;
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Z-Dev-Apikey: +zorro+',
                'User-Agent: zorro/1.0',
                'Z-Auth-Token: '.$this->token,
            ));

        } elseif (isset($response->error)) {
            die($response->error);
        } else die ('Unknown error');
    }

    public function agenda ($begin, $end, $events = 'all') {
        return $this->Request("/students/$this->ident/$events/$begin/$end");
    }

    public function calendar () {
        return $this->Request("/students/$this->ident/calendar/all");
    }

    public function notes () {
        return $this->Request("/students/$this->ident/notes/all");
    }
}

