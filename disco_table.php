<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
<?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
</head>

<body>

<?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>

<h1 class="heading">RELEASES 

<a href="disco_table.php">
<img src="img/view_table.png" title="Table" style='height: 30px; width: 30px;'>
</a>

<a href="disco_grid.php">
<img src="img/view_grid.png" title="Grid" style='height: 30px; width: 30px;'>
</a>

</h1>

<?php
// Fetch all filenames in folder
$disco = glob('disco/*.txt');
?>

<div class="standard colored">
	<br>
 	  <p>
    	<?php echo
    	"Releases: " . count($disco) . ".
    	Table is sortable."; ?>
    </p>
  <br>
</div>

<br>
<br>

<table class="standard sortable over">
  <thead>
  	<tr style="height: 45px;">
    <th style="width: 30px;" class="notwrap">							<span title="Alphabetical number">#</span></th>
    <th style="width: 50px;" class="notwrap">							<span title="Picture">SLEEVE</span></th>
    <th style="width: 150px;" class="notwrap">			<span title="Artist name">ARTIST</span></th>
    <th style="width: 150px;" class="notwrap">	<span title="Title">TITLE</span></th>
    <th style="width: 150px;" class="notwrap">						<span title="cat">CAT #</span></th>
    <th style="width: 100px;" class="notwrap">				<span title="spotify">SPOTIFY</span></th>
    </tr>
  </thead>

<?php
    // Loop through all the files in the disco folder
    foreach ($disco as $i => $file) {
    $contents = file($file);
    echo "<tr style='height: 50px;'>";

    // Fetch contents of file as an array, where each line is a 0-based numerically indexed element in the array
    $contents = file($file);

    // Fetch basename (the filename part only)
    $basename = basename($file, ".txt");

		// at the point of processing the list of names...
		// explode the list of names - works for one or more names
		$list = explode(',',$contents[5]);
		// trim all the values at once
		$list = array_map('trim',$list);
		
		// produce the link(s)
		$links = [];
		foreach($list as $name)
		{
			// lookup the file that corresponds to the name
			$file = $map[$name];
			// if there might not be a file for a name, add conditional logic here to handle that case
			$links[] = "<a href='/artist.php?a=$file'>$name</a>";
		}
		
    // Output some stuff
    echo "<td style='text-align: right;'>" . ($i + 1) . "</td>"; // index
    if (file_exists('disco_photos_small/' . $basename . '.png')) {
    echo "<td class='notwrap tight' title='" . trim($contents[0]) . "'><a href='/disco.php?a=$basename'><img src='/disco_photos_small/$basename.png' alt='release'></a></td>";
    } else {
    echo "<td><img src='no_photo_disco_50.png'></td>"; // photo
    }
    echo "<td class='notwrap' style='text-align: left;' title='" . trim($contents[0]) . "'><a href='/disco.php?a=$basename'>" . trim($contents[0]) . "</a></td>"; // disco
    echo "<td class='notwrap' style='text-align: left;' title='" . trim($contents[1]) . "'>" . trim($contents[2]) . "</td>"; // title
    echo "<td class='notwrap' style='text-align: left;' title='" . trim($contents[3]) . "'>" . trim($contents[3]) . "</td>"; // cat
    echo "<td class='notwrap' style='text-align: left;' title='" . trim($contents[5]) . "'>" . implode(', ',$links) . "</td>"; // played with
    echo "</tr>";
}

echo "</table>";
?>

<?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>
</html>