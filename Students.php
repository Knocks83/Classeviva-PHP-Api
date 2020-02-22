<?php

namespace Knocks\Classeviva\Students;

use Exception;
use Knocks\Classeviva\ClassevivaEvent;

class Classeviva
{
    private $baseUrl = 'https://web.spaggiari.eu/rest/v1';

    private function Request($dir, $data = [])
    {
        if ($data == []) {
            curl_setopt($this->curl, CURLOPT_POST, false);
        } else {
            curl_setopt_array($this->curl, [
                CURLOPT_POST       => true,
                CURLOPT_POSTFIELDS => $data,
            ]);
        }
        curl_setopt_array($this->curl, [
            CURLOPT_URL        => $this->baseUrl . $dir,
        ]);

        return curl_exec($this->curl);
    }

    public function __construct($username, $password, $identity = null)
    {
        $this->ident = $identity;
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

        $this->login();
    }

    public function login()
    {
        $json = "{
            \"ident\":\"$this->ident\",
            \"pass\":\"$this->password\",
            \"uid\":\"$this->username\"
        }";
        $response = json_decode($this->Request('/auth/login', $json));

        if (!property_exists($response, 'error') && isset($response->token)) {
            $this->ident = $response->ident;
            $this->firstName = $response->firstName;
            $this->lastName = $response->lastName;
            $this->token = $response->token;
            $this->id = filter_var($response->ident, FILTER_SANITIZE_NUMBER_INT);
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Z-Dev-Apikey: +zorro+',
                'User-Agent: zorro/1.0',
                'Z-Auth-Token: ' . $this->token,
            ));
        } elseif (isset($response->error)) {
            throw new Exception($response->error . PHP_EOL, 2);
        } else throw new Exception("Unknown error", 2);
    }

    // Start Auth section
    public function avatar()
    {
        return $this->Request("/auth/avatar");
    }

    public function sid()
    {
        return $this->Request("/auth/_zsid");
    }

    public function status()
    {
        return $this->Request('/auth/status');
    }

    public function ticket()
    {
        return $this->Request('/auth/ticket');
    }
    // End Auth section

    public function options()
    {
        return $this->Request("/students/$this->id/_options");
    }


    public function absences($begin = null, $end = null)
    {
        if ($begin != null) {
            if ($end != null) {
                return $this->Request("/students/$this->id/absences/details/$begin/$end");
            } else {
                return $this->Request("/students/$this->id/absences/details/$begin");
            }
        } else {
            return $this->Request("/students/$this->id/absences/details");
        }
    }

    public function agenda($begin, $end, $events = 'all')
    {
        return $this->Request("/students/$this->id/agenda/$events/$begin/$end");
    }

    public function calendar()
    {
        return $this->Request("/students/$this->id/calendar/all");
    }

    public function card()
    {
        return $this->Request("/students/$this->id/card");
    }

    public function cards()
    {
        return $this->Request("/students/$this->id/cards");
    }

    public function didactics($id = null)
    {
        if ($id != null) {
            return $this->Request("/students/$this->id/didactics/item/$id");
        } else {
            return $this->Request("/students/$this->id/didactics");
        }
    }

    public function documents($hash = null, bool $check = false)
    {
        if ($hash != null) {
            if ($check) {
                return $this->Request("/students/$this->id/documents/check/$hash");
            } else {
                return $this->Request("/students/$this->id/documents/read/$hash");
            }
        } else {
            return $this->Request("/students/$this->id/documents");
        }
    }

    public function grades()
    {
        return $this->Request("/students/$this->id/grades2");
    }

    public function lessons($start = null, $end = null)
    {
        if ($start != null) {
            if ($end != null) {
                return $this->Request("/students/$this->id/lessons/$start/$end");
            } else {
                return $this->Request("/students/$this->id/lessons/$start");
            }
        } else {
            return $this->Request("/students/$this->id/lessons/today");
        }
    }

    public function notes($type = null, $note = null)
    {
        if ($type = !null && $note != null) {
            return $this->Request("/students/$this->id/notes/$type/read/$note");
        } else {
            return $this->Request("/students/$this->id/notes/all");
        }
    }

    public function noticeBoard(bool $mode = null, $fileNum = null, $eventCode = null, $pubID = null)
    {
        // If mode == 1 read, else attach
        if ($mode != null) {
            if ($mode) {
                return $this->Request("/students/$this->id/noticeboard/read/$eventCode/$pubID/101");
            } elseif ($fileNum != null) {
                return $this->Request("/students/$this->id/noticeboard/attach/$eventCode/$pubID/$fileNum");
            }
        }
        return $this->Request("/students/$this->id/noticeboard");
    }

    public function periods()
    {
        return $this->Request("/students/$this->id/periods");
    }

    public function subjects()
    {
        return $this->Request("/students/$this->id/subjects");
    }

    public function schoolbooks()
    {
        return $this->Request("/students/$this->id/schoolbooks");
    }

    // Start non-requests methods
    public static function convertClassevivaAgenda(string $classevivaAgenda)
    {
        $classevivaAgenda = json_decode($classevivaAgenda);
        $classevivaEvents = array();

        foreach ($classevivaAgenda->agenda as $event) {
            $convertedEvent = new ClassevivaEvent(
                $event->evtId,
                $event->evtCode,
                $event->evtDatetimeBegin,
                $event->evtDatetimeEnd,
                $event->notes,
                $event->authorName,
                $event->classDesc,
                $event->subjectId,
                $event->subjectDesc
            );

            array_push($classevivaEvents, $convertedEvent);
        }

        return $classevivaEvents;
    }
}
