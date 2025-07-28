
<?php
include("./include/header.php");   

$query = "SELECT * FROM gericht_lebensmittel WHERE gericht_id = gericht_id ";
$gericht_lebensmittel = $conn->query($query);

$query = "SELECT * FROM gericht WHERE beschreibung IS NOT NULL ";
$gericht = $conn->query($query);

?>

<div>
    <div class="container">
        <h1 class="mt-5">Willkomen zu unserer Küche</h1>
        <p class="lead">Hier kannst du viele leckere Gerichte finden</p>
        <p>Nutze unsere Rezeptsuche um dich zu wundern.</p>
    </div>
</div>

<div class="container">
    <h2>Hauptspeisen</h2>
    <div class="row">
        <div class="col md-6 p-4 ">
            <h2>Flexitarien</h2>
            <img src="./img/hamburger-8026582_640.jpg" alt="hamburger" class="img-fluid rounded-4">
        </div>
         <div class="col md-6 p-4 ">
            <h2>Flexitarien</h2>
            <img src="./img/hamburger-8026582_640.jpg" alt="hamburger" class="img-fluid rounded-4">
        </div>
         <div class="col md-6 p-4 ">
            <h2>Flexitarien</h2>
            <img src="./img/hamburger-8026582_640.jpg" alt="hamburger" class="img-fluid rounded-4">
        </div>
        <div class="col md-6 p-4 ">
            <h2>Vegan</h2>
            <img src="./img/vegan-4809593_640.jpg" alt="vegan" class="img-fluid rounded-4">
        </div>  
    </div>      
</div>
<br><br>
<div class="container">
    <h2>Hauptspeisen</h2>
    <div class="row">
        <div class="col md-6 p-4 ">
            <h2>Flexitarien</h2>
            <img src="./img/hamburger-8026582_640.jpg" alt="hamburger" class="img-fluid rounded-4">
        </div>
         <div class="col md-6 p-4 ">
            <h2>Flexitarien</h2>
            <img src="./img/hamburger-8026582_640.jpg" alt="hamburger" class="img-fluid rounded-4">
        </div>
         <div class="col md-6 p-4 ">
            <h2>Flexitarien</h2>
            <img src="./img/hamburger-8026582_640.jpg" alt="hamburger" class="img-fluid rounded-4">
        </div>
        
        <figure class="figure md-6 p-4">
            <img src="./img/vegan-4809593_640" class="figure-img img-fluid rounded" alt="vegan">
            <figcaption class="figure-caption">eine gesunde und umweltfreundliche Ernährungsweise.</figcaption>
        </figure>

 
    </div>      
</div>


   
    <script src="./js/bootstrap.bundle.main.js"></script>
</body>
</html>