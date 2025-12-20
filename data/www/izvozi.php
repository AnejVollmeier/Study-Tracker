<?php
require_once("config.php");
require_once("db.php");

if (!isset($_POST['predmet_id']) || !isset($_POST['format'])) {
    header("HTTP/1.1 400 Bad Request");
    exit();
}

$predmetId = (int)$_POST['predmet_id'];
$format = $_POST['format'];
$userId = $_SESSION['user_id'] ?? null;

$checkQuery = "SELECT ime FROM predmet WHERE id_predmeta = ? AND TK_oseba = ?";
$checkStmt = $pdo->prepare($checkQuery);
$checkStmt->execute([$predmetId, $userId]);
$predmet = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$predmet) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}
$grafQuery = "
    SELECT 
        p.ime as ime_poglavja,
        COALESCE(SUM(s.trajanje_min), 0) as cas_ucenja
    FROM poglavje p
    LEFT JOIN seja s ON s.TK_poglavje = p.id_poglavja AND s.TK_osebe = ?
    WHERE p.TK_predmet = ?
    GROUP BY p.id_poglavja, p.ime
    ORDER BY p.ime ASC
";
$grafStmt = $pdo->prepare($grafQuery);
$grafStmt->execute([$userId, $predmetId]);
$podatki = $grafStmt->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'pdf') {
    exportPDF($predmet['ime'], $podatki);
} elseif ($format === 'excel') {
    exportExcel($predmet['ime'], $podatki);
}

