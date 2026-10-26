<!DOCTYPE html>
<html>
<head>
<?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>

<style>
    /* Add space between the skip buttons */
    .skip-buttons {
        text-align: center; /* Center the buttons horizontally */
    }

    .skip-button {
        margin: 0 50px; /* Add space between the buttons */
    }
</style>

</head>
<body>
<?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>

<?php require($_SERVER['DOCUMENT_ROOT'] . "/releases_toc.php"); ?>

<?php
// Set up the URL query
$disco = $_GET['a'];
// Get data from text files
$contents = file('disco/' . $disco . '.txt');
// Create thumbnail URL
$thumb = 'disco_photos_small/' . $disco . '.png';
// Create page title
$pagetitle = trim($contents[0]);
// Get name of artist
$artist_name = trim($contents[0]);
?>

<?php
// Get the list of artists from the directory
$discos = glob('disco/*.txt');
// Get the current artist from the URL parameter
$current_disco = isset($_GET['a']) ? $_GET['a'] : '';
// Get the index of the current artist in the list
$current_index = array_search('disco/'.$current_disco.'.txt', $discos);
// Get the next and previous artist in the list, wrapping around if necessary
$next_index = ($current_index + 1) % count($discos);
$prev_index = ($current_index - 1 + count($discos)) % count($discos);
$next_disco = basename($discos[$next_index], '.txt');
$prev_disco = basename($discos[$prev_index], '.txt');
?>

<div class="standard colored">

<!-- Display the skip left and right buttons -->
<h1>

<div class="skip-buttons">
    <span id="skip-left-btn" class="skip-button">
        <a href="<?php echo 'disco.php?a='.$prev_disco; ?>"><img src="img/prev.png" title="PREV"></a>
    </span>

    <span id="skip-right-btn" class="skip-button">
        <a href="<?php echo 'disco.php?a='.$next_disco; ?>"><img src="img/next.png" title="NEXT"></a>
    </span>
</div>

<?php

$url = trim($contents[1]);
if (!empty($url)) {
    echo "<a href='" . $url . "'>" . $artist_name . "</a>";
} else {
    echo $artist_name;
}

$title = trim($contents[2]);
if (!empty($title)) {
    echo " - " . $title . "<br>";
}

?>

</h1>

</div>

    <br>

    <?php

$spotify = trim($contents[9]);

if (file_exists('disco_photos/' . $disco . '.png')) {
    $imageTag = "<img src='disco_photos/$disco.png' title='" . trim($contents[3]) . "' style='width: 1018px;' class='full'>";
    
    if (!empty($spotify)) {
        echo "<a href='$spotify' target='_blank'>$imageTag</a>";
    } else {
        echo $imageTag;
    }
} else {
    echo "<img src='img/lp.png' title='No photo for this release' style='width: 1018px;' class='full'>"; // photo
}

    ?>

    <div class="black">
        <br>
        <br>
        <?php

        $cat = trim($contents[3]);
        if (!empty($cat)) {
            echo "<span title='Catalogue number'>CAT #: </span>" . $cat . "<br>";
        }

        $format = trim($contents[4]);
        if (!empty($format)) {
            echo "<span title='Format'>FORMAT: </span>" . $format . "<br>";
        }
        
        $description = trim($contents[5]);
        if (!empty($description)) {
            echo "<span title='Description'>DESCRIPTION: </span>" . $description . "<br>";
        }

        $tracks = trim($contents[6]);
        if (!empty($tracks)) {
            echo "<span title='Number of tracks'>TRACKS: </span>" . $tracks . "<br>";
        }

        $duration = trim($contents[7]);
        if (!empty($duration)) {
            echo "<span title='Total playing time in mm:ss'>DURATION: </span>" . $duration . "<br>";
        }

        $date = trim($contents[8]);
        if (!empty($date)) {
            echo "<span title='Release date'>DATE: </span>" . $date . "<br>";
        }

$tracklistFile = 'tracklists/' . $disco . '.txt';

if (file_exists($tracklistFile)) {
    echo "<br><br><h1>TRACKLIST</h1>";
    $lines = file($tracklistFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            echo $line . "<br>";
        }
    echo "<br><br>";
    } else {
        echo "Could not read the tracklist file.";
    }
}

        ?>
    </div>

    <?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>
</body>
</html>
