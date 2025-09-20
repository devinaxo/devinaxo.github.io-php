<?php
$projects = [];
$debug_info = '';
$new_project_message = '';

if (file_exists(__DIR__ . '/.env')) {
  $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    list($name, $value) = explode('=', $line, 2);
    putenv(trim($name) . '=' . trim($value));
  }
}

$host = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$dbname = getenv('DB_NAME');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
  $conn = new mysqli($host, $username, $password, $dbname);
  if ($conn->connect_error) {
    $new_project_message = 'Database connection failed: ' . $conn->connect_error;
  } else {
  $name = $conn->real_escape_string($_POST['name'] ?? '');
  $url = $conn->real_escape_string(isset($_POST['url']) ? $_POST['url'] : '');
  $icon = $conn->real_escape_string($_POST['icon'] ?? '');
  $size = $conn->real_escape_string($_POST['size'] ?? '');
  $type_id = intval($_POST['type_id'] ?? 0);
  $technologies = $_POST['technologies'] ?? [];

    $insert_sql = "INSERT INTO project (name, url, icon, size, type_id) VALUES ('$name', '$url', '$icon', '$size', $type_id)";
    if ($conn->query($insert_sql)) {
      $project_id = $conn->insert_id;
      foreach ($technologies as $tech_id) {
        $tech_id = intval($tech_id);
        $conn->query("INSERT INTO project_technology (project_id, technology_id) VALUES ($project_id, $tech_id)");
      }
      $conn->close();
      header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
      exit;
    } else {
      $new_project_message = "Error adding project: " . $conn->error;
    }
    $conn->close();
  }
}

if (!$host || !$username || !$password || !$dbname) {
  $debug_info = 'Database environment variables not set';
} else {
  $conn = new mysqli($host, $username, $password, $dbname);
  if ($conn->connect_error) {
    $debug_info = 'Database connection failed: ' . $conn->connect_error;
  } else {
    $sql = "SELECT p.id, p.name, p.url, p.icon, p.size, p.description, t.name AS type_name
                FROM project p
                LEFT JOIN type t ON p.type_id = t.id";
    $result = $conn->query($sql);
    if (!$result) {
      $debug_info = 'Project fetch query failed: ' . $conn->error;
    } else {
      while ($row = $result->fetch_assoc()) {
        $row['technologies'] = [];
        $tech_sql = "SELECT tech.name FROM project_technology pt JOIN technology tech ON pt.technology_id = tech.id WHERE pt.project_id = ?";
        $stmt = $conn->prepare($tech_sql);
        if ($stmt) {
          $stmt->bind_param('i', $row['id']);
          $stmt->execute();
          $tech_result = $stmt->get_result();
          if ($tech_result) {
            while ($tech = $tech_result->fetch_assoc()) {
              $row['technologies'][] = $tech;
            }
          }
          $stmt->close();
        }
        $projects[] = $row;
      }
    }
    $types = [];
    $technologies = [];
    $type_result = $conn->query("SELECT id, name FROM type");
    if ($type_result) {
      while ($row = $type_result->fetch_assoc()) $types[] = $row;
    }
    $tech_result = $conn->query("SELECT id, name FROM technology");
    if ($tech_result) {
      while ($row = $tech_result->fetch_assoc()) $technologies[] = $row;
    }
    $conn->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>devinaxo's portfolio emporium</title>
  <link rel="icon" href="img/ico.jpg" type="image/x-icon">
  <link href="https://fonts.cdnfonts.com/css/w95fa" rel="stylesheet">
  <link rel="stylesheet" href="css/98.css">
  <link rel="stylesheet" href="css/jquery-ui.css">
  <link rel="stylesheet" href="css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script src="js/jquery-3.7.1.js"></script>
  <script src="js/jquery-ui.js"></script>
  <script src="js/jquery.ui.touch-punch.js"></script>
  <script src="https://www.youtube.com/iframe_api"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
  <script type="text/javascript">
    (function() {
      emailjs.init({
        publicKey: "KA9ygHqE3XL4BIUP5",
      });
    })();
  </script>
</head>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    new TomSelect('#technologies', {
      plugins: ['remove_button'],
      maxOptions: 1000,
      create: false,
      sortField: {
        field: "text",
        direction: "asc"
      }
    });
  });
