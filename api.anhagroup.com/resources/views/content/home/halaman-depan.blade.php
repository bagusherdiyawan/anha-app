<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Laravel App with jQuery</title>
    <!-- CSS assets can be in the head -->
    @vite(['resources/css/app.css'])
</head>
<body>
    <!-- Your main HTML content goes here -->

    <h1 class="text-3xl font-bold text-center mt-10">Welcome to Laravel with jQuery!</h1>
    <button id="myButton" class="block mx-auto mt-5 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Click Me</button>

    <!-- JavaScript assets should be at the end of the body -->
    @vite(['resources/js/app.js'])

    <script type="module">
        // This inline script can now safely use jQuery
        $(document).ready(function() {
            $('#myButton').on('click', function() {
                alert('Button clicked using jQuery!');
                $(this).text('Clicked!');
            });
        });
    </script>
</body>
</html>