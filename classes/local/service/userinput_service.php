<?php

namespace mod_competencygrid\local\service;

use mod_competencygrid\local\entity\userinput;
use mod_competencygrid\local\repository\userinput_repository;

class userinput_service {
    private userinput_repository $userinput_repository;
    public function __construct() {
        $this->userinput_repository = new userinput_repository();
    }
    
    public function save_userinput_array(array $userinput, int $userid, int $courseid, int $gridid) {
        global $DB;

        $skill_service = new skill_service();
        $subskill_service = new subskill_service();

        $transaction = $DB->start_delegated_transaction();

        foreach ($userinput as $subskillid => $levelniveau) {
            // Validate and sanitize inputs.
            $subskillid = (int) $subskillid;
            $levelniveau = (int) $levelniveau;
            // Get the skillid from the subskill.
            $subskill = $subskill_service->get_subskill($subskillid);
            $skillid = $skill_service->get_skill($subskill)->id;
            // Persist data trough repository.
            $userinput_object = new userinput();
            $userinput_object->userid = $userid;
            $userinput_object->courseid = $courseid;
            $userinput_object->gridid = $gridid;
            $userinput_object->skillid = $skillid;
            $userinput_object->subskillid = $subskillid;
            $userinput_object->levelniveau = $levelniveau;
            $this->save_userinput($userinput_object);
        }
        $transaction->allow_commit();
    }

    public function save_userinput(userinput $userinput) {
        $this->userinput_repository->write_userinput($userinput);
    }
}