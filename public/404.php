<?php

declare(strict_types=1);

http_response_code(404);

require __DIR__ . '/../config/init.php';

use App\Classes\App;

App::render('404.twig');