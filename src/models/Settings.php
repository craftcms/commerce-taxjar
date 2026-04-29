<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\taxjar\models;

use craft\commerce\base\Model;
use craft\helpers\App;

/**
 * Settings model.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 1.0
 */
class Settings extends Model
{
    /**
     * @var ?string
     */
    private ?string $_apiKey = null;

    /**
     * @var ?string
     */
    private ?string $_useSandbox = null;

    /**
     * @inerhitdoc
     */
    public function attributes(): array
    {
        $names = parent::attributes();
        $names[] = 'apiKey';
        $names[] = 'useSandbox';

        return $names;
    }

    public function fields(): array
    {
        return [
            'apiKey' => fn() => $this->getApiKey(false),
            'useSandbox' => fn() => $this->getUseSandbox(false),
        ];
    }

    /**
     * @param bool $parse
     * @return string
     */
    public function getApiKey(bool $parse = true): string
    {
        return ($parse ? App::parseEnv($this->_apiKey) : $this->_apiKey) ?? '';
    }

    /**
     * @param string $value
     * @return void
     */
    public function setApiKey(string $value): void
    {
        $this->_apiKey = $value;
    }

    /**
     * @param bool $parse
     * @return bool|string
     */
    public function getUseSandbox(bool $parse = true): bool|string
    {
        if (!$parse) {
            return $this->_useSandbox ?? false;
        }
        return (bool) App::parseBooleanEnv($this->_useSandbox);
    }

    /**
     * @param bool|string $value
     * @return void
     */
    public function setUseSandbox(bool|string $value): void
    {
        $this->_useSandbox = $value;
    }
}
