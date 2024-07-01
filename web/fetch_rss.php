<?php
// Create a stream context to set headers
$options = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3\r\n" .
                    "Referer: https://drupal10.lndo.site\r\n"
    ]
];

$context = stream_context_create($options);

// Fetch the RSS feed
$response = file_get_contents($feed_url, false, $context);

// Check if the request was successful
if ($response !== false) {
    // Parse the XML string
    $xml = simplexml_load_string($response);

    // Check if parsing was successful
    if ($xml !== false) {
        // Initialize an array to store the titles and descriptions
        $items = [];

        // Iterate over the XML items
        foreach ($xml->channel->item as $item) {
            // Extract title and description
            $title = (string) $item->title;
            $description = (string) $item->description;

            // Store the title and description in the array
            $items[] = [
                'title' => $title,
                'description' => $description
            ];
        }

        // Output the extracted titles and descriptions
        foreach ($items as $item) {
            echo "Title: " . $item['title'] . "<br>";
            echo "Description: " . $item['description'] . "<br><br>";
        }
    } else {
        echo "Failed to parse XML.";
    }
} else {
    echo "Failed to fetch RSS feed.";
}
?>
