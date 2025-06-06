<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Character encoding -->
    <meta charset="UTF-8" />

    <!-- Viewport settings for responsive design -->
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- External stylesheet for Font Awesome icons -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
      integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />

    <!-- Document title -->
    <title>Japan Travel Info</title>

    <!-- Vite assets -->
    @vite(['resources/js/app.js', 'resources/css/app.css'])
  </head>
  <body>
    <!-- Main application container -->
    <div id="app">
      <travel-app></travel-app>
    </div>
  </body>
</html>
