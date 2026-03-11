<?php

declare(strict_types=1);

require __DIR__ . '/../../config/init.php';

use App\Classes\Ajax;

Ajax::init();
Ajax::requireAuthorized();