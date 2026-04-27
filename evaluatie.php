<?php

/**
 * =======================================================================================
 * FUNCTIE-INDEX: evaluatie.php
 * =======================================================================================
 *   evaluatie_civicrm_config()     Implements hook_civicrm_config().
 *   evaluatie_civicrm_install()    Implements hook_civicrm_install().
 *   evaluatie_civicrm_enable()     Implements hook_civicrm_enable().
 *   evaluatie_civicrm_customPre()
 * =======================================================================================
 */

require_once 'evaluatie.civix.php';

use CRM_Evaluatie_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function evaluatie_civicrm_config(&$config): void {
  _evaluatie_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function evaluatie_civicrm_install(): void {
  _evaluatie_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function evaluatie_civicrm_enable(): void {
  _evaluatie_civix_civicrm_enable();
}

function evaluatie_civicrm_customPre(string $op, int $groupID, int $entityID, array &$params): void {

    // 1. RECURSIE BEVEILIGING
    static $processing_evaluatie_pre = [];
    if (isset($processing_evaluatie_pre[$entityID])) return; // Beveiliger zegt: "Je bent al binnen!"

    $extdebug   = 0;          // 1 = basic // 2 = verbose // 3 = params / 4 = results
    $apidebug   = FALSE;
    $exteval    = 1;

    if ($op != 'create' && $op != 'edit') { //    did we just create or edit a custom object?
//      wachthond($extdebug,1, "EXIT: op != create OR op != edit");
        return;                             //    if not, get out of here
    }

    watchdog('civicrm_timing', base_microtimer("START EVALUATIE [PRE] voor entityID: $entityID"), NULL, WATCHDOG_DEBUG);

    $profileparteval        = array(146);
    $profilepartevaldeel    = array(209);
    $profilepartevalleid    = array(168);        
    $profilepartevalintern  = array(230);

    $profilepartevalall     = array_merge($profileparteval, $profilepartevaldeel, $profilepartevalleid);

    if (in_array($groupID, $profilepartevalall)) { // CV & PART

        wachthond($extdebug,1, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] 1.X",                                       "[START]");
        wachthond($extdebug,1, "########################################################################");

        $part_id            = $entityID;
        $ditevent_part_id   = $entityID;

    } else {
        wachthond($extdebug,4, "civicrm_custom",            $groupID);
        return; // if not, get out of here
    }    

    if ($op != 'create' && $op != 'edit') { //    did we just create or edit a custom object?
        wachthond($extdebug,3, "########################################################################");
        wachthond($extdebug,3, "### EVALUATIE [PRE] EXIT: op != create OR op != edit",        "(op: $op)");
        wachthond($extdebug,3, "########################################################################");
        return; //  if not, get out of here
    }

    $today_datetime             = date("Y-m-d H:i:s");
    wachthond($extdebug,4, "params",        $params);

    ##########################################################################################
    # CHECK IF DEZE REGISTRATIE VAN DIT JAAR IS
    ##########################################################################################

    $array_partditevent         = base_pid2part($part_id);
    wachthond($extdebug,4, "array_partditevent",       $array_partditevent);

    $contact_id                 = $array_partditevent['contact_id']             ?? NULL;
    $displayname                = $array_partditevent['displayname']            ?? NULL;
    $ditevent_fiscalyear_start  = $array_partditevent['event_fiscalyear_start'] ?? NULL;
    $ditevent_fiscalyear_einde  = $array_partditevent['event_fiscalyear_einde'] ?? NULL;

    $ditevent_part_eventid      = $array_partditevent['event_id']               ?? NULL;
    $ditevent_part_kampnaam     = $array_partditevent['part_kampnaam']          ?? NULL;
    $ditevent_part_kamptype_nr  = $array_partditevent['part_kamptype_id']       ?? NULL;
    $ditevent_part_kampkort     = $array_partditevent['part_kampkort']          ?? NULL;
    $ditevent_part_kampkort_low = $array_partditevent['part_kampkort_low']      ?? NULL;
    $ditevent_part_kampkort_cap = $array_partditevent['part_kampkort_cap']      ?? NULL;
    $ditevent_part_kampstart    = $array_partditevent['part_kampstart']         ?? NULL;
    $ditevent_part_kampeinde    = $array_partditevent['part_kampeinde']         ?? NULL;
    $ditevent_part_kampjaar     = $array_partditevent['part_kampjaar']          ?? NULL;
    $ditevent_part_functie      = $array_partditevent['part_functie']           ?? NULL;
    $ditevent_part_rol          = $array_partditevent['part_rol']               ?? NULL;

    wachthond($extdebug,3, 'contact_id',                    $contact_id);
    wachthond($extdebug,3, 'displayname',                   $displayname);
    wachthond($extdebug,3, 'ditevent_fiscalyear_start',     $ditevent_fiscalyear_start);
    wachthond($extdebug,3, 'ditevent_fiscalyear_einde',     $ditevent_fiscalyear_einde);

    wachthond($extdebug,3, 'ditevent_part_eventid',         $ditevent_part_eventid);
    wachthond($extdebug,3, 'ditevent_part_kampnaam',        $ditevent_part_kampnaam);
    wachthond($extdebug,3, 'ditevent_part_kampkort',        $ditevent_part_kampkort);
    wachthond($extdebug,3, 'ditevent_part_kamptype_nr',     $ditevent_part_kamptype_nr);
    wachthond($extdebug,3, 'ditevent_part_kampstart',       $ditevent_part_kampstart);
    wachthond($extdebug,3, 'ditevent_part_kampkort',        $ditevent_part_kampkort);
    wachthond($extdebug,3, 'ditevent_part_functie',         $ditevent_part_functie);
    wachthond($extdebug,3, 'ditevent_part_rol',             $ditevent_part_rol);

    ##########################################################################################
    # CHECK OF DEELNAME PERSOON AAN DIT EVENT OOK IN DIT JAAR IS
    ##########################################################################################

    $ditjaarpart         = 0;
    $ditjaareventdeelyes = 0;
    $ditjaareventleidyes = 0;

    if (infiscalyear($ditevent_part_kampstart, $today_datetime) == 1) {
        $ditjaarpart = 1;
    }

    if ($exteval == 1 AND in_array($groupID, $profilepartevalall) AND $ditjaarpart == 1) {

        $array_allpart_ditjaar = base_find_allpart($contact_id, $today_datetime);
        wachthond($extdebug,4, 'array_allpart_ditjaar',         $array_allpart_ditjaar);

        $ditjaar_pos_leid_count         = $array_allpart_ditjaar['result_allpart_pos_leid_count'];
        $ditjaar_pos_leid_part_id       = $array_allpart_ditjaar['result_allpart_pos_leid_part_id'];
        $ditjaar_pos_leid_event_id      = $array_allpart_ditjaar['result_allpart_pos_leid_event_id'];
        $ditjaar_pos_leid_status_id     = $array_allpart_ditjaar['result_allpart_pos_leid_status_id'];
        $ditjaar_pos_leid_kampfunctie   = $array_allpart_ditjaar['result_allpart_pos_leid_kampfunctie'];
        $ditjaar_pos_leid_kampkort      = $array_allpart_ditjaar['result_allpart_pos_leid_kampkort'];

        wachthond($extdebug,3, 'ditjaar_pos_leid_count',        $ditjaar_pos_leid_count);
        wachthond($extdebug,3, 'ditjaar_pos_leid_part_id',      $ditjaar_pos_leid_part_id);
        wachthond($extdebug,3, 'ditjaar_pos_leid_event_id',     $ditjaar_pos_leid_event_id);
        wachthond($extdebug,3, 'ditjaar_pos_leid_status_id',    $ditjaar_pos_leid_status_id);
        wachthond($extdebug,2, 'ditjaar_pos_leid_kampfunctie',  $ditjaar_pos_leid_kampfunctie);
        wachthond($extdebug,2, 'ditjaar_pos_leid_kampkort',     $ditjaar_pos_leid_kampkort);

        $ditjaar_pos_deel_count         = $array_allpart_ditjaar['result_allpart_pos_deel_count'];
        $ditjaar_pos_deel_part_id       = $array_allpart_ditjaar['result_allpart_pos_deel_part_id'];
        $ditjaar_pos_deel_event_id      = $array_allpart_ditjaar['result_allpart_pos_deel_event_id'];
        $ditjaar_pos_deel_status_id     = $array_allpart_ditjaar['result_allpart_pos_deel_status_id'];
        $ditjaar_pos_deel_kampfunctie   = $array_allpart_ditjaar['result_allpart_pos_deel_kampfunctie'];
        $ditjaar_pos_deel_kampkort      = $array_allpart_ditjaar['result_allpart_pos_deel_kampkort'];

        wachthond($extdebug,3, 'ditjaar_pos_deel_count',        $ditjaar_pos_deel_count);
        wachthond($extdebug,3, 'ditjaar_pos_deel_part_id',      $ditjaar_pos_deel_part_id);
        wachthond($extdebug,3, 'ditjaar_pos_deel_event_id',     $ditjaar_pos_deel_event_id);
        wachthond($extdebug,3, 'ditjaar_pos_deel_status_id',    $ditjaar_pos_deel_status_id);
        wachthond($extdebug,2, 'ditjaar_pos_deel_kampfunctie',  $ditjaar_pos_deel_kampfunctie);
        wachthond($extdebug,2, 'ditjaar_pos_deel_kampkort',     $ditjaar_pos_deel_kampkort);

        if ($ditjaar_pos_deel_count == 1 AND $entityID == $ditjaar_pos_deel_part_id) {
            $ditjaareventdeelyes = 1;
        }
        if ($ditjaar_pos_leid_count == 1 AND $entityID == $ditjaar_pos_leid_part_id) {
            $ditjaareventleidyes = 1;
        }

        wachthond($extdebug,3, 'ditjaareventdeelyes',           $ditjaareventdeelyes);
        wachthond($extdebug,3, 'ditjaareventleidyes',           $ditjaareventleidyes);

    }    

    #########################################################################
    ### EVALUATIE PART EVAL [PRE]                                     [START]
    #########################################################################

    if (in_array($groupID, $profileparteval)) {

        $arraysize = sizeof($params);
        wachthond($extdebug,3, 'arraysize',             $arraysize);

        wachthond($extdebug,1, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] PART EVAL 1.1 - RETRIEVE VALUES FROM PARAMS", "[$displayname - groupID: $groupID]");
        wachthond($extdebug,1, "########################################################################");

        wachthond($extdebug,4, "entityid",    $entityID);
        wachthond($extdebug,4, "params",      $params);

        foreach($params as $i=>$item) {

            if ( !isset($indexed[$i][$item['id']]) ) {
                $indexed[$i]['key']         = $i;
                $indexed[$i]['entity_id']   = $item['entity_id']    ?? NULL;
                $indexed[$i]['column_name'] = $item['column_name']  ?? NULL;
                $indexed[$i]['table_name']  = $item['table_name']   ?? NULL;
                $indexed[$i]['value']       = $item['value']        ?? NULL;
            }

            if (!isset($key_eval_datum[$i])     AND $item['column_name'] == "datum_evaluatie_1076")     { $all_eval_datum[]     = $i; }

            if (!isset($key_eval_terugblik[$i]) AND $item['column_name'] == "terugblik_score_2149")     { $all_eval_terugblik[] = $i; }
            if (!isset($key_eval_thema[$i])     AND $item['column_name'] == "kampthema_score_2151")     { $all_eval_thema[]     = $i; }
            if (!isset($key_eval_inhoud[$i])    AND $item['column_name'] == "inhoud_score_2153")        { $all_eval_inhoud[]    = $i; }
            if (!isset($key_eval_actief[$i])    AND $item['column_name'] == "actief_score_2155")        { $all_eval_actief[]    = $i; }

            if (!isset($key_eval_vrijetijd[$i]) AND $item['column_name'] == "vrijetijd_score_2157")     { $all_eval_vrijetijd[] = $i; }
            if (!isset($key_eval_corvee[$i])    AND $item['column_name'] == "corvee_score_2167")        { $all_eval_corvee[]    = $i; }

            if (!isset($key_eval_slapen[$i])    AND $item['column_name'] == "slapen_score_2165")        { $all_eval_slapen[]    = $i; }
            if (!isset($key_eval_eten[$i])      AND $item['column_name'] == "etendrinken_score_2161")   { $all_eval_eten[]      = $i; }
            if (!isset($key_eval_brengen[$i])   AND $item['column_name'] == "brengenhalen_score_2163")  { $all_eval_brengen[]   = $i; }
            if (!isset($key_eval_kampinfo[$i])  AND $item['column_name'] == "kampinfo_score_2158")      { $all_eval_kampinfo[]  = $i; }

            if (!isset($key_eval_aanrader[$i])  AND $item['column_name'] == "aanraden_score_2170")      { $all_eval_aanrader[]  = $i; }

        }

        wachthond($extdebug,4, "indexed", $indexed);

        $key_eval_datum             = $all_eval_datum[0];
        $key_eval_terugblik         = $all_eval_terugblik[0];
        $key_eval_thema             = $all_eval_thema[0];
        $key_eval_inhoud            = $all_eval_inhoud[0];
        $key_eval_actief            = $all_eval_actief[0];

        $key_eval_vrijetijd         = $all_eval_vrijetijd[0];
        $key_eval_corvee            = $all_eval_corvee[0];        

        $key_eval_slapen            = $all_eval_slapen[0];
        $key_eval_eten              = $all_eval_eten[0];
        $key_eval_brengen           = $all_eval_brengen[0];
        $key_eval_kampinfo          = $all_eval_kampinfo[0];

        $key_eval_aanrader          = $all_eval_aanrader[0];        

        wachthond($extdebug,4,  "key_eval_datum",           $key_eval_datum);
        wachthond($extdebug,4,  "key_eval_terugblik",       $key_eval_terugblik);
        wachthond($extdebug,4,  "key_eval_thema",           $key_eval_thema);
        wachthond($extdebug,4,  "key_eval_inhoud",          $key_eval_inhoud);
        wachthond($extdebug,4,  "key_eval_actief",          $key_eval_actief);

        wachthond($extdebug,4,  "key_eval_corvee",          $key_eval_corvee);
        wachthond($extdebug,4,  "key_eval_vrijetijd",       $key_eval_vrijetijd);

        wachthond($extdebug,4,  "key_eval_slapen",          $key_eval_slapen);
        wachthond($extdebug,4,  "key_eval_eten",            $key_eval_eten);
        wachthond($extdebug,4,  "key_eval_brengen",         $key_eval_brengen);
        wachthond($extdebug,4,  "key_eval_kampinfo",        $key_eval_kampinfo);

        wachthond($extdebug,4,  "key_eval_aanrader",        $key_eval_aanrader);        

        // PLEZIER
        // DIEPGANG
        // RESPECT
        // EENHEID
        // VEILIGHEID
        // VERTROUWEN

        ##########################################################################################
        ### GET EVAL DATUM INGEVULD
        ##########################################################################################        

        if ($key_eval_datum >= 0) {
            $pid_eval_datum           = $params[$key_eval_datum]['entity_id']       ?? NULL;
            wachthond($extdebug,4,      "pid_eval_datum", $pid_eval_datum);

            $raw_eval_datum           = $params[$key_eval_datum]['value']           ?? NULL;
            if ($raw_eval_datum) {
                $val_eval_datum       = date("Y-m-d H:i:s", strtotime($raw_eval_datum)); 
                $krt_eval_datum       = date("Y-m-d",       strtotime($raw_eval_datum)); 
            } else {
                $val_eval_datum       = NULL; 
                $krt_eval_datum       = NULL; 
            }
            wachthond($extdebug,4,  "raw_eval_datum",   $raw_eval_datum);
            wachthond($extdebug,4,  "val_eval_datum",   $val_eval_datum);
        }

        $inputString    = $val_eval_datum;
        // Stap 2: Zet om naar timestamp
        $inputTimestamp = strtotime($inputString);

        if ($inputTimestamp === false) {
            $output = $inputString; // Fout bij parsen
        } else {
            $datum      = date('Y-m-d', $inputTimestamp);
            $tijd       = date('H:i:s', $inputTimestamp);
            $vandaag    = date('Y-m-d');

            if ($datum === $vandaag && $tijd === '00:00:00') {
                // Vervang tijd door huidige tijd
                $output = date('d-m-Y H:i:s'); // huidige datum + tijd
            } else {
                // Houd originele input
                $output = date('Y-m-d H:i:s', $inputTimestamp);
            }
        }

        $old_part_eval_datum    = $val_eval_datum;
        $new_part_eval_datum    = $output;

        $ditevent_eval_datum    = $new_part_eval_datum;

        wachthond($extdebug,3,  "old_part_eval_datum",  $old_part_eval_datum);
        wachthond($extdebug,3,  "new_part_eval_datum",  $new_part_eval_datum);

        wachthond($extdebug,3,  "ditevent_eval_datum",  $ditevent_eval_datum);

        ##########################################################################################
        ### GET SCORE TERUBLIK
        ##########################################################################################        

        if ($key_eval_terugblik >= 0) {
            $raw_eval_terugblik     = $params[$key_eval_terugblik]['value']             ?? NULL;
            $new_eval_terugblik     = explode('', $raw_eval_terugblik ?? '');
            $val_eval_terugblik     = $raw_eval_terugblik;  
            wachthond($extdebug,4,  "raw_eval_terugblik", $raw_eval_terugblik);
            wachthond($extdebug,4,  "new_eval_terugblik", $new_eval_terugblik);
            wachthond($extdebug,3,  "val_eval_terugblik", $val_eval_terugblik);
        }        

        ##########################################################################################
        ### GET SCORE THEMA
        ##########################################################################################        

        if ($key_eval_thema >= 0) {
            $raw_eval_thema     = $params[$key_eval_thema]['value']                     ?? NULL;
            $new_eval_thema     = explode('', $raw_eval_thema ?? '');
            $val_eval_thema     = $raw_eval_thema;  
            wachthond($extdebug,4,  "raw_eval_thema", $raw_eval_thema);
            wachthond($extdebug,4,  "new_eval_thema", $new_eval_thema);
            wachthond($extdebug,3,  "val_eval_thema", $val_eval_thema);
        }

        ##########################################################################################
        ### GET SCORE INHOUD
        ##########################################################################################        

        if ($key_eval_inhoud >= 0) {
            $raw_eval_inhoud        = $params[$key_eval_inhoud]['value']                ?? NULL;
            $new_eval_inhoud        = explode('', $raw_eval_inhoud ?? '');
            $val_eval_inhoud        = $raw_eval_inhoud;  
            wachthond($extdebug,4,  "raw_eval_inhoud", $raw_eval_inhoud);
            wachthond($extdebug,4,  "new_eval_inhoud", $new_eval_inhoud);
            wachthond($extdebug,3,  "val_eval_inhoud", $val_eval_inhoud);
        }

        ##########################################################################################
        ### GET SCORE ACTIEF
        ##########################################################################################        

        if ($key_eval_actief >= 0) {
            $raw_eval_actief        = $params[$key_eval_actief]['value']                ?? NULL;
            $new_eval_actief        = explode('', $raw_eval_actief ?? '');
            $val_eval_actief        = $raw_eval_actief;  
            wachthond($extdebug,4,  "raw_eval_actief", $raw_eval_actief);
            wachthond($extdebug,4,  "new_eval_actief", $new_eval_actief);
            wachthond($extdebug,3,  "val_eval_actief", $val_eval_actief);
        }

        ##########################################################################################
        ### GET SCORE CORVEE
        ##########################################################################################        

        if ($key_eval_corvee >= 0) {
            $raw_eval_corvee        = $params[$key_eval_corvee]['value']                ?? NULL;
            $new_eval_corvee        = explode('', $raw_eval_corvee ?? '');
            $val_eval_corvee        = $raw_eval_corvee;  
            wachthond($extdebug,4,  "raw_eval_corvee", $raw_eval_corvee);
            wachthond($extdebug,4,  "new_eval_corvee", $new_eval_corvee);
            wachthond($extdebug,3,  "val_eval_corvee", $val_eval_corvee);
        }

        ##########################################################################################
        ### GET SCORE VRIJETIJD
        ##########################################################################################        

        if ($key_eval_vrijetijd >= 0) {
            $raw_eval_vrijetijd     = $params[$key_eval_vrijetijd]['value']             ?? NULL;
            $new_eval_vrijetijd     = explode('', $raw_eval_vrijetijd ?? '');
            $val_eval_vrijetijd     = $raw_eval_vrijetijd;  
            wachthond($extdebug,4,  "raw_eval_vrijetijd", $raw_eval_vrijetijd);
            wachthond($extdebug,4,  "new_eval_vrijetijd", $new_eval_vrijetijd);
            wachthond($extdebug,3,  "val_eval_vrijetijd", $val_eval_vrijetijd);
        }

        ##########################################################################################
        ### GET SCORE SLAPEN
        ##########################################################################################        

        if ($key_eval_slapen >= 0) {
            $raw_eval_slapen        = $params[$key_eval_slapen]['value']                ?? NULL;
            $new_eval_slapen        = explode('', $raw_eval_slapen ?? '');
            $val_eval_slapen        = $raw_eval_slapen;  
            wachthond($extdebug,4,  "raw_eval_slapen", $raw_eval_slapen);
            wachthond($extdebug,4,  "new_eval_slapen", $new_eval_slapen);
            wachthond($extdebug,3,  "val_eval_slapen", $val_eval_slapen);
        }

        ##########################################################################################
        ### GET SCORE ETEN
        ##########################################################################################        

        if ($key_eval_eten >= 0) {
            $raw_eval_eten          = $params[$key_eval_eten]['value']                  ?? NULL;
            $new_eval_eten          = explode('', $raw_eval_eten ?? '');
            $val_eval_eten          = $raw_eval_eten;  
            wachthond($extdebug,4,  "raw_eval_eten", $raw_eval_eten);
            wachthond($extdebug,4,  "new_eval_eten", $new_eval_eten);
            wachthond($extdebug,3,  "val_eval_eten", $val_eval_eten);
        }

        ##########################################################################################
        ### GET SCORE BRENGEN
        ##########################################################################################        

        if ($key_eval_brengen >= 0) {
            $raw_eval_brengen       = $params[$key_eval_brengen]['value']               ?? NULL;
            $new_eval_brengen       = explode('', $raw_eval_brengen ?? '');
            $val_eval_brengen       = $raw_eval_brengen;  
            wachthond($extdebug,4,  "raw_eval_brengen", $raw_eval_brengen);
            wachthond($extdebug,4,  "new_eval_brengen", $new_eval_brengen);
            wachthond($extdebug,3,  "val_eval_brengen", $val_eval_brengen);
        }

        ##########################################################################################
        ### GET SCORE KAMPINFO
        ##########################################################################################        

        if ($key_eval_kampinfo >= 0) {
            $raw_eval_kampinfo      = $params[$key_eval_kampinfo]['value']              ?? NULL;
            $new_eval_kampinfo      = explode('', $raw_eval_kampinfo ?? '');
            $val_eval_kampinfo      = $raw_eval_kampinfo;  
            wachthond($extdebug,4,  "raw_eval_kampinfo", $raw_eval_kampinfo);
            wachthond($extdebug,4,  "new_eval_kampinfo", $new_eval_kampinfo);
            wachthond($extdebug,3,  "val_eval_kampinfo", $val_eval_kampinfo);
        }

        ##########################################################################################
        ### GET SCORE AANRADER
        ##########################################################################################        

        if ($key_eval_aanrader >= 0) {
            $raw_eval_aanrader      = $params[$key_eval_aanrader]['value']              ?? NULL;
            $new_eval_aanrader      = explode('', $raw_eval_aanrader ?? '');
            $val_eval_aanrader      = $raw_eval_aanrader;  
            wachthond($extdebug,4,  "raw_eval_aanrader", $raw_eval_aanrader);
            wachthond($extdebug,4,  "new_eval_aanrader", $new_eval_aanrader);
            wachthond($extdebug,3,  "val_eval_aanrader", $val_eval_aanrader);
        }

        wachthond($extdebug,2, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] PART EVAL 2.1 BEPAAL SCORE TOP & TIP",         "[$displayname]");
        wachthond($extdebug,2, "########################################################################");

        if (in_array($val_eval_terugblik,   array(1, 2, 3, 4, 5, 6)))                   { $score_terugblik_low  = 1; } else { $score_terugblik_low  = NULL; }
        if (in_array($val_eval_inhoud,      array(1, 2, 3, 4, 5, 6)))                   { $score_inhoud_low     = 1; } else { $score_inhoud_low     = NULL; }
        if (in_array($val_eval_actief,      array(1, 2, 3, 4, 5, 6)))                   { $score_spellen_low    = 1; } else { $score_spellen_low    = NULL; }
        if (in_array($val_eval_slapen,      array("slecht","nietzogoed","kanbeter")))   { $score_slapen_low     = 1; } else { $score_slapen_low     = NULL; }
        if (in_array($val_eval_kampthema,   array(1, 2, 3, 4, 5, 6)))                   { $score_kampthema_low  = 1; } else { $score_kampthema_low  = NULL; } 
        if (in_array($val_eval_eten,        array(1, 2, 3, 4, 5, 6)))                   { $score_eten_low       = 1; } else { $score_eten_low       = NULL; } 
        if (in_array($val_eval_brengen,     array("slecht","nietzogoed","kanbeter")))   { $score_brengen_low    = 1; } else { $score_brengen_low    = NULL; }
        if (in_array($val_eval_kampinfo,    array("slecht","nietzogoed","kanbeter")))   { $score_kampinfo_low   = 1; } else { $score_kampinfo_low   = NULL; }

        if (in_array($val_eval_terugblik,   array(8, 9, 10)))                           { $score_terugblik_top  = 1; } else { $score_terugblik_top  = NULL; }
        if (in_array($val_eval_inhoud,      array(8, 9, 10)))                           { $score_inhoud_top     = 1; } else { $score_inhoud_top     = NULL; }
        if (in_array($val_eval_actief,      array(8, 9, 10)))                           { $score_spellen_top    = 1; } else { $score_spellen_top    = NULL; } 
        if (in_array($val_eval_slapen,      array("zeergoed","uitstekend")))            { $score_slapen_top     = 1; } else { $score_slapen_top     = NULL; }
        if (in_array($val_eval_kampthema,   array(8, 9, 10)))                           { $score_kampthema_top  = 1; } else { $score_kampthema_top  = NULL; }
        if (in_array($val_eval_eten,        array(8, 9, 10)))                           { $score_eten_top       = 1; } else { $score_eten_top       = NULL; }        
        if (in_array($val_eval_brengen,     array("zeergoed","uitstekend")))            { $score_brengen_top    = 1; } else { $score_brengen_top    = NULL; }
        if (in_array($val_eval_kampinfo,    array("zeergoed","uitstekend")))            { $score_kampinfo_top   = 1; } else { $score_kampinfo_top   = NULL; }

        $scores_eval_low = ( $score_terugblik_low + $score_inhoud_low + $score_spellen_low + $score_slapen_low + $score_kampthema_low + $score_eten_low + $score_brengen_low + $score_kampinfo_low );
        $scores_eval_top = ( $score_terugblik_top + $score_inhoud_top + $score_spellen_top + $score_slapen_top + $score_kampthema_top + $score_eten_top + $score_brengen_top + $score_kampinfo_top );

        wachthond($extdebug,1, "scores_eval_low",        $scores_eval_low);
        wachthond($extdebug,1, "scores_eval_top",        $scores_eval_top);

        wachthond($extdebug,2, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] PART EVAL 3.1 INJECTEER WAARDE IN PARAMS",     "[$displayname]");
        wachthond($extdebug,2, "########################################################################");

        wachthond($extdebug,2, "old_part_eval_datum",       $old_part_eval_datum);
        wachthond($extdebug,2, "new_part_eval_datum",       $new_part_eval_datum);
        wachthond($extdebug,3, "key_eval_datum",            $key_eval_datum);

        // M61 converteer de datum naar een manier die weg te schrijven is in params
        $new_part_eval_datum_datetime  = new DateTime($new_part_eval_datum);
        wachthond($extdebug,4,  'new_part_eval_datum_datetime',    $new_part_eval_datum_datetime);
        $new_part_eval_datum_string    = $new_part_eval_datum_datetime->format('Y-m-d H:i:s');
        wachthond($extdebug,2,  'new_part_eval_datum_string',      $new_part_eval_datum_string);
        $new_part_eval_datum_dbstring  = date("YmdHis",  strtotime($new_part_eval_datum_string));
        wachthond($extdebug,3,  'new_part_eval_datum_dbstring',    $new_part_eval_datum_dbstring);

        if (is_numeric($key_eval_datum) AND !empty($new_part_eval_datum) AND $old_part_eval_datum != $new_part_eval_datum_dbstring) {
            wachthond($extdebug,2, 'OLD params[key_eval_datum][value]', $params[$key_eval_datum]);
            $params[$key_eval_datum]['value'] = $new_part_eval_datum_dbstring;
            wachthond($extdebug,2, 'NEW params[key_eval_datum][value]', $params[$key_eval_datum]);
        }

        wachthond($extdebug,2, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] PART EVAL 4.1 UPDATE PART",                    "[$displayname]");
        wachthond($extdebug,2, "########################################################################");

        $params_part_ditevent = [
            #'reload'           => TRUE,
            'checkPermissions'  => FALSE,
            'debug'       => $apidebug,
            'where' => [
                ['id',  '=', $ditevent_part_id],
            ],
            'values' => [
                'id'    =>   $ditevent_part_id,
            ],
        ];

        $params_part_ditevent['values']['PART_EVALUATIE_INTERN.Scores_onvoldoende'] = $scores_eval_low;
        $params_part_ditevent['values']['PART_EVALUATIE_INTERN.Scores_uitstekend']  = $scores_eval_top;

        wachthond($extdebug,3, 'params_part_ditevent',              $params_part_ditevent);
        $result_part_ditevent = civicrm_api4('Participant','update',$params_part_ditevent);
        wachthond($extdebug,9, 'result_part_ditevent',              $result_part_ditevent);

        wachthond($extdebug,1, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] PART EVAL EINDE [$displayname $ditevent_part_functie $ditevent_part_kampkort]", "[groupID: $groupID]");
        wachthond($extdebug,1, "########################################################################");

    }

    #########################################################################
    ### EVALUATIE PART LEID [PRE]                                     [START]
    #########################################################################

    if (in_array($groupID, $profilepartevalleid)) {

        wachthond($extdebug,1, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] PART LEID 2.1 START RETRIEVE VALUES LEID FROM PARAMS", "[$displayname - groupID: $groupID]");
        wachthond($extdebug,1, "########################################################################");

        wachthond($extdebug,4, "entityid",    $entityID);
        wachthond($extdebug,4, "params",      $params);

        foreach($params as $i=>$item) {

            if ( !isset($indexed[$i][$item['id']]) ) {
                $indexed[$i]['key']         = $i;
                $indexed[$i]['entity_id']   = $item['entity_id']    ?? NULL;
                $indexed[$i]['column_name'] = $item['column_name']  ?? NULL;
                $indexed[$i]['table_name']  = $item['table_name']   ?? NULL;
                $indexed[$i]['value']       = $item['value']        ?? NULL;
            }

            if (!isset($key_eval_geestelijk[$i])    AND $item['column_name'] == "geestelijk_score_2179")            { $all_eval_geestelijk[]    = $i; }
            if (!isset($key_eval_praktisch[$i])     AND $item['column_name'] == "praktisch_score_2181")             { $all_eval_praktisch[]     = $i; }
            if (!isset($key_eval_team[$i])          AND $item['column_name'] == "team_score_2177")                  { $all_eval_team[]          = $i; }
            if (!isset($key_eval_prep[$i])          AND $item['column_name'] == "voorbereiding_score_2184")         { $all_eval_prep[]          = $i; }
            if (!isset($key_eval_veiligsociaal[$i]) AND $item['column_name'] == "veiligheid_sociaal_score_2186")    { $all_eval_veiligsociaal[] = $i; }
            if (!isset($key_eval_veiligfysiek[$i])  AND $item['column_name'] == "veiligheid_praktisch_score_2188")  { $all_eval_veiligfysiek[]  = $i; }
        }

        wachthond($extdebug,4, "indexed", $indexed);

        $key_eval_geestelijk    = $all_eval_geestelijk[0];
        $key_eval_praktisch     = $all_eval_praktisch[0];
        $key_eval_team          = $all_eval_team[0];
        $key_eval_prep          = $all_eval_prep[0];
        $key_eval_veiligsociaal = $all_eval_veiligsociaal[0];
        $key_eval_veiligfysiek  = $all_eval_veiligfysiek[0];        

        wachthond($extdebug,4,  "key_eval_geestelijk",      $key_eval_geestelijk);
        wachthond($extdebug,4,  "key_eval_praktisch",       $key_eval_praktisch);
        wachthond($extdebug,4,  "key_eval_team",            $key_eval_team);
        wachthond($extdebug,4,  "key_eval_prep",            $key_eval_prep);
        wachthond($extdebug,4,  "key_eval_actief",          $key_eval_actief);
        wachthond($extdebug,4,  "key_eval_veiligfysiek",    $key_eval_veiligfysiek);
        wachthond($extdebug,4,  "key_eval_veiligsociaal",   $key_eval_veiligsociaal);

        ##########################################################################################
        ### GET SCORE LEID GEESTELIJK
        ##########################################################################################        

        if ($key_eval_geestelijk >= 0) {
            $raw_eval_geestelijk     = $params[$key_eval_geestelijk]['value']           ?? NULL;
            $new_eval_geestelijk     = explode('', $raw_eval_geestelijk ?? '');
            $val_eval_geestelijk     = $raw_eval_geestelijk;  
            wachthond($extdebug,4,  "raw_eval_geestelijk", $raw_eval_geestelijk);
            wachthond($extdebug,4,  "new_eval_geestelijk", $new_eval_geestelijk);
            wachthond($extdebug,3,  "val_eval_geestelijk", $val_eval_geestelijk);
        }        

        ##########################################################################################
        ### GET SCORE LEID PRAKTISCH
        ##########################################################################################        

        if ($key_eval_praktisch >= 0) {
            $raw_eval_praktisch     = $params[$key_eval_praktisch]['value']             ?? NULL;
            $new_eval_praktisch     = explode('', $raw_eval_praktisch ?? '');
            $val_eval_praktisch     = $raw_eval_praktisch;  
            wachthond($extdebug,4,  "raw_eval_praktisch", $raw_eval_praktisch);
            wachthond($extdebug,4,  "new_eval_praktisch", $new_eval_praktisch);
            wachthond($extdebug,3,  "val_eval_praktisch", $val_eval_praktisch);
        }

        ##########################################################################################
        ### GET SCORE LEID TEAM
        ##########################################################################################        

        if ($key_eval_team >= 0) {
            $raw_eval_team      = $params[$key_eval_team]['value']                      ?? NULL;
            $new_eval_team      = explode('', $raw_eval_team ?? '');
            $val_eval_team      = $raw_eval_team;  
            wachthond($extdebug,4,  "raw_eval_team", $raw_eval_team);
            wachthond($extdebug,4,  "new_eval_team", $new_eval_team);
            wachthond($extdebug,3,  "val_eval_team", $val_eval_team);
        }

        ##########################################################################################
        ### GET SCORE LEID VOORBEREIDING
        ##########################################################################################        

        if ($key_eval_prep >= 0) {
            $raw_eval_prep        = $params[$key_eval_prep]['value']                    ?? NULL;
            $new_eval_prep        = explode('', $raw_eval_prep ?? '');
            $val_eval_prep        = $raw_eval_prep;  
            wachthond($extdebug,4,  "raw_eval_prep", $raw_eval_prep);
            wachthond($extdebug,4,  "new_eval_prep", $new_eval_prep);
            wachthond($extdebug,3,  "val_eval_prep", $val_eval_prep);
        }

        ##########################################################################################
        ### GET SCORE LEID VEILIGSOCIAAL
        ##########################################################################################        

        if ($key_eval_veiligsociaal >= 0) {
            $raw_eval_veiligsociaal     = $params[$key_eval_veiligsociaal]['value']     ?? NULL;
            $new_eval_veiligsociaal     = explode('', $raw_eval_veiligsociaal ?? '');
            $val_eval_veiligsociaal     = $raw_eval_veiligsociaal;  
            wachthond($extdebug,4,  "raw_eval_veiligsociaal", $raw_eval_veiligsociaal);
            wachthond($extdebug,4,  "new_eval_veiligsociaal", $new_eval_veiligsociaal);
            wachthond($extdebug,3,  "val_eval_veiligsociaal", $val_eval_veiligsociaal);
        }

        ##########################################################################################
        ### GET SCORE LEID VEILIGFYSIEK
        ##########################################################################################        

        if ($key_eval_veiligfysiek >= 0) {
            $raw_eval_veiligfysiek        = $params[$key_eval_veiligfysiek]['value'] ?? NULL;
            $new_eval_veiligfysiek        = explode('', $raw_eval_veiligfysiek ?? '');
            $val_eval_veiligfysiek        = $raw_eval_veiligfysiek;  
            wachthond($extdebug,4,  "raw_eval_veiligfysiek", $raw_eval_veiligfysiek);
            wachthond($extdebug,4,  "new_eval_veiligfysiek", $new_eval_veiligfysiek);
            wachthond($extdebug,3,  "val_eval_veiligfysiek", $val_eval_veiligfysiek);
        }

        wachthond($extdebug,2, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] PART LEID 2.1 BEPAAL SCORE TOP & TIP",         "[$displayname]");
        wachthond($extdebug,2, "########################################################################");

        if (in_array($val_eval_geestelijk,      array(1, 2, 3, 4, 5, 6)))       { $score_geestelijk_low     = 1; } else { $score_geestelijk_low     = NULL; }
        if (in_array($val_eval_praktisch,       array(1, 2, 3, 4, 5, 6)))       { $score_spellen_low        = 1; } else { $score_praktisch_low      = NULL; }
        if (in_array($val_eval_team,            array(1, 2, 3, 4, 5, 6)))       { $score_team_low           = 1; } else { $score_team_low           = NULL; }
        if (in_array($val_eval_prep,            array(1, 2, 3, 4, 5, 6)))       { $score_prep_low           = 1; } else { $score_prep_low           = NULL; }
        if (in_array($val_eval_veiligsociaal,   array(1, 2, 3, 4, 5, 6)))       { $score_veiligsociaal_low  = 1; } else { $score_veiligsociaal_low  = NULL; }
        if (in_array($val_eval_veiligfysiek,    array(1, 2, 3, 4, 5, 6)))       { $score_veiligfysiek_low   = 1; } else { $score_veiligfysiek_low   = NULL; } 

        if (in_array($val_eval_geestelijk,      array(8, 9, 10)))               { $score_geestelijk_top     = 1; } else { $score_geestelijk_top     = NULL; }
        if (in_array($val_eval_praktisch,       array(8, 9, 10)))               { $score_praktisch_top      = 1; } else { $score_praktisch_top      = NULL; } 
        if (in_array($val_eval_team,            array(8, 9, 10)))               { $score_team_top           = 1; } else { $score_team_top           = NULL; }
        if (in_array($val_eval_prep,            array(8, 9, 10)))               { $score_prep_top           = 1; } else { $score_prep_top           = NULL; }
        if (in_array($val_eval_veiligsociaal,   array(8, 9, 10)))               { $score_veiligsociaal_top  = 1; } else { $score_veiligsociaal_top  = NULL; }
        if (in_array($val_eval_veiligfysiek,    array(8, 9, 10)))               { $score_veiligfysiek_top   = 1; } else { $score_veiligfysiek_top   = NULL; }        

        $scores_leid_low = ( $score_geestelijk_low + $score_praktisch_low + $score_team_low + $score_prep_low + $score_veiligsociaal_low + $score_veiligfysiek_low );
        $scores_leid_top = ( $score_geestelijk_top + $score_praktisch_top + $score_team_top + $score_prep_top + $score_veiligsociaal_top + $score_veiligfysiek_top );

        wachthond($extdebug,1, "scores_leid_low",        $scores_leid_low);
        wachthond($extdebug,1, "scores_leid_top",        $scores_leid_top);

        wachthond($extdebug,1, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] PART LEID EINDE [$displayname $ditevent_part_functie $ditevent_part_kampkort]", "[groupID: $groupID]");
        wachthond($extdebug,1, "########################################################################");

    }

    wachthond($extdebug,1, "########################################################################");
    wachthond($extdebug,1, "### EVALUATIE [PRE] 5.X START UPDATE ACTIVITIES",        "[$displayname]");
    wachthond($extdebug,1, "########################################################################");

    if ($exteval == 1 AND in_array($groupID,$profilepartevalall) AND ($ditjaareventdeelyes == 1 OR $ditjaareventleidyes == 1 )) { // PROFILE CONT & PART (BASIC)

        wachthond($extdebug,1, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] 5.1 GET ACTIVITY EVALUATIE ###",     "[$displayname]");
        wachthond($extdebug,1, "########################################################################");

        $params_activity_evaluatie_get = [
            'checkPermissions' => FALSE,
            'debug' => $apidebug,
            'select' => [
                'row_count',
                'id',
                'activity_date_time',
                'status_id',
                'status_id:name',
                'subject',
                'activity_contact.contact_id',
                'ACT_ALG.kampkort',
                'ACT_ALG.kampfunctie',
                'ACT_ALG.kampjaar',
                'ACT_ALG.kampjaar',
                'ACT_EVAL.scores_eval_low',
                'ACT_EVAL.scores_deel_low',
                'ACT_EVAL.scores_leid_low',
                'ACT_EVAL.scores_eval_top',
                'ACT_EVAL.scores_deel_top',
                'ACT_EVAL.scores_leid_top',
            ],
            'join' => [
                ['ActivityContact AS activity_contact', 'INNER'],
            ],
            'where' => [
                ['activity_contact.contact_id',         '=',  $contact_id],
                ['activity_contact.record_type_id',     '=',  3],
    #           ['activity_type_id',                    '=',  150],
                ['activity_type_id:name',               '=', 'Evaluatie'],
                ['activity_date_time',                  '>=', $ditevent_fiscalyear_start],
                ['activity_date_time',                  '<=', $ditevent_fiscalyear_einde],        
            ],
            'orderBy' => [
                'id' => 'ASC',
            ],
            'limit' => 1,
        ];

        wachthond($extdebug,7, 'params_activity_evaluatie_get',     $params_activity_evaluatie_get);
        $result_evaluatie_get       = civicrm_api4('Activity','get',$params_activity_evaluatie_get);
        $result_evaluatie_get_count = $result_evaluatie_get->countMatched();
        wachthond($extdebug,3, 'result_activity_evaluatie_get',     $result_evaluatie_get);   
        wachthond($extdebug,4, 'result_evaluatie_count',            $result_evaluatie_get_count);

        if ($result_evaluatie_get_count == 1) {
            $evaluatie_activity_id          = $result_evaluatie_get[0]['id']                        ?? NULL;
            $evaluatie_activity_status_id   = $result_evaluatie_get[0]['status_id']                 ?? NULL;
            $evaluatie_activity_status_name = $result_evaluatie_get[0]['status_id:name']            ?? NULL;
            $evaluatie_activity_datum       = $result_evaluatie_get[0]['activity_date_time']        ?? NULL;

            $evaluatie_scores_eval_low      = $result_evaluatie_get[0]['ACT_EVAL.scores_eval_low']  ?? NULL;
            $evaluatie_scores_deel_low      = $result_evaluatie_get[0]['ACT_EVAL.scores_deel_low']  ?? NULL;
            $evaluatie_scores_leid_low      = $result_evaluatie_get[0]['ACT_EVAL.scores_leid_low']  ?? NULL;

            $evaluatie_scores_eval_top      = $result_evaluatie_get[0]['ACT_EVAL.scores_eval_top']  ?? NULL;
            $evaluatie_scores_deel_top      = $result_evaluatie_get[0]['ACT_EVAL.scores_deel_top']  ?? NULL;
            $evaluatie_scores_leid_top      = $result_evaluatie_get[0]['ACT_EVAL.scores_leid_top']  ?? NULL;

            wachthond($extdebug,2, "evaluatie_activity_id",             $evaluatie_activity_id);
            wachthond($extdebug,2, "evaluatie_activity_status_id",      $evaluatie_activity_status_id);
            wachthond($extdebug,2, "evaluatie_activity_status_name",    $evaluatie_activity_status_name);
            wachthond($extdebug,2, "evaluatie_activity_datum",          $evaluatie_activity_datum);

            wachthond($extdebug,2, "evaluatie_scores_eval_low",         $evaluatie_scores_eval_low);
            wachthond($extdebug,2, "evaluatie_scores_deel_low",         $evaluatie_scores_deel_low);
            wachthond($extdebug,2, "evaluatie_scores_leid_low",         $evaluatie_scores_leid_low);

            wachthond($extdebug,2, "evaluatie_scores_eval_top",         $evaluatie_scores_eval_top);
            wachthond($extdebug,2, "evaluatie_scores_deel_top",         $evaluatie_scores_deel_top);
            wachthond($extdebug,2, "evaluatie_scores_leid_top",         $evaluatie_scores_leid_top);

        } else {
            $evaluatie_activity_id      = NULL;
            $evaluatie_activity_status  = NULL;
            $evaluatie_activity_datum   = NULL;
            wachthond($extdebug,1, "evaluatie_activity_id",       "No Activity Found");
        }

        wachthond($extdebug,1, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] 5.2 BEPAAL NEW STATUS ACTIVITEIT",   "[$displayname]");
        wachthond($extdebug,1, "########################################################################");

        $eventendplus01     = strtotime ( '+1 day' , strtotime($ditevent_part_kampeinde));  // VERSTUUR EVALUATIE PAS DAG NA KAMP
        $eventendplus30     = strtotime ( '+30 day', strtotime($ditevent_part_kampeinde));
        $eventendplus60     = strtotime ( '+60 day', strtotime($ditevent_part_kampeinde));

        $eventendplus01date = date ( 'Y-m-d H:i' , $eventendplus01 );
        $eventendplus60date = date ( 'Y-m-d H:i' , $eventendplus60 );

        $diffsince_evaluatieverzoek = date_diff(date_create($eventendplus01date),date_create($today_datetime));
        $dayssince_evaluatieverzoek = $diffsince_evaluatieverzoek->format('%a');

                                                  $status_evaluatie = "Scheduled";          // VOOR AANPASSING METEEN NA CREATIE
        if ($dayssince_evaluatieverzoek <  0)   { $status_evaluatie = "Scheduled";      }   // INGEPLAND
        if ($dayssince_evaluatieverzoek >= 0)   { $status_evaluatie = "Pending";        }   // AFWACHTING
        if ($dayssince_evaluatieverzoek >= 7)   { $status_evaluatie = "Left Message";   }   // HERINNERD
        if ($dayssince_evaluatieverzoek >= 21)  { $status_evaluatie = "Unreachable";    }   // ONBEREIKBAAR
        if ($dayssince_evaluatieverzoek >= 35)  { $status_evaluatie = "No_show";        }   // VERLOPEN
        if ($dayssince_evaluatieverzoek >= 49)  { $status_evaluatie = "Bounced";        }   // GEFAALD    
        if ($ditevent_eval_datum)               { $status_evaluatie = 'Completed';      }   // AFGEROND (STATUS BIJ INGEVULDE EVALUATIE)

        if (infiscalyear($ditevent_eval_datum, $ditevent_part_kampstart) == 1) {
            $status_evaluatie               = 'Completed';
            $evaluatie_activity_new_date    = $ditevent_eval_datum;
            wachthond($extdebug,2, "EVALUATIE INGEVULD",        $ditevent_eval_datum);
        } else {
            $evaluatie_activity_new_date    = $eventendplus60date;
        }

        // M61: als ditevent_eval_datum leeg is (bv als profiel part eval leid wordt gebruikt > hou dan de status van de activiteit hetzelfde)
        if (empty($ditevent_eval_datum) AND $evaluatie_activity_status_name)            { $status_evaluatie = $evaluatie_activity_status_name; }

        wachthond($extdebug,1, "today_datetime",                $today_datetime);
        wachthond($extdebug,1, "event end date",                $ditevent_part_kampeinde);
        wachthond($extdebug,1, "eventendplus01date",            $eventendplus01date);
        wachthond($extdebug,1, "event end date + 60",           $eventendplus60date);
        wachthond($extdebug,1, "dayssince_evaluatieverzoek",    $dayssince_evaluatieverzoek);
        wachthond($extdebug,1, "datum evaluatie",               $ditevent_eval_datum);
        wachthond($extdebug,1, "evaluatie_activity_status_name",$evaluatie_activity_status_name);

        wachthond($extdebug,1, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] 5.3 CREATE AN ACTIVITY EVALUATIE",   "[$displayname]");
        wachthond($extdebug,1, "########################################################################");

        if (empty($evaluatie_activity_id) AND infiscalyear($ditevent_part_kampeinde,$today_datetime,'event_einde','todayfiscalyear') == 1) {

            $evaluatie_activity_status_name = 'Scheduled';  // initial status ingepland

            if (($ditjaareventdeelyes == 1 OR $ditjaareventleidyes == 1 ) AND $ditevent_part_kampeinde <= $today_datetime) {

                $params_activity_evaluatie_create = [
                    'checkPermissions' => FALSE,
                    'debug' => $apidebug,
                    'values' => [
                        'source_contact_id'         => 1,
                        'target_contact_id'         => $contact_id,
           #            'activity_type_id'          => 150,
                        'activity_type_id:name'     => 'Evaluatie',
                        'activity_date_time'        => $evaluatie_activity_new_date,
                        'subject'                   => 'Evaluatie '. $ditevent_part_kampkort_low. ' door '. $displayname,
                        'status_id:name'            => $evaluatie_activity_status_name,

                        'ACT_ALG.actcontact_naam'   => $displayname,
                        'ACT_ALG.actcontact_cid'    => $contact_id,
                        'ACT_ALG.actcontact_pid'    => $ditevent_part_id,
                        'ACT_ALG.actcontact_eid'    => $ditevent_part_eventid,

                        'ACT_ALG.kamptype_nr'       => $ditevent_part_kamptype_nr,
                        'ACT_ALG.kampnaam'          => $ditevent_part_kampkort_cap,
                        'ACT_ALG.kampkort'          => $ditevent_part_kampkort_low,
                        'ACT_ALG.kampfunctie'       => $ditevent_part_functie,
                        'ACT_ALG.kamprol'           => $ditevent_part_rol,

                        'ACT_ALG.kampstart'         => $ditevent_part_kampstart,
                        'ACT_ALG.kampeinde'         => $ditevent_part_kampeinde,
                        'ACT_ALG.kampjaar'          => $ditevent_part_kampjaar,
                        'ACT_ALG.modified'          => $today_datetime,

                        'ACT_ALG.prioriteit:label'  => 'Normaal',
                        'ACT_ALG.afgerond'          => $ditevent_eval_datum,
                    ],
                ];
                wachthond($extdebug,7, 'params_activity_evaluatie_create', $params_activity_evaluatie_create);

                if ($eventendplus60date) {
                  $result_evaluatie_create = civicrm_api4('Activity', 'create', $params_activity_evaluatie_create);
                }

                wachthond($extdebug,2, "params_activity_evaluatie_create",      "EXECUTED");
                wachthond($extdebug,9, 'result_activity_evaluatie_create RESULT',           $result_evaluatie_create);
                if (empty($evaluatie_activity_id))        { $evaluatie_activity_id        = $result_evaluatie_create[0]['id']                 ?? NULL; }
                if (empty($evaluatie_activity_status_id)) { $evaluatie_activity_status_id = $result_evaluatie_create[0]['status_id']          ?? NULL; }
                if (empty($evaluatie_activity_datum))     { $evaluatie_activity_datum     = $result_evaluatie_create[0]['activity_date_time'] ?? NULL; }

                wachthond($extdebug,3, "evaluatie_activity_id",         $evaluatie_activity_id);
                wachthond($extdebug,3, "evaluatie_activity_status_id",  $evaluatie_activity_status_id);
                wachthond($extdebug,3, "evaluatie_activity_status_name",$evaluatie_activity_status_name);
                wachthond($extdebug,3, "evaluatie_activity_datum",      $evaluatie_activity_datum);
            }

        } else {
            wachthond($extdebug,1, "evaluatie_activity_id bestaat al",  $evaluatie_activity_id);
        }

        if ($status_evaluatie != $evaluatie_activity_status_name) {
            wachthond($extdebug,1, "########################################################################");
            wachthond($extdebug,1, "### EVALUATIE [PRE] 5.4 UPDATE ACTIVITY EVALUATIE: V", "WANT NIEUWE STATUS: $status_evaluatie");
            wachthond($extdebug,1, "########################################################################");
            $evaluatie_activity_update = 1;
        } else {
            wachthond($extdebug,1, "########################################################################");
            wachthond($extdebug,1, "### EVALUATIE [PRE] 5.4 UPDATE ACTIVITY EVALUATIE: X", "GEEN NIEUWE STATUS: $status_evaluatie");
            wachthond($extdebug,1, "########################################################################");
            $evaluatie_activity_update = 1;
        }

        if ($evaluatie_activity_id AND $evaluatie_activity_update == 1) {
//      if ($evaluatie_activity_id AND $eventkamp_event_einde) {

            $params_activity_evaluatie_update = [
                'checkPermissions' => FALSE,
                'debug' => $apidebug,
                'where' => [
                    ['id',          '=', $evaluatie_activity_id],
                ],
                'values' => [
                    'source_contact_id'         => 1,
                    'target_contact_id'         => $contact_id,
                    'activity_date_time'        => $evaluatie_activity_new_date,
                    'subject'                   => 'Evaluatie '. $ditevent_part_kampkort_low. ' door '. $displayname,
                    'status_id:name'            => $status_evaluatie,

                    'ACT_ALG.actcontact_naam'   => $displayname,
                    'ACT_ALG.actcontact_cid'    => $contact_id,
                    'ACT_ALG.actcontact_pid'    => $ditevent_part_id,
                    'ACT_ALG.actcontact_eid'    => $ditevent_part_eventid,

                    'ACT_ALG.kamptype_nr'       => $ditevent_part_kamptype_nr,
                    'ACT_ALG.kampnaam'          => $ditevent_part_kampkort_cap,
                    'ACT_ALG.kampkort'          => $ditevent_part_kampkort_low,
                    'ACT_ALG.kampfunctie'       => $ditevent_part_functie,
                    'ACT_ALG.kamprol'           => $ditevent_part_rol,

                    'ACT_ALG.kampstart'         => $ditevent_part_kampstart,
                    'ACT_ALG.kampeinde'         => $ditevent_part_kampeinde,
                    'ACT_ALG.kampjaar'          => $ditevent_part_kampjaar,
                    'ACT_ALG.modified'          => $today_datetime,
                    'ACT_ALG.activity_id'       => $evaluatie_activity_id,
                    'ACT_ALG.prioriteit:label'  => 'Normaal',
                    'ACT_ALG.afgerond'          => $ditevent_eval_datum,
                ],
            ];

            if (is_numeric($scores_eval_low)) { $scores_low = $scores_eval_low              + $evaluatie_scores_deel_low    + $evaluatie_scores_leid_low;   }
            if (is_numeric($scores_deel_low)) { $scores_low = $evaluatie_scores_eval_low    + $scores_deel_low              + $evaluatie_scores_leid_low;   }
            if (is_numeric($scores_leid_low)) { $scores_low = $evaluatie_scores_eval_low    + $evaluatie_scores_deel_low    + $scores_leid_low;             }

            if (is_numeric($scores_eval_low)) { wachthond($extdebug,3, "### EVAL_LOW", $scores_eval_low);   }
            if (is_numeric($scores_deel_low)) { wachthond($extdebug,3, "### DEEL_LOW", $scores_deel_low);   }
            if (is_numeric($scores_leid_low)) { wachthond($extdebug,3, "### LEID_LOW", $scores_leid_low);   }

            if (is_numeric($scores_eval_low)) { $params_activity_evaluatie_update['values']['ACT_EVAL.scores_eval_low']     = $scores_eval_low; }
            if (is_numeric($scores_deel_low)) { $params_activity_evaluatie_update['values']['ACT_EVAL.scores_deel_low']     = $scores_deel_low; }
            if (is_numeric($scores_leid_low)) { $params_activity_evaluatie_update['values']['ACT_EVAL.scores_leid_low']     = $scores_leid_low; }
            if (is_numeric($scores_low))      { $params_activity_evaluatie_update['values']['ACT_EVAL.scores_low']          = $scores_low;      }

            wachthond($extdebug,3, "evaluatie_scores_eval_low",     $evaluatie_scores_eval_low);
            wachthond($extdebug,3, "evaluatie_scores_deel_low",     $evaluatie_scores_deel_low);
            wachthond($extdebug,3, "evaluatie_scores_leid_low",     $evaluatie_scores_leid_low);

            wachthond($extdebug,3, "scores_eval_low",               $scores_eval_low);
            wachthond($extdebug,3, "scores_deel_low",               $scores_deel_low);
            wachthond($extdebug,3, "scores_leid_low",               $scores_leid_low);

            wachthond($extdebug,3, "scores_low",                    $scores_low);

            if (is_numeric($scores_eval_top)) { $scores_top = $scores_eval_top              + $evaluatie_scores_deel_top    + $evaluatie_scores_leid_top;   }
            if (is_numeric($scores_deel_top)) { $scores_top = $evaluatie_scores_eval_top    + $scores_deel_top              + $evaluatie_scores_leid_top;   }
            if (is_numeric($scores_leid_top)) { $scores_top = $evaluatie_scores_eval_top    + $evaluatie_scores_deel_top    + $scores_leid_top;             }

            if (is_numeric($scores_eval_top)) { wachthond($extdebug,3, "### EVAL_top", $scores_eval_top);   }
            if (is_numeric($scores_deel_top)) { wachthond($extdebug,3, "### DEEL_top", $scores_deel_top);   }
            if (is_numeric($scores_leid_top)) { wachthond($extdebug,3, "### LEID_top", $scores_leid_top);   }

            if (is_numeric($scores_eval_top)) { $params_activity_evaluatie_update['values']['ACT_EVAL.scores_eval_top']     = $scores_eval_top; }
            if (is_numeric($scores_deel_top)) { $params_activity_evaluatie_update['values']['ACT_EVAL.scores_deel_top']     = $scores_deel_top; }
            if (is_numeric($scores_leid_top)) { $params_activity_evaluatie_update['values']['ACT_EVAL.scores_leid_top']     = $scores_leid_top; }
            if (is_numeric($scores_top))      { $params_activity_evaluatie_update['values']['ACT_EVAL.scores_top']          = $scores_top;      }

            wachthond($extdebug,3, "evaluatie_scores_eval_top",     $evaluatie_scores_eval_top);
            wachthond($extdebug,3, "evaluatie_scores_deel_top",     $evaluatie_scores_deel_top);
            wachthond($extdebug,3, "evaluatie_scores_leid_top",     $evaluatie_scores_leid_top);

            wachthond($extdebug,3, "scores_eval_top",               $scores_eval_top);
            wachthond($extdebug,3, "scores_deel_top",               $scores_deel_top);
            wachthond($extdebug,3, "scores_leid_top",               $scores_leid_top);

            wachthond($extdebug,3, "scores_top",                    $scores_top);

            if (is_numeric($val_eval_terugblik)) { $params_activity_evaluatie_update['values']['ACT_EVAL.score_terugblik']  = $val_eval_terugblik;  }
            if (is_numeric($val_eval_aanrader))  { $params_activity_evaluatie_update['values']['ACT_EVAL.score_aanrader']   = $val_eval_aanrader;   }

            wachthond($extdebug,3, 'params_activity_evaluatie_update',              $params_activity_evaluatie_update);
            $result_activity_evaluatie_update = civicrm_api4('Activity', 'update',  $params_activity_evaluatie_update);
            wachthond($extdebug,2, "params_activity_evaluatie_update", "EXECUTED");
            wachthond($extdebug,9, 'result_activity_evaluatie_update RESULT',       $result_activity_evaluatie_update);
        }

        wachthond($extdebug,1, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] 5.X EINDE UPDATE ACTIVITIES",        "[$displayname]");
        wachthond($extdebug,1, "########################################################################");

        wachthond($extdebug,2, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] 6.1 UPDATE PART",                    "[$displayname]");
        wachthond($extdebug,2, "########################################################################");

        // =========================================================
        // START DIRTY CHECK & RECURSIE-STOP
        // =========================================================

        // 1. Haal de huidige score op uit de database
        $current_participant = civicrm_api4('Participant', 'get', [
            'checkPermissions' => FALSE,
            'select' => ['PART_INTERN.Scores_uitstekend', 'contact_id.display_name'],
            'where' => [['id', '=', $entityID]],
        ])->first();

        $old_score   = $current_participant['PART_INTERN.Scores_uitstekend']    ?? NULL;
        $displayname = $current_participant['contact_id.display_name']          ?? "Onbekend";

        // 2. De beslissing: Moeten we echt schrijven?
        if ($exteval == 1 && $scores_top > 0) {
            
            if ($old_score != $scores_top) {
                
                // ACTIVEER RECURSIE STOP (vlag zetten)
                $processing_evaluatie_pre[$entityID] = TRUE;

                $params_part_update = [
                    'checkPermissions' => FALSE,
                    'where' => [['id', '=', $entityID]],
                    'values' => [
                        'PART_INTERN.Scores_uitstekend' => $scores_top,
                    ],
                ];

                watchdog('civicrm_timing', base_microtimer("EXECUTE Evaluatie Update voor $displayname (Nieuwe score: $scores_top)"), NULL, WATCHDOG_DEBUG);
                
                civicrm_api4('Participant', 'update', $params_part_update);
                
                // VRIJGAVEN (vlag verwijderen)
                unset($processing_evaluatie_pre[$entityID]);
                
            } else {
                // TIJDSWINST BEVESTIGD
                watchdog('civicrm_timing', base_microtimer("SKIP Evaluatie Update (Score ongewijzigd: $old_score) voor $displayname"), NULL, WATCHDOG_DEBUG);
            }
        }
        
        // =========================================================
        // EINDE DIRTY CHECK
        // =========================================================

        wachthond($extdebug,1, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] EINDE",                              "[$displayname]");
        wachthond($extdebug,1, "########################################################################");        

    } else {

        wachthond($extdebug,3, 'profilepartevalall',            $profilepartevalall);
        wachthond($extdebug,3, 'ditjaareventdeelyes',           $ditjaareventdeelyes);
        wachthond($extdebug,3, 'ditjaareventleidyes',           $ditjaareventleidyes);

        wachthond($extdebug,1, "########################################################################");
        wachthond($extdebug,1, "### EVALUATIE [PRE] 5.X SKIPPED UPDATE ACTIVITIES",      "[$displayname]");
        wachthond($extdebug,1, "########################################################################");
    }

    #########################################################################
    ### EVALUATIE PART [PRE]                                          [EINDE]
    #########################################################################

    watchdog('civicrm_timing', base_microtimer("EINDE EVALUATIE [PRE] voor entityID: $entityID"), NULL, WATCHDOG_DEBUG);

}