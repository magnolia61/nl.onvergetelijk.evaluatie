<?php

/**
 * =======================================================================================
 * FUNCTIE-INDEX: evaluatie.php
 * =======================================================================================
 *   evaluatie_get_field_map_eval()    De centrale mapping voor de evaluatievelden van deelnemers/ouders (groep 146).
 *   evaluatie_get_field_map_leid()    De centrale mapping voor de evaluatievelden van leiding (groep 168).
 *   evaluatie_civicrm_config()        Implements hook_civicrm_config().
 *   evaluatie_civicrm_install()       Implements hook_civicrm_install().
 *   evaluatie_civicrm_enable()        Implements hook_civicrm_enable().
 *   evaluatie_civicrm_customPre()     De "Portier" voor de Evaluatie-module. Vangt formulierdata op,
 *                                     extraheert velden en stuurt de rekenmachine aan.
 *   evaluatie_civicrm_configure()     De "Rekenmachine" voor Evaluatie. Berekent scores, beheert de
 *                                     Evaluatie-activiteit en werkt de deelnemer bij.
 * =======================================================================================
 */

require_once 'evaluatie.civix.php';

use CRM_Evaluatie_ExtensionUtil as E;

/**
 * =======================================================================================
 * COLOFON: evaluatie_get_field_map_eval (SINGLE SOURCE OF TRUTH)
 * =======================================================================================
 * @description     Veldmapping voor groep 146 (deelnemer/ouder evaluatieformulier).
 * @return array    Associatieve array: ['column_naam_ID' => 'EVAL.sleutel'].
 * =======================================================================================
 */
function evaluatie_get_field_map_eval(): array {
    return [
        'datum_evaluatie_1076'      => 'EVAL.datum_evaluatie',
        'terugblik_score_2149'      => 'EVAL.terugblik',
        'kampthema_score_2151'      => 'EVAL.kampthema',
        'inhoud_score_2153'         => 'EVAL.inhoud',
        'actief_score_2155'         => 'EVAL.actief',
        'vrijetijd_score_2157'      => 'EVAL.vrijetijd',
        'kampinfo_score_2158'       => 'EVAL.kampinfo',
        'etendrinken_score_2161'    => 'EVAL.eten',
        'brengenhalen_score_2163'   => 'EVAL.brengen',
        'slapen_score_2165'         => 'EVAL.slapen',
        'corvee_score_2167'         => 'EVAL.corvee',
        'aanraden_score_2170'       => 'EVAL.aanrader',
    ];
}

/**
 * =======================================================================================
 * COLOFON: evaluatie_get_field_map_leid (SINGLE SOURCE OF TRUTH)
 * =======================================================================================
 * @description     Veldmapping voor groep 168 (leiding evaluatieformulier).
 * @return array    Associatieve array: ['column_naam_ID' => 'LEID.sleutel'].
 * =======================================================================================
 */
function evaluatie_get_field_map_leid(): array {
    return [
        'team_score_2177'                   => 'LEID.team',
        'geestelijk_score_2179'             => 'LEID.geestelijk',
        'praktisch_score_2181'              => 'LEID.praktisch',
        'voorbereiding_score_2184'          => 'LEID.voorbereiding',
        'veiligheid_sociaal_score_2186'     => 'LEID.veiligsociaal',
        'veiligheid_praktisch_score_2188'   => 'LEID.veiligfysiek',
    ];
}

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

/**
 * =======================================================================================
 * COLOFON: evaluatie_civicrm_customPre
 * =======================================================================================
 * @description     De "Portier" voor de Evaluatie-module. Vangt formulierdata op,
 * extraheert velden via base-helpers en stuurt de rekenmachine aan.
 * Ondersteunt drie groepen: 146 (eval), 168 (leid), 209 (deel).
 * =======================================================================================
 */
