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
    <?php echo "Releases: " . count($artistFiles) . ". Sorting is chronological."; ?>
  </p>
</div>

<br>

<style>
  .list-item {
    display: flex;
    align-items: flex-start; /* Align items to the top of the div */
  }

  .list-item img {
    width: 200px;
    height: 200px;
    margin-right: 20px; /* Add space between image and content */
  }

  .list-item .content {
    flex-grow: 1; /* Allow content to grow and fill remaining space */
  }

  /* Adjustments for smaller screens */
  @media (max-width: 768px) {
    .list-item {
      flex-direction: column; /* Stack content vertically on smaller screens */
      align-items: center; /* Center align items horizontally on smaller screens */
      text-align: center; /* Center-align text on smaller screens */
    }

    .list-item img {
      margin: auto; /* Remove image margin on smaller screens */
      margin-bottom: 10px; /* Add space below image on smaller screens */
      width: 100%;
      height: auto;
    }
  }
</style>

<div class="standard tight">
  <?php
  $discoFiles = glob('disco/*.txt'); // Get text files from folder

  // Create an array to store releases with their dates
  $releases = [];

  foreach ($discoFiles as $index => $txtFile) {
    $discoName = basename($txtFile, '.txt');
    $discoUrl = 'disco.php?a=' . urlencode($discoName);

    // Read lines from the text file
    $fileContents = file($txtFile);
    $artist = trim($fileContents[0]); // Artist
    $artistUrl = trim($fileContents[1]); // Artist URL
    $songTitle = trim($fileContents[2]); // Track title
    $catNumber = trim($fileContents[3]); // Catalogue number
    $format = trim($fileContents[4]); // Format
    $duration = trim($fileContents[7]); // Duration
    $date = trim($fileContents[8]); // Date
    $spotifyUrl = trim($fileContents[9]); // Spotify URL

    // Define the image URL based on the discoName
    $imgUrl = 'disco_photos_small/' . $discoName . '.png';
    if (!file_exists($imgUrl)) {
      $imgUrl = 'img/lp.png'; // Use default image if no photo found
    }

    // Store release information in an array
    $releases[] = [
      'discoUrl' => $discoUrl,
      'imgUrl' => $imgUrl,
      'discoName' => $discoName,
      'catNumber' => $catNumber,
      'artistUrl' => $artistUrl,
      'artist' => $artist,
      'spotifyUrl' => $spotifyUrl,
      'songTitle' => $songTitle,
      'format' => $format,
      'duration' => $duration,
      'date' => $date,
    ];
  }

  // Sort releases by date in descending order (latest first)
  usort($releases, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
  });

  // Display the sorted releases
foreach ($releases as $release) {
    echo sprintf(
        '<div class="list-item tight">
            <a href="%s">
                <img src="%s" alt="%s" title="%s">
            </a>
            <div class="content">
                <h1 class="release_heading">',
        $release['discoUrl'],
        $release['imgUrl'],
        $release['discoName'],
        $release['catNumber']
    );

    // Check if $artistUrl is empty
    if (!empty($release['artistUrl'])) {
        echo '<a href="' . $release['artistUrl'] . '">' . $release['artist'] . '</a>';
    } else {
        echo $release['artist'];
    }

    // Check if $spotifyUrl is empty for the title
    if (!empty($release['spotifyUrl'])) {
        echo ' - <a href="' . $release['spotifyUrl'] . '" target="_blank">' . $release['songTitle'] . '</a>';
    } else {
        echo ' - ' . $release['songTitle'];
    }

    echo sprintf(
        '</h1>
                <div><span title="Catalogue number">CAT #:</span> %s</div>
                <div><span title="Format">FORMAT:</span> %s</div>
                <div><span title="Total playing time in mm:ss">DURATION:</span> %s</div>
                <div><span title="Release date">DATE:</span> %s</div>
            </div>
        </div><br><br>',
        $release['catNumber'],
        $release['format'],
        $release['duration'],
        $release['date']
    );
}

  ?>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

</body>
</html>