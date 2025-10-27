<?php
$file = 'books.json';
if (!file_exists($file)) die("Error: Missing books.json");

$data = json_decode(file_get_contents($file), true);
if (!is_array($data)) die("Error: Invalid JSON format");

function showBooks($id, $title, $books) {
  if (empty($books)) return;

  echo "<section class='category' id='cat_$id' data-genre='".strtolower($title)."'>
          <div class='cat-header'>
            <h2>".htmlspecialchars($title)."</h2>
            <div class='scroll-controls'>
              <button class='scroll-btn left' onclick=\"scrollRow('$id', -1)\">&#10094;</button>
              <button class='scroll-btn right' onclick=\"scrollRow('$id', 1)\">&#10095;</button>
            </div>
          </div>
          <div class='book-list' id='$id'>";

  foreach ($books as $b) {
    $img = htmlspecialchars($b['imageLink'] ?? 'default.jpg');
    $bookTitle = htmlspecialchars($b['title'] ?? 'Untitled');
    $author = htmlspecialchars($b['author'] ?? '');
    $year = htmlspecialchars($b['year'] ?? '');

    echo "<div class='book-card'
             data-title='".htmlspecialchars($bookTitle)."'
             data-author='".htmlspecialchars($author)."'
             data-year='".htmlspecialchars($year)."'
             data-genre='".strtolower($title)."'
             data-img='images/$img'
             onclick='showPopup(this)'>
            <div class='book-cover'>
              <img src='images/$img' alt='$bookTitle'>
            </div>
            <div class='book-info'>
              <h3>$bookTitle</h3>";
    if ($author || $year) {
      echo "<p class='book-meta'>";
      if ($author) echo htmlspecialchars($author);
      if ($author && $year) echo " • ";
      if ($year) echo htmlspecialchars($year);
      echo "</p>";
    }
    echo "</div></div>";
  }

  echo "</div></section>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Palamar Library</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      color: #fff;
      background: url('color.jpg') no-repeat center center fixed;
      background-size: cover;
      background-attachment: fixed;
    }

    body::before {
      content: '';
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.6);
      z-index: -1;
    }

    .container {
      padding: 40px 30px;
      max-width: 1300px;
      margin: auto;
    }

    h1 {
      text-align: center;
      font-size: 2.5em;
      letter-spacing: 2px;
      color: #ffcc66;
      text-shadow: 0 2px 10px rgba(0,0,0,0.8);
      margin-bottom: 40px;
    }

    /* Search bar */
    .search-box {
      text-align: center;
      margin-bottom: 40px;
    }
    .search-box input {
      width: 60%;
      padding: 12px 15px;
      border-radius: 25px;
      border: none;
      outline: none;
      font-size: 16px;
      background: rgba(255,255,255,0.2);
      color: white;
      backdrop-filter: blur(6px);
    }
    .search-box input::placeholder {
      color: #ddd;
    }

    .category {
      margin-bottom: 70px;
      position: relative;
    }

    .cat-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 0 10px 15px 10px;
    }

    .category h2 {
      margin: 0;
      font-size: 1.8em;
      color: #ffdd99;
      text-shadow: 0 2px 8px rgba(0,0,0,0.6);
    }

    .book-list {
      display: flex;
      gap: 20px;
      overflow-x: auto;
      scroll-behavior: smooth;
      padding: 10px 0 20px;
    }
    .book-list::-webkit-scrollbar { display: none; }

    .book-card {
      flex: 0 0 auto;
      width: 200px;
      background: rgba(255,255,255,0.05);
      border-radius: 15px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.4);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
      backdrop-filter: blur(6px);
    }
    .book-card:hover {
      transform: translateY(-10px) scale(1.05);
      box-shadow: 0 8px 30px rgba(255,204,102,0.4);
    }

    .book-cover img {
      width: 100%;
      height: 270px;
      object-fit: cover;
      border-bottom: 2px solid rgba(255,255,255,0.2);
    }

    .book-info {
      padding: 12px 10px 18px;
      text-align: center;
    }

    .book-info h3 {
      font-size: 15px;
      font-weight: 600;
      color: #fff;
      margin: 6px 0;
      line-height: 1.3em;
      height: 40px;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }

    .book-meta {
      font-size: 13px;
      color: #ccc;
      margin-top: 6px;
    }

    .scroll-controls {
      display: flex;
      gap: 8px;
    }
    .scroll-btn {
      background: rgba(255,255,255,0.15);
      border: none;
      border-radius: 50%;
      width: 40px; height: 40px;
      font-size: 22px;
      color: #fff;
      cursor: pointer;
      transition: background 0.3s;
    }
    .scroll-btn:hover {
      background: rgba(255,204,102,0.8);
      color: #000;
    }

    /* Popup Modal */
    .popup {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.8);
      justify-content: center;
      align-items: center;
      z-index: 10;
    }

    .popup-content {
      background: rgba(255,255,255,0.08);
      backdrop-filter: blur(10px);
      padding: 25px;
      border-radius: 15px;
      width: 380px;
      color: white;
      text-align: center;
      position: relative;
      box-shadow: 0 0 25px rgba(255,255,255,0.3);
      animation: fadeIn 0.3s ease;
    }

    .popup-content img {
      width: 100%;
      height: auto; /* Full image height visible */
      border-radius: 10px;
      margin-bottom: 15px;
    }

    .popup-content h3 {
      color: #ffcc66;
      margin: 10px 0;
      font-size: 20px;
    }

    .popup-content p {
      color: #ddd;
      font-size: 15px;
      margin: 4px 0;
    }

    .close-btn {
      position: absolute;
      top: 10px; right: 15px;
      background: #ff5555;
      border: none;
      color: white;
      font-size: 18px;
      border-radius: 50%;
      width: 28px; height: 28px;
      cursor: pointer;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }

    @media (max-width: 700px) {
      .scroll-controls { display: none; }
      .book-card { width: 160px; }
      .book-cover img { height: 220px; }
    }
  </style>