function evaluatie_civicrm_customPre(string $op, int $groupID, int $entityID, array &$params): void {

    // --- STAP 0: PREVENTIE VAN DUBBELE UITVOERING ---
    static $processing_evaluatie_pre = FALSE;
    if ($processing_evaluatie_pre || !in_array($op, ['create', 'edit'])) {
        return;
    }

    $extdebug = 'evaluatie.custompre'; // Kanaal voor centrale debug-config; niveau wordt opgezocht in ozk.debug.config.php
    $group_eval  = [146];
    $group_leid  = [168];
    $group_deel  = [209];
    $group_all   = array_merge($group_eval, $group_leid, $group_deel);

    // Vroege afbreking als we niet in een evaluatiegroep zitten
    if (!in_array($groupID, $group_all)) {
        return;
    }

    wachthond($extdebug, 2, "########################################################################");
    wachthond($extdebug, 1, "### EVALUATIE [PRE] 1.0 EXTRACTIE & MAPPING",                     "[MAP]");
    wachthond($extdebug, 2, "########################################################################");

    // --- STAP 1.0: EXTRACTIE VIA BASE HELPER ---
    if (in_array($groupID, $group_eval)) {
        $name_map = evaluatie_get_field_map_eval();
    } elseif (in_array($groupID, $group_leid)) {
        $name_map = evaluatie_get_field_map_leid();
    } else {
        $name_map = []; // Groep 209 (deel): geen eigen velden, alleen activiteit-update triggeren
    }

    $field_ids   = !empty($name_map) ? base_get_field_ids($name_map) : [];
    $params_eval = !empty($name_map) ? base_extract_from_params($params, $name_map) : [];

    // Groep 209 mag door zonder eigen velden (activiteitstatus wordt bijgewerkt)
    if (!in_array($groupID, $group_deel) && empty($params_eval)) {
        return;
    }

    $processing_evaluatie_pre = TRUE;

    wachthond($extdebug, 2, "########################################################################");
    wachthond($extdebug, 1, "### EVALUATIE [PRE] 2.0 START VERWERKING",            "[ID: $entityID]");
    wachthond($extdebug, 2, "########################################################################");

    watchdog('civicrm_timing', base_microtimer("START EVALUATIE [PRE] voor entityID: $entityID (GroupID: $groupID)"), NULL, WATCHDOG_DEBUG);

    // --- STAP 2.0: LOGICA UITBESTEDEN AAN DE REKENMACHINE ---
    // Context 'hook': configure retourneert array voor injectie i.p.v. zelf te schrijven.
    $data_to_inject = evaluatie_civicrm_configure($entityID, $groupID, $params_eval);

    wachthond($extdebug, 2, "########################################################################");
    wachthond($extdebug, 1, "### EVALUATIE [PRE] 3.0 INJECTIE IN FORMULIER",           "[$entityID]");
    wachthond($extdebug, 2, "########################################################################");

    // --- STAP 3.0: RESULTATEN TERUGSTOPPEN IN HET FORMULIER (bijv. genormaliseerde datum) ---
    if (!empty($data_to_inject) && !empty($field_ids)) {
        $success_list = base_inject_params($params, $data_to_inject, $field_ids, $entityID, "EVALUATIE", $extdebug);

        if (!empty($success_list)) {
            wachthond($extdebug, 1, "EVALUATIE [PRE] SUCCES: Injectie voltooid", $success_list);
        }
    }

    watchdog('civicrm_timing', base_microtimer("EINDE EVALUATIE [PRE] voor entityID: $entityID"), NULL, WATCHDOG_DEBUG);

    wachthond($extdebug, 2, "########################################################################");
    wachthond($extdebug, 1, "### EVALUATIE [PRE] EINDE VERWERKING",                    "[SUCCESS]");
    wachthond($extdebug, 2, "########################################################################");

    $processing_evaluatie_pre = FALSE;
}

/**
 * =======================================================================================
 * COLOFON: evaluatie_civicrm_configure
 * =======================================================================================
 * @description     De "Rekenmachine" voor Evaluatie. Berekent top/tip-scores per formuliertype,
 * beheert de Evaluatie-activiteit (aanmaken/bijwerken) en werkt de deelnemer bij.
 * @param int    $participant_id  Het CiviCRM Participant-ID.
 * @param int    $groupID         Het custom-groep-ID (146 = eval, 168 = leid, 209 = deel).
 * @param array  $params_eval     Formuliervelden, gekeyed op intern EVAL.*- of LEID.*-naam.
 * @return array                  Te injecteren veldwaarden (bijv. genormaliseerde evaluatiedatum).
 * =======================================================================================
 */
