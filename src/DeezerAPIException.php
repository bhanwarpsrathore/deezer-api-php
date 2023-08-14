<?php

declare(strict_types=1);

namespace DeezerAPI;

class DeezerAPIException extends \Exception {

    /**
     * https://developers.deezer.com/api/errors
     */

    public const CODE_QUOTA = 4;

    /**
     * Returns whether the exception was thrown because of rate limiting.
     *
     * @return bool
     */
    public function isRateLimited() {
        return $this->getCode() === self::CODE_QUOTA;
    }
}
