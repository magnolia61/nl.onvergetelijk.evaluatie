<?php

namespace Civi\Evaluatie;

use Civi\Test\EndToEndInterface;
use Civi\Test\TransactionalInterface;

/**
 * Test voor evaluatie_get_field_map_eval() en evaluatie_get_field_map_leid().
 *
 * @group e2e
 *
 * Beide functies zijn pure "Single Source of Truth" mappings zonder DB-afhankelijkheid.
 * Ze koppelen database-kolomnamen (met ID-suffix) aan interne EVAL.- en LEID.-sleutels.
 *
 * Scenario's:
 *
 * evaluatie_get_field_map_eval():
 *   - Retourneert een non-lege array
 *   - Alle sleutels bevatten een numeriek suffix (column_naam_NNNN)
 *   - Alle waarden beginnen met 'EVAL.'
 *   - Bevat verplichte velden: datum, terugblik, aanrader
 *
 * evaluatie_get_field_map_leid():
 *   - Retourneert een non-lege array
 *   - Alle waarden beginnen met 'LEID.'
 *   - Bevat verplichte velden: team, voorbereiding, veiligheid
 *
 * Consistentie:
 *   - Geen overlappende waarden tussen eval en leid map
 *   - Alle waarden zijn uniek binnen dezelfde map
 */
class FieldMapTest extends \PHPUnit\Framework\TestCase implements EndToEndInterface, TransactionalInterface {

  public function setUp(): void {
    parent::setUp();
    if (!function_exists('evaluatie_get_field_map_eval')) {
      $this->markTestSkipped('evaluatie_get_field_map_eval() niet beschikbaar; is nl.onvergetelijk.evaluatie geïnstalleerd?');
    }
  }

  // ########################################################################
  // ### evaluatie_get_field_map_eval()
  // ########################################################################

  /**
   * Retourneert een non-lege array.
   */
  public function testEvalMapIsNonLeegArray() {
    $result = evaluatie_get_field_map_eval();
    $this->assertIsArray($result, 'evaluatie_get_field_map_eval() moet een array teruggeven.');
    $this->assertNotEmpty($result, 'De eval field map mag niet leeg zijn.');
  }

  /**
   * Alle sleutels bevatten een numeriek suffix (kolomnaam_ID patroon).
   */
  public function testEvalMapSleutelsHebbenNumeriekeId() {
    foreach (evaluatie_get_field_map_eval() as $key => $value) {
      $this->assertMatchesRegularExpression('/_\d+$/', $key,
        "Sleutel '$key' moet eindigen op een numeriek suffix (bijv. _1076)."
      );
    }
  }

  /**
   * Alle waarden beginnen met 'EVAL.' (interne namespace-conventie).
   */
  public function testEvalMapWaardenBeginnenMetEval() {
    foreach (evaluatie_get_field_map_eval() as $key => $value) {
      $this->assertStringStartsWith('EVAL.', $value,
        "Waarde '$value' voor sleutel '$key' moet beginnen met 'EVAL.'."
      );
    }
  }

  /**
   * Bevat de verplichte velden: datum, terugblik en aanrader.
   */
  public function testEvalMapBevatVerplichteFelden() {
    $values = array_values(evaluatie_get_field_map_eval());
    $this->assertContains('EVAL.datum_evaluatie', $values, 'EVAL.datum_evaluatie moet aanwezig zijn in de eval map.');
    $this->assertContains('EVAL.terugblik',       $values, 'EVAL.terugblik moet aanwezig zijn in de eval map.');
    $this->assertContains('EVAL.aanrader',        $values, 'EVAL.aanrader moet aanwezig zijn in de eval map.');
  }

  /**
   * Alle waarden in de eval map zijn uniek (geen duplicaat-mappings).
   */
  public function testEvalMapWaardenZijnUniek() {
    $values = array_values(evaluatie_get_field_map_eval());
    $this->assertEquals(count($values), count(array_unique($values)), 'Elke EVAL.*-waarde mag maar één keer voorkomen in de map.');
  }

  // ########################################################################
  // ### evaluatie_get_field_map_leid()
  // ########################################################################

  /**
   * Retourneert een non-lege array.
   */
  public function testLeidMapIsNonLeegArray() {
    $result = evaluatie_get_field_map_leid();
    $this->assertIsArray($result, 'evaluatie_get_field_map_leid() moet een array teruggeven.');
    $this->assertNotEmpty($result, 'De leid field map mag niet leeg zijn.');
  }

  /**
   * Alle waarden beginnen met 'LEID.' (interne namespace-conventie).
   */
  public function testLeidMapWaardenBeginnenMetLeid() {
    foreach (evaluatie_get_field_map_leid() as $key => $value) {
      $this->assertStringStartsWith('LEID.', $value,
        "Waarde '$value' voor sleutel '$key' moet beginnen met 'LEID.'."
      );
    }
  }

  /**
   * Bevat verplichte leidingvelden: team, voorbereiding, veiligheid sociaal en fysiek.
   */
  public function testLeidMapBevatVerplichteFelden() {
    $values = array_values(evaluatie_get_field_map_leid());
    $this->assertContains('LEID.team',          $values, 'LEID.team moet aanwezig zijn.');
    $this->assertContains('LEID.voorbereiding', $values, 'LEID.voorbereiding moet aanwezig zijn.');
    $this->assertContains('LEID.veiligsociaal', $values, 'LEID.veiligsociaal moet aanwezig zijn.');
    $this->assertContains('LEID.veiligfysiek',  $values, 'LEID.veiligfysiek moet aanwezig zijn.');
  }

  /**
   * Alle waarden in de leid map zijn uniek.
   */
  public function testLeidMapWaardenZijnUniek() {
    $values = array_values(evaluatie_get_field_map_leid());
    $this->assertEquals(count($values), count(array_unique($values)), 'Elke LEID.*-waarde mag maar één keer voorkomen in de map.');
  }

  // ########################################################################
  // ### CONSISTENTIE TUSSEN BEIDE MAPS
  // ########################################################################

  /**
   * Geen overlappende waarden tussen eval en leid map (gescheiden namespaces).
   */
  public function testGeenOverlapTussenEvalEnLeidMap() {
    $evalValues = array_values(evaluatie_get_field_map_eval());
    $leidValues = array_values(evaluatie_get_field_map_leid());
    $overlap    = array_intersect($evalValues, $leidValues);

    $this->assertEmpty($overlap, 'EVAL.* en LEID.* mogen geen overlappende waarden hebben: ' . implode(', ', $overlap));
  }
}
