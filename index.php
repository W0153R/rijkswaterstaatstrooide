<?php
include_once 'secrets.php';
$hourString = date("G", time());
$todayString = $hourString >= 20 ? date("Y-m-d", (time() + 86400)) : date("Y-m-d", time());
$yesterdayString = $hourString >= 20 ? date("Y-m-d", time()) : date("Y-m-d", (time() - 86400));
$daybeforeyesterdayString = $hourString >= 20 ? date("Y-m-d", (time() - 86400)) : date("Y-m-d", (time() - 172800));
$lastweekEnoch = time() - 604800;
$lastDateEnoch = 0;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rijkswaterstaat strooide</title>
  <meta name="keywords" content="zout,strooi,strooit,strooien,rijkswaterstaat,rijkswaterstaatstrooit,rijkswaterstaatstrooide,wanneer is er voor het laatst gestrooid,wanneer is er voor het laatst zout gestrooid" />
  <meta name="description" content="Historische data van rijkswaterstaatstrooit.nl. U kunt hier zien wanneer er voor het laatst zout op de weg is gestrooid." />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="sha256-NuCn4IvuZXdBaFKJOAcsU2Q3ZpwbdFisd5dux4jkQ5w=" crossorigin="anonymous" />
  <style type="text/css">
  body, html {
    font-family: Sans,Verdana,Arial,sans-serif;
    margin: 0;
    padding: 0;
    background: #f3f3f3;
  }
  h1 {
    font-size: 1.25em;
    font-weight: 400;
    white-space: nowrap;
  }
  a, a:visited, a:hover, a:active {
    color: rgb(150,150,150);
    text-decoration: none;
  }
  #header {
    max-height: 3.5em;
    overflow: hidden;
    transition: max-height 0.5s;
  }
  #headerWrapper {
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    justify-content: space-between;
    padding: 0 1em;
    background: #eaeaea;
    border-bottom: 0.25em solid #e6e6e6;
  }
  #info a {
    margin: 0 1em;
  }
  #infoContent {
    background: #e6e6e6;
    padding: 0.5em 4em 2em 4em;
  }
  #main {
    padding: 1em 2em;
    max-width: 70%;
    display: table;
    margin: 20vh auto;
    font-size: 1.25em;
    background: #fff;
    border-width: 0.1em 0 0.1em 0;
    border-color: #e6e6e6;
    border-style: solid;
    position: relative;
  }
  #main #tooltiptext {
    visibility: hidden;
    background-color: rgb(100,100,100);
    color: rgb(240,240,240);
    text-align: center;
    padding: 0.5em;
    border-radius: 0.25em;
    position: absolute;
    z-index: 1;
    bottom: 75%;
    left: 50%;
    opacity: 0;
    transition: opacity 0.3s;
    font-size: 0.75em;
    width: 26em;
    margin-left: -13em;
  }
  #main #tooltiptext::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -0.5em;
    border-width: 0.5em;
    border-style: solid;
    border-color: rgb(100,100,100) transparent transparent transparent;
  }
  #main:hover #tooltiptext, #main.show #tooltiptext {
    visibility: visible;
    opacity: 1;
  }
  #data {
    width: 20em;
    margin: 2em auto;
    padding: 0.5em;
    background: #fff;
    border-radius: 0.5em;
    max-height: 3em;
    overflow: hidden;
    transition: max-height 0.5s;
  }
  #data button {
    display: block;
    height: 2em;
    border-radius: 0.2em;
    border: none;
    background: #e6e6e6;
    font-size: 1.5em;
    width: 100%;
    padding: 0;
    margin: 0 0 1em 0;
    color: rgb(100,100,100);
    cursor: pointer;
  }
  .result:first-of-type {
    border-top: none;
  }
  .result {
    display: grid;
    grid-template-columns: 60% 40%;
    border-top: 0.1em solid #e6e6e6;
  }
  .result span {
    margin: 0.1em 0.2em;
  }
  .result .amount {
    text-align: right;
  }
  .tip {
    color: rgb(150,150,150);
  }
  @media (max-width: 500px){
    #main {
      max-width: 100%;
    }
    #data {
      width: 80vw;
    }
    #main #tooltiptext {
      width: 90vw;
      margin-left: -45vw;
    }
  }
  @media (max-width: 330px){
    h1 {
      font-size: 5.75vw;
    }
    #headerWrapper {
      padding: 0 0.5em;
      height: 3em;
    }
    #header {
      max-height: 3.25em;
    }
  }
  </style>
  <script type="text/javascript">
    var toggleShowInfo = function(el){
      document.getElementById('header').classList.toggle('show');
      el.setAttribute('onClick', 'toggleHideInfo(this);');
    };
    var toggleHideInfo = function(el){
      document.getElementById('header').classList.toggle('show');
      el.setAttribute('onClick', 'toggleShowInfo(this);');
    };
    var toggleShowData = function(el){
      el.parentElement.classList.toggle('show');
      el.innerHTML = '<i class="fa fa-angle-double-up"></i> Data <i class="fa fa-angle-double-up"></i>';
      el.setAttribute('onClick', 'toggleHideData(this);');
    };
    var toggleHideData = function(el){
      el.parentElement.classList.toggle('show');
      el.innerHTML = '<i class="fa fa-angle-double-down"></i> Data <i class="fa fa-angle-double-down"></i>';
      el.setAttribute('onClick', 'toggleShowData(this);');
    };
    </script>
