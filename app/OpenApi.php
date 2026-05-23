<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "CRM API",
    version: "1.0.0",
    description: "API untuk CRM Agency System"
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Local server"
)]
class OpenApi {}