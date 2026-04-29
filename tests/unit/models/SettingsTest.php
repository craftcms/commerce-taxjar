<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\tests\unit\models;

use Codeception\Test\Unit;
use craft\commerce\taxjar\models\Settings;
use UnitTester;

/**
 * Settings model tests.
 */
class SettingsTest extends Unit
{
    protected UnitTester $tester;

    // API Key
    // =========================================================================

    public function testGetApiKeyReturnsEmptyStringByDefault(): void
    {
        $settings = new Settings();
        self::assertSame('', $settings->getApiKey());
    }

    public function testSetAndGetApiKeyRoundTrip(): void
    {
        $settings = new Settings();
        $settings->setApiKey('my-api-key-123');
        self::assertSame('my-api-key-123', $settings->getApiKey());
    }

    public function testGetApiKeyUnparsedReturnsBareEnvVarSyntax(): void
    {
        $settings = new Settings();
        $settings->setApiKey('$TAXJAR_API_KEY');
        self::assertSame('$TAXJAR_API_KEY', $settings->getApiKey(false));
    }

    public function testGetApiKeyParsesEnvVar(): void
    {
        putenv('TAXJAR_TEST_API_KEY=resolved-key-value');
        $_ENV['TAXJAR_TEST_API_KEY'] = 'resolved-key-value';

        $settings = new Settings();
        $settings->setApiKey('$TAXJAR_TEST_API_KEY');

        self::assertSame('resolved-key-value', $settings->getApiKey());

        putenv('TAXJAR_TEST_API_KEY');
        unset($_ENV['TAXJAR_TEST_API_KEY']);
    }

    public function testGetApiKeyReturnsEmptyStringWhenEnvVarNotFound(): void
    {
        // App::parseEnv returns null for unresolvable env vars; the ?? '' fallback gives ''
        $settings = new Settings();
        $settings->setApiKey('$TAXJAR_NONEXISTENT_VAR_XYZ');
        self::assertSame('', $settings->getApiKey());
    }

    // Sandbox
    // =========================================================================

    public function testGetUseSandboxReturnsFalseByDefault(): void
    {
        $settings = new Settings();
        self::assertFalse($settings->getUseSandbox());
    }

    public function testSetUseSandboxTrue(): void
    {
        $settings = new Settings();
        $settings->setUseSandbox(true);
        self::assertTrue($settings->getUseSandbox());
    }

    public function testSetUseSandboxFalse(): void
    {
        $settings = new Settings();
        $settings->setUseSandbox(false);
        self::assertFalse($settings->getUseSandbox());
    }

    public function testGetUseSandboxUnparsedReturnsBareEnvVarSyntax(): void
    {
        $settings = new Settings();
        $settings->setUseSandbox('$TAXJAR_USE_SANDBOX');
        // getUseSandbox(false) returns the raw stored string, not parsed
        self::assertSame('$TAXJAR_USE_SANDBOX', $settings->getUseSandbox(false));
    }

    // Attributes / Fields
    // =========================================================================

    public function testAttributesIncludesApiKeyAndUseSandbox(): void
    {
        $settings = new Settings();
        $attributes = $settings->attributes();
        self::assertContains('apiKey', $attributes);
        self::assertContains('useSandbox', $attributes);
    }

    public function testFieldsReturnUnparsedValues(): void
    {
        $settings = new Settings();
        $settings->setApiKey('$TAXJAR_API_KEY');
        $settings->setUseSandbox('$TAXJAR_USE_SANDBOX');

        $fields = $settings->fields();

        self::assertArrayHasKey('apiKey', $fields);
        self::assertArrayHasKey('useSandbox', $fields);
        // Both return the raw stored value (not env-parsed) so settings forms can display the original syntax
        self::assertSame('$TAXJAR_API_KEY', ($fields['apiKey'])());
        self::assertSame('$TAXJAR_USE_SANDBOX', ($fields['useSandbox'])());
    }
}
