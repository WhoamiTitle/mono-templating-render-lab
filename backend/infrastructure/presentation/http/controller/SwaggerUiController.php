<?php

declare(strict_types=1);

namespace infrastructure\presentation\http\controller;

use infrastructure\presentation\http\attribute\Route;
use infrastructure\presentation\http\HttpRequest;
use infrastructure\presentation\http\HttpResponse;

#[Route('GET', '/docs')]
final readonly class SwaggerUiController
{
    public function __invoke(HttpRequest $request): HttpResponse
    {
        return new HttpResponse(
            statusCode: 200,
            headers: ['content-type' => ['text/html; charset=utf-8']],
            body: <<<'HTML'
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <title>Templating Render Lab API</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
      body { margin: 0; background: #fff; }
      .swagger-ui .topbar { display: none; }
    </style>
  </head>
  <body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
      window.ui = SwaggerUIBundle({
        url: '/openapi.json',
        dom_id: '#swagger-ui',
        deepLinking: true,
        persistAuthorization: true
      });
    </script>
  </body>
</html>
HTML
        );
    }
}
