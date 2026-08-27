<?php include ('./conn/conn.php')?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sticky Note App</title>

    <!-- Style CSS -->
    <link rel="stylesheet" href="./assets/style.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light ">
        <a class="navbar-brand ml-4" href="#">Sticky Notes App</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse ml-4" id="navbarNavAltMarkup">
            <div class="navbar-nav">
            <a class="nav-link" href="" data-toggle="modal" data-target="#addNoteModal">Add Note</a>
            </div>
        </div>
    </nav>

    <!-- Add Note Modal -->
    <div class="modal fade mt-5" id="addNoteModal" tabindex="-1" aria-labelledby="addNote" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNote">Add Note</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="./endpoint/add-note.php" method="POST">
                        <div class="form-group">
                            <label for="noteTitle">Note Title</label>
                            <input type="text" class="form-control" id="noteTitle" name="note_title">
                        </div>
                        <div class="form-group">
                            <label for="noteContent">Note</label>
                            <textarea class="form-control" name="note_content" id="noteContent" cols="30" rows="10"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-dark">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Note Modal -->
    <div class="modal fade mt-5" id="viewNoteModal" tabindex="-1" aria-labelledby="viewNote" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content note-modal">

                <div class="modal-body">
                    <div class="header row">
                        <h1 class="modal-title ml-4" id="viewNote" style="width:83%">Note</h1>

                        <div class="btn-group mr-2 float-right" role="group" aria-label="First group">
                            <button type="button" class="btn" id="editModal"><i class="fa-solid fa-pencil"></i></button>
                        </div>
                    </div>

                    <div class="view-note-content">
                        <form action="./endpoint/update-note.php" method="POST">
                            <input type="text" class="form-control" id="noteModalID" name="tbl_note_id" hidden>
                            <div class="form-group">
                                <label for="noteModalTitle">Title:</label>
                                <input type="text" class="form-control" id="noteModalTitle" name="note_title" style="border:none;" readonly>
                            </div>
                            <div class="form-group">
                                <label for="noteModalContent">Content:</label>
                                <textarea class="form-control" name="note_content" id="noteModalContent" cols="30" rows="8" style="border: none;" readonly></textarea>
                            </div>
                            <small class="float-right" id="datePosted"></small>
                            <div class="float-right" id="edit-buttons" style="display:none">
                                <button type="button" class="btn btn-secondary edit-button" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-dark edit-button">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="main row">

        <?php
        
        $stmt = $conn->prepare("SELECT * FROM `tbl_note`");
        $stmt->execute();
        $result = $stmt->fetchAll();

        foreach ($result as $row) {
            $noteID = $row["tbl_note_id"];
            $noteTitle = $row["note_title"];
            $noteContent = $row["note_content"];
            $datePosted = $row["date_posted"];
        
            ?>

            <div class="note-container" onclick="viewNote(<?= $noteID ?>)">
            <button type="button" class="btn float-right mt-2 mr-2" id="deleteModal"><i class="fa-solid fa-x" onclick="deleteNote(<?= $noteID ?>)"></i></button>
                <div class="note-content">
                    <p id="noteID-<?= $noteID ?>" hidden><?= $noteID ?></p>
                    <p id="noteTitle-<?= $noteID ?>" hidden><?= $noteTitle ?></p>
                    <p id="noteContent-<?= $noteID ?>"><?= $noteContent ?></p>
                    <p id="datePosted-<?= $noteID ?>" hidden><?= $datePosted ?></p>
                </div>
            </div>
            <?php 
        }
        ?>
    </div>


    <!-- BootStrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <!-- Script JS -->
    <script src="./assets/script.js"></script>
</body>
</html>