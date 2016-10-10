<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>MyApp</title>
	<?=$odin->load->write('css')?>
</head>
<body>
	<header id="header">
		<h1>My App - Welcome</h1>
	</header>
	<section id="main">
	<?=$content?>
	</section>
	<footer id="footer">
		<p>Brought to you through the Odin PHP Framework.</p>
	</footer>
	<?=$odin->load->write('js')?>
</body>
</html>