</script>

<body>
  <div class="desktop">
    <div class="icons">
      <div class="icon-spot">
        <img class="unselectable" src="img/recycle_bin_empty-5.png" alt="Recycle bin icon" id="icon3">
        <p class="icon-title white unselectable">Recycle Bin</p>
      </div>
      <div class="icon-spot">
        <img class="unselectable" src="img/computer_explorer-5.png" alt="Computer icon" id="icon4">
        <p class="icon-title white unselectable">Computer</p>
      </div>
      <div class="icon-spot clickable-folder" data-icon="icon1" data-window="win1">
        <img class="unselectable image-folder" src="img/directory_closed_cool-0.png" alt="Folder icon" id="icon1">
        <p class="icon-title white unselectable">Projects</p>
      </div>
      <div class="icon-spot clickable-folder" data-icon="icon2" data-window="win2">
        <img class="unselectable image-folder" src="img/directory_closed_cool-0.png" alt="Folder icon" id="icon2">
        <p class="icon-title white unselectable">Multimedia</p>
      </div>
      <div class="icon-spot clickable-new-project" data-icon="icon-new" data-window="win-new">
        <img class="unselectable" src="img/project-icons/winrep-1.png" alt="New Project icon" id="icon-new">
        <p class="icon-title white unselectable">New Project</p>
      </div>
      <div class="icon-spot empty"></div>
    </div>
    <div class="centered min-w-[35%] max-w-[50%]">
      <div class="window needs-closing" id="win-new" style="display:none;">
        <div class="title-bar" id="title-new">
          <div class="title-bar-text unselectable">
            <img src="img/project-icons/winrep-1.png" alt class="window-icon">Add New Project
          </div>
          <div class="title-bar-controls">
            <button aria-label="Close" class="window-close" data-icon="icon-new" data-window="win-new"></button>
          </div>
        </div>
        <div class="window-body">
          <form id="new-project-form" method="POST" class="flex flex-col gap-4 p-4 w-full max-w-2xl mx-auto">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="name">Name:</label>
              <input type="text" name="name" id="name" required class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="url">Repo URL:</label>
              <input type="text" name="url" id="url" required class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="icon">Icon:</label>
                <select name="icon" id="icon-select" required class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="">Select icon</option>
                  <?php
                    $icon_dir = 'img/project-icons';
                    $icon_files = array_filter(scandir($icon_dir), function($f) {
                      return preg_match('/\.(png|jpg|jpeg|gif)$/i', $f);
                    });
                    foreach ($icon_files as $icon): ?>
                      <option value="<?= $icon_dir . '/' . $icon ?>"><?= $icon ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="mt-2 flex items-center gap-2">
                  <span class="text-sm text-gray-500">Preview:</span>
                  <img id="icon-preview" src="" alt="" class="w-8 h-8 border rounded bg-white">
                </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="size">Size:</label>
              <input type="text" name="size" id="size" required class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="type_id">Type:</label>
              <select name="type_id" id="type_id" required class="w-full px-3 py-2 text-gray-700 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select type</option>
                <?php if (isset($types)) foreach ($types as $type): ?>
                  <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="technologies">Technologies:</label>
              <select name="technologies[]" id="technologies" multiple required class="w-full max-w-xl px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <?php if (isset($technologies)) foreach ($technologies as $tech): ?>
                  <option value="<?= $tech['id'] ?>"><?= htmlspecialchars($tech['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex flex-row items-center justify-center gap-2">
              <img src="img/project-icons/winrep-1.png" alt class="address-sendicon w-5 h-5"> Add Project
            </button>
          </form>
          <div id="new-project-message" class="black"><?= isset($new_project_message) ? htmlspecialchars($new_project_message) : '' ?></div>
        </div>
      </div>
      <div class="window needs-closing" id="win1">
        <div class="title-bar" id="title1">
          <div class="title-bar-text unselectable"><img src="img/directory_closed_cool-0.png" alt
              class="window-icon">Exploring - C:\MyDocuments\Projects</div>
          <div class="title-bar-controls">
            <button aria-label="Close" class="window-close" data-icon="icon1" data-window="win1"></button>
          </div>
        </div>
        <div class="window-body overflow-scroll max-h-1/3">
          <div class="sunken-panel w-full">
            <table class="interactive text-black">
              <thead>
                <tr>
                  <th class="w-full">Name</th>
                  <th>Size</th>
                  <th>Type</th>
                  <th class="w-full">Technologies</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($projects)): ?>
                  <?php foreach ($projects as $project): ?>
                    <?php if (!empty($project['url'])): ?>
                      <tr class="cursor-pointer" onclick="window.open('<?= htmlspecialchars($project['url']) ?>', '_blank')">
                        <td class="flex items-center gap-2">
                          <img src="<?= htmlspecialchars($project['icon']) ?>" alt="Project icon" class="w-4 h-4">
                          <?= htmlspecialchars($project['name']) ?>
                        </td>
                        <td><?= htmlspecialchars($project['size']) ?></td>
                        <td><?= htmlspecialchars($project['type_name']) ?></td>
                        <td>
                          <?php
                          $techs = array_map(function ($tech) {
                            return htmlspecialchars($tech['name']);
                          }, $project['technologies']);
                          echo implode(', ', $techs);
                          ?>
                        </td>
                      </tr>
                    <?php else: ?>
                      <tr class="opacity-60">
                        <td class="flex items-center gap-2">
                          <img src="<?= htmlspecialchars($project['icon']) ?>" alt="Project icon" class="w-4 h-4">
                          <span class="text-gray-500">
                            <?= htmlspecialchars($project['name']) ?> (No URL available)
                          </span>
                        </td>
                        <td><?= htmlspecialchars($project['size']) ?></td>
                        <td><?= htmlspecialchars($project['type_name']) ?></td>
                        <td>
                          <?php
                          $techs = array_map(function ($tech) {
                            return htmlspecialchars($tech['name']);
                          }, $project['technologies']);
                          echo implode(', ', $techs);
                          ?>
                        </td>
                      </tr>
                    <?php endif; ?>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="4">No projects found. <?= $debug_info ? 'Debug: ' . htmlspecialchars($debug_info) : '' ?>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="window needs-closing" id="win2">
        <div class="title-bar" id="title2">
          <div class="title-bar-text unselectable">
            <img src="img/directory_closed_cool-0.png" alt class="window-icon">Exploring
            C:\MyDocuments\Multimedia
          </div>
          <div class="title-bar-controls">
            <button aria-label="Close" class="window-close" data-icon="icon2" data-window="win2"></button>
          </div>
        </div>
        <div class="window-body">
          <div class="sunken-panel w-full">
            <table class="interactive text-black">
              <thead>
                <tr>
                  <th class="w-full">Name</th>
                  <th>Size</th>
                  <th>Type</th>
                  <th>Description</th>
                </tr>
              </thead>
              <tbody>
                <tr data-trigger-modal data-modal-id="modal01">
                  <td class="flex items-center gap-2 cursor-pointer">
                    <img src="img/images/image_old_jpeg-0.png" alt="Image icon" class="w-4 h-4">
                    Sick leviathan
                  </td>
                  <td>1.2 MB</td>
                  <td>JPEG Image</td>
                  <td>Monster Hunter Frontier artwork</td>
                </tr>
                <tr data-trigger-modal data-modal-id="modal02">
                  <td class="flex items-center gap-2 cursor-pointer">
                    <img src="img/images/image_old_jpeg-0.png" alt="Image icon" class="w-4 h-4">
                    Sniffing
                  </td>
                  <td>876 KB</td>
                  <td>JPEG Image</td>
                  <td>funny</td>
                </tr>
                <tr data-trigger-modal data-modal-id="modal03">
                  <td class="flex items-center gap-2 cursor-pointer">
                    <img src="img/images/media_player_file-2.png" alt="Video icon" class="w-4 h-4">
                    Depression nap
                  </td>
                  <td>15.3 MB</td>
                  <td>Video File</td>
                  <td>Depression PSA</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div id="modal01" class="modal">
            <span class="close" id="close01">&times;</span>
            <img src="" alt="" class="modal-content" id="img01">
          </div>
          <div id="modal02" class="modal">
            <span class="close" id="close02">&times;</span>
            <img src="" alt="" class="modal-content" id="img02">
          </div>
          <div id="modal03" class="modal">
            <span class="close" id="close03">&times;</span>
            <div class="iframe-container" id="iframeContainer03">
              <iframe width="560px" height="315px" src="https://www.youtube.com/embed/t38tMHRHRco?si=k4uPcffQuu64NEY6"
                id="video03" title="YouTube video player" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
          </div>
        </div>
      </div>
      <div>
        <div class="portfolio-window">
          <div class="window" id="win3">
            <div class="title-bar" id="title3">
              <div class="title-bar-text unselectable"><img src="img/help_book_cool-4.png" alt class="window-icon">My
                Info</div>
              <div class="title-bar-controls">
                <button aria-label="Minimize" class="window-minimize" data-icon="portfolio-btn"
                  data-window="win3"></button>
              </div>
            </div>
            <div class="window-body">
              <div class="portfolio-info">
                <img src="img/mugshot.jpg" alt class="circle-mug">
                <p class="black">Hey, I'm Nacho. I'm but a humble comp sci
                  student who's been into this, officially, for at least 4
                  years now, but started tinkering around with technology
                  around 10 years ago with passion projects such as videogame
                  modding in C and C++, and have since then started to branch
                  out into anything that catches my interest (such
                  as making this and other webpages, mobile apps and Python
                  scripts). <br><br> Feel free to double click on those
                  folders and check out my stuff! (and pictures I have deemed
                  "cool")
                </p>
                <p class="black">If for any reason you want to contact me,
                  reach over to me via <br>
                  *LinkedIn: <a href="https://www.linkedin.com/in/devinacho/" target="_blank">devinacho</a> <br>
                  *Twitter: <a href="https://twitter.com/devinachoes" target="_blank">@devinachoes</a>
                  <br>
                  *... Or use that handy email form down below!
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="address-window">
          <div class="window">
            <div class="title-bar">
              <div class="title-bar-text unselectable"><img src="img/message_envelope_open-0.png" alt
                  class="window-icon">Hello! - Message (Plain Text)</div>
              <div class="title-bar-controls">
              </div>
            </div>
            <div class="window-body black">
              <form class="address-form" id="contact-form">
                <div class="address-div">
                  <button class="address-btn" type="button"><label for="destinatary"><img src="img/address_book-0.png"
                        alt class="address-sendicon">To...</label></button>
                  <input class="address-box" id="me" type="email" value="gar.la.ignacio@gmail.com" readonly>
                </div>
                <div class="address-div">
                  <button class="address-btn" type="button" id="cc-btn"><label for="remittent"><img
                        src="img/address_book-0.png" alt class="address-sendicon">Cc...</label></button>
                  <input class="address-box" id="notme" name="remittent" type="email"
                    placeholder="E-mail (Click button to change cc signature)" required>
                </div>
                <div class="address-div">
                  <label for="subject">Subject: </label>
                  <input class="address-box" type="text" name="subject" required>
                </div>
                <textarea name="message" id="letter" class="address-txtarea" required></textarea>
                <button class="address-btn flex flex-row place-items-center" type="submit" id="button"><img
                    src="img/address_book_card.png" alt class="address-sendicon">
                  Send</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <footer>
      <div><img id="start-button" src="img/start.png" alt></div>
      <div><img id="portfolio-btn" src="img/portfolio-btn-pressed.png" alt data-icon="portfolio-btn" data-window="win3">
      </div>
      <div id="time" class="text-lg">00:00:00</div>
    </footer>
    <script src="js/app.js"></script>
</body>

</html>