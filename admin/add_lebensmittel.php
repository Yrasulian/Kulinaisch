<?php

include ("./include/header.php");
include ("./include/sidebar.php");

// 2. Initialisiere Variablen für Nachrichten an den Benutzer
$success_message = '';
$error_message = '';

// 3. Verarbeite das Formular, wenn es mit der POST-Methode gesendet wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Hole die Daten sicher aus dem Formular
    $title = trim($_POST['title'] ?? '');
    // Stelle sicher, dass Kalorien eine Zahl oder NULL ist
    $kalorien = !empty($_POST['kalorien_pro_100g']) ? (int)$_POST['kalorien_pro_100g'] : null;
    $geschmack = trim($_POST['geschmack'] ?? '');

    // 4. Einfache Validierung: Der Titel ist ein Pflichtfeld
    if (!empty($title)) {
        try {
            // Bereite die SQL-Anweisung vor, um SQL-Injection zu verhindern
            $query = "INSERT INTO lebensmittel (title, kalorien_pro_100g, geschmack) VALUES (:title, :kalorien, :geschmack)";
            $stmt = $conn->prepare($query);

            // Führe die Anweisung mit den Daten aus
            $stmt->execute([
                ':title' => $title,
                ':kalorien' => $kalorien,
                ':geschmack' => $geschmack
            ]);

            // Setze eine Erfolgsmeldung für den Benutzer
            $success_message = "Das Lebensmittel '<strong>" . htmlspecialchars($title) . "</strong>' wurde erfolgreich hinzugefügt!";

        } catch (PDOException $e) {
            
            $error_message = "Datenbankfehler: " . $e->getMessage();
        }
    } else {
        
        $error_message = "Bitte geben Sie einen Namen für das Lebensmittel an. Dies ist ein Pflichtfeld.";
    }
}
?>


<main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Neues Lebensmittel hinzufügen</h1>
    </div>

    <!-- Container für das Formular für ein sauberes Aussehen -->
    <div class="row">
        <div class="col-lg-8">
            
            <!-- Zeige Erfolgs- oder Fehlermeldungen an, falls vorhanden -->
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Das HTML-Formular zum Hinzufügen -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="add_lebensmittel.php">
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Name des Lebensmittels *</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="z.B. Kartoffel" required>
                            <div class="form-text">Dies ist der Name, der in Rezepten und Listen angezeigt wird.</div>
                        </div>
                        <div class="mb-3">
                            <label for="kalorien_pro_100g" class="form-label fw-semibold">Kalorien pro 100g</label>
                            <input type="number" class="form-control" id="kalorien_pro_100g" name="kalorien_pro_100g" placeholder="z.B. 77" step="1">
                        </div>
                        <div class="mb-3">
                            <label for="geschmack" class="form-label fw-semibold">Geschmack</label>
                            <input type="text" class="form-control" id="geschmack" name="geschmack" placeholder="z.B. erdig">
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary">Lebensmittel speichern</button>
                        <a href="index.php" class="btn btn-secondary">Abbrechen</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

    </div> <!-- Schließt das .row aus der sidebar.php -->
</div> <!-- Schließt das .container-fluid aus der sidebar.php -->

<!-- Bootstrap JS für die Funktionalität von Alerts etc. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>