function evaluatie_civicrm_configure(int $participant_id, int $groupID, array $params_eval): array {

    // --- RECURSIE BEVEILIGING ---
    static $processing_evaluatie_configure = FALSE;
    if ($processing_evaluatie_configure || empty($participant_id)) {
        return [];
    }

    $extdebug = 'evaluatie.configure'; // Kanaal voor centrale debug-config; niveau wordt opgezocht in ozk.debug.config.php
    $apidebug  = FALSE;
    $processing_evaluatie_configure = TRUE;

    // --- TIMING START ---
    watchdog('civicrm_timing', base_microtimer("START EVALUATIE CONFIGURE voor entityID: $entityID"), NULL, WATCHDOG_DEBUG);

    $data_to_inject = [];
    $today_datetime = date("Y-m-d H:i:s");

    wachthond($extdebug, 2, "########################################################################");
    wachthond($extdebug, 1, "### EVALUATIE CONFIGURE - 1.0 PARTICIPANT DATA INLADEN",  "[DATABASE]");
    wachthond($extdebug, 2, "########################################################################");

    // 1. Participant info ophalen via base-helper (bevat kampstart, kampeinde, fiscalyear, etc.)
    $array_partditevent = base_pid2part($participant_id);
    wachthond($extdebug, 3, 'array_partditevent', $array_partditevent);

    watchdog('civicrm_timing', base_microtimer("STAP 1: base_pid2part voltooid"), NULL, WATCHDOG_DEBUG);

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

    wachthond($extdebug, 3, 'contact_id',   $contact_id);
    wachthond($extdebug, 3, 'displayname',  $displayname);
    wachthond($extdebug, 3, 'kampstart',    $ditevent_part_kampstart);
    wachthond($extdebug, 3, 'kampeinde',    $ditevent_part_kampeinde);
    wachthond($extdebug, 3, 'kampfunctie',  $ditevent_part_functie);

    wachthond($extdebug, 2, "########################################################################");
    wachthond($extdebug, 1, "### EVALUATIE CONFIGURE - 2.0 DITJAAR CHECK",               "[FILTER]");
    wachthond($extdebug, 2, "########################################################################");

    // 2. Controleer of dit kamp in het lopende boekjaar valt.
    $ditjaarpart         = (infiscalyear($ditevent_part_kampstart, $today_datetime) == 1) ? 1 : 0;
    $ditjaareventdeelyes = 0;
    $ditjaareventleidyes = 0;

    if ($ditjaarpart == 1) {
        $array_allpart_ditjaar = base_find_allpart($contact_id, $today_datetime);
        wachthond($extdebug, 3, 'array_allpart_ditjaar', $array_allpart_ditjaar);

        $ditjaar_pos_leid_count   = $array_allpart_ditjaar['result_allpart_pos_leid_count']   ?? 0;
        $ditjaar_pos_leid_part_id = $array_allpart_ditjaar['result_allpart_pos_leid_part_id'] ?? NULL;
        $ditjaar_pos_deel_count   = $array_allpart_ditjaar['result_allpart_pos_deel_count']   ?? 0;
        $ditjaar_pos_deel_part_id = $array_allpart_ditjaar['result_allpart_pos_deel_part_id'] ?? NULL;

        if ($ditjaar_pos_deel_count == 1 && $participant_id == $ditjaar_pos_deel_part_id) {
            $ditjaareventdeelyes = 1;
        }
        if ($ditjaar_pos_leid_count == 1 && $participant_id == $ditjaar_pos_leid_part_id) {
            $ditjaareventleidyes = 1;
        }
    }

    wachthond($extdebug, 3, 'ditjaarpart',         $ditjaarpart);
    wachthond($extdebug, 3, 'ditjaareventdeelyes', $ditjaareventdeelyes);
    wachthond($extdebug, 3, 'ditjaareventleidyes', $ditjaareventleidyes);

    watchdog('civicrm_timing', base_microtimer("STAP 2: base_find_allpart voltooid"), NULL, WATCHDOG_DEBUG);

    wachthond($extdebug, 2, "########################################################################");
    wachthond($extdebug, 1, "### EVALUATIE CONFIGURE - 3.0 SCORE BEREKENING",           "[SCORES]");
    wachthond($extdebug, 2, "########################################################################");

    $scores_eval_low  = NULL;
    $scores_eval_top  = NULL;
    $scores_leid_low  = NULL;
    $scores_leid_top  = NULL;
    $ditevent_eval_datum = NULL;

    // 3a. DEELNEMER/OUDER EVALUATIE (groep 146)
    if ($groupID === 146 && !empty($params_eval)) {

        wachthond($extdebug, 2, "########################################################################");
        wachthond($extdebug, 1, "### EVALUATIE CONFIGURE - 3.1 EVAL SCORES",               "[GROUP 146]");
        wachthond($extdebug, 2, "########################################################################");

        $val_datum      = $params_eval['EVAL.datum_evaluatie'] ?? NULL;
        $val_terugblik  = $params_eval['EVAL.terugblik']       ?? NULL;
        $val_kampthema  = $params_eval['EVAL.kampthema']       ?? NULL;
        $val_inhoud     = $params_eval['EVAL.inhoud']          ?? NULL;
        $val_actief     = $params_eval['EVAL.actief']          ?? NULL;
        $val_vrijetijd  = $params_eval['EVAL.vrijetijd']       ?? NULL;
        $val_kampinfo   = $params_eval['EVAL.kampinfo']        ?? NULL;
        $val_eten       = $params_eval['EVAL.eten']            ?? NULL;
        $val_brengen    = $params_eval['EVAL.brengen']         ?? NULL;
        $val_slapen     = $params_eval['EVAL.slapen']          ?? NULL;
        $val_corvee     = $params_eval['EVAL.corvee']          ?? NULL;
        $val_aanrader   = $params_eval['EVAL.aanrader']        ?? NULL;

        // Datum normaliseren: als tijd 00:00:00 is (puur datum gekozen), vul dan huidige tijd in.
        if ($val_datum) {
            $ts        = strtotime($val_datum);
            $datum     = date('Y-m-d', $ts);
            $tijd      = date('H:i:s', $ts);
            $vandaag   = date('Y-m-d');

            $new_datum = ($datum === $vandaag && $tijd === '00:00:00')
                ? date('Y-m-d H:i:s')  // vervang stiptijdstip door nu
                : date('Y-m-d H:i:s', $ts);

            if ($new_datum !== date('Y-m-d H:i:s', $ts)) {
                $data_to_inject['EVAL.datum_evaluatie'] = date('YmdHis', strtotime($new_datum));
                wachthond($extdebug, 1, 'datum genormaliseerd', $new_datum);
            }

            $ditevent_eval_datum = $new_datum;
        }

        wachthond($extdebug, 3, 'val_terugblik',  $val_terugblik);
        wachthond($extdebug, 3, 'val_kampthema',  $val_kampthema);
        wachthond($extdebug, 3, 'val_inhoud',     $val_inhoud);
        wachthond($extdebug, 3, 'val_actief',     $val_actief);
        wachthond($extdebug, 3, 'val_slapen',     $val_slapen);

        // LOW scores (1–6 = onvoldoende, of slechte optiewaarde)
        $score_terugblik_low = in_array($val_terugblik, [1, 2, 3, 4, 5, 6])                        ? 1 : NULL;
        $score_kampthema_low = in_array($val_kampthema, [1, 2, 3, 4, 5, 6])                        ? 1 : NULL;
        $score_inhoud_low    = in_array($val_inhoud,    [1, 2, 3, 4, 5, 6])                        ? 1 : NULL;
        $score_actief_low    = in_array($val_actief,    [1, 2, 3, 4, 5, 6])                        ? 1 : NULL;
        $score_eten_low      = in_array($val_eten,      [1, 2, 3, 4, 5, 6])                        ? 1 : NULL;
        $score_slapen_low    = in_array($val_slapen,    ['slecht', 'nietzogoed', 'kanbeter'])       ? 1 : NULL;
        $score_brengen_low   = in_array($val_brengen,   ['slecht', 'nietzogoed', 'kanbeter'])       ? 1 : NULL;
        $score_kampinfo_low  = in_array($val_kampinfo,  ['slecht', 'nietzogoed', 'kanbeter'])       ? 1 : NULL;

        // TOP scores (8–10 = uitstekend, of positieve optiewaarde)
        $score_terugblik_top = in_array($val_terugblik, [8, 9, 10])                                ? 1 : NULL;
        $score_kampthema_top = in_array($val_kampthema, [8, 9, 10])                                ? 1 : NULL;
        $score_inhoud_top    = in_array($val_inhoud,    [8, 9, 10])                                ? 1 : NULL;
        $score_actief_top    = in_array($val_actief,    [8, 9, 10])                                ? 1 : NULL;
        $score_eten_top      = in_array($val_eten,      [8, 9, 10])                                ? 1 : NULL;
        $score_slapen_top    = in_array($val_slapen,    ['zeergoed', 'uitstekend'])                 ? 1 : NULL;
        $score_brengen_top   = in_array($val_brengen,   ['zeergoed', 'uitstekend'])                 ? 1 : NULL;
        $score_kampinfo_top  = in_array($val_kampinfo,  ['zeergoed', 'uitstekend'])                 ? 1 : NULL;

        $scores_eval_low = (int)($score_terugblik_low ?? 0)
                         + (int)($score_kampthema_low ?? 0)
                         + (int)($score_inhoud_low    ?? 0)
                         + (int)($score_actief_low    ?? 0)
                         + (int)($score_eten_low      ?? 0)
                         + (int)($score_slapen_low    ?? 0)
                         + (int)($score_brengen_low   ?? 0)
                         + (int)($score_kampinfo_low  ?? 0);

        $scores_eval_top = (int)($score_terugblik_top ?? 0)
                         + (int)($score_kampthema_top ?? 0)
                         + (int)($score_inhoud_top    ?? 0)
                         + (int)($score_actief_top    ?? 0)
                         + (int)($score_eten_top      ?? 0)
                         + (int)($score_slapen_top    ?? 0)
                         + (int)($score_brengen_top   ?? 0)
                         + (int)($score_kampinfo_top  ?? 0);

        wachthond($extdebug, 1, 'scores_eval_low', $scores_eval_low);
        wachthond($extdebug, 1, 'scores_eval_top', $scores_eval_top);

        // Deelnemer intern bijwerken met nieuwe scores
        if ($ditjaarpart == 1) {
            try {
                $params_part_intern = [
                    'checkPermissions' => FALSE,
                    'debug'            => $apidebug,
                    'where'            => [['id', '=', $participant_id]],
                    'values'           => [
                        'PART_EVALUATIE_INTERN.Scores_onvoldoende' => $scores_eval_low,
                        'PART_EVALUATIE_INTERN.Scores_uitstekend'  => $scores_eval_top,
                    ],
                ];
                wachthond($extdebug, 7, 'params_part_intern', $params_part_intern);
                $result_part_intern = civicrm_api4('Participant', 'update', $params_part_intern);
                wachthond($extdebug, 9, 'result_part_intern', $result_part_intern);
            } catch (\Exception $e) {
                wachthond(1, 1, "EVALUATIE PART_INTERN UPDATE ERROR: " . $e->getMessage());
            }
        }
    }

    // 3b. LEIDING EVALUATIE (groep 168)
    if ($groupID === 168 && !empty($params_eval)) {

        wachthond($extdebug, 2, "########################################################################");
        wachthond($extdebug, 1, "### EVALUATIE CONFIGURE - 3.2 LEID SCORES",               "[GROUP 168]");
        wachthond($extdebug, 2, "########################################################################");

        $val_geestelijk   = $params_eval['LEID.geestelijk']   ?? NULL;
        $val_praktisch    = $params_eval['LEID.praktisch']     ?? NULL;
        $val_team         = $params_eval['LEID.team']          ?? NULL;
        $val_voorbereiding = $params_eval['LEID.voorbereiding'] ?? NULL;
        $val_veiligsociaal = $params_eval['LEID.veiligsociaal'] ?? NULL;
        $val_veiligfysiek  = $params_eval['LEID.veiligfysiek']  ?? NULL;

        wachthond($extdebug, 3, 'val_geestelijk',    $val_geestelijk);
        wachthond($extdebug, 3, 'val_praktisch',     $val_praktisch);
        wachthond($extdebug, 3, 'val_team',          $val_team);
        wachthond($extdebug, 3, 'val_voorbereiding', $val_voorbereiding);

        // LOW scores
        $score_geestelijk_low   = in_array($val_geestelijk,    [1, 2, 3, 4, 5, 6]) ? 1 : NULL;
        $score_praktisch_low    = in_array($val_praktisch,     [1, 2, 3, 4, 5, 6]) ? 1 : NULL;
        $score_team_low         = in_array($val_team,          [1, 2, 3, 4, 5, 6]) ? 1 : NULL;
        $score_prep_low         = in_array($val_voorbereiding, [1, 2, 3, 4, 5, 6]) ? 1 : NULL;
        $score_veiligsociaal_low = in_array($val_veiligsociaal, [1, 2, 3, 4, 5, 6]) ? 1 : NULL;
        $score_veiligfysiek_low  = in_array($val_veiligfysiek,  [1, 2, 3, 4, 5, 6]) ? 1 : NULL;

        // TOP scores
        $score_geestelijk_top   = in_array($val_geestelijk,    [8, 9, 10]) ? 1 : NULL;
        $score_praktisch_top    = in_array($val_praktisch,     [8, 9, 10]) ? 1 : NULL;
        $score_team_top         = in_array($val_team,          [8, 9, 10]) ? 1 : NULL;
        $score_prep_top         = in_array($val_voorbereiding, [8, 9, 10]) ? 1 : NULL;
        $score_veiligsociaal_top = in_array($val_veiligsociaal, [8, 9, 10]) ? 1 : NULL;
        $score_veiligfysiek_top  = in_array($val_veiligfysiek,  [8, 9, 10]) ? 1 : NULL;

        $scores_leid_low = (int)($score_geestelijk_low    ?? 0)
                         + (int)($score_praktisch_low     ?? 0)
                         + (int)($score_team_low          ?? 0)
                         + (int)($score_prep_low          ?? 0)
                         + (int)($score_veiligsociaal_low ?? 0)
                         + (int)($score_veiligfysiek_low  ?? 0);

        $scores_leid_top = (int)($score_geestelijk_top    ?? 0)
                         + (int)($score_praktisch_top     ?? 0)
                         + (int)($score_team_top          ?? 0)
                         + (int)($score_prep_top          ?? 0)
                         + (int)($score_veiligsociaal_top ?? 0)
                         + (int)($score_veiligfysiek_top  ?? 0);

        wachthond($extdebug, 1, 'scores_leid_low', $scores_leid_low);
        wachthond($extdebug, 1, 'scores_leid_top', $scores_leid_top);
    }

    watchdog('civicrm_timing', base_microtimer("STAP 3: Score berekening voltooid"), NULL, WATCHDOG_DEBUG);

    wachthond($extdebug, 2, "########################################################################");
    wachthond($extdebug, 1, "### EVALUATIE CONFIGURE - 4.0 ACTIVITEIT BEHEREN",        "[ACTIVITY]");
    wachthond($extdebug, 2, "########################################################################");

    // 4. Evaluatie-activiteit ophalen/aanmaken/bijwerken (alleen als participant dit jaar meedoet)
    if ($ditjaarpart == 1 && ($ditjaareventdeelyes == 1 || $ditjaareventleidyes == 1)) {

        // 4.1 GET activiteit
        $params_activity_get = [
            'checkPermissions' => FALSE,
            'debug'            => $apidebug,
            'select'           => [
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
                'ACT_EVAL.scores_eval_low',
                'ACT_EVAL.scores_deel_low',
                'ACT_EVAL.scores_leid_low',
                'ACT_EVAL.scores_eval_top',
                'ACT_EVAL.scores_deel_top',
                'ACT_EVAL.scores_leid_top',
            ],
            'join'  => [
                ['ActivityContact AS activity_contact', 'INNER'],
            ],
            'where' => [
                ['activity_contact.contact_id',     '=',  $contact_id],
                ['activity_contact.record_type_id', '=',  3],
                ['activity_type_id:name',           '=',  'Evaluatie'],
                ['activity_date_time',              '>=', $ditevent_fiscalyear_start],
                ['activity_date_time',              '<=', $ditevent_fiscalyear_einde],
            ],
            'orderBy' => ['id' => 'ASC'],
            'limit'   => 1,
        ];

        wachthond($extdebug, 7, 'params_activity_get', $params_activity_get);
        $result_evaluatie_get       = civicrm_api4('Activity', 'get', $params_activity_get);
        $result_evaluatie_get_count = $result_evaluatie_get->countMatched();
        wachthond($extdebug, 9, 'result_evaluatie_get', $result_evaluatie_get);

        $evaluatie_activity_id          = NULL;
        $evaluatie_activity_status_id   = NULL;
        $evaluatie_activity_status_name = NULL;
        $evaluatie_activity_datum       = NULL;
        $evaluatie_scores_eval_low      = NULL;
        $evaluatie_scores_deel_low      = NULL;
        $evaluatie_scores_leid_low      = NULL;
        $evaluatie_scores_eval_top      = NULL;
        $evaluatie_scores_deel_top      = NULL;
        $evaluatie_scores_leid_top      = NULL;

        if ($result_evaluatie_get_count >= 1) {
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

            wachthond($extdebug, 1, 'evaluatie_activity_id',          $evaluatie_activity_id);
            wachthond($extdebug, 1, 'evaluatie_activity_status_name', $evaluatie_activity_status_name);
        } else {
            wachthond($extdebug, 1, 'evaluatie_activity_id', 'geen activiteit gevonden');
        }

        // 4.2 BEPAAL STATUS en NIEUWE DATUM
        $eventendplus01     = strtotime('+1 day',  strtotime($ditevent_part_kampeinde));
        $eventendplus60     = strtotime('+60 days', strtotime($ditevent_part_kampeinde));
        $eventendplus01date = date('Y-m-d H:i', $eventendplus01);
        $eventendplus60date = date('Y-m-d H:i', $eventendplus60);

        $diffsince = date_diff(date_create($eventendplus01date), date_create($today_datetime));
        $dayssince = (int)$diffsince->format('%r%a'); // negatief als event nog niet voorbij is

        $status_evaluatie = 'Scheduled';
        if ($dayssince >= 0)  { $status_evaluatie = 'Pending';      }
        if ($dayssince >= 7)  { $status_evaluatie = 'Left Message'; }
        if ($dayssince >= 21) { $status_evaluatie = 'Unreachable';  }
        if ($dayssince >= 35) { $status_evaluatie = 'No_show';      }
        if ($dayssince >= 49) { $status_evaluatie = 'Bounced';      }

        // Als evaluatiedatum is ingevuld en in het juiste boekjaar valt → Completed
        if ($ditevent_eval_datum && infiscalyear($ditevent_eval_datum, $ditevent_part_kampstart) == 1) {
            $status_evaluatie            = 'Completed';
            $evaluatie_activity_new_date = $ditevent_eval_datum;
            wachthond($extdebug, 1, 'evaluatie ingevuld', $ditevent_eval_datum);
        } else {
            $evaluatie_activity_new_date = $eventendplus60date;
        }

        // Als de evaluatiedatum leeg is (bijv. alleen leid-formulier): status niet overschrijven
        if (empty($ditevent_eval_datum) && !empty($evaluatie_activity_status_name)) {
            $status_evaluatie = $evaluatie_activity_status_name;
        }

        wachthond($extdebug, 1, 'dayssince_evaluatieverzoek', $dayssince);
        wachthond($extdebug, 1, 'status_evaluatie',           $status_evaluatie);
        wachthond($extdebug, 1, 'new_date',                   $evaluatie_activity_new_date);

        // 4.3 CREATE activiteit (alleen als er nog geen is EN het kamp voorbij is)
        if (empty($evaluatie_activity_id)
            && infiscalyear($ditevent_part_kampeinde, $today_datetime) == 1
            && $ditevent_part_kampeinde <= $today_datetime
        ) {
            $params_activity_create = [
                'checkPermissions' => FALSE,
                'debug'            => $apidebug,
                'values'           => [
                    'source_contact_id'         => 1,
                    'target_contact_id'         => $contact_id,
                    'activity_type_id:name'     => 'Evaluatie',
                    'activity_date_time'        => $evaluatie_activity_new_date,
                    'subject'                   => 'Evaluatie ' . $ditevent_part_kampkort_low . ' door ' . $displayname,
                    'status_id:name'            => 'Scheduled',

                    'ACT_ALG.actcontact_naam'   => $displayname,
                    'ACT_ALG.actcontact_cid'    => $contact_id,
                    'ACT_ALG.actcontact_pid'    => $participant_id,
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

            wachthond($extdebug, 7, 'params_activity_create', $params_activity_create);
            try {
                $result_activity_create   = civicrm_api4('Activity', 'create', $params_activity_create);
                $evaluatie_activity_id    = $result_activity_create[0]['id']              ?? NULL;
                $evaluatie_activity_datum = $result_activity_create[0]['activity_date_time'] ?? NULL;
                wachthond($extdebug, 9, 'result_activity_create', $result_activity_create);
            } catch (\Exception $e) {
                wachthond(1, 1, "EVALUATIE ACTIVITY CREATE ERROR: " . $e->getMessage());
            }

        } else {
            wachthond($extdebug, 1, 'activiteit bestaat al of kamp nog niet voorbij', $evaluatie_activity_id);
        }

        // 4.4 UPDATE activiteit
        if (!empty($evaluatie_activity_id)) {

            wachthond($extdebug, 2, "########################################################################");
            wachthond($extdebug, 1, "### EVALUATIE CONFIGURE - 4.4 UPDATE ACTIVITY",        "[UPDATE]");
            wachthond($extdebug, 2, "########################################################################");

            $params_activity_update = [
                'checkPermissions' => FALSE,
                'debug'            => $apidebug,
                'where'            => [['id', '=', $evaluatie_activity_id]],
                'values'           => [
                    'source_contact_id'         => 1,
                    'target_contact_id'         => $contact_id,
                    'activity_date_time'        => $evaluatie_activity_new_date,
                    'subject'                   => 'Evaluatie ' . $ditevent_part_kampkort_low . ' door ' . $displayname,
                    'status_id:name'            => $status_evaluatie,

                    'ACT_ALG.actcontact_naam'   => $displayname,
                    'ACT_ALG.actcontact_cid'    => $contact_id,
                    'ACT_ALG.actcontact_pid'    => $participant_id,
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

            // Scores samenvoegen: nieuw berekende waarde + bestaande scores van andere groepen.
            // Per invocation is maar één groep actief, de overige komen uit de bestaande activiteit.
            $act_scores_low = [
                'eval' => is_numeric($scores_eval_low) ? $scores_eval_low : $evaluatie_scores_eval_low,
                'deel' => $evaluatie_scores_deel_low,
                'leid' => is_numeric($scores_leid_low) ? $scores_leid_low : $evaluatie_scores_leid_low,
            ];
            $act_scores_top = [
                'eval' => is_numeric($scores_eval_top) ? $scores_eval_top : $evaluatie_scores_eval_top,
                'deel' => $evaluatie_scores_deel_top,
                'leid' => is_numeric($scores_leid_top) ? $scores_leid_top : $evaluatie_scores_leid_top,
            ];

            $total_low = (int)($act_scores_low['eval'] ?? 0)
                       + (int)($act_scores_low['deel'] ?? 0)
                       + (int)($act_scores_low['leid'] ?? 0);

            $total_top = (int)($act_scores_top['eval'] ?? 0)
                       + (int)($act_scores_top['deel'] ?? 0)
                       + (int)($act_scores_top['leid'] ?? 0);

            if (is_numeric($act_scores_low['eval'])) { $params_activity_update['values']['ACT_EVAL.scores_eval_low'] = $act_scores_low['eval']; }
            if (is_numeric($act_scores_low['deel'])) { $params_activity_update['values']['ACT_EVAL.scores_deel_low'] = $act_scores_low['deel']; }
            if (is_numeric($act_scores_low['leid'])) { $params_activity_update['values']['ACT_EVAL.scores_leid_low'] = $act_scores_low['leid']; }
            if ($total_low > 0)                      { $params_activity_update['values']['ACT_EVAL.scores_low']      = $total_low;               }

            if (is_numeric($act_scores_top['eval'])) { $params_activity_update['values']['ACT_EVAL.scores_eval_top'] = $act_scores_top['eval']; }
            if (is_numeric($act_scores_top['deel'])) { $params_activity_update['values']['ACT_EVAL.scores_deel_top'] = $act_scores_top['deel']; }
            if (is_numeric($act_scores_top['leid'])) { $params_activity_update['values']['ACT_EVAL.scores_leid_top'] = $act_scores_top['leid']; }
            if ($total_top > 0)                      { $params_activity_update['values']['ACT_EVAL.scores_top']      = $total_top;               }

            // Individuele scores (terugblik en aanrader) op de activiteit
            $val_terugblik_upd = $params_eval['EVAL.terugblik'] ?? NULL;
            $val_aanrader_upd  = $params_eval['EVAL.aanrader']  ?? NULL;
            if (is_numeric($val_terugblik_upd)) { $params_activity_update['values']['ACT_EVAL.score_terugblik'] = $val_terugblik_upd; }
            if (is_numeric($val_aanrader_upd))  { $params_activity_update['values']['ACT_EVAL.score_aanrader']  = $val_aanrader_upd;  }

            wachthond($extdebug, 7, 'params_activity_update', $params_activity_update);
            try {
                $result_activity_update = civicrm_api4('Activity', 'update', $params_activity_update);
                wachthond($extdebug, 9, 'result_activity_update', $result_activity_update);
            } catch (\Exception $e) {
                wachthond(1, 1, "EVALUATIE ACTIVITY UPDATE ERROR: " . $e->getMessage());
            }
        }

        wachthond($extdebug, 2, "########################################################################");
        wachthond($extdebug, 1, "### EVALUATIE CONFIGURE - 5.0 PARTICIPANT INTERN BIJWERKEN", "[PART]");
        wachthond($extdebug, 2, "########################################################################");

        // 5. Deelnemer PART_INTERN bijwerken met totale top-score (dirty check om extra writes te voorkomen)
        $new_scores_top = is_numeric($scores_eval_top) ? $scores_eval_top : (is_numeric($scores_leid_top) ? $scores_leid_top : NULL);

        if (is_numeric($new_scores_top) && $new_scores_top > 0) {
            try {
                $current_participant = civicrm_api4('Participant', 'get', [
                    'checkPermissions' => FALSE,
                    'select'           => ['PART_INTERN.Scores_uitstekend'],
                    'where'            => [['id', '=', $participant_id]],
                    'limit'            => 1,
                ])->first();

                $old_score = $current_participant['PART_INTERN.Scores_uitstekend'] ?? NULL;

                if ($old_score != $new_scores_top) {
                    watchdog('civicrm_timing', base_microtimer("EXECUTE Evaluatie Update $displayname (Nieuwe score: $new_scores_top)"), NULL, WATCHDOG_DEBUG);
                    civicrm_api4('Participant', 'update', [
                        'checkPermissions' => FALSE,
                        'where'            => [['id', '=', $participant_id]],
                        'values'           => ['PART_INTERN.Scores_uitstekend' => $new_scores_top],
                    ]);
                } else {
                    watchdog('civicrm_timing', base_microtimer("SKIP Evaluatie Update (Score ongewijzigd: $old_score) $displayname"), NULL, WATCHDOG_DEBUG);
                }
            } catch (\Exception $e) {
                wachthond(1, 1, "EVALUATIE PART_INTERN SCORE UPDATE ERROR: " . $e->getMessage());
            }
        }
    }

    // --- TIMING EINDE ---
    watchdog('civicrm_timing', base_microtimer("EINDE EVALUATIE CONFIGURE voor entityID: $entityID"), NULL, WATCHDOG_DEBUG);

    wachthond($extdebug, 2, "########################################################################");
    wachthond($extdebug, 1, "### EVALUATIE CONFIGURE - EINDE",          "[$displayname $ditevent_part_kampkort]");
    wachthond($extdebug, 2, "########################################################################");

    $processing_evaluatie_configure = FALSE;
    return $data_to_inject;
}
