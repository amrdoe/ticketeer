<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="stripe-public-key" content="{{ config('services.stripe.public') }}">
    <title>Ticketeer - Event Ticketing Platform</title>
    @vite('resources/js/app.ts')
</head>
<body>
    <div id="app"></div>
    <script src="https://js.stripe.com/v3/"></script>
</body>
</html>
