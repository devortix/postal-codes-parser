<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swagger UI</title>

    <!-- Підключення Swagger UI через CDN -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/3.52.0/swagger-ui.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/3.52.0/swagger-ui-bundle.js"></script>
</head>
<body>
<div id="swagger-ui"></div>

<script>
    const ui = SwaggerUIBundle({
        url: "/swagger.json", // Замість цього вкажіть URL до вашого swagger.json
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
            SwaggerUIBundle.presets.apis,
            SwaggerUIBundle.presets.operations
        ]
    });
</script>
</body>
</html>