function exportPDF($predmetIme, $podatki)
{
    $skupajCas = 0;
    foreach ($podatki as $row) {
        $skupajCas += (int)$row['cas_ucenja'];
    }

    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 30px; color: #333; }
            .header { background: #0066cc; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            h1 { margin: 0; font-size: 28px; }
            .subtitle { font-size: 14px; margin-top: 10px; opacity: 0.9; }
            .info { background: #f0f0f0; padding: 15px; margin: 20px 0; border-left: 4px solid #0066cc; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th { background: #0066cc; color: white; padding: 12px; text-align: left; }
            td { padding: 12px; border-bottom: 1px solid #ddd; }
            tr:hover { background: #f9f9f9; }
            .number { text-align: right; font-weight: bold; }
            .total-row { background: #f0f0f0; font-weight: bold; }
            .footer { margin-top: 30px; text-align: center; color: #999; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>' . htmlspecialchars($predmetIme) . '</h1>
            <div class="subtitle">Izvoz časa učenja</div>
        </div>
        
        <div class="info">
            <strong>Datum izvoza:</strong> ' . date('d.m.Y ob H:i') . '<br>
            <strong>Skupaj poglavij:</strong> ' . count($podatki) . '<br>
            <strong>Skupaj časa:</strong> ' . $skupajCas . ' minut
        </div>

        <div class="chart-container">
            <h2>Graf učenja po poglavjih</h2>
            ' . generateChart($podatki) . '
        </div>

        <h2>Podrobnosti</h2>
        <table>
            <thead>
                <tr>
                    <th>Poglavje</th>
                    <th class="number">Čas učenja (minute)</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($podatki as $row) {
        $cas = (int)$row['cas_ucenja'];
        $html .= '<tr>
            <td>' . htmlspecialchars($row['ime_poglavja']) . '</td>
            <td class="number">' . $cas . '</td>
        </tr>';
    }

    $html .= '</tbody>
            <tfoot>
                <tr class="total-row">
                    <td>SKUPAJ</td>
                    <td class="number">' . $skupajCas . '</td>
                </tr>
            </tfoot>
        </table>
        
        <div class="footer">
            <p>Tiskano iz Study Tracker sistema</p>
        </div>
    </body>
    </html>';

    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . sanitizeFilename($predmetIme) . '_poglavja.html"');
    echo $html;
    exit();
}

function exportExcel($predmetIme, $podatki)
{
    $csv = "\xEF\xBB\xBF";

    $csv .= "Predmet," . str_replace('"', '""', $predmetIme) . "\n";
    $csv .= "Datum," . date('d.m.Y H:i') . "\n";
    $csv .= "Skupaj poglavij," . count($podatki) . "\n";
    $csv .= "\n";
    $csv .= "Poglavje,Čas učenja (minute)\n";

    $skupajCas = 0;
    foreach ($podatki as $row) {
        $cas = (int)$row['cas_ucenja'];
        $skupajCas += $cas;
        $csv .= "\"" . str_replace('"', '""', $row['ime_poglavja']) . "\"," . $cas . "\n";
    }

    $csv .= "\n";
    $csv .= "SKUPAJ,\"" . $skupajCas . "\"\n";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . sanitizeFilename($predmetIme) . '_poglavja.csv"');
    echo $csv;
    exit();
}

function generateChart($podatki)
{
    if (empty($podatki)) {
        return '<p>Ni podatkov za prikaz grafa</p>';
    }

    $maxCas = max(array_map(function ($row) {
        return (int)$row['cas_ucenja'];
    }, $podatki));
    if ($maxCas == 0) $maxCas = 1;

    $chartWidth = 600;
    $chartHeight = 400;
    $padding = 60;
    $graphWidth = $chartWidth - 2 * $padding;
    $graphHeight = $chartHeight - 2 * $padding;

    $barWidth = $graphWidth / (count($podatki) * 1.5);
    $barSpacing = $barWidth * 0.5;

    $svg = '<svg width="' . $chartWidth . '" height="' . $chartHeight . '" xmlns="http://www.w3.org/2000/svg" style="border: 1px solid #ddd; margin: 20px auto; display: block;">';

    $svg .= '<line x1="' . $padding . '" y1="' . ($chartHeight - $padding) . '" x2="' . ($chartWidth - $padding) . '" y2="' . ($chartHeight - $padding) . '" stroke="#333" stroke-width="2"/>';
    $svg .= '<line x1="' . $padding . '" y1="' . $padding . '" x2="' . $padding . '" y2="' . ($chartHeight - $padding) . '" stroke="#333" stroke-width="2"/>';

    for ($i = 0; $i <= 5; $i++) {
        $value = ($maxCas / 5) * $i;
        $y = $chartHeight - $padding - ($i / 5) * $graphHeight;
        $svg .= '<text x="' . ($padding - 10) . '" y="' . ($y + 5) . '" font-size="11" text-anchor="end">' . (int)$value . '</text>';
        if ($i > 0) {
            $svg .= '<line x1="' . ($padding - 5) . '" y1="' . $y . '" x2="' . ($padding + 5) . '" y2="' . $y . '" stroke="#333"/>';
        }
    }

    $currentX = $padding + $barSpacing;
    $colors = ['#0066cc', '#0099ff', '#00cc99', '#ff9900', '#ff6600', '#cc0000'];
    $colorIndex = 0;

    foreach ($podatki as $row) {
        $cas = (int)$row['cas_ucenja'];
        $barHeight = ($cas / $maxCas) * $graphHeight;
        $barY = $chartHeight - $padding - $barHeight;
        $color = $colors[$colorIndex % count($colors)];
        $colorIndex++;

        $svg .= '<rect x="' . $currentX . '" y="' . $barY . '" width="' . $barWidth . '" height="' . $barHeight . '" fill="' . $color . '" opacity="0.8"/>';

        $label = substr($row['ime_poglavja'], 0, 15);
        $svg .= '<text x="' . ($currentX + $barWidth / 2) . '" y="' . ($chartHeight - $padding + 20) . '" font-size="10" text-anchor="middle" transform="rotate(-45 ' . ($currentX + $barWidth / 2) . ' ' . ($chartHeight - $padding + 20) . ')">' . htmlspecialchars($label) . '</text>';

        $svg .= '<text x="' . ($currentX + $barWidth / 2) . '" y="' . ($barY - 5) . '" font-size="11" font-weight="bold" text-anchor="middle">' . $cas . '</text>';

        $currentX += $barWidth + $barSpacing;
    }

    $svg .= '</svg>';
    return $svg;
}

function sanitizeFilename($filename)
{
    return preg_replace('/[^A-Za-z0-9_\-.]/', '_', $filename);
}
