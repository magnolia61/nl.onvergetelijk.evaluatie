# nl.onvergetelijk.evaluatie

## Functionele beschrijving

De `evaluatie`-extensie verwerkt de evaluatieformulieren die na afloop van een kamp worden ingevuld voor deelnemers en begeleiders. Evaluatoren kennen scores toe op verschillende onderdelen (samenwerking, gedrag, prestaties, etc.) en `evaluatie` berekent automatisch de totaalscores, slaat de uitkomsten op en maakt of bijwerkt een evaluatieactiviteit in CiviCRM.

Voor begeleiders is er een afzonderlijke evaluatieset (LEID-scores) met eigen scorevelden. De module bewaakt of een evaluatie al bestaat en kiest dan voor een update in plaats van een nieuw aanmaken, zodat er per deelnemer per kamp altijd maximaal één evaluatierecord is.

## Afhankelijkheden

- `nl.onvergetelijk.base`

---

## Technische documentatie

### Kernfuncties

- `evaluatie_get_field_map_eval()` — field map voor deelnemers-evaluatievelden (custom group 146)
- `evaluatie_get_field_map_leid()` — field map voor leidingsevaluatievelden (custom group 168)
- `evaluatie_civicrm_customPre($op, $groupID, $entityID, &$params)` — pre-hook: filtert op evaluatieveldgroepen (146, 168), extraheert de ingevulde scores en roept `evaluatie_civicrm_configure` aan
- `evaluatie_civicrm_configure($participant_id, $groupID, $params_eval)` — de hoofdmotor:
  1. Participantdata inladen
  2. Ditjaar-check: alleen verwerken als het event dit jaar is
  3. Scoreberekening voor deelnemer (EVAL scores, custom group 146) of begeleider (LEID scores, custom group 168)
  4. Activiteitsbeheer: zoek bestaande evaluatieactiviteit op; maak aan of update
  5. Participantintern bijwerken: schrijf samenvatting naar PART_DEEL_INTERN

### Activiteitsbeheer
`evaluatie` maakt gebruik van een bestaande activiteit als die er al is (op basis van participant + activiteitstype), anders wordt er een nieuwe aangemaakt. Dit voorkomt duplicaten bij herhaaldelijk opslaan.

### Hooks geïmplementeerd
- `civicrm_customPre`
- `civicrm_config`, `civicrm_install`, `civicrm_enable`

### Custom field groups verwerkt
- **EVAL** (group 146) — deelnemers-evaluatiescores
- **LEID** (group 168) — leidingsevaluatiescores

---

*Beheerd door Stichting Onvergetelijke Zomerkampen.*
