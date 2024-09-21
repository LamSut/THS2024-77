<?php
require '../../vendor/autoload.php';
require_once "../../login/config.php";
session_start();

if (!isset($_SESSION['idacc'])){
  if(isset($_COOKIE["idacc"])){
    $username = $_COOKIE["idacc"];
    $_SESSION['idacc'] = $username;
    $stmt = $db->prepare("select * from acc where idacc = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $_SESSION['name']= $row['name'];
    $_SESSION['admin']= $row['admin'];
    $_SESSION['darkmode']= $row['darkmode'];
  }
  else{
    header("location: ../../login/index.php");
    exit;
  }  
}

if (isset($_SESSION['admin']) && $_SESSION['admin'] == 0){
  header("location: ../../user/index.php");
  exit;
}

$style = "style.css";
$logo = "Logo.png";
$settingBTN = "settings-icon.png";
$editLectureBTN = "edit-icon.png";
$deleteLectureBTN = "Delete.png";

if (isset($_SESSION['darkmode']) && $_SESSION['darkmode'] == 1) {
  $style = "style-dark.css";
  $logo = "Dark-Logo.png";
  $settingBTN = "Dark-settings-icon.png";
  $editLectureBTN = "Dark-edit-icon.png";
  $deleteLectureBTN = "Dark-Delete.png";
}
?> 

<!DOCTYPE html>
<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../../<?php echo $style; ?>?v=<?php echo time(); ?>">
<title>Lectures</title>
</head>

<body>

  <div id="header">
    <div id="top">
      <a href=""><img src="../../img/<?php echo $logo; ?>" alt="W0rm" style="height: 80px"></a>
      <div id="usermenu">
        <div style="float:right">
          <span><?php echo $_SESSION["name"];?></span>
          <button onclick="usermenu()" class="drop-btn"><img src="../../img/<?php echo $settingBTN; ?>" style="height: 25px;"></button>
        </div>
        <div class="dropdown">
          <div id="dropdownContent" class="dropdown-content">
            <a href="../profile/view.php">Profile</a>
            <a href="../comments/index.php">Comments</a>
            <a href="../settings/index.php">Settings</a>
            <a href="../logout.php" role="button">Log Out</a>
          </div>
        </div>  
      </div>
    </div>
    <div id="navbar">
      <a href="../index.php">Home</a>
      <a class="active" href="../lectures/view.php">Lectures</a>
      <a href="../challenges/view.php">CTF Challenges</a>
      <a href="../labs/view.php">Labs</a>
    </div>
  </div>
  
  <div id="content">
    <!-- SEARCH -->
    <div style="display: flex; justify-content: space-between; margin-bottom: 20px">
      <h2>Lectures</h2>
      <form action="" method="get" style="margin: 25px 25px 0px 0px;">
        <a href="add-new-lecture.php" id="add-btn">New Lecture <b>+</b></a>
        <!-- <input id="add-btn" type="submit" value="New Lecture +">   -->
        <input type="text" name="search" id="search-input" placeholder="Search challenges..." style="margin-top: 8px">
        <input id="search-btn" type="submit" value="Search" style="padding: 6px 12px 7px">  
      </form>
    </div>

    <!-- SHOW LECTURES -->
    <section class="lecture-container">
      <div class="lectures">
        <?php
        $limit = 5;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($currentPage - 1) * $limit;
        
        $search = isset($_GET['search']) ? $_GET['search'] : '';  // Get search term from URL

        $stmt = $db->prepare("SELECT * FROM lectures WHERE title LIKE ? ORDER BY id_lectures DESC LIMIT ?, ?");
        $search_term = "%$search%"; // Add wildcards for partial matches
        $stmt->bind_param("sss", $search_term, $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
          while($lecture = $result -> fetch_assoc()) {
            echo "<div id='link-lecture'>";
            echo "<div class='head-lecture'>";
            echo "<a class='title-lecture' href='news.php?id_lectures=" . $lecture['id_lectures'] . "'>" . $lecture['title'] . "</a>";
            echo "<form action='' class='edit-delete' method='post' id='form-edit-delete'>";
            echo "<a name='edit' class='icon-edit' href='edit-lecture.php?id_lectures=" .$lecture['id_lectures'] . "'><img class='edit-img' src='../../img/" . $editLectureBTN . "'></a>";
            echo "<a onclick=\"return confirm('Do you really want to delete?')\" name='delete' class='icon-edit' href='delete-lecture-action.php?id_lectures=".$lecture['id_lectures'] . "'><img class='delete-img' src='../../img/" . $deleteLectureBTN . "'></a>";
            echo "</form>";
            echo "</div>";
            echo "<div class='des'>";
            if(strlen($lecture['des']) > 250) {
              echo "<div class='lecture-content'>";
              echo "<p class='text'>" . substr($lecture['des'], 0, 250) . "<span class='dots'>...</span>" . "<span id='more' class='more'>". substr($lecture['des'], 250) ."</span>" . "<span><button style='' id='btn-read-more' class='btn-read-more'>Read more</button></span>" . "</p>";
              echo "</div>";
            } else {
              echo "<p>" . $lecture['des'] . "</p>";
            }
            echo "</div>";
            echo "<div>";
            echo "<small class='date-time'>";
            echo $lecture['time'];
            echo "</small>";
            echo "<small id='author' class='author'>";
            $stmtAuthor = $db->prepare("SELECT name from acc where idacc = ?");
            $stmtAuthor->bind_param("s", $lecture['idacc']);
            $stmtAuthor->execute();
            $resultAuthor = $stmtAuthor->get_result();

            if ($resultAuthor->num_rows > 0) {
              $authorname = $resultAuthor->fetch_assoc();
              echo "By " . $authorname['name'];  
            } else {
              echo "By Someone"; 
            }
            echo "</small>";
            echo "<hr>";
            echo "</div>";
            echo "</div>";
          }
        } else {
          echo "<p>No lecture found.</p>";
        }
        ?>
      </div>
    </section>

    <?php
      $stmt1 = $db->prepare("SELECT title, des FROM lectures WHERE title LIKE ?");
      $search_term1 = "%$search%"; // Add wildcards for partial matches
      $stmt1->bind_param("s", $search_term);

      $stmt1->execute();
      $result1 = $stmt1->get_result();
      
      $totalChallenges = $result1->num_rows; // Replace with function to get total CTF entries

      $totalPages = ceil($totalChallenges / $limit);

      if ($totalPages > 1) {
        echo "<div class='pagination' style='margin: auto; margin-top: 15px'> ";
      
        // Generate previous page link (if not on first page)
        if ($currentPage > 1) {
          $prevPage = $currentPage - 1;
          $prevUrl = "?search=$search&page=$prevPage";  
          echo "<a href='?search=$search&page=1'>First</a>";
          echo "<a href='$prevUrl'>◄</a>";
        }
        else{
          echo "<a href='?search=$search&page=1'>First</a>";
          echo "<a href=''>◄</a>";
        }
      
        // Generate page number links
        if($totalPages<6){
          for ($i = 1; $i <= $totalPages; $i++) {
            $activeClass = ($i == $currentPage) ? "active" : "";
            $c = ($i == $currentPage) ? "active" : "";
            $pageUrl = "?search=$search&page=$i";  
            echo "<a class='$activeClass' href='$pageUrl'>$i</a>";
          }
        }
        else{
          if ($currentPage < 4) {
            for ($i = 1; $i <= 5; $i++) {
              $activeClass = ($i == $currentPage) ? "active" : "";
              $pageUrl = "?search=$search&page=$i";
              echo "<a class='$activeClass' href='$pageUrl'>$i</a>";
            }
            
          }
          else if ($currentPage == $totalPages) {
            for ($i = $currentPage - 4; $i <= $totalPages; $i++) {
              $activeClass = ($i == $currentPage) ? "active" : "";
              $pageUrl = "?search=$search&page=$i";  
              echo "<a class='$activeClass' href='$pageUrl'>$i</a>";
            }
          }
          else if ($currentPage + 2 > $totalPages) {
            for ($i = $currentPage - 3; $i <= $totalPages; $i++) {
              $activeClass = ($i == $currentPage) ? "active" : "";
              $pageUrl = "?search=$search&page=$i";  
              echo "<a class='$activeClass' href='$pageUrl'>$i</a>";
            }
          } 
          else {  
            for ($i = $currentPage - 2; $i <= $currentPage + 2; $i++) {
              $activeClass = ($i == $currentPage) ? "active" : "";
              $pageUrl = "?search=$search&page=$i"; 
              echo "<a class='$activeClass' href='$pageUrl'>$i</a>";
            }
          }
        }
      
        // Generate next page link (if not on last page)
        if ($currentPage < $totalPages) {
          $nextPage = $currentPage + 1;
          $nextUrl = "?search=$search&page=$nextPage";  
          echo "<a href='$nextUrl'>►</a>";
          echo "<a href='?search=$search&page=" . ($totalPages) . "'>Last</a>";
        }
        else{
          echo "<a href=''>►</a>";
          echo "<a href='?search=$search&page=" . ($totalPages) . "'>Last</a>";
        }
        echo "</div>";
      }
    ?>
  </div>
  
  <script src="../../javascript.js"></script>
  <?php include("../../footer.php") ?>
</body>
</html>