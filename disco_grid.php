<!DOCTYPE html>
<html>
<head>
<?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
</head>
<body>

<?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>

<?php require($_SERVER['DOCUMENT_ROOT'] . "/releases_toc.php"); ?>

<div class="standard colored">
  <?php
  $artistFiles = glob('disco/*.txt'); // Get text files from folder
  ?>
    <p>
      <?php echo
    	"Releases: " . count($artistFiles) . ".
    	Sorting is alphabetical."; 
    	?>
    </p>
</div>

<br>

<div class="grid-container" id="gridContainer">
	
<?php
$discoFiles = glob('disco/*.txt'); // Get text files from folder

foreach ($discoFiles as $index => $txtFile) {
    $discoName = basename($txtFile, '.txt');
    $discoUrl = 'disco.php?a=' . urlencode($discoName);
    
    $imgUrl = 'disco_photos_small/' . $discoName . '.png';
    if (!file_exists($imgUrl)) {
        $imgUrl = 'img/lp.png'; // Use default image if no photo found
    }

    // Read the third line from the text file
    $thirdLine = '';
    if (file_exists($txtFile)) {
        $fileContents = file($txtFile); // Read all lines into an array
        if (isset($fileContents[3])) { // Check if there is a third line
            $thirdLine = trim($fileContents[3]); // Remove any leading/trailing whitespace
        }
    }

    echo sprintf(
        '<div class="grid-item">
            <a href="%s">
                <img src="%s" alt="%s" title="%s" style="width: 100px; height: 100px;">
            </a>
        </div>',
        $discoUrl,
        $imgUrl,
        $discoName,
        $thirdLine // Set the third line as the title
    );
}
?>

</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>
</html>