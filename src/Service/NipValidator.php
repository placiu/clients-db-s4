<?php

namespace App\Service;

use App\Exception\NipValidationException;

class NipValidator
{
    static public function validate(string $nip)
    {
        if (preg_match('/^[0-9]*$/', $nip) === 0) {
            throw new NipValidationException('NIP can contain numbers only!');
        }

        if (strlen($nip) !== 10) {
            throw new NipValidationException('NIP must have 10 digits!');
        }

        $value = [6, 5, 7, 2, 3, 4, 5, 6, 7];
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += $value[$i] * (string)$nip[$i];
        }

        $int = $sum % 11;

        $control = $int === 10 ? 0 : $int;

        if ($control == $nip[9]) {
            return true;
        }

        throw new NipValidationException('Wrong NIP!');
    }
}