</head>
<body>

<div class="container">
  <h1>PALAMAR COLLECTION LIBRARY</h1>

  <div class="search-box">
    <input type="text" id="searchInput" placeholder="Search:">
  </div>

  <?php
    showBooks("romance", "Romance Books", $data["romance_books"] ?? []);
    showBooks("fantasy", "Fantasy Books", $data["fantasy_books"] ?? []);
    showBooks("action", "Action Books", $data["action_books"] ?? []);
  ?>
</div>


<div class="popup" id="bookPopup">
  <div class="popup-content">
    <button class="close-btn" onclick="closePopup()">×</button>
    <img id="popupImg" src="" alt="">
    <h3 id="popupTitle"></h3>
    <p id="popupAuthor"></p>
    <p id="popupYear"></p>
  </div>
</div>

<script>
function scrollRow(id, dir) {
  const row = document.getElementById(id);
  if (row) row.scrollBy({left: dir * 400, behavior: 'smooth'});
}


document.getElementById('searchInput').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.book-card').forEach(card => {
    const text = (card.dataset.title + card.dataset.author + card.dataset.genre).toLowerCase();
    card.style.display = text.includes(q) ? 'block' : 'none';
  });
});


function showPopup(el) {
  document.getElementById('popupImg').src = el.dataset.img;
  document.getElementById('popupTitle').textContent = el.dataset.title;
  document.getElementById('popupAuthor').textContent = el.dataset.author ? "Author: " + el.dataset.author : "";
  document.getElementById('popupYear').textContent = el.dataset.year ? "Year: " + el.dataset.year : "";
  document.getElementById('bookPopup').style.display = 'flex';
}


function closePopup() {
  document.getElementById('bookPopup').style.display = 'none';
}
</script>

</body>
</html>
