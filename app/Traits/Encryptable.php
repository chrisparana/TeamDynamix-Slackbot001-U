<?php

namespace Slackbot001\Traits;

use Crypt;

trait Encryptable
{
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable)) {
            try {
                if ($value) {
                    $value = Crypt::decrypt($value);
                }
            } catch (DecryptException $e) {
                $value = $value;
                Log::error('Value not decyrptable');
            }
        }

        return $value;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable)) {
            if ($value) {
                $value = Crypt::encrypt($value);
            }
        }

        return parent::setAttribute($key, $value);
    }
}
