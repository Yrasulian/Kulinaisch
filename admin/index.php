<?php
include ("./include/header.php");
include ("./include/sidebar.php");



$query_gericht = "SELECT * FROM gericht ORDER BY id DESC LIMIT 10"; 
$gericht = $conn->query($query_gericht);



$query_lebensmittel = "SELECT * FROM lebensmittel ORDER BY id DESC LIMIT 10";
$lebensmittel = $conn->query($query_lebensmittel);

$query_gewuerz = "SELECT * FROM gewuerz ORDER BY id  lIMIT 10";
$gewuerz = $conn->query($query_gewuerz);

?>




<div class="container-fluid">
    <div class="row">
        <main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
            </div>
            <h3>recent added gerichts</h3>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>beschreibung</th>
                        </tr>
                    </thead>
                   <tbody>
                        <?php while ($row = $gericht->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['title'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['beschreibung'] ?? '-'); ?></td>
                                <td>
                                    <a href="edit_gericht.php?id=<?php echo $row['id']?>" class="btn btn-sm btn-outline-info">Edit</a>
                                    <a href="index.php?entity=gericht&action=delete&id=<?php echo $row['id']?>" class="btn btn-sm btn-outline-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <h3>recent added lebensmittel</h3>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>kalorien pro 100 gram</th>
                            <th>Geschmack</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <?php while ($row = $lebensmittel->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['title'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['kalorien_pro_100g'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['geschmack'] ?? '-'); ?></td>
                                <td>
                                    <a href="edit_lebensmittel.php?id=<?php echo $row['id']?>" class="btn btn-sm btn-outline-info">Edit</a>
                                    <a href="index.php?entity=lebensmittel&action=delete&id=<?php echo $row['id']?>" class="btn btn-sm btn-outline-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>


            <h3>recent added gewürz</h3>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $gewuerz->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['title'] ?? '-'); ?></td>
                                <td>
                                    <a href="edit_gewuerz.php?id=<?php echo $row['id']?>" class="btn btn-sm btn-outline-info">Edit</a>
                                    <a href="index.php?entity=gewuerz&action=delete&id=<?php echo $row['id']?>" class="btn btn-sm btn-outline-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>

    </div>
</div>
</body>
</html>