</head>
<body>
  <div id="header">
    <div id="headerWrapper">
      <a href="https://rijkswaterstaatstrooide.nl"><h1>Rijkswaterstaat strooide</h1></a>
      <div id="info">
        <a href="#" onclick="toggleShowInfo(this);">Info</a>
      </div>
    </div>
    <div id="infoContent">
      <p>In Nederland strooien we zout op de weg om het vriespunt van regenwater te verlagen, dit doen we tegen gladheid en ter bescherming van het wegdek. Hierdoor blijven zowel de voertuigverzekering als de wegenbelasting betaalbaar. Het nadeel van zout is dat het metaal kan aantasten wanneer dat niet perfect is beschermd, zoals bij oudere voertuigen.<br/>
      Rijkswaterstaat visualiseert via de website <a href="https://rijkswaterstaatstrooit.nl" target="_blank">rijkswaterstaatstrooit.nl</a> informatie met betrekking tot het strooien van zout. Deze data is ook beschikbaar is via het <a href="https://www.rijkswaterstaat.nl/zakelijk/open-data" target="_blank">Open Data</a> platform maar historische data mist op dit platform.</p>
      <p><span style="font-style: italic;">Rijkswaterstaat strooide</span> kijkt één keer per uur op de website van Rijkswaterstaat en slaat de meeste recente hoeveelheid gestrooid zout van die dag op. De gegevens worden een jaar bewaard.</p>
      <p>Heeft u een fout gevonden of wilt u iets toegevoegd zien aan de website? De broncode van deze website <a href="https://github.com/W0153R/rijkswaterstaatstrooide/" target="_blank">staat op GitHub</a>, maak een vork en stuur een trek verzoek!</p>
    </div>
  </div>
<?php
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if ($conn) {
  $sql = "SELECT date, amount FROM data ORDER BY date DESC";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) > 0) {
    setlocale(LC_TIME, "nl_NL");
    while($row = mysqli_fetch_assoc($result)) {
      $rowDate = $row["date"];
      $rowDateEnoch = strtotime($rowDate);
      if ($lastDateEnoch == 0) {
?>
  <div id="main" ontouchmove="this.classList.toggle('show');">
    Rijkswaterstaat heeft <?php
        switch (true) {
            case ($rowDate == $todayString):
                echo "<span class=\"tip\">vandaag</span>";
                break;
            case ($rowDate == $yesterdayString):
                echo "<span class=\"tip\">gisteren</span> voor het laatst";
                break;
            case ($rowDate == $daybeforeyesterdayString):
                echo "<span class=\"tip\">eergisteren</span> voor het laatst";
                break;
            case ($rowDateEnoch >= $lastweekEnoch):
                echo "in de afgelopen week";
                break;
            default:
                echo "op " . strftime("%e %B %Y", $rowDateEnoch) . " voor het laatst";
        }
?>
 gestrooid<span id="tooltiptext">Rijkswaterstaat meet dagen van 20:00u tot 20:00u</span>
  </div>
  <div id="data">
    <button onclick="toggleShowData(this);"><i class="fa fa-angle-double-down"></i> Data <i class="fa fa-angle-double-down"></i></button>
<?php
      } elseif ($rowDateEnoch < ($lastDateEnoch - 86400)) {
?>
    <div class="result">
      <span class="date">...</span>
      <span class="amount"></span>
    </div>
<?php
      }
?>
    <div class="result">
      <span class="date"><?php echo strftime("%e %b %Y", $rowDateEnoch);?></span>
      <span class="amount"><?php echo number_format($row["amount"], 0, ",", ".");?>kg</span>
    </div>
<?php
      $lastDateEnoch = $rowDateEnoch;
    }
  }
  mysqli_close($conn);
} else {
  die("Connection failed: " . mysqli_connect_error());
}
?>
  </div>
  <script type="text/javascript">
    var showStyle = document.createElement('style');
    showStyle.innerHTML = '#data.show { max-height: ' + document.getElementById('data').scrollHeight + 'px }' +
                          '\n#header.show { max-height: ' + document.getElementById('header').scrollHeight + 'px }';
    document.querySelector('head').insertAdjacentElement("beforeend", showStyle);
  </script>
</body>
</html>
