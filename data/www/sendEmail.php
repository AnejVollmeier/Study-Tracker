<?php
// Konstante za SMTP komunikacijo
define('SMTP_BUFFER_SIZE', 512);
define('SMTP_TIMEOUT', 10);

/**
 * Pošlje email preko SMTP strežnika (Inbucket)
 * 
 * @param string $to Email naslov prejemnika
 * @param string $subject Zadeva emaila
 * @param string $htmlBody HTML vsebina emaila
 * @return bool True če je email uspešno poslan, false sicer
 */
function sendEmail($to, $subject, $htmlBody) {
    $smtpHost = 'inbucket';
    $smtpPort = 2500;
    $from = 'noreply@studytracker.local';
    
    try {
        // Odpri SMTP povezavo
        $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, SMTP_TIMEOUT);
        if (!$socket) {
            error_log("SMTP connection failed: $errstr ($errno)");
            return false;
        }
        
        // Preberi odgovor strežnika
        $response = fgets($socket, SMTP_BUFFER_SIZE);
        if (substr($response, 0, 3) != '220') {
            error_log("SMTP Error: " . $response);
            fclose($socket);
            return false;
        }
        
        // EHLO
        fputs($socket, "EHLO studytracker.local\r\n");
        $response = fgets($socket, SMTP_BUFFER_SIZE);
        
        // Preberi vse EHLO odgovore (multiline)
        while (substr($response, 3, 1) == '-') {
            $response = fgets($socket, SMTP_BUFFER_SIZE);
        }
        
        // MAIL FROM
        fputs($socket, "MAIL FROM: <$from>\r\n");
        $response = fgets($socket, SMTP_BUFFER_SIZE);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP MAIL FROM Error: " . $response);
            fclose($socket);
            return false;
        }
        
        // RCPT TO
        fputs($socket, "RCPT TO: <$to>\r\n");
        $response = fgets($socket, SMTP_BUFFER_SIZE);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP RCPT TO Error: " . $response);
            fclose($socket);
            return false;
        }
        
        // DATA
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, SMTP_BUFFER_SIZE);
        if (substr($response, 0, 3) != '354') {
            error_log("SMTP DATA Error: " . $response);
            fclose($socket);
            return false;
        }
        
        // Pripravi email glavo in vsebino
        $headers = "From: Study Tracker <$from>\r\n";
        $headers .= "To: <$to>\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        
        $message = $headers . "\r\n" . $htmlBody . "\r\n.\r\n";
        
        fputs($socket, $message);
        $response = fgets($socket, SMTP_BUFFER_SIZE);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP Message Error: " . $response);
            fclose($socket);
            return false;
        }
        
        // QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        return true;
    } catch (Exception $e) {
        error_log("Email sending exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Generira HTML vsebino emaila za nov predmet
 * 
 * @param string $imePredmeta Ime predmeta
 * @param string $datumIzpita Datum izpita
 * @return string HTML vsebina emaila
 */
function generateSubjectCreatedEmail($imePredmeta, $datumIzpita) {
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 0; color: #333; background-color: #f4f4f4; }
            .container { max-width: 600px; margin: 20px auto; background: white; padding: 0; border-radius: 8px; overflow: hidden; }
            .header { background: #0066cc; color: white; padding: 30px 20px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; }
            .header p { margin: 10px 0 0 0; opacity: 0.9; }
            .content { padding: 30px 20px; }
            .info-box { background: #f0f0f0; padding: 20px; margin: 20px 0; border-left: 4px solid #0066cc; border-radius: 4px; }
            .info-box strong { color: #0066cc; }
            .footer { background: #f8f8f8; padding: 20px; text-align: center; color: #999; font-size: 12px; border-top: 1px solid #e0e0e0; }
            .button { display: inline-block; background: #0066cc; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>📚 Nov predmet ustvarjen!</h1>
                <p>Uspešno ste dodali nov predmet v Study Tracker</p>
            </div>
            <div class="content">
                <h2>Čestitke! 🎉</h2>
                <p>Uspešno ste ustvarili nov predmet v vašem Study Tracker sistemu.</p>
                
                <div class="info-box">
                    <strong>Ime predmeta:</strong> ' . htmlspecialchars($imePredmeta) . '<br>
                    <strong>Datum izpita:</strong> ' . date('d.m.Y', strtotime($datumIzpita)) . '<br>
                    <strong>Datum ustvarjanja:</strong> ' . date('d.m.Y ob H:i') . '
                </div>
                
                <p>Začnite z dodajanjem poglavij in sledenjem vašega napredka. Spomnili vas bomo 2 dni pred izpitom!</p>
                
                <p style="margin-top: 30px;">Srečno pri učenju! 💪</p>
            </div>
            <div class="footer">
                <p>To sporočilo je bilo poslano iz Study Tracker sistema</p>
                <p>© ' . date('Y') . ' Study Tracker</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Generira HTML vsebino emaila za opomnik 2 dni pred izpitom
 * Vsebina je podobna kot pri PDF izvozu
 * 
 * @param string $imePredmeta Ime predmeta
 * @param string $datumIzpita Datum izpita
 * @param array $podatki Podatki o poglavjih in času učenja
 * @return string HTML vsebina emaila
 */
function generateExamReminderEmail($imePredmeta, $datumIzpita, $podatki) {
    $skupajCas = 0;
    foreach ($podatki as $row) {
        $skupajCas += (int)$row['cas_ucenja'];
    }
    
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 0; color: #333; background-color: #f4f4f4; }
            .container { max-width: 700px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; }
            .header { background: #ff6600; color: white; padding: 30px 20px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; }
            .header .subtitle { font-size: 16px; margin-top: 10px; opacity: 0.9; }
            .alert-box { background: #fff3cd; border: 2px solid #ff6600; padding: 20px; margin: 20px; border-radius: 5px; text-align: center; }
            .alert-box strong { color: #ff6600; font-size: 18px; }
            .content { padding: 20px; }
            .info { background: #f0f0f0; padding: 15px 20px; margin: 20px 0; border-left: 4px solid #ff6600; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th { background: #ff6600; color: white; padding: 12px; text-align: left; }
            td { padding: 12px; border-bottom: 1px solid #ddd; }
            tr:hover { background: #f9f9f9; }
            .number { text-align: right; font-weight: bold; }
            .total-row { background: #f0f0f0; font-weight: bold; font-size: 16px; }
            .footer { background: #f8f8f8; padding: 20px; text-align: center; color: #999; font-size: 12px; border-top: 1px solid #e0e0e0; }
            .chart-container { margin: 20px; padding: 20px; background: #f9f9f9; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>⏰ Opomnik: Izpit čez 2 dni!</h1>
                <div class="subtitle">' . htmlspecialchars($imePredmeta) . '</div>
            </div>
            
            <div class="alert-box">
                <strong>🎯 Vaš izpit bo čez 2 dni: ' . date('d.m.Y', strtotime($datumIzpita)) . '</strong>
            </div>
            
            <div class="content">
                <h2>Pregled vašega učenja</h2>
                
                <div class="info">
                    <strong>Predmet:</strong> ' . htmlspecialchars($imePredmeta) . '<br>
                    <strong>Datum izpita:</strong> ' . date('d.m.Y', strtotime($datumIzpita)) . '<br>
                    <strong>Skupaj poglavij:</strong> ' . count($podatki) . '<br>
                    <strong>Skupaj časa učenja:</strong> ' . $skupajCas . ' minut (' . round($skupajCas / 60, 1) . ' ur)
                </div>
                
                <h3>Čas učenja po poglavjih</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Poglavje</th>
                            <th class="number">Čas učenja (minute)</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    if (empty($podatki)) {
        $html .= '<tr><td colspan="2" style="text-align: center; padding: 20px; color: #999;">
                      Še niste dodali nobenih poglavij ali sej učenja.
                  </td></tr>';
    } else {
        foreach ($podatki as $row) {
            $cas = (int)$row['cas_ucenja'];
            $html .= '<tr>
                <td>' . htmlspecialchars($row['ime_poglavja']) . '</td>
                <td class="number">' . $cas . '</td>
            </tr>';
        }
    }
    
    $html .= '</tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td>SKUPAJ</td>
                            <td class="number">' . $skupajCas . '</td>
                        </tr>
                    </tfoot>
                </table>
                
                <p style="margin-top: 30px; padding: 20px; background: #e8f4f8; border-radius: 5px; text-align: center;">
                    <strong>💪 Še zadnji napor! Veliko uspeha na izpitu!</strong>
                </p>
            </div>
            
            <div class="footer">
                <p>To sporočilo je bilo poslano iz Study Tracker sistema</p>
                <p>© ' . date('Y') . ' Study Tracker</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}
