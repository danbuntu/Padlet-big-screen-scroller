<?php
/**
 * Created by PhpStorm.
 * User: dattwood
 * Date: 18/12/13
 * Time: 09:22
 */

// ---CONFIG ---
// the message to appear at the top of the wall
$message = "This is the message text";
// the code for the padlet wall you wish to use
$padletCode = 'dkosma1y6x';

// database settings
$dbhost ='localhost';
$dbuser = 'root';
$dbpass = 'xxx';
$dbname= 'padlet';

// ---End Config---
?>
<!doctype html>
<html>
<head lang="en">
    <meta http-equiv="refresh" content="300">
    <link rel="stylesheet" href="owl-carousel/owl.carousel.css">
    <link rel="stylesheet" href="owl-carousel/owl.theme.css">
    <link rel="stylesheet" href="bootstrap3/css/bootstrap.min.css">
    <!--<link rel="stylesheet" href="bootstrap3/css/bootstrap-theme.min.css">-->
    <link rel="stylesheet" href="styles.css">
    <script src="jquery-2.0.3.min.js"></script>
    <script src="owl-carousel/owl.carousel.js"></script>
    <script src="bootstrap3/js/bootstrap.min.js"></script>
    <script src="jquery-textfill-master/jquery.textfill.js"></script>
</head>
<body>

<?php
$dbh = new PDO(sprintf('mysql:host=%s;dbname=%s', $dbhost, $dbname), $dbuser, $dbpass);
$string ='http://padlet.com/wall/' . $padletCode . '.csv';
$url = 'http://padlet.com/wall/' . $padletCode;
// import the files from padlet
file_put_contents("padlet.csv", fopen($string, 'r'));

$file = file("padlet.csv");

// clear the database
$stmt = $dbh->prepare("DELETE FROM messages");
$stmt->execute();

$row = 1;
if (($handle = fopen("padlet.csv", "r")) !== FALSE) {
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data);
    // insert the records into the database ignore the first line as it's just the headers
    if ($row !=1) {
        $stmt = $dbh->prepare("INSERT INTO messages (subject, body, attachment, author, created, modified) VALUES (:subject, :body, :attachment, :author, :created, :modified )");
        $stmt->bindParam(':subject', $data[0]);
        $stmt->bindParam(':body', $data[1]);
        $stmt->bindParam(':attachment', $data[2]);
        $stmt->bindParam(':author', $data[3]);
        $stmt->bindParam(':created', $data[4]);
        $stmt->bindParam(':modified', $data[5]);
        $stmt->execute();
    }
    $row++;
}
fclose($handle);
}
// get all the messages
$stmt = $dbh->prepare("SELECT * FROM messages order by modified DESC");
$stmt->execute();
?>

<div class="container well">
    <div class="row heading">
        <div class="col-lg-3">
            <img src="logo.png">
        </div>
        <div class="col-lg-9">
            <h2><?php echo $message;?></h2>
        </div>
    </div>
    <div id="owl-example" class="owl-carousel">
        <?php
        while ($row = $stmt->fetch()) {
            ?>
            <?php if (strlen($row['subject']) > 1) {?>
            <div class="slide">
                <div class="slideWrapper">
                    <div class="title textfill"><span> <?php echo ucfirst($row['subject']); ?></span></div>
                    <div class="body textfill2"><span>Message: <?php echo ucfirst($row['body']); ?></span></div>
                </div>
            </div>
            <?php } ?>
        <?php
        } ?>
    </div>

</div>
<script>
    $(document).ready(function () {
        $("#owl-example").owlCarousel({
            items: 3,
            slideSpeed: 200,
            autoPlay: true,
            navigation: false,
            pagination: false
        });

        $(function () {
            $('.textfill').textfill({ maxFontPixels: 36 });
            $('.textfill2').textfill({ maxFontPixels: 36 });
            $('.textfillAuthor').textfill({ maxFontPixels: 22 });
        });
    });
</script>
</body>
</html>