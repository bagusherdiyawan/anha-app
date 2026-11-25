<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Inforkampus</title>
	@vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
	<div id="id">
		<main class="py-4">
			@yield('content')
		</main>
	</div>
</body>
</html>