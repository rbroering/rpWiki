<?php
header('Content-Type: text/css');

require_once('../../getdata.php');

$styles = [
    'main'      => [],
    'device'    => ['mediaquery' => ['max-width: 720px']]
];

$color_scheme = 'light';

if (key_exists('color_theme', $UserPref)) {
    if ($UserPref['color_theme'] == 'adapt') {
        $styles['dark'] = [
            'mediaquery' => ['screen', 'prefers-color-scheme: dark']
        ];

        $color_scheme .= ' dark';
    } elseif ($UserPref['color_theme'] == 'dark') {
        $styles['dark'] = [];

        $color_scheme = 'dark';
    }
}

foreach ($styles as $style => $settings) {
    $MediaQuery = '';

    foreach ($settings as $setting => $value)
        if ($setting == 'mediaquery') {
            foreach ($value as $i => $query) {
                if (strstr($query, ':'))
                    $value[$i] = '(' . $query . ')';
            }

            $MediaQuery .= implode(' and ', $value);
        }

    if (!empty($MediaQuery))
        $MediaQuery = ' ' . $MediaQuery;

    echo "@import url('$style.css')$MediaQuery;\r\n";
}

?>

:root {
    color-scheme: <?php echo $color_scheme; ?>;
}
