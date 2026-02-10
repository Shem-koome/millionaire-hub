<?php
define('APP_STARTED', true);
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/middleware/auth.php';

$user_id = $_SESSION['user_id'];

// Fetch categories for optional note assignment
$catStmt = $pdo->prepare("SELECT id, name FROM categories WHERE user_id=? AND is_deleted=0");
$catStmt->execute([$user_id]);
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch notes
$noteStmt = $pdo->prepare("SELECT * FROM notes WHERE user_id=? ORDER BY created_at DESC");
$noteStmt->execute([$user_id]);
$notes = $noteStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="container">
    <h1>Notes</h1>

    <!-- ADD NOTE -->
    <div class="card">
        <h3>New Note</h3>
        <textarea id="newContent" placeholder="Write your note..." rows="5"></textarea>

        <select id="newCategory">
            <option value="">-- Optional Category --</option>
            <?php foreach($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <button class="btn" onclick="addNote()">Add Note</button>
    </div>

    <!-- NOTES LIST -->
    <div id="notesList">
        <?php foreach($notes as $note): ?>
            <div class="note card" data-id="<?= $note['id'] ?>">
                <textarea class="noteContent" rows="4"><?= htmlspecialchars($note['content']) ?></textarea>

                <select class="noteCategory">
                    <option value="">-- Optional Category --</option>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $note['category_id']==$c['id']?'selected':'' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="btn-group">
                    <button class="btn" onclick="editNote(<?= $note['id'] ?>)">Save</button>
                    <button class="btn delete" onclick="deleteNote(<?= $note['id'] ?>)">Delete</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function addNote() {
    const content = document.getElementById('newContent').value.trim();
    const category_id = document.getElementById('newCategory').value;

    if(!content){ alert('Note cannot be empty'); return; }

    fetch('../app/actions/reminders/reminder_actions.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action:'add_note',
            content,
            category_id
        })
    }).then(r=>r.json()).then(res=>{
        if(res.success) location.reload();
        else alert('Error adding note');
    });
}

function editNote(id){
    const div = document.querySelector(`.note[data-id="${id}"]`);
    const content = div.querySelector('.noteContent').value.trim();
    const category_id = div.querySelector('.noteCategory').value;

    if(!content){ alert('Note cannot be empty'); return; }

    fetch('../app/actions/reminders/reminder_actions.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action:'edit_note',
            id, content, category_id
        })
    }).then(r=>r.json()).then(res=>{
        if(res.success) alert('Note updated');
        else alert('Error updating note');
    });
}

function deleteNote(id){
    if(!confirm('Delete this note?')) return;

    fetch('../app/actions/reminders/reminder_actions.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({action:'delete_note', id})
    }).then(r=>r.json()).then(res=>{
        if(res.success) document.querySelector(`.note[data-id="${id}"]`).remove();
        else alert('Error deleting note');
    });
}
</script>

<style>
.container { max-width:800px; margin:20px auto; }
.card { padding:15px; margin:15px 0; border-radius:10px; background:#fff; box-shadow:0 0 5px rgba(0,0,0,0.1); }
textarea { width:100%; padding:10px; margin:5px 0; font-size:14px; border-radius:5px; border:1px solid #ccc; resize:vertical; }
select { width:100%; padding:5px; margin:5px 0; }
.btn { padding:5px 10px; margin:5px 5px 0 0; cursor:pointer; border:none; border-radius:5px; background:#007bff; color:#fff; }
.btn.delete { background:#dc3545; }
.btn-group { margin-top:10px; }
</style>
