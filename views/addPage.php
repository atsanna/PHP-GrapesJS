<div class="container-fluid">                   
    <div class="row">
        <div class="col-md-12 py-3">
            <?php
            // Add page properties
            if (isset($_POST['submit'])) {
                if (!empty($_FILES['image']['name'])) {
                    $errors = array();
                    $file_name = $_FILES['image']['name'];
                    $file_size = $_FILES['image']['size'];
                    $file_tmp = $_FILES['image']['tmp_name'];
                    $file_type = $_FILES['image']['type'];
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    // $file_ext = strtolower(end(explode('.', $_FILES['image']['name'])));

                    $extensions = array(
                        "jpeg",
                        "jpg",
                        "png",
                        "gif"
                    );

                    if (in_array($file_ext, $extensions)) {
                        if (file_exists("../uploads/" . $file_name)) {
                            $errors[] = $file_name . " is already exists.";
                        } else {
                            move_uploaded_file($file_tmp, "../uploads/" . $file_name);
                            echo '<div class="alert alert-success" role="alert">';
                            echo "Your file was uploaded successfully.";
                            echo '</div>';
                        }
                    } else {
                        $errors[] = "Extension not allowed, please choose a JPEG, JPG, PNG or GIF file. <br/>Or you have not selected a file";
                    }

                    if ($file_size > 2097152) {
                        $errors[] = 'File size must be excately 2 MB';
                    }

                    if (empty($errors) === true) {
                        echo '<div class="alert alert-success" role="alert">';
                        echo "Success";
                        echo '</div>';
                    } else {
                        foreach ($errors as $key => $item) {
                            echo '<div class="alert alert-danger" role="alert">';
                            echo "$item <br>";
                            echo '</div>';
                        }
                    }
                } else {
                    echo '<div class="alert alert-danger" role="alert">';
                    echo "It is necessary to add an image that relates the page";
                    echo '</div>';
                }

                $title = protect($_POST['title']); // Page name
                $link = protect(strtolower(str_replace(" ", "-", $_POST['link']))); // Page link
                $keyword = protect($_POST['keyword']);
                $classification = protect($_POST['classification']);
                $description = protect($_POST['description']);
                $startpage = protect($_POST['startpage']);
                $parent = protect($_POST['parent']);
                $active = protect($_POST['active']);
                $change=0;

                $qlv1 = $conn->prepare("SELECT id, startpage FROM page WHERE startpage=?");
                $qlv1->bind_param("i", $startpage);
                $qlv1->execute();
                $presult = $qlv1->get_result();
                if ($presult->num_rows > 0) {
                    $dt = $presult->fetch_assoc();
                    $idsp = $dt['id'];
                    $updp = $conn->prepare("UPDATE page SET startpage=? WHERE id=?");
                    $updp->bind_param("ii", $change, $idsp);
                    $updp->execute();
                    $updp->close();
                }

                $qlv1->close();

                // Check if parent exist or is empty
                if (!is_int($parent) || empty($parent)) {
                    $parent = 0;
                }

                // Insert info in table PAGE 
                $sql = "INSERT INTO page (title, link, keyword, classification, description, image, startpage, parent, active) "
                        . "VALUES (?,?,?,?,?,?,?,?,?)";
                $updp = $conn->prepare($sql);
                $updp->bind_param("ssssssiii", $title, $link, $keyword, $classification, $description, $file_name, $startpage, $parent, $active);
                $updp->execute();
                $last_id = $conn->insert_id;
                $updp->close();

                if (!empty($last_id)) {

                    // Insert info in table MENU
                    $sqlm = "INSERT INTO menu (page_id, title_page, link_page, parent_id) "
                            . "VALUES (?, ?, ?, ?)";
                    $updpm = $conn->prepare($sqlm);
                    $updpm->bind_param("issi", $last_id, $title, $link, $parent);
                    $updpm->execute();
                    $last_idm = $conn->insert_id;
                    $updpm->close();
                    if (!empty($last_idm)) {
                        $_SESSION['SuccessMessage'] = "Page " . $title . " : Created ";
                    } else {
                        $_SESSION['ErrorMessage'] = "Failed: The page was not added to the menu";
                    }
                } else {
                    $_SESSION['ErrorMessage'] = "Failed: The page has not been created";
                }
                echo '<meta http-equiv="refresh" content="3; url=builder.php?id=' . $last_id . '" />';
            }
            echo '<h3>Add new page</h3>' . "\n";
            echo '<form method="post" enctype="multipart/form-data">' . "\n";
            echo '<div class="row"><div class="col-md-6">' . "\n";
            echo '<div class="form-group">
    <label for="title">Title</label>
    <input type="text" class="form-control" id="title" name="title">
  </div>' . "\n";
            echo '</div><div class="col-md-6">' . "\n";
            echo '<div class="form-group">
    <label for="link">Link</label>
    <input type="text" class="form-control" id="link" name="link">
  </div>' . "\n";
            echo '</div></div><div class="form-group">
    <label for="keyword">Keyword</label>
    <input type="text" class="form-control" id="keyword" name="keyword">
  </div>' . "\n";
            echo '<div class="form-group">
    <label for="classification">Classification</label>
    <input type="text" class="form-control" id="classification" name="classification">
  </div>' . "\n";
            echo '<div class="form-group">
    <label for="description">Description</label>
    <input type="text" class="form-control" id="description" name="description">
  </div>' . "\n";
            echo '<div class="form-group">
    <label for="image">Image</label>
    <input type="file" class="form-control" id="imagen" name="image">
  </div>' . "\n";
            echo '<div class="form-group">
    <label for="parent">Parent</label>' . "\n";
            echo nparent();
            echo '</div>' . "\n";
            echo '<div class="form-group">
    <label for="startpage">Is home page</label>
    <select class="form-control" id="startpage" name="startpage">
    <option value="1">Yes</option>
    <option value="0">No</option>
</select>
  </div>' . "\n";
            echo '<div class="form-group">
    <label for="active">Active</label>
    <select class="form-control" id="active" name="active">
    <option value="1">Active</option>
    <option value="0">Inactive</option>
</select>
  </div>' . "\n";
            echo '<input type="submit" name="submit" class="btn btn-primary" value="Save">' . "\n";
            echo '</form>' . "\n";
            ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
$("#title").keyup(function () {

    var value = $(this).val();
    value = value.toLowerCase();

    value = value.replace(/ /g, "-");
    $("#link").val(value);
}).keyup();
});
</script>