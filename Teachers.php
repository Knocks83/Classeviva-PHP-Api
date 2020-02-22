<?php

namespace Knocks\Classeviva\Teachers;

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

    // Start Auth section
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
        return $this->Request("/teachers/$this->id/_options");
    }

    public function lessonTypes($scte = false)
    {
        if ($scte) {
            return $this->Request("/teachers/$this->id/_st/sctelessontypes");
        } else {
            return $this->Request("/teachers/$this->id/_st/lessontypes");
        }
    }

    public function agendaMine($start, $end)
    {
        return $this->Request("/teachers/$this->id/agenda/mine/$start/$end");
    }

    public function agendaAll($classType, $classId, $start, $end)
    {
        return $this->Request("/teachers/$this->id/agenda/all/$classType/$classId/$start/$end");
    }

    public function card()
    {
        return $this->Request("/teachers/$this->id/card");
    }

    public function cards()
    {
        return $this->Request("/teachers/$this->id/cards");
    }

    public function classes()
    {
        /*
        rest/v1/teachers/{teacherId}/classes/all/students1
        rest/v1/teachers/{teacherId}/classes/extracurricola/students1
        rest/v1/teachers/{teacherId}/classes/mine/students1
        */
    }

    public function didactics()
    {
        #TODO: didactics
        /*
        rest/v1/teachers/{teacherId}/didactics/addfile/{folderId}
        rest/v1/teachers/{teacherId}/didactics/addfolder
        rest/v1/teachers/{teacherId}/didactics/additem/{folderId}
        rest/v1/teachers/{teacherId}/didactics/any
        rest/v1/teachers/{teacherId}/didactics/attachfolder/{folderId}
        rest/v1/teachers/{teacherId}/didactics/attachitem/{contentId}
        rest/v1/teachers/{teacherId}/didactics/deletefolder/{folderId}
        rest/v1/teachers/{teacherId}/didactics/deleteitem/{itemId}
        rest/v1/teachers/{teacherId}/didactics/item/{contentId}
        rest/v1/teachers/{teacherId}/didactics/sharefolder/{folderId}
        rest/v1/teachers/{teacherId}/didactics/shareitem/{itemId}
        rest/v1/teachers/{teacherId}/didactics/unsharefolder/{folderId}
        rest/v1/teachers/{teacherId}/didactics/unshareitem/{itemId}
        */
    }

    public function evaluations()
    {
        #TODO: evaluations
        /*
        rest/v1/teachers/{teacherId}/evaluations/components/{classType}/{classId}/{subjectId}
        rest/v1/teachers/{teacherId}/evaluations/grades2
        rest/v1/teachers/{teacherId}/evaluations/grades2/mine
        rest/v1/teachers/{teacherId}/evaluations/grades2/{classType}/{classId}/{subjectId}
        rest/v1/teachers/{teacherId}/evaluations/skills/{classType}/{classId}/{subjectId}
        */
    }

    public function myLessons($start, $end)
    {
        return $this->Request("/teachers/$this->id/lessons/students/$start/$end");
    }

    public function studentsLessons($classType, $classId, $start = null, $end = null)
    {
        if ($start != null) {
            if ($end != null) {
                return $this->Request("/teachers/$this->id/lessons/students/$classType/$classId/$start/$end");
            } else {
                return $this->Request("/teachers/$this->id/lessons/students/$classType/$classId/$start");
            }
        } else {
            return $this->Request("/teachers/$this->id/lessons/students/$classType/$classId");
        }
    }

    public function myNotes($begin, $end)
    {
        return $this->Request("/teachers/$this->id/notes/mine/$begin/$end");
    }

    public function notes($classType, $classId, $start, $end = null)
    {
        if ($end != null) {
            return $this->Request("/teachers/$this->id/notes/range/$classType/$classId/$start/$end");
        } else {
            return $this->Request("/teachers/$this->id/notes/day/$classType/$classId/$start");
        }
    }

    public function noticeBoard(bool $mode = null, $eventCode = null, $pubID = null, $fileNum = null)
    {
        // If mode == 1 read, else attach
        if ($mode != null) {
            if ($mode) {
                return $this->Request("/teachers/$this->id/noticeboard/read/$eventCode/$pubID/101");
            } elseif ($fileNum != null) {
                return $this->Request("/teachers/$this->id/noticeboard/attach/$eventCode/$pubID/$fileNum");
            }
        } else {
            return $this->Request("/teachers/$this->id/noticeboard");
        }
    }

    public function pendingEvents($classType, $classId)
    {
        return $this->Request("/teachers/$this->id/students/pendingevents/$classType/$classId");
    }

    public function studentsStatus($classType, $classId, $day)
    {
        return $this->Request("/teachers/$this->id/students/status/$classType/$classId/$day");
    }

    public function subjects($classType, $classId)
    {
        return $this->Request("/teachers/$this->id/subjects/list/$classType/$classId");
    }

    public function teachers()
    {
        return $this->Request("/teachers/$this->id/teachers");
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
