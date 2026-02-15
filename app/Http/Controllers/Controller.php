<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Workshop API",
    version: "1.0.0",
    description: "API documentation for SSB Workshop application"
)]
#[OA\Server(
    url: "/",
    description: "Default Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
abstract class Controller
{
    //
}
