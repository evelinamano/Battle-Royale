<?php


$root = __DIR__;
$self = __FILE__;

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

foreach ($rii as $file) {
    if ($file->isDir()) continue;
    if (strtolower($file->getExtension()) !== 'php') continue;

    $path = $file->getPathname();
    // Skip this script itself
    if (realpath($path) === realpath($self)) continue;

    $contents = file_get_contents($path);

    if (strpos($contents, '$_SERVER[\'PHP_SELF\']') === false && strpos($contents, '$safe_self') === false) {
        continue;
    }

    // Prepare the safe_self line
    $safeSelfLine = '$safe_self = htmlspecialchars($_SERVER[\'PHP_SELF\'], ENT_QUOTES, \'UTF-8\');';

    // Insert after <?php
    if (preg_match('/<\?php\b/', $contents, $matches, PREG_OFFSET_CAPTURE)) {
        $pos = $matches[0][1] + strlen($matches[0][0]);
        $contents = substr_replace($contents, "\n" . $safeSelfLine . "\n", $pos, 0);
    } else {
        // If no <?php tag, insert at the top
        $contents = "<?php\n" . $safeSelfLine . "\n" . $contents;
    }

    // Replace all $_SERVER['PHP_SELF'] and $safe_self with $safe_self
    $contents = str_replace(
        ['$_SERVER[\'PHP_SELF\']', '$safe_self'],
        '$safe_self',
        $contents
    );

    file_put_contents($path, $contents);
    echo "Updated: $path\n";
}

echo "Done.\n";