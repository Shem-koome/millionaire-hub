<?php
define('APP_STARTED', true);
session_start(); // ✅ Fix: start session
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/middleware/auth.php';

$user_id = $_SESSION['user_id'];

// Fetch categories & notes
$catsStmt = $pdo->prepare("SELECT id, name FROM categories WHERE user_id=? AND is_deleted=0");
$catsStmt->execute([$user_id]);
$categories = $catsStmt->fetchAll(PDO::FETCH_ASSOC);

$notesStmt = $pdo->prepare("SELECT id, content FROM notes WHERE user_id=? ORDER BY created_at DESC");
$notesStmt->execute([$user_id]);
$notes = $notesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch reminders
$stmt = $pdo->prepare("SELECT * FROM reminders WHERE user_id=? ORDER BY remind_at ASC");
$stmt->execute([$user_id]);
$reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<div class="container">
    <h1>Reminders</h1>

    <!-- ADD REMINDER -->
    <div class="card" style="padding:15px; margin-bottom:20px;">
        <input type="text" id="newTitle" placeholder="Reminder title" style="width:100%; margin-bottom:10px;">
        <textarea id="newDesc" placeholder="Description (optional)" style="width:100%; margin-bottom:10px;"></textarea>

        <select id="newCategory" style="width:100%; margin-bottom:10px;">
            <option value="">-- Optional Category --</option>
            <?php foreach($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <select id="newNote" style="width:100%; margin-bottom:10px;">
            <option value="">-- Link Note (optional) --</option>
            <?php foreach($notes as $n): ?>
                <option value="<?= $n['id'] ?>"><?= substr(htmlspecialchars($n['content']),0,30) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="text" id="newTime" placeholder="Select date & time" style="width:100%; margin-bottom:10px;">
        <button onclick="addReminder()">Add Reminder</button>
    </div>

    <!-- REMINDERS LIST -->
    <div id="reminderList">
        <?php foreach($reminders as $r): ?>
            <div class="reminder card" data-id="<?= $r['id'] ?>" style="padding:10px; margin-bottom:10px;">
                <input class="rTitle" value="<?= htmlspecialchars($r['title']) ?>" style="width:100%; margin-bottom:5px;">
                <textarea class="rDesc" style="width:100%; margin-bottom:5px;"><?= htmlspecialchars($r['description']) ?></textarea>

                <select class="rCategory" style="width:100%; margin-bottom:5px;">
                    <option value="">-- Optional Category --</option>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $r['category_id']==$c['id']?'selected':'' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select class="rNote" style="width:100%; margin-bottom:5px;">
                    <option value="">-- Link Note (optional) --</option>
                    <?php foreach($notes as $n): ?>
                        <option value="<?= $n['id'] ?>" <?= $r['note_id']==$n['id']?'selected':'' ?>>
                            <?= substr(htmlspecialchars($n['content']),0,30) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="text" class="rTime" 
                       value="<?= date('Y-m-d H:i', strtotime($r['remind_at'])) ?>" 
                       style="width:100%; margin-bottom:5px;">

                <span class="status" style="display:block; margin-bottom:5px;"><?= $r['status'] ?></span>

                <button onclick="updateReminder(<?= $r['id'] ?>)">Save</button>
                <button onclick="markDone(<?= $r['id'] ?>)">Done</button>
                <button onclick="deleteReminder(<?= $r['id'] ?>)">Delete</button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
flatpickr("#newTime", { enableTime: true, dateFormat: "Y-m-d H:i" });

// Apply flatpickr to existing reminders
document.querySelectorAll('.rTime').forEach(input => {
    flatpickr(input, { enableTime: true, dateFormat: "Y-m-d H:i" });
});

function addReminder(){
    const title = document.getElementById('newTitle').value.trim();
    const time = document.getElementById('newTime').value.trim();

    if(!title){
        alert('Please enter a title');
        return;
    }
    if(!time){
        alert('Please select a date & time');
        return;
    }

    fetch('../app/actions/reminders/reminder_actions.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action:'add_reminder',
            title,
            description: document.getElementById('newDesc').value,
            category_id: document.getElementById('newCategory').value,
            note_id: document.getElementById('newNote').value,
            remind_at: time
        })
    }).then(r=>r.json()).then(res=>{
        if(res.success) location.reload();
        else alert('Error adding reminder');
    });
}

function updateReminder(id){
    const div = document.querySelector(`.reminder[data-id="${id}"]`);
    const title = div.querySelector('.rTitle').value.trim();
    const time = div.querySelector('.rTime').value.trim();

    if(!title){
        alert('Title cannot be empty');
        return;
    }
    if(!time){
        alert('Please select a date & time');
        return;
    }

    fetch('../app/actions/reminders/reminder_actions.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action:'edit_reminder',
            id,
            title,
            description: div.querySelector('.rDesc').value,
            category_id: div.querySelector('.rCategory').value,
            note_id: div.querySelector('.rNote').value,
            remind_at: time
        })
    }).then(r=>r.json()).then(res=>{
        if(res.success) alert('Reminder updated');
        else alert('Error updating reminder');
    });
}

function markDone(id){
    fetch('../app/actions/reminders/reminder_actions.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:new URLSearchParams({action:'mark_done',id})
    }).then(r=>r.json()).then(()=>location.reload());
}

function deleteReminder(id){
    if(!confirm('Delete this reminder?')) return;

    fetch('../app/actions/reminders/reminder_actions.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:new URLSearchParams({action:'delete_reminder',id})
    }).then(r=>r.json()).then(res=>{
        if(res.success) document.querySelector(`.reminder[data-id="${id}"]`).remove();
    });
}
</script>

<?php include '../includes/footer.php'; ?>
