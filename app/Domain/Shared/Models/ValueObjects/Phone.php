<?php
declare(strict_types=1);

namespace App\Domain\Shared\Models\ValueObjects;

use App\Domain\Shared\Exceptions\InvalidPhoneFormatException;

readonly class Phone
{
    public string $value;

    /**
     * @throws InvalidPhoneFormatException
     */
    public function __construct(string $value)
    {
        if (!preg_match('/^(\+?7|8)(\d{10})$/', $value, $matches)) {
            throw new InvalidPhoneFormatException(sprintf('Phone number format is invalid: %s.', $value));
        }

        $this->value = '7' . $matches[2];
    }
}
