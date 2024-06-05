<?php

namespace Drupal\Tests\dpl_login\Unit;

use Drupal\Core\Site\Settings;
use Drupal\dpl_login\AuthorizationIdType;
use Drupal\dpl_login\OpenIdUserInfoService;
use Drupal\Tests\UnitTestCase;
use phpmock\MockBuilder;

/**
 * Unit test for the Adgangsplatformen configuration.
 *
 * @covers \Drupal\dpl_login\Adgangsplatformen\Config
 */
class OpenIdUserInfoServiceTest extends UnitTestCase {
  /**
   * Drupal settings.
   */
  private Settings $settings;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $settings = [];
    $settings['hash_salt'] = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';
    $this->settings = new Settings($settings);
  }

  /**
   * Test precedence of CPR over uniqueId.
   *
   * @dataProvider cprHasPrecedenceOverUniqueIdData
   */
  public function testHashCreationThatCprHasPrecedenceOverUniqueid(array $userinfo, string $expected_sub_id, AuthorizationIdType $expected_id_type) {
    $service = new OpenIdUserInfoService($this->settings);
    $sub_id = $service->getSubjectIdFromUserInfo($userinfo);
    $id_type = $service->getIdentifierDataFromUserInfo($userinfo)['type'];

    $this->assertEquals($expected_sub_id, $sub_id);
    $this->assertEquals($expected_id_type, $id_type);
  }

  /**
   * Test Exception if both CPR and uniqueId are missing.
   */
  public function testThatGettingSubHashFromUserInfoThrowsAnExceptionIfBothCprAndUniqueIdAreMissing() {
    $service = new OpenIdUserInfoService($this->settings);
    $userinfo = [
      'attributes' => [],
    ];
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Unable to identify user. Both CPR and uniqueId are missing.');

    $service->getSubjectIdFromUserInfo($userinfo);
  }

  /**
   * Test that hashed identifiers are unique.
   *
   * @dataProvider weGetUniqueHashesNotMatterWhatData
   */
  public function testThatHashedIdentifiersAreUnique(string $id_1, string $id_2) {
    $service = new OpenIdUserInfoService($this->settings);
    $hash1 = $service->hashIdentifier($id_1);
    $hash2 = $service->hashIdentifier($id_2);
    $this->assertNotEquals($hash1, $hash2);
  }

  /**
   * Test that hashed identifier is reproducible.
   */
  public function testThatHashedIdentifierIsReproducible() {
    $service = new OpenIdUserInfoService($this->settings);
    $id = '9d67c9fa-81d6-41ce-8b42-9d187b306fd9';
    $hash1 = $service->hashIdentifier($id);
    $hash2 = $service->hashIdentifier($id);
    $this->assertEquals($hash1, $hash2);
  }

  /**
   * Test that we get expected user info from service.
   */
  public function testThatWeGetExpectedUserInfoFromService() {
    // getOpenIdUserInfoFromAdgangsplatformenUserInfoResponse uses uniqid()
    // to generate a unique user name. We need to mock it to get a predictable
    // result.
    $builder = new MockBuilder();
    $builder->setNamespace('Drupal\dpl_login')
      ->setName("uniqid")
      ->setFunction(
                function () {
                    return '9999999999999';
                }
            );
    $mock = $builder->build();
    $mock->enable();

    $service = new OpenIdUserInfoService($this->settings);
    $userinfo = $service->getOpenIdUserInfoFromAdgangsplatformenUserInfoResponse([
      'attributes' => [
        'cpr' => '1234567890',
        'uniqueId' => '9d67c9fa-81d6-41ce-8b42-9d187b306fd9',
      ],
    ]);

    $this->assertEquals([
      'email' => '9999999999999@dpl-cms.invalid',
      'name' => '9999999999999',
      'sub' => 'e2057a73dd0aa38c7fa4450a9a0561494cd4baa4a1b769d6f04deb3e3677379e',
    ], $userinfo);

    $mock->disable();
  }

  /**
   * Data provider for testHashCreationThatCprHasPrecedenceOverUniqueid.
   */
  public function cprHasPrecedenceOverUniqueIdData(): array {
    return [
      'cpr is getting hashed when both cpr and uniqueId are present' => [
        [
          'attributes' => [
            'cpr' => '1234567890',
            'uniqueId' => '9d67c9fa-81d6-41ce-8b42-9d187b306fd9',
          ],
        ],
        'e2057a73dd0aa38c7fa4450a9a0561494cd4baa4a1b769d6f04deb3e3677379e',
        AuthorizationIdType::CPR,
      ],
      'uniqueId is getting hashed when cpr is missing' => [
        [
          'attributes' => [
            'uniqueId' => '9d67c9fa-81d6-41ce-8b42-9d187b306fd9',
          ],
        ],
        '70abf093ee19f055b16d35e494e3cc1c6a0f4bae36c6b07ae9ed0d7867bf1909',
        AuthorizationIdType::UNIQUE_ID,
      ],
    ];
  }

  /**
   * Data provider for testThatHashedIdentifiersAreUnique.
   */
  public function weGetUniqueHashesNotMatterWhatData(): array {
    return [
      'a pair of cpr that starts with the same character' => [
        '1234567890',
        '1098765432',
      ],
      'a pair of cpr that starts with the same two characters' => [
        '1234567890',
        '1209876543',
      ],
      'a pair of cpr that starts with the same three characters' => [
        '1234567890',
        '1230987654',
      ],
      'a pair of cpr that starts with the same four characters' => [
        '1234567890',
        '1234098765',
      ],
      'a pair of cpr that starts with the same five characters' => [
        '1234567890',
        '1234509876',
      ],
      'a pair of cpr that starts with the same six characters' => [
        '1234567890',
        '1234560987',
      ],
      'a pair of cpr that starts with the same seven characters' => [
        '1234567890',
        '1234567098',
      ],
      'a pair of cpr that starts with the same eight characters' => [
        '1234567890',
        '1234567809',
      ],
      'a pair of cpr that starts with the same nine characters' => [
        '1234567890',
        '1234567891',
      ],
      'a pair of unique_ids that start with the same character' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e0aca8c3-5e36-42e0-b9e5-bd867e3b4599',
      ],
      'a pair of unique_ids that starts with the same two characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1aca8c3-5e36-42e0-b9e5-bd867e3b4599',
      ],
      'a pair of unique_ids that starts with the same three characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1aee469-527b-4025-9c55-2daa7a9fa172',
      ],
      'a pair of unique_ids that starts with the same four characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0d7c6-1dad-4372-89dd-d23d018c470b',
      ],
      'a pair of unique_ids that starts with the same five characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0e309-432e-48bd-914a-ab30cfcc2f6c',
      ],
      'a pair of unique_ids that starts with the same six characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0ecaf-2830-44da-aaa0-109d81aff5e4',
      ],
      'a pair of unique_ids that starts with the same seven characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0ecc5-90eb-4f5c-acb8-a457c38f0f02',
      ],
      'a pair of unique_ids that starts with the same nine characters (because of separator)' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-4b5b-418c-a6df-eafad2898e36',
      ],
      'a pair of unique_ids that starts withe the same ten characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-69cb-4a3c-b62b-3a9bccd75e54',
      ],
      'a pair of unique_ids that starts withe the same elleven characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6ac1-4cb5-be86-fae04806fe59',
      ],
      'a pair of unique_ids that starts withe the same twelve characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a81-4cb5-be86-fae04806fe59',
      ],
      'a pair of unique_ids that starts withe the same thirteen characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-1e9a-b38c-eed00a66a2c3',
      ],
      'a pair of unique_ids that starts withe the same sixteen characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-479a-b38c-eed00a66a2c3',
      ],
      'a pair of unique_ids that starts withe the same seventeen characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47aa-b38c-eed00a66a2c3',
      ],
      'a pair of unique_ids that starts withe the same nineteen characters (because of separator)' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-b38c-eed00a66a2c3',
      ],
      'a pair of unique_ids that starts withe the same twenty characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-838c-eed00a66a2c3',
      ],
      'a pair of unique_ids that starts withe the same twenty one characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-888c-eed00a66a2c3',
      ],
      'a pair of unique_ids that starts withe the same twenty two characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-886c-eed00a66a2c3',
      ],
      'a pair of unique_ids that starts withe the same twenty three characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-8865-eed00a66a2c3',
      ],
      'a pair of unique_ids that starts withe the same twenty five characters (because of separator)' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-886c-6ed00a66a2c3',
      ],
      'a pair of unique_ids that starts withe the same twenty six characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-886c-6ad00a66a2c3',
      ],
      'a pair of unique_ids that starts withe the same twenty seven characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-886c-6ac90a66a2c3',
      ],
      'a pair of unique_ids that starts withe the same twenty eight characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-886c-6ac90a66a2c3',
      ],
      'a pair of unique_ids that starts withe the same twenty nine characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-886c-6ac91a66a2c3',
      ],
      'a pair of unique_ids that starts withe the same thirty characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-886c-6ac91c66a2c3',
      ],
      'a pair of unique_ids that starts withe the same thirty one characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-886c-6ac91c16a2c3',
      ],
      'a pair of unique_ids that starts withe the same thirty two characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-886c-6ac91c10a2c3',
      ],
      'a pair of unique_ids that starts withe the same thirty three characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-886c-6ac91c1042c3',
      ],
      'a pair of unique_ids that starts withe the same thirty four characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-886c-6ac91c104fc3',
      ],
      'a pair of unique_ids that starts withe the same thirty five characters' => [
        'e1a0eccd-6a89-47ad-8865-6ac91c104f22',
        'e1a0eccd-6a89-47ad-886c-6ac91c104f23',
      ],
    ];
  }

}
