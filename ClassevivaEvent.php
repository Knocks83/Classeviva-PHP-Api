<?php

namespace Knocks\Classeviva;

class ClassevivaEvent
{
    public function __construct(
        string $id,
        string $evtCode,
        string $evtDatetimeBegin,
        string $evtDatetimeEnd,
        string $notes,
        string $authorName,
        string $classDesc,
        $isFullDay = false,
        $subjectId = null,
        $subjectDesc = null
    ) {
        $this->id = $id;
        $this->evtCode = $evtCode;
        $this->evtDatetimeBegin = $evtDatetimeBegin;
        $this->evtDatetimeEnd = $evtDatetimeEnd;
        $this->notes = $notes;
        $this->authorName = $authorName;
        $this->classDesc = $classDesc;
        $this->fullDay = $isFullDay;
        $this->subjectID = $subjectId;
        $this->subjectDesc = $subjectDesc;
    }
}
