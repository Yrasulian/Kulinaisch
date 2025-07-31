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
    

    // 4. Einfache Validierung: Der Titel ist ein Pflichtfeld
    if (!empty($title)) {
        try {
            // Bereite die SQL-Anweisung vor, um SQL-Injection zu verhindern
            $query = "INSERT INTO gewuerz (title) VALUES (:title)";
            $stmt = $conn->prepare($query);

            // Führe die Anweisung mit den Daten aus
            $stmt->execute([
                ':title' => $title,
                
            ]);

            // Setze eine Erfolgsmeldung für den Benutzer
            $success_message = "Der Gewürz '<strong>" . htmlspecialchars($title) . "</strong>' wurde erfolgreich hinzugefügt!";

        } catch (PDOException $e) {
            
            $error_message = "Datenbankfehler: " . $e->getMessage();
        }
    } else {
        
        $error_message = "Bitte geben Sie einen Namen für dan Gewürz an. Dies ist ein Pflichtfeld.";
    }
}
?>


<main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Neuer Gewürz hinzufügen</h1>
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
                    <form method="POST" action="add_gewuerz.php">
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Name des Gewuerzes *</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="z.B. Buntpfefer" required>
                            <div class="form-text">Dies ist der Name, der in Rezepten und Listen angezeigt wird.</div>
                        </div>
                        
                        <hr>
                        <button type="submit" class="btn btn-primary">Gewürz speichern</button>
                        <a href="index.php" class="btn btn-secondary">Abbrechen</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

    </div> 
</div> 

<!-- Bootstrap JS für die Funktionalität von Alerts etc. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>